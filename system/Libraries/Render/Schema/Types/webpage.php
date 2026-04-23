<?php
/**
 * Schema type: WebPage (generic – dùng cho mọi trang khi cần)
 *
 * File trả về array. Nhận $context từ scope.
 * Payload có thể chứa: url, name, description, datePublished, dateModified.
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl    = rtrim(base_url(), '/');
$siteName   = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$siteDesc   = option('site_desc', defined('APP_LANG') ? APP_LANG : null);
$currentUrl = $baseUrl . '/';
$name       = schema_safe_string($siteName);
$description = schema_safe_string($siteDesc);
$datePub    = date('c');
$dateMod    = date('c');

if (isset($context->payload) && $context->payload) {
    $p = $context->payload;
    $rawUrl = is_object($p) ? ($p->url ?? $p->link ?? '') : ($p['url'] ?? $p['link'] ?? $currentUrl);
    $currentUrl = is_string($rawUrl) && $rawUrl !== '' ? schema_safe_string($rawUrl) : $currentUrl;
    if ($currentUrl === '') {
        $currentUrl = $baseUrl . '/';
    }
    $rawName = is_object($p) ? ($p->title ?? $p->post_title ?? $p->name ?? $name) : ($p['title'] ?? $p['post_title'] ?? $p['name'] ?? $name);
    $rawDesc = is_object($p) ? ($p->description ?? $p->excerpt ?? $p->post_excerpt ?? $description) : ($p['description'] ?? $p['excerpt'] ?? $p['post_excerpt'] ?? $description);
    $name = schema_safe_string($rawName);
    $description = schema_safe_string($rawDesc);
    $datePub = is_object($p) ? ($p->datePublished ?? $p->created_at ?? $p->post_date ?? $datePub) : ($p['datePublished'] ?? $p['created_at'] ?? $p['post_date'] ?? $datePub);
    $dateMod = is_object($p) ? ($p->dateModified ?? $p->updated_at ?? $p->post_modified ?? $dateMod) : ($p['dateModified'] ?? $p['updated_at'] ?? $p['post_modified'] ?? $dateMod);
    if ($datePub && !preg_match('/^\d{4}-\d{2}-\d{2}/', $datePub)) {
        $datePub = date('c', is_numeric($datePub) ? $datePub : strtotime($datePub));
    }
    if ($dateMod && !preg_match('/^\d{4}-\d{2}-\d{2}/', $dateMod)) {
        $dateMod = date('c', is_numeric($dateMod) ? $dateMod : strtotime($dateMod));
    }
}

$lang = str_replace('_', '-', defined('APP_LOCALE') ? APP_LOCALE : 'en_US'); //Output example: en-US

return [
    '@type'             => 'WebPage',
    '@id'               => $currentUrl . '#webpage',
    'url'               => $currentUrl,
    'name'              => $name,
    'description'       => $description,
    'isPartOf'          => ['@id' => $baseUrl . '/#website'],
    'about'             => ['@id' => $baseUrl . '/#organization'],
    'datePublished'     => $datePub,
    'dateModified'      => $dateMod,
    'breadcrumb'        => ['@id' => $currentUrl . '#breadcrumb'],
    'inLanguage'        => $lang,
    'potentialAction'   => [
        ['@type' => 'ReadAction', 'target' => [$currentUrl]],
    ],
];
