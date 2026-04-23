<?php
namespace System\Database\Query\Traits;

/** Full-text search helper (grammar-aware). */
trait FullTextTrait
{
    /**
     * WHERE FULLTEXT on columns matches query.
     * @param string|array $columns
     * @param string       $query
     * @param array        $options Grammar-defined (e.g., mode)
     * @return $this
     */
    public function whereFullText($columns, $query, array $options = array())
    {
        if (!$this->grammar->supportsFullText()) {
            // Fallback (optional): LIKE across columns.
            $cols = (array)$columns;
            $parts = array(); $binds = array();
            foreach ($cols as $c) { $parts[] = $this->grammar->quoteIdentifier($c).' LIKE ?'; $binds[] = '%'.$query.'%'; }
            $this->wheres[] = ['type'=>'group','boolean'=>'AND','sql'=>'('.\implode(' OR ',$parts).')','bindings'=>$binds];
            return $this;
        }
        list($sql, $bindings) = $this->grammar->compileWhereFullText($this, (array)$columns, (string)$query, $options);
        $this->wheres[] = ['type'=>'fulltext','boolean'=>'AND','sql'=>$sql,'bindings'=>$bindings];
        return $this;
    }
}
