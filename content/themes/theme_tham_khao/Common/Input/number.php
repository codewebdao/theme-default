<!-- common/input/number.php -->
<div class="field px-1 floating-label mb-4 relative wrap-<?= $name ?> field_number <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label title="<?= $label ?>" for="<?= $id ?>" class="line-clamp-1 mb-2 font-medium text-sm leading-5 text-theme-bodycolor bg-white dark:text-themedark-bodycolor hover:text-primary-500 dark:hover:text-primary-500 dark:bg-themedark-cardbg">
            <p class="inline"><?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?></p>
        </label>
    <?php endif; ?>
    <input
        type="number"
        id="<?= $id ?>"
        name="<?= $name ?>"
        value="<?= htmlspecialchars($value) ?>"
        placeholder="<?= $placeholder ?>"
        step="<?= $step ?? 1 ?>"
        <?= $required ? 'required' : '' ?>
        <?= $min !== null ? 'min="' . $min . '"' : '' ?>
        <?= $max !== null ? 'max="' . $max . '"' : '' ?>
        class="form-control border px-3 py-2 w-full rounded-md hover:outline-blue-400 focus:outline-blue-600 <?= $css_class ?>"
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
