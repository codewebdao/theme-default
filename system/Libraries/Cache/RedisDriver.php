<?php
namespace System\Libraries\Cache;

/**
 * Redis Cache Driver
 * 
 * Production-ready Redis cache implementation with:
 * - Connection pooling
 * - Automatic reconnection
 * - Pipelining for batch operations
 * - Lua scripts for atomic operations
 * - Cluster support (future)
 * 
 * Requires: phpredis extension or predis library
 */
class RedisDriver implements CacheInterface
{
    /**
     * Redis connection
     * @var \Redis|\Predis\Client
     */
    private $redis;

    /**
     * Cache key prefix
     * @var string
     */
    private $prefix;

    /**
     * Redis connection config
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
     * Lua scripts cache
     * @var array
     */
    private $luaScripts = [];

    /**
     * Constructor
     *
     * @param array $config Configuration options
     * @throws \RuntimeException
     */
    public function __construct($config)
    {
        $this->config = array_merge([
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0,
            'timeout' => 5.0,
            'read_timeout' => 5.0,
            'persistent' => false,
            'prefix' => 'cmsff:',
            'retry_attempts' => 3,
            'retry_delay' => 100
        ], $config);

        $this->prefix = $this->config['prefix'];
        $this->retryAttempts = $this->config['retry_attempts'];
        $this->retryDelay = $this->config['retry_delay'];

        $this->connect();
        $this->loadLuaScripts();
    }

    /**
     * Establish Redis connection with retry logic
     *
     * @throws \RuntimeException
     */
    protected function connect()
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                // Check available drivers
                if (extension_loaded('redis')) {
                    $this->connectPhpRedis();
                    return;
                } elseif (class_exists('\\Predis\\Client')) {
                    $this->connectPredis();
                    return;
                } else {
                    throw new \RuntimeException('Redis extension or Predis library required');
                }
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
            "Redis connection failed after {$this->retryAttempts} attempts: " . 
            ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Connect using phpredis extension (faster)
     */
    protected function connectPhpRedis()
    {
        $this->redis = new \Redis();

        try {
            // Connect (persistent or regular)
            if ($this->config['persistent']) {
                $connected = $this->redis->pconnect(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['timeout'],
                    null,
                    0,
                    $this->config['read_timeout']
                );
            } else {
                $connected = $this->redis->connect(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['timeout'],
                    null,
                    0,
                    $this->config['read_timeout']
                );
            }

            if (!$connected) {
                throw new \RuntimeException('Redis connection failed');
            }

            // Authenticate if password provided
            if ($this->config['password']) {
                $this->redis->auth($this->config['password']);
            }

            // Select database
            $this->redis->select($this->config['database']);

            // Set serialization mode (PHP serializer)
            $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        } catch (\Exception $e) {
            throw new \RuntimeException('Redis connection error: ' . $e->getMessage());
        }
    }

    /**
     * Connect using Predis library
     */
    protected function connectPredis()
    {
        $this->redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'password' => $this->config['password'],
            'database' => $this->config['database'],
            'timeout' => $this->config['timeout'],
            'read_write_timeout' => $this->config['read_timeout'],
            'persistent' => $this->config['persistent']
        ]);

        try {
            $this->redis->connect();
        } catch (\Exception $e) {
            throw new \RuntimeException('Predis connection error: ' . $e->getMessage());
        }
    }

    /**
     * Get prefixed key for Redis
     *
     * @param string $key
     * @return string
     */
    protected function getRedisKey($key)
    {
        return $this->prefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $redisKey = $this->getRedisKey($key);

        try {
            $value = $this->redis->get($redisKey);

            if ($value === false || $value === null) {
                return null;
            }

            // phpredis auto-unserializes if OPT_SERIALIZER is set
            // Predis returns raw value
            if ($this->redis instanceof \Predis\Client) {
                $value = @unserialize($value);
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

        $redisKeys = array_map([$this, 'getRedisKey'], $keys);

        try {
            $values = $this->redis->mget($redisKeys);

            $results = [];
            foreach ($keys as $index => $key) {
                $value = $values[$index] ?? null;
                
                if ($value !== false && $value !== null) {
                    // Unserialize if needed
                    if ($this->redis instanceof \Predis\Client) {
                        $value = @unserialize($value);
                    }
                    $results[$key] = $value;
                } else {
                    $results[$key] = null;
                }
            }

            return $results;

        } catch (\Exception $e) {
            $this->handleException($e, 'mget');
            return array_fill_keys($keys, null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds)
    {
        $redisKey = $this->getRedisKey($key);

        try {
            // Serialize if using Predis
            if ($this->redis instanceof \Predis\Client) {
                $value = serialize($value);
            }

            if ($seconds > 0) {
                // Set with expiration
                return $this->redis->setex($redisKey, $seconds, $value);
            } else {
                // Set without expiration
                return $this->redis->set($redisKey, $value);
            }

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
            // Use pipeline for batch operations (more efficient)
            $pipe = $this->redis->pipeline();

            foreach ($values as $key => $value) {
                $redisKey = $this->getRedisKey($key);
                
                // Serialize if using Predis
                if ($this->redis instanceof \Predis\Client) {
                    $value = serialize($value);
                }

                if ($seconds > 0) {
                    $pipe->setex($redisKey, $seconds, $value);
                } else {
                    $pipe->set($redisKey, $value);
                }
            }

            $pipe->execute();
            return true;

        } catch (\Exception $e) {
            $this->handleException($e, 'putMany');
            return false;
        }
    }

    /**
     * {@inheritdoc}
     * 
     * Uses Lua script for atomic increment with TTL preservation
     * 
     * @param string $key
     * @param int $value Amount to increment
     * @param int|null $defaultTtl Optional TTL for new keys (if key doesn't exist)
     * @return int|bool New value or false on failure
     */
    public function increment($key, $value = 1, $defaultTtl = null)
    {
        $redisKey = $this->getRedisKey($key);

        try {
            // Use Lua script for atomic operation with TTL preservation
            if (isset($this->luaScripts['increment'])) {
                $args = [$value];
                // Add default TTL if provided (for new keys)
                if ($defaultTtl !== null) {
                    $args[] = $defaultTtl;
                }
                return $this->executeLua(
                    $this->luaScripts['increment'],
                    [$redisKey],
                    $args
                );
            }

            // Fallback to simple increment (may lose TTL)
            return $this->redis->incrBy($redisKey, $value);

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
        $redisKey = $this->getRedisKey($key);

        try {
            return $this->redis->decrBy($redisKey, $value);
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
        return $this->put($key, $value, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        $redisKey = $this->getRedisKey($key);

        try {
            return $this->redis->del($redisKey) > 0;
        } catch (\Exception $e) {
            $this->handleException($e, "forget key '{$key}'");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        try {
            // Use SCAN to find all keys with prefix (safer than KEYS)
            $keys = [];
            $iterator = null;
            $pattern = $this->prefix . '*';

            // Scan for keys with prefix
            do {
                $scanned = $this->redis->scan($iterator, $pattern, 1000);
                
                if ($scanned !== false) {
                    $keys = array_merge($keys, $scanned);
                }
            } while ($iterator > 0);

            // Delete in batches
            if (!empty($keys)) {
                $chunks = array_chunk($keys, 1000);
                foreach ($chunks as $chunk) {
                    $this->redis->del($chunk);
                }
            }

            return true;

        } catch (\Exception $e) {
            $this->handleException($e, 'flush');
            return false;
        }
    }

    /**
     * Get Redis connection statistics
     *
     * @return array
     */
    public function getStats()
    {
        try {
            $info = $this->redis->info();
            
            return [
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info)
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Calculate cache hit rate
     *
     * @param array $info
     * @return string
     */
    protected function calculateHitRate($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        return round(($hits / $total) * 100, 2) . '%';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Load and cache Lua scripts for atomic operations
     * Lua scripts run atomically on Redis server
     */
    protected function loadLuaScripts()
    {
        // Atomic increment with TTL preservation
        // ARGV[1] = amount to increment
        // ARGV[2] = optional default TTL for new keys
        $this->luaScripts['increment'] = <<<'LUA'
local key = KEYS[1]
local amount = tonumber(ARGV[1])
local default_ttl = ARGV[2] and tonumber(ARGV[2]) or nil
local ttl = redis.call('TTL', key)

if ttl == -2 then
    -- Key doesn't exist - set initial value with TTL if provided
    if default_ttl then
        redis.call('SETEX', key, default_ttl, amount)
    else
        redis.call('SET', key, amount)
    end
    return amount
elseif ttl == -1 then
    -- Key exists but no expiration - increment (preserve no expiration)
    return redis.call('INCRBY', key, amount)
else
    -- Key exists with TTL - increment and preserve TTL
    local result = redis.call('INCRBY', key, amount)
    redis.call('EXPIRE', key, ttl)
    return result
end
LUA;
    }

    /**
     * Execute Lua script with caching (EVALSHA for performance)
     *
     * @param string $script Lua script code
     * @param array $keys Redis keys
     * @param array $args Arguments
     * @return mixed
     */
    protected function executeLua($script, array $keys = [], array $args = [])
    {
        if ($this->redis instanceof \Redis) {
            // phpredis supports EVALSHA
            $sha1 = sha1($script);
            
            try {
                // Try cached script first
                return $this->redis->evalSha($sha1, array_merge($keys, $args), count($keys));
            } catch (\RedisException $e) {
                // Script not cached - eval and cache
                return $this->redis->eval($script, array_merge($keys, $args), count($keys));
            }
        } else {
            // Predis
            return $this->redis->eval($script, count($keys), ...array_merge($keys, $args));
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
        error_log("Redis cache error during {$operation}: " . $message);

        // Check if connection-related error
        $connectionErrors = [
            'Connection lost',
            'Connection refused',
            'Connection timeout',
            'Connection closed',
            'Broken pipe',
            'Connection reset'
        ];

        foreach ($connectionErrors as $errorPattern) {
            if (stripos($message, $errorPattern) !== false) {
                try {
                    // Attempt reconnection with retry logic
                    $this->connect();
                    error_log("Redis reconnection successful for {$operation}");
                    return;
                } catch (\Exception $reconnectException) {
                    error_log("Redis reconnection failed: " . $reconnectException->getMessage());
                    // Connection failed - operations will continue to fail
                    // Consider falling back to another cache driver
                }
                break;
            }
        }
    }

    /**
     * Close Redis connection
     */
    public function __destruct()
    {
        if ($this->redis && !$this->config['persistent']) {
            try {
                if ($this->redis instanceof \Redis) {
                    $this->redis->close();
                } elseif ($this->redis instanceof \Predis\Client) {
                    $this->redis->disconnect();
                }
            } catch (\Exception $e) {
                // Silent fail on close
            }
        }
    }
}
