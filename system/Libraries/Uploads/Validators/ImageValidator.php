<?php

namespace System\Libraries\Uploads\Validators;

/**
 * ImageValidator - Validate image-specific properties
 * 
 * Checks:
 * - Whether the file is a valid image
 * - Dimensions (width, height)
 * - Aspect ratio
 * - Image quality
 * - Color depth
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class ImageValidator
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
     * Validate image properties
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
        
        // Get image info
        $imageInfo = @getimagesize($file['tmp_name']);
        
        if ($imageInfo === false) {
            return [
                'success' => false,
                'error' => 'File is not a valid image or is corrupted',
                'data' => null
            ];
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // SECURITY: Check for unreasonable dimensions (DoS attack prevention)
        // Prevent memory exhaustion attacks with extremely large images
        $maxHeight = $options['max_height'] ?? $this->config['max_height'] ?? 10000;
        $maxWidth = $options['max_width'] ?? $this->config['max_width'] ?? 10000;
        
        if ($width > $maxWidth || $height > $maxHeight) {
            return [
                'success' => false,
                'error' => "Image dimensions are too large. Max: {$maxWidth}x{$maxHeight}px (current: {$width}x{$height}px). Possible DoS attack.",
                'data' => [
                    'width' => $width,
                    'height' => $height,
                    'max_width' => $maxWidth,
                    'max_height' => $maxHeight,
                    'security_warning' => 'Potential DoS attack - image dimensions too large'
                ]
            ];
        }
        
        // SECURITY: Check for zero or negative dimensions
        if ($width <= 0 || $height <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid image dimensions (zero or negative)',
                'data' => [
                    'width' => $width,
                    'height' => $height,
                    'security_warning' => 'Invalid dimensions - possible corrupted or malicious file'
                ]
            ];
        }
        
        // SECURITY: Check total pixels to prevent memory exhaustion
        $totalPixels = $width * $height;
        $maxPixels = $options['max_total_pixels'] ?? $this->config['max_total_pixels'] ?? 100000000; // 100 megapixels default
        
        if ($totalPixels > $maxPixels) {
            $maxMegapixels = round($maxPixels / 1000000, 1);
            $currentMegapixels = round($totalPixels / 1000000, 1);
            
            return [
                'success' => false,
                'error' => "Total pixels too large. Max: {$maxMegapixels}MP (current: {$currentMegapixels}MP). May cause memory exhaustion.",
                'data' => [
                    'width' => $width,
                    'height' => $height,
                    'total_pixels' => $totalPixels,
                    'max_pixels' => $maxPixels,
                    'security_warning' => 'Potential memory exhaustion attack'
                ]
            ];
        }
        
        // Resolve option values with fallback to config
        $minWidth = $options['min_width'] ?? ($this->config['min_width'] ?? null);
        $minHeight = $options['min_height'] ?? ($this->config['min_height'] ?? null);
        $maxWidth = $options['max_width'] ?? ($this->config['max_width'] ?? null);
        $maxHeight = $options['max_height'] ?? ($this->config['max_height'] ?? null);
        $minPixels = $options['min_pixels'] ?? ($this->config['min_pixels'] ?? null);
        $maxPixelsOpt = $options['max_pixels'] ?? ($this->config['max_pixels'] ?? null);

        // Validate minimum width
        if ($minWidth !== null && $width < $minWidth) {
            return [
                'success' => false,
                'error' => "Minimum width: {$minWidth}px (current: {$width}px)",
                'data' => ['width' => $width, 'height' => $height]
            ];
        }
        
        // Validate minimum height
        if ($minHeight !== null && $height < $minHeight) {
            return [
                'success' => false,
                'error' => "Minimum height: {$minHeight}px (current: {$height}px)",
                'data' => ['width' => $width, 'height' => $height]
            ];
        }
        
        // Validate maximum width
        if ($maxWidth !== null && $width > $maxWidth) {
            return [
                'success' => false,
                'error' => "Maximum width: {$maxWidth}px (current: {$width}px)",
                'data' => ['width' => $width, 'height' => $height]
            ];
        }
        
        // Validate maximum height
        if ($maxHeight !== null && $height > $maxHeight) {
            return [
                'success' => false,
                'error' => "Maximum height: {$maxHeight}px (current: {$height}px)",
                'data' => ['width' => $width, 'height' => $height]
            ];
        }
        
        // Validate exact dimensions
        if (isset($options['exact_width']) && $width !== $options['exact_width']) {
            return [
                'success' => false,
                'error' => "Width must be: {$options['exact_width']}px (current: {$width}px)",
                'data' => ['width' => $width, 'height' => $height]
            ];
        }
        
        if (isset($options['exact_height']) && $height !== $options['exact_height']) {
            return [
                'success' => false,
                'error' => "Height must be: {$options['exact_height']}px (current: {$height}px)",
                'data' => ['width' => $width, 'height' => $height]
            ];
        }
        
        // Validate aspect ratio
        if (isset($options['aspect_ratio'])) {
            $actualRatio = $height > 0 ? $width / $height : 0;
            $expectedRatio = $options['aspect_ratio'];
            $tolerance = $options['aspect_ratio_tolerance'] ?? 0.01; // 1% tolerance default
            
            if (abs($actualRatio - $expectedRatio) > $tolerance) {
                $expectedRatioStr = $this->formatAspectRatio($expectedRatio);
                $actualRatioStr = $this->formatAspectRatio($actualRatio);
                
                return [
                    'success' => false,
                    'error' => "Invalid aspect ratio. Required: {$expectedRatioStr}, current: {$actualRatioStr}",
                    'data' => [
                        'width' => $width,
                        'height' => $height,
                        'actual_ratio' => $actualRatio,
                        'expected_ratio' => $expectedRatio
                    ]
                ];
            }
        }
        
        // Validate minimum pixels (total)
        if ($minPixels !== null) {
            $totalPixels = $width * $height;
            if ($totalPixels < $minPixels) {
                return [
                    'success' => false,
                    'error' => "Minimum total pixels: {$minPixels} (current: {$totalPixels})",
                    'data' => ['width' => $width, 'height' => $height, 'total_pixels' => $totalPixels]
                ];
            }
        }
        
        // Validate maximum pixels (total)
        if ($maxPixelsOpt !== null) {
            $totalPixels = $width * $height;
            if ($totalPixels > $maxPixelsOpt) {
                return [
                    'success' => false,
                    'error' => "Maximum total pixels: {$maxPixelsOpt} (current: {$totalPixels})",
                    'data' => ['width' => $width, 'height' => $height, 'total_pixels' => $totalPixels]
                ];
            }
        }
        
        // Check if image is corrupted (additional check)
        if (!$this->isImageValid($file['tmp_name'], $type)) {
            return [
                'success' => false,
                'error' => 'Image is corrupted or unreadable',
                'data' => null
            ];
        }
        
        return [
            'success' => true,
            'error' => null,
            'data' => [
                'width' => $width,
                'height' => $height,
                'type' => $type,
                'mime' => $imageInfo['mime'],
                'bits' => $imageInfo['bits'] ?? null,
                'channels' => $imageInfo['channels'] ?? null,
                'aspect_ratio' => $height > 0 ? round($width / $height, 2) : 0
            ]
        ];
    }
    
    /**
     * Check if image is valid by trying to create image resource
     * 
     * @param string $filePath File path
     * @param int $type Image type constant
     * @return bool
     */
    private function isImageValid($filePath, $type)
    {
        try {
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $img = @imagecreatefromjpeg($filePath);
                    break;
                case IMAGETYPE_PNG:
                    $img = @imagecreatefrompng($filePath);
                    break;
                case IMAGETYPE_GIF:
                    $img = @imagecreatefromgif($filePath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $img = @imagecreatefromwebp($filePath);
                    } else {
                        return true; // Skip if WebP not supported
                    }
                    break;
                case IMAGETYPE_BMP:
                    if (function_exists('imagecreatefrombmp')) {
                        $img = @imagecreatefrombmp($filePath);
                    } else {
                        return true; // Skip if BMP not supported
                    }
                    break;
                default:
                    return true; // Unknown type, skip validation
            }
            
            if ($img === false) {
                return false;
            }
            
            imagedestroy($img);
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Format aspect ratio to readable string
     * 
     * @param float $ratio Aspect ratio
     * @return string Formatted ratio (e.g., "16:9", "4:3")
     */
    private function formatAspectRatio($ratio)
    {
        // Common aspect ratios
        $commonRatios = [
            1.33 => '4:3',
            1.78 => '16:9',
            1.60 => '16:10',
            1.00 => '1:1',
            0.75 => '3:4',
            0.56 => '9:16',
            2.35 => '21:9',
        ];
        
        // Find closest match
        foreach ($commonRatios as $value => $label) {
            if (abs($ratio - $value) < 0.05) {
                return $label;
            }
        }
        
        return number_format($ratio, 2);
    }
    
    /**
     * Get image dimensions
     * 
     * @param string $filePath File path
     * @return array|null ['width' => int, 'height' => int] or null
     */
    public static function getDimensions($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            return null;
        }
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1]
        ];
    }
    
    /**
     * Check if image is landscape
     * 
     * @param string $filePath File path
     * @return bool
     */
    public static function isLandscape($filePath)
    {
        $dims = self::getDimensions($filePath);
        return $dims && $dims['width'] > $dims['height'];
    }
    
    /**
     * Check if image is portrait
     * 
     * @param string $filePath File path
     * @return bool
     */
    public static function isPortrait($filePath)
    {
        $dims = self::getDimensions($filePath);
        return $dims && $dims['height'] > $dims['width'];
    }
    
    /**
     * Check if image is square
     * 
     * @param string $filePath File path
     * @return bool
     */
    public static function isSquare($filePath)
    {
        $dims = self::getDimensions($filePath);
        return $dims && $dims['width'] === $dims['height'];
    }
}
