<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class FactCheck extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('ClaimReview');
        
        // Set default data
        $defaultData = [
            'datePublished' => '',
            'url' => '',
            'itemReviewed' => [
                '@type' => 'Claim',
                'appearance' => [
                    '@type' => 'CreativeWork',
                    'name' => '',
                    'url' => ''
                ],
                'author' => [
                    '@type' => 'Organization',
                    'name' => '',
                    'url' => ''
                ],
                'datePublished' => ''
            ],
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => '',
                'bestRating' => 5,
                'worstRating' => 1,
                'alternateName' => ''
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ],
            'claimReviewed' => '',
            'reviewBody' => '',
            'reviewAspect' => '',
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => '',
                'bestRating' => 5,
                'worstRating' => 1,
                'alternateName' => ''
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Set reviewed item information
     */
    public function setItemReviewed($name, $url, $authorName = '', $authorUrl = '', $datePublished = '') {
        $this->schemaData['itemReviewed'] = [
            '@type' => 'Claim',
            'appearance' => [
                '@type' => 'CreativeWork',
                'name' => $name,
                'url' => $url
            ]
        ];

        if ($authorName) {
            $this->schemaData['itemReviewed']['author'] = [
                '@type' => 'Organization',
                'name' => $authorName
            ];

            if ($authorUrl) {
                $this->schemaData['itemReviewed']['author']['url'] = $authorUrl;
            }
        }

        if ($datePublished) {
            $this->schemaData['itemReviewed']['datePublished'] = $datePublished;
        }

        return $this;
    }

    /**
     * Set review rating
     */
    public function setReviewRating($rating, $alternateName = '') {
        $this->schemaData['reviewRating'] = [
            '@type' => 'Rating',
            'ratingValue' => $rating,
            'bestRating' => 5,
            'worstRating' => 1
        ];

        if ($alternateName) {
            $this->schemaData['reviewRating']['alternateName'] = $alternateName;
        }

        return $this;
    }

    /**
     * Set review content
     */
    public function setReviewBody($body, $aspect = '') {
        $this->schemaData['reviewBody'] = $body;

        if ($aspect) {
            $this->schemaData['reviewAspect'] = $aspect;
        }

        return $this;
    }
} 