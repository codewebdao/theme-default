<?php

/**
 * Plugin Detail Main Content Section
 * Displays plugin description, features, and specifications
 */

$plugin = $data['plugin'] ?? [];
?>

<!-- Main Content Area -->
<div class="lg:col-span-2 space-y-12">

    <!-- Plugin Description -->
    <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
        <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= __('plugin_detail.content.about_plugin') ?>
        </h2>
        <div class="prose prose-lg max-w-none text-slate-600 leading-relaxed">
            <?php if (!empty($plugin['content'])): ?>
                <div class="mt-6 pt-6 border-t border-slate-200">
                    <?= $plugin['content'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Key Features Grid -->
    <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
        <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-8 flex items-center">
            <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= __('plugin_detail.content.key_features') ?>
        </h2>

        <div class="grid md:grid-cols-2 gap-6">
            <?php if (!empty($plugin['feature_detail'])): ?>
                <?php
                $featuresData = json_decode($plugin['feature_detail'], true);
                ?>
                <?php foreach ($featuresData as $feature): ?>
                    <div class="flex items-start p-4 rounded-xl">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center mr-4" style="background-color: <?= $feature['bg_color'] ?? '#000' ?>; color: <?= $feature['color'] ?? '#000' ?>;">
                            <i data-feather="<?= $feature['icon'] ?? '' ?>" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 mb-1"><?= $feature['title'] ?? '' ?></h3>
                            <p class="text-sm text-slate-600"><?= $feature['desc'] ?? '' ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- <div class="flex items-start p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-1"><?= __('plugin_detail.content.customizable') ?></h3>
                    <p class="text-sm text-slate-600"><?= __('plugin_detail.content.flexible_settings') ?></p>
                </div>
            </div>

            <div class="flex items-start p-4 bg-gradient-to-r from-purple-50 to-violet-50 rounded-xl">
                <div class="flex-shrink-0 w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-1"><?= __('plugin_detail.content.secure_reliable') ?></h3>
                    <p class="text-sm text-slate-600"><?= __('plugin_detail.content.tested_secure') ?></p>
                </div>
            </div>

            <div class="flex items-start p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-xl">
                <div class="flex-shrink-0 w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-1"><?= __('plugin_detail.content.support_updates') ?></h3>
                    <p class="text-sm text-slate-600"><?= __('plugin_detail.content.regular_updates') ?></p>
                </div>
            </div> -->
        </div>
    </div>

    <!-- Installation Guide -->
    <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
        <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-8 flex items-center">
            <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <?= __('plugin_detail.content.installation_guide') ?>
        </h2>

        <div class="space-y-6">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white text-sm font-bold">1</span>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2"><?= __('plugin_detail.content.download_plugin') ?></h3>
                    <p class="text-slate-600"><?= __('plugin_detail.content.click_install_download') ?></p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white text-sm font-bold">2</span>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2"><?= __('plugin_detail.content.upload_plugin') ?></h3>
                    <p class="text-slate-600"><?= __('plugin_detail.content.extract_upload_files') ?></p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white text-sm font-bold">3</span>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2"><?= __('plugin_detail.content.activate_plugin') ?></h3>
                    <p class="text-slate-600"><?= __('plugin_detail.content.activate_admin_panel') ?></p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white text-sm font-bold">4</span>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2"><?= __('plugin_detail.content.configure_settings') ?></h3>
                    <p class="text-slate-600"><?= __('plugin_detail.content.configure_preferences') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Plugin Tags -->
    <?php if (!empty($plugin['tags'])): ?>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <?= __('plugin_detail.content.tags') ?>
            </h2>
            <div class="flex flex-wrap gap-3">
                <?php
                $tags = is_array($plugin['tags']) ? $plugin['tags'] : explode(',', $plugin['tags']);
                foreach ($tags as $tag):
                    $tag = trim($tag);
                    if (!empty($tag)):
                ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors cursor-pointer">
                            <?= htmlspecialchars($tag) ?>
                        </span>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Changelog -->
    <?php if (!empty($plugin['changelog'])): ?>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <?= __('plugin_detail.content.changelog') ?>
            </h2>

            <div class="space-y-4">
                <div class="border-l-4 border-green-500 pl-4">
                    <div class="flex items-center mb-2">
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                            v<?= $plugin['version'] ?? '1.0.0' ?>
                        </span>
                        <span class="ml-3 text-sm text-gray-500">
                            <?= date('M j, Y', strtotime($plugin['updated_at'])) ?>
                        </span>
                    </div>
                    <div class="text-gray-600">
                        <?= nl2br(htmlspecialchars($plugin['changelog'])) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
