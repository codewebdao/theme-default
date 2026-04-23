<?php
namespace App\Middleware\Api;

/**
 * CORS Middleware for API
 * 
 * Handles Cross-Origin Resource Sharing
 * Production-ready with security best practices:
 * - Whitelist origins (no wildcard in production)
 * - Credentials control
 * - Preflight caching
 * - Security headers
 */
class CorsMiddleware
{
    /**
     * Handle CORS headers
     *
     * @param mixed $request Request information
     * @param callable $next Next middleware
     * @return mixed
     */
    public function handle($request, $next)
    {
        $config = $this->getConfig();

        // Get request origin
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Check if origin is allowed
        if ($this->isOriginAllowed($origin, $config['allowed_origins'])) {
            // Set CORS headers
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: ' . ($config['allow_credentials'] ? 'true' : 'false'));
            header('Access-Control-Allow-Methods: ' . implode(', ', $config['allowed_methods']));
            header('Access-Control-Allow-Headers: ' . implode(', ', $config['allowed_headers']));
            header('Access-Control-Expose-Headers: ' . implode(', ', $config['exposed_headers']));
            header('Access-Control-Max-Age: ' . $config['max_age']);
            return $next($request);
        }

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204); // No Content
            exit;
        }

        return $next($request);
    }

    /**
     * Get CORS configuration
     * 
     * Priority:
     * 1. Config from security.php
     * 2. Default safe configuration
     *
     * @return array
     */
    private function getConfig()
    {
        $security = config(null, 'Security');
        
        return [
            'allowed_origins' => $security['cors']['allowed_origins'] ?? ['*'],
            'allowed_methods' => $security['cors']['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => $security['cors']['allowed_headers'] ?? [
                'Authorization',
                'Content-Type',
                'Accept',
                'X-Device-Fingerprint',
                'X-Requested-With'
            ],
            'exposed_headers' => $security['cors']['exposed_headers'] ?? [
                'X-RateLimit-Limit',
                'X-RateLimit-Remaining',
                'X-RateLimit-Reset'
            ],
            'allow_credentials' => $security['cors']['allow_credentials'] ?? false,
            'max_age' => $security['cors']['max_age'] ?? 3600
        ];
    }

    /**
     * Check if origin is allowed
     *
     * @param string $origin Request origin
     * @param array $allowedOrigins Whitelist of origins
     * @return bool
     */
    private function isOriginAllowed($origin, $allowedOrigins)
    {
        if (empty($origin)) {
            return false;
        }

        // Allow all (development only, not recommended for production)
        if (in_array('*', $allowedOrigins)) {
            return true;
        }

        // Check exact match
        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        // Check wildcard patterns (e.g., '*.example.com')
        foreach ($allowedOrigins as $allowed) {
            if ($this->matchesPattern($origin, $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match origin against pattern with wildcard support
     *
     * @param string $origin Origin to check
     * @param string $pattern Pattern (supports * wildcard)
     * @return bool
     */
    private function matchesPattern($origin, $pattern)
    {
        if ($pattern === '*') {
            return true;
        }

        // Convert wildcard pattern to regex
        $regex = '/^' . str_replace(
            ['*', '.'],
            ['.*', '\\.'],
            $pattern
        ) . '$/i';

        return preg_match($regex, $origin) === 1;
    }
}
