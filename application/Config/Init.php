<?php
/**
 * Application Initialization
 * 
 * Loads and initializes framework components:
 * 1. Auto-parse JSON input (for API requests)
 * 2. Essential helpers (uri, security, storage, hooks, users)
 * 3. Languages configuration (APP_LANG, APP_LANGUAGES)
 * 4. Posttypes for current language (APP_POSTTYPES)
 * 5. Composer autoloader (PSR-4 classes)
 * 
 * Dependencies: Requires Constants.php to be loaded first
 * 
 * @package Application\Config
 * @version 2.1.13
 */

// Prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

// =================================================================
// AUTO-PARSE JSON INPUT (for API requests)
// =================================================================
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Config\Init::parseJsonInput');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// Parse JSON for POST, PUT, PATCH, DELETE methods
if (
    in_array($method, ['POST', 'PUT', 'DELETE']) &&
    stripos($contentType, 'application/json') === 0
) {
    $json = file_get_contents('php://input');
    
    if ($json !== false && $json !== '') {
        $data = json_decode($json, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($data) && !empty($data)) {
            // For PUT/PATCH/DELETE, also populate $_POST for compatibility
            $_POST = $data;
            switch ($method) {
                case 'PUT':
                    $_PUT = $data;
                    break;
                case 'DELETE':
                    $_DELETE = $data;
                    break;
            }
            $_REQUEST = array_merge($_REQUEST, $data);
        } elseif (APP_DEBUGBAR && json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Parse Error ({$method}): " . json_last_error_msg());
        }
    }
    
    unset($json, $data);
}

unset($method, $contentType);
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::stop('Config\Init::parseJsonInput');
}

// =================================================================
// LOAD LANGUAGES (APP_LANG, APP_LANGUAGES, APP_LANG_DF)
// =================================================================
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Config\Init::loadLanguages');
}
try{
    require PATH_APP . 'Config' . DIRECTORY_SEPARATOR . 'Languages.php';
} catch (\Throwable $e) {
    _critical_error('Languages.php not found in Config/Languages.php', 500);
}
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::stop('Config\Init::loadLanguages');
}

// =================================================================
// LOAD POSTTYPES FOR CURRENT LANGUAGE
// =================================================================
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::mark('Config\Init::loadPosttypes');
}
try{
    $objectPosttypes = require PATH_APP . 'Config' . DIRECTORY_SEPARATOR . 'Posttype.php';
} catch (\Throwable $e) {
    _critical_error('Posttype.php not found in Config/Posttype.php', 500);
}
$listPosttypes = [];
if (!empty($objectPosttypes) && is_array($objectPosttypes)) {
    foreach ($objectPosttypes as $key => $item) {
        // Filter by current language
        if (isset($item['languages']) && 
            (in_array(APP_LANG, $item['languages']) || in_array('all', $item['languages']))
        ) {
            $listPosttypes[] = $key;
        }
    }
}
// Always include 'pages' posttype
$listPosttypes[] = 'pages';
define('APP_POSTTYPES', array_unique($listPosttypes));
unset($objectPosttypes, $listPosttypes);
if (APP_DEBUGBAR) {
    \System\Libraries\Monitor::stop('Config\Init::loadPosttypes');
}