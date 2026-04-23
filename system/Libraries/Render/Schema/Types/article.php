<?php
/**
 * Schema type: Article (BlogPosting)
 *
 * File trả về array. KHÔNG class, KHÔNG hook trong file.
 * Nhận $context từ scope. Dữ liệu từ $context->payload (post object).
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
$image      = is_object($payload) ? ($payload->image ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['thumbnail'] ?? '');
$url        = is_object($payload) ? ($payload->url ?? '') : ($payload['url'] ?? '');
$datePub    = is_object($payload) ? ($payload->datePublished ?? $payload->created_at ?? $payload->post_date ?? '') : ($payload['datePublished'] ?? $payload['created_at'] ?? $payload['post_date'] ?? '');
$dateMod    = is_object($payload) ? ($payload->dateModified ?? $payload->updated_at ?? $payload->post_modified ?? '') : ($payload['dateModified'] ?? $payload['updated_at'] ?? $payload['post_modified'] ?? '');
$authorName = is_object($payload) ? ($payload->author_name ?? $payload->author ?? '') : ($payload['author_name'] ?? $payload['author'] ?? '');

$title       = schema_safe_string($rawTitle);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';

$baseUrl = rtrim(base_url(), '/');

// Google: Article/BlogPosting cần headline, image, datePublished, dateModified, author, publisher
$schema = [
    '@type'            => 'BlogPosting',
    'headline'         => $title,
    'description'      => $description,
    'url'              => $url,
    'datePublished'    => $datePub ? date('c', is_numeric($datePub) ? $datePub : strtotime($datePub)) : date('c'),
    'dateModified'     => $dateMod ? date('c', is_numeric($dateMod) ? $dateMod : strtotime($dateMod)) : date('c'),
    'publisher'        => ['@id' => $baseUrl . '/#organization'],
    'mainEntityOfPage' => ['@id' => $url ? $url . '#webpage' : $baseUrl . '/#webpage'],
];

if ($image) {
    $imgUrl = is_string($image) && (strpos($image, '://') !== false || strpos($image, '//') === 0)
        ? schema_safe_string($image)
        : (($resolved = _img_url($image, 'original')) ? schema_safe_string($resolved) : '');
    if ($imgUrl !== '') {
        $schema['image'] = ['@type' => 'ImageObject', 'url' => $imgUrl];
    } elseif (is_array($image) && !empty($image['url'])) {
        $schema['image'] = ['@type' => 'ImageObject', 'url' => schema_safe_string($image['url'])];
    }
}
$authorSafe = schema_safe_string($authorName);
if ($authorSafe !== '') {
    $schema['author'] = [
        '@type' => 'Person',
        'name'  => $authorSafe,
    ];
}

return $schema;
