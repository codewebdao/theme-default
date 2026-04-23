<?php

namespace App\Services\Settings\Traits;

/**
 * Social & API: Social Links, OAuth Login, Analytics, API Keys, Webhooks.
 * Lưu trữ: TOÀN TRANG (all).
 *
 * @package App\Services\Settings\Traits
 */
trait SocialApiSettingsTrait
{
    public function getSocialApiSettingsGroup(): array
    {
        return [
            'id' => 'social_api',
            'icon' => 'share-2',
            'title' => __('Social & API'),
            'description' => __('Social media, authentication and external integrations'),
            'detail' => __('Social links, OAuth, API keys, webhooks and analytics'),
            'url' => admin_url('settings/social_api'),
            'tabs' => [
                ['id' => 'social_links', 'label' => __('Social Links')],
                ['id' => 'oauth', 'label' => __('OAuth Login')],
                ['id' => 'analytics', 'label' => __('Analytics & Tracking')],
                ['id' => 'api_keys', 'label' => __('API Keys')],
                ['id' => 'webhooks', 'label' => __('Webhooks')],
            ],
            'form_options' => ['app_lang' => ['all']],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getSocialApiSettings(): array
    {
        $tabs = [
            forms_tab('social_links', __('Social Links'), ['icon' => 'share-2']),
            forms_tab('oauth', __('OAuth Login'), ['icon' => 'lock']),
            forms_tab('analytics', __('Analytics & Tracking'), ['icon' => 'activity']),
            forms_tab('api_keys', __('API Keys'), ['icon' => 'key']),
            forms_tab('webhooks', __('Webhooks'), ['icon' => 'webhook']),
        ];

        $fields = [
            // Social Links – 2 col
            forms_field('text', 'social_facebook', __('Facebook Page URL'), [
                'tab' => 'social_links', 'placeholder' => 'https://facebook.com/yourpage', 'width_value' => 50,
            ]),
            forms_field('text', 'social_twitter', __('X / Twitter URL'), [
                'tab' => 'social_links', 'placeholder' => 'https://twitter.com/youraccount', 'width_value' => 50,
            ]),
            forms_field('text', 'social_instagram', __('Instagram URL'), [
                'tab' => 'social_links', 'placeholder' => 'https://instagram.com/youraccount', 'width_value' => 50,
            ]),
            forms_field('text', 'social_youtube', __('YouTube Channel URL'), [
                'tab' => 'social_links', 'placeholder' => 'https://youtube.com/@yourchannel', 'width_value' => 50,
            ]),
            forms_field('text', 'social_linkedin', __('LinkedIn URL'), [
                'tab' => 'social_links', 'placeholder' => 'https://linkedin.com/company/yourcompany', 'width_value' => 50,
            ]),
            forms_field('text', 'social_tiktok', __('TikTok URL'), [
                'tab' => 'social_links', 'placeholder' => 'https://tiktok.com/@youraccount', 'width_value' => 50,
            ]),
            // OAuth – 2 col
            forms_field('boolean', 'enable_social_login', __('Enable Social Login'), [
                'tab' => 'oauth', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('text', 'provider', __('Social Service'), [
                'tab' => 'oauth', 'placeholder' => 'google, facebook', 'width_value' => 50,
            ]),
            forms_field('text', 'facebook_app_id', __('Facebook App ID'), ['tab' => 'oauth', 'width_value' => 50]),
            forms_field('text', 'facebook_app_secret', __('Facebook App Secret'), ['tab' => 'oauth', 'width_value' => 50]),
            forms_field('text', 'google_client_id', __('Google Client ID'), ['tab' => 'oauth', 'width_value' => 50]),
            forms_field('text', 'google_client_secret', __('Google Client Secret'), ['tab' => 'oauth', 'width_value' => 50]),
            // Analytics
            forms_field('text', 'ga_tracking_id', __('Google Analytics / GA4 ID'), [
                'tab' => 'analytics', 'placeholder' => 'G-XXXXXXXXXX', 'width_value' => 50,
            ]),
            forms_field('text', 'gtm_id', __('Google Tag Manager ID'), [
                'tab' => 'analytics', 'placeholder' => 'GTM-XXXXXXX', 'width_value' => 50,
            ]),
            forms_field('text', 'facebook_pixel_id', __('Facebook Pixel ID'), ['tab' => 'analytics', 'width_value' => 50]),
            forms_field('textarea', 'head_scripts', __('Head Scripts'), [
                'tab' => 'analytics', 'rows' => 5, 'width_value' => 100,
            ]),
            forms_field('textarea', 'body_scripts', __('Body End Scripts'), [
                'tab' => 'analytics', 'rows' => 5, 'width_value' => 100,
            ]),
            // API Keys
            forms_field('text', 'url_api_key', __('Url Api Key'), [
                'tab' => 'api_keys', 'placeholder' => 'https://api.example.com', 'width_value' => 50,
            ]),
            forms_field('text', 'api_secret_key', __('Api Secret Key'), ['tab' => 'api_keys', 'width_value' => 50]),
            forms_field('text', 'expires_at_key', __('Usage Time / Expires'), ['tab' => 'api_keys', 'width_value' => 33]),
            forms_field('boolean', 'enable_recaptcha', __('Enable reCAPTCHA'), [
                'tab' => 'api_keys', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('text', 'recaptcha_site_key', __('reCAPTCHA Site Key'), ['tab' => 'api_keys', 'width_value' => 50]),
            forms_field('text', 'recaptcha_secret_key', __('reCAPTCHA Secret Key'), ['tab' => 'api_keys', 'width_value' => 50]),
            forms_field('text', 'google_maps_api_key', __('Google Maps API Key'), ['tab' => 'api_keys', 'width_value' => 50]),
            forms_field('text', 'mapbox_token', __('Mapbox Token'), ['tab' => 'api_keys', 'width_value' => 50]),
            // Webhooks
            forms_field('boolean', 'enable_webhooks', __('Enable Webhooks'), [
                'tab' => 'webhooks', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('text', 'url_endpoint', __('Url Endpoint'), [
                'tab' => 'webhooks', 'placeholder' => 'https://example.com/webhook', 'width_value' => 50,
            ]),
            forms_field('boolean', 'is_active_hook', __('Status Hook'), [
                'tab' => 'webhooks', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('text', 'secret_token', __('Secret Token'), ['tab' => 'webhooks', 'width_value' => 50]),
            forms_field('textarea', 'webhook_urls', __('Webhook URLs (one per line)'), [
                'tab' => 'webhooks', 'rows' => 5, 'width_value' => 100,
            ]),
        ];

        return ['tabs' => $tabs, 'fields' => $fields];
    }
}
