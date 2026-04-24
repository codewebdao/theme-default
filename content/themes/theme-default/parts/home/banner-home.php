<?php
/**
 * Hero trang chủ: cao ~full viewport; trái nội dung, phải 3 cột ảnh nghiêng (giữ rotate --visual-left).
 */
if (class_exists(\App\Libraries\Fastlang::class)) {
    \App\Libraries\Fastlang::load('Home', defined('APP_LANG') ? APP_LANG : 'en');
}
if (!function_exists('__')) {
    load_helpers(['languages']);
}

$__ta = static function (string $rel): string {
    return function_exists('theme_assets') ? theme_assets($rel) : $rel;
};

/** Ảnh theme (nếu có file trong assets) — ghép sau ảnh remote trong $marqueeSrc */
$__localMarquee = [];
foreach ([
    'images/banner_cms.webp',
    'images/bannerFeatures.webp',
    'images/frame1.webp',
    'images/backblog1.webp',
    'images/backblog2.webp',
    'images/topbar.webp',
] as $__rel) {
    $__u = $__ta($__rel);
    if (is_string($__u) && $__u !== '') {
        $__localMarquee[] = $__u;
    }
}

/**
 * Ảnh chủ đề blog (Unsplash, crop vuông) — ưu tiên trước; local ghép sau. Chuỗi sau photo-… phải khớp URL gốc (sai là 404).
 * @var list<string>
 */
$__remoteMarquee = [
    'https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&w=640&h=640&fit=crop&q=82',
    'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&w=640&h=640&fit=crop&q=82',
];

/** Remote (blog) trước — tránh ảnh theme cũ che hết danh sách Unsplash */
$marqueeSrc = array_values(array_unique(array_merge($__remoteMarquee, $__localMarquee)));
if ($marqueeSrc === []) {
    $marqueeSrc = $__remoteMarquee;
}

$nImg = count($marqueeSrc);
$seqAt = static function (array $indices) use ($marqueeSrc, $nImg): array {
    $out = [];
    foreach ($indices as $i) {
        $out[] = $marqueeSrc[(int) $i % $nImg];
    }
    return $out;
};

/** @var list<string> */
$colA = $seqAt([0, 2, 4, 1]);
/** @var list<string> */
$colB = $seqAt([1, 3, 5, 2]);
/** @var list<string> */
$colC = $seqAt([2, 0, 3, 4]);

$__marqueeImgIdx = 0;
$renderMarqueeTiles = function (array $urls) use (&$__marqueeImgIdx): void {
    if ($urls === []) {
        return;
    }
    foreach ([false, true] as $hidden) {
        foreach ($urls as $src) {
            $__marqueeImgIdx++;
            $eager = (!$hidden && $__marqueeImgIdx <= 2);
            ?>
            <div class="banner-home-marquee__tile"<?php echo $hidden ? ' aria-hidden="true"' : ''; ?>>
              <img src="<?php echo e($src); ?>" alt="" width="360" height="360" loading="<?php echo $eager ? 'eager' : 'lazy'; ?>" decoding="async"<?php echo $eager ? ' fetchpriority="high"' : ''; ?> />
            </div>
            <?php
        }
    }
};
?>
<section class="relative flex min-h-[100dvh] flex-col overflow-hidden bg-home-white" aria-labelledby="banner-home-headline">
  <div class="container relative z-[1] mx-auto flex flex-1 flex-col justify-center py-12 sm:py-14 lg:min-h-0 lg:py-16">
    <div class="grid items-center gap-10 lg:min-h-[calc(100dvh-6.5rem)] lg:grid-cols-[11fr_20fr] lg:items-stretch lg:gap-8 lg:py-1 xl:gap-10">
      <!-- Nội dung: cột trái (desktop) — hẹp hơn -->
      <div class="w-full max-w-md max-lg:mx-auto lg:max-w-[26rem] lg:flex lg:flex-col lg:justify-center lg:pr-2 xl:max-w-[27rem] xl:pr-4">
        <p class="sr sr--fade-up mb-4 w-fit max-w-full self-start inline-flex items-center gap-2 rounded-full border border-home-border/40 bg-home-surface-light/80 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-home-body backdrop-blur-sm font-plus dark:border-home-border dark:bg-home-surface-light/30 dark:text-home-body" style="--sr-delay: 0ms">
          <?php echo e(__('banner_home.badge')); ?>
        </p>

        <h1 id="banner-home-headline" class="sr sr--fade-up font-space text-4xl font-bold leading-[1.15] tracking-tight text-home-heading sm:text-5xl lg:text-[2.75rem] xl:text-5xl" style="--sr-delay: 60ms">
          <span class="block sm:inline"><?php echo e(__('banner_home.hero_title_plain')); ?> </span>
          <span class="mt-1 block bg-gradient-to-r from-home-primary via-teal-500 to-violet-600 bg-clip-text text-transparent sm:mt-0 sm:inline"><?php echo e(__('banner_home.hero_title_em')); ?></span>
        </h1>
        <p class="sr sr--fade-up mt-3 font-plus text-lg text-home-body sm:text-xl" style="--sr-delay: 120ms">
          <?php echo e(__('banner_home.hero_sub')); ?>
        </p>
        <p class="sr sr--fade-up mt-4 font-plus text-base leading-relaxed text-home-body/90 sm:text-lg" style="--sr-delay: 180ms">
          <?php echo e(__('banner_home.description')); ?>
        </p>

        <div class="sr sr--fade-up banner-home-ctas mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap" style="--sr-delay: 240ms">
          <a href="<?php echo e(base_url('blog')); ?>"
            class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-home-lg bg-home-primary px-8 py-3 font-plus text-sm font-semibold text-white no-underline shadow-lg transition hover:bg-home-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2 sm:min-w-[200px]">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="shrink-0" aria-hidden="true">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <?php echo e(__('banner_home.button_download')); ?>
          </a>
          
        </div>

      </div>

      <!-- Cột ảnh: cột phải (desktop) — rộng hơn, cao bằng hàng hero -->
      <div class="relative mx-auto flex w-full min-w-0 max-w-lg min-h-0 lg:mx-0 lg:h-full lg:max-w-none lg:min-h-0 lg:w-full lg:justify-end">
        <div class="banner-home-marquee banner-home-marquee--visual-left banner-home-marquee--fill-section banner-home-marquee--vignette banner-home-marquee--edges-fade relative h-[min(24rem,52svh)] min-h-[20rem] w-full max-w-lg overflow-hidden rounded-[1.75rem] sm:rounded-[2rem] sm:h-[min(28rem,54svh)] sm:min-h-[22rem] lg:h-full lg:min-h-0 lg:max-w-[min(100%,56rem)] lg:w-full" aria-hidden="true">
          <div class="banner-home-marquee__stage" aria-hidden="true">
            <div class="banner-home-marquee__tilt">
              <div class="banner-home-marquee__col">
                <div class="banner-home-marquee__track banner-home-marquee__track--mid">
                  <?php $renderMarqueeTiles($colA); ?>
                </div>
              </div>
              <div class="banner-home-marquee__col">
                <div class="banner-home-marquee__track banner-home-marquee__track--down">
                  <?php $renderMarqueeTiles($colB); ?>
                </div>
              </div>
              <div class="banner-home-marquee__col">
                <div class="banner-home-marquee__track">
                  <?php $renderMarqueeTiles($colC); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
