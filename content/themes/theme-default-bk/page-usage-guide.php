<?php
/**
 * Template: Usage Guide – Trang hướng dẫn sử dụng
 * Layout: usage-guide (cho menu active).
 * Head/Schema: mặc định từ context. Override: Head::setTitle() / filter render.head.defaults.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

Flang::load('CMS', APP_LANG);
Flang::load('Documentation', APP_LANG);

// Cùng posttype `tutorial` như PHP Tutorial; cột `type` = usage_guide (PHP Tutorial dùng `tutorial`).
$usage_guide_posts_res = get_posts([
    'posttype'        => 'tutorial',
    'post_status'     => 'active',
    'lang'            => APP_LANG,
    'perPage'         => 500,
    'orderby'         => 'id',
    'order'           => 'ASC',
    'with_categories' => true,
    'filters'         => [
        ['type_option', 'usage_guide'],
    ],
]) ?: [];
$usage_guide_posts = $usage_guide_posts_res['data'] ?? [];
if (!is_array($usage_guide_posts)) {
    $usage_guide_posts = [];
}

View::addCss('home-index', 'css/index.css', [], null, 'all', false, false, false);
// View::addCss('usage-guide', 'css/usage-guide.css', [], null, 'all', false, false, false);

$layout = $layout ?? 'usage-guide';
view_header(['layout' => $layout]);
?>

<?php
echo View::include('parts/documentation/documentation-tabs', [
    'usage_guide_posts' => $usage_guide_posts,
]);
?>

<?php view_footer(); ?>
