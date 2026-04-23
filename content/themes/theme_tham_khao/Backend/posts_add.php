<?php

namespace System\Libraries;

use System\Libraries\Render;
use System\Libraries\Session;

global $me_info;
$current_user = $me_info['id'];
$loadtinymce = false;
$languages = isset($posttype['languages']) ? _json_decode($posttype['languages']) : [];
if (!empty($posttype['fields'])) {
    $configFile = config('files', 'Uploads');
    foreach ($posttype['fields'] as $key => $field) {
        if ($field['type'] == 'File' || $field['type'] == 'Image') {
            $fieldMaxSize = 0;
            if (isset($field['max_file_size']) && $field['max_file_size'] > 0) {
                $fieldMaxSize = (int)(1024 * 1024 * (float)$field['max_file_size']);
            }
            if (!empty($configFile)) {
                if ($fieldMaxSize == 0) {
                    $fieldMaxSize = 1024 * 1024 * 10000; //10GB
                }
                if ($field['type'] == 'File' && isset($configFile['max_file_size']) && $configFile['max_file_size'] > 0) {
                    $fieldMaxSize = min($fieldMaxSize, (int)$configFile['max_file_size']);
                } elseif ($field['type'] == 'Image' && isset($configFile['max_images_size']) && $configFile['max_images_size'] > 0) {
                    $fieldMaxSize = min($fieldMaxSize, (int)$configFile['max_images_size']);
                    if (isset($configFile['max_file_size']) && $configFile['max_file_size'] > 0) {
                        $fieldMaxSize = min($fieldMaxSize, (int)$configFile['max_file_size']);
                    }
                }
            }
            $posttype['fields'][$key]['max_file_size'] = $fieldMaxSize;
        } elseif ($field['type'] == 'WYSIWYG') {
            $loadtinymce = true;
        }
    }
}
$posttype_encode = json_encode($posttype, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
if (!empty($post)) {
    $post_encode = json_encode($post, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
} else {
    $post_encode = '[]';
}
// Lấy danh sách ngôn ngữ từ config
$type = S_GET('type') ?? '';
$isEdit = !empty($post);
$currentLang = S_GET('post_lang') ?? APP_LANG_DF;
if ($isEdit) {
    $created_at = $post['created_at'] ?? '';
} else {
    $created_at = '';
}
$postStatuses = [
    'active'   => __('Active'),
    'pending'  => __('Pending'),
    'inactive' => __('InActive'),
    'schedule' => __('Schedule'),
    'draft'    => __('Draft'),
    'deleted'  => __('Delete'),
];
$selectedStatus = $post['status'] ?? 'active';
// Breadcrumbs
$breadcrumbs = array(
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home')
    ],
    [
        'name' => __('Posts'),
        'url' => admin_url('posts')
    ],
    [
        'name' => __($posttype['name']),
        'url' => admin_url('posts/?type=' . $posttype['slug'] . '&post_lang=' . $currentLang)
    ],
    [
        'name' => $isEdit ? __('Edit') : __('Add') . ' ' . $posttype['name'],
        'url' => admin_url('posts/add'),
        'active' => true
    ]
);


// languageData sẽ là [ 'code' => 'vi', 't' => { 'title' => 'Tiêu đề', 'description' => 'Mô tả' } ]
$langFile = PATH_APP . 'Languages/' . APP_LANG . '/Backend/Acfforms.php';
if (file_exists($langFile)) {
    $translate = include $langFile;
} else {
    $translate = [];
}
$languageData = [
    'code' => APP_LANG,
    't' => $translate
];

if ($loadtinymce) {
    Render::asset('js', 'tinymce/tinymce.min.js', ['area' => 'backend', 'location' => 'head']);
}

// [1] LẤY CÁC THÔNG TIN CHUNG
Render::block('Backend\Header', ['layout' => 'default', 'title' => $isEdit ? __('Edit') : __('Add'), 'breadcrumb' => $breadcrumbs]);
?>

<div class="pc-container">
    <div class="pc-content relative">

        <!-- Header & Description -->
        <div class="flex flex-col gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground"><?= $isEdit ? __('Edit') : __('Add') . ' ' . $posttype['name'] ?></h1>
                <p class="text-muted-foreground"><?= $isEdit ? __('Edit') : __('Add') . ' ' . $posttype['name'] ?></p>
            </div>

            <!-- Thông báo -->
            <?php if (Session::has_flash('success')): ?>
                <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
            <?php endif; ?>
            <?php if (Session::has_flash('error')): ?>
                <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
            <?php endif; ?>

            <!-- Validation Errors -->
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="bg-destructive/10 border border-destructive/20 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-destructive flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-destructive mb-2"><?= __('Please fix the following errors') ?>:</h3>
                            <ul class="space-y-1">
                                <?php foreach ($errors as $field => $fieldErrors): ?>
                                    <?php if (is_array($fieldErrors)): ?>
                                        <?php foreach ($fieldErrors as $error): ?>
                                            <li class="text-sm text-destructive/80 flex items-start gap-2">
                                                <span class="text-destructive">•</span>
                                                <span><strong><?= ucfirst(str_replace('_', ' ', $field)) ?>:</strong> <?= __($error) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="text-sm text-destructive/80 flex items-start gap-2">
                                            <span class="text-destructive">•</span>
                                            <span><?= __($fieldErrors) ?></span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- PHP sẽ tạo form wrapper này -->
        <form id="acf-form" method="post" action="" enctype="multipart/form-data">
            <!-- PHP sẽ thêm các hidden fields cần thiết -->
            <input type="hidden" name="post_id" value="" />
            <input type="hidden" name="type" value="<?= S_GET('type') ?? '' ?>" />
            <!-- <input type="hidden" name="lang" value="<?= $currentLang ?>" /> -->

            <!-- POST CONTROLS BAR -->
            <div class="bg-card rounded-xl mb-4 border">
                <!-- LANGUAGE SWITCHER -->
                <div class="flex flex-wrap items-center gap-2 p-4 pb-3">
                    <?php foreach ($languages as $lang):
                        $isCurrentLang = ($lang === $currentLang);

                        // Determine URL and action type
                        if ($isEdit) {
                            // Check if post exists in this language
                            $langHasThisPost = in_array($lang, $langHasPost ?? []);

                            if ($langHasThisPost) {
                                // Post exists → EDIT link
                                $langUrlAction = admin_url('posts/edit/' . $post['id'])
                                    . '?type=' . $type
                                    . '&post_lang=' . $lang;
                                $actionIcon = 'edit';
                            } else {
                                // Post doesn't exist → CLONE link
                                $langUrlAction = admin_url('posts/clone/' . $post['id'])
                                    . '?type=' . $type
                                    . '&post_lang=' . $lang
                                    . '&oldpost_lang=' . $currentLang;
                                $actionIcon = 'copy-plus';
                            }
                        } else {
                            // Not editing → ADD link for all languages
                            $langUrlAction = admin_url('posts/add')
                                . '?type=' . $type
                                . '&post_lang=' . $lang;
                            $actionIcon = 'plus';
                        }

                        $langClass = $isCurrentLang
                            ? 'inline-flex items-center px-3 py-2 rounded-md bg-primary text-primary-foreground shadow-sm'
                            : 'inline-flex items-center px-3 py-2 rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors';
                    ?>
                        <?php if ($isCurrentLang): ?>
                            <div class="<?= $langClass ?>">
                                <span class="text-sm font-medium uppercase"><?= $lang ?></span>
                                <span class="ml-2">
                                    <i data-lucide="check-circle" class="h-4 w-4"></i>
                                </span>
                            </div>
                        <?php else: ?>
                            <a href="<?= $langUrlAction; ?>" class="<?= $langClass ?>">
                                <span class="text-sm font-medium uppercase"><?= $lang ?></span>
                                <span class="ml-2">
                                    <i data-lucide="<?= $actionIcon ?>" class="h-4 w-4"></i>
                                </span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="flex flex-col gap-4 border-t border-border/60 p-4">
                    <div class="grid gap-4 md:grid-cols-2 md:items-end">
                        <!-- STATUS & TIME SECTION -->
                        <div class="flex items-center gap-3 w-full justify-between md:justify-start md:col-span-1">
                            <?php if ($isEdit && array_key_exists('created_at', $post)): ?>
                                <div class="flex flex-col gap-2 w-full  md:max-w-[200px]">
                                    <label for="post_created_at" class="text-sm font-medium text-muted-foreground whitespace-nowrap"><?= __('Created at') ?>:</label>
                                    <input
                                        id="post_created_at"
                                        name="created_at"
                                        type="text"
                                        placeholder="<?= __('Select time publish') ?>"
                                        class="flex h-10 w-full md:max-w-[200px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 min-w-[180px]"
                                        value="<?= $created_at ?? '' ?>" />
                                </div>
                            <?php endif; ?>
                            <?php if ($isEdit && array_key_exists('status', $post)): ?>
                                <div class="flex flex-col gap-2 w-full  md:max-w-[200px]">
                                    <label for="post-status" class="text-sm font-medium text-muted-foreground whitespace-nowrap"><?= __('Post status') ?>:</label>
                                    <select
                                        id="post-status"
                                        name="status"
                                        class="flex h-10 w-full md:max-w-[200px] items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 min-w-[160px]">
                                        <?php foreach ($postStatuses as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $value === $selectedStatus ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- ACTION BUTTONS -->
                        <div class="flex items-center gap-3 w-full justify-between md:justify-end md:col-span-1">
                            <div class="flex flex-col gap-2 w-full  md:max-w-[200px]">
                                <button
                                    type="submit"
                                    id="publish-btn"
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                    <i data-lucide="send" class="h-4 w-4 mr-2"></i>
                                    <?= __('Publish') ?>
                                </button>
                            </div>
                            <div class="flex flex-col gap-2 w-full  md:max-w-[200px]">
                                <button
                                    type="button"
                                    id="save-draft-btn"
                                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                                    <i data-lucide="file-text" class="h-4 w-4 mr-2"></i>
                                    <?= __('Save draft') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                /* Custom styles for better spacing */
                .field-wrapper {
                    margin-bottom: 1rem;
                }

                .field-wrapper:last-child {
                    margin-bottom: 0;
                }

                /* Custom styles for better spacing */
                .field-wrapper {
                    margin-bottom: 1rem;
                }

                .field-wrapper:last-child {
                    margin-bottom: 0;
                }

                /* Editor.js styles */
                .codex-editor {
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                }

                .codex-editor__redactor {
                    padding: 12px;
                }

                /* Custom scrollbar */
                ::-webkit-scrollbar {
                    width: 6px;
                }

                ::-webkit-scrollbar-track {
                    background: #f1f1f1;
                }

                ::-webkit-scrollbar-thumb {
                    background: #c1c1c1;
                    border-radius: 3px;
                }

                ::-webkit-scrollbar-thumb:hover {
                    background: #a8a8a8;
                }

                /* Loading spinner */
                .loading-spinner {
                    display: inline-block;
                    width: 20px;
                    height: 20px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
                }

                /* DND Kit specific styles */
                .repeater-item {
                    transition: all 0.2s ease;
                }

                .repeater-item:hover {
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                }

                .repeater-handle {
                    cursor: grab;
                }

                .repeater-handle:active {
                    cursor: grabbing;
                }

                .flexible-layout-selector {
                    max-height: 300px;
                    overflow-y: auto;
                }

                /* DND Kit drag overlay styles */
                .dnd-overlay {
                    opacity: 0.8;
                    transform: rotate(5deg);
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                }
            </style>
            <script type="module" crossorigin src="<?= theme_assets('js/posts_add.js', 'Backend') ?>"></script>
            <link rel="stylesheet" crossorigin href="<?= theme_assets('css/posts_add.css', 'Backend') ?>">

            <style>
                .ce-block__content {
                    max-width: inherit;
                }

                /* Form actions đã được chuyển lên POST CONTROLS BAR */
                .flatpickr-now-button {
                    position: absolute;
                    top: 4px;
                    right: 4px;
                    background: #3b82f6;
                    color: white;
                    border: none;
                    padding: 3px 8px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    font-weight: 500;
                    z-index: 1000;
                    transition: background-color 0.2s;
                }

                .flatpickr-now-button:hover {
                    background: #2563eb;
                }
            </style>
            <!-- React app container - Chỉ render fields -->
            <div id="initial-loading" style="display: flex; justify-content: center; align-items: center; height: 200px; flex-direction: column;">
                <div class="loading-spinner"></div>
                <p style="margin-top: 10px; color: #666;"><?= __('Loading ACF Form Builder...') ?></p>
                <p style="margin-top: 5px; color: #999; font-size: 12px;" id="initial-loading-content"><?= __('Initializing drag & drop components...') ?></p>
            </div>
            <div id="root">
                <!-- Initial loading state -->

            </div>

            <!-- Form actions đã được chuyển lên POST CONTROLS BAR -->
        </form>
        <!-- PHP sẽ inject data vào đây -->
        <script>
            // PHP sẽ truyền data vào window object
            window.ACF_DATA = {
                "BASE_URL": "<?= base_url() ?>",
                "PUBLIC_URL": "<?= base_url() ?>",
                "lang": "<?= $currentLang ?>",
                "FILES_URL": "<?= base_url(config('files', 'Uploads')['files_url'] ?? 'content/uploads') ?>",
                "APP_LANG": "<?= APP_LANG ?>",
                "ADMIN_URL": "<?= admin_url() ?>",
                "current_user": <?= $current_user; ?>,
                "postType": <?= $posttype_encode; ?>,
                "postEdit": <?= $post_encode; ?>,
                "languageData": [<?= json_encode($languageData); ?>]
            };
        </script>

        <style type="text/css">
            .flatpickr-calendar {
                background: transparent;
                opacity: 0;
                display: none;
                text-align: center;
                visibility: hidden;
                padding: 0;
                -webkit-animation: none;
                animation: none;
                direction: ltr;
                border: 0;
                font-size: 14px;
                line-height: 24px;
                border-radius: 5px;
                position: absolute;
                width: 307.875px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                -ms-touch-action: manipulation;
                touch-action: manipulation;
                background: #fff;
                -webkit-box-shadow: 1px 0 0 #e6e6e6, -1px 0 0 #e6e6e6, 0 1px 0 #e6e6e6, 0 -1px 0 #e6e6e6, 0 3px 13px rgba(0, 0, 0, 0.08);
                box-shadow: 1px 0 0 #e6e6e6, -1px 0 0 #e6e6e6, 0 1px 0 #e6e6e6, 0 -1px 0 #e6e6e6, 0 3px 13px rgba(0, 0, 0, 0.08)
            }

            .flatpickr-calendar.open,
            .flatpickr-calendar.inline {
                opacity: 1;
                max-height: 640px;
                visibility: visible
            }

            .flatpickr-calendar.open {
                display: inline-block;
                z-index: 99999
            }

            .flatpickr-calendar.animate.open {
                -webkit-animation: fpFadeInDown 300ms cubic-bezier(.23, 1, .32, 1);
                animation: fpFadeInDown 300ms cubic-bezier(.23, 1, .32, 1)
            }

            .flatpickr-calendar.inline {
                display: block;
                position: relative;
                top: 2px
            }

            .flatpickr-calendar.static {
                position: absolute;
                top: calc(100% + 2px)
            }

            .flatpickr-calendar.static.open {
                z-index: 999;
                display: block
            }

            .flatpickr-calendar.multiMonth .flatpickr-days .dayContainer:nth-child(n+1) .flatpickr-day.inRange:nth-child(7n+7) {
                -webkit-box-shadow: none !important;
                box-shadow: none !important
            }

            .flatpickr-calendar.multiMonth .flatpickr-days .dayContainer:nth-child(n+2) .flatpickr-day.inRange:nth-child(7n+1) {
                -webkit-box-shadow: -2px 0 0 #e6e6e6, 5px 0 0 #e6e6e6;
                box-shadow: -2px 0 0 #e6e6e6, 5px 0 0 #e6e6e6
            }

            .flatpickr-calendar .hasWeeks .dayContainer,
            .flatpickr-calendar .hasTime .dayContainer {
                border-bottom: 0;
                border-bottom-right-radius: 0;
                border-bottom-left-radius: 0
            }

            .flatpickr-calendar .hasWeeks .dayContainer {
                border-left: 0
            }

            .flatpickr-calendar.hasTime .flatpickr-time {
                height: 40px;
                border-top: 1px solid #e6e6e6
            }

            .flatpickr-calendar.noCalendar.hasTime .flatpickr-time {
                height: auto
            }

            .flatpickr-calendar:before,
            .flatpickr-calendar:after {
                position: absolute;
                display: block;
                pointer-events: none;
                border: solid transparent;
                content: '';
                height: 0;
                width: 0;
                left: 22px
            }

            .flatpickr-calendar.rightMost:before,
            .flatpickr-calendar.arrowRight:before,
            .flatpickr-calendar.rightMost:after,
            .flatpickr-calendar.arrowRight:after {
                left: auto;
                right: 22px
            }

            .flatpickr-calendar.arrowCenter:before,
            .flatpickr-calendar.arrowCenter:after {
                left: 50%;
                right: 50%
            }

            .flatpickr-calendar:before {
                border-width: 5px;
                margin: 0 -5px
            }

            .flatpickr-calendar:after {
                border-width: 4px;
                margin: 0 -4px
            }

            .flatpickr-calendar.arrowTop:before,
            .flatpickr-calendar.arrowTop:after {
                bottom: 100%
            }

            .flatpickr-calendar.arrowTop:before {
                border-bottom-color: #e6e6e6
            }

            .flatpickr-calendar.arrowTop:after {
                border-bottom-color: #fff
            }

            .flatpickr-calendar.arrowBottom:before,
            .flatpickr-calendar.arrowBottom:after {
                top: 100%
            }

            .flatpickr-calendar.arrowBottom:before {
                border-top-color: #e6e6e6
            }

            .flatpickr-calendar.arrowBottom:after {
                border-top-color: #fff
            }

            .flatpickr-calendar:focus {
                outline: 0
            }

            .flatpickr-wrapper {
                position: relative;
                display: inline-block
            }

            .flatpickr-months {
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex
            }

            .flatpickr-months .flatpickr-month {
                background: transparent;
                color: rgba(0, 0, 0, 0.9);
                fill: rgba(0, 0, 0, 0.9);
                height: 34px;
                line-height: 1;
                text-align: center;
                position: relative;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                overflow: hidden;
                -webkit-box-flex: 1;
                -webkit-flex: 1;
                -ms-flex: 1;
                flex: 1
            }

            .flatpickr-months .flatpickr-prev-month,
            .flatpickr-months .flatpickr-next-month {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                text-decoration: none;
                cursor: pointer;
                position: absolute;
                top: 0;
                height: 34px;
                padding: 10px;
                z-index: 3;
                color: rgba(0, 0, 0, 0.9);
                fill: rgba(0, 0, 0, 0.9)
            }

            .flatpickr-months .flatpickr-prev-month.flatpickr-disabled,
            .flatpickr-months .flatpickr-next-month.flatpickr-disabled {
                display: none
            }

            .flatpickr-months .flatpickr-prev-month i,
            .flatpickr-months .flatpickr-next-month i {
                position: relative
            }

            .flatpickr-months .flatpickr-prev-month.flatpickr-prev-month,
            .flatpickr-months .flatpickr-next-month.flatpickr-prev-month {
                /*
      /*rtl:begin:ignore*/
                left: 0
                    /*
      /*rtl:end:ignore*/
            }

            /*
      /*rtl:begin:ignore*/
            /*
      /*rtl:end:ignore*/
            .flatpickr-months .flatpickr-prev-month.flatpickr-next-month,
            .flatpickr-months .flatpickr-next-month.flatpickr-next-month {
                /*
      /*rtl:begin:ignore*/
                right: 0
                    /*
      /*rtl:end:ignore*/
            }

            /*
      /*rtl:begin:ignore*/
            /*
      /*rtl:end:ignore*/
            .flatpickr-months .flatpickr-prev-month:hover,
            .flatpickr-months .flatpickr-next-month:hover {
                color: #959ea9
            }

            .flatpickr-months .flatpickr-prev-month:hover svg,
            .flatpickr-months .flatpickr-next-month:hover svg {
                fill: #f64747
            }

            .flatpickr-months .flatpickr-prev-month svg,
            .flatpickr-months .flatpickr-next-month svg {
                width: 14px;
                height: 14px
            }

            .flatpickr-months .flatpickr-prev-month svg path,
            .flatpickr-months .flatpickr-next-month svg path {
                -webkit-transition: fill .1s;
                transition: fill .1s;
                fill: inherit
            }

            .numInputWrapper {
                position: relative;
                height: auto
            }

            .numInputWrapper input,
            .numInputWrapper span {
                display: inline-block
            }

            .numInputWrapper input {
                width: 100%
            }

            .numInputWrapper input::-ms-clear {
                display: none
            }

            .numInputWrapper input::-webkit-outer-spin-button,
            .numInputWrapper input::-webkit-inner-spin-button {
                margin: 0;
                -webkit-appearance: none
            }

            .numInputWrapper span {
                position: absolute;
                right: 0;
                width: 14px;
                padding: 0 4px 0 2px;
                height: 50%;
                line-height: 50%;
                opacity: 0;
                cursor: pointer;
                border: 1px solid rgba(57, 57, 57, 0.15);
                -webkit-box-sizing: border-box;
                box-sizing: border-box
            }

            .numInputWrapper span:hover {
                background: rgba(0, 0, 0, 0.1)
            }

            .numInputWrapper span:active {
                background: rgba(0, 0, 0, 0.2)
            }

            .numInputWrapper span:after {
                display: block;
                content: "";
                position: absolute
            }

            .numInputWrapper span.arrowUp {
                top: 0;
                border-bottom: 0
            }

            .numInputWrapper span.arrowUp:after {
                border-left: 4px solid transparent;
                border-right: 4px solid transparent;
                border-bottom: 4px solid rgba(57, 57, 57, 0.6);
                top: 26%
            }

            .numInputWrapper span.arrowDown {
                top: 50%
            }

            .numInputWrapper span.arrowDown:after {
                border-left: 4px solid transparent;
                border-right: 4px solid transparent;
                border-top: 4px solid rgba(57, 57, 57, 0.6);
                top: 40%
            }

            .numInputWrapper span svg {
                width: inherit;
                height: auto
            }

            .numInputWrapper span svg path {
                fill: rgba(0, 0, 0, 0.5)
            }

            .numInputWrapper:hover {
                background: rgba(0, 0, 0, 0.05)
            }

            .numInputWrapper:hover span {
                opacity: 1
            }

            .flatpickr-current-month {
                font-size: 135%;
                line-height: inherit;
                font-weight: 300;
                color: inherit;
                position: absolute;
                width: 75%;
                left: 12.5%;
                padding: 7.48px 0 0 0;
                line-height: 1;
                height: 34px;
                display: inline-block;
                text-align: center;
                -webkit-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0)
            }

            .flatpickr-current-month span.cur-month {
                font-family: inherit;
                font-weight: 700;
                color: inherit;
                display: inline-block;
                margin-left: .5ch;
                padding: 0
            }

            .flatpickr-current-month span.cur-month:hover {
                background: rgba(0, 0, 0, 0.05)
            }

            .flatpickr-current-month .numInputWrapper {
                width: 6ch;
                width: 7ch\0;
                display: inline-block
            }

            .flatpickr-current-month .numInputWrapper span.arrowUp:after {
                border-bottom-color: rgba(0, 0, 0, 0.9)
            }

            .flatpickr-current-month .numInputWrapper span.arrowDown:after {
                border-top-color: rgba(0, 0, 0, 0.9)
            }

            .flatpickr-current-month input.cur-year {
                background: transparent;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                color: inherit;
                cursor: text;
                padding: 0 0 0 .5ch;
                margin: 0;
                display: inline-block;
                font-size: inherit;
                font-family: inherit;
                font-weight: 300;
                line-height: inherit;
                height: auto;
                border: 0;
                border-radius: 0;
                vertical-align: initial;
                -webkit-appearance: textfield;
                -moz-appearance: textfield;
                appearance: textfield
            }

            .flatpickr-current-month input.cur-year:focus {
                outline: 0
            }

            .flatpickr-current-month input.cur-year[disabled],
            .flatpickr-current-month input.cur-year[disabled]:hover {
                font-size: 100%;
                color: rgba(0, 0, 0, 0.5);
                background: transparent;
                pointer-events: none
            }

            .flatpickr-current-month .flatpickr-monthDropdown-months {
                appearance: menulist;
                background: transparent;
                border: none;
                border-radius: 0;
                box-sizing: border-box;
                color: inherit;
                cursor: pointer;
                font-size: inherit;
                font-family: inherit;
                font-weight: 300;
                height: auto;
                line-height: inherit;
                margin: -1px 0 0 0;
                outline: none;
                padding: 0 0 0 .5ch;
                position: relative;
                vertical-align: initial;
                -webkit-box-sizing: border-box;
                -webkit-appearance: menulist;
                -moz-appearance: menulist;
                width: auto
            }

            .flatpickr-current-month .flatpickr-monthDropdown-months:focus,
            .flatpickr-current-month .flatpickr-monthDropdown-months:active {
                outline: none
            }

            .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
                background: rgba(0, 0, 0, 0.05)
            }

            .flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
                background-color: transparent;
                outline: none;
                padding: 0
            }

            .flatpickr-weekdays {
                background: transparent;
                text-align: center;
                overflow: hidden;
                width: 100%;
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -webkit-align-items: center;
                -ms-flex-align: center;
                align-items: center;
                height: 28px
            }

            .flatpickr-weekdays .flatpickr-weekdaycontainer {
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-flex: 1;
                -webkit-flex: 1;
                -ms-flex: 1;
                flex: 1
            }

            span.flatpickr-weekday {
                cursor: default;
                font-size: 90%;
                background: transparent;
                color: rgba(0, 0, 0, 0.54);
                line-height: 1;
                margin: 0;
                text-align: center;
                display: block;
                -webkit-box-flex: 1;
                -webkit-flex: 1;
                -ms-flex: 1;
                flex: 1;
                font-weight: bolder
            }

            .dayContainer,
            .flatpickr-weeks {
                padding: 1px 0 0 0
            }

            .flatpickr-days {
                position: relative;
                overflow: hidden;
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: start;
                -webkit-align-items: flex-start;
                -ms-flex-align: start;
                align-items: flex-start;
                width: 307.875px
            }

            .flatpickr-days:focus {
                outline: 0
            }

            .dayContainer {
                padding: 0;
                outline: 0;
                text-align: left;
                width: 307.875px;
                min-width: 307.875px;
                max-width: 307.875px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                display: inline-block;
                display: -ms-flexbox;
                display: -webkit-box;
                display: -webkit-flex;
                display: flex;
                -webkit-flex-wrap: wrap;
                flex-wrap: wrap;
                -ms-flex-wrap: wrap;
                -ms-flex-pack: justify;
                -webkit-justify-content: space-around;
                justify-content: space-around;
                -webkit-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0);
                opacity: 1
            }

            .dayContainer+.dayContainer {
                -webkit-box-shadow: -1px 0 0 #e6e6e6;
                box-shadow: -1px 0 0 #e6e6e6
            }

            .flatpickr-day {
                background: none;
                border: 1px solid transparent;
                border-radius: 150px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                color: #393939;
                cursor: pointer;
                font-weight: 400;
                width: 14.2857143%;
                -webkit-flex-basis: 14.2857143%;
                -ms-flex-preferred-size: 14.2857143%;
                flex-basis: 14.2857143%;
                max-width: 39px;
                height: 39px;
                line-height: 39px;
                margin: 0;
                display: inline-block;
                position: relative;
                -webkit-box-pack: center;
                -webkit-justify-content: center;
                -ms-flex-pack: center;
                justify-content: center;
                text-align: center
            }

            .flatpickr-day.inRange,
            .flatpickr-day.prevMonthDay.inRange,
            .flatpickr-day.nextMonthDay.inRange,
            .flatpickr-day.today.inRange,
            .flatpickr-day.prevMonthDay.today.inRange,
            .flatpickr-day.nextMonthDay.today.inRange,
            .flatpickr-day:hover,
            .flatpickr-day.prevMonthDay:hover,
            .flatpickr-day.nextMonthDay:hover,
            .flatpickr-day:focus,
            .flatpickr-day.prevMonthDay:focus,
            .flatpickr-day.nextMonthDay:focus {
                cursor: pointer;
                outline: 0;
                background: #e6e6e6;
                border-color: #e6e6e6
            }

            .flatpickr-day.today {
                border-color: #959ea9
            }

            .flatpickr-day.today:hover,
            .flatpickr-day.today:focus {
                border-color: #959ea9;
                background: #959ea9;
                color: #fff
            }

            .flatpickr-day.selected,
            .flatpickr-day.startRange,
            .flatpickr-day.endRange,
            .flatpickr-day.selected.inRange,
            .flatpickr-day.startRange.inRange,
            .flatpickr-day.endRange.inRange,
            .flatpickr-day.selected:focus,
            .flatpickr-day.startRange:focus,
            .flatpickr-day.endRange:focus,
            .flatpickr-day.selected:hover,
            .flatpickr-day.startRange:hover,
            .flatpickr-day.endRange:hover,
            .flatpickr-day.selected.prevMonthDay,
            .flatpickr-day.startRange.prevMonthDay,
            .flatpickr-day.endRange.prevMonthDay,
            .flatpickr-day.selected.nextMonthDay,
            .flatpickr-day.startRange.nextMonthDay,
            .flatpickr-day.endRange.nextMonthDay {
                background: #569ff7;
                -webkit-box-shadow: none;
                box-shadow: none;
                color: #fff;
                border-color: #569ff7
            }

            .flatpickr-day.selected.startRange,
            .flatpickr-day.startRange.startRange,
            .flatpickr-day.endRange.startRange {
                border-radius: 50px 0 0 50px
            }

            .flatpickr-day.selected.endRange,
            .flatpickr-day.startRange.endRange,
            .flatpickr-day.endRange.endRange {
                border-radius: 0 50px 50px 0
            }

            .flatpickr-day.selected.startRange+.endRange:not(:nth-child(7n+1)),
            .flatpickr-day.startRange.startRange+.endRange:not(:nth-child(7n+1)),
            .flatpickr-day.endRange.startRange+.endRange:not(:nth-child(7n+1)) {
                -webkit-box-shadow: -10px 0 0 #569ff7;
                box-shadow: -10px 0 0 #569ff7
            }

            .flatpickr-day.selected.startRange.endRange,
            .flatpickr-day.startRange.startRange.endRange,
            .flatpickr-day.endRange.startRange.endRange {
                border-radius: 50px
            }

            .flatpickr-day.inRange {
                border-radius: 0;
                -webkit-box-shadow: -5px 0 0 #e6e6e6, 5px 0 0 #e6e6e6;
                box-shadow: -5px 0 0 #e6e6e6, 5px 0 0 #e6e6e6
            }

            .flatpickr-day.flatpickr-disabled,
            .flatpickr-day.flatpickr-disabled:hover,
            .flatpickr-day.prevMonthDay,
            .flatpickr-day.nextMonthDay,
            .flatpickr-day.notAllowed,
            .flatpickr-day.notAllowed.prevMonthDay,
            .flatpickr-day.notAllowed.nextMonthDay {
                color: rgba(57, 57, 57, 0.3);
                background: transparent;
                border-color: transparent;
                cursor: default
            }

            .flatpickr-day.flatpickr-disabled,
            .flatpickr-day.flatpickr-disabled:hover {
                cursor: not-allowed;
                color: rgba(57, 57, 57, 0.1)
            }

            .flatpickr-day.week.selected {
                border-radius: 0;
                -webkit-box-shadow: -5px 0 0 #569ff7, 5px 0 0 #569ff7;
                box-shadow: -5px 0 0 #569ff7, 5px 0 0 #569ff7
            }

            .flatpickr-day.hidden {
                visibility: hidden
            }

            .rangeMode .flatpickr-day {
                margin-top: 1px
            }

            .flatpickr-weekwrapper {
                float: left
            }

            .flatpickr-weekwrapper .flatpickr-weeks {
                padding: 0 12px;
                -webkit-box-shadow: 1px 0 0 #e6e6e6;
                box-shadow: 1px 0 0 #e6e6e6
            }

            .flatpickr-weekwrapper .flatpickr-weekday {
                float: none;
                width: 100%;
                line-height: 28px
            }

            .flatpickr-weekwrapper span.flatpickr-day,
            .flatpickr-weekwrapper span.flatpickr-day:hover {
                display: block;
                width: 100%;
                max-width: none;
                color: rgba(57, 57, 57, 0.3);
                background: transparent;
                cursor: default;
                border: none
            }

            .flatpickr-innerContainer {
                display: block;
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                overflow: hidden
            }

            .flatpickr-rContainer {
                display: inline-block;
                padding: 0;
                -webkit-box-sizing: border-box;
                box-sizing: border-box
            }

            .flatpickr-time {
                text-align: center;
                outline: 0;
                display: block;
                height: 0;
                line-height: 40px;
                max-height: 40px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                overflow: hidden;
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex
            }

            .flatpickr-time:after {
                content: "";
                display: table;
                clear: both
            }

            .flatpickr-time .numInputWrapper {
                -webkit-box-flex: 1;
                -webkit-flex: 1;
                -ms-flex: 1;
                flex: 1;
                width: 40%;
                height: 40px;
                float: left
            }

            .flatpickr-time .numInputWrapper span.arrowUp:after {
                border-bottom-color: #393939
            }

            .flatpickr-time .numInputWrapper span.arrowDown:after {
                border-top-color: #393939
            }

            .flatpickr-time.hasSeconds .numInputWrapper {
                width: 26%
            }

            .flatpickr-time.time24hr .numInputWrapper {
                width: 49%
            }

            .flatpickr-time input {
                background: transparent;
                -webkit-box-shadow: none;
                box-shadow: none;
                border: 0;
                border-radius: 0;
                text-align: center;
                margin: 0;
                padding: 0;
                height: inherit;
                line-height: inherit;
                color: #393939;
                font-size: 14px;
                position: relative;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                -webkit-appearance: textfield;
                -moz-appearance: textfield;
                appearance: textfield
            }

            .flatpickr-time input.flatpickr-hour {
                font-weight: bold
            }

            .flatpickr-time input.flatpickr-minute,
            .flatpickr-time input.flatpickr-second {
                font-weight: 400
            }

            .flatpickr-time input:focus {
                outline: 0;
                border: 0
            }

            .flatpickr-time .flatpickr-time-separator,
            .flatpickr-time .flatpickr-am-pm {
                height: inherit;
                float: left;
                line-height: inherit;
                color: #393939;
                font-weight: bold;
                width: 2%;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                -webkit-align-self: center;
                -ms-flex-item-align: center;
                align-self: center
            }

            .flatpickr-time .flatpickr-am-pm {
                outline: 0;
                width: 18%;
                cursor: pointer;
                text-align: center;
                font-weight: 400
            }

            .flatpickr-time input:hover,
            .flatpickr-time .flatpickr-am-pm:hover,
            .flatpickr-time input:focus,
            .flatpickr-time .flatpickr-am-pm:focus {
                background: #eee
            }

            .flatpickr-input[readonly] {
                cursor: pointer
            }

            @-webkit-keyframes fpFadeInDown {
                from {
                    opacity: 0;
                    -webkit-transform: translate3d(0, -20px, 0);
                    transform: translate3d(0, -20px, 0)
                }

                to {
                    opacity: 1;
                    -webkit-transform: translate3d(0, 0, 0);
                    transform: translate3d(0, 0, 0)
                }
            }

            @keyframes fpFadeInDown {
                from {
                    opacity: 0;
                    -webkit-transform: translate3d(0, -20px, 0);
                    transform: translate3d(0, -20px, 0)
                }

                to {
                    opacity: 1;
                    -webkit-transform: translate3d(0, 0, 0);
                    transform: translate3d(0, 0, 0)
                }
            }
        </style>


        <script>
            // Initialize Flatpickr for datetime inputs
            document.addEventListener('DOMContentLoaded', function() {
                // Wait a bit for flatpickr to load if not immediately available
                if (typeof flatpickr !== 'undefined') {
                    // Created At datetime picker
                    const createdAtInput = document.getElementById('post_created_at');

                    if (createdAtInput) {
                        try {
                            flatpickr(createdAtInput, {
                                enableTime: true,
                                enableSeconds: true,
                                time_24hr: true,
                                dateFormat: 'Y-m-d H:i:s',
                                allowInput: true,
                                placeholder: 'Select date and time',
                                static: false,
                                // Thêm nút "Now" để chọn ngày giờ hiện tại
                                plugins: [
                                    function(instance) {
                                        return {
                                            onReady: function() {
                                                const nowButton = document.createElement('button');
                                                nowButton.type = 'button';
                                                nowButton.textContent = 'Now';
                                                nowButton.className = 'flatpickr-now-button';
                                                nowButton.style.cssText = `
                                            position: absolute;
                                            top: 4px;
                                            right: 4px;
                                            background: #3b82f6;
                                            color: white;
                                            border: none;
                                            padding: 3px 8px;
                                            border-radius: 4px;
                                            cursor: pointer;
                                            font-size: 12px;
                                            font-weight: 500;
                                            z-index: 1000;
                                        `;

                                                nowButton.addEventListener('click', function() {
                                                    const now = new Date();
                                                    instance.setDate(now, true);
                                                    instance.close();
                                                });

                                                instance.calendarContainer.appendChild(nowButton);
                                            }
                                        };
                                    }
                                ]
                            });
                            console.log('Flatpickr initialized successfully with Now button');
                        } catch (error) {
                            console.error('Error initializing flatpickr:', error);
                        }
                    }
                }

                // Fix: Remove Flatpickr's hidden inputs before form submit
                // This prevents "An invalid form control with name='' is not focusable" error
                const acfForm = document.getElementById('acf-form');
                if (acfForm) {
                    acfForm.addEventListener('submit', function(e) {
                        // Remove all Flatpickr's numInput elements
                        const numInputs = acfForm.querySelectorAll('.numInput');
                        numInputs.forEach(function(input) {
                            input.remove();
                        });

                        // Also remove any other Flatpickr helper elements
                        const flatpickrElements = acfForm.querySelectorAll('.flatpickr-time-separator, .flatpickr-am-pm');
                        flatpickrElements.forEach(function(element) {
                            element.remove();
                        });

                        console.log('Removed Flatpickr helper inputs before submit');
                    });

                    const saveDraftBtn = document.getElementById('save-draft-btn');
                    const publishBtn = document.getElementById('publish-btn');
                    const statusSelect = document.getElementById('post-status');
                    if (saveDraftBtn && statusSelect) {
                        saveDraftBtn.addEventListener('click', function() {
                            statusSelect.value = 'draft';
                            acfForm.requestSubmit();
                        });
                    }
                    if (publishBtn && statusSelect) {
                        publishBtn.addEventListener('click', function(event) {
                            event.preventDefault();
                            if (statusSelect.value == 'draft') {
                                statusSelect.value = 'active';
                            }
                            acfForm.requestSubmit();
                        });
                    }
                }
            });
        </script>

    </div>
</div>

<?php Render::block('Backend\Footer'); ?>