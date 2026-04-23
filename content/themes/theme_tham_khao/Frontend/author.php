<?php

/**
 * Template: Author Archive
 * Head: mặc định từ context.
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

view_header(['layout' => $layout ?? 'author']);
$authorSlug = $params[0];
// dùng query helper để lấy thông tin của author
$author = get_author([
    'slug' => $authorSlug,
]);
print_r($author);
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800"><?php _e('Author archive'); ?></h1>
    <p class="mt-2 text-slate-600"><?php _e('Posts by this author will be listed here.'); ?></p>
</div>
<?php view_footer(); ?>