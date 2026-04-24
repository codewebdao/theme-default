<?php
$layout = $layout ?? \System\Libraries\Render\View::getShared('layout') ?? '';
$current_path = isset($_SERVER['REQUEST_URI']) ? trim(parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
$search_url = base_url('search');
?>
<div id="mobileMenuModal" class="jmodal hidden lg:hidden" aria-hidden="true">
  <div class="jmodal-slide-left flex flex-col h-full w-[80%] max-w-[24rem]">
    <div class="flex flex-col flex-1 min-h-0 container overflow-y-auto" style="height: 100%;">
      <div class="from-emerald-500 to-green-600 py-4 text-white flex-shrink-0">
        <div class="flex items-center justify-between mb-4">
          <button type="button" data-jmodal-close class="text-white hover:text-gray-200 transition-colors p-1" aria-label="<?php echo e(__('theme.aria.close_menu')); ?>">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M24 8L8 24M8 8L24 24" stroke="black" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <form action="<?php echo e($search_url); ?>" method="GET" class="flex-1 mx-4">
            <div class="relative">
              <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 text-emerald-700 w-[24px] h-[24px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M23 21L18.66 16.66M21 11C21 15.4183 17.4183 19 13 19C8.58172 19 5 15.4183 5 11C5 6.58172 8.58172 3 13 3C17.4183 3 21 6.58172 21 11Z" stroke="url(#paint0_linear_mob)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <defs>
                  <linearGradient id="paint0_linear_mob" x1="5.58" y1="18" x2="23.08" y2="17.75" gradientUnits="userSpaceOnUse">
                    <stop stop-color="var(--home-accent)" />
                    <stop offset="0.577" stop-color="var(--home-primary)" />
                  </linearGradient>
                </defs>
              </svg>
              <input type="text" name="q" id="mobileSearchInput" placeholder="<?php echo e(__('theme.search_placeholder')); ?>" class="input w-full pl-4 pr-10 bg-white/90 focus:border-white text-gray-900">
            </div>
          </form>
        </div>
      </div>
      <div class="flex-1  overflow-y-auto custom-scrollbar bg-white">
        <nav class="py-8 ">
          <?php
          $menu_items = [
            ['slug' => 'features',     'label_key' => 'theme_nav.features',     'href' => base_url('features')],
            ['slug' => 'usage-guide',  'label_key' => 'theme_nav.usage_guide',  'href' => base_url('usage-guide')],
            ['slug' => 'tutorial', 'label_key' => 'theme_nav.php_tutorial', 'href' => base_url('tutorial')],
            ['slug' => 'reviews',   'label_key' => 'theme_nav.review_cms',   'href' => base_url('reviews')],
            ['slug' => 'blog',         'label_key' => 'theme_nav.blog',         'href' => base_url('blog')],
            ['slug' => 'contact',      'label_key' => 'theme_nav.contact',      'href' => base_url('contact')],
          ];
          foreach ($menu_items as $item):
            $item_path = trim(parse_url($item['href'], PHP_URL_PATH), '/');
            $is_active = ($layout !== '' && (strpos($layout, $item['slug']) === 0 || $layout === $item['slug']))
              || ($current_path !== '' && ($current_path === $item_path || strpos($current_path, $item_path . '/') === 0));
            $link_class = 'block p-4 rounded-home-sm hover:bg-gray-50 transition-colors border-b border-[#97A4B2] text-sm font-plus ' . ($is_active ? 'bg-home-surface-light text-home-primary' : 'text-gray-700 hover:text-blue-600');
          ?>
            <a href="<?php echo e($item['href']); ?>" class="<?php echo e($link_class); ?>"><?php echo e(__($item['label_key'])); ?></a>
          <?php endforeach; ?>
        </nav>
        <div class="flex items-center justify-center space-x-4 ">
        <?php include __DIR__ . '/../social/social-links.php'; ?>
        </div>
      </div>
      <div class="pb-12 border-gray-200 bg-white flex-shrink-0">
        <div class="-mx-4 flex flex-col items-center gap-2 mb-4 bg-home-surface-light/25 backdrop-blur-sm rounded-lg py-2">
          <svg width="17" height="17" viewBox="0 0 17 17 " fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="8.00558" cy="8.0054" r="7" transform="rotate(-171.034 8.00558 8.0054)" fill="var(--home-primary)" fill-opacity="0.1" />
            <circle cx="8.00518" cy="8.00539" r="3" transform="rotate(-171.034 8.00518 8.00539)" fill="var(--home-primary)" />
          </svg>
          <span class="text-xs text-gray-600 text-center font-plus"><?php echo e(__('theme_footer.performance_note')); ?></span>
        </div>
        <a href="<?php echo e(base_url('download')); ?>">
          <button
            class="w-full bg-home-primary text-white py-3 px-6 rounded-home-md font-medium hover:bg-home-primary-hover transition mb-4 font-plus shadow-[0_58px_16px_0_rgba(43,140,238,0),0_37px_15px_0_rgba(43,140,238,0.01),0_21px_13px_0_rgba(43,140,238,0.05),0_9px_9px_0_rgba(43,140,238,0.09),0_2px_5px_0_rgba(43,140,238,0.1)]">
            <?php echo e(__('theme_footer.download_v84')); ?>
          </button>
        </a>
        <p class="text-sm text-gray-500 text-center font-plus"><?php echo e(__('theme_footer.copyright', (string) date('Y'))); ?></p>
      </div>
    </div>
  </div>
</div>