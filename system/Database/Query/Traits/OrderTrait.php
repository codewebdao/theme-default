<?php
namespace System\Database\Query\Traits;

/** Ordering helpers including NULLS LAST emulation. */
trait OrderTrait
{
    /**
     * ORDER BY with NULLS LAST (grammar-aware).
     * @param string $column
     * @param string $dir 'ASC'|'DESC'
     * @return $this
     */
    public function orderByNullsLast($column, $dir = 'ASC')
    {
        $dir = \strtoupper($dir)==='DESC' ? 'DESC' : 'ASC';
        $col = $this->grammar->quoteIdentifier($column);

        if ($this->grammar->supportsNullsOrder()) {
            $this->orders[] = ['col'=>$column, 'dir'=>"$dir NULLS LAST"];
            return $this;
        }

        // Emulate: ORDER BY (col IS NULL) ASC, col ASC for ASC; flip for DESC
        if ($dir === 'ASC') {
            $this->orders[] = ['col'=>"$col IS NULL", 'dir'=>'ASC'];
            $this->orders[] = ['col'=>$column,       'dir'=>'ASC'];
        } else {
            $this->orders[] = ['col'=>"$col IS NULL", 'dir'=>'DESC'];
            $this->orders[] = ['col'=>$column,       'dir'=>'DESC'];
        }
        return $this;
    }
}
