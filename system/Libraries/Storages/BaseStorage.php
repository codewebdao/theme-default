<?php

namespace System\Libraries\Storages;

/**
 * BaseStorage - Lớp cơ sở cho tất cả Storage drivers
 * 
 * Khai báo các hàm bắt buộc (abstract) và implement các hàm chung
 * Các Storage con (LocalStorage, S3Storage, etc.) kế thừa class này
 * 
 * @package System\Libraries\Storages
 * @version 2.0.0
 */
abstract class BaseStorage
{
    /**
     * Storage configuration
     */
    protected $config = [];
    
    /**
     * Storage driver name
     */
    protected $driver = 'base';
    
    /**
     * Constructor
     * 
     * @param array $config Configuration
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }
    
    // ========================================
    // ABSTRACT METHODS - Bắt buộc implement
    // ========================================
    
    /**
     * Check if file exists
     * 
     * @param string $path File path
     * @return bool
     */
    abstract public function exists($path);
    
    /**
     * Get file contents
     * 
     * @param string $path File path
     * @return string|false
     */
    abstract public function get($path);
    
    /**
     * Put file contents (create or overwrite)
     * 
     * @param string $path File path
     * @param string|resource $contents Contents
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null]
     */
    abstract public function put($path, $contents, $options = []);
    
    /**
     * Save uploaded file
     * 
     * @param string $sourcePath Source file path (tmp file)
     * @param string $destinationPath Destination path
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    abstract public function save($sourcePath, $destinationPath, $options = []);
    
    /**
     * Delete file
     * 
     * @param string $path File path
     * @return array ['success' => bool, 'error' => string|null]
     */
    abstract public function delete($path);
    
    /**
     * Copy file
     * 
     * @param string $from Source path
     * @param string $to Destination path
     * @return array ['success' => bool, 'error' => string|null]
     */
    abstract public function copy($from, $to);
    
    /**
     * Move file
     * 
     * @param string $from Source path
     * @param string $to Destination path
     * @return array ['success' => bool, 'error' => string|null]
     */
    abstract public function move($from, $to);
    
    /**
     * Get file size
     * 
     * @param string $path File path
     * @return int|false
     */
    abstract public function size($path);
    
    /**
     * Get file last modified time
     * 
     * @param string $path File path
     * @return int|false Unix timestamp
     */
    abstract public function lastModified($path);
    
    /**
     * Get file MIME type
     * 
     * @param string $path File path
     * @return string|false
     */
    abstract public function mimeType($path);
    
    /**
     * Get file URL
     * 
     * @param string $path File path
     * @return string URL
     */
    abstract public function url($path);
    
    /**
     * Create directory
     * 
     * @param string $path Directory path
     * @return array ['success' => bool, 'error' => string|null]
     */
    abstract public function makeDirectory($path);
    
    /**
     * Delete directory
     * 
     * @param string $path Directory path
     * @return array ['success' => bool, 'error' => string|null]
     */
    abstract public function deleteDirectory($path);
    
    /**
     * List files in directory
     * 
     * @param string $directory Directory path
     * @param bool $recursive List recursively
     * @return array Array of file paths
     */
    abstract public function files($directory = '', $recursive = false);
    
    /**
     * List directories
     * 
     * @param string $directory Directory path
     * @param bool $recursive List recursively
     * @return array Array of directory paths
     */
    abstract public function directories($directory = '', $recursive = false);
    
    /**
     * Check if path is directory
     * 
     * @param string $path Path
     * @return bool
     */
    abstract public function isDirectory($path);
    
    /**
     * Check if path is file
     * 
     * @param string $path Path
     * @return bool
     */
    abstract public function isFile($path);
    
    // ========================================
    // COMMON METHODS - Implement sẵn, có thể override
    // ========================================
    
    /**
     * Normalize path (convert to forward slashes)
     * 
     * @param string $path Path
     * @return string Normalized path
     */
    protected function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        return trim($path, '/');
    }
    
    /**
     * Build full path from base and relative path
     * 
     * @param string $basePath Base path
     * @param string $relativePath Relative path
     * @return string Full path
     */
    protected function buildPath($basePath, $relativePath)
    {
        $basePath = rtrim($basePath, '/\\');
        $relativePath = ltrim($relativePath, '/\\');
        
        if (empty($relativePath)) {
            return $basePath;
        }
        
        return $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
    
    /**
     * Validate path (security check)
     * 
     * Kiểm tra bảo mật path để ngăn chặn:
     * - Path traversal attacks (../, ..\, %2e%2e, etc.)
     * - Null byte injection
     * - Absolute path attempts
     * - Dangerous characters
     * 
     * @param string $path Path
     * @return bool True if valid
     */
    protected function isValidPath($path)
    {
        // Empty path is invalid
        if (empty($path)) {
            return false;
        }
        
        // Check for null bytes (null byte injection)
        if (strpos($path, "\0") !== false || strpos($path, '%00') !== false) {
            error_log('Security: Null byte injection attempt detected in path: ' . $path);
            return false;
        }
        
        // Check for path traversal patterns
        $traversalPatterns = [
            '..',           // Standard traversal
            '%2e%2e',       // URL encoded ..
            '%252e%252e',   // Double URL encoded ..
            '0x2e0x2e',     // Hex encoded ..
            '\.\.',         // Escaped dots
        ];
        
        $pathLower = strtolower($path);
        foreach ($traversalPatterns as $pattern) {
            if (strpos($pathLower, strtolower($pattern)) !== false) {
                error_log('Security: Path traversal attempt detected in path: ' . $path);
                return false;
            }
        }
        
        // Check for absolute path attempts (Unix/Windows)
        if (preg_match('#^(/|\\\\|[a-zA-Z]:)#', $path)) {
            error_log('Security: Absolute path attempt detected: ' . $path);
            return false;
        }
        
        // Check for dangerous characters
        $dangerousChars = ['<', '>', ':', '"', '|', '?', '*', "\r", "\n", "\t"];
        foreach ($dangerousChars as $char) {
            if (strpos($path, $char) !== false) {
                error_log('Security: Dangerous character detected in path: ' . $path);
                return false;
            }
        }
        
        // Check for stream wrappers (php://, file://, etc.)
        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*://#', $path)) {
            error_log('Security: Stream wrapper attempt detected: ' . $path);
            return false;
        }
        
        // Normalize and check resolved path doesn't escape base
        $normalized = $this->normalizePath($path);
        if (strpos($normalized, '..') !== false) {
            error_log('Security: Path traversal in normalized path: ' . $normalized);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete multiple files
     * 
     * @param array $paths Array of file paths
     * @return array ['success' => bool, 'deleted' => array, 'failed' => array]
     */
    public function deleteMultiple($paths)
    {
        $deleted = [];
        $failed = [];
        
        foreach ($paths as $path) {
            $result = $this->delete($path);
            
            if ($result['success']) {
                $deleted[] = $path;
            } else {
                $failed[] = [
                    'path' => $path,
                    'error' => $result['error']
                ];
            }
        }
        
        return [
            'success' => empty($failed),
            'deleted' => $deleted,
            'failed' => $failed
        ];
    }
    
    /**
     * Get temporary URL (for private files)
     * Default: same as url() - drivers like S3 should override
     * 
     * @param string $path File path
     * @param int $expiration Expiration time in seconds
     * @return string Temporary URL
     */
    public function temporaryUrl($path, $expiration = 3600)
    {
        return $this->url($path);
    }
    
    /**
     * Set file visibility
     * Default: no-op - drivers like S3 should override
     * 
     * @param string $path File path
     * @param string $visibility 'public' or 'private'
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function setVisibility($path, $visibility)
    {
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Get file visibility
     * Default: 'public' - drivers like S3 should override
     * 
     * @param string $path File path
     * @return string 'public' or 'private'
     */
    public function getVisibility($path)
    {
        return 'public';
    }
    
    /**
     * Get driver name
     * 
     * @return string Driver name
     */
    public function getDriver()
    {
        return $this->driver;
    }
    
    /**
     * Get configuration
     * 
     * @param string|null $key Config key (null = all)
     * @param mixed $default Default value
     * @return mixed Config value
     */
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Set configuration
     * 
     * @param string $key Config key
     * @param mixed $value Config value
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }
    
    // ========================================
    // CHUNK UPLOAD METHODS - Optional, drivers có thể override
    // ========================================
    
    /**
     * Check if driver supports chunk upload
     * 
     * @return bool
     */
    public function supportsChunkUpload()
    {
        return false;
    }
    
    /**
     * Initialize chunk upload session
     * 
     * @param string $uploadId Upload ID
     * @param array $metadata Upload metadata
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    public function initChunkUpload($uploadId, $metadata = [])
    {
        return [
            'success' => false,
            'error' => 'Chunk upload not supported by this driver'
        ];
    }
    
    /**
     * Upload a chunk
     * 
     * @param string $uploadId Upload ID
     * @param int $chunkNumber Chunk number
     * @param string $chunkData Chunk data or file path
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function uploadChunk($uploadId, $chunkNumber, $chunkData, $options = [])
    {
        return [
            'success' => false,
            'error' => 'Chunk upload not supported by this driver'
        ];
    }
    
    /**
     * Complete chunk upload (merge chunks)
     * 
     * @param string $uploadId Upload ID
     * @param string $destinationPath Final file path
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    public function completeChunkUpload($uploadId, $destinationPath, $options = [])
    {
        return [
            'success' => false,
            'error' => 'Chunk upload not supported by this driver'
        ];
    }
    
    /**
     * Abort chunk upload
     * 
     * @param string $uploadId Upload ID
     * @param array $options Options (driver-specific metadata)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function abortChunkUpload($uploadId, $options = [])
    {
        return [
            'success' => false,
            'error' => 'Chunk upload not supported by this driver'
        ];
    }
    
    /**
     * Get chunk upload progress
     * 
     * @param string $uploadId Upload ID
     * @param array $options Options (driver-specific metadata)
     * @return array ['success' => bool, 'uploaded_chunks' => array, 'total_chunks' => int]
     */
    public function getChunkUploadProgress($uploadId, $options = [])
    {
        return [
            'success' => false,
            'error' => 'Chunk upload not supported by this driver'
        ];
    }
}
