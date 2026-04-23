<?php

namespace System\Libraries\Render\Template;

use System\Libraries\Render\Security\PathValidator;
use System\Libraries\Render\Theme\ThemeConfigLoader;
use System\Libraries\Render\Theme\ThemeContext;

/**
 * PHP Template Engine – locate + render (một class, không dùng TemplateLocator riêng).
 *
 * Theme: content/themes/{theme}/… (không còn thư mục tách Frontend/Backend). Plugin: @TênPlugin/… hoặc namespace @Plugin:KhuCon/…
 * Hooks: render.template_name, render.data, render.output.
 *
 * @package System\Libraries\Render\Template
 * @since 1.0.0
 */
class PhpTemplate
{
    private $currentTheme;
    private $parentTheme;
    /** @var array In-request cache: template::namespace => path */
    private $locateCache = [];
    /** @var array Static cache file_exists (path => bool) */
    private static $fileExistsCache = [];

    public function __construct()
    {
        $this->currentTheme = ThemeContext::getTheme();
        $this->parentTheme = $this->detectParentTheme();
    }

    public function render($template, array $data = [])
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template must be a string.');
        }
        $namespace = \System\Libraries\Render\View::getNamespace();
        if (function_exists('apply_filters')) {
            $template = apply_filters('render.template_name', $template, $namespace, $data);
        }
        if (!is_string($template)) {
            throw new \RuntimeException('Filter render.template_name must return a string.');
        }
        $path = $this->locate($template, $namespace);
        if ($path === null && function_exists('apply_filters')) {
            $fallback = apply_filters('render.template_fallback', null, $template, $namespace);
            if (is_string($fallback) && $fallback !== '') {
                $validated = PathValidator::validateResolvedPath($fallback);
                if ($validated !== null) {
                    $path = $validated;
                }
            }
        }
        if ($path === null) {
            throw new \RuntimeException("Template not found: {$template} (namespace: " . ($namespace ?? 'none') . ")");
        }
        if (function_exists('do_action')) {
            do_action('render.template_found', $path, $template, $namespace);
        }
        if (function_exists('apply_filters')) {
            $data = apply_filters('render.data', $data, $template, $path);
        }

        $startTime = defined('APP_DEBUGBAR') && APP_DEBUGBAR ? microtime(true) : null;
        $html = $this->loadPhpFile($path, $data);
        if ($startTime !== null) {
            $this->lastRenderPath = $path;
            $this->lastRenderDuration = (microtime(true) - $startTime) * 1000;
        }
        if (function_exists('apply_filters')) {
            $html = apply_filters('render.output', $html, $template, $path, $data);
        }
        return $html;
    }

    public function exists($template)
    {
        return $this->locate($template, \System\Libraries\Render\View::getNamespace()) !== null;
    }

    public function getPath($template)
    {
        return $this->locate($template, \System\Libraries\Render\View::getNamespace());
    }

    /** Resolve full path: theme (current → parent) hoặc plugin (@). */
    public function locate($template, $namespace = null)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template must be a string.');
        }
        if ($namespace !== null && !is_string($namespace)) {
            throw new \InvalidArgumentException('Namespace must be null or a string.');
        }
        if ($namespace === '') {
            $namespace = null;
        }
        if (!PathValidator::isValidTemplateName($template)) {
            \System\Libraries\Logger::error("PhpTemplate: SECURITY REJECT - Invalid template name: {$template}");
            throw new \RuntimeException("Invalid template name (security): {$template}");
        }
        if ($namespace !== null && $namespace !== '') {
            if ($namespace[0] === '@') {
                $parts = explode(':', substr($namespace, 1), 2);
                if (!PathValidator::isValidComponentName($parts[0])) {
                    \System\Libraries\Logger::error("PhpTemplate: SECURITY REJECT - Invalid component: {$namespace}");
                    throw new \RuntimeException("Invalid namespace (security): {$namespace}");
                }
                if (isset($parts[1]) && !PathValidator::isValidArea($parts[1])) {
                    throw new \RuntimeException("Invalid area in namespace (security): {$namespace}");
                }
            } elseif (!PathValidator::isValidArea($namespace)) {
                throw new \RuntimeException("Invalid theme namespace (security): {$namespace}");
            }
        }

        $cacheKey = $template . '::' . ($namespace ?? '');
        if (isset($this->locateCache[$cacheKey])) {
            return $this->locateCache[$cacheKey];
        }

        // @Plugin/path hoặc @Plugin:Area/path — resolve plugin dù View::scope('admin') đang đặt namespace theme.
        $pluginRef = $this->parsePluginViewReference($template);
        if ($pluginRef !== null) {
            $result = $this->locatePlugin($pluginRef['path'], $pluginRef['namespace']);
        } else {
            $result = $namespace !== null
                ? $this->locateWithNamespace($template, $namespace)
                : $this->locateTheme($template, null);
        }

        if ($result !== null) {
            $validated = PathValidator::validateResolvedPath($result);
            if ($validated === null) {
                \System\Libraries\Logger::error("PhpTemplate: SECURITY REJECT - Path outside allowed: {$result}");
                throw new \RuntimeException("Template path outside allowed directories (security): {$template}");
            }
            $result = $validated;
        }
        $this->locateCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Chuỗi view dạng @TenPlugin/duong-den-file (hoặc @TenPlugin:Khu/duong) → namespace + path cho locatePlugin().
     *
     * @return array{namespace:string,path:string}|null
     */
    private function parsePluginViewReference($template)
    {
        if ($template === '' || $template[0] !== '@') {
            return null;
        }
        $afterAt = substr($template, 1);
        $slash = strpos($afterAt, '/');
        if ($slash === false || $slash === 0) {
            return null;
        }
        $head = substr($afterAt, 0, $slash);
        $path = substr($afterAt, $slash + 1);
        if ($path === '') {
            return null;
        }
        if (strpos($head, ':') !== false) {
            $hp = explode(':', $head, 2);
            $plugin = $hp[0] ?? '';
            $area = $hp[1] ?? '';
            if (!PathValidator::isValidComponentName($plugin) || !PathValidator::isValidArea($area)) {
                return null;
            }

            return ['namespace' => '@' . $plugin . ':' . $area, 'path' => $path];
        }
        if (!PathValidator::isValidComponentName($head)) {
            return null;
        }

        return ['namespace' => '@' . $head, 'path' => $path];
    }

    private function locateWithNamespace($template, $namespace)
    {
        if ($namespace[0] === '@') {
            return $this->locatePlugin($template, $namespace);
        }
        return $this->locateTheme($template, $namespace);
    }

    private function locatePlugin($template, $namespace)
    {
        $ns = substr($namespace, 1);
        $parts = explode(':', $ns, 2);
        $pluginName = $parts[0];
        $area = $parts[1] ?? '';
        if (!PathValidator::isValidComponentName($pluginName) || !PathValidator::isValidArea($area)) {
            return null;
        }
        if (!PathValidator::isValidTemplatePath($template)) {
            return null;
        }
        $themeRel = $area ? $area . '/' . $template : $template;
        $ds = DIRECTORY_SEPARATOR;
        $search = [
            PATH_THEMES . $this->currentTheme . $ds . 'plugins' . $ds . $pluginName . $ds . str_replace('/', $ds, $themeRel) . '.php',
        ];
        if ($this->parentTheme) {
            $search[] = PATH_THEMES . $this->parentTheme . $ds . 'plugins' . $ds . $pluginName . $ds . str_replace('/', $ds, $themeRel) . '.php';
        }
        $pluginRel = $area ? 'Views' . $ds . $area . $ds . $template : 'Views' . $ds . $template;
        $search[] = PATH_PLUGINS . $pluginName . $ds . $pluginRel . '.php';
        foreach ($search as $p) {
            if (self::fileExists($p)) {
                return $p;
            }
        }
        return null;
    }

    private function locateTheme($template, $namespace)
    {
        if (!PathValidator::isValidTemplatePath($template)) {
            return null;
        }
        $scope = null;

        if ($namespace !== null) {
            if (!PathValidator::isValidArea($namespace)) {
                return null;
            }
            $scope = $this->normalizeScope($namespace);
            if ($scope === null) {
                // Namespace path (e.g. common/email) should be treated as template subdirectory.
                $template = trim($namespace, '/') . '/' . ltrim($template, '/');
            }
        }

        if ($scope === null) {
            $scope = ThemeContext::getScope();
        }

        $currentTheme = ThemeContext::getTheme($scope);
        $parentTheme = $this->detectParentTheme($currentTheme);
        return $this->findTemplateInThemes($template, $currentTheme, $parentTheme);
    }

    private function detectParentTheme($themeName = null)
    {
        static $cached = [];
        $themeName = trim((string) ($themeName ?? $this->currentTheme), '/');
        if ($themeName === '') {
            return null;
        }
        if (array_key_exists($themeName, $cached)) {
            return $cached[$themeName];
        }

        $cfg = ThemeConfigLoader::getThemeConfig($themeName);
        if (!empty($cfg['parent']) && is_string($cfg['parent'])) {
            $cached[$themeName] = trim($cfg['parent'], '/');
            return $cached[$themeName];
        }

        $configPath = PATH_THEMES . $themeName . '/Config/Config.php';
        if (self::fileExists($configPath)) {
            $config = include $configPath;
            if (!empty($config['parent'])) {
                $cached[$themeName] = trim((string) $config['parent'], '/');
                return $cached[$themeName];
            }
        }

        $cached[$themeName] = null;
        return null;
    }

    private function normalizeScope($scope)
    {
        $scope = strtolower(trim((string) $scope));
        if ($scope === 'frontend') {
            return 'web';
        }
        if ($scope === 'backend') {
            return 'admin';
        }
        if ($scope === 'web' || $scope === 'admin') {
            return $scope;
        }
        return null;
    }

    private function findTemplateInThemes($template, $currentTheme, $parentTheme = null)
    {
        $template = trim((string) $template, '/');
        if ($template === '') {
            return null;
        }

        $ds = DIRECTORY_SEPARATOR;
        $search = [];

        $currentRoot = ThemeConfigLoader::getThemeViewRootPath($currentTheme);
        if ($currentRoot !== '') {
            $search[] = $currentRoot . str_replace('/', $ds, $template) . '.php';
        }

        if ($parentTheme) {
            $parentRoot = ThemeConfigLoader::getThemeViewRootPath($parentTheme);
            if ($parentRoot !== '') {
                $search[] = $parentRoot . str_replace('/', $ds, $template) . '.php';
            }
        }

        foreach ($search as $path) {
            if (self::fileExists($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function fileExists($path)
    {
        if (isset(self::$fileExistsCache[$path])) {
            return self::$fileExistsCache[$path];
        }
        self::$fileExistsCache[$path] = file_exists($path);
        return self::$fileExistsCache[$path];
    }

    /** Clear file_exists cache (for tests). */
    public static function clearFileExistsCache()
    {
        self::$fileExistsCache = [];
    }

    private function loadPhpFile($__path, array $__data = [])
    {
        if (!is_string($__path) || substr($__path, -4) !== '.php') {
            throw new \RuntimeException("Only .php template files are supported. Found: " . (is_string($__path) ? $__path : gettype($__path)));
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            include $__path;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /** @var string|null */
    public $lastRenderPath = null;
    /** @var float|null ms */
    public $lastRenderDuration = null;
}
