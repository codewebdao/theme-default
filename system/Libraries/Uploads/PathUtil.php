<?php

namespace System\Libraries\Uploads;

use System\Libraries\Uploads\PathUtils\PathSanitizer;
use System\Libraries\Uploads\PathUtils\PathGenerator;
use System\Libraries\Uploads\PathUtils\DirectoryManager;
use System\Libraries\Uploads\PathUtils\UniqueNameGenerator;

/**
 * PathUtil - Main Path Utility
 * 
 * Orchestrator for all path operations
 * Facade pattern for ease of use
 * 
 * Main functions:
 * - Sanitize filenames and paths
 * - Generate upload paths
 * - Manage directories
 * - Generate unique names
 * 
 * @package System\Libraries\Uploads
 * @version 2.0.0
 */
class PathUtil
{
    private $config;
    private $baseUploadDir;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = config('files', 'Uploads') ?? [];
        $this->baseUploadDir = $this->getBaseUploadDirectory();
    }
    
    /**
     * Get base upload directory
     * 
     * @return string Base upload directory path
     */
    private function getBaseUploadDirectory()
    {
        $uploadPath = trim( config('files', 'Uploads')['base_path'] ?? 'uploads', '/' );
        // Build absolute path
        $basePath = rtrim(PATH_WRITE, '/\\') . DIRECTORY_SEPARATOR . trim($uploadPath, '/\\');
        
        return realpath($basePath) ?: $basePath;
    }
    
    /**
     * Generate upload path for file
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options
     *   - 'folder' => string: Custom folder path
     *   - 'pattern' => string: Path pattern (Y/m/d, hash, user, etc.)
     *   - 'user_id' => int: User ID (for user-based paths)
     *   - 'create_image_subfolder' => bool: Create subfolder for images
     * @return array ['success' => bool, 'error' => string|null, 'data' => array]
     */
    public function generateUploadPath($file, $options = [])
    {
        try {
            // Get filename info
            $originalName = $file['name'] ?? 'unnamed';
            
            // Check if we should preserve filename (for pre-processed files like iMagify)
            $preserveFilename = $options['preserve_filename'] ?? false;
            
            if ($preserveFilename) {
                // For iMagify: Keep original filename structure (including double extensions)
                // Just make it safe for filesystem (remove path traversal, null bytes, etc.)
                $basename = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                
                // Minimal sanitization: only remove dangerous characters, keep dots
                // $safeBasename = preg_replace('/[^\w\s\-\.]/', '_', $basename);
                // load_helpers(['string']);
                // $safeBasename = url_slug($basename, [
                //     'delimiter' => '-',
                //     'lowercase' => true
                // ]); 
                $safeBasename = $basename;
                $safeExtension = strtolower(preg_replace('/[^\w]/', '', $extension));
            } else {
                // Normal mode: Full sanitization
                $basename = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                
                // Sanitize filename
                $safeBasename = PathSanitizer::sanitizeFileName($basename, false);
                $safeExtension = strtolower($extension);
            }
            
            // Generate folder path
            if (isset($options['folder']) && !empty($options['folder'])) {
                // Use custom folder
                $folderPath = PathSanitizer::sanitizeFolderPath($options['folder']);
            } else {
                // Generate folder based on pattern
                $pattern = $options['pattern'] ?? 'Y/m/d';
                $folderPath = PathGenerator::generate([
                    'pattern' => $pattern,
                    'user_id' => $options['user_id'] ?? null,
                    'filename' => $originalName
                ]);
            }
            
            // Build directory path
            $uploadDir = $this->baseUploadDir;
            if (!empty($folderPath)) {
                $uploadDir .= DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folderPath);
            }

            // Generate unique filename
            // LUÔN gọi UniqueNameGenerator để đảm bảo unique name
            $uniqueFilename = UniqueNameGenerator::generate(
                $uploadDir,
                $safeBasename,
                $safeExtension,
                [
                    'method' => $options['unique_method'] ?? 'counter',
                    'storage' => $options['storage'] ?? null,
                    'preserve_filename' => $preserveFilename, // Pass flag để generator biết cách xử lý
                    'target_format' => $options['format'] ?? null // Pass target format để check existence đúng
                ]
            );
            
            // Build full path
            $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $uniqueFilename;
            $relativePath = $folderPath;
            if (!empty($relativePath)) {
                $relativePath .= '/';
            }
            $relativePath .= $uniqueFilename;
            
            return [
                'success' => true,
                'error' => null,
                'data' => [
                    'directory' => $uploadDir,
                    'filename' => $uniqueFilename,
                    'basename' => pathinfo($uniqueFilename, PATHINFO_FILENAME),
                    'extension' => $safeExtension,
                    'full_path' => $fullPath,
                    'relative_path' => $relativePath,
                    'folder' => $folderPath,
                    'original_name' => $originalName,
                    'safe_basename' => $safeBasename
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error while generating upload path: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Sanitize filename
     * 
     * @param string $filename Filename
     * @param bool $preserveExtension Preserve extension
     * @return string Sanitized filename
     */
    public function sanitizeFileName($filename, $preserveExtension = true)
    {
        return PathSanitizer::sanitizeFileName($filename, $preserveExtension);
    }
    
    /**
     * Sanitize folder path
     * 
     * @param string $path Folder path
     * @return string Sanitized path
     */
    public function sanitizeFolderPath($path)
    {
        return PathSanitizer::sanitizeFolderPath($path);
    }
    
    /**
     * Create directory
     * 
     * @param string $path Directory path
     * @param int $permissions Permissions
     * @return array Result
     */
    public function createDirectory($path, $permissions = 0755)
    {
        return DirectoryManager::create($path, $permissions);
    }
    
    /**
     * Delete directory
     * 
     * @param string $path Directory path
     * @param bool $deleteRoot Delete root directory
     * @return array Result
     */
    public function deleteDirectory($path, $deleteRoot = true)
    {
        return DirectoryManager::delete($path, $deleteRoot);
    }
    
    /**
     * Generate unique filename
     * 
     * @param string $directory Directory path
     * @param string $basename Base filename
     * @param string $extension Extension
     * @param string $method Method (counter, timestamp, hash, uuid)
     * @return string Unique filename
     */
    public function generateUniqueName($directory, $basename, $extension, $method = 'counter')
    {
        return UniqueNameGenerator::generate($directory, $basename, $extension, ['method' => $method]);
    }
    
    /**
     * Resolve path (convert relative to absolute)
     * 
     * @param string $path Path (relative or absolute)
     * @param string|null $basePath Base path for relative paths
     * @return string Absolute path
     */
    public function resolvePath($path, $basePath = null)
    {
        if (empty($path)) {
            return '';
        }
        
        // Check if absolute
        if ($this->isAbsolutePath($path)) {
            return realpath($path) ?: $path;
        }
        
        // Use base upload dir if no base path provided
        if ($basePath === null) {
            $basePath = $this->baseUploadDir;
        }
        
        // Build full path
        $normalizedPath = ltrim($path, '/\\');
        $fullPath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . $normalizedPath;
        
        return realpath($fullPath) ?: $fullPath;
    }
    
    /**
     * Check if path is absolute
     * 
     * @param string $path Path
     * @return bool True if absolute
     */
    public function isAbsolutePath($path)
    {
        if (empty($path)) {
            return false;
        }
        
        // Windows: C:\, D:\, etc.
        if (preg_match('/^[a-zA-Z]:[\\/]/', $path)) {
            return true;
        }
        
        // Unix: /path
        if (strpos($path, '/') === 0) {
            return true;
        }
        
        // UNC: \\server\share
        if (preg_match('/^\\\\\\\\[^\\\\]+\\\\[^\\\\]+/', $path)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if file is image
     * 
     * @param string $extension Extension
     * @return bool True if image
     */
    private function isImage($extension)
    {
        $imageExtensions = $this->config['images_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        return in_array(strtolower($extension), $imageExtensions);
    }
    
    /**
     * Get full path from relative path
     * 
     * @param string $relativePath Relative path
     * @return string Full path
     */
    public function getFullPath($relativePath)
    {
        return $this->baseUploadDir . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    }
    
    /**
     * Get relative path from full path
     * 
     * @param string $fullPath Full path
     * @return string Relative path
     */
    public function getRelativePath($fullPath)
    {
        $baseDir = $this->baseUploadDir . DIRECTORY_SEPARATOR;
        
        if (strpos($fullPath, $baseDir) === 0) {
            return substr($fullPath, strlen($baseDir));
        }
        
        return $fullPath;
    }
    
    /**
     * Get URL from relative path
     * 
     * @param string $relativePath Relative path
     * @return string URL
     */
    public function getUrl($relativePath)
    {
        return files_url( $relativePath );
    }
    
    /**
     * Clean empty directories in upload folder
     * 
     * @param string|null $path Specific path (null = base upload dir)
     * @return array Result
     */
    public function cleanEmptyDirectories($path = null)
    {
        $targetPath = $path ?? $this->baseUploadDir;
        return DirectoryManager::cleanEmpty($targetPath);
    }
    
    /**
     * Get base upload directory
     * 
     * @return string Base upload directory
     */
    public function getBaseDirectory()
    {
        return $this->baseUploadDir;
    }
    
    /**
     * Clear unique name cache
     */
    public function clearCache()
    {
        UniqueNameGenerator::clearCache();
    }
}
