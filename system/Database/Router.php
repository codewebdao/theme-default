<?php
namespace System\Database;

use System\Libraries\Logger\LoggerInterface;

/**
 * Router
 *
 * Chooses the actual "node" (read/write) for a query considering:
 * - intent (read vs write) inferred from SQL
 * - transaction depth (reads in tx -> write)
 * - sticky writes (read-after-write within sticky_ms -> write)
 * - force-write scope (explicitly route reads to write for a code block)
 * - read pool weighted round-robin + node down avoidance
 * - fallback to write if no read is available
 */
final class Router
{
    /** @var array */
    private $config;

    /** @var ConnectionManager */
    private $cm;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var array<string,int> connectionName => last write time (ms) */
    private $lastWriteAt = array();

    /** @var array<string,int> connectionName => tx depth */
    private $txDepth = array();

    /** @var array<string,int> connectionName => force-write depth */
    private $forceWriteDepth = array();

    /** @var array<string,string> connectionName => sticky read node for current request */
    private $stickyReadNode = array();

    /**
     * @param array $config
     * @param ConnectionManager $cm
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $config, ConnectionManager $cm, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->cm = $cm;
        $this->logger = $logger;
    }

    /**
     * Pick a node for the connection based on intent and sticky/tx/force-write flags.
     *
     * @param string $connectionName
     * @param string $intent 'read'|'write'
     * @return string node name (key in config['nodes'])
     */
    public function pickNode($connectionName, $intent)
    {
        $cx = isset($this->config['connections'][$connectionName]) ? $this->config['connections'][$connectionName] : null;
        if (!$cx) throw new \InvalidArgumentException("Unknown connection: {$connectionName}");

        $stickyMs = isset($cx['sticky_ms']) ? (int)$cx['sticky_ms'] : 0;
        $nowMs = (int) floor(microtime(true) * 1000);

        $stickyActive = isset($this->lastWriteAt[$connectionName]) && ($nowMs - $this->lastWriteAt[$connectionName] < $stickyMs);
        $inTx = isset($this->txDepth[$connectionName]) ? $this->txDepth[$connectionName] > 0 : false;
        $inForceWrite = isset($this->forceWriteDepth[$connectionName]) ? $this->forceWriteDepth[$connectionName] > 0 : false;

        // Route to write if: write intent, or in transaction, or sticky window, or force-write, or no read nodes
        if ($intent === 'write' || $inTx || $stickyActive || $inForceWrite || empty($cx['read'])) {
            return isset($cx['write']) ? $cx['write'] : (isset($cx['read'][0]) ? $cx['read'][0] : $this->requireNode());
        }

        // READ: Use sticky node for current request to minimize connections
        // Once we pick a read node, we stick to it for the entire request
        if (isset($this->stickyReadNode[$connectionName])) {
            $stickyNode = $this->stickyReadNode[$connectionName];
            // Verify it's still available (not down)
            if (!$this->cm->isDown($stickyNode) && in_array($stickyNode, $cx['read'])) {
                return $stickyNode;
            }
            // If sticky node is down, clear it and pick new one
            unset($this->stickyReadNode[$connectionName]);
        }

        // Pick a read node using weighted round-robin
        $candidates = $cx['read'];
        $weighted = array();
        foreach ($candidates as $node) {
            if ($this->cm->isDown($node)) continue;
            $weight = isset($this->config['nodes'][$node]['weight']) ? (int)$this->config['nodes'][$node]['weight'] : 1;
            $count = max(1, $weight);
            for ($i = 0; $i < $count; $i++) {
                $weighted[] = $node;
            }
        }
        if (!$weighted) {
            // fallback to write
            return isset($cx['write']) ? $cx['write'] : $this->requireNode();
        }

        // Pick randomly from weighted list
        $idx = random_int(0, count($weighted) - 1);
        $selectedNode = $weighted[$idx];
        
        // Store as sticky node for this request
        $this->stickyReadNode[$connectionName] = $selectedNode;
        
        return $selectedNode;
    }

    private function requireNode()
    {
        throw new \RuntimeException("No DB nodes configured/available");
    }

    /**
     * Get PDO for a node through ConnectionManager.
     *
     * @param string $node
     * @return \PDO
     */
    public function pdo($node)
    {
        return $this->cm->getPdo($node);
    }

    /** Mark a write event to activate sticky window. */
    public function markWrite($connectionName)
    {
        $this->lastWriteAt[$connectionName] = (int) floor(microtime(true) * 1000);
    }

    /* ===== Transaction scopes ===== */

    public function beginTx($connectionName)
    {
        if (!isset($this->txDepth[$connectionName])) $this->txDepth[$connectionName] = 0;
        $this->txDepth[$connectionName]++;
    }

    public function commitTx($connectionName)
    {
        if (!isset($this->txDepth[$connectionName])) return;
        $this->txDepth[$connectionName] = max(0, $this->txDepth[$connectionName] - 1);
    }

    public function rollbackTx($connectionName)
    {
        $this->txDepth[$connectionName] = 0;
    }

    public function inTx($connectionName)
    {
        return isset($this->txDepth[$connectionName]) ? ($this->txDepth[$connectionName] > 0) : false;
    }

    /* ===== Force-write scopes (for read-after-write sensitive code) ===== */

    public function beginForceWrite($connectionName)
    {
        if (!isset($this->forceWriteDepth[$connectionName])) $this->forceWriteDepth[$connectionName] = 0;
        $this->forceWriteDepth[$connectionName]++;
    }

    public function endForceWrite($connectionName)
    {
        if (!isset($this->forceWriteDepth[$connectionName])) return;
        $this->forceWriteDepth[$connectionName] = max(0, $this->forceWriteDepth[$connectionName] - 1);
    }

    public function inForceWrite($connectionName)
    {
        return isset($this->forceWriteDepth[$connectionName]) ? ($this->forceWriteDepth[$connectionName] > 0) : false;
    }

    /** Expose ConnectionManager (for marking nodes etc.) */
    public function cm()
    {
        return $this->cm;
    }
}
