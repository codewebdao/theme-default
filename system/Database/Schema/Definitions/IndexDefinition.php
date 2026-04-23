<?php
namespace System\Database\Schema\Definitions;

/**
 * IndexDefinition
 *
 * Purpose:
 *   Describe an index. Dialect compilers convert to CREATE INDEX / ALTER TABLE ADD INDEX.
 *
 * Fields:
 *   - type: 'index'|'unique'|'primary'|'fulltext'|'spatial'
 *   - name: index name
 *   - columns: array of column names
 *   - using: optional index method (btree, hash, gin, gist...)
 *   - where: partial index predicate (PG/SQLite)
 *   - include: extra columns included (PG)
 *   - comment: (MySQL)
 */
final class IndexDefinition
{
    /** @var string */
    public $type = 'index';
    /** @var string */
    public $name = '';
    /** @var array<int,string> */
    public $columns = array();
    /** @var string|null */
    public $using = null;
    /** @var string|null */
    public $where = null;
    /** @var array<int,string>|null */
    public $include = null;
    /** @var string|null */
    public $comment = null;

    public function __construct($type, $name, array $columns)
    {
        $this->type = (string)$type;
        $this->name = (string)$name;
        $this->columns = array_values($columns);
    }

    /** Set index method (btree, gin, gist...). Output: $this */
    public function using($method) { $this->using = (string)$method; return $this; }

    /** Partial index (PG/SQLite). Output: $this */
    public function where($expr) { $this->where = (string)$expr; return $this; }

    /** Include columns (PG). Output: $this */
    public function include(array $cols) { $this->include = array_values($cols); return $this; }

    /** Comment (MySQL). Output: $this */
    public function comment($text) { $this->comment = (string)$text; return $this; }
}
