<?php
/**
 * Template: Tìm kiếm toàn site (blog)
 * URL: /search?q=... (đa ngôn ngữ qua base_url). Schema WebSite dùng cùng pattern ?q=
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('Blog', APP_LANG);

$__blogCssFs = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'blog.css';
View::addCss('blog', 'css/blog.css', [], (is_file($__blogCssFs) ? (string) filemtime($__blogCssFs) : null), 'all', false, false, false);

View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);

$layout = $layout ?? 'search';

$uriSplit = (defined('APP_URI') && is_array(APP_URI)) ? (APP_URI['split'] ?? []) : [];
$blog_search_q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($blog_search_q === '' && !empty($uriSplit[1])) {
    $blog_search_q = trim(rawurldecode((string) $uriSplit[1]));
}

$blog_per_page = 9;
$blog_paged = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$blogs_query_base = [
    'posttype'        => 'blog',
    'post_status'     => 'active',
    'lang'            => APP_LANG,
    'with_categories' => true,
    'orderby'         => 'created_at',
    'order'           => 'DESC',
];

if ($blog_search_q !== '') {
    $blogs_query_base['search'] = $blog_search_q;
    $blogs_query_base['search_columns'] = ['title', 'content'];
}

if ($blog_search_q === '') {
    $blogsAllData = [];
} else {
    $blogs_all_res = get_posts($blogs_query_base) ?: [];
    $blogsAllData = $blogs_all_res['data'] ?? [];
    if (!is_array($blogsAllData)) {
        $blogsAllData = [];
    }
}

$blogs_total = count($blogsAllData);
$blog_pagination_total_pages = max(1, (int) ceil($blogs_total / $blog_per_page));
$blog_pagination_current_page = min($blog_paged, $blog_pagination_total_pages);
$offset = ($blog_pagination_current_page - 1) * $blog_per_page;
$blogsData = array_slice($blogsAllData, $offset, $blog_per_page);

$tutorial_cats = get_terms([
    'posttype' => 'blog',
    'taxonomy' => 'category',
    'lang'     => APP_LANG,
]) ?: [];

$tutorialCategoriesData = isset($tutorial_cats['data']) ? $tutorial_cats['data'] : (is_array($tutorial_cats) ? $tutorial_cats : []);

$blog_pagination_base_url = base_url('search', APP_LANG);
$blog_pagination_query = array_filter(
    ['q' => $blog_search_q],
    static function ($v) {
        return $v !== null && $v !== '';
    }
);

$blog_empty_message = '';
if ($blog_search_q === '') {
    $blog_empty_message = (string) __('search_blogs_placeholder');
} elseif ($blogs_total === 0) {
    $blog_empty_message = (string) __('search_no_results');
}

view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/blog/blog', [
    'blog'                        => $blogsData,
    'blogsData'                   => $blogsData,
    'blogsAllData'                => $blogsAllData,
    'categories'                  => $tutorialCategoriesData,
    'blog_pagination_total_pages' => $blog_pagination_total_pages,
    'blog_pagination_current_page' => $blog_pagination_current_page,
    'blog_pagination_base_url'    => $blog_pagination_base_url,
    'blog_pagination_query'       => $blog_pagination_query,
    'blog_banner_label'           => (string) __('search_blogs_title'),
    'blog_banner_home_href'       => base_url('', APP_LANG),
    'blog_search_form_action'     => $blog_pagination_base_url,
    'blog_search_input_value'     => $blog_search_q,
    'blog_empty_message'          => $blog_empty_message,
]);
?>

<?php view_footer(); ?>
