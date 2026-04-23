<?php

namespace System\Libraries\Uploads\Validators;

/**
 * ExtensionValidator - Validate extension file
 * 
 * Checks:
 * - Does file have an extension
 * - Is the extension in the allowed list
 * - Is the extension blacklisted
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class ExtensionValidator
{
    private $config;
    
    /**
     * Dangerous extensions (blacklist)
     */
    private $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash',
        'js', 'jar', 'app', 'deb', 'rpm',
        'vbs', 'vbe', 'ws', 'wsf', 'wsh',
        'scr', 'dll', 'sys', 'drv',
        'htaccess', 'htpasswd', 'ini', 'config'
    ];
    
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
     * Validate file extension
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options tùy chỉnh
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function validate($file, $options = [])
    {
        // Get filename
        $filename = $file['name'] ?? '';
        
        if (empty($filename)) {
            return [
                'success' => false,
                'error' => 'Invalid filename'
            ];
        }
        
        // Extract extension
        $ext = $this->getExtension($filename);
        
        if (empty($ext)) {
            return [
                'success' => false,
                'error' => 'File has no extension'
            ];
        }
        
        // Check blacklist first (security)
        if ($this->isBlacklisted($ext)) {
            return [
                'success' => false,
                'error' => "Extension '{$ext}' is forbidden for security reasons"
            ];
        }
        
        // SECURITY: ONLY use allowed_types from config, NEVER from options
        // This prevents bypass if validator is called directly
        $allowedTypes = $this->config['allowed_types'] ?? [];
        
        // If no whitelist configured, deny upload (fail-secure)
        if (empty($allowedTypes)) {
            return [
                'success' => false,
                'error' => 'No allowed extensions configured. Please contact the administrator.'
            ];
        }
        
        // Normalize allowed types to lowercase
        $allowedTypes = array_map('strtolower', $allowedTypes);
        
        if (!in_array($ext, $allowedTypes)) {
            $allowed = implode(', ', $allowedTypes);
            return [
                'success' => false,
                'error' => "Extension '{$ext}' is not allowed. Allowed: {$allowed}"
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Get file extension (lowercase)
     * 
     * @param string $filename Filename
     * @return string Extension (lowercase)
     */
    private function getExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Check if extension is blacklisted
     * 
     * @param string $ext Extension
     * @return bool
     */
    private function isBlacklisted($ext)
    {
        return in_array(strtolower($ext), $this->dangerousExtensions);
    }
    
    /**
     * Get allowed extensions from config
     * 
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->config['allowed_types'] ?? [];
    }
    
    /**
     * Check if extension is image
     * 
     * @param string $ext Extension
     * @return bool
     */
    public function isImageExtension($ext)
    {
        $imageExtensions = $this->config['images_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        return in_array(strtolower($ext), $imageExtensions);
    }
    
    /**
     * Check if extension is video
     * 
     * @param string $ext Extension
     * @return bool
     */
    public function isVideoExtension($ext)
    {
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', 'm4v'];
        return in_array(strtolower($ext), $videoExtensions);
    }
    
    /**
     * Check if extension is audio
     * 
     * @param string $ext Extension
     * @return bool
     */
    public function isAudioExtension($ext)
    {
        $audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'];
        return in_array(strtolower($ext), $audioExtensions);
    }
    
    /**
     * Check if extension is document
     * 
     * @param string $ext Extension
     * @return bool
     */
    public function isDocumentExtension($ext)
    {
        $docExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods', 'odp'];
        return in_array(strtolower($ext), $docExtensions);
    }
    
    /**
     * Check if extension is archive
     * 
     * @param string $ext Extension
     * @return bool
     */
    public function isArchiveExtension($ext)
    {
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'];
        return in_array(strtolower($ext), $archiveExtensions);
    }
}
