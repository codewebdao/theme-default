<!-- common/input/radio.php -->
<div class="field floating-label mb-4 w-full px-3 py-2 border rounded-md relative wrap-<?= $name ?> field_radio <?= $id ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-2 font-medium text-sm leading-5 text-gray-900 block mb-2 font-medium text-sm leading-5 text-theme-bodycolor bg-white dark:text-themedark-bodycolor hover:text-primary-500 dark:hover:text-primary-500 dark:bg-themedark-cardbg">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>
    <?php
    foreach ($options as $option):
        $option_value = xss_clean($option['value'] ?? '');
        $option_label = xss_clean($option['label'] ?? '');
        $checked = ($option_value == $value) ? 'checked="checked"' : '';
        $disabled = ($option['disabled'] ?? false) ? 'disabled="disabled"' : '';
    ?>
        <div class="form-check inline-block mr-2 ">
            <input
                class="form-check-input cursor-pointer hover:outline-blue-400 focus:outline-blue-600 <?= $css_class ?>"
                type="radio"
                name="<?= $name ?>"
                value="<?= $option_value ?>"
                id="<?= $name ?>-<?= $option_value ?>"
                <?= $checked ?>
                <?= $disabled ?>
                <?= $required ? 'required' : '' ?>
            >
            <label class="form-check-label cursor-pointer" for="<?= $name ?>-<?= $option_value ?>">
                <?= $option_label ?>
            </label>
        </div>
    <?php endforeach; ?>
    <?php if ($description): ?>
        <p class="text-gray-500 text-sm mt-1">
            <?= $description ?>
        </p>
    <?php endif; ?>
    

    <?php 
    if (!empty($error_message)) {
        if (is_array($error_message)) {
            foreach ($error_message as $error): ?>
                <p class="text-red-500 text-sm mt-1">
                    <?= xss_clean($error) ?>
                </p>
            <?php endforeach;
        } elseif (is_string($error_message)) {
            echo '<p class="text-red-500 text-sm mt-1">' . xss_clean($error_message) . '</p>';
        }
    }
    ?>
</div>
