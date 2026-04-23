<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class PodcastEpisode extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('PodcastEpisode');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'datePublished' => '',
            'dateModified' => '',
            'duration' => '',
            'episodeNumber' => '',
            'seasonNumber' => '',
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                'name' => '',
                'url' => ''
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
            'audio' => [
                '@type' => 'AudioObject',
                'contentUrl' => '',
                'duration' => '',
                'encodingFormat' => 'audio/mpeg'
            ],
            'transcript' => '',
            'keywords' => [],
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
     * Set series information
     */
    public function setSeries($name, $url) {
        $this->schemaData['partOfSeries'] = [
            '@type' => 'PodcastSeries',
            'name' => $name,
            'url' => $url
        ];

        return $this;
    }

    /**
     * Set audio information
     */
    public function setAudio($url, $duration, $format = 'audio/mpeg') {
        $this->schemaData['audio'] = [
            '@type' => 'AudioObject',
            'contentUrl' => $url,
            'duration' => $duration,
            'encodingFormat' => $format
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
     * Add keyword
     */
    public function addKeyword($keyword) {
        $this->schemaData['keywords'][] = $keyword;
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