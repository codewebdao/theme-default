<?php
namespace System\Libraries;

/**
 * Rate Limiter - Laravel-style rate limiting
 * 
 * Uses cache for tracking attempts (configurable: file/redis/memcached)
 * 
 * Features:
 * - Configurable cache backend
 * - Automatic cleanup
 * - Atomic increment operations
 * - Decay window support
 * - Clear on success pattern
 * 
 * @example
 * // Check if too many attempts
 * if (RateLimiter::tooManyAttempts('login:'.$ip, 5)) {
 *     $seconds = RateLimiter::availableIn('login:'.$ip);
 *     throw new RateLimitException("Try again in {$seconds} seconds");
 * }
 * 
 * // Hit rate limiter
 * RateLimiter::hit('login:'.$ip, 300); // 5 minutes decay
 * 
 * // Attempt pattern (Laravel-style)
 * $result = RateLimiter::attempt('send-email:'.$userId, 5, function() {
 *     // Send email...
 *     return true;
 * }, 60);
 * 
 * // Clear on success
 * if ($loginSuccessful) {
 *     RateLimiter::clear('login:'.$ip);
 * }
 */
class RateLimiter
{
    /**
     * Cache store name for rate limiting
     * @var string|null
     */
    private static $store = null;

    /**
     * Default decay time (seconds)
     * @var int
     */
    private static $defaultDecay = 60;

    /**
     * Get configured cache store for rate limiter
     * Recommended: redis > memcached > file
     *
     * @return string
     */
    private static function getStore()
    {
        if (self::$store === null) {
            $cacheConfig = config('cache');
            self::$store = $cacheConfig['limiter'] ?? $cacheConfig['default'] ?? 'file';
        }
        
        return self::$store;
    }

    /**
     * Get cache instance for rate limiting
     * Uses specified store from config
     *
     * @return \System\Libraries\CacheManager
     */
    private static function cache()
    {
        return Cache::store(self::getStore());
    }

    /**
     * Set custom cache store for rate limiting
     *
     * @param string $store Store name (file, redis, memcached)
     */
    public static function useStore($store)
    {
        self::$store = $store;
    }
    /**
     * Attempt to execute callback if not rate limited
     * 
     * @param string $key Unique key (e.g., 'login:192.168.1.1')
     * @param int $maxAttempts Max attempts allowed
     * @param callable $callback Function to execute if allowed
     * @param int $decaySeconds Time window in seconds (default: 60)
     * @return mixed Callback result or false if rate limited
     */
    public static function attempt($key, $maxAttempts, $callback, $decaySeconds = 60)
    {
        if (self::tooManyAttempts($key, $maxAttempts)) {
            return false;
        }
        
        return tap($callback(), function() use ($key, $decaySeconds) {
            self::hit($key, $decaySeconds);
        });
    }
    
    /**
     * Determine if the given key has been "accessed" too many times
     *
     * @param string $key Rate limiter key
     * @param int $maxAttempts Maximum attempts allowed
     * @return bool
     */
    public static function tooManyAttempts($key, $maxAttempts)
    {
        return self::attempts($key) >= $maxAttempts;
    }
    
    /**
     * Increment the counter for a given key for a given decay time
     * 
     * Uses atomic increment operation for thread-safety
     * Sets expiration timer on first hit
     *
     * @param string $key Rate limiter key
     * @param int $decaySeconds Decay window in seconds
     * @return int New attempt count
     */
    public static function hit($key, $decaySeconds = 60)
    {
        $cache = self::cache();
        $cacheKey = self::getCacheKey($key);
        $timerKey = self::getTimerKey($key);
        
        // Try to increment first (atomic operation)
        // Pass TTL so Redis/Memcached can set TTL for new keys atomically
        // This handles both new and existing keys efficiently
        $attempts = $cache->increment($cacheKey, 1, $decaySeconds);
        
        if ($attempts === false) {
            // Key doesn't exist or increment failed - initialize
            // Note: This is not fully atomic, but acceptable for rate limiting
            // The worst case is we might lose 1-2 hits during initialization
            $cache->put($cacheKey, 1, $decaySeconds);
            $cache->put($timerKey, time() + $decaySeconds, $decaySeconds);
            return 1;
        }
        
        // Increment succeeded - ensure timer exists
        // Check if timer exists, if not set it (may happen if cache was partially cleared)
        if (!$cache->has($timerKey)) {
            $cache->put($timerKey, time() + $decaySeconds, $decaySeconds);
        }
        
        return $attempts;
    }
    
    /**
     * Get the number of attempts for the given key
     *
     * @param string $key Rate limiter key
     * @return int
     */
    public static function attempts($key)
    {
        $cache = self::cache();
        $cacheKey = self::getCacheKey($key);
        return (int) $cache->get($cacheKey, 0);
    }
    
    /**
     * Get the number of retries left for the given key
     *
     * @param string $key Rate limiter key
     * @param int $maxAttempts Maximum attempts allowed
     * @return int
     */
    public static function remaining($key, $maxAttempts)
    {
        $attempts = self::attempts($key);
        return max(0, $maxAttempts - $attempts);
    }
    
    /**
     * Get the number of seconds until the "key" is accessible again
     *
     * @param string $key Rate limiter key
     * @return int Seconds remaining
     */
    public static function availableIn($key)
    {
        $cache = self::cache();
        $timerKey = self::getTimerKey($key);
        $availableAt = $cache->get($timerKey);
        
        if ($availableAt === null) {
            return 0;
        }
        
        return max(0, $availableAt - time());
    }
    
    /**
     * Clear the hits and lockout timer for the given key
     * Call this after successful operation (e.g., successful login)
     *
     * @param string $key Rate limiter key
     * @return bool
     */
    public static function clear($key)
    {
        $cache = self::cache();
        $success = true;
        
        $success = $cache->forget(self::getCacheKey($key)) && $success;
        $success = $cache->forget(self::getTimerKey($key)) && $success;
        
        return $success;
    }

    /**
     * Reset attempts but keep timer (for testing/debugging)
     *
     * @param string $key
     * @return bool
     */
    public static function resetAttempts($key)
    {
        $cache = self::cache();
        return $cache->forget(self::getCacheKey($key));
    }

    /**
     * Get rate limit status for a key
     * Useful for debugging and monitoring
     *
     * @param string $key Rate limiter key
     * @param int $maxAttempts Max attempts for context
     * @return array Status information
     */
    public static function status($key, $maxAttempts = null)
    {
        $attempts = self::attempts($key);
        $availableIn = self::availableIn($key);
        
        $status = [
            'key' => $key,
            'attempts' => $attempts,
            'available_in' => $availableIn,
            'locked' => false
        ];

        if ($maxAttempts !== null) {
            $status['max_attempts'] = $maxAttempts;
            $status['remaining'] = self::remaining($key, $maxAttempts);
            $status['locked'] = self::tooManyAttempts($key, $maxAttempts);
        }

        return $status;
    }
    
    /**
     * Reset all rate limiter data
     * Use ONLY for testing - this affects ALL rate limiters
     *
     * @return bool
     */
    public static function clearAll()
    {        
        // This flushes entire cache store, use with caution
        return Cache::store(self::getStore())->flush();
    }
    
    /**
     * Get cache key for attempts counter
     * Format: ratelimit:attempts:{key}
     *
     * @param string $key
     * @return string
     */
    private static function getCacheKey($key)
    {
        return 'ratelimit:attempts:' . $key;
    }
    
    /**
     * Get cache key for availability timer
     * Format: ratelimit:timer:{key}
     *
     * @param string $key
     * @return string
     */
    private static function getTimerKey($key)
    {
        return 'ratelimit:timer:' . $key;
    }
}

/**
 * Rate Limit Exception
 * 
 * Thrown when rate limit is exceeded
 * Contains retry_after information for HTTP 429 responses
 */
class RateLimitException extends \Exception
{
    /**
     * Seconds until retry is allowed
     * @var int
     */
    public $retryAfter;

    /**
     * Rate limiter key that was exceeded
     * @var string
     */
    public $key;

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param int $retryAfter Seconds to wait
     * @param string $key Rate limiter key
     */
    public function __construct($message, $retryAfter = 60, $key = '')
    {
        parent::__construct($message);
        $this->retryAfter = $retryAfter;
        $this->key = $key;
    }

    /**
     * Get HTTP 429 response data
     *
     * @return array
     */
    public function getResponseData()
    {
        return [
            'message' => $this->getMessage(),
            'retry_after' => $this->retryAfter,
            'retry_at' => date('Y-m-d H:i:s', time() + $this->retryAfter)
        ];
    }
}

/**
 * Helper tap function (like Laravel)
 */
if (!function_exists('tap')) {
    function tap($value, $callback = null)
    {
        if ($callback === null) {
            return $value;
        }
        
        $callback($value);
        return $value;
    }
}
