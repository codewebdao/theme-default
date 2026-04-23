<?php

namespace App\Controllers;

use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Render\Schema;
use System\Libraries\Render\Head\Context;
use System\Libraries\Session;
use System\Libraries\Security;
use System\Libraries\Validate;
use System\Libraries\Events;

/**
 * Frontend Authentication Controller
 * 
 * This controller handles authentication for the frontend interface.
 * It extends BaseAuthController to inherit common authentication logic
 * and implements frontend-specific response handling.
 * 
 * @package App\Controllers
 * @author Your Name
 * @version 1.0.0
 */
class AuthController extends BaseAuthController
{
    /** View namespace: account pages under active web theme (content/themes/{web}/common/auth/). */
    private const VIEW_NS_AUTH = 'common/auth';

    /** View namespace: optional v2 UI (common/auth-v2/). */
    private const VIEW_NS_AUTH_V2 = 'common/auth-v2';

    /**
     * Auth UI version: 'v1' or 'v2'
     * @var string
     */
    protected $authUiVersion = 'v1';

    /**
     * Constructor - Initialize frontend-specific components
     */
    public function __construct()
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('AuthController::__construct');
            \System\Libraries\Monitor::mark('AuthController::parentConstructor');
        }
        parent::__construct();
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('AuthController::parentConstructor');
        }

        $this->authUiVersion = $this->_getAuthUiVersion();

        if (View::getNamespace() === null) {
            View::namespace($this->authUiVersion === 'v2' ? self::VIEW_NS_AUTH_V2 : self::VIEW_NS_AUTH);
        }
        load_helpers(['Languages', 'Images', 'View', 'Links']);

        // Account routes use web scope + web theme assets (ThemeContext/AssetManager area Frontend → scope web).
        if ($this->authUiVersion === 'v2') {
            View::addCss('auth-v2', 'css/auth-v2.css', [], null, 'all', false);
            View::addJs('auth-v2', 'js/auth-v2.js', [], null, true, false, true);
        } else {
            View::addCss('auth', 'css/auth.bundle.css', [], null, 'all', false);
            View::addCss('flatpickr', 'css/flatpickr.css', [], null, 'all', false);
            View::addJs('flatpickr', 'js/flatpickr.v4.6.13.min.js', [], null, true, false, true);
        }
        View::addJs('lucide-auth', 'js/lucide-auth.min.js', [], null, true, false, true);
        View::addJs('alpinejs', 'js/alpinejs.3.15.0.min.js', [], null, true, false, true);
        View::addJs('device-fingerprint', 'js/device-fingerprint.js', [], null, true, false, true);

        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('AuthController::__construct');
        }
    }

    /**
     * Bật Head + Schema context cho trang account (meta description, canonical, OG, JSON-LD WebPage đúng URL).
     * FrontendController không chạy trên auth nên mặc định Head không có context → thiếu meta/canonical.
     */
    protected function prepareAuthPageContext(string $canonicalUrl, ?string $pageNameForSchema = null): void
    {
        load_helpers(['storage']);
        $lang = defined('APP_LANG') ? APP_LANG : 'en';
        $siteDesc = (string) (option('site_desc', $lang) ?? '');
        $siteTitle = (string) (option('site_title', $lang) ?: '');
        $desc = $siteDesc !== '' ? $siteDesc : ($pageNameForSchema ?? $siteTitle);
        $schemaTitle = $pageNameForSchema ?? $siteTitle;
        // Layout bất kỳ không khớp case trong Builder → fallback mặc định (meta, canonical, OG)
        Context::setCurrent('auth', [
            'canonical'   => $canonicalUrl,
            'page_title'  => $pageNameForSchema ?? '',
            'robots'      => 'noindex, follow',
        ]);
        Schema::setCurrentContext('webpage', [
            'url' => $canonicalUrl,
            'title' => $schemaTitle,
            'description' => $desc,
        ]);
    }

    /**
     * Read auth UI version from web theme config: config/theme.php (preferred), then legacy Config/Config.php.
     *
     * @return string 'v1' or 'v2'
     */
    protected function _getAuthUiVersion()
    {
        $base = defined('APP_THEME_PATH') ? rtrim(APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR : '';
        if ($base !== '') {
            $themePhp = $base . 'config' . DIRECTORY_SEPARATOR . 'theme.php';
            if (is_readable($themePhp)) {
                $cfg = include $themePhp;
                if (is_array($cfg) && isset($cfg['auth_ui']) && $cfg['auth_ui'] === 'v2') {
                    return 'v2';
                }
            }
            $legacy = $base . 'Config' . DIRECTORY_SEPARATOR . 'Config.php';
            if (is_readable($legacy)) {
                $config = require $legacy;
                if (is_array($config) && isset($config['auth_ui']) && $config['auth_ui'] === 'v2') {
                    return 'v2';
                }
            }
        }

        return 'v1';
    }

    // Use web CSRF
    protected function verifyCsrfToken($token)
    {
        return Session::csrf_verify($token);
    }

    /**
     * Check login status and redirect appropriately
     * Auto-login from remember cookie if available
     * 
     * @return void
     */
    public function index()
    {
        // Try auto-login from remember cookie
        $this->_autoLoginFromRemember();

        if (Session::has('user_id')) {
            // If already logged in, redirect to dashboard
            redirect(auth_url('profile'));
        } else {
            // If not logged in, redirect to login page
            redirect(auth_url('login'));
        }
    }

    // login() is inherited from BaseAuthController
    public function login()
    {
        $this->_autoLoginFromRemember();
        return parent::login();
    }

    // register() is inherited from BaseAuthController

    // forgot() is inherited from BaseAuthController

    // logout() is inherited from BaseAuthController

    // Abstract method implementations for frontend
    protected function handleInactiveAccount($user)
    {
        Session::flash('error', __('Account not active. Please confirm your email.'));
        return redirect(auth_url('confirm'));
    }

    protected function handleSuccessfulLogin($user)
    {
        Session::flash('success', __('Login successful'));
        return redirect(auth_url('profile'));
    }

    // Hooks for shared login()
    protected function displayLoginForm()
    {
        $this->prepareAuthPageContext(auth_url('login'), __('Login Account'));
        // Set page title
        Head::setTitle([__('Welcome Back - Sign In')]);
        $this->data('title', __('Welcome Back - Sign In'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('login', $this->data)->render();
    }

    protected function handleLoginErrors($errors)
    {
        $this->data('errors', $errors);
        return $this->displayLoginForm();
    }

    protected function handleSuccessfulRegistration($user_id, $userData)
    {
        Session::flash('success', __('Registration successful. Please confirm your email.'));
        return redirect(auth_url('confirm'));
    }

    protected function handleForgotPasswordSent($user)
    {
        Session::flash('success', __('Password reset code sent to: %1% successfully', $user['email']));
        return redirect(auth_url('confirm'));
    }

    // Additional abstract method implementations
    protected function handleAlreadyLoggedIn()
    {
        return redirect(auth_url('profile'));
    }

    protected function handleSessionExpired()
    {
        Session::flash('error', __('Session expired. Please try again.'));
        return redirect(auth_url('login'));
    }

    protected function handleAccountNotFound()
    {
        Session::flash('error', __('Account not found.'));
        return redirect(auth_url('login'));
    }

    protected function handleAccountAlreadyActive()
    {
        Session::flash('success', __('Account is already active.'));
        return redirect(auth_url('login'));
    }

    protected function handleAccountDisabled()
    {
        Session::flash('error', __('Account is disabled.'));
        return redirect(auth_url('login'));
    }

    protected function handleInvalidAccountStatus()
    {
        Session::flash('error', __('Invalid account status.'));
        return redirect(auth_url('login'));
    }

    protected function handleActivationExpired($activationType, $userOptional)
    {
        Session::flash('error', __('Activation code has expired. Please request a new one.'));
        return redirect(auth_url('login'));
    }

    protected function handleCsrfFailed()
    {
        Session::flash('error', __('CSRF verification failed.'));
        return redirect(auth_url('login'));
    }
    protected function handleConfirmCsrfFailed()
    {
        Session::flash('error', __('CSRF verification failed.'));
        return redirect(auth_url('confirm'));
    }

    protected function handleMaxAttemptsReached($activationType, $userOptional)
    {
        return redirect(auth_url('confirm'));
    }

    protected function handleCodeVerified($user_id, $activationString)
    {
        return redirect(auth_url('confirmlink/' . $user_id . '/' . $activationString));
    }

    protected function handleInvalidCode($activationType = 'registration', $remainingAttempts = 0, $user = null)
    {
        $this->data('errors', ['confirmation_code' => [__('Invalid code. %1% attempts remaining.', $remainingAttempts)]]);
        $user['remainingAttempts'] = $remainingAttempts;
        return $this->displayConfirmForm($activationType, $user);
    }

    protected function displayConfirmForm($activationType, $user)
    {
        $title = $activationType === 'forgot_password' ? __('Password Reset') : __('Account Activation');
        $this->prepareAuthPageContext(auth_url('confirm'), $title);
        Head::setTitle([$title]);
        
        $this->data('activationType', $activationType);
        $this->data('user', $user);
        $this->data('email', $user['email']);
        if (isset($user['cooldown_until'])) {
            $this->data('cooldown_until', $user['cooldown_until']);
        }
        if (isset($user['remainingAttempts'])) {
            Session::flash('error', __('Invalid code. %1% attempts remaining.', $user['remainingAttempts']));
        }
        $this->data('title', $title);
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('confirm', $this->data)->render();
    }

    protected function handleInvalidActivationLink()
    {
        Session::flash('error', __('Invalid activation link.'));
        return redirect(auth_url('login'));
    }

    protected function handleActivationLinkExpired()
    {
        Session::flash('error', __('Activation link has expired.'));
        return redirect(auth_url('login'));
    }

    protected function handleForgotPasswordConfirmation($user_id)
    {
        return redirect(auth_url('reset-password'));
    }

    protected function handleSuccessfulActivation($user)
    {
        Session::flash('success', __('Account activated successfully.'));
        return redirect(auth_url('profile'));
    }

    protected function handleCooldownPeriod($remainingMinutes)
    {
        Session::flash('error', __('Please wait %1% minutes before requesting a new code.', $remainingMinutes));
        return redirect(auth_url('confirm'));
    }

    protected function handleCodeResent()
    {
        Session::flash('success', __('New code sent successfully.'));
        return redirect(auth_url('confirm'));
    }

    protected function handleInvalidResetRequest()
    {
        Session::flash('error', __('Invalid reset request.'));
        return redirect(auth_url('login'));
    }

    protected function handlePasswordResetValidationErrors($errors)
    {
        $this->data('errors', $errors);
        return $this->displayPasswordResetForm();
    }

    protected function handlePasswordResetSuccess()
    {
        Session::flash('success', __('Password reset successfully.'));
        return redirect(auth_url('login'));
    }

    protected function displayPasswordResetForm()
    {
        $this->prepareAuthPageContext(auth_url('reset-password'), __('Reset Password'));
        Head::setTitle([__('Reset Password')]);
        
        $this->data('title', __('Reset Password'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('reset-password', $this->data)->render();
    }

    protected function handleUserNotFound()
    {
        Session::flash('error', __('User not found.'));
        return redirect(auth_url('login'));
    }

    protected function handlePasswordChangeSuccess()
    {
        Session::flash('success', __('Password changed successfully.'));
        return redirect(auth_url('profile'));
    }

    protected function handlePasswordChangeErrors($errors, $user)
    {
        $this->prepareAuthPageContext(auth_url('profile'), __('Profile Settings'));
        Head::setTitle([__('Profile Settings')]);
        
        $this->data('errors', $errors);
        $this->data('me_info', $this->_prepareProfileData($user));
        $this->data('title', __('Profile Settings'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('profile', $this->data)->render();
    }

    protected function displayPasswordChangeForm($user)
    {
        $this->prepareAuthPageContext(auth_url('profile'), __('Change Password'));
        Head::setTitle([__('Change Password')]);
        
        $this->data('me_info', $this->_prepareProfileData($user));
        $this->data('title', __('Change Password'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('profile', $this->data)->render();
    }

    protected function displayProfilePage($me_info)
    {
        $this->prepareAuthPageContext(auth_url('profile'), __('Profile Settings'));
        Head::setTitle([__('Profile Settings')]);
        
        $this->data('me_info', $me_info);
        $this->data('title', __('Profile Settings'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('profile', $this->data)->render();
    }

    protected function handleProfileUpdateSuccess($page_type)
    {
        $messages = [
            'personal_info' => __('Personal information updated successfully'),
            'social_media' => __('Social media updated successfully'),
            'detailed_info' => __('Detailed information updated successfully')
        ];

        Session::flash('success', $messages[$page_type] ?? __('Profile updated successfully'));
        Session::flash('activetab', $page_type);
        return redirect(auth_url('profile'));
    }

    protected function handleProfileUpdateErrors($errors, $user_id, $page_type)
    {
        $this->prepareAuthPageContext(auth_url('profile'), __('Profile Settings'));
        Head::setTitle([__('Profile Settings')]);
        
        $user = $this->usersModel->getUserById($user_id);
        $this->data('errors', $errors);
        $this->data('me_info', $this->_prepareProfileData($user));
        $this->data('title', __('Profile Settings'));
        $this->data('csrf_token', Session::csrf_token(600));
        Session::flash('activetab', $page_type);
        echo View::make('profile', $this->data)->render();
    }

    protected function handleGoogleAuthRedirect($auth_url)
    {
        if (!empty($auth_url)) {
            return redirect($auth_url);
        }
        Session::flash('error', __('Google authentication failed. Please try again.'));
        return redirect(auth_url('login'));
    }

    protected function handleGoogleLoginSuccess($user, $state = '')
    {
        Session::flash('success', __('Login with Google successful'));
        // Redirect to state URL if provided, otherwise default to profile
        $redirect_url = !empty($state) ? urldecode($state) : auth_url('profile');
        return redirect($redirect_url);
    }

    protected function handleGoogleUserNotFound($fullname, $email_user, $state = '')
    {
        Session::flash('info', __('Please complete your registration.'));
        // Redirect to state URL if provided, otherwise default to register
        $redirect_url = !empty($state) ? urldecode($state) : auth_url('register');
        return redirect($redirect_url);
    }

    protected function handleGoogleAuthError()
    {
        Session::flash('error', __('Google authentication failed. Please try again.'));
        return redirect(auth_url('login'));
    }

    // Called by BaseAuthController::logout()
    protected function handleLogoutSuccess()
    {
        return redirect(base_url());
    }

    // Abstract method implementations for register
    protected function handleRegistrationErrors($errors)
    {
        $this->data('errors', $errors);
        return $this->displayRegistrationForm();
    }

    protected function handleMissingRegistrationFields()
    {
        $this->prepareAuthPageContext(auth_url('register'), __('Register'));
        Head::setTitle([__('Register')]);
        
        $this->data('title', __('Register'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('register', $this->data)->render();
    }

    protected function displayRegistrationForm()
    {
        $this->prepareAuthPageContext(auth_url('register'), __('Register'));
        Head::setTitle([__('Register')]);
        
        $this->data('title', __('Register'));
        $this->data('csrf_token', $this->generateCsrfToken(600));
        echo View::make('register', $this->data)->render();
    }

    // Abstract method implementations for forgot password
    protected function handleForgotPasswordErrors($errors)
    {
        $this->data('errors', $errors);
        return $this->displayForgotPasswordForm();
    }

    protected function handleMissingEmailField()
    {
        $this->prepareAuthPageContext(auth_url('forgot'), __('Forgot Password'));
        Head::setTitle([__('Forgot Password')]);
        
        $this->data('csrf_token', $this->generateCsrfToken(600));
        $this->data('title', __('Forgot Password'));
        echo View::make('forgot', $this->data)->render();
    }

    protected function displayForgotPasswordForm()
    {
        $this->prepareAuthPageContext(auth_url('forgot'), __('Forgot Password'));
        Head::setTitle([__('Forgot Password')]);
        
        $this->data('csrf_token', $this->generateCsrfToken(600));
        $this->data('title', __('Forgot Password'));
        echo View::make('forgot', $this->data)->render();
    }
}
