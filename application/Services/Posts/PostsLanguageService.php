<?php

namespace App\Services\Posts;

use App\Models\PostsModel;
use App\Models\TermsModel;

/**
 * PostsLanguageService - Multilingual Operations
 * 
 * Handles all language-related operations:
 * - Clone posts to other languages
 * - Sync terms across languages
 * - Language availability checks
 * 
 * @package App\Services\Posts
 */
class PostsLanguageService
{
    /** @var PostsRelationshipService */
    protected $relationshipService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->relationshipService = new PostsRelationshipService();
    }

    /**
     * Clone post to multiple languages with synchronous fields support
     * 
     * ✅ NEW: Xử lý synchronous fields
     * - synchronous = true → Copy value từ source
     * - synchronous = false → Set NULL
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param array $targetLangs Target languages
     * @param string $sourceLang Source language
     * @param array $fields Field definitions
     * @param array $overrideData Data to override
     * @return array ['success' => bool, 'cloned' => array, 'errors' => array]
     */
    public function cloneToLanguages($postId, $posttypeSlug, $targetLangs, $sourceLang, $fields = [], $overrideData = [])
    {
        $result = [
            'success' => true,
            'cloned' => [],
            'errors' => []
        ];

        // Get source post
        $sourceModel = new PostsModel($posttypeSlug, $sourceLang);
        $sourcePost = $sourceModel->getById($postId);

        if (empty($sourcePost)) {
            $result['success'] = false;
            $result['errors'][] = "Source post not found in language: {$sourceLang}";
            return $result;
        }

        // ✅ Build synchronous fields map
        $synchronousFields = [];
        $nonSynchronousFields = [];
        
        foreach ($fields as $field) {
            $fieldName = $field['field_name'] ?? '';
            $isSynchronous = isset($field['synchronous']) && $field['synchronous'];
            
            if (empty($fieldName)) continue;
            
            if ($isSynchronous) {
                $synchronousFields[] = $fieldName;
            } else {
                $nonSynchronousFields[] = $fieldName;
            }
        }

        // Clone to each target language
        foreach ($targetLangs as $targetLang) {
            try {
                // Check if already exists
                $targetModel = new PostsModel($posttypeSlug, $targetLang);
                $exists = $targetModel->postExists($postId);

                if ($exists) {
                    $result['errors'][] = "Post already exists in language: {$targetLang}";
                    continue;
                }

                // Start with source post data
                $cloneData = $sourcePost;

                // ✅ Handle synchronous fields
                // synchronous = true → Keep value from source
                // synchronous = false → Set NULL
                foreach ($nonSynchronousFields as $fieldName) {
                    if (isset($cloneData[$fieldName])) {
                        $cloneData[$fieldName] = null;
                    }
                }

                // Apply override data (higher priority)
                $cloneData = array_merge($cloneData, $overrideData);
                if (isset($cloneData['updated_at'])){
                    $cloneData['updated_at'] = date('Y-m-d H:i:s');
                }

                // Insert to target language table
                $targetModel->insert($cloneData);

                $result['cloned'][] = $targetLang;
            } catch (\Exception $e) {
                $result['success'] = false;
                $result['errors'][] = "Failed to clone to {$targetLang}: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Clone post with relationships (terms and references)
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param array $targetLangs Target languages
     * @param string $sourceLang Source language
     * @param array $fields Field definitions
     * @param array $overrideData Data to override
     * @return array Result
     */
    public function cloneWithRelationships($postId, $posttypeSlug, $targetLangs, $sourceLang, $fields, $overrideData = [])
    {
        // First, clone the post (pass fields for synchronous handling)
        $result = $this->cloneToLanguages($postId, $posttypeSlug, $targetLangs, $sourceLang, $fields, $overrideData);

        if (!$result['success'] || empty($result['cloned'])) {
            return $result;
        }

        // Get source relationships
        $sourceModel = new PostsModel($posttypeSlug, $sourceLang);
        $sourceRelationships = $this->relationshipService->loadExistingRelationships(
            $postId,
            $fields,
            $posttypeSlug,
            $sourceLang,
            $sourceModel
        );

        // Clone relationships to each target language
        foreach ($result['cloned'] as $targetLang) {
            try {
                $this->cloneRelationships(
                    $postId,
                    $posttypeSlug,
                    $targetLang,
                    $sourceRelationships,
                    $fields
                );
            } catch (\Exception $e) {
                $result['errors'][] = "Failed to clone relationships to {$targetLang}: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Clone relationships to target language
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string $targetLang Target language
     * @param array $relationships Source relationships
     * @param array $fields Field definitions
     * @return void
     */
    protected function cloneRelationships($postId, $posttypeSlug, $targetLang, $relationships, $fields)
    {
        $targetModel = new PostsModel($posttypeSlug, $targetLang);

        // Clone terms
        if (!empty($relationships['terms'])) {
            $relationTable = table_postrel($posttypeSlug);
            $termsModel = new TermsModel();

            foreach ($relationships['terms'] as $termId) {
                // Get term info
                $term = $termsModel->getTermById($termId);
                
                if (empty($term)) {
                    continue;
                }

                $termIdMain = $term['id_main'] ?: $term['id'];

                // Find term in target language by id_main
                $targetTerms = $termsModel->getTermByIdMain($termIdMain);
                $targetTerm = null;
                
                if (!empty($targetTerms)) {
                    foreach ($targetTerms as $t) {
                        if ($t['lang'] == $targetLang) {
                            $targetTerm = $t;
                            break;
                        }
                    }
                }
                
                if (empty($targetTerm)) {
                    // Create term in target language using helper
                    $targetTermId = terms_add([
                        'name' => $term['name'],
                        'slug' => $term['slug'],
                        'type' => $term['type'],
                        'posttype' => $posttypeSlug,
                        'lang' => $targetLang,
                        'id_main' => $termIdMain,
                        'parent' => $term['parent'] ?? null,
                        'status' => $term['status'] ?? 'active'
                    ]);
                } else {
                    $targetTermId = $targetTerm['id'];
                }

                if ($targetTermId) {
                    // Create relationship
                    $this->relationshipService->addTerm(
                        $postId,
                        $targetTermId,
                        $relationTable,
                        $targetLang,
                        $targetModel
                    );
                }
            }
        }

        // Clone reference relationships
        foreach ($fields as $field) {
            if ($field['type'] !== 'Reference') {
                continue;
            }

            $fieldName = $field['field_name'] ?? '';
            
            if (empty($fieldName) || empty($relationships[$fieldName])) {
                continue;
            }

            // Get reference config (NEW structure)
            $ref = $field['reference'] ?? [];
            $postTypeRef = $ref['postTypeRef'] ?? '';
            
            if (empty($postTypeRef)) {
                continue;
            }

            // Reference relationships use same IDs across languages
            // ALWAYS save to current posttype's relation table
            $relationTable = table_postrel($posttypeSlug);
            
            foreach ($relationships[$fieldName] as $refId) {
                $targetModel->createReferenceRelationship(
                    $relationTable,
                    $postId,
                    $postTypeRef,
                    $field['id'],
                    $refId,
                    $targetLang
                );
            }
        }
    }

    /**
     * Get available languages for a post
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param array $allLanguages All available languages for posttype
     * @return array Array of language codes
     */
    public function getPostLanguages($postId, $posttypeSlug, $allLanguages = [])
    {
        $available = [];
        $languages = []; // languages supported by posttype
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            return [];
        }
        if (empty($allLanguages)) {
            $allLanguages = array_keys(APP_LANGUAGES);
        }
        foreach ($posttype['languages'] as $lang) {
            if ($lang == 'all' || in_array($lang, $allLanguages)) {
                $languages[] = $lang;
            }
        }
        foreach ($languages as $lang) {
            $model = new PostsModel($posttypeSlug, $lang);
            if ($model->postExists($postId)) {
                $available[] = $lang;
            }
        }

        return $available;
    }

    /**
     * Check if post exists in language
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @return bool
     */
    public function existsInLanguage($postId, $posttypeSlug, $lang)
    {
            $model = new PostsModel($posttypeSlug, $lang);
            return $model->postExists($postId);
    }

    /**
     * Delete post from specific language
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @return bool Success
     */
    public function deleteFromLanguage($postId, $posttypeSlug, $lang)
    {
        try {
            $model = new PostsModel($posttypeSlug, $lang);
            
            // Delete relationships first
            try{
                $this->relationshipService->deleteAllPostTerms($postId, $posttypeSlug, $lang, $model);
                $this->relationshipService->deleteAllPostReferences($postId, $posttypeSlug, $lang, $model);
            }catch(\Exception $e){
                error_log("PostsLanguageService::deleteFromLanguage error: " . $e->getMessage());
                return false;
            }
            // Delete post
            return $model->deletePost($postId) > 0;
        } catch (\Exception $e) {
            error_log("PostsLanguageService::deleteFromLanguage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete post from all languages
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param array $allLanguages All available languages
     * @return array ['success' => bool, 'deleted' => array, 'errors' => array]
     */
    public function deleteFromAllLanguages($postId, $posttypeSlug, $allLanguages)
    {
        $result = [
            'success' => true,
            'deleted' => [],
            'errors' => []
        ];

        foreach ($allLanguages as $lang) {
            try {
                if ($this->existsInLanguage($postId, $posttypeSlug, $lang)) {
                    if ($this->deleteFromLanguage($postId, $posttypeSlug, $lang)) {
                        $result['deleted'][] = $lang;
                    } else {
                        $result['errors'][] = "Failed to delete from language: {$lang}";
                    }
                }
            } catch (\Exception $e) {
                $result['success'] = false;
                $result['errors'][] = "Error deleting from {$lang}: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Sync term across all languages
     * (Used when term has synchronous_init enabled)
     * 
     * @param int $postId Post ID
     * @param int $termId Term ID or id_main
     * @param string $posttypeSlug Posttype slug
     * @param array $allLanguages All available languages
     * @return array Result
     */
    public function syncTermAcrossLanguages($postId, $termId, $posttypeSlug, $allLanguages)
    {
        $result = [
            'success' => true,
            'synced' => [],
            'errors' => []
        ];

        $TermsModel = new TermsModel();
        $term = $TermsModel->getById($termId);

        if (empty($term)) {
            $result['success'] = false;
            $result['errors'][] = "Term not found: {$termId}";
            return $result;
        }

        $termIdMain = $term['id_main'] ?: $term['id'];
        $relationTable = table_postrel($posttypeSlug);

        foreach ($allLanguages as $lang) {
            try {
                // Check if post exists in this language
                $model = new PostsModel($posttypeSlug, $lang);
                if (!$model->postExists($postId)) {
                    continue;
                }

                // Find term in this language by id_main
                $langTerms = $termsModel->getTermByIdMain($termIdMain);
                $langTerm = null;
                
                if (!empty($langTerms)) {
                    foreach ($langTerms as $t) {
                        if ($t['lang'] == $lang) {
                            $langTerm = $t;
                            break;
                        }
                    }
                }
                
                if (empty($langTerm)) {
                    // Create term using helper
                    $langTermId = terms_add([
                        'name' => $term['name'],
                        'slug' => $term['slug'],
                        'type' => $term['type'],
                        'posttype' => $posttypeSlug,
                        'lang' => $lang,
                        'id_main' => $termIdMain,
                        'parent' => $term['parent'] ?? null,
                        'status' => $term['status'] ?? 'active'
                    ]);
                } else {
                    $langTermId = $langTerm['id'];
                }

                if ($langTermId) {
                    // Add relationship
                    $model = new PostsModel($posttypeSlug, $lang);
                    $this->relationshipService->addTerm(
                        $postId,
                        $langTermId,
                        $relationTable,
                        $lang,
                        $model
                    );
                    $result['synced'][] = $lang;
                }
            } catch (\Exception $e) {
                $result['errors'][] = "Failed to sync to {$lang}: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Get post in specific language
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @return array|null Post data
     */
    public function getPostInLanguage($postId, $posttypeSlug, $lang)
    {
        $model = new PostsModel($posttypeSlug, $lang);
        return $model->getById($postId);
    }

    /**
     * Validate language code
     * 
     * @param string $lang Language code
     * @param array $allowedLanguages Allowed languages
     * @return string Valid language code (returns first allowed if invalid)
     */
    public function validateLanguage($lang, $allowedLanguages)
    {
        if (in_array($lang, $allowedLanguages)) {
            return $lang;
        }

        return $allowedLanguages[0] ?? APP_LANG;
    }
}

