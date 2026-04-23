<?php

namespace App\Services\Asset;

use System\Libraries\Render\Minify;

/**
 * AssetsService - Signature-based Asset Build (option-only)
 *
 * Build metadata trong get_option/set_option. Output built files to content/assets/.
 * Cron: đọc options, build, cleanup TTL/LRU từ options.
 *
 * @see application/Config/PROGRESS_PLAN_ASSET_BUILD.md
 * @package App\Services\Asset
 */
class AssetsService
{
    /** Option keys for build metadata (get_option/set_option). Plan §3.1 */
    public const OPTION_KEY_CSS_HEAD = 'asset_build_css_head';
    public const OPTION_KEY_CSS_FOOTER = 'asset_build_css_footer';
    public const OPTION_KEY_JS_HEAD = 'asset_build_js_head';
    public const OPTION_KEY_JS_FOOTER = 'asset_build_js_footer';

    /**
     * Stable JSON encode for deterministic signature hashing.
     * Sort keys recursively before encode.
     */
    public static function stableJsonEncode($data): string
    {
        if (is_array($data)) {
            ksort($data, SORT_STRING);
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = json_decode(self::stableJsonEncode($v), true);
                }
            }
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function contentAssetsPath(): string
    {
        return defined('PATH_CONTENT_ASSETS') ? PATH_CONTENT_ASSETS : ((defined('PATH_CONTENT') ? PATH_CONTENT : self::fallbackPath('content')) . 'assets' . DIRECTORY_SEPARATOR);
    }

    private static function fallbackPath(string $segment): string
    {
        $root = defined('PATH_ROOT') ? rtrim(PATH_ROOT, DIRECTORY_SEPARATOR) : dirname(__DIR__, 3);
        return $root . DIRECTORY_SEPARATOR . $segment . DIRECTORY_SEPARATOR;
    }

    private static function ensureDir(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }
        return @mkdir($dir, 0755, true);
    }

    /**
     * Option key for type + location (plan §3.1).
     */
    public static function getOptionKey(string $type, string $location): string
    {
        if ($type === 'css') {
            return $location === 'footer' ? self::OPTION_KEY_CSS_FOOTER : self::OPTION_KEY_CSS_HEAD;
        }
        return $location === 'footer' ? self::OPTION_KEY_JS_FOOTER : self::OPTION_KEY_JS_HEAD;
    }

    /** Lang cho asset build options: dùng 'all' vì assets giống nhau mọi ngôn ngữ. */
    private const OPTION_LANG = 'all';

    /**
     * Get entries from option (array of { id, data, build, last_seen }).
     * Asset build dùng lang 'all'; get_option tự extract value theo lang rồi trả về.
     */
    public static function getOptionEntries(string $optionKey): array
    {
        if (!function_exists('get_option')) {
            return [];
        }
        $entries = get_option($optionKey, [], self::OPTION_LANG);
        return is_array($entries) ? $entries : [];
    }

    /**
     * Merge/update one entry by id and persist via set_option (plan §3.1).
     * Tránh set_option quá thường xuyên: chỉ gọi khi thêm entry mới hoặc cập nhật build/last_seen.
     */
    public static function setOptionEntry(string $optionKey, array $entry): bool
    {
        if (!function_exists('set_option')) {
            return false;
        }
        $id = $entry['id'] ?? '';
        if ($id === '') {
            return false;
        }
        $entries = self::getOptionEntries($optionKey);
        $found = false;
        foreach ($entries as $i => $e) {
            if (($e['id'] ?? '') === $id) {
                $entries[$i] = array_merge($e, $entry);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $entries[] = $entry;
        }
        return set_option($optionKey, $entries, self::OPTION_LANG);
    }

    /**
     * Get build info for assetsId from option (url or path for output).
     * Returns entry['build'] or null.
     */
    public static function getBuildFromOption(string $optionKey, string $assetsId): ?array
    {
        $entries = self::getOptionEntries($optionKey);
        foreach ($entries as $e) {
            if (($e['id'] ?? '') === $assetsId) {
                $build = $e['build'] ?? null;
                return (is_array($build) && !empty($build)) ? $build : null;
            }
        }
        return null;
    }

    /**
     * Check if asset list would produce empty build (all external + no resolvable).
     */
    public static function wouldProduceEmptyBuild(array $assets): bool
    {
        if (empty($assets)) return true;
        $allExternal = true;
        $hasResolvable = false;
        foreach ($assets as $a) {
            $src = $a['src'] ?? '';
            if ($src === '') continue;
            if (!preg_match('#^(https?:)?//#i', $src)) {
                $allExternal = false;
                $hasResolvable = true;
                break;
            }
            if (!self::isInWhitelistSkip($src) && get_option('self_host_external_assets')) {
                $hasResolvable = true;
                break;
            }
        }
        return $allExternal && !$hasResolvable;
    }

    /**
     * Get path to built asset file (`content/assets/{area}/{filename}`).
     * `$area` thường là `web` hoặc `admin` (theo AssetManager); chuỗi legacy `Frontend`/`Backend` vẫn dùng được làm segment thư mục.
     */
    public static function getBuiltAssetPath(string $area, string $filename): string
    {
        $safeArea = preg_replace('/[^a-zA-Z0-9_-]/', '', $area) ?: 'web';
        $safeFile = basename($filename);
        return self::contentAssetsPath() . $safeArea . DIRECTORY_SEPARATOR . $safeFile;
    }

    /**
     * Ensure content/assets area dir exists
     */
    public static function ensureContentAssetsArea(string $area): bool
    {
        $safeArea = preg_replace('/[^a-zA-Z0-9_-]/', '', $area) ?: 'web';
        $dir = self::contentAssetsPath() . $safeArea . DIRECTORY_SEPARATOR;
        return self::ensureDir($dir);
    }

    /**
     * Run build: option-only. Đọc 4 option keys, entry build null hoặc file mất → resolve & ghi content/assets/.
     */
    public static function build(): array
    {
        $results = ['built' => 0, 'skipped' => 0, 'errors' => []];

        $themePath = defined('APP_THEME_PATH') ? APP_THEME_PATH : ((defined('PATH_THEMES') ? PATH_THEMES : self::fallbackPath('content') . 'themes' . DIRECTORY_SEPARATOR) . (defined('APP_THEME_NAME') ? APP_THEME_NAME : 'default') . DIRECTORY_SEPARATOR);

        $optionKeys = [
            self::OPTION_KEY_CSS_HEAD => ['type' => 'css', 'location' => 'head'],
            self::OPTION_KEY_CSS_FOOTER => ['type' => 'css', 'location' => 'footer'],
            self::OPTION_KEY_JS_HEAD => ['type' => 'js', 'location' => 'head'],
            self::OPTION_KEY_JS_FOOTER => ['type' => 'js', 'location' => 'footer'],
        ];
        foreach ($optionKeys as $optionKey => $meta) {
            $type = $meta['type'];
            $location = $meta['location'];
            $entries = self::getOptionEntries($optionKey);
            foreach ($entries as $entry) {
                    $hash = $entry['id'] ?? '';
                    $data = $entry['data'] ?? [];
                    $build = $entry['build'] ?? null;
                    $area = $entry['area'] ?? 'web';
                    if ($hash === '' || empty($data)) {
                        $results['skipped']++;
                        continue;
                    }
                    $buildInput = ['assets' => $data, 'area' => $area, 'location' => $location, 'type' => $type];
                    $contentOrList = self::resolveAndCombine($buildInput, $themePath, $area, $type);
                    if ($contentOrList === null) {
                        $results['errors'][] = ['key' => $optionKey . ':' . $hash, 'message' => 'Resolve/combine failed'];
                        if (class_exists(\System\Libraries\Logger::class)) {
                            \System\Libraries\Logger::error('AssetsService build error: ' . $optionKey . ' — ' . $hash . ' Resolve/combine failed');
                        }
                        continue;
                    }

                    $isCombined = is_string($contentOrList);

                    // Clear file build cũ trước khi build lại (không skip)
                    if (is_array($build)) {
                        if (!empty($build['path'])) {
                            $oldPath = self::getBuiltAssetPath($area, basename($build['path']));
                            if (is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                        if (!empty($build['files']) && is_array($build['files'])) {
                            foreach ($build['files'] as $f) {
                                $p = $f['path'] ?? '';
                                if ($p === '') continue;
                                $oldPath = self::getBuiltAssetPath($area, basename($p));
                                if (is_file($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                        }
                    }

                    self::ensureContentAssetsArea($area);
                    $ext = $type === 'css' ? 'css' : 'js';

                    if ($isCombined) {
                        $filename = $location . '.' . $hash . '.min.' . $ext;
                        $outPath = self::getBuiltAssetPath($area, $filename);
                        $tmpPath = $outPath . '.tmp.' . getmypid();
                        if (file_put_contents($tmpPath, $contentOrList) === false) {
                            $results['errors'][] = ['key' => $optionKey . ':' . $hash, 'message' => 'Write failed'];
                            continue;
                        }
                        if (!@rename($tmpPath, $outPath)) {
                            @unlink($tmpPath);
                            $results['errors'][] = ['key' => $optionKey . ':' . $hash, 'message' => 'Rename failed'];
                            continue;
                        }
                        $relPath = $area . '/' . $filename;
                        $buildUrl = function_exists('public_url') ? public_url('content/assets/' . $relPath) : ('/content/assets/' . $relPath);
                        self::setOptionEntry($optionKey, [
                            'id' => $hash,
                            'data' => $data,
                            'build' => ['hash' => $hash, 'path' => $relPath, 'url' => $buildUrl, 'time' => time()],
                            'last_seen' => time(),
                        ]);
                    } else {
                        $files = [];
                        foreach ($contentOrList as $i => $content) {
                            $filename = $location . '.' . $hash . '.' . $i . '.min.' . $ext;
                            $outPath = self::getBuiltAssetPath($area, $filename);
                            $tmpPath = $outPath . '.tmp.' . getmypid();
                            if (file_put_contents($tmpPath, $content) === false) {
                                continue;
                            }
                            if (!@rename($tmpPath, $outPath)) {
                                @unlink($tmpPath);
                                continue;
                            }
                            $relPath = $area . '/' . $filename;
                            $buildUrl = function_exists('public_url') ? public_url('content/assets/' . $relPath) : ('/content/assets/' . $relPath);
                            $files[] = ['path' => $relPath, 'url' => $buildUrl];
                        }
                        if (empty($files)) {
                            $results['errors'][] = ['key' => $optionKey . ':' . $hash, 'message' => 'Write failed'];
                            continue;
                        }
                        self::setOptionEntry($optionKey, [
                            'id' => $hash,
                            'data' => $data,
                            'build' => ['hash' => $hash, 'files' => $files, 'time' => time()],
                            'last_seen' => time(),
                        ]);
                    }
                    $results['built']++;
                }
        }

        self::runCleanup();

        return $results;
    }

    /**
     * Resolve asset to local filesystem path or fetch external.
     */
    private static function resolveAssetContent(string $src, string $themePath, string $area, string $type): ?string
    {
        $root = defined('PATH_ROOT') ? rtrim(PATH_ROOT, DIRECTORY_SEPARATOR) : dirname(__DIR__, 3);
        $contentPath = defined('PATH_CONTENT') ? rtrim(PATH_CONTENT, DIRECTORY_SEPARATOR) : $root . DIRECTORY_SEPARATOR . 'content';

        if (preg_match('#^(https?:)?//#i', $src)) {
            $pathPart = parse_url($src, PHP_URL_PATH);
            if ($pathPart && preg_match('#^/?content/(themes|plugins|uploads)/#i', $pathPart)) {
                $rel = ltrim(preg_replace('#^/+#', '', str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pathPart)), DIRECTORY_SEPARATOR);
                $fsPath = $root . DIRECTORY_SEPARATOR . $rel;
                if (is_file($fsPath) && is_readable($fsPath)) {
                    return file_get_contents($fsPath);
                }
            }
            if (get_option('self_host_external_assets') && !self::isInWhitelistSkip($src)) {
                return self::fetchExternal($src);
            }
            return null;
        }

        $fullPath = $themePath . $area . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $src), DIRECTORY_SEPARATOR);
        if (!is_file($fullPath) || !is_readable($fullPath)) {
            return null;
        }
        return file_get_contents($fullPath);
    }

    private static function patternToRegex(string $pattern): string
    {
        $regex = preg_quote(trim($pattern), '#');
        return str_replace(['\*', '\?'], ['.*', '.'], $regex);
    }

    private static function matchesExclude(string $src, $excludeList): bool
    {
        if (empty($excludeList)) return false;
        $lines = is_string($excludeList) ? preg_split('/\r?\n/', $excludeList, -1, PREG_SPLIT_NO_EMPTY) : (array) $excludeList;
        $basename = basename(parse_url($src, PHP_URL_PATH) ?: $src);
        foreach ($lines as $pattern) {
            if (trim($pattern) === '') continue;
            $regex = self::patternToRegex($pattern);
            if (preg_match('#' . $regex . '#i', $src) || preg_match('#' . $regex . '#i', $basename)) {
                return true;
            }
        }
        return false;
    }

    private static function isInWhitelistSkip(string $url): bool
    {
        $whitelist = get_option('self_host_skip_whitelist');
        if (empty($whitelist)) {
            return false;
        }
        $lines = is_string($whitelist) ? preg_split('/\r?\n/', $whitelist, -1, PREG_SPLIT_NO_EMPTY) : (array) $whitelist;
        foreach ($lines as $pattern) {
            if (trim($pattern) === '') continue;
            $regex = self::patternToRegex($pattern);
            if (preg_match('#' . $regex . '#i', $url)) {
                return true;
            }
        }
        return false;
    }

    private static function fetchExternal(string $url): ?string
    {
        $url = preg_replace('#^//#', 'https://', $url);
        $ch = curl_init($url);
        if (!$ch) return null;
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'CMSFullForm-AssetBuild/1.0',
        ]);
        $content = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err && class_exists(\System\Libraries\Logger::class)) {
            \System\Libraries\Logger::warning('AssetsService fetchExternal: ' . $err . ' — ' . $url);
        }
        return ($content !== false && $content !== '') ? $content : null;
    }

    /**
     * Resolve assets: minify theo option, merge chỉ khi bật combine.
     * Return: string (1 file khi combine on), array of strings (nhiều file khi combine off), null khi lỗi.
     */
    private static function resolveAndCombine(array $buildInput, string $themePath, string $area, string $type)
    {
        $assets = $buildInput['assets'] ?? [];
        if (empty($assets)) {
            return null;
        }

        $minifyCss = get_option('minify_css') && get_option('minify_css') !== '0' && get_option('minify_css') !== false;
        $minifyJs = get_option('minify_js') && get_option('minify_js') !== '0' && get_option('minify_js') !== false;
        $combineCss = get_option('combine_css') && get_option('combine_css') !== '0' && get_option('combine_css') !== false;
        $combineJs = get_option('combine_js') && get_option('combine_js') !== '0' && get_option('combine_js') !== false;
        $doCombine = ($type === 'css' && $combineCss) || ($type === 'js' && $combineJs);

        $minifyExcludeKey = $type === 'css' ? 'minify_css_exclude' : 'minify_js_exclude';
        $combineExcludeKey = $type === 'css' ? 'combine_css_exclude' : 'combine_js_exclude';
        $minifyExclude = get_option($minifyExcludeKey);
        $combineExclude = get_option($combineExcludeKey);

        $list = [];
        foreach ($assets as $a) {
            $src = $a['src'] ?? '';
            if ($src === '') {
                continue;
            }
            if (self::matchesExclude($src, $combineExclude)) {
                continue;
            }

            $content = self::resolveAssetContent($src, $themePath, $area, $type);
            if ($content === null || $content === '') {
                continue;
            }

            $shouldMinify = ($type === 'css' && $minifyCss) || ($type === 'js' && $minifyJs);
            if ($shouldMinify && !self::matchesExclude($src, $minifyExclude)) {
                $content = $type === 'css' ? Minify::css($content) : Minify::js($content);
            }

            $list[] = $content;
        }

        if (empty($list)) {
            return null;
        }

        if ($doCombine) {
            return trim(implode("\n", $list));
        }
        return $list;
    }

    /**
     * Cleanup: TTL + LRU từ option entries. Xóa file build và set entry.build = null.
     */
    private static function runCleanup(): void
    {
        if (!function_exists('get_option') || !function_exists('set_option')) {
            return;
        }
        $buildTtlDays = (int) (get_option('build_ttl_days') ?? 30);
        $buildLruMax = (int) (get_option('build_lru_max') ?? 100);
        $safetyWindowSec = 300; // 5 phút — không xóa file vừa build

        $now = time();
        $ttlCutoff = $now - ($buildTtlDays * 86400);
        $safetyCutoff = $now - $safetyWindowSec;

        $candidates = [];
        $optionKeys = [
            self::OPTION_KEY_CSS_HEAD,
            self::OPTION_KEY_CSS_FOOTER,
            self::OPTION_KEY_JS_HEAD,
            self::OPTION_KEY_JS_FOOTER,
        ];
        foreach ($optionKeys as $optionKey) {
            $entries = self::getOptionEntries($optionKey);
            foreach ($entries as $entry) {
                $build = $entry['build'] ?? null;
                if (!is_array($build)) {
                    continue;
                }
                $id = $entry['id'] ?? '';
                $area = $entry['area'] ?? 'web';
                $lastSeen = (int) ($entry['last_seen'] ?? 0);
                $paths = [];
                if (!empty($build['path'])) {
                    $fullPath = self::getBuiltAssetPath($area, basename($build['path']));
                    $paths[] = $fullPath;
                } elseif (!empty($build['files']) && is_array($build['files'])) {
                    foreach ($build['files'] as $f) {
                        $p = $f['path'] ?? '';
                        if ($p !== '') {
                            $paths[] = self::getBuiltAssetPath($area, basename($p));
                        }
                    }
                }
                if (empty($paths)) {
                    continue;
                }
                $allExist = true;
                foreach ($paths as $fullPath) {
                    if (!is_file($fullPath)) {
                        $allExist = false;
                        break;
                    }
                }
                if (!$allExist) {
                    $candidates[] = ['optionKey' => $optionKey, 'id' => $id, 'paths' => $paths, 'last_seen' => $lastSeen];
                    continue;
                }
                if (filemtime($paths[0]) >= $safetyCutoff) {
                    continue;
                }
                $candidates[] = ['optionKey' => $optionKey, 'id' => $id, 'paths' => $paths, 'last_seen' => $lastSeen];
            }
        }

        $toRemove = [];
        foreach ($candidates as $c) {
            if ($c['last_seen'] < $ttlCutoff) {
                $toRemove[] = $c;
            }
        }
        $remaining = array_filter($candidates, function ($c) use ($toRemove) {
            foreach ($toRemove as $r) {
                if (($r['optionKey'] ?? '') === ($c['optionKey'] ?? '') && ($r['id'] ?? '') === ($c['id'] ?? '')) {
                    return false;
                }
            }
            return true;
        });
        usort($remaining, function ($a, $b) {
            return ($a['last_seen'] ?? 0) <=> ($b['last_seen'] ?? 0);
        });
        if (count($remaining) > $buildLruMax) {
            $needRemove = count($remaining) - $buildLruMax;
            $toRemove = array_merge($toRemove, array_slice($remaining, 0, $needRemove));
        }

        foreach ($toRemove as $c) {
            $paths = $c['paths'] ?? (isset($c['path']) ? [$c['path']] : []);
            foreach ($paths as $p) {
                if (is_file($p)) {
                    @unlink($p);
                }
            }
            self::setOptionEntry($c['optionKey'], ['id' => $c['id'], 'build' => null]);
        }
    }
}
