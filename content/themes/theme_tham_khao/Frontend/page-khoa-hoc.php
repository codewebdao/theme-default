<?php
/**
 * Template: Khóa học (Courses)
 * URL: /khoa-hoc (page slug) hoặc tương đương
 * Hiển thị meta, schema Course/ItemList, assets theo chuẩn Auth + Frontend@.
 */
use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;
use App\Blocks\Meta\MetaBlock;
use App\Blocks\Schema\SchemaGraph;
use App\Blocks\Schema\Templates\WebPage;
use App\Blocks\Schema\Templates\BreadcrumbList;
use App\Blocks\Schema\Templates\Course;

Flang::load('CMS', APP_LANG);
Flang::load('Frontend', APP_LANG);

global $page;
$pageTitle = isset($page['post_title']) ? $page['post_title'] : (isset($page['title']) ? $page['title'] : __('Courses'));
$pageDesc = isset($page['post_excerpt']) ? $page['post_excerpt'] : (isset($page['description']) ? $page['description'] : __('List of courses and learning programs.'));
if (empty($pageDesc) && !empty($page['post_content'])) {
    $pageDesc = mb_substr(strip_tags($page['post_content']), 0, 160);
}
if (empty($pageDesc)) {
    $pageDesc = __('List of courses and learning programs.');
}
$coursesUrl = base_url('khoa-hoc');
$siteName = option('site_title', APP_LANG);

// ─── Assets (head + footer) ─────────────────────────────────────────────
View::addCss('courses-page', 'css/courses.min.css', [], null, 'all', 'Frontend', false, false, false);
View::addJs('courses-page', 'js/courses.js', [], null, true, false, 'Frontend', true, false);

// ─── Meta ───────────────────────────────────────────────────────────────
$meta = new MetaBlock();
$meta
    ->title($pageTitle . ' | ' . $siteName)
    ->description($pageDesc)
    ->robots('index, follow')
    ->canonical($coursesUrl);
$meta
    ->custom('<meta name="generator" content="CMSFullForm">')
    ->custom('<meta name="language" content="' . APP_LANG . '">');
$locale = APP_LANG === 'en' ? 'en_US' : 'vi_VN';
$meta
    ->og('locale', $locale)
    ->og('type', 'website')
    ->og('title', $pageTitle)
    ->og('description', $pageDesc)
    ->og('url', $coursesUrl)
    ->og('site_name', $siteName)
    ->og('updated_time', date('c'));
$meta
    ->twitter('card', 'summary_large_image')
    ->twitter('title', $pageTitle)
    ->twitter('description', $pageDesc)
    ->twitter('site', '@' . $siteName);

// ─── Schema (JSON-LD) ──────────────────────────────────────────────────
$schemaGraph = new SchemaGraph(base_url());

$webPageSchema = new WebPage([
    'url' => $coursesUrl,
    'name' => $pageTitle,
    'description' => $pageDesc
]);
$schemaGraph->addItem($webPageSchema);

$breadcrumbItems = [
    ['name' => $siteName, 'url' => base_url()],
    ['name' => $pageTitle, 'url' => $coursesUrl]
];
$breadcrumbSchema = new BreadcrumbList([
    'url' => $coursesUrl . '#breadcrumb',
    'items' => $breadcrumbItems
]);
$schemaGraph->addItem($breadcrumbSchema);

// Schema Course (trang danh sách: ItemList hoặc từng Course)
$courseSchema = new Course([
    'name' => $pageTitle,
    'description' => $pageDesc,
    'image' => option('site_logo') ? (is_string(option('site_logo')) ? option('site_logo') : theme_assets('images/logo/logo-icon.webp')) : '',
    'courseCode' => 'KH',
    'educationalCredentialAwarded' => __('Certificate'),
    'url' => $coursesUrl
]);
$schemaGraph->addItem($courseSchema);

// ─── Header (meta + schema + view_head + append) ────────────────────────
view_header([
    'meta' => $meta->render(),
    'schema' => $schemaGraph->render(),
    'append' => '<link rel="preload" href="' . theme_assets('css/courses.min.css') . '" as="style" type="text/css" media="all" />'
]);
?>

<main class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-10">
        <!-- Breadcrumb (hiển thị) -->
        <nav class="text-sm text-slate-500 mb-6" aria-label="Breadcrumb">
            <ol class="flex flex-wrap gap-2">
                <li><a href="<?= base_url(); ?>" class="hover:text-blue-600"><?= $siteName; ?></a></li>
                <li aria-hidden="true">/</li>
                <li class="text-slate-800 font-medium"><?= htmlspecialchars($pageTitle); ?></li>
            </ol>
        </nav>

        <header class="mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900"><?= htmlspecialchars($pageTitle); ?></h1>
            <?php if ($pageDesc): ?>
                <p class="mt-2 text-lg text-slate-600"><?= htmlspecialchars($pageDesc); ?></p>
            <?php endif; ?>
        </header>

        <!-- Danh sách khóa học (placeholder – có thể lấy từ $courses hoặc get_posts) -->
        <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" aria-label="<?php _e('Courses list'); ?>">
            <?php
            $courses = isset($courses) && is_array($courses) ? $courses : [];
            if (empty($courses)):
            ?>
                <div class="col-span-full rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                    <p><?php _e('No courses yet. Coming soon.'); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
                        <?php if (!empty($course['image'])): ?>
                            <div class="aspect-video rounded-lg overflow-hidden mb-4">
                                <?= _img($course['image'], $course['title'] ?? '', false, 'w-full h-full object-cover'); ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($course['title'] ?? ''); ?></h2>
                        <?php if (!empty($course['excerpt'])): ?>
                            <p class="mt-2 text-slate-600 line-clamp-2"><?= htmlspecialchars($course['excerpt']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($course['url'])): ?>
                            <a href="<?= htmlspecialchars($course['url']); ?>" class="mt-4 inline-block text-blue-600 font-medium hover:underline">
                                <?php _e('View course'); ?>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php view_footer(); ?>
