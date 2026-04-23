<?php

/**
 * Themes Grid Section
 * Displays themes in a grid layout
 */

use App\Libraries\Fastlang;

$themes = $themes ?? [];
$search = $search ?? '';
?>

<!-- Themes Grid Section -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($themes)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($themes as $theme): ?>
                    <div class="bg-white rounded-2xl group shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                        <!-- Theme Image -->
                        <div class="relative aspect-video overflow-hidden">
                            <a href="<?= $theme['url'] ?? base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>">
                                <?= _img(
                                    theme_assets(get_image_full($theme['thumbnail_url'] ?? $theme['thumbnail'] ?? '/assets/images/placeholder-theme.jpg')),
                                    $theme['title'],
                                    true,
                                    'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'
                                ) ?>
                            </a>

                            <!-- Price Badge -->
                            <div class="absolute top-3 right-3">
                                <?php if (empty($theme['price']) || $theme['price'] == 0): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <?= Fastlang::__('themes.grid.free') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        $<?= number_format($theme['price']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Popular Badge -->
                            <?php if (!empty($theme['is_popular'])): ?>
                                <div class="absolute top-3 left-3">
                                    <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <?= Fastlang::__('themes.grid.popular') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Theme Info -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 flex-1">
                                    <a href="<?= $theme['url'] ?? base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>" class="group-hover:text-blue-600 inline">
                                        <?= html_entity_decode($theme['title']) ?>
                                    </a>
                                </h3>
                                <button class="favorite-btn text-slate-400 hover:text-red-500 transition-colors p-1 ml-2" type="button" data-id="<?= $theme['id'] ?? $theme['slug'] ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart">
                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                    </svg>
                                </button>
                            </div>

                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?= html_entity_decode($theme['seo_description'] ?? $theme['description'] ?? '') ?>
                            </p>
                            <!-- Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <?= number_format($theme['rating_avg'] ?? 4.5, 1) ?>
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                        </svg>
                                        <?= number_format($theme['downloads'] ?? $theme['download'] ?? 0) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <!-- <a href="<?= $theme['url'] ?? base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <?= Fastlang::__('themes.grid.view_details') ?>
                                </a> -->
                                <a href="<?= $theme['download_url'] ?? base_url('download/theme/' . $theme['slug'], APP_LANG) ?>"
                                    class="inline-flex flex-1 items-center justify-center px-4 py-2 bg-blue-600 group-hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <?= Fastlang::__('themes.grid.download') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-12">
                    <div class="flex items-center gap-2">
                        <?php if ($currentPage > 1): ?>
                            <?php
                            // Build previous page URL with only category and paged in path
                            $baseUrl = rtrim(base_url('libraries/themes', APP_LANG), '/');
                            $prevUrlParts = [$baseUrl];

                            // Add category parameter to path if exists
                            if (!empty($category)) {
                                $prevUrlParts[] = 'category';
                                $prevUrlParts[] = urlencode($category);
                            }

                            // Add paged parameter to path for previous page (chỉ thêm khi không phải trang 1)
                            if ($currentPage - 1 > 1) {
                                $prevUrlParts[] = 'paged';
                                $prevUrlParts[] = ($currentPage - 1);
                            }

                            // Build URL path
                            $prevUrlPath = implode('/', $prevUrlParts);

                            // Build query parameters for search and sort
                            $prevQueryParams = [];
                            if (!empty($search)) {
                                $prevQueryParams['search'] = $search;
                            }
                            if (!empty($sortBy) && $sortBy !== 'created_at_desc') {
                                $prevQueryParams['sort'] = $sortBy;
                            }

                            // Combine URL path with query parameters
                            $prevUrl = $prevUrlPath;
                            if (!empty($prevQueryParams)) {
                                $prevUrl .= '?' . http_build_query($prevQueryParams);
                            }
                            ?>
                            <a href="<?= $prevUrl ?>" class="px-3 py-2 text-sm border border-slate-300 rounded-md hover:bg-slate-50"><?= Fastlang::__('themes.grid.previous') ?></a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <?php
                            // Build page URL with only category and paged in path
                            $baseUrl = rtrim(base_url('libraries/themes', APP_LANG), '/');
                            $pageUrlParts = [$baseUrl];

                            // Add category parameter to path if exists
                            if (!empty($category)) {
                                $pageUrlParts[] = 'category';
                                $pageUrlParts[] = urlencode($category);
                            }

                            // Add paged parameter to path if not first page
                            if ($i > 1) {
                                $pageUrlParts[] = 'paged';
                                $pageUrlParts[] = $i;
                            }

                            // Build URL path
                            $pageUrlPath = implode('/', $pageUrlParts);

                            // Build query parameters for search and sort
                            $pageQueryParams = [];
                            if (!empty($search)) {
                                $pageQueryParams['search'] = $search;
                            }
                            if (!empty($sortBy) && $sortBy !== 'created_at_desc') {
                                $pageQueryParams['sort'] = $sortBy;
                            }

                            // Combine URL path with query parameters
                            $pageUrl = $pageUrlPath;
                            if (!empty($pageQueryParams)) {
                                $pageUrl .= '?' . http_build_query($pageQueryParams);
                            }
                            ?>
                            <a href="<?= $pageUrl ?>" class="px-3 py-2 text-sm border rounded-md <?php echo $i === $currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-300 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <?php
                            // Build next page URL with only category and paged in path
                            $baseUrl = rtrim(base_url('libraries/themes', APP_LANG), '/');
                            $nextUrlParts = [$baseUrl];

                            // Add category parameter to path if exists
                            if (!empty($category)) {
                                $nextUrlParts[] = 'category';
                                $nextUrlParts[] = urlencode($category);
                            }

                            // Add paged parameter to path for next page
                            $nextUrlParts[] = 'paged';
                            $nextUrlParts[] = ($currentPage + 1);

                            // Build URL path
                            $nextUrlPath = implode('/', $nextUrlParts);

                            // Build query parameters for search and sort
                            $nextQueryParams = [];
                            if (!empty($search)) {
                                $nextQueryParams['search'] = $search;
                            }
                            if (!empty($sortBy) && $sortBy !== 'created_at_desc') {
                                $nextQueryParams['sort'] = $sortBy;
                            }

                            // Combine URL path with query parameters
                            $nextUrl = $nextUrlPath;
                            if (!empty($nextQueryParams)) {
                                $nextUrl .= '?' . http_build_query($nextQueryParams);
                            }
                            ?>
                            <a href="<?= $nextUrl ?>" class="px-3 py-2 text-sm border border-slate-300 rounded-md hover:bg-slate-50"><?= Fastlang::__('themes.grid.next') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Results -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?= Fastlang::__('themes.grid.no_results.title') ?></h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    <?php if (!empty($search)): ?>
                        <?= Fastlang::__('themes.grid.no_results.description') . '<strong>' . html_entity_decode($search) . '</strong>'; ?>
                    <?php else: ?>
                        <?= Fastlang::__('themes.grid.no_results.description') ?>
                    <?php endif; ?>
                </p>
                <a href="<?= base_url('libraries/themes', APP_LANG) ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <?= Fastlang::__('themes.grid.no_results.clear_filters') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
