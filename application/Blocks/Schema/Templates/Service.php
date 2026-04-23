<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Service extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Service');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'provider' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Vietnam'
            ],
            'serviceType' => '',
            'offers' => [
                '@type' => 'Offer',
                'price' => 0,
                'priceCurrency' => 'VND',
                'availability' => 'https://schema.org/InStock',
                'validFrom' => '',
                'url' => ''
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Services',
                'itemListElement' => []
            ],
            'review' => [],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'ratingCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add sub-service
     */
    public function addServiceItem($name, $description, $price = 0, $url = '') {
        $item = [
            '@type' => 'Offer',
            'itemOffered' => [
                '@type' => 'Service',
                'name' => $name,
                'description' => $description
            ],
            'price' => $price,
            'priceCurrency' => 'VND',
            'availability' => 'https://schema.org/InStock'
        ];

        if ($url) {
            $item['url'] = $url;
        }

        if (!isset($this->schemaData['hasOfferCatalog']['itemListElement'])) {
            $this->schemaData['hasOfferCatalog']['itemListElement'] = [];
        }

        $this->schemaData['hasOfferCatalog']['itemListElement'][] = $item;
        return $this;
    }

    /**
     * Add review
     */
    public function addReview($author, $reviewBody, $ratingValue, $datePublished = '') {
        $review = [
            '@type' => 'Review',
            'author' => [
                '@type' => 'Person',
                'name' => $author
            ],
            'reviewBody' => $reviewBody,
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $ratingValue,
                'bestRating' => 5,
                'worstRating' => 1
            ]
        ];

        if ($datePublished) {
            $review['datePublished'] = $datePublished;
        }

        if (!isset($this->schemaData['review'])) {
            $this->schemaData['review'] = [];
        }

        $this->schemaData['review'][] = $review;
        return $this;
    }

    /**
     * Set aggregate rating
     */
    public function setAggregateRating($rating, $count) {
        $this->schemaData['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'ratingCount' => $count,
            'bestRating' => 5,
            'worstRating' => 1
        ];
        return $this;
    }

    /**
     * Set service area
     */
    public function setAreaServed($areas) {
        $this->schemaData['areaServed'] = [];
        foreach ($areas as $area) {
            $this->schemaData['areaServed'][] = [
                '@type' => 'Country',
                'name' => $area
            ];
        }
        return $this;
    }
} 