# System\Database - Database Library

Modern PHP Database Library with Laravel-like API.

## Structure

```
system/Database/
├── BaseModel.php          # Main ORM Model class (Laravel-like)
├── DB.php                 # Database Facade (main entry point)
├── ConnectionManager.php  # Connection pooling & routing
├── DatabaseConnection.php # Connection wrapper
├── DatabaseDriver.php     # Driver interface
├── DatabaseManager.php    # Database manager
├── PdoDriver.php          # PDO implementation
├── Router.php             # Read/Write routing
├── Query/                 # Query Builder
│   ├── Builder.php        # Main query builder
│   ├── Grammar.php        # SQL grammar interface
│   ├── MysqlGrammar.php   # MySQL SQL compiler
│   ├── PgsqlGrammar.php   # PostgreSQL SQL compiler
│   ├── SqliteGrammar.php  # SQLite SQL compiler
│   ├── JoinClause.php     # Join conditions
│   └── Traits/            # Query builder traits
├── Schema/                # Schema Builder
│   ├── BaseSchema.php     # Schema interface
│   ├── MysqlSchema.php    # MySQL schema compiler
│   ├── PgsqlSchema.php    # PostgreSQL schema compiler
│   ├── SqliteSchema.php   # SQLite schema compiler
│   ├── TableBlueprint.php # Table definition
│   ├── SqliteTableRewriter.php # SQLite table rewrite
│   └── Definitions/       # Schema definitions
├── Relations/             # Relationship classes
├── Debug/                 # Debug utilities
├── Logging/               # Query logging
└── Support/               # Helper classes
```

## Usage

### Basic Database Operations
```php
use System\Database\DB;

// Raw queries
$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);
$id = DB::insert('INSERT INTO users (name, email) VALUES (?, ?)', ['John', 'john@example.com']);
$affected = DB::update('UPDATE users SET status = ? WHERE id = ?', ['active', 1]);
$deleted = DB::delete('DELETE FROM users WHERE id = ?', [1]);

// Query Builder
$users = DB::table('users')
    ->where('active', 1)
    ->where('role', 'admin')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### Model Usage
```php
use System\Database\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'status'];
    
    // Define relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Usage
$user = User::find(1);
$users = User::where('active', 1)->get();
$newUser = User::create(['name' => 'John', 'email' => 'john@example.com']);
```

### Schema Builder
```php
use System\Database\DB;

// Create table
DB::schema()->create('users', function($table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});

// Alter table
DB::schema()->table('users', function($table) {
    $table->string('phone')->nullable();
    $table->index('email');
});
```

## Features

- **Laravel-like API**: Familiar syntax for Laravel developers
- **Multi-database support**: MySQL, PostgreSQL, SQLite
- **Query Builder**: Fluent interface for building queries
- **ORM**: Eloquent-style models with relationships
- **Schema Builder**: Programmatic table management
- **Read/Write routing**: Automatic load balancing
- **Query logging**: Debug and performance monitoring
- **Transactions**: ACID compliance
- **Mass assignment protection**: Security features
- **Attribute casting**: Automatic type conversion
- **Model events**: Lifecycle hooks
- **Global/Local scopes**: Query constraints
- **Relationships**: All Eloquent relationship types

## Examples

See `DatabaseExamples/` directory for comprehensive examples:
- `00_bootstrap.php` - Basic setup
- `20_basemodel_usage.php` - Model usage
- `21_relationships.php` - Relationship examples
- `17_schema_portable.php` - Schema builder examples
