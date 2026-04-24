<!-- Installation Tab Content -->
<div class="mb-12 sm:px-0">
    <h1 class="font-space text-3xl  md:text-[30px] lg:text-[44px] xl:text-[64px] font-medium sm:font-bold text-black text-start mb-12">
        <?php echo e(__('doc.install.heading')); ?>
    </h1>

    <!-- Step-by-Step Guide -->
    <div class="relative mb-16">
        <!-- Vertical Line -->
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-home-accent to-home-primary">
        </div>

        <!-- Step 1 -->
        <div class="relative flex gap-6 mb-8">
            <div class="flex-shrink-0 relative z-10">
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-home-accent to-home-primary flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    1
                </div>
            </div>
            <div
                class="flex-1 rounded-[24px] border border-gray-200 overflow-hidden shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">
                <h3 class="font-plus text-xl font-semibold bg-home-surface text-gray-900 p-6"><?php echo e(__('doc.install.step1_title')); ?>
                </h3>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed p-4 sm:p-4 font-plus">
                    <?php echo e(__('doc.install.step1_body')); ?>
                </p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="relative flex gap-6 mb-8">
            <div class="flex-shrink-0 relative z-10">
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-home-accent to-home-primary flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    2
                </div>
            </div>
            <div
                class="flex-1 rounded-[24px] border border-gray-200 overflow-hidden shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">
                <h3 class="font-plus text-xl font-semibold bg-home-surface text-gray-900 p-6"><?php echo e(__('doc.install.step2_title')); ?>
                </h3>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed p-4 sm:p-4 font-plus">
                    <?php echo e(__('doc.install.step2_body')); ?>
                </p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="relative flex gap-6">
            <div class="flex-shrink-0">
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-home-accent to-home-primary flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    3
                </div>
            </div>
            <div
                class="flex-1 rounded-[24px] border border-gray-200 overflow-hidden shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">
                <h3 class="font-plus text-xl font-semibold bg-home-surface text-gray-900 p-6"><?php echo e(__('doc.install.step3_title')); ?>
                </h3>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed p-4 sm:p-4 font-plus">
                    <?php echo e(__('doc.install.step3_body')); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Important Installation Notes Section -->
    <div class="mb-8 sm:mb-12">
        <h2 class="mb-6 sm:mb-12 text-home-heading font-medium sm:font-bold text-3xl  lg:text-[44px] xl:text-[62px] leading-tight sm:leading-[60px] lg:leading-[80px] font-plus">
            <?php echo e(__('doc.install.notes_heading')); ?>
        </h2>

        <!-- Cards Grid -->
        <div class="grid grid-cols-2 gap-3 sm:gap-6">
            <!-- System Requirements Card -->
            <div class="bg-white border border-gray-200 rounded-home-lg p-3 sm:p-4 md:p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col gap-2 sm:gap-3 md:gap-4">
                    <div class="flex flex-col xl:flex-row items-start gap-3 sm:gap-3 md:gap-4">
                        <div
                            class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-home-md bg-home-surface-light flex items-center justify-center">
                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <path
                                    d="M18 23V27M20.305 13.53L21.228 13.148M21.228 10.852L20.305 10.469M22.852 9.22796L22.469 8.30396M22.852 14.772L22.469 15.695M25.148 9.22796L25.531 8.30396M25.53 15.696L25.148 14.772M26.772 10.852L27.696 10.469M26.772 13.148L27.696 13.531M28 19V21C28 21.5304 27.7893 22.0391 27.4142 22.4142C27.0391 22.7892 26.5304 23 26 23H10C9.46957 23 8.96086 22.7892 8.58579 22.4142C8.21071 22.0391 8 21.5304 8 21V11C8 10.4695 8.21071 9.96081 8.58579 9.58574C8.96086 9.21067 9.46957 8.99996 10 8.99996H17M14 27H22M27 12C27 13.6568 25.6569 15 24 15C22.3431 15 21 13.6568 21 12C21 10.3431 22.3431 8.99996 24 8.99996C25.6569 8.99996 27 10.3431 27 12Z"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </div>
                        <h3 class="mb-0 font-plus text-home-heading text-xl sm:text-base md:text-xl lg:text-xl xl:text-2xl leading-tight sm:leading-normal md:leading-[36px]">
                            <?php echo e(__('doc.install.note_sysreq_title')); ?></h3>
                    </div>
                    <p class="text-home-body font-plus text-xs sm:text-[13px] md:text-sm leading-relaxed sm:leading-relaxed md:leading-[21px]">
                        <?php echo e(__('doc.install.note_sysreq_body')); ?></p>
                </div>
            </div>

            <!-- Full vs Lite Card -->
            <div class="bg-white border border-gray-200 rounded-home-lg p-3 sm:p-4 md:p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col gap-3 sm:gap-3 md:gap-4">
                    <div class="flex flex-col xl:flex-row items-start gap-3 sm:gap-3 md:gap-4">
                        <div
                            class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-home-md bg-home-surface-light flex items-center justify-center">
                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <path
                                    d="M15 19L17 21L21 17M26 26C26.5304 26 27.0391 25.7893 27.4142 25.4142C27.7893 25.0391 28 24.5304 28 24V14C28 13.4696 27.7893 12.9609 27.4142 12.5858C27.0391 12.2107 26.5304 12 26 12H18.1C17.7655 12.0033 17.4355 11.9226 17.1403 11.7654C16.8451 11.6081 16.594 11.3794 16.41 11.1L15.6 9.9C15.4179 9.62347 15.17 9.39648 14.8785 9.2394C14.587 9.08231 14.2611 9.00005 13.93 9H10C9.46957 9 8.96086 9.21071 8.58579 9.58579C8.21071 9.96086 8 10.4696 8 11V24C8 24.5304 8.21071 25.0391 8.58579 25.4142C8.96086 25.7893 9.46957 26 10 26H26Z"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mb-0 text-home-heading text-xl sm:text-base md:text-xl lg:text-2xl leading-tight sm:leading-normal md:leading-[36px] font-plus">
                            <?php echo e(__('doc.install.note_full_lite_title')); ?></h3>
                    </div>
                    <p class="text-home-body text-xs sm:text-[13px] md:text-sm leading-relaxed sm:leading-relaxed md:leading-[21px] font-plus">
                        <?php echo e(__('doc.install.note_full_lite_body')); ?>
                    </p>
                </div>
            </div>

            <!-- Location Card -->
            <div class="bg-white border border-gray-200 rounded-home-lg p-3 sm:p-4 md:p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col gap-2 sm:gap-3 md:gap-4">
                    <div class="flex flex-col xl:flex-row items-start gap-2 sm:gap-3 md:gap-4">
                        <div
                            class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-home-md bg-home-surface-light flex items-center justify-center">
                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <path
                                    d="M28 18H8M28 18V24C28 24.5304 27.7893 25.0391 27.4142 25.4142C27.0391 25.7893 26.5304 26 26 26H10C9.46957 26 8.96086 25.7893 8.58579 25.4142C8.21071 25.0391 8 24.5304 8 24V18M28 18L24.55 11.11C24.3844 10.7768 24.1292 10.4964 23.813 10.3003C23.4967 10.1042 23.1321 10.0002 22.76 10H13.24C12.8679 10.0002 12.5033 10.1042 12.187 10.3003C11.8708 10.4964 11.6156 10.7768 11.45 11.11L8 18M12 22H12.01M16 22H16.01"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mb-0 text-home-heading text-xl sm:text-base md:text-xl lg:text-2xl leading-tight sm:leading-normal md:leading-[36px] font-plus">
                            <?php echo e(__('doc.install.note_location_title')); ?></h3>
                    </div>
                    <p class="text-home-body text-xs sm:text-[13px] md:text-sm leading-relaxed sm:leading-relaxed md:leading-[21px] font-plus">
                        <?php echo e(__('doc.install.note_location_body')); ?></p>
                </div>
            </div>

            <!-- Virtual Hosts Card -->
            <div class="bg-white border border-gray-200 rounded-home-lg p-3 sm:p-4 md:p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col gap-2 sm:gap-3 md:gap-4">
                    <div class="flex flex-col xl:flex-row items-start gap-3 sm:gap-3 md:gap-4">
                        <div
                            class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-home-md bg-home-surface-light flex items-center justify-center">
                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <path
                                    d="M28 18H8M28 18V24C28 24.5304 27.7893 25.0391 27.4142 25.4142C27.0391 25.7893 26.5304 26 26 26H10C9.46957 26 8.96086 25.7893 8.58579 25.4142C8.21071 25.0391 8 24.5304 8 24V18M28 18L24.55 11.11C24.3844 10.7768 24.1292 10.4964 23.813 10.3003C23.4967 10.1042 23.1321 10.0002 22.76 10H13.24C12.8679 10.0002 12.5033 10.1042 12.187 10.3003C11.8708 10.4964 11.6156 10.7768 11.45 11.11L8 18M12 22H12.01M16 22H16.01"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mb-0 text-home-heading text-xl sm:text-base md:text-xl lg:text-2xl leading-tight sm:leading-normal md:leading-[36px] font-plus">
                            <?php echo e(__('doc.install.note_vhosts_title')); ?></h3>
                    </div>
                    <p class="text-home-body text-xs sm:text-[13px] md:text-sm leading-relaxed sm:leading-relaxed md:leading-[21px] font-plus">
                        <?php echo e(__('doc.install.note_vhosts_body')); ?></p>
                </div>
            </div>

            <!-- Auto-start Card -->
            <div class="bg-white border border-gray-200 rounded-home-lg p-3 sm:p-4 md:p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col gap-2 sm:gap-3 md:gap-4">
                    <div class="flex flex-col xl:flex-row items-start gap-3 sm:gap-3 md:gap-4">
                        <div
                            class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-home-md bg-home-surface-light flex items-center justify-center">
                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <path
                                    d="M28 18H8M28 18V24C28 24.5304 27.7893 25.0391 27.4142 25.4142C27.0391 25.7893 26.5304 26 26 26H10C9.46957 26 8.96086 25.7893 8.58579 25.4142C8.21071 25.0391 8 24.5304 8 24V18M28 18L24.55 11.11C24.3844 10.7768 24.1292 10.4964 23.813 10.3003C23.4967 10.1042 23.1321 10.0002 22.76 10H13.24C12.8679 10.0002 12.5033 10.1042 12.187 10.3003C11.8708 10.4964 11.6156 10.7768 11.45 11.11L8 18M12 22H12.01M16 22H16.01"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mb-0 text-home-heading text-xl sm:text-base md:text-xl lg:text-2xl leading-tight sm:leading-normal md:leading-[36px] font-plus  ">
                            <?php echo e(__('doc.install.note_autostart_title')); ?></h3>
                    </div>
                    <p class="text-home-body text-xs sm:text-[13px] md:text-sm leading-relaxed sm:leading-relaxed md:leading-[21px] font-plus">
                        <?php echo e(__('doc.install.note_autostart_body')); ?></p>
                </div>
            </div>

            <!-- MySQL Backup Card -->
            <div class="bg-white border border-gray-200 rounded-home-lg p-3 sm:p-4 md:p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col gap-3 sm:gap-3 md:gap-4">
                    <div class="flex flex-col xl:flex-row items-start gap-2 sm:gap-3 md:gap-4">
                        <div
                            class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-home-md bg-home-surface-light flex items-center justify-center">
                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <path
                                    d="M26.0984 11.7C26.0984 13.1912 22.4719 14.4 17.9984 14.4C13.5249 14.4 9.89844 13.1912 9.89844 11.7M26.0984 11.7C26.0984 10.2088 22.4719 9 17.9984 9C13.5249 9 9.89844 10.2088 9.89844 11.7M26.0984 11.7V24.3C26.0984 25.0161 25.245 25.7028 23.726 26.2092C22.207 26.7155 20.1467 27 17.9984 27C15.8502 27 13.7899 26.7155 12.2709 26.2092C10.7518 25.7028 9.89844 25.0161 9.89844 24.3V11.7M9.89844 18C9.89844 18.7161 10.7518 19.4028 12.2709 19.9092C13.7899 20.4155 15.8502 20.7 17.9984 20.7C20.1467 20.7 22.207 20.4155 23.726 19.9092C25.245 19.4028 26.0984 18.7161 26.0984 18"
                                    stroke="var(--home-primary)" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mb-0 text-home-heading text-xl sm:text-base md:text-xl lg:text-2xl leading-tight sm:leading-normal md:leading-[36px] font-plus">
                            <?php echo e(__('doc.install.note_mysql_title')); ?></h3>
                    </div>
                    <p class="text-home-body text-xs sm:text-[13px] md:text-sm leading-relaxed sm:leading-relaxed md:leading-[21px] font-plus">
                        <?php echo e(__('doc.install.note_mysql_body')); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
