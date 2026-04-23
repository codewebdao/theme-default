<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get the action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'check_compression':
        checkCompressionSupport();
        break;
    case 'test_brotli':
        // Simple endpoint to test if Nginx Brotli is working
        header('Content-Type: text/plain');
        echo 'Brotli test response';
        exit;
    case 'generate_config':
        generateNginxConfig();
        break;
    case 'save_config':
        saveNginxConfig();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function checkCompressionSupport()
{
    $result = [
        'gzip' => [
            'supported' => false,
            'extension' => null,
            'details' => [
                'extension_loaded' => false,
                'can_compress' => false,
                'can_decompress' => false
            ],
            'recommendations' => []
        ],
        'brotli' => [
            'supported' => false,
            'extension' => null,
            'details' => [
                'extension_loaded' => false,
                'can_compress' => false,
                'can_decompress' => false,
                'nginx_brotli' => false
            ],
            'info' => [],
            'recommendations' => [
                'Install PHP Brotli extension: pecl install brotli',
                'Add extension=brotli to your php.ini',
                'Install Nginx Brotli module: ngx_brotli',
                'Or use a pre-compiled Nginx with Brotli support'
            ]
        ]
    ];

    // Check GZIP support
    if (extension_loaded('zlib')) {
        $result['gzip']['supported'] = true;
        $result['gzip']['extension'] = 'zlib';
        $result['gzip']['details']['extension_loaded'] = true;

        // Test compression
        $testData = 'test data for compression';
        $compressed = gzencode($testData);
        $decompressed = gzdecode($compressed);

        if ($compressed && $decompressed === $testData) {
            $result['gzip']['details']['can_compress'] = true;
            $result['gzip']['details']['can_decompress'] = true;
        }
    }

    // Check PHP Brotli extension (needed for creating Brotli cache files)
    $phpBrotliSupported = false;
    if (extension_loaded('brotli')) {
        $result['brotli']['extension'] = 'brotli';
        $result['brotli']['details']['extension_loaded'] = true;

        // Test compression if functions exist
        if (function_exists('brotli_compress') && function_exists('brotli_uncompress')) {
            $testData = 'test data for brotli compression';
            $compressed = brotli_compress($testData);
            $decompressed = brotli_uncompress($compressed);

            if ($compressed && $decompressed === $testData) {
                $result['brotli']['details']['can_compress'] = true;
                $result['brotli']['details']['can_decompress'] = true;
                $phpBrotliSupported = true;
            }
        }
    }

    // Check Nginx Brotli module (needed for serving Brotli-compressed content to browsers)
    $nginxBrotliSupported = false;
    $nginxBrotliInfo = [];

    // Method 1: Check via HTTP request (most reliable)
    // Try to request current script with Accept-Encoding: br header
    if (function_exists('curl_init')) {
        $ch = curl_init();
        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . $_SERVER['HTTP_HOST'] .
            str_replace('/nginx_api.php', '', $_SERVER['REQUEST_URI']) .
            '/nginx_api.php?action=test_brotli';

        curl_setopt_array($ch, [
            CURLOPT_URL => $currentUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true, // ✅ Follow redirects (301, 302, etc.)
            CURLOPT_MAXREDIRS => 5, // Maximum redirects to follow
            CURLOPT_HTTPHEADER => [
                'Accept-Encoding: br, gzip, deflate',
                'User-Agent: CMSFullForm-Brotli-Checker/1.0'
            ],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        if ($response !== false && ($httpCode === 200 || $httpCode === 301 || $httpCode === 302)) {
            // Check if response contains Content-Encoding: br header
            if (preg_match('/Content-Encoding:\s*br/i', $response)) {
                $nginxBrotliSupported = true;
                $nginxBrotliInfo[] = 'Nginx Brotli module is active (detected via HTTP response)';
            } else {
                if ($httpCode === 301 || $httpCode === 302) {
                    $nginxBrotliInfo[] = 'HTTP ' . $httpCode . ' redirect detected. Could not verify Brotli (redirects may strip compression headers)';
                } else {
                    $nginxBrotliInfo[] = 'Nginx Brotli module not detected in HTTP response (no Content-Encoding: br header)';
                }
            }
        } else {
            $nginxBrotliInfo[] = 'Could not test Nginx Brotli via HTTP (HTTP ' . $httpCode . ')';
            if ($httpCode === 0) {
                $nginxBrotliInfo[] = 'Connection failed or timeout. Check if server is accessible.';
            }
        }
    } else {
        $nginxBrotliInfo[] = 'cURL extension not available. Cannot test Nginx Brotli via HTTP.';
    }

    // Set Nginx Brotli status
    $result['brotli']['details']['nginx_brotli'] = $nginxBrotliSupported;
    if (!empty($nginxBrotliInfo)) {
        $result['brotli']['info'] = $nginxBrotliInfo;
    }

    // Brotli is fully supported only if both PHP and Nginx support it
    $result['brotli']['supported'] = $phpBrotliSupported && $nginxBrotliSupported;

    // ✅ OPTIMIZATION: Clear existing recommendations and add only once
    $result['brotli']['recommendations'] = [];

    // Add recommendations if not fully supported
    if (!$phpBrotliSupported) {
        $result['brotli']['recommendations'][] = 'Install PHP Brotli extension: pecl install brotli';
        $result['brotli']['recommendations'][] = 'Add extension=brotli to your php.ini';
        $result['brotli']['recommendations'][] = 'Restart PHP-FPM or web server after installation';
    }
    if (!$nginxBrotliSupported) {
        $result['brotli']['recommendations'][] = 'Install Nginx Brotli module: ngx_brotli';
        $result['brotli']['recommendations'][] = 'Or use a pre-compiled Nginx with Brotli support';
        $result['brotli']['recommendations'][] = 'Configure Nginx to enable Brotli compression in nginx.conf';
        $result['brotli']['recommendations'][] = 'Restart Nginx after configuration';
    }

    echo json_encode($result);
}

function generateNginxConfig()
{
    // Get configuration data from POST
    $configData = json_decode($_POST['config'] ?? '{}', true);

    if (empty($configData)) {
        echo json_encode(['error' => 'No configuration data provided']);
        return;
    }

    // Validate required fields
    $requiredFields = ['cacheRoot'];
    foreach ($requiredFields as $field) {
        if (empty($configData[$field])) {
            echo json_encode(['error' => "Missing required field: {$field}"]);
            return;
        }
    }

    // Load template
    $templatePath = dirname(__DIR__) . '/nginx_template.php';
    if (!file_exists($templatePath)) {
        echo json_encode(['error' => 'Template file not found']);
        return;
    }

    $template = file_get_contents($templatePath);

    // Replace placeholders
    $nginxConfig = replaceTemplatePlaceholders($template, $configData);

    // Return generated config
    echo json_encode([
        'success' => true,
        'config' => $nginxConfig
    ]);
}

function replaceTemplatePlaceholders($template, $config)
{
    // Basic settings
    $template = str_replace('{{DEBUG}}', $config['debug'] ?? false ? '1' : '0', $template);
    $template = str_replace('{{CSS_EXPIRATION}}', $config['cssExpiration'] ?? '45d', $template);
    $template = str_replace('{{JS_EXPIRATION}}', $config['jsExpiration'] ?? '45d', $template);
    $template = str_replace('{{MEDIA_EXPIRATION}}', $config['mediaExpiration'] ?? '45d', $template);

    // WebP check
    if ($config['webpPriority'] ?? true) {
        $template = str_replace('{{WEBP_CHECK}}', 'if ($http_accept ~* "webp") {
    set $webp_suffix ".webp";
}', $template);
    } else {
        $template = str_replace('{{WEBP_CHECK}}', '', $template);
    }

    // Compression checks
    if ($config['brotliEnabled'] ?? false) {
        $template = str_replace('{{BROTLI_CHECK}}', 'if ($http_accept_encoding ~ br) {
    set $cmsff_encryption "";
}', $template);
    } else {
        $template = str_replace('{{BROTLI_CHECK}}', '#if ($http_accept_encoding ~ br) {
#    set $cmsff_encryption "";
#}', $template);
    }

    if ($config['gzipEnabled'] ?? true) {
        $template = str_replace('{{GZIP_CHECK}}', 'if ($http_accept_encoding ~ gzip) {
    set $cmsff_encryption "_gzip";
}', $template);
    } else {
        $template = str_replace('{{GZIP_CHECK}}', '#if ($http_accept_encoding ~ gzip) {
#    set $cmsff_encryption "_gzip";
#}', $template);
    }

    // API Location
    if ($config['apiEnabled'] ?? true) {
        $apiPath = $config['apiPath'] ?? '/api/';
        $apiLocation = "location ^~ {$apiPath} {
    # ---- Header CORS all request api ----
    add_header 'Access-Control-Allow-Origin'      '*'     always;
    add_header 'Access-Control-Allow-Methods'     'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers'     'Authorization,Content-Type,Accept,Origin,User-Agent,Referer,X-Requested-With' always;
    add_header 'Access-Control-Allow-Credentials' 'true'  always;

    # ---- pre-flight ----
    if (\$request_method = 'OPTIONS') {
        add_header 'Access-Control-Max-Age' 1728000;
        return 204;
    }
    try_files \$uri \$uri/ /index.php\$is_args\$args;
}";
        $template = str_replace('{{API_LOCATION}}', $apiLocation, $template);
    } else {
        $template = str_replace('{{API_LOCATION}}', '', $template);
    }

    // API JSON Detection (use API path pattern to detect JSON extension)
    if ($config['apiEnabled'] ?? true) {
        $apiPath = trim($config['apiPath'] ?? '/api/');

        // Convert API path pattern to nginx regex pattern for URI path matching
        // API path can be: /api/, ^/api/, ^.*/api/, etc.
        // We need to match it against $cmsff_uri_path (which doesn't include query string)

        // Check if pattern contains regex operators (not escaped)
        $hasRegexOps = preg_match('/[^\\\\][\^\$\.\*\+\?\|\(\)\[\]\{\}]/', $apiPath);

        if (!$hasRegexOps) {
            // Simple literal path, convert to regex pattern
            // Escape special chars and add ^ prefix and .* suffix to match any path containing this
            $apiPathPattern = '^' . preg_quote($apiPath, '/') . '.*';
        } else {
            // Already a regex pattern, use as-is
            // Ensure it starts with ^ to match from beginning of path
            if (strpos($apiPath, '^') !== 0) {
                $apiPathPattern = '^' . $apiPath;
            } else {
                $apiPathPattern = $apiPath;
            }
        }

        $apiJsonDetection = "if (\$cmsff_uri_path ~ \"{$apiPathPattern}\") {
    set \$cmsff_file_ext \".json\";
}";
        $template = str_replace('{{API_JSON_DETECTION}}', $apiJsonDetection, $template);
    } else {
        $template = str_replace('{{API_JSON_DETECTION}}', '', $template);
    }

    // API CORS Headers
    if ($config['apiEnabled'] ?? true) {
        $apiCorsHeaders = "add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type,Accept,Origin,User-Agent,Referer,X-Requested-With' always;
    add_header 'Access-Control-Allow-Credentials' 'true' always;

    # If request is OPTIONS, return 204
    if (\$request_method = OPTIONS) {
        add_header 'Access-Control-Max-Age' 1728000;
        add_header 'Content-Type' 'text/plain charset=UTF-8';
        add_header 'Content-Length' 0;
        return 204;
    }";
        $template = str_replace('{{API_CORS_HEADERS}}', $apiCorsHeaders, $template);
    } else {
        $template = str_replace('{{API_CORS_HEADERS}}', '', $template);
    }

    // Content Location (combined: themes, plugins, uploads)
    // Default: fonts, images, media, documents, archives, data, code (PHP sanitizeExtensions blocks dangerous: php, exe, etc.)
    // Fonts: woff, woff2, ttf, eot, otf | Images: ico, webp, avif, jpg, png, gif, bmp, tiff, svg, cur
    // Media: mp3, mp4, m4v, wav, ogg, webm, m4a, flac, mkv, srt | Code: css, js, map, wasm
    // Documents: pdf, doc, docx, xls, xlsx, csv, ppt, pptx, rtf, odt, ods, odp
    // Archives: zip, rar, 7z, tar, gz, bz2, iso | Data: json, xml, txt, yaml, yml
    $defaultContentExts = 'css,js,map,wasm,ico,cur,woff,woff2,ttf,eot,otf,svg,webp,avif,jpg,jpeg,png,gif,bmp,tiff,pdf,txt,json,xml,yaml,yml,docx,doc,xls,xlsx,csv,ppt,pptx,rtf,odt,ods,odp,rar,zip,7z,tar,gz,bz2,iso,mp3,mp4,m4v,wav,ogg,webm,m4a,flac,mkv,srt';
    $contentExts = sanitizeExtensions($config['contentAllowedExtensions'] ?? $defaultContentExts);
    $contentExtPattern = implode('|', array_map(function ($ext) {
        return preg_quote($ext, '/');
    }, $contentExts));

    // ✅ SECURITY: Validate contentPath and contentAlias
    // Only allow: a-z, A-Z, 0-9, -, _, / (must start with /)
    // Prevent: path traversal (..), null bytes, special chars
    $contentPath = $config['contentPath'] ?? '/content';
    $contentAlias = $config['contentAlias'] ?? '/content';
    
    // Validate contentPath
    if (!is_string($contentPath) || 
        !preg_match('/^\/[a-zA-Z0-9\/\-_]*$/', $contentPath) ||
        preg_match('/\.\.|[\x00]/', $contentPath)) {
        echo json_encode(['error' => 'Invalid contentPath. Only a-z, A-Z, 0-9, -, _, / allowed. Must start with /']);
        return;
    }
    
    // Validate contentAlias
    if (!is_string($contentAlias) || 
        !preg_match('/^\/[a-zA-Z0-9\/\-_]*$/', $contentAlias) ||
        preg_match('/\.\.|[\x00]/', $contentAlias)) {
        echo json_encode(['error' => 'Invalid contentAlias. Only a-z, A-Z, 0-9, -, _, / allowed. Must start with /']);
        return;
    }
    
    // ✅ PERFORMANCE: Ensure paths end with / for alias directive (nginx best practice)
    // This prevents nginx from doing extra path resolution
    if (substr($contentPath, -1) !== '/') {
        $contentPath .= '/';
    }
    if (substr($contentAlias, -1) !== '/') {
        $contentAlias .= '/';
    }
    
    $contentExpiration = $config['contentExpiration'] ?? '365d';

    // Replace content path placeholders in template (for writable cache paths)
    $template = str_replace('{{CONTENT_PATH}}', $contentPath, $template);
    $template = str_replace('{{CONTENT_ALIAS}}', $contentAlias, $template);

    // ✅ PERFORMANCE: Use exact match location (^~) for better performance
    // This prevents nginx from checking regex locations
    $contentLocation = "location ^~ {$contentPath} {
  alias \$cache_root{$contentAlias};
  access_log  off;
  expires     {$contentExpiration};
  add_header  Cache-Control \"public, max-age=31536000, immutable\";

  # Image files with WebP support
  location ~* \\.(png|jpe?g|gif)\$ {
    add_header Vary Accept;
    gzip_static off;
    " . ($config['brotliEnabled'] ?? false ? "#brotli_static off;" : "#brotli_static off;") . "
    add_header Cache-Control \"public, must-revalidate, proxy-revalidate, immutable, stale-while-revalidate=86400, stale-if-error=604800\";
    access_log off;
    expires 365d;
    try_files \$uri\$webp_suffix \$uri =404;
  }
  
  # WebP images
  location ~* \\.webp\$ {
    add_header Vary Accept;
    add_header Cache-Control \"public, must-revalidate, proxy-revalidate, immutable, stale-while-revalidate=86400, stale-if-error=604800\";
    access_log off;
    expires 365d;
  }
  
  # All allowed extensions
  location ~* \\.({$contentExtPattern})\$ {
    allow all;
  }
  
  # Deny all other files (any extension not in allowed list above)
  location ~* \\..+\$ {
    deny all;
    return 403;
  }
  
  autoindex off;
}";

    $template = str_replace('{{CONTENT_LOCATION}}', $contentLocation, $template);

    // Query Parameters (with security validation)
    // Allow: lowercase, Unicode letters, numbers, space, dash, underscore
    // Reject: path traversal, null bytes, special chars that could break file system
    $queryParamsCache = '';
    if (!empty($config['cacheQueryParams']) && is_array($config['cacheQueryParams'])) {
        foreach ($config['cacheQueryParams'] as $param) {
            $key = $param['key'] ?? '';
            // Validate key: lowercase, Unicode letters, numbers, space, dash, underscore
            // Reject: path traversal, null bytes, slashes, dots that could be dangerous
            if (
                !empty($key) && preg_match('/^[\p{L}\p{N}\s_-]+$/u', $key) &&
                !preg_match('/\.\.|[\x00\/\\\\]/', $key)
            ) {
                // Normalize key to lowercase for consistency with PHP UriCache
                $keyLower = function_exists('mb_strtolower') ? mb_strtolower($key, 'UTF-8') : strtolower($key);
                // Normalize key for file path: replace space with underscore to match PHP behavior
                // PHP will URL-encode key with space/Unicode, but replace %20 with _, so we normalize space to _ here
                $keyForPath = str_replace(' ', '_', $keyLower);
                // Escape key for nginx regex (use original keyLower for matching, keyForPath for path)
                $keyEscaped = preg_quote($keyLower, '/');
                $keyForPathEscaped = preg_quote($keyForPath, '/');

                $queryParamsCache .= "# Build cache folder Path for \$_GET['{$key}'] field!\n";
                // Capture value (nginx automatically URL-decodes query params)
                // Pattern: match key=value - PHP will validate and sanitize the value
                // Only block path traversal attempts (../, ..\, /, \, null bytes) to prevent escaping cache folder
                // Simple pattern: match .. OR / OR \ OR null byte
                $queryParamsCache .= "if (\$args ~* \"(?:^|&){$keyEscaped}=([^&]+)\") {\n";
                $queryParamsCache .= "    set \$cmsff_param_value \$1;\n";
                $queryParamsCache .= "}\n";
                // Security: only block path traversal - PHP handles other validation
                // Block: .. (path traversal), / (slash), \ (backslash), null byte
                // Use simple alternation instead of character class to avoid escaping issues
                $queryParamsCache .= "if (\$cmsff_param_value ~ \"\\.\\.|/|\\\\|\x00\") {\n";
                $queryParamsCache .= "    set \$cmsff_param_value \"\";\n";
                $queryParamsCache .= "}\n";
                // Only add to args if value passed security check
                $queryParamsCache .= "if (\$cmsff_param_value != \"\") {\n";
                $queryParamsCache .= "    set \$cmsff_args \"\${cmsff_args}{$keyForPathEscaped}/\$cmsff_param_value/\";\n";
                $queryParamsCache .= "}\n";
            }
        }
    }
    $template = str_replace('{{QUERY_PARAMS_CACHE}}', $queryParamsCache, $template);

    // Mobile User Agents
    $mobileAgents = $config['mobileUserAgents'] ?? [];
    if (!empty($mobileAgents) && is_array($mobileAgents)) {
        $mobilePattern = implode('|', array_map(function ($agent) {
            return preg_quote($agent, '/');
        }, $mobileAgents));
        $template = str_replace('{{MOBILE_USER_AGENTS_CHECK}}', "if (\$http_user_agent ~* \"({$mobilePattern})\") {
    set \$cmsff_device \"mobile\";
}", $template);
    } else {
        $template = str_replace('{{MOBILE_USER_AGENTS_CHECK}}', '', $template);
    }

    // Cookie Check (with login bypass)
    // This sets cmsff_login_bypass flag, which is then checked against .login-active file
    $cookies = $config['cookieInvalidate'] ?? [];
    if (!empty($cookies) && is_array($cookies)) {
        $cookiePattern = implode('|', array_map(function ($cookie) {
            return preg_quote($cookie, '/');
        }, $cookies));
        $template = str_replace('{{COOKIE_CHECK}}', "if (\$http_cookie ~* \"({$cookiePattern})\") {
    set \$cmsff_login_bypass \"1\";
}", $template);
    } else {
        $template = str_replace('{{COOKIE_CHECK}}', '', $template);
    }

    // URI Cache Rules: apply in exact user order (each rule sets allow or block; last match wins)
    $uriCacheRules = '';
    $hasAllowRule = false;

    if (!empty($config['cacheableUris']) && is_array($config['cacheableUris'])) {
        foreach ($config['cacheableUris'] as $rule) {
            $path = isset($rule['path']) ? trim((string) $rule['path']) : '';
            if ($path === '') {
                continue;
            }
            $condition = isset($rule['condition']) && in_array($rule['condition'], ['=', '~', '~*'], true)
                ? $rule['condition'] : '=';
            $exclude = filter_var($rule['exclude'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (!$exclude) {
                $hasAllowRule = true;
            }

            // Escape for nginx double-quoted string (path can contain " or \)
            $pathSafe = str_replace(['\\', '"'], ['\\\\', '\\"'], $path);
            $pathComment = str_replace(["\r", "\n"], ' ', $path); // safe for # comment line
            $action = $exclude ? '0' : '1';
            $label = $exclude ? 'Block' : 'Allow';

            if ($condition === '=') {
                $uriCacheRules .= "# URI pattern (exact) [{$label}]: {$pathComment}\n";
                $uriCacheRules .= "if (\$cmsff_uri_path = \"{$pathSafe}\") {\n";
            } elseif ($condition === '~*') {
                $uriCacheRules .= "# URI pattern (iregex) [{$label}]: {$pathComment}\n";
                $uriCacheRules .= "if (\$cmsff_uri_path ~* \"{$pathSafe}\") {\n";
            } else {
                $uriCacheRules .= "# URI pattern (regex) [{$label}]: {$pathComment}\n";
                $uriCacheRules .= "if (\$cmsff_uri_path ~ \"{$pathSafe}\") {\n";
            }
            $uriCacheRules .= "    set \$should_cache \"{$action}\";\n";
            $uriCacheRules .= "}\n";
        }
    }

    // Default: if any Allow rule exists, start with 0 (cache only when a rule allows); else 1 (cache except when a rule blocks)
    $defaultShouldCache = $hasAllowRule ? '0' : '1';
    $template = str_replace('{{SHOULD_CACHE_DEFAULT}}', "set \$should_cache \"{$defaultShouldCache}\";", $template);
    $template = str_replace('{{URI_CACHE_RULES}}', $uriCacheRules, $template);

    // CORS headers for JSON API routes (non-gzip)
    $apiCorsHeadersJson = '';
    if ($config['apiEnabled'] ?? true) {
        $apiCorsHeadersJson = "add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type,Accept,Origin,User-Agent,Referer,X-Requested-With' always;
    # Note: Cannot use Access-Control-Allow-Credentials with wildcard origin

    # If request is OPTIONS, return 204
    if (\$request_method = OPTIONS) {
        add_header 'Access-Control-Max-Age' 1728000;
        add_header 'Content-Type' 'text/plain charset=UTF-8';
        add_header 'Content-Length' 0;
        return 204;
    }";
    }
    $template = str_replace('{{API_CORS_HEADERS_JSON}}', $apiCorsHeadersJson, $template);

    // Gzip Location Block for HTML files
    $gzipLocationBlock = '';
    if ($config['gzipEnabled'] ?? true) {
        $gzipLocationBlock = '# Do not gzip cached files that are already gzipped (HTML)
location ~ /writable/cache/.*html_gzip$ {
    root "$cache_root";
    types {}
    etag on;
    gzip off;
    ' . ($config['brotliEnabled'] ?? false ? '#brotli off;
    #brotli_static off;' : '#brotli off;
    #brotli_static off;') . '
    add_header Content-Type $cmsff_default_type;
    add_header Content-Encoding gzip;
    add_header Vary "Accept-Encoding, Cookie";
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header X-CMSFF-Nginx-Serving-Static $cmsff_is_bypassed;
    add_header X-CMSFF-Nginx-Reason $cmsff_reason;
    add_header X-CMSFF-Nginx-File $cmsff_file;
}';
    }
    $template = str_replace('{{GZIP_LOCATION_BLOCK}}', $gzipLocationBlock, $template);

    // Gzip Location Block for JSON files (API)
    $jsonGzipLocationBlock = '';
    if ($config['gzipEnabled'] ?? true && ($config['apiEnabled'] ?? true)) {
        $jsonGzipCorsHeaders = '';
        if ($config['apiEnabled'] ?? true) {
            $jsonGzipCorsHeaders = "add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type,Accept,Origin,User-Agent,Referer,X-Requested-With' always;
        # Note: Cannot use Access-Control-Allow-Credentials with wildcard origin

        # If request is OPTIONS, return 204
        if (\$request_method = OPTIONS) {
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Type' 'text/plain charset=UTF-8';
            add_header 'Content-Length' 0;
            return 204;
        }";
        }

        $jsonGzipLocationBlock = '# Do not gzip cached files that are already gzipped (JSON/API)
location ~ /writable/cache/.*json_gzip$ {
    root "$cache_root";
    types {}
    etag on;
    gzip off;
    ' . ($config['brotliEnabled'] ?? false ? '#brotli off;
    #brotli_static off;' : '#brotli off;
    #brotli_static off;') . '
    add_header Content-Type application/json;
    add_header Content-Encoding gzip;
    add_header Vary "Accept-Encoding, Cookie";
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header X-CMSFF-Nginx-Serving-Static $cmsff_is_bypassed;
    add_header X-CMSFF-Nginx-Reason $cmsff_reason;
    add_header X-CMSFF-Nginx-File $cmsff_file;
    
    ' . $jsonGzipCorsHeaders . '
}';
    }
    $template = str_replace('{{JSON_GZIP_LOCATION_BLOCK}}', $jsonGzipLocationBlock, $template);

    return $template;
}

function sanitizeExtensions($extensionsString)
{
    $extensions = explode(',', $extensionsString);
    $sanitized = [];
    $dangerousExtensions = [
        'php',
        'php3',
        'php4',
        'php5',
        'php7',
        'php8',
        'phtml',
        'phar',
        'phps',
        'phpt',
        'pht',
        'phtm',
        'pgif',
        'shtml',
        'htaccess',
        'htpasswd',
        'ini',
        'conf',
        'config',
        'sh',
        'bash',
        'cgi',
        'pl',
        'py',
        'rb',
        'exe',
        'dll',
        'so',
        'asp',
        'aspx',
        'jsp',
        'jspx',
        'cfm',
        'cfc',
        'bat',
        'cmd'
    ];

    foreach ($extensions as $ext) {
        $ext = trim(strtolower($ext));
        if (!empty($ext) && !in_array($ext, $dangerousExtensions)) {
            $sanitized[] = $ext;
        }
    }

    return $sanitized;
}

function saveNginxConfig()
{
    // Get the nginx configuration content from POST data
    $nginxConfig = $_POST['config'] ?? '';

    if (empty($nginxConfig)) {
        echo json_encode(['error' => 'No configuration content provided']);
        return;
    }

    // Security check: Validate allowed extensions in location blocks
    $dangerousExtensions = [
        'php',
        'php3',
        'php4',
        'php5',
        'php7',
        'php8',
        'phtml',
        'phar',
        'phps',
        'phpt',
        'pht',
        'phtm',
        'pgif',
        'shtml',
        'htaccess',
        'htpasswd',
        'ini',
        'conf',
        'config',
        'sh',
        'bash',
        'cgi',
        'pl',
        'py',
        'rb',
        'exe',
        'dll',
        'so',
        'asp',
        'aspx',
        'jsp',
        'jspx',
        'cfm',
        'cfc',
        'bat',
        'cmd'
    ];

    // Extract extensions from location blocks: location ~* \.(ext1|ext2|ext3)$
    if (preg_match_all('/location\s+~\*\s+\\\\\.\(([^)]+)\)\$/', $nginxConfig, $matches)) {
        foreach ($matches[1] as $extensionsPattern) {
            // Split by | to get individual extensions
            $extensions = explode('|', $extensionsPattern);

            foreach ($extensions as $ext) {
                // Clean extension (remove backslashes, question marks, etc.)
                $ext = trim($ext);
                $ext = str_replace(['\\', '?'], '', $ext);
                $ext = strtolower($ext);

                // Check if it's a dangerous extension
                if (in_array($ext, $dangerousExtensions)) {
                    echo json_encode([
                        'error' => 'Security violation: Dangerous file extension detected',
                        'extension' => $ext,
                        'details' => "Extension '.{$ext}' is not allowed for security reasons. Please remove it from allowed extensions.",
                        'location_block' => $matches[0][0]
                    ]);
                    return;
                }
            }
        }
    }

    // Get cache root from POST data (user-specified website root path (Don't have /public at the end))
    $cacheRoot = $_POST['cacheRoot'] ?? '';
    if (empty($cacheRoot)) {
        // Fallback to server document root if no cache root provided
        $cacheRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    }

    // Normalize path: remove trailing slashes first
    $cacheRoot = rtrim($cacheRoot, '/\\');

    // Remove /public or /public/ from the END of path only (not from middle)
    // This ensures we don't accidentally remove /public/ that appears in the middle of the path
    if (preg_match('/\/public$/', $cacheRoot)) {
        // Remove /public at the end
        $cacheRoot = substr($cacheRoot, 0, -7); // Remove '/public' (7 chars)
    }

    if (empty($cacheRoot)) {
        echo json_encode(['error' => 'Could not determine document root']);
        return;
    }

    // Get filename from POST (default to cmsfullform.conf)
    $filename = $_POST['filename'] ?? 'cmsfullform.conf';

    // Validate cache root path (prevent directory traversal)
    $cacheRoot = realpath($cacheRoot);
    if ($cacheRoot === false) {
        // If realpath fails, use the original path but validate it
        $cacheRoot = $_POST['cacheRoot'] ?? $_SERVER['DOCUMENT_ROOT'];
        // Normalize: remove trailing slashes and /public at the end
        $cacheRoot = rtrim($cacheRoot, '/\\');
        if (preg_match('/\/public$/', $cacheRoot)) {
            $cacheRoot = substr($cacheRoot, 0, -7);
        }
        // Remove any ../ or ..\\ patterns
        $cacheRoot = str_replace(['../', '..\\'], '', $cacheRoot);
    }

    // mkdir if not exists $cacheRoot
    if (!file_exists($cacheRoot)) {
        if (!mkdir($cacheRoot, 0755, true)) {
            echo json_encode([
                'error' => 'Failed to create directory',
                'path' => $cacheRoot
            ]);
            return;
        }
    }

    // Validate filename (prevent directory traversal and dangerous extensions)
    $filename = basename($filename);
    if (!preg_match('/^[a-zA-Z0-9._-]+\.conf$/', $filename)) {
        echo json_encode(['error' => 'Invalid filename. Must be alphanumeric with .conf extension']);
        return;
    }

    // Define the target file path
    $targetFile = $cacheRoot . '/' . $filename;

    // Try to save the file
    $result = file_put_contents($targetFile, $nginxConfig);

    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Configuration file saved successfully',
            'file_path' => $targetFile,
            'bytes_written' => $result
        ]);
    } else {
        echo json_encode([
            'error' => 'Failed to save configuration file',
            'file_path' => $targetFile,
            'check_permissions' => 'Make sure the web server has write permissions to the specified path: ' . $cacheRoot
        ]);
    }
}
