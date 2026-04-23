# BaseModel - Modern ORM Model

> 🎉 **Production-Ready**: Laravel-like Model base class with schema management, scopes, and events.

## Quick Start

```php
<?php
namespace App\Models;

use System\Database\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email'];
    public $timestamps = true;
    
    // Optional: Schema definition
    protected function _schema()
    {
        return [
            ['type' => 'id', 'name' => 'id'],
            ['type' => 'string', 'name' => 'name'],
            ['type' => 'string', 'name' => 'email'],
            ['type' => 'timestamps'],
        ];
    }
    
    // Optional: Local scope
    protected function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

## Usage

```php
// Retrieve
$users = User::all();
$user = User::find(1);
$user = User::active()->first(); // Using scope

// Create
$id = User::create(['name' => 'John', 'email' => 'john@example.com']);

// Update
User::update(['name' => 'John Smith'], ['id' => 1]);

// Delete
User::destroy(1);
```

## Features

✅ **Laravel-like API** - Familiar API for Laravel developers  
✅ **Schema Management** - Define and manage table structure  
✅ **Scopes** - Global and local scopes  
✅ **Events** - Model lifecycle hooks  
✅ **Relationships** - Eloquent-style relationships (hasOne, hasMany, belongsTo, etc.)  
✅ **Mass Assignment Protection** - Fillable/Guarded  
✅ **Attribute Casting** - Automatic type conversion  
✅ **Timestamps** - Auto-managed created_at/updated_at  
✅ **Transactions** - Support for database transactions  
✅ **Read-Your-Own-Writes** - Force write connection for consistency  

## Documentation

- 📘 **[USAGE_EXAMPLES.md](./USAGE_EXAMPLES.md)** - Comprehensive examples (500+ lines)
- 📋 **[BASEMODEL_REFACTOR_PLAN.md](./BASEMODEL_REFACTOR_PLAN.md)** - Implementation plan
- 📊 **[BASEMODEL_FINAL_SUMMARY.md](./BASEMODEL_FINAL_SUMMARY.md)** - Final summary & migration guide

## Core Methods

### Retrieval
- `all()`, `find($id)`, `findOrFail($id)`
- `first()`, `firstOrFail()`, `firstOrNew()`, `firstOrCreate()`
- `paginate($perPage, $page)`, `count()`

### Write Operations
- `create($data)` - Returns ID
- `createMany($data)` - Bulk insert
- `update($attributes, $where)` - Batch update
- `destroy($where)` - Delete records

### Query Builder Delegation
All query builder methods are available:
```php
User::where('status', 'active')->orderBy('name')->get();
User::select('name', 'email')->whereIn('id', [1,2,3])->get();
```

### Relationships
```php
// One-to-One
$user->profile()->first();

// One-to-Many  
$user->posts()->get();

// Many-to-One
$post->user()->first();

// Many-to-Many
$user->roles()->attach($roleId);
$user->roles()->sync([1, 2, 3]);

// Polymorphic
$post->comments()->create(['content' => 'Great!']);
$comment->commentable()->getResults();
```

## Advanced Features

### Local Scopes
```php
protected function scopeActive($query)
{
    return $query->where('status', 'active');
}

// Usage
User::active()->get();
```

### Global Scopes
```php
static::addGlobalScope(function($query) {
    return $query->where('is_deleted', false);
});
```

### Events
```php
static::on('created', function($data) {
    // Send welcome email
});
```

### Schema Management
```php
if (!$this->hasTable()) {
    $this->createTable();
}

$this->alterTable([
    ['type' => 'add', 'definition' => [...]]
]);
```

## Migration from Old BaseModel

```php
// Old way ❌
$user = UserModel::row(['id' => 1]);
UserModel::add(['name' => 'John']);
UserModel::set(['name' => 'John'], ['id' => 1]);
UserModel::del(['id' => 1]);

// New way ✅
$user = User::find(1);
User::create(['name' => 'John']);
User::update(['name' => 'John'], ['id' => 1]);
User::destroy(1);
```

## Status

**✅ Production Ready** - ~95% Complete

All essential features implemented including Relationships!

## License

Part of the System Database package.

