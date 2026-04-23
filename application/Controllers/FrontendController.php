<?php

namespace App\Controllers;

use System\Core\BaseController;
use System\Core\AppException;
use System\Drivers\Cache\UriCache;
use System\Libraries\Render\View;
use System\Libraries\Render\Schema;
use System\Libraries\Render\Theme\ThemeContext;
use App\Libraries\Fastlang as Flang;

class FrontendController extends BaseController
{
    public $query;
    public $cachingDefaultLevel;
    public function __construct()
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('FrontendController::__construct');
        }
        parent::__construct();
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('FrontendController::__construct');
        }
    }

    public function index($layout = '', ...$params)
    {
        ThemeContext::setScope('web');
        ThemeContext::setTheme(defined('APP_THEME_WEB_NAME') ? APP_THEME_WEB_NAME : (defined('APP_THEME_NAME') ? APP_THEME_NAME : 'default'), 'web');
        View::scope('web');

        load_helpers(['string', 'languages', 'frontend', 'themes', 'links', 'images', 'query', 'hooks', 'view', 'security']);
        $this->cachingDefaultLevel = option('cache_gzip') ?? 0; //Value now is: 5

        // Hook: before_detect_layout
        do_action('before_detect_layout', $params);

        $layout = $this->_detectLayout();

        //Disable Cache for Preview Mode
        $cache = $this->caching();
        if (HAS_GET('preview')) {
            if (in_array($layout, ['singular', 'single', 'page']) || strpos($layout, 'single-') !== false || strpos($layout, 'page-') !== false) {
                global $post_preview;
                $post_preview = true;
                $cache = false;
            }
        }

        // Filter: detect_layout - Allow plugins to override detected layout
        $layout = apply_filters('detect_layout', $layout, $params);

        // Hook: after_detect_layout
        do_action('after_detect_layout', $layout, $params);

        // Hook: before_cache_get
        do_action('before_cache_get', $layout, $cache);

        $cachedata = $cache ? $cache->get() : false;

        // Filter: cache_data - Allow plugins to modify cached data
        if ($cachedata) {
            $cachedata = apply_filters('cache_data', $cachedata, $layout);
        }

        // Hook: after_cache_get
        do_action('after_cache_get', $layout, $cachedata);

        if (empty($cachedata)) {
            Flang::load('CMS', APP_LANG);
            $this->_loadFunctions();

            $this->data['params'] = $params;

            // Build render path (theme root; scope web đã set ở constructor)
            $renderPath = $layout; 

            // Filter: render_path - Allow plugins to change render file path
            $renderPath = apply_filters('render_path', $renderPath, $layout, $this->data);

            // Filter: render_data - Allow plugins to modify data before render
            $this->data['layout'] = $layout;
            $this->data = apply_filters('render_data', $this->data, $layout, $renderPath);

            // Set Schema library current context (dùng trong Head::render() qua Schema::get())
            $schemaContext = $this->getSchemaContextForLayout($layout);
            if (function_exists('apply_filters')) {
                $schemaContext = apply_filters('schema.context', $schemaContext, $layout, $this->data);
            }
            if ($schemaContext !== null && !empty($schemaContext['type'])) {
                Schema::setCurrentContext($schemaContext['type'], $schemaContext['payload'] ?? null);
            }
            // Set Head context (title, meta, OG, canonical) – cùng layout + payload, Head::render() build mặc định
            \System\Libraries\Render\Head\Context::setCurrent($layout, $schemaContext !== null ? ($schemaContext['payload'] ?? null) : null);

            // Hook: before_render - Execute before rendering
            do_action('before_render', $renderPath, $this->data, $layout);

            // ✅ NEW: Use View library
            // Set web scope namespace if not already set by plugin hook
            if (View::getNamespace() === null) {
                View::namespace('web');
            }
            $result = View::make($renderPath, $this->data)->render();

            // Filter: render_output - Allow plugins to modify rendered HTML
            $result = apply_filters('render_output', $result, $layout, $renderPath, $this->data);

            // Minify HTML (option minify_html on) – qua View::minify (Minify library)
            $minifyHtml = option('minify_html');
            if ($minifyHtml && $minifyHtml !== '0' && $minifyHtml !== false) {
                $result = View::minify($result);
            }

            // Hook: after_render - Execute after rendering
            do_action('after_render', $result, $layout, $renderPath, $this->data);

            // cache
            if ($cache) {
                // Filter: cache_output - Allow plugins to modify output before caching
                $cacheOutput = apply_filters('cache_output', $result, $layout);
                // Hook: before_cache_set
                do_action('before_cache_set', $cacheOutput, $layout);
                $cachedata = $cache->set($cacheOutput, true);
                do_action('after_cache_set', $cachedata, $layout);
            } else {
                echo $result;
                return;
            }
        }

        // Filter: final_output - Final filter before output (for both cached and non-cached)
        $finalOutput = apply_filters('final_output', $cachedata, $layout);

        // Hook: before_output
        do_action('before_output', $finalOutput, $layout);

        if ($cache) {
            $cache->render($finalOutput);
        } else {
            echo $finalOutput;
        }

        // Hook: after_output
        do_action('after_output', $finalOutput, $layout);
    }

    protected function caching($functionName = 'FrontendController::index')
    {
        // Hook: before_caching
        do_action('before_caching', $functionName);

        $cacheConfig = _json_decode(option('cache_config'));
        // Array ( [0] => Array ( [cache_function] => FrontendController::index [cache_caching] => 1 [cache_mobile] => 1 [cache_login] => 1 [cache_level] => 5 [cache_type] => html [cache_clear_time] => 3600 ) )
        if (!empty($cacheConfig)) {
            // Filter: cache_config - Allow plugins to modify cache config
            $cacheConfig = apply_filters('cache_config', $cacheConfig, $functionName);

            $config = [];
            foreach ($cacheConfig as $cache) {
                if ($cache['cache_function'] == $functionName) {
                    $config = $cache;
                    break;
                }
            }

            // Filter: cache_config_for_layout - Allow plugins to modify config for specific layout
            $config = apply_filters('cache_config_for_layout', $config, $functionName);

            if (isset($config['cache_caching']) && $config['cache_caching']) {
                if (empty($config['cache_level']) || $config['cache_level'] == 'default') {
                    $config['cache_level'] = $this->cachingDefaultLevel;
                }

                // Filter: cache_level - Allow plugins to modify cache level
                $config['cache_level'] = apply_filters('cache_level', $config['cache_level'], $functionName);

                $cache = new UriCache($config['cache_level'], $config['cache_type']);
                $cache->cacheLogin($config['cache_login'] ?? 0);
                $cache->cacheMobile($config['cache_mobile'] ?? 0);

                // Hook: after_caching_init
                do_action('after_caching_init', $cache, $config, $functionName);

                return $cache;
            }
        }
        // Hook: caching_disabled
        do_action('caching_disabled', $functionName);
        return false;
    }

    /**
     * Detect layout based on WordPress Template Hierarchy
     * Uses Database_helper.php functions for data validation
     * Supports default posttype feature
     * 
     * @return string Layout name
     */
    protected function _detectLayout()
    {
        // Get URI segments
        $segments = APP_URI['split'] ?? [];

        // Filter: uri_segments - Allow plugins to modify URI segments
        $segments = apply_filters('uri_segments', $segments);

        // Homepage (no segments)
        if (empty($segments)) {
            $layout = $this->templateExists('front-page') ? 'front-page' : 'index';
            // Filter: homepage_layout
            return apply_filters('homepage_layout', $layout);
        }

        $segmentCount = count($segments);
        $firstSegment = $segments[0];


        // 1. Search Results
        if ($firstSegment === 'search') {
            return $this->getSearchTemplate($segments);
        }
        // 2. 404 Error
        if ($firstSegment === '404') {
            return $this->templateExists('404') ? '404' : 'index';
        }
        // 3. Author Archives
        if ($firstSegment === 'author') {
            $authorSlug = $segments[1] ?? null;
            return $this->getAuthorTemplate($authorSlug);
        }

        // 4. Check if first segment is a PAGE (regardless of segment count)
        $posttype = str_replace('-', '_', $firstSegment);
        $page = get_post([
            'slug' => $firstSegment,
            'post_type' => 'pages',
            'post_status' => HAS_GET('preview') ? '' : 'active'
        ]);
        if ($page && $segmentCount < 2) {
            return $this->getPageTemplate($firstSegment, $page);
        }

        // 5. Check DEFAULT POSTTYPE routes (if default posttype is set)
        $defaultPosttype = $this->defaultPosttype();
        if ($defaultPosttype) {
            $defaultPosttypeLayout = $this->defaultPosttypeLayout($segments, $defaultPosttype);
            if ($defaultPosttypeLayout !== null) {
                return $defaultPosttypeLayout;
            }
        }

        // 6. Check if first segment is a POSTTYPE (explicit posttype)
        if (posttype_lang_exists($posttype, APP_LANG)) {
            // 6a. Taxonomy Archive (posttype/taxonomy/term-slug)
            if ($segmentCount >= 3) {
                $taxonomy = $segments[1];
                $termSlug = $segments[2];
                return $this->getTaxonomyTemplate($posttype, $taxonomy, $termSlug);
            }

            // 6b. Single Post (posttype/slug)
            if ($segmentCount >= 2) {
                $slug = $segments[1];
                $post = get_post([
                    'slug' => $slug,
                    'post_type' => $posttype,
                    'post_status' => HAS_GET('preview') ? '' : 'active',
                    'with_terms' => true
                ]);

                if ($post) {
                    return $this->getSingleTemplate($posttype, $slug, $post);
                }
            } elseif ($page && $segmentCount >= 2) {
                return $this->getPageTemplate($firstSegment, $page);
            }

            // 6c. Posttype Archive (posttype/)
            return $this->getArchiveTemplate($posttype);
        }
        if ($page && $segmentCount >= 2) {
            return $this->getPageTemplate($firstSegment, $page);
        }

        // Fallback
        return '404';
    }

    /**
     * Get search template following WordPress hierarchy
     * search-{query}.php > search.php > index.php
     */
    protected function getSearchTemplate($segments)
    {
        // Hook: before_search_template
        do_action('before_search_template', $segments);

        $layout = null;

        // search-{query}.php
        if (!empty($segments[1]) && $this->templateExists("search-{$segments[1]}")) {
            $layout = "search-{$segments[1]}";
        }
        // search.php
        elseif ($this->templateExists('search')) {
            $layout = 'search';
        } else {
            $layout = 'index';
        }

        // Filter: search_template - Allow plugins to override search template
        $layout = apply_filters('search_template', $layout, $segments);

        // Hook: after_search_template
        do_action('after_search_template', $layout, $segments);

        return $layout;
    }

    /**
     * Get single template following WordPress hierarchy
     * single-{posttype}-{slug}.php > single-{posttype}.php > single.php > singular.php > index.php
     */
    protected function getSingleTemplate($posttype, $slug, $postData)
    {
        global $post;
        $post = $postData;
        $post['post_type'] = $posttype;

        // Hook: before_single_template
        do_action('before_single_template', $posttype, $slug, $postData);

        // For pages: page-{slug}.php > page-{id}.php > page-{template}.php > page.php
        if ($posttype === 'pages') {
            $layout = $this->getPageTemplate($slug, $post);
            // Filter: page_template
            return apply_filters('page_template', $layout, $slug, $post);
        }

        $layout = null;

        // single-{posttype}-{slug}.php
        if ($this->templateExists("single-{$posttype}-{$slug}")) {
            $layout = "single-{$posttype}-{$slug}";
        }
        // single-{posttype}.php
        elseif ($this->templateExists("single-{$posttype}")) {
            $layout = "single-{$posttype}";
        }
        // single.php
        elseif ($this->templateExists('single')) {
            $layout = 'single';
        }
        // singular.php
        elseif ($this->templateExists('singular')) {
            $layout = 'singular';
        } else {
            $layout = 'index';
        }

        // Filter: single_template - Allow plugins to override single template
        $layout = apply_filters('single_template', $layout, $posttype, $slug, $postData);

        // Hook: after_single_template
        do_action('after_single_template', $layout, $posttype, $slug, $postData);

        return $layout;
    }

    /**
     * Get page template following WordPress hierarchy
     * page-{slug}.php > page-{id}.php > page-{template}.php > page.php > singular.php > index.php
     */
    protected function getPageTemplate($slug, $pageData)
    {
        global $post, $page;
        $pageData['post_type'] = 'pages';
        $post = $pageData;
        $page = $pageData;

        // Hook: before_page_template
        do_action('before_page_template', $slug, $pageData);

        $layout = null;

        // page-{slug}.php
        if ($this->templateExists("page-{$slug}")) {
            $layout = "page-{$slug}";
        }
        // page-{id}.php
        elseif (isset($page['id']) && $this->templateExists("page-{$page['id']}")) {
            $layout = "page-{$page['id']}";
        }
        // page-{template}.php (if custom template is set)
        elseif (isset($page['template']) && !empty($page['template']) && $this->templateExists("page-{$page['template']}")) {
            $layout = "page-{$page['template']}";
        }
        // page.php
        elseif ($this->templateExists('page')) {
            $layout = 'page';
        }
        // singular.php
        elseif ($this->templateExists('singular')) {
            $layout = 'singular';
        } else {
            $layout = 'index';
        }

        // Filter: page_template - Allow plugins to override page template
        $layout = apply_filters('page_template', $layout, $slug, $pageData);

        // Hook: after_page_template
        do_action('after_page_template', $layout, $slug, $pageData);

        return $layout;
    }

    /**
     * Get archive template following WordPress hierarchy
     * archive-{posttype}.php > archive.php > index.php
     */
    protected function getArchiveTemplate($posttype)
    {
        // Hook: before_archive_template
        do_action('before_archive_template', $posttype);

        $layout = null;

        // archive-{posttype}.php
        if ($this->templateExists("archive-{$posttype}")) {
            $layout = "archive-{$posttype}";
        }
        // archive.php
        elseif ($this->templateExists('archive')) {
            $layout = 'archive';
        } else {
            $layout = 'index';
        }

        // Filter: archive_template - Allow plugins to override archive template
        $layout = apply_filters('archive_template', $layout, $posttype);

        // Hook: after_archive_template
        do_action('after_archive_template', $layout, $posttype);

        return $layout;
    }

    /**
     * Get taxonomy template following WordPress hierarchy
     * taxonomy-{taxonomy}-{term}.php > taxonomy-{taxonomy}.php > taxonomy.php > archive.php > index.php
     */
    protected function getTaxonomyTemplate($posttype, $taxonomy, $termSlug)
    {
        // Hook: before_taxonomy_template
        do_action('before_taxonomy_template', $posttype, $taxonomy, $termSlug);

        // Validate term exists using get_term function - much more efficient
        $term = get_term($termSlug, $posttype, $taxonomy, APP_LANG);
        if (!$term) {
            // Filter: taxonomy_template_not_found
            return apply_filters('taxonomy_template_not_found', 'index', $posttype, $taxonomy, $termSlug);
        }

        $layout = null;

        // taxonomy-{taxonomy}-{term}.php
        if ($this->templateExists("taxonomy-{$taxonomy}-{$termSlug}")) {
            $layout = "taxonomy-{$taxonomy}-{$termSlug}";
        }
        // taxonomy-{taxonomy}.php
        elseif ($this->templateExists("taxonomy-{$taxonomy}")) {
            $layout = "taxonomy-{$taxonomy}";
        }
        // taxonomy.php
        elseif ($this->templateExists('taxonomy')) {
            $layout = 'taxonomy';
        }
        // archive.php
        elseif ($this->templateExists('archive')) {
            $layout = 'archive';
        } else {
            $layout = 'index';
        }

        // Filter: taxonomy_template - Allow plugins to override taxonomy template
        $layout = apply_filters('taxonomy_template', $layout, $posttype, $taxonomy, $termSlug, $term);

        // Hook: after_taxonomy_template
        do_action('after_taxonomy_template', $layout, $posttype, $taxonomy, $termSlug, $term);

        return $layout;
    }

    /**
     * Get author template following WordPress hierarchy
     * author-{nicename}.php > author-{id}.php > author.php > archive.php > index.php
     */
    protected function getAuthorTemplate($authorSlug = null)
    {
        // Hook: before_author_template
        do_action('before_author_template', $authorSlug);

        $layout = null;

        if ($authorSlug) {
            // Check if author exists (you can implement this)
            // $author = getAuthor($authorSlug);

            // author-{nicename}.php
            if ($this->templateExists("author-{$authorSlug}")) {
                $layout = "author-{$authorSlug}";
            }
        }

        if (!$layout) {
            // author.php
            if ($this->templateExists('author')) {
                $layout = 'author';
            }
            // archive.php
            elseif ($this->templateExists('archive')) {
                $layout = 'archive';
            } else {
                $layout = 'index';
            }
        }

        // Filter: author_template - Allow plugins to override author template
        $layout = apply_filters('author_template', $layout, $authorSlug);

        // Hook: after_author_template
        do_action('after_author_template', $layout, $authorSlug);

        return $layout;
    }
    
    // /**
    //  * Get date archive template following WordPress hierarchy
    //  * date.php > archive.php > index.php
    //  */
    // protected function getDateTemplate($segments)
    // {
    //     // date.php
    //     if ($this->templateExists('date')) {
    //         return 'date';
    //     }
        
    //     // archive.php
    //     if ($this->templateExists('archive')) {
    //         return 'archive';
    //     }
        
    //     return 'index';
    // }

    /**
     * Check if segment is a date archive (year/month/day)
     */
    protected function isDateArchive($segment)
    {
        // Check if it's a 4-digit year
        return preg_match('/^\d{4}$/', $segment);
    }

    /**
     * Get default posttype from settings
     * 
     * @return string|null Default posttype slug or null if not set
     */
    protected function defaultPosttype()
    {
        $defaultPosttype = option('default_posttype', APP_LANG);
        if ($defaultPosttype && posttype_lang_exists($defaultPosttype, APP_LANG)) {
            return $defaultPosttype;
        }
        return null;
    }

    /**
     * Check routes for default posttype (URLs without posttype prefix)
     * 
     * @param array $segments URI segments
     * @param string $defaultPosttype Default posttype slug
     * @return string|null Template name or null if not matched
     */
    protected function defaultPosttypeLayout($segments, $defaultPosttype)
    {
        $segmentCount = count($segments);
        $firstSegment = $segments[0];
        // 1. Taxonomy Archive for default posttype
        // /category/tech/ -> taxonomy-category-tech.php (default posttype)
        if ($segmentCount >= 2) {
            //$firstSegment is 'category', 'tags', etc.
            $termSlug = $segments[1];
            // Check if this taxonomy/term exists for default posttype
            $term = get_term($termSlug, $defaultPosttype, $firstSegment, APP_LANG);
            if ($term) {
                return $this->getTaxonomyTemplate($defaultPosttype, $firstSegment, $termSlug);
            }
        }
        // 2. Single Post for default posttype
        // /this-is-slug-post/ -> single-{defaultPosttype}.php
        $post = get_post([
            'slug' => $firstSegment,
            'post_type' => $defaultPosttype,
            'post_status' => HAS_GET('preview') ? '' : 'active',
            'with_terms' => true
        ]);
        if ($post) {
            return $this->getSingleTemplate($defaultPosttype, $firstSegment, $post);
        }
        return null;
    }

    /**
     * Check if template file exists in theme
     */
    protected function templateExists($template)
    {
        if (!defined('APP_THEME_NAME') || APP_THEME_NAME === '') {
            return false;
        }
        $path = \System\Libraries\Render\Theme\ThemeConfigLoader::themeTemplateFilePath(APP_THEME_NAME, $template);

        return $path !== '' && is_file($path);
    }

    /**
     * Get schema context (type + payload) from current layout.
     * Head::render() gọi Schema::get() không tham số → dùng context này.
     *
     * @param string $layout Layout name (front-page, page-khoa-hoc, single-blogs, archive, search, …)
     * @return array|null ['type' => string, 'payload' => mixed] or null to keep default
     */
    protected function getSchemaContextForLayout($layout)
    {
        global $post, $page;

        $segments = APP_URI['split'] ?? [];
        $type     = 'front';
        $payload  = null;

        // Homepage
        if (empty($layout) || $layout === 'front-page' || $layout === 'index') {
            return ['type' => 'front', 'payload' => null];
        }

        // 404
        if ($layout === '404') {
            return ['type' => 'front', 'payload' => null];
        }

        // Static page (page, page-{slug}, page-{id}, …)
        if ($layout === 'page' || (strpos($layout, 'page-') === 0 && $layout !== 'page-')) {
            $payload = isset($page) && !empty($page) ? $page : (isset($post) && !empty($post) ? $post : null);
            return ['type' => 'page', 'payload' => $payload];
        }

        // Single post (single-{posttype}, single-{posttype}-{slug})
        if (strpos($layout, 'single-') === 0) {
            $rest    = substr($layout, 7);
            $dash    = strpos($rest, '-');
            $posttype = $dash !== false ? substr($rest, 0, $dash) : $rest;
            $schemaType = $this->mapPosttypeToSchemaType($posttype);
            $payload = isset($post) && !empty($post) ? $post : null;
            return ['type' => $schemaType, 'payload' => $payload];
        }

        // Singular (fallback single)
        if ($layout === 'singular') {
            $payload = isset($post) && !empty($post) ? $post : null;
            $posttype = is_array($payload) ? ($payload['post_type'] ?? '') : (is_object($payload) ? ($payload->post_type ?? '') : '');
            $schemaType = $posttype ? $this->mapPosttypeToSchemaType($posttype) : 'article';
            return ['type' => $schemaType, 'payload' => $payload];
        }

        // Archive (archive, archive-{posttype})
        if ($layout === 'archive' || strpos($layout, 'archive-') === 0) {
            $posttype = $layout === 'archive' ? '' : substr($layout, 8);
            $payload = ['post_type' => $posttype, 'title' => ''];
            return ['type' => 'archive', 'payload' => $payload];
        }

        // Search
        if ($layout === 'search' || strpos($layout, 'search-') === 0) {
            $query = isset($_GET['q']) ? (string) $_GET['q'] : (isset($segments[1]) ? $segments[1] : '');
            return ['type' => 'search', 'payload' => ['query' => $query]];
        }

        // Author
        if ($layout === 'author' || strpos($layout, 'author-') === 0) {
            $slug = strpos($layout, 'author-') === 0 ? substr($layout, 7) : (isset($segments[1]) ? $segments[1] : '');
            $payload = ['slug' => $slug];
            return ['type' => 'person', 'payload' => $payload];
        }

        // Taxonomy
        if (strpos($layout, 'taxonomy-') === 0) {
            $rest = substr($layout, 9);
            $parts = explode('-', $rest);
            $payload = [
                'taxonomy' => $parts[0] ?? '',
                'term'    => $parts[1] ?? '',
            ];
            return ['type' => 'archive', 'payload' => array_merge($payload, ['post_type' => ''])];
        }

        return ['type' => $type, 'payload' => $payload];
    }

    /**
     * Map post type slug to Schema library type (Types/*.php).
     *
     * @param string $posttype e.g. blogs, products, courses, events
     * @return string Schema type: article, product, course, event, …
     */
    protected function mapPosttypeToSchemaType($posttype)
    {
        $map = [
            'blogs'    => 'article',
            'products' => 'product',
            'courses'  => 'course',
            'events'   => 'event',
            'faqs'     => 'faq',
            'videos'   => 'video',
            'recipes'  => 'recipe',
            'pages'    => 'page',
        ];
        $posttype = str_replace('-', '_', $posttype);
        if (isset($map[$posttype])) {
            return $map[$posttype];
        }
        return $posttype ?: 'article';
    }

    /**
     * Load theme functions.php file if exists
     * Similar to WordPress functions.php
     */
    protected function _loadFunctions()
    {
        $functions_file = APP_THEME_PATH . 'functions.php';

        if (file_exists($functions_file)) {
            // Load theme functions
            require_once $functions_file;
        }
    }
}
