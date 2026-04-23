<!-- common/input/email.php -->
<div class="field px-1 floating-label mb-4 relative wrap-<?= $name ?> field_email <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-2 font-medium text-sm leading-5 text-gray-900">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>
    <input
        type="email"
        id="<?= $id ?>"
        name="<?= $name ?>"
        value="<?= htmlspecialchars($value) ?>"
        placeholder="<?= $placeholder ?>"
        <?= $required ? 'required' : '' ?>
        <?= $min ? 'minlength="' . $min . '"' : '' ?>
        <?= $max ? 'maxlength="' . $max . '"' : '' ?>
        class="<?= $css_class ?>"
    >
    <?php if ($description): ?>
        <p class="text-gray-500 text-sm mt-1"><?= $description ?></p>
    <?php endif; ?>
    <?php 
    if (!empty($error_message)) {
        if (is_array($error_message)) {
            foreach ($error_message as $error): ?>
                <p class="text-red-500 text-sm mt-1"><?= xss_clean($error) ?></p>
            <?php endforeach;
        } elseif (is_string($error_message)) {
            echo '<p class="text-red-500 text-sm mt-1">' . xss_clean($error_message) . '</p>';
        }
    }
    ?>
</div>
