<?php
/**
 * Language Helper Functions
 * 
 * Global i18n functions (WordPress-style)
 * Wrappers for Fastlang library
 * 
 * @package App\Helpers
 */

use App\Libraries\Fastlang;

if (!function_exists('__')) {
    /**
     * Get translation string
     * 
     * WordPress/i18n-style translation function
     * 
     * @param string $key Translation key
     * @param mixed ...$args Placeholder values
     * @return string Translated string
     * 
     * @example
     * echo __('ecommerce.product_added');
     * echo __('ecommerce.total_items', $count);
     */
    function __($key, ...$args) {
        return Fastlang::__($key, ...$args);
    }
}

if (!function_exists('_e')) {
    /** 
     * Echo translation string
     * 
     * WordPress/i18n-style echo translation function
     * 
     * @param string $key Translation key
     * @param mixed ...$args Placeholder values
     * @return void
     * 
     * @example
     * _e('ecommerce.welcome_message');
     * _e('ecommerce.hello_user', $userName);
     */
    function _e($key, ...$args) {
        Fastlang::_e($key, ...$args);
    }
}

if (!function_exists('lang_name')) {
    function lang_name($currentLang = APP_LANG)
    {
        return isset(APP_LANGUAGES[$currentLang]) ? APP_LANGUAGES[$currentLang]['name'] : $currentLang;
    }
}
if (!function_exists('lang_code')) {
    function lang_code($currentLang = APP_LANG)
    {
        return $currentLang;
    }
}
if (!function_exists('lang_country')) {
    function lang_country($currentLang = APP_LANG)
    {
        return isset(APP_LANGUAGES[$currentLang]) ? strtoupper(APP_LANGUAGES[$currentLang]['flag']) : 'US';
    }
}
if (!function_exists('lang_flag')) {
    function lang_flag($currentLang = APP_LANG)
    {
        // If currentLang is a 2-character string, treat it as a country code directly
        if (strlen($currentLang) === 2 && !isset(APP_LANGUAGES[$currentLang])) {
            $flag = $currentLang;
        } else {
            $flag = isset(APP_LANGUAGES[$currentLang]) ? APP_LANGUAGES[$currentLang]['flag'] : 'us';
        }
        // Check if flag is null or empty, use default
        if (empty($flag)) {
            $flag = 'us';
        }
        // Convert country code to flag emoji using Unicode Regional Indicator Symbols
        $flag = str_split(strtoupper($flag));
        $flag = array_map(function ($char) {
            return mb_chr(ord($char) + 127397);
        }, $flag);
        
        return implode('', $flag);
    }
}
