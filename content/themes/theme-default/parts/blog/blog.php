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
$blogsSrcAll = $blogsAllData ?? $blogsSrcPage;
/** Khóa tab ổn định: ưu tiên id term (đa ngôn ngữ), không thì slug đã sanitize */
$blog_cat_tab_key = static function ($cat): string {
    if (is_object($cat)) {
        $cat = (array) $cat;
    }
    if (!is_array($cat)) {
        return '';
    }
    $id = (int) ($cat['id'] ?? 0);
    if ($id > 0) {
        return 'term-' . $id;
    }
    $sl = trim((string) ($cat['slug'] ?? ''));
    if ($sl === '') {
        return '';
    }
    $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace([' ', '/'], '-', $sl));
    if ($safe === '') {
        $safe = substr(hash('sha256', $sl), 0, 12);
    }

    return 'c-' . $safe;
};
$blog_cat_label = static function ($cat): string {
    if (is_object($cat)) {
        return (string) ($cat->name ?? $cat->slug ?? '');
    }

    return (string) ($cat['name'] ?? $cat['slug'] ?? '');
};
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
$blog_search_q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

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

$blog_sorted_all = $blog_build_sorted(is_array($blogsSrcAll) ? $blogsSrcAll : []);
$blog_sorted = $blog_build_sorted(is_array($blogsSrcPage) ? $blogsSrcPage : []);
$blog_cur_page_int = max(1, (int) ($blog_pagination_current_page ?? (isset($_GET['page']) ? (int) $_GET['page'] : 1)));
if ($blog_cur_page_int === 1) {
    $blog_featured = $blog_sorted[0] ?? null;
    $blog_rest = array_slice($blog_sorted, 1);
} else {
    $blog_featured = null;
    $blog_rest = $blog_sorted;
}

// Tab category: theo get_terms (đúng ngôn ngữ) + bổ sung category chỉ có trên bài (tránh mất tab khi slug khác bản EN)
$blog_nav_list = [];
$blog_seen_tab = [];
foreach ($categories as $cat) {
    $key = $blog_cat_tab_key($cat);
    if ($key === '' || isset($blog_seen_tab[$key])) {
        continue;
    }
    $blog_seen_tab[$key] = true;
    $blog_nav_list[] = ['key' => $key, 'cat' => $cat];
}
foreach ($blog_sorted_all as $p) {
    $pid = (int) ($p['category_id'] ?? 0);
    $pslug = trim((string) ($p['category_slug'] ?? ''));
    if ($pid > 0) {
        $key = 'term-' . $pid;
        if (!isset($blog_seen_tab[$key])) {
            $blog_seen_tab[$key] = true;
            $blog_nav_list[] = [
                'key' => $key,
                'cat' => [
                    'id' => $pid,
                    'slug' => $pslug,
                    'name' => (string) ($p['category_name'] ?? $pslug),
                ],
            ];
        }
    } elseif ($pslug !== '') {
        $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace([' ', '/'], '-', $pslug));
        if ($safe === '') {
            $safe = substr(hash('sha256', $pslug), 0, 12);
        }
        $key = 'c-' . $safe;
        if (!isset($blog_seen_tab[$key])) {
            $blog_seen_tab[$key] = true;
            $blog_nav_list[] = [
                'key' => $key,
                'cat' => [
                    'id' => 0,
                    'slug' => $pslug,
                    'name' => (string) ($p['category_name'] ?? $pslug),
                ],
            ];
        }
    }
}
usort($blog_nav_list, static function ($a, $b) use ($blog_cat_label) {
    return strcasecmp($blog_cat_label($a['cat']), $blog_cat_label($b['cat']));
});

$blog_posts_by_tab = ['all-posts' => $blog_sorted];
foreach ($blog_nav_list as $nav) {
    $tkey = $nav['key'];
    $cat = $nav['cat'];
    if (is_object($cat)) {
        $cat = (array) $cat;
    }
    $cId = (int) ($cat['id'] ?? 0);
    $cSlug = (string) ($cat['slug'] ?? '');
    $blog_posts_by_tab[$tkey] = array_values(array_filter($blog_sorted_all, static function ($p) use ($cId, $cSlug) {
        if ($cId > 0 && (int) ($p['category_id'] ?? 0) === $cId) {
            return true;
        }
        if ($cSlug !== '' && (string) ($p['category_slug'] ?? '') === $cSlug) {
            return true;
        }

        return false;
    }));
}
?>
<section class="relative overflow-hidden min-h-screen bg-white">
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
                class="text-4xl sm:text-5xl md:text-[64px] font-bold text-black text-center mb-3 sm:mb-4 lg:leading-[80px] font-space">
                <?php echo e(__('listing_hero_line1')); ?>

                <br />
                <span class="bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent">
                    <?php echo e(__('listing_hero_gradient')); ?>
                </span>
            </h1>
            <p class="text-gray-600 text-base sm:text-lg md:text-xl max-w-4xl mx-auto px-4 sm:px-0 font-plus">
                <?php echo e(__('listing_hero_subtitle')); ?>
            </p>
        </div>
        <!-- Search Form -->
        <div class="mb-8 sm:mb-12 flex justify-center sm:px-0">
            <form action="" method="GET" class="w-full max-w-sm">
                <div class="relative">
                    <input type="text" name="q" id="blogSearchInput" placeholder="<?php echo e(__('search_placeholder')); ?>"
                        class="w-full h-[48px] sm:h-[56px] pl-4 pr-10 sm:pr-12 bg-white border border-home-surface rounded-home-md focus:outline-none focus:ring-2 focus:ring-home-primary focus:border-transparent text-sm sm:text-base text-gray-900 placeholder-gray-500 shadow-sm">
                    <button type="submit"
                        class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-home-primary transition-colors"
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

        <!-- Tabs Navigation (vanilla JS, giống cms-comparison) -->
        <div id="blog-tabs-section" class="mb-12">
            <div
                class="blog-tabs-nav flex flex-nowrap  sm:mx-0 justify-start sm:justify-center gap-2 sm:gap-3 mb-12 bg-home-surface-light rounded-none sm:rounded-home-lg px-3 py-2 sm:mx-auto border-gray-200 overflow-x-auto scrollbar-hide">
                <button type="button" data-blog-tab="all-posts" aria-pressed="true"
                    class="blog-tab-btn flex-shrink-0 whitespace-nowrap px-3 sm:px-6 py-2 sm:py-3 text-md font-medium transition-colors bg-home-primary border-home-primary text-white rounded-home-md">
                    <?php echo e(__('tab_all_posts')); ?>
                </button>
                <?php foreach ($blog_nav_list as $nav):
                    $tabId = $nav['key'];
                    $cat = $nav['cat'];
                    $cName = $blog_cat_label($cat);
                ?>
                    <button type="button" data-blog-tab="<?php echo e($tabId); ?>" aria-pressed="false"
                        class="blog-tab-btn flex-shrink-0 whitespace-nowrap px-3 sm:px-6 py-2 sm:py-3 text-md font-medium transition-colors text-gray-600 hover:text-gray-900 rounded-home-md">
                     <h2> <?php echo e($cName); ?></h2>  
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Tab Content: All Posts -->
            <div data-blog-content="all-posts" class="blog-tab-panel">
                <?php if ($blog_cur_page_int === 1): ?>
                <!-- Featured Article (chỉ trang 1; trang 2+ chỉ lưới + phân trang) -->
                <div>
                    <svg width="251" height="69" viewBox="0 0 251 69" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_994_11441)" filter="url(#filter0_dddd_994_11441)">
                            <path d="M63.8575 11.0129L12.752 32L65.3866 7.48816L63.8575 11.0129Z"
                                fill="url(#paint0_linear_994_11441)" />
                            <path d="M69.2882 17.8974L12.752 32L65.8101 16.2175L69.2882 17.8974Z"
                                fill="url(#paint1_linear_994_11441)" />
                            <path d="M62.9673 14.3008L12.752 32L60.0886 11.7021L62.9673 14.3008Z"
                                fill="url(#paint2_linear_994_11441)" />
                            <path d="M62.5043 18.1343L12.752 32.0001L62.9673 14.3008L62.5043 18.1343Z"
                                fill="url(#paint3_linear_994_11441)" />
                            <path d="M65.8101 16.2175L12.752 32L62.5043 18.1343L65.8101 16.2175Z"
                                fill="url(#paint4_linear_994_11441)" />
                            <path
                                d="M65.3858 7.48816L67.2487 10.8945L71.0714 11.318L68.4547 14.1106L69.2875 17.8973L65.8094 16.2175L62.5035 18.1342L62.9666 14.3008L60.0879 11.7021L63.8567 11.0129L65.3858 7.48816Z"
                                fill="#FFE029" />
                            <path d="M37.1597 9.77459L12.752 32L40.5301 2L37.1597 9.77459Z"
                                fill="url(#paint5_linear_994_11441)" />
                            <path d="M35.1963 17.0287L12.752 32L28.8539 11.2929L35.1963 17.0287Z"
                                fill="url(#paint6_linear_994_11441)" />
                            <path d="M49.1446 24.9577L12.752 32L41.467 21.2534L49.1446 24.9577Z"
                                fill="url(#paint7_linear_994_11441)" />
                            <path d="M34.1769 25.4781L12.752 32L35.1963 17.0287L34.1769 25.4781Z"
                                fill="url(#paint8_linear_994_11441)" />
                            <path
                                d="M40.5298 2L44.6432 9.51256L53.071 10.4458L47.3065 16.6052L49.1443 24.9576L41.4666 21.2534L34.1766 25.4781L35.1959 17.0287L28.8535 11.2929L37.1593 9.77459L40.5298 2Z"
                                fill="#FFE029" />
                            <path d="M14.7656 12.7251L12.752 32L16.2947 9.20392L14.7656 12.7251Z"
                                fill="url(#paint9_linear_994_11441)" />
                            <path d="M20.1999 19.6095L12.752 32L16.7182 17.9297L20.1999 19.6095Z"
                                fill="url(#paint10_linear_994_11441)" />
                            <path d="M13.8787 16.0165L12.7516 32L11 13.4142L13.8787 16.0165Z"
                                fill="url(#paint11_linear_994_11441)" />
                            <path d="M13.416 19.8464L12.752 32L13.879 16.0165L13.416 19.8464Z"
                                fill="url(#paint12_linear_994_11441)" />
                            <path d="M16.7182 17.9297L12.752 32L13.416 19.8464L16.7182 17.9297Z"
                                fill="url(#paint13_linear_994_11441)" />
                            <path
                                d="M16.2943 9.20392L18.1608 12.6066L21.9799 13.0302L19.3668 15.8263L20.1996 19.6095L16.7179 17.9297L13.4156 19.8464L13.8787 16.0166L11 13.4143L14.7653 12.7251L16.2943 9.20392Z"
                                fill="#FFE029" />
                        </g>
                        <path
                            d="M92.7323 25V10.1H102.172V11.9H94.6923V16.72H101.472V18.52H94.6923V25H92.7323ZM104.392 25V10.1H114.092V11.9H106.352V16.62H113.692V18.42H106.352V23.2H114.092V25H104.392ZM115.081 25L120.421 10.1H122.741L128.081 25H125.981L124.761 21.5H118.401L117.181 25H115.081ZM119.021 19.7H124.141L121.301 11.52H121.861L119.021 19.7ZM131.563 25V11.9H127.523V10.1H137.523V11.9H133.543V25H131.563ZM144.812 25.24C143.732 25.24 142.772 25.0133 141.932 24.56C141.105 24.1067 140.459 23.4867 139.992 22.7C139.539 21.9 139.312 20.9933 139.312 19.98V10.1H141.292V19.94C141.292 20.62 141.439 21.2267 141.732 21.76C142.039 22.28 142.452 22.6867 142.972 22.98C143.505 23.2733 144.119 23.42 144.812 23.42C145.505 23.42 146.112 23.2733 146.632 22.98C147.165 22.6867 147.579 22.28 147.872 21.76C148.179 21.2267 148.332 20.62 148.332 19.94V10.1H150.312V19.98C150.312 20.9933 150.079 21.9 149.612 22.7C149.159 23.4867 148.519 24.1067 147.692 24.56C146.865 25.0133 145.905 25.24 144.812 25.24ZM153.533 25V10.1H158.793C159.78 10.1 160.646 10.2867 161.393 10.66C162.14 11.0333 162.72 11.5667 163.133 12.26C163.56 12.9533 163.773 13.7667 163.773 14.7C163.773 15.7533 163.506 16.6467 162.973 17.38C162.44 18.1133 161.72 18.64 160.813 18.96L164.273 25H161.993L158.353 18.54L159.653 19.3H155.493V25H153.533ZM155.493 17.5H158.853C159.44 17.5 159.953 17.3867 160.393 17.16C160.833 16.9333 161.173 16.6067 161.413 16.18C161.666 15.7533 161.793 15.26 161.793 14.7C161.793 14.1267 161.666 13.6333 161.413 13.22C161.173 12.7933 160.833 12.4667 160.393 12.24C159.953 12.0133 159.44 11.9 158.853 11.9H155.493V17.5ZM166.502 25V10.1H176.202V11.9H168.462V16.62H175.802V18.42H168.462V23.2H176.202V25H166.502ZM178.631 25V10.1H183.511C185.017 10.1 186.331 10.4067 187.451 11.02C188.571 11.6333 189.437 12.5 190.051 13.62C190.664 14.7267 190.971 16.0333 190.971 17.54C190.971 19.0333 190.664 20.34 190.051 21.46C189.437 22.58 188.571 23.4533 187.451 24.08C186.331 24.6933 185.017 25 183.511 25H178.631ZM180.591 23.2H183.531C184.637 23.2 185.591 22.9667 186.391 22.5C187.204 22.0333 187.831 21.38 188.271 20.54C188.711 19.6867 188.931 18.6867 188.931 17.54C188.931 16.38 188.704 15.38 188.251 14.54C187.811 13.7 187.184 13.0533 186.371 12.6C185.571 12.1333 184.624 11.9 183.531 11.9H180.591V23.2ZM196.912 25V10.1H202.172C203.159 10.1 204.025 10.2867 204.772 10.66C205.519 11.0333 206.099 11.5667 206.512 12.26C206.939 12.9533 207.152 13.7667 207.152 14.7C207.152 15.6333 206.939 16.4467 206.512 17.14C206.099 17.82 205.519 18.3533 204.772 18.74C204.039 19.1133 203.172 19.3 202.172 19.3H198.872V25H196.912ZM198.872 17.5H202.232C202.832 17.5 203.352 17.3867 203.792 17.16C204.232 16.9333 204.572 16.6067 204.812 16.18C205.052 15.7533 205.172 15.26 205.172 14.7C205.172 14.1267 205.052 13.6333 204.812 13.22C204.572 12.7933 204.232 12.4667 203.792 12.24C203.352 12.0133 202.832 11.9 202.232 11.9H198.872V17.5ZM216.923 25.24C215.869 25.24 214.883 25.0533 213.963 24.68C213.043 24.2933 212.236 23.7533 211.543 23.06C210.863 22.3667 210.329 21.5533 209.943 20.62C209.556 19.6733 209.363 18.6467 209.363 17.54C209.363 16.42 209.556 15.3933 209.943 14.46C210.329 13.5267 210.863 12.7133 211.543 12.02C212.236 11.3267 213.036 10.7933 213.943 10.42C214.863 10.0467 215.856 9.86 216.923 9.86C217.989 9.86 218.976 10.0533 219.883 10.44C220.803 10.8133 221.603 11.3467 222.283 12.04C222.976 12.72 223.516 13.5267 223.903 14.46C224.303 15.3933 224.503 16.42 224.503 17.54C224.503 18.6467 224.303 19.6733 223.903 20.62C223.516 21.5533 222.976 22.3667 222.283 23.06C221.603 23.7533 220.803 24.2933 219.883 24.68C218.976 25.0533 217.989 25.24 216.923 25.24ZM216.923 23.42C217.749 23.42 218.503 23.2733 219.183 22.98C219.863 22.6733 220.449 22.2533 220.943 21.72C221.449 21.1733 221.836 20.5467 222.103 19.84C222.383 19.12 222.523 18.3533 222.523 17.54C222.523 16.7267 222.383 15.9667 222.103 15.26C221.836 14.5533 221.449 13.9333 220.943 13.4C220.449 12.8533 219.863 12.4333 219.183 12.14C218.503 11.8333 217.749 11.68 216.923 11.68C216.109 11.68 215.363 11.8333 214.683 12.14C214.003 12.4333 213.409 12.8533 212.903 13.4C212.409 13.9333 212.023 14.5533 211.743 15.26C211.463 15.9667 211.323 16.7267 211.323 17.54C211.323 18.3533 211.463 19.12 211.743 19.84C212.023 20.5467 212.409 21.1733 212.903 21.72C213.409 22.2533 214.003 22.6733 214.683 22.98C215.363 23.2733 216.109 23.42 216.923 23.42ZM232.481 25.24C231.535 25.24 230.668 25.0667 229.881 24.72C229.095 24.36 228.428 23.8733 227.881 23.26C227.335 22.6467 226.941 21.96 226.701 21.2L228.401 20.5C228.761 21.46 229.295 22.2 230.001 22.72C230.721 23.2267 231.561 23.48 232.521 23.48C233.108 23.48 233.621 23.3867 234.061 23.2C234.501 23.0133 234.841 22.7533 235.081 22.42C235.335 22.0733 235.461 21.6733 235.461 21.22C235.461 20.5933 235.281 20.1 234.921 19.74C234.575 19.3667 234.061 19.0867 233.381 18.9L230.641 18.06C229.561 17.7267 228.735 17.2 228.161 16.48C227.588 15.76 227.301 14.9333 227.301 14C227.301 13.1867 227.495 12.4733 227.881 11.86C228.281 11.2333 228.828 10.7467 229.521 10.4C230.228 10.04 231.028 9.86 231.921 9.86C232.815 9.86 233.621 10.02 234.341 10.34C235.075 10.66 235.695 11.0933 236.201 11.64C236.708 12.1733 237.081 12.7867 237.321 13.48L235.641 14.18C235.321 13.34 234.841 12.7067 234.201 12.28C233.561 11.84 232.808 11.62 231.941 11.62C231.408 11.62 230.935 11.7133 230.521 11.9C230.121 12.0733 229.808 12.3333 229.581 12.68C229.368 13.0133 229.261 13.4133 229.261 13.88C229.261 14.4267 229.435 14.9133 229.781 15.34C230.128 15.7667 230.655 16.0933 231.361 16.32L233.861 17.06C235.035 17.42 235.921 17.9333 236.521 18.6C237.121 19.2667 237.421 20.0933 237.421 21.08C237.421 21.8933 237.208 22.6133 236.781 23.24C236.368 23.8667 235.788 24.36 235.041 24.72C234.308 25.0667 233.455 25.24 232.481 25.24ZM242.891 25V11.9H238.851V10.1H248.851V11.9H244.871V25H242.891Z"
                            fill="var(--home-body)" />
                        <defs>
                            <filter id="filter0_dddd_994_11441" x="0" y="0" width="82.0723" height="69"
                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feOffset dy="2" />
                                <feGaussianBlur stdDeviation="2" />
                                <feColorMatrix type="matrix"
                                    values="0 0 0 0 1 0 0 0 0 0.85098 0 0 0 0 0.156863 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_994_11441" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feOffset dy="7" />
                                <feGaussianBlur stdDeviation="3.5" />
                                <feColorMatrix type="matrix"
                                    values="0 0 0 0 1 0 0 0 0 0.85098 0 0 0 0 0.156863 0 0 0 0.09 0" />
                                <feBlend mode="normal" in2="effect1_dropShadow_994_11441"
                                    result="effect2_dropShadow_994_11441" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feOffset dy="15" />
                                <feGaussianBlur stdDeviation="4.5" />
                                <feColorMatrix type="matrix"
                                    values="0 0 0 0 1 0 0 0 0 0.85098 0 0 0 0 0.156863 0 0 0 0.05 0" />
                                <feBlend mode="normal" in2="effect2_dropShadow_994_11441"
                                    result="effect3_dropShadow_994_11441" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feOffset dy="26" />
                                <feGaussianBlur stdDeviation="5.5" />
                                <feColorMatrix type="matrix"
                                    values="0 0 0 0 1 0 0 0 0 0.85098 0 0 0 0 0.156863 0 0 0 0.01 0" />
                                <feBlend mode="normal" in2="effect3_dropShadow_994_11441"
                                    result="effect4_dropShadow_994_11441" />
                                <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_994_11441"
                                    result="shape" />
                            </filter>
                            <linearGradient id="paint0_linear_994_11441" x1="12.752" y1="19.7423" x2="65.3866"
                                y2="19.7423" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint1_linear_994_11441" x1="12.752" y1="24.107" x2="69.2882"
                                y2="24.107" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F2BA38" />
                                <stop offset="1" stop-color="#EDA826" />
                            </linearGradient>
                            <linearGradient id="paint2_linear_994_11441" x1="11.0003" y1="21.8493" x2="62.9673"
                                y2="21.8493" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F2BA38" />
                                <stop offset="1" stop-color="#EDA826" />
                            </linearGradient>
                            <linearGradient id="paint3_linear_994_11441" x1="11.0003" y1="23.1522" x2="62.9673"
                                y2="23.1522" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint4_linear_994_11441" x1="11.0003" y1="24.107" x2="65.8101"
                                y2="24.107" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint5_linear_994_11441" x1="11.0003" y1="17" x2="40.5301" y2="17"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint6_linear_994_11441" x1="11.0003" y1="21.6447" x2="35.1963"
                                y2="21.6447" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F2BA38" />
                                <stop offset="1" stop-color="#EDA826" />
                            </linearGradient>
                            <linearGradient id="paint7_linear_994_11441" x1="11.0003" y1="26.6267" x2="49.1446"
                                y2="26.6267" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F2BA38" />
                                <stop offset="1" stop-color="#EDA826" />
                            </linearGradient>
                            <linearGradient id="paint8_linear_994_11441" x1="11.0003" y1="24.5126" x2="35.1963"
                                y2="24.5126" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint9_linear_994_11441" x1="11.0003" y1="20.6002" x2="16.2947"
                                y2="20.6002" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint10_linear_994_11441" x1="11.0003" y1="24.9649" x2="20.1999"
                                y2="24.9649" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F2BA38" />
                                <stop offset="1" stop-color="#EDA826" />
                            </linearGradient>
                            <linearGradient id="paint11_linear_994_11441" x1="11" y1="22.7071" x2="13.8787" y2="22.7071"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F2BA38" />
                                <stop offset="1" stop-color="#EDA826" />
                            </linearGradient>
                            <linearGradient id="paint12_linear_994_11441" x1="11.0003" y1="24.0065" x2="13.879"
                                y2="24.0065" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <linearGradient id="paint13_linear_994_11441" x1="11.0003" y1="24.9649" x2="16.7182"
                                y2="24.9649" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EDA826" />
                                <stop offset="0.33" stop-color="#EEAE2C" />
                                <stop offset="0.77" stop-color="#F2BF3D" />
                                <stop offset="1" stop-color="#F6CB49" />
                            </linearGradient>
                            <clipPath id="clip0_994_11441">
                                <rect width="60.0718" height="30" fill="white" transform="translate(11 2)" />
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <?php if (!empty($blog_featured)):
                    $__bf = $blog_featured;
                    $__bfd = '';
                    $__bf_mins = '';
                    $ca = $__bf['created_at'] ?? '';
                    if ($ca !== '') {
                        $__bfd = date('M j, Y', is_numeric($ca) ? (int) $ca : strtotime((string) $ca));
                        $__bf_ts = blog_created_at_to_unix($ca);
                        $__bf_mins = $__bf_ts > 0 ? blog_mins_only_label($__bf_ts) : '';
                    }
                    $__plain = strip_tags((string) ($__bf['description_title'] ?? ''));
                    $__bfe = mb_substr($__plain, 0, 200);
                    if (mb_strlen($__plain) > 200) {
                        $__bfe .= '...';
                    }
                    $__bf_search_raw = strip_tags(
                        ($__bf['title'] ?? '') . ' ' . ($__bf['description_title'] ?? '') . ' ' . ($__bf['category_name'] ?? '') . ' ' . ($__bf['username'] ?? '')
                    );
                    $__bf_search = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $__bf_search_raw)), 'UTF-8');
                ?>
                    <div
                        class="mb-12 sm:mb-24 blog-search-card bg-white rounded-home-lg overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow group"
                        data-blog-search="<?php echo htmlspecialchars($__bf_search, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="blog-featured-row flex flex-col md:p-0 lg:p-6 lg:flex-row">
                            <div class="relative w-full min-w-0 overflow-hidden rounded-t-xl blog-featured-media">
                                <?php if (!empty($__bf['feature'])): ?>
                                    <?php echo _imglazy($__bf['feature'], [
                                        'alt' => (string) ($__bf['title'] ?? ''),
                                        'class' => 'blog-featured-media-img block w-full h-auto object-cover',
                                        'loading' => 'eager',
                                        'fetchpriority' => 'high',
                                        'decoding' => 'sync',
                                        /* <lg: ảnh full width; lg+: ~một nửa hàng — không cần xlarge */
                                        'sizes' => [
                                            'mobile' => 'large',
                                            'tablet' => 'large',
                                            'desktop' => 'medium',
                                            'large' => 'medium',
                                        ],
                                    ]); ?>
                                <?php endif; ?>
                            </div>
                            <div class="blog-featured-content p-5 min-w-0 flex-1 flex flex-col justify-between pt-6 lg:pt-0">
                                <div class="space-y-6 lg:space-y-8">
                                    <div
                                        class="flex flex-wrap items-center gap-2 sm:gap-6 text-xs sm:text-sm text-gray-500 mb-4 sm:mb-8">
                                        <?php if (($__bf['category_name'] ?? '') !== ''): ?>
                                            <span
                                                class="<?php echo e($__bf['category_style'] ?? $blog_category_default_style); ?> text-xs font-semibold px-2 sm:px-3 py-1 sm:py-2 rounded-home-md"><?php echo e($__bf['category_name']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($__bfd !== ''): ?>
                                            <div class="flex items-center gap-1 sm:gap-2">
                                                <svg width="16" height="16" class="sm:w-5 sm:h-5" viewBox="0 0 20 20"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M6.66667 1.66669V5.00002M13.3333 1.66669V5.00002M2.5 8.33335H17.5M4.16667 3.33335H15.8333C16.7538 3.33335 17.5 4.07955 17.5 5.00002V16.6667C17.5 17.5872 16.7538 18.3334 15.8333 18.3334H4.16667C3.24619 18.3334 2.5 17.5872 2.5 16.6667V5.00002C2.5 4.07955 3.24619 3.33335 4.16667 3.33335Z"
                                                        stroke="#97A4B2" stroke-width="1.66667" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <span><?php echo e($__bfd); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex items-center gap-1 sm:gap-2">
                                            <svg width="16" height="16" class="sm:w-5 sm:h-5" viewBox="0 0 20 20"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.99984 5.00002V10L13.3332 11.6667M18.3332 10C18.3332 14.6024 14.6022 18.3334 9.99984 18.3334C5.39746 18.3334 1.6665 14.6024 1.6665 10C1.6665 5.39765 5.39746 1.66669 9.99984 1.66669C14.6022 1.66669 18.3332 5.39765 18.3332 10Z"
                                                    stroke="#97A4B2" stroke-width="1.66667" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>

                                            <span><?php echo e($__bf_mins !== '' ? $__bf_mins : '—'); ?></span>
                                        </div>
                                    </div>
                                    <a href="<?php echo e($__bf['url'] ?? '#'); ?>"
                                        class="text-xl sm:text-2xl lg:text-3xl text-gray-900 mb-3 sm:mb-4 group-hover:text-home-primary transition-colors">
                                        <?php echo e($__bf['title'] ?? ''); ?>
                                    </a>
                                    <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">
                                        <?php echo e($__bfe); ?>
                                    </p>
                                </div>
                                <div
                                    class="flex  sm:flex-row items-center justify-between gap-3 sm:gap-0 mt-6 lg:mt-0">
                                    <div class="flex items-center">
                                        <?php if (!empty($__bf['avatar'])): ?>
                                            <div class="blog-author-avatar">
                                                <?php echo _imglazy($__bf['avatar'], [
                                                    'alt' => (string) ($__bf['username'] ?? ''),
                                                    'class' => 'w-full h-full object-cover',
                                                    'sizes' => [
                                                        'mobile' => 'thumbnail',
                                                        'tablet' => 'medium',
                                                        'desktop' => 'medium',
                                                        'large' => 'medium',
                                                    ],
                                                ]); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (($__bf['username'] ?? '') !== ''): ?>
                                            <span class="text-xs sm:text-sm font-medium text-gray-900 ms-2"><?php echo e($__bf['username']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo e($__bf['url'] ?? '#'); ?>"
                                        class="group/link text-home-primary text-md font-semibold flex items-center gap-1 transition-all">
                                        <span class="group-hover/link:-translate-x-1 transition-transform duration-200 font-bold"><?php echo e(__('listing_read_article')); ?></span>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.4 12L9.4 18L8 16.6L12.6 12L8 7.4L9.4 6L15.4 12Z" fill="var(--home-primary)" />
                                        </svg>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Articles Grid -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 lg:grid-cols-3 lg:gap-8" role="list">
                    <?php foreach ($blog_rest as $item): ?>
                        <?php echo \System\Libraries\Render\View::include('parts/blog/_blog_article_card', [
                            'item' => $item,
                            'blog_category_styles' => [],
                            'blog_category_default_style' => $blog_category_default_style,
                        ]); ?>
                    <?php endforeach; ?>
                    <p role="status" aria-live="polite"
                        class="blog-search-empty hidden text-gray-500 text-center col-span-full py-12"><?php echo e(__('search_no_results')); ?></p>
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

            <?php foreach ($blog_nav_list as $nav):
                $panelKey = $nav['key'];
                $panelPosts = $blog_posts_by_tab[$panelKey] ?? [];
            ?>
                <div data-blog-content="<?php echo e($panelKey); ?>" class="blog-tab-panel" hidden>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 lg:grid-cols-3 lg:gap-8" role="list">
                        <?php foreach ($panelPosts as $item): ?>
                            <?php echo \System\Libraries\Render\View::include('parts/blog/_blog_article_card', [
                                'item' => $item,
                                'blog_category_styles' => [],
                                'blog_category_default_style' => $blog_category_default_style,
                            ]); ?>
                        <?php endforeach; ?>
                        <?php if (empty($panelPosts)): ?>
                            <p class="text-gray-500 text-center col-span-full py-12"><?php echo e(__('tab_category_empty')); ?></p>
                        <?php else: ?>
                            <p role="status" aria-live="polite"
                                class="blog-search-empty hidden text-gray-500 text-center col-span-full py-12"><?php echo e(__('search_no_results')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <script>
            (function() {
                var section = document.getElementById('blog-tabs-section');
                if (!section) return;
                var searchInput = document.getElementById('blogSearchInput');
                var paginationWrap = document.getElementById('blog-pagination-wrap');
                var searchTimer = null;
                var BLOG_SEARCH_DEBOUNCE_MS = 450;
                var activeClasses = ['bg-home-primary', 'border-home-primary', 'text-white', 'rounded-home-md'];
                var inactiveClasses = ['text-gray-600', 'hover:text-gray-900', 'rounded-home-md'];

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

                section.querySelectorAll('.blog-tab-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var tab = this.getAttribute('data-blog-tab');
                        section.querySelectorAll('.blog-tab-btn').forEach(function(b) {
                            b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                            activeClasses.forEach(function(c) {
                                b.classList.remove(c);
                            });
                            inactiveClasses.forEach(function(c) {
                                b.classList.remove(c);
                            });
                            if (b === btn) activeClasses.forEach(function(c) {
                                b.classList.add(c);
                            });
                            else inactiveClasses.forEach(function(c) {
                                b.classList.add(c);
                            });
                        });
                        section.querySelectorAll('.blog-tab-panel').forEach(function(panel) {
                            panel.hidden = panel.getAttribute('data-blog-content') !== tab;
                        });
                        applyBlogSearch();
                    });
                });

                if (searchInput) {
                    searchInput.addEventListener('input', scheduleBlogSearch);
                    searchInput.addEventListener('search', scheduleBlogSearch);
                }
                applyBlogSearch();
            })();
        </script>
    </div>
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