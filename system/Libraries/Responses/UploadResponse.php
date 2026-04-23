<?php

namespace System\Libraries\Responses;

use System\Libraries\Response;

/**
 * UploadResponse - Upload-specific Response Handler
 * 
 * Xử lý các response đặc thù cho upload operations
 * Format data theo chuẩn upload system
 * 
 * @package System\Libraries\Responses
 * @version 2.0.0
 */
class UploadResponse extends Response
{
    /**
     * Trả về response khi upload thành công
     * 
     * @param array $fileInfo Thông tin file đã upload
     * @param string $message Thông báo
     * @return array
     */
    public static function uploaded($fileInfo, $message = 'File uploaded successfully')
    {
        return self::success($fileInfo, $message);
    }
    
    /**
     * Trả về response khi upload nhiều files thành công
     * 
     * @param array $filesInfo Danh sách files đã upload
     * @param string $message Thông báo
     * @return array
     */
    public static function multipleUploaded($filesInfo, $message = 'Files uploaded successfully')
    {
        $successCount = count(array_filter($filesInfo, function($file) {
            return isset($file['success']) ? $file['success'] : true;
        }));
        
        $totalCount = count($filesInfo);
        
        return self::success([
            'files' => $filesInfo,
            'summary' => [
                'total' => $totalCount,
                'success' => $successCount,
                'failed' => $totalCount - $successCount
            ]
        ], $message);
    }
    
    /**
     * Trả về response cho chunk upload progress
     * 
     * @param array $progressInfo Thông tin tiến trình chunk
     * @return array
     */
    public static function chunkProgress($progressInfo)
    {
        $uploadedChunks = $progressInfo['uploaded_chunks'] ?? 0;
        $totalChunks = $progressInfo['total_chunks'] ?? 0;
        $isComplete = $progressInfo['is_complete'] ?? false;
        
        $percentage = $totalChunks > 0 ? round(($uploadedChunks / $totalChunks) * 100, 2) : 0;
        
        $data = [
            'uploaded_chunks' => $uploadedChunks,
            'total_chunks' => $totalChunks,
            'percentage' => $percentage,
            'is_complete' => $isComplete
        ];
        
        // Nếu upload hoàn thành, thêm thông tin file
        if ($isComplete && isset($progressInfo['file_info'])) {
            $data['file'] = $progressInfo['file_info'];
        }
        
        // Thêm missing chunks nếu có
        if (isset($progressInfo['missing_chunks'])) {
            $data['missing_chunks'] = $progressInfo['missing_chunks'];
        }
        
        $message = $isComplete ? 'Upload completed' : 'Upload in progress';
        
        return self::success($data, $message);
    }
    
    /**
     * Trả về response khi chunk upload thất bại
     * 
     * @param string $error Thông báo lỗi
     * @param array $chunkInfo Thông tin chunk bị lỗi
     * @return array
     */
    public static function chunkError($error, $chunkInfo = [])
    {
        return self::error($error, $chunkInfo);
    }
    
    /**
     * Trả về response khi resume upload
     * 
     * @param array $resumeInfo Thông tin resume
     * @return array
     */
    public static function resumeInfo($resumeInfo)
    {
        return self::success($resumeInfo, 'Resume information retrieved');
    }
    
    /**
     * Trả về response khi validation thất bại
     * 
     * @param string $error Lỗi validation
     * @param string $field Field bị lỗi
     * @return array
     */
    public static function validationFailed($error, $field = null)
    {
        $errors = $field ? [$field => $error] : ['file' => $error];
        return self::validationError($errors, 'Upload validation failed');
    }
    
    /**
     * Trả về response khi file quá lớn
     * 
     * @param int $fileSize Kích thước file (bytes)
     * @param int $maxSize Kích thước tối đa (bytes)
     * @return array
     */
    public static function fileTooLarge($fileSize, $maxSize)
    {
        $fileSizeMB = round($fileSize / 1048576, 2);
        $maxSizeMB = round($maxSize / 1048576, 2);
        
        return self::error(
            "File quá lớn ({$fileSizeMB}MB). Kích thước tối đa: {$maxSizeMB}MB",
            [
                'file_size' => $fileSize,
                'max_size' => $maxSize,
                'file_size_mb' => $fileSizeMB,
                'max_size_mb' => $maxSizeMB
            ]
        );
    }
    
    /**
     * Trả về response khi extension không được phép
     * 
     * @param string $extension Extension của file
     * @param array $allowedExtensions Danh sách extension được phép
     * @return array
     */
    public static function invalidExtension($extension, $allowedExtensions = [])
    {
        $allowed = !empty($allowedExtensions) ? implode(', ', $allowedExtensions) : 'N/A';
        
        return self::error(
            "Extension '{$extension}' không được phép",
            [
                'extension' => $extension,
                'allowed_extensions' => $allowedExtensions,
                'allowed_string' => $allowed
            ]
        );
    }
    
    /**
     * Trả về response khi MIME type không hợp lệ
     * 
     * @param string $mimeType MIME type detected
     * @param string $extension Extension của file
     * @return array
     */
    public static function invalidMimeType($mimeType, $extension)
    {
        return self::error(
            "MIME type '{$mimeType}' không khớp với extension '{$extension}'",
            [
                'mime_type' => $mimeType,
                'extension' => $extension
            ]
        );
    }
    
    /**
     * Trả về response khi file đã tồn tại
     * 
     * @param string $filename Tên file
     * @param string $path Đường dẫn file
     * @return array
     */
    public static function fileExists($filename, $path)
    {
        return self::error(
            "File '{$filename}' đã tồn tại",
            [
                'filename' => $filename,
                'path' => $path
            ]
        );
    }
    
    /**
     * Trả về response khi không thể tạo thư mục
     * 
     * @param string $directory Đường dẫn thư mục
     * @return array
     */
    public static function directoryCreationFailed($directory)
    {
        return self::error(
            "Không thể tạo thư mục: {$directory}",
            ['directory' => $directory]
        );
    }
    
    /**
     * Trả về response khi không thể di chuyển file
     * 
     * @param string $source File nguồn
     * @param string $destination File đích
     * @return array
     */
    public static function moveFileFailed($source, $destination)
    {
        return self::error(
            'Không thể di chuyển file',
            [
                'source' => $source,
                'destination' => $destination
            ]
        );
    }
    
    /**
     * Trả về response với thông tin file đầy đủ
     * 
     * @param array $fileInfo Thông tin file
     * @return array Formatted file info
     */
    public static function formatFileInfo($fileInfo)
    {
        return [
            'id' => $fileInfo['id'] ?? null,
            'name' => $fileInfo['name'] ?? '',
            'path' => $fileInfo['path'] ?? '',
            'url' => $fileInfo['url'] ?? '',
            'size' => $fileInfo['size'] ?? 0,
            'type' => $fileInfo['type'] ?? '',
            'mime_type' => $fileInfo['mime_type'] ?? '',
            'extension' => $fileInfo['extension'] ?? '',
            'is_image' => $fileInfo['is_image'] ?? false,
            'dimensions' => $fileInfo['dimensions'] ?? null,
            'variants' => $fileInfo['variants'] ?? [],
            'created_at' => $fileInfo['created_at'] ?? date('Y-m-d H:i:s'),
            'metadata' => $fileInfo['metadata'] ?? []
        ];
    }
}
