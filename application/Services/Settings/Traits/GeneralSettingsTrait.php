<?php

namespace App\Services\Settings\Traits;

/**
 * General settings: tabs and fields (per PROGRESSING_TASK – theo từng ngôn ngữ).
 * Tabs: Site Identity, URL & Language, Regional, Index.
 *
 * @package App\Services\Settings\Traits
 */
trait GeneralSettingsTrait
{
    public function getGeneralSettingsGroup(): array
    {
        return [
            'id' => 'general',
            'icon' => 'settings',
            'title' => __('General Settings'),
            'description' => __('Basic website configuration and general options'),
            'detail' => __('Site identity, URL & language, regional and index settings'),
            'url' => admin_url('settings/general'),
            'tabs' => [
                ['id' => 'site_identity', 'label' => __('Site Identity')],
                ['id' => 'url_language', 'label' => __('URL & Language')],
                ['id' => 'regional', 'label' => __('Regional Settings')],
                ['id' => 'index', 'label' => __('Index Setting')],
            ],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getGeneralSettings(): array
    {
        load_helpers(['languages']);

        $tabs = [
            forms_tab('site_identity', __('Site Identity'), ['icon' => 'type']),
            forms_tab('url_language', __('URL & Language'), ['icon' => 'globe']),
            forms_tab('regional', __('Regional Settings'), ['icon' => 'globe']),
            forms_tab('index', __('Index Setting'), ['icon' => 'search']),
        ];

        $fields = [
            // TAB 1: Site Identity – 2 col layout
            forms_field('text', 'title', __('Title'), [
                'description' => __('Nhập title trang web'),
                'tab' => 'site_identity', 'default_value' => 'CMS Full Form', 'placeholder' => __('Site title'),
                'width_value' => 50,
            ]),
            forms_field('text', 'description', __('Description'), [
                'description' => __('Mô tả trang web'),
                'tab' => 'site_identity', 'placeholder' => __('Short description'),
                'width_value' => 50,
            ]),
            forms_field('image', 'favicon', __('Favicon'), [
                'description' => __('Biểu tượng trang web'),
                'tab' => 'site_identity', 'width_value' => 50,
            ]),
            forms_field('image', 'logo', __('Logo'), [
                'description' => __('Logo trang web'),
                'tab' => 'site_identity', 'width_value' => 50,
            ]),
            forms_field('text', 'link_iframe', __('Home: YouTube video URL'), [
                'description' => __('Link YouTube (watch, youtu.be, shorts) — khối “See how it works” trên trang chủ'),
                'tab' => 'site_identity', 'placeholder' => 'https://www.youtube.com/watch?v=…',
                'width_value' => 100,
                'storage_lang' => 'all',
            ]),
            forms_field('text', 'tagline', __('Tagline'), [
                'description' => __('Khẩu hiệu / tagline'),
                'tab' => 'site_identity', 'placeholder' => __('Site tagline'), 'width_value' => 50,
            ]),
            forms_field('text', 'brand', __('Brand'), [
                'description' => __('Thương hiệu'),
                'tab' => 'site_identity', 'placeholder' => __('Brand name'), 'width_value' => 50,
            ]),
            // TAB 2: URL & Language – lưu chung (storage_lang = all)
            forms_field('text', 'url', __('Canonical URL'), [
                'description' => __('Canonical domain (www / non-www), HTTPS mặc định'),
                'tab' => 'url_language', 'placeholder' => 'https://example.com', 'width_value' => 100,
                'storage_lang' => 'all',
            ]),
            forms_field('select', 'default_language', __('Default Language'), [
                'description' => __('Chọn ngôn ngữ mặc định cho toàn site'),
                'tab' => 'url_language', 'options' => $this->getLanguagesOptions(),
                'default_value' => defined('APP_LANG_DF') ? APP_LANG_DF : 'en', 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            forms_field('checkbox', 'languages_enabled', __('Languages Enabled'), [
                'description' => __('Select which languages are available'),
                'tab' => 'url_language', 'options' => $this->getLanguagesOptions(),
                'default_value' => [defined('APP_LANG_DF') ? APP_LANG_DF : 'en'], 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'add_lang_to_url', __('Add LANG to Default Language URL'), [
                'description' => __('False: domain.com/slug | True: domain.com/en/slug'),
                'tab' => 'url_language', 'default_value' => false, 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            // TAB 3: Regional – lưu chung (all)
            forms_field('select', 'timezone', __('Timezone'), [
                'description' => __('Chọn múi giờ'),
                'tab' => 'regional', 'options' => $this->getTimezoneOptions(),
                'default_value' => 'Asia/Ho_Chi_Minh', 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            forms_field('select', 'date_format', __('Date Format'), [
                'description' => __('Chọn Format ngày/tháng/năm (d/m/Y, m/d/Y, …)'),
                'tab' => 'regional',
                'options' => [
                    ['value' => 'd/m/Y', 'label' => 'd/m/Y (25/11/2025)'],
                    ['value' => 'm/d/Y', 'label' => 'm/d/Y (11/25/2025)'],
                    ['value' => 'Y-m-d', 'label' => 'Y-m-d (2025-11-25)'],
                    ['value' => 'd-m-Y', 'label' => 'd-m-Y (25-11-2025)'],
                ],
                'default_value' => 'd/m/Y', 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            forms_field('select', 'time_format', __('Time Format'), [
                'description' => __('Chọn Format thời gian (H:i, h:i A, …)'),
                'tab' => 'regional',
                'options' => [
                    ['value' => 'H:i', 'label' => '24h (14:30)'],
                    ['value' => 'h:i A', 'label' => '12h (02:30 PM)'],
                    ['value' => 'H:i:s', 'label' => '24h with seconds (14:30:45)'],
                ],
                'default_value' => 'H:i', 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            forms_field('select', 'start_week', __('Week Starts On'), [
                'description' => __('Thứ bắt đầu của tuần (0=Chủ nhật, 1=Thứ 2, …)'),
                'tab' => 'regional',
                'options' => [
                    ['value' => '0', 'label' => __('Sunday')],
                    ['value' => '1', 'label' => __('Monday')],
                    ['value' => '6', 'label' => __('Saturday')],
                ],
                'default_value' => '0', 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
            // TAB 4: Index – lưu chung (all)
            forms_field('boolean', 'enable_index', __('Allow search engines to index'), [
                'description' => __('Bật = cho phép index; Tắt = noindex/nofollow toàn site'),
                'tab' => 'index', 'default_value' => true, 'width_value' => 50,
                'storage_lang' => 'all',
            ]),
        ];

        return ['tabs' => $tabs, 'fields' => $fields];
    }

    /** @return array<array{value: string, label: string}> */
    protected function getLanguagesOptions(): array
    {
        $options = [];
        foreach (defined('APP_LANGUAGES') && is_array(APP_LANGUAGES) ? APP_LANGUAGES : [] as $code => $lang) {
            $options[] = ['value' => $code, 'label' => $lang['name'] ?? $code];
        }
        return $options ?: [['value' => 'en', 'label' => 'English']];
    }

    /** @return array<array{value: string, label: string}> */
    protected function getTimezoneOptions(): array
    {
        return [
            ['value' => 'Asia/Ho_Chi_Minh', 'label' => 'Asia/Ho Chi Minh (GMT+7)'],
            ['value' => 'UTC', 'label' => 'UTC (GMT+0)'],
            ['value' => 'America/New_York', 'label' => 'America/New York (GMT-5)'],
            ['value' => 'Europe/London', 'label' => 'Europe/London (GMT+0)'],
            ['value' => 'Asia/Tokyo', 'label' => 'Asia/Tokyo (GMT+9)'],
        ];
    }
}
