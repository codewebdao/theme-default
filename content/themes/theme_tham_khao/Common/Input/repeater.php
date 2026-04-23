<?php

use System\Libraries\Render;
?>
<!-- xoi update các input image, file chưa thể add ta vào được vì dựa trên data-name thay vì name input -->
<!-- common/input/repeater.php -->
<div class="field relative px-1 w-full mb-4 wrap-<?= htmlspecialchars($name) ?> field_repeater <?= $id ?>"
    style="<?= $visibility ? 'width:' . htmlspecialchars($width_value) . htmlspecialchars($width_unit) . ';' : 'display:none;' ?>">

    <?php if ($label): ?>
        <label class="block mb-2 font-medium text-sm leading-5 text-gray-900">
            <?= htmlspecialchars($label) ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>

    <div id="<?= htmlspecialchars($name) ?>" data-level="<?= $level ?>" class="repeater rounded-lg shadow-sm border border-gray-200">
        <?php
        $repeater_values = is_string($value) ? json_decode($value, true) : $value;
        $repeater_values = is_array($repeater_values) ? $repeater_values : [];
        if (empty($repeater_values)) {
            $repeater_values[] = [];
        }
        foreach ($repeater_values as $index => $repeater_value): ?>
            <div data-order="<?= $index ?>" class="repeater-item p-1 pt-3 pb-0 relative hover:bg-gray-100 border-x border-t transition-all flex items-start">
                <!-- Drag & move controls -->
                
                <div  style="position: absolute;z-index: 999;background: #fff;margin-top: -50px;" class="drag-controls inline-flex rounded-md shadow-xs invisible" role="group">
                    <button type="button" class="drag-handle cursor-move inline-flex items-center px-2 py-2 text-sm font-medium border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <i data-feather="move"></i>
                    </button>
                    <button type="button" class="move-up inline-flex items-center px-2 py-2 text-sm font-medium border-t border-b border-r border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <i data-feather="chevron-up"></i>
                    </button>
                    <button type="button" class="move-down inline-flex items-center px-2 py-2 text-sm font-medium border-t border-b border-r border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <i data-feather="chevron-down"></i>
                    </button>
                    <button type="button" class="cursor-add inline-flex items-center px-2 py-2 text-sm font-medium border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <i data-feather="plus"></i>
                    </button>
                    <button type="button" data-id="<?= htmlspecialchars($name) ?>" order="<?= $index ?>" class="repeater-remove  inline-flex items-center px-2 py-2 text-sm font-medium  border btn-danger rounded-e-lg ">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
                <div class="flex flex-wrap flex-1">
                    <?php
                    foreach ($fields as $sub_field):
                        if (!isset($sub_field['field_name']) && isset($sub_field['name'])) {
                            $sub_field['field_name'] = $sub_field['name'];
                        }
                        $sub_field_name = $sub_field['field_name'];
                        $sub_field_value = $repeater_value[$sub_field_name] ?? null;
                        $sub_error_message = $error_message[$index][$sub_field_name] ?? null;
                        $sub_field['data_name'] = $sub_field_name;
                        $sub_field['is_repeater'] = true;
                        $sub_field['level'] = ($level ?? 1) + 1;
                    ?>
                        <?= Render::input($sub_field, $sub_field_value, $sub_error_message) ?>
                    <?php endforeach; ?>
                </div>
                
                
            </div>
        <?php endforeach; ?>


        <!-- <button type="button"
            data-add="<?= htmlspecialchars($name) ?>"
            data-level="1"
            class="btn btn-primary right-1 bottom-1 flex gap-2 !ml-4 !mb-4 items-center justity-center">
            <i data-feather="plus"></i>
            Add
        </button> -->
    </div>

    <?php if ($description): ?>
        <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($description) ?></p>
    <?php endif; ?>
</div>
<?php if ($level == 1): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fieldId = "<?= $name ?>";
            initRepeaterField(fieldId);
        });
    </script>
<?php endif; ?>

<style type="text/css">
    .repeater-item:hover .drag-controls{
        visibility: visible !important;
    }
</style>