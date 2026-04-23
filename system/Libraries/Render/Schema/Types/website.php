<?php
/**
 * Schema type: WebSite (Google Sitelinks Search Box)
 *
 * File trả về array. KHÔNG class, KHÔNG hook trong file.
 * Nhận $context từ scope (Builder truyền vào).
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl   = rtrim(base_url(), '/');
$siteName  = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$siteDesc  = option('site_desc', defined('APP_LANG') ? APP_LANG : null);
$lang = str_replace('_', '-', defined('APP_LOCALE') ? APP_LOCALE : 'en_US');
$langCode = defined('APP_LANG') ? APP_LANG : 'en';

// Rank Math / tùy chỉnh: option schema_search_url chứa {search_term_string}, ví dụ https://site.com/search?q={search_term_string}
$searchTpl = option('schema_search_url', $langCode);
if (is_string($searchTpl) && $searchTpl !== '' && strpos($searchTpl, '{search_term_string}') !== false) {
    $urlTemplate = $searchTpl;
} else {
    $urlTemplate = function_exists('link_search')
        ? (rtrim(link_search(''), '/') . '?q={search_term_string}')
        : (rtrim(base_url('search'), '/') . '?q={search_term_string}');
}

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
                'urlTemplate' => $urlTemplate,
            ],
            'query-input' => [
                '@type'        => 'PropertyValueSpecification',
                'valueRequired' => true,
                'valueName'    => 'search_term_string',
            ],
        ],
    ],
];
