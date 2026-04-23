<?php
$bannerGithubUrl = 'https://github.com/leokhoa/laragon';
if (function_exists('option')) {
    $__vg = trim((string) option('view_github', defined('APP_LANG') ? APP_LANG : ''));
    if ($__vg !== '') {
        $bannerGithubUrl = $__vg;
    }
}
if ($bannerGithubUrl !== '' && !preg_match('#^https?://#i', $bannerGithubUrl)) {
    $bannerGithubUrl = 'https://' . ltrim($bannerGithubUrl, '/');
}
?>
<section class="relative overflow-hidden flex items-center">

  <!-- Background -->
  <div class="absolute inset-0">
    <img src="<?php echo e(theme_assets('images/topbar.webp')); ?>" alt=""
      class="w-full h-full object-cover object-top"
      fetchpriority="high"
      decoding="async"
      loading="eager" />

    <!-- overlay -->
    <div class="absolute inset-0 bg-white/70"></div>
  </div>

  <!-- Content -->
  <div class="relative container mx-auto sm:py-24 py-12 text-center">
    <!-- Badge -->
    <div class="sr sr--fade-up inline-flex items-center 
        gap-2 sm:gap-3 
        bg-home-surface-light text-home-primary 
        text-[10px] sm:text-xs md:text-sm 
        px-3 sm:px-4 py-1.5 sm:py-2 
        rounded-lg mb-6" style="--sr-delay: 0ms">

      <!-- icon 1 -->
      <svg width="14" height="14" class="sm:w-[17px] sm:h-[17px]" viewBox="0 0 17 17" fill="none">
        <circle cx="8" cy="8" r="7" fill="var(--home-primary)" fill-opacity="0.1" />
        <circle cx="8" cy="8" r="3" fill="var(--home-primary)" />
      </svg>

      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g clip-path="url(#clip0_47_408)">
          <path
            d="M4.12543 9.61481L3.72099 10.0193C3.5279 10.2123 3.5279 10.5252 3.72099 10.718L5.28173 12.2788C5.47481 12.4718 5.78765 12.4718 5.98049 12.2788L6.38494 11.8743L4.12518 9.61456L4.12543 9.61481Z"
            fill="#3966A5" />
          <path
            d="M15.9999 0C10.9718 0.617037 6.31718 5.58642 6.31718 5.58642L6.31471 5.58395L3.20459 8.69407L7.30558 12.7951L10.4157 9.68494L10.4132 9.68247C10.4132 9.68247 15.3829 5.02815 15.9999 0Z"
            fill="#C4C2C2" />
          <path
            d="M6.31506 5.58394C6.31506 5.58394 4.01778 4.15012 1.33926 6.82864L0 8.16789L0.606667 8.77456C0.606667 8.77456 2.68074 6.88345 3.84815 8.05086L6.31506 5.58394Z"
            fill="#C72B2C" />
          <path
            d="M10.416 9.68494C10.416 9.68494 11.8498 11.9822 9.17127 14.6607L7.83201 16L7.22534 15.3933C7.22534 15.3933 9.11645 13.3193 7.94905 12.1519L10.416 9.68494Z"
            fill="#C72B2C" />
          <path
            d="M10.0047 7.9368C11.077 7.9368 11.9462 7.06757 11.9462 5.99531C11.9462 4.92306 11.077 4.05383 10.0047 4.05383C8.93246 4.05383 8.06323 4.92306 8.06323 5.99531C8.06323 7.06757 8.93246 7.9368 10.0047 7.9368Z"
            fill="#ECECEC" />
          <path
            d="M10.0046 7.46964C10.8188 7.46964 11.4789 6.80956 11.4789 5.99532C11.4789 5.18107 10.8188 4.521 10.0046 4.521C9.19035 4.521 8.53027 5.18107 8.53027 5.99532C8.53027 6.80956 9.19035 7.46964 10.0046 7.46964Z"
            fill="#3966A5" />
          <path d="M5.14539 6.75348L4.69092 7.20795L8.79196 11.309L9.24643 10.8545L5.14539 6.75348Z" fill="#ECECEC" />
          <path
            d="M5.28178 12.279L4.50129 11.4985L3.7208 10.718C3.7208 10.718 1.34277 11.4951 1.4608 14.539C4.50475 14.657 5.28178 12.279 5.28178 12.279Z"
            fill="#F2CB30" />
          <path
            d="M5.02349 12.021L4.50127 11.4988L3.97905 10.9765C3.97905 10.9765 2.38769 11.4965 2.46645 13.5336C4.50349 13.6126 5.02349 12.021 5.02349 12.021Z"
            fill="#D66D2E" />
          <path
            d="M13.868 5.42173C14.8705 3.8358 15.7613 1.94617 16.0001 0C14.0539 0.238765 12.1643 1.12988 10.5784 2.1321L13.8678 5.42148L13.868 5.42173Z"
            fill="#C72B2C" />
          <path
            d="M10.416 9.68493L10.4135 9.68246C10.4135 9.68246 11.336 8.81802 12.4352 7.43283C8.24586 9.95011 5.27376 9.71728 3.85376 9.3432L7.30586 12.7953L10.416 9.68518V9.68493Z"
            fill="#B8B7B5" />
          <path
            d="M6.97364 9.49062L8.79191 11.3089L9.24623 10.8546L7.74993 9.35828C7.4808 9.41408 7.22178 9.45778 6.97339 9.49062H6.97364Z"
            fill="#DBDAD8" />
          <path
            d="M11.0352 2.21927C11.0352 2.21927 13.7008 0.44939 15.6668 0.333588C15.6668 0.333588 13.2307 1.15606 11.471 2.65507L11.0352 2.21927Z"
            fill="#CE3E3D" />
          <path
            d="M11.3917 6.4953C10.7011 6.78592 9.62502 7.15283 8.73169 6.73925C8.79416 6.84592 8.87095 6.94641 8.96231 7.03777C9.53811 7.61357 10.4714 7.61357 11.0472 7.03777C11.2058 6.87925 11.3206 6.69333 11.3917 6.49555V6.4953Z"
            fill="#2D5585" />
          <path
            d="M5.2816 12.279C5.47469 12.4721 5.78752 12.4721 5.98036 12.279L6.38481 11.8746C6.38481 11.8746 5.12086 11.7785 3.62012 10.5721C3.64407 10.6252 3.67715 10.6748 3.72061 10.7185L5.28135 12.2793L5.2816 12.279Z"
            fill="#2D5585" />
          <path
            d="M9.17127 14.6607C9.26361 14.5684 9.35077 14.4766 9.43349 14.3852C8.7004 14.7973 7.95942 15.0701 7.34905 15.2479C7.27324 15.3407 7.22534 15.3933 7.22534 15.3933L7.83201 16L9.17127 14.6607Z"
            fill="#B8252B" />
        </g>
        <defs>
          <clipPath id="clip0_47_408">
            <rect width="16" height="16" fill="white" />
          </clipPath>
        </defs>
      </svg>

      <span class="font-medium font-semibold font-plus"><?php echo e(__('banner_home.badge')); ?></span>
    </div>


    <!-- Heading -->
    <h1 class="sr sr--fade-up text-[40px] sm:text-5xl md:text-[64px] font-bold text-black text-center mb-3 sm:mb-4 leading-[60px] lg:leading-[80px] font-space" style="--sr-delay: 50ms">
      <?php echo e(__('banner_home.title_line1')); ?>
      <br />
      <span class="bg-clip-text text-transparent font-space" style="background: linear-gradient(89deg, var(--home-accent) 3.4%, var(--home-primary) 58.72%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        <?php echo e(__('banner_home.title_line2')); ?>
      </span>
    </h1>
    <!-- Description -->
    <p class="sr sr--fade-up mt-6 text-gray-600 text-lg md:text-xl max-w-4xl mx-auto font-plus" style="--sr-delay: 100ms">
      <?php echo e(__('banner_home.description')); ?>
    </p>

    <!-- Buttons: banner-home-ctas — CSS fallback vì sm:w-auto / min-h-[44px] có thể bị PurgeCSS gỡ -->
    <div class="sr sr--fade-up banner-home-ctas mt-8 flex flex-col sm:flex-row justify-center gap-3" style="--sr-delay: 150ms">
      <a href="<?php echo e(base_url('download')); ?>"
        class="inline-flex min-h-[44px] w-full sm:w-auto items-center justify-center gap-2 bg-home-primary hover:bg-home-primary-hover text-white px-6 py-3 rounded-xl font-medium transition font-plus no-underline">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>


        <?php echo e(__('banner_home.button_download')); ?>
      </a>

      <a href="<?php echo e($bannerGithubUrl); ?>" target="_blank" rel="noopener noreferrer"
        class="inline-flex min-h-[44px] w-full sm:w-auto items-center justify-center border font-plus bg-home-surface border-gray-300 hover:bg-gray-100 px-6 py-3 rounded-home-lg font-medium transition hover:bg-gray-100 hover:border-home-primary text-home-heading no-underline">
        <svg class="mr-2 flex-shrink-0" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path
            d="M7 21H17M15.033 9.44C15.1312 9.49683 15.2127 9.57849 15.2694 9.67678C15.3261 9.77507 15.3559 9.88654 15.3559 10C15.3559 10.1135 15.3261 10.2249 15.2694 10.3232C15.2127 10.4215 15.1312 10.5032 15.033 10.56L10.968 12.912C10.8698 12.9688 10.7584 12.9987 10.645 12.9987C10.5316 12.9987 10.4202 12.9688 10.322 12.912C10.2238 12.8552 10.1424 12.7735 10.0858 12.6752C10.0293 12.5769 9.9997 12.4654 10 12.352V7.648C9.9998 7.53473 10.0294 7.42341 10.0859 7.32523C10.1424 7.22705 10.2237 7.14548 10.3218 7.08871C10.4198 7.03195 10.531 7.002 10.6443 7.00187C10.7576 7.00175 10.8689 7.03145 10.967 7.088L15.033 9.44ZM4 3H20C21.1046 3 22 3.89543 22 5V15C22 16.1046 21.1046 17 20 17H4C2.89543 17 2 16.1046 2 15V5C2 3.89543 2.89543 3 4 3Z"
            stroke="var(--home-heading)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php echo e(__('banner_home.button_view_on_github')); ?>
      </a>
    </div>
    <div class="sr sr--fade-up flex justify-center items-center py-5" style="--sr-delay: 200ms">
      <div class="flex justify-center font-plus items-center text-[#97A4B2] mr-3 sm:mr-5 text-[12px] sm:text-[14px] font-medium leading-[18px] sm:leading-[21px]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <svg width="12" height="12" class="mr-1.5 sm:mr-2 sm:w-4 sm:h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="8" cy="8" r="8" fill="#97A4B2" />
          <path d="M12 5L6.5 10.5L4 8" stroke="var(--home-surface-light)" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php echo e(__('banner_home.tag_free')); ?>
      </div>
      <div class="flex justify-center font-plus items-center text-[#97A4B2] text-[12px] sm:text-[14px] font-medium leading-[18px] sm:leading-[21px]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <svg width="12" height="12" class="mr-1.5 sm:mr-2 sm:w-4 sm:h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="8" cy="8" r="8" fill="#97A4B2" />
          <path d="M12 5L6.5 10.5L4 8" stroke="var(--home-surface-light)" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php echo e(__('banner_home.tag_opensource')); ?>
      </div>
    </div>

    <!-- Screenshot -->
    <div class="sr sr--fade-up mt-8 sm:mt-12 flex justify-center px-0 sm:px-4" style="--sr-delay: 220ms">
      <div class="rounded-lg sm:rounded-xl shadow-xl bg-white p-2 sm:p-4 max-w-4xl w-full">
        <?php
        $__shot = function_exists('cmsfullform_theme_responsive_webp_img')
            ? cmsfullform_theme_responsive_webp_img('images/img1.webp', [400, 560, 720, 900], [
                'alt'               => '',
                'class'             => 'w-full rounded-md sm:rounded-lg',
                'sizes'             => '(max-width: 640px) 100vw, min(896px, calc(100vw - 3rem))',
                'loading'           => 'lazy',
                'fetchpriority'     => 'low',
                'mobile_webp_width' => 400,
                'mobile_webp_bp'    => 640,
            ])
            : '';
        echo $__shot !== '' ? $__shot : '<img src="' . e(theme_assets('images/img1.webp')) . '" alt="" class="w-full rounded-md sm:rounded-lg" width="1062" height="622" loading="lazy" decoding="async" fetchpriority="low" />';
        ?>
      </div>
    </div>
  </div>
</section>