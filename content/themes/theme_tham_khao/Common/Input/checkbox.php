<!-- common/input/checkbox.php -->

<div class="field px-1 mb-4 wrap-<?= $name ?> field_checkbox <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <p class="block mb-2 font-medium text-sm leading-5 text-gray-900">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </p>
    <?php endif; ?>
    <?php
    $selected_values = isset($value) ? (array)$value : [];
    foreach ($options as $option):
        $option_value = xss_clean($option['value'] ?? '');
        $option_label = xss_clean($option['label'] ?? '');
        $checked = in_array($option_value, $selected_values) ? 'checked' : '';
    ?>
        <label class="inline-flex items-center mt-1">
            <input
                type="checkbox"
                name="<?= $name ?>[]"
                value="<?= $option_value ?>"
                <?= $checked ?>
                <?= $required ? 'required' : '' ?>
                class="<?= $css_class ?>"
            >
            <span class="ml-2"><?= $option_label ?></span>
        </label><br>
    <?php endforeach; ?>
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
