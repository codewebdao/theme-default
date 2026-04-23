<?php

namespace System\Libraries;

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * Logger - Static Logger for Application (Standalone - No Dependencies)
 * 
 * Features:
 * - Multiple log levels (emergency, alert, critical, error, warning, notice, info, debug)
 * - Multiple log files (logger.log, error.log, slow.log)
 * - DebugBar integration
 * - JSON context support
 * 
 * Usage:
 * Logger::info("User logged in", __FILE__, __LINE__);
 * Logger::error("Database error: " . $e->getMessage());
 * Logger::debug("Debug data: " . json_encode($data));
 * 
 * @package System\Libraries
 */
class Logger
{
    /**
     * Log levels (severity)
     */
    const EMERGENCY = 'EMERGENCY';  // 7 - System unusable
    const ALERT = 'ALERT';          // 6 - Immediate action required
    const CRITICAL = 'CRITICAL';    // 5 - Critical conditions
    const ERROR = 'ERROR';          // 4 - Error conditions
    const WARNING = 'WARNING';      // 3 - Warning conditions
    const NOTICE = 'NOTICE';        // 2 - Normal but significant
    const INFO = 'INFO';            // 1 - Informational
    const DEBUG = 'DEBUG';          // 0 - Debug messages

    /**
     * Array to store logs for debugbar
     */
    private static $logs = [];

    /**
     * Emergency log
     */
    public static function emergency($message, $file = null, $line = null, $context = [])
    {
        self::log(self::EMERGENCY, $message, $file, $line, $context);
    }

    /**
     * Alert log
     */
    public static function alert($message, $file = null, $line = null, $context = [])
    {
        self::log(self::ALERT, $message, $file, $line, $context);
    }

    /**
     * Critical log
     */
    public static function critical($message, $file = null, $line = null, $context = [])
    {
        self::log(self::CRITICAL, $message, $file, $line, $context);
    }

    /**
     * Error log
     */
    public static function error($message, $file = null, $line = null, $context = [])
    {
        self::log(self::ERROR, $message, $file, $line, $context);
    }

    /**
     * Warning log
     */
    public static function warning($message, $file = null, $line = null, $context = [])
    {
        self::log(self::WARNING, $message, $file, $line, $context);
    }

    /**
     * Notice log
     */
    public static function notice($message, $file = null, $line = null, $context = [])
    {
        self::log(self::NOTICE, $message, $file, $line, $context);
    }

    /**
     * Info log
     */
    public static function info($message, $file = null, $line = null, $context = [])
    {
        self::log(self::INFO, $message, $file, $line, $context);
    }

    /**
     * Debug log
     */
    public static function debug($message, $file = null, $line = null, $context = [])
    {
        self::log(self::DEBUG, $message, $file, $line, $context);
    }

    /**
     * Main logging function
     * 
     * Routes to appropriate file based on level
     */
    protected static function log($level, $message, $file = null, $line = null, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        
        // Build log message
        $logMessage = "[{$timestamp}] {$level}: {$message}";

        if ($file && $line) {
            $logMessage .= " in {$file} on line {$line}";
        }
        
        if (!empty($context)) {
            $logMessage .= " " . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $logMessage .= PHP_EOL;

        // Route to appropriate file
        self::writeToFile($level, $logMessage);

        // Track log for debugbar if enabled
        if (APP_DEBUGBAR) {
            self::trackLog($level, $message, $file, $line, $timestamp, $context);
        }
    }

    /**
     * Write log to appropriate file based on level
     */
    private static function writeToFile($level, $logMessage)
    {
        $severity = self::getSeverity($level);
        
        // Route based on severity
        if ($severity >= self::getSeverity(self::ERROR)) {
            // ERROR and above → error.log
            $logFile = PATH_WRITE . 'logs/error.log';
        } else {
            // WARNING, NOTICE, INFO, DEBUG → logger.log
            $logFile = PATH_WRITE . 'logs/logger.log';
        }

        // Write log to file (creates directory if needed)
        self::writeToFileInternal($logFile, $logMessage);
    }

    /**
     * Get severity level (0-7)
     * Maps log level constants to numeric severity
     */
    private static function getSeverity($level)
    {
        static $map = [
            self::EMERGENCY => 7,
            self::ALERT     => 6,
            self::CRITICAL  => 5,
            self::ERROR     => 4,
            self::WARNING   => 3,
            self::NOTICE    => 2,
            self::INFO      => 1,
            self::DEBUG     => 0,
        ];
        return $map[$level] ?? 1;
    }

    /**
     * Track log for debugbar
     */
    private static function trackLog($level, $message, $file = null, $line = null, $timestamp = null, $context = [])
    {
        if (!$timestamp) {
            $timestamp = date('Y-m-d H:i:s');
        }

        // Check if message is JSON
        $isJson = false;
        $jsonData = null;
        if (is_string($message)) {
            $decoded = json_decode($message, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $isJson = true;
                $jsonData = $decoded;
            }
        }

        self::$logs[] = [
            'level' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => $timestamp,
            'context' => $context,
            'is_json' => $isJson,
            'json_data' => $jsonData
        ];
    }

    /**
     * Get logs for debugbar
     */
    public static function getLogs()
    {
        return self::$logs;
    }

    /**
     * Clear logs
     */
    public static function clearLogs()
    {
        self::$logs = [];
    }

    /**
     * Get log file path by type
     * 
     * @param string $type 'info', 'error', or 'slow'
     * @return string Log file path
     */
    public static function getLogFile($type = 'info')
    {
        switch ($type) {
            case 'error':
                return PATH_WRITE . 'logs/error.log';
            case 'slow':
                return PATH_WRITE . 'logs/slow.log';
            case 'info':
            default:
                return PATH_WRITE . 'logs/logger.log';
        }
    }

    /**
     * Read recent logs
     * 
     * @param string $type Log type
     * @param int $lines Number of lines to read
     * @return array Log lines
     */
    public static function readLogs($type = 'info', $lines = 100)
    {
        $file = self::getLogFile($type);
        
        if (!file_exists($file)) {
            return [];
        }

        $content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($content, -$lines);
    }

    /**
     * Clear log file
     * 
     * @param string $type Log type
     * @return bool Success
     */
    public static function clearLogFile($type = 'info')
    {
        $file = self::getLogFile($type);
        
        if (file_exists($file)) {
            return @unlink($file);
        }
        
        return true;
    }

    /**
     * Internal method to write log to file (shared logic)
     * 
     * @param string $file File path
     * @param string $content Content to write
     * @return void
     */
    private static function writeToFileInternal($file, $content)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
    }
}
