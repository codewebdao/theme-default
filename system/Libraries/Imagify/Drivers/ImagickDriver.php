<?php

namespace System\Libraries\Imagify\Drivers;

use \Imagick;
use \ImagickPixel;

use System\Libraries\Imagify\BaseImageDriver;
/**
 * ImagickDriver - Imagick Extension Driver
 * 
 * @package System\Libraries\Imagify\Drivers
 * @version 2.0.0
 */
class ImagickDriver extends BaseImageDriver
{
    public function load($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        
        try {
            $class = '\\Imagick';
            $this->image = new $class($path);
            $this->path = $path;
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
            $this->type = strtolower($this->image->getImageFormat());
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function save($path, $quality = 90)
    {
        try {
            // FIX: Validate quality
            $quality = max(0, min(100, (int) $quality));
            
            // FIX: Set format based on file extension
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $formatMap = [
                'jpg' => 'JPEG',
                'jpeg' => 'JPEG',
                'png' => 'PNG',
                'gif' => 'GIF',
                'webp' => 'WEBP'
            ];
            
            if (isset($formatMap[$ext])) {
                $this->image->setImageFormat($formatMap[$ext]);
            }
            
            $this->image->setImageCompressionQuality($quality);
            return $this->image->writeImage($path);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function resize($width, $height, $mode = 'fit')
    {
        // IMPORTANT: Làm tròn về số nguyên
        $width = (int) round($width);
        $height = (int) round($height);
        
        // FIX: Validate dimensions
        if ($width <= 0 || $height <= 0) {
            return false;
        }
        
        $dims = $this->calculateDimensions($width, $height, $mode);
        
        try {
            // Crop first if needed
            if ($dims['src_x'] > 0 || $dims['src_y'] > 0 || 
                $dims['src_width'] < $this->width || $dims['src_height'] < $this->height) {
                $this->image->cropImage(
                    $dims['src_width'],
                    $dims['src_height'],
                    $dims['src_x'],
                    $dims['src_y']
                );
                // FIX: Reset image page after crop to prevent memory leak
                $this->image->setImagePage(0, 0, 0, 0);
            }
            
            // Then resize
            $filter = defined('Imagick::FILTER_LANCZOS') ? constant('Imagick::FILTER_LANCZOS') : 1;
            $this->image->resizeImage($dims['width'], $dims['height'], $filter, 1);
            
            $this->width = $dims['width'];
            $this->height = $dims['height'];
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function crop($x, $y, $width, $height)
    {
        // IMPORTANT: Làm tròn về số nguyên
        $x = (int) round($x);
        $y = (int) round($y);
        $width = (int) round($width);
        $height = (int) round($height);
        
        // FIX: Validate dimensions
        if ($width <= 0 || $height <= 0) {
            return false;
        }
        
        // FIX: Validate crop area within image bounds
        if ($x < 0 || $y < 0 || $x + $width > $this->width || $y + $height > $this->height) {
            return false;
        }
        
        try {
            $this->image->cropImage($width, $height, $x, $y);
            // FIX: Reset image page after crop to prevent memory leak
            $this->image->setImagePage(0, 0, 0, 0);
            
            $this->width = $width;
            $this->height = $height;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function toWebP($quality = 80)
    {
        try {
            $this->image->setImageFormat('webp');
            $this->image->setImageCompressionQuality($quality);
            $this->type = 'webp';
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function optimize($options = [])
    {
        try {
            // Strip metadata
            $this->image->stripImage();
            
            // Set interlace for progressive loading
            $scheme = defined('Imagick::INTERLACE_PLANE') ? constant('Imagick::INTERLACE_PLANE') : 0;
            if (method_exists($this->image, 'setInterlaceScheme')) {
                $this->image->setInterlaceScheme($scheme);
            }
            
            // Reduce quality slightly if not specified
            if (!isset($options['quality'])) {
                $this->image->setImageCompressionQuality(85);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function watermark($watermarkPath, $position = 'bottom-right', $opacity = 50, $sizeOptions = null)
    {
        if (!file_exists($watermarkPath)) {
            return false;
        }
        
        try {
            $cls = '\\Imagick';
            $watermark = new $cls($watermarkPath); // phpcs:ignore
            
            // FIX: Validate opacity range
            $opacity = max(0, min(100, (int) $opacity));
            
            // Ensure watermark has alpha channel and set global opacity without losing per-pixel alpha
            if (method_exists($watermark, 'getImageAlphaChannel') && !$watermark->getImageAlphaChannel()) {
                if (defined('Imagick::ALPHACHANNEL_SET')) {
                    $watermark->setImageAlphaChannel(constant('Imagick::ALPHACHANNEL_SET'));
                }
            }
            if (defined('Imagick::EVALUATE_MULTIPLY') && defined('Imagick::CHANNEL_ALPHA')) {
                $watermark->evaluateImage(constant('Imagick::EVALUATE_MULTIPLY'), $opacity / 100, constant('Imagick::CHANNEL_ALPHA'));
            }
            
            $wmWidth = $watermark->getImageWidth();
            $wmHeight = $watermark->getImageHeight();
            
            // Calculate position (method from Base)
            list($x, $y) = $this->calculateWatermarkPosition($position, $wmWidth, $wmHeight);
            
            // Ensure destination has alpha channel to preserve transparency
            if (method_exists($this->image, 'getImageAlphaChannel') && !$this->image->getImageAlphaChannel()) {
                if (defined('Imagick::ALPHACHANNEL_SET')) {
                    $this->image->setImageAlphaChannel(constant('Imagick::ALPHACHANNEL_SET'));
                }
            }
            // Apply watermark with alpha preserved
            if (defined('Imagick::COMPOSITE_OVER')) {
                $this->image->compositeImage($watermark, constant('Imagick::COMPOSITE_OVER'), $x, $y);
            } else {
                $this->image->compositeImage($watermark, 1, $x, $y); // 1 ~ COMPOSITE_OVER fallback
            }
            
            $watermark->destroy();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function rotate($angle)
    {
        try {
            // FIX: Validate and normalize angle to 0-360
            $angle = (float) $angle;
            $angle = fmod($angle, 360);
            if ($angle < 0) {
                $angle += 360;
            }
            
            $pxClass = '\\ImagickPixel';
            $bg = class_exists($pxClass) ? new $pxClass('none') : null; // phpcs:ignore
            $this->image->rotateImage($bg, $angle);
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function flip($direction = 'horizontal')
    {
        try {
            if ($direction === 'horizontal') {
                $this->image->flopImage();
            } else {
                $this->image->flipImage();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Strip EXIF metadata
     * 
     * Xóa tất cả EXIF/IPTC metadata từ ảnh
     * 
     * @param bool $preserveOrientation Giữ lại orientation data
     * @return bool
     */
    public function stripExif($preserveOrientation = false)
    {
        try {
            if ($preserveOrientation) {
                // Get orientation before stripping
                $orientation = $this->image->getImageOrientation();
            }
            
            // Strip all EXIF/IPTC data
            $this->image->stripImage();
            
            if ($preserveOrientation && isset($orientation)) {
                // Restore orientation
                $this->image->setImageOrientation($orientation);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getDriverName()
    {
        return 'imagick';
    }
    
    public static function isAvailable()
    {
        return extension_loaded('imagick') && class_exists('\Imagick');
    }
    
    public function destroy()
    {
        if ($this->image) {
            $this->image->clear();
            $this->image->destroy();
            $this->image = null;
        }
    }
}
