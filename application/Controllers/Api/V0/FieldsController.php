<?php

namespace App\Controllers\Api\V0;

use App\Controllers\ApiController;
use App\Services\Fields\FieldsService;
use System\Core\AppException;

/**
 * FieldsController - API for Complex Field Types (THIN CONTROLLER)
 * 
 * Delegates business logic to FieldsService
 * 
 * Endpoints:
 * - Variations: init, get, update, delete, bulk update
 * - Attributes: get, add values
 * - Repeater: (future)
 * - Flexible: (future)
 * 
 * @package App\Controllers\Api\V0
 */
class FieldsController extends ApiController
{
    /** @var FieldsService */
    protected $fieldsService;
    private $postsControllerName;
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        load_helpers(['string', 'posts', 'terms', 'languages', 'query']);

        $this->fieldsService = new FieldsService();
        $this->postsControllerName = 'App\Controllers\Backend\PostsController';
    }

    /**
     * Check if current user has manage permission
     * 
     * @return bool
     */
    protected function hasManagePermission()
    {
        return $this->checkPermission('manage', $this->postsControllerName);
    }

    /**
     * Check if current user can access a post
     * 
     * @param mixed $post Post array or post ID
     * @param array $args Additional args for get_post()
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
    // FUTURE FEATURES
    // =========================================================================

    /**
     * Initialize repeater field
     * 
     * POST /api/v0/fields/repeater-init
     */
    public function repeater_init()
    {
        try {
            $this->requirePermission('add', $this->postsControllerName);
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit', $this->postsControllerName);
            } catch (\System\Core\AppException $e2) {
                return $this->error($e2->getMessage(), [], 403);
            }
        }
        return $this->error('Not implemented yet', [], 501);
    }

    /**
     * Initialize flexible field
     * 
     * POST /api/v0/fields/flexible-init
     */
    public function flexible_init()
    {
        try {
            $this->requirePermission('add', $this->postsControllerName);
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit', $this->postsControllerName);
            } catch (\System\Core\AppException $e2) {
                return $this->error($e2->getMessage(), [], 403);
            }
        }
        return $this->error('Not implemented yet', [], 501);
    }


    // =========================================================================
    // REFERENCE FIELD
    // =========================================================================

    /**
     * Initialize Reference field
     * 
     * POST /api/v0/fields/reference-init
     */
    public function reference_init()
    {
        try {
            $this->requirePermission('add', $this->postsControllerName);
        } catch (\System\Core\AppException $e) {
            try {
                $this->requirePermission('edit', $this->postsControllerName);
            } catch (\System\Core\AppException $e2) {
                return $this->error($e2->getMessage(), [], 403);
            }
        }
        return $this->error('Not implemented yet', [], 501);
    }

    /**
     * Search posts for Reference field selection
     * 
     * POST /api/v0/fields/reference-search
     * Body: {posttype, field_name, limit, page, ids, search, post_lang, is_options}
     */
    public function reference_search()
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            // Get is_options parameter
            $isOptions = (int)(S_POST('is_options') ?? 0);

            // Validate required
            // posttype is not required if is_options = 1
            if (empty(S_POST('posttype')) && !$isOptions) {
                return $this->error('posttype is required when is_options is not set', [], 422);
            }
            if (empty(S_POST('field_name'))) {
                return $this->error('field_name is required', [], 422);
            }

            // Prepare input
            $input = [
                'posttype' => S_POST('posttype') ?? '',
                'field_name' => S_POST('field_name'),
                'limit' => (int)(S_POST('limit') ?? 10),
                'page' => (int)(S_POST('page') ?? 1),
                'search' => S_POST('search') ?? '',
                'post_lang' => S_POST('post_lang') ?? APP_LANG,
                'ids' => S_POST('ids') ?? [],
                'is_options' => $isOptions,
            ];

            // Call service
            $result = $this->fieldsService->reference_search($input);

            if ($result['success']) {
                $responseData = [
                    'data' => $result['data'],
                    'page' => $result['page'] ?? 1,
                    'limit' => $result['limit'] ?? 10,
                    'total_items' => $result['total_items'] ?? 0
                ];
                return $this->success($responseData, 'Reference search results');
            } else {
                return $this->error($result['message'] ?? 'Search failed', [], 400);
            }
        } catch (\Exception $e) {
            error_log("FieldsController::reference_search error: " . $e->getMessage());
            return $this->error('Internal server error', [], 500);
        }
    }

    // =========================================================================
    // USER FIELD
    // =========================================================================

    /**
     * Search users for User field selection
     * 
     * POST /api/v0/fields/user-search
     * Body: {page, limit, search}
     */
    public function user_search()
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            // Get current user
            $user = current_user();

            // Prepare input
            $input = [
                'page' => (int)S_POST('page') ?? 1,
                'limit' => (int)S_POST('limit') ?? 10,
                'search' => S_POST('search') ?? '',
                'ids' => S_POST('ids') ?? [],
            ];

            // Call service với current user info
            $result = $this->fieldsService->user_search($input, $user['id'], $user['role']);

            if ($result['success']) {
                return $this->success($result['data'], 'Users retrieved');
            } else {
                return $this->error($result['message'] ?? 'Search failed', [], 400);
            }
        } catch (\Exception $e) {
            error_log("FieldsController::user_search error: " . $e->getMessage());
            return $this->error('Internal server error', [], 500);
        }
    }
    

    // =========================================================================
    // VARIATIONS FIELD
    // =========================================================================

    /**
     * Initialize variations field
     * 
     * POST /api/v0/fields/variations-init
     * 
     * Request: {parent_post_id, parent_posttype, field_name, attributes}
     */
    public function variations_init()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', [], 405);
            }

            // Validate required
            $required = ['parent_post_id', 'parent_posttype', 'field_name', 'attributes'];
            foreach ($required as $field) {
                if (!HAS_POST($field)) {
                    return $this->error("Missing required field: {$field}", [], 422);
                }
            }

            // Parse input data
            $input = [
                'parent_post_id' => (int)S_POST('parent_post_id'),
                'parent_posttype' => S_POST('parent_posttype') ?? '',
                'field_name' => S_POST('field_name') ?? '',
                'attributes' => $_POST['attributes']
            ];

            // Parse attributes nếu là JSON string
            if (is_string($input['attributes'])) {
                $input['attributes'] = _json_decode($input['attributes']);
            }

            if (!is_array($input['attributes'])) {
                return $this->error('attributes must be an array', [], 422);
            }

            // ✅ Check permission (add hoặc edit)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            // ✅ Check access permission for parent post
            $postLang = S_POST('post_lang') ?? APP_LANG;
            if (!posttype_lang_exists($input['parent_posttype'], $postLang)) {
                return $this->error('Posttype not found', [], 404);
            }


            if (!$this->canAccessPost($input['parent_post_id'], [
                'post_type' => $input['parent_posttype'],
                'post_status' => '',
                'lang' => $postLang
            ])) {
                return $this->error('You do not have permission to manage variations for this post', [], 403);
            }

            // Call service
            $result = $this->fieldsService->variations_init($input);

            if ($result['success']) {
                return $this->success($result['data'], 'Variations initialized successfully');
            } else {
                return $this->error($result['message'] ?? 'Failed', [], 400);
            }
        } catch (\Exception $e) {
            error_log("FieldsController::variations_init error: " . $e->getMessage());
            return $this->error('Internal server error', [], 500);
        }
    }

    /**
     * Get variations for parent post
     * 
     * GET /api/v0/fields/variations-get/{parent_post_id}
     * Query: parent_posttype, field_name, lang
     */
    public function variations_get($parentPostId)
    {
        try {
            // Validate & parse input
            $parentPostId = (int)$parentPostId;
            $parentPosttype = S_GET('parent_posttype') ?? '';
            $fieldName = S_GET('field_name') ?? '';
            $lang = S_GET('lang') ?? APP_LANG;

            if (empty($parentPosttype) || empty($fieldName)) {
                return $this->error('parent_posttype and field_name are required', [], 422);
            }

            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            // ✅ Check access permission for parent post
            $postLang = S_GET('post_lang') ?? $lang;
            if (!posttype_lang_exists($parentPosttype, $postLang)) {
                return $this->error('Posttype not found', [], 404);
            }

            if (!$this->canAccessPost($parentPostId, [
                'post_type' => $parentPosttype,
                'post_status' => '',
                'lang' => $postLang
            ])) {
                return $this->error('You do not have permission to view variations for this post', [], 403);
            }

            $result = $this->fieldsService->variations_get(
                $parentPostId,
                $parentPosttype,
                $fieldName,
                $lang
            );

            return $this->success($result, 'Variations retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving variations', [], 500);
        }
    }

    /**
     * Get single variation item
     * 
     * GET /api/v0/fields/variation-item-get/{variation_id}
     * Query: posttype, lang
     */
    public function variation_item_get($variationId)
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            $posttype = S_GET('posttype');
            $lang = S_GET('lang') ?? APP_LANG;

            if (empty($posttype)) {
                return $this->error('Missing posttype parameter', [], 422);
            }

            $variation = get_post([
                'id' => $variationId,
                'post_type' => $posttype,
                'lang' => $lang,
                'with_terms' => true,
                'post_status' => ''
            ]);

            if (empty($variation)) {
                return $this->error('Variation not found', [], 404);
            }

            // ✅ Check access permission for variation's parent post
            if (isset($variation['post_id']) && !empty($variation['post_id'])) {
                // Extract parent posttype from variation posttype (format: parent_fieldname)
                $parts = explode('_', $posttype);
                if (count($parts) >= 2) {
                    array_pop($parts); // Remove field name
                    $parentPosttype = implode('_', $parts);

                    if (!$this->canAccessPost($variation['post_id'], [
                        'post_type' => $parentPosttype,
                        'post_status' => '',
                        'lang' => $lang
                    ])) {
                        return $this->error('You do not have permission to view this variation', [], 403);
                    }
                }
            }

            return $this->success($variation, 'Variation retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving variation', [], 500);
        }
    }

    /**
     * Update variation item
     * 
     * POST/PUT /api/v0/fields/variation-item-update/{variation_id}
     * Body: {posttype, lang, data}
     */
    public function variation_item_update($variationId)
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', [], 405);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $posttype = $input['posttype'] ?? '';
            $lang = $input['lang'] ?? APP_LANG;
            $data = $input['data'] ?? [];

            if (empty($posttype) || empty($data)) {
                return $this->error('Missing required parameters', [], 422);
            }

            // ✅ Load existing post to merge with client data
            // Client chỉ gửi những field có thay đổi, nên cần merge với existing data
            $existingPost = get_post([
                'id' => $variationId,
                'post_type' => $posttype,
                'lang' => $lang,
                'post_status' => ''
            ]);

            if (empty($existingPost)) {
                return $this->error('Variation not found', [], 404);
            }

            // ✅ Check access permission for variation's parent post
            if (isset($existingPost['post_id']) && !empty($existingPost['post_id'])) {
                // Extract parent posttype from variation posttype (format: parent_fieldname)
                $parts = explode('_', $posttype);
                if (count($parts) >= 2) {
                    array_pop($parts); // Remove field name
                    $parentPosttype = implode('_', $parts);

                    if (!$this->canAccessPost($existingPost['post_id'], [
                        'post_type' => $parentPosttype,
                        'post_status' => '',
                        'lang' => $lang
                    ])) {
                        return $this->error('You do not have permission to update this variation', [], 403);
                    }
                }
            }

            foreach ($existingPost as $key => $value) {
                if (array_key_exists($key, $data)) {
                    $existingPost[$key] = $data[$key];
                }
            }
            $success = posts_update($variationId, $posttype, $existingPost, $lang);

            if ($success) {
                return $this->success(['variation_id' => $variationId], 'Variation updated');
            } else {
                return $this->error('Failed to update variation', [], 500);
            }
        } catch (\Exception $e) {
            return $this->error('Error updating variation', [], 500);
        }
    }

    /**
     * Bulk update variations
     * 
     * POST /api/v0/fields/variations-bulk-update
     * Body: {posttype, field_name, lang, items[], parent_post_id}
     */
    public function variations_bulk_update()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', [], 405);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $posttype = $input['posttype'] ?? '';
            $fieldName = $input['field_name'] ?? '';
            $lang = $input['lang'] ?? APP_LANG;
            $items = $input['items'] ?? [];
            $parentPostId = $input['parent_post_id'] ?? 0;

            if (empty($posttype) || empty($fieldName) || empty($items)) {
                return $this->error('Missing required parameters', [], 422);
            }

            // Parse items nếu là JSON string
            if (is_string($items)) {
                $items = _json_decode($items);
            }

            if (!is_array($items) || empty($items)) {
                return $this->error('items must be a non-empty array', [], 422);
            }

            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            // ✅ Check access permission for parent post (if provided)
            if (!empty($parentPostId)) {
                $postLang = $input['post_lang'] ?? $lang;
                if (!posttype_lang_exists($posttype, $postLang)) {
                    return $this->error('Posttype not found', [], 404);
                }

                if (!$this->canAccessPost($parentPostId, [
                    'post_type' => $posttype,
                    'post_status' => '',
                    'lang' => $postLang
                ])) {
                    return $this->error('You do not have permission to update variations for this post', [], 403);
                }
            }

            // Call service
            $result = $this->fieldsService->variations_bulk_update($posttype, $fieldName, $lang, $items);

            if ($result['success']) {
                return $this->success($result['data'], 'Bulk update completed');
            } else {
                return $this->error($result['message'] ?? 'Failed', [], 400);
            }
        } catch (\Exception $e) {
            return $this->error('Error bulk updating variations', [], 500);
        }
    }

    /**
     * Delete variation item
     * 
     * DELETE/POST /api/v0/fields/variation-item-delete/{variation_id}
     * Query/Body: posttype (or field_name), lang
     */
    public function variation_item_delete($variationId)
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            $posttype = S_GET('posttype') ?? S_POST('posttype');
            $fieldName = S_GET('field_name') ?? S_POST('field_name');
            $lang = S_GET('lang') ?? S_POST('lang') ?? APP_LANG;

            // Build variation posttype
            if (empty($posttype) && !empty($fieldName)) {
                $parentPosttype = S_GET('parent_posttype') ?? S_POST('parent_posttype');
                $posttype = $parentPosttype . '_' . $fieldName;
            }

            if (empty($posttype)) {
                return $this->error('Missing posttype parameter', [], 422);
            }

            // ✅ Check access permission for variation's parent post
            $existingPost = get_post($variationId, $posttype, [
                'lang' => $lang,
                'post_status' => ''
            ]);

            if (empty($existingPost)) {
                return $this->error('Variation not found', [], 404);
            }

            if (isset($existingPost['post_id']) && !empty($existingPost['post_id'])) {
                // Extract parent posttype from variation posttype (format: parent_fieldname)
                $parts = explode('_', $posttype);
                if (count($parts) >= 2) {
                    array_pop($parts); // Remove field name
                    $parentPosttype = implode('_', $parts);

                    if (!$this->canAccessPost($existingPost['post_id'], [
                        'post_type' => $parentPosttype,
                        'post_status' => '',
                        'lang' => $lang
                    ])) {
                        return $this->error('You do not have permission to delete this variation', [], 403);
                    }
                }
            }

            $success = posts_delete($variationId, $posttype, $lang);

            if ($success) {
                return $this->success([], 'Variation deleted successfully');
            } else {
                return $this->error('Failed to delete variation', [], 500);
            }
        } catch (\Exception $e) {
            return $this->error('Error deleting variation', [], 500);
        }
    }

    // =========================================================================
    // ATTRIBUTES
    // =========================================================================

    /**
     * Get attributes for variation posttype
     * 
     * GET /api/v0/fields/variation-attributes-get
     * Query: parent_posttype, field_name
     */
    public function variation_attributes_get()
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            $parentPosttype = S_GET('parent_posttype') ?? '';
            $fieldName = S_GET('field_name') ?? '';

            if (empty($parentPosttype) || empty($fieldName)) {
                return $this->error('Missing required parameters', [], 422);
            }

            // Call service
            $result = $this->fieldsService->variations_attrs_get($parentPosttype, $fieldName);

            if ($result['success']) {
                return $this->success($result['data'], 'Attributes retrieved');
            } else {
                return $this->error($result['message'] ?? 'Failed', [], 404);
            }
        } catch (\Exception $e) {
            return $this->error('Internal server error', [], 500);
        }
    }

    /**
     * Add attribute values (terms only, no variations)
     * 
     * POST /api/v0/fields/variation-attributes-add-values
     * Body: {parent_posttype, field_name, attributes}
     */
    public function variation_attributes_add_values()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', [], 405);
            }

            // Validate required
            if (empty(S_POST('parent_posttype')) || empty(S_POST('field_name')) || empty(S_POST('attributes'))) {
                return $this->error('Missing required parameters', [], 422);
            }

            // TODO: Implement in service
            return $this->error('Not implemented yet', [], 501);
        } catch (\Exception $e) {
            return $this->error('Internal server error', [], 500);
        }
    }

    // =========================================================================
    // TERMS / TAXONOMY FIELD
    // =========================================================================

    /**
     * Search terms for Taxonomy field selection
     * 
     * GET /api/v0/fields/terms-search/{posttype}/{taxonomy}
     * Query: lang (or post_lang)
     * 
     * Example: GET /api/v0/fields/terms-search/products/brands?post_lang=en
     */
    public function terms_search($posttype = null, $taxonomy = null)
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            // Validate required parameters
            if (empty($posttype) || empty($taxonomy)) {
                return $this->error('posttype and taxonomy are required', [], 422);
            }

            $lang = S_GET('lang') ?? S_GET('post_lang') ?? 'all';

            // Call service
            $result = $this->fieldsService->terms_search($posttype, $taxonomy, $lang);

            if ($result['success']) {
                return $this->success($result['data'], 'Terms retrieved');
            } else {
                return $this->error($result['message'] ?? 'Failed to get terms', [], 400);
            }
        } catch (\Exception $e) {
            error_log("FieldsController::terms_search error: " . $e->getMessage());
            return $this->error('Internal server error', [], 500);
        }
    }

    /**
     * Create term
     * 
     * POST /api/v0/fields/terms-create
     * Body: {name, slug, posttype, taxonomy, lang, parent, description, seo_title, seo_desc, status}
     */
    public function terms_create()
    {
        try {
            // ✅ Check permission (add hoặc edit - dùng cho form post bài)
            try {
                $this->requirePermission('add', $this->postsControllerName);
            } catch (\System\Core\AppException $e) {
                try {
                    $this->requirePermission('edit', $this->postsControllerName);
                } catch (\System\Core\AppException $e2) {
                    return $this->error($e2->getMessage(), [], 403);
                }
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->error('Method not allowed', [], 405);
            }

            // Validate required
            if (empty(S_POST('name')) || empty(S_POST('posttype')) || empty(S_POST('taxonomy'))) {
                return $this->error('name, posttype and taxonomy are required', [], 422);
            }

            // ✅ Validate name length (giống TermsController)
            $name = trim(S_POST('name'));
            if (strlen($name) < 2 || strlen($name) > 100) {
                return $this->error('Name length must be between 2 and 100 characters', [
                    'name' => ['Name length must be between 2 and 100 characters']
                ], 422);
            }

            // ✅ Prepare term data (tương tự PostsController + TermsController)
            $termData = [
                'name' => $name,
                'slug' => !empty(S_POST('slug')) ? S_POST('slug') : url_slug($name) ?? null, // Let FieldsService auto-generate
                'description' => S_POST('description') ?? '',
                'type' => S_POST('taxonomy'), // taxonomy = type
                'posttype' => S_POST('posttype'),
                'lang' => S_POST('lang') ?? S_POST('post_lang') ?? 'all',
                'parent' => !empty(S_POST('parent')) ? (int)S_POST('parent') : null,
                'id_main' => !empty(S_POST('id_main')) ? (int)S_POST('id_main') : 0,
                'seo_title' => S_POST('seo_title') ?? '',
                'seo_desc' => S_POST('seo_desc') ?? '',
                'status' => S_POST('status') ?? 'active'
            ];

            // ✅ Call FieldsService (which calls TermsService)
            // FieldsService sẽ:
            // - Validate đầy đủ (name length, posttype exists, parent valid)
            // - Auto-generate slug nếu empty
            // - Normalize parent và id_main
            // - Gọi TermsService.create() để:
            //   + Validation layer 2 (TermsValidationService)
            //   + Generate UNIQUE slug (tránh trùng)
            //   + Fire events
            $result = $this->fieldsService->terms_create($termData);

            if ($result['success']) {
                return $this->success(
                    $result['data'] ?? ['id' => $result['term_id'] ?? 0],
                    'Term created successfully',
                    201
                );
            } else {
                return $this->error(
                    $result['message'] ?? 'Failed to create term',
                    $result['errors'] ?? [],
                    400
                );
            }
        } catch (\Exception $e) {
            error_log("FieldsController::terms_create error: " . $e->getMessage());
            return $this->error('Internal server error', [], 500);
        }
    }
}
