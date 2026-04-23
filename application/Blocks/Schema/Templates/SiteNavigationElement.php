<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class SiteNavigationElement extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('SiteNavigationElement');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'menuItems' => []
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add menu item
     */
    public function addMenu($name, $url, $description = '') {
        $this->schemaData['menuItems'][] = [
            '@type' => 'MenuItem',
            'name' => $name,
            'url' => $url,
            'description' => $description
        ];
        return $this;
    }

    /**
     * Add submenu
     */
    public function addSubMenu($name, $items) {
        $this->schemaData['menuItems'][] = [
            '@type' => 'MenuItem',
            'name' => $name,
            'hasMenuSection' => [
                '@type' => 'MenuSection',
                'name' => $name,
                'hasMenuItem' => $items
            ]
        ];
        return $this;
    }
} 