<?php

namespace System\Libraries\Render;

use System\Libraries\Render\Theme\ThemeContext;
use System\Libraries\Render\Theme\ThemeConfigLoader;

/**
 * View Class - Modern Rendering API
 * 
 * Simple, explicit, no magic path resolution.
 * 
 * Path formats:
 * - Theme: `parts/home/banner` → `content/themes/{theme}/parts/home/banner.php` (root theme, không còn cặp thư mục Frontend/Backend).
 * - Plugin: `@PluginName/path` → `content/plugins/PluginName/Views/path.php` (hoặc override trong theme: `themes/{theme}/plugins/...`).
 *
 * `View::namespace()` đặt prefix resolve theme (`web` | `admin` | chuỗi con như `common/auth`) hoặc namespace plugin (`@Ecommerce:Frontend` = thư mục con **trong plugin** `Views/Frontend/`, không liên quan thư mục theme cũ).
 *
 * Features:
 * - Explicit path resolution (no auto-add, no magic)
 * - Template inheritance (current theme → parent theme)
 * - Fluent interface
 * - Debugbar auto-injection
 * 
 * @example
 * // Theme: scope web + file dưới root theme
 * View::scope('web');
 * View::make('parts/home/banner', $data);
 *
 * // Theme: prefix thư mục con (vd. common/auth/login)
 * View::namespace('common/auth');
 * View::make('login', $data);
 *
 * // Plugin: @ trong tên template (hoặc View::namespace('@Mailer') rồi make tương đối)
 * View::make('@Ecommerce/Frontend/products/index', $data);
 * View::make('@Mailer/emails/welcome', $data);
 *
 * @version 2.0.0
 * @package System\Libraries\Render
 */
class View
{
    /**
     * Namespace / prefix cho PhpTemplate::locate():
     *   - `web` | `admin` — bucket scope (chọn theme đúng qua ThemeContext; chuỗi `frontend`/`backend` được quy về web/admin).
     *   - Chuỗi khác (vd. `common/auth`) — nối trước đường template: `…/theme/common/auth/{template}.php`.
     *   - `@Plugin` hoặc `@Plugin:Khu` — resolve dưới plugin (Khu = thư mục con trong `Views/`, vd. `Frontend` cho view shop).
     * @var string|null
     */
    private static $namespace = null;

    /**
     * Template engine instance
     * @var object|null
     */
    private static $engine = null;

    /**
     * Cached debugbar check (avoid repeated defined() calls)
     * @var bool|null
     */
    private static $cachedDebugbar = null;

    /**
     * Cache file_exists() trong request (dùng cho renderThemeErrorPage)
     * @var array
     */
    private static $fileExistsCache = [];

    /**
     * View đã render (debugbar) — không phụ thuộc class Render cũ.
     *
     * @var array<int, array<string, mixed>>
     */
    private static $trackedViews = [];

    /**
     * Data for fluent interface
     * @var array
     */
    private $data = [];

    /**
     * Template path for fluent interface
     * @var string
     */
    private $template;

    /**
     * Make a new view instance
     * 
     * @param string $view Đường template (vd. parts/home/banner, @Ecommerce/Frontend/shop/index)
     * @param array $data Data to pass to template
     * @return self
     */
    public static function make($view, $data = [])
    {
        if (!is_string($view)) {
            throw new \InvalidArgumentException('View template must be a string.');
        }
        $instance = new self();
        $instance->template = $view;
        $instance->data = is_array($data) ? $data : [];
        return $instance;
    }

    /**
     * Add data to the view (fluent interface)
     * 
     * @param string|array $key Key or array of key-value pairs
     * @param mixed $value Value (if key is string)
     * @return self
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        
        return $this;
    }

    /**
     * Minify HTML output (delegate to Minify library).
     * Gọi từ controller sau khi render; dùng option minify_html.
     *
     * @param string $html HTML đầy đủ
     * @return string HTML đã minify (hoặc nguyên bản nếu lỗi / option tắt)
     */
    public static function minify($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }
        return Minify::html($html);
    }

    /**
     * Render the view and return HTML
     * 
     * @return string
     */
    public function render()
    {
        try {
            $engine = self::getEngine();
            $data = array_merge(self::$sharedData, $this->data);
            $isDebugbar = self::isDebugbar();
            $startTime = $isDebugbar ? microtime(true) : null;

            $html = $engine->render($this->template, $data);

            if ($isDebugbar && $startTime !== null) {
                $duration = (microtime(true) - $startTime) * 1000;
                $fullPath = ($engine instanceof \System\Libraries\Render\Template\PhpTemplate) ? $engine->lastRenderPath : null;
                self::trackView('view', $this->template, $fullPath, $data, $duration);
            }
            if ($isDebugbar) {
                $html = self::injectDebugbar($html);
            }
            return $html;
        } catch (\Throwable $e) { // ✅ FIX: Catch Throwable (not just Exception)
            self::handleError($e, $this->template);
            return '';
        }
    }

    /**
     * Inject debugbar before </body> tag (giống Laravel Debugbar)
     * 
     * @param string $html HTML content
     * @return string HTML with debugbar injected
     */
    private static function injectDebugbar($html)
    {
        // Only inject if:
        // 1. Has </body> tag
        // 2. Not an API request
        // 3. Debugbar enabled
        
        if (stripos($html, '</body>') === false) {
            return $html;
        }
        
        // Check if API request (skip debugbar for API)
        $reqUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if ($reqUri !== '' && strpos($reqUri, '/api/') !== false) {
            return $html;
        }
        
        try {
            $debugBarHtml = self::renderDebugbarHtml();
            if ($debugBarHtml !== '') {
                $html = str_replace('</body>', $debugBarHtml . '</body>', $html);
            }
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Debugbar injection failed: ' . $e->getMessage());
        }

        return $html;
    }

    /**
     * Require một file PHP (đường dẫn tuyệt đối) trong output buffer, trả về HTML/string.
     * Dùng cho partial theme không đi qua engine View (debugbar, snippet tĩnh, …).
     * Chỉ chấp nhận file nằm trong PATH_ROOT (an toàn khi path do cấu hình/theme quyết định).
     *
     * @param string $path Đường dẫn tuyệt đối tới file .php
     * @return string Nội dung sau khi require, hoặc rỗng nếu không hợp lệ / lỗi
     */
    public static function renderPhpFile($path)
    {
        if (!is_string($path) || $path === '') {
            return '';
        }
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }
        $real = realpath($path);
        if ($real === false) {
            return '';
        }
        $ext = pathinfo($real, PATHINFO_EXTENSION);
        if (strcasecmp((string) $ext, 'php') !== 0) {
            return '';
        }
        if (defined('PATH_ROOT')) {
            $root = realpath(PATH_ROOT);
            if ($root !== false && strpos($real, $root) !== 0) {
                return '';
            }
        }

        ob_start();
        try {
            require $real;
        } catch (\Throwable $e) {
            ob_end_clean();
            \System\Libraries\Logger::error('renderPhpFile failed (' . $real . '): ' . $e->getMessage());

            return '';
        }

        return (string) ob_get_clean();
    }

    /**
     * HTML debugbar: parts/ui/debugbar.php trong theme đang active (đồng bộ web + admin).
     */
    public static function renderDebugbarHtml()
    {
        if (!defined('APP_DEBUGBAR') || !APP_DEBUGBAR) {
            return '';
        }
        if (!defined('APP_THEME_PATH') || APP_THEME_PATH === '' || !is_dir(APP_THEME_PATH)) {
            return '';
        }
        $base = rtrim(APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR;
        $candidates = [
            $base . 'parts' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'debugbar.php',
        ];
        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            return self::renderPhpFile($file);
        }

        return '';
    }

    /**
     * Include a partial/template (HTML fragment).
     *
     * Không tìm thấy file hoặc lỗi render: chỉ ghi log + trả về chuỗi an toàn (HTML comment hoặc một dòng cảnh báo khi APP_DEBUG).
     * Không gọi handleError() / không echo trang 404–500 full (tránh “render lồng” giữa layout).
     *
     * @param string $view Template path
     * @param array $data Data to pass to template
     * @return string HTML của partial, hoặc placeholder khi lỗi
     * @throws \InvalidArgumentException Nếu $view không phải string
     */
    public static function include($view, $data = [])
    {
        if (!is_string($view)) {
            throw new \InvalidArgumentException('View template must be a string.');
        }
        try {
            $engine = self::getEngine();
            if (!$engine->exists($view)) {
                $namespace = self::getNamespace();
                $namespaceStr = $namespace ? " (namespace: {$namespace})" : '';

                return self::handleIncludeFailure(
                    new \RuntimeException("Template '{$view}' not found{$namespaceStr}."),
                    $view
                );
            }
            $isDebugbar = self::isDebugbar();
            $startTime = $isDebugbar ? microtime(true) : null;
            $html = $engine->render($view, $data);
            if ($isDebugbar && $startTime !== null) {
                $fullPath = ($engine instanceof \System\Libraries\Render\Template\PhpTemplate) ? $engine->lastRenderPath : null;
                self::trackView('include', $view, $fullPath, $data, (microtime(true) - $startTime) * 1000);
            }
            return $html;
        } catch (\Throwable $e) {
            return self::handleIncludeFailure($e, $view);
        }
    }

    /**
     * Tiện ích: include file trong thư mục `parts/` (cùng cấp các view khác). Tương đương include('parts/…').
     *
     * @param string $name Đường dẫn dưới parts/, không có .php (vd. card, homecomponent/default)
     */
    public static function includePartial($name, $data = [])
    {
        if (!is_string($name) || $name === '') {
            return '';
        }

        return self::include('parts/' . ltrim($name, '/'), $data);
    }

    /**
     * Lỗi khi include partial: không dump trang lỗi theme (tránh chồng &lt;html&gt; giữa trang).
     *
     * @return string Chuỗi rỗng không dùng — luôn trả về comment hoặc (khi debug) một dòng cảnh báo.
     */
    private static function handleIncludeFailure(\Throwable $e, string $view): string
    {
        $msg = "View::include '{$view}': " . $e->getMessage();
        \System\Libraries\Logger::error($msg);

        if (defined('APP_DEBUG') && APP_DEBUG && function_exists('e')) {
            return '<p class="view-include-missing" style="margin:0;padding:6px 10px;font:12px/1.45 system-ui,sans-serif;background:#fff3cd;border:1px solid #e0a800;color:#533f03;border-radius:4px;">'
                . '<strong>Include</strong> <code style="word-break:break-all;">' . e($view) . '</code>'
                . '<br />' . e($e->getMessage())
                . '</p>';
        }

        $safe = str_replace(['--', "\n", "\r"], ['- - ', ' ', ' '], $view . ' | ' . $e->getMessage());

        return '<!-- view include failed: ' . htmlspecialchars($safe, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' -->';
    }

    /**
     * Include a partial if it exists (no error if not found)
     * 
     * @param string $view Template path
     * @param array $data Data to pass to template
     * @return string
     */
    public static function includeIf($view, $data = [])
    {
        if (!is_string($view)) {
            return '';
        }
        try {
            $engine = self::getEngine();
            if (!$engine->exists($view)) {
                return '';
            }
            $isDebugbar = self::isDebugbar();
            $startTime = $isDebugbar ? microtime(true) : null;
            $html = $engine->render($view, $data);
            if ($isDebugbar && $startTime !== null) {
                $fullPath = ($engine instanceof \System\Libraries\Render\Template\PhpTemplate) ? $engine->lastRenderPath : null;
                self::trackView('include', $view, $fullPath, $data, (microtime(true) - $startTime) * 1000);
            }
            return $html;
        } catch (\Throwable $e) {
            // Silent fail for includeIf
            if (defined('APP_DEBUG') && APP_DEBUG) {
                \System\Libraries\Logger::error("View::includeIf error for '{$view}': " . $e->getMessage());
            }
            return '';
        }
    }

    /**
     * Get current namespace
     * 
     * @return string|null
     */
    public static function getNamespace()
    {
        return self::$namespace;
    }

    /**
     * Đặt namespace cho lần resolve template tiếp theo (PhpTemplate).
     *
     * - `web` / `admin` : gắn với ThemeContext scope khi resolve theme.
     * - Chuỗi an toàn khác: coi là prefix thư mục dưới root theme (vd. `common/auth`).
     * - Bắt đầu bằng `@`: namespace plugin (`@Ecommerce`, `@Ecommerce:web`, …).
     *
     * @param string $namespace Namespace
     * @return void
     */
    public static function namespace($namespace)
    {
        if (is_string($namespace)) {
            $normalized = strtolower(trim($namespace));
            if ($normalized === 'frontend') {
                $namespace = 'web';
            } elseif ($normalized === 'backend') {
                $namespace = 'admin';
            }
        }
        self::$namespace = $namespace;
    }

    /**
     * Clear namespace (reset to default)
     * 
     * @return void
     */
    public static function clearNamespace()
    {
        self::$namespace = null;
    }

    /**
     * Set render scope (web/admin).
     *
     * @param string $scope
     * @return void
     */
    public static function scope($scope)
    {
        ThemeContext::setScope($scope);
        self::namespace($scope);
    }

    /**
     * Get current render scope.
     *
     * @return string
     */
    public static function getScope()
    {
        return ThemeContext::getScope();
    }

    /**
     * Set active theme for scope.
     *
     * @param string $themeName
     * @param string|null $scope
     * @return void
     */
    public static function setTheme($themeName, $scope = null)
    {
        ThemeContext::setTheme($themeName, $scope);
    }

    /**
     * Get active theme for scope.
     *
     * @param string|null $scope
     * @return string
     */
    public static function getTheme($scope = null)
    {
        return ThemeContext::getTheme($scope);
    }

    /**
     * Check if debugbar is enabled (cached for performance)
     * 
     * @return bool
     */
    private static function isDebugbar()
    {
        if (self::$cachedDebugbar === null) {
            self::$cachedDebugbar = defined('APP_DEBUGBAR') && APP_DEBUGBAR;
        }
        return self::$cachedDebugbar;
    }

    /**
     * Cached file_exists() check
     * 
     * ⚡ OPTIMIZED: Cache file_exists() results to reduce I/O operations
     * 
     * @param string $path File path to check
     * @return bool
     */
    private static function fileExists($path)
    {
        // Check cache first
        if (isset(self::$fileExistsCache[$path])) {
            return self::$fileExistsCache[$path];
        }
        
        // Perform actual check and cache result
        $exists = file_exists($path);
        self::$fileExistsCache[$path] = $exists;
        
        return $exists;
    }

    /**
     * Check if a template exists
     * 
     * @param string $view Template path
     * @return bool
     */
    public static function exists($view)
    {
        if (!is_string($view)) {
            return false;
        }
        try {
            $engine = self::getEngine();
            return $engine->exists($view);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get full path to a template file
     * 
     * @param string $view Template path
     * @return string|null Full path or null if not found
     */
    public static function getPath($view)
    {
        if (!is_string($view)) {
            return null;
        }
        try {
            $engine = self::getEngine();
            return $engine->getPath($view);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Share data with all views (global data). View instance data ghi đè shared.
     *
     * @param string|array $key Key or array of key-value pairs
     * @param mixed $value Value (if key is string)
     * @return void
     */
    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            self::$sharedData = array_merge(self::$sharedData, $key);
        } else {
            self::$sharedData[$key] = $value;
        }
    }

    /**
     * Get shared data (for tests or inspection).
     *
     * @param string|null $key Key to get, or null to get all shared data
     * @return mixed All shared array if $key is null, or value for $key (null if not set)
     */
    public static function getShared($key = null)
    {
        if ($key === null) {
            return self::$sharedData;
        }
        return self::$sharedData[$key] ?? null;
    }

    /**
     * Clear all shared data (useful for unit tests or when rendering multiple heads in one request).
     *
     * @return void
     */
    public static function clearShared()
    {
        self::$sharedData = [];
    }

    /**
     * Shared data for all views
     * @var array
     */
    private static $sharedData = [];


    /**
     * Get template engine instance (lazy loading)
     * 
     * @return object
     */
    private static function getEngine()
    {
        if (self::$engine === null) {
            self::$engine = new \System\Libraries\Render\Template\PhpTemplate();
        }
        return self::$engine;
    }

    /**
     * Handle rendering errors
     * 
     * ✅ SECURITY: All output escaped, catch Throwable
     * Priority: Theme error pages (404.php, errors.php) → _critical_error() → fallback
     * 
     * @param \Throwable $e Exception or Error
     * @param string $view View path
     * @return void
     */
    private static function handleError(\Throwable $e, $view)
    {
        $message = "View rendering error for '{$view}': " . $e->getMessage();
        \System\Libraries\Logger::error($message);
        
        // Determine error type and status code
        $isNotFound = (strpos($e->getMessage(), 'Template not found') !== false || 
                      strpos($e->getMessage(), 'not found') !== false);
        $statusCode = $isNotFound ? 404 : 500;
        
        // Try to render theme error page first
        $errorPageRendered = self::renderThemeErrorPage($statusCode, $e, $view);
        
        if ($errorPageRendered) {
            return; // Theme error page was rendered successfully
        }
        
        // Fallback: Use _critical_error() or show basic error
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $errorHtml = "<h3>View Rendering Error</h3>";
            $errorHtml .= "<p><strong>Template:</strong> " . e($view) . "</p>";
            $errorHtml .= "<p><strong>Error:</strong> " . e($e->getMessage()) . "</p>";
            $errorHtml .= "<p><strong>File:</strong> " . e($e->getFile()) . ":" . e($e->getLine()) . "</p>";
            $errorHtml .= "<pre>" . e($e->getTraceAsString()) . "</pre>";
            
            if (function_exists('_critical_error')) {
                _critical_error($errorHtml, $statusCode);
            } else {
                http_response_code($statusCode);
                echo $errorHtml;
            }
        } else {
            // Production mode: show generic error
            if (function_exists('_critical_error')) {
                $message = $isNotFound ? "The requested page could not be found." : "An internal server error occurred.";
                _critical_error($message, $statusCode);
            } else {
                http_response_code($statusCode);
                echo $isNotFound ? "Page Not Found" : "Internal Server Error";
            }
        }
    }
    
    /**
     * Try to render theme error page (404.php or errors.php)
     * 
     * @param int $statusCode HTTP status code (404, 500, etc.)
     * @param \Throwable $e Exception
     * @param string $view View path that failed
     * @return bool True if error page was rendered, false otherwise
     */
    private static function renderThemeErrorPage($statusCode, \Throwable $e, $view)
    {
        try {
            $themePath = rtrim(ThemeContext::getThemePath(), '/\\');
            if (!$themePath || !is_dir($themePath)) {
                return false;
            }
            
            $themeName = basename(rtrim($themePath, '/\\'));
            $errorPage = null;
            if ($statusCode === 404) {
                $errorPage = ThemeConfigLoader::themeTemplateFilePath($themeName, '404');
                if ($errorPage === '' || !self::fileExists($errorPage)) {
                    $errorPage = $themePath . DIRECTORY_SEPARATOR . '404.php';
                }
            } else {
                $errorPage = ThemeConfigLoader::themeTemplateFilePath($themeName, 'errors');
                if ($errorPage === '' || !self::fileExists($errorPage)) {
                    $errorPage = $themePath . DIRECTORY_SEPARATOR . 'errors.php';
                }
            }
            
            if ($errorPage && self::fileExists($errorPage)) {
                // Bảo mật: chỉ include file nằm trong thư mục theme (PATH_THEMES)
                $resolvedErrorPath = realpath($errorPage);
                $themesBase = realpath(rtrim(PATH_THEMES, DIRECTORY_SEPARATOR));
                if ($resolvedErrorPath === false || $themesBase === false || strpos($resolvedErrorPath, $themesBase) !== 0) {
                    \System\Libraries\Logger::error("View: Error page path outside theme directory: {$errorPage}");
                    return false;
                }
                $errorPage = $resolvedErrorPath;
                // Prepare error data for the error page
                // Variables that 404.php and errors.php expect:
                // - 404.php: $debug, $message, $file, $line, $trace
                // - errors.php: $statusCode, $message, $file, $line, $trace, $controllerInfo, $debug
                $trace = $e->getTraceAsString();
                
                // Extract controller info from trace (for errors.php)
                // Note: errors.php also extracts this, but we provide it for consistency
                $controllerInfo = '';
                if (!empty($trace) && preg_match('/App\\\\Controllers\\\\([^:]+)/', $trace, $matches)) {
                    $controllerInfo = $matches[1];
                }
                
                $message = $e->getMessage();
                $file = $e->getFile();
                $line = $e->getLine();
                $debug = defined('APP_DEBUG') && APP_DEBUG;
                
                // Set HTTP status code
                http_response_code($statusCode);
                
                // Render error page in isolated scope
                // Pass all variables that error pages might need
                ob_start();
                (static function() use ($errorPage, $statusCode, $message, $file, $line, $trace, $controllerInfo, $debug) {
                    require $errorPage;
                })();
                $output = ob_get_clean();
                
                if (!empty($output)) {
                    echo $output;
                    return true;
                }
            }
        } catch (\Throwable $errorPageException) {
            // If error page itself fails, log and continue to fallback
            \System\Libraries\Logger::error("Failed to render theme error page: " . $errorPageException->getMessage());
        }
        
        return false;
    }

    /**
     * Clear all template cache (no-op: templates load trực tiếp từ theme, không cache).
     *
     * @return bool Always true
     */
    public static function clearCache()
    {
        return true;
    }

    /**
     * Clear specific template cache (no-op: templates load trực tiếp từ theme, không cache).
     *
     * @param string $template Template path
     * @return bool Always true
     */
    public static function forgetCache($template)
    {
        return true;
    }

    // ========================================================================
    // ASSET MANAGEMENT (delegated to AssetManager)
    // ========================================================================

    /**
     * Add CSS file
     * 
     * @param string $handle Unique handle
     * @param string $src Source URL or path
     * @param array $deps Dependencies
     * @param string|null $version Version
     * @param string $media Media type
     * @param bool $in_footer Render in footer instead of head - default false
     * @param bool $preload Preload CSS for better performance - default false
     * @param bool $minify Minify khi production
     * @return void
     */
    public static function addCss($handle, $src, $deps = [], $version = null, $media = 'all', $in_footer = false, $preload = false, $minify = false)
    {
        Asset\AssetManager::addCss($handle, $src, $deps, $version, $media, $in_footer, $preload, $minify);
    }

    /**
     * Add JS file
     * 
     * @param string $handle Unique handle
     * @param string $src Source URL or path
     * @param array $deps Dependencies
     * @param string|null $version Version
     * @param bool $defer Defer loading
     * @param bool $async Async loading
     * @param bool $in_footer Render in footer instead of head - default true
     * @param bool $minify Minify khi production
     * @return void
     */
    public static function addJs($handle, $src, $deps = [], $version = null, $defer = false, $async = false, $in_footer = true, $minify = false)
    {
        Asset\AssetManager::addJs($handle, $src, $deps, $version, $defer, $async, $in_footer, $minify);
    }

    /**
     * Add inline CSS
     * 
     * @param string $handle Unique handle
     * @param string $css CSS code
     * @param array $deps Dependencies - for consistency with addCss
     * @param string|null $version Version - for consistency with addCss
     * @param bool $in_footer Render in footer instead of head - default false
     * @return void
     */
    public static function inlineCss($handle, $css, $deps = [], $version = null, $in_footer = false)
    {
        Asset\AssetManager::inlineCss($handle, $css, $deps, $version, $in_footer);
    }

    /**
     * Add inline JS
     * 
     * @param string $handle Unique handle
     * @param string $js JavaScript code
     * @param array $deps Dependencies - for consistency with addJs
     * @param string|null $version Version - for consistency with addJs
     * @param bool $in_footer Render in footer instead of head - default true
     * @return void
     */
    public static function inlineJs($handle, $js, $deps = [], $version = null, $in_footer = true)
    {
        Asset\AssetManager::inlineJs($handle, $js, $deps, $version, $in_footer);
    }

    /**
     * Localize script: inject JS variables for a given script handle (e.g. ajax_url, nonce).
     *
     * @param string $handle Script handle (must be registered with addJs)
     * @param string $objectName Global JS variable name (e.g. 'myPluginConfig')
     * @param array $data Data to pass (json_encode'd)
     * @param bool $in_footer Same as script location - default true
     * @return void
     */
    public static function localizeScript($handle, $objectName, $data, $in_footer = true)
    {
        Asset\AssetManager::localizeScript($handle, $objectName, $data, $in_footer);
    }

    /**
     * Render CSS HTML
     * 
     * @param string $location Render footer styles at footer or head - default 'head'
     * @return string HTML
     */
    public static function css($location = 'head')
    {
        return Asset\AssetManager::css($location);
    }

    /**
     * Render JS HTML
     * 
     * @param string $location Render footer scripts at footer or head - default 'footer'
     * @return string HTML
     */
    public static function js($location = 'footer')
    {
        return Asset\AssetManager::js($location);
    }

    /**
     * Clear all assets
     * 
     * @param string|null $area Area to clear (null = all)
     * @return void
     */
    public static function clearAssets($area = null)
    {
        Asset\AssetManager::clearAssets($area);
    }

    // ========================================================================
    // HEAD MANAGEMENT (delegated to HeadManager)
    // ========================================================================

    /**
     * Set page title
     * 
     * @param string|array $title Title or title parts
     * @param bool $append Append to existing
     * @return void
     */
    public static function setTitle($title, $append = false)
    {
        Head::setTitle($title, $append);
    }

    /**
     * Set meta description
     * 
     * @param string $description Description
     * @return void
     */
    public static function setDescription($description)
    {
        Head::setDescription($description);
    }

    /**
     * Set meta keywords
     * 
     * @param string|array $keywords Keywords
     * @return void
     */
    public static function setKeywords($keywords)
    {
        Head::setKeywords($keywords);
    }

    /**
     * Add meta tag
     * 
     * @param string $name Meta name
     * @param string $content Content
     * @param string $type Type (name, property, http-equiv)
     * @return void
     */
    public static function addMeta($name, $content, $type = 'name')
    {
        Head::addMeta($name, $content, $type);
    }

    /**
     * Set Open Graph tags
     * 
     * @param array $og OG data
     * @return void
     */
    public static function setOpenGraph($og)
    {
        Head::setOpenGraph($og);
    }

    /**
     * Set Twitter Card tags
     * 
     * @param array $twitter Twitter data
     * @return void
     */
    public static function setTwitterCard($twitter)
    {
        Head::setTwitterCard($twitter);
    }

    /**
     * Set canonical URL
     * 
     * @param string $url Canonical URL
     * @return void
     */
    public static function setCanonical($url)
    {
        Head::setCanonical($url);
    }

    /**
     * Add JSON-LD schema bổ sung vào Head (render trong Head::render() sau schema chính từ Schema library).
     *
     * @param array $schema Schema data
     * @param string|null $key Unique key
     * @return void
     */
    public static function addSchema($schema, $key = null)
    {
        Head::addSchema($schema, $key);
    }

    /**
     * Render <head> content
     * 
     * @param array $options Options
     * @return string HTML
     */
    public static function renderHead($options = [])
    {
        return Head::render($options);
    }

    /**
     * Views đã track trong request (debugbar). Cùng cấu trúc entry như debugbar mong đợi.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getTrackedViews()
    {
        return self::$trackedViews;
    }

    /**
     * Ghi nhận render ngoài PhpTemplate (block, layout legacy, component, …) cho debugbar.
     *
     * @param string $type Type of view (block, layout, …)
     * @param string $name Name/path
     * @param string|null $fullPath Full file path
     * @param array $data
     * @param float $duration Duration in milliseconds
     * @return void
     */
    public static function trackForDebugbar($type, $name, $fullPath = null, $data = [], $duration = 0)
    {
        if (!self::isDebugbar()) {
            return;
        }
        self::trackView($type, $name, $fullPath, is_array($data) ? $data : [], $duration);
    }

    // ========================================================================
    // VIEW TRACKING (debugbar)
    // ========================================================================

    /**
     * Ghi nhận một lần render View/include cho debugbar.
     *
     * @param string $type Type of view (view, include, …)
     * @param string $name Name/path of the view
     * @param string|null $fullPath Full file path
     * @param array $data Data passed to view
     * @param float $duration Duration in milliseconds
     * @return void
     */
    private static function trackView($type, $name, $fullPath = null, $data = [], $duration = 0)
    {
        $path = $fullPath ?? '';
        self::$trackedViews[] = [
            'type' => $type,
            'name' => $name,
            'path' => $fullPath,
            'file_size' => $path !== '' && is_file($path) ? filesize($path) : 0,
            'file_modified' => $path !== '' && is_file($path) ? filemtime($path) : 0,
            'data_keys' => array_keys(is_array($data) ? $data : []),
            'data_count' => is_array($data) ? count($data) : 0,
            'duration_ms' => $duration > 0 ? round($duration, 3) : null,
            'render_time' => microtime(true),
        ];
    }
}


