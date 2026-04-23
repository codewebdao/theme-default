<?php
// Prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}
// Database charset settings
$db_charset = 'utf8mb4';
$db_collate = 'utf8mb4_unicode_ci';
$db_timezone = '+07:00';
$app_timezone = 'Asia/Ho_Chi_Minh';
 
return [
    'backups' => true, //Enable Backups (May be not secure if Admin Password is leaked)
    'app_url' => 'https://theme-default.vn', //Base URL of the application
    'app_name' => 'Theme Default', //Name of the application
    'app_timezone' => $app_timezone, //Timezone of the application (Default is Asia/Ho_Chi_Minh)
    'app_id' => '123456', //App ID for JWT (Default is 123456)
    'app_secret' => 'MoviesApiSecretKey@2024', //App Secret for JWT (Default is MoviesApiSecretKey@2024)
    'app_theme' => 'giao-dien-education', //App Theme Default if can not call to DB)

    // DATABASE Configuration
    // Option 1: Simple Single DB (default - uncomment below)
    'database' => [
        // Single database connection (read and write use same DB)
        'host'      => 'localhost',
        'port'      => 3306,
        'dbname'    => 'theme-default.vn',
        'username'  => 'root',
        'password'  => '',
        'prefix'    => 'fast_', // Table prefix (empty string if no prefix)
        'driver'    => 'mysql', // mysql, pgsql, sqlite
        'charset'   => $db_charset,
        'collate'   => $db_collate,
        // Query performance
        'slow_ms'   => 500, // Queries > 500ms logged as slow
        // Retry configuration
        'retry'     => [
            'deadlock' => 1, // Retry on deadlock
            'lost'     => 1, // Retry on connection lost
        ],
        // Query logging configuration
        'logging' => [
            'info' => [
                'enabled' => false, // Log all queries
                'path'    => PATH_WRITE . '/logs/query.log',
            ],
            'slow' => [
                'enabled' => true, // Log slow queries only
                'path'    => PATH_WRITE . '/logs/slow.log',
            ],
            'error' => [
                'enabled' => true,
                'path'    => PATH_WRITE . '/logs/db_error.log',
            ],
        ],

        // Timezone for database connection
        'timezone'  => $db_timezone, // Change to your timezone
    ],

    // Option 2: Multi-DB (read/write separation) - uncomment below and comment Option 1 above
    // 'database' => require __DIR__ . '/MultiDatabase.php',
];
  