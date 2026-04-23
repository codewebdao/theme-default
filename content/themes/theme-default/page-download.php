<?php
/**
 * Template: Usage Guide – Trang hướng dẫn sử dụng
 * Layout: usage-guide (cho menu active).
 * Head/Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('Download', APP_LANG);

View::addCss('download', 'css/download.css', [], null, 'all', false, false, false);


View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);
$layout = $layout ?? 'download';

view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/download/banner-download');
echo View::include('parts/download/choose-edition');
echo View::include('parts/download/system-requirements');
echo View::include('parts/download/release-notes');
echo View::include('parts/download/download-faqs');
?>

<?php view_footer(); ?>
