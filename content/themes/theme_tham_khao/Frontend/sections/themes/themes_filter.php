<?php

/**
 * Themes Filter Section
 * Displays filter options for themes
 */

use App\Libraries\Fastlang;

$search = $search ?? '';
$category = $category ?? '';
$sortBy = $sortBy ?? 'created_at_desc';
$categoriesData = $categoriesData ?? [];
?>

<!-- Filter Section -->
<section class="bg-white border-b border-gray-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">

            <!-- Mobile Search -->
            <!-- <div class="w-full lg:hidden">
                <form method="GET" class="relative" id="mobileSearchForm">
                    <input
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-500"
                        placeholder="<?= Fastlang::__('themes.hero.search.placeholder') ?>"
                        type="text"
                        name="search"
                        id="mobile-search-input"
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white px-4 py-1 rounded-lg text-sm">
                        <?= Fastlang::__('themes.hero.search.button') ?>
                    </button>
                </form>
            </div> -->

            <!-- Desktop Filters -->
            <div class="flex flex-col md:flex-row gap-4 w-full items-center justify-center">
                <!-- Category Links (Desktop) -->
                <div class="overflow-x-auto w-full" style="scrollbar-width: thin;">
                    <div class="flex flex-nowrap gap-2">
                        <a href="<?= base_url('libraries/themes', APP_LANG) ?>"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap <?= empty($category) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            <?= Fastlang::__('themes.filter.all') ?>
                        </a>
                        <?php foreach ($categoriesData as $catData): ?>
                            <a href="<?= base_url('libraries/themes/category/' . $catData['slug'], APP_LANG) ?>"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap <?= $category === $catData['slug'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                                <?= htmlspecialchars($catData['name'] ?? $catData['slug']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Sort Filter -->
                <div class="relative">
                    <?php
                    $sortOptions = [
                        'created_at_desc' => Fastlang::__('themes.filter.sort.newest'),
                        'created_at_asc' => Fastlang::__('themes.filter.sort.oldest'),
                        'title_asc' => Fastlang::__('themes.filter.sort.name_asc'),
                        'title_desc' => Fastlang::__('themes.filter.sort.name_desc'),
                        'rating_desc' => Fastlang::__('themes.filter.sort.rating_desc'),
                        'download_desc' => Fastlang::__('themes.filter.sort.download_desc'),
                        'price_asc' => Fastlang::__('themes.filter.sort.price_asc'),
                        'price_desc' => Fastlang::__('themes.filter.sort.price_desc')
                    ];
                    ?>
                    <select name="sort" id="sortSelect" class="appearance-none bg-white border border-gray-300 rounded-xl px-4 py-3 pr-10 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <?php foreach ($sortOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $sortBy === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
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
