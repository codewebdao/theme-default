# Default Value Handling in Schema Builder

## Overview

The Schema Builder now correctly handles special SQL keywords and expressions for default values, ensuring they are **NOT** quoted when they should remain as raw SQL.

## Special Keywords & Expressions

### 1. NULL Keyword
```php
$table->string('optional_field')->default('NULL'); // → DEFAULT NULL (not 'NULL')
```

### 2. Boolean Literals
```php
// MySQL
$table->boolean('is_active')->default('TRUE');  // → DEFAULT 1
$table->boolean('is_deleted')->default('FALSE'); // → DEFAULT 0

// PostgreSQL
$table->boolean('is_active')->default('TRUE');  // → DEFAULT TRUE
$table->boolean('is_deleted')->default('FALSE'); // → DEFAULT FALSE

// SQLite
$table->boolean('is_active')->default('TRUE');  // → DEFAULT 1
$table->boolean('is_deleted')->default('FALSE'); // → DEFAULT 0
```

### 3. Timestamp Functions (Auto-init/update)

#### MySQL
```php
// Basic CURRENT_TIMESTAMP
$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
// → DEFAULT CURRENT_TIMESTAMP (no quotes)

// With precision
$table->timestamp('created_at')->default('CURRENT_TIMESTAMP(3)');
// → DEFAULT CURRENT_TIMESTAMP(3) (milliseconds)

$table->timestamp('updated_at')
    ->default('CURRENT_TIMESTAMP')
    ->onUpdateRaw('CURRENT_TIMESTAMP');
// → DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

#### PostgreSQL
```php
$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
// → DEFAULT CURRENT_TIMESTAMP

$table->timestamp('created_at')->default('NOW');
// → DEFAULT NOW

$table->date('created_date')->default('CURRENT_DATE');
// → DEFAULT CURRENT_DATE
```

#### SQLite
```php
$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
// → DEFAULT CURRENT_TIMESTAMP

$table->date('created_date')->default('CURRENT_DATE');
// → DEFAULT CURRENT_DATE

$table->time('created_time')->default('CURRENT_TIME');
// → DEFAULT CURRENT_TIME
```

### 4. Expression Defaults (MySQL 8.0.13+, PostgreSQL, SQLite 3.31.0+)

Expressions must be **wrapped in parentheses**:

#### MySQL
```php
// UUID generation
$table->string('uuid', 36)->default('(UUID())');
// → DEFAULT (UUID())

$table->binary('uuid_bin')->default('(UUID_TO_BIN(UUID()))');
// → DEFAULT (UUID_TO_BIN(UUID()))

// Date/Time functions
$table->datetime('created_at')->default('(NOW())');
// → DEFAULT (NOW())

$table->date('today')->default('(CURRENT_DATE)');
// → DEFAULT (CURRENT_DATE)

// JSON defaults
$table->json('settings')->default('(JSON_OBJECT())');
// → DEFAULT (JSON_OBJECT())

// Math expressions
$table->integer('score')->default('(1+1)');
// → DEFAULT (1+1)

// Random values
$table->float('random_val')->default('(RAND())');
// → DEFAULT (RAND())
```

#### PostgreSQL
```php
// UUID generation
$table->uuid('uuid')->default('(GEN_RANDOM_UUID())');
// → DEFAULT (GEN_RANDOM_UUID())

// Date/Time functions
$table->timestamp('created_at')->default('(NOW())');
// → DEFAULT (NOW())

// JSON defaults
$table->json('settings')->default('(JSON_BUILD_OBJECT())');
// → DEFAULT (JSON_BUILD_OBJECT())
```

#### SQLite
```php
// Date/Time functions
$table->timestamp('created_at')->default('(DATETIME("now"))');
// → DEFAULT (DATETIME("now"))

$table->date('today')->default('(DATE("now"))');
// → DEFAULT (DATE("now"))

// Random values
$table->integer('random_val')->default('(ABS(RANDOM()))');
// → DEFAULT (ABS(RANDOM()))
```

## How It Works

### 1. BaseSchema.php
When processing `options['default']`, the code checks if the value is a special keyword/expression:

```php
// In BaseSchema::applyColumnType()
if (array_key_exists('default', $opts)) {
    $defaultValue = $opts['default'];
    
    if (is_string($defaultValue)) {
        $upper = strtoupper(trim($defaultValue));
        
        // Check for keywords like NULL, TRUE, FALSE, CURRENT_TIMESTAMP
        if (in_array($upper, $rawKeywords, true)) {
            $column->defaultRaw($defaultValue); // No quotes
        }
        // Check for CURRENT_TIMESTAMP(3), etc.
        elseif (preg_match('/^CURRENT_TIMESTAMP\(\d+\)$/i', trim($defaultValue))) {
            $column->defaultRaw($defaultValue); // No quotes
        }
        // Check for expressions: (NOW()), (UUID()), etc.
        elseif (preg_match('/^\(.+\)$/', $defaultValue)) {
            $column->defaultRaw($defaultValue); // No quotes
        }
        // Otherwise, treat as literal string
        else {
            $column->default($defaultValue); // Will be quoted
        }
    } else {
        $column->default($defaultValue); // Non-string (int, bool, null)
    }
}
```

### 2. Schema Compilers (MysqlSchema, PgsqlSchema, SqliteSchema)
The `literal()` method in each compiler also checks for special keywords:

```php
private function literal($v)
{
    if ($v === null) return 'NULL';
    if (is_bool($v)) return $v ? '1' : '0'; // or 'TRUE'/'FALSE' for PostgreSQL
    if (is_int($v) || is_float($v)) return (string)$v;
    
    if (is_string($v)) {
        $upper = strtoupper(trim($v));
        
        // Check for NULL
        if ($upper === 'NULL') return 'NULL';
        
        // Check for TRUE/FALSE
        if ($upper === 'TRUE' || $upper === 'FALSE') {
            // MySQL/SQLite: return '1' or '0'
            // PostgreSQL: return 'TRUE' or 'FALSE'
        }
        
        // Check for CURRENT_TIMESTAMP
        if ($upper === 'CURRENT_TIMESTAMP' || preg_match('/^CURRENT_TIMESTAMP\(\d+\)$/', $upper)) {
            return $upper; // No quotes
        }
        
        // Check for expressions in parentheses
        if (preg_match('/^\(.+\)$/', $v)) {
            // Validate function names...
            return $v; // No quotes
        }
    }
    
    // Default: quote the string
    return "'".str_replace("'", "''", (string)$v)."'";
}
```

## Usage Examples

### Example 1: User Table with Timestamps
```php
Schema::create('users', function($table) {
    $table->increments('id');
    $table->string('username', 50)->unique();
    $table->string('email', 100)->unique();
    $table->boolean('is_active')->default('TRUE'); // → DEFAULT 1
    $table->timestamp('created_at')->default('CURRENT_TIMESTAMP'); // → DEFAULT CURRENT_TIMESTAMP
    $table->timestamp('updated_at')->default('CURRENT_TIMESTAMP')->onUpdateRaw('CURRENT_TIMESTAMP');
});
```

### Example 2: Posts Table with UUID (MySQL 8.0.13+)
```php
Schema::create('posts', function($table) {
    $table->increments('id');
    $table->string('uuid', 36)->default('(UUID())'); // → DEFAULT (UUID())
    $table->string('title', 255);
    $table->text('content')->nullable()->default('NULL'); // → DEFAULT NULL
    $table->json('metadata')->default('(JSON_OBJECT())'); // → DEFAULT (JSON_OBJECT())
    $table->datetime('created_at')->default('(NOW())'); // → DEFAULT (NOW())
});
```

### Example 3: Settings Table with Defaults
```php
Schema::create('settings', function($table) {
    $table->increments('id');
    $table->string('key', 100)->unique();
    $table->string('value')->default(''); // → DEFAULT '' (quoted)
    $table->integer('priority')->default(0); // → DEFAULT 0 (no quotes)
    $table->boolean('is_system')->default('FALSE'); // → DEFAULT 0
    $table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
});
```

## Important Notes

1. **Expression defaults require parentheses**: `(UUID())`, `(NOW())`, not `UUID()` or `NOW()`
   - Exception: `CURRENT_TIMESTAMP` can be used without parentheses for timestamp auto-init

2. **MySQL 8.0.13+ required** for expression defaults with parentheses

3. **PostgreSQL** is more flexible and allows many functions without parentheses

4. **SQLite 3.31.0+** supports expression defaults in parentheses

5. **Always use `defaultRaw()`** when manually setting raw expressions:
   ```php
   $table->string('uuid')->defaultRaw('(UUID())'); // Explicit
   ```

6. **Regular string values are automatically quoted**:
   ```php
   $table->string('status')->default('active'); // → DEFAULT 'active'
   ```

## Supported Functions by Database

### MySQL
- `CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`
- `(NOW())`, `(UTC_TIMESTAMP())`, `(UTC_DATE())`, `(UTC_TIME())`
- `(UUID())`, `(UUID_TO_BIN(UUID()))`
- `(RAND())`, `(MD5(...))`, `(UNIX_TIMESTAMP())`
- `(JSON_OBJECT(...))`, `(JSON_ARRAY(...))`

### PostgreSQL
- `CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`
- `NOW`, `LOCALTIME`, `LOCALTIMESTAMP`
- `(GEN_RANDOM_UUID())`, `(UUID_GENERATE_V4())`
- `(RANDOM())`, `(MD5(...))`
- `(JSON_BUILD_OBJECT(...))`, `(JSON_BUILD_ARRAY(...))`

### SQLite
- `CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`
- `(DATETIME(...))`, `(DATE(...))`, `(TIME(...))`
- `(RANDOM())`, `(ABS(...))`, `(HEX(...))`
- `(LOWER(...))`, `(UPPER(...))`, `(TRIM(...))`

## Backward Compatibility

All existing code using `default()` with literal values continues to work:
```php
$table->string('status')->default('active');     // Still quoted → DEFAULT 'active'
$table->integer('count')->default(0);            // Still works → DEFAULT 0
$table->boolean('flag')->default(true);          // Still works → DEFAULT 1
```

Only special keywords and expressions are treated differently.

