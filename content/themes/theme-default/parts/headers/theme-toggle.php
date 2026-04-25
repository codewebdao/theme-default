<?php
/**
 * Gạt sáng/tối — chrome track khớp `lang-dropdown-btn` / nút search trong `menu-pc.php` (gradient + ring + inset).
 * Class `dark` trên <html> qua theme-color-scheme.js.
 *
 * @param string $theme_toggle_id Id duy nhất (theme-toggle-pc / theme-toggle-mobi).
 */
if (class_exists(\App\Libraries\Fastlang::class)) {
    \App\Libraries\Fastlang::load('CMS', defined('APP_LANG') ? APP_LANG : 'en');
}
if (!function_exists('__')) {
    load_helpers(['languages']);
}
$id = isset($theme_toggle_id) ? (string) $theme_toggle_id : 'theme-toggle';
$aria = __('theme.aria.toggle_color_scheme');
?>
<label for="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>" class="relative inline-flex h-[36px] cursor-pointer select-none items-center">
  <span class="sr-only"><?php echo e($aria); ?></span>
  <input
    type="checkbox"
    id="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
    class="theme-color-toggle peer sr-only"
    autocomplete="off"
    role="switch"
    aria-checked="false"
    aria-label="<?php echo e($aria); ?>" />
  <span
    class="relative block h-[36px] w-[4rem] shrink-0 rounded-full bg-gradient-to-b from-neutral-100 to-neutral-300 ring-1 ring-black/[0.06] shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)] transition hover:brightness-[0.98] dark:from-zinc-800 dark:to-zinc-950 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] dark:ring-white/10 dark:hover:from-zinc-700 dark:hover:to-zinc-900 peer-checked:[&_.track-icon-moon]:pointer-events-none peer-checked:[&_.track-icon-moon]:opacity-0 peer-checked:[&_.thumb-wrap]:translate-x-[1.875rem] peer-checked:[&_.thumb-wrap]:bg-gradient-to-br peer-checked:[&_.thumb-wrap]:from-cyan-400 peer-checked:[&_.thumb-wrap]:via-teal-500 peer-checked:[&_.thumb-wrap]:to-teal-800 peer-checked:[&_.thumb-wrap]:shadow-[0_0_18px_rgba(45,212,191,0.55),0_2px_12px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.2)] peer-checked:[&_.thumb-wrap]:ring-2 peer-checked:[&_.thumb-wrap]:ring-cyan-200/40 dark:peer-checked:[&_.thumb-wrap]:from-cyan-500 dark:peer-checked:[&_.thumb-wrap]:via-teal-600 dark:peer-checked:[&_.thumb-wrap]:to-teal-950 dark:peer-checked:[&_.thumb-wrap]:shadow-[0_0_22px_rgba(34,211,238,0.45),0_2px_14px_rgba(0,0,0,0.45),inset_0_1px_0_rgba(255,255,255,0.12)] dark:peer-checked:[&_.thumb-wrap]:ring-cyan-400/30 peer-checked:[&_.toggle-sun]:scale-50 peer-checked:[&_.toggle-sun]:opacity-0 peer-checked:[&_.toggle-moon]:scale-100 peer-checked:[&_.toggle-moon]:opacity-100 peer-checked:[&_.toggle-moon]:text-white peer-checked:[&_.toggle-moon]:drop-shadow-[0_0_6px_rgba(255,255,255,0.5)]">
    <!-- Trăng outline bên phải (nổi khi giao diện sáng) -->
    <svg class="track-icon-moon pointer-events-none absolute right-1.5 top-1/2 z-0 h-3.5 w-3.5 -translate-y-1/2 text-home-body opacity-60 transition-opacity duration-300 dark:opacity-80 dark:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 0111.21 3 7 7 0 0012 21a9 9 0 009-8.21z" />
    </svg>
    <!-- Mặt trời outline bên trái (nổi khi giao diện tối) -->
    <svg class="track-icon-sun pointer-events-none absolute left-1.5 top-1/2 z-0 h-3.5 w-3.5 -translate-y-1/2 text-home-body opacity-40 transition-opacity duration-300 dark:text-zinc-400 dark:opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
      <circle cx="12" cy="12" r="3.5" />
      <path stroke-linecap="round" d="M12 2v1.5M12 20.5V22M4.22 4.22l1.06 1.06M18.72 18.72l1.06 1.06M2 12h1.5M20.5 12H22M5.28 18.72l-1.06 1.06M19.78 4.22l-1.06 1.06" />
    </svg>
    <!-- Nút trượt: sáng (mặc định) = nhạt + ring chủ đạo; tối (peer-checked) = gradient primary đậm -->
    <span class="thumb-wrap pointer-events-none absolute left-1 top-1/2 z-[2] flex h-[26px] w-[26px] -translate-y-1/2 items-center justify-center rounded-full bg-gradient-to-b from-white via-home-surface-light to-teal-50 ring-1 ring-home-primary shadow-[inset_0_2px_4px_rgba(0,0,0,0.06)] transition-[transform,background-image,box-shadow] duration-300 ease-in-out will-change-transform dark:from-zinc-700 dark:to-zinc-900 dark:shadow-[inset_0_2px_8px_rgba(0,0,0,0.35)] dark:ring-white/10">
      <svg class="toggle-sun relative h-3.5 w-3.5 text-home-primary drop-shadow transition-all duration-300 dark:text-zinc-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
        <circle cx="12" cy="12" r="4" fill="currentColor" stroke="none" />
        <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
      </svg>
      <svg class="toggle-moon pointer-events-none absolute inset-0 m-auto h-3.5 w-3.5 scale-75 text-zinc-100 opacity-0 drop-shadow transition-all duration-300" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="currentColor" fill-opacity="0.7" d="M21 12.79A9 9 0 0111.21 3 7 7 0 0012 21a9 9 0 009-8.21z" />
        <path fill="currentColor" d="M21 12.79A9 9 0 0111.21 3 7 7 0 0012 21a9 9 0 009-8.21z" />
      </svg>
    </span>
  </span>
</label>
