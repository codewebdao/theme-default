<?php

namespace App\Controllers\Api\V2;

use App\Controllers\ApiController;
use App\Models\FilesModel;
use System\Libraries\System\Libraries\Logger;

/**
 * FilesController V2 - Modern Upload Controller
 * 
 * Hỗ trợ:
 * - Multipart form-data upload
 * - Chunk upload với resume
 * - Base64/URL upload
 * - JSON API
 * - Auto detect request type
 * 
 * @package Application\Controllers\V2
 * @version 2.0.0
 */
class FilesController extends ApiController
{
    private $filesModel;
    private $filesControllerName;

    public function __construct()
    {
        parent::__construct();
        load_helpers(['upload', 'string', 'query']);
        $this->filesModel = new FilesModel();
        $this->filesControllerName = 'App\Controllers\Backend\FilesController';
    }

    /**
     * Main upload endpoint - Auto detect request type
     * 
     * Detect:
     * - multipart/form-data: Standard file upload
     * - application/json: Base64/URL upload
     * - Chunk upload: Check for chunk metadata
     */
    public function upload()
    {
        // Check permission
        try {
            $this->requirePermission('add', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        // Detect request type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Case 1: Chunk upload (có chunk metadata)
        if (HAS_POST('chunkNumber')) {
            return $this->uploadChunk();
        }

        // Case 2: JSON request (base64/URL)
        if (strpos($contentType, 'application/json') !== false) {
            return $this->uploadFromJson();
        }

        // Case 3: Standard multipart upload
        if (!empty($_FILES)) {
            return $this->uploadStandard();
        }

        // Case 4: Raw binary upload
        if (strpos($contentType, 'application/octet-stream') !== false) {
            return $this->uploadBinary();
        }

        return $this->error('Invalid request type');
    }

    /**
     * Standard file upload (multipart/form-data)
     */
    private function uploadStandard()
    {
        $payload = $this->buildUploadPayload();

        if ($payload === null) {
            return $this->error('No file uploaded');
        }

        $options = $this->buildUploadOptions();
        $result = do_upload($payload['data'], $options);

        if (!$result['success']) {
            // Return error response if upload failed
            $message = $result['message'] ?? 'Upload failed';
            $errors = $result['errors'] ?? [$result['error'] ?? 'Unknown error'];
            return $this->error($message, $errors, 400);
        }

        $result = $this->persistUploadResults($result, $payload['meta']);

        return $this->success($result, 'Upload successful');
    }

    /**
     * Chuẩn hóa dữ liệu upload và metadata từ $_FILES
     */
    private function buildUploadPayload(): ?array
    {
        if (empty($_FILES)) {
            return null;
        }

        $files = [];

        foreach ($_FILES as $file) {
            if (!isset($file['name'])) {
                continue;
            }

            if (is_array($file['name'])) {
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $error = $file['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($error === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $files[] = [
                        'name' => $file['name'][$i] ?? '',
                        'type' => $file['type'][$i] ?? '',
                        'tmp_name' => $file['tmp_name'][$i] ?? '',
                        'error' => $error,
                        'size' => $file['size'][$i] ?? 0,
                    ];
                }
            } else {
                $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
                if ($error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $files[] = [
                    'name' => $file['name'] ?? '',
                    'type' => $file['type'] ?? '',
                    'tmp_name' => $file['tmp_name'] ?? '',
                    'error' => $error,
                    'size' => $file['size'] ?? 0,
                ];
            }
        }

        if (empty($files)) {
            return null;
        }

        if (count($files) === 1) {
            return [
                'data' => $files[0],
                'meta' => [[
                    'name' => $files[0]['name'] ?? null,
                    'size' => $files[0]['size'] ?? 0,
                ]],
            ];
        }

        return [
            'data' => [
                'name' => array_column($files, 'name'),
                'type' => array_column($files, 'type'),
                'tmp_name' => array_column($files, 'tmp_name'),
                'error' => array_column($files, 'error'),
                'size' => array_column($files, 'size'),
            ],
            'meta' => array_map(function ($file) {
                return [
                    'name' => $file['name'] ?? null,
                    'size' => $file['size'] ?? 0,
                ];
            }, $files),
        ];
    }

    /**
     * Đọc options upload từ request
     */
    private function buildUploadOptions(): array
    {
        $options = [];

        if (!empty(S_POST('storage'))) {
            $options['storage'] = S_POST('storage');
        }

        if (!empty(S_POST('folder'))) {
            $options['folder'] = S_POST('folder');
        }

        if (HAS_POST('sizes')) {
            $sizes = S_POST('sizes');

            if (is_string($sizes)) {
                $sizes = trim($sizes);
                if ($sizes !== '') {
                    $decoded = $sizes[0] === '[' ? json_decode($sizes, true) : explode(',', $sizes);
                    if (is_array($decoded)) {
                        $sizes = $decoded;
                    }
                }
            }

            if (is_array($sizes)) {
                $sizes = array_values(array_filter(array_map('trim', $sizes)));
                if (!empty($sizes)) {
                    $options['sizes'] = $sizes;
                }
            }
        }

        if (!empty(S_POST('sizes_full'))) {
            $sizes_full = explode(';', S_POST('sizes_full'));
            foreach ($sizes_full as $size) {
                $size = explode(':', $size);
                if (count($size) == 2) {
                    $options['sizes_full'][$size[0]] = $size[1];
                } else {
                    $options['sizes_full'][$size[0]] = $size[0];
                }
            }
        }

        if (!empty(S_POST('format'))) {
            $options['format'] = S_POST('format');
        }

        foreach (['webp', 'optimize'] as $flag) {
            if (HAS_POST($flag)) {
                $value = filter_var(S_POST($flag), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($value !== null) {
                    $options[$flag] = $value;
                }
            }
        }

        if (HAS_POST('quality') && S_POST('quality') !== '') {
            $options['quality'] = (int) S_POST('quality');
        }

        // Watermark options
        if (HAS_POST('watermark')) {
            $watermark = filter_var(S_POST('watermark'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($watermark !== null) {
                $options['watermark'] = $watermark;
            }
        }

        if (!empty(S_POST('watermark_img'))) {
            if (strpos(PATH_WRITE . S_POST('watermark_img'), PATH_UPLOADS) !== false) {
                $options['watermark_img'] = PATH_WRITE . S_POST('watermark_img');
            } else {
                $options['watermark_img'] = PATH_UPLOADS . S_POST('watermark_img');
            }
        }

        // Watermark size options
        if (!empty(S_POST('watermark_size'))) {
            $options['watermark_size'] = S_POST('watermark_size');
        }
        if (!empty(S_POST('watermark_scale'))) {
            $options['watermark_scale'] = (float) S_POST('watermark_scale'); // % of target image (better UX)
        }
        if (!empty(S_POST('watermark_width'))) {
            $options['watermark_scale'] = (int) S_POST('watermark_width') / 100;
        }
        if (!empty(S_POST('watermark_height'))) {
            $options['watermark_height'] = (int) S_POST('watermark_height');
        }
        if (!empty(S_POST('watermark_opacity'))) {
            $options['watermark_opacity'] = (int) S_POST('watermark_opacity');
        }
        if (!empty(S_POST('watermark_position'))) {
            $options['watermark_position'] = S_POST('watermark_position');
        }
        if (!empty(S_POST('watermark_padding'))) {
            $options['watermark_padding'] = (int) S_POST('watermark_padding');
        }

        // Resize mode options (fit, fill, crop, exact)
        // fit = contain (fit trong khung, giữ tỷ lệ)
        // fill = cover (fill khung, crop nếu cần) - mặc định
        // crop = crop từ center
        // exact = scale (resize chính xác, bỏ qua tỷ lệ)
        if (!empty(S_POST('resize_mode'))) {
            $resizeMode = strtolower(trim(S_POST('resize_mode')));
            $allowedModes = ['fit', 'fill', 'crop', 'exact'];
            if (in_array($resizeMode, $allowedModes)) {
                $options['resize_mode'] = $resizeMode;
            }
        }

        // Resize mode cho từng size variant (nếu sizes_full có format: "name:200x200:mode")
        if (!empty($options['sizes_full'])) {
            foreach ($options['sizes_full'] as $key => $sizeStr) {
                // Format: "name:200x200:mode" hoặc "name:200x200"
                $parts = explode(':', $sizeStr);
                if (count($parts) >= 2) {
                    $sizeValue = $parts[1];
                    $mode = count($parts) >= 3 ? strtolower(trim($parts[2])) : null;
                    if ($mode && in_array($mode, ['fit', 'fill', 'crop', 'exact'])) {
                        $options['sizes_full'][$key] = $parts[0] . ':' . $sizeValue . ':' . $mode;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Lưu kết quả upload vào DB, xử lý duplicate và trả về dữ liệu đã chuẩn hóa
     * Chỉ lưu file gốc, WebP và size variants chỉ lưu vào storage
     */
    private function persistUploadResults(array $result, array $filesMeta = []): array
    {
        if (!($result['success'] ?? false)) {
            return $result;
        }

        $userId = current_user_id();

        if (isset($result['data']['files']) && is_array($result['data']['files'])) {
            $persisted = [];
            $duplicates = 0;

            foreach ($result['data']['files'] as $index => $fileData) {
                // Skip WebP variants - chỉ lưu file gốc
                if (isset($fileData['path']) && strpos($fileData['path'], '.webp') !== false) {
                    continue;
                }
                $meta = $filesMeta[$index] ?? [];
                $saveResult = file_save_unique($fileData, [
                    'name' => $meta['name'] ?? ($fileData['name'] ?? null),
                    'size' => $meta['size'] ?? ($fileData['size'] ?? 0),
                    'user_id' => $userId
                ]);

                if ($saveResult['is_duplicate']) {
                    $duplicates++;
                    $persisted[] = $this->buildExistingFileResponse($saveResult['existing_file']);
                } else {
                    $fileData['file_id'] = $saveResult['file_id'];
                    $fileData['is_duplicate'] = false;

                    // Update file record with size variants if exists
                    if (!empty($fileData['sizes'])) {
                        $sizeVariants = [];
                        foreach ($fileData['sizes'] as $sizeData) {
                            if (isset($sizeData['name'])) {
                                // New format with key names
                                $sizeVariants[] = $sizeData['name'] . ':' . $sizeData['size'];
                            } else {
                                // Old format
                                $sizeVariants[] = $sizeData['size'];
                            }
                        }
                        $this->updateFileWithVariants($saveResult['file_id'], $sizeVariants, !empty($fileData['webp_version']));
                    }

                    $persisted[] = $fileData;
                }
            }

            $result['data']['files'] = $persisted;

            if ($duplicates > 0) {
                $result['message'] = $duplicates === count($persisted)
                    ? 'All uploaded files already exist (duplicates detected)'
                    : 'Upload completed with some duplicates skipped';
            }

            return $result;
        }

        $meta = $filesMeta[0] ?? [];
        $saveResult = file_save_unique($result['data'], [
            'name' => $meta['name'] ?? ($result['data']['name'] ?? null),
            'size' => $meta['size'] ?? ($result['data']['size'] ?? 0),
            'user_id' => $userId
        ]);

        if ($saveResult['is_duplicate']) {
            $result['message'] = 'File has been uploaded before (duplicate detected)';
            $result['data'] = $this->buildExistingFileResponse($saveResult['existing_file']);
        } else {
            $result['data']['file_id'] = $saveResult['file_id'];
            $result['data']['is_duplicate'] = false;

            // Update file record with size variants if exists
            if (!empty($result['data']['sizes'])) {
                $sizeVariants = [];
                foreach ($result['data']['sizes'] as $sizeData) {
                    if (isset($sizeData['name'])) {
                        // New format with key names
                        $sizeVariants[] = $sizeData['name'] . ':' . $sizeData['size'];
                    } else {
                        // Old format
                        $sizeVariants[] = $sizeData['size'];
                    }
                }
                $this->updateFileWithVariants($saveResult['file_id'], $sizeVariants, !empty($result['data']['webp_version']));
            }
        }

        return $result;
    }

    /**
     * Upload từ JSON (base64 hoặc URL)
     */
    private function uploadFromJson()
    {
        $json = json_decode(file_get_contents('php://input'), true);

        if (!isset($json['data'])) {
            return $this->error('Missing data field');
        }

        $source = $json['data'];
        $filename = $json['filename'] ?? 'file_' . uniqid();
        $storage = $json['storage'] ?? 'local';
        $saveFrom = $json['save_from'] ?? 'base64'; // base64, url, binary

        // Upload
        $result = do_upload($source, [
            'save_from' => $saveFrom,
            'filename' => $filename,
            'storage' => $storage
        ]);

        if (!$result['success']) {
            // Return error response if upload failed
            $message = $result['message'] ?? 'Upload failed';
            $errors = $result['errors'] ?? [$result['error'] ?? 'Unknown error'];
            return $this->error($message, $errors, 400);
        }

        // Lưu vào DB và check duplicate
        $saveResult = file_save_unique($result['data'], [
            'user_id' => current_user_id()
        ]);

        // If duplicate, replace response data with existing file
        if ($saveResult['is_duplicate']) {
            $result['message'] = 'File has been uploaded before (duplicate detected)';
            $result['data'] = $this->buildExistingFileResponse($saveResult['existing_file']);
        } else {
            // Add file_id to response for new file
            $result['data']['file_id'] = $saveResult['file_id'];
        }

        return $this->success($result, 'Upload successful');
    }

    /**
     * Upload binary stream
     */
    private function uploadBinary()
    {
        $filename = $_SERVER['HTTP_X_FILENAME'] ?? 'file_' . uniqid();
        $storage = $_SERVER['HTTP_X_STORAGE'] ?? 'local';

        // Read raw input
        $data = file_get_contents('php://input');

        if (empty($data)) {
            return $this->error('No data received');
        }

        // Upload
        $result = do_upload($data, [
            'save_from' => 'binary',
            'filename' => $filename,
            'storage' => $storage
        ]);

        if (!$result['success']) {
            // Return error response if upload failed
            $message = $result['message'] ?? 'Upload failed';
            $errors = $result['errors'] ?? [$result['error'] ?? 'Unknown error'];
            return $this->error($message, $errors, 400);
        }

        // Lưu vào DB và check duplicate
        $saveResult = file_save_unique($result['data'], [
            'user_id' => current_user_id()
        ]);

        // If duplicate, replace response data with existing file
        if ($saveResult['is_duplicate']) {
            $result['message'] = 'File has been uploaded before (duplicate detected)';
            $result['data'] = $this->buildExistingFileResponse($saveResult['existing_file']);
        } else {
            // Add file_id to response for new file
            $result['data']['file_id'] = $saveResult['file_id'];
        }

        return $this->success($result, 'Upload successful');
    }

    /**
     * Chunk upload handler
     */
    private function uploadChunk()
    {
        $uploadId = S_POST('uploadId') ?? null;
        $chunkNumber = (int) S_POST('chunkNumber') ?? 0;
        $totalChunks = (int) S_POST('totalChunks') ?? 0;
        $fileName = S_POST('fileName') ?? null;

        if (!$uploadId || !$fileName) {
            return $this->error('Missing chunk metadata');
        }

        // Get chunk file
        $chunkFile = $_FILES['chunk'] ?? null;
        if (!$chunkFile) {
            return $this->error('No chunk file uploaded');
        }

        // Upload chunk
        $result = upload_chunk($uploadId, $chunkNumber, $chunkFile, [
            'totalChunks' => $totalChunks,
            'fileName' => $fileName,
            'storage' => S_POST('storage') ?? 'local'
        ]);

        if (!$result['success']) {
            // Return error response if upload failed
            $message = $result['message'] ?? 'Upload failed';
            $errors = $result['errors'] ?? [$result['error'] ?? 'Unknown error'];
            return $this->error($message, $errors, 400);
        }

        // Nếu upload hoàn tất, lưu vào DB
        if (isset($result['data']['is_complete']) && $result['data']['is_complete']) {
            // Get file info from result
            $fileInfo = $result['data']['file'] ?? [];

            if (!empty($fileInfo)) {
                // Save to database và check duplicate
                $saveResult = file_save_unique($fileInfo, [
                    'name' => $fileName,
                    'size' => $fileInfo['size'] ?? 0,
                    'user_id' => current_user_id()
                ]);

                // If duplicate, replace file data with existing file
                if ($saveResult['is_duplicate']) {
                    $existingFile = $saveResult['existing_file'];

                    $result['message'] = 'File has been uploaded before (duplicate detected)';
                    $result['data']['file'] = [
                        'path' => $existingFile['path'],
                        'url' => file_url($existingFile['path']),
                        'size' => $existingFile['size'],
                        'name' => $existingFile['name'],
                        'type' => $existingFile['type'],
                        'md5' => $existingFile['md5'],
                        'created_at' => $existingFile['created_at']
                    ];
                    $result['data']['file_id'] = $existingFile['id'];
                    $result['data']['is_duplicate'] = true;
                    $result['data']['existing_file'] = $existingFile;
                } else {
                    // Add file_id to response for new file
                    $result['data']['file_id'] = $saveResult['file_id'];
                }
            }
        }

        return $this->success($result, 'Upload successful');
    }

    /**
     * Build response data từ existing file
     * 
     * @param array $existingFile Existing file from database
     * @return array Response data
     */
    private function buildExistingFileResponse($existingFile)
    {
        return [
            'file_id' => $existingFile['id'],
            'is_duplicate' => true,
            'path' => $existingFile['path'],
            'url' => file_url($existingFile['path']),
            'size' => $existingFile['size'],
            'name' => $existingFile['name'],
            'type' => $existingFile['type'],
            'md5' => $existingFile['md5'],
            'created_at' => $existingFile['created_at'],
            'existing_file' => $existingFile
        ];
    }


    /**
     * Get chunk upload progress
     * Endpoint: GET /chunk_progress?uploadId=xxx
     */
    public function chunk_progress()
    {
        $uploadId = S_GET('uploadId') ?? null;

        if (!$uploadId) {
            return $this->error('Upload ID is required');
        }

        try {
            $this->requirePermission('add', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        // Get progress from storage
        $storage = S_GET('storage') ?? 'local';
        $chunker = new \System\Libraries\Uploads\Chunker($storage);

        $result = $chunker->getProgress($uploadId, []);

        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Failed to get progress');
        }

        return $this->success($result['data'], 'Progress retrieved successfully');
    }

    /**
     * Save base64 images with optimization (for iMagify)
     * Endpoint: POST /saves/
     * 
     * Uses: do_savesizes() helper → do_upload() → Uploads::uploadImage()
     * Same flow as uploadStandard() for consistency
     * 
     * Payload Format (v2.0):
     * {
     *   "filename": "product-photo",      // Base filename (no extension)
     *   "original": "data:image/jpeg;base64,...",
     *   "sizes": [
     *     {
     *       "width": 200,
     *       "height": 500,
     *       "name": "thumbnail",  // Optional: custom name (will be slugified)
     *       "data": "data:image/jpeg;base64,..."
     *     },
     *     {
     *       "width": 400,
     *       "height": 1000,
     *       "name": "",           // Optional: if empty/null, will use "400x1000"
     *       "data": "data:image/jpeg;base64,..."
     *     }
     *   ],
     *   "format": "jpg",        // Output format (jpg/png/webp)
     *   "quality": 85,          // Compression quality (0-100)
     *   "webp": true,           // Generate WebP variants
     *   "optimize": true,       // Optimize images
     * }
     * 
     * Response:
     * {
     *   "success": true,
     *   "message": "Upload complete",
     *   "data": {
     *     "original": { "file_id": 123, "path": "...", "url": "...", "basename": "...", "webp_version": {...} },
     *     "sizes": {
     *       "thumbnail": { "file_id": 124, "path": "...", "webp_version": {...} },
     *       "400x400": { "file_id": 125, "path": "...", "webp_version": {...} }
     *     },
     *     "webp_variants": [...],
     *     "folder": "2025/01/07",
     *     "files": [...]  // All files (original + sizes + webp)
     *   }
     * }
     * 
     * Flow:
     * 1. Parse payload and validate required fields
     * 2. Upload ORIGINAL image:
     *    - Call do_savesizes() → save to DB and get sanitized basename
     * 3. Process SIZE variants:
     *    - Use basename from original upload
     *    - For each size: determine name (custom or "widthxheight")
     *    - Call uploadSizeVariant() → save to storage only (no DB)
     * 4. Update original DB record with size variants info
     * 5. Build and return response with all file info
     */
    public function saves()
    {
        // Check permission
        try {
            $this->requirePermission('add', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        // Read JSON payload
        $payload = json_decode(file_get_contents('php://input'), true);

        // Validate payload
        if (!$payload) {
            return $this->error('Invalid JSON payload', [], 400);
        }

        // Check for required fields
        if (empty($payload['filename']) || empty($payload['original'])) {
            return $this->error('Missing required fields: filename, original', [], 400);
        }

        // Extract config
        $filename = $payload['filename'];  // "avatar" (no extension)
        $format = strtolower($payload['format'] ?? 'jpg');
        $quality = $payload['quality'] ?? 85;
        $webp = $payload['webp'] ?? false;
        $optimize = $payload['optimize'] ?? false;
        $resizeMode = strtolower($payload['resize_mode'] ?? 'fill'); // Default to 'fill' (cover)
        $storage = 'local'; //default 
        $folder = date('Y/m/d');
        $userId = current_user_id();

        // Prepare result structure
        $result = [
            'original' => null,
            'sizes' => [],
            'webp_variants' => [],
            'folder' => $folder,
            'files' => []
        ];

        $errors = [];

        // Upload options (shared by all files)
        $uploadOptions = [
            'folder' => $folder,
            'format' => $format,
            'quality' => $quality,
            'webp' => $webp,
            'optimize' => $optimize,
            'resize_mode' => $resizeMode,
            'storage' => $storage,
            'user_id' => $userId
        ];

        try {
            // 1. Upload ORIGINAL
            $originalFilename = $filename . '.' . $format;
            $originalResult = do_savesizes(
                $payload['original'],
                $originalFilename,
                $uploadOptions
            );

            if (!$originalResult['success']) {
                return $this->error('Original upload failed: ' . $originalResult['message'], [], 400);
            }

            // 2. Process SIZES (upload to storage but don't save to DB)
            // Lấy basename đã được sanitize + unique từ file original đã lưu
            $filename = $originalResult['data']['basename'];
            $sizeVariants = [];

            if (!empty($payload['sizes']) && is_array($payload['sizes'])) {
                foreach ($payload['sizes'] as $sizeItem) {
                    // Skip if no data
                    if (!isset($sizeItem['data']) || empty($sizeItem['data'])) {
                        continue;
                    }

                    // Determine size label: use custom name if provided, otherwise use "widthxheight"
                    if (isset($sizeItem['name']) && url_slug($sizeItem['name']) != '') {
                        $size = url_slug($sizeItem['name']);
                    } else {
                        $size = $sizeItem['width'] . 'x' . $sizeItem['height'];
                    }

                    $base64Data = $sizeItem['data'];
                    $sizeFilename = $filename . '_' . $size . '.' . $format;
                    $uploadOptions['resize'] = $sizeItem['width'] . 'x' . $sizeItem['height'];

                    // Allow per-size resize mode override
                    if (isset($sizeItem['resize_mode'])) {
                        $uploadOptions['resize_mode'] = strtolower($sizeItem['resize_mode']);
                    }

                    // Upload size variant to storage only (no DB save)
                    $sizeResult = $this->uploadSizeVariant(
                        $base64Data,
                        $sizeFilename,
                        array_merge($uploadOptions, [
                            'preserve_filename' => true
                        ])
                    );

                    if ($sizeResult['success']) {
                        $sizeVariants[] = $size;
                        $result['sizes'][$size] = $sizeResult['data'];
                    } else {
                        $errors[] = "Size {$size} upload failed: " . $sizeResult['message'];
                    }
                }
            }

            // 3. Update the original record with size variants and webp info
            $this->updateFileWithVariants($originalResult['data']['file_id'], $sizeVariants, $webp);

            // 4. Build response
            $result['original'] = $originalResult['data'];
            $result['files'] = [$originalResult['data']];

            // Add WebP variant info if created
            if (!empty($originalResult['data']['webp_version'])) {
                $result['webp_variants'][] = $originalResult['data']['webp_version'];
            }

            // Return success with warnings if any
            $message = 'Upload complete';
            if (!empty($errors)) {
                $message .= ' (with warnings)';
                $result['warnings'] = $errors;
            }

            return $this->success($result, $message);
        } catch (\Exception $e) {
            return $this->error(
                'Upload failed: ' . $e->getMessage(),
                array_merge($errors, [$e->getMessage()]),
                500
            );
        }
    }

    /**
     * List files với search, sort, filter
     */
    public function index()
    {
        // Check permission
        try {
            $this->requirePermission('index', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        try {
            // Get query parameters
            $typeLists = array('image', 'file', 'document', 'video', 'audio', 'archive');

            $page = (int) (S_GET('page') ?? 1);
            $limit = (int) (S_GET('limit') ?? 48);
            $search = S_GET('q') ?? '';
            $fileType = in_array(S_GET('type'), $typeLists) ? S_GET('type') : 'file';
            $fileFilter = explode(',', S_GET('filter_type') ?? '');
            $fileFilter = array_filter($fileFilter, function ($value) {
                return !empty($value);
            });
            $sort = S_GET('sort') ?? 'created_at_desc';
            $requestedUserId = S_GET('user_id') ?? null;

            // If user doesn't have manage permission, filter by their own user_id
            $currentUserId = current_user_id();
            if (!$this->hasManagePermission()) {
                // Non-managers can only see their own files
                $requestedUserId = $currentUserId;
            }

            // Prepare sorting
            $orderBy = $this->getOrderBy($sort);

            // Prepare search condition
            $where = '';
            $params = [];

            // Search by name
            if (!empty($search)) {
                $where = 'name LIKE ?';
                $params[] = '%' . $search . '%';
            }

            // Filter by image types
            switch ($fileType) {
                case 'image':
                    $fileTypes = config('images_types', 'Uploads') ?? [];
                    break;
                case 'file':
                    $fileTypes = config('allowed_types', 'Uploads') ?? [];
                    break;
                case 'document':
                    $fileTypes = config('document_types', 'Uploads') ?? [];
                    break;
                case 'video':
                    $fileTypes = config('video_types', 'Uploads') ?? [];
                    break;
                case 'audio':
                    $fileTypes = config('audio_types', 'Uploads') ?? [];
                    break;
                case 'archive':
                    $fileTypes = config('archive_types', 'Uploads') ?? [];
                    break;
                default:
                    $fileType = 'file';
                    $fileTypes = config('allowed_types', 'Uploads') ?? [];
                    break;
            }
            if (!empty($fileFilter)) {
                $fileTypes = array_intersect($fileTypes, $fileFilter);
            }
            if (isset($fileTypes) && !empty($fileTypes)) {
                if ($where != '') {
                    $where .= ' AND ';
                }
                $placeholders = implode(',', array_fill(0, count($fileTypes), '?'));
                $where .= " type IN ($placeholders)";
                $params = array_merge($params, $fileTypes);
            }

            // Filter by user
            if ($requestedUserId) {
                if ($where != '') {
                    $where .= ' AND ';
                }
                $where .= 'user_id = ?';
                $params[] = $requestedUserId;
            }

            // Fetch files from database
            $filesPage = $this->filesModel->getFiles($where, $params, $orderBy, $page, $limit);

            // Add URLs to each file
            foreach ($filesPage['data'] as &$file) {
                $file['url'] = file_url($file['path']);

                // Parse resize variants (semicolon-separated format)
                // if (!empty($file['resize'])) {
                //     $file['variants'] = [];
                //     $sizes = explode(';', $file['resize']);

                //     foreach ($sizes as $size) {
                //         $size = trim($size);
                //         if (strpos($size, 'x') !== false) {
                //             list($width, $height) = explode('x', $size);
                //             $ext = pathinfo($file['path'], PATHINFO_EXTENSION);
                //             $variantPath = str_replace(".{$ext}", "_{$width}x{$height}.{$ext}", $file['path']);

                //             $file['variants'][] = [
                //                 'size' => $size,
                //                 'width' => (int) $width,
                //                 'height' => (int) $height,
                //                 'url' => file_url($variantPath)
                //             ];
                //         }
                //     }
                // }
            }

            // Prepare response data (compatible với V1)
            $responseData = [
                'items' => $filesPage['data'],
                'isnext' => $filesPage['is_next'] ?? false,
                'page' => $filesPage['page'] ?? $page,
                'total' => $filesPage['total'] ?? 0,
                'limit' => $limit
            ];

            return $this->success($responseData, 'Files retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get ORDER BY clause từ sort parameter
     */
    private function getOrderBy($sort)
    {
        switch ($sort) {
            case 'name':
            case 'name_az':
                return 'name ASC';
            case 'name_za':
                return 'name DESC';
            case 'size_asc':
                return 'size ASC';
            case 'size_desc':
                return 'size DESC';
            case 'created_at_asc':
                return 'created_at ASC';
            case 'created_at_desc':
            default:
                return 'created_at DESC';
            case 'updated_at_asc':
                return 'updated_at ASC';
            case 'updated_at_desc':
                return 'updated_at DESC';
        }
    }

    /**
     * Delete file
     */
    public function delete($id = null)
    {
        // Check permission
        try {
            $this->requirePermission('delete', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        if (!$id) {
            $id = S_POST('id') ?? S_GET('id') ?? null;
        }

        if (!$id) {
            return $this->error('Missing file ID');
        } else {
            $id = (int)$id;
        }

        // Check if user can access this file
        $accessCheck = $this->canAccessFile($id);
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $result = file_delete($id);

        if (!empty($result['success'])) {
            return $this->success($result, $result['message'] ?? 'Delete successful');
        }

        return $this->error(
            $result['message'] ?? 'Delete failed',
            $result['errors'] ?? [],
            400
        );
    }

    /**
     * Delete multiple files
     */
    public function delete_multiple()
    {
        // Check permission
        try {
            $this->requirePermission('delete', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        $items = S_POST('items') ?? null;

        if (!$items) {
            return $this->error('Missing items');
        }

        // Parse JSON if string
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (!is_array($items) || empty($items)) {
            return $this->error('Invalid items format');
        }

        $results = [
            'success' => true,
            'deleted' => [],
            'failed' => []
        ];

        foreach ($items as $item) {
            $id = $item['id'] ?? $item;

            // Check if user can access this file
            $accessCheck = $this->canAccessFile($id);
            if ($accessCheck !== true) {
                $results['failed'][] = [
                    'id' => $id,
                    'error' => is_array($accessCheck) ? ($accessCheck['message'] ?? 'Access denied') : 'Access denied'
                ];
                continue;
            }

            $result = file_delete($id);

            if ($result['success']) {
                $results['deleted'][] = $id;
            } else {
                $results['failed'][] = [
                    'id' => $id,
                    'error' => $result['message'] ?? 'Unknown error'
                ];
            }
        }

        // Overall success if at least one deleted
        $results['success'] = count($results['deleted']) > 0;
        $results['message'] = sprintf(
            'Deleted %d/%d files',
            count($results['deleted']),
            count($items)
        );

        if (!empty($results['success'])) {
            return $this->success($results, $results['message'] ?? 'Delete completed');
        }

        return $this->error(
            $results['message'] ?? 'Delete failed',
            $results['failed'] ?? [],
            400
        );
    }

    /**
     * Rename file và tất cả variants
     */
    public function rename()
    {
        $id = (int)S_POST('id') ?? null;
        $newName = S_POST('newname') ?? S_POST('name') ?? null;

        if (!$id || !$newName) {
            return $this->error('Missing id or newname');
        }

        try {
            $this->requirePermission('edit', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        // Check if user can access this file
        $accessCheck = $this->canAccessFile($id);
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        // Use file_rename helper
        $result = file_rename($id, $newName);

        if (!$result['success']) {
            return $this->error($result['message'], $result['errors'] ?? []);
        }

        return $this->success($result['data'], $result['message']);
    }

    /**
     * Get file variants
     */
    public function variants($id = null)
    {
        if (!$id) {
            $id = S_GET('id') ?? S_POST('id') ?? null;
        }
        if (!$id) {
            return $this->error('Missing file ID');
        } else {
            $id = (int)$id;
        }
        // Get file from DB
        $file = file_get($id);
        if (!$file) {
            return $this->error('File not found');
        }

        try {
            $this->requirePermission('index', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        // Check if user can access this file
        $accessCheck = $this->canAccessFile($file);
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        // Get all variants
        $variants = file_get_variants($file);
        // Build response with URLs
        $variantsWithUrls = array_map(function ($path) {
            return [
                'path' => $path,
                'url' => file_url($path),
                'size' => $this->_fileSize($path)
            ];
        }, $variants);

        return $this->success([
            'original' => [
                'path' => $file['path'],
                'url' => file_url($file['path'])
            ],
            'variants' => $variantsWithUrls,
            'total_variants' => count($variants)
        ]);
    }

    /**
     * Get best variant for requested size
     */
    public function best_variant($id = null)
    {
        if (!$id) {
            $id = S_GET('id') ?? S_POST('id') ?? null;
        } else {
            $id = (int)$id;
        }

        $size = S_GET('size') ?? S_POST('size') ?? null;
        $format = S_GET('format') ?? S_POST('format') ?? null;

        if (!$id || !$size) {
            return $this->error('Missing file ID or size');
        }
        // Get file from DB
        $file = file_get($id);
        if (!$file) {
            return $this->error('File not found');
        }

        try {
            $this->requirePermission('index', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        // Check if user can access this file
        $accessCheck = $this->canAccessFile($file);
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        // Get best variant
        $bestVariant = file_get_best_variant($file, $size, $format);

        if (!$bestVariant) {
            return $this->error('No suitable variant found');
        }
        return $this->success([
            'path' => $bestVariant,
            'url' => file_url($bestVariant),
            'requested_size' => $size,
            'requested_format' => $format
        ]);
    }

    /**
     * Delete file variants (keep original)
     */
    public function delete_variants($id = null)
    {
        // Check permission
        try {
            $this->requirePermission('delete', $this->filesControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        if (!$id) {
            $id = S_POST('id') ?? S_GET('id') ?? null;
        }
        if (!$id) {
            return $this->error('Missing file ID');
        } else {
            $id = (int)$id;
        }

        // Get file from DB
        $file = file_get($id);
        if (!$file) {
            return $this->error('File not found');
        }

        // Check if user can access this file
        $accessCheck = $this->canAccessFile($file);
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        // Delete variants
        $result = file_delete_variants($file);

        if (!empty($result['success'])) {
            return $this->success($result, $result['message'] ?? 'Delete variants successful');
        }

        return $this->error(
            $result['message'] ?? 'Delete variants failed',
            $result['errors'] ?? [],
            400
        );
    }

    /**
     * Get file size helper
     */
    private function _fileSize($path)
    {
        $fullPath = PATH_WRITE . 'uploads/' . ltrim($path, '/');
        return file_exists($fullPath) ? filesize($fullPath) : 0;
    }

    /**
     * Upload size variant to storage only (no database save)
     * 
     * @param string $base64Data Base64 image data
     * @param string $sizeFilename Filename for storage
     * @param array $options Upload options
     * @return array Upload result
     */
    private function uploadSizeVariant($base64Data, $sizeFilename, $options)
    {
        try {
            // Decode base64
            if (strpos($base64Data, 'data:') === 0) {
                $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
            }

            $content = base64_decode($base64Data);

            if ($content === false || empty($content)) {
                return ['success' => false, 'message' => 'Invalid base64 data'];
            }

            // Create temp file
            $tempDir = defined('PATH_TEMP') ? PATH_TEMP : sys_get_temp_dir();
            $randomSuffix = bin2hex(random_bytes(8));
            $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'upload_' . $randomSuffix . '.tmp';

            if (@file_put_contents($tempFile, $content) === false) {
                return ['success' => false, 'message' => 'You need create writable/temp folder & chmod 777 this.'];
            }

            // Detect MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $tempFile);
            finfo_close($finfo);

            // Get extension from format
            $extension = $options['format'] ?? 'jpg';

            // Rename temp file with extension
            $tempFileWithExt = $tempFile . '.' . $extension;
            rename($tempFile, $tempFileWithExt);
            $tempFile = $tempFileWithExt;

            // Prepare file array (giữ đúng filename đã build từ basename_unique + _size + ext)
            $fileArray = [
                'name' => $sizeFilename,
                'type' => $detectedMime,
                'tmp_name' => $tempFile,
                'error' => 0,
                'size' => filesize($tempFile)
            ];


            // Upload to storage only (no DB save)
            $uploads = new \System\Libraries\Uploads($options['storage'] ?? null);
            $uploadOptions = [
                'folder' => $options['folder'],
                'format' => $options['format'],
                'quality' => $options['quality'],
                'optimize' => $options['optimize'],
                'webp' => $options['webp'],
                // Quan trọng: giữ nguyên tên đã truyền vào, tránh đổi basename lần nữa
                'preserve_filename' => $options['preserve_filename'] ?? false
            ];
            if (isset($options['resize']) && !empty($options['resize'])) {
                $uploadOptions['resize'] = $options['resize'];
            }
            $uploadResult = $uploads->uploadImage($fileArray, $uploadOptions);

            // Cleanup temp file
            @unlink($tempFile);

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

    /**
     * Update file record with size variants and webp info
     * 
     * @param int $fileId File ID
     * @param array $sizeVariants Array of size strings
     * @param bool $hasWebp Has WebP variant
     * @return bool Success
     */
    private function updateFileWithVariants($fileId, $sizeVariants, $hasWebp)
    {
        $updateData = [];

        // Update resize field with semicolon-separated string
        if (!empty($sizeVariants)) {
            $updateData['resize'] = implode(';', $sizeVariants);
        }

        // Update webp field
        $updateData['webp'] = $hasWebp ? 1 : 0;

        // Update database
        return file_update($fileId, $updateData);
    }



    /**
     * Check if current user has manage permission
     * 
     * @return bool
     */
    protected function hasManagePermission()
    {
        return $this->checkPermission('manage', $this->filesControllerName);
    }

    /**
     * Check if user can access file (must be owner or have manage permission)
     * 
     * @param array|int $file File data or file ID
     * @return bool|array Returns true if allowed, error response if not
     */
    protected function canAccessFile($file)
    {
        // If user has manage permission, allow access
        if ($this->hasManagePermission()) {
            return true;
        }

        // Get file data if ID provided
        if (is_numeric($file)) {
            $file = file_get($file);
            if (!$file) {
                return $this->error('File not found', [], 404);
            }
        }

        // Check if file belongs to current user
        $userId = current_user_id();
        if (empty($userId) || empty($file['user_id']) || $file['user_id'] != $userId) {
            return $this->error('You do not have permission to access this file', [], 403);
        }

        return true;
    }
}
