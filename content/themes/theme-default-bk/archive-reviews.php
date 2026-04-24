<?php
/**
 * Template: Review CMS – so sánh / đánh giá CMS
 * Layout: review-cms (preload banner LCP + menu active).
 * Head/Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);

// Tabs CMS Comparison: terms + bài posttype reviews, taxonomy category-cms
$cms_comparison_terms = get_terms([
    'posttype' => 'reviews',
    'taxonomy' => 'category-cms',
    'lang'     => APP_LANG,
]) ?: [];
$cms_comparison_categories = isset($cms_comparison_terms['data'])
    ? $cms_comparison_terms['data']
    : (is_array($cms_comparison_terms) ? $cms_comparison_terms : []);

View::addCss('review-cms', 'css/review-cms.css', [], null, 'all', false, false, false);

$cms_posts_res = get_posts([
    'posttype'        => 'reviews',
    'post_status'     => 'active',
    'lang'            => APP_LANG,
    'with_categories' => true,
    'orderby'         => 'created_at',
    'order'           => 'DESC',
]) ?: [];
$cms_comparison_posts = $cms_posts_res['data'] ?? [];
if (!is_array($cms_comparison_posts)) {
    $cms_comparison_posts = [];
}

View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);
$layout = $layout ?? 'review-cms';
view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/cms/banner-cms-review');
echo View::include('parts/cms/cms-comparison', [
    'cms_comparison_categories' => $cms_comparison_categories,
    'cms_comparison_posts'      => $cms_comparison_posts,
]);
echo View::include('parts/cms/performance-showdown');
echo View::include('parts/cms/cta-banner');
?>

<?php view_footer(); ?>
