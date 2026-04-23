<?php

namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use System\Libraries\Render\View;
use System\Libraries\Session;
// use System\Libraries\Events; // OLD - Deprecated
use App\Libraries\Fastlang as Flang;
use Exception;
use ZipArchive;


class LibrariesController extends BackendController
{
    protected $managerType;
    protected $managerDir;
    protected $activeKey;
    protected $viewPath;

    public function __construct()
    {
        if (!defined('APP_DEVELOPMENT') || !APP_DEVELOPMENT) {
            //Show notice user have disable backup at config file
            Session::flash('error', Flang::__("You have Disable for Security, for Enable, config 'development' to True at application/Config/Config.php"));
            redirect(admin_url('home'));
        }

        //echo storage_set('wc_settings', 'This is Setting of Default Languages: '.APP_LANG_DF, 'plugins');
        //echo storage_set('wc_settings', 'Đây là setting khi đổi ngôn ngữ sang: '.APP_LANG, 'plugins', APP_LANG);
        //echo storage_get('wc_settings', 'plugins', APP_LANG);die;

        parent::__construct();
        View::addJs('libraries-notification', 'js/notification.js', [], null, false, false, false, false);
        View::inlineJs('libraries-fastnotice-init', "
document.addEventListener('DOMContentLoaded', function () {
    window.fastNotice = new FastNotice({
        position: 'top-center',    // Vị trí mặc định
        duration: 3000,              // Thời gian hiển thị (ms)
        maxNotifications: 3          // Số notification tối đa
    });
});
        ", [], null, false);
    }

    protected function initializeManager($type, $dir, $activeKey, $viewPath)
    {
        $this->managerType = $type;
        $this->managerDir = $dir;
        $this->activeKey = $activeKey;
        $this->viewPath = $viewPath;
    }

    // Route handlers for /admin/libraries/plugins and /admin/libraries/themes
    public function plugins()
    {
        $this->initializeManager(
            'plugins',
            rtrim(PATH_PLUGINS, '/'),
            'plugins_active',
            'libraries_index'
        );
        Flang::load('Global', APP_LANG);
        Flang::load('Backend/Plugins', APP_LANG);
        return $this->index();
    }

    public function themes()
    {
        $this->initializeManager(
            'themes',
            rtrim(PATH_THEMES, '/'),
            'themes_active',
            'libraries_index'
        );
        Flang::load('Global', APP_LANG);
        Flang::load('Backend/Themes', APP_LANG);
        return $this->index();
    }

    public function index()
    {
        $installedItems = $this->scanDirectory();

        if ($this->managerType === 'themes') {
            $validSlugs = [];
            foreach ($installedItems as $row) {
                $s = strtolower(trim((string) ($row['slug'] ?? '')));
                if ($s !== '') {
                    $validSlugs[$s] = true;
                }
            }
            $activeWeb = $this->filterActiveNamesToExistingSlugs(
                $this->getActiveNamesForOptionKey('themes_active'),
                $validSlugs
            );
            $activeAdmin = $this->filterActiveNamesToExistingSlugs(
                $this->getActiveNamesForOptionKey('themes_backend'),
                $validSlugs
            );
            $items = $this->mergeItemData($installedItems, $activeWeb, $activeAdmin);
            $activeItemsMerged = array_values(array_unique(array_merge($activeWeb, $activeAdmin)));
        } else {
            $activeFromOption = $this->getActiveNamesForOptionKey($this->activeKey);
            $validSlugs = [];
            foreach ($installedItems as $row) {
                $s = strtolower(trim((string) ($row['slug'] ?? '')));
                if ($s !== '') {
                    $validSlugs[$s] = true;
                }
            }
            $activeItems = $this->filterActiveNamesToExistingSlugs($activeFromOption, $validSlugs);
            $items = $this->mergeItemData($installedItems, $activeItems, null);
            $activeItemsMerged = $activeItems;
        }

        $stats = $this->calculateStats($items);

        $this->data('title', __('title ' . $this->managerType));
        $this->data($this->managerType, $items);
        $this->data('stats', $stats);
        $this->data('activeItems', $activeItemsMerged);
        $this->data('managerType', $this->managerType);

        echo View::make($this->viewPath, $this->data)->render();
    }

    /**
     * @param array<string,bool> $validSlugs keys = lowercase folder name => true
     * @return string[]
     */
    private function filterActiveNamesToExistingSlugs(array $names, array $validSlugs): array
    {
        $out = [];
        foreach ($names as $name) {
            $key = strtolower(trim((string) $name));
            if ($key !== '' && isset($validSlugs[$key])) {
                $out[] = $name;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @return string[]
     */
    private function getActiveNamesForOptionKey(string $optionKey): array
    {
        $activeItems = option($optionKey, 'all', false);
        if ($activeItems && is_string($activeItems)) {
            $activeItems = json_decode($activeItems, true);
        }
        if (!$activeItems || !is_array($activeItems)) {
            return [];
        }
        $activeNames = [];
        foreach ($activeItems as $item) {
            if (is_array($item) && isset($item['name'])) {
                $activeNames[] = $item['name'];
            }
        }

        return $activeNames;
    }

    /**
     * Theme: ưu tiên config/theme.php (Render mới), sau đó Config/theme.php, cuối cùng Config/Config.php (legacy).
     * Plugin: giữ Config/Config.php.
     *
     * @return string|null Đường dẫn file config hợp lệ
     */
    private function resolvePackageConfigPath(string $dir): ?string
    {
        if ($this->managerType === 'themes') {
            $candidates = [
                $dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'theme.php',
                $dir . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'theme.php',
                $dir . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Config.php',
            ];
        } else {
            $candidates = [
                $dir . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Config.php',
            ];
        }
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * themes_active = giao diện web, themes_backend = giao diện admin.
     */
    private function getThemeOptionKeyForSlug(string $itemSlug): ?string
    {
        $dir = rtrim((string) $this->managerDir, '/\\') . DIRECTORY_SEPARATOR . $itemSlug;
        if (!is_dir($dir)) {
            return null;
        }
        $configFile = $this->resolvePackageConfigPath($dir);
        if ($configFile === null) {
            return null;
        }
        $config = include $configFile;
        if (!is_array($config)) {
            return 'themes_active';
        }
        $rootType = isset($config['type']) ? strtolower(trim((string) $config['type'])) : 'web';

        return ($rootType === 'admin') ? 'themes_backend' : 'themes_active';
    }

    private function scanDirectory()
    {
        $items = [];
        if (!is_dir($this->managerDir)) {
            return $items;
        }
        $directories = glob($this->managerDir . '/*', GLOB_ONLYDIR);
        $metaKey = $this->managerType === 'plugins' ? 'plugin' : 'theme';

        foreach ($directories as $dir) {
            $itemName = basename($dir);
            $configFile = $this->resolvePackageConfigPath($dir);
            if ($configFile === null) {
                continue;
            }
            $config = include $configFile;
            if (!is_array($config) || !isset($config[$metaKey]) || !is_array($config[$metaKey])) {
                continue;
            }
            $itemData = $config[$metaKey];
            if ($this->managerType === 'themes') {
                $rootType = isset($config['type']) ? strtolower(trim((string) $config['type'])) : 'web';
                $itemData['package_type'] = ($rootType === 'admin') ? 'admin' : 'web';
            }
            // Slug luôn là tên thư mục thật (kích hoạt / xóa / DB option dùng folder name)
            $itemData['directory'] = $itemName;
            $itemData['slug'] = $itemName;
            // Tên hiển thị: lấy từ config; chỉ fallback folder khi không khai báo
            $displayName = isset($itemData['name']) ? trim((string) $itemData['name']) : '';
            if ($displayName === '') {
                $displayName = $itemName;
            }
            $itemData['name'] = $displayName;
            $itemData['status'] = $itemData['status'] ?? false;
            $itemData['downloads'] = $itemData['downloads'] ?? 0;
            $itemData['category'] = $itemData['category'] ?? 'General';
            $itemData['rating'] = $itemData['rating'] ?? 0;
            $itemData['description'] = $itemData['description'] ?? 'No description available';
            // Ensure buttons is an array (only show if not empty)
            $itemData['buttons'] = isset($itemData['buttons']) && is_array($itemData['buttons']) && !empty($itemData['buttons']) ? $itemData['buttons'] : [];
            if (is_string($itemData['category'])) {
                $categories = array_map('trim', explode(',', $itemData['category']));
                $itemData['categories'] = $categories;
                $itemData['category'] = $categories[0];
            } else {
                $itemData['categories'] = [$itemData['category']];
            }
            $items[] = $itemData;
        }

        return $items;
    }

    /**
     * @param string[] $activeWebOrPlugins themes: active web slugs from option; plugins: active plugin names
     * @param string[]|null $activeAdmin themes only: active admin slugs from themes_backend
     */
    private function mergeItemData($installedItems, $activeWebOrPlugins, $activeAdmin = null)
    {
        $merged = [];

        if ($this->managerType === 'themes' && is_array($activeAdmin)) {
            $activeWebLower = array_map(static function ($name) {
                return strtolower(trim((string) $name));
            }, $activeWebOrPlugins);
            $activeAdminLower = array_map(static function ($name) {
                return strtolower(trim((string) $name));
            }, $activeAdmin);

            foreach ($installedItems as $item) {
                $itemSlug = strtolower(trim($item['slug'] ?? ''));
                $pkg = $item['package_type'] ?? 'web';
                $isActive = false;
                if ($pkg === 'admin') {
                    if ($itemSlug !== '' && in_array($itemSlug, $activeAdminLower, true)) {
                        $isActive = true;
                    }
                } else {
                    if ($itemSlug !== '' && in_array($itemSlug, $activeWebLower, true)) {
                        $isActive = true;
                    }
                }
                $item['is_active'] = $isActive;
                $item['status_text'] = $item['is_active'] ? 'Active' : 'Inactive';
                $item['status_class'] = $item['is_active'] ? 'success' : 'warning';
                $item['actions'] = [
                    'activate' => !$item['is_active'],
                    'deactivate' => $item['is_active'],
                    'settings' => true,
                    'delete' => true,
                ];
                $merged[] = $item;
            }
        } else {
            $activeItemsLower = array_map(static function ($name) {
                return strtolower(trim((string) $name));
            }, $activeWebOrPlugins);

            foreach ($installedItems as $item) {
                $itemSlug = strtolower(trim($item['slug'] ?? ''));
                $itemName = strtolower(trim($item['name'] ?? ''));
                $isActive = false;
                if (($itemSlug !== '' && in_array($itemSlug, $activeItemsLower, true))
                    || ($itemName !== '' && in_array($itemName, $activeItemsLower, true))) {
                    $isActive = true;
                }
                $item['is_active'] = $isActive ? true : false;
                $item['status_text'] = $item['is_active'] ? 'Active' : 'Inactive';
                $item['status_class'] = $item['is_active'] ? 'success' : 'warning';
                $item['actions'] = [
                    'activate' => !$item['is_active'],
                    'deactivate' => $item['is_active'],
                    'settings' => true,
                    'delete' => true,
                ];
                $merged[] = $item;
            }
        }

        usort($merged, function ($a, $b) {
            if ($a['is_active'] && !$b['is_active']) {
                return -1;
            }
            if (!$a['is_active'] && $b['is_active']) {
                return 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $merged;
    }

    private function calculateStats($items)
    {
        $total = count($items);
        $active = 0;
        $inactive = 0;
        foreach ($items as $item) {
            if ($item['is_active']) $active++;
            else $inactive++;
        }
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'updates' => 0,
            'store' => 156,
        ];
    }

    public function action()
    {
        if (!is_ajax()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        // Detect and set manager by type
        $reqType = $input['type'] ?? '';
        if ($reqType === 'themes' || isset($input['theme'])) {
            $this->initializeManager('themes', rtrim(PATH_THEMES, '/'), 'themes_active', 'Backend/libraries_index');
        } else if ($reqType === 'plugins' || isset($input['plugin'])) {
            $input['plugin'] = $input['plugin'];
            $this->initializeManager('plugins', rtrim(PATH_PLUGINS, '/'), 'plugins_active', 'Backend/libraries_index');
        }
        $action = $input['action'] ?? '';
        $itemSlug = $input['item'] ?? $input['theme'] ?? $input['plugin'] ?? $input[$this->managerType] ?? '';
        if (empty($action) || empty($itemSlug)) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }
        try {
            switch ($action) {
                case 'activate':
                    $result = $this->activateItem($itemSlug);
                    break;
                case 'deactivate':
                    $result = $this->deactivateItem($itemSlug);
                    break;
                case 'delete':
                    $result = $this->deleteItem($itemSlug);
                    break;
                default:
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid action']);
                    return;
            }
            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function activateItem($itemSlug)
    {
        $itemDir = rtrim((string) $this->managerDir, '/\\') . '/' . $itemSlug;
        if (!is_dir($itemDir)) {
            return ['success' => false, 'message' => __($this->managerType . ' not found')];
        }

        if ($this->managerType === 'themes') {
            $optionKey = $this->getThemeOptionKeyForSlug($itemSlug);
            if ($optionKey === null) {
                return ['success' => false, 'message' => __('theme config not found')];
            }
            $activeItems = option($optionKey, 'all', false);
            if ($activeItems && is_string($activeItems)) {
                $activeItems = json_decode($activeItems, true);
            }
            if (!$activeItems || !is_array($activeItems)) {
                $activeItems = [];
            }
            $slugLower = strtolower(trim($itemSlug));
            foreach ($activeItems as $item) {
                if (is_array($item) && isset($item['name']) && strtolower(trim((string) $item['name'])) === $slugLower) {
                    return ['success' => false, 'message' => __($this->managerType . ' already active')];
                }
            }
            $activeItems = [];
            $activeItems[] = ['name' => $itemSlug];
            set_option($optionKey, array_values($activeItems), 'all');
            do_action('theme_activated', $itemSlug, [
                'theme' => $itemSlug,
                'type' => 'themes',
                'option_key' => $optionKey,
                'scope' => $optionKey === 'themes_backend' ? 'admin' : 'web',
            ]);

            return ['success' => true, 'message' => __($this->managerType . ' activated successfully')];
        }

        $activeItems = option($this->activeKey, APP_LANG, false);
        if ($activeItems && is_string($activeItems)) {
            $activeItems = json_decode($activeItems, true);
        }
        if (!$activeItems || !is_array($activeItems)) {
            $activeItems = [];
        }

        foreach ($activeItems as $item) {
            if (is_array($item) && isset($item['name'])) {
                $existingName = strtolower(trim($item['name']));
                if ($existingName === strtolower(trim($itemSlug))) {
                    return ['success' => false, 'message' => __($this->managerType . ' already active')];
                }
            }
        }

        $activeItems[] = ['name' => $itemSlug];
        set_option($this->activeKey, array_values($activeItems), 'all');

        if ($this->managerType === 'plugins') {
            \System\Core\PluginLoader::init(true);
            do_action('plugin_activated', $itemSlug, [
                'plugin' => $itemSlug,
                'type' => 'plugins',
            ]);
        }

        return ['success' => true, 'message' => __($this->managerType . ' activated successfully')];
    }

    private function deactivateItem($itemSlug)
    {
        if ($this->managerType === 'themes') {
            return [
                'success' => false,
                'message' => __('themes cannot deactivate switch only'),
            ];
        }

        $activeItems = option($this->activeKey, APP_LANG, false);
        if ($activeItems && is_string($activeItems)) {
            $activeItems = json_decode($activeItems, true);
        }
        if (!$activeItems || !is_array($activeItems)) {
            $activeItems = [];
        }
        $exists = false;
        foreach ($activeItems as $item) {
            if (is_array($item) && isset($item['name']) && $item['name'] === $itemSlug) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            return ['success' => false, 'message' => __($this->managerType . ' not active')];
        }
        $activeItems = array_filter($activeItems, function ($item) use ($itemSlug) {
            return !is_array($item) || !isset($item['name']) || $item['name'] !== $itemSlug;
        });
        set_option($this->activeKey, array_values($activeItems), 'all');

        if ($this->managerType === 'plugins') {
            do_action('plugin_deactivated', $itemSlug, [
                'plugin' => $itemSlug,
                'type' => 'plugins',
            ]);
        }

        return ['success' => true, 'message' => __($this->managerType . ' deactivated successfully')];
    }

    /**
     * Gỡ slug khỏi themes_active / themes_backend (khi xóa thư mục theme). Không dùng cho “vô hiệu hóa” thủ công.
     */
    private function removeThemeSlugFromActiveOption(string $itemSlug): void
    {
        $optionKey = $this->getThemeOptionKeyForSlug($itemSlug);
        if ($optionKey === null) {
            return;
        }
        $activeItems = option($optionKey, 'all', false);
        if ($activeItems && is_string($activeItems)) {
            $activeItems = json_decode($activeItems, true);
        }
        if (!$activeItems || !is_array($activeItems)) {
            return;
        }
        $slugLower = strtolower(trim($itemSlug));
        $activeItems = array_filter($activeItems, function ($item) use ($slugLower) {
            if (!is_array($item) || !isset($item['name'])) {
                return true;
            }

            return strtolower(trim((string) $item['name'])) !== $slugLower;
        });
        set_option($optionKey, array_values($activeItems), 'all');
        do_action('theme_deactivated', $itemSlug, [
            'theme' => $itemSlug,
            'type' => 'themes',
            'option_key' => $optionKey,
            'scope' => $optionKey === 'themes_backend' ? 'admin' : 'web',
            'reason' => 'deleted',
        ]);
    }

    private function deleteItem($itemSlug)
    {
        $itemDir = $this->managerDir . '/' . $itemSlug;
        if (!is_dir($itemDir)) {
            return ['success' => false, 'message' => __($this->managerType . ' not found')];
        }
        if ($this->managerType === 'themes') {
            $this->removeThemeSlugFromActiveOption($itemSlug);
        } else {
            $this->deactivateItem($itemSlug);
        }
        $this->rrmdir($itemDir);

        return ['success' => true, 'message' => __($this->managerType . ' deleted successfully')];
    }

    public function uploadWithOverwrite()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['item_files'])) {
            $this->jsonResponse(['success' => false, 'message' => __('no file uploaded')]);
        }
        // Detect and set manager by type (POST form)
        $reqType = S_POST('type') ?? '';
        if ($reqType === 'themes') {
            $this->initializeManager('themes', rtrim(PATH_THEMES, '/'), 'themes_active', 'Backend/libraries_index');
        } else if ($reqType === 'plugins') {
            $this->initializeManager('plugins', rtrim(PATH_PLUGINS, '/'), 'plugins_active', 'Backend/libraries_index');
        }
        $files = $_FILES['item_files'];
        $errors = [];
        $successCount = 0;
        $overwriteItems = [];
        if (HAS_POST('overwrite_items')) {
            $overwriteItems = json_decode(S_POST('overwrite_items'), true) ?: [];
        }
        $existingItems = $this->scanDirectory();
        $existingSlugs = array_map(function ($item) {
            return $item['slug'];
        }, $existingItems);
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        for ($i = 0; $i < $fileCount; $i++) {
            $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "$name: " . __('upload error');
                continue;
            }
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'zip') {
                $errors[] = "$name: " . __('only zip allowed');
                continue;
            }
            $zip = new ZipArchive();
            if ($zip->open($tmpName) === TRUE) {
                $firstEntry = $zip->getNameIndex(0);
                $itemFolder = explode('/', $firstEntry)[0];
                $itemSlug = $itemFolder;
                $extractPath = $this->managerDir . '/' . $itemFolder;
                if (in_array($itemSlug, $existingSlugs)) {
                    if (!in_array($itemSlug, $overwriteItems)) {
                        $errors[] = "$name: " . __($this->managerType . ' exists') . " '$itemFolder'";
                        $zip->close();
                        continue;
                    }
                }
                if (is_dir($extractPath)) {
                    $this->rrmdir($extractPath);
                }
                if (!$zip->extractTo($this->managerDir)) {
                    $errors[] = "$name: " . __('failed extract zip');
                    $zip->close();
                    continue;
                }
                $zip->close();
                $configPath = $this->resolvePackageConfigPath($extractPath);
                if ($configPath === null) {
                    $this->rrmdir($extractPath);
                    $errors[] = "$name: " . __($this->managerType . ' invalid missing config');
                    continue;
                }
                $successCount++;
            } else {
                $errors[] = "$name: " . __('failed open zip');
            }
        }
        if ($successCount > 0) {
            $msg = __($this->managerType . ' upload success');
            if ($errors) $msg .= ' ' . __($this->managerType . ' upload errors') . ': ' . implode(' ', $errors);
            $this->jsonResponse(['success' => true, 'message' => $msg]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
        }
    }

    private function rrmdir($dir)
    {
        if (!is_dir($dir)) return;
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object == "." || $object == "..") continue;
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}


