<?php
/**
 * Template: Index (default) / Trang chủ
 * Head + Schema: mặc định từ context (FrontendController) → view_head() xuất JSON-LD (WebSite, Organization, BreadcrumbList, WebPage).
 * Bổ sung giống file meta_index.php (SchemaGraph): dùng add_filter trong Frontend/functions.php (schema.organization, schema.webpage).
 * Không echo thêm SchemaGraph ở đây — sẽ trùng thẻ application/ld+json.
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
View::addCss('home-scroll-reveal', 'css/features-scroll-reveal.css', [], null, 'all', false, false, false);
View::addJs('home-scroll-reveal', 'js/features-scroll-reveal.js', [], null, true, false, true, false);
View::addCss('home-index', 'css/index.css', [], null, 'all', false, false, false);
View::addCss('see-how-yt', 'css/see-how-yt.css', [], null, 'all', false, false, false);
View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addCss('faq-accordion', 'css/faq-accordion.css', [], null, 'all', false, false, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);

// Data
$reviews_vi = get_posts(['posttype' => 'customer', 'perPage' => 6], APP_LANG) ?: [];
$tutorials_vi = get_posts(['posttype' => 'blog', 'perPage' => 6, 'with_categories' => true], APP_LANG) ?: [];
// terms API: ?posttype=tutorials&type=category&post_lang=...
$tutorial_categories = get_terms(['posttype' => 'blog', 'taxonomy' => 'category', 'lang' => APP_LANG]) ?: [];

$reviewsData = $reviews_vi['data'] ?? [];
$tutorialsData = $tutorials_vi['data'] ?? [];
$tutorialCategoriesData = isset($tutorial_categories['data']) ? $tutorial_categories['data'] : (is_array($tutorial_categories) ? $tutorial_categories : []);

// view_header: pass layout for nav highlighting (meta/schema from Head::render)
view_header(['layout' => $layout ?? 'index']);
?>

<?php
echo View::include('parts/home/banner-home');
echo View::include('parts/home/performance');
echo View::include('parts/home/features');
echo View::include('parts/home/supported');
echo View::include('parts/home/loved-by-developers', ['reviews' => $reviewsData]);
echo View::include('parts/home/get-started');
echo View::include('parts/home/see-how-works');
echo View::include('parts/home/tutorials', ['tutorials' => $tutorialsData, 'categories' => $tutorialCategoriesData]);
echo View::include('parts/home/laragon-academy');
echo View::include('parts/home/frequently-asked-questions');
echo View::include('parts/home/choose-your-edition');

?>

<?php view_footer(); ?>
