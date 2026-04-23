<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Dataset extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Dataset');
        
        // Set default data
        $defaultData = [
            'name' => '',
            'description' => '',
            'url' => '',
            'version' => '',
            'datePublished' => '',
            'dateModified' => '',
            'author' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url()
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
            'license' => '',
            'keywords' => [],
            'includedInDataCatalog' => [
                '@type' => 'DataCatalog',
                'name' => '',
                'url' => ''
            ],
            'distribution' => [],
            'variableMeasured' => [],
            'spatialCoverage' => [
                '@type' => 'Place',
                'name' => 'Vietnam'
            ],
            'temporalCoverage' => '',
            'citation' => [],
            'isAccessibleForFree' => true,
            'measurementTechnique' => '',
            'variableMeasured' => [],
            'includedInDataCatalog' => [
                '@type' => 'DataCatalog',
                'name' => '',
                'url' => ''
            ]
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add keyword
     */
    public function addKeyword($keyword) {
        if (!isset($this->schemaData['keywords'])) {
            $this->schemaData['keywords'] = [];
        }
        $this->schemaData['keywords'][] = $keyword;
        return $this;
    }

    /**
     * Add data distribution
     */
    public function addDistribution($name, $url, $format = '', $size = '') {
        $distribution = [
            '@type' => 'DataDownload',
            'name' => $name,
            'url' => $url
        ];

        if ($format) {
            $distribution['encodingFormat'] = $format;
        }

        if ($size) {
            $distribution['contentSize'] = $size;
        }

        if (!isset($this->schemaData['distribution'])) {
            $this->schemaData['distribution'] = [];
        }

        $this->schemaData['distribution'][] = $distribution;
        return $this;
    }

    /**
     * Add measured variable
     */
    public function addVariableMeasured($name, $unit = '') {
        $variable = [
            '@type' => 'PropertyValue',
            'name' => $name
        ];

        if ($unit) {
            $variable['unitText'] = $unit;
        }

        if (!isset($this->schemaData['variableMeasured'])) {
            $this->schemaData['variableMeasured'] = [];
        }

        $this->schemaData['variableMeasured'][] = $variable;
        return $this;
    }

    /**
     * Add citation
     */
    public function addCitation($name, $url = '') {
        $citation = [
            '@type' => 'CreativeWork',
            'name' => $name
        ];

        if ($url) {
            $citation['url'] = $url;
        }

        if (!isset($this->schemaData['citation'])) {
            $this->schemaData['citation'] = [];
        }

        $this->schemaData['citation'][] = $citation;
        return $this;
    }

    /**
     * Set spatial coverage
     */
    public function setSpatialCoverage($name, $geo = null) {
        $coverage = [
            '@type' => 'Place',
            'name' => $name
        ];

        if ($geo) {
            $coverage['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude']
            ];
        }

        $this->schemaData['spatialCoverage'] = $coverage;
        return $this;
    }

    /**
     * Set temporal coverage
     */
    public function setTemporalCoverage($startDate, $endDate = '') {
        if ($endDate) {
            $this->schemaData['temporalCoverage'] = $startDate . '/' . $endDate;
        } else {
            $this->schemaData['temporalCoverage'] = $startDate;
        }
        return $this;
    }

    /**
     * Set data catalog
     */
    public function setDataCatalog($name, $url) {
        $this->schemaData['includedInDataCatalog'] = [
            '@type' => 'DataCatalog',
            'name' => $name,
            'url' => $url
        ];
        return $this;
    }
} 