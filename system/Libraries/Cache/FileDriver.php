<?php

namespace System\Libraries\Cache;

/**
 * File Cache Driver
 * 
 * Production-ready file cache implementation with:
 * - File locking for concurrency
 * - Secure serialization
 * - Automatic garbage collection
 * - Directory hashing for performance
 * - Atomic operations
 * 
 * Based on Laravel and CodeIgniter implementations
 */
class FileDriver implements CacheInterface
{
    /**
     * Cache directory path
     * @var string
     */
    private $path;

    /**
     * Cache key prefix
     * @var string
     */
    private $prefix;

    /**
     * File permissions
     * @var int
     */
    private $filePermission = 0640;

    /**
     * Directory permissions
     * @var int
     */
    private $directoryPermission = 0750;

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct($config)
    {
        $this->path = rtrim($config['path'] ?? PATH_WRITE . '/cache', '/');
        $this->prefix = $config['prefix'] ?? 'cmsff_';

        // Create cache directory if not exists
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, $this->directoryPermission, true)) {
                throw new \RuntimeException("Unable to create cache directory: {$this->path}");
            }
        }

        // Verify directory is writeable
        if (!is_writable($this->path)) {
            throw new \RuntimeException("Cache directory is not writeable: {$this->path}");
        }
    }

    /**
     * Get cache file path for a key
     * Uses 3-level directory hashing for performance with large caches
     *
     * @param string $key
     * @return string
     */
    private function getPath($key)
    {
        $hash = hash('sha256', $this->prefix . $key);

        // 3-level directory structure: ab/cd/efgh...
        // This prevents too many files in single directory
        $level1 = substr($hash, 0, 2);
        $level2 = substr($hash, 2, 2);

        $dir = $this->path . DIRECTORY_SEPARATOR . $level1 . DIRECTORY_SEPARATOR . $level2;

        return $dir . DIRECTORY_SEPARATOR . $hash;
    }

    /**
     * Ensure directory exists for cache file
     *
     * @param string $path Full file path
     * @return bool
     */
    private function ensureDirectory($path)
    {
        $dir = dirname($path);

        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir, $this->directoryPermission, true);
    }


    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->getPayload($key)['data'] ?? null;
    }

    /**
     * Retrieve multiple items from the cache by key
     *
     * @param array $keys
     * @return array
     */
    public function many(array $keys)
    {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }

        return $results;
    }

    /**
     * Get cache payload with metadata and integrity check
     *
     * @param string $key
     * @return array ['data' => mixed, 'time' => int]
     */
    protected function getPayload($key)
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return ['data' => null, 'time' => null];
        }

        try {
            // Open file with shared lock for concurrent reads
            $handle = fopen($path, 'rb');

            if ($handle === false) {
                return ['data' => null, 'time' => null];
            }

            // Acquire shared lock (multiple simultaneous reads allowed)
            if (!flock($handle, LOCK_SH)) {
                fclose($handle);
                return ['data' => null, 'time' => null];
            }

            $size = filesize($path);

            // Sanity check file size (max 10MB per cache file)
            if ($size === false || $size > 10485760) {
                flock($handle, LOCK_UN);
                fclose($handle);
                error_log("Cache file too large or unreadable for key '{$key}': {$size} bytes");
                $this->forget($key);
                return ['data' => null, 'time' => null];
            }

            // Read entire file
            $contents = fread($handle, $size);

            // Release lock and close immediately
            flock($handle, LOCK_UN);
            fclose($handle);

            // Quick check if contents is valid
            if ($contents === false || empty($contents)) {
                $this->forget($key);
                return ['data' => null, 'time' => null];
            }

            // Unserialize data with error suppression
            $data = @unserialize($contents);

            // Verify data structure and integrity
            if (!is_array($data) || !isset($data['time'], $data['data'])) {
                // Corrupted cache file - delete it
                error_log("Cache: Corrupted file for key '{$key}', removing");
                $this->forget($key);
                return ['data' => null, 'time' => null];
            }

            // Optional: Verify checksum if present (future enhancement)
            if (isset($data['checksum'])) {
                $expectedChecksum = hash('xxh3', serialize($data['data']));
                if (!hash_equals($data['checksum'], $expectedChecksum)) {
                    error_log("Cache: Checksum mismatch for key '{$key}', data corrupted");
                    $this->forget($key);
                    return ['data' => null, 'time' => null];
                }
            }

            // Check expiration (0 = forever)
            $now = time();
            if ($data['time'] > 0 && $data['time'] < $now) {
                // Expired - lazy deletion
                $this->forget($key);
                return ['data' => null, 'time' => null];
            }

            return $data;
        } catch (\Exception $e) {
            error_log("Cache read exception for key '{$key}': " . $e->getMessage());
            return ['data' => null, 'time' => null];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds)
    {
        return $this->putInternal($key, $value, $seconds);
    }

    /**
     * Store multiple items in the cache
     *
     * @param array $values
     * @param int $seconds
     * @return bool
     */
    public function putMany(array $values, $seconds)
    {
        foreach ($values as $key => $value) {
            if (!$this->put($key, $value, $seconds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Internal put method with file locking and integrity
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds TTL in seconds (0 = forever)
     * @return bool
     */
    protected function putInternal($key, $value, $seconds)
    {
        $path = $this->getPath($key);

        // Ensure directory structure exists
        if (!$this->ensureDirectory($path)) {
            error_log("Cache: Failed to create directory for key '{$key}'");
            return false;
        }

        // Calculate expiration timestamp
        $expiration = $seconds > 0 ? time() + $seconds : 0;

        // Prepare payload with metadata
        $payload = [
            'time' => $expiration,
            'data' => $value,
            // Optional: Add checksum for integrity verification
            // 'checksum' => hash('xxh3', serialize($value))
        ];

        // Serialize data
        try {
            $serialized = serialize($payload);
        } catch (\Exception $e) {
            error_log("Cache: Serialization failed for key '{$key}': " . $e->getMessage());
            return false;
        }

        // Check size limit (10MB per cache file)
        if (strlen($serialized) > 10485760) {
            error_log("Cache: Value too large for key '{$key}': " . strlen($serialized) . " bytes");
            return false;
        }

        try {
            // Write to temporary file first (atomic operation)
            $tempPath = $path . '.' . uniqid('tmp', true);

            // Open temp file for writing
            $handle = fopen($tempPath, 'wb');

            if ($handle === false) {
                return false;
            }

            // Acquire exclusive lock
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                @unlink($tempPath);
                return false;
            }

            // Write data
            $bytesWritten = fwrite($handle, $serialized);

            // Flush to disk (ensure data is written)
            fflush($handle);

            // Release lock
            flock($handle, LOCK_UN);
            fclose($handle);

            if ($bytesWritten === false || $bytesWritten !== strlen($serialized)) {
                @unlink($tempPath);
                return false;
            }

            // Set permissions on temp file
            @chmod($tempPath, $this->filePermission);

            // Atomic rename (overwrites existing file atomically)
            // This ensures readers never see partial writes
            if (!rename($tempPath, $path)) {
                @unlink($tempPath);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            error_log("Cache write exception for key '{$key}': " . $e->getMessage());

            // Cleanup temp file if exists
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return false;
        }
    }

    /**
     * {@inheritdoc}
     * 
     * Atomic increment with file locking
     * Preserves expiration time of existing key
     */
    /**
     * {@inheritdoc}
     * 
     * @param string $key
     * @param int $value Amount to increment
     * @param int|null $defaultTtl Optional TTL for new keys (if key doesn't exist)
     * @return int|bool New value or false on failure
     */
    public function increment($key, $value = 1, $defaultTtl = null)
    {
        $path = $this->getPath($key);
        $ttl = $defaultTtl ?? 3600; // Default 1 hour if not specified

        try {
            // If file doesn't exist, initialize
            if (!file_exists($path)) {
                return $this->put($key, $value, $ttl) ? $value : false;
            }

            // Open with read-write mode
            $handle = fopen($path, 'r+b');

            if ($handle === false) {
                // Fallback to regular put
                return $this->put($key, $value, $ttl) ? $value : false;
            }

            // Acquire exclusive lock for atomic operation
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                return false;
            }

            // Read current data
            $size = filesize($path);
            $contents = $size > 0 ? fread($handle, $size) : '';
            $data = @unserialize($contents);

            // Verify data structure
            if (!is_array($data) || !isset($data['time'], $data['data'])) {
                flock($handle, LOCK_UN);
                fclose($handle);
                // Reinitialize with provided TTL
                return $this->put($key, $value, $ttl) ? $value : false;
            }

            // Check expiration
            $now = time();
            if ($data['time'] > 0 && $data['time'] < $now) {
                flock($handle, LOCK_UN);
                fclose($handle);
                $this->forget($key);
                // Reinitialize with provided TTL
                return $this->put($key, $value, $ttl) ? $value : false;
            }

            // Calculate new value
            $current = is_numeric($data['data']) ? $data['data'] : 0;
            $new = $current + $value;

            // Update data (preserve expiration)
            $data['data'] = $new;
            $serialized = serialize($data);

            // Write updated data
            rewind($handle);
            ftruncate($handle, 0);
            $result = fwrite($handle, $serialized);
            fflush($handle);

            // Release lock and close
            flock($handle, LOCK_UN);
            fclose($handle);

            return $result !== false ? $new : false;
        } catch (\Exception $e) {
            error_log("Cache increment exception for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        $path = $this->getPath($key);

        if (file_exists($path)) {
            try {
                // Secure delete with lock
                $handle = fopen($path, 'r+b');
                if ($handle) {
                    flock($handle, LOCK_EX);
                    fclose($handle);
                }

                return @unlink($path);
            } catch (\Exception $e) {
                error_log("Cache forget error for key '{$key}': " . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        return $this->deleteDirectory($this->path, false);
    }

    /**
     * Recursively delete directory contents
     *
     * @param string $directory
     * @param bool $deleteSelf Whether to delete the directory itself
     * @return bool
     */
    protected function deleteDirectory($directory, $deleteSelf = true)
    {
        if (!is_dir($directory)) {
            return true;
        }

        $items = new \FilesystemIterator($directory);

        foreach ($items as $item) {
            // Skip .htaccess
            if ($item->getFilename() === '.htaccess') {
                continue;
            }

            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname(), true);
            } else {
                @unlink($item->getPathname());
            }
        }

        if ($deleteSelf) {
            return @rmdir($directory);
        }

        return true;
    }

    /**
     * Remove expired cache items (garbage collection)
     * 
     * Optimized with:
     * - Batch processing (limit items per run)
     * - Early termination (max execution time)
     * - Safe iteration (exception handling per file)
     * - Directory cleanup
     * 
     * Should be called periodically via cron or probabilistically
     *
     * @param int $maxItems Max items to process (prevent long-running GC)
     * @param int $maxTime Max execution time in seconds
     * @return int Number of items removed
     */
    public function gc($maxItems = 1000, $maxTime = 30)
    {
        $count = 0;
        $now = time();
        $startTime = time();
        $processed = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->path,
                    \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO
                ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                // Check execution time limit
                if (time() - $startTime >= $maxTime) {
                    error_log("Cache GC: Stopped due to time limit (processed {$processed} items)");
                    break;
                }

                // Check item limit
                if ($processed >= $maxItems) {
                    break;
                }

                if (!$file->isFile()) {
                    continue;
                }

                $processed++;

                try {
                    // Quick read without lock (for GC performance)
                    $contents = @file_get_contents($file->getPathname());

                    if ($contents === false) {
                        continue;
                    }

                    // Unserialize
                    $data = @unserialize($contents);

                    // Invalid/corrupted file - delete
                    if (!is_array($data) || !isset($data['time'])) {
                        @unlink($file->getPathname());
                        $count++;
                        continue;
                    }

                    // Delete expired items (time > 0 means has expiration)
                    if ($data['time'] > 0 && $data['time'] < $now) {
                        if (@unlink($file->getPathname())) {
                            $count++;
                        }
                    }
                } catch (\Exception $e) {
                    // Skip this file, don't stop GC
                    continue;
                }
            }

            // Remove empty directories after cleanup
            if ($count > 0) {
                $this->removeEmptyDirectories($this->path);
            }
        } catch (\Exception $e) {
            error_log("Cache GC exception: " . $e->getMessage());
        }

        return $count;
    }

    /**
     * Remove empty subdirectories
     *
     * @param string $directory
     */
    protected function removeEmptyDirectories($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = @scandir($directory);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.htaccess') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->removeEmptyDirectories($path);

                // Try to remove if empty
                $contents = @scandir($path);
                if ($contents !== false && count($contents) === 2) { // Only . and ..
                    @rmdir($path);
                }
            }
        }
    }

    /**
     * Get the cache key prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
