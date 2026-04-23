<?php
/**
 * Schema Graph – Convert array schema → JSON-LD @graph
 *
 * Trách nhiệm:
 * - Nhận array schema (đã qua Builder + filters)
 * - Convert sang JSON-LD dạng @graph
 * - KHÔNG hook trong Graph
 * - KHÔNG cho phép chỉnh JSON string (chỉ convert array → JSON, XSS-safe)
 *
 * Output format:
 * [
 *   '@context' => 'https://schema.org',
 *   '@graph' => [...]
 * ]
 *
 * @package System\Libraries\Render\Schema
 * @since 1.0.0
 */

namespace System\Libraries\Render\Schema;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Graph
{
    /** JSON encode flags: XSS-safe (escape < and > to prevent </script> breakout) */
    private static $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_HEX_TAG;

    /**
     * Recursively sanitize string values in schema (strip tags, control chars).
     * Phòng XSS: chuỗi trong JSON-LD không chứa HTML/script; json_encode dùng JSON_HEX_TAG
     * để tránh breakout </script>. View khi hiển thị user content trong HTML nên dùng e()/h().
     *
     * @param mixed $value
     * @return mixed
     */
    private static function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeValue'], $value);
        }
        if (is_string($value)) {
            $value = strip_tags($value);
            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
            return trim($value);
        }
        return $value;
    }

    /**
     * Render schema array to JSON-LD script tag.
     * KHÔNG hook. Chỉ convert array → JSON (XSS-safe).
     *
     * @param array $schemas Associative or indexed array of schema arrays (from Builder + filters)
     * @return string HTML <script type="application/ld+json">...</script>
     */
    public static function render(array $schemas)
    {
        $schemas = array_filter($schemas, function ($schema) {
            return $schema !== null && is_array($schema);
        });

        if (empty($schemas)) {
            return '';
        }

        $schemas = array_map([self::class, 'sanitizeValue'], $schemas);
        $graph   = [
            '@context' => 'https://schema.org',
            '@graph'   => array_values($schemas),
        ];

        $json = json_encode($graph, self::$jsonFlags);
        if ($json === false) {
            //\System\Libraries\Logger::error('Schema Graph: json_encode failed (invalid UTF-8 or depth).');
            return '';
        }
        return "<script type=\"application/ld+json\">\n" . $json . "\n</script>\n";
    }
}
