<?php
// API columns: id, title, slug, lang_slug, status, description, content, img/feature, created_at, ...
// Màu badge theo category — đồng bộ logic với parts/blog/blog.php (Eloquent cần toArray, không dùng (array)$model)
$tut_style_tutorial = 'bg-home-success-20 text-home-success-on-mint';
$tut_style_updates = 'bg-[#E0EEF5] text-home-primary';
$tut_style_community = 'bg-[#F5E7E0] text-[#ED661D]';
$tut_style_academy = 'bg-[#ECE4F6] text-[#9747FF]';

$tutorial_category_styles = [
    'Academy Series' => $tut_style_academy,
    'Chuỗi học tập'   => $tut_style_academy,
    'Tutorials'       => $tut_style_tutorial,
    'Tutorial'        => $tut_style_tutorial,
    'Hướng Dẫn'       => $tut_style_tutorial,
    'Hướng dẫn'       => $tut_style_tutorial,
    'Community'       => $tut_style_community,
    'Cộng Đồng'       => $tut_style_community,
    'Cộng đồng'       => $tut_style_community,
    'Updates'         => $tut_style_updates,
    'Thêm Mới'        => $tut_style_updates,
    'Thêm mới'        => $tut_style_updates,
    'Cập nhật'        => $tut_style_updates,
    'Tin tức'         => $tut_style_updates,
    'Tin Tức'         => $tut_style_updates,
    'Cập nhật mới'    => $tut_style_updates,
];
$tutorial_category_default_style = 'bg-gray-100 text-gray-700';
$tutorial_category_styles_by_slug = [
    'tutorial'       => $tut_style_tutorial,
    'tutorials'      => $tut_style_tutorial,
    'updates'        => $tut_style_updates,
    'academy-series' => $tut_style_academy,
    'community'      => $tut_style_community,
    'huong-dan'      => $tut_style_tutorial,
    'huongdan'       => $tut_style_tutorial,
    'them-moi'       => $tut_style_updates,
    'themmoi'        => $tut_style_updates,
    'cap-nhat'       => $tut_style_updates,
    'capnhat'        => $tut_style_updates,
    'tin-tuc'        => $tut_style_updates,
    'tintuc'         => $tut_style_updates,
    'cong-dong'      => $tut_style_community,
    'congdong'       => $tut_style_community,
    'chuoi-hoc-tap'  => $tut_style_academy,
    'chuoi-hoc-tap-series' => $tut_style_academy,
];

$tutorial_resolve_category_style = static function (string $name) use ($tutorial_category_styles, $tutorial_category_default_style): string {
    $name = trim(strip_tags(html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if ($name === '') {
        return $tutorial_category_default_style;
    }
    $name = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $name) ?? $name;
    if (class_exists(\Normalizer::class, false)) {
        $n = \Normalizer::normalize($name, \Normalizer::FORM_C);
        if (is_string($n) && $n !== '') {
            $name = $n;
        }
    }
    if (isset($tutorial_category_styles[$name])) {
        return $tutorial_category_styles[$name];
    }
    if (function_exists('mb_strtolower')) {
        $needle = mb_strtolower($name, 'UTF-8');
        foreach ($tutorial_category_styles as $label => $classes) {
            if (mb_strtolower(trim((string) $label), 'UTF-8') === $needle) {
                return $classes;
            }
        }
    }

    return $tutorial_category_default_style;
};

$tutorial_resolve_category_style_full = static function (string $name, string $catSlug) use (
    $tutorial_resolve_category_style,
    $tutorial_category_styles_by_slug,
    $tutorial_category_default_style
): string {
    $byName = $tutorial_resolve_category_style($name);
    if ($byName !== $tutorial_category_default_style) {
        return $byName;
    }
    $slug = strtolower(trim($catSlug));
    $slug = str_replace('_', '-', $slug);
    if ($slug !== '' && isset($tutorial_category_styles_by_slug[$slug])) {
        return $tutorial_category_styles_by_slug[$slug];
    }

    return $tutorial_category_default_style;
};

$tutorial_term_row_to_array = static function ($item): array {
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

$tutorial_categories_list_normalize = static function ($cats): array {
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

$tutorials = $tutorials ?? [];
$categories = $categories ?? [];

$list = [];
foreach ($tutorials as $row) {
    $img = null;
    if (!empty($row['feature'])) {
        $av = $row['feature'];
        if (is_array($av)) {
            $img = $av;
        } elseif (is_string($av) && strpos(trim($av), '{') === 0) {
            $decoded = json_decode($av, true);
            $img = is_array($decoded) ? $decoded : $av;
        } else {
            $img = $av;
        }
    }
    $cats = $tutorial_categories_list_normalize($row['categories'] ?? []);
    $firstRaw = $cats[0] ?? null;
    $firstCat = $tutorial_term_row_to_array($firstRaw);
    $categoryName = (string) ($firstCat['name'] ?? $firstCat['title'] ?? $firstCat['label'] ?? '');
    $categoryTermSlug = (string) ($firstCat['slug'] ?? $firstCat['term_slug'] ?? '');
    $categoryId = (int) ($firstCat['id'] ?? $firstCat['term_id'] ?? 0);
    $idMain = (int) ($firstCat['id_main'] ?? 0);
    if (($categoryName === '' || $categoryTermSlug === '') && ($categoryId > 0 || $idMain > 0)) {
        foreach ($categories as $c) {
            $ca = $tutorial_term_row_to_array($c);
            $cid = (int) ($ca['id'] ?? 0);
            $cm = (int) ($ca['id_main'] ?? 0);
            $match = ($categoryId > 0 && $cid === $categoryId) || ($idMain > 0 && ($cm === $idMain || $cid === $idMain));
            if ($match) {
                if ($categoryName === '') {
                    $categoryName = (string) ($ca['name'] ?? $ca['title'] ?? $ca['label'] ?? '');
                }
                if ($categoryTermSlug === '') {
                    $categoryTermSlug = (string) ($ca['slug'] ?? $ca['term_slug'] ?? '');
                }
                break;
            }
        }
    }
    $slug = $row['slug'] ?? '';
    $categoryName = trim((string) $categoryName);
    $categoryStyle = $tutorial_resolve_category_style_full($categoryName, (string) $categoryTermSlug);
    $list[] = [
        'title'           => $row['title'] ?? '',
        'description_title' => $row['description_title'] ?? '',
        'category_name'   => $categoryName,
        'category_style'   => $categoryStyle,
        'img'             => $img,
        'created_at'  => $row['created_at'] ?? '',
        'url'         => $slug ? (string) link_posts($slug, 'blog', defined('APP_LANG') ? APP_LANG : '') : '#',
    ];
}
$sidebarList = $list;
?>
<section class="mx-auto py-12 sm:py-24 container">
    <div class=" mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-12 gap-4  md:px-0">

            <!-- Left -->
            <div class="text-center md:text-left">
                <h2 class="sr sr--fade-up
                    text-3xl leading-[34px]
                    sm:text-[32px] sm:leading-[40px]
                    md:text-[40px] md:leading-[52px]
                    lg:text-[48px] lg:leading-[61px]
                    font-medium text-home-heading mb-2" style="--sr-delay: 0ms">
                    <?php echo e(__('home_tutorials.heading')); ?>
                </h2>

                <p class="sr sr--fade-up
                    text-gray-600
                    text-xs sm:text-sm md:text-base
                    leading-relaxed
                    mt-2
                font-plus" style="--sr-delay: 50ms">
                    <?php echo e(__('home_tutorials.intro')); ?>
                </p>
            </div>

            <!-- Right: desktop hiện bên phải title, mobile ẩn (link nằm trên PHP Quick Start) -->
            <a href="<?php echo e(base_url('usage-guide')); ?>"
                class="sr sr--fade-up hidden md:flex items-center justify-center md:justify-start text-home-primary font-medium hover:underline text-sm sm:text-base font-plus self-center md:self-auto" style="--sr-delay: 80ms">
                <?php echo e(__('home_get_started.cta_guide')); ?>
                <svg class="ml-1" width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M9 6L15 12L9 18" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>


        </div>


        <!-- Content -->
        <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6 md:gap-6 lg:gap-8 xl:gap-10 sm:grid-cols-1 ">

            <div class="space-y-6">
                <?php foreach (array_slice($list, 0, 2) as $item): ?>
                    <div class="flex flex-col md:flex-row gap-4 md:gap-6 p-5 md:p-6 border rounded-2xl overflow-hidden hover:shadow-md transition">
                        <?php if (!empty($item['img'])): ?>
                            <div class="w-full md:w-[220px] h-[200px] md:h-auto flex-shrink-0 overflow-hidden rounded-home-lg">
                                <?php
                                // Responsive + WebP: _imglazy cần sizes dạng array mới tạo <source> theo breakpoint; mặc định '100vw' không có srcset → tải ảnh quá lớn trên mobile.
                                $__tutImg = $item['img'];
                                $__tutSizesMeta = is_array($__tutImg) ? ($__tutImg['sizes'] ?? []) : (is_object($__tutImg) ? ($__tutImg->sizes ?? []) : []);
                                $__tutNames = [];
                                foreach ((array) $__tutSizesMeta as $__sz) {
                                    if (is_array($__sz) && !empty($__sz['name'])) {
                                        $__tutNames[] = (string) $__sz['name'];
                                    }
                                }
                                $__tutPick = static function (array $prefs, array $avail): string {
                                    foreach ($prefs as $p) {
                                        if (in_array($p, $avail, true)) {
                                            return $p;
                                        }
                                    }

                                    return $avail[0] ?? 'medium';
                                };
                                $__tutAttrs = [
                                    'alt' => $item['title'],
                                    'class' => 'w-full h-full object-cover md:transition-transform md:duration-500 md:ease-out md:hover:scale-110',
                                ];
                                if (!empty($__tutNames)) {
                                    $__tutMobile = $__tutPick(['thumbnail', 'medium', 'large'], $__tutNames);
                                    $__tutDesktop = $__tutPick(['thumbnail', 'medium'], $__tutNames);
                                    $__tutAttrs['sizes'] = [
                                        'mobile' => $__tutMobile,
                                        'tablet' => $__tutMobile,
                                        'desktop' => $__tutDesktop,
                                        'large' => $__tutDesktop,
                                    ];
                                }
                                echo _imglazy($item['img'], $__tutAttrs);
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-col justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-6 mb-6">
                                    <span class="<?php echo e($item['category_style'] ?? $tutorial_category_default_style); ?> text-xs md:text-sm font-semibold px-3 py-1 rounded-home-sm font-plus"><?php echo e($item['category_name'] ?? __('Category')); ?></span>
                                    <?php if (!empty($item['created_at'])): ?>
                                        <div class="flex items-center text-xs md:text-sm text-gray-600 gap-1 font-plus">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-600" aria-hidden="true">
                                                <g clip-path="url(#clip0_2089_7588)">
                                                    <path d="M5.33333 1.3335V4.00016M10.6667 1.3335V4.00016M2 6.66683H14M3.33333 2.66683H12.6667C13.403 2.66683 14 3.26378 14 4.00016V13.3335C14 14.0699 13.403 14.6668 12.6667 14.6668H3.33333C2.59695 14.6668 2 14.0699 2 13.3335V4.00016C2 3.26378 2.59695 2.66683 3.33333 2.66683Z" stroke="currentColor" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2089_7588">
                                                        <rect width="16" height="16" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <?php echo e(date('M j, Y', is_numeric($item['created_at']) ? $item['created_at'] : strtotime($item['created_at']))); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-lg md:text-[24px] text-gray-900 mb-2 hover:text-home-primary font-plus line-clamp-2">
                                    <a href="<?php echo e($item['url']); ?>"><?php echo e($item['title']); ?></a>
                                </h3>
                                <p class="text-sm md:text-base text-gray-600 font-plus line-clamp-2">
                                    <?php
                                    $__tut_desc = trim(strip_tags((string) ($item['description_title'] ?? '')));
                                    if ($__tut_desc !== '' && function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($__tut_desc, 'UTF-8') > 120) {
                                        $__tut_desc = mb_substr($__tut_desc, 0, 120, 'UTF-8') . '…';
                                    }
                                    echo e($__tut_desc);
                                    ?>
                                </p>
                            </div>
                            <a href="<?php echo e($item['url']); ?>" class="mt-6 inline-flex items-center text-sm font-semibold hover:underline font-plus hover:text-home-primary">
                                <?php echo e(__('home_tutorials.read_article')); ?>
                                <svg class="ml-1" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="2" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mobile: View Full Guide ngay trên PHP Quick Start -->
            <div class="lg:hidden flex justify-center ">
                <a href="<?php echo e(base_url('usage-guide')); ?>"
                    class="flex items-center py-3 px-4 rounded-home-sm justify-center text-home-primary font-medium hover:underline text-sm font-plus bg-home-surface-light sm:bg-transparent">
                    <?php echo e(__('home_get_started.cta_guide')); ?>
                    <svg class="ml-1" width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M9 6L15 12L9 18" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div>

            <!-- RIGHT: Sidebar -->
            <div class="border rounded-2xl p-6 shadow-sm flex flex-col h-full">
                <h4 class="text-lg  mb-5 flex items-center gap-2 font-plus ">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="2" fill="#9747FF" fill-opacity="0.08" />
                        <path
                            d="M20 15V29M20 15C20 13.9391 19.5786 12.9217 18.8284 12.1716C18.0783 11.4214 17.0609 11 16 11H11C10.7348 11 10.4804 11.1054 10.2929 11.2929C10.1054 11.4804 10 11.7348 10 12V25C10 25.2652 10.1054 25.5196 10.2929 25.7071C10.4804 25.8946 10.7348 26 11 26H17C17.7956 26 18.5587 26.3161 19.1213 26.8787C19.6839 27.4413 20 28.2044 20 29M20 15C20 13.9391 20.4214 12.9217 21.1716 12.1716C21.9217 11.4214 22.9391 11 24 11H29C29.2652 11 29.5196 11.1054 29.7071 11.2929C29.8946 11.4804 30 11.7348 30 12V25C30 25.2652 29.8946 25.5196 29.7071 25.7071C29.5196 25.8946 29.2652 26 29 26H23C22.2044 26 21.4413 26.3161 20.8787 26.8787C20.3161 27.4413 20 28.2044 20 29"
                            stroke="#9747FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <?php echo e(__('home_tutorials.php_quick_start')); ?>
                </h4>

                <ul class="space-y-8 text-sm text-gray-600 mb-6">
                    <?php foreach ($sidebarList as $item): ?>
                        <li class="group flex gap-2 items-center transition-colors">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                <path
                                    class="transition-[stroke] group-hover:!stroke-[#9747FF]"
                                    d="M11.25 2.25H15.75M15.75 2.25V6.75M15.75 2.25L7.5 10.5M13.5 9.75V14.25C13.5 14.6478 13.342 15.0294 13.0607 15.3107C12.7794 15.592 12.3978 15.75 12 15.75H3.75C3.35218 15.75 2.97064 15.592 2.68934 15.3107C2.40804 15.0294 2.25 14.6478 2.25 14.25V6C2.25 5.60218 2.40804 5.22064 2.68934 4.93934C2.97064 4.65804 3.35218 4.5 3.75 4.5H8.25"
                                    stroke="var(--home-body)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="<?php echo e($item['url']); ?>" class="font-plus transition-colors group-hover:text-[#9747FF]"><?php echo e($item['title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?php echo e(base_url('blog')); ?>"
                    class=" w-full border mt-auto flex justify-center items-center py-2.5 rounded-home-md bg-gray-100 text-gray-700 font-medium  transition hover:border-home-primary font-plus ">
                    <?php echo e(__('home_tutorials.see_documents')); ?>
                </a>
            </div>

        </div>
    </div>
</section>