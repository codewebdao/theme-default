<?php

namespace App\Models;

use System\Database\BaseModel;

/**
 * TermsModel - Terms/Taxonomy Model (NEW ARCHITECTURE)
 * 
 * Copy from TermsModel with same functionality
 * Sử dụng static BaseModel methods
 * 
 * @package App\Models
 */
class TermsModel extends BaseModel
{
    /** @var string Table name */
    protected $table = 'terms';
    
    /** @var string Primary key */
    protected $primaryKey = 'id';
    
    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var array Fillable fields */
    protected $fillable = [
        'name', 'slug', 'description', 'seo_title', 'seo_desc',
        'type', 'posttype', 'parent', 'lang', 'id_main', 'status'
    ];

    /** @var array Guarded fields */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Define the table schema
     */
    public function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['primary' => true, 'unsigned' => true]],
            ['type' => 'varchar', 'name' => 'name', 'options' => ['length' => 150, 'null' => false, 'default' => '']],
            ['type' => 'varchar', 'name' => 'slug', 'options' => ['length' => 150, 'null' => false, 'index' => true]],
            ['type' => 'text', 'name' => 'description', 'options' => ['null' => true]],
            ['type' => 'varchar', 'name' => 'posttype', 'options' => ['length' => 50, 'null' => true, 'index' => true]],
            ['type' => 'varchar', 'name' => 'seo_title', 'options' => ['length' => 255, 'null' => true]],
            ['type' => 'text', 'name' => 'seo_desc', 'options' => ['null' => true]],
            ['type' => 'varchar', 'name' => 'type', 'options' => ['length' => 50, 'null' => false, 'default' => 'category', 'index' => true]],
            ['type' => 'int', 'name' => 'parent', 'options' => ['unsigned' => true, 'null' => true, 'default' => 0, 'index' => true]],
            ['type' => 'varchar', 'name' => 'lang', 'options' => ['length' => 3, 'null' => true, 'index' => true]],
            ['type' => 'int', 'name' => 'id_main', 'options' => ['unsigned' => true, 'null' => true, 'default' => 0, 'index' => true]],
            ['type' => 'enum', 'name' => 'status', 'options' => ['values' => ['active', 'inactive', 'deleted'], 'default' => 'active']]
        ];
    }

    // =========================================================================
    // QUERY METHODS (Using static BaseModel methods)
    // =========================================================================

    /**
     * Get terms by query with pagination
     */
    public function getTermsFieldsPagination($fields = '*', $where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        return static::query()->paginateWith($fields, $where, $params, $orderBy, $page, $limit);
    }

    /**
     * Get all terms
     */
    public function getTerms($posttype, $type, $lang)
    {
        return static::where('lang', $lang)
            ->where('posttype', $posttype)
            ->where('type', $type)
            ->get();
    }

    /**
     * Get all taxonomies
     */
    public function getTaxonomies($where = '', $params = [], $orderBy = 'id desc', $limit = null, $offset = null)
    {
        $query = static::query();
        
        if (!empty($where)) {
            $query->whereRaw($where, $params);
        }
        
        if (!empty($orderBy)) {
            $parts = preg_split('/\s+/', trim($orderBy), 2);
            $column = $parts[0];
            $direction = isset($parts[1]) ? strtoupper($parts[1]) : 'ASC';
            $query->orderBy($column, $direction);
        }
        
        if ($limit !== null) {
            $query->limit($limit);
        }
        
        if ($offset !== null) {
            $query->offset($offset);
        }
        
        return $query->get();
    }

    /**
     * Get term by ID
     */
    public function getTermById($id)
    {
        return static::find($id);
    }

    /**
     * Get term by slug
     */
    public function getTermBySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get terms by parent
     */
    public function getTermByParent($parent_id)
    {
        return static::where('parent', $parent_id)->get();
    }

    /**
     * Get terms by type
     */
    public function getTermsByType($type, $orderBy = 'id desc')
    {
        $query = static::where('type', $type);
        
        if (!empty($orderBy)) {
            $parts = preg_split('/\s+/', trim($orderBy), 2);
            $column = $parts[0];
            $direction = isset($parts[1]) ? strtoupper($parts[1]) : 'ASC';
            $query->orderBy($column, $direction);
        }
        
        return $query->get();
    }

    /**
     * Get terms by type and posttype
     */
    public function getTermsByTypeAndPostType($posttype, $type)
    {
        return static::where('type', $type)
            ->where('posttype', $posttype)
            ->get();
    }

    /**
     * Get terms by type, posttype and language
     */
    public function getTermsByTypeAndPostTypeAndLang($posttype, $type, $lang)
    {
        return static::where('type', $type)
            ->where('posttype', $posttype)
            ->where('lang', $lang)
            ->get();
    }

    /**
     * Get terms by slug, type, posttype and language
     */
    public function getTermsSlugAndByTypeAndPostTypeAndLang($slug, $posttype, $type, $lang)
    {
        return static::where('slug', $slug)
            ->where('type', $type)
            ->where('posttype', $posttype)
            ->where('lang', $lang)
            ->get();
    }

    /**
     * Get terms by id_main
     */
    public function getTermByIdMain($id_main)
    {
        return static::where('id_main', $id_main)->get();
    }

    /**
     * Add a new term
     */
    public function addTerm($data)
    {
        return static::create($data);
    }

    /**
     * Update an existing term
     */
    public function setTerm($id, $data)
    {
        return static::where('id', $id)->update($data);
    }

    /**
     * Delete a term
     */
    public function delTerm($id)
    {
        return static::destroy($id);
    }

    /**
     * Delete all terms by posttype
     */
    public function delTermByPostType($posttype)
    {
        return static::where('posttype', $posttype)->delete();
    }

    /**
     * Delete all terms by type
     */
    public function delTermByType($type)
    {
        return static::where('type', $type)->delete();
    }

    /**
     * Delete all terms by posttype and lang
     */
    public function delTermByPostTypeAndLang($posttype, $lang)
    {
        return static::where('posttype', $posttype)
            ->where('lang', $lang)
            ->delete();
    }

    /**
     * Get term by slug and posttype
     */
    public function getTermBySlugAndPostType($posttype, $slug)
    {
        return static::where('slug', $slug)
            ->where('posttype', $posttype)
            ->first();
    }

    /**
     * Get terms by IDs and type
     */
    public function getTermsByIdsAndType($ids, $type, $lang = APP_LANG)
    {
        if (empty($ids) || !is_array($ids)) {
            return [];
        }
        
        return static::select('name')
            ->whereIn('id', $ids)
            ->where('type', $type)
            ->where('lang', $lang)
            ->get();
    }

    /**
     * Change type (bulk update)
     */
    public function updateTermType($oldType, $newType)
    {
        return static::where('type', $oldType)->update(['type' => $newType]);
    }

    /**
     * Change posttype (bulk update)
     */
    public function updateTermPostType($oldType, $newType)
    {
        return static::where('posttype', $oldType)->update(['posttype' => $newType]);
    }

    /**
     * Get posts by term (cross-table query)
     */
    public function getPostsByTerm($termId, $postTable = 'posts', $termRelationshipTable = 'post_term_relationships')
    {
        // Get all post IDs related to the term
        $relationships = \System\Database\DB::table($termRelationshipTable)
            ->where('rel_id', $termId)
            ->get();
        
        $postIds = array_column($relationships, 'post_id');

        if (empty($postIds)) {
            return [];
        }

        // Get posts by IDs
        return \System\Database\DB::table($postTable)
            ->whereIn('id', $postIds)
            ->get();
    }
}

