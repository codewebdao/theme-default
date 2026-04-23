<?php

namespace System\Commands;

class BlockCommand
{
    /**
     * Create a new model file
     */
    public function create($blockName)
    {
        // Define the path for the new model
        $blockName = ucfirst($blockName);
        $blockPath = PATH_APP . 'Blocks/' . ucfirst($blockName) . '/' . ucfirst($blockName) . 'Block.php';
        $blockViewsPath = PATH_APP . 'Blocks/' . ucfirst($blockName) . '/Views/default.php';

        // Check if the model already exists
        if (file_exists($blockPath)) {
            echo "Block {$blockName} already exists.\n";
            return;
        }

        // Define the contents of the block controller
        $blockContent = <<<PHP
<?php

namespace App\Blocks\\{$blockName};

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
}

PHP;

        // Create the block control file
        $viewsDir11 = dirname($blockPath);
        if (!is_dir($viewsDir11) && !mkdir($viewsDir11, 0777, true) && !is_dir($viewsDir11)) {
            echo "Cannot create Views directory.\n";
            return;
        }
        file_put_contents($blockPath, $blockContent);

        // create block default view
        $viewsDir = dirname($blockViewsPath);
        if (!is_dir($viewsDir) && !mkdir($viewsDir, 0777, true) && !is_dir($viewsDir)) {
            echo "Cannot create Views directory.\n";
            return;
        }
        file_put_contents($blockViewsPath, '');


        echo "Block {$blockName}Block has been created successfully.\n";
    }
}