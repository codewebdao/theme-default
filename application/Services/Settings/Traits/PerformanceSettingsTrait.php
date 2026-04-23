<?php

namespace App\Services\Settings\Traits;

/**
 * Performance settings: tabs and fields definition + group card for overview.
 * Stored with lang "all" (see SettingsService::getStorageLang / save).
 *
 * @package App\Services\Settings\Traits
 */
trait PerformanceSettingsTrait
{
    /**
     * One group entry for settings overview page.
     *
     * @return array
     */
    public function getPerformanceSettingsGroup(): array
    {
        return [
            'id' => 'performance',
            'icon' => 'zap',
            'title' => __('Performance'),
            'description' => __('Cache, optimization and performance settings'),
            'detail' => __('Page cache, object cache drivers, speed & minify'),
            'url' => admin_url('settings/performance'),
            'tabs' => [
                ['id' => 'cache_general', 'label' => __('Cache General')],
                ['id' => 'speed_minify', 'label' => __('Speed & Minify')],
                ['id' => 'cache_drivers', 'label' => __('Cache Drivers')],
            ],
            'form_options' => ['app_lang' => ['all']],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getPerformanceSettings(): array
    {
        $tabs = [
            forms_tab('cache_general', __('Cache General'), ['icon' => 'database']),
            forms_tab('speed_minify', __('Speed & Minify'), ['icon' => 'zap']),
            forms_tab('cache_drivers', __('Cache Drivers'), ['icon' => 'hard-drive']),
        ];
        $fields = $this->getPerformanceFields();
        return ['tabs' => $tabs, 'fields' => $fields];
    }

    /**
     * Performance form fields (override in service to customize).
     *
     * @return array
     */
    protected function getPerformanceFields(): array
    {
        return [
            forms_field('boolean', 'enable_app_cache', __('Enable Page Cache'), [
                'description' => __('Cache full HTML pages; reduce server load for anonymous users'),
                'tab' => 'cache_general', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('boolean', 'object_cache', __('Enable Object Cache'), [
                'description' => __('Cache objects and queries; storage is configured in Cache Drivers (Files, Redis, or Memcached)'),
                'tab' => 'cache_general', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('number', 'cache_ttl', __('Cache TTL (seconds)'), [
                'description' => __('Default cache duration in seconds'),
                'tab' => 'cache_general', 'min' => 60, 'default_value' => 3600, 'width_value' => 33,
            ]),
            forms_field('text', 'cache_key_prefix', __('Cache Key Prefix'), [
                'description' => __('Prefix to avoid key collision between sites'),
                'tab' => 'cache_general', 'placeholder' => 'cms_', 'width_value' => 33,
            ]),
            forms_field('boolean', 'cache_logged_out_only', __('Cache logged-out users only'), [
                'description' => __('Skip page cache when user is logged in'),
                'tab' => 'cache_general', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('textarea', 'exclude_url', __('Exclude URL'), [
                'description' => __('URLs or patterns to exclude from page cache (one per line)'),
                'tab' => 'cache_general', 'rows' => 3, 'width_value' => 50,
            ]),
            forms_field('textarea', 'exclude_cookie', __('Exclude Cookie'), [
                'description' => __('If any of these cookies are present, page cache is skipped (one per line)'),
                'tab' => 'cache_general', 'rows' => 2, 'width_value' => 50,
            ]),
            forms_field('boolean', 'device_variant_cache', __('Device variant cache'), [
                'description' => __('Separate cache for mobile and desktop'),
                'tab' => 'cache_general', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'user_variant_cache', __('User variant cache'), [
                'description' => __('Separate cache for guest and logged-in users'),
                'tab' => 'cache_general', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('text', 'nginx_purge_endpoint', __('Nginx Purge Endpoint'), [
                'description' => __('URL to call for Nginx cache purge'),
                'tab' => 'cache_general', 'placeholder' => 'http://127.0.0.1/nginx-purge', 'width_value' => 50,
            ]),
            forms_field('text', 'nginx_secret_key', __('Nginx Secret Key'), [
                'description' => __('Secret token for purge requests'),
                'tab' => 'cache_general', 'width_value' => 50,
            ]),
            forms_field('boolean', 'nginx_auto_purge_post', __('Auto purge on post update'), [
                'tab' => 'cache_general', 'default_value' => true, 'width_value' => 50,
            ]),
            forms_field('boolean', 'nginx_auto_purge_homepage', __('Auto purge homepage'), [
                'tab' => 'cache_general', 'default_value' => true, 'width_value' => 50,
            ]),
            forms_field('boolean', 'minify_css', __('Minify CSS'), [
                'description' => __('Enable or disable CSS minification'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 25,
            ]),
            forms_field('boolean', 'minify_js', __('Minify JS'), [
                'description' => __('Enable or disable JS minification'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 25,
            ]),
            forms_field('boolean', 'combine_css', __('Merge CSS'), [
                'description' => __('Combine multiple CSS files into one'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 25,
            ]),
            forms_field('boolean', 'combine_js', __('Merge JS'), [
                'description' => __('Combine multiple JS files into one'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 25,
            ]),
            forms_field('boolean', 'minify_html', __('Minify HTML'), [
                'description' => __('Remove extra whitespace from HTML output'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('boolean', 'defer_js', __('Defer JavaScript'), [
                'description' => __('Load JavaScript after page content'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'async_css', __('Async CSS'), [
                'description' => __('Load CSS without blocking render (preload + onload)'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('number', 'build_ttl_days', __('Build cache TTL (days)'), [
                'description' => __('Remove unused build files after N days'),
                'tab' => 'speed_minify', 'min' => 1, 'default_value' => 30, 'width_value' => 33,
            ]),
            forms_field('number', 'build_lru_max', __('Build LRU max files'), [
                'description' => __('Max build files to keep; oldest removed when exceeded'),
                'tab' => 'speed_minify', 'min' => 10, 'default_value' => 100, 'width_value' => 33,
            ]),
            forms_field('textarea', 'minify_css_exclude', __('Exclude from minify (CSS)'), [
                'description' => __('Files or patterns to skip (e.g. *.min.css, one per line)'),
                'tab' => 'speed_minify', 'rows' => 2, 'placeholder' => '*.min.css', 'width_value' => 50,
            ]),
            forms_field('textarea', 'minify_js_exclude', __('Exclude from minify (JS)'), [
                'description' => __('Files or patterns to skip (e.g. *.min.js, one per line)'),
                'tab' => 'speed_minify', 'rows' => 2, 'placeholder' => '*.min.js', 'width_value' => 50,
            ]),
            forms_field('textarea', 'combine_css_exclude', __('Exclude from merge (CSS)'), [
                'description' => __('CSS files to exclude from bundle (one per line)'),
                'tab' => 'speed_minify', 'rows' => 2, 'width_value' => 50,
            ]),
            forms_field('textarea', 'combine_js_exclude', __('Exclude from merge (JS)'), [
                'description' => __('JS files to exclude from bundle (one per line)'),
                'tab' => 'speed_minify', 'rows' => 2, 'width_value' => 50,
            ]),
            forms_field('boolean', 'self_host_external_assets', __('Self-host external assets'), [
                'description' => __('Download external (CDN) URLs to local when merging'),
                'tab' => 'speed_minify', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('number', 'external_ttl_days', __('External assets TTL (days)'), [
                'description' => __('Re-download external assets after N days'),
                'tab' => 'speed_minify', 'min' => 1, 'default_value' => 7, 'width_value' => 50,
            ]),
            forms_field('textarea', 'self_host_skip_whitelist', __('Do not self-host (whitelist)'), [
                'description' => __('Domains or patterns to keep external (e.g. *google*, one per line)'),
                'tab' => 'speed_minify', 'rows' => 2, 'width_value' => 100,
            ]),
            forms_field('select', 'cache_driver', __('Cache Driver'), [
                'description' => __('Where to store object cache: Files (local), Redis, or Memcached'),
                'tab' => 'cache_drivers',
                'options' => [
                    ['value' => 'files', 'label' => __('Files')],
                    ['value' => 'redis', 'label' => 'Redis'],
                    ['value' => 'memcached', 'label' => 'Memcached'],
                ],
                'default_value' => 'files', 'width_value' => 50,
            ]),
            forms_field('text', 'cache_path', __('Cache Path (Files)'), [
                'description' => __('Directory for file-based cache. Leave empty for default.'),
                'tab' => 'cache_drivers', 'placeholder' => 'cache/object', 'width_value' => 50,
            ]),
            forms_field('text', 'cache_host', __('Host'), [
                'description' => __('Redis or Memcached server host'),
                'tab' => 'cache_drivers', 'placeholder' => '127.0.0.1', 'width_value' => 50,
            ]),
            forms_field('number', 'cache_port', __('Port'), [
                'description' => __('e.g. 6379 (Redis) or 11211 (Memcached)'),
                'tab' => 'cache_drivers', 'min' => 1, 'max' => 65535, 'default_value' => 6379, 'width_value' => 50,
            ]),
            forms_field('text', 'cache_username', __('Username (Redis ACL)'), [
                'description' => __('Redis ACL username; leave empty if not required'),
                'tab' => 'cache_drivers', 'width_value' => 50,
            ]),
            forms_field('password', 'cache_password', __('Password'), [
                'description' => __('Stored encrypted.'), 'tab' => 'cache_drivers', 'width_value' => 50,
            ]),
            forms_field('number', 'cache_db_index', __('Redis DB Index'), [
                'description' => __('Redis database index 0–15 (Redis only)'),
                'tab' => 'cache_drivers', 'min' => 0, 'max' => 15, 'default_value' => 0, 'width_value' => 33,
            ]),
            forms_field('number', 'connection_timeout', __('Connection Timeout (seconds)'), [
                'description' => __('Maximum connection wait time for Redis/Memcached'),
                'tab' => 'cache_drivers', 'min' => 1, 'default_value' => 5, 'width_value' => 33,
            ]),
            forms_field('number', 'object_cache_ttl', __('Object Cache TTL (seconds)'), [
                'description' => __('TTL for object cache entries'),
                'tab' => 'cache_drivers', 'min' => 60, 'default_value' => 3600, 'width_value' => 33,
            ]),
            forms_field('text', 'cache_params', __('Query params in cache key'), [
                'description' => __('Comma-separated GET param names (e.g. id,page,paged)'),
                'tab' => 'cache_drivers', 'placeholder' => 'id,page,paged,limit,sort,order', 'width_value' => 50,
            ]),
            forms_field('text', 'cache_uri', __('Cache subpath'), [
                'description' => __('Subpath under content directory for file cache. Leave empty for default.'),
                'tab' => 'cache_drivers', 'placeholder' => 'cache', 'width_value' => 50,
            ]),
            forms_field('boolean', 'enable_query_cache', __('Enable Query Cache'), [
                'description' => __('Cache DB query results'),
                'tab' => 'cache_drivers', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('number', 'query_cache_ttl', __('Query Cache TTL (seconds)'), [
                'tab' => 'cache_drivers', 'min' => 60, 'default_value' => 300, 'width_value' => 50,
            ]),
            forms_field('boolean', 'optimize_tables_auto', __('Auto optimize tables'), [
                'description' => __('Run table optimize weekly'),
                'tab' => 'cache_drivers', 'default_value' => false, 'width_value' => 50,
            ]),
        ];
    }
}
