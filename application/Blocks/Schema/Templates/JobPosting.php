<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class JobPosting extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('JobPosting');
        
        // Set default data
        $defaultData = [
            'title' => '',
            'description' => '',
            'datePosted' => '',
            'validThrough' => '',
            'employmentType' => '',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => option('site_title'),
                'url' => base_url(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => option('site_logo') ?? public_url('assets/images/logo.png')
                ]
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'Vietnam'
                ]
            ],
            'baseSalary' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'VND',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'value' => 0,
                    'unitText' => 'MONTH'
                ]
            ],
            'applicantLocationRequirements' => [
                '@type' => 'Country',
                'name' => 'Vietnam'
            ],
            'jobBenefits' => [],
            'qualifications' => [],
            'responsibilities' => [],
            'skills' => [],
            'workHours' => '',
            'specialCommitments' => ''
        ];

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }

    /**
     * Add job benefit
     */
    public function addJobBenefit($benefit) {
        if (!isset($this->schemaData['jobBenefits'])) {
            $this->schemaData['jobBenefits'] = [];
        }
        $this->schemaData['jobBenefits'][] = $benefit;
        return $this;
    }

    /**
     * Add qualification
     */
    public function addQualification($qualification) {
        if (!isset($this->schemaData['qualifications'])) {
            $this->schemaData['qualifications'] = [];
        }
        $this->schemaData['qualifications'][] = $qualification;
        return $this;
    }

    /**
     * Add responsibility
     */
    public function addResponsibility($responsibility) {
        if (!isset($this->schemaData['responsibilities'])) {
            $this->schemaData['responsibilities'] = [];
        }
        $this->schemaData['responsibilities'][] = $responsibility;
        return $this;
    }

    /**
     * Add skill
     */
    public function addSkill($skill) {
        if (!isset($this->schemaData['skills'])) {
            $this->schemaData['skills'] = [];
        }
        $this->schemaData['skills'][] = $skill;
        return $this;
    }

    /**
     * Set job location
     */
    public function setJobLocation($address) {
        $this->schemaData['jobLocation'] = [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => $address['country'] ?? 'Vietnam',
                'addressLocality' => $address['city'] ?? '',
                'addressRegion' => $address['region'] ?? '',
                'streetAddress' => $address['street'] ?? ''
            ]
        ];
        return $this;
    }

    /**
     * Set base salary
     */
    public function setBaseSalary($amount, $currency = 'VND', $unit = 'MONTH') {
        $this->schemaData['baseSalary'] = [
            '@type' => 'MonetaryAmount',
            'currency' => $currency,
            'value' => [
                '@type' => 'QuantitativeValue',
                'value' => $amount,
                'unitText' => $unit
            ]
        ];
        return $this;
    }
} 