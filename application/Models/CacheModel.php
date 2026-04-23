<?php
namespace App\Models;

use System\Database\BaseModel;

/**
 * CacheModel - Database Cache Storage
 * 
 * This model defines the database cache table structure
 * Optimized for high-performance caching with proper indexes
 */
class CacheModel extends BaseModel
{
    protected $table = 'cache';
    protected $primaryKey = 'key';
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'expiration'];

    /**
     * Define cache table schema
     * 
     * Optimized for:
     * - Fast key lookup (PRIMARY KEY)
     * - Fast expiration cleanup (INDEX on expiration)
     * - MEDIUMTEXT for large cached values
     * 
     * @return array
     */
    protected function _schema()
    {
        return [
            // Primary key (string key)
            ['type' => 'varchar', 'name' => 'key', 'options' => ['length' => 255, 'primary' => true]],
            
            // Cached value (MEDIUMTEXT = 16MB max)
            ['type' => 'mediumtext', 'name' => 'value'],
            
            // Expiration timestamp (for cleanup queries)
            ['type' => 'integer', 'name' => 'expiration', 'options' => ['unsigned' => true, 'index' => true]],
            
            // Index for cleanup queries
            ['type' => 'index', 'name' => 'idx_expiration', 'columns' => ['expiration']]
        ];
    }
}
