<?php

/**
 * Terms Helper - Terms Management Functions (NEW ARCHITECTURE)
 * 
 * Wrapper functions that delegate to TermsService
 * Function names GIỐNG Y HỆT code cũ
 * 
 * @package App\Helpers
 */

use App\Services\Terms\TermsService;
use App\Models\TermsModel;

if (!function_exists('get_author_id')) {
    /**
     * Get current author ID from global $me_info
     * 
     * @return int|null Author ID or null
     */
    function get_author_id()
    {
        global $me_info;
        if (!empty($me_info) && !empty($me_info['id'])) {
            return (int)$me_info['id'];
        }
        return null;
    }
}

if (!function_exists('terms_find_or_create')) {
    /**
     * Find or create term for a specific language (internal helper)
     * 
     * @param int $id_main Term id_main to match
     * @param array $sourceTermInfo Source term data
     * @param string $posttype_slug Posttype slug
     * @param string $target_lang Target language
     * @param object $termsModel TermsModel instance
     * @return int|false Term ID or false on failure
     */
    function terms_find_or_create($id_main, $sourceTermInfo, $posttype_slug, $target_lang, $termsModel)
    {
        try {
            $service = new TermsService();
            return $service->findOrCreate($id_main, $sourceTermInfo, $posttype_slug, $target_lang);
        } catch (\Exception $e) {
            error_log("terms_find_or_create error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('terms_add')) {
    /**
     * Add a new term (category, tag, etc.)
     * 
     * @param array $data Term data
     * @return int|false Term ID on success, false on failure
     */
    function terms_add($data)
    {
        try {
            // Create via service
            $service = new TermsService();
            $result = $service->create($data);
            
            return $result['success'] ? $result['term_id'] : false;
        } catch (\Exception $e) {
            error_log("terms_add error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('terms_update')) {
    /**
     * Update an existing term
     * 
     * @param int $term_id Term ID
     * @param array $data Term data to update
     * @return bool Success
     */
    function terms_update($term_id, $data)
    {
        try {
            // Update via service
            $service = new TermsService();
            $result = $service->update($term_id, $data);
            
            return $result['success'];
        } catch (\Exception $e) {
            error_log("terms_update error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('terms_delete')) {
    /**
     * Delete a term
     * 
     * @param int $term_id Term ID
     * @return bool Success
     */
    function terms_delete($term_id)
    {
        try {
            // Delete via service
            $service = new TermsService();
            $result = $service->delete($term_id);
            
            return $result['success'];
        } catch (\Exception $e) {
            error_log("terms_delete error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('terms_clone_language')) {
    /**
     * Clone/create term in another language
     * 
     * @param int $source_term_id Source term ID
     * @param string $target_lang Target language code
     * @param array $overrides Data to override
     * @return int|false New term ID on success, false on failure
     */
    function terms_clone_language($source_term_id, $target_lang, $overrides = [])
    {
        try {
            // Clone via service
            $service = new TermsService();
            $result = $service->cloneToLanguage($source_term_id, $target_lang, $overrides);
            
            return $result['success'] ? $result['term_id'] : false;
        } catch (\Exception $e) {
            error_log("terms_clone_language error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('terms_clone_languages')) {
    /**
     * Clone term to multiple languages
     * 
     * @param int $source_term_id Source term ID
     * @param array $translations Translations per language
     * @return array Result with created term IDs per language
     */
    function terms_clone_languages($source_term_id, $translations)
    {
        $result = [];

        foreach ($translations as $lang => $overrides) {
            $term_id = terms_clone_language($source_term_id, $lang, $overrides);
            $result[$lang] = $term_id ?: false;
        }

        return $result;
    }
}
?>