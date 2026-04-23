<?php

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * base_url function
 * Returns the base URL of the application
 * 
 * @param string $path Relative path to append to base URL
 * @return string Full URL
 */
function base_url($path = '', $lang = APP_LANG) {
	global $base_url;
	if (empty($base_url)){
		$base_url = config('app_url');
    	$base_url = !empty($base_url) ? $base_url : '/';
	}
    //Step 1: Lang Default have Lang Code? True or False
    $rewrite_uri_lang = option('rewrite_uri_lang');
	//Step 2: Split path and query string
    $parts = explode('?', trim($path, '/'), 2);
    $clean_path = trim($parts[0], '/');
    if (!empty($clean_path)) {
        if ($lang != APP_LANG_DF || $rewrite_uri_lang){
            $clean_path = $lang.'/'.$clean_path.'/'; 
        }else{
            $clean_path = $clean_path.'/';
        }
    }else{
        if ($lang != APP_LANG_DF || $rewrite_uri_lang){
            $clean_path = $lang.'/'; 
        }
    }
    $query = isset($parts[1]) && !empty($parts[1]) ? '?' . $parts[1] : '';
    if (empty($query)) {
        return rtrim($base_url, '/') . '/' . $clean_path;
    } else {
        return rtrim($base_url, '/') . '/' . $clean_path . $query;
    }
}

if (!function_exists('lang_url')) {
    /**
     * Change Language of URL
     *
     * @param string $lang Code New Languages (EXP: "en", "vi", ...)
     * @return string Full New Languages URL
     */
   function lang_url($lang = APP_LANG, $uri = null){
        if (empty($uri)){
            $uri = ltrim($_SERVER['REQUEST_URI'], '/');
        }
        $segments = explode('/', $uri);
        if (isset(APP_LANGUAGES[$segments[0]])) {
            array_shift($segments);
        }
        return base_url(implode('/', $segments), $lang);
    }
}


if (!function_exists('public_url')) {
    function public_url($path = '')
    {
        global $public_url;
        if (empty($public_url)) {
            $public_url = config('app_url') ?? '/';
        }
        return rtrim($public_url, '/') . '/' . trim($path, '/');
    }
}

if (!function_exists('files_url')) {
    function files_url($path = '') {
        global $files_base_url;
        if ($files_base_url === null) {
            $files_base_url = trim( config('files', 'Uploads')['files_url'] ?? '/content/uploads/', '/' );
            if (strpos($files_base_url, '://') === false) {
                $files_base_url = public_url($files_base_url) . '/';
            }
        }
        return $files_base_url . trim($path, '/');
    }
}

/**
 * Theme Theme URL
 * @param string $path Relative path to append to theme assets URL
 * @return string URI
 */
if(!function_exists('theme_url')) {
    function theme_url($path = '') {
        return public_url('content/themes/'.APP_THEME_NAME.'/').trim($path, '/');
    }
}

/**
 * URL tới file trong thư mục assets của theme đang active.
 *
 * `APP_THEME_NAME` / `APP_THEME_PATH` được gán ở bootstrap theo URI (admin vs site),
 * xem {@see application/Config/Constants.php} (APP_THEME_SCOPE).
 *
 * @param string $path Đường dẫn tương đối trong assets (vd. images/x.webp, js/app.js)
 * @param mixed  ...$_deprecated Tham số thừa từ API cũ (vd. theme_assets($path, $area)) — bị bỏ qua.
 */
if (!function_exists('theme_assets')) {
    function theme_assets($path = '', ...$_deprecated)
    {
        unset($_deprecated);
        $path = trim(str_replace('\\', '/', (string) $path), '/');
        $themeName = defined('APP_THEME_NAME') ? APP_THEME_NAME : '';
        if ($themeName === '') {
            return public_url('assets/' . $path);
        }

        return public_url('content/themes/' . $themeName . '/assets/' . $path);
    }
}

/**
 * Plugin assets URL
 * @param string $path Relative path to append to theme assets URL
 * @return string URI
 */
if(!function_exists('plugin_assets')) {
    function plugin_assets($path = '', $area = 'default') {
        return public_url('content/plugins/'.ucfirst($area).'/assets/'.trim($path, '/'));
    }
}


if (!function_exists('api_url')){
    function api_url($path = '') {
        return base_url('api/'.trim($path, '/'));
    }
}
if (!function_exists('admin_url')){
    function admin_url($path = '') {
        $parts = explode('?', trim($path, '/'), 2);
        if (count($parts) > 1 && !empty(trim($parts[1]))) {
            return base_url('/admin/' . trim($parts[0], '/').'/?' . $parts[1]);
        }else{
            return base_url('/admin/' . trim($parts[0], '/').'/');
        }
    }
}


/**
 * Auth URL
 * @param string $path Relative path to append to auth URL
 * @return string URI
 */
if (!function_exists('auth_url')) {
    function auth_url($path = '')
    {
        $path = trim($path, '/');
        switch ($path) {
            case 'login':
                return base_url('account/login');
                break;
            case 'register':
                return base_url('account/register');
                break;
            case 'forgot':
                return base_url('account/forgot');
                break;
            case 'reset':
                return base_url('account/forgot');
                break;
            case 'logout':
                return base_url('account/logout');
                break;
            case 'google':
                return base_url('account/login_google');
                break;
            case 'profile':
                return base_url('account/profile');
                break;
            default:
                return base_url('account/' . $path);
                break;
        }
    }
}



/**
 * redirect function
 * Redirect to another URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header('Location: ' . $url);
    echo '<meta http-equiv="refresh" content="0; url='.$url.'">';
    exit();
}

/**
 * sanitize_url function
 * Process and remove URLs containing unsafe paths like '../../'
 * 
 * @param string $url URL to check
 * @return string Processed URL
 */
function sanitize_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * parse_uri function
 * Convert URI to appropriate format for router processing
 * 
 * @param string $uri URI to parse
 * @return string Cleaned and normalized URI
 */
function parse_uri($uri) {
	if (!empty($uri)){
        return trim($uri, '/');
    }
    return $uri;
}

/**
 * Get URI from request
 * 
 * @return string Processed URI
 */
function request_uri() {
    if (!isset($_SERVER['REQUEST_URI'])){
        $_SERVER['REQUEST_URI'] = '/';
    }
    $app_url = config('app_url') ?? '/';
    $base_path = parse_url((string) $app_url, PHP_URL_PATH);
    $base_path = is_string($base_path) ? $base_path : '';
    $request_uri = $_SERVER['REQUEST_URI'];
	$request_uri = preg_replace('/(\/+)/', '/', $request_uri);
	if ($request_uri != $_SERVER['REQUEST_URI']){
		redirect($request_uri);
	}
	// If request URI starts with base_path, remove it
	if (strpos($request_uri, $base_path) === 0) {
		$request_uri = substr($request_uri, strlen($base_path));
	}
    // Clean remaining URI
    $pathOut = parse_url($request_uri, PHP_URL_PATH);

    return is_string($pathOut) ? $pathOut : '';
}
