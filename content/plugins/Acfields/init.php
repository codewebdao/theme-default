<?php
/**
 * ACFields Plugin Initialization
 * 
 * This file is auto-loaded by PluginLoader during CMS bootstrap
 * Provides core ACFields functionality indicator for other plugins
 * 
 * @package Plugins\ACFields
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('PATH_APP')) {
    exit('No direct script access allowed');
}

/**
 * ================================================================
 * PLUGIN REQUIREMENTS CHECK
 * ================================================================
 */

// Check PHP version (require 7.4+)
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    if (function_exists('log_message')) {
        log_message('error', 'ACFields plugin requires PHP 7.4 or higher. Current version: ' . PHP_VERSION);
    }
    return;
}

if (!extension_loaded('ionCube Loader')) {
    $activeItems = option('plugins_active');
    if (!empty($activeItems)) {
        foreach ($activeItems as $key => $item) {
            if ($item['name'] === 'Acfields') {
                //remove this $item
                unset($activeItems[$key]);
            }
        }
        option_set('plugins_active', array_values($activeItems), 'all');
    }
    \System\Libraries\Session::flash('error', __('You need to install PHP Extension ionCube, please contact your hosting provider'));
    redirect(admin_url('libraries/plugins'));
    return;
}

/**
 * ================================================================
 * CORE ACFIELDS FUNCTIONS
 * ================================================================
 * 
 * These functions act as indicators that ACFields is installed and active.
 * Other plugins can check: if (function_exists('acfield_get')) { ... }
 */

if (!function_exists('acfield_get')) {
    /**
     * Get ACField value by post ID and field name
     * 
     * This function indicates ACFields plugin is active
     * 
     * @param int $postId Post ID
     * @param string $fieldName Field name
     * @param mixed $default Default value if not found
     * @return mixed Field value
     */
    function acfield_get($postId, $fieldName, $default = null) {
        // TODO: Implement actual field retrieval logic
        // For now, just return default to indicate plugin is loaded
        return $default;
    }
}

if (!function_exists('acfield_set')) {
    /**
     * Set ACField value for a post
     * 
     * @param int $postId Post ID
     * @param string $fieldName Field name
     * @param mixed $value Value to set
     * @return bool Success
     */
    function acfield_set($postId, $fieldName, $value) {
        // TODO: Implement actual field setting logic
        return true;
    }
}

if (!function_exists('acfield_exists')) {
    /**
     * Check if ACField exists for a post
     * 
     * @param int $postId Post ID
     * @param string $fieldName Field name
     * @return bool Exists
     */
    function acfield_exists($postId, $fieldName) {
        // TODO: Implement actual field existence check
        return false;
    }
}

if (!function_exists('acfields_active')) {
    /**
     * Check if ACFields plugin is active
     * 
     * This is the main indicator function
     * 
     * @return bool Always returns true when this file is loaded
     */
    function acfields_active() {
        return true;
    }
}

/**
 * ================================================================
 * LOAD PLUGIN HELPERS
 * ================================================================
 */

// Load main ACFields helper (always required)
load_helpers(['Acfields'], 'Acfields');

/**
 * ================================================================
 * PLUGIN LOADED NOTIFICATION
 * ================================================================
 */

if (function_exists('log_message')) {
    log_message('info', 'ACFields plugin initialized successfully');
}

// Fire action to notify that ACFields is ready
if (function_exists('do_action')) {
    //do_action('acfields_loaded');
}

