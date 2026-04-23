<?php
namespace System\Libraries;

class Shortcode
{
    protected static $handlers = [];
    protected static $loadedFiles = [];
    protected static $context = null;

    /**
     * Register a shortcode
     *
     * @param string   $name
     * @param callable $callback
     */
    public static function register($name, $callback)
    {
        self::$handlers[$name] = $callback;
    }

    /**
     * Execute shortcode
     *
     * @param string $name
     * @param mixed ...$params
     * @return mixed|string
     */
    public static function run($name, ...$params)
    {
        if (!isset(self::$handlers[$name])) {
            trigger_error("Shortcode '{$name}' does not exist.", E_USER_WARNING);
            return '';
        }

        try {
            return call_user_func_array(self::$handlers[$name], $params);
        } catch (\Throwable $e) {
            error_log("Shortcode '{$name}' error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Return list of registered shortcodes
     *
     * @return array
     */
    public static function list()
    {
        return array_keys(self::$handlers);
    }

    /**
     * Check if shortcode exists
     *
     * @param string $name
     * @return bool
     */
    public static function exists($name)
    {
        return isset(self::$handlers[$name]);
    }

    /**
     * Set context for shortcodes (optional)
     *
     * @param array $data
     */
    public static function setContext($data)
    {
        self::$context = $data;
    }

    /**
     * Get context
     *
     * @return array|null
     */
    public static function getContext()
    {
        return self::$context;
    }

    /**
     * Initialize - load all shortcodes from plugins and theme
     */
    public static function init()
    {
        if (!empty(self::$loadedFiles)) {
            return;
        }

        // Load plugin shortcode
        foreach (glob(PATH_PLUGINS . '*/shortcode/*.php') as $file) {
            if (!in_array($file, self::$loadedFiles)) {
                require_once $file;
                self::$loadedFiles[] = $file;
            }
        }

        // Load theme shortcode
        foreach (glob(APP_THEME_PATH . '/shortcode/*.php') as $file) {
            if (!in_array($file, self::$loadedFiles)) {
                require_once $file;
                self::$loadedFiles[] = $file;
            }
        }
    }

    /**
     * Reload a specific shortcode
     *
     * @param string $name
     * @return bool
     */
    public static function reload($name)
    {
        $paths = array_merge(
            glob(PATH_PLUGINS . '*/shortcode/' . $name . '.php'),
            glob(APP_THEME_PATH . '/shortcode/' . $name . '.php')
        );

        if (empty($paths)) {
            return false;
        }

        foreach ($paths as $file) {
            require_once $file;
            self::$loadedFiles[] = $file;
        }

        return true;
    }
}
