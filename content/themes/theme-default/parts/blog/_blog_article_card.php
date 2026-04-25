<?php
require_once __DIR__ . '/_blog_read_time.php';
$item = $item ?? [];

$lang = defined('APP_LANG') ? APP_LANG : 'en';
$__catName = (string) ($item['category_name'] ?? '');
$__title = html_entity_decode((string) ($item['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$__url = (string) ($item['url'] ?? '#');
$__description_title = (string) ($item['description_title'] ?? '');
$__plain = strip_tags($__description_title);
$__username = (string) ($item['username'] ?? '');
$__search_blob = trim(preg_replace('/\s+/u', ' ', strip_tags($__title . ' ' . $__plain . ' ' . $__catName . ' ' . $__username)));
$__search_blob = mb_strtolower($__search_blob, 'UTF-8');

$__created = $item['created_at'] ?? '';
$__ts = blog_created_at_to_unix($__created);
$__dateFmt = ($lang === 'vi') ? 'd/m/Y' : 'M j, Y';
$__dateStr = $__ts > 0 ? date($__dateFmt, $__ts) : '';

$editorial = function_exists('__') ? (string) __('home_featured_blog.meta_editorial') : 'Editorial';
$metaParts = [$editorial];
if ($__dateStr !== '') {
    $metaParts[] = $__dateStr;
}
if ($__catName !== '') {
    $metaParts[] = $__catName;
}
$__meta = implode(' · ', $metaParts);

$badgeNew = function_exists('__') ? (string) __('home_featured_blog.badge_new') : 'NEW';

$fallbackImg = function_exists('theme_assets') ? theme_assets('images/banner_cms.webp') : '';

$featureHtml = '';
if (!empty($item['feature']) && function_exists('_imglazy')) {
    $featureHtml = (string) _imglazy($item['feature'], [
        'alt'     => $__title,
        'title'   => $__title,
        'class'   => 'h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]',
        'loading' => 'lazy',
        'sizes'   => [
            'mobile'  => 'medium',
            'tablet'  => 'medium',
            'desktop' => 'medium',
            'large'   => 'medium',
        ],
    ]);
}
?>
<article
    class="group blog-search-card flex h-full min-h-0 flex-col overflow-hidden rounded-home-xl border border-home-border/50 bg-white shadow-sm ring-1 ring-black/5 transition hover:shadow-md dark:border-home-border/60 dark:bg-home-surface-light dark:shadow-none dark:ring-white/10 dark:hover:border-home-border dark:hover:shadow-lg dark:hover:shadow-black/25"
    data-blog-search="<?php echo htmlspecialchars($__search_blob, ENT_QUOTES, 'UTF-8'); ?>"
    role="listitem">
    <a href="<?php echo e($__url); ?>" class="flex min-h-0 flex-1 flex-col no-underline text-inherit outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-home-white">
        <div class="blog-grid-card-media aspect-[16/10] overflow-hidden bg-home-surface">
            <?php if ($featureHtml !== '') { ?>
                <?php echo $featureHtml; ?>
            <?php } elseif ($fallbackImg !== '') { ?>
                <img
                    src="<?php echo e($fallbackImg); ?>"
                    alt=""
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]"
                    loading="lazy"
                    decoding="async" />
            <?php } ?>
        </div>
        <div class="flex w-full flex-1 flex-col items-start p-5 sm:p-6">
            <span class="mb-2 inline-flex w-fit shrink-0 rounded bg-home-primary/10 py-0.5 pl-0 pr-2 text-[10px] font-bold uppercase tracking-wide text-home-primary font-plus">
                <?php echo e($badgeNew); ?>
            </span>
            <h3 class="line-clamp-2 font-space text-lg font-bold leading-snug text-home-heading dark:text-gray-100">
                <?php echo e($__title); ?>
            </h3>
            <p class="mt-3 font-plus text-sm text-home-body/90">
                <?php echo e($__meta); ?>
            </p>
        </div>
    </a>
</article>
