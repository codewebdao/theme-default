<?php
namespace App\Blocks\Schema\Templates;

use App\Blocks\Schema\SchemaBlock;

class Organization extends SchemaBlock {
    public function __construct($data = []) {
        parent::__construct();
        $this->setSchemaType('Organization');
        
        $baseUrl = rtrim(base_url(), '/');
        $logoUrl = $data['logo'] ?? option('site_logo');
        
        // Set default data theo chuẩn RankMath
        $defaultData = [
            '@id' => $baseUrl . '/#organization',
            'name' => $data['name'] ?? option('site_title', APP_LANG),
            'alternateName' => $data['alternateName'] ?? option('site_title', APP_LANG),
            'url' => $baseUrl,
            'email' => $data['email'] ?? option('site_email'),
            'telephone' => $data['telephone'] ?? option('site_phone'),
            'description' => $data['description'] ?? option('site_desc', APP_LANG),
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => $baseUrl . '/#/schema/logo/image/',
                'inLanguage' => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
                'url' => is_string($logoUrl) ? $logoUrl : $baseUrl . '/assets/images/logo.png',
                'contentUrl' => is_string($logoUrl) ? $logoUrl : $baseUrl . '/assets/images/logo.png',
                'width' => $data['logoWidth'] ?? 600,
                'height' => $data['logoHeight'] ?? 60,
                'caption' => $data['name'] ?? option('site_title', APP_LANG)
            ],
            'image' => ['@id' => $baseUrl . '/#/schema/logo/image/']
        ];
        
        // Add social media links if available
        $socialLinks = [];
        $socialData = $data['social'] ?? option('social', APP_LANG);
        if (is_array($socialData)) {
            foreach (['facebook', 'twitter', 'youtube', 'instagram', 'linkedin', 'pinterest'] as $platform) {
                if (!empty($socialData[$platform])) {
                    $socialLinks[] = $socialData[$platform];
                }
            }
        }
        if (!empty($socialLinks)) {
            $defaultData['sameAs'] = $socialLinks;
        }
        
        // Add contact points if available
        if (!empty($defaultData['telephone'])) {
            $defaultData['contactPoint'] = [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $defaultData['telephone'],
                    'contactType' => 'customer support'
                ]
            ];
        }
        
        // Add business details if provided
        if (!empty($data['foundingDate'])) {
            $defaultData['foundingDate'] = $data['foundingDate'];
        }
        if (!empty($data['legalName'])) {
            $defaultData['legalName'] = $data['legalName'];
        }
        if (!empty($data['vatID'])) {
            $defaultData['vatID'] = $data['vatID'];
            $defaultData['taxID'] = $data['vatID'];
        }

        // Merge with provided data
        $this->setSchemaData(array_merge($defaultData, $data));
    }
} 