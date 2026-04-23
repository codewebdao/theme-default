<section class=" py-12 md:py-24 bg-white">
    <div class=" mx-auto container">
        <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-12 items-start">
            <div class="">
                <h2 class="sr sr--fade-up text-[30px] md:text-4xl lg:text-[48px] font-medium leading-normal md:leading-[61px] text-center md:text-left text-home-heading md:mb-2" style="--sr-delay: 0ms">
                    <?php echo e(__('home_performance.heading')); ?>
                </h2>
                <p
                    class="sr sr--fade-up text-home-body md:text-gray-600 md:max-w-[680px] font-plus mb-8 md:mb-12 text-center md:text-left text-[14px] md:text-base font-medium md:font-normal leading-[21px] md:leading-relaxed"
                  style="--sr-delay: 60ms">
                    <?php echo e(__('home_performance.intro')); ?>
                </p>

                <div class="grid gap-2 sm:gap-4 grid-cols-3">

                    <!-- StatCard 1 -->
                    <div
                        class="sr sr--fade-up group relative h-full py-[2px] rounded-home-md sm:rounded-home-xl bg-gradient-to-r from-home-accent to-home-primary transition-all duration-300 border-t-3 hover:shadow-[0_20px_40px_-15px_rgba(43,140,238,0.5)]" style="--sr-delay: 0ms">
                        <div
                            class="flex h-full flex-col items-center justify-center rounded-home-md sm:rounded-home-xl bg-white py-3 text-center">
                            <span
                                class="mb-1 sm:mb-4 font-medium sm:font-bold sm:text-4xl font-space text-[24px] leading-normal bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent  ">
                                <?php echo e(__('home_performance.card1_title')); ?>
                            </span>

                            <p class="max-w-[260px] text-sm font-plus sm:text-base text-gray-600 leading-[18px] sm:leading-relaxed font-medium sm:font-normal font-['Plus_Jakarta_Sans'] ">
                                <?php echo e(__('home_performance.card1_desc')); ?>
                            </p>
                        </div>
                    </div>
                    <div
                        class="sr sr--fade-up group relative h-full py-[2px] rounded-home-md sm:rounded-home-xl bg-gradient-to-r from-home-accent to-home-primary transition-all duration-300 border-t-3 hover:shadow-[0_20px_40px_-15px_rgba(43,140,238,0.5)]" style="--sr-delay: 80ms">
                        <div
                            class="flex h-full flex-col items-center justify-center rounded-home-md sm:rounded-home-xl bg-white py-3 text-center">
                            <span
                                class="mb-1 sm:mb-4 font-medium sm:font-bold sm:text-5xl font-space text-[24px] leading-normal bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent">
                                <?php echo e(__('home_performance.card2_title')); ?>
                            </span>

                            <p class="max-w-[260px] text-sm font-plus sm:text-base text-gray-600 leading-[18px] sm:leading-relaxed font-medium sm:font-normal font-['Plus_Jakarta_Sans'] ">
                                <?php echo e(__('home_performance.card2_desc')); ?>
                            </p>
                        </div>
                    </div>
                    <div
                        class="sr sr--fade-up group relative h-full py-[2px] rounded-home-md sm:rounded-home-xl bg-gradient-to-r from-home-accent to-home-primary transition-all duration-300 border-t-3 hover:shadow-[0_20px_40px_-15px_rgba(43,140,238,0.5)]" style="--sr-delay: 160ms">
                        <div
                            class="flex h-full flex-col items-center justify-center rounded-home-md sm:rounded-home-xl bg-white py-3 text-center">
                            <span
                                class="mb-1 sm:mb-4 font-medium sm:font-bold sm:text-5xl font-space text-[24px] leading-normal bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent">
                                <?php echo e(__('home_performance.card3_title')); ?>
                            </span>

                            <p class="max-w-[260px] text-sm font-plus sm:text-base text-gray-600 leading-[18px] sm:leading-relaxed font-medium sm:font-normal font-['Plus_Jakarta_Sans'] ">
                                <?php echo e(__('home_performance.card3_desc')); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Second Column with Progress Bars -->
            <div class="sr sr--slide-left bg-[rgba(233,243,253,0.4)] border rounded-home-lg sm:p-6 p-4 shadow-sm" style="--sr-delay: 40ms">
                <h3 class="text-[24px] text-home-heading mb-2 font-plus"><?php echo e(__('home_performance.chart_heading')); ?></h3>
                <p class="text-sm text-gray-500 mb-6 font-plus"><?php echo e(__('home_performance.chart_subtitle')); ?></p>

                <div class="space-y-5">
                    <!-- Progress Bars -->
                    <div class="w-full">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-blue-600 font-plus"><?php echo e(__('home_performance.bar_laragon')); ?></span>
                            <span
                                class="font-bold bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent"><?php echo e(__('home_performance.time_laragon')); ?></span>
                        </div>
                        <div class="h-4 w-full overflow-hidden rounded-full bg-home-surface">
                            <div class="h-full rounded-full transition-all duration-500 ease-out bg-gradient-to-r from-home-accent to-home-primary"
                                style="width: 10%"></div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="text-gray-700 font-plus"><?php echo e(__('home_performance.bar_xampp')); ?></span>
                            <span class="font-bold "><?php echo e(__('home_performance.time_xampp')); ?></span>
                        </div>
                        <div class="h-4 w-full overflow-hidden rounded-full bg-home-surface">
                            <div class="h-full rounded-full transition-all duration-500 ease-out bg-gray-300"
                                style="width: 45%"></div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="text-gray-700 font-plus"><?php echo e(__('home_performance.bar_wamp')); ?></span>
                            <span class="font-bold "><?php echo e(__('home_performance.time_wamp')); ?></span>
                        </div>
                        <div class="h-4 w-full overflow-hidden rounded-full bg-home-surface">
                            <div class="h-full rounded-full transition-all duration-500 ease-out bg-gray-300"
                                style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
