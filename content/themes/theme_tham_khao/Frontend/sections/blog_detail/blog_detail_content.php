<?php

use App\Libraries\Fastlang;
?>
<!-- Main Content Area -->
<div class="lg:col-span-3">
    <article class="bg-white rounded-2xl shadow-xl overflow-hidden">

        <!-- Article Header -->
        <div class="p-8 border-b border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <!-- Author Avatar -->
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                        <?= _img(theme_assets(get_image_full($author['avatar'] ?? json_encode(['path' => 'images/avatar.png']))), $author['fullname'], true, 'w-full h-full object-cover') ?>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900"><?= $author['fullname'] ?></div>
                        <div class="text-sm text-gray-500"><?= date('F d, Y', strtotime($blog['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Social Share -->
                <div class="flex items-center gap-2">
                    <button class="share-facebook p-2 text-blue-600 hover:text-black hover:bg-blue-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </button>
                    <button class="share-twitter p-2 text-sky-500 hover:text-black hover:bg-sky-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                        </svg>
                    </button>
                    <button class="share-linkedin p-2 text-blue-700 hover:text-black hover:bg-blue-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </button>
                    <button class="copy-link p-2 text-gray-600 hover:text-black hover:bg-gray-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Article Content -->
        <div class="p-8">
            <!-- Featured Content -->
            <?php if (!empty($blog['feature'])): ?>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-6 mb-8 rounded-r-lg">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500 mr-2 inline">
                            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                        </svg>
                        <?= Fastlang::__('featured_highlights') ?>
                    </h3>
                    <div class="text-blue-800 prose prose-blue">
                        <?= _img(theme_assets(get_image_full($blog['feature'])), $blog['title'], true, 'w-full h-full object-cover') ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Table of Contents (if content is long) -->
            <?php if (strlen(strip_tags($blog['content'])) > 2000): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500">
                            <line x1="8" x2="21" y1="6" y2="6"></line>
                            <line x1="8" x2="21" y1="12" y2="12"></line>
                            <line x1="8" x2="21" y1="18" y2="18"></line>
                            <line x1="3" x2="3.01" y1="6" y2="6"></line>
                            <line x1="3" x2="3.01" y1="12" y2="12"></line>
                            <line x1="3" x2="3.01" y1="18" y2="18"></line>
                        </svg>
                        <?= Fastlang::__('table_of_contents') ?>
                    </h3>
                    <div id="table-of-contents" class="space-y-2">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Article Content -->
            <div class="prose prose-lg prose-blue max-w-none">
                <div class="blog-content" id="blog-content">
                    <?= $blog['content'] ?>
                </div>
            </div>

            <!-- Article Footer -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <!-- Tags -->
                <?php if (!empty($blog['tags'])):
                    $tags_array = explode(',', $blog['tags']);
                ?>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3"><?= Fastlang::__('tags') ?></h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tags_array as $tag): ?>
                                <a href="<?= base_url('blogs?tag=' . urlencode(trim($tag)), APP_LANG) ?>"
                                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                                    #<?= trim(htmlspecialchars($tag)) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Article Actions -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-gray-100">
                    <div class="flex items-center gap-4">
                        <!-- Like Button -->
                        <button class="blog-like-btn flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7z"></path>
                            </svg>
                            <span class="like-count"><?= format_views($blog['likes'] ?? 0) ?></span>
                        </button>

                        <!-- Comment Button -->
                        <button class="blog-comment-btn flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                            </svg>
                            <span><?= Fastlang::__('comment') ?></span>
                        </button>
                    </div>

                    <!-- Share Button -->
                    <button class="blog-share-btn-footer bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <polyline points="16,6 12,2 8,6"></polyline>
                            <line x1="12" x2="12" y1="2" y2="15"></line>
                        </svg>
                        <?= Fastlang::__('share_article') ?>
                    </button>
                </div>
            </div>
        </div>
    </article>

    <!-- Comments Section -->
    <div class="mt-12">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500">
                    <path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"></path>
                    <path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"></path>
                </svg>
                <?= Fastlang::__('comments') ?>
            </h3>

            <!-- Comment Form -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                <h4 class="font-semibold text-gray-900 mb-4"><?= Fastlang::__('leave_comment') ?></h4>
                <?php do_shortcode('feature-rating') ?>
                <form class="space-y-4" id="comment-form">
                    <div class="grid md:grid-cols-2 gap-4">
                        <input type="text" placeholder="<?= Fastlang::__('your_name') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <input type="email" placeholder="<?= Fastlang::__('your_email') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <textarea placeholder="<?= Fastlang::__('your_comment') ?>" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    <button type="submit"
                        class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"></path>
                            <path d="M6 12h16"></path>
                        </svg>
                        <?= Fastlang::__('post_comment') ?>
                    </button>
                </form>
            </div>

            <!-- Comments List -->
            <div class="space-y-6">
                <!-- Sample Comment -->
                <div class="border border-gray-200 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                            J
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h5 class="font-semibold text-gray-900">John Doe</h5>
                                <span class="text-sm text-gray-500">2 hours ago</span>
                            </div>
                            <p class="text-gray-700 mb-3">This is a great article! Very informative and well-written. Thanks for sharing.</p>
                            <div class="flex items-center gap-4">
                                <button class="text-sm text-gray-500 hover:text-blue-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 10v12"></path>
                                        <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path>
                                    </svg> Like (5)
                                </button>
                                <button class="text-sm text-gray-500 hover:text-blue-500">Reply</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
