<?php

namespace System\Libraries\Uploads;

use System\Libraries\Uploads\Validators\UploadErrorValidator;
use System\Libraries\Uploads\Validators\SizeValidator;
use System\Libraries\Uploads\Validators\ExtensionValidator;
use System\Libraries\Uploads\Validators\MimeValidator;
use System\Libraries\Uploads\Validators\MagicBytesValidator;
use System\Libraries\Uploads\Validators\SecurityValidator;
use System\Libraries\Uploads\Validators\ImageValidator;
use System\Libraries\Uploads\Validators\ZipBombValidator;

/**
 * UploadValidator - Main Upload Validator
 * 
 * Orchestrator cho tất cả validation rules
 * Chạy chuỗi validation qua các validator chuyên biệt
 * Mỗi validator có trách nhiệm riêng, dễ extend và maintain
 * 
 * Validation pipeline:
 * 1. Upload Error Check
 * 2. Size Validation
 * 3. Extension Validation
 * 4. MIME Type Validation
 * 5. Magic Bytes Validation
 * 6. Security Validation
 * 7. Image Validation (nếu là image)
 * 
 * @package System\Libraries\Uploads
 * @version 2.0.0
 */
class UploadValidator
{
    private $validators = [];
    private $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load both root level config and files config
        $rootConfig = config('', 'Uploads') ?? [];
        $filesConfig = config('files', 'Uploads') ?? [];

        // Remove 'files' key from root config to avoid duplication
        if (isset($rootConfig['files'])) {
            unset($rootConfig['files']);
        }

        // Merge: root level config first, then files config (files config takes precedence for overlapping keys)
        $this->config = array_merge($rootConfig, $filesConfig);
        $this->initializeValidators();
    }

    /**
     * Khởi tạo các validator
     * 
     * Mỗi validator là một class riêng biệt với method validate()
     * Dễ hiểu, dễ maintain, dễ extend
     */
    private function initializeValidators()
    {
        $this->validators = [
            'upload_error' => new UploadErrorValidator($this->config),
            'size' => new SizeValidator($this->config),
            'extension' => new ExtensionValidator($this->config),
            'mime' => new MimeValidator($this->config),
            'magic_bytes' => new MagicBytesValidator($this->config),
            'security' => new SecurityValidator($this->config),
            'zip_bomb' => new ZipBombValidator($this->config),
        ];
    }

    /**
     * Validate file upload - Main entry point
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options tùy chỉnh (NON-SECURITY options only)
     *   - 'skip_validators' => array: Skip specific validators ['mime', 'magic_bytes']
     *   - 'folder' => string: Upload folder (non-security)
     * 
     * SECURITY: The following parameters CANNOT be overridden from options:
     *   - allowed_types (CRITICAL)
     *   - strict_mime (CRITICAL)
     *   - max_file_size, min_file_size (CRITICAL)
     *   - allowed_mimes (CRITICAL)
     * 
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    public function validate($file, $options = [])
    {
        // SECURITY: Only allow safe non-security options to be merged
        // Critical security params MUST come from config only
        $safeOptions = $this->extractSafeOptions($options);
        $config = array_merge($this->config, $safeOptions);

        // Get skip validators list
        $skipValidators = $options['skip_validators'] ?? [];

        // Chạy qua từng validator
        foreach ($this->validators as $name => $validator) {
            // Skip if in skip list
            if (in_array($name, $skipValidators)) {
                continue;
            }

            $result = $this->runValidator($validator, $file, $config);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'],
                    'validator' => $name,
                    'data' => $result['data'] ?? null
                ];
            }
        }

        // Nếu là image, validate thêm image-specific rules
        if ($this->isImage($file)) {
            $imageValidator = new ImageValidator($config);
            $result = $imageValidator->validate($file, $config);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'],
                    'validator' => 'image',
                    'data' => $result['data'] ?? null
                ];
            }
        }

        // All validations passed
        return [
            'success' => true,
            'error' => null,
            'data' => [
                'file_size' => $file['size'],
                'mime_type' => $this->getMimeType($file),
                'extension' => $this->getExtension($file),
                'is_image' => $this->isImage($file),
                'validated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Extract safe (non-security) options from options array
     * 
     * SECURITY: Block critical security parameters from being overridden
     * Only allow safe, non-security options to pass through
     * 
     * @param array $options Options array
     * @return array Safe options only
     */
    private function extractSafeOptions($options)
    {
        // CRITICAL SECURITY PARAMETERS - NEVER allow override from client
        $blockedParams = [
            'allowed_types',      // CRITICAL: File type whitelist
            'strict_mime',        // CRITICAL: MIME validation toggle
            'max_file_size',      // CRITICAL: Size limit
            'min_file_size',      // CRITICAL: Size limit
            'max_file_count',     // CRITICAL: Batch limit
            'allowed_mimes',      // CRITICAL: MIME whitelist
            'max_chunk_size',     // CRITICAL: Chunk size limit
            'max_total_chunks',   // CRITICAL: Chunk count limit
            'images_types',       // CRITICAL: Image type list
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
     * Run a validator
     * 
     * Tất cả validators đều có method validate() giống nhau
     * Không còn cần check is_callable nữa
     * 
     * @param object $validator Validator instance
     * @param array $file File array
     * @param array $config Config array
     * @return array Validation result
     */
    private function runValidator($validator, $file, $config)
    {
        return $validator->validate($file, $config);
    }

    /**
     * Validate multiple files
     * 
     * @param array $files Multiple files array từ $_FILES
     * @param array $options Options tùy chỉnh
     * @return array ['success' => bool, 'results' => array]
     */
    public function validateMultiple($files, $options = [])
    {
        $results = [];
        $hasError = false;

        // Check if it's multiple files format
        if (!isset($files['name']) || !is_array($files['name'])) {
            return [
                'success' => false,
                'error' => 'Invalid multiple files format',
                'results' => []
            ];
        }

        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $singleFile = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $result = $this->validate($singleFile, $options);
            $results[] = $result;

            if (!$result['success']) {
                $hasError = true;
            }
        }

        return [
            'success' => !$hasError,
            'results' => $results,
            'total' => $count,
            'passed' => count(array_filter($results, function ($r) {
                return $r['success'];
            })),
            'failed' => count(array_filter($results, function ($r) {
                return !$r['success'];
            }))
        ];
    }

    /**
     * Quick validate - chỉ validate basic rules (size, extension)
     * 
     * @param array $file File array
     * @param array $options Options (non-security only)
     * @return array Validation result
     */
    public function quickValidate($file, $options = [])
    {
        // SECURITY: Extract safe options only
        $safeOptions = $this->extractSafeOptions($options);
        $config = array_merge($this->config, $safeOptions);

        // Only run essential validators
        $essentialValidators = ['upload_error', 'size', 'extension'];

        foreach ($essentialValidators as $name) {
            if (isset($this->validators[$name])) {
                $result = $this->runValidator($this->validators[$name], $file, $config);

                if (!$result['success']) {
                    return [
                        'success' => false,
                        'error' => $result['error'],
                        'validator' => $name
                    ];
                }
            }
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Check if file is image
     * 
     * @param array $file File array
     * @return bool
     */
    private function isImage($file)
    {
        $ext = $this->getExtension($file);
        $imageExtensions = $this->config['images_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        return in_array($ext, $imageExtensions);
    }

    /**
     * Get file extension
     * 
     * @param array $file File array
     * @return string Extension (lowercase)
     */
    private function getExtension($file)
    {
        return strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    }

    /**
     * Get MIME type
     * 
     * @param array $file File array
     * @return string|null MIME type
     */
    private function getMimeType($file)
    {
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        return $mime ?: null;
    }

    /**
     * Add custom validator
     * 
     * @param string $name Validator name
     * @param mixed $validator Validator instance or callable
     */
    public function addValidator($name, $validator)
    {
        $this->validators[$name] = $validator;
    }

    /**
     * Remove validator
     * 
     * @param string $name Validator name
     */
    public function removeValidator($name)
    {
        unset($this->validators[$name]);
    }

    /**
     * Get validator
     * 
     * @param string $name Validator name
     * @return mixed|null Validator or null
     */
    public function getValidator($name)
    {
        return $this->validators[$name] ?? null;
    }

    /**
     * Get all validators
     * 
     * @return array Validators array
     */
    public function getValidators()
    {
        return $this->validators;
    }
}
