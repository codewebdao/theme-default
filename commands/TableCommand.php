<?php

namespace Commands;

use System\Core\BaseCommand;
use System\Database\DB;

class TableCommand extends BaseCommand
{
    protected $schema;

    // Cache reflection objects to avoid recreating them
    private $reflectionCache = null;
    private $reflectionMethods = [];

    public function __construct($dbConnection = null)
    {
        parent::__construct();
        // Legacy compatibility - not used in new system
        if ($dbConnection) {
            // Ignore legacy connection, use DB facade instead
        }
    }

    /**
     * Get cached reflection method for BaseSchema
     * 
     * @throws \RuntimeException if schema is not initialized
     */
    protected function getReflectionMethod(string $methodName): \ReflectionMethod
    {
        if ($this->schema === null) {
            throw new \RuntimeException('Schema is not initialized. Call initializeDB() first.');
        }

        if ($this->reflectionCache === null) {
            $this->reflectionCache = new \ReflectionClass($this->schema);
        }

        if (!isset($this->reflectionMethods[$methodName])) {
            $method = $this->reflectionCache->getMethod($methodName);
            $method->setAccessible(true);
            $this->reflectionMethods[$methodName] = $method;
        }

        return $this->reflectionMethods[$methodName];
    }

    protected function initialize()
    {
        $this->name = 'create:table';
        $this->description = 'Create or synchronize database table from model schema';

        $this->arguments = [
            'name' => 'Name of the model/table to synchronize'
        ];

        $this->options = [
            '--force' => 'Force synchronization even if data exists',
            '--dry-run' => 'Show what would be synchronized without doing it'
        ];
    }

    public function execute(array $arguments = [], array $options = [])
    {
        // Get name from first argument (indexed array from cmd parsing)
        $name = $arguments[0] ?? null;

        if (!$name) {
            $this->output("Model/table name is required!", 'error');
            $this->output("Usage: php cmd create:table <name>", 'info');
            return;
        }

        try {
            // Initialize DB if not already initialized
            $this->initializeDB();

            // Initialize schema using DB facade
            $this->schema = DB::schema();

            // Check for dry-run option
            $dryRun = isset($options['dry-run']) || isset($options['dry_run']);
            if ($dryRun) {
                $this->schema->dryRun(true);
                $this->output("DRY RUN: Would synchronize table for model '$name'", 'info');
            }

            $this->handle($name);
            $this->output("Table synchronization completed for '$name'!", 'success');
        } catch (\Exception $e) {
            $this->output("Error synchronizing table: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Run table synchronization
     */
    public function handle(string $tableName)
    {
        $modelClass = "\\App\\Models\\" . ucfirst($tableName) . 'Model';

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class '$modelClass' not found. Please create the model first.");
        }

        // Initialize model
        $model = new $modelClass();
        // Always use UNPREFIXED name when passing to Schema builder (it will prefix internally)
        $table = method_exists($model, 'getTableUnprefix') ? $model->getTableUnprefix() : $model->getTable();
        $schema = $model->getSchema();
        if (empty($schema)) {
            throw new \Exception("Model '$modelClass' does not define a schema. Please implement _schema() method.");
        }

        // Check table and synchronize structure
        // Pass model instance so createTable() can use Model::createTable()
        $this->syncTableSchema($table, $schema, $model);

        echo "Synchronized table {$table}\n";
    }

    /**
     * Synchronize table structure with schema
     * 
     * @param string $table Table name
     * @param array $schema Schema definition
     * @param object|null $model Model instance (optional, for createTable optimization)
     */
    protected function syncTableSchema(string $table, array $schema, $model = null)
    {
        if (!$this->tableExists($table)) {
            $this->createTable($table, $schema, $model);
        } else {
            $this->updateTable($table, $schema);
        }
    }

    /**
     * Check if table exists
     */
    protected function tableExists(string $table)
    {
        // Use prefixed table name
        $prefixedTable = DB::tableName($table);
        return $this->schema->existsTable($prefixedTable);
    }

    /**
     * Create new table from schema using built-in createFromSchema().
     *
     * @param string      $table  Unprefixed table name
     * @param array       $schema Schema definition
     * @param object|null $model  Optional model (for logging)
     */
    protected function createTable(string $table, array $schema, $model = null)
    {
        $configdb = db_config();
        $charset  = $configdb['db_charset'] ?? 'utf8mb4';
        $collate  = $configdb['db_collate'] ?? 'utf8mb4_unicode_ci';

        $tableOptions = [
            'engine'  => 'InnoDB',
            'charset' => $charset,
            'collate' => $collate,
        ];

        $result = $this->schema->createFromSchema($table, $schema, $tableOptions);

        if (is_array($result)) {
            echo "Would execute the following SQL:\n";
            foreach ($result as $sql) {
                echo $sql . ";\n";
            }
        } else {
            $tableName = DB::tableName($table);
            echo "Created table {$tableName} using createFromSchema()\n";
            echo "Note: Full schema support (prefix, options, indexes, timestamps)\n";
        }
    }

    /**
     * Find model class name from table name
     * Helper method to locate model when we only have table name
     */
    protected function findModelClass(string $table): ?string
    {
        // Try common patterns
        $patterns = [
            "\\App\\Models\\" . ucfirst($table) . 'Model',
            "\\App\\Models\\" . str_replace('_', '', ucwords($table, '_')) . 'Model',
        ];

        foreach ($patterns as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Update current table with new schema.
     * Adds missing columns; modifies existing columns; does not drop/rename.
     */
    protected function updateTable(string $table, array $schema)
    {
        $prefixedTable   = DB::tableName($table);
        $existingColumns = $this->schema->columns($prefixedTable);
        $existingNames   = array_column($existingColumns, 'name');
        // Determine columns present in DB but not in new schema (to be dropped)
        $schemaNames     = array_column($schema, 'name');
        $columnsToDrop   = array_diff($existingNames, $schemaNames);

        // Detect existing primary key to avoid "Multiple primary key defined"
        $primaryExists   = $this->tableHasPrimaryKey($prefixedTable);
        $schema          = $this->normalizeSchemaForExistingPrimary($schema, $primaryExists);

        // Detect existing indexes to avoid duplicate key errors
        $existingIndexes = $this->tableIndexes($prefixedTable);
        $schema          = $this->normalizeIndexesForExisting($schema, $prefixedTable, $existingIndexes);

        try {
            $result = $this->schema->table($prefixedTable, function ($blueprint) use ($schema, $existingNames, $columnsToDrop) {
                // First, drop columns that are no longer defined in schema
                foreach ($columnsToDrop as $col) {
                    echo "Dropping column: {$col}\n";
                    $blueprint->dropColumn($col);
                }

                foreach ($schema as $def) {
                    $name    = $def['name']   ?? null;
                    $type    = $def['type']   ?? 'string';
                    $options = $def['options'] ?? [];
                    if (!$name) {
                        continue;
                    }

                    $exists = in_array($name, $existingNames, true);

                    if (!$exists) {
                        echo "Adding column: {$name}\n";
                        $this->addColumnFromSchema($blueprint, $type, $name, $options);
                    } else {
                        echo "Modifying column: {$name}\n";
                        $this->modifyColumnFromSchema($blueprint, $type, $name, $options);
                    }
                }
            });

            if (is_array($result)) {
                echo "\nWould execute the following SQL:\n";
                foreach ($result as $sql) {
                    echo $sql . ";\n";
                }
            } else {
                echo "Successfully synchronized table {$table}\n";
            }
        } catch (\Exception $e) {
            // In case of error, try to print generated SQL for debugging
            $this->schema->dryRun(true);
            try {
                $sqls = $this->schema->table($prefixedTable, function ($blueprint) use ($schema, $existingNames, $columnsToDrop) {
                    foreach ($columnsToDrop as $col) {
                        $blueprint->dropColumn($col);
                    }
                    foreach ($schema as $def) {
                        $name    = $def['name']   ?? null;
                        $type    = $def['type']   ?? 'string';
                        $options = $def['options'] ?? [];
                        if (!$name) {
                            continue;
                        }
                        $exists = in_array($name, $existingNames, true);
                        if (!$exists) {
                            $this->addColumnFromSchema($blueprint, $type, $name, $options);
                        } else {
                            $this->modifyColumnFromSchema($blueprint, $type, $name, $options);
                        }
                    }
                });
                echo "\n⚠️  Generated SQL statements (for debugging):\n";
                foreach ($sqls as $sql) {
                    echo "   " . $sql . ";\n";
                }
            } catch (\Exception $e2) {
                // ignore
            }
            throw $e;
        }
    }

    /**
     * Check if table already has a PRIMARY KEY.
     */
    protected function tableHasPrimaryKey(string $prefixedTable): bool
    {
        try {
            $rows = DB::select(
                "SELECT COUNT(*) AS c
                 FROM information_schema.table_constraints
                 WHERE table_schema = DATABASE()
                   AND table_name = ?
                   AND constraint_type = 'PRIMARY KEY'",
                [$prefixedTable]
            );
            return isset($rows[0]['c']) ? ((int)$rows[0]['c'] > 0) : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * If table already has a primary key, strip primary-related options to avoid duplicate PK errors.
     */
    protected function normalizeSchemaForExistingPrimary(array $schema, bool $primaryExists): array
    {
        if (!$primaryExists) {
            return $schema;
        }

        foreach ($schema as &$def) {
            $opts = $def['options'] ?? [];

            // Remove primary flag
            if (isset($opts['primary']) && $opts['primary']) {
                unset($opts['primary']);
            }

            // Remove primary from index config
            if (isset($opts['index'])) {
                $idx = $opts['index'];
                if (is_array($idx) && strtolower($idx['type'] ?? '') === 'primary') {
                    unset($opts['index']);
                }
            }

            $def['options'] = $opts;
        }
        unset($def);

        return $schema;
    }

    /**
     * Get existing index names for a table (lowercased).
     */
    protected function tableIndexes(string $prefixedTable): array
    {
        try {
            $rows = DB::select(
                "SELECT DISTINCT index_name
                 FROM information_schema.statistics
                 WHERE table_schema = DATABASE()
                   AND table_name = ?",
                [$prefixedTable]
            );
            return array_map(static function ($r) {
                return strtolower($r['index_name']);
            }, $rows);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Remove index instructions if the index already exists in DB.
     */
    protected function normalizeIndexesForExisting(array $schema, string $prefixedTable, array $existingIndexNames): array
    {
        $existingIndexNames = array_unique($existingIndexNames);

        foreach ($schema as &$def) {
            $name = $def['name'] ?? null;
            if (!$name) {
                continue;
            }
            $opts = $def['options'] ?? [];
            if (!array_key_exists('index', $opts)) {
                $def['options'] = $opts;
                continue;
            }

            $idx = $opts['index'];
            $desiredName = null;
            $desiredType = 'index';
            $columns     = [$name];

            if ($idx === true) {
                $desiredType = 'index';
            } elseif (is_string($idx)) {
                $desiredName = $idx;
            } elseif (is_array($idx)) {
                $desiredType = strtolower($idx['type'] ?? 'index');
                $columns     = $idx['columns'] ?? $columns;
                if (!is_array($columns)) {
                    $columns = [$columns];
                }
                $desiredName = $idx['name'] ?? null;
            }

            if (!$desiredName) {
                $desiredName = $this->defaultIndexName($prefixedTable, $columns, $desiredType);
            }

            // Skip spatial index if column is nullable (MySQL requires NOT NULL)
            if ($desiredType === 'spatial') {
                $isNullable = $opts['nullable'] ?? $opts['null'] ?? null;
                if ($isNullable === null) {
                    // default behavior in applyColumnOptions is NOT NULL if not set,
                    // but our schema often sets null => true explicitly. Only skip when explicitly nullable.
                    $isNullable = false;
                }
                if ($isNullable) {
                    unset($opts['index']);
                    $def['options'] = $opts;
                    continue;
                }
            }

            if (in_array(strtolower($desiredName), $existingIndexNames, true)) {
                unset($opts['index']); // index already exists, skip adding again
            }

            $def['options'] = $opts;
        }
        unset($def);

        return $schema;
    }

    /**
     * Generate default index name consistent with TableBlueprint.
     */
    protected function defaultIndexName(string $table, array $columns, string $type): string
    {
        $type = strtolower($type);
        $suffix = match ($type) {
            'primary' => 'pk',
            'unique'  => 'uk',
            'fulltext' => 'ft',
            'spatial' => 'sp',
            default   => 'idx',
        };
        return $table . '_' . implode('_', $columns) . '_' . $suffix;
    }

    /**
     * Add column from schema definition using Schema Builder
     * In ALTER mode, we should use addColumn() directly, not mapSchemaTypeToColumn()
     * because mapSchemaTypeToColumn() is designed for CREATE mode
     */
    protected function addColumnFromSchema($blueprint, string $type, string $name, array $options)
    {
        // Map schema type to portable type for addColumn()
        $portableType = $this->mapSchemaTypeToPortableType($type);

        // Use addColumn() directly for ALTER mode
        $column = $blueprint->addColumn($portableType, $name);

        // Apply type-specific options that addColumn() doesn't handle automatically
        $this->applyTypeSpecificOptions($column, $type, $options);

        // Apply general options using BaseSchema's method - uses cached reflection
        $applyMethod = $this->getReflectionMethod('applyColumnOptions');
        $applyMethod->invoke($this->schema, $column, $options);

        // Apply indexes if needed - uses cached reflection
        $applyIndexMethod = $this->getReflectionMethod('applyColumnIndexes');
        $applyIndexMethod->invoke($this->schema, $blueprint, $name, $options);

        return $column;
    }

    /**
     * Modify column from schema definition using Schema Builder
     * Reuses BaseSchema's protected methods via reflection (cached)
     * 
     * Note: modifyColumn() only accepts type string, so we need to:
     * 1. Map schema type to portable type
     * 2. Apply type-specific options (unsigned, length, precision, scale, enum values, etc.)
     * 3. Apply general options using BaseSchema::applyColumnOptions()
     */
    protected function modifyColumnFromSchema($blueprint, string $type, string $name, array $options)
    {
        // Map schema type to portable type for modifyColumn()
        $portableType = $this->mapSchemaTypeToPortableType($type);

        // Create modifyColumn with portable type
        $column = $blueprint->modifyColumn($portableType, $name);

        // Apply type-specific options that modifyColumn() doesn't handle
        $this->applyTypeSpecificOptions($column, $type, $options);

        // Apply general options using BaseSchema's method - uses cached reflection
        $applyMethod = $this->getReflectionMethod('applyColumnOptions');
        $applyMethod->invoke($this->schema, $column, $options);

        // Apply indexes (unique/index/fulltext/spatial) if defined on this column
        $applyIndexMethod = $this->getReflectionMethod('applyColumnIndexes');
        $applyIndexMethod->invoke($this->schema, $blueprint, $name, $options);

        return $column;
    }

    /**
     * Map schema type to portable type name used by TableBlueprint::modifyColumn()
     * This is needed because modifyColumn() expects portable type names
     */
    protected function mapSchemaTypeToPortableType(string $type)
    {
        $type = strtolower($type);

        // Map schema types to portable types that TableBlueprint understands
        $map = [
            'increments' => 'increments',
            'increment' => 'increments',
            'bigincrements' => 'bigIncrements',
            'bigincrement' => 'bigIncrements',
            'integer' => 'int',
            'int' => 'int',
            'number' => 'int',
            'biginteger' => 'bigint',
            'bigint' => 'bigint',
            'bignumber' => 'bigint',
            'tinyinteger' => 'tinyint',
            'tinyint' => 'tinyint',
            'boolean' => 'bool',
            'bool' => 'bool',
            'string' => 'string',
            'varchar' => 'string',
            'text' => 'text',
            'mediumtext' => 'mediumtext',
            'longtext' => 'longtext',
            'json' => 'json',
            'decimal' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'time' => 'time',
            'year' => 'year',
            'enum' => 'enum',
            'set' => 'set',
            'point' => 'point',
            'blob' => 'blob',
        ];

        return $map[$type] ?? 'string';
    }

    /**
     * Apply type-specific options that modifyColumn() doesn't handle automatically
     * (e.g., unsigned, length, precision, scale, enum/set values)
     */
    protected function applyTypeSpecificOptions($column, string $type, array $options)
    {
        $type = strtolower($type);

        // Apply unsigned for integer types
        if (in_array($type, ['integer', 'int', 'number', 'biginteger', 'bigint', 'bignumber', 'tinyinteger', 'tinyint'])) {
            if (isset($options['unsigned']) && $options['unsigned']) {
                $column->unsigned(true);
            }
        }

        // Apply length for string types only
        // Note: Integer types (INT, BIGINT) should NOT have length in MySQL ALTER TABLE
        // Only TINYINT can have length, and it's usually 1
        if (in_array($type, ['string', 'varchar'])) {
            if (isset($options['length'])) {
                $column->length($options['length']);
            }
        }

        // TINYINT can have length (usually 1 for boolean-like columns)
        if (in_array($type, ['tinyinteger', 'tinyint'])) {
            if (isset($options['length'])) {
                $column->length($options['length']);
            }
        }

        // Apply precision and scale for decimal
        if ($type === 'decimal') {
            $precision = $options['precision'] ?? 10;
            $scale = $options['scale'] ?? 2;
            $column->precisionScale($precision, $scale);
        }

        // Apply enum values
        if ($type === 'enum' && isset($options['values'])) {
            $column->enum($options['values']);
        }

        // Apply set values
        if ($type === 'set' && isset($options['values'])) {
            $column->set($options['values']);
        }
    }

    /**
     * Check if column needs modification by comparing current DB column with new schema definition
     */
    protected function needsModification(array $currentColumn, array $newDef)
    {
        $name = $newDef['name'] ?? '';
        $type = $newDef['type'] ?? 'string';
        $options = $newDef['options'] ?? [];

        // Compare type (need to normalize)
        $currentType = $this->normalizeTypeForComparison($currentColumn['type']);
        $newType = $this->normalizeTypeForComparison($type, $options);

        if ($currentType !== $newType) {
            return true;
        }

        // Compare nullable
        $currentNullable = $currentColumn['nullable'] ?? false;
        $newNullable = $options['null'] ?? $options['nullable'] ?? false;
        if ($currentNullable !== $newNullable) {
            return true;
        }

        // Compare default
        $currentDefault = $currentColumn['default'] ?? null;
        $newDefault = $options['default'] ?? null;
        if ($this->normalizeDefault($currentDefault) !== $this->normalizeDefault($newDefault)) {
            return true;
        }

        // Compare auto_increment
        $currentAutoInc = $currentColumn['auto_increment'] ?? false;
        $newAutoInc = $options['auto_increment'] ?? $options['autoIncrement'] ?? false;
        if ($currentAutoInc !== $newAutoInc) {
            return true;
        }

        return false;
    }

    /**
     * Normalize type for comparison
     */
    protected function normalizeTypeForComparison(string $type, array $options = [])
    {
        $type = strtolower($type);

        // Map to canonical types
        switch ($type) {
            case 'increments':
            case 'increment':
                return 'int';
            case 'bigincrements':
            case 'bigincrement':
                return 'bigint';
            case 'integer':
            case 'int':
            case 'number':
                return 'int';
            case 'biginteger':
            case 'bigint':
            case 'bignumber':
                return 'bigint';
            case 'tinyinteger':
            case 'tinyint':
                return 'tinyint';
            case 'boolean':
            case 'bool':
                return 'tinyint';
            case 'string':
            case 'varchar':
                $length = $options['length'] ?? 255;
                return "varchar({$length})";
            case 'text':
                return 'text';
            case 'mediumtext':
                return 'mediumtext';
            case 'longtext':
                return 'longtext';
            case 'json':
                return 'json';
            case 'decimal':
                $precision = $options['precision'] ?? 10;
                $scale = $options['scale'] ?? 2;
                return "decimal({$precision},{$scale})";
            case 'float':
                return 'float';
            case 'double':
                return 'double';
            case 'date':
                return 'date';
            case 'datetime':
                return 'datetime';
            case 'timestamp':
                return 'timestamp';
            case 'time':
                return 'time';
            case 'year':
                return 'year';
            case 'enum':
                return 'enum';
            case 'set':
                return 'set';
            case 'point':
                return 'point';
            case 'blob':
                return 'blob';
            default:
                return strtolower($type);
        }
    }

    /**
     * Normalize default value for comparison
     */
    protected function normalizeDefault($default)
    {
        if ($default === null) {
            return 'NULL';
        }
        if (is_bool($default)) {
            return $default ? '1' : '0';
        }
        if (is_string($default)) {
            $upper = strtoupper(trim($default));
            if (in_array($upper, ['CURRENT_TIMESTAMP', 'CURRENT_DATE', 'CURRENT_TIME', 'NULL'])) {
                return $upper;
            }
        }
        return (string)$default;
    }


    /**
     * Initialize DB if not already initialized
     */
    protected function initializeDB()
    {
        try {
            // Try to get connection to check if DB is initialized
            DB::connection();
        } catch (\RuntimeException $e) {
            // DB not initialized, initialize it now
            // Load database config from Config.php (handles both simple and complex)
            $dbConfig = config('database');
            
            if (empty($dbConfig)) {
                throw new \RuntimeException('Database configuration not found');
            }

            // Initialize DB (will auto-convert simple config if needed)
            DB::init($dbConfig);
        }
    }


    public function showHelp(): void
    {
        $this->output("Make Table Command");
        $this->output("=================");
        $this->output("");
        $this->output("Usage:");
        $this->output("  php cmd create:table <name> [options]");
        $this->output("");
        $this->output("Arguments:");
        $this->output("  name    Name of the model/table to synchronize");
        $this->output("");
        $this->output("Options:");
        $this->output("  --force     Force synchronization even if data exists");
        $this->output("  --dry-run   Show what would be synchronized without doing it");
        $this->output("");
        $this->output("Examples:");
        $this->output("  php cmd create:table User");
        $this->output("  php cmd create:table Product --dry-run");
        $this->output("  php cmd create:table Category --force");
    }
}
