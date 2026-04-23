<?php
namespace System\Database\Query\Traits;

/** JSON helpers (grammar-aware). */
trait JsonTrait
{
    /**
     * WHERE JSON contains needle at optional path.
     * @param string       $column
     * @param mixed        $needle scalar|array
     * @param string|null  $path MySQL: '$.path', PG: jsonpath (adapt in grammar)
     * @return $this
     */
    public function whereJsonContains($column, $needle, $path = null)
    {
        if (!$this->grammar->supportsJson()) {
            throw new \LogicException('JSON predicates not supported by this grammar.');
        }
        list($sql, $bindings) = $this->grammar->compileWhereJsonContains($this, $column, $needle, $path);
        $this->wheres[] = ['type'=>'json','boolean'=>'AND','sql'=>$sql,'bindings'=>$bindings];
        return $this;
    }

    /**
     * WHERE JSON length OP len at path.
     * @param string      $column
     * @param int         $len
     * @param string      $op default '='
     * @param string|null $path
     * @return $this
     */
    public function whereJsonLength($column, $len, $op = '=', $path = null)
    {
        if (!$this->grammar->supportsJson()) {
            throw new \LogicException('JSON predicates not supported by this grammar.');
        }
        $op = $this->normalizeOp($op);
        list($sql, $bindings) = $this->grammar->compileWhereJsonLength($this, $column, (int)$len, $op, $path);
        $this->wheres[] = ['type'=>'json_len','boolean'=>'AND','sql'=>$sql,'bindings'=>$bindings];
        return $this;
    }

    /**
     * WHERE JSON does not contain needle at optional path.
     * @param string       $column
     * @param mixed        $needle scalar|array
     * @param string|null  $path
     * @return $this
     */
    public function whereJsonDoesntContain($column, $needle, $path = null)
    {
        if (!$this->grammar->supportsJson()) {
            throw new \LogicException('JSON predicates not supported by this grammar.');
        }
        list($sql, $bindings) = $this->grammar->compileWhereJsonDoesntContain($this, $column, $needle, $path);
        $this->wheres[] = ['type'=>'json_not_contains','boolean'=>'AND','sql'=>$sql,'bindings'=>$bindings];
        return $this;
    }

    /**
     * WHERE JSON contains key at path.
     * @param string       $column
     * @param string       $key
     * @param string|null  $path
     * @return $this
     */
    public function whereJsonContainsKey($column, $key, $path = null)
    {
        if (!$this->grammar->supportsJson()) {
            throw new \LogicException('JSON predicates not supported by this grammar.');
        }
        list($sql, $bindings) = $this->grammar->compileWhereJsonContainsKey($this, $column, $key, $path);
        $this->wheres[] = ['type'=>'json_contains_key','boolean'=>'AND','sql'=>$sql,'bindings'=>$bindings];
        return $this;
    }

    /**
     * WHERE JSON does not contain key at path.
     * @param string       $column
     * @param string       $key
     * @param string|null  $path
     * @return $this
     */
    public function whereJsonDoesntContainKey($column, $key, $path = null)
    {
        if (!$this->grammar->supportsJson()) {
            throw new \LogicException('JSON predicates not supported by this grammar.');
        }
        list($sql, $bindings) = $this->grammar->compileWhereJsonDoesntContainKey($this, $column, $key, $path);
        $this->wheres[] = ['type'=>'json_not_contains_key','boolean'=>'AND','sql'=>$sql,'bindings'=>$bindings];
        return $this;
    }
}
