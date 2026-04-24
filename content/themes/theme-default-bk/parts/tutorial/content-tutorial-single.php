<?php
/**
 * Một bài tutorial (CMS): cùng khung section/sidebar với archive-tutorial; nội dung từ bài post.
 */

use System\Libraries\Render\View;

$php_tutorial_phases = $php_tutorial_phases ?? [];
$php_tutorial_topic_to_phase = $php_tutorial_topic_to_phase ?? [];
$php_tutorial_valid_topics = $php_tutorial_valid_topics ?? [];
$php_tutorial_sidebar_link_mode = $php_tutorial_sidebar_link_mode ?? 'query';
$php_tutorial_default_phase = $php_tutorial_default_phase ?? 'p1';
$php_tutorial_lesson_titles = $php_tutorial_lesson_titles ?? [];
$php_tutorial_lesson_descriptions = $php_tutorial_lesson_descriptions ?? [];
$php_tutorial_topic_breadcrumb = $php_tutorial_topic_breadcrumb ?? [];
$php_tutorial_breadcrumb_fallback = $php_tutorial_breadcrumb_fallback ?? '';
$php_tutorial_current_topic = isset($php_tutorial_current_topic) ? (string) $php_tutorial_current_topic : '';

$tutorial_post = isset($tutorial_post) && is_array($tutorial_post) ? $tutorial_post : [];
$tutorial_slug = trim((string) ($tutorial_post['slug'] ?? $php_tutorial_current_topic));
$tutorial_title_raw = (string) ($tutorial_post['title'] ?? '');
$tutorial_content = (string) ($tutorial_post['content'] ?? '');
$tutorial_desc = trim((string) ($tutorial_post['description'] ?? $tutorial_post['description_title'] ?? ''));

$php_tutorial_tutorial_index_href = base_url('tutorial');

$langLink = defined('APP_LANG') ? APP_LANG : '';

$tutorial_single_phase_heading = static function (string $title): string {
    if ($title === '') {
        return '';
    }
    $parts = explode(':', $title, 2);

    return trim($parts[1] ?? $parts[0]);
};

$mapTitle = (string) ($php_tutorial_lesson_titles[$tutorial_slug] ?? '');
$h1Text = $mapTitle !== '' ? $tutorial_single_phase_heading($mapTitle) : $tutorial_single_phase_heading($tutorial_title_raw);
if ($h1Text === '') {
    $h1Text = $tutorial_title_raw !== '' ? $tutorial_title_raw : $tutorial_slug;
}

$breadcrumbPhase = (string) ($php_tutorial_topic_breadcrumb[$tutorial_slug] ?? $php_tutorial_breadcrumb_fallback);
$breadcrumbLesson = $mapTitle !== '' ? $mapTitle : ($tutorial_title_raw !== '' ? $tutorial_title_raw : $tutorial_slug);

$lessonOrder = array_values($php_tutorial_valid_topics);
$idx = array_search($tutorial_slug, $lessonOrder, true);
$prevSlug = ($idx !== false && $idx > 0) ? $lessonOrder[$idx - 1] : null;
$nextSlug = ($idx !== false && $idx < count($lessonOrder) - 1) ? $lessonOrder[$idx + 1] : null;
$prevUrl = $prevSlug ? link_posts($prevSlug, 'tutorial', $langLink) : '';
$nextUrl = $nextSlug ? link_posts($nextSlug, 'tutorial', $langLink) : '';

$bodyDesc = $tutorial_desc;
if ($bodyDesc === '' && isset($php_tutorial_lesson_descriptions[$tutorial_slug])) {
    $bodyDesc = (string) $php_tutorial_lesson_descriptions[$tutorial_slug];
}
?>
<section class="sm:py-12 px-0 relative overflow-hidden bg-[#CFDEEE] sm:bg-[#C5D8EC]">
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute w-full h-[1454px] rounded-full bg-[#2377FD80]/10 blur-[250px] -translate-x-1/2"
            style="left: 100%; top: 0;"></div>
        <div class="absolute w-full h-[772px] rounded-full bg-[#63ECFF80]/15 blur-[200px] -translate-x-1/2"
            style="left: 0; top: 100px;"></div>
        <div class="absolute w-full h-[1366px] rounded-full bg-[#63ECFF33]/0.9 blur-[100px] -translate-x-1/2"
            style="left: 50%; top: 0px;"></div>
    </div>

    <div class="container mx-auto relative z-10">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-0 p-0 py-8 lg:p-8 xl:p-12 bg-none lg:bg-white rounded-none sm:rounded-2xl lg:rounded-[48px] shadow-sm">

            <?php
            echo View::include('parts/tutorial/sidebar-tutorial', [
                'php_tutorial_phases'              => $php_tutorial_phases,
                'php_tutorial_topic_to_phase'      => $php_tutorial_topic_to_phase ?? [],
                'php_tutorial_valid_topics'        => $php_tutorial_valid_topics ?? [],
                'php_tutorial_default_phase'       => $php_tutorial_default_phase ?? 'p1',
                'php_tutorial_current_topic'       => $php_tutorial_current_topic,
                'php_tutorial_sidebar_link_mode'   => $php_tutorial_sidebar_link_mode,
            ]);
            ?>

            <div class="hidden lg:flex items-stretch px-0 lg:pl-[24px] lg:pr-[40px] xl:px-[48px]  flex-shrink-0 self-stretch">
                <div class="w-2 bg-home-surface-light"></div>
            </div>

            <div class="flex-1 min-w-0 min-h-tutorial-main flex flex-col lg:min-h-0">

                <div class="flex-1 min-w-0 flex flex-col">
                    <div class="mb-6 sm:mb-12">
                        <nav class="flex flex-wrap items-center sm:gap-x-2 gap-x-1 gap-y-1 text-sm font-plus text-home-body" aria-label="Breadcrumb">
                            <a href="<?php echo htmlspecialchars($php_tutorial_tutorial_index_href, ENT_QUOTES, 'UTF-8'); ?>"
                                class="hover:text-home-primary transition-colors shrink-0"><?php echo e(__('theme_nav.php_tutorial')); ?></a>
                            <span class="text-home-body opacity-50 select-none" aria-hidden="true">&gt;</span>
                            <span class="shrink-0"><?php echo e($breadcrumbPhase); ?></span>
                            <span class="text-home-body opacity-50 select-none" aria-hidden="true">&gt;</span>
                            <span class="text-home-primary font-medium min-w-0"><?php echo e($breadcrumbLesson); ?></span>
                        </nav>
                    </div>

                    <div class="mb-6 sm:mb-8">
                        <h1 class="font-space text-3xl sm:text-4xl md:text-5xl lg:text-[40px] xl:text-[48px] font-bold text-home-heading leading-tight sm:leading-snug md:leading-[60px] lg:leading-[64px] text-start mb-3 sm:mb-4">
                            <?php echo e($h1Text); ?>
                        </h1>
                        <?php if ($bodyDesc !== ''): ?>
                            <p class="text-sm sm:text-base text-gray-600 leading-relaxed font-plus">
                                <?php echo e($bodyDesc); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-6 sm:space-y-8 mb-8 sm:mb-12">
                        <div class="tutorial-lesson-body blog-post-body text-[16px] leading-[24px] font-normal text-home-body font-plus
                                [&_h1]:text-[28px] [&_h1]:sm:text-[32px] [&_h1]:font-semibold [&_h1]:text-home-heading [&_h1]:mb-4 [&_h1]:mt-10 [&_h1]:font-plus
                                [&_h2]:text-[24px] [&_h2]:sm:text-[26px] [&_h2]:font-semibold [&_h2]:text-home-heading [&_h2]:mb-3 [&_h2]:mt-10 [&_h2]:font-plus
                                [&_h3]:text-[22px] [&_h3]:sm:text-[24px] [&_h3]:font-semibold [&_h3]:text-home-heading [&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:font-plus
                                [&_p]:mb-4 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-4 [&_ol]:list-decimal [&_ol]:ps-6 [&_ol]:mb-4
                                [&_a]:text-home-primary [&_a]:underline [&_img]:rounded-home-md [&_img]:max-w-full [&_figure]:my-6 [&_pre]:overflow-x-auto">
                            <?php echo $tutorial_content; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-auto flex items-center justify-between gap-4 pt-8 sm:pt-12 border-t border-gray-200 shrink-0">
                    <div class="min-h-[1.5rem] flex items-center">
                        <?php if ($prevUrl !== ''): ?>
                            <a href="<?php echo htmlspecialchars($prevUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                class="flex items-center gap-2 text-sm sm:text-base text-gray-600 hover:text-home-primary transition-colors font-plus">
                                <span>&lt; Previous</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="min-h-[1.5rem] flex items-center">
                        <?php if ($nextUrl !== ''): ?>
                            <a href="<?php echo htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                class="flex items-center gap-2 text-home-primary text-sm sm:text-base font-semibold hover:opacity-90 transition-opacity font-plus">
                                <span>Next Lesson -&gt;</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
