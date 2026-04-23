<?php
/**
 * Schema – Entry point (public API), cùng cấp với Head, View.
 *
 * Lifecycle: Schema::get() / Schema::render()
 *   → Schema\Context::make()
 *   → Schema\Builder::build()
 *   → apply_filters('schema.render', $schemas, $context)
 *   → Schema\Graph::render()
 *
 * Schema chính được gọi trong Head::render() qua Schema::get(). View chỉ cần view_head().
 *
 * @package System\Libraries\Render
 * @since 1.0.0
 */

namespace System\Libraries\Render;

use System\Libraries\Render\Schema\Context;
use System\Libraries\Render\Schema\Builder;
use System\Libraries\Render\Schema\Graph;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Schema
{
    /** @var array|null Current context [type => string, payload => mixed] when no args passed to render() */
    private static $currentContext = null;

    /**
     * Set current page context (type + payload). Gọi từ FrontendController trước khi render view.
     * Head::render() gọi Schema::get() không tham số → context này được dùng.
     *
     * @param string $type    Schema type: front, page, article, product, archive, search, person, …
     * @param object|array|null $payload Data (post, page, query, …)
     */
    public static function setCurrentContext($type, $payload = null)
    {
        self::$currentContext = [
            'type'    => $type ?: 'front',
            'payload' => $payload,
        ];
    }

    /**
     * Get current context (set by FrontendController or manually).
     *
     * @return array|null ['type' => string, 'payload' => mixed] or null
     */
    public static function getCurrentContext()
    {
        return self::$currentContext;
    }

    /**
     * Clear current context (useful for tests or when switching context).
     */
    public static function clearCurrentContext()
    {
        self::$currentContext = null;
    }

    /**
     * Render JSON-LD schema (output <script type="application/ld+json">).
     *
     * Được gọi trong view. Khi gọi không tham số: dùng context đã set bởi FrontendController.
     *
     * @param string|null $type   Schema type: front, product, article, …
     * @param object|array|null $payload Data từ view (product, post, …)
     * @return void Echoes HTML script tag
     */
    public static function render($type = null, $payload = null)
    {
        if ($type === null && $payload === null) {
            $current = self::getCurrentContext();
            if ($current !== null) {
                $type    = $current['type'];
                $payload = $current['payload'];
            }
        }
        $context = Context::make($type ?: 'front', $payload);
        $schemas = Builder::build($context);

        if (function_exists('apply_filters')) {
            $schemas = apply_filters('schema.render', $schemas, $context);
        }

        if (!is_array($schemas)) {
            $schemas = [];
        }

        echo Graph::render($schemas);
    }

    /**
     * Return JSON-LD string (không echo) – dùng khi cần gắn vào Head hoặc buffer.
     *
     * @param string|null $type
     * @param object|array|null $payload
     * @return string HTML <script type="application/ld+json">...</script>
     */
    public static function get($type = null, $payload = null)
    {
        if ($type === null && $payload === null) {
            $current = self::getCurrentContext();
            if ($current !== null) {
                $type    = $current['type'];
                $payload = $current['payload'];
            }
        }
        $context = Context::make($type ?: 'front', $payload);
        $schemas = Builder::build($context);

        if (function_exists('apply_filters')) {
            $schemas = apply_filters('schema.render', $schemas, $context);
        }

        if (!is_array($schemas)) {
            $schemas = [];
        }

        return Graph::render($schemas);
    }
}
