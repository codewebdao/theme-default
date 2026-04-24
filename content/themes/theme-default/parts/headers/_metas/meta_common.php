<?php
/**
 * Helpers dùng chung cho SEO meta
 * Không tự chạy; được nạp bởi meta_bootstrap.php.
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

if (!function_exists('cmsfullform_meta_lang')) {
    function cmsfullform_meta_lang(): string
    {
        return defined('APP_LANG') ? (string) APP_LANG : '';
    }
}

if (!function_exists('cmsfullform_meta_author_label')) {
    function cmsfullform_meta_author_label(): string
    {
        $lang = cmsfullform_meta_lang();

        return trim((string) (option('site_title', $lang) ?: option('site_brand', $lang) ?: ''));
    }
}

if (!defined('CMSFULLFORM_META_PRIMARY_HEX')) {
    /** Khớp :root --home-primary trong Frontend/assets/css/index.css (màu primary giao diện). */
    define('CMSFULLFORM_META_PRIMARY_HEX', '#0d9488');
}

if (!function_exists('cmsfullform_meta_theme_color')) {
    function cmsfullform_meta_theme_color(): string
    {
        $lang = cmsfullform_meta_lang();
        foreach (['theme_color', 'site_theme_color'] as $key) {
            $c = option($key, $lang);
            $c = is_string($c) ? trim($c) : '';
            if ($c !== '') {
                return $c;
            }
        }

        return CMSFULLFORM_META_PRIMARY_HEX;
    }
}

if (!function_exists('cmsfullform_meta_image_url_from_option')) {
    /**
     * URL ảnh public từ option lưu JSON upload (site_logo, favicon, …).
     */
    function cmsfullform_meta_image_url_from_option($raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        if (!function_exists('_img_url')) {
            return '';
        }
        if (is_string($raw) && (strpos($raw, '://') !== false || strpos($raw, '//') === 0)) {
            return $raw;
        }

        $u = _img_url($raw, 'original');

        return is_string($u) ? $u : '';
    }
}

if (!function_exists('cmsfullform_meta_preload_links_for_layout')) {
    /**
     * @return list<array<string, string>> Danh sách thuộc tính thẻ link (rel, href, as, …)
     */
    function cmsfullform_meta_preload_links_for_layout(string $layout): array
    {
        if (!function_exists('theme_assets') || !function_exists('apply_filters')) {
            return [];
        }

        $featuresPreloadLink = [
            'rel'             => 'preload',
            'href'            => theme_assets('images/bannerFeatures.webp'),
            'as'              => 'image',
            'fetchpriority'   => 'high',
        ];
        if (function_exists('cmsfullform_theme_responsive_webp_data')) {
            $bannerData = cmsfullform_theme_responsive_webp_data('images/bannerFeatures.webp', [400, 560, 720, 900]);
            if ($bannerData !== [] && ($bannerData['preload_href'] ?? '') !== '') {
                $featuresPreloadLink['href'] = (string) $bannerData['preload_href'];
            }
            if ($bannerData !== [] && ($bannerData['srcset'] ?? '') !== '') {
                $featuresPreloadLink['imagesrcset'] = (string) $bannerData['srcset'];
                $featuresPreloadLink['imagesizes'] = '100vw';
            }
        }

        $reviewCmsPreloadLink = [
            'rel'             => 'preload',
            'href'            => theme_assets('images/banner_cms.webp'),
            'as'              => 'image',
            'fetchpriority'   => 'high',
        ];
        if (function_exists('cmsfullform_theme_responsive_webp_data')) {
            $reviewBannerData = cmsfullform_theme_responsive_webp_data('images/banner_cms.webp', [640, 960, 1200, 1536]);
            if ($reviewBannerData !== [] && ($reviewBannerData['preload_href'] ?? '') !== '') {
                $reviewCmsPreloadLink['href'] = (string) $reviewBannerData['preload_href'];
            }
            if ($reviewBannerData !== [] && ($reviewBannerData['srcset'] ?? '') !== '') {
                $reviewCmsPreloadLink['imagesrcset'] = (string) $reviewBannerData['srcset'];
                $reviewCmsPreloadLink['imagesizes'] = '100vw';
            }
        }

        $map = [
            'index' => [
                [
                    'rel'             => 'preload',
                    'href'            => theme_assets('images/topbar.webp'),
                    'as'              => 'image',
                    'fetchpriority'   => 'high',
                ],
            ],
            'front-page' => [
                [
                    'rel'             => 'preload',
                    'href'            => theme_assets('images/topbar.webp'),
                    'as'              => 'image',
                    'fetchpriority'   => 'high',
                ],
            ],
            'features' => [
                $featuresPreloadLink,
            ],
            'review-cms' => [
                $reviewCmsPreloadLink,
            ],
            'usage-guide' => [
                [
                    'rel'         => 'preload',
                    'href'        => theme_assets('fonts/SpaceGrotesk-Latin.woff2'),
                    'as'          => 'font',
                    'type'        => 'font/woff2',
                    'crossorigin' => 'anonymous',
                ],
                [
                    'rel'         => 'preload',
                    'href'        => theme_assets('fonts/PlusJakartaSans-Latin.woff2'),
                    'as'          => 'font',
                    'type'        => 'font/woff2',
                    'crossorigin' => 'anonymous',
                ],
            ],
            'tutorial' => [
                [
                    'rel'         => 'preload',
                    'href'        => theme_assets('fonts/SpaceGrotesk-Latin.woff2'),
                    'as'          => 'font',
                    'type'        => 'font/woff2',
                    'crossorigin' => 'anonymous',
                ],
                [
                    'rel'         => 'preload',
                    'href'        => theme_assets('fonts/PlusJakartaSans-Latin.woff2'),
                    'as'          => 'font',
                    'type'        => 'font/woff2',
                    'crossorigin' => 'anonymous',
                ],
            ],
        ];

        $links = $map[$layout] ?? [];

        return apply_filters('cmsfullform_meta_preload_links', $links, $layout);
    }
}

if (!function_exists('cmsfullform_meta_echo_preload_links')) {
    function cmsfullform_meta_echo_preload_links(array $links): void
    {
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $href = isset($link['href']) ? trim((string) $link['href']) : '';
            if ($href === '') {
                continue;
            }
            $rel = isset($link['rel']) ? trim((string) $link['rel']) : 'preload';
            if ($rel === '') {
                $rel = 'preload';
            }
            echo '    <link rel="' . htmlspecialchars($rel, ENT_QUOTES, 'UTF-8') . '" href="'
                . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"';
            foreach ($link as $attr => $value) {
                if ($attr === 'rel' || $attr === 'href') {
                    continue;
                }
                if ($value === '' || $value === null) {
                    continue;
                }
                echo ' ' . htmlspecialchars((string) $attr, ENT_QUOTES, 'UTF-8') . '="'
                    . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
            }
            echo " />\n";
        }
    }
}
