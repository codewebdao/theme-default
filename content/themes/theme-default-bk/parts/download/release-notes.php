<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Download', APP_LANG);
?>
<section class="py-12 sm:py-24 bg-home-surface-light" id="release-notes">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="max-w-5xl mx-auto">

            <div class="bg-white rounded-t-home-lg border-l-4 border-home-success p-6 sm:p-8 shadow-sm">
                <!-- Header Section -->
                <div class="flex items-center gap-3">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M4 16C4 18.3734 4.70379 20.6935 6.02236 22.6668C7.34094 24.6402 9.21509 26.1783 11.4078 27.0866C13.6005 27.9948 16.0133 28.2324 18.3411 27.7694C20.6689 27.3064 22.8071 26.1635 24.4853 24.4853C26.1635 22.8071 27.3064 20.6689 27.7694 18.3411C28.2324 16.0133 27.9948 13.6005 27.0866 11.4078C26.1783 9.21509 24.6402 7.34094 22.6668 6.02236C20.6935 4.70379 18.3734 4 16 4C12.6453 4.01262 9.42529 5.32163 7.01333 7.65333L4 10.6667M4 10.6667V4M4 10.6667H10.6667M16 9.33333V16L21.3333 18.6667"
                            stroke="var(--home-primary)" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>


                    <h2
                        class="text-center text-[22px] sm:text-[48px] font-medium leading-normal text-home-primary font-space">
                        Release Notes
                    </h2>

                    <span
                        class="flex justify-between items-center font-plus w-[74px] py-1 px-4 rounded-tr-[12px] rounded-bl-[12px] bg-home-success text-white font-semibold text-sm">
                        Latest
                    </span>
                </div>

            </div>

            <!-- Version and Date -->
            <div class="p-6 lg:py-3 bg-[#F3F4F6] lg:px-8 shadow-sm">
                <h3 class="text-2xl font-semibold leading-9 text-home-heading font-space">
                    <?php echo e(__('download.release.version')); ?>
                </h3>
                <p class="text-sm font-normal text-home-body leading-[22px] font-plus">
                    <?php echo e(__('download.release.date')); ?>
                </p>
            </div>


            <!-- Release Details -->
            <ul class="bg-white rounded-b-home-lg border-l-4 border-home-success p-6 lg:p-8 shadow-sm">
                <li class="flex items-start gap-3 items-center mb-6">
                    <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="3" cy="3" r="3" fill="var(--home-success)" />
                    </svg>

                    <span class="text-base font-normal leading-6 text-home-body font-plus"><?php echo e(__('download.release.change_1')); ?></span>
                </li>
                <li class="flex items-start gap-3 items-center mb-6">
                    <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="3" cy="3" r="3" fill="var(--home-success)" />
                    </svg>

                    <span class="text-base font-normal leading-6 text-home-body font-plus"><?php echo e(__('download.release.change_2')); ?></span>
                </li>
                <li class="flex items-start gap-3 items-center mb-6">
                    <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="3" cy="3" r="3" fill="var(--home-success)" />
                    </svg>
                    <span class="text-base font-normal leading-6 text-home-body font-plus">Improved MySQL 8.0 startup
                        speed</span>
                </li>
                <li class="flex items-start gap-3 items-center">
                    <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="3" cy="3" r="3" fill="var(--home-success)" />
                    </svg>
                    <span class="text-base font-normal leading-6 text-home-body font-plus"><?php echo e(__('download.release.change_4')); ?></span>
                </li>
            </ul>
        </div>

    </div>
</section>