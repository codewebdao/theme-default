<?php
/**
 * Theme functions (Frontend)
 * Được load bởi FrontendController::_loadFunctions() trước khi render.
 *
 * SEO: filter render.head.defaults — trang chủ + page / page-* (CMS + option seo_config JSON).
 * Meta keywords: render.head.defaults gán $GLOBALS['cmsfullform_head_keywords_pending'];
 *   render.head.meta.before (meta_bootstrap.php) đưa vào HTML — không dùng view_head_before (chạy trước khi Builder build).
 * Meta bổ sung (author, theme-color, preload, robots seo_follow, og image fallback): parts/headers/_metas/meta_bootstrap.php
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

if (function_exists('add_filter')) {
    add_filter('render_data', static function ($data, $layout, $renderPath = null) {
        if (!is_array($data)) {
            $data = [];
        }
        $data['layout'] = $layout;

        return $data;
    }, 10, 3);
}

// Global assets (View::addJs/addCss — bucket web qua ThemeContext)
use System\Libraries\Render\View;

View::addCss('fonts', 'css/fonts.css', [], null, 'all', false, false, false);
View::addCss('home-index', 'css/home-index.css', [], null, 'all', false, false, false);
// Mobile menu (menu-mobi.php): header.php gọi window.jModal — phải có trước script inline trong body
View::addJs('jmodal', 'js/jmodal.js', [], null, false, false, false, false);
// Bỏ đăng ký các file không tồn tại (thêm file vào assets/js/ rồi bật lại nếu cần):
// View::addJs('lazysizes', 'js/lazysizes.min.js', [], null, false, false, false, false);
// View::addJs('main', 'js/main.js', [], null, false, false, false, false);
// View::addJs('blaze-slider', 'js/blaze-slider.min.js', [], null, false, false, false, false);

// --- Ảnh theme: srcset + optional <picture> mobile -400 (build: scripts/build-theme-responsive-webp.php)

if (!function_exists('cmsfullform_theme_asset_fs_path')) {
    function cmsfullform_theme_asset_fs_path(string $relative): string
    {
        if (!defined('APP_THEME_PATH')) {
            return '';
        }
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relative, '/\\'));

        return rtrim((string) APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $relative;
    }
}

if (!function_exists('cmsfullform_theme_responsive_webp_data')) {
    /**
     * @param int[] $variantWidths
     * @return array{src:string,srcset:string,width:int,height:int,preload_href:string}
     */
    function cmsfullform_theme_responsive_webp_data(string $relative, array $variantWidths): array
    {
        $relative = str_replace('\\', '/', trim($relative, '/'));
        if ($relative === '' || !function_exists('theme_assets')) {
            return [];
        }
        $full = cmsfullform_theme_asset_fs_path($relative);
        if ($full === '' || !is_file($full)) {
            return [];
        }
        $dim = @getimagesize($full);
        $iw = is_array($dim) ? (int) ($dim[0] ?? 0) : 0;
        $ih = is_array($dim) ? (int) ($dim[1] ?? 0) : 0;
        if ($iw <= 0 || $ih <= 0) {
            return [];
        }

        $dir = trim(str_replace('\\', '/', dirname($relative)), '/');
        $stem = pathinfo($relative, PATHINFO_FILENAME);
        $origUrl = theme_assets($relative);

        $entries = [];
        $haveW = [];

        $variantWidths = array_values(array_unique(array_map('intval', $variantWidths), SORT_REGULAR));
        sort($variantWidths, SORT_NUMERIC);

        foreach ($variantWidths as $w) {
            if ($w <= 0 || $w >= $iw) {
                continue;
            }
            $relVar = ($dir === '' || $dir === '.') ? ($stem . '-' . $w . '.webp') : ($dir . '/' . $stem . '-' . $w . '.webp');
            if (!is_file(cmsfullform_theme_asset_fs_path($relVar))) {
                continue;
            }
            $entries[] = ['w' => $w, 'url' => theme_assets($relVar)];
            $haveW[$w] = true;
        }

        if (!isset($haveW[$iw])) {
            $entries[] = ['w' => $iw, 'url' => $origUrl];
        }

        if ($entries === []) {
            return [
                'src'          => $origUrl,
                'srcset'       => '',
                'width'        => $iw,
                'height'       => $ih,
                'preload_href' => $origUrl,
            ];
        }

        usort($entries, static function ($a, $b) {
            return $a['w'] <=> $b['w'];
        });

        $srcsetParts = [];
        foreach ($entries as $e) {
            $srcsetParts[] = $e['url'] . ' ' . $e['w'] . 'w';
        }

        return [
            'src'          => $entries[count($entries) - 1]['url'],
            'srcset'       => implode(', ', $srcsetParts),
            'width'        => $iw,
            'height'       => $ih,
            'preload_href' => $entries[0]['url'],
        ];
    }
}

if (!function_exists('cmsfullform_theme_responsive_webp_img')) {
    /**
     * @param array<string, mixed> $attributes alt, class, sizes, loading, fetchpriority, decoding,
     *   mobile_webp_width (int): nếu có file {stem}-{n}.webp → <picture><source media max-width>…
     *   mobile_webp_bp (int): breakpoint px, mặc định 640
     */
    function cmsfullform_theme_responsive_webp_img(string $relative, array $variantWidths, array $attributes = []): string
    {
        $mobileWebpW = isset($attributes['mobile_webp_width']) ? (int) $attributes['mobile_webp_width'] : 0;
        $mobileWebpBp = isset($attributes['mobile_webp_bp']) ? (int) $attributes['mobile_webp_bp'] : 640;
        if ($mobileWebpBp <= 0) {
            $mobileWebpBp = 640;
        }
        unset($attributes['mobile_webp_width'], $attributes['mobile_webp_bp']);

        $data = cmsfullform_theme_responsive_webp_data($relative, $variantWidths);
        if ($data === []) {
            return '';
        }

        $alt = isset($attributes['alt']) ? (string) $attributes['alt'] : '';
        unset($attributes['alt']);
        $sizes = isset($attributes['sizes']) ? (string) $attributes['sizes'] : '';
        unset($attributes['sizes']);

        $esc = static function (string $s): string {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };

        $img = '<img src="' . $esc($data['src']) . '"';
        if ($data['srcset'] !== '') {
            $img .= ' srcset="' . $esc($data['srcset']) . '"';
        }
        if ($sizes !== '') {
            $img .= ' sizes="' . $esc($sizes) . '"';
        }
        $img .= ' width="' . $esc((string) $data['width']) . '" height="' . $esc((string) $data['height']) . '" alt="' . $esc($alt) . '"';

        if (!array_key_exists('decoding', $attributes)) {
            $attributes['decoding'] = 'async';
        }

        foreach ($attributes as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $img .= ' ' . $esc((string) $k) . '="' . $esc((string) $v) . '"';
        }
        $img .= '>';

        $mobileUrl = '';
        if ($mobileWebpW > 0 && function_exists('theme_assets')) {
            $relNorm = str_replace('\\', '/', trim($relative, '/'));
            $dir = trim(str_replace('\\', '/', dirname($relNorm)), '/');
            $stem = pathinfo($relNorm, PATHINFO_FILENAME);
            $relVar = ($dir === '' || $dir === '.') ? ($stem . '-' . $mobileWebpW . '.webp') : ($dir . '/' . $stem . '-' . $mobileWebpW . '.webp');
            if (is_file(cmsfullform_theme_asset_fs_path($relVar))) {
                $mobileUrl = theme_assets($relVar);
            }
        }

        if ($mobileUrl !== '') {
            return '<picture><source media="(max-width: ' . (int) $mobileWebpBp . 'px)" srcset="' . $esc($mobileUrl) . '" type="image/webp">' . $img . '</picture>';
        }

        return $img;
    }
}

// --- SEO (theme only): seo_config + trang CMS ---------------------------------

if (!function_exists('cmsfullform_seo_config_rows')) {
    function cmsfullform_seo_config_rows($lang)
    {
        $raw = option('seo_config', $lang !== '' ? $lang : (defined('APP_LANG') ? APP_LANG : ''));
        if (is_array($raw)) {
            return array_values(array_filter($raw, 'is_array'));
        }
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }
}

if (!function_exists('cmsfullform_seo_pick_index_row')) {
    function cmsfullform_seo_pick_index_row(array $rows)
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $fn = strtolower(trim((string) ($row['function'] ?? '')));
            if ($fn === 'index' || $fn === 'home' || $fn === 'front-page') {
                return $row;
            }
            if ($fn !== '' && preg_match('/::index$/', $fn)) {
                return $row;
            }
        }

        return (!empty($rows) && is_array($rows[0])) ? $rows[0] : [];
    }
}

if (!function_exists('cmsfullform_seo_pick_page_row')) {
    function cmsfullform_seo_pick_page_row(array $rows, string $pageSlug)
    {
        $needle = strtolower(trim($pageSlug));
        if ($needle === '') {
            return [];
        }
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $fn = strtolower(trim((string) ($row['function'] ?? '')));
            $sl = strtolower(trim((string) ($row['slug'] ?? $row['page'] ?? '')));
            if ($sl !== '' && $sl === $needle) {
                return $row;
            }
            if ($fn !== '' && ($fn === $needle || $fn === 'page-' . $needle)) {
                return $row;
            }
        }

        return [];
    }
}

if (!function_exists('cmsfullform_seo_apply_tokens')) {
    function cmsfullform_seo_apply_tokens(string $text, string $siteTitle, string $siteDesc)
    {
        if ($text === '') {
            return '';
        }

        return trim(str_replace(
            ['%site_title%', '%site_desc%', '%seo_desc%', '%site_name%'],
            [$siteTitle, $siteDesc, $siteDesc, $siteTitle],
            $text
        ));
    }
}

if (!function_exists('cmsfullform_seo_page_slug')) {
    function cmsfullform_seo_page_slug(string $layout, $payload)
    {
        $s = '';
        if (is_array($payload) && isset($payload['slug'])) {
            $s = trim((string) $payload['slug']);
        } elseif (is_object($payload) && isset($payload->slug)) {
            $s = trim((string) $payload->slug);
        }
        if ($s !== '') {
            return $s;
        }
        if ($layout !== 'page' && strpos($layout, 'page-') === 0 && strlen($layout) > 5) {
            return substr($layout, 5);
        }

        return '';
    }
}

if (!function_exists('cmsfullform_payload_pick')) {
    /**
     * @param array<int, string> $keys
     */
    function cmsfullform_payload_pick($payload, array $keys, bool $nonEmptyOnly = false): string
    {
        foreach ($keys as $key) {
            if (is_array($payload) && array_key_exists($key, $payload)) {
                $v = $payload[$key];
                if ($nonEmptyOnly) {
                    $s = is_string($v) ? trim($v) : (is_scalar($v) && $v !== null ? trim((string) $v) : '');
                    if ($s !== '') {
                        return $s;
                    }
                } else {
                    return is_string($v) ? $v : (string) $v;
                }
            }
            if (is_object($payload) && isset($payload->$key)) {
                $v = $payload->$key;
                if ($nonEmptyOnly) {
                    $s = is_string($v) ? trim($v) : (is_scalar($v) && $v !== null ? trim((string) $v) : '');
                    if ($s !== '') {
                        return $s;
                    }
                } else {
                    return is_string($v) ? $v : (string) $v;
                }
            }
        }

        return '';
    }
}

if (!function_exists('cmsfullform_payload_seo_data')) {
    function cmsfullform_payload_seo_data($payload, string $field)
    {
        $raw = null;
        if (is_array($payload) && array_key_exists('seo_data', $payload)) {
            $raw = $payload['seo_data'];
        } elseif (is_object($payload) && isset($payload->seo_data)) {
            $raw = $payload->seo_data;
        }
        if ($raw === null || $raw === '') {
            return '';
        }
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $data = $raw;
        } else {
            return '';
        }
        if (!array_key_exists($field, $data)) {
            return '';
        }
        $v = $data[$field];

        return is_string($v) ? trim($v) : (is_scalar($v) && $v !== null ? trim((string) $v) : '');
    }
}

if (!function_exists('cmsfullform_seo_build_website_og')) {
    function cmsfullform_seo_build_website_og($title, $description, $url, $image, $locale)
    {
        $siteName = option('site_title', defined('APP_LANG') ? APP_LANG : '') ?: '';
        $og = [
            'locale'      => $locale,
            'type'        => 'website',
            'title'       => $title,
            'description' => $description,
            'url'         => $url,
            'site_name'   => $siteName,
        ];
        if ($image) {
            $og['image'] = $image;
            $og['image:secure_url'] = $image;
        }
        $fb = option('fb_admins', defined('APP_LANG') ? APP_LANG : '') ?: '';
        if ($fb !== '') {
            $og['fb:admins'] = $fb;
        }

        return $og;
    }
}

if (!function_exists('cmsfullform_seo_merge_og_image')) {
    function cmsfullform_seo_merge_og_image(array &$og, $image, $alt = '')
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
    }
}

if (!function_exists('cmsfullform_seo_build_twitter')) {
    function cmsfullform_seo_build_twitter($title, $description, $image)
    {
        $siteName = option('site_title', defined('APP_LANG') ? APP_LANG : '') ?: '';
        $tw = [
            'card'        => 'summary_large_image',
            'title'       => $title,
            'description' => $description,
            'site'        => '@' . preg_replace('/\s+/', '', $siteName),
        ];
        if ($image) {
            $tw['image'] = $image;
        }

        return $tw;
    }
}

/**
 * JSON-LD (Schema.org) — tương đương meta_index.php (Organization + WebSite + WebPage) nhưng KHÔNG echo trùng.
 *
 * Graph gốc: view_head() → Head::render() → Schema::get() (Types: website, organization, breadcrumb, webpage).
 * Email & phone Organization: core system/Libraries/Render/Schema/Types/organization.php + schema_resolve_organization_email()
 * (site_email theo lang + all, fallback mail_from_address trong option JSON "email") và schema_resolve_organization_phone() (site_phone).
 * Filter bên dưới chỉ bổ sung: địa chỉ (site_address), alternateName (site_brand), publisher (WebPage trang chủ).
 * Kiểm tra: Rich Results Test, Validator Schema.org.
 */
if (function_exists('add_filter')) {
    add_filter('schema.organization', function (array $schema, $ctx) {
        $lang = defined('APP_LANG') ? APP_LANG : '';
        $brand = trim((string) (option('site_brand', $lang) ?: ''));
        $title = trim((string) (option('site_title', $lang) ?: ''));
        if ($brand !== '' && $brand !== $title && function_exists('schema_safe_string')) {
            $schema['alternateName'] = schema_safe_string($brand);
        }
        $addr = option('site_address', $lang);
        $addrStr = is_string($addr) ? trim($addr) : '';
        if ($addrStr !== '' && function_exists('schema_safe_string')) {
            $schema['address'] = [
                '@type'         => 'PostalAddress',
                'streetAddress' => schema_safe_string($addrStr),
            ];
        }

        return $schema;
    }, 10, 2);

    add_filter('schema.webpage', function (array $schema, $ctx) {
        if (($ctx->type ?? '') !== 'front') {
            return $schema;
        }
        $base = rtrim(base_url(), '/');
        $schema['publisher'] = ['@id' => $base . '/#organization'];

        return $schema;
    }, 10, 2);

    add_filter('render.head.defaults', function ($result, $layout, $payload) {
        if (!is_array($result)) {
            return $result;
        }

        $lang = defined('APP_LANG') ? APP_LANG : '';
        $siteName = option('site_title', $lang) ?: '';
        $siteDesc = option('site_desc', $lang) ?? '';
        $baseUrl = rtrim(base_url(), '/');
        $siteLogo = option('site_logo', $lang);
        $logoUrl = $siteLogo ? _img_url($siteLogo, 'original') : '';
        if (!$logoUrl && function_exists('theme_assets')) {
            $logoUrl = theme_assets('images/logo/logo-icon.webp');
        }
        $locale = (defined('APP_LANG') && APP_LANG === 'en') ? 'en_US' : 'vi_VN';

        $GLOBALS['cmsfullform_head_keywords_pending'] = '';

        if ($layout === '' || $layout === 'front-page' || $layout === 'index') {
            $rows = cmsfullform_seo_config_rows($lang);
            $row = cmsfullform_seo_pick_index_row($rows);
            if (!empty($row)) {
                $t = cmsfullform_seo_apply_tokens(trim((string) ($row['seo_title'] ?? $row['title'] ?? '')), $siteName, $siteDesc);
                if ($t !== '') {
                    $result['title_parts'] = [$t];
                }
                $d = cmsfullform_seo_apply_tokens(trim((string) ($row['seo_desc'] ?? $row['description'] ?? '')), $siteName, $siteDesc);
                if ($d !== '') {
                    $result['description'] = $d;
                }
                $kw = cmsfullform_seo_apply_tokens(
                    trim((string) ($row['seo_keywords'] ?? $row['keywords'] ?? '')),
                    $siteName,
                    $siteDesc
                );
                if ($kw === '') {
                    $kw = trim((string) option('site_keywords', $lang));
                }
                $GLOBALS['cmsfullform_head_keywords_pending'] = $kw;
                $rb = trim((string) ($row['meta_robots'] ?? $row['robots'] ?? ''));
                if ($rb !== '') {
                    $result['robots'] = $rb;
                }
                $title0 = $result['title_parts'][0] ?? $siteName;
                $desc0 = $result['description'] ?? $siteDesc;
                $result['og'] = cmsfullform_seo_build_website_og($title0, $desc0, $baseUrl, $logoUrl, $locale);
                $result['og']['updated_time'] = date('c');
                $result['twitter'] = cmsfullform_seo_build_twitter($title0, $desc0, $logoUrl);
            } else {
                $GLOBALS['cmsfullform_head_keywords_pending'] = trim((string) option('site_keywords', $lang));
            }

            return $result;
        }

        if ($layout === 'page' || (strpos($layout, 'page-') === 0 && $layout !== 'page-')) {
            if ($payload && (is_array($payload) || is_object($payload))) {
                $rows = cmsfullform_seo_config_rows($lang);
                $slug = cmsfullform_seo_page_slug($layout, $payload);
                $seoRow = cmsfullform_seo_pick_page_row($rows, $slug);

                $presetTitle = !empty($seoRow)
                    ? cmsfullform_seo_apply_tokens(trim((string) ($seoRow['seo_title'] ?? $seoRow['title'] ?? '')), $siteName, $siteDesc)
                    : '';
                $presetDesc = !empty($seoRow)
                    ? cmsfullform_seo_apply_tokens(trim((string) ($seoRow['seo_desc'] ?? $seoRow['description'] ?? '')), $siteName, $siteDesc)
                    : '';
                $presetKw = !empty($seoRow)
                    ? cmsfullform_seo_apply_tokens(
                        trim((string) ($seoRow['seo_keywords'] ?? $seoRow['keywords'] ?? '')),
                        $siteName,
                        $siteDesc
                    )
                    : '';

                $titleMain = cmsfullform_payload_pick($payload, ['seo_title', 'title', 'post_title'], true);
                if ($titleMain === '') {
                    $titleMain = cmsfullform_payload_seo_data($payload, 'title');
                }
                if ($titleMain === '') {
                    $titleMain = $presetTitle;
                }
                $displayTitle = $titleMain !== '' ? $titleMain : trim((string) $siteName);
                if ($displayTitle === '') {
                    $displayTitle = __('Page', $lang ?: null);
                }
                $result['title_parts'] = [$displayTitle];

                $desc = cmsfullform_payload_pick($payload, ['seo_desc', 'description', 'excerpt', 'post_excerpt'], true);
                if ($desc === '') {
                    $desc = cmsfullform_payload_seo_data($payload, 'description');
                }
                if ($desc === '') {
                    $desc = $presetDesc;
                }
                if ($desc === '') {
                    $desc = $titleMain !== '' ? $titleMain : $displayTitle;
                }
                if ($desc === '') {
                    $desc = trim((string) $siteDesc);
                }
                $result['description'] = $desc;

                $kw = cmsfullform_payload_pick($payload, ['seo_keywords', 'meta_keywords'], true);
                if ($kw === '') {
                    $kw = cmsfullform_payload_seo_data($payload, 'seo_keywords');
                }
                if ($kw === '') {
                    $kw = cmsfullform_payload_seo_data($payload, 'keywords');
                }
                if ($kw === '') {
                    $kw = $presetKw;
                }
                if ($kw === '') {
                    $kw = trim((string) option('site_keywords', $lang));
                }
                $GLOBALS['cmsfullform_head_keywords_pending'] = $kw;

                $url = trim((string) cmsfullform_payload_pick($payload, ['url'], false));
                if ($url === '') {
                    $slugOnly = cmsfullform_payload_pick($payload, ['slug'], true);
                    if ($slugOnly !== '') {
                        $url = rtrim((string) base_url($slugOnly, $lang), '/');
                    }
                }
                $result['canonical'] = $url !== '' ? $url : $baseUrl;
                $result['robots'] = 'index, follow';
                if (!empty($seoRow)) {
                    $rb = trim((string) ($seoRow['meta_robots'] ?? $seoRow['robots'] ?? ''));
                    if ($rb !== '') {
                        $result['robots'] = $rb;
                    }
                }

                $image = cmsfullform_payload_pick($payload, ['thumbnail', 'image'], false);
                $og = cmsfullform_seo_build_website_og($displayTitle, $desc, $result['canonical'], $logoUrl, $locale);
                if ($image !== '' && $image !== null && $image !== '0') {
                    cmsfullform_seo_merge_og_image($og, $image, $displayTitle);
                }
                $modRaw = cmsfullform_payload_pick($payload, ['updated_at', 'post_modified'], true);
                if ($modRaw !== '') {
                    $ts = strtotime($modRaw);
                    $og['updated_time'] = ($ts !== false) ? date('c', $ts) : date('c');
                } else {
                    $og['updated_time'] = date('c');
                }
                $result['og'] = $og;
                $ogImg = $og['image'] ?? $logoUrl;
                $result['twitter'] = cmsfullform_seo_build_twitter($displayTitle, $desc, $ogImg);
            } else {
                $GLOBALS['cmsfullform_head_keywords_pending'] = trim((string) option('site_keywords', $lang));
            }
        }

        // Layout không thuộc index / page-* (contact, features, download, blog, archive-*, single-*, …)
        // hoặc payload không có keywords: lấy từ payload + seo_config (slug/layout) + site_keywords.
        if (trim((string) ($GLOBALS['cmsfullform_head_keywords_pending'] ?? '')) === '') {
            $kw = '';
            if ($payload && (is_array($payload) || is_object($payload))) {
                $kw = cmsfullform_payload_pick($payload, ['seo_keywords', 'meta_keywords'], true);
                if ($kw === '') {
                    $kw = cmsfullform_payload_seo_data($payload, 'seo_keywords');
                }
                if ($kw === '') {
                    $kw = cmsfullform_payload_seo_data($payload, 'keywords');
                }
            }
            if ($kw === '') {
                $rows = cmsfullform_seo_config_rows($lang);
                $slug = cmsfullform_seo_page_slug($layout, $payload);
                $layoutLc = strtolower((string) $layout);
                $slugLc = strtolower((string) $slug);
                $candidates = array_unique(array_filter([
                    $layoutLc,
                    $slugLc,
                    ($slugLc !== '' && strpos($layout, 'page-') !== 0) ? 'page-' . $slugLc : '',
                    (strpos($layout, 'page-') === 0 && strlen($layout) > 5) ? strtolower(substr($layout, 5)) : '',
                ]));
                foreach ($candidates as $key) {
                    if ($key === '') {
                        continue;
                    }
                    $seoRow = cmsfullform_seo_pick_page_row($rows, $key);
                    if (!empty($seoRow)) {
                        $kw = cmsfullform_seo_apply_tokens(
                            trim((string) ($seoRow['seo_keywords'] ?? $seoRow['keywords'] ?? '')),
                            $siteName,
                            $siteDesc
                        );
                        if ($kw !== '') {
                            break;
                        }
                    }
                }
            }
            if ($kw === '') {
                $kw = trim((string) option('site_keywords', $lang));
            }
            $GLOBALS['cmsfullform_head_keywords_pending'] = $kw;
        }

        return $result;
    }, 20, 3);
}

if (!function_exists('cmsfullform_youtube_parse_id')) {
    /**
     * Trích video ID từ URL YouTube (embed, watch, shorts, youtu.be). Rỗng nếu không hợp lệ.
     */
    function cmsfullform_youtube_parse_id(string $url): string
    {
        $url = trim($url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return '';
        }
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return '';
        }
        $isYoutube = (bool) preg_match('/(^|\.)youtube\.com$/i', $host) || $host === 'youtu.be';
        if (!$isYoutube) {
            return '';
        }
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{6,32})#', $url, $m)) {
            return $m[1];
        }

        return '';
    }
}

if (!function_exists('cmsfullform_youtube_embed_url')) {
    /**
     * Chuẩn hóa link YouTube → URL embed cho iframe.
     */
    function cmsfullform_youtube_embed_url(string $url): string
    {
        $id = cmsfullform_youtube_parse_id($url);

        return $id !== '' ? 'https://www.youtube.com/embed/' . $id : '';
    }
}

if (!function_exists('cmsfullform_youtube_video_id')) {
    /**
     * Video ID cho thumbnail (i.ytimg.com).
     */
    function cmsfullform_youtube_video_id(string $url): string
    {
        return cmsfullform_youtube_parse_id($url);
    }
}

$__cmsfullform_meta_boot = __DIR__ . '/parts/headers/_metas/meta_bootstrap.php';
if (is_file($__cmsfullform_meta_boot)) {
    require_once $__cmsfullform_meta_boot;
}
unset($__cmsfullform_meta_boot);
