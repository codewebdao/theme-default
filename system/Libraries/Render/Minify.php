<?php

namespace System\Libraries\Render;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * Minify HTML (mức vừa phải – ít rủi ro gây lỗi tag/script).
 *
 * - Bật/tắt qua option: minify_html (on/off).
 * - Không xóa tag; nội dung <style>/<script>/JSON-LD được minify khi khôi phục.
 *
 * Các bước:
 * 1. Thay <style>, <script>, <pre>, <textarea> bằng placeholder.
 * 2. Xóa comment HTML; giữ conditional <!--[if ...]>, <!--<![endif]-->.
 * 3. Thu gọn khoảng trắng giữa hai thẻ: >\s+< -> ><.
 * 4. Thu gọn mọi khoảng trắng còn lại (space, newline) thành một space.
 * 5. Khôi phục placeholder; minify nội dung style/script (CSS, JS, JSON-LD).
 *
 * Lỗi: preg_* null hoặc exception → trả chuỗi gốc / khôi phục không minify; không throw.
 *
 * @package System\Libraries\Render
 * @since 1.0.0
 */
class Minify
{
    private static $placeholders = [];

    /**
     * Minify HTML (mức vừa phải).
     *
     * @param string $html HTML đầy đủ
     * @return string HTML đã minify (hoặc nguyên bản nếu lỗi)
     */
    public static function html($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }
        if (function_exists('apply_filters')) {
            $html = apply_filters('minify.html.before', $html);
        }
        self::$placeholders = [];
        try {
            $html = self::protectBlocks($html);
            $html = self::removeHtmlComments($html);
            $html = self::collapseWhitespaceBetweenTags($html);
            $html = self::collapseNewlinesAndTrim($html);
            $html = self::restoreBlocks($html);
            if (function_exists('apply_filters')) {
                $html = apply_filters('minify.html.after', $html);
            }
            return $html;
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Minify::html error: ' . $e->getMessage());
            try {
                return self::restoreBlocks($html, false);
            } catch (\Throwable $e2) {
                \System\Libraries\Logger::error('Minify::html restore fallback error: ' . $e2->getMessage());
                return $html;
            }
        }
    }

    /**
     * Thay script/style bằng placeholder để không minify nhầm nội dung.
     * Thứ tự: style trước, script sau (để block ngoài được thay trước; restore sẽ dùng thứ tự ngược).
     */
    private static function protectBlocks($html)
    {
        $patterns = [
            '/<style[^>]*>[\s\S]*?<\/style>/iu',
            '/<script[^>]*>[\s\S]*?<\/script>/iu',
        ];
        foreach ($patterns as $pattern) {
            $result = preg_replace_callback($pattern, function ($m) {
                $key = '%%MINIFY_' . count(self::$placeholders) . '%%';
                self::$placeholders[$key] = $m[0];
                return $key;
            }, $html);
            if ($result === null) {
                return $html;
            }
            $html = $result;
        }
        return $html;
    }

    /**
     * Khôi phục script/style từ placeholder; minify nội dung bên trong nếu $minifyContent = true.
     *
     * @param string $html HTML có placeholder
     * @param bool $minifyContent Có minify nội dung style/script hay không (false khi khôi phục sau lỗi)
     * @return string
     */
    private static function restoreBlocks($html, $minifyContent = true)
    {
        // Khôi phục theo thứ tự ngược để placeholder ngoài (số lớn) được thay trước, tránh lỗi khi block lồng nhau
        $order = array_reverse(array_keys(self::$placeholders), true);
        foreach ($order as $key) {
            $content = self::$placeholders[$key];
            $content = $minifyContent ? self::minifyBlockContent($content) : $content;
            $html = str_replace($key, $content, $html);
        }
        self::$placeholders = [];
        return $html;
    }

    /**
     * Minify nội dung bên trong thẻ <style> / <script> (CSS, JS, JSON-LD hoặc application/json).
     *
     * @param string $block Full tag e.g. <style>...</style> or <script>...</script>
     * @return string Block với nội dung đã minify (hoặc nguyên bản nếu lỗi)
     */
    private static function minifyBlockContent($block)
    {
        if (!is_string($block) || $block === '') {
            return $block;
        }
        try {
            // <style>...</style> -> minify CSS
            if (preg_match('/^(<style[^>]*>)([\s\S]*?)(<\/style>)$/iu', $block, $m)) {
                return $m[1] . self::css($m[2]) . $m[3];
            }
            // <script type="application/ld+json"> hoặc type="application/json" -> minify JSON
            if (preg_match('/^(<script(?=[^>]*type\s*=\s*["\']?\s*application\/(?:ld\+)?json)[^>]*>)([\s\S]*?)(<\/script>)$/iu', $block, $m)) {
                return $m[1] . self::json(trim($m[2])) . $m[3];
            }
            // <script>...</script> -> minify JS
            if (preg_match('/^(<script[^>]*>)([\s\S]*?)(<\/script>)$/iu', $block, $m)) {
                return $m[1] . self::js($m[2]) . $m[3];
            }
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Minify::minifyBlockContent error: ' . $e->getMessage());
        }
        return $block;
    }

    /**
     * Xóa comment HTML (cả nhiều dòng); giữ conditional <!--[if ...]> và <!--<![endif]-->.
     */
    private static function removeHtmlComments($html)
    {
        $result = preg_replace('/<!--(?!\s*\[if)(?!\s*<!\[endif])[\s\S]*?-->/u', '', $html);
        return $result !== null ? $result : $html;
    }

    /**
     * Thu gọn khoảng trắng giữa hai thẻ: >\s+< -> ><
     */
    private static function collapseWhitespaceBetweenTags($html)
    {
        $result = preg_replace('/>\s+</u', '><', $html);
        return $result !== null ? $result : $html;
    }

    /**
     * Gộp nhiều newline thành một, trim từng dòng.
     */
    private static function collapseNewlinesAndTrim($html)
    {
        $lines = explode("\n", $html);
        $lines = array_map('trim', $lines);
        $html = implode("\n", $lines);
        $result = preg_replace('/\n{2,}/', "\n", $html);
        return $result !== null ? $result : $html;
    }

    /**
     * Minify CSS (inline). Bật qua option minify_css.
     */
    public static function css($css)
    {
        if (!is_string($css) || $css === '') {
            return $css;
        }
        $orig = $css;
        try {
            $css = preg_replace('/\/\*(?!\!)[\s\S]*?\*\//u', '', $css);
            if ($css === null) {
                return $orig;
            }
            $css = preg_replace('/\s+/u', ' ', $css);
            return $css !== null ? trim($css) : $orig;
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Minify::css error: ' . $e->getMessage());
            return $orig;
        }
    }

    /**
     * Minify JS (inline). Bật qua option minify_js.
     * - Bảo vệ chuỗi "..." và '...' rồi mới xóa comment; tránh gộp dòng sau // (gây lỗi).
     * - Xóa comment đa dòng và comment một dòng (//), rồi gộp khoảng trắng (giữ an toàn cho URL trong chuỗi).
     */
    public static function js($js)
    {
        if (!is_string($js) || $js === '') {
            return $js;
        }
        $orig = $js;
        try {
            $strings = [];
            // 1) Bảo vệ chuỗi literal (tránh xóa nhầm // trong "http://..." hoặc '...')
            $js = preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'/s', function ($m) use (&$strings) {
                $key = '%%JS_STR_' . count($strings) . '%%';
                $strings[$key] = $m[0];
                return $key;
            }, $js);
            if ($js === null) {
                return $orig;
            }
            // 2) Xóa multi-line comment
            $js = preg_replace('/\/\*[\s\S]*?\*\//u', '', $js);
            if ($js === null) {
                return $orig;
            }
            // 3) Xóa single-line comment (// đến hết dòng hoặc cuối chuỗi); giữ newline để code dòng sau không dính vào
            $js = preg_replace('/\/\/[^\n]*\n/', "\n", $js);
            if ($js === null) {
                return $orig;
            }
            $js = preg_replace('/\/\/[^\n]*$/', '', $js);
            if ($js === null) {
                return $orig;
            }
            // 4) Gộp khoảng trắng thành một space (sau khi đã xóa // nên gộp dòng an toàn)
            $js = preg_replace('/\s+/u', ' ', $js);
            if ($js === null) {
                return $orig;
            }
            $js = trim($js);
            // 5) Khôi phục chuỗi
            foreach (array_reverse(array_keys($strings), true) as $key) {
                $js = str_replace($key, $strings[$key], $js);
            }
            return $js;
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Minify::js error: ' . $e->getMessage());
            return $orig;
        }
    }

    /**
     * Minify JSON (inline). Dùng cho <script type="application/ld+json"> hoặc application/json.
     * - json_decode(false) + json_encode để giữ {} vs [] và bỏ whitespace; lỗi thì trả nguyên bản.
     */
    public static function json($json)
    {
        if (!is_string($json) || $json === '') {
            return $json;
        }
        try {
            $decoded = json_decode($json, false);
            if (json_last_error() !== \JSON_ERROR_NONE) {
                return $json;
            }
            $encoded = json_encode($decoded, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
            return $encoded !== false ? $encoded : $json;
        } catch (\Throwable $e) {
            \System\Libraries\Logger::error('Minify::json error: ' . $e->getMessage());
            return $json;
        }
    }
}
