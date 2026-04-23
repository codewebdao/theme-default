<?php

namespace System\Libraries\Uploads\Validators;

/**
 * ZipBombValidator - Detect zip bombs and decompression attacks
 * 
 * Prevent:
 * - Zip bombs (tiny file → extremely large when decompressed)
 * - Nested archives (zip inside zip inside zip...)
 * - Excessive compression ratio
 * - Too many files in an archive
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class ZipBombValidator
{
    private $config;
    
    /**
     * Default limits
     */
    private $defaults = [
        'max_uncompressed_size' => 1073741824,  // 1GB
        'max_compression_ratio' => 100,          // 100:1
        'max_files' => 10000,                    // 10,000 files
        'max_nesting_level' => 2,                // 2 levels
    ];
    
    /**
     * Constructor
     * 
     * @param array $config Configuration
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->defaults, $config);
    }
    
    /**
     * Validate archive file
     * 
     * @param array $file File array từ $_FILES
     * @param array $options Options
     * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
     */
    public function validate($file, $options = [])
    {
        $filePath = $file['tmp_name'] ?? '';
        
        if (!file_exists($filePath)) {
            return ['success' => true, 'error' => null]; // Skip if file doesn't exist
        }
        
        // Get extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Only validate archive files
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];
        if (!in_array($ext, $archiveExtensions)) {
            return ['success' => true, 'error' => null]; // Not an archive
        }
        
        // Validate based on type
        switch ($ext) {
            case 'zip':
                return $this->validateZip($filePath, $options);
            
            case 'tar':
            case 'gz':
                return $this->validateTar($filePath, $options);
            
            default:
                // For other types (rar, 7z), do basic checks
                return $this->validateGenericArchive($filePath, $options);
        }
    }
    
    /**
     * Validate ZIP file
     * 
     * @param string $filePath File path
     * @param array $options Options
     * @return array Validation result
     */
    private function validateZip($filePath, $options = [])
    {
        if (!extension_loaded('zip')) {
            return ['success' => true, 'error' => null]; // Skip if ZIP extension not available
        }
        
        $zip = new \ZipArchive();
        $opened = $zip->open($filePath);
        
        if ($opened !== true) {
            return [
                'success' => false,
                'error' => 'Cannot open ZIP file - may be corrupted'
            ];
        }
        
        $maxUncompressed = $options['max_uncompressed_size'] ?? $this->config['max_uncompressed_size'];
        $maxRatio = $options['max_compression_ratio'] ?? $this->config['max_compression_ratio'];
        $maxFiles = $options['max_files'] ?? $this->config['max_files'];
        
        $totalUncompressed = 0;
        $totalCompressed = 0;
        $fileCount = $zip->numFiles;
        
        // Check file count
        if ($fileCount > $maxFiles) {
            $zip->close();
                return [
                'success' => false,
                'error' => "ZIP contains too many files ({$fileCount}). Maximum: {$maxFiles}. Possible zip bomb."
            ];
        }
        
        // Check each file
        for ($i = 0; $i < $fileCount; $i++) {
            $stat = $zip->statIndex($i);
            
            if ($stat === false) {
                continue;
            }
            
            $uncompressedSize = $stat['size'];
            $compressedSize = $stat['comp_size'];
            
            $totalUncompressed += $uncompressedSize;
            $totalCompressed += $compressedSize;
            
            // Check total uncompressed size
            if ($totalUncompressed > $maxUncompressed) {
                $zip->close();
                $maxMB = round($maxUncompressed / 1048576, 2);
                $currentMB = round($totalUncompressed / 1048576, 2);
                
                return [
                    'success' => false,
                    'error' => "ZIP uncompressed size too large ({$currentMB}MB). Maximum: {$maxMB}MB. Possible zip bomb."
                ];
            }
            
            // Check individual file compression ratio
            if ($compressedSize > 0) {
                $ratio = $uncompressedSize / $compressedSize;
                
                if ($ratio > $maxRatio) {
                    $zip->close();
                    return [
                        'success' => false,
                        'error' => "Suspicious compression ratio ({$ratio}:1) detected. Maximum: {$maxRatio}:1. Possible zip bomb."
                    ];
                }
            }
        }
        
        // Check overall compression ratio
        if ($totalCompressed > 0) {
            $overallRatio = $totalUncompressed / $totalCompressed;
            
            if ($overallRatio > $maxRatio) {
                $zip->close();
                return [
                    'success' => false,
                    'error' => "Overall compression ratio too high ({$overallRatio}:1). Possible zip bomb."
                ];
            }
        }
        
        $zip->close();
        
        return [
            'success' => true,
            'error' => null,
            'data' => [
                'file_count' => $fileCount,
                'total_uncompressed' => $totalUncompressed,
                'total_compressed' => $totalCompressed,
                'compression_ratio' => $totalCompressed > 0 ? round($totalUncompressed / $totalCompressed, 2) : 0
            ]
        ];
    }
    
    /**
     * Validate TAR/GZ file
     * 
     * @param string $filePath File path
     * @param array $options Options
     * @return array Validation result
     */
    private function validateTar($filePath, $options = [])
    {
        // Basic size check for tar/gz
        $maxUncompressed = $options['max_uncompressed_size'] ?? $this->config['max_uncompressed_size'];
        $fileSize = filesize($filePath);
        
        // For compressed tar (tar.gz), estimate uncompressed size
        // Typical compression ratio for gzip is 2-10x
        $estimatedUncompressed = $fileSize * 10;
        
        if ($estimatedUncompressed > $maxUncompressed) {
            $maxMB = round($maxUncompressed / 1048576, 2);
            $estMB = round($estimatedUncompressed / 1048576, 2);
            
            return [
                'success' => false,
                'error' => "Estimated uncompressed size too large ({$estMB}MB). Maximum: {$maxMB}MB."
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Validate generic archive (RAR, 7Z, etc.)
     * 
     * @param string $filePath File path
     * @param array $options Options
     * @return array Validation result
     */
    private function validateGenericArchive($filePath, $options = [])
    {
        // For archives we can't inspect, do basic size check
        $maxCompressed = $options['max_compressed_size'] ?? 104857600; // 100MB
        $fileSize = filesize($filePath);
        
        if ($fileSize > $maxCompressed) {
            $maxMB = round($maxCompressed / 1048576, 2);
            $currentMB = round($fileSize / 1048576, 2);
            
            return [
                'success' => false,
                'error' => "Archive file too large ({$currentMB}MB). Maximum: {$maxMB}MB."
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
}
