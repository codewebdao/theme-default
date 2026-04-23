<?php
/**
 * Danh sách bài trong 1 phase – tiêu đề từ CMS (vd. Part 1: Env Setup & Hello World).
 * @var array|null $phase phần tử $php_tutorial_phases[i]
 */
$phase = $phase ?? null;
$lessons = is_array($phase) ? ($phase['lessons'] ?? []) : [];
if ($lessons !== []) {
    foreach ($lessons as $lesson) {
        $slug = trim((string) ($lesson['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $title = (string) ($lesson['title'] ?? $slug);
        $href = (string) link_posts($slug, 'tutorial', defined('APP_LANG') ? APP_LANG : '');
        ?>
<li class="flex items-center ">
    <svg class="mr-2 shrink-0 text-gray-600 transition group-hover:text-home-primary font-plus" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M7.00006 7.58363C7.32222 7.58363 7.58339 7.32247 7.58339 7.0003C7.58339 6.67813 7.32222 6.41697 7.00006 6.41697C6.67789 6.41697 6.41672 6.67813 6.41672 7.0003C6.41672 7.32247 6.67789 7.58363 7.00006 7.58363Z" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M11.7834 11.7836C12.9734 10.5995 11.7951 7.4903 9.15839 4.84197C6.51006 2.2053 3.40089 1.02697 2.21672 2.21697C1.02672 3.40113 2.20506 6.5103 4.84172 9.15863C7.49006 11.7953 10.5992 12.9736 11.7834 11.7836Z" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M9.15839 9.15863C11.7951 6.5103 12.9734 3.40113 11.7834 2.21697C10.5992 1.02697 7.49006 2.2053 4.84172 4.84197C2.20506 7.4903 1.02672 10.5995 2.21672 11.7836C3.40089 12.9736 6.51006 11.7953 9.15839 9.15863Z" stroke="currentColor" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <a href="<?php echo e($href); ?>" class="font-plus hover:text-home-primary text-left lg:line-clamp-1 line-clamp-2"><?php echo e($title); ?></a>
</li>
        <?php
    }
} else {
    ?>
<li class="text-sm text-gray-600 font-plus px-1">—</li>
    <?php
}
