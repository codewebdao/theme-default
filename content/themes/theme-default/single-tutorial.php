<?php
/**
 * Template: Chi tiết bài tutorial — URL `tutorial/{slug}` (posttype tutorial).
 * Bố cục: giống archive-tutorial (layout tutorial, sidebar roadmap).
 */

use System\Libraries\Render\View;
use System\Libraries\Render\Head\Context as HeadContext;
use App\Libraries\Fastlang as Flang;

global $post;

if (empty($post) || !is_array($post)) {
    http_response_code(404);
    echo View::make('404', ['layout' => '404'])->render();
    exit;
}

$layout = $layout ?? 'single-tutorial';
HeadContext::setCurrent($layout, $post);
$postSlug = trim((string) ($post['slug'] ?? ''));

Flang::load('CMS', APP_LANG);

require __DIR__ . '/parts/tutorial/tutorial-sidebar-data.php';

$php_tutorial_current_topic = $postSlug;

View::addCss('home-index', 'css/index.css', [], null, 'all', false, false, false);
View::addCss('tutorial', 'css/tutorial.css', [], null, 'all', false, false, false);
View::addJs('home-index', 'js/tabs.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/sliders.js', [], null, true, false, true, false);
View::addCss('faq-accordion', 'css/faq-accordion.css', [], null, 'all', false, false, false);
View::addJs('faq-accordion', 'js/faq-accordion.js', [], null, true, false, true, false);
View::addJs('home-index', 'js/index.js', [], null, true, false, true, false);

view_header(['layout' => $layout]);
$php_tutorial_json_flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
$php_tutorial_lesson_urls = [];
if (is_array($php_tutorial_valid_topics)) {
    foreach ($php_tutorial_valid_topics as $topicSlug) {
        $topicSlug = (string) $topicSlug;
        if ($topicSlug === '') {
            continue;
        }
        $php_tutorial_lesson_urls[$topicSlug] = link_posts($topicSlug, 'tutorial', APP_LANG);
    }
}
$php_tutorial_client_cfg = [
    'topicToPhase'       => is_array($php_tutorial_topic_to_phase) ? $php_tutorial_topic_to_phase : [],
    'defaultPhase'       => (string) $php_tutorial_default_phase,
    'firstTopic'         => $php_tutorial_valid_topics[0] ?? 'syntax',
    'phaseLabelMap'      => is_array($php_tutorial_topic_breadcrumb) ? $php_tutorial_topic_breadcrumb : [],
    'lessonTitleMap'     => is_array($php_tutorial_lesson_titles) ? $php_tutorial_lesson_titles : [],
    'lessonOrder'        => array_values(is_array($php_tutorial_valid_topics) ? $php_tutorial_valid_topics : []),
    'lessonUrls'         => $php_tutorial_lesson_urls,
    'breadcrumbFallback' => (string) $php_tutorial_breadcrumb_fallback,
    'currentTopic'       => (string) $php_tutorial_current_topic,
];
$php_tutorial_client_json = json_encode($php_tutorial_client_cfg, $php_tutorial_json_flags);
if ($php_tutorial_client_json === false) {
    $php_tutorial_client_json = '{}';
}
?>
<script>
(function () {
  var d = <?php echo $php_tutorial_client_json; ?>;
  window.__PHP_TUTORIAL_DEFAULT_TOPIC__ = (d && d.currentTopic) ? d.currentTopic : ((d && d.firstTopic) ? d.firstTopic : 'syntax');
  window.__PHP_TUTORIAL_XDATA__ = d || {};
})();
</script>
<?php
echo View::include('parts/tutorial/content-tutorial-single', [
    'tutorial_post'                    => $post,
    'php_tutorial_phases'              => $php_tutorial_phases,
    'php_tutorial_topic_to_phase'      => $php_tutorial_topic_to_phase,
    'php_tutorial_topic_breadcrumb'    => $php_tutorial_topic_breadcrumb,
    'php_tutorial_lesson_titles'       => $php_tutorial_lesson_titles,
    'php_tutorial_lesson_descriptions' => $php_tutorial_lesson_descriptions,
    'php_tutorial_valid_topics'        => $php_tutorial_valid_topics,
    'php_tutorial_default_topic'       => $php_tutorial_default_topic,
    'php_tutorial_default_phase'       => $php_tutorial_default_phase,
    'php_tutorial_breadcrumb_fallback' => $php_tutorial_breadcrumb_fallback,
    'php_tutorial_current_topic'       => $php_tutorial_current_topic,
    'php_tutorial_sidebar_link_mode'   => 'permalink',
]);

view_footer();
