<?php

namespace App\Services\Fields;

use App\Models\PostsModel;
use App\Models\TermsModel;
use App\Models\OptionsModel;
use App\Services\Terms\TermsService;
use System\Database\DB;

/**
 * FieldsService - Complex Field Types Management
 * 
 * Handles business logic for:
 * - Variations field (Product variants with attributes)
 * - Repeater field (Repeating field groups) - Future
 * - Flexible field (Flexible content layouts) - Future
 * 
 * @package App\Services\Fields
 */
class FieldsService
{
    /** @var PostsModel */
    protected $postsModel;

    /** @var TermsModel */
    protected $termsModel;

    /** @var TermsService */
    protected $termsService;

    /** @var OptionsModel */
    protected $optionsModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->postsModel = new PostsModel();
        $this->termsModel = new TermsModel();
        $this->termsService = new TermsService();
        $this->optionsModel = new OptionsModel();
    }

    protected function getFullClassName($controllerName = 'Posts')
    {
        return 'App\Controllers\Backend\\' . ucfirst($controllerName) . 'Controller';
    }


    /**
     * Check permission for controller and action
     * @param string $action Action name
     * @return bool
     */
    protected function checkPermission($action = null, $controller = null)
    {
        if (empty($controller)) {
            $controller = $this->getFullClassName('Posts');
        }
        if (empty($action) || empty($controller)) {
            return false;
        }
        return has_permission($controller, $action) ?? false;
    }

    /**
     * Check permission and throw exception if not allowed
     * 
     * @param string $action Action name
     * @param string|null $controller Controller name (optional)
     * @return void
     * @throws \System\Core\AppException
     */
    protected function requirePermission($action, $controller = null)
    {
        if (!$this->checkPermission($action, $controller)) {
            throw new \System\Core\AppException(__('You do not have permission to perform this action'), 403, null, 403);
        }
    }

    /**
     * Check if current user has manage permission
     * 
     * @return bool App\Controllers\Backend\FilesController
     */
    private function hasManagePermission($controllerName = 'Posts')
    {
        return $this->checkPermission('manage', $this->getFullClassName($controllerName));
    }

    /**
     * Check if current user can access a post
     * 
     * @param mixed $post Post array or post ID
     * @return bool
     */
    protected function canAccessPost($post = null, $args = [])
    {
        if ($this->hasManagePermission()) return true;
        if (empty(current_user_id())) return false;

        if (is_numeric($post)) {
            $args = array_merge($args, ['id' => $post, 'post_status' => '']);
            $post = get_post($args);
        }

        if (empty($post) || !isset($post['id'])) {
            return false;
        }
        if (isset($post['id'])) {
            if (isset($post['user_id'])) {
                if (current_user_id() == $post['user_id']) return true;
            }
            if (isset($post['author'])) {
                if (current_user_id() == $post['author']) return true;
            }
        }

        return false;
    }

    // =========================================================================
    // VARIATIONS FIELD
    // =========================================================================

    /**
     * Initialize variations field
     * 
     * @param array $input Request data
     * @return array Result with success status
     */
    public function variations_init($input)
    {
        // ✅ Check permission first
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        try {
            // Validate required parameters
            if (empty($input['parent_posttype'])) {
                return ['success' => false, 'message' => __('parent_posttype is required')];
            }
            if (empty($input['field_name'])) {
                return ['success' => false, 'message' => __('field_name is required')];
            }
            if (empty($input['attributes']) || !is_array($input['attributes'])) {
                return ['success' => false, 'message' => __('Attributes must be a non-empty array')];
            }

            $parentPosttypeSlug = $input['parent_posttype'];
            $fieldName = $input['field_name'];
            $attributes = $input['attributes'];

            // Validate parent posttype
            $parentPostType = posttype_db($parentPosttypeSlug);
            if (empty($parentPostType)) {
                return [
                    'success' => false,
                    'message' => __("Parent posttype %s not found", $parentPosttypeSlug)
                ];
            }
            $postLang = S_POST('post_lang') ? S_POST('post_lang') : APP_LANG;
            $parentLanguages = _json_decode($parentPostType['languages']);
            if (!in_array($postLang, $parentLanguages)) {
                return ['success' => false, 'message' => __('Parent posttype not support this language')];
            }

            // Create or update variation posttype
            $variationPosttype = $this->variations_posttype_create($parentPostType, $fieldName, $parentLanguages, $attributes);

            // Create/update attribute terms
            $attributeTerms = $this->variations_attrs_create($variationPosttype, $attributes, $parentLanguages);

            if (empty($input['parent_post_id'])) {
                return ['success' => false, 'message' => __('parent_post_id is required')];
            }
            $parentPostTable = posttype_name($parentPosttypeSlug, $postLang);
            $parentPostId = (int)$input['parent_post_id'];
            $parentPost = $this->postsModel->getPostById($parentPostTable, $parentPostId);
            if (empty($parentPost)) {
                return ['success' => false, 'message' => __('Parent post not found')];
            }

            // ✅ Check access permission for parent post
            if (!$this->canAccessPost($parentPost)) {
                return [
                    'success' => false,
                    'message' => __('You do not have permission to manage variations for this post')
                ];
            }

            $parentPostSearchString = $parentPost['search_string'] ?? keyword_slug($parentPost['title']) ?? '';

            // Cleanup old variations
            $this->variations_items_cleanup($parentPostId, $variationPosttype, $attributeTerms, $parentLanguages);

            // Generate all combinations
            $variations = $this->variations_items_array($attributeTerms);

            // Create variation posts
            $createdVariations = $this->variations_items_create(
                $parentPostId,
                $variationPosttype,
                $variations,
                $parentLanguages,
                $parentPostSearchString
            );

            return [
                'success' => true,
                'data' => [
                    'posttype_slug' => $variationPosttype,
                    'attributes_created' => count($attributeTerms),
                    'terms_created' => array_sum(array_map('count', $attributeTerms)),
                    'variations_created' => count($createdVariations),
                    'variations' => $createdVariations
                ]
            ];
        } catch (\Exception $e) {
            error_log("FieldsService::variations_init error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get variations for a parent post
     * 
     * @param int $parentPostId Parent post ID
     * @param string $parentPosttype Parent posttype slug
     * @param string $fieldName Field name
     * @param string $lang Language code
     * @return array Variations data
     */
    public function variations_get($parentPostId, $parentPosttype, $fieldName, $lang)
    {
        // ✅ Check permission
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return ['data' => [], 'is_next' => false];
            }
        }

        $post_lang = S_POST('post_lang') ? S_POST('post_lang') : (S_GET('post_lang') ? S_GET('post_lang') : APP_LANG);

        // Validate parameters
        if (empty($parentPostId) || $parentPostId < 1) {
            return ['data' => [], 'is_next' => false];
        }
        if (empty($parentPosttype) || empty($fieldName)) {
            return ['data' => [], 'is_next' => false];
        }

        $variationPosttype = $parentPosttype . '_' . $fieldName;

        if (!posttype_lang_exists($parentPosttype, $post_lang) || !posttype_lang_exists($variationPosttype, $post_lang)) {
            return ['data' => [], 'is_next' => false];
        }

        $parentPost = $this->postsModel->getPostById(posttype_name($parentPosttype, $post_lang), $parentPostId);
        if (empty($parentPost)) {
            return ['data' => [], 'is_next' => false];
        }

        // ✅ Check access permission for parent post
        if (!$this->canAccessPost($parentPost)) {
            return ['data' => [], 'is_next' => false];
        }

        $variations = get_posts([
            'post_type' => $variationPosttype,
            'lang' => $lang,
            'posts_per_page' => -1,
            'filters' => [['post_id', $parentPostId, '=']],
            'orderby' => 'id',
            'order' => 'ASC'
        ]);

        return $variations;
    }

    /**
     * Bulk update variations
     * 
     * @param string $posttype Parent posttype
     * @param string $fieldName Field name
     * @param string $lang Language code
     * @param array $items Items to update
     * @return array Update result
     */
    public function variations_bulk_update($posttype, $fieldName, $lang, $items)
    {
        // ✅ Check permission first
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // Validate parameters
        if (empty($posttype) || empty($fieldName)) {
            return [
                'success' => false,
                'message' => __('posttype and field_name are required')
            ];
        }
        if (empty($items) || !is_array($items)) {
            return [
                'success' => false,
                'message' => __('items must be a non-empty array')
            ];
        }

        $variationPosttype = $posttype . '_' . $fieldName;

        // Verify posttype exists
        $postType = posttype_db($variationPosttype);
        if (empty($postType)) {
            return [
                'success' => false,
                'message' => __("Posttype %s not found", $variationPosttype)
            ];
        }

        $updated = 0;
        $failed = 0;
        $errors = [];
        $parentPosts = [];

        foreach ($items as $index => $item) {
            $variationId = $item['id'] ?? null;
            $data = $item['data'] ?? [];

            if (empty($variationId) || empty($data)) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'id' => $variationId,
                    'error' => __('Missing ID or data')
                ];
                continue;
            }

            try {
                // Validate table exists
                $tableName = posttype_name($variationPosttype, $lang);
                if (empty($tableName)) {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'id' => $variationId,
                        'error' => __("Table not found")
                    ];
                    continue;
                }

                // Verify post exists
                $existingPost = $this->postsModel->getPostById($tableName, $variationId);
                if (empty($existingPost)) {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'id' => $variationId,
                        'error' => __("Variation not found")
                    ];
                    continue;
                }

                // ✅ Merge client data into existing post
                // Client chỉ gửi những field có thay đổi, nên cần merge với existing data
                foreach ($existingPost as $key => $value) {
                    if (array_key_exists($key, $data)) {
                        $existingPost[$key] = $data[$key];
                    }
                }

                if (!$this->hasManagePermission('Posts')) {
                    $canAccessPost = false;
                    if (isset($existingPost['post_id']) && !empty($existingPost['post_id'])) {
                        if (!isset($parentPosts[$existingPost['post_id']])) {
                            $parentPosts[$existingPost['post_id']] = $this->postsModel->getPostById(posttype_name($posttype), $existingPost['post_id']);
                        }
                        if (isset($parentPosts[$existingPost['post_id']]) && $this->canAccessPost($parentPosts[$existingPost['post_id']])) {
                            $canAccessPost = true;
                        }
                    }
                    if (!$canAccessPost) {
                        $failed++;
                        $errors[] = [
                            'index' => $index,
                            'id' => $variationId,
                            'error' => __('You do not have permission to access this post')
                        ];
                        continue;
                    }
                }

                //Update by PostsService and show detail error
                $postsService = new \App\Services\Posts\PostsService();
                $success = $postsService->update($variationPosttype, $variationId, $existingPost, $lang);
                if ($success['success']) {
                    $updated++;
                } else {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'id' => $variationId,
                        'error' => $success['errors']
                    ];
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'id' => $variationId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'updated' => $updated,
                'failed' => $failed,
                'total' => count($items),
                'errors' => $errors
            ]
        ];
    }

    /**
     * Get attributes for a variation posttype
     * 
     * @param string $parentPosttype Parent posttype slug
     * @param string $fieldName Field name
     * @return array Attributes list
     */
    public function variations_attrs_get($parentPosttype, $fieldName)
    {
        // ✅ Check permission
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // Validate parameters
        if (empty($parentPosttype) || empty($fieldName)) {
            return [
                'success' => false,
                'message' => __('parent_posttype and field_name are required')
            ];
        }

        $variationPosttype = $parentPosttype . '_' . $fieldName;
        $variationPostType = posttype_db($variationPosttype);

        if (empty($variationPostType)) {
            return [
                'success' => false,
                'message' => __("Variation posttype %s not found", $variationPosttype)
            ];
        }

        $existingTerms = _json_decode($variationPostType['terms']);
        $resultAttributes = [];

        if (!empty($existingTerms) && is_array($existingTerms)) {
            foreach ($existingTerms as $term) {
                $attrSlug = $term['type'] ?? '';
                $attrName = $term['name'] ?? ucfirst($attrSlug);

                $resultAttributes[] = [
                    'name' => $attrName,
                    'slug' => $attrSlug,
                ];
            }
        }

        return [
            'success' => true,
            'data' => $resultAttributes
        ];
    }

    // =========================================================================
    // PROTECTED HELPER METHODS
    // =========================================================================

    /**
     * Create variation posttype
     * 
     * @param array $parentPosttype Parent posttype data
     * @param string $fieldName Field name
     * @param array $languages Languages
     * @param array $attributes Attributes config
     * @return string Variation posttype slug
     */
    protected function variations_posttype_create($parentPosttype, $fieldName, $languages, $attributes = [])
    {
        $variationSlug = $parentPosttype['slug'] . '_' . $fieldName;
        $parentFields = _json_decode($parentPosttype['fields']);
        $variationsFields = [];
        //Check title and search_string columns in parent posttype
        $titleColumn = null;
        $searchStringColumn = null;

        if (!empty($parentFields)) {
            foreach ($parentFields as $field) {
                if (($field['field_name'] ?? '') === $fieldName) {
                    $variationsFields = $field['fields'] ?? [];
                    break;
                }
            }
        }

        if (empty($variationsFields)) {
            throw new \Exception(__("Fields configuration required for variation posttype"));
        }

        foreach ($parentFields as $field) {
            if (($field['field_name'] ?? '') === 'title' || ($field['field_name'] ?? '') === 'name' || ($field['field_name'] ?? '') === 'label') {
                $titleColumn = $field['field_name'];
                break;
            }
        }
        foreach ($parentFields as $field) {
            if (($field['field_name'] ?? '') === 'search_string') {
                $searchStringColumn = $field['field_name'];
                break;
            }
        }

        // Ensure post_id field exists
        $hasPostIdField = false;
        foreach ($variationsFields as $field) {
            if (($field['field_name'] ?? '') === 'post_id') {
                $hasPostIdField = true;
                break;
            }
        }

        $postIdField = null;
        if (!$hasPostIdField) {
            $postIdField = [
                'id' => \App\Libraries\Fastuuid::timeuuid(),
                'field_name' => 'post_id',
                //'type' => 'Number',
                'type' => 'Reference',
                'reference' => [
                    'postTypeRef' => $parentPosttype['slug'],
                    'selectionMode' => 'single',
                    'bidirectional' => false,
                    'reverseField' => '',
                    'search_columns' => [],
                    'display_columns' => ['id'],
                    'filter' => [],
                    'sort' => [
                        ['column' => 'id', 'direction' => 'desc']
                    ],
                ],
                'label' => 'Parent Post ID',
                'required' => true,
                'indexdb' => true,
                'visibility' => true,
            ];
            if (!empty($titleColumn)) {
                $postIdField['reference']['search_columns'][] = $titleColumn;
                $postIdField['reference']['display_columns'][] = $titleColumn;
            }
            if (!empty($searchStringColumn)) {
                $postIdField['reference']['search_columns'][] = $searchStringColumn;
            }
        }

        // Check if exists
        if (posttype_config($variationSlug)) {
            $variationPostType = posttype_db($variationSlug);
            if (empty($variationPostType)) {
                throw new \Exception(__("Variation posttype %s not found", $variationSlug));
            }
            $variationPostType['fields'] = _json_decode($variationPostType['fields']);
            foreach ($variationPostType['fields'] as $field) {
                if (($field['field_name'] ?? '') === 'post_id') {
                    $postIdField = $field;
                    break;
                }
            }
            if (!empty($postIdField)) {
                array_unshift($variationsFields, $postIdField);
            }
            $this->variations_posttype_update($variationSlug, $variationsFields, $attributes, $languages);
            return $variationSlug;
        } else {
            if (!empty($postIdField)) {
                array_unshift($variationsFields, $postIdField);
            }
        }

        // Create new
        $processedFields = [];
        foreach ($variationsFields as $index => $field) {
            $processedFields[] = array_merge([
                'id' => \App\Libraries\Fastuuid::timeuuid(),
                'synchronous' => true,
                'required' => false,
                'order' => $index + 1,
                'width_value' => 100,
                'width_unit' => '%',
                'position' => 'left',
            ], $field);
        }

        $termsConfig = [];
        foreach ($attributes as $attribute) {
            $attrSlug = $attribute['slug'] ?? url_slug($attribute['name'] ?? '');
            $attrName = $attribute['name'] ?? ucfirst($attrSlug);

            $termsConfig[] = [
                'id' => \App\Libraries\Fastuuid::timeuuid(),
                'name' => $attrName,
                'type' => $attrSlug,
                'synchronous_init' => 'true',
                'hierarchical' => true
            ];
        }

        $posttypeConfig = [
            'name' => ucfirst($parentPosttype['slug']) . ' ' . ucfirst($fieldName),
            'languages' => $languages,
            'fields' => $processedFields,
            'terms' => $termsConfig,
            'status' => 'active',
            'menu' => '',
        ];

        $result = register_posttype($variationSlug, $posttypeConfig);

        if (!$result['success']) {
            throw new \Exception(__("Failed to create variation posttype"));
        }

        return $variationSlug;
    }

    /**
     * Update existing variation posttype
     */
    protected function variations_posttype_update($variationSlug, $variationsFields, $attributes, $languages)
    {
        $updatePosttype = posttype_db($variationSlug);
        if (empty($updatePosttype)) {
            return;
        }

        $updatePosttype['fields'] = _json_decode($updatePosttype['fields']);
        $updatePosttype['terms'] = _json_decode($updatePosttype['terms']);
        $updatePosttype['languages'] = _json_decode($updatePosttype['languages']);

        // Sync fields
        $existingFieldsMap = [];
        foreach ($updatePosttype['fields'] as $existingField) {
            if (isset($existingField['field_name'])) {
                $existingFieldsMap[$existingField['field_name']] = $existingField;
            }
        }

        $mergedFields = [];
        foreach ($variationsFields as $index => $newField) {
            $fieldName = $newField['field_name'] ?? '';
            if (!$fieldName) continue;

            $existingFieldId = $existingFieldsMap[$fieldName]['id'] ?? null;

            $mergedFields[] = array_merge([
                'id' => $existingFieldId ?? \App\Libraries\Fastuuid::timeuuid(),
                'synchronous' => true,
                'required' => false,
                'order' => $index + 1,
                'width_value' => 100,
                'width_unit' => '%',
                'position' => 'left',
            ], $newField);
        }

        // Append terms
        $mergedTerms = $updatePosttype['terms'];
        $existingTypes = array_column($mergedTerms, 'type');

        foreach ($attributes as $attribute) {
            $attrSlug = $attribute['slug'] ?? url_slug($attribute['name'] ?? '');
            $attrName = $attribute['name'] ?? ucfirst($attrSlug);

            if (!in_array($attrSlug, $existingTypes)) {
                $mergedTerms[] = [
                    'id' => \App\Libraries\Fastuuid::timeuuid(),
                    'name' => $attrName,
                    'type' => $attrSlug,
                    'synchronous_init' => 'true',
                    'hierarchical' => false
                ];
            }
        }

        $updatePosttype['fields'] = $mergedFields;
        $updatePosttype['terms'] = $mergedTerms;
        $updatePosttype['languages'] = $languages;
        $updatePosttype['updated_at'] = date('Y-m-d H:i:s');

        update_posttype($variationSlug, $updatePosttype, true);
    }

    /**
     * Create attribute terms
     */
    protected function variations_attrs_create($variationPosttype, $attributes, $languages)
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $attrSlug = $attribute['slug'] ?? url_slug($attribute['name'] ?? '');
            $attrName = $attribute['name'] ?? ucfirst($attrSlug);
            $items = $attribute['items'] ?? [];

            $result[$attrSlug] = [];

            foreach ($items as $item) {
                $itemSlug = $item['slug'] ?? url_slug($item['name'] ?? '');
                $itemName = $item['name'] ?? ucfirst($itemSlug);
                $itemSearchString = keyword_slug($attrName . ' ' . $itemName);

                $termIds = [];
                $firstTermId = null;

                foreach ($languages as $lang) {
                    $existingTerm = $this->termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                        $itemSlug,
                        $variationPosttype,
                        $attrSlug,
                        $lang
                    );

                    if (!empty($existingTerm)) {
                        $termId = is_array($existingTerm) ? $existingTerm[0]['id'] : $existingTerm['id'];
                    } else {
                        // ✅ Create via TermsService (with validation and unique slug)
                        $createResult = $this->termsService->create([
                            'name' => $itemName,
                            'slug' => $itemSlug,
                            'type' => $attrSlug,
                            'posttype' => $variationPosttype,
                            'lang' => $lang,
                            'id_main' => $firstTermId ?? 0,
                            'status' => 'active'
                        ]);

                        $termId = $createResult['term_id'] ?? 0;

                        if ($firstTermId === null) {
                            $firstTermId = $termId;
                        }
                    }

                    $termIds[$lang] = $termId;
                }

                $result[$attrSlug][] = [
                    'slug' => $itemSlug,
                    'name' => $itemName,
                    'search_string' => $itemSearchString,
                    'term_ids' => $termIds
                ];
            }
        }

        return $result;
    }

    /**
     * Cleanup old variations
     */
    protected function variations_items_cleanup($parentPostId, $variationPosttype, $newAttributeTerms, $languages)
    {
        $existingVariations = get_posts([
            'post_type' => $variationPosttype,
            'lang' => $languages[0],
            'posts_per_page' => -1,
            'filters' => [['post_id', $parentPostId, '=']]
        ]);

        if (empty($existingVariations['data'])) {
            return;
        }

        // Build valid term IDs
        $validTermIds = [];
        foreach ($newAttributeTerms as $items) {
            foreach ($items as $item) {
                foreach ($item['term_ids'] as $termId) {
                    $validTermIds[] = $termId;
                }
            }
        }

        $tableRelation = table_postrel($variationPosttype);

        foreach ($existingVariations['data'] as $variation) {
            $variationTerms = DB::table($tableRelation)
                ->where('post_id', $variation['id'])
                ->where('lang', $languages[0])
                ->get();

            $hasInvalidTerm = false;
            foreach ($variationTerms as $rel) {
                if (!in_array($rel['rel_id'], $validTermIds)) {
                    $hasInvalidTerm = true;
                    break;
                }
            }

            if ($hasInvalidTerm) {
                posts_delete($variation['id'], $variationPosttype);
            }
        }
    }

    /**
     * Generate all variation combinations
     */
    protected function variations_items_array($attributeTerms)
    {
        if (empty($attributeTerms)) {
            return [];
        }

        $arrays = [];
        $attributeSlugs = [];

        foreach ($attributeTerms as $attrSlug => $items) {
            $attributeSlugs[] = $attrSlug;
            $arrays[] = $items;
        }

        $combinations = $this->variations_items_cartesian($arrays);

        $variations = [];
        foreach ($combinations as $combination) {
            $variation = ['attributes' => []];

            foreach ($combination as $index => $item) {
                $attrSlug = $attributeSlugs[$index];
                $variation['attributes'][$attrSlug] = $item;
            }

            $variations[] = $variation;
        }

        return $variations;
    }

    /**
     * Cartesian product
     */
    protected function variations_items_cartesian($arrays)
    {
        $result = [[]];

        foreach ($arrays as $key => $values) {
            $append = [];
            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }
            $result = $append;
        }

        return $result;
    }

    /**
     * Create variation posts
     */
    protected function variations_items_create($parentPostId, $variationPosttype, $variations, $languages, $parentPostSearchString = '')
    {
        $created = [];

        // ✅ Load variation posttype config to get fields (DYNAMIC)
        $variationPostTypeConfig = posttype_db($variationPosttype);
        if (empty($variationPostTypeConfig)) {
            throw new \Exception(__("Variation posttype %s not found", $variationPosttype));
        }

        $variationFields = _json_decode($variationPostTypeConfig['fields'] ?? '[]');

        foreach ($variations as $variation) {
            $titleParts = [];
            $slugParts = [];
            $searchStringParts = [];
            $attributeTermIds = [];

            foreach ($variation['attributes'] as $attrSlug => $item) {
                $titleParts[] = $item['name'];
                $slugParts[] = $item['slug'];
                $searchStringParts[] = $item['search_string'];
                $attributeTermIds[$attrSlug] = $item['term_ids'];
            }

            $title = implode(' - ', $titleParts);
            $slug = implode('-', $slugParts);
            $searchString = implode(' ', $searchStringParts);
            if (!empty($parentPostSearchString)) {
                $searchString = $parentPostSearchString . ' ' . $searchString;
            }
            $sku = $parentPostId . '__' . implode('__', $slugParts);

            $firstLang = $languages[0];

            // Check existing
            $existingVariation = get_post([
                'slug' => $slug,
                'post_type' => $variationPosttype,
                'post_status' => '',
                'lang' => $firstLang,
                'filters' => [['post_id', $parentPostId, '=']]
            ]);

            if ($existingVariation) {
                $existingVariation['post_id'] = $parentPostId;
                $existingVariation['attributes'] = $variation['attributes'];
                $created[] = $existingVariation;
                continue;
            }

            // ✅ Build draft data ĐỘNG - chỉ set fields NẾU CÓ trong variation posttype
            $variationData = [];
            $variationDataResult = [];

            if (!empty($variationFields)) {
                foreach ($variationFields as $field) {
                    $fieldName = $field['field_name'] ?? '';
                    $fieldType = $field['type'] ?? 'Text';

                    if (empty($fieldName)) {
                        continue;
                    }

                    // Skip ID (auto-increment)
                    if ($fieldName === 'id') {
                        continue;
                    }

                    $variationDataResult[$fieldName] = null; // Default value return is null

                    // Set post_id from parent post ID
                    if ($fieldName === 'post_id') {
                        $variationData['post_id'] = $parentPostId;
                        continue;
                    }

                    // Skip Reference fields (handled separately)
                    if ($fieldType === 'Reference') {
                        continue;
                    }

                    // ✅ XỬ LÝ SPECIAL FIELDS NẾU CÓ
                    if ($fieldName === 'title' || $fieldName === 'name' || $fieldName === 'item_title') {
                        // Title field exists → set from generated title
                        $variationData['title'] = $title;
                        continue;
                    }

                    // Gen Search String from Title
                    if ($fieldName === 'search_string') {
                        // Search String field exists → set from generated search string
                        $variationData['search_string'] = $searchString;
                        continue;
                    }

                    //Gen SKU 
                    if ($fieldName === 'sku') {
                        $variationData['sku'] = $sku;
                        continue;
                    }

                    if ($fieldName === 'slug') {
                        // Slug field exists → set from generated slug
                        $variationData['slug'] = $slug;
                        continue;
                    }

                    if ($fieldName === 'status') {
                        // Status field exists → set to default or 'active'
                        $variationData['status'] = null;
                        if (isset($field['default_value']) && !empty($field['default_value'])) {
                            $variationData['status'] = $field['default_value'];
                        } else {
                            if (isset($field['options']) && is_array($field['options']) && count($field['options']) > 0) {
                                foreach ($field['options'] as $option) {
                                    $variationData['status'] = $option['value'];
                                    if (isset($option['value']) && $option['value'] === 'active') {
                                        break;
                                    }
                                }
                            }
                            // Fallback to 'active' if no default found
                            if (empty($variationData['status'])) {
                                $variationData['status'] = 'active';
                            }
                        }
                        continue;
                    }

                    if ($fieldName === 'created_at') {
                        // created_at exists → set to now
                        $variationData['created_at'] = _DateTime();
                        continue;
                    }

                    if ($fieldName === 'updated_at') {
                        // updated_at exists → set to now
                        $variationData['updated_at'] = _DateTime();
                        continue;
                    }

                    // ✅ NORMAL FIELDS → NULL (draft can be empty)
                    $variationData[$fieldName] = null;
                }
                foreach ($variationData as $fieldName => $value) {
                    if (!is_null($value)) {
                        $variationDataResult[$fieldName] = $value;
                    }
                }
            }

            // Set create_draft flag to skip required validation
            $variationData['create_draft'] = true;

            $variationId = posts_add($variationPosttype, $variationData);

            if ($variationId) {
                // Add terms
                foreach ($attributeTermIds as $attrSlug => $termIds) {
                    if (isset($termIds[$firstLang])) {
                        posts_add_term($variationId, $termIds[$firstLang], $variationPosttype, $firstLang);
                    }
                }

                // Clone to other languages
                $otherLangs = array_diff($languages, [$firstLang]);
                if (!empty($otherLangs)) {
                    posts_clone_language($variationId, $variationPosttype, $otherLangs, $firstLang);
                }

                $created[] = array_merge($variationDataResult, ['id' => $variationId, 'attributes' => $variation['attributes']]);
            }
        }

        return $created;
    }

    // =========================================================================
    // REFERENCE FIELD
    // =========================================================================

    /**
     * Extract base field name from nested key (for repeater fields)
     * 
     * Handles formats:
     * - "repeater_field[0][sub_field]" → "sub_field"
     * - "repeater_field.0.sub_field" → "sub_field"
     * - "repeater_field[0][nested][1][sub_field]" → "sub_field"
     * - "repeater_field[0][nested_repeater][1][deep_field]" → "deep_field"
     * - "simple_field" → "simple_field" (unchanged)
     * 
     * @param string $fieldName Field name (may be nested)
     * @return string Base field name
     */
    protected function extractBaseFieldName($fieldName)
    {
        if (empty($fieldName) || !is_string($fieldName)) {
            return $fieldName;
        }

        // Case 1: Array notation: "repeater[0][sub_field]" or "repeater[0][nested][1][sub_field]"
        // Match the last bracket pair that contains a non-numeric value (field name)
        if (preg_match_all('/\[([^\]]+)\]/', $fieldName, $matches)) {
            // Get all matches and find the last non-numeric one (should be the field name)
            $brackets = $matches[1];
            for ($i = count($brackets) - 1; $i >= 0; $i--) {
                $value = $brackets[$i];
                // If it's not purely numeric, it's likely the field name
                if (!is_numeric($value) && !empty($value)) {
                    return $value;
                }
            }
            // If all are numeric, return the last one (edge case)
            if (!empty($brackets)) {
                return end($brackets);
            }
        }

        // Case 2: Dot notation: "repeater.0.sub_field" or "repeater.0.nested.1.sub_field"
        if (strpos($fieldName, '.') !== false) {
            $parts = explode('.', $fieldName);
            // Find the last non-numeric part (should be the field name)
            for ($i = count($parts) - 1; $i >= 0; $i--) {
                $part = $parts[$i];
                if (!is_numeric($part) && !empty($part)) {
                    return $part;
                }
            }
            // If all are numeric (edge case), return the last part
            return end($parts);
        }

        // Case 3: Simple field name (no nesting)
        return $fieldName;
    }

    /**
     * Search posts for Reference field selection
     * 
     * @param array $input Request data
     * @return array Search results with formatted items
     */
    public function reference_search($input)
    {
        // ✅ Check permission (add/edit để search/browse)
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        try {
            // Validate required parameters
            $isOptions = (int)($input['is_options'] ?? 0);

            if (empty($input['posttype']) && !$isOptions) {
                return ['success' => false, 'message' => __('posttype is required')];
            }
            if (empty($input['field_name'])) {
                return ['success' => false, 'message' => __('field_name is required')];
            }

            $sourcePosttype = $input['posttype'] ?? '';
            $fieldName = $input['field_name'];
            $limit = (int)($input['limit'] ?? 10);
            $page = (int)($input['page'] ?? 1);
            $search = $input['search'] ?? '';
            $postLang = $input['post_lang'] ?? APP_LANG;

            // Validate limit and page
            if ($limit < 1 || $limit > 100) {
                $limit = 10;
            }
            if ($page < 1) {
                $page = 1;
            }

            // Parse preselected IDs
            $preselectedIds = [];
            if (!empty($input['ids'])) {
                if (is_string($input['ids'])) {
                    $preselectedIds = _json_decode($input['ids']);
                } elseif (is_array($input['ids'])) {
                    $preselectedIds = $input['ids'];
                }
                $preselectedIds = array_filter(array_map('intval', $preselectedIds));
            }

            $referenceField = null;
            $referenceConfig = [];
            $repeaterKey = trim((string)($input['repeater_key'] ?? ''));

            // Get config from options table if is_options = 1
            if ($isOptions == 1) {
                // When repeater_key is provided: get Repeater option by field_name, then find Reference inside its fields[]
                if ($repeaterKey !== '') {
                    $optionData = $this->optionsModel->getByName($fieldName);
                    if (empty($optionData)) {
                        return [
                            'success' => false,
                            'message' => __("Option field %s not found", $fieldName)
                        ];
                    }

                    // Parse optional: may be JSON string in DB
                    $optionalRaw = $optionData['optional'] ?? '';
                    $optional = is_string($optionalRaw) ? _json_decode($optionalRaw) : $optionalRaw;
                    if (!is_array($optional)) {
                        $optional = [];
                    }

                    if (($optionData['type'] ?? '') !== 'Repeater') {
                        return [
                            'success' => false,
                            'message' => __("Field %s is not a Repeater field", $fieldName)
                        ];
                    }

                    $repeaterFields = $optional['fields'] ?? [];
                    if (!is_array($repeaterFields)) {
                        $repeaterFields = [];
                    }

                    $nestedReferenceField = null;
                    foreach ($repeaterFields as $subField) {
                        if (isset($subField['field_name']) && $subField['field_name'] === $repeaterKey) {
                            $nestedReferenceField = $subField;
                            break;
                        }
                    }

                    if (empty($nestedReferenceField)) {
                        return [
                            'success' => false,
                            'message' => __("Reference field %s not found inside Repeater", $repeaterKey)
                        ];
                    }

                    if (($nestedReferenceField['type'] ?? '') !== 'Reference') {
                        return [
                            'success' => false,
                            'message' => __("Field %s is not a Reference field", $repeaterKey)
                        ];
                    }

                    $referenceField = $nestedReferenceField;
                    $referenceConfig = $nestedReferenceField['reference'] ?? [];
                } else {
                    // No repeater_key: direct option is Reference (legacy / flat options)
                    // Extract base field name from nested key (for repeater fields)
                    // Support formats: "repeater[0][sub_field]" or "repeater.0.sub_field"
                    $baseFieldName = $this->extractBaseFieldName($fieldName);

                    if ($baseFieldName !== $fieldName) {
                        error_log("FieldsService::reference_search - Extracted base field name: '{$baseFieldName}' from nested key: '{$fieldName}'");
                    }

                    $optionData = $this->optionsModel->getByName($baseFieldName);
                    if (empty($optionData) && $baseFieldName !== $fieldName) {
                        $optionData = $this->optionsModel->getByName($fieldName);
                    }

                    if (empty($optionData)) {
                        $errorMessage = $baseFieldName !== $fieldName
                            ? __("Option field not found. Tried: '%s' and '%s'", $baseFieldName, $fieldName)
                            : __("Option field %s not found", $baseFieldName);
                        return [
                            'success' => false,
                            'message' => $errorMessage
                        ];
                    }

                    if (($optionData['type'] ?? '') !== 'Reference') {
                        return [
                            'success' => false,
                            'message' => __("Field %s is not a Reference field", $fieldName)
                        ];
                    }

                    $optionalRaw = $optionData['optional'] ?? '{}';
                    $optional = is_string($optionalRaw) ? _json_decode($optionalRaw) : $optionalRaw;
                    if (empty($optional) || !is_array($optional)) {
                        return [
                            'success' => false,
                            'message' => __("Option field %s config not found", $fieldName)
                        ];
                    }

                    $referenceField = $optional;
                    $referenceConfig = $optional['reference'] ?? [];
                }
            } else {
                // Get source posttype config (original logic)
                $posttypeData = posttype_db($sourcePosttype);
                if (empty($posttypeData)) {
                    return [
                        'success' => false,
                        'message' => __('Source posttype not found')
                    ];
                }

                // Find Reference field
                $fields = _json_decode($posttypeData['fields']);

                foreach ($fields as $field) {
                    if (($field['field_name'] ?? '') === $fieldName && ($field['type'] ?? '') === 'Reference') {
                        $referenceField = $field;
                        break;
                    }
                }

                if (empty($referenceField)) {
                    return [
                        'success' => false,
                        'message' => __("Reference field %s not found", $fieldName)
                    ];
                }

                // Get reference config
                $referenceConfig = $referenceField['reference'] ?? [];
            }

            if (empty($referenceConfig)) {
                return [
                    'success' => false,
                    'message' => __('Reference config not found')
                ];
            }

            $referencedPosttype = $referenceConfig['postTypeRef'] ?? '';

            if (empty($referencedPosttype)) {
                return [
                    'success' => false,
                    'message' => __('Referenced posttype not configured')
                ];
            }

            // Verify table exists
            $tableName = posttype_name($referencedPosttype, $postLang);
            if (empty($tableName)) {
                return [
                    'success' => false,
                    'message' => __("Referenced posttype table not found")
                ];
            }

            // Get referenced fields
            $referencedPosttypeData = posttype_db($referencedPosttype);
            $referencedFields = _json_decode($referencedPosttypeData['fields']);
            $referencedFieldNames = array_column($referencedFields, 'field_name');

            // Build query
            $model = new PostsModel($referencedPosttype, $postLang);
            $query = $model->newQuery();

            // Apply search
            if (!empty($search) && !empty($referenceConfig['search_columns'])) {
                $searchColumns = array_filter($referenceConfig['search_columns'], function ($col) use ($referencedFieldNames) {
                    return in_array($col, $referencedFieldNames);
                });

                if (!empty($searchColumns)) {
                    $query->whereGroup(function ($q) use ($search, $searchColumns, $tableName) {
                        $isFirst = true;
                        foreach ($searchColumns as $col) {
                            if ($isFirst) {
                                $q->where("{$tableName}.{$col}", '%' . $search . '%', 'LIKE');
                                $isFirst = false;
                            } else {
                                $q->orWhere("{$tableName}.{$col}", '%' . $search . '%', 'LIKE');
                            }
                        }
                    });
                }
            }
            if (!$this->hasManagePermission('Posts')) {
                if (in_array('user_id', $referencedFieldNames)) {
                    $query = $query->where('user_id', current_user_id());
                } elseif (in_array('author', $referencedFieldNames)) {
                    $query = $query->where('author', current_user_id());
                }
            }

            // Apply filters
            if (!empty($referenceConfig['filter'])) {
                $this->reference_apply_filters($query, $referenceConfig['filter'], $tableName, $referencedFieldNames);
            }

            // Apply sort
            if (!empty($referenceConfig['sort'])) {
                foreach ($referenceConfig['sort'] as $sort) {
                    $column = $sort['column'] ?? '';
                    $direction = strtoupper($sort['direction'] ?? 'ASC');

                    if (in_array($column, $referencedFieldNames) && in_array($direction, ['ASC', 'DESC'])) {
                        $query->orderBy("{$tableName}.{$column}", $direction);
                    }
                }
            } else {
                $query->orderBy("{$tableName}.id", 'DESC');
            }

            // Select columns
            $displayColumns = $referenceConfig['display_columns'] ?? ['id'];
            if (!in_array('id', $displayColumns)) {
                array_unshift($displayColumns, 'id');
            }

            $selectColumns = array_map(function ($col) use ($tableName) {
                return "{$tableName}.{$col}";
            }, $displayColumns);
            $query->select($selectColumns);

            // Get preselected items
            $preselectedPosts = [];
            if (!empty($preselectedIds)) {
                $preselectedModel = new PostsModel($referencedPosttype, $postLang);
                $preselectedQuery = $preselectedModel->newQuery()->select($selectColumns);
                $preselectedQuery = $preselectedQuery->whereIn('id', $preselectedIds);
                if (!$this->hasManagePermission('Posts')) {
                    if (in_array('user_id', $referencedFieldNames)) {
                        $preselectedQuery = $preselectedQuery->where('user_id', current_user_id());
                    } elseif (in_array('author', $referencedFieldNames)) {
                        $preselectedQuery = $preselectedQuery->where('author', current_user_id());
                    }
                }
                $preselectedPosts = $preselectedQuery->get();
            }

            // Pagination
            $offset = ($page - 1) * $limit;
            $query->limit($limit);
            if ($offset > 0) {
                $query->offset($offset);
            }

            // Get results
            $posts = $query->get();

            // Merge preselected
            if (!empty($preselectedPosts)) {
                $existingIds = array_column($posts, 'id');
                foreach ($preselectedPosts as $preselected) {
                    if (!in_array($preselected['id'], $existingIds)) {
                        array_unshift($posts, $preselected);
                    }
                }
            }

            // Format: id + display_text
            $formattedPosts = [];
            foreach ($posts as $post) {
                $displayParts = [];
                foreach ($displayColumns as $col) {
                    if (isset($post[$col]) && $post[$col] !== null && $post[$col] !== '') {
                        $displayParts[] = $post[$col];
                    }
                }

                $formattedPosts[] = [
                    'id' => $post['id'],
                    'display_text' => implode(' - ', $displayParts)
                ];
            }

            return [
                'success' => true,
                'data' => $formattedPosts,  // Trực tiếp array, giống code cũ
                'page' => $page,
                'limit' => $limit,
                'total_items' => count($formattedPosts)
            ];
        } catch (\Exception $e) {
            error_log("FieldsService::reference_search error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Apply reference filters to query
     */
    protected function reference_apply_filters($query, $filters, $tableName, $allowedFields)
    {
        foreach ($filters as $filterGroup) {
            if (!isset($filterGroup['logic']) || !isset($filterGroup['conditions'])) {
                continue;
            }

            $logic = strtoupper($filterGroup['logic']);
            $conditions = $filterGroup['conditions'];

            if (!in_array($logic, ['AND', 'OR']) || empty($conditions)) {
                continue;
            }

            $query->whereGroup(function ($q) use ($conditions, $logic, $tableName, $allowedFields) {
                $isFirst = true;

                foreach ($conditions as $condition) {
                    $column = $condition['column'] ?? '';
                    $operator = strtoupper($condition['op'] ?? '=');
                    $value = $condition['value'] ?? '';

                    if (!in_array($column, $allowedFields)) {
                        continue;
                    }

                    $fullColumn = "{$tableName}.{$column}";
                    $method = ($isFirst || $logic === 'AND') ? 'where' : 'orWhere';

                    switch ($operator) {
                        case 'IN':
                            $valueArray = is_array($value) ? $value : explode(',', $value);
                            if ($method === 'orWhere') {
                                $q->orWhereIn($fullColumn, $valueArray);
                            } else {
                                $q->whereIn($fullColumn, $valueArray);
                            }
                            break;

                        case 'NOT IN':
                            $valueArray = is_array($value) ? $value : explode(',', $value);
                            if ($method === 'orWhere') {
                                $q->orWhereNotIn($fullColumn, $valueArray);
                            } else {
                                $q->whereNotIn($fullColumn, $valueArray);
                            }
                            break;

                        case 'LIKE':
                            $q->$method($fullColumn, '%' . $value . '%', 'LIKE');
                            break;

                        case 'NOT LIKE':
                            $q->$method($fullColumn, '%' . $value . '%', 'NOT LIKE');
                            break;

                        case 'BETWEEN':
                            if (is_array($value) && count($value) === 2) {
                                if ($method === 'orWhere') {
                                    $q->orWhereBetween($fullColumn, $value[0], $value[1]);
                                } else {
                                    $q->whereBetween($fullColumn, $value[0], $value[1]);
                                }
                            }
                            break;

                        default:
                            $q->$method($fullColumn, $value, $operator);
                            break;
                    }

                    $isFirst = false;
                }
            });
        }
    }

    // =========================================================================
    // USER FIELD
    // =========================================================================

    /**
     * Search users for User field selection
     * 
     * @param array $input Request data
     * @param int|null $currentUserId Current user ID (for permission check)
     * @param string|null $currentUserRole Current user role
     * @return array Search results
     */
    public function user_search($input, $currentUserId = null, $currentUserRole = null)
    {
        // ✅ Check permission (add/edit để search/browse users)
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        try {
            // Validate parameters
            $page = (int)($input['page'] ?? 1);
            $limit = (int)($input['limit'] ?? 10);
            $keyword = $input['search'] ?? '';

            // Parse preselected IDs
            $preselectedIds = [];
            if (!empty($input['ids'])) {
                if (is_string($input['ids'])) {
                    $ids = _json_decode($input['ids']);
                } elseif (is_array($input['ids'])) {
                    $ids = $input['ids'];
                } else {
                    $ids = [];
                }
                $preselectedIds = array_filter(array_map('intval', $ids));
            }

            // Validate ranges
            if ($limit < 1 || $limit > 100) {
                $limit = 10;
            }
            if ($page < 1) {
                $page = 1;
            }

            // Permission-based filtering
            if ($this->hasManagePermission('Posts')) {
                // Admin/Moderator or Manage Permission can see all active users
                $where = 'status = ?';
                $params = ['active'];
            } else {
                // Regular users can only see themselves
                $where = 'status = ? AND id = ?';
                $params = ['active', current_user_id()];
                // Only allow preselected IDs if it's the current user
                if (!empty($preselectedIds)) {
                    $preselectedIds = array_filter($preselectedIds, function ($id) {
                        return $id == current_user_id();
                    });
                }
            }

            // Apply keyword search
            if (!empty($keyword)) {
                $where .= " AND (fullname LIKE ? OR email LIKE ? OR username LIKE ?)";
                $params[] = '%' . $keyword . '%';
                $params[] = '%' . $keyword . '%';
                $params[] = '%' . $keyword . '%';
            }

            // Get preselected users (if any)
            $preselectedUsers = [];
            if (!empty($preselectedIds)) {
                $preselectedUsers = \App\Models\UsersModel::query()
                    ->select('id', 'fullname', 'username', 'role')
                    ->whereIn('id', $preselectedIds)
                    ->get();
            }

            // Query users (paginated)
            $users = \App\Models\UsersModel::query()->paginateWith(
                'id, fullname, username, role',
                $where,
                $params,
                'id desc',
                $page,
                $limit
            );

            $searchResults = $users['data'] ?? [];

            // Merge preselected users vào đầu (nếu chưa có)
            if (!empty($preselectedUsers)) {
                $existingIds = array_column($searchResults, 'id');
                foreach ($preselectedUsers as $preselected) {
                    if (!in_array($preselected['id'], $existingIds)) {
                        array_unshift($searchResults, $preselected);
                    }
                }
            }

            // Format for frontend: id + display_text
            $formattedUsers = [];
            foreach ($searchResults as $user) {
                $displayParts = [
                    $user['id'],
                    $user['fullname'] ?? $user['username'],
                    $user['username']
                ];

                $formattedUsers[] = [
                    'id' => $user['id'],
                    'display_text' => implode(' - ', array_filter($displayParts))
                ];
            }

            return [
                'success' => true,
                'data' => $formattedUsers,
                'page' => $page,
                'limit' => $limit,
                'total_items' => count($formattedUsers)
            ];
        } catch (\Exception $e) {
            error_log("FieldsService::user_search error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // =========================================================================
    // TERMS FIELD
    // =========================================================================

    /**
     * Search terms for Taxonomy field selection
     * 
     * @param string $posttype Posttype slug
     * @param string $taxonomy Taxonomy type
     * @param string|null $lang Language code
     * @return array Search results
     */
    public function terms_search($posttype = 'posts', $taxonomy = 'category', $lang = null)
    {
        // ✅ Check permission (add/edit để search/browse terms)
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        try {
            $lang = $lang ?? (S_GET('lang') ?? S_GET('post_lang') ?? 'all');

            // Verify posttype exists
            if (!posttype_lang_exists($posttype, $lang)) {
                return [
                    'success' => false,
                    'message' => 'Posttype not found'
                ];
            }

            // ✅ Get terms via TermsService (better than direct helper)
            $result = $this->termsService->getList([
                'taxonomy' => $taxonomy,
                'post_type' => $posttype,
                'lang' => $lang,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);

            $terms = $result['data'] ?? $result ?? [];

            return [
                'success' => true,
                'data' => $terms
            ];
        } catch (\Exception $e) {
            error_log("FieldsService::terms_search error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create term
     * 
     * @param array $data Term data from request
     * @return array Create result
     */
    public function terms_create($data)
    {
        // ✅ Check permission (add/edit để tạo term)
        try {
            $this->requirePermission('add');
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit');
            } catch (\System\Core\AppException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        try {
            // ✅ Validate required parameters
            if (empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'name is required',
                    'errors' => ['name' => ['Name is required']]
                ];
            }
            if (empty($data['posttype'])) {
                return [
                    'success' => false,
                    'message' => 'posttype is required',
                    'errors' => ['posttype' => ['Posttype is required']]
                ];
            }
            if (empty($data['type'])) {
                return [
                    'success' => false,
                    'message' => 'type (taxonomy) is required',
                    'errors' => ['type' => ['Taxonomy type is required']]
                ];
            }

            // ✅ Validate name length (giống TermsController)
            if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
                return [
                    'success' => false,
                    'message' => 'Name length must be between 2 and 100 characters',
                    'errors' => ['name' => ['Name length must be between 2 and 100 characters']]
                ];
            }

            $lang = $data['lang'] ?? $data['post_lang'] ?? 'all';

            // ✅ Verify posttype exists and has this language
            if (!posttype_lang_exists($data['posttype'], $lang)) {
                return [
                    'success' => false,
                    'message' => 'Posttype not found or does not support this language',
                    'errors' => ['posttype' => ['Posttype not found or does not support this language']]
                ];
            }

            // ✅ Auto-generate slug nếu empty (giống TermsController)
            if (empty($data['slug'])) {
                $data['slug'] = url_slug($data['name']);
            }

            // ✅ Validate and normalize parent
            if (isset($data['parent'])) {
                if (empty($data['parent'])) {
                    $data['parent'] = null;
                } else {
                    $data['parent'] = (int)$data['parent'];
                }
            }

            // ✅ Validate and normalize id_main (giống TermsController)
            if (empty($data['id_main']) || !is_numeric($data['id_main'])) {
                $data['id_main'] = 0;
            } else {
                $data['id_main'] = (int)$data['id_main'];
            }

            // ✅ Create via TermsService (with full validation and unique slug)
            // TermsService sẽ xử lý:
            // - Validation đầy đủ (TermsValidationService)
            // - Generate unique slug (tránh trùng)
            // - Check parent term exists
            // - Fire events
            // - Auto-set id_main nếu = 0
            $result = $this->termsService->create($data);

            if ($result['success']) {
                // Get created term with full data
                $createdTerm = $this->termsService->get($result['term_id']);

                return [
                    'success' => true,
                    'data' => $createdTerm,
                    'term_id' => $result['term_id']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create term',
                    'errors' => $result['errors'] ?? []
                ];
            }
        } catch (\Exception $e) {
            error_log("FieldsService::terms_create error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    // =========================================================================
    // PERMISSION HELPERS
    // =========================================================================

}
