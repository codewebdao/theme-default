<?php
if (class_exists(\App\Libraries\Fastlang::class, false)) {
    $blogDetailLang = defined('APP_LANG') ? APP_LANG : 'en';
    \App\Libraries\Fastlang::load('Blog', $blogDetailLang);
    \App\Libraries\Fastlang::load('CMS', $blogDetailLang);
}
require_once __DIR__ . '/_blog_category_badge.php';
require_once __DIR__ . '/_blog_read_time.php';
$bp = isset($blog_post) && is_array($blog_post) ? $blog_post : [];
$bp_title = html_entity_decode((string) ($bp['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$bp_content = (string) ($bp['content'] ?? '');
$bp_desc = (string) ($bp['description_title'] ?? '');
$authorRow = $bp['author'] ?? [];
if (is_object($authorRow) && method_exists($authorRow, 'toArray')) {
    $authorRow = $authorRow->toArray();
}
if (!is_array($authorRow)) {
    $authorRow = [];
}
$bp_author_name = trim((string) (
    $authorRow['fullname']
    ?? $authorRow['Fullname']
    ?? $authorRow['name']
    ?? $authorRow['display_name']
    ?? $authorRow['username']
    ?? $authorRow['user_login']
    ?? $bp['username']
    ?? $bp['author_name']
    ?? $bp['author_username']
    ?? ''
));
$bp_avatar_raw = $authorRow['avatar'] ?? null;
if ($bp_avatar_raw === null || $bp_avatar_raw === '') {
    $bp_avatar_raw = $bp['avatar'] ?? null;
}
$bp_parse_img = static function ($val) {
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

/** ID user từ bài + (nếu thiếu) một lần get_posts theo id bài để lấy cột user_id/author_id; sau đó UsersModel để tên & avatar chuẩn */
$bp_post_id_for_author = (int) ($bp['id'] ?? $bp['id_main'] ?? 0);
$bp_author_user_id = (int) ($bp['user_id'] ?? $bp['author_id'] ?? $bp['created_by'] ?? $bp['creator_id'] ?? 0);
if ($bp_author_user_id < 1 && $authorRow !== [] && isset($authorRow['id'])) {
    $bp_author_user_id = (int) $authorRow['id'];
}
if ($bp_author_user_id < 1 && $bp_post_id_for_author > 0 && function_exists('get_posts')) {
    $bp_ref_status = (function_exists('HAS_GET') && HAS_GET('preview')) ? '' : 'active';
    $bp_ref = get_posts([
        'posttype'    => 'blog',
        'post__in'    => [$bp_post_id_for_author],
        'post_status' => $bp_ref_status,
        'lang'        => defined('APP_LANG') ? APP_LANG : '',
        'perPage'     => 1,
    ]);
    $bp_ref_rows = is_array($bp_ref) ? ($bp_ref['data'] ?? []) : [];
    $bp_ref_row = isset($bp_ref_rows[0]) && is_array($bp_ref_rows[0]) ? $bp_ref_rows[0] : null;
    if ($bp_ref_row !== null) {
        $bp_author_user_id = (int) (
            $bp_ref_row['user_id']
            ?? $bp_ref_row['author_id']
            ?? $bp_ref_row['created_by']
            ?? $bp_ref_row['creator_id']
            ?? 0
        );
        if ($bp_author_name === '' && trim((string) ($bp_ref_row['username'] ?? '')) !== '') {
            $bp_author_name = trim((string) $bp_ref_row['username']);
        }
    }
}
if ($bp_author_user_id > 0 && class_exists(\App\Models\UsersModel::class)) {
    $bp_user_row = (new \App\Models\UsersModel())->getUserById($bp_author_user_id);
    if (is_array($bp_user_row) && $bp_user_row !== []) {
        $bp_u_name = trim((string) ($bp_user_row['fullname'] ?? $bp_user_row['username'] ?? ''));
        if ($bp_u_name !== '') {
            $bp_author_name = $bp_u_name;
        }
        if (!empty($bp_user_row['avatar'])) {
            $bp_avatar_raw = $bp_user_row['avatar'];
        }
    }
}
if ($bp_author_name === '') {
    $bp_author_name = '—';
}
$bp_author_img_alt = ($bp_author_name !== '' && $bp_author_name !== '—') ? $bp_author_name : '';

$bp_feature = $bp_parse_img($bp['feature'] ?? null);
$bp_avatar = $bp_parse_img($bp_avatar_raw);
$bp_created = (string) ($bp['created_at'] ?? '');
$bp_ts = blog_created_at_to_unix($bp_created);
$bp_date_label = $bp_ts > 0 ? date('M j, Y', $bp_ts) : '';
$bp_home_url = base_url('', defined('APP_LANG') ? APP_LANG : '');
$bp_blog_url = base_url('blog', defined('APP_LANG') ? APP_LANG : '');
/* URL đã lẫn entity &amp; (double-encode) → không dùng href, tránh link hỏng */
$bp_blog_breadcrumb_link_ok = strpos($bp_blog_url, '&amp;') === false;
$bp_cats = [];
if (!empty($bp['categories'])) {
    $rawCats = $bp['categories'];
    if (is_string($rawCats)) {
        $d = json_decode($rawCats, true);
        $rawCats = is_array($d) ? $d : [];
    }
    if (is_object($rawCats) && method_exists($rawCats, 'toArray')) {
        $rawCats = $rawCats->toArray();
    }
    $bp_cats = is_array($rawCats) ? array_values($rawCats) : [];
}
$bp_tags = [];
$rawTags = $bp['tags'] ?? null;
if ($rawTags !== null && $rawTags !== '') {
    if (is_string($rawTags)) {
        $trim = trim($rawTags);
        $d = null;
        if ($trim !== '' && ($trim[0] === '[' || $trim[0] === '{')) {
            $d = json_decode($trim, true);
            if (!is_array($d) && function_exists('stripslashes')) {
                $d = json_decode(stripslashes($trim), true);
            }
        }
        $rawTags = is_array($d) ? $d : [];
    }
    if (is_object($rawTags) && method_exists($rawTags, 'toArray')) {
        $rawTags = $rawTags->toArray();
    }
    $bp_tags = is_array($rawTags) ? array_values($rawTags) : [];
}
$bp_note = trim((string) ($bp['note'] ?? $bp['content_note'] ?? $bp['article_note'] ?? ''));
$bp_tag_labels = [];
foreach ($bp_tags as $ti) {
    $raw = '';
    if (is_string($ti)) {
        $raw = trim($ti);
    } elseif (is_array($ti)) {
        $raw = trim((string) ($ti['tag_name'] ?? $ti['name'] ?? $ti['title'] ?? $ti['label'] ?? $ti['slug'] ?? ''));
    }
    if ($raw === '') {
        continue;
    }
    $bp_tag_labels[] = strncmp($raw, '#', 1) === 0 ? $raw : '#' . $raw;
}
$bp_rel_term_to_array = static function ($item): array {
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
$bp_rel_cats_norm = static function ($cats): array {
    if ($cats === null || $cats === '') {
        return [];
    }
    if (is_string($cats)) {
        $d = json_decode($cats, true);
        $cats = is_array($d) ? $d : [];
    }
    if (is_object($cats) && method_exists($cats, 'toArray')) {
        $cats = $cats->toArray();
    }
    if (!is_array($cats)) {
        return [];
    }

    return array_values($cats);
};
$bp_rel_cat_ids = [];
foreach ($bp_cats as $bp_rel_c) {
    $ca = $bp_rel_term_to_array($bp_rel_c);
    $mid = (int) ($ca['id_main'] ?? 0);
    if ($mid < 1) {
        $mid = (int) ($ca['id'] ?? 0);
    }
    if ($mid > 0) {
        $bp_rel_cat_ids[] = $mid;
    }
}
$bp_rel_cat_ids = array_values(array_unique($bp_rel_cat_ids));
$bp_detail_post_id = (int) ($bp['id'] ?? $bp['id_main'] ?? 0);
$bp_slug_early = trim((string) ($bp['slug'] ?? ''));
$bp_rel_categories_list = [];
if (function_exists('get_terms')) {
    $bp_rel_tc = get_terms([
        'posttype' => 'blog',
        'taxonomy' => 'category',
        'lang'     => defined('APP_LANG') ? APP_LANG : '',
    ]) ?: [];
    $bp_rel_categories_list = isset($bp_rel_tc['data']) ? $bp_rel_tc['data'] : (is_array($bp_rel_tc) ? $bp_rel_tc : []);
}
$blog_related_items = [];
if ($bp_rel_cat_ids !== [] && $bp_detail_post_id > 0 && function_exists('get_posts')) {
    $bp_rel_res = get_posts([
        'posttype'        => 'blog',
        'post_status'     => 'active',
        'lang'            => defined('APP_LANG') ? APP_LANG : '',
        'category__in'    => $bp_rel_cat_ids,
        'post__not_in'    => [$bp_detail_post_id],
        'perPage'         => 8,
        'orderby'         => 'created_at',
        'order'           => 'DESC',
        'with_categories' => true,
        'with_author'     => true,
    ]);
    $bp_rel_rows = $bp_rel_res['data'] ?? [];
    if (!is_array($bp_rel_rows)) {
        $bp_rel_rows = [];
    }
    foreach ($bp_rel_rows as $bp_rel_row) {
        if (!is_array($bp_rel_row)) {
            continue;
        }
        $bp_rel_slug = trim((string) ($bp_rel_row['slug'] ?? ''));
        $bp_rel_cats_row = $bp_rel_cats_norm($bp_rel_row['categories'] ?? []);
        $bp_rel_first = $bp_rel_cats_row[0] ?? null;
        $bp_rel_fc = $bp_rel_first !== null ? $bp_rel_term_to_array($bp_rel_first) : [];
        $bp_rel_cname = trim((string) ($bp_rel_fc['name'] ?? $bp_rel_fc['title'] ?? $bp_rel_fc['label'] ?? ''));
        $bp_rel_cslug = trim((string) ($bp_rel_fc['slug'] ?? $bp_rel_fc['term_slug'] ?? ''));
        $bp_rel_cid = (int) ($bp_rel_fc['id'] ?? $bp_rel_fc['term_id'] ?? 0);
        $bp_rel_id_main = (int) ($bp_rel_fc['id_main'] ?? 0);
        if (($bp_rel_cname === '' || $bp_rel_cslug === '') && ($bp_rel_cid > 0 || $bp_rel_id_main > 0)) {
            foreach ($bp_rel_categories_list as $bp_rel_cand) {
                $ca = $bp_rel_term_to_array($bp_rel_cand);
                $cid = (int) ($ca['id'] ?? 0);
                $cm = (int) ($ca['id_main'] ?? 0);
                $match = ($bp_rel_cid > 0 && $cid === $bp_rel_cid) || ($bp_rel_id_main > 0 && ($cm === $bp_rel_id_main || $cid === $bp_rel_id_main));
                if ($match) {
                    if ($bp_rel_cname === '') {
                        $bp_rel_cname = trim((string) ($ca['name'] ?? $ca['title'] ?? $ca['label'] ?? ''));
                    }
                    if ($bp_rel_cslug === '') {
                        $bp_rel_cslug = trim((string) ($ca['slug'] ?? $ca['term_slug'] ?? ''));
                    }
                    break;
                }
            }
        }
        $bp_rel_author = isset($bp_rel_row['author']) && is_array($bp_rel_row['author']) ? $bp_rel_row['author'] : [];
        $bp_rel_content = (string) ($bp_rel_row['content'] ?? '');
        $bp_rel_desc = (string) ($bp_rel_row['description_title'] ?? '');
        $blog_related_items[] = [
            'title'             => (string) ($bp_rel_row['title'] ?? ''),
            'description_title' => $bp_rel_desc !== '' ? $bp_rel_desc : $bp_rel_content,
            'content'           => $bp_rel_content,
            'url'               => $bp_rel_slug !== ''
                ? (string) link_posts($bp_rel_slug, 'blog', defined('APP_LANG') ? APP_LANG : '')
                : '#',
            'username'          => (string) ($bp_rel_author['username'] ?? $bp_rel_author['fullname'] ?? $bp_rel_row['username'] ?? ''),
            'avatar'            => $bp_parse_img($bp_rel_author['avatar'] ?? null) ?: $bp_parse_img($bp_rel_row['avatar'] ?? null),
            'feature'           => $bp_parse_img($bp_rel_row['feature'] ?? null),
            'created_at'        => (string) ($bp_rel_row['created_at'] ?? ''),
            'category_name'     => $bp_rel_cname,
            'category_style'    => blog_category_badge_classes($bp_rel_cname, $bp_rel_cslug),
        ];
    }
}
$blog_related_items = array_slice($blog_related_items, 0, 8);

/** Cột phải (lg+): bài mới nhất trừ bài hiện tại — cùng logic “nổi bật” editorial như strip trang chủ */
$blog_sidebar_featured = [];
if (function_exists('get_posts')) {
    $bf_q = [
        'posttype'        => 'blog',
        'post_status'     => 'active',
        'lang'            => defined('APP_LANG') ? APP_LANG : '',
        'perPage'         => 6,
        'orderby'         => 'created_at',
        'order'           => 'DESC',
        'with_categories' => true,
    ];
    if ($bp_detail_post_id > 0) {
        $bf_q['post__not_in'] = [$bp_detail_post_id];
    }
    $bf_raw = get_posts($bf_q);
    $bf_res = is_array($bf_raw) && ($bf_raw === [] || isset($bf_raw['data'])) ? $bf_raw : ['data' => []];
    $bf_rows = $bf_res['data'] ?? [];
    if (!is_array($bf_rows)) {
        $bf_rows = [];
    }
    $bf_lang = defined('APP_LANG') ? APP_LANG : '';
    $bf_date_fmt = ($bf_lang === 'vi') ? 'd/m/Y' : 'M j, Y';
    foreach ($bf_rows as $bf_row) {
        if (!is_array($bf_row)) {
            continue;
        }
        $bf_rid = (int) ($bf_row['id'] ?? $bf_row['id_main'] ?? 0);
        if ($bp_detail_post_id > 0 && $bf_rid === $bp_detail_post_id) {
            continue;
        }
        $bf_slug = trim((string) ($bf_row['slug'] ?? ''));
        if ($bp_slug_early !== '' && $bf_slug !== '' && $bf_slug === $bp_slug_early) {
            continue;
        }
        $bf_cats = $bp_rel_cats_norm($bf_row['categories'] ?? []);
        $bf_first = $bf_cats[0] ?? null;
        $bf_fc = $bf_first !== null ? $bp_rel_term_to_array($bf_first) : [];
        $bf_cat = trim((string) ($bf_fc['name'] ?? $bf_fc['title'] ?? $bf_fc['label'] ?? ''));
        $bf_ca = (string) ($bf_row['created_at'] ?? '');
        $bf_ts = is_numeric($bf_ca) ? (int) $bf_ca : (strtotime($bf_ca) ?: 0);
        $bf_date_label = $bf_ts > 0 ? date($bf_date_fmt, $bf_ts) : '';
        $blog_sidebar_featured[] = [
            'title'   => (string) ($bf_row['title'] ?? ''),
            'url'     => $bf_slug !== '' ? (string) link_posts($bf_slug, 'blog', $bf_lang) : '#',
            'feature' => $bp_parse_img($bf_row['feature'] ?? null),
            'cat'     => $bf_cat,
            'date'    => $bf_date_label,
        ];
        if (count($blog_sidebar_featured) >= 5) {
            break;
        }
    }
}

$bp_slug = $bp_slug_early;
$bp_share_url = ($bp_slug !== '')
    ? rtrim((string) link_posts($bp_slug, 'blog', defined('APP_LANG') ? APP_LANG : ''), '/')
    : '';
?>
<section class=" pt-12">
    <div class="container mx-auto  ">
        <div class="">
            <nav
                class="flex flex-wrap items-center justify-center gap-2 gap-y-1 text-sm text-gray-500 font-plus"
                aria-label="<?php echo e(function_exists('__') ? __('listing_banner_nav_aria') : 'Breadcrumb'); ?>">
                <a href="<?php echo e($bp_home_url); ?>"
                    class="text-home-body text-sm font-normal leading-[22px] hover:text-home-primary transition-colors shrink-0">
                    <?php echo e(function_exists('__') ? __('listing_banner_home') : 'Home'); ?>
                </a>
                <?php if ($bp_blog_breadcrumb_link_ok): ?>
                <span class="text-gray-400 shrink-0" aria-hidden="true">&gt;</span>
                <a href="<?php echo e($bp_blog_url); ?>"
                    class="text-home-body text-sm font-normal leading-[22px] hover:text-home-primary transition-colors shrink-0">
                    <?php echo e(function_exists('__') ? __('listing_banner_page') : 'Blog'); ?>
                </a>
                <?php endif; ?>
                <?php if ($bp_title !== ''): ?>
                    <span class="text-gray-400 shrink-0" aria-hidden="true">&gt;</span>
                    <span
                        class="text-home-primary text-sm font-normal leading-[22px] text-center break-words"
                        aria-current="page"><?php echo e($bp_title); ?></span>
                <?php endif; ?>
            </nav>
        </div>
        <h2
            class="w-full text-[30px] sm:text-[24px] lg:text-[32px] font-medium leading-normal sm:leading-[36px] lg:leading-[48px] text-center text-home-heading font-plus mb-3">
            <?php echo e($bp_title); ?>
        </h2>

        <p class="text-md text-gray-600 mb-6 line-clamp-2 text-center">
            <?php echo e($bp_desc); ?>
        </p>
        <div class="flex sm:flex-row items-center justify-center gap-4 sm:gap-12 sm:mt-12 mt-6 text-sm text-gray-500">

            <!-- Author (tên/ảnh: quan hệ bài + get_posts lấy user_id + bảng users) -->
            <div class="flex min-w-0 max-w-full items-center">
                <div class="h-8 w-8 shrink-0 overflow-hidden rounded-full bg-gray-300 dark:bg-zinc-600 sm:h-10 sm:w-10">
                    <?php if (!empty($bp_avatar)): ?>
                        <img src="<?php echo e(_img_url($bp_avatar, 'thumbnail')); ?>" alt="<?php echo e($bp_author_img_alt); ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                    <img src="<?php echo theme_assets('images/user1.png'); ?>" alt="<?php echo e($bp_author_img_alt); ?>" class="h-full w-full object-cover">
                    <?php endif; ?>
                </div>
                <span class="ml-2 min-w-0 truncate text-sm font-medium text-home-heading font-plus">
                    <?php echo e($bp_author_name); ?>
                </span>
            </div>

            <!-- Meta -->
            <div class="flex items-center gap-4 sm:gap-12 text-xs sm:text-sm text-gray-500">

                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.66667 1.66669V5.00002M13.3333 1.66669V5.00002M2.5 8.33335H17.5M4.16667 3.33335H15.8333C16.7538 3.33335 17.5 4.07955 17.5 5.00002V16.6667C17.5 17.5872 16.7538 18.3334 15.8333 18.3334H4.16667C3.24619 18.3334 2.5 17.5872 2.5 16.6667V5.00002C2.5 4.07955 3.24619 3.33335 4.16667 3.33335Z"
                            stroke="#97A4B2" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span><?php echo e($bp_date_label !== '' ? $bp_date_label : '—'); ?></span>
                </div>

            </div>
        </div>

        <!-- Article content: lg flex — cột share sticky lg:top-20 (dưới header h-20), không absolute -->
        <div
            class="relative w-full py-12 lg:flex lg:flex-row lg:items-start lg:gap-9">

            <div
                class="hidden lg:flex lg:sticky lg:top-20 lg:self-start shrink-0 flex-col items-center gap-0 w-11 z-10">

                <!-- <button type="button" aria-label="<?php echo e('Like article'); ?>"
                    class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-100 transition">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" focusable="false">
                        <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6"
                            stroke-width="0.842105" />
                        <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
                        <g clip-path="url(#clip0_897_13530)">
                            <path
                                d="M12.6673 14.6666V22.6666M18.0007 11.92L17.334 14.6666H21.2207C21.4276 14.6666 21.6318 14.7148 21.8169 14.8074C22.0021 14.9 22.1631 15.0344 22.2873 15.2C22.4115 15.3656 22.4954 15.5578 22.5325 15.7615C22.5695 15.9651 22.5586 16.1746 22.5007 16.3733L20.9473 21.7066C20.8665 21.9836 20.6981 22.2269 20.4673 22.4C20.2365 22.5731 19.9558 22.6666 19.6673 22.6666H10.6673C10.3137 22.6666 9.97456 22.5262 9.72451 22.2761C9.47446 22.0261 9.33398 21.6869 9.33398 21.3333V16C9.33398 15.6464 9.47446 15.3072 9.72451 15.0572C9.97456 14.8071 10.3137 14.6666 10.6673 14.6666H12.5073C12.7554 14.6665 12.9985 14.5972 13.2093 14.4665C13.4201 14.3357 13.5903 14.1488 13.7007 13.9266L16.0007 9.33331C16.315 9.33721 16.6245 9.41209 16.9059 9.55238C17.1872 9.69266 17.4333 9.89472 17.6256 10.1434C17.8179 10.3922 17.9515 10.6812 18.0165 10.9888C18.0814 11.2964 18.076 11.6147 18.0007 11.92Z"
                                stroke="var(--home-body)" stroke-width="1.33333" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </g>
                        <defs>
                            <clipPath id="clip0_897_13530">
                                <rect width="16" height="16" fill="white" transform="translate(8 8)" />
                            </clipPath>
                        </defs>
                    </svg>
                </button>
                <button type="button" aria-label="<?php echo e('Bookmark article'); ?>"
                    class="w-9 h-9 mb-[48px] rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-100 transition">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" focusable="false">
                        <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6"
                            stroke-width="0.842105" />
                        <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
                        <path
                            d="M20.6673 22L16.0007 19.3333L11.334 22V11.3333C11.334 10.9797 11.4745 10.6406 11.7245 10.3905C11.9746 10.1405 12.3137 10 12.6673 10H19.334C19.6876 10 20.0267 10.1405 20.2768 10.3905C20.5268 10.6406 20.6673 10.9797 20.6673 11.3333V22Z"
                            stroke="var(--home-body)" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button> -->
                <div class="flex flex-col items-center gap-4">
                <?php
                echo \System\Libraries\Render\View::include('parts/social/social-links', [
                    'social_links_variant' => 'blog_share',
                    'social_share_url'     => $bp_share_url,
                    'social_share_title'   => $bp_title,
                ]);
                ?>
                </div>
            </div>

            <div class="blog-detail__body-row min-w-0 flex-1 font-plus">
                <div class="blog-detail__body-main w-full min-w-0">

                <div class="blog-post-body text-[16px] leading-[24px] font-normal text-home-body font-plus
                    [&_h1]:text-[28px] [&_h1]:sm:text-[32px] [&_h1]:font-semibold [&_h1]:text-home-heading [&_h1]:mb-4 [&_h1]:mt-10 [&_h1]:font-plus
                    [&_h2]:text-[24px] [&_h2]:sm:text-[26px] [&_h2]:font-semibold [&_h2]:text-home-heading [&_h2]:mb-3 [&_h2]:mt-10 [&_h2]:font-plus
                    [&_h3]:text-[22px] [&_h3]:sm:text-[24px] [&_h3]:font-semibold [&_h3]:text-home-heading [&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:font-plus
                    [&_p]:mb-4 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-4 [&_ol]:list-decimal [&_ol]:ps-6 [&_ol]:mb-4
                    [&_a]:text-home-primary [&_a]:underline [&_img]:rounded-home-md [&_img]:max-w-full [&_figure]:my-6">
                    <?php if (trim($bp_content) !== ''): ?>
                        <?php echo $bp_content; ?>
                    <?php elseif (trim($bp_desc) !== ''): ?>
                        <p class="mb-12"><?php echo e($bp_desc); ?></p>
                    <?php else: ?>
                        <p class="mb-12 text-home-body/80">No content for this article.</p>
                    <?php endif; ?>
                    <?php if ($bp_note !== ''): ?>
                        <div
                            class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full border-l-4 border-[#FEB934] bg-[rgba(237,168,38,0.10)] px-4 sm:px-6 py-4 mt-8">

                    <div
                        class="text-[#FEB934] font-medium text-[14px] leading-[22px] font-plus flex items-center gap-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect width="24" height="24" fill="url(#pattern0_897_13559)" />
                            <defs>
                                <pattern id="pattern0_897_13559" patternContentUnits="objectBoundingBox" width="1"
                                    height="1">
                                    <use xlink:href="#image0_897_13559" transform="scale(0.01)" />
                                </pattern>
                                <image id="image0_897_13559" width="100" height="100" preserveAspectRatio="none"
                                    xlink:href="data:image/png;base64,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" />
                            </defs>
                        </svg>
                        <p class="text-[14px] leading-[22px] text-[#DB3D23] font-plus font-semibold">
                                    <?php echo e($bp_note); ?>
                        </p>
                    </div>
                </div>
                    <?php endif; ?>
                <!-- Mobile: social -->
                <div class="flex lg:hidden w-full mt-8">
                    <div class="flex flex-row flex-wrap items-center justify-center gap-2 sm:gap-4 w-full">
                        <?php
                        echo \System\Libraries\Render\View::include('parts/social/social-links', [
                            'social_links_variant' => 'blog_share',
                            'social_share_url'     => $bp_share_url,
                            'social_share_title'   => $bp_title,
                        ]);
                        ?>
                    </div>
                </div>
                    <?php if ($bp_tag_labels !== []): ?>
                <div
                    class="flex flex-wrap items-center gap-3 sm:gap-4 w-full pt-4 mt-8 sm:mt-12 border-t-2 border-[#E5E7EB]">
                            <?php foreach ($bp_tag_labels as $tagLabel): ?>
                    <div
                                    class="inline-flex items-center italic justify-center gap-[10px] px-4 sm:px-6 py-2 rounded-full bg-gray-100 text-home-heading text-[12px] sm:text-[14px] leading-[18px] sm:leading-[22px] font-['Plus_Jakarta_Sans']">
                                    <?php echo e($tagLabel); ?>
                    </div>
                            <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                </div>
                <?php if (!empty($blog_sidebar_featured)): ?>
                <aside class="blog-detail__body-aside mt-10 w-full shrink-0 rounded-home-lg border border-gray-200/90 bg-gray-50/90 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 lg:mt-0 lg:shrink-0 lg:sticky lg:top-20 lg:self-start"
                    aria-label="<?php echo e(function_exists('__') ? __('blog_sidebar_featured') : 'Featured posts'); ?>">
                    <h3 class="border-b border-gray-200 pb-3 text-sm font-bold uppercase tracking-wider text-home-heading dark:border-zinc-600 font-space">
                        <?php echo e(function_exists('__') ? __('blog_sidebar_featured') : 'Featured posts'); ?>
                    </h3>
                    <ul class="mt-4 space-y-4" role="list">
                        <?php foreach ($blog_sidebar_featured as $bf): ?>
                            <?php
                            $bf_t = trim((string) ($bf['title'] ?? ''));
                            if ($bf_t === '') {
                                continue;
                            }
                            $bf_h = trim((string) ($bf['url'] ?? ''));
                            ?>
                        <li>
                            <a href="<?php echo e($bf_h !== '' ? $bf_h : '#'); ?>"
                                class="group flex gap-3 rounded-home-md p-1 -m-1 transition-colors hover:bg-white/80 dark:hover:bg-zinc-800/80">
                                <div class="relative h-16 w-24 shrink-0 overflow-hidden rounded-home-md bg-gray-200 dark:bg-zinc-700">
                                    <?php if (!empty($bf['feature'])): ?>
                                        <img src="<?php echo e(_img_url($bf['feature'], 'thumbnail')); ?>" alt=""
                                            class="h-full w-full object-cover transition-transform group-hover:scale-[1.03]"
                                            loading="lazy" decoding="async" width="96" height="64">
                                    <?php else: ?>
                                        <img src="<?php echo e(theme_assets('images/banner_cms.webp')); ?>" alt=""
                                            class="h-full w-full object-cover opacity-90" loading="lazy" decoding="async" width="96" height="64">
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0 flex-1 py-0.5">
                                    <?php if (trim((string) ($bf['cat'] ?? '')) !== ''): ?>
                                        <span class="mb-0.5 block truncate text-[11px] font-medium uppercase tracking-wide text-home-primary/90"><?php echo e((string) $bf['cat']); ?></span>
                                    <?php endif; ?>
                                    <span class="line-clamp-2 text-sm font-medium leading-snug text-home-heading group-hover:text-home-primary dark:text-zinc-100 font-plus"><?php echo e($bf_t); ?></span>
                                    <?php if (trim((string) ($bf['date'] ?? '')) !== ''): ?>
                                        <span class="mt-1 block text-xs text-gray-500 dark:text-zinc-400"><?php echo e((string) $bf['date']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!-- content -->
<?php if (!empty($blog_related_items)): ?>
<section class="bg-gray-100 py-12 sm:py-24 ">
    <div class="container mx-auto">
            <h2 class="w-full text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-start text-home-heading mb-8 sm:mb-12 flex-none order-0 self-stretch flex-grow-0 px-4 font-plus">
                <?php echo function_exists('__') ? e(__('related_blogs')) : 'Related Articles'; ?>
            </h2>
            <div class="relative">
                <div id="blog-related-scroll"
                    class="overflow-x-auto pb-4 -mx-4 px-4 sm:px-6 scrollbar-hide scroll-smooth">
                    <div id="blog-related-track" class="flex gap-4 md:gap-6">
                        <?php foreach ($blog_related_items as $blog_related_item): ?>
                            <div class="blog-related-card-slide shrink-0 min-w-0">
                                <?php
                                echo \System\Libraries\Render\View::include('parts/blog/_blog_article_card', [
                                    'item' => $blog_related_item,
                                    'blog_category_styles' => [],
                                    'blog_category_default_style' => blog_category_badge_default(),
                                ]);
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <script>
                (function() {
                    var AUTO_MS = 3000;
                    var RESUME_MS = 12000;
                    var DRAG_THRESHOLD = 6;
                    document.addEventListener('DOMContentLoaded', function() {
                        var scrollEl = document.getElementById('blog-related-scroll');
                        var track = document.getElementById('blog-related-track');
                        if (!scrollEl || !track) return;
                        var cards = track.children;
                        var idx = 0;
                        var userHold = false;
                        var resumeT = null;
                        var dragPointerId = null;
                        var dragStartX = 0;
                        var dragStartScrollLeft = 0;
                        var dragActive = false;
                        var suppressClick = false;

                        function pause() {
                            userHold = true;
                            clearTimeout(resumeT);
                            resumeT = setTimeout(function() {
                                userHold = false;
                            }, RESUME_MS);
                        }
                        scrollEl.addEventListener('touchstart', pause, {
                            passive: true
                        });
                        scrollEl.addEventListener('wheel', pause, {
                            passive: true
                        });
                        track.addEventListener('click', function(e) {
                            if (suppressClick) {
                                e.preventDefault();
                                e.stopPropagation();
                                suppressClick = false;
                            }
                        }, true);
                        function blogRelatedIsInteractiveTarget(t) {
                            return t && typeof t.closest === 'function' &&
                                !!t.closest('a[href], button:not([disabled]), input, textarea, select, label, [role="link"]');
                        }
                        scrollEl.addEventListener('pointerdown', function(e) {
                            if (e.pointerType !== 'mouse') return;
                            if (e.button !== 0) return;
                            /* setPointerCapture trên strip làm click không tới <a> — chỉ kéo khi không bấm link/nút */
                            if (blogRelatedIsInteractiveTarget(e.target)) return;
                            dragPointerId = e.pointerId;
                            dragStartX = e.clientX;
                            dragStartScrollLeft = scrollEl.scrollLeft;
                            dragActive = false;
                            try {
                                scrollEl.setPointerCapture(e.pointerId);
                            } catch (err) {}
                        });
                        scrollEl.addEventListener('pointermove', function(e) {
                            if (dragPointerId !== e.pointerId) return;
                            var dx = e.clientX - dragStartX;
                            if (!dragActive && Math.abs(dx) > DRAG_THRESHOLD) {
                                dragActive = true;
                                scrollEl.classList.add('blog-related--dragging');
                                pause();
                            }
                            if (dragActive) {
                                scrollEl.scrollLeft = dragStartScrollLeft - dx;
                                e.preventDefault();
                            }
                        });
                        function endPointerDrag(e) {
                            if (dragPointerId === null || e.pointerId !== dragPointerId) return;
                            try {
                                if (scrollEl.hasPointerCapture(e.pointerId)) {
                                    scrollEl.releasePointerCapture(e.pointerId);
                                }
                            } catch (err) {}
                            if (dragActive) {
                                suppressClick = true;
                            }
                            dragPointerId = null;
                            dragActive = false;
                            scrollEl.classList.remove('blog-related--dragging');
                        }
                        scrollEl.addEventListener('pointerup', endPointerDrag);
                        scrollEl.addEventListener('pointercancel', endPointerDrag);
                        function blogRelatedScrollLeftForCard(card) {
                            var scrollRect = scrollEl.getBoundingClientRect();
                            var cardRect = card.getBoundingClientRect();
                            var padL = parseFloat(window.getComputedStyle(scrollEl).paddingLeft) || 0;
                            var innerLeft = scrollRect.left + padL;
                            return Math.max(0, scrollEl.scrollLeft + (cardRect.left - innerLeft));
                        }
                        if (cards.length >= 2) {
                            setInterval(function() {
                                if (userHold) return;
                                var n = cards.length;
                                var prev = idx;
                                idx = (idx + 1) % n;
                                var wrapped = idx === 0 && prev === n - 1;
                                var card = cards[idx];
                                scrollEl.scrollTo({
                                    left: blogRelatedScrollLeftForCard(card),
                                    behavior: wrapped ? 'auto' : 'smooth'
                                });
                            }, AUTO_MS);
                        }
                    });
                })();
            </script>
    </div>
</section>
<?php endif; ?>

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
                    <g clip-path="url(#clip_blog_detail_cta_req)">
            <path
              d="M13 3H2.33333C1.59695 3 1 3.59695 1 4.33333V5.66667C1 6.40305 1.59695 7 2.33333 7H13C13.7364 7 14.3333 6.40305 14.3333 5.66667V4.33333C14.3333 3.59695 13.7364 3 13 3Z"
              stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
            <path
              d="M13 9.66667H2.33333C1.59695 9.66667 1 10.2636 1 11V12.3333C1 13.0697 1.59695 13.6667 2.33333 13.6667H13C13.7364 13.6667 14.3333 13.0697 14.3333 12.3333V11C14.3333 10.2636 13.7364 9.66667 13 9.66667Z"
              stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
          </g>
          <defs>
                        <clipPath id="clip_blog_detail_cta_req">
              <rect width="16" height="16.6667" fill="white" />
            </clipPath>
          </defs>
        </svg>
                <span class="font-plus font-xs"><?php echo e(__('listing_cta_requirements')); ?></span>
      </div>
    </div>
  </div>
</section>