<?php
namespace System\Database\Query\Traits;

/**
 * Like helpers.
 * - whereLike: case-sensitive/insensitive depend on collation
 * - whereILike: case-insensitive (PG native; MySQL/SQLite emulate with LOWER)
 * - whereLikeAny: OR chain across multiple columns
 */
trait LikeTrait
{
    /** @return $this */
    public function whereLike($col, $pattern)
    {
        $qid = $this->grammar->quoteIdentifier($col);
        $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"$qid LIKE ?",'bindings'=>[$pattern]];
        return $this;
    }

    /** @return $this */
    public function orWhereLike($col, $pattern)
    {
        $before = \count($this->wheres);
        $this->whereLike($col, $pattern);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * Case-insensitive LIKE (PG: ILIKE; MySQL/SQLite: LOWER(col) LIKE LOWER(?))
     * @return $this
     */
    public function whereILike($col, $pattern)
    {
        if ($this->grammar->supportsILike()) {
            $qid = $this->grammar->quoteIdentifier($col);
            $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"$qid ILIKE ?",'bindings'=>[$pattern]];
            return $this;
        }
        // Fallback
        $qid = $this->grammar->quoteIdentifier($col);
        $this->wheres[] = ['type'=>'raw','boolean'=>'AND','sql'=>"LOWER($qid) LIKE LOWER(?)",'bindings'=>[$pattern]];
        return $this;
    }

    /**
     * OR multiple LIKE across columns with the same pattern.
     * @param array<int,string> $cols
     * @param string $pattern
     * @return $this
     */
    public function whereLikeAny(array $cols, $pattern)
    {
        $parts = array(); $binds = array();
        foreach ($cols as $c) {
            $parts[] = $this->grammar->quoteIdentifier($c) . ' LIKE ?';
            $binds[] = $pattern;
        }
        $this->wheres[] = ['type'=>'group','boolean'=>'AND','sql'=>'('.\implode(' OR ', $parts).')','bindings'=>$binds];
        return $this;
    }
}
