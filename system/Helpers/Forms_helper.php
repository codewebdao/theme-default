<?php
/**
 * Forms Helper - Thin Wrapper for Fastform Library
 * 
 * Provides procedural API for Fastform library.
 * For advanced usage, use Fastform class directly.
 * 
 * @package System\Helpers
 */

if (!function_exists('forms_init')) {
    /**
     * Init form library
     * 
     * @param array $config Form configuration
     * @return \App\Libraries\Fastform
     * 
     * @example
     * // Fluent API
     * echo forms_init(['title' => 'Settings'])
     *     ->addField('text', 'api_key', 'API Key')
     *     ->setData($data)
     *     ->render();
     */
    function forms_init($config = []) {
        return new \App\Libraries\Fastform($config);
    }
}

if (!function_exists('forms_field')) {
    /**
     * Create field configuration array
     * 
     * Helper for building field config without Fastform instance.
     * 
     * @param string $type Field type
     * @param string $name Field name
     * @param string $label Field label
     * @param array $options Field options
     * @return array Field configuration
     * 
     * @example
     * $fields = [
     *     forms_field('text', 'api_key', 'API Key', ['required' => true]),
     *     forms_field('number', 'timeout', 'Timeout', ['default' => 30])
     * ];
     */
    function forms_field($type, $name, $label, $options = []) {
        return array_merge([
            'id' => \App\Libraries\Fastuuid::timeuuid(),
            'type' => ucfirst($type),
            'field_name' => $name,
            'label' => $label,
            'description' => '',
            'required' => false,
            'tab' => '',
            'default_value' => '',
        ], $options);
    }
}

if (!function_exists('forms_tab')) {
    /**
     * Create tab configuration array
     * 
     * @param string $id Tab ID
     * @param string $label Tab label
     * @param array $options Tab options
     * @return array Tab configuration
     * 
     * @example
     * $tabs = [
     *     forms_tab('general', 'General', ['icon' => 'settings']),
     *     forms_tab('advanced', 'Advanced', ['icon' => 'sliders'])
     * ];
     */
    function forms_tab($id, $label, $options = []) {
        return array_merge([
            'id' => $id,
            'label' => $label,
            'icon' => '',
        ], $options);
    }
}

if (!function_exists('forms_create')) {
    /**
     * Create form (shorthand)
     * 
     * @param string $title Form title
     * @param array $fields Fields array
     * @param string $submitUrl Submit URL
     * @param array $data Current data
     * @param array $tabs Optional tabs array
     * @param array $config Optional additional config (id, method, etc)
     * @return string Rendered HTML
     * 
     * @example
     * echo forms_create('Plugin Settings', [
     *     forms_field('text', 'api_key', 'API Key'),
     *     forms_field('boolean', 'debug', 'Debug Mode')
     * ], admin_url('save'), $currentData, $tabs);
     * 
     * // With tabs
     * echo forms_create('Settings', $fields, admin_url('save'), $data, $tabs);
     */
    function forms_create($title, $fields, $submitUrl, $data = [], $tabs = [], $config = []) {
        $formConfig = array_merge([
            'title' => $title,
            'submit_url' => $submitUrl
        ], $config);
        
        $form = new \App\Libraries\Fastform($formConfig);
        
        // Add tabs if provided
        if (!empty($tabs)) {
            foreach ($tabs as $tab) {
                $form->addTab(
                    $tab['id'],
                    $tab['label'],
                    $tab
                );
            }
        }
        
        // Add fields
        foreach ($fields as $field) {
            $form->addField(
                strtolower($field['type']),
                $field['field_name'],
                $field['label'],
                $field
            );
        }
        
        return $form->setData($data)->render();
    }
}
