<?php

namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use App\Services\Posts\PostsService;
use System\Libraries\Session;
use System\Libraries\Render\View;

/**
 * PostsController - Thin Controller (NEW ARCHITECTURE)
 * 
 * Handles HTTP requests and delegates business logic to PostsService
 * Responsibilities:
 * - Routing
 * - Request/Response handling
 * - View rendering
 * - Session management
 * 
 * All business logic is in PostsService and sub-services
 * 
 * @package App\Controllers\Backend
 */
class PostsController extends BackendController
{
    /** @var postsService */
    protected $postsService;

    /** @var string Current language */
    protected $postLang;

    /** @var string Backend controller name for permission checks */
    protected $backendControllerName;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        \App\Libraries\Fastlang::load('Backend/Posts');

        // Initialize service and models
        $this->postsService = new PostsService();

        // Set backend controller name for permission checks
        $this->backendControllerName = get_class($this);

        // Get language from request
        $this->postLang = S_GET('post_lang') ?? APP_LANG_DF;

        View::addJs('flatpickr-admin', 'js/flatpickr.v4.6.13.min.js', ['admin-lucide'], null, true, false, false, false);
    }

    /**
     * Check if current user has manage permission
     * 
     * @return bool
     */
    protected function hasManagePermission()
    {
        return $this->checkPermission('manage');
    }

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

    /**
     * List posts
     * 
     * GET /admin/posts?type={posttype}&post_lang={lang}
     */
    public function index()
    {
        // Check permission
        $this->requirePermission('index');

        View::addJs('notification', 'js/notification.js', ['admin-lucide'], null, true, false, false, false);
        View::inlineJs(
            'posts-index-fastnotice',
            "
document.addEventListener('DOMContentLoaded', function () {
    window.fastNotice = new FastNotice({
        position: 'top-center',
        duration: 3000,
        maxNotifications: 3
    });
});
",
            [],
            null,
            false
        );

        $posttypeSlug = S_GET('type') ?? 'posts';
        $search = S_GET('q') ?? '';
        $status = S_GET('status') ?? '';
        $limit = (int)(S_GET('limit') ?? 10);
        $page = (int)(S_GET('page') ?? 1);
        $sort = S_GET('sort') ?? 'id';
        $order = S_GET('order') ?? 'DESC';

        // Validate parameters
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;

        // ✅ Get posttype config directly (optimized - only need posttype, not full form data)
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            Session::flash('error', __('Posttype not found'));
            redirect(admin_url('/'));
        }

        // ✅ Validate and auto-correct language
        $currentLang = posttype_post_lang($posttype, $this->postLang);
        if ($currentLang === null) {
            Session::flash('error', __('Posttype %1% not have any language', $posttype['name']));
            redirect(admin_url());
        }
        if ($currentLang != $this->postLang) {
            redirect(admin_url('posts') . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang);
        }

        // Build filters - filter by user if no manage permission
        $filters = [];
        if (!$this->hasManagePermission()) {
            $currentUserId = current_user_id();
            if ($currentUserId) {
                // Check which field exists in posttype (user_id, author, vendor)
                $fieldNames = array_column($posttype['fields'] ?? [], 'field_name');
                if (in_array('user_id', $fieldNames)) {
                    $filters[] = ['user_id', $currentUserId, '='];
                }
            }
        }

        // Get posts via service
        $result = $this->postsService->getList(
            $posttypeSlug,
            $currentLang,
            [
                'post_status' => $status,
                'search' => $search,
                'filters' => $filters
            ],
            [
                'page' => $page,
                'limit' => $limit,
                'order_by' => $sort,
                'order' => $order,
                'search_fields' => ['title', 'search_string'],
                'with_categories' => true,
                'with_tags' => false,
                //'with_author' => false,
                //'with_comments' => false,
            ]
        );

        // ✅ OPTIMIZED: Get available languages for all posts (BATCH)
        // 1 query per language instead of N queries (N = number of posts)
        if (!empty($result['data'])) {
            // Collect all post IDs
            $postIds = array_column($result['data'], 'id');

            // Get languages for all posts in batch (3 queries for 3 languages, not 100 queries for 100 posts!)
            $languagesMap = $this->postsService->getBatchPostLanguages($posttypeSlug, $postIds, $posttype['languages'], $currentLang);

            // Assign languages to each post
            foreach ($result['data'] as &$post) {
                $post['languages'] = $languagesMap[$post['id']] ?? [$currentLang];
            }
        }

        // ✅ Pass COMPLETE data to view
        $this->data('posts', $result);
        $this->data('posttype', $posttype); // ✅ Full posttype data
        $this->data('allPostType', posttype()); // ✅ For posttype switcher
        $this->data('languages', $posttype['languages']); // ✅ Available languages
        $this->data('currentLang', $currentLang);
        $this->data('limit', $limit);
        $this->data('sort', $sort);
        $this->data('order', $order);
        $this->data('page', $page);
        $this->data('search', $search);
        $this->data('status', $status);
        $this->data('title', __('List') . ' ' . $posttype['name'] . ' (' . $currentLang . ')');

        $this->data('breadcrumb', [
            ['name' => __('Dashboard'), 'url' => admin_url('home')],
            ['name' => __('Posts'), 'url' => admin_url('posts')],
            ['name' => __($posttype['name']), 'url' => admin_url('posts/?type=' . $posttype['slug'] . '&post_lang=' . $currentLang), 'active' => true],
        ]);

        echo View::make('posts_index', $this->data)->render();
    }

    /**
     * Add new post (WordPress-style: create draft and redirect to edit)
     * 
     * GET /admin/posts/add?type={posttype}&post_lang={lang}
     */
    public function add()
    {
        // Check permission
        $this->requirePermission('add');

        $posttypeSlug = S_GET('type') ?? 'posts';
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            Session::flash('error', __('Posttype not found'));
            redirect(admin_url('/'));
        }
        // ✅ Validate and auto-correct language
        $currentLang = posttype_post_lang($posttype, $this->postLang);
        if ($currentLang === null) {
            Session::flash('error', __('Posttype %1% not have any language', $posttype['name']));
            redirect(admin_url());
        }
        if ($currentLang != $this->postLang) {
            redirect(admin_url('posts/add') . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang);
        }

        // ✅ Build draft data ĐỘNG - chỉ set fields NẾU CÓ trong posttype
        $draftData = [];

        if (!empty($posttype['fields'])) {
            foreach ($posttype['fields'] as $field) {
                $fieldName = $field['field_name'] ?? '';
                $fieldType = $field['type'] ?? 'Text';

                if (empty($fieldName)) {
                    continue;
                }

                // Skip ID (auto-increment)
                if ($fieldName === 'id') {
                    continue;
                }

                // Skip Reference fields (handled separately)
                if ($fieldType === 'Reference') {
                    continue;
                }

                // ✅ XỬ LÝ SPECIAL FIELDS NẾU CÓ
                if ($fieldName === 'status') {
                    // Status field exists → set to draft
                    $draftData['status'] = null;
                    if (isset($field['options']) && is_array($field['options']) && count($field['options']) > 0) {
                        foreach ($field['options'] as $option) {
                            $draftData['status'] = $option['value'];
                            if (isset($option['value']) && $option['value'] === 'draft') {
                                break;
                            }
                        }
                    }
                    continue;
                }

                // Set user_id của bài viết default theo người đăng bài
                if (in_array($fieldName, ['user_id'])) {
                    $draftData[$fieldName] = current_user_id();
                }

                if ($fieldName === 'created_at') {
                    // created_at exists → set to now
                    $draftData['created_at'] = date('Y-m-d H:i:s');
                    continue;
                }

                if ($fieldName === 'updated_at') {
                    // updated_at exists → set to now
                    $draftData['updated_at'] = date('Y-m-d H:i:s');
                    continue;
                }

                // ✅ NORMAL FIELDS → NULL (draft can be empty)
                $draftData[$fieldName] = null;

                $draftData['create_draft'] = true; //Need for skip required validation
            }
        }

        // Create draft post via service
        $result = $this->postsService->create($posttypeSlug, $draftData, $currentLang);
        if ($result['success']) {
            // Redirect to edit with 301 (permanent redirect)
            $url = admin_url('posts/edit/' . $result['post_id']) . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang;
            header('Location: ' . $url, true, 301);
            exit;
        } else {
            // Failed - redirect to list
            Session::flash('error', __('Failed to create post') . ': ' . json_encode($result['errors']));
            redirect(admin_url('posts') . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang);
        }
    }

    /**
     * Edit post
     * 
     * GET/POST /admin/posts/edit/{id}?type={posttype}&post_lang={lang}
     */
    public function edit($id = null)
    {
        // Check permission
        $this->requirePermission('edit');

        // Load JS for string manipulation
        View::addJs('posts-jstring', 'js/jstring.1.1.0.js', [], null, false, false, false, false);

        $posttypeSlug = S_POST('type') ?? (S_GET('type') ?? 'posts');
        // Validate
        if (empty($id)) {
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }

        // ✅ Get posttype and validate language
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            Session::flash('error', __('Posttype not found'));
            redirect(admin_url('/'));
        }
        $currentLang = posttype_post_lang($posttype, $this->postLang);
        if ($currentLang === null) {
            Session::flash('error', __('Posttype %1% not have any language', $posttype['name']));
            redirect(admin_url());
        }
        if ($currentLang != $this->postLang) {
            redirect(admin_url('posts/edit/' . $id) . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang);
        }

        // Load form data first (cần có để hiển thị form khi có errors)
        $formData = $this->postsService->loadFormData($posttypeSlug, $id, $currentLang);

        if (empty($formData) || empty($formData['post'])) {
            Session::flash('error', __('Post not found'));
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }

        // Check access permission
        if (!$this->canAccessPost($formData['post'])) {
            Session::flash('error', __('You do not have permission to edit this post'));
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }

        // Handle POST submission
        if (HAS_POST('type') && !empty(S_POST('type'))) {
            // Check access permission before update
            $dataUpdate = $formData['post'];
            foreach (S_POST() as $key => $value) {
                if (array_key_exists($key, $dataUpdate)) {
                    $dataUpdate[$key] = $value;
                }
            }
            if (HAS_POST('id') && S_POST('id') != $id) {
                $dataUpdate['id'] = (int)$id;
            }
            //Anti XSS at title, description, seo_title, seo_desc, .v.v.
            if (HAS_POST('title')) {
                $dataUpdate['title'] = xss_clean(strip_tags(S_POST('title')));
            }
            if (HAS_POST('description')) {
                $dataUpdate['description'] = xss_clean(strip_tags(S_POST('description')));
            }
            if (HAS_POST('seo_title')) {
                $dataUpdate['seo_title'] = xss_clean(strip_tags(S_POST('seo_title')));
            }
            if (HAS_POST('seo_desc')) {
                $dataUpdate['seo_desc'] = xss_clean(strip_tags(S_POST('seo_desc')));
            }

            //Set DateTime to Validate
            if (HAS_POST('created_at') && !empty(S_POST('created_at'))) {
                $dataUpdate['created_at'] = _DateTime(S_POST('created_at'));
            } else {
                $dataUpdate['created_at'] = _DateTime();
            }
            $dataUpdate['updated_at'] = _DateTime();

            if (HAS_POST('status') && isset($formData['post']['status']) && S_POST('status') !== $formData['post']['status']) {
                try {
                    $this->requirePermission('changestatus');
                } catch (\Exception $e) {
                    if ($formData['post']['status'] == 'draft') {
                        if ($_POST['status'] == 'active') {
                            $_POST['status'] = 'pending';
                        }
                    } elseif ($formData['post']['status'] == 'active') {
                        $_POST['status'] = 'active';
                    } elseif ($formData['post']['status'] == 'inactive') {
                        $_POST['status'] = 'inactive';
                    } elseif ($formData['post']['status'] == 'schedule') {
                        $_POST['status'] = 'schedule';
                    } elseif ($formData['post']['status'] == 'suspended') {
                        $_POST['status'] = 'suspended';
                    } elseif ($formData['post']['status'] == 'pending') {
                        $_POST['status'] = 'pending';
                    } else {
                        $_POST['status'] = 'draft';
                    }
                    $dataUpdate['status'] = S_POST('status');
                }
            }

            if (!$this->hasManagePermission()) {
                // Not have manage all post permission, so set user_id, author, vendor to current user id
                if (HAS_POST('user_id')) {
                    $dataUpdate['user_id'] = current_user_id();
                }
            } else {
                // Have manage all post permission, can set to any, not need change.
            }
            $result = $this->postsService->update(
                $posttypeSlug,
                $id,
                $dataUpdate,
                $currentLang
            );

            if ($result['success']) {
                Session::flash('success', __('Post updated successfully'));
                // redirect(admin_url('posts') . '?type=' . $posttypeSlug . '&post_lang=' . $currentLang);
                $formData = $this->postsService->loadFormData($posttypeSlug, $id, $currentLang);
            } else {
                // ✅ Validation failed - Merge $_POST vào $formData['post'] để giữ values user vừa nhập
                $formData['post'] = array_merge($formData['post'], $dataUpdate);
                if (isset($formData['post']['terms']) && is_string($formData['post']['terms'])) {
                    $formData['post']['terms'] = _json_decode($formData['post']['terms']);
                }
                // Set errors
                $this->data('errors', $result['errors']);
            }
        }


        // Get languages available for this post
        $langHasPost = $this->postsService->getPostLanguages($posttypeSlug, $id);

        // Pass data to view
        $this->data('posttype', $formData['posttype']);
        $this->data('post', $formData['post']);  // ✅ Contains $_POST values if validation failed
        $this->data('languages', $formData['posttype']['languages']);
        $this->data('langHasPost', $langHasPost);
        $this->data('currentLang', $currentLang);
        $this->data('title', __('Edit Post'));

        echo View::make('posts_add', $this->data)->render();
    }

    /**
     * Delete post
     * 
     * GET/POST /admin/posts/delete/{id}?type={posttype}&post_lang={lang}
     */
    public function delete($id = null)
    {
        // Check permission
        $this->requirePermission('delete');

        $posttypeSlug = S_REQUEST('type') ?? 'posts';
        $lang = S_REQUEST('post_lang') ?? $this->postLang;

        // Check if AJAX request (bulk delete)
        $isAjax = is_ajax();

        if ($isAjax && S_POST('ids')) {
            // Bulk delete via AJAX
            $ids = S_POST('ids');

            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            if (!is_array($ids)) {
                $this->error('Invalid ids format');
                return;
            }

            $results = [];
            $success = 0;
            foreach ($ids as $postId) {
                // Check access permission for each post
                if (!$this->canAccessPost($postId, ['id' => $postId, 'post_type' => $posttypeSlug, 'lang' => $lang])) {
                    $results[] = ['success' => false, 'message' => __('You do not have permission to delete this post')];
                    continue;
                }

                $result = $this->postsService->delete($posttypeSlug, $postId, $lang);
                $results[] = $result;
                if ($result['success']) {
                    $success++;
                }
            }
            if ($success > 0) {
                Session::flash('success', $success === 1
                    ? __('Post deleted successfully')
                    : __('Deleted %1% posts successfully', $success));
            } else {
                Session::flash('error', __('Failed to delete posts'));
            }
            $this->success($results, 'Success');
            return;
        }

        // Single delete
        if ($id) {
            // Check access permission
            if (!$this->canAccessPost($id, ['id' => $id, 'post_type' => $posttypeSlug, 'lang' => $lang])) {
                Session::flash('error', __('You do not have permission to delete this post'));
                redirect(admin_url('posts') . '?type=' . $posttypeSlug);
            }

            $result = $this->postsService->delete($posttypeSlug, $id, $lang);

            if ($result['success']) {
                Session::flash('success', __('Post deleted successfully'));
            } else {
                Session::flash('error', __('Failed to delete post'));
            }

            redirect(admin_url('posts') . '?type=' . $posttypeSlug . '&post_lang=' . $lang);
        } else {
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }
    }

    /**
     * Clone post to other languages
     * 
     * GET /admin/posts/clone/{id}?type={posttype}&post_lang={target_langs}&oldpost_lang={source_lang}
     */
    public function clone($id)
    {
        // Check permission
        $this->requirePermission('clone');

        $posttypeSlug = S_GET('type') ?? 'posts';
        $targetLangs = S_GET('post_lang');
        $sourceLang = S_GET('oldpost_lang') ?? $this->postLang;

        if (empty($targetLangs)) {
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }

        if (!$this->canAccessPost($id, ['id' => $id, 'post_type' => $posttypeSlug, 'lang' => $sourceLang])) {
            Session::flash('error', __('You do not have permission to clone this post'));
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }

        // Parse target languages
        if (is_string($targetLangs)) {
            $targetLangs = explode(',', $targetLangs);
        }

        // Clone via service
        $result = $this->postsService->cloneToLanguages(
            $id,
            $posttypeSlug,
            $targetLangs,
            $sourceLang
        );

        if ($result['success'] && !empty($result['cloned'])) {
            Session::flash('success', sprintf(__('Post cloned to: %s'), implode(', ', $result['cloned'])));
            // Redirect to first cloned language
            redirect(admin_url('posts/edit/' . $id) . '?type=' . $posttypeSlug . '&post_lang=' . $result['cloned'][0]);
        } else {
            Session::flash('error', __('Failed to clone post'));
            redirect(admin_url('posts') . '?type=' . $posttypeSlug);
        }
    }

    /**
     * Change post status via AJAX
     * 
     * POST /admin/posts/changestatus
     */
    public function changestatus()
    {
        // Check AJAX
        $isAjax = is_ajax();

        if (!$isAjax) {
            redirect(admin_url('posts'));
        }

        header('Content-Type: application/json');

        $id = S_POST('id');
        $status = S_POST('status');
        $posttypeSlug = S_POST('type') ?? 'posts';
        $lang = S_POST('post_lang') ?? APP_LANG;

        // Validate parameters
        if (empty($id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => __('Invalid parameters')]);
            return;
        }

        $this->requirePermission('changestatus');

        try {
            // Check access permission
            $post = get_post([
                'id' => $id,
                'post_type' => $posttypeSlug,
                'post_status' => '',
                'lang' => $lang
            ]);

            if (empty($post)) {
                echo json_encode(['success' => false, 'message' => __('Post not found')]);
                return;
            }

            if (!$this->canAccessPost($post)) {
                echo json_encode(['success' => false, 'message' => __('You do not have permission to change status of this post')]);
                return;
            }

            // ✅ Use dedicated updateStatus() method (lightweight, checks field exists)
            $result = $this->postsService->updateStatus(
                $posttypeSlug,
                $post,
                $status,
                $lang
            );

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => __('Status updated successfully')]);
            } else {
                // Return first error message
                $errorMsg = __('Failed to update status');
                if (!empty($result['errors'])) {
                    $firstError = reset($result['errors']);
                    $errorMsg = is_array($firstError) ? $firstError[0] : $firstError;
                }
                echo json_encode(['success' => false, 'message' => $errorMsg]);
            }
        } catch (\Exception $e) {
            error_log("Error in changestatus: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Export posts to JSON
     * 
     * GET: Show export form with column selection
     * POST: Process export and return JSON file
     */
    public function export()
    {
        // Check permission
        $this->requirePermission('export');

        // Load notification library
        View::addJs('posts-notification', 'js/notification.js', [], null, false, false, false, false);
        View::inlineJs('posts-fastnotice-export', "
document.addEventListener('DOMContentLoaded', function () {
    window.fastNotice = new FastNotice({
        position: 'top-center',
        duration: 3000,
        maxNotifications: 3
    });
});
        ", [], null, false);

        $posttypeSlug = S_GET('type') ?? 'posts';

        // ✅ Get posttype config directly (optimized - only need posttype, not full form data)
        $posttype = posttype_active($posttypeSlug);

        if (empty($posttype)) {
            Session::flash('error', __('Posttype not found'));
            redirect(admin_url('/'));
        }

        // Handle count posts action
        if (S_POST('action') == 'count_posts') {
            $search = S_POST('search') ?? '';
            $status = S_POST('status') ?? '';

            $count = $this->postsService->countPosts(
                $posttypeSlug,
                $this->postLang,
                [
                    'search' => $search,
                    'post_status' => $status
                ]
            );

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'count' => $count]);
            return;
        }

        // Handle AJAX export request
        if (S_POST('action') == 'export_posts') {
            $selectedFields = S_POST('fields') ?? [];
            $selectedTerms = S_POST('terms') ?? [];
            $search = S_POST('search') ?? '';
            $status = S_POST('status') ?? '';
            $limit = (int)(S_POST('limit') ?? 500);

            // Decode if string
            if (is_string($selectedFields)) {
                $selectedFields = _json_decode($selectedFields);
            }
            if (is_string($selectedTerms)) {
                $selectedTerms = _json_decode($selectedTerms);
            }

            $page = (int)(S_POST('page') ?? 1);

            // Validate limit
            if ($limit === -1) {
                $limit = 99999; // All posts
            } elseif ($limit < 1) {
                $limit = 500;
            }

            // Get posts via helper with filters
            $postsResult = get_posts([
                'post_type' => $posttypeSlug,
                'lang' => $this->postLang,
                'posts_per_page' => $limit,
                'paged' => $page,
                'orderby' => 'id',
                'order' => 'ASC',
                'with_terms' => !empty($selectedTerms),
                'post_status' => $status,
                'search' => $search
            ]);

            $exportData = [];

            foreach ($postsResult['data'] as $post) {
                $item = ['id' => $post['id']]; // Always include ID

                // Add selected fields
                if (!empty($selectedFields)) {
                    foreach ($selectedFields as $fieldName) {
                        if ($fieldName === 'id') continue; // Already added

                        if (isset($post[$fieldName])) {
                            $value = $post[$fieldName];

                            // Decode JSON fields for export
                            if (is_string($value) && (substr($value, 0, 1) === '{' || substr($value, 0, 1) === '[')) {
                                $decoded = json_decode($value, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $value = $decoded;
                                }
                            }

                            $item[$fieldName] = $value;
                        }
                    }
                }

                // Add selected terms
                if (!empty($selectedTerms) && !empty($post['terms'])) {
                    foreach ($post['terms'] as $term) {
                        if (in_array($term['type'], $selectedTerms)) {
                            $key = 'terms:' . $term['type'];
                            $item[$key] = isset($item[$key]) ? $item[$key] . ',' . $term['slug'] : $term['slug'];
                        }
                    }
                }

                $exportData[] = $item;
            }

            $response = [
                'success' => true,
                'data' => $exportData,
                'page' => $page,
                'is_next' => $postsResult['is_next'] ?? false,
                'total_items' => count($exportData)
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Show export form
        $this->data('posttype', $posttype);
        $this->data('languages', $posttype['languages']);
        $this->data('currentLang', $currentLang);
        $this->data('title', __('Export') . ' ' . $posttype['name']);

        echo View::make('posts_export', $this->data)->render();
    }

    /**
     * Import posts from JSON
     * 
     * GET: Show import form
     * POST: Handle file upload and import
     */
    public function import()
    {
        // Check permission
        $this->requirePermission('import');

        // Load notification library
        View::addJs('posts-notification', 'js/notification.js', [], null, false, false, false, false);
        View::inlineJs('posts-fastnotice-import', "
document.addEventListener('DOMContentLoaded', function () {
    window.fastNotice = new FastNotice({
        position: 'top-center',
        duration: 3000,
        maxNotifications: 3
    });
});
        ", [], null, false);

        $posttypeSlug = S_POST('type') ?? (S_GET('type') ?? 'posts');

        // ✅ Get posttype config directly (optimized - only need posttype, not full form data)
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            Session::flash('error', __('Posttype not found'));
            redirect(admin_url('/'));
        }

        foreach ($posttype['fields'] as $key => $field) {
            if ($field['type'] == 'Taxonomy') {
                unset($posttype['fields'][$key]);
            }
        }
        $posttype['fields'] = array_values($posttype['fields']);

        // Handle JSON file upload
        if (S_POST('action') == 'upload_json') {
            $response = ['success' => false, 'message' => '', 'data' => []];

            if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] == 0) {
                $jsonContent = file_get_contents($_FILES['json_file']['tmp_name']);
                $jsonData = json_decode($jsonContent, true);

                if ($jsonData !== null && is_array($jsonData) && !empty($jsonData)) {
                    // Validate JSON structure
                    if (!isset($jsonData[0]) || !is_array($jsonData[0])) {
                        $response['message'] = 'Invalid JSON structure. Expected array of objects: [{...}, {...}]';
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        return;
                    }

                    // Save file
                    load_helpers(['upload']);
                    $uploadResult = do_upload($_FILES['json_file'], [
                        'folder' => 'imports/' . date('Y/m/d'),
                        'storage' => 'local'
                    ]);

                    if ($uploadResult['success']) {
                        $response['success'] = true;
                        $response['data'] = [
                            'columns' => array_keys($jsonData[0]),
                            'total_items' => count($jsonData),
                            'file_path' => $uploadResult['data']['path'],
                            'preview' => array_slice($jsonData, 0, 5)
                        ];
                        $response['message'] = 'JSON file uploaded successfully';
                    } else {
                        $response['message'] = 'Failed to save uploaded file';
                    }
                } else {
                    $response['message'] = 'Invalid JSON format or empty file';
                }
            } else {
                $response['message'] = 'No file uploaded or file error';
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Handle import batch processing
        if (S_POST('action') == 'import_json') {
            $filePath = S_POST('file_path');
            $columnMapping = S_POST('column_mapping') ?? [];
            $startIndex = (int)(S_POST('start_index') ?? 0);
            $batchSize = (int)(S_POST('batch_size') ?? 100);

            if (empty($filePath) || empty($columnMapping)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Missing file path or column mapping']);
                return;
            }

            // Decode column mapping
            if (is_string($columnMapping)) {
                $columnMapping = json_decode($columnMapping, true);
            }

            // Read JSON file
            $fullPath = PATH_UPLOADS . ltrim($filePath, '/');
            if (!file_exists($fullPath)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File not found']);
                return;
            }

            $jsonContent = file_get_contents($fullPath);
            $jsonData = json_decode($jsonContent, true);

            if (!$jsonData || !is_array($jsonData)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
                return;
            }

            // Get batch
            $batch = array_slice($jsonData, $startIndex, $batchSize);

            // Process batch via service
            $result = $this->postsService->importBatch(
                $batch,
                $columnMapping,
                $posttypeSlug,
                $this->postLang
            );

            header('Content-Type: application/json');
            echo json_encode($result);
            return;
        }

        // Show import form
        $this->data('posttype', $posttype);
        $this->data('availableFields', $posttype['fields']); // ✅ All dynamic fields
        $this->data('languages', $posttype['languages']);
        $this->data('currentLang', $currentLang);
        $this->data('title', __('Import') . ' ' . $posttype['name']);

        echo View::make('posts_import', $this->data)->render();
    }

    /**
     * Bulk edit posts
     */
    public function bulkedit()
    {
        // Check permission
        $this->requirePermission('bulkedit');

        // Load notification library
        View::addJs('posts-notification', 'js/notification.js', [], null, false, false, false, false);
        View::addJs('posts-spreadsheet-helper', 'js/spreadsheet-helper.js', [], null, false, false, false, false);
        View::inlineJs('posts-fastnotice-bulk', "
document.addEventListener('DOMContentLoaded', function () {
    window.fastNotice = new FastNotice({
        position: 'top-center',
        duration: 3000,
        maxNotifications: 3
    });
});
        ", [], null, false);

        $posttypeSlug = S_REQUEST('type') ?? 'posts';
        $postIds = S_POST('post_ids') ?? '';
        $postIds = array_filter(array_map('intval', explode(',', $postIds)));

        // Get all posttypes for switcher
        $allPostType = posttype_active();
        // ✅ Get posttype config directly (optimized - only need posttype, not full form data)
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            Session::flash('error', __('Posttype not found'));
            redirect(admin_url('posts'));
        }
        $posttypeColumns = array_column($posttype['fields'], 'field_name');

        $pagination = null;

        // If no post_ids, get from filters
        if (empty($postIds)) {
            $search = S_GET('q') ?? '';
            $status = S_GET('status') ?? '';
            $limit = (int)(S_GET('limit') ?? 10);
            $page = (int)(S_GET('page') ?? 1);
            $filters = [];
            if (!$this->hasManagePermission()) {
                if (in_array('user_id', $posttypeColumns)) {
                    $filters[] = ['user_id', current_user_id(), '='];
                }
            }

            $postsResult = get_posts([
                'post_type' => $posttypeSlug,
                'lang' => $this->postLang,
                'posts_per_page' => $limit,
                'post_status' => $status,
                'orderby' => 'id',
                'order' => 'DESC',
                'paged' => $page,
                's' => $search,
                'filters' => $filters
            ]);

            if (!empty($postsResult['data'])) {
                $postIds = array_column($postsResult['data'], 'id');
                $pagination = [
                    'page' => $postsResult['page'] ?? 1,
                    'is_next' => $postsResult['is_next'] ?? false
                ];
            }
        }

        $this->data('posttype', $posttype);
        $this->data('allPostType', $allPostType);
        $this->data('languages', $posttype['languages']);
        $this->data('currentLang', $currentLang);
        $this->data('posts', $postIds);
        $this->data('pagination', $pagination);
        $this->data('title', __('Bulk Edit') . ' ' . $posttype['name']);

        echo View::make('posts_bulk_edit', $this->data)->render();
    }

    /**
     * Get bulk posts data via AJAX
     */
    public function getbulkposts()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => __('Invalid request method')]);
            return;
        }

        $postIds = S_POST('post_ids');
        $postIds = array_filter(array_map('intval', explode(',', $postIds)));
        $posttypeSlug = S_POST('type') ?? 'posts';

        if (empty($postIds)) {
            echo json_encode(['success' => false, 'message' => __('Invalid post selection')]);
            return;
        }
        if (!posttype_lang_exists($posttypeSlug, $this->postLang)) {
            echo json_encode(['success' => false, 'message' => __('Posttype not found')]);
            return;
        }
        $posttype = posttype_active($posttypeSlug);
        if (empty($posttype)) {
            echo json_encode(['success' => false, 'message' => __('Posttype not found')]);
            return;
        }
        $posttypeColumns = array_column($posttype['fields'], 'field_name');

        $this->requirePermission('getbulkposts');

        try {
            // Use get_posts helper
            $filters = [];
            $canManage = $this->hasManagePermission();
            if (!$canManage) {
                if (in_array('user_id', $posttypeColumns)) {
                    $filters[] = ['user_id', current_user_id(), '='];
                }
            }
            $postsResult = get_posts([
                'post_type' => $posttypeSlug,
                'lang' => $this->postLang,
                'post__in' => $postIds,
                'filters' => $filters,
                'posts_per_page' => -1,
                'post_status' => '',
                'orderby' => 'id',
                'order' => 'ASC',
                'with_terms' => true
            ]);

            if (empty($postsResult['data'])) {
                echo json_encode(['success' => false, 'message' => __('No posts found')]);
                return;
            }

            // Get posttype for term types (reuse already loaded posttype)
            // $posttype already loaded above, no need to reload

            $termsConfig = $posttype['terms'];
            $fieldsConfig = $posttype['fields'];

            // ✅ Load data for special field types (User, Reference)
            foreach ($fieldsConfig as &$field) {
                $fieldType = $field['type'] ?? '';

                // Load User field options
                if ($fieldType === 'User') {
                    $usersModel = new \App\Models\UsersModel();
                    $field['data'] = $usersModel->getUsers();
                }

                // Load Reference field options (NEW structure)
                elseif ($fieldType === 'Reference') {
                    $ref = $field['reference'] ?? [];
                    $refPosttype = $ref['postTypeRef'] ?? '';

                    if (!empty($refPosttype)) {
                        $refModel = new \App\Models\PostsModel($refPosttype, $this->postLang);
                        $refPosts = $refModel->getPostsList('', [], 'id DESC', 100);  // Max 100 for dropdown
                        $field['data'] = $refPosts;
                    }
                }
            }

            // ✅ Load terms data for each term type
            $termsModel = new \App\Models\TermsModel();
            $termsData = [];

            if (!empty($termsConfig)) {
                foreach ($termsConfig as $termConfig) {
                    $termType = $termConfig['type'] ?? '';
                    if (!empty($termType)) {
                        $terms = $termsModel->getTermsByTypeAndPostTypeAndLang(
                            $posttypeSlug,
                            $termType,
                            $this->postLang
                        );
                        $termsData[$termType] = $terms;  // Array of terms for this type
                    }
                }
            }

            // Format data for spreadsheet
            $posts = [];
            $allTermTypes = [];

            // Collect term types
            if (!empty($termsConfig)) {
                foreach ($termsConfig as $term) {
                    $allTermTypes[$term['type']] = true;
                }
            }

            foreach ($postsResult['data'] as $post) {
                $item = [];

                // Add all fields from post
                foreach ($post as $fieldName => $value) {
                    if ($fieldName === 'terms') continue;

                    // Decode JSON fields for display
                    if (is_string($value) && (substr($value, 0, 1) === '{' || substr($value, 0, 1) === '[')) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $value = is_array($decoded) ? json_encode($decoded) : $value;
                        }
                    }

                    $item[$fieldName] = $value ?? '';
                }

                // Add terms as "terms:type" fields
                if (!empty($allTermTypes)) {
                    foreach ($allTermTypes as $termType => $v) {
                        $item['terms:' . $termType] = '';
                    }
                }

                if (!empty($post['terms'])) {
                    foreach ($post['terms'] as $term) {
                        $termType = $term['type'];
                        $key = 'terms:' . $termType;
                        $item[$key] = isset($item[$key]) && $item[$key] !== ''
                            ? $item[$key] . ',' . $term['slug']
                            : $term['slug'];
                    }
                }

                $posts[] = $item;
            }

            echo json_encode([
                'success' => true,
                'data' => $posts,
                'term_types' => array_keys($allTermTypes),
                'posttype_fields' => $fieldsConfig,
                'terms_data' => $termsData  // ✅ Terms list for dropdowns
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update single field of a post via AJAX
     */
    public function updatebulkpost()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => __('Invalid request')]);
            return;
        }

        $postId = (int)S_POST('post_id');
        $field = S_POST('field');
        $value = S_POST('value');
        $posttypeSlug = S_POST('type') ?? 'posts';

        if (empty($postId) || empty($field)) {
            echo json_encode(['success' => false, 'message' => __('Missing parameters')]);
            return;
        }

        $this->requirePermission('updatebulkpost');

        try {
            // Check if term field
            if (strpos($field, 'terms:') === 0) {
                $termType = substr($field, 6);

                // Get current post with terms
                $postsResult = get_posts([
                    'post_type' => $posttypeSlug,
                    'lang' => $this->postLang,
                    'post__in' => [$postId],
                    'posts_per_page' => 1,
                    'post_status' => '',
                    'with_terms' => true
                ]);

                if (empty($postsResult['data'])) {
                    echo json_encode(['success' => false, 'message' => __('Post not found')]);
                    return;
                }

                $post = $postsResult['data'][0];

                if (!$this->canAccessPost($post)) {
                    echo json_encode(['success' => false, 'message' => __('You do not have permission to update this post')]);
                    return;
                }

                // Get current term IDs for this type
                $currentTermIds = [];
                if (!empty($post['terms'])) {
                    foreach ($post['terms'] as $term) {
                        if ($term['type'] === $termType) {
                            $currentTermIds[] = $term['id'];
                        }
                    }
                }

                // Parse new term slugs
                $newTermSlugs = array_filter(array_map('trim', explode(',', $value)));
                $newTermIds = [];

                if (!empty($newTermSlugs)) {
                    $termsModel = new \App\Models\TermsModel();
                    foreach ($newTermSlugs as $termSlug) {
                        $term = $termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                            $termSlug,
                            $posttypeSlug,
                            $termType,
                            $this->postLang
                        );

                        if ($term) {
                            $termId = is_array($term) && isset($term[0]) ? $term[0]['id'] : ($term['id'] ?? null);
                            if ($termId) {
                                $newTermIds[] = $termId;
                            }
                        }
                    }
                }

                // Sync terms via helper
                $removedTermIds = array_diff($currentTermIds, $newTermIds);
                $addedTermIds = array_diff($newTermIds, $currentTermIds);

                foreach ($addedTermIds as $termId) {
                    posts_add_term($postId, $termId, $posttypeSlug, $this->postLang);
                }

                foreach ($removedTermIds as $termId) {
                    posts_remove_term($postId, $termId, $posttypeSlug, $this->postLang);
                }

                echo json_encode(['success' => true, 'message' => __('Terms updated successfully')]);
            } else {
                // ✅ Regular field update - Use lightweight updateSingleField()
                $result = $this->postsService->updateSingleField(
                    $posttypeSlug,
                    $postId,
                    $field,
                    $value,
                    $this->postLang
                );

                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => __('Post updated successfully'),
                        'data' => $result['data'] ?? []
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => __('Update failed'),
                        'errors' => $result['errors']
                    ]);
                }
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create new post from bulk edit via AJAX
     */
    public function createbulkpost()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => __('Invalid request')]);
            return;
        }

        $this->requirePermission('createbulkpost');

        $field = S_POST('field');
        $value = S_POST('value');
        $posttypeSlug = S_POST('type') ?? 'posts';

        if (empty($field)) {
            echo json_encode(['success' => false, 'message' => __('Field name required')]);
            return;
        }

        try {
            // ✅ Load posttype để validate fields (DYNAMIC)
            $formData = $this->postsService->loadFormData($posttypeSlug, null, $this->postLang);

            if (empty($formData) || empty($formData['posttype'])) {
                echo json_encode(['success' => false, 'message' => __('Posttype not found')]);
                return;
            }

            $posttype = $formData['posttype'];

            // ✅ Build draft data ĐỘNG - chỉ set fields NẾU CÓ trong posttype
            $draftData = [];

            // Check if field is a term field (terms:type)
            $isTermField = strpos($field, 'terms:') === 0;

            if (!$isTermField) {
                // Check if field exists in posttype
                $fieldExists = false;
                foreach ($posttype['fields'] as $fieldDef) {
                    if (($fieldDef['field_name'] ?? '') === $field) {
                        $fieldExists = true;
                        break;
                    }
                }

                if (!$fieldExists) {
                    echo json_encode(['success' => false, 'message' => __('Invalid field: ') . $field]);
                    return;
                }

                // Set field value
                $draftData[$field] = $value;
            }

            // ✅ Chỉ set status NẾU field tồn tại trong posttype
            foreach ($posttype['fields'] as $fieldDef) {
                if (($fieldDef['field_name'] ?? '') === 'status') {
                    $draftData['status'] = null;
                    if (isset($fieldDef['default_value']) && !empty($fieldDef['default_value'])) {
                        $draftData['status'] = $fieldDef['default_value'];
                    } else {
                        if (isset($fieldDef['options']) && is_array($fieldDef['options']) && count($fieldDef['options']) > 0) {
                            foreach ($fieldDef['options'] as $option) {
                                if (isset($option['value']) && $option['value'] == 'draft') {
                                    $draftData['status'] = 'draft';
                                    break;
                                }
                            }
                        }
                        if (is_null($draftData['status'])) {
                            $draftData['status'] = $fieldDef['options'][0]['value'];
                        }
                    }
                    break;
                }
            }

            // ✅ Set timestamps NẾU tồn tại
            foreach ($posttype['fields'] as $fieldDef) {
                $fieldName = $fieldDef['field_name'] ?? '';
                if ($fieldName === 'created_at') {
                    $draftData['created_at'] = date('Y-m-d H:i:s');
                }
                if ($fieldName === 'updated_at') {
                    $draftData['updated_at'] = date('Y-m-d H:i:s');
                }
            }

            // Create via service
            $result = $this->postsService->create($posttypeSlug, $draftData, $this->postLang);

            if ($result['success']) {
                // Get created post
                $post = $this->postsService->get($posttypeSlug, $result['post_id'], $this->postLang);

                echo json_encode([
                    'success' => true,
                    'message' => __('Post created successfully'),
                    'data' => $post
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => __('Create failed'), 'errors' => $result['errors']]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
