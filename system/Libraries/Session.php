<?php
namespace System\Libraries;
// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Session {

    /**
     * Initialize session if not already started
     */
    public static function start() {
        if (self::isCli()) return;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function isCli(): bool { return PHP_SAPI === 'cli'; }

    /**
     * Set a value in session
     * 
     * @param string $key Session name
     * @param mixed $value Value to store
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from session
     * 
     * @param string $key Session name
     * @return mixed|null Session value, or null if not exists
     */
    public static function get($key) {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    /**
     * Delete a specific session
     * 
     * @param string $key Session name to delete
     */
    public static function del($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy entire session
     */
    public static function destroy() {
        self::start();
        session_unset();
        $_SESSION = [];
        session_destroy();
    }

    /** use session_write_close() for fix LOCK
     * @return void
     */
    public static function write_close() {
        self::start();
        session_write_close();
    }

    /**
     * Check existence of a session
     * 
     * @param string $key Session name to check
     * @return bool True if session exists, False if not
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function has_flash($key) {
        self::start();
        return isset($_SESSION['flash']) && isset($_SESSION['flash'][$key]);
    }

    /**
     * Create a temporary message (flash data). If no value is passed, it will be get flash data
     * This data will only exist in the next request and be deleted afterwards
     * 
     * @param string $key Flash message name
     * @param mixed $value Flash message value
     */
    public static function flash($key, $value = null) {
        self::start();
        // Setter: Have 2 parameters
        if (func_num_args() >= 2){
            $_SESSION['flash'][$key] = ['data'=>$value, 'expires'=>time()+60];
            return null;
        }
        
        // Getter: Have 1 parameter
        if (isset($_SESSION['flash'][$key])) {
            $item = $_SESSION['flash'][$key];
            $valid = ($item['expires'] > time());
            unset($_SESSION['flash'][$key]);
            if (empty($_SESSION['flash'])) unset($_SESSION['flash']);
            return $valid ? $item['data'] : null;
        }
        return null;
    }

    /**
     * Regenerate session ID to prevent session fixation
     * Should be called after user login or access permission change
     */
    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Check and limit session lifetime
     * Destroy session if timeout
     * 
     * @param int $maxLifetime Maximum time in seconds
     */
    public static function checkSessionTimeout($maxLifetime = 1800) {
        self::start();
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxLifetime)) {
            // Destroy session if exceeded allowed time
            self::destroy();
            return false;
        }
        $_SESSION['last_activity'] = time(); // Update last activity time
        return true;
    }

    /**
     * Create and get CSRF token
     * @param int $expired Token expiration time in seconds (default: 1800 = 30 minutes)
     * @param bool $cookie Use Double-Submit Cookie pattern for API (default: false)
     * @return string String in format `csrf_id__csrf_token` or just `csrf_token` for cookie mode
     */
    public static function csrf_token($expired = 1800, $cookie = false) {
        self::start();
        self::csrf_clean();
        
        // Double-Submit Cookie pattern for API
        if ($cookie) {
            // Generate a random CSRF token
            $csrfToken = bin2hex(random_bytes(32));
            
            // Set cookie with the same token (HttpOnly = false for JavaScript access)
            $cookieName = 'XSRF-TOKEN';
            $cookieValue = $csrfToken;
            $cookieExpiry = time() + $expired;
            $cookiePath = '/';
            $cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
            $cookieSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $cookieHttpOnly = false; // Allow JavaScript to read for API requests
            $cookieSameSite = 'Lax';
            
            setcookie($cookieName, $cookieValue, $cookieExpiry, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly);
            
            // Also store in session for verification (with shorter expiry)
            $uri = trim(APP_URI['uri'], '/');
            $csrfId = hash('sha256', $uri . '_cookie');
            $_SESSION['csrf_tokens'][$csrfId] = [
                'token'   => $csrfToken,
                'expires' => time() + $expired,
                'created' => time()
            ];
            
            return $csrfToken; // Return just the token for API
        }
        
        // Traditional session-based CSRF token
        if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        // Create csrf_id based on current URL
        $uri = trim(APP_URI['uri'], '/');
        $csrfId = hash('sha256', $uri);
        $now = time();
        // Check if csrf_id exists in session and token is not expired
        if ( !empty($_SESSION['csrf_tokens'][$csrfId]) && !empty($_SESSION['csrf_tokens'][$csrfId]['token']) && 
            $_SESSION['csrf_tokens'][$csrfId]['expires'] >= $now ) {
            $_SESSION['csrf_tokens'][$csrfId]['expires'] = $now + $expired;
            $_SESSION['csrf_tokens'][$csrfId]['created'] = $now;
            return $csrfId . '__' . $_SESSION['csrf_tokens'][$csrfId]['token'];
        }
        // Create new csrf_token
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$csrfId] = [
            'token'   => $csrfToken,
            'expires' => $now + $expired,
            'created' => $now
        ];

        $max = 50;
        if (count($_SESSION['csrf_tokens']) > $max) {
            uasort($_SESSION['csrf_tokens'], function($a, $b) {
                return $a['created'] <=> $b['created']; // oldest first
            });
            // Lấy danh sách key dư thừa (older)
            $excess = array_slice(array_keys($_SESSION['csrf_tokens']), 0, count($_SESSION['csrf_tokens']) - $max);
            foreach ($excess as $dropKey) {
                unset($_SESSION['csrf_tokens'][$dropKey]);
            }
        }
        return $csrfId . '__' . $csrfToken;
    }

    /**
     * Verify CSRF token from session and form data
     * @param string $token String in format `csrf_id__csrf_token` from form or just `csrf_token` for cookie mode
     * @param bool $cookie Use Double-Submit Cookie pattern for API (default: false)
     * @return bool True if token is valid, False if not
     */
    public static function csrf_verify($token = null, $cookie = false) {
        self::start();
        self::csrf_clean();
        
        // Double-Submit Cookie pattern for API
        if ($cookie) {
            // Get token from request data
            if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
            if (empty($token)) {
                return false;
            }
            
            // Get cookie value
            $cookieName = 'XSRF-TOKEN';
            $cookieValue = $_COOKIE[$cookieName] ?? '';
            
            if (empty($cookieValue)) {
                return false;
            }
            
            // Verify that the token in request matches the cookie
            if (!hash_equals($cookieValue, $token)) {
                return false;
            }
            
            // Also verify against session for additional security
            $uri = trim(APP_URI['uri'], '/');
            $csrfId = hash('sha256', $uri . '_cookie');
            
            if (isset($_SESSION['csrf_tokens'][$csrfId])) {
                $storedTokenData = $_SESSION['csrf_tokens'][$csrfId];
                if (hash_equals($storedTokenData['token'], $token) && $storedTokenData['expires'] >= time()) {
                    // Delete token after successful verification to prevent reuse
                    unset($_SESSION['csrf_tokens'][$csrfId]);
                    if (empty($_SESSION['csrf_tokens'])) {
                        unset($_SESSION['csrf_tokens']);
                    }
                    return true;
                }
            }
            
            return false;
        }
        
        // Traditional session-based CSRF verification
        // Check if token is valid (token must not be empty and must contain '__')
        if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])){
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if (empty($token) || strpos($token, '__') === false) {
            return false;
        }
        // Extract csrf_id and csrf_token from input string
        list($csrfId, $csrfToken) = explode('__', $token, 2);

        // Check if csrf_id exists in session
        if (!isset($_SESSION['csrf_tokens'][$csrfId])) {
            return false;
        }
        // Get csrf_token information from session
        $storedTokenData = $_SESSION['csrf_tokens'][$csrfId];
        // Check if token matches and not expired
        if (hash_equals($storedTokenData['token'], $csrfToken) && $storedTokenData['expires'] >= time()) {
            // Delete token after successful verification to prevent reuse
            unset($_SESSION['csrf_tokens'][$csrfId]);
            if (empty($_SESSION['csrf_tokens'])){
                unset($_SESSION['csrf_tokens']);
            }
            return true;
        }else{
            // Failed verification should also delete csrf to recreate
            unset($_SESSION['csrf_tokens'][$csrfId]);
            if (empty($_SESSION['csrf_tokens'])){
                unset($_SESSION['csrf_tokens']);
            }
            return false;
        }
    }
    
    /**
     * Delete expired CSRF tokens in session
     * @param bool $cookie Also clean cookie-based tokens (default: false)
     */
    public static function csrf_clean($cookie = false) {
        self::start();

        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $now = time();
        
        // Delete expired tokens
        foreach ($_SESSION['csrf_tokens'] as $csrfId => $tokenData) {
            if ($tokenData['expires'] < $now) {
                unset($_SESSION['csrf_tokens'][$csrfId]);
            }
        }
        
        // Clean cookie-based tokens if requested
        if ($cookie) {
            $cookieName = 'XSRF-TOKEN';
            if (isset($_COOKIE[$cookieName])) {
                // Check if cookie is expired by checking session data
                $uri = trim(APP_URI['uri'], '/');
                $csrfId = hash('sha256', $uri . '_cookie');
                
                if (isset($_SESSION['csrf_tokens'][$csrfId])) {
                    $tokenData = $_SESSION['csrf_tokens'][$csrfId];
                    if ($tokenData['expires'] < $now) {
                        // Cookie is expired, remove it
                        setcookie($cookieName, '', time() - 3600, '/');
                        unset($_SESSION['csrf_tokens'][$csrfId]);
                    }
                }
            }
        }
        
        if (empty($_SESSION['csrf_tokens'])){
            unset($_SESSION['csrf_tokens']);
        }
    }
    
    /**
     * Helper function to get CSRF token for API (Double-Submit Cookie pattern)
     * @param int $expired Token expiration time in seconds (default: 600 = 10 minutes)
     * @return array Array with token and cookie information
     */
    public static function csrf_api($expired = 600) {
        return self::csrf_token($expired, true);
    }
    
    /**
     * Helper function to verify CSRF token for API (Double-Submit Cookie pattern)
     * @param string $token CSRF token from request data
     * @return bool True if token is valid, False if not
     */
    public static function csrf_verify_api($token = null) {
        return self::csrf_verify($token, true);
    }
}