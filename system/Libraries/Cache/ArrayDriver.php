<?php
namespace System\Libraries\Cache;

/**
 * Array Cache Driver
 * 
 * In-memory cache driver for per-request caching
 * Uses PHP arrays stored in static class property
 * 
 * Use cases:
 * - Shared cache between multiple functions
 * - Namespace protection (prevents naming conflicts)
 * - Plugin/theme safe (isolated with prefix)
 * - Debugging and tracking cache keys
 * 
 * Performance: Very fast (array access), minimal overhead
 * 
 * TTL Note: TTL is optional and rarely needed for per-request cache
 * (requests typically last < 1 minute). TTL is useful for:
 * - Long-running CLI commands
 * - Queue workers
 * - Background jobs
 * 
 * Note: Data is lost when request ends (per-request only)
 * For cross-request persistence, use File/Redis/Memcached drivers
 */
class ArrayDriver implements CacheInterface
{
    /**
     * In-memory cache storage
     * Format: ['prefix:key' => ['value' => mixed, 'expires' => int|null]]
     * 
     * @var array
     */
    private static $cache = [];

    /**
     * Cache key prefix
     * @var string
     */
    private $prefix;

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct($config)
    {
        // Use prefix from config (shared with other drivers)
        // No need for separate prefix - config['prefix'] is sufficient
        $this->prefix = $config['prefix'] ?? 'cmsff:';
        
        // Ensure prefix ends with colon for consistency
        if (substr($this->prefix, -1) !== ':') {
            $this->prefix .= ':';
        }
    }

    /**
     * Get prefixed key
     *
     * @param string $key
     * @return string
     */
    private function getKey($key)
    {
        return $this->prefix . $key;
    }

    /**
     * Check if key is expired
     * 
     * Optimized: Only check expiration if expires is set
     * Most per-request cache items don't need TTL
     *
     * @param string $prefixedKey
     * @param array $item
     * @return bool
     */
    private function isExpired($prefixedKey, $item)
    {
        // Fast path: No expiration set (most common case)
        if (!isset($item['expires']) || $item['expires'] === null) {
            return false;
        }

        // Check expiration only if set (for long-running scripts)
        if ($item['expires'] > 0 && $item['expires'] < time()) {
            // Expired - remove from cache
            unset(self::$cache[$prefixedKey]);
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $prefixedKey = $this->getKey($key);

        if (!isset(self::$cache[$prefixedKey])) {
            return null;
        }

        $item = self::$cache[$prefixedKey];

        // Check expiration
        if ($this->isExpired($prefixedKey, $item)) {
            return null;
        }

        return $item['value'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function many(array $keys)
    {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds)
    {
        $prefixedKey = $this->getKey($key);

        $item = [
            'value' => $value,
            'expires' => null
        ];

        // Set expiration if TTL provided
        if ($seconds > 0) {
            $item['expires'] = time() + $seconds;
        }

        self::$cache[$prefixedKey] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function putMany(array $values, $seconds)
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $seconds);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        $current = $this->get($key);

        if ($current === null) {
            // Key doesn't exist - initialize
            $newValue = $value;
        } else {
            // Increment existing value
            if (!is_numeric($current)) {
                $current = 0;
            }
            $newValue = $current + $value;
        }

        // Get expiration from existing item if any
        $prefixedKey = $this->getKey($key);
        $expires = null;
        if (isset(self::$cache[$prefixedKey])) {
            $expires = self::$cache[$prefixedKey]['expires'] ?? null;
        }

        // Store new value with same expiration
        self::$cache[$prefixedKey] = [
            'value' => $newValue,
            'expires' => $expires
        ];

        return $newValue;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, -$value);
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
        $prefixedKey = $this->getKey($key);

        if (isset(self::$cache[$prefixedKey])) {
            unset(self::$cache[$prefixedKey]);
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        // Only flush keys with this prefix
        $prefixLength = strlen($this->prefix);
        
        foreach (array_keys(self::$cache) as $key) {
            if (substr($key, 0, $prefixLength) === $this->prefix) {
                unset(self::$cache[$key]);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get all cache keys (for debugging)
     * Returns keys without prefix
     *
     * @return array
     */
    public function getAllKeys()
    {
        $keys = [];
        $prefixLength = strlen($this->prefix);

        foreach (array_keys(self::$cache) as $prefixedKey) {
            if (substr($prefixedKey, 0, $prefixLength) === $this->prefix) {
                $key = substr($prefixedKey, $prefixLength);
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Get cache statistics (for debugging)
     *
     * @return array
     */
    public function getStats()
    {
        $prefixLength = strlen($this->prefix);
        $totalKeys = 0;
        $expiredKeys = 0;
        $activeKeys = 0;

        foreach (self::$cache as $prefixedKey => $item) {
            if (substr($prefixedKey, 0, $prefixLength) === $this->prefix) {
                $totalKeys++;
                
                if ($this->isExpired($prefixedKey, $item)) {
                    $expiredKeys++;
                } else {
                    $activeKeys++;
                }
            }
        }

        return [
            'total_keys' => $totalKeys,
            'active_keys' => $activeKeys,
            'expired_keys' => $expiredKeys,
            'prefix' => $this->prefix
        ];
    }

    /**
     * Clear expired items (garbage collection)
     *
     * @return int Number of items removed
     */
    public function gc()
    {
        $removed = 0;
        $prefixLength = strlen($this->prefix);

        foreach (self::$cache as $prefixedKey => $item) {
            if (substr($prefixedKey, 0, $prefixLength) === $this->prefix) {
                if ($this->isExpired($prefixedKey, $item)) {
                    $removed++;
                }
            }
        }

        return $removed;
    }
}

