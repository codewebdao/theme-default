<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

// Set page title
Head::setTitle([Fastlang::__('Login Account')]);
view_header();
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="relative h-screen flex-col items-center justify-center w-full grid grid-cols-1 lg:max-w-none lg:grid-cols-2 lg:px-0">

        <!-- Left Panel - Branding -->
        <?php echo View::include('auth-left'); ?>

        <!-- Right Panel - Login Form -->
        <div class="flex items-center h-full justify-center p-4 md:p-8 bg-white/80 backdrop-blur-sm">
            <div class="w-full max-w-sm space-y-8">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900"><?php _e('Welcome Back - Sign In') ?></h2>
                    <p class="text-slate-600">
                        <?php _e('or') ?>
                        <a class="font-medium text-blue-600 hover:text-blue-500 transition-colors" href="<?php echo auth_url('register'); ?>">
                            <?php _e('Register') ?>
                        </a>
                    </p>
                </div>

                <!-- Error Messages -->
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="x-circle" class="h-5 w-5 text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    <?php _e('Please Correct Errors'); ?>
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <?php foreach ($errors as $field => $fieldErrors): ?>
                                            <?php foreach ($fieldErrors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Success Messages -->
                <?php if (Session::has_flash('success')): ?>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    <?php echo Session::flash('success'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (Session::has_flash('error')): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="x-circle" class="h-5 w-5 text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    <?php echo Session::flash('error'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Google Login -->
                <a href="<?php echo auth_url('google'); ?>" class="block">
                    <button class="w-full flex items-center justify-center gap-3 px-2 py-2 border border-slate-200 rounded-md bg-white hover:bg-slate-50 hover:border-slate-300 transition-all duration-300 shadow-sm hover:shadow-md group" type="button">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"></path>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"></path>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"></path>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"></path>
                        </svg>
                        <span class="font-medium text-slate-700 group-hover:text-slate-900 transition-colors">
                            <?php _e('Login With Google') ?>
                        </span>
                    </button>
                </a>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-1 bg-white text-slate-500 capitalize"><?php _e('or') ?> <?php _e('login with') ?></span>
                    </div>
                </div>

                <!-- Login Form -->
                <form class="space-y-4" method="POST" action="<?php echo auth_url('login'); ?>" id="loginForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <!-- Device Fingerprint for Remember Me security -->
                    <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

                    <!-- Email Input -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="username">
                            <?php _e('Email or Username') ?>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="w-full pl-8 pr-2 py-2 text-sm border border-slate-200 rounded-md bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400"
                                placeholder="<?php _e('Email or Username') ?>"
                                value="<?php echo HAS_POST('username') ? htmlspecialchars(S_POST('username')) : ''; ?>"
                                required>
                        </div>
                        <?php if (isset($errors['username'])): ?>
                            <div class="text-red-500 text-sm mt-1">
                                <?php echo implode(', ', $errors['username']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="password">
                            <?php _e('Password') ?>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="w-full pl-8 pr-2 py-2 text-sm border border-slate-200 rounded-md bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400"
                                placeholder="<?php _e('Password') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="text-red-500 text-sm mt-1">
                                <?php echo implode(', ', $errors['password']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                value="on"
                                <?php echo (HAS_POST('remember') && S_POST('remember') == 'on') ? 'checked' : ''; ?>
                                class="ml-0.5 w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 focus:ring-2">
                            <label for="remember" class="text-sm text-slate-700 cursor-pointer">
                                <?php _e('Remember Me') ?>
                            </label>
                        </div>
                        <a class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors" href="<?php echo auth_url('forgot'); ?>">
                            <?php _e('Forgot Password') ?>
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        <?php _e('Sign In') ?>
                    </button>
                </form>

                <!-- Language Switcher -->
                <div class="mt-6 " style="display: flex; justify-content: center;">
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
            input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
            input.classList.remove('border-slate-200');

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
            input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
            input.classList.add('border-slate-200');
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
                    const firstError = loginForm.querySelector('.border-red-500');
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