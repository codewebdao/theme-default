<?php

namespace System\Libraries\Uploads\PathUtils;

/**
 * UniqueNameGenerator - Generate unique filenames
 * 
 * Tạo tên file unique để tránh ghi đè:
 * - Append counter (file_1.jpg, file_2.jpg)
 * - Append timestamp
 * - Append hash
 * - Cache để tối ưu performance
 * 
 * @package System\Libraries\Uploads\PathUtils
 * @version 2.0.0
 */
class UniqueNameGenerator
{
    /**
     * Cache for unique names (tối ưu khi upload nhiều files)
     */
    private static $cache = [];
    
    /**
     * Generate unique filename in directory
     * 
     * @param string $directory Directory path (for local) or prefix (for S3/GCS)
     * @param string $basename Base filename (without extension)
     * @param string $extension File extension
     * @param array $options Options
     *   - 'method' => string: 'counter', 'timestamp', 'hash', 'uuid' (default: 'counter')
     *   - 'use_cache' => bool: Use cache (default: true)
     *   - 'max_attempts' => int: Max attempts (default: 1000)
     *   - 'storage' => StorageInterface: Storage instance (optional, for S3/GCS)
     *   - 'preserve_filename' => bool: Don't sanitize filename (default: false)
     *   - 'target_format' => string: Target format for existence check (default: null)
     * @return string Unique filename
     */
    public static function generate($directory, $basename, $extension, $options = [])
    {
        $method = $options['method'] ?? 'counter';
        $useCache = $options['use_cache'] ?? true;
        $maxAttempts = $options['max_attempts'] ?? 1000;
        $storage = $options['storage'] ?? null;
        $preserveFilename = $options['preserve_filename'] ?? false;
        $targetFormat = $options['target_format'] ?? null;
        
        // Sanitize inputs (chỉ khi không preserve filename)
        if (!$preserveFilename) {
            $basename = PathSanitizer::sanitizeFileName($basename, false);
        }
        
        // Sử dụng target format nếu có (cho format conversion)
        $finalExtension = $targetFormat ? strtolower($targetFormat) : strtolower($extension);
        
        switch ($method) {
            case 'timestamp':
                return self::generateWithTimestamp($directory, $basename, $finalExtension, $useCache);
                
            case 'hash':
                return self::generateWithHash($directory, $basename, $finalExtension, $useCache);
                
            case 'uuid':
                return self::generateWithUuid($directory, $basename, $finalExtension, $useCache);
                
            case 'counter':
            default:
                return self::generateWithCounter($directory, $basename, $finalExtension, $useCache, $maxAttempts, $storage);
        }
    }
    
    /**
     * Generate unique filename with counter
     * 
     * file.jpg -> file_1.jpg -> file_2.jpg
     * 
     * @param string $directory Directory path
     * @param string $basename Base filename
     * @param string $extension Extension
     * @param bool $useCache Use cache
     * @param int $maxAttempts Max attempts
     * @param mixed $storage Storage instance (optional)
     * @return string Unique filename
     */
    private static function generateWithCounter($directory, $basename, $extension, $useCache, $maxAttempts, $storage = null)
    {
        $filename = $basename . '.' . $extension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        
        // Check cache first: if reserved in this process, treat as existing to force counter
        if ($useCache && isset(self::$cache[$fullPath])) {
            $exists = true;
        } else {
            // Check if file exists on storage/disk
            $exists = self::checkExists($fullPath, $storage);
        }
        
        // If not exists, return as is
        if (!$exists) {
            // Reserve this exact path in-process to avoid race within same request
            if ($useCache) {
                self::$cache[$fullPath] = true;
            }
            return $filename;
        }
        
        // Try with counter
        $counter = 1;
        
        while ($counter <= $maxAttempts) {
            $filename = $basename . '-c' . $counter . '.' . $extension;
            $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
            
            // Check cache first to avoid picking same name in current process
            if ($useCache && isset(self::$cache[$fullPath])) {
                $exists = true;
            } else {
                $exists = self::checkExists($fullPath, $storage);
            }
            
            if (!$exists) {
                // Reserve chosen name
                if ($useCache) {
                    self::$cache[$fullPath] = true;
                }
                return $filename;
            }
            
            $counter++;
        }
        
        // Fallback: use timestamp
        return self::generateWithTimestamp($directory, $basename, $extension, false);
    }
    
    /**
     * Check if file exists (support both local and cloud storage)
     * 
     * @param string $fullPath Full file path
     * @param mixed $storage Storage instance or driver name (optional)
     * @return bool
     */
    private static function checkExists($fullPath, $storage = null)
    {
        if ($storage !== null) {
            // If storage is string, convert to Storage instance
            if (is_string($storage)) {
                $storage = \System\Libraries\Storages::make($storage);
            }
            
            // Use Storage driver to check exists
            // Extract relative path from full path
            if (method_exists($storage, 'getRoot')) {
                $root = $storage->getRoot();
                $relativePath = str_replace($root, '', $fullPath);
                $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
            } else {
                // For S3/GCS, fullPath is already the key
                $relativePath = $fullPath;
            }
            
            return $storage->exists($relativePath);
        }
        
        // Fallback to local filesystem check
        return file_exists($fullPath);
    }
    
    /**
     * Generate unique filename with timestamp
     * 
     * file.jpg -> file_1696234567.jpg
     * 
     * @param string $directory Directory path
     * @param string $basename Base filename
     * @param string $extension Extension
     * @param bool $useCache Use cache
     * @return string Unique filename
     */
    private static function generateWithTimestamp($directory, $basename, $extension, $useCache)
    {
        $timestamp = time();
        $filename = $basename . '_' . $timestamp . '.' . $extension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        
        // If still exists (unlikely), add microseconds
        if (file_exists($fullPath)) {
            $microtime = microtime(true);
            $filename = $basename . '_' . str_replace('.', '', $microtime) . '.' . $extension;
        }
        
        if ($useCache) {
            self::$cache[$fullPath] = true;
        }
        
        return $filename;
    }
    
    /**
     * Generate unique filename with hash
     * 
     * file.jpg -> file_a3f5b2c1.jpg
     * 
     * @param string $directory Directory path
     * @param string $basename Base filename
     * @param string $extension Extension
     * @param bool $useCache Use cache
     * @return string Unique filename
     */
    private static function generateWithHash($directory, $basename, $extension, $useCache)
    {
        $hash = substr(md5($basename . microtime(true) . uniqid('', true)), 0, 8);
        $filename = $basename . '_' . $hash . '.' . $extension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        
        if ($useCache) {
            self::$cache[$fullPath] = true;
        }
        
        return $filename;
    }
    
    /**
     * Generate unique filename with UUID
     * 
     * file.jpg -> file_550e8400-e29b-41d4-a716-446655440000.jpg
     * 
     * @param string $directory Directory path
     * @param string $basename Base filename
     * @param string $extension Extension
     * @param bool $useCache Use cache
     * @return string Unique filename
     */
    private static function generateWithUuid($directory, $basename, $extension, $useCache)
    {
        $uuid = self::generateUuid();
        $filename = $basename . '_' . $uuid . '.' . $extension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        
        if ($useCache) {
            self::$cache[$fullPath] = true;
        }
        
        return $filename;
    }
    
    /**
     * Generate UUID v4
     * 
     * @return string UUID
     */
    private static function generateUuid()
    {
        $data = random_bytes(16);
        
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Check if filename exists in directory
     * 
     * @param string $directory Directory path
     * @param string $filename Filename
     * @return bool True if exists
     */
    public static function exists($directory, $filename)
    {
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        return file_exists($fullPath);
    }
    
    /**
     * Clear cache
     * 
     * @param string|null $directory Clear cache for specific directory (null = all)
     */
    public static function clearCache($directory = null)
    {
        if ($directory === null) {
            self::$cache = [];
        } else {
            $directory = rtrim($directory, DIRECTORY_SEPARATOR);
            
            foreach (self::$cache as $path => $value) {
                if (strpos($path, $directory) === 0) {
                    unset(self::$cache[$path]);
                }
            }
        }
    }
    
    /**
     * Get cache size
     * 
     * @return int Number of cached entries
     */
    public static function getCacheSize()
    {
        return count(self::$cache);
    }
    
    /**
     * Generate unique filename with custom suffix
     * 
     * @param string $directory Directory path
     * @param string $basename Base filename
     * @param string $extension Extension
     * @param string $suffix Custom suffix
     * @return string Unique filename
     */
    public static function generateWithSuffix($directory, $basename, $extension, $suffix)
    {
        $filename = $basename . '_' . $suffix . '.' . $extension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        
        if (!file_exists($fullPath)) {
            return $filename;
        }
        
        // If exists, add counter
        return self::generateWithCounter($directory, $basename . '_' . $suffix, $extension, true, 100);
    }
    
    /**
     * Generate short unique name (for URLs)
     * 
     * @param int $length Length of unique part (default: 8)
     * @return string Short unique name
     */
    public static function generateShortName($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate filename from original with unique suffix
     * 
     * Giữ nguyên tên gốc, chỉ thêm suffix unique
     * 
     * @param string $directory Directory path
     * @param string $originalFilename Original filename (with extension)
     * @param string $method Method: 'counter', 'timestamp', 'hash'
     * @return string Unique filename
     */
    public static function makeUnique($directory, $originalFilename, $method = 'counter')
    {
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        
        return self::generate($directory, $basename, $extension, ['method' => $method]);
    }
}
