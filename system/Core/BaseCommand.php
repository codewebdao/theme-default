<?php

namespace System\Core;

/**
 * Base Command Class
 * Provides common functionality for all commands
 */
abstract class BaseCommand
{
    protected $name;
    protected $description;
    protected $arguments = [];
    protected $options = [];

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize command - override in child classes
     */
    protected function initialize()
    {
        // Override in child classes
    }

    /**
     * Execute the command
     */
    abstract public function execute(array $arguments = [], array $options = []);

    /**
     * Get command name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get command arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get command options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Output message to console
     */
    protected function output(string $message, string $type = 'info'): void
    {
        // $prefix = match ($type) {
        //     'error' => ' ',
        //     'success' => ' ',
        //     'warning' => ' ',
        //     'info' => 'ℹ️ ',
        //     default => ''
        // };

        echo $message . "\n";
    }

    /**
     * Output progress bar
     */
    protected function progressBar(int $current, int $total, string $message = ''): void
    {
        $percentage = $total > 0 ? round(($current / $total) * 100, 2) : 0;
        $barLength = 30;
        $filledLength = $barLength * $current / $total;
        $bar = str_repeat('█', $filledLength) . str_repeat('░', $barLength - $filledLength);

        echo "\r" . $message . " [$bar] $percentage% ($current/$total)";

        if ($current >= $total) {
            echo "\n";
        }
    }

    /**
     * Confirm action with user
     */
    protected function confirm(string $message): bool
    {
        echo $message . " (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        return strtolower(trim($line)) === 'y';
    }

    /**
     * Get input from user
     */
    protected function ask(string $message, string $default = ''): string
    {
        echo $message . ($default ? " [$default]: " : ": ");
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        $input = trim($line);
        return $input ?: $default;
    }

    /**
     * Log error
     */
    protected function logError(string $message, \Exception $e = null): void
    {
        $errorMessage = $message;
        if ($e) {
            $errorMessage .= " - " . $e->getMessage();
        }

        $this->output($errorMessage, 'error');

        // Log to file if needed
        error_log($errorMessage);
    }

    /**
     * Show command help
     */
    public function showHelp(): void
    {
        echo "Command: {$this->name}\n";
        echo "Description: {$this->description}\n\n";

        if (!empty($this->arguments)) {
            echo "Arguments:\n";
            foreach ($this->arguments as $arg => $desc) {
                echo "  $arg: $desc\n";
            }
            echo "\n";
        }

        if (!empty($this->options)) {
            echo "Options:\n";
            foreach ($this->options as $option => $desc) {
                echo "  $option: $desc\n";
            }
            echo "\n";
        }
    }
}
