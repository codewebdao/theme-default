<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Download', APP_LANG);
?>
<section class="
        relative overflow-hidden
        flex flex-col items-center justify-center gap-8 sm:gap-10 lg:gap-12
        py-16 sm:py-20 md:py-24 lg:py-[105px]
        bg-no-repeat bg-center
        [background-blend-mode:hard-light]
    " style="background: url(<?php echo theme_assets('images/banner-download.webp'); ?>) lightgray 50% 50% / cover no-repeat;">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-gradient-to-b from-[#E8F4FD]/60 to-white flex flex-col items-center gap-8 w-full">
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 text-center max-w-4xl">
        <div
            class="inline-flex gap-1.5 sm:gap-2 px-2 sm:px-3 items-center py-1.5 sm:py-2 mb-4 sm:mb-6 rounded-home-md bg-home-surface-light text-home-primary hover:underline justify-center whitespace-nowrap">
            <svg width="20" height="20" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"
                class="flex-shrink-0 sm:w-[27px] sm:h-[27px]" style="display: block;">
                <g clip-path="url(#clip0_964_8644)" filter="url(#filter0_dddd_964_8644)">
                    <path
                        d="M21.3329 8.33357C21.7134 10.2013 21.4422 12.1431 20.5644 13.8351C19.6866 15.527 18.2553 16.8669 16.5091 17.6313C14.763 18.3957 12.8076 18.5384 10.9689 18.0355C9.13034 17.5327 7.51969 16.4147 6.4056 14.8681C5.2915 13.3214 4.7413 11.4396 4.84675 9.53639C4.9522 7.63318 5.70693 5.82364 6.98508 4.40954C8.26322 2.99545 9.98752 2.06226 11.8704 1.76561C13.7533 1.46897 15.681 1.82679 17.332 2.7794M10.6654 9.1669L13.1654 11.6669L21.4987 3.33357"
                        stroke="var(--home-primary)" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                </g>
                <defs>
                    <filter id="filter0_dddd_964_8644" x="-0.833984" y="0" width="28" height="33"
                        filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                            result="hardAlpha" />
                        <feOffset dy="1" />
                        <feGaussianBlur stdDeviation="0.5" />
                        <feColorMatrix type="matrix"
                            values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.2 0" />
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_964_8644" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                            result="hardAlpha" />
                        <feOffset dy="2" />
                        <feGaussianBlur stdDeviation="1" />
                        <feColorMatrix type="matrix"
                            values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.17 0" />
                        <feBlend mode="normal" in2="effect1_dropShadow_964_8644" result="effect2_dropShadow_964_8644" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                            result="hardAlpha" />
                        <feOffset dy="5" />
                        <feGaussianBlur stdDeviation="1.5" />
                        <feColorMatrix type="matrix"
                            values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.1 0" />
                        <feBlend mode="normal" in2="effect2_dropShadow_964_8644" result="effect3_dropShadow_964_8644" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                            result="hardAlpha" />
                        <feOffset dy="9" />
                        <feGaussianBlur stdDeviation="2" />
                        <feColorMatrix type="matrix"
                            values="0 0 0 0 0.168627 0 0 0 0 0.54902 0 0 0 0 0.933333 0 0 0 0.03 0" />
                        <feBlend mode="normal" in2="effect3_dropShadow_964_8644" result="effect4_dropShadow_964_8644" />
                        <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow_964_8644" result="shape" />
                    </filter>
                    <clipPath id="clip0_964_8644">
                        <rect width="20" height="20" fill="white" transform="translate(3.16602)" />
                    </clipPath>
                </defs>
            </svg>
            <a href="#release-notes" class="text-[10px] sm:text-[12px] leading-tight font-semibold uppercase">
                <?php echo e(__('download.banner.badge')); ?>
            </a>
        </div>
        <h1
            class="text-black text-center font-space text-[32px] leading-[40px] sm:text-[40px] sm:leading-[48px] md:text-[52px] md:leading-[60px] lg:text-[64px] lg:leading-[80px] font-bold mb-4 sm:mb-6 lg:mb-8">
            <?php echo e(__('download.banner.title')); ?>
        </h1>
        <p
            class="text-home-body text-center font-plus font-normal  text-[16px] leading-[24px] sm:text-[18px] sm:leading-[28px] lg:text-[20px] lg:leading-[30px]">
            <?php echo e(__('download.banner.subtitle_1')); ?>
        </p>
        <p class="text-home-body text-center font-plus font-normal  text-[16px] leading-[24px] sm:text-[18px] sm:leading-[28px] lg:text-[20px] lg:leading-[30px]">
            <?php echo e(__('download.banner.subtitle_2')); ?>
        </p>
    </div>
</section>