<?php
/**
 * Schema type: Organization (doanh nghiệp)
 *
 * File trả về array. KHÔNG class, KHÔNG hook trong file.
 * Nhận $context từ scope.
 * Logo: dùng _img_url() (Images_helper) khi site_logo là image data (array/JSON).
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl  = rtrim(base_url(), '/');
$siteName = option('site_title', defined('APP_LANG') ? APP_LANG : null) ?: '';
$siteDesc = option('site_desc', defined('APP_LANG') ? APP_LANG : null) ?: '';
$logoRaw  = option('site_logo', null);

// Logo URL: dùng _img_url() khi là image data (array/object/JSON có path), URL khi đã là string URL
$logoUrl = $baseUrl . '/assets/images/logo.png';
if ($logoRaw !== null && $logoRaw !== '') {
    if (is_string($logoRaw) && (strpos($logoRaw, '://') !== false || strpos($logoRaw, '//') === 0)) {
        $logoUrl = schema_safe_string($logoRaw);
    } else {
        $resolved = _img_url($logoRaw, 'original');
        if ($resolved !== null && $resolved !== '') {
            $logoUrl = schema_safe_string($resolved);
        }
    }
}

$nameSafe   = schema_safe_string($siteName);
$descSafe   = schema_safe_string($siteDesc);
$emailRaw   = option('site_email', null);
$phoneRaw   = option('site_phone', null);
$emailSafe  = is_string($emailRaw) ? schema_safe_string($emailRaw) : '';
$phoneSafe  = is_string($phoneRaw) ? schema_safe_string($phoneRaw) : '';
$langCode = str_replace('_', '-', defined('APP_LOCALE') ? APP_LOCALE : 'en_US');

$schema = [
    '@type'         => 'Organization',
    '@id'           => $baseUrl . '/#organization',
    'name'          => $nameSafe,
    'alternateName' => $nameSafe,
    'url'           => $baseUrl,
    'email'         => $emailSafe !== '' ? $emailSafe : null,
    'telephone'     => $phoneSafe !== '' ? $phoneSafe : null,
    'description'   => $descSafe,
    'logo'          => [
        '@type'       => 'ImageObject',
        '@id'         => $baseUrl . '/#/schema/logo/image/',
        'inLanguage'  => $langCode,
        'url'         => $logoUrl,
        'contentUrl'  => $logoUrl,
        'width'       => 600,
        'height'      => 60,
        'caption'     => $nameSafe,
    ],
    'image' => ['@id' => $baseUrl . '/#/schema/logo/image/'],
];

$social = option('social', null);
if (is_array($social)) {
    $sameAs = [];
    foreach (['facebook', 'twitter', 'youtube', 'instagram', 'linkedin', 'pinterest'] as $platform) {
        if (!empty($social[$platform])) {
            $url = is_string($social[$platform]) ? schema_safe_string($social[$platform]) : '';
            if ($url !== '') {
                $sameAs[] = $url;
            }
        }
    }
    if (!empty($sameAs)) {
        $schema['sameAs'] = $sameAs;
    }
}

if (!empty($phoneSafe)) {
    $schema['contactPoint'] = [
        [
            '@type'       => 'ContactPoint',
            'telephone'   => $phoneSafe,
            'contactType' => 'customer support',
        ],
    ];
}

return $schema;
