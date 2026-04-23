<?php

use App\Libraries\Fastlang;
?>
<!-- Sidebar -->
<div class="lg:col-span-1" data-blog-id="<?= $blog['id'] ?? $blog['id_main'] ?? 'unknown' ?>">
    <div class="sticky top-28 space-y-8">

        <!-- Blog Stats -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500">
                    <path d="M3 3v18h18"></path>
                    <path d="m19 9-5 5-4-4-3 3"></path>
                </svg>
                <?= Fastlang::__('blog_stats') ?>
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600"><?= Fastlang::__('views') ?></span>
                    <span class="font-semibold text-blue-600"><?= format_views($blog['views']) ?></span>
                </div>
                <?php
                if (!empty($blog['rating_count'])): ?>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><?= Fastlang::__('ratings') ?></span>
                        <span class="font-semibold text-yellow-600"><?= $blog['rating_count'] ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600"><?= Fastlang::__('reading_time') ?></span>
                    <span class="font-semibold text-green-600"><?= $blog['estimated_read_time'] ?? ceil(str_word_count(strip_tags($blog['content'] ?? '')) / 200) ?> min</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600"><?= Fastlang::__('published') ?></span>
                    <span class="font-semibold text-purple-600"><?= date('M d, Y', strtotime($blog['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500">
                    <polygon points="13,2 3,14 12,14 11,22 21,10 12,10"></polygon>
                </svg>
                <?= Fastlang::__('quick_actions') ?>
            </h3>
            <div class="space-y-3">
                <button class="quick-action-save w-full text-left p-3 rounded-lg hover:bg-blue-50 transition-colors group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500 group-hover:text-blue-600">
                            <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path>
                        </svg>
                        <span class="text-gray-700 group-hover:text-blue-600"><?= Fastlang::__('save_for_later') ?></span>
                    </div>
                </button>
                <button class="quick-action-share w-full text-left p-3 rounded-lg hover:bg-green-50 transition-colors group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 group-hover:text-green-600">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <polyline points="16,6 12,2 8,6"></polyline>
                            <line x1="12" x2="12" y1="2" y2="15"></line>
                        </svg>
                        <span class="text-gray-700 group-hover:text-green-600"><?= Fastlang::__('share_blog') ?></span>
                    </div>
                </button>
                <button class="quick-action-like w-full text-left p-3 rounded-lg hover:bg-red-50 transition-colors group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500 group-hover:text-red-600">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7z"></path>
                        </svg>
                        <span class="text-gray-700 group-hover:text-red-600"><?= Fastlang::__('like_blog') ?></span>
                        <span class="like-count text-xs text-gray-500"><?= format_views($blog['likes']) ?></span>
                    </div>
                </button>
                <button class="quick-action-print w-full text-left p-3 rounded-lg hover:bg-purple-50 transition-colors group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-500 group-hover:text-purple-600">
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                            <rect x="6" y="14" width="12" height="8" rx="1"></rect>
                        </svg>
                        <span class="text-gray-700 group-hover:text-purple-600"><?= Fastlang::__('print_blog') ?></span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Blog Navigation -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-500">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="16.24,7.76 14.12,14.12 7.76,16.24 9.88,9.88"></polygon>
                </svg>
                <?= Fastlang::__('blog_navigation') ?>
            </h3>
            <div class="space-y-3">
                <a href="<?= base_url('blogs', APP_LANG) ?>"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 group-hover:text-blue-500">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    <span class="text-gray-700 group-hover:text-blue-600"><?= Fastlang::__('back_to_blogs') ?></span>
                </a>
                <!-- <button class="w-full text-left p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 group-hover:text-blue-500">
                            <path d="m18 15-6-6-6 6"></path>
                        </svg>
                        <span class="text-gray-700 group-hover:text-blue-600"><?= Fastlang::__('previous_blog') ?></span>
                    </div>
                </button>
                <button class="w-full text-left p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 group-hover:text-blue-500">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                        <span class="text-gray-700 group-hover:text-blue-600"><?= Fastlang::__('next_blog') ?></span>
                    </div>
                </button> -->
            </div>
        </div>

        <!-- Recent Blogs -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12,6 12,12 16,14"></polyline>
                </svg>
                <?= Fastlang::__('recent_blogs') ?>
            </h3>
            <div class="space-y-4">
                <?php foreach ($recent_blogs as $recent_blog): ?>
                    <div class="group">
                        <a href="<?= base_url('blogs/' . $recent_blog['slug'], APP_LANG) ?>" class="flex gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-16 h-16 rounded-lg flex-shrink-0 flex items-center justify-center">
                                <?= _img(
                                    theme_assets(get_image_full($recent_blog['thumb_url'])),
                                    $recent_blog['title'],
                                    true,
                                    'w-full h-full object-cover'
                                ) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 group-hover:text-blue-600 line-clamp-2 text-sm">
                                    <?= html_entity_decode($recent_blog['title']) ?>
                                </h4>
                                <p class="text-xs text-gray-500 mt-1"><?= date('M d, Y', strtotime($recent_blog['created_at'])) ?></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-400"><?= format_views($recent_blog['views']) ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="<?= base_url('blogs', APP_LANG) ?>"
                    class="text-sm text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                    <?= Fastlang::__('view_all_blogs') ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Newsletter Signup -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl text-white p-6">
            <h3 class="text-lg font-semibold mb-2 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-300">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <?= Fastlang::__('stay_updated') ?>
            </h3>
            <p class="text-blue-100 text-sm mb-4">
                <?= Fastlang::__('subscribe_newsletter_desc') ?>
            </p>
            <form class="space-y-3">
                <input type="email"
                    placeholder="<?= Fastlang::__('enter_email') ?>"
                    class="w-full px-3 py-2 rounded-lg text-gray-900 focus:ring-2 focus:ring-white/50 focus:outline-none">
                <button type="submit"
                    class="w-full bg-white text-blue-600 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    <?= Fastlang::__('subscribe') ?>
                </button>
            </form>
        </div>

    </div>
</div>
