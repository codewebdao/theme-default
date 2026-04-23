<?php

use System\Libraries\Render;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Backend/Global', APP_LANG);
Flang::load('Backend/Backups', APP_LANG);

// Helper function to format bytes
function formatBytes($bytes, $decimals = 2) {
  if ($bytes == 0) return '0 Bytes';
  $k = 1024;
  $dm = $decimals < 0 ? 0 : $decimals;
  $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  $i = floor(log($bytes) / log($k));
  return number_format(($bytes / pow($k, $i)), $dm) . ' ' . $sizes[$i];
}

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Backups'),
      'url' => admin_url('backups')
  ],
  [
      'name' => __('Settings'),
      'url' => admin_url('backups/settings'),
      'active' => true
  ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => __('Backup Settings'), 'breadcrumb' => $breadcrumbs ]);

$settings = $settings ?? [];
?>

<div class="" x-data="{ 
  isCreating: false,
  
  async createBackup() {
    if (this.isCreating) return;
    
    this.isCreating = true;
    
    try {
      const formData = new FormData();
      formData.append('csrf_token', '<?= $csrf_token ?>');
      formData.append('type', 'full');
      formData.append('name', 'Manual Backup ' + new Date().toLocaleString());
      formData.append('description', 'Manual backup created from settings page');
      formData.append('submit', '1');
      
      const response = await fetch('<?= admin_url('backups/create') ?>', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        alert('<?= __('Backup created successfully') ?>');
        window.location.href = '<?= admin_url('backups/index') ?>';
      } else {
        alert(data.message || '<?= __('Error creating backup') ?>');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('<?= __('Network error occurred') ?>');
    } finally {
      this.isCreating = false;
    }
  }
}">

  <!-- Header -->
  <div class="flex flex-col gap-4 mb-6">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= __('Backup Settings') ?></h1>
      <p class="text-muted-foreground"><?= __('Configure automatic backup settings for your system') ?></p>
    </div>

    <!-- Thông báo -->
    <?php if (Session::has_flash('success')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
    <?php endif; ?>
    
    <!-- Hiển thị lỗi validation -->
    <?php if (!empty($errors)): ?>
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
        <div class="flex items-start">
          <i data-lucide="alert-circle" class="h-5 w-5 text-red-500 mt-0.5 mr-3 flex-shrink-0"></i>
          <div class="flex-1">
            <h3 class="text-sm font-medium text-red-800 mb-2"><?= __('Please correct the following errors:') ?></h3>
            <ul class="text-sm text-red-700 space-y-1">
              <?php foreach ($errors as $field => $fieldErrors): ?>
                <?php if (is_array($fieldErrors)): ?>
                  <?php foreach ($fieldErrors as $error): ?>
                    <li>• <?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li>• <?= htmlspecialchars($fieldErrors) ?></li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Main Content -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Settings Form -->
    <div class="lg:col-span-2">
      <div class="bg-card rounded-xl border">
        <div class="flex flex-col space-y-1.5 bg-menu-background-hover text-menu-text-hover rounded-t-lg p-2"><div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4"><h3 class="tracking-tight flex items-center text-lg font-semibold"><?= __('Automatic Backup Configuration') ?></h3></div></div>
          
        <div class="p-6 pt-0">
          
          <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <!-- Auto Backup Toggle -->
            <div class="space-y-2">
              <label class="text-sm font-medium"><?= __('Enable Automatic Backup') ?></label>
              <div class="flex items-center space-x-2">
                <input type="checkbox" id="backup_auto" name="backup_auto" value="1" 
                       <?= (!empty($settings['backup_auto']) && $settings['backup_auto'] == '1') ? 'checked' : '' ?>
                       class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <label for="backup_auto" class="text-sm text-muted-foreground">
                  <?= __('Automatically create backups based on schedule') ?>
                </label>
              </div>
            </div>

            <!-- Backup Frequency -->
            <div class="space-y-2">
              <label class="text-sm font-medium" for="backup_frequency"><?= __('Backup Frequency') ?></label>
              <select id="backup_frequency" name="backup_frequency" 
                      class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                <option value="hourly" <?= ($settings['backup_frequency'] ?? '') == 'hourly' ? 'selected' : '' ?>><?= __('Hourly') ?></option>
                <option value="daily" <?= ($settings['backup_frequency'] ?? '') == 'daily' ? 'selected' : '' ?>><?= __('Daily') ?></option>
                <option value="weekly" <?= ($settings['backup_frequency'] ?? '') == 'weekly' ? 'selected' : '' ?>><?= __('Weekly') ?></option>
                <option value="monthly" <?= ($settings['backup_frequency'] ?? '') == 'monthly' ? 'selected' : '' ?>><?= __('Monthly') ?></option>
              </select>
            </div>

            <!-- Backup Time -->
            <div class="space-y-2">
              <label class="text-sm font-medium" for="backup_time"><?= __('Backup Time') ?></label>
              <input type="time" id="backup_time" name="backup_time" 
                     value="<?= htmlspecialchars($settings['backup_time'] ?? '02:00') ?>"
                     class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
              <p class="text-xs text-muted-foreground"><?= __('Time when automatic backup should run (24-hour format)') ?></p>
            </div>

            <!-- Max Backups -->
            <div class="space-y-2">
              <label class="text-sm font-medium" for="backup_max"><?= __('Maximum Backups to Keep') ?></label>
              <input type="number" id="backup_max" name="backup_max" min="1" max="100" 
                     value="<?= htmlspecialchars($settings['backup_max'] ?? '10') ?>"
                     class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
              <p class="text-xs text-muted-foreground"><?= __('Oldest backups will be automatically deleted when this limit is reached') ?></p>
            </div>

            <!-- Backup Types -->
            <div class="space-y-4">
              <label class="text-sm font-medium"><?= __('What to Backup') ?></label>
              
              <div class="space-y-3">
                <div class="flex items-center space-x-2">
                  <input type="checkbox" id="backup_database" name="backup_database" value="1" 
                         <?= (!empty($settings['backup_database']) && $settings['backup_database'] == '1') ? 'checked' : '' ?>
                         class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                  <label for="backup_database" class="text-sm">
                    <span class="font-medium"><?= __('Database') ?></span>
                    <span class="text-muted-foreground ml-1"><?= __('Include database in backup') ?></span>
                  </label>
                </div>
                
                <div class="flex items-center space-x-2">
                  <input type="checkbox" id="backup_files" name="backup_files" value="1" 
                         <?= (!empty($settings['backup_files']) && $settings['backup_files'] == '1') ? 'checked' : '' ?>
                         class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                  <label for="backup_files" class="text-sm">
                    <span class="font-medium"><?= __('Files') ?></span>
                    <span class="text-muted-foreground ml-1"><?= __('Include application files in backup') ?></span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Email Notifications -->
            <div class="space-y-4">
              <div class="flex items-center space-x-2">
                <input type="checkbox" id="backup_email_notifications" name="backup_email_notifications" value="1" 
                       <?= (!empty($settings['backup_email_notifications']) && $settings['backup_email_notifications'] == '1') ? 'checked' : '' ?>
                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="backup_email_notifications" class="text-sm font-medium"><?= __('Email Notifications') ?></label>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium" for="backup_email"><?= __('Email Address') ?></label>
                <input type="email" id="backup_email" name="backup_email" 
                       value="<?= htmlspecialchars($settings['backup_email'] ?? '') ?>"
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                <p class="text-xs text-muted-foreground"><?= __('Email address to receive backup notifications') ?></p>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
              <button type="submit" name="submit" value="1" 
                      class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <i data-lucide="save" class="h-4 w-4 mr-2"></i>
                <?= __('Save Settings') ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="space-y-6">
      
      <!-- Manual Backup -->
      <div class="bg-card rounded-xl border">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4"><?= __('Quick Actions') ?></h3>
          
          <div class="space-y-4">
            <button @click="createBackup()" 
                    :disabled="isCreating"
                    class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 disabled:opacity-50">
              <i x-show="!isCreating" data-lucide="download" class="h-4 w-4 mr-2"></i>
              <i x-show="isCreating" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
              <span x-text="isCreating ? '<?= __('Creating...') ?>' : '<?= __('Create Manual Backup') ?>'"></span>
            </button>
            
            <a href="<?= admin_url('backups/index') ?>" 
               class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
              <i data-lucide="list" class="h-4 w-4 mr-2"></i>
              <?= __('View All Backups') ?>
            </a>
          </div>
        </div>
      </div>

      <!-- System Info -->
      <div class="bg-card rounded-xl border">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4"><?= __('System Information') ?></h3>
          
          <div class="space-y-3 text-sm">
            <div class="flex justify-between">
              <span class="text-muted-foreground"><?= __('PHP Version') ?>:</span>
              <span class="font-mono"><?= PHP_VERSION ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground"><?= __('Available Space') ?>:</span>
              <span class="font-mono"><?= formatBytes(disk_free_space(PATH_WRITE)) ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground"><?= __('Backup Directory') ?>:</span>
              <span class="font-mono text-xs"><?= PATH_WRITE ?>backups/</span>
            </div>
            <?php
            $backup_dir = PATH_WRITE . 'backups/';
            $backup_size = 0;
            if (is_dir($backup_dir)) {
              $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backup_dir, RecursiveDirectoryIterator::SKIP_DOTS));
              foreach ($iterator as $file) {
                if ($file->isFile()) {
                  $backup_size += $file->getSize();
                }
              }
            }
            ?>
            <div class="flex justify-between">
              <span class="text-muted-foreground"><?= __('Backup Size') ?>:</span>
              <span class="font-mono"><?= formatBytes($backup_size) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Help -->
      <div class="bg-card rounded-xl border">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4"><?= __('Help & Tips') ?></h3>
          
          <div class="space-y-3 text-sm text-muted-foreground">
            <div class="flex items-start space-x-2">
              <i data-lucide="info" class="h-4 w-4 mt-0.5 flex-shrink-0"></i>
              <p><?= __('Automatic backups run in the background and don\'t affect your website performance.') ?></p>
            </div>
            <div class="flex items-start space-x-2">
              <i data-lucide="shield" class="h-4 w-4 mt-0.5 flex-shrink-0"></i>
              <p><?= __('Backups are stored securely and can be restored at any time.') ?></p>
            </div>
            <div class="flex items-start space-x-2">
              <i data-lucide="clock" class="h-4 w-4 mt-0.5 flex-shrink-0"></i>
              <p><?= __('Choose backup time when your website has low traffic.') ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Helper function to format bytes
function formatBytes(bytes, decimals = 2) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Khởi tạo lại các icon Lucide sau khi load trang
document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
});
</script>

<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>
