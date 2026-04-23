<?php
namespace App\Middleware\Api;

use App\Libraries\Fasttoken;
use App\Models\UserSessionsModel;
use System\Libraries\Response;

/**
 * API Authentication Middleware
 * 
 * Lightweight token validation for API requests
 * Returns 401 JSON if unauthorized (NO redirects)
 * 
 * Features:
 * - Bearer token validation (signature + expiration)
 * - Token type verification (access only)
 * - Populates global $me_info for controllers
 * 
 * Note: Does NOT validate fingerprint or session DB on every request
 * for performance reasons. Fingerprint validation only needed for:
 * - Login (creating session)
 * - Token refresh (validating device)
 * - Sensitive operations (password change, etc.)
 */
class ApiAuthMiddleware
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
        // Get Bearer token from Authorization header
        $token = \App\Libraries\Fasttoken::headerToken();

        if (!$token) {
            return $this->unauthorized(\App\Libraries\Fastlang::__('No authentication token provided'), 'NO_TOKEN');
        }

        // Validate token structure and expiration (lightweight check)
        // Note: We don't validate session DB on every request for performance
        // Session validation happens only on sensitive operations (login, refresh, etc.)
        $tokenData = \App\Libraries\Fasttoken::validateAccessToken($token, false); // false = skip DB check

        if (!$tokenData) {
            return $this->unauthorized(\App\Libraries\Fastlang::__('Invalid or expired access token'), 'INVALID_TOKEN');
        }

        // Optional: Quick session check in cache (future optimization)
        // For now, trust the token signature and expiration

        // Set user info in global for controllers
        global $me_info;
        $me_info = [
            'id' => $tokenData['user_id'],
            'user_id' => $tokenData['user_id'],
            'role' => $tokenData['role'] ?? 'member',
            'permissions' => $tokenData['permissions'] ?? [],
            'username' => $tokenData['username'] ?? '',
            'email' => $tokenData['email'] ?? ''
        ];

        // All checks passed - allow request
        return $next($request);
    }

    /**
     * Send 401 unauthorized response and exit
     *
     * @param string $message Error message
     * @param string $errorCode Error code
     * @return void
     */
    private function unauthorized($message, $errorCode)
    {
        Response::sendUnauthorized($message, $errorCode);
    }
}

