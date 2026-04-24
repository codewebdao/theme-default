<?php
/**
 * ACFields Helper Functions
 * 
 * WordPress-style wrapper functions
 * Simple API for posttype operations
 * 
 * @package Plugins\ACFields\Helpers
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('PATH_APP')) {
    exit('No direct script access allowed');
}

if (!function_exists('register_posttype')) {
    /**
     * Register a post type (WordPress-style)
     * 
     * @param string $posttype Posttype slug
     * @param array $args Posttype arguments
     * @param bool $withDefaultFields Whether to include default fields from Config (default: true)
     * @return array ['success' => bool, 'errors' => array, 'data' => array]
     * 
     * @example
     * // With default fields (title, slug, status, created_at, etc.)
     * register_posttype('products', [
     *     'name' => 'Products',
     *     'languages' => ['all'],
     *     'fields' => [
     *         ['field_name' => 'price', 'type' => 'Number', 'label' => 'Price']
     *     ],
     * ]);
     * 
     * // Without default fields (custom fields only)
     * register_posttype('simple_data', [
     *     'name' => 'Simple Data',
     *     'languages' => ['all'],
     *     'fields' => [
     *         ['field_name' => 'value', 'type' => 'Text', 'label' => 'Value']
     *     ],
     * ], false);
     */
    function register_posttype($posttype, $args = [], $withDefaultFields = true) {
        $posttypeExists = \System\Database\DB::table('posttype')->where('slug', $posttype)->exists();
        if (!empty($args['slug']) && empty($args['name'])){
            $args['name'] = $args['slug'];
        }
        if (empty($args['slug']) && !empty($args['name'])){
            $args['slug'] = _sqlname($args['name']);
        }
        if (empty($args['slug']) && empty($args['name'])){
            return array('success' => false, 'message' => 'Slug or name is required to register posttype');
        }

        //Set Menu Display at Sidebar if not setting Menu.
        if (!empty($args['menu'])){
            if (!posttype_config($args['menu']) && $args['menu'] != $args['slug']){
                $args['menu'] = '';
            }
        }
        if (!isset($args['status'])){
            $args['status'] = 'active';
        }
        if (!isset($args['is_locked'])){
            $args['is_locked'] = 0;
        }
        if (!isset($args['languages'])){
            $args['languages'] = ['all'];
        }
        // Check if already exists
        if ($posttypeExists) {
            $service = new \Plugins\Acfields\Services\AcfieldsService();
            if ($service->enable($posttype)) {
                return array('success' => true, 'message' => 'Posttype already exists');
            }else{
                return array('success' => false, 'message' => 'Failed to enable posttype');
            }
        }else{
            // Add slug to args
            $args['slug'] = $posttype;
            // Use AcfieldsService with $withDefaultFields parameter
            $service = new \Plugins\Acfields\Services\AcfieldsService();
            return $service->createFromArray($args, $withDefaultFields);
        }
    }
}


if (!function_exists('update_posttype')) {
    /**
     * Update a posttype
     * 
     * @param string $slug Posttype slug
     * @param array $args New data
     * @return bool Success
     * 
     * @example
     * update_posttype('products', [
     *     'name' => 'Products Updated',
     *     'status' => 'active',
     *     'fields' => [...],
     * ]);
     */
    function update_posttype($slug, $args, $withDefaultFields = false) {
        $service = new \Plugins\Acfields\Services\AcfieldsService();
        $result = $service->update($slug, $args, $withDefaultFields);
        
        return $result['success'] ?? false;
    }
}

if (!function_exists('disable_posttype')) {
    /**
     * Disable a post type
     * 
     * @param string $posttype Posttype slug
     * @return bool Success
     */
    function disable_posttype($posttype) {
        $posttypeExists = \System\Database\DB::table('posttype')->where('slug', $posttype)->exists();
        if (!$posttypeExists) {
            return array('success' => true, 'message' => 'Posttype not found');
        }
         $service = new \Plugins\Acfields\Services\AcfieldsService();
         return $service->disable($posttype);
    }
}

if (!function_exists('unregister_posttype')) {
    /**
     * Unregister a post type
     * 
     * @param string $posttype Posttype slug
     * @return bool Success
     */
    function unregister_posttype($posttype) {
        $posttypeExists = \System\Database\DB::table('posttype')->where('slug', $posttype)->exists();
        if (!$posttypeExists) {
            return array('success' => true, 'message' => 'Posttype not found');
        }
        $service = new \Plugins\Acfields\Services\AcfieldsService();
        return $service->delete($posttype);
    }
}


if (!function_exists('register_posttypes_from_config')) {
    /**
     * Register multiple posttypes from config array
     * 
     * @param array $config Config data (from Config/Posttypes.php)
     * @return array Results [slug => success]
     * 
     * @example
     * $config = require __DIR__ . '/Config/Posttypes.php';
     * $results = register_posttypes_from_config($config);
     */
    function register_posttypes_from_config($config) {
        $results = [];
        
        // Extract posttypes array
        $posttypes = $config['posttypes'] ?? $config;
        
        if (!is_array($posttypes)) {
            return $results;
        }
        
        // Register each
        foreach ($posttypes as $slug => $data) {
            $results[$slug] = register_posttype($slug, $data);
        }
        
        return $results;
    }
}

if (!function_exists('register_posttype_from_json')) {
    /**
     * Register posttype from JSON file
     * 
     * @param string $jsonFile JSON file path
     * @param bool $withDefaultFields Whether to include default fields from Config (default: true)
     * @return bool Success
     * 
     * @example
     * // With default fields
     * register_posttype_from_json(__DIR__ . '/Posttypes/products.json');
     * 
     * // Without default fields
     * register_posttype_from_json(__DIR__ . '/Posttypes/simple.json', false);
     */
    function register_posttype_from_json($jsonFile, $withDefaultFields = true) {
        if (!file_exists($jsonFile)) {
            return false;
        }
        
        $jsonContent = file_get_contents($jsonFile);
        
        $service = new \Plugins\Acfields\Services\AcfieldsService();
        $result = $service->createFromJson($jsonContent, $withDefaultFields);
        
        return $result['success'] ?? false;
    }
}
