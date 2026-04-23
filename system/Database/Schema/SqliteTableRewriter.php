<?php
namespace System\Database\Schema;

use System\Database\DB;

/**
 * SqliteTableRewriter
 *
 * Purpose:
 *   Build a list of SQL statements to "rewrite" a table to a new schema in SQLite:
 *     - CREATE TABLE __tmp__
 *     - COPY data (intersection columns)
 *     - DROP old
 *     - RENAME __tmp__ -> old
 *     - Recreate indexes/foreign keys if needed
 *
 * Why:
 *   SQLite lacks full ALTER TABLE; complex changes require this technique.
 */
final class SqliteTableRewriter
{
    /**
     * Build rewrite SQL array.
     *
     * INPUT:
     *   - string|null $connection DB connection name
     *   - string $table          existing table name
     *   - TableBlueprint $bp     blueprint describing final wanted schema
     *   - BaseSchema $dialect    to reuse compilation helpers (column/index/foreign)
     *
     * OUTPUT:
     *   - string[] SQL statements to execute sequentially in a transaction
     */
    public static function buildRewriteSql($connection, $table, TableBlueprint $bp, BaseSchema $dialect)
    {
        $tmp = $table.'__tmp_'.substr(md5(uniqid('', true)), 0, 6);

        // 1) Lấy schema cột hiện thời
        $currentCols = self::pragmaColumns($connection, $table);
        $currentColNames = array_map(function($c){ return $c['name']; }, $currentCols);

        // 2) Cột đích từ blueprint (giống compileCreateTable của SqliteSchema)
        $newCols = $bp->allColumns();
        $newColNames = array_map(function($c){ return $c->name; }, $newCols);

        // 3) CREATE TABLE tạm với cột/constraints theo blueprint
        $createSqls = self::compileCreateForTemp($dialect, $tmp, $bp);

        // 4) Xác định giao cột để copy data
        $intersect = array_values(array_intersect($currentColNames, $newColNames));
        $copySql = null;
        if (!empty($intersect)) {
            $colsList = implode(',', array_map(function($c){ return self::qi($c); }, $intersect));
            $copySql  = 'INSERT INTO '.self::qi($tmp).' ('.$colsList.') SELECT '.$colsList.' FROM '.self::qi($table);
        }

        // 5) Lấy danh sách index cũ để recreate (chỉ plain INDEX)
        $oldIndexes = self::listIndexes($connection, $table);

        // 6) DROP + RENAME
        $finalSqls = array();
        $finalSqls = array_merge($finalSqls, $createSqls);
        if ($copySql) $finalSqls[] = $copySql;
        $finalSqls[] = 'DROP TABLE '.self::qi($table);
        $finalSqls[] = 'ALTER TABLE '.self::qi($tmp).' RENAME TO '.self::qi($table);

        // 7) Recreate indexes (UNIQUE/PK đã inline trong CREATE TABLE tạm)
        foreach ($oldIndexes as $idx) {
            if (!empty($idx['unique'])) {
                // UNIQUE đã inline ở blueprint → nếu blueprint vẫn định nghĩa, nó đã có.
                // Ở đây bỏ qua để tránh duplicate.
                continue;
            }
            // CREATE INDEX idx_name ON table(cols)
            $finalSqls[] = $idx['sql_create'];
        }

        return $finalSqls;
    }

    /** PRAGMA table_info */
    private static function pragmaColumns($connection, $table)
    {
        $rows = DB::select(
            "PRAGMA table_info(".self::qi($table).")",
            array(),
            $connection
        );
        return $rows ?: array();
    }

    /** Liệt kê index thường (bỏ UNIQUE/PK vì đã inline) */
    private static function listIndexes($connection, $table)
    {
        $rows = DB::select(
            "PRAGMA index_list(".self::qi($table).")",
            array(),
            $connection
        );
        $out = array();
        foreach ($rows as $r) {
            // r: seq, name, unique, origin, partial
            $name = $r['name'];
            $unique = (int)$r['unique'] === 1;

            // lấy cột
            $cols = DB::select(
                "PRAGMA index_info(".self::qi($name).")",
                array(),
                $connection
            );
            $colNames = array();
            foreach ($cols as $c) $colNames[] = $c['name'];

            $sql = 'CREATE '.($unique ? 'UNIQUE ' : '').'INDEX '.self::qi($name)
                 .' ON '.self::qi($table).' ('.implode(',', array_map(function($c){ return self::qi($c); }, $colNames)).')';

            $out[] = array(
                'name'       => $name,
                'unique'     => $unique,
                'columns'    => $colNames,
                'sql_create' => $sql
            );
        }
        return $out;
    }

    /** Build CREATE TABLE for tmp with blueprint (reuse SqliteSchema helpers). */
    private static function compileCreateForTemp(BaseSchema $dialect, $tmp, TableBlueprint $bp)
    {
        // Clone blueprint vào 1 bản tạm với tên bảng tạm
        $tb = new TableBlueprint($tmp);

        // copy columns
        foreach ($bp->allColumns() as $col) {
            $tb->addColumn($col->type, $col->name);
            $newCol = $tb->alterAddColumns()[count($tb->alterAddColumns())-1];
            // Copy properties
            $newCol->length = $col->length;
            $newCol->precision = $col->precision;
            $newCol->scale = $col->scale;
            $newCol->unsigned = $col->unsigned;
            $newCol->nullable = $col->nullable;
            $newCol->default = $col->default;
            $newCol->default_raw = $col->default_raw;
            $newCol->on_update_raw = $col->on_update_raw;
            $newCol->auto_increment = $col->auto_increment;
            $newCol->charset = $col->charset;
            $newCol->collation = $col->collation;
            $newCol->comment = $col->comment;
            $newCol->after = $col->after;
            $newCol->first = $col->first;
            $newCol->generated_expression = $col->generated_expression;
            $newCol->generated_stored = $col->generated_stored;
            $newCol->check = $col->check;
            $newCol->enum_values = $col->enum_values;
            $newCol->set_values = $col->set_values;
        }
        // copy indexes
        foreach ($bp->allIndexes() as $idx) {
            $tb->addIndex($idx->type, $idx->columns, $idx->name);
        }
        // copy foreigns
        foreach ($bp->allForeigns() as $fk) {
            $tb->addForeign($fk->columns);
        }
        // copy options (không có ý nghĩa lớn ở SQLite)
        foreach ($bp->options() as $k=>$v) $tb->option($k, $v);

        // Dùng dialect Sqlite để compile create
        $sqls = array();
        // call protected compileCreateTable thông qua wrapper public dryRun:
        // cách đơn giản: tạo sub-Schema class friend hoặc thêm method public. Ở đây tái lập logic đủ dùng:
        $defs = array();
        foreach ($tb->allColumns() as $c) {
            $defs[] = self::compileColumnVia($dialect, $c);
        }
        $defs = array_merge($defs, self::compileInlineIndexesVia($dialect, $tb->allIndexes()));
        foreach ($tb->allForeigns() as $fk) {
            $defs[] = self::compileForeignVia($dialect, $fk);
        }
        
        // Add CHECK constraints
        foreach ($bp->checkConstraints() as $check) {
            $defs[] = 'CHECK ('.$check.')';
        }
        
        $sqls[] = 'CREATE TABLE '.self::qi($tmp).' ('.implode(', ', $defs).')';

        // post indexes
        $sqls = array_merge($sqls, self::compilePostIndexesVia($dialect, $tmp, $tb->allIndexes()));

        return $sqls;
    }

    /* ===== Tiny proxy helpers (call private logic via BaseSchema public hooks when possible) ===== */

    private static function qi($id)
    {
        // quote identifier with double quote for SQLite
        $id = (string)$id;
        if ($id === '*') return $id;
        // Handle "schema.table" (not common in SQLite), just quote segments
        if (strpos($id, '.') !== false) {
            $parts = explode('.', $id);
            $parts = array_map(function($p){ return '"'.str_replace('"','""',$p).'"'; }, $parts);
            return implode('.', $parts);
        }
        return '"'.str_replace('"','""',$id).'"';
    }

    /** 
     * SQL literal for SQLite (used in table rewriting).
     * Handles special SQLite keywords and expressions that should NOT be quoted.
     */
    private static function literal($v)
    {
        if ($v === null) return 'NULL';
        if (is_bool($v)) return $v ? '1' : '0';
        if (is_int($v) || is_float($v)) return (string)$v;
        
        // Check if it's a special keyword/expression that should NOT be quoted
        if (is_string($v)) {
            $upper = strtoupper(trim($v));
            
            // 1. NULL keyword
            if ($upper === 'NULL') {
                return 'NULL';
            }
            
            // 2. Boolean literals (SQLite uses 0/1 but TRUE/FALSE are recognized)
            if ($upper === 'TRUE' || $upper === 'FALSE') {
                return $upper === 'TRUE' ? '1' : '0';
            }
            
            // 3. CURRENT_TIMESTAMP, CURRENT_DATE, CURRENT_TIME (SQLite built-in)
            $timeFunctions = [
                'CURRENT_TIMESTAMP', 'CURRENT_DATE', 'CURRENT_TIME',
            ];
            if (in_array($upper, $timeFunctions, true)) {
                return $upper;
            }
            
            // 4. Expression defaults - wrapped in parentheses (SQLite 3.31.0+)
            // Pattern: (FUNCTION_NAME(...)) or (expression)
            if (preg_match('/^\(.+\)$/', $v)) {
                // It's already wrapped in parentheses, check if it's a valid expression
                $inner = strtoupper(trim(substr($v, 1, -1)));
                
                // Common functions that are valid in expression defaults
                $validFunctions = [
                    'DATETIME', 'DATE', 'TIME', 'JULIANDAY', 'STRFTIME',
                    'RANDOM', 'ABS', 'HEX', 'QUOTE', 'SUBSTR', 'LENGTH',
                    'LOWER', 'UPPER', 'TRIM', 'REPLACE', 'COALESCE',
                ];
                
                foreach ($validFunctions as $func) {
                    if (strpos($inner, $func) === 0) {
                        return $v; // Return as-is (no quotes)
                    }
                }
                
                // Check for common patterns like (1+1), (column_name), etc.
                if (preg_match('/^[\w\s\+\-\*\/\(\)]+$/', $inner)) {
                    return $v; // Return as-is (no quotes)
                }
            }
        }
        
        // Default: quote the string
        return "'".str_replace("'", "''", (string)$v)."'";
    }

    // Re-implement minimal compilation identical to SqliteSchema's private methods:

    private static function compileColumnVia(BaseSchema $dialect, $c)
    {
        // tái tạo logic của SqliteSchema::compileColumn ở mức tối thiểu
        $sql = self::qi($c->name).' '.self::mapTypeVia($c);
        $sql .= $c->nullable ? '' : ' NOT NULL';
        if ($c->default_raw !== null) {
            $sql .= ' DEFAULT '.$c->default_raw;
        } elseif ($c->default !== null) {
            $sql .= ' DEFAULT '.self::literal($c->default);
        }
        if ($c->check) {
            $sql .= ' CHECK ('.$c->check.')';
        }
        return $sql;
    }

    private static function mapTypeVia($c)
    {
        switch ($c->type) {
            case 'increments':
            case 'bigIncrements': return 'INTEGER PRIMARY KEY';
            case 'int':
            case 'bigint':
            case 'tinyint':
            case 'bool':         return 'INTEGER';
            case 'decimal':
            case 'float':
            case 'double':       return 'REAL';
            case 'json':         return 'TEXT';
            case 'string':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'enum':
            case 'set':
            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'time':
            case 'year':         return 'TEXT';
            case 'point':        return 'BLOB';
            case 'blob':         return 'BLOB';
            default:             return 'TEXT';
        }
    }

    private static function compileInlineIndexesVia(BaseSchema $dialect, array $indexes)
    {
        $out = array();
        foreach ($indexes as $idx) {
            $t = strtolower($idx->type);
            if ($t === 'primary') {
                $out[] = 'PRIMARY KEY ('.self::qcols($idx->columns).')';
            } elseif ($t === 'unique') {
                $name = $idx->name ? ' CONSTRAINT '.self::qi($idx->name) : '';
                $out[] = 'UNIQUE'.$name.' ('.self::qcols($idx->columns).')';
            }
        }
        return $out;
    }

    private static function compilePostIndexesVia(BaseSchema $dialect, $table, array $indexes)
    {
        $out = array();
        foreach ($indexes as $idx) {
            $t = strtolower($idx->type);
            if ($t === 'index') {
                $name = self::qi($idx->name ?: $table.'_'.implode('_',$idx->columns).'_idx');
                $out[] = 'CREATE INDEX '.$name.' ON '.self::qi($table).' ('.self::qcols($idx->columns).')';
            }
        }
        return $out;
    }

    private static function compileForeignVia(BaseSchema $dialect, $fk)
    {
        $name = $fk->name !== '' ? ' CONSTRAINT '.self::qi($fk->name) : '';
        $sql  = $name.' FOREIGN KEY ('.self::qcols($fk->columns).') REFERENCES '.self::qi($fk->ref_table)
              .' ('.self::qcols($fk->ref_columns).')';
        if ($fk->on_delete) $sql .= ' ON DELETE '.strtoupper(self::fkAction($fk->on_delete));
        if ($fk->on_update) $sql .= ' ON UPDATE '.strtoupper(self::fkAction($fk->on_update));
        return $sql;
    }

    private static function fkAction($a)
    {
        $a = strtolower(trim((string)$a));
        switch ($a) {
            case 'cascade':     return 'cascade';
            case 'restrict':    return 'restrict';
            case 'set null':    return 'set null';
            case 'no action':   return 'no action';
            case 'set default': return 'set default';
            default:            return 'no action';
        }
    }

    private static function qcols(array $cols)
    {
        $q = array();
        foreach ($cols as $c) $q[] = self::qi($c);
        return implode(',', $q);
    }
}
