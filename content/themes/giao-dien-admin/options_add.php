<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

if (!empty($allPostTypes)) {
    foreach ($allPostTypes as &$item) {
        $item = [
            'id' => $item['id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'menu' => $item['menu'],
            'status' => $item['status'],
        ];
    }
}

$breadcrumbs = array(
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home')
    ],
    [
        'name' => __('Options'),
        'url' => admin_url('options')
    ],
    [
        'name' => isset($options[0]['id']) && !empty($options[0]['id']) ? __('Edit Option') : __('Add Option'),
        'url' => admin_url('options/add'),
        'active' => true
    ]
);

view_header([
    'title' => $title ?? 'CMS Full Form',
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);

$errors = (!empty($errors)) ? json_encode($errors) : '{}';
$actionLink = admin_url('options/add');
if (isset($options[0]['id']) && !empty($options[0]['id'])) {
    $actionLink = admin_url('options/edit/' . $options[0]['id']);
    $deleteLink = admin_url('options/delete/' . $options[0]['id']);
    $data['delete_link'] = $deleteLink;
}
?>
<script defer="defer" src="<?php echo plugin_assets('js/acfields.js', 'acfields') ?>"></script>
<link href="<?php echo plugin_assets('css/acfields.css', 'acfields') ?>" rel="stylesheet">
<?php
// include file lang (langcode) in file Options.php
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
$optionData = array(
    'option_groups' => $option_groups,
    'allowed_types' => $allowed_types,
    'isEditing' => $isEditing,
    'title' => $title,
    'csrf_token' => $csrf_token,
    'actionLink' => $actionLink
);
if (isset($options)) {
    $optionData['options'] = $options;
}
?>
<script>
    window.ADMIN_URL = '<?php echo admin_url(); ?>';
    const errors = <?php echo json_encode($errors); ?>;
    const currentLanguage = '<?php echo APP_LANG; ?>';
    const allLanguages = <?php echo json_encode(array_keys(APP_LANGUAGES)); ?>;
    const allPostTypes = <?php echo json_encode($allPostTypes); ?>;
    const isEditing = <?php echo isset($isEditing) && $isEditing ? 'true' : 'false'; ?>;
    const actionLink = <?php echo json_encode($actionLink); ?>;
    const csrf_token = '<?php echo $csrf_token; ?>';
    const optionsData = <?php echo json_encode($optionData); ?>;
    const page = 'options';

    // get ALLOWED_FILE_TYPES
    const ALLOWED_FILE_TYPES = <?php echo json_encode(config('allowed_types', 'Uploads')); ?>;
    const ALLOWED_IMAGE_TYPES = <?php echo json_encode(config('images_types', 'Uploads')); ?>;
    const AUTO_IMAGE_SIZES = <?php echo json_encode(config('files', 'Uploads')['images_sizes_auto']); ?>;
    window.ALLOWED_FILE_TYPES = ALLOWED_FILE_TYPES || ["jpg", "jpeg", "png", "gif", "webp", "pdf", "doc", "docx", "xls", "xlsx", "csv", "ppt", "pptx", "txt", "rar", "zip", "iso", "mp3", "wav", "mkv", "mp4", "srt"];
    window.ALLOWED_IMAGE_TYPES = ALLOWED_IMAGE_TYPES || ["jpg", "jpeg", "png", "gif", "webp"];
    window.AUTO_IMAGE_SIZES = AUTO_IMAGE_SIZES || {};

    // Data Languages for Translate React App
    window.languageData = {
        name: "<?php echo lang_name(); ?>",
        code: "<?php echo lang_code(); ?>",
        flag: "<?php echo lang_flag(); ?>",
        t: <?php echo json_encode($translate); ?>
    };
</script>

<div class="pc-container">
    <div class="pc-content">

        <!-- Header & Description -->
        <div class="flex flex-col gap-4 mb-6">
            <!-- Thông báo -->
            <?php if (Session::has_flash('success')): ?>
                <?php echo View::include('parts/ui/notification', ['type' => 'success', 'message' => Session::flash('success')]); ?>
            <?php endif; ?>
            <?php if (Session::has_flash('error')): ?>
                <?php echo View::include('parts/ui/notification', ['type' => 'error', 'message' => Session::flash('error')]); ?>
            <?php endif; ?>
        </div>

        <div id="app"></div>
        <div id="root"></div>
    </div>
</div>


<?php view_footer(); ?>