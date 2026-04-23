<?php

namespace System\Libraries\Uploads\Validators;

/**
 * SizeValidator - Validate file size
 * 
 * Checks:
 * - Is the file empty
 * - Does the file exceed the maximum size
 * - Does the file meet the minimum size (if required)
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class SizeValidator
{
    private $config;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }
    
    /**
     * Validate file size
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options tùy chỉnh
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function validate($file, $options = [])
    {
        // Check file size field exists
        if (!isset($file['size'])) {
            return [
                'success' => false,
                'error' => 'Missing file size information'
            ];
        }
        
        $fileSize = $file['size'];
        
        // Check empty file
        if ($fileSize <= 0) {
            return [
                'success' => false,
                'error' => 'Empty file (0 bytes)'
            ];
        }
        
        // SECURITY: Check minimum size (from config ONLY, not from options)
        $minSize = $this->config['min_file_size'] ?? null;
        if ($minSize !== null && $fileSize < $minSize) {
            $minSizeKB = round($minSize / 1024, 2);
            $fileSizeKB = round($fileSize / 1024, 2);
            
            return [
                'success' => false,
                'error' => "File too small ({$fileSizeKB}KB). Minimum size: {$minSizeKB}KB"
            ];
        }
        
        // SECURITY: Check maximum size (from config ONLY, not from options)
        $maxSize = $this->config['max_file_size'] ?? 10485760; // 10MB default
        
        if ($fileSize > $maxSize) {
            return [
                'success' => false,
                'error' => $this->formatSizeError($fileSize, $maxSize)
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Format size error message
     * 
     * @param int $fileSize File size in bytes
     * @param int $maxSize Max size in bytes
     * @return string Error message
     */
    private function formatSizeError($fileSize, $maxSize)
    {
        // Determine best unit (KB, MB, GB)
        if ($maxSize >= 1073741824) { // >= 1GB
            $maxSizeFormatted = round($maxSize / 1073741824, 2) . 'GB';
            $fileSizeFormatted = round($fileSize / 1073741824, 2) . 'GB';
        } elseif ($maxSize >= 1048576) { // >= 1MB
            $maxSizeFormatted = round($maxSize / 1048576, 2) . 'MB';
            $fileSizeFormatted = round($fileSize / 1048576, 2) . 'MB';
        } else { // KB
            $maxSizeFormatted = round($maxSize / 1024, 2) . 'KB';
            $fileSizeFormatted = round($fileSize / 1024, 2) . 'KB';
        }
        
        return "File too large ({$fileSizeFormatted}). Max size: {$maxSizeFormatted}";
    }
    
    /**
     * Convert bytes to human readable format
     * 
     * @param int $bytes Bytes
     * @param int $precision Decimal precision
     * @return string Formatted size
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
