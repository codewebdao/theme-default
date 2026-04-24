<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

Head::setTitle([Fastlang::__('Reset Your Password')]);
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

            <h1 class="auth-login-title"><?php _e('Reset Your Password') ?></h1>
            <p class="auth-login-sub"><?php _e('Enter your new password below') ?></p>

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

                <form method="POST" action="" id="resetPasswordForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="reset_token" value="<?php echo $reset_token; ?>">

                    <div class="auth-stack" style="gap:1rem">
                        <div class="auth-field-group">
                            <label class="auth-field-label" for="password"><?php _e('New Password') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="lock" class="auth-input-icon"></i>
                                <input type="password" id="password" name="password" class="auth-input<?php echo isset($errors['password']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Enter new password')); ?>"
                                    required minlength="6" autocomplete="new-password">
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['password'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-field-group">
                            <label class="auth-field-label" for="password_confirm"><?php _e('Confirm New Password') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="lock" class="auth-input-icon"></i>
                                <input type="password" id="password_confirm" name="password_confirm" class="auth-input<?php echo isset($errors['password_confirm']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Confirm new password')); ?>"
                                    required minlength="6" autocomplete="new-password">
                            </div>
                            <?php if (isset($errors['password_confirm'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['password_confirm'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="auth-pw-hint">
                            <strong><?php _e('Password Requirements:') ?></strong>
                            <ul>
                                <li class="password-requirement">
                                    <i data-lucide="check" class="w-3 h-3 auth-pw-req-icon"></i>
                                    <span><?php _e('At least 6 characters long') ?></span>
                                </li>
                                <li class="password-requirement">
                                    <i data-lucide="check" class="w-3 h-3 auth-pw-req-icon"></i>
                                    <span><?php _e('Must match confirmation') ?></span>
                                </li>
                            </ul>
                        </div>

                        <button type="submit" class="auth-btn-submit">
                            <i data-lucide="key" class="w-5 h-5"></i>
                            <?php _e('Update Password') ?>
                        </button>
                    </div>
                </form>

                <p class="auth-login-sub" style="margin-bottom:0">
                    <a href="<?php echo auth_url('login'); ?>"><?php _e('Back to Login') ?></a>
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

        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const form = document.getElementById('resetPasswordForm');
        if (!password || !passwordConfirm || !form) {
            return;
        }

        function validatePassword() {
            const passwordValue = password.value;
            const confirmValue = passwordConfirm.value;

            const existingError = passwordConfirm.closest('.auth-field-group').querySelector('.password-error');
            if (existingError) {
                existingError.remove();
            }
            passwordConfirm.classList.remove('auth-input--invalid');

            if (confirmValue && passwordValue !== confirmValue) {
                passwordConfirm.classList.add('auth-input--invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'auth-field-error password-error';
                errorDiv.textContent = <?php echo json_encode((string) Fastlang::__('Passwords do not match'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
                passwordConfirm.closest('.auth-field-group').appendChild(errorDiv);
                return false;
            }
            return true;
        }

        function updatePasswordStrength() {
            const passwordValue = password.value;
            document.querySelectorAll('.password-requirement').forEach(function(req) {
                const text = (req.textContent || '').toLowerCase();
                let isValid = false;
                if (text.indexOf('6 characters') !== -1) {
                    isValid = passwordValue.length >= 6;
                } else if (text.indexOf('match') !== -1) {
                    isValid = passwordConfirm.value === passwordValue && passwordValue.length > 0;
                }
                const icon = req.querySelector('.auth-pw-req-icon');
                if (icon) {
                    icon.classList.toggle('is-ok', isValid);
                }
            });
        }

        password.addEventListener('input', function() {
            updatePasswordStrength();
            validatePassword();
        });
        passwordConfirm.addEventListener('input', validatePassword);

        form.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                passwordConfirm.focus();
            }
        });

        password.focus();
    });
</script>

<?php
view_footer();
