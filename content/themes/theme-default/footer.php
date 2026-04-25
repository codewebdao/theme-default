</div>
</main>

<?php
$footer_link_class = 'inline-flex rounded-home-md px-1 py-0.5 text-sm text-gray-600 transition-colors hover:bg-home-surface-light hover:text-home-primary dark:text-zinc-400 dark:hover:bg-zinc-800/80 dark:hover:text-home-primary font-plus';
/* text-home-heading = var(--home-heading), tự sáng ở dark mode */
$footer_heading_class = 'mb-4 text-xs font-bold uppercase tracking-wider text-home-heading font-space';
?>

<footer class="relative border-t border-home-border/60 bg-gradient-to-b from-gray-50 to-white dark:border-zinc-800 dark:from-zinc-950 dark:to-zinc-950" role="contentinfo">
    <div class="container mx-auto px-4 pb-10 pt-14 sm:px-6 sm:pb-12 sm:pt-20 lg:px-8">
        <!-- 3 cột ~50/25/25; khe giữa các cột = column-gap đồng nhất — .theme-footer-grid trong css/footer.css -->
        <div class="theme-footer-grid mb-12 lg:mb-16">
            <div>
                <div class="mb-5 flex items-center gap-3">
                    <?php echo \System\Libraries\Render\View::include('parts/headers/logo'); ?>
                </div>
                <p class="text-sm leading-relaxed text-gray-600 dark:text-zinc-400 font-plus">
                    <?php
                    $footerDesc = function_exists('option')
                        ? trim((string) option('site_desc', defined('APP_LANG') ? APP_LANG : ''))
                        : '';
                    echo e($footerDesc !== '' ? $footerDesc : __('theme_footer.description'));
                    ?>
                </p>
            </div>

            <div>
                <h3 class="<?php echo e($footer_heading_class); ?>"><?php echo e(__('theme_footer.col_legal')); ?></h3>
                <ul class="space-y-1">
                    <li><a href="<?php echo e(link_page('terms-of-service')); ?>"
                        class="<?php echo e($footer_link_class); ?>"><?php echo e(__('theme_footer.terms')); ?></a></li>
                    <li><a href="<?php echo e(link_page('privacy-policy')); ?>"
                        class="<?php echo e($footer_link_class); ?>"><?php echo e(__('theme_footer.privacy')); ?></a></li>
                    <li><a href="<?php echo e(link_page('cookies')); ?>"
                        class="<?php echo e($footer_link_class); ?>"><?php echo e(__('theme_footer.cookies')); ?></a></li>
                </ul>
            </div>
            <div>
                <h3 class="<?php echo e($footer_heading_class); ?>"><?php echo e(__('theme_footer.col_contact')); ?></h3>
                <ul class="space-y-1">
                    <li><a href="<?php echo e(base_url('support')); ?>" class="<?php echo e($footer_link_class); ?>"><?php echo e(__('Support')); ?></a></li>
                    <li><a href="<?php echo e(base_url('contact')); ?>" class="<?php echo e($footer_link_class); ?>"><?php echo e(__('theme_nav.contact')); ?></a></li>
                </ul>
            </div>
        </div>

        <!-- Bản quyền + mạng xã hội: 2 cột (sm+) — trái chữ, phải icon (.theme-footer-bottom trong css/footer.css) -->
        <div class="border-t border-gray-200/90 pt-8 dark:border-zinc-800">
            <div class="theme-footer-bottom">
                <p class="theme-footer-bottom__copy text-sm text-gray-500 dark:text-zinc-500 font-plus">
                    <?php echo e(__('theme_footer.copyright', (string) date('Y'))); ?>
                </p>
                <nav class="theme-footer-bottom__social" aria-label="<?php echo e(__('theme.nav.drawer_social')); ?>">
                    <?php include __DIR__ . '/parts/social/social-links.php'; ?>
                </nav>
            </div>
        </div>
    </div>

    <button type="button" id="scrollToTop"
        class="fixed bottom-8 right-3 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-home-primary text-white shadow-lg ring-1 ring-black/5 transition-all duration-300 hover:bg-home-primary-hover hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-home-primary sm:right-8 dark:ring-white/10 opacity-0 pointer-events-none"
        aria-label="<?php echo e(__('theme.aria.scroll_to_top')); ?>">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M10 5L5 10L6.25 11.25L10 7.5L13.75 11.25L15 10L10 5Z" fill="currentColor" />
        </svg>
    </button>

    <script>
        window.addEventListener('scroll', function () {
            var scrollButton = document.getElementById('scrollToTop');
            if (!scrollButton) return;
            if (window.pageYOffset > 300) {
                scrollButton.classList.remove('opacity-0', 'pointer-events-none');
                scrollButton.classList.add('opacity-100');
            } else {
                scrollButton.classList.add('opacity-0', 'pointer-events-none');
                scrollButton.classList.remove('opacity-100');
            }
        });
        var scrollBtn = document.getElementById('scrollToTop');
        if (scrollBtn) {
            scrollBtn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    </script>
</footer>
<?php assets_footer(); ?>

</body>
</html>
