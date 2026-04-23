<?php
namespace System\Database\Schema;

use System\Database\Schema\Definitions\ColumnDefinition;
use System\Database\Schema\Definitions\IndexDefinition;
use System\Database\Schema\Definitions\ForeignKeyDefinition;

/**
 * TableBlueprint
 *
 * Purpose:
 *   Collect columns, indexes, foreign keys and alteration ops for a table.
 *   Dialect compilers read this blueprint to generate SQL.
 *
 * Construction:
 *   internal by Schema driver, not by user directly.
 *
 * Output:
 *   - arrays of ColumnDefinition/IndexDefinition/ForeignKeyDefinition and ops.
 */
final class TableBlueprint
{
    /** @var string */
    private $table;
    /** @var string 'create'|'alter' */
    private $mode;

    /** @var array<int,ColumnDefinition> */
    private $columns = array();
    /** @var array<int,IndexDefinition> */
    private $indexes = array();
    /** @var array<int,ForeignKeyDefinition> */
    private $foreigns = array();

    // Alter ops
    /** @var array<int,ColumnDefinition> */
    private $addColumns = array();
    /** @var array<int,ColumnDefinition> */
    private $modifyColumns = array();
    /** @var array<int,string> columns to drop */
    private $dropColumns = array();
    /** @var array<int,IndexDefinition> */
    private $addIndexes = array();
    /** @var array<int,string> index names to drop */
    private $dropIndexes = array();
    /** @var array<int,ForeignKeyDefinition> */
    private $addForeigns = array();
    /** @var array<int,string> fk names to drop */
    private $dropForeigns = array();

    /** @var array<int,string> check constraints */
    private $checkConstraints = array();

    /** @var array<int,array{from: string, to: string}> column renames */
    private $renameColumns = array();

    /** @var array<string,mixed> table options (engine, charset, collate, comment, autoincrement...) */
    private $options = array();

    public function __construct($table, $mode = 'create')
    {
        $this->table = (string)$table;
        $this->mode  = $mode === 'alter' ? 'alter' : 'create';
    }

    /** Get table name. Output: string */
    public function table() { return $this->table; }

    /** Get mode. Output: 'create'|'alter' */
    public function mode()  { return $this->mode; }

    /** Set table options (engine, charset, collate...). Output: $this */
    public function setOptions(array $opts) { $this->options = $opts; return $this; }

    /** Get table options. Output: array */
    public function options() { return $this->options; }

    /** Set a single table option. Output: $this */
    public function option($key, $value) { $this->options[$key] = $value; return $this; }

    /* ==================== Column helpers (portable) ==================== */

    /** @return ColumnDefinition */
    public function increments($name)
    {
        $c = new ColumnDefinition($name, 'increments');
        $c->notNull()->autoIncrement(true)->unsigned(true);
        return $this->pushColumn($c);
    }

    /**
     * Create an auto-incrementing primary key (id) column.
     * 
     * @return ColumnDefinition
     */
    public function id()
    {
        return $this->increments('id');
    }

    /** @return ColumnDefinition */
    public function bigIncrements($name)
    {
        $c = new ColumnDefinition($name, 'bigIncrements');
        $c->notNull()->autoIncrement(true)->unsigned(true);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function integer($name, $unsigned = false)
    {
        $c = new ColumnDefinition($name, 'int');
        if ($unsigned) $c->unsigned(true);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function bigInteger($name, $unsigned = false)
    {
        $c = new ColumnDefinition($name, 'bigint');
        if ($unsigned) $c->unsigned(true);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function tinyInteger($name, $unsigned = false)
    {
        $c = new ColumnDefinition($name, 'tinyint');
        if ($unsigned) $c->unsigned(true);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function boolean($name)
    {
        $c = new ColumnDefinition($name, 'bool');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function string($name, $length = 255)
    {
        $c = new ColumnDefinition($name, 'string');
        $c->length((int)$length);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function text($name)
    {
        $c = new ColumnDefinition($name, 'text');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function mediumText($name)
    {
        $c = new ColumnDefinition($name, 'mediumtext');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function longText($name)
    {
        $c = new ColumnDefinition($name, 'longtext');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function json($name)
    {
        $c = new ColumnDefinition($name, 'json');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function decimal($name, $precision = 10, $scale = 2)
    {
        $c = new ColumnDefinition($name, 'decimal');
        $c->precisionScale($precision, $scale);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function float($name)
    {
        $c = new ColumnDefinition($name, 'float');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function double($name)
    {
        $c = new ColumnDefinition($name, 'double');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function date($name)
    {
        $c = new ColumnDefinition($name, 'date');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function dateTime($name)
    {
        $c = new ColumnDefinition($name, 'datetime');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function timestamp($name)
    {
        $c = new ColumnDefinition($name, 'timestamp');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function time($name)
    {
        $c = new ColumnDefinition($name, 'time');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function year($name)
    {
        $c = new ColumnDefinition($name, 'year');
        return $this->pushColumn($c);
    }

    /**
     * Create timestamp columns (created_at, updated_at).
     * 
     * @param array $columns Column names (default: ['created_at', 'updated_at'])
     * @return void
     */
    public function timestamps(array $columns = ['created_at', 'updated_at'])
    {
        foreach ($columns as $column) {
            $this->timestamp($column)->nullable();
        }
    }

    /** @return ColumnDefinition */
    public function enum($name, array $values)
    {
        $c = new ColumnDefinition($name, 'enum');
        $c->enum($values);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function set($name, array $values)
    {
        $c = new ColumnDefinition($name, 'set');
        $c->set($values);
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function point($name)
    {
        $c = new ColumnDefinition($name, 'point');
        return $this->pushColumn($c);
    }

    /** @return ColumnDefinition */
    public function blob($name)
    {
        $c = new ColumnDefinition($name, 'blob');
        return $this->pushColumn($c);
    }

    /* ==================== Index helpers ==================== */

    /** @return IndexDefinition */
    public function primary($columns, $name = null)
    {
        $cols = is_array($columns) ? $columns : array($columns);
        $idx = new IndexDefinition('primary', $name ?: $this->table.'_pk', $cols);
        return $this->pushIndex($idx);
    }

    /** @return IndexDefinition */
    public function unique($columns, $name = null)
    {
        $cols = is_array($columns) ? $columns : array($columns);
        $idx = new IndexDefinition('unique', $name ?: $this->table.'_'.implode('_',$cols).'_uk', $cols);
        return $this->pushIndex($idx);
    }

    /** @return IndexDefinition */
    public function index($columns, $name = null)
    {
        $cols = is_array($columns) ? $columns : array($columns);
        $idx = new IndexDefinition('index', $name ?: $this->table.'_'.implode('_',$cols).'_idx', $cols);
        return $this->pushIndex($idx);
    }

    /** @return IndexDefinition */
    public function fulltext($columns, $name = null)
    {
        $cols = is_array($columns) ? $columns : array($columns);
        $idx = new IndexDefinition('fulltext', $name ?: $this->table.'_'.implode('_',$cols).'_ft', $cols);
        return $this->pushIndex($idx);
    }

    /** @return IndexDefinition */
    public function spatial($columns, $name = null)
    {
        $cols = is_array($columns) ? $columns : array($columns);
        $idx = new IndexDefinition('spatial', $name ?: $this->table.'_'.implode('_',$cols).'_sp', $cols);
        return $this->pushIndex($idx);
    }

    /* ==================== Foreign key helpers ==================== */

    /** @return ForeignKeyDefinition */
    public function foreign(array $columns)
    {
        $fk = new ForeignKeyDefinition($columns);
        return $this->pushForeign($fk);
    }

    /**
     * Create a foreign key column.
     * 
     * @param string $name
     * @return ColumnDefinition
     */
    public function foreignId($name)
    {
        return $this->bigInteger($name, true);
    }

    /* ==================== Alter helpers ==================== */

    /** Mark column to be added in ALTER. Output: ColumnDefinition */
    public function addColumn($type, $name)
    {
        $c = new ColumnDefinition($name, (string)$type);
        $this->addColumns[] = $c;
        return $c;
    }

    /** Mark column to be modified in ALTER. Output: ColumnDefinition */
    public function modifyColumn($type, $name)
    {
        $c = new ColumnDefinition($name, (string)$type);
        $this->modifyColumns[] = $c;
        return $c;
    }

    /** Drop column in ALTER. Output: $this */
    public function dropColumn($name)
    {
        $this->dropColumns[] = (string)$name;
        return $this;
    }

    /** Add index in ALTER. Output: IndexDefinition */
    public function addIndex($type, $columns, $name = null)
    {
        $cols = is_array($columns) ? $columns : array($columns);
        $idx = new IndexDefinition($type, $name ?: $this->table.'_'.implode('_',$cols).'_'.$type, $cols);
        $this->addIndexes[] = $idx;
        return $idx;
    }

    /** Drop index by name in ALTER. Output: $this */
    public function dropIndex($name)
    {
        $this->dropIndexes[] = (string)$name;
        return $this;
    }

    /** Add foreign key in ALTER. Output: ForeignKeyDefinition */
    public function addForeign(array $columns)
    {
        $fk = new ForeignKeyDefinition($columns);
        $this->addForeigns[] = $fk;
        return $fk;
    }

    /** Drop foreign key by name in ALTER. Output: $this */
    public function dropForeign($name)
    {
        $this->dropForeigns[] = (string)$name;
        return $this;
    }

    /**
     * Add a CHECK constraint to the table.
     * 
     * @param string $expression
     * @return $this
     */
    public function check($expression)
    {
        // Store check constraint for later processing
        if (!isset($this->checkConstraints)) {
            $this->checkConstraints = [];
        }
        $this->checkConstraints[] = $expression;
        return $this;
    }

    /**
     * Mark a column for modification in ALTER TABLE.
     * This is a placeholder method - actual modification happens via modifyColumn.
     * 
     * @return ColumnDefinition
     */
    public function change()
    {
        // This is a placeholder method for Laravel compatibility
        // The actual column modification should be done via modifyColumn()
        return new ColumnDefinition('', '');
    }

    /**
     * Rename a column in ALTER TABLE.
     * 
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function renameColumn($from, $to)
    {
        if (!isset($this->renameColumns)) {
            $this->renameColumns = [];
        }
        $this->renameColumns[] = ['from' => $from, 'to' => $to];
        return $this;
    }

    /* ==================== Accessors for compiler ==================== */
    /** @return array<int,ColumnDefinition> */
    public function allColumns()  { return $this->columns; }
    /** @return array<int,IndexDefinition> */
    public function allIndexes()  { return $this->indexes; }
    /** @return array<int,ForeignKeyDefinition> */
    public function allForeigns() { return $this->foreigns; }

    /** @return array<int,ColumnDefinition> */
    public function alterAddColumns()    { return $this->addColumns; }
    /** @return array<int,ColumnDefinition> */
    public function alterModifyColumns() { return $this->modifyColumns; }
    /** @return array<int,string> */
    public function alterDropColumns()   { return $this->dropColumns; }
    /** @return array<int,IndexDefinition> */
    public function alterAddIndexes()    { return $this->addIndexes; }
    /** @return array<int,string> */
    public function alterDropIndexes()   { return $this->dropIndexes; }
    /** @return array<int,ForeignKeyDefinition> */
    public function alterAddForeigns()   { return $this->addForeigns; }
    /** @return array<int,string> */
    public function alterDropForeigns()  { return $this->dropForeigns; }

    /** @return array<int,string> */
    public function checkConstraints() { return $this->checkConstraints; }

    /** @return array<int,array{from: string, to: string}> */
    public function renameColumns() { return $this->renameColumns; }

    /* ==================== Internals ==================== */

    /** Push a column to "create" mode list. Output: ColumnDefinition */
    private function pushColumn(ColumnDefinition $c)
    {
        if ($this->mode === 'alter') {
            // in alter mode, it is safer to treat explicit adds via addColumn()
            $this->addColumns[] = $c;
        } else {
            $this->columns[] = $c;
        }
        return $c;
    }

    /** Push an index to "create" mode list. Output: IndexDefinition */
    private function pushIndex(IndexDefinition $i)
    {
        if ($this->mode === 'alter') {
            $this->addIndexes[] = $i;
        } else {
            $this->indexes[] = $i;
        }
        return $i;
    }

    /** Push a foreign key to "create" mode list. Output: ForeignKeyDefinition */
    private function pushForeign(ForeignKeyDefinition $f)
    {
        if ($this->mode === 'alter') {
            $this->addForeigns[] = $f;
        } else {
            $this->foreigns[] = $f;
        }
        return $f;
    }
}
