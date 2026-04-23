<?php

namespace System\Libraries\Uploads\PathUtils;

/**
 * DirectoryManager - Manage directories
 * 
 * Directory management:
 * - Create directories (recursive)
 * - Delete directories (recursive)
 * - Check access permissions
 * - Clean up empty directories
 * 
 * @package System\Libraries\Uploads\PathUtils
 * @version 2.0.0
 */
class DirectoryManager
{
    /**
     * Create directory recursively
     * 
     * @param string $path Directory path
     * @param int $permissions Permissions (default: 0755)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function create($path, $permissions = 0755)
    {
        if (empty($path)) {
            return [
                'success' => false,
                'error' => 'Directory path cannot be empty'
            ];
        }
        
        // Already exists
        if (is_dir($path)) {
            return ['success' => true, 'error' => null];
        }
        
        // Try to create
        try {
            if (!mkdir($path, $permissions, true)) {
                return [
                    'success' => false,
                    'error' => "Cannot create directory: {$path}"
                ];
            }
            
            // Verify creation
            if (!is_dir($path)) {
                return [
                    'success' => false,
                    'error' => "Directory created but not accessible: {$path}"
                ];
            }
            
            return ['success' => true, 'error' => null];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Error creating directory: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete directory recursively
     * 
     * @param string $path Directory path
     * @param bool $deleteRoot Delete root directory (default: true)
     * @return array ['success' => bool, 'error' => string|null, 'deleted_count' => int]
     */
    public static function delete($path, $deleteRoot = true)
    {
        if (empty($path) || !is_dir($path)) {
            return [
                'success' => false,
                'error' => 'Directory does not exist',
                'deleted_count' => 0
            ];
        }
        
        // Safety check: prevent deleting system directories
        if (self::isSystemDirectory($path)) {
            return [
                'success' => false,
                'error' => 'Cannot delete system directory',
                'deleted_count' => 0
            ];
        }
        
        $deletedCount = 0;
        
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($items as $item) {
                if ($item->isDir()) {
                    rmdir($item->getRealPath());
                } else {
                    unlink($item->getRealPath());
                }
                $deletedCount++;
            }
            
            // Delete root directory
            if ($deleteRoot) {
                rmdir($path);
                $deletedCount++;
            }
            
            return [
                'success' => true,
                'error' => null,
                'deleted_count' => $deletedCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Error deleting directory: " . $e->getMessage(),
                'deleted_count' => $deletedCount
            ];
        }
    }
    
    /**
     * Check if directory is empty
     * 
     * @param string $path Directory path
     * @return bool True if empty
     */
    public static function isEmpty($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        
        $items = scandir($path);
        
        // Remove . and ..
        $items = array_diff($items, ['.', '..']);
        
        return count($items) === 0;
    }
    
    /**
     * Clean empty directories recursively
     * 
         * Remove empty directories in the directory tree
     * 
     * @param string $path Root directory path
     * @return array ['success' => bool, 'deleted_count' => int]
     */
    public static function cleanEmpty($path)
    {
        if (!is_dir($path)) {
            return [
                'success' => false,
                'deleted_count' => 0
            ];
        }
        
        $deletedCount = 0;
        
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($items as $item) {
                if ($item->isDir()) {
                    $dirPath = $item->getRealPath();
                    if (self::isEmpty($dirPath)) {
                        rmdir($dirPath);
                        $deletedCount++;
                    }
                }
            }
            
            return [
                'success' => true,
                'deleted_count' => $deletedCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'deleted_count' => $deletedCount,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if directory is writable
     * 
     * @param string $path Directory path
     * @return bool True if writable
     */
    public static function isWritable($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        
        return is_writable($path);
    }
    
    /**
     * Check if directory is readable
     * 
     * @param string $path Directory path
     * @return bool True if readable
     */
    public static function isReadable($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        
        return is_readable($path);
    }
    
    /**
     * Get directory size (recursive)
     * 
     * @param string $path Directory path
     * @return int Size in bytes
     */
    public static function getSize($path)
    {
        if (!is_dir($path)) {
            return 0;
        }
        
        $size = 0;
        
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($items as $item) {
                if ($item->isFile()) {
                    $size += $item->getSize();
                }
            }
            
        } catch (\Exception $e) {
            // Silent fail
        }
        
        return $size;
    }
    
    /**
     * Count files in directory (recursive)
     * 
     * @param string $path Directory path
     * @param string $extension Filter by extension (optional)
     * @return int File count
     */
    public static function countFiles($path, $extension = null)
    {
        if (!is_dir($path)) {
            return 0;
        }
        
        $count = 0;
        
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($items as $item) {
                if ($item->isFile()) {
                    if ($extension === null) {
                        $count++;
                    } else {
                        if (strtolower($item->getExtension()) === strtolower($extension)) {
                            $count++;
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            // Silent fail
        }
        
        return $count;
    }
    
    /**
     * Ensure directory exists and is writable
     * 
     * @param string $path Directory path
     * @param int $permissions Permissions (default: 0755)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function ensure($path, $permissions = 0755)
    {
        // Create if not exists
        if (!is_dir($path)) {
            $result = self::create($path, $permissions);
            if (!$result['success']) {
                return $result;
            }
        }
        
        // Check writable
        if (!self::isWritable($path)) {
            return [
                'success' => false,
                'error' => "Directory is not writable: {$path}"
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Check if path is system directory (safety check)
     * 
     * @param string $path Directory path
     * @return bool True if system directory
     */
    private static function isSystemDirectory($path)
    {
        $path = realpath($path);
        
        if ($path === false) {
            return false;
        }
        
        // Normalize path
        $path = str_replace('\\', '/', strtolower($path));
        
        // System directories to protect
        $systemDirs = [
            '/windows',
            '/system32',
            '/program files',
            '/boot',
            '/etc',
            '/bin',
            '/sbin',
            '/usr',
            '/var',
            '/root',
            '/home',
            '/tmp',
            '/temp',
            'c:/windows',
            'c:/program files',
        ];
        
        foreach ($systemDirs as $sysDir) {
            if (strpos($path, $sysDir) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Copy directory recursively
     * 
     * @param string $source Source directory
     * @param string $destination Destination directory
     * @return array ['success' => bool, 'error' => string|null, 'copied_count' => int]
     */
    public static function copy($source, $destination)
    {
        if (!is_dir($source)) {
            return [
                'success' => false,
                'error' => 'Source directory does not exist',
                'copied_count' => 0
            ];
        }
        
        // Create destination
        $result = self::create($destination);
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'],
                'copied_count' => 0
            ];
        }
        
        $copiedCount = 0;
        
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($items as $item) {
                $destPath = $destination . DIRECTORY_SEPARATOR . $items->getSubPathName();
                
                if ($item->isDir()) {
                    self::create($destPath);
                } else {
                    copy($item, $destPath);
                    $copiedCount++;
                }
            }
            
            return [
                'success' => true,
                'error' => null,
                'copied_count' => $copiedCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Error copying directory: " . $e->getMessage(),
                'copied_count' => $copiedCount
            ];
        }
    }
}
