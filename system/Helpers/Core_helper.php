<?php

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Logger;
use App\Libraries\Fastlang as Flang;

/**
 * load_helpers function
 * Load list of specified helpers
 * 
 * @param array $helpers List of helpers to load
 */
function load_helpers(array $helpers = [], $plugin = '')
{
    // Global variable to track loaded helpers
    global $fast_helpers;
    // If variable not initialized, create array to store loaded helpers
    if (!isset($fast_helpers)) {
        $fast_helpers = [];
    }
    if (!empty($plugin)) {
        $plugin = ucfirst($plugin);
        foreach ($helpers as $helper) {
            if (!in_array($plugin . '.' . $helper, $fast_helpers)) {
                $helperPath = PATH_PLUGINS . $plugin . '/Helpers/' . ucfirst($helper) . '_helper.php';
                if (file_exists($helperPath)) {
                    $fast_helpers[] = $plugin . '.' . $helper;
                    require_once $helperPath;
                }
            }
        }
    } else {
        //Load Helpers of System and Application
        foreach ($helpers as $helper) {
            // Check if helper has been loaded before
            if (!in_array($helper, $fast_helpers)) {
                $helperPath = PATH_SYS . 'Helpers/' . ucfirst($helper) . '_helper.php';
                if (file_exists($helperPath)) {
                    $fast_helpers[] = $helper;
                    require_once $helperPath;
                } else {
                    $helperPath = PATH_APP . 'Helpers/' . ucfirst($helper) . '_helper.php';
                    if (file_exists($helperPath)) {
                        $fast_helpers[] = $helper;
                        require_once $helperPath;
                    } else {
                        throw new \System\Core\AppException("Helper not found: " . $helper . " - " . $helperPath);
                    }
                }
            }
        }
    }
}


if (!function_exists('is_sqltable')) {
    /**
     * Validate SQL table name.
     *
     * @param string $str Table name to check.
     * @return bool Returns true if valid, otherwise returns false.
     */
    function is_sqltable($str)
    {
        // Regex check:
        // ^                    : Start of string.
        // (?!(...))           : Negative lookahead excludes forbidden SQL keywords.
        // (select|order|...)   : List of keywords (case insensitive with /i flag).
        // [A-Za-z0-9_]+        : Allows letters, numbers and underscores.
        // $                    : End of string.
        $pattern = '/^(?!(select|order|table|group|where|index|insert|update|delete|from|join|union|having|into|alter|drop|create)$)[A-Za-z0-9_]+$/i';
        return (bool) preg_match($pattern, $str);
    }
}

if (!function_exists('is_sqlcolumn')) {
    /**
     * Validate SQL column name.
     *
     * @param string $str Column name to check.
     * @return bool Returns true if valid, otherwise returns false.
     */
    function is_sqlcolumn($str)
    {
        // Use same regex as table name
        $pattern = '/^(?!(select|order|table|group|where|index|insert|update|delete|from|join|union|having|into|alter|drop|create)$)[A-Za-z0-9_]+$/i';
        return (bool) preg_match($pattern, $str);
    }
}

if (!function_exists('is_slug')) {
    /**
     * Validate slug.
     *
     * @param string $str Slug to check.
     * @return bool Returns true if slug is valid, otherwise returns false.
     */
    function is_slug($str)
    {
        // Regex for slug: allows letters, numbers, hyphens and underscores.
        // ^[A-Za-z0-9\-_]+$ : Start and end with valid characters.
        $pattern = '/^[A-Za-z0-9\-_]+$/i';
        return (bool) preg_match($pattern, $str);
    }
}

if (!function_exists('is_ajax')) {
    function is_ajax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

if (!function_exists('_sqlname')) {
    /**
     * Normalize and return a safe SQL identifier (e.g., table name).
     * - Transliterate to ASCII (if iconv available), lowercase.
     * - Keep only [a-z0-9_], collapse multiple '_' and trim edges.
     * - Ensure first char is [a-z_] (prefix 't_' if needed).
     * - Enforce max length (default 63 for PostgreSQL compatibility).
     * - If the whole name equals a reserved keyword, append suffix.
     *
     * @param string $str             Raw name.
     * @param int    $maxLen          Max length (63=Postgres, 64=MySQL).
     * @param string $conflictSuffix  Suffix when matching a reserved word.
     * @return string                 Safe SQL identifier.
     */
    function _sqlname(string $str, int $maxLen = 63, string $conflictSuffix = '_tbl'): string
    {
        $s = trim($str);

        // 1) Transliterate (optional)
        if ($s !== '' && function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($converted !== false) $s = $converted;
        }

        // 2) Lowercase
        $s = strtolower($s);

        // 3) Keep [a-z0-9_], replace others with '_'
        $s = preg_replace('/[^a-z0-9_]+/', '_', $s) ?? '';

        // 4) Collapse '_' and trim
        $s = preg_replace('/_+/', '_', $s) ?? '';
        $s = trim($s, '_');

        // 5) Fallback if empty
        if ($s === '') $s = 't';

        // 6) Ensure valid starting char
        if (!preg_match('/^[a-z_]/', $s)) $s = 't_' . $s;

        // 7) Pre-trim to max length
        if ($maxLen > 0 && strlen($s) > $maxLen) {
            $s = rtrim(substr($s, 0, $maxLen), '_');
            if ($s === '') $s = 't';
        }

        // 8) Reserved words check (match full name only)
        static $reserved = null;
        if ($reserved === null) {
            $reserved = array_flip([
                'select',
                'insert',
                'update',
                'delete',
                'from',
                'where',
                'group',
                'order',
                'by',
                'having',
                'into',
                'limit',
                'offset',
                'join',
                'inner',
                'left',
                'right',
                'full',
                'on',
                'union',
                'all',
                'distinct',
                'and',
                'or',
                'not',
                'null',
                'true',
                'false',
                'as',
                'case',
                'when',
                'then',
                'else',
                'end',
                'create',
                'table',
                'index',
                'unique',
                'primary',
                'key',
                'constraint',
                'references',
                'foreign',
                'check',
                'default',
                'view',
                'sequence',
                'function',
                'procedure',
                'trigger',
                'alter',
                'drop',
                'rename',
                'database',
                'schema',
                'grant',
                'revoke',
                'user',
                'role',
                'transaction',
                'commit',
                'rollback',
                'savepoint',
                'with',
                'recursive',
                'materialized',
                'temporary',
                'temp',
                'cascade',
                'restrict',
                'if',
                'exists'
            ]);
        }

        if (isset($reserved[$s])) {
            $avail = max(1, $maxLen - strlen($conflictSuffix));
            if (strlen($s) > $avail) {
                $s = rtrim(substr($s, 0, $avail), '_');
                if ($s === '') $s = 't';
            }
            $s .= $conflictSuffix;
        }

        // 9) Final length guard
        if ($maxLen > 0 && strlen($s) > $maxLen) {
            $s = rtrim(substr($s, 0, $maxLen), '_');
            if ($s === '') $s = 't';
        }

        return $s;
    }
}



function posttype($key = null, $refresh = false)
{
    static $global_posttypes;
    if ($refresh || !isset($global_posttypes) || !is_array($global_posttypes)) {
        $global_posttypes = require PATH_APP . 'Config/Posttype.php';
        if (empty($global_posttypes) || !is_array($global_posttypes)) {
            $global_posttypes = [];
        }
    }
    if (is_null($key)) {
        return $global_posttypes;
    }
    return $global_posttypes[$key] ?? null;
}

if (!function_exists('posttype_config')) {
    /**
     * Get posttype configuration without cache (fresh/realtime data)
     * 
     * Use cases:
     * - After updating posttype config programmatically
     * - When you need to verify changes immediately
     * - In admin panels where config might change frequently
     * 
     * @param string|null $key Posttype slug (null = all posttypes)
     * @return array|null Posttype config or null if not found
     */
    function posttype_config($key = null)
    {
        // Load fresh data (no cache)
        $fresh_posttypes = require PATH_APP . 'Config/Posttype.php';

        if (empty($fresh_posttypes) || !is_array($fresh_posttypes)) {
            return is_null($key) ? [] : null;
        }

        if (is_null($key)) {
            return $fresh_posttypes;
        }

        return $fresh_posttypes[$key] ?? null;
    }
}

if (!function_exists('posttype_active')) {
    /**
     * Get posttype data from database with static cache
     * 
     * Similar to posttype() but loads from database instead of config file.
     * Returns array indexed by slug for easy lookup.
     * 
     * @param string|null $key Posttype slug (null = all posttypes)
     * @param bool $refresh Force refresh from database (bypass cache)
     * @return array|null Posttype data or null if not found
     * 
     * @example
     * // Get all posttypes
     * $all = posttype_active();
     * 
     * // Get specific posttype by slug
     * $product = posttype_active('products');
     * 
     * // Force refresh from database
     * $fresh = posttype_active(null, true);
     */
    function posttype_active($key = null, $refresh = false)
    {
        static $active_posttypes;
        if ($refresh || !isset($active_posttypes) || !is_array($active_posttypes)) {
            try {
                $rows = \System\Database\DB::table('posttype')->where('status', 'active')->get();

                $active_posttypes = [];
                foreach ($rows as $row) {
                    // Index by slug for easy lookup (consistent with posttype() function)
                    if (isset($row['slug']) && !empty($row['slug'])) {
                        $row['languages'] = _json_decode($row['languages']);
                        $row['terms'] = _json_decode($row['terms']);
                        $row['fields'] = _json_decode($row['fields']);
                        $active_posttypes[$row['slug']] = $row;
                    }
                }
            } catch (\Exception $e) {
                error_log("Error loading posttypes from database: " . $e->getMessage());
                if (!isset($active_posttypes) || !is_array($active_posttypes)) {
                    $active_posttypes = [];
                }
            }
        }

        if (is_null($key)) {
            return $active_posttypes;
        }

        return $active_posttypes[$key] ?? null;
    }
}



if (!function_exists('posttype_db')) {
    /**
     * Get posttype data from database with static cache
     * 
     * Similar to posttype() but loads from database instead of config file.
     * Returns array indexed by slug for easy lookup.
     * 
     * @param string|null $key Posttype slug (null = all posttypes)
     * @param bool $refresh Force refresh from database (bypass cache)
     * @return array|null Posttype data or null if not found
     * 
     * @example
     * // Get all posttypes
     * $all = posttype_db();
     * 
     * // Get specific posttype by slug
     * $product = posttype_db('products');
     * 
     * // Force refresh from database
     * $fresh = posttype_db(null, true);
     */
    function posttype_db($key = null, $refresh = false)
    {
        static $db_posttypes;
        if ($refresh || !isset($db_posttypes) || !is_array($db_posttypes)) {
            try {
                $rows = \System\Database\DB::table('posttype')->get();

                $db_posttypes = [];
                foreach ($rows as $row) {
                    // Index by slug for easy lookup (consistent with posttype() function)
                    if (isset($row['slug']) && !empty($row['slug'])) {
                        $row['languages'] = _json_decode($row['languages']);
                        $row['terms'] = _json_decode($row['terms']);
                        $row['fields'] = _json_decode($row['fields']);
                        $db_posttypes[$row['slug']] = $row;
                    }
                }
            } catch (\Exception $e) {
                error_log("Error loading posttypes from database: " . $e->getMessage());
                if (!isset($db_posttypes) || !is_array($db_posttypes)) {
                    $db_posttypes = [];
                }
            }
        }

        if (is_null($key)) {
            return $db_posttypes;
        }

        return $db_posttypes[$key] ?? null;
    }
}

if (!function_exists('posttype_post_lang')) {
    /**
     * Validate and auto-correct language for posttype
     * 
     * Returns the validated/corrected language, or null if posttype has no languages.
     * Auto-corrects language based on priority:
     * 1. 'all' (if in languages array)
     * 2. APP_LANG_DF (if in languages array)
     * 3. APP_LANG (if in languages array)
     * 4. First language in languages array
     * 
     * @param array|string $posttype Posttype array or slug (if slug, will load from posttype() cache)
     * @param string|null $currentLang Current language to validate
     * @return string|null Validated/corrected language, or null if posttype has no languages
     * 
     * @example
     * // Basic usage
     * $currentLang = posttype_post_lang($posttype, $this->postLang);
     * if ($currentLang === null) {
     *     Session::flash('error', __('Posttype has no languages'));
     *     redirect(admin_url('/'));
     *     return;
     * }
     * if ($currentLang != $this->postLang) {
     *     redirect(admin_url('posts') . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang);
     *     return;
     * }
     * 
     * // With posttype slug
     * $currentLang = posttype_post_lang('products', $this->post_lang);
     */
    function posttype_post_lang($posttype, $currentLang = null)
    {
        // Load posttype if slug provided (use posttype() for file cache)
        if (is_string($posttype)) {
            $posttype = posttype($posttype);
        }
        if (empty($posttype) || !is_array($posttype)) {
            return null;
        }
        // Ensure languages is decoded
        if (isset($posttype['languages']) && is_string($posttype['languages'])) {
            $posttype['languages'] = _json_decode($posttype['languages']);
        }
        $languages = $posttype['languages'] ?? [];
        // If no languages defined, return null
        if (empty($languages) || !is_array($languages)) {
            return null;
        }
        // If current language is valid, return it
        if (!empty($currentLang) && in_array($currentLang, $languages)) {
            return $currentLang;
        }
        // Auto-correct language (priority order)
        // Priority 1: 'all' if available
        if (in_array('all', $languages)) {
            return 'all';
        }
        // Priority 2: APP_LANG_DF if available
        if (in_array(APP_LANG_DF, $languages)) {
            return APP_LANG_DF;
        }
        // Priority 3: APP_LANG if available
        if (in_array(APP_LANG, $languages)) {
            return APP_LANG;
        }
        // Priority 4: First language in array
        if (!empty($languages) && count($languages) > 0) {
            return $languages[0];
        }
        // Fallback: return null if no valid language found
        return null;
    }
}

// trans table name posttype $tableName = APP_PREFIX.$data['slug'].'_'.$lang;
if (!function_exists('posttype_name')) {
    /**
     * Get table name for posttype based on slug and language
     * 
     * @param string $slug Posttype slug
     * @param string $lang Language code
     * @return string|null Table name or null if posttype doesn't exist
     */
    function posttype_name($slug, $lang = APP_LANG)
    {
        // Sanitize slug
        $slug = _sqlname($slug);
        // Get posttype configuration using posttype() function
        $posttype = posttype($slug);
        if (empty($posttype)) {
            return null;
        }
        // If first language is 'all' - table name is just slug
        if (empty($posttype['languages']) || $posttype['languages'][0] === 'all') {
            return APP_PREFIX . $slug;
        }
        // If not 'all', table name includes language suffix
        if (empty($lang) || $lang == 'all') {
            return APP_PREFIX . $slug . '_' . APP_LANG;
        } else {
            return APP_PREFIX . $slug . '_' . $lang;
        }
    }
}

if (!function_exists('posttype_lang_exists')) {
    /**
     * Check if posttype exists and language is supported
     * 
     * @param string $slug Posttype slug
     * @param string $lang Language code (optional)
     * @return bool True if posttype exists and language is supported, false otherwise
     */
    function posttype_lang_exists($slug, $lang = APP_LANG)
    {
        // Sanitize slug
        $slug = _sqlname($slug);
        // Get posttype configuration using posttype() function
        $posttype = posttype($slug);
        if (empty($posttype)) {
            return false;
        }
        // Check if first language is 'all' - means supports all languages
        if (empty($posttype['languages']) || $posttype['languages'][0] === 'all') {
            return true;
        }
        // Check if the specified language is in the supported languages array
        if (in_array($lang, $posttype['languages'])) {
            return true;
        }
        return false;
    }
}

// trans table name posttype $tableName = APP_PREFIX.$data['slug'].'_'.$lang;
if (!function_exists('table_posttype')) {
    function table_posttype($slug, $lang = '')
    {
        $slug = _sqlname($slug);
        if (empty($lang) || $lang === 'all') {
            $tableName = APP_PREFIX . $slug;
        } else {
            $tableName = APP_PREFIX . $slug . '_' . $lang;
        }
        return  $tableName;
    }
}
// trans table name relationshop postype
if (!function_exists('table_postrel')) {
    function table_postrel($slug)
    {
        $slug = _sqlname($slug);
        $tableName = APP_PREFIX . $slug . '_rel';
        return  $tableName;
    }
}

// trans table name posts rel
if (!function_exists('table_reference')) {
    /**
     * Get reference table information
     * 
     * Structure (NEW ONLY):
     * {
     *   "type": "Reference",
     *   "field_name": "vendor_id",
     *   "reference": {
     *     "postTypeRef": "ec_vendors",
     *     "selectionMode": "single",
     *     "bidirectional": false,
     *     "reverseField": "",
     *     "search_columns": ["title"],
     *     "display_columns": ["id", "title"],
     *     "filter": [...],
     *     "sort": [...]
     *   }
     * }
     * 
     * @param string $posttype_slug Current posttype slug
     * @param array $field Reference field configuration
     * @return array|null Reference table info
     */
    function table_reference($posttype_slug, $field)
    {
        if (is_object($field)) {
            $field = (array)$field;
        }

        if (empty($posttype_slug) || empty($field['type']) || ucfirst($field['type']) != 'Reference') {
            return null;
        }

        // Get reference config
        $ref = $field['reference'] ?? [];
        $postTypeRef = $ref['postTypeRef'] ?? '';

        if (empty($postTypeRef)) {
            return null;
        }

        // ALWAYS save to current posttype's relation table
        // Direction: current_posttype → referenced_posttype
        return [
            "posttype_slug" => $posttype_slug,
            "field_id" => $field['id'] ?? 0,
            "reference" => $postTypeRef,
            "whereby" => "rel_id",
            "selectby" => "post_id"
        ];
    }
}

function _DateTime($time = '')
{
    return (new \DateTime($time))->format('Y-m-d H:i:s');
}

/**
 * random_string function
 * Generate a random string with desired length
 * 
 * @param int $length Length of random string to generate
 * @return string Generated random string
 */
function random_string($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


/**
 * Function to get configuration from config.php file
 * 
 * @param string $key Name of configuration to get
 * @param string $file Name of file to get
 * @param string $plugin Name of plugin if get Config of Plugin, empty for get Application Config
 * @return mixed Configuration value or default value
 */
function config($keyConfig = '', $file = 'Config', $plugin = '')
{
    if (empty($plugin)) {
        //Load Config of Application
        static $global_configs;
        $file = ucfirst($file);
        if (!is_array($global_configs)) {
            $global_configs = array();
        }
        if (!isset($global_configs[$file])) {
            $filePath = PATH_APP . 'Config/' . $file . '.php';
            if (file_exists($filePath)) {
                $global_configs[$file] = require PATH_APP . 'Config/' . $file . '.php';
                if (empty($global_configs[$file]) || !is_array($global_configs[$file])) {
                    $global_configs[$file] = array();
                    return null;
                }
            } else {
                $global_configs[$file] = array();
                return null;
            }
        }
        return $global_configs[$file][$keyConfig] ?? $global_configs[$file] ?? null;
    } else {
        //load Config of Plugin
        static $plugins_configs;
        $plugin = ucfirst($plugin);
        $file = ucfirst($file);
        if (!is_array($plugins_configs)) {
            $plugins_configs = array();
        }
        if (empty($plugins_configs[$plugin]) || !is_array($plugins_configs[$plugin])) {
            $plugins_configs[$plugin] = array();
        }
        if (!isset($plugins_configs[$plugin][$file])) {
            $pluginConfigPath = PATH_PLUGINS . $plugin . '/Config/' . $file . '.php';
            if (file_exists($pluginConfigPath)) {
                $plugins_configs[$plugin][$file] = require $pluginConfigPath;
                if (empty($plugins_configs[$plugin][$file]) || !is_array($plugins_configs[$plugin][$file])) {
                    $plugins_configs[$plugin][$file] = array();
                    return null;
                }
            } else {
                $plugins_configs[$plugin][$file] = array();
                return null;
            }
        }
        return $plugins_configs[$plugin][$file][$keyConfig] ?? $plugins_configs[$plugin][$file] ?? null;
    }
}

/**
 * Get database configuration in unified format
 * Handles both simple config (from Config.php) and complex config (from Database.php)
 * 
 * @return array Database config with keys: db_host, db_port, db_database, db_username, db_password, db_charset, db_collate, db_prefix
 */
if (!function_exists('db_config')) {
    function db_config()
    {
        static $dbConfigCache = null;

        if ($dbConfigCache !== null) {
            return $dbConfigCache;
        }

        $dbConfig = config('database');

        if (empty($dbConfig)) {
            throw new \RuntimeException('Database configuration not found');
        }

        // Check if complex config (has 'connections' and 'nodes')
        if (isset($dbConfig['connections']) && isset($dbConfig['nodes'])) {
            // Complex config: extract from first write node
            $default = $dbConfig['default'] ?? 'mysql_main';
            $connection = $dbConfig['connections'][$default] ?? [];
            $writeNode = $connection['write'] ?? null;

            if (!$writeNode || !isset($dbConfig['nodes'][$writeNode])) {
                throw new \RuntimeException('Database write node not found in complex config');
            }

            $node = $dbConfig['nodes'][$writeNode];

            // Parse DSN
            $dsn = $node['dsn'] ?? '';
            preg_match('/host=([^;]+)/', $dsn, $hostMatch);
            preg_match('/port=([^;]+)/', $dsn, $portMatch);
            preg_match('/dbname=([^;]+)/', $dsn, $dbnameMatch);
            preg_match('/charset=([^;]+)/', $dsn, $charsetMatch);

            $dbConfigCache = [
                'db_host' => $hostMatch[1] ?? 'localhost',
                'db_port' => $portMatch[1] ?? 3306,
                'db_database' => $dbnameMatch[1] ?? '',
                'db_username' => $node['username'] ?? '',
                'db_password' => $node['password'] ?? '',
                'db_charset' => $charsetMatch[1] ?? 'utf8mb4',
                'db_collate' => 'utf8mb4_unicode_ci', // Default, can be extracted from init command if needed
                'db_prefix' => $connection['prefix'] ?? '',
            ];
        } else {
            // Simple config: use directly
            $dbConfigCache = [
                'db_host' => $dbConfig['host'] ?? 'localhost',
                'db_port' => $dbConfig['port'] ?? 3306,
                'db_database' => $dbConfig['dbname'] ?? '',
                'db_username' => $dbConfig['username'] ?? '',
                'db_password' => $dbConfig['password'] ?? '',
                'db_charset' => $dbConfig['charset'] ?? 'utf8mb4',
                'db_collate' => $dbConfig['collate'] ?? 'utf8mb4_unicode_ci',
                'db_prefix' => $dbConfig['prefix'] ?? '',
            ];
        }

        return $dbConfigCache;
    }
}

/* 
* Function get config_roles 
* Get config roles from roles.php file and all plugins active plugins/plugin_name/Config/roles.php file
* Merger all config roles from all files and return array
*/
if (!function_exists('config_roles')) {
    function config_roles($roleName = '')
    {
        static $global_config_roles;
        $roleName = function_exists('url_slug') ? url_slug($roleName) : preg_replace('/[^a-zA-Z0-9_]/', '', $roleName);
        if (strlen($roleName) > 64) {
            $roleName = substr($roleName, 0, 63);
        }
        //Fix Roles Name maximium 64 characters
        if (!is_array($global_config_roles)) {
            $global_config_roles = array();
            //Load Config Roles of Application
            if (file_exists(PATH_APP . 'Config/Roles.php')) {
                $global_config_roles = require PATH_APP . 'Config/Roles.php';
                if (empty($global_config_roles) || !is_array($global_config_roles)) {
                    $global_config_roles = array();
                }
            }
            //Load Config Roles of Plugins into temp array $pluginsRoles
            $pluginsRoles = [];
            $activePlugins = _json_decode(option('plugins_active', 'all'));
            if (is_array($activePlugins) && !empty($activePlugins)) {
                foreach ($activePlugins as $plugin) {
                    $pluginName = $plugin['name'] ?? '';
                    if (empty($pluginName)) continue;
                    $pluginConfigPath = PATH_PLUGINS . $pluginName . '/Config/Roles.php';
                    if (file_exists($pluginConfigPath)) {
                        $roleData = require $pluginConfigPath;
                        if (!empty($roleData) && is_array($roleData)) {
                            $pluginsRoles[] = $roleData;
                        }
                    }
                }
            }
            if (!empty($pluginsRoles)) {
                //Sort array $pluginsRoles by item['order'] ASC
                foreach ($pluginsRoles as $key => $role) {
                    if (!isset($role['order'])) {
                        $pluginsRoles[$key]['order'] = 1;
                    }
                }
                usort($pluginsRoles, function ($a, $b) {
                    return $a['order'] - $b['order'];
                });
                //Merge value array item of $pluginsRoles into $global_config_roles
                foreach ($pluginsRoles as $key => $roleData) {
                    $type = $roleData['roles_type'] ?? 'merge';
                    unset($roleData['roles_type']);
                    unset($roleData['order']);

                    switch ($type) {
                        case 'add':
                            // ADD: Only add new role, do not overwrite existing role
                            foreach ($roleData as $roleKey => $roleValue) {
                                if (!isset($global_config_roles[$roleKey])) {
                                    $global_config_roles[$roleKey] = $roleValue;
                                }
                            }
                            break;

                        case 'replace':
                            // REPLACE: Overwrite completely, plugin is the main source
                            foreach ($roleData as $roleKey => $roleValue) {
                                $global_config_roles[$roleKey] = $roleValue;
                            }
                            break;

                        case 'merge':
                        default:
                            // Merge strategy: Merge permissions only, not name/description
                            $global_config_roles = array_merge_recursive($global_config_roles, $roleData);
                            /*
                            foreach ($roleData as $roleKey => $roleValue) {
                                if (!isset($global_config_roles[$roleKey])) {
                                    // Role chưa tồn tại → add new
                                    $global_config_roles[$roleKey] = $roleValue;
                                } else {
                                    // Role đã tồn tại → merge permissions only
                                    if (isset($roleValue['permissions']) && is_array($roleValue['permissions'])) {
                                        foreach ($roleValue['permissions'] as $controller => $actions) {
                                            if (!isset($global_config_roles[$roleKey]['permissions'][$controller])) {
                                                $global_config_roles[$roleKey]['permissions'][$controller] = $actions;
                                            } else {
                                                // Merge actions and remove duplicates
                                                $global_config_roles[$roleKey]['permissions'][$controller] = array_values(
                                                    array_unique(
                                                        array_merge(
                                                            $global_config_roles[$roleKey]['permissions'][$controller],
                                                            $actions
                                                        )
                                                    )
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                                */
                            break;
                    }
                }
            }
            foreach ($global_config_roles as $key => $role) {
                if (strlen($key) > 64) {
                    $newKey = substr($key, 0, 63);
                    $global_config_roles[$newKey] = $global_config_roles[$key];
                    unset($global_config_roles[$key]);
                }
            }
        }
        if (!empty($roleName)) {
            return $global_config_roles[$roleName] ?? array();
        }
        return $global_config_roles; //Return all config roles
    }
}

if (!function_exists('user_permissions')) {
    /**
     * Merge user permissions with role base permissions
     * 
     * Apply add/remove overrides to base role permissions
     * 
     * @param string $role User role (admin, moderator, author, member)
     * @param array|string|null $permissions User-specific permissions override
     *                          Format: {"add": {...}, "remove": {...}}
     * @return array Final merged permissions
     * 
     * @example
     * $role = 'moderator';
     * $permissions = [
     *   'add' => [
     *     'Backend\Files' => ['manage'],
     *     'Backend\Posts' => ['export']
     *   ],
     *   'remove' => [
     *     'Backend\Users' => ['delete'],
     *     'Backend\Posts' => ['delete']
     *   ]
     * ];
     * $final = user_permissions($role, $permissions);
     */
    function user_permissions($role, $permissions = null)
    {
        // Get base role permissions from config
        $roleConfig = config_roles($role);
        $basePermissions = $roleConfig['permissions'] ?? [];

        // If no custom permissions, return base permissions
        if (empty($permissions)) {
            return $basePermissions;
        }

        // Parse permissions if string (JSON)
        if (is_string($permissions)) {
            $permissions = _json_decode($permissions);
        }

        if (!is_array($permissions)) {
            return $basePermissions;
        }

        // Clone base permissions for modification
        $finalPermissions = $basePermissions;

        // Apply "remove" first (subtract permissions)
        if (!empty($permissions['remove']) && is_array($permissions['remove'])) {
            foreach ($permissions['remove'] as $controller => $actions) {
                if (!is_array($actions)) {
                    continue;
                }

                if (isset($finalPermissions[$controller])) {
                    // Remove specified actions
                    $finalPermissions[$controller] = array_diff(
                        $finalPermissions[$controller],
                        $actions
                    );

                    // Remove controller completely if no actions left
                    if (empty($finalPermissions[$controller])) {
                        unset($finalPermissions[$controller]);
                    } else {
                        // Re-index array
                        $finalPermissions[$controller] = array_values($finalPermissions[$controller]);
                    }
                }
            }
        }

        // Apply "add" second (add new permissions)
        if (!empty($permissions['add']) && is_array($permissions['add'])) {
            foreach ($permissions['add'] as $controller => $actions) {
                if (!is_array($actions)) {
                    continue;
                }

                if (!isset($finalPermissions[$controller])) {
                    // Controller doesn't exist, create it
                    $finalPermissions[$controller] = $actions;
                } else {
                    // Merge actions and remove duplicates
                    $finalPermissions[$controller] = array_unique(
                        array_merge($finalPermissions[$controller], $actions)
                    );
                    // Re-index array
                    $finalPermissions[$controller] = array_values($finalPermissions[$controller]);
                }
            }
        }

        return $finalPermissions;
    }
}


if (!function_exists('all_permissions')) {
    /**
     * Get all available permissions from all roles (for admin UI)
     * 
     * Returns unique list of all controllers and actions from all roles
     * Useful for building permissions management UI
     * 
     * @return array All available permissions
     * 
     * @example
     * $available = all_permissions();
     * // Returns:
     * // [
     * //   'Backend\Posts' => ['index', 'add', 'edit', 'delete', ...],
     * //   'Backend\Users' => ['index', 'add', 'edit', ...],
     * //   ...
     * // ]
     */
    function all_permissions()
    {
        $allRoles = config_roles(); // Get all roles
        $allPermissions = [];

        foreach ($allRoles as $roleKey => $roleData) {
            $permissions = $roleData['permissions'] ?? [];

            foreach ($permissions as $controller => $actions) {
                if (!isset($allPermissions[$controller])) {
                    $allPermissions[$controller] = [];
                }

                // Merge actions và loại bỏ duplicates
                $allPermissions[$controller] = array_unique(
                    array_merge($allPermissions[$controller], $actions)
                );
            }
        }

        // Sort controllers alphabetically
        ksort($allPermissions);

        // Sort actions alphabetically within each controller
        foreach ($allPermissions as $controller => $actions) {
            sort($allPermissions[$controller]);
        }

        return $allPermissions;
    }
}

if (!function_exists('override_permissions')) {
    /**
     * Build permissions override structure from base and submitted permissions
     * 
     * @param array $basePermissions Base role permissions
     * @param array $submittedPermissions Permissions from form
     * @return array Override structure ['add' => [...], 'remove' => [...]]
     * 
     * @example
     * $base = user_permissions('moderator', null);
     * $submitted = S_POST('permissions');
     * $override = override_permissions($base, $submitted);
     * // Returns: {"add": {...}, "remove": {...}}
     */
    function override_permissions($basePermissions, $submittedPermissions)
    {
        $add = [];
        $remove = [];

        if (!is_array($basePermissions)) {
            $basePermissions = [];
        }
        if (!is_array($submittedPermissions)) {
            $submittedPermissions = [];
        }

        // Get all unique controllers
        $allControllers = array_unique(array_merge(
            array_keys($basePermissions),
            array_keys($submittedPermissions)
        ));

        foreach ($allControllers as $controller) {
            $baseActions = $basePermissions[$controller] ?? [];
            $submittedActions = $submittedPermissions[$controller] ?? [];

            // Ensure arrays
            if (!is_array($baseActions)) {
                $baseActions = [];
            }
            if (!is_array($submittedActions)) {
                $submittedActions = [];
            }

            // Find actions to ADD (có trong submitted, không có trong base)
            $toAdd = array_diff($submittedActions, $baseActions);
            if (!empty($toAdd)) {
                $add[$controller] = array_values($toAdd);
            }

            // Find actions to REMOVE (có trong base, không có trong submitted)
            $toRemove = array_diff($baseActions, $submittedActions);
            if (!empty($toRemove)) {
                $remove[$controller] = array_values($toRemove);
            }
        }

        return [
            'add' => $add,
            'remove' => $remove
        ];
    }
}



function option($key, $lang = APP_LANG, $get_cache = true)
{
    return storage_get($key, 'application', $lang, null, !$get_cache);
}


if (!function_exists('log_message')) {
    function log_message($level, $message, $context = [])
    {
        if (!function_exists('System\Libraries\Logger::{$level}')) {
            return;
        }
        call_user_func(['System\Libraries\Logger', $level], $message, __FILE__, __LINE__, $context);
    }
}

/**
 * env function
 * Get environment variable value from cache or read from .env file (if not exists in cache)
 * 
 * @param string $key Environment variable name to get
 * @param mixed $default Default value if variable doesn't exist
 * @return mixed Environment variable value or default value
 */
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        // Use static array to store loaded values
        static $env_cache = [];

        // If value already exists in cache, return value from cache
        if (isset($env_cache[$key])) {
            return $env_cache[$key];
        }

        // Get value from environment variable
        $value = getenv($key);

        // If environment variable not found, use default value
        if ($value === false) {
            $env_cache[$key] = $default;
            return $default;
        }

        // Remove unsafe characters and save to cache
        $value = trim($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        // Process special values: true, false, null
        switch (strtolower($value)) {
            case 'true':
                $env_cache[$key] = true;
                break;
            case 'false':
                $env_cache[$key] = false;
                break;
            case 'null':
                $env_cache[$key] = null;
                break;
            default:
                $env_cache[$key] = $value;
        }

        return $env_cache[$key];
    }
}



// debug function
if (!function_exists('prt')) {
    function prt($variable, $name = '')
    {
        $type = gettype($variable);
        echo '<div style="background: #f4f4f4; border: 1px solid #ccc; border-radius: 6px; padding: 12px; margin: 10px 0; font-size: 15px; line-height: 1.5; font-family: Consolas, monospace;">';
        if ($name) {
            echo '<div style="font-weight: bold; color: #333; margin-bottom: 4px;">' . htmlspecialchars($name) . '</div>';
        }
        echo '<div style="color: #888; font-size: 13px; margin-bottom: 6px;">Type Object: <b>' . $type . '</b></div>';
        echo '<pre style="margin:0; background:transparent; border:none; padding:0; color:#222;">';
        if (is_array($variable) || is_object($variable)) {
            echo htmlspecialchars(print_r($variable, true));
        } else {
            echo htmlspecialchars(var_export($variable, true));
        }
        echo '</pre>';
        echo '</div>';
    }
}
function _bytes($size)
{
    $unit = strtolower(substr($size, -1));
    $bytes = (int) $size;
    switch ($unit) {
        case 'g':
            $bytes *= 1024 * 1024 * 1024;
            break;
        case 'm':
            $bytes *= 1024 * 1024;
            break;
        case 'k':
            $bytes *= 1024;
            break;
    }
    return $bytes;
}


if (!function_exists('clear_cache')) {
    function clear_cache($url = '')
    {
        try {
            $cache_path = PATH_WRITE . 'cache/';

            if (empty($url)) {
                // Delete all subdirectories in cache/
                $dirs = glob($cache_path . '*', GLOB_ONLYDIR);
                foreach ($dirs as $dir) {
                    delete_dir_recursive($dir);
                }
                Logger::info('Clear All Cache');
                return true;
            }

            // Remove http:// or https:// from URL
            $url = preg_replace('#^https?://#', '', $url);

            // Split host and path
            $parts = explode('/', $url, 2);
            $host = $parts[0];
            $path = isset($parts[1]) ? '/' . $parts[1] : '';

            // Cache directory path
            $dirPath = rtrim(PATH_WRITE . 'cache/' . $host . $path, '/');
            $file1 = $dirPath . '/index-https.html';
            $file2 = $dirPath . '/index-https.html_gzip';

            if (file_exists($file1)) unlink($file1);
            if (file_exists($file2)) unlink($file2);

            // If directory is empty after deleting files, delete it too
            if (is_dir($dirPath) && count(glob("$dirPath/*")) === 0) {
                rmdir($dirPath);
            }

            Logger::info('Clear Cache: ' . $url);
            return true;
        } catch (\Throwable $e) {
            Logger::info('Clear Cache Failed: ' . $e->getMessage());
            return false;
        }
    }

    function delete_dir_recursive($dir)
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? delete_dir_recursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}

/**
 * dd function (Dump and Die) - Laravel style debug function
 * Dump the given variables and end execution of the script
 * 
 * @param mixed ...$variables Variables to dump
 */
if (!function_exists('dd')) {
    function dd(...$variables)
    {
        // Get backtrace to show file and line where dd() was called
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'Unknown';
        $line = $backtrace[0]['line'] ?? 'Unknown';

        echo '<div style="background: #1e1e1e; color: #fff; font-family: Consolas, Monaco, monospace; padding: 20px; margin: 10px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
        echo '<div style="color: #ff6b6b; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">';
        echo '🔍 DD() called at: ' . htmlspecialchars($file) . ':' . $line;
        echo '</div>';

        if (empty($variables)) {
            echo '<div style="color: #888; font-style: italic;">No variables provided to dd()</div>';
        } else {
            foreach ($variables as $index => $variable) {
                $type = gettype($variable);
                $typeColor = match ($type) {
                    'string' => '#4ecdc4',
                    'integer' => '#45b7d1',
                    'double' => '#45b7d1',
                    'boolean' => '#f39c12',
                    'array' => '#9b59b6',
                    'object' => '#e74c3c',
                    'NULL' => '#95a5a6',
                    default => '#95a5a6'
                };

                echo '<div style="margin-bottom: 20px; border: 1px solid #333; border-radius: 4px; overflow: hidden;">';
                echo '<div style="background: #2c2c2c; padding: 8px 12px; font-weight: bold; color: ' . $typeColor . ';">';
                echo 'Variable #' . ($index + 1) . ' (' . $type . ')';
                echo '</div>';
                echo '<div style="padding: 12px; background: #1e1e1e;">';
                echo '<pre style="margin: 0; color: #fff; font-size: 13px; line-height: 1.4; overflow-x: auto;">';

                if (is_array($variable)) {
                    echo htmlspecialchars(print_r($variable, true));
                } elseif (is_object($variable)) {
                    echo htmlspecialchars(print_r($variable, true));
                    if (method_exists($variable, '__toString')) {
                        echo "\n\nString representation:\n";
                        echo htmlspecialchars((string) $variable);
                    }
                } elseif (is_bool($variable)) {
                    echo $variable ? 'true' : 'false';
                } elseif (is_null($variable)) {
                    echo 'null';
                } else {
                    echo htmlspecialchars(var_export($variable, true));
                }

                echo '</pre>';
                echo '</div>';
                echo '</div>';
            }
        }

        echo '<div style="color: #ff6b6b; font-weight: bold; margin-top: 15px; border-top: 1px solid #333; padding-top: 10px;">';
        echo '🚫 Script execution terminated by dd()';
        echo '</div>';
        echo '</div>';

        // End execution
        exit(1);
    }
}

/**
 * dump function - Laravel style debug function without terminating execution
 * Dump the given variables but continue execution
 * 
 * @param mixed ...$variables Variables to dump
 */
if (!function_exists('dump')) {
    function dump(...$variables)
    {
        // Get backtrace to show file and line where dump() was called
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'Unknown';
        $line = $backtrace[0]['line'] ?? 'Unknown';

        echo '<div style="background: #1e1e1e; color: #fff; font-family: Consolas, Monaco, monospace; padding: 20px; margin: 10px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
        echo '<div style="color: #4ecdc4; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">';
        echo '🔍 DUMP() called at: ' . htmlspecialchars($file) . ':' . $line;
        echo '</div>';

        if (empty($variables)) {
            echo '<div style="color: #888; font-style: italic;">No variables provided to dump()</div>';
        } else {
            foreach ($variables as $index => $variable) {
                $type = gettype($variable);
                $typeColor = match ($type) {
                    'string' => '#4ecdc4',
                    'integer' => '#45b7d1',
                    'double' => '#45b7d1',
                    'boolean' => '#f39c12',
                    'array' => '#9b59b6',
                    'object' => '#e74c3c',
                    'NULL' => '#95a5a6',
                    default => '#95a5a6'
                };

                echo '<div style="margin-bottom: 20px; border: 1px solid #333; border-radius: 4px; overflow: hidden;">';
                echo '<div style="background: #2c2c2c; padding: 8px 12px; font-weight: bold; color: ' . $typeColor . ';">';
                echo 'Variable #' . ($index + 1) . ' (' . $type . ')';
                echo '</div>';
                echo '<div style="padding: 12px; background: #1e1e1e;">';
                echo '<pre style="margin: 0; color: #fff; font-size: 13px; line-height: 1.4; overflow-x: auto;">';

                if (is_array($variable)) {
                    echo htmlspecialchars(print_r($variable, true));
                } elseif (is_object($variable)) {
                    echo htmlspecialchars(print_r($variable, true));
                    if (method_exists($variable, '__toString')) {
                        echo "\n\nString representation:\n";
                        echo htmlspecialchars((string) $variable);
                    }
                } elseif (is_bool($variable)) {
                    echo $variable ? 'true' : 'false';
                } elseif (is_null($variable)) {
                    echo 'null';
                } else {
                    echo htmlspecialchars(var_export($variable, true));
                }

                echo '</pre>';
                echo '</div>';
                echo '</div>';
            }
        }

        echo '<div style="color: #4ecdc4; font-weight: bold; margin-top: 15px; border-top: 1px solid #333; padding-top: 10px;">';
        echo '✅ Script execution continues after dump()';
        echo '</div>';
        echo '</div>';
    }
}

if (!function_exists('_json_decode')) {
    /**
     * Safely decode JSON data
     * @param mixed $data
     * @param mixed $default
     * @return array
     */
    function _json_decode($data, $default = [])
    {
        if (is_array($data)) {
            return $data;
        }
        $decoded = json_decode((string)$data, true);
        return is_array($decoded) ? $decoded : $default;
    }
}

function _cors()
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization,Accept,Content-Type,Origin,User-Agent,Referer,DNT,Cache-Control,X-Mx-ReqToken,Keep-Alive,X-Requested-With,X-CSRF-Token');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
