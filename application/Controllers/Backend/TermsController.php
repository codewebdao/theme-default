<?php

namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use App\Services\Terms\TermsService;
use App\Models\TermsModel;
use App\Models\LanguagesModel;
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Validate;
use App\Libraries\Fastlang as Flang;

/**
 * TermsController - Thin Terms Controller (NEW ARCHITECTURE)
 * 
 * Handles HTTP requests and delegates business logic to TermsService
 * 
 * @package App\Controllers\Backend
 */
class TermsController extends BackendController
{
    /** @var termsService */
    protected $termsService;

    /** @var TermsModel */
    protected $termsModel;

    /** @var LanguagesModel */
    protected $languagesModel;

    /** @var string Backend controller name for permission checks */
    protected $termsControllerName;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
                
        $this->termsService = new TermsService();
        $this->termsModel = new TermsModel();
        $this->languagesModel = new LanguagesModel();
        
        // Set backend controller name for permission checks
        $this->termsControllerName = get_class($this);
        
        Flang::load('general', APP_LANG);
        Flang::load('terms', APP_LANG);
    }


    /**
     * List terms
     */
    public function index()
    {
        // Check permission
        $this->requirePermission('index');
        
        $this->data('csrf_token', Session::csrf_token(600));
        
        // Get and validate parameters
        $posttype = S_GET('posttype') ?? '';
        $type = S_GET('type') ?? '';
        $post_lang = S_GET('post_lang') ?? '';

        if (!HAS_GET('posttype') || empty(S_GET('posttype'))) {
            Session::flash('error', Flang::__('posttype is required'));
            redirect(admin_url('/'));
            return;
        }
        
        // Get all posttypes
        $allPostTypes = posttype_db();
        
        // Auto-select first posttype if empty
        if (empty($posttype) && !empty($allPostTypes)) {
            $posttype = $allPostTypes[0]['slug'];
            // Redirect with selected posttype to avoid loop
            redirect(admin_url('terms/?posttype=' . $posttype));
            return;
        }
        
        // Get posttype data
        $posttypeData = posttype_db($posttype);
        if (empty($posttypeData)) {
            Session::flash('error', Flang::__('posttype not found'));
            redirect(admin_url('/'));
            return;
        }
        
        $termsInfo = _json_decode($posttypeData['terms']);
        
        // Auto-select first type if empty
        if (empty($type) && !empty($termsInfo)) {
            $type = $termsInfo[0]['type'];
            // Redirect with selected type
            redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type));
            return;
        }
        
        // Validate language using helper
        $currentLang = posttype_post_lang($posttypeData, $post_lang);
        if ($currentLang === null) {
            Session::flash('error', Flang::__('Posttype %1% not have any language', $posttypeData['name']));
            redirect(admin_url('/'));
            return;
        }
        if ($currentLang != $post_lang) {
            redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type . '&post_lang=' . $currentLang));
            return;
        }
        
        // Get other parameters
        $search = S_GET('q') ?? '';
        $limit = (int)(S_GET('limit') ?? 20);
        $page = (int)(S_GET('page') ?? 1);
        $sort = S_GET('sort') ?? 'id';
        $order = S_GET('order') ?? 'DESC';
        
        // Validate
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 20;
        
        $allowedSort = ['id', 'name', 'created_at', 'slug'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'id';
        }

        // Get terms via optimized helper
        $termsResult = get_terms([
            'taxonomy' => $type,
            'post_type' => $posttype,
            'lang' => $currentLang,
            'search' => $search,
            'orderby' => $sort,
            'order' => $order,
            'limit' => $limit,
            'offset' => ($page - 1) * $limit
        ]);

        // Format and build tree
        $allTerm = $termsResult['data'] ?? [];
        $tree = $this->loadTermsWithLanguages($allTerm, $posttype, $type, $currentLang, $sort, $order);
        
        // Pass data to view (view will loop to find posttypeData and termData from allPostTypes and termsInfo)
        $this->data('title', Flang::__('title_index') . ' - ' . $posttype . ' - ' . $type);
        $this->data('allTerm', $allTerm);
        $this->data('allPostTypes', $allPostTypes);
        $this->data('termsInfo', $termsInfo);
        $this->data('tree', $tree);
        $this->data('is_next', $termsResult['is_next'] ?? false);
        $this->data('page', $page);
        $this->data('limit', $limit);
        $this->data('search', $search);
        $this->data('sort', $sort);
        $this->data('order', $order);
        $this->data('currentLang', $currentLang);
        $this->data('posttype', $posttype);
        $this->data('type', $type);
        
        echo View::make('terms_index', $this->data)->render();
    }

    /**
     * Add term
     * 
     * GET: Show add form
     * POST: Create term
     */
    public function add($id = null)
    {
        // Check permission
        $this->requirePermission('add');
        
        $posttype = S_REQUEST('posttype') ?? '';
        $type = S_REQUEST('type') ?? '';
        $post_lang = S_REQUEST('post_lang') ?? S_POST('lang') ?? APP_LANG;

        // Get posttype data
        $posttypeData = posttype_active($posttype);
        
        if (empty($posttypeData)) {
            Session::flash('error', Flang::__('posttype not found or not active'));
            redirect(admin_url('/'));
            return;
        }

        // Validate language
        if (!in_array($post_lang, $posttypeData['languages'])) {
            Session::flash('error', Flang::__('lang not in posttype or not active'));
            redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type . '&post_lang=' . $posttypeData['languages'][0]));
            return;
        }

        // Handle POST submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = [
                'name' => S_POST('name'),
                'slug' => S_POST('slug'),
                'description' => S_POST('description'),
                'type' => S_POST('type'),
                'posttype' => S_POST('posttype'),
                'parent' => S_POST('parent') ?: null,
                'lang' => S_POST('lang') ?? $post_lang,
                'id_main' => S_POST('id_main'),
                'seo_title' => S_POST('seo_title'),
                'seo_desc' => S_POST('seo_desc'),
                'status' => S_POST('status') ?? 'active'
            ];

            // Validate id_main
            if (empty($input['id_main']) || !is_numeric($input['id_main'])) {
                $input['id_main'] = 0;
            } else {
                $input['id_main'] = (int)$input['id_main'];
            }

            // Auto-generate slug
            if (empty($input['slug'])) {
                $input['slug'] = url_slug($input['name']);
            }

            // Validation rules
            $rules = [
                'name' => [
                    'rules' => [Validate::notEmpty(), Validate::length(2, 100)],
                    'messages' => [Flang::__('name empty'), Flang::__('Name length must be between 2 and 100 characters')]
                ],
                'slug' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('slug empty')]
                ],
                'type' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('type empty')]
                ],
                'posttype' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('posttype empty')]
                ],
                'lang' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('lang empty')]
                ]
            ];

            $validator = new Validate();
            if (!$validator->check($input, $rules)) {
                $this->data('errors', $validator->getErrors());
            } else {
                // Create term via service
                $result = $this->termsService->create($input);
                
                if ($result['success']) {
                    Session::flash('success', Flang::__('add_terms_success'));
                    redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type . '&post_lang=' . $post_lang));
                    return;
                } else {
                    $this->data('errors', $result['errors']);
                }
            }
        }

        // Get term info
        $currentTermInfo = [];
        if (!empty($posttypeData['terms'])) {
            foreach ($posttypeData['terms'] as $term) {
                if ($term['type'] == $type) {
                    $currentTermInfo = $term;
                    break;
                }
            }
        }

        if (empty($currentTermInfo)) {
            Session::flash('error', Flang::__('term type not found'));
            redirect(admin_url('/'));
            return;
        }

        // Get all terms for parent selection
        $all_terms = $this->termsModel->getTermsByTypeAndPostTypeAndLang($posttype, $type, $post_lang);
        $all_terms_lang = $this->formatTermsByLanguage($all_terms, $post_lang);
        $all_terms_tree = $this->buildTree($all_terms_lang);

        // If mainterm provided, get main term
        $mainterm = S_GET('mainterm');
        if (!empty($mainterm)) {
            $main_term = $this->termsModel->getTermById($mainterm);
            if (empty($main_term)) {
                Session::flash('error', Flang::__('main term not found'));
                redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type . '&post_lang=' . $post_lang));
                return;
            }
            $this->data('mainterm', $mainterm);
            
            // Get all language versions
            $main_terms_lang = $this->termsModel->getTermByIdMain($mainterm);
            $main_terms_lang = array_column($main_terms_lang, null, 'lang');
            $this->data('main_terms_lang', $main_terms_lang);
        }

        $this->data('all_terms', $all_terms);
        $this->data('all_terms_tree', $all_terms_tree);
        $this->data('posttypeData', $posttypeData);
        $this->data('type', $type);
        $this->data('post_lang', $post_lang);
        $this->data('csrf_token', Session::csrf_token(600));
        
        View::addJs('terms-jstring', 'js/jstring.1.1.0.js', [], null, true, false, true, false);

        echo View::make('terms_add', $this->data)->render();
    }

    /**
     * Edit term
     * 
     * GET: Show edit form
     * POST: Update term
     */
    public function edit($termId)
    {
        // Check permission
        $this->requirePermission('edit');
        
        $term = $this->termsModel->getTermById($termId);
        
        if (empty($term)) {
            Session::flash('error', Flang::__('term not found or not active'));
            redirect(admin_url('terms'));
            return;
        }

        $posttype = $term['posttype'];
        $type = $term['type'];
        
        // Get posttype data
        $posttypeData = posttype_active($posttype);
        $languagesPosttype = _json_decode($posttypeData['languages']);

        // Handle POST submission
        if (HAS_POST('name')) {
            // ✅ FIX: Lấy language từ post_lang parameter, fallback to lang field
            $lang = S_REQUEST('post_lang') ?? S_POST('lang') ?? $term['lang'];
            
            $input = [
                'name' => S_POST('name'),
                'slug' => S_POST('slug'),
                'type' => S_POST('type'),
                'posttype' => S_POST('posttype'),
                'parent' => S_POST('parent') ?: null,
                'lang' => $lang,
                'status' => S_POST('status') ?? 'active',
                'description' => S_POST('description'),
                'seo_title' => S_POST('seo_title'),
                'seo_desc' => S_POST('seo_desc'),
                'id_main' => S_POST('id_main'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Validation rules
            $rules = [
                'name' => [
                    'rules' => [Validate::notEmpty(), Validate::length(2, 100)],
                    'messages' => [Flang::__('name empty'), Flang::__('Name length must be between 2 and 100 characters')]
                ],
                'slug' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('slug empty')]
                ],
                'type' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('type empty')]
                ],
                'posttype' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('posttype empty')]
                ],
                'lang' => [
                    'rules' => [Validate::notEmpty()],
                    'messages' => [Flang::__('lang empty')]
                ]
            ];

            $validator = new Validate();
            if (!$validator->check($input, $rules)) {
                $this->data('errors', $validator->getErrors());
            } else {
                // Update via service
                $result = $this->termsService->update($termId, $input);
                
                if ($result['success']) {
                    Session::flash('success', Flang::__('edit terms success'));
                    redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type . '&post_lang=' . $input['lang']));
                    return;
                } else {
                    $this->data('errors', $result['errors']);
                }
            }
        }

        // Get language data
        $termsLang = [];
        if (in_array('all', $languagesPosttype)) {
            $termsLang = [['code' => 'all', 'name' => 'All']];
        } else {
            foreach (APP_LANGUAGES as $key => $lang) {
                if (in_array($key, $languagesPosttype)) {
                    $lang['code'] = $key;
                    $termsLang[] = $lang;
                }
            }
        }

        // Get terms info
        $termsInfo = _json_decode($posttypeData['terms']);
        $currentTermInfo = [];
        foreach ($termsInfo as $termInfo) {
            if ($termInfo['type'] == $type) {
                $currentTermInfo = $termInfo;
                break;
            }
        }

        // Get all terms for parent selection
        $allTerm = $this->termsModel->getTermsByTypeAndPostType($posttype, $type);
        $lang = $this->languagesModel->getActiveLanguages();
        $allTermLang = $this->formatTermsByLanguage($allTerm, $term['lang']);
        $tree = $this->buildTree($allTermLang);

        // Get main terms for id_main selection
        $mainterms = [];
        if ($term['lang'] !== APP_LANG_DF) {
            $mainterms = $this->termsModel->getTermsByTypeAndPostTypeAndLang($posttype, $type, APP_LANG_DF);
            $mainterms = $this->buildTree($mainterms);
        }

        $this->data('csrf_token', Session::csrf_token(600));
        $this->data('default_lang', APP_LANG_DF);
        $this->data('title', Flang::__('Edit Term'));
        $this->data('lang', $lang);
        $this->data('langActive', $termsLang);
        $this->data('currentTermInfo', $currentTermInfo);
        $this->data('allTerm', $allTerm);
        $this->data('term', $term);
        $this->data('tree', $tree);
        $this->data('mainterms', $mainterms);
        $this->data('posttypeData', $posttypeData);
        $this->data('posttype', $posttype);
        $this->data('type', $type);
        
        echo View::make('terms_edit', $this->data)->render();
    }

    /**
     * Delete term(s)
     */
    public function delete($termId = null)
    {
        // Check permission
        $this->requirePermission('delete');
        
        $posttype = S_REQUEST('posttype') ?? '';
        $type = S_REQUEST('type') ?? '';

        if (!empty($termId)) {
            // Single delete via service
            $result = $this->termsService->delete($termId);
            
            if ($result['success']) {
                Session::flash('success', Flang::__('delete_terms_success'));
            } else {
                Session::flash('error', Flang::__('delete_terms_error'));
            }
            
            redirect(admin_url('terms/?posttype=' . $posttype . '&type=' . $type));
        } elseif (S_POST('ids')) {
            // Bulk delete via service
            $ids = S_POST('ids');
            $ids = is_string($ids) ? json_decode($ids, true) : $ids;
            
            $results = [];
            foreach ($ids as $id) {
                $results[] = $this->termsService->delete($id);
            }
            
            $this->success($results, Flang::__('delete_terms_success'));
        } else {
            redirect(admin_url('terms'));
        }
    }

    /**
     * Change term status via AJAX
     */
    public function changestatus()
    {
        // Check permission
        $this->requirePermission('changestatus');
        
        header('Content-Type: application/json');
        
        if (!HAS_POST('id') || !HAS_POST('status')) {
            echo json_encode(['success' => false, 'message' => __('Missing parameters')]);
            return;
        }
        
        $id = (int)S_POST('id');
        $status = S_POST('status');
        
        // Validate status
        if (!in_array($status, ['active', 'inactive'])) {
            echo json_encode(['success' => false, 'message' => __('Invalid status')]);
            return;
        }
        
        try {
            // Update via service
            $result = $this->termsService->update($id, ['status' => $status]);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => __('Status updated successfully')]);
            } else {
                echo json_encode(['success' => false, 'message' => __('Failed to update status')]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get terms by language via AJAX
     */
    public function gettermsbylang()
    {
        header('Content-Type: application/json');
        
        if (!HAS_POST('lang') || !HAS_POST('type') || !HAS_POST('posttype')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters'
            ]);
            return;
        }

        $this->requirePermission('index');
        
        $lang = S_POST('lang');
        $type = S_POST('type');
        $posttype = S_POST('posttype');
        
        try {
            $terms = $this->termsModel->getTermsByTypeAndPostTypeAndLang($posttype, $type, $lang);
            $tree = $this->buildTree($terms);
            
            echo json_encode([
                'status' => 'success',
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format terms by language
     * 
     * @param array $terms Array of terms
     * @param string $mainLang Main language
     * @return array Formatted terms
     */
    protected function formatTermsByLanguage($terms, $mainLang)
    {
        $formattedTerms = [];
        $termsByMainId = [];
        
        // Group by id_main
        foreach ($terms as $term) {
            $idMain = $term['id_main'] ?? $term['id'];
            if (!isset($termsByMainId[$idMain])) {
                $termsByMainId[$idMain] = [];
            }
            $termsByMainId[$idMain][] = $term;
        }
        
        // Process each group
        foreach ($termsByMainId as $idMain => $termGroup) {
            $mainTerm = null;
            $subTerms = [];
            
            foreach ($termGroup as $term) {
                if ($term['lang'] === $mainLang) {
                    $mainTerm = $term;
                } else {
                    $subTerms[] = $term;
                }
            }
            
            if ($mainTerm) {
                $langTerms = [];
                foreach ($subTerms as $subTerm) {
                    $langTerms[$subTerm['lang']] = $subTerm;
                }
                $mainTerm['lang_terms'] = $langTerms;
                $formattedTerms[] = $mainTerm;
            }
        }
        
        return $formattedTerms;
    }

    /**
     * Build tree structure
     * 
     * @param array $terms Array of terms
     * @param array $originalOrder Original order
     * @return array Tree structure
     */
    protected function buildTree($terms, $originalOrder = [])
    {
        $result = [];
        $tree = [];
        
        // Cache languages
        static $languagesCache = null;
        if ($languagesCache === null) {
            $allLanguages = $this->languagesModel->getActiveLanguages();
            $languagesCache = [];
            foreach ($allLanguages as $lang) {
                $languagesCache[$lang['code']] = $lang['name'];
            }
        }
        
        // Create ID map for original order
        $originalOrderIds = [];
        if (!empty($originalOrder)) {
            foreach ($originalOrder as $index => $item) {
                $originalOrderIds[$item['id']] = $index;
            }
        }
        
        // Build result array
        foreach ($terms as $item) {
            $result[$item['id']] = $item;
            $result[$item['id']]['children'] = [];
            
            if (isset($originalOrderIds[$item['id']])) {
                $result[$item['id']]['_sort_order'] = $originalOrderIds[$item['id']];
            }
        }
        
        // Build tree
        foreach ($result as $id => &$node) {
            // Add language name
            if (!empty($node['lang'])) {
                $node['lang_name'] = $languagesCache[$node['lang']] ?? '';
            }

            if (!empty($node['parent']) && isset($result[$node['parent']])) {
                $result[$node['parent']]['children'][] = &$node;
                $node['parent_name'] = $result[$node['parent']]['name'] ?? '';
                $node['parent_slug'] = $result[$node['parent']]['slug'] ?? '';
            } else {
                $tree[] = &$node;
            }
        }
        
        // Sort by original order
        if (!empty($originalOrderIds)) {
            usort($tree, function($a, $b) {
                $orderA = $a['_sort_order'] ?? PHP_INT_MAX;
                $orderB = $b['_sort_order'] ?? PHP_INT_MAX;
                return $orderA - $orderB;
            });
        }
        
        return $tree;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================


    /**
     * Load terms with all language versions
     */
    protected function loadTermsWithLanguages($allTerm, $posttype, $type, $post_lang, $sort, $order)
    {
        if (empty($allTerm)) {
            return [];
        }

        // Get id_mains from current page
        $idMains = [];
        foreach ($allTerm as $term) {
            $idMain = $term['id_main'] ?? $term['id'];
            if (!in_array($idMain, $idMains)) {
                $idMains[] = $idMain;
            }
        }
        
        // Get all language versions
        $allLangTerms = TermsModel::query()
            ->whereIn('id_main', $idMains)
            ->where('posttype', $posttype)
            ->where('type', $type)
            ->orderBy($sort, $order)
            ->get();
        
        $terms_languages = $this->formatTermsByLanguage($allLangTerms, $post_lang);
        
        return $this->buildTree($terms_languages, $allTerm);
    }
}


