<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
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
      'url' => admin_url('backups'),
      'active' => true
  ]
);
view_header([
    'title' => __('Backup Management'),
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);

$backupsData = $backups['data']   ?? [];

$search = S_GET('q')     ?? '';
$type   = S_GET('type')  ?? '';
$sort   = S_GET('sort')  ?? 'created_at';
$order  = S_GET('order') ?? 'desc';
?>
<div class="" x-data="{ 
  showCreateBackup: false,
  selectedItems: [], 
  isDeleting: false,
  isCreating: false,
  
  toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
      checkbox.checked = selectAllCheckbox.checked;
    });
    
    this.updateSelectedItems();
  },
  
  updateSelectedItems() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    this.selectedItems = Array.from(checkboxes).map(checkbox => checkbox.value);
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    const allChecked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
    const someChecked = Array.from(allCheckboxes).some(checkbox => checkbox.checked);
    
    selectAllCheckbox.checked = allChecked;
    selectAllCheckbox.indeterminate = someChecked && !allChecked;
  },
  
  async deleteSelected() {
    if (this.selectedItems.length === 0) {
      alert('<?= __('Please select items to delete') ?>');
      return;
    }
    
    if (!confirm('<?= __('Are you sure you want to delete selected items?') ?>')) {
      return;
    }
    
    this.isDeleting = true;
    
    try {
      const formData = new FormData();
      formData.append('csrf_token', '<?= $csrf_token ?>');
      formData.append('ids', JSON.stringify(this.selectedItems));
      
      const response = await fetch('<?= admin_url('backups/delete') ?>', {
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
        window.location.reload();
      } else {
        alert(data.message || '<?= __('Error deleting items') ?>');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('<?= __('Network error occurred') ?>');
    } finally {
      this.isDeleting = false;
    }
  },

  async createBackup(type) {
    if (this.isCreating) return;
    
    this.isCreating = true;
    
    try {
      const formData = new FormData();
      formData.append('csrf_token', '<?= $csrf_token ?>');
      formData.append('type', type);
      formData.append('name', type.charAt(0).toUpperCase() + type.slice(1) + ' Backup ' + new Date().toLocaleString());
      formData.append('description', 'Manual ' + type + ' backup created from management page');
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
        window.location.reload();
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

  <!-- Header & Filter -->
  <div class="flex flex-col gap-4">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= __('Backup Management') ?></h1>
      <p class="text-muted-foreground"><?= __('Manage system backups and restore points') ?></p>
    </div>

    <!-- Thông báo -->
    <?php if (Session::has_flash('success')): ?>
      <?php echo View::include('parts/ui/notification', ['type' => 'success', 'message' => Session::flash('success')]); ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php echo View::include('parts/ui/notification', ['type' => 'error', 'message' => Session::flash('error')]); ?>
    <?php endif; ?>
    
    <div class="bg-card rounded-xl mb-4">
      <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto">
          <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
            <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10" placeholder="<?= __('search') ?>..." name="q" value="<?= htmlspecialchars($search) ?>" />
          </div>
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="type" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value=""><?= __('All Types') ?></option>
              <option value="full" <?= $type==='full'?'selected':'' ?>><?= __('Full Backup') ?></option>
              <option value="database" <?= $type==='database'?'selected':'' ?>><?= __('Database Only') ?></option>
              <option value="files" <?= $type==='files'?'selected':'' ?>><?= __('Files Only') ?></option>
            </select>
          </div>
        </div>
        <div class="flex gap-2">
          <!-- Delete Selected Button -->
          <button 
            type="button"
            @click="deleteSelected()" 
            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 h-10 px-4 py-2 whitespace-nowrap"
            :class="selectedItems.length > 0 ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
            :disabled="isDeleting || selectedItems.length === 0"
          >
            <i x-show="!isDeleting" data-lucide="trash2" class="h-4 w-4 mr-2"></i>
            <i x-show="isDeleting" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
            <span x-text="isDeleting ? '<?= __('Deleting...') ?>' : '<?= __('Delete Selected') ?>'"></span>
          </button>
          
          <!-- Create Backup Buttons -->
          <div class="flex gap-2">
            <button @click="createBackup('full')" 
                    :disabled="isCreating"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap disabled:opacity-50">
              <i x-show="!isCreating" data-lucide="download" class="h-4 w-4 mr-2"></i>
              <i x-show="isCreating" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
              <span x-text="isCreating ? '<?= __('Creating...') ?>' : '<?= __('Full Backup') ?>'"></span>
            </button>
            
            
            <a href="<?= admin_url('backups/settings') ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 whitespace-nowrap">
              <i data-lucide="settings" class="h-4 w-4 mr-2"></i>
              <?= __('Settings') ?>
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Bảng danh sách -->
  <div class="bg-card card-content !p-0 border overflow-hidden">
    <div class="overflow-x-auto">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm ">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
              <!-- Checkbox Select All -->
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-12">
                <input type="checkbox" id="selectAll" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" @change="toggleSelectAll()">
              </th>
              <?php
              // Helper để build sort link giữ lại filter
              function sort_link($label, $field, $sort, $order) {
                $params = $_GET;
                $params['sort'] = $field;
                $params['order'] = ($sort === $field && $order === 'asc') ? 'desc' : 'asc';
                $arrow = '';
                if ($sort === $field) {
                  $arrow = $order === 'asc' ? ' ▲' : ' ▼';
                }
                return '<a href="?' . http_build_query($params) . '" class="hover:text-primary">' . $label . $arrow . '</a>';
              }
              ?>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Name'), 'name', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Type'), 'type', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Size'), 'file_size', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Status'), 'status', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Created'), 'created_at', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Actions') ?></th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <?php if (!empty($backupsData)): ?>
              <?php foreach ($backupsData as $backup): ?>
                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                  <!-- Checkbox -->
                  <td class="px-4 py-1 align-middle text-center">
                    <input type="checkbox" class="row-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" 
                           value="<?= $backup['id'] ?>" @change="updateSelectedItems()">
                  </td>
                  <td class="px-4 py-1 align-middle">
                    <div class="flex flex-col">
                      <span class="font-medium text-foreground whitespace-nowrap truncate max-w-[200px]" title="<?= htmlspecialchars($backup['name'] ?? '') ?>">
                        <?= htmlspecialchars($backup['name'] ?? '') ?>
                      </span>
                      <?php if (!empty($backup['description'])): ?>
                        <span class="text-xs text-muted-foreground truncate max-w-[200px]" title="<?= htmlspecialchars($backup['description']) ?>">
                          <?= htmlspecialchars($backup['description']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle">
                    <?php
                    $badgeClass = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent ';
                    switch ($backup['type']) {
                      case 'full':
                        $badgeClass .= 'bg-primary text-primary-foreground';
                        break;
                      case 'database':
                        $badgeClass .= 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
                        break;
                      case 'files':
                        $badgeClass .= 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                        break;
                    }
                    ?>
                    <div class="<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($backup['type'] ?? '')) ?></div>
                  </td>
                  <td class="px-4 py-1 align-middle text-foreground whitespace-nowrap">
                    <?= formatBytes($backup['file_size'] ?? 0) ?>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <?php
                    $statusClass = 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-transparent ';
                    switch ($backup['status']) {
                      case 'completed':
                        $statusClass .= 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                        break;
                      case 'pending':
                        $statusClass .= 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
                        break;
                      case 'failed':
                        $statusClass .= 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                        break;
                    }
                    ?>
                    <div class="<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($backup['status'] ?? '')) ?></div>
                  </td>
                  <td class="px-4 py-1 align-middle text-foreground whitespace-nowrap">
                    <?= date('M j, Y H:i', strtotime($backup['created_at'] ?? '')) ?>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-1 justify-center">
                      <a href="<?= admin_url('backups/download/' . $backup['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" title="<?= __('Download Backup') ?>">
                        <i data-lucide="download" class="h-4 w-4"></i>
                      </a>
                      <a href="<?= admin_url('backups/restore/' . $backup['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" onclick="return confirm('<?= __('Are you sure you want to restore this backup? This will overwrite current data.') ?>');" title="<?= __('Restore Backup') ?>">
                        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                      </a>
                      <a href="<?= admin_url('backups/delete/' . $backup['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" onclick="return confirm('<?= __('Are you sure you want to delete this backup?') ?>');" title="<?= __('Delete Backup') ?>">
                        <i data-lucide="trash2" class="h-4 w-4"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center py-4 text-muted-foreground"><?= __('No backups found.') ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Results Summary -->
    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-1 border-t gap-4">
      <div class="text-sm text-muted-foreground">
        <!-- Hiển thị số lượng -->
        <?php
        $total = $backups['total'] ?? count($backupsData);
        if ($total > 0) {
          _e('Total').' '. $total.' '. __('results');
        } else {
          _e('No results');
        }
        ?>
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

<?php view_footer(); ?>
