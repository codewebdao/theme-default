<?php
/**
 * Theme admin — enqueue asset qua View / AssetManager.
 * BackendController nạp file này trước mỗi request admin.
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;

// JS lõi admin (thay đăng ký cũ trong HeadBlock): tailwind → alpine collapse → alpine → lucide
// Thứ tự giữ qua deps; area Backend → AssetManager key admin
View::addJs('admin-main', 'js/backend.js', [], null, false, false, false, false);
View::addJs('admin-tailwind', 'js/tailwindcss.min.js', [], null, false, false, false, false);
View::addJs('admin-alpine-collapse', 'js/alpinejs.collapse.js', ['admin-tailwind'], null, true, false, false, false);
View::addJs('admin-alpine', 'js/alpinejs.3.15.0.min.js', ['admin-alpine-collapse'], null, true, false, false, false);
View::addJs('admin-lucide', 'js/lucide.0.544.0.js', ['admin-alpine'], null, true, false, false, false);
