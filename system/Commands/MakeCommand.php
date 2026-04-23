<?php

namespace System\Commands;

use System\Commands\CommandGenerator;
use System\Core\BaseCommand;

/**
 * Make Command
 * Creates new commands easily
 */
class MakeCommand extends BaseCommand
{
    protected function initialize(): void
    {
        $this->name = 'make:command';
        $this->description = 'Create a new command class';

        $this->arguments = [
            'name' => 'The name of the command (e.g., user:create)'
        ];

        $this->options = [
            '--description' => 'Description of the command',
            '--category' => 'Category of the command (default: custom)'
        ];
    }

    public function execute(array $arguments = [], array $options = []): void
    {
        $commandName = $arguments[0] ?? null;

        if (!$commandName) {
            $this->output("Command name is required.", 'error');
            $this->output("Usage: php artisan make:command [name]", 'info');
            return;
        }

        $description = $this->getOptionValue('--description', $options, 'A new command');
        $category = $this->getOptionValue('--category', $options, 'custom');

        try {
            $generator = new CommandGenerator();
            $commandFile = $generator->createCommand($commandName, $description, $category);

            $this->output("Command '$commandName' created successfully!", 'success');
            $this->output("File: $commandFile", 'info');
            $this->output("Note: You need to manually register this command in Kernel.php", 'warning');
        } catch (\Exception $e) {
            $this->logError("Error creating command", $e);
        }
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
