<?php

/**
 * Application Constants
 * 
 * Defines all framework constants:
 * - PATH_* (Application paths)
 * - APP_* (Application settings from configs)
 * 
 * Can be loaded independently for CLI commands without full bootstrap
 * 
 * Dependencies: Requires PATH_ROOT to be defined
 * 
 * @package Application\Config
 */
// Prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

// Debug mode and performance tracking
define('APP_DEBUG', true); //For display errors of PHP
define('APP_DEBUGBAR', true); //For display debugbar of application
define('APP_DEVELOPMENT', true); //For development mode of application

// Performance measurement - Set at the very beginning to track all execution time
if (APP_DEBUGBAR) {
    define('APP_START_TIME', microtime(true));
    define('APP_START_MEMORY', memory_get_usage());
}
// =================================================================
// PATH CONSTANTS (No realpath() for performance - PHP will error if wrong)
// =================================================================
define('PATH_APP', PATH_ROOT . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('PATH_SYS', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
// Content Uploads, Themes, Plugins can access from Client HTTP Request (but block Dangerous extension file)
define('PATH_CONTENT', PATH_ROOT . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR);
define('PATH_PLUGINS', PATH_CONTENT . 'plugins' . DIRECTORY_SEPARATOR);
define('PATH_THEMES', PATH_CONTENT . 'themes' . DIRECTORY_SEPARATOR);
// Writeable is for temporary files, cache, logs, etc. Can not access from HTTP Request
define('PATH_WRITE', PATH_ROOT . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR);
define('PATH_TEMP', PATH_WRITE . 'temp' . DIRECTORY_SEPARATOR);
define('PATH_CONTENT_ASSETS', PATH_CONTENT . 'assets' . DIRECTORY_SEPARATOR);

if (APP_DEBUGBAR) { // Error reporting for display errors
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);
} else {
    ini_set('display_startup_errors', 0);
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE);
}

/**
 * Temporary monitor helper (works before Monitor class is loaded)
 * 
 * @param string $label Marker label
 * @param string $action 'mark' or 'stop'
 */
function temp_monitor($label, $action = 'mark')
{
    static $temp_monitor;
    if (!defined('APP_DEBUGBAR') || !APP_DEBUGBAR) {
        return;
    }
    if ($action === 'mark') {
        $temp_monitor = [
            'label' => $label,
            'start' => microtime(true),
            'memory_start' => memory_get_usage(),
            'parent' => null,
            'level' => 0,
            'children' => []
        ];
    } elseif ($action === 'stop') {
        $temp_monitor['end'] = microtime(true);
        $temp_monitor['memory_end'] = memory_get_usage();
        return $temp_monitor;
    }
    return null;
}
/**
 * Display critical error (before Composer loaded)
 * 
 * @param string $message Error message
 * @param int $code HTTP status code
 */
function _critical_error($message, $code = 500)
{
    http_response_code($code);

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Critical Error</title>
        <style>
            body { font-family: system-ui, sans-serif; padding: 40px; background: #fee; }
            .error { background: white; padding: 30px; border-radius: 8px; max-width: 700px; margin: 0 auto; border-left: 4px solid #dc2626; }
            h1 { color: #dc2626; margin-top: 0; }
            .message { background: #f9fafb; padding: 15px; border-radius: 6px; font-family: monospace; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>⚠️ Critical Error</h1>
            <div class="message">' . $message . '</div>
            <p><strong>This error occurred during bootstrap initialization.</strong></p>
        </div>
    </body>
    </html>
    ';

    exit($html);
}

// =================================================================
// LOAD CORE HELPER (needed for config() function)
// =================================================================
temp_monitor('Application::coreFunction', 'mark');
require_once PATH_SYS . 'Helpers' . DIRECTORY_SEPARATOR . 'Core_helper.php';
$monitor_core = temp_monitor('Application::coreFunction', 'stop');

// =================================================================
// LOAD COMPOSER AUTOLOADER
// =================================================================
temp_monitor('Application::autoVendor', 'mark');
$autoloadFile = PATH_ROOT . '/vendor/autoload.php';
try {
    require_once $autoloadFile;
} catch (\Throwable $e) {
    _critical_error('Vendor/autoload.php not found in ' . $autoloadFile . '. Please install dependencies using Composer: <b>composer install</b>', 500);
}
unset($autoloadFile);
$monitor_autoload = temp_monitor('Application::autoVendor', 'stop');


// =================================================================
// APPLICATION CONSTANTS (from configs)
// =================================================================

if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Application::Bootstrap');
    \System\Libraries\Monitor::addItem($monitor_core);
    \System\Libraries\Monitor::addItem($monitor_autoload);
    unset($monitor_core, $monitor_autoload);
}

// Database prefix (required for table names)
// Support both simple config (array) and complex config (require result)
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Config\Constants::loadDatabaseConfig');
}
$dbConfig = config('database');
if (empty($dbConfig)) {
    _critical_error('Database configuration not found in Config.php', 500);
}

$dbPrefix = '';

// Check if complex config (has 'connections' and 'nodes')
if (isset($dbConfig['connections']) && isset($dbConfig['nodes'])) {
    // Complex config: get prefix from connections
    $dbDefault = $dbConfig['default'] ?? 'mysql_main';
    $dbConnections = $dbConfig['connections'] ?? [];
    if (!empty($dbConnections[$dbDefault])) {
        $dbPrefix = $dbConnections[$dbDefault]['prefix'] ?? '';
    }
} else {
    // Simple config: get prefix directly
    $dbPrefix = $dbConfig['prefix'] ?? '';
}

define('APP_PREFIX', $dbPrefix);
unset($dbConfig, $dbDefault, $dbConnections, $dbPrefix);
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::stop('Config\Constants::loadDatabaseConfig');
}

// Upload path (simple concatenation, no I/O)
$uploadsBasePath = trim( config('files', 'Uploads')['base_path'] ?? 'uploads', '/');
define('PATH_UPLOADS', PATH_CONTENT . $uploadsBasePath . DIRECTORY_SEPARATOR);
unset($uploadsBasePath);

// =================================================================
// LOAD ESSENTIAL HELPERS
// =================================================================
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Config\Constants::loadHelpers');
}
load_helpers(['uri', 'string', 'security', 'languages', 'storage', 'hooks', 'users']);
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::stop('Config\Constants::loadHelpers');
}

// =================================================================
// LOAD THEME CONSTANTS
// =================================================================
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Config\Constants::loadThemeConfig');
}
$defaultTheme = trim( config('app_theme'), '/' ) ?? 'default';
$resolveThemeName = static function ($optionRows, $fallback) {
    if (!empty($optionRows) && is_array($optionRows) && !empty($optionRows[0]['name'])) {
        $candidate = trim((string) $optionRows[0]['name'], '/');
        if ($candidate !== '') {
            return $candidate;
        }
    }
    return $fallback;
};
$themeWebName = $defaultTheme;
$themeAdminName = 'giao-dien-admin';
try {
    $themeWebRows = option('themes_active', 'all');
    $themeWebName = $resolveThemeName($themeWebRows, $defaultTheme);

    $themeAdminRows = option('themes_backend', 'all');
    $themeAdminName = $resolveThemeName($themeAdminRows, $themeWebName);
} catch (\Exception $e) {
}

$themeScope = 'web';
if (PHP_SAPI !== 'cli') {
    $uri = trim((string) request_uri(), '/');
    $segments = $uri === '' ? [] : explode('/', $uri);
    $first = $segments[0] ?? '';
    $second = $segments[1] ?? '';
    $isLangPrefix = (bool) preg_match('/^[a-z]{2}$/i', $first);
    if ($first === 'admin' || ($isLangPrefix && $second === 'admin')) {
        $themeScope = 'admin';
    }
}

define('APP_THEME_SCOPE', $themeScope);
define('APP_THEME_WEB_NAME', $themeWebName);
define('APP_THEME_WEB_PATH', PATH_THEMES . APP_THEME_WEB_NAME . DIRECTORY_SEPARATOR);
define('APP_THEME_ADMIN_NAME', $themeAdminName);
define('APP_THEME_ADMIN_PATH', PATH_THEMES . APP_THEME_ADMIN_NAME . DIRECTORY_SEPARATOR);

$themeName = APP_THEME_SCOPE === 'admin' ? APP_THEME_ADMIN_NAME : APP_THEME_WEB_NAME;
define('APP_THEME_NAME', $themeName);
define('APP_THEME_PATH', PATH_THEMES . APP_THEME_NAME . DIRECTORY_SEPARATOR);
unset($defaultTheme, $resolveThemeName, $themeWebName, $themeAdminName, $themeName, $themeScope, $themeWebRows, $themeAdminRows, $uri, $segments, $first, $second, $isLangPrefix);
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::stop('Config\Constants::loadThemeConfig');
}

// =================================================================
// MULTI-CURRENCY CONSTANT
// =================================================================
// System currency (for database storage and calculations)
define('APP_CURRENCY_DF', 'USD');
// Display currency (can be overridden by user preference)
// Will be set from ec_options or user session in runtime
if (!defined('APP_CURRENCY')) {
    define('APP_CURRENCY', APP_CURRENCY_DF);
}
