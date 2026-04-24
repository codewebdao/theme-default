<section class="py-12 sm:py-24 sm:bg-white bg-home-surface-light/50 rounded-t-[24px] sm:rounded-none sm:mb-0 mb-6">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="rounded-none bg-home-surface-light/50 sm:rounded-[48px] shadow-sm sm:p-8 md:p-12 lg:px-[106px] lg:py-[96px]">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="sr sr--fade-up text-[30px] sm:text-center text-left sm:text-3xl mb-2 md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-home-heading font-space" style="--sr-delay: 0ms">
                    <?php echo e(__('features_laragon.sec_heading')); ?>
                </h2>
                <p class="sr sr--fade-up text-gray-600 text-sm sm:text-base sm:text-center text-left font-plus" style="--sr-delay: 50ms">
                    <?php echo e(__('features_laragon.sec_subtitle')); ?>
                </p>
            </div>

            <!-- Features Grid -->
            <div class="flex flex-col gap-8">
                <!-- Feature 1: Auto SSL -->
                <div class="sr sr--fade-up flex gap-4" style="--sr-delay: 0ms">
                    <div class="flex-shrink-0">
                        <svg class="w-[60px] sm:w-[80px] h-[60px] sm:h-[80px] mb-4 sm:mb-0" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="80" height="80" rx="5.33333" fill="var(--home-primary)" fill-opacity="0.1" />
                            <path
                                d="M28.8889 37.7776V28.8887C28.8889 25.9418 30.0595 23.1157 32.1433 21.032C34.227 18.9482 37.0532 17.7776 40 17.7776C42.9469 17.7776 45.773 18.9482 47.8567 21.032C49.9405 23.1157 51.1111 25.9418 51.1111 28.8887V37.7776M24.4444 37.7776H55.5556C58.0102 37.7776 60 39.7674 60 42.222V57.7776C60 60.2322 58.0102 62.222 55.5556 62.222H24.4444C21.9898 62.222 20 60.2322 20 57.7776V42.222C20 39.7674 21.9898 37.7776 24.4444 37.7776Z"
                                stroke="var(--home-primary)" stroke-width="4.44444" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-medium  text-home-heading mb-2 font-plus">Auto SSL</h3>
                        <p class="text-gray-600 text-sm sm:text-base leading-relaxed">
                            Automatically generates valid SSL certificates for virtual domains. Enables HTTPS
                            development easily and aligns local environments with production standards.
                        </p>
                    </div>
                </div>

                <!-- Feature 2: Easy Database Management -->
                <div class="sr sr--fade-up flex gap-4" style="--sr-delay: 90ms">
                    <div class="flex-shrink-0">
                        <svg class="w-[60px] sm:w-[80px] h-[60px] sm:h-[80px] mb-4 sm:mb-0" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="80" height="80" rx="5.33333" fill="var(--home-primary)" fill-opacity="0.1"/>
                            <path d="M36.6667 60H24.4444C23.2657 60 22.1352 59.5317 21.3017 58.6983C20.4683 57.8648 20 56.7343 20 55.5556V24.4444C20 23.2657 20.4683 22.1352 21.3017 21.3017C22.1352 20.4683 23.2657 20 24.4444 20H55.5556C56.7343 20 57.8648 20.4683 58.6983 21.3017C59.5317 22.1352 60 23.2657 60 24.4444V36.6667M45.1111 56.8889L47.3333 56M46.6667 20V36.6667M47.1111 50.8889L45.1111 50.2222M50.2222 61.5556L50.8889 59.5556M50.6667 47.3333L49.7778 45.1111M55.7778 47.1111L56.4444 45.1111M56.8889 61.5556L56 59.3333M59.3333 50.6667L61.5556 49.7778M61.5556 56.4444L59.5556 55.7778M33.3333 20V60M60 53.3333C60 57.0152 57.0152 60 53.3333 60C49.6514 60 46.6667 57.0152 46.6667 53.3333C46.6667 49.6514 49.6514 46.6667 53.3333 46.6667C57.0152 46.6667 60 49.6514 60 53.3333Z" stroke="var(--home-primary)" stroke-width="4.44444" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>                            
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-medium text-home-heading mb-2 font-plus"><?php echo e(__('features_laragon.sec_db_title')); ?></h3>
                        <p class="text-gray-600 text-sm sm:text-base leading-relaxed">
                            <?php echo e(__('features_laragon.sec_db_desc')); ?>
                        </p>
                    </div>
                </div>

                <!-- Feature 3: Deep Configuration Customization -->
                <div class="sr sr--fade-up flex gap-4" style="--sr-delay: 180ms">
                    <div class="flex-shrink-0">
                        <svg class="w-[60px] sm:w-[80px] h-[60px] sm:h-[80px] mb-4 sm:mb-0" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="80" height="80" rx="5.33333" fill="var(--home-primary)" fill-opacity="0.1" />
                            <path
                                d="M35.5556 24.4442H20M40 55.5553H20M44.4444 19.9998V28.8886M48.8889 51.1109V59.9998M60 39.9998H40M60 55.5553H48.8889M60 24.4442H44.4444M31.1111 35.5553V44.4442M31.1111 39.9998H20"
                                stroke="var(--home-primary)" stroke-width="4.44444" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-medium text-home-heading mb-2 font-plus"><?php echo e(__('features_laragon.sec_deep_title')); ?></h3>
                        <p class="text-gray-600 text-sm sm:text-base leading-relaxed font-plus">
                            <?php echo e(__('features_laragon.sec_deep_desc')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="relative py-12 sm:py-24 overflow-hidden">

  <div class="absolute inset-0 " style="background-image: url('<?php echo theme_assets('images/frame1.webp'); ?>'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 opacity-30">
      <div class="absolute top-0 left-0 w-full h-full">
        <svg width="100%" height="100%" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="none">
          <path d="M0,300 Q300,100 600,300 T1200,300" stroke="rgba(255,255,255,0.3)" stroke-width="2" fill="none" />
          <path d="M0,200 Q400,400 800,200 T1200,200" stroke="rgba(135,206,250,0.4)" stroke-width="2" fill="none" />
          <path d="M0,400 Q200,100 500,400 T1200,400" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none" />
        </svg>
      </div>
    </div>
    <div class="absolute top-20 right-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-cyan-300/10 rounded-full blur-3xl"></div>
  </div>
  <div class="relative mx-auto px-4 sm:px-6 text-center z-10">
    <h2 class="sr sr--fade-up text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 sm:mb-6 font-space" style="--sr-delay: 0ms">
      <?php echo e(__('home_cta_faster.heading')); ?>
    </h2>
    <p class="sr sr--fade-up text-base sm:text-lg lg:text-xl text-white/90 mb-8 sm:mb-10 max-w-2xl mx-auto font-plus" style="--sr-delay: 60ms">
      <?php echo e(__('home_cta_faster.description')); ?>
    </p>
    <div class="sr sr--fade flex flex-col items-center gap-4 sm:gap-6" style="--sr-delay: 100ms">
      <a href="<?php echo e(base_url('download')); ?>"
        class="inline-flex items-center gap-3 bg-home-primary hover:bg-home-primary-hover text-white font-semibold px-16 sm:px-8 py-4 rounded-home-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12.8572 16.0715V3.21436M12.8572 16.0715L7.50007 10.7144M12.8572 16.0715L18.2144 10.7144M22.5001 16.0715V20.3572C22.5001 20.9255 22.2743 21.4706 21.8724 21.8724C21.4706 22.2743 20.9255 22.5001 20.3572 22.5001H5.35721C4.78889 22.5001 4.24385 22.2743 3.84198 21.8724C3.44012 21.4706 3.21436 20.9255 3.21436 20.3572V16.0715"
            stroke="white" stroke-width="2.14286" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="text-lg font-plus"><?php echo e(__('home_cta_faster.button_start')); ?></span>
      </a>
      <div class="flex items-center justify-center gap-2 text-white/80 text-xs">
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
        <span class="font-plus font-xs"><?php echo e(__('home_cta_faster.system_requirements')); ?></span>
      </div>
    </div>
  </div>
</section>

<section class=" py-12 sm:py-24">
    <div class="container mx-auto px-4 sm:px-6">
        <h2 class="sr sr--fade-up w-full text-[30px] sm:text-3xl lg:mb-12 md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading  sm:mb-12 mb-8 flex-none order-0 self-stretch flex-grow-0 font-space" style="--sr-delay: 0ms"><?php echo e(__('features_laragon.more_heading')); ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">

            <div
                class="sr sr--fade relative flex flex-col items-start border w-full h-full p-6 gap-3 bg-white rounded-home-lg overflow-hidden transition-all duration-300 hover:shadow-[0px_10px_30px_rgba(43,140,238,0.15)] hover:-translate-y-1" style="--sr-delay: 0ms">
                <div class="relative flex-none order-0 flex-grow-0 z-0">
                    <div class="flex items-center gap-2">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                            <path
                                d="M15 18L17 20L21 16M26 19C26 24 22.5 26.5 18.34 27.95C18.1222 28.0238 17.8855 28.0202 17.67 27.94C13.5 26.5 10 24 10 19V12C10 11.7347 10.1054 11.4804 10.2929 11.2929C10.4804 11.1053 10.7348 11 11 11C13 11 15.5 9.79996 17.24 8.27996C17.4519 8.09896 17.7214 7.99951 18 7.99951C18.2786 7.99951 18.5481 8.09896 18.76 8.27996C20.51 9.80996 23 11 25 11C25.2652 11 25.5196 11.1053 25.7071 11.2929C25.8946 11.4804 26 11.7347 26 12V19Z"
                                stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <span class="text-2xl text-home-heading font-plus"><?php echo e(__('features_laragon.more_reliable_title')); ?></span>
                    </div>

                </div>
                <p class="w-full  text-md text-left font-plus">
                    <?php echo e(__('features_laragon.more_reliable_desc')); ?>
                </p>
            </div>
            <div
                class="sr sr--fade relative flex flex-col items-start border w-full h-full p-6 gap-3 bg-white rounded-home-lg overflow-hidden transition-all duration-300 hover:shadow-[0px_10px_30px_rgba(43,140,238,0.15)] hover:-translate-y-1" style="--sr-delay: 60ms">
                <div class="relative flex-none order-0 flex-grow-0 z-0">
                    <div class="flex items-center gap-2">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                            <path
                                d="M16 17V23M20 17V23M25 12V26C25 26.5304 24.7893 27.0391 24.4142 27.4142C24.0391 27.7893 23.5304 28 23 28H13C12.4696 28 11.9609 27.7893 11.5858 27.4142C11.2107 27.0391 11 26.5304 11 26V12M9 12H27M14 12V10C14 9.46957 14.2107 8.96086 14.5858 8.58579C14.9609 8.21071 15.4696 8 16 8H20C20.5304 8 21.0391 8.21071 21.4142 8.58579C21.7893 8.96086 22 9.46957 22 10V12"
                                stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <span class="text-2xl text-home-heading font-plus"><?php echo e(__('features_laragon.more_disposable_title')); ?></span>
                    </div>

                </div>
                <p class="w-full  text-md text-left font-plus">
                    <?php echo e(__('features_laragon.more_disposable_desc')); ?>
                </p>
            </div>
            <div
                class="sr sr--fade relative flex flex-col items-start border w-full h-full p-6 gap-3 bg-white rounded-home-lg overflow-hidden transition-all duration-300 hover:shadow-[0px_10px_30px_rgba(43,140,238,0.15)] hover:-translate-y-1" style="--sr-delay: 120ms">
                <div class="relative flex-none order-0 flex-grow-0 z-0">
                    <div class="flex items-center gap-2">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)"/>
                            <path d="M27 18C27 15.6131 26.0518 13.3239 24.364 11.636C22.6761 9.94821 20.3869 9 18 9C15.484 9.00947 13.069 9.99122 11.26 11.74L9 14M9 14V9M9 14H14M9 18C9 20.3869 9.94821 22.6761 11.636 24.364C13.3239 26.0518 15.6131 27 18 27C20.516 26.9905 22.931 26.0088 24.74 24.26L27 22M27 22H22M27 22V27M19 18C19 18.5523 18.5523 19 18 19C17.4477 19 17 18.5523 17 18C17 17.4477 17.4477 17 18 17C18.5523 17 19 17.4477 19 18Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            


                        <span class="text-2xl text-home-heading font-plus"><?php echo e(__('features_laragon.more_multi_title')); ?></span>
                    </div>

                </div>
                <p class="w-full  text-md text-left font-plus">
                    <?php echo e(__('features_laragon.more_multi_desc')); ?>
                </p>
            </div>
            <div
                class="sr sr--fade relative flex flex-col items-start border w-full h-full p-6 gap-3 bg-white rounded-home-lg overflow-hidden transition-all duration-300 hover:shadow-[0px_10px_30px_rgba(43,140,238,0.15)] hover:-translate-y-1" style="--sr-delay: 180ms">
                <div class="relative flex-none order-0 flex-grow-0 z-0">
                    <div class="flex items-center gap-2">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                            <path
                                d="M15.6711 10.1362C15.7262 9.55649 15.9954 9.0182 16.4262 8.62643C16.8569 8.23467 17.4183 8.01758 18.0006 8.01758C18.5828 8.01758 19.1442 8.23467 19.575 8.62643C20.0057 9.0182 20.275 9.55649 20.3301 10.1362C20.3632 10.5106 20.486 10.8716 20.6882 11.1885C20.8904 11.5054 21.1659 11.769 21.4915 11.9568C21.8171 12.1447 22.1832 12.2514 22.5588 12.2678C22.9343 12.2842 23.3083 12.2099 23.6491 12.0512C24.1782 11.8109 24.7777 11.7762 25.3311 11.9536C25.8844 12.1311 26.3519 12.5081 26.6426 13.0113C26.9333 13.5144 27.0263 14.1077 26.9037 14.6757C26.7811 15.2437 26.4515 15.7458 25.9791 16.0842C25.6714 16.3 25.4203 16.5868 25.247 16.9202C25.0736 17.2536 24.9831 17.6239 24.9831 17.9997C24.9831 18.3754 25.0736 18.7457 25.247 19.0791C25.4203 19.4125 25.6714 19.6993 25.9791 19.9152C26.4515 20.2535 26.7811 20.7556 26.9037 21.3236C27.0263 21.8916 26.9333 22.4849 26.6426 22.988C26.3519 23.4912 25.8844 23.8682 25.3311 24.0457C24.7777 24.2231 24.1782 24.1884 23.6491 23.9482C23.3083 23.7894 22.9343 23.7151 22.5588 23.7315C22.1832 23.7479 21.8171 23.8546 21.4915 24.0425C21.1659 24.2303 20.8904 24.4939 20.6882 24.8108C20.486 25.1277 20.3632 25.4887 20.3301 25.8632C20.275 26.4428 20.0057 26.9811 19.575 27.3729C19.1442 27.7646 18.5828 27.9817 18.0006 27.9817C17.4183 27.9817 16.8569 27.7646 16.4262 27.3729C15.9954 26.9811 15.7262 26.4428 15.6711 25.8632C15.638 25.4886 15.5152 25.1275 15.3129 24.8104C15.1107 24.4934 14.835 24.2298 14.5093 24.0419C14.1836 23.854 13.8173 23.7474 13.4416 23.7311C13.0659 23.7147 12.6919 23.7892 12.3511 23.9482C11.8219 24.1884 11.2224 24.2231 10.6691 24.0457C10.1157 23.8682 9.64823 23.4912 9.35754 22.988C9.06685 22.4849 8.97377 21.8916 9.09642 21.3236C9.21907 20.7556 9.54866 20.2535 10.0211 19.9152C10.3287 19.6993 10.5798 19.4125 10.7531 19.0791C10.9265 18.7457 11.017 18.3754 11.017 17.9997C11.017 17.6239 10.9265 17.2536 10.7531 16.9202C10.5798 16.5868 10.3287 16.3 10.0211 16.0842C9.54932 15.7456 9.22031 15.2437 9.09796 14.6761C8.97561 14.1085 9.06867 13.5157 9.35904 13.0129C9.64942 12.51 10.1164 12.1331 10.6692 11.9554C11.2219 11.7777 11.821 11.8118 12.3501 12.0512C12.6908 12.2099 13.0648 12.2842 13.4404 12.2678C13.8159 12.2514 14.182 12.1447 14.5076 11.9568C14.8332 11.769 15.1088 11.5054 15.3109 11.1885C15.5131 10.8716 15.6359 10.5106 15.6691 10.1362M21.0001 18.0002C21.0001 19.657 19.6569 21.0002 18.0001 21.0002C16.3432 21.0002 15.0001 19.657 15.0001 18.0002C15.0001 16.3433 16.3432 15.0002 18.0001 15.0002C19.6569 15.0002 21.0001 16.3433 21.0001 18.0002Z"
                                stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>


                        <span class="text-2xl text-home-heading font-plus"><?php echo e(__('features_laragon.more_flexible_title')); ?></span>
                    </div>

                </div>
                <p class="w-full  text-md text-left font-plus">
                    <?php echo e(__('features_laragon.more_flexible_desc')); ?>
                </p>
            </div>
            <div
                class="sr sr--fade relative flex flex-col items-start border w-full h-full p-6 gap-3 bg-white rounded-home-lg overflow-hidden transition-all duration-300 hover:shadow-[0px_10px_30px_rgba(43,140,238,0.15)] hover:-translate-y-1" style="--sr-delay: 240ms">
                <div class="relative flex-none order-0 flex-grow-0 z-0">
                    <div class="flex items-center gap-2">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)"/>
                            <path d="M22 9.5C22 8.67157 21.3284 8 20.5 8C19.6716 8 19 8.67157 19 9.5V14.5C19 15.3284 19.6716 16 20.5 16C21.3284 16 22 15.3284 22 14.5V9.5Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M25 14.5V16H26.5C26.7967 16 27.0867 15.912 27.3334 15.7472C27.58 15.5824 27.7723 15.3481 27.8858 15.074C27.9993 14.7999 28.0291 14.4983 27.9712 14.2074C27.9133 13.9164 27.7704 13.6491 27.5607 13.4393C27.3509 13.2296 27.0836 13.0867 26.7926 13.0288C26.5017 12.9709 26.2001 13.0006 25.926 13.1142C25.6519 13.2277 25.4176 13.42 25.2528 13.6666C25.088 13.9133 25 14.2033 25 14.5Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 21.5C17 20.6716 16.3284 20 15.5 20C14.6716 20 14 20.6716 14 21.5V26.5C14 27.3284 14.6716 28 15.5 28C16.3284 28 17 27.3284 17 26.5V21.5Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11 21.5V20H9.5C9.20333 20 8.91332 20.088 8.66665 20.2528C8.41997 20.4176 8.22771 20.6519 8.11418 20.926C8.00065 21.2001 7.97094 21.5017 8.02882 21.7926C8.0867 22.0836 8.22956 22.3509 8.43934 22.5607C8.64912 22.7704 8.91639 22.9133 9.20737 22.9712C9.49834 23.0291 9.79994 22.9994 10.074 22.8858C10.3481 22.7723 10.5824 22.58 10.7472 22.3334C10.912 22.0867 11 21.7967 11 21.5Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M26.5 19H21.5C20.6716 19 20 19.6716 20 20.5C20 21.3284 20.6716 22 21.5 22H26.5C27.3284 22 28 21.3284 28 20.5C28 19.6716 27.3284 19 26.5 19Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21.5 25H20V26.5C20 26.7967 20.088 27.0867 20.2528 27.3334C20.4176 27.58 20.6519 27.7723 20.926 27.8858C21.2001 27.9993 21.5017 28.0291 21.7926 27.9712C22.0836 27.9133 22.3509 27.7704 22.5607 27.5607C22.7704 27.3509 22.9133 27.0836 22.9712 26.7926C23.0291 26.5017 22.9994 26.2001 22.8858 25.926C22.7723 25.6519 22.58 25.4176 22.3334 25.2528C22.0867 25.088 21.7967 25 21.5 25Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5 14H9.5C8.67157 14 8 14.6716 8 15.5C8 16.3284 8.67157 17 9.5 17H14.5C15.3284 17 16 16.3284 16 15.5C16 14.6716 15.3284 14 14.5 14Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5 11H16V9.5C16 9.20333 15.912 8.91332 15.7472 8.66665C15.5824 8.41997 15.3481 8.22771 15.074 8.11418C14.7999 8.00065 14.4983 7.97094 14.2074 8.02882C13.9164 8.0867 13.6491 8.22956 13.4393 8.43934C13.2296 8.64912 13.0867 8.91639 13.0288 9.20737C12.9709 9.49834 13.0006 9.79994 13.1142 10.074C13.2277 10.3481 13.42 10.5824 13.6666 10.7472C13.9133 10.912 14.2033 11 14.5 11Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            

                        <span class="text-2xl text-home-heading font-plus"><?php echo e(__('features_laragon.more_easy_title')); ?></span>
                    </div>

                </div>
                <p class="w-full  text-md text-left font-plus">
                    <?php echo e(__('features_laragon.more_easy_desc')); ?>
                </p>
            </div>
            <div
                class="sr sr--fade relative flex flex-col items-start border w-full h-full p-6 gap-3 bg-white rounded-home-lg overflow-hidden transition-all duration-300 hover:shadow-[0px_10px_30px_rgba(43,140,238,0.15)] hover:-translate-y-1" style="--sr-delay: 300ms">
                <div class="relative flex-none order-0 flex-grow-0 z-0">
                    <div class="flex items-center gap-2">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)"/>
                            <path d="M18 27.9999V17.9999M18 17.9999L9.29 12.9999M18 17.9999L26.71 12.9999M13.5 10.2699L22.5 15.4199M17 27.7299C17.304 27.9054 17.6489 27.9979 18 27.9979C18.3511 27.9979 18.696 27.9054 19 27.7299L26 23.7299C26.3037 23.5545 26.556 23.3024 26.7315 22.9987C26.9071 22.6951 26.9996 22.3506 27 21.9999V13.9999C26.9996 13.6492 26.9071 13.3047 26.7315 13.0011C26.556 12.6974 26.3037 12.4453 26 12.2699L19 8.2699C18.696 8.09437 18.3511 8.00195 18 8.00195C17.6489 8.00195 17.304 8.09437 17 8.2699L10 12.2699C9.69626 12.4453 9.44398 12.6974 9.26846 13.0011C9.09294 13.3047 9.00036 13.6492 9 13.9999V21.9999C9.00036 22.3506 9.09294 22.6951 9.26846 22.9987C9.44398 23.3024 9.69626 23.5545 10 23.7299L17 27.7299Z" stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            
                            

                        <span class="text-2xl text-home-heading font-plus"><?php echo e(__('features_laragon.more_isolated_title')); ?></span>
                    </div>

                </div>
                <p class="w-full  text-md text-left font-plus">
                    <?php echo e(__('features_laragon.more_isolated_desc')); ?>
                </p>
            </div>

        </div>
    </div>
</section>