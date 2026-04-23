<?php
namespace System\Core;

/**
 * Middleware - Highly Optimized Middleware Handler
 * 
 * ✅ PERFORMANCE OPTIMIZATIONS:
 * - Cache middleware instances (avoid repeated instantiation)
 * - Pre-check class_exists before caching
 * - Early exit optimizations
 * - Optimized stack processing (iterative instead of recursive when possible)
 * 
 * @package System\Core
 */
class Middleware
{
    /** @var array Middleware stack */
    protected $middleware = [];

    /** @var int Current middleware index */
    protected $current = 0;

    /** @var array Cached middleware instances */
    protected static $instances = [];

    /** @var array Pre-validated middleware classes (class_exists check cached) */
    protected static $validatedClasses = [];

    /**
     * Add middleware to stack
     *
     * @param callable|string $middleware Middleware name or callback
     * @return void
     */
    public function add($middleware)
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Execute middleware stack
     * 
     * ✅ OPTIMIZED: 
     * - Early exit if no middlewares
     * - Cached instances
     * - Pre-validated classes
     * - Optimized recursion
     *
     * @param mixed $request Current request
     * @param callable $next Callback when middleware completes
     * @param bool $isFirstCall Internal flag to track first call (for marking)
     * @return mixed
     */
    public function handle($request, $next, $isFirstCall = true)
    {
        // ✅ FIX: Only mark on first call (not on recursive calls)
        if ($isFirstCall && APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Middleware::handle');
        }
        
        // ✅ OPTIMIZATION: Early exit if no middlewares
        $count = count($this->middleware);
        if ($this->current >= $count) {
            if ($isFirstCall && APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('Middleware::handle');
            }
            return $next($request);
        }

        // Get current middleware
        $middleware = $this->middleware[$this->current];
        $this->current++;

        // ✅ OPTIMIZATION: Handle callable middleware (fastest - no class loading)
        if (is_callable($middleware)) {
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::mark('Middleware::handle::callable');
            }
            $result = $middleware($request, function ($request) use ($next) {
                // ✅ FIX: Pass false for recursive calls
                return $this->handle($request, $next, false);
            });
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('Middleware::handle::callable');
            }
            // ✅ FIX: Only stop on first call
            if ($isFirstCall && APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('Middleware::handle');
            }
            return $result;
        }

        // ✅ OPTIMIZATION: Handle class-based middleware with validation caching
        if (is_string($middleware)) {
            // Extract class name for display (remove namespace if needed)
            $displayName = $middleware;
            if (strpos($middleware, '\\') !== false) {
                $parts = explode('\\', $middleware);
                $displayName = end($parts);
            }
            
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::mark("Middleware::handle::{$displayName}");
            }
            // Check if class is valid (cached check)
            if (!isset(self::$validatedClasses[$middleware])) {
                self::$validatedClasses[$middleware] = class_exists($middleware);
            }

            if (self::$validatedClasses[$middleware]) {
                // Get or create cached instance
                $instance = self::getInstance($middleware);
                
                $result = $instance->handle($request, function ($request) use ($next) {
                    // ✅ FIX: Pass false for recursive calls
                    return $this->handle($request, $next, false);
                });
                if (APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop("Middleware::handle::{$displayName}");
                }
                // ✅ FIX: Only stop on first call
                if ($isFirstCall && APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop('Middleware::handle');
                }
                return $result;
            }
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop("Middleware::handle::{$displayName}");
            }
        }

        // Invalid middleware - skip and continue
        // ✅ FIX: Pass false for recursive calls
        $result = $this->handle($request, $next, false);
        // ✅ FIX: Only stop on first call
        if ($isFirstCall && APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Middleware::handle');
        }
        return $result;
    }

    /**
     * Get or create middleware instance (with caching)
     * 
     * ✅ OPTIMIZED: Cache instances to avoid repeated instantiation
     * 
     * @param string $middlewareClass Middleware class name
     * @return object Middleware instance
     */
    protected static function getInstance($middlewareClass)
    {
        // ✅ CACHE: Return cached instance if available
        if (!isset(self::$instances[$middlewareClass])) {
            // Extract class name for display
            $displayName = $middlewareClass;
            if (strpos($middlewareClass, '\\') !== false) {
                $parts = explode('\\', $middlewareClass);
                $displayName = end($parts);
            }
            
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::mark("Middleware::getInstance::{$displayName}");
            }
            self::$instances[$middlewareClass] = new $middlewareClass();
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop("Middleware::getInstance::{$displayName}");
            }
        }
        
        return self::$instances[$middlewareClass];
    }

    /**
     * Clear middleware caches (for testing)
     * 
     * @return void
     */
    public static function clearCache()
    {
        self::$instances = [];
        self::$validatedClasses = [];
    }
}
