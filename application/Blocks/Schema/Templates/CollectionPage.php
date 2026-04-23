<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class CollectionPage extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('CollectionPage');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'inLanguage' => 'vi-VN',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => option('site_title'),
                'url' => base_url()
            ],
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => []
            ],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => ''
            ],
            'datePublished' => '',
            'dateModified' => '',
            'author' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => []
            ],
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['.content', '.list-items']
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add item to the list
     */
    public function addItem($item) {
        if (!isset($this->schemaData['mainEntity']['itemListElement'])) {
            $this->schemaData['mainEntity']['itemListElement'] = [];
        }

        $position = count($this->schemaData['mainEntity']['itemListElement']) + 1;
        $this->schemaData['mainEntity']['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => $item
        ];

        return $this;
    }

    /**
     * Set breadcrumb
     */
    public function setBreadcrumb($items) {
        $breadcrumbItems = [];
        foreach ($items as $index => $item) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url']
            ];
        }

        $this->schemaData['breadcrumb']['itemListElement'] = $breadcrumbItems;
        return $this;
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage($url, $caption = '') {
        $this->schemaData['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            'url' => $url
        ];

        if ($caption) {
            $this->schemaData['primaryImageOfPage']['caption'] = $caption;
        }

        return $this;
    }
} 