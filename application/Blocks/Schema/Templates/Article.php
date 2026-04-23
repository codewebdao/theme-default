<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Article extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Article');
        
        // Set default data
        $defaultData = [
            'headline' => '',
            'image' => '',
            'datePublished' => '',
            'dateModified' => '',
            'author' => [
                '@type' => 'Person',
                'name' => config('site.name')
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('site.name'),
                'logo' => [
                '@type' => 'ImageObject',
                'value' => '',
                'width' => 600,
                'height'=> 60
            ]
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }
} 