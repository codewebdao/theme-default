<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Download', APP_LANG);

/**
 * URL tải từ option CMS: link_download_laragoninstall, link_download_portabl.
 * Ghi đè từ page-download.php: $download_edition_full_url, $download_edition_portable_url, $download_edition_services_url
 */
$lang = defined('APP_LANG') ? APP_LANG : 'all';
$download_edition_option_url = static function (string $key) use ($lang): string {
    if (!function_exists('option')) {
        return '';
    }
    $v = trim((string) option($key, $lang));
    if ($v !== '') {
        return $v;
    }

    return trim((string) option($key, 'all'));
};

if (!isset($download_edition_full_url)) {
    $download_edition_full_url = $download_edition_option_url('link_download_laragoninstall');
}
if (!isset($download_edition_portable_url)) {
    $download_edition_portable_url = $download_edition_option_url('link_download_portabl');
    if ($download_edition_portable_url === '') {
        $download_edition_portable_url = $download_edition_full_url;
    }
}
if (!isset($download_edition_services_url)) {
    $download_edition_services_url = base_url('contact', defined('APP_LANG') ? APP_LANG : '');
}

$download_edition_download_basename = static function (string $url): string {
    if ($url === '') {
        return '';
    }
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || $path === '' || substr($path, -1) === '/') {
        return '';
    }
    $b = basename($path);

    return ($b !== '' && $b !== '.' && $b !== '..') ? $b : '';
};
$download_edition_full_dl_name = $download_edition_download_basename((string) $download_edition_full_url);
$download_edition_portable_dl_name = $download_edition_download_basename((string) $download_edition_portable_url);
?>
<section class="py-12 sm:py-24 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12">
            <h2
                class="text-home-heading text-center font-space font-medium text-[28px] leading-[36px] sm:text-[36px] sm:leading-[44px] lg:text-[48px] lg:leading-normal">
                <?php echo e(__('download.editions.heading')); ?>
            </h2>
        </div>

        <!-- Mobile: Horizontal Scroll — center = Laragon-Install.exe, 0.1 card peek left/right -->
        <div id="mobile-cards-scroll" class="md:hidden overflow-x-auto pb-4 -mx-4 px-4 scrollbar-hide"
            style="scroll-snap-type: x mandatory; scroll-padding-left: 28px; scroll-padding-right: 28px;">
            <div id="mobile-cards-track" class="flex gap-6" style="scroll-snap-type: x mandatory;">
                <!--Laragon Portable Card (Left) -->
                <div class="flex-shrink-0 w-[280px] sm:w-[322px] snap-center">
                    <div class="w-full h-full flex flex-col px-4 lg:px-6 py-10 bg-white relative rounded-home-lg border-[1.5px] border-home-border
                            shadow-[0_77.333px_21.333px_0_rgba(43,140,238,0.00),0_49.333px_20px_0_rgba(43,140,238,0.01),0_28px_17.333px_0_rgba(43,140,238,0.05),0_12px_12px_0_rgba(43,140,238,0.09),0_2.667px_6.667px_0_rgba(43,140,238,0.10)]">

                        <h3 class="  text-home-heading text-start text-[22px] lg:text-[24px] font-medium leading-[32px] lg:leading-[36px] mb-2 font-plus">
                            <?php echo e(__('download.portable.title')); ?>
                        </h3>
                        <p class="text-sm text-home-body mb-6 text-start mb-8 flex-grow font-plus">
                            <?php echo e(__('download.portable.desc')); ?>
                        </p>

                        <a href="<?php echo e($download_edition_portable_url); ?>"<?php if ($download_edition_portable_dl_name !== ''): ?> download="<?php echo e($download_edition_portable_dl_name); ?>"<?php endif; ?> rel="noopener noreferrer"
                            class="w-full flex items-center justify-center gap-2 text-sm lg:text-[15px] bg-home-surface hover:bg-white hover:border-home-surface border border-home-surface hover:bg-home-surface text-black font-semibold py-3 px-4 rounded-home-md transition-colors mt-auto mb-8 min-h-[52px] flex-shrink-0 no-underline">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                <path
                                    d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15"
                                    stroke="var(--home-heading)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="whitespace-nowrap"><?php echo e(__('download.portable.btn')); ?></span>
                        </a>

                        <!-- PACKAGES INCLUDED Section -->
                        <div class="w-full">
                            <h4 class=" text-home-body text-[14px]font-medium leading-[21px] uppercase mb-3">
                                <?php echo e(__('download.portable.includes')); ?>
                            </h4>
                            <ul class="space-y-2 text-sm text-home-body">
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px]">
                                        <?php echo e(__('download.portable.li_1')); ?>
                                    </span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px]"><?php echo e(__('download.portable.li_2')); ?></span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span
                                        class="text-center text-home-body text-[14px] font-normal leading-[22px]"><?php echo e(__('download.portable.li_3')); ?></span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px]"><?php echo e(__('download.portable.li_4')); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Laragon Full Card (Center) -->
                <div class="flex-shrink-0 w-[322px] snap-center" id="mobile-card-center">
                    <div class="w-full h-full flex flex-col px-4 lg:px-6 py-10 bg-white relative rounded-home-lg border-[1.5px] border-home-primary
                        shadow-[0_77.333px_21.333px_0_rgba(43,140,238,0.00),0_49.333px_20px_0_rgba(43,140,238,0.01),0_28px_17.333px_0_rgba(43,140,238,0.05),0_12px_12px_0_rgba(43,140,238,0.09),0_2.667px_6.667px_0_rgba(43,140,238,0.10)]">
                        <div
                            class="flex justify-center items-center gap-1.5 py-1.5 px-4 rounded-tr-xl rounded-bl-xl bg-home-primary  absolute right-0 top-0">
                            <span class="text-xs font-semibold text-white">
                                <?php echo e(__('download.badge.recommended')); ?>
                            </span>
                        </div>
                        <h3 class="  text-home-heading text-start text-[22px] lg:text-[24px] font-medium leading-[32px] lg:leading-[36px] mb-2 font-plus">
                            <?php echo e(__('download.full.title')); ?>
                        </h3>
                        <p class="text-sm text-home-body mb-6 text-start mb-8 flex-grow font-plus">
                            <?php echo e(__('download.full.desc')); ?>
                        </p>

                        <a href="<?php echo e($download_edition_full_url); ?>"<?php if ($download_edition_full_dl_name !== ''): ?> download="<?php echo e($download_edition_full_dl_name); ?>"<?php endif; ?> rel="noopener noreferrer"
                            class="w-full flex items-center justify-center gap-2 text-sm lg:text-[15px] bg-home-primary hover:bg-home-primary-hover text-white font-semibold py-3 px-4 rounded-home-md transition-colors mt-auto mb-8 min-h-[52px] flex-shrink-0 no-underline">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                <path
                                    d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="whitespace-nowrap"><?php echo e(__('download.full.btn')); ?></span>
                        </a>

                        <!-- PACKAGES INCLUDED Section -->
                        <div class="w-full">
                            <h4 class=" text-home-body text-[14px]font-medium leading-[21px] uppercase mb-3">
                                <?php echo e(__('download.full.packages_heading')); ?>
                            </h4>
                            <ul class="space-y-2 text-sm text-home-body">
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px]">
                                        <?php echo e(__('download.full.li_1')); ?>
                                    </span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px]"><?php echo e(__('download.full.li_2')); ?></span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px]"><?php echo e(__('download.full.li_3')); ?></span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span
                                        class="text-center text-home-body text-[14px] font-normal leading-[22px]"><?php echo e(__('download.full.li_4')); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Laragon Services Card -->
                <div class="flex-shrink-0 w-[280px] sm:w-[322px] snap-center">
                    <div class="w-full h-full flex flex-col px-4 lg:px-6 py-10 bg-white relative rounded-home-lg border-[1.5px] border-home-success
                            shadow-[0_77.333px_21.333px_0_rgba(43,140,238,0.00),0_49.333px_20px_0_rgba(43,140,238,0.01),0_28px_17.333px_0_rgba(43,140,238,0.05),0_12px_12px_0_rgba(43,140,238,0.09),0_2.667px_6.667px_0_rgba(43,140,238,0.10)]">
                        <div
                            class="flex justify-center items-center gap-1.5 py-1.5 px-4 rounded-tr-xl rounded-bl-xl bg-home-success absolute right-0 top-0">
                            <span class="text-xs font-semibold text-white">
                                <?php echo e(__('download.badge.business')); ?>
                            </span>
                        </div>
                        <h3 class="  text-home-heading text-start text-[22px] lg:text-[24px] font-medium leading-[32px] lg:leading-[36px] mb-2 font-plus">
                            <?php echo e(__('download.services.title')); ?>
                        </h3>
                        <p class="text-sm text-home-body mb-6 text-start mb-8 flex-grow font-plus">
                            <?php echo e(__('download.services.desc')); ?>
                        </p>

                        <a href="<?php echo e($download_edition_services_url); ?>"
                            class="w-full flex items-center justify-center gap-2 text-sm lg:text-[15px] bg-home-success hover:bg-white hover:border-home-success border border-home-success text-black font-semibold py-3 px-4 rounded-home-md transition-colors mt-auto mb-8 min-h-[52px] flex-shrink-0 no-underline">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                <path
                                    d="M9.99969 8.99994L6.99969 11.9999L9.99969 14.9999M13.9997 14.9999L16.9997 11.9999L13.9997 8.99994M2.99169 16.3419C3.13873 16.7129 3.17147 17.1193 3.08569 17.5089L2.02069 20.7989C1.98638 20.9658 1.99525 21.1386 2.04647 21.3011C2.09769 21.4635 2.18955 21.6102 2.31336 21.7272C2.43716 21.8442 2.5888 21.9276 2.75389 21.9696C2.91898 22.0115 3.09205 22.0106 3.25669 21.9669L6.66969 20.9689C7.03741 20.896 7.41822 20.9279 7.76869 21.0609C9.90408 22.0582 12.3231 22.2691 14.5988 21.6567C16.8746 21.0442 18.861 19.6476 20.2074 17.7133C21.5538 15.779 22.1738 13.4313 21.958 11.0845C21.7422 8.73763 20.7044 6.54241 19.0278 4.88613C17.3511 3.22986 15.1434 2.21898 12.7941 2.03183C10.4448 1.84469 8.10483 2.49332 6.18713 3.86328C4.26944 5.23323 2.89722 7.23648 2.31258 9.51958C1.72795 11.8027 1.96846 14.2189 2.99169 16.3419Z"
                                    stroke="var(--home-heading)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="whitespace-nowrap"><?php echo e(__('download.services.btn')); ?></span>
                        </a>

                        <!-- PACKAGES INCLUDED Section -->
                        <div class="w-full">
                            <h4 class=" text-home-body text-[14px]font-medium leading-[21px] uppercase mb-3">
                                <?php echo e(__('download.services.includes')); ?>
                            </h4>
                            <ul class="space-y-2 text-sm text-home-body">
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus">
                                        <?php echo e(__('download.services.li_1')); ?>
                                    </span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span
                                        class="text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.services.li_2')); ?></span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span
                                        class="text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.services.li_3')); ?></span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.services.li_4')); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Laragon Services Card (Right) -->
            </div>
        </div>

        <script>
            (function () {
                var peek = 28;
                var AUTO_MS = 3000;
                var RESUME_AFTER_MS = 12000;

                function scrollToCard(scrollContainer, card) {
                    if (!scrollContainer || !card) return;
                    scrollContainer.scrollTo({ left: card.offsetLeft - peek, behavior: 'smooth' });
                }

                function scrollToCenterCard() {
                    var scrollContainer = document.getElementById('mobile-cards-scroll');
                    if (!scrollContainer || window.innerWidth >= 768) return;
                    var centerCard = document.getElementById('mobile-card-center');
                    if (centerCard) scrollToCard(scrollContainer, centerCard);
                }

                document.addEventListener('DOMContentLoaded', function () {
                    scrollToCenterCard();

                    var scrollContainer = document.getElementById('mobile-cards-scroll');
                    var track = document.getElementById('mobile-cards-track');
                    if (!scrollContainer || !track) return;

                    var cards = Array.prototype.slice.call(track.children);
                    if (cards.length < 2) return;

                    var autoIndex = 1;
                    var userHold = false;
                    var resumeTimer = null;

                    function pauseAuto() {
                        userHold = true;
                        clearTimeout(resumeTimer);
                        resumeTimer = setTimeout(function () {
                            userHold = false;
                        }, RESUME_AFTER_MS);
                    }

                    scrollContainer.addEventListener('touchstart', pauseAuto, { passive: true });
                    scrollContainer.addEventListener('wheel', pauseAuto, { passive: true });

                    setInterval(function () {
                        if (window.innerWidth >= 768 || userHold) return;
                        autoIndex = (autoIndex + 1) % cards.length;
                        scrollToCard(scrollContainer, cards[autoIndex]);
                    }, AUTO_MS);
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth < 768) scrollToCenterCard();
                });
            })();
        </script>

        <!-- Desktop: Grid Layout -->
        <div class="download-edition-desktop-grid hidden md:grid md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-8 md:gap-4 lg:gap-8 xl:gap-10 mx-auto w-full max-w-[1600px] items-stretch">
            <!-- Laragon Full Card -->
            <div class="flex justify-center">
                <div class="w-full h-full flex flex-col px-2  lg:px-6 py-10 bg-white relative rounded-home-lg border-[1.5px] border-home-primary
                    shadow-[0_77.333px_21.333px_0_rgba(43,140,238,0.00),0_49.333px_20px_0_rgba(43,140,238,0.01),0_28px_17.333px_0_rgba(43,140,238,0.05),0_12px_12px_0_rgba(43,140,238,0.09),0_2.667px_6.667px_0_rgba(43,140,238,0.10)]">
                    <div
                        class="flex justify-center items-center gap-1.5 py-1.5 px-4 rounded-tr-xl rounded-bl-xl bg-home-primary  absolute right-0 top-0">
                        <span class="text-xs font-semibold text-white font-plus">
                            <?php echo e(__('download.badge.recommended')); ?>
                        </span>
                    </div>
                    <h3 class="  text-home-heading text-start text-[22px] lg:text-[24px] font-medium leading-[32px] lg:leading-[36px] mb-2 font-plus">
                        <?php echo e(__('download.full.title')); ?>
                    </h3>
                    <p class="text-sm text-home-body mb-6 text-start mb-8 flex-grow font-plus">
                        <?php echo e(__('download.full.desc')); ?>
                    </p>

                    <a href="<?php echo e($download_edition_full_url); ?>"<?php if ($download_edition_full_dl_name !== ''): ?> download="<?php echo e($download_edition_full_dl_name); ?>"<?php endif; ?> rel="noopener noreferrer"
                        class="w-full flex items-center justify-center gap-2 lg:gap-2 text-sm lg:text-[15px] bg-home-primary hover:bg-home-primary-hover text-white font-semibold py-2.5 px-3.5 lg:py-3 lg:px-4 rounded-home-md transition-colors mt-auto mb-6 lg:mb-8 min-h-[48px] lg:min-h-[52px] flex-shrink-0 no-underline">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="flex-shrink-0 w-[22px] h-[22px] lg:w-6 lg:h-6">
                            <path
                                d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15"
                                stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="whitespace-nowrap font-plus"><?php echo e(__('download.full.btn')); ?></span>
                    </a>

                    <!-- PACKAGES INCLUDED Section -->
                    <div class="w-full flex-grow">
                        <h4 class=" text-home-body text-[14px]font-medium leading-[21px] uppercase mb-3 font-plus">
                            <?php echo e(__('download.full.packages_heading')); ?>
                        </h4>
                        <ul class="space-y-2 text-sm text-home-body">
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus">
                                    <?php echo e(__('download.full.li_1')); ?>
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.full.li_2')); ?></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.full.li_3')); ?></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-primary)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span
                                    class="text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.full.li_4')); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Laragon Services Card -->
            <div class="flex justify-center">
                <div class="w-full h-full flex flex-col px-2 lg:px-6 py-10 bg-white relative rounded-home-lg border-[1.5px] border-home-success
                        shadow-[0_77.333px_21.333px_0_rgba(43,140,238,0.00),0_49.333px_20px_0_rgba(43,140,238,0.01),0_28px_17.333px_0_rgba(43,140,238,0.05),0_12px_12px_0_rgba(43,140,238,0.09),0_2.667px_6.667px_0_rgba(43,140,238,0.10)]">
                    <div
                        class="flex justify-center items-center gap-1.5 py-1.5 px-4 rounded-tr-xl rounded-bl-xl bg-home-success absolute right-0 top-0">
                        <span class="text-xs font-semibold text-white font-plus">
                            <?php echo e(__('download.badge.business')); ?>
                        </span>
                    </div>
                    <h3 class="  text-home-heading text-start text-[22px] lg:text-[24px] font-medium leading-[32px] lg:leading-[36px] mb-2 font-plus">
                        <?php echo e(__('download.services.title')); ?>
                    </h3>
                        <p class="text-sm text-home-body mb-6 text-start mb-8 flex-grow font-plus">
                        <?php echo e(__('download.services.desc')); ?>
                    </p>

                    <a href="<?php echo e($download_edition_services_url); ?>"
                        class="w-full flex items-center justify-center gap-2 lg:gap-2 text-sm lg:text-[15px] bg-home-success hover:bg-white hover:border-home-success border border-home-success text-black font-semibold py-2.5 px-3.5 lg:py-3 lg:px-4 rounded-home-md transition-colors mt-auto mb-6 lg:mb-8 min-h-[48px] lg:min-h-[52px] flex-shrink-0 no-underline">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="flex-shrink-0 w-[22px] h-[22px] lg:w-6 lg:h-6">
                            <path
                                d="M9.99969 8.99994L6.99969 11.9999L9.99969 14.9999M13.9997 14.9999L16.9997 11.9999L13.9997 8.99994M2.99169 16.3419C3.13873 16.7129 3.17147 17.1193 3.08569 17.5089L2.02069 20.7989C1.98638 20.9658 1.99525 21.1386 2.04647 21.3011C2.09769 21.4635 2.18955 21.6102 2.31336 21.7272C2.43716 21.8442 2.5888 21.9276 2.75389 21.9696C2.91898 22.0115 3.09205 22.0106 3.25669 21.9669L6.66969 20.9689C7.03741 20.896 7.41822 20.9279 7.76869 21.0609C9.90408 22.0582 12.3231 22.2691 14.5988 21.6567C16.8746 21.0442 18.861 19.6476 20.2074 17.7133C21.5538 15.779 22.1738 13.4313 21.958 11.0845C21.7422 8.73763 20.7044 6.54241 19.0278 4.88613C17.3511 3.22986 15.1434 2.21898 12.7941 2.03183C10.4448 1.84469 8.10483 2.49332 6.18713 3.86328C4.26944 5.23323 2.89722 7.23648 2.31258 9.51958C1.72795 11.8027 1.96846 14.2189 2.99169 16.3419Z"
                                stroke="var(--home-heading)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="whitespace-nowrap font-plus text-home-heading font-plus"><?php echo e(__('download.services.btn')); ?></span>
                    </a>

                    <!-- PACKAGES INCLUDED Section -->
                    <div class="w-full flex-grow">
                        <h4 class=" text-home-body text-[14px]font-medium leading-[21px] uppercase mb-3 font-plus">
                            <?php echo e(__('download.services.includes')); ?>
                        </h4>
                        <ul class="space-y-2 text-sm text-home-body">
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus">
                                    <?php echo e(__('download.services.li_1')); ?>
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span
                                    class="text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.services.li_2')); ?></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.services.li_3')); ?></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-success)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.services.li_4')); ?></span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <!--Laragon Portable Card -->
            <div class="flex justify-center">
                <div class="w-full h-full flex flex-col px-2 lg:px-6 py-10 bg-white relative rounded-home-lg border-[1.5px] border-home-border
                        shadow-[0_77.333px_21.333px_0_rgba(43,140,238,0.00),0_49.333px_20px_0_rgba(43,140,238,0.01),0_28px_17.333px_0_rgba(43,140,238,0.05),0_12px_12px_0_rgba(43,140,238,0.09),0_2.667px_6.667px_0_rgba(43,140,238,0.10)]">

                    <h3 class="  text-home-heading text-start text-[22px] lg:text-[24px] font-medium leading-[32px] lg:leading-[36px] mb-2 font-plus">
                        <?php echo e(__('download.portable.title')); ?>
                    </h3>
                    <p class="text-sm text-home-body mb-6 text-start mb-8 flex-grow font-plus">
                        <?php echo e(__('download.portable.desc')); ?>
                    </p>

                    <a href="<?php echo e($download_edition_portable_url); ?>"<?php if ($download_edition_portable_dl_name !== ''): ?> download="<?php echo e($download_edition_portable_dl_name); ?>"<?php endif; ?> rel="noopener noreferrer"
                        class="w-full flex items-center justify-center gap-2 lg:gap-2 text-sm lg:text-[15px] bg-home-surface hover:bg-white hover:border-home-surface border border-home-surface hover:bg-home-surface text-black font-semibold py-2.5 px-3.5 lg:py-3 lg:px-4 rounded-home-md transition-colors mt-auto mb-6 lg:mb-8 min-h-[48px] lg:min-h-[52px] flex-shrink-0 no-underline">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="flex-shrink-0 w-[22px] h-[22px] lg:w-6 lg:h-6">
                            <path
                                d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15"
                                stroke="var(--home-heading)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="whitespace-nowrap font-plus"><?php echo e(__('download.portable.btn')); ?></span>
                    </a>

                    <!-- PACKAGES INCLUDED Section -->
                    <div class="w-full flex-grow">
                        <h4 class=" text-home-body text-[14px]font-medium leading-[21px] uppercase mb-3 font-plus">
                        <?php echo e(__('download.portable.includes_desktop')); ?>
                        </h4>
                        <ul class="space-y-2 text-sm text-home-body">
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus">
                                    <?php echo e(__('download.portable.li_1')); ?>
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-start text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.portable.li_2')); ?></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.portable.li_3')); ?></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--home-body)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <span class="text-home-body text-[14px] font-normal leading-[22px] font-plus"><?php echo e(__('download.portable.li_4')); ?></span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
