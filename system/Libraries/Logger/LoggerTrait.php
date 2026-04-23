<?php

namespace System\Libraries\Logger;

/**
 * LoggerTrait - Shared functionality for loggers
 * 
 * Provides common methods for severity mapping and file writing
 * to reduce code duplication between PsrLogger and Logger classes.
 * 
 * @package System\Libraries\Logger
 */
trait LoggerTrait
{
    /**
     * Get severity level (0-7)
     * 
     * @param string $level Log level
     * @return int Severity (0-7)
     */
    protected function getSeverity($level)
    {
        static $map = [
            LogLevel::EMERGENCY => 7,
            LogLevel::ALERT     => 6,
            LogLevel::CRITICAL  => 5,
            LogLevel::ERROR     => 4,
            LogLevel::WARNING   => 3,
            LogLevel::NOTICE    => 2,
            LogLevel::INFO      => 1,
            LogLevel::DEBUG     => 0,
        ];
        return $map[$level] ?? 1;
    }

    /**
     * Write content to file (creates directory if needed)
     * 
     * @param string $file File path
     * @param string $content Content to write
     * @param bool $lock Use file locking (default: false)
     * @return void
     */
    protected function appendToFile($file, $content, $lock = false)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        $flags = FILE_APPEND;
        if ($lock) {
            $flags |= LOCK_EX;
        }
        
        @file_put_contents($file, $content, $flags);
    }
}
