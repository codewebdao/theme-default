<?php

/**
 * Posts Helper - Post and Term Management Functions (NEW ARCHITECTURE)
 * 
 * REPLACEMENT for system/Helpers/Posts_helper.php
 * All function names GIỐNG Y HỆT, nhưng implementation dùng Services mới
 * 
 * Functions:
 * - get_author_id()        Get current author ID
 * - terms_find_or_create() Internal helper
 * - posts_add()            Create a new post
 * - terms_add()            Create a new term
 * - posts_add_term()       Add term to post
 * - posts_remove_term()    Remove term from post
 * - posts_update()         Update post
 * - posts_update_single()  Update single field (lightweight)
 * - posts_delete()         Delete post
 * - posts_get_terms()      Get post terms
 * - terms_update()         Update term
 * - terms_delete()         Delete term
 * - terms_clone_language() Clone term to language
 * - terms_clone_languages() Clone term to multiple languages
 * - posts_clone_language() Clone post to language(s)
 * 
 * @package App\Helpers
 */

use App\Services\Posts\PostsService;
use App\Models\PostsModel;
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

if (!function_exists('posts_add')) {
    /**
     * Add a new post with relationships
     * 
     * @param string $posttype_slug Posttype slug
     * @param array $data Post data
     * @param array $options Additional options
     * @return int|false Post ID on success, false on failure
     */
    function posts_add($posttype_slug, $data, $options = [])
    {
        try {
            $lang = $data['lang'] ?? $options['lang'] ?? APP_LANG;
            
            // Create via service
            $service = new PostsService();
            $result = $service->create($posttype_slug, $data, $lang);
            
            if (!$result['success']) {
                error_log("posts_add: " . json_encode($result['errors']));
                return false;
            }
            
            $postId = $result['post_id'];

            // Handle term relationships từ options
            $terms_list = $options['terms'] ?? $data['terms'] ?? [];
            if (!empty($terms_list)) {
                foreach ($terms_list as $termIdOrData) {
                    posts_add_term($postId, $termIdOrData, $posttype_slug, $lang);
                }
            }

            return $postId;
        } catch (\Exception $e) {
            error_log("posts_add error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('posts_add_term')) {
    /**
     * Add term to post (create relationship)
     * 
     * @param int $post_id Post ID
     * @param int|array $term_id Term ID or term data array
     * @param string $posttype_slug Posttype slug
     * @param string $lang Language code
     * @return bool Success
     */
    function posts_add_term($post_id, $term_id, $posttype_slug, $lang = null)
    {
        try {
            $lang = $lang ?? APP_LANG;

            $termsModel = new TermsModel();

            // Get posttype configuration
            $postType = posttype_db($posttype_slug);
            if (empty($postType)) {
                error_log("posts_add_term: Posttype '{$posttype_slug}' not found");
                return false;
            }

            // Handle array input (find or create term)
            if (is_array($term_id)) {
                $termData = $term_id;

                if (empty($termData['type'])) {
                    error_log("posts_add_term: 'type' is required in term data array");
                    return false;
                }

                $type = $termData['type'];
                $slug = $termData['slug'] ?? (isset($termData['name']) ? url_slug($termData['name']) : '');
                
                if (empty($slug)) {
                    error_log("posts_add_term: Term 'slug' or 'name' is required in array");
                    return false;
                }

                // Step 1: Try to find existing term in current language
                $existingTerms = $termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                    $slug,
                    $posttype_slug,
                    $type,
                    $lang
                );

                if (!empty($existingTerms)) {
                    $term_id = is_array($existingTerms) ? $existingTerms[0]['id'] : $existingTerms['id'];
                } else {
                    // Step 2: Check other languages for id_main
                    $id_main_to_use = 0;

                    $allTermsWithSlug = TermsModel::query()
                        ->where('slug', $slug)
                        ->where('type', $type)
                        ->where('posttype', $posttype_slug)
                        ->get();

                    if (!empty($allTermsWithSlug)) {
                        $firstTerm = $allTermsWithSlug[0];
                        $id_main_to_use = ($firstTerm['id_main'] != 0) ? $firstTerm['id_main'] : $firstTerm['id'];
                    }

                    // Step 3: Create new term
                    $term_id = terms_add([
                        'name' => $termData['name'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug)),
                        'slug' => $slug,
                        'description' => $termData['description'] ?? '',
                        'type' => $type,
                        'posttype' => $posttype_slug,
                        'lang' => $lang,
                        'parent' => $termData['parent'] ?? null,
                        'id_main' => $termData['id_main'] ?? $id_main_to_use,
                        'seo_title' => $termData['seo_title'] ?? '',
                        'seo_desc' => $termData['seo_desc'] ?? '',
                        'status' => $termData['status'] ?? 'active'
                    ]);

                    if (!$term_id) {
                        error_log("posts_add_term: Failed to create term");
                        return false;
                    }
                }
            }

            if (!is_numeric($term_id)) {
                error_log("posts_add_term: Invalid term ID");
                return false;
            }

            // Get term info
            $termInfo = $termsModel->getTermById($term_id);
            if (empty($termInfo)) {
                error_log("posts_add_term: Term ID {$term_id} not found");
                return false;
            }

            // Decode languages
            $languages = _json_decode($postType['languages']);
            $termsConfig = _json_decode($postType['terms']);
            $tableRelation = table_postrel($posttype_slug);
            $term_id_main = $termInfo['id_main'];

            // Check if this term type should sync across languages
            $shouldSync = false;
            if (!empty($termsConfig)) {
                foreach ($termsConfig as $termConfig) {
                    if (
                        $termInfo['type'] == $termConfig['type'] &&
                        isset($termConfig['synchronous_init']) &&
                        $termConfig['synchronous_init'] === 'true'
                    ) {
                        $shouldSync = true;
                        break;
                    }
                }
            }

            if ($shouldSync) {
                // Sync to ALL languages
                $model = new PostsModel($posttype_slug, $lang);
                
                foreach ($languages as $postLang) {
                    // Find term in this language
                    $langTerms = $termsModel->getTermsByTypeAndPostTypeAndLang(
                        $posttype_slug,
                        $termInfo['type'],
                        $postLang
                    );

                    $langTermId = null;
                    foreach ($langTerms as $langTerm) {
                        if ($langTerm['id_main'] == $term_id_main) {
                            $langTermId = $langTerm['id'];
                            break;
                        }
                    }

                    if (!$langTermId) {
                        $langTermId = terms_find_or_create(
                            $term_id_main,
                            $termInfo,
                            $posttype_slug,
                            $postLang,
                            $termsModel
                        );
                    }

                    if ($langTermId) {
                        // Check if relationship exists
                        $existingRel = \System\Database\DB::table($tableRelation)
                            ->where('post_id', $post_id)
                            ->where('rel_id', $langTermId)
                            ->where('lang', $postLang)
                            ->first();

                        if (!$existingRel) {
                            $model->createTermRelationship($tableRelation, $post_id, $langTermId, $postLang);
                        }
                    }
                }
                return true;
            } else {
                // Add to current language only
                $model = new PostsModel($posttype_slug, $lang);
                
                $existingRel = \System\Database\DB::table($tableRelation)
                    ->where('post_id', $post_id)
                    ->where('rel_id', $term_id)
                    ->where('lang', $lang)
                    ->first();

                if (!$existingRel) {
                    return $model->createTermRelationship($tableRelation, $post_id, $term_id, $lang);
                }
                return true; // Already exists
            }
        } catch (\Exception $e) {
            error_log("posts_add_term error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('posts_update')) {
    /**
     * Update an existing post
     * 
     * @param int $post_id Post ID
     * @param string $posttype_slug Posttype slug
     * @param array $data Post data to update
     * @param string $lang Language code
     * @return bool Success
     */
    function posts_update($post_id, $posttype_slug, $data, $lang = null)
    {
        try {
            $lang = $lang ?? APP_LANG;
            
            // Update via service
            $service = new PostsService();
            $result = $service->update($posttype_slug, $post_id, $data, $lang);

            return $result['success'];
        } catch (\Exception $e) {
            error_log("posts_update error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('posts_update_single')) {
    /**
     * Update single field of a post (lightweight - for bulk edit)
     * 
     * ✅ OPTIMIZED: Chỉ update 1 field, không validate tất cả fields
     * ✅ DYNAMIC: Check field tồn tại trong posttype
     * ✅ AUTO-SYNC: Tự động sync nếu field là synchronous
     * ✅ AUTO-SLUG: Tự động generate slug nếu update title
     * 
     * @param int $post_id Post ID
     * @param string $posttype_slug Posttype slug
     * @param string $field_name Field name to update
     * @param mixed $value New value
     * @param string|null $lang Language code (default: APP_LANG)
     * @return array ['success' => bool, 'errors' => array, 'data' => array]
     *               - success: true/false
     *               - errors: Array of errors (empty if success)
     *               - data: Additional data (e.g., generated slug)
     */
    function posts_update_single($post_id, $posttype_slug, $field_name, $value, $lang = null)
    {
        try {
            $lang = $lang ?? APP_LANG;
            
            // Update via service
            $service = new PostsService();
            $result = $service->updateSingleField($posttype_slug, $post_id, $field_name, $value, $lang);

            return $result;
        } catch (\Exception $e) {
            error_log("posts_update_single error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['exception' => [$e->getMessage()]],
                'data' => []
            ];
        }
    }
}

if (!function_exists('posts_delete')) {
    /**
     * Delete a post
     * 
     * @param int $post_id Post ID
     * @param string $posttype_slug Posttype slug
     * @param string $lang Language code (if null, deletes from all languages)
     * @return bool Success
     */
    function posts_delete($post_id, $posttype_slug, $lang = null)
    {
        try {
            // Delete via service
            $service = new PostsService();
            $result = $service->delete($posttype_slug, $post_id, $lang);

            return $result['success'];
        } catch (\Exception $e) {
            error_log("posts_delete error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('posts_remove_term')) {
    /**
     * Remove term from post (delete relationship)
     * 
     * @param int $post_id Post ID
     * @param int|array $term_id Term ID or term data array
     * @param string $posttype_slug Posttype slug
     * @param string|null $lang Language code
     * @return bool Success
     */
    function posts_remove_term($post_id, $term_id, $posttype_slug, $lang = null)
    {
        try {
            $lang = $lang ?? APP_LANG;

            $termsModel = new TermsModel();

            // Handle array input (find term)
            if (is_array($term_id)) {
                $termData = $term_id;

                if (empty($termData['type'])) {
                    error_log("posts_remove_term: 'type' is required in term data array");
                    return false;
                }

                $type = $termData['type'];
                $slug = $termData['slug'] ?? (isset($termData['name']) ? url_slug($termData['name']) : '');
                
                if (empty($slug)) {
                    error_log("posts_remove_term: Term 'slug' or 'name' is required in array");
                    return false;
                }

                // Find existing term
                $existingTerms = $termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                    $slug,
                    $posttype_slug,
                    $type,
                    $lang
                );

                if (empty($existingTerms)) {
                    error_log("posts_remove_term: Term not found with slug '{$slug}'");
                    return false;
                }

                $term_id = is_array($existingTerms) ? $existingTerms[0]['id'] : $existingTerms['id'];
            }

            if (!is_numeric($term_id)) {
                error_log("posts_remove_term: Invalid term ID");
                return false;
            }

            // Get term info
            $termInfo = $termsModel->getTermById($term_id);
            if (empty($termInfo)) {
                error_log("posts_remove_term: Term ID {$term_id} not found");
                return false;
            }

            // Decode config
            $languages = _json_decode($postType['languages']);
            $termsConfig = _json_decode($postType['terms']);

            // Check if this term type should sync
            $shouldSync = false;
            if (!empty($termsConfig)) {
                foreach ($termsConfig as $termConfig) {
                    if (
                        $termInfo['type'] == $termConfig['type'] &&
                        isset($termConfig['synchronous_init']) &&
                        $termConfig['synchronous_init'] === 'true'
                    ) {
                        $shouldSync = true;
                        break;
                    }
                }
            }

            $tableRelation = table_postrel($posttype_slug);
            $term_id_main = $termInfo['id_main'];

            if ($shouldSync) {
                // Remove from ALL languages
                foreach ($languages as $postLang) {
                    $langTerms = $termsModel->getTermsByTypeAndPostTypeAndLang(
                        $posttype_slug,
                        $termInfo['type'],
                        $postLang
                    );

                    foreach ($langTerms as $langTerm) {
                        if ($langTerm['id_main'] == $term_id_main) {
                            \System\Database\DB::table($tableRelation)
                                ->where('post_id', $post_id)
                                ->where('rel_id', $langTerm['id'])
                                ->where('lang', $postLang)
                                ->delete();
                            break;
                        }
                    }
                }
                return true;
            } else {
                // Remove for specific language or all
                if ($lang) {
                    return \System\Database\DB::table($tableRelation)
                        ->where('post_id', $post_id)
                        ->where('rel_id', $term_id)
                        ->where('lang', $lang)
                        ->delete() > 0;
                } else {
                    $model = new PostsModel($posttype_slug, APP_LANG);
                    return $model->deleteTermRelationship($tableRelation, $post_id, $term_id);
                }
            }
        } catch (\Exception $e) {
            error_log("posts_remove_term error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('posts_get_terms')) {
    /**
     * Get all terms for a post
     * 
     * @param int $post_id Post ID
     * @param string $posttype_slug Posttype slug
     * @param string $term_type Term type
     * @param string $lang Language code
     * @return array Array of term IDs or term data
     */
    function posts_get_terms($post_id, $posttype_slug, $term_type = null, $lang = null)
    {
        try {
            $lang = $lang ?? APP_LANG;

            $postsModel = new PostsModel($posttype_slug, $lang);
            $tableRelation = table_postrel($posttype_slug);

            $terms = $postsModel->getTermIdsByPostId($tableRelation, $post_id, $lang);

            // Filter by type if specified
            if ($term_type && !empty($terms)) {
                $termsModel = new TermsModel();
                $filtered = [];

                foreach ($terms as $termId) {
                    $termData = $termsModel->getTermById($termId);
                    if ($termData && $termData['type'] == $term_type) {
                        $filtered[] = $termData;
                    }
                }

                return $filtered;
            }

            return $terms;
        } catch (\Exception $e) {
            error_log("posts_get_terms error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('posts_clone_language')) {
    /**
     * Clone/duplicate post to other languages
     * 
     * @param int $post_id Post ID to clone
     * @param string $posttype_slug Posttype slug
     * @param string|array $target_langs Target language(s)
     * @param string|null $source_lang Source language
     * @return array Result with success status and cloned languages
     */
    function posts_clone_language($post_id, $posttype_slug, $target_langs, $source_lang = null)
    {
        try {
            $source_lang = $source_lang ?? APP_LANG;
            
            // Normalize to array
            if (!is_array($target_langs)) {
                $target_langs = [$target_langs];
            }

            // Clone via service
            $service = new PostsService();
            return $service->cloneToLanguages($post_id, $posttype_slug, $target_langs, $source_lang);
        } catch (\Exception $e) {
            error_log("posts_clone_language error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
