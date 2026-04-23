<?php
/**
 * Schema type: Review (đánh giá – thường dùng lồng trong Product/Article)
 *
 * File trả về array. Nhận $context từ scope. Payload: itemReviewed, author, reviewRating, reviewBody.
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

$itemReviewed = is_object($payload) ? ($payload->itemReviewed ?? null) : ($payload['itemReviewed'] ?? null);
$rawAuthor    = is_object($payload) ? ($payload->author ?? $payload->author_name ?? '') : ($payload['author'] ?? $payload['author_name'] ?? '');
$reviewRating = is_object($payload) ? ($payload->reviewRating ?? $payload->rating ?? null) : ($payload['reviewRating'] ?? $payload['rating'] ?? null);
$rawBody      = is_object($payload) ? ($payload->reviewBody ?? $payload->review_body ?? $payload->content ?? '') : ($payload['reviewBody'] ?? $payload['review_body'] ?? $payload['content'] ?? '');
$datePublished = is_object($payload) ? ($payload->datePublished ?? $payload->created_at ?? '') : ($payload['datePublished'] ?? $payload['created_at'] ?? '');

$author    = schema_safe_string($rawAuthor);
$reviewBody = schema_safe_string($rawBody);

$schema = [
    '@type'       => 'Review',
    'reviewBody'  => $reviewBody,
    'author'     => ['@type' => 'Person', 'name' => $author],
];

if ($itemReviewed) {
    $schema['itemReviewed'] = is_array($itemReviewed) ? array_merge(['@type' => 'Thing'], $itemReviewed) : $itemReviewed;
}
if ($reviewRating !== null) {
    if (is_numeric($reviewRating)) {
        $schema['reviewRating'] = [
            '@type'       => 'Rating',
            'ratingValue' => (float) $reviewRating,
            'bestRating'  => 5,
            'worstRating' => 1,
        ];
    } elseif (is_array($reviewRating)) {
        $schema['reviewRating'] = array_merge(['@type' => 'Rating'], $reviewRating);
    } else {
        $schema['reviewRating'] = $reviewRating;
    }
}
if ($datePublished) {
    $schema['datePublished'] = date('c', is_numeric($datePublished) ? $datePublished : strtotime($datePublished));
}

return $schema;
