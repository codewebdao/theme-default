<?php
namespace System\Database;

use System\Database\Query\Builder;
use System\Database\Support\SqlExpression;
use System\Database\Debug\QueryCollector;
use System\Database\Schema\SchemaFactory;

/**
 * DB Facade
 *
 * New developers:
 *   // Builder (prefix applied automatically):
 *   DB::table('users')->where('active','=',1)->get();
 *
 *   // RAW SQL (use tableName() to get prefixed name safely):
 *   $t = DB::tableName('users');
 *   DB::select("SELECT * FROM {$t} WHERE id = ?", [$id]);
 */
final class DB
{
    /** @var DatabaseManager|null */
    private static $manager = null;

    /**
     * Initialize once during bootstrap.
     *
     * @param array $config Global DB config (simple or complex format)
     * @param \System\Libraries\Logger\LoggerInterface|null $logger
     * @return void
     */
    public static function init(array $config, $logger = null)
    {
        // Convert simple config to complex format if needed
        $config = self::normalizeConfig($config);
        self::$manager = new DatabaseManager($config, $logger);
    }

    /**
     * Convert simple database config to complex format.
     * 
     * Simple format (from Config.php):
     *   ['host' => '...', 'port' => 3306, 'dbname' => '...', ...]
     * 
     * Complex format (from Database.php):
     *   ['default' => '...', 'connections' => [...], 'nodes' => [...]]
     * 
     * @param array $config
     * @return array Normalized config in complex format
     */
    private static function normalizeConfig(array $config)
    {
        // If already in complex format (has 'connections' and 'nodes'), return as-is
        if (isset($config['connections']) && isset($config['nodes'])) {
            return $config;
        }

        // Simple format detected - convert to complex format
        $db = $config;
        
        // Extract connection details
        $host     = $db['host'] ?? 'localhost';
        $port     = $db['port'] ?? 3306;
        $dbname   = $db['dbname'] ?? '';
        $username = $db['username'] ?? '';
        $password = $db['password'] ?? '';
        $driver   = $db['driver'] ?? 'mysql';
        $charset  = $db['charset'] ?? 'utf8mb4';
        $collate  = $db['collate'] ?? 'utf8mb4_unicode_ci';
        $prefix   = $db['prefix'] ?? '';
        $slowMs   = $db['slow_ms'] ?? 500;
        $retry    = $db['retry'] ?? ['deadlock' => 1, 'lost' => 1];
        $logging  = $db['logging'] ?? [];
        $timezone = $db['timezone'] ?? '+00:00';

        // Build DSN
        $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        // Build PDO options
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_PERSISTENT         => true,
        ];

        // Add MySQL-specific init command (chỉ khi PDO MySQL driver đã load)
        if ($driver === 'mysql' && defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$charset} COLLATE {$collate}, sql_mode='STRICT_ALL_TABLES'";
        }

        // Create single node name
        $nodeName = $driver . '_single';

        // Build normalized config
        $normalized = [
            'default' => 'mysql_main',
            'connections' => [
                'mysql_main' => [
                    'driver'    => $driver,
                    'read'      => [], // Single DB - no read separation
                    'write'     => $nodeName,
                    'sticky_ms' => 0, // Not needed for single DB
                    'retry'     => $retry,
                    'prefix'    => $prefix,
                    'slow_ms'   => $slowMs,
                    'logging'   => $logging,
                ],
            ],
            'nodes' => [
                $nodeName => [
                    'dsn'      => $dsn,
                    'username' => $username,
                    'password' => $password,
                    'options'  => $options,
                    'init_sql' => [
                        "SET time_zone = '{$timezone}'",
                    ],
                    'weight' => 1,
                ],
            ],
        ];

        return $normalized;
    }

    /**
     * Get named connection or default.
     *
     * @param string|null $name
     * @return DatabaseConnection
     */
    public static function connection($name = null)
    {
        if (!self::$manager) {
            throw new \RuntimeException('DB not initialized. Call DB::init($config).');
        }
        return self::$manager->connection($name);
    }

    /**
     * Start a Query Builder for table.
     * (Builder will automatically apply prefix internally.)
     *
     * @param string $table    Unprefixed base table name (e.g. "users")
     * @param string|null $connection
     * @return Builder
     */
    public static function table($table, $connection = null)
    {
        return self::connection($connection)->table($table);
    }

    /**
     * Get a prefixed table name for RAW SQL building.
     * Safer replacement for any manual concatenation of prefixes.
     *
     * INPUT:
     *   - string $table       "users" (or "users u", "users AS u", "schema.users")
     *   - string|null $connection
     * OUTPUT:
     *   - string Prefixed name, e.g., "cms_users u" or "schema.cms_users"
     */
    public static function tableName($table, $connection = null)
    {
        return self::connection($connection)->tableName($table);
    }

    /** Backward-compat alias (optional). Prefer tableName(). */
    public static function prefixTable($table, $connection = null)
    {
        return self::tableName($table, $connection);
    }

    /**
     * Get a Schema object for the given connection (dialect-aware).
     *
     * INPUT:
     *  - string|null $connection  Connection name or null for default
     * OUTPUT:
     *  - \System\Database\Schema\BaseSchema
     */
    public static function schema($connection = null)
    {
        return SchemaFactory::make($connection);
    }

    /* ================= RAW helpers (Laravel-style) ================= */

    /** @return array<int,array<string,mixed>> */
    public static function select($sql, array $bindings = array(), $connection = null)
    {
        $res = self::connection($connection)->driver()->query($sql, $bindings);
        return is_array($res) ? $res : array();
    }

    /** @return bool */
    public static function insert($sql, array $bindings = array(), $connection = null)
    {
        return self::affectingStatement($sql, $bindings, $connection) > 0;
    }

    /** @return int affected rows */
    public static function update($sql, array $bindings = array(), $connection = null)
    {
        return self::affectingStatement($sql, $bindings, $connection);
    }

    /** @return int affected rows */
    public static function delete($sql, array $bindings = array(), $connection = null)
    {
        return self::affectingStatement($sql, $bindings, $connection);
    }

    /** @return bool */
    public static function statement($sql, array $bindings = array(), $connection = null)
    {
        return self::affectingStatement($sql, $bindings, $connection) > 0;
    }

    /** @return int affected rows (0 if n/a) */
    public static function affectingStatement($sql, array $bindings = array(), $connection = null)
    {
        $driver = self::connection($connection)->driver();
        $res = $driver->query($sql, $bindings);
        return is_int($res) ? $res : 0;
    }

    /** @return SqlExpression */
    public static function raw($sql)
    {
        return new SqlExpression((string)$sql);
    }

    /**
     * Run a callback within a transaction.
     *
     * @param callable $fn  function(DatabaseConnection): mixed
     * @param string|null $connection
     * @return mixed
     * @throws \Throwable
     */
    public static function transaction($fn, $connection = null)
    {
        return self::connection($connection)->transaction($fn);
    }

    /** Manually begin a transaction on the default (WRITE) connection. */
    public static function beginTransaction($connection = null)
    {
        return self::connection($connection)->driver()->beginTransaction();
    }

    /** Manually commit current transaction. */
    public static function commit($connection = null)
    {
        return self::connection($connection)->driver()->commit();
    }

    /** Manually roll back current transaction. */
    public static function rollBack($connection = null)
    {
        return self::connection($connection)->driver()->rollBack();
    }

    /** Enable in-memory query collector (for debug bar) */
    public static function enableQueryLog()
    {
        QueryCollector::enable();
    }

    /** Disable and clear */
    public static function disableQueryLog()
    {
        QueryCollector::disable();
        QueryCollector::flush();
    }

    /** Get current collected queries (array) */
    public static function getQueryLog()
    {
        return QueryCollector::all();
    }

    /** Clear current log entries */
    public static function flushQueryLog()
    {
        QueryCollector::flush();
    }
}
