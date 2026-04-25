<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Blog', APP_LANG);
\App\Libraries\Fastlang::load('Home', APP_LANG);
require_once __DIR__ . '/_blog_category_badge.php';
require_once __DIR__ . '/_blog_read_time.php';
$blog_category_default_style = blog_category_badge_default();

$blogsSrcPage = $blogsData ?? $blogs ?? [];
$blog_ts = static function ($row) {
    $ca = $row['created_at'] ?? null;
    if ($ca === null || $ca === '') {
        return 0;
    }
    if (is_numeric($ca)) {
        return (int) $ca;
    }
    $t = strtotime((string) $ca);
    return $t !== false ? $t : 0;
};
$blog_parse_img = static function ($val) {
    if ($val === null || $val === '') {
        return null;
    }
    if (is_array($val)) {
        return $val;
    }
    if (is_string($val) && strpos(trim($val), '{') === 0) {
        $decoded = json_decode($val, true);
        return is_array($decoded) ? $decoded : $val;
    }
    return $val;
};
/** Eloquent/model: (array)$obj không có name/slug — cần toArray() hoặc json */
$blog_term_row_to_array = static function ($item): array {
    if ($item === null) {
        return [];
    }
    if (is_array($item)) {
        return $item;
    }
    if (is_object($item)) {
        if (method_exists($item, 'toArray')) {
            return (array) $item->toArray();
        }
        $j = json_decode(json_encode($item), true);

        return is_array($j) ? $j : [];
    }

    return [];
};
$blog_categories_list_normalize = static function ($cats): array {
    if ($cats === null || $cats === '') {
        return [];
    }
    if (is_string($cats)) {
        $catsDec = json_decode($cats, true);
        $cats = is_array($catsDec) ? $catsDec : [];
    }
    if (is_object($cats)) {
        if (method_exists($cats, 'toArray')) {
            $cats = $cats->toArray();
        } elseif ($cats instanceof \Traversable) {
            $cats = iterator_to_array($cats, false);
        } else {
            $cats = [];
        }
    }
    if (!is_array($cats)) {
        return [];
    }

    return array_values($cats);
};

$categories = $categories ?? [];

$blog_build_sorted = function (array $rows) use (
    $categories,
    $blog_parse_img,
    $blog_categories_list_normalize,
    $blog_term_row_to_array,
    $blog_ts
) {
    $blog_posts_raw = [];
    foreach ($rows as $row) {
        $featureImg = $blog_parse_img($row['feature'] ?? null);
        $authorAvatar = $blog_parse_img($row['avatar'] ?? null);
        $cats = $blog_categories_list_normalize($row['categories'] ?? []);
        $firstRaw = $cats[0] ?? null;
        $firstCat = $blog_term_row_to_array($firstRaw);
        $categoryName = (string) ($firstCat['name'] ?? $firstCat['title'] ?? $firstCat['label'] ?? '');
        $categorySlug = (string) ($firstCat['slug'] ?? $firstCat['term_slug'] ?? '');
        $categoryId = (int) ($firstCat['id'] ?? $firstCat['term_id'] ?? 0);
        $idMain = (int) ($firstCat['id_main'] ?? 0);
        if (($categoryName === '' || $categorySlug === '') && ($categoryId > 0 || $idMain > 0)) {
            foreach ($categories as $c) {
                $ca = $blog_term_row_to_array($c);
                $cid = (int) ($ca['id'] ?? 0);
                $cm = (int) ($ca['id_main'] ?? 0);
                $match = ($categoryId > 0 && $cid === $categoryId) || ($idMain > 0 && ($cm === $idMain || $cid === $idMain));
                if ($match) {
                    if ($categoryName === '') {
                        $categoryName = (string) ($ca['name'] ?? $ca['title'] ?? $ca['label'] ?? '');
                    }
                    if ($categorySlug === '') {
                        $categorySlug = (string) ($ca['slug'] ?? $ca['term_slug'] ?? '');
                    }
                    break;
                }
            }
        }
        $slug = $row['slug'] ?? '';
        $categoryStyle = blog_category_badge_classes((string) $categoryName, (string) $categorySlug);
        $blog_posts_raw[] = [
            'title' => $row['title'] ?? '',
            'description_title' => (string) ($row['description_title'] ?? '' ?? ''),
            'username' => $row['username'] ?? '',
            'feature' => $featureImg,
            'avatar' => $authorAvatar,
            'category_name' => $categoryName,
            'category_style' => $categoryStyle,
            'category_slug' => $categorySlug,
            'category_id' => $categoryId,
            'created_at' => $row['created_at'] ?? '',
            'url' => $slug ? (string) link_posts($slug, 'blog', defined('APP_LANG') ? APP_LANG : '') : '#',
            '_ts' => $blog_ts($row),
        ];
    }
    usort($blog_posts_raw, static function ($a, $b) {
        return ($b['_ts'] ?? 0) <=> ($a['_ts'] ?? 0);
    });
    $out = [];
    foreach ($blog_posts_raw as $p) {
        unset($p['_ts']);
        $out[] = $p;
    }

    return $out;
};

$blog_sorted = $blog_build_sorted(is_array($blogsSrcPage) ? $blogsSrcPage : []);

$blog_search_form_action = isset($blog_search_form_action) ? trim((string) $blog_search_form_action) : '';
$blog_search_input_value = isset($blog_search_input_value)
    ? (string) $blog_search_input_value
    : (isset($_GET['q']) ? (string) $_GET['q'] : '');
?>
<section class="relative overflow-hidden min-h-screen bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    <!-- Trang trí hai bên: giới hạn theo viewport để ảnh phải không bị đẩy ra ngoài (overflow-hidden) -->
    <div class="blog-hero-bg-deco pointer-events-none absolute inset-x-0 top-0 flex w-full max-w-full justify-between items-start gap-1 sm:gap-4">
        <?php
        $__bhW = [360, 480, 640, 800, 960];
        $__bhA = static fn(string $cls, string $sz) => [
            'alt' => '', 'class' => $cls, 'sizes' => $sz,
            'loading' => 'lazy', 'decoding' => 'async', 'mobile_webp_width' => 640, 'mobile_webp_bp' => 640,
        ];
        $__bhU = static fn(string $r) => function_exists('theme_assets') ? theme_assets($r) : '';
        echo (function_exists('cmsfullform_theme_responsive_webp_img')
            ? cmsfullform_theme_responsive_webp_img('images/backblog1.webp', $__bhW, $__bhA(
                'blog-hero-bg-deco__left h-auto object-cover object-left',
                '(max-width: 639px) 58vw, min(54vw, 560px)'
            )) : '') ?: '<img src="' . e($__bhU('images/backblog1.webp')) . '" alt="" class="blog-hero-bg-deco__left h-auto object-cover object-left" width="1047" height="665" loading="lazy" decoding="async" />';
        echo (function_exists('cmsfullform_theme_responsive_webp_img')
            ? cmsfullform_theme_responsive_webp_img('images/backblog2.webp', $__bhW, $__bhA(
                'blog-hero-bg-deco__right h-auto object-cover object-right',
                '(max-width: 639px) 52vw, min(48vw, 460px)'
            )) : '') ?: '<img src="' . e($__bhU('images/backblog2.webp')) . '" alt="" class="blog-hero-bg-deco__right h-auto object-cover object-right" loading="lazy" decoding="async" />';
        ?>
    </div>
    <div class="relative container mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <!-- Heading -->

        <div class="text-center mb-8 sm:mb-12">

            <h1
                class="text-4xl sm:text-5xl md:text-[64px] font-bold text-black text-center mb-3 sm:mb-4 lg:leading-[80px] font-space dark:text-gray-50">
                <?php echo e(__('listing_hero_line1')); ?>

                <br />
                <span class="bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent">
                    <?php echo e(__('listing_hero_gradient')); ?>
                </span>
            </h1>
            <p class="text-gray-600 text-base sm:text-lg md:text-xl max-w-4xl mx-auto px-4 sm:px-0 font-plus dark:text-gray-400">
                <?php echo e(__('listing_hero_subtitle')); ?>
            </p>
        </div>
        <!-- Search Form -->
        <div class="mb-8 sm:mb-12 flex justify-center sm:px-0">
            <form action="<?php echo e($blog_search_form_action); ?>" method="GET" class="w-full max-w-sm">
                <div class="relative">
                    <input type="text" name="q" id="blogSearchInput" value="<?php echo e($blog_search_input_value); ?>"
                        placeholder="<?php echo e(__('search_placeholder')); ?>"
                        class="w-full h-[48px] sm:h-[56px] pl-4 pr-10 sm:pr-12 bg-white border border-home-surface rounded-home-md focus:outline-none focus:ring-2 focus:ring-home-primary focus:border-transparent text-sm sm:text-base text-gray-900 placeholder-gray-500 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:shadow-none">
                    <button type="submit"
                        class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-home-primary transition-colors dark:text-gray-500 dark:hover:text-home-primary"
                        aria-label="<?php echo e(__('button_search')); ?>">
                        <svg width="20" height="20" class="sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g filter="url(#filter0_dddd_104_5323)">
                                <path
                                    d="M23 21L18.66 16.66M21 11C21 15.4183 17.4183 19 13 19C8.58172 19 5 15.4183 5 11C5 6.58172 8.58172 3 13 3C17.4183 3 21 6.58172 21 11Z"
                                    stroke="url(#paint0_linear_104_5323)" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </g>
                            <defs>
                                <filter id="filter0_dddd_104_5323" x="-2" y="0" width="32" height="39"
                                    filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="1" />
                                    <feGaussianBlur stdDeviation="0.5" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.0901961 0 0 0 0 0.74902 0 0 0 0 0.627451 0 0 0 0.1 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix"
                                        result="effect1_dropShadow_104_5323" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="3" />
                                    <feGaussianBlur stdDeviation="1.5" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.0901961 0 0 0 0 0.74902 0 0 0 0 0.627451 0 0 0 0.09 0" />
                                    <feBlend mode="normal" in2="effect1_dropShadow_104_5323"
                                        result="effect2_dropShadow_104_5323" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="6" />
                                    <feGaussianBlur stdDeviation="2" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.0901961 0 0 0 0 0.74902 0 0 0 0 0.627451 0 0 0 0.05 0" />
                                    <feBlend mode="normal" in2="effect2_dropShadow_104_5323"
                                        result="effect3_dropShadow_104_5323" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="11" />
                                    <feGaussianBlur stdDeviation="2" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.0901961 0 0 0 0 0.74902 0 0 0 0 0.627451 0 0 0 0.01 0" />
                                    <feBlend mode="normal" in2="effect3_dropShadow_104_5323"
                                        result="effect4_dropShadow_104_5323" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_104_5323"
                                        result="shape" />
                                </filter>
                                <linearGradient id="paint0_linear_104_5323" x1="5.57692" y1="18" x2="23.0833"
                                    y2="17.7463" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="var(--home-accent)" />
                                    <stop offset="0.576923" stop-color="var(--home-primary)" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div id="blog-tabs-section" class="mb-12">
            <div class="blog-tab-panel" data-blog-content="all-posts">
                <!-- Articles Grid -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 lg:grid-cols-3 lg:gap-8" role="list">
                    <?php foreach ($blog_sorted as $item): ?>
                        <?php echo \System\Libraries\Render\View::include('parts/blog/_blog_article_card', [
                            'item' => $item,
                            'blog_category_styles' => [],
                            'blog_category_default_style' => $blog_category_default_style,
                        ]); ?>
                    <?php endforeach; ?>
                    <?php
                    $__blog_empty_msg = isset($blog_empty_message) ? trim((string) $blog_empty_message) : '';
                    if ($__blog_empty_msg !== '' && count($blog_sorted) === 0): ?>
                        <p role="status" class="col-span-full py-12 text-center text-base text-gray-500 dark:text-gray-400 font-plus"><?php echo e($__blog_empty_msg); ?></p>
                    <?php endif; ?>
                    <p role="status" aria-live="polite"
                        class="blog-search-empty hidden text-gray-500 text-center col-span-full py-12 dark:text-gray-400"><?php echo e(__('search_no_results')); ?></p>
                </div>
                <!-- Pagination -->
                <div id="blog-pagination-wrap">
                    <?php
                    echo \System\Libraries\Render\View::include('parts/pagination/pagination', [
                        'base_url'      => $blog_pagination_base_url ?? base_url('blog'),
                        'current_page'  => (int) ($blog_pagination_current_page ?? (isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1)),
                        'total_pages'   => (int) ($blog_pagination_total_pages ?? 1),
                        'show_pages'    => 5,
                        'query_params'  => isset($blog_pagination_query) && is_array($blog_pagination_query) ? $blog_pagination_query : [],
                    ]);
                    ?>
                </div>
            </div>

        </div>
        <script>
            (function() {
                var section = document.getElementById('blog-tabs-section');
                if (!section) return;
                var searchInput = document.getElementById('blogSearchInput');
                var paginationWrap = document.getElementById('blog-pagination-wrap');
                var searchTimer = null;
                var BLOG_SEARCH_DEBOUNCE_MS = 450;
                function applyBlogSearch() {
                    var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
                    section.querySelectorAll('.blog-tab-panel').forEach(function(panel) {
                        var cards = panel.querySelectorAll('.blog-search-card');
                        var emptyEl = panel.querySelector('.blog-search-empty');
                        if (!cards.length) {
                            if (emptyEl) emptyEl.classList.add('hidden');
                            return;
                        }
                        var visible = 0;
                        cards.forEach(function(card) {
                            var hay = (card.getAttribute('data-blog-search') || '').toLowerCase();
                            var show = !q || hay.indexOf(q) !== -1;
                            card.style.display = show ? '' : 'none';
                            if (show) visible++;
                        });
                        if (emptyEl) {
                            if (q && visible === 0) emptyEl.classList.remove('hidden');
                            else emptyEl.classList.add('hidden');
                        }
                    });
                    if (paginationWrap) paginationWrap.style.display = q ? 'none' : '';
                }

                function scheduleBlogSearch() {
                    if (searchTimer) clearTimeout(searchTimer);
                    searchTimer = null;
                    if (searchInput && searchInput.value.trim() === '') {
                        applyBlogSearch();
                        return;
                    }
                    searchTimer = setTimeout(function() {
                        searchTimer = null;
                        applyBlogSearch();
                    }, BLOG_SEARCH_DEBOUNCE_MS);
                }

                if (searchInput) {
                    searchInput.addEventListener('input', scheduleBlogSearch);
                    searchInput.addEventListener('search', scheduleBlogSearch);
                }
                applyBlogSearch();
            })();
        </script>
    </div>
</section>
<section class="relative py-12 sm:py-24 overflow-hidden">

    <div class="absolute inset-0 " style="background-image: url('<?php echo theme_assets('images/frame1.webp'); ?>'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-0 left-0 w-full h-full">
                <svg width="100%" height="100%" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg"
                    preserveAspectRatio="none">
                    <path d="M0,300 Q300,100 600,300 T1200,300" stroke="rgba(255,255,255,0.3)" stroke-width="2" fill="none" />
                    <path d="M0,200 Q400,400 800,200 T1200,200" stroke="rgba(135,206,250,0.4)" stroke-width="2" fill="none" />
                    <path d="M0,400 Q200,100 500,400 T1200,400" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none" />
                </svg>
            </div>
        </div>
        <div class="absolute top-20 right-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-cyan-300/10 rounded-full blur-3xl"></div>
    </div>
    <div class="relative mx-auto px-4 sm:px-6 text-center z-10">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 sm:mb-6 font-space">
            <?php echo e(__('listing_cta_title')); ?>
        </h2>
        <p class="text-base sm:text-lg lg:text-xl text-white/90 mb-8 sm:mb-10 max-w-2xl mx-auto font-plus">
            <?php echo e(__('listing_cta_body')); ?>
        </p>
        <div class="flex flex-col items-center gap-4 sm:gap-6">
            <a href="<?php echo e(base_url('download')); ?>"
                class="inline-flex items-center gap-3 bg-home-primary hover:bg-home-primary-hover text-white font-semibold px-16 sm:px-8 py-4 rounded-home-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12.8572 16.0715V3.21436M12.8572 16.0715L7.50007 10.7144M12.8572 16.0715L18.2144 10.7144M22.5001 16.0715V20.3572C22.5001 20.9255 22.2743 21.4706 21.8724 21.8724C21.4706 22.2743 20.9255 22.5001 20.3572 22.5001H5.35721C4.78889 22.5001 4.24385 22.2743 3.84198 21.8724C3.44012 21.4706 3.21436 20.9255 3.21436 20.3572V16.0715"
                        stroke="white" stroke-width="2.14286" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-lg font-plus"><?php echo e(__('listing_cta_download')); ?></span>
            </a>
            <div class="flex items-center justify-center gap-2 text-white/80 text-xs">
                <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_47_621)">
                        <path
                            d="M13 3H2.33333C1.59695 3 1 3.59695 1 4.33333V5.66667C1 6.40305 1.59695 7 2.33333 7H13C13.7364 7 14.3333 6.40305 14.3333 5.66667V4.33333C14.3333 3.59695 13.7364 3 13 3Z"
                            stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M13 9.66667H2.33333C1.59695 9.66667 1 10.2636 1 11V12.3333C1 13.0697 1.59695 13.6667 2.33333 13.6667H13C13.7364 13.6667 14.3333 13.0697 14.3333 12.3333V11C14.3333 10.2636 13.7364 9.66667 13 9.66667Z"
                            stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <clipPath id="clip0_47_621">
                            <rect width="16" height="16.6667" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <span class="font-plus font-xs"><?php echo e(__('listing_cta_requirements')); ?></span>
            </div>
        </div>
    </div>
</section>