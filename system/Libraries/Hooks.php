<?php
namespace System\Libraries;

/**
 * Hooks System - WordPress-style implementation
 * 
 * Provides add_action, do_action, add_filter, apply_filters functionality
 * Compatible with WordPress plugin development patterns
 * 
 * @package System\Libraries
 * @author CMS FullForm
 * @version 1.0.0
 */
class Hooks
{
    /**
     * Registered action hooks
     * 
     * @var array
     */
    protected static $actions = [];
    
    /**
     * Registered filter hooks
     * 
     * @var array
     */
    protected static $filters = [];
    
    /**
     * Current filter being executed
     * 
     * @var array
     */
    protected static $currentFilter = [];
    
    /**
     * Execution counter for hooks
     * 
     * @var array
     */
    protected static $executionCount = [];
    
    /**
     * Debug log for hook executions (when APP_DEBUGBAR enabled)
     * 
     * @var array
     */
    protected static $debugLog = [];
    
    /**
     * Performance tracking for hooks
     * 
     * @var array
     */
    protected static $performanceLog = [];
    
    /**
     * Session key for storing hooks history
     * 
     * @var string
     */
    const SESSION_KEY = 'debugbar_hooks';
    
    /**
     * Maximum number of requests to keep in history
     * 
     * @var int
     */
    const MAX_HISTORY_REQUESTS = 10;
    
    /**
     * Register an action hook
     * 
     * @param string $hook Action hook name
     * @param callable $callback Callback function
     * @param int $priority Priority (default 10, lower runs first)
     * @param int $acceptedArgs Number of arguments callback accepts
     * @return bool
     */
    public static function addAction($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return self::addHook('action', $hook, $callback, $priority, $acceptedArgs);
    }
    
    /**
     * Register a filter hook
     * 
     * @param string $hook Filter hook name
     * @param callable $callback Callback function
     * @param int $priority Priority (default 10, lower runs first)
     * @param int $acceptedArgs Number of arguments callback accepts
     * @return bool
     */
    public static function addFilter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return self::addHook('filter', $hook, $callback, $priority, $acceptedArgs);
    }
    
    /**
     * Execute action hooks
     * 
     * @param string $hook Action hook name
     * @param mixed ...$args Arguments to pass to callbacks
     * @return void
     */
    public static function doAction($hook, ...$args)
    {
        self::$currentFilter[] = $hook;
        
        // Increment execution counter
        if (!isset(self::$executionCount[$hook])) {
            self::$executionCount[$hook] = 0;
        }
        self::$executionCount[$hook]++;
        
        // Start performance tracking if debug enabled
        $startTime = APP_DEBUGBAR ? microtime(true) : 0;
        $startMemory = APP_DEBUGBAR ? memory_get_usage() : 0;
        
        // Get caller information for debug
        $callerInfo = null;
        if (APP_DEBUGBAR) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            if (isset($backtrace[1])) {
                $callerInfo = [
                    'file' => isset($backtrace[1]['file']) ? str_replace('\\', '/', $backtrace[1]['file']) : 'unknown',
                    'line' => $backtrace[1]['line'] ?? 0,
                ];
            }
        }
        
        if (!isset(self::$actions[$hook])) {
            array_pop(self::$currentFilter);
            
            // Log empty action
            if (APP_DEBUGBAR) {
                //self::logDebug('action', $hook, 0, 0, 0, 'No callbacks registered', $args, null, null, $callerInfo);
            }
            
            return;
        }
        
        // Sort by priority (lower number = higher priority, runs first)
        ksort(self::$actions[$hook]);
        
        $callbacksExecuted = 0;
        
        foreach (self::$actions[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callbackId => $callback) {
                $acceptedArgs = $callback['accepted_args'];
                $function = $callback['function'];
                
                // Call with limited args based on accepted_args
                $callArgs = array_slice($args, 0, $acceptedArgs);
                
                $callbackStart = APP_DEBUGBAR ? microtime(true) : 0;
                $callbackStartMemory = APP_DEBUGBAR ? memory_get_usage() : 0;
                
                try {
                    call_user_func_array($function, $callArgs);
                    $callbacksExecuted++;
                    
                    // Log successful callback with full details
                    if (APP_DEBUGBAR) {
                        $callbackTime = microtime(true) - $callbackStart;
                        $callbackMemory = memory_get_usage() - $callbackStartMemory;
                        
                        $callbackLogEntry = [
                            'type' => 'action',
                            'hook' => $hook,
                            'callback_id' => $callbackId,
                            'priority' => $priority,
                            'time' => $callbackTime,
                            'memory' => $callbackMemory,
                            'result' => 'success',
                            'timestamp' => microtime(true),
                            'is_callback' => true,
                            'args' => self::serializeArgs($callArgs),
                            'args_count' => count($callArgs),
                        ];
                        
                        self::$debugLog[] = $callbackLogEntry;
                        
                        // Auto-save to session
                        self::autoSaveToSession();
                    }
                } catch (\Exception $e) {
                    // Log error but continue executing other hooks
                    if (function_exists('log_message')) {
                        log_message('error', "Error in action hook '{$hook}': " . $e->getMessage());
                    }
                    
                    // Log failed callback with full details
                    if (APP_DEBUGBAR) {
                        $callbackTime = microtime(true) - $callbackStart;
                        $callbackMemory = memory_get_usage() - $callbackStartMemory;
                        
                        $callbackLogEntry = [
                            'type' => 'action',
                            'hook' => $hook,
                            'callback_id' => $callbackId,
                            'priority' => $priority,
                            'time' => $callbackTime,
                            'memory' => $callbackMemory,
                            'result' => 'error: ' . $e->getMessage(),
                            'timestamp' => microtime(true),
                            'is_callback' => true,
                            'args' => self::serializeArgs($callArgs),
                            'args_count' => count($callArgs),
                        ];
                        
                        self::$debugLog[] = $callbackLogEntry;
                        
                        // Auto-save to session
                        self::autoSaveToSession();
                    }
                }
            }
        }
        
        array_pop(self::$currentFilter);
        
        // Log action execution summary
        if (APP_DEBUGBAR) {
            $totalTime = microtime(true) - $startTime;
            $totalMemory = memory_get_usage() - $startMemory;
            self::logDebug('action', $hook, $callbacksExecuted, $totalTime, $totalMemory, '', $args, null, null, $callerInfo);
        }
    }
    
    /**
     * Apply filter hooks
     * 
     * @param string $hook Filter hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed Filtered value
     */
    public static function applyFilters($hook, $value, ...$args)
    {
        self::$currentFilter[] = $hook;
        
        // Increment execution counter
        if (!isset(self::$executionCount[$hook])) {
            self::$executionCount[$hook] = 0;
        }
        self::$executionCount[$hook]++;
        
        // Start performance tracking if debug enabled
        $startTime = APP_DEBUGBAR ? microtime(true) : 0;
        $startMemory = APP_DEBUGBAR ? memory_get_usage() : 0;
        $originalValue = $value; // Store for debug comparison
        
        // Get caller information for debug
        $callerInfo = null;
        if (APP_DEBUGBAR) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            if (isset($backtrace[1])) {
                $callerInfo = [
                    'file' => isset($backtrace[1]['file']) ? str_replace('\\', '/', $backtrace[1]['file']) : 'unknown',
                    'line' => $backtrace[1]['line'] ?? 0,
                ];
            }
        }
        
        if (!isset(self::$filters[$hook])) {
            array_pop(self::$currentFilter);
            
            // Log empty filter
            if (APP_DEBUGBAR) {
                //self::logDebug('filter', $hook, 0, 0, 0, 'No callbacks registered', array_merge([$value], $args), $value, $value, $callerInfo);
            }
            
            return $value;
        }
        
        // Sort by priority (lower number = higher priority, runs first)
        ksort(self::$filters[$hook]);
        
        $callbacksExecuted = 0;
        
        foreach (self::$filters[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callbackId => $callback) {
                $acceptedArgs = $callback['accepted_args'];
                $function = $callback['function'];
                
                // Prepare args: first arg is always $value, then additional args
                $callArgs = array_merge([$value], array_slice($args, 0, $acceptedArgs - 1));
                
                $callbackStart = APP_DEBUGBAR ? microtime(true) : 0;
                $callbackStartMemory = APP_DEBUGBAR ? memory_get_usage() : 0;
                $beforeValue = $value;
                
                try {
                    $value = call_user_func_array($function, $callArgs);
                    $callbacksExecuted++;
                    
                    // Log successful callback with full details
                    if (APP_DEBUGBAR) {
                        $callbackTime = microtime(true) - $callbackStart;
                        $callbackMemory = memory_get_usage() - $callbackStartMemory;
                        $valueChanged = ($beforeValue !== $value);
                        
                        $callbackLogEntry = [
                            'type' => 'filter',
                            'hook' => $hook,
                            'callback_id' => $callbackId,
                            'priority' => $priority,
                            'time' => $callbackTime,
                            'memory' => $callbackMemory,
                            'result' => 'success (' . ($valueChanged ? 'modified' : 'unchanged') . ')',
                            'timestamp' => microtime(true),
                            'is_callback' => true,
                            'args' => self::serializeArgs($callArgs),
                            'args_count' => count($callArgs),
                            'before_value' => self::serializeValue($beforeValue),
                            'after_value' => self::serializeValue($value),
                            'value_changed' => $valueChanged,
                        ];
                        
                        self::$debugLog[] = $callbackLogEntry;
                        
                        // Auto-save to session
                        self::autoSaveToSession();
                    }
                } catch (\Exception $e) {
                    // Log error but continue with original value
                    if (function_exists('log_message')) {
                        log_message('error', "Error in filter hook '{$hook}': " . $e->getMessage());
                    }
                    
                    // Log failed callback with full details
                    if (APP_DEBUGBAR) {
                        $callbackTime = microtime(true) - $callbackStart;
                        $callbackMemory = memory_get_usage() - $callbackStartMemory;
                        
                        $callbackLogEntry = [
                            'type' => 'filter',
                            'hook' => $hook,
                            'callback_id' => $callbackId,
                            'priority' => $priority,
                            'time' => $callbackTime,
                            'memory' => $callbackMemory,
                            'result' => 'error: ' . $e->getMessage(),
                            'timestamp' => microtime(true),
                            'is_callback' => true,
                            'args' => self::serializeArgs($callArgs),
                            'args_count' => count($callArgs),
                        ];
                        
                        self::$debugLog[] = $callbackLogEntry;
                        
                        // Auto-save to session
                        self::autoSaveToSession();
                    }
                }
            }
        }
        
        array_pop(self::$currentFilter);
        
        // Log filter execution summary
        if (APP_DEBUGBAR) {
            $totalTime = microtime(true) - $startTime;
            $totalMemory = memory_get_usage() - $startMemory;
            $valueChanged = ($originalValue !== $value);
            self::logDebug('filter', $hook, $callbacksExecuted, $totalTime, $totalMemory, $valueChanged ? 'Value modified' : 'Value unchanged', array_merge([$originalValue], $args), $originalValue, $value, $callerInfo);
        }
        
        return $value;
    }
    
    /**
     * Remove an action hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority
     * @return bool
     */
    public static function removeAction($hook, $callback, $priority = 10)
    {
        return self::removeHook('action', $hook, $callback, $priority);
    }
    
    /**
     * Remove a filter hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority
     * @return bool
     */
    public static function removeFilter($hook, $callback, $priority = 10)
    {
        return self::removeHook('filter', $hook, $callback, $priority);
    }
    
    /**
     * Remove all hooks for a specific hook name
     * 
     * @param string $hook Hook name
     * @param string $type Type (action or filter)
     * @param int|null $priority Specific priority or null for all
     * @return bool
     */
    public static function removeAllHooks($hook, $type = 'action', $priority = null)
    {
        // Fix: Cannot use ternary with references, use if-else
        if ($type === 'action') {
            $storage = &self::$actions;
        } else {
            $storage = &self::$filters;
        }
        
        if (!isset($storage[$hook])) {
            return false;
        }
        
        if ($priority !== null) {
            unset($storage[$hook][$priority]);
        } else {
            unset($storage[$hook]);
        }
        
        return true;
    }
    
    /**
     * Check if hook has any callbacks registered
     * 
     * @param string $hook Hook name
     * @param string $type Type (action or filter)
     * @param callable|null $callback Specific callback or null for any
     * @return bool|int False if none, priority number if callback specified and found, true if hook has callbacks
     */
    public static function hasHook($hook, $type = 'action', $callback = null)
    {
        $storage = $type === 'action' ? self::$actions : self::$filters;
        
        if (!isset($storage[$hook])) {
            return false;
        }
        
        if ($callback === null) {
            return !empty($storage[$hook]);
        }
        
        // Search for specific callback
        foreach ($storage[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $registered) {
                if ($registered['function'] === $callback) {
                    return $priority;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get current filter being executed
     * 
     * @return string|null
     */
    public static function currentFilter()
    {
        return end(self::$currentFilter) ?: null;
    }
    
    /**
     * Check if currently executing a specific filter
     * 
     * @param string|null $hook Hook name (null to check if any filter is executing)
     * @return bool
     */
    public static function doingFilter($hook = null)
    {
        if ($hook === null) {
            return !empty(self::$currentFilter);
        }
        
        return in_array($hook, self::$currentFilter);
    }
    
    /**
     * Get all registered hooks
     * 
     * @param string $type Type (action, filter, or all)
     * @return array
     */
    public static function getAllHooks($type = 'all')
    {
        if ($type === 'action') {
            return self::$actions;
        }
        
        if ($type === 'filter') {
            return self::$filters;
        }
        
        return [
            'actions' => self::$actions,
            'filters' => self::$filters,
        ];
    }
    
    /**
     * Internal method to add hook
     * 
     * @param string $type Type (action or filter)
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $acceptedArgs Accepted arguments
     * @return bool
     */
    protected static function addHook($type, $hook, $callback, $priority, $acceptedArgs)
    {
        // Fix: Cannot use ternary with references, use if-else
        if ($type === 'action') {
            $storage = &self::$actions;
        } else {
            $storage = &self::$filters;
        }
        
        // Validate callback
        if (!is_callable($callback)) {
            if (function_exists('log_message')) {
                log_message('warning', "Invalid callback for {$type} hook: {$hook}");
            }
            return false;
        }
        
        // Initialize hook array if not exists
        if (!isset($storage[$hook])) {
            $storage[$hook] = [];
        }
        
        if (!isset($storage[$hook][$priority])) {
            $storage[$hook][$priority] = [];
        }
        
        // Generate unique ID for callback
        $id = self::generateCallbackId($callback);
        
        // Add callback
        $storage[$hook][$priority][$id] = [
            'function' => $callback,
            'accepted_args' => $acceptedArgs,
        ];
        
        return true;
    }
    
    /**
     * Internal method to remove hook
     * 
     * @param string $type Type (action or filter)
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priority
     * @return bool
     */
    protected static function removeHook($type, $hook, $callback, $priority)
    {
        // Fix: Cannot use ternary with references, use if-else
        if ($type === 'action') {
            $storage = &self::$actions;
        } else {
            $storage = &self::$filters;
        }
        
        if (!isset($storage[$hook][$priority])) {
            return false;
        }
        
        $id = self::generateCallbackId($callback);
        
        if (isset($storage[$hook][$priority][$id])) {
            unset($storage[$hook][$priority][$id]);
            
            // Clean up empty priority arrays
            if (empty($storage[$hook][$priority])) {
                unset($storage[$hook][$priority]);
            }
            
            // Clean up empty hook arrays
            if (empty($storage[$hook])) {
                unset($storage[$hook]);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate unique ID for callback
     * 
     * @param callable $callback Callback
     * @return string
     */
    protected static function generateCallbackId($callback)
    {
        if (is_string($callback)) {
            return $callback;
        }
        
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                return spl_object_hash($callback[0]) . '::' . $callback[1];
            }
            return $callback[0] . '::' . $callback[1];
        }
        
        if (is_object($callback)) {
            if ($callback instanceof \Closure) {
                return spl_object_hash($callback);
            }
            return get_class($callback) . '::__invoke';
        }
        
        return md5(serialize($callback));
    }
    
    /**
     * Get number of callbacks registered for a hook
     * 
     * @param string $hook Hook name
     * @param string $type Type (action or filter)
     * @return int Number of registered callbacks
     */
    public static function getCallbackCount($hook, $type = 'action')
    {
        $storage = $type === 'action' ? self::$actions : self::$filters;
        
        if (!isset($storage[$hook])) {
            return 0;
        }
        
        $count = 0;
        foreach ($storage[$hook] as $callbacks) {
            $count += count($callbacks);
        }
        
        return $count;
    }
    
    /**
     * Get number of times a hook was executed
     * 
     * WordPress-compatible (did_action/did_filter)
     * 
     * @param string $hook Hook name
     * @return int Execution count
     */
    public static function getExecutionCount($hook)
    {
        return self::$executionCount[$hook] ?? 0;
    }
    
    /**
     * Reset execution counter
     * 
     * Useful for testing
     * 
     * @param string|null $hook Specific hook or null for all
     * @return void
     */
    public static function resetExecutionCount($hook = null)
    {
        if ($hook === null) {
            self::$executionCount = [];
        } else {
            unset(self::$executionCount[$hook]);
        }
    }
    
    /**
     * Reset all hooks (for testing)
     * 
     * Clears all registered actions, filters, and execution counters
     * 
     * @return void
     */
    public static function resetAll()
    {
        self::$actions = [];
        self::$filters = [];
        self::$currentFilter = [];
        self::$executionCount = [];
        self::$debugLog = [];
        self::$performanceLog = [];
    }
    
    /**
     * Log hook execution for debug
     * 
     * @param string $type Type (action or filter)
     * @param string $hook Hook name
     * @param int $callbackCount Number of callbacks executed
     * @param float $time Execution time
     * @param int $memory Memory used
     * @param string $note Additional note
     * @param array $args Arguments passed to hook
     * @param mixed $beforeValue Before value (for filters)
     * @param mixed $afterValue After value (for filters)
     * @param array|null $callerInfo Caller file and line info
     * @return void
     */
    protected static function logDebug($type, $hook, $callbackCount, $time, $memory, $note = '', $args = [], $beforeValue = null, $afterValue = null, $callerInfo = null)
    {
        $logEntry = [
            'type' => $type,
            'hook' => $hook,
            'callbacks' => $callbackCount,
            'time' => $time,
            'memory' => $memory,
            'note' => $note,
            'timestamp' => microtime(true),
            'args' => self::serializeArgs($args),
            'args_count' => count($args),
        ];
        
        // Add caller information
        if ($callerInfo !== null) {
            $logEntry['caller_file'] = $callerInfo['file'];
            $logEntry['caller_line'] = $callerInfo['line'];
        }
        
        // Add before/after values for filters
        if ($type === 'filter' && $beforeValue !== null) {
            $logEntry['before_value'] = self::serializeValue($beforeValue);
            $logEntry['after_value'] = self::serializeValue($afterValue);
            $logEntry['value_changed'] = ($beforeValue !== $afterValue);
        }
        
        self::$debugLog[] = $logEntry;
        
        // Store in performance log for aggregation
        if (!isset(self::$performanceLog[$hook])) {
            self::$performanceLog[$hook] = [
                'type' => $type,
                'executions' => 0,
                'total_time' => 0,
                'total_memory' => 0,
                'total_callbacks' => 0,
            ];
        }
        
        self::$performanceLog[$hook]['executions']++;
        self::$performanceLog[$hook]['total_time'] += $time;
        self::$performanceLog[$hook]['total_memory'] += $memory;
        self::$performanceLog[$hook]['total_callbacks'] += $callbackCount;
        
        // Auto-save to session
        self::autoSaveToSession();
    }
    
    
    /**
     * Serialize arguments for debug display
     * 
     * @param array $args Arguments array
     * @return array Serialized arguments
     */
    protected static function serializeArgs($args)
    {
        $serialized = [];
        foreach ($args as $index => $arg) {
            $serialized[$index] = self::serializeValue($arg);
        }
        return $serialized;
    }
    
    /**
     * Serialize a single value for debug display
     * 
     * @param mixed $value Value to serialize
     * @return array Serialized value info
     */
    protected static function serializeValue($value)
    {
        $type = gettype($value);
        
        switch ($type) {
            case 'string':
                return [
                    'type' => 'string',
                    'value' => mb_strlen($value) > 200 ? mb_substr($value, 0, 200) . '...' : $value,
                    'length' => mb_strlen($value),
                ];
            
            case 'integer':
            case 'double':
            case 'boolean':
            case 'NULL':
                return [
            'type' => $type,
                    'value' => $value,
                ];
            
            case 'array':
                return [
                    'type' => 'array',
                    'count' => count($value),
                    'keys' => array_keys($value),
                    'preview' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
                ];
            
            case 'object':
                return [
                    'type' => 'object',
                    'class' => get_class($value),
                    'methods' => get_class_methods($value),
                ];
            
            case 'resource':
                return [
                    'type' => 'resource',
                    'resource_type' => get_resource_type($value),
                ];
            
            default:
                return [
                    'type' => $type,
                    'value' => 'unknown',
        ];
        }
    }
    
    /**
     * Get debug log (for debugbar integration)
     * 
     * @return array Debug log entries
     */
    public static function getDebugLog()
    {
        return self::$debugLog;
    }
    
    /**
     * Get performance log (for debugbar integration)
     * 
     * @return array Performance statistics per hook
     */
    public static function getPerformanceLog()
    {
        return self::$performanceLog;
    }
    
    /**
     * Get formatted debug output for debugbar
     * 
     * @return array Formatted for debugbar display
     */
    public static function getDebugBarData()
    {
        $totalHooks = count(self::$performanceLog);
        $totalExecutions = array_sum(array_column(self::$performanceLog, 'executions'));
        $totalTime = array_sum(array_column(self::$performanceLog, 'total_time'));
        $totalCallbacks = array_sum(array_column(self::$performanceLog, 'total_callbacks'));
        
        // Sort by total time (slowest first)
        $sorted = self::$performanceLog;
        uasort($sorted, function($a, $b) {
            return $b['total_time'] <=> $a['total_time'];
        });
        
        return [
            'summary' => [
                'total_hooks' => $totalHooks,
                'total_executions' => $totalExecutions,
                'total_callbacks' => $totalCallbacks,
                'total_time' => $totalTime,
                'total_time_ms' => round($totalTime * 1000, 2),
            ],
            'hooks' => $sorted,
            'detailed_log' => self::$debugLog,
        ];
    }
    
    /**
     * Auto-save hooks to session (called after each hook/callback execution)
     * Groups by URI - hooks from same request are accumulated
     * Separates by time - if > 1 second gap, treat as new request
     * 
     * @return void
     */
    protected static function autoSaveToSession()
    {
        if (!APP_DEBUGBAR) {
            return;
        }
        
        if (!class_exists('\System\Libraries\Session')) {
            return;
        }
        
        // Get current URI and method
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestKey = $currentMethod . ' ' . $currentUri;
        $currentTime = microtime(true);
        
        // Get existing history from session
        $history = \System\Libraries\Session::get(self::SESSION_KEY, []);
        if (!is_array($history)) {
            $history = [];
        }
        
        // Check if latest entry matches current request
        $isCurrentRequest = false;
        if (!empty($history) && isset($history[0]['request_info'])) {
            $latestKey = ($history[0]['request_info']['method'] ?? 'GET') . ' ' . ($history[0]['request_info']['uri'] ?? '/');
            
            // Get last hook execution time (not request completion time)
            $lastHookTime = 0;
            if (!empty($history[0]['detailed_log'])) {
                // Get timestamp of last hook in detailed_log
                $lastLog = end($history[0]['detailed_log']);
                $lastHookTime = $lastLog['timestamp'] ?? 0;
            }
            
            // If no hooks yet, use request start time
            if ($lastHookTime === 0) {
                $lastHookTime = $history[0]['request_info']['microtime'] ?? 0;
            }
            
            $timeDiff = $currentTime - $lastHookTime;
            
            // Same request if: same URI AND time gap < 1 second since last hook
            $isCurrentRequest = ($latestKey === $requestKey) && ($timeDiff < 1.0);
        }
        
        if (!$isCurrentRequest) {
            // New request - create new entry
            $newEntry = [
                'summary' => [
                    'total_hooks' => 0,
                    'total_executions' => 0,
                    'total_callbacks' => 0,
                    'total_time' => 0,
                    'total_time_ms' => 0,
                ],
                'hooks' => [],
                'detailed_log' => [],
                'request_info' => [
                    'uri' => $currentUri,
                    'method' => $currentMethod,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'microtime' => $currentTime,
                ],
            ];
            
            array_unshift($history, $newEntry);
            $history = array_slice($history, 0, self::MAX_HISTORY_REQUESTS);
        }
        
        // Get current data and update history[0]
        $currentData = self::getDebugBarData();
        $history[0]['summary'] = $currentData['summary'];
        $history[0]['hooks'] = $currentData['hooks'];
        $history[0]['detailed_log'] = $currentData['detailed_log'];
        
        // Update timestamp
        $history[0]['request_info']['timestamp'] = date('Y-m-d H:i:s');
        $history[0]['request_info']['microtime'] = $currentTime;
        
        // Save back to session
        \System\Libraries\Session::set(self::SESSION_KEY, $history);
    }
    
    /**
     * Save current request hooks to session history (manual call)
     * Optional - autoSaveToSession already handles it automatically
     * 
     * @return void
     */
    public static function saveToHistory()
    {
        self::autoSaveToSession();
    }
    
    /**
     * Get hooks history from session (excludes current request)
     * 
     * @return array Array of hooks data from previous requests
     */
    public static function getHistory()
    {
        if (!class_exists('\System\Libraries\Session')) {
            return [];
        }
        
        $history = \System\Libraries\Session::get(self::SESSION_KEY, []);
        if (!is_array($history) || empty($history)) {
            return [];
        }
        
        // Get current URI to exclude it
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $currentKey = $currentMethod . ' ' . $currentUri;
        
        // Return history excluding current request (skip index 0 if it matches current)
        if (isset($history[0]['request_info'])) {
            $firstKey = ($history[0]['request_info']['method'] ?? 'GET') . ' ' . ($history[0]['request_info']['uri'] ?? '/');
            
            if ($firstKey === $currentKey) {
                // First entry is current request, return from index 1 onwards
                return array_slice($history, 1);
            }
        }
        
        // All entries are from previous requests
        return $history;
    }
    
    /**
     * Clear hooks history
     * 
     * @return void
     */
    public static function clearHistory()
    {
        if (class_exists('\System\Libraries\Session')) {
            \System\Libraries\Session::delete(self::SESSION_KEY);
        }
    }
}


