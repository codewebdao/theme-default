<?php
/**
 * Template: Index (default) / Trang chủ
 * Head + Schema: mặc định từ context (FrontendController). Override: Head::setTitle() / filter render.head.defaults.
 *
 * Can thiệp Schema cho trang này:
 * - add_filter('schema.context', ...)  – đổi type/payload trước khi build
 * - add_filter('schema.render', ...)  – sửa toàn bộ $schemas (thêm/xóa/sửa từng block)
 * - add_filter('schema.webpage', ...)  – sửa riêng schema WebPage (nhận $schema, $context)
 * - add_filter('schema.website', ...) – sửa riêng schema WebSite
 * - View::addSchema($array, $key)      – thêm block JSON-LD riêng (render sau schema chính)
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);
Flang::load('Home', APP_LANG);

// Ví dụ: chỉnh schema WebPage cho trang chủ (type = front) – bật lại bằng cách bỏ comment
// add_filter('schema.webpage', function ($schema, $ctx) {
//     if (($ctx->type ?? '') !== 'front') {
//         return $schema;
//     }
//     $schema['primaryImageOfPage'] = [
//         '@type' => 'ImageObject',
//         'url'   => rtrim(base_url(), '/') . '/content/uploads/og-home.jpg',
//     ];
//     return $schema;
// }, 10, 2);

// Ví dụ: thêm/sửa schema qua schema.render (toàn bộ graph trước khi ra JSON)
// add_filter('schema.render', function ($schemas, $ctx) {
//     if (($ctx->type ?? '') !== 'front') {
//         return $schemas;
//     }
//     if (isset($schemas['webpage'])) {
//         $schemas['webpage']['speakable'] = [
//             '@type' => 'SpeakableSpecification',
//             'cssSelector' => ['.hero h1', '.hero p'],
//         ];
//     }
//     return $schemas;
// }, 10, 2);

// Ví dụ: thêm block JSON-LD riêng (vd. FAQ, HowTo) – render trong Head sau schema chính
// View::addSchema([
//     '@type' => 'FAQPage',
//     'mainEntity' => [
//         ['@type' => 'Question', 'name' => '...', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => '...']],
//     ],
// ], 'faq-home');

// Assets
View::addCss('home-index', 'css/home-index.css', [], null, 'all', 'Frontend', false, false, false);
View::addJs('home-index', 'js/home-index.js', [], null, true, false, 'Frontend', true, false);

// Data
$theme = get_posts(['posttype' => 'themes', 'perPage' => 8, 'sort' => ['download', 'DESC'], 'filters' => ['status' => 'active']]) ?: [];
$plugin = get_posts(['posttype' => 'plugins', 'perPage' => 8, 'sort' => ['download', 'DESC'], 'filters' => [['status', '=', 'active']]]) ?: [];
$blogs = get_posts(['posttype' => 'blogs', 'perPage' => 6, 'lang' => APP_LANG]) ?: [];
$project = get_posts(['posttype' => 'project', 'perPage' => 8], APP_LANG) ?: [];
$reviews_vi = get_posts(['posttype' => 'review', 'perPage' => 6], APP_LANG) ?: [];

$themeData = $theme['data'] ?? [];
$pluginData = $plugin['data'] ?? [];
$blogsData = $blogs['data'] ?? [];
$projectData = $project['data'] ?? [];
$reviewsData = $reviews_vi['data'] ?? [];

$threeinone = ['tabs' => [['themes' => $themeData], ['plugins' => $pluginData]]];

// view_header: pass layout for nav highlighting (meta/schema from Head::render)
view_header(['layout' => $layout ?? 'index']);
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
