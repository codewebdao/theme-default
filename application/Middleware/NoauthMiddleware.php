<?php
namespace App\Middleware;

use System\Libraries\Session;
use System\Libraries\Response;

/**
 * No Authentication Middleware (Guest Only)
 * 
 * Ensures user is NOT logged in
 * Used for: login, register, forgot password pages
 * 
 * Behavior:
 * - If logged in + API request → Return 403 JSON
 * - If logged in + Web request → Redirect to dashboard
 * - If not logged in → Allow (continue)
 */
class NoauthMiddleware
{
    /**
     * Handle guest-only request
     *
     * @param mixed $request Request information
     * @param callable $next Next middleware
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('NoauthMiddleware::handle');
        }
        // Check if user is logged in (Session or Bearer token)
        $isLoggedIn = $this->checkAuthentication();

        if ($isLoggedIn) {
            // User is logged in - should not access this page
            $isApiRequest = $this->isApiRequest();

            if ($isApiRequest) {
                if (APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop('NoauthMiddleware::handle');
                }
                // API: Return 403 JSON
                Response::sendError(
                    \App\Libraries\Fastlang::__('Already authenticated. Logout first to access this endpoint.'),
                    [],
                    'ALREADY_AUTHENTICATED',
                    403
                );
            } else {
                if (APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop('NoauthMiddleware::handle');
                }
                // Web: Redirect to profile/dashboard
                Session::flash('info', \App\Libraries\Fastlang::__('You are already logged in'));
                redirect(auth_url('profile'));
                exit;
            }
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('NoauthMiddleware::handle');
        }
        // User not logged in - allow access (guest)
        return $next($request);
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    private function checkAuthentication()
    {
        // Check session
        if (Session::has('user_id')) {
            return true;
        }

        // Check Bearer token (API)
        if ($token = \App\Libraries\Fasttoken::headerToken()) {
            $tokenData = \App\Libraries\Fasttoken::checkToken($token);
            if ($tokenData && isset($tokenData['user_id'])) {
                return true;
            }
        }

        return false;
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

        return false;
    }
}

