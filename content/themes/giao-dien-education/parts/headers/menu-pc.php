<?php
/**
 * Desktop menu – active state từ $layout hoặc từ URL (REQUEST_URI).
 */
$layout = $layout ?? \System\Libraries\Render\View::getShared('layout') ?? '';
$current_path = isset($_SERVER['REQUEST_URI']) ? trim(parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
$menu_items = [
  ['slug' => 'features',      'label_key' => 'theme_nav.features',     'href' => base_url('features')],
  ['slug' => 'usage-guide',   'label_key' => 'theme_nav.usage_guide',  'href' => base_url('usage-guide')],
  ['slug' => 'tutorial',  'label_key' => 'theme_nav.php_tutorial', 'href' => base_url('tutorial')],
  ['slug' => 'reviews',    'label_key' => 'theme_nav.review_cms',   'href' => base_url('reviews')],
  ['slug' => 'blog',          'label_key' => 'theme_nav.blog',         'href' => base_url('blog')],
  ['slug' => 'contact',       'label_key' => 'theme_nav.contact',      'href' => base_url('contact')],
];
$current_lang = function_exists('lang_code') ? lang_code() : (defined('APP_LANG') ? APP_LANG : 'en');
$current_lang_name = function_exists('lang_name') ? lang_name($current_lang) : (defined('APP_LANGUAGES') && isset(APP_LANGUAGES[$current_lang]) ? APP_LANGUAGES[$current_lang]['name'] : $current_lang);
$langs = defined('APP_LANGUAGES') ? APP_LANGUAGES : ['en' => ['name' => 'English'], 'vi' => ['name' => 'Tiếng Việt']];
$link_class_base = 'cursor-pointer font-plus font-semibold transition-colors duration-200 hover:bg-home-surface-light py-2 px-4 rounded-home-md text-[16px] lg:text-xs ';
?>
<!-- Desktop Navigation -->
<div class="hidden lg:flex items-center ">
  <!-- Navigation Links -->
  <nav class="flex items-center space-x-2 xl:space-x-2 lg:space-x-0">
    <?php foreach ($menu_items as $item):
      $item_path = trim(parse_url($item['href'], PHP_URL_PATH), '/');
      $is_active = ($layout !== '' && (strpos($layout, $item['slug']) === 0 || $layout === $item['slug']))
        || ($current_path !== '' && ($current_path === $item_path || strpos($current_path, $item_path . '/') === 0));
      $link_class = $link_class_base . ($is_active ? 'bg-home-surface-light text-home-primary' : 'text-gray-700 hover:text-blue-600');
    ?>
    <a href="<?php echo e($item['href']); ?>" class="<?php echo e($link_class); ?>">
      <?php echo e(__($item['label_key'])); ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Language Dropdown (PHP + details/summary, không phụ thuộc Alpine) -->
  <div class="relative lang-dropdown-js xl:ml-10 lg:ml-5 ml-5">
    <button type="button" aria-haspopup="true" aria-expanded="false" aria-controls="lang-menu-pc"
      class="lang-dropdown-btn flex items-center gap-2 px-2 h-[36px] bg-gray-100 rounded-home-md hover:bg-gray-200 transition font-semibold">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M8.4538 7.43751H5.54641C5.57884 8.96143 5.77913 10.3179 6.07833 11.3076C6.24293 11.852 6.43017 12.2607 6.61808 12.5236C6.81215 12.7952 6.94672 12.8328 6.99885 12.8333L7.00004 12.8333C7.05124 12.8333 7.18648 12.7973 7.38212 12.5236C7.57004 12.2607 7.75728 11.852 7.92188 11.3075C8.22107 10.3179 8.42137 8.96143 8.4538 7.43751Z" fill="var(--home-body)" />
        <path d="M5.54641 6.56251H8.4538C8.42137 5.03859 8.22107 3.68211 7.92188 2.69246C7.75728 2.14801 7.57004 1.73927 7.38212 1.47637C7.18648 1.20267 7.05131 1.16667 7.0001 1.16667C6.9489 1.16667 6.81372 1.20267 6.61808 1.47638C6.43017 1.73928 6.24293 2.14801 6.07833 2.69247C5.77913 3.68211 5.57884 5.03859 5.54641 6.56251Z" fill="var(--home-body)" />
        <path d="M9.32899 7.43751C9.29652 9.02812 9.08815 10.4735 8.75944 11.5608C8.63133 11.9845 8.48118 12.3666 8.30836 12.686C10.7625 12.1237 12.6262 10.0134 12.8172 7.43751L9.32899 7.43751Z" fill="var(--home-body)" />
        <path d="M12.8172 6.56251L9.32899 6.56251C9.29652 4.97189 9.08815 3.52654 8.75944 2.43925C8.63133 2.01552 8.48118 1.63338 8.30836 1.31396C10.7625 1.87633 12.6262 3.98664 12.8172 6.56251Z" fill="var(--home-body)" />
        <path d="M4.67122 6.56251L1.18286 6.56251C1.37386 3.98658 3.23767 1.87624 5.69186 1.31393C5.51904 1.63335 5.36888 2.0155 5.24077 2.43925C4.91205 3.52654 4.70368 4.9719 4.67122 6.56251Z" fill="var(--home-body)" />
        <path d="M4.67122 7.43751L1.18286 7.43751C1.37386 10.0134 3.23767 12.1238 5.69186 12.6861C5.51904 12.3667 5.36887 11.9845 5.24077 11.5608C4.91205 10.4735 4.70368 9.02812 4.67122 7.43751Z" fill="var(--home-body)" />
      </svg>
      <span class="text-xs font-medium text-home-body font-semibold font-plus"><?php echo e($current_lang_name); ?></span>
      <svg width="10" height="6" viewBox="0 0 10 6" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>
    <div id="lang-menu-pc" role="menu" class="lang-dropdown-panel absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded-home-md shadow-md overflow-hidden z-50 hidden">
      <?php foreach ($langs as $code => $info): ?>
      <a href="<?php echo e(function_exists('lang_url') ? lang_url($code) : (base_url() . '?lang=' . $code)); ?>" role="menuitem"
        class="block w-full text-left px-4 py-2 text-xs text-home-body hover:bg-gray-100 transition-colors font-semibold font-plus">
        <?php echo e(function_exists('lang_name') ? lang_name($code) : ($info['name'] ?? $code)); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Download Button -->
  <a class="ml-3" href="<?php echo e(base_url('download')); ?>">
    <button type="button" class="bg-home-primary text-white lg:h-[41px] px-6 rounded-home-md font-sm font-plus hover:bg-home-primary-hover transition truncate">
      <?php echo e(__('theme_footer.download_v84')); ?>
    </button>
  </a>
</div>

<script>
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.lang-dropdown-js').forEach(function(wrap) {
      var btn = wrap.querySelector('.lang-dropdown-btn');
      var panel = wrap.querySelector('.lang-dropdown-panel');
      if (!btn || !panel) return;
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var open = panel.classList.toggle('hidden');
        btn.setAttribute('aria-expanded', !open);
      });
      panel.addEventListener('click', function(e) { e.stopPropagation(); });
      document.addEventListener('click', function() {
        panel.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
      });
    });
  });
})();
</script>
