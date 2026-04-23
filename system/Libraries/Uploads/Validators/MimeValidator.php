<?php

namespace System\Libraries\Uploads\Validators;

/**
 * MimeValidator - Validate MIME type
 * 
 * Checks:
 * - Whether MIME type matches file extension
 * - Whether MIME type is in allowed list
 * - Detect actual MIME type of the file
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class MimeValidator
{
    private $config;
    
    /**
     * Mapping extension -> allowed MIME types
     * Comprehensive list based on IANA standards and browser implementations
     */
    private $mimeMap = [
        // Images - Raster
        'jpg' => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
        'png' => ['image/png', 'image/x-png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'bmp' => ['image/bmp', 'image/x-bmp', 'image/x-ms-bmp', 'image/x-windows-bmp'],
        'ico' => ['image/x-icon', 'image/vnd.microsoft.icon', 'image/ico'],
        'tiff' => ['image/tiff', 'image/x-tiff'],
        'tif' => ['image/tiff', 'image/x-tiff'],
        'heic' => ['image/heic', 'image/heif'],
        'heif' => ['image/heif', 'image/heic'],
        'avif' => ['image/avif'],
        'jfif' => ['image/jpeg', 'image/pjpeg'],
        
        // Images - Vector
        'svg' => ['image/svg+xml', 'text/xml', 'application/xml'],
        'eps' => ['application/postscript', 'image/x-eps'],
        'ai' => ['application/postscript', 'application/illustrator'],
        
        // Documents - Microsoft Office
        'doc' => ['application/msword', 'application/vnd.ms-word'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream'],
        'xls' => ['application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/mspowerpoint', 'application/x-mspowerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/octet-stream'],
        
        // Documents - OpenOffice/LibreOffice
        'odt' => ['application/vnd.oasis.opendocument.text'],
        'ods' => ['application/vnd.oasis.opendocument.spreadsheet'],
        'odp' => ['application/vnd.oasis.opendocument.presentation'],
        
        // Documents - Other
        'pdf' => ['application/pdf', 'application/x-pdf', 'application/acrobat'],
        'txt' => ['text/plain', 'text/txt'],
        'rtf' => ['application/rtf', 'text/rtf'],
        'csv' => ['text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values'],
        'xml' => ['application/xml', 'text/xml'],
        'json' => ['application/json', 'text/json'],
        'md' => ['text/markdown', 'text/plain'],
        
        // Archives - Common
        'zip' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'],
        'rar' => ['application/x-rar-compressed', 'application/x-rar', 'application/vnd.rar', 'application/octet-stream'],
        '7z' => ['application/x-7z-compressed', 'application/octet-stream'],
        'tar' => ['application/x-tar', 'application/tar'],
        'gz' => ['application/gzip', 'application/x-gzip', 'application/x-gunzip', 'application/gzipped'],
        'bz2' => ['application/x-bzip2', 'application/x-bzip'],
        'xz' => ['application/x-xz'],
        
        // Archives - ISO/Disk Images
        'iso' => ['application/x-iso9660-image', 'application/octet-stream'],
        'dmg' => ['application/x-apple-diskimage'],
        
        // Videos - Common
        'mp4' => ['video/mp4', 'video/mpeg4'],
        'avi' => ['video/x-msvideo', 'video/avi', 'video/msvideo'],
        'mov' => ['video/quicktime', 'video/x-quicktime'],
        'wmv' => ['video/x-ms-wmv', 'video/x-ms-asf'],
        'flv' => ['video/x-flv', 'video/flv'],
        'mkv' => ['video/x-matroska', 'video/mkv'],
        'webm' => ['video/webm'],
        'mpeg' => ['video/mpeg', 'video/mpg'],
        'mpg' => ['video/mpeg', 'video/mpg'],
        'm4v' => ['video/x-m4v', 'video/mp4'],
        '3gp' => ['video/3gpp', 'video/3gp'],
        'ogv' => ['video/ogg', 'application/ogg'],
        
        // Videos - Subtitles
        'srt' => ['text/plain', 'application/x-subrip', 'text/srt'],
        'vtt' => ['text/vtt', 'text/plain'],
        'ass' => ['text/plain', 'application/x-ass'],
        'ssa' => ['text/plain', 'application/x-ssa'],
        
        // Audio - Common
        'mp3' => ['audio/mpeg', 'audio/mp3', 'audio/mpeg3', 'audio/x-mpeg-3'],
        'wav' => ['audio/wav', 'audio/x-wav', 'audio/wave'],
        'ogg' => ['audio/ogg', 'application/ogg', 'audio/x-ogg'],
        'flac' => ['audio/flac', 'audio/x-flac'],
        'm4a' => ['audio/mp4', 'audio/x-m4a', 'audio/m4a'],
        'aac' => ['audio/aac', 'audio/x-aac'],
        'wma' => ['audio/x-ms-wma'],
        'opus' => ['audio/opus', 'audio/ogg'],
        'mid' => ['audio/midi', 'audio/x-midi'],
        'midi' => ['audio/midi', 'audio/x-midi'],
        
        // Code/Programming
        'js' => ['application/javascript', 'text/javascript', 'application/x-javascript'],
        'css' => ['text/css'],
        'html' => ['text/html'],
        'htm' => ['text/html'],
        'php' => ['application/x-php', 'text/x-php', 'text/plain'],
        'py' => ['text/x-python', 'text/plain'],
        'java' => ['text/x-java-source', 'text/plain'],
        'c' => ['text/x-c', 'text/plain'],
        'cpp' => ['text/x-c++', 'text/plain'],
        'h' => ['text/x-c', 'text/plain'],
        'sh' => ['application/x-sh', 'text/x-shellscript'],
        
        // Fonts
        'ttf' => ['font/ttf', 'application/x-font-ttf', 'font/sfnt'],
        'otf' => ['font/otf', 'application/x-font-otf', 'font/sfnt'],
        'woff' => ['font/woff', 'application/font-woff'],
        'woff2' => ['font/woff2', 'application/font-woff2'],
        'eot' => ['application/vnd.ms-fontobject'],
        
        // 3D Models
        'obj' => ['text/plain', 'application/x-tgif'],
        'stl' => ['application/sla', 'application/vnd.ms-pki.stl', 'application/x-navistyle'],
        'fbx' => ['application/octet-stream'],
        'gltf' => ['model/gltf+json'],
        'glb' => ['model/gltf-binary'],
        
        // CAD
        'dwg' => ['application/acad', 'image/vnd.dwg'],
        'dxf' => ['application/dxf', 'image/vnd.dxf'],
        
        // eBooks
        'epub' => ['application/epub+zip'],
        'mobi' => ['application/x-mobipocket-ebook'],
        'azw' => ['application/vnd.amazon.ebook'],
        'azw3' => ['application/vnd.amazon.ebook'],
        
        // Database
        'sql' => ['application/sql', 'text/plain'],
        'db' => ['application/x-sqlite3', 'application/octet-stream'],
        'sqlite' => ['application/x-sqlite3'],
        'mdb' => ['application/x-msaccess'],
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
     * Validate MIME type
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options tùy chỉnh
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    public function validate($file, $options = [])
    {
        // Check tmp file exists
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'Temporary file does not exist',
                'data' => null
            ];
        }
        
        // Detect MIME type using finfo
        $detectedMime = $this->detectMimeType($file['tmp_name']);
        
        // If cannot detect MIME, use extension-based fallback (lenient mode)
        if ($detectedMime === false) {
            // Get extension and try to get expected MIME
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (isset($this->mimeMap[$ext])) {
                // Use first MIME type for this extension as fallback
                $detectedMime = $this->mimeMap[$ext][0];
            } else {
                // Cannot detect and no mapping, pass validation (extension validator will catch invalid files)
                return [
                    'success' => true,
                    'error' => null,
                    'data' => [
                        'mime_type' => 'application/octet-stream', // Generic binary
                        'detection_method' => 'fallback'
                    ]
                ];
            }
        }
        
        // Get extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check if MIME matches extension
        if (isset($this->mimeMap[$ext])) {
            $allowedMimes = $this->mimeMap[$ext];
            
            if (!in_array($detectedMime, $allowedMimes)) {
                // SECURITY: ONLY use strict_mime from config, NEVER from options
                $strictMode = $this->config['strict_mime'] ?? false;
                
                // Special case: some browsers send different MIME for same file
                if (!$this->isAcceptableMimeVariation($detectedMime, $allowedMimes)) {
                    if ($strictMode) {
                        // STRICT MODE: Fail validation on MIME mismatch
                        return [
                            'success' => false,
                            'error' => "MIME type '{$detectedMime}' does not match extension '{$ext}'. Expected: " . implode(', ', $allowedMimes),
                            'data' => [
                                'detected_mime' => $detectedMime,
                                'expected_mimes' => $allowedMimes,
                                'extension' => $ext,
                                'security_warning' => 'Possible file type spoofing attack'
                            ]
                        ];
                    } else {
                        // LENIENT MODE: Only warn, don't fail (extension validator is primary check)
                        return [
                            'success' => true,
                            'error' => null,
                            'data' => [
                                'mime_type' => $detectedMime,
                                'warning' => "MIME type '{$detectedMime}' does not fully match extension '{$ext}'",
                                'expected_mimes' => $allowedMimes
                            ]
                        ];
                    }
                }
            }
        }
        
        // SECURITY: Check allowed MIME types from config ONLY (not from options)
        $allowedMimes = $this->config['allowed_mimes'] ?? null;
        
        if ($allowedMimes !== null && !in_array($detectedMime, $allowedMimes)) {
            return [
                'success' => false,
                'error' => "MIME type '{$detectedMime}' is not allowed",
                'data' => [
                    'detected_mime' => $detectedMime,
                    'allowed_mimes' => $allowedMimes
                ]
            ];
        }
        
        return [
            'success' => true,
            'error' => null,
            'data' => [
                'mime_type' => $detectedMime
            ]
        ];
    }
    
    /**
     * Detect MIME type using finfo
     * 
     * @param string $filePath File path
     * @return string|false MIME type or false on failure
     */
    private function detectMimeType($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        // Check if file is readable
        if (!is_readable($filePath)) {
            return false;
        }
        
        // Try finfo first (most reliable)
        if (function_exists('finfo_open')) {
            try {
                // Suppress warnings and use error suppression
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $mime = @finfo_file($finfo, $filePath);
                    @finfo_close($finfo);
                    
                    if ($mime !== false && !empty($mime)) {
                        return $mime;
                    }
                }
            } catch (\Exception $e) {
                // Silent fail, try next method
            }
        }
        
        // Fallback to mime_content_type
        if (function_exists('mime_content_type')) {
            try {
                $mime = @mime_content_type($filePath);
                if ($mime !== false && !empty($mime)) {
                    return $mime;
                }
            } catch (\Exception $e) {
                // Silent fail
            }
        }
        
        // Last resort: detect from extension
        return $this->getMimeFromExtension($filePath);
    }
    
    /**
     * Get MIME type from file extension (fallback)
     * 
     * @param string $filePath File path
     * @return string|false MIME type or false
     */
    private function getMimeFromExtension($filePath)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (isset($this->mimeMap[$ext]) && !empty($this->mimeMap[$ext])) {
            // Return first MIME type for this extension
            return $this->mimeMap[$ext][0];
        }
        
        return false;
    }
    
    /**
     * Check if MIME is acceptable variation
     * 
     * Một số browser/OS có thể gửi MIME type hơi khác nhau
     * 
     * @param string $detectedMime Detected MIME
     * @param array $allowedMimes Allowed MIMEs
     * @return bool
     */
    private function isAcceptableMimeVariation($detectedMime, $allowedMimes)
    {
        // application/octet-stream là generic binary, có thể chấp nhận cho archives
        if ($detectedMime === 'application/octet-stream') {
            foreach ($allowedMimes as $allowed) {
                if (strpos($allowed, 'application/') === 0) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get MIME type for extension
     * 
     * @param string $ext Extension
     * @return array|null Array of allowed MIME types
     */
    public function getMimeTypesForExtension($ext)
    {
        $ext = strtolower($ext);
        return $this->mimeMap[$ext] ?? null;
    }
    
    /**
     * Check if MIME is image
     * 
     * @param string $mime MIME type
     * @return bool
     */
    public function isImageMime($mime)
    {
        return strpos($mime, 'image/') === 0;
    }
    
    /**
     * Check if MIME is video
     * 
     * @param string $mime MIME type
     * @return bool
     */
    public function isVideoMime($mime)
    {
        return strpos($mime, 'video/') === 0;
    }
    
    /**
     * Check if MIME is audio
     * 
     * @param string $mime MIME type
     * @return bool
     */
    public function isAudioMime($mime)
    {
        return strpos($mime, 'audio/') === 0;
    }
}
