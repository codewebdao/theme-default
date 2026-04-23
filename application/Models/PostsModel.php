<?php

namespace App\Models;

use System\Database\BaseModel;
use System\Database\DB;

/**
 * PostsModel - Dynamic Post Type Model (NEW ARCHITECTURE)
 * 
 * Handles all post-related database operations using Query Builder and BaseModel methods
 * Priority: Model methods > Query Builder > Raw SQL (never use raw SQL with user input)
 * 
 * @package App\Models
 */
class PostsModel extends BaseModel
{
    /** @var string Table name (set dynamically based on posttype) */
    protected $table = '';
    
    /** @var string Posttype table name */
    protected $posttype_table = 'posttype';
    
    /** @var string Primary key */
    protected $primaryKey = 'id';
    
    /** @var bool Enable timestamps */
    public $timestamps = true;
    
    /** @var array Fillable fields */
    protected $fillable = [];

    /** @var string Current language code */
    protected $lang = APP_LANG;

    /** @var string Current posttype slug */
    protected $posttypeSlug = '';

    /**
     * Constructor - Set table dynamically based on posttype
     * 
     * @param string $posttype Posttype slug
     * @param string|null $lang Language code
     */
    public function __construct($posttype = '', $lang = APP_LANG)
    {
        parent::__construct();
        $this->lang = $lang;
        $this->posttypeSlug = $posttype;
        
        if (!empty($posttype)) {
            $this->table = posttype_name($posttype, $lang);
            if (empty($this->table)) {
                error_log("PostsModel: Failed to get table name for posttype '{$posttype}' and lang '{$lang}'");
            }
        }
    }
    
    /**
     * Validate that table is set before query
     * 
     * @throws \RuntimeException
     */
    protected function validateTable()
    {
        if (empty($this->table)) {
            throw new \RuntimeException(
                "PostsModel table is not set. Make sure to instantiate with a valid posttype: new PostsModel('posts', 'vi')"
            );
        }
    }

    // =========================================================================
    // BASIC CRUD OPERATIONS (Using BaseModel & Query Builder)
    // =========================================================================

    /**
     * Get post by ID from current table
     * 
     * @param int $id Post ID
     * @param string|array $fields Fields to select (* or array)
     * @return array|null
     */
    public function getById($id, $fields = "*")
    {
        $this->validateTable();
        
        // ✅ FIX: KHÔNG dùng static::find() vì nó tạo instance mới không có $table
        // Dùng $this->newQuery() để query trên instance hiện tại (có $table)
        
        if ($fields === '*') {
            // Query all fields
            return $this->newQuery()->where('id', $id)->first();
        }
        
        // With specific fields
        if (is_string($fields)) {
            $fieldsArray = array_map('trim', explode(',', $fields));
        } else {
            $fieldsArray = $fields;
        }
        
        return $this->newQuery()->select(...$fieldsArray)->where('id', $id)->first();
    }

    /**
     * Get post by ID from specific table
     * 
     * @param string $tableName Table name
     * @param int $id Post ID
     * @param string|array $fields Fields to select (* or array)
     * @return array|null
     */
    public function getPostById($tableName, $id, $fields = "*")
    {
        if (empty($tableName)) {
            return null;
        }
        
        $query = DB::table($tableName);
        
        if ($fields !== '*') {
            if (is_string($fields)) {
                $fieldsArray = array_map('trim', explode(',', $fields));
            } else {
                $fieldsArray = $fields;
            }
            $query->select(...$fieldsArray);
        }
        
        return $query->where('id', $id)->first();
    }

    /**
     * Get post by slug from current table
     * 
     * @param string $slug Post slug
     * @return array|null
     */
    public function getBySlug($slug)
    {
        $this->validateTable();
        // ✅ FIX: Use instance query (not static)
        return $this->newQuery()->where('slug', $slug)->first();
    }

    /**
     * Get posts with pagination
     * 
     * @param array $where WHERE conditions [['field', 'value'], ['field', 'value', '!=']]
     * @param string $orderBy Order by field
     * @param string $order Order direction (ASC/DESC)
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array ['data' => [], 'is_next' => bool, 'page' => int]
     */
    public function getPaginated($where = [], $orderBy = 'id', $order = 'DESC', $page = 1, $limit = 15)
    {
        $this->validateTable();
        
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery();
        
        // Apply WHERE conditions
        if (!empty($where)) {
            foreach ($where as $condition) {
                if (count($condition) === 2) {
                    $query->where($condition[0], $condition[1]);
                } elseif (count($condition) === 3) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
        }
        
        // Order by and paginate
        return $query->orderBy($orderBy, $order)->paginate($page, $limit);
    }

    /**
     * Insert post to current table
     * 
     * @param array $data Post data
     * @return int Last insert ID
     */
    public function insert($data)
    {
        $this->validateTable();
        // Use DB::table for insertGetId (BaseModel's create returns array)
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * Update post in current table
     * 
     * @param int $id Post ID
     * @param array $data Post data
     * @return int Affected rows
     */
    public function updatePost($id, $data)
    {
        $this->validateTable();
        // ✅ FIX: Use instance query (not static)
        return $this->newQuery()->where('id', $id)->update($data);
    }

    /**
     * Delete post from current table
     * 
     * @param int $id Post ID
     * @return int Affected rows
     */
    public function deletePost($id)
    {
        $this->validateTable();
        // ✅ FIX: Use DB::table() with instance table property
        return DB::table($this->table)->where('id', $id)->delete();
    }

    /**
     * Check if post exists by ID
     * 
     * @param int $id Post ID
     * @return bool
     */
    public function postExists($id)
    {
        $this->validateTable();
        // ✅ FIX: Use instance query (not static)
        return $this->newQuery()->where('id', $id)->exists();
    }

    /**
     * Get posts by multiple IDs (BATCH query)
     * 
     * ✅ OPTIMIZED: Use WHERE id IN (...) instead of multiple queries
     * 
     * @param array $ids Array of post IDs
     * @param string|array $fields Fields to select (* or ['id', 'title'])
     * @return array Array of posts
     */
    public function getPostsByIds($ids, $fields = '*')
    {
        $this->validateTable();
        
        if (empty($ids) || !is_array($ids)) {
            return [];
        }

        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery()->whereIn('id', $ids);
        
        // Select specific fields if needed
        if ($fields !== '*') {
            if (is_string($fields)) {
                $fieldsArray = array_map('trim', explode(',', $fields));
            } else {
                $fieldsArray = $fields;
            }
            $query->select(...$fieldsArray);
        }
        
        return $query->get();
    }

    /**
     * Check if slug is unique (excluding specific ID)
     * 
     * @param string $slug Slug to check
     * @param int|null $excludeId ID to exclude from check
     * @return bool True if unique, false if duplicate
     */
    public function isSlugUnique($slug, $excludeId = null)
    {
        $this->validateTable();
        
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery()->where('slug', $slug);
        
        if ($excludeId !== null) {
            $query->where('id', $excludeId, '!=');
        }
        
        return !$query->exists();
    }

    /**
     * Generate unique slug
     * 
     * @param string $slug Base slug
     * @param int|null $excludeId ID to exclude from check
     * @return string Unique slug
     */
    public function generateUniqueSlug($slug, $excludeId = null)
    {
        $this->validateTable();
        
        $originalSlug = $slug;
        $counter = 2;
        
        while (!$this->isSlugUnique($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Check if field value is unique (excluding specific ID)
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param int|null $excludeId ID to exclude from check
     * @return bool True if unique, false if duplicate
     */
    public function isFieldUnique($field, $value, $excludeId = null)
    {
        $this->validateTable();
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery()->where($field, $value);
        
        if ($excludeId !== null) {
            $query->where('id', $excludeId, '!=');
        }
        $query->where($field, $value);
        return !$query->exists();
    }

    // =========================================================================
    // SEARCH & FILTER OPERATIONS
    // =========================================================================

    /**
     * Search posts by keyword
     * 
     * @param string $keyword Search keyword
     * @param array $searchFields Fields to search in
     * @param array $filters Additional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function search($keyword, $searchFields = ['title', 'search_string'], $filters = [], $page = 1, $limit = 15)
    {
        $this->validateTable();
        
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery();
        
        // Search in multiple fields
        if (!empty($keyword) && !empty($searchFields)) {
            $query->whereGroup(function($q) use ($keyword, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'LIKE', '%' . $keyword . '%');
                }
            });
        }
        
        // Apply additional filters
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->orderBy('id', 'DESC')->paginate($page, $limit);
    }

    /**
     * Count posts with filters
     * 
     * @param array $filters Filters
     * @return int
     */
    public function countWithFilters($filters = [])
    {
        $this->validateTable();
        
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery();
        
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->count();
    }

    // =========================================================================
    // POSTTYPE OPERATIONS
    // =========================================================================

    /**
     * Get posttype configuration by slug
     * 
     * @param string $slug Posttype slug
     * @return array|null
     */
    public function getPosttypeBySlug($slug)
    {
        try {
            if (empty($slug) || !is_string($slug)) {
                return null;
            }
            
            return DB::table($this->posttype_table)
                ->where('slug', $slug)
                ->first();
        } catch (\Exception $e) {
            error_log("Error in getPosttypeBySlug: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update posttype configuration
     * 
     * @param int $id Posttype ID
     * @param array $data Posttype data
     * @return int|bool Affected rows or false
     */
    public function updatePosttype($id, $data)
    {
        try {
            if (!is_numeric($id) || empty($data)) {
                return false;
            }

            return DB::table($this->posttype_table)
                ->where('id', $id)
                ->update($data);
        } catch (\Exception $e) {
            error_log("Error in updatePosttype: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // TERMS RELATIONSHIP OPERATIONS (Using Query Builder)
    // =========================================================================

    /**
     * Get term IDs by post ID and language
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param string $lang Language code
     * @return array Array of term IDs
     */
    public function getTermIdsByPostId($relationTable, $postId, $lang)
    {
        try {
            return DB::table($relationTable)
                ->select(['rel_id'])
                ->where('post_id', $postId)
                ->whereGroup(function($q) use ($lang) {
                    $q->where('lang', $lang)->orWhere('lang', 'all');
                })
                ->whereNotNull('rel_id')
                ->pluck('rel_id');
        } catch (\Exception $e) {
            error_log("Error in getTermIdsByPostId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create term relationship
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param int $termId Term ID
     * @param string $lang Language code
     * @return int Last insert ID
     */
    public function createTermRelationship($relationTable, $postId, $termId, $lang)
    {
        return DB::table($relationTable)->insertGetId([
            'post_id' => (int)$postId,
            'rel_id' => (int)$termId,
            'lang' => $lang
        ]);
    }

    /**
     * Delete term relationship
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param int $termId Term ID
     * @param string|null $lang Language code (null = all languages)
     * @return int Affected rows
     */
    public function deleteTermRelationship($relationTable, $postId, $termId, $lang = null)
    {
        $query = DB::table($relationTable)
            ->where('post_id', $postId)
            ->where('rel_id', $termId);
        
        if ($lang !== null) {
            $query->where('lang', $lang);
        }
        
        return $query->delete();
    }

    /**
     * Delete all term relationships by post ID
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param string|null $lang Language code (null = all languages)
     * @return int Affected rows
     */
    public function deleteAllTermRelationships($relationTable, $postId, $lang = null)
    {
        $query = DB::table($relationTable)
            ->where('post_id', $postId)
            ->whereNotNull('rel_id');
        
        if ($lang !== null) {
            $query->where('lang', $lang);
        }
        
        return $query->delete();
    }

    /**
     * Get term IDs by post ID (without language filter)
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @return array Array of term IDs
     */
    public function getTermIdsByPostIdAll($relationTable, $postId)
    {
        try {
            return DB::table($relationTable)
                ->select(['rel_id'])
                ->where('post_id', $postId)
                ->whereNotNull('rel_id')
                ->pluck('rel_id');
        } catch (\Exception $e) {
            error_log("Error in getTermIdsByPostIdAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count posts with filters (compatibility method)
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters
     * @return int
     */
    public function countPosts($where = '', $params = [])
    {
        $this->validateTable();
        
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery();
        
        if (!empty($where)) {
            $query->whereRaw($where, $params);
        }
        
        return $query->count();
    }

    /**
     * Get posts list (compatibility method)
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters
     * @param string $orderBy ORDER BY clause
     * @param int|null $limit Limit
     * @return array
     */
    public function getPostsList($where = '', $params = [], $orderBy = 'id DESC', $limit = null)
    {
        $this->validateTable();
        
        // ✅ FIX: Use instance query (not static)
        $query = $this->newQuery();
        
        if (!empty($where)) {
            $query->whereRaw($where, $params);
        }
        
        // Parse orderBy
        if (!empty($orderBy)) {
            $parts = preg_split('/\s+/', trim($orderBy), 2);
            $field = $parts[0];
            $direction = isset($parts[1]) ? strtoupper($parts[1]) : 'ASC';
            
            if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                $query->orderBy($field, $direction);
            }
        }
        
        if ($limit !== null) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    // =========================================================================
    // REFERENCE RELATIONSHIP OPERATIONS (Using Query Builder)
    // =========================================================================

    /**
     * Get reference post IDs by post ID (for Reference fields)
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param string $posttypeSlug Referenced posttype slug
     * @param int $fieldId Field ID
     * @param string $lang Language code
     * @return array Array of related post IDs
     */
    public function getReferenceIdsByPostId($relationTable, $postId, $posttypeSlug, $fieldId, $lang)
    {
        try {
            return DB::table($relationTable)
                ->select(['rel_id'])
                ->where('post_id', $postId)
                ->where('rel_type', $posttypeSlug)
                ->where('field_id', $fieldId)
                ->whereGroup(function($q) use ($lang) {
                    $q->where('lang', $lang)->orWhere('lang', 'all');
                })
                ->pluck('rel_id');
        } catch (\Exception $e) {
            error_log("Error in getReferenceIdsByPostId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get post IDs that reference this post (reverse lookup)
     * 
     * @param string $relationTable Relationship table name
     * @param int $postRelId Related post ID (this post)
     * @param string $posttypeSlug Posttype slug
     * @param int $fieldId Field ID
     * @param string $lang Language code
     * @return array Array of post IDs
     */
    public function getPostIdsByReference($relationTable, $postRelId, $posttypeSlug, $fieldId, $lang)
    {
        try {
            return DB::table($relationTable)
                ->select(['post_id'])
                ->where('rel_id', $postRelId)
                ->where('rel_type', $posttypeSlug)
                ->where('field_id', $fieldId)
                ->whereGroup(function($q) use ($lang) {
                    $q->where('lang', $lang)->orWhere('lang', 'all');
                })
                ->pluck('post_id');
        } catch (\Exception $e) {
            error_log("Error in getPostIdsByReference: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create reference relationship
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param string $posttypeSlug Referenced posttype slug
     * @param int $fieldId Field ID
     * @param int $postRelId Related post ID
     * @param string $lang Language code
     * @return int Last insert ID
     */
    public function createReferenceRelationship($relationTable, $postId, $posttypeSlug, $fieldId, $postRelId, $lang)
    {
        return DB::table($relationTable)->insertGetId([
            'post_id' => (int)$postId,
            'rel_type' => $posttypeSlug,
            'rel_id' => (int)$postRelId,
            'field_id' => (int)$fieldId,
            'lang' => $lang
        ]);
    }

    /**
     * Delete reference relationship
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param int $postRelId Related post ID
     * @param string $posttypeSlug Posttype slug
     * @param int $fieldId Field ID
     * @param string|null $lang Language code
     * @return int Affected rows
     */
    public function deleteReferenceRelationship($relationTable, $postId, $postRelId, $posttypeSlug, $fieldId, $lang = null)
    {
        $query = DB::table($relationTable)
            ->where('post_id', $postId)
            ->where('rel_id', $postRelId)
            ->where('rel_type', $posttypeSlug)
            ->where('field_id', $fieldId);
        
        if ($lang !== null) {
            $query->where('lang', $lang);
        }
        
        return $query->delete();
    }

    /**
     * Delete all reference relationships by post ID
     * 
     * @param string $relationTable Relationship table name
     * @param int $postId Post ID
     * @param string|null $lang Language code
     * @return int Affected rows
     */
    public function deleteAllReferenceRelationships($relationTable, $postId, $lang = null)
    {
        $query = DB::table($relationTable)
            ->where('post_id', $postId)
            ->whereNotNull('rel_id');
        
        if ($lang !== null) {
            $query->where('lang', $lang);
        }
        
        return $query->delete();
    }

    // =========================================================================
    // LANGUAGE OPERATIONS
    // =========================================================================

    /**
     * Check if post exists in specific language
     * 
     * @param int $id Post ID
     * @param string $lang Language code
     * @return bool
     */
    public function existsInLanguage($id, $lang)
    {
        $tableName = posttype_name($this->posttypeSlug, $lang);
        
        if (empty($tableName)) {
            return false;
        }
        
        return DB::table($tableName)->where('id', $id)->exists();
    }

    /**
     * Clone post to another language
     * 
     * @param int $id Post ID
     * @param string $sourceLang Source language
     * @param string $targetLang Target language
     * @param array $overrideData Data to override
     * @return int Last insert ID
     */
    public function cloneToLanguage($id, $sourceLang, $targetLang, $overrideData = [])
    {
        $sourceTable = posttype_name($this->posttypeSlug, $sourceLang);
        $targetTable = posttype_name($this->posttypeSlug, $targetLang);
        
        if (empty($sourceTable) || empty($targetTable)) {
            return false;
        }
        
        // Get source post data
        $sourcePost = DB::table($sourceTable)->where('id', $id)->first();
        
        if (empty($sourcePost)) {
            return false;
        }
        
        // Merge with override data
        $targetData = array_merge($sourcePost, $overrideData);
        
        // Update timestamps
        $targetData['updated_at'] = date('Y-m-d H:i:s');
        
        // Insert to target table
        return DB::table($targetTable)->insertGetId($targetData);
    }

    // =========================================================================
    // RELATIONSHIPS (for Eager Loading)
    // =========================================================================

    /**
     * Terms relationship (BelongsToMany)
     * 
     * Post has many terms through pivot table
     * 
     * @return \System\Database\ModelRelations\BelongsToMany
     */
    public function terms()
    {
        global $post_table_query;
        $postrelTable = $post_table_query ? $post_table_query : $this->getTableUnprefix();
        // Determine pivot table based on current posttype
        $pivotTable = table_postrel($postrelTable);
        
        return $this->belongsToMany(
            'App\\Models\\TermsModel',
            $pivotTable,
            'post_id',
            'rel_id',
            'id',
            'id_main'
        );
    }

    /**
     * Categories relationship (BelongsToMany)
     * 
     * Post has many categories through pivot table
     * 
     * @return \System\Database\ModelRelations\BelongsToMany
     */
    public function categories()
    {
        global $post_table_query;
        $postrelTable = $post_table_query ? $post_table_query : $this->getTableUnprefix();
        // Determine pivot table based on current posttype
        $pivotTable = table_postrel($postrelTable);
        
        return $this->belongsToMany(
            'App\\Models\\TermsModel',  // Related model
            $pivotTable,                // Pivot table
            'post_id',                  // Foreign key in pivot (pivot.post_id = this.id)
            'rel_id',                   // Related key in pivot (pivot.rel_id = terms.id_main)
            'id',                       // Parent key (this model)
            'id_main'                   // Related key (terms.id_main)
        );
    }

    /**
     * Tags relationship (BelongsToMany)
     * 
     * Post has many tags through pivot table
     * 
     * @return \System\Database\ModelRelations\BelongsToMany
     */
    public function tags()
    {
        global $post_table_query;
        $postrelTable = $post_table_query ? $post_table_query : $this->getTableUnprefix();
        // Determine pivot table based on current posttype
        $pivotTable = table_postrel($postrelTable);
        
        return $this->belongsToMany(
            'App\\Models\\TermsModel',
            $pivotTable,
            'post_id',
            'rel_id',
            'id',
            'id_main'
        );
    }

    /**
     * Author relationship (BelongsTo)
     * 
     * Post belongs to one author (user)
     * 
     * @return \System\Database\ModelRelations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(
            'App\\Models\\UsersModel',  // Related model (no leading backslash)
            'author',                   // Foreign key on this model (posts.author)
            'id'                        // Owner key on users table (users.id)
        );
    }

    /**
     * Comments relationship (HasMany)
     * 
     * Post has many comments
     * 
     * @return \System\Database\ModelRelations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(
            '\App\\Models\\CommentsModel',  // Related model
            'post_id',                     // Foreign key on comments table
            'id'                           // Local key on this model
        );
    }


    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Get current posttype slug
     * 
     * @return string
     */
    public function getPosttypeSlug()
    {
        return $this->posttypeSlug;
    }

    /**
     * Get current language
     * 
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set language dynamically
     * 
     * @param string $lang Language code
     * @return $this
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        $this->table = posttype_name($this->posttypeSlug, $lang);
        return $this;
    }
}

