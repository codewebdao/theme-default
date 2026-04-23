<?php
namespace System\Core;
use System\Database\DB;
use System\Libraries\Logger;
use Exception;

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Bootstrap {

    protected $routes;
    protected $uri;

    public function __construct() {
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Application::Bootstrap');
            \System\Libraries\Monitor::mark('Bootstrap::__construct');
        }
        
        $timezone = config('app_timezone');
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }
        
        // Load database configuration from Config.php
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Bootstrap::initDatabase');
        }
        $dbConfig = config('database');
        
        if (empty($dbConfig)) {
            throw new \RuntimeException('Database configuration not found in Config.php');
        }

        DB::init($dbConfig);
        if (APP_DEBUGBAR){
            // Enable query logging
            DB::enableQueryLog();
            $connCount = isset($dbConfig['connections']) ? count($dbConfig['connections']) : 1;
            \System\Libraries\Monitor::stop('Bootstrap::initDatabase');
        }
        
        // Initialize URI
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Bootstrap::initUri');
        }
        $this->init_uri();
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::initUri');
        }
        
        // ================================================================
        // LOAD PLUGIN INIT FILES (before Routes)
        // ================================================================
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Bootstrap::loadPlugins');
        }
        PluginLoader::init();
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::loadPlugins');
        }
        
        // ✅ OPTIMIZED: Router is now static, no instance needed
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Bootstrap::loadRoutes');
        }
        $this->loadRoutes();          // Load routes
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::loadRoutes');
        }
        
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::__construct');
        }
    }


    /**
     * Canonicalise the current request URI and build $this->uri.
     *
     * @return array{uri:string, split:string[]} Sanitised path + segments
    */
    private function init_uri(){
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Bootstrap::initUri::parseRequest');
        }
        /* -----------------------------------------------------------------
        * 1) Grab raw path + raw query from super-globals
        * -----------------------------------------------------------------*/
        $rawPath  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');  // path only
        $rawQuery = $_SERVER['QUERY_STRING']    ?? '';
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Bootstrap::initUri::parseRequest');
            \System\Libraries\Monitor::mark('Bootstrap::initUri::sanitizePath');
        }
        /* -----------------------------------------------------------------
        * 2) Sanitise the path part (custom security filter + collapse slash)
        * -----------------------------------------------------------------*/
        $path = preg_replace('#/+#', '/', uri_security(trim($rawPath, '/')));
	    $path = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Bootstrap::initUri::sanitizePath');
            \System\Libraries\Monitor::mark('Bootstrap::initUri::sanitizeQuery');
        }
        /* -----------------------------------------------------------------
        * 3) Sanitise the query string via your GET-filter helper
        * -----------------------------------------------------------------*/
        $safeQuery = '';
        if ($rawQuery !== '') {
            $safeQuery = http_build_query(sget_security());  // returns cleaned $_GET
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Bootstrap::initUri::sanitizeQuery');
            \System\Libraries\Monitor::mark('Bootstrap::initUri::buildCanonical');
        }
        /* -----------------------------------------------------------------
        * 4) Assemble the canonical URI (to compare / redirect)
        * -----------------------------------------------------------------*/
        if ($path == ''){
            $path = '/';
            $canonical = '/';
        }else{
            $canonical = '/' . $path.'/';
        }
        if ($safeQuery !== '') {
            $canonical .= '?' . $safeQuery;
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Bootstrap::initUri::buildCanonical');
            \System\Libraries\Monitor::mark('Bootstrap::initUri::checkRedirect');
        }
        /* -----------------------------------------------------------------
        * 5) If canonical ≠ original → 301 redirect to canonical form
        *    (trailing-slash tolerant)
        * -----------------------------------------------------------------*/
        $original = $_SERVER['REQUEST_URI'] ?? '/';
        if ($canonical !== $original && $canonical !== $original.'/') {
            if ($segments && defined('APP_LANGUAGES') && isset(APP_LANGUAGES[$segments[0]])) {
                $canonical = '/'.substr($canonical, 3);
                redirect(base_url($canonical));
            }else{
                redirect(base_url($canonical));
            }
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Bootstrap::initUri::checkRedirect');
            \System\Libraries\Monitor::mark('Bootstrap::initUri::processLanguage');
        }
        /* -----------------------------------------------------------------
        * 6) Build segments array + strip language prefix if needed
        * -----------------------------------------------------------------*/
        $segments = ($path === '' || $path === '/') ? [] : explode('/', $path);
        if ($segments && defined('APP_LANGUAGES') && isset(APP_LANGUAGES[$segments[0]])) {
            array_shift($segments);                 // remove language code
            $path = implode('/', $segments);
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Bootstrap::initUri::processLanguage');
        }
        define('APP_URI', [
            'uri'   => $path,                       // e.g. "api/v1/auth"
            'split' => $segments,                    // e.g. ['api','v1','auth']
            'query' => $safeQuery                   // e.g. "?page=1&limit=10"
        ]);

        return $this->uri = APP_URI;
    }

    /**
     * Get Router class (for compatibility)
     * Router is now static, but we keep this for backward compatibility
     */
    public function getRouter() {
        return Router::class;
    }
    /**
     * Start framework
     */
    public function run() {
        try {
            if (APP_DEBUGBAR){
                \System\Libraries\Monitor::mark('Bootstrap::dispatch');
            }
            if (!isset($_SERVER['REQUEST_METHOD'])) $_SERVER['REQUEST_METHOD'] = 'GET';
            $method = $_SERVER['REQUEST_METHOD'];
            $this->dispatch($this->uri['uri'], $method);
            if (APP_DEBUGBAR){
                \System\Libraries\Monitor::stop('Bootstrap::dispatch');
            }
        } catch (AppException $e) {
            $e->handle();
        } catch (\Throwable $e) { // Catch all exceptions and errors
            Logger::error($e->getMessage(), $e->getFile(), $e->getLine());
            http_response_code(500);
            if (defined('APP_DEBUG') && APP_DEBUG ? true : false) {
                echo $e->getMessage(), $e->getFile(), $e->getLine();
            }else{
                echo "An unknown error has occurred. Lets check file logger.log! ";
            }
        }
    }

    /**
     * Load routes from routes/web.php and routes/api.php files
     * 
     * ✅ OPTIMIZED:
     * - Cached plugins list
     * - Cached file_exists checks
     * - Router wrapper for backward compatibility
     */
    private function loadRoutes() {
        // ✅ OPTIMIZED: Use cached plugins list from PluginLoader (avoid duplicate DB query)
        $plugins = PluginLoader::activeLists();
        
        $isApi = !empty($this->uri) && !empty($this->uri['split']) && $this->uri['split'][0] == 'api';
        $routeFile = $isApi ? 'Api.php' : 'Web.php';
        $routePath = PATH_APP . 'Routes/' . $routeFile;
        
        // ✅ OPTIMIZATION: Load plugin routes first (only if plugins exist)
        if (!empty($plugins)) {
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::mark('Router::loadPluginRoutes');
            }
            $pluginRoutePath = $isApi ? '/Routes/Api.php' : '/Routes/Web.php';
            foreach ($plugins as $plugin) {
                $pluginName = is_array($plugin) ? ($plugin['name'] ?? '') : $plugin;
                if (empty($pluginName)) {
                    continue;
                }
                $pluginFile = PATH_PLUGINS . $pluginName . $pluginRoutePath;
                // ✅ OPTIMIZATION: Single file_exists check per plugin
                if (file_exists($pluginFile)) {
                    if (APP_DEBUGBAR) {
                        \System\Libraries\Monitor::mark("Router::loadPluginRoutes::{$pluginName}");
                    }
                    include_once $pluginFile;
                    if (APP_DEBUGBAR) {
                        \System\Libraries\Monitor::stop("Router::loadPluginRoutes::{$pluginName}");
                    }
                }
            }
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('Router::loadPluginRoutes');
            }
        }
        // ✅ OPTIMIZATION: Load main route file (single file_exists check)
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Router::loadMainRoutes');
        }
        if (file_exists($routePath)) {
            require_once $routePath;
            
            // Load Events.php only for Web routes
            // if (!$isApi && file_exists(PATH_APP . 'Config/Events.php')) {
            //     require_once PATH_APP . 'Config/Events.php';
            // }
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Router::loadMainRoutes');
        }
    }

    /**
     * Route URI to corresponding controller and action
     */
    private function dispatch($uri, $method) {
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::mark('Bootstrap::dispatch::matchRoute');
        }
        // ✅ OPTIMIZED: Use static Router::match()
        $route = Router::match($uri, $method);
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::dispatch::matchRoute');
        }
        
        if (isset($route['action']) && $route['action'][0] == '_'){
            Logger::warning("Router: Private action attempted - /{$uri} ({$method})");
            throw new AppException("404 - Router: /{$uri} ({$method}) can not access!", 404, null, 404);
        }
        
        if (!$route) {
            Logger::error("Router: Route not found - /{$uri} ({$method})");
            throw new AppException("404 - Router: /{$uri} ({$method}) not found!", 404, null, 404);
        }

        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::mark('Bootstrap::dispatch::initMiddleware');
        }
        //Process Middleware before calling Controller.
        $middleware = new Middleware();
        if (!empty($route['middleware'])) {
            if (is_string($route['middleware'])){
                $route['middleware'] = ['App\\Middleware\\'.$route['middleware']];
            }
            // Add middlewares to list if Middleware exists
            foreach ($route['middleware'] as $mw) {
                $middleware->add($mw);
            }
        }
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::dispatch::initMiddleware');
        }
        // Execute middleware before calling controller
        unset($route['middleware']);//can skip this function if need to use middleware below
        $route['uri'] = $uri;
        
        // Measure middleware handle separately (before controller)
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::mark('Bootstrap::dispatch::handleMiddleware');
        }
        $middleware->handle($route, function () use ($route) {
            if (APP_DEBUGBAR){
                \System\Libraries\Monitor::mark('ExecuteController');
            }
            
            // Get controller and method information from matched route
            $controllerClass = $route['controller'];
            $action = str_replace('-', '_', $route['action']);
            $params = $route['params'];
            if (!defined('APP_ROUTE')){
                define('APP_ROUTE', $route);
            }
            
            if (APP_DEBUGBAR){
                \System\Libraries\Monitor::mark('ExecuteController::validateController');
            }
            try{
                // Check if controller exists
                if (!class_exists($controllerClass)) {
                    throw new AppException("Controller {$controllerClass} not found.", 404, null, 404);
                }
                // Initialize controller object
                if (APP_DEBUGBAR){
                    \System\Libraries\Monitor::stop('ExecuteController::validateController');
                    \System\Libraries\Monitor::mark('ExecuteController::instantiateController');
                }
                $controller = new $controllerClass();
                if (APP_DEBUGBAR){
                    \System\Libraries\Monitor::stop('ExecuteController::instantiateController');
                    \System\Libraries\Monitor::mark('ExecuteController::validateAction');
                }
                // Check if action exists
                if (!method_exists($controller, $action)) {
                    throw new AppException("Action {$action} not found in {$controllerClass} Controller.", 404, null, 404);
                }
                if (APP_DEBUGBAR){
                    \System\Libraries\Monitor::stop('ExecuteController::validateAction');
                    \System\Libraries\Monitor::mark("ExecuteController::{$controllerClass}::{$action}");
                }
                
                // Execute controller action
                call_user_func_array([$controller, $action], $params);
                
                if (APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop("ExecuteController::{$controllerClass}::{$action}");
                }
            } catch (\PDOException $e) {
                if (APP_DEBUGBAR) {
                    // Stop any active markers in case of exception
                    if (isset($controllerClass) && isset($action)) {
                        \System\Libraries\Monitor::stop("ExecuteController::{$controllerClass}::{$action}");
                    }
                    // Stop validateAction if still active
                    \System\Libraries\Monitor::stop('ExecuteController::validateAction');
                    // Stop instantiateController if still active
                    \System\Libraries\Monitor::stop('ExecuteController::instantiateController');
                    // Stop validateController if still active
                    \System\Libraries\Monitor::stop('ExecuteController::validateController');
                }
                // SQL error - show more details
                $message = $e->getMessage();
                
                // Try to get SQL query from debug info
                if (class_exists('\System\Database\DB')) {
                    $queryLog = \System\Database\DB::getQueryLog();
                    if (!empty($queryLog)) {
                        foreach ($queryLog as $query) {
                            $message .= "\n\n" . $query['sql_rendered'];
                            if (!empty($query['params'])) {
                                $message .= "(Params: " . json_encode($query['params']) . ")";
                            }
                            $message .= "<br />\n";
                        }
                    }
                }
                
                throw new AppException($message, 500, $e, 500);
            } catch (\RedisException $e) {
                if (APP_DEBUGBAR) {
                    if (isset($controllerClass) && isset($action)) {
                        \System\Libraries\Monitor::stop("ExecuteController::{$controllerClass}::{$action}");
                    }
                    \System\Libraries\Monitor::stop('ExecuteController::validateAction');
                    \System\Libraries\Monitor::stop('ExecuteController::instantiateController');
                    \System\Libraries\Monitor::stop('ExecuteController::validateController');
                }
                // Redis-specific error
                throw new AppException("Redis Connection Error: " . $e->getMessage(), 500, $e, 500);
            } catch (\Exception $e) {
                if (APP_DEBUGBAR) {
                    if (isset($controllerClass) && isset($action)) {
                        \System\Libraries\Monitor::stop("ExecuteController::{$controllerClass}::{$action}");
                    }
                    \System\Libraries\Monitor::stop('ExecuteController::validateAction');
                    \System\Libraries\Monitor::stop('ExecuteController::instantiateController');
                    \System\Libraries\Monitor::stop('ExecuteController::validateController');
                }
                throw new AppException($e->getMessage(), 500, $e, 500);
            } finally {
                if (APP_DEBUGBAR){
                    // Stop all possible active markers in finally block
                    if (isset($controllerClass) && isset($action)) {
                        \System\Libraries\Monitor::stop("ExecuteController::{$controllerClass}::{$action}");
                    }
                    \System\Libraries\Monitor::stop('ExecuteController::validateAction');
                    \System\Libraries\Monitor::stop('ExecuteController::instantiateController');
                    \System\Libraries\Monitor::stop('ExecuteController::validateController');
                    \System\Libraries\Monitor::stop('ExecuteController');
                }

                // Kiểm tra thư mục theme (không dùng $e ở đây: finally chạy cả khi request thành công, biến $e từ catch có thể không tồn tại → fatal sau khi đã output HTML → client thấy 500 dù trang vẫn render).
                if (!is_dir(APP_THEME_PATH)) {
                    _critical_error('Theme directory not found in ' . APP_THEME_PATH . '. Please install a theme.', 500);
                    exit;
                }
            }
        });
        if (APP_DEBUGBAR){
            \System\Libraries\Monitor::stop('Bootstrap::dispatch::handleMiddleware');
        }
    }
}

