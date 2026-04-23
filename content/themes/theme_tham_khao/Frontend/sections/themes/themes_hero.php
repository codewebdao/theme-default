<?php

/**
 * Themes Hero Section
 * Displays hero section with search functionality
 */

use App\Libraries\Fastlang;

$search = $search ?? '';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 text-white py-20 lg:py-32 overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight md:leading-[1.4]"><?= Fastlang::__('themes.hero.title') ?></h1>
            <p class="text-xl md:text-2xl mb-8 text-blue-100 leading-relaxed"><?= Fastlang::__('themes.hero.description') ?></p>

            <!-- Search Form -->
            <form method="GET" class="max-w-2xl mx-auto mb-12" id="desktopSearchForm">
                <div class="relative">
                    <input
                        class="w-full px-6 py-4 text-lg bg-white/95 backdrop-blur-sm border-0 rounded-2xl text-gray-900 placeholder-gray-500 relative z-0"
                        placeholder="<?= Fastlang::__('themes.hero.search.placeholder') ?>"
                        type="text"
                        id="desktop-search-input"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>">
                    <button
                        type="submit"
                        class="absolute right-2 top-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-2 rounded-xl z-10">
                        <?= Fastlang::__('themes.hero.search.button') ?>
                    </button>
                </div>
            </form>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold mb-2"><?= number_format(1500) ?>+</div>
                    <div class="text-blue-200 text-sm"><?= Fastlang::__('themes.hero.stats.themes') ?></div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold mb-2">4.8</div>
                    <div class="text-blue-200 text-sm"><?= Fastlang::__('themes.hero.stats.rating') ?></div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold mb-2"><?= number_format(500) ?>+</div>
                    <div class="text-blue-200 text-sm"><?= Fastlang::__('themes.hero.stats.designers') ?></div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold mb-2"><?= number_format(50000) ?>+</div>
                    <div class="text-blue-200 text-sm"><?= Fastlang::__('themes.hero.stats.downloads') ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
