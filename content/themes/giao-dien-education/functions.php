<?php
/**
 * Theme functions (Frontend)
 * Được load bởi FrontendController::_loadFunctions() trước khi render.
 *
 * SEO: filter render.head.defaults — trang chủ + page / page-* (CMS + option seo_config JSON).
 * Meta keywords: render.head.defaults gán $GLOBALS['cmsfullform_head_keywords_pending'];
 *   render.head.meta.before (meta_bootstrap.php) đưa vào HTML — không dùng view_head_before (chạy trước khi Builder build).
 * Meta bổ sung (author, theme-color, preload, robots seo_follow, og image fallback): parts/headers/_metas/meta_bootstrap.php
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

// Cache-bust query `?ver=` cho asset theme: tăng khi deploy / đổi bundle CSS/JS quan trọng.
if (!defined('THEME_VER')) {
    define('THEME_VER', '1.0.0');
}

if (function_exists('add_filter')) {
    add_filter('render_data', static function ($data, $layout, $renderPath = null) {
        if (!is_array($data)) {
            $data = [];
        }
        $data['layout'] = $layout;

        return $data;
    }, 10, 3);
}

// Global assets (View::addJs/addCss — bucket web qua ThemeContext)
use System\Libraries\Render\View;

View::addCss('fonts', 'css/fonts.css', [], defined('THEME_VER') ? THEME_VER : null, 'all', false, false, false);
//View::addCss('home-index', 'css/home-index.css', [], null, 'all', false, false, false);
// Mobile menu (menu-mobi.php): header.php gọi window.jModal — phải có trước script inline trong body
View::addJs('jmodal', 'js/jmodal.js', [], null, false, false, false, false);
// Bỏ đăng ký các file không tồn tại (thêm file vào assets/js/ rồi bật lại nếu cần):
// View::addJs('lazysizes', 'js/lazysizes.min.js', [], null, false, false, false, false);
// View::addJs('main', 'js/main.js', [], null, false, false, false, false);
// View::addJs('blaze-slider', 'js/blaze-slider.min.js', [], null, false, false, false, false);


if (!function_exists('cmsfullform_youtube_parse_id')) {
    /**
     * Trích video ID từ URL YouTube (embed, watch, shorts, youtu.be). Rỗng nếu không hợp lệ.
     */
    function cmsfullform_youtube_parse_id(string $url): string
    {
        $url = trim($url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return '';
        }
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return '';
        }
        $isYoutube = (bool) preg_match('/(^|\.)youtube\.com$/i', $host) || $host === 'youtu.be';
        if (!$isYoutube) {
            return '';
        }
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }

        return '';
    }
}

if (!function_exists('cmsfullform_youtube_embed_url')) {
    /**
     * Chuẩn hóa link YouTube → URL embed cho iframe.
     */
    function cmsfullform_youtube_embed_url(string $url): string
    {
        $id = cmsfullform_youtube_parse_id($url);

        return $id !== '' ? 'https://www.youtube.com/embed/' . $id : '';
    }
}

if (!function_exists('cmsfullform_youtube_video_id')) {
    /**
     * Video ID cho thumbnail (i.ytimg.com).
     */
    function cmsfullform_youtube_video_id(string $url): string
    {
        return cmsfullform_youtube_parse_id($url);
    }
}
