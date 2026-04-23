<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class MathSolver extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('MathSolver');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'applicationCategory' => 'MathSolver',
            'operatingSystem' => '',
            'browserRequirements' => '',
            'offers' => [
                '@type' => 'Offer',
                'price' => '',
                'priceCurrency' => 'VND',
                'availability' => 'InStock'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '',
                'ratingCount' => '',
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'educationalUse' => ['MathSolver'],
            'learningResourceType' => ['MathSolver'],
            'educationalLevel' => [],
            'educationalAlignment' => [],
            'educationalCredentialAwarded' => '',
            'teaches' => [],
            'assesses' => [],
            'educationalProgramMode' => '',
            'timeToComplete' => '',
            'inLanguage' => ['vi', 'en'],
            'isAccessibleForFree' => false,
            'isFamilyFriendly' => true,
            'isBasedOn' => [],
            'isPartOf' => [],
            'hasPart' => [],
            'position' => '',
            'potentialAction' => [
                '@type' => 'SolveMathAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => '',
                    'actionPlatform' => [
                        'https://schema.org/DesktopWebPlatform',
                        'https://schema.org/MobileWebPlatform'
                    ]
                ]
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add educational level
     */
    public function addEducationalLevel($level) {
        $this->schemaData['educationalLevel'][] = $level;
        return $this;
    }

    /**
     * Add educational alignment
     */
    public function addEducationalAlignment($alignment) {
        $this->schemaData['educationalAlignment'][] = $alignment;
        return $this;
    }

    /**
     * Add subject
     */
    public function addTeaches($subject) {
        $this->schemaData['teaches'][] = $subject;
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

    /**
     * Set URL template for action
     */
    public function setActionUrlTemplate($template) {
        $this->schemaData['potentialAction']['target']['urlTemplate'] = $template;
        return $this;
    }
} 