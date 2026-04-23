<?php
namespace System\Database\Debug;

/**
 * QueryCollector
 * - Lưu per-request (static) danh sách truy vấn đã chạy.
 * - Mỗi entry gồm: sql_raw, sql_rendered, params, time_ms, node, intent, connection.
 */
final class QueryCollector
{
    /** @var bool */
    private static $enabled = false;

    /** @var array<int,array<string,mixed>> */
    private static $entries = array();

    public static function enable() { self::$enabled = true; }
    public static function disable(){ self::$enabled = false; }
    public static function flush()  { self::$entries = array(); }

    /** @return bool */
    public static function isEnabled() { return self::$enabled; }

    /**
     * Add a query entry and return its ID
     * 
     * @param array $entry
     * Required keys: sql_raw, sql_rendered, params, time_ms, node, intent, connection
     * @return int Query ID (index in entries array)
     */
    public static function add(array $entry)
    {
        if (!self::$enabled) return -1;
        self::$entries[] = $entry;
        return count(self::$entries) - 1;
    }
    
    /**
     * Update an existing query entry by ID
     * 
     * @param int   $queryId
     * @param array $updates
     * @return bool Success
     */
    public static function update($queryId, array $updates)
    {
        if (!self::$enabled) return false;
        if (!isset(self::$entries[$queryId])) return false;
        
        self::$entries[$queryId] = array_merge(self::$entries[$queryId], $updates);
        return true;
    }
    
    /**
     * Get a query entry by ID
     * 
     * @param int $queryId
     * @return array|null
     */
    public static function get($queryId)
    {
        if (!self::$enabled) return null;
        return isset(self::$entries[$queryId]) ? self::$entries[$queryId] : null;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all()
    {
        return self::$entries;
    }
}
