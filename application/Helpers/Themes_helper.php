<?php

use App\Libraries\Fastlang as Flang;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

// hàm get_header include file header.php trong theme
if (!function_exists('get_header')) {
    function get_header($data = [])
    {
        if (!defined('APP_THEME_PATH') || APP_THEME_PATH === '') {
            echo '<!-- APP_THEME_PATH not defined -->';

            return;
        }
        $headerFile = rtrim(APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'header.php';
        if (is_file($headerFile)) {
            extract($data);
            include_once $headerFile;
        } else {
            echo '<!-- Header file not found: ' . htmlspecialchars($headerFile, ENT_QUOTES, 'UTF-8') . ' -->';
        }
    }
}

// hàm get_footer include file footer.php trong theme
if (!function_exists('get_footer')) {
    function get_footer()
    {
        if (!defined('APP_THEME_PATH') || APP_THEME_PATH === '') {
            echo '<!-- APP_THEME_PATH not defined -->';

            return;
        }
        $footerFile = rtrim(APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'footer.php';
        if (is_file($footerFile)) {
            include_once $footerFile;
        } else {
            echo '<!-- Footer file not found: ' . htmlspecialchars($footerFile, ENT_QUOTES, 'UTF-8') . ' -->';
        }
    }
}

if (!function_exists('get_template')) {
    function get_template($templateName, $data = [])
    {
        if (!defined('APP_THEME_PATH') || APP_THEME_PATH === '') {
            echo '<!-- APP_THEME_PATH not defined -->';

            return;
        }
        $rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim((string) $templateName, '/\\'));
        $templateFile = rtrim(APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR . $rel . '.php';
        if (is_file($templateFile)) {
            (function ($data) use ($templateFile) {
                extract($data);
                include $templateFile;
            })($data);
        } else {
            echo '<!-- Template file not found: ' . htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8') . ' -->';
        }
    }
}

