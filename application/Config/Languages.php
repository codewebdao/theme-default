<?php
define('APP_LANG_DF', 'en');
define('APP_LANGUAGES', array (
  'en' => 
  array (
    'name' => 'United States',
    'flag' => 'us',
    'locale' => 'en_US',
  ),
  'vi' => 
  array (
    'name' => 'Việt Nam',
    'flag' => 'vn',
    'locale' => 'vi_VN',
  ),
));

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_path = preg_replace('#/+#', '/', $uri_path); // Replace multiple consecutive / with a single /
$uri_segments = explode('/', trim($uri_path, '/'));

// Check if the first segment is in the language list
if (!empty($uri_segments[0]) && isset(APP_LANGUAGES[$uri_segments[0]])) {
    define('APP_LANG', $uri_segments[0]);
    define('APP_LOCALE', APP_LANGUAGES[$uri_segments[0]]['locale']);
} else {
    if (!empty($_REQUEST['lang']) && isset(APP_LANGUAGES[$_REQUEST['lang']])) {
        define('APP_LANG', $_REQUEST['lang']);
        define('APP_LOCALE', APP_LANGUAGES[$_REQUEST['lang']]['locale']);
    }else{
        define('APP_LANG', APP_LANG_DF);
        define('APP_LOCALE', APP_LANGUAGES[APP_LANG_DF]['locale']);
    }
}
unset($uri_path);
unset($uri_segments);