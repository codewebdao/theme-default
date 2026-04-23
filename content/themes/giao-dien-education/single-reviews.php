<?php
/**
 * Template: Chi tiết Review CMS — URL `reviews/{slug}` (posttype reviews).
 * So sánh hiệu năng + Pro/Cons + nội dung (dữ liệu cùng cấu trúc archive / cms-comparison).
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

$layout = $layout ?? 'single-reviews';
HeadContext::setCurrent($layout, $post);

Flang::load('CMS', APP_LANG);
Flang::load('Blog', APP_LANG);

// Reuse blog detail assets so review single page has consistent spacing/typography.
View::addCss('review-cms', 'css/review-cms.css', [], THEME_VER, 'all', false, false, false);
View::addJs('fast-notice', 'js/notification.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);

view_header(['layout' => $layout]);
?>

<?php echo View::include('parts/cms/review-cms-detail', ['review_post' => $post]); ?>

<?php view_footer(); ?>
