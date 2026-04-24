<?php
$php_tutorial_phases = $php_tutorial_phases ?? [];
$php_tutorial_esc = static function ($v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
?>
<!-- Mobile Menu Button -->
<div x-data="{ sidebarOpen: false }" @close-php-sidebar.window="sidebarOpen = false" class="">

    <!-- Menu Button - Mobile Only -->
    <button
        @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden fixed top-20 right-4 z-50 flex items-center gap-2 px-4 py-2 bg-white rounded-home-md shadow-md border border-gray-200 hover:bg-gray-50 transition-colors"
        aria-label="Toggle menu">
        <span class="text-sm font-medium text-gray-700" style="font-family: 'Plus Jakarta Sans', sans-serif;">Menu</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 12H21M3 6H21M3 18H21" stroke="var(--home-body)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <!-- Mobile Overlay -->
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
        style="display: none;"></div>

    <!-- Left Sidebar - PHP Tutorial Learning Roadmap -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
        class="fixed lg:sticky top-0 right-0 lg:top-24 h-screen lg:h-auto w-full lg:w-auto lg:max-w-none bg-white lg:bg-transparent z-40 lg:z-auto flex-shrink-0 shadow-xl lg:shadow-none border-l border-gray-200 lg:border-0 transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="sticky lg:relative top-0 flex flex-col items-start gap-6 p-6 lg:p-0 pb-6 lg:pb-0">
            <!-- Mobile Header -->
            <div class="lg:hidden flex items-center justify-between w-full mb-4 pb-4 border-b border-gray-200">
                <span class="text-[18px] font-semibold text-home-body leading-[27px]">
                    <?php echo e(__('php_tutorial.learning_roadmap')); ?>
                </span>
                <button
                    @click="sidebarOpen = false"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-home-md transition-colors"
                    aria-label="Close menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <!-- Desktop Header -->
            <h2 class="hidden lg:block text-[18px] font-semibold text-home-body leading-[27px] ml-[12px]">
                <?php echo e(__('php_tutorial.learning_roadmap')); ?>
            </h2>

            <div class="flex flex-col w-full" x-data="{
            topicToPhase: {},
            activePhase: '',
            activeTopic: '',
            getPhaseForTopic(topic) {
                var m = this.topicToPhase || {};
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                return m[topic] || d.defaultPhase || '';
            },
            init() {
                var d = window.__PHP_TUTORIAL_XDATA__ || {};
                this.topicToPhase = d.topicToPhase || {};
                var st = window.Alpine && Alpine.store('phpTutorial');
                // Phải ưu tiên currentTopic (URL / single tutorial) để mở đúng phase và highlight lesson.
                this.activeTopic = (st && st.activeTopic) ? st.activeTopic : (d.currentTopic || d.firstTopic || 'syntax');
                this.activePhase = this.getPhaseForTopic(this.activeTopic);
                if (window.Alpine && Alpine.store('phpTutorial')) {
                    Alpine.store('phpTutorial').activeTopic = this.activeTopic;
                    this.$watch('$store.phpTutorial.activeTopic', function (value) {
                        this.activeTopic = value;
                        this.activePhase = this.getPhaseForTopic(value);
                    }.bind(this));
                }
            }
        }">
                <?php if ($php_tutorial_phases === []): ?>
                    <p class="text-sm text-gray-500 px-3 font-plus">Chưa có danh mục hoặc bài hướng dẫn (posttype <code class="text-xs">tutorial</code>).</p>
                <?php endif; ?>
                <?php foreach ($php_tutorial_phases as $phase): ?>
                    <?php
                    $pid = (string) ($phase['alpine_id'] ?? '');
                    $puid = 'pt' . preg_replace('/[^a-zA-Z0-9]/', '', $pid);
                    $v = (int) ($phase['icon_variant'] ?? 0);
                    ?>
                    <div class="flex flex-col gap-2 p-3">
                        <button type="button"
                            @click="activePhase = activePhase === '<?php echo $php_tutorial_esc($pid); ?>' ? null : '<?php echo $php_tutorial_esc($pid); ?>'"
                            class="flex items-center gap-3 rounded-home-md transition-colors text-left w-full group ">
                            <?php if ($v === 0): ?>
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="var(--home-success)" fill-opacity="0.2" />
                                    <path
                                        d="M45.2784 26.9449V36.1115M20.8339 30.7643V36.1115C20.8339 37.3271 21.7997 38.4929 23.5188 39.3524C25.2379 40.212 27.5694 40.6949 30.0006 40.6949C32.4317 40.6949 34.7633 40.212 36.4824 39.3524C38.2015 38.4929 39.1673 37.3271 39.1673 36.1115V30.7643M44.3923 28.3535C44.6658 28.2328 44.8978 28.0346 45.0598 27.7833C45.2217 27.532 45.3064 27.2388 45.3033 26.9399C45.3002 26.641 45.2095 26.3495 45.0424 26.1016C44.8753 25.8538 44.6392 25.6604 44.3632 25.5454L31.2686 19.581C30.8706 19.3994 30.4381 19.3054 30.0006 19.3054C29.563 19.3054 29.1306 19.3994 28.7325 19.581L15.6395 25.5393C15.3675 25.6584 15.1361 25.8542 14.9736 26.1028C14.8111 26.3513 14.7246 26.6418 14.7246 26.9387C14.7246 27.2357 14.8111 27.5262 14.9736 27.7747C15.1361 28.0233 15.3675 28.2191 15.6395 28.3382L28.7325 34.3087C29.1306 34.4903 29.563 34.5843 30.0006 34.5843C30.4381 34.5843 30.8706 34.4903 31.2686 34.3087L44.3923 28.3535Z"
                                        stroke="var(--home-success)" stroke-width="3.05556" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            <?php elseif ($v === 1): ?>
                                <svg width="55" height="55" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                    <rect width="55" height="55" rx="5.5" fill="#9747FF" fill-opacity="0.15" />
                                    <path
                                        d="M13.5977 19.2502H19.0977M13.5977 24.7502H19.0977M13.5977 30.2502H19.0977M13.5977 35.7502H19.0977M23.9102 22.0002H30.7852M23.9102 27.5002H32.8477M23.9102 33.0002H30.0977M19.0977 13.7502H35.5977C37.1164 13.7502 38.3477 14.9814 38.3477 16.5002V38.5002C38.3477 40.019 37.1164 41.2502 35.5977 41.2502H19.0977C17.5789 41.2502 16.3477 40.019 16.3477 38.5002V16.5002C16.3477 14.9814 17.5789 13.7502 19.0977 13.7502Z"
                                        stroke="#9747FF" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            <?php elseif ($v === 2): ?>
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="#ED661D" fill-opacity="0.15" />
                                    <path
                                        d="M42.375 20.3751C42.375 22.6533 36.8345 24.5001 30 24.5001C23.1655 24.5001 17.625 22.6533 17.625 20.3751M42.375 20.3751C42.375 18.0969 36.8345 16.2501 30 16.2501C23.1655 16.2501 17.625 18.0969 17.625 20.3751M42.375 20.3751V39.6251C42.375 40.7191 41.0712 41.7684 38.7504 42.5419C36.4297 43.3155 33.2821 43.7501 30 43.7501C26.7179 43.7501 23.5703 43.3155 21.2496 42.5419C18.9288 41.7684 17.625 40.7191 17.625 39.6251V20.3751M17.625 30.0001C17.625 31.0941 18.9288 32.1434 21.2496 32.9169C23.5703 33.6905 26.7179 34.1251 30 34.1251C33.2821 34.1251 36.4297 33.6905 38.7504 32.9169C41.0712 32.1434 42.375 31.0941 42.375 30.0001"
                                        stroke="#ED661D" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            <?php else: ?>
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.5" y="2.5" width="55" height="55" rx="5.5" fill="var(--home-primary)" fill-opacity="0.2"></rect>
                                    <path d="M35.5 27.2501C35.5 28.7088 34.9205 30.1078 33.8891 31.1392C32.8576 32.1707 31.4587 32.7501 30 32.7501C28.5413 32.7501 27.1424 32.1707 26.1109 31.1392C25.0795 30.1078 24.5 28.7088 24.5 27.2501M17.7666 21.7969H42.2334M18.175 21.0172C17.818 21.4933 17.625 22.0722 17.625 22.6672V41.0001C17.625 41.7295 17.9147 42.4289 18.4305 42.9447C18.9462 43.4604 19.6457 43.7501 20.375 43.7501H39.625C40.3543 43.7501 41.0538 43.4604 41.5695 42.9447C42.0853 42.4289 42.375 41.7295 42.375 41.0001V22.6672C42.375 22.0722 42.182 21.4933 41.825 21.0172L39.075 17.3501C38.8188 17.0086 38.4867 16.7314 38.1048 16.5404C37.723 16.3495 37.3019 16.2501 36.875 16.2501H23.125C22.6981 16.2501 22.277 16.3495 21.8952 16.5404C21.5133 16.7314 21.1812 17.0086 20.925 17.3501L18.175 21.0172Z" stroke="var(--home-primary)" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            <?php endif; ?>

                            <div class="flex-1 min-w-0">
                                <div class="text-[18px] font-semibold rounded-sm text-home-heading leading-[27px] mb-1 font-plus">
                                    <?php echo $php_tutorial_esc($phase['title_display'] ?? ''); ?>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-md <?php echo $php_tutorial_esc($phase['badge_style'] ?? 'bg-gray-100 text-gray-700'); ?>">
                                        <?php echo $php_tutorial_esc($phase['badge_label'] ?? ''); ?>
                                    </span>
                                    <div class="flex items-center gap-2 bg-home-surface-light/50 px-1.5 py-0.5 rounded-md">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#<?php echo $php_tutorial_esc($puid); ?>_clock)">
                                                <path
                                                    d="M5.99902 3.00003V6.00003L7.99902 7.00003M10.999 6.00003C10.999 8.76145 8.76045 11 5.99902 11C3.2376 11 0.999023 8.76145 0.999023 6.00003C0.999023 3.23861 3.2376 1.00003 5.99902 1.00003C8.76045 1.00003 10.999 3.23861 10.999 6.00003Z"
                                                    stroke="#97A4B2" stroke-linecap="round" stroke-linejoin="round" />
                                            </g>
                                            <defs>
                                                <clipPath id="<?php echo $php_tutorial_esc($puid); ?>_clock">
                                                    <rect width="12" height="12" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span class="text-[10px] text-gray-500"><?php echo $php_tutorial_esc($phase['duration_label'] ?? ''); ?></span>
                                    </div>
                                </div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                :class="activePhase === '<?php echo $php_tutorial_esc($pid); ?>' ? 'rotate-180' : ''"
                                class="transform transition-transform flex-shrink-0">
                                <path d="M6 9L12 15L18 9" stroke="var(--home-body)" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div x-show="activePhase === '<?php echo $php_tutorial_esc($pid); ?>'" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="flex flex-col gap-1">
                            <?php foreach ($phase['lessons'] ?? [] as $lesson): ?>
                                <?php
                                $ls = (string) ($lesson['slug'] ?? '');
                                $lt = (string) ($lesson['title'] ?? '');
                                $php_tutorial_sidebar_permalink = ($php_tutorial_sidebar_link_mode ?? 'query') === 'permalink';
                                $php_tutorial_current_sidebar_topic = (string) ($php_tutorial_current_topic ?? '');
                                $lesson_href = link_posts($ls, 'tutorial', defined('APP_LANG') ? APP_LANG : '');
                                $lesson_is_active = $ls !== '' && $ls === $php_tutorial_current_sidebar_topic;
                                ?>
                                <?php if ($php_tutorial_sidebar_permalink): ?>
                                    <a href="<?php echo $php_tutorial_esc($lesson_href); ?>"
                                        class="flex items-center gap-3 p-4 self-stretch rounded-home-md transition-colors group <?php echo $lesson_is_active ? 'bg-home-surface-light' : 'hover:bg-home-surface-light'; ?>">
                                        <?php if (!$lesson_is_active): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="flex-shrink-0">
                                                <path
                                                    d="M4 12.15V4.00001C4 3.46957 4.21071 2.96087 4.58579 2.58579C4.96086 2.21072 5.46957 2.00001 6 2.00001H14M14 2.00001C14.3169 1.99923 14.6308 2.06122 14.9236 2.18239C15.2164 2.30357 15.4823 2.48153 15.706 2.70601L19.294 6.29401C19.5185 6.51768 19.6964 6.78359 19.8176 7.0764C19.9388 7.36921 20.0008 7.68312 20 8.00001M14 2.00001V7.00001C14 7.26522 14.1054 7.51958 14.2929 7.70711C14.4804 7.89465 14.7348 8.00001 15 8.00001H20M20 8.00001V20C20 20.5304 19.7893 21.0391 19.4142 21.4142C19.0391 21.7893 18.5304 22 18 22H14.65M5 16L2 19L5 22M9 22L12 19L9 16"
                                                    stroke="var(--home-body)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="flex-shrink-0">
                                                <path
                                                    d="M12 21V7M12 21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H3C2.73478 18 2.48043 17.8946 2.29289 17.7071C2.10536 17.5196 2 17.2652 2 17V4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7M12 21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H21C21.2652 18 21.5196 17.8946 21.7071 17.7071C21.8946 17.5196 22 17.2652 22 17V15.7M12 7C12 5.93913 12.4214 4.92172 13.1716 4.17157C13.9217 3.42143 14.9391 3 16 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V6M16 12L18 14L22 10"
                                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        <?php endif; ?>
                                        <span class="text-sm transition-colors <?php echo $lesson_is_active ? 'font-medium text-home-primary' : 'text-gray-700 group-hover:text-home-primary'; ?>"><?php echo $php_tutorial_esc($lt); ?></span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $php_tutorial_esc($lesson_href); ?>"
                                        :class="activeTopic === '<?php echo $php_tutorial_esc($ls); ?>' ? 'bg-home-surface-light' : 'hover:bg-home-surface-light'"
                                        class="flex items-center gap-3 p-4 self-stretch rounded-home-md transition-colors group">
                                        <svg x-show="activeTopic !== '<?php echo $php_tutorial_esc($ls); ?>'" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" class="flex-shrink-0">
                                            <path
                                                d="M4 12.15V4.00001C4 3.46957 4.21071 2.96087 4.58579 2.58579C4.96086 2.21072 5.46957 2.00001 6 2.00001H14M14 2.00001C14.3169 1.99923 14.6308 2.06122 14.9236 2.18239C15.2164 2.30357 15.4823 2.48153 15.706 2.70601L19.294 6.29401C19.5185 6.51768 19.6964 6.78359 19.8176 7.0764C19.9388 7.36921 20.0008 7.68312 20 8.00001M14 2.00001V7.00001C14 7.26522 14.1054 7.51958 14.2929 7.70711C14.4804 7.89465 14.7348 8.00001 15 8.00001H20M20 8.00001V20C20 20.5304 19.7893 21.0391 19.4142 21.4142C19.0391 21.7893 18.5304 22 18 22H14.65M5 16L2 19L5 22M9 22L12 19L9 16"
                                                stroke="var(--home-body)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <svg x-show="activeTopic === '<?php echo $php_tutorial_esc($ls); ?>'" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" class="flex-shrink-0">
                                            <path
                                                d="M12 21V7M12 21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H3C2.73478 18 2.48043 17.8946 2.29289 17.7071C2.10536 17.5196 2 17.2652 2 17V4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7M12 21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H21C21.2652 18 21.5196 17.8946 21.7071 17.7071C21.8946 17.5196 22 17.2652 22 17V15.7M12 7C12 5.93913 12.4214 4.92172 13.1716 4.17157C13.9217 3.42143 14.9391 3 16 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V6M16 12L18 14L22 10"
                                                stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <span
                                            :class="activeTopic === '<?php echo $php_tutorial_esc($ls); ?>' ? 'font-medium text-home-primary' : 'text-gray-700 group-hover:text-home-primary'"
                                            class="text-sm transition-colors"><?php echo $php_tutorial_esc($lt); ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>