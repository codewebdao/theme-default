<?php
/**
 * Chuẩn bị sidebar PHP Tutorial: terms (posttype tutorial, taxonomy category) + bài viết group theo cột phase.
 * Trả về biến: $php_tutorial_phases, $php_tutorial_topic_to_phase, $php_tutorial_topic_breadcrumb,
 * $php_tutorial_valid_topics, $php_tutorial_default_topic, $php_tutorial_default_phase, $php_tutorial_breadcrumb_fallback,
 * $php_tutorial_lesson_titles,
 * $php_tutorial_lesson_descriptions, $php_tutorial_lesson_contents.
 *
 * CMS: terms bảng fast_terms — posttype = tutorial, type = category (giống blog).
 * Bài: fast_tutorial_{lang} — ưu tiên gán theo category trên bài (như admin: Newbie, Intermediate…);
 * fallback cột phase khớp name term. Tiêu đề nhóm ưu tiên text phase trên bài (Phase 1: …) nếu có.
 * Breadcrumb cột giữa: cột phase trên từng bài ($php_tutorial_topic_breadcrumb), không dùng tên category.
 */
$lang = defined('APP_LANG') ? APP_LANG : '';

/**
 * Cột `type` trên bảng bài posttype `tutorial`: trang tutorial (archive-tutorial) chỉ lấy `tutorial`.
 * Trang Usage Guide dùng cùng posttype/bảng với `usage_guide` (xem page-usage-guide.php).
 */
$php_tutorial_post_row_type = 'tutorial';

$tut_style_tutorial = 'bg-[#08e779] text-white';
$tut_style_updates = 'bg-[#0D8BD9] text-white';
$tut_style_community = 'bg-[#ed6821] text-white';
$tut_style_academy = 'bg-[#9747FF] text-white';
/** Advanced: xanh (primary / blue), không dùng tím academy */
$tut_style_advanced = 'bg-[#2f63f1] text-white shadow-sm';

$tutorial_category_styles = [
    'Academy Series'        => $tut_style_academy,
    'Chuỗi học tập'         => $tut_style_academy,
    'Tutorials'             => $tut_style_tutorial,
    'Tutorial'              => $tut_style_tutorial,
    'Hướng Dẫn'             => $tut_style_tutorial,
    'Hướng dẫn'             => $tut_style_tutorial,
    'Community'             => $tut_style_community,
    'Cộng Đồng'             => $tut_style_community,
    'Cộng đồng'             => $tut_style_community,
    'Updates'               => $tut_style_updates,
    'Phase 1: PHP Basics'   => $tut_style_tutorial,
    'Phase 2: Advanced PHP' => $tut_style_academy,
    'Phase 3: Build News Site' => $tut_style_community,
    /** Phase 4 = Advanced (E-Commerce), không dùng màu Updates */
    'Phase 4: Build E-Commerce' => $tut_style_advanced,
    'Newbie'                  => $tut_style_tutorial,
    'Intermediate'            => $tut_style_academy,
    'Advanced'                => $tut_style_advanced,
    'Project'                 => $tut_style_community,
    'Người mới'               => $tut_style_tutorial,
    'Mới bắt đầu'             => $tut_style_tutorial,
    'Cơ bản'                  => $tut_style_tutorial,
    'Trung cấp'               => $tut_style_academy,
    'Nâng cao'                => $tut_style_advanced,
    'Dự án'                   => $tut_style_community,
    'Giai đoạn 1: PHP cơ bản'     => $tut_style_tutorial,
    'Giai đoạn 2: PHP nâng cao'   => $tut_style_academy,
    'Giai đoạn 3: Xây site tin tức' => $tut_style_community,
    'Giai đoạn 4: Thương mại điện tử' => $tut_style_advanced,
    'Pha 1: PHP cơ bản'           => $tut_style_tutorial,
    'Pha 2: PHP nâng cao'         => $tut_style_academy,
    'Pha 3: Xây site tin tức'     => $tut_style_community,
    'Pha 4: Thương mại điện tử'   => $tut_style_advanced,
];
$tutorial_category_default_style = 'bg-gray-100 text-gray-700';
$tutorial_category_styles_by_slug = [
    'tutorial'                  => $tut_style_tutorial,
    'tutorials'                 => $tut_style_tutorial,
    'updates'                   => $tut_style_updates,
    'academy-series'            => $tut_style_academy,
    'community'                 => $tut_style_community,
    'phase-1-php-basics'        => $tut_style_tutorial,
    'phase-2-advanced-php'      => $tut_style_academy,
    'phase-3-build-news-site'   => $tut_style_community,
    'phase-4-build-e-commerce'  => $tut_style_advanced,
    'huong-dan'                 => $tut_style_tutorial,
    'newbie'                    => $tut_style_tutorial,
    'intermediate'              => $tut_style_academy,
    'advanced'                  => $tut_style_advanced,
    'project'                   => $tut_style_community,
    'beginner'                  => $tut_style_tutorial,
    'nguoi-moi'                 => $tut_style_tutorial,
    'moi-bat-dau'               => $tut_style_tutorial,
    'co-ban'                    => $tut_style_tutorial,
    'coban'                     => $tut_style_tutorial,
    'trung-cap'                 => $tut_style_academy,
    'trungcap'                  => $tut_style_academy,
    'nang-cao'                  => $tut_style_advanced,
    'nangcao'                   => $tut_style_advanced,
    'du-an'                     => $tut_style_community,
    'duan'                      => $tut_style_community,
    'giai-doan-1-php-co-ban'    => $tut_style_tutorial,
    'giai-doan-2-php-nang-cao'  => $tut_style_academy,
    'giai-doan-3-xay-site-tin' => $tut_style_community,
    'giai-doan-4-thuong-mai-dien-tu' => $tut_style_advanced,
    'pha-1-php-co-ban'          => $tut_style_tutorial,
    'pha-2-php-nang-cao'        => $tut_style_academy,
    'pha-3-xay-site-tin-tuc'    => $tut_style_community,
    'pha-4-thuong-mai-dien-tu'  => $tut_style_advanced,
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

$php_tutorial_term_to_array = static function ($item): array {
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

$php_tutorial_normalize_phase = static function (string $s): string {
    $s = html_entity_decode(trim($s), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

    return $s;
};

$php_tutorial_clean_title = static function (string $title): string {
    $title = trim($title);
    $prev = '';
    while ($title !== $prev) {
        $prev = $title;
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = trim($title);
    }

    return $title;
};

$php_tutorial_lower = static function (string $s): string {
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
};

/**
 * Phase 1–4 trong roadmap CMS (slug phase-N… hoặc tên "Phase N: …").
 * 0 = không phải phase số — dùng để sort sidebar đúng 1→4, không theo track màu.
 */
$php_tutorial_roadmap_phase_number = static function (string $termName, string $termSlug) use ($php_tutorial_lower, $php_tutorial_normalize_phase): int {
    $slug = $php_tutorial_lower(str_replace('_', '-', trim($termSlug)));
    $name = $php_tutorial_normalize_phase($termName);

    for ($n = 4; $n >= 1; $n--) {
        foreach (['phase-', 'giai-doan-', 'pha-'] as $p) {
            $prefix = $p . $n;
            if ($slug === $prefix || ($slug !== '' && str_starts_with($slug, $prefix . '-'))) {
                return $n;
            }
        }
    }
    if ($name !== '') {
        if (preg_match('/phase\s*4\s*:|giai\s*đoạn\s*4\s*:|pha\s*4\s*:/u', $name)) {
            return 4;
        }
        if (preg_match('/phase\s*3\s*:|giai\s*đoạn\s*3\s*:|pha\s*3\s*:/u', $name)) {
            return 3;
        }
        if (preg_match('/phase\s*2\s*:|giai\s*đoạn\s*2\s*:|pha\s*2\s*:/u', $name)) {
            return 2;
        }
        if (preg_match('/phase\s*1\s*:|giai\s*đoạn\s*1\s*:|pha\s*1\s*:/u', $name)) {
            return 1;
        }
        /** Tên đầy đủ / rút gọn (EN/VI, CMS không gắn số phase) — 4→1 để tránh chồng chữ */
        $nl = $php_tutorial_lower($name);
        if (preg_match('/e-?commerce|thương\s*mại\s*điện\s*tử|cửa\s*hàng\s*trực\s*tuyến|mua\s*sắm\s*online/u', $nl)) {
            return 4;
        }
        if (preg_match('/build\s+news\s+site|xây\s+dựng\s+site\s+tin|site\s+tin\s+tức|tin\s+tức|báo\s+điện\s+tử|website\s+tin/u', $nl)) {
            return 3;
        }
        /** PHP nâng cao = phase 2 (trung cấp), không gộp với nhãn "Nâng cao" phase 4 */
        if (preg_match('/advanced\s*php|php\s*nâng\s*cao|nâng\s*cao\s*php/u', $nl)) {
            return 2;
        }
        if (preg_match('/trung\s*cấp|trung\s*cap/u', $nl)) {
            return 2;
        }
        if (preg_match('/dự\s*án(\s+xây|\s+website)?|du\s*an(\s+xay)?/u', $nl)) {
            return 3;
        }
        if (preg_match('/\bnâng\s*cao\b/u', $name) && !preg_match('/php\s*nâng\s*cao|nâng\s*cao\s*php/u', $nl)) {
            return 4;
        }
        if (preg_match('/php\s*basics?|php\s*cơ\s*bản|cơ\s*bản\s*php|người\s*mới|mới\s*bắt\s*đầu/u', $nl)) {
            return 1;
        }
    }

    return 0;
};

/**
 * Thứ tự hiển thị sidebar 1…4 = Phase 1…4 (Project trước Advanced).
 * Track nội bộ: 0 newbie, 1 intermediate, 2 advanced, 3 project — map sang 1,2,3,4.
 */
$php_tutorial_track_to_sidebar_position = static function (int $track): int {
    if ($track < 0) {
        return 0;
    }
    if ($track === 0) {
        return 1;
    }
    if ($track === 1) {
        return 2;
    }
    if ($track === 3) {
        return 3;
    }
    if ($track === 2) {
        return 4;
    }

    return 0;
};

/**
 * Track lộ trình: 0 newbie, 1 intermediate, 2 advanced, 3 project; -1 không nhận diện.
 * Roadmap 4 phase: 1→Newbie, 2→Intermediate, 3→Project, 4→Advanced (E-Commerce).
 */
$php_tutorial_curriculum_track = static function (string $termName, string $termSlug) use (
    $php_tutorial_lower,
    $php_tutorial_normalize_phase,
    $php_tutorial_roadmap_phase_number
): int {
    $pn = $php_tutorial_roadmap_phase_number($termName, $termSlug);
    if ($pn === 1) {
        return 0;
    }
    if ($pn === 2) {
        return 1;
    }
    if ($pn === 3) {
        return 3;
    }
    if ($pn === 4) {
        return 2;
    }

    $slug = $php_tutorial_lower(str_replace('_', '-', trim($termSlug)));
    $name = $php_tutorial_normalize_phase($termName);
    $nl = $php_tutorial_lower($name);

    static $slugExact = [
        'newbie'       => 0,
        'beginner'     => 0,
        'intermediate' => 1,
        'advanced'     => 2,
        'project'      => 3,
    ];
    if (isset($slugExact[$slug])) {
        return (int) $slugExact[$slug];
    }

    $slugVi = [
        'nguoi-moi'   => 0,
        'moi-bat-dau' => 0,
        'co-ban'      => 0,
        'coban'       => 0,
        'trung-cap'   => 1,
        'trungcap'    => 1,
        'nang-cao'    => 2,
        'nangcao'     => 2,
        'du-an'       => 3,
        'duan'        => 3,
    ];
    if (isset($slugVi[$slug])) {
        return (int) $slugVi[$slug];
    }
    /** Slug "php-nang-cao" = phase 2 (trung cấp), không khớp nhầm suffix "nang-cao" → advanced */
    if ($slug !== '' && preg_match('/php-nang-cao|php-nang-cap|nang-cao-php|nang-cap-php/u', $slug)) {
        return 1;
    }
    foreach ($slugVi as $frag => $tr) {
        if ($slug !== '' && strpos($slug, $frag) !== false) {
            return (int) $tr;
        }
    }

    if ($name !== '') {
        if (preg_match('/người mới|mới bắt đầu|cơ bản/u', $name)
            || strpos($nl, 'newbie') !== false
            || strpos($nl, 'beginner') !== false) {
            return 0;
        }
        /** Phase 2: Advanced PHP / PHP nâng cao — trước nhánh "nâng cao" / "advanced" tổng quát */
        if (preg_match('/advanced\s*php/u', $nl) && !preg_match('/phase\s*4\s*:|giai\s*đoạn\s*4\s*:|pha\s*4\s*:/u', $name)) {
            return 1;
        }
        if (preg_match('/php\s*nâng\s*cao|nâng\s*cao\s*php/u', $nl)
            && !preg_match('/giai\s*đoạn\s*4\s*:|pha\s*4\s*:/u', $name)) {
            return 1;
        }
        if (preg_match('/trung\s*cấp/u', $name) || strpos($nl, 'intermediate') !== false) {
            return 1;
        }
        if (preg_match('/build\s+news\s+site|xây\s+dựng\s+site\s+tin|site\s+tin\s+tức|tin\s+tức|báo\s+điện\s+tử/u', $nl)) {
            return 3;
        }
        if (preg_match('/build\s+e-?commerce|e-?commerce\b|thương\s*mại\s*điện\s*tử|cửa\s*hàng\s*trực\s*tuyến/u', $nl)) {
            return 2;
        }
        if (preg_match('/dự\s*án/u', $name) || strpos($nl, 'project') !== false) {
            return 3;
        }
        /** "Nâng cao" đơn (tier cuối) = advanced phase 4; đã loại "PHP nâng cao" ở trên */
        if (preg_match('/\bnâng\s*cao\b/u', $name)
            || (strpos($nl, 'advanced') !== false && strpos($nl, 'intermediate') === false)) {
            return 2;
        }
    }

    return -1;
};

/** Map track → icon variant trong sidebar (advanced = 3, project = 2). */
$php_tutorial_track_to_icon_variant = static function (int $track): int {
    if ($track === 2) {
        return 3;
    }
    if ($track === 3) {
        return 2;
    }
    if ($track === 0 || $track === 1) {
        return $track;
    }

    return -1;
};

/**
 * Icon roadmap: theo track (VI/EN + slug); không nhận diện thì crc32 slug để ổn định.
 */
$php_tutorial_sidebar_icon_variant = static function (string $termName, string $termSlug) use (
    $php_tutorial_lower,
    $php_tutorial_curriculum_track,
    $php_tutorial_track_to_icon_variant
): int {
    $tr = $php_tutorial_curriculum_track($termName, $termSlug);
    if ($tr >= 0) {
        $v = $php_tutorial_track_to_icon_variant($tr);
        if ($v >= 0) {
            return $v;
        }
    }
    $ts = $php_tutorial_lower(str_replace('_', '-', trim($termSlug)));
    $slugKey = $ts !== '' ? $ts : $php_tutorial_lower(trim($termName));

    return (int) (abs((int) crc32($slugKey)) % 4);
};

/** Màu badge: map + slug + tên; nếu vẫn default thì theo track (VI/EN). */
$php_tutorial_term_badge_style = static function (string $termName, string $termSlug) use (
    $tutorial_resolve_category_style_full,
    $tutorial_category_default_style,
    $php_tutorial_curriculum_track,
    $tut_style_tutorial,
    $tut_style_academy,
    $tut_style_advanced,
    $tut_style_community
): string {
    $s = $tutorial_resolve_category_style_full($termName, $termSlug);
    if ($s !== $tutorial_category_default_style) {
        return $s;
    }
    $tr = $php_tutorial_curriculum_track($termName, $termSlug);
    switch ($tr) {
        case 0:
            return $tut_style_tutorial;
        case 1:
            return $tut_style_academy;
        case 2:
            return $tut_style_advanced;
        case 3:
            return $tut_style_community;
        default:
            return $tutorial_category_default_style;
    }
};

/**
 * Badge sidebar: tên category CMS (viết hoa); icon khớp 4 nhóm CMS.
 *
 * @return array{badge: string, variant: int, duration: string}
 */
$php_tutorial_sidebar_level_meta = static function (string $termName, string $termSlug) use ($php_tutorial_lower, $php_tutorial_sidebar_icon_variant): array {
    $name = trim($termName);
    if ($name === '') {
        $fromSlug = trim(str_replace(['-', '_'], ' ', $termSlug));
        $name = $fromSlug !== '' ? $fromSlug : 'Course';
    }
    $badge = function_exists('mb_strtoupper') ? mb_strtoupper($name, 'UTF-8') : strtoupper($name);
    if (function_exists('mb_strlen') ? mb_strlen($badge, 'UTF-8') > 22 : strlen($badge) > 22) {
        $badge = (function_exists('mb_substr') ? mb_substr($badge, 0, 19, 'UTF-8') : substr($badge, 0, 19)) . '…';
    }
    $variant = $php_tutorial_sidebar_icon_variant($termName, $termSlug);

    return ['badge' => $badge, 'variant' => $variant, 'duration' => '1-2 WEEKS'];
};

$php_tutorial_categories_list_normalize = static function ($cats): array {
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

$php_tutorial_posts_res = [];
if (function_exists('get_posts')) {
    try {
        $php_tutorial_posts_res = get_posts([
            'posttype'        => 'tutorial',
            'post_status'     => 'active',
            'lang'            => $lang,
            'perPage'         => 500,
            'orderby'         => 'id',
            'order'           => 'ASC',
            'with_categories' => true,
            'filters'         => [
                ['type_option', $php_tutorial_post_row_type],
            ],
        ]) ?: [];
    } catch (\Throwable $e) {
        $php_tutorial_posts_res = [];
    }
}
$php_tutorial_rows = (is_array($php_tutorial_posts_res) && isset($php_tutorial_posts_res['data']) && is_array($php_tutorial_posts_res['data']))
    ? $php_tutorial_posts_res['data']
    : [];

$php_tutorial_terms_raw = [];
if (function_exists('get_terms')) {
    try {
        $php_tutorial_terms_raw = get_terms([
            'posttype' => 'tutorial',
            'taxonomy' => 'category',
            'lang'     => $lang,
            'orderby'  => 'id',
            'order'    => 'ASC',
        ]) ?: [];
    } catch (\Throwable $e) {
        $php_tutorial_terms_raw = [];
    }
}
$php_tutorial_categories = [];
if (is_array($php_tutorial_terms_raw) && isset($php_tutorial_terms_raw['data']) && is_array($php_tutorial_terms_raw['data'])) {
    $php_tutorial_categories = $php_tutorial_terms_raw['data'];
} elseif (is_array($php_tutorial_terms_raw)) {
    $php_tutorial_categories = $php_tutorial_terms_raw;
}

$byPhase = [];
foreach ($php_tutorial_rows as $row) {
    if (is_object($row)) {
        $row = method_exists($row, 'toArray') ? (array) $row->toArray() : (array) json_decode(json_encode($row), true);
    }
    if (!is_array($row)) {
        continue;
    }
    $ph = $php_tutorial_normalize_phase((string) ($row['phase'] ?? ''));
    if ($ph === '') {
        continue;
    }
    if (!isset($byPhase[$ph])) {
        $byPhase[$ph] = [];
    }
    $byPhase[$ph][] = $row;
}
foreach ($byPhase as $k => $items) {
    usort($byPhase[$k], static function ($a, $b): int {
        $oa = (int) ($a['order_type'] ?? 0);
        $ob = (int) ($b['order_type'] ?? 0);
        if ($oa !== $ob) {
            return $oa <=> $ob;
        }

        return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
    });
}

$php_tutorial_sort_lesson_rows = static function (array $items): array {
    usort($items, static function ($a, $b): int {
        $oa = (int) ($a['order_type'] ?? 0);
        $ob = (int) ($b['order_type'] ?? 0);
        if ($oa !== $ob) {
            return $oa <=> $ob;
        }

        return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
    });

    return $items;
};

$php_tutorial_category_terms = [];
foreach ($php_tutorial_categories as $raw) {
    $t = $php_tutorial_term_to_array($raw);
    if ($t === []) {
        continue;
    }
    $php_tutorial_category_terms[] = $t;
}

if ($php_tutorial_category_terms === [] && $byPhase !== []) {
    $autoId = 0;
    foreach (array_keys($byPhase) as $phName) {
        ++$autoId;
        $php_tutorial_category_terms[] = [
            'id'          => $autoId,
            'name'        => $phName,
            'slug'        => 'phase-' . $autoId,
            'description' => '',
        ];
    }
}

usort($php_tutorial_category_terms, static function ($a, $b) use (
    $php_tutorial_term_to_array,
    $php_tutorial_roadmap_phase_number,
    $php_tutorial_curriculum_track,
    $php_tutorial_track_to_sidebar_position,
    $php_tutorial_lower
): int {
    $ta = $php_tutorial_term_to_array($a);
    $tb = $php_tutorial_term_to_array($b);
    if ($ta === [] && $tb === []) {
        return 0;
    }
    if ($ta === []) {
        return 1;
    }
    if ($tb === []) {
        return -1;
    }
    $na = (string) ($ta['name'] ?? '');
    $nb = (string) ($tb['name'] ?? '');
    $sa = trim((string) ($ta['slug'] ?? ''));
    $sb = trim((string) ($tb['slug'] ?? ''));
    $sortPos = static function (string $name, string $slug) use (
        $php_tutorial_roadmap_phase_number,
        $php_tutorial_curriculum_track,
        $php_tutorial_track_to_sidebar_position
    ): int {
        $p = $php_tutorial_roadmap_phase_number($name, $slug);
        if ($p >= 1 && $p <= 4) {
            return $p;
        }

        return $php_tutorial_track_to_sidebar_position($php_tutorial_curriculum_track($name, $slug));
    };
    $ka = $sortPos($na, $sa);
    $kb = $sortPos($nb, $sb);
    if ($ka > 0 && $kb > 0 && $ka !== $kb) {
        return $ka <=> $kb;
    }
    if ($ka > 0 && $kb <= 0) {
        return -1;
    }
    if ($kb > 0 && $ka <= 0) {
        return 1;
    }
    $ordA = (int) ($ta['term_order'] ?? $ta['order'] ?? $ta['menu_order'] ?? 0);
    $ordB = (int) ($tb['term_order'] ?? $tb['order'] ?? $tb['menu_order'] ?? 0);
    if ($ordA !== $ordB) {
        return $ordA <=> $ordB;
    }
    $ida = (int) ($ta['id'] ?? $ta['term_id'] ?? 0);
    $idb = (int) ($tb['id'] ?? $tb['term_id'] ?? 0);
    if ($ida !== $idb) {
        return $ida <=> $idb;
    }

    return strcmp($php_tutorial_lower($na), $php_tutorial_lower($nb));
});

/** @var array<string|int, array<int, array<string, mixed>>> */
$php_tutorial_posts_by_bucket = [];

foreach ($php_tutorial_rows as $row) {
    if (is_object($row)) {
        $row = method_exists($row, 'toArray') ? (array) $row->toArray() : (array) json_decode(json_encode($row), true);
    }
    if (!is_array($row)) {
        continue;
    }
    $bucketKey = null;
    $catsRaw = $php_tutorial_categories_list_normalize($row['categories'] ?? []);
    if ($catsRaw !== []) {
        $firstCat = $php_tutorial_term_to_array($catsRaw[0]);
        $cid = (int) ($firstCat['id'] ?? $firstCat['term_id'] ?? 0);
        $cname = $php_tutorial_normalize_phase((string) ($firstCat['name'] ?? $firstCat['title'] ?? $firstCat['label'] ?? ''));
        $cslug = strtolower(trim((string) ($firstCat['slug'] ?? $firstCat['term_slug'] ?? '')));
        $cslug = str_replace('_', '-', $cslug);
        foreach ($php_tutorial_category_terms as $ti => $tx) {
            $tx = $php_tutorial_term_to_array($tx);
            if ($tx === []) {
                continue;
            }
            $txid = (int) ($tx['id'] ?? 0);
            $txname = $php_tutorial_normalize_phase((string) ($tx['name'] ?? ''));
            $txslug = strtolower(str_replace('_', '-', trim((string) ($tx['slug'] ?? ''))));
            if ($cid > 0 && $txid > 0 && $txid === $cid) {
                $bucketKey = $txid;
                break;
            }
            if ($cname !== '' && $txname !== '' && $php_tutorial_lower($cname) === $php_tutorial_lower($txname)) {
                $bucketKey = $txid > 0 ? $txid : ('t' . $ti);
                break;
            }
            if ($cslug !== '' && $txslug !== '' && $cslug === $txslug) {
                $bucketKey = $txid > 0 ? $txid : ('t' . $ti);
                break;
            }
        }
    }
    if ($bucketKey === null) {
        $ph = $php_tutorial_normalize_phase((string) ($row['phase'] ?? ''));
        if ($ph !== '') {
            foreach ($php_tutorial_category_terms as $ti => $tx) {
                $tx = $php_tutorial_term_to_array($tx);
                $txid = (int) ($tx['id'] ?? 0);
                $txname = $php_tutorial_normalize_phase((string) ($tx['name'] ?? ''));
                if ($txname !== '' && $php_tutorial_lower($txname) === $php_tutorial_lower($ph)) {
                    $bucketKey = $txid > 0 ? $txid : ('t' . $ti);
                    break;
                }
            }
        }
    }
    if ($bucketKey !== null) {
        if (!isset($php_tutorial_posts_by_bucket[$bucketKey])) {
            $php_tutorial_posts_by_bucket[$bucketKey] = [];
        }
        $php_tutorial_posts_by_bucket[$bucketKey][] = $row;
    }
}
foreach ($php_tutorial_posts_by_bucket as $bk => $bucket) {
    $php_tutorial_posts_by_bucket[$bk] = $php_tutorial_sort_lesson_rows($bucket);
}

$php_tutorial_phases = [];
$php_tutorial_topic_to_phase = [];
$php_tutorial_topic_breadcrumb = [];
$php_tutorial_lesson_titles = [];
$php_tutorial_lesson_descriptions = [];
$php_tutorial_lesson_contents = [];
$php_tutorial_valid_topics = [];

$phaseOrdinal = 0;
foreach ($php_tutorial_category_terms as $ti => $t) {
    $tid = (int) ($t['id'] ?? 0);
    $termName = $php_tutorial_normalize_phase((string) ($t['name'] ?? ''));
    $termSlug = trim((string) ($t['slug'] ?? ''));
    if ($termName === '') {
        continue;
    }
    $bucketKey = $tid > 0 ? $tid : ('t' . $ti);
    $rowsForTerm = $php_tutorial_posts_by_bucket[$bucketKey] ?? [];
    if ($rowsForTerm === []) {
        $rowsForTerm = $byPhase[$termName] ?? [];
    }

    $alpineId = 'p' . ($tid > 0 ? $tid : ('g' . $phaseOrdinal));

    $desc = trim((string) ($t['description'] ?? ''));

    $title_display = $termName;
    $uniqPhase = [];
    foreach ($rowsForTerm as $rr) {
        $pp = $php_tutorial_normalize_phase((string) ($rr['phase'] ?? ''));
        if ($pp !== '') {
            $uniqPhase[$pp] = true;
        }
    }
    if (count($uniqPhase) === 1) {
        $title_display = (string) array_key_first($uniqPhase);
    }

    $levelMeta = $php_tutorial_sidebar_level_meta($termName, $termSlug);
    $badgeLabel = $levelMeta['badge'];
    $durationLabel = $levelMeta['duration'];
    $iconVariant = (int) $levelMeta['variant'];
    if ($desc !== '') {
        $lines = preg_split('/\r\n|\r|\n/', $desc) ?: [];
        if (isset($lines[0]) && trim($lines[0]) !== '') {
            $badgeLabel = trim($lines[0]);
        }
        if (isset($lines[1]) && trim($lines[1]) !== '') {
            $durationLabel = trim($lines[1]);
        }
    }

    $lessons = [];
    foreach ($rowsForTerm as $row) {
        $slug = trim((string) ($row['slug'] ?? ''));
        if ($slug === '' || !preg_match('/^[a-z0-9_\-]+$/i', $slug)) {
            continue;
        }
        $title = $php_tutorial_clean_title((string) ($row['title'] ?? ''));
        $lessons[] = [
            'slug'  => $slug,
            'title' => $title !== '' ? $title : $slug,
        ];
        $php_tutorial_topic_to_phase[$slug] = $alpineId;
        $rowPhase = $php_tutorial_normalize_phase((string) ($row['phase'] ?? ''));
        $php_tutorial_topic_breadcrumb[$slug] = $rowPhase !== '' ? $rowPhase : $title_display;
        $php_tutorial_lesson_titles[$slug] = $title !== '' ? $title : $slug;
        $rawDesc = (string) ($row['description'] ?? '');
        $php_tutorial_lesson_descriptions[$slug] = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($rawDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?? '');
        $php_tutorial_lesson_contents[$slug] = (string) ($row['content'] ?? '');
        $php_tutorial_valid_topics[] = $slug;
    }

    $badgeStyle = $php_tutorial_term_badge_style($termName, $termSlug);

    $php_tutorial_phases[] = [
        'alpine_id'      => $alpineId,
        'term_id'        => $tid,
        'term_name'      => $termName,
        'term_slug'      => $termSlug,
        'title_display'  => $title_display,
        'badge_label'    => $badgeLabel,
        'duration_label' => $durationLabel,
        'badge_style'    => $badgeStyle,
        'icon_variant'   => $iconVariant,
        'lessons'        => $lessons,
    ];
    ++$phaseOrdinal;
}

$php_tutorial_default_topic = 'syntax';
$php_tutorial_default_phase = 'phase1';
$php_tutorial_breadcrumb_fallback = 'PHP Tutorial';

if ($php_tutorial_valid_topics !== []) {
    $php_tutorial_valid_topics = array_values(array_unique($php_tutorial_valid_topics));
    $php_tutorial_default_topic = $php_tutorial_valid_topics[0];
    $php_tutorial_default_phase = $php_tutorial_topic_to_phase[$php_tutorial_default_topic] ?? ($php_tutorial_phases[0]['alpine_id'] ?? 'p1');
    $php_tutorial_breadcrumb_fallback = (string) ($php_tutorial_topic_breadcrumb[$php_tutorial_default_topic] ?? '');
}
