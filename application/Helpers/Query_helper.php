<?php

/**
 * Query Helper - WordPress-style database query functions
 * 
 * Provides WordPress-compatible API for querying posts, pages, and terms
 * Uses BaseModel with Eager Loading to prevent N+1 query problems
 * 
 * Functions:
 * - get_term()     Get single term by slug
 * - get_terms()    Get multiple terms with filtering
 * - get_post()     Get single post by ID or slug
 * - get_posts()    Get multiple posts with filtering & pagination
 * - get_page()     Get single page (wrapper of get_post)
 * - get_pages()    Get multiple pages (wrapper of get_posts)
 * 
 * @package App\Helpers
 */

use App\Models\PostsModel;
use App\Models\TermsModel;
use App\Models\CommentsModel;
use App\Models\UsersModel;
use System\Database\DB;

/**
 * Normalize column name by trimming table prefixes and casing.
 *
 * @param string|null $column
 * @return string|null
 */
function _normalize_column_name($column)
{
    if ($column === null) {
        return null;
    }

    $column = trim($column);
    if ($column === '') {
        return null;
    }

    if (($pos = strrpos($column, '.')) !== false) {
        $column = substr($column, $pos + 1);
    }

    return strtolower($column);
}

/**
 * Infer primary key column (with prefix if applicable) based on an order column.
 *
 * @param string|null $orderColumn
 * @return string
 */
function _primary_column_for_order($orderColumn)
{
    if ($orderColumn === null) {
        return 'id';
    }

    $orderColumn = trim($orderColumn);
    if ($orderColumn === '') {
        return 'id';
    }

    if (($pos = strrpos($orderColumn, '.')) !== false) {
        return substr($orderColumn, 0, $pos) . '.id';
    }

    return 'id';
}

/**
 * Apply cursor-based pagination to a query
 * 
 * @param object $query Query builder instance
 * @param string $orderColumn Column used for ordering
 * @param string $orderDir Order direction (ASC/DESC)
 * @param mixed $cursorValue Cursor value (last item's orderby value)
 * @param mixed|null $cursorIdValue Cursor primary key value (required when using non-PK ordering)
 * @return object Query builder with cursor applied
 */
function _apply_cursor_pagination($query, $orderColumn, $orderDir, $cursorValue, $cursorIdValue = null)
{
    if ($cursorValue === null) {
        return $query;
    }

    $primaryColumn = _primary_column_for_order($orderColumn);
    $normalizedOrderColumn = _normalize_column_name($orderColumn);
    $requiresTieBreaker = $normalizedOrderColumn !== null
        && $normalizedOrderColumn !== 'id'
        && $cursorIdValue !== null;

    $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
    $comparison = $orderDir === 'DESC' ? '<' : '>';
    $idComparison = $orderDir === 'DESC' ? '<' : '>';

    // Apply cursor condition based on order direction
    if (!$requiresTieBreaker) {
        $query->where($orderColumn, $cursorValue, $comparison);
    } else {
        $query->where(function ($q) use ($orderColumn, $comparison, $cursorValue, $primaryColumn, $idComparison, $cursorIdValue) {
            $q->where($orderColumn, $cursorValue, $comparison)
                ->orWhere(function ($sub) use ($orderColumn, $cursorValue, $primaryColumn, $idComparison, $cursorIdValue) {
                    $sub->where($orderColumn, $cursorValue)
                        ->where($primaryColumn, $cursorIdValue, $idComparison);
                });
        });
    }

    return $query;
}

/**
 * Apply pagination (cursor or offset) and limits to query
 * 
 * @param object $query Query builder instance
 * @param array $args Arguments with cursor/offset/limit
 * @param string $orderColumn Column used for ordering
 * @param string $orderDir Order direction
 */
function _apply_pagination($query, $args, $orderColumn, $orderDir)
{
    $limit = $args['limit'] ?? null;
    $offsetUsed = isset($args['offset']) && $args['offset'] !== null;
    $cursorValue = $args['cursor'] ?? null;
    $cursorId = $args['cursor_id'] ?? null;

    if ($offsetUsed) {
        $query->offset((int)$args['offset']);
    } elseif ($cursorValue !== null) {
        _apply_cursor_pagination($query, $orderColumn, $orderDir, $cursorValue, $cursorId);
    }

    if ($limit !== null) {
        $query->limit((int)$limit + 1);
    }
}

/**
 * Process pagination result with is_next logic
 * 
 * @param array $results Query results
 * @param array $args Original query arguments
 * @param string|null $orderColumn Column used for ordering (for cursor)
 * @return array Formatted result with is_next flag and cursor/page info
 */
function _result_pagination($results, $args, $orderColumn = null)
{
    $limit = $args['limit'] ?? null;
    $offsetUsed = isset($args['offset']) && $args['offset'] !== null;

    if ($limit === null) {
        return [
            'data' => $results,
            'is_next' => false,
            'page' => isset($args['paged']) ? (int)$args['paged'] : 1
        ];
    }

    $limit = (int)$limit;
    $hasNext = count($results) > $limit;

    if ($hasNext) {
        array_pop($results);
    }

    $nextCursor = null;
    $nextCursorId = null;

    if (!$offsetUsed && $orderColumn && !empty($results)) {
        $lastItem = end($results);
        $cursorKey = strpos($orderColumn, '.') !== false
            ? substr($orderColumn, strrpos($orderColumn, '.') + 1)
            : $orderColumn;
        $nextCursor = $lastItem[$cursorKey] ?? null;
        $nextCursorId = $lastItem['id'] ?? null;
    }

    $result = [
        'data' => $results,
        'is_next' => $hasNext,
        'limit' => $limit
    ];

    if ($offsetUsed) {
        $offset = (int)($args['offset'] ?? 0);
        $currentPage = $limit > 0 ? (int)floor($offset / $limit) + 1 : 1;
        $result['page'] = $currentPage;
    } else {
        if ($result['is_next']) {
            $result['cursor'] = $nextCursor;
            if ($nextCursorId !== null) {
                $result['cursor_id'] = $nextCursorId;
            }
        }
    }

    return $result;
}

/**
 * Apply custom filters to query
 * 
 * @param object $query Query builder instance
 * @param array $filters Array of filter conditions
 * @param string|null $tablePrefix Optional table prefix for columns
 * @return object Query builder with filters applied
 */
function _apply_filters($query, $filters, $tablePrefix = null)
{
    if (empty($filters) || !is_array($filters)) {
        return $query;
    }

    foreach ($filters as $filter) {
        if (!is_array($filter) || count($filter) < 2) continue;

        $column = $filter[0];
        $value = $filter[1];
        $operator = $filter[2] ?? '=';
        $boolean = isset($filter[3]) ? strtoupper($filter[3]) : 'AND';
        $isOr = ($boolean === 'OR');

        // Add table prefix if provided and column doesn't have one
        if ($tablePrefix && strpos($column, '.') === false) {
            $column = "{$tablePrefix}.{$column}";
        }

        // Apply filter based on operator and boolean type
        $operatorUpper = strtoupper($operator);

        if ($operatorUpper === 'IN') {
            if ($isOr) {
                $query->orWhereIn($column, (array)$value);
            } else {
                $query->whereIn($column, (array)$value);
            }
        } elseif ($operatorUpper === 'NOT IN') {
            if ($isOr) {
                $query->orWhereNotIn($column, (array)$value);
            } else {
                $query->whereNotIn($column, (array)$value);
            }
        } elseif ($operatorUpper === 'BETWEEN' && is_array($value) && count($value) === 2) {
            if ($isOr) {
                // Build OR BETWEEN manually using whereRaw
                $query->orWhereRaw("{$column} BETWEEN ? AND ?", [$value[0], $value[1]]);
            } else {
                $query->whereBetween($column, $value[0], $value[1]);
            }
        } elseif ($operatorUpper === 'NOT BETWEEN' && is_array($value) && count($value) === 2) {
            if ($isOr) {
                $query->orWhereRaw("NOT ({$column} BETWEEN ? AND ?)", [$value[0], $value[1]]);
            } else {
                $query->whereNotBetween($column, $value[0], $value[1]);
            }
        } else {
            // Standard where with operator
            if ($isOr) {
                $query->orWhere($column, $value, $operator);
            } else {
                $query->where($column, $value, $operator);
            }
        }
    }

    return $query;
}


if (!function_exists('get_term')) {
    /**
     * Get single term by slug, posttype and type.
     * 
     * WordPress-style function using BaseModel for better performance.
     * 
     * @param string|int $slug Term slug or ID
     * @param string $posttype Posttype name (e.g., 'posts', 'products')
     * @param string $type Term type (e.g., 'category', 'tags', 'brand')
     * @param string $lang Language code (default: APP_LANG)
     * @return array|null Term data or null if not found
     * 
     * @example
     *   $category = get_term('electronics', 'products', 'category');
     *   $tag = get_term('featured', 'posts', 'tags', 'en');
     */
    function get_term($slug, $posttype = 'posts', $type = 'category', $lang = APP_LANG)
    {
        try {
            $query = TermsModel::query();

            // Filter by ID or slug
            if (is_numeric($slug)) {
                $query->where('id', $slug);
            } else {
                $query->where('slug', $slug);
            }

            // Filter by posttype, type, and lang
            $term = $query->where('posttype', $posttype)
                ->where('type', $type)
                ->where('lang', $lang)
                ->first();

            return $term;
        } catch (Exception $e) {
            error_log('get_term error: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('get_terms')) {
    /**
     * Get multiple terms with filtering and ordering.
     * 
     * WordPress-compatible function with args similar to WP_Term_Query.
     * 
     * @param array $args {
     *     Query arguments (WordPress-style).
     * 
     *     @type string       $taxonomy    Taxonomy name (maps to 'type' column)
     *     @type string       $post_type   Post type (maps to 'posttype' column)
     *     @type string       $lang        Language code (default: APP_LANG)
     *     @type array        $slug__in    Array of term slugs to include
     *     @type array        $slug__not_in Array of term slugs to exclude
     *     @type array        $include     Array of term IDs to include
     *     @type array        $exclude     Array of term IDs to exclude
     *     @type string       $search      Search term in name/slug
     *     @type string       $orderby     Order by field (default: 'name')
     *     @type string       $order       ASC or DESC (default: 'ASC')
     *     @type int          $limit      Limit limit of results
     *     @type int          $offset      Offset for pagination
     *     @type bool         $count       Return count only (default: false)
     *     @type array        $fields      Specific fields to select (default: all)
     * }
     * @return array|int Array of terms or count if $args['count'] is true
     * 
     * @example
     *   // Get all active categories for products
     *   $categories = get_terms([
     *       'taxonomy' => 'category',
     *       'post_type' => 'products',
     *       'orderby' => 'name',
     *       'order' => 'ASC'
     *   ]);
     * 
     *   // Get specific tags
     *   $tags = get_terms([
     *       'taxonomy' => 'tags',
     *       'post_type' => 'posts',
     *       'slug__in' => ['featured', 'trending'],
     *       'fields' => ['id', 'name', 'slug']
     *   ]);
     */
    function get_terms($args = [])
    {
        // Default arguments
        $defaults = [
            'taxonomy'     => 'category',
            'post_type'    => 'posts',
            'lang'         => APP_LANG,
            'slug__in'     => [],
            'slug__not_in' => [],
            'include'      => [],
            'exclude'      => [],
            'search'       => '',
            'search_columns'    => ['name', 'slug'],
            'orderby'      => 'id',
            'order'        => 'DESC',
            'limit'        => null,
            'offset'       => null,
            'cursor'       => null,  // Cursor-based pagination
            'cursor_id'    => null,
            'count'        => false,
            'fields'       => '*',
            'filters'      => [],
        ];
        $args = array_merge($defaults, $args);

        if (isset($args['posttype'])) {
            $args['post_type'] = $args['posttype'];
            unset($args['posttype']);
        }

        if (isset($args['posts_per_page']) && (int)$args['posts_per_page'] > 0) {
            $args['limit'] = (int)$args['posts_per_page'];
        }

        if (isset($args['paged']) && (int)$args['paged'] >= 1 && $args['limit'] !== null) {
            $args['offset'] = ((int)$args['paged'] - 1) * (int)$args['limit'];
        }

        try {
            // Build query using BaseModel
            $query = TermsModel::query();

            // Get table name
            $tableName = APP_PREFIX . 'terms';

            // Select fields
            if ($args['fields'] !== '*') {
                if (is_array($args['fields'])) {
                    $query->select(...$args['fields']);
                } else {
                    $query->select($args['fields']);
                }
            }

            // Taxonomy (type)
            if (!empty($args['taxonomy'])) {
                $query->where('type', $args['taxonomy']);
            }

            // Post type
            if (!empty($args['post_type'])) {
                $query->where('posttype', $args['post_type']);
            }

            // Language
            if (!empty($args['lang'])) {
                $query->where('lang', $args['lang']);
            }

            // Include specific slugs
            if (!empty($args['slug__in'])) {
                $query->whereIn('slug', (array)$args['slug__in']);
            }

            // Exclude specific slugs
            if (!empty($args['slug__not_in'])) {
                $query->whereNotIn('slug', (array)$args['slug__not_in']);
            }

            // Apply custom filters
            _apply_filters($query, $args['filters'] ?? []);

            // Include specific IDs
            if (!empty($args['include'])) {
                $query->whereIn('id', (array)$args['include']);
            }

            // Exclude specific IDs
            if (!empty($args['exclude'])) {
                $query->whereNotIn('id', (array)$args['exclude']);
            }

            // Search functionality
            if (!empty($args['search'])) {
                if (empty($args['search_columns'])) {
                    $args['search_columns'] = ['name'];
                }
                $search = keyword_slug($args['search']);
                $searchColumns = (array)$args['search_columns'];

                $query->whereGroup(function ($q) use ($search, $searchColumns, $tableName) {
                    foreach ($searchColumns as $column) {
                        $q->orWhere("{$tableName}.{$column}", '%' . $search . '%', 'LIKE');
                    }
                });
            }

            // Count only
            if ($args['count']) {
                return $query->count();
            }

            // Order by
            $orderDir = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
            $orderColumn = $args['orderby'];
            $primaryColumn = _primary_column_for_order($orderColumn);
            $query->orderBy($orderColumn, $orderDir);
            if (_normalize_column_name($orderColumn) !== _normalize_column_name($primaryColumn)) {
                $query->orderBy($primaryColumn, $orderDir);
            }

            // Apply pagination (cursor or offset)
            _apply_pagination($query, $args, $orderColumn, $orderDir);

            $results = $query->get();
            return _result_pagination($results, $args, $orderColumn);
        } catch (Exception $e) {
            error_log('get_terms error: ' . $e->getMessage());
            return $args['count'] ? 0 : [];
        }
    }
}

if (!function_exists('get_post')) {
    /**
     * Get single post by ID or slug with optional relationship loading.
     * 
     * WordPress-compatible function using BaseModel with Eager Loading.
     * 
     * @param int|array $post Post ID or array of arguments
     * @param string $post_type Post type (default: 'posts')
     * @param array $args Additional query arguments
     * @return array|null Post data or null if not found
     * 
     * @example
     *   // Get post by ID
     *   $post = get_post(123);
     * 
     *   // Get post with categories using Eager Loading
     *   $post = get_post([
     *       'id' => 123,
     *       'post_type' => 'products',
     *       'with_categories' => true
     *   ]);
     * 
     *   // Get post by slug
     *   $post = get_post([
     *       'slug' => 'my-product-slug',
     *       'post_type' => 'products',
     *       'with_categories' => true,
     *       'with_tags' => true
     *   ]);
     */
    function get_post($post = null, $post_type = 'posts', $args = [])
    {
        // Handle different parameter formats
        if (is_numeric($post)) {
            $args = array_merge($args, ['id' => $post, 'post_type' => $post_type]);
        } elseif (is_string($post)) {
            $args = array_merge($args, ['slug' => $post, 'post_type' => $post_type]);
        } elseif (is_array($post)) {
            $args = array_merge($post, $args);
            if (isset($args['post_type']) && !empty($args['post_type'])) {
                $post_type = $args['post_type'];
            }
        } else {
            return null;
        }

        // Default arguments
        $defaults = [
            'id'               => 0,
            'slug'             => '',
            'post_type'        => $post_type,
            'post_status'      => 'active',
            'lang'             => APP_LANG,
            'fields'           => '*',
            'filters'          => [],
            'with_terms'  => false,
            'with_categories'  => false,
            'with_tags'        => false,
            'with_author'      => false,
            'with_comments'    => false,
        ];

        $args = array_merge($defaults, $args);

        // Validate inputs
        if (empty($args['id']) && empty($args['slug'])) {
            return null;
        }
        if (isset($args['posttype'])) {
            $args['post_type'] = $args['posttype'];
            unset($args['posttype']);
        }

        try {
            // Get table name
            $tableName = posttype_name($args['post_type'], $args['lang']);
            if (empty($tableName)) {
                return null;
            }

            // Use PostsModel::for() for dynamic table
            $query = PostsModel::for($tableName, 'id', null, false)->newQuery();

            // ✅ Handle fields parameter (support Point fields)
            $selectFields = $args['fields'];
            $pointFieldNames = [];

            if ($selectFields !== '*') {
                if (is_array($selectFields)) {
                    // Check if array of field definitions or field names
                    $firstItem = $selectFields[0] ?? null;

                    if (is_array($firstItem) && isset($firstItem['field_name'])) {
                        // Array of field definitions: [['field_name' => 'title', 'type' => 'Text'], ...]
                        $fieldNames = [];
                        foreach ($selectFields as $fieldDef) {
                            $fieldName = $fieldDef['field_name'] ?? '';
                            if (!empty($fieldName)) {
                                $fieldNames[] = $fieldName;
                                // Track Point fields
                                if (($fieldDef['type'] ?? '') === 'Point') {
                                    $pointFieldNames[] = $fieldName;
                                }
                            }
                        }
                        if (!in_array('id', $fieldNames)) {
                            $fieldNames[] = 'id';
                        }
                        $query->select(...$fieldNames);
                    } else {
                        // Array of field names: ['id', 'title', 'toado']
                        if (!in_array('id', $selectFields)) {
                            $selectFields[] = 'id';
                        }
                        $query->select(...$selectFields);
                    }
                } else {
                    // String format: "id, name, toado" or "*"
                    if ($selectFields !== '*') {
                        $fieldsArray = array_map('trim', explode(',', $selectFields));
                        if (!in_array('id', $fieldsArray)) {
                            $fieldsArray[] = 'id';
                        }
                        $query->select(...$fieldsArray);
                    }
                }
            }

            // ✅ Add ST_AsText() for Point fields if detected
            if (!empty($pointFieldNames)) {
                foreach ($pointFieldNames as $pointFieldName) {
                    $query->addSelect(DB::raw("ST_AsText(`{$pointFieldName}`) as `{$pointFieldName}`"));
                }
            }

            // Filter by ID or slug
            if (!empty($args['id'])) {
                $query->where('id', $args['id']);
            } elseif (!empty($args['slug'])) {
                $query->where('slug', $args['slug']);
            }

            // Filter by status (if not already in custom filters)
            if (!empty($args['post_status'])) {
                $query->where('status', $args['post_status']);
            }
            // Apply custom filters with table prefix
            _apply_filters($query, $args['filters'] ?? [], $tableName);

            // Eager load relationships using associative array format (prevent N+1!)
            $withRelations = [];
            global $post_table_query;
            $post_table_query = $args['post_type'];
            $termsTable = APP_PREFIX . 'terms';

            if ($args['with_terms']) {
                $withRelations['terms'] = function ($q) use ($args, $termsTable) {
                    $q->where("{$termsTable}.posttype", $args['post_type'])
                        ->where("{$termsTable}.status", 'active')
                        ->where("{$termsTable}.lang", $args['lang']);
                };
            }

            if ($args['with_categories']) {
                $withRelations['categories'] = function ($q) use ($args, $termsTable) {
                    $q->where("{$termsTable}.type", 'category')
                        ->where("{$termsTable}.posttype", $args['post_type'])
                        ->where("{$termsTable}.status", 'active')
                        ->where("{$termsTable}.lang", $args['lang']);
                };
            }

            if ($args['with_tags']) {
                $withRelations['tags'] = function ($q) use ($args, $termsTable) {
                    $q->where("{$termsTable}.type", 'tag')
                        ->where("{$termsTable}.posttype", $args['post_type'])
                        ->where("{$termsTable}.status", 'active')
                        ->where("{$termsTable}.lang", $args['lang']);
                };
            }

            if ($args['with_author']) {
                // Author relationship with column selection
                $query->with('author:id,username,fullname,avatar,birthday,phone,personal,address,country,online,display,activity_at,created_at,updated_at');
            }

            if ($args['with_comments']) {
                $withRelations['comments'] = function ($q) use ($args) {
                    $commentsTable = APP_PREFIX . 'comments';
                    $q->where("{$commentsTable}.status", 'active')
                        ->where("{$commentsTable}.posttype", $args['post_type'])
                        ->orderBy("{$commentsTable}.id", 'DESC');
                };
            }

            // Apply eager loading if any
            if (!empty($withRelations)) {
                $query->with($withRelations);
            }
            $result = $query->first();
            // ✅ Parse Point fields từ WKT text → lat/lng object
            if (!empty($result) && !empty($pointFieldNames)) {
                foreach ($pointFieldNames as $pointFieldName) {
                    if (!empty($pointFieldName) && isset($result[$pointFieldName])) {
                        $wkt = $result[$pointFieldName];
                        if (is_string($wkt) && preg_match('/POINT\(\s*([-0-9\.]+)\s+([-0-9\.]+)\s*\)/i', $wkt, $m)) {
                            $lng = (float)$m[1];
                            $lat = (float)$m[2];
                            $result[$pointFieldName] = ['lat' => $lat, 'lng' => $lng];
                        }
                    }
                }
            }
            return $result;
        } catch (Exception $e) {
            error_log('get_post error: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('get_posts')) {
    /**
     * Get multiple posts with filtering, pagination, and relationship loading.
     * 
     * WordPress-compatible function with extensive args support.
     * Uses BaseModel Eager Loading to prevent N+1 query problems.
     * 
     * @param array $args {
     *     Query arguments (WordPress-style).
     * 
     *     @type string       $post_type       Post type (default: 'posts')
     *     @type string|array $post_status     Post status (default: 'active')
     *     @type int          $posts_per_page  Posts per page (default: -1 = all)
     *     @type int          $paged           Current page number (default: 1)
     *     @type string       $orderby         Order by field (default: 'id')
     *     @type string       $order           ASC or DESC (default: 'DESC')
     *     @type array        $post__in        Include specific post IDs
     *     @type array        $post__not_in    Exclude specific post IDs
     *     @type array        $category__in    Include posts in these categories
     *     @type array        $category__not_in Exclude posts in these categories
     *     @type array        $tag__in         Include posts with these tags
     *     @type array        $tag__not_in     Exclude posts with these tags
     *     @type string       $s               Search term
     *     @type array        $search_columns  Columns to search in
     *     @type string       $lang            Language code
     *     @type array        $fields          Specific fields to select
     *     @type bool         $with_categories Load categories (Eager Loading)
     *     @type bool         $with_tags       Load tags (Eager Loading)
     *     @type bool         $with_author     Load author info (Eager Loading)
     *     @type bool         $count    Return total count in result
     *     @type array        $meta_query      Custom field queries
     * }
     * @return array Array of posts or pagination result
     * 
     * @example
     *   // Get latest 10 posts
     *   $posts = get_posts([
     *       'posts_per_page' => 10,
     *       'orderby' => 'id',
     *       'order' => 'DESC'
     *   ]);
     * 
     *   // Get products in category with eager loading
     *   $products = get_posts([
     *       'post_type' => 'products',
     *       'category__in' => [1, 2, 3],
     *       'posts_per_page' => 20,
     *       'paged' => 1,
     *       'with_categories' => true,  // Eager Loading!
     *       'with_tags' => true
     *   ]);
     * 
     *   // Search posts
     *   $results = get_posts([
     *       'search' => 'keyword',
     *       'search_columns' => ['title', 'content'],
     *       'post_status' => 'active'
     *   ]);
     */
    function get_posts($args = [])
    {
        // Default arguments
        $defaults = [
            'post_type'        => 'posts',
            'post_status'      => 'active',
            'orderby'          => 'id',
            'order'            => 'DESC',
            'limit'            => null,
            'offset'           => null,
            'cursor'           => null,
            'cursor_id'        => null,
            'post__in'         => [],
            'post__not_in'     => [],
            'category__in'     => [],
            'category__not_in' => [],
            'tag__in'          => [],
            'tag__not_in'      => [],
            'search'           => '',
            'search_columns'   => ['search_string'],
            'lang'             => APP_LANG,
            'fields'           => '*',
            'filters'          => [],
            'with_terms'  => false,
            'with_categories'  => false,
            'with_tags'        => false,
            'with_author'      => false,
            'with_comments'    => false,
            'count'            => false,
        ];
        if (isset($args['status'])) {
            $args['post_status'] = $args['status'];
        }
        if (isset($args['posttype'])) {
            $args['post_type'] = $args['posttype'];
            unset($args['posttype']);
        }

        $args = array_merge($defaults, $args);

        if (isset($args['sort']) && is_array($args['sort'])) {
            $args['orderby'] = $args['sort'][0] ?? $args['orderby'];
            $args['order'] = $args['sort'][1] ?? $args['order'];
        }
        if (isset($args['perPage']) && (int)$args['perPage'] > 0) {
            $args['limit'] = (int)$args['perPage'];
        }
        if (isset($args['posts_per_page']) && (int)$args['posts_per_page'] > 0) {
            $args['limit'] = (int)$args['posts_per_page'];
        }

        if (isset($args['paged']) && (int)$args['paged'] >= 1 && $args['limit'] !== null) {
            $args['offset'] = ((int)$args['paged'] - 1) * (int)$args['limit'];
        }

        try {
            // Get table name
            $tableName = posttype_name($args['post_type'], $args['lang']);
            if (empty($tableName)) {
                return [];
            }

            $pivotTable = table_postrel($args['post_type']);

            // Use PostsModel::for() for dynamic table
            $query = PostsModel::for($tableName, 'id', null, false)->newQuery();

            // ✅ Handle fields parameter (support Point fields)
            $selectFields = $args['fields'];
            $pointFieldNames = [];

            if ($selectFields !== '*') {
                if (is_array($selectFields)) {
                    // Check if array of field definitions or field names
                    $firstItem = $selectFields[0] ?? null;

                    if (is_array($firstItem) && isset($firstItem['field_name'])) {
                        // Array of field definitions: [['field_name' => 'title', 'type' => 'Text'], ...]
                        $fieldNames = [];
                        foreach ($selectFields as $fieldDef) {
                            $fieldName = $fieldDef['field_name'] ?? '';
                            if (!empty($fieldName)) {
                                $fieldNames[] = $fieldName;
                                // Track Point fields
                                if (($fieldDef['type'] ?? '') === 'Point') {
                                    $pointFieldNames[] = $fieldName;
                                }
                            }
                        }
                        if (!in_array('id', $fieldNames)) {
                            $fieldNames[] = 'id';
                        }
                        $query->select(...$fieldNames);
                    } else {
                        // Array of field names: ['id', 'title', 'toado']
                        if (!in_array('id', $selectFields)) {
                            $selectFields[] = 'id';
                        }
                        $query->select(...$selectFields);
                    }
                } else {
                    // String format: "id, name, toado"
                    if ($selectFields !== '*') {
                        $fieldsArray = array_map('trim', explode(',', $selectFields));
                        if (!in_array('id', $fieldsArray)) {
                            $fieldsArray[] = 'id';
                        }
                        $query->select(...$fieldsArray);
                    }
                }
            }

            // ✅ Add ST_AsText() for Point fields if detected
            if (!empty($pointFieldNames)) {
                foreach ($pointFieldNames as $pointFieldName) {
                    $query->addSelect(DB::raw("ST_AsText(`{$tableName}`.`{$pointFieldName}`) as `{$pointFieldName}`"));
                }
            }

            // Filter by status (if not already in custom filters)
            if (!empty($args['post_status'])) {
                if (is_array($args['post_status'])) {
                    $query->whereIn('status', $args['post_status']);
                } else {
                    $query->where('status', $args['post_status']);
                }
            }

            // Include specific post IDs
            if (!empty($args['post__in'])) {
                $query->whereIn("{$tableName}.id", (array)$args['post__in']);
            }

            // Exclude specific post IDs
            if (!empty($args['post__not_in'])) {
                $query->whereNotIn("{$tableName}.id", (array)$args['post__not_in']);
            }

            // Apply custom filters with table prefix
            _apply_filters($query, $args['filters'] ?? [], $tableName);

            // Search functionality
            if (!empty($args['search']) && !empty($args['search_columns'])) {
                $search = keyword_slug($args['search']);
                $searchColumns = (array)$args['search_columns'];

                $query->whereGroup(function ($q) use ($search, $searchColumns, $tableName) {
                    foreach ($searchColumns as $column) {
                        $q->orWhere("{$tableName}.{$column}", '%' . $search . '%', 'LIKE');
                    }
                });
            }

            // Category filtering (using subquery - efficient!)
            $hasCategoryFilter = !empty($args['category__in']) || !empty($args['category__not_in']);
            if ($hasCategoryFilter) {
                $termTable = DB::tableName('terms');

                $query->whereInSub(
                    "{$tableName}.id",
                    function ($sub) use ($pivotTable, $termTable, $args) {
                        $sub->table($pivotTable)
                            ->select(["{$pivotTable}.post_id"])
                            ->join($termTable, "{$termTable}.id_main", '=', "{$pivotTable}.rel_id")
                            ->where("{$termTable}.type", 'category');

                        if (!empty($args['category__in'])) {
                            $sub->whereIn("{$termTable}.id_main", (array)$args['category__in']);
                        }

                        if (!empty($args['category__not_in'])) {
                            $sub->whereNotIn("{$termTable}.id_main", (array)$args['category__not_in']);
                        }
                    }
                );
            }

            // Tag filtering (using subquery - efficient!)
            $hasTagFilter = !empty($args['tag__in']) || !empty($args['tag__not_in']);
            if ($hasTagFilter) {
                $termTable = DB::tableName('terms');

                $query->whereInSub(
                    "{$tableName}.id",
                    function ($sub) use ($pivotTable, $termTable, $args) {
                        $sub->table($pivotTable)
                            ->select(["{$pivotTable}.post_id"])
                            ->join($termTable, "{$termTable}.id_main", '=', "{$pivotTable}.rel_id")
                            ->where("{$termTable}.type", 'tags');

                        if (!empty($args['tag__in'])) {
                            $sub->whereIn("{$termTable}.id_main", (array)$args['tag__in']);
                        }

                        if (!empty($args['tag__not_in'])) {
                            $sub->whereNotIn("{$termTable}.id_main", (array)$args['tag__not_in']);
                        }
                    }
                );
            }

            // Eager load relationships using associative array format (prevent N+1!)
            $withRelations = [];
            global $post_table_query;
            $post_table_query = $args['post_type'];
            $termsTable = APP_PREFIX . 'terms';

            if ($args['with_terms']) {
                $withRelations['terms'] = function ($q) use ($args, $termsTable) {
                    $q->where("{$termsTable}.posttype", $args['post_type'])
                        ->where("{$termsTable}.status", 'active')
                        ->where("{$termsTable}.lang", $args['lang']);
                };
            }

            if ($args['with_categories']) {
                $withRelations['categories'] = function ($q) use ($args, $termsTable) {
                    // Prefix all columns with table name to avoid ambiguous errors after JOIN
                    $q->where("{$termsTable}.type", 'category')
                        ->where("{$termsTable}.posttype", $args['post_type'])
                        ->where("{$termsTable}.status", 'active')
                        ->where("{$termsTable}.lang", $args['lang']);
                };
            }

            if ($args['with_tags']) {
                $withRelations['tags'] = function ($q) use ($args, $termsTable) {
                    // Prefix all columns with table name to avoid ambiguous errors after JOIN
                    $q->where("{$termsTable}.type", 'tag')
                        ->where("{$termsTable}.posttype", $args['post_type'])
                        ->where("{$termsTable}.status", 'active')
                        ->where("{$termsTable}.lang", $args['lang']);
                };
            }

            if ($args['with_author']) {
                // Author relationship with essential columns only
                $query->with('author:id,username,fullname,avatar,email,phone,role,status,online,created_at');
            }

            if ($args['with_comments']) {
                $withRelations['comments'] = function ($q) use ($args) {
                    $commentsTable = APP_PREFIX . 'comments';
                    $q->where("{$commentsTable}.status", 'active')
                        ->where("{$commentsTable}.posttype", $args['post_type'])
                        ->orderBy("{$commentsTable}.id", 'DESC');
                };
            }

            // Apply eager loading if any
            if (!empty($withRelations)) {
                $query->with($withRelations);
            }

            // Order by
            $orderDir = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
            $orderColumn = $args['orderby'];

            // Add table prefix if not already present
            if (strpos($orderColumn, '.') === false) {
                $orderColumn = "{$tableName}.{$orderColumn}";
            }

            $primaryColumn = _primary_column_for_order($orderColumn);
            $query->orderBy($orderColumn, $orderDir);
            if (_normalize_column_name($orderColumn) !== _normalize_column_name($primaryColumn)) {
                $query->orderBy($primaryColumn, $orderDir);
            }

            // Apply pagination (cursor or offset)
            _apply_pagination($query, $args, $orderColumn, $orderDir);

            $results = $query->get();

            // ✅ Parse Point fields từ WKT text → lat/lng object
            if (!empty($results) && !empty($pointFieldNames)) {
                foreach ($results as &$result) {
                    foreach ($pointFieldNames as $pointFieldName) {
                        if (!empty($pointFieldName) && isset($result[$pointFieldName])) {
                            $wkt = $result[$pointFieldName];
                            if (is_string($wkt) && preg_match('/POINT\(\s*([-0-9\.]+)\s+([-0-9\.]+)\s*\)/i', $wkt, $m)) {
                                $lng = (float)$m[1];
                                $lat = (float)$m[2];
                                $result[$pointFieldName] = ['lat' => $lat, 'lng' => $lng];
                            }
                        }
                    }
                }
            }

            return _result_pagination($results, $args, $orderColumn);
        } catch (Exception $e) {
            error_log('get_posts error: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('get_page')) {
    /**
     * Get single page by ID or slug.
     * 
     * Wrapper function for get_post() with post_type='pages'.
     * 
     * @param int|array $page Page ID or array of arguments
     * @param array $args Additional query arguments
     * @return array|null Page data or null if not found
     * 
     * @example
     *   // Get page by ID
     *   $page = get_page(123);
     * 
     *   // Get page by slug with categories
     *   $page = get_page([
     *       'slug' => 'about-us',
     *       'with_categories' => true
     *   ]);
     */
    function get_page($page = null, $args = [])
    {
        if (is_numeric($page)) {
            $args['id'] = $page;
        } elseif (is_array($page)) {
            $args = array_merge($page, $args);
        }

        $args['post_type'] = 'pages';

        return get_post($args);
    }
}

if (!function_exists('get_pages')) {
    /**
     * Get multiple pages with filtering and pagination.
     * 
     * Wrapper function for get_posts() with post_type='pages'.
     * 
     * @param array $args Query arguments (see get_posts for details)
     * @return array Array of pages or pagination result
     */
    function get_pages($args = [])
    {
        $args['post_type'] = 'pages';

        return get_posts($args);
    }
}

if (!function_exists('get_post_comments')) {
    /**
     * Get comments for a specific post or multiple posts
     * 
     * @param int|array $post_id Single post ID or array of post IDs
     * @param string $post_type Post type (default: 'posts')
     * @param array $args Additional arguments
     * @return array Array of comments
     * 
     * @example
     *   // Get comments for one post
     *   $comments = get_post_comments(123, 'blogs');
     * 
     *   // Get comments for multiple posts
     *   $comments = get_post_comments([1, 2, 3], 'blogs', ['with_author' => true]);
     */
    function get_post_comments($post_id, $post_type = 'posts', $args = [])
    {
        // Convenience wrapper - convert to get_comments() format
        if (is_array($post_id)) {
            $args['post_ids'] = $post_id;
        } else {
            $args['post_id'] = $post_id;
        }

        $args['post_type'] = $post_type;

        // Call main get_comments() function
        return get_comments($args);
    }
}

if (!function_exists('get_comment_replies')) {
    /**
     * Get child comments (replies) of a parent comment
     * 
     * @param int $parent_id Parent comment ID
     * @param array $args Additional arguments
     * @return array Array of child comments
     * 
     * @example
     *   // Get all replies of a comment
     *   $replies = get_comment_replies(456);
     * 
     *   // Get approved replies with user info
     *   $replies = get_comment_replies(456, [
     *       'status' => 'active',
     *       'with_author' => true
     *   ]);
     */
    function get_comment_replies($parent_id, $args = [])
    {
        // Convenience wrapper - get child comments of a parent
        $args['parent'] = $parent_id;

        // Default order for replies is ASC (oldest first)
        if (!isset($args['order'])) {
            $args['order'] = 'ASC';
        }

        // Call main get_comments() function
        return get_comments($args);
    }
}

if (!function_exists('get_comments')) {
    /**
     * Get comments with filtering and optional relationship loading.
     * 
     * @param array $args {
     *     Query arguments.
     * 
     *     @type int          $post_id         Post ID to get comments for
     *     @type array        $post_ids        Multiple post IDs
     *     @type string       $post_type       Post type (default: 'posts')
     *     @type string       $status          Comment status (default: 'active')
     *     @type int          $user_id         User ID to filter by
     *     @type int          $parent          Parent comment ID (0 = top level, null = all)
     *     @type string       $orderby         Order by field (default: 'id')
     *     @type string       $order           ASC or DESC (default: 'DESC')
     *     @type int          $limit           Limit results
     *     @type int          $offset          Offset for pagination
     *     @type bool         $with_author       Load user info (Eager Loading)
     *     @type bool         $with_replies    Load replies (Eager Loading)
     *     @type bool         $count           Return count only
     *     @type array        $filters         Custom filters
     * }
     * @return array Array of comments
     * 
     * @example
     *   // Get comments for a post
     *   $comments = get_comments([
     *       'post_id' => 123,
     *       'post_type' => 'products',
     *       'with_author' => true
     *   ]);
     * 
     *   // Get top-level comments only
     *   $comments = get_comments([
     *       'post_id' => 123,
     *       'parent' => 0,
     *       'with_replies' => true
     *   ]);
     * 
     *   // Get comments from multiple posts
     *   $comments = get_comments([
     *       'post_ids' => [1, 2, 3],
     *       'post_type' => 'blogs'
     *   ]);
     */
    function get_comments($args = [])
    {
        // Default arguments
        $defaults = [
            'post_id'        => 0,
            'post_ids'       => [],
            'post_type'      => 'posts',
            'status'         => 'active',
            'user_id'        => 0,
            'parent'         => null,
            'orderby'        => 'id',
            'order'          => 'DESC',
            'limit'          => null,
            'offset'         => null,
            'cursor'         => null,
            'cursor_id'      => null,
            'with_author'      => false,
            'with_replies'   => false,
            'count'          => false,
            'filters'        => [],
        ];

        $args = array_merge($defaults, $args);

        if (isset($args['posts_per_page']) && (int)$args['posts_per_page'] > 0) {
            $args['limit'] = (int)$args['posts_per_page'];
        }

        if (isset($args['paged']) && (int)$args['paged'] >= 1 && $args['limit'] !== null) {
            $args['offset'] = ((int)$args['paged'] - 1) * (int)$args['limit'];
        }

        try {
            // Build query using BaseModel
            $query = CommentsModel::query();

            // Filter by post_id or post_ids
            if (!empty($args['post_ids']) && is_array($args['post_ids'])) {
                $query->whereIn('post_id', $args['post_ids']);
            } elseif (!empty($args['post_id'])) {
                $query->where('post_id', $args['post_id']);
            }

            // Filter by post_type
            if (!empty($args['post_type'])) {
                $query->where('posttype', $args['post_type']);
            }

            // Filter by status
            if (!empty($args['status'])) {
                $query->where('status', $args['status']);
            }

            // Filter by user_id
            if (!empty($args['user_id'])) {
                $query->where('user_id', $args['user_id']);
            }

            // Filter by parent (null = all, 0 = top level, >0 = specific parent)
            if ($args['parent'] !== null) {
                $query->where('par_comment', $args['parent']);
            }

            // Apply custom filters
            _apply_filters($query, $args['filters'] ?? []);

            // Count only
            if ($args['count']) {
                return $query->count();
            }

            // Eager loading with associative array
            $withRelations = [];

            if ($args['with_author']) {
                $query->with('user:id,username,fullname,avatar,email,phone,role');
            }

            if ($args['with_replies']) {
                $withRelations['replies'] = function ($q) {
                    $q->where('status', 'active')
                        ->orderBy('id', 'ASC');
                };
            }

            // Apply eager loading
            if (!empty($withRelations)) {
                $query->with($withRelations);
            }

            // Order by
            $orderDir = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
            $orderColumn = $args['orderby'];
            $primaryColumn = _primary_column_for_order($orderColumn);
            $query->orderBy($orderColumn, $orderDir);
            if (_normalize_column_name($orderColumn) !== _normalize_column_name($primaryColumn)) {
                $query->orderBy($primaryColumn, $orderDir);
            }

            // Apply pagination (cursor or offset)
            _apply_pagination($query, $args, $orderColumn, $orderDir);

            $results = $query->get();
            return _result_pagination($results, $args, $orderColumn);
        } catch (Exception $e) {
            error_log('get_comments error: ' . $e->getMessage());
            return $args['count'] ? 0 : [];
        }
    }
}

if (!function_exists('get_authors')) {
    /**
     * Get author(s) by ID, slug, or with filters
     * 
     * @param int|string|array|null $author Author ID, slug, array of IDs/slugs, or null for args-only
     * @param array $args Additional arguments
     * @return array|null Single author or array of authors
     * 
     * @example
     *   // Get author by ID
     *   $author = get_authors(1);
     * 
     *   // Get author by username
     *   $author = get_authors('admin');
     * 
     *   // Get multiple authors by IDs
     *   $authors = get_authors([1, 2, 3]);
     * 
     *   // Get multiple authors by usernames
     *   $authors = get_authors(['admin', 'editor', 'john']);
     * 
     *   // Get with filters
     *   $authors = get_authors(null, [
     *       'role' => 'author',
     *       'status' => 'active',
     *       'limit' => 10,
     *       'orderby' => 'fullname'
     *   ]);
     * 
     *   // Get with custom filters
     *   $authors = get_authors(null, [
     *       'filters' => [
     *           ['role', ['admin', 'moderator'], 'IN'],
     *           ['status', 'active']
     *       ],
     *       'fields' => ['id', 'username', 'fullname', 'email', 'avatar']
     *   ]);
     */
    function get_authors($author = null, $args = [])
    {
        $defaults = [
            'fields'   => '*',
            'status'   => 'active',
            'role'     => null,
            'orderby'  => 'id',
            'order'    => 'ASC',
            'limit'    => null,
            'offset'   => null,
            'filters'  => [],
            'count'    => false,
        ];

        $args = array_merge($defaults, $args);

        if (isset($args['posts_per_page']) && (int)$args['posts_per_page'] > 0) {
            $args['limit'] = (int)$args['posts_per_page'];
        }

        if (isset($args['paged']) && (int)$args['paged'] >= 1 && $args['limit'] !== null) {
            $args['offset'] = ((int)$args['paged'] - 1) * (int)$args['limit'];
        }

        try {
            $query = UsersModel::query();

            // Handle different input formats
            $returnSingle = false;
            if ($author !== null) {
                if (is_numeric($author)) {
                    // Single ID
                    $query->where('id', $author);
                    $returnSingle = true;
                } elseif (is_string($author)) {
                    // Single username/slug
                    $query->where('username', $author);
                    $returnSingle = true;
                } elseif (is_array($author) && !empty($author)) {
                    // Array of IDs or usernames
                    $firstItem = $author[0];
                    if (is_numeric($firstItem)) {
                        // Array of IDs
                        $query->whereIn('id', $author);
                    } else {
                        // Array of usernames
                        $query->whereIn('username', $author);
                    }
                }
            }

            // Select fields
            if ($args['fields'] !== '*') {
                if (is_array($args['fields'])) {
                    $query->select(...$args['fields']);
                } else {
                    $query->select($args['fields']);
                }
            }

            // Apply custom filters (use _apply_filters helper)
            _apply_filters($query, $args['filters'] ?? []);

            // Filter by status
            if (!empty($args['status'])) {
                $query->where('status', $args['status']);
            }

            // Filter by role
            if (!empty($args['role'])) {
                $query->where('role', $args['role']);
            }

            // Count only
            if ($args['count']) {
                return $query->count();
            }

            // Order by
            $orderDir = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
            $query->orderBy($args['orderby'], $orderDir);

            // Limit & Offset
            if ($args['limit'] !== null) {
                $query->limit((int)$args['limit']);
            }
            if ($args['offset'] !== null) {
                $query->offset((int)$args['offset']);
            }

            // Execute query
            if ($returnSingle) {
                return $query->first();
            }

            return $query->get();
        } catch (Exception $e) {
            error_log('get_authors error: ' . $e->getMessage());
            return $returnSingle ? null : [];
        }
    }
}

if (!function_exists('get_posttypes')) {
    /**
     * Get all post types or filtered post types
     * 
     * @param array $args {
     *     Query arguments.
     * 
     *     @type string       $status          Posttype status (default: 'active')
     *     @type array        $slug__in        Include specific slugs
     *     @type array        $slug__not_in    Exclude specific slugs
     *     @type string       $orderby         Order by field (default: 'id')
     *     @type string       $order           ASC or DESC (default: 'ASC')
     *     @type int          $limit           Limit results
     *     @type array        $filters         Custom filters
     * }
     * @return array Array of posttypes
     * 
     * @example
     *   // Get all active posttypes
     *   $posttypes = get_posttypes();
     * 
     *   // Get specific posttypes
     *   $posttypes = get_posttypes([
     *       'slug__in' => ['posts', 'blogs', 'products'],
     *       'status' => 'active'
     *   ]);
     * 
     *   // Get with custom filters
     *   $posttypes = get_posttypes([
     *       'filters' => [
     *           ['menu', '', '!='],  // Has menu
     *           ['status', 'active']
     *       ]
     *   ]);
     */
    function get_posttypes($args = [])
    {
        $defaults = [
            'status'       => 'active',
            'slug__in'     => [],
            'slug__not_in' => [],
            'orderby'      => 'id',
            'order'        => 'ASC',
            'limit'        => null,
            'offset'       => null,
            'cursor'       => null,
            'cursor_id'    => null,
            'filters'      => [],
        ];

        $args = array_merge($defaults, $args);

        try {
            $posttypeTable = APP_PREFIX . 'posttype';
            $query = DB::table($posttypeTable);

            // Apply custom filters
            _apply_filters($query, $args['filters'] ?? []);

            // Filter by status
            if (!empty($args['status'])) {
                $query->where('status', $args['status']);
            }

            // Include specific slugs
            if (!empty($args['slug__in'])) {
                $query->whereIn('slug', (array)$args['slug__in']);
            }

            // Exclude specific slugs
            if (!empty($args['slug__not_in'])) {
                $query->whereNotIn('slug', (array)$args['slug__not_in']);
            }

            // Order by
            $orderDir = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
            $orderColumn = $args['orderby'];
            $primaryColumn = _primary_column_for_order($orderColumn);
            $query->orderBy($orderColumn, $orderDir);
            if (_normalize_column_name($orderColumn) !== _normalize_column_name($primaryColumn)) {
                $query->orderBy($primaryColumn, $orderDir);
            }

            // Apply pagination (cursor or offset)
            _apply_pagination($query, $args, $orderColumn, $orderDir);

            $results = $query->get();
            return _result_pagination($results, $args, $orderColumn);
        } catch (Exception $e) {
            error_log('get_posttypes error: ' . $e->getMessage());
            return [];
        }
    }
}
