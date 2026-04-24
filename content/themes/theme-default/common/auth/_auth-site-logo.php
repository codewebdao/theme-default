<?php
/**
 * URL logo: ưu tiên option site_logo (CMS), sau đó favicon / ảnh theme.
 */
if (!function_exists('cmsfullform_auth_site_logo_url')) {
    function cmsfullform_auth_site_logo_url(): string
    {
        $lang = defined('APP_LANG') ? APP_LANG : '';
        $logoOpt = option('site_logo', $lang);
        if ($logoOpt === null || $logoOpt === '' || $logoOpt === false) {
            $logoOpt = option('site_logo');
        }
        if ($logoOpt) {
            if (function_exists('cmsfullform_meta_image_url_from_option')) {
                $u = cmsfullform_meta_image_url_from_option($logoOpt);
                if ($u !== '') {
                    return $u;
                }
            }
            if (function_exists('_img_url')) {
                foreach (['medium', 'large', 'full', 'original'] as $sz) {
                    $webp = _img_url($logoOpt, $sz, true);
                    $u = is_string($webp) && $webp !== '' ? $webp : _img_url($logoOpt, $sz, false);
                    if (is_string($u) && $u !== '') {
                        return $u;
                    }
                }
            }
        }
        if (function_exists('cmsfullform_meta_image_url_from_option')) {
            $u = cmsfullform_meta_image_url_from_option(option('favicon'));
            if (is_string($u) && $u !== '') {
                return $u;
            }
        }
        if (function_exists('theme_assets')) {
            return theme_assets('images/logo/blog-logo.svg');
        }

        return '';
    }
}

if (!function_exists('cmsfullform_auth_site_brand_label')) {
    function cmsfullform_auth_site_brand_label(): string
    {
        $lang = defined('APP_LANG') ? APP_LANG : '';

        return trim((string) (option('site_brand', $lang) ?: option('site_brand') ?: ''));
    }
}
