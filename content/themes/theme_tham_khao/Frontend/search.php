<?php
/**
 * Template: Search Results
 * Head: mặc định từ context (title "Search" / "Search: {query}", noindex).
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

$query = isset($_GET['q']) ? trim(strip_tags((string) $_GET['q'])) : '';
$title = $query !== '' ? sprintf(__('Search results for: %s'), $query) : __('Search');

view_header(['layout' => $layout ?? 'search']);
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800"><?php echo h($title); ?></h1>
    <?php if ($query !== '') : ?>
        <p class="mt-2 text-slate-600"><?php echo sprintf(__('You searched for: %s'), h($query)); ?></p>
        <div class="mt-6 text-slate-500"><?php _e('Search results will be listed here.'); ?></div>
    <?php else : ?>
        <p class="mt-2 text-slate-600"><?php _e('Enter a search term in the form.'); ?></p>
    <?php endif; ?>
</div>
<?php view_footer(); ?>
