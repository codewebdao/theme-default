<?php
/**
 * Schema type: CollectionPage / ItemList (trang archive – danh sách bài/post type)
 *
 * File trả về array. Nhận $context từ scope. Payload: post_type, title, items (optional).
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl   = rtrim(base_url(), '/');
$siteName  = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$payload   = isset($context->payload) ? $context->payload : null;
$postType  = is_array($payload) ? ($payload['post_type'] ?? '') : (is_object($payload) ? ($payload->post_type ?? '') : '');
$postType  = is_string($postType) ? schema_safe_string($postType) : '';
$rawTitle  = is_array($payload) ? ($payload['title'] ?? $siteName) : (is_object($payload) ? ($payload->title ?? $siteName) : $siteName);
$title     = schema_safe_string($rawTitle);
$currentUrl = $baseUrl;
if ($postType !== '') {
    $currentUrl = rtrim((string) base_url($postType), '/');
}

$lang = str_replace('_', '-', defined('APP_LOCALE') ? APP_LOCALE : 'en_US');

$schema = [
    '@type'        => 'CollectionPage',
    '@id'          => $currentUrl . '#collectionpage',
    'url'          => $currentUrl,
    'name'         => $title,
    'description'  => $title,
    'isPartOf'     => ['@id' => $baseUrl . '/#website'],
    'about'        => ['@id' => $baseUrl . '/#organization'],
    'breadcrumb'   => ['@id' => $currentUrl . '#breadcrumb'],
    'inLanguage'  => $lang,
];

$items = is_array($payload) ? ($payload['items'] ?? $payload['posts'] ?? []) : (is_object($payload) ? ($payload->items ?? $payload->posts ?? []) : []);
if (!empty($items)) {
    $listElements = [];
    foreach (array_slice($items, 0, 10) as $i => $item) {
        $rawName = is_array($item) ? ($item['title'] ?? $item['post_title'] ?? '') : ($item->title ?? $item->post_title ?? '');
        $rawUrl  = is_array($item) ? ($item['url'] ?? $item['link'] ?? '') : ($item->url ?? $item->link ?? '');
        $name    = schema_safe_string($rawName);
        $url     = is_string($rawUrl) ? schema_safe_string($rawUrl) : '';
        $listElements[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $name,
            'item' => $url !== '' ? $url : null,
        ];
    }
    $schema['mainEntity'] = [
        '@type'           => 'ItemList',
        'numberOfItems'   => count($items),
        'itemListElement' => $listElements,
    ];
}

return $schema;
