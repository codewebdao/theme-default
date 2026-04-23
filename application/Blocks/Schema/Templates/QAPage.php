<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class QAPage extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('QAPage');
        
        // Set default data
        $defaultData = [
            'mainEntity' => [
                '@type' => 'Question',
                'name' => '',
                'text' => '',
                'answerCount' => 0,
                'upvoteCount' => 0,
                'dateCreated' => '',
                'dateModified' => '',
                'author' => [
                    '@type' => 'Person',
                    'name' => '',
                    'url' => ''
                ],
                'suggestedAnswer' => [],
                'acceptedAnswer' => null
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
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Set question information
     */
    public function setQuestion($name, $text, $authorName = '', $authorUrl = '') {
        $this->schemaData['mainEntity'] = [
            '@type' => 'Question',
            'name' => $name,
            'text' => $text,
            'answerCount' => 0,
            'upvoteCount' => 0,
            'dateCreated' => date('c'),
            'dateModified' => date('c')
        ];

        if ($authorName) {
            $this->schemaData['mainEntity']['author'] = [
                '@type' => 'Person',
                'name' => $authorName
            ];

            if ($authorUrl) {
                $this->schemaData['mainEntity']['author']['url'] = $authorUrl;
            }
        }

        return $this;
    }

    /**
     * Add suggested answer
     */
    public function addSuggestedAnswer($text, $authorName = '', $authorUrl = '', $upvoteCount = 0) {
        $answer = [
            '@type' => 'Answer',
            'text' => $text,
            'upvoteCount' => $upvoteCount,
            'dateCreated' => date('c')
        ];

        if ($authorName) {
            $answer['author'] = [
                '@type' => 'Person',
                'name' => $authorName
            ];

            if ($authorUrl) {
                $answer['author']['url'] = $authorUrl;
            }
        }

        $this->schemaData['mainEntity']['suggestedAnswer'][] = $answer;
        $this->schemaData['mainEntity']['answerCount']++;

        return $this;
    }

    /**
     * Set accepted answer
     */
    public function setAcceptedAnswer($text, $authorName = '', $authorUrl = '', $upvoteCount = 0) {
        $answer = [
            '@type' => 'Answer',
            'text' => $text,
            'upvoteCount' => $upvoteCount,
            'dateCreated' => date('c')
        ];

        if ($authorName) {
            $answer['author'] = [
                '@type' => 'Person',
                'name' => $authorName
            ];

            if ($authorUrl) {
                $answer['author']['url'] = $authorUrl;
            }
        }

        $this->schemaData['mainEntity']['acceptedAnswer'] = $answer;
        $this->schemaData['mainEntity']['answerCount']++;

        return $this;
    }

    /**
     * Update upvote count
     */
    public function updateUpvoteCount($count) {
        $this->schemaData['mainEntity']['upvoteCount'] = $count;
        return $this;
    }
} 