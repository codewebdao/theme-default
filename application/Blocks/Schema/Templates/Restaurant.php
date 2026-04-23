<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Restaurant extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Restaurant');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'url' => '',
            'telephone' => '',
            'priceRange' => '',
            'servesCuisine' => [],
            'menu' => '',
            'hasMenu' => [
                '@type' => 'Menu',
                'name' => '',
                'url' => '',
                'hasMenuSection' => []
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'Vietnam',
                'addressLocality' => '',
                'addressRegion' => '',
                'streetAddress' => ''
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '',
                'longitude' => ''
            ],
            'openingHoursSpecification' => [],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'ratingCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'photos' => [],
            'amenityFeature' => [],
            'paymentAccepted' => [],
            'publicAccess' => true,
            'smokingAllowed' => false,
            'wheelchairAccessible' => false,
            'servesAlcohol' => false,
            'deliveryAvailable' => false,
            'takeoutAvailable' => false,
            'reservations' => false
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add menu item
     */
    public function addMenu($name, $description, $price, $image = '') {
        $menuItem = [
            '@type' => 'MenuItem',
            'name' => $name,
            'description' => $description,
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'VND'
            ]
        ];

        if ($image) {
            $menuItem['image'] = $image;
        }

        if (!isset($this->schemaData['hasMenu']['hasMenuSection'])) {
            $this->schemaData['hasMenu']['hasMenuSection'] = [];
        }

        $this->schemaData['hasMenu']['hasMenuSection'][] = $menuItem;
        return $this;
    }

    /**
     * Add opening hours
     */
    public function addOpeningHours($dayOfWeek, $opens, $closes) {
        $hours = [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $dayOfWeek,
            'opens' => $opens,
            'closes' => $closes
        ];

        if (!isset($this->schemaData['openingHoursSpecification'])) {
            $this->schemaData['openingHoursSpecification'] = [];
        }

        $this->schemaData['openingHoursSpecification'][] = $hours;
        return $this;
    }

    /**
     * Add photo
     */
    public function addPhoto($url, $caption = '') {
        $photo = [
            '@type' => 'ImageObject',
            'url' => $url
        ];

        if ($caption) {
            $photo['caption'] = $caption;
        }

        if (!isset($this->schemaData['photos'])) {
            $this->schemaData['photos'] = [];
        }

        $this->schemaData['photos'][] = $photo;
        return $this;
    }

    /**
     * Add amenity
     */
    public function addAmenity($name, $value) {
        $amenity = [
            '@type' => 'LocationFeatureSpecification',
            'name' => $name,
            'value' => $value
        ];

        if (!isset($this->schemaData['amenityFeature'])) {
            $this->schemaData['amenityFeature'] = [];
        }

        $this->schemaData['amenityFeature'][] = $amenity;
        return $this;
    }

    /**
     * Add payment method
     */
    public function addPaymentMethod($method) {
        if (!isset($this->schemaData['paymentAccepted'])) {
            $this->schemaData['paymentAccepted'] = [];
        }
        $this->schemaData['paymentAccepted'][] = $method;
        return $this;
    }

    /**
     * Set address
     */
    public function setAddress($address) {
        $this->schemaData['address'] = [
            '@type' => 'PostalAddress',
            'addressCountry' => $address['country'] ?? 'Vietnam',
            'addressLocality' => $address['city'] ?? '',
            'addressRegion' => $address['region'] ?? '',
            'streetAddress' => $address['street'] ?? ''
        ];
        return $this;
    }

    /**
     * Set coordinates
     */
    public function setGeo($latitude, $longitude) {
        $this->schemaData['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
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
} 