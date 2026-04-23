<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class SoftwareApplication extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('SoftwareApplication');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'applicationCategory' => 'EntertainmentApplication',
            'operatingSystem' => '',
            'offers' => [
                '@type' => 'Offer',
                'price' => 0,
                'priceCurrency' => 'VND',
                'availability' => 'https://schema.org/InStock'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'ratingCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'downloadUrl' => '',
            'softwareVersion' => '',
            'releaseNotes' => '',
            'screenshot' => [],
            'author' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add screenshot
     */
    public function addScreenshot($url, $caption = '') {
        $screenshot = [
            '@type' => 'ImageObject',
            'url' => $url
        ];

        if ($caption) {
            $screenshot['caption'] = $caption;
        }

        if (!isset($this->schemaData['screenshot'])) {
            $this->schemaData['screenshot'] = [];
        }

        $this->schemaData['screenshot'][] = $screenshot;
        return $this;
    }

    /**
     * Set rating
     */
    public function setRating($rating, $count) {
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
     * Set price information
     */
    public function setPrice($price, $currency = 'VND') {
        $this->schemaData['offers'] = [
            '@type' => 'Offer',
            'price' => $price,
            'priceCurrency' => $currency,
            'availability' => 'https://schema.org/InStock'
        ];
        return $this;
    }
} 