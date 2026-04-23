<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('CMS', APP_LANG);
?>
<section class="relative py-16 sm:py-20 lg:py-24 overflow-hidden">

    <div class="absolute inset-0 " style="background-image: url(<?php echo e(theme_assets('images/frame1.webp')); ?>); background-size: cover; background-position: center;">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-0 left-0 w-full h-full">
                <svg width="100%" height="100%" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg"
                    preserveAspectRatio="none">
                    <path d="M0,300 Q300,100 600,300 T1200,300" stroke="rgba(255,255,255,0.3)" stroke-width="2"
                        fill="none" />
                    <path d="M0,200 Q400,400 800,200 T1200,200" stroke="rgba(135,206,250,0.4)" stroke-width="2"
                        fill="none" />
                    <path d="M0,400 Q200,100 500,400 T1200,400" stroke="rgba(255,255,255,0.2)" stroke-width="2"
                        fill="none" />
                </svg>
            </div>
        </div>
        <div class="absolute top-20 right-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-cyan-300/10 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-[1200px] mx-auto px-4 sm:px-6 text-center z-10">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 sm:mb-6">
            <?php echo e(__('cms_cta_banner.title')); ?>
        </h2>
        <p class="text-base sm:text-lg lg:text-xl text-white/90 mb-8 sm:mb-10 max-w-2xl mx-auto">
            <?php echo e(__('cms_cta_banner.body')); ?>
        </p>
        <div class="flex flex-col items-center gap-4 sm:gap-6">
            <a href="<?php echo e(base_url('download')); ?>"
                class="inline-flex items-center gap-3 bg-home-primary hover:bg-home-primary-hover text-white font-semibold px-8 py-4 rounded-home-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12.8572 16.0715V3.21436M12.8572 16.0715L7.50007 10.7144M12.8572 16.0715L18.2144 10.7144M22.5001 16.0715V20.3572C22.5001 20.9255 22.2743 21.4706 21.8724 21.8724C21.4706 22.2743 20.9255 22.5001 20.3572 22.5001H5.35721C4.78889 22.5001 4.24385 22.2743 3.84198 21.8724C3.44012 21.4706 3.21436 20.9255 3.21436 20.3572V16.0715"
                        stroke="white" stroke-width="2.14286" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-lg"><?php echo e(__('cms_cta_banner.download')); ?></span>
            </a>
            <div class="flex items-center justify-center gap-2 text-white/80 text-xs sm:text-base">
                <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_47_621)">
                        <path
                            d="M13 3H2.33333C1.59695 3 1 3.59695 1 4.33333V5.66667C1 6.40305 1.59695 7 2.33333 7H13C13.7364 7 14.3333 6.40305 14.3333 5.66667V4.33333C14.3333 3.59695 13.7364 3 13 3Z"
                            stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M13 9.66667H2.33333C1.59695 9.66667 1 10.2636 1 11V12.3333C1 13.0697 1.59695 13.6667 2.33333 13.6667H13C13.7364 13.6667 14.3333 13.0697 14.3333 12.3333V11C14.3333 10.2636 13.7364 9.66667 13 9.66667Z"
                            stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <clipPath id="clip0_47_621">
                            <rect width="16" height="16.6667" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <span class="font-plus font-xs"><?php echo e(__('cms_cta_banner.requirements')); ?></span>
            </div>
        </div>
    </div>
</section>