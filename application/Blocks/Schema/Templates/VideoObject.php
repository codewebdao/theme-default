<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class VideoObject extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('VideoObject');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'thumbnailUrl' => '',
            'contentUrl' => '',
            'embedUrl' => '',
            'uploadDate' => '',
            'duration' => '',
            'width' => '',
            'height' => '',
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
            'transcript' => '',
            'caption' => '',
            'interactionStatistic' => [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/WatchAction',
                'userInteractionCount' => 0
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
     * Set transcript
     */
    public function setTranscript($transcript) {
        $this->schemaData['transcript'] = $transcript;
        return $this;
    }

    /**
     * Set caption
     */
    public function setCaption($caption) {
        $this->schemaData['caption'] = $caption;
        return $this;
    }

    /**
     * Update view count
     */
    public function updateViewCount($count) {
        $this->schemaData['interactionStatistic']['userInteractionCount'] = $count;
        return $this;
    }
} 