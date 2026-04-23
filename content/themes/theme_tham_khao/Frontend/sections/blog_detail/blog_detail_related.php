<?php

use App\Libraries\Fastlang;

if (!empty($related_blogs) && count($related_blogs) > 0): ?>
    <!-- Related Blogs Section -->
    <section class="bg-gradient-to-r from-slate-100 to-blue-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-medium mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.582a.5.5 0 0 1 0 .962L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                        <path d="M20 3v4"></path>
                        <path d="M22 5h-4"></path>
                        <path d="M4 17v2"></path>
                        <path d="M5 18H3"></path>
                    </svg>
                    <?= Fastlang::__('more_content', 'Blog') ?>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    <?= Fastlang::__('related_blogs', 'Blog') ?>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    <?= Fastlang::__('discover_more_interesting_content', 'Blog') ?>
                </p>
            </div>

            <!-- Related Blogs Grid -->
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($related_blogs as $index => $relatedBlog): ?>

                    <article class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">

                        <!-- Blog Image -->
                        <div class="relative h-48 overflow-hidden">
                            <?php if (!empty($relatedBlog['thumb_url'])): ?>
                                <?= _img(
                                    theme_assets(get_image_full($relatedBlog['thumb_url'])),
                                    $relatedBlog['title'],
                                    true,
                                    'w-full h-full object-cover group-hover:scale-110 transition-transform duration-700'
                                ) ?>
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-70">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>

                            <!-- Reading Time Badge -->
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 inline">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12,6 12,12 16,14"></polyline>
                                </svg>
                                <?= $relatedBlog['estimated_read_time'] ?? ceil(str_word_count(strip_tags($relatedBlog['content'] ?? '')) / 200) ?> min
                            </div>

                            <!-- Type Badge -->
                            <!-- <?php if (!empty($relatedBlog['type'])): ?>
                                <div class="absolute top-4 left-4 bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium uppercase">
                                    <?= htmlspecialchars($relatedBlog['type']) ?>
                                </div>
                            <?php endif; ?> -->
                        </div>

                        <!-- Blog Content -->
                        <div class="p-6">

                            <!-- Blog Meta -->
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8 2v4"></path>
                                        <path d="M16 2v4"></path>
                                        <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                        <path d="M3 10h18"></path>
                                    </svg>
                                    <span><?= date('M d, Y', strtotime($relatedBlog['created_at'])) ?></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span><?= formatViews($relatedBlog['views']) ?></span>
                                </div>
                            </div>

                            <!-- Blog Title -->
                            <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                <a href="<?= base_url('blogs/' . $relatedBlog['slug'], APP_LANG) ?>">
                                    <?= htmlspecialchars($relatedBlog['title']) ?>
                                </a>
                            </h3>

                            <!-- Blog Excerpt -->
                            <p class="text-gray-600 line-clamp-3 mb-4">
                                <?= htmlspecialchars(substr(strip_tags($relatedBlog['content'] ?? ''), 0, 120) . '...') ?>
                            </p>

                            <!-- Blog Footer -->
                            <div class="flex items-center justify-between">

                                <!-- Tags -->
                                <?php if (!empty($relatedBlog['tags'])):
                                    $tags = array_slice(explode(',', $relatedBlog['tags']), 0, 2);
                                ?>
                                    <div class="flex gap-2">
                                        <?php foreach ($tags as $tag): ?>
                                            <a href="<?= base_url('blogs?tag=' . urlencode(trim($tag)), APP_LANG) ?>"
                                                class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs hover:bg-blue-200 transition-colors">
                                                #<?= trim(htmlspecialchars($tag)) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>

                                <!-- Read More Button -->
                                <a href="<?= base_url('blogs/' . $relatedBlog['slug'], APP_LANG) ?>"
                                    class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-medium text-sm group-hover:gap-3 transition-all">
                                    <?= Fastlang::__('read_more') ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>

                <?php endforeach; ?>
            </div>

            <!-- View All Blogs Button -->
            <div class="text-center mt-12">
                <a href="<?= base_url('blogs', APP_LANG) ?>"
                    class="inline-flex items-center gap-3 bg-blue-500 text-white px-8 py-4 rounded-xl font-semibold hover:bg-blue-600 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                        <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                    </svg>
                    <?= Fastlang::__('explore_all_blogs') ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>
