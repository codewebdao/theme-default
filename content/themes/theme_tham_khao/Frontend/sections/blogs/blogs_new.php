<?php

// Get pagination data
// Updated to use query parameters for search and sort, category remains in URL path
$currentPage = $pagination['currentPage'] ?? 1;
$totalPages = $pagination['totalPages'] ?? 1;
$totalBlogs = $pagination['totalBlogs'] ?? 0;
$perPage = $pagination['perPage'] ?? 8;

// Get search data
$searchParams = $searchParams ?? [];
$blogTypes = $blogTypes ?? [];
$popularTags = $popularTags ?? [];

$listCategories = get_terms([
    'post_type' => 'blogs',
    'taxonomy' => 'category',
    'lang' => APP_LANG,
    'status' => 'active',
    'orderby' => 'id',
    'order' => 'DESC',
    'limit' => 10,
    'offset' => 0
]);

// Define Feather icons for categories (blog-related icons only)
$featherIcons = [
    'file-text',    // Document/Article
    'book',         // Book/Reading
    'edit',         // Writing/Editing
    'pen-tool',     // Creative writing
    'type',         // Typography/Text
    'message-circle', // Communication
    'share',        // Sharing content
    'tag',          // Categories/Tags
    'folder',       // Organization
    'archive'       // Content storage
];

// Define color schemes for categories with random icons
$categoryColors = [
    [
        'bg' => 'bg-blue-100',
        'bg_hover' => 'bg-blue-200',
        'text' => 'text-blue-600',
        'text_hover' => 'text-blue-700',
        'border' => 'border-blue-200',
        'badge_bg' => 'bg-blue-100',
        'badge_text' => 'text-blue-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-green-100',
        'bg_hover' => 'bg-green-200',
        'text' => 'text-green-600',
        'text_hover' => 'text-green-700',
        'border' => 'border-green-200',
        'badge_bg' => 'bg-green-100',
        'badge_text' => 'text-green-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-red-100',
        'bg_hover' => 'bg-red-200',
        'text' => 'text-red-600',
        'text_hover' => 'text-red-700',
        'border' => 'border-red-200',
        'badge_bg' => 'bg-red-100',
        'badge_text' => 'text-red-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-purple-100',
        'bg_hover' => 'bg-purple-200',
        'text' => 'text-purple-600',
        'text_hover' => 'text-purple-700',
        'border' => 'border-purple-200',
        'badge_bg' => 'bg-purple-100',
        'badge_text' => 'text-purple-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-pink-100',
        'bg_hover' => 'bg-pink-200',
        'text' => 'text-pink-600',
        'text_hover' => 'text-pink-700',
        'border' => 'border-pink-200',
        'badge_bg' => 'bg-pink-100',
        'badge_text' => 'text-pink-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-orange-100',
        'bg_hover' => 'bg-orange-200',
        'text' => 'text-orange-600',
        'text_hover' => 'text-orange-700',
        'border' => 'border-orange-200',
        'badge_bg' => 'bg-orange-100',
        'badge_text' => 'text-orange-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-teal-100',
        'bg_hover' => 'bg-teal-200',
        'text' => 'text-teal-600',
        'text_hover' => 'text-teal-700',
        'border' => 'border-teal-200',
        'badge_bg' => 'bg-teal-100',
        'badge_text' => 'text-teal-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-indigo-100',
        'bg_hover' => 'bg-indigo-200',
        'text' => 'text-indigo-600',
        'text_hover' => 'text-indigo-700',
        'border' => 'border-indigo-200',
        'badge_bg' => 'bg-indigo-100',
        'badge_text' => 'text-indigo-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-yellow-100',
        'bg_hover' => 'bg-yellow-200',
        'text' => 'text-yellow-600',
        'text_hover' => 'text-yellow-700',
        'border' => 'border-yellow-200',
        'badge_bg' => 'bg-yellow-100',
        'badge_text' => 'text-yellow-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ],
    [
        'bg' => 'bg-cyan-100',
        'bg_hover' => 'bg-cyan-200',
        'text' => 'text-cyan-600',
        'text_hover' => 'text-cyan-700',
        'border' => 'border-cyan-200',
        'badge_bg' => 'bg-cyan-100',
        'badge_text' => 'text-cyan-700',
        'icon' => $featherIcons[array_rand($featherIcons)]
    ]
];
?>

<section class="py-12 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('latest_title') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">
                    <?php _e('latest_highlight') ?>
                </span>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('latest_description') ?>
            </p>
        </div>

        <!-- Blog Search Form -->
        <div id="blog-search-form" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 md:p-8 mb-12 border border-blue-200">
            <!-- <h3 class="text-xl font-semibold text-slate-800 mb-6 text-center">
                <i class="fas fa-search text-blue-600 mr-2"></i>
                <?php _e('search_blogs_title', 'Search Blogs') ?>
            </h3> -->

            <form id="search-form" method="GET" class="">
                <!-- Main Search Input -->
                <div class="flex flex-col md:flex-row justify-center gap-2">
                    <div class="flex-1 max-w-md">
                        <input
                            id="search-input"
                            name="search"
                            value="<?= htmlspecialchars($searchParams['search'] ?? '') ?>"
                            placeholder="<?php _e('search_blogs_placeholder', 'Search by title, content, or tags...') ?>"
                            class="w-full px-4 h-10 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            type="text">
                    </div>
                    <button type="submit" class="bg-gradient-to-r h-10 max-md:w-fit flex items-center justify-center from-blue-600 to-indigo-700 text-white px-6 py-3 rounded-lg hover:from-teal-700 hover:to-cyan-800 transition-all font-semibold">
                        <i data-feather="search" class="mr-2 w-4 h-4"></i>
                        <?php _e('search_button', 'Search') ?>
                    </button>
                </div>
                <!-- Blog Filter Section - Similar to plugins_filter.php -->
                <?php if (!empty($listCategories)): ?>
                    <section class="pt-6">
                        <div class="">
                            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-12">
                                <div class="w-full">
                                    <div class="flex overflow-x-auto scrollbar-hide py-1 gap-2 category-scroll-container pb-3" style="scrollbar-width: thin;">
                                        <div class="flex gap-2 min-w-max">
                                            <?php
                                            // Build URL for "All Categories" with query parameters
                                            $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                                            $allCategoriesUrl = $baseUrl . '/';

                                            // Build query parameters (only search and sort)
                                            $allCategoriesParams = [];
                                            if (!empty($searchParams['search'])) {
                                                $allCategoriesParams['search'] = $searchParams['search'];
                                            }
                                            if (!empty($searchParams['tags'])) {
                                                $allCategoriesParams['tags'] = $searchParams['tags'];
                                            }
                                            if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                                                $allCategoriesParams['sort'] = $searchParams['sortBy'];
                                            }

                                            $allCategoriesQueryString = !empty($allCategoriesParams) ? '?' . http_build_query($allCategoriesParams) : '';
                                            $allCategoriesUrl .= $allCategoriesQueryString;
                                            ?>
                                            <a href="<?= $allCategoriesUrl ?>"
                                                class="inline-flex items-center justify-center gap-2 text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-9 transition-all duration-200 rounded-full px-4 py-2 whitespace-nowrap flex-shrink-0 category-button
                                                    <?php echo (empty($searchParams['category_id'])) ? 'bg-gradient-to-r from-teal-600 to-blue-600 text-white border-transparent' : 'border bg-background hover:text-accent-foreground border-slate-300 text-slate-600 hover:bg-slate-50 hover:border-slate-400'; ?>">
                                                <?php _e('all_categories', 'All Categories') ?>
                                            </a>
                                            <?php 
                                            if (!empty($listCategories['data'])):
                                            foreach ($listCategories['data'] as $category): ?>
                                                <?php
                                                // Build URL with category in path and query parameters
                                                $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                                                $categoryUrl = $baseUrl . '/category/' . $category['slug'] . '/';

                                                // Build query parameters (only search and sort)
                                                $categoryParams = [];
                                                if (!empty($searchParams['search'])) {
                                                    $categoryParams['search'] = $searchParams['search'];
                                                }
                                                if (!empty($searchParams['tags'])) {
                                                    $categoryParams['tags'] = $searchParams['tags'];
                                                }
                                                if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                                                    $categoryParams['sort'] = $searchParams['sortBy'];
                                                }

                                                $categoryQueryString = !empty($categoryParams) ? '?' . http_build_query($categoryParams) : '';
                                                $categoryUrl .= $categoryQueryString;
                                                ?>
                                                <a href="<?= $categoryUrl ?>"
                                                    class="inline-flex items-center justify-center gap-2 text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-9 transition-all duration-200 rounded-full px-4 py-2 whitespace-nowrap flex-shrink-0 category-button
                                            <?php echo (($searchParams['category_id'] ?? '') == $category['id'] || ($searchParams['category_id'] ?? '') == $category['id_main']) ? 'bg-gradient-to-r from-teal-600 to-blue-600 text-white border-transparent' : 'border bg-background hover:text-accent-foreground border-slate-300 text-slate-600 hover:bg-slate-50 hover:border-slate-400'; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </a>
                                            <?php endforeach; 
                                            endif;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </section>
                <?php endif; ?>
                <!-- Advanced Filters -->
                <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-200"> -->
                <!-- Tags Input -->
                <!-- <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-tags text-slate-500 mr-1"></i>
                            <?php _e('filter_by_tags', 'Filter by Tags') ?>
                        </label>
                        <input
                            id="tags-input"
                            name="tags"
                            value="<?= htmlspecialchars($searchParams['tags'] ?? '') ?>"
                            placeholder="<?php _e('tags_placeholder', 'Enter tags separated by comma') ?>"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm"
                            type="text">
                    </div> -->
                <!-- </div> -->
                <!-- Clear Filters -->
                <!-- <?php if (!empty(array_filter($searchParams))): ?>
                    <div class="text-center pt-2 border-t border-slate-200">
                        <a href="<?= base_url('blogs', APP_LANG) ?>"
                            class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700">
                            <i class="fas fa-times mr-1"></i>
                            <?php _e('clear_filters', 'Clear All Filters') ?>
                        </a>
                    </div>
                <?php endif; ?> -->
            </form>
        </div>

        <!-- Search Results Info -->
        <?php if (!empty(array_filter($searchParams))): ?>
            <div class="mb-8 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <div>
                        <p class="text-blue-800 font-medium">
                            <?php
                            if ($totalBlogs > 0) {
                                echo sprintf(__('search_results_found', 'Found %d blog(s)'), $totalBlogs);
                            } else {
                                echo __('search_no_results', 'No blogs found matching your criteria');
                            }
                            ?>
                        </p>
                        <?php if (!empty($searchParams['search'])): ?>
                            <p class="text-blue-600 text-sm">
                                <?php _e('search_query', 'Search query') ?>: "<strong><?= htmlspecialchars($searchParams['search']) ?></strong>"
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <!-- Mobile horizontal scroll container -->
        <div class="md:flex max-lg:flex-col-reverse items-start gap-4">
            <div class="w-full lg:flex-1">
                <div class="overflow-x-auto md:overflow-visible pb-4 md:pb-0">
                    <?php if (!empty($blogs) && is_array($blogs)): ?>
                        <div class="flex gap-4 md:grid md:grid-cols-2 lg:grid-cols-3 min-w-max md:min-w-0">
                            <?php foreach ($blogs as $blog): ?>
                                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-blue-200 overflow-hidden group flex-shrink-0 w-80 md:w-auto">
                                    <div class="flex flex-col space-y-1.5 p-0 relative">
                                        <?php if (!empty($blog['thumb_url'])): ?>
                                            <a href="<?php echo content_url('blogs', $blog['slug'] ?? '') ?>">
                                                <?= _img(
                                                    theme_assets(get_image_full($blog['thumb_url'])),
                                                    $blog['title'],
                                                    true,
                                                    'w-full h-52 object-cover'
                                                ) ?>
                                            </a>
                                        <?php else: ?>
                                            <div class="w-full h-52 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                <i class="fas fa-blog text-6xl text-white opacity-50"></i>
                                            </div>
                                        <?php endif; ?>

                                        <!-- <div class="absolute top-3 left-3">
                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 text-xs font-semibold bg-blue-500 text-white">
                                                <?php _e('category_performance') ?>
                                            </div>
                                        </div> -->
                                        <div class="absolute top-3 right-3">
                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-blue-500 bg-blue-600 text-white text-xs font-semibold">
                                                <?php _e('badge_featured') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-xl font-bold mb-3 group-hover:text-blue-700 transition-colors duration-300 line-clamp-2">
                                            <a class="inline" href="<?php echo base_url('blogs/' . ($blog['slug'] ?? ''), APP_LANG) ?>">
                                                <?php echo $blog['title'] ?? 'Untitled' ?>
                                            </a>
                                        </h3>
                                        <div class="flex items-center justify-between text-xs text-slate-500">
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user">
                                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                                <span><?php echo htmlspecialchars($blog['author_name'] ?? ($blog['author'] ?? 'Admin')) ?></span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar">
                                                    <path d="M8 2v4"></path>
                                                    <path d="M16 2v4"></path>
                                                    <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                                    <path d="M3 10h18"></path>
                                                </svg>
                                                <?php
                                                $created_at = $blog['created_at'] ?? date('Y-m-d H:i:s');
                                                $day = date('j', strtotime($created_at));
                                                $month = (int)date('n', strtotime($created_at));
                                                $year = date('Y', strtotime($created_at));

                                                // Get month names from translation
                                                $month_names = __('month_names');
                                                $month_name = $month_names[$month] ?? 'month ' . $month;

                                                $date = $day . ' ' . $month_name . ', ' . $year;
                                                ?>
                                                <span><?php echo $date ?></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path d="M2 12l10 10 10-10V2H2v10z" />
                                                    <circle cx="7" cy="7" r="2" fill="currentColor" />
                                                </svg>
                                                <span><?php echo htmlspecialchars($blog['type_display'] ?? ucfirst($blog['type'] ?? 'General')) ?></span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye">
                                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                <span><?php echo format_views($blog['views'] ?? 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center p-4 pt-0">
                                        <a
                                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 group-hover:underline h-10 text-blue-600 p-0 hover:text-blue-700 font-semibold group-hover:translate-x-1 transition-all duration-300"
                                            href="<?php echo content_url('blogs', $blog['slug'] ?? '') ?>">
                                            <?php _e('button_read_more') ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                                <path d="M5 12h14"></path>
                                                <path d="m12 5 7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- No Results Found -->
                        <div class="text-center py-16">
                            <div class="max-w-md mx-auto">
                                <div class="mb-6">
                                    <i class="fas fa-search text-6xl text-slate-300"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-slate-700 mb-4">
                                    <?php _e('no_blogs_found', 'No blogs found') ?>
                                </h3>
                                <p class="text-slate-500 mb-6">
                                    <?php if (!empty(array_filter($searchParams))): ?>
                                        <?php _e('no_search_results_message', 'No blogs match your search criteria. Try adjusting your filters or search terms.') ?>
                                    <?php else: ?>
                                        <?php _e('no_blogs_available', 'No blogs are currently available.') ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty(array_filter($searchParams))): ?>
                                    <a href="<?= base_url('blogs', APP_LANG) ?>"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-refresh mr-2"></i>
                                        <?php _e('clear_search', 'Clear Search') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Categories Sidebar -->
            <div class="w-full lg:w-80 max-lg:hidden flex-shrink-0">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 sticky top-4">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list mr-2 text-blue-600">
                            <line x1="8" x2="21" y1="6" y2="6"></line>
                            <line x1="8" x2="21" y1="12" y2="12"></line>
                            <line x1="8" x2="21" y1="18" y2="18"></line>
                            <line x1="3" x2="3.01" y1="6" y2="6"></line>
                            <line x1="3" x2="3.01" y1="12" y2="12"></line>
                            <line x1="3" x2="3.01" y1="18" y2="18"></line>
                        </svg>
                        <?= _e('categories_title', 'Categories') ?> <?= _e('categories_highlight', 'Categories') ?>
                    </h3>

                    <div class="space-y-3 max-md:max-h-80 max-md:overflow-y-auto category-scroll-container">
                        <?php if (!empty($listCategories['data'])): ?>
                            <?php foreach ($listCategories['data'] as $index => $category): ?>
                                <?php
                                // Get random color scheme for this category
                                $colorScheme = $categoryColors[$index % count($categoryColors)];
                                ?>
                                <a href="<?= rtrim(base_url('blogs', APP_LANG), '/') . '/category/' . $category['slug'] . '/' ?>"
                                    class="flex items-center p-3 rounded-lg hover:<?= $colorScheme['bg_hover'] ?> <?= ($searchParams['category_id'] == $category['id_main'] || $searchParams['category_id'] == $category['id']) ? $colorScheme['bg_hover'] : '' ?> border border-transparent hover:<?= $colorScheme['border'] ?> transition-all duration-200 group category-button">
                                    <div class="w-10 h-10 <?= $colorScheme['bg'] ?> <?= ($searchParams['category_id'] == $category['id_main'] || $searchParams['category_id'] == $category['id']) ? $colorScheme['bg'] : '' ?> rounded-lg flex items-center justify-center mr-3 group-hover:<?= $colorScheme['bg_hover'] ?> transition-colors">
                                        <?php if (!empty($category['thumb_url'])): ?>
                                            <?= _img(
                                                theme_assets(get_image_full($category['thumb_url'])),
                                                $category['name'],
                                                true,
                                                'w-full h-full object-cover rounded-lg'
                                            ) ?>
                                        <?php else: ?>
                                            <!-- Default Feather icon if no thumbnail -->
                                            <i data-feather="<?= $colorScheme['icon'] ?>" class="<?= $colorScheme['text'] ?>" width="18" height="18"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-slate-800 group-hover:<?= $colorScheme['text_hover'] ?> <?= ($searchParams['category_id'] == $category['id_main'] || $searchParams['category_id'] == $category['id']) ? $colorScheme['text_hover'] : '' ?> transition-colors truncate">
                                            <?= $category['name'] ?>
                                        </h4>
                                        <p class="text-xs text-slate-500 truncate">
                                            <?= $category['description'] ?? '' ?>
                                        </p>
                                    </div>
                                    <?php if (!empty($category['count'])): ?>
                                        <div class="ml-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 <?= $colorScheme['badge_bg'] ?> <?= $colorScheme['badge_text'] ?> text-xs font-semibold rounded-full transition-colors">
                                                <?= $category['count'] ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Request New Category -->
                    <!-- <div class="mt-6 pt-6 border-t border-slate-200">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">
                            <?php _e('categories_not_found_title', 'Request New Category') ?>
                        </h4>
                        <p class="text-slate-500 mb-3 text-sm">
                            <?= _e('categories_not_found_desc') ?>
                        </p>
                        <div class="space-y-2">
                                <input
                                    placeholder="<?php _e('categories_request_input_placeholder') ?>"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    type="text">
                            <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-800 transition-all text-sm font-medium">
                                    <?php _e('categories_request_submit') ?>
                                </button>
                            </div>
                    </div> -->
                </div>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
            <!-- Blog Pagination -->
            <div class="mt-12 mb-8">
                <div class="text-center mb-6">
                    <p class="text-sm text-slate-600">
                        <?php
                        $start = (($currentPage - 1) * $perPage) + 1;
                        $end = min($currentPage * $perPage, $totalBlogs);
                        $infoText = __('showing_blogs_info');
                        echo sprintf($infoText, $start, $end, $totalBlogs);
                        ?>
                    </p>
                </div>

                <!-- Pagination Navigation -->
                <div class="flex justify-center">
                    <nav class="flex items-center gap-2" aria-label="Blog Pagination">
                        <!-- Previous Button -->
                        <?php
                        // Build previous page URL with paged in path and query parameters
                        $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                        $prevUrl = $baseUrl . '/';

                        // Add category to path if exists
                        if (!empty($searchParams['category'])) {
                            $prevUrl .= 'category/' . urlencode($searchParams['category']) . '/';
                        }

                        // Add paged to path if not page 1
                        if ($currentPage - 1 > 1) {
                            $prevUrl .= 'paged/' . ($currentPage - 1) . '/';
                        }

                        // Build query parameters (only search and sort)
                        $prevParams = [];
                        if (!empty($searchParams['search'])) {
                            $prevParams['search'] = $searchParams['search'];
                        }
                        if (!empty($searchParams['tags'])) {
                            $prevParams['tags'] = $searchParams['tags'];
                        }
                        if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                            $prevParams['sort'] = $searchParams['sortBy'];
                        }

                        $prevQueryString = !empty($prevParams) ? '?' . http_build_query($prevParams) : '';
                        $prevUrl .= $prevQueryString;
                        ?>
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= $prevUrl ?>"
                                class="flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="hidden sm:inline text-sm font-medium"><?php _e('pagination_previous') ?></span>
                            </a>
                        <?php else: ?>
                            <span class="flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="hidden sm:inline text-sm"><?php _e('pagination_previous') ?></span>
                            </span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);

                        // Show first page if not in range
                        if ($start > 1):
                            // Build first page URL with paged in path and query parameters
                            $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                            $firstUrl = $baseUrl . '/';

                            // Add category to path if exists
                            if (!empty($searchParams['category'])) {
                                $firstUrl .= 'category/' . urlencode($searchParams['category']) . '/';
                            }

                            // Build query parameters (only search and sort)
                            $firstParams = [];
                            if (!empty($searchParams['search'])) {
                                $firstParams['search'] = $searchParams['search'];
                            }
                            if (!empty($searchParams['tags'])) {
                                $firstParams['tags'] = $searchParams['tags'];
                            }
                            if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                                $firstParams['sort'] = $searchParams['sortBy'];
                            }

                            $firstQueryString = !empty($firstParams) ? '?' . http_build_query($firstParams) : '';
                            $firstUrl .= $firstQueryString;
                        ?>
                            <a href="<?= $firstUrl ?>"
                                class="flex items-center justify-center w-10 h-10 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors text-sm font-medium">
                                1
                            </a>
                            <?php if ($start > 2): ?>
                                <span class="flex items-center justify-center w-10 h-10 text-slate-400 text-sm">⋯</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Current page range -->
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-md">
                                    <?= $i ?>
                                </span>
                            <?php else:
                                // Build page URL with paged in path and query parameters
                                $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                                $pageUrl = $baseUrl . '/';

                                // Add category to path if exists
                                if (!empty($searchParams['category'])) {
                                    $pageUrl .= 'category/' . urlencode($searchParams['category']) . '/';
                                }

                                // Add paged to path if not page 1
                                if ($i > 1) {
                                    $pageUrl .= 'paged/' . $i . '/';
                                }

                                // Build query parameters (only search and sort)
                                $pageParams = [];
                                if (!empty($searchParams['search'])) {
                                    $pageParams['search'] = $searchParams['search'];
                                }
                                if (!empty($searchParams['tags'])) {
                                    $pageParams['tags'] = $searchParams['tags'];
                                }
                                if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                                    $pageParams['sort'] = $searchParams['sortBy'];
                                }

                                $pageQueryString = !empty($pageParams) ? '?' . http_build_query($pageParams) : '';
                                $pageUrl .= $pageQueryString;
                            ?>
                                <a href="<?= $pageUrl ?>"
                                    class="flex items-center justify-center w-10 h-10 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors text-sm font-medium">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Show last page if not in range -->
                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <span class="flex items-center justify-center w-10 h-10 text-slate-400 text-sm">⋯</span>
                            <?php endif; ?>
                            <?php
                            // Build last page URL with paged in path and query parameters
                            $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                            $lastUrl = $baseUrl . '/';

                            // Add category to path if exists
                            if (!empty($searchParams['category'])) {
                                $lastUrl .= 'category/' . urlencode($searchParams['category']) . '/';
                            }

                            // Add paged to path
                            $lastUrl .= 'paged/' . $totalPages . '/';

                            // Build query parameters (only search and sort)
                            $lastParams = [];
                            if (!empty($searchParams['search'])) {
                                $lastParams['search'] = $searchParams['search'];
                            }
                            if (!empty($searchParams['tags'])) {
                                $lastParams['tags'] = $searchParams['tags'];
                            }
                            if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                                $lastParams['sort'] = $searchParams['sortBy'];
                            }

                            $lastQueryString = !empty($lastParams) ? '?' . http_build_query($lastParams) : '';
                            $lastUrl .= $lastQueryString;
                            ?>
                            <a href="<?= $lastUrl ?>"
                                class="flex items-center justify-center w-10 h-10 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors text-sm font-medium">
                                <?= $totalPages ?>
                            </a>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <?php
                        // Build next page URL with paged in path and query parameters
                        $baseUrl = rtrim(base_url('blogs', APP_LANG), '/');
                        $nextUrl = $baseUrl . '/';

                        // Add category to path if exists
                        if (!empty($searchParams['category'])) {
                            $nextUrl .= 'category/' . urlencode($searchParams['category']) . '/';
                        }

                        // Add paged to path if not page 1
                        if ($currentPage + 1 <= $totalPages) {
                            $nextUrl .= 'paged/' . ($currentPage + 1) . '/';
                        }

                        // Build query parameters (only search and sort)
                        $nextParams = [];
                        if (!empty($searchParams['search'])) {
                            $nextParams['search'] = $searchParams['search'];
                        }
                        if (!empty($searchParams['tags'])) {
                            $nextParams['tags'] = $searchParams['tags'];
                        }
                        if (!empty($searchParams['sortBy']) && $searchParams['sortBy'] !== 'created_at_desc') {
                            $nextParams['sort'] = $searchParams['sortBy'];
                        }

                        $nextQueryString = !empty($nextParams) ? '?' . http_build_query($nextParams) : '';
                        $nextUrl .= $nextQueryString;
                        ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= $nextUrl ?>"
                                class="flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition-colors">
                                <span class="hidden sm:inline text-sm font-medium"><?php _e('pagination_next') ?></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed">
                                <span class="hidden sm:inline text-sm"><?php _e('pagination_next') ?></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Load Blogs JavaScript -->

<!-- CSS for enhanced styling -->
<style>
    .search-form {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(99, 102, 241, 0.05) 100%);
    }

    .tag-button {
        transition: all 0.2s ease;
    }

    .tag-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Custom scrollbar for horizontal scroll */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Enhanced category scroll behavior */
    .category-scroll-container {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }

    .category-scroll-container::-webkit-scrollbar {
        height: 8px;
    }

    .category-scroll-container::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }

    .category-scroll-container::-webkit-scrollbar-thumb {
        background: linear-gradient(to right, #cbd5e1, #94a3b8);
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }

    .category-scroll-container::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to right, #94a3b8, #64748b);
    }

    .category-scroll-container::-webkit-scrollbar-thumb:active {
        background: linear-gradient(to right, #64748b, #475569);
    }

    /* Category button hover effects */
    .category-button {
        transition: all 0.2s ease-in-out;
        transform: translateY(0);
    }

    .category-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .category-button:active {
        transform: translateY(0);
    }

    /* Responsive scroll behavior */
    @media (max-width: 768px) {
        .category-scroll-container {
            scroll-snap-type: x mandatory;
        }

        .category-button {
            scroll-snap-align: start;
            min-width: max-content;
        }
    }

    /* Search form animations */
    .search-form input:focus,
    .search-form select:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
    }

    /* Search results highlight */
    .search-results-info {
        animation: fadeIn 0.5s ease-in-out;
    }

    /* Loading states */
    .search-loading {
        opacity: 0.7;
        pointer-events: none;
    }

    #search-loading-overlay {
        backdrop-filter: blur(2px);
    }

    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }

    /* Search form highlight effect */
    #blog-search-form {
        transition: box-shadow 0.3s ease;
    }

    #blog-search-form.highlighted {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }

    /* Focus indicators */
    input:focus,
    select:focus {
        outline: 2px solid rgba(59, 130, 246, 0.5);
        outline-offset: 2px;
    }

    /* Button loading state */
    button:disabled {
        cursor: not-allowed;
    }

    button .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Search suggestions styling */
    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 0.5rem 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 50;
    }

    .search-suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s;
    }

    .search-suggestion-item:hover {
        background-color: #f8fafc;
    }

    .search-suggestion-item:last-child {
        border-bottom: none;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        #blog-search-form {
            margin-bottom: 2rem;
        }

        .search-form {
            padding: 1rem;
        }
    }

    /* Accessibility improvements */
    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto;
        }

        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* Hide scrollbar for webkit browsers */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for Firefox */
    .scrollbar-hide {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }
</style>



</div>
</section>
