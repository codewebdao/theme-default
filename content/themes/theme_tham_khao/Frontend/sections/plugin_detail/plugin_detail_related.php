<?php

/**
 * Plugin Detail Related Section
 * Related plugins section
 */

// Get data from parent template
$related_plugins = $related_plugins ?? [];
?>

<?php if (!empty($related_plugins)): ?>
    <!-- Related Plugins Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                <?= __('plugin_detail.related.related_plugins') ?>
            </h2>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto">
                <?= __('plugin_detail.related.discover_more_plugins') ?>
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($related_plugins as $plugin): ?>
                <div class="group flex flex-col justify-between bg-white rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden hover:shadow-xl hover:border-purple-200 transition-all duration-300 transform hover:-translate-y-1">

                    <!-- Plugin Icon & Badge -->
                    <div class="relative p-6 pb-4">
                        <?php if ($plugin['is_featured']): ?>
                            <div class="absolute top-3 right-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                <?= __('plugin_detail.related.featured') ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-2xl <?= $plugin['icon_bg_class'] ?? 'bg-gradient-to-br from-purple-500 to-pink-600' ?>">
                            <?php if (!empty($plugin['icon_url'])): ?>
                                <?= _img(theme_assets(get_image_full($plugin['icon_url'])), htmlspecialchars($plugin['title']), false, 'w-12 h-12 object-contain') ?>
                            <?php else: ?>
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 text-center mb-2 group-hover:text-purple-600 transition-colors">
                            <a href="<?= base_url('libraries/plugins/' . $plugin['slug'], APP_LANG) ?>" class="group-hover:text-purple-600 transition-colors"><?= htmlspecialchars($plugin['title']) ?></a>
                        </h3>

                        <p class="text-sm text-slate-600 text-center line-clamp-2 mb-4">
                            <?= htmlspecialchars($plugin['seo_desc']) ?>
                        </p>
                    </div>

                    <!-- Plugin Stats -->
                    <div class="px-6 pb-4">
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 fill-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span><?= number_format($plugin['rating_avg'] ?? 4.8, 1) ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span><?= format_views($plugin['download']) ?></span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <a href="<?= base_url('libraries/plugins/' . $plugin['slug'], APP_LANG) ?>"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all duration-200 text-center block">
                            <?= __('plugin_detail.related.view_details') ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- View All Plugins -->
        <div class="text-center mt-12">
            <a href="<?= base_url('libraries/plugins', APP_LANG) ?>"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold px-8 py-4 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <?= __('plugin_detail.related.view_all_plugins') ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </section>
<?php endif; ?>
