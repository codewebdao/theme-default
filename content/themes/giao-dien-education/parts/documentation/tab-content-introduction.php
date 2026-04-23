<!-- Introduction Tab Content -->
 <?php 
$data = $usage_guide_posts;


?>
<div class="mb-12 sm:px-0">
    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[48px] xl:text-[64px]
           font-bold text-black
           text-center lg:text-left
           mb-3 sm:mb-6
           leading-tight lg:leading-[80px] font-space">
        <?php echo e(__('doc.intro.title_line1')); ?> <br />
        <span class="bg-gradient-to-r from-home-accent to-home-primary bg-clip-text text-transparent">
            <?php echo e(__('doc.intro.title_highlight')); ?>
        </span>
    </h1>

    <p class="text-base sm:text-lg md:text-xl 
           text-gray-600 
           text-center lg:text-left
           mb-6 md:mb-12 
           leading-relaxed font-plus">
        <?php echo e(__('doc.intro.lead')); ?>
    </p>


    <!-- Cards Grid -->
    <div
        class="flex md:grid md:grid-cols-3 gap-4 md:gap-6 mb-12 overflow-x-auto md:overflow-x-visible pb-4 md:pb-0 scrollbar-hide -mx-4 px-4 sm:mx-0 sm:px-0">
        <!-- Quick Start Card — khớp markup laragon `tab-content-introduction.html` -->
        <div @click="activeTab = 'installation'"
            class="group rounded-home-lg transition-all duration-300 border-transparent p-[1.5px] bg-gradient-to-r from-home-accent to-home-primary md:border-gray-200 md:border md:bg-none md:p-0 md:hover:border-transparent md:hover:p-[1px] md:hover:bg-gradient-to-r md:hover:from-home-accent md:hover:to-home-primary flex-shrink-0 w-[200px] md:w-full cursor-pointer">
            <div
                class="group relative w-full h-full bg-white md:border md:border-gray-200 rounded-home-lg p-4 md:p-6 lg:p-2 xl:p-6 hover:shadow-lg transition-all duration-200 overflow-hidden">

                <div
                    class="absolute -bottom-[36px] -right-[60px] opacity-10 md:group-hover:text-home-primary md:group-hover:opacity-30 md:group-hover:scale-110 transition-all duration-300 ease-out rotate-[-19.82deg] md:group-hover:-translate-y-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="104" height="104" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-notebook-pen-icon lucide-notebook-pen">
                        <path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4" />
                        <path d="M2 6h4" />
                        <path d="M2 10h4" />
                        <path d="M2 14h4" />
                        <path d="M2 18h4" />
                        <path
                            d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
                    </svg>
                </div>
                <h3 class="font-plus text-base md:text-xl text-gray-900 mb-2 font-plus"><?php echo e(__('doc.intro.card_quick_title')); ?></h3>
                <p class="text-gray-600 mb-4 flex-1 text-sm md:text-base font-plus"><?php echo e(__('doc.intro.card_quick_desc')); ?></p>
                <!-- Go Link -->
                <span
                    class="inline-flex items-center gap-2 text-home-primary font-medium opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 h-6 text-md md:text-md">
                    <?php echo e(__('doc.intro.go')); ?> <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="md:w-6 md:h-6"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-arrow-right-icon lucide-arrow-right">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg></span>
                </span>
            </div>
        </div>

        <!-- Workflows Card -->
        <div @click="activeTab = 'workflows'"
            class="group rounded-home-lg transition-all duration-300 border-transparent p-[1.5px] bg-gradient-to-r from-home-accent to-home-primary md:border-gray-200 md:border md:bg-none md:p-0 md:hover:border-transparent md:hover:p-[1px] md:hover:bg-gradient-to-r md:hover:from-home-accent md:hover:to-home-primary flex-shrink-0 w-[200px] md:w-full cursor-pointer">
            <div
                class="group relative w-full h-full bg-white md:border md:border-gray-200 rounded-home-lg p-4 md:p-6 lg:p-2 xl:p-6 hover:shadow-lg transition-all duration-200 overflow-hidden">

                <div
                    class="absolute -bottom-[36px] -right-[60px] opacity-10 md:group-hover:text-home-primary md:group-hover:opacity-30 md:group-hover:scale-110 transition-all duration-300 ease-out rotate-[-19.82deg] md:group-hover:-translate-y-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="104" height="104" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-notebook-pen-icon lucide-notebook-pen">
                        <path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4" />
                        <path d="M2 6h4" />
                        <path d="M2 10h4" />
                        <path d="M2 14h4" />
                        <path d="M2 18h4" />
                        <path
                            d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
                    </svg>
                </div>
                <h3 class="text-base md:text-xl text-gray-900 mb-2 font-plus"><?php echo e(__('doc.intro.card_workflows_title')); ?></h3>
                <p class="text-gray-600 mb-4 flex-1 text-sm md:text-base font-plus"><?php echo e(__('doc.intro.card_workflows_desc')); ?></p>
                <!-- Go Link -->
                <span
                    class="inline-flex items-center gap-2 text-home-primary font-medium opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 h-6 text-md md:text-md">
                    <?php echo e(__('doc.intro.go')); ?> <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="md:w-6 md:h-6"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-arrow-right-icon lucide-arrow-right">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg></span>
                </span>
            </div>
        </div>

        <!-- FAQs Card -->
        <div @click="activeTab = 'troubleshooting'"
            class="group rounded-home-lg transition-all duration-300 border-transparent p-[1.5px] bg-gradient-to-r from-home-accent to-home-primary md:border-gray-200 md:border md:bg-none md:p-0 md:hover:border-transparent md:hover:p-[1px] md:hover:bg-gradient-to-r md:hover:from-home-accent md:hover:to-home-primary flex-shrink-0 w-[200px] md:w-full cursor-pointer">
            <div
                class="group relative w-full h-full bg-white md:border md:border-gray-200 rounded-home-lg p-4 md:p-6 lg:p-2 xl:p-6 hover:shadow-lg transition-all duration-200 overflow-hidden">

                <div
                    class="absolute -bottom-[36px] -right-[60px] opacity-10 md:group-hover:text-home-primary md:group-hover:opacity-30 md:group-hover:scale-110 transition-all duration-300 ease-out rotate-[-19.82deg] md:group-hover:-translate-y-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="104" height="104" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-notebook-pen-icon lucide-notebook-pen">
                        <path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4" />
                        <path d="M2 6h4" />
                        <path d="M2 10h4" />
                        <path d="M2 14h4" />
                        <path d="M2 18h4" />
                        <path
                            d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
                    </svg>
                </div>
                <h3 class="font-plus text-base md:text-xl text-gray-900 mb-2 font-plus"><?php echo e(__('doc.intro.card_faq_title')); ?></h3>
                <p class="text-gray-600 mb-4 flex-1 text-sm md:text-base font-plus"><?php echo e(__('doc.intro.card_faq_desc')); ?></p>
                <!-- Go Link -->
                <span
                    class="inline-flex items-center gap-2 text-home-primary font-medium opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 h-6 text-md md:text-md font-plus">
                    <?php echo e(__('doc.intro.go')); ?> <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="md:w-6 md:h-6"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-arrow-right-icon lucide-arrow-right">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="border-2 border-home-primary rounded-home-lg p-4 sm:p-6 bg-home-surface-light">
        <div class="flex flex-row gap-3 sm:gap-4">
            <div class="flex items-start flex-shrink-0">
                <svg width="32" height="32" class="sm:w-12 sm:h-12" viewBox="0 0 48 48" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="3.2" fill="var(--home-primary)" fill-opacity="0.1" />
                    <path
                        d="M25.867 10.6667H16.0003C15.2931 10.6667 14.6148 10.9477 14.1147 11.4478C13.6146 11.9478 13.3337 12.6261 13.3337 13.3334V34.6667C13.3337 35.3739 13.6146 36.0522 14.1147 36.5523C14.6148 37.0524 15.2931 37.3334 16.0003 37.3334H32.0003C32.7076 37.3334 33.3858 37.0524 33.8859 36.5523C34.386 36.0522 34.667 35.3739 34.667 34.6667V24.8M10.667 16H16.0003M10.667 21.3334H16.0003M10.667 26.6667H16.0003M10.667 32H16.0003M36.5043 15.5014C37.0355 14.9702 37.3339 14.2498 37.3339 13.4987C37.3339 12.7476 37.0355 12.0272 36.5043 11.496C35.9732 10.9649 35.2528 10.6665 34.5017 10.6665C33.7505 10.6665 33.0301 10.9649 32.499 11.496L25.819 18.1787C25.502 18.4955 25.27 18.8871 25.1443 19.3174L24.0283 23.144C23.9949 23.2588 23.9929 23.3804 24.0225 23.4962C24.0522 23.6119 24.1124 23.7176 24.1969 23.8021C24.2814 23.8866 24.3871 23.9469 24.5029 23.9765C24.6186 24.0062 24.7403 24.0042 24.855 23.9707L28.6817 22.8547C29.1119 22.7291 29.5035 22.497 29.8203 22.18L36.5043 15.5014Z"
                        stroke="var(--home-primary)" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="text-left">
                <h4 class="font-plus text-[16px] sm:text-lg font-semibold text-home-heading mb-1 sm:mb-2 leading-6"
                    style="font-family: 'Plus Jakarta Sans', sans-serif;"><?php echo e(__('doc.intro.read_heading')); ?></h4>
                <p class="text-xs sm:text-base text-gray-700 leading-relaxed font-plus">
                    <?php echo e(__('doc.intro.read_p1')); ?><span class="text-home-heading font-semibold"><?php echo e(__('doc.intro.read_emphasis')); ?></span><?php echo e(__('doc.intro.read_p2')); ?>
                </p>
            </div>
        </div>
    </div>
</div>
