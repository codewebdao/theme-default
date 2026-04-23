<?php

/**
 * Application Entry Point
 * 
 * Clean bootstrap process:
 * 1. Define root path and version
 * 2. Load constants (paths, app settings)
 * 3. Load initialization (helpers, languages, vendors)
 * 4. Start framework
 * 
 * @package Public
 */

// =================================================================
// APPLICATION VERSION & ROOT PATH
// =================================================================

define('APP_VER', '2.3.1');
define('PATH_ROOT', realpath(__DIR__ . '/../'));

// =================================================================
// LOAD CONSTANTS (Paths, App Settings, Database Prefix)
// =================================================================

require_once PATH_ROOT . '/application/Config/Constants.php';

// =================================================================
// LOAD INITIALIZATION (Helpers, Languages, Posttypes, Composer)
// =================================================================

require_once PATH_ROOT . '/application/Config/Init.php';

// =================================================================
// START FRAMEWORK
// =================================================================

require_once PATH_SYS . 'Core/Bootstrap.php';

$application = new \System\Core\Bootstrap();
$application->run();
