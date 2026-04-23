<?php

namespace System\Core;

/**
 * PluginLoader
 * 
 * Auto-discovers and loads plugin init files
 * Integrates with CMS plugin activation system
 * 
 * @package System\Core
 * @author CMS FullForm
 * @version 1.0.0
 */
class PluginLoader
{
    /**
     * Loaded plugin names
     * 
     * @var array
     */
    protected static $loadedPlugins = [];

    /**
     * Cached active plugins list (to avoid repeated DB queries)
     * 
     * @var array|null
     */
    protected static $activePlugins = null;

    /**
     * Initialize all active plugins
     * 
     * Main entry point - loads init.php from all active plugins
     * Uses option('plugins_active') to get list
     * 
     * @return void
     */
    public static function init($refresh = false)
    {
        // Fire before event (if hooks available)
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Hooks::action:before_plugins_init');
        }
        if (function_exists('do_action')) {
            do_action('before_plugins_init');
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Hooks::action:before_plugins_init');
        }
        $activePlugins = self::activeLists($refresh);
        if (empty($activePlugins)) {
            return;
        }

        // Load each plugin's init file
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('PluginLoader::loadPlugins');
        }
        foreach ($activePlugins as $plugin) {
            self::initPlugin($plugin);
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('PluginLoader::loadPlugins');
        }

        // Fire after all plugins loaded
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('Hooks::action:plugins_loaded');
        }
        if (function_exists('do_action')) {
            do_action('plugins_loaded', self::$loadedPlugins);
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('Hooks::action:plugins_loaded');
        }
    }

    /**
     * Get list of active plugins (with caching)
     * 
     * ✅ OPTIMIZED: Cache result to avoid repeated DB queries
     * 
     * @param bool $refresh Force refresh from database
     * @return array Array of plugin data
     */
    public static function activeLists($refresh = false)
    {
        // ✅ CACHE: Return cached result if available
        if (!$refresh && self::$activePlugins !== null) {
            return self::$activePlugins;
        }

        // Get active plugins from options table
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('PluginLoader::activeLists::queryDatabase');
        }
        $plugins = _json_decode( option('plugins_active', 'all', false) );
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('PluginLoader::activeLists::queryDatabase');
        }


        if (!is_array($plugins)) {
            $plugins = [];
        }
        // ✅ CACHE: Store result for next call
        self::$activePlugins = $plugins;
        return $plugins;
    }

    /**
     * Initialize single plugin
     * 
     * Loads plugin's init.php file
     * 
     * @param array|string $plugin Plugin data array or plugin name string
     * @return bool Success
     */
    public static function initPlugin($plugin)
    {
        // Extract plugin name
        $pluginName = is_array($plugin) ? ($plugin['name'] ?? '') : $plugin;
        if (empty($pluginName)) {
            return false;
        }

        // Build path to init file
        $initFile = PATH_PLUGINS . $pluginName . '/init.php';

        // Check if init file exists
        if (!file_exists($initFile)) {
            return false; // Not all plugins need init.php
        }

        // Start performance tracking for this plugin
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark("+ {$pluginName}");
        }

        try {
            require_once $initFile;

            // Track loaded plugin (avoid duplicates)
            if (!in_array($pluginName, self::$loadedPlugins)) {
                self::$loadedPlugins[] = $pluginName;
            }

            // Fire plugin loaded event
            if (function_exists('do_action')) {
                do_action('plugin_loaded', $pluginName);
            }

            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop("+ {$pluginName}");
            }

            return true;
        } catch (\Exception $e) {
            // Log error but don't stop other plugins
            if (function_exists('log_message')) {
                log_message('error', "Plugin init error ({$pluginName}): " . $e->getMessage());
            }

            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop("+ {$pluginName}");
            }

            return false;
        }
    }

    /**
     * Get list of loaded plugins
     * 
     * @return array Loaded plugin names
     */
    public static function loadedLists()
    {
        return self::$loadedPlugins;
    }

    /**
     * Check if specific plugin is loaded
     * 
     * @param string $pluginName Plugin name
     * @return bool
     */
    public static function isLoaded($pluginName)
    {
        return in_array($pluginName, self::$loadedPlugins);
    }
}
