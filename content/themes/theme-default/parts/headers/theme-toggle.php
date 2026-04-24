<?php
/**
 * Gạt sáng/tối (pill + nút trượt mặt trời / trăng) — class `dark` trên <html> qua theme-color-scheme.js.
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
    class="relative block h-[36px] w-[4rem] shrink-0 rounded-full bg-gradient-to-b from-neutral-100 to-neutral-300 shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)] ring-1 ring-black/[0.06] transition-[background,box-shadow] duration-300 dark:from-zinc-800 dark:to-zinc-950 dark:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] dark:ring-white/10 peer-checked:from-zinc-800 peer-checked:to-zinc-950 peer-checked:shadow-[inset_0_2px_10px_rgba(0,0,0,0.4)] peer-checked:ring-white/10 peer-checked:[&_.track-icon-moon]:opacity-25 peer-checked:[&_.track-icon-sun]:opacity-70 peer-checked:[&_.thumb-wrap]:translate-x-[1.875rem] peer-checked:[&_.thumb-wrap]:from-zinc-600 peer-checked:[&_.thumb-wrap]:via-zinc-700 peer-checked:[&_.thumb-wrap]:to-zinc-900 peer-checked:[&_.thumb-wrap]:shadow-[0_3px_10px_rgba(0,0,0,0.45)] peer-checked:[&_.toggle-sun]:scale-50 peer-checked:[&_.toggle-sun]:opacity-0 peer-checked:[&_.toggle-moon]:scale-100 peer-checked:[&_.toggle-moon]:opacity-100">
    <!-- Trăng outline bên phải (nổi khi giao diện sáng) -->
    <svg class="track-icon-moon pointer-events-none absolute right-1.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400 opacity-70 transition-opacity duration-300 dark:text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 0111.21 3 7 7 0 0012 21a9 9 0 009-8.21z" />
    </svg>
    <!-- Mặt trời outline bên trái (nổi khi giao diện tối) -->
    <svg class="track-icon-sun pointer-events-none absolute left-1.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400 opacity-25 transition-opacity duration-300 dark:text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
      <circle cx="12" cy="12" r="3.5" />
      <path stroke-linecap="round" d="M12 2v1.5M12 20.5V22M4.22 4.22l1.06 1.06M18.72 18.72l1.06 1.06M2 12h1.5M20.5 12H22M5.28 18.72l-1.06 1.06M19.78 4.22l-1.06 1.06" />
    </svg>
    <!-- Nút trượt: cam/vàng = sáng, xám = tối -->
    <span class="thumb-wrap pointer-events-none absolute left-1 top-1/2 z-[1] flex h-[26px] w-[26px] -translate-y-1/2 items-center justify-center rounded-full bg-gradient-to-b from-amber-200 via-amber-300 to-orange-400 shadow-[0_2px_8px_rgba(0,0,0,0.2)] transition-[transform,background-image,box-shadow] duration-300 ease-[cubic-bezier(0.34,1.45,0.64,1)] will-change-transform">
      <svg class="toggle-sun relative h-3.5 w-3.5 text-white drop-shadow transition-all duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
        <circle cx="12" cy="12" r="4" fill="currentColor" stroke="none" />
        <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
      </svg>
      <svg class="toggle-moon absolute inset-0 m-auto h-3.5 w-3.5 scale-75 text-white opacity-0 drop-shadow transition-all duration-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M21 12.79A9 9 0 0111.21 3 7 7 0 0012 21a9 9 0 009-8.21z" />
      </svg>
    </span>
  </span>
</label>
