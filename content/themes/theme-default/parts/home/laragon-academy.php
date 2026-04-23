<?php

use System\Libraries\Render\View;

if (!isset($php_tutorial_phases)) {
    require __DIR__ . '/../tutorial/tutorial-sidebar-data.php';
}
function getPhaseTitle($title)
{
    if (!$title) return '';
    $parts = explode(':', $title, 2);
    return trim($parts[1] ?? $parts[0]);
}
$academy_phases = array_values(array_slice($php_tutorial_phases ?? [], 0, 4));
$academy_defaults = [
    ['title' => 'PHP Basics', 'badge' => 'NEWBIE'],
    ['title' => 'Advanced PHP', 'badge' => 'Intermediate'],
    ['title' => 'Build News Site', 'badge' => 'Project'],
    ['title' => 'Build E-Commerce', 'badge' => 'Advanced'],
];
$academy_see_href = static function (int $i) use ($academy_phases): string {
    $p = $academy_phases[$i] ?? null;
    $lessons = is_array($p) ? ($p['lessons'] ?? []) : [];
    if ($lessons !== [] && !empty($lessons[0]['slug'])) {
        $slug = trim((string) $lessons[0]['slug']);
        if ($slug !== '') {
            return (string) link_posts($slug, 'tutorial', defined('APP_LANG') ? APP_LANG : '');
        }
    }

    return rtrim((string) base_url('tutorial'), '/');
};
?>
<section class="py-16 sm:py-20 lg:py-24 bg-home-surface-light/50">

    <div class="container mx-auto">
        <!-- Section Title -->
        <div class="text-center mb-8 sm:mb-10 md:mb-12 lg:mb-14 ">
            <h2 class="sr sr--fade-up w-full  text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-3 sm:mb-2 flex-none order-0 self-stretch flex-grow-0 font-space" style="--sr-delay: 0ms">
                Laragon Academy
            </h2>
            <p
                class="sr sr--fade-up text-gray-600 mt-3 sm:mt-2 md:mt-3 text-xs sm:text-sm md:text-base leading-relaxed max-w-2xl mx-auto font-plus" style="--sr-delay: 50ms">
                Free Project-Based Learning Paths. From Zero to Hero.
            </p>
        </div>
        <div class="relative">
            <!-- Mobile: Horizontal Scroll Slider -->
            <div class="block sm:hidden">
                <div id="academy-slider-mobile"
                    class="overflow-x-auto scrollbar-hide snap-x snap-mandatory flex gap-8 px-4 pb-4 scroll-smooth">
                    <div
                        class="academy-card group min-w-[calc((100%-2rem)/1.5)] flex-shrink-0 snap-start rounded-home-lg transition-all duration-300 border border-gray-200 shadow-[0_2.667px_16px_0_rgba(43,140,238,0.15)] hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">
                        <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full">
                            <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                                <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                    viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="var(--home-success)"
                                        fill-opacity="0.2" />
                                    <path
                                        d="M45.2779 26.9446V36.1113M20.8334 30.7641V36.1113C20.8334 37.3269 21.7992 38.4926 23.5183 39.3522C25.2374 40.2117 27.5689 40.6946 30.0001 40.6946C32.4312 40.6946 34.7628 40.2117 36.4819 39.3522C38.201 38.4926 39.1668 37.3269 39.1668 36.1113V30.7641M44.3918 28.3532C44.6653 28.2326 44.8974 28.0343 45.0593 27.7831C45.2212 27.5318 45.3059 27.2385 45.3028 26.9396C45.2997 26.6407 45.209 26.3493 45.0419 26.1014C44.8748 25.8535 44.6387 25.6601 44.3627 25.5452L31.2682 19.5807C30.8701 19.3991 30.4376 19.3052 30.0001 19.3052C29.5626 19.3052 29.1301 19.3991 28.732 19.5807L15.639 25.5391C15.367 25.6582 15.1356 25.854 14.9731 26.1025C14.8107 26.3511 14.7241 26.6416 14.7241 26.9385C14.7241 27.2354 14.8107 27.5259 14.9731 27.7745C15.1356 28.023 15.367 28.2188 15.639 28.3379L28.732 34.3085C29.1301 34.4901 29.5626 34.584 30.0001 34.584C30.4376 34.584 30.8701 34.4901 31.2682 34.3085L44.3918 28.3532Z"
                                        stroke="var(--home-success)" stroke-width="3.05556" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>

                            </div>
                            <div class="mb-2 sm:mb-3 flex justify-between">
                                <?php
                                $title = ($academy_phases[0]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                                echo e(getPhaseTitle($title));
                                ?>
                                <span
                                    class="text-[11px] bg-home-surface-light/50 px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-home-success font-plus">
                                    <?php echo e(trim((string) (($academy_phases[0]['badge_label'] ?? '') ?: $academy_defaults[0]['badge']))); ?>
                                </span>
                            </div>
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[0] ?? null]); ?>
                            </ul>
                            <a href="<?php echo e($academy_see_href(0)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-gray-700 font-medium transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>
                    </div>
                    <!-- Card 2: Advanced PHP -->
                    <div
                        class=" academy-card group min-w-[calc((100%-2rem)/1.5)] flex-shrink-0 snap-start rounded-home-lg transition-all duration-300 border border-gray-200 shadow-[0_2.667px_16px_0_rgba(43,140,238,0.15)] hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">
                        <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full">
                            <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                                <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                    viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="#9747FF"
                                        fill-opacity="0.15" />
                                    <path
                                        d="M16.0974 21.75H21.5974M16.0974 27.25H21.5974M16.0974 32.75H21.5974M16.0974 38.25H21.5974M26.4099 24.5H33.2849M26.4099 30H35.3474M26.4099 35.5H32.5974M21.5974 16.25H38.0974C39.6162 16.25 40.8474 17.4812 40.8474 19V41C40.8474 42.5188 39.6162 43.75 38.0974 43.75H21.5974C20.0786 43.75 18.8474 42.5188 18.8474 41V19C18.8474 17.4812 20.0786 16.25 21.5974 16.25Z"
                                        stroke="#9747FF" stroke-width="2.75" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="mb-2 sm:mb-3 flex justify-between">
                                <?php
                                $title = ($academy_phases[1]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                                echo e(getPhaseTitle($title));
                                ?>
                                <span
                                    class="text-[11px] bg-home-surface-light/50 px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-[#9747FF] font-plus">
                                    <?php echo e(trim((string) (($academy_phases[1]['badge_label'] ?? '') ?: $academy_defaults[1]['badge']))); ?>
                                </span>
                            </div>
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[1] ?? null]); ?>
                            </ul>
                            <a href="<?php echo e($academy_see_href(1)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-gray-700 font-medium transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>
                    </div>
                    <!-- Card 3: Build News Site -->
                    <div
                        class="academy-card group min-w-[calc((100%-2rem)/1.5)] flex-shrink-0 snap-start rounded-home-lg transition-all duration-300 border border-gray-200 shadow-[0_2.667px_16px_0_rgba(43,140,238,0.15)] hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">
                        <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full">
                            <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                                <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                    viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="#ED661D"
                                        fill-opacity="0.15" />
                                    <path
                                        d="M42.375 20.375C42.375 22.6532 36.8345 24.5 30 24.5C23.1655 24.5 17.625 22.6532 17.625 20.375M42.375 20.375C42.375 18.0968 36.8345 16.25 30 16.25C23.1655 16.25 17.625 18.0968 17.625 20.375M42.375 20.375V39.625C42.375 40.719 41.0712 41.7682 38.7504 42.5418C36.4297 43.3154 33.2821 43.75 30 43.75C26.7179 43.75 23.5703 43.3154 21.2496 42.5418C18.9288 41.7682 17.625 40.719 17.625 39.625V20.375M17.625 30C17.625 31.094 18.9288 32.1432 21.2496 32.9168C23.5703 33.6904 26.7179 34.125 30 34.125C33.2821 34.125 36.4297 33.6904 38.7504 32.9168C41.0712 32.1432 42.375 31.094 42.375 30"
                                        stroke="#ED661D" stroke-width="2.75" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="mb-2 sm:mb-3 flex justify-between">
                                <?php
                                $title = ($academy_phases[2]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                                echo e(getPhaseTitle($title));
                                ?>
                                <span
                                    class="text-[11px] bg-home-surface-light/50 px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-[#ED661D] font-plus">
                                    <?php echo e(trim((string) (($academy_phases[2]['badge_label'] ?? '') ?: $academy_defaults[2]['badge']))); ?>
                                </span>
                            </div>
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[2] ?? null]); ?>
                            </ul>
                            <a href="<?php echo e($academy_see_href(2)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-gray-700 font-medium transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>
                    </div>
                    <!-- Card 4: Build E-Commerce -->
                    <div
                        class="academy-card group min-w-[calc((100%-2rem)/1.5)] flex-shrink-0 snap-start rounded-home-lg transition-all duration-300 border border-gray-200 shadow-[0_2.667px_16px_0_rgba(43,140,238,0.15)] hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">
                        <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full">
                            <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                                <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                    viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="var(--home-primary)"
                                        fill-opacity="0.2" />
                                    <path
                                        d="M35.5 27.25C35.5 28.7087 34.9205 30.1076 33.8891 31.1391C32.8576 32.1705 31.4587 32.75 30 32.75C28.5413 32.75 27.1424 32.1705 26.1109 31.1391C25.0795 30.1076 24.5 28.7087 24.5 27.25M17.7666 21.7967H42.2334M18.175 21.0171C17.818 21.4931 17.625 22.0721 17.625 22.6671V41C17.625 41.7293 17.9147 42.4288 18.4305 42.9445C18.9462 43.4603 19.6457 43.75 20.375 43.75H39.625C40.3543 43.75 41.0538 43.4603 41.5695 42.9445C42.0853 42.4288 42.375 41.7293 42.375 41V22.6671C42.375 22.0721 42.182 21.4931 41.825 21.0171L39.075 17.35C38.8188 17.0085 38.4867 16.7313 38.1048 16.5403C37.723 16.3494 37.3019 16.25 36.875 16.25H23.125C22.6981 16.25 22.277 16.3494 21.8952 16.5403C21.5133 16.7313 21.1812 17.0085 20.925 17.35L18.175 21.0171Z"
                                        stroke="var(--home-primary)" stroke-width="2.75" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>

                            </div>
                            <div class="mb-2 sm:mb-3 flex justify-between">
                                <?php
                                $title = ($academy_phases[3]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                                echo e(getPhaseTitle($title));
                                ?>
                                <span
                                    class="text-[11px] bg-home-surface-light/50 px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-home-primary font-plus">
                                    <?php echo e(trim((string) (($academy_phases[3]['badge_label'] ?? '') ?: $academy_defaults[3]['badge']))); ?>
                                </span>
                            </div>
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[3] ?? null]); ?>
                            </ul>
                            <a href="<?php echo e($academy_see_href(3)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-gray-700 font-medium transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Pagination Dots -->
            <div class="flex justify-center items-center gap-3 mt-6 sm:hidden">
                <button type="button" onclick="prevAcademyMobile()" class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-gray-600 hover:text-home-primary hover:bg-gray-100 transition font-plus touch-manipulation" aria-label="Previous">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="flex gap-2">
                    <?php for ($ai = 0; $ai < 4; $ai++): ?>
                        <?php
                        $_atitle = ($academy_phases[$ai]['title_display'] ?? '') ?: ($academy_defaults[$ai]['title'] ?? '');
                        $_apath = getPhaseTitle($_atitle);
                        $_alabel = $_apath !== ''
                            ? ('Go to ' . $_apath . ', slide ' . ($ai + 1) . ' of 4')
                            : ('Go to slide ' . ($ai + 1) . ' of 4');
                        $dotBg = $ai === 0 ? 'bg-home-primary' : 'bg-gray-300';
                        ?>
                    <button type="button" onclick="goToAcademyMobile(<?php echo (int) $ai; ?>)"
                        class="academy-dot-mobile inline-flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-full touch-manipulation"
                        aria-label="<?php echo e($_alabel); ?>">
                        <span class="academy-dot-mobile-inner pointer-events-none h-2 w-2 rounded-full <?php echo e($dotBg); ?> transition-colors" aria-hidden="true"></span>
                    </button>
                    <?php endfor; ?>
                </div>
                <button type="button" onclick="nextAcademyMobile()" class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-gray-600 hover:text-home-primary hover:bg-gray-100 transition touch-manipulation" aria-label="Next">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Desktop: Grid Layout -->
        <div class="hidden sm:block">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 md:gap-6 lg:gap-4 xl:gap-8 justify-items-center">
                <!-- Card 1: PHP Basics -->
                <div
                    class="group w-full max-w-sm rounded-home-lg transition-all duration-300 border border-gray-200 hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">

                    <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full flex flex-col">

                        <!-- Icon -->
                        <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="var(--home-success)"
                                    fill-opacity="0.2" />
                                <path
                                    d="M45.2779 26.9446V36.1113M20.8334 30.7641V36.1113C20.8334 37.3269 21.7992 38.4926 23.5183 39.3522C25.2374 40.2117 27.5689 40.6946 30.0001 40.6946C32.4312 40.6946 34.7628 40.2117 36.4819 39.3522C38.201 38.4926 39.1668 37.3269 39.1668 36.1113V30.7641M44.3918 28.3532C44.6653 28.2326 44.8974 28.0343 45.0593 27.7831C45.2212 27.5318 45.3059 27.2385 45.3028 26.9396C45.2997 26.6407 45.209 26.3493 45.0419 26.1014C44.8748 25.8535 44.6387 25.6601 44.3627 25.5452L31.2682 19.5807C30.8701 19.3991 30.4376 19.3052 30.0001 19.3052C29.5626 19.3052 29.1301 19.3991 28.732 19.5807L15.639 25.5391C15.367 25.6582 15.1356 25.854 14.9731 26.1025C14.8107 26.3511 14.7241 26.6416 14.7241 26.9385C14.7241 27.2354 14.8107 27.5259 14.9731 27.7745C15.1356 28.023 15.367 28.2188 15.639 28.3379L28.732 34.3085C29.1301 34.4901 29.5626 34.584 30.0001 34.584C30.4376 34.584 30.8701 34.4901 31.2682 34.3085L44.3918 28.3532Z"
                                    stroke="var(--home-success)" stroke-width="3.05556" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>

                        <!-- Title + Badge -->
                        <div class="mb-2 sm:mb-3 flex justify-between">
                            <?php
                            $title = ($academy_phases[0]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                            echo e(getPhaseTitle($title));
                            ?>
                            <span
                                class="text-[11px] px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-home-success font-plus">
                                <?php echo e(trim((string) (($academy_phases[0]['badge_label'] ?? '') ?: $academy_defaults[0]['badge']))); ?>
                            </span>
                        </div>
                        <!-- List + Button -->
                        <div class="flex flex-col flex-1">
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 flex-1 mb-12 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[0] ?? null]); ?>
                            </ul>

                            <!-- Button luôn nằm dưới cùng -->
                            <a href="<?php echo e($academy_see_href(0)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-gray-700 transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Card 2: Advanced PHP -->
                <div
                    class="group w-full max-w-sm rounded-home-lg transition-all duration-300 border border-gray-200 hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">

                    <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full flex flex-col">

                        <!-- Icon -->
                        <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="#9747FF"
                                    fill-opacity="0.15" />
                                <path
                                    d="M16.0974 21.75H21.5974M16.0974 27.25H21.5974M16.0974 32.75H21.5974M16.0974 38.25H21.5974M26.4099 24.5H33.2849M26.4099 30H35.3474M26.4099 35.5H32.5974M21.5974 16.25H38.0974C39.6162 16.25 40.8474 17.4812 40.8474 19V41C40.8474 42.5188 39.6162 43.75 38.0974 43.75H21.5974C20.0786 43.75 18.8474 42.5188 18.8474 41V19C18.8474 17.4812 20.0786 16.25 21.5974 16.25Z"
                                    stroke="#9747FF" stroke-width="2.75" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>

                        <!-- Title + Badge -->
                        <div class="mb-2 sm:mb-3 flex justify-between">
                            <?php
                            $title = ($academy_phases[1]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                            echo e(getPhaseTitle($title));
                            ?>
                            <span
                                class="text-[11px]  px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-[#9747FF] font-plus">
                                <?php echo e(trim((string) (($academy_phases[1]['badge_label'] ?? '') ?: $academy_defaults[1]['badge']))); ?>
                            </span>
                        </div>
                        <!-- List + Button -->
                        <div class="flex flex-col flex-1">
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 flex-1 mb-12 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[1] ?? null]); ?>
                            </ul>

                            <!-- Button luôn nằm dưới cùng -->
                            <a href="<?php echo e($academy_see_href(1)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-sm text-gray-700 transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>

                    </div>
                </div>
                <!-- Card 3: Build News Site -->
                <div
                    class="group w-full max-w-sm rounded-home-lg transition-all duration-300 border border-gray-200 hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">

                    <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full flex flex-col">

                        <!-- Icon -->
                        <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="#ED661D"
                                    fill-opacity="0.15" />
                                <path
                                    d="M42.375 20.375C42.375 22.6532 36.8345 24.5 30 24.5C23.1655 24.5 17.625 22.6532 17.625 20.375M42.375 20.375C42.375 18.0968 36.8345 16.25 30 16.25C23.1655 16.25 17.625 18.0968 17.625 20.375M42.375 20.375V39.625C42.375 40.719 41.0712 41.7682 38.7504 42.5418C36.4297 43.3154 33.2821 43.75 30 43.75C26.7179 43.75 23.5703 43.3154 21.2496 42.5418C18.9288 41.7682 17.625 40.719 17.625 39.625V20.375M17.625 30C17.625 31.094 18.9288 32.1432 21.2496 32.9168C23.5703 33.6904 26.7179 34.125 30 34.125C33.2821 34.125 36.4297 33.6904 38.7504 32.9168C41.0712 32.1432 42.375 31.094 42.375 30"
                                    stroke="#ED661D" stroke-width="2.75" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>

                        <!-- Title + Badge -->
                        <div class="mb-2 sm:mb-3 flex justify-between">
                            <?php
                            $title = ($academy_phases[2]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                            echo e(getPhaseTitle($title));
                            ?>
                            <span
                                class="text-[11px] px-2 py-1 rounded-home-sm  text-gray-700  transition  group-hover:text-[#ED661D] font-plus">
                                <?php echo e(trim((string) (($academy_phases[2]['badge_label'] ?? '') ?: $academy_defaults[2]['badge']))); ?>
                            </span>
                        </div>

                        <!-- List + Button -->
                        <div class="flex flex-col flex-1">
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 flex-1 mb-12 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[2] ?? null]); ?>
                            </ul>

                            <!-- Button luôn nằm dưới cùng -->
                            <a href="<?php echo e($academy_see_href(2)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-sm text-gray-700 transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Card 4: Build E-Commerce -->
                <div
                    class="group w-full max-w-sm rounded-home-lg transition-all duration-300 border border-gray-200 hover:border-transparent hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary flex flex-col hover:text-home-primary">

                    <div class="space-y-6 bg-white rounded-home-lg p-4 sm:p-5 md:p-6 transition w-full h-full flex flex-col">

                        <!-- Icon -->
                        <div class="overflow-hidden flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <svg class="duration-500 ease-out hover:scale-110" width="60" height="60"
                                viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="var(--home-primary)"
                                    fill-opacity="0.2" />
                                <path
                                    d="M35.5 27.25C35.5 28.7087 34.9205 30.1076 33.8891 31.1391C32.8576 32.1705 31.4587 32.75 30 32.75C28.5413 32.75 27.1424 32.1705 26.1109 31.1391C25.0795 30.1076 24.5 28.7087 24.5 27.25M17.7666 21.7967H42.2334M18.175 21.0171C17.818 21.4931 17.625 22.0721 17.625 22.6671V41C17.625 41.7293 17.9147 42.4288 18.4305 42.9445C18.9462 43.4603 19.6457 43.75 20.375 43.75H39.625C40.3543 43.75 41.0538 43.4603 41.5695 42.9445C42.0853 42.4288 42.375 41.7293 42.375 41V22.6671C42.375 22.0721 42.182 21.4931 41.825 21.0171L39.075 17.35C38.8188 17.0085 38.4867 16.7313 38.1048 16.5403C37.723 16.3494 37.3019 16.25 36.875 16.25H23.125C22.6981 16.25 22.277 16.3494 21.8952 16.5403C21.5133 16.7313 21.1812 17.0085 20.925 17.35L18.175 21.0171Z"
                                    stroke="var(--home-primary)" stroke-width="2.75" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>

                        <!-- Title + Badge -->
                        <div class="mb-2 sm:mb-3 flex justify-between">
                            <?php
                            $title = ($academy_phases[3]['title_display'] ?? '') ?: $academy_defaults[0]['title'];
                            echo e(getPhaseTitle($title));
                            ?>
                            <span
                                class="text-[11px] px-2 py-1 rounded-home-sm text-gray-700 transition group-hover:text-home-primary font-plus">
                                <?php echo e(trim((string) (($academy_phases[3]['badge_label'] ?? '') ?: $academy_defaults[3]['badge']))); ?>
                            </span>
                        </div>

                        <!-- List + Button -->
                        <div class="flex flex-col flex-1">
                            <ul class="space-y-2 sm:space-y-3 text-sm text-gray-600 flex-1 mb-12 font-plus">
                                <?php echo View::include('parts/home/laragon-academy-lessons-ul', ['phase' => $academy_phases[3] ?? null]); ?>
                            </ul>

                            <!-- Button luôn nằm dưới cùng -->
                            <a href="<?php echo e($academy_see_href(3)); ?>"
                                class="block text-center w-full font-semibold mt-auto py-2.5 rounded-home-md bg-gray-100 font-plus text-sm text-gray-700 transition hover:bg-home-primary hover:text-white">
                                See Documents
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        let academyMobileIndex = 0;
        let autoSlideInterval;

        const slider = document.getElementById('academy-slider-mobile');
        const cards = document.querySelectorAll('.academy-card');
        const totalAcademyCards = cards.length;

        /* Lấy width + gap REAL (chuẩn 100%) */
        function getCardWidth() {
            if (!cards.length) return 0;

            const card = cards[0];
            const style = window.getComputedStyle(slider);
            const gap = parseInt(style.columnGap || style.gap || 0);

            return card.offsetWidth + gap;
        }

        /* Update dots */
        function updateAcademyMobileDots() {
            const dots = document.querySelectorAll('.academy-dot-mobile');

            dots.forEach((dot, index) => {
                const inner = dot.querySelector('.academy-dot-mobile-inner');
                if (!inner) return;
                if (index === academyMobileIndex) {
                    inner.classList.remove('bg-gray-300');
                    inner.classList.add('bg-home-primary');
                } else {
                    inner.classList.remove('bg-home-primary');
                    inner.classList.add('bg-gray-300');
                }
            });
        }

        /* Scroll tới card */
        function goToAcademyMobile(index) {
            if (!slider) return;

            academyMobileIndex = (index + totalAcademyCards) % totalAcademyCards;

            const cardWidth = getCardWidth();

            slider.scrollTo({
                left: academyMobileIndex * cardWidth,
                behavior: 'smooth'
            });

            updateAcademyMobileDots();
        }

        function nextAcademyMobile() {
            goToAcademyMobile(academyMobileIndex + 1);
        }

        function prevAcademyMobile() {
            goToAcademyMobile(academyMobileIndex - 1);
        }

        /* Auto slide */
        function startAutoSlide() {
            stopAutoSlide();

            autoSlideInterval = setInterval(() => {
                nextAcademyMobile();
            }, 3000);
        }

        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
            }
        }

        /* Pause khi user tương tác */
        if (slider) {
            slider.addEventListener('touchstart', stopAutoSlide);
            slider.addEventListener('mouseenter', stopAutoSlide);

            slider.addEventListener('touchend', startAutoSlide);
            slider.addEventListener('mouseleave', startAutoSlide);

            /* Sync index khi scroll tay */
            slider.addEventListener('scroll', () => {
                const cardWidth = getCardWidth();
                const newIndex = Math.round(slider.scrollLeft / cardWidth);

                if (newIndex !== academyMobileIndex) {
                    academyMobileIndex = newIndex;
                    updateAcademyMobileDots();
                }
            });
        }

        /* Gán ra global để button onclick dùng */
        window.nextAcademyMobile = nextAcademyMobile;
        window.prevAcademyMobile = prevAcademyMobile;
        window.goToAcademyMobile = goToAcademyMobile;

        /* Init */
        updateAcademyMobileDots();
        startAutoSlide();
    });
</script>