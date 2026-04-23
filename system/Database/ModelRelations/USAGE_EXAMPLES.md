# BaseModel Usage Examples

## Overview
This document provides comprehensive examples for using the new BaseModel class.

## Basic Configuration

```php
<?php
namespace App\Models;

use System\Database\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    // Mass assignment protection
    protected $fillable = ['name', 'email', 'status'];
    protected $guarded = ['password', 'is_admin'];
    
    // Attribute casting
    protected $casts = [
        'is_active' => 'bool',
        'created_at' => 'datetime',
        'metadata' => 'json',
    ];
    
    // Automatic timestamps
    public $timestamps = true;
    
    // Schema definition (optional, for IDE autocomplete and table management)
    protected function _schema()
    {
        return [
            ['type' => 'id', 'name' => 'id'],
            ['type' => 'string', 'name' => 'name', 'options' => ['length' => 255]],
            ['type' => 'string', 'name' => 'email', 'options' => ['length' => 255]],
            ['type' => 'string', 'name' => 'password', 'options' => ['length' => 255]],
            ['type' => 'boolean', 'name' => 'is_active', 'options' => ['default' => true]],
            ['type' => 'json', 'name' => 'metadata', 'options' => ['nullable' => true]],
            ['type' => 'timestamps'],
            ['type' => 'index', 'name' => 'idx_email', 'options' => ['email']],
            ['type' => 'unique', 'name' => 'unique_email', 'options' => ['email']],
        ];
    }
}
```

## Retrieval Methods

### Get All Records
```php
$users = User::all();
```

### Find by ID
```php
$user = User::find(1);

// Throw exception if not found
$user = User::findOrFail(1);
```

### Get First Record
```php
// Get first user
$user = User::first();

// Get first active user
$user = User::where('status', 'active')->first();

// Throw exception if not found
$user = User::where('status', 'active')->firstOrFail();
```

### Find or Create
```php
// Return array if exists, create and save if not
$user = User::firstOrCreate(
    ['email' => 'john@example.com'],  // where to search
    ['name' => 'John Doe', 'status' => 'active']  // values to create
);

// Return array if exists, return array data if not (not saved)
$user = User::firstOrNew(['email' => 'jane@example.com']);
```

### Pagination
```php
// Paginate 15 items per page, page 2
$result = User::paginate(15, 2);
// Returns: ['data' => [...], 'is_next' => true, 'page' => 2, 'total' => 150]
```

## Write Methods

### Create Single Record
```php
$id = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active'
]);
```

### Update Records
```php
$affected = User::update(
    ['status' => 'inactive'],
    ['id' => 1]
);
```

### Delete Records
```php
User::destroy(1);
```

## Query Builder Usage

```php
// Where clauses
$users = User::where('status', 'active')->get();

// Order By
$users = User::orderBy('created_at', 'desc')->get();

// Select specific columns
$users = User::select('name', 'email')->get();
```

## Schema Management

```php
// Check if table exists
$userModel = new User();
if (!$userModel->hasTable()) {
    // Create table from schema
    $userModel->createTable();
}

// Alter table (add/modify columns)
$userModel->alterTable([
    ['type' => 'add', 'definition' => ['type' => 'string', 'name' => 'avatar', 'options' => ['length' => 500, 'nullable' => true]]],
    ['type' => 'modify', 'column' => 'email', 'definition' => ['type' => 'string', 'name' => 'email', 'options' => ['length' => 320]]]
]);
```

## Scopes

### Local Scopes (Reusable Query Logic)

Define scope methods in your model:

```php
<?php
class User extends BaseModel
{
    // Scope naming convention: scopeXxx($query, $param1, $param2...)
    protected function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    protected function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
    
    protected function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    protected function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
    
    // Chain scopes
    protected function scopeAdmin($query)
    {
        return $this->scopeActive($query)->scopeRole($query, 'admin');
    }
}
```

Usage:

```php
// Call scopes (note: no 'scope' prefix)
$activeUsers = User::active()->get();
$admins = User::role('admin')->get();
$verifiedUsers = User::verified()->get();

// Chain multiple scopes
$activeAdmins = User::active()->role('admin')->get();

// With additional constraints
$recentAdmins = User::active()->role('admin')->where('created_at', '>', '2024-01-01')->get();
```

### Global Scopes (Apply to All Queries)

Global scopes are applied automatically to all queries.

```php
<?php
class User extends BaseModel
{
    protected function boot()
    {
        // Register global scope (runs once)
        static::addGlobalScope('isActive', function($query) {
            $query->where('is_deleted', false);
        });
        
        static::addGlobalScope('tenantScope', function($query) {
            $query->where('tenant_id', getCurrentTenantId());
        });
    }
}
```

Usage:

```php
// All queries automatically include global scopes
$users = User::all(); // WHERE is_deleted = 0 AND tenant_id = X

// Skip global scopes when needed
$allUsers = User::withoutGlobalScopes()->get();

// Skip specific global scope
$allTenants = User::withoutGlobalScopes(['tenantScope'])->get();
```

## Model Events

### Register Event Listeners

```php
<?php
class User extends BaseModel
{
    public function boot()
    {
        // Listen to events
        static::on('creating', function($data) {
            Log::info("Creating user: {$data['email']}");
        });
        
        static::on('created', function($data) {
            // Send welcome email
            Mail::send('welcome', ['user' => $data]);
        });
        
        static::on('updating', function($payload) {
            Log::info("Updating user: {$payload['id']}");
        });
        
        static::on('updated', function($payload) {
            // Cache refresh
            Cache::forget("user:{$payload['id']}");
        });
        
        static::on('deleting', function($id) {
            Log::warning("Deleting user: $id");
        });
        
        static::on('deleted', function($id) {
            // Clean up related data
            Post::where('user_id', $id)->delete();
        });
    }
}
```

### Event Flow

```php
// Creating event fires BEFORE insert
// Created event fires AFTER insert
$id = User::create(['name' => 'John', 'email' => 'john@example.com']);
// Output: "Creating user: john@example.com" (before insert)
// Output: Welcome email sent (after insert)

// Updating event fires BEFORE update
// Updated event fires AFTER update
User::update(['name' => 'John Doe'], ['id' => $id]);
// Output: "Updating user: 1" (before update)
// Output: Cache cleared (after update)

// Deleting event fires BEFORE delete
// Deleted event fires AFTER delete
User::destroy($id);
// Output: "Deleting user: 1" (before delete)
// Output: Related posts deleted (after delete)
```

### Execute Without Events

Sometimes you want to skip events:

```php
// Insert without triggering events
User::withoutEvents(function() use ($data) {
    return User::create($data);
});

// This is useful for:
// - Bulk imports
// - Background jobs
// - Seeders
```

## Relationships

### One-to-One (hasOne)
```php
<?php
class User extends BaseModel
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }
}

// Usage
$user = User::find(1);
$profile = $user->profile()->first();
$profileId = $user->profile()->create(['bio' => 'Developer']);
```

### One-to-Many (hasMany)
```php
<?php
class User extends BaseModel
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
}

// Usage
$user = User::find(1);
$posts = $user->posts()->get();
$postId = $user->posts()->create(['title' => 'My Post']);
```

### Many-to-One (belongsTo)
```php
<?php
class Post extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

// Usage
$post = Post::find(1);
$user = $post->user()->first();
$post->user()->associate($user);
```

### Many-to-Many (belongsToMany)
```php
<?php
class User extends BaseModel
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
}

// Usage
$user = User::find(1);
$roles = $user->roles()->get();
$user->roles()->attach(1);
$user->roles()->sync([1, 2, 3]);
$changes = $user->roles()->sync([1, 2]); // Returns attached/detached arrays
```

### Polymorphic Relationships
```php
<?php
class Post extends BaseModel
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Comment extends BaseModel
{
    public function commentable()
    {
        return $this->morphTo('commentable');
    }
}

// Usage
$post = Post::find(1);
$comments = $post->comments()->get();
$commentId = $post->comments()->create(['content' => 'Great post!']);

$comment = Comment::find(1);
$parent = $comment->commentable()->getResults(); // Returns Post or Video
```

## Advanced Query Examples

### Complex Queries

```php
// Aggregates
$totalUsers = User::count();
$avgAge = User::avg('age');
$maxScore = User::max('score');
$minScore = User::min('score');

// Value and pluck
$email = User::where('id', 1)->value('email'); // Returns single value
$emails = User::pluck('email'); // Returns array of emails
$emailsByName = User::pluck('email', 'name'); // Returns ['name' => 'email', ...]

// Exists checks
if (User::where('email', 'exists@example.com')->exists()) {
    // Email already exists
}

// Chunk processing (memory efficient)
User::where('status', 'pending')->chunk(1000, function($users) {
    foreach ($users as $user) {
        // Process 1000 users at a time
        processUser($user);
    }
});

// Chunk by ID (safer, handles changing data)
User::where('status', 'pending')->chunkById(500, function($users) {
    foreach ($users as $user) {
        updateUser($user);
    }
});
```

### Joins and Relationships

```php
// Join with other tables (manual, not relationships)
$usersWithPosts = User::leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->get();
```

### Subqueries

```php
// Select with subquery
$users = User::select('*')
    ->whereIn('id', function($query) {
        $query->select('user_id')
            ->from('orders')
            ->where('total', '>', 1000);
    })
    ->get();
```

## Transactions

```php
// Basic transaction
User::beginTransaction();
try {
    $user = User::create($userData);
    Profile::create(['user_id' => $user, 'bio' => '...']);
    User::commit();
} catch (\Exception $e) {
    User::rollBack();
    throw $e;
}

// Transaction with callback (auto-commit/rollback)
User::transaction(function() use ($userData) {
    $user = User::create($userData);
    Profile::create(['user_id' => $user, 'bio' => '...']);
    return $user;
});
```

## Read-Your-Own-Writes (Consistency)

```php
// Force subsequent reads to use write connection
User::forceWrite();

// All SELECT queries will use write connection
$user = User::find(1); // Uses write connection
$users = User::where('status', 'active')->get(); // Uses write connection

// Useful for scenarios:
// - Just updated a record, need to read it immediately
// - Avoid replication lag issues
User::update(['status' => 'active'], ['id' => 1]);
User::forceWrite();
$user = User::find(1); // Returns updated data
```

## Attribute Casting

Benefits of casting:
- Automatic type conversion
- JSON fields handled automatically
- Date/datetime formatting

```php
<?php
class Post extends BaseModel
{
    protected $casts = [
        'is_published' => 'bool',           // 0/1 ↔ true/false
        'views' => 'int',                   // String ↔ Integer
        'price' => 'float',                 // String ↔ Float
        'tags' => 'array',                  // JSON ↔ PHP Array
        'metadata' => 'json',               // JSON ↔ Object
        'published_at' => 'datetime',       // Timestamp ↔ DateTime
        'created_date' => 'date',           // Y-m-d ↔ DateTime
    ];
}
```

Usage:

```php
$post = Post::find(1);

// Automatic casting
$post['is_published'];  // Returns: true (from DB: 1)
$post['views'];         // Returns: 42 (from DB: "42")
$post['tags'];          // Returns: ['php', 'laravel'] (from DB: '["php","laravel"]')
$post['metadata'];      // Returns: stdClass (from DB: '{"key":"value"}')
$post['published_at'];  // Returns: DateTime object

// All casting works both ways
Post::create([
    'is_published' => true,        // Stored as: 1
    'views' => 42,                 // Stored as: "42"
    'tags' => ['php', 'laravel'],  // Stored as: '["php","laravel"]'
    'metadata' => ['key' => 'value'] // Stored as: '{"key":"value"}'
]);
```

## Mass Assignment Protection

### Fillable (Whitelist Approach)

```php
<?php
class User extends BaseModel
{
    protected $fillable = ['name', 'email', 'phone'];
}

// Only name, email, phone will be saved
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'phone' => '123456',
    'is_admin' => true  // ⚠️ IGNORED (not in fillable)
]);
```

### Guarded (Blacklist Approach)

```php
<?php
class User extends BaseModel
{
    protected $guarded = ['is_admin', 'role', 'password'];
}

// Everything except guarded fields will be saved
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'is_admin' => true  // ⚠️ IGNORED (guarded)
]);
```

### Best Practice

- Use `$fillable` when you have many fields (whitelist is safer)
- Use `$guarded` when you have few sensitive fields (more convenient)
- Never use both (one overrides the other)

