<?php
namespace System\Database\Query\Traits;

/** Column-to-column comparisons. */
trait ColumnCompareTrait
{
    /**
     * WHERE left OP right (both are columns).
     * @param string $left
     * @param string $right
     * @param string $op default '='
     * @return $this
     */
    public function whereColumn($left, $right, $op = '=')
    {
        $op = $this->normalizeOp($op);
        $this->wheres[] = [
            'type'=>'col','boolean'=>'AND',
            'sql'=>$this->grammar->quoteIdentifier($left)." {$op} ".$this->grammar->quoteIdentifier($right),
            'bindings'=>[]
        ];
        return $this;
    }
}
