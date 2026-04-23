<?php

/**
 * Badge Tailwind classes for blog category (aligned with blog listing).
 */
if (!function_exists('blog_category_badge_default')) {
    function blog_category_badge_default(): string
    {
        return 'bg-gray-100 text-gray-700';
    }
}

if (!function_exists('blog_category_badge_classes')) {
    function blog_category_badge_classes(string $name, string $catSlug): string
    {
        $blog_category_default_style = blog_category_badge_default();
        $blog_category_styles = [
            'Academy Series' => 'bg-[#ECE4F6] text-[#9747FF]',
            'Chuỗi học tập'   => 'bg-[#ECE4F6] text-[#9747FF]',
            'blog'            => 'bg-home-success-20 text-home-success-on-mint',
            'Tutorial'        => 'bg-home-success-20 text-home-success-on-mint',
            'Tutorials'       => 'bg-home-success-20 text-home-success-on-mint',
            'Hướng Dẫn'       => 'bg-home-success-20 text-home-success-on-mint',
            'Hướng dẫn'       => 'bg-home-success-20 text-home-success-on-mint',
            'Community'       => 'bg-[#F5E7E0] text-[#ED661D]',
            'Cộng Đồng'       => 'bg-[#F5E7E0] text-[#ED661D]',
            'Updates'         => 'bg-[#E0EEF5] text-home-primary',
            'Thêm Mới'        => 'bg-[#E0EEF5] text-home-primary',
            'Thêm mới'        => 'bg-[#E0EEF5] text-home-primary',
            'Cập nhật'        => 'bg-[#E0EEF5] text-home-primary',
            'Tin tức'         => 'bg-[#E0EEF5] text-home-primary',
            'Tin Tức'         => 'bg-[#E0EEF5] text-home-primary',
            'Cập nhật mới'    => 'bg-[#E0EEF5] text-home-primary',
        ];
        $blog_style_tutorial = 'bg-home-success-20 text-home-success-on-mint';
        $blog_style_updates = 'bg-[#E0EEF5] text-home-primary';
        $blog_style_community = 'bg-[#F5E7E0] text-[#ED661D]';
        $blog_style_academy = 'bg-[#ECE4F6] text-[#9747FF]';
        $blog_category_styles_by_slug = [
            'tutorial'       => $blog_style_tutorial,
            'blog'           => $blog_style_tutorial,
            'updates'        => $blog_style_updates,
            'community'      => $blog_style_community,
            'academy-series' => $blog_style_academy,
            'huong-dan'      => $blog_style_tutorial,
            'huongdan'       => $blog_style_tutorial,
            'them-moi'       => $blog_style_updates,
            'themmoi'        => $blog_style_updates,
            'cap-nhat'       => $blog_style_updates,
            'capnhat'        => $blog_style_updates,
            'tin-tuc'        => $blog_style_updates,
            'tintuc'         => $blog_style_updates,
            'cong-dong'      => $blog_style_community,
            'congdong'       => $blog_style_community,
            'chuoi-hoc-tap'  => $blog_style_academy,
            'chuoi-hoc-tap-series' => $blog_style_academy,
        ];

        $resolveByName = static function (string $n) use ($blog_category_styles, $blog_category_default_style): string {
            $n = trim(strip_tags(html_entity_decode($n, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($n === '') {
                return $blog_category_default_style;
            }
            $n = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $n) ?? $n;
            if (class_exists(\Normalizer::class, false)) {
                $norm = \Normalizer::normalize($n, \Normalizer::FORM_C);
                if (is_string($norm) && $norm !== '') {
                    $n = $norm;
                }
            }
            if (isset($blog_category_styles[$n])) {
                return $blog_category_styles[$n];
            }
            if (function_exists('mb_strtolower')) {
                $needle = mb_strtolower($n, 'UTF-8');
                foreach ($blog_category_styles as $label => $classes) {
                    if (mb_strtolower(trim((string) $label), 'UTF-8') === $needle) {
                        return $classes;
                    }
                }
            }

            return $blog_category_default_style;
        };

        $byName = $resolveByName($name);
        if ($byName !== $blog_category_default_style) {
            return $byName;
        }
        $slug = strtolower(trim($catSlug));
        $slug = str_replace('_', '-', $slug);
        if ($slug !== '' && isset($blog_category_styles_by_slug[$slug])) {
            return $blog_category_styles_by_slug[$slug];
        }

        return $blog_category_default_style;
    }
}
