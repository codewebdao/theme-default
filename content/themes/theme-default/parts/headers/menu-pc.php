<?php
/**
 * Desktop menu – active state từ $layout hoặc từ URL (REQUEST_URI).
 */
if (class_exists(\App\Libraries\Fastlang::class)) {
  \App\Libraries\Fastlang::load('CMS', defined('APP_LANG') ? APP_LANG : 'en');
}
$layout = $layout ?? \System\Libraries\Render\View::getShared('layout') ?? '';
$current_path = isset($_SERVER['REQUEST_URI']) ? trim(parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
/** Menu blog: nhãn qua CMS (theme_nav.*), slug dùng so khớp active */
$menu_items = [
  ['slug' => 'home',        'label_key' => 'theme_nav.home',        'href' => base_url()],
  ['slug' => 'blog',        'label_key' => 'theme_nav.blog',        'href' => base_url('blog')],
  ['slug' => 'contact',     'label_key' => 'theme_nav.contact',     'href' => base_url('contact')],
];
$current_lang = function_exists('lang_code') ? lang_code() : (defined('APP_LANG') ? APP_LANG : 'en');
$current_lang_name = function_exists('lang_name') ? lang_name($current_lang) : (defined('APP_LANGUAGES') && isset(APP_LANGUAGES[$current_lang]) ? APP_LANGUAGES[$current_lang]['name'] : $current_lang);
$langs = defined('APP_LANGUAGES') ? APP_LANGUAGES : ['en' => ['name' => 'English'], 'vi' => ['name' => 'Tiếng Việt']];
$link_class_base = 'inline-flex h-[36px] shrink-0 cursor-pointer items-center justify-center rounded-full px-4 font-plus font-semibold transition-[background-color,color,box-shadow] duration-200 text-[16px] lg:text-xs ring-1 ring-transparent hover:bg-home-surface-light hover:ring-black/[0.04] dark:hover:ring-white/[0.06] ';
?>
<!-- Desktop Navigation -->
<div class="hidden lg:flex items-center ">
  <!-- Navigation Links -->
  <nav class="flex items-center space-x-2 xl:space-x-2 lg:space-x-0">
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
      $link_class = $link_class_base . ($is_active
        ? 'bg-home-surface-light text-home-primary ring-1 ring-black/[0.06] shadow-[inset_0_2px_6px_rgba(0,0,0,0.06)] dark:ring-white/10 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.15)]'
        : 'text-gray-700 hover:text-home-primary dark:text-gray-300 dark:hover:bg-home-surface-light dark:hover:text-home-primary');
    ?>
    <a href="<?php echo e($item['href']); ?>" class="<?php echo e($link_class); ?>">
      <?php echo e(__($item['label_key'])); ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Icon tìm kiếm + ngôn ngữ + theme (theme bên phải, cùng chiều cao 36px) -->
  <div class="ml-5 flex shrink-0 items-center gap-3 lg:ml-5 xl:ml-10">
  <a href="<?php echo e(base_url('search')); ?>" class="flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-full bg-gradient-to-b from-neutral-100 to-neutral-300 text-home-body ring-1 ring-black/[0.06] shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)] transition hover:brightness-[0.98] dark:from-zinc-800 dark:to-zinc-950 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] dark:ring-white/10 dark:hover:from-zinc-700 dark:hover:to-zinc-900" aria-label="<?php echo e(__('theme.aria.search')); ?>">
    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </a>
  <div class="relative lang-dropdown-js">
    <button type="button" aria-haspopup="true" aria-expanded="false" aria-controls="lang-menu-pc"
      class="lang-dropdown-btn flex h-[36px] items-center gap-2 rounded-full bg-gradient-to-b from-neutral-100 to-neutral-300 px-2 font-semibold ring-1 ring-black/[0.06] shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)] transition hover:brightness-[0.98] dark:from-zinc-800 dark:to-zinc-950 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] dark:ring-white/10 dark:hover:from-zinc-700 dark:hover:to-zinc-900">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M8.4538 7.43751H5.54641C5.57884 8.96143 5.77913 10.3179 6.07833 11.3076C6.24293 11.852 6.43017 12.2607 6.61808 12.5236C6.81215 12.7952 6.94672 12.8328 6.99885 12.8333L7.00004 12.8333C7.05124 12.8333 7.18648 12.7973 7.38212 12.5236C7.57004 12.2607 7.75728 11.852 7.92188 11.3075C8.22107 10.3179 8.42137 8.96143 8.4538 7.43751Z" fill="var(--home-body)" />
        <path d="M5.54641 6.56251H8.4538C8.42137 5.03859 8.22107 3.68211 7.92188 2.69246C7.75728 2.14801 7.57004 1.73927 7.38212 1.47637C7.18648 1.20267 7.05131 1.16667 7.0001 1.16667C6.9489 1.16667 6.81372 1.20267 6.61808 1.47638C6.43017 1.73928 6.24293 2.14801 6.07833 2.69247C5.77913 3.68211 5.57884 5.03859 5.54641 6.56251Z" fill="var(--home-body)" />
        <path d="M9.32899 7.43751C9.29652 9.02812 9.08815 10.4735 8.75944 11.5608C8.63133 11.9845 8.48118 12.3666 8.30836 12.686C10.7625 12.1237 12.6262 10.0134 12.8172 7.43751L9.32899 7.43751Z" fill="var(--home-body)" />
        <path d="M12.8172 6.56251L9.32899 6.56251C9.29652 4.97189 9.08815 3.52654 8.75944 2.43925C8.63133 2.01552 8.48118 1.63338 8.30836 1.31396C10.7625 1.87633 12.6262 3.98664 12.8172 6.56251Z" fill="var(--home-body)" />
        <path d="M4.67122 6.56251L1.18286 6.56251C1.37386 3.98658 3.23767 1.87624 5.69186 1.31393C5.51904 1.63335 5.36888 2.0155 5.24077 2.43925C4.91205 3.52654 4.70368 4.9719 4.67122 6.56251Z" fill="var(--home-body)" />
        <path d="M4.67122 7.43751L1.18286 7.43751C1.37386 10.0134 3.23767 12.1238 5.69186 12.6861C5.51904 12.3667 5.36887 11.9845 5.24077 11.5608C4.91205 10.4735 4.70368 9.02812 4.67122 7.43751Z" fill="var(--home-body)" />
      </svg>
      <span class="text-xs font-medium text-home-body font-semibold font-plus"><?php echo e($current_lang_name); ?></span>
      <svg class="shrink-0 text-neutral-400 dark:text-zinc-400" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="none" d="M1 1.25L5 4.75L9 1.25" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>
    <div id="lang-menu-pc" role="menu" class="lang-dropdown-panel absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded-home-md shadow-md overflow-hidden z-50 hidden dark:border-zinc-600 dark:bg-zinc-900">
      <?php foreach ($langs as $code => $info): ?>
      <a href="<?php echo e(function_exists('lang_url') ? lang_url($code) : (base_url() . '?lang=' . $code)); ?>" role="menuitem"
        class="block w-full text-left px-4 py-2 text-xs text-home-body hover:bg-gray-100 transition-colors font-semibold font-plus dark:text-zinc-200 dark:hover:bg-zinc-800">
        <?php echo e(function_exists('lang_name') ? lang_name($code) : ($info['name'] ?? $code)); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="flex h-[36px] shrink-0 items-center">
    <?php echo \System\Libraries\Render\View::include('parts/headers/theme-toggle', ['theme_toggle_id' => 'theme-toggle-pc']); ?>
  </div>
  </div>

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
