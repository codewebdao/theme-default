<?php
namespace System\Database;

use PDO;
use System\Libraries\Logger\LoggerInterface;

/**
 * ConnectionManager
 *
 * - Creates and caches PDO instances per "node" name from config['nodes'].
 * - Runs optional init_sql once per PDO.
 * - Can mark node "down" temporarily (simple circuit breaker).
 * - PHP 7.4 compatible.
 */
final class ConnectionManager
{
    /** @var array<string, PDO> */
    private $pool = array();

    /** @var array<string, int> node => timestamp_ms until down */
    private $downUntil = array();

    /** @var array Global config (connections + nodes) */
    private $config;

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * @param array $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get (or create) a PDO for a node.
     *
     * @param string $node
     * @return PDO
     */
    public function getPdo($node)
    {
        if (isset($this->pool[$node])) {
            return $this->pool[$node];
        }

        $nodeCfg = isset($this->config['nodes'][$node]) ? $this->config['nodes'][$node] : null;
        if (!$nodeCfg) {
            throw new \InvalidArgumentException("Unknown DB node: {$node}");
        }

        $dsn      = isset($nodeCfg['dsn']) ? $nodeCfg['dsn'] : '';
        $username = isset($nodeCfg['username']) ? $nodeCfg['username'] : null;
        $password = isset($nodeCfg['password']) ? $nodeCfg['password'] : null;
        $options  = isset($nodeCfg['options'])  ? $nodeCfg['options']  : array();

        $pdo = new PDO($dsn, $username, $password, $options);

        // Run connection init SQL (optional)
        if (isset($nodeCfg['init_sql']) && is_array($nodeCfg['init_sql'])) {
            foreach ($nodeCfg['init_sql'] as $sql) {
                $pdo->exec($sql);
            }
        }

        $this->pool[$node] = $pdo;
        return $pdo;
    }

    /**
     * Reset a node (drop from pool). Next access will create a new PDO.
     *
     * @param string $node
     * @return void
     */
    public function reset($node)
    {
        if (isset($this->pool[$node])) {
            unset($this->pool[$node]);
        }
    }

    /**
     * Mark a node as temporarily down.
     *
     * @param string $node
     * @param int $ms Down duration in milliseconds
     * @return void
     */
    public function markDown($node, $ms)
    {
        $nowMs = (int) floor(microtime(true) * 1000);
        $this->downUntil[$node] = $nowMs + (int)$ms;
        if ($this->logger) {
            $this->logger->warning('db.node.down', array('node' => $node, 'until_ms' => $this->downUntil[$node]));
        }
    }

    /**
     * Check if node is down (within window).
     *
     * @param string $node
     * @return bool
     */
    public function isDown($node)
    {
        if (!isset($this->downUntil[$node])) return false;
        $nowMs = (int) floor(microtime(true) * 1000);
        if ($this->downUntil[$node] <= $nowMs) {
            unset($this->downUntil[$node]);
            return false;
        }
        return true;
    }
}
