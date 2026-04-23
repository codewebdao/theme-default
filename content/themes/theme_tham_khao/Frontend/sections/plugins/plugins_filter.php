<?php

/**
 * Filter Section for Plugins Library
 * Updated to use query parameters for search and sort
 * Category remains in URL path for better SEO
 */

use App\Libraries\Fastlang;

// Load UrlRewrite helper nếu chưa có

// Get current filter parameters from URL
$currentParams = [];
if (!empty($search)) $currentParams['search'] = $search;
if (!empty($category)) $currentParams['category'] = $category;
if (!empty($sortBy) && $sortBy !== 'created_at_desc') $currentParams['sort'] = $sortBy;
if (!empty($paged) && $paged > 1) $currentParams['paged'] = $paged;

// Define categories với count động từ database
// Convert get_terms() result to expected format
$categories = [];
if (!empty($categoriesData)) {
    foreach ($categoriesData as $term) {
        $categories[$term['slug']] = [
            'name' => $term['name'],
            'slug' => $term['slug'],
            'count' => 0 // get_terms() doesn't provide count, set to 0
        ];
    }
}

// Define sort options
$sortOptions = [
    'created_at_desc' => Fastlang::__('plugins.filter.sort.newest'),
    'created_at_asc' => Fastlang::__('plugins.filter.sort.oldest'),
    'title_asc' => Fastlang::__('plugins.filter.sort.name_asc'),
    'title_desc' => Fastlang::__('plugins.filter.sort.name_desc'),
    'rating_desc' => Fastlang::__('plugins.filter.sort.rating_desc'),
    'download_desc' => Fastlang::__('plugins.filter.sort.download_desc'),
    'price_asc' => Fastlang::__('plugins.filter.sort.price_asc'),
    'price_desc' => Fastlang::__('plugins.filter.sort.price_desc')
];
?>
<section class="py-6 bg-white border-b border-slate-200 sticky top-20 z-40 backdrop-blur-sm bg-white/95">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-12">
            <div class="flex-1 min-w-0">
                <div class="hidden lg:block">
                    <div class="overflow-x-auto scrollbar-hide py-1" style="scrollbar-width: none;">
                        <div class="flex gap-2 min-w-max">
                            <a href="<?= base_url('libraries/plugins', APP_LANG) ?>"
                                class="inline-flex items-center justify-center gap-2 text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-9 transition-all duration-200 rounded-full px-4 py-2 whitespace-nowrap flex-shrink-0 
                               <?php echo ($category == '') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white border-transparent' : 'border bg-background hover:text-accent-foreground border-slate-300 text-slate-600 hover:bg-slate-50 hover:border-slate-400'; ?>">
                                <?= Fastlang::__('plugins.filter.all') ?>
                            </a>
                            <?php foreach ($categories as $catKey => $catData): ?>
                                <a href="<?= base_url('libraries/plugins/category/' . $catKey . '/', APP_LANG) ?>"
                                    class="inline-flex items-center justify-center gap-2 text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-9 transition-all duration-200 rounded-full px-4 py-2 whitespace-nowrap flex-shrink-0 
                                    <?php echo ($category === $catKey) ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white border-transparent' : 'border bg-background hover:text-accent-foreground border-slate-300 text-slate-600 hover:bg-slate-50 hover:border-slate-400'; ?>">
                                    <?php echo $catData['name']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="lg:hidden">
                    <!-- Mobile Search -->
                    <div class="mb-4">
                        <form method="GET" class="relative" id="mobileSearchForm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                            <input id="mobile-search-input" class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-slate-800 placeholder:text-slate-500" placeholder="<?php echo Fastlang::__('plugins.hero.search.placeholder'); ?>" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                                <?php echo Fastlang::__('plugins.hero.search.button'); ?>
                            </button>
                        </form>
                    </div>
                    <!-- Mobile Category Select -->
                    <select id="mobile-category-select" class="flex h-10 items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 w-full">
                        <option value="" <?php echo ($category === '') ? 'selected' : ''; ?>>
                            <?= Fastlang::__('plugins.filter.all') ?>
                        </option>
                        <?php foreach ($categories as $catKey => $catData): ?>
                            <option value="<?php echo $catKey; ?>" <?php echo ($category === $catKey) ? 'selected' : ''; ?>>
                                <?php echo $catData['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-4 w-full lg:w-auto">
                <select id="sort-select" class="flex h-10 items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 w-full lg:w-[200px]">
                    <?php foreach ($sortOptions as $sortKey => $sortLabel): ?>
                        <option value="<?php echo $sortKey; ?>" <?php echo ($sortBy === $sortKey) ? 'selected' : ''; ?>>
                            <?php echo $sortLabel; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-sm text-slate-600 whitespace-nowrap hidden lg:block">
                    <span class="font-semibold text-slate-800"><?php echo $totalPlugins; ?></span> <?= Fastlang::__('plugins.filter.plugins_count') ?? 'plugins' ?>
                </div>
            </div>
        </div>
    </div>
</section>



<style>
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
