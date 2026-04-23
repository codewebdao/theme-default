<?php
namespace System\Database\Schema;

use System\Database\DB;
use System\Database\Schema\Definitions\ColumnDefinition;
use System\Database\Schema\Definitions\IndexDefinition;
use System\Database\Schema\Definitions\ForeignKeyDefinition;

/**
 * MysqlSchema
 *
 * Purpose:
 *   Compile TableBlueprint into MySQL SQL statements and execute via DB facade.
 *   Compatible with MySQL 5.7+ / 8.0+ (prefers 8.0 features when possible).
 *
 * Notes:
 *   - Column types map to MySQL dialect.
 *   - ENUM/SET supported.
 *   - Generated columns (VIRTUAL|STORED) supported on 5.7+.
 *   - RENAME COLUMN is available on 8.0+; for 5.7 fallback uses MODIFY (not included).
 *   - existsTable/existsColumn use information_schema.
 *
 * Public API: inherited from BaseSchema
 *   create(), table(), renameTable(), renameColumn(), dropTable(), dropTableIfExists(),
 *   existsTable(), existsColumn(), columns(), dryRun()
 */
final class MysqlSchema extends BaseSchema
{
    /* ===================== Public (introspection) ===================== */

    /**
     * Check if table exists.
     * INPUT: string $table
     * OUTPUT: bool
     */
    public function existsTable($table)
    {
        $row = DB::select(
            "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
            array($table),
            $this->connection
        );
        return isset($row[0]['c']) ? ((int)$row[0]['c'] > 0) : false;
    }

    /**
     * Check if column exists.
     * INPUT: string $table, string $column
     * OUTPUT: bool
     */
    public function existsColumn($table, $column)
    {
        $row = DB::select(
            "SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            array($table, $column),
            $this->connection
        );
        return isset($row[0]['c']) ? ((int)$row[0]['c'] > 0) : false;
    }

    /**
     * Get normalized columns metadata.
     * OUTPUT: array<int,array{name:string,type:string,nullable:bool,default:mixed,auto_increment:bool,comment:?string}>
     */
    public function columns($table)
    {
        $rows = DB::select(
            "SELECT column_name, data_type, is_nullable, column_default, extra, column_comment
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ?
             ORDER BY ordinal_position",
            array($table),
            $this->connection
        );
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'name'           => $r['column_name'],
                'type'           => strtolower($r['data_type']),
                'nullable'       => strtoupper($r['is_nullable']) === 'YES',
                'default'        => $r['column_default'],
                'auto_increment' => (strpos(strtolower((string)$r['extra']), 'auto_increment') !== false),
                'comment'        => $r['column_comment'] !== '' ? $r['column_comment'] : null,
            );
        }
        return $out;
    }

    /* ===================== Compilation (CREATE/ALTER/RENAME/DROP) ===================== */

    /**
     * Compile CREATE TABLE and ancillary SQLs.
     * INPUT: TableBlueprint $bp
     * OUTPUT: string[] SQL statements
     */
    protected function compileCreateTable(TableBlueprint $bp)
    {
        $table = $bp->table();
        $defs  = array();

        // Columns
        foreach ($bp->allColumns() as $c) {
            $defs[] = $this->compileColumn($c);
        }

        // Primary/unique indexes defined during create (from blueprint indexes)
        $inline = $this->compileInlineIndexes($bp->allIndexes());
        $defs   = array_merge($defs, $inline);

        // Foreign keys
        foreach ($bp->allForeigns() as $fk) {
            $defs[] = $this->compileForeignInline($fk);
        }

        // Check constraints
        foreach ($bp->checkConstraints() as $check) {
            $defs[] = 'CHECK ('.$check.')';
        }

        $sql = 'CREATE TABLE '.$this->qi($table).' ('.implode(', ', $defs).')';

        // Table options
        $opts = $this->compileTableOptions($bp->options());
        if ($opts !== '') $sql .= ' '.$opts;

        // Additional non-inline indexes (e.g., FULLTEXT/SPATIAL)
        $postSql = $this->compilePostCreateIndexes($table, $bp->allIndexes());

        return array_merge(array($sql), $postSql);
    }

    /**
     * Compile ALTER TABLE statements for adds/modifies/drops.
     * INPUT: TableBlueprint $bp
     * OUTPUT: string[] SQL statements
     */
    protected function compileAlterTable(TableBlueprint $bp)
    {
        $table = $bp->table();
        $alters = array();

        // ADD COLUMNS
        foreach ($bp->alterAddColumns() as $c) {
            $frag = 'ADD COLUMN '.$this->compileColumn($c, false);
            $alters[] = 'ALTER TABLE '.$this->qi($table).' '.$frag;
        }

        // MODIFY COLUMNS
        foreach ($bp->alterModifyColumns() as $c) {
            // MySQL: MODIFY COLUMN keeps name; use CHANGE COLUMN if renaming (exposed via renameColumn).
            $frag = 'MODIFY COLUMN '.$this->compileColumn($c, false);
            $alters[] = 'ALTER TABLE '.$this->qi($table).' '.$frag;
        }

        // DROP COLUMNS
        foreach ($bp->alterDropColumns() as $name) {
            $alters[] = 'ALTER TABLE '.$this->qi($table).' DROP COLUMN '.$this->qi($name);
        }

        // ADD INDEXES (non-primary/unique go post-create; here we support all types)
        foreach ($bp->alterAddIndexes() as $idx) {
            $alters[] = 'ALTER TABLE '.$this->qi($table).' ADD '.$this->compileStandaloneIndex($table, $idx);
        }

        // DROP INDEXES
        foreach ($bp->alterDropIndexes() as $name) {
            // PRIMARY KEY is special
            if (strtolower($name) === 'primary' || preg_match('/_pk$/i', $name)) {
                $alters[] = 'ALTER TABLE '.$this->qi($table).' DROP PRIMARY KEY';
            } else {
                $alters[] = 'ALTER TABLE '.$this->qi($table).' DROP INDEX '.$this->qi($name);
            }
        }

        // ADD FOREIGN KEYS
        foreach ($bp->alterAddForeigns() as $fk) {
            $alters[] = 'ALTER TABLE '.$this->qi($table).' ADD '.$this->compileForeignInline($fk);
        }

        // DROP FOREIGN KEYS
        foreach ($bp->alterDropForeigns() as $name) {
            $alters[] = 'ALTER TABLE '.$this->qi($table).' DROP FOREIGN KEY '.$this->qi($name);
        }

        // RENAME COLUMNS
        foreach ($bp->renameColumns() as $rename) {
            // For rename, we need to get the column definition to compile the new column type
            // This is a simplified version - in practice, you'd need to get the column definition
            $alters[] = 'ALTER TABLE '.$this->qi($table).' CHANGE COLUMN '.$this->qi($rename['from']).' '.$this->qi($rename['to']).' VARCHAR(255)';
        }

        // ADD CHECK CONSTRAINTS
        foreach ($bp->checkConstraints() as $check) {
            $alters[] = 'ALTER TABLE '.$this->qi($table).' ADD CHECK ('.$check.')';
        }

        return $alters;
    }

    /**
     * Compile RENAME TABLE.
     * OUTPUT: string[] SQL statements
     */
    protected function compileRenameTable($from, $to)
    {
        return array('RENAME TABLE '.$this->qi($from).' TO '.$this->qi($to));
    }

    /**
     * Compile RENAME COLUMN (MySQL 8.0+).
     * OUTPUT: string[] SQL statements
     */
    protected function compileRenameColumn($table, $from, $to)
    {
        return array('ALTER TABLE '.$this->qi($table).' RENAME COLUMN '.$this->qi($from).' TO '.$this->qi($to));
    }

    /**
     * Compile DROP TABLE / DROP TABLE IF EXISTS.
     * INPUT: string $table, bool $ifExists
     * OUTPUT: string[] SQL statements
     */
    protected function compileDropTable($table, $ifExists)
    {
        return array('DROP TABLE '.($ifExists ? 'IF EXISTS ' : '').$this->qi($table));
    }

    /* ===================== Helpers: column/index/fk/options ===================== */

    /**
     * Compile a ColumnDefinition into MySQL fragment.
     * INPUT: ColumnDefinition $c, bool $forCreate default true; when false omit "COLUMN" keyword (handled by caller).
     * OUTPUT: string
     */
    private function compileColumn(ColumnDefinition $c, $forCreate = true)
    {
            // name
            $sql = $this->qi($c->name).' '.$this->mapType($c);

            // generated column
            if ($c->generated_expression) {
                $sql .= ' AS ('.$c->generated_expression.') '.($c->generated_stored ? 'STORED' : 'VIRTUAL');
            }

            // nullability
            $sql .= $c->nullable ? ' NULL' : ' NOT NULL';

            // default
            if ($c->default_raw !== null) {
                $sql .= ' DEFAULT '.$c->default_raw;
            } elseif ($c->default !== null) {
                $sql .= ' DEFAULT '.$this->literal($c->default);
            }

            // on update CURRENT_TIMESTAMP (MySQL)
            if ($c->on_update_raw) {
                $sql .= ' ON UPDATE '.$c->on_update_raw;
            }

            // auto increment
            if ($c->auto_increment) {
                $sql .= ' AUTO_INCREMENT';
            }

            // charset/collation per column (for string/text)
            if ($c->charset)   $sql .= ' CHARACTER SET '.$c->charset;
            if ($c->collation) $sql .= ' COLLATE '.$c->collation;

            // comment
            if ($c->comment !== null && $c->comment !== '') {
                $sql .= ' COMMENT '.$this->literal($c->comment);
            }

            // position
            if (!$forCreate) {
                if ($c->first)          $sql .= ' FIRST';
                elseif ($c->after)      $sql .= ' AFTER '.$this->qi($c->after);
            }

            // check (MySQL 8.0+)
            if ($c->check) {
                $sql .= ' CHECK ('.$c->check.')';
            }

            return $sql;
    }

    /** Map portable type to MySQL type SQL. */
    private function mapType(ColumnDefinition $c)
    {
        switch ($c->type) {
            case 'increments':
                return 'INT UNSIGNED';
            case 'bigIncrements':
                return 'BIGINT UNSIGNED';
            case 'int': {
                $type = 'INT'.$this->optLength($c);
                return $c->unsigned ? $type.' UNSIGNED' : $type;
            }
            case 'bigint': {
                $type = 'BIGINT';
                return $c->unsigned ? $type.' UNSIGNED' : $type;
            }
            case 'tinyint': {
                $type = 'TINYINT'.$this->optLength($c, 1);
                return $c->unsigned ? $type.' UNSIGNED' : $type;
            }
            case 'bool':
                return 'TINYINT(1)';
            case 'string':
                $len = $c->length !== null ? $c->length : 255;
                return 'VARCHAR('.max(1,(int)$len).')';
            case 'text':        return 'TEXT';
            case 'mediumtext':  return 'MEDIUMTEXT';
            case 'longtext':    return 'LONGTEXT';
            case 'json':        return 'JSON';
            case 'decimal':
                $p = $c->precision !== null ? max(1,(int)$c->precision) : 10;
                $s = $c->scale     !== null ? max(0,(int)$c->scale)     : 0;
                return 'DECIMAL('.$p.','.$s.')';
            case 'float':   return 'FLOAT';
            case 'double':  return 'DOUBLE';
            case 'date':    return 'DATE';
            case 'datetime':return 'DATETIME';
            case 'timestamp':return 'TIMESTAMP';
            case 'time':    return 'TIME';
            case 'year':    return 'YEAR';
            case 'enum':
                $vals = $c->enum_values ? array_map(array($this,'literal'), $c->enum_values) : array("'0'");
                return 'ENUM('.implode(',', $vals).')';
            case 'set':
                $vals = $c->set_values ? array_map(array($this,'literal'), $c->set_values) : array("'0'");
                return 'SET('.implode(',', $vals).')';
            case 'point':   return 'POINT';
            case 'blob':    return 'BLOB';
            default:        return 'TEXT';
        }
    }

    /** Optional length for integer-ish. */
    private function optLength(ColumnDefinition $c, $fallback = null)
    {
        if ($c->length !== null) return '('.(int)$c->length.')';
        if ($fallback !== null)  return '('.(int)$fallback.')';
        return '';
    }

    /** 
     * SQL literal for scalar defaults/comments.
     * Handles special MySQL keywords and expressions that should NOT be quoted.
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
            
            // 2. Boolean literals (MySQL)
            if ($upper === 'TRUE' || $upper === 'FALSE') {
                return $upper === 'TRUE' ? '1' : '0';
            }
            
            // 3. CURRENT_TIMESTAMP and variants (with optional precision)
            if ($upper === 'CURRENT_TIMESTAMP' || preg_match('/^CURRENT_TIMESTAMP\(\d+\)$/', $upper)) {
                return $upper;
            }
            
            // 4. Expression defaults (MySQL 8.0.13+) - wrapped in parentheses
            // Pattern: (FUNCTION_NAME(...)) or (expression)
            if (preg_match('/^\(.+\)$/', $v)) {
                // It's already wrapped in parentheses, check if it's a valid expression
                $inner = strtoupper(trim(substr($v, 1, -1)));
                
                // Common functions that are valid in expression defaults
                $validFunctions = [
                    'NOW', 'CURRENT_DATE', 'CURRENT_TIME', 'LOCALTIME', 'LOCALTIMESTAMP',
                    'UTC_TIMESTAMP', 'UTC_DATE', 'UTC_TIME', 'UUID', 'UUID_TO_BIN',
                    'RAND', 'MD5', 'JSON_OBJECT', 'JSON_ARRAY', 'UNIX_TIMESTAMP',
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

    /** Compile inline PK/UNIQUE for CREATE TABLE. Others (INDEX/FULLTEXT/SPATIAL) go post-create. */
    private function compileInlineIndexes(array $indexes)
    {
        $out = array();
        foreach ($indexes as $idx) {
            $t = strtolower($idx->type);
            if ($t === 'primary') {
                $out[] = 'PRIMARY KEY ('.$this->qcols($idx->columns).')';
            } elseif ($t === 'unique') {
                $nm = $idx->name ? ' '.$this->qi($idx->name) : '';
                $out[] = 'UNIQUE KEY'.$nm.' ('.$this->qcols($idx->columns).')';
            }
        }
        return $out;
    }

    /** Compile CREATE INDEX after table creation for non-inline types. */
    private function compilePostCreateIndexes($table, array $indexes)
    {
        $out = array();
        foreach ($indexes as $idx) {
            $t = strtolower($idx->type);
            if ($t === 'index' || $t === 'fulltext' || $t === 'spatial') {
                // Build index type prefix (FULLTEXT/SPATIAL or none for plain index)
                $indexType = '';
                if ($t === 'fulltext') {
                    $indexType = 'FULLTEXT ';
                } elseif ($t === 'spatial') {
                    $indexType = 'SPATIAL ';
                }
                
                $out[] = 'CREATE '.$indexType.'INDEX '.$this->qi($idx->name ?: $table.'_'.implode('_',$idx->columns).'_'.$t)
                      .' ON '.$this->qi($table).' ('.$this->qcols($idx->columns).')';
            }
        }
        return $out;
    }

    /** Compile a standalone index fragment for ALTER TABLE ADD ... */
    private function compileStandaloneIndex($table, IndexDefinition $idx)
    {
        $t = strtolower($idx->type);
        $name = $this->qi($idx->name ?: $table.'_'.implode('_',$idx->columns).'_'.$t);

        if ($t === 'primary') {
            return 'PRIMARY KEY ('.$this->qcols($idx->columns).')';
        }
        if ($t === 'unique') {
            return 'UNIQUE INDEX '.$name.' ('.$this->qcols($idx->columns).')';
        }
        if ($t === 'fulltext') {
            return 'FULLTEXT INDEX '.$name.' ('.$this->qcols($idx->columns).')';
        }
        if ($t === 'spatial') {
            return 'SPATIAL INDEX '.$name.' ('.$this->qcols($idx->columns).')';
        }
        // plain index
        return 'INDEX '.$name.' ('.$this->qcols($idx->columns).')';
    }

    /** Compile FK as inline/standalone (same SQL fragment). */
    private function compileForeignInline(ForeignKeyDefinition $fk)
    {
        $name = $fk->name !== '' ? ' CONSTRAINT '.$this->qi($fk->name) : '';
        $sql  = $name.' FOREIGN KEY ('.$this->qcols($fk->columns).') REFERENCES '.$this->qi($fk->ref_table)
              .' ('.$this->qcols($fk->ref_columns).')';

        if ($fk->on_delete) $sql .= ' ON DELETE '.strtoupper($this->fkAction($fk->on_delete));
        if ($fk->on_update) $sql .= ' ON UPDATE '.strtoupper($this->fkAction($fk->on_update));

        return $sql;
    }

    /** Normalize FK action words. */
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

    /** Compile table options (ENGINE/CHARSET/COLLATE/COMMENT/AUTO_INCREMENT). */
    private function compileTableOptions(array $opts)
    {
        $frags = array();
        if (!empty($opts['engine']))     $frags[] = 'ENGINE='.$opts['engine'];
        if (!empty($opts['charset']))    $frags[] = 'DEFAULT CHARSET='.$opts['charset'];
        if (!empty($opts['collate']))    $frags[] = 'COLLATE='.$opts['collate'];
        if (!empty($opts['comment']))    $frags[] = 'COMMENT '.$this->literal($opts['comment']);
        if (!empty($opts['auto_increment'])) $frags[] = 'AUTO_INCREMENT='.(int)$opts['auto_increment'];
        return implode(' ', $frags);
    }

    /* ===================== New Methods ===================== */

    /**
     * Compile DUPLICATE TABLE for MySQL.
     * Uses: CREATE TABLE new LIKE old + INSERT INTO new SELECT * FROM old
     */
    protected function compileDuplicateTable($originalTable, $newTable, $withData)
    {
        $createSql = 'CREATE TABLE '.$this->qi($newTable).' LIKE '.$this->qi($originalTable);
        
        if ($withData) {
            $insertSql = 'INSERT INTO '.$this->qi($newTable).' SELECT * FROM '.$this->qi($originalTable);
            return array($createSql, $insertSql);
        }
        
        return array($createSql);
    }

    /**
     * Compile DESCRIBE TABLE for MySQL.
     */
    protected function compileDescribeTable($table)
    {
        return DB::select('DESCRIBE '.$this->qi($table), array(), $this->connection);
    }

    /**
     * Compile TRUNCATE TABLE for MySQL.
     */
    protected function compileTruncateTable($table)
    {
        return array('TRUNCATE TABLE '.$this->qi($table));
    }

    /**
     * Compile LIST TABLES for MySQL.
     */
    protected function compileListTables($pattern)
    {
        $sql = "SHOW TABLES";
        $params = array();
        
        if ($pattern !== null) {
            $sql .= " LIKE ?";
            $params[] = $pattern;
        }
        
        $rows = DB::select($sql, $params, $this->connection);
        
        // Extract table names from result
        $tables = array();
        foreach ($rows as $row) {
            $tables[] = reset($row); // Get first value from associative array
        }
        
        return $tables;
    }
}
