<?php
$php_tutorial_phases = $php_tutorial_phases ?? [];
$php_tutorial_topic_to_phase = $php_tutorial_topic_to_phase ?? [];
$php_tutorial_valid_topics = $php_tutorial_valid_topics ?? [];
$php_tutorial_default_phase = $php_tutorial_default_phase ?? 'p1';
$php_tutorial_lesson_titles = $php_tutorial_lesson_titles ?? [];
$php_tutorial_lesson_descriptions = $php_tutorial_lesson_descriptions ?? [];
$php_tutorial_lesson_contents = $php_tutorial_lesson_contents ?? [];
$php_tutorial_tutorial_index_href = base_url('php-tutorial');
$php_tutorial_current_topic = isset($php_tutorial_current_topic) ? (string) $php_tutorial_current_topic : '';
if ($php_tutorial_valid_topics !== [] && !in_array($php_tutorial_current_topic, $php_tutorial_valid_topics, true)) {
    $php_tutorial_current_topic = (string) ($php_tutorial_valid_topics[0] ?? '');
}
$php_tutorial_esc_js = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
function getPhaseTitle($title)
{
    if (!$title) return '';
    $parts = explode(':', $title, 2);
    return trim($parts[1] ?? $parts[0]);
}
?>
<section class="sm:py-12 px-0 relative overflow-hidden bg-[#CFDEEE] sm:bg-[#C5D8EC]">
    <!-- Background Blur Effects -->
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute w-full h-[1454px] rounded-full bg-[#2377FD80]/10 blur-[250px] -translate-x-1/2"
            style="left: 100%; top: 0;"></div>
        <div class="absolute w-full h-[772px] rounded-full bg-[#63ECFF80]/15 blur-[200px] -translate-x-1/2"
            style="left: 0; top: 100px;"></div>
        <div class="absolute w-full h-[1366px] rounded-full bg-[#63ECFF33]/0.9 blur-[100px] -translate-x-1/2"
            style="left: 50%; top: 0px;"></div>
    </div>

    <div class="container mx-auto relative z-10" x-data="{
            activeTopic: '',
            getBreadcrumbPhase() {
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                var m = d.phaseLabelMap || {};
                return m[this.activeTopic] || d.breadcrumbFallback || '';
            },
            getBreadcrumbLessonTitle() {
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                var m = d.lessonTitleMap || {};
                return m[this.activeTopic] || '';
            },
            getLessonOrder() {
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                return d.lessonOrder || [];
            },
            getPrevSlug() {
                var order = this.getLessonOrder();
                var i = order.indexOf(this.activeTopic);
                return i > 0 ? order[i - 1] : null;
            },
            getNextSlug() {
                var order = this.getLessonOrder();
                var i = order.indexOf(this.activeTopic);
                return (i >= 0 && i < order.length - 1) ? order[i + 1] : null;
            },
            getLessonUrl(slug) {
                if (!slug) return '#';
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                var m = d.lessonUrls || {};
                return m[slug] || '#';
            },
            getPrevUrl() {
                return this.getLessonUrl(this.getPrevSlug());
            },
            getNextUrl() {
                return this.getLessonUrl(this.getNextSlug());
            },
            init() {
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                var st = window.Alpine && Alpine.store('phpTutorial');
                this.activeTopic = (st && st.activeTopic) ? st.activeTopic : (d.currentTopic || d.firstTopic || 'syntax');
                if (window.Alpine && Alpine.store('phpTutorial')) {
                    this.$watch('$store.phpTutorial.activeTopic', function (value) { this.activeTopic = value; }.bind(this));
                }
            }
        }">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-0 p-0 py-8 lg:p-8 xl:p-12 bg-none lg:bg-white rounded-none sm:rounded-2xl lg:rounded-[48px] shadow-sm">

            <?php
            echo \System\Libraries\Render\View::include('parts/php_tutorial/sidebar-php-tutorial', [
                'php_tutorial_phases'            => $php_tutorial_phases,
                'php_tutorial_topic_to_phase'    => $php_tutorial_topic_to_phase ?? [],
                'php_tutorial_valid_topics'      => $php_tutorial_valid_topics ?? [],
                'php_tutorial_default_phase'     => $php_tutorial_default_phase ?? 'p1',
                'php_tutorial_current_topic'     => $php_tutorial_current_topic,
                'php_tutorial_sidebar_link_mode' => 'query',
            ]);
            ?>

            <!-- Divider Line -->
            <div class="hidden lg:flex items-stretch px-0 lg:pl-[24px] lg:pr-[40px] xl:px-[48px]  flex-shrink-0 self-stretch">
                <div class="w-2 bg-home-surface-light"></div>
            </div>


            <!-- Main Content Area: flex-col + flex-1 phần nội dung + mt-auto nav → Previous/Next luôn đáy card (lg) -->
            <div class="flex-1 min-w-0 min-h-php-tutorial-main flex flex-col lg:min-h-0">

                <div class="flex-1 min-w-0 flex flex-col">
                <!-- Breadcrumbs -->
                <div class="mb-6 sm:mb-12">
                    <nav class="flex flex-wrap items-center sm:gap-x-2 gap-x-1 gap-y-1 text-sm font-plus text-home-body">
                        <a href="<?php echo htmlspecialchars($php_tutorial_tutorial_index_href, ENT_QUOTES, 'UTF-8'); ?>"
                            class="hover:text-home-primary transition-colors shrink-0">PHP Tutorial</a>
                        <span class="text-home-body opacity-50 select-none" aria-hidden="true">&gt;</span>
                        <span class="shrink-0" x-text="getBreadcrumbPhase()">Phase</span>
                        <span class="text-home-body opacity-50 select-none" aria-hidden="true">&gt;</span>
                        <span class="text-home-primary font-medium min-w-0" x-text="getBreadcrumbLessonTitle()">Lesson</span>
                    </nav>
                </div>

                <?php if ($php_tutorial_valid_topics === []): ?>
                    <div class="mb-8 rounded-home-lg border border-gray-200 p-6 sm:p-8">
                        <p class="text-sm sm:text-base text-gray-600 font-plus">
                            <?php echo e(__('php_tutorial.erro_message')); ?>
                        </p>
                    </div>
                <?php else: ?>

                    <!-- Một khối x-show / bài: tránh hai transition chồng nhau (tiêu đề + body) gây giật layout -->
                    <div class="mb-8 sm:mb-12">
                        <?php foreach ($php_tutorial_valid_topics as $lessonSlug):
                            $slugJs = $php_tutorial_esc_js($lessonSlug);
                            $ltitle = (string) ($php_tutorial_lesson_titles[$lessonSlug] ?? $lessonSlug);
                            $ldesc = (string) ($php_tutorial_lesson_descriptions[$lessonSlug] ?? '');
                            $lcontent = (string) ($php_tutorial_lesson_contents[$lessonSlug] ?? '');
                            $lessonBodyMinClass = trim($lcontent) === '' ? 'min-h-php-tutorial-lesson-empty' : '';
                            $lessonActiveInitially = ($lessonSlug === $php_tutorial_current_topic);
                        ?>
                            <div x-show="activeTopic === '<?php echo $slugJs; ?>'"
                                <?php if (!$lessonActiveInitially): ?>style="display: none"<?php endif; ?>
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100">
                                <div class="mb-6 sm:mb-8">
                                    <h1 class="font-space text-3xl sm:text-4xl md:text-5xl lg:text-[40px] xl:text-[48px] font-bold text-home-heading leading-tight sm:leading-snug md:leading-[60px] lg:leading-[64px] text-start mb-3 sm:mb-4">
                                        <?php echo e(getPhaseTitle($ltitle)); ?>
                                    </h1>
                                    <?php if ($ldesc !== ''): ?>
                                        <p class="text-sm sm:text-base text-gray-600 leading-relaxed font-plus">
                                            <?php echo e($ldesc); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-6 sm:space-y-8">
                                    <div class="php-tutorial-lesson-body blog-post-body text-[16px] leading-[24px] font-normal text-home-body font-plus
                                [&_h1]:text-[28px] [&_h1]:sm:text-[32px] [&_h1]:font-semibold [&_h1]:text-home-heading [&_h1]:mb-4 [&_h1]:mt-10 [&_h1]:font-plus
                                [&_h2]:text-[24px] [&_h2]:sm:text-[26px] [&_h2]:font-semibold [&_h2]:text-home-heading [&_h2]:mb-3 [&_h2]:mt-10 [&_h2]:font-plus
                                [&_h3]:text-[22px] [&_h3]:sm:text-[24px] [&_h3]:font-semibold [&_h3]:text-home-heading [&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:font-plus
                                [&_p]:mb-4 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-4 [&_ol]:list-decimal [&_ol]:ps-6 [&_ol]:mb-4
                                [&_a]:text-home-primary [&_a]:underline [&_img]:rounded-home-md [&_img]:max-w-full [&_figure]:my-6 [&_pre]:overflow-x-auto <?php echo $lessonBodyMinClass; ?>">
                                        <?php if (trim($lcontent) !== ''): ?>
                                            <?php echo $lcontent; ?>
                                        <?php else: ?>
                                            <div class="rounded-home-lg border border-dashed border-gray-300 bg-gray-50/80 flex-1 flex flex-col justify-center min-h-[280px] sm:min-h-[320px]">
                                                <div class="p-8 sm:p-10 text-center sm:text-left max-w-xl mx-auto sm:mx-0">
                                                    <h2 class="text-xl sm:text-2xl font-semibold text-home-heading mb-3 font-space">
                                                    <?php echo e(__('php_tutorial.coming_soon')); ?>
                                                    </h2>
                                                    <p class="text-sm sm:text-base text-gray-600 font-plus leading-relaxed">
                                                    <?php echo e(__('php_tutorial.coming_soon_detail')); ?>
                                            
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
                </div>

                <!-- Bottom Navigation: mt-auto = sát đáy khối card trắng khi nội dung ngắn -->
                <div class="mt-auto flex items-center justify-between gap-4 pt-8 sm:pt-12 border-t border-gray-200 shrink-0">
                    <div class="min-h-[1.5rem] flex items-center">
                        <a x-show="getPrevSlug()"
                            :href="getPrevUrl()"
                            class="flex items-center gap-2 text-sm sm:text-base text-gray-600 hover:text-home-primary transition-colors font-plus">
                            <span>&lt; Previous</span>
                        </a>
                    </div>
                    <div class="min-h-[1.5rem] flex items-center">
                        <a x-show="getNextSlug()"
                            :href="getNextUrl()"
                            class="flex items-center gap-2 text-home-primary text-sm sm:text-base font-semibold hover:opacity-90 transition-opacity font-plus">
                            <span>Next Lesson -&gt;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>