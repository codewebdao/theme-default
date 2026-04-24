<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

// Set page title
Head::setTitle([Fastlang::__('Login Account')]);
view_header();
require __DIR__ . '/_auth-login-card-styles.php';
?>

<div class="auth-portal">
    <?php echo View::include('auth-left'); ?>
    <div class="auth-portal__center">
        <div class="auth-login-card">
            <?php if ($auth_brand_logo !== ''): ?>
            <div class="auth-login-logo">
                <a href="<?php echo e(base_url()); ?>" title="<?php echo e($auth_brand_alt !== '' ? $auth_brand_alt : 'Home'); ?>">
                    <img src="<?php echo e($auth_brand_logo); ?>" alt="<?php echo e($auth_brand_alt !== '' ? $auth_brand_alt : 'Logo'); ?>" decoding="async">
                </a>
            </div>
            <?php endif; ?>

            <h1 class="auth-login-title"><?php _e('Welcome Back - Sign In') ?></h1>
            <p class="auth-login-sub">
                <?php _e('or') ?>
                <a href="<?php echo auth_url('register'); ?>"><?php _e('Create New Account') ?></a>
            </p>

            <div class="auth-stack">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="auth-alert auth-alert--error" role="alert">
                        <i data-lucide="alert-triangle" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div>
                            <strong><?php _e('Please Correct Errors'); ?></strong>
                            <ul>
                                <?php foreach ($errors as $field => $fieldErrors): ?>
                                    <?php foreach ($fieldErrors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (Session::has_flash('success')): ?>
                    <div class="auth-alert auth-alert--ok" role="status">
                        <i data-lucide="shield-check" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php echo Session::flash('success'); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (Session::has_flash('error')): ?>
                    <div class="auth-alert auth-alert--error" role="alert">
                        <i data-lucide="alert-triangle" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php echo Session::flash('error'); ?></div>
                    </div>
                <?php endif; ?>

                <a href="<?php echo auth_url('google'); ?>" class="block" style="text-decoration:none">
                    <button type="button" class="auth-btn-google">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"></path>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"></path>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"></path>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"></path>
                        </svg>
                        <?php _e('Login With Google') ?>
                    </button>
                </a>

                <div class="auth-divider"><span><?php _e('or') ?> <?php _e('login with') ?></span></div>

                <form method="POST" action="<?php echo auth_url('login'); ?>" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

                    <div class="auth-stack" style="gap:1rem">
                        <div>
                            <label class="auth-field-label" for="username"><?php _e('Email or Username') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="mail" class="auth-input-icon"></i>
                                <input type="text" id="username" name="username" class="auth-input" autocomplete="username"
                                    placeholder="<?php echo e(Fastlang::__('Email or Username')); ?>"
                                    value="<?php echo HAS_POST('username') ? htmlspecialchars(S_POST('username')) : ''; ?>" required>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', $errors['username']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label class="auth-field-label" for="password"><?php _e('Password') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="lock" class="auth-input-icon"></i>
                                <input type="password" id="password" name="password" class="auth-input" autocomplete="current-password"
                                    placeholder="<?php echo e(Fastlang::__('Password')); ?>" required>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', $errors['password']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-row-between">
                            <label class="auth-check" for="remember">
                                <input type="checkbox" id="remember" name="remember" value="on"
                                    <?php echo (HAS_POST('remember') && S_POST('remember') == 'on') ? 'checked' : ''; ?>>
                                <span><?php _e('Remember Me') ?></span>
                            </label>
                            <a class="auth-link-muted" href="<?php echo auth_url('forgot'); ?>"><?php _e('Forgot Password') ?></a>
                        </div>

                        <button type="submit" class="auth-btn-submit">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            <?php _e('Sign In') ?>
                        </button>
                    </div>
                </form>

                <div class="auth-lang">
                    <?php echo View::include('language-switcher'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // ========== FORM VALIDATION ==========
        const loginForm = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        // Helper function to show error message
        function showFieldError(input, message) {
            // Remove existing error message
            const existingError = input.parentElement.parentElement.querySelector('.validation-error');
            if (existingError) {
                existingError.remove();
            }

            // Add error class to input
            input.classList.add('auth-input--invalid');

            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-red-500 text-xs mt-1 validation-error';
            errorDiv.textContent = message;
            input.parentElement.parentElement.appendChild(errorDiv);
        }

        // Helper function to clear error
        function clearFieldError(input) {
            const existingError = input.parentElement.parentElement.querySelector('.validation-error');
            if (existingError) {
                existingError.remove();
            }
            input.classList.remove('auth-input--invalid');
        }

        // Validation functions
        function validateUsernameOrEmail(value) {
            if (!value || value.trim().length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Email or Username is required") ?>'
                };
            }

            const trimmedValue = value.trim();

            // Check if it's an email (contains @)
            if (trimmedValue.includes('@')) {
                // Validate as email
                if (trimmedValue.length < 5) {
                    return {
                        valid: false,
                        message: '<?php _e("Email is too short") ?>'
                    };
                }
                if (trimmedValue.length > 100) {
                    return {
                        valid: false,
                        message: '<?php _e("Email is too long") ?>'
                    };
                }
                // Check email format: username@domain
                const emailRegex = /^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(trimmedValue)) {
                    return {
                        valid: false,
                        message: '<?php _e("Please enter a valid email address") ?>'
                    };
                }
            } else {
                // Validate as username
                if (trimmedValue.length < 3) {
                    return {
                        valid: false,
                        message: '<?php _e("Username must be at least 3 characters") ?>'
                    };
                }
                if (trimmedValue.length > 30) {
                    return {
                        valid: false,
                        message: '<?php _e("Username must not exceed 30 characters") ?>'
                    };
                }
                // Only allow a-z, 0-9, . and _
                const usernameRegex = /^[a-z0-9._]+$/i;
                if (!usernameRegex.test(trimmedValue)) {
                    return {
                        valid: false,
                        message: '<?php _e("Username can only contain letters, numbers, dots and underscores") ?>'
                    };
                }
            }

            return {
                valid: true
            };
        }

        function validatePassword(password) {
            if (!password || password.length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Password is required") ?>'
                };
            }
            if (password.length < 6) {
                return {
                    valid: false,
                    message: '<?php _e("Password must be at least 6 characters") ?>'
                };
            }
            if (password.length > 128) {
                return {
                    valid: false,
                    message: '<?php _e("Password is too long") ?>'
                };
            }
            return {
                valid: true
            };
        }

        // Validate all fields
        function validateForm() {
            let isValid = true;

            // Validate username/email
            if (usernameInput) {
                const usernameResult = validateUsernameOrEmail(usernameInput.value.trim());
                if (!usernameResult.valid) {
                    showFieldError(usernameInput, usernameResult.message);
                    isValid = false;
                } else {
                    clearFieldError(usernameInput);
                }
            }

            // Validate password
            if (passwordInput) {
                const passwordResult = validatePassword(passwordInput.value);
                if (!passwordResult.valid) {
                    showFieldError(passwordInput, passwordResult.message);
                    isValid = false;
                } else {
                    clearFieldError(passwordInput);
                }
            }

            return isValid;
        }

        // Add form submit validation
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    // Scroll to first error
                    const firstError = loginForm.querySelector('.auth-input--invalid');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                    return false;
                }

                // If validation passes, submit the form
                loginForm.submit();
            });
        }

        // Real-time validation on blur (optional, for better UX)
        if (usernameInput) {
            usernameInput.addEventListener('blur', function() {
                const result = validateUsernameOrEmail(this.value.trim());
                if (!result.valid) {
                    showFieldError(this, result.message);
                } else {
                    clearFieldError(this);
                }
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                const result = validatePassword(this.value);
                if (!result.valid) {
                    showFieldError(this, result.message);
                } else {
                    clearFieldError(this);
                }
            });
        }

        // Device fingerprint is now handled by device-fingerprint.js
    });
</script>

<?php
view_footer();