<!-- common/input/text.php -->
<div class="field floating-label px-1 mb-4 relative wrap-<?= $name ?> field_text <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-2 font-medium text-sm leading-5 text-theme-bodycolor bg-white dark:text-themedark-bodycolor hover:text-primary-500 dark:hover:text-primary-500 dark:bg-themedark-cardbg">
            <?= $label ?><?= $required ? '<span class="text-red-500 dark:text-white">*</span>' : '' ?>
        </label>
    <?php endif; ?>
    <input
        type="text"
        id="<?= $id ?>"
        name="<?= $name ?>"
        value="<?= xss_clean($value) ?>"
        placeholder="<?= $placeholder ?>"
        <?= $required ? 'required' : '' ?>
        <?= $min ? 'minlength="' . $min . '"' : '' ?>
        <?= $max ? 'maxlength="' . $max . '"' : '' ?>
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

<?php
if (!empty($autofill)){
    $slug_name = $autofill;
    ?>

<script type="text/javascript">
if (!window.autofill) {
    window.autofill = {};
}
if (!autofill['<?= $slug_name ?>']) {
    autofill['<?= $slug_name ?>'] = true;
    document.addEventListener("DOMContentLoaded", function() {
        jFast('input[name=<?= $name ?>]').on('keyup change', function() {
            var slug;
            if ('<?= $autofill_type; ?>' == 'slug'){
                slug = url_slug( jFast(this).val() );
            }else if ('<?= $autofill_type; ?>' == 'keyword'){
                slug = keyword_slug( jFast(this).val() );
            }else{
                slug = jFast(this).val();
            }
            
            jFast('input[name=<?= $slug_name ?>]').val(slug).trigger('change');
        });
    });
}
</script>

    <?php
}
