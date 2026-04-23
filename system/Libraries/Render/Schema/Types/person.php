<?php
/**
 * Schema type: Person (tác giả / profile)
 *
 * File trả về array. Nhận $context từ scope. Payload: slug, name, url, image, description, …
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl   = rtrim(base_url(), '/');
$payload   = isset($context->payload) ? $context->payload : null;
if ($payload === null || $payload === []) {
    return [];
}

$rawName  = is_object($payload) ? ($payload->name ?? $payload->display_name ?? $payload->title ?? '') : ($payload['name'] ?? $payload['display_name'] ?? $payload['title'] ?? '');
$slug     = is_object($payload) ? ($payload->slug ?? $payload->nicename ?? '') : ($payload['slug'] ?? $payload['nicename'] ?? '');
$url      = is_object($payload) ? ($payload->url ?? $payload->link ?? '') : ($payload['url'] ?? $payload['link'] ?? '');
$image    = is_object($payload) ? ($payload->image ?? $payload->avatar ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['avatar'] ?? $payload['thumbnail'] ?? '');
$rawDesc  = is_object($payload) ? ($payload->description ?? $payload->bio ?? $payload->excerpt ?? '') : ($payload['description'] ?? $payload['bio'] ?? $payload['excerpt'] ?? '');

$name = schema_safe_string($rawName);
$slug = is_string($slug) ? schema_safe_string($slug) : '';
$url  = is_string($url) ? schema_safe_string($url) : '';
$desc = schema_safe_string($rawDesc);

if ($url === '' && $slug !== '') {
    $url = function_exists('link_author')
        ? rtrim(link_author($slug), '/')
        : rtrim(base_url('author/' . ltrim($slug, '/')), '/');
} elseif ($url === '') {
    $url = $baseUrl . '/';
}

$schema = [
    '@type'       => 'Person',
    '@id'         => $url . '#person',
    'name'        => $name,
    'url'         => $url,
    'description' => $desc,
];

if ($image) {
    $imgUrl = is_string($image) && (strpos($image, '://') !== false || strpos($image, '//') === 0)
        ? schema_safe_string($image)
        : (($resolved = _img_url($image, 'original')) ? schema_safe_string($resolved) : '');
    if ($imgUrl === '' && is_array($image) && !empty($image['url'])) {
        $imgUrl = schema_safe_string($image['url']);
    }
    if ($imgUrl !== '') {
        $schema['image'] = ['@type' => 'ImageObject', 'url' => $imgUrl];
    }
}

return $schema;
