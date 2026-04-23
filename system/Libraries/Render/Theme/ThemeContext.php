<?php

namespace System\Libraries\Render\Theme;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

/**
 * Runtime theme context for render scope (web/admin).
 */
class ThemeContext
{
    /** @var string */
    private static $scope = '';

    /** @var array<string,string> */
    private static $themesByScope = [];

    /**
     * Set current render scope.
     */
    public static function setScope($scope)
    {
        $scope = self::normalizeScope($scope);
        if ($scope === '') {
            return;
        }
        self::$scope = $scope;
    }

    /**
     * Get current render scope.
     */
    public static function getScope()
    {
        if (self::$scope !== '') {
            return self::$scope;
        }
        if (defined('APP_THEME_SCOPE') && is_string(APP_THEME_SCOPE) && APP_THEME_SCOPE !== '') {
            self::$scope = self::normalizeScope(APP_THEME_SCOPE);
            if (self::$scope !== '') {
                return self::$scope;
            }
        }
        self::$scope = 'web';
        return self::$scope;
    }

    /**
     * Set active theme for a scope.
     */
    public static function setTheme($themeName, $scope = null)
    {
        $scope = $scope === null ? self::getScope() : self::normalizeScope($scope);
        if ($scope === '') {
            return;
        }
        $themeName = trim((string) $themeName, '/');
        if ($themeName === '') {
            return;
        }
        self::$themesByScope[$scope] = $themeName;
    }

    /**
     * Get active theme by scope.
     */
    public static function getTheme($scope = null)
    {
        $scope = $scope === null ? self::getScope() : self::normalizeScope($scope);
        if ($scope === '') {
            $scope = 'web';
        }
        if (isset(self::$themesByScope[$scope]) && self::$themesByScope[$scope] !== '') {
            return self::$themesByScope[$scope];
        }

        $theme = self::getThemeFromConstants($scope);
        if ($theme !== '') {
            self::$themesByScope[$scope] = $theme;
            return $theme;
        }

        return defined('APP_THEME_NAME') && APP_THEME_NAME !== '' ? APP_THEME_NAME : 'default';
    }

    /**
     * Get active theme path by scope.
     */
    public static function getThemePath($scope = null)
    {
        $theme = self::getTheme($scope);
        return rtrim(PATH_THEMES, '/\\') . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
    }

    /**
     * Reset context (useful for tests).
     */
    public static function clear()
    {
        self::$scope = '';
        self::$themesByScope = [];
    }

    private static function normalizeScope($scope)
    {
        $scope = strtolower(trim((string) $scope));
        if ($scope === 'backend') {
            return 'admin';
        }
        if ($scope === 'frontend') {
            return 'web';
        }
        if ($scope !== 'web' && $scope !== 'admin') {
            return '';
        }
        return $scope;
    }

    private static function getThemeFromConstants($scope)
    {
        if ($scope === 'admin') {
            if (defined('APP_THEME_ADMIN_NAME') && APP_THEME_ADMIN_NAME !== '') {
                return APP_THEME_ADMIN_NAME;
            }
        } else {
            if (defined('APP_THEME_WEB_NAME') && APP_THEME_WEB_NAME !== '') {
                return APP_THEME_WEB_NAME;
            }
        }

        if (defined('APP_THEME_NAME') && APP_THEME_NAME !== '') {
            return APP_THEME_NAME;
        }

        return '';
    }
}

