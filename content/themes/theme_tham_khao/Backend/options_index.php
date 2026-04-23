<?php
namespace System\Libraries;

use System\Libraries\Render;    
use App\Libraries\Fastlang as Flang;

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Options'),
      'url' => admin_url('options'),
      'active' => true
  ]
);

Render::block('Backend\Header', ['layout'=>'default', 'title' => __('Website Settings'), 'breadcrumb' => $breadcrumbs]);
?>

<div class="pc-container">
    <div class="pc-content">
    
    <!-- Header & Description -->
    <div class="flex flex-col gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-foreground"><?= __('Website Settings') ?></h1>
        <p class="text-muted-foreground"><?= __('Manage website configuration and settings') ?></p>
      </div>

      <!-- Thông báo -->
      <?php if (Session::has_flash('success')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
      <?php endif; ?>
      <?php if (Session::has_flash('error')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
      <?php endif; ?>
    </div>

    <style>
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* DND Kit specific styles */
        .repeater-item {
            transition: all 0.2s ease;
        }
        
        .repeater-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
  <script type="module" crossorigin src="<?= theme_assets('js/posts_add.js', 'Backend') ?>"></script>
  <link rel="stylesheet" crossorigin href="<?= theme_assets('css/posts_add.css', 'Backend') ?>">

  <style>
       .ce-block__content {
        max-width: inherit;
       }
      .fixed-form-actions { transition: box-shadow 0.2s; }
      @media (max-width: 600px) {
        .fixed-form-actions { padding: 1rem 2vw 1rem 2vw; }
        .fixed-form-actions > div { flex-direction: column; align-items: stretch !important; gap: 0.5rem !important; }
      }
    </style>
    <!-- PHP sẽ tạo form wrapper này -->
    <!-- <form id="acf-form" method="post" action="" enctype="multipart/form-data"> -->
    
    <!-- React app container - Chỉ render fields -->
    <div id="initial-loading" style="display: flex; justify-content: center; align-items: center; height: 200px; flex-direction: column;">
        <div class="loading-spinner"></div>
        <p style="margin-top: 10px; color: #666;"><?= __('Loading ACF Form Builder...') ?></p>
        <p style="margin-top: 5px; color: #999; font-size: 12px;" id="initial-loading-content"><?= __('Initializing drag & drop components...') ?></p>
    </div>
    <div id="root">
        <!-- Initial loading state -->
        
    </div>
    
    <!-- PHP sẽ inject data vào đây -->
    <script>
        // PHP sẽ truyền data vào window object
        window.ACF_DATA = {
        "lang": "<?= APP_LANG ?>",
        "ADMIN_URL": "<?= admin_url() ?>",
        "app_lang": <?= json_encode(array_keys(APP_LANGUAGES)) ?>,
        "post_lang": "<?= $post_lang ?>",
        "page": "options",
        "inputConfig": {
          "border": {
            "width": 1,
            "style": "solid",
            "color": "#e2e8f0",
            "radius": 6
          },
          "background": {
            "color": "#ffffff",
            "hover": "#f8fafc",
            "focus": "#ffffff"
          },
          "text": {
            "color": "#1e293b",
            "fontSize": 14,
            "fontWeight": "normal"
          },
          "spacing": {
            "padding": { "x": 12, "y": 8 },
            "margin": { "x": 0, "y": 2 },
            "fieldGap": 16
          },
          "size": {
            "height": 38,
            "minHeight": 38
          },
          "wrapper": {
            "enabled": true,
            "border": {
              "width": 1,
              "style": "solid",
              "color": "#f1f5f9",
              "radius": 6
            },
            "background": "#ffffff",
            "padding": { "x": 12, "y": 12 },
            "margin": { "x": 0, "y": 8 }
          },
          "label": {
            "color": "#374151",
            "fontSize": 14,
            "fontWeight": "medium",
            "marginBottom": 4
          },
          "effects": {
            "transition": "all 0.2s ease",
            "focusRing": true,
            "hoverEnabled": true
          },
          "outerWrapper": {
            "enabled": true,
            "border": {
              "width": 0,
              "style": "solid",
              "color": "transparent",
              "radius": 8
            },
            "background": "#fafafa",
            "padding": { "x": 0, "y": 0 },
            "margin": { "x": 0, "y": 0 },
            "shadow": true
          }
        },
        "optionsData": <?= json_encode($data) ?>
      };
    </script>


    </div>
    <!-- </form> -->
</div>

<?php Render::block('Backend\Footer'); ?>

