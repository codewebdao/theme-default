<?php
use System\Libraries\Render\View;
?>
<!DOCTYPE html>
<html lang="<?= lang_code() ?>" prefix="og: https://ogp.me/ns#">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    (function(){try{var K='theme-color-pref';var v=localStorage.getItem(K);var sys=window.matchMedia('(prefers-color-scheme:dark)').matches;var dark=v==='dark'||(v!=='light'&&!!sys);document.documentElement.classList.toggle('dark',dark);}catch(e){}})();
    </script>
    <?php
    $layout = $layout ?? '';
    // view_head() renders Head (title, meta, OG, Schema::get()), assets_head()
    view_head();
    ?>
  </head>

<body>
  <script src="<?php echo (defined('APP_THEME_NAME') && function_exists('theme_assets')) ? theme_assets('js/theme-color-scheme.js') : ('/content/themes/' . basename(__DIR__) . '/assets/js/theme-color-scheme.js'); ?>"></script>
  <script src="<?php echo (defined('APP_THEME_NAME') && function_exists('theme_assets')) ? theme_assets('js/header.js') : (function_exists('public_url') ? public_url('content/themes/' . basename(__DIR__) . '/assets/js/header.js') : ('/content/themes/' . basename(__DIR__) . '/assets/js/header.js')); ?>"></script>
  <!-- Header: blog / tạp chí — màu & viền theo token theme -->
  <header class="sticky top-0 z-50 border-b border-home-border/35 bg-home-white/95 shadow-sm backdrop-blur-md" x-data="headerComponent()" role="banner">
    <script src="<?php echo defined('APP_THEME_NAME') && function_exists('theme_assets') ? theme_assets('js/swiper-bundle.min.js') : '/assets/js/swiper-bundle.min.js'; ?>"></script>
    <!-- Main Header -->
    <div class="container">
      <div class="flex h-20 items-center justify-between gap-2">
        <!-- Logo: căn trái mọi breakpoint (mobile + desktop) -->
        <div class="flex min-w-0 flex-1 justify-start lg:flex-none">
          <?php echo View::include('parts/headers/logo'); ?>
        </div>

        <!-- Mobile Language Selector: chỉ hiện EN và VN -->
        <?php
        $current_lang = defined('APP_LANG') ? APP_LANG : 'en';
        $langs_mobi = ['en' => 'EN', 'vi' => 'VN'];
        $current_lang_mobi = $langs_mobi[$current_lang] ?? 'EN';
        if (class_exists(\App\Libraries\Fastlang::class)) {
          \App\Libraries\Fastlang::load('CMS', defined('APP_LANG') ? APP_LANG : 'en');
        }
        ?>
        <div class="flex items-center gap-2 lg:hidden">
        <div class="relative lang-dropdown-js">
          <button type="button" aria-haspopup="true" aria-expanded="false" aria-controls="lang-menu-mobi" class="lang-dropdown-btn flex h-[36px] items-center gap-2 rounded-full bg-gradient-to-b from-neutral-100 to-neutral-300 px-3 ring-1 ring-black/[0.06] shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)] transition hover:brightness-[0.98] dark:from-zinc-800 dark:to-zinc-950 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] dark:ring-white/10 dark:hover:from-zinc-700 dark:hover:to-zinc-900">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M8.4538 7.43751H5.54641C5.57884 8.96143 5.77913 10.3179 6.07833 11.3076C6.24293 11.852 6.43017 12.2607 6.61808 12.5236C6.81215 12.7952 6.94672 12.8328 6.99885 12.8333L7.00004 12.8333C7.05124 12.8333 7.18648 12.7973 7.38212 12.5236C7.57004 12.2607 7.75728 11.852 7.92188 11.3075C8.22107 10.3179 8.42137 8.96143 8.4538 7.43751Z" fill="var(--home-body)" />
              <path d="M5.54641 6.56251H8.4538C8.42137 5.03859 8.22107 3.68211 7.92188 2.69246C7.75728 2.14801 7.57004 1.73927 7.38212 1.47637C7.18648 1.20267 7.05131 1.16667 7.0001 1.16667C6.9489 1.16667 6.81372 1.20267 6.61808 1.47638C6.43017 1.73928 6.24293 2.14801 6.07833 2.69247C5.77913 3.68211 5.57884 5.03859 5.54641 6.56251Z" fill="var(--home-body)" />
              <path d="M9.32899 7.43751C9.29652 9.02812 9.08815 10.4735 8.75944 11.5608C8.63133 11.9845 8.48118 12.3666 8.30836 12.686C10.7625 12.1237 12.6262 10.0134 12.8172 7.43751L9.32899 7.43751Z" fill="var(--home-body)" />
              <path d="M12.8172 6.56251L9.32899 6.56251C9.29652 4.97189 9.08815 3.52654 8.75944 2.43925C8.63133 2.01552 8.48118 1.63338 8.30836 1.31396C10.7625 1.87633 12.6262 3.98664 12.8172 6.56251Z" fill="var(--home-body)" />
              <path d="M4.67122 6.56251L1.18286 6.56251C1.37386 3.98658 3.23767 1.87624 5.69186 1.31393C5.51904 1.63335 5.36888 2.0155 5.24077 2.43925C4.91205 3.52654 4.70368 4.9719 4.67122 6.56251Z" fill="var(--home-body)" />
              <path d="M4.67122 7.43751L1.18286 7.43751C1.37386 10.0134 3.23767 12.1238 5.69186 12.6861C5.51904 12.3667 5.36887 11.9845 5.24077 11.5608C4.91205 10.4735 4.70368 9.02812 4.67122 7.43751Z" fill="var(--home-body)" />
            </svg>
            <span class="text-xs font-medium text-gray-700 font-plus dark:text-zinc-200"><?php echo e($current_lang_mobi); ?></span>
            <svg class="shrink-0 text-neutral-400 dark:text-zinc-400" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="none" d="M1 1.25L5 4.75L9 1.25" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </button>
          <div id="lang-menu-mobi" role="menu" class="lang-dropdown-panel absolute right-0 mt-2 w-full bg-white border border-gray-200 rounded-home-md shadow-md overflow-hidden z-50 hidden dark:border-zinc-600 dark:bg-zinc-900">
            <?php foreach ($langs_mobi as $code => $label): ?>
              <a href="<?php echo e(function_exists('lang_url') ? lang_url($code) : (base_url() . '?lang=' . $code)); ?>" role="menuitem" class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 transition-colors font-plus dark:text-zinc-200 dark:hover:bg-zinc-800"><?php echo e($label); ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="flex h-[36px] shrink-0 items-center">
          <?php echo View::include('parts/headers/theme-toggle', ['theme_toggle_id' => 'theme-toggle-mobi']); ?>
        </div>
        <button type="button" id="mobileMenuToggle" class="flex h-[36px] w-[36px] shrink-0 cursor-pointer items-center justify-center rounded-full bg-gradient-to-b from-neutral-100 to-neutral-300 text-neutral-700 ring-1 ring-black/[0.06] shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)] transition hover:brightness-[0.98] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-home-primary dark:from-zinc-800 dark:to-zinc-950 dark:text-zinc-200 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] dark:ring-white/10 dark:hover:from-zinc-700 dark:hover:to-zinc-900 lg:hidden touch-manipulation" aria-label="Mở menu điều hướng">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="pointer-events-none" aria-hidden="true">
            <path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        </div>

        <!-- Desktop Menu -->
        <?php echo View::include('parts/headers/menu-pc', ['layout' => $layout]); ?>
      </div>
    </div>
  </header>
  <?php echo View::include('parts/headers/menu-mobi', ['layout' => $layout]); ?>
  <script>
  (function() {
    function initMobileMenuModal() {
      if (typeof window.jModal === 'undefined') return;
      window.jModal.init('#mobileMenuModal');
      var btn = document.getElementById('mobileMenuToggle');
      if (btn && !btn._jmodalBound) {
        btn._jmodalBound = true;
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          window.jModal.open('mobileMenuModal');
        }, true);
      }
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initMobileMenuModal);
    } else {
      initMobileMenuModal();
    }
    setTimeout(initMobileMenuModal, 500);
  })();
  </script>
  <main>
    <div class="site-root min-h-screen bg-background text-slate-800 dark:bg-home-surface dark:text-gray-100">
      <style>
        @keyframes shake {

          0%,
          100% {
            transform: translateX(0)
          }

          25% {
            transform: translateX(-5px)
          }

          75% {
            transform: translateX(5px)
          }
        }
      </style>

