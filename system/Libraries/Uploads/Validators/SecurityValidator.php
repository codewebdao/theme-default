<?php

namespace System\Libraries\Uploads\Validators;

/**
 * SecurityValidator - Validate security threats
 * 
 * Checks for security threats:
 * - PHP code injection
 * - SVG XSS attacks
 * - Double extension (file.php.jpg)
 * - Null byte injection
 * - Malicious content patterns
 * - Path traversal attempts
 * 
 * @package System\Libraries\Uploads\Validators
 * @version 2.0.0
 */
class SecurityValidator
{
    private $config;
    
    /**
     * Dangerous patterns to scan in file content
     */
    private $dangerousPatterns = [
        // PHP code
        '/<\?php/i',
        '/<\?=/i',
        '/<\?/i',
        '/<%/i',  // ASP-style tags
        
        // Script tags
        '/<script[\s>]/i',
        '/<\/script>/i',
        
        // Dangerous functions - Code execution
        '/eval\s*\(/i',
        '/assert\s*\(/i',
        '/base64_decode\s*\(/i',
        '/system\s*\(/i',
        '/exec\s*\(/i',
        '/shell_exec\s*\(/i',
        '/passthru\s*\(/i',
        '/proc_open\s*\(/i',
        '/popen\s*\(/i',
        '/pcntl_exec\s*\(/i',
        '/curl_exec\s*\(/i',
        '/curl_multi_exec\s*\(/i',
        '/parse_ini_file\s*\(/i',
        '/show_source\s*\(/i',
        '/highlight_file\s*\(/i',
        '/phpinfo\s*\(/i',
        '/posix_mkfifo\s*\(/i',
        '/posix_getlogin\s*\(/i',
        '/posix_ttyname\s*\(/i',
        '/getenv\s*\(/i',
        '/get_current_user\s*\(/i',
        '/proc_get_status\s*\(/i',
        '/get_cfg_var\s*\(/i',
        '/disk_free_space\s*\(/i',
        '/disk_total_space\s*\(/i',
        '/diskfreespace\s*\(/i',
        '/getcwd\s*\(/i',
        '/getlastmo\s*\(/i',
        '/getmygid\s*\(/i',
        '/getmyinode\s*\(/i',
        '/getmypid\s*\(/i',
        '/getmyuid\s*\(/i',
        
        // File operations - SSRF & File manipulation
        '/file_get_contents\s*\(/i',
        '/file_put_contents\s*\(/i',
        '/fopen\s*\(/i',
        '/readfile\s*\(/i',
        '/unlink\s*\(/i',
        '/rmdir\s*\(/i',
        '/mkdir\s*\(/i',
        '/rename\s*\(/i',
        '/copy\s*\(/i',
        '/chgrp\s*\(/i',
        '/chmod\s*\(/i',
        '/chown\s*\(/i',
        '/file\s*\(/i',
        '/fileatime\s*\(/i',
        '/filectime\s*\(/i',
        '/filegroup\s*\(/i',
        '/fileinode\s*\(/i',
        '/filemtime\s*\(/i',
        '/fileowner\s*\(/i',
        '/fileperms\s*\(/i',
        '/filesize\s*\(/i',
        '/filetype\s*\(/i',
        '/glob\s*\(/i',
        '/is_dir\s*\(/i',
        '/is_file\s*\(/i',
        '/is_link\s*\(/i',
        '/symlink\s*\(/i',
        '/link\s*\(/i',
        '/tempnam\s*\(/i',
        '/tmpfile\s*\(/i',
        
        // SQL injection patterns
        '/union\s+select/i',
        '/union\s+all\s+select/i',
        '/drop\s+table/i',
        '/drop\s+database/i',
        '/insert\s+into/i',
        '/delete\s+from/i',
        '/update\s+.*\s+set/i',
        '/create\s+table/i',
        '/alter\s+table/i',
        '/truncate\s+table/i',
        '/load_file\s*\(/i',
        '/into\s+outfile/i',
        '/into\s+dumpfile/i',
        
        // XSS patterns
        '/javascript:/i',
        '/vbscript:/i',
        '/data:text\/html/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        '/onclick\s*=/i',
        '/onmouseover\s*=/i',
        '/onmouseout\s*=/i',
        '/onfocus\s*=/i',
        '/onblur\s*=/i',
        '/onchange\s*=/i',
        '/onsubmit\s*=/i',
        
        // Command injection
        '/`.*`/i',  // Backticks
        '/\$\(.*\)/i',  // Command substitution
        '/;\s*ls\s/i',
        '/;\s*cat\s/i',
        '/;\s*wget\s/i',
        '/;\s*curl\s/i',
        '/\|\s*nc\s/i',  // Netcat
        '/\|\s*bash/i',
        '/\|\s*sh/i',
        
        // Serialization attacks
        '/O:\d+:"/i',  // PHP object serialization
        '/unserialize\s*\(/i',
        
        // Include/Require (LFI/RFI)
        '/include\s*\(/i',
        '/include_once\s*\(/i',
        '/require\s*\(/i',
        '/require_once\s*\(/i',
        
        // Webshells patterns
        '/c99shell/i',
        '/r57shell/i',
        '/b374k/i',
        '/wso\s*shell/i',
        '/FilesMan/i',
        '/IndoXploit/i',
        '/\$_GET\[.*\]\s*\(/i',
        '/\$_POST\[.*\]\s*\(/i',
        '/\$_REQUEST\[.*\]\s*\(/i',
        '/\$_COOKIE\[.*\]\s*\(/i',
        '/\$_SERVER\[.*\]\s*\(/i',
    ];
    
    /**
     * Dangerous file extensions
     */
    private $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash', 'zsh',
        'js', 'jar', 'app', 'deb', 'rpm',
        'vbs', 'vbe', 'ws', 'wsf', 'wsh', 'msi',
        'scr', 'dll', 'sys', 'drv', 'cpl',
        'htaccess', 'htpasswd', 'ini', 'config', 'conf'
    ];
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }
    
    /**
     * Validate security
     * 
     * @param array $file File array from $_FILES
     * @param array $options Custom options
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function validate($file, $options = [])
    {
        $filename = $file['name'] ?? '';
        $tmpPath = $file['tmp_name'] ?? '';
        
        // Check filename
        if (empty($filename)) {
            return [
                'success' => false,
                'error' => 'Invalid filename'
            ];
        }
        
        // Check double extension
        if ($this->hasDoubleExtension($filename)) {
            return [
                'success' => false,
                'error' => 'Double extension is not allowed (e.g., file.php.jpg)'
            ];
        }
        
        // Check null byte injection
        if ($this->hasNullByte($filename)) {
            return [
                'success' => false,
                'error' => 'Filename contains null byte - possible security attack'
            ];
        }
        
        // Check path traversal
        if ($this->hasPathTraversal($filename)) {
            return [
                'success' => false,
                'error' => 'Filename contains path traversal patterns (../, ..\\, etc.)'
            ];
        }
        
        // Check dangerous characters in filename
        if ($this->hasDangerousCharacters($filename)) {
            return [
                'success' => false,
                'error' => 'Filename contains dangerous characters'
            ];
        }
        
        // Check file content (if enabled)
        // Skip binary files (images, videos, etc.) - only scan text-based files
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $binaryExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'ico', 'tiff', 'tif', 
                             'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm',
                             'mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a',
                             'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                             'zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
        
        $shouldScanContent = ($options['scan_content'] ?? true) && !in_array($ext, $binaryExtensions);
        
        if ($shouldScanContent) {
            if (!empty($tmpPath) && file_exists($tmpPath)) {
                $contentCheck = $this->scanFileContent($tmpPath);
                if (!$contentCheck['success']) {
                    return $contentCheck;
                }
            }
        }
        
        // Check SVG specifically (high risk for XSS)
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'svg') {
            if (!empty($tmpPath) && file_exists($tmpPath)) {
                $svgCheck = $this->validateSvg($tmpPath);
                if (!$svgCheck['success']) {
                    return $svgCheck;
                }
            }
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Check double extension (e.g., file.php.jpg)
     * 
     * @param string $filename Filename
     * @return bool
     */
    private function hasDoubleExtension($filename)
    {
        $parts = explode('.', $filename);
        
        // Need at least 3 parts: name.ext1.ext2
        if (count($parts) < 3) {
            return false;
        }
        
        // Check if any part (except last) is dangerous extension
        for ($i = 0; $i < count($parts) - 1; $i++) {
            if (in_array(strtolower($parts[$i]), $this->dangerousExtensions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check null byte injection
     * 
     * @param string $filename Filename
     * @return bool
     */
    private function hasNullByte($filename)
    {
        return strpos($filename, "\0") !== false || strpos($filename, '%00') !== false;
    }
    
    /**
     * Check path traversal attempts
     * 
     * @param string $filename Filename
     * @return bool
     */
    private function hasPathTraversal($filename)
    {
        $patterns = [
            '../',
            '..\\',
            './',
            '.\\',
            '~/',
            '%2e%2e/',
            '%2e%2e%5c',
        ];
        
        $filenameLower = strtolower($filename);
        
        foreach ($patterns as $pattern) {
            if (strpos($filenameLower, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check dangerous characters in filename
     * 
     * @param string $filename Filename
     * @return bool
     */
    private function hasDangerousCharacters($filename)
    {
        // Allow: letters, numbers, dash, underscore, dot, space
        // Disallow: special chars that could be used in attacks
        $dangerousChars = ['<', '>', ':', '"', '/', '\\', '|', '?', '*', ';', '&', '$', '`', '\''];
        
        foreach ($dangerousChars as $char) {
            if (strpos($filename, $char) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Scan file content for malicious code
     * 
     * @param string $filePath File path
     * @return array ['success' => bool, 'error' => string|null]
     */
    private function scanFileContent($filePath)
    {
        // Check if file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            // File not readable, skip content scan (extension check is enough)
            return ['success' => true, 'error' => null];
        }
        
        try {
            // Read first 16KB of file (enough to detect most attacks)
            $content = @file_get_contents($filePath, false, null, 0, 16384);
            
            if ($content === false) {
                // Cannot read, but don't fail validation (extension check is enough)
                return ['success' => true, 'error' => null];
            }
            
            // Scan for dangerous patterns
            foreach ($this->dangerousPatterns as $pattern) {
                if (@preg_match($pattern, $content)) {
                    return [
                        'success' => false,
                        'error' => 'File contains malicious code or malware'
                    ];
                }
            }
            
            return ['success' => true, 'error' => null];
        } catch (\Exception $e) {
            // Error during scan, but don't fail validation
            return ['success' => true, 'error' => null];
        }
    }
    
    /**
     * Validate SVG file for XSS attacks
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    private function validateSvg($filePath)
    {
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            return [
                'success' => false,
                'error' => 'Cannot read SVG file'
            ];
        }
        
        // SECURITY: Check for XXE (XML External Entity) attacks
        if (preg_match('/<!DOCTYPE[^>]*\[/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains DOCTYPE with entity declarations - possible XXE attack'
            ];
        }
        
        if (preg_match('/<!ENTITY/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains ENTITY declarations - possible XXE attack'
            ];
        }
        
        if (preg_match('/SYSTEM\s+["\']file:/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains SYSTEM file: reference - possible XXE attack'
            ];
        }
        
        // Check for script tags
        if (preg_match('/<script[\s>]/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains script tag - possible XSS attack'
            ];
        }
        
        // Check for event handlers
        $eventHandlers = [
            'onload', 'onerror', 'onclick', 'onmouseover', 'onmouseout',
            'onmousemove', 'onmousedown', 'onmouseup', 'onfocus', 'onblur',
            'onchange', 'onsubmit', 'onkeydown', 'onkeyup', 'onkeypress'
        ];
        
        foreach ($eventHandlers as $handler) {
            if (preg_match('/' . $handler . '\s*=/i', $content)) {
                return [
                    'success' => false,
                    'error' => "SVG contains event handler ({$handler}) - possible XSS attack"
                ];
            }
        }
        
        // Check for javascript: protocol
        if (preg_match('/javascript:/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains javascript: protocol - possible XSS attack'
            ];
        }
        
        // Check for data: protocol with script
        if (preg_match('/data:.*script/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains data: protocol with script - possible XSS attack'
            ];
        }
        
        // Check for external references
        if (preg_match('/<use[\s>].*xlink:href\s*=\s*["\']https?:/i', $content)) {
            return [
                'success' => false,
                'error' => 'SVG contains external reference - possibly unsafe'
            ];
        }
        
        return ['success' => true, 'error' => null];
    }
    
    /**
     * Sanitize SVG content (remove dangerous elements)
     * 
     * Clean SVG content instead of rejecting entirely
     * Allow safe SVG upload after sanitization
     * 
     * @param string $svgContent SVG content
     * @param array $options Sanitization options
     * @return array ['success' => bool, 'content' => string, 'removed' => array]
     */
    public function sanitizeSvg($svgContent, $options = [])
    {
        $removed = [];
        $originalContent = $svgContent;
        
        // Remove script tags and their contents
        $beforeCount = substr_count($svgContent, '<script');
        $svgContent = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $svgContent);
        $afterCount = substr_count($svgContent, '<script');
        if ($beforeCount > $afterCount) {
            $removed[] = 'script tags (' . ($beforeCount - $afterCount) . ')';
        }
        
        // Remove object tags
        $beforeCount = substr_count($svgContent, '<object');
        $svgContent = preg_replace('/<object\b[^>]*>.*?<\/object>/is', '', $svgContent);
        $afterCount = substr_count($svgContent, '<object');
        if ($beforeCount > $afterCount) {
            $removed[] = 'object tags (' . ($beforeCount - $afterCount) . ')';
        }
        
        // Remove embed tags
        $beforeCount = substr_count($svgContent, '<embed');
        $svgContent = preg_replace('/<embed\b[^>]*>/is', '', $svgContent);
        $afterCount = substr_count($svgContent, '<embed');
        if ($beforeCount > $afterCount) {
            $removed[] = 'embed tags (' . ($beforeCount - $afterCount) . ')';
        }
        
        // Remove iframe tags
        $beforeCount = substr_count($svgContent, '<iframe');
        $svgContent = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $svgContent);
        $afterCount = substr_count($svgContent, '<iframe');
        if ($beforeCount > $afterCount) {
            $removed[] = 'iframe tags (' . ($beforeCount - $afterCount) . ')';
        }
        
        // Remove event handlers (onload, onclick, etc.)
        $eventHandlers = [
            'onload', 'onerror', 'onclick', 'onmouseover', 'onmouseout',
            'onmousemove', 'onmousedown', 'onmouseup', 'onfocus', 'onblur',
            'onchange', 'onsubmit', 'onkeydown', 'onkeyup', 'onkeypress',
            'onabort', 'ondblclick', 'onreset', 'onselect', 'onunload'
        ];
        
        $eventCount = 0;
        foreach ($eventHandlers as $handler) {
            $beforeCount = preg_match_all('/' . $handler . '\s*=/i', $svgContent);
            $svgContent = preg_replace('/' . $handler . '\s*=\s*["\'][^"\']*["\']/i', '', $svgContent);
            $afterCount = preg_match_all('/' . $handler . '\s*=/i', $svgContent);
            $eventCount += ($beforeCount - $afterCount);
        }
        if ($eventCount > 0) {
            $removed[] = 'event handlers (' . $eventCount . ')';
        }
        
        // Remove javascript: protocol
        $beforeCount = substr_count(strtolower($svgContent), 'javascript:');
        $svgContent = preg_replace('/javascript:/i', '', $svgContent);
        $afterCount = substr_count(strtolower($svgContent), 'javascript:');
        if ($beforeCount > $afterCount) {
            $removed[] = 'javascript: URLs (' . ($beforeCount - $afterCount) . ')';
        }
        
        // Remove data: protocol with script
        $beforeCount = preg_match_all('/data:.*script/i', $svgContent);
        $svgContent = preg_replace('/data:.*script/i', '', $svgContent);
        $afterCount = preg_match_all('/data:.*script/i', $svgContent);
        if ($beforeCount > $afterCount) {
            $removed[] = 'data:script URLs (' . ($beforeCount - $afterCount) . ')';
        }
        
        // Remove external references (optional, configurable)
        if ($options['remove_external_refs'] ?? true) {
            $beforeCount = preg_match_all('/<use[\s>].*xlink:href\s*=\s*["\']https?:/i', $svgContent);
            $svgContent = preg_replace('/(<use[^>]*xlink:href\s*=\s*["\'])https?:[^"\']*(["\'])/i', '$1$2', $svgContent);
            $afterCount = preg_match_all('/<use[\s>].*xlink:href\s*=\s*["\']https?:/i', $svgContent);
            if ($beforeCount > $afterCount) {
                $removed[] = 'external references (' . ($beforeCount - $afterCount) . ')';
            }
        }
        
        // Remove any remaining dangerous attributes
        $dangerousAttrs = ['formaction', 'action', 'xlink:href="javascript:', 'href="javascript:'];
        foreach ($dangerousAttrs as $attr) {
            $beforeCount = substr_count(strtolower($svgContent), strtolower($attr));
            $svgContent = preg_replace('/' . preg_quote($attr, '/') . '/i', '', $svgContent);
            $afterCount = substr_count(strtolower($svgContent), strtolower($attr));
            if ($beforeCount > $afterCount) {
                $removed[] = $attr . ' (' . ($beforeCount - $afterCount) . ')';
            }
        }
        
        return [
            'success' => true,
            'content' => $svgContent,
            'removed' => $removed,
            'was_modified' => $originalContent !== $svgContent
        ];
    }
    
    /**
     * Sanitize and save SVG file
     * 
     * @param string $filePath Path to SVG file
     * @param array $options Sanitization options
     * @return array ['success' => bool, 'error' => string|null, 'removed' => array]
     */
    public function sanitizeSvgFile($filePath, $options = [])
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'error' => 'File not found',
                'removed' => []
            ];
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [
                'success' => false,
                'error' => 'Cannot read file',
                'removed' => []
            ];
        }
        
        $result = $this->sanitizeSvg($content, $options);
        
        if ($result['success'] && $result['was_modified']) {
            // Save sanitized content
            if (file_put_contents($filePath, $result['content']) === false) {
                return [
                    'success' => false,
                    'error' => 'Cannot write sanitized content',
                    'removed' => $result['removed']
                ];
            }
        }
        
        return [
            'success' => true,
            'error' => null,
            'removed' => $result['removed'],
            'was_modified' => $result['was_modified']
        ];
    }
    
    /**
     * Add custom dangerous pattern
     * 
     * @param string $pattern Regex pattern
     */
    public function addDangerousPattern($pattern)
    {
        $this->dangerousPatterns[] = $pattern;
    }
    
    /**
     * Add custom dangerous extension
     * 
     * @param string $extension Extension (without dot)
     */
    public function addDangerousExtension($extension)
    {
        $this->dangerousExtensions[] = strtolower($extension);
    }
}
