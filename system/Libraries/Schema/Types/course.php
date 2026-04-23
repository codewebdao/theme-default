<?php
/**
 * Schema type: Course (khóa học)
 *
 * File trả về array. Nhận $context từ scope. Dữ liệu từ $context->payload.
 *
 * @package System\Libraries\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$payload = isset($context->payload) ? $context->payload : null;
if (!$payload) {
    return [];
}

$rawName     = is_object($payload) ? ($payload->name ?? $payload->title ?? $payload->post_title ?? '') : ($payload['name'] ?? $payload['title'] ?? $payload['post_title'] ?? '');
$rawDesc     = is_object($payload) ? ($payload->description ?? $payload->excerpt ?? $payload->post_excerpt ?? '') : ($payload['description'] ?? $payload['excerpt'] ?? $payload['post_excerpt'] ?? '');
$url         = is_object($payload) ? ($payload->url ?? $payload->link ?? '') : ($payload['url'] ?? $payload['link'] ?? '');
$image       = is_object($payload) ? ($payload->image ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['thumbnail'] ?? '');
$provider    = is_object($payload) ? ($payload->provider ?? $payload->author_name ?? '') : ($payload['provider'] ?? $payload['author_name'] ?? '');
$datePub     = is_object($payload) ? ($payload->datePublished ?? $payload->created_at ?? $payload->post_date ?? '') : ($payload['datePublished'] ?? $payload['created_at'] ?? $payload['post_date'] ?? '');
$hasCourseInstance = is_object($payload) ? ($payload->hasCourseInstance ?? []) : ($payload['hasCourseInstance'] ?? []);

$name        = schema_safe_string($rawName);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';
$provider    = is_string($provider) ? schema_safe_string($provider) : '';

$baseUrl = rtrim(base_url(), '/');

$schema = [
    '@type'        => 'Course',
    'name'         => $name,
    'description'  => $description,
    'url'          => $url,
    'provider'     => ['@type' => 'Organization', '@id' => $baseUrl . '/#organization'],
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
if ($provider !== '') {
    $schema['provider'] = ['@type' => 'Organization', 'name' => $provider];
}
if ($datePub) {
    $schema['datePublished'] = date('c', is_numeric($datePub) ? $datePub : strtotime($datePub));
}
if (!empty($hasCourseInstance) && is_array($hasCourseInstance)) {
    $schema['hasCourseInstance'] = [];
    foreach ($hasCourseInstance as $inst) {
        $schema['hasCourseInstance'][] = is_array($inst) ? array_merge(['@type' => 'CourseInstance'], $inst) : $inst;
    }
}

return $schema;
