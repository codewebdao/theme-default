<?php
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use App\Libraries\Fastlang;

Head::setTitle([Fastlang::__('Login Account')]);
$auth_right_title = __('Welcome Back');
$auth_right_subtitle = __('Sign in to access your account and continue.');
$auth_right_points = [
  __('Secure login and data protection'),
  __('Manage your account in one place'),
  __('Quick access from any device'),
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
            <h1><?php _e('Welcome Back - Sign In'); ?></h1>
            <div class="v2-auth-mode-switch" role="tablist">
              <span class="v2-auth-mode-btn v2-auth-mode-btn--active" role="tab" aria-selected="true" aria-current="page"><?php _e('Sign In'); ?></span>
              <a href="<?php echo auth_url('register'); ?>" class="v2-auth-mode-btn" role="tab" aria-selected="false"><?php _e('Register'); ?></a>
            </div>
          </div>

          <a href="<?php echo auth_url('google'); ?>" class="v2-auth-google">
            <span style="height: 50px;">
            
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" style="height: 16px; margin-right: 10px; width: 16px;padding-top: 5px;" viewBox="0 0 48 48" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
              <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
              <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
              <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
              <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
              <path fill="none" d="M0 0h48v48H0z"></path>
            </svg>     

            </span> 
            <?php _e('Login With Google'); ?>
          </a>

          <p class="v2-auth-divider">&mdash; <?php _e('or') ?> <?php _e('login with') ?> &mdash;</p>

          <form method="POST" action="<?php echo auth_url('login'); ?>" id="loginForm" class="v2-auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

            <div class="v2-auth-field">
              <label for="username"><?php _e('Email or Username'); ?></label>
              <input type="text" id="username" name="username" required class="v2-auth-input"
                value="<?php echo HAS_POST('username') ? htmlspecialchars(S_POST('username')) : ''; ?>"
                placeholder="<?php _e('Email or Username'); ?>">
              <?php if (isset($errors['username'])): ?>
                <span class="v2-auth-err"><?php echo implode(', ', (array)$errors['username']); ?></span>
              <?php endif; ?>
            </div>

            <div class="v2-auth-field">
              <label for="password"><?php _e('Password'); ?></label>
              <input type="password" id="password" name="password" required class="v2-auth-input"
                placeholder="<?php _e('Password'); ?>">
              <?php if (isset($errors['password'])): ?>
                <span class="v2-auth-err"><?php echo implode(', ', (array)$errors['password']); ?></span>
              <?php endif; ?>
            </div>

            <div class="v2-auth-actions">
              <label style="display:flex;align-items:center;gap:0.5rem;color:#64748b;cursor:pointer;">
                <input type="checkbox" name="remember" value="on" <?php echo (HAS_POST('remember') && S_POST('remember') == 'on') ? 'checked' : ''; ?>>
                <?php _e('Remember Me'); ?>
              </label>
              <a href="<?php echo auth_url('forgot'); ?>" class="v2-auth-link"><?php _e('Forgot Password'); ?></a>
            </div>

            <button type="submit" class="v2-auth-btn">
              <?php _e('Sign In'); ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  var f = document.getElementById('loginForm');
  var u = document.getElementById('username');
  var p = document.getElementById('password');
  function err(el, msg) {
    var s = el.parentElement.querySelector('.v2-err');
    if (s) s.remove();
    var div = document.createElement('span');
    div.className = 'v2-err v2-auth-err';
    div.textContent = msg;
    el.parentElement.appendChild(div);
  }
  function ok(el) {
    var s = el.parentElement.querySelector('.v2-err');
    if (s) s.remove();
  }
  if (f) {
    f.addEventListener('submit', function(e) {
      e.preventDefault();
      var valid = true;
      if (!u.value.trim()) { err(u, '<?php _e("Email or Username is required"); ?>'); valid = false; } else ok(u);
      if (!p.value || p.value.length < 6) { err(p, '<?php _e("Password is required"); ?>'); valid = false; } else ok(p);
      if (valid) f.submit();
    });
  }
});
</script>

<?php view_footer(); ?>
