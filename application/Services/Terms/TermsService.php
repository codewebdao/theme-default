<?php

namespace App\Services\Terms;

use App\Models\TermsModel;
use App\Models\PostsModel;

/**
 * TermsService - Terms/Taxonomy Service
 * 
 * Handles all term-related business logic
 * Delegates to TermsValidationService for validation
 * 
 * @package App\Services\Terms
 */
class TermsService
{
    /** @var TermsModel */
    protected $termsModel;

    /** @var TermsValidationService */
    protected $validationService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->termsModel = new TermsModel();
        $this->validationService = new TermsValidationService();
    }

    /**
     * Create new term
     * 
     * @param array $data Term data
     * @return array ['success' => bool, 'term_id' => int|null, 'errors' => array]
     */
    public function create($data)
    {
        try {
            // Validate and prepare data
            $prepared = $this->validationService->validateAndPrepare($data);
            
            if (!$prepared['success']) {
                return [
                    'success' => false,
                    'term_id' => null,
                    'errors' => $prepared['errors']
                ];
            }

            $termData = $prepared['data'];

            // Ensure unique slug
            $termData['slug'] = $this->generateUniqueSlug(
                $termData['slug'],
                $termData['type'],
                $termData['posttype'],
                $termData['lang']
            );

            // Insert term
            $termId = $this->termsModel->addTerm($termData);

            if (!$termId) {
                return [
                    'success' => false,
                    'term_id' => null,
                    'errors' => ['database' => ['Failed to insert term']]
                ];
            }

            // If id_main = 0, set it to the new term ID
            if ($termData['id_main'] == 0) {
                $this->termsModel->setTerm($termId, ['id_main' => $termId]);
            }

            // Fire event
            if (class_exists('\System\Libraries\Events')) {
                \System\Libraries\Events::run('Backend\\TermsAddEvent', $termData);
            }

            return [
                'success' => true,
                'term_id' => $termId,
                'errors' => []
            ];
        } catch (\Exception $e) {
            error_log("TermsService::create error: " . $e->getMessage());
            return [
                'success' => false,
                'term_id' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Update existing term
     * 
     * @param int $termId Term ID
     * @param array $data Term data
     * @return array ['success' => bool, 'errors' => array]
     */
    public function update($termId, $data)
    {
        try {
            // Check if term exists
            $existingTerm = $this->termsModel->getTermById($termId);
            
            if (empty($existingTerm)) {
                return [
                    'success' => false,
                    'errors' => ['term' => ['Term not found']]
                ];
            }

            // Auto-update slug if name changed and slug not provided
            if (isset($data['name']) && !isset($data['slug'])) {
                $data['slug'] = url_slug($data['name']);
            }

            // Always update updated_at
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Update term
            $success = $this->termsModel->setTerm($termId, $data);

            if (!$success) {
                return [
                    'success' => false,
                    'errors' => ['database' => ['Failed to update term']]
                ];
            }

            // Fire event
            if (class_exists('\System\Libraries\Events')) {
                \System\Libraries\Events::run('Backend\\TermsEditEvent', $data);
            }

            return [
                'success' => true,
                'errors' => []
            ];
        } catch (\Exception $e) {
            error_log("TermsService::update error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Delete term
     * 
     * @param int $termId Term ID
     * @return array ['success' => bool, 'errors' => array]
     */
    public function delete($termId)
    {
        try {
            // Get children and unset their parent
            $children = $this->termsModel->getTermByParent($termId);
            if (!empty($children)) {
                foreach ($children as $child) {
                    $this->termsModel->setTerm($child['id'], ['parent' => null]);
                }
            }

            // Delete term
            $success = $this->termsModel->delTerm($termId);

            if (!$success) {
                return [
                    'success' => false,
                    'errors' => ['database' => ['Failed to delete term']]
                ];
            }

            // Fire event
            if (class_exists('\System\Libraries\Events')) {
                \System\Libraries\Events::run('Backend\\TermsDeleteEvent', $termId);
            }

            return [
                'success' => true,
                'errors' => []
            ];
        } catch (\Exception $e) {
            error_log("TermsService::delete error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Clone term to another language
     * 
     * @param int $sourceTermId Source term ID
     * @param string $targetLang Target language
     * @param array $overrides Data to override
     * @return array ['success' => bool, 'term_id' => int|null, 'errors' => array]
     */
    public function cloneToLanguage($sourceTermId, $targetLang, $overrides = [])
    {
        try {
            // Get source term
            $sourceTerm = $this->termsModel->getTermById($sourceTermId);
            
            if (empty($sourceTerm)) {
                return [
                    'success' => false,
                    'term_id' => null,
                    'errors' => ['term' => ['Source term not found']]
                ];
            }

            $idMain = $sourceTerm['id_main'];

            // Check if already exists in target language
            $existingTerm = $this->termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                $overrides['slug'] ?? $sourceTerm['slug'],
                $sourceTerm['posttype'],
                $sourceTerm['type'],
                $targetLang
            );

            if (!empty($existingTerm)) {
                $existingId = is_array($existingTerm) ? $existingTerm[0]['id'] : $existingTerm['id'];
                
                return [
                    'success' => true,
                    'term_id' => $existingId,
                    'errors' => [],
                    'message' => 'Term already exists in this language'
                ];
            }

            // Create new term
            $newTermData = [
                'name' => $overrides['name'] ?? $sourceTerm['name'],
                'slug' => $overrides['slug'] ?? $sourceTerm['slug'],
                'description' => $overrides['description'] ?? $sourceTerm['description'],
                'type' => $sourceTerm['type'],
                'posttype' => $sourceTerm['posttype'],
                'parent' => $overrides['parent'] ?? $sourceTerm['parent'],
                'lang' => $targetLang,
                'id_main' => $idMain,
                'seo_title' => $overrides['seo_title'] ?? $sourceTerm['seo_title'],
                'seo_desc' => $overrides['seo_desc'] ?? $sourceTerm['seo_desc'],
                'status' => $overrides['status'] ?? $sourceTerm['status']
            ];

            return $this->create($newTermData);
        } catch (\Exception $e) {
            error_log("TermsService::cloneToLanguage error: " . $e->getMessage());
            return [
                'success' => false,
                'term_id' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Get term by ID
     * 
     * @param int $termId Term ID
     * @return array|null Term data
     */
    public function get($termId)
    {
        try {
            return $this->termsModel->getTermById($termId);
        } catch (\Exception $e) {
            error_log("TermsService::get error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get terms list
     * 
     * @param array $filters Filters
     * @param array $options Options
     * @return array Terms list
     */
    public function getList($filters = [], $options = [])
    {
        try {
            // ✅ Use get_terms() helper (optimized)
            return get_terms(array_merge($filters, $options));
        } catch (\Exception $e) {
            error_log("TermsService::getList error: " . $e->getMessage());
            return ['data' => [], 'is_next' => false];
        }
    }

    /**
     * Generate unique slug for term
     * 
     * @param string $slug Desired slug
     * @param string $type Term type
     * @param string $posttype Posttype slug
     * @param string $lang Language code
     * @param int|null $excludeId Exclude this ID
     * @return string Unique slug
     */
    public function generateUniqueSlug($slug, $type, $posttype, $lang, $excludeId = null)
    {
        $originalSlug = $slug;
        $counter = 2;

        while (true) {
            $existing = $this->termsModel->getTermsSlugAndByTypeAndPostTypeAndLang(
                $slug,
                $posttype,
                $type,
                $lang
            );

            // Check if unique or is the same term being updated
            if (empty($existing)) {
                break;
            }

            $existingId = is_array($existing) ? $existing[0]['id'] : $existing['id'];
            
            if ($excludeId && $existingId == $excludeId) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Find or create term
     * 
     * @param int $idMain Term id_main
     * @param array $sourceTermInfo Source term data
     * @param string $posttypeSlug Posttype slug
     * @param string $targetLang Target language
     * @return int|false Term ID or false
     */
    public function findOrCreate($idMain, $sourceTermInfo, $posttypeSlug, $targetLang)
    {
        try {
            // Try to find existing term
            $targetTerms = $this->termsModel->getTermsByTypeAndPostTypeAndLang(
                $posttypeSlug,
                $sourceTermInfo['type'],
                $targetLang
            );

            foreach ($targetTerms as $targetTerm) {
                if ($targetTerm['id_main'] == $idMain || $targetTerm['id'] == $idMain) {
                    return $targetTerm['id'];
                }
            }

            // Not found, create new term
            $result = $this->create([
                'name' => $sourceTermInfo['name'],
                'slug' => $sourceTermInfo['slug'],
                'description' => $sourceTermInfo['description'] ?? '',
                'type' => $sourceTermInfo['type'],
                'posttype' => $posttypeSlug,
                'lang' => $targetLang,
                'parent' => $sourceTermInfo['parent'] ?? null,
                'id_main' => $idMain,
                'seo_title' => $sourceTermInfo['seo_title'] ?? '',
                'seo_desc' => $sourceTermInfo['seo_desc'] ?? '',
                'status' => $sourceTermInfo['status'] ?? 'active'
            ]);

            return $result['success'] ? $result['term_id'] : false;
        } catch (\Exception $e) {
            error_log("TermsService::findOrCreate error: " . $e->getMessage());
            return false;
        }
    }
}

