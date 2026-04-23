<?php

/**
 * Theme Detail Hero Section
 * Displays theme preview and basic information
 */

use App\Libraries\Fastlang;

$theme = $data['theme'] ?? [];

$themeFeatures = [
    [
        'icon' => 'search', // SEO optimized (feather: search)
        'color' => '#2563eb', // text-blue-600
        'bg_color' => '#dbeafe' // bg-blue-100
    ],
    [
        'icon' => 'smartphone', // Responsive (feather: smartphone)
        'color' => '#16a34a', // text-green-600
        'bg_color' => '#bbf7d0' // bg-green-100
    ],
    [
        'icon' => 'zap', // Fast loading (feather: zap)
        'color' => '#eab308', // text-yellow-500
        'bg_color' => '#fef9c3' // bg-yellow-100
    ],
    [
        'icon' => 'shield', // Security (feather: shield)
        'color' => '#7c3aed', // text-purple-600
        'bg_color' => '#ede9fe' // bg-purple-100
    ],
    [
        'icon' => 'feather', // Lightweight (feather: feather)
        'color' => '#db2777', // text-pink-600
        'bg_color' => '#fce7f3' // bg-pink-100
    ],
    [
        'icon' => 'layout', // Modern layout (feather: layout)
        'color' => '#4f46e5', // text-indigo-600
        'bg_color' => '#e0e7ff' // bg-indigo-100
    ],
    [
        'icon' => 'code', // Developer friendly (feather: code)
        'color' => '#f59e42', // text-orange-500
        'bg_color' => '#fff7ed' // bg-orange-100
    ],
    [
        'icon' => 'eye', // Accessibility (feather: eye)
        'color' => '#0891b2', // text-cyan-600
        'bg_color' => '#cffafe' // bg-cyan-100
    ],
    [
        'icon' => 'heart', // Popular/Favorite (feather: heart)
        'color' => '#ef4444', // text-red-500
        'bg_color' => '#fee2e2' // bg-red-100
    ],
    [
        'icon' => 'check-circle', // Verified/Trusted (feather: check-circle)
        'color' => '#059669', // text-emerald-600
        'bg_color' => '#d1fae5' // bg-emerald-100
    ],
];

?>

<!-- Hero Section with Theme Preview -->
<section class="relative overflow-hidden bg-white border-b border-gray-200">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-indigo-600/5"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            <!-- Theme Preview -->
            <div class="order-2 lg:order-1">
                <div class="sticky top-8">
                    <!-- Main Preview Image -->
                    <div class="relative group">
                        <div class="absolute -inset-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-all duration-300"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                            <?= _img(
                                theme_assets(get_image_full($theme['thumbnail_url'] ?? $theme['thumbnail'] ?? '/assets/images/placeholder-theme.jpg')),
                                $theme['title'],
                                true,
                                'w-full h-auto aspect-video object-cover'
                            ) ?>

                            <!-- Live Preview Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="absolute bottom-6 left-6 right-6">
                                    <?php
                                    $previewImage    = theme_assets(get_image_full($theme['thumbnail_url'] ?? '/assets/images/placeholder-theme.jpg'));
                                    $hasLivePreview  = !empty($theme['live_preview_url']);
                                    ?>

                                    <?php if ($hasLivePreview): ?>
                                        <a href="<?= $theme['live_preview_url'] ?>" target="_blank" rel="noopener"
                                            class="inline-flex items-center justify-center w-full px-6 py-3 bg-white/95 backdrop-blur-sm text-gray-900 font-semibold rounded-xl hover:bg-white transition-all duration-200 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            <?= Fastlang::__('theme_detail.hero.live_preview') ?>
                                        </a>
                                    <?php else: ?>
                                        <button type="button" onclick="openImageModal('<?= htmlspecialchars($previewImage) ?>', '<?= htmlspecialchars($theme['title']) ?>')"
                                            class="inline-flex items-center justify-center w-full px-6 py-3 bg-white/95 backdrop-blur-sm text-gray-900 font-semibold rounded-xl hover:bg-white transition-all duration-200 shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <?= Fastlang::__('theme_detail.hero.view_image') ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Screenshots -->
                    <?php if (!empty($theme['screenshots'])): ?>
                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <?php foreach (array_slice($theme['screenshots'], 0, 3) as $index => $screenshot): ?>
                                <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer"
                                    onclick="openImageModal('<?= htmlspecialchars($screenshot) ?>', '<?= htmlspecialchars($theme['title']) ?> - Screenshot <?= $index + 1 ?>')">
                                    <?= _img($screenshot, $theme['title'], true, 'w-full h-full object-cover') ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Theme Information -->
            <div class="order-1 lg:order-2">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
                    <a href="<?= base_url('libraries', APP_LANG) ?>" class="hover:text-green-600 transition-colors"><?= Fastlang::__('theme_detail.hero.breadcrumb.library') ?></a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="<?= base_url('libraries/themes', APP_LANG) ?>" class="hover:text-green-600 transition-colors"><?= Fastlang::__('theme_detail.hero.breadcrumb.themes') ?></a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($theme['title'] ?? '') ?></span>
                </nav>

                <!-- Theme Title & Category -->
                <div class="mb-6">
                    <div class="flex items-center mb-4">
                        <?php
                        if (!empty($theme['categories'])):
                            foreach ($theme['categories'] as $category): ?>
                                <?php if ($category['lang'] == APP_LANG && $category['type'] == 'category'): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mr-3">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </span>
                                <?php endif; ?>
                        <?php endforeach;
                        endif;
                        ?>
                        <?php if (!empty($theme['is_featured'])): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 mr-3">
                                <?= Fastlang::__('theme_detail.hero.featured') ?>
                            </span>
                        <?php endif; ?>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            <?= date('M d, Y', strtotime($theme['created_at'] ?? date('Y-m-d'))) ?>
                        </div>
                    </div>

                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                        <?= htmlspecialchars($theme['title'] ?? '') ?>
                    </h1>

                    <?php if (!empty($theme['tagline'])): ?>
                        <p class="text-xl text-blue-600 font-medium mb-3">
                            <?= htmlspecialchars($theme['tagline']) ?>
                        </p>
                    <?php endif; ?>

                    <p class="text-xl text-gray-600 leading-relaxed mb-6">
                        <?= htmlspecialchars($theme['description'] ?? $theme['seo_desc'] ?? '') ?>
                    </p>
                </div>

                <!-- Stats & Rating -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900"><?= number_format($theme['download'] ?? 0) ?></div>
                        <div class="text-sm text-gray-500"><?= Fastlang::__('theme_detail.hero.downloads') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center mb-1">
                            <div class="text-2xl font-bold text-gray-900 mr-1"><?= $theme['rating_avg'] ?? '4.8' ?></div>
                            <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-500"><?= ($theme['rating_count'] ?? 45) . ' ' . Fastlang::__('theme_detail.hero.reviews') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900"><?= format_views($theme['views'] ?? 0) ?></div>
                        <div class="text-sm text-gray-500"><?= Fastlang::__('theme_detail.hero.views') ?></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mb-8">
                    <a href="<?= $theme['detail_url'] ?>"
                        class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <?php if (empty($theme['price']) || $theme['price'] == 0): ?>
                            <?= Fastlang::__('theme_detail.hero.download_free') ?>
                        <?php else: ?>
                            <?= Fastlang::__('theme_detail.hero.buy_now') . ' - $' . number_format($theme['price']) ?>
                        <?php endif; ?>
                    </a>
                    <a href="<?= $theme['demo_url'] ?>"
                        class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-yellow-100 text-yellow-800 font-semibold rounded-xl hover:bg-yellow-200 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <!-- SVG icon "demo" - màn hình hiển thị (monitor/screen) -->
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="12" rx="2" stroke="currentColor" stroke-width="2" fill="none" />
                            <path d="M8 20h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M12 16v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        <?= Fastlang::__('theme_detail.hero.demo') ?>
                    </a>
                    <!-- <button class="px-6 py-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <?= Fastlang::__('theme_detail.hero.add_to_favorites') ?>
                    </button> -->
                </div>

                <!-- Quick Info Tags -->
                <div class="flex flex-wrap gap-2">
                    <?php if (!empty($theme['categories'])): ?>
                        <?php foreach ($theme['categories'] as $category): ?>
                            <?php if ($category['lang'] == APP_LANG && $category['type'] == 'tags'): ?>
                                <?php $randomFeature = $themeFeatures[array_rand($themeFeatures)]; ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" style="background-color: <?= $randomFeature['bg_color'] ?>; color: <?= $randomFeature['color'] ?>;">
                                    <i data-feather="<?= $randomFeature['icon'] ?>" class="w-4 h-4 mr-1"></i>
                                    <?= Fastlang::__('theme_detail.hero.seo_optimized') ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <?= Fastlang::__('theme_detail.hero.responsive') ?>
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        <?= Fastlang::__('theme_detail.hero.seo_optimized') ?>
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                        <?= Fastlang::__('theme_detail.hero.fast_loading') ?>
                    </span> -->
                    <?php if (!empty($theme['tags'])): ?>
                        <?php foreach (array_slice($theme['tags'], 0, 3) as $tag): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
