<?php
namespace System\Database\Query\Traits;

/** HAVING helpers. */
trait HavingTrait
{
    /**
     * Basic HAVING raw expression (AND-ed).
     * @param string $rawSql
     * @return $this
     */
    public function havingRaw($rawSql)
    {
        $this->havings[] = (string)$rawSql;
        return $this;
    }

    /**
     * HAVING column OP value (simplified).
     * @param string $col
     * @param mixed  $val
     * @param string $op
     * @return $this
     */
    public function having($col, $val, $op = '=')
    {
        $op  = $this->normalizeOp($op);
        $qid = $this->grammar->quoteIdentifier($col);
        $this->havings[] = $qid.' '.$op.' '.$this->grammar->parameterize($val);
        return $this;
    }

    /**
     * OR HAVING RAW variant.
     * @param string $rawSql
     * @return $this
     */
    public function orHavingRaw($rawSql)
    {
        $this->havings[] = 'OR ' . (string)$rawSql;
        return $this;
    }
}
