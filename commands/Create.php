<?php

namespace Commands;

use Commands\TableCommand;
use System\Core\BaseCommand;

class Create extends BaseCommand
{
    protected $name = 'create';
    protected $description = 'Create various components (command, controller, model, block, table)';

    public function execute(array $arguments = [], array $options = []): void
    {
        if (empty($arguments)) {
            $this->output("❌ Component type is required");
            $this->showHelp();
            return;
        }

        $componentType = $arguments[0];
        $componentName = $arguments[1] ?? null;

        if (!$componentName) {
            $this->output("❌ Component name is required");
            $this->output("Usage: php cmd create:{$componentType} <name>");
            return;
        }

        // Handle different component types
        switch ($componentType) {
            case 'command':
                $this->createCommand($componentName);
                break;
            case 'controller':
                $this->createController($componentName);
                break;
            case 'model':
                $this->createModel($componentName);
                break;
            case 'block':
                $this->createBlock($componentName);
                break;
            case 'table':
                $this->createTable($componentName);
                break;
            default:
                $this->output("❌ Unknown component type: {$componentType}");
                $this->showHelp();
                break;
        }
    }

    /**
     * Fallback to legacy commands if Create.php doesn't exist
     */
    public static function fallbackToLegacyCommand(string $componentType, string $componentName): void
    {
        switch ($componentType) {
            case 'controller':
                if (class_exists('System\Commands\ControllersCommand')) {
                    $command = new \System\Commands\ControllersCommand();
                    $command->create($componentName);
                } else {
                    echo "❌ ControllersCommand not found in system/Commands/\n";
                }
                break;

            case 'model':
                if (class_exists('System\Commands\ModelsCommand')) {
                    $command = new \System\Commands\ModelsCommand();
                    $command->create($componentName);
                } else {
                    echo "❌ ModelsCommand not found in system/Commands/\n";
                }
                break;

            case 'block':
                if (class_exists('System\Commands\BlockCommand')) {
                    $command = new \System\Commands\BlockCommand();
                    $command->create($componentName);
                } else {
                    echo "❌ BlockCommand not found in system/Commands/\n";
                }
                break;

            default:
                echo "❌ No fallback available for component type: {$componentType}\n";
                break;
        }
    }

    /**
     * Create a new command
     */
    private function createCommand(string $commandName): void
    {
        $className = ucfirst($commandName) . 'Command';
        $fileName = $className . '.php';
        $filePath = PATH_ROOT . '/commands/' . $fileName;

        // Check if command already exists
        if (file_exists($filePath)) {
            $this->output("❌ Command '{$className}' already exists");
            return;
        }

        // Create command content
        $content = $this->generateCommandContent($className, $commandName);

        // Write file
        if (file_put_contents($filePath, $content)) {
            $this->output("✅ Command '{$className}' created successfully");
            $this->output("📁 Location: {$filePath}");
            $this->output("💡 Don't forget to register it in Commands.php config");
        } else {
            $this->output("❌ Failed to create command file");
        }
    }

    /**
     * Create a new controller
     */
    private function createController(string $controllerName): void
    {
        // Parse namespace and class name
        $parts = explode('/', $controllerName);
        $namespace = '';
        $className = ucfirst(array_pop($parts)) . 'Controller';

        if (!empty($parts)) {
            $namespace = '\\' . implode('\\', array_map('ucfirst', $parts));
        }

        $fileName = $className . '.php';
        $directory = PATH_APP . 'Controllers/';

        // Create directory structure if needed
        if (!empty($parts)) {
            $directory .= implode('/', array_map('ucfirst', $parts)) . '/';
        }

        $filePath = $directory . $fileName;

        // Check if controller already exists
        if (file_exists($filePath)) {
            $this->output("❌ Controller '{$className}' already exists");
            return;
        }

        // Create directory if not exists
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                $this->output("❌ Cannot create controller directory: {$directory}");
                return;
            }
        }

        // Create controller content
        $content = $this->generateControllerContent($className, $namespace);

        // Write file
        if (file_put_contents($filePath, $content)) {
            $this->output("✅ Controller '{$className}' created successfully");
            $this->output("📁 Location: {$filePath}");
            if ($namespace) {
                $this->output("📦 Namespace: App\\Controllers{$namespace}");
            }
        } else {
            $this->output("❌ Failed to create controller file");
        }
    }

    /**
     * Create a new model
     */
    private function createModel(string $modelName): void
    {
        // Parse namespace and class name
        $parts = explode('/', $modelName);
        $namespace = '';
        $className = ucfirst(array_pop($parts)) . 'Model';

        if (!empty($parts)) {
            $namespace = '\\' . implode('\\', array_map('ucfirst', $parts));
        }

        $fileName = $className . '.php';
        $directory = PATH_APP . 'Models/';

        // Create directory structure if needed
        if (!empty($parts)) {
            $directory .= implode('/', array_map('ucfirst', $parts)) . '/';
        }

        $filePath = $directory . $fileName;

        // Check if model already exists
        if (file_exists($filePath)) {
            $this->output("❌ Model '{$className}' already exists");
            return;
        }

        // Create directory if not exists
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                $this->output("❌ Cannot create model directory: {$directory}");
                return;
            }
        }

        // Create model content
        $content = $this->generateModelContent($className, $modelName, $namespace);

        // Write file
        if (file_put_contents($filePath, $content)) {
            $this->output("✅ Model '{$className}' created successfully");
            $this->output("📁 Location: {$filePath}");
            if ($namespace) {
                $this->output("📦 Namespace: App\\Models{$namespace}");
            }
        } else {
            $this->output("❌ Failed to create model file");
        }
    }


    /**
     * Create/synchronize database table
     */
    private function createTable(string $tableName): void
    {
        try {
            // Load TableCommand
            $tableCommand = new TableCommand();
            $tableCommand->execute([$tableName], []);
        } catch (\Exception $e) {
            $this->output("❌ Error creating/synchronizing table: " . $e->getMessage());
        }
    }

    /**
     * Create a new block
     */
    private function createBlock(string $blockName): void
    {
        // Parse namespace and block name
        $parts = explode('/', $blockName);
        $namespace = '';
        $blockName = ucfirst(array_pop($parts));

        if (!empty($parts)) {
            $namespace = '\\' . implode('\\', array_map('ucfirst', $parts));
        }

        $blockPath = PATH_APP . 'Blocks/';

        // Create directory structure if needed
        if (!empty($parts)) {
            $blockPath .= implode('/', array_map('ucfirst', $parts)) . '/';
        }

        $blockPath .= $blockName . '/' . $blockName . 'Block.php';
        $blockViewsPath = dirname($blockPath) . '/Views/default.php';

        // Check if block already exists
        if (file_exists($blockPath)) {
            $this->output("❌ Block '{$blockName}' already exists");
            return;
        }

        // Create block content
        $content = $this->generateBlockContent($blockName, $namespace);

        // Create block directory
        $blockDir = dirname($blockPath);
        if (!is_dir($blockDir)) {
            if (!mkdir($blockDir, 0777, true)) {
                $this->output("❌ Cannot create block directory: {$blockDir}");
                return;
            }
        }

        // Write block file
        if (file_put_contents($blockPath, $content)) {
            $this->output("✅ Block '{$blockName}Block' created successfully");
            $this->output("📁 Location: {$blockPath}");
            if ($namespace) {
                $this->output("📦 Namespace: App\\Blocks{$namespace}");
            }
        } else {
            $this->output("❌ Failed to create block file");
            return;
        }

        // Create block views directory and default view
        $viewsDir = dirname($blockViewsPath);
        if (!is_dir($viewsDir)) {
            if (!mkdir($viewsDir, 0777, true)) {
                $this->output("❌ Cannot create Views directory: {$viewsDir}");
                return;
            }
        }

        // Create default view file
        if (file_put_contents($blockViewsPath, '')) {
            $this->output("✅ Block view 'default.php' created successfully");
            $this->output("📁 Location: {$blockViewsPath}");
        } else {
            $this->output("❌ Failed to create block view file");
        }
    }

    /**
     * Generate command content
     */
    private function generateCommandContent(string $className, string $commandName): string
    {
        return "<?php

namespace Commands;

use System\Core\BaseCommand;

class {$className} extends BaseCommand
{
    protected \$name = '{$commandName}';
    protected \$description = '{$commandName} command description';

    public function execute(array \$arguments = [], array \$options = []): void
    {
        \$this->output(\"🚀 Executing {$commandName} command\");
        
        // Your command logic here
        \$this->output(\"✅ {$commandName} command completed successfully\");
    }

    public function showHelp(): void
    {
        \$this->output(\"📖 {$commandName} Command\");
        \$this->output(\"==================\");
        \$this->output(\"\");
        \$this->output(\"Description: {$commandName} command description\");
        \$this->output(\"Usage: php cmd {$commandName} [options]\");
        \$this->output(\"\");
        \$this->output(\"Examples:\");
        \$this->output(\"  php cmd {$commandName}              - Run {$commandName} command\");
        \$this->output(\"  php cmd {$commandName} --option     - Run with option\");
        \$this->output(\"\");
        \$this->output(\"Options:\");
        \$this->output(\"  --help (-h)    Show this help message\");
    }
}";
    }

    /**
     * Generate controller content
     */
    private function generateControllerContent(string $className, string $namespace = ''): string
    {
        $fullNamespace = 'App\\Controllers' . $namespace;

        return "<?php
namespace {$fullNamespace};

use System\Core\BaseController;

class {$className} extends BaseController {
    public function __construct()
    {
        parent::__construct();        
        load_helpers(['frontend','query']);
    }
    public function index() {
        echo 'Hello from {$className}!';
    }
}";
    }

    /**
     * Generate model content
     */
    private function generateModelContent(string $className, string $modelName, string $namespace = ''): string
    {
        $fullNamespace = 'App\\Models' . $namespace;

        return "<?php
namespace {$fullNamespace};

use System\Database\BaseModel;

/**
 * {$className}
 * 
 * Auto-generated model class
 */
class {$className} extends BaseModel
{
    /** @var string Unprefixed base table name */
    protected \$table = '{$modelName}';
    
    /** @var string Primary key column name */
    protected \$primaryKey = 'id';
    
    /** @var string[] Fillable fields for mass assignment */
    protected \$fillable = [
        'name',
        // Add more fillable fields here
    ];
    
    /** @var string[] Guarded fields (blacklist) */
    protected \$guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    
    /** @var bool Enable automatic timestamps */
    public \$timestamps = true;

    /**
     * Define the table schema
     * 
     * Schema types: increments, bigincrements, integer, biginteger, tinyinteger,
     *               string, text, mediumtext, longtext, json, decimal, float, double,
     *               date, datetime, timestamp, time, year, enum, set, boolean, blob
     * 
     * Options: nullable, null, default, unsigned, unique, index, comment, length,
     *          precision, scale, values (for enum/set), after
     * 
     * Note: increments automatically creates AUTO_INCREMENT + UNSIGNED
     *
     * 
     * @return array Table schema definition
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['index' => ['type' => 'primary']  ]],
            ['type' => 'integer', 'name' => 'user_id', 'options' => ['null' => true, 'unsigned' => true]],
            ['type' => 'int', 'name' => 'quantity', 'options' => ['null' => false, 'default' => 0, 'unsigned' => true]],
        ];
    }
}";
    }


    /**
     * Generate block content
     */
    private function generateBlockContent(string $blockName, string $namespace = ''): string
    {
        $fullNamespace = 'App\\Blocks' . $namespace . '\\' . $blockName;

        return "<?php

namespace {$fullNamespace};

use System\Core\BaseBlock;

class {$blockName}Block extends BaseBlock
{

    public function __construct()
    {
        \$this->setLabel('{$blockName} Block');
        \$this->setName('{$blockName}');
        \$this->setProps([
            'layout'      => 'default',
        ]);
    }

    // This is the required data processing function
    public function handleData()
    {   \$props = \$this->getProps();
        \$data = \$props;
        return \$data;
    }
}";
    }


    public function showHelp(): void
    {
        $this->output("📖 Create Command");
        $this->output("================");
        $this->output("");
        $this->output("Description: Create various components (command, controller, model, block, table)");
        $this->output("Usage: php cmd create:<type> <name>");
        $this->output("");
        $this->output("Available types:");
        $this->output("  command     - Create a new command class");
        $this->output("  controller  - Create a new controller");
        $this->output("  model       - Create a new model");
        $this->output("  block       - Create a new block component");
        $this->output("  table       - Create/synchronize database table from model");
        $this->output("");
        $this->output("Examples:");
        $this->output("  php cmd create:command user     - Create UserCommand");
        $this->output("  php cmd create:controller User  - Create UserController");
        $this->output("  php cmd create:model Product    - Create ProductModel");
        $this->output("  php cmd create:block hero       - Create HeroBlock");
        $this->output("  php cmd create:table files      - Create/sync files table");
        $this->output("");
        $this->output("The command will create:");
        $this->output("  - Command: Command file in commands/ directory");
        $this->output("  - Controller: Controller file in application/Controllers/");
        $this->output("  - Model: Model file in application/Models/");
        $this->output("  - Block: Block file in application/Blocks/ with Views/");
        $this->output("  - Table: Create/synchronize database table from model schema");
    }
}
