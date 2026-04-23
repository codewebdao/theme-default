# Query Helper - WordPress-Style Database Query Functions

## 📖 Overview

Query Helper provides WordPress-compatible database query functions using **BaseModel** with **Eager Loading** relationships. All functions use proper Model relationships to prevent N+1 query problems.

## 🎯 Key Features

- ✅ **WordPress-compatible API** - Familiar for WP developers
- ✅ **True Eager Loading** - Uses BaseModel relationships (belongsToMany, belongsTo)
- ✅ **BaseModel Based** - Modern ORM with Laravel-like API
- ✅ **Multilingual Support** - Language-aware queries
- ✅ **Performance Optimized** - Efficient subqueries for filtering
- ✅ **Type Safety** - Proper error handling
- ✅ **Avoids JOINs** - Uses efficient subqueries for filtering, Eager Loading for relationships

## 📚 Available Functions

1. [`get_term()`](#get_term) - Get single term
2. [`get_terms()`](#get_terms) - Get multiple terms
3. [`get_post()`](#get_post) - Get single post
4. [`get_posts()`](#get_posts) - Get multiple posts
5. [`get_page()`](#get_page) - Get single page
6. [`get_pages()`](#get_pages) - Get multiple pages
7. [`get_comments()`](#get_comments) - Get post comments

---

## Function Reference

### `get_term()`

Get a single term by slug, posttype, and type.

**Signature:**
```php
get_term(string $slug, string $posttype, string $type = 'category', string $lang = APP_LANG): array|null
```

**Parameters:**
- `$slug` - Term slug
- `$posttype` - Post type name (e.g., 'posts', 'products')
- `$type` - Term type (e.g., 'category', 'tags', 'brand')
- `$lang` - Language code (default: APP_LANG)

**Returns:** Term data array or `null` if not found

**Examples:**
```php
// Get a category
$category = get_term('electronics', 'products', 'category');

// Get a tag
$tag = get_term('featured', 'posts', 'tags', 'en');

// Check result
if ($category) {
    echo $category['name'];
    echo $category['slug'];
}
```

**SQL Generated:**
```sql
SELECT * FROM cms_terms 
WHERE slug = 'electronics' 
  AND posttype = 'products' 
  AND type = 'category' 
  AND lang = 'en'
LIMIT 1
```

---

### `get_terms()`

Get multiple terms with filtering and ordering.

**Signature:**
```php
get_terms(array $args = []): array|int
```

**Parameters:**

| Argument | Type | Default | Description |
|----------|------|---------|-------------|
| `taxonomy` | string | 'category' | Taxonomy name (maps to 'type' column) |
| `post_type` | string | '' | Post type name |
| `lang` | string | APP_LANG | Language code |
| `slug__in` | array | [] | Include terms with these slugs |
| `slug__not_in` | array | [] | Exclude terms with these slugs |
| `include` | array | [] | Include term IDs |
| `exclude` | array | [] | Exclude term IDs |
| `search` | string | '' | Search in name/slug |
| `orderby` | string | 'name' | Order by field |
| `order` | string | 'ASC' | ASC or DESC |
| `number` | int\|null | null | Limit results |
| `offset` | int\|null | null | Offset for pagination |
| `count` | bool | false | Return count only |
| `fields` | array\|string | '*' | Specific fields to select |

**Returns:** Array of terms or count (if `count` is true)

**Examples:**

```php
// Get all categories for products
$categories = get_terms([
    'taxonomy' => 'category',
    'post_type' => 'products',
    'orderby' => 'name',
    'order' => 'ASC'
]);

// Get specific tags
$tags = get_terms([
    'taxonomy' => 'tags',
    'post_type' => 'posts',
    'slug__in' => ['featured', 'trending'],
    'fields' => ['id', 'name', 'slug']
]);

// Search terms
$results = get_terms([
    'taxonomy' => 'category',
    'search' => 'elec',
    'number' => 10
]);

// Get count
$count = get_terms([
    'taxonomy' => 'tags',
    'post_type' => 'products',
    'count' => true
]);
```

---

### `get_post()`

Get a single post by ID or slug with optional relationship loading.

**Signature:**
```php
get_post(int|array $post = null, string $post_type = 'posts', array $args = []): array|null
```

**Parameters:**

| Argument | Type | Default | Description |
|----------|------|---------|-------------|
| `ID` | int | 0 | Post ID |
| `name` | string | '' | Post slug |
| `post_type` | string | 'posts' | Post type |
| `post_status` | string | 'active' | Post status |
| `lang` | string | APP_LANG | Language code |
| `fields` | array\|string | '*' | Fields to select |
| `with_categories` | bool | false | Eager load categories |
| `with_tags` | bool | false | Eager load tags |
| `with_author` | bool | false | Eager load author |

**Returns:** Post data array or `null` if not found

**Examples:**

```php
// Get post by ID
$post = get_post(123);

// Get post by ID with specific post type
$product = get_post(456, 'products');

// Get post by slug with categories (Eager Loading)
$post = get_post([
    'name' => 'my-product-slug',
    'post_type' => 'products',
    'with_categories' => true
]);

// Get post with all relationships (prevent N+1)
$post = get_post([
    'ID' => 123,
    'post_type' => 'products',
    'with_categories' => true,
    'with_tags' => true,
    'with_author' => true
]);

// Access loaded relationships
if ($post) {
    echo $post['title'];
    
    // Categories loaded via Eager Loading (no extra query!)
    foreach ($post['categories'] ?? [] as $cat) {
        echo $cat['name'];
    }
    
    // Tags loaded via Eager Loading (no extra query!)
    foreach ($post['tags'] ?? [] as $tag) {
        echo $tag['name'];
    }
    
    // Author loaded via Eager Loading (no extra query!)
    $author = $post['author_data'] ?? null;
    if ($author) {
        echo $author['fullname'];
    }
}
```

**Performance Note:**
```php
// ❌ WITHOUT Eager Loading (N+1 Problem):
// - 1 query to get post
// - 1 query to get categories (if accessed)
// - 1 query to get tags (if accessed)
// - 1 query to get author (if accessed)
// TOTAL: 4 queries

// ✅ WITH Eager Loading (BaseModel relationships):
// - 1 query to get post
// - 1 query to eager load categories (via BelongsToMany)
// - 1 query to eager load tags (via BelongsToMany)
// - 1 query to eager load author (via BelongsTo)
// TOTAL: 4 efficient queries loaded upfront (no N+1!)
// All relationships loaded in batch before first access
```

---

### `get_posts()`

Get multiple posts with filtering, pagination, and relationship loading.

**Signature:**
```php
get_posts(array $args = []): array
```

**Parameters:**

| Argument | Type | Default | Description |
|----------|------|---------|-------------|
| `post_type` | string | 'posts' | Post type |
| `post_status` | string\|array | 'active' | Post status(es) |
| `posts_per_page` | int | -1 | Posts per page (-1 = all) |
| `paged` | int | 1 | Current page |
| `orderby` | string | 'created_at' | Order by field |
| `order` | string | 'DESC' | ASC or DESC |
| `post__in` | array | [] | Include post IDs |
| `post__not_in` | array | [] | Exclude post IDs |
| `category__in` | array | [] | Include categories |
| `category__not_in` | array | [] | Exclude categories |
| `tag__in` | array | [] | Include tags |
| `tag__not_in` | array | [] | Exclude tags |
| `s` | string | '' | Search term |
| `search_columns` | array | ['search_string'] | Columns to search |
| `lang` | string | APP_LANG | Language code |
| `fields` | array\|string | '*' | Fields to select |
| `with_categories` | bool | false | Eager load categories |
| `with_tags` | bool | false | Eager load tags |
| `with_author` | bool | false | Eager load author |
| `return_total` | bool | false | Return total count |

**Returns:** Array of posts or pagination result

**Examples:**

```php
// Get latest 10 posts
$posts = get_posts([
    'posts_per_page' => 10,
    'orderby' => 'created_at',
    'order' => 'DESC'
]);

// Get products in specific categories with pagination
$products = get_posts([
    'post_type' => 'products',
    'category__in' => [1, 2, 3],
    'posts_per_page' => 20,
    'paged' => 1,
    'with_categories' => true,  // Eager Loading!
    'with_tags' => true          // Eager Loading!
]);

// Pagination result
foreach ($products['data'] as $product) {
    echo $product['title'];
    
    // No extra queries! Categories already loaded
    foreach ($product['categories'] ?? [] as $cat) {
        echo $cat['name'];
    }
}

echo "Page: " . $products['page'];
echo "Has Next: " . ($products['is_next'] ? 'Yes' : 'No');

// Search posts
$results = get_posts([
    's' => 'keyword',
    'search_columns' => ['title', 'content', 'seo_desc'],
    'post_status' => 'active'
]);

// Complex query with multiple filters
$posts = get_posts([
    'post_type' => 'products',
    'post_status' => ['active', 'featured'],
    'category__in' => [1, 2],
    'tag__not_in' => [5],
    'posts_per_page' => 15,
    'paged' => 2,
    'orderby' => 'views',
    'order' => 'DESC',
    'with_categories' => true,
    'with_tags' => true,
    'with_author' => true
]);
```

**Performance Comparison:**

```php
// ❌ WITHOUT Eager Loading (N+1 Problem):
$posts = get_posts(['posts_per_page' => 50]);
foreach ($posts['data'] as $post) {
    // Each iteration = 2 extra queries!
    $categories = get_terms(['post__in' => [$post['id']]]);  // Query per post!
    $tags = get_terms(['post__in' => [$post['id']]]);        // Query per post!
}
// TOTAL: 1 + (50 * 2) = 101 queries 😱

// ✅ WITH Eager Loading (BaseModel relationships):
$posts = get_posts([
    'posts_per_page' => 50,
    'with_categories' => true,  // Uses BelongsToMany!
    'with_tags' => true          // Uses BelongsToMany!
]);
foreach ($posts['data'] as $post) {
    // No additional queries! Data already loaded via Eager Loading
    $categories = $post['categories'] ?? [];
    $tags = $post['tags'] ?? [];
}
// TOTAL: 4 queries only! 🚀
// Query 1: SELECT 50 posts
// Query 2: SELECT all categories for these 50 posts (1 efficient query with whereIn)
// Query 3: SELECT all tags for these 50 posts (1 efficient query with whereIn)
// Query 4: Pagination check (if needed)

// 🎯 The magic: EagerLoadTrait batches all relationship queries using whereIn()
// Instead of N queries (1 per post), we get 1 query per relationship for ALL posts!
```

---

### `get_page()`

Get a single page by ID or slug.

**Signature:**
```php
get_page(int|array $page = null, array $args = []): array|null
```

Wrapper for `get_post()` with `post_type='pages'`.

**Examples:**

```php
// Get page by ID
$page = get_page(123);

// Get page by slug
$page = get_page([
    'name' => 'about-us',
    'with_categories' => true
]);
```

---

### `get_pages()`

Get multiple pages with filtering and pagination.

**Signature:**
```php
get_pages(array $args = []): array
```

Wrapper for `get_posts()` with `post_type='pages'`.

**Examples:**

```php
// Get all active pages
$pages = get_pages([
    'orderby' => 'title',
    'order' => 'ASC'
]);

// Get pages with pagination
$pages = get_pages([
    'posts_per_page' => 20,
    'paged' => 1,
    'with_categories' => true
]);
```

---

### `get_comments()`

Get comments for posts with filtering and relationship loading.

**Signature:**
```php
get_comments(array $args = []): array
```

**Parameters:**

| Argument | Type | Default | Description |
|----------|------|---------|-------------|
| `post_id` | int | 0 | Post ID to get comments for |
| `post_type` | string | 'posts' | Post type |
| `status` | string | 'active' | Comment status |
| `user_id` | int | 0 | Filter by user ID |
| `parent` | int\|null | null | Parent comment ID (0 = top level, null = all) |
| `orderby` | string | 'created_at' | Order by field |
| `order` | string | 'DESC' | ASC or DESC |
| `limit` | int\|null | null | Limit results |
| `offset` | int\|null | null | Offset for pagination |
| `with_user` | bool | false | Eager load user info |
| `with_replies` | bool | false | Eager load replies |
| `count` | bool | false | Return count only |

**Returns:** Array of comments

**Examples:**

```php
// Get comments for a post
$comments = get_comments([
    'post_id' => 123,
    'post_type' => 'products',
    'with_user' => true  // Eager load user info
]);

// Get top-level comments only (no replies)
$comments = get_comments([
    'post_id' => 123,
    'parent' => 0,
    'with_replies' => true  // Eager load replies
]);

// Get comments by user
$userComments = get_comments([
    'user_id' => 456,
    'status' => 'active',
    'orderby' => 'created_at',
    'order' => 'DESC'
]);

// Count total comments
$count = get_comments([
    'post_id' => 123,
    'count' => true
]);
```

**With Post Eager Loading:**

```php
// Get post with comments in one query
$post = get_post([
    'id' => 123,
    'with_comments' => true  // Eager Loading!
]);

// Comments already loaded
foreach ($post['comments'] ?? [] as $comment) {
    echo $comment['content'];
}
```

---

## 🎯 Best Practices

### 1. Always Use Eager Loading for Lists

```php
// ❌ BAD: N+1 Problem
$posts = get_posts(['posts_per_page' => 50]);
foreach ($posts['data'] as $post) {
    $categories = get_terms([...]);  // Extra query per post!
}

// ✅ GOOD: Eager Loading
$posts = get_posts([
    'posts_per_page' => 50,
    'with_categories' => true  // Loaded in batch!
]);
foreach ($posts['data'] as $post) {
    $categories = $post['categories'] ?? [];  // No query!
}
```

### 2. Select Only Needed Fields

```php
// ❌ BAD: Select all columns
$posts = get_posts([
    'posts_per_page' => 100
]);

// ✅ GOOD: Select specific fields
$posts = get_posts([
    'posts_per_page' => 100,
    'fields' => ['id', 'title', 'slug', 'created_at']
]);
```

### 3. Use Subqueries for Filtering

```php
// ✅ The function automatically uses subqueries for category/tag filtering
// This is more efficient than JOIN for filtering
$posts = get_posts([
    'category__in' => [1, 2, 3],  // Uses efficient subquery
    'tag__not_in' => [5, 6]       // Uses efficient subquery
]);
```

---

## 📊 Performance Tips

1. **Enable Query Logging** to monitor performance:
```php
DB::enableQueryLog();

$posts = get_posts([
    'posts_per_page' => 50,
    'with_categories' => true
]);

$queries = DB::getQueryLog();
echo "Total queries: " . count($queries);
```

2. **Use Pagination** for large datasets:
```php
// Better than loading 1000 posts at once
$posts = get_posts([
    'posts_per_page' => 20,
    'paged' => 1
]);
```

3. **Combine Filters Wisely**:
```php
// Efficient: Single query with subqueries
$posts = get_posts([
    'post_type' => 'products',
    'category__in' => [1, 2],
    'post_status' => 'active',
    'posts_per_page' => 20
]);
```

---

## 🔄 Migration from Database_helper.php

| Old Function | New Function | Notes |
|-------------|--------------|-------|
| `get_posts()` with FastModel | `get_posts()` | Now uses **BaseModel** with **Eager Loading** |
| `get_post()` with FastModel | `get_post()` | Now uses **BaseModel** with **Eager Loading** |
| Manual relationship loading | `with_categories`, `with_tags`, `with_author` | Built-in **BelongsToMany/BelongsTo** relationships |

**Before:**
```php
$posts = (new FastModel($table))->where(...)->get();
// Manual loading relationships
foreach ($posts as &$post) {
    $post['categories'] = get_terms([...]); // N+1 problem!
}
```

**After:**
```php
$posts = get_posts([
    'post_type' => 'products',
    'with_categories' => true  // Eager Loading via BaseModel!
]);
// Categories already loaded, no N+1!
```

## 🏗️ Architecture

**Models:**
- `PostsNewModel` - Dynamic post type model with relationships
- `TermsNewModel` - Terms model with relationships
- `CommentsNewModel` - Comments model with relationships
- All extend `BaseModel` for ORM features

**Relationships:**
- `belongsToMany` - Posts ↔ Categories/Tags (via pivot table `posts_..._rel`)
- `belongsTo` - Posts → Author (User), Comments → Post/User
- `hasMany` - Posts → Comments, Comments → Replies
- Uses `EagerLoadTrait` for automatic batch loading

---

## 🐛 Error Handling

All functions include proper error handling and logging:

```php
try {
    $post = get_post(123);
    if ($post) {
        // Process post
    } else {
        // Post not found
    }
} catch (Exception $e) {
    // Error is automatically logged
    // Check error_log for details
}
```

---

## 📝 Complete Example

```php
// Load helper
// (Auto-loaded if in application/Helpers/)

// Get featured products with all relationships
$products = get_posts([
    'post_type' => 'products',
    'category__in' => [1, 2, 3],
    'tag__in' => [10],
    'post_status' => 'active',
    'posts_per_page' => 20,
    'paged' => 1,
    'orderby' => 'created_at',
    'order' => 'DESC',
    'with_categories' => true,
    'with_tags' => true,
    'with_author' => true
]);

// Display results
foreach ($products['data'] as $product) {
    echo "<h2>{$product['title']}</h2>";
    
    // Categories (no extra query!)
    echo "<div class='categories'>";
    foreach ($product['categories'] ?? [] as $cat) {
        echo "<span>{$cat['name']}</span>";
    }
    echo "</div>";
    
    // Tags (no extra query!)
    echo "<div class='tags'>";
    foreach ($product['tags'] ?? [] as $tag) {
        echo "<span>{$tag['name']}</span>";
    }
    echo "</div>";
    
    // Author (no extra query!)
    if ($author = $product['author_data'] ?? null) {
        echo "<p>By: {$author['fullname']}</p>";
    }
}

// Pagination
if ($products['is_next']) {
    echo '<a href="?page=' . ($products['page'] + 1) . '">Next</a>';
}
```

---

## 🚀 Summary

- ✅ **WordPress-compatible API** - Familiar function names and arguments
- ✅ **True Eager Loading** - Uses BaseModel relationships (not manual batch loading)
- ✅ **BaseModel ORM** - Modern Laravel-like API with relationships
- ✅ **Efficient Filtering** - Subqueries instead of JOINs for better performance
- ✅ **No N+1 Problems** - Relationships loaded in batch via `EagerLoadTrait`
- ✅ **Proper error handling** - Try-catch with error logging
- ✅ **Multilingual support** - Language-aware queries
- ✅ **Type Safety** - Model-based with fillable protection

**Query Reduction Example (50 posts with categories & tags):**
- ❌ Without Eager Loading: **101 queries** (1 + 50×2)
- ✅ With Eager Loading: **4 queries** (1 main + 2 relationships + 1 pagination)
- **Performance Improvement: 96%** 🎉

**How Eager Loading Works:**
1. Load main data (posts/pages)
2. Extract parent IDs from results
3. Query **all relationships** for these IDs in **batch** (using `whereIn`)
4. Group relationship data by parent ID
5. Attach to parent records
6. All done automatically by `EagerLoadTrait`!

**Why This Approach:**
- ✅ Uses proper ORM relationships (belongsToMany, belongsTo)
- ✅ Leverages BaseModel's EagerLoadTrait
- ✅ Avoids JOINs which are slow (subqueries for filtering instead)
- ✅ True Eager Loading, not manual batch loading
- ✅ Relationships defined in Models, reusable everywhere

