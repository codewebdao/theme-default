<?php

namespace System\Libraries\Uploads\ChunkUtils;

/**
 * ChunkValidator - Validate chunk uploads
 * 
 * Kiểm tra:
 * - Chunk number hợp lệ
 * - Upload ID hợp lệ
 * - Chunk size
 * - Chunk integrity
 * 
 * SECURITY: All validation limits are loaded from config('files', 'Uploads') ONLY.
 * Client cannot override max_chunks or max_chunk_size via options.
 * 
 * @package System\Libraries\Uploads\ChunkUtils
 * @version 2.1.0
 */
class ChunkValidator
{
    /**
     * Validate chunk info
     * 
     * @param array $chunkInfo Chunk information
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function validate($chunkInfo, $options = [])
    {
        // Check required fields
        $required = ['uploadId', 'chunkNumber', 'totalChunks', 'fileName'];
        
        foreach ($required as $field) {
            if (!isset($chunkInfo[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: {$field}"
                ];
            }
        }
        
        // Validate upload ID format
        if (!self::isValidUploadId($chunkInfo['uploadId'])) {
            return [
                'success' => false,
                'error' => 'Invalid upload ID format'
            ];
        }
        
        // Validate chunk number (0-based: 0 to N-1)
        $chunkNumber = (int) $chunkInfo['chunkNumber'];
        $totalChunks = (int) $chunkInfo['totalChunks'];
        
        if ($chunkNumber < 0) {
            return [
                'success' => false,
                'error' => 'Chunk number cannot be negative'
            ];
        }
        
        if ($chunkNumber >= $totalChunks) {
            return [
                'success' => false,
                'error' => "Chunk number ({$chunkNumber}) must be less than total chunks ({$totalChunks})"
            ];
        }
        
        // Validate total chunks
        if ($totalChunks <= 0) {
            return [
                'success' => false,
                'error' => 'Total chunks must be greater than 0'
            ];
        }
        
        // SECURITY: Load from config ONLY, not from options
        $config = config('files', 'Uploads') ?? [];
        $maxChunks = $config['max_total_chunks'] ?? 1000;
        
        if ($totalChunks > $maxChunks) {
            return [
                'success' => false,
                'error' => "Total chunks ({$totalChunks}) exceeds maximum ({$maxChunks})"
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Validate upload ID format
     * 
     * @param string $uploadId Upload ID
     * @return bool
     */
    private static function isValidUploadId($uploadId)
    {
        // Only allow alphanumeric, dash, underscore
        return preg_match('/^[a-zA-Z0-9_-]+$/', $uploadId) === 1;
    }
    
    /**
     * Validate chunk file
     * 
     * @param array $file File array from $_FILES
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function validateChunkFile($file, $options = [])
    {
        // Check upload error
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Chunk upload error'
            ];
        }
        
        // Check file exists
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'Chunk file not found'
            ];
        }
        
        // Check chunk size
        $chunkSize = $file['size'] ?? 0;
        
        if ($chunkSize <= 0) {
            return [
                'success' => false,
                'error' => 'Chunk is empty'
            ];
        }
        
        // SECURITY: Load from config ONLY, not from options
        $config = config('files', 'Uploads') ?? [];
        $maxChunkSize = $config['max_chunk_size'] ?? 10485760; // 10MB default
        
        if ($chunkSize > $maxChunkSize) {
            return [
                'success' => false,
                'error' => 'Chunk size exceeds maximum'
            ];
        }
        
        // SECURITY: Validate chunk content (prevent bypass)
        // Scan for malicious patterns even in chunks
        $enableContentScan = $options['scan_chunk_content'] ?? true;
        
        if ($enableContentScan) {
            $contentCheck = self::scanChunkContent($file['tmp_name']);
            if (!$contentCheck['success']) {
                return $contentCheck;
            }
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Scan chunk content for malicious patterns
     * 
     * SECURITY: Prevent bypassing security by uploading malicious code via chunks
     * 
     * @param string $filePath Chunk file path
     * @return array ['success' => bool, 'error' => string|null]
     */
    private static function scanChunkContent($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return ['success' => true, 'error' => null];
        }
        
        try {
            // Read first 16KB of chunk (enough to detect most attacks)
            $content = @file_get_contents($filePath, false, null, 0, 16384);
            
            if ($content === false) {
                return ['success' => true, 'error' => null];
            }
            
            // Critical patterns that should NEVER be in uploaded files
            $criticalPatterns = [
                '/<\?php/i',           // PHP code
                '/<\?=/i',             // PHP short tags
                '/<script[\s>]/i',     // Script tags
                '/eval\s*\(/i',        // eval() function
                '/base64_decode\s*\(/i', // base64_decode (often used in malware)
                '/system\s*\(/i',      // system() function
                '/exec\s*\(/i',        // exec() function
                '/shell_exec\s*\(/i',  // shell_exec() function
                '/passthru\s*\(/i',    // passthru() function
                '/c99shell/i',         // C99 shell
                '/r57shell/i',         // R57 shell
                '/b374k/i',            // B374k shell
                '/wso\s*shell/i',      // WSO shell
                '/\$_GET\[.*\]\s*\(/i', // Dynamic function calls
                '/\$_POST\[.*\]\s*\(/i',
            ];
            
            foreach ($criticalPatterns as $pattern) {
                if (@preg_match($pattern, $content)) {
                    error_log('Security: Malicious content detected in chunk: ' . $filePath);
                    return [
                        'success' => false,
                        'error' => 'Chunk contains malicious code or malware. Upload rejected for security reasons.'
                    ];
                }
            }
            
            return ['success' => true, 'error' => null];
            
        } catch (\Exception $e) {
            error_log('Chunk content scan error: ' . $e->getMessage());
            // Don't fail on scan error, but log it
            return ['success' => true, 'error' => null];
        }
    }
}
