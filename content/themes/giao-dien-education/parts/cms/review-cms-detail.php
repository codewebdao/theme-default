<?php
/**
 * Chi tiết bài reviews: tiêu đề, logo, đánh giá, khối so sánh (hiệu năng + Pro/Cons), nội dung.
 *
 * @var array $review_post Row từ get_post (posttype reviews)
 */

use System\Libraries\Render\View;

if (class_exists(\App\Libraries\Fastlang::class, false)) {
    $cmsLang = defined('APP_LANG') ? APP_LANG : 'en';
    \App\Libraries\Fastlang::load('CMS', $cmsLang);
}

require_once __DIR__ . '/_cms_review_normalize.php';

$review_post = isset($review_post) && is_array($review_post) ? $review_post : [];
$card = cms_normalize_reviews_row($review_post);

$title = html_entity_decode((string) ($card['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$description = (string) ($card['description'] ?? '');
$label = trim((string) ($card['label'] ?? ''));
$feature = $card['feature'] ?? null;
$ratingDisplay = (string) ($card['rating_display'] ?? '0.0');
$contentHtml = (string) ($review_post['content'] ?? '');
$featureImage = $feature;

$createdRaw = (string) ($review_post['created_at'] ?? '');
$createdTs = strtotime($createdRaw);
$createdLabel = $createdTs ? date('M j, Y', $createdTs) : '';
$homeHref = base_url('', defined('APP_LANG') ? APP_LANG : '');

$archiveHref = base_url('reviews');
$archiveLabel = function_exists('__') ? (string) __('theme_nav.review_cms') : 'Review CMS';

$cmsT = static function (string $k, string $fb = ''): string {
    return function_exists('__') ? (string) __($k) : ($fb !== '' ? $fb : $k);
};

$link = (string) ($card['link'] ?? '#');
$linkDownloadName = trim((string) ($card['link_download_name'] ?? ''));
$linkAttrs = '';
if ($link !== '#' && $link !== '') {
    if ($linkDownloadName !== '') {
        $linkAttrs .= ' download="' . htmlspecialchars($linkDownloadName, ENT_QUOTES, 'UTF-8') . '"';
    } else {
        $linkAttrs .= ' download';
    }
}
if ((bool) preg_match('#^https?://#i', $link)) {
    $linkAttrs .= ' rel="noopener noreferrer"';
}
?>
<section class="pt-12">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="sm:mb-12 mb-6">
            <nav class="flex flex-wrap items-center justify-center gap-2 gap-y-1 text-sm text-gray-500 font-plus" aria-label="Breadcrumb">
                <a href="<?php echo e($homeHref); ?>" class="text-home-body text-sm font-normal leading-[22px] hover:text-home-primary transition-colors shrink-0">
                    <?php echo e(function_exists('__') ? __('listing_banner_home') : 'Home'); ?>
                </a>
                <span class="text-gray-400 shrink-0" aria-hidden="true">&gt;</span>
                <a href="<?php echo htmlspecialchars($archiveHref, ENT_QUOTES, 'UTF-8'); ?>" class="text-home-body text-sm font-normal leading-[22px] hover:text-home-primary transition-colors shrink-0">
                    <?php echo e($archiveLabel); ?>
                </a>
                <span class="text-gray-400 shrink-0" aria-hidden="true">&gt;</span>
                <span class="text-home-primary text-sm font-normal leading-[22px] text-center break-words" aria-current="page"><?php echo e($title); ?></span>
            </nav>
        </div>

        <!-- <?php if ($label !== ''): ?>
            <div class="text-center mb-3">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-home-md bg-[#FACC15]/90 text-[#2C2C2C] text-sm font-medium font-plus">
                    <?php echo e($label); ?>
                </span>
            </div>
        <?php endif; ?> -->

        <h1 class="w-full text-[30px] sm:text-[24px] lg:text-[32px] font-medium leading-normal sm:leading-[36px] lg:leading-[48px] text-center text-home-heading font-plus mb-3">
            <?php echo e($title); ?>
        </h1>

        <?php if ($description !== ''): ?>
            <p class="text-md text-gray-600 mb-6 line-clamp-2 text-center">
                <?php echo e($description); ?>
            </p>
        <?php endif; ?>

        <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-12 sm:mt-12 mt-6 text-sm text-gray-500">
            <div class="flex items-center gap-2">
                <span class="text-home-body"><?php echo e($cmsT('cms_comparison_rating', 'Rating')); ?></span>
                <span class="text-home-heading font-semibold"><?php echo e($ratingDisplay); ?></span>
            </div>
            <?php if ($createdLabel !== ''): ?>
                <div class="flex items-center gap-2">
                    <span><?php echo e($createdLabel); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($featureImage) && function_exists('_imglazy')): ?>
            <div class="relative w-full rounded-home-md overflow-hidden mt-6 sm:mt-12 aspect-[16/9] sm:aspect-[16/6]">
                <?php echo _imglazy($featureImage, [
                    'alt' => $title,
                    'class' => 'w-full h-full object-cover rounded-home-md',
                    'loading' => 'eager',
                    'fetchpriority' => 'high',
                    'sizes' => [
                        'mobile' => 'thumbnail',
                        'tablet' => 'medium',
                        'desktop' => 'large',
                        'large' => 'large',
                    ],
                ]); ?>
            </div>
        <?php endif; ?>

        <div class="w-full review-cms-detail-main-inner py-12 font-plus">
            <?php echo View::include('parts/cms/_cms_review_compare_block', ['card' => $card]); ?>

            <?php if (trim($contentHtml) !== ''): ?>
                <div class="review-cms-detail-body blog-post-body text-[16px] leading-[24px] font-normal text-home-body font-plus border-t border-gray-200 pt-10 sm:pt-12
                    [&_h1]:text-[28px] [&_h1]:sm:text-[32px] [&_h1]:font-semibold [&_h1]:text-home-heading [&_h1]:mb-4 [&_h1]:mt-10 [&_h1]:font-plus
                    [&_h2]:text-[24px] [&_h2]:sm:text-[26px] [&_h2]:font-semibold [&_h2]:text-home-heading [&_h2]:mb-3 [&_h2]:mt-10 [&_h2]:font-plus
                    [&_h3]:text-[22px] [&_h3]:sm:text-[24px] [&_h3]:font-semibold [&_h3]:text-home-heading [&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:font-plus
                    [&_p]:mb-4 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-4 [&_ol]:list-decimal [&_ol]:ps-6 [&_ol]:mb-4
                    [&_a]:text-home-primary [&_a]:underline [&_img]:rounded-home-md [&_img]:max-w-full [&_figure]:my-6 [&_pre]:overflow-x-auto">
                    <?php echo $contentHtml; ?>
                </div>
            <?php endif; ?>

            <?php if ($link !== '#' && $link !== ''): ?>
                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 p-4 sm:p-5 rounded-home-xl bg-[#F3F4F6] border border-gray-100">
                    <?php if (trim((string) ($card['performance_user'] ?? '')) !== ''): ?>
                        <p class="text-sm text-gray-600 font-plus">
                            <?php echo e((string) $card['performance_user']); ?>
                        </p>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <a href="<?php echo e($link); ?>"
                        class="inline-flex items-center gap-2 text-home-primary text-sm font-semibold hover:opacity-90 transition-opacity font-plus"
                        <?php echo $linkAttrs; ?>>
                        <?php echo e($cmsT('Try on Laragon', 'Try on Laragon')); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
