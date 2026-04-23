<?php

/**
 * Storage Helper Functions - Universal Key-Value Storage
 * 
 * Simple, fast, type-safe storage system with multi-language support
 * 
 * Features:
 * - Scoped storage (application/plugins/themes/system)
 * - Multi-language support (like option system)
 * - Type-safe (bool stays bool, not "1")
 * - 3-tier caching (memory → file → database)
 * - TTL support with auto-refresh
 * - Opcache optimized
 * - Security hardened
 * 
 * @package System\Helpers
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('PATH_APP')) {
    exit('No direct script access allowed');
}

// Global memory cache (2-level: scope → key → data)
global $FAST_KEY_VALUES;
global $FAST_SCOPE_LOADS;
if (!isset($FAST_KEY_VALUES)) {
    $FAST_KEY_VALUES = [];
}
if (!isset($FAST_SCOPE_LOADS)) {
    $FAST_SCOPE_LOADS = [];
}

if (!function_exists('storage_get')) {
    /**
     * Get value from storage
     * 
     * @param string $key Storage key
     * @param string $scope Scope (application/plugins/themes/system)
     * @param string $lang Language code (default APP_LANG = current user language)
     * @param mixed $default Default value
     * @param bool $noCache Skip cache, get from DB directly
     * @return mixed Stored value
     * 
     * @example
     * $val = storage_get('currency', 'application');
     * $val = storage_get('api_key', 'ecommerce');
     * $val = storage_get('endpoint', 'ecommerce/payments/stripe');
     * $val = storage_get('realtime', 'system', APP_LANG, null, true); // No cache
     */
    function storage_get($key, $scope = 'application', $lang = APP_LANG, $default = null, $noCache = false)
    {
        global $FAST_KEY_VALUES, $FAST_SCOPE_LOADS;

        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);

        // If noCache, skip to database
        if ($noCache) {
            $dbValue = dbstorage_get($key, $scope, $lang);
            if ($dbValue !== null) {
                $FAST_KEY_VALUES[$scope][$key] = $dbValue;
                filestorage_set($scope, $FAST_KEY_VALUES[$scope]);
            }else{
                return $default;
            }
        }

        // 1. Check if scope is loaded in memory
        if (!array_key_exists($scope, $FAST_SCOPE_LOADS)) {
            // Load entire scope from file/db once
            filestorage_get($scope);
        }

        // 2. Get from memory cache (scope already loaded)
        if (isset($FAST_KEY_VALUES[$scope]) && array_key_exists($key, $FAST_KEY_VALUES[$scope])) {
            $valuesArray = $FAST_KEY_VALUES[$scope][$key];
        } else {
            // 3. Not in memory/file, try database
            $valuesArray = dbstorage_get($key, $scope, $lang);
            if ($valuesArray === null) {
                // 4. Not found - cache null and return default
                $FAST_KEY_VALUES[$scope][$key] = null;
                return $default;
            }
            // Cache to memory and file
            $FAST_KEY_VALUES[$scope][$key] = $valuesArray;
            filestorage_set($scope, $FAST_KEY_VALUES[$scope]);
        }

        // ✅ NEW: Extract value with priority order
        if (!is_array($valuesArray)) {
            return $default;
        }

        // 1. Check 'all' key (synchronous field)
        if (isset($valuesArray['all'])) {
            return $valuesArray['all'];
        }

        // 2. Check requested language
        if (isset($valuesArray[$lang])) {
            return $valuesArray[$lang];
        }

        // 3. Fallback to default language
        if (isset($valuesArray[APP_LANG_DF])) {
            return $valuesArray[APP_LANG_DF];
        }

        // 4. Return first available value
        if (!empty($valuesArray)) {
            return reset($valuesArray);
        }

        return $default;
    }
}

if (!function_exists('storage_set')) {
    /**
     * Set value to storage
     * 
     * @param string $key Storage key
     * @param mixed $value Value (bool/int/float/string/array/object)
     * @param string $scope Scope
     * @param string $lang Language code (default APP_LANG)
     * @return bool Success
     * 
     * @example
     * storage_set('currency', 'USD', 'application');
     * storage_set('api_key', '12345', 'plugins', 'ecommerce');
     * storage_set('heavy_data', $bigArray, 'plugins', 'ecommerce', APP_LANG, true);
     */
    function storage_set($key, $value, $scope = 'application', $lang = APP_LANG)
    {
        global $FAST_KEY_VALUES, $FAST_SCOPE_LOADS;

        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);

        // Load full scope into memory before updating one key (CLI/build may not have loaded file cache)
        // Otherwise filestorage_set() would overwrite file with partial data and web would miss updates
        if (!array_key_exists($scope, $FAST_SCOPE_LOADS)) {
            filestorage_get($scope);
        }
        if (!array_key_exists($scope, $FAST_KEY_VALUES)) {
            $FAST_KEY_VALUES[$scope] = [];
        }
        if (empty($FAST_KEY_VALUES[$scope])) {
            dbstorage_load_scope($scope);
        }

        // 1. Save to database (and cache to $FAST_KEY_VALUES[$scope][$key])
        if (dbstorage_set($key, $value, $scope, $lang)) {
            // 2. Save to file cache (full scope, not partial)
            filestorage_set($scope, $FAST_KEY_VALUES[$scope]);
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('storage_delete')) {
    /**
     * Delete value from storage
     * 
     * @param string $key Storage key
     * @param string $scope Scope (application/plugins)
     * @param string $lang Language code
     * @return bool Success
     */
    function storage_delete($key, $scope = 'application', $lang = APP_LANG)
    {
        global $FAST_KEY_VALUES, $FAST_SCOPE_LOADS;

        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);

        // Remove from memory (2-level structure)
        if (isset($FAST_KEY_VALUES[$scope]) && array_key_exists($key, $FAST_KEY_VALUES[$scope])) {
            unset($FAST_KEY_VALUES[$scope][$key]);
        }

        // Remove from database
        storage_db_delete($key, $scope, $lang);

        // Update file cache
        if (array_key_exists($scope, $FAST_SCOPE_LOADS)) {
            filestorage_set($scope, $FAST_KEY_VALUES[$scope]);
        }

        return true;
    }
}

if (!function_exists('storage_has')) {
    /**
     * Check if key exists in storage
     * 
     * @param string $key Storage key
     * @param string $scope Scope (application/plugins)
     * @param string $lang Language code
     * @return bool Exists
     */
    function storage_has($key, $scope = 'application', $lang = APP_LANG)
    {
        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);
        $value = storage_get($key, $scope, $lang, '__STORAGE_NOT_FOUND__');
        return $value !== '__STORAGE_NOT_FOUND__';
    }
}

/**
 * ================================================================
 * DATABASE OPERATIONS (Using DB::table)
 * ================================================================
 */

if (!function_exists('dbstorage_get')) {
    /**
     * Get value from database
     * 
     * @param string $key Key
     * @param string $scope Full scope (application/plugins)
     * @param string $lang Language
     * @return mixed|null Value or null
     */
    function dbstorage_get($key, $scope, $lang)
    {
        global $FAST_KEY_VALUES;

        // Security: Sanitize all inputs
        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);

        // ✅ Query database using scope + key (no prefix needed)
        $row = \System\Database\DB::table('storage')
            ->where('scope', $scope)
            ->where('name', $key)
            ->first();

        if (!$row) {
            return null;
        }

        // ✅ Unserialize value array
        $valuesArray = unserialize_safe($row['value']);

        if (!is_array($valuesArray)) {
            return null;
        }

        // Cache to memory
        if (!array_key_exists($scope, $FAST_KEY_VALUES)) {
            $FAST_KEY_VALUES[$scope] = [];
        }

        $FAST_KEY_VALUES[$scope][$key] = $valuesArray;

        // Return full value array (let storage_get() handle extraction)
        return $valuesArray;
    }
}

if (!function_exists('dbstorage_load_scope')) {
    /**
     * Load entire scope from database into memory cache (one query)
     * 
     * @param string $scope Scope to load
     * @return void
     */
    function dbstorage_load_scope($scope)
    {
        global $FAST_KEY_VALUES, $FAST_SCOPE_LOADS;

        $scope = sanitize_scope($scope);

        // If already loaded from file cache, skip
        if (array_key_exists($scope, $FAST_SCOPE_LOADS)) {
            return; // Already loaded from file cache
        }

        // Initialize scope in memory if not exists
        if (!array_key_exists($scope, $FAST_KEY_VALUES)) {
            $FAST_KEY_VALUES[$scope] = [];
        }

        // Query all keys for this scope (ONE query for entire scope)
        $rows = \System\Database\DB::table('storage')
            ->where('scope', $scope)
            ->get();

        // Load all into memory
        foreach ($rows as $row) {
            // ✅ Use key column directly (no prefix needed)
            $key = $row['name'];
            
            // Only add if not already in memory (file cache might have some)
            if (!array_key_exists($key, $FAST_KEY_VALUES[$scope])) {
                // Unserialize value array
                $valuesArray = unserialize_safe($row['value']);
                
                if (is_array($valuesArray)) {
                    $FAST_KEY_VALUES[$scope][$key] = $valuesArray;
                }
            }
        }
    }
}

if (!function_exists('dbstorage_set')) {
    /**
     * Save value to database & memory cache $FAST_KEY_VALUES[$scope][$key]
     * 
     * @param string $key Key
     * @param mixed $value Value
     * @param string $scope Full scope (ecommerce/general)
     * @param string $lang Language
     * @return bool Success
     */
    function dbstorage_set($key, $value, $scope, $lang)
    {
        global $FAST_KEY_VALUES;
        // Security: Sanitize all inputs
        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);

        if (empty($FAST_KEY_VALUES) || !array_key_exists($scope, $FAST_KEY_VALUES)) {
            $FAST_KEY_VALUES[$scope] = array();
        }

        // ✅ Check if key exists using scope + key (no prefix needed)
        $existing = \System\Database\DB::table('storage')
            ->where('scope', $scope)
            ->where('name', $key)
            ->first();
        
        // ✅ Get existing value array
        if ($existing) {
            $valuesArray = unserialize_safe($existing['value']) ?? [];
        } else {
            $valuesArray = [];
        }

        // ✅ NEW: Handle edge cases
        // Case 1: Setting sync field (lang='all')
        if ($lang === 'all') {
            // Clear all other languages, keep only 'all'
            $valuesArray = ['all' => $value];
        }
        // Case 2: Setting specific language
        else {
            // If was sync field (had 'all' key), convert to non-sync
            if (isset($valuesArray['all'])) {
                $oldValue = $valuesArray['all'];
                // Keep old value for default lang, set new value for requested lang
                if ($lang === APP_LANG_DF) {
                    // If setting default lang, just set it (no need to duplicate)
                    $valuesArray = [APP_LANG_DF => $value];
                } else {
                    // Keep old 'all' value for default lang, set new value for requested lang
                    $valuesArray = [
                        APP_LANG_DF => $oldValue,
                        $lang => $value
                    ];
                }
            } else {
                // Normal update (non-sync field)
                $valuesArray[$lang] = $value;
            }
        }

        // Serialize value array
        $serialized = @serialize($valuesArray);
        if ($serialized === false) {
            return false;
        }

        // ✅ Save to database using scope + key
        if ($existing) {
            $updateStatus = true;
            try{
                \System\Database\DB::table('storage')
                ->where('scope', $scope)
                ->where('name', $key)
                ->update([
                    'value' => $serialized
                ]);
            }catch(\Exception $e){
                $updateStatus = false;
            }
        } else {
            $updateStatus = true;
            try{
                \System\Database\DB::table('storage')->insert([
                    'scope' => $scope,
                    'name' => $key,
                    'value' => $serialized
                ]);
            }catch(\Exception $e){
                $updateStatus = false;
            }
        }
        
        // Update memory cache
        $FAST_KEY_VALUES[$scope][$key] = $valuesArray;

        return $updateStatus;
    }
}

if (!function_exists('storage_db_delete')) {
    /**
     * Delete value from database
     * 
     * @param string $key Key
     * @param string $scope Full scope (ecommerce/general)
     * @param string $lang Language (if empty, delete entire key)
     * @return bool Success
     */
    function storage_db_delete($key, $scope, $lang)
    {
        // Security: Sanitize all inputs
        $scope = sanitize_scope($scope);
        $key = sanitize_key($key);

        if (empty($lang) || $lang === APP_LANG_DF) {
            // ✅ Delete entire row using scope + key
            \System\Database\DB::table('storage')
                ->where('scope', $scope)
                ->where('name', $key)
                ->delete();
        } else {
            // ✅ Remove only language value from value array
            $existing = \System\Database\DB::table('storage')
                ->where('scope', $scope)
                ->where('name', $key)
                ->first();

            if ($existing) {
                $valuesArray = unserialize_safe($existing['value']) ?? [];
                unset($valuesArray[$lang]);

                \System\Database\DB::table('storage')
                    ->where('scope', $scope)
                    ->where('name', $key)
                    ->update([
                        'value' => @serialize($valuesArray),
                    ]);
            }
        }
        if (isset($FAST_KEY_VALUES[$scope]) && array_key_exists($key, $FAST_KEY_VALUES[$scope])) {
            unset($FAST_KEY_VALUES[$scope][$key]);
        }

        return true;
    }
}

/**
 * ================================================================
 * FILE CACHE OPERATIONS
 * ================================================================
 */

if (!function_exists('filestorage_get')) {
    /**
     * Load entire scope into memory cache (one-time operation per scope)
     * 
     * @param string $scope Scope to load
     * @return void
     */
    function filestorage_get($scope)
    {
        global $FAST_KEY_VALUES, $FAST_SCOPE_LOADS;

        $scope = sanitize_scope($scope);
        // Get file path for this scope
        $filePath = storage_path('', $scope);

        if (!file_exists($filePath)) {
            // No file cache, will load from DB on demand
            return;
        }

        // Load entire file (opcache will cache this)
        $cacheData = @include $filePath;

        if (!is_array($cacheData)) {
            return;
        }

        // Mark scope as loaded (even if empty)
        if (!array_key_exists($scope, $FAST_KEY_VALUES)) {
            $FAST_KEY_VALUES[$scope] = [];
        }
        if (!array_key_exists($scope, $FAST_SCOPE_LOADS)) {
            $FAST_SCOPE_LOADS[$scope] = true;
        }

        // Load all keys into memory
        if (is_array($cacheData) && !empty($cacheData)) {
            foreach ($cacheData as $key => $data) {
                if (!array_key_exists($key, $FAST_KEY_VALUES[$scope])) {
                    $FAST_KEY_VALUES[$scope][$key] = $data;
                }
            }
        }
    }
}

if (!function_exists('filestorage_set')) {
    /**
     * Write entire scope to file cache
     * 
     * @param string $scope Scope
     * @param array $scopeData All keys data for this scope
     * @return bool Success
     */
    function filestorage_set($scope, $scopeData)
    {
        $scope = sanitize_scope($scope);
        $filePath = storage_path('', $scope);
        $fileDir = dirname($filePath);

        // Create directory if not exists
        if (!is_dir($fileDir)) {
            @mkdir($fileDir, 0755, true);
        }

        // Export entire scope to PHP file using ArrayString for optimized output
        // ArrayString::ret() returns: "<?php\ndeclare(strict_types=1);\nreturn [...];\n"
        $content = \System\Libraries\ArrayString::ret($scopeData, [
            'strict' => true
        ]);
        // Insert comment after opening PHP tag
        $content = str_replace("<?php\n", "<?php\n// Storage cache: {$scope}\n", $content);

        $result = @file_put_contents($filePath, $content, LOCK_EX);

        // Invalidate opcache immediately
        if ($result !== false && function_exists('opcache_invalidate')) {
            @opcache_invalidate($filePath, true);
        }

        return $result !== false;
    }
}


if (!function_exists('storage_path')) {
    /**
     * Get file path for scope (one file per scope)
     * 
     * All keys with same scope share ONE file
     * 
     * @param string $storageKey Storage key (not used, kept for compatibility)
     * @param string $scope Full scope (ecommerce/general)
     * @return string File path
     */
    function storage_path($storageKey, $scope)
    {
        // Sanitize scope
        $scope = sanitize_scope($scope);

        // Build base path
        $basePath = PATH_WRITE . 'cache/storage/';

        // Hash scope to create filename
        $fileHash = md5($scope);

        // Extract first part for directory
        $scopeParts = explode('/', $scope);
        $mainScope = $scopeParts[0]; // plugins, themes, application, system

        // Create path: storage/{mainScope}/{hash}.php
        return $basePath . "{$mainScope}/{$fileHash}.php";
    }
}

/**
 * ================================================================
 * UTILITY FUNCTIONS
 * ================================================================
 */

if (!function_exists('sanitize_scope')) {
    /**
     * Sanitize full scope (max 100 chars, allows a-zA-Z0-9_-/)
     * 
     * Supports: application, ecommerce, themes/mytheme, ecommerce/payments/stripe
     */
    function sanitize_scope($scope)
    {
        // ✅ Only allow a-zA-Z0-9_-/ (removes all invalid chars including . and \)
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $scope);

        // Remove leading/trailing slashes
        $sanitized = trim($sanitized, '/');

        // Remove consecutive slashes (// becomes /)
        $sanitized = preg_replace('/\/+/', '/', $sanitized);

        // Limit length
        $sanitized = substr($sanitized, 0, 100);

        // Default if empty
        return !empty($sanitized) ? $sanitized : 'application';
    }
}

if (!function_exists('sanitize_key')) {
    /**
     * Sanitize key (max 50 chars, only a-zA-Z0-9_-)
     * NO COLONS for security (used as separator)
     */
    function sanitize_key($key)
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $key);
        $sanitized = substr($sanitized, 0, 50);
        return $sanitized;
    }
}

if (!function_exists('storage_delete_directory')) {
    /**
     * Recursively delete directory
     */
    function storage_delete_directory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                storage_delete_directory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}

if (!function_exists('storage_clear_all')) {
    /**
     * Clear all storage (all scopes)
     * 
     * @return bool Success
     */
    function storage_clear_all()
    {
        global $FAST_KEY_VALUES;

        // Clear memory
        $FAST_KEY_VALUES = [];

        // Clear files
        $cachePath = PATH_WRITE . 'cache/storage/';
        if (is_dir($cachePath)) {
            storage_delete_directory($cachePath);
            @mkdir($cachePath, 0755, true);
        }

        // No need to clear database
        //\System\Database\DB::table('storage')->truncate();

        return true;
    }
}

/**
 * ================================================================
 * SECURITY HELPERS
 * ================================================================
 */

if (!function_exists('unserialize_safe')) {
    /**
     * Safe unserialize (prevents object injection attacks)
     * 
     * @param string $serialized Serialized data
     * @return mixed Unserialized data or null on failure
     */
    function unserialize_safe($serialized)
    {
        if (empty($serialized)) {
            return null;
        }

        // PHP 7.0+: Use allowed_classes to prevent object injection
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $result = @unserialize($serialized, ['allowed_classes' => false]);
        } else {
            $result = @unserialize($serialized);
        }

        // Validate result
        if ($result === false && $serialized !== 'b:0;') {
            // Unserialize failed (unless it's false value)
            return null;
        }

        return $result;
    }
}

/**
 * ================================================================
 * WORDPRESS-STYLE OPTION FUNCTIONS (Mapped to Storage)
 * ================================================================
 */

if (!function_exists('get_option')) {
    /**
     * Get option value (WordPress-style)
     * 
     * Maps to storage_get() with scope='application'.
     * For site-wide options (e.g. performance) use $lang = 'all' so the same value applies to all languages.
     * 
     * @param string $option Option name
     * @param mixed $default Default value if option doesn't exist
     * @param string $lang Language code (default: APP_LANG). Use 'all' for global options (e.g. performance).
     * @return mixed Option value
     * 
     * @example
     * $site_title = get_option('site_title', 'My Website');
     * $minify_css = get_option('minify_css', false, 'all');
     */
    function get_option($option, $default = null, $lang = APP_LANG)
    {
        return storage_get($option, 'application', S_REQUEST('post_lang') ?? $lang, $default);
    }
}

if (!function_exists('set_option')) {
    /**
     * Update option value (WordPress-style)
     * 
     * Creates option if it doesn't exist, updates if it does.
     * For site-wide options (e.g. performance) use $lang = 'all'.
     * 
     * @param string $option Option name
     * @param mixed $value Option value
     * @param string $lang Language code (default: APP_LANG). Use 'all' for global options.
     * @return bool Success
     * 
     * @example
     * set_option('site_title', 'My Awesome Site');
     * set_option('minify_css', true, 'all');
     */
    function set_option($option, $value, $lang = APP_LANG)
    {
        return storage_set($option, $value, 'application', S_REQUEST('post_lang') ?? $lang);
    }
}


if (!function_exists('delete_option')) {
    /**
     * Delete option (WordPress-style)
     * 
     * Maps to storage_delete() with scope='application'.
     * Use $lang = 'all' when deleting a global option (e.g. performance).
     * 
     * @param string $option Option name
     * @param string $lang Language code (default: APP_LANG). Use 'all' for global options.
     * @return bool Success
     * 
     * @example
     * delete_option('old_feature_flag');
     * delete_option('temporary_token', 'all');
     */
    function delete_option($option, $lang = APP_LANG)
    {
        return storage_delete($option, 'application', S_REQUEST('post_lang') ?? $lang);
    }
}

if (!function_exists('get_theme_option')) {
    /**
     * Get theme option (WordPress-style)
     * 
     * Maps to storage_get() with scope='themes/{active_theme}'
     * 
     * @param string $option Option name
     * @param mixed $default Default value
     * @param string|null $theme Theme slug (null = active theme)
     * @param string $lang Language code
     * @return mixed Option value
     * 
     * @example
     * $logo = get_theme_option('logo_url', '/default-logo.png');
     * $color = get_theme_option('primary_color', '#667eea');
     */
    function get_theme_option($option, $default = null, $theme = null, $lang = APP_LANG)
    {
        $theme = $theme ?? (defined('APP_THEME') ? APP_THEME : 'default');
        return storage_get($option, "themes/{$theme}", $lang, $default);
    }
}

if (!function_exists('set_theme_option')) {
    /**
     * Update theme option (WordPress-style)
     * 
     * @param string $option Option name
     * @param mixed $value Option value
     * @param string|null $theme Theme slug (null = active theme)
     * @param string $lang Language code
     * @return bool Success
     * 
     * @example
     * set_theme_option('logo_url', '/uploads/new-logo.png');
     * set_theme_option('show_breadcrumbs', true);
     */
    function set_theme_option($option, $value, $theme = null, $lang = APP_LANG)
    {
        $theme = $theme ?? (defined('APP_THEME') ? APP_THEME : 'default');
        return storage_set($option, $value, "themes/{$theme}", $lang);
    }
}
