<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

Head::setTitle([Fastlang::__('Register Account')]);
view_header();
$auth_reg_show_progressive = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) === 'POST';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.5/build/css/intlTelInput.min.css">
<?php require __DIR__ . '/_auth-login-card-styles.php'; ?>

<div class="auth-portal">
    <?php echo View::include('auth-left'); ?>
    <div class="auth-portal__center">
        <div class="auth-login-card auth-login-card--register">
            <?php if ($auth_brand_logo !== ''): ?>
            <div class="auth-login-logo">
                <a href="<?php echo e(base_url()); ?>" title="<?php echo e($auth_brand_alt !== '' ? $auth_brand_alt : 'Home'); ?>">
                    <img src="<?php echo e($auth_brand_logo); ?>" alt="<?php echo e($auth_brand_alt !== '' ? $auth_brand_alt : 'Logo'); ?>" decoding="async">
                </a>
            </div>
            <?php endif; ?>

            <h1 class="auth-login-title"><?php _e('Create Admin Account') ?></h1>
            <p class="auth-login-sub">
                <?php _e('or') ?>
                <a href="<?= auth_url('login') ?>"><?php _e('Login If Account Exists') ?></a>
            </p>

            <div class="auth-stack">
                <?php if (!empty($errors)): ?>
                    <?php
                    $auth_reg_err_other = $errors;
                    unset($auth_reg_err_other['csrf_failed']);
                    ?>
                    <div class="auth-alert auth-alert--error" role="alert">
                        <i data-lucide="alert-triangle" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div>
                            <strong><?php _e('Please Correct Errors'); ?></strong>
                            <?php if (isset($errors['csrf_failed'])): ?>
                                <p style="margin:0.35rem 0 0"><?php echo htmlspecialchars((string) ($errors['csrf_failed'][0] ?? '')); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($auth_reg_err_other)): ?>
                                <ul>
                                    <?php foreach ($auth_reg_err_other as $fieldErrors): ?>
                                        <?php foreach ((array) $fieldErrors as $error): ?>
                                            <li><?php echo htmlspecialchars((string) $error); ?></li>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
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

                <div class="auth-divider"><span><?php _e('or') ?> <?php _e('register') ?></span></div>

                <form method="POST" action="<?php echo auth_url('register'); ?>" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::csrf_token(600); ?>">
                    <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

                    <div class="auth-stack" style="gap:1rem">
                        <div class="auth-field-group">
                            <label class="auth-field-label" for="username"><?php _e('Username') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="user" class="auth-input-icon"></i>
                                <input type="text" id="username" name="username"
                                    class="auth-input<?php echo isset($errors['username']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Username Placeholder')); ?>"
                                    value="<?= HAS_POST('username') ? htmlspecialchars((string) S_POST('username'), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    autocomplete="username" required>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['username'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group">
                            <label class="auth-field-label" for="email"><?php _e('Email') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="mail" class="auth-input-icon"></i>
                                <input type="email" id="email" name="email"
                                    class="auth-input<?php echo isset($errors['email']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Email Address Placeholder')); ?>"
                                    value="<?= HAS_POST('email') ? htmlspecialchars((string) S_POST('email'), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    autocomplete="email" required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['email'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group">
                            <label class="auth-field-label" for="password"><?php _e('Password') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="lock" class="auth-input-icon"></i>
                                <input type="password" id="password" name="password"
                                    class="auth-input<?php echo isset($errors['password']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Password Placeholder')); ?>"
                                    value="<?= HAS_POST('password') ? htmlspecialchars((string) S_POST('password'), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    autocomplete="new-password" required>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['password'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group<?php echo $auth_reg_show_progressive ? '' : ' auth-hidden'; ?>" data-auth-reg-progressive>
                            <label class="auth-field-label" for="password_repeat"><?php _e('Confirm Password') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="lock" class="auth-input-icon"></i>
                                <input type="password" id="password_repeat" name="password_repeat"
                                    class="auth-input<?php echo isset($errors['password_repeat']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Confirm Password Placeholder')); ?>"
                                    value="<?= HAS_POST('password_repeat') ? htmlspecialchars((string) S_POST('password_repeat'), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    autocomplete="new-password" required>
                            </div>
                            <?php if (isset($errors['password_repeat'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['password_repeat'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group<?php echo $auth_reg_show_progressive ? '' : ' auth-hidden'; ?>" data-auth-reg-progressive>
                            <label class="auth-field-label" for="fullname"><?php _e('Full Name') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="user" class="auth-input-icon"></i>
                                <input type="text" id="fullname" name="fullname"
                                    class="auth-input<?php echo isset($errors['fullname']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Full Name Placeholder')); ?>"
                                    value="<?= HAS_POST('fullname') ? htmlspecialchars((string) S_POST('fullname'), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    autocomplete="name" required>
                            </div>
                            <?php if (isset($errors['fullname'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['fullname'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group<?php echo $auth_reg_show_progressive ? '' : ' auth-hidden'; ?>" data-auth-reg-progressive>
                            <label class="auth-field-label" for="phone"><?php _e('Phone') ?></label>
                            <div class="auth-input-wrap">
                                <input type="tel" id="phone" name="phone"
                                    class="auth-input auth-input--phone<?php echo isset($errors['phone']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Phone Number Placeholder')); ?>"
                                    value="<?= HAS_POST('phone') ? htmlspecialchars((string) S_POST('phone'), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    autocomplete="tel" required>
                            </div>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['phone'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group">
                            <label class="auth-check auth-check--start" for="terms">
                                <input type="checkbox" id="terms" name="terms" value="on" checked="checked" required>
                                <span>
                                    <?php _e('I agree to the') ?>
                                    <a class="auth-link-muted" href="<?= base_url('terms-of-service') ?>" target="_blank" rel="noopener noreferrer"><?php _e('terms of service') ?></a>
                                    <?php _e('and') ?>
                                    <a class="auth-link-muted" href="<?= base_url('privacy-policy') ?>" target="_blank" rel="noopener noreferrer"><?php _e('privacy policy') ?></a>
                                </span>
                            </label>
                            <?php if (isset($errors['terms'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['terms'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" id="register" class="auth-btn-submit">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                            <?php _e('Register') ?>
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

        const confirmPasswordDiv = passwordRepeatInput ? passwordRepeatInput.closest('[data-auth-reg-progressive]') : null;
        const fullnameDiv = fullnameInput ? fullnameInput.closest('[data-auth-reg-progressive]') : null;
        const phoneDiv = phoneInput ? phoneInput.closest('[data-auth-reg-progressive]') : null;

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
                if (confirmPasswordDiv) confirmPasswordDiv.classList.remove('auth-hidden');
                if (fullnameDiv) fullnameDiv.classList.remove('auth-hidden');
                if (phoneDiv) phoneDiv.classList.remove('auth-hidden');

                // Initialize intl-tel-input when phone field becomes visible
                // Use setTimeout to ensure DOM is updated
                setTimeout(() => {
                    if (phoneDiv && !phoneDiv.classList.contains('auth-hidden') && phoneInput) {
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
                if (confirmPasswordDiv) confirmPasswordDiv.classList.add('auth-hidden');
                if (fullnameDiv) fullnameDiv.classList.add('auth-hidden');
                if (phoneDiv) phoneDiv.classList.add('auth-hidden');
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

        function fieldGroupEl(input) {
            return input ? input.closest('.auth-field-group') : null;
        }

        // Helper function to show error message
        function showFieldError(input, message) {
            const group = fieldGroupEl(input);
            if (!group) return;
            const existingError = group.querySelector('.validation-error');
            if (existingError) {
                existingError.remove();
            }
            input.classList.add('auth-input--invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'auth-field-error validation-error';
            errorDiv.textContent = message;
            group.appendChild(errorDiv);
        }

        // Helper function to clear error
        function clearFieldError(input) {
            const group = fieldGroupEl(input);
            if (!group) return;
            const existingError = group.querySelector('.validation-error');
            if (existingError) {
                existingError.remove();
            }
            input.classList.remove('auth-input--invalid');
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
            if (passwordRepeatInput && passwordRepeatInput.closest('[data-auth-reg-progressive]') && !passwordRepeatInput.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
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
            if (fullnameInput && fullnameInput.closest('[data-auth-reg-progressive]') && !fullnameInput.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
                const fullnameResult = validateFullName(fullnameInput.value.trim());
                if (!fullnameResult.valid) {
                    showFieldError(fullnameInput, fullnameResult.message);
                    isValid = false;
                } else {
                    clearFieldError(fullnameInput);
                }
            }

            // Validate phone (only if visible)
            if (phoneInput && phoneInput.closest('[data-auth-reg-progressive]') && !phoneInput.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
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
                    const firstError = registerForm.querySelector('.auth-input--invalid');
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
                if (phoneInput && phoneInput.closest('[data-auth-reg-progressive]') && !phoneInput.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
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
                if (this.closest('[data-auth-reg-progressive]') && !this.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
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
                if (this.closest('[data-auth-reg-progressive]') && !this.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
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
                if (this.closest('[data-auth-reg-progressive]') && !this.closest('[data-auth-reg-progressive]').classList.contains('auth-hidden')) {
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
    .iti { width: 100% !important; }
</style>

<?php
view_footer();