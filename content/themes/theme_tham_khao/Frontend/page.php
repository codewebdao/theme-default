<?php
/**
 * Template: Static Page
 * Head + Schema: mặc định từ context (global $page). Override: Head::setTitle() / filter render.head.defaults.
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

global $page;

Flang::load('CMS', APP_LANG);

$p = $page ?? null;
$title = '';
if ($p) {
    $title = is_array($p) ? ($p['title'] ?? $p['post_title'] ?? '') : ($p->title ?? $p->post_title ?? '');
}
$pageTitle = $title !== '' ? $title : __('Page');

view_header(['layout' => $layout ?? 'page']);
?>
<div class="container mx-auto px-4 py-8">
    <?php if ($p) : ?>
        <article class="prose prose-slate max-w-none">
            <h1 class="text-2xl font-bold text-slate-800"><?php echo h($pageTitle); ?></h1>
            <?php
            $content = is_array($p) ? ($p['content'] ?? $p['post_content'] ?? '') : ($p->content ?? $p->post_content ?? '');
            $desc   = is_array($p) ? ($p['description'] ?? $p['excerpt'] ?? '') : ($p->description ?? $p->excerpt ?? '');
            if ($content !== '') {
                echo $content;
            } else {
                echo '<p class="mt-2 text-slate-600">' . h($desc ?: __('No content.')) . '</p>';
            }
            ?>
        </article>
    <?php else : ?>
        <p class="text-slate-600"><?php _e('Page not found.'); ?></p>
    <?php endif; ?>
</div>
<?php view_footer(); ?>
