<?php

namespace Plugins\Acfields\Models;

use System\Database\BaseModel;
use System\Database\DB;

/**
 * AcfieldsModel - Dynamic Form & Content Type Builder
 * 
 * This model manages custom post types, their fields, and dynamic tables.
 * Uses new BaseModel with Laravel-like API and Schema Builder.
 * 
 * Architecture: Main file uses traits for organization (similar to Builder.php)
 * 
 * @package Plugins\Acfields\Models
 */
class AcfieldsModel extends BaseModel
{
    // =========================================================================
    // TRAIT IMPORTS
    // =========================================================================

    use Traits\AcfieldsCrudTrait;
    use Traits\AcfieldsTableTrait;
    use Traits\AcfieldsColumnTrait;
    use Traits\AcfieldsRelationTrait;
    use Traits\AcfieldsPaginationTrait;
    use Traits\AcfieldsSqlGenerationTrait; // SQL generation helpers

    // =========================================================================
    // CONFIGURATION PROPERTIES
    // =========================================================================

    /** @var string Unprefixed base table name */
    protected $table = 'posttype';

    /** @var string|null Connection name */
    protected $connection = null;

    /** @var string Primary key column name */
    protected $primaryKey = 'id';

    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = ['current_id', 'name', 'slug', 'menu', 'fields', 'status', 'languages', 'terms', 'is_locked'];

    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /** @var bool Enable automatic timestamps */
    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const CREATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';
    public const UPDATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';

    /** @var array<string, mixed> Default attribute values (focus when insert if not set in attributes) */
    protected $attributes = [
        'status' => 'active',
    ];

    // =========================================================================
    // SCHEMA DEFINITION
    // =========================================================================

    /**
     * Define the posttype table structure.
     * Using new Schema Builder format with timestamps.
     * 
     * @return array Schema definition
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => []],
            ['type' => 'string', 'name' => 'name', 'options' => ['length' => 100]],
            ['type' => 'string', 'name' => 'slug', 'options' => ['length' => 100, 'unique' => true]],
            ['type' => 'string', 'name' => 'menu', 'options' => ['length' => 100]],
            ['type' => 'string', 'name' => 'languages', 'options' => ['length' => 100, 'null' => true]],
            ['type' => 'string', 'name' => 'fields', 'options' => ['length' => 100, 'null' => true]],
            ['type' => 'string', 'name' => 'terms', 'options' => ['length' => 100, 'null' => true]],
            ['type' => 'integer', 'name' => 'current_id', 'options' => ['null' => true, 'default' => 0]],
            ['type' => 'tinyint', 'name' => 'status', 'options' => ['default' => 1]], // 1=active, 0=inactive
            ['type' => 'tinyint', 'name' => 'is_locked', 'options' => ['default' => 0]], // 1=locked, 0=unlocked
        ];
    }
}
