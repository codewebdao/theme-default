<?php
namespace App\Blocks\Meta\Config;

class MetaConfig {
    /**
     * Get default meta tags configuration
     */
    public static function getDefaultMeta() {
        $supportedLanguages = [];
        foreach (APP_LANGUAGES as $lang => $langData) {
            $supportedLanguages[$lang] = base_url($lang === APP_LANG_DF ? '' : $lang);
        }

        return [
            'title' => option('site_title', APP_LANG),
            'title_futures' => option('site_futures_title', APP_LANG),
            'title_library' => option('site_library_title', APP_LANG),
            'title_blogs' => option('site_blogs_title', APP_LANG),
            'description' => option('site_description', APP_LANG),
            'futures_description' => option('site_futures_description', APP_LANG),
            'library_description' => option('site_library_description', APP_LANG),
            'blogs_description' => option('site_blogs_description', APP_LANG),
            'login_title' => option('site_login_title', APP_LANG),
            'login_description' => option('site_login_description', APP_LANG),
            'register_title' => option('site_register_title', APP_LANG),
            'register_description' => option('site_register_description', APP_LANG),
            'forgot_password_title' => option('site_forgot_pass_title', APP_LANG),
            'forgot_password_description' => option('site_forgot_pass_description', APP_LANG),
            'keywords' => option('site_keywords', ''),
            'robots' => option('site_robots', 'index, follow'), 
            'canonical' => option('site_url', base_url()),
            'generator' => option('site_generator', 'CMS Full Form'),
            'language' => APP_LANG,
            'author' => option('site_author', ''),
            'copyright' => option('site_copyright', ''),
            'theme_color' => option('site_theme_color', '#ffffff'),
            'og_type' => option('site_og_type', 'website'),
            'twitter_card' => option('site_twitter_card', 'summary_large_image'),
            'locale' => option('site_locale', APP_LANG),
            'supported_languages' => $supportedLanguages
        ];
    }

    /**
     * Get social media meta tags configuration
     */
    public static function getSocialMeta() {
        return [
            'facebook' => option('social_facebook', ''),
            'twitter' => option('social_twitter', ''),
            'youtube' => option('social_youtube', ''),
            'instagram' => option('social_instagram', ''),
            'pinterest' => option('social_pinterest', '')
        ];
    }

    /**
     * Get third-party services configuration
     */
    public static function getThirdPartyConfig() {
        return [
            'google_analytics_id' => option('google_analytics_id', ''),
            'google_adsense_id' => option('google_adsense_id', ''),
            'dmca_verification' => option('dmca_verification', '')
        ];
    }
} 