<?php
/**
 * Schema Builder – Build schema data array (KHÔNG build JSON)
 *
 * Trách nhiệm:
 * - Build schema dưới dạng array
 * - Mỗi schema type = 1 file trong Types/
 * - Core schema luôn có: website, organization
 * - Hook: apply_filters('schema.build', $schemas, $context)
 * - Sau mỗi type: apply_filters('schema.{type}', $schema, $context)
 *
 * @package System\Libraries\Render\Schema
 * @since 1.0.0
 */

namespace System\Libraries\Render\Schema;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Builder
{
    /** @var string Path to Types directory */
    private static $typesPath;

    /**
     * Build schema array from context.
     * Hook: schema.build (toàn bộ schemas), schema.{type} (từng type).
     *
     * @param Context $context
     * @return array Associative array of schema type => schema array
     */
    public static function build(Context $context)
    {
        self::$typesPath = self::$typesPath ?: __DIR__ . DIRECTORY_SEPARATOR . 'Types' . DIRECTORY_SEPARATOR;
        $schemas         = [];

        // Core: luôn có website, organization
        $schemas['website']      = self::loadType('website', $context);
        $schemas['organization'] = self::loadType('organization', $context);

        // Breadcrumb (thường có cho mọi page)
        $schemas['breadcrumb'] = self::loadType('breadcrumb', $context);

        // Type-specific: product, article, page, archive, search, person, course, event, faq, …
        $type = $context->type;
        if ($type && $type !== 'front') {
            $schema = self::loadType($type, $context);
            if (!empty($schema)) {
                $schemas[$type] = $schema;
            }
        } else {
            // Trang chủ: thêm WebPage generic (webpage.php) để graph có WebPage
            $webpage = self::loadType('webpage', $context);
            if (!empty($webpage)) {
                $schemas['webpage'] = $webpage;
            }
        }

        if (function_exists('apply_filters')) {
            $schemas = apply_filters('schema.build', $schemas, $context);
        }

        return is_array($schemas) ? $schemas : [];
    }

    /**
     * Load schema array từ file Types/{$type}.php.
     * File nhận $context trong scope, return array.
     * Sau khi load gọi apply_filters('schema.' . $type, $schema, $context).
     *
     * @param string $type
     * @param Context $context
     * @return array
     */
    private static function loadType($type, Context $context)
    {
        if (!is_string($type) || $type === '' || !preg_match('/^[a-z0-9_-]+$/i', $type)) {
            return [];
        }
        $file = self::$typesPath . $type . '.php';
        if (!is_file($file)) {
            return [];
        }

        $schema = (static function () use ($file, $context) {
            $context = $context;
            return require $file;
        })();

        if (!is_array($schema)) {
            return [];
        }

        if (function_exists('apply_filters')) {
            $schema = apply_filters('schema.' . $type, $schema, $context);
        }

        return is_array($schema) ? $schema : [];
    }
}
