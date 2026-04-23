<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Course extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Course');
        
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
            'courseCode' => '',
            'educationalCredentialAwarded' => '',
            'timeToComplete' => '',
            'hasCourseInstance' => [],
            'instructor' => [],
            'offers' => [
                '@type' => 'Offer',
                'price' => 0,
                'priceCurrency' => 'VND',
                'availability' => 'https://schema.org/InStock',
                'validFrom' => '',
                'url' => ''
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add instructor
     */
    public function addInstructor($name, $image = '', $url = '') {
        $instructor = [
            '@type' => 'Person',
            'name' => $name
        ];

        if ($image) {
            $instructor['image'] = $image;
        }

        if ($url) {
            $instructor['url'] = $url;
        }

        if (!isset($this->schemaData['instructor'])) {
            $this->schemaData['instructor'] = [];
        }

        $this->schemaData['instructor'][] = $instructor;
        return $this;
    }

    /**
     * Add specific course instance
     */
    public function addCourseInstance($name, $startDate, $endDate, $location = '') {
        $instance = [
            '@type' => 'CourseInstance',
            'name' => $name,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        if ($location) {
            $instance['location'] = [
                '@type' => 'Place',
                'name' => $location
            ];
        }

        if (!isset($this->schemaData['hasCourseInstance'])) {
            $this->schemaData['hasCourseInstance'] = [];
        }

        $this->schemaData['hasCourseInstance'][] = $instance;
        return $this;
    }

    /**
     * Set price information
     */
    public function setPrice($price, $currency = 'VND', $validFrom = '', $url = '') {
        $this->schemaData['offers'] = [
            '@type' => 'Offer',
            'price' => $price,
            'priceCurrency' => $currency,
            'availability' => 'https://schema.org/InStock',
            'validFrom' => $validFrom,
            'url' => $url
        ];
        return $this;
    }
} 