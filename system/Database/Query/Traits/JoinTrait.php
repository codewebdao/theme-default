<?php
namespace System\Database\Query\Traits;

/** JOIN helpers. */
trait JoinTrait
{
    /**
     * JOIN table ON left op right
     * @param string $table
     * @param string $left
     * @param string $op
     * @param string $right
     * @param string $type 'INNER'|'LEFT'|'RIGHT'
     * @return $this
     */
    public function join($table, $left, $op, $right, $type = 'INNER')
    {
        $this->joins[] = array(
            'type'  => \strtoupper($type),
            'table' => $this->prefixed($table),
            'left'  => $left,
            'op'    => $op,
            'right' => $right,
        );
        return $this;
    }

    /** @return $this */
    public function leftJoin($table, $left, $op, $right)  { return $this->join($table,$left,$op,$right,'LEFT'); }
    /** @return $this */
    public function rightJoin($table, $left, $op, $right) { return $this->join($table,$left,$op,$right,'RIGHT'); }

    /**
     * Cross join table.
     * @param string $table
     * @return $this
     */
    public function crossJoin($table)
    {
        $this->joins[] = array(
            'type'  => 'CROSS',
            'table' => $this->prefixed($table),
            'left'  => '',
            'op'    => '',
            'right' => '',
        );
        return $this;
    }

    /**
     * Join subquery.
     * @param \System\Database\Query\Builder $query
     * @param string $alias
     * @param callable $callback
     * @return $this
     */
    public function joinSub($query, $alias, callable $callback)
    {
        list($sql, $bindings) = $query->compileSelect();
        
        // Create a temporary join clause to collect conditions
        $joinClause = new \System\Database\Query\JoinClause($this->grammar, $alias);
        $callback($joinClause);
        
        $this->joins[] = array(
            'type'  => 'INNER',
            'table' => '(' . $sql . ') AS ' . $alias,
            'left'  => $joinClause->left,
            'op'    => $joinClause->op,
            'right' => $joinClause->right,
        );
        
        return $this;
    }

    /**
     * Left join subquery.
     * @param \System\Database\Query\Builder $query
     * @param string $alias
     * @param callable $callback
     * @return $this
     */
    public function leftJoinSub($query, $alias, callable $callback)
    {
        list($sql, $bindings) = $query->compileSelect();
        
        // Create a temporary join clause to collect conditions
        $joinClause = new \System\Database\Query\JoinClause($this->grammar, $alias);
        $callback($joinClause);
        
        $this->joins[] = array(
            'type'  => 'LEFT',
            'table' => '(' . $sql . ') AS ' . $alias,
            'left'  => $joinClause->left,
            'op'    => $joinClause->op,
            'right' => $joinClause->right,
        );
        
        return $this;
    }

    /**
     * Right join subquery.
     * @param \System\Database\Query\Builder $query
     * @param string $alias
     * @param callable $callback
     * @return $this
     */
    public function rightJoinSub($query, $alias, callable $callback)
    {
        list($sql, $bindings) = $query->compileSelect();
        
        // Create a temporary join clause to collect conditions
        $joinClause = new \System\Database\Query\JoinClause($this->grammar, $alias);
        $callback($joinClause);
        
        $this->joins[] = array(
            'type'  => 'RIGHT',
            'table' => '(' . $sql . ') AS ' . $alias,
            'left'  => $joinClause->left,
            'op'    => $joinClause->op,
            'right' => $joinClause->right,
        );
        
        return $this;
    }

}
