
<!-- common/input/image.php -->
<?php 
$value_decode = is_string($value) ? json_decode($value, true) : $value;
?>
<div class="fieldset px-1 w-full md:w-[354px] mb-8 wrap-<?= $name ?> field_image <?= $id ?> <?= $is_repeater ? 'is_repeater' : '' ?>"
     style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label class="block mb-4 font-medium text-sm leading-5">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>

    <!-- Upload Area -->
    <div id="upload-area-<?= $name ?>"
         class="form-control flex flex-col items-center justify-center w-full h-48 p-4 rounded-lg border-2 border-dashed border-gray-300"
         style="<?= (!empty($value_decode) && !$multiple) ? 'display: none;' : ''; ?>">
        <div class="text-center cursor-pointer" id="open-storage-button-<?= $name ?>">
            <div class="flex justify-center text-gray-400 mb-4">
                <!-- icon upload -->
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M11 34C6.02944 34 2 29.9706 2 25C2 21.0368 4.56168 17.6719 8.11959 16.4708C8.04092 15.9922 8 15.5009 8 15C8 10.0294 12.0294 6 17 6C20.2808 6 23.1515 7.75543 24.7243 10.3783C25.4377 10.1331 26.2033 10 27 10C30.866 10 34 13.134 34 17C34 17.6667 33.9068 18.3116 33.7327 18.9224C36.2706 20.2636 38 22.9298 38 26C38 30.4183 34.4183 34 30 34H11ZM19.25 28.5C19.25 28.9142 19.5858 29.25 20 29.25C20.4142 29.25 20.75 28.9142 20.75 28.5V17.4099L25.9504 23.0103C26.2323 23.3139 26.7068 23.3314 27.0103 23.0496C27.3139 22.7677 27.3314 22.2932 27.0496 21.9897L20.5496 14.9897C20.4077 14.8368 20.2086 14.75 20 14.75C19.7914 14.75 19.5923 14.8368 19.4504 14.9897L12.9504 21.9897C12.6686 22.2932 12.6861 22.7677 12.9897 23.0496C13.2932 23.3314 13.7677 23.3139 14.0496 23.0103L19.25 17.4099V28.5Z"
                          fill="#6B7280"/>
                </svg>
            </div>

            <p class="form-control border-none">
                Click to upload
                <span class="text-gray-500 text-sm">(<?= implode(', ', $allow_types) ?>)</span>
            </p>

            <p class="mt-2 text-sm text-gray-600">
                Autocrop: <span class="font-medium leading-5"><?= $autocrop ? 'Yes' : 'No' ?></span>,
                Watermark: <span class="font-medium leading-5"><?= $watermark ? 'Yes' : 'No' ?></span>
            </p>
        </div>
    </div>

    <input type="hidden"
           id="<?= $name ?>_data"
           name="<?= $name ?>"
           <?= $required ? 'required' : '' ?>
           value='<?= $value ?>'>

    <?php if ($multiple): ?>
        <div id="multi-preview-area-<?= $name ?>"
             class="<?= empty($value_decode) ? 'hidden' : ''; ?> mt-6">
        </div>
    <?php else: ?>
        <!-- Single preview -->
        <div id="preview-area-<?= $name ?>"
             class="<?= empty($value_decode) ? 'hidden' : ''; ?> mt-6">
            <div class="flex items-center justify-between w-full p-4 rounded-lg bg-gray-50 truncate">
                <div class="flex items-center">
                    <?php if (!empty($value_decode)): 
                        $url_image = base_url('uploads/') . $value_decode['path'];
                        echo _img(
                            $url_image, 'Preview',
                            false,
                            'h-20 max-h-20 max-w-20 mr-4 rounded-lg object-contain',
                            '', '', '', 'file-preview-' . $name
                        );
                    else: ?>
                        <img id="file-preview-<?= $name ?>"
                             src=""
                             alt="Preview"
                             class="h-20 max-h-20 max-w-20 mr-4 rounded-lg object-contain">
                    <?php endif; ?>

                    <div>
                        <p id="image-name-<?= $name ?>"
                           class="font-semibold truncate max-w-xs">
                            <?= $value_decode['name'] ?? '' ?>
                        </p>
                        <p id="image-size-<?= $name ?>"
                           class="text-gray-500 text-sm"></p>

                        <div class="flex items-center space-x-4 mt-2">
                            <div id="replace-button-file-<?= $name ?>"
                                 class="text-primary text-sm font-medium leading-5 cursor-pointer">
                                Thay thế
                            </div>
                            <div id="remove-button-file-<?= $name ?>"
                                 class="text-gray-600 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

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

<!-- Modal -->
<div id="file-manager-modal-<?= $name ?>"
     class="fixed inset-0 z-[99999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        <div class="relative bg-white rounded-lg w-full max-w-7xl !h-[80vh] max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Thư viện Media</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-hidden">
                <iframe id="file-manager-iframe-<?= $name ?>"
                        class="w-full h-full"
                        src="about:blank"
                        frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<?php 
$dataconfig = [
    'fieldId'      => $name,
    'multi'        => $multiple,
    'value'        => $value,
    'autocrop'     => $autocrop,
    'watermark'    => $watermark,
    'watermark_img'=> $watermark_img,
    'resizes'      => $resizes,
    'type'         => 'image',
    'extensions'   => $allow_types,
];
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    initStorage(<?= json_encode($dataconfig) ?>);
});
</script>
