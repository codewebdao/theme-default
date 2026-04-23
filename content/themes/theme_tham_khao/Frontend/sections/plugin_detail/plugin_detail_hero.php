<?php

/**
 * Plugin Detail Hero Section
 * Displays plugin preview and basic information
 */

use App\Libraries\Fastlang;

$plugin = $data['plugin'] ?? [];
?>

<!-- Hero Section with Plugin Preview -->
<section class="relative overflow-hidden bg-slate-50 border-b border-slate-200">
    <div class="absolute inset-0 bg-gradient-to-r from-purple-600/5 to-pink-600/5"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">

            <!-- Plugin Preview -->
            <div class="order-2 lg:order-1">
                <div class="sticky top-8">
                    <!-- Main Preview Image -->
                    <div class="relative group">
                        <div class="absolute -inset-4 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-all duration-300"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200">
                            <?= _img(
                                theme_assets(get_image_full($plugin['feature'] ?? '/assets/images/placeholder-plugin.jpg')),
                                $plugin['title'],
                                true,
                                'w-full h-auto aspect-video object-cover'
                            ) ?>

                            <!-- Live Preview Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="absolute bottom-6 left-6 right-6">
                                    <?php
                                    $previewImage = theme_assets(get_image_full($plugin['feature'] ?? '/assets/images/placeholder-plugin.jpg'));
                                    $hasLiveDemo = !empty($plugin['demo_url']);
                                    ?>

                                    <?php if ($hasLiveDemo): ?>
                                        <a href="<?= $plugin['demo_url'] ?>" target="_blank" rel="noopener"
                                            class="inline-flex items-center justify-center w-full px-6 py-3 bg-white/95 backdrop-blur-sm text-gray-900 font-semibold rounded-xl hover:bg-white transition-all duration-200 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            <?= __('plugin_detail.hero.live_demo') ?>
                                        </a>
                                    <?php else: ?>
                                        <button type="button" onclick="openImageModal('<?= htmlspecialchars($previewImage) ?>', '<?= htmlspecialchars($plugin['title']) ?>')"
                                            class="inline-flex items-center justify-center w-full px-6 py-3 bg-white/95 backdrop-blur-sm text-gray-900 font-semibold rounded-xl hover:bg-white transition-all duration-200 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <?= __('plugin_detail.hero.view_image') ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Screenshots -->
                    <?php if (!empty($plugin['screenshots'])): ?>
                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <?php foreach (array_slice($plugin['screenshots'], 0, 3) as $index => $screenshot): ?>
                                <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer"
                                    onclick="openImageModal('<?= htmlspecialchars($screenshot) ?>', '<?= htmlspecialchars($plugin['title']) ?> - Screenshot <?= $index + 1 ?>')">
                                    <?= _img(theme_assets(get_image_full($screenshot)), $plugin['title'], true, 'w-full h-full object-cover') ?>
                                </div>  
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Plugin Information -->
            <div class="order-1 lg:order-2 space-y-8">

                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-slate-500 mb-6">
                    <a href="<?= base_url('libraries', APP_LANG) ?>" class="hover:text-purple-600 transition-colors"><?= Fastlang::__('plugin_detail.hero.breadcrumb.library') ?></a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="<?= base_url('libraries/plugins', APP_LANG) ?>" class="hover:text-purple-600 transition-colors"><?= __('plugin_detail.hero.breadcrumb.plugins') ?></a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-slate-900 font-medium"><?= htmlspecialchars($plugin['title']) ?></span>
                </nav>

                <!-- Plugin Icon and Title -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-2xl <?= $plugin['icon_bg_class'] ?? 'bg-gradient-to-br from-purple-500 to-pink-600' ?> flex items-center justify-center shadow-lg">
                            <?php if (!empty($plugin['icon_url'])): ?>
                                <?= _img(theme_assets(get_image_full($plugin['icon_url'])), $plugin['title'], true, 'w-12 h-12 object-contain') ?>
                            <?php else: ?>
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-2"><?= htmlspecialchars($plugin['title']) ?></h1>
                        <p class="text-lg text-slate-600 leading-relaxed"><?= htmlspecialchars($plugin['description'] ?? '') ?></p>
                    </div>
                </div>

                <!-- Plugin Stats -->
                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-slate-50 rounded-xl">
                        <div class="flex items-center justify-center mb-2">
                            <svg class="w-5 h-5 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-xl md:text-2xl font-bold text-slate-900"><?= number_format($plugin['rating_avg'] ?? 4.8, 1) ?></span>
                        </div>
                        <p class="text-sm text-slate-600"><?= __('plugin_detail.hero.rating') ?></p>
                    </div>

                    <div class="text-center p-4 bg-slate-50 rounded-xl">
                        <div class="text-xl md:text-2xl font-bold text-slate-900 mb-2"><?= $plugin['formatted_downloads'] ?? '0' ?></div>
                        <p class="text-sm text-slate-600"><?= __('plugin_detail.hero.downloads') ?></p>
                    </div>

                    <div class="text-center p-4 bg-slate-50 rounded-xl">
                        <div class="text-xl md:text-2xl font-bold text-slate-900 mb-2"><?= $plugin['formatted_views'] ?? '0' ?></div>
                        <p class="text-sm text-slate-600"><?= __('plugin_detail.hero.views') ?></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= $plugin['install_url'] ?? '#' ?>"
                        class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <?= __('plugin_detail.hero.install_plugin') ?>
                    </a>

                    <div class="flex gap-3">
                        <?php if (!empty($plugin['demo_url'])): ?>
                            <a href="<?= $plugin['demo_url'] ?>" target="_blank"
                                class="flex items-center justify-center px-6 py-4 bg-white border-2 border-slate-300 hover:border-purple-500 text-slate-700 hover:text-purple-600 font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                <?= __('plugin_detail.hero.demo') ?>
                            </a>
                        <?php endif; ?>

                        <button id="favoriteBtn" class="flex items-center justify-center px-6 py-4 bg-slate-50 border-2 border-slate-200 hover:border-red-200 text-slate-600 hover:text-red-600 font-semibold rounded-xl transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span><?= __('plugin_detail.hero.favorite') ?></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
