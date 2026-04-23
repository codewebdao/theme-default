<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class WebSite extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('WebSite');
        
        $baseUrl = rtrim(base_url(), '/');
        
        // Set default data theo chuẩn RankMath
        $defaultData = [
            '@id' => $baseUrl . '/#website',
            'url' => $baseUrl,
            'name' => $data['name'] ?? option('site_title', APP_LANG),
            'description' => $data['description'] ?? option('site_desc', APP_LANG),
            'alternateName' => $data['alternateName'] ?? option('site_title', APP_LANG),
            'publisher' => ['@id' => $baseUrl . '/#organization'],
            'inLanguage' => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
            'potentialAction' => [
                [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => base_url('search') . '?q={search_term_string}'
                    ],
                    'query-input' => [
                        '@type' => 'PropertyValueSpecification',
                        'valueRequired' => true,
                        'valueName' => 'search_term_string'
                    ]
                ]
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }
} 