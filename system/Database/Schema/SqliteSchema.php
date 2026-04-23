<?php
namespace System\Database\Schema;

use System\Database\DB;
use System\Database\Schema\Definitions\ColumnDefinition;
use System\Database\Schema\Definitions\IndexDefinition;
use System\Database\Schema\Definitions\ForeignKeyDefinition;

/**
 * SqliteSchema
 *
 * Purpose:
 *   Compile TableBlueprint into SQLite SQL statements and execute via DB facade.
 *
 * Notes:
 *   - SQLite ALTER TABLE hỗ trợ hạn chế:
 *       + ADD COLUMN: OK (không DROP/MODIFY trực tiếp)
 *       + RENAME TABLE/COLUMN: OK (SQLite 3.25+)
 *     Với các thay đổi phức tạp → dùng chiến lược "table rewrite".
 *   - Kiểu dữ liệu map theo type affinity:
 *       INTEGER / REAL / NUMERIC / TEXT / BLOB.
 *   - DEFAULT/NOT NULL/CHECK/PRIMARY/UNIQUE/FOREIGN KEY hỗ trợ như SQLite cho phép.
 *   - JSON lưu TEXT (hoặc NUMERIC nếu bạn dùng json1).
 *
 * Public API: thừa kế từ BaseSchema
 *   create(), table(), renameTable(), renameColumn(), dropTable(), dropTableIfExists(),
 *   existsTable(), existsColumn(), columns(), dryRun()
 */
final class SqliteSchema extends BaseSchema
{
    /* ===================== Introspection ===================== */

    /**
     * Check if table exists.
     * INPUT: string $table
     * OUTPUT: bool
     */
    public function existsTable($table)
    {
        $rows = DB::select(
            "SELECT name FROM sqlite_master WHERE type='table' AND name = ?",
            array($table),
            $this->connection
        );
        return !empty($rows);
    }

    /**
     * Check if column exists.
     * INPUT: string $table, string $column
     * OUTPUT: bool
     */
    public function existsColumn($table, $column)
    {
        $cols = $this->columns($table);
        foreach ($cols as $c) {
            if (strcasecmp($c['name'], $column) === 0) return true;
        }
        return false;
    }

    /**
     * List normalized column metadata using PRAGMA.
     * OUTPUT: array<int,array{name,type,nullable,default,auto_increment,comment}>
     */
    public function columns($table)
    {
        $rows = DB::select(
            "PRAGMA table_info(".$this->qi($table).")",
            array(),
            $this->connection
        );
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'name'           => $r['name'],
                'type'           => strtolower($r['type']),
                'nullable'       => ((int)$r['notnull']) === 0,
                'default'        => $r['dflt_value'],
                'auto_increment' => false, // chỉ đúng nếu là PK INTEGER (rowid); bỏ qua ở đây
                'comment'        => null,
            );
        }
        return $out;
    }

    /* ===================== Compilation ===================== */

    /**
     * Compile CREATE TABLE (+ post indexes).
     * INPUT: TableBlueprint $bp
     * OUTPUT: string[] SQL statements
     */
    protected function compileCreateTable(TableBlueprint $bp)
    {
        $table = $bp->table();

        $defs = array();
        foreach ($bp->allColumns() as $c) {
            $defs[] = $this->compileColumn($c);
        }

        // Inline PK/UNIQUE/FOREIGN KEY
        $defs = array_merge($defs, $this->compileInlineIndexes($bp->allIndexes()));
        foreach ($bp->allForeigns() as $fk) {
            $defs[] = $this->compileForeignInline($fk);
        }

        // Check constraints
        foreach ($bp->checkConstraints() as $check) {
            $defs[] = 'CHECK ('.$check.')';
        }

        $sql = 'CREATE TABLE '.$this->qi($table).' ('.implode(', ', $defs).')';

        $post = array($sql);

        // Post-create indexes (plain index)
        $post = array_merge($post, $this->compilePostCreateIndexes($table, $bp->allIndexes()));

        return $post;
    }

    /**
     * Compile ALTER TABLE statements.
     * - Nếu chỉ ADD COLUMN (đơn giản) → dùng ALTER ... ADD COLUMN
     * - Nếu có MODIFY/DROP/đổi null/default/… → table rewrite
     *
     * INPUT: TableBlueprint $bp
     * OUTPUT: string[] SQL statements
     */
    protected function compileAlterTable(TableBlueprint $bp)
    {
        $table = $bp->table();

        $onlyAdds   = $bp->alterDropColumns() === array()
                   && $bp->alterModifyColumns() === array()
                   && $bp->alterDropForeigns() === array()
                   && $bp->alterDropIndexes() === array()
                   && $bp->alterAddForeigns() === array()
                   && $bp->alterAddIndexes() === array()
                   && $bp->renameColumns() === array()
                   && $bp->checkConstraints() === array();

        // Nếu chỉ add column (không đổi not null/default phức tạp) → dùng ADD COLUMN
        if ($onlyAdds && !empty($bp->alterAddColumns())) {
            $sqls = array();
            foreach ($bp->alterAddColumns() as $c) {
                // SQLite yêu cầu: ADD COLUMN <col def> (NHƯ LÚC CREATE)
                $sqls[] = 'ALTER TABLE '.$this->qi($table).' ADD COLUMN '.$this->compileColumn($c, false);
            }
            return $sqls;
        }

        // Ngược lại → rewrite
        return \System\Database\Schema\SqliteTableRewriter::buildRewriteSql($this->connection, $table, $bp, $this);
    }

    /**
     * Compile RENAME TABLE (SQLite supports).
     * OUTPUT: string[] SQL statements
     */
    protected function compileRenameTable($from, $to)
    {
        return array('ALTER TABLE '.$this->qi($from).' RENAME TO '.$this->qi($to));
    }

    /**
     * Compile RENAME COLUMN (SQLite 3.25+).
     * OUTPUT: string[] SQL statements
     */
    protected function compileRenameColumn($table, $from, $to)
    {
        return array('ALTER TABLE '.$this->qi($table).' RENAME COLUMN '.$this->qi($from).' TO '.$this->qi($to));
    }

    /**
     * Compile DROP TABLE / DROP TABLE IF EXISTS.
     * OUTPUT: string[] SQL statements
     */
    protected function compileDropTable($table, $ifExists)
    {
        return array('DROP TABLE '.($ifExists ? 'IF EXISTS ' : '').$this->qi($table));
    }

    /* ===================== Helpers ===================== */

    /**
     * Compile single column definition fragment (for CREATE/ADD).
     * INPUT: ColumnDefinition $c, bool $forCreate default true
     * OUTPUT: string
     */
    private function compileColumn(ColumnDefinition $c, $forCreate = true)
    {
        $sql = $this->qi($c->name).' '.$this->mapType($c);

        // GENERATED column: SQLite không hỗ trợ generated columns chuẩn → bỏ qua (hoặc CHECK/trigger tuỳ dự án)
        // Nullability
        $sql .= $c->nullable ? '' : ' NOT NULL';

        // DEFAULT
        if ($c->default_raw !== null) {
            $sql .= ' DEFAULT '.$c->default_raw;
        } elseif ($c->default !== null) {
            $sql .= ' DEFAULT '.$this->literal($c->default);
        }

        // PRIMARY KEY/UNIQUE có thể inline ở compileInlineIndexes (tránh double),
        // nhưng nếu bạn muốn PK đơn cột tại ColumnDefinition (type increments) bạn có thể set ở mapType.

        // CHECK
        if ($c->check) {
            $sql .= ' CHECK ('.$c->check.')';
        }

        // COMMENT: SQLite không có COMMENT; bỏ qua
        return $sql;
    }

    /** Map portable type -> SQLite affinity type. */
    private function mapType(ColumnDefinition $c)
    {
        switch ($c->type) {
            case 'increments':
            case 'bigIncrements':
                // Để có auto rowid, dùng "INTEGER PRIMARY KEY" (duy nhất) cho cột đó.
                // Nếu muốn unsigned không có ý nghĩa ở SQLite.
                return 'INTEGER PRIMARY KEY';

            case 'int':
            case 'bigint':
            case 'tinyint':
            case 'bool':
                return 'INTEGER';

            case 'decimal':
            case 'float':
            case 'double':
                return 'REAL';

            case 'json':
                return 'TEXT'; // hoặc NUMERIC nếu bật json1 extension

            case 'string':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'enum':   // emulate by TEXT + CHECK
            case 'set':    // emulate by TEXT
                return 'TEXT';

            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'time':
            case 'year':
                return 'TEXT'; // lưu ISO string 'YYYY-mm-dd ...'

            case 'point':
                return 'BLOB'; // hoặc TEXT; project-specific (Geo: cần extension)
            case 'blob':
                return 'BLOB';

            default:
                return 'TEXT';
        }
    }

    /** Inline PK/UNIQUE (CREATE TABLE). Others → CREATE INDEX sau. */
    private function compileInlineIndexes(array $indexes)
    {
        $out = array();
        foreach ($indexes as $idx) {
            $t = strtolower($idx->type);
            if ($t === 'primary') {
                $out[] = 'PRIMARY KEY ('.$this->qcols($idx->columns).')';
            } elseif ($t === 'unique') {
                $name = $idx->name ? ' CONSTRAINT '.$this->qi($idx->name) : '';
                $out[] = 'UNIQUE'.$name.' ('.$this->qcols($idx->columns).')';
            }
        }
        return $out;
    }

    /** CREATE INDEX sau khi tạo bảng (SQLite không có FULLTEXT mặc định—FTS là virtual table). */
    private function compilePostCreateIndexes($table, array $indexes)
    {
        $out = array();
        foreach ($indexes as $idx) {
            $t = strtolower($idx->type);
            if ($t === 'index') {
                $name = $this->qi($idx->name ?: $table.'_'.implode('_',$idx->columns).'_idx');
                $out[] = 'CREATE INDEX '.$name.' ON '.$this->qi($table).' ('.$this->qcols($idx->columns).')';
            }
            // UNIQUE đã inline; FULLTEXT/SPATIAL: bỏ qua (tuỳ dự án, có thể dùng FTS5/RTREE)
        }
        return $out;
    }

    /** Compile FK inline. SQLite yêu cầu PRAGMA foreign_keys=ON ở mức connection. */
    private function compileForeignInline(ForeignKeyDefinition $fk)
    {
        $name = $fk->name !== '' ? ' CONSTRAINT '.$this->qi($fk->name) : '';
        $sql  = $name.' FOREIGN KEY ('.$this->qcols($fk->columns).') REFERENCES '.$this->qi($fk->ref_table)
              .' ('.$this->qcols($fk->ref_columns).')';
        if ($fk->on_delete) $sql .= ' ON DELETE '.strtoupper($this->fkAction($fk->on_delete));
        if ($fk->on_update) $sql .= ' ON UPDATE '.strtoupper($this->fkAction($fk->on_update));
        return $sql;
    }

    private function fkAction($a)
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

    /** 
     * SQL literal for SQLite.
     * Handles special SQLite keywords and expressions that should NOT be quoted.
     */
    private function literal($v)
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

    /* ===================== New Methods ===================== */

    /**
     * Compile DUPLICATE TABLE for SQLite.
     * SQLite doesn't support CREATE TABLE LIKE, so we use CREATE TABLE AS SELECT
     */
    protected function compileDuplicateTable($originalTable, $newTable, $withData)
    {
        if ($withData) {
            // Create table with data
            $createSql = 'CREATE TABLE '.$this->qi($newTable).' AS SELECT * FROM '.$this->qi($originalTable);
            return array($createSql);
        } else {
            // Create table without data (structure only)
            $createSql = 'CREATE TABLE '.$this->qi($newTable).' AS SELECT * FROM '.$this->qi($originalTable).' WHERE 1=0';
            return array($createSql);
        }
    }

    /**
     * Compile DESCRIBE TABLE for SQLite.
     */
    protected function compileDescribeTable($table)
    {
        return DB::select('PRAGMA table_info('.$this->qi($table).')', array(), $this->connection);
    }

    /**
     * Compile TRUNCATE TABLE for SQLite.
     * SQLite doesn't support TRUNCATE, so we use DELETE FROM
     */
    protected function compileTruncateTable($table)
    {
        // SQLite doesn't support TRUNCATE, use DELETE FROM
        // Optionally reset auto-increment: DELETE FROM sqlite_sequence WHERE name = 'table'
        return array('DELETE FROM '.$this->qi($table));
    }

    /**
     * Compile LIST TABLES for SQLite.
     */
    protected function compileListTables($pattern)
    {
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
        $params = array();
        
        if ($pattern !== null) {
            $sql .= " AND name LIKE ?";
            $params[] = $pattern;
        }
        
        $rows = DB::select($sql, $params, $this->connection);
        
        // Extract table names from result
        $tables = array();
        foreach ($rows as $row) {
            $tables[] = $row['name'];
        }
        
        return $tables;
    }
}
