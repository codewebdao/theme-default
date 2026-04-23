<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Download', APP_LANG);
?>
<section class="py-12 sm:py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-medium text-home-heading sm:mb-12 mb-8 font-space">
                <?php echo e(__('download.faq.title')); ?>
            </h2>
         
        </div>

        <div class="max-w-3xl mx-auto space-y-8 sm:space-y-12" x-data="{ activeItem: null }">
            <!-- FAQ 1 -->
            <div class="bg-white border border-home-surface-light rounded-home-lg px-6 py-3    shadow-sm">
                <div class="flex items-start gap-3">
                    <!-- Blue circular information icon -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        
                    <div class="flex-1">
                        <h3 class=" text-base font-medium leading-6 text-black mb-3 font-plus">
                            Is it safe to download?
                        </h3>
                        <p class="text-sm text-gray-500 leading-relaxed font-plus font-normal">
                            Yes. Downloading from the official site ensures it is free from malware. We scan every release.
                        </p>
                    </div>
                </div>
            </div>
            <!-- FAQ 2 -->
            <div class="bg-white border border-home-surface-light rounded-home-lg px-6 py-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <!-- Blue circular information icon -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        
                    <div class="flex-1">
                        <h3 class=" text-base font-medium leading-6 text-black mb-3 font-plus">
                            <?php echo e(__('download.faq.q2')); ?>
                        </h3>
                        <p class="text-sm text-gray-500 leading-relaxed font-plus font-normal">
                            <?php echo e(__('download.faq.a2')); ?>
                        </p>
                    </div>
                </div>
            </div>
            <!-- FAQ 3 -->
            <div class="bg-white border border-home-surface-light rounded-home-lg px-6 py-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <!-- Blue circular information icon -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        
                    <div class="flex-1">
                        <h3 class=" text-base font-medium leading-6 text-black mb-3 font-plus">
                            <?php echo e(__('download.faq.q3')); ?>
                        </h3>
                        <p class="text-sm text-gray-500 leading-relaxed font-plus font-normal">
                            <?php echo e(__('download.faq.a3')); ?>
                        </p>
                    </div>
                </div>
            </div>

       
        </div>
    </div>
</section>

