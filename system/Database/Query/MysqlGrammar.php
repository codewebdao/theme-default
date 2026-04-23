<?php

namespace System\Database\Query;

/**
 * MySQL Grammar (aimed for MySQL 8.x; works with 5.7 except a few features)
 */
class MysqlGrammar extends Grammar
{
    /**
     * Quote identifier using backticks.
     * - Allows raw fragments (contains space or '(') to pass-through.
     * - Supports "schema.table", and "expr AS alias".
     */
    public function quoteIdentifier($name)
    {
        $name = (string)$name;
        if ($name === '*' || \strpos($name, '(') !== false || \strpos($name, ' ') !== false) {
            return $name;
        }
        if (\stripos($name, ' as ') !== false) {
            list($left, $alias) = \preg_split('/\s+as\s+/i', $name);
            return $this->quoteIdentifier($left) . ' AS ' . $this->quoteIdentifier($alias);
        }
        if (\strpos($name, '.') !== false) {
            $parts = \explode('.', $name);
            return \implode('.', \array_map(function ($p) {
                return '`' . \str_replace('`', '``', $p) . '`';
            }, $parts));
        }
        return '`' . \str_replace('`', '``', $name) . '`';
    }

    public function supportsLocking()
    {
        return true;
    }
    public function supportsSkipLocked()
    {
        return true;
    } // 8.0
    public function shareLockKeyword()
    {
        return 'LOCK IN SHARE MODE';
    } // MySQL legacy
    public function supportsJson()
    {
        return true;
    }
    public function supportsFullText()
    {
        return true;
    }
    public function supportsCte()
    {
        return true;
    } // MySQL 8
    public function supportsCteRecursive()
    {
        return true;
    }
    public function supportsReturning()
    {
        return false;
    } // keep false for compatibility

    public function compileLimitOffset($limit, $offset)
    {
        $limit  = (int)$limit;
        $offset = (int)$offset;
        if ($offset > 0) return 'LIMIT ' . $offset . ', ' . $limit;
        return 'LIMIT ' . $limit;
    }

    public function compileExplain($sql, array $bindings, array $options = array())
    {
        if (!empty($options['format']) && \strtolower($options['format']) === 'json') {
            return array('EXPLAIN FORMAT=JSON ' . $sql, $bindings);
        }
        return array('EXPLAIN ' . $sql, $bindings);
    }

    // JSON
    public function compileWhereJsonContains($builder, $column, $needle, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $json = \json_encode($needle);
        if ($path) {
            $expr = "JSON_CONTAINS($col, ?, ?)";
            return array($expr, array($json, $path));
        }
        return array("JSON_CONTAINS($col, ?)", array($json));
    }

    public function compileWhereJsonLength($builder, $column, $len, $op, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $op  = \in_array($op, array('=', '>', '>=', '<', '<=', '<>', '!='), true) ? $op : '=';
        if ($path) {
            $sql = "JSON_LENGTH($col, ?) $op ?";
            return array($sql, array($path, (int)$len));
        }
        $sql = "JSON_LENGTH($col) $op ?";
        return array($sql, array((int)$len));
    }

    public function compileWhereJsonDoesntContain($builder, $column, $needle, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        $json = \json_encode($needle);
        if ($path) {
            $expr = "NOT JSON_CONTAINS($col, ?, ?)";
            return array($expr, array($json, $path));
        }
        return array("NOT JSON_CONTAINS($col, ?)", array($json));
    }

    public function compileWhereJsonContainsKey($builder, $column, $key, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        if ($path) {
            $expr = "JSON_CONTAINS_PATH($col, 'one', ?)";
            return array($expr, array($path . '.' . $key));
        }
        $expr = "JSON_CONTAINS_PATH($col, 'one', ?)";
        return array($expr, array('$.' . $key));
    }

    public function compileWhereJsonDoesntContainKey($builder, $column, $key, $path = null)
    {
        $col = $this->quoteIdentifier($column);
        if ($path) {
            $expr = "NOT JSON_CONTAINS_PATH($col, 'one', ?)";
            return array($expr, array($path . '.' . $key));
        }
        $expr = "NOT JSON_CONTAINS_PATH($col, 'one', ?)";
        return array($expr, array('$.' . $key));
    }

    public function compileInsertOrIgnore($builder, array $data)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols = \array_keys($data);
        $placeholders = \array_fill(0, \count($cols), '?');

        $sql = "INSERT IGNORE INTO {$table} (" . \implode(',', \array_map(array($this, 'quoteIdentifier'), $cols)) . ") VALUES (" . \implode(',', $placeholders) . ")";
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

    // FullText
    public function compileWhereFullText($builder, array $columns, $query, array $options = array())
    {
        $cols = \array_map(array($this, 'quoteIdentifier'), $columns);
        $mode = 'IN BOOLEAN MODE';
        if (!empty($options['mode']) && \is_string($options['mode'])) $mode = \strtoupper($options['mode']);
        $sql = 'MATCH(' . \implode(',', $cols) . ') AGAINST (? ' . $mode . ')';
        return array($sql, array($query));
    }

    // UPSERT (ON DUPLICATE KEY UPDATE)
    public function compileUpsert($builder, array $data, array $uniqueBy, array $updateCols)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols  = \array_keys($data);
        $colsQ = array();
        foreach ($cols as $c) $colsQ[] = $this->quoteIdentifier($c);
        $place = \implode(',', \array_fill(0, \count($cols), '?'));

        // default update all except uniqueBy
        if (empty($updateCols)) {
            foreach ($cols as $c) {
                if (!\in_array($c, $uniqueBy, true)) $updateCols[] = $c;
            }
        }

        $updates = array();
        foreach ($updateCols as $c) {
            $q = $this->quoteIdentifier($c);
            $updates[] = "$q = VALUES($q)";
        }

        $sql = "INSERT INTO {$table} (" . \implode(',', $colsQ) . ") VALUES ({$place}) ON DUPLICATE KEY UPDATE " . \implode(', ', $updates);
        $bindings = \array_values($data);
        return array($sql, $bindings);
    }

    // TRUNCATE, INSERT/UPDATE/DELETE inherit base
}
