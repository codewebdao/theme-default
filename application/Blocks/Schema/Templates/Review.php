<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Review extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Review');
        
        // Set default data
        $defaultData = [
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'author' => [
                '@type' => 'Person',
                'name' => option('site_title')
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Set rating
     */
    public function setRating($value, $best = 5, $worst = 1) {
        $this->schemaData['reviewRating'] = [
            '@type' => 'Rating',
            'ratingValue' => $value,
            'bestRating' => $best,
            'worstRating' => $worst
        ];
        return $this;
    }
} 