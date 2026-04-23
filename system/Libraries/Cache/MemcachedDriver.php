<?php
namespace System\Libraries\Cache;

/**
 * Memcached Cache Driver
 * 
 * Production-ready Memcached cache implementation with:
 * - Connection pooling
 * - Automatic reconnection
 * - Multi-server support
 * - SASL authentication support
 * - Compression support
 * 
 * Requires: memcached extension (PHP 7.4+)
 * 
 * Note: Uses 'memcached' extension (not 'memcache' - that's the old extension)
 */
class MemcachedDriver implements CacheInterface
{
    /**
     * Memcached connection
     * @var \Memcached
     */
    private $memcached;

    /**
     * Cache key prefix
     * @var string
     */
    private $prefix;

    /**
     * Memcached connection config
     * @var array
     */
    private $config;

    /**
     * Connection retry attempts
     * @var int
     */
    private $retryAttempts = 3;

    /**
     * Connection retry delay (milliseconds)
     * @var int
     */
    private $retryDelay = 100;

    /**
     * Constructor
     *
     * @param array $config Configuration options
     * @throws \RuntimeException
     */
    public function __construct($config)
    {
        if (!extension_loaded('memcached')) {
            throw new \RuntimeException('Memcached extension is not loaded. Install it with: pecl install memcached');
        }

        $this->config = array_merge([
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 0]
            ],
            'options' => [],
            'prefix' => 'cmsff:',
            'retry_attempts' => 3,
            'retry_delay' => 100,
            'compression' => true,
            'serializer' => \Memcached::SERIALIZER_PHP,
            'binary_protocol' => true,
            'sasl_auth' => false,
            'sasl_user' => null,
            'sasl_pass' => null
        ], $config);

        // Handle single server config (backward compatibility)
        if (isset($config['host']) && !isset($config['servers'])) {
            $this->config['servers'] = [[
                'host' => $config['host'],
                'port' => $config['port'] ?? 11211,
                'weight' => $config['weight'] ?? 0
            ]];
        }

        $this->prefix = $this->config['prefix'];
        $this->retryAttempts = $this->config['retry_attempts'];
        $this->retryDelay = $this->config['retry_delay'];

        $this->connect();
    }

    /**
     * Establish Memcached connection with retry logic
     *
     * @throws \RuntimeException
     */
    protected function connect()
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $this->memcached = new \Memcached();
                
                // Set options
                $this->configureOptions();
                
                // Add servers
                $this->addServers();
                
                // SASL authentication if configured
                if ($this->config['sasl_auth'] && $this->config['sasl_user'] && $this->config['sasl_pass']) {
                    $this->memcached->setSaslAuthData($this->config['sasl_user'], $this->config['sasl_pass']);
                }
                
                // Test connection
                $this->testConnection();
                
                return;
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->retryAttempts) {
                    // Wait before retry (exponential backoff)
                    usleep($this->retryDelay * 1000 * $attempt);
                }
            }
        }

        // All attempts failed
        throw new \RuntimeException(
            "Memcached connection failed after {$this->retryAttempts} attempts: " . 
            ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Configure Memcached options
     */
    protected function configureOptions()
    {
        // Set serializer
        $this->memcached->setOption(\Memcached::OPT_SERIALIZER, $this->config['serializer']);
        
        // Enable binary protocol (faster)
        if ($this->config['binary_protocol']) {
            $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
        }
        
        // Compression
        if ($this->config['compression']) {
            $this->memcached->setOption(\Memcached::OPT_COMPRESSION, true);
        }
        
        // Connection timeout
        if (isset($this->config['timeout'])) {
            $this->memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->config['timeout'] * 1000);
        }
        
        // Retry timeout
        if (isset($this->config['retry_timeout'])) {
            $this->memcached->setOption(\Memcached::OPT_RETRY_TIMEOUT, $this->config['retry_timeout']);
        }
        
        // Distribution (consistent hashing)
        $this->memcached->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
        $this->memcached->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        
        // Custom options from config
        if (!empty($this->config['options']) && is_array($this->config['options'])) {
            foreach ($this->config['options'] as $option => $value) {
                if (defined($option)) {
                    $this->memcached->setOption(constant($option), $value);
                }
            }
        }
    }

    /**
     * Add servers to Memcached
     */
    protected function addServers()
    {
        $servers = [];
        foreach ($this->config['servers'] as $server) {
            $servers[] = [
                $server['host'],
                $server['port'],
                $server['weight'] ?? 0
            ];
        }
        
        $this->memcached->addServers($servers);
    }

    /**
     * Test connection by getting server list
     *
     * @throws \RuntimeException
     */
    protected function testConnection()
    {
        $servers = $this->memcached->getServerList();
        
        if (empty($servers)) {
            throw new \RuntimeException('No Memcached servers available');
        }
        
        // Try a simple operation to verify connection
        $testKey = $this->prefix . '__connection_test__';
        $this->memcached->set($testKey, 'test', 1);
        $this->memcached->delete($testKey);
    }

    /**
     * Get prefixed key for Memcached
     *
     * @param string $key
     * @return string
     */
    protected function getMemcachedKey($key)
    {
        return $this->prefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $memcachedKey = $this->getMemcachedKey($key);

        try {
            $value = $this->memcached->get($memcachedKey);
            
            // Memcached returns false on miss, null on error
            if ($value === false) {
                $resultCode = $this->memcached->getResultCode();
                
                // MEMCACHED_NOTFOUND is normal (key doesn't exist)
                if ($resultCode === \Memcached::RES_NOTFOUND) {
                    return null;
                }
                
                // Other errors
                if ($resultCode !== \Memcached::RES_SUCCESS) {
                    $this->handleError("get key '{$key}'", $resultCode);
                    return null;
                }
            }

            return $value;

        } catch (\Exception $e) {
            $this->handleException($e, "get key '{$key}'");
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function many(array $keys)
    {
        if (empty($keys)) {
            return [];
        }

        $memcachedKeys = array_map([$this, 'getMemcachedKey'], $keys);

        try {
            // Use getMulti for batch retrieval (more efficient)
            $values = $this->memcached->getMulti($memcachedKeys);

            $results = [];
            foreach ($keys as $index => $key) {
                $memcachedKey = $memcachedKeys[$index];
                $results[$key] = $values[$memcachedKey] ?? null;
            }

            return $results;

        } catch (\Exception $e) {
            $this->handleException($e, 'getMulti');
            return array_fill_keys($keys, null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds)
    {
        $memcachedKey = $this->getMemcachedKey($key);

        try {
            // Memcached TTL: max 30 days (2592000 seconds)
            // If seconds > 30 days, use timestamp instead
            if ($seconds > 2592000) {
                $expiration = time() + $seconds;
            } else {
                $expiration = $seconds;
            }

            $result = $this->memcached->set($memcachedKey, $value, $expiration);

            if (!$result) {
                $this->handleError("put key '{$key}'", $this->memcached->getResultCode());
            }

            return $result;

        } catch (\Exception $e) {
            $this->handleException($e, "put key '{$key}'");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function putMany(array $values, $seconds)
    {
        if (empty($values)) {
            return true;
        }

        try {
            // Memcached TTL: max 30 days
            if ($seconds > 2592000) {
                $expiration = time() + $seconds;
            } else {
                $expiration = $seconds;
            }

            $prefixedValues = [];
            foreach ($values as $key => $value) {
                $prefixedValues[$this->getMemcachedKey($key)] = $value;
            }

            $result = $this->memcached->setMulti($prefixedValues, $expiration);

            if (!$result) {
                $this->handleError('putMany', $this->memcached->getResultCode());
            }

            return $result;

        } catch (\Exception $e) {
            $this->handleException($e, 'putMany');
            return false;
        }
    }

    /**
     * {@inheritdoc}
     * 
     * Memcached has native increment support
     * 
     * @param string $key
     * @param int $value Amount to increment
     * @param int|null $defaultTtl Optional TTL for new keys (if key doesn't exist)
     * @return int|bool New value or false on failure
     */
    public function increment($key, $value = 1, $defaultTtl = null)
    {
        $memcachedKey = $this->getMemcachedKey($key);

        try {
            // Memcached increment returns false if key doesn't exist
            // We need to initialize it first if it doesn't exist
            $result = $this->memcached->increment($memcachedKey, $value);
            
            if ($result === false) {
                $resultCode = $this->memcached->getResultCode();
                
                if ($resultCode === \Memcached::RES_NOTFOUND) {
                    // Key doesn't exist - initialize it with TTL if provided
                    $expiration = 0; // Forever by default
                    if ($defaultTtl !== null) {
                        // Memcached TTL: max 30 days
                        if ($defaultTtl > 2592000) {
                            $expiration = time() + $defaultTtl;
                        } else {
                            $expiration = $defaultTtl;
                        }
                    }
                    $this->memcached->set($memcachedKey, $value, $expiration);
                    return $value;
                }
                
                $this->handleError("increment key '{$key}'", $resultCode);
                return false;
            }

            return $result;

        } catch (\Exception $e) {
            $this->handleException($e, "increment key '{$key}'");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        $memcachedKey = $this->getMemcachedKey($key);

        try {
            $result = $this->memcached->decrement($memcachedKey, $value);
            
            if ($result === false) {
                $resultCode = $this->memcached->getResultCode();
                
                if ($resultCode === \Memcached::RES_NOTFOUND) {
                    // Key doesn't exist - initialize it with 0
                    $this->memcached->set($memcachedKey, 0, 0);
                    return 0;
                }
                
                $this->handleError("decrement key '{$key}'", $resultCode);
                return false;
            }

            return $result;

        } catch (\Exception $e) {
            $this->handleException($e, "decrement key '{$key}'");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value)
    {
        // Memcached doesn't support "forever" - use max TTL (30 days)
        // Or use 0 which means "no expiration" (but may be evicted by LRU)
        return $this->put($key, $value, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        $memcachedKey = $this->getMemcachedKey($key);

        try {
            $result = $this->memcached->delete($memcachedKey);
            
            // Memcached returns false if key doesn't exist, but that's OK
            $resultCode = $this->memcached->getResultCode();
            
            if ($resultCode === \Memcached::RES_NOTFOUND) {
                return true; // Key already deleted, consider it success
            }
            
            return $result;

        } catch (\Exception $e) {
            $this->handleException($e, "forget key '{$key}'");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     * 
     * Note: Memcached doesn't support flushing all keys with prefix
     * This will flush ALL keys in the Memcached server (use with caution!)
     */
    public function flush()
    {
        try {
            // Memcached flush() flushes ALL keys on ALL servers
            // There's no way to flush only keys with a specific prefix
            // This is a limitation of Memcached protocol
            $result = $this->memcached->flush();
            
            if (!$result) {
                $this->handleError('flush', $this->memcached->getResultCode());
            }
            
            return $result;

        } catch (\Exception $e) {
            $this->handleException($e, 'flush');
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get Memcached connection statistics
     *
     * @return array
     */
    public function getStats()
    {
        try {
            $stats = $this->memcached->getStats();
            
            if (empty($stats)) {
                return ['error' => 'No stats available'];
            }
            
            // Aggregate stats from all servers
            $aggregated = [
                'servers' => count($stats),
                'total_items' => 0,
                'total_connections' => 0,
                'get_hits' => 0,
                'get_misses' => 0,
                'bytes' => 0
            ];
            
            foreach ($stats as $server => $serverStats) {
                $aggregated['total_items'] += $serverStats['curr_items'] ?? 0;
                $aggregated['total_connections'] += $serverStats['total_connections'] ?? 0;
                $aggregated['get_hits'] += $serverStats['get_hits'] ?? 0;
                $aggregated['get_misses'] += $serverStats['get_misses'] ?? 0;
                $aggregated['bytes'] += $serverStats['bytes'] ?? 0;
            }
            
            $totalGets = $aggregated['get_hits'] + $aggregated['get_misses'];
            $aggregated['hit_rate'] = $totalGets > 0 
                ? round(($aggregated['get_hits'] / $totalGets) * 100, 2) . '%'
                : '0%';
            
            return $aggregated;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Handle Memcached errors
     *
     * @param string $operation
     * @param int $resultCode
     */
    protected function handleError($operation, $resultCode)
    {
        $errorMessages = [
            \Memcached::RES_SUCCESS => 'Success',
            \Memcached::RES_FAILURE => 'Failure',
            \Memcached::RES_HOST_LOOKUP_FAILURE => 'Host lookup failure',
            \Memcached::RES_CONNECTION_FAILURE => 'Connection failure',
            \Memcached::RES_WRITE_FAILURE => 'Write failure',
            \Memcached::RES_READ_FAILURE => 'Read failure',
            \Memcached::RES_UNKNOWN_READ_FAILURE => 'Unknown read failure',
            \Memcached::RES_PROTOCOL_ERROR => 'Protocol error',
            \Memcached::RES_CLIENT_ERROR => 'Client error',
            \Memcached::RES_SERVER_ERROR => 'Server error',
            \Memcached::RES_NOTFOUND => 'Not found',
            \Memcached::RES_NOTSTORED => 'Not stored',
            \Memcached::RES_PARTIAL_READ => 'Partial read',
            \Memcached::RES_SOME_ERRORS => 'Some errors',
            \Memcached::RES_NO_SERVERS => 'No servers',
            \Memcached::RES_END => 'End',
            \Memcached::RES_ERRNO => 'Errno',
            \Memcached::RES_BUFFERED => 'Buffered',
            \Memcached::RES_TIMEOUT => 'Timeout',
            \Memcached::RES_BAD_KEY_PROVIDED => 'Bad key provided',
            \Memcached::RES_STORED => 'Stored',
            \Memcached::RES_DELETED => 'Deleted',
            \Memcached::RES_STAT => 'Stat',
            \Memcached::RES_ITEM => 'Item',
            \Memcached::RES_NOT_SUPPORTED => 'Not supported',
            \Memcached::RES_FETCH_NOTFINISHED => 'Fetch not finished',
            \Memcached::RES_INVALID_HOST_PROTOCOL => 'Invalid host protocol',
            \Memcached::RES_MEMORY_ALLOCATION_FAILURE => 'Memory allocation failure',
            \Memcached::RES_CONNECTION_BIND_FAILURE => 'Connection bind failure',
            \Memcached::RES_ERRNO => 'Errno',
            \Memcached::RES_NOT_SUPPORTED => 'Not supported',
            \Memcached::RES_FETCH_NOTFINISHED => 'Fetch not finished',
            \Memcached::RES_INVALID_HOST_PROTOCOL => 'Invalid host protocol',
            \Memcached::RES_MEMORY_ALLOCATION_FAILURE => 'Memory allocation failure',
            \Memcached::RES_CONNECTION_BIND_FAILURE => 'Connection bind failure',
            \Memcached::RES_AUTH_PROBLEM => 'Authentication problem',
            \Memcached::RES_AUTH_FAILURE => 'Authentication failure',
            \Memcached::RES_AUTH_CONTINUE => 'Authentication continue',
            \Memcached::RES_PARSE_ERROR => 'Parse error',
            \Memcached::RES_PARSE_USER_ERROR => 'Parse user error',
            \Memcached::RES_DEPRECATED => 'Deprecated',
            \Memcached::RES_IN_PROGRESS => 'In progress',
            \Memcached::RES_SERVER_TEMPORARILY_DISABLED => 'Server temporarily disabled',
            \Memcached::RES_SERVER_MEMORY_ALLOCATION_FAILURE => 'Server memory allocation failure',
            \Memcached::RES_UNKNOWN_STAT_KEY => 'Unknown stat key',
            \Memcached::RES_E2BIG => 'E2BIG',
        ];

        $errorMessage = $errorMessages[$resultCode] ?? "Unknown error (code: {$resultCode})";
        
        // Only log non-normal errors
        if ($resultCode !== \Memcached::RES_SUCCESS && 
            $resultCode !== \Memcached::RES_NOTFOUND && 
            $resultCode !== \Memcached::RES_STORED &&
            $resultCode !== \Memcached::RES_DELETED) {
            error_log("Memcached cache error during {$operation}: {$errorMessage} (code: {$resultCode})");
        }
    }

    /**
     * Handle exceptions and attempt reconnection
     *
     * @param \Exception $e
     * @param string $operation
     */
    protected function handleException(\Exception $e, $operation)
    {
        $message = $e->getMessage();
        error_log("Memcached cache exception during {$operation}: " . $message);

        // Check if connection-related error
        $connectionErrors = [
            'Connection lost',
            'Connection refused',
            'Connection timeout',
            'Connection closed',
            'Broken pipe',
            'Connection reset',
            'No servers available'
        ];

        foreach ($connectionErrors as $errorPattern) {
            if (stripos($message, $errorPattern) !== false) {
                try {
                    // Attempt reconnection with retry logic
                    $this->connect();
                    error_log("Memcached reconnection successful for {$operation}");
                    return;
                } catch (\Exception $reconnectException) {
                    error_log("Memcached reconnection failed: " . $reconnectException->getMessage());
                }
                break;
            }
        }
    }

    /**
     * Close Memcached connection
     */
    public function __destruct()
    {
        if ($this->memcached) {
            try {
                $this->memcached->quit();
            } catch (\Exception $e) {
                // Silent fail on close
            }
        }
    }
}

