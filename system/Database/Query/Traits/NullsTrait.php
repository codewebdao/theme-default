<?php
namespace System\Database\Query\Traits;

/** Null checks helpers. */
trait NullsTrait
{
    /** @return $this */
    public function whereNull($col)
    {
        $this->wheres[] = array(
            'type'=>'null','boolean'=>'AND',
            'sql'=>$this->grammar->quoteIdentifier($col).' IS NULL',
            'bindings'=>array()
        );
        return $this;
    }

    /** @return $this */
    public function whereNotNull($col)
    {
        $this->wheres[] = array(
            'type'=>'notnull','boolean'=>'AND',
            'sql'=>$this->grammar->quoteIdentifier($col).' IS NOT NULL',
            'bindings'=>array()
        );
        return $this;
    }
}
