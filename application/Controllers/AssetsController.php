<?php

namespace App\Controllers;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * AssetsController - Serves combined CSS/JS when option combine_css / combine_js is on.
 *
 * GET /_assets/combined.css?q=base64(json_encode([url1, url2, ...]))
 * GET /_assets/combined.js?q=base64(json_encode([url1, url2, ...]))
 *
 * Fetches each same-origin URL (resolved to local path), concatenates, returns with correct Content-Type.
 *
 * @package App\Controllers
 */
class AssetsController
{
    /**
     * Serve combined CSS or JS
     *
     * @param string $type 'css' or 'js'
     * @return void
     */
    public function combined($type = 'css')
    {
        $type = strtolower($type);
        if (!in_array($type, ['css', 'js'], true)) {
            $this->sendError(400, 'Invalid type');
            return;
        }

        $q = isset($_GET['q']) ? $_GET['q'] : '';
        if ($q === '') {
            $this->sendError(400, 'Missing q');
            return;
        }

        $decoded = @base64_decode($q, true);
        if ($decoded === false) {
            $this->sendError(400, 'Invalid q');
            return;
        }

        $urls = json_decode($decoded, true);
        if (!is_array($urls)) {
            $this->sendError(400, 'Invalid q');
            return;
        }

        $base = $this->getBaseUrl();
        $out = [];

        foreach ($urls as $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }
            $content = $this->fetchLocalContent($url, $base);
            if ($content !== null) {
                $out[] = $content;
            }
        }

        $body = implode("\n", $out);

        $contentType = $type === 'css' ? 'text/css; charset=UTF-8' : 'application/javascript; charset=UTF-8';
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=86400');
        echo $body;
    }

    /**
     * Get base URL (same-origin) for resolving relative/local URLs
     *
     * @return string
     */
    private function getBaseUrl()
    {
        if (function_exists('base_url')) {
            return rtrim(base_url('', defined('APP_LANG') ? APP_LANG : 'en'), '/');
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    /**
     * Fetch content from URL if same-origin; resolve to local path and read file
     *
     * @param string $url Full URL
     * @param string $base Base URL (same-origin)
     * @return string|null Content or null on failure
     */
    private function fetchLocalContent($url, $base)
    {
        if (strpos($url, $base) !== 0) {
            return null;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null || $path === '') {
            return null;
        }
        $path = '/' . trim($path, '/');
        if (preg_match('#^/([a-z]{2})(/|$)#', $path, $m)) {
            $path = substr($path, strlen($m[1]) + 1) ?: '/';
        }
        $path = preg_replace('#/\.+#', '/', $path);
        while (strpos($path, '/../') !== false) {
            $path = preg_replace('#/[^/]+/\.\./#', '/', $path);
        }
        $path = preg_replace('#/\.+/$#', '/', $path);
        $allowedPrefixes = ['/content/', '/assets/'];
        $allowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            $allowed = (strpos($path, '/') === 0 && preg_match('#^/[a-zA-Z0-9_-]+/#', $path));
        }
        if (!$allowed) {
            return null;
        }
        $file = defined('PATH_ROOT') ? PATH_ROOT . $path : null;
        if ($file === null || !is_file($file) || !is_readable($file)) {
            return null;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext !== 'css' && $ext !== 'js') {
            return null;
        }
        return file_get_contents($file);
    }

    /**
     * Send error response
     *
     * @param int $code HTTP code
     * @param string $message
     * @return void
     */
    private function sendError($code, $message)
    {
        http_response_code($code);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
}
