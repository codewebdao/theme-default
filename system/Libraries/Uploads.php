<?php

namespace System\Libraries;

use System\Libraries\Uploads\UploadValidator;
use System\Libraries\Uploads\PathUtil;
use System\Libraries\Storages;
use System\Libraries\Imagify;
use System\Libraries\Responses\UploadResponse;

/**
 * Uploads - Thư viện chính xử lý upload files
 * 
 * CHỨC NĂNG CHÍNH:
 * - Upload single/multiple files
 * - Upload images và xử lý hình ảnh (resize, optimize, webp)
 * - Validation đầy đủ (size, type, MIME, security)
 * - Hỗ trợ lưu vào nhiều loại storage (local, S3, GCS)
 * - Auto-detect file format
 * 
 * SỬ DỤNG:
 * 
 * // 1. Upload đơn giản
 * $uploads = new Uploads();
 * $result = $uploads->upload($_FILES['file']);
 * 
 * // 2. Upload lên S3 or GCS
 * $uploads = new Uploads('s3'); // hoặc new Uploads('gcs');
 * $result = $uploads->upload($_FILES['file'], ['folder' => 'avatars']);
 * 
 * // 3. Upload image với sizes
 * $result = $uploads->uploadImage($_FILES['image'], [
 *     'sizes' => ['200x200', '500x500'],
 *     'optimize' => true
 * ]);
 * 
 * @package System\Libraries
 * @version 2.0.0
 */
class Uploads
{
    private $storage;
    private $validator;
    private $pathUtil;
    private $config;

    /**
     * Constructor
     * 
     * @param string|null $storageDriver Storage driver name
     */
    public function __construct($storageDriver = null)
    {
        $this->config = config('files', 'Uploads') ?? [];
        $this->storage = Storages::make($storageDriver);
        $this->validator = new UploadValidator();
        $this->pathUtil = new PathUtil();
    }

    /**
     * Upload file - Tự động detect đơn/nhiều files
     * 
     * @param array $file File từ $_FILES (single hoặc multiple)
     * @param array $options Tùy chọn: folder, allowed_types, max_size, etc.
     * @return array Response ['success' => bool, 'data' => array]
     */
    public function upload($file, $options = [])
    {
        // Auto-detect: Multiple files or single file?
        if ($this->isMultipleFilesFormat($file)) {
            return $this->handleMultipleUpload($file, $options);
        }

        // Single file upload
        return $this->handleSingleUpload($file, $options);
    }

    /**
     * Xử lý upload 1 file
     * 
     * Pipeline: Validate → Generate path → Save → Security check
     * 
     * @param array $file File từ $_FILES
     * @param array $options Tùy chọn upload
     * @return array Response
     */
    private function handleSingleUpload($file, $options = [])
    {
        // Normalize file format (convert multiple format to single if needed)
        $file = $this->normalizeFileArray($file);

        // 1. Validate file (using slugified name to avoid false positives)
        $fileForValidation = $file;
        if (!empty($fileForValidation['name'])) {
            // Preserve extension while slugifying the base name
            $fileForValidation['name'] = $this->pathUtil->sanitizeFileName($fileForValidation['name'], true);
        }
        $validation = $this->validator->validate($fileForValidation, $options);
        if (!$validation['success']) {
            return UploadResponse::validationError(
                $validation['errors'] ?? [$validation['error'] ?? 'Validation failed']
            );
        }

        // 2. Generate path
        $pathResult = $this->pathUtil->generateUploadPath($file, array_merge($options, [
            'storage' => $this->storage
        ]));

        if (!$pathResult['success']) {
            return UploadResponse::error($pathResult['error']);
        }

        // 3. Save to storage
        $saveResult = $this->storage->save(
            $file['tmp_name'],
            $pathResult['data']['relative_path']
        );

        if (!$saveResult['success']) {
            return UploadResponse::error($saveResult['error']);
        }

        // Normalize final paths to ensure DB gets actual stored filename (including _1 suffix when uniquified)
        $finalRelative = $saveResult['data']['relative_path']
            ?? $saveResult['data']['path']
            ?? $pathResult['data']['relative_path'];
        // Ensure saveResult has consistent keys
        $saveResult['data']['relative_path'] = $finalRelative;
        $saveResult['data']['path'] = $finalRelative;
        // 4. SECURITY: Apply post-upload security measures
        $this->applySecurityMeasures($saveResult['data']['full_path'] ?? $finalRelative, $file);

        // 5. Return response
        // Merge with saveResult taking precedence
        return UploadResponse::uploaded(array_merge(
            $pathResult['data'],
            $saveResult['data'] ?? []
        ));
    }

    /**
     * Xử lý upload nhiều files
     * 
     * Upload từng file riêng, trả về kết quả từng file
     * 
     * @param array $files Files từ $_FILES
     * @param array $options Tùy chọn upload
     * @return array Response ['success' => bool, 'data' => array, 'errors' => array]
     */
    private function handleMultipleUpload($files, $options = [])
    {
        $results = [];
        $errors = [];

        // Convert to array of single files
        $fileList = $this->convertToFileList($files);

        foreach ($fileList as $index => $file) {
            $result = $this->handleSingleUpload($file, $options);

            if ($result['success']) {
                $results[] = $result['data'];
            } else {
                $errors[] = [
                    'index' => $index,
                    'file' => $file['name'] ?? 'unknown',
                    'error' => $result['message'] ?? 'Upload failed'
                ];
            }
        }

        if (empty($results)) {
            return UploadResponse::error('All uploads failed', ['errors' => $errors]);
        }

        return UploadResponse::uploaded([
            'files' => $results,
            'errors' => $errors,
            'total' => count($fileList),
            'success_count' => count($results),
            'error_count' => count($errors)
        ]);
    }

    /**
     * Check if file array is multiple files format
     * 
     * @param array $file File array
     * @return bool
     */
    private function isMultipleFilesFormat($file)
    {
        // Check if 'name' is array and has more than 1 file
        if (isset($file['name']) && is_array($file['name'])) {
            return count($file['name']) > 1;
        }

        return false;
    }

    /**
     * Convert multiple files format to array of single files
     * 
     * @param array $files Multiple files array
     * @return array Array of single file arrays
     */
    private function convertToFileList($files)
    {
        $fileList = [];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $fileList[] = [
                'name' => $files['name'][$i] ?? '',
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }

        return $fileList;
    }

    /**
     * Upload ảnh với xử lý (resize, optimize, convert format)
     * 
     * @param array $file File from $_FILES
     * @param array $options Upload options:
     *   - format: 'jpg'|'png'|'webp' - Convert format
     *   - sizes: ['200x200', '500x500'] - Generate sizes
     *   - webp: true - Create .ext.webp for each file (if format != webp)
     *   - optimize: true - Optimize images
     *   - quality: 0-100 - Image quality
     * @return array Response
     */
    public function uploadImage($file, $options = [])
    {
        // Upload original first
        $uploadResult = $this->upload($file, $options);

        if (!$uploadResult['success']) {
            return $uploadResult;
        }

        // Kiểm tra và lấy relative_path từ upload result
        // Xử lý cả 2 trường hợp: single file và multiple files
        $fileData = null;
        $isMultipleFiles = false;
        if (isset($uploadResult['data']['files']) && is_array($uploadResult['data']['files'])) {
            // Multiple files: xử lý từng file
            $isMultipleFiles = true;
            $fileData = $uploadResult['data']['files'];
        } else {
            // Single file: lấy data trực tiếp
            $fileData = [$uploadResult['data']];
        }

        if (!$fileData) {
            return UploadResponse::error('Upload result missing file data');
        }

        // Xử lý từng file để tạo sizes và watermark
        foreach ($fileData as $index => $currentFileData) {
            $originalPath = $currentFileData['relative_path'] ?? $currentFileData['path'] ?? null;
            if (!$originalPath) {
                continue; // Skip file nếu không có path
            }

            $originalExt = pathinfo($originalPath, PATHINFO_EXTENSION);

            try {
                $imagify = new Imagify(null, $this->storage);
                $imagify->load($originalPath);

                // Get target format (default: keep original)
                $targetFormat = $options['format'] ?? $originalExt;
                $quality = $options['quality'] ?? 85;
                $shouldOptimize = $options['optimize'] ?? false;
                $createWebP = $options['webp'] ?? false;
                $addWatermark = $options['watermark'] ?? false;
                $watermarkImg = $options['watermark_img'] ?? null;
                $watermarkSize = $options['watermark_size'] ?? null;
                $watermarkScale = $options['watermark_scale'] ?? null;
                $watermarkWidth = $options['watermark_width'] ?? null;
                $watermarkHeight = $options['watermark_height'] ?? null;
                $watermarkOpacity = $options['watermark_opacity'] ?? null;
                $watermarkPosition = $options['watermark_position'] ?? null;
                $watermarkPadding = $options['watermark_padding'] ?? null;

                // Build watermark options
                $watermarkOptions = [];
                if ($watermarkSize) $watermarkOptions['watermark_size'] = $watermarkSize;
                if ($watermarkScale) $watermarkOptions['watermark_scale'] = $watermarkScale;
                if ($watermarkWidth) $watermarkOptions['watermark_width'] = $watermarkWidth;
                if ($watermarkHeight) $watermarkOptions['watermark_height'] = $watermarkHeight;
                if ($watermarkOpacity) $watermarkOptions['watermark_opacity'] = $watermarkOpacity;
                if ($watermarkPosition) $watermarkOptions['watermark_position'] = $watermarkPosition;
                if ($watermarkPadding) $watermarkOptions['watermark_padding'] = $watermarkPadding;

                // Step 0: Resize current image in-place if explicit single size requested
                // This is independent from sizes generation logic.
                $resizedApplied = false;
                if (!empty($options['resize']) && is_string($options['resize'])) {
                    if (preg_match('/^(\d+)x(\d+)$/', $options['resize'], $matches)) {
                        $targetWidth = (int)$matches[1];
                        $targetHeight = (int)$matches[2];
                        $resizeMode = $options['resize_mode'] ?? 'fill'; // Default to 'fill' (cover)
                        $imagify->load($originalPath);
                        $imagify->resize($targetWidth, $targetHeight, $resizeMode);
                        if ($shouldOptimize) {
                            $imagify->optimize();
                        }
                        $imagify->saveToStorage($originalPath, $quality);
                        $resizedApplied = true;
                    }
                }

                // Step 1: Convert format if needed (NO WATERMARK YET)
                if ($targetFormat !== $originalExt) {
                    $newPath = $this->changeFileExtension($originalPath, $targetFormat);

                    // Convert and save (without watermark)
                    $imagify->toFormat($targetFormat);
                    if ($shouldOptimize) {
                        $imagify->optimize();
                    }
                    // NO WATERMARK HERE - will be added later
                    $imagify->saveToStorage($newPath, $quality);

                    // Delete original if format changed
                    $this->storage->delete($originalPath);

                    // Update result
                    if ($isMultipleFiles) {
                        $uploadResult['data']['files'][$index]['relative_path'] = $newPath;
                        $uploadResult['data']['files'][$index]['path'] = $newPath;
                        $uploadResult['data']['files'][$index]['url'] = $this->storage->url($newPath);
                    } else {
                        $uploadResult['data']['relative_path'] = $newPath;
                        $uploadResult['data']['path'] = $newPath;
                        $uploadResult['data']['url'] = $this->storage->url($newPath);
                    }
                    $uploadResult['data']['format_converted'] = true;
                    $uploadResult['data']['original_format'] = $originalExt;

                    $originalPath = $newPath;
                } else if ($shouldOptimize && !$resizedApplied) {
                    // Just optimize without watermark
                    if ($shouldOptimize) {
                        $imagify->optimize();
                    }
                    // NO WATERMARK HERE - will be added later
                    $imagify->saveToStorage($originalPath, $quality);
                }

                //Tôi nghĩ xử lý resize của các variant saves base64 ở đây không biết đúng không.

                // Step 2: Create WebP version (if format != webp) - NO WATERMARK YET
                if ($createWebP && $targetFormat !== 'webp') {
                    $webpPath = $originalPath . '.webp';
                    $imagify->load($originalPath);
                    $imagify->toWebP($quality);
                    // NO WATERMARK HERE - will be added later
                    $imagify->saveToStorage($webpPath, $quality);

                    $webpData = [
                        'path' => $webpPath,
                        'url' => $this->storage->url($webpPath)
                    ];

                    if ($isMultipleFiles) {
                        $uploadResult['data']['files'][$index]['webp_version'] = $webpData;
                    } else {
                        $uploadResult['data']['webp_version'] = $webpData;
                    }
                }

                // Step 3: Generate sizes if specified
                if (!empty($options['sizes_full'])) {
                    $sizes = [];
                    $defaultResizeMode = $options['resize_mode'] ?? 'fill'; // Default to 'fill' (cover)

                    foreach ($options['sizes_full'] as $sizeKey => $sizeStr) {
                        // Parse size string "200x200" or "name:200x200:mode"
                        $sizeValue = $sizeStr;
                        $resizeMode = $defaultResizeMode;

                        // Check if sizeStr contains mode: "name:200x200:mode"
                        if (strpos($sizeStr, ':') !== false) {
                            $parts = explode(':', $sizeStr);
                            if (count($parts) >= 2) {
                                $sizeValue = $parts[1]; // Get "200x200" part
                                if (count($parts) >= 3) {
                                    $customMode = strtolower(trim($parts[2]));
                                    if (in_array($customMode, ['fit', 'fill', 'crop', 'exact'])) {
                                        $resizeMode = $customMode;
                                    }
                                }
                            }
                        }

                        if (preg_match('/^(\d+)x(\d+)$/', $sizeValue, $matches)) {
                            $width = (int)$matches[1];
                            $height = (int)$matches[2];

                            // Generate size path with key name instead of dimension
                            $sizePath = $this->addSizeToFilename($originalPath, $sizeKey);

                            // Create resized version (NO WATERMARK YET)
                            $imagify->load($originalPath);
                            $imagify->resize($width, $height, $resizeMode);
                            if ($shouldOptimize) {
                                $imagify->optimize();
                            }
                            // NO WATERMARK HERE - will be added later
                            $imagify->saveToStorage($sizePath, $quality);

                            $sizeData = [
                                'name' => $sizeKey,
                                'size' => $sizeStr,
                                'path' => $sizePath,
                                'url' => $this->storage->url($sizePath)
                            ];

                            // Create WebP for this size
                            if ($createWebP && $targetFormat !== 'webp') {
                                $sizeWebpPath = $sizePath . '.webp';
                                $imagify->load($sizePath);
                                $imagify->toWebP($quality);
                                // NO WATERMARK HERE - will be added later
                                $imagify->saveToStorage($sizeWebpPath, $quality);

                                $sizeData['webp'] = [
                                    'path' => $sizeWebpPath,
                                    'url' => $this->storage->url($sizeWebpPath)
                                ];
                            }

                            $sizes[] = $sizeData;
                        }
                    }

                    // Gán sizes vào đúng vị trí trong cấu trúc result
                    if ($isMultipleFiles) {
                        $uploadResult['data']['files'][$index]['sizes'] = $sizes;
                    } else {
                        $uploadResult['data']['sizes'] = $sizes;
                    }
                } else if (!empty($options['sizes'])) {
                    $sizes = [];
                    $defaultResizeMode = $options['resize_mode'] ?? 'fill'; // Default to 'fill' (cover)

                    foreach ($options['sizes'] as $sizeStr) {
                        // Parse size string "200x200"
                        if (preg_match('/^(\d+)x(\d+)$/', $sizeStr, $matches)) {
                            $width = (int)$matches[1];
                            $height = (int)$matches[2];

                            // Generate size path
                            $sizePath = $this->addSizeToFilename($originalPath, $sizeStr);

                            // Create resized version (NO WATERMARK YET)
                            $imagify->load($originalPath);
                            $imagify->resize($width, $height, $defaultResizeMode);
                            if ($shouldOptimize) {
                                $imagify->optimize();
                            }
                            // NO WATERMARK HERE - will be added later
                            $imagify->saveToStorage($sizePath, $quality);

                            $sizeData = [
                                'size' => $sizeStr,
                                'name' => $sizeKey, // Lưu key name (poster, square, banner, etc.)
                                'path' => $sizePath,
                                'url' => $this->storage->url($sizePath)
                            ];

                            // Create WebP for this size
                            if ($createWebP && $targetFormat !== 'webp') {
                                $sizeWebpPath = $sizePath . '.webp';
                                $imagify->load($sizePath);
                                $imagify->toWebP($quality);
                                // NO WATERMARK HERE - will be added later
                                $imagify->saveToStorage($sizeWebpPath, $quality);

                                $sizeData['webp'] = [
                                    'path' => $sizeWebpPath,
                                    'url' => $this->storage->url($sizeWebpPath)
                                ];
                            }

                            $sizes[] = $sizeData;
                        }
                    }

                    // Gán sizes vào đúng vị trí trong cấu trúc result
                    if ($isMultipleFiles) {
                        $uploadResult['data']['files'][$index]['sizes'] = $sizes;
                    } else {
                        $uploadResult['data']['sizes'] = $sizes;
                    }
                }

                // Step 4: Add watermark to all variants (if enabled)
                if ($addWatermark && $watermarkImg) {
                    // Add watermark to original image
                    $imagify->load($originalPath);
                    $imagify->watermark($watermarkImg, $watermarkPosition, $watermarkOpacity, $watermarkOptions);
                    $imagify->saveToStorage($originalPath, $quality);

                    // Add watermark to WebP version (if exists)
                    if ($createWebP && $targetFormat !== 'webp') {
                        $webpPath = $originalPath . '.webp';
                        if ($this->storage->exists($webpPath)) {
                            $imagify->load($webpPath);
                            $imagify->watermark($watermarkImg, $watermarkPosition, $watermarkOpacity, $watermarkOptions);
                            $imagify->saveToStorage($webpPath, $quality);
                        }
                    }

                    // Add watermark to size variants
                    if (!empty($options['sizes_full'])) {
                        foreach ($options['sizes_full'] as $sizeKey => $sizeStr) {
                            if (preg_match('/^(\d+)x(\d+)$/', $sizeStr, $matches)) {
                                $sizePath = $this->addSizeToFilename($originalPath, $sizeKey);

                                // Add watermark to size variant
                                if ($this->storage->exists($sizePath)) {
                                    $imagify->load($sizePath);
                                    $imagify->watermark($watermarkImg, $watermarkPosition, $watermarkOpacity, $watermarkOptions);
                                    $imagify->saveToStorage($sizePath, $quality);
                                }

                                // Add watermark to size WebP variant (if exists)
                                if ($createWebP && $targetFormat !== 'webp') {
                                    $sizeWebpPath = $sizePath . '.webp';
                                    if ($this->storage->exists($sizeWebpPath)) {
                                        $imagify->load($sizeWebpPath);
                                        $imagify->watermark($watermarkImg, $watermarkPosition, $watermarkOpacity, $watermarkOptions);
                                        $imagify->saveToStorage($sizeWebpPath, $quality);
                                    }
                                }
                            }
                        }
                    } else if (!empty($options['sizes'])) {
                        foreach ($options['sizes'] as $sizeStr) {
                            if (preg_match('/^(\d+)x(\d+)$/', $sizeStr, $matches)) {
                                $sizePath = $this->addSizeToFilename($originalPath, $sizeStr);

                                // Add watermark to size variant
                                if ($this->storage->exists($sizePath)) {
                                    $imagify->load($sizePath);
                                    $imagify->watermark($watermarkImg, $watermarkPosition, $watermarkOpacity, $watermarkOptions);
                                    $imagify->saveToStorage($sizePath, $quality);
                                }

                                // Add watermark to size WebP variant (if exists)
                                if ($createWebP && $targetFormat !== 'webp') {
                                    $sizeWebpPath = $sizePath . '.webp';
                                    if ($this->storage->exists($sizeWebpPath)) {
                                        $imagify->load($sizeWebpPath);
                                        $imagify->watermark($watermarkImg, $watermarkPosition, $watermarkOpacity, $watermarkOptions);
                                        $imagify->saveToStorage($sizeWebpPath, $quality);
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Processing failed but original upload succeeded
                if ($isMultipleFiles) {
                    $uploadResult['data']['files'][$index]['processing_error'] = $e->getMessage();
                } else {
                    $uploadResult['data']['processing_error'] = $e->getMessage();
                }
            }
        }

        return $uploadResult;
    }

    /**
     * Change file extension
     * 
     * @param string $path Original path
     * @param string $newExt New extension
     * @return string New path
     */
    private function changeFileExtension($path, $newExt)
    {
        $pathInfo = pathinfo($path);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $newExt;
    }

    /**
     * Add size suffix to filename
     * 
     * @param string $path Original path
     * @param string $size Size string (e.g., "200x200")
     * @return string New path with size
     */
    private function addSizeToFilename($path, $size)
    {
        $pathInfo = pathinfo($path);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
    }

    /**
     * Xóa file khỏi storage
     * 
     * @param string $path Đường dẫn file
     * @return array Response ['success' => bool, 'message' => string]
     */
    public function delete($path)
    {
        $result = $this->storage->delete($path);

        if ($result['success']) {
            return UploadResponse::success([], 'File deleted successfully');
        }

        return UploadResponse::error($result['error'] ?? 'Delete failed');
    }

    /**
     * Lấy thông tin file (size, URL, MIME, last modified)
     * 
     * @param string $path Đường dẫn file
     * @return array Response ['success' => bool, 'data' => array]
     */
    public function getInfo($path)
    {
        if (!$this->storage->exists($path)) {
            return UploadResponse::notFound('File not found');
        }

        return UploadResponse::success([
            'path' => $path,
            'url' => $this->storage->url($path),
            'size' => $this->storage->size($path),
            'last_modified' => $this->storage->lastModified($path),
            'mime_type' => $this->storage->mimeType($path)
        ]);
    }

    /**
     * Normalize file array format
     * Convert multiple upload format to single file format
     * 
     * @param array $file File array
     * @return array Normalized file array
     */
    private function normalizeFileArray($file)
    {
        // Check if it's multiple upload format (arrays inside)
        if (isset($file['name']) && is_array($file['name'])) {
            // Convert to single file format (take first file)
            return [
                'name' => $file['name'][0] ?? '',
                'type' => $file['type'][0] ?? '',
                'tmp_name' => $file['tmp_name'][0] ?? '',
                'error' => $file['error'][0] ?? UPLOAD_ERR_NO_FILE,
                'size' => $file['size'][0] ?? 0,
            ];
        }

        // Already in single file format
        return $file;
    }

    /**
     * Apply security measures after file upload
     * 
     * SECURITY MEASURES:
     * 1. Strip EXIF metadata from images (privacy protection)
     * 2. Sanitize SVG files (XSS prevention)
     * 
     * @param string $filePath Uploaded file path
     * @param array $file Original file array
     * @return void
     */
    private function applySecurityMeasures($filePath, $file)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // 1. Strip EXIF from images (privacy protection)
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $imageExtensions)) {
            try {
                Imagify::autoStripExif($filePath, $this->storage);
            } catch (\Exception $e) {
                error_log('EXIF strip failed: ' . $e->getMessage());
                // Don't fail upload, just log the error
            }
        }

        // 2. Sanitize SVG files (XSS prevention)
        if ($ext === 'svg') {
            try {
                $validator = new \System\Libraries\Uploads\Validators\SecurityValidator();
                $result = $validator->sanitizeSvgFile($filePath, [
                    'remove_external_refs' => true
                ]);

                if ($result['success'] && !empty($result['removed'])) {
                    error_log('SVG sanitized: ' . $filePath . ' - Removed: ' . implode(', ', $result['removed']));
                }
            } catch (\Exception $e) {
                error_log('SVG sanitization failed: ' . $e->getMessage());
                // Don't fail upload, just log the error
            }
        }
    }
}
