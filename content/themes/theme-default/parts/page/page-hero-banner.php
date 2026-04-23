<?php
/**
 * Banner trang CMS — nền giống review-cms, fade xuống nội dung trắng mượt (không vạch ngang).
 */
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('CMS', defined('APP_LANG') ? APP_LANG : '');

$page = isset($page) && is_array($page) ? $page : [];
$pageHeroTitle = trim((string) ($page['title'] ?? $page['post_title'] ?? ''));
if ($pageHeroTitle === '') {
    $pageHeroTitle = __('page.banner_untitled');
}
$homeHref = base_url('', defined('APP_LANG') ? APP_LANG : '');
$pageHeroSubtitle = trim((string) ($page['description'] ?? $page['excerpt'] ?? ''));
?>
<section class="relative w-full overflow-hidden bg-white" id="page-cms-banner"
    aria-labelledby="page-cms-banner-title">
    <!-- Nền: cùng tông xanh nhạt dưới ảnh để không lộ mép trắng / xám lệch -->
    <div class="pointer-events-none absolute inset-0 z-0 bg-[#eef3fb]" aria-hidden="true"></div>
    <div class="absolute inset-0 z-0 overflow-hidden">
        <img src="<?php echo e(theme_assets('images/banner_cms.webp')); ?>" alt=""
            class="h-full min-h-full w-full object-cover object-center" />
        <div class="absolute -translate-x-1/2 rounded-full bg-[#2377FD80]/10 blur-[250px] sm:h-[550px] sm:w-full"
            style="left: 100%; top: 0;"></div>
        <div class="absolute -translate-x-1/2 rounded-full bg-[#63ECFF80]/15 blur-[200px] sm:h-[550px] sm:w-full"
            style="left: 0; top: 100px;"></div>
        <div class="absolute left-1/2 top-0 h-[800px] w-[800px] max-w-[1366px] -translate-x-1/2 rounded-full bg-[#63ECFF]/20 blur-[100px] sm:h-[1366px] sm:w-[1366px]"
            style="opacity: 0.35;"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.35]"
            style="background-image: linear-gradient(rgba(35,119,253,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(35,119,253,0.06) 1px, transparent 1px); background-size: 48px 48px;">
        </div>
        <!-- Một lớp: dọc (xuống trắng) + nhẹ ngang (mềm mé), tránh band ngang -->
        <div class="pointer-events-none absolute inset-0 z-[2]"
            style="background-image:
              linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0) 36%, rgba(255,255,255,0.14) 58%, rgba(255,255,255,0.52) 78%, rgba(255,255,255,0.9) 92%, #ffffff 100%),
              linear-gradient(90deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 14%, rgba(255,255,255,0) 86%, rgba(255,255,255,0.12) 100%);"
            aria-hidden="true"></div>
    </div>

    <div class="relative z-10 container mx-auto flex min-h-[400px] flex-col px-4 pb-16 pt-6 sm:min-h-[460px] sm:px-6 sm:pb-20 sm:pt-8 md:min-h-[500px] md:pb-24">
        <div
            class="mx-auto flex w-full max-w-3xl flex-1 flex-col items-center justify-center py-10 text-center sm:py-14 md:py-16">
            <h1 id="page-cms-banner-title"
                class="sr sr--fade-up mb-4 w-full font-space text-[34px] font-bold leading-tight text-black sm:text-4xl md:text-[56px] md:leading-[1.1]" style="--sr-delay: 0ms">
                <?php echo e($pageHeroTitle); ?>
            </h1>
            <?php if ($pageHeroSubtitle !== '') { ?>
                <p class="sr sr--fade-up mx-auto max-w-xl font-plus text-sm leading-relaxed text-gray-600 sm:text-base md:text-lg" style="--sr-delay: 60ms">
                    <?php echo e($pageHeroSubtitle); ?>
                </p>
            <?php } ?>
        </div>
    </div>
</section>
