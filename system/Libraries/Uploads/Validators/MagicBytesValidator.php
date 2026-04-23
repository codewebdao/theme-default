<?php

namespace System\Libraries\Uploads\Validators;

/**
 * MagicBytesValidator - Validate magic bytes (file signature)
 * 
 * Validate file signature to ensure the real file type matches
 * Prevents spoofed extensions (e.g., renaming .exe to .jpg)
 * 
 * Magic bytes are the first bytes of a file; each type has its own signature
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class MagicBytesValidator
{
    private $config;
    
    /**
     * Magic byte signatures for common file types
     * Format: ['offset' => byte position, 'bytes' => hex signature]
     */
    private $signatures = [
        // Images
        'jpg' => [
            ['offset' => 0, 'bytes' => 'FFD8FF'], // JPEG/JFIF
        ],
        'jpeg' => [
            ['offset' => 0, 'bytes' => 'FFD8FF'],
        ],
        'png' => [
            ['offset' => 0, 'bytes' => '89504E470D0A1A0A'], // PNG signature
        ],
        'gif' => [
            ['offset' => 0, 'bytes' => '474946383761'], // GIF87a
            ['offset' => 0, 'bytes' => '474946383961'], // GIF89a
        ],
        'webp' => [
            ['offset' => 0, 'bytes' => '52494646'], // RIFF
            ['offset' => 8, 'bytes' => '57454250'], // WEBP
        ],
        'bmp' => [
            ['offset' => 0, 'bytes' => '424D'], // BM
        ],
        'ico' => [
            ['offset' => 0, 'bytes' => '00000100'], // ICO
        ],
        'tiff' => [
            ['offset' => 0, 'bytes' => '49492A00'], // Little-endian
            ['offset' => 0, 'bytes' => '4D4D002A'], // Big-endian
        ],
        
        // Documents
        'pdf' => [
            ['offset' => 0, 'bytes' => '255044462D'], // %PDF-
        ],
        'doc' => [
            ['offset' => 0, 'bytes' => 'D0CF11E0A1B11AE1'], // MS Office
        ],
        'docx' => [
            ['offset' => 0, 'bytes' => '504B0304'], // ZIP (Office Open XML)
        ],
        'xls' => [
            ['offset' => 0, 'bytes' => 'D0CF11E0A1B11AE1'],
        ],
        'xlsx' => [
            ['offset' => 0, 'bytes' => '504B0304'],
        ],
        'ppt' => [
            ['offset' => 0, 'bytes' => 'D0CF11E0A1B11AE1'],
        ],
        'pptx' => [
            ['offset' => 0, 'bytes' => '504B0304'],
        ],
        
        // Archives
        'zip' => [
            ['offset' => 0, 'bytes' => '504B0304'], // PK..
            ['offset' => 0, 'bytes' => '504B0506'], // Empty archive
            ['offset' => 0, 'bytes' => '504B0708'], // Spanned archive
        ],
        'rar' => [
            ['offset' => 0, 'bytes' => '526172211A07'], // Rar!
        ],
        '7z' => [
            ['offset' => 0, 'bytes' => '377ABCAF271C'], // 7z
        ],
        'gz' => [
            ['offset' => 0, 'bytes' => '1F8B'], // gzip
        ],
        'tar' => [
            ['offset' => 257, 'bytes' => '7573746172'], // ustar
        ],
        
        // Videos
        'mp4' => [
            ['offset' => 4, 'bytes' => '66747970'], // ftyp
        ],
        'avi' => [
            ['offset' => 0, 'bytes' => '52494646'], // RIFF
            ['offset' => 8, 'bytes' => '41564920'], // AVI
        ],
        'mkv' => [
            ['offset' => 0, 'bytes' => '1A45DFA3'], // Matroska
        ],
        'webm' => [
            ['offset' => 0, 'bytes' => '1A45DFA3'],
        ],
        
        // Audio
        'mp3' => [
            ['offset' => 0, 'bytes' => 'FFFB'], // MP3 with ID3v2
            ['offset' => 0, 'bytes' => 'FFF3'],
            ['offset' => 0, 'bytes' => '494433'], // ID3
        ],
        'wav' => [
            ['offset' => 0, 'bytes' => '52494646'], // RIFF
            ['offset' => 8, 'bytes' => '57415645'], // WAVE
        ],
        'ogg' => [
            ['offset' => 0, 'bytes' => '4F676753'], // OggS
        ],
        'flac' => [
            ['offset' => 0, 'bytes' => '664C6143'], // fLaC
        ],
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
     * Validate magic bytes
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options tùy chỉnh
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function validate($file, $options = [])
    {
        // Skip validation nếu không enable
        if (!($options['check_magic_bytes'] ?? true)) {
            return ['success' => true, 'error' => null];
        }
        
        // Check tmp file exists
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'Temporary file does not exist'
            ];
        }
        
        // Get extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Chỉ validate các extension có signature
        if (!isset($this->signatures[$ext])) {
            // Không có signature cho extension này, skip validation
            return ['success' => true, 'error' => null];
        }
        
        // Read file header (first 512 bytes should be enough)
        $handle = @fopen($file['tmp_name'], 'rb');
        if (!$handle) {
            return [
                'success' => false,
                'error' => 'Cannot read file'
            ];
        }
        
        $header = fread($handle, 512);
        fclose($handle);
        
        if ($header === false) {
            return [
                'success' => false,
                'error' => 'Cannot read file header'
            ];
        }
        
        $headerHex = bin2hex($header);
        
        // Check signatures
        $signatures = $this->signatures[$ext];
        $matched = false;
        
        foreach ($signatures as $sig) {
            $offset = $sig['offset'];
            $bytes = strtoupper($sig['bytes']);
            $bytesLength = strlen($bytes);
            
            // Extract bytes at offset
            $extractedBytes = strtoupper(substr($headerHex, $offset * 2, $bytesLength));
            
            if ($extractedBytes === $bytes) {
                $matched = true;
                break;
            }
        }
        
        if (!$matched) {
            return [
                'success' => false,
                'error' => "File signature does not match extension '{$ext}'. File may be spoofed or corrupt."
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Detect file type by magic bytes
     * 
     * @param string $filePath File path
     * @return string|null Extension detected or null
     */
    public function detectFileType($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return null;
        }
        
        $header = fread($handle, 512);
        fclose($handle);
        
        if ($header === false) {
            return null;
        }
        
        $headerHex = bin2hex($header);
        
        // Check all signatures
        foreach ($this->signatures as $ext => $signatures) {
            foreach ($signatures as $sig) {
                $offset = $sig['offset'];
                $bytes = strtoupper($sig['bytes']);
                $bytesLength = strlen($bytes);
                
                $extractedBytes = strtoupper(substr($headerHex, $offset * 2, $bytesLength));
                
                if ($extractedBytes === $bytes) {
                    return $ext;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get signature for extension
     * 
     * @param string $ext Extension
     * @return array|null Signatures array
     */
    public function getSignature($ext)
    {
        $ext = strtolower($ext);
        return $this->signatures[$ext] ?? null;
    }
    
    /**
     * Add custom signature
     * 
     * @param string $ext Extension
     * @param array $signature Signature array
     */
    public function addSignature($ext, $signature)
    {
        $ext = strtolower($ext);
        
        if (!isset($this->signatures[$ext])) {
            $this->signatures[$ext] = [];
        }
        
        $this->signatures[$ext][] = $signature;
    }
}
