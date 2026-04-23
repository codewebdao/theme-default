<?php
namespace System\Libraries\Render;

/**
 * Head Manager
 *
 * Giống Schema: title, meta, OG, Twitter, canonical build mặc định từ context (layout + payload).
 * FrontendController set Head\Context::setCurrent($layout, $payload); Head::render() gọi Head\Builder::build()
 * và fill chỉ khi view chưa set. View chỉ gọi Head::set* khi override; can thiệp thêm qua filter render.head.defaults.
 *
 * - Title (separator, site name)
 * - Meta (description, keywords, robots, generator, language)
 * - Open Graph + Twitter Card (+ og:locale:alternate từ Builder key "alternate" khi đa ngôn ngữ)
 * - Canonical URL
 * - JSON-LD schema: chính (Schema::get()) + bổ sung (addSchema())
 *
 * @package System\Libraries\Render
 * @since 1.0.0
 */
class Head
{
    /**
     * Page title parts
     * @var array
     */
    private static $titleParts = [];

    /**
     * Title separator
     * @var string
     */
    private static $titleSeparator = '|';

    /**
     * Site name
     * @var string
     */
    private static $siteName = '';

    /**
     * Meta tags
     * @var array
     */
    private static $metaTags = [];

    /**
     * JSON-LD schemas bổ sung (từ addSchema/view_schema). Schema chính do Schema::get() trong Head::render().
     * @var array
     */
    private static $schemas = [];

    /**
     * Canonical URL
     * @var string|null
     */
    private static $canonical = null;

    /**
     * Link tags (profile, hreflang, icon) – từ Builder hoặc addLink()
     * @var array [rel => [['href' => url, 'hreflang' => ...?, ...], ...]]
     */
    private static $links = [];

    /**
     * og:locale:alternate (nhiều thẻ meta) – khi APP_LANGUAGES có từ 2 ngôn ngữ
     * @var string[]
     */
    private static $ogLocaleAlternates = [];

    /**
     * Set page title
     * 
     * @param string|array $title Title or array of title parts
     * @param bool $append Append to existing parts
     * @return void
     */
    public static function setTitle($title, $append = false)
    {
        if (is_array($title)) {
            if ($append) {
                self::$titleParts = array_merge(self::$titleParts, $title);
            } else {
                self::$titleParts = $title;
            }
        } else {
            if ($append) {
                self::$titleParts[] = $title;
            } else {
                self::$titleParts = [$title];
            }
        }
        
        // Apply filter (allow plugins to modify title)
        if (function_exists('apply_filters')) {
            self::$titleParts = apply_filters('render.head.set_title', self::$titleParts, $append);
        }
    }

    /**
     * Set title separator
     * 
     * @param string $separator Separator (|, -, –, etc.)
     * @return void
     */
    public static function setTitleSeparator($separator)
    {
        self::$titleSeparator = $separator;
    }

    /**
     * Set site name
     * 
     * @param string $name Site name
     * @return void
     */
    public static function setSiteName($name)
    {
        self::$siteName = $name;
    }

    /**
     * Get formatted title
     * 
     * @param bool $includeSiteName Include site name
     * @return string
     */
    public static function getTitle($includeSiteName = true)
    {
        $parts = self::$titleParts;

        // Apply filter (allow plugins to modify) BEFORE adding site name
        if (function_exists('apply_filters')) {
            $parts = apply_filters('render.head.title_parts', $parts);
        }

        // Remove empty parts
        $parts = array_filter($parts, function($part) {
            return !empty(trim($part ?? ''));
        });

        // Add site name if needed (avoid duplicates)
        if ($includeSiteName && !empty(self::$siteName)) {
            $siteName = trim(self::$siteName);
            // Check if site name already exists in parts (avoid duplicate)
            $hasSiteName = false;
            foreach ($parts as $part) {
                if (trim($part ?? '') === $siteName) {
                    $hasSiteName = true;
                    break;
                }
            }
            if (!$hasSiteName) {
                $parts[] = $siteName;
            }
        }

        // If no parts, return empty string
        if (empty($parts)) {
            return '';
        }

        return implode(' ' . self::$titleSeparator . ' ', $parts);
    }

    /**
     * Add meta tag
     * 
     * @param string $name Meta name or property
     * @param string $content Content
     * @param string $type Type (name, property, http-equiv)
     * @return void
     */
    public static function addMeta($name, $content, $type = 'name')
    {
        $meta = [
            'name' => $name,
            'content' => $content,
            'type' => $type,
        ];
        
        // Apply filter (allow plugins to modify meta before adding)
        if (function_exists('apply_filters')) {
            $meta = apply_filters('render.head.add_meta', $meta, $name, $content, $type);
        }
        
        if ($meta !== null) {
            self::$metaTags[$name] = $meta;
        }
    }

    /**
     * Set meta description
     * Chỉ thêm description, og:description, twitter:description (không ghi đè og:title, og:url, …).
     *
     * @param string $description Description
     * @return void
     */
    public static function setDescription($description)
    {
        // Apply filter (allow plugins to modify description)
        if (function_exists('apply_filters')) {
            $description = apply_filters('render.head.set_description', $description);
        }
        $description = is_scalar($description) ? (string) $description : '';
        self::addMeta('description', $description, 'name');
        self::addMeta('og:description', $description, 'property');
        self::addMeta('twitter:description', $description, 'name');
    }

    /**
     * Set meta keywords
     * 
     * @param string|array $keywords Keywords
     * @return void
     */
    public static function setKeywords($keywords)
    {
        if (is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }
        self::addMeta('keywords', $keywords, 'name');
    }

    /**
     * Set Open Graph meta tags
     * Chỉ thêm giá trị scalar (string/number) để meta content luôn an toàn khi render.
     *
     * @param array $og OG data (title, description, image, url, type, etc.)
     * @return void
     */
    public static function setOpenGraph($og)
    {
        foreach ($og as $key => $value) {
            if ($value !== null && is_scalar($value)) {
                self::addMeta('og:' . $key, (string) $value, 'property');
            }
        }
    }

    /**
     * Set Twitter Card meta tags
     * Chỉ thêm giá trị scalar để meta content luôn an toàn khi render.
     *
     * @param array $twitter Twitter data (card, title, description, image, etc.)
     * @return void
     */
    public static function setTwitterCard($twitter)
    {
        foreach ($twitter as $key => $value) {
            if ($value !== null && is_scalar($value)) {
                self::addMeta('twitter:' . $key, (string) $value, 'name');
            }
        }
    }

    /**
     * Set canonical URL
     * 
     * @param string $url Canonical URL
     * @return void
     */
    public static function setCanonical($url)
    {
        // Apply filter (allow plugins to modify canonical URL)
        if (function_exists('apply_filters')) {
            $url = apply_filters('render.head.set_canonical', $url);
        }
        
        self::$canonical = $url;
    }

    /**
     * Add link tag (profile, alternate/hreflang, icon, apple-touch-icon, …)
     *
     * @param string $rel  rel (profile, alternate, icon, apple-touch-icon, …)
     * @param string $href URL
     * @param array  $attrs Attributes (e.g. ['hreflang' => 'vi', 'type' => 'image/png'])
     * @return void
     */
    public static function addLink($rel, $href, array $attrs = [])
    {
        if ($href === '' && empty($attrs)) {
            return;
        }
        $link = array_merge(['href' => $href], $attrs);
        if (!isset(self::$links[$rel])) {
            self::$links[$rel] = [];
        }
        self::$links[$rel][] = $link;
    }

    /**
     * Add JSON-LD schema bổ sung (render trong Head::render() sau schema chính từ Schema library).
     * Schema chính do Schema::get() trong Head::render(). Dùng addSchema() khi view/plugin cần thêm schema riêng.
     *
     * @param array $schema Schema data (can contain nested @type objects)
     * @param string|null $key Unique key (for replacement/removal)
     * @return void
     */
    public static function addSchema($schema, $key = null)
    {
        // Apply filter for individual schema before adding (allow plugins to modify)
        if (function_exists('apply_filters')) {
            $schema = apply_filters('render.head.schema.add', $schema, $key);
        }
        
        if ($schema !== null) {
            if ($key !== null) {
                self::$schemas[$key] = $schema;
            } else {
                self::$schemas[] = $schema;
            }
        }
    }

    /**
     * Render complete <head> section
     *
     * Giống Schema: nếu đã set context (FrontendController), build mặc định title/meta/OG/canonical
     * chỉ khi view chưa set. View/plugin chỉ cần gọi Head::set* khi override; can thiệp thêm qua filter render.head.defaults.
     *
     * @param array $options Options (title, description, etc.)
     * @return string HTML
     */
    public static function render($options = [])
    {
        // 1. Fill defaults from Head context (layout + payload) – chỉ khi view chưa set
        $headContext = \System\Libraries\Render\Head\Context::getCurrent();
        if ($headContext !== null) {
            if (empty($headContext['payload'])) {
                global $post;
                if (!empty($post)){
                    \System\Libraries\Render\Head\Context::setCurrent($headContext['layout'], $post);
                    $headContext = \System\Libraries\Render\Head\Context::getCurrent();
                }
            }
            $built = \System\Libraries\Render\Head\Builder::build(
                $headContext['layout'],
                $headContext['payload'] ?? null
            );
            if (!empty($built)) {
                if (empty(self::$titleParts) && !empty($built['title_parts'])) {
                    self::setTitle($built['title_parts']);
                }
                if (self::$canonical === null && $built['canonical'] !== '') {
                    self::setCanonical($built['canonical']);
                }
                if (self::$siteName === '' && $built['site_name'] !== '') {
                    self::setSiteName($built['site_name']);
                }
                // OG và Twitter trước setDescription để luôn có đủ og:title, og:url, og:type, twitter:card, twitter:title, twitter:site (setDescription chỉ thêm description)
                $hasOg = false;
                foreach (array_keys(self::$metaTags) as $k) {
                    if (strpos($k, 'og:') === 0) {
                        $hasOg = true;
                        break;
                    }
                }
                if (!$hasOg && !empty($built['og'])) {
                    self::setOpenGraph($built['og']);
                }
                $hasTwitter = false;
                foreach (array_keys(self::$metaTags) as $k) {
                    if (strpos($k, 'twitter:') === 0) {
                        $hasTwitter = true;
                        break;
                    }
                }
                if (!$hasTwitter && !empty($built['twitter'])) {
                    self::setTwitterCard($built['twitter']);
                }
                if (!isset(self::$metaTags['description']) && $built['description'] !== '') {
                    self::setDescription($built['description']);
                }
                if (!isset(self::$metaTags['robots']) && $built['robots'] !== '') {
                    self::addMeta('robots', $built['robots'], 'name');
                }
                // Profile, hreflang, icons (WordPress / Rank Math style)
                if (!empty($built['profile']) && empty(self::$links['profile'])) {
                    self::addLink('profile', 'https://gmpg.org/xfn/11');
                }
                if (!empty($built['hreflang']) && empty(self::$links['alternate'])) {
                    foreach ($built['hreflang'] as $item) {
                        $href = is_array($item) ? ($item['href'] ?? $item['url'] ?? '') : (string) $item;
                        $hreflang = is_array($item) ? ($item['hreflang'] ?? $item['lang'] ?? '') : '';
                        if ($href !== '' && $hreflang !== '') {
                            self::addLink('alternate', $href, ['hreflang' => $hreflang]);
                        }
                    }
                }
                if (!empty($built['icons'])) {
                    if (!empty($built['icons']['favicon']) && empty(self::$links['icon'])) {
                        self::addLink('icon', $built['icons']['favicon'], ['sizes' => '32x32']);
                        self::addLink('icon', $built['icons']['favicon'], ['sizes' => '192x192']);
                    }
                    if (!empty($built['icons']['apple_touch_icon'])) {
                        self::addLink('apple-touch-icon', $built['icons']['apple_touch_icon']);
                    }
                    if (!empty($built['icons']['tile_image']) && !isset(self::$metaTags['msapplication-TileImage'])) {
                        self::addMeta('msapplication-TileImage', $built['icons']['tile_image'], 'name');
                    }
                }
                self::$ogLocaleAlternates = [];
                $altLocales = $built['alternate'] ?? $built['alternate'] ?? null;
                if (!empty($altLocales) && is_array($altLocales)) {
                    foreach ($altLocales as $loc) {
                        if (is_string($loc) && $loc !== '') {
                            self::$ogLocaleAlternates[] = $loc;
                        }
                    }
                }
            }
        }
        // Meta mặc định (generator, language) – chỉ thêm khi view chưa set
        if (!isset(self::$metaTags['generator'])) {
            self::addMeta('generator', 'CMSFullForm', 'name');
        }
        if (!isset(self::$metaTags['language']) && defined('APP_LANG')) {
            self::addMeta('language', APP_LANG, 'name');
        }

        // 2. Apply options (view override)
        if (isset($options['title'])) {
            self::setTitle($options['title']);
        }
        if (isset($options['description'])) {
            self::setDescription($options['description']);
        }
        if (isset($options['keywords'])) {
            self::setKeywords($options['keywords']);
        }

        // 3. Apply filter BEFORE render (allow plugins to modify arrays)
        $metaTags = self::$metaTags;
        $schemas = self::$schemas;
        $canonical = self::$canonical;
        
        if (function_exists('apply_filters')) {
            $metaTags = apply_filters('render.head.meta.before', $metaTags);
            
            // Filter schemas - supports nested structures
            $schemas = array_map(function($schema, $key) {
                return apply_filters('render.head.schema.item', $schema, $key);
            }, $schemas, array_keys($schemas));
            $schemas = apply_filters('render.head.schema.before', $schemas);
            $canonical = apply_filters('render.head.canonical.before', $canonical);
        }

        $html = '';

        // 0. Link tags: profile, hreflang (WordPress / Rank Math style)
        if (!empty(self::$links['profile'])) {
            foreach (self::$links['profile'] as $link) {
                $html .= self::renderLinkTag('profile', $link);
            }
        }
        if (!empty(self::$links['alternate'])) {
            foreach (self::$links['alternate'] as $link) {
                $html .= self::renderLinkTag('alternate', $link);
            }
        }

        // 1. Title
        $title = self::getTitle();
        if (!empty($title)) {
            $html .= "    <title>" . htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</title>\n";
        }

        // 2. Basic Meta Tags (description, keywords, robots)
        $basicMetaOrder = ['description', 'keywords', 'robots', 'author', 'viewport'];
        foreach ($basicMetaOrder as $metaName) {
            if (isset($metaTags[$metaName])) {
                $html .= self::renderMetaTag($metaTags[$metaName]);
                unset($metaTags[$metaName]);
            }
        }

        // 3. Canonical
        if ($canonical) {
            $html .= "    <link rel=\"canonical\" href=\"" . htmlspecialchars($canonical, ENT_QUOTES | ENT_XML1, 'UTF-8') . "\" />\n";
        }

        // 4. Open Graph Meta Tags (grouped)
        $ogMetas = [];
        foreach ($metaTags as $name => $meta) {
            if (strpos($name, 'og:') === 0) {
                $ogMetas[$name] = $meta;
                unset($metaTags[$name]); // Remove from main array to avoid duplicate
            }
        }
        // Sort OG tags: type, title, description, url, image, site_name, article:*, fb:admins, etc.
        $ogOrder = [
            'og:type', 'og:title', 'og:description', 'og:url', 'og:image', 'og:image:secure_url',
            'og:image:type', 'og:image:width', 'og:image:height', 'og:image:alt',
            'og:site_name', 'og:locale', 'og:updated_time',
            'article:publisher', 'article:author', 'article:section', 'article:published_time', 'article:modified_time',
            'fb:admins',
        ];
        foreach ($ogOrder as $ogName) {
            if (isset($ogMetas[$ogName])) {
                $html .= self::renderMetaTag($ogMetas[$ogName]);
                unset($ogMetas[$ogName]);
            }
        }
        // Render remaining OG tags
        foreach ($ogMetas as $meta) {
            $html .= self::renderMetaTag($meta);
        }
        foreach (self::$ogLocaleAlternates as $locAlt) {
            $esc = htmlspecialchars($locAlt, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $html .= "    <meta property=\"og:locale:alternate\" content=\"{$esc}\" />\n";
        }

        // 5. Twitter Card Meta Tags (grouped)
        $twitterMetas = [];
        foreach ($metaTags as $name => $meta) {
            if (strpos($name, 'twitter:') === 0) {
                $twitterMetas[$name] = $meta;
                unset($metaTags[$name]); // Remove from main array to avoid duplicate
            }
        }
        // Sort Twitter tags: card, title, description, image, site, creator, label1/data1, label2/data2
        $twitterOrder = [
            'twitter:card', 'twitter:title', 'twitter:description', 'twitter:image',
            'twitter:site', 'twitter:creator',
            'twitter:label1', 'twitter:data1', 'twitter:label2', 'twitter:data2',
        ];
        foreach ($twitterOrder as $twitterName) {
            if (isset($twitterMetas[$twitterName])) {
                $html .= self::renderMetaTag($twitterMetas[$twitterName]);
                unset($twitterMetas[$twitterName]);
            }
        }
        // Render remaining Twitter tags
        foreach ($twitterMetas as $meta) {
            $html .= self::renderMetaTag($meta);
        }

        // 6. Product Meta Tags (grouped)
        $productMetas = [];
        foreach ($metaTags as $name => $meta) {
            if (strpos($name, 'product:') === 0) {
                $productMetas[$name] = $meta;
                unset($metaTags[$name]); // Remove from main array to avoid duplicate
            }
        }
        // Sort Product tags: price:amount, price:currency, availability, etc.
        $productOrder = ['product:price:amount', 'product:price:currency', 'product:availability', 'product:retailer_item_id', 'product:condition', 'product:brand'];
        foreach ($productOrder as $productName) {
            if (isset($productMetas[$productName])) {
                $html .= self::renderMetaTag($productMetas[$productName]);
                unset($productMetas[$productName]);
            }
        }
        // Render remaining Product tags
        foreach ($productMetas as $meta) {
            $html .= self::renderMetaTag($meta);
        }

        // 7. Other Meta Tags (remaining)
        foreach ($metaTags as $meta) {
            $html .= self::renderMetaTag($meta);
        }

        // 8. JSON-LD: schema chính (Schema – context từ FrontendController) + schema bổ sung (addSchema)
        $schemaOutput = Schema::get();
        if ($schemaOutput !== '') {
            $html .= $schemaOutput;
        }
        if (!empty($schemas)) {
            $html .= self::renderSchemasFiltered($schemas);
        }

        // 9. Link tags: icon, apple-touch-icon (WordPress / Rank Math style)
        if (!empty(self::$links['icon'])) {
            foreach (self::$links['icon'] as $link) {
                $html .= self::renderLinkTag('icon', $link);
            }
        }
        if (!empty(self::$links['apple-touch-icon'])) {
            foreach (self::$links['apple-touch-icon'] as $link) {
                $html .= self::renderLinkTag('apple-touch-icon', $link);
            }
        }

        // Apply filter AFTER render (allow plugins to modify HTML output)
        if (function_exists('apply_filters')) {
            $html = apply_filters('render.head.after', $html);
        }

        return $html;
    }

    /**
     * Render a meta tag
     * 
     * @param array $meta Meta data
     * @return string HTML
     */
    private static function renderMetaTag($meta)
    {
        $name = htmlspecialchars($meta['name'], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $content = htmlspecialchars($meta['content'], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $type = $meta['type'];

        if ($type === 'property') {
            return "    <meta property=\"{$name}\" content=\"{$content}\" />\n";
        } elseif ($type === 'http-equiv') {
            return "    <meta http-equiv=\"{$name}\" content=\"{$content}\" />\n";
        } else {
            return "    <meta name=\"{$name}\" content=\"{$content}\" />\n";
        }
    }

    /**
     * Render link tag
     *
     * @param string $rel  rel (profile, alternate, icon, apple-touch-icon)
     * @param array  $link ['href' => url, 'hreflang' => ?, 'sizes' => ?, 'type' => ?]
     * @return string
     */
    private static function renderLinkTag($rel, array $link)
    {
        $href = $link['href'] ?? '';
        if ($href === '') {
            return '';
        }
        $href = htmlspecialchars($href, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $out = '    <link rel="' . htmlspecialchars($rel, ENT_QUOTES | ENT_XML1, 'UTF-8') . '" href="' . $href . '"';
        unset($link['href']);
        foreach ($link as $attr => $value) {
            if ($value !== '' && $value !== null) {
                $out .= ' ' . htmlspecialchars($attr, ENT_QUOTES | ENT_XML1, 'UTF-8') . '="' . htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8') . '"';
            }
        }
        $out .= " />\n";
        return $out;
    }

    /** JSON encode flags for schema: XSS-safe (JSON_HEX_TAG = escape < and > to prevent </script> breakout) */
    private static $schemaJsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_HEX_TAG;

    /**
     * Recursively sanitize schema string values (strip tags, control chars) for XSS-safe JSON-LD.
     *
     * @param mixed $value Schema value
     * @return mixed Sanitized value
     */
    private static function sanitizeSchemaValue($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeSchemaValue'], $value);
        }
        if (is_string($value)) {
            $value = strip_tags($value);
            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
            return $value;
        }
        return $value;
    }

    /**
     * Render JSON-LD schemas (using filtered schemas)
     * 
     * Supports nested schemas (e.g., Product with nested Organization, Review, AggregateRating, etc.)
     * 
     * Nested schemas can be:
     * 1. Inline (kept within parent schema) - e.g., AggregateRating in Product
     * 2. Extracted to @graph (when multiple top-level schemas exist)
     * 
     * Plugins can use filters to modify nested structures before rendering.
     * 
     * @param array $schemas Filtered schemas array (can contain nested structures)
     * @return string HTML
     */
    private static function renderSchemasFiltered($schemas)
    {
        // Remove null schemas (filtered out)
        $schemas = array_filter($schemas, function($schema) {
            return $schema !== null && is_array($schema);
        });
        
        if (empty($schemas)) {
            return '';
        }
        
        $html = "<script type=\"application/ld+json\">\n";
        
        // Single schema - render directly (nested objects stay inline)
        if (count($schemas) === 1) {
            $schema = self::sanitizeSchemaValue(reset($schemas));
            if (!isset($schema['@context'])) {
                $schema['@context'] = 'https://schema.org';
            }
            $encoded = json_encode($schema, self::$schemaJsonFlags);
            $html .= ($encoded !== false) ? $encoded : '{}';
        } else {
            $graph = [
                '@context' => 'https://schema.org',
                '@graph' => array_values(array_map([self::class, 'sanitizeSchemaValue'], $schemas)),
            ];
            $encoded = json_encode($graph, self::$schemaJsonFlags);
            $html .= ($encoded !== false) ? $encoded : '{"@context":"https://schema.org","@graph":[]}';
        }
        $html .= "\n</script>\n";
        return $html;
    }

    /**
     * Render JSON-LD schemas (legacy method - uses self::$schemas)
     * 
     * @return string HTML
     */
    private static function renderSchemas()
    {
        return self::renderSchemasFiltered(self::$schemas);
    }

    /**
     * Clear all head data (title, meta, schema, canonical, links, siteName, separator)
     *
     * @return void
     */
    public static function clear()
    {
        self::$titleParts = [];
        self::$metaTags = [];
        self::$schemas = [];
        self::$canonical = null;
        self::$links = [];
        self::$ogLocaleAlternates = [];
        self::$siteName = '';
        self::$titleSeparator = '|';
    }
}

