<?php
/**
 * Schema type: SearchResultsPage (trang kết quả tìm kiếm)
 *
 * File trả về array. Nhận $context từ scope. Payload: query.
 *
 * @package System\Libraries\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl    = rtrim(base_url(), '/');
$searchUrl  = base_url('search');
$siteName   = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$query      = '';
if (isset($context->payload) && $context->payload) {
    $p = $context->payload;
    $rawQuery = is_array($p) ? ($p['query'] ?? '') : ($p->query ?? '');
    $query = is_string($rawQuery) ? schema_safe_string($rawQuery) : '';
}
$currentUrl = $searchUrl . ($query !== '' ? '?q=' . rawurlencode($query) : '');
$lang       = defined('APP_LANG') && APP_LANG === 'en' ? 'en-US' : 'vi-VN';
$nameSafe   = schema_safe_string($siteName);

return [
    '@type'           => 'SearchResultsPage',
    '@id'             => $currentUrl . '#searchresults',
    'url'             => $currentUrl,
    'name'            => $nameSafe . ($query !== '' ? ' - ' . $query : ' - ' . __('Search', defined('APP_LANG') ? APP_LANG : null)),
    'description'     => $query !== '' ? sprintf(__('Search results for: %s', defined('APP_LANG') ? APP_LANG : null), $query) : __('Search', defined('APP_LANG') ? APP_LANG : null),
    'isPartOf'        => ['@id' => $baseUrl . '/#website'],
    'about'           => ['@id' => $baseUrl . '/#organization'],
    'breadcrumb'      => ['@id' => $currentUrl . '#breadcrumb'],
    'inLanguage'      => $lang,
];
