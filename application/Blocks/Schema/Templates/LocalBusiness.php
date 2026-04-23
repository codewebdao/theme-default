<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class LocalBusiness extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('LocalBusiness');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'url' => '',
            'telephone' => '',
            'email' => '',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '',
                'addressLocality' => '',
                'addressRegion' => '',
                'postalCode' => '',
                'addressCountry' => 'VN'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '',
                'longitude' => ''
            ],
            'openingHoursSpecification' => [],
            'priceRange' => '',
            'paymentAccepted' => [],
            'currenciesAccepted' => ['VND'],
            'areaServed' => [],
            'hasMap' => '',
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '',
                'ratingCount' => '',
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'amenityFeature' => [],
            'photo' => [],
            'publicAccess' => true,
            'smokingAllowed' => false,
            'wheelchairAccessible' => false,
            'servesCuisine' => [],
            'menu' => '',
            'acceptsReservations' => false,
            'servesCuisine' => [],
            'hasMenu' => '',
            'menuUrl' => '',
            'servesCuisine' => []
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add opening hours
     */
    public function addOpeningHours($dayOfWeek, $opens, $closes) {
        $this->schemaData['openingHoursSpecification'][] = [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $dayOfWeek,
            'opens' => $opens,
            'closes' => $closes
        ];
        return $this;
    }

    /**
     * Add payment method
     */
    public function addPaymentMethod($method) {
        $this->schemaData['paymentAccepted'][] = $method;
        return $this;
    }

    /**
     * Add service area
     */
    public function addAreaServed($area) {
        $this->schemaData['areaServed'][] = $area;
        return $this;
    }

    /**
     * Add amenity
     */
    public function addAmenity($name, $value) {
        $this->schemaData['amenityFeature'][] = [
            '@type' => 'LocationFeatureSpecification',
            'name' => $name,
            'value' => $value
        ];
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
        $this->schemaData['photo'][] = $photo;
        return $this;
    }

    /**
     * Add cuisine
     */
    public function addCuisine($cuisine) {
        $this->schemaData['servesCuisine'][] = $cuisine;
        return $this;
    }

    /**
     * Set menu
     */
    public function setMenu($url) {
        $this->schemaData['hasMenu'] = $url;
        $this->schemaData['menuUrl'] = $url;
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