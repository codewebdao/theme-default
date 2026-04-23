<?php

namespace System\Commands;

/**
 * Command Generator
 * Generates commands easily (CMSFullForm-style)
 */
class CommandGenerator
{
    private $commandsDir;

    public function __construct()
    {
        $this->commandsDir = PATH_APP . 'Console/Commands';
    }

    /**
     * Create a new command
     */
    public function createCommand(string $commandName, string $description = '', string $category = 'custom'): string
    {
        $className = $this->getClassName($commandName);

        // Create command directly in Commands directory
        $commandFile = $this->commandsDir . '/' . $className . '.php';

        // Create command file
        $this->generateCommandFile($commandName, $description, $className, $commandFile, $category);

        return $commandFile;
    }

    /**
     * Generate command file content
     */
    private function generateCommandFile(string $commandName, string $description, string $className, string $filePath, string $category): void
    {
        $namespace = 'App\\Console\\Commands';

        $content = "<?php\n\n";
        $content .= "namespace $namespace;\n\n";
        $content .= "use System\\Core\\BaseCommand;\n\n";
        $content .= "/**\n";
        $content .= " * $className\n";
        $content .= " * $description\n";
        $content .= " */\n";
        $content .= "class $className extends BaseCommand\n";
        $content .= "{\n";
        $content .= "    protected function initialize(): void\n";
        $content .= "    {\n";
        $content .= "        \$this->name = '$commandName';\n";
        $content .= "        \$this->description = '$description';\n";
        $content .= "        \n";
        $content .= "        \$this->arguments = [\n";
        $content .= "            // Add your arguments here\n";
        $content .= "        ];\n";
        $content .= "        \n";
        $content .= "        \$this->options = [\n";
        $content .= "            // Add your options here\n";
        $content .= "        ];\n";
        $content .= "    }\n\n";
        $content .= "    public function execute(array \$arguments = [], array \$options = []): void\n";
        $content .= "    {\n";
        $content .= "        \$this->output('Executing $commandName...', 'info');\n";
        $content .= "        \n";
        $content .= "        // Add your command logic here\n";
        $content .= "        \n";
        $content .= "        \$this->output('Command completed!', 'success');\n";
        $content .= "    }\n";
        $content .= "}\n";

        file_put_contents($filePath, $content);
    }

    /**
     * Get class name from command name
     */
    private function getClassName(string $commandName): string
    {
        $parts = explode(':', $commandName);
        $className = '';

        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }

        return $className . 'Command';
    }
}
