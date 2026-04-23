<?php
namespace System\Database\Query\Traits;

use System\Database\DatabaseDriver;
use System\Database\Support\SqlExpression;

/**
 * BaseTrait
 * - Holds builder state
 * - Prefix handling, quoting, op normalization
 * - Generic compilation glue (delegates to Grammar)
 * - Common API: order/limit/offset/select
 */
trait BaseTrait
{
    /** @var DatabaseDriver */
    protected $driver;
    /** @var \System\Database\Query\Grammar */
    protected $grammar;
    /** @var string */
    protected $connectionName = '';
    /** @var string */
    protected $prefix = '';

    /** @var string */
    protected $from = '';
    /** @var array<int,string|SqlExpression> */
    protected $columns = array('*');
    /** @var array<int,array{type:string,boolean:string,sql:string,bindings:array}> */
    protected $wheres = array();
    /** @var array<int,array{type:string,table:string,left:string,op:string,right:string}> */
    protected $joins = array();
    /** @var array<int,string> */
    protected $groups = array();
    /** @var array<int,string> */
    protected $havings = array();
    /** @var array<int,array{col:string,dir:string}> */
    protected $orders = array();
    /** @var int|null */
    protected $limit = null;
    /** @var int|null */
    protected $offset = null;
    /** @var bool */
    protected $forceWrite = false;
    /** @var bool */
    protected $distinct = false;

    /** Reset transient state (keep connection & grammar). */
    protected function reset(): void
    {
        $this->from    = '';
        $this->columns = array('*');
        $this->wheres  = array();
        $this->joins   = array();
        $this->groups  = array();
        $this->havings = array();
        $this->orders  = array();
        $this->limit   = null;
        $this->offset  = null;
        $this->forceWrite = false;
        $this->distinct = false;
    }

    /**
     * Apply table prefix (schema.table supported; prefix last part).
     * @param string $table
     * @return string
     */
    protected function prefixed($table)
    {
        $table = \trim((string)$table);
        if ($this->prefix === '' || $table === '') return $table;

        if (\strpos($table, '.') !== false) {
            $parts = \explode('.', $table);
            $last  = \array_pop($parts);
            if (\strpos($last, $this->prefix) !== 0) {
                $last = $this->prefix . $last;
            }
            return \implode('.', $parts) . '.' . $last;
        }
        if (\strpos($table, $this->prefix) !== 0) {
            return $this->prefix . $table;
        }
        return $table;
    }

    /**
     * Normalize operator and fix common typos ('=>', '=<').
     * @param string $op
     * @return string
     */
    protected function normalizeOp($op)
    {
        $op = \strtoupper(\trim((string)$op));
        if ($op === '=>') $op = '>=';
        if ($op === '=<') $op = '<=';
        if ($op === '<>') $op = '!=';

        $ok = array('=','!=','>','>=','<','<=','LIKE','NOT LIKE','IN','NOT IN');
        return \in_array($op, $ok, true) ? $op : '=';
    }

    /**
     * Set LIMIT.
     * @param int $n
     * @return $this
     */
    public function limit($n)
    {
        $this->limit = (int)$n;
        return $this;
    }

    /**
     * Set OFFSET.
     * @param int $n
     * @return $this
     */
    public function offset($n)
    {
        $this->offset = (int)$n;
        return $this;
    }

    /**
     * ORDER BY (basic).
     * @param string $col
     * @param string $dir 'ASC'|'DESC'
     * @return $this
     */
    public function orderBy($col, $dir = 'ASC')
    {
        $this->orders[] = array(
            'col' => (string)$col,
            'dir' => \strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC'
        );
        return $this;
    }

    /**
     * Add columns to existing SELECT clause.
     * @param string|array|SqlExpression ...$cols
     * @return $this
     */
    public function addSelect(...$cols)
    {
        if (empty($cols)) {
            return $this;
        }

        // If first argument is an array, merge it with existing columns
        if (is_array($cols[0]) && count($cols) === 1) {
            $this->columns = array_merge($this->columns, $cols[0]);
        } else {
            // Handle variadic arguments
            $this->columns = array_merge($this->columns, $cols);
        }
        
        return $this;
    }

    /**
     * Add raw expression to SELECT clause.
     * @param string $expression
     * @param array $bindings
     * @return $this
     */
    public function selectRaw($expression, array $bindings = array())
    {
        $this->columns[] = new \System\Database\Support\SqlExpression($expression);
        return $this;
    }

    /**
     * Get maximum value of a column.
     * @param string $column
     * @return mixed
     */
    public function max($column)
    {
        $oldCols = $this->columns;
        $this->columns = array(new \System\Database\Support\SqlExpression("MAX({$this->grammar->quoteIdentifier($column)})"));
        
        $result = $this->first();
        $this->columns = $oldCols;
        
        return $result ? reset($result) : null;
    }

    /**
     * Get minimum value of a column.
     * @param string $column
     * @return mixed
     */
    public function min($column)
    {
        $oldCols = $this->columns;
        $this->columns = array(new \System\Database\Support\SqlExpression("MIN({$this->grammar->quoteIdentifier($column)})"));
        
        $result = $this->first();
        $this->columns = $oldCols;
        
        return $result ? reset($result) : null;
    }

    /**
     * Get average value of a column.
     * @param string $column
     * @return mixed
     */
    public function avg($column)
    {
        $oldCols = $this->columns;
        $this->columns = array(new \System\Database\Support\SqlExpression("AVG({$this->grammar->quoteIdentifier($column)})"));
        
        $result = $this->first();
        $this->columns = $oldCols;
        
        return $result ? reset($result) : null;
    }

    /**
     * Get sum of a column.
     * @param string $column
     * @return mixed
     */
    public function sum($column)
    {
        $oldCols = $this->columns;
        $this->columns = array(new \System\Database\Support\SqlExpression("SUM({$this->grammar->quoteIdentifier($column)})"));
        
        $result = $this->first();
        $this->columns = $oldCols;
        
        return $result ? reset($result) : null;
    }

    /**
     * ORDER BY DESC (convenience method).
     * @param string $col
     * @return $this
     */
    public function orderByDesc($col)
    {
        return $this->orderBy($col, 'DESC');
    }

    /**
     * ORDER BY latest date (default: created_at).
     * @param string $column
     * @return $this
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * ORDER BY oldest date (default: created_at).
     * @param string $column
     * @return $this
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'ASC');
    }

    /**
     * ORDER BY random.
     * @return $this
     */
    public function inRandomOrder()
    {
        $this->orders[] = array(
            'col' => 'RAND()',
            'dir' => ''
        );
        return $this;
    }

    /**
     * Remove all existing ORDER BY clauses.
     * @param string|null $column
     * @param string $direction
     * @return $this
     */
    public function reorder($column = null, $direction = 'ASC')
    {
        $this->orders = array();
        
        if ($column !== null) {
            $this->orderBy($column, $direction);
        }
        
        return $this;
    }

    /**
     * Remove all existing ORDER BY clauses and order by column DESC.
     * @param string $column
     * @return $this
     */
    public function reorderDesc($column)
    {
        return $this->reorder($column, 'DESC');
    }

    /**
     * ORDER BY raw expression.
     * @param string $expression
     * @return $this
     */
    public function orderByRaw($expression)
    {
        $this->orders[] = array(
            'col' => $expression,
            'dir' => ''
        );
        return $this;
    }

    // ---------- Compilation glue (Grammar) ----------

    /** @return array{0:string,1:array} */
    protected function compileSelect()
    {
        return $this->grammar->compileSelect($this);
    }

    /** @param array<string,mixed> $data @return array{0:string,1:array} */
    protected function compileInsert(array $data)
    {
        return $this->grammar->compileInsert($this, $data);
    }

    /** @param array<int,array<string,mixed>> $rows @return array{0:string,1:array} */
    protected function compileInsertMany(array $rows)
    {
        return $this->grammar->compileInsertMany($this, $rows);
    }

    /** @return array{0:string,1:array} */
    protected function compileUpdate(array $data, $whereRaw, array $params)
    {
        return $this->grammar->compileUpdate($this, $data, $whereRaw, $params);
    }

    /** @return array{0:string,1:array} */
    protected function compileDelete($whereRaw, array $params)
    {
        return $this->grammar->compileDelete($this, $whereRaw, $params);
    }

    /** @return array{0:string,1:array} */
    protected function compileTruncate()
    {
        return $this->grammar->compileTruncate($this);
    }

    /** @return array{0:string,1:array} */
    protected function compileUpsert(array $data, array $uniqueBy, array $updateCols)
    {
        return $this->grammar->compileUpsert($this, $data, $uniqueBy, $updateCols);
    }

        // ---------- Safe getters for Grammar ----------

    /** @return string */
    public function getFrom() { return (string)$this->from; }

    /** @return array<int,string|\System\Database\Support\SqlExpression> */
    public function getColumns() { return $this->columns; }

    /** @return bool */
    public function isDistinct() { return (bool)$this->distinct; }

    /** @return array<int,array{type:string,boolean:string,sql:string,bindings:array}> */
    public function getWheres() { return $this->wheres; }

    /** @return array<int,array{type:string,table:string,left:string,op:string,right:string}> */
    public function getJoins() { return $this->joins; }

    /** @return array<int,string> */
    public function getGroups() { return $this->groups; }

    /** @return array<int,string> */
    public function getHavings() { return $this->havings; }

    /** @return array<int,array{col:string,dir:string}> */
    public function getOrders() { return $this->orders; }

    /** @return int|null */
    public function getLimit() { return $this->limit; }

    /** @return int|null */
    public function getOffset() { return $this->offset; }

    /** @return array<int,array{name:string,sql:string,bindings:array,recursive:bool}> */
    public function getCtes() { return \property_exists($this, 'ctes') ? $this->ctes : array(); }

    /** @return string|null */
    public function getLockClause() { return \property_exists($this, 'lockClause') ? $this->lockClause : null; }

    /** @return array<int,string>|null */
    public function getReturning() { return \property_exists($this, 'returning') ? $this->returning : null; }

}
