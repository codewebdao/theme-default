<div class="flex justify-center w-full">
    <ul class="pages-items flex flex-wrap gap-2 items-center">
    <?php if ($current_page > 1 ): ?>
        <li class="items">
            <a href="<?= $current_page == 2 ? $base_url . (!empty($query_params) ? '?' . $query_params : '') : $prev_page_url; ?>" 
            class=" btn btn-primary  flex px-3 h-8 justify-center items-center rounded">
                Prev
            </a>
        </li>
        <?php else: ?>
            <li>
                <span class="flex px-3 h-8 justify-center items-center rounded bg-gray-200 opacity-50">First</span>
            </li>
        <?php endif; ?>
        <li class="items current">
            <span class="btn btn-primary flex px-3 h-8 justify-center items-center rounded" >
            <?= $current_page; ?>
            </span>
        </li>
        <?php if ($is_next): ?>
            <li class="items">
                <a href="<?= $next_page_url; ?>" class="btn btn-primary flex px-3 h-8 justify-center items-center rounded">
                Next
                </a>
            </li>
        <?php else: ?>
            <li class="items"><span class="flex px-3 h-8 justify-center items-center rounded bg-gray-200 opacity-50">End</span>
        </li>
        <?php endif; ?>
    </ul>
</div>