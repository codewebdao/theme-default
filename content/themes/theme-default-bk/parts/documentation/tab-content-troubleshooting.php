<!-- Troubleshooting Tab Content -->
<div class="mb-8 sm:mb-12 sm:px-0">
    <h1 class="font-space text-3xl  md:text-[30px] lg:text-[48px] xl:text-[64px] font-medium sm:font-bold text-home-heading leading-tight sm:leading-snug md:leading-[60px] lg:leading-[80px] text-start mb-12">
        <?php echo e(__('doc.trouble.heading')); ?>
    </h1>

    <!-- Error Sections -->
    <div class="space-y-4 sm:space-y-6">
        <!-- Error 1: Apache fails to start -->
        <div class="rounded-home-lg">
            <div class="flex items-start gap-2 sm:gap-4">
                <div class="flex-1 rounded-[24px] border border-gray-200 overflow-hidden mb-6 shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">
                        <div class="flex items-center gap-4 self-stretch bg-home-surface text-gray-900 p-4 sm:p-6">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                            <circle cx="12" cy="12" r="10" stroke="#EF4444" stroke-width="2" />
                            <path d="M12 8V12M12 16H12.01" stroke="#EF4444" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                        <h3 class="font-plus text-[20px] sm:text-lg md:text-2xl font-semibold"><?php echo e(__('doc.trouble.apache_title')); ?>
                        </h3>
                    </div>
                    <p class="text-sm xl:text-sm text-gray-600 p-4 sm:p-6 leading-[22px] font-normal font-plus">
                        <?php echo e(__('doc.trouble.apache_body')); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Error 2: MySQL 8.0 cannot start -->
        <div class="rounded-home-lg">
            <div class="flex items-start gap-2 sm:gap-4">
                <div class="flex-1 rounded-[24px] border border-gray-200 overflow-hidden mb-6 shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">
                    <div class="flex items-center gap-4 self-stretch bg-home-surface text-gray-900 p-4 sm:p-6">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                            <circle cx="12" cy="12" r="10" stroke="#EF4444" stroke-width="2" />
                            <path d="M12 8V12M12 16H12.01" stroke="#EF4444" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                        <h3 class="font-plus text-[20px] sm:text-lg md:text-2xl font-semibold"><?php echo e(__('doc.trouble.mysql_title')); ?>
                        </h3>
                    </div>
                    <p class="text-sm xl:text-sm text-gray-600 p-4 sm:p-6 leading-[22px] font-normal font-plus">
                        <?php echo e(__('doc.trouble.mysql_body')); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Error 3: Pretty URLs not working -->
        <div class="rounded-home-lg">
            <div class="flex items-start gap-2 sm:gap-4">
                <div class="flex-1 rounded-[24px] border border-gray-200 overflow-hidden mb-6 shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">
                    <div class="flex items-center gap-4 self-stretch bg-home-surface text-gray-900 p-4 sm:p-6">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                            <circle cx="12" cy="12" r="10" stroke="#EF4444" stroke-width="2" />
                            <path d="M12 8V12M12 16H12.01" stroke="#EF4444" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                        <h3 class="font-plus text-[20px] sm:text-lg md:text-2xl font-semibold"><?php echo e(__('doc.trouble.urls_title')); ?>
                        </h3>
                    </div>
                    <p class="text-sm xl:text-sm text-gray-600 p-4 sm:p-6 leading-[22px] font-normal font-plus">
                        <?php echo e(__('doc.trouble.urls_body')); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
