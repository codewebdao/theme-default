<?php

use System\Libraries\Render;
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
Render::block('Backend\\Header', ['layout' => 'default', 'title' => __('Files Timeline'), 'breadcrumb' => $breadcrumbs ]);
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
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
    <?php endif; ?>

    <!-- Files Timeline iframe -->
    <div class="bg-card rounded-xl border overflow-hidden">
      <iframe src="<?= admin_url('files/timeline') ?>" class="w-full h-[calc(100vh-200px)] border-0"></iframe>
    </div>
  </div>
  
</div>

<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>