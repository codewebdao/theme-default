<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

// Set page title
Head::setTitle([Fastlang::__('Enter Confirmation Code')]);

// Include header
echo View::include('header', ['layout' => 'default', 'title' => Fastlang::__('Enter Confirmation Code')]);
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="relative h-screen flex-col items-center justify-center w-full grid grid-cols-1 lg:max-w-none lg:grid-cols-2 lg:px-0">

        <!-- Left Panel - Branding -->
        <?php echo View::include('auth-left'); ?>

        <!-- Right Panel - Confirmation Form -->
        <div class="flex items-center h-full justify-center p-8 bg-white/80 backdrop-blur-sm">
            <div class="w-full max-w-sm space-y-8">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900"><?php _e('Enter Confirmation Code') ?></h2>
                    <p class="text-slate-600">
                        <?php _e('Enter 8 numbers at: %1%', $email) ?>
                    </p>
                    <p class="text-slate-600">
                        <?php _e('or') ?>
                        <a class="font-medium text-blue-600 hover:text-blue-500 transition-colors" href="<?php echo auth_url('login'); ?>">
                            <?php _e('Back to Login') ?>
                        </a>
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

                <!-- Cooldown Notice -->
                <?php if (isset($cooldown_until) && $cooldown_until > time()): ?>
                    <?php
                    $remainingMinutes = ceil(($cooldown_until - time()) / 60);
                    ?>
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="clock" class="h-5 w-5 text-orange-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-orange-800">
                                    <?php _e('Please wait %1% minutes before requesting a new code.', $remainingMinutes) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Confirmation Form -->
                <form class="space-y-4" method="POST" action="">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- 8-Digit Code Input -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="confirmation_code">
                            <?php _e('Enter your 8-digit confirmation code') ?>
                        </label>

                        <!-- Custom 8-Digit Input -->
                        <div class="relative">
                            <!-- Hidden input for form submission -->
                            <input
                                type="text"
                                id="confirmation_code"
                                name="confirmation_code"
                                class="absolute opacity-0 pointer-events-none"
                                maxlength="8"
                                pattern="[0-9]{8}"
                                required
                                autocomplete="one-time-code">

                            <!-- Visual 8-digit display -->
                            <div class="flex gap-2 justify-center" id="code-display">
                                <?php for ($i = 0; $i < 8; $i++): ?>
                                    <div class="w-12 h-14 border-2 border-slate-200 rounded-xl flex items-center justify-center text-2xl font-bold text-slate-700 bg-white transition-all duration-200 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 cursor-pointer"
                                        data-index="<?php echo $i; ?>"
                                        tabindex="0">
                                        <span class="digit-display">-</span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 text-center">
                            <?php _e('Please enter the 8-digit code sent to your email.') ?>
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        <?php _e('Continue') ?>
                    </button>
                </form>

                <!-- Resend Code -->
                <div class="text-center">
                    <p class="text-sm text-slate-600">
                        <?php _e('Not recive the code?') ?>
                        <?php if (isset($cooldown_until) && $cooldown_until > time()): ?>
                            <span class="font-medium text-gray-400 cursor-not-allowed">
                                <?php _e('Resend Code') ?> (<?php _e('Please wait %1% minutes', ceil(($cooldown_until - time()) / 60)) ?>)
                            </span>
                        <?php else: ?>
                            <a href="<?php echo auth_url('resend_code'); ?>" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                <?php _e('Resend Code') ?>
                            </a>
                        <?php endif; ?>
                    </p>
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
        const codeInput = document.getElementById('confirmation_code');
        const digitDisplays = document.querySelectorAll('.digit-display');
        const digitBoxes = document.querySelectorAll('[data-index]');
        let currentIndex = 0;

        // Function to update visual display
        function updateDisplay() {
            const value = codeInput.value;
            for (let i = 0; i < 8; i++) {
                if (i < value.length) {
                    digitDisplays[i].textContent = value[i];
                    digitBoxes[i].classList.add('border-blue-500', 'bg-blue-50');
                    digitBoxes[i].classList.remove('border-slate-200');
                } else {
                    digitDisplays[i].textContent = '-';
                    digitBoxes[i].classList.remove('border-blue-500', 'bg-blue-50');
                    digitBoxes[i].classList.add('border-slate-200');
                }
            }

            // Highlight current position
            digitBoxes.forEach((box, index) => {
                box.classList.remove('ring-2', 'ring-blue-500/20');
            });

            if (currentIndex < 8) {
                digitBoxes[currentIndex].classList.add('ring-2', 'ring-blue-500/20');
            }
        }

        // Function to handle input
        function handleInput(value) {
            // Only allow numbers
            const numbersOnly = value.replace(/[^0-9]/g, '');

            // Limit to 8 digits
            const limitedValue = numbersOnly.substring(0, 8);

            // Update hidden input
            codeInput.value = limitedValue;

            // Update visual display
            updateDisplay();

            // Move to next position
            currentIndex = Math.min(limitedValue.length, 7);

            // Auto-submit when 8 digits are entered
            if (limitedValue.length === 8) {
                setTimeout(() => {
                    codeInput.form.submit();
                }, 300);
            }
        }

        // Handle keyboard input
        document.addEventListener('keydown', function(e) {
            // Only handle if we're on this page
            if (!codeInput) return;

            // Handle backspace
            if (e.key === 'Backspace') {
                e.preventDefault();
                if (currentIndex > 0) {
                    currentIndex--;
                    const newValue = codeInput.value.substring(0, currentIndex);
                    handleInput(newValue);
                }
                return;
            }

            // Handle numbers
            if (e.key >= '0' && e.key <= '9') {
                e.preventDefault();
                const newValue = codeInput.value + e.key;
                handleInput(newValue);
                return;
            }

            // Handle other keys (ignore)
            if (e.key.length === 1) {
                e.preventDefault();
            }
        });

        // Handle paste events
        codeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 8);
            handleInput(pastedData);
        });

        // Handle click and focus on digit boxes
        digitBoxes.forEach((box, index) => {
            box.addEventListener('click', function() {
                currentIndex = index;
                updateDisplay();
                codeInput.focus();
            });

            box.addEventListener('focus', function() {
                currentIndex = index;
                updateDisplay();
            });
        });

        // Handle focus on the hidden input
        codeInput.addEventListener('focus', function() {
            updateDisplay();
        });

        // Initialize display
        updateDisplay();

        // Focus on the first digit box
        digitBoxes[0].focus();
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
