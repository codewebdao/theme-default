<?php

use System\Libraries\Render;
use System\Libraries\Session;

$breadcrumbs = array(
  [
    'name' => __('Dashboard'),
    'url' => admin_url('home')
  ],
  [
    'name' => __('Advanced Custom Fields'),
    'url' => admin_url('acfields'),
    'active' => true
  ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => __('Advanced Custom Fields Management'), 'breadcrumb' => $breadcrumbs]);

$data    = $postTypes['data']   ?? $postTypes;
$is_next = $postTypes['is_next'] ?? 0;
$page    = $postTypes['page']    ?? 1;

// Lấy các tham số GET
$currentLang = S_GET('post_lang') ?? null;
$search      = S_GET('q')        ?? '';
$limit       = S_GET('limit')    ?? 10;
$sort        = S_GET('sort')     ?? 'id';
$order       = S_GET('order')    ?? 'desc';
?>

<div class="" x-data="{ 
  showAdd: false, 
  selectedItems: [], 
  isDeleting: false,
  
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
      formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
      formData.append('ids', JSON.stringify(this.selectedItems));
      
      const response = await fetch('<?= admin_url('acfields/delete') ?>', {
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
  }
}">

  <!-- Header & Filter -->
  <div class="flex flex-col gap-4">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= __('Post Types Management') ?></h1>
      <p class="text-muted-foreground"><?= __('Manage system post types and their configurations') ?></p>
    </div>

    <!-- Thông báo -->
    <?php if (Session::has_flash('success')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
    <?php endif; ?>



    <div class="bg-card rounded-xl mb-4">
      <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto">
          <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
            <input
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10"
              placeholder="<?= __('Search post types...') ?>"
              name="q"
              value="<?= htmlspecialchars($search) ?>"
              @keydown.enter="$event.target.closest('form').submit()" />
          </div>
          <div class="min-w-[100px] w-full sm:w-auto">
            <select name="limit" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value="5" <?= ($limit == 5) ? 'selected' : '' ?>>5</option>
              <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
              <option value="15" <?= ($limit == 15) ? 'selected' : '' ?>>15</option>
              <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
              <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
              <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
              <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
              <option value="200" <?= ($limit == 200) ? 'selected' : '' ?>>200</option>
            </select>
          </div>
        </div>
        <div class="flex gap-2">
          <!-- Delete Selected Button -->
          <button
            @click="deleteSelected()"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 h-10 px-4 py-2 whitespace-nowrap"
            :class="selectedItems.length > 0 ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
            :disabled="isDeleting || selectedItems.length === 0">
            <i x-show="!isDeleting" data-lucide="trash2" class="h-4 w-4 mr-2"></i>
            <i x-show="isDeleting" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
            <span x-text="isDeleting ? '<?= __('Deleting...') ?>' : '<?= __('Delete Selected') ?>'"></span>
          </button>

          <!-- Add New Button -->
          <a href="<?= admin_url('acfields/add/') ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap w-full lg:w-auto">
            <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
            <?= __('Add Post Type') ?>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Bảng danh sách -->
  <div class="bg-card card-content !p-0 border overflow-hidden">
    <div class="overflow-x-auto">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm table-fixed">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
              <!-- Checkbox Select All -->
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-12">
                <input type="checkbox" id="selectAll" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" @change="toggleSelectAll()">
              </th>
              <?php
              // Helper để build sort link giữ lại filter
              function sort_link($label, $field, $sort, $order)
              {
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
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap w-16"><?php echo sort_link(__('ID'), 'id', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap w-32"><?php echo sort_link(__('Name'), 'name', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap w-24"><?php echo sort_link(__('Slug'), 'slug', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap w-60"><?= __('Languages') ?></th>
              <th class="px-4 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap w-24"><?= __('Total Items') ?></th>
              <th class="px-4 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap w-60"><?= __('Terms') ?></th>
              <th class="px-4 py-3 text-center align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap w-24"><?php echo sort_link(__('Status'), 'status', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap w-20"><?= __('Locked') ?></th>
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap w-24"><?= __('Actions') ?></th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <?php if (!empty($data)): ?>
              <?php foreach ($data as $postType):
                $isLocked = isset($postType['is_locked']) && $postType['is_locked'] == 1;
              ?>
                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                  <!-- Checkbox -->
                  <td class="px-4 py-1 align-middle text-center">
                    <input type="checkbox" class="row-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                      value="<?= $postType['id'] ?>"
                      <?= $isLocked ? 'disabled' : '' ?>
                      @change="updateSelectedItems()">
                  </td>
                  <td class="px-4 py-1 align-middle font-medium text-foreground whitespace-nowrap w-16"><?= htmlspecialchars($postType['id'] ?? '') ?></td>
                  <td class="px-4 py-1 align-middle text-foreground whitespace-nowrap truncate w-32" title="<?= htmlspecialchars($postType['name'] ?? '') ?>">
                    <a href="<?= admin_url('acfields/edit/' . $postType['id']); ?>" class="text-primary hover:underline hover:text-primary/80 transition-colors">
                      <?= htmlspecialchars($postType['name'] ?? '') ?>
                    </a>
                  </td>
                  <td class="px-4 py-1 align-middle text-foreground whitespace-nowrap truncate w-24" title="<?= htmlspecialchars($postType['slug'] ?? '') ?>"><?= htmlspecialchars($postType['slug'] ?? '') ?></td>
                  <td class="px-4 py-1 align-middle w-80">
                    <?php
                    if (!empty($postType['languages'])) {
                      $postType['languages'] = json_decode($postType['languages'], true) ?? $postType['languages'];
                      if (!empty($postType['languages'])) {
                    ?>
                        <div class="flex flex-wrap gap-1 max-w-full">
                          <?php

                          foreach ($postType['languages'] as $lang):
                          ?>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold border-transparent bg-primary/10 text-primary whitespace-nowrap min-w-0">
                              <?= htmlspecialchars($lang); ?>
                            </span>
                          <?php endforeach; ?>
                        </div>
                      <?php                         } else {
                        echo '<span class="text-muted-foreground text-xs">' . __('No languages') . '</span>';
                      } ?>
                    <?php } else {
                      echo '<span class="text-muted-foreground text-xs">' . __('No languages') . '</span>';
                    } ?>

                  </td>
                  <td class="px-4 py-1 align-middle text-center w-20"><?= htmlspecialchars($postType['current_id'] ?? '') ?></td>
                  <td class="px-4 py-1 align-middle w-40">
                    <?php if (!empty($postType['terms'])): ?>
                      <div class="flex flex-wrap gap-1 max-w-full">
                        <?php
                        is_string($postType['terms']) ? $postType['terms'] = json_decode($postType['terms'], true) : $postType['terms'];
                        if (!empty($postType['terms'])):
                          foreach ($postType['terms'] as $term):
                        ?>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 whitespace-nowrap min-w-0">
                              <?= htmlspecialchars($term['name']); ?>
                            </span>
                        <?php
                          endforeach;
                        endif;
                        ?>
                      </div>
                    <?php else: ?>
                      <span class="text-muted-foreground text-xs"><?= __('No terms') ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-1 align-middle text-center w-24">
                    <div class="flex items-center gap-2 justify-center">
                      <button type="button" onclick="changeStatusPostType(<?= $postType['id']; ?>, event);" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background data-[state=checked]:bg-primary data-[state=unchecked]:bg-input <?= $postType['status'] === 'active' ? 'bg-primary' : 'bg-input' ?>">
                        <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform <?= $postType['status'] === 'active' ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                      </button>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-center w-20">
                    <div class="flex items-center justify-center">
                      <?php if ($isLocked): ?>
                        <i data-lucide="lock" class="h-4 w-4 text-red-600" title="<?= __('Locked') ?>"></i>
                      <?php else: ?>
                        <i data-lucide="unlock" class="h-4 w-4 text-green-600" title="<?= __('Unlocked') ?>"></i>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-1 justify-center">
                      <?php if ($isLocked): ?>
                        <span class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background border border-input bg-background rounded-md h-8 w-8 p-0 flex-shrink-0 opacity-50 cursor-not-allowed" title="<?= __('Post type is locked') ?>">
                          <i data-lucide="square-pen" class="h-4 w-4"></i>
                        </span>
                        <a href="<?= admin_url('acfields/copy/' . $postType['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" title="<?= __('Copy Post Type') ?>">
                          <i data-lucide="copy" class="h-4 w-4"></i>
                        </a>
                        <span class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background border border-input bg-background rounded-md h-8 w-8 p-0 flex-shrink-0 opacity-50 cursor-not-allowed" title="<?= __('Post type is locked') ?>">
                          <i data-lucide="trash2" class="h-4 w-4"></i>
                        </span>
                      <?php else: ?>
                        <a href="<?= admin_url('acfields/edit/' . $postType['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" title="<?= __('Edit Post Type') ?>">
                          <i data-lucide="square-pen" class="h-4 w-4"></i>
                        </a>
                        <a href="<?= admin_url('acfields/copy/' . $postType['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" title="<?= __('Copy Post Type') ?>">
                          <i data-lucide="copy" class="h-4 w-4"></i>
                        </a>
                        <a href="<?= admin_url('acfields/delete/' . $postType['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" onclick="return confirm('<?= __('Are you sure you want to delete this item?') ?>');" title="<?= __('Delete Post Type') ?>">
                          <i data-lucide="trash2" class="h-4 w-4"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center py-4 text-muted-foreground"><?= __('No post types found.') ?></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-1 border-t gap-4">
      <div class="text-sm text-muted-foreground">
        <!-- Hiển thị số lượng -->
        <?php
        $total = $postTypes['total'] ?? count($data);
        $from = ($page - 1) * $limit + 1;
        $to = $from + count($data) - 1;
        if ($total > 0) {
          _e('Showing %1% to %2% of %3% results', $from, $to, $total);
        } else {
          _e('No results');
        }
        ?>
      </div>
      <div class="flex items-center gap-2">
        <?php
        $query_params = [];
        if (!empty($search)) $query_params['q'] = $search;
        if ($limit != 10) $query_params['limit'] = $limit;
        if (!empty($sort)) $query_params['sort'] = $sort;
        if (!empty($order)) $query_params['order'] = $order;
        if (!empty($currentLang)) $query_params['post_lang'] = $currentLang;
        echo Render::pagination(admin_url('acfields/index'), $page, $is_next, $query_params);
        ?>
      </div>
    </div>
  </div>
</div>

<script>
  function changeStatusPostType(id, event) {
    event.preventDefault();
    if (confirm('<?= __('Are you sure you want to change the status?') ?>')) {
      window.location.href = '<?= admin_url('acfields/changestatus/') ?>' + id + '/';
    }
  }
</script>

<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>