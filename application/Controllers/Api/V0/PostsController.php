<?php

namespace App\Controllers\Api\V0;

use App\Controllers\ApiController;
use App\Services\Posts\PostsService;
use App\Services\Terms\TermsService;
use App\Models\TermsModel;
use System\Database\DB;

/**
 * PostsController - RESTful API v0
 * 
 * Complete REST API for managing posts, terms, and taxonomies.
 * Supports dynamic post types with full CRUD operations.
 * 
 * =============================================================================
 * AUTHENTICATION
 * =============================================================================
 * 
 * All write operations (POST, PUT, DELETE) require authentication via:
 * 1. Bearer Token: Authorization: Bearer {token}
 * 2. Session Cookie: For web clients
 * 
 * =============================================================================
 * ENDPOINTS
 * =============================================================================
 * 
 * POSTS:
 * ------
 * GET    /api/v0/posts/{posttype}
 *        List posts with filtering, sorting, and pagination
 *        Query params:
 *          - sort: string (e.g., "title", "-created_at", "+price")
 *                  Prefix: + or none = ASC, - = DESC
 *          - author: string (e.g., "1,5,9") - Filter by author IDs
 *          - categories: string (e.g., "5,7,9") - Filter by category IDs
 *          - tags: string (e.g., "3,6,8") - Filter by tag IDs
 *          - search: string - Search in title and search_string
 *          - status: string (default: "active") - Filter by status
 *          - lang: string (default: APP_LANG) - Language code
 *          - limit: int (default: 20, max: 100) - Items per page
 *          - after: string - Cursor for next page
 *          - before: string - Cursor for previous page
 *          - cursor_id: int - Cursor ID for tie-breaking
 *          - select: string (e.g., "id,title,slug" or "*")
 *                    Default: all fields except WYSIWYG/heavy fields
 * 
 * POST   /api/v0/posts/{posttype}
 *        Create new post
 *        Auth: Required (Bearer token)
 *        Permissions: author, moderator, admin
 *        Body (JSON or form-data):
 *          - title: string (required)
 *          - slug: string (auto-generated if not provided)
 *          - status: string (default: "draft")
 *          - author: int (admin/moderator only, others use own ID)
 *          - lang: string (default: APP_LANG)
 *          - ... other fields based on posttype
 * 
 * GET    /api/v0/posts/{posttype}/{id|slug}
 *        Get single post by ID or slug
 *        Query params:
 *          - lang: string (default: APP_LANG)
 * 
 * PUT    /api/v0/posts/{posttype}/{id|slug}
 *        Update post by ID or slug
 *        Auth: Required (Bearer token)
 *        Permissions: owner, admin, moderator
 *        Body (JSON):
 *          - Fields to update
 *          - author: int (admin/moderator only)
 *          - lang: string
 * 
 * DELETE /api/v0/posts/{posttype}/{id|slug}
 *        Delete post by ID or slug
 *        Auth: Required (Bearer token)
 *        Permissions: owner, admin, moderator
 *        Query params:
 *          - lang: string (default: APP_LANG)
 * 
 * TERMS:
 * ------
 * GET    /api/v0/posts/{posttype}/terms/
 *        List all term types for posttype
 * 
 * POST   /api/v0/posts/{posttype}/terms/
 *        Create new term type (Admin only)
 *        Status: Not implemented (501)
 * 
 * GET    /api/v0/posts/{posttype}/terms/{term_slug}
 *        List posts filtered by term
 *        Same query params as GET /api/v0/posts/{posttype}
 * 
 * POST   /api/v0/posts/{posttype}/terms/{term_slug}
 *        Create new term item
 *        Auth: Required (Bearer token)
 *        Permissions: admin, moderator
 *        Body (JSON or form-data):
 *          - name: string (required)
 *          - slug: string (auto-generated if not provided)
 *          - description: string
 *          - parent: int (parent term ID)
 *          - id_main: int (main term ID for multilingual)
 *          - seo_title: string
 *          - seo_desc: string
 *          - status: string (default: "active")
 *          - lang: string (default: APP_LANG)
 * 
 * =============================================================================
 * EXAMPLES
 * =============================================================================
 * 
 * List products with filters:
 *   GET /api/v0/posts/products?sort=-created_at&categories=5,7&limit=20
 * 
 * Create post (with auth):
 *   POST /api/v0/posts/blogs
 *   Headers: Authorization: Bearer {token}
 *   Body: {"title": "My Blog Post", "status": "draft"}
 * 
 * Get post by slug:
 *   GET /api/v0/posts/products/iphone-15-pro
 * 
 * Update post:
 *   PUT /api/v0/posts/blogs/123
 *   Headers: Authorization: Bearer {token}
 *   Body: {"title": "Updated Title", "status": "active"}
 * 
 * Delete post:
 *   DELETE /api/v0/posts/blogs/my-slug
 *   Headers: Authorization: Bearer {token}
 * 
 * Get posts by category:
 *   GET /api/v0/posts/products/terms/electronics
 * 
 * Create category:
 *   POST /api/v0/posts/products/terms/category
 *   Headers: Authorization: Bearer {token}
 *   Body: {"name": "Electronics", "slug": "electronics"}
 * 
 * =============================================================================
 * RESPONSE FORMAT
 * =============================================================================
 * 
 * Success (200):
 * {
 *   "success": true,
 *   "message": "Posts retrieved",
 *   "data": {...},
 *   "timestamp": 1234567890
 * }
 * 
 * Error (4xx, 5xx):
 * {
 *   "success": false,
 *   "message": "Error message",
 *   "errors": {...},
 *   "timestamp": 1234567890
 * }
 * 
 * Pagination response:
 * {
 *   "data": [...],
 *   "pagination": {
 *     "has_next": true,
 *     "limit": 20,
 *     "cursor": "2025-12-05",
 *     "cursor_id": 123,
 *     "page": 1
 *   }
 * }
 * 
 * @package App\Controllers\Api\V0
 * @version 0.1.0
 */
class PostsController extends ApiController
{
    /** @var PostsService */
    protected $postsService;

    /** @var TermsService */
    protected $termsService;

    /** @var TermsModel */
    protected $termsModel;

    public function __construct()
    {
        parent::__construct();
        load_helpers(['string', 'query', 'posts', 'terms']);

        $this->postsService = new PostsService();
        $this->termsService = new TermsService();
        $this->termsModel = new TermsModel();
        $this->postsControllerName = 'App\Controllers\Backend\PostsController';
        $this->termsControllerName = 'App\Controllers\Backend\TermsController';
    }

    /**
     * Main router - Routes requests to appropriate handlers
     * 
     * @param string $posttype Posttype slug
     * @param string|null $param1 ID/slug or 'terms'
     * @param string|null $param2 Term slug (if param1 is 'terms')
     * @return void JSON response
     */
    public function index($posttype = '', $param1 = null, $param2 = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];

            // Route: /api/v0/posts/{posttype}
            if ($param1 === null) {
                if ($method === 'GET') {
                    return $this->_list($posttype);
                } elseif ($method === 'POST') {
                    try {
                        $this->requirePermission('add', $this->postsControllerName);
                    } catch (\System\Core\AppException $e) {
                        return $this->error($e->getMessage(), [], 403);
                    }
                    return $this->_create($posttype);
                }
            }

            // Route: /api/v0/posts/{posttype}/terms
            if ($param1 === 'terms' && $param2 === null) {
                if ($method === 'GET') {
                    return $this->_term_types($posttype);
                } elseif ($method === 'POST') {
                    try {
                        $this->requirePermission('manage', $this->termsControllerName);
                    } catch (\System\Core\AppException $e) {
                        return $this->error($e->getMessage(), [], 403);
                    }
                    return $this->_term_type_create($posttype);
                }
            }

            // Route: /api/v0/posts/{posttype}/terms/{slug}
            if ($param1 === 'terms' && $param2 !== null) {
                if ($method === 'GET') {
                    return $this->_posts_by_term($posttype, $param2);
                } elseif ($method === 'POST') {
                    try {
                        $this->requirePermission('add', $this->termsControllerName);
                    } catch (\System\Core\AppException $e) {
                        return $this->error($e->getMessage(), [], 403);
                    }
                    return $this->_term_create($posttype, $param2);
                }
            }

            // Route: /api/v0/posts/{posttype}/{id|slug}/views
            if ($param1 !== null && $param1 !== 'terms' && $param2 === 'views') {
                if ($method === 'PUT') {
                    return $this->_update_views($posttype, $param1);
                }
            }

            // Route: /api/v0/posts/{posttype}/{id|slug}/rating
            if ($param1 !== null && $param1 !== 'terms' && $param2 === 'rating') {
                if ($method === 'PUT') {
                    return $this->_update_rating($posttype, $param1);
                }
            }

            // Route: /api/v0/posts/{posttype}/{id|slug}
            if ($param1 !== null && $param1 !== 'terms' && $param2 === null) {
                if ($method === 'GET') {
                    return $this->_detail($posttype, $param1);
                } elseif ($method === 'PUT') {
                    try {
                        $this->requirePermission('edit', $this->postsControllerName);
                    } catch (\System\Core\AppException $e) {
                        return $this->error($e->getMessage(), [], 403);
                    }
                    return $this->_update($posttype, $param1);
                } elseif ($method === 'DELETE') {
                    try {
                        $this->requirePermission('delete', $this->postsControllerName);
                    } catch (\System\Core\AppException $e) {
                        return $this->error($e->getMessage(), [], 403);
                    }
                    return $this->_delete($posttype, $param1);
                }
            }

            return $this->error(__('Invalid endpoint'), [], 404);
        } catch (\Exception $e) {
            error_log("PostsController v0 error: " . $e->getMessage());
            return $this->error(__('Internal server error'), [], 500);
        }
    }

    // =========================================================================
    // POSTS ENDPOINTS
    // =========================================================================


    /**
     * Check if current user has manage permission
     * 
     * @return bool
     */
    protected function hasManagePermission()
    {
        return $this->checkPermission('manage', $this->postsControllerName);
    }

    protected function canAccessPost($post = null, $args = []){
        if ($this->hasManagePermission()) return true;
        if (empty(current_user_id())) return false;
        
        if (is_numeric($post)) {
            $args = array_merge($args, ['id' => $post, 'post_status' => '']);
            $post = get_post($args);
        }
        if (empty($post) || !isset($post['id'])) {
            return false;
        }
        if (isset($post['id'])){
            if (isset($post['user_id'])){
                if (current_user_id() == $post['user_id']) return true;
            }
            if (isset($post['author'])){
                if (current_user_id() == $post['author']) return true;
            }
        }
        
        return false;
    }

    /**
     * List posts with filters and pagination
     * 
     * GET /api/v0/posts/{posttype}
     * 
     * @param string $posttype Posttype slug
     * @return void JSON response
     */
    protected function _list($posttype)
    {
        // Check permission
        try {
            $this->requirePermission('index', $this->postsControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        if (empty($posttype)) {
            return $this->error(__('Posttype is required'), [], 422);
        }

        // Get posttype config
        $posttypeData = posttype_active($posttype);
        if (empty($posttypeData)) {
            return $this->error(__('Posttype not found or not active'), [], 404);
        }

        $fields = _json_decode($posttypeData['fields']);
        $fieldNames = array_column($fields, 'field_name');

        // Build filters - filter by user if no manage permission
        if (!$this->hasManagePermission()) {
            $currentUserId = current_user_id();
            if ($currentUserId) {
                // Check which field exists in posttype (user_id, author, vendor)
                if (in_array('user_id', $fieldNames)) {
                    $args['filters'][] = ['user_id', $currentUserId, '='];
                } elseif (in_array('author', $fieldNames)) {
                    $args['filters'][] = ['author', $currentUserId, '='];
                } elseif (in_array('vendor', $fieldNames)) {
                    $args['filters'][] = ['vendor', $currentUserId, '='];
                }
            }
        }

        // Parse query parameters
        $limit = (int)(S_GET('limit') ?? 20);
        $search = S_GET('search') ?? '';
        $select = S_GET('select') ?? '';
        $sortParam = S_GET('sort') ?? '';

        // Build args for get_posts()
        $args = [
            'post_type' => $posttype,
            'lang' => S_GET('lang') ?? APP_LANG,
            'post_status' => S_GET('status') ?? 'active',
            'limit' => min($limit, 100),
            'orderby' => 'id',
            'order' => 'DESC',
            'with_terms' => S_GET('terms') ?? false
        ];

        // Parse sort: field_name or -field_name
        if (!empty($sortParam)) {
            $sortFields = array_map('trim', explode(',', $sortParam));

            if (!empty($sortFields)) {
                $firstField = $sortFields[0];

                // Check for - prefix (DESC)
                if (substr($firstField, 0, 1) === '-') {
                    $fieldName = substr($firstField, 1);
                    $direction = 'DESC';
                } else {
                    // Remove + prefix if exists
                    $fieldName = ltrim($firstField, '+');
                    $direction = 'ASC';
                }

                // Validate field exists in posttype
                if (in_array($fieldName, $fieldNames)) {
                    $args['orderby'] = $fieldName;
                    $args['order'] = $direction;
                }
            }
        }

        // Parse author filter
        if (!empty(S_GET('author'))) {
            $authorIds = array_map('intval', explode(',', S_GET('author')));
            $authorIds = array_filter($authorIds);

            if (!empty($authorIds)) {
                if (count($authorIds) > 1) {
                    $args['filters'][] = ['user_id', $authorIds, 'IN'];
                } else {
                    $args['filters'][] = ['user_id', $authorIds[0], '='];
                }
            }
        }

        // Parse category filter
        if (!empty(S_GET('categories'))) {
            $categoryIds = array_map('intval', explode(',', S_GET('categories')));
            $categoryIds = array_filter($categoryIds);

            if (!empty($categoryIds)) {
                $args['category__in'] = $categoryIds;
            }
        }

        // Parse tag filter
        if (!empty(S_GET('tags'))) {
            $tagIds = array_map('intval', explode(',', S_GET('tags')));
            $tagIds = array_filter($tagIds);

            if (!empty($tagIds)) {
                $args['tag__in'] = $tagIds;
            }
        }

        // Search
        if (!empty($search)) {
            $args['search'] = $search;
            $args['search_columns'] = ['title', 'search_string'];
        }

        // Cursor pagination (depends on orderby column)
        if (!empty(S_GET('after'))) {
            $args['cursor'] = S_GET('after');
            if (!empty(S_GET('cursor_id'))) {
                $args['cursor_id'] = (int)S_GET('cursor_id');
            }
        } elseif (!empty(S_GET('before'))) {
            $args['cursor'] = S_GET('before');
            if (!empty(S_GET('cursor_id'))) {
                $args['cursor_id'] = (int)S_GET('cursor_id');
            }
        }

        // Get posts via helper
        $result = get_posts($args);
        $posts = $result['data'] ?? [];

        // Field selection
        if (!empty($select)) {
            $posts = $this->selectFields($posts, $select, $fields);
        } else {
            // Default: exclude heavy fields (WYSIWYG, etc.)
            $posts = $this->excludeHeavyFields($posts, $fields);
        }

        // Build response with pagination info
        $response = [
            'data' => $posts,
            'pagination' => [
                'has_next' => $result['is_next'] ?? false,
                'limit' => $args['limit']
            ]
        ];

        // Add cursor info if available
        if (isset($result['cursor'])) {
            $response['pagination']['cursor'] = $result['cursor'];
            $response['pagination']['cursor_id'] = $result['cursor_id'] ?? null;
        }

        // Add page info if using offset pagination
        if (isset($result['page'])) {
            $response['pagination']['page'] = $result['page'];
        }

        return $this->success($response, 'Posts retrieved');
    }

    /**
     * Create new post
     * 
     * POST /api/v0/posts/{posttype}
     * 
     * @param string $posttype Posttype slug
     * @return void JSON response
     */
    protected function _create($posttype)
    {
        // Validate authentication
        $user = $this->_auth();
        if (!$user) {
            return $this->error(__('Unauthorized - Invalid or missing token'), [], 401);
        }

        $input = $_POST;

        if (empty($input)) {
            return $this->error(__('Request body is required'), [], 422);
        }

        $lang = $input['lang'] ?? APP_LANG;

        // ✅ Load lightweight posttype config (không cần loadFormData - quá nặng)
        $posttypeConfig = posttype_active($posttype);

        if (empty($posttypeConfig)) {
            return $this->error(__('Posttype not found or not active'), [], 404);
        }

        // ✅ Build data ĐỘNG - chỉ set fields NẾU CÓ trong posttype
        $postData = [];

        if (!empty($posttypeConfig['fields'])) {
            $haveSetUserId = false;
            foreach ($posttypeConfig['fields'] as $field) {
                $fieldName = $field['field_name'] ?? '';
                $fieldType = $field['type'] ?? 'Text';

                if (empty($fieldName)) {
                    continue;
                }

                // Skip ID (auto-increment)
                if ($fieldName === 'id') {
                    continue;
                }

                if ($fieldType === 'Reference') {
                    $referenceField = $field['reference'] ?? [];
                    if (!empty($referenceField) && !empty($referenceField['selectionMode']) && $referenceField['selectionMode'] === 'single') {
                        $postData[$fieldName] = (int)$input[$fieldName] ?? null;
                    }
                    continue;
                }

                // ✅ XỬ LÝ SPECIAL FIELDS
                if ($fieldName === 'status') {
                    // User provided status
                    if (isset($input['status'])) {
                        $postData['status'] = $input['status'];
                        
                        // Check changestatus permission if trying to set non-draft status
                        if (!in_array($input['status'], ['draft', 'pending'])) {
                            try {
                                $this->requirePermission('changestatus', $this->postsControllerName);
                            } catch (\System\Core\AppException $e) {
                                // No changestatus permission - force to pending or draft
                                $postData['status'] = 'pending';
                            }
                        }
                    } else {
                        // No status provided - use default
                        $postData['status'] = null;
                        if (isset($field['default_value']) && !empty($field['default_value'])) {
                            $postData['status'] = $field['default_value'];
                        } else {
                            if (isset($field['options']) && is_array($field['options']) && count($field['options']) > 0) {
                                foreach ($field['options'] as $option) {
                                    $postData['status'] = $option['value'];
                                    if (isset($option['value']) && $option['value'] === 'draft') {
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    continue;
                }

                // Set user_id/author của bài viết
                if (in_array($fieldName, ['user_id', 'author'])) {
                    if ($this->hasManagePermission()) {
                        $postData[$fieldName] = $input[$fieldName] ?? $user['id'];
                        $haveSetUserId = true;
                    } else {
                        $postData[$fieldName] = $user['id'];
                        $haveSetUserId = true;
                    }
                    continue;
                }

                if ($fieldName === 'created_at') {
                    // created_at exists → set from input or now
                    $postData['created_at'] = isset($input['created_at']) && !empty($input['created_at']) 
                        ? _DateTime($input['created_at']) 
                        : _DateTime();
                    continue;
                }

                if ($fieldName === 'updated_at') {
                    // updated_at exists → set to now
                    $postData['updated_at'] = _DateTime();
                    continue;
                }

                // ✅ NORMAL FIELDS → get from input or NULL
                $postData[$fieldName] = $input[$fieldName] ?? null;
            }
        }

        // Create via service
        $result = $this->postsService->create($posttype, $postData, $lang);

        if ($result['success']) {
            return $this->success([
                'id' => $result['post_id']
            ], 'Post created', 201);
        } else {
            return $this->error(__('Failed to create post'), $result['errors'], 400);
        }
    }

    /**
     * Get single post by ID or slug
     * 
     * GET /api/v0/posts/{posttype}/{id|slug}
     * 
     * @param string $posttype Posttype slug
     * @param string|int $idOrSlug Post ID or slug
     * @return void JSON response
     */
    protected function _detail($posttype, $idOrSlug)
    {
        $args = [
            'post_type' => $posttype,
            'lang' => S_GET('lang') ?? APP_LANG,
            'with_terms' => true
        ];

        // Determine if ID or slug
        if (is_numeric($idOrSlug)) {
            $args['id'] = (int)$idOrSlug;
        } else {
            $args['slug'] = $idOrSlug;
        }

        $post = get_post($args);

        if (empty($post)) {
            return $this->error(__('Post not found'), [], 404);
        }

        return $this->success($post, 'Post retrieved');
    }

    /**
     * Update post by ID or slug
     * 
     * PUT /api/v0/posts/{posttype}/{id|slug}
     * 
     * @param string $posttype Posttype slug
     * @param string|int $idOrSlug Post ID or slug
     * @return void JSON response
     */
    protected function _update($posttype, $idOrSlug)
    {
        // Validate authentication
        $user = $this->_auth();
        if (!$user) {
            return $this->error(__('Unauthorized - Invalid or missing token'), [], 401);
        }

        // Parse PUT data (JSON)
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($input)) {
            return $this->error(__('Request body is required'), [], 422);
        }

        // Find post
        $lang = $input['lang'] ?? APP_LANG;

        if (is_numeric($idOrSlug)) {
            $postId = (int)$idOrSlug;
            $post = get_post([
                'id' => $postId,
                'post_type' => $posttype,
                'post_status' => '',
                'lang' => $lang
            ]);

            if (empty($post)) {
                return $this->error(__('Post not found'), [], 404);
            }
        } else {
            // Find by slug
            if (empty($idOrSlug)) {
                return $this->error(__('Slug cannot be empty (draft posts)'), [], 422);
            }

            $post = get_post([
                'slug' => $idOrSlug,
                'post_type' => $posttype,
                'post_status' => '',
                'lang' => $lang
            ]);

            if (empty($post)) {
                return $this->error(__('Post not found'), [], 404);
            }

            $postId = $post['id'];
        }

        // Check access permission
        if (!$this->canAccessPost($post)) {
            return $this->error(__('Permission denied - You do not have permission to edit this post'), [], 403);
        }

        // ✅ Load lightweight posttype config (không cần loadFormData - quá nặng)
        $posttypeConfig = posttype_active($posttype);

        if (empty($posttypeConfig)) {
            return $this->error(__('Posttype not found or not active'), [], 404);
        }

        // ✅ Merge input với existing post - giữ lại giá trị cũ nếu field không được truyền
        $updateData = [];

        if (!empty($posttypeConfig['fields'])) {
            foreach ($posttypeConfig['fields'] as $field) {
                $fieldName = $field['field_name'] ?? '';
                $fieldType = $field['type'] ?? 'Text';

                if (empty($fieldName)) {
                    continue;
                }

                // Skip ID (immutable)
                if ($fieldName === 'id') {
                    continue;
                }

                // Handle Reference fields
                if ($fieldType === 'Reference') {
                    $referenceField = $field['reference'] ?? [];
                    if (!empty($referenceField) && !empty($referenceField['selectionMode']) && $referenceField['selectionMode'] === 'single') {
                        $updateData[$fieldName] = isset($input[$fieldName]) ? (int)$input[$fieldName] : ($post[$fieldName] ?? null);
                    }
                    continue;
                }

                // Skip Taxonomy fields
                if ($fieldType === 'Taxonomy') {
                    continue;
                }

                // ✅ XỬ LÝ SPECIAL FIELDS
                if ($fieldName === 'status') {
                    // Check changestatus permission if status is being changed
                    if (isset($input['status']) && isset($post['status']) && $input['status'] !== $post['status']) {
                        try {
                            $this->requirePermission('changestatus', $this->postsControllerName);
                            $updateData['status'] = $input['status'];
                        } catch (\System\Core\AppException $e) {
                            // No changestatus permission - handle based on current status
                            if ($post['status'] == 'draft') {
                                if ($input['status'] == 'active') {
                                    $updateData['status'] = 'pending';
                                } else {
                                    $updateData['status'] = $input['status'];
                                }
                            } else {
                                // Keep original status
                                $updateData['status'] = $post['status'];
                            }
                        }
                    } else {
                        // Status not changed or not provided - keep original
                        $updateData['status'] = $input['status'] ?? $post['status'];
                    }
                    continue;
                }

                // Handle user_id/author/vendor assignment
                if (in_array($fieldName, ['user_id', 'author'])) {
                    if (!$this->hasManagePermission()) {
                        // No manage permission → cannot change ownership
                        $updateData[$fieldName] = $post[$fieldName] ?? $user['id'];
                    } else {
                        // Has manage permission → can set to any value, but preserve original if not provided
                        $updateData[$fieldName] = $input[$fieldName] ?? ($post[$fieldName] ?? null);
                    }
                    continue;
                }

                if ($fieldName === 'created_at') {
                    // Preserve created_at from existing post (or set from input if admin)
                    if ($this->hasManagePermission() && isset($input['created_at']) && !empty($input['created_at'])) {
                        $updateData['created_at'] = _DateTime($input['created_at']);
                    } else {
                        $updateData['created_at'] = $post['created_at'] ?? (_DateTime($input['created_at']) ?? _DateTime());
                    }
                    continue;
                }

                if ($fieldName === 'updated_at') {
                    // updated_at always set to now
                    $updateData['updated_at'] = _DateTime();
                    continue;
                }

                // ✅ NORMAL FIELDS → get from input OR keep existing value
                if (array_key_exists($fieldName, $input)) {
                    $updateData[$fieldName] = $input[$fieldName];
                } else {
                    // Field not in input - keep existing value
                    $updateData[$fieldName] = $post[$fieldName] ?? null;
                }
            }
        }

        // Update via service
        $result = $this->postsService->update($posttype, $postId, $updateData, $lang);

        if ($result['success']) {
            return $this->success(['id' => $postId], 'Post updated');
        } else {
            return $this->error(__('Failed to update post'), $result['errors'], 400);
        }
    }

    /**
     * Delete post by ID or slug
     * 
     * DELETE /api/v0/posts/{posttype}/{id|slug}
     * 
     * @param string $posttype Posttype slug
     * @param string|int $idOrSlug Post ID or slug
     * @return void JSON response
     */
    protected function _delete($posttype, $idOrSlug)
    {
        // Validate authentication
        $user = $this->_auth();
        if (!$user) {
            return $this->error(__('Unauthorized - Invalid or missing token'), [], 401);
        }

        $lang = S_GET('post_lang') ?? S_GET('lang') ?? APP_LANG;

        $checkPosttype = posttype_lang_exists($posttype, $lang);
        if (!$checkPosttype){
            return $this->error(__('Posttype not found or not have this language'), [], 404);
        }

        // Find post
        $post = null;
        if (is_numeric($idOrSlug)) {
            $postId = (int)$idOrSlug;
            $post = get_post([
                'id' => $postId,
                'post_type' => $posttype,
                'post_status' => '',
                'lang' => $lang
            ]);
        } else {
            // Find by slug
            if (empty($idOrSlug)) {
                return $this->error(__('Slug cannot be empty (draft posts)'), [], 422);
            }

            $post = get_post([
                'slug' => $idOrSlug,
                'post_type' => $posttype,
                'post_status' => '',
                'lang' => $lang
            ]);

            if (empty($post)) {
                return $this->error(__('Post not found'), [], 404);
            }

            $postId = $post['id'];
        }

        if (empty($post)) {
            return $this->error(__('Post not found'), [], 404);
        }

        // Check access permission
        if (!$this->canAccessPost($post)) {
            return $this->error(__('Permission denied - You do not have permission to delete this post'), [], 403);
        }

        // Delete via service
        $result = $this->postsService->delete($posttype, $postId, $lang);

        if ($result['success']) {
            return $this->success(['id' => $postId], 'Post deleted');
        } else {
            return $this->error(__('Failed to delete post'), [], 500);
        }
    }

    // =========================================================================
    // TERMS ENDPOINTS
    // =========================================================================

    /**
     * List term types for posttype
     * 
     * GET /api/v0/posts/{posttype}/terms/
     * 
     * @param string $posttype Posttype slug
     * @return void JSON response
     */
    protected function _term_types($posttype)
    {
        $posttypeData = posttype_active($posttype);
        if (empty($posttypeData)) {
            return $this->error(__('Posttype not found or not active'), [], 404);
        }

        $terms = _json_decode($posttypeData['terms']);

        return $this->success($terms, 'Term types retrieved');
    }

    /**
     * Create term type (Admin only)
     * 
     * POST /api/v0/posts/{posttype}/terms/
     * 
     * @param string $posttype Posttype slug
     * @return void JSON response
     */
    protected function _term_type_create($posttype)
    {
        // TODO: Implement term type creation (requires posttype update)
        return $this->error(__('Not implemented yet'), [], 501);
    }

    /**
     * List posts filtered by term
     * 
     * GET /api/v0/posts/{posttype}/terms/{term_slug}
     * 
     * @param string $posttype Posttype slug
     * @param string $termSlug Term slug
     * @return void JSON response
     */
    protected function _posts_by_term($posttype, $termType)
    {
        // Get term type from posttype config
        $posttypeData = posttype_active($posttype);
        if (empty($posttypeData)) {
            return $this->error(__('Posttype not found or not active'), [], 404);
        }

        $terms = _json_decode($posttypeData['terms']);
        $termTypeExists = false;
        foreach ($terms as $t) {
            if ($t['type'] == $termType) {
                $termTypeExists = true;
                break;
            }
        }
        if (!$termTypeExists) {
            return $this->error(__('Term type not found'), [], 404);
        }
        // Get term by slug
        $terms = get_terms(['taxonomy' => $termType, 'post_type' => $posttype, 'lang' => S_GET('lang') ?? APP_LANG]);
        if (empty($terms)) {
            return $this->error(__('Term not found'), [], 404);
        }

        return $this->_list($posttype);
    }

    /**
     * Create term item
     * 
     * POST /api/v0/posts/{posttype}/terms/{term_type}
     * 
     * @param string $posttype Posttype slug
     * @param string $termType Term type
     * @return void JSON response
     */
    protected function _term_create($posttype, $termType)
    {
        // Validate authentication
        $user = $this->_auth();
        if (!$user) {
            return $this->error(__('Unauthorized - Invalid or missing token'), [], 401);
        }

        // Check permission using helper
        try {
            $this->requirePermission('add', $this->termsControllerName);
        } catch (\System\Core\AppException $e) {
            return $this->error($e->getMessage(), [], 403);
        }

        $input = $_POST;

        if (empty($input['name'])) {
            return $this->error(__('name is required'), [], 422);
        }

        // Prepare term data
        $termData = [
            'name' => $input['name'],
            'slug' => $input['slug'] ?? url_slug($input['name']),
            'description' => $input['description'] ?? '',
            'type' => $termType,
            'posttype' => $posttype,
            'lang' => $input['lang'] ?? 'all',
            'parent' => $input['parent'] ?? null,
            'id_main' => $input['id_main'] ?? 0,
            'seo_title' => $input['seo_title'] ?? '',
            'seo_desc' => $input['seo_desc'] ?? '',
            'status' => $input['status'] ?? 'active'
        ];

        // Create via service
        $result = $this->termsService->create($termData);

        if ($result['success']) {
            return $this->success([
                'id' => $result['term_id'] ?? 0
            ], 'Term created', 201);
        } else {
            return $this->error(__('Failed to create term'), $result['errors'], 400);
        }
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Select specific fields from posts
     * 
     * @param array $posts Posts array
     * @param string $select Comma-separated field names or "*"
     * @param array $fields Posttype fields configuration
     * @return array Filtered posts
     */
    protected function selectFields($posts, $select, $fields)
    {
        if ($select === '*') {
            return $posts;
        }

        $selectedFields = array_map('trim', explode(',', $select));

        // Validate fields exist in posttype
        $fieldNames = array_column($fields, 'field_name');
        $selectedFields = array_filter($selectedFields, function ($f) use ($fieldNames) {
            return in_array($f, $fieldNames) || $f === 'id';
        });

        // Always include 'id'
        if (!in_array('id', $selectedFields)) {
            array_unshift($selectedFields, 'id');
        }

        // Filter posts to only selected fields
        $filtered = [];
        foreach ($posts as $post) {
            $item = [];
            foreach ($selectedFields as $field) {
                $item[$field] = $post[$field] ?? null;
            }
            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * Update post views
     * 
     * PUT /api/v0/posts/{posttype}/{id|slug}/views
     * 
     * Increments view counters: views, views_day, views_week, views_month, views_year, views_total
     * 
     * @param string $posttype Posttype slug
     * @param string|int $idOrSlug Post ID or slug
     * @return void JSON response
     */
    protected function _update_views($posttype, $idOrSlug)
    {
        try {
            if (empty($posttype)) {
                return $this->error(__('Posttype is required'), [], 422);
            }

            $lang = S_GET('lang') ?? APP_LANG;

            // Get post using helper (automatically checks posttype via posttype_name)
            $post = get_post($idOrSlug, $posttype, [
                'lang' => $lang,
                'post_status' => ''
            ]);

            if (empty($post)) {
                return $this->error(__('Post not found'), [], 404);
            }

            $postId = $post['id'];

            // Build update data for view counters
            $updateData = [];

            // Check and increment each view counter if column exists
            $viewColumns = ['views', 'views_day', 'views_week', 'views_month', 'views_year'];

            foreach ($viewColumns as $column) {
                //dùng isset không hợp lý nhiều khi value là null isset check không được
                if (array_key_exists($column, $post)) {
                    $updateData[$column] = (int)$post[$column] + 1;
                }
            }

            if (empty($updateData)) {
                return $this->error(__('No view counters available for this posttype'), [], 400);
            }

            // Get table name and update
            $tableName = posttype_name($posttype, $lang);
            $updated = DB::table($tableName)
                ->where('id', $postId)
                ->update($updateData);

            if ($updated) {
                $updateData['id'] = $postId;
                return $this->success($updateData, 'Views updated successfully');
            } else {
                return $this->error(__('Failed to update views'), [], 500);
            }
        } catch (\Exception $e) {
            error_log("PostsController v0 _update_views error: " . $e->getMessage());
            return $this->error(__('Internal server error'), [], 500);
        }
    }

    /**
     * Update post rating
     * 
     * PUT /api/v0/posts/{posttype}/{id|slug}/rating
     * 
     * Updates rating counters: rating_avg, rating_count, rating_total
     * Body (JSON):
     *   - rating: float (required) - Rating value (typically 1-5)
     *   - action: string (optional) - 'add' (default) or 'remove'
     * 
     * @param string $posttype Posttype slug
     * @param string|int $idOrSlug Post ID or slug
     * @return void JSON response
     */
    protected function _update_rating($posttype, $idOrSlug)
    {
        try {
            if (empty($posttype)) {
                return $this->error(__('Posttype is required'), [], 422);
            }

            // Parse PUT data (JSON)
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (empty($input['rating']) || !is_numeric($input['rating'])) {
                return $this->error(__('Rating is required and must be numeric'), [], 422);
            }

            $rating = (float)$input['rating'];
            $action = $input['action'] ?? 'add'; // 'add' or 'remove'

            // Validate rating range (typically 1-5)
            if ($rating < 0 || $rating > 5) {
                return $this->error(__('Rating must be between 0 and 5'), [], 422);
            }

            $lang = $input['lang'] ?? S_GET('lang') ?? APP_LANG;

            // Get post using helper - support both ID and slug
            $post = get_post($idOrSlug, $posttype, [
                'lang' => $lang,
                'post_status' => ''
            ]);

            if (empty($post)) {
                return $this->error(__('Post not found'), [], 404);
            }

            $postId = $post['id'];

            // Build update data for rating counters
            $updateData = [];

            // Get current rating values (only if columns exist)
            $currentRatingCount = array_key_exists('rating_count', $post) ? (int)$post['rating_count'] : 0;
            $currentRatingTotal = array_key_exists('rating_total', $post) ? (float)$post['rating_total'] : 0;
            $currentRatingAvg = array_key_exists('rating_avg', $post) ? (float)$post['rating_avg'] : 0;

            if ($action === 'add') {
                // Add new rating
                $newRatingCount = $currentRatingCount + 1;
                $newRatingTotal = $currentRatingTotal + $rating;
                $newRatingAvg = $newRatingCount > 0 ? round($newRatingTotal / $newRatingCount, 2) : 0;
            } else {
                // Remove rating
                if ($currentRatingCount <= 0) {
                    return $this->error(__('Cannot remove rating: no ratings exist'), [], 400);
                }
                $newRatingCount = max(0, $currentRatingCount - 1);
                $newRatingTotal = max(0, $currentRatingTotal - $rating);
                $newRatingAvg = $newRatingCount > 0 ? round($newRatingTotal / $newRatingCount, 2) : 0;
            }

            // Update rating columns if they exist
            if (array_key_exists('rating_count', $post)) {
                $updateData['rating_count'] = $newRatingCount;
            }
            if (array_key_exists('rating_total', $post)) {
                $updateData['rating_total'] = $newRatingTotal;
            }
            if (array_key_exists('rating_avg', $post)) {
                $updateData['rating_avg'] = $newRatingAvg;
            }

            if (empty($updateData)) {
                return $this->error(__('No rating counters available for this posttype'), [], 400);
            }

            // Get table name and update
            $tableName = posttype_name($posttype, $lang);
            $updated = DB::table($tableName)
                ->where('id', $postId)
                ->update($updateData);

            if ($updated) {
                $updateData['id'] = $postId;
                return $this->success($updateData, 'Rating updated successfully');
            } else {
                return $this->error(__('Failed to update rating'), [], 500);
            }
        } catch (\Exception $e) {
            error_log("PostsController v0 _update_rating error: " . $e->getMessage());
            return $this->error(__('Internal server error'), [], 500);
        }
    }

    /**
     * Exclude heavy fields from response
     * 
     * Heavy fields: WYSIWYG, Textarea, Richtext, Flexible, Repeater
     * These fields can be very large and slow down API responses
     * 
     * @param array $posts Posts array
     * @param array $fields Posttype fields configuration
     * @return array Posts with heavy fields removed
     */
    protected function excludeHeavyFields($posts, $fields)
    {
        $heavyTypes = ['WYSIWYG', 'Textarea', 'Richtext', 'Flexible', 'Repeater'];
        $excludeFields = [];

        foreach ($fields as $field) {
            if (in_array($field['type'] ?? '', $heavyTypes)) {
                $excludeFields[] = $field['field_name'];
            }
        }

        if (empty($excludeFields)) {
            return $posts;
        }

        // Remove heavy fields
        foreach ($posts as &$post) {
            foreach ($excludeFields as $fieldName) {
                unset($post[$fieldName]);
            }
        }

        return $posts;
    }
}
