<?php

namespace System\Libraries\Imagify\Drivers;

use System\Libraries\Imagify\BaseImageDriver;

/**
 * GDDriver - GD Library Image Driver
 * 
 * @package System\Libraries\Imagify\Drivers
 * @version 2.0.0
 */
class GDDriver extends BaseImageDriver
{
    public function load($path)
    {
        // Kiểm tra path hợp lệ
        if (empty($path) || !is_string($path)) {
            return false;
        }
        
        if (!file_exists($path)) {
            return false;
        }
        
        $this->path = $path;
        $info = getimagesize($path);
        
        if (!$info) {
            return false;
        }
        
        $this->width = $info[0];
        $this->height = $info[1];
        $this->type = image_type_to_extension($info[2], false);
        
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $this->image = @imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $this->image = @imagecreatefrompng($path);
                if ($this->image) {
                    imagealphablending($this->image, false);
                    imagesavealpha($this->image, true);
                }
                break;
            case IMAGETYPE_GIF:
                $this->image = @imagecreatefromgif($path);
                break;
            case IMAGETYPE_WEBP:
                $this->image = @imagecreatefromwebp($path);
                break;
            default:
                return false;
        }
        
        // FIX: Check if image resource created successfully
        if ($this->image === false || !is_resource($this->image)) {
            return false;
        }
        
        return true;
    }
    
    public function save($path, $quality = 90)
    {
        // Kiểm tra image resource hợp lệ
        if (!$this->image || !is_resource($this->image) && !($this->image instanceof \GdImage)) {
            return false;
        }
        
        // Kiểm tra path hợp lệ
        if (empty($path) || !is_string($path)) {
            return false;
        }
        
        // FIX: Validate quality parameter
        $quality = max(0, min(100, (int) $quality));
        
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return @imagejpeg($this->image, $path, $quality);
            case 'png':
                // PNG quality: 0 (best) to 9 (worst compression)
                $pngQuality = (int) ((100 - $quality) / 11.111111);
                $pngQuality = max(0, min(9, $pngQuality));
                return @imagepng($this->image, $path, $pngQuality);
            case 'gif':
                return @imagegif($this->image, $path);
            case 'webp':
                return @imagewebp($this->image, $path, $quality);
            default:
                return false;
        }
    }
    
    public function resize($width, $height, $mode = 'fit')
    {
        // IMPORTANT: Làm tròn về số nguyên
        $width = (int) round($width);
        $height = (int) round($height);
        
        $dims = $this->calculateDimensions($width, $height, $mode);
        
        // FIX: Check if dimensions are valid
        if ($dims['width'] <= 0 || $dims['height'] <= 0) {
            return false;
        }
        
        $newImage = @imagecreatetruecolor($dims['width'], $dims['height']);
        
        // FIX: Check if image creation succeeded
        if ($newImage === false) {
            return false;
        }
        
        // FIX: Preserve transparency for PNG/GIF/WebP
        if ($this->type === 'png' || $this->type === 'gif' || $this->type === 'webp') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $dims['width'], $dims['height'], $transparent);
        }
        
        imagecopyresampled(
            $newImage, $this->image,
            0, 0,
            $dims['src_x'], $dims['src_y'],
            $dims['width'], $dims['height'],
            $dims['src_width'], $dims['src_height']
        );
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $dims['width'];
        $this->height = $dims['height'];
        
        return true;
    }
    
    public function crop($x, $y, $width, $height)
    {
        // IMPORTANT: Làm tròn về số nguyên
        $x = (int) round($x);
        $y = (int) round($y);
        $width = (int) round($width);
        $height = (int) round($height);
        
        // FIX: Check if dimensions are valid
        if ($width <= 0 || $height <= 0) {
            return false;
        }
        
        $newImage = @imagecreatetruecolor($width, $height);
        
        // FIX: Check if image creation succeeded
        if ($newImage === false) {
            return false;
        }
        
        // FIX: Preserve transparency for PNG/GIF/WebP
        if ($this->type === 'png' || $this->type === 'gif' || $this->type === 'webp') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        imagecopy($newImage, $this->image, 0, 0, $x, $y, $width, $height);
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;
        
        return true;
    }
    
    public function toWebP($quality = 80)
    {
        $this->type = 'webp';
        return true;
    }
    
    public function optimize($options = [])
    {
        // GD doesn't have built-in optimization
        // Just return true
        return true;
    }
    
    /**
     * Strip EXIF metadata
     * 
     * GD re-encodes image which automatically strips EXIF
     * 
     * @param bool $preserveOrientation Giữ lại orientation (not supported in GD)
     * @return bool
     */
    public function stripExif($preserveOrientation = false)
    {
        // GD automatically strips EXIF when re-encoding
        // No action needed
        return true;
    }
    
    public function watermark($watermarkPath, $position = 'bottom-right', $opacity = 50, $sizeOptions = null)
    {
        
        if (!file_exists($watermarkPath)) {
            return false;
        }
        
        // FIX: Validate opacity
        $opacity = max(0, min(100, (int) $opacity));
        
        // Load watermark
        $watermark = $this->loadWatermarkImage($watermarkPath);
        if (!$watermark) {
            return false;
        }
        
        $originalWmWidth = imagesx($watermark);
        $originalWmHeight = imagesy($watermark);
        
        // Calculate new size
        $newSize = $this->calculateWatermarkSize(
            $originalWmWidth, 
            $originalWmHeight, 
            $this->width, 
            $this->height, 
            $sizeOptions
        );
        
        
        // Resize watermark if needed
        if ($newSize['width'] != $originalWmWidth || $newSize['height'] != $originalWmHeight) {
            $resizedWatermark = $this->resizeWatermark($watermark, $newSize['width'], $newSize['height']);
            imagedestroy($watermark);
            $watermark = $resizedWatermark;
        }
        
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);
        
        // Calculate position (method from Base)
        list($x, $y) = $this->calculateWatermarkPosition($position, $wmWidth, $wmHeight);
        
        // Preserve transparency on destination if applicable
        if ($this->type === 'png' || $this->type === 'gif' || $this->type === 'webp') {
            imagealphablending($this->image, true);
            imagesavealpha($this->image, true);
        }

        // Apply overall opacity to watermark if needed (using per-pixel alpha scaling)
        if ($opacity < 100) {
            $this->applyOpacityToImage($watermark, $opacity);
        }

        // Copy watermark preserving its alpha channel (avoid imagecopymerge which breaks alpha)
        imagecopy($this->image, $watermark, $x, $y, 0, 0, $wmWidth, $wmHeight);
        imagedestroy($watermark);
        
        return true;
    }

    /**
     * Apply global opacity to a truecolor image with alpha channel (0-100)
     * Scales per-pixel alpha to keep transparency and avoid black background.
     */
    private function applyOpacityToImage($img, $opacity)
    {
        if (!$img || (!is_resource($img) && !($img instanceof \GdImage))) {
            return;
        }
        $opacity = max(0, min(100, (int) $opacity));
        if ($opacity === 100) {
            return;
        }
        $width = imagesx($img);
        $height = imagesy($img);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        // Convert 0-100 opacity to alpha multiplier (0 transparent .. 1 opaque)
        $alphaMultiplier = $opacity / 100.0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                // Extract RGBA
                $a = ($rgba & 0x7F000000) >> 24; // 0..127 (0 opaque)
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                // Recalculate alpha: keep existing alpha and apply global factor
                // Convert to 0..1 alpha, scale, then back to 0..127
                $alpha01 = $a / 127.0;
                $alpha01 = 1 - ((1 - $alpha01) * $alphaMultiplier);
                $newA = (int) round($alpha01 * 127);
                $color = imagecolorallocatealpha($img, $r, $g, $b, max(0, min(127, $newA)));
                if ($color !== false) {
                    imagesetpixel($img, $x, $y, $color);
                }
            }
        }
        imagealphablending($img, true);
        imagesavealpha($img, true);
    }
    
    private function loadWatermarkImage($watermarkPath)
    {
        // FIX: Support multiple watermark formats
        $info = @getimagesize($watermarkPath);
        if (!$info) {
            return false;
        }
        
        switch ($info[2]) {
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($watermarkPath);
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($watermarkPath);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($watermarkPath);
            case IMAGETYPE_WEBP:
                return @imagecreatefromwebp($watermarkPath);
            default:
                return false;
        }
    }
    
    private function calculateWatermarkSize($wmWidth, $wmHeight, $imgWidth, $imgHeight, $sizeOptions)
    {
        if (!$sizeOptions || empty($sizeOptions)) {
            return ['width' => $wmWidth, 'height' => $wmHeight];
        }
        
        // Size mode: scale, fixed, width, height, max
        if (isset($sizeOptions['watermark_scale'])) {
            // Scale by percentage of image size (changed from watermark size for better UX)
            // But maintain watermark aspect ratio to avoid distortion
            $scale = max(0.01, min(1.0, (float) $sizeOptions['watermark_scale']));
            
            // Calculate target size based on image dimensions
            $targetWidth = (int) ($imgWidth * $scale);
            $targetHeight = (int) ($imgHeight * $scale);
            
            // Calculate scale factor to maintain watermark aspect ratio
            $widthRatio = $targetWidth / $wmWidth;
            $heightRatio = $targetHeight / $wmHeight;
            $aspectScale = min($widthRatio, $heightRatio); // Use smaller ratio to fit within bounds
            
            return [
                'width' => (int) ($wmWidth * $aspectScale),
                'height' => (int) ($wmHeight * $aspectScale)
            ];
        }
        
        if (isset($sizeOptions['watermark_size'])) {
            // Fixed size: "100x50"
            if (preg_match('/^(\d+)x(\d+)$/', $sizeOptions['watermark_size'], $matches)) {
                return [
                    'width' => (int) $matches[1],
                    'height' => (int) $matches[2]
                ];
            }
        }
        
        if (isset($sizeOptions['watermark_width'])) {
            // Fixed width, auto height
            $ratio = $wmHeight / $wmWidth;
            return [
                'width' => (int) $sizeOptions['watermark_width'],
                'height' => (int) ($sizeOptions['watermark_width'] * $ratio)
            ];
        }
        
        if (isset($sizeOptions['watermark_height'])) {
            // Fixed height, auto width
            $ratio = $wmWidth / $wmHeight;
            return [
                'width' => (int) ($sizeOptions['watermark_height'] * $ratio),
                'height' => (int) $sizeOptions['watermark_height']
            ];
        }
        
        
        return ['width' => $wmWidth, 'height' => $wmHeight];
    }
    
    private function resizeWatermark($watermark, $newWidth, $newHeight)
    {
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        
        imagecopyresampled(
            $resized, $watermark,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            imagesx($watermark), imagesy($watermark)
        );
        
        return $resized;
    }
    
    public function rotate($angle)
    {
        // FIX: Validate angle
        $angle = (float) $angle;
        
        $rotated = @imagerotate($this->image, -$angle, 0);
        
        // FIX: Check if rotation succeeded
        if ($rotated === false) {
            return false;
        }
        
        imagedestroy($this->image);
        $this->image = $rotated;
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return true;
    }
    
    public function flip($direction = 'horizontal')
    {
        if ($direction === 'horizontal') {
            imageflip($this->image, IMG_FLIP_HORIZONTAL);
        } else {
            imageflip($this->image, IMG_FLIP_VERTICAL);
        }
        return true;
    }
    
    public function getDriverName()
    {
        return 'gd';
    }
    
    public static function isAvailable()
    {
        return extension_loaded('gd');
    }
    
    public function destroy()
    {
        if ($this->image) {
            imagedestroy($this->image);
            $this->image = null;
        }
    }
}
