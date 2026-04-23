<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Music extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('MusicRecording');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'datePublished' => '',
            'duration' => '',
            'genre' => [],
            'byArtist' => [
                '@type' => 'MusicGroup',
                'name' => '',
                'url' => ''
            ],
            'inAlbum' => [
                '@type' => 'MusicAlbum',
                'name' => '',
                'url' => ''
            ],
            'isrcCode' => '',
            'recordingOf' => [
                '@type' => 'MusicComposition',
                'name' => '',
                'composer' => [
                    '@type' => 'Person',
                    'name' => ''
                ]
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'ratingCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'audio' => [
                '@type' => 'AudioObject',
                'contentUrl' => '',
                'duration' => '',
                'encodingFormat' => 'mp3'
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add genre
     */
    public function addGenre($genre) {
        if (!isset($this->schemaData['genre'])) {
            $this->schemaData['genre'] = [];
        }
        $this->schemaData['genre'][] = $genre;
        return $this;
    }

    /**
     * Set artist
     */
    public function setArtist($name, $url = '') {
        $this->schemaData['byArtist'] = [
            '@type' => 'MusicGroup',
            'name' => $name,
            'url' => $url
        ];
        return $this;
    }

    /**
     * Set album
     */
    public function setAlbum($name, $url = '') {
        $this->schemaData['inAlbum'] = [
            '@type' => 'MusicAlbum',
            'name' => $name,
            'url' => $url
        ];
        return $this;
    }

    /**
     * Set original composition
     */
    public function setRecordingOf($name, $composerName) {
        $this->schemaData['recordingOf'] = [
            '@type' => 'MusicComposition',
            'name' => $name,
            'composer' => [
                '@type' => 'Person',
                'name' => $composerName
            ]
        ];
        return $this;
    }

    /**
     * Set audio
     */
    public function setAudio($url, $duration, $format = 'mp3') {
        $this->schemaData['audio'] = [
            '@type' => 'AudioObject',
            'contentUrl' => $url,
            'duration' => $duration,
            'encodingFormat' => $format
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