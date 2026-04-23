<?php
/**
 * Schema type: LocalBusiness (doanh nghiệp địa phương / địa điểm)
 *
 * File trả về array. Nhận $context từ scope. Payload hoặc options từ site.
 *
 * @package System\Libraries\Render\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl   = rtrim(base_url(), '/');
$siteName  = option('site_title', defined('APP_LANG') ? APP_LANG : null);
$siteDesc   = option('site_desc', defined('APP_LANG') ? APP_LANG : null);
$payload   = isset($context->payload) ? $context->payload : null;

$name = schema_safe_string($siteName);
$description = schema_safe_string($siteDesc);
$url = $baseUrl;
$image = '';
$telephone = option('site_phone', null);
$email = option('site_email', null);
$address = [];
$geo = null;
$openingHours = null;

if ($payload && (is_array($payload) || is_object($payload))) {
    $p = $payload;
    $rawName = is_object($p) ? ($p->name ?? $p->title ?? $name) : ($p['name'] ?? $p['title'] ?? $name);
    $rawDesc = is_object($p) ? ($p->description ?? $p->excerpt ?? $description) : ($p['description'] ?? $p['excerpt'] ?? $description);
    $name = schema_safe_string($rawName);
    $description = schema_safe_string($rawDesc);
    $url = is_object($p) ? ($p->url ?? $p->link ?? $url) : ($p['url'] ?? $p['link'] ?? $url);
    $url = is_string($url) ? schema_safe_string($url) : $baseUrl;
    $image = is_object($p) ? ($p->image ?? $p->thumbnail ?? $image) : ($p['image'] ?? $p['thumbnail'] ?? $image);
    $telephone = is_object($p) ? ($p->telephone ?? $p->phone ?? $telephone) : ($p['telephone'] ?? $p['phone'] ?? $telephone);
    $email = is_object($p) ? ($p->email ?? $email) : ($p['email'] ?? $email);
    $address = is_object($p) ? ($p->address ?? []) : ($p['address'] ?? []);
    $geo = is_object($p) ? ($p->geo ?? null) : ($p['geo'] ?? null);
    $openingHours = is_object($p) ? ($p->openingHours ?? $p->opening_hours ?? null) : ($p['openingHours'] ?? $p['opening_hours'] ?? null);
}
$telephone = is_string($telephone) ? schema_safe_string($telephone) : '';
$email = is_string($email) ? schema_safe_string($email) : '';
$addrStreet = is_array($address) ? ($address['streetAddress'] ?? $address['street_address'] ?? '') : '';
$addrCity = is_array($address) ? ($address['addressLocality'] ?? $address['city'] ?? '') : '';
$addrRegion = is_array($address) ? ($address['addressRegion'] ?? $address['region'] ?? '') : '';
$addrPostal = is_array($address) ? ($address['postalCode'] ?? $address['postal_code'] ?? '') : '';
$addrCountry = is_array($address) ? ($address['addressCountry'] ?? $address['country'] ?? 'VN') : 'VN';
$addrStreet = is_string($addrStreet) ? schema_safe_string($addrStreet) : '';
$addrCity = is_string($addrCity) ? schema_safe_string($addrCity) : '';
$addrRegion = is_string($addrRegion) ? schema_safe_string($addrRegion) : '';
$addrPostal = is_string($addrPostal) ? schema_safe_string($addrPostal) : '';
$addrCountry = is_string($addrCountry) ? schema_safe_string($addrCountry) : 'VN';

$schema = [
    '@type'       => 'LocalBusiness',
    '@id'         => $baseUrl . '/#localbusiness',
    'name'        => $name,
    'description' => $description,
    'url'         => $url,
    'telephone'   => $telephone !== '' ? $telephone : null,
    'email'       => $email !== '' ? $email : null,
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $addrStreet,
        'addressLocality' => $addrCity,
        'addressRegion'   => $addrRegion,
        'postalCode'      => $addrPostal,
        'addressCountry'  => $addrCountry !== '' ? $addrCountry : 'VN',
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
        $schema['image'] = ['@type' => 'ImageObject', 'url' => $imgUrl];
    }
}
if ($geo && (is_array($geo) || is_object($geo))) {
    $lat = is_array($geo) ? ($geo['latitude'] ?? $geo['lat'] ?? null) : ($geo->latitude ?? $geo->lat ?? null);
    $lng = is_array($geo) ? ($geo['longitude'] ?? $geo['lng'] ?? null) : ($geo->longitude ?? $geo->lng ?? null);
    if ($lat !== null && $lng !== null) {
        $schema['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $lat, 'longitude' => $lng];
    }
}
if (!empty($openingHours)) {
    $schema['openingHoursSpecification'] = is_array($openingHours) ? $openingHours : [['@type' => 'OpeningHoursSpecification', 'opens' => '00:00', 'closes' => '23:59']];
}

return $schema;
