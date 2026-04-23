<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Book extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Book');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'image' => '',
            'description' => '',
            'author' => [
                '@type' => 'Person',
                'name' => ''
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('site.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => public_url('assets/images/logo.png')
                ]
            ],
            'datePublished' => '',
            'dateModified' => '',
            'inLanguage' => 'vi',
            'genre' => [],
            'keywords' => []
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }
} 