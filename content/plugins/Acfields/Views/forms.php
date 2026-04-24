<?php

use System\Libraries\Render;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Session;

// Language files are loaded globally

if (!empty($allPostTypes)) {
    foreach ($allPostTypes as &$item) {
        $tempFields = _json_decode($item['fields']);
        //remove item key not in [id,type,label,field_name,reference]
        foreach ($tempFields as &$field) {
            $reducedField = [
                'id' => $field['id'],
                'type' => $field['type'],
                'label' => $field['label'],
                'field_name' => $field['field_name']
            ];
            if (isset($field['reference']) && isset($field['reference']['postTypeRef']) && isset($field['reference']['selectionMode']) && !empty($field['reference']['postTypeRef']) && $field['reference']['selectionMode'] === 'single') {
                $reducedField['reference'] = $field['reference'];
            }
            $field = $reducedField;
        }
        // add field có field_name = id, type number, label = ID vào đầu mảng $tempFields
        array_unshift($tempFields, [
            'id' => 0,
            'type' => 'number',
            'label' => 'ID',
            'field_name' => 'id'
        ]);

        //$tempFields = array_values($tempFields);
        $item = [
            'id' => $item['id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'menu' => $item['menu'],
            'status' => $item['status'],
            'fields' => $tempFields
        ];
    }
}

$breadcrumbs = array(
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home')
    ],
    [
        'name' => __('Advanced Custom Fields'),
        'url' => admin_url('acfields')
    ],
    [
        'name' => isset($posttype['id']) && !empty($posttype['id']) ? __('Edit') : __('Add'),
        'url' => admin_url('posttype'),
        'active' => true
    ]
);

Render::block('Backend\Header', ['layout' => 'default', 'title' => $title ?? 'Create - Advanced Custom Fields', 'breadcrumb' => $breadcrumbs]);

$errors = (!empty($errors)) ? json_encode($errors) : '{}';
$posttype['fields'] = isset($posttype['fields']) && !empty($posttype['fields']) ? _json_decode($posttype['fields']) : $fields_available;
$posttype['terms'] = isset($posttype['terms']) && !empty($posttype['terms']) ? _json_decode($posttype['terms']) : [];
$posttype['languages'] = isset($posttype['languages']) && !empty($posttype['languages']) ? _json_decode($posttype['languages']) : [];
$actionLink = admin_url('acfields/add');
if (isset($posttype['id']) && !empty($posttype['id'])) {
    $actionLink = admin_url('acfields/edit/' . $posttype['id']);
}

?>
<script defer="defer" src="<?php echo plugin_assets('js/acfields.js', 'acfields') ?>"></script>
<link href="<?php echo plugin_assets('css/acfields.css', 'acfields') ?>" rel="stylesheet">

<?php
// include file lang (langcode) in file Acforms.php
$translate = []; // Initialize with empty array to avoid undefined variable error
$langFile = PATH_PLUGINS . 'Acfields/Languages/' . APP_LANG . '/Acforms.php';
if (!file_exists($langFile)) {
    $langFile = PATH_PLUGINS . 'Acfields/Languages/en/Acforms.php';
}
if (file_exists($langFile)) {
    $translate = include $langFile;
    // Ensure $translate is an array
    if (!is_array($translate)) {
        $translate = [];
    }
}
?>
<script>
    window.ADMIN_URL = '<?php echo admin_url(); ?>';
    window.API_URL = '<?php echo api_url(); ?>';
    const errors = <?php echo json_encode($errors); ?>;
    const currentLanguage = '<?php echo APP_LANG; ?>';
    const allLanguages = <?php echo json_encode(array_keys(APP_LANGUAGES)); ?>;
    const allPostTypes = <?php echo json_encode($allPostTypes); ?>;
    // A H close cai nay lai vi kha nang khong can dung den
    /* const posttype = <?php //echo json_encode($posttype); 
                        ?>; */
    const isEditing = <?php echo isset($posttype['id']) && !empty($posttype['id']) ? 'true' : 'false'; ?>;
    const actionLink = <?php echo json_encode($actionLink); ?>;
    const csrf_token = '<?php echo $csrf_token; ?>';
    const data = <?php echo json_encode(['posttype' => $posttype, 'isEditing' => isset($posttype['id']) && !empty($posttype['id'])]); ?>;
    // get ALLOWED_FILE_TYPES
    const ALLOWED_FILE_TYPES = <?php echo json_encode(config('allowed_types', 'Uploads') ?? []); ?>;
    const ALLOWED_IMAGE_TYPES = <?php echo json_encode(config('images_types', 'Uploads') ?? []); ?>;
    const AUTO_IMAGE_SIZES = <?php echo json_encode(config('files', 'Uploads')['images_sizes_auto']); ?>;
    window.ALLOWED_FILE_TYPES = ALLOWED_FILE_TYPES || ["jpg", "jpeg", "png", "gif", "webp", "pdf", "doc", "docx", "xls", "xlsx", "csv", "ppt", "pptx", "txt", "rar", "zip", "iso", "mp3", "wav", "mkv", "mp4", "srt"];
    window.ALLOWED_IMAGE_TYPES = ALLOWED_IMAGE_TYPES || ["jpg", "jpeg", "png", "gif", "webp"];
    window.AUTO_IMAGE_SIZES = AUTO_IMAGE_SIZES || {};

    window.languageData = {
        name: "<?php echo lang_name(); ?>",
        code: "<?php echo lang_code(); ?>",
        flag: "<?php echo lang_flag(); ?>",
        t: <?php echo json_encode($translate); ?>
    };
</script>

<style type="text/css">
    @media (min-width: 640px) {
        .sm\:p-6 {
            padding: 1.5rem;
        }
    }
</style>

<!-- Header & Description -->
<div class="flex flex-col gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-foreground"><?= isset($posttype['id']) && !empty($posttype['id']) ? __('Edit Post Type') : __('Add Post Type') ?></h1>
        <p class="text-muted-foreground"><?= isset($posttype['id']) && !empty($posttype['id']) ? __('Edit post type configuration and field settings') : __('Create a new post type with custom fields and settings') ?></p>
    </div>
    <!-- Thông báo -->
    <?php if (Session::has_flash('success')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
    <?php endif; ?>
    <?php if (isset($errors) && strlen($errors) > 3): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => ($errors)]) ?>
    <?php endif; ?>
</div>

<div class="pc-container">

    <div class="pc-content">
        <div id="app"></div>
        <div id="root"></div>
    </div>
</div>

<?php
Render::block('Backend\Footer', ['layout' => 'default']);
?>