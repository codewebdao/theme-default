<?php
/**
 * Khối Hiệu năng + Pro / Cons (cùng markup với phần giữa _cms_comparison_card.php).
 *
 * @var array $card Kết quả cms_normalize_reviews_row()
 */
$card = $card ?? [];
$perf = (int) ($card['performance'] ?? 0);
$perfLabel = (string) ($card['performance_label'] ?? ($perf . '/100'));
$proItems = is_array($card['pro_items'] ?? null) ? $card['pro_items'] : [];
$consItems = is_array($card['cons_items'] ?? null) ? $card['cons_items'] : [];
$perfClamped = (int) min(100, max(0, $perf));

$cmsT = static function (string $k, string $fb = ''): string {
    return function_exists('__') ? (string) __($k) : ($fb !== '' ? $fb : $k);
};
?>
<div class="w-full mb-10 sm:mb-12">
    <div class="review-cms-performance-card mb-8 w-full self-stretch font-plus" role="group" aria-label="<?php echo e($cmsT('cms_comparison_performance', 'Performance')); ?> <?php echo e($perfLabel); ?>">
        <div class="review-cms-performance-card__top">
            <span class="review-cms-performance-card__title text-sm font-semibold font-plus">
                <?php echo e($cmsT('cms_comparison_performance', 'Performance')); ?>
            </span>
            <span class="review-cms-performance-card__score-wrap">
                <span class="review-cms-performance-card__score" data-score="<?php echo (int) $perfClamped; ?>"><?php echo e($perfLabel); ?></span>
            </span>
        </div>
        <div class="review-cms-performance-card__tube" aria-hidden="true">
            <div class="review-cms-performance-card__tube-channel">
                <div
                    class="review-cms-performance-card__fill"
                    style="--review-perf-pct: <?php echo $perfClamped; ?>;"
                ></div>
            </div>
        </div>
        <div class="review-cms-performance-card__ticks" aria-hidden="true">
            <span>0</span><span>25</span><span>50</span><span>75</span><span>100</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 w-full review-cms-pros-cons">
        <div class="review-cms-pros-cons__col review-cms-pros-cons__col--pros">
            <div class="review-cms-pros-cons__head flex items-center gap-3">
                <span class="review-cms-pros-cons__icon review-cms-pros-cons__icon--pros inline-flex items-center justify-center shrink-0" aria-hidden="true">
                    <svg class="text-home-success" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="review-cms-pros-cons__title review-cms-pros-cons__title--pros text-base font-bold font-plus"><?php echo e($cmsT('cms_comparison_pro', 'Pro')); ?></span>
            </div>
            <ul class="review-cms-pros-cons__list">
                <?php foreach ($proItems as $line): ?>
                <li class="review-cms-pros-cons__item review-cms-pros-cons__item--pros font-plus">
                    <span class="review-cms-pros-cons__item-text"><?php echo e((string) $line); ?></span>
                </li>
                <?php endforeach; ?>
                <?php if ($proItems === []): ?>
                <li class="review-cms-pros-cons__item review-cms-pros-cons__item--empty font-plus">—</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="review-cms-pros-cons__col review-cms-pros-cons__col--cons">
            <div class="review-cms-pros-cons__head flex items-center gap-3">
                <span class="review-cms-pros-cons__icon review-cms-pros-cons__icon--cons inline-flex items-center justify-center shrink-0" aria-hidden="true">
                    <svg class="text-[#ED661D]" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="review-cms-pros-cons__title review-cms-pros-cons__title--cons text-base font-bold font-plus"><?php echo e($cmsT('cms_comparison_cons', 'Cons')); ?></span>
            </div>
            <ul class="review-cms-pros-cons__list">
                <?php foreach ($consItems as $line): ?>
                <li class="review-cms-pros-cons__item review-cms-pros-cons__item--cons font-plus">
                    <span class="review-cms-pros-cons__item-text"><?php echo e((string) $line); ?></span>
                </li>
                <?php endforeach; ?>
                <?php if ($consItems === []): ?>
                <li class="review-cms-pros-cons__item review-cms-pros-cons__item--empty font-plus">—</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
