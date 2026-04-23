<?php

namespace System\Libraries\Uploads\ChunkUtils;

use System\Libraries\RateLimiter as BaseRateLimiter;

/**
 * RateLimiter - Rate limiting for chunk uploads
 * 
 * Wrapper around System\Libraries\RateLimiter with additional session tracking
 * for concurrent upload sessions management.
 * 
 * Ngăn chặn abuse và DoS attacks bằng cách giới hạn:
 * - Số chunks upload per window (dùng BaseRateLimiter)
 * - Số upload sessions per IP (logic riêng cho concurrent sessions)
 * 
 * @package System\Libraries\Uploads\ChunkUtils
 * @version 3.0.0
 */
class RateLimiter
{
    /**
     * Storage for session tracking (concurrent upload sessions)
     * Uses cache for persistence
     */
    private static $sessionsCache = null;
    
    /**
     * Get cache instance for sessions tracking
     * 
     * @return \System\Libraries\CacheManager
     */
    private static function cache()
    {
        if (self::$sessionsCache === null) {
            self::$sessionsCache = \System\Libraries\Cache::store('file');
        }
        return self::$sessionsCache;
    }
    
    /**
     * Get cache key for sessions
     * 
     * @param string $identifier Identifier (IP address, user ID, etc.)
     * @return string
     */
    private static function getSessionsKey($identifier)
    {
        return 'upload_sessions:' . md5($identifier);
    }
    
    /**
     * Check if request is allowed
     * 
     * @param string $identifier Identifier (IP address, user ID, etc.)
     * @param array $options Rate limit options
     *   - 'max_requests' => int: Max requests per window (default: 100)
     *   - 'window' => int: Time window in seconds (default: 60)
     *   - 'max_sessions' => int: Max concurrent sessions (default: 5)
     * @return array ['allowed' => bool, 'error' => string|null, 'retry_after' => int|null]
     */
    public static function check($identifier, $options = [])
    {
        $maxRequests = $options['max_requests'] ?? 100;
        $window = $options['window'] ?? 60; // 60 seconds
        $maxSessions = $options['max_sessions'] ?? 5;
        
        // ✅ Use BaseRateLimiter for requests rate limiting
        $rateLimitKey = 'upload:' . $identifier;
        
        // Check IP-based rate limit (requests per window) FIRST
        if (BaseRateLimiter::tooManyAttempts($rateLimitKey, $maxRequests)) {
            $seconds = BaseRateLimiter::availableIn($rateLimitKey);
            
            error_log("Upload rate limit exceeded for: {$identifier} - {$maxRequests} requests in {$window}s");
            
            return [
                'allowed' => false,
                'error' => "Rate limit exceeded. Maximum {$maxRequests} requests per {$window} seconds.",
                'retry_after' => max(1, $seconds)
            ];
        }
        
        // ✅ Check max concurrent sessions BEFORE hitting rate limiter
        // This prevents hitting rate limiter if sessions check fails
        $sessionsKey = self::getSessionsKey($identifier);
        $sessions = self::cache()->get($sessionsKey, []);
        
        // Clean old sessions (older than 1 hour)
        $now = time();
        $activeSessions = [];
        foreach ($sessions as $uploadId => $timestamp) {
            if (($now - $timestamp) < 3600) {
                $activeSessions[$uploadId] = $timestamp;
            }
        }
        
        // Update cache with cleaned sessions
        if (count($activeSessions) !== count($sessions)) {
            self::cache()->put($sessionsKey, $activeSessions, 3600);
        }
        
        // Check max concurrent sessions
        if (count($activeSessions) >= $maxSessions) {
            error_log("Max upload sessions exceeded for: {$identifier} - {$maxSessions} concurrent sessions");
            
            return [
                'allowed' => false,
                'error' => "Maximum concurrent upload sessions exceeded ({$maxSessions}).",
                'retry_after' => 60
            ];
        }
        
        // ✅ All checks passed - hit BaseRateLimiter for this request
        BaseRateLimiter::hit($rateLimitKey, $window);
        
        return [
            'allowed' => true,
            'error' => null,
            'retry_after' => null
        ];
    }
    
    /**
     * Register a new upload session
     * 
     * @param string $identifier Identifier (IP address, user ID, etc.)
     * @param string $uploadId Upload session ID
     */
    public static function registerSession($identifier, $uploadId)
    {
        $sessionsKey = self::getSessionsKey($identifier);
        $sessions = self::cache()->get($sessionsKey, []);
        
        $sessions[$uploadId] = time();
        
        // Store for 1 hour (session lifetime)
        self::cache()->put($sessionsKey, $sessions, 3600);
    }
    
    /**
     * Unregister upload session
     * 
     * @param string $identifier Identifier (IP address, user ID, etc.)
     * @param string $uploadId Upload session ID
     */
    public static function unregisterSession($identifier, $uploadId)
    {
        $sessionsKey = self::getSessionsKey($identifier);
        $sessions = self::cache()->get($sessionsKey, []);
        
        if (isset($sessions[$uploadId])) {
            unset($sessions[$uploadId]);
            
            if (empty($sessions)) {
                self::cache()->forget($sessionsKey);
            } else {
                self::cache()->put($sessionsKey, $sessions, 3600);
            }
        }
    }
    
    /**
     * Get current rate limit status
     * 
     * @param string $identifier Identifier
     * @param array $options Options
     * @return array Status information
     */
    public static function getStatus($identifier, $options = [])
    {
        $maxRequests = $options['max_requests'] ?? 100;
        $window = $options['window'] ?? 60;
        $maxSessions = $options['max_sessions'] ?? 5;
        
        // ✅ Get requests status from BaseRateLimiter
        $rateLimitKey = 'upload:' . $identifier;
        $requestsCount = BaseRateLimiter::attempts($rateLimitKey);
        $requestsRemaining = BaseRateLimiter::remaining($rateLimitKey, $maxRequests);
        
        // ✅ Get sessions status from cache
        $sessionsKey = self::getSessionsKey($identifier);
        $sessions = self::cache()->get($sessionsKey, []);
        
        // Clean old sessions
        $now = time();
        $activeSessions = [];
        foreach ($sessions as $uploadId => $timestamp) {
            if (($now - $timestamp) < 3600) {
                $activeSessions[$uploadId] = $timestamp;
            }
        }
        
        return [
            'requests_count' => $requestsCount,
            'requests_remaining' => $requestsRemaining,
            'sessions_count' => count($activeSessions),
            'sessions_remaining' => max(0, $maxSessions - count($activeSessions)),
            'window' => $window
        ];
    }
    
    /**
     * Clear rate limit for identifier
     * 
     * @param string $identifier Identifier
     */
    public static function clear($identifier)
    {
        // ✅ Clear BaseRateLimiter
        $rateLimitKey = 'upload:' . $identifier;
        BaseRateLimiter::clear($rateLimitKey);
        
        // ✅ Clear sessions
        $sessionsKey = self::getSessionsKey($identifier);
        self::cache()->forget($sessionsKey);
    }
    
}
