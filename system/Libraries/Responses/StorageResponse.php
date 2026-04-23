<?php

namespace System\Libraries\Responses;

use System\Libraries\Response;

/**
 * StorageResponse - Storage-specific Response Handler
 * 
 * Xử lý các response đặc thù cho storage operations
 * 
 * @package System\Libraries\Responses
 * @version 2.0.0
 */
class StorageResponse extends Response
{
    /**
     * Trả về response khi lưu file thành công
     * 
     * @param array $fileInfo Thông tin file đã lưu
     * @return array
     */
    public static function saved($fileInfo)
    {
        return self::success($fileInfo, 'File saved successfully');
    }
    
    /**
     * Trả về response khi xóa file thành công
     * 
     * @param string $path Đường dẫn file đã xóa
     * @return array
     */
    public static function deleted($path)
    {
        return self::success(['path' => $path], 'File deleted successfully');
    }
    
    /**
     * Trả về response khi xóa nhiều files thành công
     * 
     * @param array $deletedPaths Danh sách đường dẫn đã xóa
     * @return array
     */
    public static function multipleDeleted($deletedPaths)
    {
        return self::success([
            'deleted_files' => $deletedPaths,
            'count' => count($deletedPaths)
        ], count($deletedPaths) . ' files deleted successfully');
    }
    
    /**
     * Trả về response khi move file thành công
     * 
     * @param string $from Đường dẫn cũ
     * @param string $to Đường dẫn mới
     * @return array
     */
    public static function moved($from, $to)
    {
        return self::success([
            'from' => $from,
            'to' => $to
        ], 'File moved successfully');
    }
    
    /**
     * Trả về response khi copy file thành công
     * 
     * @param string $from Đường dẫn nguồn
     * @param string $to Đường dẫn đích
     * @return array
     */
    public static function copied($from, $to)
    {
        return self::success([
            'from' => $from,
            'to' => $to
        ], 'File copied successfully');
    }
    
    /**
     * Trả về response khi file tồn tại
     * 
     * @param string $path Đường dẫn file
     * @return array
     */
    public static function exists($path)
    {
        return self::success([
            'path' => $path,
            'exists' => true
        ], 'File exists');
    }
    
    /**
     * Trả về response khi file không tồn tại
     * 
     * @param string $path Đường dẫn file
     * @return array
     */
    public static function notExists($path)
    {
        return self::error('File not found', [
            'path' => $path,
            'exists' => false
        ]);
    }
    
    /**
     * Trả về response với thông tin file
     * 
     * @param string $path Đường dẫn file
     * @return array
     */
    public static function fileInfo($path)
    {
        if (!file_exists($path)) {
            return self::notExists($path);
        }
        
        return self::success([
            'path' => $path,
            'size' => filesize($path),
            'modified_time' => filemtime($path),
            'is_readable' => is_readable($path),
            'is_writable' => is_writable($path),
            'mime_type' => mime_content_type($path)
        ], 'File information retrieved');
    }
    
    /**
     * Trả về response khi không thể lưu file
     * 
     * @param string $path Đường dẫn file
     * @param string $reason Lý do thất bại
     * @return array
     */
    public static function saveFailed($path, $reason = '')
    {
        $message = 'Failed to save file';
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        return self::error($message, [
            'path' => $path,
            'reason' => $reason
        ]);
    }
    
    /**
     * Trả về response khi không thể xóa file
     * 
     * @param string $path Đường dẫn file
     * @param string $reason Lý do thất bại
     * @return array
     */
    public static function deleteFailed($path, $reason = '')
    {
        $message = 'Failed to delete file';
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        return self::error($message, [
            'path' => $path,
            'reason' => $reason
        ]);
    }
    
    /**
     * Trả về response khi không có quyền truy cập
     * 
     * @param string $path Đường dẫn file
     * @param string $operation Thao tác đang thực hiện
     * @return array
     */
    public static function permissionDenied($path, $operation = 'access')
    {
        return self::forbidden("Permission denied to {$operation} file: {$path}");
    }
    
    /**
     * Trả về response khi disk đầy
     * 
     * @param string $path Đường dẫn
     * @return array
     */
    public static function diskFull($path)
    {
        return self::error('Disk is full', [
            'path' => $path,
            'error_type' => 'disk_full'
        ]);
    }
    
    /**
     * Trả về response với thông tin storage
     * 
     * @param string $path Đường dẫn storage
     * @return array
     */
    public static function storageInfo($path)
    {
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercent = $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 2) : 0;
        
        return self::success([
            'path' => $path,
            'total_space' => $totalSpace,
            'free_space' => $freeSpace,
            'used_space' => $usedSpace,
            'used_percent' => $usedPercent,
            'total_space_gb' => round($totalSpace / 1073741824, 2),
            'free_space_gb' => round($freeSpace / 1073741824, 2),
            'used_space_gb' => round($usedSpace / 1073741824, 2)
        ], 'Storage information retrieved');
    }
}
