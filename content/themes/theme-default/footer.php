</div>
</main>

<footer class="relative border-t border-gray-200 bg-gray-50 bg-white dark:border-home-border dark:bg-home-surface">
  <div class="container mx-auto pt-12 sm:pt-24 pb-6 ">
    <!-- Main Footer Content -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-6 sm:gap-8 lg:gap-12 mb-8 sm:mb-12">
      <!-- Left Section: Branding and Description -->
      <div class="sm:col-span-1 lg:col-span-2 space-y-4">
        <!-- Logo -->
        <div class="flex items-center space-x-2 mb-4">
          <?php echo \System\Libraries\Render\View::include('parts/headers/logo'); ?>
        </div>
        <!-- Description: site_desc (Options) giống SEO/logo; fallback bản dịch theme -->
        <p class="max-w-md text-sm leading-relaxed text-gray-600 dark:text-home-body font-plus">
          <?php
          $footerDesc = function_exists('option')
            ? trim((string) option('site_desc', defined('APP_LANG') ? APP_LANG : ''))
            : '';
          echo e($footerDesc !== '' ? $footerDesc : __('theme_footer.description'));
          ?>
        </p>
      </div>

      <!-- Right Section: Footer Links -->
      <div class="sm:col-span-2 lg:col-span-4 grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-8 sm:gap-x-6 sm:gap-y-8 lg:gap-12 items-start">
        <!-- Product Column -->
        <div>
          <h3 class="text-md font-semibold text-gray-900 mb-6 font-space dark:text-home-heading"><?php echo e(__('theme_footer.col_product')); ?></h3>
          <ul class="space-y-4">
            <li><a href="<?php echo e(base_url('download')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('Download')); ?></a></li>
            <li><a href="<?php echo e(base_url('features')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('Features')); ?></a></li>
            <li><a href="<?php echo e(base_url('theme-default-pro')); ?>" class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.theme-default_pro')); ?></a></li>
            <li><a href="<?php echo e(base_url('updates')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.updates')); ?></a></li>
          </ul>
        </div>

        <!-- Resources Column -->
        <div>
          <h3 class="text-md font-semibold text-gray-900 mb-6 font-space"><?php echo e(__('theme_footer.col_resources')); ?></h3>
          <ul class="space-y-4">
            <li><a href="<?php echo e(base_url('documentation')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('Documentation')); ?></a></li>
            <li><a href="<?php echo e(base_url('usage-guide')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.guides')); ?></a></li>
            <li><a href="<?php echo e(base_url('blog')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('Blog')); ?></a></li>
            <li><a href="<?php echo e(base_url('faq')); ?>" class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.faq')); ?></a>
            </li>
          </ul>
        </div>

        <!-- Community Column -->
        <div>
          <h3 class="text-md font-semibold text-gray-900 mb-6 font-space"><?php echo e(__('theme_footer.col_community')); ?></h3>
          <ul class="space-y-4 mb-6">
            <li><a href="<?php echo e(base_url('forum')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.forum')); ?></a></li>
            <li><a href="<?php echo e(base_url('contribute')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.contribute')); ?></a></li>
            <li><a href="<?php echo e(base_url('events')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_footer.events')); ?></a></li>
          </ul>
        </div>

        <!-- Contact Column -->
        <div>
          <h3 class="text-md font-semibold text-gray-900 mb-6 font-space"><?php echo e(__('theme_footer.col_contact')); ?></h3>
          <ul class="space-y-4 mb-4">
            <li><a href="<?php echo e(base_url('support')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('Support')); ?></a></li>
            <li><a href="<?php echo e(base_url('contact')); ?>"
                class="text-sm text-gray-600 hover:text-home-primary transition-colors font-plus"><?php echo e(__('theme_nav.contact')); ?></a></li>
            <li class="flex flex-wrap items-center gap-2">
              <?php include __DIR__ . '/parts/social/social-links.php'; ?>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="border-b border-gray-200 ">
      <!-- Performance Note -->
      <div class="flex items-center justify-center mb-4 ">
        <div class="flex items-center space-x-2 text-sm text-gray-600  rounded-home-lg bg-home-surface-light/20 p-2">
          <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="8.0046" cy="8.00533" r="7" transform="rotate(-171.034 8.0046 8.00533)" fill="var(--home-primary)"
              fill-opacity="0.1" />
            <circle cx="8.0042" cy="8.00563" r="3" transform="rotate(-171.034 8.0042 8.00563)" fill="var(--home-primary)" />
          </svg>
          <span class="font-plus text-xs"><?php echo e(__('theme_footer.performance_note')); ?></span>
        </div>
      </div>
    </div>

    <!-- Bottom Links -->
    <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0 mt-4">
      <p class="text-sm text-gray-600 font-plus"><?php echo e(__('theme_footer.copyright', (string) date('Y'))); ?></p>
      <div class="flex items-center space-x-6">
        <a href="<?= link_page('terms-of-service') ?>"
          class="text-sm font-medium text-gray-600 hover:text-home-primary transition-colors uppercase font-space"><?php echo e(__('theme_footer.terms')); ?></a>
        <a href="<?= link_page('privacy-policy') ?>"
          class="text-sm font-medium text-gray-600 hover:text-home-primary transition-colors uppercase font-space"><?php echo e(__('theme_footer.privacy')); ?></a>
        <a href="<?= link_page('cookies') ?>"
          class="text-sm font-medium text-gray-600 hover:text-home-primary transition-colors uppercase font-space"><?php echo e(__('theme_footer.cookies')); ?></a>
      </div>
    </div>
  </div>

  <!-- Scroll to Top Button -->
  <button id="scrollToTop" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="fixed bottom-8 sm:right-8 right-3 w-12 h-12 bg-home-primary hover:bg-home-primary-hover text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 opacity-0 pointer-events-none z-50"
    aria-label="<?php echo e(__('theme.aria.scroll_to_top')); ?>">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M10 5L5 10L6.25 11.25L10 7.5L13.75 11.25L15 10L10 5Z" fill="currentColor" />
    </svg>
  </button> 

  <script>
    // Show/hide scroll to top button
    window.addEventListener('scroll', function () {
      const scrollButton = document.getElementById('scrollToTop');
      if (window.pageYOffset > 300) {
        scrollButton.classList.remove('opacity-0', 'pointer-events-none');
        scrollButton.classList.add('opacity-100');
      } else {
        scrollButton.classList.add('opacity-0', 'pointer-events-none');
        scrollButton.classList.remove('opacity-100');
      }
    });
  </script>
</footer>
<?php assets_footer(); ?>

</body>
</html>
