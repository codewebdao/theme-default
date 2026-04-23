<?php
namespace System\Database\Query;

use System\Database\DatabaseDriver;
use System\Database\Support\SqlExpression;
use System\Database\EagerLoader;
use System\Database\Query\Traits\BaseTrait;
use System\Database\Query\Traits\CoreWhereTrait;
use System\Database\Query\Traits\DateTimeTrait;
use System\Database\Query\Traits\NullsTrait;
use System\Database\Query\Traits\LikeTrait;
use System\Database\Query\Traits\ColumnCompareTrait;
use System\Database\Query\Traits\HavingTrait;
use System\Database\Query\Traits\OrderTrait;
use System\Database\Query\Traits\JoinTrait;
use System\Database\Query\Traits\GroupTrait;
use System\Database\Query\Traits\SubqueryTrait;
use System\Database\Query\Traits\CursorTrait;
use System\Database\Query\Traits\LockingTrait;
use System\Database\Query\Traits\ExplainTrait;
use System\Database\Query\Traits\JsonTrait;
use System\Database\Query\Traits\FullTextTrait;
use System\Database\Query\Traits\CteTrait;
use System\Database\Query\Traits\ReturningTrait;
use System\Database\Query\Traits\DebugSqlTrait;
use System\Database\Query\Traits\EagerLoadTrait;

/**
 * Query Builder (single entry; composed by Traits)
 *
 * - Prefix integration (applied to tables only)
 * - Select/Where/Join/Group/Having/Order/Limit/Offset
 * - Date/DateTime/Time helpers (sargable)
 * - Subqueries/Unions
 * - DML: insert/insertGetId/insertMany/update/delete/truncate/upsert
 * - Pagination: paginate(), cursorPaginate()
 * - Locking: forUpdate/forShare/skipLocked (grammar-guarded)
 * - Explain (grammar-guarded)
 * - JSON / Full-Text / CTE / Returning (grammar-guarded)
 */
final class Builder
{
    /** @var string|null Model class for relationships */
    protected $model = null;
    
    /** @var \System\Database\EagerLoader|null Eager loader instance */
    protected $eagerLoader = null;

    /**
     * Set the model for this query builder.
     * 
     * @param string $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get the model for this query builder.
     * 
     * @return string|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Create a new instance of the model.
     * 
     * @return mixed
     */
    public function newModelInstance()
    {
        if ($this->model) {
            return new $this->model;
        }
        return null;
    }

    // ===== Shared state & helpers =====
    use BaseTrait;

    // ===== Core feature groups =====
    use CoreWhereTrait,
        DebugSqlTrait,
        DateTimeTrait,
        NullsTrait,
        LikeTrait,
        ColumnCompareTrait,
        HavingTrait,
        OrderTrait,
        JoinTrait,
        GroupTrait,
        SubqueryTrait,
        CursorTrait,
        LockingTrait,
        ExplainTrait,
        JsonTrait,
        FullTextTrait,
        CteTrait,
        ReturningTrait,
        EagerLoadTrait;

    /**
     * @param DatabaseDriver $driver      Underlying driver (PDO wrapper)
     * @param Grammar        $grammar     SQL grammar/dialect adapter
     * @param string         $connection  Connection name
     * @param string         $prefix      Table prefix (e.g. APP_PREFIX)
     */
    public function __construct(DatabaseDriver $driver, Grammar $grammar, $connection, $prefix = '')
    {
        $this->driver          = $driver;
        $this->grammar         = $grammar;
        $this->connectionName  = (string)$connection;
        $this->prefix          = (string)$prefix;
        $this->reset();
    }

    /**
     * Set base table (unprefixed name); prefix is applied here.
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $this->from = $this->prefixed($table);
        return $this;
    }

    /**
     * Alias of from().
     * @param string $table
     * @return $this
     */
    public function table($table)
    {
        return $this->from($table);
    }

    /**
     * Set selected columns.
     * Supports multiple input formats:
     * - select('name', 'email') - variadic arguments
     * - select(['name', 'email']) - array of columns
     * - select('name as user_name', 'email as user_email') - with aliases
     * - select(DB::raw('count(*) as user_count')) - raw expressions
     * 
     * @param string|array|SqlExpression ...$cols
     * @return $this
     */
    public function select(...$cols)
    {
        if (empty($cols)) {
            $this->columns = '*';
            return $this;
        }
        // If first argument is an array, use it as columns
        if (is_array($cols[0]) && count($cols) === 1) {
            $this->columns = $cols[0];
        } else {
            // Handle variadic arguments
            $this->columns = $cols;
        }
        return $this;
    }

    /**
     * Force subsequent SELECT of this builder to route to WRITE node.
     * Useful for read-after-write scenarios on replicated setups.
     * This is an alias for backward compatibility. Use forceWrite() instead.
     * @return $this
     */
    public function useWrite()
    {
        $this->forceWrite = true;
        return $this;
    }
    
    /**
     * Force subsequent SELECT of this builder to route to WRITE node.
     * Useful for read-after-write scenarios on replicated setups.
     * @return $this
     */
    public function forceWrite()
    {
        $this->forceWrite = true;
        return $this;
    }

    /**
     * Execute SELECT and return all rows as array.
     * @return array<int,array<string,mixed>>
     */
    public function get()
    {
        list($sql, $bindings) = $this->compileSelect();
        if ($this->forceWrite && \method_exists($this->driver, 'withForceWrite')) {
            $res = $this->driver->withForceWrite(function () use ($sql, $bindings) {
                $res = $this->driver->query($sql, $bindings);
                return is_array($res) ? $res : [];
            });
        } else {
            $res = $this->driver->query($sql, $bindings);
            $res = \is_array($res) ? $res : [];
        }
        
        // Apply eager loading if relationships are specified
        if ($this->eagerLoader !== null && $this->eagerLoader->hasConstraints()) {
            $res = $this->loadRelationships($res);
        }
        
        return $res;
    }

    /**
     * Fetch first row (or null).
     * @return array<string,mixed>|null
     */
    public function first()
    {
        $oldLimit = $this->limit;
        $this->limit(1);
        $rows = $this->get();
        $this->limit = $oldLimit;
        return isset($rows[0]) ? $rows[0] : null;
    }

    /**
     * Fetch first row or throw exception if not found.
     * @return array<string,mixed>
     * @throws \RuntimeException
     */
    public function firstOrFail()
    {
        $result = $this->first();
        if ($result === null) {
            throw new \RuntimeException('No records found.');
        }
        return $result;
    }

    /**
     * Get single value from first row.
     * @param string $column
     * @return mixed
     */
    public function value($column)
    {
        $result = $this->first();
        return $result ? ($result[$column] ?? null) : null;
    }

    /**
     * Get column values as array.
     * @param string $column
     * @param string|null $keyColumn
     * @return array
     */
    public function pluck($column, $keyColumn = null)
    {
        $results = $this->get();
        $plucked = [];
        
        foreach ($results as $row) {
            if ($keyColumn === null) {
                $plucked[] = $row[$column] ?? null;
            } else {
                $key = $row[$keyColumn] ?? null;
                $plucked[$key] = $row[$column] ?? null;
            }
        }
        
        return $plucked;
    }

    /**
     * Find record by ID.
     * @param mixed $id
     * @return array<string,mixed>|null
     */
    public function find($id)
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Check if any records exist.
     * @return bool
     */
    public function exists()
    {
        $oldCols = $this->columns;
        $this->columns = [new \System\Database\Support\SqlExpression('1')];
        
        $result = $this->first();
        $this->columns = $oldCols;
        
        return $result !== null;
    }

    /**
     * Check if no records exist.
     * @return bool
     */
    public function doesntExist()
    {
        return !$this->exists();
    }

    /**
     * Chunk results by processing them in chunks.
     * @param int $count
     * @param callable $callback
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        $this->orderBy('id');
        $page = 1;
        
        do {
            $results = $this->forPage($page, $count)->get();
            
            if (empty($results)) {
                break;
            }
            
            if ($callback($results, $page) === false) {
                return false;
            }
            
            $page++;
        } while (count($results) == $count);
        
        return true;
    }

    /**
     * Chunk results by ID to avoid issues with updated records.
     * @param int $count
     * @param callable $callback
     * @param string $column
     * @param string $alias
     * @return bool
     */
    public function chunkById($count, callable $callback, $column = 'id', $alias = null)
    {
        $alias = $alias ?: $column;
        $lastId = 0;
        
        do {
            $clone = clone $this;
            // Reset orders for chunkById to avoid conflicts
            $clone->orders = [];
            $results = $clone->forPageAfterId($count, $lastId, $column)->get();
            
            if (empty($results)) {
                break;
            }
            
            if ($callback($results) === false) {
                return false;
            }
            
            $lastId = end($results)[$alias];
        } while (count($results) == $count);
        
        return true;
    }

    /**
     * Get a page of results for pagination.
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function forPage($page, $perPage)
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    /**
     * Get results after a specific ID for cursor pagination.
     * @param int $perPage
     * @param mixed $lastId
     * @param string $column
     * @return $this
     */
    public function forPageAfterId($perPage, $lastId, $column = 'id')
    {
        return $this->where($column, $lastId, '>')->limit($perPage);
    }

    /**
     * Lazy load results using a generator.
     * @param int $chunkSize
     * @return \Generator
     */
    public function lazy($chunkSize = 1000)
    {
        $this->orderBy('id');
        $page = 1;
        
        do {
            $results = $this->forPage($page, $chunkSize)->get();
            
            if (empty($results)) {
                break;
            }
            
            foreach ($results as $result) {
                yield $result;
            }
            
            $page++;
        } while (count($results) == $chunkSize);
    }

    /**
     * Lazy load results by ID using a generator.
     * @param int $chunkSize
     * @param string $column
     * @param string $alias
     * @return \Generator
     */
    public function lazyById($chunkSize = 1000, $column = 'id', $alias = null)
    {
        $alias = $alias ?: $column;
        $lastId = 0;
        
        do {
            $clone = clone $this;
            // Reset orders for lazyById to avoid conflicts
            $clone->orders = [];
            $results = $clone->forPageAfterId($chunkSize, $lastId, $column)->get();
            
            if (empty($results)) {
                break;
            }
            
            foreach ($results as $result) {
                yield $result;
            }
            
            $lastId = end($results)[$alias];
        } while (count($results) == $chunkSize);
    }

    /**
     * Union all with another query.
     * @param callable $query
     * @return array
     */
    public function unionAll(callable $query)
    {
        $sub = $this->newSub();
        $query($sub);
        list($subSql, $subBindings) = $sub->compileSelect();
        
        list($sql, $bindings) = $this->compileSelect();
        $unionSql = $sql . ' UNION ALL ' . $subSql;
        $unionBindings = array_merge($bindings, $subBindings);
        
        return $this->driver->query($unionSql, $unionBindings);
    }

    /**
     * Count rows for current query (ignores ORDER BY; preserves filters/joins).
     * @return int
     */
    public function count()
    {
        $oldCols   = $this->columns;
        $oldOrders = $this->orders;
        $oldLimit  = $this->limit;
        $oldOffset = $this->offset;

        $this->columns = [new SqlExpression('COUNT(*)')];
        $this->orders  = [];
        $this->limit   = null;
        $this->offset  = null;

        list($sql, $bindings) = $this->compileSelect();

        // restore
        $this->columns = $oldCols;
        $this->orders  = $oldOrders;
        $this->limit   = $oldLimit;
        $this->offset  = $oldOffset;

        $rows = $this->driver->query($sql, $bindings);
        if (\is_array($rows) && isset($rows[0])) {
            $firstRow = $rows[0];
            $val = \reset($firstRow);
            return (int)$val;
        }
        return 0;
    }

    /**
     * Get list of records with optional filtering and ordering.
     * Convenient method for fetching all records with where/order/limit.
     * 
     * Usage:
     *   $users = DB::table('users')->list('status = ?', ['active'], 'id desc', 10);
     *   $posts = DB::table('posts')->list('', [], 'created_at desc');
     * 
     * @param string $where WHERE clause (raw SQL, default: '')
     * @param array $params Parameters for WHERE clause (default: [])
     * @param string $orderBy ORDER BY clause as "column direction" (default: '')
     * @param int|null $limit Limit results (default: null = no limit)
     * @return array List of records
     */
    public function list($where = '', $params = [], $orderBy = '', $limit = null)
    {
        // Apply WHERE clause if provided
        if (!empty($where)) {
            $this->whereRaw($where, $params);
        }

        // Parse and apply ORDER BY
        if (!empty($orderBy)) {
            $orderParts = preg_split('/\s+/', trim($orderBy), 2);
            $orderColumn = $orderParts[0] ?? 'id';
            $orderDirection = isset($orderParts[1]) ? strtolower(trim($orderParts[1])) : 'asc';
            
            // Validate direction
            if (!in_array($orderDirection, ['asc', 'desc'])) {
                $orderDirection = 'asc';
            }
            
            $this->orderBy($orderColumn, $orderDirection);
        }

        // Apply limit if provided
        if ($limit !== null) {
            $this->limit((int)$limit);
        }

        return $this->get();
    }

    /**
     * Get list of records with custom fields, where, and ordering.
     * Convenient method combining select, whereRaw, orderBy, and get.
     * 
     * Usage:
     *   $users = DB::table('users')->listWith('id, name', 'status = ?', ['active'], 'id desc', 10);
     *   $posts = DB::table('posts')->listWith('title, created_at', '', [], 'created_at desc');
     * 
     * @param string|array $fields Fields to select (default: '*')
     * @param string $where WHERE clause (raw SQL, default: '')
     * @param array $params Parameters for WHERE clause (default: [])
     * @param string $orderBy ORDER BY clause as "column direction" (default: '')
     * @param int|null $limit Limit results (default: null = no limit)
     * @return array List of records
     */
    public function listWith($fields = '*', $where = '', $params = [], $orderBy = '', $limit = null)
    {
        // Apply SELECT fields if not default
        if ($fields !== '*') {
            if (is_string($fields)) {
                // Convert comma-separated string to array
                $fieldsArray = array_map('trim', explode(',', $fields));
                $this->select(...$fieldsArray);
            } elseif (is_array($fields)) {
                $this->select(...$fields);
            }
        }

        // Apply WHERE clause if provided
        if (!empty($where)) {
            $this->whereRaw($where, $params);
        }

        // Parse and apply ORDER BY
        if (!empty($orderBy)) {
            $orderParts = preg_split('/\s+/', trim($orderBy), 2);
            $orderColumn = $orderParts[0] ?? 'id';
            $orderDirection = isset($orderParts[1]) ? strtolower(trim($orderParts[1])) : 'asc';
            
            // Validate direction
            if (!in_array($orderDirection, ['asc', 'desc'])) {
                $orderDirection = 'asc';
            }
            
            $this->orderBy($orderColumn, $orderDirection);
        }

        // Apply limit if provided
        if ($limit !== null) {
            $this->limit((int)$limit);
        }

        return $this->get();
    }

    /**
     * Simple pagination (limit/offset) with next-flag.
     * @param int $perPage
     * @param int $page 1-based
     * @return array{data:array,is_next:bool,page:int}
     */
    public function paginate($perPage, $page)
    {
        $perPage = (int)$perPage > 0 ? (int)$perPage : 10;
        $page    = (int)$page > 0 ? (int)$page : 1;

        $this->limit($perPage + 1)->offset(($page - 1) * $perPage);
        $rows = $this->get();

        $isNext = false;
        if (\count($rows) > $perPage) {
            $isNext = true;
            \array_pop($rows);
        }
        return ['data' => $rows, 'is_next' => $isNext, 'page' => $page];
    }

    /**
     * Advanced pagination with custom fields, where, and order by.
     * Convenient method combining select, whereRaw, orderBy, and paginate.
     * 
     * Usage:
     *   $result = DB::table('users')->paginateWith('*', 'status = ?', ['active'], 'id desc', 1, 15);
     *   $result = User::query()->paginateWith('name, email', '', [], 'created_at desc', 2, 20);
     *   $result = DB::table('posts')->paginateWith('id, title', 'status = ?', ['active'], 'users.id asc', 1, 10);
     * 
     * @param string|array $fields Fields to select (default: '*')
     * @param string $where WHERE clause (raw SQL, default: '')
     * @param array $params Parameters for WHERE clause (default: [])
     * @param string $orderBy ORDER BY clause as "column direction" (default: 'id desc')
     * @param int $page Current page number (1-based, default: 1)
     * @param int|null $limit Items per page (default: 15)
     * @return array{data:array,is_next:bool,page:int} Pagination result
     */
    public function paginateWith($fields = '*', $where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null)
    {
        // Apply SELECT fields if not default
        if ($fields !== '*') {
            if (is_string($fields)) {
                // Convert comma-separated string to array
                $fieldsArray = array_map('trim', explode(',', $fields));
                $this->select(...$fieldsArray);
            } elseif (is_array($fields)) {
                $this->select(...$fields);
            }
        }

        // Apply WHERE clause if provided
        if (!empty($where)) {
            $this->whereRaw($where, $params);
        }

        // Parse and apply ORDER BY
        if (!empty($orderBy)) {
            // Use preg_split to handle multiple spaces properly
            $orderParts = preg_split('/\s+/', trim($orderBy), 2);
            $orderColumn = $orderParts[0] ?? 'id';
            // Default to 'desc' for pagination (newest first) if direction not specified
            $orderDirection = isset($orderParts[1]) ? strtolower(trim($orderParts[1])) : 'desc';
            
            // Validate direction
            if (!in_array($orderDirection, ['asc', 'desc'])) {
                $orderDirection = 'desc';
            }
            
            $this->orderBy($orderColumn, $orderDirection);
        }

        // Execute pagination
        $perPage = $limit ?? 15;
        return $this->paginate($perPage, $page);
    }

    /**
     * Insert one row.
     * @param array<string,mixed> $data
     * @return bool
     */
    public function insert(array $data)
    {
        list($sql, $bindings) = $this->compileInsert($data);
        $aff = $this->driver->query($sql, $bindings);
        return \is_int($aff) ? ($aff > 0) : (bool)$aff;
    }

    /**
     * Insert and get last inserted ID (string).
     * @param array<string,mixed> $data
     * @return string
     */
    public function insertGetId(array $data)
    {
        return $this->insert($data) ? $this->driver->lastInsertId() : '0';
    }

    /**
     * Insert multiple rows in a single statement.
     * @param array<int,array<string,mixed>> $rows
     * @return int affected rows
     */
    public function insertMany(array $rows)
    {
        if (empty($rows)) return 0;
        list($sql, $bindings) = $this->compileInsertMany($rows);
        $aff = $this->driver->query($sql, $bindings);
        return \is_int($aff) ? $aff : 0;
    }

    /**
     * Insert or ignore (skip on duplicate key).
     * @param array<string,mixed> $data
     * @return bool
     */
    public function insertOrIgnore(array $data)
    {
        list($sql, $bindings) = $this->compileInsertOrIgnore($data);
        $aff = $this->driver->query($sql, $bindings);
        return \is_int($aff) ? ($aff > 0) : (bool)$aff;
    }

    /**
     * Insert using subquery.
     * @param array<string> $columns
     * @param callable $query
     * @return int affected rows
     */
    public function insertUsing(array $columns, callable $query)
    {
        $sub = $this->newSub();
        $query($sub);
        list($sql, $bindings) = $this->compileInsertUsing($columns, $sub);
        $aff = $this->driver->query($sql, $bindings);
        return \is_int($aff) ? $aff : 0;
    }

    /**
     * Update or insert (upsert).
     * @param array<string,mixed> $values
     * @param array<string,mixed> $updateValues
     * @return bool
     */
    public function updateOrInsert(array $values, array $updateValues = array())
    {
        // First try to update
        $updateBuilder = clone $this;
        foreach ($values as $key => $value) {
            $updateBuilder->where($key, $value);
        }
        
        if (!empty($updateValues)) {
            $affected = $updateBuilder->update($updateValues);
            if ($affected > 0) {
                return true;
            }
        }
        
        // If no rows updated, insert
        return $this->insert($values);
    }

    /**
     * Increment a column's value.
     * @param string $column
     * @param int $amount
     * @param array<string,mixed> $extra
     * @return int affected rows
     */
    public function increment($column, $amount = 1, array $extra = array())
    {
        $quotedColumn = $this->grammar->quoteIdentifier($column);
        $data = array_merge([$column => new \System\Database\Support\SqlExpression("{$quotedColumn} + " . (int)$amount)], $extra);
        return $this->update($data);
    }

    /**
     * Decrement a column's value.
     * @param string $column
     * @param int $amount
     * @param array<string,mixed> $extra
     * @return int affected rows
     */
    public function decrement($column, $amount = 1, array $extra = array())
    {
        $quotedColumn = $this->grammar->quoteIdentifier($column);
        $data = array_merge([$column => new \System\Database\Support\SqlExpression("{$quotedColumn} - " . (int)$amount)], $extra);
        return $this->update($data);
    }

    /**
     * Apply a callback if a given condition is true.
     * @param mixed $value
     * @param callable $callback
     * @param callable|null $default
     * @return $this
     */
    public function when($value, callable $callback, callable $default = null)
    {
        if ($value) {
            $callback($this, $value);
        } elseif ($default) {
            $default($this, $value);
        }
        
        return $this;
    }

    /**
     * Update rows.
     *
     * Supports:
     *  1) Laravel-like chaining:
     *     ->where(...)->update($data)
     *  2) CodeIgniter-like inline where:
     *     ->update($data, ['status' => 'inactive'])
     *     ->update($data, 'status = ?', ['inactive'])
     *  3) Callable group:
     *     ->update($data, function($q){ $q->where('status','inactive'); })
     *
     * ⚠️ SECURITY: Throws exception if no WHERE clause is provided to prevent accidental mass updates
     *
     * @param array<string,mixed> $data
     * @param array|string|callable|null $where  (optional)
     * @param array<int,mixed> $params          (when $where is string)
     * @return int affected rows
     * @throws \RuntimeException If no WHERE clause is provided
     */
    public function update(array $data, $where = null, array $params = array())
    {
        $table = $this->grammar->quoteIdentifier($this->from);
        $cols  = array_keys($data);

        $sets  = array();
        $bindings = array();
        foreach ($cols as $c) {
            $val = $data[$c];
            if ($val instanceof \System\Database\Support\SqlExpression) {
                $sets[] = $this->grammar->quoteIdentifier($c).' = '.$val;
            } else {
                $sets[] = $this->grammar->quoteIdentifier($c).' = ?';
                $bindings[] = $val;
            }
        }

        $sql = "UPDATE {$table} SET ".implode(', ', $sets);

        // Build WHERE
        $compiled = $this->compileWhereFromMixed($where, $params);
        if ($compiled['sql'] !== '') {
            $sql .= ' WHERE '.$compiled['sql'];
            $bindings = array_merge($bindings, $compiled['bindings']);
        } else {
            // 🚨 SECURITY CHECK: Prevent mass update without WHERE clause
            throw new \RuntimeException(
                "Update query must include a WHERE clause to prevent mass updates. " .
                "Use ->where() before ->update() or pass where as parameter."
            );
        }

        $aff = $this->driver->query($sql, $bindings);
        return is_int($aff) ? $aff : 0;
    }


    /**
     * Delete rows.
     *
     * Supports CI-like and Laravel-like usage:
     *  ->where(...)->delete()
     *  ->delete(['status'=>'inactive'])
     *  ->delete('status = ?', ['inactive'])
     *  ->delete(function($q){ $q->where('status','inactive'); })
     *
     * ⚠️ SECURITY: Throws exception if no WHERE clause is provided to prevent accidental mass deletion
     *
     * @param array|string|callable|null $where
     * @param array<int,mixed>           $params
     * @return int affected rows
     * @throws \RuntimeException If no WHERE clause is provided
     */
    public function delete($where = null, array $params = array())
    {
        $table = $this->grammar->quoteIdentifier($this->from);
        $sql   = "DELETE FROM {$table}";
        $bindings = array();

        $compiled = $this->compileWhereFromMixed($where, $params);
        if ($compiled['sql'] !== '') {
            $sql .= ' WHERE '.$compiled['sql'];
            $bindings = $compiled['bindings'];
        } else {
            // 🚨 SECURITY CHECK: Prevent mass deletion without WHERE clause
            throw new \RuntimeException(
                "Delete query must include a WHERE clause to prevent mass deletion. " .
                "Use ->where() before ->delete() or pass where as parameter."
            );
        }

        $aff = $this->driver->query($sql, $bindings);
        return is_int($aff) ? $aff : 0;
    }
    
    /**
     * Compile WHERE from:
     *  - null      : use $this->wheres already chained
     *  - string    : raw SQL, use $params
     *  - array     : ['col' => val, ...] or [['col','op','val'], ...]
     *  - callable  : function(Builder $q){ ... }  (grouped)
     *
     * @param mixed $where
     * @param array $params
     * @return array{sql:string, bindings:array}
     */
    private function compileWhereFromMixed($where, array $params): array
    {
        // Case 1: raw string WHERE
        if (is_string($where) && $where !== '') {
            return array('sql' => $where, 'bindings' => $params);
        }

        // Case 2: callable => build a sub builder group WHERE
        if (is_callable($where)) {
            $sub = $this->newSub();
            $where($sub); // mutate sub where
            list($subSql, $subBindings) = $sub->compileSelect();
            $frag = $this->extractWhereFragment($subSql);
            if ($frag !== '') {
                return array('sql' => '('.$frag.')', 'bindings' => $subBindings);
            }
            // no conditions
            return array('sql' => '', 'bindings' => array());
        }

        // Case 3: array (assoc or list of tuples)
        if (is_array($where) && !empty($where)) {
            $tmpSql = array();
            $tmpBind = array();
            foreach ($where as $k => $v) {
                if (is_int($k) && is_array($v)) {
                    // tuple form: ['col','op','val'] or ['col','val'] (op '=')
                    $col = $v[0] ?? null;
                    $op  = $v[2] !== null ? ($v[1] ?? '=') : (isset($v[1]) ? '=' : '=');
                    $val = $v[2] ?? ($v[1] ?? null);
                } else {
                    // assoc form: ['col' => val]  (op '=')
                    $col = $k; $op = '='; $val = $v;
                }

                $chunk = $this->buildWhereChunk($col, $val, $op);
                $tmpSql[] = $chunk['sql'];
                foreach ($chunk['bindings'] as $b) $tmpBind[] = $b;
            }
            return array('sql' => implode(' AND ', $tmpSql), 'bindings' => $tmpBind);
        }

        // Case 4: null/empty => use $this->wheres already built by chain
        if (!empty($this->wheres)) {
            $sql = '';
            $bindings = array();
            $first = true;
            foreach ($this->wheres as $w) {
                if (!$first) $sql .= ' '.$w['boolean'].' ';
                $sql .= $w['sql'];
                foreach ($w['bindings'] as $b) $bindings[] = $b;
                $first = false;
            }
            return array('sql' => $sql, 'bindings' => $bindings);
        }

        return array('sql' => '', 'bindings' => array());
    }

    /** Extract WHERE ... from a compiled SELECT sql */
    private function extractWhereFragment(string $sql): string
    {
        $pos = stripos($sql, ' WHERE ');
        if ($pos === false) return '';
        return substr($sql, $pos + 7);
    }


    /**
     * TRUNCATE table (best effort; SQLite fallback to DELETE FROM).
     * @return bool
     */
    public function truncate()
    {
        list($sql, $bindings) = $this->compileTruncate();
        $res = $this->driver->query($sql, $bindings);
        return \is_int($res) ? ($res >= 0) : (bool)$res;
    }

    /**
     * Upsert (single-row) via Grammar::compileUpsert.
     * @param array<string,mixed> $data
     * @param string[]            $uniqueBy
     * @param string[]            $updateCols
     * @return bool
     */
    public function upsert(array $data, array $uniqueBy = array('id'), array $updateCols = array())
    {
        list($sql, $bindings) = $this->compileUpsert($data, $uniqueBy, $updateCols);
        $aff = $this->driver->query($sql, $bindings);
        return \is_int($aff) ? ($aff > 0) : (bool)$aff;
    }
}
