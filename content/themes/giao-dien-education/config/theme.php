<?php

// auth_ui: optional 'v2' for common/auth-v2 UI (default v1 = common/auth). See AuthController::_getAuthUiVersion().

return [
    'path' => 'giao-dien-education',
    'version' => '1.0.0',
    'type' => 'web',
    'parent' => 'giao-dien-education',
    'auth_ui' => 'v1',
    'theme' => [
        'name' => 'Giao diện Education — Đào tạo & học liệu',
        'version' => '1.0.0',
        'description' => 'Giao diện web tập trung đào tạo: khóa học, tutorial, học liệu PHP, tài liệu và trải nghiệm học tập rõ ràng trên mọi thiết bị.',
        'author' => 'CMS Team',
        'author_url' => 'https://cmsfullform.net',
        'support_url' => 'https://cmsfullform.net/support',
        'documentation_url' => 'https://cmsfullform.net/docs',
        'status' => false,
        'downloads' => 0,
        'rating' => 0,
        'category' => 'Education, E-learning, Training, Documentation',
        'tags' => ['education', 'tutorial', 'academy', 'php-learning', 'responsive', 'documentation'],
        'screenshot' => 'assets/images/screenshot.png',
        'min_php_version' => '7.4',
        'min_cms_version' => '1.0.0',
        'features' => [
            'Trang tutorial & học liệu có sidebar điều hướng',
            'Lộ trình / academy (Laragon Academy, bài PHP)',
            'Blog, đánh giá CMS, tài liệu và hướng dẫn sử dụng',
            'Giao diện responsive, tối ưu đọc nội dung dài',
            'Kế thừa layout & tài nguyên từ giao-dien-web (parent)',
            'SEO và cấu trúc heading thân thiện nội dung giáo dục',
        ],
        'compatibility' => [
            'browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge'],
            'devices' => ['Desktop', 'Tablet', 'Mobile'],
        ],
        'changelog' => [
            '1.0.0' => [
                'date' => '2026-04-18',
                'changes' => [
                    'Tách theme Education với metadata đào tạo (tutorial, academy, tài liệu).',
                    'parent: giao-dien-web — fallback view/asset khi cần.',
                    'Chuẩn hóa path theme: giao-dien-education.',
                ],
            ],
        ],
    ],
];
