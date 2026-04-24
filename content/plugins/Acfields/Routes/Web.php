<?php
use System\Core\Router;

// Set plugin namespace context
Router::plugin('Acfields');

// Acfields admin routes
Router::group([
    'prefix' => 'admin/acfields',
    'middleware' => [\App\Middleware\AuthMiddleware::class, \App\Middleware\RolesMiddleware::class]
], function() {
    Router::matchMethods(['GET', 'POST'], '/(:any)/(:any)', 'AcfieldsController::$1:$2');
    Router::matchMethods(['GET', 'POST'], '/(:any)', 'AcfieldsController::$1');
    Router::matchMethods(['GET', 'POST'], '', 'AcfieldsController::index');
});

// End plugin namespace
Router::endPlugin();
