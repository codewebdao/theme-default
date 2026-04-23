<div class="flex justify-center w-full">
    <ul class="flex flex-wrap gap-2 items-center">
    <?php if ($current_page > 1 ): ?>
        <li>
            <a href="<?= $current_page == 2 ? $base_url . (!empty($query_params) ? '?' . $query_params : '') : $prev_page_url; ?>" 
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-8 px-3">
                <span class="hidden md:inline">Prev</span><span class="block md:hidden"><<</span>
            </a>
        </li>
        <?php else: ?>
            <li>
                <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3 opacity-50 cursor-not-allowed">First</span>
            </li>
        <?php endif; ?>
        <li class="current">
            <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground h-8 px-3" >
            <?= $current_page; ?>
            </span>
        </li>
        <?php if ($is_next): ?>
            <li>
                <a href="<?= $next_page_url; ?>" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-8 px-3">
                <span class="hidden md:inline">Next</span><span class="block md:hidden">>></span>
                </a>
            </li>
        <?php else: ?>
            <li><span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3 opacity-50 cursor-not-allowed">End</span>
        </li>
        <?php endif; ?>
    </ul>
</div>