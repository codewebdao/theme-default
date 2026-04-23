<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Person extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Person');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'alternateName' => '',
            'description' => '',
            'image' => '',
            'url' => '',
            'email' => '',
            'telephone' => '',
            'jobTitle' => '',
            'worksFor' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
            ],
            'alumniOf' => [],
            'award' => [],
            'knowsAbout' => [],
            'knowsLanguage' => [],
            'sameAs' => [],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '',
                'addressLocality' => '',
                'addressRegion' => '',
                'postalCode' => '',
                'addressCountry' => 'VN'
            ],
            'birthDate' => '',
            'birthPlace' => [
                '@type' => 'Place',
                'name' => ''
            ],
            'gender' => '',
            'nationality' => '',
            'height' => '',
            'weight' => '',
            'follows' => [],
            'followedBy' => [],
            'makesOffer' => [],
            'seeks' => []
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add school
     */
    public function addAlumniOf($name, $url = '') {
        $school = [
            '@type' => 'CollegeOrUniversity',
            'name' => $name
        ];

        if ($url) {
            $school['url'] = $url;
        }

        $this->schemaData['alumniOf'][] = $school;
        return $this;
    }

    /**
     * Add award
     */
    public function addAward($award) {
        $this->schemaData['award'][] = $award;
        return $this;
    }

    /**
     * Add expertise area
     */
    public function addKnowsAbout($topic) {
        $this->schemaData['knowsAbout'][] = $topic;
        return $this;
    }

    /**
     * Add language
     */
    public function addKnowsLanguage($language) {
        $this->schemaData['knowsLanguage'][] = $language;
        return $this;
    }

    /**
     * Add social media link
     */
    public function addSameAs($url) {
        $this->schemaData['sameAs'][] = $url;
        return $this;
    }

    /**
     * Set address
     */
    public function setAddress($address) {
        $this->schemaData['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $address['streetAddress'] ?? '',
            'addressLocality' => $address['addressLocality'] ?? '',
            'addressRegion' => $address['addressRegion'] ?? '',
            'postalCode' => $address['postalCode'] ?? '',
            'addressCountry' => $address['addressCountry'] ?? 'VN'
        ];

        return $this;
    }

    /**
     * Set birth place
     */
    public function setBirthPlace($name, $geo = null) {
        $place = [
            '@type' => 'Place',
            'name' => $name
        ];

        if ($geo) {
            $place['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude']
            ];
        }

        $this->schemaData['birthPlace'] = $place;
        return $this;
    }

    /**
     * Add person being followed
     */
    public function addFollows($person) {
        $this->schemaData['follows'][] = $person;
        return $this;
    }

    /**
     * Add follower
     */
    public function addFollowedBy($person) {
        $this->schemaData['followedBy'][] = $person;
        return $this;
    }

    /**
     * Add service offered
     */
    public function addMakesOffer($offer) {
        $this->schemaData['makesOffer'][] = $offer;
        return $this;
    }

    /**
     * Add service sought
     */
    public function addSeeks($seeks) {
        $this->schemaData['seeks'][] = $seeks;
        return $this;
    }
} 