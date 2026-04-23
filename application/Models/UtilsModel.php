<?php

namespace App\Models;

use System\Database\BaseModel;
use System\Database\DB;

/**
 * UtilsModel - Utility Model
 * 
 * Handles utility functions, menu generation, and posttype queries
 * Uses new BaseModel with Query Builder support
 * 
 * @package App\Models
 */
class UtilsModel extends BaseModel
{
    // =========================================================================
    // CONFIGURATION PROPERTIES
    // =========================================================================

    /** @var string Unprefixed base table name */
    protected $table = 'utils';
    
    /** @var string|null Connection name */
    protected $connection = null;
    
    /** @var string Primary key column name */
    protected $primaryKey = 'id';
    
    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = ['name'];
    
    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = ['id', 'created_at'];
    
    /** @var bool Enable timestamps */
    public $timestamps = true;

    // =========================================================================
    // SCHEMA DEFINITION
    // =========================================================================

    /**
     * Define the table schema structure
     * 
     * @return array Schema definition
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['primary' => true, 'unsigned' => true]],
            ['type' => 'varchar', 'name' => 'name', 'options' => ['length' => 150, 'null' => false, 'default' => '']],
        ];
    }

    // =========================================================================
    // TABLE NAME METHODS
    // =========================================================================

    /**
     * Get prefixed table name
     * 
     * @param string $name Unprefixed table name
     * @return string|null Prefixed table name
     */
    public function table($name = '')
    {
        if (empty($name)) {
            return null;
        }
        return APP_PREFIX . $name;
    }

    // =========================================================================
    // DATA RETRIEVAL METHODS
    // =========================================================================

    /**
     * Get all records from a specific table
     * 
     * @param string $tableName Table name (unprefixed)
     * @return array List of records
     */
    public function getDatasByTable($tableName)
    {
        try {
            return DB::table($tableName)->get();
        } catch (\Exception $e) {
            error_log("Error in UtilsModel->getDatasByTable: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get utils records (legacy method name)
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters
     * @param string $orderBy ORDER BY clause
     * @param int|null $limit Limit
     * @param int|null $offset Offset
     * @return array List of records
     */
    public function getUntis($where = '', $params = [], $orderBy = 'id desc', $limit = null, $offset = null)
    {
        try {
            $query = static::query();

            if (!empty($where)) {
                $query->whereRaw($where, $params);
            }

            if (!empty($orderBy)) {
                // Parse orderBy (e.g., "id desc" -> orderBy('id', 'desc'))
                $parts = explode(' ', trim($orderBy));
                $column = $parts[0];
                $direction = isset($parts[1]) ? strtolower($parts[1]) : 'asc';
                $query->orderBy($column, $direction);
            }

            if ($limit !== null) {
                $query->limit((int)$limit);
            }

            if ($offset !== null) {
                $query->offset((int)$offset);
            }

            return $query->get();

        } catch (\Exception $e) {
            error_log("Error in UtilsModel->getUntis: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add a new utils record (legacy method name)
     * 
     * @param array $data Record data
     * @return mixed Last insert ID
     */
    public function addUnti($data)
    {
        try {
            return static::create($data);
        } catch (\Exception $e) {
            error_log("Error in UtilsModel->addUnti: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update a utils record (legacy method name)
     * 
     * @param int $id Record ID
     * @param array $data Data to update
     * @return int Affected rows
     */
    public function setUnti($id, $data)
    {
        try {
            return static::where('id', $id)->update($data);
        } catch (\Exception $e) {
            error_log("Error in UtilsModel->setUnti: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete a utils record (legacy method name)
     * 
     * @param int $id Record ID
     * @return int Affected rows
     */
    public function delUnti($id)
    {
        try {
            return static::where('id', $id)->delete();
        } catch (\Exception $e) {
            error_log("Error in UtilsModel->delUnti: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // POSTTYPE METHODS
    // =========================================================================


    // =========================================================================
    // UI HELPER METHODS
    // =========================================================================

    /**
     * Get menu configuration
     * 
     * @return array Menu structure
     */
    public function getMenus()
    {
        $menus = [
            [
                'type'  => 'block',
                'label' => 'search',
                'name'  => 'search',
                'icon'  => '/uploads/assets/menus/search.png'
            ],
            [
                'type'  => 'block',
                'label' => '',
                'name'  => 'dash',
                'icon'  => '/uploads/assets/menus/dash.png'
            ],
            [
                'type'  => 'block',
                'name'  => 'login',
                'label' => 'Get more benefits by becoming a member',
                'items' => [
                    [
                        'type'  => 'button',
                        'color' => 'red',
                        'name'  => 'login',
                        'label' => 'login'
                    ],
                    [
                        'type'  => 'button',
                        'color' => 'primary',
                        'name'  => 'register',
                        'label' => 'register'
                    ]
                ]
            ],
            [
                'type'  => 'block',
                'name'  => 'languages',
                'label' => 'languages',
                'icon'  => '/uploads/assets/menus/languages.png'
            ],
            'nav' => [
                'home' => [
                    'label' => 'home',
                    'icon'  => '/uploads/assets/menus/home.png'
                ],
                'movie' => [
                    'label' => 'movie',
                    'icon'  => '/uploads/assets/menus/movie.png'
                ],
                'comic' => [
                    'label' => 'comic',
                    'icon'  => '/uploads/assets/menus/comic.png'
                ],
                'game' => [
                    'label' => 'game',
                    'icon'  => '/uploads/assets/menus/game.png'
                ],
                'novel' => [
                    'label' => 'novel',
                    'icon'  => '/uploads/assets/menus/novel.png'
                ],
                'chat' => [
                    'label' => 'chat',
                    'icon'  => '/uploads/assets/menus/chat.png'
                ],
            ],
            'languages' => [
                'name'  => 'languages',
                'label' => 'languages',
                'icon'  => '/uploads/assets/menus/languages.png'
            ]
        ];

        return $menus;
    }

    /**
     * Get navbar configuration
     * 
     * @return array Navbar structure
     */
    public function getNavbar()
    {
        $navbar = [
            'home' => [
                'label'  => 'home',
                'active' => true,
                'icon'   => '/uploads/assets/menus/home.png'
            ],
            'movie' => [
                'label' => 'movie',
                'icon'  => '/uploads/assets/menus/movie.png'
            ],
            'game' => [
                'label' => 'game',
                'type'  => 'bigsize',
                'icon'  => '/uploads/assets/menus/game.png'
            ],
            'comic' => [
                'label' => 'comic',
                'icon'  => '/uploads/assets/menus/comic.png'
            ],
            'chat' => [
                'label' => 'chat',
                'icon'  => '/uploads/assets/menus/chat.png'
            ]
        ];
        
        return $navbar;
    }
}
