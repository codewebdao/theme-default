<section class="relative w-full overflow-hidden bg-gray-50 pb-16 sm:pb-20 md:pb-24" id="cms-review-banner">
    <div class="absolute inset-0 overflow-hidden">
        <?php
        $__cms_banner_alt = function_exists('__') ? (string) __('cms_review_banner.image_alt') : 'Banner CMS Review';
        $__cms_banner = function_exists('cmsfullform_theme_responsive_webp_img')
            ? cmsfullform_theme_responsive_webp_img('images/banner_cms.webp', [640, 960, 1200, 1536], [
                'alt'               => $__cms_banner_alt,
                'class'             => 'relative z-[1] h-full min-h-full w-full object-cover object-center',
                'sizes'             => '100vw',
                'loading'           => 'eager',
                'fetchpriority'     => 'high',
                'decoding'          => 'sync',
                'mobile_webp_width' => 640,
                'mobile_webp_bp'    => 640,
            ])
            : '';
        if ($__cms_banner !== '') {
            echo $__cms_banner;
        } else {
            ?>
        <img src="<?php echo e(function_exists('theme_assets') ? theme_assets('images/banner_cms.webp') : ''); ?>"
            alt="<?php echo e($__cms_banner_alt); ?>"
            class="relative z-[1] h-full min-h-full w-full object-cover object-center"
            fetchpriority="high"
            loading="eager"
            decoding="sync" />
            <?php
        }
        ?>
        <div class="absolute -translate-x-1/2 rounded-full bg-[#2377FD80]/10 blur-[250px] sm:h-[550px] sm:w-full"
            style="left: 100%; top: 0;"></div>
        <div class="absolute -translate-x-1/2 rounded-full bg-[#63ECFF80]/15 blur-[200px] sm:h-[550px] sm:w-full"
            style="left: 0; top: 100px;"></div>
        <div class="absolute left-1/2 top-0 h-[800px] w-[800px] max-w-[1366px] -translate-x-1/2 rounded-full bg-[#63ECFF]/20 blur-[100px] sm:h-[1366px] sm:w-[1366px]"
            style="opacity: 0.35;"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.35]"
            style="background-image: linear-gradient(rgba(35,119,253,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(35,119,253,0.06) 1px, transparent 1px); background-size: 48px 48px;">
        </div>
        <!-- Fade into next section (cms-comparison uses bg-gray-50) — avoids a hard blue/white cut -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-[2] h-32 bg-gradient-to-b from-transparent via-gray-50/70 to-gray-50 sm:h-40 md:h-48"
            aria-hidden="true"></div>
    </div>

    <div class="relative container mx-auto flex min-h-[400px] flex-col px-4 sm:min-h-[460px] sm:px-6 md:min-h-[500px]">
        <div class="pointer-events-none absolute inset-x-0 top-0 bottom-0 z-[1] mx-auto max-w-5xl sm:max-w-6xl"
            aria-hidden="true">
            <div class="cms-review-float-card cms-review-float-card--tl" data-rot="-7" data-speed="1.05" data-phase="0">
                <svg width="169" height="202" viewBox="0 0 169 202" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_dddd_1994_3710)">
                        <rect x="16.7754" y="34.944" width="106" height="106" rx="12"
                            transform="rotate(-18.8046 16.7754 34.944)" fill="white" />
                    </g>
                    <g clip-path="url(#clip0_1994_3710)">
                        <path
                            d="M98.4202 66.0801L90.3595 52.1837C90.1402 51.8056 89.8174 51.4982 89.4291 51.2975C89.0408 51.0969 88.6032 51.0115 88.168 51.0514L58.2848 53.185C57.8937 53.2206 57.5183 53.3562 57.1947 53.5787C56.8711 53.8012 56.6101 54.1031 56.4367 54.4555C56.2633 54.8079 56.1835 55.1989 56.2047 55.5911C56.2259 55.9832 56.3476 56.3634 56.558 56.695L73.1047 81.6702C73.3387 82.0393 73.6734 82.3339 74.0693 82.5191C74.4652 82.7042 74.9059 82.7723 75.3392 82.7153L91.267 80.6203M56.4574 54.4657L77.6454 64.8894M99.5719 85.7643C99.3036 86.3095 98.8297 86.7259 98.2545 86.9217C97.6793 87.1176 97.0498 87.077 96.5045 86.8088L91.8923 84.5398C91.3471 84.2715 90.9308 83.7976 90.7349 83.2224C90.539 82.6472 90.5796 82.0177 90.8478 81.4724L98.8394 65.228C99.1077 64.6828 99.5816 64.2665 100.157 64.0706C100.732 63.8747 101.362 63.9154 101.907 64.1835L106.519 66.4525C107.064 66.7208 107.481 67.1947 107.676 67.77C107.872 68.3452 107.832 68.9747 107.564 69.52L99.5719 85.7643ZM86.0961 65.4349C86.912 67.8311 85.631 70.4351 83.2348 71.251C80.8386 72.0669 78.2346 70.7859 77.4187 68.3897C76.6028 65.9935 77.8838 63.3896 80.28 62.5736C82.6762 61.7577 85.2801 63.0387 86.0961 65.4349Z"
                            stroke="#ED661D" stroke-width="4.58333" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <filter id="filter0_dddd_1994_3710" x="0" y="-2.38419e-07" width="168.062" height="201.395"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="2.66667" />
                            <feGaussianBlur stdDeviation="3.33333" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1994_3710" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="12" />
                            <feGaussianBlur stdDeviation="6" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                            <feBlend mode="normal" in2="effect1_dropShadow_1994_3710"
                                result="effect2_dropShadow_1994_3710" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="28" />
                            <feGaussianBlur stdDeviation="8.66667" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                            <feBlend mode="normal" in2="effect2_dropShadow_1994_3710"
                                result="effect3_dropShadow_1994_3710" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="49.3333" />
                            <feGaussianBlur stdDeviation="10" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                            <feBlend mode="normal" in2="effect3_dropShadow_1994_3710"
                                result="effect4_dropShadow_1994_3710" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_1994_3710"
                                result="shape" />
                        </filter>
                        <clipPath id="clip0_1994_3710">
                            <rect width="55" height="55" fill="white"
                                transform="translate(49.7686 51.1752) rotate(-18.8046)" />
                        </clipPath>
                    </defs>
                </svg>

            </div>
            <div class="cms-review-float-card cms-review-float-card--tr" data-rot="6" data-speed="0.92" data-phase="1.3">
                <svg width="142" height="190" viewBox="0 0 142 190" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_dddd_2_43089)">
                        <rect x="41.4395" y="20" width="90" height="90" rx="10.1887"
                            transform="rotate(17.4895 41.4395 20)" fill="white" />
                    </g>
                    <g clip-path="url(#clip0_2_43089)">
                        <path
                            d="M59.7264 90.8591L66.7435 68.5894M89.0132 75.6065L55.6087 65.0809M83.1656 94.1646L91.3522 68.1833C91.9981 66.1334 90.86 63.9481 88.8101 63.3022L62.8289 55.1156C60.779 54.4696 58.5936 55.6078 57.9477 57.6576L49.7611 83.6389C49.1152 85.6888 50.2533 87.8742 52.3032 88.5201L78.2845 96.7067C80.3343 97.3526 82.5197 96.2145 83.1656 94.1646Z"
                            stroke="#2B8CEE" stroke-width="3.89151" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <filter id="filter0_dddd_2_43089" x="-0.000663757" y="19.1922" width="141.673" height="169.975"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="2.26415" />
                            <feGaussianBlur stdDeviation="2.83019" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2_43089" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="10.1887" />
                            <feGaussianBlur stdDeviation="5.09434" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                            <feBlend mode="normal" in2="effect1_dropShadow_2_43089"
                                result="effect2_dropShadow_2_43089" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="23.7736" />
                            <feGaussianBlur stdDeviation="7.35849" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                            <feBlend mode="normal" in2="effect2_dropShadow_2_43089"
                                result="effect3_dropShadow_2_43089" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="41.8868" />
                            <feGaussianBlur stdDeviation="8.49057" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                            <feBlend mode="normal" in2="effect3_dropShadow_2_43089"
                                result="effect4_dropShadow_2_43089" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_2_43089" result="shape" />
                        </filter>
                        <clipPath id="clip0_2_43089">
                            <rect width="46.6981" height="46.6981" fill="white"
                                transform="matrix(0.300531 -0.953772 -0.953772 -0.300531 85.8105 105.198)" />
                        </clipPath>
                    </defs>
                </svg>

            </div>
            <div class="cms-review-float-card cms-review-float-card--bl" data-rot="-5" data-speed="1.12" data-phase="2.1">
                <svg width="126" height="165" viewBox="0 0 126 165" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_dddd_2_43090)">
                        <rect x="12.8516" y="38.3037" width="80" height="80" rx="9.0566"
                            transform="rotate(-16.9357 12.8516 38.3037)" fill="white" />
                    </g>
                    <g clip-path="url(#clip0_2_43090)">
                        <path
                            d="M73.6625 48.5497C74.4973 51.2911 68.5071 55.5435 60.283 58.0477C52.059 60.552 44.7153 60.3598 43.8806 57.6184M73.6625 48.5497C72.8278 45.8084 65.4842 45.6162 57.2601 48.1204C49.0361 50.6247 43.0458 54.8771 43.8806 57.6184M73.6625 48.5497L80.716 71.7135C81.1168 73.0299 79.9324 74.7702 77.4233 76.5514C74.9141 78.3326 71.2858 80.0089 67.3365 81.2115C63.3871 82.4141 59.4403 83.0444 56.3643 82.9639C53.2882 82.8834 51.3349 82.0986 50.934 80.7822L43.8806 57.6184M47.4073 69.2003C47.8082 70.5168 49.7615 71.3016 52.8375 71.3821C55.9136 71.4626 59.8604 70.8322 63.8097 69.6296C67.7591 68.427 71.3874 66.7507 73.8966 64.9695C76.4057 63.1883 77.5901 61.448 77.1893 60.1316"
                            stroke="#00E676" stroke-width="3.45912" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <filter id="filter0_dddd_2_43090" x="0.000387192" y="14.2241" width="125.537" height="150.694"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="2.01258" />
                            <feGaussianBlur stdDeviation="2.51572" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2_43090" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="9.0566" />
                            <feGaussianBlur stdDeviation="4.5283" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                            <feBlend mode="normal" in2="effect1_dropShadow_2_43090"
                                result="effect2_dropShadow_2_43090" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="21.1321" />
                            <feGaussianBlur stdDeviation="6.54088" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                            <feBlend mode="normal" in2="effect2_dropShadow_2_43090"
                                result="effect3_dropShadow_2_43090" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="37.2327" />
                            <feGaussianBlur stdDeviation="7.54717" />
                            <feColorMatrix type="matrix"
                                values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                            <feBlend mode="normal" in2="effect3_dropShadow_2_43090"
                                result="effect4_dropShadow_2_43090" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_2_43090" result="shape" />
                        </filter>
                        <clipPath id="clip0_2_43090">
                            <rect width="41.5094" height="41.5094" fill="white"
                                transform="translate(36.3975 50.8572) rotate(-16.9357)" />
                        </clipPath>
                    </defs>
                </svg>

            </div>
            <div class="cms-review-float-card cms-review-float-card--br" data-rot="8" data-speed="0.88" data-phase="0.7">
                <svg width="113" height="143" viewBox="0 0 113 143" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_dddd_2_43091)">
                        <rect x="36.0977" y="9" width="70" height="70" rx="7.92453" transform="rotate(21.1089 36.0977 9)" fill="white" />
                    </g>
                    <g clip-path="url(#clip0_2_43091)">
                        <path d="M44.5787 49.6807L49.0207 38.1744C49.3098 37.4256 49.8845 36.8222 50.6185 36.4971C51.3524 36.172 52.1855 36.1518 52.9344 36.4409L64.2289 40.8011M64.2289 40.8011C64.6767 40.9727 65.0861 41.2313 65.4334 41.5619C65.7808 41.8926 66.0592 42.2888 66.2526 42.7276L69.3627 49.7488C69.5577 50.1869 69.664 50.6593 69.6755 51.1387C69.687 51.6182 69.6034 52.0951 69.4296 52.5421M64.2289 40.8011L61.5037 47.8601C61.3592 48.2346 61.3693 48.6511 61.5319 49.0181C61.6944 49.3851 61.9961 49.6724 62.3705 49.817L69.4296 52.5421M69.4296 52.5421L62.8893 69.4839C62.6002 70.2328 62.0254 70.8361 61.2915 71.1612C60.5575 71.4863 59.7245 71.5066 58.9756 71.2175L54.246 69.3916M43.8922 55.6612L38.0217 58.2616L40.622 64.1321M46.2693 66.3122L52.1398 63.7119L49.5394 57.8413" stroke="#9747FF" stroke-width="3.02673" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <filter id="filter0_dddd_2_43091" x="0.000460625" y="8.67856" width="112.287" height="134.3" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="1.76101" />
                            <feGaussianBlur stdDeviation="2.20126" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2_43091" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="7.92453" />
                            <feGaussianBlur stdDeviation="3.96226" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.09 0" />
                            <feBlend mode="normal" in2="effect1_dropShadow_2_43091" result="effect2_dropShadow_2_43091" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="18.4906" />
                            <feGaussianBlur stdDeviation="5.72327" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.05 0" />
                            <feBlend mode="normal" in2="effect2_dropShadow_2_43091" result="effect3_dropShadow_2_43091" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="32.5786" />
                            <feGaussianBlur stdDeviation="6.60377" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.01 0" />
                            <feBlend mode="normal" in2="effect3_dropShadow_2_43091" result="effect4_dropShadow_2_43091" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_2_43091" result="shape" />
                        </filter>
                        <clipPath id="clip0_2_43091">
                            <rect width="36.3208" height="36.3208" fill="white" transform="translate(45.5547 30.3474) rotate(21.1089)" />
                        </clipPath>
                    </defs>
                </svg>

            </div>
        </div>

        <div
            class="relative z-[2] mx-auto flex w-full max-w-3xl flex-1 flex-col items-center justify-center py-10 text-center sm:py-14 md:py-16">
            <div
                class="mb-4 inline-flex items-center gap-1.5 rounded-home-md bg-home-surface-light px-2 py-1 text-[10px] text-home-primary sm:mb-5 sm:px-2.5 sm:py-1.5 sm:text-xs">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.051 7.616C11.1171 7.41334 11.2462 7.23709 11.4195 7.11297C11.5928 6.98884 11.8012 6.92335 12.0143 6.92602C12.2275 6.9287 12.4342 6.99942 12.6043 7.12786C12.7744 7.2563 12.899 7.43574 12.96 7.64L13.697 9.092C13.7687 9.23315 13.8729 9.35526 14.0011 9.44827C14.1292 9.54128 14.2776 9.60254 14.434 9.627L16.068 9.883C16.2784 9.88382 16.4832 9.95099 16.6532 10.0749C16.8232 10.1989 16.9498 10.3733 17.015 10.5734C17.0801 10.7735 17.0805 10.989 17.016 11.1893C16.9516 11.3896 16.8256 11.5645 16.656 11.689L15.484 12.857C15.3718 12.9687 15.2877 13.1055 15.2387 13.256C15.1897 13.4066 15.1771 13.5666 15.202 13.723L15.461 15.336C15.5323 15.5382 15.5367 15.7579 15.4736 15.9627C15.4105 16.1676 15.2832 16.3468 15.1106 16.4738C14.9379 16.6009 14.729 16.6691 14.5146 16.6684C14.3003 16.6677 14.0918 16.5982 13.92 16.47L12.455 15.72C12.3139 15.6477 12.1576 15.61 11.999 15.61C11.8404 15.61 11.6841 15.6477 11.543 15.72L10.078 16.47C9.90618 16.5971 9.69816 16.6658 9.48445 16.666C9.27073 16.6663 9.06256 16.598 8.89046 16.4713C8.71835 16.3446 8.59136 16.1661 8.52811 15.962C8.46486 15.7579 8.46868 15.5388 8.539 15.337L8.797 13.724C8.82208 13.5675 8.80959 13.4072 8.76055 13.2565C8.71152 13.1057 8.62736 12.9688 8.515 12.857L7.359 11.705C7.18366 11.5835 7.05175 11.4092 6.98256 11.2074C6.91336 11.0056 6.9105 10.787 6.9744 10.5835C7.03829 10.38 7.16558 10.2022 7.33769 10.0762C7.50979 9.95015 7.71769 9.88246 7.931 9.883L9.564 9.627C9.72043 9.60254 9.86881 9.54128 9.99694 9.44827C10.1251 9.35526 10.2293 9.23315 10.301 9.092L11.051 7.616Z" stroke="#2B8CEE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#2B8CEE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <span class="font-plus font-semibold tracking-wide">
                    <?php echo e(__('cms_review_banner.badge')); ?>
                </span>
            </div>
            <h1
                class="mb-4 w-full font-space text-[34px] font-bold leading-tight text-black sm:text-4xl md:text-[56px] md:leading-[1.1]">
                <?php echo e(__('cms_review_banner.title')); ?>
            </h1>
            <p class="mx-auto max-w-xl font-plus text-sm leading-relaxed text-gray-600 sm:text-base md:text-lg">
                <?php echo e(__('cms_review_banner.subtitle')); ?>
            </p>
        </div>
    </div>

    <script>
        (function() {
            var root = document.getElementById('cms-review-banner');
            if (!root) return;
            var cards = root.querySelectorAll('.cms-review-float-card');
            if (!cards.length) return;
            var t = 0;
            var amp = 11;
            var stepMs = 40;
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

            function tick() {
                if (reduceMotion.matches) return;
                t += 0.042;
                cards.forEach(function(el) {
                    var speed = parseFloat(el.getAttribute('data-speed')) || 1;
                    var phase = parseFloat(el.getAttribute('data-phase')) || 0;
                    var rot = parseFloat(el.getAttribute('data-rot')) || 0;
                    var dy = Math.sin(t * speed + phase) * amp;
                    el.style.transform = 'translateY(' + dy.toFixed(2) + 'px) rotate(' + rot + 'deg)';
                });
                setTimeout(tick, stepMs);
            }
            if (!reduceMotion.matches) setTimeout(tick, stepMs);
        })();
    </script>
</section>