<!-- common/input/checkbox.php -->
<div class="field px-1 mb-4 wrap-<?= $name ?> field_boolean <?= $id ?>"
     style="<?= $visibility ? "width:{$width_value}{$width_unit};" : 'display:none;' ?>">
  
  <div class="form-check form-switch">
    <label for="<?= $id ?>" class="inline-flex items-center cursor-pointer">
      <!-- (1) Hidden input để luôn gửi giá trị 0 khi unchecked -->
      <input type="hidden" name="<?= $name ?>" value="0">

      <!-- (2) Checkbox thật -->
      <input
        id="<?= $id ?>"
        type="checkbox"
        name="<?= $name ?>"
        value="1"
        <?= $required ? 'required' : '' ?>
        class="form-check-input input-primary !rounded-full <?= $css_class ?>"
        style="width: 3rem; font-size: 20px;"
        <?= !empty($value) ? 'checked' : '' ?>
      />

      <!-- (3) Text label bên cạnh -->
      <?php if ($label): ?>
        <span class="ml-2 block font-medium text-sm leading-5 text-gray-900">
          <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </span>
      <?php endif; ?>
    </label>
  </div>

  <?php if ($description): ?>
    <p class="text-gray-500 text-sm mt-1"><?= $description ?></p>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <?php if (is_array($error_message)): ?>
      <?php foreach ($error_message as $error): ?>
        <p class="text-red-500 text-sm mt-1"><?= xss_clean($error) ?></p>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-red-500 text-sm mt-1"><?= xss_clean($error_message) ?></p>
    <?php endif; ?>
  <?php endif; ?>
</div>
