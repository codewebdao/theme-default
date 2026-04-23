<?php

namespace App\Services\Settings\Traits;

/**
 * Developer settings: tabs and fields definition + group card for overview.
 *
 * @package App\Services\Settings\Traits
 */
trait DeveloperSettingsTrait
{
    public function getDeveloperSettingsGroup(): array
    {
        return [
            'id' => 'developer',
            'icon' => 'code',
            'title' => __('Developer Tools'),
            'description' => __('API, webhooks and development settings'),
            'detail' => __('REST API, GraphQL, debug mode, logs and developer options'),
            'url' => admin_url('settings/developer'),
            'tabs' => [
                ['id' => 'api', 'label' => __('REST API')],
                ['id' => 'graphql', 'label' => __('GraphQL')],
                ['id' => 'debug', 'label' => __('Debug & Logs')],
                ['id' => 'advanced', 'label' => __('Advanced Options')],
            ],
            'form_options' => ['app_lang' => ['all']],
        ];
    }

    public function getDeveloperSettings(): array
    {
        $tabs = [
            forms_tab('api', __('REST API'), ['icon' => 'globe']),
            forms_tab('graphql', __('GraphQL'), ['icon' => 'git-branch']),
            forms_tab('debug', __('Debug & Logs'), ['icon' => 'alert-circle']),
            forms_tab('advanced', __('Advanced Options'), ['icon' => 'sliders']),
        ];
        $fields = [
            forms_field('boolean', 'enable_rest_api', __('Enable REST API'), [
                'tab' => 'api', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('text', 'api_prefix', __('API Prefix'), [
                'tab' => 'api', 'default_value' => 'api', 'placeholder' => 'api', 'width_value' => 33,
            ]),
            forms_field('number', 'api_rate_limit', __('API Rate Limit'), [
                'tab' => 'api', 'min' => 10, 'default_value' => 60, 'width_value' => 33,
            ]),
            forms_field('boolean', 'enable_graphql', __('Enable GraphQL'), [
                'tab' => 'graphql', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('text', 'graphql_endpoint', __('GraphQL Endpoint'), [
                'tab' => 'graphql', 'default_value' => '/graphql', 'placeholder' => '/graphql', 'width_value' => 50,
            ]),
            forms_field('boolean', 'debug_mode', __('Debug Mode'), [
                'tab' => 'debug', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'enable_query_log', __('Enable Query Log'), [
                'tab' => 'debug', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('select', 'log_level', __('Log Level'), [
                'tab' => 'debug',
                'options' => [['value' => 'error', 'label' => 'Error'], ['value' => 'warning', 'label' => 'Warning'], ['value' => 'info', 'label' => 'Info'], ['value' => 'debug', 'label' => 'Debug']],
                'default_value' => 'error', 'width_value' => 33,
            ]),
            forms_field('boolean', 'enable_maintenance_mode', __('Maintenance Mode'), [
                'tab' => 'advanced', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('textarea', 'custom_php_config', __('Custom PHP Config'), [
                'tab' => 'advanced', 'rows' => 5, 'width_value' => 100,
            ]),
        ];
        return ['tabs' => $tabs, 'fields' => $fields];
    }
}
