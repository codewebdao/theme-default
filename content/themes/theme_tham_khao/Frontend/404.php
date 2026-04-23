<?php
/**
 * Template: 404 Not Found
 * Head: mặc định từ context (title "Page not found", noindex).
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

view_header(['layout' => $layout ?? '404']);
?>
<div class="container mx-auto px-4 py-16 text-center min-h-[60vh]">
    <h1 class="text-4xl font-bold text-slate-800">404</h1>
    <p class="mt-4 text-xl text-slate-600"><?php _e('Page not found'); ?></p>
    <p class="mt-2 text-slate-500"><?php _e('The page you are looking for might have been removed or does not exist.'); ?></p>
    <a href="<?php echo e(base_url()); ?>" class="inline-block mt-8 px-6 py-3 bg-slate-800 text-white rounded hover:bg-slate-700"><?php _e('Back to home'); ?></a>
</div>
<?php view_footer(); ?>
