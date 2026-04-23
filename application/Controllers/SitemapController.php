<?php

namespace App\Controllers;

use System\Core\BaseController;

/**
 * Sitemap XML — index + urlset theo ngôn ngữ (APP_LANGUAGES), trang CMS (pages) và bài blogs (blog-detail/{slug}).
 *
 * Routes: sitemap.xml (index), sitemap-{main|en|vi|...}.xml (urlset).
 * Mở rộng: add_filter('sitemap_index_segments', ...), sitemap_urlset_registry, sitemap_blog_post_path.
 */
class SitemapController extends BaseController
{
    /** @var int Cache TTL (giây) */
    protected $cacheTtl = 3600;

    /** @var string */
    protected $cacheDir;

    public function __construct()
    {
        parent::__construct();
        $this->cacheDir = PATH_WRITE . 'cache/sitemap/';
    }

    /**
     * /sitemap.xml → index | /sitemap-{type}.xml → urlset (main, vi, pages, …)
     */
    public function index($type = null)
    {
        load_helpers(['string', 'languages', 'query', 'hooks', 'uri']);

        $cacheKey = $type ?: 'index';
        $xml = $this->getCache($cacheKey);
        if ($xml === null) {
            $xml = $type ? $this->buildUrlset($type) : $this->buildIndex();
            $this->setCache($cacheKey, $xml);
        }

        $this->outputXml($xml);
    }

    public function page()
    {
        $this->index('pages');
    }

    protected function outputXml($xml)
    {
        header('Content-Type: application/xml; charset=UTF-8');
        echo $xml;
        exit;
    }

    protected function getCache($key)
    {
        $file = $this->cacheDir . preg_replace('/[^a-z0-9_-]/i', '', $key) . '.xml';
        if (!file_exists($file)) {
            return null;
        }
        if (time() - filemtime($file) > $this->cacheTtl) {
            @unlink($file);

            return null;
        }

        return file_get_contents($file);
    }

    protected function setCache($key, $content)
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        $file = $this->cacheDir . preg_replace('/[^a-z0-9_-]/i', '', $key) . '.xml';
        file_put_contents($file, $content, LOCK_EX);
    }

    /**
     * Gốc site — ưu tiên config app_url (khớp base_url), fallback HTTP_HOST.
     */
    protected function siteOrigin(): string
    {
        $fromConfig = '';
        if (function_exists('config')) {
            $fromConfig = trim((string) config('app_url'));
        }
        if ($fromConfig !== '') {
            return rtrim($fromConfig, '/');
        }

        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    /**
     * Các file con trong sitemap index: main (ngôn ngữ mặc định) + mỗi mã trong APP_LANGUAGES (trừ default) + pages (tùy registry).
     */
    protected function sitemapIndexSegments(): array
    {
        $segments = ['main'];
        foreach (array_keys(APP_LANGUAGES) as $code) {
            if ($code !== '' && $code !== APP_LANG_DF && isset(APP_LANGUAGES[$code])) {
                $segments[] = $code;
            }
        }
        if (function_exists('apply_filters')) {
            $filtered = apply_filters('sitemap_index_segments', $segments);
            if (is_array($filtered) && $filtered !== []) {
                return array_values(array_unique(array_map('strval', $filtered)));
            }
        }

        return $segments;
    }

    protected function buildIndex()
    {
        $origin = $this->siteOrigin();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->sitemapIndexSegments() as $t) {
            $t = preg_replace('/[^a-z0-9_-]/i', '', $t);
            if ($t === '') {
                continue;
            }
            $loc = $origin . '/sitemap-' . $t . '.xml';
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
            $xml .= '  </sitemap>' . "\n";
        }
        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * @return array<string, string> key → handler (main_pages | all_pages | method name)
     */
    protected function urlsetRegistry(): array
    {
        $map = [
            'main'  => 'main_pages',
            'pages' => 'all_pages',
        ];
        if (function_exists('apply_filters')) {
            $filtered = apply_filters('sitemap_urlset_registry', $map);
            if (is_array($filtered)) {
                return $filtered;
            }
        }

        return $map;
    }

    protected function renderUrlsetBody(string $type): string
    {
        if (isset(APP_LANGUAGES[$type]) && $type !== APP_LANG_DF) {
            return $this->urlsForLanguage($type, true);
        }

        $registry = $this->urlsetRegistry();
        $handler = $registry[$type] ?? null;
        if ($handler === null || $handler === '') {
            return '';
        }
        if ($handler === 'main_pages') {
            return $this->urlsForLanguage(APP_LANG_DF, false);
        }
        if ($handler === 'all_pages') {
            return $this->urlsAllLanguagesPages();
        }
        if (is_string($handler) && method_exists($this, $handler)) {
            return $this->{$handler}($this->siteOrigin());
        }

        return '';
    }

    protected function buildUrlset($type)
    {
        $type = is_string($type) ? preg_replace('/[^a-z0-9_-]/i', '', $type) : '';
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= $this->renderUrlsetBody($type);
        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Slug trang xuất hiện trước trong sitemap — chỉ khi đã có bản ghi `pages` active (không tạo URL “ảo”).
     * Mặc định rỗng; thêm slug qua filter `sitemap_priority_page_slugs` nếu muốn sắp thứ tự.
     */
    protected function priorityPageSlugs(): array
    {
        $slugs = [];
        if (function_exists('apply_filters')) {
            $filtered = apply_filters('sitemap_priority_page_slugs', $slugs);
            if (is_array($filtered)) {
                return $filtered;
            }
        }

        return $slugs;
    }

    /**
     * URL public của một trang (slug) theo ngôn ngữ — dùng base_url giống frontend.
     */
    protected function publicPageLoc(string $slug, string $lang): string
    {
        $slug = trim($slug, '/');
        if ($slug === '') {
            return rtrim(base_url('', $lang), '/');
        }

        return rtrim(base_url($slug, $lang), '/');
    }

    /**
     * Prefix path bài blog (trang CMS dùng blog-detail/{slug}).
     */
    protected function blogPostPathPrefix(): string
    {
        $prefix = 'blog-detail/';
        if (function_exists('apply_filters')) {
            $filtered = apply_filters('sitemap_blog_post_path_prefix', $prefix);
            if (is_string($filtered) && $filtered !== '') {
                return rtrim($filtered, '/') . '/';
            }
        }

        return $prefix;
    }

    /**
     * URL bài posttype blogs.
     */
    protected function publicBlogPostLoc(string $postSlug, string $lang): string
    {
        $postSlug = trim($postSlug, '/');
        if ($postSlug === '') {
            return '';
        }
        $path = $this->blogPostPathPrefix() . $postSlug;

        return rtrim(base_url($path, $lang), '/');
    }

    /**
     * Trang chủ + pages + bài blogs cho một ngôn ngữ.
     */
    protected function urlsForLanguage(string $lang, bool $useLangPrefix): string
    {
        unset($useLangPrefix);

        $data = get_posts([
            'posttype'         => 'pages',
            'post_status'      => 'active',
            'posts_per_page'   => 50000,
            'lang'             => $lang,
            'orderby'          => 'updated_at',
            'order'            => 'DESC',
        ]);
        $pages = $data['data'] ?? [];
        if (!is_array($pages)) {
            $pages = [];
        }

        $bySlug = [];
        foreach ($pages as $p) {
            if (!is_array($p)) {
                continue;
            }
            $slug = trim((string) ($p['slug'] ?? ''), '/');
            if ($slug !== '') {
                $bySlug[strtolower($slug)] = $p;
            }
        }

        $out = $this->urlXml($this->publicPageLoc('', $lang), null, 'daily', '1.0');

        $emitted = [];
        foreach ($this->priorityPageSlugs() as $slug) {
            $slug = trim((string) $slug, '/');
            if ($slug === '') {
                continue;
            }
            $key = strtolower($slug);
            $p = $bySlug[$key] ?? null;
            if (!is_array($p)) {
                continue;
            }
            $lastmod = $p['updated_at'] ?? null;
            $loc = $this->publicPageLoc($slug, $lang);
            $out .= $this->urlXml($loc, $lastmod, 'weekly', '0.8');
            $emitted[$key] = true;
        }

        foreach ($pages as $p) {
            if (!is_array($p)) {
                continue;
            }
            $slug = trim((string) ($p['slug'] ?? ''), '/');
            if ($slug === '') {
                continue;
            }
            $key = strtolower($slug);
            if (isset($emitted[$key])) {
                continue;
            }
            if (in_array($key, ['trang-chu', 'home', 'blog-detail'], true)) {
                continue;
            }
            $loc = $this->publicPageLoc($slug, $lang);
            $out .= $this->urlXml($loc, $p['updated_at'] ?? null, 'weekly', '0.8');
        }

        $out .= $this->urlsBlogPostsForLang($lang);

        return $out;
    }

    /**
     * Bài viết posttype `blogs` (đường dẫn blog-detail/{slug}).
     */
    protected function urlsBlogPostsForLang(string $lang): string
    {
        if (!function_exists('posttype_lang_exists') || !posttype_lang_exists('blogs', $lang)) {
            return '';
        }

        $data = get_posts([
            'posttype'         => 'blogs',
            'post_status'      => 'active',
            'posts_per_page'   => 50000,
            'lang'             => $lang,
            'orderby'          => 'updated_at',
            'order'            => 'DESC',
        ]);
        $rows = $data['data'] ?? [];
        if (!is_array($rows)) {
            return '';
        }

        $out = '';
        foreach ($rows as $post) {
            if (!is_array($post)) {
                continue;
            }
            $slug = trim((string) ($post['slug'] ?? ''), '/');
            if ($slug === '') {
                continue;
            }
            $loc = $this->publicBlogPostLoc($slug, $lang);
            if ($loc === '') {
                continue;
            }
            $out .= $this->urlXml($loc, $post['updated_at'] ?? null, 'weekly', '0.7');
        }

        return $out;
    }

    protected function urlsAllLanguagesPages(): string
    {
        $out = $this->urlsForLanguage(APP_LANG_DF, false);
        foreach (array_keys(APP_LANGUAGES) as $code) {
            if ($code === APP_LANG_DF) {
                continue;
            }
            $out .= $this->urlsForLanguage($code, true);
        }

        return $out;
    }

    protected function urlXml($loc, $lastmod = null, $changefreq = 'weekly', $priority = '0.7')
    {
        $lastmodTag = '';
        if ($lastmod !== null && $lastmod !== '') {
            $ts = strtotime((string) $lastmod);
            if ($ts !== false) {
                $lastmodTag = '<lastmod>' . gmdate('c', $ts) . '</lastmod>';
            }
        }
        $locEsc = htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '  <url><loc>' . $locEsc . '</loc>' . $lastmodTag
            . '<changefreq>' . htmlspecialchars((string) $changefreq, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</changefreq>'
            . '<priority>' . htmlspecialchars((string) $priority, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</priority></url>' . "\n";
    }
}
