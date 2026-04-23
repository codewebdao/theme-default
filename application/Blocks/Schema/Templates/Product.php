<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Product extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Product');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'image' => '',
            'description' => '',
            'brand' => [
                '@type' => 'Brand',
                'name' => option('site_title')
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'VND',
                'price' => 0,
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => option('site_title')
                ]
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'reviewCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Set product price
     */
    public function setPrice($price, $currency = 'VND') {
        $this->schemaData['offers'] = [
            '@type' => 'Offer',
            'priceCurrency' => $currency,
            'price' => $price,
            'availability' => 'https://schema.org/InStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => option('site_title')
            ]
        ];
        return $this;
    }

    /**
     * Set aggregate rating
     */
    public function setAggregateRating($rating, $count, $best = 5, $worst = 1) {
        $this->schemaData['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'reviewCount' => $count,
            'bestRating' => $best,
            'worstRating' => $worst
        ];
        return $this;
    }
} 