<?php
namespace System\Database\Query;

/**
 * SQLite Grammar
 */
class SqliteGrammar extends Grammar
{
    public function quoteIdentifier($name)
    {
        $name = (string)$name;
        if ($name === '*' || \strpos($name, '(') !== false || \strpos($name, ' ') !== false) return $name;
        if (\stripos($name, ' as ') !== false) {
            list($left, $alias) = \preg_split('/\s+as\s+/i', $name);
            return $this->quoteIdentifier($left).' AS '.$this->quoteIdentifier($alias);
        }
        if (\strpos($name, '.') !== false) {
            $parts = \explode('.', $name);
            return \implode('.', \array_map(function($p){ return '"'.\str_replace('"','""',$p).'"'; }, $parts));
        }
        return '"'.\str_replace('"','""',$name).'"';
    }

    public function supportsLocking()      { return false; }
    public function supportsSkipLocked()   { return false; }
    public function supportsJson()         { return true; }   // Assuming JSON1 extension
    public function supportsFullText()     { return false; }  // unless FTS5 is configured
    public function supportsCte()          { return true; }
    public function supportsCteRecursive() { return true; }
    public function supportsReturning()    { return true; }   // SQLite 3.35+

    public function compileLimitOffset($limit, $offset)
    {
        $limit  = (int)$limit;
        $offset = (int)$offset;
        $sql = 'LIMIT '.$limit;
        if ($offset > 0) $sql .= ' OFFSET '.$offset;
        return $sql;
    }

    public function compileExplain($sql, array $bindings, array $options = array())
    {
        // Use EXPLAIN QUERY PLAN for more readable info
        return array('EXPLAIN QUERY PLAN '.$sql, $bindings);
    }

    // JSON (simplified portable helpers)
    public function compileWhereJsonContains($builder, $column, $needle, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $json = \json_encode($needle);
        if ($path) {
            $sql = "json_extract($col, ?) = json(?)";
            return array($sql, array($path, $json));
        }
        $sql = "$col = json(?)";
        return array($sql, array($json));
    }

    public function compileWhereJsonLength($builder, $column, $len, $op, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $op  = \in_array($op, array('=','>','>=','<','<=','<>','!='), true) ? $op : '=';
        $fn  = $path ? "json_array_length(json_extract($col, ?))" : "json_array_length($col)";
        $sql = "$fn $op ?";
        $bindings = $path ? array($path, (int)$len) : array((int)$len);
        return array($sql, $bindings);
    }

    public function compileWhereJsonDoesntContain($builder, $column, $needle, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $json = \json_encode($needle);
        if ($path) {
            $sql = "NOT json_extract($col, ?) = ?";
            return array($sql, array($path, $json));
        }
        $sql = "NOT json_extract($col, '$') = ?";
        return array($sql, array($json));
    }

    public function compileWhereJsonContainsKey($builder, $column, $key, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        if ($path) {
            $sql = "json_extract($col, ?) IS NOT NULL";
            return array($sql, array($path . '.' . $key));
        }
        $sql = "json_extract($col, ?) IS NOT NULL";
        return array($sql, array('$.' . $key));
    }

    public function compileWhereJsonDoesntContainKey($builder, $column, $key, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        if ($path) {
            $sql = "json_extract($col, ?) IS NULL";
            return array($sql, array($path . '.' . $key));
        }
        $sql = "json_extract($col, ?) IS NULL";
        return array($sql, array('$.' . $key));
    }

    public function compileInsertOrIgnore($builder, array $data)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols = \array_keys($data);
        $placeholders = \array_fill(0, \count($cols), '?');
        
        $sql = "INSERT OR IGNORE INTO {$table} (" . \implode(',', \array_map(array($this, 'quoteIdentifier'), $cols)) . ") VALUES (" . \implode(',', $placeholders) . ")";
        $bindings = \array_values($data);
        
        return array($sql, $bindings);
    }

    public function compileInsertUsing($builder, array $columns, $subquery)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        list($subSql, $subBindings) = $subquery->compileSelect();
        
        $sql = "INSERT INTO {$table} (" . \implode(',', \array_map(array($this, 'quoteIdentifier'), $columns)) . ") {$subSql}";
        
        return array($sql, $subBindings);
    }

    public function compileUpsert($builder, array $data, array $uniqueBy, array $updateCols)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols  = \array_keys($data);
        $colsQ = array(); foreach ($cols as $c) $colsQ[] = $this->quoteIdentifier($c);
        $place = \implode(',', \array_fill(0, \count($cols), '?'));

        if (empty($updateCols)) {
            foreach ($cols as $c) {
                if (!\in_array($c, $uniqueBy, true)) $updateCols[] = $c;
            }
        }

        $conf  = \array_map(array($this, 'quoteIdentifier'), $uniqueBy);
        $updates = array();
        foreach ($updateCols as $c) {
            $q = $this->quoteIdentifier($c);
            $updates[] = "$q = excluded.$q";
        }

        $sql = "INSERT INTO {$table} (".\implode(',', $colsQ).") VALUES ({$place}) ON CONFLICT (".\implode(',', $conf).") DO UPDATE SET ".\implode(', ', $updates);
        $bindings = \array_values($data);
        return array($sql, $bindings);
    }

    public function compileTruncate($builder)
    {
        // SQLite has no TRUNCATE; fallback to DELETE
        $tbl = $this->quoteIdentifier($builder->getFrom());
        return array('DELETE FROM '.$tbl, array());
    }
}
