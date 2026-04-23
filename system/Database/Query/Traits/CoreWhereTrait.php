<?php
namespace System\Database\Query\Traits;

/**
 * CoreWhereTrait
 * - where/orWhere
 * - whereIn/whereNotIn
 * - whereBetween
 * - whereRaw
 */
trait CoreWhereTrait
{
    /**
     * Basic WHERE (AND).
     * @param string $column
     * @param mixed  $val
     * @param string $op default '=' | IN/NOT IN/LIKE/NOT LIKE also supported
     * @return $this
     */
    public function where($column, $val, $op = '=')
    {
        $op = $this->normalizeOp($op);
        $qid = $this->grammar->quoteIdentifier($column);

        if ($op === 'IN' || $op === 'NOT IN') {
            $vals = \array_values((array)$val);
            if (empty($vals)) {
                // IN () -> false; NOT IN () -> true
                $this->wheres[] = array('type'=>'basic','boolean'=>'AND','sql'=>($op==='IN'?'1=0':'1=1'),'bindings'=>array());
                return $this;
            }
            $ph = \implode(',', \array_fill(0, \count($vals), '?'));
            $this->wheres[] = array('type'=>'basic','boolean'=>'AND','sql'=>"$qid $op ($ph)",'bindings'=>$vals);
            return $this;
        }

        $this->wheres[] = array('type'=>'basic','boolean'=>'AND','sql'=>"$qid $op ?",'bindings'=>array($val));
        return $this;
    }

    /**
     * OR WHERE variant.
     * @param string $column
     * @param mixed  $val
     * @param string $op
     * @return $this
     */
    public function orWhere($col, $val, $op = '=')
    {
        $before = \count($this->wheres);
        $this->where($col, $val, $op);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * WHERE BETWEEN (inclusive).
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @return $this
     */
    public function whereBetween($column, $min, $max)
    {
        $qid = $this->grammar->quoteIdentifier($column);
        $this->wheres[] = array(
            'type'=>'between','boolean'=>'AND',
            'sql'=>"$qid BETWEEN ? AND ?", 'bindings'=>array($min, $max)
        );
        return $this;
    }

    /**
     * OR WHERE BETWEEN (inclusive).
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @return $this
     */
    public function orWhereBetween($column, $min, $max)
    {
        $before = \count($this->wheres);
        $this->whereBetween($column, $min, $max);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * WHERE IN (values).
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereIn($column, array $values)
    {
        return $this->where($column, $values, 'IN');
    }

    /**
     * OR WHERE IN (values).
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function orWhereIn($column, array $values)
    {
        return $this->orWhere($column, $values, 'IN');
    }

    /**
     * Raw WHERE (advanced).
     * @param string $rawSql
     * @param array  $bindings
     * @param string $boolean 'AND'|'OR'
     * @return $this
     */
    public function whereRaw($rawSql, array $bindings = array(), $boolean = 'AND')
    {
        $this->wheres[] = array(
            'type'=>'raw','boolean'=> \strtoupper($boolean)==='OR'?'OR':'AND',
            'sql'=>(string)$rawSql,'bindings'=>$bindings
        );
        return $this;
    }

    /**
     * WHERE NOT (negate a condition).
     * @param string $column
     * @param mixed  $val
     * @param string $op
     * @return $this
     */
    public function whereNot($column, $val, $op = '=')
    {
        $op = $this->normalizeOp($op);
        $qid = $this->grammar->quoteIdentifier($column);
        $this->wheres[] = array('type'=>'basic','boolean'=>'AND','sql'=>"NOT ($qid $op ?)",'bindings'=>array($val));
        return $this;
    }

    /**
     * WHERE NOT IN (values).
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereNotIn($column, array $values)
    {
        return $this->where($column, $values, 'NOT IN');
    }

    /**
     * WHERE NOT BETWEEN (exclusive).
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @return $this
     */
    public function whereNotBetween($column, $min, $max)
    {
        $qid = $this->grammar->quoteIdentifier($column);
        $this->wheres[] = array(
            'type'=>'not_between','boolean'=>'AND',
            'sql'=>"NOT ($qid BETWEEN ? AND ?)", 'bindings'=>array($min, $max)
        );
        return $this;
    }

    /**
     * OR WHERE NOT variant.
     * @param string $column
     * @param mixed  $val
     * @param string $op
     * @return $this
     */
    public function orWhereNot($col, $val, $op = '=')
    {
        $before = \count($this->wheres);
        $this->whereNot($col, $val, $op);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * OR WHERE NOT IN variant.
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function orWhereNotIn($col, array $values)
    {
        $before = \count($this->wheres);
        $this->whereNotIn($col, $values);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * OR WHERE NOT BETWEEN variant.
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @return $this
     */
    public function orWhereNotBetween($col, $min, $max)
    {
        $before = \count($this->wheres);
        $this->whereNotBetween($col, $min, $max);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * OR WHERE RAW variant.
     * @param string $rawSql
     * @param array $bindings
     * @return $this
     */
    public function orWhereRaw($rawSql, array $bindings = array())
    {
        return $this->whereRaw($rawSql, $bindings, 'OR');
    }

    /**
     * WHERE BETWEEN COLUMNS.
     * @param string $columnumn
     * @param string $minColumn
     * @param string $maxColumn
     * @return $this
     */
    public function whereBetweenColumns($column, $minColumn, $maxColumn)
    {
        $qid = $this->grammar->quoteIdentifier($column);
        $minQid = $this->grammar->quoteIdentifier($minColumn);
        $maxQid = $this->grammar->quoteIdentifier($maxColumn);
        
        $this->wheres[] = array(
            'type' => 'between_columns',
            'boolean' => 'AND',
            'sql' => "{$qid} BETWEEN {$minQid} AND {$maxQid}",
            'bindings' => array()
        );
        return $this;
    }

    /**
     * WHERE VALUE BETWEEN COLUMNS.
     * @param mixed $value
     * @param string $minColumn
     * @param string $maxColumn
     * @return $this
     */
    public function whereValueBetween($value, $minColumn, $maxColumn)
    {
        $minQid = $this->grammar->quoteIdentifier($minColumn);
        $maxQid = $this->grammar->quoteIdentifier($maxColumn);
        
        $this->wheres[] = array(
            'type' => 'value_between_columns',
            'boolean' => 'AND',
            'sql' => "? BETWEEN {$minQid} AND {$maxQid}",
            'bindings' => array($value)
        );
        return $this;
    }
}
