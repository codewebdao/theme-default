<?php

namespace System\Libraries\Uploads;

use System\Libraries\Uploads\ChunkUtils\ChunkValidator;
use System\Libraries\Uploads\ChunkUtils\RateLimiter;
use System\Libraries\Responses\UploadResponse;
use System\Libraries\Storages;

/**
 * Chunker - Chunk Upload Manager
 * 
 * Quản lý chunk upload với resume capability qua Storage abstraction:
 * - Nhận và lưu chunks qua Storage driver
 * - Track progress
 * - Merge chunks khi hoàn thành
 * - Resume interrupted uploads
 * - Support LocalStorage, S3, GCS
 * 
 * SECURITY: Rate limiting và chunk limits được load từ config ONLY.
 * Client KHÔNG THỂ override các security parameters.
 * 
 * @package System\Libraries\Uploads
 * @version 2.0.0
 */
class Chunker
{
    private $storage;
    private $config;
    private $sessionMetadata = []; // Store session metadata for S3/GCS
    
    /**
     * Constructor
     * 
     * @param string|null $storageDriver Storage driver name
     */
    public function __construct($storageDriver = null)
    {
        $this->config = config('files', 'Uploads') ?? [];
        $this->storage = Storages::make($storageDriver);
    }
    
    /**
     * Extract safe (non-security) options from options array
     * 
     * SECURITY: Block critical security parameters from being overridden
     * 
     * @param array $options Options array
     * @return array Safe options only
     */
    private function extractSafeOptions($options)
    {
        // CRITICAL SECURITY PARAMETERS - NEVER allow override from client
        $blockedParams = [
            // Rate limiting
            'max_chunk_requests',
            'rate_limit_window',
            'max_concurrent_sessions',
            'enable_rate_limit', // Must come from config
            
            // Chunk limits
            'max_chunks',
            'max_chunk_size',
            'max_total_chunks',
            
            // Other security params
            'scan_chunk_content',
        ];
        
        // Filter out blocked params
        $safeOptions = [];
        foreach ($options as $key => $value) {
            if (!in_array($key, $blockedParams)) {
                $safeOptions[$key] = $value;
            }
        }
        
        return $safeOptions;
    }
    
    /**
     * Handle chunk upload
     * 
     * @param array $chunkInfo Chunk information
     * @param array $file File array from $_FILES
     * @param array $options Options
     * @return array Response
     */
    public function handle($chunkInfo, $file, $options = [])
    {
        // Check if storage supports chunk upload
        if (!$this->storage->supportsChunkUpload()) {
            return UploadResponse::error('Storage driver does not support chunk upload');
        }
        
        // SECURITY: Extract safe options first
        $safeOptions = $this->extractSafeOptions($options);
        
        // SECURITY: Rate limiting check (use config ONLY)
        $enableRateLimit = $this->config['rate_limit_enabled'] ?? true;
        if ($enableRateLimit) {
            $identifier = $this->getIdentifier($safeOptions);
            
            // Build rate limit config from config('files', 'Uploads') ONLY
            $rateLimitConfig = [
                'max_requests' => $this->config['max_chunk_requests'] ?? 100,
                'window' => $this->config['rate_limit_window'] ?? 60,
                'max_sessions' => $this->config['max_concurrent_sessions'] ?? 5
            ];
            
            $rateLimitCheck = RateLimiter::check($identifier, $rateLimitConfig);
            
            if (!$rateLimitCheck['allowed']) {
                return UploadResponse::error($rateLimitCheck['error'], [
                    'retry_after' => $rateLimitCheck['retry_after']
                ]);
            }
        }
        
        // Validate chunk info (pass safe options only)
        $validation = ChunkValidator::validate($chunkInfo, $safeOptions);
        if (!$validation['success']) {
            return UploadResponse::chunkError($validation['error']);
        }
        
        // Validate chunk file (pass safe options only)
        $fileValidation = ChunkValidator::validateChunkFile($file, $safeOptions);
        if (!$fileValidation['success']) {
            return UploadResponse::chunkError($fileValidation['error']);
        }
        
        $uploadId = $chunkInfo['uploadId'];
        $chunkNumber = (int) $chunkInfo['chunkNumber'];
        $totalChunks = (int) $chunkInfo['totalChunks'];
        $fileName = $chunkInfo['fileName'];
        
        // Initialize upload session on first chunk (0-based indexing)
        if ($chunkNumber === 0) {
            // Check if session already exists
            $progressCheck = $this->storage->getChunkUploadProgress($uploadId, []);
            
            // Only init if session doesn't exist
            if (!$progressCheck['success'] || empty($progressCheck['uploaded_chunks'])) {
                // Generate destination path
                $pathUtil = new PathUtil();
                $pathResult = $pathUtil->generateUploadPath(['name' => $fileName], $safeOptions);
                
                if (!$pathResult['success']) {
                    return UploadResponse::chunkError($pathResult['error']);
                }
                
                $metadata = [
                    'file_name' => $fileName,
                    'total_chunks' => $totalChunks,
                    'file_size' => $chunkInfo['fileSize'] ?? null,
                    'file_md5' => $chunkInfo['fileMd5'] ?? null,
                    'destination_path' => $pathResult['data']['relative_path'],
                    'content_type' => $chunkInfo['contentType'] ?? null,
                ];
                
                $initResult = $this->storage->initChunkUpload($uploadId, $metadata);
                if (!$initResult['success']) {
                    return UploadResponse::chunkError($initResult['error']);
                }
                
                // Store metadata for later use (S3/GCS need this)
                $this->sessionMetadata[$uploadId] = array_merge($metadata, $initResult['data'] ?? []);
                
                // Register session for rate limiting
                if ($enableRateLimit) {
                    RateLimiter::registerSession($identifier, $uploadId);
                }
            }
        }
        
        // Get session metadata
        $sessionMeta = $this->sessionMetadata[$uploadId] ?? [];
        
        // If sessionMeta is empty, try to load from storage
        if (empty($sessionMeta)) {
            $progressResult = $this->storage->getChunkUploadProgress($uploadId, []);
            if ($progressResult['success'] && isset($progressResult['metadata'])) {
                $sessionMeta = $progressResult['metadata'];
                $this->sessionMetadata[$uploadId] = $sessionMeta;
            } else {
                return UploadResponse::chunkError('Chunk session not initialized');
            }
        }
        
        // Upload chunk
        $uploadResult = $this->storage->uploadChunk($uploadId, $chunkNumber, $file['tmp_name'], $sessionMeta);
        
        if (!$uploadResult['success']) {
            return UploadResponse::chunkError($uploadResult['error']);
        }
        
        // Get progress
        $progressResult = $this->storage->getChunkUploadProgress($uploadId, $sessionMeta);
        
        if (!$progressResult['success']) {
            return UploadResponse::chunkError($progressResult['error']);
        }
        
        $uploadedChunks = $progressResult['uploaded_chunks'];
        $uploadedCount = count($uploadedChunks);
        
        // Check if complete
        $isComplete = $uploadedCount === $totalChunks;
        
        $progressInfo = [
            'uploaded_chunks' => $uploadedCount,
            'total_chunks' => $totalChunks,
            'is_complete' => $isComplete
        ];
        
        // If complete, merge chunks
        if ($isComplete) {
            $destinationPath = $sessionMeta['destination_path'] ?? '';
            
            // Complete chunk upload (merge)
            $completeResult = $this->storage->completeChunkUpload($uploadId, $destinationPath, $sessionMeta);
            
            if ($completeResult['success']) {
                $progressInfo['file_info'] = $completeResult['data'];
                
                // Clean up session metadata
                unset($this->sessionMetadata[$uploadId]);
            } else {
                return UploadResponse::chunkError($completeResult['error']);
            }
        }
        
        return UploadResponse::chunkProgress($progressInfo);
    }
    
    /**
     * Get upload progress
     * 
     * @param string $uploadId Upload ID
     * @param array $sessionMeta Session metadata (for S3/GCS)
     * @return array Response
     */
    public function getProgress($uploadId, $sessionMeta = [])
    {
        $progressResult = $this->storage->getChunkUploadProgress($uploadId, $sessionMeta);
        
        if (!$progressResult['success']) {
            return UploadResponse::error($progressResult['error']);
        }
        
        $uploadedChunks = $progressResult['uploaded_chunks'];
        $totalChunks = $progressResult['total_chunks'];
        
        // Find missing chunks
        $missingChunks = [];
        for ($i = 0; $i < $totalChunks; $i++) {
            if (!in_array($i, $uploadedChunks)) {
                $missingChunks[] = $i;
            }
        }
        
        return UploadResponse::chunkProgress([
            'uploaded_chunks' => count($uploadedChunks),
            'total_chunks' => $totalChunks,
            'missing_chunks' => $missingChunks,
            'is_complete' => empty($missingChunks)
        ]);
    }
    
    /**
     * Resume upload
     * 
     * @param string $uploadId Upload ID
     * @param array $sessionMeta Session metadata (for S3/GCS)
     * @return array Response
     */
    public function resume($uploadId, $sessionMeta = [])
    {
        return $this->getProgress($uploadId, $sessionMeta);
    }
    
    /**
     * Cancel upload
     * 
     * @param string $uploadId Upload ID
     * @param array $sessionMeta Session metadata (for S3/GCS)
     * @return array Response
     */
    public function cancel($uploadId, $sessionMeta = [])
    {
        $result = $this->storage->abortChunkUpload($uploadId, $sessionMeta);
        
        if ($result['success']) {
            // Clean up session metadata
            unset($this->sessionMetadata[$uploadId]);
            
            // Unregister from rate limiter
            $identifier = $this->getIdentifier([]);
            RateLimiter::unregisterSession($identifier, $uploadId);
            
            return UploadResponse::success([], 'Upload cancelled');
        }
        
        return UploadResponse::error($result['error'] ?? 'Failed to cancel upload');
    }
    
    /**
     * Get identifier for rate limiting
     * 
     * @param array $options Options
     * @return string Identifier (IP address or user ID)
     */
    private function getIdentifier($options = [])
    {
        // Priority 1: Use provided identifier
        if (isset($options['identifier'])) {
            return $options['identifier'];
        }
        
        // Priority 2: Use user ID if available
        if (function_exists('current_user_id')) {
            $userId = current_user_id();
            if ($userId) {
                return 'user_' . $userId;
            }
        }
        
        // Priority 3: Use IP address
        $ip = $this->getClientIp();
        return 'ip_' . $ip;
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private function getClientIp()
    {
        // Check for proxy headers
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Standard proxy header
            'HTTP_X_REAL_IP',           // Nginx proxy
            'REMOTE_ADDR'               // Direct connection
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle multiple IPs (X-Forwarded-For can have multiple)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
}
