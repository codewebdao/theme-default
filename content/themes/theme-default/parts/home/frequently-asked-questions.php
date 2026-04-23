<?php
$faq_items = [
  ['q' => __('home_faq.q1.question'), 'a' => __('home_faq.q1.answer')],
  ['q' => __('home_faq.q2.question'), 'a' => __('home_faq.q2.answer')],
  ['q' => __('home_faq.q3.question'), 'a' => __('home_faq.q3.answer')],
  ['q' => __('home_faq.q4.question'), 'a' => __('home_faq.q4.answer')],
];
?>
<section class="py-12 sm:py-24">
  <div class="container mx-auto">
    <!-- Section Title -->
    <div class="text-center mb-12 lg:mb-14 ">
      <div
        class="sr sr--fade-up inline-flex items-center gap-2 bg-home-surface-light text-home-primary text-xs sm:text-sm px-4 py-2 rounded-full mb-2" style="--sr-delay: 0ms">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
          <path
            d="M9.1 9C9.34 8.34 9.8 7.78 10.41 7.43C11.02 7.08 11.73 6.95 12.43 7.07C13.12 7.19 13.75 7.55 14.21 8.08C14.66 8.62 14.91 9.3 14.92 10C14.92 12 11.92 13 11.92 13M12 17H12.01M20 13C20 18 16.5 20.5 12.34 21.95C12.12 22.02 11.89 22.02 11.67 21.94C7.5 20.5 4 18 4 13V6C4 5.45 4.45 5 5 5C7 5 9.5 3.8 11.24 2.28C11.45 2.1 11.72 2 12 2C12.28 2 12.55 2.1 12.76 2.28C14.51 3.81 17 5 19 5C19.55 5 20 5.45 20 6V13Z"
            stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="font-medium font-plus"><?php echo e(__('home_faq.badge')); ?></span>
      </div>
      <h2
        class="sr sr--fade-up w-full font-space text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-3 sm:mb-2 flex-none order-0 self-stretch flex-grow-0" style="--sr-delay: 40ms">
        <?php echo e(__('home_faq.heading')); ?>
      </h2>
      <p class="sr sr--fade-up text-gray-500 mt-3 text-sm md:text-base leading-relaxed font-plus" style="--sr-delay: 80ms">
        <?php echo e(__('home_faq.subheading')); ?>
      </p>
    </div>

    <div class="max-w-3xl mx-auto space-y-4 faq-accordion">
      <?php foreach ($faq_items as $i => $item): ?>
      <div class="border border-gray-200 rounded-2xl overflow-hidden faq-item">
        <button type="button" class="faq-btn flex w-full items-center justify-between p-4 sm:p-6 text-left font-medium font-plus text-gray-900 hover:bg-gray-50 transition-colors">
          <span class="font-plus"><?php echo e($item['q']); ?></span>
          <svg class="faq-chevron transition-transform duration-300 ease-in-out flex-shrink-0" width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M15 19.25L22.5 11.75L20.75 10L15 15.75L9.25 10L7.5 11.75L15 19.25Z" fill="var(--home-body)" />
          </svg>
        </button>
        <div class="faq-content overflow-hidden" aria-hidden="true">
          <div class="px-4 sm:px-6 pb-4 sm:pb-6 text-gray-500 font-plus">
            <?php echo e($item['a']); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
