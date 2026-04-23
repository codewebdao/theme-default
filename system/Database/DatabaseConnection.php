<?php

namespace System\Database;

use System\Libraries\Logger\LoggerInterface;
use System\Database\Query\Builder;
use System\Database\Query\Grammar;
use System\Database\Query\MysqlGrammar;
use System\Database\Query\PgsqlGrammar;
use System\Database\Query\SqliteGrammar;

/**
 * DatabaseConnection
 *
 * Logical connection (driver + router + grammar + prefix).
 * Provides: driver(), table(), tableName(), transaction().
 */
final class DatabaseConnection
{
    /** @var string */
    private $name;

    /** @var array */
    private $config;

    /** @var Router */
    private $router;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var DatabaseDriver */
    private $driver;

    /** @var Grammar */
    private $grammar;

    /** @var string Table prefix for this connection ('' if none) */
    private $prefix = '';

    /**
     * @param string $name
     * @param array $config
     * @param Router $router
     * @param LoggerInterface|null $logger
     */
    public function __construct($name, array $config, Router $router, LoggerInterface $logger = null)
    {
        $this->name   = (string)$name;
        $this->config = $config;
        $this->router = $router;
        $this->logger = $logger;

        $cx = isset($config['connections'][$name]) ? $config['connections'][$name] : null;
        if (!$cx) {
            throw new \InvalidArgumentException("Unknown connection: {$name}");
        }

        // Prefix resolution
        if (isset($cx['prefix']) && $cx['prefix'] !== '') {
            $this->prefix = (string)$cx['prefix'];
        } elseif (defined('APP_PREFIX')) {
            $this->prefix = (string)APP_PREFIX;
        }

        $driverName = isset($cx['driver']) ? $cx['driver'] : 'mysql';
        $retry      = isset($cx['retry']) ? $cx['retry'] : array('deadlock' => 1, 'lost' => 1);

        switch ($driverName) {
            case 'mysql':
                $this->grammar = new MysqlGrammar();
                break;
            case 'pgsql':
                $this->grammar = new PgsqlGrammar();
                break;
            case 'sqlite':
                $this->grammar = new SqliteGrammar();
                break;
            default:
                throw new \InvalidArgumentException("Unknown driver {$driverName}");
        }

        // PdoDriver (router/retry/force_write) is implemented in Part 2
        $this->driver = new PdoDriver($name, $driverName, $this->grammar, $this->router, $retry, $logger);

        // slow_ms
        $slowMs = isset($cx['slow_ms']) ? (int)$cx['slow_ms'] : 500;
        if (method_exists($this->driver, 'setSlowMs')) {
            $this->driver->setSlowMs($slowMs);
        }

        // logging flags per connection
        $log = isset($cx['logging']) ? $cx['logging'] : array();
        $infoEnabled = isset($log['info']['enabled']) ? (bool)$log['info']['enabled'] : false;
        $slowEnabled = isset($log['slow']['enabled']) ? (bool)$log['slow']['enabled'] : true;

        if (method_exists($this->driver, 'setLogToggles')) {
            $this->driver->setLogToggles($infoEnabled, $slowEnabled);
        }
    }

    /** @return string Connection name */
    public function name()
    {
        return $this->name;
    }

    /** @return DatabaseDriver */
    public function driver()
    {
        return $this->driver;
    }

    public function driverName()
    {
        return method_exists($this->driver, 'getDriverName')
            ? $this->driver->getDriverName()
            : 'mysql';
    }

    /** @return Grammar */
    public function grammar()
    {
        return $this->grammar;
    }

    /** @return string Current table prefix ('' when not used) */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * Safe helper to get prefixed table name for RAW SQL.
     * Handles simple alias ("users u" / "users AS u") and schema.table (prefix last part).
     *
     * INPUT:
     *   - string $table
     * OUTPUT:
     *   - string e.g., "cms_users u" or "schema.cms_users"
     */
    public function tableName($table)
    {
        $table = trim((string)$table);
        if ($this->prefix === '' || $table === '') {
            return $table;
        }

        // Extract alias if present
        $alias = '';
        $base  = $table;

        if (preg_match('/\s+AS\s+([a-zA-Z_][a-zA-Z0-9_]*)$/i', $table, $m)) {
            $alias = $m[1];
            $base  = trim(preg_replace('/\s+AS\s+[a-zA-Z_][a-zA-Z0-9_]*$/i', '', $table));
        } elseif (preg_match('/\s+([a-zA-Z_][a-zA-Z0-9_]*)$/', $table, $m)) {
            $before = substr($table, 0, -strlen($m[0]));
            if (substr($before, -1) !== '.') {
                $alias = $m[1];
                $base  = trim(substr($table, 0, -strlen($m[0])));
            }
        }

        // If schema.table => prefix only last segment
        if (strpos($base, '.') !== false) {
            $parts = explode('.', $base);
            $last  = array_pop($parts);
            if (strpos($last, $this->prefix) !== 0) {
                $last = $this->prefix . $last;
            }
            $base = implode('.', $parts) . '.' . $last;
        } else {
            if (strpos($base, $this->prefix) !== 0) {
                $base = $this->prefix . $base;
            }
        }

        if ($alias !== '') {
            return $base . ' AS ' . $alias;
        }
        return $base;
    }

    /**
     * Builder entrypoint for a table.
     * NOTE: Builder (Part 2) will receive $prefix and apply it automatically.
     *
     * @param string $table Unprefixed table name, e.g., "users"
     * @return Builder
     */
    public function table($table)
    {
        return (new Builder($this->driver, $this->grammar, $this->name, $this->prefix))->from($table);
    }

    /**
     * Execute callback inside a transaction (write).
     *
     * @param callable $fn function(DatabaseConnection): mixed
     * @return mixed
     * @throws \Throwable
     */
    public function transaction($fn)
    {
        $this->driver->beginTransaction();
        try {
            $result = $fn($this);
            $this->driver->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->driver->rollBack();
            throw $e;
        }
    }
}
