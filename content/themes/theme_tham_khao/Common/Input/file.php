<!-- common/input/file.php -->
<div class="fieldset px-1 w-full md:w-[354px] mb-8 wrap-<?= $name ?> field_file <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label class="block mb-4 font-medium text-sm leading-5">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>

    <!-- Upload Area -->
    <div id="upload-area-<?= $name ?>" class="form-control flex flex-col items-center justify-center w-full h-48 p-4 rounded-lg border-2 border-dashed border-gray-300" style="<?= (!empty($value_decode) && !$multiple ) ? 'display: none;' : ''; ?>">
        <div class="text-center cursor-pointer" id="open-storage-button-<?= $name ?>">
            <div class="flex justify-center text-gray-400 mb-4">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11 34C6.02944 34 2 29.9706 2 25C2 21.0368 4.56168 17.6719 8.11959 16.4708C8.04092 15.9922 8 15.5009 8 15C8 10.0294 12.0294 6 17 6C20.2808 6 23.1515 7.75543 24.7243 10.3783C25.4377 10.1331 26.2033 10 27 10C30.866 10 34 13.134 34 17C34 17.6667 33.9068 18.3116 33.7327 18.9224C36.2706 20.2636 38 22.9298 38 26C38 30.4183 34.4183 34 30 34H11ZM19.25 28.5C19.25 28.9142 19.5858 29.25 20 29.25C20.4142 29.25 20.75 28.9142 20.75 28.5V17.4099L25.9504 23.0103C26.2323 23.3139 26.7068 23.3314 27.0103 23.0496C27.3139 22.7677 27.3314 22.2932 27.0496 21.9897L20.5496 14.9897C20.4077 14.8368 20.2086 14.75 20 14.75C19.7914 14.75 19.5923 14.8368 19.4504 14.9897L12.9504 21.9897C12.6686 22.2932 12.6861 22.7677 12.9897 23.0496C13.2932 23.3314 13.7677 23.3139 14.0496 23.0103L19.25 17.4099V28.5Z" fill="#6B7280"/>
                </svg>
            </div>
            <p class="form-control border-none">
                <p>Click for upload <span class="text-gray-500 text-sm">(<?= implode(', ', $allow_types) ?>)</span></p>
                
            </p>
            <div class="form-control rounded-md border-none cursor-pointer">
                <!-- Chọn từ Thư viện -->
            </div>
        </div>
    </div>

    <input type="hidden" id="<?= $name ?>_data"  <?= $required ? 'required' : '' ?> name="<?= $name ?>" value='<?= $value ?>'>
    <?php if($multiple == 1) {?>
        <div id="multi-preview-area-<?= $name ?>" class="<?= empty($value_decode) ? 'hidden' : ''; ?> mt-6">
        </div>
    <?php } else { ?>
        <!-- Preview Section -->
        <div id="preview-area-<?= $name ?>" class="<?= empty($value_decode) ? 'hidden' : ''; ?> mt-6">
            <div class="flex items-center justify-between w-full p-4 rounded-lg bg-gray-50 truncate">
                <div class="flex items-center">
                    <?php 
                    if(!empty($value_decode)) {
                        $url_file = base_url('uploads/') . $value_decode['path'];
                        $file_extension = pathinfo($value_decode['path'], PATHINFO_EXTENSION);
                        $is_image = in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                        
                        if($is_image) {
                            echo _img($url_file ?? '', 'Preview', false, 'h-20 max-h-20 max-w-20 mr-4 rounded-lg object-contain', '', '', '', 'file-preview-' . $name);
                        } else {
                            echo '<img id="file-preview-' . $name . '" 
                                     src="' . base_url('uploads/assets/filetype/' . strtolower($file_extension) . '.svg') . '" 
                                     alt="' . $file_extension . '" 
                                     class="h-20 max-h-20 max-w-20 mr-4 rounded-lg object-contain"
                                     onerror="this.src=\'' . base_url('uploads/assets/filetype/file2.svg') . '\'">';
                        }
                    } else {
                        echo '<img id="file-preview-' . $name . '" 
                                 src="' . base_url('uploads/assets/filetype/file.svg') . '" 
                                 alt="File" 
                                 class="h-20 max-h-20 max-w-20 mr-4 rounded-lg object-contain">';
                    }
                    ?>
                    <div>
                        <div>
                            <p id="file-name-<?= $name ?>" style="max-width: 20rem;" class="font-semibold truncate max-w-xs"><?= $value_decode['name'] ?? ''; ?></p>
                            <p id="file-size-<?= $name ?>" class="text-gray-500 text-sm"></p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div id="replace-button-file-<?= $name ?>" class="text-primary text-sm font-medium leading-5 cursor-pointer">
                                Thay thế
                            </div>
                            <div id="remove-button-file-<?= $name ?>" class="text-gray-600 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

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
<div id="file-manager-modal-<?= $name ?>" class="fixed inset-0 z-[99999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg w-full max-w-7xl !h-[80vh] max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Thư viện Media</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
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
$dataconfig  = [
    'fieldId' => $name,
    'multi' => $multiple,
    'value' => $value,
    'type'  => 'file',
    'extensions' => $allow_types,
];
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    initStorage(<?= json_encode($dataconfig) ?>);
});
</script>
