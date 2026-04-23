<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Movie extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Movie');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'datePublished' => '',
            'director' => [],
            'actor' => [],
            'genre' => [],
            'duration' => '',
            'contentRating' => '',
            'productionCompany' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'ratingCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'trailer' => [
                '@type' => 'VideoObject',
                'name' => '',
                'description' => '',
                'thumbnailUrl' => '',
                'uploadDate' => '',
                'duration' => '',
                'contentUrl' => '',
                'embedUrl' => ''
            ],
            'basedOn' => [
                '@type' => 'Book',
                'name' => '',
                'url' => ''
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add director
     */
    public function addDirector($name, $url = '') {
        $director = [
            '@type' => 'Person',
            'name' => $name
        ];

        if ($url) {
            $director['url'] = $url;
        }

        if (!isset($this->schemaData['director'])) {
            $this->schemaData['director'] = [];
        }

        $this->schemaData['director'][] = $director;
        return $this;
    }

    /**
     * Add actor
     */
    public function addActor($name, $url = '', $role = '') {
        $actor = [
            '@type' => 'Person',
            'name' => $name
        ];

        if ($url) {
            $actor['url'] = $url;
        }

        if ($role) {
            $actor['characterName'] = $role;
        }

        if (!isset($this->schemaData['actor'])) {
            $this->schemaData['actor'] = [];
        }

        $this->schemaData['actor'][] = $actor;
        return $this;
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
     * Set trailer
     */
    public function setTrailer($name, $description, $thumbnailUrl, $contentUrl, $embedUrl, $duration = '') {
        $this->schemaData['trailer'] = [
            '@type' => 'VideoObject',
            'name' => $name,
            'description' => $description,
            'thumbnailUrl' => $thumbnailUrl,
            'uploadDate' => date('Y-m-d'),
            'duration' => $duration,
            'contentUrl' => $contentUrl,
            'embedUrl' => $embedUrl
        ];
        return $this;
    }

    /**
     * Set original story
     */
    public function setBasedOn($name, $url) {
        $this->schemaData['basedOn'] = [
            '@type' => 'Book',
            'name' => $name,
            'url' => $url
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