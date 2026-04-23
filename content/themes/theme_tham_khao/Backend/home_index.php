<?php

namespace System\Libraries;

use App\Libraries\Fastlang as Flang;
use System\Libraries\Session;

// Load language files
Flang::load('Global', APP_LANG);
Flang::load('Backend/Home', APP_LANG);

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home'),
      'active' => true
  ]
);
Render::block('Backend\Header', ['layout' => 'default', 'title' => __('Dashboard'), 'breadcrumb' => $breadcrumbs]);
?>

<!-- [ Main Content ] start -->
<div class="pc-container">
    <div class="pc-content">
        <!-- Header Section -->
        <div class="flex flex-col gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground"><?= __('Dashboard') ?></h1>
                <p class="text-muted-foreground"><?= __('Welcome to your admin dashboard') ?></p>
            </div>
        </div>

        <!-- Thông báo -->
        <?php if (Session::has_flash('success')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
        <?php endif; ?>
        <?php if (Session::has_flash('error')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
        <?php endif; ?>

        <!-- Dashboard Content -->
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                <?php get_template('HomeComponent/website-overview', [], 'Backend'); ?>
            </div>
        </div>
    </div>
</div>

<?php
Render::block('Backend\Footer', ['layout' => 'default']);
?>