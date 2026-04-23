<?php
namespace System\Database\Schema\Definitions;

/**
 * ForeignKeyDefinition
 *
 * Purpose:
 *   Describe a foreign key constraint in a portable way.
 *
 * Example:
 *   $t->foreign(['user_id'])->references('users',['id'])->onDelete('cascade');
 */
final class ForeignKeyDefinition
{
    /** @var string */
    public $name = '';
    /** @var array<int,string> */
    public $columns = array();
    /** @var string */
    public $ref_table = '';
    /** @var array<int,string> */
    public $ref_columns = array('id');
    /** @var string|null 'cascade'|'restrict'|'set null'|'no action'|'set default' */
    public $on_delete = null;
    /** @var string|null same as on_delete */
    public $on_update = null;

    /** @var bool PG: DEFERRABLE */
    public $deferrable = false;
    /** @var bool PG: INITIALLY DEFERRED */
    public $initially_deferred = false;

    public function __construct(array $columns)
    {
        $this->columns = array_values($columns);
    }

    /** Name of constraint. Output: $this */
    public function name($name) { $this->name = (string)$name; return $this; }

    /** Set referenced table/columns. Output: $this */
    public function references($table, array $cols = array('id'))
    { $this->ref_table=(string)$table; $this->ref_columns=array_values($cols); return $this; }

    /** ON DELETE action. Output: $this */
    public function onDelete($action) { $this->on_delete = strtolower((string)$action); return $this; }

    /** ON UPDATE action. Output: $this */
    public function onUpdate($action) { $this->on_update = strtolower((string)$action); return $this; }

    /** PG: DEFERRABLE. Output: $this */
    public function deferrable($flag = true) { $this->deferrable=(bool)$flag; return $this; }

    /** PG: INITIALLY DEFERRED. Output: $this */
    public function initiallyDeferred($flag = true) { $this->initially_deferred=(bool)$flag; return $this; }
}
