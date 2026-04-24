<?php

use System\Libraries\Render;
use System\Libraries\Session;

$breadcrumbs = [
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home')
    ],
    [
        'name' => __('Advanced Custom Fields'),
        'url' => admin_url('acfields')
    ],
    [
        'name' => __('Settings'),
        'url' => admin_url('acfields/settings'),
        'active' => true
    ]
];

Render::block('Backend\\Header', [
    'layout' => 'default', 
    'title' => __('Acfields Plugin Settings'), 
    'breadcrumb' => $breadcrumbs
]);

// Flash messages
$successMessage = Session::flash('success');
$errorMessage = Session::flash('error');
?>

<div class="container mx-auto px- py-6">
    <!-- Flash Messages -->
    <?php if ($successMessage): ?>
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center" 
         x-data="{ show: true }" 
         x-show="show" 
         x-transition>
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?= esc_html($successMessage) ?></span>
        <button @click="show = false" class="ml-auto text-green-700 hover:text-green-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center" 
         x-data="{ show: true }" 
         x-show="show" 
         x-transition>
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span><?= esc_html($errorMessage) ?></span>
        <button @click="show = false" class="ml-auto text-red-700 hover:text-red-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>

    <div class="">
            <?= $formHtml ?>
        </div>

    <!-- Help Section -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 mb-1">
                    <?= __('Settings Help') ?>
                </h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><strong><?= __('Cache:') ?></strong> <?= __('Enable caching to improve performance when loading fields') ?></li>
                    <li><strong><?= __('Logging:') ?></strong> <?= __('Enable logging to track field operations and debug issues') ?></li>
                    <li><strong><?= __('Validation:') ?></strong> <?= __('Automatic validation of field data before saving') ?></li>
                    <li><strong><?= __('Debug Mode:') ?></strong> <?= __('Show detailed error messages and debug information (not recommended for production)') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php Render::block('Backend\\Footer'); ?>

