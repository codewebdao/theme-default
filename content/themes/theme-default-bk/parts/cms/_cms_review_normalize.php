<?php
/**
 * Chuẩn hóa một row reviews giống logic trong cms-comparison.php ($cms_normalize_card).
 * Dùng cho trang chi tiết và có thể gọi lại từ nơi khác.
 */

if (!function_exists('cms_normalize_reviews_row')) {
    function cms_normalize_reviews_row(array $row): array
    {
        $cms_comparison_filter_slug = static function (array $c): string {
            $s = strtolower(trim((string) ($c['slug'] ?? '')));
            $s = preg_replace('/[^a-z0-9\-_]+/', '-', $s);
            $s = trim($s, '-');

            return $s !== '' ? $s : 'cat-' . substr(hash('sha256', (string) ($c['id'] ?? $c['name'] ?? '')), 0, 10);
        };

        $cms_term_row_to_array = static function ($item): array {
            if ($item === null) {
                return [];
            }
            if (is_array($item)) {
                return $item;
            }
            if (is_object($item) && method_exists($item, 'toArray')) {
                return (array) $item->toArray();
            }
            $j = is_object($item) ? json_decode(json_encode($item), true) : [];

            return is_array($j) ? $j : [];
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
    }
}
