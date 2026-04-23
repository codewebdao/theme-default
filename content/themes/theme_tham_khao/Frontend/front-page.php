<?php
/**
 * Template: Front Page (Trang chủ – ưu tiên khi có file này)
 * Head + Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);
Flang::load('Home', APP_LANG);

View::addCss('home-index', 'css/home-index.css', [], null, 'all', 'Frontend', false, false, false);
View::addJs('home-index', 'js/home-index.js', [], null, true, false, 'Frontend', true, false);

$theme = get_posts(['posttype' => 'themes', 'perPage' => 8, 'sort' => ['download', 'DESC'], 'filters' => ['status' => 'active']]) ?: [];
$plugin = get_posts(['posttype' => 'plugins', 'perPage' => 8, 'sort' => ['download', 'DESC'], 'filters' => [['status', '=', 'active']]]) ?: [];
$blogs = get_posts(['posttype' => 'blogs', 'perPage' => 6, 'lang' => APP_LANG]) ?: [];
$project = get_posts(['posttype' => 'project', 'perPage' => 8]) ?: [];
$reviews_vi = get_posts(['posttype' => 'review', 'perPage' => 6]) ?: [];

$themeData = $theme['data'] ?? [];
$pluginData = $plugin['data'] ?? [];
$blogsData = $blogs['data'] ?? [];
$projectData = $project['data'] ?? [];
$reviewsData = $reviews_vi['data'] ?? [];

$threeinone = ['tabs' => [['themes' => $themeData], ['plugins' => $pluginData]]];

view_header(['layout' => $layout ?? 'front-page']);
?>

<?php
echo View::include('sections/home_index/home_index_hero');
echo View::include('sections/home_index/home_index_solution');
echo View::include('sections/home_index/home_index_why');
echo View::include('sections/home_index/home_index_perfomance');
echo View::include('sections/home_index/home_index_manager');
echo View::include('sections/home_index/home_index_file');
echo View::include('sections/home_index/home_index_multipost');
echo View::include('sections/home_index/home_index_mutilang');
echo View::include('sections/home_index/home_index_webmodern');
echo View::include('sections/home_index/home_index_fourstep');
echo View::include('sections/home_index/home_index_dev');
echo View::include('sections/home_index/home_index_move');
echo View::include('sections/home_index/home_index_service');
echo View::include('sections/home_index/home_index_project', ['projects' => ['data' => $projectData]]);
echo View::include('sections/home_index/home_index_themesplugs', $threeinone);
echo View::include('sections/home_index/home_index_partner');
echo View::include('sections/home_index/home_index_review', ['reviews' => $reviewsData]);
echo View::include('sections/home_index/home_index_blogs', ['blogs' => $blogsData]);
echo View::include('sections/home_index/home_index_faq');
echo View::include('sections/cta');
?>

<?php view_footer(); ?>
