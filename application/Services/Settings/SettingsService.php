<?php

namespace App\Services\Settings;

use App\Services\Settings\Traits\GeneralSettingsTrait;
use App\Services\Settings\Traits\EmailSettingsTrait;
use App\Services\Settings\Traits\MediaSettingsTrait;
use App\Services\Settings\Traits\PerformanceSettingsTrait;
use App\Services\Settings\Traits\SeoSettingsTrait;
use App\Services\Settings\Traits\SocialApiSettingsTrait;
use App\Services\Settings\Traits\SecuritySettingsTrait;
use App\Services\Settings\Traits\DeveloperSettingsTrait;

/**
 * SettingsService - Settings Management
 * 
 * Handles all settings: groups/forms via traits, load/save via storage (lang = 'all' for performance, APP_LANG for others).
 * 
 * @package App\Services\Settings
 */
class SettingsService
{
    use GeneralSettingsTrait;
    use EmailSettingsTrait;
    use MediaSettingsTrait;
    use PerformanceSettingsTrait;
    use SeoSettingsTrait;
    use SocialApiSettingsTrait;
    use SecuritySettingsTrait;
    use DeveloperSettingsTrait;

    protected const SCOPE = 'application';

    /** Map setting type id => method name that returns ['tabs'=>..., 'fields'=>...] */
    protected static $settingTypeMethods = [
        'general' => 'getGeneralSettings',
        'email' => 'getEmailSettings',
        'media' => 'getMediaSettings',
        'performance' => 'getPerformanceSettings',
        'seo' => 'getSeoSettings',
        'social_api' => 'getSocialApiSettings',
        'security' => 'getSecuritySettings',
        'developer' => 'getDeveloperSettings',
    ];

    /**
     * All registered setting type ids (for routing / __call).
     *
     * @return string[]
     */
    public function getSettingTypeIds(): array
    {
        return array_keys(self::$settingTypeMethods);
    }

    /**
     * Get one group by type id (for title, form_options, etc.).
     *
     * @param string $type Setting type id
     * @return array|null Group array or null if not found
     */
    public function getGroup(string $type): ?array
    {
        foreach ($this->getSettingsGroups() as $group) {
            if (($group['id'] ?? '') === $type) {
                return $group;
            }
        }
        return null;
    }

    /**
     * Get tabs + fields for a setting type (for form build).
     *
     * @param string $type Setting type id
     * @return array{tabs: array, fields: array}
     */
    public function getSettingsByType(string $type): array
    {
        $method = self::$settingTypeMethods[$type] ?? null;
        if ($method === null || !method_exists($this, $method)) {
            return ['tabs' => [], 'fields' => []];
        }
        return $this->{$method}();
    }

    /**
     * Form options for forms_create (e.g. app_lang for performance).
     *
     * @param string $type Setting type id
     * @return array
     */
    public function getFormOptions(string $type): array
    {
        $group = $this->getGroup($type);
        return $group['form_options'] ?? [];
    }

    /**
     * Language key for storage (when whole page uses one lang).
     * TOÀN TRANG (all): performance, media, email, social_api, security, developer.
     * THEO TỪNG NGÔN NGỮ hoặc MIX (general, seo): dùng per-field storage_lang trong load/save.
     */
    public function getStorageLang(string $settingType): string
    {
        $globalTypes = ['performance', 'media', 'email', 'social_api', 'security', 'developer'];
        if (in_array($settingType, $globalTypes, true)) {
            return 'all';
        }
        return defined('APP_LANG') ? APP_LANG : 'en';
    }

    /** Setting types that have mixed storage: một số field lưu 'all', một số lưu theo ngôn ngữ. */
    protected function hasMixedStorageLang(string $settingType): bool
    {
        return in_array($settingType, ['general', 'seo'], true);
    }

    /** Current language for form (request or APP_LANG). */
    protected function getCurrentLang(): string
    {
        $lang = $_REQUEST['post_lang'] ?? null;
        if ($lang !== null && $lang !== '') {
            return $lang;
        }
        return defined('APP_LANG') ? APP_LANG : 'en';
    }

    /**
     * Load current values for a setting type.
     * Với general/seo: mỗi field load theo storage_lang ('all' hoặc current lang).
     *
     * @param string $settingType general|media|performance|seo|...
     * @param array  $fields      Field definitions (field_name, default_value, storage_lang, ...)
     * @return array Key => value for form
     */
    public function load(string $settingType, array $fields): array
    {
        $data = [];
        $currentLang = $this->getCurrentLang();

        if ($this->hasMixedStorageLang($settingType)) {
            foreach ($fields as $field) {
                $key = $field['field_name'] ?? null;
                if ($key === null) {
                    continue;
                }
                $lang = (isset($field['storage_lang']) && $field['storage_lang'] === 'all') ? 'all' : $currentLang;
                $default = $field['default_value'] ?? null;
                $data[$key] = storage_get($key, self::SCOPE, $lang, $default);
            }
            return $data;
        }

        $lang = $this->getStorageLang($settingType);
        foreach ($fields as $field) {
            $key = $field['field_name'] ?? null;
            if ($key === null) {
                continue;
            }
            $default = $field['default_value'] ?? null;
            $data[$key] = storage_get($key, self::SCOPE, $lang, $default);
        }
        return $data;
    }

    /**
     * Save POST data for a setting type.
     * Với general/seo: mỗi field lưu theo storage_lang ('all' hoặc current lang).
     * For performance, also sets object_cache_enabled from cache_driver.
     *
     * @param string $settingType general|media|performance|...
     * @param array  $post        POST data (e.g. $_POST)
     */
    public function save(string $settingType, array $post): void
    {
        if ($this->hasMixedStorageLang($settingType)) {
            $config = $this->getSettingsByType($settingType);
            $fields = $config['fields'] ?? [];
            $currentLang = $this->getCurrentLang();
            $fieldMap = [];
            foreach ($fields as $field) {
                $name = $field['field_name'] ?? null;
                if ($name === null) {
                    continue;
                }
                $fieldMap[$name] = (isset($field['storage_lang']) && $field['storage_lang'] === 'all') ? 'all' : $currentLang;
            }
            foreach ($post as $key => $value) {
                if ($key === 'csrf_token') {
                    continue;
                }
                if (!array_key_exists($key, $fieldMap)) {
                    continue;
                }
                storage_set($key, $value, self::SCOPE, $fieldMap[$key]);
            }
        } else {
            $lang = $this->getStorageLang($settingType);
            foreach ($post as $key => $value) {
                if ($key === 'csrf_token') {
                    continue;
                }
                storage_set($key, $value, self::SCOPE, $lang);
            }
        }

        if ($settingType === 'performance') {
            $driver = $post['cache_driver'] ?? (function_exists('get_option') ? get_option('cache_driver', 'files', 'all') : 'files');
            $objectCacheEnabled = in_array($driver, ['redis', 'memcached'], true) ? 1 : 0;
            storage_set('object_cache_enabled', $objectCacheEnabled, self::SCOPE, 'all');
        }
    }

    /**
     * Get all settings cards for overview page (built from registry).
     *
     * @return array Settings cards
     */
    public function getSettingsGroups(): array
    {
        $groups = [];
        foreach (self::$settingTypeMethods as $method) {
            $groupMethod = preg_replace('/Settings$/', 'SettingsGroup', $method);
            if (method_exists($this, $groupMethod)) {
                $groups[] = $this->{$groupMethod}();
            }
        }
        return $groups;
    }
}
