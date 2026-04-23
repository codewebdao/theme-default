<?php
namespace System\Database\Query\Traits;

/** Common Table Expressions (WITH/RECURSIVE), grammar-aware. */
trait CteTrait
{
    /** @var array<int,array{name:string,sql:string,bindings:array,recursive:bool}> */
    protected $ctes = array();

    /**
     * WITH name AS (subquery) - CTE (Common Table Expression)
     * @param string   $name
     * @param callable $closure function(Builder $q): Builder|void
     * @return $this
     */
    public function withCte($name, callable $closure)
    {
        if (!$this->grammar->supportsCte()) {
            throw new \LogicException('CTE not supported by this grammar.');
        }
        $sub = $this->compileSub($closure);
        $this->ctes[] = array('name'=>$name,'sql'=>$sub['sql'],'bindings'=>$sub['bindings'],'recursive'=>false);
        return $this;
    }

    /**
     * WITH RECURSIVE name AS (subquery)
     * @param string   $name
     * @param callable $closure
     * @return $this
     */
    public function withRecursive($name, callable $closure)
    {
        if (!$this->grammar->supportsCteRecursive()) {
            throw new \LogicException('Recursive CTE not supported by this grammar.');
        }
        $sub = $this->compileSub($closure);
        $this->ctes[] = array('name'=>$name,'sql'=>$sub['sql'],'bindings'=>$sub['bindings'],'recursive'=>true);
        return $this;
    }
}
