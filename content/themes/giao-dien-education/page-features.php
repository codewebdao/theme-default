<?php
/**
 * Template: Features – Trang tính năng (Under the Hood)
 * Layout: features (cho menu active).
 * Head/Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;


Flang::load('Features', APP_LANG);
Flang::load('Home', APP_LANG);
// Assets (dùng chung design system với trang chủ)
View::addCss('home-index', 'css/features.css', [], THEME_VER, 'all', false, false, false);
View::addCss('features-scroll-reveal', 'css/features-scroll-reveal.css', [], THEME_VER, 'all', false, false, false);
View::addJs('features-scroll-reveal', 'js/features-scroll-reveal.js', [], null, true, false, true, false);

$layout = $layout ?? 'features';
view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/features/banner-features');
echo View::include('parts/features/fast-portable');
echo View::include('parts/features/developer-toolkit');
echo View::include('parts/features/management-security');
?>

<?php view_footer(); ?>
