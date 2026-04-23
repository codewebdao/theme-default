<?php

namespace App\Events\Backend;

use System\Libraries\Events;

/**
 * Backup Restore Event
 * Xử lý sự kiện khôi phục backup
 */
class BackupRestoreEvent
{
    /**
     * Xử lý sự kiện khi ứng dụng shutdown
     * Chạy restore processor trong background
     */
    public static function onShutdown()
    {
        // Kiểm tra xem có restore process nào đang chờ không
        $restoreQueue = PATH_WRITE . 'restore_queue.json';
        
        if (file_exists($restoreQueue)) {
            $queue = json_decode(file_get_contents($restoreQueue), true);
            
            if (!empty($queue) && is_array($queue)) {
                foreach ($queue as $backupId) {
                    self::processRestoreInBackground($backupId);
                }
                
                // Xóa queue sau khi xử lý
                unlink($restoreQueue);
            }
        }
    }

    /**
     * Thêm backup vào queue để restore
     * 
     * @param string $backupId ID của backup
     */
    public static function addToQueue($backupId)
    {
        $restoreQueue = PATH_WRITE . 'restore_queue.json';
        $queue = [];
        
        if (file_exists($restoreQueue)) {
            $queue = json_decode(file_get_contents($restoreQueue), true) ?: [];
        }
        
        if (!in_array($backupId, $queue)) {
            $queue[] = $backupId;
            file_put_contents($restoreQueue, json_encode($queue));
        }
    }

    /**
     * Chạy restore processor trong background
     * 
     * @param string $backupId ID của backup
     */
    private static function processRestoreInBackground($backupId)
    {
        try {
            $processorClass = "\\App\\Controllers\\Backend\\Backups\\BackupRestoreProcessor";
            
            if (class_exists($processorClass)) {
                $processorClass::runInBackground($backupId);
            }
        } catch (\Exception $e) {
            error_log("Failed to start background restore for backup {$backupId}: " . $e->getMessage());
        }
    }
}

// Đăng ký shutdown handler
register_shutdown_function([BackupRestoreEvent::class, 'onShutdown']);
