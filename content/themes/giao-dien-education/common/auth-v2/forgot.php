<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

Head::setTitle([Fastlang::__('Forgot Password')]);
$auth_right_title = __('Reset Password');
$auth_right_subtitle = __('We will send you instructions to recover your account.');
$auth_right_points = [
  __('Check your email for the reset link'),
  __('Use a strong password for security'),
  __('You can sign in right after updating'),
];
view_header();
?>

<main class="v2-auth">
  <div class="v2-auth-grid">
    <div class="v2-auth-form-col">
      <div class="v2-auth-form-wrap">
        <a href="<?php echo base_url(); ?>" class="v2-auth-back">&larr; <?php _e('Back to Home'); ?></a>

        <section class="v2-auth-card">
          <?php if (isset($errors) && !empty($errors)): ?>
            <div class="v2-auth-msg err">
              <strong><?php _e('Please Correct Errors'); ?></strong>
              <ul style="margin:0.5rem 0 0 1rem;padding:0;">
                <?php foreach ($errors as $field => $fieldErrors): ?>
                  <?php foreach ((array)$fieldErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <?php if (Session::has_flash('success')): ?>
            <div class="v2-auth-msg ok"><?php echo Session::flash('success'); ?></div>
          <?php endif; ?>
          <?php if (Session::has_flash('error')): ?>
            <div class="v2-auth-msg err"><?php echo Session::flash('error'); ?></div>
          <?php endif; ?>

          <div class="v2-auth-head">
            <h1><?php _e('Forgot Password'); ?></h1>
            <p><?php _e('Enter email receive password reset link'); ?></p>
            <div class="v2-auth-head-actions">
              <a href="<?php echo auth_url('login'); ?>" class="v2-auth-btn-outline"><?php _e('Back to Login'); ?></a>
            </div>
          </div>

          <form method="POST" action="<?php echo auth_url('forgot'); ?>" class="v2-auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="v2-auth-field">
              <label for="email"><?php _e('Email Address'); ?></label>
              <input type="email" id="email" name="email" required class="v2-auth-input"
                value="<?php echo HAS_POST('email') ? htmlspecialchars(S_POST('email')) : ''; ?>"
                placeholder="<?php _e('Enter your email'); ?>">
              <?php if (isset($errors['email'])): ?>
                <span class="v2-auth-err"><?php echo implode(', ', (array)$errors['email']); ?></span>
              <?php endif; ?>
            </div>
            <button type="submit" class="v2-auth-btn">
              <?php _e('Forgot Password'); ?>
            </button>
          </form>

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

<?php view_footer(); ?>
