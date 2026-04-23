<?php
namespace System\Libraries;

/**
 * Cache Library - Laravel-style caching
 * 
 * Production-ready cache system with:
 * - Multiple drivers (file, redis, memcached)
 * - Laravel-compatible API
 * - Automatic garbage collection
 * - Safe serialization/unserialization
 * - Connection pooling
 * - Error handling and logging
 * 
 * @example
 * Cache::put('key', 'value', 600);
 * $value = Cache::get('key', 'default');
 * $users = Cache::remember('users', 3600, fn() => DB::query(...));
 */
class Cache
{
    /**
     * Cache driver instances (singleton per store)
     * @var array
     */
    private static $drivers = [];

    /**
     * Cache configuration
     * @var array|null
     */
    private static $config = null;
    
    /**
     * Initialize cache configuration
     */
    private static function init()
    {
        if (self::$config === null) {
            // Try to load from config file
            $loadedConfig = config(null, 'Cache');
            
            // Fallback to default config if not loaded
            if (empty($loadedConfig) || !isset($loadedConfig['stores'])) {
                self::$config = self::getDefaultConfig();
            } else {
                // Merge with defaults to ensure all required keys exist
                self::$config = array_merge(self::getDefaultConfig(), $loadedConfig);
                
                // Ensure stores is merged
                if (isset($loadedConfig['stores'])) {
                    self::$config['stores'] = array_merge(
                        self::getDefaultConfig()['stores'],
                        $loadedConfig['stores']
                    );
                }
            }
        }
    }

    /**
     * Get default cache configuration (fallback)
     *
     * @return array
     */
    private static function getDefaultConfig()
    {
        $cachePath = defined('PATH_WRITE') ? PATH_WRITE . '/cache' : sys_get_temp_dir() . '/cache/';

        return [
            'default' => 'file',
            'prefix' => 'cmsff:',
            'stores' => [
                'file' => [
                    'driver' => 'file',
                    'path' => $cachePath,
                    'prefix' => 'cmsff:'
                ],
                'redis' => [
                    'driver' => 'redis',
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'password' => null,
                    'database' => 0,
                    'timeout' => 5.0,
                    'persistent' => false,
                    'prefix' => 'cmsff:'
                ],
                // 'memcached' => [
                //     'driver' => 'memcached',
                //     'servers' => [
                //         ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 0]
                //     ],
                //     'prefix' => 'cmsff:',
                //     'compression' => true,
                //     'serializer' => \Memcached::SERIALIZER_PHP,
                //     'binary_protocol' => true
                // ],
                'array' => [
                    'driver' => 'array',
                    'prefix' => 'cmsff:'
                ]
            ],
            'limiter' => 'file'
        ];
    }
    
    /**
     * Get cache driver instance (singleton)
     *
     * @param string|null $store Store name
     * @return \System\Libraries\Cache\CacheInterface
     * @throws \RuntimeException
     */
    public static function getDriver($store = null)
    {
        self::init();
        
        $storeName = $store ?? self::$config['default'];
        
        // Return cached driver instance
        if (isset(self::$drivers[$storeName])) {
            return self::$drivers[$storeName];
        }
        
        // Get store configuration
        if (!isset(self::$config['stores'][$storeName])) {
            // Fallback to default file store if requested store not found
            error_log("Cache store '{$storeName}' not configured, falling back to file store");
            $storeName = 'file';
            
            // If file store also doesn't exist, use hardcoded default
            if (!isset(self::$config['stores']['file'])) {
                $storeConfig = self::getDefaultConfig()['stores']['file'];
            } else {
                $storeConfig = self::$config['stores']['file'];
            }
        } else {
            $storeConfig = self::$config['stores'][$storeName];
        }
        
        // Ensure prefix is set (use global prefix from config)
        $storeConfig['prefix'] = $storeConfig['prefix'] ?? self::$config['prefix'] ?? 'cmsff';     
        // Ensure prefix ends with colon for consistency
        if (substr($storeConfig['prefix'], -1) !== ':') {
            $storeConfig['prefix'] .= ':';
        }
        
        // Get driver name
        $driverName = $storeConfig['driver'] ?? 'file';
        
        // Instantiate driver
        $driver = null;
        
        switch ($driverName) {
            case 'file':
                $driver = new \System\Libraries\Cache\FileDriver($storeConfig);
                break;
                
            case 'redis':
                $driver = new \System\Libraries\Cache\RedisDriver($storeConfig);
                break;
                
            case 'memcached':
                $driver = new \System\Libraries\Cache\MemcachedDriver($storeConfig);
                break;
                
            case 'array':
                $driver = new \System\Libraries\Cache\ArrayDriver($storeConfig);
                break;
                
            default:
                throw new \RuntimeException("Unsupported cache driver: {$driverName}");
        }
        
        // Cache driver instance
        self::$drivers[$storeName] = $driver;
        
        return $driver;
    }
    
    /**
     * Retrieve an item from the cache by key
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found (can be closure)
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        try {
            $driver = self::getDriver();
            $value = $driver->get($key);
            
            if ($value === null && is_callable($default)) {
                return $default();
            }
            
            return $value ?? $default;
        } catch (\Exception $e) {
            error_log("Cache::get error for key '{$key}': " . $e->getMessage());
            return is_callable($default) ? $default() : $default;
        }
    }

    /**
     * Retrieve multiple items from the cache by key
     *
     * @param array $keys
     * @return array Key-value pairs
     */
    public static function many(array $keys)
    {
        try {
            $driver = self::getDriver();
            return $driver->many($keys);
        } catch (\Exception $e) {
            error_log("Cache::many error: " . $e->getMessage());
            return array_fill_keys($keys, null);
        }
    }
    
    /**
     * Store an item in the cache for a given number of seconds
     * 
     * @param string|array $key Cache key or array of key-value pairs
     * @param mixed $value Value to store (if key is string)
     * @param int $seconds TTL in seconds (default: 3600 = 1 hour)
     * @return bool
     */
    public static function put($key, $value = null, $seconds = 3600)
    {
        try {
            // Handle array of key-value pairs
            if (is_array($key)) {
                return self::putMany($key, $value ?? 3600); // $value is actually $seconds
            }

            $driver = self::getDriver();
            return $driver->put($key, $value, $seconds);
        } catch (\Exception $e) {
            error_log("Cache::put error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store multiple items in the cache
     *
     * @param array $values Key-value pairs
     * @param int $seconds TTL in seconds
     * @return bool
     */
    public static function putMany(array $values, $seconds)
    {
        try {
            $driver = self::getDriver();
            return $driver->putMany($values, $seconds);
        } catch (\Exception $e) {
            error_log("Cache::putMany error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get an item from the cache, or execute callback and store result
     * 
     * @param string $key Cache key
     * @param int $seconds TTL in seconds
     * @param callable $callback Function to generate value if not cached
     * @return mixed
     */
    public static function remember($key, $seconds, $callback)
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        // Execute callback and cache result
        $value = $callback();
        self::put($key, $value, $seconds);
        
        return $value;
    }

    /**
     * Get an item from the cache, or execute callback and store forever
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public static function rememberForever($key, $callback)
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::forever($key, $value);
        
        return $value;
    }

    /**
     * Get an item from the cache, or execute callback (without storing)
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public static function sear($key, $callback)
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        return $callback();
    }
    
    /**
     * Determine if an item exists in the cache
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return self::get($key) !== null;
    }

    /**
     * Determine if an item doesn't exist in the cache
     *
     * @param string $key
     * @return bool
     */
    public static function missing($key)
    {
        return !self::has($key);
    }
    
    /**
     * Store an item in the cache if the key does not exist
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public static function add($key, $value, $seconds)
    {
        if (!self::has($key)) {
            return self::put($key, $value, $seconds);
        }
        return false;
    }
    
    /**
     * Increment the value of an item in the cache
     *
     * @param string $key
     * @param int $amount
     * @param int|null $defaultTtl Optional TTL for new keys (Redis/Memcached only)
     * @return int|bool New value or false on failure
     */
    public static function increment($key, $amount = 1, $defaultTtl = null)
    {
        try {
            $driver = self::getDriver();
            // Only pass TTL if driver supports it (Redis, Memcached)
            if ($defaultTtl !== null && method_exists($driver, 'increment')) {
                // Use reflection to check if increment accepts 3 parameters
                $reflection = new \ReflectionMethod($driver, 'increment');
                if ($reflection->getNumberOfParameters() >= 3) {
                    return $driver->increment($key, $amount, $defaultTtl);
                }
            }
            return $driver->increment($key, $amount);
        } catch (\Exception $e) {
            error_log("Cache::increment error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrement the value of an item in the cache
     *
     * @param string $key
     * @param int $amount
     * @return int|bool New value or false on failure
     */
    public static function decrement($key, $amount = 1)
    {
        try {
            $driver = self::getDriver();
            return $driver->decrement($key, $amount);
        } catch (\Exception $e) {
            error_log("Cache::decrement error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Store an item in the cache indefinitely
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function forever($key, $value)
    {
        try {
            $driver = self::getDriver();
            return $driver->forever($key, $value);
        } catch (\Exception $e) {
            error_log("Cache::forever error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove an item from the cache
     *
     * @param string $key
     * @return bool
     */
    public static function forget($key)
    {
        try {
            $driver = self::getDriver();
            return $driver->forget($key);
        } catch (\Exception $e) {
            error_log("Cache::forget error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve an item from the cache and delete it
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull($key, $default = null)
    {
        $value = self::get($key, $default);
        self::forget($key);
        return $value;
    }
    
    /**
     * Remove all items from the cache
     *
     * @return bool
     */
    public static function flush()
    {
        try {
            $driver = self::getDriver();
            return $driver->flush();
        } catch (\Exception $e) {
            error_log("Cache::flush error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the cache key prefix
     *
     * @return string
     */
    public static function getPrefix()
    {
        $driver = self::getDriver();
        return $driver->getPrefix();
    }
    
    /**
     * Begin interacting with a specific cache store
     *
     * @param string $name Store name
     * @return \System\Libraries\CacheManager
     */
    public static function store($name)
    {
        return new CacheManager($name);
    }

    /**
     * Run garbage collection on current driver
     * Remove expired items
     *
     * @return int|null Number of items removed (if supported)
     */
    public static function gc()
    {
        try {
            $driver = self::getDriver();
            
            if (method_exists($driver, 'gc')) {
                return $driver->gc();
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Cache::gc error: " . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Cache Manager - Allows using specific cache store
 * 
 * Complete implementation with all Cache methods
 * Delegates to specific store driver
 * 
 * @example Cache::store('redis')->put('key', 'value', 600);
 */
class CacheManager
{
    private $storeName;
    
    public function __construct($storeName)
    {
        $this->storeName = $storeName;
    }
    
    /**
     * Get cache driver for this store
     *
     * @return \System\Libraries\Cache\CacheInterface
     */
    private function driver()
    {
        return Cache::getDriver($this->storeName);
    }
    
    public function get($key, $default = null)
    {
        $value = $this->driver()->get($key);
        return $value ?? (is_callable($default) ? $default() : $default);
    }
    
    public function many(array $keys)
    {
        return $this->driver()->many($keys);
    }
    
    public function put($key, $value, $seconds = 3600)
    {
        return $this->driver()->put($key, $value, $seconds);
    }
    
    public function putMany(array $values, $seconds)
    {
        return $this->driver()->putMany($values, $seconds);
    }
    
    public function has($key)
    {
        return $this->get($key) !== null;
    }
    
    public function missing($key)
    {
        return !$this->has($key);
    }
    
    public function add($key, $value, $seconds)
    {
        if (!$this->has($key)) {
            return $this->put($key, $value, $seconds);
        }
        return false;
    }
    
    public function remember($key, $seconds, $callback)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->put($key, $value, $seconds);
        
        return $value;
    }
    
    public function rememberForever($key, $callback)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->forever($key, $value);
        
        return $value;
    }
    
    public function forever($key, $value)
    {
        return $this->driver()->forever($key, $value);
    }
    
    public function forget($key)
    {
        return $this->driver()->forget($key);
    }
    
    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }
    
    public function flush()
    {
        return $this->driver()->flush();
    }
    
    public function increment($key, $amount = 1, $defaultTtl = null)
    {
        $driver = $this->driver();
        // Only pass TTL if driver supports it
        if ($defaultTtl !== null && method_exists($driver, 'increment')) {
            $reflection = new \ReflectionMethod($driver, 'increment');
            if ($reflection->getNumberOfParameters() >= 3) {
                return $driver->increment($key, $amount, $defaultTtl);
            }
        }
        return $driver->increment($key, $amount);
    }
    
    public function decrement($key, $amount = 1)
    {
        return $this->driver()->decrement($key, $amount);
    }
    
    public function getPrefix()
    {
        return $this->driver()->getPrefix();
    }
    
    public function gc()
    {
        $driver = $this->driver();
        if (method_exists($driver, 'gc')) {
            return $driver->gc();
        }
        return null;
    }
}
