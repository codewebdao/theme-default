<?php

namespace System\Database;

use System\Database\DB;
use System\Database\Query\Builder;

/**
 * BaseModel - Modern ORM Model Class
 * 
 * Features:
 * - Laravel-like API (find, create, update, delete)
 * - Clean, modern API (no legacy methods)
 * - Mass assignment protection
 * - Automatic timestamps
 * - Attribute casting
 * - Schema definition support
 * - Query builder delegation
 * - Read-your-own-writes support
 * 
 * @package System\Database\Model
 */
abstract class BaseModel
{
    // =========================================================================
    // CONFIGURATION PROPERTIES
    // =========================================================================

    /** @var string|null Connection name (null => default) */
    protected $connection = null;

    /** @var string Unprefixed base table name */
    protected $table = '';

    /** @var string Primary key column name */
    protected $primaryKey = 'id';

    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = [];

    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = [];

    /** @var array<string, string> Attribute casting */
    protected $casts = [];

    /** @var bool Enable automatic timestamps */
    public $timestamps = true;

    /** @var string|null Timestamp column name for created_at */
    public const CREATED_AT = 'created_at';

    /** @var string|null Timestamp column name for updated_at */
    public const UPDATED_AT = 'updated_at';

    /** @var string|null Default value for created_at column */
    public const CREATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';

    /** @var string|null Default value for updated_at column */
    public const UPDATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';

    /** @var array<string, mixed> Default attribute values */
    protected $attributes = [];

    /** @var array<string> Hidden attributes when toArray/toJson */
    protected $hidden = [];

    /** @var array<string> Visible attributes (if set, only these will be shown) */
    protected $visible = [];

    /** @var array<callable> Global scopes applied to all queries */
    protected static $globalScopes = [];

    /** @var array<array{event: string, listeners: callable[]}> Event listeners */
    protected static $eventListeners = [];

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================

    /**
     * Constructor
     * 
     * @param string $tableUnprefix Optional unprefixed table name to override $this->table
     */
    public function __construct($tableUnprefix = '')
    {
        if (!empty($tableUnprefix)) {
            $this->table = $tableUnprefix;
        }
    }

    /**
     * Create a new model instance with dynamic table name
     * (Laravel-style static factory for dynamic tables)
     * 
     * @param string $table Unprefixed table name
     * @param string $primaryKey Primary key column name
     * @param string|null $connection Connection name
     * @param bool $timestamps Enable timestamps
     * @return static
     */
    public static function for(
        string $table,
        string $primaryKey = 'id',
        ?string $connection = null,
        bool $timestamps = false
    ) {
        $instance = new static();
        $instance->table = $table;
        $instance->primaryKey = $primaryKey;
        if ($connection !== null) {
            $instance->connection = $connection;
        }
        $instance->timestamps = $timestamps;
        return $instance;
    }

    /**
     * Set the table name dynamically
     * 
     * @param string $table Unprefixed table name
     * @return $this
     */
    public function setTable(string $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the primary key name dynamically
     * 
     * @param string $key Primary key name
     * @return $this
     */
    public function setKeyName(string $key)
    {
        $this->primaryKey = $key;
        return $this;
    }

    /**
     * Set the connection name dynamically
     * 
     * @param string|null $connection Connection name
     * @return $this
     */
    public function setConnection(?string $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    // =========================================================================
    // SCHEMA DEFINITION
    // =========================================================================

    /**
     * Define the table schema structure.
     * Override this method in child models to define table schema.
     * 
     * @return array Schema definition
     */
    protected function _schema()
    {
        return [];
    }

    // =========================================================================
    // QUERY BUILDING
    // =========================================================================

    /**
     * Get a new query builder instance for this model's table.
     * 
     * @return Builder
     */
    public static function query()
    {
        $instance = new static();
        return $instance->newQuery();
    }

    /**
     * Get a new query builder instance.
     * 
     * @return Builder
     */
    public function newQuery()
    {
        // if ($this->table == 'posts'){
        //     $this->table = 'fast_blogs_en';
        // }
        $builder = DB::table($this->table, $this->connection);

        // Set model for relationships
        $builder->setModel(static::class);

        // Apply global scopes
        $this->applyGlobalScopes($builder);

        return $builder;
    }

    /**
     * Apply global scopes to the query builder.
     * 
     * @param Builder $builder
     */
    protected function applyGlobalScopes(Builder $builder)
    {
        $scopes = static::getGlobalScopes();

        foreach ($scopes as $scope) {
            if (is_callable($scope)) {
                $scope($builder);
            }
        }
    }

    /**
     * Get all global scopes for this model.
     * 
     * @return array<callable>
     */
    protected static function getGlobalScopes()
    {
        return static::$globalScopes ?? [];
    }

    /**
     * Add a global scope.
     * 
     * @param callable $scope
     * @return void
     */
    public static function addGlobalScope(callable $scope)
    {
        if (!isset(static::$globalScopes)) {
            static::$globalScopes = [];
        }
        static::$globalScopes[] = $scope;
    }

    /**
     * Remove a specific global scope.
     * 
     * @param callable $scope
     * @return void
     */
    public static function removeGlobalScope(callable $scope)
    {
        if (isset(static::$globalScopes)) {
            static::$globalScopes = array_filter(static::$globalScopes, function ($s) use ($scope) {
                return $s !== $scope;
            });
        }
    }

    /**
     * Remove all global scopes.
     * 
     * @return void
     */
    public static function removeAllGlobalScopes()
    {
        static::$globalScopes = [];
    }

    /**
     * Get a new query builder without global scopes.
     * 
     * @return Builder
     */
    public static function withoutGlobalScopes()
    {
        $instance = new static();
        return DB::table($instance->table, $instance->connection);
    }

    /**
     * Get the table name for this model.
     * Returns prefixed table name to match Builder behavior.
     * 
     * @return string
     */
    public function getTable()
    {
        // Use DB::tableName() to get prefixed table name
        return DB::tableName($this->table, $this->connection);
    }

    /**
     * Get the unprefixed table name for this model.
     * 
     * @return string
     */
    public function getTableUnprefix()
    {
        return $this->table;
    }

    /**
     * Get the primary key for this model.
     * 
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the connection name for this model.
     * 
     * @return string|null
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    // =========================================================================
    // STATIC RETRIEVAL METHODS
    // =========================================================================

    /**
     * Execute the query and get all results.
     * 
     * @param array $columns
     * @return array
     */
    public static function all($columns = ['*'])
    {
        return static::query()->get($columns);
    }

    /**
     * Find a model by its primary key.
     * 
     * @param mixed $id
     * @return array|null
     */
    public static function find($id)
    {
        $instance = new static();
        return static::query()->where($instance->primaryKey, $id)->first();
    }

    /**
     * Find a model by its primary key or throw exception.
     * 
     * @param mixed $id
     * @return array
     * @throws \RuntimeException
     */
    public static function findOrFail($id)
    {
        $result = static::find($id);
        if ($result === null) {
            throw new \RuntimeException("Model not found with ID: {$id}");
        }
        return $result;
    }

    /**
     * Get the first model matching the query.
     * 
     * @param array $columns
     * @return array|null
     */
    public static function first($columns = ['*'])
    {
        return static::query()->first($columns);
    }

    /**
     * Get the first model matching the query or throw exception.
     * 
     * @param array $columns
     * @return array
     * @throws \RuntimeException
     */
    public static function firstOrFail($columns = ['*'])
    {
        $result = static::query()->first($columns);
        if ($result === null) {
            throw new \RuntimeException("No results found");
        }
        return $result;
    }

    /**
     * Find or create a new model instance.
     * 
     * @param array $attributes
     * @return array
     */
    public static function firstOrNew(array $attributes)
    {
        $builder = static::query();
        foreach ($attributes as $key => $value) {
            $builder->where($key, $value);
        }

        $result = $builder->first();
        if ($result !== null) {
            return $result;
        }

        // Return new instance data (not saved)
        $instance = new static();
        return $instance->prepareAttributes($attributes, true);
    }

    /**
     * Find or create and save a new model instance.
     * 
     * @param array $attributes
     * @param array $values
     * @return array
     */
    public static function firstOrCreate(array $attributes, array $values = [])
    {
        $builder = static::query();
        foreach ($attributes as $key => $value) {
            $builder->where($key, $value);
        }

        $result = $builder->first();
        if ($result !== null) {
            return $result;
        }

        // Create new record
        $instance = new static();
        $data = array_merge($attributes, $values);
        $id = $instance->create($data);

        // Read back from database
        return static::find($id);
    }

    /**
     * Update an existing model or create a new one.
     * 
     * @param array $attributes
     * @param array $values
     * @return array
     */
    public static function updateOrCreate(array $attributes, array $values = [])
    {
        $builder = static::query();
        foreach ($attributes as $key => $value) {
            $builder->where($key, $value);
        }

        $result = $builder->first();
        if ($result !== null) {
            // Update existing
            $instance = new static();
            $instance->updateById($result[$instance->primaryKey], $values);
            return static::find($result[$instance->primaryKey]);
        }

        // Create new
        $instance = new static();
        $data = array_merge($attributes, $values);
        $id = $instance->create($data);

        return static::find($id);
    }

    /**
     * Paginate the query results.
     * 
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public static function paginate($perPage = 15, $page = 1)
    {
        return static::query()->paginate($perPage, $page);
    }

    /**
     * Count the number of models.
     * 
     * @return int
     */
    public static function count()
    {
        return static::query()->count();
    }

    // =========================================================================
    // STATIC WRITE METHODS
    // =========================================================================

    /**
     * Create a new model instance and save it to the database.
     * 
     * @param array $attributes
     * @return mixed Last insert ID
     */
    public static function create(array $attributes = [])
    {
        $instance = new static();
        $attributes = $instance->filterFillable($attributes);
        return $instance->newQuery()->insertGetId($instance->prepareAttributes($attributes, true));
    }

    /**
     * Insert multiple records into the database.
     * 
     * @param array $attributes
     * @return int Number of records inserted
     */
    public static function createMany(array $attributes)
    {
        $instance = new static();
        $rows = [];

        foreach ($attributes as $attr) {
            $filtered = $instance->filterFillable($attr);
            $rows[] = $instance->prepareAttributes($filtered, true);
        }

        return $instance->newQuery()->insertMany($rows);
    }

    /**
     * Update models matching the query.
     * 
     * @param array $attributes
     * @param mixed $where
     * @return int Number of affected rows
     */
    public static function update(array $attributes, $where = null)
    {
        $instance = new static();
        $attributes = $instance->filterFillable($attributes);

        $builder = $instance->newQuery();

        if ($where !== null) {
            if (is_array($where)) {
                foreach ($where as $column => $value) {
                    $builder->where($column, $value);
                }
            }
        }

        return $builder->update($instance->prepareAttributes($attributes, false));
    }

    /**
     * Delete models matching the query.
     * 
     * @param mixed $where
     * @return int Number of affected rows
     */
    public static function destroy($where = null)
    {
        $instance = new static();
        $builder = $instance->newQuery();

        if ($where !== null) {
            if (is_array($where)) {
                foreach ($where as $column => $value) {
                    $builder->where($column, $value);
                }
            } elseif (is_numeric($where)) {
                $builder->where($instance->primaryKey, $where);
            } elseif (is_array($where) && array_keys($where) === range(0, count($where) - 1)) {
                $builder->whereIn($instance->primaryKey, $where);
            }
        }

        return $builder->delete();
    }

    // =========================================================================
    // INSTANCE METHODS
    // =========================================================================

    /**
     * Insert a new record.
     * 
     * @param array $data
     * @return mixed Last insert ID
     */
    public function insert(array $data)
    {
        // Fire creating event
        $this->fireEvent('creating', $data);

        $data = $this->filterFillable($data);
        $data = $this->prepareAttributes($data, true);

        $id = $this->newQuery()->insertGetId($data);

        // Fire created event
        $this->fireEvent('created', array_merge($data, [$this->primaryKey => $id]));

        return $id;
    }

    /**
     * Update a model by ID.
     * This method is kept for explicit ID-based updates with events.
     * 
     * For query builder approach (recommended):
     *   User::where('id', $id)->update($data)
     * 
     * @param mixed $id
     * @param array $data
     * @return int Number of affected rows
     */
    public function updateById($id, array $data)
    {
        // Fire updating event
        $this->fireEvent('updating', ['id' => $id, 'data' => $data]);

        $data = $this->filterFillable($data);
        $affected = $this->newQuery()->where($this->primaryKey, $id)->update($this->prepareAttributes($data, false));

        // Fire updated event
        if ($affected > 0) {
            $this->fireEvent('updated', ['id' => $id, 'data' => $data]);
        }

        return $affected;
    }

    /**
     * Delete a model by ID.
     * This method is kept for explicit ID-based deletes with events.
     * 
     * For query builder approach (recommended):
     *   User::where('id', $id)->delete()
     * 
     * @param mixed $id
     * @return int Number of affected rows
     */
    public function deleteById($id)
    {
        // Fire deleting event
        $this->fireEvent('deleting', $id);

        $affected = $this->newQuery()->where($this->primaryKey, $id)->delete();

        // Fire deleted event
        if ($affected > 0) {
            $this->fireEvent('deleted', $id);
        }

        return $affected;
    }

    // =========================================================================
    // QUERY BUILDER DELEGATION
    // =========================================================================

    public static function where($column, $value = null, $operator = '=')
    {
        return static::query()->where($column, $value, $operator);
    }

    public static function whereIn($column, array $values)
    {
        return static::query()->whereIn($column, $values);
    }

    public static function orderBy($column, $direction = 'asc')
    {
        return static::query()->orderBy($column, $direction);
    }

    public static function limit($limit)
    {
        return static::query()->limit($limit);
    }

    public static function offset($offset)
    {
        return static::query()->offset($offset);
    }

    public static function select(...$columns)
    {
        return static::query()->select(...$columns);
    }

    public static function join($table, $first, $operator = null, $second = null)
    {
        return static::query()->join($table, $first, $operator, $second);
    }

    public static function leftJoin($table, $first, $operator = null, $second = null)
    {
        return static::query()->leftJoin($table, $first, $operator, $second);
    }

    // =========================================================================
    // AGGREGATE METHODS
    // =========================================================================

    public static function sum($column)
    {
        return static::query()->sum($column);
    }

    public static function avg($column)
    {
        return static::query()->avg($column);
    }

    public static function min($column)
    {
        return static::query()->min($column);
    }

    public static function max($column)
    {
        return static::query()->max($column);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    public static function value($column)
    {
        return static::query()->value($column);
    }

    public static function pluck($column, $key = null)
    {
        return static::query()->pluck($column, $key);
    }

    public static function exists()
    {
        return static::query()->exists();
    }

    public static function chunk($size, callable $callback)
    {
        return static::query()->chunk($size, $callback);
    }

    // =========================================================================
    // EAGER LOADING METHODS
    // =========================================================================

    /**
     * Eager load relationships.
     * 
     * @param string|array $relationships Relationship name(s) to load
     * @param callable|null $constraints Optional constraints
     * @return Builder
     */
    public static function with($relationships, callable $constraints = null)
    {
        return static::query()->with($relationships, $constraints);
    }

    /**
     * Eager load nested relationships.
     * 
     * @param string $relationship Parent relationship
     * @param callable $callback Nested constraints
     * @return Builder
     */
    public static function withNested($relationship, callable $callback)
    {
        return static::query()->withNested($relationship, $callback);
    }

    /**
     * Get query without eager loading.
     * 
     * @return Builder
     */
    public static function withoutEagerLoading()
    {
        return static::query()->withoutEagerLoading();
    }

    // =========================================================================
    // MASS ASSIGNMENT PROTECTION
    // =========================================================================

    /**
     * Filter attributes based on fillable/guarded.
     * 
     * @param array $attributes
     * @return array
     */
    protected function filterFillable(array $attributes)
    {
        if (!empty($this->fillable)) {
            // Whitelist approach
            return array_intersect_key($attributes, array_flip($this->fillable));
        }

        if (!empty($this->guarded)) {
            // Blacklist approach
            foreach ($this->guarded as $key) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * Prepare attributes before insert/update.
     * 
     * @param array $attributes
     * @param bool $isInsert
     * @return array
     */
    protected function prepareAttributes(array $attributes, $isInsert = true)
    {
        if ($this->timestamps) {
            if ($isInsert && defined(static::class . '::CREATED_AT')) {
                $attributes[static::CREATED_AT] = date('Y-m-d H:i:s');
            }
            if (defined(static::class . '::UPDATED_AT')) {
                $attributes[static::UPDATED_AT] = date('Y-m-d H:i:s');
            }
        }
        if ($isInsert) {
            foreach ($this->attributes as $key => $value) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = $value;
                }
            }
        }
        return $attributes;
    }

    // =========================================================================
    // ATTRIBUTE CASTING
    // =========================================================================

    /**
     * Cast a single row.
     * 
     * @param array $row
     * @return array
     */
    protected function castRow(array $row)
    {
        foreach ($this->casts as $column => $type) {
            if (!isset($row[$column])) {
                continue;
            }
            $row[$column] = $this->castValue($row[$column], $type);
        }
        return $row;
    }

    /**
     * Cast multiple rows.
     * 
     * @param array $rows
     * @return array
     */
    protected function castRows(array $rows)
    {
        foreach ($rows as &$row) {
            $row = $this->castRow($row);
        }
        return $rows;
    }

    /**
     * Cast a single value.
     * 
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'float':
            case 'double':
                return (float)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'string':
                return (string)$value;
            case 'json':
            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                $decoded = json_decode($value, true);
                return $decoded ?? $value;
            case 'datetime':
                return date('Y-m-d H:i:s', is_numeric($value) ? $value : strtotime($value));
            case 'date':
                return date('Y-m-d', is_numeric($value) ? $value : strtotime($value));
            default:
                return $value;
        }
    }

    // =========================================================================
    // READ-YOUR-OWN-WRITES
    // =========================================================================

    /**
     * Force a callback to use the write connection.
     * 
     * @param callable $callback
     * @return mixed
     */
    public function forceWrite(callable $callback)
    {
        $driver = DB::connection($this->connection)->driver();
        if (method_exists($driver, 'withForceWrite')) {
            return $driver->withForceWrite($callback);
        }
        return $callback();
    }

    /**
     * Get a query builder that forces reads from write connection.
     * 
     * @return Builder
     */
    public function useWrite()
    {
        return $this->newQuery()->forceWrite();
    }

    // =========================================================================
    // TRANSACTIONS
    // =========================================================================

    public static function beginTransaction()
    {
        DB::beginTransaction();
    }

    public static function commit()
    {
        DB::commit();
    }

    public static function rollBack()
    {
        DB::rollBack();
    }

    public static function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    // =========================================================================
    // UTILITIES
    // =========================================================================

    public static function getInstance()
    {
        return new static();
    }

    public static function raw($sql, array $params = [])
    {
        $instance = new static();
        $driver = DB::connection($instance->connection)->driver();
        return $driver->query($sql, $params);
    }

    // =========================================================================
    // MODEL EVENTS
    // =========================================================================

    /**
     * Register an event listener.
     * 
     * @param string $event Event name (creating, created, updating, updated, saving, saved, deleting, deleted)
     * @param callable $listener
     * @return void
     */
    public static function on($event, callable $listener)
    {
        if (!isset(static::$eventListeners[$event])) {
            static::$eventListeners[$event] = [];
        }
        static::$eventListeners[$event][] = $listener;
    }

    /**
     * Fire an event and execute all listeners.
     * 
     * @param string $event
     * @param mixed $payload
     * @return void
     */
    protected function fireEvent($event, $payload = null)
    {
        if (!isset(static::$eventListeners[$event])) {
            return;
        }

        foreach (static::$eventListeners[$event] as $listener) {
            $listener($payload);
        }
    }

    /**
     * Execute callback without firing model events.
     * 
     * @param callable $callback
     * @return mixed
     */
    public static function withoutEvents(callable $callback)
    {
        $original = static::$eventListeners;
        static::$eventListeners = [];

        try {
            $result = $callback();
            static::$eventListeners = $original;
            return $result;
        } catch (\Exception $e) {
            static::$eventListeners = $original;
            throw $e;
        }
    }

    // =========================================================================
    // LOCAL SCOPES (Dynamic Scope Methods)
    // =========================================================================

    /**
     * Handle dynamic scope calls.
     * E.g: User::active() will call scopeActive() method
     * 
     * @param string $method
     * @param array $parameters
     * @return Builder
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static();

        // Check if it's a scope method (scopeXxx)
        if (strpos($method, 'scope') === 0) {
            $scope = lcfirst(substr($method, 5)); // Remove 'scope' prefix
            $scopeMethod = 'scope' . ucfirst($scope);

            if (method_exists($instance, $scopeMethod)) {
                return $instance->$scopeMethod($instance->newQuery(), ...$parameters);
            }
        }

        // Fallback to query() for other static calls
        return static::query()->$method(...$parameters);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Define a one-to-one relationship.
     * 
     * @param string $related Related model class
     * @param string $foreignKey Foreign key on the related model
     * @param string $localKey Local key on this model
     * @return \System\Database\ModelRelations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        return new \System\Database\ModelRelations\HasOne($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     * 
     * @param string $related Related model class
     * @param string $foreignKey Foreign key on the related model
     * @param string $localKey Local key on this model
     * @return \System\Database\ModelRelations\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        return new \System\Database\ModelRelations\HasMany($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     * 
     * @param string $related Related model class
     * @param string $foreignKey Foreign key on this model
     * @param string $ownerKey Owner key on the related model
     * @return \System\Database\ModelRelations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $instance = new $related;
        $ownerKey = $ownerKey ?: $instance->getKeyName();
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        return new \System\Database\ModelRelations\BelongsTo($instance->newQuery(), $this, $foreignKey, $ownerKey);
    }

    /**
     * Define a many-to-many relationship.
     * 
     * @param string $related Related model class
     * @param string $table Pivot table name
     * @param string $foreignPivotKey Foreign key for this model in pivot table
     * @param string $relatedPivotKey Foreign key for related model in pivot table
     * @param string $parentKey Parent key on this model
     * @param string $relatedKey Related key on related model
     * @return \System\Database\ModelRelations\BelongsToMany
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
    {
        $instance = new $related;
        $parentKey = $parentKey ?: $this->getKeyName();
        $relatedKey = $relatedKey ?: $instance->getKeyName();

        if ($table === null) {
            $table = $this->joiningTable($related);
        }

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        return new \System\Database\ModelRelations\BelongsToMany($instance->newQuery(), $this, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    }

    /**
     * Define a polymorphic one-to-one relationship.
     * 
     * @param string $related Related model class
     * @param string $name Polymorphic relation name
     * @param string $type Polymorphic type column
     * @param string $id Polymorphic id column
     * @param string $localKey Local key on this model
     * @return \System\Database\ModelRelations\MorphOne
     */
    public function morphOne($related, $name, $type = null, $id = null, $localKey = null)
    {
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();
        $type = $type ?: $name . '_type';
        $id = $id ?: $name . '_id';

        return new \System\Database\ModelRelations\MorphOne($instance->newQuery(), $this, $type, $id, $localKey);
    }

    /**
     * Define a polymorphic one-to-many relationship.
     * 
     * @param string $related Related model class
     * @param string $name Polymorphic relation name
     * @param string $type Polymorphic type column
     * @param string $id Polymorphic id column
     * @param string $localKey Local key on this model
     * @return \System\Database\ModelRelations\MorphMany
     */
    public function morphMany($related, $name, $type = null, $id = null, $localKey = null)
    {
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();
        $type = $type ?: $name . '_type';
        $id = $id ?: $name . '_id';

        return new \System\Database\ModelRelations\MorphMany($instance->newQuery(), $this, $type, $id, $localKey);
    }

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     * 
     * @param string $name Polymorphic relation name
     * @param string $type Polymorphic type column
     * @param string $id Polymorphic id column
     * @param string $ownerKey Owner key on the related model
     * @return \System\Database\ModelRelations\MorphTo
     */
    public function morphTo($name = 'morphable', $type = null, $id = null, $ownerKey = null)
    {
        $type = $type ?: $name . '_type';
        $id = $id ?: $name . '_id';

        return new \System\Database\ModelRelations\MorphTo($this->newQuery(), $this, $type, $id, $ownerKey, $name);
    }

    /**
     * Define a polymorphic many-to-many relationship.
     * 
     * @param string $related Related model class
     * @param string $name Polymorphic relation name
     * @param string $table Pivot table name
     * @param string $foreignPivotKey Foreign key for this model in pivot table
     * @param string $relatedPivotKey Foreign key for related model in pivot table
     * @param string $parentKey Parent key on this model
     * @param string $relatedKey Related key on related model
     * @param string $type Polymorphic type column
     * @param string $id Polymorphic id column
     * @return \System\Database\ModelRelations\MorphToMany
     */
    public function morphToMany($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $type = null, $id = null)
    {
        $instance = new $related;
        $parentKey = $parentKey ?: $this->getKeyName();
        $relatedKey = $relatedKey ?: $instance->getKeyName();

        if ($table === null) {
            $table = $this->joiningTable($related, $name);
        }

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();
        $type = $type ?: $name . '_type';
        $id = $id ?: $name . '_id';

        return new \System\Database\ModelRelations\MorphToMany($instance->newQuery(), $this, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $type, $id);
    }

    /**
     * Get the default foreign key name for the model.
     * 
     * @return string
     */
    public function getForeignKey()
    {
        return strtolower(class_basename($this)) . '_id';
    }

    /**
     * Get the joining table name for a many-to-many relationship.
     * 
     * @param string $related
     * @param string $name
     * @return string
     */
    public function joiningTable($related, $name = null)
    {
        $models = [
            strtolower(class_basename($this)),
            strtolower(class_basename($related))
        ];

        sort($models);

        return implode('_', $models);
    }

    /**
     * Get an attribute from the model.
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute on the model.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Get all attributes.
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set multiple attributes.
     * 
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Get the class name for polymorphic relations.
     * 
     * @return string
     */
    public function getMorphClass()
    {
        return static::class;
    }

    // =========================================================================
    // SCHEMA METHODS
    // =========================================================================

    /**
     * Get the schema definition for this model.
     * Automatically handles timestamps based on model configuration.
     * 
     * @return array
     */
    public function getSchema()
    {
        static $cache = [];
        $class = static::class;

        if (!isset($cache[$class])) {
            $schema = $this->_schema();

            // Auto-add timestamp columns if enabled
            if ($this->timestamps) {
                // Get existing column names
                $existingColumns = [];
                foreach ($schema as $item) {
                    if (isset($item['name'])) {
                        $existingColumns[$item['name']] = true;
                    }
                }

                // Get timestamp column names and defaults from constants
                $createdAt = defined('static::CREATED_AT') ? static::CREATED_AT : 'created_at';
                $updatedAt = defined('static::UPDATED_AT') ? static::UPDATED_AT : 'updated_at';
                // Add created_at if not already defined and not empty
                if (!empty($createdAt) && !isset($existingColumns[$createdAt])) {
                    $createdAtDefault = defined('static::CREATED_AT_DEFAULT') ? static::CREATED_AT_DEFAULT : 'CURRENT_TIMESTAMP';
                    $options = ['nullable' => false];
                    if ($createdAtDefault) {
                        $options['default'] = $createdAtDefault;
                    }

                    $schema[] = [
                        'type' => 'datetime',
                        'name' => $createdAt,
                        'options' => $options
                    ];
                }

                // Add updated_at if not already defined and not empty
                if (!empty($updatedAt) && !isset($existingColumns[$updatedAt])) {
                    $updatedAtDefault = defined('static::UPDATED_AT_DEFAULT') ? static::UPDATED_AT_DEFAULT : 'CURRENT_TIMESTAMP';
                    $options = ['nullable' => false];
                    if ($updatedAtDefault) {
                        $options['default'] = $updatedAtDefault;
                        //$options['onUpdate'] = $updatedAtDefault;
                    }

                    $schema[] = [
                        'type' => 'datetime',
                        'name' => $updatedAt,
                        'options' => $options
                    ];
                }
            }

            $cache[$class] = $schema;
        }

        return $cache[$class];
    }

    /**
     * Check if a table exists.
     * 
     * @return bool
     */
    public function hasTable()
    {
        $schema = DB::schema($this->connection);

        if (method_exists($schema, 'hasTable')) {
            return $schema->hasTable($this->getTableUnprefix());
        }

        // Fallback: try to select from table
        try {
            $this->newQuery()->limit(1)->get();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create the table from schema definition.
     * Uses getSchema() which automatically processes timestamps.
     * 
     * @return bool Success
     */
    public function createTable()
    {
        $schema = $this->getSchema();
        if (empty($schema)) {
            throw new \RuntimeException("No schema defined for " . static::class);
        }

        $schemaBuilder = DB::schema($this->connection);

        if (!method_exists($schemaBuilder, 'create')) {
            throw new \RuntimeException("Schema builder does not support table creation");
        }

        return $schemaBuilder->create($this->getTableUnprefix(), function ($table) use ($schema) {
            foreach ($schema as $definition) {
                $this->applySchemaDefinition($table, $definition);
            }
        });
    }

    /**
     * Alter the table structure.
     * 
     * @param array $changes Schema changes
     * @return bool Success
     */
    public function alterTable(array $changes)
    {
        $schemaBuilder = DB::schema($this->connection);

        if (!method_exists($schemaBuilder, 'table')) {
            throw new \RuntimeException("Schema builder does not support table alteration");
        }

        return $schemaBuilder->table($this->getTableUnprefix(), function ($table) use ($changes) {
            foreach ($changes as $change) {
                $this->applySchemaChange($table, $change);
            }
        });
    }

    /**
     * Apply a single schema definition to table builder.
     * 
     * @param mixed $table
     * @param array $definition
     */
    protected function applySchemaDefinition($table, array $definition)
    {
        $type = $definition['type'] ?? 'string';
        $name = $definition['name'] ?? '';
        $options = $definition['options'] ?? [];

        if (empty($name)) {
            return;
        }

        // Support common schema operations
        switch ($type) {
            case 'id':
                $table->id(...$options);
                break;
            case 'string':
                $length = $options['length'] ?? 255;
                $table->string($name, $length, ...array_diff_key($options, ['length' => null]));
                break;
            case 'integer':
                $table->integer($name, ...$options);
                break;
            case 'bigInteger':
                $table->bigInteger($name, ...$options);
                break;
            case 'text':
                $table->text($name, ...$options);
                break;
            case 'boolean':
                $table->boolean($name, ...$options);
                break;
            case 'decimal':
                $precision = $options['precision'] ?? 8;
                $scale = $options['scale'] ?? 2;
                $table->decimal($name, $precision, $scale, ...array_diff_key($options, ['precision' => null, 'scale' => null]));
                break;
            case 'date':
                $table->date($name, ...$options);
                break;
            case 'timestamp':
                $table->timestamp($name, ...$options);
                break;
            case 'json':
                $table->json($name, ...$options);
                break;
            case 'index':
                $columns = is_array($options) ? $options : [$name];
                $table->index($columns, $name);
                break;
            case 'unique':
                $columns = is_array($options) ? $options : [$name];
                $table->unique($columns, $name);
                break;
            case 'foreign':
                $foreignTable = $options['table'] ?? '';
                $foreignKey = $options['key'] ?? 'id';
                if (!empty($foreignTable)) {
                    $table->foreign($name)->references($foreignKey)->on($foreignTable);
                }
                break;
        }
    }

    /**
     * Apply a schema change to table builder.
     * 
     * @param mixed $table
     * @param array $change
     */
    protected function applySchemaChange($table, array $change)
    {
        $action = $change['action'] ?? '';
        $type = $change['type'] ?? '';
        $name = $change['name'] ?? '';
        $options = $change['options'] ?? [];

        switch ($action) {
            case 'add':
                $this->applySchemaDefinition($table, array_merge(['name' => $name, 'type' => $type], ['options' => $options]));
                break;
            case 'drop':
                $table->dropColumn($name);
                break;
            case 'rename':
                $newName = $options['to'] ?? '';
                if ($newName) {
                    $table->renameColumn($name, $newName);
                }
                break;
            case 'modify':
                $this->applySchemaDefinition($table, array_merge(['name' => $name, 'type' => $type], ['options' => $options]));
                break;
        }
    }

    /**
     * Drop the table (DANGEROUS).
     * 
     * @param bool $confirm Must be true to proceed
     * @return bool Success
     */
    public function dropTable($confirm = false)
    {
        if (!$confirm) {
            throw new \RuntimeException("dropTable requires explicit confirmation");
        }

        $schemaBuilder = DB::schema($this->connection);

        if (method_exists($schemaBuilder, 'dropTable')) {
            return $schemaBuilder->dropTable($this->getTableUnprefix());
        }

        if (method_exists($schemaBuilder, 'dropIfExists')) {
            return $schemaBuilder->dropTableIfExists($this->getTableUnprefix());
        }

        throw new \RuntimeException("Schema builder does not support table dropping");
    }
}
