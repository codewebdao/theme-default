<!-- common/input/point.php -->
<div 
    class="field floating-label px-1 mb-4 relative wrap-<?= $name ?> field_point <?= $id ?>" 
    style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>"
>
    <?php if (!empty($label)): ?>
        <label class="block mb-2 font-medium text-sm leading-5 text-gray-900">
            <?= $label ?>
            <?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>

    <?php
    if (preg_match('/^POINT\(([-+]?\d*\.?\d+)\s+([-+]?\d*\.?\d+)\)$/', $value, $matches)) {
        $lngValue = $matches[1]; // Get longitude
        $latValue = $matches[2]; // Get latitude
    }
    ?>

    <div class="flex space-x-2">
        <!-- Input số cho Latitude -->
        <input
            type="number"
            step="any"        
            id="<?= $id ?>-lat"
            name="<?= $name ?>[]" 
            value="<?= $latValue ?>"
            placeholder="Latitude"
            <?= $required ? 'required' : '' ?>
            class="<?= $css_class ?>"
        >
        <!-- Input số cho Longitude -->
        <input
            type="number"
            step="any"
            id="<?= $id ?>-lng"
            name="<?= $name ?>[]" 
            value="<?= $lngValue ?>"
            placeholder="Longitude"
            <?= $required ? 'required' : '' ?>
            class="<?= $css_class ?>"
        >
    </div>

    <?php if (!empty($description)): ?>
        <p class="text-gray-500 text-sm mt-1"><?= $description ?></p>
    <?php endif; ?>

    <?php 
    // Display error (if any)
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
