<?php

use System\Core\Route;

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * Generate URL from named route
 * 
 * @param string $name Route name
 * @param array $params Route parameters
 * @return string|null Generated URL or null if route not found
 */
if (!function_exists('route')) {
    function route($name, $params = [])
    {
        return Route::url($name, $params);
    }
}

/**
 * Redirect to named route
 * 
 * @param string $name Route name
 * @param array $params Route parameters
 * @param int $status HTTP status code
 */
if (!function_exists('redirect_route')) {
    function redirect_route($name, $params = [], $status = 302)
    {
        $url = route($name, $params);
        if ($url) {
            redirect($url, $status);
        }
    }
}

/**
 * Get current route name
 * 
 * @return string|null Current route name
 */
if (!function_exists('current_route_name')) {
    function current_route_name()
    {
        return Route::currentRouteName();
    }
}

/**
 * Check if current route matches name
 * 
 * @param string $name Route name
 * @return bool
 */
if (!function_exists('route_is')) {
    function route_is($name)
    {
        return Route::named($name);
    }
}

/**
 * Get current route
 * 
 * @return array|null Current route information
 */
if (!function_exists('current_route')) {
    function current_route()
    {
        return Route::current();
    }
}

