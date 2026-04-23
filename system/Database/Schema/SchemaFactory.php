<?php
namespace System\Database\Schema;

use System\Database\DB;

/**
 * SchemaFactory
 *
 * Purpose:
 *   Return proper dialect schema compiler based on connection driver.
 *
 * INPUT: optional $connection name
 * OUTPUT: instance of MysqlSchema|PgsqlSchema|SqliteSchema
 */
final class SchemaFactory
{
    /** @return BaseSchema */
    public static function make($connection = null)
    {
        $conn = DB::connection($connection);
        $driver = method_exists($conn, 'driverName') ? $conn->driverName() : 'mysql';

        switch (strtolower($driver)) {
            case 'pgsql':
            case 'postgres':
            case 'postgresql':
                return new \System\Database\Schema\PgsqlSchema($connection);
            case 'sqlite':
                return new \System\Database\Schema\SqliteSchema($connection);
            case 'mysql':
            case 'mariadb':
            default:
                return new \System\Database\Schema\MysqlSchema($connection);
        }
    }
}
