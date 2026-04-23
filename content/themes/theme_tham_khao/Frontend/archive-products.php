<?php
/**
 * Template: Product Archive
 * Head: mặc định từ context (title "Products", canonical /products/).
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

view_header(['layout' => $layout ?? 'archive-products']);
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800"><?php _e('Products'); ?></h1>
    <p class="mt-2 text-slate-600"><?php _e('Products will be listed here.'); ?></p>
</div>
<?php view_footer(); ?>
