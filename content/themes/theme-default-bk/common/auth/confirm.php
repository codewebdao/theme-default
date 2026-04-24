<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

Head::setTitle([Fastlang::__('Enter Confirmation Code')]);
view_header();
require __DIR__ . '/_auth-login-card-styles.php';
?>

<div class="auth-portal">
    <?php echo View::include('auth-left'); ?>
    <div class="auth-portal__center">
        <div class="auth-login-card auth-login-card--wide">
            <?php if ($auth_brand_logo !== ''): ?>
            <div class="auth-login-logo">
                <a href="<?php echo e(base_url()); ?>" title="<?php echo e($auth_brand_alt !== '' ? $auth_brand_alt : 'Home'); ?>">
                    <img src="<?php echo e($auth_brand_logo); ?>" alt="<?php echo e($auth_brand_alt !== '' ? $auth_brand_alt : 'Logo'); ?>" decoding="async">
                </a>
            </div>
            <?php endif; ?>

            <h1 class="auth-login-title"><?php _e('Enter Confirmation Code') ?></h1>
            <p class="auth-login-sub"><?php _e('Enter 8 numbers at: %1%', $email) ?></p>
            <p class="auth-login-sub" style="margin-top:-1rem">
                <?php _e('or') ?>
                <a href="<?php echo auth_url('login'); ?>"><?php _e('Back to Login') ?></a>
            </p>

            <div class="auth-stack">
                <?php if (Session::has_flash('error')): ?>
                    <div class="auth-alert auth-alert--error" role="alert">
                        <i data-lucide="alert-triangle" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php echo Session::flash('error'); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (Session::has_flash('success')): ?>
                    <div class="auth-alert auth-alert--ok" role="status">
                        <i data-lucide="shield-check" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php echo Session::flash('success'); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($cooldown_until) && $cooldown_until > time()): ?>
                    <?php $remainingMinutes = ceil(($cooldown_until - time()) / 60); ?>
                    <div class="auth-alert auth-alert--warn" role="status">
                        <i data-lucide="clock" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php _e('Please wait %1% minutes before requesting a new code.', $remainingMinutes) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="auth-stack" style="gap:1rem">
                        <div class="auth-field-group">
                            <label class="auth-field-label" style="text-align:center"><?php _e('Enter your 8-digit confirmation code') ?></label>

                            <div class="relative" style="position:relative">
                                <input type="text" id="confirmation_code" name="confirmation_code"
                                    class="absolute opacity-0 pointer-events-none w-px h-px overflow-hidden"
                                    maxlength="8" pattern="[0-9]{8}" required autocomplete="one-time-code"
                                    aria-hidden="true" tabindex="-1">

                                <div class="auth-code-row" id="code-display">
                                    <?php for ($i = 0; $i < 8; $i++): ?>
                                        <div class="auth-code-digit" data-index="<?php echo $i; ?>" tabindex="0" role="presentation">
                                            <span class="digit-display">-</span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <p class="auth-text-muted"><?php _e('Please enter the 8-digit code sent to your email.') ?></p>
                        </div>

                        <button type="submit" class="auth-btn-submit">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <?php _e('Continue') ?>
                        </button>
                    </div>
                </form>

                <p class="auth-text-muted">
                    <?php _e('Not recive the code?') ?>
                    <?php if (isset($cooldown_until) && $cooldown_until > time()): ?>
                        <span style="color:#64748b;cursor:not-allowed"><?php _e('Resend Code') ?> (<?php _e('Please wait %1% minutes', ceil(($cooldown_until - time()) / 60)) ?>)</span>
                    <?php else: ?>
                        <a class="auth-link-muted" href="<?php echo auth_url('resend_code'); ?>"><?php _e('Resend Code') ?></a>
                    <?php endif; ?>
                </p>

                <div class="auth-lang">
                    <?php echo View::include('language-switcher'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const codeInput = document.getElementById('confirmation_code');
        const digitDisplays = document.querySelectorAll('.digit-display');
        const digitBoxes = document.querySelectorAll('.auth-code-digit');
        let currentIndex = 0;

        function updateDisplay() {
            const value = codeInput ? codeInput.value : '';
            for (let i = 0; i < 8; i++) {
                if (i < value.length) {
                    digitDisplays[i].textContent = value[i];
                    digitBoxes[i].classList.add('is-filled');
                } else {
                    digitDisplays[i].textContent = '-';
                    digitBoxes[i].classList.remove('is-filled');
                }
            }
            digitBoxes.forEach(function(box) {
                box.classList.remove('is-active');
            });
            if (currentIndex < 8 && digitBoxes[currentIndex]) {
                digitBoxes[currentIndex].classList.add('is-active');
            }
        }

        function handleInput(value) {
            const numbersOnly = value.replace(/[^0-9]/g, '');
            const limitedValue = numbersOnly.substring(0, 8);
            if (codeInput) {
                codeInput.value = limitedValue;
            }
            updateDisplay();
            currentIndex = Math.min(limitedValue.length, 7);
            if (limitedValue.length === 8 && codeInput && codeInput.form) {
                setTimeout(function() {
                    codeInput.form.submit();
                }, 300);
            }
        }

        document.addEventListener('keydown', function(e) {
            if (!codeInput) return;
            if (e.key === 'Backspace') {
                e.preventDefault();
                if (currentIndex > 0) {
                    currentIndex--;
                    const newValue = codeInput.value.substring(0, currentIndex);
                    handleInput(newValue);
                }
                return;
            }
            if (e.key >= '0' && e.key <= '9') {
                e.preventDefault();
                handleInput(codeInput.value + e.key);
                return;
            }
            if (e.key.length === 1) {
                e.preventDefault();
            }
        });

        if (codeInput) {
            codeInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 8);
                handleInput(pastedData);
            });
        }

        digitBoxes.forEach(function(box, index) {
            box.addEventListener('click', function() {
                currentIndex = index;
                updateDisplay();
                if (codeInput) codeInput.focus();
            });
            box.addEventListener('focus', function() {
                currentIndex = index;
                updateDisplay();
            });
        });

        if (codeInput) {
            codeInput.addEventListener('focus', updateDisplay);
        }
        updateDisplay();
        if (digitBoxes[0]) {
            digitBoxes[0].focus();
        }
    });
</script>

<?php
view_footer();
