<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class CreativeWork extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('CreativeWork');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'image' => '',
            'datePublished' => '',
            'dateModified' => '',
            'author' => [
                '@type' => 'Person',
                'name' => '',
                'url' => ''
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
            'isAccessibleForFree' => false,
            'isFamilyFriendly' => true,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => option('site_title'),
                'url' => base_url()
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => '',
                'priceCurrency' => 'VND',
                'availability' => 'InStock',
                'validFrom' => '',
                'url' => ''
            ],
            'license' => '',
            'copyrightYear' => '',
            'copyrightHolder' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
            ],
            'inLanguage' => ['vi', 'en'],
            'keywords' => [],
            'genre' => [],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '',
                'ratingCount' => '',
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'interactionStatistic' => [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/ViewAction',
                'userInteractionCount' => 0
            ],
            'comment' => [],
            'commentCount' => 0,
            'wordCount' => 0,
            'text' => '',
            'citation' => [],
            'isBasedOn' => [],
            'isPartOf' => [],
            'hasPart' => [],
            'position' => '',
            'potentialAction' => [
                '@type' => 'ReadAction',
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
     * Add keyword
     */
    public function addKeyword($keyword) {
        $this->schemaData['keywords'][] = $keyword;
        return $this;
    }

    /**
     * Add genre
     */
    public function addGenre($genre) {
        $this->schemaData['genre'][] = $genre;
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
     * Update view count
     */
    public function updateViewCount($count) {
        $this->schemaData['interactionStatistic']['userInteractionCount'] = $count;
        return $this;
    }

    /**
     * Add comment
     */
    public function addComment($text, $authorName = '', $authorUrl = '', $datePublished = '') {
        $comment = [
            '@type' => 'Comment',
            'text' => $text
        ];

        if ($authorName) {
            $comment['author'] = [
                '@type' => 'Person',
                'name' => $authorName
            ];
            if ($authorUrl) {
                $comment['author']['url'] = $authorUrl;
            }
        }

        if ($datePublished) {
            $comment['datePublished'] = $datePublished;
        }

        $this->schemaData['comment'][] = $comment;
        $this->schemaData['commentCount'] = count($this->schemaData['comment']);
        return $this;
    }

    /**
     * Add citation
     */
    public function addCitation($citation) {
        $this->schemaData['citation'][] = $citation;
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