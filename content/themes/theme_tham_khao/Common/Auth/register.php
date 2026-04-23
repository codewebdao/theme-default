<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

// Set page title
Head::setTitle([Fastlang::__('Register Account')]);

// Include header
echo View::include('header', ['layout' => 'default', 'title' => Fastlang::__('Register Account')]);
?>
<!-- intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.5/build/css/intlTelInput.min.css">


<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="relative h-screen flex-col items-center justify-center w-full grid grid-cols-1 lg:max-w-none lg:grid-cols-2 lg:px-0">

        <!-- Left Panel - Branding -->
        <?php echo View::include('auth-left'); ?>

        <!-- Right Panel - Login Form -->
        <div class="flex items-center h-full justify-center p-8 bg-white/80 backdrop-blur-sm">
            <div class="w-full max-w-sm space-y-8">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900"><?php _e('Create Admin Account') ?></h2>
                    <p class="text-slate-600">
                        <?php _e('or') ?>
                        <a class="font-medium text-blue-600 hover:text-blue-500 transition-colors" href="<?= auth_url('login') ?>">
                            <?php _e('Login') ?>
                        </a>
                    </p>
                </div>

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
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-1 bg-white text-slate-500 capitalize"><?php _e('or') ?> <?php _e('register') ?></span>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="x-circle" class="h-5 w-5 text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    <?= isset($errors['csrf_failed']) ? $errors['csrf_failed'][0] : __('Please Correct Errors'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form class="space-y-4" action="<?php echo auth_url('register'); ?>" method="post" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::csrf_token(600); ?>">
                    <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">


                    <!-- Username -->
                    <div class="space-y-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= HAS_POST('username') ? S_POST('username') : ''; ?>"
                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm <?php echo (isset($errors['username']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                placeholder="<?php _e('Username Placeholder') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['username'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['username'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= HAS_POST('email') ? S_POST('email') : ''; ?>"
                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm <?php echo (isset($errors['email']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                placeholder="<?php _e('Email Address Placeholder') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['email'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                value="<?= HAS_POST('password') ? S_POST('password') : ''; ?>"
                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm <?php echo (isset($errors['password']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                placeholder="<?php _e('Password Placeholder') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['password'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1 hidden">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <input
                                type="password"
                                id="password_repeat"
                                name="password_repeat"
                                value="<?= HAS_POST('password_repeat') ? S_POST('password_repeat') : ''; ?>"
                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm <?php echo (isset($errors['password_repeat']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                placeholder="<?php _e('Confirm Password Placeholder') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['password_repeat'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['password_repeat'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Full Name -->
                    <div class="space-y-1 hidden">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <input
                                type="text"
                                id="fullname"
                                name="fullname"
                                value="<?= HAS_POST('fullname') ? S_POST('fullname') : ''; ?>"
                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm <?php echo (isset($errors['fullname']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                placeholder="<?php _e('Full Name Placeholder') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['fullname'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['fullname'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-1 hidden">
                        <div class="relative">
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?= HAS_POST('phone') ? S_POST('phone') : ''; ?>"
                                class="w-full pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm <?php echo (isset($errors['phone']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                placeholder="<?php _e('Phone Number Placeholder') ?>"
                                required>
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['phone'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="space-y-1">
                        <div class="flex items-start space-x-3">
                            <div class="relative flex items-center h-5 mt-0.5">
                                <input
                                    type="checkbox"
                                    id="terms"
                                    name="terms"
                                    class="peer sr-only"
                                    checked="checked"
                                    required />
                                <label for="terms" class="relative flex items-center justify-center w-5 h-5 border-2 border-gray-300 rounded-lg bg-white cursor-pointer transition-all duration-300 hover:border-blue-400 peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-focus:ring-4 peer-focus:ring-blue-500/20 <?php echo (isset($errors['terms']) ? 'border-red-500 peer-checked:border-red-500' : ''); ?>">
                                    <i data-lucide="check" class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-all duration-300 transform scale-0 peer-checked:scale-100"></i>
                                </label>
                            </div>
                            <label for="terms" class="text-sm text-gray-700 leading-5 cursor-pointer font-medium">
                                <?php _e('I agree to the') ?> <a href="<?= link_page('terms-of-service') ?>" target="_blank" class="text-blue-600 hover:text-blue-500"><?php _e('terms of service') ?></a> <?php _e('and') ?> <a href="<?= link_page('privacy-policy') ?>" target="_blank" class="text-blue-600 hover:text-blue-500"><?php _e('privacy policy') ?></a>
                            </label>
                        </div>
                        <?php if (isset($errors['terms'])): ?>
                            <div class="text-red-500 text-xs mt-1">
                                <?php foreach ($errors['terms'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Register -->
                    <button
                        type="submit"
                        id="register"
                        class="w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-400 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <?php _e('Register') ?>
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

<!-- intl-tel-input JS -->
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.5/build/js/intlTelInput.min.js"></script>
<!-- libphonenumber-js for phone validation (Google libphonenumber) -->
<script type="module">
    // Import and expose parsePhoneNumberFromString to window
    import { parsePhoneNumberFromString } from 'https://cdn.jsdelivr.net/npm/libphonenumber-js@1.11.0/+esm';
    window.parsePhoneNumberFromString = parsePhoneNumberFromString;
    window.libphonenumberLoaded = true;
    // Dispatch event to notify that libphonenumber is loaded
    window.dispatchEvent(new Event('libphonenumber:loaded'));
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Handle checkbox checkmark visibility
        const termsCheckbox = document.getElementById('terms');
        const checkIcon = document.querySelector('#terms + label i[data-lucide="check"]');
        
        if (termsCheckbox && checkIcon) {
            // Update icon on page load
            if (termsCheckbox.checked && typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Update icon when checkbox changes
            termsCheckbox.addEventListener('change', function() {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        }

        // Fetch country code from IP with localStorage cache (by day)
        let detectedCountryCode = 'us'; // Default fallback

        // Cache key for localStorage
        const CACHE_KEY = 'intl_tel_country_code';
        const CACHE_DATE_KEY = 'intl_tel_country_code_date';

        // Check if we have cached country code
        function getCachedCountryCode() {
            try {
                const cachedCode = localStorage.getItem(CACHE_KEY);
                const cachedDate = localStorage.getItem(CACHE_DATE_KEY);

                if (cachedCode && cachedDate) {
                    // Check if cache is still valid (same day)
                    const today = new Date().toDateString();
                    if (cachedDate === today) {
                        return cachedCode;
                    }
                }
            } catch (e) {
                // localStorage might not be available
                console.warn('localStorage not available:', e);
            }
            return null;
        }

        // Save country code to cache
        function saveCountryCodeToCache(countryCode) {
            try {
                const today = new Date().toDateString();
                localStorage.setItem(CACHE_KEY, countryCode);
                localStorage.setItem(CACHE_DATE_KEY, today);
            } catch (e) {
                // localStorage might not be available
                console.warn('Failed to save to localStorage:', e);
            }
        }

        // Try to get from cache first
        const cachedCode = getCachedCountryCode();
        if (cachedCode) {
            detectedCountryCode = cachedCode;
        } else {
            // Fetch from API if no cache or cache expired
            fetch("https://ipapi.co/json")
                .then(res => res.json())
                .then(data => {
                    if (data.country_code) {
                        detectedCountryCode = data.country_code.toLowerCase();
                        // Save to cache
                        saveCountryCodeToCache(detectedCountryCode);
                    }
                })
                .catch(() => {
                    detectedCountryCode = 'us';
                    // Save default to cache to avoid repeated failed requests
                    saveCountryCodeToCache('us');
                });
        }

        // Get form elements
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordRepeatInput = document.getElementById('password_repeat');
        const fullnameInput = document.getElementById('fullname');
        const phoneInput = document.getElementById('phone');

        // Get parent divs (the ones with class "space-y-1")
        const confirmPasswordDiv = passwordRepeatInput ? passwordRepeatInput.closest('.space-y-1') : null;
        const fullnameDiv = fullnameInput ? fullnameInput.closest('.space-y-1') : null;
        const phoneDiv = phoneInput ? phoneInput.closest('.space-y-1') : null;

        // Initialize intl-tel-input function
        function initializePhoneInput() {
            if (!phoneInput || typeof intlTelInput === 'undefined') {
                return;
            }

            // Check if already initialized
            if (phoneInput.dataset.intlTelInputInitialized === 'true') {
                return;
            }

            // Get current language code
            const currentLang = '<?php echo strtolower(lang_code()); ?>';
            const i18nLang = currentLang.length === 2 ? currentLang : 'en';

            // Initialize intl-tel-input with simple options
            const itiInstance = intlTelInput(phoneInput, {
                separateDialCode: true,
                initialCountry: detectedCountryCode,
                i18n: i18nLang,
                nationalMode: true,
                formatOnDisplay: true,
                autoPlaceholder: "aggressive",
            });

            // Mark as initialized
            phoneInput.dataset.intlTelInputInitialized = 'true';

            // Store instance globally for easy access
            if (!window.itiInstances) {
                window.itiInstances = {};
            }
            window.itiInstances[phoneInput.id] = itiInstance;

            // Remove leading zero when user types
            phoneInput.addEventListener('input', function(e) {
                let value = this.value.trim();
                // Remove leading zero if present
                if (value.length > 0 && value[0] === '0') {
                    value = value.substring(1);
                    this.value = value;
                }
            });
        }

        // Function to check if all three fields are filled
        function checkFieldsAndShow() {
            const username = usernameInput ? usernameInput.value.trim() : '';
            const email = emailInput ? emailInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value.trim() : '';

            if (username && email && password) {
                // Remove hidden class from all three fields
                if (confirmPasswordDiv) confirmPasswordDiv.classList.remove('hidden');
                if (fullnameDiv) fullnameDiv.classList.remove('hidden');
                if (phoneDiv) phoneDiv.classList.remove('hidden');

                // Initialize intl-tel-input when phone field becomes visible
                // Use setTimeout to ensure DOM is updated
                setTimeout(() => {
                    if (phoneDiv && !phoneDiv.classList.contains('hidden') && phoneInput) {
                        // Wait for intl-tel-input script to load if not already loaded
                        if (typeof intlTelInput !== 'undefined') {
                            initializePhoneInput();
                        } else {
                            const checkScript = setInterval(() => {
                                if (typeof intlTelInput !== 'undefined') {
                                    clearInterval(checkScript);
                                    initializePhoneInput();
                                }
                            }, 100);
                            setTimeout(() => clearInterval(checkScript), 5000);
                        }
                    }
                }, 50);
            } else {
                // Add hidden class if any field is empty
                if (confirmPasswordDiv) confirmPasswordDiv.classList.add('hidden');
                if (fullnameDiv) fullnameDiv.classList.add('hidden');
                if (phoneDiv) phoneDiv.classList.add('hidden');
            }
        }

        // Add event listeners to username, email, and password fields
        if (usernameInput) usernameInput.addEventListener('input', checkFieldsAndShow);
        if (emailInput) emailInput.addEventListener('input', checkFieldsAndShow);
        if (passwordInput) passwordInput.addEventListener('input', checkFieldsAndShow);

        // Check on page load in case fields are pre-filled
        checkFieldsAndShow();

        // ========== FORM VALIDATION ==========
        const registerForm = document.getElementById('registerForm');

        // Helper to get itiInstance (simple version)
        function getItiInstance() {
            if (!phoneInput) return null;
            // Try to get from window.itiInstances first
            if (window.itiInstances && window.itiInstances[phoneInput.id]) {
                return window.itiInstances[phoneInput.id];
            }
            // Fallback: try intlTelInputGlobals
            if (window.intlTelInputGlobals && typeof window.intlTelInputGlobals.getInstance === 'function') {
                try {
                    return window.intlTelInputGlobals.getInstance(phoneInput);
                } catch (e) {
                    // Silent fail
                }
            }
            return null;
        }

        // Helper function to show error message
        function showFieldError(input, message) {
            // Remove existing error message
            const existingError = input.parentElement.parentElement.querySelector('.validation-error');
            if (existingError) {
                existingError.remove();
            }

            // Add error class to input
            input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
            input.classList.remove('border-gray-200');

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
            input.classList.add('border-gray-200');
        }

        // Validation functions
        function validateUsername(username) {
            if (!username || username.trim().length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Username is required") ?>'
                };
            }
            if (username.length < 3) {
                return {
                    valid: false,
                    message: '<?php _e("Username must be at least 3 characters") ?>'
                };
            }
            if (username.length > 30) {
                return {
                    valid: false,
                    message: '<?php _e("Username must not exceed 30 characters") ?>'
                };
            }
            // Only allow a-z, 0-9, . and _
            const usernameRegex = /^[a-z0-9._]+$/i;
            if (!usernameRegex.test(username)) {
                return {
                    valid: false,
                    message: '<?php _e("Username can only contain a-z0-9_") ?>'
                };
            }
            return {
                valid: true
            };
        }

        function validateEmail(email) {
            if (!email || email.trim().length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Email is required") ?>'
                };
            }
            if (email.length < 5) {
                return {
                    valid: false,
                    message: '<?php _e("Email is too short") ?>'
                };
            }
            if (email.length > 100) {
                return {
                    valid: false,
                    message: '<?php _e("Email is too long") ?>'
                };
            }
            // Check format: username@domain
            const emailRegex = /^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(email)) {
                return {
                    valid: false,
                    message: '<?php _e("Please enter a valid email address") ?>'
                };
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

        function validatePasswordMatch(password, confirmPassword) {
            if (!confirmPassword || confirmPassword.length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Please confirm your password") ?>'
                };
            }
            if (password !== confirmPassword) {
                return {
                    valid: false,
                    message: '<?php _e("Passwords do not match") ?>'
                };
            }
            return {
                valid: true
            };
        }

        function validateFullName(fullname) {
            if (!fullname || fullname.trim().length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Full name is required") ?>'
                };
            }
            if (fullname.trim().length < 2) {
                return {
                    valid: false,
                    message: '<?php _e("Full name must be at least 2 characters") ?>'
                };
            }
            if (fullname.length > 100) {
                return {
                    valid: false,
                    message: '<?php _e("Full name must not exceed 100 characters") ?>'
                };
            }
            return {
                valid: true
            };
        }

        function validatePhone(phone) {
            // Check if phone is empty
            if (!phone || phone.trim().length === 0) {
                return {
                    valid: false,
                    message: '<?php _e("Phone number is required") ?>'
                };
            }

            // Use libphonenumber-js for validation (Google libphonenumber)
            if (typeof window.parsePhoneNumberFromString !== 'undefined') {
                try {
                    // Get country code from intl-tel-input instance
                    const iti = getItiInstance();
                    let countryCode = 'US'; // default
                    
                    if (iti && typeof iti.getSelectedCountryData === 'function') {
                        try {
                            const countryData = iti.getSelectedCountryData();
                            if (countryData && countryData.iso2) {
                                countryCode = countryData.iso2.toUpperCase();
                            }
                        } catch (e) {
                            // Fallback to default
                        }
                    }

                    // Build full phone number with country code
                    let fullPhoneNumber = phone.trim();
                    if (!fullPhoneNumber.startsWith('+')) {
                        // Get dial code from iti if available
                        if (iti && typeof iti.getSelectedCountryData === 'function') {
                            try {
                                const countryData = iti.getSelectedCountryData();
                                if (countryData && countryData.dialCode) {
                                    fullPhoneNumber = '+' + countryData.dialCode + fullPhoneNumber;
                                }
                            } catch (e) {
                                // Fallback: try to parse with country code
                            }
                        }
                    }

                    // Parse phone number using libphonenumber-js
                    const phoneNumber = window.parsePhoneNumberFromString(fullPhoneNumber, countryCode);
                    
                    if (phoneNumber && phoneNumber.isValid()) {
                        return { valid: true };
                    } else {
                        return {
                            valid: false,
                            message: '<?php _e("Please enter a valid phone number") ?>'
                        };
                    }
                } catch (e) {
                    // Fall through to basic validation on error
                }
            }

            // Basic validation fallback (when libphonenumber-js not loaded or validation fails)
            // With separateDialCode, the input only contains the number without country code
            const digitsOnly = phone.replace(/\D/g, '');
            
            if (digitsOnly.length < 7) {
                return {
                    valid: false,
                    message: '<?php _e("Phone number is too short") ?>'
                };
            }
            if (digitsOnly.length > 15) {
                return {
                    valid: false,
                    message: '<?php _e("Phone number is too long") ?>'
                };
            }
            
            return { valid: true };
        }

        // Validate all fields
        function validateForm() {
            let isValid = true;
            const errors = [];

            // Validate username
            if (usernameInput) {
                const usernameResult = validateUsername(usernameInput.value.trim());
                if (!usernameResult.valid) {
                    showFieldError(usernameInput, usernameResult.message);
                    isValid = false;
                } else {
                    clearFieldError(usernameInput);
                }
            }

            // Validate email
            if (emailInput) {
                const emailResult = validateEmail(emailInput.value.trim());
                if (!emailResult.valid) {
                    showFieldError(emailInput, emailResult.message);
                    isValid = false;
                } else {
                    clearFieldError(emailInput);
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

            // Validate confirm password (only if visible)
            if (passwordRepeatInput && !passwordRepeatInput.closest('.space-y-1').classList.contains('hidden')) {
                const passwordMatchResult = validatePasswordMatch(
                    passwordInput ? passwordInput.value : '',
                    passwordRepeatInput.value
                );
                if (!passwordMatchResult.valid) {
                    showFieldError(passwordRepeatInput, passwordMatchResult.message);
                    isValid = false;
                } else {
                    clearFieldError(passwordRepeatInput);
                }
            }

            // Validate full name (only if visible)
            if (fullnameInput && !fullnameInput.closest('.space-y-1').classList.contains('hidden')) {
                const fullnameResult = validateFullName(fullnameInput.value.trim());
                if (!fullnameResult.valid) {
                    showFieldError(fullnameInput, fullnameResult.message);
                    isValid = false;
                } else {
                    clearFieldError(fullnameInput);
                }
            }

            // Validate phone (only if visible)
            if (phoneInput && !phoneInput.closest('.space-y-1').classList.contains('hidden')) {
                const phoneResult = validatePhone(phoneInput.value);
                if (!phoneResult.valid) {
                    showFieldError(phoneInput, phoneResult.message);
                    isValid = false;
                } else {
                    clearFieldError(phoneInput);
                }
            }

            return isValid;
        }

        // Add form submit validation
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    // Scroll to first error
                    const firstError = registerForm.querySelector('.border-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                    return false;
                }

                // Update phone number to include country code before submit
                if (phoneInput && !phoneInput.closest('.space-y-1').classList.contains('hidden')) {
                    const iti = getItiInstance();
                    if (iti) {
                        try {
                            // Remove leading zero from phone number if present
                            let phoneValue = phoneInput.value.trim();
                            if (phoneValue.length > 0 && phoneValue[0] === '0') {
                                phoneValue = phoneValue.substring(1);
                                phoneInput.value = phoneValue;
                                // Update iti instance with new value
                                if (typeof iti.setNumber === 'function') {
                                    iti.setNumber('+' + phoneValue);
                                }
                            }

                            // Get full phone number with country code (E.164 format: +84901234567)
                            // Try getNumber() first, if not available, build manually
                            let fullNumber = null;
                            if (typeof iti.getNumber === 'function') {
                                fullNumber = iti.getNumber();
                            }
                            
                            // Fallback: build manually if getNumber() not available
                            if (!fullNumber) {
                                const countryData = iti.getSelectedCountryData();
                                if (countryData && countryData.dialCode && phoneValue) {
                                    fullNumber = '+' + countryData.dialCode + phoneValue;
                                }
                            }

                            if (fullNumber) {
                                phoneInput.value = fullNumber;
                            }
                        } catch (e) {
                            console.warn('Failed to get full phone number:', e);
                        }
                    }
                }

                // If validation passes, submit the form
                registerForm.submit();
            });
        }

        // Real-time validation on blur (optional, for better UX)
        if (usernameInput) {
            usernameInput.addEventListener('blur', function() {
                const result = validateUsername(this.value.trim());
                if (!result.valid) {
                    showFieldError(this, result.message);
                } else {
                    clearFieldError(this);
                }
            });
        }

        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const result = validateEmail(this.value.trim());
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

        if (passwordRepeatInput) {
            passwordRepeatInput.addEventListener('blur', function() {
                if (!this.closest('.space-y-1').classList.contains('hidden')) {
                    const result = validatePasswordMatch(
                        passwordInput ? passwordInput.value : '',
                        this.value
                    );
                    if (!result.valid) {
                        showFieldError(this, result.message);
                    } else {
                        clearFieldError(this);
                    }
                }
            });
        }

        if (fullnameInput) {
            fullnameInput.addEventListener('blur', function() {
                if (!this.closest('.space-y-1').classList.contains('hidden')) {
                    const result = validateFullName(this.value.trim());
                    if (!result.valid) {
                        showFieldError(this, result.message);
                    } else {
                        clearFieldError(this);
                    }
                }
            });
        }

        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                if (!this.closest('.space-y-1').classList.contains('hidden')) {
                    const result = validatePhone(this.value);
                    if (!result.valid) {
                        showFieldError(this, result.message);
                    } else {
                        clearFieldError(this);
                    }
                }
            });
        }
    });
</script>
<style type="text/css">
    .iti {
        width: 100% !important;
    }
    
    /* Checkbox checkmark styles */
    #terms:checked + label {
        background-color: rgb(37, 99, 235) !important; /* blue-600 */
        border-color: rgb(37, 99, 235) !important;
    }
    
    /* Show checkmark when checked */
    #terms:checked + label > i[data-lucide="check"],
    #terms:checked + label > svg {
        opacity: 1 !important;
        transform: scale(1) !important;
        visibility: visible !important;
    }
    
    /* Hide checkmark when not checked */
    #terms:not(:checked) + label > i[data-lucide="check"],
    #terms:not(:checked) + label > svg {
        opacity: 0 !important;
        transform: scale(0) !important;
        visibility: hidden !important;
    }
</style>

<?php
view_footer();