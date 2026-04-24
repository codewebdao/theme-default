<section class="relative overflow-hidden bg-white py-[120px]">
    <!-- Background: ảnh LCP phía trên lớp blur; blur nhỏ hơn để giảm GPU (blur 200px+ làm tăng element render delay) -->
    <div class="absolute inset-0 overflow-hidden isolate">
        <div class="absolute inset-0 z-0 pointer-events-none" aria-hidden="true">
            <div class="absolute w-full h-[550px] rounded-full bg-[#2377FD80]/10 blur-[100px] sm:blur-[120px] -translate-x-1/2"
                style="left: 100%; top: -0;"></div>
            <div class="absolute w-full h-[550px] rounded-full bg-[#63ECFF80]/15 blur-[80px] sm:blur-[100px] -translate-x-1/2"
                style="left: 0; top: 100px;"></div>
            <div class="absolute w-[1366px] h-[1366px] rounded-full bg-[#63ECFF33]/0.9 blur-[48px] sm:blur-[64px] -translate-x-1/2"
                style="left: 50%; top: 0px;"></div>
        </div>
        <?php
        $__bannerFeatures = function_exists('cmsfullform_theme_responsive_webp_img')
            ? cmsfullform_theme_responsive_webp_img('images/bannerFeatures.webp', [400, 560, 720, 900], [
                'alt'               => (string) __('features_laragon.banner_image_alt'),
                'class'             => 'relative z-[1] w-full h-full sm:h-auto object-cover',
                'sizes'             => '100vw',
                'loading'           => 'eager',
                'fetchpriority'     => 'high',
                'decoding'          => 'sync',
                'mobile_webp_width' => 400,
                'mobile_webp_bp'    => 640,
            ])
            : '';
        echo $__bannerFeatures !== '' ? $__bannerFeatures : '<img src="' . e(function_exists('theme_assets') ? theme_assets('images/bannerFeatures.webp') : '') . '" alt="' . e(__('features_laragon.banner_image_alt')) . '" class="relative z-[1] w-full h-full sm:h-auto object-cover" fetchpriority="high" loading="eager" decoding="sync" />';
        ?>
    </div>

    <!-- Content -->
    <div class="relative container mx-auto px-4 sm:px-6 py-0 sm:py-12">
        <div class="text-center mb-6 sm:mb-8">
            <!-- Badge -->
            <div class="sr sr--fade-up mt-12 inline-flex items-center gap-2 bg-home-surface-light text-home-primary 
                text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-home-md mb-6" style="--sr-delay: 0ms">
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none">
                    <circle cx="8" cy="8" r="7" fill="var(--home-primary)" fill-opacity="0.1" />
                    <circle cx="8" cy="8" r="3" fill="var(--home-primary)" />
                </svg>
                <span class="font-semibold font-plus"><?php echo e(__('features_laragon.banner_badge')); ?></span>
            </div>

            <!-- Title -->
            <h1 class="sr sr--fade-up text-[40px] sm:text-4xl md:text-[64px] font-bold text-black mb-3 sm:mb-4 
                leading-snug md:leading-[80px] font-space" style="--sr-delay: 70ms">
                <?php echo e(__('features_laragon.banner_title_line1')); ?>
                <br />
                <span class="bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent font-space">
                    <?php echo e(__('features_laragon.banner_title_highlight')); ?>
                </span>
            </h1>
        </div>
    </div>

    <!-- Button: cuộn xuống nội dung chính (#features-main), animation nhẹ trên icon -->
    <div class="sr sr--fade-up mt-6 relative z-10 pb-8" style="--sr-delay: 140ms">
        <button type="button"
            class="features-banner-cta group flex items-center justify-center gap-2 bg-home-primary hover:bg-home-primary-hover 
            text-white px-6 sm:px-7 py-3.5 sm:py-4 rounded-home-lg font-medium transition-all duration-300 
            mx-auto shadow-lg hover:shadow-xl hover:shadow-home-primary/50 hover:scale-[1.02] active:scale-[0.98] text-sm sm:text-base"
            data-features-scroll="#features-main"
            aria-controls="features-main"
            aria-label="<?php echo e(__('features_laragon.banner_cta')); ?>">

            <span class="font-plus"><?php echo e(__('features_laragon.banner_cta')); ?></span>

            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                class="features-banner-cta__icon sm:w-[24px] sm:h-[24px] transition-transform duration-300 group-hover:translate-y-1">
                <path d="M19 12L12 19M12 19L5 12M12 19L12 5" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>
</section>