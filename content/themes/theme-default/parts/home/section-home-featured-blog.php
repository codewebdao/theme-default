<?php
$lang = defined('APP_LANG') ? APP_LANG : '';

if (class_exists(\App\Libraries\Fastlang::class)) {
    \App\Libraries\Fastlang::load('Home', $lang !== '' ? $lang : 'en');
}
if (!function_exists('__')) {
    load_helpers(['languages']);
}

/** Fallback image when a post has no featured image */
$__imgCard = $__imgCard ?? theme_assets('images/banner_cms.webp');

/**
 * Pass $__featured from View::include(..., ['__featured' => ...]) to override.
 * Otherwise: load the 3 latest blog posts via get_posts.
 */
if (!isset($__featured) || !is_array($__featured)) {
    $__featured = [];
    if (function_exists('get_posts')) {
        $res = get_posts([
            'posttype'        => 'blogs',
            'perPage'         => 3,
            'post_status'     => 'active',
            'lang'            => $lang,
            'orderby'         => 'created_at',
            'order'           => 'DESC',
            'with_categories' => true,
        ]) ?: [];
        $rows = $res['data'] ?? [];
        if (!is_array($rows)) {
            $rows = [];
        }

        $firstCat = static function (array $row): string {
            $cats = $row['categories'] ?? null;
            if ($cats === null || $cats === '') {
                return '';
            }
            if (is_string($cats)) {
                $dec = json_decode($cats, true);
                $cats = is_array($dec) ? $dec : [];
            }
            if (is_object($cats)) {
                $cats = method_exists($cats, 'toArray') ? $cats->toArray() : (array) $cats;
            }
            if (!is_array($cats) || !isset($cats[0])) {
                return '';
            }
            $c0 = $cats[0];
            if (is_object($c0)) {
                $c0 = method_exists($c0, 'toArray') ? $c0->toArray() : (array) $c0;
            }

            return is_array($c0) ? trim((string) ($c0['name'] ?? $c0['title'] ?? $c0['slug'] ?? '')) : '';
        };

        $dateFmt = ($lang === 'vi') ? 'd/m/Y' : 'M j, Y';
        $fmtDate = static function (array $row) use ($dateFmt): string {
            $ca = $row['created_at'] ?? $row['updated_at'] ?? '';
            if ($ca === null || $ca === '') {
                return '';
            }
            if (is_numeric($ca)) {
                return date($dateFmt, (int) $ca);
            }
            $t = strtotime((string) $ca);

            return $t ? date($dateFmt, $t) : '';
        };

        $editorialLabel = function_exists('__') ? (string) __('home_featured_blog.meta_editorial') : 'Editorial';

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = trim((string) ($row['slug'] ?? ''));
            $url = ($slug !== '' && function_exists('link_posts'))
                ? (string) link_posts($slug, 'blog', $lang)
                : base_url('blog', $lang);

            $cat = $firstCat($row);
            $d = $fmtDate($row);
            $metaParts = [$editorialLabel];
            if ($d !== '') {
                $metaParts[] = $d;
            }
            if ($cat !== '') {
                $metaParts[] = $cat;
            }

            $__featured[] = [
                'title'   => (string) ($row['title'] ?? ''),
                'meta'    => implode(' · ', $metaParts),
                'url'     => $url,
                'feature' => $row['feature'] ?? null,
            ];
        }
    }
}

$__featured_count = count($__featured);
$__blog_archive_url = base_url('blog', $lang !== '' ? $lang : (defined('APP_LANG') ? APP_LANG : ''));
?>
<section class="relative bg-home-surface pb-16 pt-10 sm:pb-20 sm:pt-14" aria-labelledby="home-featured-blog-heading">
  <div class="container">
    <div class="mb-10 flex flex-col items-center justify-between gap-4 text-center sm:mb-12 sm:flex-row sm:text-left">
      <div>
        <h2 id="home-featured-blog-heading" class="font-space text-2xl font-bold text-home-heading sm:text-3xl">
          <?php echo e(__('banner_home.section_featured_title')); ?>
        </h2>
        <p class="mt-1 font-plus text-sm text-home-body sm:text-base">
          <?php echo e($__featured_count > 0
            ? __('home_featured_blog.intro_has_posts')
            : __('home_featured_blog.intro_empty')); ?>
        </p>
      </div>
      <a
        href="<?php echo e($__blog_archive_url); ?>"
        class="inline-flex shrink-0 items-center gap-1.5 rounded-home-lg border border-home-border/60 bg-white px-4 py-2.5 text-sm font-semibold text-home-primary shadow-sm ring-1 ring-black/5 transition hover:border-home-primary/40 hover:bg-home-surface-light hover:text-home-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2 focus-visible:ring-offset-home-white dark:border-home-border dark:bg-home-surface-light dark:text-home-primary dark:ring-white/10 dark:hover:border-home-primary/50 dark:hover:bg-home-surface dark:hover:text-home-primary-hover dark:focus-visible:ring-offset-home-white font-plus sm:px-5 sm:py-3 sm:text-base">
        <?php echo e(__('home_featured_blog.view_more')); ?>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="shrink-0" aria-hidden="true">
          <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </a>
    </div>

    <?php if ($__featured_count === 0) { ?>
      <p class="rounded-home-lg border border-dashed border-home-border/60 bg-white/60 py-10 text-center font-plus text-sm text-home-body dark:border-home-border/70 dark:bg-home-surface-light/80 dark:text-home-body">
        <?php echo e(__('home_featured_blog.empty')); ?>
      </p>
    <?php } else { ?>
    <div class="-mx-4 px-4 md:mx-0 md:px-0">
      <div
        class="flex snap-x snap-mandatory gap-4 overflow-x-auto overflow-y-visible pb-3 [-ms-overflow-style:none] [scrollbar-width:none] scroll-smooth overscroll-x-contain touch-pan-x md:snap-none md:grid md:grid-cols-2 md:gap-6 md:overflow-visible md:pb-0 lg:grid-cols-3 lg:gap-8 [&::-webkit-scrollbar]:hidden"
        style="scroll-padding-inline: 1rem"
        role="list"
        aria-label="<?php echo e(__('home_featured_blog.list_aria_label')); ?>">
      <?php foreach ($__featured as $row) {
          $url = (string) ($row['url'] ?? base_url('blog', $lang));
          $title = (string) ($row['title'] ?? '');
          $featureHtml = '';
          if (!empty($row['feature']) && function_exists('_imglazy')) {
              $featureHtml = (string) _imglazy($row['feature'], [
                  'alt'     => $title,
                  'title'   => $title,
                  'class'   => 'h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]',
                  'loading' => 'lazy',
              ]);
          }
      ?>
        <article class="group flex w-[min(82vw,20rem)] shrink-0 snap-start flex-col overflow-hidden rounded-home-xl border border-home-border/50 bg-white shadow-sm ring-1 ring-black/5 transition hover:shadow-md dark:border-home-border/60 dark:bg-home-surface-light dark:shadow-none dark:ring-white/10 dark:hover:border-home-border dark:hover:shadow-lg dark:hover:shadow-black/25 md:w-auto md:min-w-0 md:snap-normal" role="listitem">
          <a href="<?php echo e($url); ?>" class="flex min-h-0 flex-1 flex-col no-underline text-inherit outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-home-white">
            <div class="aspect-[16/10] overflow-hidden bg-home-surface">
              <?php if ($featureHtml !== '') { ?>
                <?php echo $featureHtml; ?>
              <?php } else { ?>
                <img
                  src="<?php echo e($__imgCard); ?>"
                  alt=""
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]"
                  loading="lazy"
                  decoding="async" />
              <?php } ?>
            </div>
            <div class="flex w-full flex-1 flex-col items-start p-5 sm:p-6">
              <span class="mb-2 inline-flex w-fit shrink-0 rounded bg-home-primary/10 py-0.5 pl-0 pr-2 text-[10px] font-bold uppercase tracking-wide text-home-primary font-plus">
                <?php echo e(__('home_featured_blog.badge_new')); ?>
              </span>
              <h3 class="font-space text-lg font-bold leading-snug text-home-heading">
                <?php echo e($title); ?>
              </h3>
              <p class="mt-3 font-plus text-sm text-home-body/90">
                <?php echo e((string) ($row['meta'] ?? '')); ?>
              </p>
            </div>
          </a>
        </article>
      <?php } ?>
      </div>
    </div>
    <?php } ?>
  </div>
</section>
