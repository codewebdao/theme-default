<?php
/**
 * Template: Usage Guide – Trang hướng dẫn sử dụng
 * Layout: usage-guide (cho menu active).
 * Head/Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('Contact', APP_LANG);

View::addCss('contact', 'css/contact.css', [], null, 'all', false, false, false);


View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);
$layout = $layout ?? 'contact';

view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/contact/contact');
?>

<?php view_footer(); ?>
