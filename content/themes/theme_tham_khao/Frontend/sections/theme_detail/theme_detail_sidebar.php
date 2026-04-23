<?php

/**
 * Theme Detail Sidebar Section
 * Displays download card, quick links, and author info
 */

$theme = $data['theme'] ?? [];
$author = getAuthor($theme['author']);
$authorThemesCount = countAuthorThemesPlugins('themes', $theme['author']);
?>

<!-- Enhanced Sidebar -->
<div class="lg:col-span-1">
    <div class="sticky top-20 space-y-8">

        <!-- Download Card -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl">
            <div class="text-center mb-6">
                <div class="text-3xl font-bold mb-2">
                    <?= (empty($theme['price']) || $theme['price'] == 0)
                        ? __('theme_detail.common.free')
                        : '$' . number_format($theme['price'])
                    ?>
                </div>
                <p class="text-blue-100"><?= __('theme_detail.sidebar.one_time_payment') ?></p>
            </div>

            <a href="<?= $theme['detail_url'] ?>"
                class="w-full inline-flex items-center justify-center px-6 py-4 bg-white text-blue-600 font-semibold rounded-xl hover:bg-gray-50 transition-all duration-200 shadow-lg mb-4">
                <?= __('theme_detail.sidebar.download_now') ?>
            </a>

            <div class="space-y-3 text-sm text-blue-100">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <?= __('theme_detail.sidebar.instant_download') ?>
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                    <?= __('theme_detail.sidebar.lifetime_updates') ?>
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <?= __('theme_detail.sidebar.premium_support') ?>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="font-bold text-gray-900 mb-4"><?= __('theme_detail.sidebar.quick_links') ?></h3>
            <div class="space-y-3">
                <?php if (!empty($theme['documentation_url'])): ?>
                    <a href="<?= $theme['documentation_url'] ?>" target="_blank" rel="noopener" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <?= __('theme_detail.sidebar.documentation') ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($theme['demo_url'])): ?>
                    <a href="<?= $theme['demo_url'] ?>" target="_blank" rel="noopener" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <?= __('theme_detail.sidebar.live_demo') ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($theme['detail_url'])): ?>
                    <a href="<?= $theme['detail_url'] ?>" target="_blank" rel="noopener" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <?= __('theme_detail.sidebar.theme_details') ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($theme['support_url'])): ?>
                    <a href="<?= $theme['support_url'] ?>" target="_blank" rel="noopener" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <?= __('theme_detail.sidebar.support') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Author Info -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="font-bold text-gray-900 mb-4"><?= __('theme_detail.sidebar.about_author') ?></h3>
            <div class="flex items-start">
                <!-- <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                    <?= strtoupper(substr($theme['author'] ?? 'A', 0, 1)) ?>
                </div> -->
                <?= _img(theme_assets($author['avatar'] ?? 'images/avatar.png'), 'Avatar', true, 'w-12 h-12 rounded-full mr-2') ?>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">
                        <?= html_entity_decode($author['fullname'] ?? 'Administrator') ?>
                    </h4>
                    <p class="text-sm text-gray-600 mb-3"><?= __('theme_detail.sidebar.theme_developer') ?></p>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        <?= $authorThemesCount . ' ' . __('theme_detail.sidebar.themes') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
