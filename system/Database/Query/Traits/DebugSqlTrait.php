<?php
namespace System\Database\Query\Traits;

/**
 * DebugSqlTrait
 *
 * Purpose:
 *  - Provide non-executing SQL inspection helpers for Query Builder.
 *  - Work with SELECT statements compiled by compileSelect() (from BaseTrait).
 *
 * Requirements:
 *  - The host class (Builder) must provide a protected compileSelect(): array{0:string,1:array}
 *  - PHP 7.4+
 *
 * Methods:
 *  - toSql(): string
 *      Return SQL with placeholders (NOT executed).
 *  - getBindings(): array
 *      Return bindings array corresponding to current SELECT.
 *  - toRawSql(): string
 *      Interpolate current bindings into SQL for DEBUG VIEW ONLY (do not execute!).
 */
trait DebugSqlTrait
{
    /**
     * Compile current SELECT statement without executing.
     *
     * @return string SQL with placeholders (NOT executed)
     */
    public function toSql()
    {
        list($sql, $bindings) = $this->compileSelect();
        return $sql;
    }

    /**
     * Get current bindings for the compiled SELECT.
     *
     * @return array<int|string,mixed>
     */
    public function getBindings()
    {
        list($sql, $bindings) = $this->compileSelect();
        return $bindings;
    }

    /**
     * Render SQL by interpolating current bindings (debug view only).
     * DO NOT execute the returned string against the database.
     *
     * @return string
     */
    public function toRawSql()
    {
        list($sql, $bindings) = $this->compileSelect();
        return $this->dbgRenderSql($sql, $bindings);
    }

    /* ------------------- local debug helpers ------------------- */

    /** Quote value for debug rendering (NOT for execution). */
    private function dbgRenderValue($v)
    {
        if ($v === null) return 'NULL';
        if (is_bool($v)) return $v ? '1' : '0';
        if (is_int($v) || is_float($v)) return (string)$v;
        // string: escape single quotes
        return "'" . str_replace("'", "''", (string)$v) . "'";
    }

    /**
     * Interpolate :named and ? bindings for debug.
     * Accepts both ['name'=>val] and [':name'=>val] named styles.
     *
     * @param string $sql
     * @param array  $params
     * @return string
     */
    private function dbgRenderSql($sql, array $params)
    {
        $rendered = (string)$sql;

        // 1) Named params (:name)
        $named = array();
        foreach ($params as $k => $v) {
            if (is_string($k)) {
                $key = ltrim($k, ':'); // normalize ':name' => 'name'
                $named[$key] = $v;
            }
        }
        foreach ($named as $k => $v) {
            $rendered = preg_replace('/:'.preg_quote($k,'/').'\b/', $this->dbgRenderValue($v), $rendered);
        }

        // 2) Positional '?'
        if (strpos($rendered, '?') !== false) {
            $positional = array_values(array_filter(
                $params,
                static function($kk){ return !is_string($kk); },
                ARRAY_FILTER_USE_KEY
            ));
            $out = '';
            $parts = explode('?', $rendered);
            $cnt = count($parts);
            for ($i = 0; $i < $cnt - 1; $i++) {
                $val = array_key_exists($i, $positional) ? $this->dbgRenderValue($positional[$i]) : '?';
                $out .= $parts[$i] . $val;
            }
            $out .= $parts[$cnt - 1];
            $rendered = $out;
        }

        return $rendered;
    }
}
