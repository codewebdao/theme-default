<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Recipe extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Recipe');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'datePublished' => '',
            'dateModified' => '',
            'author' => [
                '@type' => 'Person',
                'name' => option('site_title')
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
            'recipeYield' => '',
            'recipeCategory' => '',
            'recipeCuisine' => '',
            'keywords' => '',
            'recipeIngredient' => [],
            'recipeInstructions' => [],
            'cookTime' => '',
            'prepTime' => '',
            'totalTime' => '',
            'nutrition' => [
                '@type' => 'NutritionInformation',
                'calories' => '',
                'proteinContent' => '',
                'fatContent' => '',
                'carbohydrateContent' => '',
                'fiberContent' => '',
                'sugarContent' => '',
                'sodiumContent' => '',
                'cholesterolContent' => ''
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => 0,
                'ratingCount' => 0,
                'bestRating' => 5,
                'worstRating' => 1
            ],
            'review' => [],
            'video' => [
                '@type' => 'VideoObject',
                'name' => '',
                'description' => '',
                'thumbnailUrl' => '',
                'uploadDate' => '',
                'duration' => '',
                'contentUrl' => '',
                'embedUrl' => ''
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add ingredient
     */
    public function addIngredient($ingredient) {
        if (!isset($this->schemaData['recipeIngredient'])) {
            $this->schemaData['recipeIngredient'] = [];
        }
        $this->schemaData['recipeIngredient'][] = $ingredient;
        return $this;
    }

    /**
     * Add cooking step
     */
    public function addInstruction($name, $text, $image = '') {
        $instruction = [
            '@type' => 'HowToStep',
            'name' => $name,
            'text' => $text
        ];

        if ($image) {
            $instruction['image'] = $image;
        }

        if (!isset($this->schemaData['recipeInstructions'])) {
            $this->schemaData['recipeInstructions'] = [];
        }

        $this->schemaData['recipeInstructions'][] = $instruction;
        return $this;
    }

    /**
     * Set nutrition information
     */
    public function setNutrition($data) {
        $this->schemaData['nutrition'] = [
            '@type' => 'NutritionInformation',
            'calories' => $data['calories'] ?? '',
            'proteinContent' => $data['protein'] ?? '',
            'fatContent' => $data['fat'] ?? '',
            'carbohydrateContent' => $data['carbohydrate'] ?? '',
            'fiberContent' => $data['fiber'] ?? '',
            'sugarContent' => $data['sugar'] ?? '',
            'sodiumContent' => $data['sodium'] ?? '',
            'cholesterolContent' => $data['cholesterol'] ?? ''
        ];
        return $this;
    }

    /**
     * Set video
     */
    public function setVideo($name, $description, $thumbnailUrl, $contentUrl, $embedUrl, $duration = '') {
        $this->schemaData['video'] = [
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