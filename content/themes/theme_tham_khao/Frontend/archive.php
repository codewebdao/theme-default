<?php
/**
 * Template: Archive
 * Head: mặc định từ context (title Archive / post type label).
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

$title = isset($params[0]) ? (string) $params[0] : '';
$archiveTitle = $title !== '' ? $title : __('Archive');

view_header(['layout' => $layout ?? 'archive']);
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800"><?php echo h($archiveTitle); ?></h1>
    <p class="mt-2 text-slate-600"><?php _e('Archive listing will be displayed here.'); ?></p>
</div>
<?php view_footer(); ?>
