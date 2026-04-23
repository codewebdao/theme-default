<?php

namespace App\Services\Settings\Traits;

/**
 * Security & Backup: Login, Captcha & 2FA, Backup, Firewall.
 * Lưu trữ: TOÀN TRANG (all).
 *
 * @package App\Services\Settings\Traits
 */
trait SecuritySettingsTrait
{
    public function getSecuritySettingsGroup(): array
    {
        return [
            'id' => 'security',
            'icon' => 'shield',
            'title' => __('Security & Backup'),
            'description' => __('Security settings and backup management'),
            'detail' => __('Login, Captcha & 2FA, Backup and firewall options'),
            'url' => admin_url('settings/security'),
            'tabs' => [
                ['id' => 'login', 'label' => __('Login')],
                ['id' => 'captcha_2fa', 'label' => __('Captcha & 2FA')],
                ['id' => 'backup', 'label' => __('Backup')],
                ['id' => 'firewall', 'label' => __('Firewall & IP Filter')],
            ],
            'form_options' => ['app_lang' => ['all']],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getSecuritySettings(): array
    {
        $tabs = [
            forms_tab('login', __('Login'), ['icon' => 'log-in']),
            forms_tab('captcha_2fa', __('Captcha & 2FA'), ['icon' => 'shield']),
            forms_tab('backup', __('Backup'), ['icon' => 'database']),
            forms_tab('firewall', __('Firewall & IP Filter'), ['icon' => 'lock']),
        ];

        $fields = [
            // TAB 1: Login – 2 col
            forms_field('number', 'login_max_attemps', __('Login Max Attempts'), [
                'tab' => 'login', 'min' => 3, 'max' => 20, 'default_value' => 5, 'width_value' => 33,
            ]),
            forms_field('number', 'login_lockout_time', __('Login Lockout Time (minutes)'), [
                'tab' => 'login', 'min' => 1, 'default_value' => 15, 'width_value' => 33,
            ]),
            forms_field('text', 'admin_url', __('Admin URL'), [
                'tab' => 'login', 'placeholder' => 'admin', 'width_value' => 33,
            ]),
            forms_field('boolean', 'enable_csrf_protection', __('Enable CSRF Protection'), [
                'tab' => 'login', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('number', 'session_timeout', __('Session Timeout (minutes)'), [
                'tab' => 'login', 'min' => 5, 'default_value' => 60, 'width_value' => 33,
            ]),
            forms_field('boolean', 'force_https', __('Force HTTPS'), [
                'tab' => 'login', 'default_value' => false, 'width_value' => 33,
            ]),
            // TAB 2: Captcha & 2FA
            forms_field('boolean', 'enable_2fa', __('Enable 2FA'), [
                'tab' => 'captcha_2fa', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'captcha_enable', __('Enable Captcha'), [
                'tab' => 'captcha_2fa', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('select', 'captcha_type', __('Captcha Type'), [
                'tab' => 'captcha_2fa',
                'options' => [['value' => 'recaptcha_v2', 'label' => 'reCAPTCHA v2'], ['value' => 'recaptcha_v3', 'label' => 'reCAPTCHA v3'], ['value' => 'hcaptcha', 'label' => 'hCaptcha'], ['value' => 'none', 'label' => __('None')]],
                'default_value' => 'none', 'width_value' => 33,
            ]),
            forms_field('text', 'captcha_public_key', __('Public Key'), ['tab' => 'captcha_2fa', 'width_value' => 50]),
            forms_field('text', 'captcha_secret_key', __('Secret Key'), ['tab' => 'captcha_2fa', 'width_value' => 50]),
            // TAB 3: Backup
            forms_field('boolean', 'backup_enable', __('Enable Backup'), [
                'tab' => 'backup', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('select', 'backup_schedule_frequency', __('Schedule Frequency'), [
                'tab' => 'backup',
                'options' => [['value' => 'daily', 'label' => __('Daily')], ['value' => 'weekly', 'label' => __('Weekly')], ['value' => 'monthly', 'label' => __('Monthly')]],
                'default_value' => 'weekly', 'width_value' => 33,
            ]),
            forms_field('number', 'backup_retention_count', __('Retention Count'), [
                'tab' => 'backup', 'min' => 1, 'max' => 90, 'default_value' => 7, 'width_value' => 33,
            ]),
            forms_field('boolean', 'backup_include_database', __('Backup Database'), [
                'tab' => 'backup', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('select', 'backup_storage_driver', __('Storage Driver'), [
                'tab' => 'backup',
                'options' => [['value' => 'local', 'label' => 'Local'], ['value' => 's3', 'label' => 'S3'], ['value' => 'gdrive', 'label' => 'G Drive'], ['value' => 'gcs', 'label' => 'GCS']],
                'default_value' => 'local', 'width_value' => 50,
            ]),
            forms_field('password', 'backup_encrypt_password', __('Encrypt Password'), [
                'tab' => 'backup', 'width_value' => 50,
            ]),
            // TAB 4: Firewall
            forms_field('boolean', 'enable_ip_whitelist', __('Enable IP Whitelist'), [
                'tab' => 'firewall', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('textarea', 'allowed_ips', __('Allowed IP Addresses'), [
                'tab' => 'firewall', 'rows' => 5, 'placeholder' => "127.0.0.1\n192.168.1.1", 'width_value' => 100,
            ]),
        ];

        return ['tabs' => $tabs, 'fields' => $fields];
    }
}
