<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

$title = isset($activationType) && $activationType === 'forgot_password' ? __('Password Reset') : __('Account Activation');
Head::setTitle([Fastlang::__('Enter Confirmation Code')]);
$auth_right_title = __('Almost There');
$auth_right_subtitle = __('Enter the code we sent to your email to continue.');
$auth_right_points = [
  __('Check your inbox and spam folder'),
  __('Code is 8 digits only'),
  __('Request a new code if it expired'),
];
view_header();
?>

<main class="v2-auth">
  <div class="v2-auth-grid">
    <div class="v2-auth-form-col">
      <div class="v2-auth-form-wrap">
        <a href="<?php echo base_url(); ?>" class="v2-auth-back">&larr; <?php _e('Back to Home'); ?></a>

        <section class="v2-auth-card">
          <?php if (Session::has_flash('error')): ?>
            <div class="v2-auth-msg err"><?php echo Session::flash('error'); ?></div>
          <?php endif; ?>
          <?php if (Session::has_flash('success')): ?>
            <div class="v2-auth-msg ok"><?php echo Session::flash('success'); ?></div>
          <?php endif; ?>
          <?php if (isset($cooldown_until) && $cooldown_until > time()): ?>
            <?php $rem = ceil(($cooldown_until - time()) / 60); ?>
            <div class="v2-auth-msg" style="background:#fffbeb;color:#b45309;"><?php _e('Please wait %1% minutes before requesting a new code.', $rem); ?></div>
          <?php endif; ?>

          <div class="v2-auth-head">
            <h1><?php _e('Enter Confirmation Code'); ?></h1>
            <p><?php _e('Enter 8 numbers at: %1%', isset($email) ? $email : ''); ?></p>
            <div class="v2-auth-head-actions">
              <a href="<?php echo auth_url('login'); ?>" class="v2-auth-btn-outline"><?php _e('Back to Login'); ?></a>
            </div>
          </div>

          <form method="POST" action="<?php echo auth_url('confirm'); ?>" class="v2-auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="v2-auth-field">
              <label for="confirmation_code"><?php _e('Enter your 8-digit confirmation code'); ?></label>
              <input type="text" id="confirmation_code" name="confirmation_code" required maxlength="8" pattern="[0-9]{8}" class="v2-auth-input"
                placeholder="00000000" autocomplete="one-time-code"
                style="text-align:center;letter-spacing:0.3em;">
              <p style="margin:0.5rem 0 0;color:#64748b;"><?php _e('Please enter the 8-digit code sent to your email.'); ?></p>
            </div>
            <button type="submit" class="v2-auth-btn">
              <?php _e('Continue'); ?>
            </button>
          </form>

          <p style="margin-top:1.5rem;color:#64748b;text-align:center;">
            <?php _e('Not recive the code?'); ?>
            <?php if (isset($cooldown_until) && $cooldown_until > time()): ?>
              <span style="color:#94a3b8;"><?php _e('Resend Code') ?> (<?php _e('Please wait %1% minutes', ceil(($cooldown_until - time()) / 60)); ?>)</span>
            <?php else: ?>
              <a href="<?php echo auth_url('resend_code'); ?>" class="v2-auth-link"><?php _e('Resend Code'); ?></a>
            <?php endif; ?>
          </p>

          <div class="v2-auth-footer">
            <?php echo View::include('language-switcher'); ?>
          </div>
        </section>
      </div>
    </div>

    <div class="v2-auth-right">
      <?php echo View::include('auth-right'); ?>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var inp = document.getElementById('confirmation_code');
  if (inp) {
    inp.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
      if (this.value.length === 8) this.form.submit();
    });
    inp.focus();
  }
});
</script>

<?php view_footer(); ?>
