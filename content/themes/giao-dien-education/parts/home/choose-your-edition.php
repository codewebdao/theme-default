<?php

/**
 * Card 1 (Portable) & 2 (Full): URL tải từ option CMS (link_download_laragoninstall, link_download_portabl).
 * Ghi đè trước khi include: $home_edition_full_download_url, $home_edition_portable_download_url.
 */
$lang = defined('APP_LANG') ? APP_LANG : 'all';
$home_edition_option_url = static function (string $key) use ($lang): string {
    if (!function_exists('option')) {
        return '';
    }
    $v = trim((string) option($key, $lang));
    if ($v !== '') {
        return $v;
    }

    return trim((string) option($key, 'all'));
};

if (!isset($home_edition_full_download_url)) {
    $home_edition_full_download_url = $home_edition_option_url('link_download_laragoninstall');
}
if (!isset($home_edition_portable_download_url)) {
    $home_edition_portable_download_url = $home_edition_option_url('link_download_portabl');
    if ($home_edition_portable_download_url === '') {
        $home_edition_portable_download_url = $home_edition_full_download_url;
    }
}

$home_edition_download_basename = static function (string $url): string {
    if ($url === '') {
        return '';
    }
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || $path === '' || substr($path, -1) === '/') {
        return '';
    }
    $b = basename($path);

    return ($b !== '' && $b !== '.' && $b !== '..') ? $b : '';
};
$home_edition_full_dl_name = $home_edition_download_basename((string) $home_edition_full_download_url);
$home_edition_portable_dl_name = $home_edition_download_basename((string) $home_edition_portable_download_url);

$homeEditionContactUrl = link_page('contact');
?>
<section class="py-12 sm:py-24 bg-[rgba(233,243,253,0.7)]">
  <div class="container mx-auto">
    <div class="text-center lg:mb-14 px-4">
      <h2 class="sr sr--fade-up w-full font-space text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-home-heading mb-8 sm:mb-12" style="--sr-delay: 0ms">
        <?php echo e(__('home_edition.heading')); ?>
      </h2>
    </div>

    <!-- Mobile only: card 2 giữa, 1.2 items; card 1 & 3 nhỏ (chiều cao tự nhiên), đáy 3 card thẳng hàng -->
    <div id="chooseEditionSwiper" class="sm:hidden mt-10 overflow-hidden choose-edition-swiper-container opacity-0 transition-opacity duration-200">
      <div class="swiper choose-edition-swiper choose-edition-swiper--equal-height">
        <div class="swiper-wrapper">

          <!-- Card 1 -->
          <div class="swiper-slide">
            <div class="p-[1px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg w-full flex flex-col">
              <div class="relative bg-white rounded-home-lg p-6 flex flex-col">
                <h3 class="text-lg text-gray-900 mb-2 text-center font-medium font-plus">
                  <?php echo e(__('home_edition.portable_title')); ?>
                </h3>
                <p class="text-sm text-gray-600 mb-6 text-center font-plus">
                  <?php echo e(__('home_edition.portable_desc')); ?>
                </p>
                <a href="<?php echo e($home_edition_portable_download_url); ?>"
                  <?php if ($home_edition_portable_dl_name !== ''): ?>download="<?php echo e($home_edition_portable_dl_name); ?>"<?php endif; ?>
                  rel="noopener noreferrer"
                  class="w-full flex items-center text-sm justify-center gap-2 border bg-gray-100 hover:border-home-primary text-gray-700 font-medium py-3 px-4 rounded-home-md transition no-underline">
                  <?php echo e(__('home_edition.download_portable')); ?>
                </a>
                <p class="text-xs text-gray-600 mt-4 text-center font-plus">
                  <?php echo e(__('home_edition.windows_bits')); ?>
                </p>
              </div>
            </div>
          </div>

          <!-- Card 2 (Recommended - cao nhất) -->
          <div class="swiper-slide">
            <div class="p-[1px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg w-full h-full flex flex-col">
              <div class="border-2 rounded-home-lg flex-1 flex flex-col min-h-0">
                <div class="relative bg-home-surface-light w-full flex-1 rounded-home-md p-6 flex flex-col min-h-0 justify-end">
                  <div class="text-center absolute left-1/2 -translate-x-1/2 top-6">
                    <span class="inline-block text-xs sm:text-sm font-medium px-4 sm:px-6 py-2 bg-home-primary text-white rounded-full font-plus"><?php echo e(__('home_edition.recommended')); ?></span>
                  </div>
                  <h3 class="text-lg sm:text-xl lg:text-2xl text-gray-900 mb-2 text-center mt-12 font-medium font-plus">
                    <?php echo e(__('home_edition.full_title')); ?>
                  </h3>
                  <p class="text-sm sm:text-base text-center text-gray-600 leading-tight font-plus">
                    <?php echo e(__('home_edition.full_desc')); ?>
                  </p>
                  <a href="<?php echo e($home_edition_full_download_url); ?>"
                    <?php if ($home_edition_full_dl_name !== ''): ?>download="<?php echo e($home_edition_full_dl_name); ?>"<?php endif; ?>
                    rel="noopener noreferrer"
                    class="w-full flex items-center justify-center gap-2 bg-home-primary hover:bg-home-primary-hover text-white py-3 px-4 rounded-home-md transition-colors duration-200 mt-8 no-underline">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="font-plus font-semibold text-sm"><?php echo e(__('home_edition.download_full')); ?></span>
                  </a>
                  <p class="text-xs text-gray-700 mt-4 text-center font-plus">
                    <?php echo e(__('home_edition.windows_bits')); ?>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3 -->
          <div class="swiper-slide">
            <div class="p-[1px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg w-full flex flex-col">
              <div class="relative bg-white rounded-home-lg p-6 flex flex-col">
                <h3 class="text-lg text-gray-900 mb-2 text-center font-medium font-plus">
                  <?php echo e(__('home_edition.outsource_title')); ?>
                </h3>
                <p class="text-sm text-gray-600 mb-6 text-center font-plus">
                  <?php echo e(__('home_edition.outsource_desc')); ?>
                </p>
                <a href="<?php echo e($homeEditionContactUrl); ?>"
                  class="w-full flex items-center text-sm justify-center border bg-gray-100 text-gray-700 font-medium py-3 px-4 rounded-home-md no-underline hover:border-home-primary transition">
                  <?php echo e(__('home_edition.chat_with_us')); ?>
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <script>
      (function() {
        var swiperInstance = null;

        function initSwiper() {
          var el = document.getElementById('chooseEditionSwiper');
          if (!el || typeof Swiper === 'undefined') return;

          var isMobile = window.innerWidth < 640;
          var c = el.querySelector('.choose-edition-swiper');

          if (isMobile) {
            if (!swiperInstance) {
              swiperInstance = new Swiper(c, {
                slidesPerView: 1.2,
                spaceBetween: 20,
                centeredSlides: true,
                centeredSlidesBounds: true,
                initialSlide: 1,
                // loop + slidesPerView thập phân ít slide dễ lệch peek khi đang vuốt; rewind giữ autoplay vòng lặp ổn định
                loop: false,
                rewind: true,
                speed: 450,
                roundLengths: true,
                resistanceRatio: 0,
                grabCursor: true,
                watchSlidesProgress: true,

                autoplay: {
                  delay: 3000,
                  disableOnInteraction: false,
                  pauseOnMouseEnter: true
                },

                on: {
                  init: function() {
                    el.classList.add('opacity-100');
                  }
                }
              });
            }
          } else {
            // destroy khi lên desktop
            if (swiperInstance) {
              swiperInstance.destroy(true, true);
              swiperInstance = null;
            }
          }
        }

        // load lần đầu
        window.addEventListener('DOMContentLoaded', initSwiper);

        // resize
        window.addEventListener('resize', initSwiper);
      })();
    </script>

    <!-- PC: grid giữ nguyên (wrapper tránh hidden đè sm:grid trong CSS build) -->
    <div class="hidden sm:block mt-8 lg:mt-12">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-8 xl:gap-10 items-end download-edition-desktop-grid">

        <!-- Card 1 -->
        <div class="p-[1px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg">
          <div class="relative bg-white rounded-home-lg w-full h-full p-6 flex flex-col">
            <h3 class="text-lg sm:text-xl lg:text-2xl text-gray-900 mb-2 text-center font-medium font-plus">
              <?php echo e(__('home_edition.portable_title')); ?>
            </h3>
            <p class="text-sm sm:text-base text-gray-600 mb-6 flex-grow text-center font-plus">
              <?php echo e(__('home_edition.portable_desc')); ?>
            </p>
            <a href="<?php echo e($home_edition_portable_download_url); ?>"
              <?php if ($home_edition_portable_dl_name !== ''): ?>download="<?php echo e($home_edition_portable_dl_name); ?>"<?php endif; ?>
              rel="noopener noreferrer"
              class="w-full flex items-center justify-center gap-2 border bg-gray-100 hover:border-home-primary text-gray-700 font-medium py-3 px-4 rounded-home-md transition-colors duration-200 no-underline">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="var(--home-heading)" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="font-plus"><?php echo e(__('home_edition.download_portable')); ?></span>
            </a>
            <p class="text-xs text-gray-600 mt-4 text-center font-plus">
              <?php echo e(__('home_edition.windows_bits')); ?>
            </p>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="p-[1px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg">
          <div class="border-2 rounded-home-lg">
            <div class="relative bg-home-surface-light w-full h-full rounded-home-md p-6 flex flex-col ">
              <div class="text-center absolute left-1/2 -translate-x-1/2">
                <span class="inline-block text-xs sm:text-sm font-medium px-4 sm:px-6 py-2 bg-home-primary text-white rounded-full font-plus"><?php echo e(__('home_edition.recommended')); ?></span>
              </div>
              <h3 class="text-lg sm:text-xl lg:text-2xl text-gray-900 mb-2 text-center mt-16 font-medium font-plus">
                <?php echo e(__('home_edition.full_title')); ?>
              </h3>
              <p class="text-sm sm:text-base text-center text-gray-600 flex-grow leading-tight font-plus">
                <?php echo e(__('home_edition.full_desc')); ?>
              </p>
              <a href="<?php echo e($home_edition_full_download_url); ?>"
                <?php if ($home_edition_full_dl_name !== ''): ?>download="<?php echo e($home_edition_full_dl_name); ?>"<?php endif; ?>
                rel="noopener noreferrer"
                class="w-full flex items-center justify-center gap-2 bg-home-primary hover:bg-home-primary-hover text-white font-medium py-3 px-4 rounded-home-md transition-colors duration-200 mt-8 no-underline">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="font-plus"><?php echo e(__('home_edition.download_full')); ?></span>
              </a>
              <p class="text-xs text-gray-700 mt-4 text-center font-plus">
                <?php echo e(__('home_edition.windows_bits')); ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="p-[1px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg">
          <div class="relative w-full h-full bg-white rounded-home-lg border-green-200 p-6 flex flex-col">
            <h3 class="text-lg sm:text-xl lg:text-2xl text-gray-900 mb-2 text-center font-medium font-plus">
              <?php echo e(__('home_edition.outsource_title')); ?>
            </h3>
            <p class="text-sm sm:text-base text-gray-600 mb-6 flex-grow text-center font-plus">
              <?php echo e(__('home_edition.outsource_desc')); ?>
            </p>
            <a href="<?php echo e($homeEditionContactUrl); ?>"
              class="w-full flex items-center border justify-center gap-2 bg-gray-100 hover:border-home-primary text-gray-700 font-medium py-3 px-4 rounded-home-md transition-colors duration-200 no-underline">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="var(--home-heading)" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M9.99994 8.99969L6.99994 11.9997L9.99994 14.9997M13.9999 14.9997L16.9999 11.9997L13.9999 8.99969M2.99194 16.3417C3.13897 16.7126 3.17171 17.119 3.08594 17.5087L2.02094 20.7987C1.98662 20.9655 1.99549 21.1384 2.04671 21.3008C2.09793 21.4633 2.1898 21.61 2.3136 21.727C2.43741 21.844 2.58904 21.9274 2.75413 21.9693C2.91923 22.0113 3.0923 22.0104 3.25694 21.9667L6.66994 20.9687C7.03765 20.8958 7.41846 20.9276 7.76894 21.0607C9.90432 22.0579 12.3233 22.2689 14.5991 21.6564C16.8749 21.0439 18.8612 19.6473 20.2076 17.7131C21.5541 15.7788 22.1741 13.4311 21.9582 11.0842C21.7424 8.73738 20.7046 6.54216 19.028 4.88589C17.3514 3.22962 15.1436 2.21873 12.7943 2.03159C10.445 1.84445 8.10507 2.49308 6.18738 3.86303C4.26968 5.23299 2.89747 7.23624 2.31283 9.51933C1.72819 11.8024 1.9687 14.2186 2.99194 16.3417Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="font-plus"><?php echo e(__('home_edition.chat_with_us')); ?></span>
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>