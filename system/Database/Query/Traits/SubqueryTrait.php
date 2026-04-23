<?php
namespace System\Database\Query\Traits;

use System\Database\Support\SqlExpression;

/**
 * Subquery helpers:
 * - whereGroup/orWhereGroup
 * - whereInSub/whereNotInSub
 * - whereExists/whereNotExists
 * - selectSub/fromSub
 * - union/unionAll (immediate execution)
 */
trait SubqueryTrait
{
    /** Create a child builder inheriting connection/grammar/prefix. */
    private function newSub()
    {
        return new \System\Database\Query\Builder($this->driver, $this->grammar, $this->connectionName, $this->prefix);
    }

    /**
     * Compile a closure-defined subquery to "( ... )" SQL + bindings.
     * @param callable $closure function(Builder $q): Builder|void
     * @return array{sql:string,bindings:array}
     */
    private function compileSub(callable $closure)
    {
        $sub = $this->newSub();
        $result = $closure($sub);
        $use = ($result instanceof \System\Database\Query\Builder) ? $result : $sub;
        list($sql, $bind) = $use->compileSelect();
        return array('sql'=>'('.$sql.')','bindings'=>$bind);
    }

    /**
     * Grouped WHERE with parentheses using a sub-builder.
     * @param callable $closure function(Builder $q): void
     * @return $this
     */
    public function whereGroup(callable $closure)
    {
        $sub = $this->newSub(); $closure($sub);
        list($sql, $bindings) = $sub->compileSelect();

        $whereFrag = '';
        $pos = \stripos($sql, ' WHERE ');
        if ($pos !== false) $whereFrag = \substr($sql, $pos + 7);
        if ($whereFrag === '') return $this;

        $this->wheres[] = array('type'=>'group','boolean'=>'AND','sql'=>'('.$whereFrag.')','bindings'=>$bindings);
        return $this;
    }

    /** OR (...) variant */
    public function orWhereGroup(callable $closure)
    {
        $sub = $this->newSub(); $closure($sub);
        list($sql, $bindings) = $sub->compileSelect();
        $whereFrag = '';
        $pos = \stripos($sql, ' WHERE ');
        if ($pos !== false) $whereFrag = \substr($sql, $pos + 7);
        if ($whereFrag === '') return $this;

        $this->wheres[] = array('type'=>'group','boolean'=>'OR','sql'=>'('.$whereFrag.')','bindings'=>$bindings);
        return $this;
    }

    /** WHERE col IN (subquery) */
    public function whereInSub($column, callable $closure)
    {
        $sub = $this->compileSub($closure);
        $this->wheres[] = array('type'=>'in_sub','boolean'=>'AND','sql'=>$this->grammar->quoteIdentifier($column).' IN '.$sub['sql'],'bindings'=>$sub['bindings']);
        return $this;
    }

    /** WHERE col NOT IN (subquery) */
    public function whereNotInSub($column, callable $closure)
    {
        $sub = $this->compileSub($closure);
        $this->wheres[] = array('type'=>'in_sub','boolean'=>'AND','sql'=>$this->grammar->quoteIdentifier($column).' NOT IN '.$sub['sql'],'bindings'=>$sub['bindings']);
        return $this;
    }

    /** WHERE EXISTS (subquery) */
    public function whereExists(callable $closure)
    {
        $sub = $this->compileSub($closure);
        $this->wheres[] = array('type'=>'exists','boolean'=>'AND','sql'=>'EXISTS '.$sub['sql'],'bindings'=>$sub['bindings']);
        return $this;
    }

    /** WHERE NOT EXISTS (subquery) */
    public function whereNotExists(callable $closure)
    {
        $sub = $this->compileSub($closure);
        $this->wheres[] = array('type'=>'exists','boolean'=>'AND','sql'=>'NOT EXISTS '.$sub['sql'],'bindings'=>$sub['bindings']);
        return $this;
    }

    /** SELECT (subquery) AS alias */
    public function selectSub(callable $closure, $alias)
    {
        $sub = $this->compileSub($closure);
        $this->columns[] = new SqlExpression($sub['sql'].' AS '.$this->grammar->quoteIdentifier($alias));
        return $this;
    }

    /** FROM (subquery) AS alias */
    public function fromSub(callable $closure, $alias)
    {
        $sub = $this->compileSub($closure);
        $this->from = $sub['sql'].' AS '.$alias;
        return $this;
    }

    /** UNION */
    public function union(callable $closure)
    {
        $sub = $this->compileSub($closure);
        list($sql, $bind) = $this->compileSelect();
        $unionSql  = $sql.' UNION '.$sub['sql'];
        $unionBind = \array_merge($bind, $sub['bindings']);
        return $this->driver->query($unionSql, $unionBind);
    }

    /** UNION ALL */
    public function unionAll(callable $closure)
    {
        $sub = $this->compileSub($closure);
        list($sql, $bind) = $this->compileSelect();
        $unionSql  = $sql.' UNION ALL '.$sub['sql'];
        $unionBind = \array_merge($bind, $sub['bindings']);
        return $this->driver->query($unionSql, $unionBind);
    }
}
