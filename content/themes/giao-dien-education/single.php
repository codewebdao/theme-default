<?php
/**
 * Template: Blog detail — URL `blog/{slug}` (posttype blog), khớp FrontendController posttype/slug.
 * Head: context payload ghi đè bằng bài viết để Builder dùng seo_title / seo_desc / OG.
 */

use System\Libraries\Render\View;
use System\Libraries\Render\Head\Context as HeadContext;
use App\Libraries\Fastlang as Flang;

global $post;
if (empty($post) || !is_array($post)) {
    http_response_code(404);
    echo View::make('404', ['layout' => '404'])->render();
    exit;
}

$layout = $layout ?? 'single';
HeadContext::setCurrent($layout, $post);

Flang::load('Blog', APP_LANG);

View::addCss('blog-detail', 'css/blog-detail.css', [], THEME_VER, 'all', false, false, false);

View::addJs('fast-notice', 'js/notification.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);
view_header(['layout' => $layout]);
?>

<?php echo View::include('parts/blog/blog-detail', ['blog_post' => $post]); ?>

<?php view_footer(); ?>
