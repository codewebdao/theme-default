<?php
/**
 * Theme functions (Frontend)
 * Được load bởi FrontendController::_loadFunctions() trước khi render.
 *
 * Head (title, meta, OG, canonical): build tự động từ layout + payload.
 * Chỉ dùng filter render.head.defaults khi cần can thiệp:
 *
 *   add_filter('render.head.defaults', function ($defaults, $layout, $payload) {
 *       $defaults['title_parts'][0] = 'Custom title';
 *       return $defaults;
 *   }, 10, 3);
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

// Global assets (dùng View::addJs/addCss – area Frontend)
use System\Libraries\Render\View;

View::addCss('home-index', 'css/home-index.css', [], null, 'all', 'Frontend', false, false, false);
View::addJs('lazysizes', 'js/lazysizes.min.js', [], null, false, false, 'Frontend', false, false);
View::addJs('main', 'js/main.js', [], null, false, false, 'Frontend', false, false);
View::addJs('blaze-slider', 'js/blaze-slider.min.js', [], null, false, false, 'Frontend', false, false);

// Ví dụ: đổi title trang chủ
// add_filter('render.head.defaults', function ($defaults, $layout, $payload) {
//     if ($layout === 'front-page' || $layout === 'index') {
//         $defaults['title_parts'] = [__('My Home')];
//     }
//     return $defaults;
// }, 10, 3);
