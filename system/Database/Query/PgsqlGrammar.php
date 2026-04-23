<?php
namespace System\Database\Query;

/**
 * PostgreSQL Grammar
 */
class PgsqlGrammar extends Grammar
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

    public function supportsILike()        { return true; }
    public function supportsNullsOrder()   { return true; }
    public function supportsLocking()      { return true; }
    public function supportsSkipLocked()   { return true; }
    public function shareLockKeyword()     { return 'FOR SHARE'; }
    public function supportsJson()         { return true; }  // json/jsonb
    public function supportsFullText()     { return true; }
    public function supportsCte()          { return true; }
    public function supportsCteRecursive() { return true; }
    public function supportsReturning()    { return true; }

    public function compileExplain($sql, array $bindings, array $options = array())
    {
        if (!empty($options['analyze'])) {
            $fmt = (!empty($options['format']) && \strtolower($options['format'])==='json') ? ' (ANALYZE, BUFFERS, FORMAT JSON)' : ' (ANALYZE, BUFFERS)';
            return array('EXPLAIN'.$fmt.' '.$sql, $bindings);
        }
        return array('EXPLAIN '.$sql, $bindings);
    }

    // JSON
    public function compileWhereJsonContains($builder, $column, $needle, $path = null)
    {
        // Prefer jsonb containment with @>
        $col = $this->quoteIdentifier($column);
        $json = \json_encode($needle);
        $sql = "$col::jsonb @> ?::jsonb";
        return array($sql, array($json));
    }

    public function compileWhereJsonLength($builder, $column, $len, $op, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $op  = \in_array($op, array('=','>','>=','<','<=','<>','!='), true) ? $op : '=';
        if ($path) {
            // Simplified: jsonb_array_length of first match
            $sql = "jsonb_array_length(jsonb_path_query_first($col::jsonb, ?)) $op ?";
            return array($sql, array($path, (int)$len));
        }
        $sql = "jsonb_array_length($col::jsonb) $op ?";
        return array($sql, array((int)$len));
    }

    public function compileWhereJsonDoesntContain($builder, $column, $needle, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $json = \json_encode($needle);
        if ($path) {
            $sql = "NOT ($col::jsonb @> ?::jsonb)";
            return array($sql, array($json));
        }
        $sql = "NOT ($col::jsonb @> ?::jsonb)";
        return array($sql, array($json));
    }

    public function compileWhereJsonContainsKey($builder, $column, $key, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        if ($path) {
            $sql = "jsonb_exists($col::jsonb, ?)";
            return array($sql, array($path . '.' . $key));
        }
        $sql = "jsonb_exists($col::jsonb, ?)";
        return array($sql, array('$.' . $key));
    }

    public function compileWhereJsonDoesntContainKey($builder, $column, $key, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        if ($path) {
            $sql = "NOT jsonb_exists($col::jsonb, ?)";
            return array($sql, array($path . '.' . $key));
        }
        $sql = "NOT jsonb_exists($col::jsonb, ?)";
        return array($sql, array('$.' . $key));
    }

    public function compileInsertOrIgnore($builder, array $data)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols = \array_keys($data);
        $placeholders = \array_fill(0, \count($cols), '?');
        
        $sql = "INSERT INTO {$table} (" . \implode(',', \array_map(array($this, 'quoteIdentifier'), $cols)) . ") VALUES (" . \implode(',', $placeholders) . ") ON CONFLICT DO NOTHING";
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

    // FullText (tsvector vs plainto_tsquery)
    public function compileWhereFullText($builder, array $columns, $query, array $options = array())
    {
        $cfg = !empty($options['config']) ? $options['config'] : 'simple';
        $cols = \array_map(array($this, 'quoteIdentifier'), $columns);
        // to_tsvector(config, col1 || ' ' || col2 ...) @@ plainto_tsquery(config, ?)
        $concat = \implode(" || ' ' || ", $cols);
        $sql = "to_tsvector('{$cfg}', {$concat}) @@ plainto_tsquery('{$cfg}', ?)";
        return array($sql, array($query));
    }

    public function compileLimitOffset($limit, $offset)
    {
        $limit  = (int)$limit;
        $offset = (int)$offset;
        $sql = 'LIMIT '.$limit;
        if ($offset > 0) $sql .= ' OFFSET '.$offset;
        return $sql;
    }

    // UPSERT (ON CONFLICT)
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
            $updates[] = "$q = EXCLUDED.$q";
        }

        $sql = "INSERT INTO {$table} (".\implode(',', $colsQ).") VALUES ({$place}) ON CONFLICT (".\implode(',', $conf).") DO UPDATE SET ".\implode(', ', $updates);
        $bindings = \array_values($data);
        return array($sql, $bindings);
    }
}
