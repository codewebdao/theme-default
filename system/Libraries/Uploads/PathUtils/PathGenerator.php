<?php

namespace System\Libraries\Uploads\PathUtils;

/**
 * PathGenerator - Generate upload paths
 * 
 * Tạo đường dẫn upload theo các pattern:
 * - Date-based: Y/m/d, Y/m, Y-m-d
 * - Hash-based: md5, sha1
 * - User-based: user_id/Y/m
 * - Custom patterns
 * 
 * @package System\Libraries\Uploads\PathUtils
 * @version 2.0.0
 */
class PathGenerator
{
    /**
     * Generate upload path
     * 
     * @param array $options Options
     *   - 'pattern' => string: Path pattern (default: 'Y/m/d')
     *   - 'user_id' => int: User ID (for user-based paths)
     *   - 'filename' => string: Filename (for hash-based paths)
     *   - 'base_dir' => string: Base directory
     * @return string Generated path
     */
    public static function generate($options = [])
    {
        $pattern = $options['pattern'] ?? 'Y/m/d';
        
        switch ($pattern) {
            case 'date':
            case 'Y/m/d':
                return self::generateDatePath('Y/m/d');
                
            case 'Y/m':
                return self::generateDatePath('Y/m');
                
            case 'Y-m-d':
                return self::generateDatePath('Y-m-d');
                
            case 'Y-m':
                return self::generateDatePath('Y-m');
                
            case 'Ymd':
                return self::generateDatePath('Ymd');
                
            case 'hash':
            case 'md5':
                return self::generateHashPath($options['filename'] ?? '', 'md5');
                
            case 'sha1':
                return self::generateHashPath($options['filename'] ?? '', 'sha1');
                
            case 'user':
                return self::generateUserPath($options['user_id'] ?? 0);
                
            case 'user_date':
                return self::generateUserDatePath($options['user_id'] ?? 0);
                
            case 'flat':
                return '';
                
            default:
                // Custom pattern
                return self::parseCustomPattern($pattern, $options);
        }
    }
    
    /**
     * Generate date-based path
     * 
     * @param string $format Date format
     * @return string Path
     */
    public static function generateDatePath($format = 'Y/m/d')
    {
        return date($format);
    }
    
    /**
     * Generate hash-based path
     * 
     * Chia hash thành folders để tránh quá nhiều files trong 1 folder
     * Ví dụ: abc123def456 -> ab/c1/23/abc123def456
     * 
     * @param string $filename Filename
     * @param string $algo Hash algorithm (md5, sha1)
     * @param int $depth Folder depth (default: 3)
     * @param int $length Chars per folder (default: 2)
     * @return string Path
     */
    public static function generateHashPath($filename, $algo = 'md5', $depth = 3, $length = 2)
    {
        if (empty($filename)) {
            $filename = uniqid('', true);
        }
        
        $hash = hash($algo, $filename . time());
        
        $parts = [];
        for ($i = 0; $i < $depth; $i++) {
            $offset = $i * $length;
            $parts[] = substr($hash, $offset, $length);
        }
        
        return implode('/', $parts);
    }
    
    /**
     * Generate user-based path
     * 
     * @param int $userId User ID
     * @return string Path (e.g., user_123)
     */
    public static function generateUserPath($userId)
    {
        if (empty($userId)) {
            return 'guest';
        }
        
        return 'user_' . $userId;
    }
    
    /**
     * Generate user + date path
     * 
     * @param int $userId User ID
     * @param string $dateFormat Date format (default: Y/m)
     * @return string Path (e.g., user_123/2025/01)
     */
    public static function generateUserDatePath($userId, $dateFormat = 'Y/m')
    {
        $userPath = self::generateUserPath($userId);
        $datePath = self::generateDatePath($dateFormat);
        
        return $userPath . '/' . $datePath;
    }
    
    /**
     * Parse custom pattern
     * 
     * Supported placeholders:
     * - {Y}, {m}, {d}, {H}, {i}, {s}: Date/time
     * - {user_id}: User ID
     * - {hash}: MD5 hash
     * - {random}: Random string
     * 
     * @param string $pattern Pattern string
     * @param array $options Options
     * @return string Parsed path
     */
    public static function parseCustomPattern($pattern, $options = [])
    {
        $replacements = [
            '{Y}' => date('Y'),
            '{m}' => date('m'),
            '{d}' => date('d'),
            '{H}' => date('H'),
            '{i}' => date('i'),
            '{s}' => date('s'),
            '{user_id}' => $options['user_id'] ?? 0,
            '{hash}' => md5(($options['filename'] ?? '') . time()),
            '{random}' => substr(md5(uniqid('', true)), 0, 8),
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $pattern);
    }
    
    /**
     * Generate full upload path
     * 
     * @param string $baseDir Base directory
     * @param string $subPath Sub path
     * @param string $filename Filename
     * @return array ['dir' => string, 'relative' => string, 'full' => string]
     */
    public static function generateFullPath($baseDir, $subPath, $filename)
    {
        // Normalize base dir
        $baseDir = rtrim($baseDir, '/\\');
        
        // Normalize sub path
        $subPath = trim($subPath, '/\\');
        $subPath = str_replace('\\', '/', $subPath);
        
        // Build paths
        $dir = $baseDir;
        if (!empty($subPath)) {
            $dir .= '/' . $subPath;
        }
        
        $relative = $subPath;
        if (!empty($filename)) {
            if (!empty($relative)) {
                $relative .= '/';
            }
            $relative .= $filename;
        }
        
        $full = $dir;
        if (!empty($filename)) {
            $full .= '/' . $filename;
        }
        
        return [
            'dir' => $dir,
            'relative' => $relative,
            'full' => $full
        ];
    }
    
    /**
     * Generate image subfolder path
     * 
     * Tạo subfolder cho image để chứa variants (resizes, webp, etc.)
     * 
     * @param string $filename Original filename
     * @param string $parentPath Parent path
     * @return string Subfolder path
     */
    public static function generateImageSubfolder($filename, $parentPath = '')
    {
        // Get filename without extension
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Sanitize
        $basename = PathSanitizer::sanitizeFileName($basename, false);
        
        // Build subfolder path
        $subfolder = $basename;
        
        if (!empty($parentPath)) {
            $parentPath = trim($parentPath, '/\\');
            $subfolder = $parentPath . '/' . $subfolder;
        }
        
        return $subfolder;
    }
    
    /**
     * Generate variant filename
     * 
     * @param string $originalFilename Original filename
     * @param string $suffix Suffix (e.g., '300x200', 'thumb')
     * @param string $extension New extension (optional)
     * @return string Variant filename
     */
    public static function generateVariantFilename($originalFilename, $suffix, $extension = null)
    {
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $ext = $extension ?? pathinfo($originalFilename, PATHINFO_EXTENSION);
        
        return $basename . '_' . $suffix . '.' . $ext;
    }
    
    /**
     * Generate temporary path
     * 
     * @param string $prefix Prefix
     * @return string Temp path
     */
    public static function generateTempPath($prefix = 'upload_')
    {
        $tempDir = sys_get_temp_dir();
        $uniqueId = uniqid($prefix, true);
        
        return $tempDir . '/' . $uniqueId;
    }
}
