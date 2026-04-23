<?php
/**
 * Template: Single Product
 * Head + Schema: mặc định từ context (Product). Override: Head::setTitle() / filter render.head.defaults.
 */
define('APP_DEBUGBAR_SKIP', true);

use App\Libraries\Fastlang as Flang;

global $post;

Flang::load('CMS', APP_LANG);

$p = $post ?? null;
$title = '';
$desc = '';
$image = '';
$price = '';
$sku = '';
if ($p) {
    $title = is_array($p) ? ($p['title'] ?? $p['post_title'] ?? '') : ($p->title ?? $p->post_title ?? '');
    $desc  = is_array($p) ? ($p['description'] ?? $p['excerpt'] ?? '') : ($p->description ?? $p->excerpt ?? '');
    $image = is_array($p) ? ($p['thumbnail'] ?? $p['image'] ?? '') : ($p->thumbnail ?? $p->image ?? '');
    $price = is_array($p) ? ($p['price'] ?? '') : ($p->price ?? '');
    $sku   = is_array($p) ? ($p['sku'] ?? '') : ($p->sku ?? '');
}
$productTitle = $title !== '' ? $title : __('Product');

view_header(['layout' => $layout ?? 'single-products']);
?>
<div class="container mx-auto px-4 py-8">
    <?php if ($p) : ?>
        <article class="prose prose-slate max-w-none" itemscope itemtype="https://schema.org/Product">
            <h1 class="text-2xl font-bold text-slate-800" itemprop="name"><?php echo h($productTitle); ?></h1>
            <?php if ($image) : ?>
                <?php $imgUrl = is_string($image) && function_exists('_img_url') ? _img_url($image, 'original') : $image; ?>
                <?php if ($imgUrl) : ?>
                    <p class="mt-2"><img src="<?php echo e($imgUrl); ?>" alt="<?php echo e($productTitle); ?>" itemprop="image" class="max-w-md rounded" /></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($price !== '') : ?>
                <p class="mt-2 text-lg text-slate-700" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                    <span itemprop="price" content="<?php echo e($price); ?>"><?php echo h($price); ?></span>
                </p>
            <?php endif; ?>
            <?php if ($sku !== '') : ?>
                <p class="text-sm text-slate-500"><?php _e('SKU'); ?>: <span itemprop="sku"><?php echo h($sku); ?></span></p>
            <?php endif; ?>
            <?php
            $content = is_array($p) ? ($p['content'] ?? $p['post_content'] ?? '') : ($p->content ?? $p->post_content ?? '');
            if ($content !== '') {
                echo $content;
            } else {
                echo '<p class="mt-2 text-slate-600">' . h($desc ?: __('No description.')) . '</p>';
            }
            ?>
        </article>
    <?php else : ?>
        <p class="text-slate-600"><?php _e('Product not found.'); ?></p>
    <?php endif; ?>
</div>
<?php view_footer(); ?>
