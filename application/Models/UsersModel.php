<?php

namespace App\Models;

use System\Database\BaseModel;

/**
 * UsersModel - User Management
 * 
 * This model manages user accounts, authentication, profiles,
 * and user relationships (likes, matches, etc.).
 * Uses new BaseModel with Laravel-like API.
 * 
 * @package App\Models
 */
class UsersModel extends BaseModel
{
    // =========================================================================
    // CONFIGURATION PROPERTIES
    // =========================================================================

    /** @var string Unprefixed base table name */
    protected $table = 'users';

    /** @var string|null Connection name */
    protected $connection = null;

    /** @var string Primary key column name */
    protected $primaryKey = 'id';

    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'fullname',
        'birthday',
        'gender',
        'avatar',
        'coin',
        'phone',
        'role',
        'permissions',
        'optional',
        'personal',
        'country',
        'address',
        'package_name',
        'package_exp',
        'online',
        'display',
        'status',
        'password_at',
        'activity_at'
    ];

    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = ['created_at', 'updated_at'];

    /** @var bool Enable automatic timestamps */
    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const CREATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';
    public const UPDATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';

    /** @var array<string, mixed> Default attribute values */
    protected $attributes = [
        'status' => 'active',
        'role' => 'member',
        'online' => 0,
        'display' => 1,
    ];

    // =========================================================================
    // SCHEMA DEFINITION
    // =========================================================================

    /**
     * Define the users table structure.
     * Using new Schema Builder format with timestamps.
     * 
     * Fields explanation:
     * - address: JSON {address1, address2, city, state, zipcode}
     * - personal: JSON {about_me, work_experiences, educations, skills, languages, hobbies, certifications, socials}
     * - optional: JSON {activation_code, activation_string, login_attempts, username_changed_at, etc.}
     * 
     * @return array Schema definition
     */
    protected function _schema()
    {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['primary' => true, 'unsigned' => true]],
            ['type' => 'varchar', 'name' => 'username', 'options' => ['length' => 40, 'unique' => true]],
            ['type' => 'varchar', 'name' => 'email', 'options' => ['length' => 150, 'unique' => true]],
            ['type' => 'varchar', 'name' => 'password', 'options' => ['length' => 255]],
            ['type' => 'varchar', 'name' => 'fullname', 'options' => ['length' => 150, 'null' => true, 'default' => '']],
            ['type' => 'date', 'name' => 'birthday', 'options' => ['null' => true]],
            ['type' => 'enum', 'name' => 'gender', 'options' => ['values' => ['male', 'female', 'other'], 'null' => true, 'default' => 'male']],
            ['type' => 'varchar', 'name' => 'avatar', 'options' => ['length' => 255, 'null' => true, 'default' => '']],
            ['type' => 'int', 'name' => 'coin', 'options' => ['null' => true, 'default' => 0]],
            ['type' => 'varchar', 'name' => 'phone', 'options' => ['length' => 30, 'null' => true, 'default' => '']],
            ['type' => 'varchar', 'name' => 'role', 'options' => ['length' => 30, 'index' => true, 'default' => 'member', 'comment' => 'User role (dynamic from config_roles())']],
            ['type' => 'longtext', 'name' => 'permissions', 'options' => ['null' => true, 'comment' => 'JSON array of permissions']],
            ['type' => 'longtext', 'name' => 'optional', 'options' => ['null' => true, 'comment' => 'JSON: activation_code, login_attempts, etc.']],
            ['type' => 'longtext', 'name' => 'personal', 'options' => ['null' => true, 'comment' => 'JSON: about_me, work_experiences, educations, skills, languages, hobbies, certifications, socials']],
            ['type' => 'varchar', 'name' => 'country', 'options' => ['length' => 2, 'null' => true, 'default' => '', 'comment' => 'Country code (2 chars)']],
            ['type' => 'longtext', 'name' => 'address', 'options' => ['null' => true, 'comment' => 'JSON: address1, address2, city, state, zipcode']],
            ['type' => 'varchar', 'name' => 'package_name', 'options' => ['length' => 50, 'null' => true, 'default' => 'membership']],
            ['type' => 'datetime', 'name' => 'package_exp', 'options' => ['null' => true]],
            ['type' => 'boolean', 'name' => 'online', 'options' => ['default' => 0]],
            ['type' => 'boolean', 'name' => 'display', 'options' => ['default' => 1]],
            ['type' => 'enum', 'name' => 'status', 'options' => ['values' => ['active', 'inactive', 'banned', 'deleted'], 'default' => 'active']],
            ['type' => 'datetime', 'name' => 'password_at', 'options' => ['null' => true]],
            ['type' => 'datetime', 'name' => 'activity_at', 'options' => ['null' => true, 'default' => 'CURRENT_TIMESTAMP']],
        ];
    }

    // =========================================================================
    // RETRIEVAL METHODS
    // =========================================================================

    /**
     * Get all users with optional filtering.
     * Uses modern list() from Query Builder.
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @param string $orderBy ORDER BY clause
     * @param int $page Current page (not used, kept for compatibility)
     * @param int|null $limit Limit results
     * @return array List of users
     */
    public function getUsers($where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        return static::query()->list($where, $params, $orderBy, $limit);
    }

    /**
     * Get users with pagination.
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @param string $orderBy ORDER BY clause
     * @param int $page Current page number
     * @param int|null $limit Items per page
     * @return array Pagination result
     */
    public function getUsersPage($where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        return static::query()->paginateWith('*', $where, $params, $orderBy, $page, $limit);
    }

    /**
     * Get users with pagination and specific fields.
     * 
     * @param string $fields Fields to select
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @param string $orderBy ORDER BY clause
     * @param int $page Current page number
     * @param int|null $limit Items per page
     * @return array Pagination result
     */
    public function getFieldUsersPage($fields = '*', $where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        return static::query()->paginateWith($fields, $where, $params, $orderBy, $page, $limit);
    }

    /**
     * Get user by ID.
     * 
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserById($id)
    {
        return static::find($id);
    }

    /**
     * Get user by ID with specific fields.
     * 
     * @param string|array $fields Fields to select
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserByIdField($fields, $id)
    {
        $query = static::query();

        if (is_string($fields)) {
            $fieldsArray = array_map('trim', explode(',', $fields));
            $query->select(...$fieldsArray);
        } elseif (is_array($fields)) {
            $query->select(...$fields);
        }

        return $query->where('id', $id)->first();
    }

    /**
     * Get user by username.
     * 
     * @param string $username Username
     * @return array|null User data or null
     */
    public function getUserByUsername($username)
    {
        return static::where('username', $username)->first();
    }

    /**
     * Get user by email.
     * 
     * @param string $email Email address
     * @return array|null User data or null
     */
    public function getUserByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    // =========================================================================
    // MANIPULATION METHODS
    // =========================================================================

    /**
     * Add new user.
     * 
     * @param array $data User data
     * @return mixed Last insert ID
     */
    public function addUser($data)
    {
        return static::create($data);
    }

    /**
     * Update user information.
     * 
     * @param int $id User ID
     * @param array $data Data to update
     * @return int Number of affected rows
     */
    public function updateUser($id, $data)
    {
        return static::where('id', $id)->update($data);
    }

    /**
     * Delete user.
     * 
     * @param int $id User ID
     * @return int Number of affected rows
     */
    public function deleteUser($id)
    {
        return static::where('id', $id)->delete();
    }

    // =========================================================================
    // SEARCH & QUERY METHODS
    // =========================================================================

    /**
     * Search users by multiple fields with LIKE.
     * 
     * @param array $conditions Field => value pairs for LIKE search
     * @return array List of matching users
     */
    public function searchUser($conditions = [])
    {
        if (empty($conditions)) {
            return static::all();
        }

        $query = static::query();
        $first = true;

        foreach ($conditions as $field => $value) {
            if ($first) {
                $query->where($field, '%' . $value . '%', 'LIKE');
                $first = false;
            } else {
                $query->orWhere($field, '%' . $value . '%', 'LIKE');
            }
        }

        return $query->get();
    }
    // =========================================================================
    // GEOLOCATION METHODS
    // =========================================================================

    /**
     * Get user location coordinates.
     * Uses MySQL spatial functions (ST_X, ST_Y).
     * 
     * @param int $userId User ID
     * @return array|null Location data or null
     */
    public function getLocation($userId)
    {
        // Validate input
        $userId = (int)$userId;

        // Use raw SQL for spatial functions
        $tableName = $this->getTable();
        $sql = "
            SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude
            FROM {$tableName}
            WHERE id = ? AND status = 'active'
            LIMIT 1
        ";

        $result = $this->raw($sql, [$userId]);

        if (!empty($result)) {
            return [
                'longitude' => (float)$result[0]['longitude'],
                'latitude'  => (float)$result[0]['latitude']
            ];
        }

        return null;
    }
    
    // =========================================================================
    // USER RELATIONSHIP METHODS
    // =========================================================================

    /**
     * Add relationship between user and target user.
     * 
     * @param int $user_id User performing action
     * @param int $target_user_id Target user
     * @param string $relation_type Type of relationship ('like', 'dislike', 'super_like')
     * @return bool Success status
     */
    public function addRelation($user_id, $target_user_id, $relation_type)
    {
        try {
            // Validate relation type
            if (!in_array($relation_type, ['like', 'dislike', 'super_like'])) {
                throw new \InvalidArgumentException('Invalid relation type.');
            }

            // Prepare data
            $data = [
                'user_id' => $user_id,
                'target_user_id' => $target_user_id,
                'relation_type' => $relation_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert using query builder
            $relationsTable = \System\Database\DB::tableName('user_relations', $this->connection);
            return \System\Database\DB::table('user_relations', $this->connection)->insert($data);
        } catch (\Exception $e) {
            error_log("Error in addRelation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get users who have a specific relationship with the user.
     * Uses window functions for efficient pagination with total count.
     * 
     * @param int $userId User ID
     * @param string $relationType Relation type ('like', 'dislike', 'super_like')
     * @param int $limit Items per page
     * @param int $page Current page
     * @return array Pagination result with user details
     */
    public function get_user_relations($userId, $relationType = 'like', $limit = 10, $page = 1)
    {
        // Validate inputs
        $userId = (int)$userId;
        $relationType = trim($relationType);
        $page = max((int)$page, 1);
        $limit = max((int)$limit, 1);
        $offset = ($page - 1) * $limit;

        // Get table names with prefix
        $relationsTable = \System\Database\DB::tableName('user_relations', $this->connection);
        $usersTable = $this->getTable();

        // Step 1: Get user IDs with total count using window function
        $sql = "
            SELECT 
                r.user_id,
                COUNT(*) OVER() AS total_count
            FROM {$relationsTable} r
            WHERE r.target_user_id = ?
              AND r.relation_type = ?
            ORDER BY r.created_at DESC
            LIMIT $limit OFFSET $offset
        ";

        $idsResult = $this->raw($sql, [$userId, $relationType]);

        // Early return if no results
        if (empty($idsResult)) {
            return [
                'data' => [],
                'is_next' => false,
                'page' => $page,
                'total' => 0
            ];
        }

        // Get total count and determine if there's a next page
        $total = (int)$idsResult[0]['total_count'];
        $is_next = ($page * $limit < $total);

        // Extract user IDs
        $userIds = array_column($idsResult, 'user_id');

        // Step 2: Get detailed user information
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $detailsSql = "
            SELECT 
                id, username, email, fullname,
                ST_X(location) AS longitude,
                ST_Y(location) AS latitude,
                avatar, phone, telegram, whatsapp, skype,
                birthday, about_me, personal, online
            FROM {$usersTable}
            WHERE id IN ($placeholders)
              AND status = 'active'
        ";

        $users = $this->raw($detailsSql, $userIds);

        return [
            'data' => $users,
            'is_next' => $is_next,
            'page' => $page,
            'total' => $total
        ];
    }

    /**
     * Get matched users (mutual likes/super_likes).
     * Finds users where both parties have liked each other.
     * 
     * @param int $userId User ID
     * @param int $limit Items per page
     * @param int $page Current page
     * @return array Pagination result with matched user details
     */
    public function get_user_matching($userId, $limit = 10, $page = 1)
    {
        // Validate inputs
        $userId = (int)$userId;
        $limit = max((int)$limit, 1);
        $page = max((int)$page, 1);
        $offset = ($page - 1) * $limit;

        // Get table names
        $relationsTable = \System\Database\DB::tableName('user_relations', $this->connection);
        $usersTable = $this->getTable();

        // Step 1: Get users that $userId has liked
        $sqlLiked = "
            SELECT target_user_id
            FROM {$relationsTable}
            WHERE user_id = ?
              AND relation_type IN ('like', 'super_like')
        ";

        $likedResult = $this->raw($sqlLiked, [$userId]);
        $likedUserIds = array_map('intval', array_column($likedResult, 'target_user_id'));

        // Step 2: Get users who have liked $userId back
        $sqlLikedMe = "
            SELECT user_id
            FROM {$relationsTable}
            WHERE target_user_id = ?
              AND relation_type IN ('like', 'super_like')
        ";

        $likedMeResult = $this->raw($sqlLikedMe, [$userId]);
        $likedMeUserIds = array_map('intval', array_column($likedMeResult, 'user_id'));

        // Step 3: Find mutual matches (intersection)
        $matchedUserIds = array_values(array_intersect($likedUserIds, $likedMeUserIds));
        $total = count($matchedUserIds);

        // Step 4: Apply pagination in PHP
        $pagedMatchedUserIds = array_slice($matchedUserIds, $offset, $limit);

        // Early return if no matches on this page
        if (empty($pagedMatchedUserIds)) {
            return [
                'data'   => [],
                'is_next' => false,
                'page'   => $page,
                'total'  => $total
            ];
        }

        // Determine if there's a next page
        $is_next = ($offset + $limit < $total);

        // Step 5: Get detailed user information
        $placeholders = implode(',', array_fill(0, count($pagedMatchedUserIds), '?'));
        $detailsSql = "
            SELECT 
                id, username, email, fullname,
                ST_X(location) AS longitude,
                ST_Y(location) AS latitude,
                avatar, phone, telegram, whatsapp, skype,
                birthday, about_me, personal, online
            FROM {$usersTable}
            WHERE id IN ($placeholders)
              AND status = 'active'
        ";

        $users = $this->raw($detailsSql, $pagedMatchedUserIds);

        return [
            'data'   => $users,
            'is_next' => $is_next,
            'page'   => $page,
            'total'  => $total
        ];
    }
}
