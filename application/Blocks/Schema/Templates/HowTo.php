<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class HowTo extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('HowTo');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'image' => '',
            'totalTime' => '',
            'estimatedCost' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'VND',
                'value' => 0
            ],
            'supply' => [],
            'tool' => [],
            'step' => [],
            'author' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add instruction step
     */
    public function addStep($name, $text, $image = '') {
        $step = [
            '@type' => 'HowToStep',
            'name' => $name,
            'text' => $text
        ];

        if ($image) {
            $step['image'] = $image;
        }

        if (!isset($this->schemaData['step'])) {
            $this->schemaData['step'] = [];
        }

        $this->schemaData['step'][] = $step;
        return $this;
    }

    /**
     * Add required supplies
     */
    public function addSupply($name, $url = '') {
        $supply = [
            '@type' => 'HowToSupply',
            'name' => $name
        ];

        if ($url) {
            $supply['url'] = $url;
        }

        if (!isset($this->schemaData['supply'])) {
            $this->schemaData['supply'] = [];
        }

        $this->schemaData['supply'][] = $supply;
        return $this;
    }

    /**
     * Add required tools
     */
    public function addTool($name, $url = '') {
        $tool = [
            '@type' => 'HowToTool',
            'name' => $name
        ];

        if ($url) {
            $tool['url'] = $url;
        }

        if (!isset($this->schemaData['tool'])) {
            $this->schemaData['tool'] = [];
        }

        $this->schemaData['tool'][] = $tool;
        return $this;
    }
} 