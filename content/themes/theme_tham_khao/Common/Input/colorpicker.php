<!-- common/input/colorpicker.php -->
<div class="field floating-label px-1 mb-4 relative wrap-<?= $name ?> field_colorpicker <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-2 font-medium text-sm leading-5 text-gray-900">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>
    <input
        type="color"
        id="<?= $id ?>"
        name="<?= $name ?>"
        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
        <?= $required ? 'required' : '' ?>
        class="<?= $css_class ?> w-10 h-10 p-0 border-0 focus:ring-0"
    >
    <?php if ($description): ?>
        <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php 
    if (!empty($error_message)) {
        if (is_array($error_message)) {
            foreach ($error_message as $error): ?>
                <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach;
        } elseif (is_string($error_message)) {
            echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }
    ?>
</div>
