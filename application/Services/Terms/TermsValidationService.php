<?php

namespace App\Services\Terms;

use System\Libraries\Validate;

/**
 * TermsValidationService - Validation for Terms
 * 
 * Handles term validation logic
 * 
 * @package App\Services\Terms
 */
class TermsValidationService
{
    /**
     * Build validation rules for term
     * 
     * @return array Validation rules
     */
    public function buildRules()
    {
        return [
            'name' => [
                'rules' => [
                    Validate::notEmpty(),
                    Validate::length(2, 100)
                ],
                'messages' => [
                    __('Name is required'),
                    __('Name length must be between 2 and 100 characters')
                ]
            ],
            'slug' => [
                'rules' => [Validate::notEmpty()],
                'messages' => [__('Slug is required')]
            ],
            'type' => [
                'rules' => [Validate::notEmpty()],
                'messages' => [__('Type is required')]
            ],
            'posttype' => [
                'rules' => [Validate::notEmpty()],
                'messages' => [__('Posttype is required')]
            ],
            'lang' => [
                'rules' => [Validate::notEmpty()],
                'messages' => [__('Language is required')]
            ],
            'parent' => [
                'rules' => [Validate::optional(Validate::numericVal())],
                'messages' => [__('Parent must be a number')]
            ]
        ];
    }

    /**
     * Validate term data
     * 
     * @param array $data Term data
     * @param array $rules Validation rules
     * @return array Errors
     */
    public function validate($data, $rules)
    {
        $validator = new Validate();
        
        if (!$validator->check($data, $rules)) {
            return $validator->getErrors();
        }

        return [];
    }

    /**
     * Validate and prepare term data
     * 
     * @param array $data Input data
     * @return array ['success' => bool, 'data' => array, 'errors' => array]
     */
    public function validateAndPrepare($data)
    {
        // Required fields check
        $required = ['name', 'type', 'posttype', 'lang'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'data' => [],
                    'errors' => [$field => ["Field {$field} is required"]]
                ];
            }
        }

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = url_slug($data['name']);
        }

        // Prepare term data
        $termData = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'type' => $data['type'],
            'posttype' => $data['posttype'],
            'parent' => (!empty($data['parent']) && $data['parent'] != 0) ? (int)$data['parent'] : null,
            'lang' => $data['lang'],
            'id_main' => (isset($data['id_main']) && is_numeric($data['id_main'])) ? (int)$data['id_main'] : 0,
            'seo_title' => $data['seo_title'] ?? '',
            'seo_desc' => $data['seo_desc'] ?? '',
            'status' => in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active'
        ];

        return [
            'success' => true,
            'data' => $termData,
            'errors' => []
        ];
    }

    /**
     * Validate status value
     * 
     * @param string $status Status to validate
     * @return string Valid status
     */
    public function validateStatus($status)
    {
        return in_array($status, ['active', 'inactive']) ? $status : 'active';
    }
}

