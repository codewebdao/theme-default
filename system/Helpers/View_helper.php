<?php
/**
 * View Helper Functions
 * 
 * WordPress-style template functions for Render V2
 * 
 * @package System\Helpers
 */

// Prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;

// ========================================================================
// TEMPLATE FUNCTIONS (giống WordPress)
// ========================================================================

if (!function_exists('view_render')) {
    /**
     * Render template và trả về HTML (gọi View::make($template, $data)->render()).
     *
     * @param string $template Tên template (vd. parts/home/banner, @Plugin/path)
     * @param array $data Data truyền vào template
     * @return string HTML
     */
    function view_render($template, $data = [])
    {
        return View::make($template, $data)->render();
    }
}

if (!function_exists('view_header')) {
    /**
     * Include header template (giống WordPress view_header())
     * 
     * WordPress-style: view_header() hoặc view_header(['meta' => '...', 'schema' => '...'])
     * 
     * @param string|array $name Header variant name hoặc array data (như WordPress)
     * @param array $data Data to pass (nếu $name là string)
     * @return void
     */
    function view_header($name = null, $data = [])
    {
        // WordPress-style: nếu $name là array, đó là data
        if (is_array($name) && !isset($data['meta']) && !isset($data['schema'])) {
            $data = $name;
            $name = null;
        }
        
        // Apply filter (allow plugins to modify header data)
        if (function_exists('apply_filters')) {
            $data = apply_filters('render.view_header', $data, $name);
        }
        
        // Hook: before view_header
        if (function_exists('do_action')) {
            do_action('render.view_header_before', $name, $data);
        }
        
        // Header tại root theme: header-{name}.php, header.php
        $templates = [];
        if ($name) {
            $templates[] = "header-{$name}";
        }
        $templates[] = "header";

        // Try each template (namespace sẽ được lấy tự động từ View::getNamespace() trong engine)
        foreach ($templates as $template) {
            $result = View::includeIf($template, $data);
            if (!empty($result)) {
                echo $result;
                break;
            }
        }
        
        // Hook: after view_header
        if (function_exists('do_action')) {
            do_action('render.view_header_after', $name, $data);
        }
    }
}

if (!function_exists('view_footer')) {
    /**
     * Include footer template (giống WordPress view_footer())
     * 
     * WordPress-style: view_footer() hoặc view_footer(['data' => '...'])
     * 
     * @param string|array $name Footer variant name hoặc array data (như WordPress)
     * @param array $data Data to pass (nếu $name là string)
     * @return void
     */
    function view_footer($name = null, $data = [])
    {
        // WordPress-style: nếu $name là array, đó là data
        if (is_array($name)) {
            $data = $name;
            $name = null;
        }
        
        // Apply filter (allow plugins to modify footer data)
        if (function_exists('apply_filters')) {
            $data = apply_filters('render.view_footer', $data, $name);
        }
        
        // Hook: before view_footer
        if (function_exists('do_action')) {
            do_action('render.view_footer_before', $name, $data);
        }
        
        // Footer tại root theme: footer-{name}.php, footer.php
        $templates = [];
        if ($name) {
            $templates[] = "footer-{$name}";
        }
        $templates[] = "footer";
        
        // Try each template (namespace sẽ được lấy tự động từ View::getNamespace() trong engine)
        foreach ($templates as $template) {
            $result = View::includeIf($template, $data);
            if (!empty($result)) {
                echo $result;
                break;
            }
        }
        // Hook: after view_footer
        if (function_exists('do_action')) {
            do_action('render.view_footer_after', $name, $data);
        }
    }
}

if (!function_exists('view_sidebar')) {
    /**
     * Include sidebar template (giống WordPress view_sidebar())
     * 
     * @param string $name Sidebar variant name
     * @param array $data Data to pass
     * @return void
     */
    function view_sidebar($name = null, $data = [])
    {
        $template = $name ? 'parts/sidebar-' . $name : 'parts/sidebar';
        echo View::includeIf($template, $data);
    }
}

if (!function_exists('view_template_part')) {
    /**
     * Include template part (giống WordPress view_template_part())
     * 
     * @param string $slug Template slug
     * @param string $name Template name
     * @param array $data Data to pass
     * @return void
     */
    function view_template_part($slug, $name = null, $data = [])
    {
        $templates = [];
        
        if ($name) {
            $templates[] = "{$slug}-{$name}";
        }
        $templates[] = $slug;
        
        // Try each template
        foreach ($templates as $template) {
            $result = View::includeIf($template, $data);
            if (!empty($result)) {
                echo $result;
                return;
            }
        }
    }
}

// ========================================================================
// HEAD FUNCTIONS (giống WordPress wp_head())
// ========================================================================

if (!function_exists('assets_head')) {
    /**
     * Render footer scripts
     * 
     * Outputs:
     * - Enqueued scripts
     * - Hooks for plugins
     * 
     * @return void
     */
    function assets_head()
    {
        // Hook: before_footer_scripts
        if (function_exists('do_action')) {
            do_action('assets_head_before');
        }

        // Render styles và scripts (bucket web|admin theo ThemeContext::getScope())
        echo View::css('head');
        echo View::js('head');

        // Hook: assets_footer (allow plugins to add content)
        if (function_exists('do_action')) {
            do_action('assets_head');
        }

        // Hook: after_footer_scripts
        if (function_exists('do_action')) {
            do_action('assets_head_after');
        }
    }
}

if (!function_exists('assets_footer')) {
    /**
     * Render footer scripts
     * 
     * Outputs:
     * - Enqueued scripts
     * - Hooks for plugins
     * 
     * @return void
     */
    function assets_footer()
    {
        // Hook: before_footer_scripts
        if (function_exists('do_action')) {
            do_action('assets_footer_before');
        }

        // Render styles và scripts (bucket web|admin theo ThemeContext::getScope())
        echo View::css('footer');
        echo View::js('footer');

        // Hook: assets_footer (allow plugins to add content)
        if (function_exists('do_action')) {
            do_action('assets_footer');
        }

        // Hook: after_footer_scripts
        if (function_exists('do_action')) {
            do_action('assets_footer_after');
        }
    }
}


if (!function_exists('view_head')) {
    /**
     * Render <head> section
     *
     * Outputs (qua Head::render()): title, meta, canonical, JSON-LD schema (Schema library + addSchema), rồi assets_head + hooks.
     *
     * @return void
     */
    function view_head()
    {
        if (function_exists('do_action')) {
            do_action('view_head_before');
        }

        echo \System\Libraries\Render\Head::render();

        assets_head();

        if (function_exists('do_action')) {
            do_action('view_head');
        }
        if (function_exists('do_action')) {
            do_action('view_head_after');
        }
    }
}

// ========================================================================
// HELPER FUNCTIONS
// ========================================================================

if (!function_exists('view_title')) {
    /**
     * Set page title
     * 
     * @param string|array $title Title
     * @param bool $append Append to existing
     * @return void
     */
    function view_title($title, $append = false)
    {
        View::setTitle($title, $append);
    }
}

if (!function_exists('view_description')) {
    /**
     * Set meta description
     * 
     * @param string $description Description
     * @return void
     */
    function view_description($description)
    {
        View::setDescription($description);
    }
}

if (!function_exists('view_keywords')) {
    /**
     * Set meta keywords
     * 
     * @param string|array $keywords Keywords
     * @return void
     */
    function view_keywords($keywords)
    {
        View::setKeywords($keywords);
    }
}

if (!function_exists('view_schema')) {
    /**
     * Add JSON-LD schema bổ sung vào Head (render trong Head::render() sau schema chính từ Schema library).
     * Dùng view_schema() khi view/plugin cần thêm schema riêng (vd: product schema từ plugin).
     *
     * @param array $schema Schema data (array, không phải JSON string)
     * @param string|null $key Unique key
     * @return void
     */
    function view_schema($schema, $key = null)
    {
        View::addSchema($schema, $key);
    }
}

if (!function_exists('view_meta')) {
    /**
     * Add meta tag
     * 
     * @param string $name Meta name or property
     * @param string $content Content
     * @param string $type Type (name, property, http-equiv)
     * @return void
     */
    function view_meta($name, $content, $type = 'name')
    {
        View::addMeta($name, $content, $type);
    }
}

if (!function_exists('view_canonical')) {
    /**
     * Set canonical URL
     * 
     * @param string $url Canonical URL
     * @return void
     */
    function view_canonical($url)
    {
        View::setCanonical($url);
    }
}

if (!function_exists('view_og')) {
    /**
     * Set Open Graph meta tag (một cặp key-value)
     *
     * @param string $key OG key (type, url, image, etc.)
     * @param string $value Value
     * @return void
     */
    function view_og($key, $value)
    {
        View::addMeta('og:' . $key, $value, 'property');
    }
}

if (!function_exists('view_og_array')) {
    /**
     * Set Open Graph meta tags từ mảng (title, description, image, url, type, site_name, locale, ...)
     *
     * @param array $og Mảng key => value (chỉ scalar)
     * @return void
     */
    function view_og_array($og)
    {
        View::setOpenGraph($og);
    }
}

if (!function_exists('view_twitter_card')) {
    /**
     * Set Twitter Card meta tags từ mảng (card, title, description, image, site, creator, ...)
     *
     * @param array $twitter Mảng key => value (chỉ scalar)
     * @return void
     */
    function view_twitter_card($twitter)
    {
        View::setTwitterCard($twitter);
    }
}

// ========================================================================
// ASSET HELPERS (wp_enqueue_style / wp_enqueue_script / wp_localize_script style)
// ========================================================================

if (!function_exists('add_css')) {
    /**
     * Đăng ký file CSS (gọi View::addCss).
     * Dùng trong theme/plugin để thêm stylesheet; output qua view_css() hoặc view_head().
     *
     * @param string $handle Handle duy nhất
     * @param string $src Đường dẫn/URL file CSS
     * @param array $deps Handles phụ thuộc
     * @param string|null $version Version cache busting
     * @param string $media all|screen|print
     * @param bool $in_footer In footer
     * @param bool $preload Preload
     * @param bool $minify Minify khi production
     * @return void
     */
    function add_css($handle, $src, $deps = [], $version = null, $media = 'all', $in_footer = false, $preload = false, $minify = false)
    {
        View::addCss($handle, $src, $deps, $version, $media, $in_footer, $preload, $minify);
    }
}

if (!function_exists('add_js')) {
    /**
     * Đăng ký file JS (gọi View::addJs).
     * Dùng trong theme/plugin; output qua view_js() hoặc view_head() / assets_footer().
     *
     * @param string $handle Handle duy nhất
     * @param string $src Đường dẫn/URL file JS
     * @param array $deps Handles phụ thuộc
     * @param string|null $version Version cache busting
     * @param bool $defer Defer
     * @param bool $async Async
     * @param bool $in_footer In footer (default true)
     * @param bool $minify Minify khi production
     * @return void
     */
    function add_js($handle, $src, $deps = [], $version = null, $defer = false, $async = false, $in_footer = true, $minify = false)
    {
        View::addJs($handle, $src, $deps, $version, $defer, $async, $in_footer, $minify);
    }
}

if (!function_exists('enqueue_script')) {
    /**
     * Đăng ký và xếp hàng script (wp_enqueue_script style).
     * Ánh xạ tới add_js() – cùng tham số, cùng hành vi.
     *
     * @param string $handle Handle duy nhất
     * @param string $src Đường dẫn/URL file JS
     * @param array $deps Handles phụ thuộc
     * @param string|null $version Version cache busting
     * @param bool $defer Defer
     * @param bool $async Async
     * @param bool $in_footer In footer (default true)
     * @param bool $minify Minify khi production
     * @return void
     */
    function enqueue_script($handle, $src, $deps = [], $version = null, $defer = false, $async = false, $in_footer = true, $minify = false)
    {
        View::addJs($handle, $src, $deps, $version, $defer, $async, $in_footer, $minify);
    }
}

if (!function_exists('enqueue_style')) {
    /**
     * Đăng ký và xếp hàng stylesheet (wp_enqueue_style style).
     * Ánh xạ tới add_css() – cùng tham số, cùng hành vi.
     *
     * @param string $handle Handle duy nhất
     * @param string $src Đường dẫn/URL file CSS
     * @param array $deps Handles phụ thuộc
     * @param string|null $version Version cache busting
     * @param string $media all|screen|print
     * @param bool $in_footer In footer
     * @param bool $preload Preload
     * @param bool $minify Minify khi production
     * @return void
     */
    function enqueue_style($handle, $src, $deps = [], $version = null, $media = 'all', $in_footer = false, $preload = false, $minify = false)
    {
        View::addCss($handle, $src, $deps, $version, $media, $in_footer, $preload, $minify);
    }
}

if (!function_exists('inline_css')) {
    /**
     * Thêm CSS nội tuyến (gọi View::inlineCss).
     *
     * @param string $handle Handle
     * @param string $css Nội dung CSS
     * @param array $deps Dependencies
     * @param string|null $version Version
     * @param bool $in_footer In footer
     * @return void
     */
    function inline_css($handle, $css, $deps = [], $version = null, $in_footer = false)
    {
        View::inlineCss($handle, $css, $deps, $version, $in_footer);
    }
}

if (!function_exists('inline_js')) {
    /**
     * Thêm JS nội tuyến (gọi View::inlineJs).
     *
     * @param string $handle Handle
     * @param string $js Nội dung JS
     * @param array $deps Dependencies
     * @param string|null $version Version
     * @param bool $in_footer In footer
     * @return void
     */
    function inline_js($handle, $js, $deps = [], $version = null, $in_footer = true)
    {
        View::inlineJs($handle, $js, $deps, $version, $in_footer);
    }
}

if (!function_exists('localize_script')) {
    /**
     * Inject biến JS từ PHP cho một script handle (wp_localize_script style).
     * Output dạng: var $objectName = {json}; trước script tương ứng.
     *
     * @param string $handle Handle script (đã đăng ký bằng add_js)
     * @param string $objectName Tên biến global JS (vd. myConfig)
     * @param array $data Mảng dữ liệu (sẽ json_encode)
     * @param bool $in_footer Cùng vị trí với script (footer/head)
     * @return void
     */
    function localize_script($handle, $objectName, $data, $in_footer = true)
    {
        View::localizeScript($handle, $objectName, $data, $in_footer);
    }
}

if (!function_exists('view_css')) {
    /**
     * In ra HTML các CSS đã đăng ký (link tags).
     * Thường dùng trong <head> hoặc gọi qua view_head() / assets_head().
     *
     * @param string $location 'head' hoặc 'footer'
     * @return string HTML
     */
    function view_css($location = 'head')
    {
        return View::css($location === 'footer' ? 'footer' : 'head');
    }
}

if (!function_exists('view_js')) {
    /**
     * In ra HTML các JS đã đăng ký (script tags).
     * Thường dùng trong footer hoặc gọi qua assets_footer().
     *
     * @param string $location 'footer' hoặc 'head'
     * @return string HTML
     */
    function view_js($location = 'footer')
    {
        return View::js($location === 'head' ? 'head' : 'footer');
    }
}

if (!function_exists('view_pagination')) {
    /**
     * Phân trang kiểu Prev / Trang hiện tại / Next — partial theme: parts/ui/pagination.php (active theme).
     *
     * @param string $base_url     URL base (vd. admin_url('posts/index'))
     * @param int    $current_page Trang hiện tại
     * @param bool   $is_next      Còn trang sau
     * @param array  $query_params Tham số GET giữ lại (trừ page)
     * @param array  $custom_names Tên biến page trong query (mặc định ['page' => 'page'])
     * @return string HTML
     */
    function view_pagination($base_url, $current_page, $is_next, $query_params = [], $custom_names = [])
    {
        $default_names = [
            'page' => 'page',
        ];
        $custom_names = array_merge($default_names, $custom_names);
        $query_string = http_build_query($query_params);

        $prev_page_url = $current_page > 2
            ? $base_url . '?' . $custom_names['page'] . '=' . ($current_page - 1) . '&' . $query_string
            : ($query_string ? $base_url . '?' . $query_string : $base_url);
        $next_page_url = $base_url . '?' . $custom_names['page'] . '=' . ($current_page + 1) . '&' . $query_string;
        $prev_page_url = rtrim($prev_page_url, '&');
        $next_page_url = rtrim($next_page_url, '&');

        return View::include('parts/ui/pagination', [
            'base_url' => $base_url,
            'current_page' => (int) $current_page,
            'is_next' => (bool) $is_next,
            'prev_page_url' => $prev_page_url,
            'next_page_url' => $next_page_url,
            'custom_names' => $custom_names,
            'query_params' => $query_string,
        ]);
    }
}

