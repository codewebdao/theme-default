<?php
if (class_exists(\App\Libraries\Fastlang::class)) {
    \App\Libraries\Fastlang::load('CMS', defined('APP_LANG') ? APP_LANG : 'en');
}
$layout = $layout ?? \System\Libraries\Render\View::getShared('layout') ?? '';
$current_path = isset($_SERVER['REQUEST_URI']) ? trim((string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
$search_url = base_url('search');
$menu_items = [
    ['slug' => 'home',    'icon' => 'home',    'label_key' => 'theme_nav.home',    'href' => base_url()],
    ['slug' => 'blog',    'icon' => 'blog',    'label_key' => 'theme_nav.blog',    'href' => base_url('blog')],
    ['slug' => 'contact', 'icon' => 'contact', 'label_key' => 'theme_nav.contact', 'href' => base_url('contact')],
];
/** Nội dung <path> cho SVG icon trái (stroke, viewBox 24×24). */
$nav_icon_paths = [
    'home' => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-2v6H5a1 1 0 0 1-1-1v-9.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    'blog' => '<path d="M6 4h9a2 2 0 0 1 2 2v14l-4-2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    'contact' => '<path d="M4 5h16a1 1 0 0 1 1 1v12H3V6a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M4 7l8 5 8-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
];
$link_row_base = 'group relative flex w-full items-center gap-3 px-1 py-2.5 text-base font-semibold font-plus outline-none transition-[background-color,color,transform] duration-200 focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-home-primary dark:focus-visible:ring-offset-0 ';
?>

<div id="mobileMenuModal" class="jmodal jmodal-mobile-menu-full hidden lg:hidden" aria-hidden="true">
    <div
        class="jmodal-slide-left flex h-full min-h-0 w-full max-w-none flex-col bg-white dark:bg-zinc-950">
        <!-- Header: tìm kiếm trái + đóng phải -->
        <div
            class="flex-shrink-0 border-b border-white/15 bg-gradient-to-br from-[color:var(--home-primary)] to-[color:var(--home-heading)] px-4 pb-5 pt-5 text-white shadow-[0_1px_0_rgba(255,255,255,0.06)_inset] dark:border-zinc-700/60 dark:bg-gradient-to-b dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950 dark:text-zinc-100 dark:shadow-none">
            <div class="flex items-center gap-3">
                <form action="<?php echo e($search_url); ?>" method="GET" class="min-w-0 flex-1">
                    <div class="relative">
                        <input type="text" name="q" id="mobileSearchInput"
                            placeholder="<?php echo e(__('theme.nav.mobile_search_posts')); ?>"
                            class="box-border h-[36px] w-full rounded-full border border-white/30 bg-white/95 py-0 pl-4 pr-10 text-sm text-gray-900 placeholder:text-gray-500 shadow-md backdrop-blur-sm focus:border-white focus:outline-none focus:ring-2 focus:ring-white/45 dark:border-zinc-600 dark:bg-zinc-800/95 dark:text-zinc-100 dark:placeholder:text-zinc-400 dark:shadow-inner dark:focus:border-home-primary/50 dark:focus:ring-2 dark:focus:ring-home-primary/40" />
                        <button type="submit"
                            class="absolute right-1 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-home-primary transition hover:bg-home-primary/10 dark:text-zinc-300 dark:hover:bg-zinc-700/90"
                            aria-label="<?php echo e(__('theme.aria.search')); ?>">
                            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </form>
                <button type="button" data-jmodal-close
                    class="flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-sm transition hover:bg-white/25 active:scale-95 dark:bg-zinc-800/90 dark:text-zinc-100 dark:shadow-inner dark:hover:bg-zinc-700 dark:ring-1 dark:ring-zinc-600/50"
                    aria-label="<?php echo e(__('theme.aria.close_menu')); ?>">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Nội dung cuộn: menu + MXH -->
        <div class="flex min-h-0 flex-1 flex-col overflow-hidden bg-gradient-to-b from-gray-50 to-gray-100/90 dark:from-zinc-950 dark:to-zinc-900">
            <nav class="flex-1 overflow-y-auto overscroll-y-contain px-4 py-5" aria-label="<?php echo e(__('theme.aria.mobile_menu_nav')); ?>">
                <ul class="space-y-3" role="list">
                    <?php foreach ($menu_items as $item):
                        $item_path = trim((string) parse_url($item['href'], PHP_URL_PATH), '/');
                        $is_home = ($item['slug'] === 'home');
                        if ($is_home) {
                            $is_active = in_array((string) $layout, ['index', 'home', 'front'], true)
                                || $current_path === ''
                                || preg_match('#^(vi|en)$#i', (string) $current_path) === 1;
                        } else {
                            $is_active = ($layout !== '' && (strpos((string) $layout, $item['slug']) === 0 || $layout === $item['slug']))
                                || ($current_path !== '' && ($current_path === $item_path || strpos($current_path, $item_path . '/') === 0));
                        }
                        $link_class = $link_row_base . ($is_active
                            ? 'text-home-primary'
                            : 'text-gray-100/95 hover:text-home-primary dark:text-zinc-100 dark:hover:text-white');
                        $icon_class = 'h-5 w-5 shrink-0 transition-colors ' . ($is_active
                            ? 'text-home-primary'
                            : 'text-gray-400 group-hover:text-home-primary/85 dark:text-zinc-500 dark:group-hover:text-zinc-200');
                        $icon_key = $item['icon'] ?? $item['slug'];
                        $icon_inner = $nav_icon_paths[$icon_key] ?? $nav_icon_paths['home'];
                        ?>
                        <li>
                            <a href="<?php echo e($item['href']); ?>" class="<?php echo e($link_class); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                                <svg class="<?php echo e($icon_class); ?>" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><?php echo $icon_inner; ?></svg>
                                <span class="min-w-0 flex-1 truncate"><?php echo e(__($item['label_key'])); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="mx-4 mb-4 mt-2 flex flex-shrink-0 flex-wrap items-center justify-center gap-3" role="list" aria-label="<?php echo e(__('theme.nav.drawer_social')); ?>">
                <?php
                $social_links_variant = 'drawer';
                include __DIR__ . '/../social/social-links.php';
                ?>
            </div>
        </div>

        <!-- Chân drawer -->
        <footer class="flex-shrink-0 border-t border-gray-200/90 bg-white/95 px-4 pb-6 pt-4 dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-center text-[11px] leading-relaxed text-gray-500 dark:text-zinc-500 font-plus"><?php echo e(__('theme.nav.drawer_copyright', (string) date('Y'))); ?></p>
        </footer>
    </div>
</div>
