<?php

use App\Libraries\Fastlang;
?>
<div class="relative bg-gradient-to-r from-blue-900 via-purple-900 to-indigo-900 text-white overflow-hidden" data-blog-id="<?= $blog['id'] ?? $blog['id_main'] ?? 'unknown' ?>">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/30 to-purple-600/30"></div>
        <svg class="absolute bottom-0 left-0 w-full h-32" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                fill="rgba(255,255,255,0.1)"></path>
        </svg>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-24">
        <div class="flex flex-col lg:grid lg:grid-cols-2 gap-8 lg:gap-12 lg:items-center">
            <!-- Content Column -->
            <div class="order-2 lg:order-1 space-y-4 lg:space-y-6">
                <!-- Breadcrumb -->
                <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-xs sm:text-sm text-blue-200">
                    <a href="<?= base_url('', APP_LANG) ?>" class="hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg> <?= Fastlang::__('home', 'Blog') ?>
                    </a>
                    <span>/</span>
                    <a href="<?= base_url('blogs', APP_LANG) ?>" class="hover:text-white transition-colors">
                        <?= Fastlang::__('blog', 'Blog') ?>
                    </a>
                    <span>/</span>
                    <span class="text-white truncate max-w-[200px] sm:max-w-xs block" title="<?= htmlspecialchars($blog['title']) ?>">
                        <?= htmlspecialchars($blog['title']) ?>
                    </span>
                </nav>

                <!-- Blog Title -->
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold leading-tight">
                    <?= htmlspecialchars($blog['title']) ?>
                </h1>

                <!-- Blog Meta Information -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-center gap-3 lg:gap-6 text-blue-200 text-sm">
                    <!-- Date -->
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-300">
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg>
                        <span><?= date('F d, Y', strtotime($blog['created_at'])) ?></span>
                    </div>

                    <!-- Views -->
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-300">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span><?= $blog['formatted_views'] ?? number_format($blog['views'] ?? 0) ?> <?= Fastlang::__('views', 'Blog') ?></span>
                    </div>

                    <!-- Reading Time -->
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-300">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                        <span><?= $blog['estimated_read_time'] ?? ceil(str_word_count(strip_tags($blog['content'] ?? '')) / 200) ?> <?= Fastlang::__('minutes', 'Blog') ?> <?= Fastlang::__('read_time', 'Blog') ?></span>
                    </div>

                    <!-- Rating -->
                    <?php if (!empty($blog['rating_avg']) && $blog['rating_avg'] > 0): ?>
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $blog['rating_avg']): ?>
                                        <!-- Filled Star -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                                        </svg>
                                    <?php else: ?>
                                        <!-- Empty Star -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                                        </svg>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span>(<?= $blog['rating_count'] ?? 0 ?>)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($blog['tags'])):
                    $tags_array = explode(',', $blog['tags']);
                ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($tags_array as $tag): ?>
                            <a href="<?= base_url('blogs?tag=' . urlencode(trim($tag)), APP_LANG) ?>"
                                class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm hover:bg-white/30 transition-all duration-300 hover:scale-105">
                                #<?= trim(htmlspecialchars($tag)) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2 lg:pt-4">
                    <button class="blog-share-btn bg-white text-blue-900 px-4 lg:px-6 py-2 lg:py-3 rounded-lg font-semibold hover:bg-blue-50 transition-all duration-300 flex items-center justify-center gap-2 text-sm lg:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <polyline points="16,6 12,2 8,6"></polyline>
                            <line x1="12" x2="12" y1="2" y2="15"></line>
                        </svg>
                        <?= Fastlang::__('share') ?>
                    </button>
                    <button class="blog-like-btn border-2 border-white text-white px-4 lg:px-6 py-2 lg:py-3 rounded-lg font-semibold hover:bg-white hover:text-red-500 transition-all duration-300 flex items-center justify-center gap-2 text-sm lg:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7z"></path>
                        </svg>
                        <span class="like-count"><?= format_views($blog['likes'] ?? 0) ?></span>
                    </button>
                    <button class="blog-bookmark-btn border-2 border-white text-white px-4 lg:px-6 py-2 lg:py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-900 transition-all duration-300 flex items-center justify-center gap-2 text-sm lg:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path>
                        </svg>
                        <?= Fastlang::__('bookmark') ?>
                    </button>
                </div>
            </div>

            <!-- Image Column -->
            <div class="order-1 lg:order-2 relative">
                <div class="relative rounded-xl lg:rounded-2xl overflow-hidden shadow-xl lg:shadow-2xl transform hover:scale-105 transition-transform duration-700">
                    <?php if (!empty($blog['thumb_url'])): ?>
                        <?= _img(
                            theme_assets(get_image_full($blog['thumb_url'])),
                            $blog['title'],
                            true,
                            'w-full h-64 sm:h-80 lg:h-96 object-cover'
                        ) ?>
                    <?php else: ?>
                        <div class="w-full h-64 sm:h-80 lg:h-96 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-50 lg:w-24 lg:h-24">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <!-- Overlay with play button if video type -->
                    <!-- <?php if ($blog['type'] === 'video'): ?>
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                            <button class="blog-play-video bg-white bg-opacity-20 backdrop-blur-sm rounded-full p-4 lg:p-6 hover:bg-opacity-30 transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white lg:w-8 lg:h-8 ml-1">
                                    <polygon points="5,3 19,12 5,21"></polygon>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?> -->
                </div>

                <!-- Floating Stats - Hidden on mobile, shown on md screens and up -->
                <div class="hidden md:block absolute -bottom-4 -right-4 lg:-bottom-6 lg:-right-6 bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-xl">
                    <div class="text-center">
                        <div class="text-lg lg:text-2xl font-bold text-gray-900"><?= $blog['formatted_views'] ?? number_format($blog['views'] ?? 0) ?></div>
                        <div class="text-xs lg:text-sm text-gray-500"><?= Fastlang::__('total_views') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
