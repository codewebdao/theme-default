<?php
/**
 * Template: Usage Guide – Trang hướng dẫn sử dụng
 * Layout: usage-guide (cho menu active).
 * Head/Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('Blog', APP_LANG);

View::addCss('blog', 'css/blog.css', [], null, 'all', false, false, false);


View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);
$layout = $layout ?? 'blog';

// posttype blog: 1 lần get_posts — phân trang 9 bài/trang bằng array_slice ở theme
$blog_per_page = 9;
$blog_paged = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$blog_search_q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

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

$blogs_all_res = get_posts($blogs_query_base) ?: [];
$blogsAllData = $blogs_all_res['data'] ?? [];
if (!is_array($blogsAllData)) {
    $blogsAllData = [];
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

$blog_pagination_base_url = base_url('blog', APP_LANG);
$blog_pagination_query = array_filter(
    ['q' => $blog_search_q],
    static function ($v) {
        return $v !== null && $v !== '';
    }
);

view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/blog/blog', [
    'blog'                   => $blogsData,
    'blogsData'             => $blogsData,
    'blogsAllData'          => $blogsAllData,
    'categories'                  => $tutorialCategoriesData,
    'blog_pagination_total_pages' => $blog_pagination_total_pages,
    'blog_pagination_current_page' => $blog_pagination_current_page,
    'blog_pagination_base_url'    => $blog_pagination_base_url,
    'blog_pagination_query'       => $blog_pagination_query,
    /** Tên trang trên banner (mặc định: __('listing_banner_page') trong section) */
    'blog_banner_label'           => __('listing_banner_page'),
    'blog_banner_home_href'       => base_url('', APP_LANG),
]);
?>

<?php view_footer(); ?>
