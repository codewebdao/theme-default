<?php
/**
 * Schema type: Event (sự kiện)
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
$image       = is_object($payload) ? ($payload->image ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['thumbnail'] ?? '');
$startDate   = is_object($payload) ? ($payload->startDate ?? $payload->start_date ?? $payload->event_start ?? '') : ($payload['startDate'] ?? $payload['start_date'] ?? $payload['event_start'] ?? '');
$endDate     = is_object($payload) ? ($payload->endDate ?? $payload->end_date ?? $payload->event_end ?? '') : ($payload['endDate'] ?? $payload['end_date'] ?? $payload['event_end'] ?? '');
$location    = is_object($payload) ? ($payload->location ?? null) : ($payload['location'] ?? null);

$name        = schema_safe_string($rawName);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';

$baseUrl = rtrim(base_url(), '/');

$schema = [
    '@type'       => 'Event',
    'name'        => $name,
    'description' => $description,
    'url'         => $url,
];

if ($startDate) {
    $schema['startDate'] = date('c', is_numeric($startDate) ? $startDate : strtotime($startDate));
}
if ($endDate) {
    $schema['endDate'] = date('c', is_numeric($endDate) ? $endDate : strtotime($endDate));
}
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
if ($location) {
    if (is_string($location)) {
        $locName = schema_safe_string($location);
        $schema['location'] = ['@type' => 'Place', 'name' => $locName];
    } elseif (is_array($location)) {
        $schema['location'] = array_merge(['@type' => 'Place'], $location);
    } else {
        $schema['location'] = $location;
    }
}
$schema['organizer'] = ['@type' => 'Organization', '@id' => $baseUrl . '/#organization'];

return $schema;
