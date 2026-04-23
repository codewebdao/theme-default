<?php

namespace System\Libraries\Imagify;

/**
 * BaseImageDriver - Base Image Driver
 * 
 * Abstract class kết hợp Interface và Base implementation
 * Drivers con chỉ cần implement các abstract methods
 * 
 * @package System\Libraries\Imagify
 * @version 2.0.0
 */
abstract class BaseImageDriver
{
    protected $image;
    protected $width;
    protected $height;
    protected $type;
    protected $path;
    
    // ========================================
    // ABSTRACT METHODS - Drivers phải implement
    // ========================================
    
    /**
     * Load image from file
     */
    abstract public function load($path);
    
    /**
     * Save image to file
     */
    abstract public function save($path, $quality = 90);
    
    /**
     * Resize image
     */
    abstract public function resize($width, $height, $mode = 'fit');
    
    /**
     * Crop image
     */
    abstract public function crop($x, $y, $width, $height);
    
    /**
     * Convert to WebP
     */
    abstract public function toWebP($quality = 80);
    
    /**
     * Optimize image
     */
    abstract public function optimize($options = []);
    
    /**
     * Add watermark
     */
    abstract public function watermark($watermarkPath, $position = 'bottom-right', $opacity = 50, $sizeOptions = null);
    
    /**
     * Rotate image
     */
    abstract public function rotate($angle);
    
    /**
     * Flip image
     */
    abstract public function flip($direction = 'horizontal');
    
    /**
     * Strip EXIF metadata
     * 
     * Xóa metadata EXIF từ ảnh để bảo vệ privacy:
     * - GPS location
     * - Camera info
     * - Author/copyright
     * 
     * @param bool $preserveOrientation Giữ lại orientation data
     * @return bool Success status
     */
    abstract public function stripExif($preserveOrientation = false);
    
    /**
     * Get driver name
     */
    abstract public function getDriverName();
    /**
     * Check if driver is available
     */
    abstract public static function isAvailable();
    
    /**
     * Destroy image resource
     */
    abstract public function destroy();
    
    // ========================================
    // COMMON METHODS - Dùng chung, có thể override
    // ========================================
    
    /**
     * Get image width
     */
    public function getWidth()
    {
        return $this->width;
    }
    
    /**
     * Get image height
     */
    public function getHeight()
    {
        return $this->height;
    }
    
    /**
     * Get image type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Calculate resize dimensions
     * 
     * IMPORTANT: Tất cả dimensions phải là số nguyên (int)
     * Làm tròn để tránh lỗi với GD và Imagick
     * 
     * BUG FIX: Nếu ảnh vuông (sourceWidth == sourceHeight) nhưng target lệch 1px
     * (vd: 500x501 hoặc 500x499), normalize về số lớn nhất để giữ tỷ lệ vuông
     * 
     * @param int|float $targetWidth Target width
     * @param int|float $targetHeight Target height
     * @param string $mode Resize mode (fit, fill, crop, exact)
     * @return array Calculated dimensions (all integers)
     */
    protected function calculateDimensions($targetWidth, $targetHeight, $mode = 'fit')
    {
        // IMPORTANT: Làm tròn input về số nguyên
        $targetWidth = (int) round($targetWidth);
        $targetHeight = (int) round($targetHeight);
        
        $sourceWidth = $this->width;
        $sourceHeight = $this->height;
        
        // BUG FIX: Nếu ảnh gốc vuông nhưng target lệch 1px
        // → Normalize về số lớn nhất để giữ tỷ lệ vuông
        if ($sourceWidth === $sourceHeight) {
            $diff = abs($targetWidth - $targetHeight);
            if ($diff === 1) {
                $maxDimension = max($targetWidth, $targetHeight);
                $targetWidth = $maxDimension;
                $targetHeight = $maxDimension;
            }
        }
        
        switch ($mode) {
            case 'fit':
                // Fit within dimensions, maintain aspect ratio
                $ratio = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
                return [
                    'width' => (int) ($sourceWidth * $ratio),
                    'height' => (int) ($sourceHeight * $ratio),
                    'src_x' => 0,
                    'src_y' => 0,
                    'src_width' => $sourceWidth,
                    'src_height' => $sourceHeight
                ];
                
            case 'fill':
                // Fill dimensions, crop if needed
                $ratio = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
                $newWidth = (int) ($sourceWidth * $ratio);
                $newHeight = (int) ($sourceHeight * $ratio);
                $srcX = (int) (($newWidth - $targetWidth) / (2 * $ratio));
                $srcY = (int) (($newHeight - $targetHeight) / (2 * $ratio));
                return [
                    'width' => $targetWidth,
                    'height' => $targetHeight,
                    'src_x' => $srcX,
                    'src_y' => $srcY,
                    'src_width' => (int) ($targetWidth / $ratio),
                    'src_height' => (int) ($targetHeight / $ratio)
                ];
                
            case 'exact':
                // Resize to exact dimensions, ignore aspect ratio
                return [
                    'width' => $targetWidth,
                    'height' => $targetHeight,
                    'src_x' => 0,
                    'src_y' => 0,
                    'src_width' => $sourceWidth,
                    'src_height' => $sourceHeight
                ];
                
            case 'crop':
                // Crop to exact dimensions from center
                $ratio = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
                $cropWidth = (int) ($targetWidth / $ratio);
                $cropHeight = (int) ($targetHeight / $ratio);
                return [
                    'width' => $targetWidth,
                    'height' => $targetHeight,
                    'src_x' => (int) (($sourceWidth - $cropWidth) / 2),
                    'src_y' => (int) (($sourceHeight - $cropHeight) / 2),
                    'src_width' => $cropWidth,
                    'src_height' => $cropHeight
                ];
                
            default:
                return $this->calculateDimensions($targetWidth, $targetHeight, 'fit');
        }
    }
    
    /**
     * Calculate watermark position
     * 
     * @param string $position Position name
     * @param int $wmWidth Watermark width
     * @param int $wmHeight Watermark height
     * @return array [x, y] coordinates
     */
    protected function calculateWatermarkPosition($position, $wmWidth, $wmHeight)
    {
        $margin = 10;
        
        switch ($position) {
            case 'top-left':
                return [$margin, $margin];
                
            case 'top-right':
                return [$this->width - $wmWidth - $margin, $margin];
                
            case 'bottom-left':
                return [$margin, $this->height - $wmHeight - $margin];
                
            case 'bottom-right':
                return [$this->width - $wmWidth - $margin, $this->height - $wmHeight - $margin];
                
            case 'center':
                return [
                    (int) (($this->width - $wmWidth) / 2),
                    (int) (($this->height - $wmHeight) / 2)
                ];
                
            default:
                return [$this->width - $wmWidth - $margin, $this->height - $wmHeight - $margin];
        }
    }
}
