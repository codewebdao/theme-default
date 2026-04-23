<?php
$auth_right_title = $auth_right_title ?? __('Welcome');
$auth_right_subtitle = $auth_right_subtitle ?? '';
$auth_right_points = $auth_right_points ?? [];
if (empty($auth_right_points)) {
  $auth_right_points = [
    __('Secure login and data protection'),
    __('Manage your account in one place'),
    __('Quick access from any device'),
  ];
}
?>
<div class="v2-auth-right-shapes" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
<div class="v2-auth-right-inner">
  <h2 style="margin:0 0 0.75rem;font-weight:700;color:#1d4ed8;line-height:1.3;">
    <?php echo htmlspecialchars($auth_right_title); ?>
  </h2>
  <?php if ($auth_right_subtitle): ?>
    <p style="margin:0 0 1.5rem;color:#475569;line-height:1.6;">
      <?php echo htmlspecialchars($auth_right_subtitle); ?>
    </p>
  <?php endif; ?>
  <ul style="margin:0;padding:0;list-style:none;">
    <?php foreach ($auth_right_points as $i => $point): ?>
      <li style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem;color:#334155;line-height:1.5;">
        <span style="flex-shrink:0;width:28px;height:28px;border-radius:50%;background:#bfdbfe;color:#1d4ed8;display:inline-flex;align-items:center;justify-content:center;font-weight:700;"><?php echo $i + 1; ?></span>
        <?php echo is_string($point) ? htmlspecialchars($point) : $point; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
