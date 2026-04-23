<?php

namespace System\Libraries;

Render::block('Backend\Head', [
    'title' => $title ?? 'Home',
    'code' => '',
    'user_info' => $userInfo ?? null,
    'url' => $_SERVER['REQUEST_URI']
]);
?>
<!-- [ Sidebar ] start -->
<?php
Render::block('Backend\Sidebar', ['layout' => 'default', 'user_info' => $userInfo, 'url' => $_SERVER['REQUEST_URI']]);
?>
<!-- [ Header Topbar ] start -->
<?php
Render::block('Backend\Topbar', ['layout' => 'default', 'user_info' => $userInfo, 'breadcrumb' => $breadcrumb]);
?>

<!-- [ Header ] end -->