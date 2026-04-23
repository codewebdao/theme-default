<?php
/**
 * Template: 404 Not Found
 * Head: layout 404 → title "Page not found", robots noindex, nofollow (Head\Builder).
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;
use System\Libraries\Render\View;

Flang::load('CMS', APP_LANG);

// functions.php chỉ enqueue home-index.css — thiếu class header (bg-white/95, rounded-home-*, …) nằm trong index.css
// (index.php / page.php gọi addCss('home-index', 'css/index.css') để thay/ghi đè; 404 không qua các file đó).
View::addCss('cmsfullform-404-page', 'css/404-page.css', ['cmsfullform-404-shell'], THEME_VER, 'all', false, false, false);

view_header(['layout' => $layout ?? '404']);
?>
<section class="cmsfullform-404" aria-labelledby="error-404-title">
    <div class="cmsfullform-404__bg" aria-hidden="true"></div>
    <div class="cmsfullform-404__blob cmsfullform-404__blob--tr" aria-hidden="true"></div>
    <div class="cmsfullform-404__blob cmsfullform-404__blob--bl" aria-hidden="true"></div>
    <div class="cmsfullform-404__grid" aria-hidden="true"></div>

    <div class="cmsfullform-404__inner">
        <div class="cmsfullform-404__card">
            <p class="cmsfullform-404__kicker"><?php _e('Error'); ?></p>
            <h1 id="error-404-title" class="cmsfullform-404__title font-plus">
                404
            </h1>
            <p class="cmsfullform-404__lead font-plus"><?php _e('Page not found'); ?></p>
            <p class="cmsfullform-404__desc font-plus"><?php _e('The page you are looking for might have been removed or does not exist.'); ?></p>
            <div class="cmsfullform-404__actions">
                <a href="<?php echo e(base_url()); ?>" class="cmsfullform-404__btn cmsfullform-404__btn--primary font-plus gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:-translate-x-1">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg> <?php _e('Back to home'); ?>
                </a>
            </div>
        </div>
    </div>
</section>
<?php view_footer(); ?>
