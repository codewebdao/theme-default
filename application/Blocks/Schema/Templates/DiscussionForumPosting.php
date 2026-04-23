<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class DiscussionForumPosting extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('DiscussionForumPosting');
        
        // Set default data
        $defaultData = [
            'headline' => '',
            'text' => '',
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
            'comment' => [],
            'upvoteCount' => 0,
            'downvoteCount' => 0,
            'interactionStatistic' => [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/ViewAction',
                'userInteractionCount' => 0
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add comment
     */
    public function addComment($text, $authorName = '', $authorUrl = '', $datePublished = '', $upvoteCount = 0, $downvoteCount = 0, $replies = []) {
        $comment = [
            '@type' => 'Comment',
            'text' => $text,
            'upvoteCount' => $upvoteCount,
            'downvoteCount' => $downvoteCount
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

        if (!empty($replies)) {
            $comment['comment'] = [];
            foreach ($replies as $reply) {
                $comment['comment'][] = [
                    '@type' => 'Comment',
                    'text' => $reply['text'] ?? '',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $reply['authorName'] ?? '',
                        'url' => $reply['authorUrl'] ?? ''
                    ],
                    'datePublished' => $reply['datePublished'] ?? '',
                    'upvoteCount' => $reply['upvoteCount'] ?? 0,
                    'downvoteCount' => $reply['downvoteCount'] ?? 0
                ];
            }
        }

        $this->schemaData['comment'][] = $comment;
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
     * Update upvote count
     */
    public function updateUpvoteCount($count) {
        $this->schemaData['upvoteCount'] = $count;
        return $this;
    }

    /**
     * Update downvote count
     */
    public function updateDownvoteCount($count) {
        $this->schemaData['downvoteCount'] = $count;
        return $this;
    }
} 