<?php

namespace System\Libraries;

use App\Libraries\Fastlang as Flang;
//Render::block('Backend\Head', ['layout' => 'default', 'title' => $title ?? 'Files']);
Render::asset('css', 'css/files_timeline.css', ['area' => 'backend', 'location' => 'head']);
Render::asset('js', 'js/iMagify.2.0.js', ['area' => 'backend', 'location' => 'head']);
Render::asset('js', 'js/files_timeline.js?t=' . time(), ['area' => 'backend', 'location' => 'head']);
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?? 'Files' ?></title>

  <?= \System\Libraries\Render::renderAsset('head', 'backend') ?>
  <script type="text/javascript">
    // JS Global
    var urlfiles_tmp = '<?= config('files', 'Uploads')['files_url'] ?? '/uploads' ?>';
    const FILES_URL = urlfiles_tmp.replace(/\/$/, '') + '/';
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