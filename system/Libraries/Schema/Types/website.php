<?php
/**
 * Schema type: WebSite (Google Sitelinks Search Box)
 *
 * File trả về array. KHÔNG class, KHÔNG hook trong file.
 * Nhận $context từ scope (Builder truyền vào).
 *
 * @package System\Libraries\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl   = rtrim(base_url(), '/');
$siteName  = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$siteDesc  = option('site_desc', defined('APP_LANG') ? APP_LANG : null);
$searchUrl = base_url('search');
$lang      = defined('APP_LANG') && APP_LANG === 'en' ? 'en-US' : 'vi-VN';

$nameSafe   = schema_safe_string($siteName);
$descSafe   = schema_safe_string($siteDesc);
$altName    = option('site_brand', null) ?: $siteName;
$altNameSafe = schema_safe_string($altName);

return [
    '@type'         => 'WebSite',
    '@id'           => $baseUrl . '/#website',
    'url'           => $baseUrl,
    'name'          => $nameSafe,
    'description'   => $descSafe,
    'alternateName' => $altNameSafe,
    'publisher'     => ['@id' => $baseUrl . '/#organization'],
    'inLanguage'   => $lang,
    'potentialAction' => [
        [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => $searchUrl . '?q={search_term_string}',
            ],
            'query-input' => [
                '@type'        => 'PropertyValueSpecification',
                'valueRequired' => true,
                'valueName'    => 'search_term_string',
            ],
        ],
    ],
];
