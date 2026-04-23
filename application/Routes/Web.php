<?php
/**
 * Web Routes
 * 
 * ✅ OPTIMIZED: Using static Router with group support
 * 
 * Note: Router is now static, but $this->routes is available for backward compatibility
 * You can use either Router::method() or $this->routes::method()
 */
use System\Core\Router;
Router::get('sitemap.xml', 'SitemapController::index', []);
Router::get('sitemap-(:any).xml', 'SitemapController::index:$1', []);
// LocalizationTest routes
Router::get('/LocalizationTest/(:any)/(:any)/', 'LocalizationTestController::$1:$2:$3', []);
Router::get('/LocalizationTest/(:any)', 'LocalizationTestController::$1:$2', []);
Router::get('/LocalizationTest', 'LocalizationTestController::index', []);


// Account routes with auth middleware
Router::group([
    'prefix' => 'account',
    'middleware' => [\App\Middleware\AuthMiddleware::class]
], function() {
    Router::get('', 'AuthController::index');
    Router::get('logout', 'AuthController::logout');
    Router::matchMethods(['GET', 'POST'], 'profile', 'AuthController::profile');
    Router::matchMethods(['GET', 'POST'], 'set-profile', 'AuthController::set_profile');
    Router::matchMethods(['GET', 'POST'], 'change-password', 'AuthController::change_password');
});

// Account public routes (no auth)
Router::group([
    'prefix' => 'account',
    'middleware' => [\App\Middleware\NoauthMiddleware::class]
], function() {
    Router::get('confirmlink', 'AuthController::confirmlink');
    Router::get('login_google/', 'AuthController::login_google');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)', 'AuthController::$1:$2:$3');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)', 'AuthController::$1:$2');
    Router::matchMethods(['GET', 'POST'], '/(:any)', 'AuthController::$1');
});


// ✅ OPTIMIZED: Use group for admin routes with shared middleware
Router::group([
    'prefix' => 'admin/files',
    'namespace' => 'App\\Controllers\\Backend',
    'middleware' => [\App\Middleware\AuthMiddleware::class, \App\Middleware\RolesMiddleware::class]
], function() {
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)', 'FilesController::$1:$2:$3');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)', 'FilesController::$1:$2');
    Router::matchMethods(['GET', 'POST'], '/(:any)', 'FilesController::$1');
});

// Admin routes with auth and roles middleware
Router::get('auth/logout/', 'AuthController::logout');
Router::group([
    'prefix' => 'admin',
    'middleware' => [\App\Middleware\AuthMiddleware::class, \App\Middleware\RolesMiddleware::class],
    'namespace' => 'Backend'
], function() {
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)/(:any)/(:any)', '$1Controller::$2:$3:$4:$5');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)', '$1Controller::$2:$3');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)', '$1Controller::$2');
    Router::matchMethods(['GET', 'POST'], '/(:any)', '$1Controller::index');
    Router::get('', 'HomeController::index');
});

// URL Rewrites from Admin Settings
// $rewrite = option('url_rewrite', APP_LANG);
// if (!empty($rewrite)) {
//     // If stored data is JSON string, convert to array
//     $rewrite = is_string($rewrite) ? json_decode($rewrite, true) : $rewrite;
    
//     function ensureSlashes($url)
//     {
//         if (substr($url, 0, 1) !== '/') {
//             $url = '/' . $url;
//         }
//         if (substr($url, -1) !== '/') {
//             $url = $url . '/';
//         }
//         return $url;
//     }

//     // Update array with URLs that ensure "/" at beginning and end
//     foreach ($rewrite as &$item) {
//         if (empty($item['url_struct'])) continue;
//         $item['url_struct'] = ensureSlashes($item['url_struct']);
//     }
//     unset($item); // release reference variable

//     // Function to count URL segments
//     function countSegments($url)
//     {
//         if (empty($url)) {
//             return 0;
//         }
//         $trimmed = trim($url, '/');
//         $segments = array_filter(explode('/', $trimmed));
//         return count($segments);
//     }

//     // Iterate through each item in rewrite array
//     foreach ($rewrite as $item) {
//         // Check if required keys exist
//         if (!isset($item['url_struct'], $item['url_function'])) {
//             continue;
//         }
//         $url = $item['url_struct'];
        
//         // Count number of placeholders in $url
//         $pattern = '/\(:any\)|\(:num\)/';
//         preg_match_all($pattern, $url, $matches);
//         $captureCount = count($matches[0]);

//         // Build callback based on number of capture groups
//         $callback = $item['url_function'];
//         if ($captureCount > 0) {
//             // Kiểm tra xem url_function đã có placeholder nào rồi
//             $existingCaptures = [];
//             preg_match_all('/\$(\d+)/', $callback, $existingMatches);
//             if (!empty($existingMatches[1])) {
//                 $existingCaptures = array_map('intval', $existingMatches[1]);
//             }

//             // Chỉ thêm những placeholder còn thiếu
//             $captures = [];
//             for ($i = 1; $i <= $captureCount; $i++) {
//                 if (!in_array($i, $existingCaptures)) {
//                     $captures[] = '$' . $i;
//                 }
//             }

//             // Chỉ thêm nếu có placeholder còn thiếu
//             if (!empty($captures)) {
//                 $callback .= ':' . implode(':', $captures);
//             }
//         }
//         $middleware = $item['middleware'] ?? [];
//         if ($middleware === 'false') {
//             $middleware = [];
//         }
//         $callback = str_replace(' ', '', $callback);
//         Router::get($url, $callback, $middleware);
//     }
// }

// Frontend catch-all routes (lowest priority)
Router::get('/(:any)/(:any)/(:any)/(:any)/(:any)/', 'FrontendController::index:$1:$2:$3:$4:$5', []);
Router::get('/(:any)/(:any)/(:any)/(:any)/', 'FrontendController::index:$1:$2:$3:$4', []);
Router::get('/(:any)/(:any)/(:any)/', 'FrontendController::index:$1:$2:$3', []);
Router::get('/(:any)/(:any)', 'FrontendController::index:$1:$2', []);
Router::get('/(:any)', 'FrontendController::index:$1', []);
Router::get('/', 'FrontendController::index', []);
