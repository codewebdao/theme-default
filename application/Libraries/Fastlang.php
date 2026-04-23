<?php
namespace App\Libraries;

class Fastlang
{
    // Array to store all loaded translations
    protected static $translations = [];

    // Array to store loaded files
    protected static $load_list = [];

    /**
     * Load language file if not already loaded
     * Supports loading from:
     * - App Languages: {PATH_APP}Languages/{lang}/{file}.php
     * - Plugin Languages: {PATH_PLUGINS}{plugin}/Languages/{lang}/{file}.php
     * - Theme Languages: {PATH_THEMES}{theme}/Languages/{lang}/{file}.php
     * 
     * Security: Prevents path traversal attacks
     */
    public static function load($fileName, $lang = APP_LANG, $source = 'application', $sourceName = '')
    {
        // ================================================================
        // SECURITY: Sanitize inputs to prevent path traversal attacks
        // ================================================================
        
        // Validate source (whitelist only)
        if (!in_array($source, ['application', 'plugins', 'themes'], true)) {
            $source = 'application';
        }
        // Sanitize language code (only allow alphanumeric and underscore)
        $lang = preg_replace('/[^a-zA-Z0-9_]/', '', $lang);
        if (empty($lang)) {
            $lang = APP_LANG;
        }
        
        // Sanitize fileName (remove path traversal attempts)
        $fileName = preg_replace('/[^a-zA-Z0-9\/_\-]/', '', $fileName); // Only allow safe characters
        if (empty($fileName)) {
            return; // Invalid filename
        }
        
        // Sanitize sourceName (remove path traversal attempts)
        $sourceName = basename($sourceName); // Remove any directory path
        $sourceName = preg_replace('/[^a-zA-Z0-9\/_\-]/', '', $sourceName); // Only allow safe characters
        
        $file_key = "{$source}_{$sourceName}_{$lang}_{$fileName}";

        // Check if language file has already been loaded
        if (!isset(self::$load_list[$file_key])) {
            $file_lang = '';
            
            // Determine file path based on source
            switch ($source) {
                case 'plugins':
                    if ($sourceName) {
                        $file_lang = PATH_PLUGINS . "{$sourceName}/Languages/{$lang}/" . ucfirst($fileName) . ".php";
                    }
                    break;
                    
                case 'themes':
                    if ($sourceName) {
                        $file_lang = PATH_THEMES . "{$sourceName}/Languages/{$lang}/" . ucfirst($fileName) . ".php";
                    }
                    break;
                    
                case 'application':
                default:
                    $file_lang = PATH_APP . "Languages/{$lang}/" . ucfirst($fileName) . ".php";
                    break;
            }
            
            // ================================================================
            // SECURITY: Verify file path is within allowed directories
            // ================================================================
            if (!empty($file_lang) && file_exists($file_lang)) {
                $translations = require $file_lang;

                // Merge new translations into main translations array
                self::$translations = array_merge(self::$translations, $translations);

                // Mark this file as loaded
                self::$load_list[$file_key] = true;
            }
        }
    }

    /**
     * Load plugin language file
     * 
     * @param string $fileName Language file name
     * @param string $plugin Plugin name
     * @param string $lang Language code
     */
    public static function loadPlugin($fileName, $plugin, $lang = APP_LANG)
    {
        $plugin = ucfirst($plugin);
        self::load($fileName, $lang, 'plugins', $plugin);
    }

    /**
     * Load theme language file
     * 
     * @param string $fileName Language file name
     * @param string $theme Theme name
     * @param string $lang Language code
     */
    public static function loadTheme($fileName, $theme, $lang = APP_LANG)
    {
        self::load($fileName, $lang, 'themes', $theme);
    }

    /**
     * Load app language file (default behavior)
     * 
     * @param string $fileName Language file name
     * @param string $lang Language code
     */
    public static function loadApp($fileName, $lang = APP_LANG)
    {
        self::load($fileName, $lang);
    }

    /**
     * Auto load language file from multiple sources
     * Tries to load from app, then plugin, then theme
     * 
     * @param string $fileName Language file name
     * @param string $lang Language code
     * @param string $plugin Plugin name (optional)
     * @param string $theme Theme name (optional)
     */
    public static function loadAuto($fileName, $lang = APP_LANG, $plugin = null, $theme = null)
    {
        // Try app first
        self::loadApp($fileName, $lang);
        
        // Try plugin if specified
        if ($plugin) {
            self::loadPlugin($fileName, $plugin, $lang);
        }
        
        // Try theme if specified
        if ($theme) {
            self::loadTheme($fileName, $theme, $lang);
        }
    }

    /**
     * Get translation string (i18n/WordPress-style)
     * 
     * Example: $textTranslate = Fastlang::__('Hello %1%, this is %3%, and this is %2%', 'John#first', 'Jane#end', 'Doe#center')
     * 
     * @param string $key Translation key
     * @param mixed ...$args Placeholder values
     * @return string Translation
     */
    public static function __($key, ...$args)
    {
        if (is_array($key)) {
            return json_encode($key);
        }
        $translation = self::$translations[$key] ?? ucfirst($key);
        // Only call replacePlaceholders if $args is not empty
        if (!empty($args)) {
            $translation = self::replacePlaceholders($translation, $args);
        }
        return $translation;
    }

    /**
     * Echo translation string (i18n/WordPress-style)
     * 
     * Example: Fastlang::_e('Hello %1%, this is %3%, and this is %2%', 'John#first', 'Jane#end', 'Doe#center')
     * 
     * @param string $key Translation key
     * @param mixed ...$args Placeholder values
     * @return void
     */
    public static function _e($key, ...$args)
    {
        if (is_array($key)) {
            echo json_encode($key);
        }
        $translation = self::$translations[$key] ?? ucfirst($key);
        // Only call replacePlaceholders if $args is not empty
        if (!empty($args)) {
            $translation = self::replacePlaceholders($translation, $args);
        }
        echo $translation;
        unset($translation);
    }
    
    /**
     * Legacy support - Get translation (old method name)
     * 
     * Example: $textTranslate = Fastlang::get('Hello %1%, this is %3%, and this is %2%', 'John#first', 'Jane#end', 'Doe#center')
     * 
     * @deprecated Use __() instead
     * @param string $key Translation key
     * @param mixed ...$args Placeholder values
     * @return string Translation
     */
    public static function get($key, ...$args)
    {
        return self::__($key, ...$args);
    }
    
    /**
     * Legacy support - Echo translation (old method name)
     * 
     * Example: Fastlang::echo('Hello %1%, this is %3%, and this is %2%', 'John#first', 'Jane#end', 'Doe#center')
     * 
     * @deprecated Use _e() instead
     * @param string $key Translation key
     * @param mixed ...$args Placeholder values
     * @return void
     */
    public static function echo($key, ...$args)
    {
        self::_e($key, ...$args);
    }

    /**
     * Replace placeholders in string with provided values
     */
    protected static function replacePlaceholders($string, $args)
    {
        foreach ($args as $index => $value) {
            $string = str_replace('%' . ($index + 1) . '%', $value, $string);
        }
        return $string;
    }

    /**
     * Reset translations and load list
     */
    public static function reset()
    {
        self::$translations = [];
        self::$load_list = [];
    }
}
