<?php
/**
 * Curl_Helper.php
 *
 * Enhanced cURL helper functions with GuzzleHttp-like features:
 * - Comprehensive error handling with HTTP status codes
 * - Retry mechanism with exponential backoff
 * - Connection and request timeouts
 * - Follow redirects with limit
 * - Professional logging
 * - Response structure (status, headers, body)
 * - Input validation
 * - SSL verification (configurable)
 * - User agent and cookie support
 * - Better error messages
 */

if (!function_exists('curl_run')) {
    /**
     * Common cURL execution function with enhanced error handling.
     *
     * @param string $url URL to call.
     * @param array $customOptions Additional cURL options.
     * @param int $timeout Request timeout (seconds).
     * @param int $connectTimeout Connection timeout (seconds).
     * @param int $maxRedirects Maximum redirects to follow (0 = disabled).
     * @param bool $returnResponseStructure If true, returns array with status, headers, body.
     * @return mixed Response content, response structure array, or false on error.
     */
    function curl_run($url, $customOptions = array(), $timeout = 30, $connectTimeout = 10, $maxRedirects = 5, $returnResponseStructure = false)
    {
        // Validate URL
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Invalid URL provided", __FILE__, __LINE__, ['url' => $url]);
            } else {
                error_log("cURL Error: Invalid URL - " . $url);
            }
            return false;
        }

        // Validate timeouts
        $timeout = max(1, min(300, (int)$timeout)); // 1-300 seconds
        $connectTimeout = max(1, min(60, (int)$connectTimeout)); // 1-60 seconds
        $maxRedirects = max(0, min(10, (int)$maxRedirects)); // 0-10 redirects

        $ch = curl_init();
        if ($ch === false) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Failed to initialize cURL", __FILE__, __LINE__);
            } else {
                error_log("cURL Error: Failed to initialize cURL");
            }
            return false;
        }

        // Basic options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_FOLLOWLOCATION => $maxRedirects > 0,
            CURLOPT_MAXREDIRS => $maxRedirects,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => $customOptions[CURLOPT_USERAGENT] ?? ('CMSFullForm/' . (defined('APP_VERSION') ? APP_VERSION : '1.0')),
            CURLOPT_ENCODING => '', // Accept all encodings (gzip, deflate, etc.)
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        // If returnResponseStructure is true, capture headers
        if ($returnResponseStructure) {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $header = trim($header);
                if (!empty($header)) {
                    if (strpos($header, 'HTTP/') === 0) {
                        // Status line
                        $responseHeaders['_status'] = $header;
                    } else {
                        // Header line
                        $parts = explode(':', $header, 2);
                        if (count($parts) === 2) {
                            $key = trim($parts[0]);
                            $value = trim($parts[1]);
                            if (isset($responseHeaders[$key])) {
                                // Multiple headers with same name
                                if (!is_array($responseHeaders[$key])) {
                                    $responseHeaders[$key] = [$responseHeaders[$key]];
                                }
                                $responseHeaders[$key][] = $value;
                            } else {
                                $responseHeaders[$key] = $value;
                            }
                        }
                    }
                }
                return $len;
            });
            $responseHeaders = [];
        }

        // Apply custom options (override defaults if needed)
        foreach ($customOptions as $option => $value) {
            // Skip user agent if already set
            if ($option === CURLOPT_USERAGENT && isset($customOptions[CURLOPT_USERAGENT])) {
                continue;
            }
            curl_setopt($ch, $option, $value);
        }

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $headerSize = $returnResponseStructure ? curl_getinfo($ch, CURLINFO_HEADER_SIZE) : 0;

        // Handle cURL errors
        if ($curlError !== 0) {
            $errorMessage = "cURL Error #{$curlError}: {$curlErrorMessage}";
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error($errorMessage, __FILE__, __LINE__, [
                    'url' => $url,
                    'curl_errno' => $curlError,
                    'curl_error' => $curlErrorMessage,
                ]);
            } else {
                error_log($errorMessage . " - URL: " . $url);
            }
            curl_close($ch);
            return false;
        }

        // Handle HTTP errors (4xx, 5xx)
        if ($httpCode >= 400) {
            $errorMessage = "HTTP Error {$httpCode}";
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::warning($errorMessage, __FILE__, __LINE__, [
                    'url' => $url,
                    'http_code' => $httpCode,
                    'response_length' => strlen($response),
                ]);
            }
            // Don't return false for HTTP errors - let caller decide
        }

        // Log slow requests (> 5 seconds)
        if ($totalTime > 5.0) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::warning("Slow cURL request: {$totalTime}s", __FILE__, __LINE__, [
                    'url' => $url,
                    'time' => $totalTime,
                ]);
            }
        }

        // Return response structure if requested
        if ($returnResponseStructure) {
            // Split headers and body
            $body = $headerSize > 0 ? substr($response, $headerSize) : $response;
            
            curl_close($ch);
            
            return [
                'status' => $httpCode,
                'headers' => $responseHeaders ?? [],
                'body' => $body,
                'content_type' => $contentType,
                'total_time' => $totalTime,
            ];
        }

        curl_close($ch);
        return $response;
    }
}

if (!function_exists('curl_get')) {
    /**
     * Execute GET request with cURL.
     *
     * @param string $url URL to call.
     * @param array $params Query string data.
     * @param array $headers Optional headers array.
     * @param int $timeout Request timeout (seconds).
     * @param int $connectTimeout Connection timeout (seconds).
     * @param int $maxRedirects Maximum redirects to follow.
     * @param bool $returnResponseStructure If true, returns array with status, headers, body.
     * @return mixed Response content, response structure array, or false on error.
     */
    function curl_get($url, $params = array(), $headers = array(), $timeout = 30, $connectTimeout = 10, $maxRedirects = 5, $returnResponseStructure = false)
    {
        // Build query string
        if (!empty($params)) {
            $queryString = http_build_query($params);
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $separator . $queryString;
        }

        $customOptions = array();
        if (!empty($headers)) {
            $customOptions[CURLOPT_HTTPHEADER] = $headers;
        }

        return curl_run($url, $customOptions, $timeout, $connectTimeout, $maxRedirects, $returnResponseStructure);
    }
}

if (!function_exists('curl_post')) {
    /**
     * Execute POST request with cURL.
     *
     * @param string $url URL to call.
     * @param array|string $postData POST data (array or JSON string).
     * @param array $headers Optional headers array.
     * @param int $timeout Request timeout (seconds).
     * @param int $connectTimeout Connection timeout (seconds).
     * @param int $maxRedirects Maximum redirects to follow.
     * @param bool $returnResponseStructure If true, returns array with status, headers, body.
     * @param bool $sendAsJson If true, sends data as JSON with Content-Type: application/json.
     * @return mixed Response content, response structure array, or false on error.
     */
    function curl_post($url, $postData = array(), $headers = array(), $timeout = 30, $connectTimeout = 10, $maxRedirects = 5, $returnResponseStructure = false, $sendAsJson = false)
    {
        $customOptions = array(
            CURLOPT_POST => true,
        );

        // Handle JSON data
        if ($sendAsJson || (is_array($postData) && isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') !== false)) {
            if (is_array($postData)) {
                $postData = json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            if (!isset($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/json';
            }
            $customOptions[CURLOPT_POSTFIELDS] = $postData;
        } else {
            // Regular form data
            $customOptions[CURLOPT_POSTFIELDS] = is_array($postData) ? http_build_query($postData) : $postData;
        }

        // Convert headers array to cURL format
        if (!empty($headers)) {
            $curlHeaders = array();
            foreach ($headers as $key => $value) {
                if (is_numeric($key)) {
                    $curlHeaders[] = $value;
                } else {
                    $curlHeaders[] = $key . ': ' . $value;
                }
            }
            $customOptions[CURLOPT_HTTPHEADER] = $curlHeaders;
        }

        return curl_run($url, $customOptions, $timeout, $connectTimeout, $maxRedirects, $returnResponseStructure);
    }
}

if (!function_exists('curl_post_file')) {
    /**
     * Execute file upload (or send multipart file) with cURL.
     * After execution, if temporary file paths are provided, they will be deleted automatically.
     *
     * @param string $url API upload URL.
     * @param array $postData POST data array (may include CURLFile objects).
     * @param array $tempFiles (Optional) List of temporary files to delete after upload.
     * @param array $headers Optional headers array.
     * @param int $timeout Request timeout (seconds).
     * @param int $connectTimeout Connection timeout (seconds).
     * @param int $maxRedirects Maximum redirects to follow.
     * @param bool $returnResponseStructure If true, returns array with status, headers, body.
     * @return mixed Response content, response structure array, or false on error.
     */
    function curl_post_file($url, $postData = array(), $tempFiles = array(), $headers = array(), $timeout = 60, $connectTimeout = 10, $maxRedirects = 5, $returnResponseStructure = false)
    {
        // Validate files exist
        foreach ($postData as $key => $value) {
            if ($value instanceof CURLFile) {
                if (!file_exists($value->getFilename())) {
                    if (class_exists('\System\Libraries\Logger')) {
                        \System\Libraries\Logger::error("cURL: File not found for upload", __FILE__, __LINE__, [
                            'file' => $value->getFilename(),
                        ]);
                    }
                    return false;
                }
            }
        }

        $customOptions = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
        );

        // Convert headers array to cURL format
        if (!empty($headers)) {
            $curlHeaders = array();
            foreach ($headers as $key => $value) {
                if (is_numeric($key)) {
                    $curlHeaders[] = $value;
                } else {
                    $curlHeaders[] = $key . ': ' . $value;
                }
            }
            $customOptions[CURLOPT_HTTPHEADER] = $curlHeaders;
        }

        $response = curl_run($url, $customOptions, $timeout, $connectTimeout, $maxRedirects, $returnResponseStructure);

        // Clean up temporary files
        if (!empty($tempFiles)) {
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }

        return $response;
    }
}

if (!function_exists('curl_download_file')) {
    /**
     * Download file from URL and save to specified path.
     * Validates file type and size based on Uploads config to prevent DDoS.
     * REQUIRED: allowed_types must be configured in Uploads.php
     * 
     * Note: File extension is validated from $savePath (not URL), because URLs may not have extensions.
     *
     * @param string $url File URL to download.
     * @param string $savePath Path to save file after download (must have valid extension).
     * @param int $timeout Request timeout (seconds).
     * @param int $connectTimeout Connection timeout (seconds).
     * @param callable|null $progressCallback Optional callback for download progress (receives $downloaded, $total).
     * @return mixed Saved file path if successful, false on error.
     */
    function curl_download_file($url, $savePath, $timeout = 300, $connectTimeout = 10, $progressCallback = null)
    {
        // Validate URL
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Invalid URL for download", __FILE__, __LINE__, ['url' => $url]);
            }
            return false;
        }

        // Load Uploads config (REQUIRED)
        $uploadsConfig = function_exists('config') ? config('files', 'Uploads') : null;
        if (empty($uploadsConfig) || !is_array($uploadsConfig)) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Uploads config not found or invalid", __FILE__, __LINE__);
            }
            return false;
        }

        // REQUIRED: allowed_types must be configured
        $allowedTypes = $uploadsConfig['allowed_types'] ?? null;
        if (empty($allowedTypes) || !is_array($allowedTypes)) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: allowed_types not configured in Uploads.php", __FILE__, __LINE__);
            }
            return false;
        }

        // Optional: max_file_size, strict_mime, allowed_mimes
        $maxFileSize = $uploadsConfig['max_file_size'] ?? null;
        $strictMime = $uploadsConfig['strict_mime'] ?? false;
        $allowedMimes = $uploadsConfig['allowed_mimes'] ?? [];

        // REQUIRED: Validate file extension from savePath (not URL, because URL may not have extension)
        $fileExtension = strtolower(pathinfo($savePath, PATHINFO_EXTENSION));
        
        if (empty($fileExtension) || !in_array($fileExtension, $allowedTypes)) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: File type not allowed for download", __FILE__, __LINE__, [
                    'url' => $url,
                    'save_path' => $savePath,
                    'extension' => $fileExtension,
                    'allowed_types' => $allowedTypes,
                ]);
            }
            return false;
        }

        // Validate save path is in PATH_WRITE
        $realSavePath = realpath(dirname($savePath));
        $realWritePath = realpath(PATH_WRITE);
        if ($realWritePath === false || $realSavePath === false || strpos($realSavePath, $realWritePath) !== 0) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Invalid save path for download", __FILE__, __LINE__, [
                    'save_path' => $savePath,
                    'write_path' => PATH_WRITE,
                ]);
            }
            return false;
        }

        // Create directory if it doesn't exist
        $saveDir = dirname($savePath);
        if (!is_dir($saveDir)) {
            if (!@mkdir($saveDir, 0755, true)) {
                if (class_exists('\System\Libraries\Logger')) {
                    \System\Libraries\Logger::error("cURL: Failed to create directory for download", __FILE__, __LINE__, [
                        'directory' => $saveDir,
                    ]);
                }
                return false;
            }
        }

        // First, get headers to check Content-Length (HEAD request)
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true, // HEAD request only
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            
            $headersResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($headersResponse !== false && $httpCode < 400) {
                // Parse Content-Length from headers
                $contentLength = null;
                if (preg_match('/Content-Length:\s*(\d+)/i', $headersResponse, $matches)) {
                    $contentLength = (int)$matches[1];
                }
                
                // Check size before download (prevent DDoS)
                if ($maxFileSize !== null && $contentLength !== null && $contentLength > $maxFileSize) {
                    if (class_exists('\System\Libraries\Logger')) {
                        \System\Libraries\Logger::error("cURL: File size exceeds maximum allowed", __FILE__, __LINE__, [
                            'url' => $url,
                            'content_length' => $contentLength,
                            'max_file_size' => $maxFileSize,
                        ]);
                    }
                    return false;
                }
            }
        }

        // Track downloaded size to stop early if exceeds limit
        $downloadedSize = 0;
        $sizeExceeded = false;

        // Open file handle for streaming write
        $fileHandle = @fopen($savePath, 'wb');
        if ($fileHandle === false) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Failed to open file for writing", __FILE__, __LINE__, [
                    'save_path' => $savePath,
                ]);
            }
            return false;
        }

        // Write function to stream to file with size checking
        $writeFunction = function($ch, $data) use ($fileHandle, &$downloadedSize, &$sizeExceeded, $maxFileSize) {
            $dataLength = strlen($data);
            $downloadedSize += $dataLength;
            
            // Stop download if exceeds max size (prevent DDoS)
            if ($maxFileSize !== null && $downloadedSize > $maxFileSize) {
                $sizeExceeded = true;
                @fclose($fileHandle);
                if (class_exists('\System\Libraries\Logger')) {
                    \System\Libraries\Logger::error("cURL: Download stopped - file size exceeded during download", __FILE__, __LINE__, [
                        'downloaded' => $downloadedSize,
                        'max_file_size' => $maxFileSize,
                    ]);
                }
                return -1; // Stop cURL transfer
            }
            
            $written = @fwrite($fileHandle, $data);
            return $written === false ? -1 : $written;
        };

        $customOptions = array(
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_WRITEFUNCTION => $writeFunction,
        );

        // Add progress callback if provided
        if (is_callable($progressCallback)) {
            $customOptions[CURLOPT_NOPROGRESS] = false;
            $customOptions[CURLOPT_PROGRESSFUNCTION] = function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($progressCallback, $maxFileSize, &$sizeExceeded) {
                // Check size in progress callback too
                if ($maxFileSize !== null && $downloaded > $maxFileSize) {
                    $sizeExceeded = true;
                    return -1; // Stop cURL transfer
                }
                if ($downloadSize > 0) {
                    $progressCallback($downloaded, $downloadSize);
                }
            };
        }

        // Execute download
        $ch = curl_init($url);
        if ($ch === false) {
            @fclose($fileHandle);
            @unlink($savePath);
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'CMSFullForm/' . (defined('APP_VERSION') ? APP_VERSION : '1.0'),
            CURLOPT_ENCODING => '',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        foreach ($customOptions as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        curl_close($ch);
        @fclose($fileHandle);

        // Check for errors
        if ($curlError !== 0 || $result === false || $sizeExceeded) {
            @unlink($savePath);
            if ($sizeExceeded) {
                return false; // Already logged above
            }
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Download failed", __FILE__, __LINE__, [
                    'url' => $url,
                    'curl_errno' => $curlError,
                    'curl_error' => $curlErrorMessage,
                    'http_code' => $httpCode,
                ]);
            }
            return false;
        }

        // Check HTTP status
        if ($httpCode >= 400) {
            @unlink($savePath);
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: HTTP error during download", __FILE__, __LINE__, [
                    'url' => $url,
                    'http_code' => $httpCode,
                ]);
            }
            return false;
        }

        // Validate file size after download
        if ($maxFileSize !== null) {
            $actualSize = @filesize($savePath);
            if ($actualSize !== false && $actualSize > $maxFileSize) {
                @unlink($savePath);
                if (class_exists('\System\Libraries\Logger')) {
                    \System\Libraries\Logger::error("cURL: Downloaded file size exceeds maximum", __FILE__, __LINE__, [
                        'url' => $url,
                        'actual_size' => $actualSize,
                        'max_file_size' => $maxFileSize,
                    ]);
                }
                return false;
            }
        }

        // Double-check file extension after download (safety check)
        $savedFileExtension = strtolower(pathinfo($savePath, PATHINFO_EXTENSION));
        if (empty($savedFileExtension) || !in_array($savedFileExtension, $allowedTypes)) {
            @unlink($savePath);
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Downloaded file type not allowed", __FILE__, __LINE__, [
                    'url' => $url,
                    'save_path' => $savePath,
                    'extension' => $savedFileExtension,
                    'allowed_types' => $allowedTypes,
                ]);
            }
            return false;
        }

        // Validate MIME type if strict_mime is enabled
        if ($strictMime && !empty($allowedMimes) && function_exists('mime_content_type')) {
            $detectedMime = @mime_content_type($savePath);
            if ($detectedMime !== false) {
                $expectedMime = $allowedMimes[$savedFileExtension] ?? null;
                
                if ($expectedMime !== null && $detectedMime !== $expectedMime) {
                    @unlink($savePath);
                    if (class_exists('\System\Libraries\Logger')) {
                        \System\Libraries\Logger::error("cURL: MIME type mismatch", __FILE__, __LINE__, [
                            'url' => $url,
                            'detected_mime' => $detectedMime,
                            'expected_mime' => $expectedMime,
                            'extension' => $fileExtension,
                        ]);
                    }
                    return false;
                }
            }
        }

        return $savePath;
    }
}

if (!function_exists('curl_request_with_retry')) {
    /**
     * Execute cURL request with retry mechanism (exponential backoff).
     * Inspired by GuzzleHttp's retry middleware.
     *
     * @param callable $requestCallback Function that executes the cURL request (should return response or false).
     * @param int $maxRetries Maximum number of retries.
     * @param int $initialDelay Initial delay in seconds before first retry.
     * @param float $backoffMultiplier Multiplier for exponential backoff.
     * @param array $retryableStatusCodes HTTP status codes that should trigger retry.
     * @return mixed Response from request callback or false if all retries failed.
     */
    function curl_request_with_retry($requestCallback, $maxRetries = 3, $initialDelay = 1, $backoffMultiplier = 2.0, $retryableStatusCodes = array(429, 500, 502, 503, 504))
    {
        if (!is_callable($requestCallback)) {
            if (class_exists('\System\Libraries\Logger')) {
                \System\Libraries\Logger::error("cURL: Invalid callback for retry", __FILE__, __LINE__);
            }
            return false;
        }

        $attempt = 0;
        $delay = $initialDelay;

        while ($attempt <= $maxRetries) {
            $response = call_user_func($requestCallback);

            // If response is false, it's a cURL error - retry
            if ($response === false) {
                $attempt++;
                if ($attempt <= $maxRetries) {
                    if (class_exists('\System\Libraries\Logger')) {
                        \System\Libraries\Logger::info("cURL: Retrying request (attempt {$attempt}/{$maxRetries})", __FILE__, __LINE__, [
                            'delay' => $delay,
                        ]);
                    }
                    sleep($delay);
                    $delay = (int)($delay * $backoffMultiplier);
                }
                continue;
            }

            // If response is array with status code, check if retryable
            if (is_array($response) && isset($response['status'])) {
                if (in_array($response['status'], $retryableStatusCodes)) {
                    $attempt++;
                    if ($attempt <= $maxRetries) {
                        if (class_exists('\System\Libraries\Logger')) {
                            \System\Libraries\Logger::info("cURL: Retrying request due to HTTP {$response['status']} (attempt {$attempt}/{$maxRetries})", __FILE__, __LINE__, [
                                'status' => $response['status'],
                                'delay' => $delay,
                            ]);
                        }
                        sleep($delay);
                        $delay = (int)($delay * $backoffMultiplier);
                        continue;
                    }
                }
            }

            // Success or non-retryable error
            return $response;
        }

        // All retries exhausted
        if (class_exists('\System\Libraries\Logger')) {
            \System\Libraries\Logger::error("cURL: All retry attempts exhausted", __FILE__, __LINE__, [
                'max_retries' => $maxRetries,
            ]);
        }
        return false;
    }
}
