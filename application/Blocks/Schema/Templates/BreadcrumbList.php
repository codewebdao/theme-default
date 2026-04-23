<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class BreadcrumbList extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('BreadcrumbList');
        
        $currentUrl = $data['url'] ?? base_url();
        $items = $data['items'] ?? [];
        
        // Format items
        $formattedItems = [];
        foreach ($items as $index => $item) {
            $formattedItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null
            ];
        }

        // Set default data theo chuẩn RankMath
        $defaultData = [
            '@id' => $currentUrl . '#breadcrumb',
            'itemListElement' => $formattedItems
        ];

        $this->setSchemaData(array_merge($defaultData, $data));
    }
    
    public function addItem($name, $url, $position = null) {
        if ($position === null) {
            $position = count($this->schemaData['itemListElement']) + 1;
        }
        
        $this->schemaData['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $url
        ];
        
        return $this;
    }
    
    /**
     * Create breadcrumb for homepage
     */
    public static function forHomepage($data = []) {
        $items = [
            [
                'name' => $data['siteName'] ?? option('site_title', APP_LANG)
            ]
        ];
        
        return new self([
            'url' => $data['url'] ?? base_url(),
            'items' => $items
        ]);
    }
} 