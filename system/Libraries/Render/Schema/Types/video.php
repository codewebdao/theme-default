<?php
/**
 * Schema type: VideoObject (video)
 *
 * File trả về array. Nhận $context từ scope. Dữ liệu từ $context->payload.
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

$rawName     = is_object($payload) ? ($payload->name ?? $payload->title ?? $payload->post_title ?? '') : ($payload['name'] ?? $payload['title'] ?? $payload['post_title'] ?? '');
$rawDesc     = is_object($payload) ? ($payload->description ?? $payload->excerpt ?? $payload->post_excerpt ?? '') : ($payload['description'] ?? $payload['excerpt'] ?? $payload['post_excerpt'] ?? '');
$url         = is_object($payload) ? ($payload->url ?? $payload->link ?? '') : ($payload['url'] ?? $payload['link'] ?? '');
$thumbnail   = is_object($payload) ? ($payload->thumbnail ?? $payload->image ?? $payload->embedUrl ?? '') : ($payload['thumbnail'] ?? $payload['image'] ?? $payload['embedUrl'] ?? '');
$uploadDate  = is_object($payload) ? ($payload->uploadDate ?? $payload->datePublished ?? $payload->created_at ?? $payload->post_date ?? '') : ($payload['uploadDate'] ?? $payload['datePublished'] ?? $payload['created_at'] ?? $payload['post_date'] ?? '');
$duration    = is_object($payload) ? ($payload->duration ?? $payload->length ?? '') : ($payload['duration'] ?? $payload['length'] ?? '');
$embedUrl    = is_object($payload) ? ($payload->embedUrl ?? $payload->embed_url ?? '') : ($payload['embedUrl'] ?? $payload['embed_url'] ?? '');

$name        = schema_safe_string($rawName);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';

$baseUrl = rtrim(base_url(), '/');

$schema = [
    '@type'       => 'VideoObject',
    'name'        => $name,
    'description' => $description,
    'url'         => $url,
    'publisher'   => ['@id' => $baseUrl . '/#organization'],
];

if ($thumbnail) {
    $thumbUrl = is_string($thumbnail) && (strpos($thumbnail, '://') !== false || strpos($thumbnail, '//') === 0)
        ? schema_safe_string($thumbnail)
        : (($resolved = _img_url($thumbnail, 'original')) ? schema_safe_string($resolved) : '');
    if ($thumbUrl === '' && is_array($thumbnail) && !empty($thumbnail['url'])) {
        $thumbUrl = schema_safe_string($thumbnail['url']);
    }
    if ($thumbUrl !== '') {
        $schema['thumbnailUrl'] = $thumbUrl;
    }
}
if ($uploadDate) {
    $schema['uploadDate'] = date('c', is_numeric($uploadDate) ? $uploadDate : strtotime($uploadDate));
}
if ($duration !== '' && $duration !== null) {
    $schema['duration'] = is_string($duration) ? schema_safe_string($duration) : $duration;
}
if ($embedUrl) {
    $embedSafe = is_string($embedUrl) ? schema_safe_string($embedUrl) : '';
    if ($embedSafe !== '') {
        $schema['embedUrl'] = $embedSafe;
    }
}

return $schema;
