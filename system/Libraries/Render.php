<?php

namespace System\Libraries;

use System\Core\AppException;
use Exception;
use MatthiasMullie\Minify;

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Render
{
    // Name of the theme
    private static $themeName;
    // Path to theme directory
    private static $themePath;
    // Path to public/assets directory
    private static $assetsPath;

    /*
     * Manage assets with structure:
     * assets[area][asset_type][location]
     * asset_type: 'css', 'js', 'inlineCss', 'inlineJs'
     */
    protected static $assets = [
        'frontend' => [
            'css'       => ['head' => [], 'footer' => []],
            'js'        => ['head' => [], 'footer' => []],
            'inlineCss' => ['head' => [], 'footer' => []],
            'inlineJs'  => ['head' => [], 'footer' => []],
        ],
        'backend'  => [
            'css'       => ['head' => [], 'footer' => []],
            'js'        => ['head' => [], 'footer' => []],
            'inlineCss' => ['head' => [], 'footer' => []],
            'inlineJs'  => ['head' => [], 'footer' => []],
        ],
    ];

    /**
     * Initialize and load theme configuration only once
     * ✅ Uses constants from Constants.php: APP_THEME_NAME, APP_THEME_PATH, PATH_THEMES, PATH_PLUGINS
     */
    private static function init()
    {
        if (self::$themeName === null || self::$themePath === null) {
            // ✅ Use constants directly from Constants.php (already defined)
            self::$themeName = APP_THEME_NAME;
            self::$themePath = APP_THEME_PATH;
            if (self::$assetsPath === null) {
                // Note: PATH_PUBLIC not defined in Constants.php, so keep as is
                self::$assetsPath = PATH_ROOT . '/public/assets/';
            }
        }
    }

    /**
     * Render the entire layout and view with data
     *
     * @param string $layout Name of the layout to load (e.g.: 'layout' or 'layout2')
     * @param array $data Data passed to the view
     * @throws \Exception
     */
    public static function html($layout, $data = [], $area = '')
    {
        self::init(); // Ensure configuration is loaded
        $layoutPath = APP_THEME_PATH . ($area ? ucfirst($area) . '/' : '') . trim($layout, '/') . '.php';
        if (!file_exists($layoutPath)) {
            if (empty($area)) {
                //get first segment of layout
                $layoutSegments = explode('/', trim($layout, '/'));
                $area = $layoutSegments[0];
                $layout = implode('/', array_slice($layoutSegments, 1));
            }
            $layoutPath = PATH_PLUGINS . ($area ? ucfirst($area) . '/' : '') . 'Views/' . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new AppException("Layout '{$layout}' not found.");
            }
        }
        extract($data);
        ob_start();
        if (APP_DEBUGBAR) $___start_time = microtime(true);
        require_once $layoutPath;
        $html = ob_get_clean();
        // Track layout AFTER render with duration
        if (APP_DEBUGBAR) {
            $tracked = $data;
            if (!is_array($tracked)) {
                $tracked = [];
            }
            \System\Libraries\Render\View::trackForDebugbar('layout', $layout, $layoutPath, $tracked, (microtime(true) - $___start_time) * 1000);
        }
        if (APP_DEBUGBAR && stripos($html, '</body>') !== false  && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
            $debugBarHtml = \System\Libraries\Render\View::renderDebugbarHtml();
            $html = str_replace('</body>', $debugBarHtml . '</body>', $html);
        }
        return $html;
    }

    /**
     * Render a specific plugin layout and return as string
     *
     * @param string $pluginName Name of the plugin
     * @param string $layout Name of the layout to load (e.g.: 'index' or 'edit')
     * @param array $data Data passed to the layout
     * @return string Rendered layout result
     * @throws \Exception
     */
    public static function plugin_html($pluginName, $layout = 'index', $data = [])
    {
        self::init(); // Ensure configuration is loaded
        $pluginName = ucfirst($pluginName);
        $layoutPath = APP_THEME_PATH . 'Plugins/' . $pluginName . '/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            $layoutPath = PATH_PLUGINS . $pluginName . '/Views/' . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new AppException("Layout '{$layout}' not found at Path: '{$layoutPath}'.");
            }
        }
        extract($data);
        ob_start();
        if (APP_DEBUGBAR) $___start_time = microtime(true);
        require_once $layoutPath;
        $html = ob_get_clean();
        // Track layout AFTER render with duration
        if (APP_DEBUGBAR) {
            $tracked = $data;
            if (!is_array($tracked)) {
                $tracked = [];
            }
            \System\Libraries\Render\View::trackForDebugbar('layout', $layout, $layoutPath, $tracked, (microtime(true) - $___start_time) * 1000);
        }
        if (APP_DEBUGBAR && stripos($html, '</body>') !== false  && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
            $debugBarHtml = \System\Libraries\Render\View::renderDebugbarHtml();
            $html = str_replace('</body>', $debugBarHtml . '</body>', $html);
        }
        return $html;
    }

    /**
     * Render a specific component and return as string
     *
     * @param string $component Name of the component to render (e.g.: 'header', 'footer')
     * @param array $data Data passed to the component
     * @return string Rendered component result
     * @throws \Exception
     */
    public static function component($component, $data = [])
    {
        self::init(); // Ensure configuration is loaded

        $componentPath = APP_THEME_PATH . $component . '.php';
        if (!file_exists($componentPath)) {
            throw new \Exception("Component '{$component}' does not exist at path '{$componentPath}'.");
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Render\View::trackForDebugbar('component', $component, $componentPath, $data, 1);
        }

        // Start buffer to store output
        // Pass data to component
        extract($data);
        ob_start();
        require $componentPath;
        $componentHtml = ob_get_clean();
        return $componentHtml;
    }


    /**
     * Block method: Load Block data and Render to html
     *
     * @param string $blockName Name of the Block, can be capitalized or lowercase
     * @param array $data Additional props parameters of the Block, if not passed will use Default
     *
     * @return string HTML data rendered from the Block
     */
    public static function block($blockName, $data = [])
    {
        $blockFolder = ucfirst($blockName);
        if (strpos($blockName, '\\') !== false) {
            $blockName = explode("\\", $blockName);
            $blockName = ucfirst(end($blockName));
        } else {
            $blockName = ucfirst($blockName);
        }
        $blockClass = "\App\Blocks\\" . $blockFolder . "\\" . $blockName . "Block";
        if (class_exists($blockClass)) {
            $block = new $blockClass();
            $block->setProps($data);
            $block->render();
            return $block;
        } else {
            throw new AppException("Block class $blockClass does not exist.");
        }
    }

    public static function getblock($blockName)
    {
        $blockName = ucfirst($blockName);
        $blockClass = "\App\Blocks\\" . $blockName . "\\" . $blockName . "Block";
        if (class_exists($blockClass)) {
            $block = new $blockClass();
            return $block;
        } else {
            return null;
        }
    }

    /**
     * Get data returned from block via handleData() without rendering HTML
     *
     * @param string $blockName Block name, e.g.: 'Frontend\Sliders\SliderPost'
     * @param array $props Props data passed to block
     * @return array|null
     */
    public static function getDataFromBlock($blockName, $props = [])
    {
        $blockFolder = ucfirst($blockName);
        if (strpos($blockName, '\\') !== false) {
            $parts = explode("\\", $blockName);
            $blockClassName = ucfirst(end($parts));
            $blockFolder = implode("\\", array_map('ucfirst', $parts));
        } else {
            $blockClassName = ucfirst($blockName);
            $blockFolder = $blockClassName;
        }

        $blockClass = "\\App\\Blocks\\{$blockFolder}\\{$blockClassName}Block";

        if (class_exists($blockClass)) {
            $block = new $blockClass();
            $block->setProps($props);
            return method_exists($block, 'handleData') ? $block->handleData() : null;
        }

        return null;
    }


    ////////////////////// ASSET MANAGEMENT (CSS, JS) //////////////////////

    /**
     * Add asset file (css or js) with options.
     *
     * @param string $assetType 'css' or 'js'
     * @param string $file      File name (relative path from assets folder in view)
     * @param array  $options   Options array including:
     *                          - 'area': (default 'frontend')
     *                          - 'location': (default 'head' or 'footer')
     */
    public static function asset($assetType, $file, $options = [])
    {
        self::init();
        $assetType = strtolower($assetType);
        if (!in_array($assetType, ['css', 'js'])) {
            throw new AppException("Invalid asset type: $assetType");
        }
        $area = $options['area'] ?? 'frontend';
        $location = $options['location'] ?? 'head';
        $defer = $options['defer'] ?? true; // Mặc định defer = true
        if (!in_array($location, ['head', 'footer'])) {
            $location = 'head';
        }
        if (!isset(self::$assets[$area])) {
            self::$assets[$area] = [
                'css'       => ['head' => [], 'footer' => []],
                'js'        => ['head' => [], 'footer' => []],
                'inlineCss' => ['head' => [], 'footer' => []],
                'inlineJs'  => ['head' => [], 'footer' => []],
            ];
        }

        // Lưu cả file và options để render sau
        $type = $options['type'] ?? null;
        self::$assets[$area][$assetType][$location][] = [
            'file' => $file,
            'defer' => $defer,
            'type' => $type
        ];
    }

    /**
     * Add inline content for asset (css or js) with options.
     *
     * @param string $assetType 'css' or 'js'
     * @param string $content   Inline content to add.
     * @param array  $options   Options array including:
     *                          - 'area': (default 'frontend')
     *                          - 'location': (default 'head' or 'footer')
     */
    public static function inline($assetType, $content, $options = [])
    {
        self::init();
        $assetType = strtolower($assetType);
        if (!in_array($assetType, ['css', 'js'])) {
            throw new AppException("Invalid inline asset type: $assetType");
        }
        $area = $options['area'] ?? 'frontend';
        $location = $options['location'] ?? 'head';
        if (!in_array($location, ['head', 'footer'])) {
            $location = 'head';
        }
        $key = ($assetType === 'css') ? 'inlineCss' : 'inlineJs';
        if (!isset(self::$assets[$area])) {
            self::$assets[$area] = [
                'css'       => ['head' => [], 'footer' => []],
                'js'        => ['head' => [], 'footer' => []],
                'inlineCss' => ['head' => [], 'footer' => []],
                'inlineJs'  => ['head' => [], 'footer' => []],
            ];
        }
        self::$assets[$area][$key][$location][] = $content;
    }

    /**
     * Output <link>/<script> tags & inline assets exactly as registered
     * NO merging – NO compression.
     *
     * @param string $location 'head' | 'footer'
     * @param string $area     'frontend' | 'backend'
     * @return string
     */
    public static function renderAsset($location = 'head', $area = 'frontend')
    {
        self::init();
        $output = '';

        // ---------- helper build URL (khớp theme_assets(): assets/ ở root theme, fallback legacy Frontend|Backend) ----------
        $buildUrl = function (string $file) use ($area) {
            // Absolute URL or data URI → return as is
            if (preg_match('#^(https?:)?//|^/|^data:#i', $file)) {
                return $file;
            }
            $legacyArea = ($area === 'backend') ? 'Backend' : 'Frontend';

            return function_exists('theme_assets')
                ? theme_assets(ltrim($file, '/'), $legacyArea)
                : public_url('content/themes/' . APP_THEME_NAME . '/assets/' . ltrim($file, '/'));
        };
        // ---------- CSS ----------
        if (!empty(self::$assets[$area]['css'][$location])) {
            foreach (self::$assets[$area]['css'][$location] as $asset) {
                // Backward compatibility: nếu $asset là string
                $file = is_string($asset) ? $asset : ($asset['file'] ?? '');
                if ($file === '') {
                    continue;                                // skip empty items
                }
                $output .= '<link rel="stylesheet" href="' . $buildUrl($file) . '">' . PHP_EOL;
            }
        }
        // ---------- JS ----------
        if (!empty(self::$assets[$area]['js'][$location])) {
            foreach (self::$assets[$area]['js'][$location] as $asset) {
                // Backward compatibility: nếu $asset là string thì coi như defer = true
                if (is_string($asset)) {
                    $file = $asset;
                    $defer = true;
                    $type = null;
                } else {
                    $file = $asset['file'] ?? '';
                    $defer = $asset['defer'] ?? true;
                    $type = $asset['type'] ?? null;
                }

                if ($file === '') {
                    continue;
                }

                $deferAttr = $defer ? ' defer' : '';
                $typeAttr = $type ? ' type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"' : '';
                $output .= '<script src="' . $buildUrl($file) . '"' . $typeAttr . $deferAttr . '></script>' . PHP_EOL;
            }
        }
        // ---------- INLINE CSS ----------
        if (!empty(self::$assets[$area]['inlineCss'][$location])) {
            $output .= '<style>' . implode("\n", self::$assets[$area]['inlineCss'][$location]) . '</style>' . PHP_EOL;
        }
        // ---------- INLINE JS ----------
        if (!empty(self::$assets[$area]['inlineJs'][$location])) {
            $output .= '<script>' . implode("\n", self::$assets[$area]['inlineJs'][$location]) . '</script>' . PHP_EOL;
        }
        return $output;
    }


    /**
     * Pagination method: create Previous/Next pagination
     * 
     * @param string $base_url Base URL for pagination
     * @param int $current_page Current page number
     * @param bool $is_next Whether there is a next page
     * @param array $query_params Other query parameters to keep on URL
     * @param array $custom_names Custom variable names in query string (page, ...)
     * 
     * @return string Previous/Next pagination HTML
     */
    public static function pagination($base_url, $current_page, $is_next, $query_params = ['limit' =>  10], $custom_names = [])
    {
        self::init();

        // Default variable names for pagination
        $default_names = [
            'page' => 'page',
        ];

        // Combine custom variables with default variable names
        $custom_names = array_merge($default_names, $custom_names);

        // Create query string for other parameters (excluding page)
        $query_string = http_build_query($query_params);

        // Remove ?page=1 if currently on page 1
        if ($current_page == 1) {
            $page_query_string = $query_string ? '?' . $query_string : ''; // No ? if no other query string
        } else {
            $page_query_string = '?' . $custom_names['page'] . '=' . $current_page;
            if ($query_string) {
                $page_query_string .= '&' . $query_string;
            }
        }

        // URLs for previous and next pages
        $prev_page_url = $current_page > 2 ? $base_url . '?' . $custom_names['page'] . '=' . ($current_page - 1) . '&' . $query_string : ($query_string ? $base_url . '?' . $query_string : $base_url);
        $next_page_url = $base_url . '?' . $custom_names['page'] . '=' . ($current_page + 1) . '&' . $query_string;

        // Remove trailing & characters
        $prev_page_url = rtrim($prev_page_url, '&');
        $next_page_url = rtrim($next_page_url, '&');

        $data = [
            'base_url'       => $base_url,
            'current_page'   => $current_page,
            'is_next'        => $is_next,
            'prev_page_url'  => $prev_page_url,
            'next_page_url'  => $next_page_url,
            'custom_names'   => $custom_names,
            'query_params'   => $query_string,
        ];

        $html = \System\Libraries\Render\View::includeIf('parts/ui/pagination', $data);
        if ($html !== '') {
            return $html;
        }
        \System\Libraries\Logger::error('Render::pagination: missing parts/ui/pagination.php in active theme.');

        return '';
    }
}
