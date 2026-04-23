<?php

/**
 * Cache Helper Functions
 * 
 * Global helper functions for caching
 * Laravel-style API for convenience
 */

if (!function_exists('cache')) {
    /**
     * Get / set cache values
     *
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed|\System\Libraries\Cache
     * 
     * @example
     * // Get value
     * $value = cache('key');
     * 
     * // Set value
     * cache(['key' => 'value'], 600);
     * 
     * // Get Cache instance
     * cache()->remember('users', 3600, fn() => User::all());
     */
    function cache($key = null, $default = null)
    {
        if (is_null($key)) {
            return new \System\Libraries\Cache();
        }

        if (is_array($key)) {
            return \System\Libraries\Cache::putMany($key, $default ?? 3600);
        }

        return \System\Libraries\Cache::get($key, $default);
    }
}

if (!function_exists('cache_remember')) {
    /**
     * Get an item from the cache, or execute the given Closure and store the result
     *
     * @param string $key
     * @param int $seconds
     * @param callable $callback
     * @return mixed
     */
    function cache_remember($key, $seconds, $callback)
    {
        return \System\Libraries\Cache::remember($key, $seconds, $callback);
    }
}

if (!function_exists('cache_forget')) {
    /**
     * Remove an item from the cache
     *
     * @param string $key
     * @return bool
     */
    function cache_forget($key)
    {
        return \System\Libraries\Cache::forget($key);
    }
}

if (!function_exists('cache_flush')) {
    /**
     * Remove all items from the cache
     *
     * @return bool
     */
    function cache_flush()
    {
        return \System\Libraries\Cache::flush();
    }
}

if (!function_exists('cache_tags')) {
    /**
     * Get tagged cache items (future feature)
     *
     * @param array|string $tags
     * @return \System\Libraries\TaggedCache
     */
    function cache_tags($tags)
    {
        // Future implementation
        throw new \RuntimeException('Cache tags not yet implemented');
    }
}
