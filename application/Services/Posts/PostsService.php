<?php

namespace App\Services\Posts;

use App\Models\PostsModel;
use System\Database\DB;

/**
 * PostsService - Core Post Service
 * 
 * Main service that orchestrates all post operations
 * Delegates to specialized services for:
 * - Validation (PostsValidationService)
 * - Field processing (PostsFieldService)
 * - Relationships (PostsRelationshipService)
 * - Language operations (PostsLanguageService)
 * 
 * @package App\Services\Posts
 */
class PostsService
{
    /** @var PostsValidationService */
    protected $validationService;

    /** @var PostsFieldService */
    protected $fieldService;

    /** @var PostsRelationshipService */
    protected $relationshipService;

    /** @var PostsLanguageService */
    protected $languageService;

    /**
     * Constructor - Initialize all services
     */
    public function __construct()
    {
        $this->validationService = new PostsValidationService();
        $this->fieldService = new PostsFieldService();
        $this->relationshipService = new PostsRelationshipService();
        $this->languageService = new PostsLanguageService();
    }

    // =========================================================================
    // MAIN CRUD OPERATIONS
    // =========================================================================

    /**
     * Create new post
     * 
     * @param string $posttypeSlug Posttype slug
     * @param array $data Post data
     * @param string $lang Language code
     * @return array ['success' => bool, 'post_id' => int|null, 'errors' => array]
     */
    public function create($posttypeSlug, $data, $lang)
    {
        try {
            // Get posttype configuration
            $model = new PostsModel($posttypeSlug, $lang);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [
                    'success' => false,
                    'post_id' => null,
                    'errors' => ['posttype' => ['Posttype not found']]
                ];
            }

            // Decode posttype data
            $fields = _json_decode($posttype['fields']);
            $languages = _json_decode($posttype['languages']);

            // Validate language
            $lang = $this->languageService->validateLanguage($lang, $languages);

            // Get next ID
            $currentId = $posttype['current_id'] ?? 0;
            $newId = $currentId + 1;

            // Process special fields (pass fields để check field tồn tại)
            $data = array_merge(['id' => $newId], $data);
            //Anti XSS at title, description, seo_title, seo_desc, .v.v.
            if (isset($data['title'])) {
                $data['title'] = xss_clean(strip_tags($data['title']));
            }
            if (isset($data['description'])) {
                $data['description'] = xss_clean(strip_tags($data['description']));
            }
            if (isset($data['seo_title'])) {
                $data['seo_title'] = xss_clean(strip_tags($data['seo_title']));
            }
            if (isset($data['seo_desc'])) {
                $data['seo_desc'] = xss_clean(strip_tags($data['seo_desc']));
            }
            $specialFields = $this->fieldService->processSpecialFields($data, [], $fields);

            // ✅ VALIDATE TỔNG HỢP: 4 Layers (Library + Required + Unique + Complex)
            $isDraft = ($data['status'] ?? 'draft') === 'draft';
            if (array_key_exists('create_draft', $data)) {
                $isDraft = true;
                unset($data['create_draft']);
            }
            $errors = $this->validationService->validate(
                $fields,
                $data,
                $model,      // For unique check
                null,        // No exclude ID (creating new)
                $isDraft     // Skip required if draft
            );

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'post_id' => null,
                    'errors' => $errors
                ];
            }

            // Generate unique slug
            if (!empty($specialFields['slug'])) {
                $specialFields['slug'] = $model->generateUniqueSlug($specialFields['slug']);
            }

            // Process custom fields (pass context for Variations, etc.)
            $context = [
                'post_id' => null, // Chưa có ID (đang create)
                'posttype_slug' => $posttypeSlug,
                'lang' => $lang
            ];
            $customFields = $this->fieldService->processFields($fields, $data, [], $context);

            // Merge all data
            $insertData = array_merge($specialFields, $customFields);

            // ✅ WRAP IN TRANSACTION to ensure data consistency
            $postId = null;

            DB::beginTransaction();

            try {
                // Insert post
                $postId = $model->insert($insertData);

                if (!$postId) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'post_id' => null,
                        'errors' => ['database' => ['Failed to insert post']]
                    ];
                }

                // ✅ FIX: Update posttype current_id = postId (KHÔNG +1 vì postId đã là currentId+1 rồi)
                $model->updatePosttype($posttype['id'], ['current_id' => $postId]);

                // Handle relationships (terms and references)
                $this->handleRelationshipsForCreate($postId, $fields, $data, $posttypeSlug, $lang, $model);

                // ✅ Update lang_slug column (chỉ khi bài public) for FE 301 by lang
                $isPublic = ($insertData['status'] ?? '') === 'active';
                if ($isPublic && !empty($insertData['slug'])) {
                    $this->handleLangSlugColumn($posttype, $postId, $lang, $insertData['slug'], []);
                }

                // Commit transaction
                DB::commit();

                // Fire event (after successful commit)
                if (class_exists('\System\Libraries\Events')) {
                    \System\Libraries\Events::run('Backend\\PostsAddEvent', $insertData);
                }

                return [
                    'success' => true,
                    'post_id' => $postId,
                    'errors' => []
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                error_log("PostsService::create transaction error: " . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            error_log("PostsService::create error: " . $e->getMessage());
            return [
                'success' => false,
                'post_id' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Update existing post
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @param array $data Post data
     * @param string $lang Language code
     * @return array ['success' => bool, 'errors' => array]
     */
    public function update($posttypeSlug, $postId, $data, $lang)
    {
        try {
            // Get posttype and post
            $model = new PostsModel($posttypeSlug, $lang);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [
                    'success' => false,
                    'errors' => ['posttype' => ['Posttype not found']]
                ];
            }

            // Get existing post
            $existingPost = $model->getById($postId);

            if (empty($existingPost)) {
                return [
                    'success' => false,
                    'errors' => ['post' => ['Post not found']]
                ];
            }

            // Decode posttype data
            $fields = _json_decode($posttype['fields']);
            $languages = _json_decode($posttype['languages']);

            //Anti XSS at title, description, seo_title, seo_desc, .v.v.
            if (isset($data['title'])) {
                $data['title'] = xss_clean(strip_tags($data['title']));
            }
            if (isset($data['description'])) {
                $data['description'] = xss_clean(strip_tags($data['description']));
            }
            if (isset($data['seo_title'])) {
                $data['seo_title'] = xss_clean(strip_tags($data['seo_title']));
            }
            if (isset($data['seo_desc'])) {
                $data['seo_desc'] = xss_clean(strip_tags($data['seo_desc']));
            }

            // Process special fields (pass fields để check field tồn tại)
            $specialFields = $this->fieldService->processSpecialFields($data, $existingPost, $fields);

            // ✅ VALIDATE TỔNG HỢP: 4 Layers (Library + Required + Unique + Complex)
            $isDraft = ($data['status'] ?? $existingPost['status'] ?? '') === 'draft';

            $errors = $this->validationService->validate(
                $fields,
                $data,
                $model,       // For unique check
                $postId,      // Exclude current post ID
                $isDraft      // Skip required if draft
            );

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }

            // Generate unique slug if changed (chỉ nếu slug field tồn tại)
            $slugFieldExists = false;
            foreach ($fields as $field) {
                if (($field['field_name'] ?? '') === 'slug') {
                    $slugFieldExists = true;
                    break;
                }
            }

            if ($slugFieldExists && !empty($specialFields['slug'])) {
                // Check if slug changed
                $existingSlug = $existingPost['slug'] ?? '';
                if ($specialFields['slug'] !== $existingSlug) {
                    $specialFields['slug'] = $model->generateUniqueSlug($specialFields['slug'], $postId);
                }
            }

            // Process custom fields (pass context for Variations, etc.)
            $context = [
                'post_id' => $postId,
                'posttype_slug' => $posttypeSlug,
                'lang' => $lang
            ];
            $customFields = $this->fieldService->processFields($fields, $data, $existingPost, $context);

            // Merge all data
            $updateData = array_merge($specialFields, $customFields);

            // ✅ WRAP IN TRANSACTION to ensure data consistency
            DB::beginTransaction();

            try {
                // Update post in current language
                $affected = $model->updatePost($postId, $updateData);

                // ✅ NEW: Sync synchronous fields to other languages
                $this->syncSynchronousFieldsToLanguages($postId, $posttypeSlug, $updateData, $fields, $lang, $languages);

                // Handle relationships (terms and references)
                $this->handleRelationshipsForUpdate($postId, $fields, $data, $existingPost, $posttypeSlug, $lang, $model);

                // ✅ Update lang_slug: chỉ public (active) mới có slug trong map; draft → bỏ lang khỏi map
                $effectiveStatus = $updateData['status'] ?? $existingPost['status'] ?? '';
                $newSlugForMap = ($effectiveStatus === 'active') ? ($updateData['slug'] ?? $existingPost['slug'] ?? '') : '';
                $currentLangSlug = isset($existingPost['lang_slug'])
                    ? (is_array($existingPost['lang_slug']) ? $existingPost['lang_slug'] : _json_decode($existingPost['lang_slug']))
                    : [];
                $this->handleLangSlugColumn($posttype, $postId, $lang, $newSlugForMap, is_array($currentLangSlug) ? $currentLangSlug : []);

                // Commit transaction
                DB::commit();

                // Fire event (after successful commit)
                if (class_exists('\System\Libraries\Events')) {
                    \System\Libraries\Events::run('Backend\\PostsEditEvent', $updateData);
                }

                return [
                    'success' => true,
                    'errors' => []
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                error_log("PostsService::update transaction error: " . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            error_log("PostsService::update error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Update single field (lightweight - for bulk edit)
     * 
     * ✅ OPTIMIZED: Chỉ update 1 field, không validate tất cả fields
     * ✅ DYNAMIC: Check field tồn tại trong posttype
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @param string $fieldName Field name to update
     * @param mixed $value New value
     * @param string $lang Language code
     * @return array ['success' => bool, 'errors' => array, 'data' => array]
     */
    public function updateSingleField($posttypeSlug, $postId, $fieldName, $value, $lang)
    {
        try {
            // Get posttype and post
            $model = new PostsModel($posttypeSlug, $lang);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [
                    'success' => false,
                    'errors' => ['posttype' => ['Posttype not found']]
                ];
            }

            // Check if post exists
            $existingPost = $model->getById($postId);
            if (empty($existingPost)) {
                return [
                    'success' => false,
                    'errors' => ['post' => ['Post not found']]
                ];
            }

            // Decode fields
            $fields = _json_decode($posttype['fields']);
            $languages = _json_decode($posttype['languages']);

            // ✅ Find field definition
            $fieldDef = null;
            foreach ($fields as $field) {
                if (($field['field_name'] ?? '') === $fieldName) {
                    $fieldDef = $field;
                    break;
                }
            }

            if (!$fieldDef) {
                return [
                    'success' => false,
                    'errors' => [$fieldName => ['Field not found in posttype']]
                ];
            }

            // ✅ Pre-process value for special field types (from bulk edit dropdowns)
            $fieldType = $fieldDef['type'] ?? 'Text';

            // Extract ID from dropdown format "ID: Name"
            if (in_array($fieldType, ['User', 'Reference']) && is_string($value) && strpos($value, ':') !== false) {
                $parts = explode(':', $value, 2);
                $value = trim($parts[0]);  // Get ID part
            }

            if (in_array($fieldName, ['title', 'description', 'seo_title', 'seo_desc'])) {
                $value = xss_clean(strip_tags($value));
            }

            // Process field value
            $processedValue = $this->fieldService->processFieldValue($fieldDef, $value);

            // Build update data
            $updateData = [
                $fieldName => $processedValue
            ];

            if (isset($existingPost['updated_at'])) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
            }

            // Update post
            $affected = $model->updatePost($postId, $updateData);

            // Sync if field is synchronous
            $isSynchronous = isset($fieldDef['synchronous']) && $fieldDef['synchronous'];
            if ($isSynchronous) {
                $this->syncSynchronousFieldsToLanguages($postId, $posttypeSlug, $updateData, $fields, $lang, $languages);
            }

            // ✅ Update lang_slug: chỉ khi bài public (active); draft → bỏ lang khỏi map
            if ($fieldName === 'slug') {
                $newSlugForMap = ($existingPost['status'] ?? '') === 'active' ? $processedValue : '';
                $currentLangSlug = isset($existingPost['lang_slug'])
                    ? (is_array($existingPost['lang_slug']) ? $existingPost['lang_slug'] : _json_decode($existingPost['lang_slug']))
                    : [];
                $this->handleLangSlugColumn($posttype, $postId, $lang, $newSlugForMap, is_array($currentLangSlug) ? $currentLangSlug : []);
            }

            // ✅ Khi đổi status (draft ↔ active): refresh lang_slug để ẩn/hiện lang
            if ($fieldName === 'status') {
                $newSlugForMap = ($processedValue === 'active') ? ($existingPost['slug'] ?? '') : '';
                $currentLangSlug = isset($existingPost['lang_slug'])
                    ? (is_array($existingPost['lang_slug']) ? $existingPost['lang_slug'] : _json_decode($existingPost['lang_slug']))
                    : [];
                $this->handleLangSlugColumn($posttype, $postId, $lang, $newSlugForMap, is_array($currentLangSlug) ? $currentLangSlug : []);
            }

            // Auto-generate slug if title updated
            $responseData = [];
            if ($fieldName === 'title' && !empty($processedValue)) {
                // Check if slug field exists
                $hasSlugField = false;
                foreach ($fields as $field) {
                    if (($field['field_name'] ?? '') === 'slug') {
                        $hasSlugField = true;
                        break;
                    }
                }

                // Auto-generate slug from title — chỉ đưa vào lang_slug khi bài public
                if ($hasSlugField && (empty($existingPost['slug']) || $existingPost['slug'] === url_slug($existingPost['title']))) {
                    $newSlug = $model->generateUniqueSlug(url_slug($processedValue), $postId);
                    $model->updatePost($postId, ['slug' => $newSlug]);
                    $responseData['slug'] = $newSlug;
                    if (($existingPost['status'] ?? '') === 'active') {
                        $currentLangSlug = isset($existingPost['lang_slug'])
                            ? (is_array($existingPost['lang_slug']) ? $existingPost['lang_slug'] : _json_decode($existingPost['lang_slug']))
                            : [];
                        $this->handleLangSlugColumn($posttype, $postId, $lang, $newSlug, is_array($currentLangSlug) ? $currentLangSlug : []);
                    }
                }
            }

            return [
                'success' => true,
                'errors' => [],
                'data' => $responseData
            ];
        } catch (\Exception $e) {
            error_log("PostsService::updateSingleField error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Update post status (lightweight - no full validation)
     * 
     * ✅ OPTIMIZED: Chỉ update status, không validate tất cả fields
     * ✅ DYNAMIC: Check field 'status' có tồn tại không
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @param string $status New status
     * @param string $lang Language code
     * @return array ['success' => bool, 'errors' => array]
     */
    public function updateStatus($posttypeSlug, $postData, $status, $lang)
    {
        try {
            // Get posttype
            $model = new PostsModel($posttypeSlug, $lang);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [
                    'success' => false,
                    'errors' => ['posttype' => ['Posttype not found']]
                ];
            }

            // ✅ Check if posttype has 'status' field (DYNAMIC check)
            $fields = _json_decode($posttype['fields']);
            $hasStatusField = false;
            $hasUpdatedAtField = false;

            foreach ($fields as $field) {
                if (($field['field_name'] ?? '') === 'status') {
                    $hasStatusField = true;
                    break;
                }
            }
            foreach ($fields as $field) {
                if (($field['field_name'] ?? '') === 'updated_at') {
                    $hasUpdatedAtField = true;
                    break;
                }
            }

            if (!$hasStatusField) {
                return [
                    'success' => false,
                    'errors' => ['status' => ['This posttype does not have status field']]
                ];
            }

            // Validate status value
            $validStatuses = ['active', 'pending', 'inactive', 'schedule', 'draft', 'suspended', 'deleted'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'errors' => ['status' => ['Invalid status value']]
                ];
            }

            // Check if post exists
            if (empty($postData) || empty($postData['id'])) {
                return [
                    'success' => false,
                    'errors' => ['post' => ['Post not found']]
                ];
            }

            // Update status only (lightweight)
            $updateData = [
                'status' => $status,
            ];
            if ($hasUpdatedAtField) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
            }

            $affected = $model->updatePost($postData['id'], $updateData);

            if ($affected > 0) {
                // ✅ Khi đổi status: lang_slug chỉ chứa bản public (active); draft → bỏ lang khỏi map
                $currentLangSlug = [];
                if (!empty($postData['lang_slug'])) {
                    $currentLangSlug = is_array($postData['lang_slug']) ? $postData['lang_slug'] : _json_decode($postData['lang_slug']);
                }
                $newSlugForMap = ($status === 'active') ? ($postData['slug'] ?? '') : '';
                $this->handleLangSlugColumn($posttype, $postData['id'], $lang, $newSlugForMap, is_array($currentLangSlug) ? $currentLangSlug : []);

                return [
                    'success' => true,
                    'errors' => []
                ];
            }

            return [
                'success' => false,
                'errors' => ['database' => ['Failed to update status']]
            ];
        } catch (\Exception $e) {
            error_log("PostsService::updateStatus error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Delete post
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @param string|null $lang Language code (null = all languages)
     * @return array ['success' => bool, 'deleted' => array, 'errors' => array]
     */
    public function delete($posttypeSlug, $postId, $lang = null)
    {
        try {
            $model = new PostsModel($posttypeSlug, $lang ?? APP_LANG);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [
                    'success' => false,
                    'deleted' => [],
                    'errors' => ['posttype' => ['Posttype not found']]
                ];
            }

            $languages = _json_decode($posttype['languages']);

            if ($lang === null) {
                // Delete from all languages
                return $this->languageService->deleteFromAllLanguages($postId, $posttypeSlug, $languages);
            } else {
                // Delete from specific language: get current lang_slug before delete, then update other lang tables
                $existingPost = $model->getById($postId, ['lang_slug']);
                $currentLangSlug = [];
                if (!empty($existingPost['lang_slug'])) {
                    $currentLangSlug = is_array($existingPost['lang_slug']) ? $existingPost['lang_slug'] : _json_decode($existingPost['lang_slug']);
                }

                $success = $this->languageService->deleteFromLanguage($postId, $posttypeSlug, $lang);

                // ✅ Update lang_slug in remaining lang tables (remove this lang from map)
                if ($success && !empty($posttype)) {
                    $this->handleLangSlugColumn($posttype, $postId, $lang, '', is_array($currentLangSlug) ? $currentLangSlug : []);
                }

                return [
                    'success' => $success,
                    'deleted' => $success ? [$lang] : [],
                    'errors' => $success ? [] : ['database' => ['Failed to delete post']]
                ];
            }
        } catch (\Exception $e) {
            error_log("PostsService::delete error: " . $e->getMessage());
            return [
                'success' => false,
                'deleted' => [],
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Get post by ID
     * 
     * ✅ OPTIMIZED: Use get_post() helper (auto-handles Point fields khi truyền fields)
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @param string $lang Language code
     * @param array $options Options ['fields' => array, 'with_terms' => bool, ...]
     * @return array|null Post data
     */
    public function get($posttypeSlug, $postId, $lang, $options = [])
    {
        try {
            // ✅ Pass fields parameter để helper xử lý Point fields tự động
            $args = array_merge([
                'id' => $postId,
                'post_type' => $posttypeSlug,
                'lang' => $lang,
                'post_status' => '', // Get all statuses
                'fields' => '*' // Default select all
            ], $options);

            $post = get_post($args);

            if (empty($post)) {
                return null;
            }

            // Parse fields for display nếu cần
            if (!empty($options['parse_fields'])) {
                $model = new PostsModel($posttypeSlug, $lang);
                $posttype = posttype_db($posttypeSlug);

                if (!empty($posttype)) {
                    $fields = _json_decode($posttype['fields']);
                    $post = $this->fieldService->parseFieldsForDisplay($fields, $post);
                }
            }
            return $post;
        } catch (\Exception $e) {
            error_log("PostsService::get error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get posts list with filters and pagination
     * 
     * ✅ OPTIMIZED: Use get_posts() helper từ Query_helper.php
     * 
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param array $filters Filters
     * @param array $options Options
     * @return array Paginated result
     */
    public function getList($posttypeSlug, $lang, $filters = [], $options = [])
    {
        try {
            // ✅ Use get_posts() helper (có eager loading, tối ưu hơn)
            $args = [
                'post_type' => $posttypeSlug,
                'lang' => $lang,
                'post_status' => $filters['post_status'] ?? '',
                'posts_per_page' => $options['limit'] ?? 15,
                'paged' => $options['page'] ?? 1,
                'orderby' => $options['order_by'] ?? 'id',
                'order' => $options['order'] ?? 'DESC',
                'search' => $filters['search'] ?? '',
                'search_columns' => $options['search_fields'] ?? ['title', 'search_string']
            ];
            //add filters to args
            if (isset($filters['filters']) && !empty($filters['filters'])) {
                $args['filters'] = $filters['filters'];
            }
            //add //with_categories, with_tags, with_author, with_comments if have set
            if (isset($options['with_categories']) && $options['with_categories']) {
                $args['with_categories'] = true;
            }
            if (isset($options['with_tags']) && $options['with_tags']) {
                $args['with_tags'] = true;
            }
            if (isset($options['with_author']) && $options['with_author']) {
                $args['with_author'] = true;
            }
            if (isset($options['with_comments']) && $options['with_comments']) {
                $args['with_comments'] = true;
            }
            //add with_terms if have set
            if (isset($options['with_terms']) && $options['with_terms']) {
                $args['with_terms'] = true;
            }

            return get_posts($args);
        } catch (\Exception $e) {
            error_log("PostsService::getList error: " . $e->getMessage());
            return [
                'data' => [],
                'is_next' => false,
                'page' => 1
            ];
        }
    }

    /**
     * Generate unique slug
     * 
     * @param string $posttypeSlug Posttype slug
     * @param string $slug Desired slug
     * @param string $lang Language code
     * @param int|null $excludeId Exclude ID
     * @return string Unique slug
     */
    public function generateUniqueSlug($posttypeSlug, $slug, $lang, $excludeId = null)
    {
        try {
            $model = new PostsModel($posttypeSlug, $lang);
            return $model->generateUniqueSlug($slug, $excludeId);
        } catch (\Exception $e) {
            error_log("PostsService::generateUniqueSlug error: " . $e->getMessage());
            return $slug;
        }
    }

    /**
     * Sync synchronous fields to other languages
     * 
     * ✅ NEW: Khi update post, tự động sync fields có synchronous=true sang các languages khác
     * 
     * @param int $postId Post ID
     * @param string $posttypeSlug Posttype slug
     * @param array $updateData Data đã update
     * @param array $fields Field definitions
     * @param string $currentLang Current language đang edit
     * @param array $allLanguages All posttype languages
     * @return void
     */
    protected function syncSynchronousFieldsToLanguages($postId, $posttypeSlug, $updateData, $fields, $currentLang, $allLanguages)
    {
        try {
            // Build list of synchronous fields
            $syncFields = [];
            $hasUpdatedAtField = false;
            foreach ($fields as $field) {
                if (($field['field_name'] ?? '') === 'updated_at') {
                    $hasUpdatedAtField = true;
                    break;
                }
            }
            foreach ($fields as $field) {
                $fieldName = $field['field_name'] ?? '';
                $isSynchronous = isset($field['synchronous']) && $field['synchronous'];

                if (empty($fieldName)) continue;

                // Check if field is in updateData AND is synchronous
                if ($isSynchronous && array_key_exists($fieldName, $updateData)) {
                    $syncFields[$fieldName] = $updateData[$fieldName];
                }
            }

            // If no synchronous fields changed, skip
            if (empty($syncFields)) {
                return;
            }

            // Sync to other languages
            foreach ($allLanguages as $lang) {
                // Skip current language (already updated)
                if ($lang === $currentLang) {
                    continue;
                }

                // Check if post exists in this language
                $langModel = new PostsModel($posttypeSlug, $lang);
                if (!$langModel->postExists($postId)) {
                    continue;
                }

                // Update synchronous fields only
                $syncData = $syncFields;
                if ($hasUpdatedAtField) {
                    $syncData['updated_at'] = date('Y-m-d H:i:s');
                }

                $langModel->updatePost($postId, $syncData);
            }
        } catch (\Exception $e) {
            error_log("syncSynchronousFieldsToLanguages error: " . $e->getMessage());
            // Don't throw - sync is optional
        }
    }

    // =========================================================================
    // RELATIONSHIP HANDLERS
    // =========================================================================

    /**
     * Handle relationships when creating post
     * 
     * @param int $postId Post ID
     * @param array $fields Field definitions
     * @param array $data Input data
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return void
     */
    protected function handleRelationshipsForCreate($postId, $fields, $data, $posttypeSlug, $lang, PostsModel $model)
    {
        // Handle terms
        if (!empty($data['terms'])) {
            $termIds = is_array($data['terms']) ? $data['terms'] : json_decode($data['terms'], true);

            if (is_array($termIds)) {
                $relationTable = table_postrel($posttypeSlug);
                foreach ($termIds as $termId) {
                    $this->relationshipService->addTerm($postId, $termId, $relationTable, $lang, $model);
                }
            }
        }

        // Handle references
        foreach ($fields as $field) {
            if ($field['type'] !== 'Reference') {
                continue;
            }

            $fieldName = $field['field_name'] ?? '';

            if (empty($fieldName) || !isset($data[$fieldName])) {
                continue;
            }

            // Extract reference IDs từ various formats
            $refIds = $this->extractReferenceIds($data[$fieldName]);

            if (empty($refIds)) {
                continue;
            }

            $this->relationshipService->syncReferences(
                $postId,
                $field,
                $refIds,
                [],
                $posttypeSlug,
                $lang,
                $model
            );
        }
    }

    /**
     * Handle relationships when updating post
     * 
     * @param int $postId Post ID
     * @param array $fields Field definitions
     * @param array $newData New data
     * @param array $oldData Old data
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param PostsModel $model Model instance
     * @return void
     */
    protected function handleRelationshipsForUpdate($postId, $fields, $newData, $oldData, $posttypeSlug, $lang, PostsModel $model)
    {
        // Load existing relationships
        $existingRelationships = $this->relationshipService->loadExistingRelationships(
            $postId,
            $fields,
            $posttypeSlug,
            $lang,
            $model
        );

        // Handle terms
        if (isset($newData['terms'])) {
            $newTermIds = is_array($newData['terms']) ? $newData['terms'] : json_decode($newData['terms'], true);
            $oldTermIds = $existingRelationships['terms'] ?? [];

            if (is_array($newTermIds)) {
                $this->relationshipService->syncTerms(
                    $postId,
                    $newTermIds,
                    $oldTermIds,
                    $posttypeSlug,
                    $lang,
                    $model
                );
            }
        }

        // Handle references - Extract IDs from POST data first
        $normalizedNewData = [];
        foreach ($fields as $field) {
            if ($field['type'] === 'Reference') {
                $fieldName = $field['field_name'] ?? '';
                if (!empty($fieldName) && isset($newData[$fieldName])) {
                    $normalizedNewData[$fieldName] = $this->extractReferenceIds($newData[$fieldName]);
                }
            }
        }

        $this->relationshipService->processAllReferenceFields(
            $postId,
            $fields,
            $normalizedNewData,
            $existingRelationships,
            $posttypeSlug,
            $lang,
            $model
        );
    }

    // =========================================================================
    // LANGUAGE OPERATIONS
    // =========================================================================

    /**
     * Clone post to other languages
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @param array $targetLangs Target languages
     * @param string $sourceLang Source language
     * @param array $overrideData Data to override
     * @return array Result
     */
    public function cloneToLanguages($postId, $posttypeSlug, $targetLangs, $sourceLang, $overrideData = [])
    {
        try {
            $model = new PostsModel($posttypeSlug, $sourceLang);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [
                    'success' => false,
                    'cloned' => [],
                    'errors' => ['posttype' => ['Posttype not found']]
                ];
            }

            $fields = _json_decode($posttype['fields']);

            $result = $this->languageService->cloneWithRelationships(
                $postId,
                $posttypeSlug,
                $targetLangs,
                $sourceLang,
                $fields,
                $overrideData
            );

            // ✅ After clone: refresh lang_slug — chỉ đưa bản có status active vào map
            if ($result['success'] && !empty($result['cloned'])) {
                foreach ($result['cloned'] as $clonedLang) {
                    $langModel = new PostsModel($posttypeSlug, $clonedLang);
                    $p = $langModel->getById($postId, ['slug', 'status']);
                    $newSlugForMap = (!empty($p['slug']) && (!isset($p['status']) || $p['status'] === 'active')) ? $p['slug'] : '';
                    $this->handleLangSlugColumn($posttype, $postId, $clonedLang, $newSlugForMap, []);
                }
            }

            return $result;
        } catch (\Exception $e) {
            error_log("PostsService::cloneToLanguages error: " . $e->getMessage());
            return [
                'success' => false,
                'cloned' => [],
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Get available languages for a post
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int $postId Post ID
     * @return array Array of language codes
     */
    public function getPostLanguages($posttypeSlug, $postId)
    {
        try {
            $model = new PostsModel($posttypeSlug, APP_LANG);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return [];
            }

            $languages = _json_decode($posttype['languages']);

            return $this->languageService->getPostLanguages($postId, $posttypeSlug, $languages);
        } catch (\Exception $e) {
            error_log("PostsService::getPostLanguages error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available languages for multiple posts (BATCH - optimized)
     * 
     * ✅ OPTIMIZED: 1 query per language instead of N queries (N = number of posts)
     * Example: 100 posts, 3 languages → 3 queries (not 100 queries!)
     * 
     * @param string $posttypeSlug Posttype slug
     * @param array $postIds Array of post IDs
     * @param array $languages All posttype languages
     * @param string $currentLang Current language
     * @return array Map [post_id => [lang1, lang2, ...]]
     */
    public function getBatchPostLanguages($posttypeSlug, $postIds, $languages, $currentLang)
    {
        try {
            if (empty($postIds) || empty($languages)) {
                return [];
            }

            // Initialize result: All posts have current language
            $result = [];
            foreach ($postIds as $postId) {
                $result[$postId] = [$currentLang];
            }

            // Query each language ONCE (batch query)
            foreach ($languages as $lang) {
                // Skip current language (already added)
                if ($lang === $currentLang) {
                    continue;
                }

                // ✅ BATCH QUERY: Get all posts in this language with WHERE id IN (...)
                $model = new PostsModel($posttypeSlug, $lang);
                $postsInLang = $model->getPostsByIds($postIds, ['id']); // Only need ID

                // Add language to posts that exist
                if (!empty($postsInLang)) {
                    foreach ($postsInLang as $post) {
                        $result[$post['id']][] = $lang;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            error_log("PostsService::getBatchPostLanguages error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count posts with filters
     * 
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @param array $filters Filters
     * @return int Count
     */
    public function countPosts($posttypeSlug, $lang, $filters = [])
    {
        try {
            $args = [
                'post_type' => $posttypeSlug,
                'lang' => $lang,
                'post_status' => $filters['post_status'] ?? '',
                'search' => $filters['search'] ?? '',
                'posts_per_page' => -1  // All
            ];

            $result = get_posts($args);
            return count($result['data'] ?? []);
        } catch (\Exception $e) {
            error_log("PostsService::countPosts error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Import batch of posts from JSON
     * 
     * @param array $batch Batch data
     * @param array $columnMapping Column mapping
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @return array Result
     */
    public function importBatch($batch, $columnMapping, $posttypeSlug, $lang)
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        try {
            $model = new PostsModel($posttypeSlug, $lang);
            $posttype = posttype_db($posttypeSlug);

            if (empty($posttype)) {
                return ['success' => false, 'message' => 'Posttype not found'];
            }

            $fields = _json_decode($posttype['fields']);

            // Build fields map for type checking
            $fieldsMap = [];
            foreach ($fields as $field) {
                $fieldsMap[$field['field_name'] ?? ''] = $field;
            }

            foreach ($batch as $rowIndex => $row) {
                try {
                    // Map JSON row -> post data
                    $postData = [];
                    $termsData = [];

                    foreach ($columnMapping as $posttypeField => $jsonColumn) {
                        if (empty($jsonColumn)) continue;

                        if (isset($row[$jsonColumn])) {
                            // Check if term field
                            if (strpos($posttypeField, 'terms:') === 0) {
                                $termType = substr($posttypeField, 6);
                                $termsData[$termType] = $row[$jsonColumn];
                            } else {
                                $value = $row[$jsonColumn];

                                // ✅ JSON encode array/object fields
                                if (is_array($value) || is_object($value)) {
                                    $value = json_encode($value);
                                }

                                $postData[$posttypeField] = $value;
                            }
                        }
                    }

                    // Check if post exists
                    $existingPost = null;
                    if (!empty($postData['id'])) {
                        $existingPost = $model->getById($postData['id']);
                    } elseif (!empty($postData['slug'])) {
                        $existingPost = $model->getBySlug($postData['slug']);
                    }

                    // Generate slug if missing
                    if (empty($postData['slug']) && !empty($postData['title'])) {
                        $postData['slug'] = url_slug($postData['title']);
                    }

                    if ($existingPost) {
                        // UPDATE
                        $result = $this->update($posttypeSlug, $existingPost['id'], $postData, $lang);

                        // Update terms if provided
                        if ($result['success'] && !empty($termsData)) {
                            $this->importTermsForPost($existingPost['id'], $termsData, $posttypeSlug, $lang);
                        }

                        if ($result['success']) {
                            $updated++;
                        } else {
                            $errors[] = "Row " . ($rowIndex + 1) . ": " . json_encode($result['errors']);
                        }
                    } else {
                        // CREATE
                        $result = $this->create($posttypeSlug, $postData, $lang);

                        // Add terms if provided
                        if ($result['success'] && !empty($termsData)) {
                            $this->importTermsForPost($result['post_id'], $termsData, $posttypeSlug, $lang);
                        }

                        if ($result['success']) {
                            $imported++;
                        } else {
                            $errors[] = "Row " . ($rowIndex + 1) . ": " . json_encode($result['errors']);
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": " . $e->getMessage();
                }
            }

            return [
                'success' => true,
                'data' => [
                    'imported' => $imported,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'errors' => $errors
                ],
                'message' => "{$imported} imported, {$updated} updated, {$skipped} skipped"
            ];
        } catch (\Exception $e) {
            error_log("PostsService::importBatch error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Import terms for post from term slugs (helper for importBatch)
     * 
     * @param int $postId Post ID
     * @param array $termsData Terms data [type => 'slug1,slug2']
     * @param string $posttypeSlug Posttype slug
     * @param string $lang Language code
     * @return void
     */
    protected function importTermsForPost($postId, $termsData, $posttypeSlug, $lang)
    {
        if (empty($termsData) || !is_array($termsData)) {
            return;
        }

        $termsModel = new \App\Models\TermsModel();

        foreach ($termsData as $termType => $termSlugsString) {
            if (empty($termSlugsString)) continue;

            // Parse comma-separated slugs
            $termSlugs = array_map('trim', explode(',', $termSlugsString));

            foreach ($termSlugs as $termSlug) {
                if (empty($termSlug)) continue;

                // Find term by slug
                $terms = $termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                    $termSlug,
                    $posttypeSlug,
                    $termType,
                    $lang
                );

                if (!empty($terms)) {
                    $termId = is_array($terms) && isset($terms[0]) ? $terms[0]['id'] : ($terms['id'] ?? null);

                    if ($termId) {
                        posts_add_term($postId, $termId, $posttypeSlug, $lang);
                    }
                }
            }
        }
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Load form data for edit/add pages
     * 
     * @param string $posttypeSlug Posttype slug
     * @param int|null $postId Post ID (null for add)
     * @param string $lang Language code
     * @return array Form data
     */
    public function loadFormData($posttypeSlug, $postId, $lang = APP_LANG)
    {
        try {
            $model = new PostsModel($posttypeSlug, $lang);
            $posttype = posttype_db($posttypeSlug);
            if (empty($posttype)) {
                return null;
            }
            $queryFields = $posttype['fields'];

            // ✅ Load Term inputs (add Taxonomy fields cho categories, tags)
            $posttype = $this->loadTermInputs($posttype);

            $formData = [
                'posttype' => $posttype,
                'post' => null
            ];

            // Load post if editing
            if ($postId) {
                // ✅ Pass fields để helper xử lý Point fields với ST_AsText()
                $post = $this->get($posttypeSlug, $postId, $lang, [
                    'fields' => $queryFields, // ← Array of field definitions
                    //'with_terms' => true,
                ]);

                if (!empty($post)) {
                    // Load existing relationships
                    $termIds = $this->relationshipService->loadExistingRelationships(
                        $postId,
                        $posttype['fields'],
                        $posttypeSlug,
                        $lang,
                        $model
                    );
                    if (!empty($termIds) && isset($termIds['terms'])) {
                        $post['terms'] = $termIds['terms'];
                    }

                    $formData['post'] = $post;
                }
            }
            // ✅ Finalize và load field-specific data
            foreach ($posttype['fields'] as &$field) {
                $fieldType = $field['type'] ?? '';

                // Finalize Image fields (add watermark config)
                if ($fieldType === 'Image') {
                    $field = $this->fieldService->finalizeImageFieldConfig($field);
                }

                /**
                 * Load Reference field values từ relation table
                 * 
                 * - selectionMode = single: Load ID từ cột OR relation table
                 * - selectionMode = multiple: Load IDs từ relation table
                 */
                if ($fieldType === 'Reference' && $postId && !empty($formData['post'])) {
                    $fieldName = $field['field_name'] ?? '';

                    if (!empty($fieldName)) {
                        // Get reference config
                        $refConfig = $this->fieldService->getReferenceConfig($field);
                        $selectionMode = $refConfig['selection_mode'] ?? 'single';
                        if ($selectionMode === 'single') {
                            $formData['post'][$fieldName] = (int)$formData['post'][$fieldName];
                        } else {
                            // Load reference IDs từ relation table
                            $referenceIds = $this->relationshipService->getReferenceIds(
                                $postId,
                                $field,
                                $posttypeSlug,
                                $lang,
                                $model
                            );
                            // Multiple mode: Return array of IDs
                            $formData['post'][$fieldName] = $referenceIds;
                        }
                    }
                }

                // Load options for Reference fields
                // if ($fieldType === 'Reference') {
                //     $field['data'] = $this->fieldService->loadReferenceFieldOptions($field, $lang, PostsModel::class);
                // }
                // Not Need
                // elseif ($fieldType === 'User') {
                //     // Load options for User fields
                //     $field['data'] = $this->loadUserOptions();
                // }
            }

            if ($postId) {
                // ✅ Remove system fields (status, created_at, updated_at - handled separately in view when Edit)
                $posttype['fields'] = $this->removeSystemFields($posttype['fields']);
                // ✅ Reindex array sau khi unset (fix JSON index)
                $posttype['fields'] = array_values($posttype['fields']);
                // ✅ Layout fields by position
                //$formData['fields_layout'] = $this->layoutFields($posttype['fields']);
            }

            $formData['posttype'] = $posttype;
            return $formData;
        } catch (\Exception $e) {
            error_log("PostsService::loadFormData error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Load user options for User field
     * 
     * @return array Array of users
     */
    protected function loadUserOptions()
    {
        try {
            $usersModel = new \App\Models\UsersModel();
            return $usersModel->getUsers();
        } catch (\Exception $e) {
            error_log("loadUserOptions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Load term inputs (add Taxonomy fields to form)
     * 
     * @param array $posttype Posttype config
     * @return array Updated posttype
     */
    protected function loadTermInputs($posttype)
    {
        $terms = $posttype['terms'] ?? [];

        if (empty($terms)) {
            return $posttype;
        }

        // Add Taxonomy field for each term type
        foreach ($terms as $term) {
            $field = [
                "id" => 1,
                "type" => "Taxonomy",
                "label" => $term['name'] ?? ucfirst($term['type']),
                "field_name" => $term['type'],
                "required" => false,
                "order" => 26,
                "posttype" => $posttype['slug'],
                "taxonomy" => $term['type'],
                "field_type" => "checkbox",
                "add_term" => true,
                "position" => "right"
            ];

            array_unshift($posttype['fields'], $field);
        }

        return $posttype;
    }

    /**
     * Remove system fields (handled separately in view)
     * 
     * @param array $fields Fields array
     * @return array Filtered fields
     */
    protected function removeSystemFields($fields)
    {
        $filtered = [];

        foreach ($fields as $field) {
            $fieldName = $field['field_name'] ?? '';

            if (!in_array($fieldName, ['status', 'created_at', 'updated_at'])) {
                $filtered[] = $field;
            } else {
                if ('updated_at' == $fieldName) {
                    $field['readonly'] = true;
                    $filtered[] = $field;
                }
            }
        }

        return $filtered;
    }

    /**
     * Layout fields by position
     * 
     * @param array $fields Fields array
     * @return array Grouped by position
     */
    protected function layoutFields($fields)
    {
        $layout = [
            'top' => [],
            'left' => [],
            'right' => [],
            'bottom' => []
        ];

        foreach ($fields as $field) {
            $position = $field['position'] ?? 'left';

            switch ($position) {
                case 'top':
                    $layout['top'][] = $field;
                    break;
                case 'right':
                    $layout['right'][] = $field;
                    break;
                case 'bottom':
                    $layout['bottom'][] = $field;
                    break;
                case 'left':
                default:
                    $layout['left'][] = $field;
                    break;
            }
        }

        return $layout;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Extract reference IDs from various value formats
     * 
     * Supports:
     * - Single ID: 123
     * - Array of IDs: [1, 2, 3]
     * - Object with ID: {"id": 123, "title": "..."}
     * - Array of objects: [{"id": 1}, {"id": 2}]
     * - JSON string
     * - Comma-separated string: "1,2,3"
     * 
     * @param mixed $value Input value
     * @return array Array of IDs
     */
    protected function extractReferenceIds($value)
    {
        $ids = [];

        if (empty($value)) {
            return $ids;
        }

        if (is_numeric($value)) {
            // Single ID: 123
            $ids[] = (int)$value;
        } elseif (is_string($value)) {
            // JSON string or comma-separated IDs
            if (strpos($value, '{') === 0 || strpos($value, '[') === 0) {
                // JSON format
                $decoded = _json_decode($value);
                $ids = $this->extractReferenceIds($decoded);
            } elseif (strpos($value, ',') !== false) {
                // Comma-separated: "1,2,3"
                $ids = array_map('intval', explode(',', $value));
            } else {
                // Single ID as string
                $ids[] = (int)$value;
            }
        } elseif (is_array($value)) {
            if (isset($value['id'])) {
                // Single object: {"id": 123, "title": "..."}
                $ids[] = (int)$value['id'];
            } else {
                // Array of IDs or objects
                foreach ($value as $item) {
                    if (is_numeric($item)) {
                        // Array of IDs: [1, 2, 3]
                        $ids[] = (int)$item;
                    } elseif (is_array($item) && isset($item['id'])) {
                        // Array of objects: [{"id": 1}, {"id": 2}]
                        $ids[] = (int)$item['id'];
                    }
                }
            }
        }

        // Remove duplicates and filter out invalid IDs
        $ids = array_unique(array_filter($ids, function ($id) {
            return is_numeric($id) && $id > 0;
        }));

        return array_values($ids);
    }

    /**
     * Handle lang_slug column across all language tables for a post.
     *
     * Bỏ qua nếu posttype dùng ngôn ngữ "all" (một bảng, không đa ngôn ngữ).
     * Chỉ áp dụng cho bài viết trạng thái public (active): chỉ các bản ngôn ngữ có status = active
     * mới được đưa slug vào map. Draft/khác → bỏ lang đó khỏi lang_slug để FE ẩn/không 301.
     *
     * @param array       $posttype        Posttype config (from posttype_db); must have 'slug', 'languages'
     * @param int         $postId          Post ID
     * @param string      $langChanges     Language code that changed (e.g. 'vi')
     * @param string      $newSlug         New slug for that language (empty = remove lang from map, e.g. when draft)
     * @param array       $currentLangSlug Optional. Current map ['lang' => 'slug']. If empty, built from DB (only active).
     * @return void
     */
    protected function handleLangSlugColumn($posttype, $postId, $langChanges, $newSlug, $currentLangSlug = [])
    {
        $posttypeSlug = $posttype['slug'] ?? '';
        if (empty($posttypeSlug) || $postId <= 0) {
            return;
        }

        $languages = isset($posttype['languages']) && is_array($posttype['languages'])
            ? $posttype['languages']
            : _json_decode($posttype['languages'] ?? '[]');
        if (empty($languages)) {
            return;
        }

        // Posttype ngôn ngữ "all" (một bảng) → không cần lang_slug cho FE 301 đa ngôn ngữ
        if (isset($languages[0]) && $languages[0] === 'all') {
            return;
        }

        // Step 1: Build lang_slug map — only include langs where post status is active (public)
        if (empty($currentLangSlug)) {
            $currentLangSlug = [];
            foreach ($languages as $lang) {
                $model = new PostsModel($posttypeSlug, $lang);
                $row = $model->getById($postId, ['slug', 'status']);
                if (empty($row) || empty($row['slug'])) {
                    continue;
                }
                // Chỉ đưa vào map khi trạng thái public (active); posttype không có status thì giữ tương thích
                $isPublic = !isset($row['status']) || $row['status'] === 'active';
                if ($isPublic) {
                    $currentLangSlug[$lang] = $row['slug'];
                }
            }
        }

        $currentLangSlug[$langChanges] = $newSlug;
        $currentLangSlug = array_filter($currentLangSlug, function ($v) {
            return $v !== null && $v !== '';
        });
        $langSlugJson = json_encode($currentLangSlug, JSON_UNESCAPED_UNICODE);

        // Step 2: Update lang_slug in every language table for this post ID
        foreach ($languages as $lang) {
            $tableName = posttype_name($posttypeSlug, $lang);
            if (!empty($tableName)) {
                DB::table($tableName)->where('id', $postId)->update(['lang_slug' => $langSlugJson]);
            }
        }
    }
}
