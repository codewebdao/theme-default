<?php

namespace System\Drivers\Cache;

use System\Core\AppException;

class UriCache
{

    protected $cacheDir;
    protected $whitelist = ['page', 'paged', 'limit', 'sortby', 'sort', 'sc', 'order', 'orderby', 'id'];
    protected $compression; // 0 = no gzip, 1-9 = gzip level
    protected $headerGzip = false; // Variable to determine sending gzip header when getting cache
    protected $headerType;
    protected $cacheLogin = false;
    protected $cacheMobile = false;
    protected $cacheFolderPath;
    // Request Information
    protected $isHttps; // Cached HTTPS detection
    protected $isMobile; // Cached mobile detection
    protected $isUserLoggedIn; // Cached login status
    protected $gzipSupported; // Cached gzip support detection

    public function __construct($compression = 0, $type = 'html')
    {
        // Set cache path (assume PATH_ROOT is defined)
        $this->cacheDir = PATH_CONTENT . 'cache/';
        $this->compression = $compression;
        $this->headerType = $type;

        $option_cache = _json_decode(option('cache'));
        $option_cache = array_column($option_cache, 'cache_value', 'cache_key');
        //Value is: Array ( [cache_driver] => redis [cache_host] => 127.0.0.1 [cache_port] => 6379 [cache_password] => [cache_database] => 0 [cache_uri] => cache [cache_params] => id,page,paged,sort,sortby,order,orderby )
        if (isset($option_cache['cache_params']) && !empty($option_cache['cache_params'])) {
            $option_cache['cache_params'] = explode(',', $option_cache['cache_params']);
            $this->whitelist = $option_cache['cache_params'];
        }
        if (isset($option_cache['cache_uri']) && !empty($option_cache['cache_uri'])) {
            $this->cacheDir = PATH_CONTENT . $option_cache['cache_uri'] . '/';
        }

        // Cache environment detection once in constructor to avoid repeated IO/system calls
        $this->isHttps = $this->_detectHttps();
        $this->isMobile = $this->_detectMobile();
        $this->isUserLoggedIn = isset($_COOKIE['cmsff_logged']);
        $this->gzipSupported = !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
    }

    /**
     * Function to get gzip compression ratio
     */

    public function gzip_level()
    {
        return $this->compression;
    }

    /**
     * Send appropriate header based on $this->headerGzip and document type and html or json etc.
     */
    public function headers()
    {
        $contentTypeMap = [
            'html' => 'text/html; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'text' => 'text/plain; charset=UTF-8',
            'xml' => 'application/xml; charset=UTF-8',
            'css' => 'text/css; charset=UTF-8',
            'js'  => 'application/javascript; charset=UTF-8',
        ];
        if ($this->headerGzip) {
            header('Content-Encoding: gzip');
            header('X-Accel-Buffering: no');
        }
        $ctype = isset($contentTypeMap[$this->headerType]) ? $contentTypeMap[$this->headerType] : 'text/html; charset=UTF-8';
        header('Content-Type: ' . $ctype);
    }

    /**
     * Send appropriate header based on $this->headerGzip and document type and html or json etc. Then echo $content to browser
     */
    public function render($content)
    {
        // Disable output buffering if content is gzip to prevent double encoding
        if ($this->headerGzip) {
            // Disable any output buffering that might interfere
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $this->headers();
        // Set Content-Length for gzip content to help with proper transmission
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit();
    }

    /**
     * Build path to cache folder
     */
    protected function getCacheFolderPath()
    {
        if ($this->cacheFolderPath) {
            return $this->cacheFolderPath;
        }
        // Use HTTP_HOST but remove port to match nginx $host variable behavior
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Remove port if present (nginx $host doesn't include port)
        if (strpos($host, ':') !== false) {
            $host = substr($host, 0, strpos($host, ':'));
        }
        $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri  = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }
        $query_str = $_SERVER['QUERY_STRING'] ?? '';
        $args_path = '';
        if (!empty($query_str)) {
            parse_str($query_str, $params);
            $filtered = [];
            foreach ($params as $k => $v) {
                // Normalize key to lowercase (support Unicode)
                $k_lower = function_exists('mb_strtolower') ? mb_strtolower($k, 'UTF-8') : strtolower($k);
                // Security: validate key - allow lowercase, Unicode letters, numbers, space, dash, underscore
                // Reject: path traversal, null bytes, slashes, dangerous patterns
                if (
                    preg_match('/^[\p{L}\p{N}\s_-]+$/u', $k_lower) &&
                    !preg_match('/\.\.|[\x00\/\\\\]/', $k_lower) &&
                    in_array($k_lower, $this->whitelist)
                ) {

                    // Security: validate and sanitize value
                    // Allow: Unicode letters, numbers, space, dash, underscore (max 128 chars for search strings)
                    // Reject: path traversal, null bytes, slashes
                    if (is_string($v) && strlen($v) > 0) {
                        // Use mb_strlen for proper Unicode length checking
                        $vLength = function_exists('mb_strlen') ? mb_strlen($v, 'UTF-8') : strlen($v);
                        if ($vLength > 128) {
                            continue; // Skip if too long
                        }

                        // Decode any existing URL encoding to avoid double encoding
                        // parse_str() already decodes once, but decode again to be safe
                        $v_decoded = $v;
                        $previous = '';
                        $iterations = 0;
                        while ($v_decoded !== $previous && $iterations < 3) {
                            $previous = $v_decoded;
                            $v_decoded = rawurldecode($v_decoded);
                            $iterations++;
                        }
                        $v = $v_decoded;

                        // Reject dangerous patterns (check after decoding)
                        if (preg_match('/\.\.|[\x00\/\\\\]/', $v)) {
                            continue; // Skip this param if dangerous
                        }

                        // Remove null bytes (extra safety)
                        $v = str_replace("\0", '', $v);
                        if (empty($v)) {
                            continue;
                        }

                        // Sanitize for file system: encode to make it safe for file paths while preserving Unicode
                        // Use rawurlencode to encode special characters safely
                        $v_safe = rawurlencode($v);
                        // Replace encoded space (%20) with underscore for cleaner paths
                        $v_safe = str_replace('%20', '_', $v_safe);
                        // Keep other safe encoded chars, but ensure no dangerous patterns remain
                        if (!empty($v_safe) && !preg_match('/\.\.|[\x00\/\\\\]/', $v_safe)) {
                            $filtered[$k_lower] = $v_safe;
                        }
                    }
                }
            }
            ksort($filtered);
            if (!empty($filtered)) {
                $pairs = [];
                foreach ($filtered as $fk => $fv) {
                    // Normalize key for file path: replace space with underscore first (to match nginx behavior)
                    // Then encode if contains Unicode or other special chars
                    $fk_safe = str_replace(' ', '_', $fk);
                    // If key contains non-ASCII (Unicode), URL-encode it
                    // Note: space is already normalized to _, so we only need to encode Unicode
                    if (preg_match('/[\x80-\xFF]/', $fk_safe)) {
                        $fk_safe = rawurlencode($fk_safe);
                        // Replace any encoded spaces (shouldn't happen after normalization, but safety check)
                        $fk_safe = str_replace('%20', '_', $fk_safe);
                    }
                    $pairs[] = $fk_safe . '/' . $fv;
                }
                $args_path = '/' . implode('/', $pairs);
            }
        }
        $fullPath = $this->cacheDir
            . $host
            . '/'
            . trim($uri, '/')
            . $args_path
            . '/';
        $this->cacheFolderPath = $fullPath;
        return $fullPath;
    }


    /**
     * Return cache file path based on gzip configuration and content type
     * @param bool $use_gzip If true, return gzip file (.html_gzip or .json_gzip), otherwise return uncompressed file (.html or .json)
     * @param bool $createDir If true, create directory if it doesn't exist (default: false)
     */
    protected function getCacheFilePath($use_gzip = false, $createDir = false)
    {
        $fullPath = $this->getCacheFolderPath();
        if ($createDir && !is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        // Use cached values instead of calling methods
        $gzip_suffix = ($use_gzip && $this->compression > 0) ? '_gzip' : '';
        $filename = 'index';
        if ($this->isMobile && $this->cacheMobile) {
            $filename .= '-mobile';
        }
        if ($this->isHttps) {
            $filename .= '-https';
        }
        // Use appropriate extension based on content type
        $extension = ($this->headerType === 'json') ? '.json' : '.html';
        $filename .= $extension . $gzip_suffix;
        return $fullPath . $filename;
    }

    /**
     * Configure cache for logged in users:
     * If state = 1 => cache even when logged in
     * If state = 0 => when logged in will not cache, when not logged in still cache
     */
    public function cacheLogin($state = 1)
    {
        $this->cacheLogin = $state;
    }

    /**
     * Enable/disable cache specifically for mobile
     *
     * @param int $state 1 = enable (create .mobile-active file), 0 = disable (delete .mobile-active file)
     * @return bool true if operation successful, false if failed
     */
    public function cacheMobile($state = 1)
    {
        return $this->cacheMobile = $state;
        /*
        $fullPath = $this->getCacheFolderPath();
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        $mobileActivePath = $fullPath . '/.mobile-active';
        if ($state) {
            return touch($mobileActivePath);
        } else {
            return !file_exists($mobileActivePath) || unlink($mobileActivePath);
        }
        */
    }

    public function createMobileActive()
    {
        $fullPath = $this->getCacheFolderPath();
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        $mobileActivePath = $fullPath . '/.mobile-active';
        // touch() is atomic, no need for LOCK_EX
        return @touch($mobileActivePath);
    }

    public function createLoginActive()
    {
        $fullPath = $this->getCacheFolderPath();
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        $loginActivePath = $fullPath . '/.login-active';
        // touch() is atomic, no need for LOCK_EX
        return @touch($loginActivePath);
    }

    /**
     * Save cache.
     * If $this->compression > 0, will save both uncompressed file and gzip compressed file.
     */
    public function set($content, $return_gzip = false)
    {
        // Use cached login status
        if ($this->isUserLoggedIn) { // If user is logged in, return content without create caching file, for Security Guest interface.
            if ($this->headerGzip) {
                $gzipContent = gzencode($content, $this->compression);
                return $gzipContent;
            } else {
                return $content;
            }
        }
        // Create active files if needed (only when actually saving cache)
        if ($this->cacheLogin) {
            $this->createLoginActive();
        }
        if ($this->cacheMobile) {
            $this->createMobileActive();
        }
        // Cache file paths to avoid multiple calls
        $nonGzipPath = $this->getCacheFilePath(false, true);
        if (file_put_contents($nonGzipPath, $content, LOCK_EX) === false) {
            throw new AppException('Can not write cache: ' . $nonGzipPath);
        }
        // If gzip is enabled, save additional compressed file
        if ($this->compression > 0) {
            $gzipContent = gzencode($content, $this->compression);
            $gzipPath = $this->getCacheFilePath(true, true);
            if (file_put_contents($gzipPath, $gzipContent, LOCK_EX) === false) {
                throw new AppException('Can not write cache: ' . $gzipPath);
            }
            if ($return_gzip) {
                // Use cached gzip support
                if ($this->gzipSupported) {
                    $this->headerGzip = true;
                    return $gzipContent;
                }
            }
        }
        return $content;
    }

    /**
     * Check if content is gzip compressed
     * @param string $content Content to check
     * @return bool True if content is gzip compressed
     */
    public function isGzipContent($content)
    {
        return strlen($content) >= 2 && ord($content[0]) === 0x1f && ord($content[1]) === 0x8b;
    }

    /**
     * Get cache.
     * If browser supports gzip, and .html_gzip file exists then return compressed content (and set headerGzip = true),
     * otherwise return uncompressed file (with headerGzip = false).
     */
    public function get()
    {
        // Use cached login status
        if ($this->isUserLoggedIn && !$this->cacheLogin) {
            return null;
        }
        // Use cached gzip support
        $pathFileGzip = $this->getCacheFilePath(true);
        if ($this->gzipSupported && $this->compression > 0 && file_exists($pathFileGzip)) {
            $this->headerGzip = true;
            $file = $pathFileGzip;
        } else {
            $this->headerGzip = false;
            $file = $this->getCacheFilePath(false);
        }
        if (!file_exists($file)) {
            return null;
        }
        $data = file_get_contents($file);
        return ($data === false) ? null : $data;
    }

    public function debug()
    {
        // Use cached login status
        if ($this->isUserLoggedIn && !$this->cacheLogin) {
            return null;
        }
        // Use cached gzip support and cache file paths
        $gzipPath = $this->getCacheFilePath(true);
        if ($this->gzipSupported && $this->compression > 0 && file_exists($gzipPath)) {
            $file = $gzipPath;
        } else {
            $file = $this->getCacheFilePath(false);
        }
        return [
            'gzip' => $this->gzipSupported,
            'cache_path' => $file
        ];
    }

    /**
     * Delete cache.
     */
    public function delete()
    {
        $nonGzipPath = $this->getCacheFilePath(false);
        $gzipPath    = $this->getCacheFilePath(true);
        $result1 = file_exists($nonGzipPath) ? @unlink($nonGzipPath) : false;
        $result2 = file_exists($gzipPath) ? @unlink($gzipPath) : false;
        return $result1 || $result2;
    }

    /**
     * Check if cache exists (at least one of the 2 files)
     */
    public function has()
    {
        return file_exists($this->getCacheFilePath(false)) || file_exists($this->getCacheFilePath(true));
    }

    /**
     * Detect HTTPS (called once in constructor)
     * @return bool
     */
    protected function _detectHttps()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            return true;
        }
        return false;
    }

    /**
     * Detect mobile device (called once in constructor)
     * @return bool
     */
    protected function _detectMobile()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $pattern = '/phone|iphone|windows\s+phone|ipod|ipad|blackberry|(?:android|bb\d+|meego|silk|googlebot).+?mobile|palm|windows\s+ce|opera\ mini|avantgo|mobilesafari|docomo|kaios/i';
        return (bool)preg_match($pattern, $ua);
    }

    /**
     * Get HTTPS status (returns cached value)
     * @return bool
     */
    protected function isHttps()
    {
        return $this->isHttps;
    }

    /**
     * Get mobile status (returns cached value)
     * @return bool
     */
    protected function isMobile()
    {
        return $this->isMobile;
    }

    /**
     * Get login status (returns cached value)
     * @return bool
     */
    protected function isUserLoggedIn()
    {
        return $this->isUserLoggedIn;
    }

    /**
     * Clear all cache.
     */
    public function clear()
    {
        $this->rrmdir($this->cacheDir);
        return true;
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $objPath = $dir . '/' . $object;
                    if (is_dir($objPath)) {
                        $this->rrmdir($objPath);
                    } else {
                        @unlink($objPath);
                    }
                }
            }
            @rmdir($dir);
        }
    }
}
