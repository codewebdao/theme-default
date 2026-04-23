<?php
namespace App\Middleware;

/**
 * Unified Authentication Middleware
 * 
 * Auto-detects request type (API vs Web) and delegates to appropriate middleware
 * - API requests: Uses ApiAuthMiddleware (Bearer token, JSON response)
 * - Web requests: Uses WebAuthMiddleware (Session, redirect)
 * 
 * Detection based on:
 * 1. URL path (starts with /api/)
 * 2. Content-Type header (application/json)
 * 3. Authorization header (Bearer token)
 */
class AuthMiddleware
{
    /**
     * Handle authentication with auto-detection
     *
     * @param mixed $request Request information
     * @param callable $next Next middleware
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('AuthMiddleware::handle');
        }
        // Detect if API request
        $isApiRequest = $this->isApiRequest();

        if ($isApiRequest) {
            // Use API authentication (Bearer token). Chain runs inside apiAuth->handle when auth succeeds.
            $apiAuth = new \App\Middleware\Api\ApiAuthMiddleware();
            $result = $apiAuth->handle($request, $next);
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('AuthMiddleware::handle');
            }
            // Return delegated result (never call $next again - would double-invoke controller)
            return $result;
        }

        // Use Web authentication (Session). Chain runs inside webAuth->handle when auth succeeds.
        $webAuth = new \App\Middleware\Web\WebAuthMiddleware();
        $result = $webAuth->handle($request, $next);
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('AuthMiddleware::handle');
        }
        // Return delegated result (never call $next again - would double-invoke controller)
        return $result;
    }

    /**
     * Detect if request is API request
     *
     * @return bool
     */
    private function isApiRequest()
    {
        // Check URL path
        if (strpos(APP_URI['uri'], 'api/') === 0) {
            return true;
        }

        // // Check Content-Type header
        // $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        // if (strpos($contentType, 'application/json') !== false) {
        //     return true;
        // }

        // // Check Accept header
        // $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        // if (strpos($accept, 'application/json') !== false && 
        //     strpos($accept, 'text/html') === false) {
        //     return true;
        // }

        // // Check for Authorization Bearer header
        // $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        // if (strpos($auth, 'Bearer ') !== false) {
        //     return true;
        // }

        // // Check X-Requested-With (AJAX)
        // if (is_ajax()) {
        //    return true;
        //}

        return false;
    }
}
