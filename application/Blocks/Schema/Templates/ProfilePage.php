<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class ProfilePage extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('ProfilePage');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'mainEntity' => [
                '@type' => 'Person',
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
            ],
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => []
            ],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => ''
            ],
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['article', 'section']
            ],
            'isAccessibleForFree' => true,
            'isFamilyFriendly' => true,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => option('site_title'),
                'url' => base_url()
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Set user information
     */
    public function setMainEntity($data) {
        $this->schemaData['mainEntity'] = array_merge($this->schemaData['mainEntity'], $data);
        return $this;
    }

    /**
     * Add breadcrumb
     */
    public function addBreadcrumb($name, $url, $position) {
        $this->schemaData['breadcrumb']['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $url
        ];
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
        $this->schemaData['mainEntity']['alumniOf'][] = $school;
        return $this;
    }

    /**
     * Add award
     */
    public function addAward($award) {
        $this->schemaData['mainEntity']['award'][] = $award;
        return $this;
    }

    /**
     * Add expertise area
     */
    public function addKnowsAbout($topic) {
        $this->schemaData['mainEntity']['knowsAbout'][] = $topic;
        return $this;
    }

    /**
     * Add language
     */
    public function addKnowsLanguage($language) {
        $this->schemaData['mainEntity']['knowsLanguage'][] = $language;
        return $this;
    }

    /**
     * Add social media link
     */
    public function addSameAs($url) {
        $this->schemaData['mainEntity']['sameAs'][] = $url;
        return $this;
    }

    /**
     * Set address
     */
    public function setAddress($address) {
        $this->schemaData['mainEntity']['address'] = [
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
        $this->schemaData['mainEntity']['birthPlace'] = $place;
        return $this;
    }

    /**
     * Add person being followed
     */
    public function addFollows($person) {
        $this->schemaData['mainEntity']['follows'][] = $person;
        return $this;
    }

    /**
     * Add follower
     */
    public function addFollowedBy($person) {
        $this->schemaData['mainEntity']['followedBy'][] = $person;
        return $this;
    }

    /**
     * Add service offered
     */
    public function addMakesOffer($offer) {
        $this->schemaData['mainEntity']['makesOffer'][] = $offer;
        return $this;
    }

    /**
     * Add service sought
     */
    public function addSeeks($seeks) {
        $this->schemaData['mainEntity']['seeks'][] = $seeks;
        return $this;
    }
} 