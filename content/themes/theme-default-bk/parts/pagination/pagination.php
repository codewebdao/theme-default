<?php
/**
 * Pagination blog — Prev + dải số trang + Next (cần total_pages).
 * Khác parts/ui/pagination.php (chỉ Prev / trang hiện tại / Next, dùng admin + view_pagination).
 *
 * @var string $base_url        URL trang (không có ?page=)
 * @var int    $current_page
 * @var int    $total_pages
 * @var int    $show_pages      Số ô số trang tối đa (mặc định 5)
 * @var array  $query_params    Tham số giữ lại (vd: ['q' => '...'])
 */
$current_page = isset($current_page) ? max(1, (int) $current_page) : (isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1);
$total_pages = isset($total_pages) ? max(1, (int) $total_pages) : 1;
$base_url = isset($base_url) ? (string) $base_url : (string) base_url();
$show_pages = isset($show_pages) ? max(1, (int) $show_pages) : 5;
$query_params = isset($query_params) && is_array($query_params) ? $query_params : [];

$current_page = min($current_page, $total_pages);

$page_url = static function (int $n) use ($base_url, $query_params): string {
    $params = $query_params;
    if ($n > 1) {
        $params['page'] = $n;
    } else {
        unset($params['page']);
    }
    $params = array_filter(
        $params,
        static function ($v) {
            return $v !== '' && $v !== null;
        }
    );
    $qstr = http_build_query($params);

    return $base_url . ($qstr !== '' ? '?' . $qstr : '');
};

$half = intdiv($show_pages, 2);
$start = max(1, $current_page - $half);
$end = min($total_pages, $start + $show_pages - 1);
$start = max(1, $end - $show_pages + 1);
$pages_to_show = $total_pages > 0 ? range($start, $end) : [];

$link_class = 'px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-home-md transition-colors inline-flex items-center justify-center min-w-[2.25rem] ';
$link_normal = 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:text-home-primary';
$link_active = 'bg-home-primary text-white border border-home-primary';
$link_disabled = 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed pointer-events-none opacity-60';

$prev_disabled = $current_page <= 1;
$next_disabled = $current_page >= $total_pages;
?>
<?php if ($total_pages > 1): ?>
<div class="flex items-center justify-center gap-1 sm:gap-2 mt-8 sm:mt-12 mb-6 flex-wrap px-4 sm:px-0" role="navigation" aria-label="Pagination">
    <?php if ($prev_disabled): ?>
    <span class="<?php echo e($link_class . $link_disabled); ?>" aria-disabled="true" aria-label="Previous page">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </span>
    <?php else: ?>
    <a href="<?php echo e($page_url($current_page - 1)); ?>"
        class="<?php echo e($link_class . $link_normal); ?>"
        rel="prev"
        aria-label="Previous page">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <?php endif; ?>

    <?php foreach ($pages_to_show as $p) : ?>
    <a href="<?php echo e($page_url((int) $p)); ?>"
        class="<?php echo e($link_class . ($current_page === (int) $p ? $link_active : $link_normal)); ?>"
        <?php echo $current_page === (int) $p ? 'aria-current="page"' : ''; ?>>
        <?php echo (int) $p; ?>
    </a>
    <?php endforeach; ?>

    <?php if ($next_disabled): ?>
    <span class="<?php echo e($link_class . $link_disabled); ?>" aria-disabled="true" aria-label="Next page">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </span>
    <?php else: ?>
    <a href="<?php echo e($page_url($current_page + 1)); ?>"
        class="<?php echo e($link_class . $link_normal); ?>"
        rel="next"
        aria-label="Next page">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>
