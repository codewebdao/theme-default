<?php
/**
 * Tabs lọc theo category-cms (bảng terms: posttype=reviews, taxonomy category-cms).
 *
 * @var array $cms_comparison_categories
 * @var array $cms_comparison_posts      Raw rows từ get_posts (posttype reviews)
 */
use System\Libraries\Render\View;

$cms_comparison_categories = $cms_comparison_categories ?? [];
$__cms_cats = [];
foreach ($cms_comparison_categories as $_row) {
    if (is_object($_row) && method_exists($_row, 'toArray')) {
        $_row = $_row->toArray();
    } elseif (is_object($_row)) {
        $_row = (array) $_row;
    }
    if (is_array($_row) && trim((string) ($_row['slug'] ?? '')) !== '') {
        $__cms_cats[] = $_row;
    }
}
if ($__cms_cats === []) {
    $__cms_cats = [
        ['slug' => 'php-based', 'name' => 'PHP Based'],
        ['slug' => 'node-js', 'name' => 'Node.js'],
        ['slug' => 'headless', 'name' => 'Headless'],
    ];
}
$cms_comparison_filter_slug = static function (array $c): string {
    $s = strtolower(trim((string) ($c['slug'] ?? '')));
    $s = preg_replace('/[^a-z0-9\-_]+/', '-', $s);
    $s = trim($s, '-');

    return $s !== '' ? $s : 'cat-' . substr(hash('sha256', (string) ($c['id'] ?? $c['name'] ?? '')), 0, 10);
};
$cms_btn_base = 'cms-filter-btn px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm font-medium transition-colors font-plus ';
$cms_btn_active = $cms_btn_base . 'bg-home-primary border-home-primary text-white rounded-home-md';
$cms_btn_inactive = $cms_btn_base . 'text-gray-600 hover:text-gray-900 rounded-home-md';

$cms_comparison_posts = $cms_comparison_posts ?? [];
$cms_posts_rows = [];
if (is_array($cms_comparison_posts)) {
    foreach ($cms_comparison_posts as $r) {
        if (is_object($r) && method_exists($r, 'toArray')) {
            $cms_posts_rows[] = $r->toArray();
        } elseif (is_object($r)) {
            $cms_posts_rows[] = (array) $r;
        } elseif (is_array($r)) {
            $cms_posts_rows[] = $r;
        }
    }
}

$cms_term_row_to_array = static function ($item): array {
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

$cms_categories_normalize = static function ($cats): array {
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

$cms_parse_feature = static function ($val) {
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

$cms_parse_detail_list = static function ($raw, array $keys): array {
    if ($raw === null || $raw === '') {
        return [];
    }
    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $raw = $decoded;
        } else {
            $line = trim($raw);

            return $line !== '' ? [$line] : [];
        }
    }
    if (!is_array($raw)) {
        return [];
    }
    if ($raw !== [] && array_keys($raw) !== range(0, count($raw) - 1)) {
        $raw = [$raw];
    }
    $out = [];
    foreach ($raw as $item) {
        if (!is_array($item)) {
            if (is_string($item) && trim($item) !== '') {
                $out[] = trim($item);
            }
            continue;
        }
        $line = '';
        foreach ($keys as $k) {
            if (!empty($item[$k])) {
                $line = trim((string) $item[$k]);
                break;
            }
        }
        if ($line === '' && isset($item['value'])) {
            $line = trim((string) $item['value']);
        }
        if ($line !== '') {
            $out[] = $line;
        }
    }

    return $out;
};

$cms_normalize_card = static function (array $row) use (
    $cms_comparison_filter_slug,
    $cms_term_row_to_array,
    $cms_categories_normalize,
    $cms_parse_feature,
    $cms_parse_detail_list
): array {
    $feature = $cms_parse_feature($row['feature'] ?? null);
    $proItems = $cms_parse_detail_list($row['pro'] ?? null, ['pro_detail', 'text', 'title', 'name', 'label', 'detail']);
    $consItems = $cms_parse_detail_list($row['cons'] ?? null, ['cons_detail', 'con_detail', 'text', 'title', 'name', 'label', 'detail']);

    $perf = (int) round((float) ($row['performance'] ?? 0));
    $perf = max(0, min(100, $perf));
    $barClass = $perf >= 50
        ? 'bg-gradient-to-r from-home-primary to-home-accent'
        : 'bg-gradient-to-r from-[#FBB84B] to-[#ED661D]';

    $rating = isset($row['rating_avg']) ? (float) $row['rating_avg'] : 0.0;
    $ratingDisplay = number_format($rating, 1, '.', '');

    $cats = $cms_categories_normalize($row['categories'] ?? []);
    $filterSlugs = [];
    foreach ($cats as $craw) {
        $ca = $cms_term_row_to_array($craw);
        if ($ca === []) {
            continue;
        }
        $filterSlugs[] = $cms_comparison_filter_slug($ca);
    }
    $filterSlugs = array_values(array_unique(array_filter($filterSlugs, static function ($s): bool {
        return $s !== '';
    })));

    $link = trim((string) ($row['link'] ?? ''));
    if ($link === '') {
        $link = '#';
    }
    $linkDownloadName = '';
    if ($link !== '' && $link !== '#') {
        $linkDownloadName = trim((string) ($row['download_filename'] ?? $row['link_filename'] ?? ''));
        if ($linkDownloadName === '') {
            $path = parse_url($link, PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $b = basename($path);
                if ($b !== '' && $b !== '/') {
                    $linkDownloadName = $b;
                }
            }
        }
    }

    return [
        'title'                 => (string) ($row['title'] ?? ''),
        'description'           => (string) ($row['description'] ?? ''),
        'label'                 => trim((string) ($row['label'] ?? '')),
        'slug'             => trim((string) ($row['slug'] ?? '')),
        'feature'               => $feature,
        'rating_display'        => $ratingDisplay,
        'performance'           => $perf,
        'performance_label'     => $perf . '/100',
        'performance_bar_class' => $barClass,
        'performance_user'      => trim((string) ($row['performance_user'] ?? '')),
        'link'                  => $link,
        'link_download_name'    => $linkDownloadName,
        'pro_items'             => $proItems,
        'cons_items'            => $consItems,
        'category_filter_slugs' => $filterSlugs,
    ];
};

$cms_cards_all = [];
foreach ($cms_posts_rows as $row) {
    if (!is_array($row)) {
        continue;
    }
    $cms_cards_all[] = $cms_normalize_card($row);
}

$cms_by_panel = ['all' => $cms_cards_all];
foreach ($__cms_cats as $c) {
    $fk = $cms_comparison_filter_slug($c);
    $cms_by_panel[$fk] = array_values(array_filter($cms_cards_all, static function (array $card) use ($fk): bool {
        return in_array($fk, $card['category_filter_slugs'] ?? [], true);
    }));
}

$cms_empty_all_msg = function_exists('__')
    ? (string) __('cms_comparison_empty_all')
    : 'No CMS entries to compare yet.';
$cms_grid_class = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-4 xl:gap-10';
?>
<!-- CMS COMPARISON CARDS SECTION (vanilla JS, không phụ thuộc Alpine) -->
<section id="cms-comparison-section" class="bg-gray-50 py-12 sm:py-24">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filter Tabs: h2 không được đặt trong <button> (HTML); nhóm nút dùng aria-labelledby -->
        <div class="mb-8 w-full max-w-[500px] mx-auto">
           
            <div role="group" aria-labelledby="cms-comparison-filters-heading"
                class="flex flex-wrap justify-center gap-3 bg-home-surface-light rounded-home-lg w-full px-1.5 py-1.5 border border-gray-200">
            <button type="button" data-cms-filter="all" aria-pressed="true"
                class="<?php echo e($cms_btn_active); ?>">
              <h2><?php echo e(function_exists('__') ? __('All CMS') : 'All CMS'); ?></h2>  
            </button>
            <?php foreach ($__cms_cats as $c) :
                $fk = $cms_comparison_filter_slug($c);
                $nm = (string) ($c['name'] ?? $fk);
                ?>
            <button type="button" data-cms-filter="<?php echo e($fk); ?>" aria-pressed="false"
                class="<?php echo e($cms_btn_inactive); ?>">
                <?php echo e($nm); ?>
            </button>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab Content: All CMS -->
        <div data-cms-content="all" class="cms-filter-panel">
            <div class="<?php echo e($cms_grid_class); ?>">
                <?php if ($cms_by_panel['all'] === []): ?>
                <p class="text-gray-500 text-center col-span-full py-12 font-plus">
                    <?php echo e($cms_empty_all_msg); ?>
                </p>
                <?php else: ?>
                    <?php foreach ($cms_by_panel['all'] as $card) : ?>
                        <?php echo View::include('parts/cms/_cms_comparison_card', ['card' => $card]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                            </div>
                            </div>

        <?php foreach ($__cms_cats as $c) :
            $fk = $cms_comparison_filter_slug($c);
            $nm = (string) ($c['name'] ?? $fk);
            $panelList = $cms_by_panel[$fk] ?? [];
            $emptyCatMsg = function_exists('__')
                ? sprintf((string) __('cms_comparison_category_empty_named'), $nm)
                : sprintf('No %s CMS available at this time.', $nm);
            ?>
        <div data-cms-content="<?php echo e($fk); ?>" class="cms-filter-panel" hidden>
            <div class="<?php echo e($cms_grid_class); ?>">
                <?php if ($panelList === []): ?>
                <p class="text-gray-500 text-center col-span-full py-12 font-plus">
                    <?php echo e($emptyCatMsg); ?>
                </p>
                <?php else: ?>
                    <?php foreach ($panelList as $card) : ?>
                        <?php echo View::include('parts/cms/_cms_comparison_card', ['card' => $card]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                            </div>
                            </div>
        <?php endforeach; ?>
    </div>
    <script>
    (function() {
        var section = document.getElementById('cms-comparison-section');
        if (!section) return;
        var activeClasses = ['bg-home-primary', 'border-home-primary', 'text-white', 'rounded-home-md'];
        var inactiveClasses = ['text-gray-600', 'hover:text-gray-900', 'rounded-home-md'];
        section.querySelectorAll('.cms-filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var filter = this.getAttribute('data-cms-filter');
                section.querySelectorAll('.cms-filter-btn').forEach(function(b) {
                    b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                    activeClasses.forEach(function(c) { b.classList.remove(c); });
                    inactiveClasses.forEach(function(c) { b.classList.remove(c); });
                    if (b === btn) { activeClasses.forEach(function(c) { b.classList.add(c); }); }
                    else { inactiveClasses.forEach(function(c) { b.classList.add(c); }); }
                });
                section.querySelectorAll('.cms-filter-panel').forEach(function(panel) {
                    panel.hidden = panel.getAttribute('data-cms-content') !== filter;
                });
            });
        });
    })();
    </script>
</section>
