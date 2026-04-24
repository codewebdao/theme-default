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

$__imgCard = static function () {
    return theme_assets('images/img1.webp');
};
$__imgAvatar = static function () {
    return theme_assets('images/img1.webp');
};

$__wavePatternId = 'banner_home_wave_' . substr(md5(__FILE__), 0, 8);

$__featured = [
    ['title' => __('banner_home.card_1_title'), 'meta' => __('banner_home.card_1_meta')],
    ['title' => __('banner_home.card_2_title'), 'meta' => __('banner_home.card_2_meta')],
    ['title' => __('banner_home.card_3_title'), 'meta' => __('banner_home.card_3_meta')],
];
?>
<section class="relative overflow-hidden bg-white pb-16 pt-12 sm:pb-20 sm:pt-16 lg:pb-24 lg:pt-20" aria-labelledby="banner-home-headline">

  <!-- Nền sóng nhẹ -->
  <div class="pointer-events-none absolute inset-0 text-home-heading/10" aria-hidden="true">
    <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <defs>
        <pattern id="<?php echo e($__wavePatternId); ?>" width="120" height="28" patternUnits="userSpaceOnUse">
          <path d="M0 14 Q30 2 60 14 T120 14" fill="none" stroke="currentColor" stroke-width="1.25" />
          <path d="M0 22 Q30 10 60 22 T120 22" fill="none" stroke="currentColor" stroke-width="0.75" opacity="0.6" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#<?php echo e($__wavePatternId); ?>)" />
    </svg>
  </div>

  <div class="relative container mx-auto max-w-6xl px-4">
    <!-- Hero: chữ trái + avatar vòng đỏ -->
    <div class="flex flex-col items-center gap-12 lg:flex-row lg:items-center lg:justify-between lg:gap-16">
      <div class="w-full max-w-xl text-center lg:max-w-lg lg:text-left">
        <p class="font-plus text-3xl font-bold tracking-tight text-home-heading sm:text-4xl">
          <?php echo e(__('banner_home.hero_greeting')); ?>
        </p>
        <p class="mt-2 font-plus text-lg text-home-body sm:text-xl">
          <?php echo e(__('banner_home.hero_sub')); ?>
        </p>
        <div class="mt-8 flex flex-col items-stretch gap-4 sm:mt-10 sm:flex-row sm:items-end sm:gap-6 lg:items-end">
          <h1 id="banner-home-headline" class="font-space text-4xl font-extrabold leading-tight tracking-tight text-home-heading sm:text-5xl lg:text-6xl">
            <?php echo e(__('banner_home.hero_headline')); ?>
          </h1>
          <span class="hidden min-h-[3px] flex-1 border-b-[3px] border-home-heading sm:mb-4 sm:block lg:min-w-[5rem]" aria-hidden="true"></span>
        </div>

        <div class="banner-home-ctas mt-8 flex flex-wrap items-center justify-center gap-4 font-plus text-sm font-medium lg:justify-start">
          <a href="<?php echo e(base_url('download')); ?>" class="text-home-primary underline decoration-2 underline-offset-4 transition hover:text-home-primary-hover">
            <?php echo e(__('banner_home.button_download')); ?>
          </a>
          <span class="text-home-border" aria-hidden="true">·</span>
          <a href="<?php echo e($bannerGithubUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-home-heading/80 underline decoration-home-border underline-offset-4 transition hover:text-home-primary">
            <?php echo e(__('banner_home.button_view_on_github')); ?>
          </a>
        </div>
      </div>

      <!-- Avatar trong vòng đỏ + sóng trắng -->
      <div class="relative flex shrink-0 items-center justify-center">
        <div class="relative flex h-[17rem] w-[17rem] items-center justify-center sm:h-[19rem] sm:w-[19rem]">
          <div class="absolute inset-0 overflow-hidden rounded-full bg-red-600 shadow-lg ring-4 ring-red-600/20" aria-hidden="true">
            <svg class="h-full w-full text-white/35" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 400 400">
              <defs>
                <pattern id="<?php echo e($__wavePatternId); ?>_inner" width="64" height="20" patternUnits="userSpaceOnUse" patternTransform="rotate(-8 200 200)">
                  <path d="M0 10 Q16 0 32 10 T64 10" fill="none" stroke="currentColor" stroke-width="3" />
                  <path d="M0 16 Q16 6 32 16 T64 16" fill="none" stroke="currentColor" stroke-width="2" opacity="0.7" />
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#<?php echo e($__wavePatternId); ?>_inner)" />
            </svg>
          </div>
          <img
            src="<?php echo e($__imgAvatar()); ?>"
            alt="<?php echo e(__('banner_home.hero_avatar_alt')); ?>"
            class="relative z-[1] h-52 w-52 rounded-full border-[5px] border-white object-cover shadow-2xl sm:h-56 sm:w-56"
            width="400"
            height="400"
            loading="eager"
            decoding="async"
            fetchpriority="high" />
        </div>
      </div>
    </div>

    <!-- Featured: 3 cột -->
    <div class="mt-16 grid grid-cols-1 gap-8 sm:mt-20 md:grid-cols-2 md:gap-7 lg:mt-24 lg:grid-cols-3 lg:gap-8">
      <?php foreach ($__featured as $row) { ?>
        <article class="group flex flex-col">
          <div class="relative aspect-[4/3] overflow-hidden rounded-home-lg bg-home-surface shadow-sm ring-1 ring-home-border/40">
            <img
              src="<?php echo e($__imgCard()); ?>"
              alt=""
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
              loading="lazy"
              decoding="async" />
            <span class="absolute right-3 top-3 rounded bg-home-heading px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm font-plus">
              <?php echo e(__('banner_home.featured_badge')); ?>
            </span>
          </div>
          <div class="relative z-[1] -mt-10 mx-3 rounded-home-lg border border-home-border/50 bg-white p-5 shadow-md sm:mx-4 sm:p-6">
            <h2 class="font-space text-lg font-bold leading-snug text-home-heading sm:text-xl">
              <?php echo e($row['title']); ?>
            </h2>
            <p class="mt-2 font-plus text-sm text-home-body/90">
              <?php echo e($row['meta']); ?>
            </p>
          </div>
        </article>
      <?php } ?>
    </div>
  </div>
</section>
