<?php

namespace Commands;

use System\Core\BaseCommand;

// Define path constants if not already defined
if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', realpath(__DIR__ . '/..'));
}

class DeleteCommand extends BaseCommand
{
    protected $name = 'delete:command';
    protected $description = 'Delete a command class';

    public function execute(array $arguments = [], array $options = []): void
    {
        if (empty($arguments)) {
            $this->output("❌ Command name is required");
            $this->output("Usage: php cmd delete:command <name>");
            return;
        }

        $commandName = $arguments[0];
        $className = ucfirst($commandName) . 'Command';
        $fileName = $className . '.php';
        $filePath = PATH_ROOT . '/commands/' . $fileName;

        // Check if command exists
        if (!file_exists($filePath)) {
            $this->output("❌ Command '{$className}' not found");
            $this->output("📁 Expected location: {$filePath}");
            return;
        }

        // Show command info before deletion
        $this->output("🔍 Command found:");
        $this->output("   Name: {$className}");
        $this->output("   File: {$fileName}");
        $this->output("   Path: {$filePath}");

        // Check if command is registered in Commands.php config
        $commandsConfig = include PATH_ROOT . '/application/Config/Commands.php';
        $isRegistered = isset($commandsConfig['commands'][$commandName]);

        if ($isRegistered) {
            $this->output("⚠️  Warning: This command is registered in Commands.php config");
            $this->output("   You should remove it from Commands.php after deletion");
        }

        // Check for force option
        $forceDelete = false;
        foreach ($options as $option => $value) {
            if ($option === 'force' || $option === 'f') {
                $forceDelete = true;
                break;
            }
        }

        // Confirm deletion
        if ($forceDelete) {
            $this->deleteCommand($filePath, $className);
        } else {
            $this->output("\n❓ Are you sure you want to delete this command?");
            $this->output("   Use --force or -f to skip confirmation");
            $this->output("   Or run: php cmd delete:command {$commandName} --force");
            $this->output("\n💡 Command not deleted. Use --force to confirm deletion.");
        }
    }

    private function deleteCommand(string $filePath, string $className): void
    {
        try {
            if (unlink($filePath)) {
                $this->output("✅ Command '{$className}' deleted successfully");
                $this->output("📁 File removed: {$filePath}");

                $this->output("\n💡 Next steps:");
                $this->output("   1. Remove the command from Commands.php config if it's registered");
                $this->output("   2. Update any documentation that references this command");
                $this->output("   3. Test your application to ensure no dependencies");
            } else {
                $this->output("❌ Failed to delete command file");
                $this->output("   Check file permissions or if file is in use");
            }
        } catch (\Exception $e) {
            $this->output("❌ Error deleting command: " . $e->getMessage());
        }
    }

    public function showHelp(): void
    {
        $this->output("📖 Delete Command Command");
        $this->output("========================");
        $this->output("");
        $this->output("Description: Delete a command class");
        $this->output("Usage: php cmd delete:command <name> [options]");
        $this->output("");
        $this->output("Options:");
        $this->output("  --force (-f)    Skip confirmation prompt");
        $this->output("");
        $this->output("Examples:");
        $this->output("  php cmd delete:command user           - Delete UserCommand");
        $this->output("  php cmd delete:command product --force - Delete ProductCommand (no confirmation)");
        $this->output("");
        $this->output("This command will:");
        $this->output("  - Check if command file exists");
        $this->output("  - Show command information");
        $this->output("  - Check if command is registered in Commands.php config");
        $this->output("  - Ask for confirmation (unless --force is used)");
        $this->output("  - Delete the command file");
        $this->output("");
        $this->output("⚠️  Warning:");
        $this->output("  - This action cannot be undone");
        $this->output("  - Make sure to remove the command from Commands.php config");
        $this->output("  - Check for any dependencies before deletion");
    }
}
