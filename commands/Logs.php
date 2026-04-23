<?php

namespace Commands;

use System\Core\BaseCommand;

/**
 * Logs Command
 * Monitor and manage log files
 */
class Logs extends BaseCommand
{
    protected function initialize(): void
    {
        $this->name = 'logs';
        $this->description = 'Monitor and manage log files';

        $this->arguments = [
            'action' => 'Action to perform (monitor, clean, show)',
            'command' => 'Command name to monitor (for monitor action)'
        ];

        $this->options = [
            '--follow' => 'Follow log file in real-time (for monitor)',
            '--lines' => 'Number of lines to show (default: 50)',
            '--all' => 'Show all log files (for show action)'
        ];
    }

    public function execute(array $arguments = [], array $options = []): void
    {
        $action = $arguments[0] ?? 'show';

        switch ($action) {
            case 'monitor':
                $this->monitorLogs($arguments[1] ?? null, $options);
                break;

            case 'clean':
                $this->cleanLogs($options);
                break;

            case 'show':
                $this->showLogs($options);
                break;

            default:
                $this->output("Unknown action: $action");
                $this->showHelp();
                break;
        }
    }

    /**
     * Monitor log files
     */
    private function monitorLogs(?string $command, array $options): void
    {
        $follow = in_array('--follow', $options);
        $lines = (int) $this->getOptionValue('--lines', $options, 50);

        if (!$command) {
            $this->output("Please specify command name to monitor:");
            $this->output("  php cmd logs monitor gold");
            $this->output("  php cmd logs monitor prices --follow");
            return;
        }

        $logsDir = PATH_WRITE . 'logs';
        $pattern = $logsDir . '/command_' . $command . '_*.log';
        $logFiles = glob($pattern);

        if (empty($logFiles)) {
            $this->output("No log files found for command '$command'");
            return;
        }

        // Get the most recent log file
        $latestLog = max($logFiles);
        $this->output("Monitoring: $latestLog");
        $this->output("Lines: $lines");
        $this->output("Follow: " . ($follow ? 'Yes' : 'No'));
        $this->output("");

        if ($follow) {
            $this->followLog($latestLog, $lines);
        } else {
            $this->showLogContent($latestLog, $lines);
        }
    }

    /**
     * Show log content
     */
    private function showLogContent(string $logFile, int $lines): void
    {
        if (!file_exists($logFile)) {
            $this->output("Log file not found: $logFile");
            return;
        }

        $content = file_get_contents($logFile);
        $logLines = explode("\n", $content);
        $totalLines = count($logLines);

        $this->output("Total lines: $totalLines");
        $this->output("Showing last $lines lines:");
        $this->output("");

        $startLine = max(0, $totalLines - $lines);
        for ($i = $startLine; $i < $totalLines; $i++) {
            echo $logLines[$i] . "\n";
        }
    }

    /**
     * Follow log file in real-time
     */
    private function followLog(string $logFile, int $lines): void
    {
        if (!file_exists($logFile)) {
            $this->output("Log file not found: $logFile");
            return;
        }

        $this->output("Following log file... Press Ctrl+C to stop");
        $this->output("");

        // Show last few lines first
        $this->showLogContent($logFile, $lines);

        $lastSize = filesize($logFile);
        $handle = fopen($logFile, 'r');

        if (!$handle) {
            $this->output("Cannot open log file for reading");
            return;
        }

        // Seek to end of file
        fseek($handle, 0, SEEK_END);

        while (true) {
            $currentSize = filesize($logFile);

            if ($currentSize > $lastSize) {
                // New content available
                fseek($handle, $lastSize);
                $newContent = fread($handle, $currentSize - $lastSize);
                echo $newContent;
                $lastSize = $currentSize;
            }

            sleep(1); // Check every second
        }

        fclose($handle);
    }

    /**
     * Clean log files
     */
    private function cleanLogs(array $options): void
    {
        $this->output("🧹 Cleaning log files...");

        $logsDir = PATH_WRITE . 'logs';
        $commandLogs = glob($logsDir . '/command_*.log');
        $debugLogs = glob($logsDir . '/debug_*.log');

        $totalFiles = count($commandLogs) + count($debugLogs);

        if ($totalFiles === 0) {
            $this->output("✅ No log files to clean up.");
            return;
        }

        $deletedCount = 0;
        $errorCount = 0;

        // Delete command logs
        foreach ($commandLogs as $file) {
            if (unlink($file)) {
                $deletedCount++;
            } else {
                $errorCount++;
            }
        }

        // Delete debug logs
        foreach ($debugLogs as $file) {
            if (unlink($file)) {
                $deletedCount++;
            } else {
                $errorCount++;
            }
        }

        $this->output("📊 Cleanup results:");
        $this->output("✅ Deleted: $deletedCount files");

        if ($errorCount > 0) {
            $this->output("❌ Failed: $errorCount files");
        }

        $this->output("🎉 Log cleanup completed!");
    }

    /**
     * Show available log files
     */
    private function showLogs(array $options): void
    {
        $showAll = in_array('--all', $options);
        $logsDir = PATH_WRITE . 'logs';

        $this->output("📁 Available log files:");
        $this->output("");

        // Show command logs
        $commandLogs = glob($logsDir . '/command_*.log');
        if (!empty($commandLogs)) {
            $this->output("Command Logs:");
            foreach ($commandLogs as $log) {
                $fileName = basename($log);
                $fileSize = filesize($log);
                $fileTime = date('Y-m-d H:i:s', filemtime($log));
                $this->output("  $fileName ($fileSize bytes, $fileTime)", 'comment');
            }
            $this->output("");
        }

        // Show other logs if --all is specified
        if ($showAll) {
            $otherLogs = glob($logsDir . '/*.log');
            $otherLogs = array_filter($otherLogs, function ($log) {
                return strpos(basename($log), 'command_') !== 0;
            });

            if (!empty($otherLogs)) {
                $this->output("Other Logs:");
                foreach ($otherLogs as $log) {
                    $fileName = basename($log);
                    $fileSize = filesize($log);
                    $fileTime = date('Y-m-d H:i:s', filemtime($log));
                    $this->output("  $fileName ($fileSize bytes, $fileTime)", 'comment');
                }
            }
        }

        $this->output("");
        $this->output("Usage:");
        $this->output("  php cmd logs clean");
        $this->output("  php cmd logs show --all");
    }

    /**
     * Get option value from command line options
     */
    private function getOptionValue(string $option, array $options, $default = null)
    {
        foreach ($options as $opt) {
            if (strpos($opt, $option . '=') === 0) {
                return substr($opt, strlen($option) + 1);
            }
        }
        return $default;
    }
}
