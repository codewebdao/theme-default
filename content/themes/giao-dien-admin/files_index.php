<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Files', APP_LANG);

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Files'),
      'url' => admin_url('files/index'),
      'active' => true
  ]
);
view_header([
    'title' => __('Files Timeline'),
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);
?>
<div class="" x-data="{ showAdd: false }">

  <!-- Header & Filter -->
  <div class="flex flex-col gap-4">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= __('Files Timeline') ?></h1>
      <p class="text-muted-foreground"><?= __('Manage system files timeline') ?></p>
    </div>

    <!-- Thông báo -->
    <?php if (Session::has_flash('success')): ?>
      <?php echo View::include('parts/ui/notification', ['type' => 'success', 'message' => Session::flash('success')]); ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php echo View::include('parts/ui/notification', ['type' => 'error', 'message' => Session::flash('error')]); ?>
    <?php endif; ?>

    <!-- Files Timeline iframe -->
    <div class="bg-card rounded-xl border overflow-hidden">
      <iframe src="<?= admin_url('files/timeline') ?>" class="w-full h-[calc(100vh-200px)] border-0"></iframe>
    </div>
  </div>
  
</div>

<?php view_footer(); ?>