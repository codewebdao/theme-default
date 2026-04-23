<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Event extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Event');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'startDate' => '',
            'endDate' => '',
            'eventStatus' => 'EventScheduled',
            'eventAttendanceMode' => 'OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => '',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '',
                    'addressLocality' => '',
                    'addressRegion' => '',
                    'postalCode' => '',
                    'addressCountry' => 'VN'
                ]
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ],
            'performer' => [],
            'offers' => [
                '@type' => 'Offer',
                'price' => '',
                'priceCurrency' => 'VND',
                'availability' => 'InStock',
                'validFrom' => '',
                'url' => ''
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '',
                'ratingCount' => '',
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => []
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Set location
     */
    public function setLocation($name, $address) {
        $this->schemaData['location'] = [
            '@type' => 'Place',
            'name' => $name,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['streetAddress'] ?? '',
                'addressLocality' => $address['addressLocality'] ?? '',
                'addressRegion' => $address['addressRegion'] ?? '',
                'postalCode' => $address['postalCode'] ?? '',
                'addressCountry' => $address['addressCountry'] ?? 'VN'
            ]
        ];

        return $this;
    }

    /**
     * Add performer
     */
    public function addPerformer($name, $url = '') {
        $performer = [
            '@type' => 'Person',
            'name' => $name
        ];

        if ($url) {
            $performer['url'] = $url;
        }

        $this->schemaData['performer'][] = $performer;
        return $this;
    }

    /**
     * Set ticket price
     */
    public function setOffer($price, $currency = 'VND', $availability = 'InStock', $validFrom = '', $url = '') {
        $this->schemaData['offers'] = [
            '@type' => 'Offer',
            'price' => $price,
            'priceCurrency' => $currency,
            'availability' => $availability
        ];

        if ($validFrom) {
            $this->schemaData['offers']['validFrom'] = $validFrom;
        }

        if ($url) {
            $this->schemaData['offers']['url'] = $url;
        }

        return $this;
    }

    /**
     * Add review
     */
    public function addReview($review) {
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
} 