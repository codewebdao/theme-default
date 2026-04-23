<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

// Set namespace for Auth area
View::namespace('Common');

// Set page title
Head::setTitle([Fastlang::__('Device Activation')]);

// Include header
echo View::include('Auth/header', ['layout' => 'default', 'title' => Fastlang::__('Device Activation')]);
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Device Activation Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <!-- Icon -->
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php _e('Device Activation') ?></h1>

            <!-- Instructions -->
            <p class="text-gray-600 mb-8"><?php _e('Enter the code displayed on your device') ?></p>

            <!-- Error Messages -->
            <?php if (isset($error) && !empty($error)): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Messages -->
            <?php if (Session::has('success')): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800"><?php echo Session::get('success'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Validation Form -->
            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Code Input -->
                <div>
                    <label for="activation_code" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Enter your one-time code') ?>
                    </label>
                    <input
                        type="text"
                        id="activation_code"
                        name="activation_code"
                        class="w-full px-4 py-4 text-center text-2xl font-bold tracking-widest border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        placeholder="000000"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        autocomplete="one-time-code">
                    <p class="text-xs text-gray-500 mt-2">
                        <?php _e('Please enter the 6-digit code sent to your email.') ?>
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-xl transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <?php _e('Continue') ?>
                </button>
            </form>

            <!-- Resend Code -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <?php _e('Not recive the code?') ?>
                    <a href="<?php echo auth_url('resend-code/' . $user_id . '/' . $activation_string); ?>" class="text-blue-600 hover:text-blue-500 font-medium">
                        <?php _e('Resend Code') ?>
                    </a>
                </p>
            </div>

            <!-- Back to Login -->
            <div class="mt-4 text-center">
                <a href="<?php echo auth_url('login'); ?>" class="text-sm text-gray-500 hover:text-gray-700">
                    <?php _e('Back to Login') ?>
                </a>
            </div>
        </div>

        <!-- Language Switcher -->
        <div class="mt-6 flex justify-center">
            <?php echo View::include('Auth/language-switcher'); ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('activation_code');

        // Auto-format input to numbers only and limit to 6 digits
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);

            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                this.form.submit();
            }
        });

        // Focus on input when page loads
        codeInput.focus();

        // Handle paste events
        codeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 6);
            this.value = pastedData;

            if (pastedData.length === 6) {
                this.form.submit();
            }
        });
    });
</script>

<?php
view_footer();
?>