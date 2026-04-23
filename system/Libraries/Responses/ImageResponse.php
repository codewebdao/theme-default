<?php

namespace System\Libraries\Responses;

use System\Libraries\Response;

/**
 * ImageResponse - Image-specific Response Handler
 * 
 * Xử lý các response đặc thù cho image processing operations
 * 
 * @package System\Libraries\Responses
 * @version 2.0.0
 */
class ImageResponse extends Response
{
    /**
     * Trả về response khi xử lý ảnh thành công
     * 
     * @param array $processedInfo Thông tin ảnh đã xử lý
     * @param string $message Thông báo
     * @return array
     */
    public static function processed($processedInfo, $message = 'Image processed successfully')
    {
        return self::success($processedInfo, $message);
    }
    
    /**
     * Trả về response khi resize ảnh thành công
     * 
     * @param array $resizeInfo Thông tin resize
     * @return array
     */
    public static function resized($resizeInfo)
    {
        return self::success($resizeInfo, 'Image resized successfully');
    }
    
    /**
     * Trả về response khi crop ảnh thành công
     * 
     * @param array $cropInfo Thông tin crop
     * @return array
     */
    public static function cropped($cropInfo)
    {
        return self::success($cropInfo, 'Image cropped successfully');
    }
    
    /**
     * Trả về response khi convert format thành công
     * 
     * @param array $convertInfo Thông tin convert
     * @return array
     */
    public static function converted($convertInfo)
    {
        $fromFormat = $convertInfo['from_format'] ?? 'unknown';
        $toFormat = $convertInfo['to_format'] ?? 'unknown';
        
        return self::success(
            $convertInfo,
            "Image converted from {$fromFormat} to {$toFormat}"
        );
    }
    
    /**
     * Trả về response khi optimize ảnh thành công
     * 
     * @param array $optimizeInfo Thông tin optimize
     * @return array
     */
    public static function optimized($optimizeInfo)
    {
        $originalSize = $optimizeInfo['original_size'] ?? 0;
        $optimizedSize = $optimizeInfo['optimized_size'] ?? 0;
        $savedBytes = $originalSize - $optimizedSize;
        $savedPercent = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 2) : 0;
        
        return self::success([
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
            'path' => $optimizeInfo['path'] ?? ''
        ], "Image optimized (saved {$savedPercent}%)");
    }
    
    /**
     * Trả về response khi tạo variants thành công
     * 
     * @param array $variants Danh sách variants đã tạo
     * @return array
     */
    public static function variantsCreated($variants)
    {
        return self::success([
            'variants' => $variants,
            'count' => count($variants)
        ], 'Image variants created successfully');
    }
    
    /**
     * Trả về response khi watermark thành công
     * 
     * @param array $watermarkInfo Thông tin watermark
     * @return array
     */
    public static function watermarked($watermarkInfo)
    {
        return self::success($watermarkInfo, 'Watermark applied successfully');
    }
    
    /**
     * Trả về response khi ảnh không hợp lệ
     * 
     * @param string $reason Lý do không hợp lệ
     * @return array
     */
    public static function invalidImage($reason = 'Invalid image file')
    {
        return self::error($reason, ['type' => 'invalid_image']);
    }
    
    /**
     * Trả về response khi dimensions không đúng
     * 
     * @param int $width Chiều rộng thực tế
     * @param int $height Chiều cao thực tế
     * @param array $requirements Yêu cầu dimensions
     * @return array
     */
    public static function invalidDimensions($width, $height, $requirements = [])
    {
        return self::error(
            'Image dimensions không đúng yêu cầu',
            [
                'actual' => ['width' => $width, 'height' => $height],
                'requirements' => $requirements
            ]
        );
    }
    
    /**
     * Trả về response khi aspect ratio không đúng
     * 
     * @param float $actualRatio Tỷ lệ thực tế
     * @param float $expectedRatio Tỷ lệ mong đợi
     * @return array
     */
    public static function invalidAspectRatio($actualRatio, $expectedRatio)
    {
        return self::error(
            'Aspect ratio không đúng',
            [
                'actual_ratio' => $actualRatio,
                'expected_ratio' => $expectedRatio
            ]
        );
    }
    
    /**
     * Trả về response khi không thể xử lý ảnh
     * 
     * @param string $operation Thao tác đang thực hiện
     * @param string $reason Lý do thất bại
     * @return array
     */
    public static function processingFailed($operation, $reason = '')
    {
        $message = "Không thể {$operation} ảnh";
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        return self::error($message, [
            'operation' => $operation,
            'reason' => $reason
        ]);
    }
    
    /**
     * Trả về response với thông tin ảnh đầy đủ
     * 
     * @param string $imagePath Đường dẫn ảnh
     * @return array
     */
    public static function imageInfo($imagePath)
    {
        if (!file_exists($imagePath)) {
            return self::notFound('Image');
        }
        
        $imageInfo = @getimagesize($imagePath);
        if ($imageInfo === false) {
            return self::invalidImage('Cannot read image information');
        }
        
        list($width, $height, $type) = $imageInfo;
        
        return self::success([
            'path' => $imagePath,
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'mime' => $imageInfo['mime'],
            'size' => filesize($imagePath),
            'aspect_ratio' => $height > 0 ? round($width / $height, 2) : 0
        ], 'Image information retrieved');
    }
}
