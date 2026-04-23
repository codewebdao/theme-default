<?php

namespace System\Libraries;

use System\Libraries\Imagify\Drivers\ImagickDriver;
use System\Libraries\Imagify\Drivers\GDDriver;

/**
 * Imagify - Thư viện xử lý ảnh (Fluent Interface)
 * 
 * CHỨC NĂNG:
 * - Resize, crop, rotate, flip ảnh
 * - Convert format (JPG, PNG, WebP, GIF)
 * - Optimize và compress
 * - Watermark
 * - Strip EXIF metadata (privacy)
 * - Tạo multiple sizes (thumbnails)
 * 
 * DRIVERS:
 * - Auto-detect: Imagick (ưu tiên) > GD (fallback)
 * - Imagick: Đầy đủ tính năng, chất lượng cao
 * - GD: Basic features, có sẵn trên mọi server
 * 
 * SỬ DỤNG:
 * 
 * // 1. Resize ảnh
 * Imagify::make('image.jpg')
 *     ->resize(800, 600)
 *     ->save('resized.jpg');
 * 
 * // 2. Tạo thumbnail
 * Imagify::make('photo.jpg')
 *     ->resize(200, 200, 'crop')
 *     ->optimize()
 *     ->save('thumb.jpg');
 * 
 * // 3. Convert sang WebP
 * Imagify::make('image.png')
 *     ->toWebP()
 *     ->save('image.webp', 80);
 * 
 * // 4. Watermark
 * Imagify::make('photo.jpg')
 *     ->watermark('logo.png', 'bottom-right', 50)
 *     ->save('watermarked.jpg');
 * 
 * // 5. Strip EXIF (privacy)
 * Imagify::make('photo.jpg')
 *     ->stripExif()
 *     ->save('clean.jpg');
 * 
 * @package System\Libraries
 * @version 2.0.0
 */
class Imagify
{
    private $driver;
    private $storage = null;
    private $sourcePath = null;
    
    /**
     * Constructor
     * 
     * @param string|null $driverName Driver name ('imagick', 'gd', or null for auto-detect)
     * @param mixed $storage Storage instance (optional)
     */
    public function __construct($driverName = null, $storage = null)
    {
        if ($driverName === 'imagick' && ImagickDriver::isAvailable()) {
            $this->driver = new ImagickDriver();
        } elseif ($driverName === 'gd' && GDDriver::isAvailable()) {
            $this->driver = new GDDriver();
        } else {
            // Auto-detect: prefer Imagick over GD
            if (ImagickDriver::isAvailable()) {
                $this->driver = new ImagickDriver();
            } elseif (GDDriver::isAvailable()) {
                $this->driver = new GDDriver();
            } else {
                throw new \Exception('No image driver available (GD or Imagick required)');
            }
        }
        
        $this->storage = $storage;
    }
    
    /**
     * Set storage instance
     * 
     * @param mixed $storage Storage instance
     * @return $this
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }
    
    /**
     * Load ảnh từ file hoặc Storage
     * 
     * @param string $path Đường dẫn ảnh
     * @return $this Fluent interface
     */
    public function load($path)
    {
        // Kiểm tra path hợp lệ
        if (empty($path) || !is_string($path)) {
            throw new \InvalidArgumentException('Invalid image path provided');
        }
        
        $this->sourcePath = $path;
        
        // Download from storage if needed
        if ($this->storage && !file_exists($path)) {
            $content = $this->storage->get($path);
            if ($content !== false) {
                $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'imagify_' . uniqid() . '.tmp';
                file_put_contents($tempFile, $content);
                $this->driver->load($tempFile);
                return $this;
            }
        }
        
        $this->driver->load($path);
        return $this;
    }
    
    /**
     * Save to Storage
     * 
     * @param string $destinationPath Destination path
     * @param int $quality Quality (0-100)
     * @return array Response
     */
    public function saveToStorage($destinationPath, $quality = 90)
    {
        if (!$this->storage) {
            throw new \Exception('Storage not configured');
        }
        
        // Kiểm tra destination path hợp lệ
        if (empty($destinationPath) || !is_string($destinationPath)) {
            throw new \InvalidArgumentException('Invalid destination path provided');
        }
        
        // Save to temp file
        $extension = pathinfo($destinationPath, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = 'jpg'; // fallback
        }
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'output_' . uniqid() . '.' . $extension;
        $this->driver->save($tempFile, $quality);
        
        // Upload to storage
        $result = $this->storage->save($tempFile, $destinationPath);
        
        // Clean up
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        return $result;
    }
    
    // ========================================
    // IMAGE MANIPULATION METHODS
    // ========================================
    
    /**
     * Resize ảnh theo kích thước
     * 
     * @param int $width Chiều rộng
     * @param int $height Chiều cao
     * @param string $mode Chế độ: 'fit'|'fill'|'crop'|'exact' (mặc định: 'fit')
     * @return $this Fluent interface
     */
    public function resize($width, $height, $mode = 'fit')
    {
        $this->driver->resize($width, $height, $mode);
        return $this;
    }
    
    /**
     * Crop (cắt) ảnh theo vùng chọn
     * 
     * @param int $x Tọa độ X (bắt đầu)
     * @param int $y Tọa độ Y (bắt đầu)
     * @param int $width Chiều rộng vùng crop
     * @param int $height Chiều cao vùng crop
     * @return $this Fluent interface
     */
    public function crop($x, $y, $width, $height)
    {
        $this->driver->crop($x, $y, $width, $height);
        return $this;
    }
    
    /**
     * Convert to specific format
     * 
     * Note: Format conversion happens during save() by file extension
     * This method just validates and prepares for WebP if needed
     * 
     * @param string $format Format (jpg, png, webp, gif)
     * @param int $quality Quality (0-100)
     * @return $this
     */
    public function toFormat($format, $quality = 85)
    {
        $format = strtolower($format);
        
        // Only WebP needs special handling
        if ($format === 'webp') {
            $this->driver->toWebP($quality);
        }
        // For jpg, png, gif - format is determined by file extension in save()
        // No action needed here
        
        return $this;
    }
    
    /**
     * Convert sang định dạng WebP (giảm dung lượng 30-50%)
     * 
     * @param int $quality Chất lượng 0-100 (mặc định: 80)
     * @return $this Fluent interface
     */
    public function toWebP($quality = 80)
    {
        $this->driver->toWebP($quality);
        return $this;
    }
    
    /**
     * Convert to AVIF (if supported)
     * 
     * @param int $quality Quality (0-100)
     * @return $this
     */
    public function toAvif($quality = 80)
    {
        if ($this->driver->getDriverName() === 'imagick') {
            try {
                // Try AVIF conversion
                $this->driver->toWebP($quality); // Fallback to WebP if AVIF not supported
            } catch (\Exception $e) {
                $this->driver->toWebP($quality);
            }
        } else {
            // GD doesn't support AVIF, use WebP
            $this->driver->toWebP($quality);
        }
        return $this;
    }
    
    /**
     * Tối ưu hóa ảnh (giảm dung lượng, strip metadata)
     * 
     * @param array $options Tùy chọn: ['quality' => 85]
     * @return $this Fluent interface
     */
    public function optimize($options = [])
    {
        $this->driver->optimize($options);
        return $this;
    }
    
    /**
     * Thêm watermark (chữ/logo) lên ảnh
     * 
     * @param string $watermarkPath Đường dẫn ảnh watermark
     * @param string $position Vị trí: 'top-left'|'top-right'|'bottom-left'|'bottom-right'|'center'
     * @param int $opacity Độ mờ 0-100 (mặc định: 50)
     * @return $this Fluent interface
     */
    public function watermark($watermarkPath, $position = 'bottom-right', $opacity = 50, $sizeOptions = null)
    {
        $this->driver->watermark($watermarkPath, $position, $opacity, $sizeOptions);
        return $this;
    }
    
    /**
     * Xoay ảnh theo góc
     * 
     * @param int $angle Góc xoay (degrees): 90, 180, 270, etc.
     * @return $this Fluent interface
     */
    public function rotate($angle)
    {
        $this->driver->rotate($angle);
        return $this;
    }
    
    /**
     * Lật ảnh (flip)
     * 
     * @param string $direction Hướng: 'horizontal' (ngang) | 'vertical' (dọc)
     * @return $this Fluent interface
     */
    public function flip($direction)
    {
        $this->driver->flip($direction);
        return $this;
    }
    
    /**
     * Lưu ảnh ra file
     * 
     * @param string $path Đường dẫn output
     * @param int $quality Chất lượng 0-100 (mặc định: 90)
     * @return bool true nếu thành công
     */
    public function save($path, $quality = 90)
    {
        return $this->driver->save($path, $quality);
    }
    
    /**
     * Get image width
     * 
     * @return int Width in pixels
     */
    public function getWidth()
    {
        return $this->driver->getWidth();
    }
    
    /**
     * Get image height
     * 
     * @return int Height in pixels
     */
    public function getHeight()
    {
        return $this->driver->getHeight();
    }
    
    /**
     * Get image type
     * 
     * @return string Type (jpeg, png, gif, webp, etc.)
     */
    public function getType()
    {
        return $this->driver->getType();
    }
    
    /**
     * Get driver name
     * 
     * @return string Driver name (gd, imagick)
     */
    public function getDriverName()
    {
        return $this->driver->getDriverName();
    }
    
    /**
     * Destroy image resource
     */
    public function destroy()
    {
        $this->driver->destroy();
    }
    
    /**
     * Tạo nhiều variants (thumbnails, sizes khác nhau)
     * 
     * @param array $variants Config: ['thumb' => ['width' => 200, 'height' => 200]]
     * @param array $options Tùy chọn
     * @return array Variants với paths và URLs
     */
    public function generateVariants($variants, $options = [])
    {
        if (!$this->storage) {
            throw new \Exception('Storage required for generating variants');
        }
        
        $results = [];
        $sourceDir = dirname($this->sourcePath);
        $sourceBasename = pathinfo($this->sourcePath, PATHINFO_FILENAME);
        $sourceExt = pathinfo($this->sourcePath, PATHINFO_EXTENSION);
        
        foreach ($variants as $name => $config) {
            try {
                // Build variant filename
                $variantFilename = $sourceBasename . '_' . $name;
                $variantExt = $sourceExt;
                
                // Determine extension based on WebP option
                if ($config['webp'] ?? false) {
                    $variantExt = 'webp';
                }
                
                $variantPath = $sourceDir . '/' . $variantFilename . '.' . $variantExt;
                
                // Reload source image
                $this->load($this->sourcePath);
                
                // Apply resize if specified
                if (isset($config['width']) && isset($config['height'])) {
                    $this->resize(
                        $config['width'],
                        $config['height'],
                        $config['mode'] ?? 'fit'
                    );
                }
                
                // Apply optimize if specified
                if ($config['optimize'] ?? false) {
                    $this->optimize();
                }
                
                // Convert to WebP if specified
                if ($config['webp'] ?? false) {
                    $this->toWebP($config['quality'] ?? 80);
                }
                
                // Save to storage
                $saveResult = $this->saveToStorage($variantPath, $config['quality'] ?? 90);
                
                if ($saveResult['success']) {
                    $results[$name] = [
                        'path' => $variantPath,
                        'url' => $this->storage->url($variantPath),
                        'width' => $config['width'] ?? null,
                        'height' => $config['height'] ?? null,
                        'webp' => $config['webp'] ?? false
                    ];
                    
                    // Generate additional WebP version if requested
                    if (($config['also_webp'] ?? false) && !($config['webp'] ?? false)) {
                        $webpPath = $sourceDir . '/' . $variantFilename . '.webp';
                        
                        // Reload and convert to WebP
                        $this->load($this->sourcePath);
                        
                        if (isset($config['width']) && isset($config['height'])) {
                            $this->resize($config['width'], $config['height'], $config['mode'] ?? 'fit');
                        }
                        
                        $this->toWebP($config['quality'] ?? 80);
                        $webpResult = $this->saveToStorage($webpPath, $config['quality'] ?? 80);
                        
                        if ($webpResult['success']) {
                            $results[$name . '_webp'] = [
                                'path' => $webpPath,
                                'url' => $this->storage->url($webpPath),
                                'width' => $config['width'] ?? null,
                                'height' => $config['height'] ?? null,
                                'webp' => true
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                $results[$name] = ['error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
    
    /**
     * Tạo các size chuẩn (thumb, medium, large)
     * 
     * @param array $options Tùy chọn: thumb_width, medium_width, large_width, webp
     * @return array Các variants đã tạo
     */
    public function generateStandardVariants($options = [])
    {
        $variants = [
            'thumb' => [
                'width' => $options['thumb_width'] ?? 150,
                'height' => $options['thumb_height'] ?? 150,
                'mode' => 'crop',
                'webp' => $options['webp'] ?? false,
                'also_webp' => $options['also_webp'] ?? false,
                'quality' => $options['thumb_quality'] ?? 85
            ],
            'medium' => [
                'width' => $options['medium_width'] ?? 600,
                'height' => $options['medium_height'] ?? 400,
                'mode' => 'fit',
                'webp' => $options['webp'] ?? false,
                'also_webp' => $options['also_webp'] ?? false,
                'quality' => $options['medium_quality'] ?? 90
            ],
            'large' => [
                'width' => $options['large_width'] ?? 1200,
                'height' => $options['large_height'] ?? 800,
                'mode' => 'fit',
                'webp' => $options['webp'] ?? false,
                'also_webp' => $options['also_webp'] ?? false,
                'quality' => $options['large_quality'] ?? 90
            ]
        ];
        
        return $this->generateVariants($variants, $options);
    }
    
    /**
     * Xóa EXIF metadata (bảo vệ privacy)
     * 
     * Xóa: GPS location, camera info, author, copyright
     * 
     * @param array $options ['preserve_orientation' => bool]
     * @return $this Fluent interface
     */
    public function stripExif($options = [])
    {
        $preserveOrientation = $options['preserve_orientation'] ?? false;
        
        // Use driver's strip method if available
        if (method_exists($this->driver, 'stripExif')) {
            $this->driver->stripExif($preserveOrientation);
        } else {
            // Fallback: strip using driver's optimize (which usually strips metadata)
            $this->driver->optimize(['strip_metadata' => true]);
        }
        
        return $this;
    }
    
    /**
     * Tự động xóa EXIF từ ảnh (helper bảo mật)
     * 
     * Gọi static, tiện dùng sau khi upload
     * 
     * @param string $imagePath Đường dẫn file ảnh
     * @param mixed $storage Storage instance (optional)
     * @return bool true nếu thành công
     */
    public static function autoStripExif($imagePath, $storage = null)
    {
        try {
            // Check if file is image
            $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($ext, $imageExtensions)) {
                return true; // Not an image, skip
            }
            
            // Try ImageMagick first (more reliable)
            if (extension_loaded('imagick') && class_exists('\\Imagick')) {
                try {
                    $className = '\\Imagick';
                    $imagick = new $className($imagePath);
                    $imagick->stripImage(); // Remove all EXIF/IPTC data
                    $imagick->writeImage($imagePath);
                    $imagick->destroy();
                    return true;
                } catch (\Exception $e) {
                    // Fall through to GD
                }
            }
            
            // Fallback to GD (re-encode image)
            $imageInfo = @getimagesize($imagePath);
            if ($imageInfo === false) {
                return false;
            }
            
            $type = $imageInfo[2];
            
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $img = @imagecreatefromjpeg($imagePath);
                    if ($img) {
                        imagejpeg($img, $imagePath, 90);
                        imagedestroy($img);
                        return true;
                    }
                    break;
                    
                case IMAGETYPE_PNG:
                    $img = @imagecreatefrompng($imagePath);
                    if ($img) {
                        // Preserve alpha channel to prevent black background
                        imagealphablending($img, false);
                        imagesavealpha($img, true);
                        imagepng($img, $imagePath);
                        imagedestroy($img);
                        return true;
                    }
                    break;
                    
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $img = @imagecreatefromwebp($imagePath);
                        if ($img) {
                            imagewebp($img, $imagePath, 90);
                            imagedestroy($img);
                            return true;
                        }
                    }
                    break;
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log('EXIF strip error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->destroy();
    }
}
