<?php
/**
 * Schema type: Product
 *
 * File trả về array. KHÔNG class, KHÔNG hook trong file.
 * Nhận $context từ scope. Dữ liệu từ $context->payload (product object).
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

$rawName     = is_object($payload) ? ($payload->name ?? $payload->title ?? '') : ($payload['name'] ?? $payload['title'] ?? '');
$rawDesc     = is_object($payload) ? ($payload->description ?? $payload->excerpt ?? '') : ($payload['description'] ?? $payload['excerpt'] ?? '');
$image       = is_object($payload) ? ($payload->image ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['thumbnail'] ?? '');
$url         = is_object($payload) ? ($payload->url ?? '') : ($payload['url'] ?? '');
$sku         = is_object($payload) ? ($payload->sku ?? '') : ($payload['sku'] ?? '');
$price       = is_object($payload) ? ($payload->price ?? 0) : ($payload['price'] ?? 0);
$currency    = is_object($payload) ? ($payload->currency ?? 'VND') : ($payload['currency'] ?? 'VND');
$availability = is_object($payload) ? ($payload->availability ?? 'https://schema.org/InStock') : ($payload['availability'] ?? 'https://schema.org/InStock');

$name        = schema_safe_string($rawName);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';
$sku         = is_string($sku) ? schema_safe_string($sku) : '';

$baseUrl = rtrim(base_url(), '/');

// Google Product: name, image (recommended)
$schema = [
    '@type'       => 'Product',
    'name'        => $name,
    'description' => $description,
    'url'         => $url,
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
if ($sku !== '') {
    $schema['sku'] = $sku;
}
if ($price !== '' && $price !== null) {
    $schema['offers'] = [
        '@type'         => 'Offer',
        'price'         => $price,
        'priceCurrency' => $currency,
        'availability'  => $availability,
        'url'           => $url,
    ];
}

return $schema;
