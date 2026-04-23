<?php
/**
 * Hooks Helper Functions - WordPress-style API
 * 
 * Provides WordPress-compatible hooks system for CMS FullForm
 * Functions: add_action, do_action, add_filter, apply_filters
 * 
 * @package System\Helpers
 * @author CMS FullForm
 * @version 1.0.0
 */

use System\Libraries\Hooks;

if (!function_exists('add_action')) {
    /**
     * Register an action hook
     * 
     * WordPress compatible function
     * 
     * @param string $hook Action hook name
     * @param callable $callback Callback function
     * @param int $priority Priority (default 10, lower runs first)
     * @param int $acceptedArgs Number of arguments callback accepts (default 1)
     * @return bool True on success
     * 
     * @example
     * add_action('ecommerce_order_created', 'send_order_email', 10, 2);
     * add_action('ecommerce_order_created', function($orderId, $customerId) {
     *     // Send email
     * }, 10, 2);
     */
    function add_action($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return Hooks::addAction($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('do_action')) {
    /**
     * Execute action hooks
     * 
     * WordPress compatible function
     * 
     * @param string $hook Action hook name
     * @param mixed ...$args Arguments to pass to callbacks
     * @return void
     * 
     * @example
     * do_action('ecommerce_order_created', $orderId, $customerId);
     * do_action('ecommerce_product_updated', $productId);
     */
    function do_action($hook, ...$args)
    {
        Hooks::doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Register a filter hook
     * 
     * WordPress compatible function
     * 
     * @param string $hook Filter hook name
     * @param callable $callback Callback function (must return filtered value)
     * @param int $priority Priority (default 10, lower runs first)
     * @param int $acceptedArgs Number of arguments callback accepts (default 1)
     * @return bool True on success
     * 
     * @example
     * add_filter('ecommerce_product_price', 'apply_custom_discount', 10, 2);
     * add_filter('ecommerce_payment_gateways', function($gateways) {
     *     $gateways[] = 'MyPlugin\StripeGateway';
     *     return $gateways;
     * });
     */
    function add_filter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return Hooks::addFilter($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Apply filter hooks
     * 
     * WordPress compatible function
     * 
     * @param string $hook Filter hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments to pass to callbacks
     * @return mixed Filtered value
     * 
     * @example
     * $price = apply_filters('ecommerce_product_price', $basePrice, $productId);
     * $gateways = apply_filters('ecommerce_payment_gateways', []);
     */
    function apply_filters($hook, $value, ...$args)
    {
        return Hooks::applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('remove_action')) {
    /**
     * Remove an action hook
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority (default 10)
     * @return bool True if removed
     * 
     * @example
     * remove_action('ecommerce_order_created', 'send_order_email', 10);
     */
    function remove_action($hook, $callback, $priority = 10)
    {
        return Hooks::removeAction($hook, $callback, $priority);
    }
}

if (!function_exists('remove_filter')) {
    /**
     * Remove a filter hook
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority (default 10)
     * @return bool True if removed
     * 
     * @example
     * remove_filter('ecommerce_product_price', 'apply_custom_discount', 10);
     */
    function remove_filter($hook, $callback, $priority = 10)
    {
        return Hooks::removeFilter($hook, $callback, $priority);
    }
}

if (!function_exists('remove_all_actions')) {
    /**
     * Remove all action hooks for a specific hook name
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @param int|null $priority Specific priority or null for all
     * @return bool
     * 
     * @example
     * remove_all_actions('ecommerce_order_created');
     * remove_all_actions('ecommerce_order_created', 10); // Only priority 10
     */
    function remove_all_actions($hook, $priority = null)
    {
        return Hooks::removeAllHooks($hook, 'action', $priority);
    }
}

if (!function_exists('remove_all_filters')) {
    /**
     * Remove all filter hooks for a specific hook name
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @param int|null $priority Specific priority or null for all
     * @return bool
     * 
     * @example
     * remove_all_filters('ecommerce_product_price');
     */
    function remove_all_filters($hook, $priority = null)
    {
        return Hooks::removeAllHooks($hook, 'filter', $priority);
    }
}

if (!function_exists('has_action')) {
    /**
     * Check if action hook has callbacks
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @param callable|null $callback Specific callback or null for any
     * @return bool|int False if none, priority if callback found, true if hook has callbacks
     * 
     * @example
     * if (has_action('ecommerce_order_created')) {
     *     // Hook has callbacks
     * }
     */
    function has_action($hook, $callback = null)
    {
        return Hooks::hasHook($hook, 'action', $callback);
    }
}

if (!function_exists('has_filter')) {
    /**
     * Check if filter hook has callbacks
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @param callable|null $callback Specific callback or null for any
     * @return bool|int False if none, priority if callback found, true if hook has callbacks
     * 
     * @example
     * if (has_filter('ecommerce_product_price')) {
     *     // Filter has callbacks
     * }
     */
    function has_filter($hook, $callback = null)
    {
        return Hooks::hasHook($hook, 'filter', $callback);
    }
}

if (!function_exists('current_filter')) {
    /**
     * Get name of current filter being executed
     * 
     * WordPress compatible function
     * 
     * @return string|null Current filter name or null
     * 
     * @example
     * add_filter('my_filter', function($value) {
     *     $current = current_filter(); // Returns 'my_filter'
     *     return $value;
     * });
     */
    function current_filter()
    {
        return Hooks::currentFilter();
    }
}

if (!function_exists('current_action')) {
    /**
     * Get name of current action being executed
     * 
     * WordPress compatible alias for current_filter()
     * 
     * @return string|null Current action name or null
     */
    function current_action()
    {
        return Hooks::currentFilter();
    }
}

if (!function_exists('doing_filter')) {
    /**
     * Check if currently executing a filter
     * 
     * WordPress compatible function
     * 
     * @param string|null $hook Hook name (null to check if any)
     * @return bool
     * 
     * @example
     * if (doing_filter('ecommerce_product_price')) {
     *     // Currently in this filter
     * }
     */
    function doing_filter($hook = null)
    {
        return Hooks::doingFilter($hook);
    }
}

if (!function_exists('doing_action')) {
    /**
     * Check if currently executing an action
     * 
     * WordPress compatible alias for doing_filter()
     * 
     * @param string|null $hook Hook name (null to check if any)
     * @return bool
     */
    function doing_action($hook = null)
    {
        return Hooks::doingFilter($hook);
    }
}

if (!function_exists('did_action')) {
    /**
     * Get number of times an action was executed
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @return int Execution count
     * 
     * @example
     * do_action('my_action');
     * do_action('my_action');
     * echo did_action('my_action'); // Returns: 2
     */
    function did_action($hook)
    {
        return Hooks::getExecutionCount($hook);
    }
}

if (!function_exists('did_filter')) {
    /**
     * Get number of times a filter was executed
     * 
     * WordPress compatible function
     * 
     * @param string $hook Hook name
     * @return int Execution count
     * 
     * @example
     * apply_filters('my_filter', $value);
     * apply_filters('my_filter', $value);
     * echo did_filter('my_filter'); // Returns: 2
     */
    function did_filter($hook)
    {
        return Hooks::getExecutionCount($hook);
    }
}

