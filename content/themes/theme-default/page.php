<?php
/**
 * Template: Trang CMS (posttype pages) — fallback mặc định.
 *
 * Hierarchy (FrontendController::getPageTemplate):
 * page-{slug}.php > page-{id}.php > page-{template}.php > page.php > singular.php > index.php
 *
 * SEO: theme Frontend/functions.php (filter render.head.defaults + seo_config theo slug).
 *
 * Khi CMS tạo page mà không có file theme trùng tên ở trên, nội dung được render tại đây.
 */

use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

global $page;

if (!is_array($page)) {
    $page = [];
}

/*
 * Page CMS trùng slug với posttype (vd. bản vi có page slug "blog") → có thể vào page.php thay vì archive.
 * Ưu tiên sửa ở FrontendController::_detectLayout(); đoạn dưới là lưới an toàn theme.
 */
$pageSlug = trim((string) ($page['slug'] ?? ''), '/');
$posttypeKey = str_replace('-', '_', $pageSlug);
if ($pageSlug !== '' && posttype_lang_exists($posttypeKey, defined('APP_LANG') ? APP_LANG : '')) {
    $archivePath = __DIR__ . DIRECTORY_SEPARATOR . 'archive-' . $posttypeKey . '.php';
    if (is_file($archivePath)) {
        if ($posttypeKey === 'blog') {
            $layout = 'blog';
        } elseif ($posttypeKey === 'reviews') {
            $layout = 'reviews';
        }
        require $archivePath;
        exit;
    }
}

Flang::load('CMS', defined('APP_LANG') ? APP_LANG : '');

View::addCss('page-scroll-reveal', 'css/features-scroll-reveal.css', [], null, 'all', false, false, false);
View::addJs('page-scroll-reveal', 'js/features-scroll-reveal.js', [], null, true, false, true, false);
View::addCss('page-cms', 'css/page.css', [], null, 'all', false, false, false);
View::addCss('home-index', 'css/index.css', [], null, 'all', false, false, false);

View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);

$layout = $layout ?? 'page';
view_header(['layout' => $layout]);
?>

<main class="min-h-screen w-full bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    <?php echo View::include('parts/page/page-hero-banner', ['page' => $page]); ?>
    <div class="border-t border-gray-100 bg-white dark:border-zinc-800 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-10 sm:px-6 sm:py-12 md:py-16 lg:px-8">
            <div id="main-content" class="content-seo cms-page-body sr sr--fade-up mx-auto w-full max-w-4xl overflow-x-auto text-balance" role="article" style="--sr-delay: 0ms">
            <?php
            $raw = (string) ($page['content'] ?? $page['post_content'] ?? '');
            if ($raw !== '') {
                echo html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                ?>
                <p class="page-empty-msg font-plus text-sm text-gray-600 sm:text-base dark:text-gray-400">
                    <?php echo e(__('page.empty_content')); ?>
                </p>
                <?php
            }
            ?>
            </div>
        </div>
    </div>
</main>

<?php view_footer(); ?>
