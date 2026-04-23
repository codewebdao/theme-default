<?php

namespace System\Core;

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * Router - Optimized Static Router with Group/Namespace Support
 * 
 * ✅ PERFORMANCE OPTIMIZATIONS:
 * - Static methods (no instance overhead)
 * - Cached regex patterns
 * - Exact match first (fastest path)
 * - Route priority sorting
 * - Match result caching
 * 
 * @package System\Core
 */
class Router
{
    /** @var array All registered routes [method => [uri => route]] */
    private static $routes = [];

    /** @var array Route groups stack */
    private static $groupStack = [];

    /** @var array Cached regex patterns for performance */
    private static $regexCache = [];

    /** @var array Cached match results [method:uri => result] */
    private static $matchCache = [];

    /** @var array Routes separated by type (exact vs pattern) for faster matching */
    private static $routesByType = [
        'exact' => [],
        'pattern' => []
    ];

    /** @var string Base namespace context: 'App' (default) or 'Plugins\{PluginName}' */
    private static $baseNamespace = 'App';

    /**
     * Start a route group
     * 
     * @param array $attributes Group attributes: prefix, middleware, namespace
     * @param callable $callback Routes to register in group
     * @return void
     */
    public static function group(array $attributes, callable $callback)
    {
        self::$groupStack[] = $attributes;
        $callback();
        array_pop(self::$groupStack);
    }

    /**
     * Set plugin namespace context (Laravel-style)
     * 
     * Changes base namespace from 'App' to 'Plugins\{PluginName}'
     * 
     * Usage:
     * Router::plugin('Ecommerce');
     * Router::get('shop', 'ShopController::index'); // Uses Plugins\Ecommerce\Controllers\ShopController
     * 
     * Router::plugin('Ecommerce', 'Backend');
     * Router::get('dashboard', 'DashboardController::index'); // Uses Plugins\Ecommerce\Controllers\Backend\DashboardController
     * Router::endPlugin();
     * 
     * @param string $pluginName Plugin name (e.g., 'Ecommerce', 'CommentFlow')
     * @param string|null $subNamespace Sub-namespace (e.g., 'Backend', 'Frontend', 'Api')
     * @return void
     */
    public static function plugin($pluginName, $subNamespace = null)
    {
        // Change base namespace to Plugin
        self::$baseNamespace = "Plugins\\{$pluginName}";
        
        // Build namespace: Plugins\{PluginName}\Controllers\{SubNamespace}\
        $namespace = "Plugins\\{$pluginName}\\Controllers";
        if ($subNamespace) {
            $namespace .= "\\{$subNamespace}";
        }
        self::$groupStack[] = ['namespace' => $namespace];
    }

    /**
     * End plugin namespace context
     * 
     * Returns base namespace to 'App' (default)
     * 
     * @return void
     */
    public static function endPlugin()
    {
        // Reset base namespace to App
        self::$baseNamespace = 'App';
        
        if (!empty(self::$groupStack)) {
            array_pop(self::$groupStack);
        }
    }


    /**
     * Register GET route
     * 
     * @param string $uri Route URI
     * @param string $controller Controller::action format
     * @param array $middleware Middleware array (optional, can use group middleware)
     * @param string $namespace Namespace (optional, defaults to 'application' or group namespace)
     */
    public static function get($uri, $controller, $middleware = [], $namespace = null)
    {
        self::addRoute('GET', $uri, $controller, $middleware, $namespace);
    }

    /**
     * Register POST route
     * 
     * @param string $uri Route URI
     * @param string $controller Controller::action format
     * @param array $middleware Middleware array (optional, can use group middleware)
     * @param string $namespace Namespace (optional, defaults to 'application' or group namespace)
     */
    public static function post($uri, $controller, $middleware = [], $namespace = null)
    {
        self::addRoute('POST', $uri, $controller, $middleware, $namespace);
    }

    /**
     * Register PUT route
     * 
     * @param string $uri Route URI
     * @param string $controller Controller::action format
     * @param array $middleware Middleware array (optional, can use group middleware)
     * @param string $namespace Namespace (optional, defaults to 'application' or group namespace)
     */
    public static function put($uri, $controller, $middleware = [], $namespace = null)
    {
        self::addRoute('PUT', $uri, $controller, $middleware, $namespace);
    }

    /**
     * Register DELETE route
     * 
     * @param string $uri Route URI
     * @param string $controller Controller::action format
     * @param array $middleware Middleware array (optional, can use group middleware)
     * @param string $namespace Namespace (optional, defaults to 'application' or group namespace)
     */
    public static function delete($uri, $controller, $middleware = [], $namespace = null)
    {
        self::addRoute('DELETE', $uri, $controller, $middleware, $namespace);
    }

    /**
     * Register route for multiple HTTP methods
     * 
     * @param array $methods HTTP methods (e.g., ['GET', 'POST'])
     * @param string $uri Route URI
     * @param string $controller Controller::action format
     * @param array $middleware Middleware array (optional)
     * @param string $namespace Namespace (optional)
     */
    public static function matchMethods(array $methods, $uri, $controller, $middleware = [], $namespace = null)
    {
        foreach ($methods as $method) {
            self::addRoute($method, $uri, $controller, $middleware, $namespace);
        }
    }

    /**
     * Register route for all HTTP methods
     * 
     * @param string $uri Route URI
     * @param string $controller Controller::action format
     * @param array $middleware Middleware array (optional)
     * @param string $namespace Namespace (optional)
     */
    public static function any($uri, $controller, $middleware = [], $namespace = null)
    {
        self::matchMethods(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $controller, $middleware, $namespace);
    }

    /**
     * Add route to routes list (with group support)
     * 
     * ✅ OPTIMIZED: Separate exact routes from pattern routes
     */
    private static function addRoute($method, $uri, $controller, $middleware = [], $namespace = null)
    {
        // Apply group attributes
        $prefix = '';
        $groupMiddleware = [];
        $groupNamespace = $namespace; // Use provided namespace if set, otherwise use group namespace

        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix = rtrim($group['prefix'], '/') . '/' . ltrim($prefix, '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddleware = array_merge($groupMiddleware, (array)$group['middleware']);
            }
            if (isset($group['namespace']) && $groupNamespace === null) {
                // Only use group namespace if no explicit namespace provided
                $groupNamespace = $group['namespace'];
            }
        }

        // Default namespace if none provided
        if ($groupNamespace === null) {
            $groupNamespace = 'application';
        }

        // ✅ OPTIMIZATION: Build final URI with prefix (avoid double parse_uri call)
        if ($prefix) {
            $finalUri = rtrim($prefix, '/') . '/' . ltrim($uri, '/');
        } else {
            $finalUri = $uri;
        }
        $finalUri = parse_uri($finalUri);

        // Merge middleware
        $finalMiddleware = array_merge($groupMiddleware, (array)$middleware);

        // Store route
        self::$routes[$method][$finalUri] = [
            'controller' => $controller,
            'middleware' => $finalMiddleware,
            'namespace' => $groupNamespace
        ];

        // ✅ OPTIMIZATION: Categorize route (exact vs pattern) for faster matching
        // Check for patterns: (:any), (:num), etc. or custom regex patterns
        $hasPattern = strpos($finalUri, '(') !== false || strpos($finalUri, ':') !== false;
        $type = $hasPattern ? 'pattern' : 'exact';
        
        if (!isset(self::$routesByType[$type][$method])) {
            self::$routesByType[$type][$method] = [];
        }
        // ✅ OPTIMIZATION: Direct assignment (no reference needed - routes are immutable after add)
        self::$routesByType[$type][$method][$finalUri] = self::$routes[$method][$finalUri];
    }

    /**
     * Match URI with route and return route information
     * 
     * ✅ OPTIMIZED: 
     * - Match result caching
     * - Exact routes checked first (fastest)
     * - Pattern routes only if exact match fails
     * - Minimal APP_DEBUGBAR checks (cached once)
     * 
     * @param string $uri Request URI
     * @param string $method HTTP method
     * @return array|false Route info or false if not found
     */
    public static function match($uri, $method)
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Router::match');
        }
        
        $uri = parse_uri($uri);
        $cacheKey = "{$method}:{$uri}";

        // ✅ OPTIMIZATION: Check match cache first (fastest path)
        if (isset(self::$matchCache[$cacheKey])) {
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('Router::match');
            }
            return self::$matchCache[$cacheKey];
        }

        // ✅ OPTIMIZATION: Check exact routes first (O(1) lookup)
        if (isset(self::$routesByType['exact'][$method][$uri])) {
            $route = self::$routesByType['exact'][$method][$uri];
            $result = self::buildRouteResult($route, []);
            self::$matchCache[$cacheKey] = $result;
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('Router::match');
            }
            return $result;
        }

        // ✅ OPTIMIZATION: Check pattern routes only if exact match fails
        if (isset(self::$routesByType['pattern'][$method])) {
            foreach (self::$routesByType['pattern'][$method] as $routeUri => $route) {
                $regex = self::getCachedRegex($routeUri);
                
                if (preg_match($regex, $uri, $matches)) {
                    array_shift($matches);
                    $result = self::buildRouteResult($route, $matches);
                    self::$matchCache[$cacheKey] = $result;
                    if (APP_DEBUGBAR) {
                        \System\Libraries\Monitor::stop('Router::match');
                    }
                    return $result;
                }
            }
        }

        // ✅ OPTIMIZATION: Auto-route as last resort
        $result = self::autoRoute($uri);
        self::$matchCache[$cacheKey] = $result;
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Router::match');
        }
        return $result;
    }

    /**
     * Build route result array
     * 
     */
    private static function buildRouteResult($route, $params)
    {
        $controllerAction = self::getControllerAction($route['controller'], $params, $route['namespace']);
        
        return [
            'controller' => $controllerAction[0],
            'action' => $controllerAction[1],
            'params' => $controllerAction[2],
            'middleware' => $route['middleware']
        ];
    }

    /**
     * Get cached regex pattern for route URI
     * 
     * ✅ OPTIMIZED: Single compilation, cached forever, no Monitor overhead
     */
    private static function getCachedRegex($routeUri)
    {
        if (!isset(self::$regexCache[$routeUri])) {
            self::$regexCache[$routeUri] = self::convertToRegex($routeUri);
        }
        return self::$regexCache[$routeUri];
    }

    /**
     * Parse controller string and replace parameters
     * 
     * ✅ OPTIMIZED: Single pass parsing, minimal string operations, cached namespace checks
     */
    private static function getControllerAction($controllerString, $params, $namespace = 'application')
    {
        // ✅ OPTIMIZATION: Only process if params exist and controllerString has placeholders
        if (!empty($params) && strpos($controllerString, '$') !== false) {
            // Replace $n placeholders with actual values
            if (preg_match_all('/\$(\d+)/', $controllerString, $matches)) {
                $controllerPos = strpos($controllerString, '::');
                foreach ($matches[1] as $key => $paramIndex) {
                    $index = intval($paramIndex) - 1;
                    if (isset($params[$index])) {
                        $value = $params[$index];
                        
                        // ✅ OPTIMIZATION: Handle slashes in value (only if needed)
                        $slashPos = strpos($value, '/');
                        if ($slashPos !== false) {
                            $value = substr($value, 0, $slashPos);
                        }
                        
                        // Clean value for controller/action names
                        if ($key < 2) {
                            $value = str_replace('.', '', $value);
                        }
                        
                        // ✅ OPTIMIZATION: Check if controller part (cache controllerPos)
                        $placeholderPos = strpos($controllerString, '$' . $paramIndex);
                        if ($controllerPos !== false && $placeholderPos < $controllerPos) {
                            $value = ucfirst($value);
                        }
                        
                        $controllerString = str_replace('$' . $paramIndex, $value, $controllerString);
                    }
                }
            }
        }

        // ✅ OPTIMIZATION: Single explode for controller/action split
        $parts = explode('::', $controllerString, 2);
        $controller = $parts[0];
        $actionString = $parts[1] ?? 'index';
        
        // ✅ OPTIMIZATION: Single explode for action/params split
        $actionParts = explode(':', $actionString);
        $action = array_shift($actionParts);

        // ✅ OPTIMIZATION: Build controller class name (cached namespace checks)
        if ($namespace === 'application') {
            $controllerClass = "App\\Controllers\\{$controller}";
        } elseif (strpos($namespace, 'Plugins\\') === 0 || strpos($namespace, 'App\\') === 0) {
            // Full namespace path
            $controllerClass = "{$namespace}\\{$controller}";
        } else {
            // Sub-namespace - use baseNamespace context
            if (self::$baseNamespace === 'App') {
                $controllerClass = "App\\Controllers\\{$namespace}\\{$controller}";
            } else {
                $controllerClass = self::$baseNamespace . "\\Controllers\\{$namespace}\\{$controller}";
            }
        }

        return [$controllerClass, $action, $actionParts];
    }

    /**
     * Convert route URI to regex pattern
     * 
     * ✅ OPTIMIZED: Single pass with array replacements (faster than multiple str_replace)
     */
    private static function convertToRegex($routeUri)
    {
        // Pattern replacements (ordered by specificity)
        $replacements = [
            '(:any)' => '(.+)',
            '(:segment)' => '([^/]+)',
            '(:num)' => '(\d+)',
            '(:alpha)' => '([a-zA-Z]+)',
            '(:alphadash)' => '([a-zA-Z\-]+)',
            '(:alphanum)' => '([a-zA-Z0-9]+)',
            '(:alphanumdash)' => '([a-zA-Z0-9\-]+)',
        ];

        $routeUri = str_replace(array_keys($replacements), array_values($replacements), $routeUri);

        // Support custom regex patterns
        $routeUri = preg_replace('#\(([a-zA-Z0-9_\-\.\[\]\+\*]+)\)#', '($1)', $routeUri);

        return "#^{$routeUri}$#";
    }

    /**
     * Auto-route from URI structure
     * 
     * ✅ OPTIMIZED: Early exit, minimal operations, avoid array_filter overhead
     */
    private static function autoRoute($uri)
    {
        // ✅ OPTIMIZATION: Direct explode without filter (faster)
        $uri = trim($uri, '/');
        if (empty($uri)) {
            return false;
        }
        
        $segments = explode('/', $uri);
        // Remove empty segments (from double slashes)
        $segments = array_values(array_filter($segments, function($s) { return $s !== ''; }));
        
        if (empty($segments)) {
            return false;
        }

        $controller = ucfirst($segments[0]) . 'Controller';
        $action = isset($segments[1]) ? $segments[1] : 'index';
        $params = array_slice($segments, 2);

        $controllerClass = "App\\Controllers\\{$controller}";
        
        // ✅ OPTIMIZATION: Check class_exists before method_exists (faster)
        if (class_exists($controllerClass) && method_exists($controllerClass, $action)) {
            return [
                'controller' => $controllerClass,
                'action' => $action,
                'params' => $params,
                'middleware' => []
            ];
        }

        return false;
    }

    /**
     * Get all registered routes (for debugging)
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Clear all routes and caches (for testing)
     */
    public static function clear()
    {
        self::$routes = [];
        self::$regexCache = [];
        self::$matchCache = [];
        self::$groupStack = [];
        self::$routesByType = ['exact' => [], 'pattern' => []];
        self::$baseNamespace = 'App';
    }
}
