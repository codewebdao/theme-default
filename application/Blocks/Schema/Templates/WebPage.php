<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class WebPage extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('WebPage');
        
        $currentUrl = $data['url'] ?? base_url();
        $baseUrl = rtrim(base_url(), '/');
        
        // Set default data theo chuẩn RankMath
        $defaultData = [
            '@id' => $currentUrl . '#webpage',
            'url' => $currentUrl,
            'name' => $data['name'] ?? option('site_title', APP_LANG),
            'description' => $data['description'] ?? option('site_desc', APP_LANG),
            'isPartOf' => ['@id' => $baseUrl . '/#website'],
            'about' => ['@id' => $baseUrl . '/#organization'],
            'datePublished' => $data['datePublished'] ?? date('c'),
            'dateModified' => $data['dateModified'] ?? date('c'),
            'breadcrumb' => ['@id' => $currentUrl . '#breadcrumb'],
            'inLanguage' => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
            'potentialAction' => [
                [
                    '@type' => 'ReadAction',
                    'target' => [$currentUrl]
                ]
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
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
     * Set main entity
     */
    public function setMainEntity($entity) {
        $this->schemaData['mainEntity'] = $entity;
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