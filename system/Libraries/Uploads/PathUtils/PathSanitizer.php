<?php

namespace System\Libraries\Uploads\PathUtils;

/**
 * PathSanitizer - Sanitize paths and filenames
 * 
 * Làm sạch và chuẩn hóa:
 * - Filename: loại bỏ ký tự nguy hiểm, unicode, accents
 * - Folder path: chuẩn hóa separators, loại bỏ path traversal
 * - Slug: tạo URL-friendly strings
 * 
 * @package System\Libraries\Uploads\PathUtils
 * @version 2.0.0
 */
class PathSanitizer
{
    /**
     * Sanitize filename - loại bỏ ký tự nguy hiểm
     * 
     * Uses url_slug() from String_helper.php for better handling
     * 
     * @param string $filename Filename cần sanitize
     * @param bool $preserveExtension Giữ nguyên extension
     * @return string Sanitized filename
     */
    public static function sanitizeFileName($filename, $preserveExtension = true)
    {
        if (empty($filename)) {
            return 'unnamed_' . uniqid();
        }
        
        // Tách extension nếu cần preserve
        $extension = '';
        if ($preserveExtension) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = pathinfo($filename, PATHINFO_FILENAME);
        }
        
        // Use url_slug() from String_helper.php
        if (!function_exists('url_slug')) {
            load_helpers(['string']);
        }
        // This handles Vietnamese, French, and many other languages
        $filename = url_slug($filename, [
            'delimiter' => '-',
            'limit' => 140,
            'lowercase' => true
        ]);
        
        // If empty after sanitization, use default
        if (empty($filename)) {
            $filename = 'file_' . uniqid();
        }
        
        // Add extension back
        if ($preserveExtension && !empty($extension)) {
            $filename .= '.' . strtolower($extension);
        }
        
        return $filename;
    }
    
    /**
     * Sanitize folder path - chuẩn hóa và bảo mật
     * 
     * SECURITY: Enhanced protection against:
     * - Path traversal (../, ..\, encoded variants)
     * - Null byte injection
     * - Stream wrappers
     * - Encoded attacks (%2e%2e, %252e, etc.)
     * 
     * Uses url_slug() from String_helper.php for each path segment
     * 
     * @param string $path Folder path
     * @return string Sanitized path
     */
    public static function sanitizeFolderPath($path)
    {
        if (empty($path)) {
            return '';
        }
        
        // SECURITY: Decode URL encoding multiple times to catch double/triple encoding
        $previousPath = '';
        $iterations = 0;
        while ($path !== $previousPath && $iterations < 5) {
            $previousPath = $path;
            $path = urldecode($path);
            $iterations++;
        }
        
        // SECURITY: Remove null bytes
        $path = str_replace(["\0", '%00'], '', $path);
        
        // Normalize separators to forward slash
        $path = str_replace(['\\', ':'], '/', $path);
        
        // SECURITY: Remove all path traversal patterns (including encoded)
        $traversalPatterns = [
            '../',
            './',
            '..\\',
            '.\\',
            '..;/',
            '..;\\',
            '..../',
            '....\\',
            '%2e%2e/',
            '%2e%2e%5c',
            '..%2f',
            '..%5c'
        ];
        
        foreach ($traversalPatterns as $pattern) {
            $path = str_replace($pattern, '', $path);
        }
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        // SECURITY: Check for stream wrappers
        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*://#', $path)) {
            error_log('Security: Stream wrapper detected in path: ' . $path);
            return ''; // Return empty to reject
        }
        
        // Split into parts
        $parts = explode('/', $path);
        $sanitized = [];
        load_helpers(['string']);
        
        foreach ($parts as $part) {
            if (empty($part) || $part === '.' || $part === '..') {
                continue;
            }
            
            // SECURITY: Additional check for encoded dots
            if (preg_match('/%[0-9a-f]{2}/i', $part)) {
                error_log('Security: Encoded characters in path segment: ' . $part);
                continue; // Skip this segment
            }
            
            // Use url_slug() for each path segment
            $part = url_slug($part, [
                'delimiter' => '-',
                'lowercase' => true
            ]);
            
            if (!empty($part)) {
                $sanitized[] = $part;
            }
        }
        
        return implode('/', $sanitized);
    }
    
    /**
     * Remove accents from string (Vietnamese, French, etc.)
     * 
     * Uses remove_accents() from String_helper.php
     * This function supports 500+ character mappings including:
     * - Vietnamese (all tones)
     * - French, German, Spanish, Portuguese
     * - Greek, Cyrillic, and many more
     * 
     * @param string $string String with accents
     * @return string String without accents
     */
    public static function removeAccents($string)
    {
        if (empty($string)) {
            return '';
        }
        
        // Use remove_accents() from String_helper.php
        // This is much more comprehensive than our old implementation
        return remove_accents($string);
    }
    
    /**
     * Create URL-friendly slug
     * 
     * Uses url_slug() from String_helper.php
     * 
     * @param string $string String to slugify
     * @param string $separator Separator character (default: dash)
     * @return string Slug
     */
    public static function slugify($string, $separator = '-')
    {
        if (empty($string)) {
            return '';
        }
        if (!function_exists('url_slug')) {
            load_helpers(['string']);
        }        
        // Use url_slug() from String_helper.php
        return url_slug($string, [
            'delimiter' => $separator,
            'lowercase' => true
        ]);
    }
    
    /**
     * Validate filename - check if safe
     * 
     * @param string $filename Filename to validate
     * @return bool True if valid
     */
    public static function isValidFileName($filename)
    {
        if (empty($filename)) {
            return false;
        }
        
        // Check for path traversal
        if (strpos($filename, '..') !== false) {
            return false;
        }
        
        // Check for directory separators
        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return false;
        }
        
        // Check for null bytes
        if (strpos($filename, "\0") !== false) {
            return false;
        }
        
        // Check for dangerous characters
        $dangerous = ['<', '>', ':', '"', '|', '?', '*'];
        foreach ($dangerous as $char) {
            if (strpos($filename, $char) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Truncate filename to max length (preserve extension)
     * 
     * @param string $filename Filename
     * @param int $maxLength Max length (default: 140)
     * @return string Truncated filename
     */
    public static function truncateFileName($filename, $maxLength = 140)
    {
        if (strlen($filename) <= $maxLength) {
            return $filename;
        }
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Calculate max length for basename
        $maxBasenameLength = $maxLength - strlen($extension) - 1; // -1 for dot
        
        if ($maxBasenameLength <= 0) {
            return substr($filename, 0, $maxLength);
        }
        
        $basename = substr($basename, 0, $maxBasenameLength);
        
        return $basename . '.' . $extension;
    }
}
