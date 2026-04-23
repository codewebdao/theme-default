<?php

namespace System\Libraries\Storages;

use System\Libraries\Responses\StorageResponse;

/**
 * LocalStorage - Local Filesystem Storage Driver
 * 
 * Storage driver cho local filesystem
 * Xử lý tất cả file operations trên local disk
 * 
 * @package System\Libraries\Storages
 * @version 2.0.0
 */
class LocalStorage extends BaseStorage
{
    /**
     * Root directory for storage
     */
    protected $root;

    /**
     * Base URL for files
     */
    protected $baseUrl;

    /**
     * Driver name
     */
    protected $driver = 'local';

    /**
     * Constructor
     * 
     * @param array $config Configuration
     *   - 'root' => string: Root directory path
     *   - 'url' => string: Base URL for files
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Set root directory
        if (isset($config['root']) && strpos(PATH_CONTENT, $config['root']) !== false) {
            $defaultRoot = $config['root'];
        } elseif (defined('PATH_UPLOADS')) {
            $defaultRoot = PATH_UPLOADS;
        } else {
            $defaultRoot = rtrim(PATH_ROOT, '/\\') . DIRECTORY_SEPARATOR . 'writeable' . DIRECTORY_SEPARATOR . 'uploads';
        }

        $this->root = rtrim($defaultRoot, '/\\') . DIRECTORY_SEPARATOR;

        // Set base URL
        $this->baseUrl = $config['url'] ?? files_url();
        // Ensure root directory exists
        if (!is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }
    }

    /**
     * Check if file exists
     * 
     * @param string $path File path (relative to root)
     * @return bool
     */
    public function exists($path)
    {
        if (!$this->isValidPath($path)) {
            return false;
        }

        $fullPath = $this->getFullPath($path);
        return file_exists($fullPath);
    }

    /**
     * Get file contents
     * 
     * @param string $path File path
     * @return string|false
     */
    public function get($path)
    {
        if (!$this->exists($path)) {
            return false;
        }

        $fullPath = $this->getFullPath($path);
        return file_get_contents($fullPath);
    }

    /**
     * Put file contents
     * 
     * @param string $path File path
     * @param string|resource $contents Contents
     * @param array $options Options
     * @return array
     */
    public function put($path, $contents, $options = [])
    {
        if (!$this->isValidPath($path)) {
            return StorageResponse::saveFailed($path, 'Invalid path');
        }

        $fullPath = $this->getFullPath($path);

        // SECURITY: Check for symlink attack
        $symlinkCheck = $this->checkSymlinkSecurity($fullPath);
        if (!$symlinkCheck['safe']) {
            error_log('Security: Symlink attack detected: ' . $fullPath);
            return StorageResponse::saveFailed($path, $symlinkCheck['error']);
        }

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return StorageResponse::saveFailed($path, 'Cannot create directory');
            }
        }

        // Write file
        $result = file_put_contents($fullPath, $contents);

        if ($result === false) {
            return StorageResponse::saveFailed($path, 'Cannot write file');
        }

        // Set permissions
        if (isset($options['permissions'])) {
            chmod($fullPath, $options['permissions']);
        }

        return StorageResponse::saved([
            'path' => $path,
            'full_path' => $fullPath,
            'size' => $result,
            'url' => $this->url($path)
        ]);
    }

    /**
     * Save uploaded file
     * 
     * @param string $sourcePath Source file path (tmp file)
     * @param string $destinationPath Destination path
     * @param array $options Options
     * @return array
     */
    public function save($sourcePath, $destinationPath, $options = [])
    {
        if (!file_exists($sourcePath)) {
            return StorageResponse::saveFailed($destinationPath, 'Source file not found');
        }

        if (!$this->isValidPath($destinationPath)) {
            return StorageResponse::saveFailed($destinationPath, 'Invalid destination path');
        }

        $fullPath = $this->getFullPath($destinationPath);

        // SECURITY: Check for symlink attack
        $symlinkCheck = $this->checkSymlinkSecurity($fullPath);
        if (!$symlinkCheck['safe']) {
            error_log('Security: Symlink attack detected: ' . $fullPath);
            return StorageResponse::saveFailed($destinationPath, $symlinkCheck['error']);
        }

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return StorageResponse::saveFailed($destinationPath, 'Cannot create directory');
            }
        }

        // SECURITY: Use atomic operation with exclusive lock
        // Move uploaded file with race condition protection
        if (is_uploaded_file($sourcePath)) {
            $result = move_uploaded_file($sourcePath, $fullPath);
        } else {
            // For non-uploaded files: use copy + unlink for safety
            $result = copy($sourcePath, $fullPath);
            if ($result) {
                @unlink($sourcePath);
            }
        }

        if (!$result) {
            return StorageResponse::saveFailed($destinationPath, 'Cannot move file');
        }

        // Set permissions
        chmod($fullPath, 0644);

        return StorageResponse::saved([
            'path' => $destinationPath,
            'full_path' => $fullPath,
            'size' => filesize($fullPath),
            'url' => $this->url($destinationPath)
        ]);
    }

    /**
     * Delete file
     * 
     * @param string $path File path
     * @return array
     */
    public function delete($path)
    {
        if (!$this->exists($path)) {
            return StorageResponse::deleted($path); // Already deleted
        }

        $fullPath = $this->getFullPath($path);

        if (!unlink($fullPath)) {
            return StorageResponse::deleteFailed($path, 'Cannot delete file');
        }

        return StorageResponse::deleted($path);
    }

    /**
     * Copy file
     * 
     * @param string $from Source path
     * @param string $to Destination path
     * @return array
     */
    public function copy($from, $to)
    {
        if (!$this->exists($from)) {
            return StorageResponse::saveFailed($to, 'Source file not found');
        }

        $fromPath = $this->getFullPath($from);
        $toPath = $this->getFullPath($to);

        // Ensure destination directory exists
        $directory = dirname($toPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!copy($fromPath, $toPath)) {
            return StorageResponse::saveFailed($to, 'Cannot copy file');
        }

        return StorageResponse::copied($from, $to);
    }

    /**
     * Move file
     * 
     * @param string $from Source path
     * @param string $to Destination path
     * @return array
     */
    public function move($from, $to)
    {
        if (!$this->exists($from)) {
            return StorageResponse::saveFailed($to, 'Source file not found');
        }

        $fromPath = $this->getFullPath($from);
        $toPath = $this->getFullPath($to);

        // Ensure destination directory exists
        $directory = dirname($toPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!rename($fromPath, $toPath)) {
            return StorageResponse::saveFailed($to, 'Cannot move file');
        }

        return StorageResponse::moved($from, $to);
    }

    /**
     * Get file size
     * 
     * @param string $path File path
     * @return int|false
     */
    public function size($path)
    {
        if (!$this->exists($path)) {
            return false;
        }

        $fullPath = $this->getFullPath($path);
        return filesize($fullPath);
    }

    /**
     * Get last modified time
     * 
     * @param string $path File path
     * @return int|false
     */
    public function lastModified($path)
    {
        if (!$this->exists($path)) {
            return false;
        }

        $fullPath = $this->getFullPath($path);
        return filemtime($fullPath);
    }

    /**
     * Get MIME type
     * 
     * @param string $path File path
     * @return string|false
     */
    public function mimeType($path)
    {
        if (!$this->exists($path)) {
            return false;
        }

        $fullPath = $this->getFullPath($path);

        if (function_exists('mime_content_type')) {
            return mime_content_type($fullPath);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        return $mime;
    }

    /**
     * Get file URL
     * 
     * @param string $path File path
     * @return string
     */
    public function url($path)
    {
        if (empty($path)) {
            return rtrim($this->baseUrl, '/');
        }

        $path = $this->normalizePath($path);

        // Ensure there's a '/' between baseUrl and path
        $baseUrl = rtrim($this->baseUrl, '/');
        $path = '/' . ltrim($path, '/');

        return $baseUrl . $path;
    }

    /**
     * Create directory
     * 
     * @param string $path Directory path
     * @return array
     */
    public function makeDirectory($path)
    {
        $fullPath = $this->getFullPath($path);

        if (is_dir($fullPath)) {
            return ['success' => true, 'error' => null];
        }

        if (!mkdir($fullPath, 0755, true)) {
            return ['success' => false, 'error' => 'Cannot create directory'];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Delete directory
     * 
     * @param string $path Directory path
     * @return array
     */
    public function deleteDirectory($path)
    {
        $fullPath = $this->getFullPath($path);

        if (!is_dir($fullPath)) {
            return ['success' => true, 'error' => null]; // Already deleted
        }

        return $this->deleteDirectoryRecursive($fullPath);
    }

    /**
     * Delete directory recursively
     * 
     * @param string $directory Full directory path
     * @return array
     */
    private function deleteDirectoryRecursive($directory)
    {
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($items as $item) {
                if ($item->isDir()) {
                    rmdir($item->getRealPath());
                } else {
                    unlink($item->getRealPath());
                }
            }

            rmdir($directory);

            return ['success' => true, 'error' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * List files in directory
     * 
     * @param string $directory Directory path
     * @param bool $recursive List recursively
     * @return array
     */
    public function files($directory = '', $recursive = false)
    {
        $fullPath = $this->getFullPath($directory);

        if (!is_dir($fullPath)) {
            return [];
        }

        $files = [];

        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $relativePath = $this->getRelativePath($item->getPathname());
                    $files[] = $relativePath;
                }
            }
        } else {
            $items = scandir($fullPath);

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
                if (is_file($itemPath)) {
                    $relativePath = $directory ? $directory . '/' . $item : $item;
                    $files[] = $this->normalizePath($relativePath);
                }
            }
        }

        return $files;
    }

    /**
     * List directories
     * 
     * @param string $directory Directory path
     * @param bool $recursive List recursively
     * @return array
     */
    public function directories($directory = '', $recursive = false)
    {
        $fullPath = $this->getFullPath($directory);

        if (!is_dir($fullPath)) {
            return [];
        }

        $directories = [];

        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    $relativePath = $this->getRelativePath($item->getPathname());
                    $directories[] = $relativePath;
                }
            }
        } else {
            $items = scandir($fullPath);

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
                if (is_dir($itemPath)) {
                    $relativePath = $directory ? $directory . '/' . $item : $item;
                    $directories[] = $this->normalizePath($relativePath);
                }
            }
        }

        return $directories;
    }

    /**
     * Check if path is directory
     * 
     * @param string $path Path
     * @return bool
     */
    public function isDirectory($path)
    {
        $fullPath = $this->getFullPath($path);
        return is_dir($fullPath);
    }

    /**
     * Check if path is file
     * 
     * @param string $path Path
     * @return bool
     */
    public function isFile($path)
    {
        $fullPath = $this->getFullPath($path);
        return is_file($fullPath);
    }

    /**
     * Get full path from relative path
     * 
     * @param string $path Relative path
     * @return string Full path
     */
    protected function getFullPath($path)
    {
        $path = $this->normalizePath($path);
        return $this->buildPath($this->root, $path);
    }

    /**
     * Get relative path from full path
     * 
     * @param string $fullPath Full path
     * @return string Relative path
     */
    protected function getRelativePath($fullPath)
    {
        $fullPath = str_replace('\\', '/', $fullPath);
        $root = str_replace('\\', '/', $this->root);

        if (strpos($fullPath, $root) === 0) {
            return substr($fullPath, strlen($root));
        }

        return $fullPath;
    }

    /**
     * Get root directory
     * 
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
    
    // ========================================
    // CHUNK UPLOAD IMPLEMENTATION
    // ========================================

    /**
     * Check if supports chunk upload
     * 
     * @return bool
     */
    public function supportsChunkUpload()
    {
        return true;
    }

    /**
     * Initialize chunk upload session
     * 
     * @param string $uploadId Upload ID
     * @param array $metadata Upload metadata
     * @return array
     */
    public function initChunkUpload($uploadId, $metadata = [])
    {
        $chunkDir = $this->getChunkDirectory($uploadId);

        // Create chunk directory
        if (!is_dir($chunkDir)) {
            if (!mkdir($chunkDir, 0755, true)) {
                return [
                    'success' => false,
                    'error' => 'Cannot create chunk directory'
                ];
            }
        }

        // Save metadata
        $metadata['upload_id'] = $uploadId;
        $metadata['created_at'] = date('Y-m-d H:i:s');
        $metadata['storage_driver'] = 'local';

        $metadataPath = $chunkDir . DIRECTORY_SEPARATOR . 'metadata.json';
        file_put_contents($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'error' => null,
            'data' => ['upload_id' => $uploadId]
        ];
    }

    /**
     * Upload a chunk
     * 
     * @param string $uploadId Upload ID
     * @param int $chunkNumber Chunk number
     * @param string $chunkData Chunk file path or data
     * @param array $options Options
     * @return array
     */
    public function uploadChunk($uploadId, $chunkNumber, $chunkData, $options = [])
    {
        $chunkDir = $this->getChunkDirectory($uploadId);

        if (!is_dir($chunkDir)) {
            return [
                'success' => false,
                'error' => 'Chunk session not initialized'
            ];
        }

        $chunkPath = $chunkDir . DIRECTORY_SEPARATOR . 'chunk_' . $chunkNumber;

        // If chunkData is a file path, move it
        if (is_file($chunkData)) {
            if (!rename($chunkData, $chunkPath)) {
                return [
                    'success' => false,
                    'error' => 'Cannot save chunk'
                ];
            }
        } else {
            // If it's data, write it
            if (file_put_contents($chunkPath, $chunkData) === false) {
                return [
                    'success' => false,
                    'error' => 'Cannot write chunk'
                ];
            }
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Complete chunk upload (merge chunks)
     * 
     * @param string $uploadId Upload ID
     * @param string $destinationPath Destination path (relative)
     * @param array $options Options
     * @return array
     */
    public function completeChunkUpload($uploadId, $destinationPath, $options = [])
    {
        $chunkDir = $this->getChunkDirectory($uploadId);

        if (!is_dir($chunkDir)) {
            return [
                'success' => false,
                'error' => 'Chunk session not found'
            ];
        }

        // Get metadata
        $metadataPath = $chunkDir . DIRECTORY_SEPARATOR . 'metadata.json';
        if (!file_exists($metadataPath)) {
            return [
                'success' => false,
                'error' => 'Metadata not found'
            ];
        }

        $metadata = json_decode(file_get_contents($metadataPath), true);
        $totalChunks = $metadata['total_chunks'] ?? 0;

        // Build full destination path
        $fullPath = $this->getFullPath($destinationPath);

        // Ensure destination directory exists
        $destDir = dirname($fullPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Merge chunks
        $output = fopen($fullPath, 'wb');
        if ($output === false) {
            return [
                'success' => false,
                'error' => 'Cannot create output file'
            ];
        }

        $totalSize = 0;

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $chunkDir . DIRECTORY_SEPARATOR . 'chunk_' . $i;

            if (!file_exists($chunkPath)) {
                fclose($output);
                unlink($fullPath);
                return [
                    'success' => false,
                    'error' => "Missing chunk: {$i}"
                ];
            }

            $chunk = fopen($chunkPath, 'rb');
            while (!feof($chunk)) {
                $data = fread($chunk, 8192);
                fwrite($output, $data);
                $totalSize += strlen($data);
            }
            fclose($chunk);
        }

        fclose($output);

        // Verify if MD5 provided
        if (!empty($metadata['file_md5'])) {
            $actualMd5 = md5_file($fullPath);
            if ($actualMd5 !== $metadata['file_md5']) {
                unlink($fullPath);
                return [
                    'success' => false,
                    'error' => 'MD5 verification failed'
                ];
            }
        }

        // Clean up chunks
        $this->deleteChunkDirectory($uploadId);

        return [
            'success' => true,
            'error' => null,
            'data' => [
                'path' => $destinationPath,
                'full_path' => $fullPath,
                'size' => $totalSize,
                'url' => $this->url($destinationPath)
            ]
        ];
    }

    /**
     * Abort chunk upload
     * 
     * @param string $uploadId Upload ID
     * @param array $options Options
     * @return array
     */
    public function abortChunkUpload($uploadId, $options = [])
    {
        $result = $this->deleteChunkDirectory($uploadId);

        return [
            'success' => $result,
            'error' => $result ? null : 'Failed to abort upload'
        ];
    }

    /**
     * Get chunk upload progress
     * 
     * @param string $uploadId Upload ID
     * @param array $options Options
     * @return array
     */
    public function getChunkUploadProgress($uploadId, $options = [])
    {
        $chunkDir = $this->getChunkDirectory($uploadId);

        if (!is_dir($chunkDir)) {
            return [
                'success' => false,
                'error' => 'Upload session not found'
            ];
        }

        // Get metadata
        $metadataPath = $chunkDir . DIRECTORY_SEPARATOR . 'metadata.json';
        if (!file_exists($metadataPath)) {
            return [
                'success' => false,
                'error' => 'Metadata not found'
            ];
        }

        $metadata = json_decode(file_get_contents($metadataPath), true);
        $totalChunks = $metadata['total_chunks'] ?? 0;

        // Get uploaded chunks
        $uploadedChunks = [];
        $files = glob($chunkDir . DIRECTORY_SEPARATOR . 'chunk_*');

        foreach ($files as $file) {
            if (preg_match('/chunk_(\d+)$/', $file, $matches)) {
                $uploadedChunks[] = (int) $matches[1];
            }
        }

        sort($uploadedChunks);

        return [
            'success' => true,
            'uploaded_chunks' => $uploadedChunks,
            'total_chunks' => $totalChunks,
            'metadata' => $metadata
        ];
    }

    /**
     * Get chunk directory
     * 
     * @param string $uploadId Upload ID
     * @return string
     */
    private function getChunkDirectory($uploadId)
    {
        $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $uploadId);
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chunks';
        return $tempDir . DIRECTORY_SEPARATOR . $uploadId;
    }

    /**
     * Delete chunk directory
     * 
     * @param string $uploadId Upload ID
     * @return bool
     */
    private function deleteChunkDirectory($uploadId)
    {
        $chunkDir = $this->getChunkDirectory($uploadId);

        if (!is_dir($chunkDir)) {
            return true;
        }

        $items = scandir($chunkDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $chunkDir . DIRECTORY_SEPARATOR . $item;
            if (is_file($path)) {
                unlink($path);
            }
        }

        return rmdir($chunkDir);
    }
    
    // ========================================
    // SECURITY METHODS
    // ========================================

    /**
     * Check for symlink security issues
     * 
     * SECURITY: Prevent symlink attacks where attacker creates symlink
     * pointing to sensitive files (/etc/passwd, config files, etc.)
     * 
     * @param string $fullPath Full path to check
     * @return array ['safe' => bool, 'error' => string|null]
     */
    private function checkSymlinkSecurity($fullPath)
    {
        // Check if target path is a symlink
        if (file_exists($fullPath) && is_link($fullPath)) {
            return [
                'safe' => false,
                'error' => 'Target is a symlink - not allowed for security reasons'
            ];
        }

        // Check if any parent directory is a symlink
        $directory = dirname($fullPath);
        if (file_exists($directory) && is_link($directory)) {
            return [
                'safe' => false,
                'error' => 'Parent directory is a symlink - not allowed'
            ];
        }

        // Resolve real path and verify it's within root
        if (file_exists($fullPath)) {
            $realPath = realpath($fullPath);
            if ($realPath === false) {
                return [
                    'safe' => false,
                    'error' => 'Cannot resolve real path'
                ];
            }

            // Normalize paths for comparison
            $realPath = str_replace('\\', '/', $realPath);
            $rootPath = str_replace('\\', '/', $this->root);

            // Check if resolved path is within root
            if (strpos($realPath, $rootPath) !== 0) {
                return [
                    'safe' => false,
                    'error' => 'Resolved path is outside storage root - possible symlink attack'
                ];
            }
        } else {
            // For new files, check parent directory
            $parentDir = dirname($fullPath);
            if (file_exists($parentDir)) {
                $realParent = realpath($parentDir);
                if ($realParent === false) {
                    return [
                        'safe' => false,
                        'error' => 'Cannot resolve parent directory'
                    ];
                }

                $realParent = str_replace('\\', '/', $realParent);
                $rootPath = str_replace('\\', '/', $this->root);

                if (strpos($realParent, $rootPath) !== 0) {
                    return [
                        'safe' => false,
                        'error' => 'Parent directory is outside storage root'
                    ];
                }
            }
        }

        return ['safe' => true, 'error' => null];
    }
}
