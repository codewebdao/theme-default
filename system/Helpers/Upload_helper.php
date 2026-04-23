<?php

use System\Libraries\Uploads;
use System\Libraries\Uploads\Chunker;
use System\Libraries\Storages;
use System\Libraries\Imagify;

// ========================================
// CORE UPLOAD FUNCTIONS
// ========================================

if (!function_exists('do_upload')) {
    /**
     * Upload file - Function chính cho mọi loại upload
     * 
     * HỖ TRỢ:
     * - Upload đơn: do_upload($_FILES['file'])
     * - Upload nhiều: do_upload($_FILES['files'])
     * - Upload image với sizes: do_upload($_FILES['image'], ['sizes' => ['200x200', '500x500']])
     * - Save từ base64/URL: do_upload('data:image/png;base64,...', ['save_from' => 'base64', 'filename' => 'image.png'])
     * 
     * @param array|string $file File từ $_FILES hoặc base64/URL string
     * @param array $options Tùy chọn upload:
     * STORAGE OPTIONS:
     *   - 'storage' => string: Driver lưu trữ - 'local'|'s3'|'gcs' (mặc định: 'local')
     *   - 'folder' => string: Thư mục tùy chỉnh (vd: 'avatars', 'documents/2024')
     * 
     * IMAGE PROCESSING OPTIONS:
     *   - 'sizes' => array: Tạo variants theo kích thước ['200x200', '500x500', '1000x1000']
     *   - 'format' => string: Convert sang format khác - 'jpg'|'png'|'webp' (mặc định: giữ nguyên)
     *   - 'webp' => bool: Tạo thêm file .ext.webp cho mỗi size (mặc định: false)
     *   - 'optimize' => bool: Optimize ảnh (giảm dung lượng) (mặc định: false)
     *   - 'quality' => int: Chất lượng ảnh 0-100 (mặc định: 85) 
     *   - 'watermark' => bool: Thêm watermark vào ảnh (mặc định: false)
     *   - 'watermark_img' => string: Đường dẫn ảnh watermark
     *   - 'watermark_scale' => float: Tỷ lệ % của ảnh đích (0.01-1.0) - dễ kiểm soát hiển thị đồng đều
     *   - 'watermark_size' => string: Kích thước cố định "100x50"
     *   - 'watermark_width' => int: Chiều rộng cố định, tự động tính chiều cao
     *   - 'watermark_height' => int: Chiều cao cố định, tự động tính chiều rộng
     * 
     * VALIDATION OPTIONS:
     *   ⚠️ SECURITY NOTE: Validation limits (max_file_size, allowed_types, strict_mime) are
     *      loaded from config('files', 'Uploads') and CANNOT be overridden via options for security.
     * 
     * LƯU Ý BẢO MẬT - CỰC KỲ QUAN TRỌNG:
     *   - 'allowed_types' - KHÔNG thể override (chỉ từ config)
     *   - 'max_file_size' - KHÔNG thể override (chỉ từ config)
     *   - 'strict_mime' - KHÔNG thể override (chỉ từ config)
     *   - 'allowed_mimes' - KHÔNG thể override (chỉ từ config)
     *   → Tất cả security params CHỈ admin có quyền thay đổi trong config file
     * 
     * SAVE FROM STRING OPTIONS:
     *   - 'save_from' => string: Nguồn dữ liệu - 'base64'|'url'|'binary'
     *   - 'filename' => string: Tên file khi save từ string (bắt buộc nếu dùng save_from)
     * 
     * @return array Response chuẩn:
     *   [
     *     'success' => bool,           // true nếu upload thành công
     *     'message' => string,         // Thông báo kết quả
     *     'data' => [                  // Dữ liệu file đã upload
     *       'path' => string,          // Đường dẫn tương đối
     *       'full_path' => string,     // Đường dẫn đầy đủ (local only)
     *       'url' => string,           // URL public của file
     *       'size' => int,             // Kích thước file (bytes)
     *       'name' => string,          // Tên file gốc
     *       'extension' => string,     // Extension
     *       'mime_type' => string,     // MIME type
     *       'sizes' => array,          // Các variants (nếu có)
     *       'webp_version' => array    // WebP version (nếu có)
     *     ]
     *   ]
     * 
     * 
     * // 1. Upload đơn giản
     * $result = do_upload($_FILES['file']);
     * 
     * // 2. Upload với custom folder (validation từ config)
     * $result = do_upload($_FILES['file'], [
     *     'folder' => 'avatars/2024'
     * ]);
     * 
     * // 3. Upload ảnh với sizes
     * $result = do_upload($_FILES['image'], [
     *     'sizes' => ['200x200', '500x500'],
     *     'optimize' => true,
     *     'quality' => 90
     * ]);
     * 
     * // 4. Upload và convert sang WebP
     * $result = do_upload($_FILES['image'], [
     *     'format' => 'webp',
     *     'optimize' => true
     * ]);
     * 
     * // 5. Save từ base64
     * $result = do_upload('data:image/png;base64,iVBORw0KG...', [
     *     'save_from' => 'base64',
     *     'filename' => 'avatar.png'
     * ]);
     * 
     * // 6. Save từ URL
     * $result = do_upload('https://example.com/image.jpg', [
     *     'save_from' => 'url',
     *     'filename' => 'downloaded.jpg'
     * ]);
     * 
     * // 7. Upload lên S3
     * $result = do_upload($_FILES['file'], [
     *     'storage' => 's3',
     *     'folder' => 'uploads/2024'
     * ]);
     */
    function do_upload($file, $options = [])
    {
        $uploads = new Uploads($options['storage'] ?? null);
        
        // Case 1: Save từ base64/URL/binary
        if (is_string($file) && isset($options['save_from'])) {
            return _do_save_from_string($file, $options);
        }
        
        // Case 2: Upload image với bất kỳ image processing options
        // sizes, format, webp, optimize, quality, watermark
        if (isset($options['sizes']) || isset($options['sizes_full']) || isset($options['format']) || isset($options['webp']) 
            || isset($options['optimize']) || isset($options['quality']) || isset($options['watermark'])) {
            return $uploads->uploadImage($file, $options);
        }
        
        // Case 3: Upload đơn giản (auto-detect single/multiple)
        // Uploads::upload() tự động detect multiple files format
        return $uploads->upload($file, $options);
    }
}

if (!function_exists('do_savesizes')) {
    /**
     * Upload single base64 image with optimization
     * Wrapper around do_upload() for base64 strings
     * 
     * @param string $base64Data Base64 image data (with or without data URI prefix)
     * @param string $filename Filename for saved file (e.g., "avatar.jpg")
     * @param array $options Same options as do_upload():
     *   - 'folder' => string: Upload folder
     *   - 'storage' => string: Storage driver (local/s3/gcs)
     *   - 'format' => string: Output format (jpg/png/webp)
     *   - 'quality' => int: Compression quality (0-100)
     *   - 'webp' => bool: Generate WebP variant
     *   - 'optimize' => bool: Optimize image
     * 
     * @return array Same as do_upload() response
     * 
     * EXAMPLE:
     * $result = do_savesizes($base64, 'avatar.jpg', [
     *     'folder' => '2025/01/07',
     *     'quality' => 85,
     *     'webp' => true
     * ]);
     */
    function do_savesizes($base64Data, $filename, $options = [])
    {
        try {
            // 1. Decode base64
            if (strpos($base64Data, 'data:') === 0) {
                $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
            }
            
            $content = base64_decode($base64Data);
            
            if ($content === false || empty($content)) {
                return ['success' => false, 'message' => 'Invalid base64 data'];
            }
            
            // 2. Create temp file
            $tempDir = defined('PATH_TEMP') ? PATH_TEMP : sys_get_temp_dir();
            $randomSuffix = bin2hex(random_bytes(8));
            $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'upload_' . $randomSuffix . '.tmp';
            
            if (@file_put_contents($tempFile, $content) === false) {
                return ['success' => false, 'message' => 'You need create writable/temp folder & chmod 777 this.'];
            }
            
            // 3. Detect MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $tempFile);
            finfo_close($finfo);
            
            // 4. Get extension from format (not from MIME)
            $extension = $options['format'] ?? 'jpg';
            
            // 5. Rename temp file with extension
            $tempFileWithExt = $tempFile . '.' . $extension;
            rename($tempFile, $tempFileWithExt);
            $tempFile = $tempFileWithExt;
            
            // 6. Prepare file array for do_upload
            $fileArray = [
                'name' => $filename,
                'type' => $detectedMime,
                'tmp_name' => $tempFile,
                'error' => 0,
                'size' => filesize($tempFile)
            ];

            // 7. Prepare upload options
            $uploadOptions = [
                'folder' => $options['folder'] ?? date('Y/m/d'),
                'storage' => $options['storage'] ?? 'local',
                'quality' => $options['quality'] ?? 85,
                'optimize' => $options['optimize'] ?? false,
                'webp' => $options['webp'] ?? false,
                'watermark' => $options['watermark'] ?? false,
                'watermark_img' => $options['watermark_img'] ?? null,
                'preserve_filename' => $options['preserve_filename'] ?? false
            ];
            
            // 8. Upload using do_upload() - this handles image processing
            $uploadResult = do_upload($fileArray, $uploadOptions);

            // 9. Cleanup temp file
            @unlink($tempFile);
            
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            
            // 10. Save to database
            $fileData = $uploadResult['data'];
            $saveResult = file_save_unique($fileData, [
                'name' => $filename,
                'size' => $fileData['size'] ?? 0,
                'user_id' => $options['user_id'] ?? 0
            ]);
            
            // 11. Add file_id to result
            $uploadResult['data']['file_id'] = $saveResult['file_id'];
            $uploadResult['data']['is_duplicate'] = $saveResult['is_duplicate'];
            
            // 12. WebP variant is saved to storage only (no database record)
            // The webp field in the main record indicates if WebP variant exists
            
            return $uploadResult;
            
        } catch (\Exception $e) {
            // Cleanup on error
            if (isset($tempFile) && file_exists($tempFile)) {
                @unlink($tempFile);
            }
            
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
        }
    }

/**
 * Helper internal: Save từ string (base64, URL, binary)
 */
function _do_save_from_string($source, $options)
{
    $storage = Storages::make($options['storage'] ?? null);
    $tempFile = null;
    
    try {
        // Detect source type and get content
        if ($options['save_from'] === 'url' || filter_var($source, FILTER_VALIDATE_URL)) {
            // Use secure download function
            $downloadResult = download_url($source, $options);
            if (!$downloadResult['success']) {
                return $downloadResult;
            }
            $content = $downloadResult['content'];
        } elseif ($options['save_from'] === 'base64' || preg_match('/^data:([^;]+);base64,(.+)$/', $source, $matches)) {
            $content = isset($matches[2]) ? base64_decode($matches[2]) : base64_decode($source);
            
            // SECURITY: Validate decoded content
            if ($content === false || empty($content)) {
                return ['success' => false, 'message' => 'Invalid base64 content'];
            }
        } else {
            $content = $source; // Binary
        }
        
        // Create temp file using Storage temp directory (if available)
        // Otherwise use system temp dir
        $tempDir = defined('PATH_TEMP') ? PATH_TEMP : sys_get_temp_dir();
        
        // SECURITY: Use cryptographically secure random for temp filename
        $randomSuffix = bin2hex(random_bytes(8));
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'upload_' . $randomSuffix . '.tmp';
        
        // Write content to temp file
        if (@file_put_contents($tempFile, $content) === false) {
            return ['success' => false, 'message' => 'You need create writable/temp folder & chmod 777 this.'];
        }
        
        // Generate filename
        $filename = $options['filename'] ?? 'file_' . bin2hex(random_bytes(4));
        
        // Upload to storage
        $result = $storage->save($tempFile, $filename);
        
        return $result;
        
    } finally {
        // SECURITY: Always cleanup temp file (even on exception)
        if ($tempFile !== null && file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }
}

// ========================================
// HTTP/DOWNLOAD UTILITIES
// ========================================

if (!function_exists('download_url')) {
    /**
     * Download file từ URL một cách bảo mật
     * 
     * TỰ ĐỘNG:
     * - Ưu tiên dùng cURL extension (nhanh, kiểm soát tốt)
     * - Fallback sang file_get_contents nếu không có cURL
     * 
     * BẢO MẬT:
     * - Validate URL format
     * - Chỉ cho phép HTTP/HTTPS
     * - Block localhost, private IPs (SSRF protection)
     * - Block metadata services (AWS, Azure, GCP)
     * - Block dangerous ports (SSH, DB, Admin panels)
     * - Giới hạn file size (abort ngay khi vượt quá)
     * - Follow redirects an toàn (max 5)
     * - Verify SSL certificates
     * - Timeout protection
     * 
     * @param string $url URL cần download
     * @param array $options Tùy chọn:
     * 
     * TIMEOUT & SIZE:
     *   - 'timeout' => int: Timeout (giây) (mặc định: 30)
     *   - 'max_size' => int: Kích thước tối đa (bytes) (mặc định: 50MB = 52428800)
     * 
     * SECURITY:
     *   - 'verify_ssl' => bool: Verify SSL certificates (mặc định: true)
     *   - 'user_agent' => string: Custom user agent
     * 
     * @return array Response:
     *   [
     *     'success' => bool,      // true nếu download thành công
     *     'content' => string,    // Nội dung file (binary)
     *     'message' => string     // Thông báo lỗi (nếu có)
     *   ]
     * 
     * VÍ DỤ:
     * 
     * // 1. Download đơn giản
     * $result = download_url('https://example.com/image.jpg');
     * if ($result['success']) {
     *     file_put_contents('image.jpg', $result['content']);
     * }
     * 
     * // 2. Download với timeout và size limit
     * $result = download_url('https://example.com/large.zip', [
     *     'timeout' => 60,
     *     'max_size' => 104857600  // 100MB
     * ]);
     * 
     * // 3. Download không verify SSL (không khuyến khích)
     * $result = download_url('https://self-signed.com/file.pdf', [
     *     'verify_ssl' => false
     * ]);
     */
    function download_url($url, $options = [])
    {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'content' => null, 'message' => 'Invalid URL format'];
        }
        
        // Parse URL and check protocol
        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            return ['success' => false, 'content' => null, 'message' => 'Only HTTP/HTTPS protocols are allowed'];
        }
        
        // SECURITY: Block dangerous hosts (SSRF protection)
        $host = strtolower($parsed['host'] ?? '');
        
        // Block localhost, loopback, private IPs
        $blockedHosts = [
            'localhost',
            'localhost.localdomain',
            '127.0.0.1',
            '127.0.0.2',
            '127.1',
            '0.0.0.0',
            '::1',
            '[::1]',
            '0000::1',
            '::ffff:127.0.0.1',
            // Broadcast
            '255.255.255.255',
            // Link-local
            '169.254.0.1',
            'fe80::1',
        ];
        
        if (in_array($host, $blockedHosts)) {
            return ['success' => false, 'content' => null, 'message' => 'Access to localhost is forbidden'];
        }
        
        // Block private IP ranges
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return ['success' => false, 'content' => null, 'message' => 'Access to private/reserved IP ranges is forbidden'];
            }
        }
        
        // Block metadata services (cloud providers)
        $metadataHosts = [
            '169.254.169.254', // AWS, Azure, GCP metadata
            'metadata.google.internal',
            'metadata.azure.com',
        ];
        
        if (in_array($host, $metadataHosts)) {
            return ['success' => false, 'content' => null, 'message' => 'Access to metadata services is forbidden'];
        }
        
        // Block dangerous ports
        $port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);
        $dangerousPorts = [
            // Remote access
            22,    // SSH
            23,    // Telnet
            3389,  // RDP
            5900,  // VNC
            // Email
            25,    // SMTP
            110,   // POP3
            143,   // IMAP
            465,   // SMTPS
            587,   // SMTP submission
            993,   // IMAPS
            995,   // POP3S
            // File sharing
            21,    // FTP
            445,   // SMB
            139,   // NetBIOS
            2049,  // NFS
            // Databases
            1433,  // MSSQL
            1521,  // Oracle
            3306,  // MySQL
            5432,  // PostgreSQL
            5984,  // CouchDB
            6379,  // Redis
            7000,  // Cassandra
            7001,  // Cassandra
            8529,  // ArangoDB
            9042,  // Cassandra
            9200,  // Elasticsearch
            9300,  // Elasticsearch
            27017, // MongoDB
            27018, // MongoDB
            28015, // RethinkDB
            // Message queues
            5672,  // RabbitMQ
            15672, // RabbitMQ Management
            61613, // ActiveMQ
            // Monitoring/Admin
            2375,  // Docker
            2376,  // Docker TLS
            4243,  // Docker
            6443,  // Kubernetes
            8001,  // Kubernetes
            8080,  // Common admin panels
            8443,  // Common admin panels
            9090,  // Prometheus
            // Other services
            11211, // Memcached
            50000, // SAP
        ];
        
        if (in_array($port, $dangerousPorts)) {
            return ['success' => false, 'content' => null, 'message' => 'Access to port ' . $port . ' is forbidden'];
        }
        
        // Extract options
        $timeout = $options['timeout'] ?? 30;
        $maxSize = $options['max_size'] ?? (50 * 1024 * 1024); // 50MB default
        $userAgent = $options['user_agent'] ?? 'Mozilla/5.0 (compatible; FileUploader/2.0)';
        $verifySsl = $options['verify_ssl'] ?? true;
        
        // Priority 1: Use cURL extension if available (best option)
        if (extension_loaded('curl') && function_exists('curl_init')) {
            $ch = curl_init($url);
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
                CURLOPT_ENCODING => '',
                CURLOPT_BUFFERSIZE => 8192,
            ]);
            
            // Progress callback to limit file size during download
            $downloadedSize = 0;
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $downloadSize, $downloaded) use ($maxSize, &$downloadedSize) {
                $downloadedSize = $downloaded;
                return ($downloaded > $maxSize) ? 1 : 0; // Abort if too large
            });
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($content === false) {
                return ['success' => false, 'content' => null, 'message' => 'cURL error: ' . $error];
            }
            
            if ($httpCode !== 200) {
                return ['success' => false, 'content' => null, 'message' => "HTTP error: $httpCode"];
            }
            
            if ($downloadedSize > $maxSize) {
                return ['success' => false, 'content' => null, 'message' => 'File too large'];
            }
            
            return ['success' => true, 'content' => $content, 'message' => null];
        }
        
        // Priority 2: Fallback to file_get_contents (if cURL not available)
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'user_agent' => $userAgent,
                'follow_location' => 1,
                'max_redirects' => 5,
            ],
            'ssl' => [
                'verify_peer' => $verifySsl,
                'verify_peer_name' => $verifySsl,
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        
        if ($content === false) {
            return ['success' => false, 'content' => null, 'message' => 'Download failed (all methods)'];
        }
        
        if (strlen($content) > $maxSize) {
            return ['success' => false, 'content' => null, 'message' => 'File too large'];
        }
        
        return ['success' => true, 'content' => $content, 'message' => null];
    }
}

// ========================================
// FILE MANAGEMENT FUNCTIONS
// ========================================

if (!function_exists('file_save_unique')) {
    /**
     * Lưu file vào database với tự động phát hiện trùng lặp
     * 
     * CHỨC NĂNG:
     * - Tính MD5 hash của file
     * - Kiểm tra file đã tồn tại trong DB chưa (qua MD5)
     * - Nếu trùng: Xóa file mới upload, trả về file cũ (tiết kiệm storage)
     * - Nếu không trùng: Lưu vào DB
     * 
     * LỢI ÍCH:
     * - Tiết kiệm storage (không lưu file trùng)
     * - Tăng tốc upload (file đã có sẵn)
     * - Quản lý tốt hơn
     * 
     * @param array $fileData Dữ liệu file từ kết quả upload (do_upload)
     * @param array $options Tùy chọn:
     *   - 'name' => string: Tên file gốc
     *   - 'size' => int: Kích thước file (bytes)
     *   - 'user_id' => int: ID người upload
     * 
     * @return array Kết quả:
     *   [
     *     'file_id' => int,              // ID của file trong DB
     *     'is_duplicate' => bool,        // true nếu file đã tồn tại
     *     'existing_file' => array|null  // Thông tin file cũ (nếu trùng)
     *   ]
     * 
     * VÍ DỤ:
     * 
     * // 1. Upload và lưu DB với duplicate detection
     * $uploadResult = do_upload($_FILES['file']);
     * if ($uploadResult['success']) {
     *     $dbResult = file_save_unique($uploadResult['data'], [
     *         'name' => $_FILES['file']['name'],
     *         'size' => $_FILES['file']['size'],
     *         'user_id' => current_user_id()
     *     ]);
     *     
     *     if ($dbResult['is_duplicate']) {
     *         echo "File đã tồn tại, dùng lại file cũ ID: " . $dbResult['file_id'];
     *     } else {
     *         echo "File mới được lưu với ID: " . $dbResult['file_id'];
     *     }
     * }
     */
    function file_save_unique($fileData, $options = [])
    {
        // Get path from response
        $filePath = $fileData['relative_path'] ?? $fileData['path'] ?? null;
        
        if (!$filePath) {
            return ['file_id' => null, 'is_duplicate' => false, 'existing_file' => null];
        }
        
        // Calculate MD5
        $md5 = file_md5($filePath);
        
        // Check duplicate by MD5
        // if ($md5) {
        //     $existing = file_get_by_md5($md5);
            
        //     if ($existing) {
        //         // File đã tồn tại, xóa file mới upload (chỉ xóa storage, không xóa DB)
        //         try {
        //             $storageInstance = \System\Libraries\Storages::make($fileData['storage'] ?? 'local');
        //             $storageInstance->delete($filePath);
        //         } catch (\Exception $e) {
        //             // Silent fail - file might already be deleted
        //         }
                
        //         return [
        //             'file_id' => $existing['id'],
        //             'is_duplicate' => true,
        //             'existing_file' => $existing
        //         ];
        //     }
        // }
        
        // Build resize array từ sizes hoặc variants
        $resizeSizes = [];
        if (isset($fileData['sizes']) && is_array($fileData['sizes'])) {
            foreach ($fileData['sizes'] as $size) {
                if (isset($size['size'])) {
                    $resizeSizes[] = $size['size'];
                }
            }
        } elseif (isset($fileData['variants'])) {
            foreach ($fileData['variants'] as $variant) {
                if (isset($variant['width'], $variant['height'])) {
                    $resizeSizes[] = "{$variant['width']}x{$variant['height']}";
                }
            }
        }
        // Check if has WebP variant
        $hasWebp = !empty($fileData['webp_version']['path']) || 
                   (isset($fileData['webp']) && $fileData['webp'] === true);
        
        // Determine final filename for DB (must match actual stored file)
        // Priority: basename from final storage path (already sanitized/uniquified),
        // then fallback to provided name (slugified) if path missing.
        $finalBasename = basename($filePath);
        if (!empty($finalBasename)) {
            // Use as-is to preserve suffix like _1, _2 added by storage
            $fileName = $finalBasename;
        } else {
            $rawName = $options['name'] ?? $fileData['name'] ?? 'file';
            $pi = pathinfo($rawName);
            $base = $pi['filename'] ?? $rawName;
            $ext = $pi['extension'] ?? '';
            // Always use url_slug for consistent filename sanitization
            
            if (function_exists('url_slug')) {
                $safeBase = url_slug($base, [
                    'delimiter' => '-',
                    'limit' => 140,
                    'lowercase' => true
                ]);
            } else {
                // Fallback: use PathSanitizer if url_slug not available
                $safeBase = \System\Libraries\Uploads\PathUtils\PathSanitizer::sanitizeFileName($base, false);
            }
            $fileName = $ext ? ($safeBase . '.' . $ext) : $safeBase;
        }
        // Save to DB
        $fileId = file_save($filePath, [
            'name' => $fileName,
            'type' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => $options['size'] ?? ($fileData['size'] ?? 0),
            'md5' => $md5,
            'storage' => $fileData['storage'] ?? 'local',
            'resize' => !empty($resizeSizes) ? implode(';', $resizeSizes) : null,
            'webp' => $hasWebp ? 1 : 0,
            'user_id' => $options['user_id'] ?? 0
        ]);
        
        return [
            'file_id' => $fileId,
            'is_duplicate' => false,
            'existing_file' => null
        ];
    }
}

// Use file_delete() for deleting files from storage + DB

// ========================================
// CHUNK UPLOAD FUNCTIONS
// ========================================

if (!function_exists('init_chunk')) {
    /**
     * Khởi tạo chunk upload (upload file lớn theo từng phần)
     * 
     * Sử dụng khi upload file lớn (>5MB) để:
     * - Tăng tốc upload
     * - Tránh timeout
     * - Hỗ trợ resume upload
     * 
     * @param string $uploadId Upload ID duy nhất (UUID hoặc random string)
     * @param array $metadata Thông tin file:
     *   - 'fileName' => string: Tên file
     *   - 'fileSize' => int: Kích thước file (bytes)
     *   - 'totalChunks' => int: Tổng số chunks
     *   - 'fileMd5' => string: MD5 hash của file (optional)
     * @param string|null $storage Storage driver (mặc định: 'local')
     * @return array Response
     * 
     * VÍ DỤ:
     * $result = init_chunk('upload_abc123', [
     *     'fileName' => 'video.mp4',
     *     'fileSize' => 104857600,  // 100MB
     *     'totalChunks' => 100
     * ]);
     */
    function init_chunk($uploadId, $metadata, $storage = null)
    {
        $storageInstance = Storages::make($storage);
        return $storageInstance->initChunkUpload($uploadId, $metadata);
    }
}

if (!function_exists('upload_chunk')) {
    /**
     * Upload một chunk (phần) của file
     * 
     * Gọi nhiều lần cho mỗi chunk của file lớn
     * 
     * @param string $uploadId Upload ID (đã khởi tạo bằng init_chunk)
     * @param int $chunkNumber Số thứ tự chunk (bắt đầu từ 1)
     * @param array $file Chunk file từ $_FILES
     * @param array $options Tùy chọn:
     *   - 'totalChunks' => int: Tổng số chunks
     *   - 'fileName' => string: Tên file
     *   - 'fileSize' => int: Kích thước file
     *   - 'fileMd5' => string: MD5 hash
     *   - 'storage' => string: Storage driver
     * 
     * @return array Response:
     *   [
     *     'success' => bool,
     *     'message' => string,
     *     'progress' => float,        // Tiến độ % (0-100)
     *     'uploaded_chunks' => int,   // Số chunks đã upload
     *     'completed' => bool         // true nếu hoàn thành
     *   ]
     * 
     * VÍ DỤ:
     * for ($i = 1; $i <= $totalChunks; $i++) {
     *     $result = upload_chunk('upload_abc123', $i, $_FILES['chunk'], [
     *         'totalChunks' => $totalChunks,
     *         'fileName' => 'video.mp4'
     *     ]);
     *     echo "Progress: {$result['progress']}%";
     * }
     */
    function upload_chunk($uploadId, $chunkNumber, $file, $options = [])
    {
        $chunker = new Chunker($options['storage'] ?? null);
        
        return $chunker->handle([
            'uploadId' => $uploadId,
            'chunkNumber' => $chunkNumber,
            'totalChunks' => $options['totalChunks'] ?? 0,
            'fileName' => $options['fileName'] ?? 'unknown',
            'fileSize' => $options['fileSize'] ?? null,
            'fileMd5' => $options['fileMd5'] ?? null,
        ], $file, $options);
    }
}

if (!function_exists('complete_chunk')) {
    /**
     * Hoàn tất chunk upload và merge các chunks thành file hoàn chỉnh
     * 
     * Gọi sau khi upload hết tất cả chunks
     * 
     * @param string $uploadId Upload ID
     * @param array $options Tùy chọn:
     *   - 'storage' => string: Storage driver
     *   - 'sessionMeta' => array: Metadata bổ sung
     * 
     * @return array Response:
     *   [
     *     'success' => bool,
     *     'message' => string,
     *     'data' => [
     *       'path' => string,      // Đường dẫn file hoàn chỉnh
     *       'url' => string,       // URL public
     *       'size' => int          // Kích thước file
     *     ]
     *   ]
     * 
     * VÍ DỤ:
     * $result = complete_chunk('upload_abc123');
     * if ($result['success']) {
     *     echo "File uploaded: " . $result['data']['url'];
     * }
     */
    function complete_chunk($uploadId, $options = [])
    {
        $chunker = new Chunker($options['storage'] ?? null);
        return $chunker->getProgress($uploadId, $options['sessionMeta'] ?? []);
    }
}

// ========================================
// FILE UTILITIES
// ========================================

if (!function_exists('file_url')) {
    /**
     * Lấy URL public của file
     * 
     * Hỗ trợ tất cả storage drivers: local, S3, GCS
     * 
     * @param string $path Đường dẫn file (relative path)
     * @param string $storage Storage driver (mặc định: 'local')
     * @return string URL public của file
     * 
     * VÍ DỤ:
     * 
     * // 1. Local storage
     * $url = file_url('uploads/2024/image.jpg');
     * // => http://domain.com/uploads/2024/image.jpg
     * 
     * // 2. S3 storage
     * $url = file_url('uploads/2024/image.jpg', 's3');
     * // => https://bucket.s3.amazonaws.com/uploads/2024/image.jpg
     * 
     * // 3. Dùng trong view
     * <img src="<?= file_url($file['path']) ?>" alt="Image">
     */
    function file_url($path, $storage = 'local')
    {
        return Storages::make($storage)->url($path);
    }
}

if (!function_exists('file_delete')) {
    /**
     * Xóa file (cả storage và database)
     * 
     * Tự động:
     * - Xóa file từ storage (local/S3/GCS)
     * - Xóa record trong database
     * - Xóa cả variants (nếu có)
     * 
     * @param string|int $pathOrId File path hoặc file ID
     * @return array Kết quả:
     *   [
     *     'success' => bool,
     *     'message' => string
     *   ]
     * 
     * VÍ DỤ:
     * 
     * // 1. Xóa bằng ID
     * $result = file_delete(123);
     * 
     * // 2. Xóa bằng path
     * $result = file_delete('uploads/2024/image.jpg');
     * 
     * if ($result['success']) {
     *     echo "File đã bị xóa";
     * }
     */
    function file_delete($pathOrId)
    {
        $filesModel = new \App\Models\FilesModel();
        
        $file = is_numeric($pathOrId) 
            ? $filesModel->getFileById($pathOrId)
            : $filesModel->getFileByPath($pathOrId);
        
        if ($file) {
            $storage = Storages::make($file['storage']);
            $deletedFiles = [];
            $errors = [];
            
            // 1. Xóa file gốc
            $result = $storage->delete($file['path']);
            if ($result['success']) {
                $deletedFiles[] = $file['path'];
            } else {
                $errors[] = $file['path'] . ': ' . ($result['error'] ?? 'Unknown error');
            }
            
            // 2. Xóa tất cả variants (sizes + webp)
            $variants = file_get_variants($file);
            foreach ($variants as $variant) {
                $result = $storage->delete($variant);
                if ($result['success']) {
                    $deletedFiles[] = $variant;
                } else {
                    $errors[] = $variant . ': ' . ($result['error'] ?? 'Unknown error');
                }
            }
            
            // 3. Xóa DB record
            $dbResult = $filesModel->deleteFile($file['id']);
            
            $message = "Deleted " . count($deletedFiles) . " files";
            if (!empty($errors)) {
                $message .= " (with " . count($errors) . " errors)";
            }
            
            return [
                'success' => $dbResult > 0,
                'message' => $message,
                'errors' => $errors
            ];
        }
        return ['success' => false, 'message' => 'File not found'];
    }
}

if (!function_exists('file_exists')) {
    /**
     * Kiểm tra file có tồn tại trong storage không
     * 
     * Chỉ kiểm tra storage, KHÔNG kiểm tra database
     * Dùng file_dbexists() để kiểm tra DB
     * 
     * @param string $path Đường dẫn file
     * @param string $storage Storage driver (mặc định: 'local')
     * @return bool true nếu file tồn tại
     * 
     * VÍ DỤ:
     * if (file_exists('uploads/image.jpg')) {
     *     echo "File tồn tại";
     * }
     */
    function file_exists($path, $storage = 'local')
    {
        return Storages::make($storage)->exists($path);
    }
}

if (!function_exists('file_dbexists')) {
    /**
     * Kiểm tra file có tồn tại trong database không
     * 
     * Chỉ kiểm tra database, KHÔNG kiểm tra storage
     * Dùng file_exists() để kiểm tra storage
     * 
     * @param string|int $pathOrId File path hoặc file ID
     * @return bool true nếu file có trong DB
     * 
     * VÍ DỤ:
     * 
     * // Kiểm tra bằng ID
     * if (file_dbexists(123)) {
     *     echo "File ID 123 tồn tại trong DB";
     * }
     * 
     * // Kiểm tra bằng path
     * if (file_dbexists('uploads/image.jpg')) {
     *     echo "File tồn tại trong DB";
     * }
     */
    function file_dbexists($pathOrId)
    {
        $filesModel = new \App\Models\FilesModel();
        
        if (is_numeric($pathOrId)) {
            $file = $filesModel->getFileById($pathOrId);
        } else {
            $file = $filesModel->getFileByPath($pathOrId);
        }
        
        return !empty($file);
    }
}

if (!function_exists('file_info')) {
    /**
     * Lấy thông tin chi tiết của file từ storage
     * 
     * @param string $path Đường dẫn file
     * @param string $storage Storage driver (mặc định: 'local')
     * @return array Thông tin file:
     *   [
     *     'name' => string,      // Tên file
     *     'size' => int,         // Kích thước (bytes)
     *     'type' => string,      // Extension
     *     'mime' => string,      // MIME type
     *     'url' => string,       // URL public
     *     'path' => string       // Đường dẫn
     *   ]
     * 
     * VÍ DỤ:
     * $info = file_info('uploads/2024/document.pdf');
     * echo "File: {$info['name']} - Size: {$info['size']} bytes";
     */
    function file_info($path, $storage = 'local')
    {
        $uploads = new Uploads($storage);
        return $uploads->getInfo($path);
    }
}

if (!function_exists('file_md5')) {
    /**
     * Tính MD5 hash của file
     * 
     * Dùng để:
     * - Kiểm tra file integrity
     * - Phát hiện file trùng lặp
     * - Verify download
     * 
     * @param string $path Đường dẫn file
     * @param string $storage Storage driver (mặc định: 'local')
     * @return string|false MD5 hash (32 ký tự hex) hoặc false nếu lỗi
     * 
     * VÍ Dụ:
     * 
     * // 1. Tính MD5
     * $hash = file_md5('uploads/file.zip');
     * echo "MD5: $hash";
     * 
     * // 2. So sánh 2 files
     * $hash1 = file_md5('file1.jpg');
     * $hash2 = file_md5('file2.jpg');
     * if ($hash1 === $hash2) {
     *     echo "2 files giống nhau";
     * }
     */
    function file_md5($path, $storage = 'local')
    {
        $storageInstance = Storages::make($storage);
        
        // Check if it's a local file path
        if (file_exists($path)) {
            return md5_file($path);
        }
        
        // Get from storage
        $content = $storageInstance->get($path);
        return $content !== false ? md5($content) : false;
    }
}

if (!function_exists('file_get')) {
    /**
     * Lấy file từ DB (by ID hoặc path)
     * 
     * @param string|int $pathOrId File path hoặc ID
     * @return array|null File data
     */
    function file_get($pathOrId)
    {
        $filesModel = new \App\Models\FilesModel();
        
        if (is_numeric($pathOrId)) {
            return $filesModel->getFileById($pathOrId);
        }
        return $filesModel->getFileByPath($pathOrId);
    }
}

if (!function_exists('file_get_by_md5')) {
    /**
     * Lấy file từ DB by MD5
     * 
     * @param string $md5 MD5 hash
     * @return array|null File data
     */
    function file_get_by_md5($md5)
    {
        $filesModel = new \App\Models\FilesModel();
        return $filesModel->getFileByMd5($md5);
    }
}

if (!function_exists('file_save')) {
    /**
     * Lưu file info vào DB
     * 
     * @param string $path File path
     * @param array $data File data
     * @return int|false File ID
     */
    function file_save($path, $data = [])
    {
        $filesModel = new \App\Models\FilesModel();
        
        $fileData = [
            'name' => $data['name'] ?? basename($path),
            'path' => $path,
            'type' => $data['type'] ?? pathinfo($path, PATHINFO_EXTENSION),
            'size' => $data['size'] ?? 0,
            'md5' => $data['md5'] ?? null,
            'storage' => $data['storage'] ?? 'local',
            'resize' => $data['resize'] ?? null,
            'webp' => $data['webp'] ?? 0,
            'user_id' => $data['user_id'] ?? 0
        ];
        
        return $filesModel->addFile($fileData);
    }
}

if (!function_exists('file_update')) {
    /**
     * Update file info trong DB
     * 
     * @param string|int $pathOrId File path hoặc ID
     * @param array $data Data to update
     * @return bool Success
     */
    function file_update($pathOrId, $data = [])
    {
        $filesModel = new \App\Models\FilesModel();
        
        $fileId = is_numeric($pathOrId) ? $pathOrId : null;
        
        if (!$fileId) {
            $file = $filesModel->getFileByPath($pathOrId);
            $fileId = $file['id'] ?? null;
        }
        
        if ($fileId) {
            return $filesModel->updateFile($fileId, $data);
        }
        
        return false;
    }
}

// ========================================
// VARIANTS MANAGEMENT
// ========================================

if (!function_exists('file_get_variants')) {
    /**
     * Get all variants of a file from database (sizes, webp versions)
     * 
     * Now reads from database instead of scanning directories for better performance
     * 
     * @param string|array $filePathOrData File path or file data array
     * @param string|null $storageName Storage name (local, s3, gcs) - auto-detect if null
     * @return array List of variant paths
     */
    function file_get_variants($filePathOrData, $storageName = null)
    {
        // Extract file path and storage
        if (is_array($filePathOrData)) {
            $filePath = $filePathOrData['path'] ?? $filePathOrData['relative_path'] ?? null;
            $storageName = $storageName ?? $filePathOrData['storage'] ?? 'local';
        } else {
            $filePath = $filePathOrData;
            $storageName = $storageName ?? 'local';
        }
        
        // Security: Validate file path
        if (empty($filePath)) {
            return [];
        }
        
        // Security: Prevent path traversal
        if (strpos($filePath, '..') !== false || strpos($filePath, "\0") !== false) {
            error_log('Security: Path traversal attempt detected in file_get_variants: ' . $filePath);
            return [];
        }
        
        // Get file data from database or use provided data
        if (is_array($filePathOrData)) {
            $file = $filePathOrData;
        } else {
            $file = file_get($filePath);
            if (!$file) {
                return [];
            }
        }
        
        $variants = [];
        $pathInfo = pathinfo($filePath);
        $dir = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $ext = $pathInfo['extension'] ?? '';
        
        // The filename from database should already be sanitized
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        
        if (empty($filename) || empty($ext)) {
            return [];
        }
        
        // Build variants from database info
        // All storage types (local, S3, GCS) use the same path structure
        
        // 1. Size variants from resize field
        if (!empty($file['resize'])) {
            $sizes = explode(';', $file['resize']);
            foreach ($sizes as $size) {
                $size = trim($size);
                if (strpos($size, 'x') !== false) {
                    $variantPath = $dir . DIRECTORY_SEPARATOR . $filename . '_' . $size . '.' . $ext;
                    $variants[] = ltrim($variantPath, DIRECTORY_SEPARATOR);
                }
            }
        }
        
        // 2. WebP variants (original + sizes)
        if (!empty($file['webp'])) {
            // Original WebP: image.jpg.webp
            $webpPath = $dir . DIRECTORY_SEPARATOR . $filename . '.' . $ext . '.webp';
            $variants[] = ltrim($webpPath, DIRECTORY_SEPARATOR);
            
            // Size WebP variants: image_200x200.jpg.webp
            if (!empty($file['resize'])) {
                $sizes = explode(';', $file['resize']);
                foreach ($sizes as $size) {
                    $size = trim($size);
                    if (strpos($size, 'x') !== false) {
                        $webpVariantPath = $dir . DIRECTORY_SEPARATOR . $filename . '_' . $size . '.' . $ext . '.webp';
                        $variants[] = ltrim($webpVariantPath, DIRECTORY_SEPARATOR);
                    }
                }
            }
        }
        
        return $variants;
    }
}

if (!function_exists('file_get_best_variant')) {
    /**
     * Get best matching variant for requested size and format
     * 
     * Security features:
     * - Input validation (size format, dimensions)
     * - Format whitelist
     * - Safe regex matching
     * 
     * @param string|array $filePathOrData File path or file data
     * @param string $requestedSize Size string (e.g., "300x200")
     * @param string|null $format Preferred format (webp, jpg, png)
     * @param string|null $storageName Storage name (auto-detect if null)
     * @return string|null Best matching variant path
     */
    function file_get_best_variant($filePathOrData, $requestedSize, $format = null, $storageName = null)
    {
        // Extract file path and storage
        if (is_array($filePathOrData)) {
            $filePath = $filePathOrData['path'] ?? $filePathOrData['relative_path'] ?? null;
            $storageName = $storageName ?? $filePathOrData['storage'] ?? 'local';
        } else {
            $filePath = $filePathOrData;
            $storageName = $storageName ?? 'local';
        }
        
        // Security: Validate file path
        if (empty($filePath)) {
            return null;
        }
        
        // Security: Validate requested size format
        if (!preg_match('/^(\d{1,5})x(\d{1,5})$/', $requestedSize, $matches)) {
            error_log('Security: Invalid size format in file_get_best_variant: ' . $requestedSize);
            return $filePath; // Return original if invalid size
        }
        
        $reqWidth = (int)$matches[1];
        $reqHeight = (int)$matches[2];
        
        // Security: Validate dimensions (max 10000px)
        if ($reqWidth > 10000 || $reqHeight > 10000 || $reqWidth < 1 || $reqHeight < 1) {
            error_log('Security: Invalid dimensions in file_get_best_variant: ' . $requestedSize);
            return $filePath;
        }
        
        // Security: Validate format if specified
        if ($format !== null) {
            $allowedFormats = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
            $format = strtolower($format);
            
            if (!in_array($format, $allowedFormats)) {
                error_log('Security: Invalid format in file_get_best_variant: ' . $format);
                $format = null;
            }
        }
        
        // Get all variants with storage support
        $variants = file_get_variants($filePathOrData, $storageName);
        
        // Add original file
        array_unshift($variants, $filePath);
        
        // Filter by format if specified
        if ($format) {
            $filtered = array_filter($variants, function($path) use ($format) {
                $pathLower = strtolower($path);
                return str_ends_with($pathLower, '.' . $format);
            });
            
            if (!empty($filtered)) {
                $variants = array_values($filtered);
            }
        }
        
        // Find best match
        $bestMatch = null;
        $bestScore = PHP_INT_MAX;
        
        foreach ($variants as $variant) {
            // Security: Validate variant path
            if (strpos($variant, '..') !== false) {
                continue;
            }
            
            // Extract size from filename using safe regex
            if (preg_match('/_(\d{1,5})x(\d{1,5})\./', $variant, $sizeMatches)) {
                $varWidth = (int)$sizeMatches[1];
                $varHeight = (int)$sizeMatches[2];
                
                // Security: Validate extracted dimensions
                if ($varWidth > 10000 || $varHeight > 10000) {
                    continue;
                }
            } else {
                // Original file - use large score for fallback
                $varWidth = 9999;
                $varHeight = 9999;
            }
            
            // Calculate score (prefer exact match or slightly larger)
            $widthDiff = abs($varWidth - $reqWidth);
            $heightDiff = abs($varHeight - $reqHeight);
            $score = $widthDiff + $heightDiff;
            
            // Penalize if smaller than requested
            if ($varWidth < $reqWidth || $varHeight < $reqHeight) {
                $score += 1000;
            }
            
            // Prefer WebP if available (slight bonus)
            if (str_ends_with(strtolower($variant), '.webp')) {
                $score -= 1;
            }
            
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMatch = $variant;
            }
        }
        
        return $bestMatch ?? $filePath;
    }
}

if (!function_exists('file_delete_variants')) {
    /**
     * Delete all variants of a file (keep original)
     * 
     * Security features:
     * - Storage validation
     * - Path verification before deletion
     * - Transaction-like rollback on critical errors
     * 
     * @param string|array $filePathOrData File path or file data
     * @param string|null $storageName Storage name (auto-detect if null)
     * @return array Result with deleted count
     */
    function file_delete_variants($filePathOrData, $storageName = null)
    {
        // Extract storage name
        if (is_array($filePathOrData)) {
            $storageName = $storageName ?? $filePathOrData['storage'] ?? 'local';
        } else {
            $storageName = $storageName ?? 'local';
        }
        
        // Security: Validate storage name
        $allowedStorages = ['local', 's3', 'gcs'];
        if (!in_array($storageName, $allowedStorages)) {
            error_log('Security: Invalid storage name in file_delete_variants: ' . $storageName);
            return [
                'success' => false,
                'deleted_count' => 0,
                'errors' => ['Invalid storage name'],
                'message' => 'Invalid storage'
            ];
        }
        
        // Get all variants with storage support
        $variants = file_get_variants($filePathOrData, $storageName);
        
        if (empty($variants)) {
            return [
                'success' => true,
                'deleted_count' => 0,
                'message' => 'No variants found'
            ];
        }
        
        // Get storage instance
        $storageClass = match($storageName) {
            's3' => \System\Libraries\Storages\S3Storage::class,
            'gcs' => \System\Libraries\Storages\GCSStorage::class,
            default => \System\Libraries\Storages\LocalStorage::class
        };
        
        try {
            $storage = new $storageClass();
        } catch (\Exception $e) {
            error_log('Error creating storage instance: ' . $e->getMessage());
            return [
                'success' => false,
                'deleted_count' => 0,
                'errors' => ['Storage initialization failed'],
                'message' => 'Failed to initialize storage'
            ];
        }
        
        $deleted = 0;
        $errors = [];
        
        foreach ($variants as $variant) {
            // Security: Final path validation before deletion
            if (strpos($variant, '..') !== false || strpos($variant, "\0") !== false) {
                error_log('Security: Invalid path in file_delete_variants: ' . $variant);
                $errors[] = [
                    'path' => $variant,
                    'error' => 'Invalid path'
                ];
                continue;
            }
            
            try {
                $result = $storage->delete($variant);
                if ($result['success']) {
                    $deleted++;
                } else {
                    $errors[] = [
                        'path' => $variant,
                        'error' => $result['error'] ?? 'Unknown error'
                    ];
                }
            } catch (\Exception $e) {
                error_log('Error deleting variant: ' . $e->getMessage());
                $errors[] = [
                    'path' => $variant,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => empty($errors),
            'deleted_count' => $deleted,
            'total_variants' => count($variants),
            'errors' => $errors,
            'message' => "Deleted {$deleted}/" . count($variants) . " variants"
        ];
    }
}

// IMAGE PROCESSING HELPERS
// ========================================

if (!function_exists('image_resize')) {
    /**
     * Resize ảnh nhanh
     * 
     * @param string $sourcePath Source image path
     * @param string $destPath Destination path
     * @param int $width Width
     * @param int $height Height
     * @param string $mode Resize mode
     * @param array $options Options
     * @return bool Success
     */
    function image_resize($sourcePath, $destPath, $width, $height, $mode = 'fit', $options = [])
    {
        $storage = Storages::make($options['storage'] ?? null);
        $imagify = new Imagify(null, $storage);
        
        $imagify->load($sourcePath);
        $imagify->resize($width, $height, $mode);
        
        if ($storage) {
            $result = $imagify->saveToStorage($destPath, $options['quality'] ?? 90);
            return $result['success'] ?? false;
        } else {
            return $imagify->save($destPath, $options['quality'] ?? 90);
        }
    }
}

if (!function_exists('image_webp')) {
    /**
     * Convert ảnh sang WebP
     * 
     * @param string $sourcePath Source path
     * @param string $destPath Destination path
     * @param int $quality Quality
     * @param array $options Options
     * @return bool Success
     */
    function image_webp($sourcePath, $destPath, $quality = 80, $options = [])
    {
        $storage = Storages::make($options['storage'] ?? null);
        $imagify = new Imagify(null, $storage);
        
        $imagify->load($sourcePath);
        $imagify->toWebP($quality);
        
        if ($storage) {
            $result = $imagify->saveToStorage($destPath, $quality);
            return $result['success'] ?? false;
        } else {
            return $imagify->save($destPath, $quality);
        }
    }
}

if (!function_exists('image_optimize')) {
    /**
     * Optimize ảnh (strip metadata, progressive)
     * 
     * @param string $path Image path
     * @param array $options Options
     * @return bool Success
     */
    function image_optimize($path, $options = [])
    {
        $storage = Storages::make($options['storage'] ?? null);
        $imagify = new Imagify(null, $storage);
        
        $imagify->load($path);
        $imagify->optimize($options);
        
        if ($storage) {
            $result = $imagify->saveToStorage($path, $options['quality'] ?? 90);
            return $result['success'] ?? false;
        } else {
            return $imagify->save($path, $options['quality'] ?? 90);
        }
    }
}

if (!function_exists('file_rename')) {
    /**
     * Rename file và tất cả variants của nó
     * 
     * CHỨC NĂNG:
     * - Rename file gốc trong storage
     * - Rename tất cả variants (sizes, webp) trong storage
     * - Update database với tên mới
     * - Rollback nếu có lỗi
     * 
     * @param string|int $pathOrId File path hoặc file ID
     * @param string $newName Tên mới (không có extension)
     * @param array $options Tùy chọn:
     *   - 'storage' => string: Storage driver (auto-detect nếu null)
     * 
     * @return array Kết quả:
     *   [
     *     'success' => bool,
     *     'message' => string,
     *     'data' => [
     *       'id' => int,
     *       'old_name' => string,
     *       'new_name' => string,
     *       'old_path' => string,
     *       'new_path' => string,
     *       'url' => string,
     *       'renamed_variants' => array
     *     ],
     *     'errors' => array
     *   ]
     * 
     * VÍ DỤ:
     * 
     * // 1. Rename bằng ID
     * $result = file_rename(123, 'new-filename');
     * 
     * // 2. Rename bằng path
     * $result = file_rename('uploads/image.jpg', 'new-filename');
     * 
     * // 3. Rename với custom storage
     * $result = file_rename(123, 'new_filename', ['storage' => 's3']);
     * 
     * if ($result['success']) {
     *     echo "Renamed to: " . $result['data']['new_name'];
     *     echo "Variants renamed: " . count($result['data']['renamed_variants']);
     * }
     */
    function file_rename($pathOrId, $newName, $options = [])
    {
        // Get file from database
        $file = file_get($pathOrId);
        if (!$file) {
            return [
                'success' => false,
                'message' => 'File not found',
                'data' => null,
                'errors' => []
            ];
        }
        $oldPath = $file['path'];
        $pathInfo = pathinfo($oldPath);
        $extension = $pathInfo['extension'] ?? '';
        
        // Sanitize new name
        $baseNew = pathinfo($newName, PATHINFO_FILENAME) ?: $newName;
        
        if (function_exists('url_slug')) {
            $safeBase = url_slug($baseNew, [
                'delimiter' => '-',
                'limit' => 140,
                'lowercase' => true
            ]);
        } else {
            $safeBase = preg_replace('/[^a-zA-Z0-9\s-]/', '', strtolower($baseNew));
            $safeBase = preg_replace('/\s+/', '_', $safeBase);
        }
        
        // Build new filename (always keep extension)
        $newFileName = $safeBase;
        if ($extension) {
            $newFileName .= '.' . $extension;
        }
        
        // Build new path
        $newPath = $pathInfo['dirname'] . '/' . $newFileName;
        
        // Get storage instance
        $storageName = $file['storage'] ?? 'local';
        $storage = Storages::make($storageName);
        
        // Check if new path already exists
        if ($storage->exists($newPath)) {
            return [
                'success' => false,
                'message' => 'File with this name already exists',
                'data' => null,
                'errors' => ['Target path already exists: ' . $newPath]
            ];
        }
        
        // Get all variants
        $variants = file_get_variants($file);
        $renamedFiles = [];
        $errors = [];
        $rollbackFiles = [];
        
        try {
            // 1. Rename main file
            $renameResult = $storage->move($oldPath, $newPath);
            if (!$renameResult['success']) {
                throw new \Exception('Failed to rename main file: ' . ($renameResult['error'] ?? 'Unknown error'));
            }
            $renamedFiles[] = ['old' => $oldPath, 'new' => $newPath];
            $rollbackFiles[] = ['old' => $newPath, 'new' => $oldPath];
            
            // 2. Rename all variants
            foreach ($variants as $variantPath) {
                $variantPathInfo = pathinfo($variantPath);
                $variantDir = $variantPathInfo['dirname'];
                $variantFilename = $variantPathInfo['filename'];
                $variantExt = $variantPathInfo['extension'] ?? '';
                
                // Build new variant filename
                $newVariantFilename = $safeBase;
                
                // Check if this is a size variant (contains _WIDTHxHEIGHT)
                if (preg_match('/^(.+)_(\d+x\d+)$/', $variantFilename, $matches)) {
                    $size = $matches[2];
                    $newVariantFilename .= '_' . $size;
                }
                
                // Check if this is a WebP variant (extension is webp)
                if ($variantExt === 'webp') {
                    // For WebP variants, we need to extract the original extension from the full path
                    if (preg_match('/^(.+)\.(\w+)\.webp$/', $variantPath, $webpMatches)) {
                        $baseVariantName = $webpMatches[1];
                        $originalExt = $webpMatches[2];
                        
                        // Rebuild base variant name with new safe base
                        if (preg_match('/^(.+)_(\d+x\d+)$/', $baseVariantName, $sizeMatches)) {
                            $size = $sizeMatches[2];
                            $newVariantFilename = $safeBase . '_' . $size . '.' . $originalExt . '.webp';
                        } else {
                            $newVariantFilename = $safeBase . '.' . $originalExt . '.webp';
                        }
                    } else {
                        // Fallback: just replace the base name
                        $newVariantFilename = $safeBase . '.webp';
                    }
                } else {
                    // Regular variant - add extension
                    $newVariantFilename .= '.' . $variantExt;
                }
                
                $newVariantPath = $variantDir . '/' . $newVariantFilename;
                
                // Check if variant target exists
                if ($storage->exists($newVariantPath)) {
                    $errors[] = "Variant target already exists: {$newVariantPath}";
                    continue;
                }
                
                // Rename variant
                $variantRenameResult = $storage->move($variantPath, $newVariantPath);
                if (!$variantRenameResult['success']) {
                    $errors[] = "Failed to rename variant {$variantPath}: " . ($variantRenameResult['error'] ?? 'Unknown error');
                    continue;
                }
                
                $renamedFiles[] = ['old' => $variantPath, 'new' => $newVariantPath];
                $rollbackFiles[] = ['old' => $newVariantPath, 'new' => $variantPath];
            }
            
            // 3. Update database
            $updateData = [
                'name' => $newFileName,
                'path' => $newPath
            ];
            
            $updateResult = file_update($file['id'], $updateData);
            if (!$updateResult) {
                throw new \Exception('Failed to update database');
            }
            
            // 4. Success response
            return [
                'success' => true,
                'message' => 'File and variants renamed successfully',
                'data' => [
                    'id' => $file['id'],
                    'old_name' => $file['name'],
                    'new_name' => $newFileName,
                    'old_path' => $oldPath,
                    'new_path' => $newPath,
                    'url' => file_url($newPath),
                    'renamed_variants' => $renamedFiles,
                    'total_variants' => count($variants),
                    'renamed_count' => count($renamedFiles)
                ],
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            // Rollback all renamed files
            foreach ($rollbackFiles as $rollback) {
                try {
                    $storage->move($rollback['old'], $rollback['new']);
                } catch (\Exception $rollbackError) {
                    $errors[] = "Rollback failed for {$rollback['old']}: " . $rollbackError->getMessage();
                }
            }
            
            return [
                'success' => false,
                'message' => 'Rename failed: ' . $e->getMessage(),
                'data' => null,
                'errors' => array_merge($errors, [$e->getMessage()])
            ];
        }
    }
}
