<?php

namespace App\Services\Posts;

use App\Models\PostsModel;
use App\Models\TermsModel;
use App\Services\Posts\PostsFieldService;

/**
 * PostsRelationshipService - Relationship Management
 * 
 * Handles all relationship operations:
 * - Terms relationships (categories, tags, etc.)
 * - Reference relationships (post-to-post)
 * - Sync logic for multilingual support
 * 
 * Reference config structure:
 * - Nested "reference" object with advanced filters and sorting
 * - selectionMode: single (save to column + relation) or multiple (relation only)
 * - bidirectional: true (sync reverse column) or false (reverse relation only)
 * 
 * @package App\Services\Posts
 */
class PostsRelationshipService
{
    /** @var TermsModel */
    protected $termsModel;

    /** @var PostsFieldService */
    protected $fieldService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->termsModel = new TermsModel();
        $this->fieldService = new PostsFieldService();
    }

    // =========================================================================
    // TERMS RELATIONSHIPS
    // =========================================================================

    /**
     * Sync terms for a post (add new, remove old)
     * 
     * @param int $postId Post ID
     * @param array $newTermIds New term IDs
     * @param array $oldTermIds Old term IDs
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return array ['added' => int, 'removed' => int]
     */
    public function syncTerms($postId, $newTermIds, $oldTermIds, $posttypeSlug, $lang, PostsModel $model)
    {
        // Calculate diff
        $toAdd = array_diff($newTermIds, $oldTermIds);
        $toRemove = array_diff($oldTermIds, $newTermIds);

        $relationTable = table_postrel($posttypeSlug);

        $added = 0;
        $removed = 0;

        // Add new terms
        foreach ($toAdd as $termId) {
            $this->addTerm($postId, $termId, $relationTable, $lang, $model);
            $added++;
        }

        // Remove old terms
        foreach ($toRemove as $termId) {
            $this->removeTerm($postId, $termId, $relationTable, $lang, $model);
            $removed++;
        }

        return [
            'added' => $added,
            'removed' => $removed
        ];
    }

    /**
     * Add term to post
     * 
     * @param int $postId Post ID
     * @param int $termId Term ID
     * @param string $relationTable Relationship table name
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return int Last insert ID
     */
    public function addTerm($postId, $termId, $relationTable, $lang, PostsModel $model)
    {
        // Check if relationship already exists
        $exists = \System\Database\DB::table($relationTable)
            ->where('post_id', $postId)
            ->where('rel_id', $termId)
            ->where('lang', $lang)
            ->exists();

        if ($exists) {
            return 0; // Already exists
        }

        // Create relationship
        return $model->createTermRelationship($relationTable, $postId, $termId, $lang);
    }

    /**
     * Remove term from post
     * 
     * @param int $postId Post ID
     * @param int $termId Term ID
     * @param string $relationTable Relationship table name
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return int Affected rows
     */
    public function removeTerm($postId, $termId, $relationTable, $lang, PostsModel $model)
    {
        return $model->deleteTermRelationship($relationTable, $postId, $termId, $lang);
    }

    /**
     * Add term with sync support (for multilingual)
     * Handles synchronous_init logic
     * 
     * @param int $postId Post ID
     * @param int $termId Term ID (or id_main)
     * @param string $posttypeSlug Posttype slug
     * @param string $currentLang Current language
     * @param array $allLanguages All available languages for this posttype
     * @param PostsModel $model Model instance
     * @param bool $sync Whether to sync to all languages
     * @return int Number of relationships created
     */
    public function addTermWithSync($postId, $termId, $posttypeSlug, $currentLang, $allLanguages, PostsModel $model, $sync = false)
    {
        $relationTable = table_postrel($posttypeSlug);
        $created = 0;

        // Get term info
        $term = $this->termsModel->getTermById($termId);

        if (empty($term)) {
            return 0;
        }

        $termIdMain = $term['id_main'] ?: $term['id'];

        if ($sync) {
            // Sync mode: Add to all languages
            foreach ($allLanguages as $lang) {
                // Find term in this language
                $langTerm = $this->TermsModel->findByIdMainLang($termIdMain, $lang);

                if (empty($langTerm)) {
                    // Term doesn't exist in this language, clone it
                    $langTermId = $this->TermsModel->cloneToLanguage($termId, $lang);
                    if ($langTermId) {
                        $this->addTerm($postId, $langTermId, $relationTable, $lang, $model);
                        $created++;
                    }
                } else {
                    // Term exists, create relationship
                    $this->addTerm($postId, $langTerm['id'], $relationTable, $lang, $model);
                    $created++;
                }
            }
        } else {
            // No sync: Add only to current language
            $this->addTerm($postId, $termId, $relationTable, $currentLang, $model);
            $created++;
        }

        return $created;
    }

    /**
     * Remove term with sync support
     * 
     * @param int $postId Post ID
     * @param int $termId Term ID
     * @param string $posttypeSlug Posttype slug
     * @param string $currentLang Current language
     * @param array $allLanguages All available languages
     * @param PostsModel $model Model instance
     * @param bool $sync Whether to sync removal to all languages
     * @return int Number of relationships removed
     */
    public function removeTermWithSync($postId, $termId, $posttypeSlug, $currentLang, $allLanguages, PostsModel $model, $sync = false)
    {
        $relationTable = table_postrel($posttypeSlug);
        $removed = 0;

        if ($sync) {
            // Sync mode: Remove from all languages
            foreach ($allLanguages as $lang) {
                $removed += $this->removeTerm($postId, $termId, $relationTable, $lang, $model);
            }
        } else {
            // No sync: Remove only from current language
            $removed = $this->removeTerm($postId, $termId, $relationTable, $currentLang, $model);
        }

        return $removed;
    }

    /**
     * Get term IDs for a post
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return array Array of term IDs
     */
    public function getPostTermIds($postId, $posttypeSlug, $lang, PostsModel $model)
    {
        $relationTable = table_postrel($posttypeSlug);
        return $model->getTermIdsByPostId($relationTable, $postId, $lang);
    }

    /**
     * Delete all terms for a post
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string|null $lang Language code (null = all languages)
     * @param PostsModel $model Model instance
     * @return int Affected rows
     */
    public function deleteAllPostTerms($postId, $posttypeSlug, $lang, PostsModel $model)
    {
        $relationTable = table_postrel($posttypeSlug);
        return $model->deleteAllTermRelationships($relationTable, $postId, $lang);
    }

    // =========================================================================
    // REFERENCE RELATIONSHIPS (Post-to-Post)
    // =========================================================================

    /**
     * Sync reference relationships (NEW structure only)
     * 
     * Reference Config Structure:
     * {
     *   "type": "Reference",
     *   "reference": {
     *     "postTypeRef": "ec_vendors",
     *     "selectionMode": "single",
     *     "bidirectional": false,
     *     "reverseField": "",
     *     "search_columns": ["title"],
     *     "display_columns": ["id", "title"],
     *     "filter": [...],
     *     "sort": [...]
     *   }
     * }
     * 
     * Storage Logic:
     * --------------
     * 
     * 1. selectionMode = "single":
     *    a. LƯU VÀO CỘT:
     *       → UPDATE fast_venues_vi SET relfield = 2
     *       → processReferenceField() returns (int) 2
     *    
     *    b. LƯU VÀO RELATION TABLE:
     *       → INSERT fast_venues_post_rel (post_id=5, rel_type='pages', rel_id=2, ...)
     *       → syncReferences() xử lý
     *    
     *    c. BIDIRECTIONAL (nếu enabled):
     *       → UPDATE fast_pages_vi SET venue_id = 5 WHERE id = 2
     *       → syncBidirectional() xử lý
     * 
     * 2. selectionMode = "multiple":
     *    a. KHÔNG LƯU VÀO CỘT:
     *       → processReferenceField() returns null
     *    
     *    b. LƯU VÀO RELATION TABLE:
     *       → INSERT fast_venues_post_rel (post_id=5, rel_type='pages', rel_id=7, ...)
     *       → INSERT fast_venues_post_rel (post_id=5, rel_type='pages', rel_id=12, ...)
     *       → syncReferences() xử lý
     * 
     * NOTE: CẢ 2 MODE ĐỀU LƯU VÀO RELATION TABLE
     * Lý do: Helpers load từ 1 nguồn duy nhất (relation table) cho chuẩn
     * 
     * Backward Compatibility:
     * -----------------------
     * OLD flat structure vẫn được hỗ trợ qua getReferenceConfig()
     * 
     * @param int $postId Post ID
     * @param array $field Reference field definition
     * @param array $newRefIds New reference post IDs
     * @param array $oldRefIds Old reference post IDs
     * @param string $posttypeSlug Current posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return array ['added' => int, 'removed' => int]
     */
    public function syncReferences($postId, $field, $newRefIds, $oldRefIds, $posttypeSlug, $lang, PostsModel $model)
    {
        // Calculate diff
        $toAdd = array_diff($newRefIds, $oldRefIds);
        $toRemove = array_diff($oldRefIds, $newRefIds);

        $added = 0;
        $removed = 0;

        // Get normalized reference config (supports NEW and OLD structure)
        $refConfig = $this->fieldService->getReferenceConfig($field);
        $referencedPosttype = $refConfig['post_type_reference'];
        
        if (empty($referencedPosttype)) {
            error_log("syncReferences: post_type_reference is empty for field " . ($field['field_name'] ?? ''));
            return ['added' => 0, 'removed' => 0];
        }

        // Get selection mode
        $selectionMode = $refConfig['selection_mode'];
        
        /**
         * LƯU VÀO RELATION TABLE (cho CẢ 2 MODE):
         * ========================================
         * 
         * SINGLE MODE:
         *   - ID đã được lưu vào CỘT (processReferenceField returned ID)
         *   - VẪN LƯU VÀO RELATION TABLE (để helpers load dễ dàng)
         * 
         * MULTIPLE MODE:
         *   - ID KHÔNG lưu vào cột (vì nhiều IDs)
         *   - CHỈ LƯU VÀO RELATION TABLE
         * 
         * Luôn lưu vào CURRENT posttype's relation table
         * Example: Venue (id=5) → Pages [7, 12]
         * Table: fast_venues_post_rel
         * Rows: 
         *   - post_id=5, rel_type='pages', rel_id=7, field_id, lang='vi'
         *   - post_id=5, rel_type='pages', rel_id=12, field_id, lang='vi'
         */
        $relationTable = table_postrel($posttypeSlug);

        // Remove old references
        foreach ($toRemove as $refId) {
            $model->deleteReferenceRelationship(
                $relationTable,
                $postId,
                $refId,
                $referencedPosttype,
                $field['id'],
                $lang
            );
            $removed++;
        }

        // Add new references
        foreach ($toAdd as $refId) {
            $model->createReferenceRelationship(
                $relationTable,
                $postId,
                $referencedPosttype,
                $field['id'],
                $refId,
                $lang
            );
            $added++;
        }

        // LUÔN LUÔN tạo reverse relation (bất kể bidirectional true/false)
        $this->syncReverseRelation(
            $postId,
            $refConfig,
            $newRefIds,
            $oldRefIds,
            $posttypeSlug,
            $lang,
            $field
        );

        return [
            'added' => $added,
            'removed' => $removed
        ];
    }

    /**
     * Sync reverse relation (LUÔN LUÔN chạy, bất kể bidirectional true/false)
     * 
     * REVERSE RELATION SYNC:
     * ======================
     * 
     * LUÔN LUÔN tạo reverse relation trong _rel table (2 chiều)
     * - Bất kể selectionMode = single hay multiple
     * - Bất kể bidirectional = true hay false
     * 
     * Bidirectional CHỈ kiểm soát việc UPDATE CỘT reverseField:
     * - bidirectional = true → Update cột reverseField
     * - bidirectional = false → KHÔNG update cột
     * 
     * Example:
     * - Posttype "posttyperong" (id=14) chọn "banglienket" (id=1)
     * 
     * Forward (syncReferences):
     *   → fast_posttyperong_post_rel: (post_id=14, rel_type='banglienket', rel_id=1, field_id=1764119393437)
     * 
     * Reverse (ở đây - LUÔN LUÔN):
     *   → fast_banglienket_post_rel: (post_id=1, rel_type='posttyperong', rel_id=14, field_id=1764119393500)
     * 
     * Nếu bidirectional=true + reverseField="lienketnguoc":
     *   → fast_banglienket_en: lienketnguoc = 14
     * 
     * @param int $postId Current post ID
     * @param array $refConfig Reference config
     * @param array $newRefIds New reference IDs
     * @param array $oldRefIds Old reference IDs
     * @param string $posttypeSlug Current posttype slug
     * @param string $lang Language code
     * @param array $field Field definition (để lấy field_id)
     * @return void
     */
    protected function syncReverseRelation($postId, $refConfig, $newRefIds, $oldRefIds, $posttypeSlug, $lang, $field)
    {
        $referencedPosttype = $refConfig['post_type_reference'];
        $reverseField = $refConfig['reverse_field'] ?? '';
        $bidirectional = $refConfig['bidirectional'] ?? false;
        
        // Cần ít nhất referencedPosttype
        if (empty($referencedPosttype)) {
            return;
        }
        
        // Note: reverseField có thể rỗng → vẫn tạo reverse relation, chỉ không update cột
        
        try {
            // Get reverse relation table
            $reverseRelationTable = table_postrel($referencedPosttype);
            
            // Get posttype config của referenced posttype để tìm field_id
            $referencedPosttypeData = posttype_db($referencedPosttype);
            
            if (empty($referencedPosttypeData)) {
                error_log("syncReverseRelation: Referenced posttype '{$referencedPosttype}' not found");
                return;
            }
            
            // Tìm field có field_name = reverseField (nếu có)
            $referencedFields = _json_decode($referencedPosttypeData['fields']);
            $reverseFieldId = 0;
            
            if (!empty($reverseField) && !empty($referencedFields) && is_array($referencedFields)) {
                foreach ($referencedFields as $refField) {
                    if (($refField['field_name'] ?? '') === $reverseField) {
                        $reverseFieldId = $refField['id'] ?? 0;
                        break;
                    }
                }
            }
            
            // Calculate diff
            $toAdd = array_diff($newRefIds, $oldRefIds);
            $toRemove = array_diff($oldRefIds, $newRefIds);
            
            /**
             * PART 1: LUÔN LUÔN tạo/xóa REVERSE RELATION
             * (Bất kể bidirectional true hay false)
             */
            
            // DELETE reverse relations
            foreach ($toRemove as $refId) {
                \System\Database\DB::table($reverseRelationTable)
                    ->where('post_id', $refId)
                    ->where('rel_type', $posttypeSlug)
                    ->where('rel_id', $postId)
                    ->where('lang', $lang)
                    ->delete();
            }
            
            // CREATE reverse relations
            foreach ($toAdd as $refId) {
                $existingReverseRel = \System\Database\DB::table($reverseRelationTable)
                    ->where('post_id', $refId)
                    ->where('rel_type', $posttypeSlug)
                    ->where('rel_id', $postId)
                    ->where('lang', $lang)
                    ->first();
                
                if (!$existingReverseRel) {
                    \System\Database\DB::table($reverseRelationTable)
                        ->insert([
                            'post_id' => $refId,
                            'rel_type' => $posttypeSlug,
                            'rel_id' => $postId,
                            'field_id' => $reverseFieldId, // Field ID của reverseField (0 nếu không tìm thấy)
                            'lang' => $lang
                        ]);
                }
            }
            
            /**
             * PART 2: Update CỘT reverseField (CHỈ KHI bidirectional=true)
             */
            if ($bidirectional && !empty($reverseField)) {
                $referencedTable = posttype_name($referencedPosttype, $lang);
                
                if (!empty($referencedTable)) {
                    // Remove old: Clear cột
                    foreach ($toRemove as $refId) {
                        \System\Database\DB::table($referencedTable)
                            ->where('id', $refId)
                            ->update([$reverseField => null]);
                    }
                    
                    // Add new: Set cột = current post ID
                    foreach ($toAdd as $refId) {
                        \System\Database\DB::table($referencedTable)
                            ->where('id', $refId)
                            ->update([$reverseField => $postId]);
                    }
                }
            }
            
        } catch (\Exception $e) {
            error_log("syncReverseRelation error: " . $e->getMessage());
        }
    }

    /**
     * Get reference post IDs for a field
     * 
     * @param int $postId Post ID
     * @param array $field Reference field definition
     * @param string $posttypeSlug Current posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return array Array of reference post IDs
     */
    public function getReferenceIds($postId, $field, $posttypeSlug, $lang, PostsModel $model)
    {
        // Get normalized reference config
        $refConfig = $this->fieldService->getReferenceConfig($field);
        $referencedPosttype = $refConfig['post_type_reference'];
        
        if (empty($referencedPosttype)) {
            return [];
        }
        
        /**
         * LUÔN QUERY TỪ RELATION TABLE (cho cả single và multiple mode)
         * 
         * Lý do:
         * - Helpers có thể load dữ liệu dễ dàng từ 1 nguồn duy nhất
         * - Không cần check xem single hay multiple
         * - Consistent data source
         * 
         * Cả 2 mode đều lưu vào CURRENT posttype's relation table
         */
        $relationTable = table_postrel($posttypeSlug);
        return $model->getReferenceIdsByPostId(
            $relationTable,
            $postId,
            $referencedPosttype,
            $field['id'],
            $lang
        );
    }

    /**
     * Delete all reference relationships for a post
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param string|null $lang Language code (null = all languages)
     * @param PostsModel $model Model instance
     * @return int Affected rows
     */
    public function deleteAllPostReferences($postId, $posttypeSlug, $lang, PostsModel $model)
    {
        $relationTable = table_postrel($posttypeSlug);
        return $model->deleteAllReferenceRelationships($relationTable, $postId, $lang);
    }

    /**
     * Process all reference fields for a post
     * 
     * IMPORTANT: $newData[$fieldName] MUST be array of IDs only!
     * Use PostsService::extractReferenceIds() to normalize before calling.
     * 
     * @param int $postId Post ID
     * @param array $fields All field definitions
     * @param array $newData New post data (field_name => [id1, id2, ...])
     * @param array $oldData Old relationships (field_name => [old_id1, old_id2, ...])
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return array Summary of changes
     */
    public function processAllReferenceFields($postId, $fields, $newData, $oldData, $posttypeSlug, $lang, PostsModel $model)
    {
        $summary = [];

        foreach ($fields as $field) {
            if ($field['type'] !== 'Reference') {
                continue;
            }

            $fieldName = $field['field_name'] ?? '';

            if (empty($fieldName)) {
                continue;
            }

            // Get new and old reference IDs
            $newRefIds = $newData[$fieldName] ?? [];
            $oldRefIds = $oldData[$fieldName] ?? [];

            // Ensure arrays
            if (!is_array($newRefIds)) {
                $newRefIds = [];
            }
            if (!is_array($oldRefIds)) {
                $oldRefIds = [];
            }

            // Sync references
            $result = $this->syncReferences(
                $postId,
                $field,
                $newRefIds,
                $oldRefIds,
                $posttypeSlug,
                $lang,
                $model
            );

            $summary[$fieldName] = $result;
        }

        return $summary;
    }

    /**
     * Load existing relationships for display
     * 
     * @param int $postId Post ID
     * @param array $fields All field definitions
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return array Existing relationships by field name
     */
    public function loadExistingRelationships($postId, $fields, $posttypeSlug, $lang, PostsModel $model)
    {
        $existing = [];

        // Load terms
        $existing['terms'] = $this->getPostTermIds($postId, $posttypeSlug, $lang, $model);

        // Load references
        foreach ($fields as $field) {
            if ($field['type'] === 'Reference') {
                $fieldName = $field['field_name'] ?? '';
                if (!empty($fieldName)) {
                    $existing[$fieldName] = $this->getReferenceIds($postId, $field, $posttypeSlug, $lang, $model);
                }
            }
        }

        return $existing;
    }
}
