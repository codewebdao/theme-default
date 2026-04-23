<?php

namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use App\Services\Settings\SettingsService;
use System\Libraries\Session;
use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

/**
 * SettingsController - Website Settings Management
 * 
 * Handles all website configuration:
 * - General settings
 * - Media & images
 * - Performance & cache
 * - SEO & sitemap
 * - Social & API
 * 
 * Uses Storage Helper for data persistence with caching
 * 
 * @package App\Controllers\Backend
 */
class SettingsController extends BackendController
{
    /** @var SettingsService */
    protected $settingsService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        load_helpers(['storage', 'forms']);
        
        $this->settingsService = new SettingsService();
        
        Flang::load('general', APP_LANG);
        Flang::load('Backend/Settings', APP_LANG);
    }

    /**
     * Settings Overview Page
     * 
     * Display all settings groups with cards
     * 
     * GET /admin/settings
     */
    public function index()
    {
        $groups = $this->settingsService->getSettingsGroups();
        
        $this->data('title', __('Website Settings'));
        $this->data('groups', $groups);
        $this->data('csrf_token', Session::csrf_token(600));
        
        echo View::make('settings_index', $this->data)->render();
    }

    /** GET|POST /admin/settings/general – phân quyền theo action. */
    public function general(): void { $this->showSettingsPage('general'); }

    /** GET|POST /admin/settings/email */
    public function email(): void { $this->showSettingsPage('email'); }

    /** GET|POST /admin/settings/media */
    public function media(): void { $this->showSettingsPage('media'); }

    /** GET|POST /admin/settings/performance */
    public function performance(): void { $this->showSettingsPage('performance'); }

    /** GET|POST /admin/settings/seo */
    public function seo(): void { $this->showSettingsPage('seo'); }

    /** GET|POST /admin/settings/social_api */
    public function social_api(): void { $this->showSettingsPage('social_api'); }

    /** GET|POST /admin/settings/security */
    public function security(): void { $this->showSettingsPage('security'); }

    /** GET|POST /admin/settings/developer */
    public function developer(): void { $this->showSettingsPage('developer'); }

    /**
     * Generic settings page: GET shows form, POST saves.
     */
    public function showSettingsPage(string $type): void
    {
        if (!in_array($type, $this->settingsService->getSettingTypeIds(), true)) {
            show_404();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveSettings($type);
            return;
        }

        $config = $this->settingsService->getSettingsByType($type);
        $group = $this->settingsService->getGroup($type);
        $title = $group['title'] ?? __('Settings');
        $currentData = $this->loadSettings($type, $config['fields']);
        $formOptions = array_merge(
            ['id' => $type . '_settings', 'method' => 'POST'],
            $this->settingsService->getFormOptions($type)
        );

        // General/SEO: có field lưu theo ngôn ngữ → cần post_lang trong URL để load/save đúng
        $submitUrl = admin_url("settings/{$type}");
        if (in_array($type, ['general', 'seo'], true)) {
            $postLang = $_REQUEST['post_lang'] ?? (defined('APP_LANG') ? APP_LANG : 'en');
            $formOptions['post_lang'] = $postLang;
            $submitUrl = $submitUrl . (strpos($submitUrl, '?') !== false ? '&' : '?') . 'post_lang=' . rawurlencode($postLang);
        }

        $formHtml = forms_create(
            $title,
            $config['fields'],
            $submitUrl,
            $currentData,
            $config['tabs'],
            $formOptions
        );

        $this->data('title', $title);
        $this->data('formHtml', $formHtml);
        $this->data('settingType', $type);
        echo View::make('settings_forms', $this->data)->render();
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Load settings from storage (uses handler per setting type: all vs per-language).
     *
     * @param string $settingType Setting type (general, media, performance, etc.)
     * @param array $fields Field definitions with default values
     * @return array Current settings
     */
    protected function loadSettings($settingType, $fields)
    {
        return $this->settingsService->load($settingType, $fields);
    }

    /**
     * Save settings to storage.
     * General/SEO: per-field storage_lang (all vs current lang). Others: one lang per page.
     *
     * @param string $settingType Setting type
     * @return void
     */
    protected function saveSettings($settingType)
    {
        if (!Session::csrf_verify(S_POST('csrf_token'))) {
            Session::flash('error', __('Invalid CSRF token'));
            $this->redirectToSettings($settingType);
            return;
        }

        $this->settingsService->save($settingType, $_POST);

        if ($settingType === 'performance') {
            $this->clearPerformanceCache();
            $this->maybeBumpAssetBuildVersion();
            $this->triggerAssetBuild();
        }

        Session::flash('success', __('Settings saved successfully'));
        $this->redirectToSettings($settingType);
    }

    /** Redirect to settings page; giữ post_lang cho general/seo. */
    protected function redirectToSettings(string $settingType): void
    {
        $url = admin_url("settings/{$settingType}");
        if (in_array($settingType, ['general', 'seo'], true)) {
            $postLang = $_REQUEST['post_lang'] ?? (defined('APP_LANG') ? APP_LANG : 'en');
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'post_lang=' . rawurlencode($postLang);
        }
        redirect($url);
    }

    /**
     * Bump asset_build_version when build-related settings change.
     * Only bump for: combine_*, minify_*, exclude lists, self_host_*, build_ttl_days, build_lru_max, external_ttl_days.
     * NOT for: defer_js, async_css, minify_html (render/output options).
     */
    protected function maybeBumpAssetBuildVersion()
    {
        load_helpers(['storage']);
        $scope = 'application';
        $lang = 'all';
        $buildRelatedKeys = [
            'minify_css', 'minify_css_exclude', 'minify_js', 'minify_js_exclude',
            'combine_css', 'combine_css_exclude', 'combine_js', 'combine_js_exclude',
            'build_ttl_days', 'build_lru_max', 'external_ttl_days',
            'self_host_external_assets', 'self_host_skip_whitelist',
        ];
        $bump = false;
        foreach ($buildRelatedKeys as $key) {
            if (!isset($_POST[$key])) continue;
            $old = storage_get($key, $scope, $lang, null);
            $new = $_POST[$key];
            if ((string)$old !== (string)$new) {
                $bump = true;
                break;
            }
        }
        if ($bump) {
            $v = (int) storage_get('asset_build_version', $scope, $lang, 1);
            storage_set('asset_build_version', $v + 1, $scope, $lang);
        }
    }

    /**
     * Trigger asset build (async or sync)
     */
    protected function triggerAssetBuild()
    {
        if (!class_exists(\App\Services\Asset\AssetsService::class)) {
            return;
        }
        try {
            \App\Services\Asset\AssetsService::build();
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Asset build error: ' . $e->getMessage());
        }
    }

    /**
     * Clear performance cache
     * 
     * @return void
     */
    protected function clearPerformanceCache()
    {
        // Clear file cache
        if (function_exists('storage_clear_all')) {
            storage_clear_all();
        }
        
        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    /**
     * Purge Nginx cache (for AJAX)
     * 
     * POST /admin/settings/purge-nginx
     */
    public function purgeNginx()
    {
        header('Content-Type: application/json');
        
        if (!Session::csrf_verify(S_POST('csrf_token'))) {
            echo json_encode(['success' => false, 'message' => __('Invalid CSRF token')]);
            return;
        }
        
        $url = S_POST('url') ?? '';
        $purgeAll = S_POST('purge_all') ?? false;
        
        // Get Nginx settings (performance = global, use 'all')
        $endpoint = storage_get('nginx_purge_endpoint', 'application', 'all', '');
        $secretKey = storage_get('nginx_secret_key', 'application', 'all', '');
        
        if (empty($endpoint)) {
            echo json_encode(['success' => false, 'message' => __('Nginx purge endpoint not configured')]);
            return;
        }
        
        // Build purge request
        $purgeUrl = $purgeAll ? $endpoint . '/purge/all' : $endpoint . '/purge/' . urlencode($url);
        
        // Make HTTP request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $purgeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        if (!empty($secretKey)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $secretKey
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo json_encode(['success' => true, 'message' => __('Cache purged successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => __('Failed to purge cache')]);
        }
    }
}

