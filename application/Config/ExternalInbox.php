<?php
/**
 * Hòm thư tạm qua domain bên thứ ba (MX không trỏ về server của bạn).
 *
 * API inboxes.com: https://inboxes.com/api/v3/inboxes/ — khóa đặt qua biến môi trường
 * INBOXES_COM_API_KEY (không commit khóa thật vào git).
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$inboxesKey = (string) (getenv('INBOXES_COM_API_KEY') ?: '');

return [
    'inboxes_com' => [
        'api_base' => 'https://inboxes.com/api/v3',
        'api_key' => $inboxesKey,
        /** Header gửi kèm mỗi request (theo tài liệu inboxes.com) */
        'api_key_header' => 'apikey',
    ],

    /**
     * Domain được phép proxy qua API (đuôi khớp chính xác hoặc subdomain).
     * `kind` = external — hiển thị nhãn “ngoài hệ thống” ở client.
     */
    'allowed_domains' => [
        [
            'domain' => 'getnada.com',
            'label' => 'GetNada (domain ngoài hệ thống — inboxes.com)',
            'kind' => 'external',
            'provider' => 'inboxes.com',
        ],
    ],

    /** Client nên poll tối thiểu (giây); API vẫn cho phép gọi thường xuyên hơn nhưng bị rate limit. */
    'client_poll_interval_seconds' => 8,

    /** Giới hạn gọi upstream / phút / IP (chống lạm dụng). */
    'rate_limit_per_ip_per_minute' => 90,
];
