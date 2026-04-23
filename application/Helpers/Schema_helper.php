<?php
/**
 * Schema Helper – JSON-LD schema builders (không dùng Block)
 * Dùng với System\Libraries\Render\Head::addSchema()
 *
 * @package Application\Helpers
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * WebSite schema (Google Sitelinks Search Box)
 *
 * @param array $options name, description, alternateName, searchUrl
 * @return array
 */
if (!function_exists('schema_website')) {
    function schema_website($options = [])
    {
        $baseUrl = rtrim(base_url(), '/');
        $name = $options['name'] ?? option('site_title', APP_LANG);
        $description = $options['description'] ?? option('site_desc', APP_LANG);
        $searchUrl = $options['searchUrl'] ?? base_url('search');

        return [
            '@type' => 'WebSite',
            '@id' => $baseUrl . '/#website',
            'url' => $baseUrl,
            'name' => $name,
            'description' => $description,
            'alternateName' => $options['alternateName'] ?? option('site_brand') ?? $name,
            'publisher' => ['@id' => $baseUrl . '/#organization'],
            'inLanguage' => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
            'potentialAction' => [
                [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $searchUrl . '?q={search_term_string}',
                    ],
                    'query-input' => [
                        '@type' => 'PropertyValueSpecification',
                        'valueRequired' => true,
                        'valueName' => 'search_term_string',
                    ],
                ],
            ],
        ];
    }
}

/**
 * Organization schema (doanh nghiệp)
 *
 * @param array $options name, description, url, email, telephone, logo, social, foundingDate, legalName
 * @return array
 */
if (!function_exists('schema_organization')) {
    function schema_organization($options = [])
    {
        $baseUrl = rtrim(base_url(), '/');
        $name = $options['name'] ?? option('site_title', APP_LANG);
        $description = $options['description'] ?? option('site_desc', APP_LANG);
        $logoUrl = $options['logo'] ?? option('site_logo', APP_LANG);
        if (!is_string($logoUrl)) {
            $logoUrl = $baseUrl . '/assets/images/logo.png';
        }

        $schema = [
            '@type' => 'Organization',
            '@id' => $baseUrl . '/#organization',
            'name' => $name,
            'alternateName' => $options['alternateName'] ?? $name,
            'url' => $baseUrl,
            'email' => $options['email'] ?? option('site_email'),
            'telephone' => $options['telephone'] ?? option('site_phone'),
            'description' => $description,
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => $baseUrl . '/#/schema/logo/image/',
                'inLanguage' => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
                'url' => $logoUrl,
                'contentUrl' => $logoUrl,
                'width' => $options['logoWidth'] ?? 600,
                'height' => $options['logoHeight'] ?? 60,
                'caption' => $name,
            ],
            'image' => ['@id' => $baseUrl . '/#/schema/logo/image/'],
        ];

        $social = $options['social'] ?? option('social', APP_LANG);
        if (is_array($social)) {
            $sameAs = [];
            foreach (['facebook', 'twitter', 'youtube', 'instagram', 'linkedin', 'pinterest'] as $platform) {
                if (!empty($social[$platform])) {
                    $sameAs[] = $social[$platform];
                }
            }
            if (!empty($sameAs)) {
                $schema['sameAs'] = $sameAs;
            }
        }

        if (!empty($schema['telephone'])) {
            $schema['contactPoint'] = [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $schema['telephone'],
                    'contactType' => 'customer support',
                ],
            ];
        }

        if (!empty($options['foundingDate'])) {
            $schema['foundingDate'] = $options['foundingDate'];
        }
        if (!empty($options['legalName'])) {
            $schema['legalName'] = $options['legalName'];
        }
        if (!empty($options['vatID'])) {
            $schema['vatID'] = $options['vatID'];
            $schema['taxID'] = $options['vatID'];
        }

        return $schema;
    }
}

/**
 * WebPage schema
 *
 * @param array $options url, name, description, datePublished, dateModified
 * @return array
 */
if (!function_exists('schema_web_page')) {
    function schema_web_page($options = [])
    {
        $baseUrl = rtrim(base_url(), '/');
        $currentUrl = $options['url'] ?? base_url();
        $name = $options['name'] ?? option('site_title', APP_LANG);
        $description = $options['description'] ?? option('site_desc', APP_LANG);

        return [
            '@type' => 'WebPage',
            '@id' => $currentUrl . '#webpage',
            'url' => $currentUrl,
            'name' => $name,
            'description' => $description,
            'isPartOf' => ['@id' => $baseUrl . '/#website'],
            'about' => ['@id' => $baseUrl . '/#organization'],
            'datePublished' => $options['datePublished'] ?? date('c'),
            'dateModified' => $options['dateModified'] ?? date('c'),
            'breadcrumb' => ['@id' => $currentUrl . '#breadcrumb'],
            'inLanguage' => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
            'potentialAction' => [
                [
                    '@type' => 'ReadAction',
                    'target' => [$currentUrl],
                ],
            ],
        ];
    }
}

/**
 * BreadcrumbList schema
 *
 * @param array $items [['name' => '...', 'url' => '...'], ...]
 * @param array $options url (current page URL for @id)
 * @return array
 */
if (!function_exists('schema_breadcrumb_list')) {
    function schema_breadcrumb_list($items, $options = [])
    {
        $currentUrl = $options['url'] ?? base_url();
        $list = [];
        foreach ($items as $i => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $currentUrl . '#breadcrumb',
            'itemListElement' => $list,
        ];
    }
}

/**
 * Breadcrumb cho trang chủ (1 mục)
 *
 * @param array $options url, siteName
 * @return array
 */
if (!function_exists('schema_breadcrumb_home')) {
    function schema_breadcrumb_home($options = [])
    {
        $url = $options['url'] ?? base_url();
        $siteName = $options['siteName'] ?? option('site_title', APP_LANG);
        return schema_breadcrumb_list([['name' => $siteName, 'url' => $url]], ['url' => $url]);
    }
}

/**
 * ImageObject schema (logo / ảnh)
 *
 * @param string $url URL ảnh
 * @param array $options width, height, caption, id
 * @return array
 */
if (!function_exists('schema_image_object')) {
    function schema_image_object($url, $options = [])
    {
        $baseUrl = rtrim(base_url(), '/');
        return [
            '@type' => 'ImageObject',
            '@id' => $options['id'] ?? $baseUrl . '/#/schema/logo/image/',
            'url' => $url,
            'contentUrl' => $url,
            'width' => $options['width'] ?? 600,
            'height' => $options['height'] ?? 60,
            'caption' => $options['caption'] ?? option('site_title', APP_LANG),
            'inLanguage' => $options['inLanguage'] ?? (APP_LANG === 'en' ? 'en-US' : 'vi-VN'),
        ];
    }
}

/**
 * LocalBusiness schema (địa điểm / doanh nghiệp địa phương)
 *
 * @param array $options name, description, url, image, telephone, email, address, geo, openingHours
 * @return array
 */
if (!function_exists('schema_local_business')) {
    function schema_local_business($options = [])
    {
        $baseUrl = rtrim(base_url(), '/');
        $name = $options['name'] ?? option('site_title', APP_LANG);
        $description = $options['description'] ?? option('site_desc', APP_LANG);
        $address = $options['address'] ?? [];

        $schema = [
            '@type' => 'LocalBusiness',
            'name' => $name,
            'description' => $description,
            'url' => $options['url'] ?? $baseUrl,
            'image' => $options['image'] ?? '',
            'telephone' => $options['telephone'] ?? option('site_phone'),
            'email' => $options['email'] ?? option('site_email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['streetAddress'] ?? '',
                'addressLocality' => $address['addressLocality'] ?? '',
                'addressRegion' => $address['addressRegion'] ?? '',
                'postalCode' => $address['postalCode'] ?? '',
                'addressCountry' => $address['addressCountry'] ?? 'VN',
            ],
        ];

        if (!empty($address['latitude']) && !empty($address['longitude'])) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $address['latitude'],
                'longitude' => $address['longitude'],
            ];
        }

        if (!empty($options['openingHours'])) {
            $schema['openingHoursSpecification'] = $options['openingHours'];
        }

        return $schema;
    }
}
