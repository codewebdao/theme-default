<?php
/**
 * API Routes
 * 
 * ✅ OPTIMIZED: Using static Router with group support
 * 
 * Note: Router is now static, but $this->routes is available for backward compatibility
 * You can use either Router::method() or $this->routes::method()
 */

use System\Core\Router;

// ✅ OPTIMIZED: Use group for API v0 posts routes
Router::group([
    'prefix' => 'api/v0/posts',
    'namespace' => 'App\\Controllers\\Api\\V0'
], function() {
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)/(:any)', 'PostsController::index:$1:$2:$3');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)', 'PostsController::index:$1:$2');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)', 'PostsController::index:$1');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '', 'PostsController::index');
});

// API v0 generic routes
Router::group([
    'prefix' => 'api/v0',
    'namespace' => 'App\\Controllers\\Api\\V0'
], function() {
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)/(:any)/(:any)', '$1Controller::$2:$3:$4');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)/(:any)', '$1Controller::$2:$3');
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)/(:any)', '$1Controller::$2');
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)', '$1Controller::index');
});

// ✅ OPTIMIZED: Use group for API v2 auth routes
Router::group([
    'prefix' => 'api/v2/auth',
    'middleware' => [\App\Middleware\Api\CorsMiddleware::class],
    'namespace' => 'App\\Controllers\\Api\\V2'
], function() {
    // Public endpoints (no auth required)
    Router::group([
        'middleware' => [\App\Middleware\NoauthMiddleware::class]
    ], function() {
        Router::post('login', 'AuthController::login');
        Router::post('register', 'AuthController::register');
        Router::post('forgot', 'AuthController::forgot');
        Router::get('health', 'AuthController::health');
        Router::post('refresh', 'AuthController::refresh');
        Router::get('confirmlink/(:any)/(:any)', 'AuthController::confirmlink:$1:$2');
        Router::matchMethods(['GET', 'POST'], '/(:any)', 'AuthController::$1');
    });

    // Protected endpoints (auth required)
    Router::group([
        'middleware' => [\App\Middleware\Api\ApiAuthMiddleware::class]
    ], function() {
        Router::get('profile', 'AuthController::profile');
        Router::post('set-profile', 'AuthController::set_profile');
        Router::post('change-password', 'AuthController::change_password');
        Router::post('logout', 'AuthController::logout');
        Router::get('sessions', 'AuthController::sessions');
        Router::post('revoke-session', 'AuthController::revoke_session');
        Router::post('revoke-others', 'AuthController::revoke_others');
        Router::get('token-info', 'AuthController::token_info');
    });
});

// API v2 public forms (contact, …) — CORS + POST/OPTIONS
Router::group([
    'prefix' => 'api/v2/form',
    'middleware' => [\App\Middleware\Api\CorsMiddleware::class],
    'namespace' => 'App\\Controllers\\Api\\V2'
], function() {
    Router::matchMethods(['POST', 'OPTIONS'], 'contact', 'FormController::contact');
});

// API v2 files routes
Router::group([
    'prefix' => 'api/v2/files',
    'namespace' => 'App\\Controllers\\Api\\V2'
], function() {
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)/(:any)/(:any)', 'FilesController::$1:$2:$3');
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)/(:any)', 'FilesController::$1:$2');
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)', 'FilesController::$1');
});

// API v2 generic routes
Router::group([
    'prefix' => 'api/v2',
    'namespace' => 'App\\Controllers\\Api\\V2'
], function() {
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)/(:any)/(:any)', '$1Controller::$2:$3:$4');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)/(:any)', '$1Controller::$2:$3');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], '/(:any)/(:any)', '$1Controller::$2');
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)', '$1Controller::index');
});

// API v1 routes
Router::group([
    'prefix' => 'api/v1/posts',
    'namespace' => 'App\\Controllers\\Api\\V1'
], function() {
    Router::get('/(:any)/(:any)/(:any)/(:any)/paged/(:num)/', 'PostsController::$1:$2:$3:$4:$5');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)/(:any)/(:num)/', 'PostsController::$1:$2:$3:$4:$5');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)/(:num)/', 'PostsController::$1:$2:$3:$4');
});

Router::group([
    'prefix' => 'api/v1',
    'namespace' => 'App\\Controllers\\Api\\V1'
], function() {
    Router::get('/(:any)/(:any)/(:any)/(:any)/(:num)', '$1Controller::$2:$3:$4');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)/(:any)', '$1Controller::$2:$3:$4');
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)/(:any)', '$1Controller::$2:$3');
    Router::matchMethods(['GET', 'POST', 'DELETE'], '/(:any)/(:any)', '$1Controller::$2');
});

// Generic API routes (lowest priority)
Router::group([
    'namespace' => 'App\\Controllers\\Api'
], function() {
    Router::matchMethods(['GET', 'POST'], 'api/(:any)/(:any)/(:any)/(:any)', '$1Controller::$2:$3:$4');
    Router::get('api/(:any)/(:any)/(:any)/', '$1Controller::$2:$3');
    Router::matchMethods(['GET', 'POST', 'PUT', 'DELETE'], 'api/(:any)/(:any)', '$1Controller::$2');
});
