<?php

/**
 * Plugins Grid Section for Plugins Library
 */

use App\Libraries\Fastlang;
?>
<section class="py-16 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
            <?php if (!empty($plugins)): ?>
                <?php foreach ($plugins as $plugin): ?>
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-slate-200 group">
                        <div class="relative overflow-hidden">
                            <a href="<?= base_url('libraries/plugins/' . $plugin['slug'], APP_LANG) ?>">
                                <?= _img(theme_assets(get_image_full($plugin['icon_url'])), $plugin['title'], true, 'w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300') ?>
                            </a>
                            <!-- <img alt="<?php echo htmlspecialchars($plugin['title']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" src="<?php echo $plugin['icon_url']; ?>"> -->
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold <?php echo ($plugin['price'] ?? 0) == 0 ? 'bg-green-100 text-green-700 border-green-200' : 'bg-purple-100 text-purple-700 border-purple-200'; ?>">
                                    <?php echo ($plugin['price'] ?? 0) == 0 ? Fastlang::__('plugins.common.free') : '$' . $plugin['price']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-semibold text-slate-800 group-hover:text-purple-600 transition-colors line-clamp-1">
                                    <a href="<?= base_url('libraries/plugins/' . $plugin['slug'], APP_LANG) ?>"><?php echo htmlspecialchars($plugin['title']); ?></a>
                                </h3>
                                <button class="favorite-btn text-slate-400 hover:text-red-500 transition-colors p-1" type="button" data-id="<?= $plugin['id'] ?? $plugin['slug'] ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart">
                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-slate-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($plugin['description'] ?? ''); ?></p>

                            <!-- Rating -->
                            <div class="flex items-center gap-2 mb-4">
                                <div class="flex items-center gap-1">
                                    <?php
                                    $rating = $plugin['rating'] ?? 4.5;
                                    for ($i = 1; $i <= 5; $i++):
                                    ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star <?php echo $i <= $rating ? 'fill-yellow-400 text-yellow-400' : 'text-slate-300'; ?>">
                                            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm font-medium text-slate-700"><?php echo number_format($rating, 1); ?></span>
                                <span class="text-xs text-slate-500">(<?= formatViews($plugin['download_count'] ?? $plugin['formatted_downloads']); ?> <?= Fastlang::__('plugins.common.downloads') ?>)</span>
                            </div>

                            <!-- Author and Version -->
                            <div class="flex items-center gap-2 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user text-slate-400">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="text-xs text-slate-600"><?php echo htmlspecialchars($plugin['author'] ?? 'Unknown'); ?></span>
                                <span class="text-xs text-slate-400">v<?php echo htmlspecialchars($plugin['version'] ?? '1.0.0'); ?></span>
                            </div>

                            <!-- Tags -->
                            <div class="flex flex-wrap gap-1 mb-4">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold text-xs">
                                    <?php echo ($plugin['price'] ?? 0) == 0 ? Fastlang::__('plugins.common.free') : Fastlang::__('plugins.common.premium'); ?>
                                </span>
                                <?php if ($plugin['is_popular'] ?? false): ?>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold text-xs"><?= Fastlang::__('plugins.common.popular') ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <!-- <a href="<?php echo $plugin['detail_url']; ?>" class="flex-1 inline-flex items-center justify-center gap-2 text-sm font-medium border border-purple-200 text-purple-600 hover:bg-purple-50 h-9 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye mr-1">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a> -->
                                <a href="<?php echo $plugin['install_url']; ?>" class="flex-1 inline-flex items-center justify-center gap-2 text-sm font-medium bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white h-9 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download mr-1">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" x2="12" y1="15" y2="3"></line>
                                    </svg>
                                    <?= Fastlang::__('plugins.grid.install') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-x mx-auto mb-4 text-slate-400">
                        <path d="m13.5 8.5-5 5"></path>
                        <path d="m8.5 8.5 5 5"></path>
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.3-4.3"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= Fastlang::__('plugins.grid.no_results.title') ?></h3>
                    <p class="text-slate-600 mb-4"><?= Fastlang::__('plugins.grid.no_results.description') ?></p>
                    <a href="<?php echo base_url('libraries/plugins', APP_LANG); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                        <?= Fastlang::__('plugins.grid.no_results.clear_filters') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center">
                <div class="flex items-center gap-2">
                    <?php if ($currentPage > 1): ?>
                        <?php
                        // Build previous page URL with rewrite format
                        $baseUrl = rtrim(base_url('libraries/plugins', APP_LANG), '/');
                        $prevUrlParts = [$baseUrl];

                        // Add paged parameter for previous page (chỉ thêm khi không phải trang 1)
                        if ($currentPage - 1 > 1) {
                            $prevUrlParts[] = 'paged';
                            $prevUrlParts[] = ($currentPage - 1);
                        }

                        // Add search parameter if exists
                        if (!empty($search)) {
                            $prevUrlParts[] = 'search';
                            $prevUrlParts[] = urlencode(str_replace(' ', '-', $search));
                        }

                        // Add category parameter if exists
                        if (!empty($category)) {
                            $prevUrlParts[] = 'category';
                            $prevUrlParts[] = urlencode($category);
                        }

                        // Add sort parameter if exists and not default
                        if (!empty($sortBy) && $sortBy !== 'created_at_desc') {
                            $prevUrlParts[] = 'sort';
                            $prevUrlParts[] = urlencode($sortBy);
                        }
                        $prevUrl = implode('/', $prevUrlParts);
                        ?>
                        <a href="<?= $prevUrl ?>" class="px-3 py-2 text-sm border border-slate-300 rounded-md hover:bg-slate-50"><?= Fastlang::__('plugins.grid.previous') ?></a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <?php
                        // Build page URL with rewrite format
                        $baseUrl = rtrim(base_url('libraries/plugins', APP_LANG), '/');
                        $pageUrlParts = [$baseUrl];

                        // Add paged parameter
                        if ($i > 1) {
                            $pageUrlParts[] = 'paged';
                            $pageUrlParts[] = $i;
                        }

                        // Add search parameter if exists
                        if (!empty($search)) {
                            $pageUrlParts[] = 'search';
                            $pageUrlParts[] = urlencode(str_replace(' ', '-', $search));
                        }

                        // Add category parameter if exists
                        if (!empty($category)) {
                            $pageUrlParts[] = 'category';
                            $pageUrlParts[] = urlencode($category);
                        }

                        // Add sort parameter if exists and not default
                        if (!empty($sortBy) && $sortBy !== 'created_at_desc') {
                            $pageUrlParts[] = 'sort';
                            $pageUrlParts[] = urlencode($sortBy);
                        }

                        $pageUrl = implode('/', $pageUrlParts);
                        ?>
                        <a href="<?= $pageUrl ?>" class="px-3 py-2 text-sm border rounded-md <?php echo $i === $currentPage ? 'bg-purple-600 text-white border-purple-600' : 'border-slate-300 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <?php
                        // Build next page URL with rewrite format
                        $baseUrl = rtrim(base_url('libraries/plugins', APP_LANG), '/');
                        $nextUrlParts = [$baseUrl];

                        // Add paged parameter for next page
                        $nextUrlParts[] = 'paged';
                        $nextUrlParts[] = ($currentPage + 1);

                        // Add search parameter if exists
                        if (!empty($search)) {
                            $nextUrlParts[] = 'search';
                            $nextUrlParts[] = urlencode(str_replace(' ', '-', $search));
                        }

                        // Add category parameter if exists
                        if (!empty($category)) {
                            $nextUrlParts[] = 'category';
                            $nextUrlParts[] = urlencode($category);
                        }

                        // Add sort parameter if exists and not default
                        if (!empty($sortBy) && $sortBy !== 'created_at_desc') {
                            $nextUrlParts[] = 'sort';
                            $nextUrlParts[] = urlencode($sortBy);
                        }

                        $nextUrl = implode('/', $nextUrlParts);
                        ?>
                        <a href="<?= $nextUrl ?>" class="px-3 py-2 text-sm border border-slate-300 rounded-md hover:bg-slate-50"><?= Fastlang::__('plugins.grid.next') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
