<?php

namespace System\Libraries\Render\Theme;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * Load and cache theme PHP config files.
 *
 * View PHP: luôn resolve dưới content/themes/{name}/ (không có thư mục templates/ hay templates_dir).
 */
class ThemeConfigLoader
{
    /** @var array<string,array> */
    private static $cache = [];

    public static function getThemeConfig($themeName)
    {
        return self::loadConfig($themeName, 'theme');
    }

    /**
     * Root theme — mọi file view (index, parts/..., parts/...) nằm trực tiếp dưới đây.
     */
    public static function getThemeViewRootPath($themeName)
    {
        $themeName = trim((string) $themeName, '/\\');
        if ($themeName === '') {
            return '';
        }

        return rtrim(PATH_THEMES, '/\\') . DIRECTORY_SEPARATOR . $themeName . DIRECTORY_SEPARATOR;
    }

    /**
     * Đường dẫn tuyệt đối tới file view (vd. index → .../index.php, parts/foo → .../parts/foo.php).
     */
    public static function themeTemplateFilePath($themeName, $template)
    {
        $template = trim((string) $template, '/');
        if ($template === '' || strpos($template, '..') !== false) {
            return '';
        }
        $root = self::getThemeViewRootPath($themeName);
        if ($root === '') {
            return '';
        }
        $ds = DIRECTORY_SEPARATOR;

        return $root . str_replace('/', $ds, $template) . '.php';
    }

    public static function clear()
    {
        self::$cache = [];
    }

    private static function loadConfig($themeName, $fileName)
    {
        $themeName = trim((string) $themeName, '/');
        $fileName = trim((string) $fileName);
        if ($themeName === '' || $fileName === '') {
            return [];
        }

        $cacheKey = $themeName . '::' . $fileName;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $path = rtrim(PATH_THEMES, '/\\') . DIRECTORY_SEPARATOR
            . $themeName . DIRECTORY_SEPARATOR
            . 'config' . DIRECTORY_SEPARATOR
            . $fileName . '.php';

        if (!is_file($path)) {
            self::$cache[$cacheKey] = [];

            return self::$cache[$cacheKey];
        }

        $loaded = include $path;
        self::$cache[$cacheKey] = is_array($loaded) ? $loaded : [];

        return self::$cache[$cacheKey];
    }
}
