<?php

/**
 * Theme Detail Related Section
 * Displays related themes
 */

use App\Libraries\Fastlang;

$relatedThemes = $data['relatedThemes'] ?? [];
?>

<!-- Related Themes Section -->
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4"><?= Fastlang::__('theme_detail.related.title') ?></h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?= Fastlang::__('theme_detail.related.description') ?></p>
        </div>

        <?php if (!empty($relatedThemes)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($relatedThemes as $relatedTheme): ?>
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                        <!-- Theme Image -->
                        <div class="relative aspect-video overflow-hidden">
                            <?= _img(
                                theme_assets(get_image_full($relatedTheme['thumbnail_url'] ?? $relatedTheme['thumbnail'] ?? '/assets/images/placeholder-theme.jpg')),
                                $relatedTheme['title'],
                                true,
                                'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'
                            ) ?>

                            <!-- Price Badge -->
                            <div class="absolute top-3 right-3">
                                <?php if (empty($relatedTheme['price']) || $relatedTheme['price'] == 0): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <?= Fastlang::__('theme_detail.common.free') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        $<?= number_format($relatedTheme['price']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Theme Info -->
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                <a href="<?= base_url('libraries/themes/' . $relatedTheme['slug'], APP_LANG) ?>" class="hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($relatedTheme['title']) ?>
                                </a>
                            </h3>

                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?= htmlspecialchars($relatedTheme['seo_description'] ?? $relatedTheme['description'] ?? '') ?>
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <?= number_format($relatedTheme['rating_avg'] ?? 4.5, 1) ?>
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                        </svg>
                                        <?= number_format($relatedTheme['downloads'] ?? 0) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="<?= base_url('libraries/themes/' . $relatedTheme['slug'], APP_LANG) ?>"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <?= Fastlang::__('theme_detail.related.view_details') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- View All Button -->
            <div class="text-center mt-12">
                <a href="<?= base_url('libraries/themes', APP_LANG) ?>"
                    class="inline-flex items-center px-8 py-3 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-xl transition-colors">
                    <?= Fastlang::__('theme_detail.related.view_all_themes') ?>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-gray-500">No related themes found.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
