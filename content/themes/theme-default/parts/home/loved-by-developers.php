<?php
// API columns: id, title, slug, lang_slug, status, search_string, description, content, seo_title, seo_desc, avatar, created_at, updated_at
$reviews = $reviews ?? [];
$list = [];
foreach ($reviews as $row) {
  $avatar = null;
  if (isset($row['avatar']) && $row['avatar'] !== '') {
    $av = $row['avatar'];
    if (is_array($av)) {
      $avatar = $av;
    } elseif (is_string($av) && (strpos(trim($av), '{') === 0)) {
      $decoded = json_decode($av, true);
      $avatar = is_array($decoded) ? $decoded : $av;
    } else {
      $avatar = $av;
    }
  }
  $list[] = [
    'name'    => $row['title'] ?? '',
    'role'    => $row['description'] ?? '',
    'content' => $row['content'] ?? '',
    'avatar'  => $avatar,
  ];
}
$totalReviews = count($list);
$desktopDots = $totalReviews >= 3 ? $totalReviews - 2 : max(1, $totalReviews);
?>
<section class="py-12 sm:py-24 container">
  <style>
    /* Avatar: clip tròn ở wrapper — <picture> từ _imglazy + lazy load đôi khi làm rounded-full trên img không ổn định */
    .loved-avatar-wrap picture {
      display: block;
      height: 100%;
      width: 100%;
    }
    .loved-avatar-wrap img {
      display: block;
      height: 100%;
      width: 100%;
      max-width: none;
      object-fit: cover;
    }
  </style>
  <!-- Title -->
  <div class=" mx-auto">
    <!-- Section Header -->
    <h2
      class="sr sr--fade-up w-full text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-2 flex-none order-0 self-stretch flex-grow-0 px-4 font-space" style="--sr-delay: 0ms">
      <?php echo e(__('home_loved.heading')); ?></h2>
    <div
      class="sr sr--fade-up text-center mb-12 text-home-body text-sm md:text-base max-w-3xl mx-auto px-4 leading-relaxed font-plus" style="--sr-delay: 50ms">
      <?php echo e(__('home_loved.intro')); ?>
    </div>


    <!-- Slider -->
    <div class="relative">
      <?php if (!empty($list)): ?>
        <!-- VIEWPORT -->
        <!-- Mobile: Scrollable với scroll-snap (chỉ mobile) -->
        <div class="relative sm:hidden">
          <div id="slider-mobile" class="overflow-x-auto snap-x snap-mandatory"
            style="scrollbar-width: none; -ms-overflow-style: none;">
            <style>
              #slider-mobile::-webkit-scrollbar {
                display: none;
              }
            </style>
            <div class="flex">
              <?php foreach ($list as $idx => $item): ?>
                <div class="min-w-[83.33%] snap-start px-2">
                  <div class="group rounded-home-lg transition-all duration-300 border border-gray-200 hover:border-transparent hover:p-[1px] hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary w-full">
                    <div class="bg-white rounded-home-lg p-4 sm:p-4 md:p-5 transition w-full h-full">
                      <div class="flex items-center gap-2 sm:gap-2.5 md:gap-3 mb-3 sm:mb-3 md:mb-4">
                        <?php if (!empty($item['avatar'])): ?>
                          <div class="loved-avatar-wrap flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 lg:w-[80px] lg:h-[80px] overflow-hidden rounded-full">
                            <?php echo _imglazy($item['avatar'], ['alt' => $item['name']]); ?>
                          </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                          <p class="font-medium bg-home-surface-light rounded-md py-1.5 sm:py-1.5 md:py-2 px-2 sm:px-2.5 md:px-3 text-xs sm:text-xs md:text-sm font-plus"><?php echo e($item['name']); ?></p>
                          <?php if (!empty($item['role'])): ?><p class="text-xs sm:text-xs md:text-sm text-gray-500 mt-1 font-plus"><?php echo e($item['role']); ?></p><?php endif; ?>
                        </div>
                      </div>
                      <div class="mb-2 sm:mb-2.5 md:mb-3">
                        <svg width="290" height="40" viewBox="0 0 290 40" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="w-full h-auto">
                          <rect width="40" height="40" fill="url(#pattern_mob_<?php echo $idx; ?>)" />
                          <line x1="53" y1="19" x2="289" y2="19" stroke="var(--home-heading)" stroke-width="2" stroke-linecap="round" />
                          <defs>
                            <pattern id="pattern_mob_<?php echo $idx; ?>" patternContentUnits="objectBoundingBox" width="1" height="1">
                              <use xlink:href="#img_mob_<?php echo $idx; ?>" transform="scale(0.01)" />
                            </pattern>
                            <image id="img_mob_<?php echo $idx; ?>" width="100" height="100" preserveAspectRatio="none" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEB0lEQVR4nO3dS6hXVRTH8e8tJR9QFpImFYWJSJOEIhAxI02LMsxE0IEDc+ArGgQ6EK2GRRJogmhQhA6yQY0iCKQgUMwiTAQRAxNFC/KdPW47NuxBO+J69r7nnLWWrQ+csb/ff92/573/4JxzzjnnnHPOOeeccz26GZgPvAnsBj5seXsHWAHc3kH2O4AX07/Rdu7d6TOZB9xETx4BvgdCD9t5YE2L2V8CLvSU/TDwMB2bDVztqVD4x/ZGC9nfEsgdP6vH6Mg44JxAqZC2BcPIvlAw91ngNjqwSbBUAL4bRvbDwtk30oFvhUsFYHJF7ikKch/qYB5cVlBsfkXupxXkvtTBPMRLBWBxRe7FCnLHrXXShYIPJCddKPhActKFgg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJCddKPhActKF gg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJCddKPhActKF gg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJPeX0YEsulEH8pPRgcy+UQfyqdGBjAN+V5C9dUuMDiT6SEH21g0A+4wOZEqP7xb2NpBoPHDQ4ECiOcJD6czo9Hrbz8YGEj0A7BXap/Tyrnp87fe59EGVbmcrSj3fUvb4/vusytwbKnIPYsCpimLzpEMDMypyX8SAmlesZ0qHrjyvOYMB5yuKTZcODTxZkfs4BtSsCHG/dGjgmYrc8chUvT8LS/0BjJQOnQ4sSgcSj+xUu7Wi1A/osEJorZZOTa4otQ8d1ldkX41yNYeOb6PDFqNHh62vzrMMHfZU7PvGoNwrPS0804X9hbm/wYDtFSdWA9g8od2KAZ8VlnoXu0eHT2HAycJSL6DDo4V5r6Sr46pNKCz1a/rL1GBNYfaPMaD00sMe9HivMHs8mlTv1cJSc9GjZHnceAPvFgz4vKDUiT4XJG5w+3qwIHtcUFm9scC1glJr0WNpQe54i/heDHi28Cs/Fj3eL8ge9zUmbCsotR49BtLJadNLJVMxYARwumGpV8qu/zxe8IcUr0KYsKCg1HJ0+aBh7vjc10SM+KRhqS8VXbcirdt+pWH2dRhxV/q/tclZ+TR0Wd1wGAfSs2omNL2pswpdRqYnRposJW5iRx5NaviEyV70afrtiOcoZuxoUOh4V7+xMQyjgB8bZI8/nWTGfQ0ebI6PWT6EPi83GMZXaXBmvH6dQr8BT6DT0etkP5J+YMyUXUMUGkxvZWl1YYjs8QbbPRi0cohvhuZhDHWb+ZiSR1qrDxu/+I99RnybSbsHgV/+lf1r4E6MG5F+yHEn8BpwN3ZMAjan7Cut3HByzjnnnHPOOeecc87x//M3gbACcetr2BAAAAAASUVORK5CYII=" />
                          </defs>
                        </svg>
                      </div>
                      <div class="text-gray-600 leading-relaxed text-sm sm:text-sm md:text-base font-plus prose prose-sm max-w-none"><?php echo $item['content']; ?></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Mobile Navigation: Nút và Dots nằm ngang -->
        <div class="flex items-center justify-center gap-4 mt-6 sm:hidden">
          <button type="button" onclick="prevMobile()" aria-label="<?php echo e(__('home_loved.aria_prev')); ?>"
            class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-full transition-colors">
            <svg class="w-6 h-6" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M17.2 24L29.2 36L32 33.2L22.8 24L32 14.8L29.2 12L17.2 24Z" fill="var(--home-body)" />
            </svg>
          </button>
          <div class="flex gap-1 items-center">
            <?php for ($i = 0; $i < $totalReviews; $i++): ?>
              <button type="button" onclick="goToMobile(<?php echo $i; ?>)"
                class="dot-mobile inline-flex items-center justify-center p-0 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2"
                aria-label="<?php echo e(__('home_loved.aria_goto_slide', (string) ($i + 1), (string) $totalReviews)); ?>"
                <?php echo $i === 0 ? 'aria-current="true"' : ''; ?>>
                <span class="dot-mobile-indicator block w-2 h-2 rounded-full <?php echo $i === 0 ? 'bg-blue-500' : 'bg-gray-300'; ?> transition-colors" aria-hidden="true"></span>
              </button>
            <?php endfor; ?>
          </div>
          <button type="button" onclick="nextMobile()" aria-label="<?php echo e(__('home_loved.aria_next')); ?>"
            class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-full transition-colors">
            <svg class="w-6 h-6" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M30.8 24L18.8 36L16 33.2L25.2 24L16 14.8L18.8 12L30.8 24Z" fill="var(--home-body)" />
            </svg>
          </button>
        </div>

        <!-- Tablet & Desktop: viewport + nút prev/next trong khung (flex, không tràn ra ngoài) -->
        <div class="hidden sm:flex sm:items-center sm:gap-2 md:gap-3 lg:gap-4 w-full min-w-0">
          <button type="button" onclick="prev()" aria-label="<?php echo e(__('home_loved.aria_prev')); ?>"
            class="hidden lg:inline-flex flex-shrink-0 items-center justify-center
              w-10 h-10 rounded-full border border-gray-200 bg-white shadow-sm
              text-home-body hover:bg-gray-50 hover:border-gray-300 active:scale-95
              focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2
              transition-all duration-200 z-10">
            <svg class="w-5 h-5" viewBox="0 0 48 48" fill="none" aria-hidden="true">
              <path d="M17.2 24L29.2 36L32 33.2L22.8 24L32 14.8L29.2 12L17.2 24Z" fill="currentColor" />
            </svg>
          </button>
          <div id="loved-slider-viewport" class="flex-1 min-w-0 overflow-hidden">
            <div id="slider" class="flex gap-4 transition-transform duration-500 px-1 sm:px-0">

              <?php foreach ($list as $dIdx => $item): ?>
                <?php $uid = 'svg_' . $dIdx; ?>

                <div class="loved-slider-card flex-shrink-0 w-[280px] sm:w-[320px] group rounded-home-lg transition-all duration-300 border border-gray-200 hover:border-transparent hover:p-[1px] hover:bg-gradient-to-r hover:from-home-accent hover:to-home-primary">

                  <div class="bg-white rounded-home-lg p-4 sm:p-4 md:p-5 transition w-full h-full flex flex-col">

                    <!-- Header -->
                    <div class="flex items-center gap-2 sm:gap-2.5 md:gap-3 mb-3 sm:mb-3 md:mb-4">
                      <?php if (!empty($item['avatar'])): ?>
                        <div class="loved-avatar-wrap flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-[60px] lg:h-[60px] overflow-hidden rounded-full">
                          <?php echo _imglazy($item['avatar'], ['alt' => $item['name']]); ?>
                        </div>
                      <?php endif; ?>

                      <div class="min-w-0">
                        <p class="font-medium bg-home-surface-light rounded-md py-1.5 px-2.5 text-xs md:text-sm font-plus truncate">
                          <?php echo e($item['name']); ?>
                        </p>

                        <?php if (!empty($item['role'])): ?>
                          <p class="text-xs md:text-sm text-gray-500 mt-1 font-plus truncate">
                            <?php echo e($item['role']); ?>
                          </p>
                        <?php endif; ?>
                      </div>
                    </div>

                    <!-- Quote Line SVG -->
                    <div class="mb-3">
                      <svg class="xl:w-[290px] md:w-[230px] w-full" height="40" viewBox="0 0 290 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" fill="url(#pattern_<?php echo $uid; ?>)" />
                        <line x1="53" y1="19" x2="289" y2="19" stroke="#2C2C2C" stroke-width="2" stroke-linecap="round" />

                        <defs>
                          <pattern id="pattern_<?php echo $uid; ?>" patternContentUnits="objectBoundingBox" width="1" height="1">
                            <use xlink:href="#image_<?php echo $uid; ?>" transform="scale(0.01)" />
                          </pattern>

                          <image id="image_<?php echo $uid; ?>" width="100" height="100" preserveAspectRatio="none"
                            xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEB0lEQVR4nO3dS6hXVRTH8e8tJR9QFpImFYWJSJOEIhAxI02LMsxE0IEDc+ArGgQ6EK2GRRJogmhQhA6yQY0iCKQgUMwiTAQRAxNFC/KdPW47NuxBO+J69r7nnLWWrQ+csb/ff92/573/4JxzzjnnnHPOOeeccz26GZgPvAnsBj5seXsHWAHc3kH2O4AX07/Rdu7d6TOZB9xETx4BvgdCD9t5YE2L2V8CLvSU/TDwMB2bDVztqVD4x/ZGC9nfEsgdP6vH6Mg44JxAqZC2BcPIvlAw91ngNjqwSbBUAL4bRvbDwtk30oFvhUsFYHJF7ikKch/qYB5cVlBsfkXupxXkvtTBPMRLBWBxRe7FCnLHrXXShYIPJCddKPhActKFgg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJCddKPhActKFgg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJCddKPhActKFgg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJCddKPhActKFgg8kJ10o+EBy0oWCDyQnXSj4QHLShYIPJPeX0YEsulEH8pPRgcy+UQfyqdGBjAN+V5C9dUuMDiT6SEH21g0A+4wOZEqP7xb2NpBoPHDQ4ECiOcJD6czo9Hrbz8YGEj0A7BXap/Tyrnp87fe59EGVbmcrSj3fUvb4/vusytwbKnIPYsCpimLzpEMDMypyX8SAmlesZ0qHrjyvOYMB5yuKTZcODTxZkfs4BtSsCHG/dGjgmYrc8chUvT8LS/0BjJQOnQ4sSgcSj+xUu7Wi1A/osEJorZZOTa4otQ8d1ldkX41yNYeOb6PDFqNHh62vzrMMHfZU7PvGoNwrPS0804X9hbm/wYDtFSdWA9g8od2KAZ8VlnoXu0eHT2HAycJSL6DDo4W5r6Sr46pNKCz1a/rL1GBNYfaPMaD00sMe9HivMHs8mlTv1cJSc9GjZHnceAPvFgz4vKDUiT4XJG5w+3qwIHtcUFm9scC1glJr0WNpQe54i/heDHi28Cs/Fj3eL8ge9zUmbCsotR49BtLJadNLJVMxYARwumGpU8qu/zxe8IcUr0KYsKCg1HJ0+aBh7vjc10SM+KRhqS8VXbcirdt+pWH2dRhxV/q/tclZ+TR0Wd1wGAfSs2omNL2pswpdRqYnRposJW5iRx5NaviEyV70afrtiOcoZuxoUOh4V7+xMQyjgB8bZI8/nWTGfQ0ebI6PWT6EPi83GMZXaXBmvH6dQr8BT6DT0etkP5J+YMyUXUMUGkxvZWl1YYjs8QbbPRi0cohvhuZhDHWb+ZiSR1qrDxu/+I99RnybSbsHgV/+lf1r4E6MG5F+yHEn8BpwN3ZMAjan7Cut3HByzjnnnHPOOeecc87x//M3gbACcetr2BAAAAAASUVORK5CYII=" />
                        </defs>
                      </svg>
                    </div>

                    <!-- Content -->
                    <p class="text-gray-600 leading-relaxed text-sm md:text-base font-plus line-clamp-4">
                      <?php echo $item['content']; ?>
                    </p>

                  </div>
                </div>
              <?php endforeach; ?>

            </div>
          </div>
          <button type="button" onclick="next()" aria-label="<?php echo e(__('home_loved.aria_next')); ?>"
            class="hidden lg:inline-flex flex-shrink-0 items-center justify-center
              w-10 h-10 rounded-full border border-gray-200 bg-white shadow-sm
              text-home-body hover:bg-gray-50 hover:border-gray-300 active:scale-95
              focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2
              transition-all duration-200 z-10">
            <svg class="w-5 h-5" viewBox="0 0 48 48" fill="none" aria-hidden="true">
              <path d="M30.8 24L18.8 36L16 33.2L25.2 24L16 14.8L18.8 12L30.8 24Z" fill="currentColor" />
            </svg>
          </button>
        </div>

        <!-- Dots - Desktop only -->
        <div class="hidden sm:flex justify-center gap-1 mt-6 sm:mt-8 lg:mt-10 items-center">
          <?php for ($i = 0; $i < $desktopDots; $i++): ?>
            <button type="button" onclick="goTo(<?php echo $i; ?>)"
              class="dot inline-flex items-center justify-center p-0 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2"
              aria-label="<?php echo e(__('home_loved.aria_goto_slide', (string) ($i + 1), (string) $desktopDots)); ?>"
              <?php echo $i === 0 ? 'aria-current="true"' : ''; ?>>
              <span class="dot-indicator block w-2 h-2 rounded-full <?php echo $i === 0 ? 'bg-blue-500' : 'bg-gray-300'; ?> transition-colors" aria-hidden="true"></span>
            </button>
          <?php endfor; ?>
        </div>
    </div>
  <?php else: ?>
  </div>
<?php endif; ?>
</section>
<script>
  let autoSlideInterval;

  function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
      nextMobile(); // gọi hàm bạn đã có
    }, 3000);
  }

  function stopAutoSlide() {
    clearInterval(autoSlideInterval);
  }

  // chạy khi load
  startAutoSlide();
</script>