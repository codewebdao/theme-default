<?php
namespace System\Database\Query;

use System\Database\Support\SqlExpression;

/**
 * Grammar (abstract)
 * Dialect adapter for compiling SQL from Builder state.
 *
 * Implementations must at least provide:
 *  - quoteIdentifier()
 *  - (optionally) override supports* feature flags and compile* methods.
 */
abstract class Grammar
{
    /* ====== IDENTIFIERS ====== */

    /**
     * Quote an identifier (table/column/schema.table or with alias).
     * Input:
     *  - string $name
     * Output:
     *  - string quoted identifier (dialect-specific)
     */
    abstract public function quoteIdentifier($name);

    /* ====== CAPABILITY FLAGS ====== */

    /** Whether dialect supports ILIKE operator (case-insensitive like). */
    public function supportsILike()        { return false; }
    /** Whether dialect supports ORDER BY ... NULLS FIRST/LAST syntax. */
    public function supportsNullsOrder()   { return false; }
    /** Whether dialect supports SELECT ... FOR UPDATE|SHARE. */
    public function supportsLocking()      { return false; }
    /** Whether dialect supports SKIP LOCKED. */
    public function supportsSkipLocked()   { return false; }
    /** Share lock keyword (MySQL legacy uses LOCK IN SHARE MODE). */
    public function shareLockKeyword()     { return 'FOR SHARE'; }

    /** Whether dialect has native JSON functions/ops used here. */
    public function supportsJson()         { return false; }
    /** Whether dialect has native full text search used here. */
    public function supportsFullText()     { return false; }
    /** Whether dialect supports CTE (WITH). */
    public function supportsCte()          { return false; }
    /** Whether dialect supports recursive CTE. */
    public function supportsCteRecursive() { return false; }
    /** Whether dialect supports DML ... RETURNING. */
    public function supportsReturning()    { return false; }

    /* ====== PARAMETERIZATION ====== */

    /**
     * Parameterize a single value for inline contexts (e.g., simple HAVING usage).
     * Default: return '?' so upper layer can still bind if needed.
     * Input:
     *  - mixed $val
     * Output:
     *  - string placeholder (usually '?')
     */
    public function parameterize($val)
    {
        return '?';
    }

    /* ====== LIMIT/OFFSET ====== */

    /**
     * Compile LIMIT/OFFSET clause (ANSI-ish default).
     * Input:
     *  - int $limit
     *  - int $offset
     * Output:
     *  - string SQL clause
     */
    public function compileLimitOffset($limit, $offset)
    {
        $limit  = (int)$limit;
        $offset = (int)$offset;
        $sql = 'LIMIT '.$limit;
        if ($offset > 0) $sql .= ' OFFSET '.$offset;
        return $sql;
    }

    /* ====== EXPLAIN WRAPPER ====== */

    /**
     * Wrap a SELECT with EXPLAIN (dialect may override).
     * Input:
     *  - string $sql
     *  - array  $bindings
     *  - array  $options (dialect-specific options)
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileExplain($sql, array $bindings, array $options = array())
    {
        return array('EXPLAIN '.$sql, $bindings);
    }

    /* ====== JSON PREDICATES (override in JSON-capable dialects) ====== */

    /**
     * Compile WHERE JSON contains predicate.
     * Input:
     *  - Builder $builder
     *  - string  $column
     *  - mixed   $needle  (scalar|array will be json_encode’d)
     *  - ?string $path    (dialect-dependent path expression)
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileWhereJsonContains($builder, $column, $needle, $path = null)
    {
        throw new \LogicException('JSON not supported by this grammar.');
    }

    /**
     * Compile WHERE JSON length comparison.
     * Input:
     *  - Builder $builder
     *  - string  $column
     *  - int     $len
     *  - string  $op
     *  - ?string $path
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileWhereJsonLength($builder, $column, $len, $op, $path = null)
    {
        throw new \LogicException('JSON not supported by this grammar.');
    }

    /**
     * Compile WHERE JSON does not contain.
     * Input:
     *  - Builder $builder
     *  - string  $column
     *  - mixed   $needle
     *  - ?string $path
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileWhereJsonDoesntContain($builder, $column, $needle, $path = null)
    {
        throw new \LogicException('JSON not supported by this grammar.');
    }

    /**
     * Compile WHERE JSON contains key.
     * Input:
     *  - Builder $builder
     *  - string  $column
     *  - string  $key
     *  - ?string $path
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileWhereJsonContainsKey($builder, $column, $key, $path = null)
    {
        throw new \LogicException('JSON not supported by this grammar.');
    }

    /**
     * Compile WHERE JSON does not contain key.
     * Input:
     *  - Builder $builder
     *  - string  $column
     *  - string  $key
     *  - ?string $path
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileWhereJsonDoesntContainKey($builder, $column, $key, $path = null)
    {
        throw new \LogicException('JSON not supported by this grammar.');
    }

    /**
     * Compile INSERT OR IGNORE.
     * Input:
     *  - Builder $builder
     *  - array   $data
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileInsertOrIgnore($builder, array $data)
    {
        throw new \LogicException('INSERT OR IGNORE not supported by this grammar.');
    }

    /**
     * Compile INSERT USING subquery.
     * Input:
     *  - Builder $builder
     *  - array   $columns
     *  - Builder $subquery
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileInsertUsing($builder, array $columns, $subquery)
    {
        throw new \LogicException('INSERT USING not supported by this grammar.');
    }

    /* ====== FULLTEXT (override per dialect) ====== */

    /**
     * Compile WHERE fulltext.
     * Input:
     *  - Builder      $builder
     *  - string[]     $columns
     *  - string       $query
     *  - array        $options (dialect-specific)
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileWhereFullText($builder, array $columns, $query, array $options = array())
    {
        throw new \LogicException('FullText not supported by this grammar.');
    }

    /* ====== COMPILE SELECT (reads Builder state via getters) ====== */

    /**
     * Compile SELECT from Builder state.
     * Output:
     *  - array{0:string,1:array} [sql, bindings]
     */
    public function compileSelect($builder)
    {
        $bindings = array();
        $parts = array();

        // WITH (CTEs)
        $ctes = \method_exists($builder, 'getCtes') ? $builder->getCtes() : array();
        if (!empty($ctes)) {
            $hasRecursive = false;
            foreach ($ctes as $c) if (!empty($c['recursive'])) { $hasRecursive = true; break; }
            $parts[] = 'WITH'.($hasRecursive ? ' RECURSIVE' : '');
            $defs = array();
            foreach ($ctes as $c) {
                $defs[] = $this->quoteIdentifier($c['name']).' AS '.$c['sql'];
                foreach ($c['bindings'] as $b) $bindings[] = $b;
            }
            $parts[] = \implode(', ', $defs);
        }

        // SELECT columns
        $columns = $builder->getColumns();
        $colsSql = '*';
        if (is_array($columns)) {
            // Check if it's the default ['*']
            $isSelectAll = (count($columns) === 1 && $columns[0] === '*');
            
            if (!$isSelectAll) {
                // Build column list
                $tmp = array();
                foreach ($columns as $c) {
                    $tmp[] = ($c instanceof SqlExpression) ? (string)$c : $this->quoteIdentifier($c);
                }
                $colsSql = \implode(',', $tmp);
            }
        } elseif (is_string($columns)) {
            $colsSql = $columns;
        }

        $distinct = \method_exists($builder, 'isDistinct') && $builder->isDistinct();

        $from = $builder->getFrom();
        $sql  = \implode(' ', $parts);
        if (!empty($parts)) $sql .= ' ';
        $sql .= 'SELECT'.($distinct ? ' DISTINCT' : '').' '.$colsSql.' FROM '.$this->quoteIdentifier($from);

        // JOINs
        foreach ($builder->getJoins() as $j) {
            $sql .= ' '.$j['type'].' JOIN '.$this->quoteIdentifier($j['table'])
                 .' ON '.$this->quoteIdentifier($j['left'])
                 .' '.$j['op'].' '.$this->quoteIdentifier($j['right']);
        }

        // WHERE
        $wheres = $builder->getWheres();
        if (!empty($wheres)) {
            $sql .= ' WHERE ';
            $first = true;
            foreach ($wheres as $w) {
                if (!$first) $sql .= ' '.$w['boolean'].' ';
                $sql .= $w['sql'];
                foreach ($w['bindings'] as $b) $bindings[] = $b;
                $first = false;
            }
        }

        // GROUP BY
        $groups = $builder->getGroups();
        if (!empty($groups)) {
            $g = array();
            foreach ($groups as $c) $g[] = $this->quoteIdentifier($c);
            $sql .= ' GROUP BY '.\implode(',', $g);
        }

        // HAVING (current simple inline version)
        $havings = $builder->getHavings();
        if (!empty($havings)) {
            $sql .= ' HAVING '.\implode(' AND ', $havings);
        }

        // ORDER BY
        $orders = $builder->getOrders();
        if (!empty($orders)) {
            $o = array();
            foreach ($orders as $od) {
                // Allow prebuilt "col IS NULL" expression to pass through intact
                if (\strpos($od['col'], ' ') !== false && \stripos($od['col'], ' IS NULL') !== false) {
                    $o[] = $od['col'].' '.$od['dir'];
                } else {
                    $o[] = $this->quoteIdentifier($od['col']).' '.$od['dir'];
                }
            }
            $sql .= ' ORDER BY '.\implode(',', $o);
        }

        // LIMIT/OFFSET
        $limit = $builder->getLimit();
        if ($limit !== null) {
            $offset = $builder->getOffset() ?? 0;
            $sql .= ' '.$this->compileLimitOffset((int)$limit, (int)$offset);
        }

        // LOCK (FOR UPDATE/SHARE)
        $lock = \method_exists($builder, 'getLockClause') ? $builder->getLockClause() : null;
        if ($lock) $sql .= ' '.$lock;

        return array($sql, $bindings);
    }

    /* ====== DML ====== */

    /** @return array{0:string,1:array} */
    public function compileInsert($builder, array $data)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols  = \array_keys($data);
        $colsQ = array(); foreach ($cols as $c) $colsQ[] = $this->quoteIdentifier($c);
        $place = \implode(',', \array_fill(0, \count($cols), '?'));
        $sql = "INSERT INTO {$table} (".\implode(',', $colsQ).") VALUES ({$place})";
        return array($sql, \array_values($data));
    }

    /** @return array{0:string,1:array} */
    public function compileInsertMany($builder, array $rows)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $cols  = \array_keys($rows[0]);
        $colsQ = array(); foreach ($cols as $c) $colsQ[] = $this->quoteIdentifier($c);

        $valuesSql = array(); $bindings = array();
        foreach ($rows as $r) {
            $valuesSql[] = '('.\implode(',', \array_fill(0, \count($cols), '?')).')';
            foreach ($cols as $c) $bindings[] = \array_key_exists($c, $r) ? $r[$c] : null;
        }
        $sql = "INSERT INTO {$table} (".\implode(',', $colsQ).") VALUES ".\implode(',', $valuesSql);
        return array($sql, $bindings);
    }

    /** @return array{0:string,1:array} */
    public function compileUpdate($builder, array $data, $whereRaw, array $params)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $sets  = array(); $bindings = array();

        foreach ($data as $c => $v) {
            if ($v instanceof SqlExpression) {
                $sets[] = $this->quoteIdentifier($c).' = '.$v;
            } else {
                $sets[] = $this->quoteIdentifier($c).' = ?';
                $bindings[] = $v;
            }
        }

        $sql = "UPDATE {$table} SET ".\implode(', ', $sets);

        if ($whereRaw) {
            $sql .= " WHERE {$whereRaw}";
            $bindings = \array_merge($bindings, $params);
        } else {
            $wheres = $builder->getWheres();
            if (!empty($wheres)) {
                $sql .= ' WHERE ';
                $first = true;
                foreach ($wheres as $w) {
                    if (!$first) $sql .= ' '.$w['boolean'].' ';
                    $sql .= $w['sql'];
                    foreach ($w['bindings'] as $b) $bindings[] = $b;
                    $first = false;
                }
            }
        }

        // RETURNING (if supported & requested)
        $ret = \method_exists($builder, 'getReturning') ? $builder->getReturning() : null;
        if ($ret && $this->supportsReturning()) {
            $cols = \array_map(array($this, 'quoteIdentifier'), $ret);
            $sql .= ' RETURNING '.\implode(',', $cols);
        }

        return array($sql, $bindings);
    }

    /** @return array{0:string,1:array} */
    public function compileDelete($builder, $whereRaw, array $params)
    {
        $table = $this->quoteIdentifier($builder->getFrom());
        $sql   = "DELETE FROM {$table}";
        $bindings = array();

        if ($whereRaw) {
            $sql .= " WHERE {$whereRaw}";
            $bindings = $params;
        } else {
            $wheres = $builder->getWheres();
            if (!empty($wheres)) {
                $sql .= ' WHERE ';
                $first = true;
                foreach ($wheres as $w) {
                    if (!$first) $sql .= ' '.$w['boolean'].' ';
                    $sql .= $w['sql'];
                    foreach ($w['bindings'] as $b) $bindings[] = $b;
                    $first = false;
                }
            }
        }

        // RETURNING
        $ret = \method_exists($builder, 'getReturning') ? $builder->getReturning() : null;
        if ($ret && $this->supportsReturning()) {
            $cols = \array_map(array($this, 'quoteIdentifier'), $ret);
            $sql .= ' RETURNING '.\implode(',', $cols);
        }

        return array($sql, $bindings);
    }

    /** @return array{0:string,1:array} */
    public function compileTruncate($builder)
    {
        $tbl = $this->quoteIdentifier($builder->getFrom());
        return array('TRUNCATE TABLE '.$tbl, array());
    }

    /**
     * Compile UPSERT (dialect should override).
     * @return array{0:string,1:array}
     */
    public function compileUpsert($builder, array $data, array $uniqueBy, array $updateCols)
    {
        throw new \LogicException('Upsert not implemented for this grammar.');
    }
}
