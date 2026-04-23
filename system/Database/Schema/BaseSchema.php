<?php

namespace System\Database\Schema;

use System\Database\DB;

/**
 * BaseSchema
 *
 * Purpose:
 *   Base class for Schema compilers (MySQL/PG/SQLite). Provides public API:
 *     - create($table, callable $blueprint, array $options = [])
 *     - table($table, callable $blueprint)  // ALTER
 *     - renameTable($from, $to)
 *     - renameColumn($table, $from, $to)
 *     - dropTable($table)
 *     - dropTableIfExists($table)
 *     - existsTable($table) / existsColumn($table, $column)
 *     - columns($table)   // normalized meta
 *     - dryRun($bool)
 *
 * Input: schema commands from application code.
 * Output: executes (or returns) SQL statements via DB facade.
 */
abstract class BaseSchema
{
    /** @var string Connection name */
    protected $connection;
    /** @var bool when true, do not execute SQL; return/render only */
    protected $dryRun = false;

    /**
     * @param string|null $connection A DB connection name or null for default
     */
    public function __construct($connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Enable/disable dry-run mode.
     * INPUT: bool $flag
     * OUTPUT: $this
     */
    public function dryRun($flag = true)
    {
        $this->dryRun = (bool)$flag;
        return $this;
    }

    /**
     * Create a table by describing a blueprint.
     * INPUT:
     *   - string $table
     *   - callable $callback function(TableBlueprint $tbl): void
     *   - array $options table options (engine, charset, collate, comment, autoincrement...)
     * OUTPUT:
     *   - bool|string[]  true if executed OK; or array of SQL strings if dry-run
     */
    public function create($table, $callback, array $options = array())
    {
        $bp = new TableBlueprint($table, 'create');
        $bp->setOptions($options);
        $callback($bp);

        $sqls = $this->compileCreateTable($bp);
        return $this->runMany($sqls);
    }

    /**
     * Alter a table.
     * INPUT:
     *   - string $table
     *   - callable $callback function(TableBlueprint $tbl): void
     * OUTPUT:
     *   - bool|array SQLs if dry-run
     */
    public function table($table, $callback)
    {
        $bp = new TableBlueprint($table, 'alter');
        $callback($bp);
        $sqls = $this->compileAlterTable($bp);
        return $this->runMany($sqls);
    }

    /**
     * Rename a table.
     * INPUT: $from, $to
     * OUTPUT: bool|array (dry-run SQLs)
     */
    public function renameTable($from, $to)
    {
        $sqls = $this->compileRenameTable((string)$from, (string)$to);
        return $this->runMany($sqls);
    }

    /**
     * Rename a column.
     * INPUT: $table, $from, $to
     * OUTPUT: bool|array (dry-run SQLs)
     */
    public function renameColumn($table, $from, $to)
    {
        $sqls = $this->compileRenameColumn((string)$table, (string)$from, (string)$to);
        return $this->runMany($sqls);
    }

    /**
     * Drop table.
     * INPUT: $table
     * OUTPUT: bool|array
     */
    public function dropTable($table)
    {
        $sqls = $this->compileDropTable((string)$table, false);
        return $this->runMany($sqls);
    }

    /**
     * Drop table if exists.
     * INPUT: $table
     * OUTPUT: bool|array
     */
    public function dropTableIfExists($table)
    {
        $sqls = $this->compileDropTable((string)$table, true);
        return $this->runMany($sqls);
    }

    /**
     * Alias for dropTableIfExists for backward compatibility.
     * INPUT: $table
     * OUTPUT: bool|array
     */
    public function dropIfExists($table)
    {
        return $this->dropTableIfExists($table);
    }

    /**
     * Duplicate a table (copy structure and optionally data).
     * INPUT: $originalTable, $newTable, $withData (default: true)
     * OUTPUT: bool|array (dry-run SQLs)
     */
    public function duplicateTable($originalTable, $newTable, $withData = true)
    {
        $sqls = $this->compileDuplicateTable((string)$originalTable, (string)$newTable, (bool)$withData);
        return $this->runMany($sqls);
    }

    /**
     * Create table from Model _schema() definition.
     * 
     * This method accepts the schema array format from Model::_schema():
     *   [
     *     ['type' => 'increments', 'name' => 'id', 'options' => []],
     *     ['type' => 'string', 'name' => 'name', 'options' => ['length' => 100]],
     *   ]
     * 
     * INPUT:
     *   - string $table        Table name (will be prefixed automatically)
     *   - array $schema        Schema definition array from Model::getSchema()
     *   - array $options       Table options (engine, charset, collate, etc.)
     * 
     * OUTPUT:
     *   - bool|array           true if executed OK; or array of SQL strings if dry-run
     */
    public function createFromSchema($table, array $schema, array $options = array())
    {
        // Prefix the table name
        $prefixedTable = DB::tableName($table);

        return $this->create($prefixedTable, function ($tbl) use ($schema) {
            foreach ($schema as $def) {
                $type = $def['type'] ?? 'string';
                $name = $def['name'] ?? null;
                $opts = $def['options'] ?? array();

                // Column name is required
                if (empty($name)) {
                    continue;
                }

                // Map type to TableBlueprint method
                $column = $this->mapSchemaTypeToColumn($tbl, $type, $name, $opts);

                // If type is 'primary' or 'increments', automatically set primary option
                // increments() creates auto_increment column which MUST be a key in MySQL
                $typeLower = strtolower($type);
                $isIncrementType = in_array($typeLower, ['increments', 'increment', 'bigincrements', 'bigincrement']);

                if (($typeLower === 'primary' || $isIncrementType) && !isset($opts['primary'])) {
                    $opts['primary'] = true;
                }

                // Apply options (skip auto_increment and unsigned for increments types - already set)
                $this->applyColumnOptions($column, $opts, $isIncrementType);
                $this->applyColumnIndexes($tbl, $name, $opts);
            }
        }, $options);
    }

    /**
     * Map schema type to TableBlueprint column method.
     * 
     * @param TableBlueprint $table
     * @param string $type
     * @param string $name
     * @param array $opts
     * @return \System\Database\Schema\Definitions\ColumnDefinition|null
     */
    protected function mapSchemaTypeToColumn($table, $type, $name, $opts)
    {
        $column = null;

        switch (strtolower($type)) {
            case 'increments':
            case 'increment':
                $column = $table->increments($name);
                break;

            case 'bigincrements':
            case 'bigincrement':
                $column = $table->bigIncrements($name);
                break;

            case 'primary':
                // Primary key column - create unsigned integer and mark as primary
                $unsigned = $opts['unsigned'] ?? true; // Default to unsigned for primary keys
                $column = $table->integer($name, $unsigned);
                // Primary key will be set in applyColumnIndexes via 'primary' option
                break;

            case 'integer':
            case 'int':
            case 'number':
                $unsigned = $opts['unsigned'] ?? false;
                $column = $table->integer($name, $unsigned);
                break;

            case 'biginteger':
            case 'bigint':
            case 'bignumber':
                $unsigned = $opts['unsigned'] ?? false;
                $column = $table->bigInteger($name, $unsigned);
                break;

            case 'tinyinteger':
            case 'tinyint':
                $unsigned = $opts['unsigned'] ?? false;
                $column = $table->tinyInteger($name, $unsigned);
                break;

            case 'boolean':
            case 'bool':
                $column = $table->boolean($name);
                break;

            case 'string':
            case 'varchar':
                $length = $opts['length'] ?? 255;
                $column = $table->string($name, $length);
                break;

            case 'text':
                $column = $table->text($name);
                break;

            case 'mediumtext':
                $column = $table->mediumText($name);
                break;

            case 'longtext':
                $column = $table->longText($name);
                break;

            case 'json':
                $column = $table->json($name);
                break;

            case 'decimal':
                $precision = $opts['precision'] ?? 10;
                $scale = $opts['scale'] ?? 2;
                $column = $table->decimal($name, $precision, $scale);
                break;

            case 'float':
                $column = $table->float($name);
                break;

            case 'double':
                $column = $table->double($name);
                break;

            case 'date':
                $column = $table->date($name);
                break;

            case 'datetime':
                $column = $table->dateTime($name);
                break;

            case 'timestamp':
                $column = $table->timestamp($name);
                break;

            case 'time':
                $column = $table->time($name);
                break;

            case 'year':
                $column = $table->year($name);
                break;

            case 'enum':
                $values = $opts['values'] ?? [];
                $column = $table->enum($name, $values);
                break;

            case 'set':
                $values = $opts['values'] ?? [];
                $column = $table->set($name, $values);
                break;

            case 'point':
                $column = $table->point($name);
                break;

            case 'blob':
                $column = $table->blob($name);
                break;

            default:
                // Fallback to string for unknown types
                $column = $table->string($name);
                break;
        }

        return $column;
    }

    /**
     * Apply column options to a column definition.
     * 
     * @param mixed $column
     * @param array $opts
     * @param bool $isIncrementType Whether this is an increments/bigincrements type (already has auto_increment and unsigned)
     * @return void
     */
    protected function applyColumnOptions($column, $opts, $isIncrementType = false)
    {
        if (!$column) {
            return;
        }

        // nullable
        if (isset($opts['nullable']) && $opts['nullable']) {
            $column->nullable();
        } elseif (isset($opts['null']) && $opts['null']) {
            $column->nullable();
        } elseif (!isset($opts['nullable']) && !isset($opts['null'])) {
            // Default to NOT NULL if not specified
            $column->notNull();
        }

        // default value
        if (array_key_exists('default', $opts)) {
            $defaultValue = $opts['default'];

            // Check if it's a special keyword/expression that should use defaultRaw()
            if (is_string($defaultValue)) {
                $upper = strtoupper(trim($defaultValue));

                // List of keywords/expressions that should NOT be quoted
                $rawKeywords = [
                    'NULL',
                    'TRUE',
                    'FALSE',
                    'CURRENT_TIMESTAMP',
                    'CURRENT_DATE',
                    'CURRENT_TIME',
                    'LOCALTIME',
                    'LOCALTIMESTAMP',
                    'NOW',
                    'UTC_TIMESTAMP',
                    'UTC_DATE',
                    'UTC_TIME',
                ];

                // Check exact match for keywords
                if (in_array($upper, $rawKeywords, true)) {
                    $column->defaultRaw($defaultValue);
                }
                // Check for CURRENT_TIMESTAMP with precision: CURRENT_TIMESTAMP(3)
                elseif (preg_match('/^CURRENT_TIMESTAMP\(\d+\)$/i', trim($defaultValue))) {
                    $column->defaultRaw($defaultValue);
                }
                // Check for expression defaults wrapped in parentheses: (NOW()), (UUID()), etc.
                elseif (preg_match('/^\(.+\)$/', $defaultValue)) {
                    $column->defaultRaw($defaultValue);
                }
                // Otherwise, treat as literal value (will be quoted)
                else {
                    $column->default($defaultValue);
                }
            } else {
                // Non-string values (int, bool, null) - use default()
                $column->default($defaultValue);
            }
        }

        // unsigned - skip for increments types (already set by increments()/bigIncrements())
        if (!$isIncrementType && isset($opts['unsigned']) && $opts['unsigned']) {
            $column->unsigned(true);
        }

        // auto_increment - skip for increments types (already set by increments()/bigIncrements())
        if (!$isIncrementType) {
            if (isset($opts['autoIncrement']) && $opts['autoIncrement']) {
                $column->autoIncrement(true);
            } elseif (isset($opts['auto_increment']) && $opts['auto_increment']) {
                $column->autoIncrement(true);
            }
        }

        // unique
        if (isset($opts['unique']) && $opts['unique']) {
            $column->unique();
        }

        // primary key - handled via index system in applyColumnIndexes method
        // Note: Primary keys should be defined via ['primary' => true] or ['index' => ['type' => 'primary']]

        // comment
        if (isset($opts['comment'])) {
            $column->comment($opts['comment']);
        }

        // after (column positioning)
        if (isset($opts['after'])) {
            $column->after($opts['after']);
        }
    }

    /**
     * Apply index helpers defined in schema options.
     *
     * Supported formats:
     *   ['index' => true]                     // simple non-unique index
     *   ['index' => 'custom_name']            // named non-unique index
     *   ['index' => ['type' => 'unique']]     // unique index
     *   ['index' => ['type' => 'index', 'name' => 'idx', 'columns' => ['a','b']]]
     *   ['primary' => true]                    // primary key (backward compatibility)
     *
     * @param TableBlueprint $table
     * @param string $columnName
     * @param array $opts
     * @return void
     */
    protected function applyColumnIndexes(TableBlueprint $table, $columnName, array $opts)
    {
        // Handle primary key option (backward compatibility)
        if (isset($opts['primary']) && $opts['primary']) {
            $table->primary($columnName);
            // Don't return here - allow index option to also be processed if set
        }

        if (!array_key_exists('index', $opts)) {
            return;
        }

        $config = $opts['index'];
        if ($config === false || $config === null) {
            return;
        }

        // Default single-column index
        if ($config === true) {
            $table->index($columnName);
            return;
        }

        // Named single-column index
        if (is_string($config)) {
            $table->index($columnName, $config);
            return;
        }

        if (!is_array($config)) {
            return;
        }

        $columns = $config['columns'] ?? $columnName;
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $name = $config['name'] ?? null;
        $type = strtolower($config['type'] ?? 'index');

        switch ($type) {
            case 'primary':
                $table->primary($columns, $name);
                break;
            case 'unique':
                $table->unique($columns, $name);
                break;
            case 'fulltext':
                $table->fulltext($columns, $name);
                break;
            case 'spatial':
                $table->spatial($columns, $name);
                break;
            default:
                $table->index($columns, $name);
                break;
        }
    }

    /**
     * Get table structure (DESCRIBE/SHOW COLUMNS).
     * INPUT: $table
     * OUTPUT: array Detailed table structure
     */
    public function describeTable($table)
    {
        return $this->compileDescribeTable((string)$table);
    }

    /**
     * Truncate a table (delete all rows).
     * INPUT: $table
     * OUTPUT: bool
     */
    public function truncateTable($table)
    {
        $sqls = $this->compileTruncateTable((string)$table);
        return $this->runMany($sqls);
    }

    /**
     * List all tables in database.
     * INPUT: $pattern (optional filter pattern)
     * OUTPUT: array<string> Table names
     */
    public function listTables($pattern = null)
    {
        return $this->compileListTables($pattern);
    }

    /**
     * Check if table exists (driver-specific).
     * INPUT: $table
     * OUTPUT: bool
     */
    abstract public function existsTable($table);

    /**
     * Check if column exists (driver-specific).
     * INPUT: $table, $column
     * OUTPUT: bool
     */
    abstract public function existsColumn($table, $column);

    /**
     * Get normalized columns metadata.
     * OUTPUT: array<int,array<string,mixed>>
     *   Each item sample:
     *     ['name'=>'id','type'=>'int','nullable'=>false,'default'=>null,'auto_increment'=>true,'comment'=>null]
     */
    abstract public function columns($table);

    /* ===================== Compilation hooks (dialect-specific) ===================== */

    /**
     * Compile CREATE TABLE and ancillary SQLs.
     * INPUT: TableBlueprint $bp
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileCreateTable(TableBlueprint $bp);

    /**
     * Compile ALTER TABLE SQLs (add/modify/drop cols, indexes, FKs).
     * INPUT: TableBlueprint $bp
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileAlterTable(TableBlueprint $bp);

    /**
     * Compile RENAME TABLE.
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileRenameTable($from, $to);

    /**
     * Compile RENAME COLUMN.
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileRenameColumn($table, $from, $to);

    /**
     * Compile DROP TABLE.
     * INPUT: string $table, bool $ifExists
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileDropTable($table, $ifExists);

    /**
     * Compile DUPLICATE TABLE (copy structure and optionally data).
     * INPUT: string $originalTable, string $newTable, bool $withData
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileDuplicateTable($originalTable, $newTable, $withData);

    /**
     * Compile DESCRIBE TABLE.
     * INPUT: string $table
     * OUTPUT: array Detailed table structure
     */
    abstract protected function compileDescribeTable($table);

    /**
     * Compile TRUNCATE TABLE.
     * INPUT: string $table
     * OUTPUT: array<int,string> SQL statements
     */
    abstract protected function compileTruncateTable($table);

    /**
     * Compile LIST TABLES.
     * INPUT: string|null $pattern
     * OUTPUT: array<string> Table names
     */
    abstract protected function compileListTables($pattern);

    /* ===================== Execution helper ===================== */

    /**
     * Execute multiple SQL statements or return them in dry-run mode.
     * INPUT: array $sqls
     * OUTPUT: bool|array
     */
    protected function runMany(array $sqls)
    {
        if ($this->dryRun) {
            return $sqls;
        }
        foreach ($sqls as $sql) {
            DB::statement($sql, array(), $this->connection);
        }
        return true;
    }

    /* ===================== Utility helpers (shared) ===================== */

    /**
     * Quote identifier by delegating to Grammar via DB::connection()->grammar()
     * Fallback: enclose with backticks/double-quotes if grammar not exposed.
     * INPUT: string $id
     * OUTPUT: string
     */
    protected function qi($id)
    {
        // Try to use grammar if available:
        try {
            $conn = DB::connection($this->connection);
            if (method_exists($conn, 'grammar') && $conn->grammar()) {
                return $conn->grammar()->quoteIdentifier($id);
            }
        } catch (\Throwable $e) {
        }
        // Fallback: simple backtick-quote (safe enough for MySQL-like)
        $id = str_replace('`', '``', (string)$id);
        return '`' . $id . '`';
    }

    /** Build comma-separated quoted column list. */
    protected function qcols(array $cols)
    {
        $out = array();
        foreach ($cols as $c) $out[] = $this->qi($c);
        return implode(', ', $out);
    }
}
