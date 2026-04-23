<?php
// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    //exit('No direct access allowed.');
}

/**
 * xss_clean function
 * Filter inputs to prevent XSS (Cross-Site Scripting)
 * 
 * @param string $data Data to filter
 * @return string Cleaned data
 */
function xss_clean($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * e() - Escape HTML to prevent XSS
 * Short and memorable function name (Laravel-style)
 * 
 * Usage: echo e($userInput); // Safe to output
 * 
 * @param string|null $value Value to escape
 * @param bool $doubleEncode Whether to double encode existing entities (default: false)
 * @return string Escaped string
 */
if (!function_exists('e')) {
    function e($value, $doubleEncode = false)
    {
        if ($value === null) {
            return '';
        }
        if (!is_string($value)) {
            try{
                $value = (string)$value;
            } catch (\Exception $e) {
                $value = json_encode($value);
            }
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

/**
 * h() - HTML escape (alternative short name)
 * Alias for e() function
 * 
 * @param string|null $value Value to escape
 * @param bool $doubleEncode Whether to double encode existing entities (default: false)
 * @return string Escaped string
 */
if (!function_exists('h')) {
    function h($value, $doubleEncode = false)
    {
        return e($value, $doubleEncode);
    }
}

/**
 * schema_safe_string() – Sanitize string for JSON-LD schema (chống XSS)
 * Dùng cho chuỗi từ payload/option trong Schema Types. KHÔNG dùng e()/h() cho giá trị đưa vào
 * schema vì output là JSON; khi hiển thị user content trong HTML thì dùng e() hoặc h().
 *
 * @param mixed $value Giá trị (string, number, null)
 * @return string Chuỗi an toàn (strip_tags, trim, loại control chars)
 */
if (!function_exists('schema_safe_string')) {
    function schema_safe_string($value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (!is_string($value)) {
            if (is_numeric($value)) {
                return (string) $value;
            }
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }
            return '';
        }
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        return trim($value);
    }
}

/**
 * clean_input function
 * Clean input data to prevent security vulnerabilities like XSS
 * 
 * ✅ IMPROVED: Less strict - preserves quotes and special chars for API compatibility
 * Uses htmlspecialchars only for XSS protection, doesn't remove valid characters
 * 
 * @param mixed $data Data to clean (string or array)
 * @param bool $strict If true, use strict mode (remove quotes, more filtering)
 * @return mixed Cleaned data
 */
function clean_input($data, $strict = false)
{
    if (is_array($data)) {
        // If $data is an array, apply clean_input to each element
        foreach ($data as $key => $value) {
            $data[$key] = clean_input($value, $strict);
        }
        return $data;
    }
    
    if (!is_string($data)) {
        return $data; // Return non-strings as-is (int, bool, null, etc.)
    }
    
    // Remove whitespace at beginning and end
    $data = trim($data);
    
    // Strict mode: Remove quotes and apply heavy filtering (for web forms)
    if ($strict) {
        // Remove unwanted characters like ', "
        $data = str_replace(["'", '"'], '', $data);
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        // Remove characters that are not letters, numbers, spaces and basic punctuation
        $data = preg_replace('/[^\w\s\p{P}]/u', '', $data);
    } else {
        // Normal mode: Light cleaning for API/JSON compatibility
        // Remove null bytes and dangerous control characters (except newline, tab, carriage return)
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data);
        // Note: htmlspecialchars() NOT used here to preserve data integrity for JSON
        // XSS protection is handled at output time (when rendering HTML)
    }
    
    return $data;
}

/**
 * strict_input function
 * Strict cleaning for web forms (removes quotes, heavy filtering)
 * 
 * @param mixed $data Data to clean
 * @return mixed Cleaned data
 */
if (!function_exists('strict_input')) {
    function strict_input($data)
    {
        return clean_input($data, true);
    }
}

/**
 * Function to safely get data from $_GET
 * 
 * @param string|null $key Name of parameter to get (null = return all $_GET)
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Cleaned data or default value
 */
function S_GET($key = null, $default = null)
{
    if ($key === null) {
        return clean_input($_GET);
    }
    if (isset($_GET[$key])) {
        return clean_input($_GET[$key]);
    }
    return $default;
}

/**
 * Function to check if data exists in $_GET
 * 
 * @param string $key Name of parameter to get
 * @return boolean True or False
 */
function HAS_GET($key)
{
    return isset($_GET[$key]);
}

/**
 * Function to safely get data from $_POST
 * 
 * @param string|null $key Name of parameter to get (null = return all $_POST)
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Cleaned data or default value
 */
function S_POST($key = null, $default = null)
{
    if ($key === null) {
        return clean_input($_POST);
    }
    if (isset($_POST[$key])) {
        return clean_input($_POST[$key]);
    }
    return $default;
}

/**
 * Function to check if data exists in $_POST
 * 
 * @param string $key Name of parameter to get
 * @return boolean True or False
 */
function HAS_POST($key)
{
    return isset($_POST[$key]);
}

/**
 * Function to safely get data from $_REQUEST
 * 
 * @param string|null $key Name of parameter to get (null = return all $_REQUEST)
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Cleaned data or default value
 */
function S_REQUEST($key = null, $default = null)
{
    if ($key === null) {
        return clean_input($_REQUEST);
    }
    if (isset($_REQUEST[$key])) {
        return clean_input($_REQUEST[$key]);
    }
    return $default;
}

/**
 * Function to check if data exists in $_REQUEST
 * 
 * @param string $key Name of parameter to get
 * @return boolean True or False
 */
function HAS_REQUEST($key)
{
    return isset($_REQUEST[$key]);
}

/**
 * Function to safely get data from $_DELETE
 * 
 * @param string|null $key Name of parameter to get (null = return all $_DELETE)
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Cleaned data or default value
 */
function S_DELETE($key = null, $default = null)
{
    if ($key === null) {
        return clean_input($_DELETE ?? []);
    }
    if (isset($_DELETE[$key])) {
        return clean_input($_DELETE[$key]);
    }
    return $default;
}

/**
 * Function to check if data exists in $_DELETE
 * 
 * @param string $key Name of parameter to get
 * @return boolean True or False
 */
function HAS_DELETE($key)
{
    return isset($_DELETE[$key]);
}

/**
 * Function to safely get data from $_PUT
 * 
 * Note: PHP doesn't populate $_PUT automatically.
 * For PUT requests, data is usually in php://input (JSON or form-data)
 * This function reads from parsed PUT data if available, otherwise returns default
 * 
 * @param string $key Name of parameter to get
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Cleaned data or default value
 */
if (!function_exists('S_PUT')) {
    function S_PUT($key = null, $default = null)
    {
        if ($key === null) {
            return clean_input($_PUT ?? []);
        }
        if (isset($_PUT[$key])) {
            return clean_input($_PUT[$key]);
        }
        return $default;
    }
}

/**
 * Function to check if data exists in $_PUT
 * 
 * @param string $key Name of parameter to get
 * @return boolean True or False
 */
if (!function_exists('HAS_PUT')) {
    function HAS_PUT($key)
    {
        return isset($_PUT[$key]);
    }
}

/**
 * uri_security function
 * Clean and protect URI against XSS, SQL Injection attacks
 * 
 * @param string $uri URI data to clean
 * @return string Cleaned URI
 */
// function uri_security($uri) {
//     // Remove invalid characters from URI
//     $uri = filter_var($uri, FILTER_SANITIZE_URL);
//     $uri = preg_replace('#/+#', '/', $uri); // Remove consecutive // characters
//     $uri = preg_replace('#\.\.+#', '', $uri); // Replace .. or ... with index
//     // Apply additional XSS cleaning steps
//     return xss_clean($uri);
// }

/**
 * Clean URI (path) – remove unwanted characters, keep a-z, A-Z, 0-9, -, _
 * While still **preserving** slash `/` characters to divide folders/route levels.
 */
function uri_security($uri)
{
    // Step 1: Decode %xx (if any)
    if (!empty($uri)) {
        $uri = rawurldecode($uri);
        // Step 2: Remove consecutive // characters -> only 1 remains
        $uri = preg_replace('#/+#', '/', $uri);
        // Step 3: Avoid '..' or '...' => directory traversal security
        $uri = str_replace(['..', '...'], '', $uri);
        // Step 4: Split by slash, sanitize each "segment"
        $parts = explode('/', $uri);
        $cleanParts = [];
        foreach ($parts as $p) {
            // Only allow [A-Za-z0-9_-.], you can expand as needed (e.g., add . or ~)
            $p = preg_replace('/[^A-Za-z0-9_\-.]/', '', $p);
            $p = trim($p, '.');
            // If segment is not empty after filtering then keep it
            if ($p !== '') {
                $cleanParts[] = $p;
            }
        }
        // Step 5: Combine into new URI
        $cleanUri = implode('/', $cleanParts);
        // Step 6: XSS clean (if xss_clean function exists)
        $cleanUri = xss_clean($cleanUri);
        return $cleanUri;
    }
    return '';
}

function sget_security()
{
    $cacheParams = [];
    $option_cache = option('cache');
    if (!is_array($option_cache)) {
        $option_cache = json_decode($option_cache, true) ?? [];
    }
    $option_cache = array_column($option_cache, 'cache_value', 'cache_key');
    if (isset($option_cache['cache_params']) && !empty($option_cache['cache_params'])) {
        $option_cache['cache_params'] = explode(',', $option_cache['cache_params']);
        $cacheParams = $option_cache['cache_params'];
    }
    unset($option_cache);
    foreach ($_GET as $key => $value) {
        // Convert key to lowercase:
        if (in_array($key, $cacheParams)) {
            //$safeValue = preg_replace('/[^A-Za-z0-9\p{L}\s\/_-.]/u', '', rawurldecode($value) );
            $safeValue = preg_replace('/[^A-Za-z0-9\p{L}\s\/_\.\-]/u', '', rawurldecode($value));
            //$safeValue = rawurlencode($safeValue);
            if ($safeValue === null) {
                $safeValue = '';
            }
            $_GET[$key] = $safeValue;
        }
    }
    return $_GET;
}


/**
 * Security Helper Functions
 * 
 * Common security-related functions used across the application
 * Includes IP handling, fingerprint validation, rate limiting helpers
 */

if (!function_exists('get_client_ip')) {
    /**
     * Get real client IP address
     * Handles proxies, load balancers, and CDNs
     * 
     * Priority order:
     * 1. CF-Connecting-IP (Cloudflare)
     * 2. X-Forwarded-For (Proxies/Load Balancers)
     * 3. X-Real-IP (Nginx proxy)
     * 4. REMOTE_ADDR (Direct connection)
     *
     * @param bool $trustProxies Whether to trust proxy headers
     * @return string IPv4 or IPv6 address
     */
    function get_client_ip($trustProxies = true)
    {
        // Direct connection (most reliable)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!$trustProxies) {
            return $ip;
        }

        // Check proxy headers (in order of priority)
        $headers = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_FORWARDED_FOR',   // Standard proxy header
            'HTTP_X_REAL_IP',         // Nginx proxy
            'HTTP_CLIENT_IP',         // Less common
            'HTTP_X_FORWARDED',       // Rare
            'HTTP_FORWARDED_FOR',     // Rare
            'HTTP_FORWARDED'          // Rare
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                $ipList = $_SERVER[$header];

                // X-Forwarded-For can contain multiple IPs: "client, proxy1, proxy2"
                // Take the first one (client IP)
                if (strpos($ipList, ',') !== false) {
                    $ips = array_map('trim', explode(',', $ipList));
                    $ip = $ips[0];
                } else {
                    $ip = trim($ipList);
                }

                // Validate IP format
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if (!function_exists('get_user_agent')) {
    /**
     * Get sanitized User-Agent string
     *
     * @param int $maxLength Maximum length (default: 500)
     * @return string
     */
    function get_user_agent($maxLength = 500)
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Sanitize and limit length
        $ua = strip_tags($ua);
        $ua = substr($ua, 0, $maxLength);

        return $ua;
    }
}

if (!function_exists('validate_fingerprint')) {
    /**
     * Validate device fingerprint format
     *
     * @param string $fingerprint Fingerprint to validate
     * @return bool True if valid 32-char hex string
     */
    function validate_fingerprint($fingerprint)
    {
        if (empty($fingerprint)) {
            return false;
        }

        // Must be 32-character hexadecimal string (MD5 length)
        return preg_match('/^[a-f0-9]{32}$/i', $fingerprint) === 1;
    }
}

if (!function_exists('is_trusted_proxy')) {
    /**
     * Check if IP is a trusted proxy
     * 
     * Configure trusted proxies in config/security.php
     *
     * @param string $ip IP address to check
     * @return bool
     */
    function is_trusted_proxy($ip)
    {
        $trustedProxies = config('trusted_proxies', 'Security') ?? [];

        if (empty($trustedProxies)) {
            return false;
        }

        // Support CIDR notation: 192.168.1.0/24
        foreach ($trustedProxies as $proxy) {
            if (ip_in_range($ip, $proxy)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('ip_in_range')) {
    /**
     * Check if IP is in CIDR range
     *
     * @param string $ip IP address to check
     * @param string $range CIDR notation (e.g., "192.168.1.0/24")
     * @return bool
     */
    function ip_in_range($ip, $range)
    {
        if (strpos($range, '/') === false) {
            // Single IP, not CIDR
            return $ip === $range;
        }

        list($subnet, $mask) = explode('/', $range);

        // Convert to binary for comparison
        $ipBin = sprintf('%032b', ip2long($ip));
        $subnetBin = sprintf('%032b', ip2long($subnet));

        // Compare first $mask bits
        return substr($ipBin, 0, $mask) === substr($subnetBin, 0, $mask);
    }
}

if (!function_exists('generate_token_id')) {
    /**
     * Generate unique token ID for tracking
     * Format: yyyymmdd-hhmmss-randomhex
     *
     * @return string
     */
    function generate_token_id()
    {
        return date('Ymd-His') . '-' . bin2hex(random_bytes(8));
    }
}

if (!function_exists('hash_token')) {
    /**
     * Hash token for secure storage
     * Uses SHA-256 for consistent 64-char output
     *
     * @param string $token Token to hash
     * @return string 64-character hash
     */
    function hash_token($token)
    {
        return hash('sha256', $token);
    }
}

if (!function_exists('is_bot_user_agent')) {
    /**
     * Check if User-Agent is a bot/crawler
     *
     * @param string|null $userAgent User-Agent string (defaults to current request)
     * @return bool
     */
    function is_bot_user_agent($userAgent = null)
    {
        $userAgent = $userAgent ?? get_user_agent();
        $userAgent = strtolower($userAgent);

        $botPatterns = [
            'bot',
            'crawler',
            'spider',
            'scraper',
            'googlebot',
            'bingbot',
            'slurp',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'facebookexternalhit',
            'ia_archiver',
            'archive.org',
            'curl',
            'wget'
        ];

        foreach ($botPatterns as $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('get_request_signature')) {
    /**
     * Get unique signature for current request
     * Useful for request deduplication
     *
     * @return string
     */
    function get_request_signature()
    {
        $components = [
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            get_client_ip(),
            get_user_agent(),
            $_SERVER['HTTP_ACCEPT'] ?? ''
        ];

        return hash('sha256', implode('|', $components));
    }
}

if (!function_exists('sanitize_cache_key')) {
    /**
     * Sanitize cache key to prevent injection
     *
     * @param string $key
     * @return string
     */
    function sanitize_cache_key($key)
    {
        // Remove dangerous characters
        $key = preg_replace('/[^a-zA-Z0-9_\-:.]/', '', $key);

        // Limit length
        $key = substr($key, 0, 250);

        return $key;
    }
}

if (!function_exists('is_private_ip')) {
    /**
     * Check if IP is private/local
     *
     * @param string $ip
     * @return bool
     */
    function is_private_ip($ip)
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}

if (!function_exists('get_request_fingerprint')) {
    /**
     * Generate request fingerprint from headers
     * More detailed than device fingerprint, for request tracking
     *
     * @return string
     */
    function get_request_fingerprint()
    {
        $components = [
            get_client_ip(),
            get_user_agent(),
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
            $_SERVER['HTTP_ACCEPT'] ?? ''
        ];

        $fingerprint = implode('|', $components);
        return hash('md5', $fingerprint); // 32 chars
    }
}
