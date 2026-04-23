<?php

namespace System\Libraries;

Render::block('Backend\Header', ['layout' => 'default', 'title' => $title ?? 'Languages']);

use System\Libraries\Session;
?>
<div class="pc-container">
  <div class="pc-content">
    <div class="card">
      <!-- Card Header: Page Title & Breadcrumb -->
      <div class="card-header">
        <div class="page-block">
          <?php Render::block('Backend\PageTitle', [
            'layout'     => 'default',
            'title'      => __('Edit Language'),
            'breadcrumb' => [
              ['title' => 'Home', 'url' => admin_url('home')],
              ['title' => __('Languages'), 'url' => admin_url('languages')],
              // Bạn có thể thêm các breadcrumb khác nếu cần
            ],
          ]); ?>
        </div>
      </div>
      <!-- End Card Header -->

      <!-- Card Body: Form chỉnh sửa Language -->
      <div class="card-body">
        <!-- Flash messages -->
        <!-- notification success -->
        <?php if (Session::has_flash('success')): ?>
          <?php Render::block('Backend\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
        <?php endif; ?>

        <!-- notification error -->
        <?php if (Session::has_flash('error')): ?>
          <?php Render::block('Backend\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
        <?php endif; ?>

        <form method="post" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

          <!-- Language Name -->
          <div>
            <label for="name" class="block text-sm font-medium ">
              <?= __('Language Name') ?>:
            </label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($language['name']); ?>" required
              class="form-control mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          </div>

          <!-- Language Code -->
          <div>
            <label for="code" class="block text-sm font-medium ">
              <?= __('Language Code') ?>:
            </label>
            <input type="text" name="code" id="code" value="<?= htmlspecialchars($language['code']); ?>" required
              class="form-control mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          </div>

          <!-- Country Flag -->
          <div>
            <label for="flag" class="block text-sm font-medium ">
              <?= __('Country Flag') ?>:
            </label>
            <input type="text" name="flag" id="flag" value="<?= htmlspecialchars($language['flag'] ?? ''); ?>" 
              class="form-control mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., us, vn, uk">
            <p class="text-xs text-gray-500 mt-1">Country code for flag (e.g., us, vn, uk, fr)</p>
          </div>

          <!-- Locale -->
          <div>
            <label for="locale" class="block text-sm font-medium ">
              <?= __('Locale') ?>:
            </label>
            <input type="text" name="locale" id="locale" value="<?= htmlspecialchars($language['locale'] ?? ''); ?>" 
              class="form-control mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., en_US, vi_VN">
            <p class="text-xs text-gray-500 mt-1">Locale code (auto-generated from code_FLAG). Format: code_FLAG (e.g., en_US, vi_VN)</p>
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium ">
              <?= __('Status') ?>:
            </label>
            <select name="status" id="status"
              class="form-control mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              <option value="active" <?= $language['status'] == 'active' ? 'selected' : ''; ?>>
                <?= __('Active'); ?>
              </option>
              <option value="inactive" <?= $language['status'] == 'inactive' ? 'selected' : ''; ?>>
                <?= __('Inactive'); ?>
              </option>
            </select>
          </div>

          <!-- Default -->
          <div>
            <label for="is_default" class="block text-sm font-medium ">
              <?= __('Default') ?>
            </label>
            <select name="is_default" id="is_default"
              class="form-control mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              <?php if ($language['is_default'] == 1): ?>
                <option value="1" selected><?= __('Yes'); ?></option>
                <option value="0"><?= __('No'); ?></option>
              <?php else: ?>
                <option value="0" selected><?= __('No'); ?></option>
                <option value="1"><?= __('Yes'); ?></option>
              <?php endif; ?>
            </select>
          </div>

          <!-- Submit & Delete -->
          <div class="flex flex-wrap items-center justify-between gap-2">
            <button type="submit" class="btn btn-primary w-full md:w-fit">
              <?= __('Update'); ?>
            </button>
            <a href="<?= admin_url('languages/delete/' . $language['id']); ?>"
              class="btn btn-danger w-full md:w-fit"
              onclick="return confirm('<?= __('confirm_delete'); ?>');">
              <?= __('Delete'); ?>
            </a>
          </div>
        </form>
      </div>
      <!-- End Card Body -->
    </div>
  </div>
</div>

<script>
// Auto-generate locale from code and flag
document.addEventListener('DOMContentLoaded', function() {
  const codeInput = document.getElementById('code');
  const flagInput = document.getElementById('flag');
  const localeInput = document.getElementById('locale');

  function updateLocale() {
    const code = codeInput.value.trim().toLowerCase();
    const flag = flagInput.value.trim().toUpperCase();
    
    if (code && flag) {
      // Only auto-update if locale is empty or matches the pattern
      const currentLocale = localeInput.value.trim();
      if (!currentLocale || currentLocale.match(/^[a-z]{2}_[A-Z]{2}$/)) {
        localeInput.value = code + '_' + flag;
      }
    }
  }

  // Update locale when code or flag changes
  codeInput.addEventListener('input', updateLocale);
  codeInput.addEventListener('blur', updateLocale);
  flagInput.addEventListener('input', updateLocale);
  flagInput.addEventListener('blur', updateLocale);
});
</script>

<?php Render::block('Backend\Footer', ['layout' => 'default']); ?>