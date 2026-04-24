<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

Head::setTitle([Fastlang::__('Register Account')]);
$auth_right_title = __('Join Us');
$auth_right_subtitle = __('Create an account to get started.');
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

          <div class="v2-auth-head">
            <h1><?php _e('Register'); ?></h1>
            <div class="v2-auth-mode-switch" role="tablist">
              <a href="<?php echo auth_url('login'); ?>" class="v2-auth-mode-btn" role="tab" aria-selected="false"><?php _e('Login'); ?></a>
              <span class="v2-auth-mode-btn v2-auth-mode-btn--active" role="tab" aria-selected="true" aria-current="page"><?php _e('Register'); ?></span>
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
            <?php _e('Register With Google'); ?>
          </a>

          <p class="v2-auth-divider">&mdash; <?php _e('or') ?> <?php _e('register') ?> &mdash;</p>

          <form action="<?php echo auth_url('register'); ?>" method="post" id="registerForm" class="v2-auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? Session::csrf_token(600); ?>">
            <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

            <?php
            $fields = [
              ['id' => 'username', 'name' => 'username', 'label' => __('Username'), 'type' => 'text', 'placeholder' => __('Username Placeholder')],
              ['id' => 'email', 'name' => 'email', 'label' => __('Email'), 'type' => 'email', 'placeholder' => __('Email Address Placeholder')],
              ['id' => 'password', 'name' => 'password', 'label' => __('Password'), 'type' => 'password', 'placeholder' => __('Password Placeholder')],
              ['id' => 'password_repeat', 'name' => 'password_repeat', 'label' => __('Confirm Password'), 'type' => 'password', 'placeholder' => __('Confirm Password Placeholder')],
            ];
            foreach ($fields as $f):
              $err = isset($errors[$f['name']]) ? implode(', ', (array)$errors[$f['name']]) : '';
              $val = in_array($f['name'], ['username','email']) && HAS_POST($f['name']) ? htmlspecialchars(S_POST($f['name'])) : '';
            ?>
            <div class="v2-auth-field">
              <label for="<?php echo $f['id']; ?>"><?php echo $f['label']; ?></label>
              <input type="<?php echo $f['type']; ?>" id="<?php echo $f['id']; ?>" name="<?php echo $f['name']; ?>" required class="v2-auth-input"
                value="<?php echo $val; ?>"
                placeholder="<?php echo $f['placeholder']; ?>">
              <?php if ($err): ?><span class="v2-auth-err"><?php echo $err; ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>

            <div style="margin-bottom:1.5rem;">
              <label style="display:flex;align-items:flex-start;gap:0.5rem;color:#64748b;cursor:pointer;">
                <input type="checkbox" name="terms" value="1" required style="margin-top:0.25rem;">
                <span style="font-size: 0.8rem;"><?php _e('I agree to the'); ?> <a href="<?php echo link_page('terms-of-service'); ?>" target="_blank" class="v2-auth-link"><?php _e('terms of service'); ?></a> <?php _e('and'); ?> <a href="<?php echo link_page('privacy-policy'); ?>" target="_blank" class="v2-auth-link"><?php _e('privacy policy'); ?></a></span>
              </label>
            </div>

            <button type="submit" class="v2-auth-btn">
              <?php _e('Register'); ?>
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
  var form = document.getElementById('registerForm');
  var pw = document.getElementById('password');
  var pwRep = document.getElementById('password_repeat');
  function err(el, msg) {
    var s = el.parentElement.querySelector('.v2-err');
    if (s) s.remove();
    var d = document.createElement('span');
    d.className = 'v2-err v2-auth-err';
    d.textContent = msg;
    el.parentElement.appendChild(d);
  }
  function ok(el) {
    var s = el.parentElement.querySelector('.v2-err');
    if (s) s.remove();
  }
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var v = true;
      if (pw && pw.value.length < 6) { err(pw, '<?php _e("Password must be at least 6 characters"); ?>'); v = false; } else if (pw) ok(pw);
      if (pwRep && pwRep.value !== (pw ? pw.value : '')) { err(pwRep, '<?php _e("Passwords do not match"); ?>'); v = false; } else if (pwRep) ok(pwRep);
      if (v) form.submit();
    });
  }
});
</script>

<?php view_footer(); ?>
