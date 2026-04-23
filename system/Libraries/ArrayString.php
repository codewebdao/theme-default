<?php
declare(strict_types=1);
namespace System\Libraries;
/**
 * PhpArr - Safe, minified PHP array exporter (short syntax [])
 * - expr($v): PHP expression string
 * - ret($v):  full "<?php return ...;" string
 *
 * Default security:
 * - Reject objects & resources
 * - Reject array references (prevents weird recursion/invariants)
 * - Safe escaping that prevents breaking out of quotes to inject PHP
 */
final class ArrayString
{
    /**
     * Export value to minified PHP expression.
     *
     * @param mixed $value
     * @param array{
     *   maxDepth?: int,
     *   maxItems?: int,
     *   maxStringBytes?: int,
     *   allowRefs?: bool,
     *   rejectObjects?: bool,
     *   rejectResources?: bool,
     *   asciiOnly?: bool
     * } $opt
     */
    public static function expr(mixed $value, array $opt = []): string
    {
        $opt = self::opt($opt);
        return self::walk($value, $opt, 0);
    }

    /**
     * Build full PHP return-file content (still just a string).
     *
     * @param mixed $value
     * @param array $opt same as expr() plus:
     * @param array{
     *   strict?: bool
     * } $opt
     */
    public static function ret(mixed $value, array $opt = []): string
    {
        $strict = array_key_exists('strict', $opt) ? (bool)$opt['strict'] : true;
        unset($opt['strict']);

        $expr = self::expr($value, $opt);
        if ($strict) {
            return "<?php\ndeclare(strict_types=1);\nreturn {$expr};\n";
        }
        return "<?php\nreturn {$expr};\n";
    }

    // ---------------- internal ----------------

    private static function opt(array $opt): array
    {
        return [
            'maxDepth'        => isset($opt['maxDepth']) ? max(1, (int)$opt['maxDepth']) : 64,
            'maxItems'        => isset($opt['maxItems']) ? max(1, (int)$opt['maxItems']) : 200000,
            'maxStringBytes'  => isset($opt['maxStringBytes']) ? max(0, (int)$opt['maxStringBytes']) : 4 * 1024 * 1024,
            'allowRefs'       => isset($opt['allowRefs']) ? (bool)$opt['allowRefs'] : false,
            'rejectObjects'   => isset($opt['rejectObjects']) ? (bool)$opt['rejectObjects'] : true,
            'rejectResources' => isset($opt['rejectResources']) ? (bool)$opt['rejectResources'] : true,

            // If true: escape any non-ASCII byte as \xNN (max safety for “binary” strings, but larger output)
            // If false (default): keep UTF-8 bytes as-is to reduce file size (still safe from code injection).
            'asciiOnly'       => isset($opt['asciiOnly']) ? (bool)$opt['asciiOnly'] : false,
        ];
    }

    private static function walk(mixed $v, array $opt, int $depth): string
    {
        if ($depth > $opt['maxDepth']) {
            throw new RuntimeException("Max depth exceeded ({$opt['maxDepth']})");
        }

        if ($v === null) return 'null';
        if (is_bool($v)) return $v ? 'true' : 'false';
        if (is_int($v)) return (string)$v;

        if (is_float($v)) {
            if (is_nan($v)) return 'NAN';
            if ($v === INF) return 'INF';
            if ($v === -INF) return '-INF';

            // Locale-safe, stable float output
            $j = json_encode($v, JSON_PRESERVE_ZERO_FRACTION);
            if (!is_string($j) || $j === 'null') {
                $j = rtrim(rtrim(sprintf('%.17g', $v), '0'), '.');
                if ($j === '' || $j === '-0') $j = '0';
            }
            return $j;
        }

        if (is_string($v)) {
            if ($opt['maxStringBytes'] > 0 && strlen($v) > $opt['maxStringBytes']) {
                throw new RuntimeException("String too large (>{$opt['maxStringBytes']} bytes)");
            }
            return self::str($v, $opt['asciiOnly']);
        }

        if (is_array($v)) {
            return self::arr($v, $opt, $depth);
        }

        if (is_object($v)) {
            if ($opt['rejectObjects']) {
                throw new InvalidArgumentException('Objects rejected for safe export.');
            }
            throw new InvalidArgumentException('Object export not implemented (convert to array yourself).');
        }

        if (is_resource($v) || gettype($v) === 'resource (closed)') {
            if ($opt['rejectResources']) {
                throw new InvalidArgumentException('Resources rejected for safe export.');
            }
            throw new InvalidArgumentException('Resource export not implemented.');
        }

        throw new InvalidArgumentException('Unsupported type: ' . gettype($v));
    }

    private static function arr(array $a, array $opt, int $depth): string
    {
        $count = count($a);
        if ($count > $opt['maxItems']) {
            throw new RuntimeException("Array too large ({$count} items > {$opt['maxItems']})");
        }

        // Reject references (PHP 7.4+)
        if (!$opt['allowRefs'] && class_exists('ReflectionReference')) {
            foreach ($a as $k => $_v) {
                $ref = \ReflectionReference::fromArrayElement($a, $k);
                if ($ref !== null) {
                    throw new InvalidArgumentException('Array contains references; rejected.');
                }
            }
        }

        if ($a === []) return '[]';

        $isList = function_exists('array_is_list') ? array_is_list($a) : self::isListFallback($a);

        $parts = [];
        if ($isList) {
            foreach ($a as $v) {
                $parts[] = self::walk($v, $opt, $depth + 1);
            }
            return '[' . implode(',', $parts) . ']';
        }

        foreach ($a as $k => $v) {
            $key = is_int($k) ? (string)$k : self::str((string)$k, $opt['asciiOnly']);
            $val = self::walk($v, $opt, $depth + 1);
            $parts[] = $key . '=>' . $val;
        }
        return '[' . implode(',', $parts) . ']';
    }

    private static function isListFallback(array $a): bool
    {
        $i = 0;
        foreach ($a as $k => $_v) {
            if ($k !== $i) return false;
            $i++;
        }
        return true;
    }

    /**
     * Safe string literal:
     * - Prefer single quotes when possible (smaller output)
     * - Otherwise use double quotes with strict escaping
     *
     * SECURITY: This prevents code injection because:
     * 1. All quotes are properly escaped (can't break out of string)
     * 2. All control bytes are escaped as \xNN (can't inject newlines/tabs)
     * 3. $ is escaped in double quotes (prevents variable interpolation)
     * 4. <?php tags in strings are safe (they're just text, not executed)
     * 5. Objects/resources are rejected (prevents unserialize attacks)
     *
     * This prevents code injection because the generated PHP string literal
     * cannot be broken by untrusted content.
     */
    private static function str(string $s, bool $asciiOnly): string
    {
        // If no control chars/newlines, prefer single quotes (smaller)
        if (self::canSingle($s, $asciiOnly)) {
            // ✅ SECURITY: Escape backslash and single quote to prevent breaking out
            // Single quotes don't interpolate variables, so $ is safe
            $esc = str_replace(['\\', '\''], ['\\\\', '\\\''], $s);
            return "'" . $esc . "'";
        }

        // Otherwise double quotes; escape \ " $ and control bytes; keep UTF-8 raw if asciiOnly=false
        $out = '"';
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($s[$i]);

            // ✅ SECURITY: Escape backslash first (must be first to prevent double-escaping issues)
            if ($c === 0x5C) { $out .= '\\\\'; continue; } // \
            
            // ✅ SECURITY: Escape double quote to prevent breaking out of string
            if ($c === 0x22) { $out .= '\\"';  continue; } // "
            
            // ✅ SECURITY: Escape $ to prevent variable interpolation attacks
            if ($c === 0x24) { $out .= '\\$';  continue; } // $ (avoid interpolation)

            // ✅ SECURITY: Escape all control bytes (0x00-0x1F, 0x7F) as \xNN
            // This prevents injection of newlines, tabs, null bytes, etc.
            if ($c < 0x20 || $c === 0x7F) {
                $out .= '\\x' . strtoupper(str_pad(dechex($c), 2, '0', STR_PAD_LEFT));
                continue;
            }

            // ✅ SECURITY: Optionally escape non-ASCII bytes (for maximum safety)
            if ($c >= 0x80 && $asciiOnly) {
                $out .= '\\x' . strtoupper(str_pad(dechex($c), 2, '0', STR_PAD_LEFT));
                continue;
            }

            // ✅ SAFE: Printable ASCII and UTF-8 bytes (when asciiOnly=false)
            // Note: <?php tags in strings are safe - they're just text, not executed
            // PHP parser only executes <?php at file level, not inside string literals
            $out .= $s[$i];
        }
        return $out . '"';
    }

    private static function canSingle(string $s, bool $asciiOnly): bool
    {
        // single-quote string literal supports UTF-8 bytes safely.
        // We only reject control chars/newlines and (optional) non-ASCII for asciiOnly mode.
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($s[$i]);
            if ($c < 0x20 || $c === 0x7F) return false;
            if ($asciiOnly && $c >= 0x80) return false;
        }
        return true;
    }
}

/*
USAGE:

// expression only
$expr = PhpArr::expr([['x'=>1],'item b','item c']);
// => [[ "x"=>1 ],"item b","item c"] (minified no spaces actually)

// full return file content
$php = PhpArr::ret(['routes' => ['/' => 'Home@index']]);
// => "<?php\ndeclare(strict_types=1);\nreturn [\"routes\"=>[\"/\"=>\"Home@index\"]];\n"
*/
