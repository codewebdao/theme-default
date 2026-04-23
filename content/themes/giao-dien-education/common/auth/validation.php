<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

Head::setTitle([Fastlang::__('Device Activation')]);
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

            <div class="auth-icon-ring" aria-hidden="true">
                <i data-lucide="smartphone" class="w-8 h-8"></i>
            </div>

            <h1 class="auth-login-title"><?php _e('Device Activation') ?></h1>
            <p class="auth-login-sub"><?php _e('Enter the code displayed on your device') ?></p>

            <div class="auth-stack">
                <?php if (isset($error) && $error !== ''): ?>
                    <div class="auth-alert auth-alert--error" role="alert">
                        <i data-lucide="alert-triangle" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php echo htmlspecialchars((string) $error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (Session::has('success')): ?>
                    <div class="auth-alert auth-alert--ok" role="status">
                        <i data-lucide="shield-check" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div><?php echo Session::get('success'); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-stack" style="gap:1rem">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="auth-field-group">
                        <label class="auth-field-label" for="activation_code"><?php _e('Enter your one-time code') ?></label>
                        <input type="text" id="activation_code" name="activation_code"
                            class="auth-input auth-input--otp<?php echo (isset($error) && $error !== '') ? ' auth-input--invalid' : ''; ?>"
                            placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code">
                        <p class="auth-text-muted auth-text-muted--left"><?php _e('Please enter the 6-digit code sent to your email.') ?></p>
                    </div>

                    <button type="submit" class="auth-btn-submit">
                        <?php _e('Continue') ?>
                    </button>
                </form>

                <p class="auth-text-muted">
                    <?php _e('Not recive the code?') ?>
                    <a class="auth-link-muted" href="<?php echo auth_url('resend-code/' . $user_id . '/' . $activation_string); ?>"><?php _e('Resend Code') ?></a>
                </p>

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
        const codeInput = document.getElementById('activation_code');
        if (!codeInput) return;

        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
            if (this.value.length === 6) {
                this.form.submit();
            }
        });

        codeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 6);
            this.value = pastedData;
            if (pastedData.length === 6) {
                this.form.submit();
            }
        });

        codeInput.focus();
    });
</script>

<?php
view_footer();
