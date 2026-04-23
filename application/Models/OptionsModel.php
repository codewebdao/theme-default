<?php

namespace App\Models;

use System\Database\BaseModel;
use System\Libraries\Logger;

class OptionsModel extends BaseModel
{
    // Table configuration
    protected $table = 'options';
    protected $primaryKey = 'id';

    // Mass assignment protection
    protected $fillable = ['label', 'type', 'name', 'description', 'status', 'optional'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Timestamps
    public $timestamps = true;
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    // Default values
    protected $attributes = [
        'status' => 'active',
        'label' => '',
        'type' => '',
        'name' => '',
        'description' => '',
    ];

    /**
     * Define the table schema.
     * 
     * @return array Table schema
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['primary' => true, 'unsigned' => true]],
            ['type' => 'varchar', 'name' => 'label', 'options' => ['length' => 100, 'default' => '']],
            ['type' => 'varchar', 'name' => 'type', 'options' => ['length' => 100, 'default' => '']],
            ['type' => 'varchar', 'name' => 'name', 'options' => ['length' => 100, 'default' => '']],
            ['type' => 'varchar', 'name' => 'description', 'options' => ['length' => 255, 'default' => '']],
            ['type' => 'enum', 'name' => 'status', 'options' => ['values' => ['active', 'inactive'], 'default' => 'active']],
            ['type' => 'text', 'name' => 'optional', 'options' => ['nullable' => true]]
        ];
    }

    /**
     * Get option by name.
     * 
     * @param string $name Option name
     * @return array|null
     */
    public function getByName($name = '')
    {
        try {
            if (empty($name) || !is_string($name)) {
                return false;
            }
            return static::where('name', $name)->first();
        } catch (\PDOException $e) {
            Logger::error("Database error in OptionsModel->getByName: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return null;
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->getByName: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return null;
        }
    }

    /**
     * Get option by ID.
     * 
     * @param int $id Option ID
     * @return array|null
     */
    public function getById($id = 0)
    {
        try {
            if (empty($id) || $id <= 0) {
                return false;
            }
            return static::find($id);
        } catch (\PDOException $e) {
            Logger::error("Database error in OptionsModel->getById: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return null;
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->getById: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return null;
        }
    }

    /**
     * Get all options with optional filtering.
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @param string $orderBy ORDER BY clause
     * @param int|null $limit Limit
     * @param int|null $offset Offset (not used in new API)
     * @return array
     */
    public function getOptions($where = '', $params = [], $orderBy = 'id desc', $limit = null, $offset = null)
    {
        try {
            return static::query()->list($where, $params, $orderBy, $limit);
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->getOptions: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return [];
        }
    }

    /**
     * Get paginated options with filtering.
     * 
     * @param string $fields Fields to select
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @param string $orderBy ORDER BY clause
     * @param int $page Current page number
     * @param int|null $limit Items per page
     * @return array Pagination result
     */
    public function getOptionsFieldsPagination($fields = '*', $where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        try {
            return static::query()->paginateWith($fields, $where, $params, $orderBy, $page, $limit);
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->getOptionsFieldsPagination: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return [
                'data' => [],
                'page' => 1,
                'is_next' => false
            ];
        }
    }

    /**
     * Add a new option.
     * 
     * @param array $data Option data
     * @return array Result with success status and ID
     */
    public function addOptions($data)
    {
        try {
            return static::create($data);
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->addOptions: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update an existing option by ID.
     * 
     * @param int $id Option ID
     * @param array $data Option data
     * @return array Result with success status
     */
    public function setOptions($id, $data)
    {
        try {
            $affected = static::where('id', $id)->update($data);
            return [
                'success' => $affected > 0,
                'affected' => $affected,
                'id' => $id
            ];
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->setOptions: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update an existing option by name.
     * 
     * @param string $name Option name
     * @param array $data Option data
     * @return array Result with success status
     */
    public function setOptionbyMame($name, $data)
    {
        try {
            $affected = static::where('name', $name)->update($data);
            return [
                'success' => $affected > 0,
                'affected' => $affected,
                'name' => $name
            ];
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->setOptionbyMame: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    /**
     * Delete an option by ID.
     * 
     * @param int $id Option ID
     * @return int Number of affected rows
     */
    public function delOptions($id)
    {
        try {
            return static::where('id', $id)->delete();
        } catch (\Exception $e) {
            Logger::error("Error in OptionsModel->delOptions: " . $e->getMessage(), $e->getFile(), $e->getLine());
            return 0;
        }
    }
}
