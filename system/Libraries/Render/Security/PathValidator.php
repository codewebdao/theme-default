<?php

namespace System\Libraries\Render\Security;

/**
 * Path Validator
 * 
 * Validates and sanitizes template paths to prevent:
 * - Path traversal attacks (../)
 * - Null byte injection (\0)
 * - Absolute path injection
 * - LFI/RCE via malicious paths
 * 
 * @package System\Libraries\Render\Security
 * @since 1.0.0
 */
class PathValidator
{
    /**
     * Allowed base directories (will be set dynamically)
     * @var array
     */
    private static $allowedBaseDirs = [];

    /**
     * Initialize allowed base directories (không dùng realpath – tránh I/O, cache 1 lần/request).
     */
    private static function init()
    {
        if (empty(self::$allowedBaseDirs)) {
            $ds = DIRECTORY_SEPARATOR;
            $bases = [
                rtrim(str_replace('\\', '/', PATH_THEMES), '/'),
                rtrim(str_replace('\\', '/', PATH_PLUGINS), '/'),
            ];
            self::$allowedBaseDirs = array_values(array_filter($bases, function ($p) {
                return $p !== '';
            }));
        }
    }

    /**
     * Validate template name (before parsing)
     * 
     * ✅ SECURITY: Prevents path traversal, null bytes, absolute paths, URLs
     * 
     * @param string $template Template name
     * @return bool
     */
    public static function isValidTemplateName($template)
    {
        if (empty($template) || !is_string($template)) {
            return false;
        }

        // Max length (prevent DoS)
        if (strlen($template) > 500) {
            return false;
        }

        // Check for dangerous characters
        $dangerous = [
            '..',      // Path traversal
            "\0",      // Null byte
            chr(0),    // Null byte
            '\\',      // Windows path (force /)
        ];

        foreach ($dangerous as $char) {
            if (strpos($template, $char) !== false) {
                return false;
            }
        }

        // Reject absolute paths
        if ($template[0] === '/' || preg_match('/^[A-Z]:/i', $template)) {
            return false;
        }

        // Reject URLs
        if (preg_match('#^(https?:)?//#i', $template)) {
            return false;
        }

        // Reject file:// protocol
        if (strpos($template, 'file://') === 0) {
            return false;
        }

        // Allow only safe characters: A-Z, a-z, 0-9, /, -, _, @, :, .
        if (!preg_match('/^[@A-Za-z0-9\/_:.-]+$/', $template)) {
            return false;
        }

        return true;
    }

    /**
     * Validate area / scope segment (prefix theme hoặc segment trong @Plugin:Area).
     *
     * Bucket render: web, admin. Chuỗi khác (vd. common/auth) dùng làm prefix thư mục con trong theme.
     *
     * ✅ SECURITY: Prevent path traversal in area
     *
     * @param string $area Area path
     * @return bool
     */
    public static function isValidArea($area)
    {
        if (empty($area)) {
            return true; // Empty area is OK
        }

        if (!is_string($area)) {
            return false;
        }

        // Max length
        if (strlen($area) > 100) {
            return false;
        }

        // Check for dangerous patterns
        if (strpos($area, '..') !== false ||
            strpos($area, "\0") !== false ||
            strpos($area, chr(0)) !== false ||
            strpos($area, '\\') !== false) {
            return false;
        }

        // Allow: A-Z, a-z, 0-9, /, -, _
        if (!preg_match('/^[A-Za-z0-9\/_-]+$/', $area)) {
            return false;
        }

        return true;
    }

    /**
     * Validate template path segments (after parsing)
     * 
     * ✅ SECURITY: Prevent path traversal in template path
     * 
     * @param string $templatePath Template path
     * @return bool
     */
    public static function isValidTemplatePath($templatePath)
    {
        if (empty($templatePath) || !is_string($templatePath)) {
            return false;
        }

        // Max length
        if (strlen($templatePath) > 500) {
            return false;
        }

        // Check for dangerous patterns
        if (strpos($templatePath, '..') !== false ||
            strpos($templatePath, "\0") !== false ||
            strpos($templatePath, chr(0)) !== false ||
            strpos($templatePath, '\\') !== false) {
            return false;
        }

        // Allow: A-Z, a-z, 0-9, /, -, _, .
        if (!preg_match('/^[A-Za-z0-9\/_.-]+$/', $templatePath)) {
            return false;
        }

        return true;
    }

    /**
     * Validate plugin/block name
     * 
     * @param string $name Plugin or block name
     * @return bool
     */
    public static function isValidComponentName($name)
    {
        if (empty($name) || !is_string($name)) {
            return false;
        }

        // Max length
        if (strlen($name) > 100) {
            return false;
        }

        // Only alphanumeric, dash, underscore
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            return false;
        }

        return true;
    }

    /**
     * Validate resolved path: không dùng realpath (tránh I/O).
     * Chỉ kiểm tra: path thuộc một trong base dirs (PATH_THEMES, PATH_PLUGINS)
     * và không chứa ký tự nguy hiểm (.., \0). Chuẩn hóa path (collapse ..) rồi so prefix.
     *
     * Lưu ý: Symlink trong theme không được resolve – thư mục theme/plugin nên được tin cậy.
     *
     * @param string $path Full path to validate
     * @return string|null Original path if valid, null if invalid
     */
    public static function validateResolvedPath($path)
    {
        if (empty($path) || !is_string($path)) {
            return null;
        }
        // Reject ký tự nguy hiểm
        if (strpos($path, "\0") !== false || strpos($path, '..') !== false) {
            \System\Libraries\Logger::error("PathValidator: Path contains dangerous characters: {$path}");
            return null;
        }
        $normalized = self::normalizePath($path);
        if ($normalized === null || $normalized === '') {
            return null;
        }
        self::init();
        foreach (self::$allowedBaseDirs as $baseDir) {
            if (strpos($normalized, $baseDir) === 0) {
                $after = strlen($baseDir);
                if ($after === strlen($normalized) || $normalized[$after] === '/') {
                    return $path;
                }
            }
        }
        \System\Libraries\Logger::error("PathValidator: Rejected path outside allowed directories: {$path}");
        return null;
    }

    /**
     * Chuẩn hóa path: \ -> /, collapse . và .. (không I/O). Giữ leading / hoặc C:.
     *
     * @param string $path
     * @return string|null Normalized path or null nếu path thoát ra ngoài root (quá nhiều ..)
     */
    private static function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');
        if ($path === '') {
            return '';
        }
        $parts = explode('/', $path);
        $stack = [];
        foreach ($parts as $seg) {
            if ($seg === '.') {
                continue;
            }
            if ($seg === '..') {
                if (empty($stack)) {
                    return null;
                }
                array_pop($stack);
                continue;
            }
            $stack[] = $seg;
        }
        return implode('/', $stack);
    }

    /**
     * Sanitize path segments
     * 
     * @param string $path Path to sanitize
     * @return string Sanitized path
     */
    public static function sanitizePath($path)
    {
        // Remove dangerous characters
        $path = str_replace(['..', "\0", chr(0), '\\'], '', $path);
        
        // Normalize slashes
        $path = str_replace('\\', '/', $path);
        
        // Remove multiple slashes
        $path = preg_replace('#/+#', '/', $path);
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        return $path;
    }
}

