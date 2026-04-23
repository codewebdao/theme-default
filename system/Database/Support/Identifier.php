<?php
namespace System\Database\Support;

/**
 * Identifier
 *
 * Parses a user-provided identifier into core name + optional alias.
 * Detects "raw" for function calls (COUNT(id)) or SqlExpression.
 *
 * Supported:
 *  - "users", "public.users", "users u", "users AS u"
 *  - "orders.total"
 *  - "COUNT(id)"  (raw)
 *  - "metadata->'$.a'" (MySQL JSON)
 *  - "data->>'name'", "data#>>'{a,b}'" (PostgreSQL JSON)
 *  - "*"
 */
final class Identifier
{
    /** @var string */
    public $raw;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $alias;

    /** @var bool */
    public $isRaw = false;

    /**
     * @param string|SqlExpression $input
     */
    public function __construct($input)
    {
        if ($input instanceof SqlExpression) {
            $this->raw = (string)$input;
            $this->name = $this->raw;
            $this->isRaw = true;
            return;
        }

        $this->raw = trim((string)$input);

        // Function call detection, e.g., COUNT(id)
        if (preg_match('/\w+\s*\(.*\)/', $this->raw)) {
            $this->name = $this->raw;
            $this->isRaw = true;
            return;
        }

        // Extract alias: "... AS alias" or "... alias"
        $tmp = $this->raw;
        $alias = null;

        if (preg_match('/\s+AS\s+([a-zA-Z_][a-zA-Z0-9_]*)$/i', $tmp, $m)) {
            $alias = $m[1];
            $tmp = trim(preg_replace('/\s+AS\s+[a-zA-Z_][a-zA-Z0-9_]*$/i', '', $tmp));
        } else {
            if (preg_match('/\s+([a-zA-Z_][a-zA-Z0-9_]*)$/', $tmp, $m)) {
                $before = substr($tmp, 0, -strlen($m[0]));
                if (!$this->endsWith($before, '.')) {
                    $alias = $m[1];
                    $tmp = trim(substr($tmp, 0, -strlen($m[0])));
                }
            }
        }

        $this->name  = $tmp;
        $this->alias = $alias;
    }

    /** @return bool */
    public function hasAlias()
    {
        return !empty($this->alias);
    }

    /** Polyfill for PHP 7.4 (no str_ends_with) */
    private function endsWith($haystack, $needle)
    {
        if ($needle === '') return true;
        $len = strlen($needle);
        return substr($haystack, -$len) === $needle;
    }
}
