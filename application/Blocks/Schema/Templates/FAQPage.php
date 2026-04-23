<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class FAQPage extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('FAQPage');
        
        // Set default data
        $defaultData = [
            'mainEntity' => []
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add a question and answer to FAQ
     */
    public function addFAQ($question, $answer) {
        $faq = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer
            ]
        ];

        if (!isset($this->schemaData['mainEntity'])) {
            $this->schemaData['mainEntity'] = [];
        }

        $this->schemaData['mainEntity'][] = $faq;
        return $this;
    }
} 