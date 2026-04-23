<?php
namespace System\Database\Schema\Definitions;

/**
 * ColumnDefinition
 *
 * Purpose:
 *   Describe a column in a portable, driver-agnostic way. Dialect compilers
 *   (MysqlSchema/PgsqlSchema/SqliteSchema) translate this object to SQL.
 *
 * Typical usage (via Blueprint helpers):
 *   $t->string('name', 100)->notNull()->default('');
 *
 * Input:  constructed through Blueprint helpers, then fluent setters.
 * Output: consumed by dialect compiler to generate SQL fragments.
 */
final class ColumnDefinition
{
    /** @var string */
    public $name = '';
    /** @var string Portable type (string,int,bigint,datetime,json,enum,set,decimal,double,float,bool,tinyint,time,date,point,blob,text,longtext,mediumtext,year,timestamp,uuid,reference,increments, bigIncrements, etc.) */
    public $type = '';

    // Numeric/String sizing
    /** @var int|null e.g. VARCHAR length */
    public $length = null;
    /** @var int|null DECIMAL precision */
    public $precision = null;
    /** @var int|null DECIMAL scale */
    public $scale = null;

    // Attributes/constraints
    /** @var bool */
    public $unsigned = false;
    /** @var bool */
    public $nullable = true;
    /** @var mixed|null Scalar default value (converted to literal). */
    public $default = null;
    /** @var string|null Raw default expression (e.g. CURRENT_TIMESTAMP, now()) */
    public $default_raw = null;
    /** @var string|null Raw ON UPDATE expression (MySQL only; others may ignore) */
    public $on_update_raw = null;
    /** @var bool */
    public $auto_increment = false;

    // Charset/Collation (MySQL)
    /** @var string|null */
    public $charset = null;
    /** @var string|null */
    public $collation = null;

    // Comments
    /** @var string|null */
    public $comment = null;

    // Column positioning (MySQL)
    /** @var string|null column name to place this after */
    public $after = null;
    /** @var bool put column as first */
    public $first = false;

    // Generated column (computed)
    /** @var string|null SQL expression for generated value */
    public $generated_expression = null;
    /** @var bool true = STORED; false = VIRTUAL (if dialect supports) */
    public $generated_stored = true;

    // Checks/Enums
    /** @var string|null CHECK(expr) raw expression */
    public $check = null;
    /** @var array<int,string>|null Enum values */
    public $enum_values = null;
    /** @var array<int,string>|null Set values (MySQL only) */
    public $set_values = null;

    public function __construct($name, $type)
    {
        $this->name = (string)$name;
        $this->type = (string)$type;
    }

    /** Mark column as NOT NULL. Output: $this for chaining. */
    public function notNull() { $this->nullable = false; return $this; }

    /** Mark column as NULLABLE. Output: $this */
    public function nullable() { $this->nullable = true; return $this; }

    /** Set length (VARCHAR, etc.). Output: $this */
    public function length($n) { $this->length = (int)$n; return $this; }

    /** Set precision/scale (DECIMAL). Output: $this */
    public function precisionScale($precision, $scale)
    { $this->precision=(int)$precision; $this->scale=(int)$scale; return $this; }

    /** Unsigned (numeric). Output: $this */
    public function unsigned($flag = true) { $this->unsigned = (bool)$flag; return $this; }

    /** Default scalar value (safe). Output: $this */
    public function default($val) { $this->default = $val; $this->default_raw = null; return $this; }

    /** Default raw expression (CURRENT_TIMESTAMP/now()). Output: $this */
    public function defaultRaw($expr) { $this->default_raw = (string)$expr; $this->default = null; return $this; }

    /** ON UPDATE raw expression (MySQL). Output: $this */
    public function onUpdateRaw($expr) { $this->on_update_raw = (string)$expr; return $this; }

    /** Auto-increment. Output: $this */
    public function autoIncrement($flag = true) { $this->auto_increment = (bool)$flag; return $this; }

    /** Charset (MySQL). Output: $this */
    public function charset($name) { $this->charset = (string)$name; return $this; }

    /** Collation (MySQL). Output: $this */
    public function collation($name) { $this->collation = (string)$name; return $this; }

    /** Comment text. Output: $this */
    public function comment($text) { $this->comment = (string)$text; return $this; }

    /** Place AFTER another column (MySQL). Output: $this */
    public function after($col) { $this->after = (string)$col; return $this; }

    /** Place as FIRST column (MySQL). Output: $this */
    public function first($flag = true) { $this->first = (bool)$flag; return $this; }

    /** Generated (computed) column. Output: $this */
    public function generated($expression, $stored = true)
    { $this->generated_expression=(string)$expression; $this->generated_stored=(bool)$stored; return $this; }

    /** CHECK constraint expression. Output: $this */
    public function check($expr) { $this->check = (string)$expr; return $this; }

    /** Enum values. Output: $this */
    public function enum(array $vals) { $this->enum_values = array_values($vals); return $this; }

    /** Set values (MySQL). Output: $this */
    public function set(array $vals) { $this->set_values = array_values($vals); return $this; }

    /**
     * Mark this column as a foreign key constraint.
     * This is a placeholder method - actual constraint creation happens via TableBlueprint.
     * 
     * @param string $table
     * @return $this
     */
    public function constrained($table)
    {
        // This is a placeholder - the actual foreign key constraint
        // will be created by the TableBlueprint when the column is processed
        $this->comment = "Foreign key to {$table}";
        return $this;
    }
}
