<?php
namespace App\Models;

use System\Database\BaseModel;

/**
 * LanguagesModel - Multi-language Management
 * 
 * This model manages application languages, default language settings,
 * and language activation status.
 * Uses new BaseModel with Laravel-like API.
 * 
 * @package App\Models
 */
class LanguagesModel extends BaseModel
{
    // =========================================================================
    // CONFIGURATION PROPERTIES
    // =========================================================================

    /** @var string Unprefixed base table name */
    protected $table = 'languages';
    
    /** @var string|null Connection name */
    protected $connection = null;
    
    /** @var string Primary key column name */
    protected $primaryKey = 'id';
    
    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = ['name', 'code', 'flag', 'locale', 'is_default', 'status'];

    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    /** @var bool Enable automatic timestamps */
    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const CREATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';
    public const UPDATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';

    /** @var array<string, mixed> Default attribute values */
    protected $attributes = [
        'status' => 'active',
        'is_default' => 0,
    ];

    // =========================================================================
    // SCHEMA DEFINITION
    // =========================================================================

    /**
     * Define the languages table structure.
     * Using new Schema Builder format with timestamps.
     * 
     * @return array Schema definition
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['primary' => true, 'unsigned' => true]],
            ['type' => 'varchar', 'name' => 'code', 'options' => ['length' => 2, 'unique' => true]],
            ['type' => 'varchar', 'name' => 'name', 'options' => ['length' => 100]],
            ['type' => 'varchar', 'name' => 'flag', 'options' => ['length' => 2, 'null' => true]],
            ['type' => 'varchar', 'name' => 'locale', 'options' => ['length' => 10, 'null' => true, 'comment' => 'Locale code (e.g., en_US, vi_VN)']],
            ['type' => 'tinyint', 'name' => 'is_default', 'options' => ['default' => 0]],
            ['type' => 'enum', 'name' => 'status', 'options' => ['values' => ['active', 'inactive'], 'default' => 'active']],
        ];
    }

    // =========================================================================
    // PAGINATION METHODS
    // =========================================================================

    /**
     * Get paginated languages with custom fields and filtering.
     * Uses modern paginateWith() from Query Builder.
     * 
     * @param string $fields Fields to select
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @param string $orderBy ORDER BY clause
     * @param int $page Current page number
     * @param int|null $limit Items per page
     * @return array Pagination result
     */
    public function getLanguagesFieldsPagination($fields = '*', $where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        return static::query()->paginateWith($fields, $where, $params, $orderBy, $page, $limit);
    }

    // =========================================================================
    // RETRIEVAL METHODS
    // =========================================================================

    /**
     * Get all languages (active and inactive).
     * 
     * @return array List of all languages
     */
    public function getAllLanguages()
    {
        return static::all();
    }

    /**
     * Get only active languages.
     * 
     * @return array List of active languages
     */
    public function getActiveLanguages()
    {
        return static::where('status', 'active')->get();
    }

    /**
     * Get the default language record.
     * 
     * @return array|null Default language data or null
     */
    public function getDefaultLanguage()
    {
        return static::where('is_default', 1)->first();
    }

    /**
     * Get default language code only.
     * 
     * @return string|null Language code (e.g., 'en', 'vi') or null
     */
    public function getDefaultLanguageCode()
    {
        $lang = static::where('is_default', 1)->first();
        if (!empty($lang) && !empty($lang['code'])) {
            return $lang['code'];
        }
        return null;
    }

    /**
     * Get language by ID.
     * 
     * @param int $id Language ID
     * @return array|null Language data or null
     */
    public function getLanguageById($id)
    {
        return static::find($id);
    }

    /**
     * Get language by code (e.g., 'en', 'vi').
     * 
     * @param string $code Language code
     * @return array|null Language data or null
     */
    public function getLanguageByCode($code, $excludeId = null)
    {
        $query = static::where('code', $code);
        if (!empty($excludeId)) {
            $query = $query->where('id', '!=', (int)$excludeId);
        }
        return $query->first();
    }

    /**
     * Get language by country flag code.
     *
     * @param string $flag Country code (e.g., 'us', 'vn')
     * @param int|null $excludeId Optional record ID to exclude
     * @return array|null Language data or null
     */
    public function getLanguageByFlag($flag, $excludeId = null)
    {
        $query = static::where('flag', strtolower($flag));
        if (!empty($excludeId)) {
            $query = $query->where('id', '!=', (int)$excludeId);
        }
        return $query->first();
    }

    /**
     * Get language by locale code.
     *
     * @param string $locale Locale (e.g., 'en_US', 'vi_VN')
     * @param int|null $excludeId Optional record ID to exclude
     * @return array|null Language data or null
     */
    public function getLanguageByLocale($locale, $excludeId = null)
    {
        $query = static::where('locale', $locale);
        if (!empty($excludeId)) {
            $query = $query->where('id', '!=', (int)$excludeId);
        }
        return $query->first();
    }

    // =========================================================================
    // MANIPULATION METHODS
    // =========================================================================

    /**
     * Unset default flag for all languages.
     * Used before setting a new default language.
     * 
     * @return int Number of affected rows
     */
    public function unsetDefaultLanguage()
    {
        return static::where('is_default', 1)->update(['is_default' => 0]);
    }

    /**
     * Add a new language.
     * Uses modern create() method with automatic fillable filtering and timestamps.
     * 
     * @param array $data Language data (name, code, flag, is_default, status)
     * @return array Result with success status and id or error message
     */
    public function addLanguage($data)
    {
        try {
            // Create new record (auto-filters fillable & adds timestamps)
            $id = static::create($data);
            
            return [
                'success' => true,
                'id' => $id
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update an existing language.
     * Uses modern query builder with automatic fillable filtering.
     * 
     * @param int $id Language ID
     * @param array $data Updated language data
     * @return array Result with success status or error message
     */
    public function setLanguage($id, $data)
    {
        try {
            // Update record (auto-filters fillable & updates timestamps)
            $affected = static::where('id', $id)->update($data);
            
            return [
                'success' => true,
                'id' => $id,
                'affected' => $affected
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'id' => $id,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete one or multiple languages by IDs.
     * Uses modern whereIn() method for cleaner syntax.
     * 
     * @param array $ids Array of language IDs to delete
     * @return int Number of deleted rows
     */
    public function deleteLanguage($ids)
    {
        // Ensure $ids is an array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return static::whereIn('id', $ids)->delete();
    }
}
