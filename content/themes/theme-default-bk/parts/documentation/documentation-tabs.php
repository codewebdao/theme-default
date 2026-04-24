<?php

/**
 * Documentation tabs – Mobile: tất cả nội dung xếp dọc; Desktop: sidebar tabs + panel (PHP + vanilla JS).
 *
 * @var array<int, mixed> $usage_guide_posts Chỉ do page-usage-guide.php truyền vào (get_posts type usage_guide).
 */

use System\Libraries\Render\View;

$usage_guide_posts = (isset($usage_guide_posts) && is_array($usage_guide_posts)) ? $usage_guide_posts : [];

// Generate tabs from usage guide posts data
$doc_tabs = [];
if (!empty($usage_guide_posts)) {
    $available_icons = ['doc', 'install', 'workflow', 'help'];
    foreach ($usage_guide_posts as $index => $post) {
        $tab_id = 'usage_guide_' . $index;
        $icon_index = $index % count($available_icons);
        $doc_tabs[$tab_id] = [
            'label_key' => html_entity_decode((string) ($post['title'] ?? 'Usage Guide ' . ($index + 1)), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'icon' => $available_icons[$icon_index],
            'post' => $post
        ];
    }
} else {
    // Fallback to default tabs if no data
    $doc_tabs = [
        'introduction'    => ['label_key' => 'doc.tab.introduction', 'icon' => 'doc'],
        'installation'    => ['label_key' => 'doc.tab.installation', 'icon' => 'install'],
        'workflows'       => ['label_key' => 'doc.tab.workflows', 'icon' => 'workflow'],
        'troubleshooting' => ['label_key' => 'doc.tab.troubleshooting', 'icon' => 'help'],
    ];
}
$search_url = base_url('search');
?>
<!-- Mobile: tất cả content, không tabs -->
<section class="doc-docs-section doc-usage-guide-mobile lg:hidden py-8 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute w-full h-[1454px] rounded-full bg-[#2377FD80]/10 blur-[250px] -translate-x-1/2" style="left: 100%; top: 0;"></div>
        <div class="absolute w-full h-[772px] rounded-full bg-[#63ECFF80]/15 blur-[200px] -translate-x-1/2" style="left: 0; top: 100px;"></div>
        <div class="absolute w-full h-[1366px] rounded-full bg-[#63ECFF33]/0.9 blur-[100px] -translate-x-1/2" style="left: 50%; top: 0;"></div>
    </div>
    <div class="container mx-auto">
        <div class="rounded-none ">
            <!-- Mobile Search -->
            <div class="mb-8">
                <form action="<?php echo e($search_url); ?>" method="GET" class="flex-1 rounded-[12px]" id="mobile-search-form">
                    <div class="relative">
                        <svg width="28" height="37" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-emerald-700 w-[28px] h-[27px]" viewBox="0 0 28 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M23 21L18.66 16.66M21 11C21 15.4183 17.4183 19 13 19C8.58172 19 5 15.4183 5 11C5 6.58172 8.58172 3 13 3C17.4183 3 21 6.58172 21 11Z" stroke="url(#paint0_linear_doc)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <defs>
                                <linearGradient id="paint0_linear_doc" x1="5.57692" y1="18" x2="23.0833" y2="17.7463" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="var(--home-accent)" />
                                    <stop offset="0.576923" stop-color="var(--home-primary)" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <input type="text" name="q" id="mobile-search-input" placeholder="<?php echo e(__('doc.search_placeholder')); ?>" class="input w-full h-[22px] p-7 bg-white/90 focus:border-white text-gray-900">
                    </div>
                </form>
            </div>

            <div class="space-y-12">
                <?php if (!empty($usage_guide_posts)): ?>
                    <!-- Display dynamic usage guide posts -->
                    <?php foreach ($usage_guide_posts as $index => $post): ?>
                        <div class="doc-mobile-guide-post bg-white rounded-lg">
                           <h1 class="font-space text-3xl sm:text-5xl md:text-[64px] lg:text-[48px] xl:text-[64px] font-medium sm:font-bold text-home-heading text-start mb-10 sm:mb-12 leading-tight sm:leading-[60px] md:leading-[80px]">
                                    <?php echo e(html_entity_decode((string) ($post['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
                                </h1>
                                <p class="text-base sm:text-lg md:text-xl text-gray-600 -center lg:text-left mb-6 md:mb-12 leading-relaxed font-plus">
                                    <?php echo e(html_entity_decode((string) ($post['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
                                </p>

                            <?php if (!empty($post['content'])): ?>
                                <div class="prose prose-gray max-w-none mb-4">
                                    <?php echo $post['content']; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($post['excerpt'])): ?>
                                <p class="text-gray-600 italic mb-4"><?php echo e($post['excerpt']); ?></p>
                            <?php endif; ?>




                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback to hardcoded content when no data -->
                    <?php echo View::include('parts/documentation/tab-content-introduction'); ?>
                    <?php echo View::include('parts/documentation/tab-content-installation'); ?>
                    <?php echo View::include('parts/documentation/tab-content-workflows'); ?>
                    <?php echo View::include('parts/documentation/tab-content-troubleshooting'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Desktop: sidebar tabs + panels (vanilla JS) -->
<section class="doc-docs-section hidden lg:block py-12 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute w-full h-[1454px] rounded-full bg-[#2377FD80]/10 blur-[250px] -translate-x-1/2" style="left: 100%; top: 0;"></div>
        <div class="absolute w-full h-[772px] rounded-full bg-[#63ECFF80]/15 blur-[200px] -translate-x-1/2" style="left: 0; top: 100px;"></div>
        <div class="absolute w-full h-[1366px] rounded-full bg-[#63ECFF33]/0.9 blur-[100px] -translate-x-1/2" style="left: 50%; top: 0;"></div>
    </div>
    <div class="container mx-auto relative z-10">
        <div class="doc-tabs flex flex-row gap-0 p-12 bg-white rounded-[48px] shadow-sm">
            <aside class="w-[288px] flex-shrink-0">
                <div class="sticky top-24 flex flex-col items-start gap-6 px-6 pt-6 pb-0 w-[288px]">
                    <h2 class="text-lg font-plus font-semibold text-home-body leading-[27px]"><?php echo e(__('doc.sidebar_title')); ?></h2>
                    <nav class="flex flex-col items-start gap-3 w-full">
                        <?php foreach ($doc_tabs as $tab_id => $tab): ?>
                            <button type="button" data-tab="<?php echo e($tab_id); ?>"
                                class="doc-tab-btn flex items-center gap-3 p-4 self-stretch rounded-home-md font-medium transition-colors w-full text-left <?php echo $tab_id === array_key_first($doc_tabs) ? ' active' : ''; ?>">
                                <?php if ($tab['icon'] === 'doc'): ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                <?php elseif ($tab['icon'] === 'install'): ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 7V21M12 7C12 5.93913 11.5786 4.92172 10.8284 4.17157C10.0783 3.42143 9.06087 3 8 3H3C2.73478 3 2.48043 3.10536 2.29289 3.29289C2.10536 3.48043 2 3.73478 2 4V17C2 17.2652 2.10536 17.5196 2.29289 17.7071C2.48043 17.8946 2.73478 18 3 18H9C9.79565 18 10.5587 18.3161 11.1213 18.8787C11.6839 19.4413 12 20.2044 12 21M12 7C12 5.93913 12.4214 4.92172 13.1716 4.17157C13.9217 3.42143 14.9391 3 16 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V17C22 17.2652 21.8946 17.5196 21.7071 17.7071C21.5196 17.8946 21.2652 18 21 18H15C14.2044 18 13.4413 18.3161 12.8787 18.8787C12.3161 19.4413 12 20.2044 12 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                <?php elseif ($tab['icon'] === 'workflow'): ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 9H21M9 21V9M5 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                <?php else: ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.9999 9.00001V13M11.9999 17H12.0099M21.7299 18L13.7299 4.00001C13.5555 3.69222 13.3025 3.4362 12.9969 3.25808C12.6912 3.07996 12.3437 2.98611 11.9899 2.98611C11.6361 2.98611 11.2887 3.07996 10.983 3.25808C10.6773 3.4362 10.4244 3.69222 10.2499 4.00001L2.24993 18C2.07361 18.3054 1.98116 18.6519 1.98194 19.0045C1.98272 19.3571 2.07671 19.7033 2.25438 20.0078C2.43204 20.3124 2.68708 20.5646 2.99362 20.7388C3.30017 20.9131 3.64734 21.0032 3.99993 21H19.9999C20.3508 20.9997 20.6955 20.907 20.9992 20.7313C21.303 20.5556 21.5551 20.3031 21.7304 19.9991C21.9057 19.6951 21.998 19.3504 21.9979 18.9995C21.9978 18.6486 21.9054 18.3039 21.7299 18Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                <?php endif; ?>
                                <span class="font-plus"><?php echo isset($tab['post']) ? e($tab['label_key']) : e(__($tab['label_key'])); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </aside>
            <div class="hidden xl:flex items-stretch flex-shrink-0 self-stretch xl:px-[80px]">
                <div class="w-2 bg-home-surface-light"></div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="mb-12">
                    <form action="<?php echo e($search_url); ?>" method="GET" class="flex-1 rounded-[12px]" id="search-form">
                        <div class="relative">
                            <svg width="28" height="37" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-emerald-700 w-[28px] h-[27px]" viewBox="0 0 28 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g filter="url(#filter0_dddd_desktop)">
                                    <path d="M23 21L18.66 16.66M21 11C21 15.4183 17.4183 19 13 19C8.58172 19 5 15.4183 5 11C5 6.58172 8.58172 3 13 3C17.4183 3 21 6.58172 21 11Z" stroke="url(#paint0_linear_desktop)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                                <defs>
                                    <filter id="filter0_dddd_desktop" x="-2" y="0" width="32" height="39" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                                        <feOffset dy="1"></feOffset>
                                        <feGaussianBlur stdDeviation="0.5"></feGaussianBlur>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0.0901961 0 0 0 0 0.74902 0 0 0 0 0.627451 0 0 0 0.1 0"></feColorMatrix>
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"></feBlend>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"></feBlend>
                                    </filter>
                                    <linearGradient id="paint0_linear_desktop" x1="5.57692" y1="18" x2="23.0833" y2="17.7463" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="var(--home-accent)"></stop>
                                        <stop offset="0.576923" stop-color="var(--home-primary)"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <input type="text" name="q" id="search-input" placeholder="<?php echo e(__('doc.search_placeholder')); ?>" class="input w-full h-[22px] p-7 bg-white/90 focus:border-white text-gray-900">
                        </div>
                    </form>
                </div>
                <?php foreach ($doc_tabs as $tab_id => $tab): ?>
                    <div data-tab-content="<?php echo e($tab_id); ?>" class="doc-tab-content<?php echo $tab_id === array_key_first($doc_tabs) ? ' active' : ''; ?>">
                        <?php if (isset($tab['post'])): ?>
                            <!-- Display dynamic post content -->
                            <div class="bg-white rounded-lg p-6 shadow-sm">
                                <h1 class="font-space text-3xl sm:text-5xl md:text-[64px] lg:text-[48px] xl:text-[64px] font-medium sm:font-bold text-home-heading text-start mb-10 sm:mb-12 leading-tight sm:leading-[60px] md:leading-[80px]">
                                    <?php echo e(html_entity_decode((string) ($tab['post']['title'] ?? 'Documentation'), ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
                                </h1>
                                <p class="text-base sm:text-lg md:text-xl text-gray-600 text-center lg:text-left mb-6 md:mb-12 leading-relaxed font-plus">
                                    <?php echo e(html_entity_decode((string) ($tab['post']['description'] ?? 'Documentation'), ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
                                </p>
                                <?php if (!empty($tab['post']['content'])): ?>
                                    <div class="prose prose-gray max-w-none mb-4">
                                        <?php echo $tab['post']['content']; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($tab['post']['excerpt'])): ?>
                                    <p class="text-gray-600 italic mb-4"><?php echo e($tab['post']['excerpt']); ?></p>
                                <?php endif; ?>




                            </div>
                        <?php else: ?>
                            <!-- Fallback to hardcoded content -->
                            <?php echo View::include('parts/documentation/tab-content-' . $tab_id); ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<style>
    .doc-tabs .doc-tab-btn {
        color: var(--home-body);
    }

    .doc-tabs .doc-tab-btn:hover {
        background: #f3f4f6;
    }

    .doc-tabs .doc-tab-btn.active {
        background: var(--home-surface-light);
        color: var(--home-primary);
    }

    .doc-tabs .doc-tab-content {
        display: none;
    }

    .doc-tabs .doc-tab-content.active {
        display: block;
    }
</style>
<script>
    (function() {
        // Tab functionality
        var wrap = document.querySelector('.doc-tabs');
        if (!wrap) return;
        var btns = wrap.querySelectorAll('.doc-tab-btn');
        var panels = wrap.querySelectorAll('.doc-tab-content');

        function show(id) {
            btns.forEach(function(b) {
                b.classList.toggle('active', b.getAttribute('data-tab') === id);
            });
            panels.forEach(function(p) {
                p.classList.toggle('active', p.getAttribute('data-tab-content') === id);
            });
        }
        btns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                show(btn.getAttribute('data-tab'));
            });
        });

        // Search functionality with debounce - filter actual content
        var desktopSearchInput = document.getElementById('search-input');
        var mobileSearchInput = document.getElementById('mobile-search-input');
        var searchTimeout;

        // Usage guide posts data from PHP
        var usageGuidePosts = <?php echo json_encode($usage_guide_posts); ?>;

        function setupSearch(input) {
            if (!input) return;

            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                var query = this.value.toLowerCase().trim();
                // Debounce: wait 500ms after user stops typing
                searchTimeout = setTimeout(function() {
                    filterContent(query);
                }, 500);
            });
        }

        // Setup search for both desktop and mobile
        setupSearch(desktopSearchInput);
        setupSearch(mobileSearchInput);

        function filterContent(query) {
            if (!usageGuidePosts.length) {
                return;
            }
            // Filter desktop tab panels (chỉ khi có bài CMS; tab tĩnh introduction|… không có usage_guide_)
            var desktopTabs = document.querySelectorAll('.doc-docs-section.hidden .doc-tab-content');

            desktopTabs.forEach(function(tab) {
                var tabId = tab.getAttribute('data-tab-content');
                if (tabId) {
                    var postIndex = tabId.replace('usage_guide_', '');
                    var post = usageGuidePosts[postIndex];

                    if (query.length < 2) {
                        tab.style.display = ''; // Show all tabs
                    } else if (post && matchesQuery(post, query)) {
                        tab.style.display = ''; // Show matching tab
                    } else {
                        tab.style.display = 'none'; // Hide non-matching tab
                    }
                }
            });

            // Mobile: khối bài CMS (class khớp markup thực tế)
            var mobilePosts = document.querySelectorAll('.doc-usage-guide-mobile .doc-mobile-guide-post');
            mobilePosts.forEach(function(postElement, index) {
                var post = usageGuidePosts[index];

                if (query.length < 2) {
                    postElement.style.display = ''; // Show all posts
                } else if (post && matchesQuery(post, query)) {
                    postElement.style.display = ''; // Show matching post
                } else {
                    postElement.style.display = 'none'; // Hide non-matching post
                }
            });

            // Show first visible tab if current tab is hidden
            if (query.length >= 2) {
                var visibleTabs = document.querySelectorAll('.doc-docs-section.hidden .doc-tab-content:not([style*="display: none"])');
                var activeTab = document.querySelector('.doc-docs-section.hidden .doc-tab-content.active');

                if (activeTab && activeTab.style.display === 'none' && visibleTabs.length > 0) {
                    show(visibleTabs[0].getAttribute('data-tab-content'));
                }
            }
        }

        function matchesQuery(post, query) {
            var title = (post.title || '').toLowerCase();
            var content = (post.content || '').toLowerCase();
            var excerpt = (post.excerpt || '').toLowerCase();

            return title.includes(query) || content.includes(query) || excerpt.includes(query);
        }
    })();
</script>