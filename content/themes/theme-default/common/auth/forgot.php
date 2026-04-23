<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

Head::setTitle([Fastlang::__('Forgot Password')]);
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

            <h1 class="auth-login-title"><?php _e('Forgot Password') ?></h1>
            <p class="auth-login-sub"><?php _e('Enter email receive password reset link') ?></p>
            <p class="auth-login-sub" style="margin-top:-1rem">
                <?php _e('or') ?>
                <a href="<?php echo auth_url('login'); ?>"><?php _e('Login If Account Exists') ?></a>
            </p>

            <div class="auth-stack">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="auth-alert auth-alert--error" role="alert">
                        <i data-lucide="alert-triangle" class="h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        <div>
                            <strong><?php _e('Please Correct Errors'); ?></strong>
                            <ul>
                                <?php foreach ($errors as $fieldErrors): ?>
                                    <?php foreach ((array) $fieldErrors as $error): ?>
                                        <li><?php echo htmlspecialchars((string) $error); ?></li>
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

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="auth-stack" style="gap:1rem">
                        <div class="auth-field-group">
                            <label class="auth-field-label" for="email"><?php _e('Email Address') ?></label>
                            <div class="auth-input-wrap">
                                <i data-lucide="mail" class="auth-input-icon"></i>
                                <input type="email" id="email" name="email" class="auth-input<?php echo isset($errors['email']) ? ' auth-input--invalid' : ''; ?>"
                                    placeholder="<?php echo e(Fastlang::__('Enter your email')); ?>"
                                    value="<?php echo HAS_POST('email') ? htmlspecialchars(S_POST('email')) : ''; ?>"
                                    autocomplete="email" required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="auth-field-error"><?php echo implode(', ', array_map('htmlspecialchars', $errors['email'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="auth-btn-submit">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            <?php _e('Send Instructions') ?>
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
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php
view_footer();
