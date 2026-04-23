<?php

namespace App\Services\Settings\Traits;

/**
 * Email settings: SMTP, Sender, Test (no save), Template.
 * Lưu trữ: TOÀN TRANG (all) – SMTP & Sender; Template có thể JSON (global).
 *
 * @package App\Services\Settings\Traits
 */
trait EmailSettingsTrait
{
    public function getEmailSettingsGroup(): array
    {
        return [
            'id' => 'email',
            'icon' => 'mail',
            'title' => __('Email Settings'),
            'description' => __('SMTP configuration and email templates'),
            'detail' => __('Mail server, sender info, SMTP settings and email template management'),
            'url' => admin_url('settings/email'),
            'tabs' => [
                ['id' => 'smtp', 'label' => __('SMTP Settings')],
                ['id' => 'sender', 'label' => __('Sender Info')],
                ['id' => 'test_email', 'label' => __('Test Email')],
                ['id' => 'template', 'label' => __('Templates')],
            ],
            'form_options' => ['app_lang' => ['all']],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getEmailSettings(): array
    {
        $tabs = [
            forms_tab('smtp', __('SMTP Settings'), ['icon' => 'mail']),
            forms_tab('sender', __('Sender Info'), ['icon' => 'user']),
            forms_tab('test_email', __('Test Email'), ['icon' => 'send']),
            forms_tab('template', __('Templates'), ['icon' => 'file-text']),
        ];

        $fields = [
            // TAB 1: SMTP – 2 col
            forms_field('text', 'smtp_host', __('SMTP Host'), [
                'description' => __('vd: smtp.gmail.com'),
                'tab' => 'smtp', 'placeholder' => 'smtp.gmail.com', 'width_value' => 50,
            ]),
            forms_field('number', 'smtp_port', __('SMTP Port'), [
                'description' => __('465 / 587'),
                'tab' => 'smtp', 'min' => 1, 'max' => 65535, 'default_value' => 587, 'width_value' => 33,
            ]),
            forms_field('select', 'smtp_encryption', __('Encryption'), [
                'description' => __('none / ssl / tls'),
                'tab' => 'smtp',
                'options' => [['value' => 'none', 'label' => 'None'], ['value' => 'ssl', 'label' => 'SSL'], ['value' => 'tls', 'label' => 'TLS']],
                'default_value' => 'tls', 'width_value' => 33,
            ]),
            forms_field('boolean', 'smtp_enabled', __('Enable SMTP'), [
                'tab' => 'smtp', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('text', 'smtp_username', __('SMTP Username'), [
                'description' => __('Email gửi'),
                'tab' => 'smtp', 'placeholder' => 'user@example.com', 'width_value' => 50,
            ]),
            forms_field('password', 'smtp_password', __('SMTP Password'), [
                'description' => __('Nên encrypt'),
                'tab' => 'smtp', 'width_value' => 50,
            ]),
            forms_field('boolean', 'smtp_auth', __('SMTP Auth'), [
                'tab' => 'smtp', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('number', 'smtp_timeout', __('Timeout (giây)'), [
                'tab' => 'smtp', 'min' => 5, 'default_value' => 30, 'width_value' => 33,
            ]),
            // TAB 2: Sender Info
            forms_field('text', 'email_sender_name', __('Tên người gửi'), [
                'tab' => 'sender', 'placeholder' => 'Company Name', 'width_value' => 50,
            ]),
            forms_field('text', 'email_sender_address', __('Email người gửi'), [
                'description' => __('From email'),
                'tab' => 'sender', 'placeholder' => 'noreply@example.com', 'width_value' => 50,
            ]),
            forms_field('text', 'email_reply_to', __('Email reply-to'), [
                'tab' => 'sender', 'placeholder' => 'reply@example.com', 'width_value' => 50,
            ]),
            forms_field('select', 'email_language', __('Ngôn ngữ email'), [
                'tab' => 'sender',
                'options' => [['value' => 'en', 'label' => 'English'], ['value' => 'vi', 'label' => 'Tiếng Việt']],
                'default_value' => 'en', 'width_value' => 50,
            ]),
            forms_field('textarea', 'email_signature', __('Chữ ký mặc định'), [
                'tab' => 'sender', 'rows' => 4, 'width_value' => 100,
            ]),
            // TAB 3: Test Email
            forms_field('textarea', 'email_test_note', __('Test Email'), [
                'description' => __('Tab này dùng để gửi email test. Dùng nút "Send Test" khi đã cấu hình SMTP. Không lưu.'),
                'tab' => 'test_email', 'rows' => 2, 'default_value' => __('Send test email from Settings after configuring SMTP.'), 'width_value' => 100,
            ]),
            // TAB 4: Template
            forms_field('text', 'template_key', __('Template key'), [
                'tab' => 'template', 'placeholder' => 'welcome_email', 'width_value' => 33,
            ]),
            forms_field('text', 'template_category', __('Template Category'), [
                'tab' => 'template', 'placeholder' => 'general', 'width_value' => 33,
            ]),
            forms_field('boolean', 'template_enabled', __('Enable'), [
                'tab' => 'template', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('text', 'template_label', __('Tên template'), [
                'tab' => 'template', 'placeholder' => __('Welcome Email'), 'width_value' => 100,
            ]),
            forms_field('text', 'template_subject', __('Subject'), [
                'tab' => 'template', 'placeholder' => 'Welcome, {name}', 'width_value' => 100,
            ]),
            forms_field('textarea', 'template_html', __('Nội dung HTML'), [
                'tab' => 'template', 'rows' => 6, 'width_value' => 100,
            ]),
            forms_field('textarea', 'template_plain', __('Nội dung text (fallback)'), [
                'tab' => 'template', 'rows' => 4, 'width_value' => 100,
            ]),
        ];

        return ['tabs' => $tabs, 'fields' => $fields];
    }
}
