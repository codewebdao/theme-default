<?php

namespace App\Models;

use System\Database\BaseModel;

/**
 * TestModel
 * 
 * Auto-generated model class
 */
class TestModel extends BaseModel
{
    /** @var string Unprefixed base table name */
    protected $table = 'test';

    /** @var string Primary key column name */
    protected $primaryKey = 'id';

    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = [
        'name',
        // Add more fillable fields here
    ];

    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @var bool Enable automatic timestamps */
    public $timestamps = true;

    /**
     * Define the table schema
     * 
     * Schema types: increments, bigincrements, integer, biginteger, tinyinteger,
     *               string, text, mediumtext, longtext, json, decimal, float, double,
     *               date, datetime, timestamp, time, year, enum, set, boolean, blob
     * 
     * Options: nullable, null, default, unsigned, unique, index, comment, length,
     *          precision, scale, values (for enum/set), after
     * 
     * Note: increments automatically creates AUTO_INCREMENT + UNSIGNED
     *
     * 
     * @return array Table schema definition
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['index' => ['type' => 'primary']]],
            ['type' => 'integer', 'name' => 'user_id', 'options' => ['null' => true, 'unsigned' => true]],
            ['type' => 'int', 'name' => 'quantity', 'options' => ['null' => false, 'default' => 0, 'unsigned' => true]],
        ];
    }
}
