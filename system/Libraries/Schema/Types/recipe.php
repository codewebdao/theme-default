<?php
/**
 * Schema type: Recipe (công thức nấu ăn)
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
$image       = is_object($payload) ? ($payload->image ?? $payload->thumbnail ?? '') : ($payload['image'] ?? $payload['thumbnail'] ?? '');
$url         = is_object($payload) ? ($payload->url ?? $payload->link ?? '') : ($payload['url'] ?? $payload['link'] ?? '');
$datePub     = is_object($payload) ? ($payload->datePublished ?? $payload->created_at ?? $payload->post_date ?? '') : ($payload['datePublished'] ?? $payload['created_at'] ?? $payload['post_date'] ?? '');
$prepTime    = is_object($payload) ? ($payload->prepTime ?? $payload->prep_time ?? '') : ($payload['prepTime'] ?? $payload['prep_time'] ?? '');
$cookTime    = is_object($payload) ? ($payload->cookTime ?? $payload->cook_time ?? '') : ($payload['cookTime'] ?? $payload['cook_time'] ?? '');
$recipeYield = is_object($payload) ? ($payload->recipeYield ?? $payload->yield ?? '') : ($payload['recipeYield'] ?? $payload['yield'] ?? '');
$recipeIngredient = is_object($payload) ? ($payload->recipeIngredient ?? $payload->ingredients ?? []) : ($payload['recipeIngredient'] ?? $payload['ingredients'] ?? []);
$recipeInstructions = is_object($payload) ? ($payload->recipeInstructions ?? $payload->instructions ?? []) : ($payload['recipeInstructions'] ?? $payload['instructions'] ?? []);

$name        = schema_safe_string($rawName);
$description = schema_safe_string($rawDesc);
$url         = is_string($url) ? schema_safe_string($url) : '';
$prepTime    = is_string($prepTime) ? schema_safe_string($prepTime) : $prepTime;
$cookTime    = is_string($cookTime) ? schema_safe_string($cookTime) : $cookTime;
$recipeYield = is_string($recipeYield) ? schema_safe_string($recipeYield) : $recipeYield;

$baseUrl = rtrim(base_url(), '/');

$schema = [
    '@type'       => 'Recipe',
    'name'        => $name,
    'description' => $description,
    'url'         => $url,
    'publisher'   => ['@id' => $baseUrl . '/#organization'],
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
if ($datePub) {
    $schema['datePublished'] = date('c', is_numeric($datePub) ? $datePub : strtotime($datePub));
}
if ($prepTime !== '' && $prepTime !== null) {
    $schema['prepTime'] = $prepTime;
}
if ($cookTime !== '' && $cookTime !== null) {
    $schema['cookTime'] = $cookTime;
}
if ($recipeYield !== '' && $recipeYield !== null) {
    $schema['recipeYield'] = $recipeYield;
}
if (!empty($recipeIngredient) && is_array($recipeIngredient)) {
    $schema['recipeIngredient'] = [];
    foreach ($recipeIngredient as $ing) {
        $text = is_string($ing) ? schema_safe_string($ing) : (is_array($ing) ? schema_safe_string($ing['name'] ?? $ing['text'] ?? '') : '');
        if ($text !== '') {
            $schema['recipeIngredient'][] = $text;
        }
    }
}
if (!empty($recipeInstructions)) {
    if (is_array($recipeInstructions)) {
        $steps = [];
        foreach ($recipeInstructions as $i => $step) {
            if (is_string($step)) {
                $text = schema_safe_string($step);
                $steps[] = ['@type' => 'HowToStep', 'text' => $text];
            } elseif (is_array($step)) {
                $stepText = $step['text'] ?? $step['name'] ?? '';
                $text = schema_safe_string($stepText);
                $steps[] = array_merge(['@type' => 'HowToStep', 'text' => $text], $step);
            }
        }
        $schema['recipeInstructions'] = $steps;
    } else {
        $text = schema_safe_string($recipeInstructions);
        $schema['recipeInstructions'] = [['@type' => 'HowToStep', 'text' => $text]];
    }
}

return $schema;
