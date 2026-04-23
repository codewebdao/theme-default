<?php
/**
 * Head Builder – Build default title, meta, OG, Twitter, canonical từ layout + payload
 *
 * Chỉ build mảng dữ liệu; không gọi Head::set*. Head::render() gọi build() rồi fill vào Head chỉ khi view chưa set.
 * Filter: render.head.defaults để plugin/theme override toàn bộ hoặc từng phần.
 *
 * @package System\Libraries\Render\Head
 */

namespace System\Libraries\Render\Head;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Builder
{
    /**
     * Build default head data từ layout + payload.
     *
     * @param string $layout Layout name
     * @param mixed  $payload Post/page/query (object hoặc array)
     * @return array [title_parts, description, canonical, site_name, og, twitter, robots]
     */
    public static function build($layout, $payload = null)
    {
        $baseUrl   = rtrim(base_url(), '/');
        $siteName  = option('site_title', defined('APP_LANG') ? APP_LANG : '') ?: '';
        $siteDesc  = option('site_desc', defined('APP_LANG') ? APP_LANG : '') ?? '';
        $siteLogo  = option('site_logo', defined('APP_LANG') ? APP_LANG : '');
        $logoUrl   = $siteLogo ? _img_url($siteLogo, 'original') : '';
        if (!$logoUrl) {
            $logoUrl = theme_assets('images/logo/logo-icon.webp');
        }
        // application/Config/Languages.php: APP_LOCALE + APP_LANGUAGES[*]['locale']
        $locale = defined('APP_LOCALE') ? APP_LOCALE : 'en_US';
        $ogAlts = [];
        if (defined('APP_LANGUAGES') && is_array(APP_LANGUAGES) && count(APP_LANGUAGES) >= 2) {
            foreach (APP_LANGUAGES as $c => $row) {
                if (!is_string($c) || $c === '') {
                    continue;
                }
                if (!is_array($row) || empty($row['locale']) || !is_string($row['locale'])) {
                    continue;
                }
                $loc = $row['locale'];
                if ($loc !== $locale) {
                    $ogAlts[$loc] = true;
                }
            }
            $ogAlts = array_keys($ogAlts);
        }

        $hreflang = self::hreflang($layout);
        // Icons (option: site_icon url, or site_favicon, site_apple_touch_icon, site_tile_image)
        $siteIcon = option('site_icon', defined('APP_LANG') ? APP_LANG : '');
        $faviconUrl = $siteIcon ? _img_url($siteIcon, 'original') : '';
        if (!$faviconUrl) {
            $faviconUrl = option('site_favicon', defined('APP_LANG') ? APP_LANG : '') ?: '';
        }
        $appleTouch = $faviconUrl ?: ($siteIcon ? _img_url($siteIcon, 'original') : '');
        if (!$appleTouch) {
            $appleTouch = option('site_apple_touch_icon', defined('APP_LANG') ? APP_LANG : '') ?: '';
        }
        $tileImage = $appleTouch ?: $faviconUrl;
        $t = option('site_tile_image', defined('APP_LANG') ? APP_LANG : '');
        if ($t !== '' && $t !== null) {
            $tileImage = is_string($t) ? _img_url($t, 'original') : $t;
        }

        $result = [
            'title_parts' => [],
            'description' => '',
            'canonical'   => '',
            'site_name'   => $siteName,
            'og'          => [],
            'twitter'     => [],
            'robots'      => '',
            'profile'     => true,
            'hreflang'    => $hreflang,
            'alternate'   => $ogAlts,
            'icons'       => array_filter([
                'favicon'           => $faviconUrl,
                'apple_touch_icon'  => $appleTouch,
                'tile_image'        => $tileImage,
            ]),
        ];

        // 404
        if ($layout === '404') {
            $result['title_parts'] = [__('Page not found', defined('APP_LANG') ? APP_LANG : null)];
            $result['description'] = __('Page not found', defined('APP_LANG') ? APP_LANG : null);
            $result['canonical']   = $baseUrl;
            $result['robots']     = 'noindex, follow';
            $result['og']         = self::siteOg($siteName, $siteDesc, $baseUrl, $logoUrl, $locale, 'website');
            $result['twitter']    = self::siteTwitter($siteName, $siteDesc, $logoUrl, null);
            return self::filter($result, $layout, $payload);
        }

        // Homepage (front-page, index, empty)
        if (empty($layout) || $layout === 'front-page' || $layout === 'index') {
            $result['title_parts'] = [$siteName];
            $result['description'] = $siteDesc;
            $result['canonical']   = $baseUrl;
            $result['robots']     = 'index, follow';
            $result['og']         = self::siteOg($siteName, $siteDesc, $baseUrl, $logoUrl, $locale, 'website');
            $result['og']['updated_time'] = date('c');
            $result['twitter']    = self::siteTwitter($siteName, $siteDesc, $logoUrl, null);
            return self::filter($result, $layout, $payload);
        }

        // Search
        if ($layout === 'search' || strpos($layout, 'search-') === 0) {
            $query = is_array($payload) && isset($payload['query']) ? trim(strip_tags((string) $payload['query'])) : '';
            $searchPosttype = 'posts';
            if (strpos($layout, 'search-') === 0) {
                $suffix = substr($layout, 7);
                if ($suffix !== '') {
                    $searchPosttype = $suffix;
                }
            }
            if ($query !== '') {
                $result['title_parts'] = [sprintf(__('Search results for: %s', defined('APP_LANG') ? APP_LANG : null), $query)];
            } else {
                $result['title_parts'] = [__('Search', defined('APP_LANG') ? APP_LANG : null)];
            }
            $result['description'] = $result['title_parts'][0];
            $result['canonical']   = function_exists('link_search')
                ? link_search($query, $searchPosttype)
                : ($baseUrl . '/search' . ($query !== '' ? '?q=' . rawurlencode($query) : ''));
            $result['robots']     = 'noindex, follow';
            $result['og']         = self::siteOg($result['title_parts'][0], $result['description'], $result['canonical'], $logoUrl, $locale, 'website');
            $result['twitter']    = self::siteTwitter($result['title_parts'][0], $result['description'], $logoUrl, null);
            return self::filter($result, $layout, $payload);
        }

        // Author
        if ($layout === 'author' || strpos($layout, 'author-') === 0) {
            $result['title_parts'] = [__('Author archive', defined('APP_LANG') ? APP_LANG : null)];
            $result['description'] = $result['title_parts'][0];
            $result['canonical']   = function_exists('link_author')
                ? rtrim(link_author(''), '/')
                : rtrim($baseUrl . '/author', '/');
            $result['robots']     = 'index, follow';
            $result['og']         = self::siteOg($result['title_parts'][0], $result['description'], $result['canonical'], $logoUrl, $locale, 'website');
            $result['twitter']    = self::siteTwitter($result['title_parts'][0], $result['description'], $logoUrl, null);
            return self::filter($result, $layout, $payload);
        }

        // Static page (page, page-*)
        if ($layout === 'page' || (strpos($layout, 'page-') === 0 && $layout !== 'page-')) {
            $p = $payload;
            if ($p) {
                $title   = self::payloadGet($p, ['title', 'post_title']);
                $desc    = self::payloadGet($p, ['description', 'excerpt', 'post_excerpt']);
                $url     = self::payloadGet($p, ['url']);
                $image   = self::payloadGet($p, ['thumbnail', 'image']);
                if (!$url) {
                    $slug = self::payloadGet($p, ['slug']);
                    if ($slug) {
                        $url = function_exists('link_page')
                            ? rtrim(link_page($slug), '/')
                            : rtrim($baseUrl . '/' . ltrim($slug, '/'), '/');
                    }
                }
                $result['title_parts'] = [$title ?: __('Page', defined('APP_LANG') ? APP_LANG : null)];
                $result['description'] = $desc ?: $result['title_parts'][0];
                $result['canonical']   = $url ?: $baseUrl;
                $result['robots']     = 'index, follow';
                $result['og']         = array_filter(array_merge(
                    [
                        'locale' => $locale,
                        'type'   => 'article',
                        'title'  => $result['title_parts'][0],
                        'description' => $result['description'],
                        'url'    => $result['canonical'],
                        'site_name' => $siteName,
                    ],
                    self::articleOgExtra($baseUrl, $p, $locale)
                ));
                if ($image) {
                    self::addOgImage($result['og'], $image, $result['title_parts'][0]);
                }
                $result['twitter'] = self::articleTwitter($result['title_parts'][0], $result['description'], $result['og']['image'] ?? $logoUrl, $siteName, $p);
            } else {
                $result['title_parts'] = [__('Page', defined('APP_LANG') ? APP_LANG : null)];
                $result['canonical']   = $baseUrl;
                $result['og']          = self::siteOg($siteName, $siteDesc, $baseUrl, $logoUrl, $locale, 'website');
                $result['twitter']    = self::siteTwitter($siteName, $siteDesc, $logoUrl, null);
            }
            return self::filter($result, $layout, $payload);
        }

        // Single (single-*, singular)
        if ($layout === 'single' || strpos($layout, 'single-') === 0 || $layout === 'singular') {
            $p = $payload;
            if ($p) {
                $title   = self::payloadGet($p, ['title', 'post_title']);
                $desc    = self::payloadGet($p, ['description', 'excerpt', 'post_excerpt']);
                $url     = self::payloadGet($p, ['url']);
                $image   = self::payloadGet($p, ['thumbnail', 'image']);
                $postType = self::payloadGet($p, ['post_type']);
                if (!$url) {
                    $slug = self::payloadGet($p, ['slug']);
                    if ($slug) {
                        $pt = $postType ?: 'posts';
                        $url = function_exists('link_posts')
                            ? rtrim(link_posts($slug, $pt), '/')
                            : rtrim($baseUrl . '/' . ($postType ? $postType . '/' : '') . ltrim($slug, '/'), '/');
                    }
                }
                $result['title_parts'] = [$title ?: __('Post', defined('APP_LANG') ? APP_LANG : null)];
                $result['description'] = $desc ?: $result['title_parts'][0];
                $result['canonical']   = $url ?: $baseUrl;
                $result['robots']     = 'index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large';
                $ogType = (strpos($layout, 'single-products') === 0) ? 'product' : 'article';
                $result['og']         = array_filter(array_merge(
                    [
                        'locale' => $locale,
                        'type'   => $ogType,
                        'title'  => $result['title_parts'][0],
                        'description' => $result['description'],
                        'url'    => $result['canonical'],
                        'site_name' => $siteName,
                        'updated_time' => self::payloadGet($p, ['updated_at', 'post_modified']) ? date('c', strtotime(self::payloadGet($p, ['updated_at', 'post_modified']))) : date('c'),
                    ],
                    $ogType === 'article' ? self::articleOgExtra($baseUrl, $p, $locale) : []
                ));
                if ($image) {
                    self::addOgImage($result['og'], $image, $result['title_parts'][0]);
                }
                $result['twitter'] = self::articleTwitter($result['title_parts'][0], $result['description'], $result['og']['image'] ?? $logoUrl, $siteName, $p);
            } else {
                $result['title_parts'] = [__('Post', defined('APP_LANG') ? APP_LANG : null)];
                $result['canonical']   = $baseUrl;
                $result['og']          = self::siteOg($siteName, $siteDesc, $baseUrl, $logoUrl, $locale, 'website');
                $result['twitter']     = self::siteTwitter($siteName, $siteDesc, $logoUrl, null);
            }
            return self::filter($result, $layout, $payload);
        }

        // Archive (archive, archive-*)
        if ($layout === 'archive' || strpos($layout, 'archive-') === 0) {
            $posttype = $layout === 'archive' ? '' : substr($layout, 8);
            $labels = [
                'blogs' => __('Blog', defined('APP_LANG') ? APP_LANG : null),
                'products' => __('Products', defined('APP_LANG') ? APP_LANG : null),
                'courses' => __('Courses', defined('APP_LANG') ? APP_LANG : null),
                'events' => __('Events', defined('APP_LANG') ? APP_LANG : null),
            ];
            $label = isset($labels[$posttype]) ? $labels[$posttype] : __('Archive', defined('APP_LANG') ? APP_LANG : null);
            $result['title_parts'] = [$label];
            $result['description'] = $label;
            $result['canonical']   = $posttype !== ''
                ? rtrim((string) base_url($posttype), '/')
                : $baseUrl;
            $result['robots']     = 'index, follow';
            $result['og']         = self::siteOg($label, $label, $result['canonical'], $logoUrl, $locale, 'website');
            $result['twitter']    = self::siteTwitter($label, $label, $logoUrl, null);
            return self::filter($result, $layout, $payload);
        }

        // Taxonomy
        if (strpos($layout, 'taxonomy-') === 0) {
            $result['title_parts'] = [__('Taxonomy archive', defined('APP_LANG') ? APP_LANG : null)];
            $result['description'] = $result['title_parts'][0];
            $result['canonical']   = $baseUrl;
            $result['robots']     = 'index, follow';
            $result['og']         = self::siteOg($result['title_parts'][0], $result['description'], $baseUrl, $logoUrl, $locale, 'website');
            $result['twitter']    = self::siteTwitter($result['title_parts'][0], $result['description'], $logoUrl, null);
            return self::filter($result, $layout, $payload);
        }

        // Fallback: mọi layout không khớp case (auth, plugin, …) — meta/OG/canonical chuẩn từ site + payload
        return self::buildDefaultForUnknownLayout($result, $baseUrl, $siteName, $siteDesc, $logoUrl, $locale, $layout, $payload);
    }

    /**
     * Head mặc định khi layout không thuộc case đặc biệt (404, index, search, page, single, archive, taxonomy, …).
     */
    private static function buildDefaultForUnknownLayout(
        array $result,
        string $baseUrl,
        string $siteName,
        $siteDesc,
        string $logoUrl,
        string $locale,
        string $layout,
        $payload
    ): array {
        $desc = ($siteDesc !== '' && $siteDesc !== null) ? (string) $siteDesc : $siteName;
        $pDesc = self::payloadGet($payload, ['description', 'meta_description']);
        if ($pDesc !== '') {
            $desc = $pDesc;
        }
        $ogTitle = self::payloadGet($payload, ['page_title', 'og_title']);
        if ($ogTitle === '') {
            $ogTitle = $siteName;
        }
        $robots = self::payloadGet($payload, ['robots']);
        if ($robots === '') {
            $robots = 'index, follow';
        }

        $result['title_parts'] = [$siteName];
        $result['description'] = $desc;
        $result['canonical'] = self::canonicalForUnknownLayout($baseUrl, $payload);
        $result['robots'] = $robots;
        $result['og'] = self::siteOg($ogTitle, $desc, $result['canonical'], $logoUrl, $locale, 'website');
        $result['twitter'] = self::siteTwitter($ogTitle, $desc, $logoUrl, null);
        return self::filter($result, $layout, $payload);
    }

    /**
     * hreflang: option hreflang_alternates (custom) hoặc tự sinh khi >= 2 ngôn ngữ (cùng path qua lang_url + x-default).
     *
     * @return array<int, array{href: string, hreflang: string}>
     */
    private static function hreflang(string $layout): array
    {
        $lang = defined('APP_LANG') ? APP_LANG : 'en';
        $custom = option('hreflang_alternates', $lang);
        if (is_array($custom) && count($custom) > 0) {
            $out = [];
            foreach ($custom as $item) {
                if (is_string($item)) {
                    continue;
                }
                $href = '';
                $hl = '';
                if (is_array($item)) {
                    $href = isset($item['href']) ? (string) $item['href'] : (isset($item['url']) ? (string) $item['url'] : '');
                    $hl = isset($item['hreflang']) ? (string) $item['hreflang'] : (isset($item['lang']) ? (string) $item['lang'] : '');
                }
                if ($href !== '' && $hl !== '') {
                    $out[] = ['href' => $href, 'hreflang' => $hl];
                }
            }
            return $out;
        }
        if (!defined('APP_LANGUAGES') || !is_array(APP_LANGUAGES) || count(APP_LANGUAGES) < 2) {
            return [];
        }
        $out = [];
        foreach (array_keys(APP_LANGUAGES) as $code) {
            if (!is_string($code) || $code === '') {
                continue;
            }
            $href = rtrim(function_exists('lang_url') ? lang_url($code) : base_url('', $code), '/');
            $out[] = ['href' => $href, 'hreflang' => $code];
        }
        $df = defined('APP_LANG_DF') ? APP_LANG_DF : $lang;
        $hasX = false;
        foreach ($out as $row) {
            if (($row['hreflang'] ?? '') === 'x-default') {
                $hasX = true;
                break;
            }
        }
        if (is_string($df) && $df !== '' && !$hasX) {
            $hrefDef = rtrim(function_exists('lang_url') ? lang_url($df) : base_url('', $df), '/');
            $out[] = ['href' => $hrefDef, 'hreflang' => 'x-default'];
        }
        if (function_exists('apply_filters')) {
            $out = apply_filters('render.head.hreflang', $out, $layout);
        }
        return is_array($out) ? $out : [];
    }

    /** Canonical fallback: payload canonical/url/permalink hoặc base_url(path) từ request_uri(). */
    private static function canonicalForUnknownLayout(string $baseRoot, $payload): string
    {
        $baseRoot = rtrim($baseRoot, '/');
        $hint = trim(self::payloadGet($payload, ['canonical', 'url', 'permalink']));
        if ($hint !== '') {
            if (preg_match('#^https?://#i', $hint)) {
                $cut = strtok($hint, '#');
                return $cut !== false ? $cut : $hint;
            }
            $clean = ltrim(str_replace('\\', '/', $hint), '/');
            return rtrim((string) base_url($clean), '/');
        }
        $path = '';
        if (function_exists('request_uri')) {
            $path = trim((string) request_uri(), '/');
        } else {
            $raw = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $path = is_string($raw) ? trim($raw, '/') : '';
        }
        if ($path !== '') {
            $seg = explode('/', $path);
            if (defined('APP_LANGUAGES') && is_array(APP_LANGUAGES) && isset(APP_LANGUAGES[$seg[0]])) {
                array_shift($seg);
            }
            $path = implode('/', $seg);
        }
        if ($path === '') {
            return $baseRoot;
        }
        return rtrim((string) base_url($path), '/');
    }

    private static function siteOg($title, $description, $url, $image, $locale, $type = 'website')
    {
        $siteName = option('site_title', defined('APP_LANG') ? APP_LANG : '') ?: '';
        $og = [
            'locale' => $locale,
            'type'   => $type,
            'title'  => $title,
            'description' => $description,
            'url'    => $url,
            'site_name' => $siteName,
        ];
        if ($image) {
            $og['image'] = $image;
            $og['image:secure_url'] = $image;
        }
        $fbAdmins = option('fb_admins', defined('APP_LANG') ? APP_LANG : '') ?: '';
        if ($fbAdmins !== '') {
            $og['fb:admins'] = $fbAdmins;
        }
        return $og;
    }

    private static function siteTwitter($title, $description, $image, $creator = null)
    {
        $siteName = option('site_title', defined('APP_LANG') ? APP_LANG : '') ?: '';
        $tw = [
            'card' => 'summary_large_image',
            'title' => $title,
            'description' => $description,
            'site' => '@' . preg_replace('/\s+/', '', $siteName),
        ];
        if ($creator !== null && $creator !== '') {
            $tw['creator'] = (strpos($creator, '@') === 0) ? $creator : ('@' . preg_replace('/\s+/', '', $creator));
        }
        if ($image) {
            $tw['image'] = $image;
        }
        return $tw;
    }

    /** Article OG: publisher, author, section, published_time, modified_time (Rank Math / WordPress style) */
    private static function articleOgExtra($baseUrl, $payload, $locale)
    {
        $extra = [];
        $publisher = option('og_publisher', defined('APP_LANG') ? APP_LANG : '') ?: $baseUrl;
        $extra['article:publisher'] = $publisher;
        $author = self::payloadGet($payload, ['author_url', 'author_link', 'author']);
        if ($author) {
            $extra['article:author'] = $author;
        }
        $section = self::payloadGet($payload, ['category', 'section', 'term_name']);
        if ($section) {
            $extra['article:section'] = $section;
        }
        $published = self::payloadGet($payload, ['created_at', 'post_date', 'datePublished']);
        if ($published) {
            $extra['article:published_time'] = date('c', is_numeric($published) ? $published : strtotime($published));
        }
        $modified = self::payloadGet($payload, ['updated_at', 'post_modified', 'dateModified']);
        if ($modified) {
            $extra['article:modified_time'] = date('c', is_numeric($modified) ? $modified : strtotime($modified));
        }
        $fbAdmins = option('fb_admins', defined('APP_LANG') ? APP_LANG : '') ?: '';
        if ($fbAdmins !== '') {
            $extra['fb:admins'] = $fbAdmins;
        }
        return array_filter($extra);
    }

    /** OG image với width, height, alt, type (Google / Rank Math khuyến nghị) */
    private static function addOgImage(array &$og, $image, $alt = '')
    {
        $imgUrl = is_string($image) && (strpos($image, '://') !== false || strpos($image, '//') === 0)
            ? $image
            : _img_url($image, 'original');
        if (!$imgUrl || !is_string($imgUrl)) {
            return;
        }
        $og['image'] = $imgUrl;
        $og['image:secure_url'] = $imgUrl;
        if ($alt !== '') {
            $og['image:alt'] = $alt;
        }
        $og['image:type'] = 'image/jpeg';
        // width/height từ payload nếu có (image_width, image_height)
        $w = is_array($image) ? ($image['width'] ?? '') : '';
        $h = is_array($image) ? ($image['height'] ?? '') : '';
        if ($w !== '' && $h !== '') {
            $og['image:width'] = $w;
            $og['image:height'] = $h;
        }
    }

    /** Twitter Card cho article: creator, label1/data1 (author), label2/data2 (reading time) */
    private static function articleTwitter($title, $description, $imageUrl, $siteName, $payload)
    {
        $tw = [
            'card' => 'summary_large_image',
            'title' => $title,
            'description' => $description,
            'site' => '@' . preg_replace('/\s+/', '', $siteName),
        ];
        if ($imageUrl) {
            $tw['image'] = $imageUrl;
        }
        $creator = self::payloadGet($payload, ['author_twitter', 'twitter', 'author']);
        if ($creator !== '') {
            $tw['creator'] = (strpos($creator, '@') === 0) ? $creator : ('@' . preg_replace('/\s+/', '', $creator));
        }
        $authorName = self::payloadGet($payload, ['author_name', 'author']);
        if ($authorName !== '') {
            $tw['label1'] = __('Written by', defined('APP_LANG') ? APP_LANG : null);
            $tw['data1'] = $authorName;
        }
        $readingTime = self::payloadGet($payload, ['reading_time', 'reading_time_minutes']);
        if ($readingTime !== '') {
            $tw['label2'] = __('Reading time', defined('APP_LANG') ? APP_LANG : null);
            $tw['data2'] = $readingTime;
        }
        return $tw;
    }

    /**
     * Lấy giá trị từ payload (array hoặc object).
     *
     * @param mixed $payload
     * @param array $keys Các key thử lần lượt
     * @return string
     */
    private static function payloadGet($payload, array $keys)
    {
        foreach ($keys as $key) {
            if (is_array($payload) && array_key_exists($key, $payload)) {
                $v = $payload[$key];
                return is_string($v) ? $v : (string) $v;
            }
            if (is_object($payload) && isset($payload->$key)) {
                $v = $payload->$key;
                return is_string($v) ? $v : (string) $v;
            }
        }
        return '';
    }

    private static function filter(array $result, $layout, $payload)
    {
        if (function_exists('apply_filters')) {
            $result = apply_filters('render.head.defaults', $result, $layout, $payload);
        }
        return is_array($result) ? $result : [];
    }
}
