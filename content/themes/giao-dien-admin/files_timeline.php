<?php
use System\Libraries\Render\View;
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use App\Libraries\Fastlang as Flang;

View::addCss('page-css-css-files-timeline-css', 'css/files_timeline.css', [], null, 'all', false);
View::addJs('page-js-iMagify-2-0-js', 'js/iMagify.2.0.js', [], null, true, false, false, false);
View::addJs('page-js-files-timeline', 'js/files_timeline.js', [], null, true, false, false, false);
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?? 'Files' ?></title>

  <?php echo view_css('head'); echo view_js('head'); ?>
  <script type="text/javascript">
    // JS Global
    const FILES_URL = '<?= files_url() ?>';
    const FILES_API = "<?= base_url('/api/v2/files/'); ?>";
    const PUBLIC_URL = '<?= public_url() ?>';
    const BASE_URL = '<?= base_url() ?>';
    // Debug log control
    const show_log = true; // Set false để tắt log

    window.CONFIG_FILES = <?= json_encode($config_files); ?>;

    window.LANG = {
      uploads: "<?= Flang::__('uploads'); ?>",
      delete: "<?= Flang::__('delete'); ?>",
      select_files: "<?= Flang::__('select files'); ?>",
      enter_file_name: "<?= Flang::__('enter file name'); ?>",
      download: "<?= Flang::__('download'); ?>",
      rename: "<?= Flang::__('rename'); ?>",
      enter_new_name: "<?= Flang::__('enter new name'); ?>",
      name_required_and_different: "<?= Flang::__('name is required and different from current name'); ?>",
      prev: "<?= Flang::__('prev'); ?>",
      next: "<?= Flang::__('next'); ?>",
      confirm_delete_one: "<?= Flang::__('are you sure to delete'); ?>",
      confirm_delete_multi: "<?= Flang::__('are you sure to delete selected items'); ?>",
    };
    window.APP_CONFIG = {
      FILES_URL,
      FILES_API,
      PUBLIC_URL,
      BASE_URL,
      show_log,
      CONFIG_FILES,
      LANG
    };
  </script>

</head>

<body>

  <style>
    /* Global Loading Before Vue Load */
    #global-loader {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.8);
      z-index: 9999;
      backdrop-filter: blur(2px);
      pointer-events: auto;
      user-select: none;
    }

    #global-loader * {
      pointer-events: none;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg)
      }
    }
  </style>

  <div id="global-loader">
    <svg class="w-12 h-12 text-blue-600" style="animation:spin 1s linear infinite"
      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4">
      <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
      <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path>
    </svg>
  </div>

  <div id="app"></div>
</body>

</html>