<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

Head::setTitle([Fastlang::__('Reset Your Password')]);
$auth_right_title = __('New Password');
$auth_right_subtitle = __('Choose a strong password to keep your account secure.');
$auth_right_points = [
  __('At least 6 characters'),
  __('Avoid common words'),
  __('You can sign in right after'),
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

          <div class="v2-auth-head">
            <h1><?php _e('Reset Your Password'); ?></h1>
            <p><?php _e('Enter your new password below'); ?></p>
          </div>

          <form method="POST" action="<?php echo auth_url('reset-password'); ?>" id="resetForm" class="v2-auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="user_id" value="<?php echo isset($user_id) ? htmlspecialchars($user_id) : ''; ?>">
            <input type="hidden" name="reset_token" value="<?php echo isset($reset_token) ? htmlspecialchars($reset_token) : ''; ?>">

            <?php
            $fields = [
              ['id' => 'password', 'name' => 'password', 'label' => __('New Password'), 'ph' => __('Enter new password')],
              ['id' => 'password_confirm', 'name' => 'password_confirm', 'label' => __('Confirm New Password'), 'ph' => __('Confirm new password')],
            ];
            foreach ($fields as $f):
              $err = isset($errors[$f['name']]) ? implode(', ', (array)$errors[$f['name']]) : '';
            ?>
            <div class="v2-auth-field">
              <label for="<?php echo $f['id']; ?>"><?php echo $f['label']; ?></label>
              <input type="password" id="<?php echo $f['id']; ?>" name="<?php echo $f['name']; ?>" required minlength="6" class="v2-auth-input"
                placeholder="<?php echo $f['ph']; ?>">
              <?php if ($err): ?><span class="v2-auth-err"><?php echo $err; ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>

            <p style="margin:0 0 1.5rem;color:#64748b;"><?php _e('At least 6 characters long'); ?> · <?php _e('Must match confirmation'); ?></p>

            <button type="submit" class="v2-auth-btn">
              <?php _e('Update Password'); ?>
            </button>
          </form>

          <div class="v2-auth-head-actions v2-auth-after-form">
            <a href="<?php echo auth_url('login'); ?>" class="v2-auth-btn-outline"><?php _e('Back to Login'); ?></a>
          </div>

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
  var f = document.getElementById('resetForm');
  var pw = document.getElementById('password');
  var cf = document.getElementById('password_confirm');
  if (f && pw && cf) {
    f.addEventListener('submit', function(e) {
      if (cf.value !== pw.value) {
        e.preventDefault();
        var s = cf.parentElement.querySelector('.v2-err');
        if (s) s.remove();
        var d = document.createElement('span');
        d.className = 'v2-err v2-auth-err';
        d.textContent = '<?php _e('Passwords do not match'); ?>';
        cf.parentElement.appendChild(d);
      }
    });
  }
});
</script>

<?php view_footer(); ?>
