<?php

namespace Commands;

use System\Core\BaseCommand;
use System\Libraries\CommandStatusManager;

/**
 * Status Command
 * Check status of running commands
 */
class Status extends BaseCommand
{
    protected function initialize(): void
    {
        $this->name = 'status';
        $this->description = 'Check status of running commands';

        $this->arguments = [
            'command' => 'Specific command to check status (optional)'
        ];

        $this->options = [
            '--clear' => 'Clear old status entries',
            '--all' => 'Show all commands (including completed/failed)'
        ];
    }

    public function execute(array $arguments = [], array $options = []): void
    {
        $statusManager = new CommandStatusManager();
        $commandName = $arguments[0] ?? null;
        $clearOld = in_array('--clear', $options);
        $showAll = in_array('--all', $options);

        if ($clearOld) {
            $statusManager->clearOldStatus();
            $this->output("✅ Old status entries cleared!");
            return;
        }

        if ($commandName) {
            $this->showCommandStatus($statusManager, $commandName);
        } else {
            $this->showAllStatus($statusManager, $showAll);
        }
    }

    /**
     * Show status of specific command
     */
    private function showCommandStatus(CommandStatusManager $statusManager, string $commandName): void
    {
        $status = $statusManager->getCommandStatus($commandName);

        if (!$status) {
            $this->output("❌ No status found for command: $commandName");
            return;
        }

        $this->output("📊 Status for command: $commandName");
        $this->output("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->output("Status: " . $this->getStatusIcon($status['status']) . " " . strtoupper($status['status']));
        $this->output("Started: " . $status['started_at']);
        $this->output("PID: " . $status['pid']);
        $this->output("Arguments: " . implode(' ', $status['arguments']));

        if (isset($status['completed_at'])) {
            $this->output("Completed: " . $status['completed_at']);
        }

        if (isset($status['failed_at'])) {
            $this->output("Failed: " . $status['failed_at']);
            if (isset($status['error'])) {
                $this->output("Error: " . $status['error']);
            }
        }
    }

    /**
     * Show status of all commands
     */
    private function showAllStatus(CommandStatusManager $statusManager, bool $showAll): void
    {
        $runningCommands = $statusManager->getRunningCommands();

        if (empty($runningCommands)) {
            $this->output("✅ No commands are currently running");
            return;
        }

        $this->output("🔄 Currently Running Commands:");
        $this->output("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        foreach ($runningCommands as $command => $status) {
            $this->output("Command: $command");
            $this->output("  Status: " . $this->getStatusIcon($status['status']) . " " . strtoupper($status['status']));
            $this->output("  Started: " . $status['started_at']);
            $this->output("  PID: " . $status['pid']);
            $this->output("  Arguments: " . implode(' ', $status['arguments']));
            $this->output("");
        }

        if ($showAll) {
            $this->output("📋 All Commands Status:");
            $this->output("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            // This would require additional method to get all statuses
            $this->output("Use --all option to see completed/failed commands");
        }
    }

    /**
     * Get status icon
     */
    private function getStatusIcon(string $status): string
    {
        switch ($status) {
            case 'running':
                return '🔄';
            case 'completed':
                return '✅';
            case 'failed':
                return '❌';
            default:
                return '❓';
        }
    }

    /**
     * Show help information
     */
    public function showHelp(): void
    {
        $this->output("Status Command - Check command execution status");
        $this->output("");
        $this->output("Usage:");
        $this->output("  php cmd status                    # Show running commands");
        $this->output("  php cmd status <command>          # Show specific command status");
        $this->output("  php cmd status --all              # Show all commands (including completed)");
        $this->output("  php cmd status --clear            # Clear old status entries");
        $this->output("");
        $this->output("Examples:");
        $this->output("  php cmd status");
        $this->output("  php cmd status gold");
        $this->output("  php cmd status prices --all");
        $this->output("  php cmd status --clear");
    }
}
