<?php
namespace System\Database\Query\Traits;

/**
 * Cursor-based pagination (keyset) to avoid huge OFFSET scans.
 */
trait CursorTrait
{
    /**
     * Cursor paginate by a single sortable column (e.g., id or created_at).
     * @param int         $limit
     * @param mixed|null  $cursor Last seen value of $orderCol
     * @param string      $orderCol
     * @param string      $dir 'DESC'|'ASC'
     * @return array{data:array<int,array>, next_cursor:mixed|null}
     */
    public function cursorPaginate($limit, $cursor = null, $orderCol = 'id', $dir = 'DESC')
    {
        $limit = (int)$limit;
        $dir   = \strtoupper($dir)==='ASC' ? 'ASC' : 'DESC';

        if ($cursor !== null) {
            $op = $dir==='DESC' ? '<' : '>';
            $this->where($orderCol, $cursor, $op);
        }

        $this->orderBy($orderCol, $dir)->limit($limit + 1);
        $rows = $this->get();

        $next = null;
        if (\count($rows) > $limit) {
            $last = \array_pop($rows);
            $next = $last[$orderCol] ?? null;
        }
        return array('data'=>$rows, 'next_cursor'=>$next);
    }
}
