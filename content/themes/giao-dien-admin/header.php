<?php
/**
 * Admin shell — đầu trang: head + sidebar + topbar + mở <main> (giống flow giao-dien-web: header mở main).
 *
 * Biến do view_header($data) truyền: title, user_info, menuData, breadcrumb, layout (optional)
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;

$breadcrumbs = $breadcrumb ?? [
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home'),
        'active' => true,
    ],
];

$pageTitle = $title ?? __('Dashboard');

echo View::include('parts/layout/head', [
    'title' => $pageTitle,
    'user_info' => $user_info ?? null,
]);

echo View::include('parts/layout/sidebar', [
    'user_info' => $user_info ?? null,
    'menuData' => $menuData ?? [],
    'url' => $_SERVER['REQUEST_URI'] ?? '/',
]);

echo View::include('parts/layout/topbar', [
    'user_info' => $user_info ?? [],
    'breadcrumb' => $breadcrumbs,
]);
