<?php
/**
 * SEO meta bổ sung cho Frontend: preload theo layout, author, theme-color,
 * robots theo option seo_follow, đảm bảo og/twitter image khi thiếu.
 *
 * Keywords meta: render.head.meta.before đọc cmsfullform_head_keywords_pending (set trong render.head.defaults khi build).
 * Title / description / canonical: Head\Builder + filter render.head.defaults (functions.php). Schema: Schema::get() + Head::render.
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

require_once __DIR__ . '/meta_common.php';

if (!function_exists('add_filter') || !function_exists('add_action')) {
    return;
}

add_filter('render.head.defaults', function ($result, $layout, $payload) {
    if (!is_array($result)) {
        return $result;
    }

    $lang = cmsfullform_meta_lang();
    if (option('seo_follow', $lang) === 'nofollow') {
        $result['robots'] = 'noindex, nofollow';
    }

    $imgUrl = '';
    if (function_exists('_img_url')) {
        $imgUrl = cmsfullform_meta_image_url_from_option(option('site_logo', $lang));
    }

    if ($imgUrl !== '' && !empty($result['og']) && is_array($result['og'])) {
        if (empty($result['og']['image'])) {
            $result['og']['image'] = $imgUrl;
            $result['og']['image:secure_url'] = $imgUrl;
        }
    }
    if ($imgUrl !== '' && !empty($result['twitter']) && is_array($result['twitter'])) {
        if (empty($result['twitter']['image'])) {
            $result['twitter']['image'] = $imgUrl;
        }
    }

    return $result;
}, 25, 3);

add_action('view_head_before', function () {
    $ctx = \System\Libraries\Render\Head\Context::getCurrent();
    $layout = is_array($ctx) ? trim((string) ($ctx['layout'] ?? '')) : '';
    if ($layout !== '') {
        cmsfullform_meta_echo_preload_links(cmsfullform_meta_preload_links_for_layout($layout));
    }
}, 5);

add_filter('render.head.meta.before', function ($metaTags) {
    if (!is_array($metaTags)) {
        $metaTags = [];
    }
    // Keywords: pending được gán trong render.head.defaults khi Head::render() gọi Builder::build().
    // Không dùng view_head_before (chạy trước build nên pending luôn rỗng).
    if (!isset($metaTags['keywords'])) {
        $kw = isset($GLOBALS['cmsfullform_head_keywords_pending'])
            ? trim((string) $GLOBALS['cmsfullform_head_keywords_pending'])
            : '';
        if ($kw !== '') {
            $metaTags['keywords'] = ['name' => 'keywords', 'content' => $kw, 'type' => 'name'];
        }
    }
    $GLOBALS['cmsfullform_head_keywords_pending'] = '';

    if (!isset($metaTags['author'])) {
        $author = cmsfullform_meta_author_label();
        if ($author !== '') {
            $metaTags['author'] = ['name' => 'author', 'content' => $author, 'type' => 'name'];
        }
    }
    if (!isset($metaTags['theme-color'])) {
        $metaTags['theme-color'] = [
            'name'    => 'theme-color',
            'content' => cmsfullform_meta_theme_color(),
            'type'    => 'name',
        ];
    }

    return $metaTags;
}, 10, 1);
