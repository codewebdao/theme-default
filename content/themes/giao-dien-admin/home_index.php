<?php
/**
 * Admin dashboard — view_header / view_footer (cùng pattern giao-dien-web).
 *
 * Controller truyền: title, breadcrumb, user_info, menuData, component (optional)
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use App\Libraries\Fastlang as Flang;
use System\Libraries\Session;
use System\Libraries\Render\View;

Flang::load('Global', APP_LANG);
Flang::load('Backend/Home', APP_LANG);

view_header([
    'title' => $title ?? __('Dashboard'),
    'layout' => $layout ?? 'dashboard',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumb ?? [
        [
            'name' => __('Dashboard'),
            'url' => admin_url('home'),
            'active' => true,
        ],
    ],
]);
?>

<!-- [ Main Content ] start -->
<div class="pc-container">
    <div class="pc-content">
        <div class="flex flex-col gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground"><?= __('Dashboard') ?></h1>
                <p class="text-muted-foreground"><?= __('Welcome to your admin dashboard') ?></p>
            </div>
        </div>

        <?php if (Session::has_flash('success')): ?>
            <?php echo View::include('parts/ui/notification', ['type' => 'success', 'message' => Session::flash('success')]); ?>
        <?php endif; ?>
        <?php if (Session::has_flash('error')): ?>
            <?php echo View::include('parts/ui/notification', ['type' => 'error', 'message' => Session::flash('error')]); ?>
        <?php endif; ?>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                <?php echo View::include('parts/home/website-overview', []); ?>
            </div>
        </div>
    </div>
</div>

<?php view_footer(); ?>
