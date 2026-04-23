<section class="py-16 sm:py-24 bg-cover bg-center bg-no-repeat" style="
    background:
      linear-gradient(rgba(233,243,253,0.6), rgba(233,243,253,0.6)),
      url('<?php echo defined('APP_THEME_NAME') && function_exists('theme_assets') ? theme_assets('images/get-started-bg.webp') : ''; ?>') center / cover no-repeat;
  ">
    <div class="container mx-auto px-4 sm:px-6">
        <!-- Section Title -->
        <h2 class="sr sr--fade-up w-full text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-8 font-space" style="--sr-delay: 0ms">
            <?php echo e(__('home_get_started.heading')); ?>
        </h2>

        <!-- Steps Container -->
        <div class="relative">

            <!-- Steps Grid -->
            <div class="grid grid-cols-3 gap-4 sm:gap-8 md:gap-12 lg:gap-16 relative z-10">

                <!-- Step 1 -->
                <div class="sr sr--fade-up flex flex-col items-center text-center flex-shrink-0" style="--sr-delay: 0ms">
                    <div class="relative w-[80px] h-[80px] mx-auto"
                        style="filter: drop-shadow(0 1.6px 4px rgba(43,140,238,0.10)) drop-shadow(0 7.2px 7.2px rgba(43,140,238,0.09)) drop-shadow(0 16.8px 10.4px rgba(43,140,238,0.05)) drop-shadow(0 29.6px 12px rgba(43,140,238,0.01));">
                        <svg class="w-[84px] h-[104px] sm:w-[112px] sm:h-[139px] mx-auto" viewBox="0 0 112 139" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g filter="url(#filter0_dddd_47_536)">
                                <circle cx="56" cy="43.2002" r="40" fill="white" />
                            </g>
                            <path
                                d="M56.0001 47.9998V28.7998M56.0001 47.9998L48.0001 39.9998M56.0001 47.9998L64.0001 39.9998M70.4001 47.9998V54.3998C70.4001 55.2485 70.063 56.0624 69.4628 56.6625C68.8627 57.2627 68.0488 57.5998 67.2001 57.5998H44.8001C43.9514 57.5998 43.1375 57.2627 42.5374 56.6625C41.9372 56.0624 41.6001 55.2485 41.6001 54.3998V47.9998"
                                stroke="var(--home-success)" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                            <defs>
                                <filter id="filter0_dddd_47_536" x="0" y="0.000195265" width="112" height="138.667"
                                    filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="2.13333" />
                                    <feGaussianBlur stdDeviation="2.66667" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix"
                                        result="effect1_dropShadow_47_536" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="9.6" />
                                    <feGaussianBlur stdDeviation="4.8" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                                    <feBlend mode="normal" in2="effect1_dropShadow_47_536"
                                        result="effect2_dropShadow_47_536" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="22.4" />
                                    <feGaussianBlur stdDeviation="6.93333" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                                    <feBlend mode="normal" in2="effect2_dropShadow_47_536"
                                        result="effect3_dropShadow_47_536" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="39.4667" />
                                    <feGaussianBlur stdDeviation="8" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                                    <feBlend mode="normal" in2="effect3_dropShadow_47_536"
                                        result="effect4_dropShadow_47_536" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_47_536"
                                        result="shape" />
                                </filter>
                            </defs>
                        </svg>

                    </div>

                    <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-2 sm:mb-6 mt-4 font-plus">
                        <?php echo e(__('home_get_started.step1_title')); ?>
                    </h3>

                    <p class="text-xs sm:text-base text-gray-600 leading-relaxed max-w-xs mx-auto font-plus">
                        <?php echo e(__('home_get_started.step1_desc')); ?>
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="sr sr--fade-up flex flex-col items-center text-center flex-shrink-0" style="--sr-delay: 80ms">
                    <div class="relative w-[80px] h-[80px] mx-auto"
                        style="filter: drop-shadow(0 1.6px 4px rgba(43,140,238,0.10)) drop-shadow(0 7.2px 7.2px rgba(43,140,238,0.09)) drop-shadow(0 16.8px 10.4px rgba(43,140,238,0.05)) drop-shadow(0 29.6px 12px rgba(43,140,238,0.01));">
                        <svg class="w-[84px] h-[104px] sm:w-[112px] sm:h-[139px] mx-auto" viewBox="0 0 112 139" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g filter="url(#filter0_dddd_47_543)">
                                <circle cx="56" cy="43.2002" r="40" fill="white" />
                            </g>
                            <path
                                d="M43.1999 46.4C42.8972 46.401 42.6003 46.3161 42.3439 46.1551C42.0874 45.9942 41.8819 45.7637 41.7512 45.4906C41.6205 45.2175 41.57 44.9129 41.6055 44.6122C41.6411 44.3115 41.7612 44.0271 41.9519 43.792L57.7919 27.472C57.9108 27.3348 58.0727 27.2421 58.2511 27.2091C58.4295 27.1761 58.6139 27.2048 58.7739 27.2904C58.9339 27.376 59.0601 27.5134 59.1317 27.6801C59.2033 27.8469 59.2161 28.033 59.1679 28.208L56.0959 37.84C56.0054 38.0824 55.9749 38.3432 56.0073 38.6C56.0396 38.8568 56.1338 39.1019 56.2817 39.3143C56.4296 39.5266 56.6268 39.7 56.8564 39.8194C57.086 39.9389 57.3411 40.0008 57.5999 40H68.7999C69.1027 39.9989 69.3996 40.0838 69.656 40.2448C69.9125 40.4058 70.118 40.6362 70.2487 40.9093C70.3794 41.1824 70.4299 41.487 70.3943 41.7877C70.3588 42.0884 70.2387 42.3728 70.0479 42.608L54.2079 58.928C54.0891 59.0651 53.9272 59.1578 53.7488 59.1908C53.5703 59.2238 53.386 59.1951 53.226 59.1096C53.066 59.024 52.9398 58.8865 52.8682 58.7198C52.7966 58.5531 52.7838 58.3669 52.8319 58.192L55.9039 48.56C55.9945 48.3175 56.0249 48.0567 55.9926 47.8C55.9602 47.5432 55.8661 47.2981 55.7182 47.0857C55.5703 46.8733 55.3731 46.6999 55.1435 46.5805C54.9139 46.4611 54.6587 46.3991 54.3999 46.4H43.1999Z"
                                stroke="var(--home-primary)" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                            <defs>
                                <filter id="filter0_dddd_47_543" x="0" y="0.000195265" width="112" height="138.667"
                                    filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="2.13333" />
                                    <feGaussianBlur stdDeviation="2.66667" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix"
                                        result="effect1_dropShadow_47_543" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="9.6" />
                                    <feGaussianBlur stdDeviation="4.8" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                                    <feBlend mode="normal" in2="effect1_dropShadow_47_543"
                                        result="effect2_dropShadow_47_543" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="22.4" />
                                    <feGaussianBlur stdDeviation="6.93333" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                                    <feBlend mode="normal" in2="effect2_dropShadow_47_543"
                                        result="effect3_dropShadow_47_543" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="39.4667" />
                                    <feGaussianBlur stdDeviation="8" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                                    <feBlend mode="normal" in2="effect3_dropShadow_47_543"
                                        result="effect4_dropShadow_47_543" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_47_543"
                                        result="shape" />
                                </filter>
                            </defs>
                        </svg>

                    </div>

                    <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-2 sm:mb-6 mt-4 font-plus">
                        <?php echo e(__('home_get_started.step2_title')); ?>
                    </h3>

                    <p class="text-xs sm:text-base text-gray-600 leading-relaxed max-w-xs mx-auto font-plus">
                        <?php echo e(__('home_get_started.step2_desc')); ?>
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="sr sr--fade-up flex flex-col items-center text-center flex-shrink-0" style="--sr-delay: 160ms">
                    <div class="relative w-[80px] h-[80px] mx-auto"
                        style="filter: drop-shadow(0 1.6px 4px rgba(43,140,238,0.10)) drop-shadow(0 7.2px 7.2px rgba(43,140,238,0.09)) drop-shadow(0 16.8px 10.4px rgba(43,140,238,0.05)) drop-shadow(0 29.6px 12px rgba(43,140,238,0.01));">
                        <svg class="w-[84px] h-[104px] sm:w-[112px] sm:h-[139px] mx-auto" viewBox="0 0 112 139" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g filter="url(#filter0_dddd_47_550)">
                                <circle cx="56" cy="43.2002" r="40" fill="white" />
                            </g>
                            <path d="M62.4 52.8001L72 43.2001L62.4 33.6001M49.6 33.6001L40 43.2001L49.6 52.8001"
                                stroke="#9747FF" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                            <defs>
                                <filter id="filter0_dddd_47_550" x="0" y="0.000195265" width="112" height="138.667"
                                    filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="2.13333" />
                                    <feGaussianBlur stdDeviation="2.66667" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix"
                                        result="effect1_dropShadow_47_550" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="9.6" />
                                    <feGaussianBlur stdDeviation="4.8" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                                    <feBlend mode="normal" in2="effect1_dropShadow_47_550"
                                        result="effect2_dropShadow_47_550" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="22.4" />
                                    <feGaussianBlur stdDeviation="6.93333" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                                    <feBlend mode="normal" in2="effect2_dropShadow_47_550"
                                        result="effect3_dropShadow_47_550" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="39.4667" />
                                    <feGaussianBlur stdDeviation="8" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                                    <feBlend mode="normal" in2="effect3_dropShadow_47_550"
                                        result="effect4_dropShadow_47_550" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_47_550"
                                        result="shape" />
                                </filter>
                            </defs>
                        </svg>

                    </div>

                    <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-2 sm:mb-6 mt-4 font-plus">
                        <?php echo e(__('home_get_started.step3_title')); ?>
                    </h3>

                    <p class="text-xs sm:text-base text-gray-600 leading-relaxed max-w-xs mx-auto font-plus">
                        <?php echo e(__('home_get_started.step3_desc')); ?>
                    </p>
                </div>
            </div>

            <!-- Dotted line (desktop only) -->
            <div class="hidden md:block absolute top-[43px] left-1/2 -translate-x-1/2 z-0 pointer-events-none"
                style="width: calc(100% - 224px - 6rem);">
                <svg width="100%" height="4" viewBox="0 0 100 4" fill="none" xmlns="http://www.w3.org/2000/svg"
                    preserveAspectRatio="none">
                    <line x1="10" y1="0.2" x2="95" y2="0.2" stroke="var(--home-accent)" stroke-width="2" stroke-dasharray="0.1 3"
                        stroke-linecap="round" />
                </svg>
            </div>
        </div>

        <!-- CTA -->
        <div class="sr sr--fade-up text-center mt-[56px]" style="--sr-delay: 100ms">
            <a href="<?php echo e(base_url('usage-guide', APP_LANG)); ?>"
                class="inline-flex items-center text-home-primary font-medium text-base sm:text-lg hover:text-home-primary-hover transition-colors group relative font-plus">
                <span class="font-bold"><?php echo e(__('home_get_started.cta_guide')); ?></span>
                <svg width="20" height="20" class="transform group-hover:translate-x-1 transition-transform" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
        </div>

    </div>
</section>