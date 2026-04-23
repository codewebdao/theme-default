<?php
/**
 * Schema type: BreadcrumbList
 *
 * File trả về array. KHÔNG class, KHÔNG hook trong file.
 * Nhận $context từ scope. Có thể dùng $context->payload để nhận items (name, url).
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl    = rtrim(base_url(), '/');
$siteName   = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$nameSafe   = schema_safe_string($siteName);
$currentUrl = $baseUrl . '/';

$items = [
    ['name' => $nameSafe, 'url' => $baseUrl],
];

if (isset($context->payload) && $context->payload) {
    $p = $context->payload;
    if (is_object($p) && !empty($p->breadcrumb)) {
        $items = $p->breadcrumb;
    } elseif (is_array($p) && !empty($p['breadcrumb'])) {
        $items = $p['breadcrumb'];
    }
    $pageUrl = is_object($p) ? ($p->url ?? $p->link ?? null) : ($p['url'] ?? $p['link'] ?? null);
    if (is_string($pageUrl) && $pageUrl !== '') {
        $currentUrl = rtrim(schema_safe_string($pageUrl), '/');
    }
}

$list = [];
foreach ($items as $i => $item) {
    $rawName = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? '');
    $rawUrl  = is_array($item) ? ($item['url'] ?? null) : ($item->url ?? null);
    $name    = schema_safe_string($rawName);
    $url     = is_string($rawUrl) && $rawUrl !== '' ? schema_safe_string($rawUrl) : '';
    $list[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $name,
        'item'     => $url !== '' ? $url : null,
    ];
}

return [
    '@type'           => 'BreadcrumbList',
    '@id'             => $currentUrl . '#breadcrumb',
    'itemListElement' => $list,
];
