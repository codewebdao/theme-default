<?php
namespace System\Database;

use PDO;
use PDOStatement;
use System\Libraries\Logger\LoggerInterface;
use System\Database\Query\Grammar;
use System\Database\Debug\QueryCollector;

/**
 * PdoDriver
 *
 * - Implements DatabaseDriver on top of PDO using Router for node selection.
 * - Auto route SQL to read/write.
 * - Sticky reads after writes within sticky_ms.
 * - Retries on deadlock or lost connection (configurable counters).
 * - Force-write scope support: withForceWrite(callable) (for Builder/raw API).
 * - PHP 7.4 compatible.
 */
final class PdoDriver implements DatabaseDriver
{
    /** @var string Connection name */
    private $connectionName;
    /** @var string Driver name: mysql|pgsql|sqlite */
    private $driverName;
    /** @var Grammar */
    private $grammar;
    /** @var Router */
    private $router;
    /** @var array{deadlock:int,lost:int} */
    private $retry;
    /** @var LoggerInterface|null */
    private $logger;
    /** @var int slow query threshold (ms) */
    private $slowMs = 500;
    /** @var bool */
    private $infoEnabled = false;
    /** @var bool */
    private $slowEnabled = true;

    /**
     * @param string $connectionName
     * @param string $driverName
     * @param Grammar $grammar
     * @param Router $router
     * @param array $retry e.g. ['deadlock'=>1,'lost'=>1]
     * @param LoggerInterface|null $logger
     */
    public function __construct($connectionName, $driverName, Grammar $grammar, Router $router, array $retry, LoggerInterface $logger = null)
    {
        $this->connectionName = (string)$connectionName;
        $this->driverName = (string)$driverName;
        $this->grammar = $grammar;
        $this->router = $router;
        $this->retry = array(
            'deadlock' => isset($retry['deadlock']) ? (int)$retry['deadlock'] : 0,
            'lost'     => isset($retry['lost'])     ? (int)$retry['lost']     : 0,
        );
        $this->logger = $logger;
    }

    public function getDriverName() { return $this->driverName; }

    /**
     * Infer intent from SQL (read if starts with SELECT/SHOW/DESCRIBE/EXPLAIN).
     *
     * @param string $sql
     * @return string 'read'|'write'
     */
    private function intentFromSql($sql)
    {
        return preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $sql) ? 'read' : 'write';
    }


    private function maskParams($params)
    {
        $maskKeys = array('password','token','secret','cvv');
        $out = array();
        foreach ($params as $k => $v) {
            $key = is_string($k) ? ltrim(strtolower($k), ':') : $k;
            $isMask = is_string($key) && in_array($key, $maskKeys, true);
            $out[$k] = $isMask ? '***' : $v;
        }
        return $out;
    }

    private function isDeadlock(\PDOException $e)
    {
        $msg = strtolower($e->getMessage());
        return (strpos($msg, 'deadlock') !== false) || (strpos($msg, 'lock wait timeout') !== false);
    }

    private function isLost(\PDOException $e)
    {
        $msg = strtolower($e->getMessage());
        return (strpos($msg, 'server has gone away') !== false) || (strpos($msg, 'lost connection') !== false);
    }

    /**
     * Core executor with retries and node routing.
     *
     * @param callable $fn function(PDO $pdo): mixed
     * @param string   $intent 'read'|'write'
     * @param string   $sql
     * @param array    $params
     * @return mixed
     * @throws \PDOException
     */
    private function execWithRetry($fn, $intent, $sql, $params)
    {
        $deadlockLeft = $this->retry['deadlock'];
        $lostLeft     = $this->retry['lost'];

        start:
        $start = microtime(true);
        $node  = null;
        $queryId = null; // Track query ID for updating later
        
        try {
            $node = $this->router->pickNode($this->connectionName, $intent);
            $pdo  = $this->router->pdo($node);
            
            // Log query BEFORE execution with temporary status
            $queryId = $this->logQueryBefore($sql, $params, $intent, $node);
            
            // Execute query
            $res  = $fn($pdo);
            $elapsed = (microtime(true) - $start) * 1000.0;

            // Update query log with success status and elapsed time
            $this->logQueryAfter($queryId, $elapsed, true);

            if ($intent === 'write') {
                $this->router->markWrite($this->connectionName);
            }
            return $res;
        } catch (\PDOException $e) {
            $elapsed = (microtime(true) - $start) * 1000.0;
            
            // Check if we should retry
            if ($this->isDeadlock($e) && $deadlockLeft-- > 0) { 
                // Update query log with retry status
                if ($queryId !== null) {
                    $this->logQueryAfter($queryId, $elapsed, false, 'deadlock_retry');
                }
                usleep(20000); 
                goto start; 
            }
            if ($this->isLost($e) && $lostLeft-- > 0) { 
                // Update query log with retry status
                if ($queryId !== null) {
                    $this->logQueryAfter($queryId, $elapsed, false, 'connection_lost_retry');
                }
                usleep(20000); 
                goto start; 
            }
            
            // Update query log with error status
            if ($queryId !== null) {
                $this->logQueryAfter($queryId, $elapsed, false, $e->getMessage());
            }
            
            // Log error nếu bật error target
            if ($this->logger) {
                $this->logger->error('db.error', array(
                    'conn' => $this->connectionName,
                    'node' => $node,
                    'sql'  => $sql,
                    'params'=> $this->maskParams($params),
                    'error'=> $e->getMessage(),
                ));
            }
            throw $e;
        }
    }

    /** Allow DatabaseConnection to configure slow_ms per connection. */
    public function setSlowMs($ms) { $this->slowMs = max(0, (int)$ms); return $this; }

    /** Toggle logging without changing external Logger */
    public function setLogToggles($infoEnabled, $slowEnabled)
    {
        $this->infoEnabled = (bool)$infoEnabled;
        $this->slowEnabled = (bool)$slowEnabled;
        return $this;
    }

    /** Quote value for rendering (debug only, not executed). */
    private function renderValue($v)
    {
        if ($v === null) return 'NULL';
        if (is_bool($v)) return $v ? '1' : '0';
        if (is_int($v) || is_float($v)) return (string)$v;
        // if (is_array($v)){
        //     $v = json_encode($v);
        // }
        // string: escape single quotes
        return "'" . str_replace("'", "''", (string)$v) . "'";
    }

    /** Interpolate SQL with bindings for debug view. Supports ? and :name. */
    private function renderSql($sql, array $params)
    {
        $rendered = (string)$sql;

        // 1) Named params (:name)
        $named = array();
        foreach ($params as $k => $v) {
            if (is_string($k)) {
                // chấp nhận key 'name' hoặc ':name' đều được
                $key = ltrim($k, ':');
                $named[$key] = $v;
            }
        }
        foreach ($named as $k => $v) {
            // thay đúng token ':key'
            $rendered = preg_replace('/:'.preg_quote($k,'/').'\b/', $this->renderValue($v), $rendered);
        }

        // 2) Positional '?'
        if (strpos($rendered, '?') !== false) {
            $positional = array_values(array_filter(
                $params,
                static function($kk){ return !is_string($kk); },
                ARRAY_FILTER_USE_KEY
            ));
            $out = '';
            $parts = explode('?', $rendered);
            $cnt = count($parts);
            for ($i=0; $i<$cnt-1; $i++) {
                $val = array_key_exists($i, $positional) ? $this->renderValue($positional[$i]) : '?';
                $out .= $parts[$i].$val;
            }
            $out .= $parts[$cnt-1];
            $rendered = $out;
        }

        return $rendered;
    }



    /**
     * Log query BEFORE execution (with status = pending)
     * Returns query ID for later updates
     *
     * @param string $sql
     * @param array  $params
     * @param string $intent
     * @param string $node
     * @return int Query ID in collector
     */
    private function logQueryBefore($sql, $params, $intent, $node)
    {
        // Push to in-memory collector with pending status
        $queryId = QueryCollector::add(array(
            'connection'   => $this->connectionName,
            'node'         => $node,
            'intent'       => $intent,
            'time_ms'      => 0.0,  // Will be updated after execution
            'sql_raw'      => $sql,
            'sql_rendered' => $this->renderSql($sql, $params),
            'params'       => $params,
            'status'       => 'pending', // Track execution status
            'error'        => null,
        ));
        
        return $queryId;
    }
    
    /**
     * Update query log AFTER execution with timing and status
     *
     * @param int    $queryId
     * @param float  $ms
     * @param bool   $success
     * @param string $error
     * @return void
     */
    private function logQueryAfter($queryId, $ms, $success = true, $error = null)
    {
        // Update the query collector entry
        QueryCollector::update($queryId, array(
            'time_ms' => round($ms, 3),
            'status'  => $success ? 'success' : 'error',
            'error'   => $error,
        ));
        
        if (!$this->logger) return;
        
        // Get full query data for logger
        $query = QueryCollector::get($queryId);
        if (!$query) return;
        
        $payload = array(
            'conn'   => $this->connectionName,
            'node'   => $query['node'],
            'driver' => $this->driverName,
            'intent' => $query['intent'],
            'sql'    => $query['sql_raw'],
            'params' => $this->maskParams($query['params']),
            'time_ms'=> round($ms, 3),
            'tx'     => $this->router->inTx($this->connectionName),
            'force'  => $this->router->inForceWrite($this->connectionName),
            'status' => $success ? 'success' : 'error',
        );
        
        if (!$success) {
            $payload['error'] = $error;
        }

        // Info log (chỉ khi bật)
        if ($this->infoEnabled) {
            $level = $success ? 'info' : 'error';
            $this->logger->log($level, 'db.query', $payload);
        }

        // Slow log (khi bật & vượt ngưỡng & success)
        if ($success && $this->slowEnabled && $this->slowMs > 0 && $ms >= $this->slowMs) {
            $this->logger->warning('db.slow_query', $payload);
        }
    }

    /* ===================== DatabaseDriver interface ===================== */

    /**
     * Execute SQL with bindings.
     * - SELECT-like → array rows
     * - DML → affected rows (int)
     *
     * @param string $sql
     * @param array  $params
     * @return mixed
     * @throws \PDOException
     */
    public function query($sql, array $params = array())
    {
        $intent = $this->intentFromSql($sql);

        return $this->execWithRetry(function(PDO $pdo) use ($sql, $params, $intent) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($intent === 'read') {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $stmt->rowCount();
        }, $intent, $sql, $params);
    }

    /** @return string */
    public function lastInsertId()
    {
        $node = $this->router->pickNode($this->connectionName, 'write');
        $pdo  = $this->router->pdo($node);
        return $pdo->lastInsertId();
    }

    /** @return bool */
    public function beginTransaction()
    {
        $this->router->beginTx($this->connectionName);
        $node = $this->router->pickNode($this->connectionName, 'write');
        $pdo  = $this->router->pdo($node);
        return $pdo->beginTransaction();
    }

    /** @return bool */
    public function commit()
    {
        $node = $this->router->pickNode($this->connectionName, 'write');
        $pdo  = $this->router->pdo($node);
        $ok   = $pdo->commit();
        $this->router->commitTx($this->connectionName);
        return $ok;
    }

    /** @return bool */
    public function rollBack()
    {
        $node = $this->router->pickNode($this->connectionName, 'write');
        $pdo  = $this->router->pdo($node);
        $ok   = $pdo->rollBack();
        $this->router->rollbackTx($this->connectionName);
        return $ok;
    }

    /* ===================== Additional helper (not in interface) ===================== */

    /**
     * Force-write scope: run callback with reads routed to WRITE (for read-after-write consistency).
     *
     * @param callable $fn function(): mixed
     * @return mixed
     */
    public function withForceWrite($fn)
    {
        $this->router->beginForceWrite($this->connectionName);
        try {
            return $fn();
        } finally {
            $this->router->endForceWrite($this->connectionName);
        }
    }
}
