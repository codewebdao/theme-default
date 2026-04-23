<?php

namespace System\Libraries\Render\Asset;

use System\Libraries\Render\Minify;
use System\Libraries\Render\Theme\ThemeContext;

/**
 * Asset Manager
 * 
 * Manages CSS and JS assets with:
 * - Dependency resolution
 * - Priority ordering
 * - Deferred/async loading
 * - Inline assets
 * - Area buckets `web` / `admin` (alias đầu vào: frontend→web, backend→admin); bucket lấy từ ThemeContext::getScope(), không dùng View::namespace
 * 
 * Inspired by WordPress wp_enqueue_* functions
 * 
 * @package System\Libraries\Render\Asset
 * @since 1.0.0
 */
class AssetManager
{
    /**
     * Registered styles
     * Structure: $styles[area][location][handle] = [...]
     * @var array
     */
    private static $styles = [];

    /**
     * Registered scripts
     * Structure: $scripts[area][location][handle] = [...]
     * @var array
     */
    private static $scripts = [];

    /**
     * Inline styles
     * Structure: $inlineStyles[area][location][handle] = css
     * @var array
     */
    private static $inlineStyles = [];

    /**
     * Inline scripts
     * Structure: $inlineScripts[area][location][handle] = js
     * @var array
     */
    private static $inlineScripts = [];

    /**
     * Localize data for scripts (inject JS variables from PHP, wp_localize_script style).
     * Structure: $localizeScripts[area][location][handle] = ['object_name' => string, 'data' => array]
     * @var array
     */
    private static $localizeScripts = [];

    /**
     * Cached asset_build_version per request (avoid repeated get_option in computeSignature)
     * @var int|null
     */
    private static $cachedAssetBuildVersion = null;

    /**
     * Add CSS file
     * 
     * @param string $handle Unique handle
     * @param string $src Source URL or path
     * @param array $deps Dependencies (handles)
     * @param string|null $version Version for cache busting
     * @param string $media Media type (all, screen, print)
     * @param bool $in_footer Render in footer instead of head - default false
     * @param bool $preload Preload CSS for better performance - default false
     * @param bool $minify Minify this asset in production mode - default false
     * @return void
     */
    public static function addCss($handle, $src, $deps = [], $version = null, $media = 'all', $in_footer = false, $preload = false, $minify = false)
    {
        $area = self::normalizeAreaAlias(self::detectArea());
        $location = $in_footer ? 'footer' : 'head';
        
        if (!isset(self::$styles[$area])) {
            self::$styles[$area] = [];
        }
        if (!isset(self::$styles[$area][$location])) {
            self::$styles[$area][$location] = [];
        }
        
        self::$styles[$area][$location][$handle] = [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'version' => $version,
            'media' => $media,
            'preload' => $preload,
            'minify' => $minify,
            'loaded' => false,
        ];
    }

    /**
     * Add JS file
     * 
     * @param string $handle Unique handle
     * @param string $src Source URL or path
     * @param array $deps Dependencies (handles)
     * @param string|null $version Version for cache busting
     * @param bool $defer Defer loading
     * @param bool $async Async loading
     * @param bool $in_footer Render in footer instead of head - default true
     * @param bool $minify Minify this asset in production mode - default false
     * @return void
     */
    public static function addJs($handle, $src, $deps = [], $version = null, $defer = false, $async = false, $in_footer = true, $minify = false)
    {
        $area = self::normalizeAreaAlias(self::detectArea());
        $location = $in_footer ? 'footer' : 'head';
        
        if (!isset(self::$scripts[$area])) {
            self::$scripts[$area] = [];
        }
        if (!isset(self::$scripts[$area][$location])) {
            self::$scripts[$area][$location] = [];
        }
        
        self::$scripts[$area][$location][$handle] = [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'version' => $version,
            'defer' => $defer,
            'async' => $async,
            'minify' => $minify,
            'loaded' => false,
        ];
    }

    /**
     * Add inline CSS
     * 
     * @param string $handle Unique handle
     * @param string $css CSS code
     * @param array $deps Dependencies (handles) - for consistency with addCss
     * @param string|null $version Version - for consistency with addCss
     * @param bool $in_footer Render in footer instead of head - default false
     * @return void
     */
    public static function inlineCss($handle, $css, $deps = [], $version = null, $in_footer = false)
    {
        $area = self::normalizeAreaAlias(self::detectArea());
        $location = $in_footer ? 'footer' : 'head';
        
        if (!isset(self::$inlineStyles[$area])) {
            self::$inlineStyles[$area] = [];
        }
        if (!isset(self::$inlineStyles[$area][$location])) {
            self::$inlineStyles[$area][$location] = [];
        }
        
        self::$inlineStyles[$area][$location][$handle] = is_string($css) ? $css : (string) $css;
    }

    /**
     * Add inline JS
     * 
     * @param string $handle Unique handle
     * @param string $js JavaScript code
     * @param array $deps Dependencies (handles) - for consistency with addJs
     * @param string|null $version Version - for consistency with addJs
     * @param bool $in_footer Render in footer instead of head - default true
     * @return void
     */
    public static function inlineJs($handle, $js, $deps = [], $version = null, $in_footer = true)
    {
        $area = self::normalizeAreaAlias(self::detectArea());
        $location = $in_footer ? 'footer' : 'head';
        
        if (!isset(self::$inlineScripts[$area])) {
            self::$inlineScripts[$area] = [];
        }
        if (!isset(self::$inlineScripts[$area][$location])) {
            self::$inlineScripts[$area][$location] = [];
        }
        
        self::$inlineScripts[$area][$location][$handle] = is_string($js) ? $js : (string) $js;
    }

    /**
     * Localize script: inject a JS object (e.g. ajax_url, nonce) for a given handle.
     * Output is an inline script "var {objectName} = {json};" before the script tag(s).
     *
     * @param string $handle Script handle (must be registered with addJs)
     * @param string $objectName Global JS variable name (e.g. 'myPluginConfig')
     * @param array $data Data to pass (will be json_encode'd; only scalar/array values)
     * @param bool $in_footer Same as script location - default true
     * @return void
     */
    public static function localizeScript($handle, $objectName, $data, $in_footer = true)
    {
        $area = self::normalizeAreaAlias(self::detectArea());
        $location = $in_footer ? 'footer' : 'head';
        if (!isset(self::$localizeScripts[$area])) {
            self::$localizeScripts[$area] = [];
        }
        if (!isset(self::$localizeScripts[$area][$location])) {
            self::$localizeScripts[$area][$location] = [];
        }
        self::$localizeScripts[$area][$location][$handle] = [
            'object_name' => $objectName,
            'data' => $data,
        ];
    }

    /**
     * Render CSS HTML
     * 
     * @param string $location Render footer styles at footer or head - default 'head'
     * @return string HTML
     */
    public static function css($location = 'head')
    {
        $location = $location == 'head' ? 'head' : 'footer';
        $html = '';

        $activeArea = self::normalizeAreaAlias(self::detectArea());
        $stylesSubset = [];
        if (isset(self::$styles[$activeArea])) {
            $stylesSubset[$activeArea] = self::$styles[$activeArea];
        }

        foreach ($stylesSubset as $area => $areaStyles) {
            // Render enqueued styles for this location and area
            if (isset($areaStyles[$location]) && !empty($areaStyles[$location])) {
                // Resolve dependencies and sort (fallback to registration order on failure)
                $sorted = self::sortByDependenciesSafe($areaStyles[$location], 'style');
                
                // Apply filter BEFORE render (allow plugins to modify array)
                if (function_exists('apply_filters')) {
                    $sorted = apply_filters('render.css.before', $sorted, $area, $location);
                }
                
                // Separate preload and regular styles
                $preloadStyles = [];
                $regularStyles = [];
                
                foreach ($sorted as $style) {
                    if (!empty($style['preload'])) {
                        $preloadStyles[] = $style;
                    } else {
                        $regularStyles[] = $style;
                    }
                }
                
                $minifyCssOpt = get_option('minify_css');
                $optMinifyCss = $minifyCssOpt && $minifyCssOpt !== '0' && $minifyCssOpt !== false;
                
                // Render preload links first (for better performance)
                foreach ($preloadStyles as $style) {
                    $useMinify = ($style['minify'] ?? false) || $optMinifyCss;
                    $url = self::buildAssetUrl($style['src'], 'css', $area, $useMinify);
                    $version = $style['version'] ? '?ver=' . htmlspecialchars($style['version'], ENT_QUOTES | ENT_XML1, 'UTF-8') : '';
                    $urlEscaped = htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $html .= "<link rel=\"preload\" as=\"style\" href=\"{$urlEscaped}{$version}\" />\n";
                }

                $combineCss = get_option('combine_css');
                $optCombineCss = $combineCss && $combineCss !== '0' && $combineCss !== false;
                $asyncCss = get_option('async_css');
                $optAsyncCss = $asyncCss && $asyncCss !== '0' && $asyncCss !== false;
                $builtCssUrl = self::getBuiltAssetUrl($area, $location, 'css', $sorted);
                $builtCssUrls = is_array($builtCssUrl) ? $builtCssUrl : ($builtCssUrl !== '' ? [$builtCssUrl] : []);
                if (!empty($builtCssUrls)) {
                    foreach ($builtCssUrls as $url) {
                        if ($url === '') continue;
                        $urlEscaped = htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        if ($optAsyncCss) {
                            $html .= "<link rel=\"preload\" href=\"{$urlEscaped}\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\" />\n";
                            $html .= "<noscript><link rel=\"stylesheet\" href=\"{$urlEscaped}\" media=\"all\" /></noscript>\n";
                        } else {
                            $html .= "<link rel=\"stylesheet\" href=\"{$urlEscaped}\" media=\"all\" />\n";
                        }
                    }
                } else {
                    // Chưa có bản build: ghi cache miss nếu đang bật minify hoặc combine để cron build; output raw
                    if ($optMinifyCss || $optCombineCss) {
                        self::recordCacheMiss($area, $location, 'css', $sorted);
                    }
                    foreach ($regularStyles as $style) {
                        $useMinify = ($style['minify'] ?? false) || $optMinifyCss;
                        $url = self::buildAssetUrl($style['src'], 'css', $area, $useMinify);
                        $version = $style['version'] ? '?ver=' . htmlspecialchars($style['version'], ENT_QUOTES | ENT_XML1, 'UTF-8') : '';
                        $urlEscaped = htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $mediaEscaped = htmlspecialchars($style['media'], ENT_QUOTES | ENT_XML1, 'UTF-8');
                        if ($optAsyncCss) {
                            $html .= "<link rel=\"preload\" href=\"{$urlEscaped}{$version}\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\" />\n";
                            $html .= "<noscript><link rel=\"stylesheet\" href=\"{$urlEscaped}{$version}\" media=\"{$mediaEscaped}\" /></noscript>\n";
                        } else {
                            $html .= "<link rel=\"stylesheet\" href=\"{$urlEscaped}{$version}\" media=\"{$mediaEscaped}\" />\n";
                        }
                    }
                }

                // Render preload stylesheets (after preload link)
                foreach ($preloadStyles as $style) {
                    $useMinify = ($style['minify'] ?? false) || $optMinifyCss;
                    $url = self::buildAssetUrl($style['src'], 'css', $area, $useMinify);
                    $version = $style['version'] ? '?ver=' . htmlspecialchars($style['version'], ENT_QUOTES | ENT_XML1, 'UTF-8') : '';
                    $urlEscaped = htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $mediaEscaped = htmlspecialchars($style['media'], ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $html .= "<link rel=\"stylesheet\" href=\"{$urlEscaped}{$version}\" media=\"{$mediaEscaped}\" />\n";
                }
            }
            
            // Get inline styles for this area and location
            $inlineStyles = isset(self::$inlineStyles[$area][$location]) ? self::$inlineStyles[$area][$location] : [];
            
            // Apply filter for inline styles BEFORE render
            if (function_exists('apply_filters') && !empty($inlineStyles)) {
                $inlineStyles = apply_filters('render.css.inline.before', $inlineStyles, $area, $location);
            }
            
            // Render inline styles for this location (option minify_css; limit ≤10KB to avoid CPU spike)
            if (!empty($inlineStyles)) {
                $minifyCssOpt = get_option('minify_css');
                $minifyCss = $minifyCssOpt && $minifyCssOpt !== '0' && $minifyCssOpt !== false;
                $inlineLimit = 10 * 1024;
                $html .= "\n<style type=\"text/css\">\n";
                foreach ($inlineStyles as $handle => $css) {
                    $css = is_string($css) ? $css : (string) $css;
                    $handleEscaped = htmlspecialchars($handle, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $html .= "/* {$handleEscaped} */\n";
                    $html .= ($minifyCss && strlen($css) <= $inlineLimit) ? Minify::css($css) : $css;
                    $html .= "\n";
                }
                $html .= "</style>\n";
            }
        }
        
        // Apply filter AFTER render (allow plugins/themes to modify HTML output)
        if (function_exists('apply_filters')) {
            $html = apply_filters('render.css.after', $html, null, $location);
        }
        
        return $html;
    }

    /**
     * Render JS HTML
     * 
     * @param string $location Render footer scripts at footer or head - default 'footer'
     * @return string HTML
     */
    public static function js($location = 'footer')
    {
        $location = $location == 'footer' ? 'footer' : 'head';
        $html = '';

        $activeArea = self::normalizeAreaAlias(self::detectArea());
        $scriptsSubset = [];
        if (isset(self::$scripts[$activeArea])) {
            $scriptsSubset[$activeArea] = self::$scripts[$activeArea];
        }

        foreach ($scriptsSubset as $area => $areaScripts) {
            // Render enqueued scripts for this location and area
            if (isset($areaScripts[$location]) && !empty($areaScripts[$location])) {
                // Resolve dependencies and sort (fallback to registration order on failure)
                $sorted = self::sortByDependenciesSafe($areaScripts[$location], 'script');
                
                // Apply filter BEFORE render (allow plugins to modify array)
                if (function_exists('apply_filters')) {
                    $sorted = apply_filters('render.js.before', $sorted, $area, $location);
                }
                
                $deferJs = get_option('defer_js');
                $optDeferJs = $deferJs && $deferJs !== '0' && $deferJs !== false;
                $combineJs = get_option('combine_js');
                $optCombineJs = $combineJs && $combineJs !== '0' && $combineJs !== false;
                $minifyJsOpt = get_option('minify_js');
                $optMinifyJs = $minifyJsOpt && $minifyJsOpt !== '0' && $minifyJsOpt !== false;

                $builtJsUrl = self::getBuiltAssetUrl($area, $location, 'js', $sorted);
                $builtJsUrls = is_array($builtJsUrl) ? $builtJsUrl : ($builtJsUrl !== '' ? [$builtJsUrl] : []);
                $localizeForArea = isset(self::$localizeScripts[$area][$location]) ? self::$localizeScripts[$area][$location] : [];
                if (!empty($builtJsUrls)) {
                    $html .= self::renderLocalizeBlock($localizeForArea, array_column($sorted, 'handle'));
                    $defer = $optDeferJs ? ' defer' : '';
                    foreach ($builtJsUrls as $url) {
                        if ($url === '') continue;
                        $html .= "<script type=\"text/javascript\" src=\"" . htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8') . "\"{$defer}></script>\n";
                    }
                } else {
                    if ($optMinifyJs || $optCombineJs) {
                        self::recordCacheMiss($area, $location, 'js', $sorted);
                    }
                    foreach ($sorted as $script) {
                        $h = $script['handle'] ?? '';
                        if ($h !== '' && isset($localizeForArea[$h])) {
                            $html .= self::renderLocalizeBlock([$h => $localizeForArea[$h]], [$h]);
                        }
                        $useMinify = ($script['minify'] ?? false) || $optMinifyJs;
                        $url = self::buildAssetUrl($script['src'], 'js', $area, $useMinify);
                        $version = $script['version'] ? '?ver=' . htmlspecialchars($script['version'], ENT_QUOTES | ENT_XML1, 'UTF-8') : '';
                        $urlEscaped = htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $defer = ($script['defer'] || ($optDeferJs && !$script['async'])) ? ' defer' : '';
                        $async = $script['async'] ? ' async' : '';
                        $html .= "<script type=\"text/javascript\" src=\"{$urlEscaped}{$version}\"{$defer}{$async}></script>\n";
                    }
                }
            }
            
            // Get inline scripts for this area and location
            $inlineScripts = isset(self::$inlineScripts[$area][$location]) ? self::$inlineScripts[$area][$location] : [];
            
            // Apply filter for inline scripts BEFORE render
            if (function_exists('apply_filters') && !empty($inlineScripts)) {
                $inlineScripts = apply_filters('render.js.inline.before', $inlineScripts, $area, $location);
            }
            
            // Render inline scripts for this location (option minify_js; limit ≤10KB)
            if (!empty($inlineScripts)) {
                $minifyJsOpt = get_option('minify_js');
                $minifyJs = $minifyJsOpt && $minifyJsOpt !== '0' && $minifyJsOpt !== false;
                $inlineLimit = 10 * 1024;
                $html .= "\n<script type=\"text/javascript\">\n";
                foreach ($inlineScripts as $handle => $js) {
                    $js = is_string($js) ? $js : (string) $js;
                    $handleEscaped = htmlspecialchars($handle, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $html .= "/* {$handleEscaped} */\n";
                    $html .= ($minifyJs && strlen($js) <= $inlineLimit) ? Minify::js($js) : $js;
                    $html .= "\n";
                }
                $html .= "</script>\n";
            }
        }
        
        // Apply filter AFTER render (allow plugins/themes to modify HTML output)
        if (function_exists('apply_filters')) {
            $html = apply_filters('render.js.after', $html, null, $location);
        }
        
        return $html;
    }

    /**
     * Render one inline script block with localized variables (var name = {...};).
     *
     * @param array $localizeMap [handle => ['object_name' => string, 'data' => array]]
     * @param array $handles Order of handles to output (only these are rendered)
     * @return string HTML script block or empty string
     */
    private static function renderLocalizeBlock(array $localizeMap, array $handles)
    {
        $parts = [];
        foreach ($handles as $h) {
            if (!isset($localizeMap[$h]) || !is_array($localizeMap[$h])) {
                continue;
            }
            $name = $localizeMap[$h]['object_name'] ?? '';
            $data = $localizeMap[$h]['data'] ?? [];
            if ($name === '' || !is_string($name)) {
                continue;
            }
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
                continue;
            }
            $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                continue;
            }
            $parts[] = 'var ' . $name . ' = ' . $json . ';';
        }
        if (empty($parts)) {
            return '';
        }
        return "<script type=\"text/javascript\">\n" . implode("\n", $parts) . "\n</script>\n";
    }

    /**
     * Sort by dependencies with fallback to registration order on failure.
     * Avoids breaking render when circular deps or missing handles occur.
     */
    private static function sortByDependenciesSafe(array $assets, string $type = 'asset'): array
    {
        try {
            $sorted = self::sortByDependencies($assets, $type);
            if (count($sorted) !== count($assets)) {
                if (class_exists(\System\Libraries\Logger::class)) {
                    \System\Libraries\Logger::warning("AssetManager: dependency resolution incomplete for {$type}, using registration order");
                }
                return array_values($assets);
            }
            return $sorted;
        } catch (\Throwable $e) {
            if (class_exists(\System\Libraries\Logger::class)) {
                \System\Libraries\Logger::warning('AssetManager: dependency resolution failed — ' . $e->getMessage() . ', using registration order');
            }
            return array_values($assets);
        }
    }

    /**
     * Sort assets by dependencies (topological sort)
     * 
     * @param array $assets Assets array
     * @param string $type Type (style or script) for error messages
     * @return array Sorted assets
     */
    private static function sortByDependencies($assets, $type = 'asset')
    {
        $sorted = [];
        $visited = [];
        $visiting = [];

        foreach ($assets as $handle => $asset) {
            self::visitAsset($handle, $assets, $sorted, $visited, $visiting, $type);
        }

        return $sorted;
    }

    /**
     * Visit asset for topological sort (DFS)
     * 
     * ✅ OPTIMIZED: Prevents duplicate loading using $visited array
     * - If asset already in $visited, skip (already added to $sorted)
     * - Dependencies are visited first, then asset itself
     * - Each handle is only added once to $sorted array
     * 
     * @param string $handle Asset handle
     * @param array $assets All assets
     * @param array &$sorted Sorted result
     * @param array &$visited Visited nodes (prevents duplicates)
     * @param array &$visiting Currently visiting (for circular detection)
     * @param string $type Type for error messages
     * @return void
     */
    private static function visitAsset($handle, $assets, &$sorted, &$visited, &$visiting, $type)
    {
        // ✅ Already processed - skip to prevent duplicate loading
        if (isset($visited[$handle])) {
            return;
        }

        // Circular dependency detection
        if (isset($visiting[$handle])) {
            \System\Libraries\Logger::error("Asset Manager: Circular dependency detected for {$type} '{$handle}'");
            return;
        }

        // Mark as visiting (for circular detection)
        $visiting[$handle] = true;

        // ✅ Visit dependencies FIRST (ensures correct load order)
        if (isset($assets[$handle]['deps']) && !empty($assets[$handle]['deps'])) {
            foreach ($assets[$handle]['deps'] as $dep) {
                if (isset($assets[$dep])) {
                    // Recursively visit dependency (will skip if already visited)
                    self::visitAsset($dep, $assets, $sorted, $visited, $visiting, $type);
                } else {
                    \System\Libraries\Logger::error("Asset Manager: Dependency '{$dep}' not found for {$type} '{$handle}'");
                }
            }
        }

        // ✅ Add to sorted list (only once, after dependencies)
        $sorted[] = $assets[$handle];
        $visited[$handle] = true;  // Mark as visited to prevent duplicate
        unset($visiting[$handle]);
    }

    /**
     * Build asset URL
     * 
     * ✅ DEVELOPMENT MODE: Use original source file
     * ✅ PRODUCTION MODE: Auto-use .min version if minify=true (NO file_exists check for performance)
     * 
     * ⚡ OPTIMIZED: No file_exists() check - auto-use .min in production
     * If .min file doesn't exist, browser will 404 and admin can fix
     * 
     * @param string $src Source (URL or relative path)
     * @param string $type Type (css or js)
     * @param string $area Bucket `web` hoặc `admin` (ThemeContext::getTheme)
     * @param bool $minify Whether to minify in production
     * @return string Full URL
     */
    private static function buildAssetUrl($src, $type, $area, $minify = false)
    {
        $src = trim((string) $src);
        if ($src === '') {
            return '/';
        }

        // If absolute URL (http://, https://, //), return as is
        if (preg_match('#^(https?:)?//#i', $src)) {
            return $src;
        }

        // If starts with /, treat as absolute path from public
        if ($src[0] === '/') {
            return $src;
        }

        // ✅ PRODUCTION MODE + MINIFY: Auto-use .min version (NO file_exists check)
        if (!APP_DEVELOPMENT && $minify) {
            $basePath = pathinfo($src, PATHINFO_DIRNAME);
            $filename = pathinfo($src, PATHINFO_FILENAME);
            $extension = pathinfo($src, PATHINFO_EXTENSION);
            
            // Build minified path
            $minPath = ($basePath ? $basePath . '/' : '') . $filename . '.min.' . $extension;
            
            // Auto-use .min version (no file_exists check for performance)
            // If file doesn't exist, browser will 404 and admin can fix
            $themeName = ThemeContext::getTheme($area);
            if ($themeName !== '') {
                return public_url('content/themes/' . $themeName . '/assets/' . $minPath);
            }
        }

        // Theme assets
        $themeName = ThemeContext::getTheme($area);
        if ($themeName !== '') {
            return public_url('content/themes/' . $themeName . '/assets/' . $src);
        }

        return '/' . $src;
    }

    /**
     * Compute signature hash from sorted assets (assetsId for option lookup)
     *
     * @param string $area
     * @param string $location
     * @param string $type css|js
     * @param array $sortedAssets
     * @return string|null [key, hash] or null if can't compute
     */
    private static function computeSignature($area, $location, $type, array $sortedAssets)
    {
        if (self::$cachedAssetBuildVersion === null) {
            $v = (int) (function_exists('get_option') ? get_option('asset_build_version') : null) ?: 1;
            self::$cachedAssetBuildVersion = $v > 0 ? $v : 1;
        }
        $assetBuildVersion = self::$cachedAssetBuildVersion;
        $normArea = self::normalizeAreaAlias($area);
        $themeName = ThemeContext::getTheme($normArea);
        if ($themeName === '') {
            $themeName = defined('APP_THEME_NAME') ? APP_THEME_NAME : 'default';
        }
        $themeVersion = defined('APP_THEME_VER') ? APP_THEME_VER : '1';

        $normalized = [];
        foreach ($sortedAssets as $a) {
            $normalized[] = [
                'src' => $a['src'] ?? '',
                'media' => ($type === 'css') ? ($a['media'] ?? 'all') : null,
                'type' => self::isExternalUrl($a['src'] ?? '') ? 'external' : 'local',
            ];
        }
        $combineOpt = ($type === 'css') ? (get_option('combine_css') && get_option('combine_css') !== '0' && get_option('combine_css') !== false) : (get_option('combine_js') && get_option('combine_js') !== '0' && get_option('combine_js') !== false);
        $input = [$themeName, $themeVersion, $area, $location, $normalized, $assetBuildVersion, $combineOpt];
        $json = class_exists(\App\Services\Asset\AssetsService::class) ? \App\Services\Asset\AssetsService::stableJsonEncode($input) : json_encode($input);
        $hash = substr(md5($json), 0, 12);
        $key = $type . ':' . $area . ':' . $location . ':' . $hash;
        return ['key' => $key, 'hash' => $hash];
    }

    /**
     * Record cache miss: set_option entry chỉ khi chưa có (option-only).
     */
    private static function recordCacheMiss($area, $location, $type, array $sortedAssets): void
    {
        if (!class_exists(\App\Services\Asset\AssetsService::class)) {
            return;
        }
        if (empty($sortedAssets)) {
            return;
        }
        $sig = self::computeSignature($area, $location, $type, $sortedAssets);
        if (!$sig) {
            return;
        }
        $hash = $sig['hash'];
        $lastSeen = time();
        $optionKey = \App\Services\Asset\AssetsService::getOptionKey($type, $location);
        $entries = \App\Services\Asset\AssetsService::getOptionEntries($optionKey);
        $exists = false;
        foreach ($entries as $e) {
            if (($e['id'] ?? '') === $hash) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $normalizedData = array_map(function ($a) use ($type) {
                $isExternal = self::isExternalUrl($a['src'] ?? '');
                $item = ['src' => $a['src'] ?? '', 'type' => $isExternal ? 'external' : 'local'];
                if ($type === 'css') {
                    $item['media'] = $a['media'] ?? 'all';
                }
                return $item;
            }, $sortedAssets);
            if (\App\Services\Asset\AssetsService::wouldProduceEmptyBuild($normalizedData)) {
                return;
            }
            \App\Services\Asset\AssetsService::setOptionEntry($optionKey, [
                'id' => $hash,
                'area' => $area,
                'data' => $normalizedData,
                'build' => null,
                'last_seen' => $lastSeen,
            ]);
        }
    }

    private static function isExternalUrl(string $src): bool
    {
        return preg_match('#^(https?:)?//#i', $src) === 1;
    }

    /**
     * URL(s) tới file build: option-only. Return string (1 file) hoặc array of strings (nhiều file khi không combine).
     */
    private static function getBuiltAssetUrl($area, $location, $type, array $sortedAssets = [])
    {
        if (!class_exists(\App\Services\Asset\AssetsService::class)) {
            return '';
        }
        $sig = self::computeSignature($area, $location, $type, $sortedAssets);
        if (!$sig) {
            return '';
        }
        $assetsId = $sig['hash'];
        $optionKey = \App\Services\Asset\AssetsService::getOptionKey($type, $location);
        $build = \App\Services\Asset\AssetsService::getBuildFromOption($optionKey, $assetsId);
        if ($build !== null) {
            if (!empty($build['files']) && is_array($build['files'])) {
                $urls = [];
                foreach ($build['files'] as $f) {
                    if (!empty($f['url']) && is_string($f['url'])) {
                        $urls[] = $f['url'];
                    } elseif (!empty($f['path'])) {
                        $path = str_replace(['\\', '..'], ['/', ''], trim($f['path'], '/'));
                        if ($path !== '') {
                            $urls[] = public_url('content/assets/' . $path);
                        }
                    }
                }
                return $urls;
            }
            if (!empty($build['url'])) {
                return is_string($build['url']) ? $build['url'] : '';
            }
            if (!empty($build['path'])) {
                $path = str_replace(['\\', '..'], ['/', ''], trim($build['path'], '/'));
                return $path !== '' ? public_url('content/assets/' . $path) : '';
            }
        }
        return '';
    }

    /**
     * Bucket asset hiện tại: chỉ theo ThemeContext (web|admin).
     * View::namespace() chỉ phục vụ resolve đường dẫn template (prefix theme / @plugin), không ảnh hưởng enqueue.
     *
     * @return string
     */
    private static function detectArea()
    {
        return self::normalizeAreaAlias(ThemeContext::getScope());
    }

    /**
     * Clear all assets for an area
     * 
     * @param string|null $area Area to clear (null = all)
     * @return void
     */
    public static function clearAssets($area = null)
    {
        if ($area === null) {
            self::$styles = [];
            self::$scripts = [];
            self::$inlineStyles = [];
            self::$inlineScripts = [];
            self::$localizeScripts = [];
            self::$cachedAssetBuildVersion = null;
        } else {
            $area = self::normalizeAreaAlias($area);
            unset(self::$styles[$area]);
            unset(self::$scripts[$area]);
            unset(self::$inlineStyles[$area]);
            unset(self::$inlineScripts[$area]);
            unset(self::$localizeScripts[$area]);
        }
    }

    /**
     * Normalize legacy area aliases to scope names.
     */
    private static function normalizeAreaAlias($area)
    {
        $area = strtolower(trim((string) $area));
        if ($area === 'frontend') {
            return 'web';
        }
        if ($area === 'backend') {
            return 'admin';
        }
        if ($area === '') {
            return 'web';
        }
        return $area;
    }
}

