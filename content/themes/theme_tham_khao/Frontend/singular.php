<?php
/**
 * Template: Singular (fallback single)
 * Head + Schema: mặc định từ context (global $post).
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

global $post;

Flang::load('CMS', APP_LANG);

$p = $post ?? null;
$title = '';
$datePub = '';
$author = '';
if ($p) {
    $title   = is_array($p) ? ($p['title'] ?? $p['post_title'] ?? '') : ($p->title ?? $p->post_title ?? '');
    $datePub = is_array($p) ? ($p['created_at'] ?? $p['post_date'] ?? '') : ($p->created_at ?? $p->post_date ?? '');
    $author  = is_array($p) ? ($p['author_name'] ?? $p['author'] ?? '') : ($p->author_name ?? $p->author ?? '');
}
$postTitle = $title !== '' ? $title : __('Post');
$fmt = function_exists('option') ? option('site_date_format', defined('APP_LANG') ? APP_LANG : '') : '';
$dateFmt = is_string($fmt) ? $fmt : 'Y-m-d';

view_header(['layout' => $layout ?? 'singular']);
?>
<div class="container mx-auto px-4 py-8">
    <?php if ($p) : ?>
        <article class="prose prose-slate max-w-none">
            <h1 class="text-2xl font-bold text-slate-800"><?php echo h($postTitle); ?></h1>
            <?php if ($datePub || $author) : ?>
                <p class="mt-1 text-sm text-slate-500">
                    <?php if ($datePub) : ?>
                        <time datetime="<?php echo e(date('c', is_numeric($datePub) ? $datePub : strtotime($datePub))); ?>"><?php echo e(date($dateFmt, is_numeric($datePub) ? $datePub : strtotime($datePub))); ?></time>
                    <?php endif; ?>
                    <?php if ($author) : ?>
                        <span class="ml-2"><?php echo h($author); ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
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
        <p class="text-slate-600"><?php _e('Post not found.'); ?></p>
    <?php endif; ?>
</div>
<?php view_footer(); ?>
