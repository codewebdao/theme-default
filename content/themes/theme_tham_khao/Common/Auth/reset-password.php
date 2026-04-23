<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

// Set page title
Head::setTitle([Fastlang::__('Reset Your Password')]);

// Include header
echo View::include('header', ['layout' => 'default', 'title' => Fastlang::__('Reset Your Password')]);
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="relative h-screen flex-col items-center justify-center w-full grid grid-cols-1 lg:max-w-none lg:grid-cols-2 lg:px-0">

        <!-- Left Panel - Branding -->
        <?php echo View::include('auth-left'); ?>

        <!-- Right Panel - Reset Password Form -->
        <div class="flex items-center h-full justify-center p-8 bg-white/80 backdrop-blur-sm">
            <div class="w-full max-w-sm space-y-8">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900"><?php _e('Reset Your Password') ?></h2>
                    <p class="text-slate-600">
                        <?php _e('Enter your new password below') ?>
                    </p>
                </div>

                <!-- Error Messages -->
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

                <!-- Reset Password Form -->
                <form class="space-y-4" method="POST" action="">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="reset_token" value="<?php echo $reset_token; ?>">

                    <!-- New Password Input -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="password">
                            <?php _e('New Password') ?>
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
                                placeholder="<?php _e('Enter new password') ?>"
                                required
                                minlength="6">
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="text-red-500 text-sm mt-1">
                                <?php echo implode(', ', $errors['password']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="password_confirm">
                            <?php _e('Confirm New Password') ?>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input
                                type="password"
                                id="password_confirm"
                                name="password_confirm"
                                class="w-full pl-8 pr-2 py-2 text-sm border border-slate-200 rounded-md bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400"
                                placeholder="<?php _e('Confirm new password') ?>"
                                required
                                minlength="6">
                        </div>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="text-red-500 text-sm mt-1">
                                <?php echo implode(', ', $errors['password_confirm']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <p class="text-xs text-slate-600 mb-2 font-medium"><?php _e('Password Requirements:') ?></p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li class="flex items-center password-requirement">
                                <i data-lucide="check" class="w-3 h-3 mr-2 text-slate-400 transition-colors duration-200"></i>
                                <?php _e('At least 6 characters long') ?>
                            </li>
                            <li class="flex items-center password-requirement">
                                <i data-lucide="check" class="w-3 h-3 mr-2 text-slate-400 transition-colors duration-200"></i>
                                <?php _e('Must match confirmation') ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                        <i data-lucide="key" class="w-5 h-5"></i>
                        <?php _e('Update Password') ?>
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="<?php echo auth_url('login'); ?>" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">
                        <?php _e('Back to Login') ?>
                    </a>
                </div>

                <!-- Language Switcher -->
                <div class="mt-6" style="display: flex; justify-content: center;">
                    <?php echo View::include('language-switcher'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const form = document.querySelector('form');

        // Real-time password confirmation validation
        function validatePassword() {
            const passwordValue = password.value;
            const confirmValue = passwordConfirm.value;

            // Clear previous error styling
            passwordConfirm.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
            passwordConfirm.classList.add('border-slate-200', 'focus:border-blue-500', 'focus:ring-blue-500/20');

            // Remove existing error message
            const existingError = passwordConfirm.parentNode.querySelector('.password-error');
            if (existingError) {
                existingError.remove();
            }

            if (confirmValue && passwordValue !== confirmValue) {
                // Add error styling
                passwordConfirm.classList.remove('border-slate-200', 'focus:border-blue-500', 'focus:ring-blue-500/20');
                passwordConfirm.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');

                // Add error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'password-error text-red-500 text-sm mt-1';
                errorDiv.textContent = '<?php _e('Passwords do not match') ?>';
                passwordConfirm.parentNode.appendChild(errorDiv);

                return false;
            }

            return true;
        }

        // Password strength indicator
        function updatePasswordStrength() {
            const passwordValue = password.value;
            const requirements = document.querySelectorAll('.password-requirement');

            requirements.forEach(req => {
                const text = req.textContent.toLowerCase();
                let isValid = false;

                if (text.includes('6 characters')) {
                    isValid = passwordValue.length >= 6;
                } else if (text.includes('match')) {
                    isValid = passwordConfirm.value === passwordValue && passwordValue.length > 0;
                }

                const icon = req.querySelector('svg');
                if (isValid) {
                    icon.classList.remove('text-slate-400');
                    icon.classList.add('text-green-500');
                } else {
                    icon.classList.remove('text-green-500');
                    icon.classList.add('text-slate-400');
                }
            });
        }

        // Event listeners
        password.addEventListener('input', function() {
            updatePasswordStrength();
            validatePassword();
        });

        passwordConfirm.addEventListener('input', validatePassword);

        // Form submission validation
        form.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                passwordConfirm.focus();
                return false;
            }
        });

        // Focus on first input
        password.focus();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php
view_footer();