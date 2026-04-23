<!-- common/input/oembed.php -->
<div class="field px-1 floating-label mb-4 relative wrap-<?= $name ?> field_oembed <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-2 font-medium text-sm leading-5 text-gray-900">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>
    <input
        type="url"
        id="<?= $id ?>"
        name="<?= $name ?>"
        value="<?= xss_clean($value) ?>"
        placeholder="<?= $placeholder ?>"
        <?= $required ? 'required' : '' ?>
        class="<?= $css_class ?> form-control w-full px-3 py-2 border rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
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
    