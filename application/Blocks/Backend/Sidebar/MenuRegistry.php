<?php

namespace App\Blocks\Backend\Sidebar;

use App\Blocks\Backend\Sidebar\MenuItem\MenuItemFactory;

/**
 * MenuService - Quản lý toàn bộ menu system
 *
 * Bao gồm:
 * - Load menu từ plugins
 * - Lưu trữ và quản lý menu items
 * - Xử lý permissions và render menu
 */
class MenuService
{
    /** @var array<int, array> Danh sách menu items phẳng */
    protected static $items = [];


    /** @var string|null Lưu URL hiện tại để đánh dấu active */
    protected static $currentUrl = null;
    /** @var array Lưu query hiện tại để đánh dấu active theo params khi cần */
    protected static $currentQuery = [];

    /** @var array|null Lưu permissions của user hiện tại */
    protected static $userPermissions = null;

    /** @var array<string,bool> Cache kết quả permission theo path đã chuẩn hoá */
    protected static $permissionCache = [];

    /** @var bool Đã thử init routerForResolve chưa (tránh include nhiều lần) */
    protected static $routerInitTried = false;


    /**
     * Load menu items từ active plugins
     */
    public static function getPluginMenus(): array
    {
        $menuItems = [];

        // Lấy danh sách plugins active từ Options
        $activePlugins = _json_decode(option('plugins_active', 'all'));

        if (is_array($activePlugins) && !empty($activePlugins)) {
            foreach ($activePlugins as $plugin) {
                $pluginName = $plugin['name'] ?? '';
                if (empty($pluginName)) continue;
                $pluginMenu = config('menu', 'Config', $pluginName);

                if (is_array($pluginMenu) && !empty($pluginMenu)) {
                    foreach ($pluginMenu as $menuItem) {
                        // Tạo MenuItem object
                        $item = MenuItemFactory::create($menuItem);
                        if ($item) {
                            $menuItems[] = $item->toArray();
                        }
                    }
                }
            }
        }

        return $menuItems;
    }

    /**
     * Thay thế toàn bộ menu items bằng mảng mới.
     * @param array<int, array> $items
     */
    public static function setItems(array $items): void
    {
        self::$items = [];
        foreach ($items as $item) {
            self::registerItem($item);
        }
    }

    /**
     * Đăng ký một menu item sử dụng MenuItemFactory.
     * @param array $item
     */
    public static function registerItem(array $item): void
    {
        // Sử dụng MenuItemFactory để tạo và validate item
        $menuItem = MenuItemFactory::create($item);
        if (!$menuItem) {
            return; // Skip item nếu không hợp lệ
        }

        $normalized = $menuItem->toArray();

        // Kiểm tra xem item đã tồn tại chưa (chỉ cho type menu)
        if ($normalized['type'] === 'menu' && isset($normalized['id'])) {
            foreach (self::$items as $idx => $existing) {
                if ($existing['type'] === 'menu' && isset($existing['id']) && $existing['id'] === $normalized['id']) {
                    // Merge thông tin mới vào item cũ
                    self::$items[$idx] = array_merge($existing, array_filter($normalized, function ($v) {
                        return $v !== null && $v !== '';
                    }));
                    return;
                }
            }
        }

        self::$items[] = $normalized;
    }


    /**
     * Kiểm tra user có quyền truy cập URL không.
     * @param string $url URL cần kiểm tra
     * @return bool True nếu có quyền
     */
    protected static function checkUrlPermission(string $url): bool
    {
        // Nếu không có permissions hoặc URL là # thì cho phép
        if (self::$userPermissions === null || $url === '#') {
            return true;
        }
        // Parse URL để lấy path (parser đơn giản, đủ dùng cho permission check)
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $path = trim($path, '/');
        if (empty($path)) {
            return true; // Root path luôn được phép
        }

        // Loại bỏ segment ngôn ngữ nếu có
        $path = self::removeLanguageSegment($path);

        // Dùng cache theo path để tránh resolve lặp lại trong foreach
        if (isset(self::$permissionCache[$path])) {
            return self::$permissionCache[$path];
        }

        $segments = explode('/', $path);
        $resolvedAction = 'index';
        $resolvedController = null;

        // Thử resolve bằng Router hệ thống (đã init 1 lần)
        $routerResolved = self::resolveByRouter($path);
        if (is_array($routerResolved) && count($routerResolved) >= 2) {
            $resolvedController = $routerResolved[0];
            $resolvedAction = $routerResolved[1] ?: 'index';
        }

        // Nếu Router không resolve được thì coi như không có quyền (tránh tự detect sai)
        if ($resolvedController === null) {
            return self::$permissionCache[$path] = false;
        }

        // Kiểm tra permission theo cấu trúc của RolesMiddleware
        foreach (self::$userPermissions as $account_controller => $account_actions) {
            $account_controller = '\\' . $account_controller . 'Controller';
            // So khớp bằng controller class thực tế đã resolve
            if (is_string($resolvedController) && strpos($resolvedController, $account_controller) !== false) {
                if (in_array($resolvedAction, $account_actions)) {
                    return self::$permissionCache[$path] = true;
                }
            }
        }

        return self::$permissionCache[$path] = false;
    }

    /**
     * Loại bỏ segment ngôn ngữ khỏi path nếu có.
     * Xử lý chính xác cả trường hợp có và không có segment ngôn ngữ.
     * @param string $path Path cần xử lý
     * @return string Path đã loại bỏ segment ngôn ngữ
     */
    protected static function removeLanguageSegment(string $path): string
    {
        if (empty($path) || $path === '/') {
            return $path;
        }

        // Lấy danh sách ngôn ngữ được hỗ trợ
        $supportedLanguages = [];
        if (defined('APP_LANGUAGES') && is_array(APP_LANGUAGES)) {
            $supportedLanguages = array_keys(APP_LANGUAGES);
        }

        // Nếu không có ngôn ngữ nào được hỗ trợ thì trả về path gốc
        if (empty($supportedLanguages)) {
            return $path;
        }

        // Loại bỏ dấu slash đầu và cuối để xử lý
        $path = trim($path, '/');
        if (empty($path)) {
            return '/';
        }

        $segments = explode('/', $path);

        // Kiểm tra segment đầu tiên có phải là ngôn ngữ không
        if (!empty($segments[0]) && in_array($segments[0], $supportedLanguages)) {
            // Loại bỏ segment ngôn ngữ đầu tiên
            array_shift($segments);
            $result = implode('/', $segments);
            return empty($result) ? '/' : '/' . $result;
        }

        // Trả về path gốc với dấu slash đầu
        return '/' . $path;
    }

    /**
     * Resolve controller/action bằng Router cục bộ với các rule admin cơ bản.
     * Trả về [controllerClass, action] hoặc null nếu không match.
     */
    protected static function resolveByRouter(string $path)
    {
        try {
            // Dùng Router đã được Bootstrap expose
            $bootstrap = $GLOBALS['application'] ?? null;
            if (!$bootstrap instanceof \System\Core\Bootstrap) {
                return null;
            }
            $router = $bootstrap->getRouter();
            if (!$router instanceof \System\Core\Router) {
                return null;
            }

            // Match bằng GET cho check permission (read-only)
            $matched = $router->match($path, 'GET');
            if ($matched && isset($matched['controller'], $matched['action'])) {
                return [$matched['controller'], $matched['action']];
            }
        } catch (\Throwable $e) {
            // Bỏ qua, trả null để fail safe
        }
        return null;
    }

    /**
     * Tự động detect và set permissions của user hiện tại từ global $me_info.
     * Method này sẽ tự động gọi khi cần thiết trong getMenus().
     */
    protected static function autoDetectUserPermissions(): void
    {
        // Nếu đã set rồi thì không cần detect lại
        if (self::$userPermissions !== null) {
            return;
        }

        // Sử dụng global $me_info từ BackendController
        global $me_info;

        if (!empty($me_info) && !empty($me_info['id'])) {
            $permissions = user_permissions($me_info['role'], $me_info['permissions']);
            self::$userPermissions = $permissions;
        } else {
            self::$userPermissions = null; // Disable permission check
        }
    }

    /**
     * Tự động set current URL từ $_SERVER['REQUEST_URI'].
     * Method này sẽ tự động gọi khi cần thiết trong getMenus().
     */
    protected static function autoSetCurrentUrl(): void
    {
        // Nếu đã set rồi thì không cần set lại
        if (self::$currentUrl !== null) {
            return;
        }

        // Tự động lấy URL hiện tại từ $_SERVER
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        self::$currentUrl = self::normalizeUrl($currentUrl);
        // Lưu current query để so khớp tham số
        $queryString = parse_url($currentUrl, PHP_URL_QUERY) ?: '';
        $queryArr = [];
        if ($queryString !== '') {
            parse_str($queryString, $queryArr);
        }
        self::$currentQuery = is_array($queryArr) ? $queryArr : [];
    }

    /**
     * Trả về danh sách items đã sort theo 'order' và đánh dấu active.
     * Tự động xử lý permissions, URL resolver và current URL bên trong.
     * @return array<int, array>
     */
    public static function getMenus(): array
    {
        // Tự động detect và set permissions của user hiện tại
        self::autoDetectUserPermissions();

        // Tự động set current URL để đánh dấu active
        self::autoSetCurrentUrl();

        $items = self::$items;

        // Filter items theo permissions
        $items = self::filterItemsByPermissions($items);

        // Sort items theo order
        usort($items, function ($a, $b) {
            return ($a['order'] ?? 9999) <=> ($b['order'] ?? 9999);
        });

        // Ẩn các label không có item menu nào bên dưới (sau khi đã filter quyền)
        $items = self::pruneOrphanLabels($items);

        // Đánh dấu active theo currentUrl (nếu có)
        if (self::$currentUrl) {
            foreach ($items as &$item) {
                $item['active'] = false;
                if ($item['type'] === 'menu') {
                    // Kiểm tra children trước
                    if (!empty($item['children']) && is_array($item['children'])) {
                        $anyChildActive = self::markActiveForItems($item['children'], self::$currentUrl);
                        if ($anyChildActive) {
                            $item['active'] = true;
                            $item['expanded'] = true; // Tự động mở rộng nếu có child active
                        }
                    }
                    // Kiểm tra chính item
                    if (isset($item['href']) && $item['href'] !== '#') {
                        if (self::linkMatchesCurrent($item['href'])) {
                            $item['active'] = true;
                        }
                    }
                }
            }
            unset($item);
        }

        return $items;
    }

    /**
     * Loại bỏ các label "mồ côi": label mà từ vị trí của nó đến trước label kế tiếp (hoặc cuối danh sách)
     * không có item nào type = 'menu' thì sẽ bị ẩn.
     * Thao tác này cần thực hiện sau khi filter quyền và sort theo order.
     *
     * @param array<int,array> $items
     * @return array<int,array>
     */
    protected static function pruneOrphanLabels(array $items): array
    {
        $result = [];
        $count = count($items);
        for ($i = 0; $i < $count; $i++) {
            $item = $items[$i];
            if (($item['type'] ?? 'menu') !== 'label') {
                $result[] = $item;
                continue;
            }

            // Tìm xem từ $i+1 tới trước label kế tiếp có item menu nào không
            $hasMenu = false;
            for ($j = $i + 1; $j < $count; $j++) {
                $next = $items[$j];
                $nextType = $next['type'] ?? 'menu';
                if ($nextType === 'label') {
                    break; // gặp label tiếp theo => dừng kiểm tra
                }
                if ($nextType === 'menu') {
                    $hasMenu = true;
                    break;
                }
            }

            if ($hasMenu) {
                $result[] = $item; // giữ label vì có ít nhất một menu phía dưới
            } else {
                // bỏ label này (không push vào $result)
            }
        }
        return $result;
    }

    /**
     * Filter items theo permissions (đệ quy cho children).
     * @param array $items Mảng items cần filter
     * @return array Mảng items đã được filter
     */
    protected static function filterItemsByPermissions(array $items): array
    {
        $filteredItems = [];

        foreach ($items as $item) {
            if (($item['type'] ?? 'menu') === 'menu') {
                // Kiểm tra permission cho menu item
                if (isset($item['permission_check']) && $item['permission_check'] && isset($item['href'])) {
                    if (!self::checkUrlPermission($item['href'])) {
                        continue; // Skip item nếu không có quyền
                    }
                }
                // Filter children nếu có
                if (!empty($item['children']) && is_array($item['children'])) {
                    $item['children'] = self::filterItemsByPermissions($item['children']);

                    // Nếu không còn children nào sau khi filter, có thể skip parent
                    if (empty($item['children']) && $item['href'] === '#') {
                        continue;
                    }
                }
            }

            $filteredItems[] = $item;
        }
        return $filteredItems;
    }

    /**
     * Đánh dấu active cho danh sách items (đệ quy children). Trả về true nếu có item active trong danh sách.
     * @param array<int, array> &$items
     */
    protected static function markActiveForItems(array &$items, string $currentUrl): bool
    {
        $found = false;
        foreach ($items as &$item) {
            $item['active'] = false;
            // Kiểm tra children trước
            if (!empty($item['children']) && is_array($item['children'])) {
                if (self::markActiveForItems($item['children'], $currentUrl)) {
                    $item['active'] = true;
                    $found = true;
                }
            }
            // Kiểm tra chính item
            if (isset($item['href']) && $item['href'] !== '#') {
                if (self::linkMatchesCurrent($item['href'], $currentUrl, self::$currentQuery)) {
                    $item['active'] = true;
                    $found = true;
                }
            }
        }
        unset($item);
        return $found;
    }

    /**
     * Chuẩn hoá URL (cắt dấu slash cuối, bỏ query/fragment để so sánh đơn giản).
     * Xử lý chính xác cả trường hợp có và không có segment ngôn ngữ.
     */
    protected static function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';

        // Chuẩn hoá path: loại bỏ slash cuối (trừ root)
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        // Loại bỏ segment ngôn ngữ nếu có
        $path = self::removeLanguageSegment($path);

        return $path === '' ? '/' : $path;
    }

    /**
     * Quy tắc match đơn giản:
     * - Chính xác hoặc prefix match theo path
     */
    protected static function urlMatches(string $itemUrl, string $currentUrl): bool
    {
        if ($itemUrl === $currentUrl) return true;
        // Treat "/x" and "/x/index" as equivalent
        $a = rtrim($itemUrl, '/');
        $b = rtrim($currentUrl, '/');
        if (substr($a, -6) === '/index') $a = substr($a, 0, -6);
        if (substr($b, -6) === '/index') $b = substr($b, 0, -6);
        if ($a === $b) return true;
        // Prefix match theo segment
        if (strpos($currentUrl, $itemUrl) === 0) {
            if ($itemUrl === '/') return true;
            $nextChar = substr($currentUrl, strlen($itemUrl), 1);
            return $nextChar === '' || $nextChar === '/';
        }
        return false;
    }

    /**
     * So khớp link với URL hiện tại, gồm cả query cho một số route đặc biệt.
     * Xử lý chính xác cả trường hợp có và không có segment ngôn ngữ.
     */
    protected static function linkMatchesCurrent(string $href, ?string $currentPath = null, ?array $currentQuery = null): bool
    {
        if (self::isExternalLink($href)) return false;
        $currentPath = $currentPath ?? (self::$currentUrl ?? '/');
        $currentQuery = $currentQuery ?? self::$currentQuery;

        $parsed = parse_url($href);
        $hrefPath = self::normalizeUrl($href);
        $hrefQuery = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $hrefQuery);
        }

        // Chuẩn hoá currentPath để loại bỏ segment ngôn ngữ
        $currentPathNormalized = self::removeLanguageSegment($currentPath);

        // Trước tiên so khớp path như cũ
        if (!self::urlMatches($hrefPath, $currentPathNormalized)) {
            return false;
        }

        // Xử lý đặc biệt cho các trang posts
        if (strpos($hrefPath, '/admin/posts') !== false) {
            // Kiểm tra type parameter
            if (array_key_exists('type', $hrefQuery)) {
                if (($currentQuery['type'] ?? null) !== $hrefQuery['type']) return false;
            }

            // Kiểm tra post_lang parameter - xử lý cả trường hợp có và không có ngôn ngữ
            if (array_key_exists('post_lang', $hrefQuery)) {
                $hrefPostLang = $hrefQuery['post_lang'];
                $currentPostLang = $currentQuery['post_lang'] ?? null;

                // Nếu href có post_lang thì current cũng phải có và giống nhau
                if ($currentPostLang !== $hrefPostLang) return false;
            } else {
                // Nếu href không có post_lang thì current cũng không được có
                if (array_key_exists('post_lang', $currentQuery)) return false;
            }
        }

        // Xử lý đặc biệt cho các trang terms
        if (strpos($hrefPath, '/admin/terms') !== false) {
            // Kiểm tra posttype parameter
            if (array_key_exists('posttype', $hrefQuery)) {
                if (($currentQuery['posttype'] ?? null) !== $hrefQuery['posttype']) return false;
            }

            // Kiểm tra type parameter
            if (array_key_exists('type', $hrefQuery)) {
                if (($currentQuery['type'] ?? null) !== $hrefQuery['type']) return false;
            }
        }

        return true;
    }

    /**
     * Kiểm tra liên kết có thuộc domain hiện tại không (external nếu host khác).
     */
    protected static function isExternalLink(string $href): bool
    {
        $host = parse_url($href, PHP_URL_HOST);
        if (!$host) return false; // relative URL (không external)
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        return $currentHost !== '' && strcasecmp($host, $currentHost) !== 0;
    }
}
