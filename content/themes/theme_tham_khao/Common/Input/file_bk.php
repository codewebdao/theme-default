<div class="fieldset px-1 w-full md:w-[354px] mb-8 wrap-<?= $name ?> field_file <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label class="block mb-4 font-medium text-sm leading-5">
            <?= $label ?><?= $required ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
    <?php endif; ?>

    <!-- Upload Section -->
    <div id="upload-area" class="flex flex-col items-center justify-center w-full h-48 p-4 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50">
        <div class="text-center cursor-pointer"  id="browse-button">
            <div class="flex justify-center text-gray-400 mb-4">
                <!-- Upload Icon -->
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11 34C6.02944 34 2 29.9706 2 25C2 21.0368 4.56168 17.6719 8.11959 16.4708C8.04092 15.9922 8 15.5009 8 15C8 10.0294 12.0294 6 17 6C20.2808 6 23.1515 7.75543 24.7243 10.3783C25.4377 10.1331 26.2033 10 27 10C30.866 10 34 13.134 34 17C34 17.6667 33.9068 18.3116 33.7327 18.9224C36.2706 20.2636 38 22.9298 38 26C38 30.4183 34.4183 34 30 34H11ZM19.25 28.5C19.25 28.9142 19.5858 29.25 20 29.25C20.4142 29.25 20.75 28.9142 20.75 28.5V17.4099L25.9504 23.0103C26.2323 23.3139 26.7068 23.3314 27.0103 23.0496C27.3139 22.7677 27.3314 22.2932 27.0496 21.9897L20.5496 14.9897C20.4077 14.8368 20.2086 14.75 20 14.75C19.7914 14.75 19.5923 14.8368 19.4504 14.9897L12.9504 21.9897C12.6686 22.2932 12.6861 22.7677 12.9897 23.0496C13.2932 23.3314 13.7677 23.3139 14.0496 23.0103L19.25 17.4099V28.5Z" fill="#6B7280"></path>
                </svg>
            </div>
            <p class=" text-center text-sm font-medium leading-5 mb-2">
                Click to upload or drag and drop your banner or video for your campaign
            </p>
            <p class="text-gray-600 text-center text-xs font-normal leading-4 mb-2">
                Max. File Size: <?= $max_file_size > 0 ? $max_file_size.'MB' : 'Unlimited'; ?>
            </p>
            <div class="inline-block px-4 py-2 text-xs text-white bg-blue-600 rounded-md cursor-pointer">
                Browse File
            </div>
        </div>
        <input
            type="file"
            id="<?= $id ?>"
            name="<?= $name ?><?= $multiple ? '[]' : '' ?>"
            accept="<?= !empty($allow_types) ? implode(',', $allow_types) : '' ?>"
            <?= $required ? 'required' : '' ?>
            <?= $multiple ? 'multiple' : '' ?>
            class="hidden"
        >
    </div>

    <!-- Preview Section (Hidden initially) -->
    <div id="preview-area" class="hidden mt-6">
        <div class="flex items-center justify-between w-full p-4 rounded-lg bg-gray-50">
            <div class="flex items-center">
                <?= _img("", 'Uploaded Image', false, 'w-20 h-20 mr-4 rounded-lg object-cover', '', '', '', 'image-preview')?>
                <div>
                    <p id="image-name" class="font-semibold "></p>
                    <p id="image-size" class="text-gray-500 text-sm"></p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div id="replace-button" class="text-primary text-sm font-medium leading-5 cursor-pointer">
                    Replace
                </div>
                <div id="remove-button" class="text-gray-600 cursor-pointer">
                    <!-- Close Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <?php if ($description): ?>
        <p class="text-gray-500 text-sm mt-1"><?= $description ?></p>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <?php if (is_array($error_message)): ?>
            <?php foreach ($error_message as $error): ?>
                <p class="text-red-500 text-sm mt-1"><?= xss_clean($error) ?></p>
            <?php endforeach; ?>
        <?php elseif (is_string($error_message)): ?>
            <p class="text-red-500 text-sm mt-1"><?= xss_clean($error_message) ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>



<script type="text/javascript">
// if (!loaded_upload_file){
//     var script = document.createElement('script');
//     script.src = '<?= base_url('assets/js/upload-file.js') ?>';
//     document.head.appendChild(script);
//     var loaded_upload_file = true;
// }
document.addEventListener("DOMContentLoaded", function () {
    // Show file input dialog when clicking "Browse File" div
    $("#browse-button").click(function () {
        $("#<?= $id ?>").click();
    });

    // Handle file selection and display preview
    $("#<?= $id ?>").change(function (e) {
        let file = e.target.files[0];
        if (file) {
            let fileReader = new FileReader();
            fileReader.onload = function (event) {
                // Show preview image and file info
                $("#image-preview").attr("src", event.target.result);
                $("#image-name").text(file.name);
                $("#image-size").text((file.size / 1024).toFixed(2) + " KB");

                // Toggle areas
                $("#upload-area").hide();
                $("#preview-area").show();
                if ($("#preview-area").hasClass('hidden')){
                    $("#preview-area").removeClass('hidden');
                }
            };
            fileReader.readAsDataURL(file);
        }
    });

    // Replace image
    $("#replace-button").click(function () {
        $("#<?= $id ?>").click();
    });

    // Remove image and reset the upload area
    $("#remove-button").click(function () {
        $("#<?= $id ?>").val("");
        $("#upload-area").show();
        $("#preview-area").hide();
    });

    // Drag and drop file upload
    $("#upload-area").on("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass("border-blue-500");
    });

    $("#upload-area").on("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("border-blue-500");
    });

    $("#upload-area").on("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("border-blue-500");

        // Check dataTransfer from e.originalEvent, if not available then try to get directly from e
        const dataTransfer = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer : (e.dataTransfer ?? null);
        let file = dataTransfer ? dataTransfer.files[0] : null;

        if (file) {
            let fileReader = new FileReader();
            fileReader.onload = function (event) {
                // Show image preview and file information
                $("#image-preview").attr("src", event.target.result);
                $("#image-name").text(file.name);
                $("#image-size").text((file.size / 1024).toFixed(2) + " KB");

                // Toggle display
                $("#upload-area").hide();
                $("#preview-area").show();
                if ($("#preview-area").hasClass('hidden')){
                    $("#preview-area").removeClass('hidden');
                }
            };
            fileReader.readAsDataURL(file);
        }
    });

});
</script>
