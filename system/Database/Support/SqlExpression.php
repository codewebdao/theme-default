<?php
namespace System\Database\Support;

/**
 * SqlExpression
 * A raw SQL fragment that should not be quoted/escaped as identifier.
 * Typical use: new SqlExpression('COUNT(*) AS total'), DB::raw('NOW()')
 *
 * Input:
 *  - string $sql  Raw SQL fragment
 * Output:
 *  - string when casted
 */
final class SqlExpression
{
    /** @var string */
    private $sql;

    public function __construct($sql)
    {
        $this->sql = (string)$sql;
    }

    public function __toString()
    {
        return $this->sql;
    }
}
