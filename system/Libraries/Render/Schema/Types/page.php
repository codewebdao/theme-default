<?php
/**
 * Schema type: WebPage (trang tĩnh CMS – page, page-{slug})
 *
 * File trả về array. Nhận $context từ scope. Dữ liệu từ $context->payload (page/post object).
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$payload = isset($context->payload) ? $context->payload : null;
if (!$payload) {
    return [];
}

$rawTitle   = is_object($payload) ? ($payload->title ?? $payload->post_title ?? '') : ($payload['title'] ?? $payload['post_title'] ?? '');
$rawDesc    = is_object($payload) ? ($payload->description ?? $payload->excerpt ?? $payload->post_excerpt ?? '') : ($payload['description'] ?? $payload['excerpt'] ?? $payload['post_excerpt'] ?? '');
$url        = is_object($payload) ? ($payload->url ?? $payload->link ?? '') : ($payload['url'] ?? $payload['link'] ?? '');
$datePub    = is_object($payload) ? ($payload->datePublished ?? $payload->created_at ?? $payload->post_date ?? '') : ($payload['datePublished'] ?? $payload['created_at'] ?? $payload['post_date'] ?? '');
$dateMod    = is_object($payload) ? ($payload->dateModified ?? $payload->updated_at ?? $payload->post_modified ?? '') : ($payload['dateModified'] ?? $payload['updated_at'] ?? $payload['post_modified'] ?? '');
$image      = is_object($payload) ? ($payload->image ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['thumbnail'] ?? '');

$title       = schema_safe_string($rawTitle);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';

$baseUrl = rtrim(base_url(), '/');
if (!$url) {
    $slug = is_object($payload) ? ($payload->slug ?? '') : ($payload['slug'] ?? '');
    $slug = is_string($slug) ? trim($slug, '/') : '';
    if ($slug !== '' && function_exists('link_page')) {
        $url = rtrim(link_page($slug), '/');
    } else {
        $url = $slug !== '' ? rtrim($baseUrl . '/' . $slug, '/') : $baseUrl;
    }
}
if ($datePub && !preg_match('/^\d{4}-\d{2}-\d{2}/', $datePub)) {
    $datePub = date('c', is_numeric($datePub) ? $datePub : strtotime($datePub));
} elseif (!$datePub) {
    $datePub = date('c');
}
if ($dateMod && !preg_match('/^\d{4}-\d{2}-\d{2}/', $dateMod)) {
    $dateMod = date('c', is_numeric($dateMod) ? $dateMod : strtotime($dateMod));
} elseif (!$dateMod) {
    $dateMod = $datePub;
}

$lang = str_replace('_', '-', defined('APP_LOCALE') ? APP_LOCALE : 'en_US');

$schema = [
    '@type'           => 'WebPage',
    '@id'             => $url . '#webpage',
    'url'             => $url,
    'name'            => $title,
    'description'     => $description,
    'isPartOf'        => ['@id' => $baseUrl . '/#website'],
    'about'           => ['@id' => $baseUrl . '/#organization'],
    'datePublished'   => $datePub,
    'dateModified'    => $dateMod,
    'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
    'inLanguage'      => $lang,
    'potentialAction' => [
        ['@type' => 'ReadAction', 'target' => [$url]],
    ],
];

if ($image) {
    $imgUrl = is_string($image) && (strpos($image, '://') !== false || strpos($image, '//') === 0)
        ? schema_safe_string($image)
        : (($resolved = _img_url($image, 'original')) ? schema_safe_string($resolved) : '');
    if ($imgUrl === '' && is_array($image) && !empty($image['url'])) {
        $imgUrl = schema_safe_string($image['url']);
    }
    if ($imgUrl !== '') {
        $schema['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $imgUrl];
    }
}

return $schema;
