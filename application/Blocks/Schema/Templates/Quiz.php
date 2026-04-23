<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Quiz extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Quiz');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'educationalUse' => ['Quiz'],
            'learningResourceType' => ['Quiz'],
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
            'question' => [],
            'answer' => [],
            'potentialAction' => [
                '@type' => 'TakeQuizAction',
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
     * Add question
     */
    public function addQuestion($text, $options = [], $correctAnswer = '') {
        $question = [
            '@type' => 'Question',
            'text' => $text
        ];

        if (!empty($options)) {
            $question['suggestedAnswer'] = [];
            foreach ($options as $option) {
                $question['suggestedAnswer'][] = [
                    '@type' => 'Answer',
                    'text' => $option
                ];
            }
        }

        if ($correctAnswer) {
            $question['acceptedAnswer'] = [
                '@type' => 'Answer',
                'text' => $correctAnswer
            ];
        }

        $this->schemaData['question'][] = $question;
        return $this;
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
     * Set URL template for action
     */
    public function setActionUrlTemplate($template) {
        $this->schemaData['potentialAction']['target']['urlTemplate'] = $template;
        return $this;
    }
} 