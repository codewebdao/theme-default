<?php
/**
 * Template: Taxonomy Archive
 * Head: mặc định từ context.
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

$title = isset($params[0]) ? (string) $params[0] : '';
$taxTitle = $title !== '' ? $title : __('Taxonomy archive');

view_header(['layout' => $layout ?? 'taxonomy']);
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800"><?php echo h($taxTitle); ?></h1>
    <p class="mt-2 text-slate-600"><?php _e('Posts in this term will be listed here.'); ?></p>
</div>
<?php view_footer(); ?>
