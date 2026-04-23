<?php
$current_lang = APP_LANG;
$available_languages = APP_LANGUAGES;
?>
<div style="position:relative;display:inline-block;" id="languageDropdownV2">
  <button type="button" onclick="toggleLangV2()" id="langBtnV2" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;background:#fff;border:1px solid #e2e8f0;color:#475569;cursor:pointer;border-radius:12px;box-shadow:0 1px 3px rgba(30,64,175,0.06);">
    <span><?php echo lang_flag($current_lang); ?></span>
    <span><?php echo lang_name($current_lang); ?></span>
    <span id="langArrowV2" style="transition:transform 0.2s;">&#9660;</span>
  </button>
  <div id="langMenuV2" style="position:absolute;right:0;top:100%;margin-top:0.25rem;min-width:140px;background:#fff;border:1px solid #e2e8f0;padding:0.25rem;display:none;z-index:50;border-radius:12px;box-shadow:0 4px 12px rgba(30,64,175,0.1);">
    <?php foreach ($available_languages as $code => $info): ?>
      <?php if ($code !== $current_lang): ?>
        <a href="<?php echo lang_url($code); ?>" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;color:#334155;text-decoration:none;border-radius:8px;"><?php echo lang_flag($code); ?> <?php echo htmlspecialchars($info['name'] ?? $code); ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<script>
function toggleLangV2() {
  var m = document.getElementById('langMenuV2');
  var a = document.getElementById('langArrowV2');
  if (!m) return;
  if (m.style.display === 'block') { m.style.display = 'none'; if (a) a.style.transform = 'rotate(0deg)'; }
  else { m.style.display = 'block'; if (a) a.style.transform = 'rotate(180deg)'; }
}
document.addEventListener('click', function(e) {
  var d = document.getElementById('languageDropdownV2');
  if (!d || d.contains(e.target)) return;
  var m = document.getElementById('langMenuV2');
  var a = document.getElementById('langArrowV2');
  if (m && m.style.display === 'block') { m.style.display = 'none'; if (a) a.style.transform = 'rotate(0deg)'; }
});
</script>
