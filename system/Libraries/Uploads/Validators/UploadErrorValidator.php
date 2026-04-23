<?php

namespace System\Libraries\Uploads\Validators;

/**
 * UploadErrorValidator - Validate PHP upload errors
 * 
 * Kiểm tra error code từ $_FILES['error']
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class UploadErrorValidator
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
     * Validate upload error code
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options tùy chỉnh
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    public function validate($file, $options = [])
    {
        if (!isset($file['error'])) {
            return [
                'success' => false,
                'error' => 'Missing error code in uploaded file',
                'data' => null
            ];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => $this->getUploadErrorMessage($file['error']),
                'data' => [
                    'error_code' => $file['error']
                ]
            ];
        }
        
        return [
            'success' => true,
            'error' => null,
            'data' => null
        ];
    }
    
    /**
     * Get upload error message from error code
     * 
     * @param int $errorCode Upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        // Ensure errorCode is integer
        $errorCode = (int) $errorCode;
        
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];
        
        return $errors[$errorCode] ?? "Unknown upload error (code: {$errorCode})";
    }
}
