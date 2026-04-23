<?php
namespace System\Libraries;

use System\Libraries\Render;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Posts', APP_LANG);

// Nếu không có post_lang trong URL, chuyển hướng về trang với APP_LANG_DF
if (!HAS_GET('post_lang')) {
    $redirectParams = S_GET();
    $redirectParams['post_lang'] = APP_LANG_DF;
    header('Location: ' . admin_url('posts') . '?' . http_build_query($redirectParams));
    exit;
}

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Posts'),
      'url' => admin_url('posts'),
  ],
  [
      'name' => __($posttype['name']),
      'url' => admin_url('posts/?type=' . $posttype['slug'] . '&post_lang=' . $currentLang),
      'active' => true
  ]
);
Render::block('Backend\Header', ['layout' => 'default', 'title' => __('Posts Management'), 'breadcrumb' => $breadcrumbs]);

// Định nghĩa biến trước khi sử dụng trong Alpine.js
$posttype_slug = S_GET('type') ?? $posttype['slug'] ?? 'post';
$posttype_languages = is_string($posttype['languages']) ? json_decode($posttype['languages'], true) : (is_array($posttype['languages']) ? $posttype['languages'] : []);
// xoi thêm check box ở trước để có thể hiển thị nhiều dòng
// phân tab theo trạng thái sẽ ổn định hơn
// Ví dụ $posts:
// $posts = [
//   'data'   => [...],
//   'is_next'=> 1,
//   'page'   => 1
// ];
$data    = $posts['data']   ?? [];
$is_next = $posts['is_next'] ?? 0;
$page    = $posts['page']    ?? 1;
// Các tham số GET hiện có
$currentLang = S_GET('post_lang') ?? '';
$search      = S_GET('q')         ?? '';
$limit       = S_GET('limit')     ?? 10;
$sort        = S_GET('sort')      ?? '';
$order       = S_GET('order')     ?? '';
?>
<div class="" x-data="{ 
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
      formData.append('type', '<?= htmlspecialchars($posttype_slug) ?>');
      formData.append('post_lang', '<?= htmlspecialchars($currentLang) ?>');
      
      const response = await fetch('<?= admin_url('posts/delete') ?>', {
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
      
      // API dùng Response::success → { success: true, message, data, ... } (không có status)
      if (data.success === true) {
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
  
  bulkEdit() {
    // Create a form to submit post IDs via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= admin_url('posts/bulkedit') ?>';
    
    // Add post IDs as comma-separated string
    if (this.selectedItems.length > 0) {
      const idsInput = document.createElement('input');
      idsInput.type = 'hidden';
      idsInput.name = 'post_ids';
      idsInput.value = this.selectedItems.join(',');
      form.appendChild(idsInput);
    }
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= Session::csrf_token(600) ?>';
    form.appendChild(csrfInput);
    
    // Add type
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = '<?= htmlspecialchars($posttype_slug) ?>';
    form.appendChild(typeInput);
    
    // Add post_lang
    const langInput = document.createElement('input');
    langInput.type = 'hidden';
    langInput.name = 'post_lang';
    langInput.value = '<?= htmlspecialchars($currentLang) ?>';
    form.appendChild(langInput);
    
    document.body.appendChild(form);
    form.submit();
  }
}">

<?php
// Tạo mảng sao chép $_GET, rồi xóa post_lang để trở về ALL
$allParams = $_GET;
unset($allParams['post_lang']);

// Nút ALL
$allBtnClasses = 'btn btn-secondary';
if (is_null($currentLang)) {
  // Nếu đang ở ALL thì có thể thêm "active" hoặc style khác
  $allBtnClasses = 'btn btn-primary';
}
?>
<!-- [ Main Content ] start -->
<div class="pc-container">
  <div class="pc-content relative">

    <!-- Header & Description -->
    <div class="flex flex-col gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-foreground"><?= __('Posts Management') ?></h1>
        <p class="text-muted-foreground"><?= __('Manage system posts and their content') ?></p>
      </div>

      <!-- Thông báo -->
      <?php if (Session::has_flash('success')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
      <?php endif; ?>
      <?php if (Session::has_flash('error')): ?>
        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
      <?php endif; ?>
    </div>
    
    <!-- Status Filter Options -->
    <?php
    $statusOptions = [
        '' => __('All Statuses'),
        'active' => __('Active'),
        'pending' => __('Pending'),
        'inactive' => __('Inactive'),
        'schedule' => __('Scheduled'),
        'draft' => __('Draft'),
        'deleted' => __('Deleted')
    ];
    $currentStatus = S_GET('status') ?? '';
    ?>

    <!-- Tabs ngôn ngữ -->
    <div class="mb-4">
      <div role="tablist" aria-orientation="horizontal" class="inline-flex p-1 items-center justify-center rounded-md bg-muted text-muted-foreground">
        <?php 
        $langParams = $allParams;
        foreach ($languages as $lang): 
          $langParams['post_lang'] = $lang;
          $isActive = ($lang == $currentLang);
        ?>
          <a href="<?= admin_url('posts') . '?' . http_build_query($langParams) ?>">
            <button type="button" role="tab" 
              aria-selected="<?= $isActive ? 'true' : 'false' ?>" 
              data-state="<?= $isActive ? 'active' : 'inactive' ?>"
              class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2 <?= $isActive ? 'bg-background text-foreground shadow-sm' : 'bg-transparent text-muted-foreground' ?>">
              <?= strtoupper($lang) ?>
            </button>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="bg-card rounded-xl mb-4">
      <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto flex-wrap">
          <div class="relative flex-1 min-w-[60px] w-full sm:w-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
            <input 
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10" 
              placeholder="<?= __('Search') ?>..." 
              name="q" 
              value="<?= htmlspecialchars($search) ?>"
              @keydown.enter="$event.target.closest('form').submit()"
            />
          </div>
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="type" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <?php
              foreach ($allPostType as $item): ?>
                <option value="<?= $item['slug'] ?>" <?= $item['slug'] ==$posttype_slug ? 'selected':''  ?>><?= __($item['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="min-w-[120px] w-full sm:w-auto">
            <select name="status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= $value ?>" <?= $value === $currentStatus ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="min-w-[100px] w-full sm:w-auto">
            <select name="limit" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value="5" <?= ($limit == 5)  ? 'selected' : '' ?>>5</option>
              <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
              <option value="15" <?= ($limit == 15) ? 'selected' : '' ?>>15</option>
              <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
              <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
              <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
              <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
              <option value="200" <?= ($limit == 200) ? 'selected' : '' ?>>200</option>
              <option value="500" <?= ($limit == 500) ? 'selected' : '' ?>>500</option>
            </select>
          </div>
        </div>
        
        <!-- Hidden inputs to preserve other params -->
        <?php if (!empty($sort)) echo '<input type="hidden" name="sort" value="' . htmlspecialchars($sort) . '">'; ?>
        <?php if (!empty($order)) echo '<input type="hidden" name="order" value="' . htmlspecialchars($order) . '">'; ?>
        <?php if (!empty($currentLang)) echo '<input type="hidden" name="post_lang" value="' . htmlspecialchars($currentLang) . '">'; ?>
        
        <div class="flex gap-2 w-full lg:w-auto">
          <!-- Actions Dropdown -->
          <div x-data="{ open: false }" class="relative inline-block text-left w-full lg:w-auto" @click.away="open = false">
            <button @click="open = !open" type="button" 
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap w-full">
              <i data-lucide="menu" class="h-4 w-4 mr-2"></i>
              <?= __('Actions') ?>
              <i data-lucide="chevron-down" class="h-4 w-4 ml-2" x-bind:class="{ 'rotate-180': open }"></i>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-card border shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                 style="display: none;">
              <div class="py-1">
                <a href="<?= admin_url('posts/add') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $currentLang]) ?>" 
                   class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-accent transition-colors">
                  <i data-lucide="plus" class="h-4 w-4 mr-3"></i>
                  <?= __('Add New') ?>
                </a>
                
                <div class="border-t my-1"></div>
                
                <a href="<?= admin_url('posts/import') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $currentLang]) ?>" 
                   class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-accent transition-colors">
                  <i data-lucide="upload" class="h-4 w-4 mr-3"></i>
                  <?= __('Import') ?>
                </a>
                
                <a href="<?= admin_url('posts/export') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $currentLang]) ?>" 
                   class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-accent transition-colors">
                  <i data-lucide="download" class="h-4 w-4 mr-3"></i>
                  <?= __('Export') ?>
                </a>
                
                <div class="border-t my-1"></div>
                
                <button type="button" @click="bulkEdit()"
                   class="flex items-center w-full px-4 py-2 text-sm text-left text-foreground hover:bg-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                  <i data-lucide="table" class="h-4 w-4 mr-3"></i>
                  <?= __('Bulk Edit') ?>
                  <span x-show="selectedItems.length > 0" class="ml-auto text-xs bg-primary text-primary-foreground px-2 py-0.5 rounded-full" x-text="selectedItems.length"></span>
                </button>
                
                <button type="button" @click="deleteSelected()" :disabled="selectedItems.length === 0 || isDeleting"
                   class="flex items-center w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                  <i x-show="!isDeleting" data-lucide="trash2" class="h-4 w-4 mr-3"></i>
                  <i x-show="isDeleting" data-lucide="loader-2" class="h-4 w-4 mr-3 animate-spin"></i>
                  <span x-text="isDeleting ? '<?= __('Deleting...') ?>' : '<?= __('Delete') ?>'"></span>
                  <span x-show="selectedItems.length > 0 && !isDeleting" class="ml-auto text-xs bg-red-600 text-white px-2 py-0.5 rounded-full" x-text="selectedItems.length"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  
    
  <!-- Bảng danh sách -->
  <div class="bg-card card-content !p-0 border overflow-hidden">
    <div class="overflow-x-auto">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm table-fixed min-w-[1000px]">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
              <!-- Checkbox Select All -->
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-10">
                <input type="checkbox" id="selectAll" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" @change="toggleSelectAll()">
              </th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium w-12 whitespace-nowrap"><?= __('ID') ?></th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Title') ?></th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Categories') ?></th>
              <th class="px-2 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-32 whitespace-nowrap"><?= __('Status') ?></th>
              <th class="px-2 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-20 whitespace-nowrap"><?= __('Views') ?></th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium w-40 whitespace-nowrap"><?= __('Created') ?></th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Languages') ?></th>
              <th class="px-2 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-24 whitespace-nowrap"><?= __('Actions') ?></th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <?php if (!empty($data)): ?>
              <?php foreach ($data as $post): ?>
                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                  <!-- Checkbox -->
                  <td class="px-4 py-1 align-middle text-center">
                    <input type="checkbox" class="row-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" 
                           value="<?= $post['id'] ?>" @change="updateSelectedItems()">
                  </td>
                  <td class="px-2 py-1 align-middle font-medium whitespace-nowrap">
                    <a href="<?= admin_url('posts/edit/' . urlencode($post['id'])) . '?' . http_build_query([
                                'type'      => $posttype_slug,
                                'post_lang' => $currentLang,
                              ]) ?>" class="text-primary hover:underline hover:text-primary/80 transition-colors">
                      <?= isset($post['id']) && !empty( $post['id'] ) ?  htmlspecialchars( $post['id'] ) : 'N/A' ?>
                    </a>
                  </td>
                  <td class="px-2 py-1 align-middle text-foreground">
                    <?php 
                    $previewPrefix = isset($post['status']) && $post['status'] != 'active' ? '?preview=true' : '';
                    if (!empty($post['slug'])): ?>
                      <a href="<?= link_posts($post['slug'], $posttype_slug, $currentLang).$previewPrefix ?>" 
                         class="text-primary font-bold hover:underline hover:text-primary/80 transition-colors"
                         target="_blank"
                         title="<?= isset($post['title']) && !empty( trim($post['title']) ) ?  htmlspecialchars(trim($post['title'])) : 'N/A Title' ?>">
                        <?= isset($post['title']) && !empty( trim($post['title']) ) ?  htmlspecialchars(trim($post['title'])) : 'N/A Title' ?>
                      </a>
                    <?php else: ?>
                      <?= isset($post['title']) && !empty( trim($post['title']) ) ?  htmlspecialchars(trim($post['title'])) : 'N/A Title' ?>
                    <?php endif; ?>
                  </td>
                  <td class="px-2 py-1 align-middle">
                    <?php
                    $categories = $post['categories'] ?? [];
                    if (!empty($categories)) {
                      $catLinks = [];
                      foreach ($categories as $cat) {
                        $catSlug = $cat['slug'] ?? '';
                        $catName = $cat['name'] ?? 'Unknown';
                        if (!empty($catSlug)) {
                          $catLinks[] = '<a href="' . link_category($catSlug, $posttype_slug, $currentLang) . '" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border border-transparent bg-accent text-accent-foreground hover:bg-accent/80 transition-colors" target="_blank" title="' . htmlspecialchars($catName) . '">' . htmlspecialchars($catName) . '</a>';
                        } else {
                          $catLinks[] = '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-muted text-muted-foreground">' . htmlspecialchars($catName) . '</span>';
                        }
                      }
                      echo '<div class="flex flex-wrap gap-1">' . implode('', $catLinks) . '</div>';
                    } else {
                      echo '<span class="text-muted-foreground text-xs">—</span>';
                    }
                    ?>
                  </td>
                  <td class="px-2 py-1 align-middle text-center">
                    <?php
                    $postStatus = $post['status'] ?? 'draft';
                    $statusColors = [
                        'active' => 'bg-primary text-primary-foreground',
                        'pending' => 'bg-amber-100 text-amber-700',
                        'inactive' => 'bg-secondary text-secondary-foreground',
                        'schedule' => 'bg-amber-100 text-amber-700',
                        'draft' => 'bg-slate-100 text-slate-700',
                        'deleted' => 'bg-red-100 text-red-700'
                    ];
                    $statusColor = $statusColors[$postStatus] ?? 'bg-secondary text-secondary-foreground';
                    ?>
                    <select 
                      class="inline-flex items-center rounded-full border-0 px-2.5 py-1 text-xs font-semibold <?= $statusColor ?> cursor-pointer"
                      onchange="changePostStatus(<?= $post['id'] ?>, this.value, '<?= $postStatus ?>', event)">
                      <option value="active" <?= $postStatus === 'active' ? 'selected' : '' ?>><?= __('Active') ?></option>
                      <option value="pending" <?= $postStatus === 'pending' ? 'selected' : '' ?>><?= __('Pending') ?></option>
                      <option value="inactive" <?= $postStatus === 'inactive' ? 'selected' : '' ?>><?= __('Inactive') ?></option>
                      <option value="schedule" <?= $postStatus === 'schedule' ? 'selected' : '' ?>><?= __('Schedule') ?></option>
                      <option value="draft" <?= $postStatus === 'draft' ? 'selected' : '' ?>><?= __('Draft') ?></option>
                      <option value="deleted" <?= $postStatus === 'deleted' ? 'selected' : '' ?>><?= __('Deleted') ?></option>
                    </select>
                  </td>
                  <td class="px-2 py-1 align-middle text-center font-medium text-foreground whitespace-nowrap">
                    <?= number_format($post['views'] ?? 0) ?>
                  </td>
                  <td class="px-2 py-1 align-middle text-foreground whitespace-nowrap">
                    <?php 
                    if (!empty($post['created_at'])) {
                      $timestamp = strtotime($post['created_at']);
                      echo date('Y-m-d H:i', $timestamp);
                    } else {
                      echo '<span class="text-muted-foreground">—</span>';
                    }
                    ?>
                  </td>
                  <td class="px-2 py-1 align-middle">
                    <?php
                    $post_languages = $post['languages'] ?? [];
                    $langs_not_post = array_diff($posttype_languages, $post_languages);
                    $links = [];
                    
                    if (!empty($post_languages)) {
                      foreach ($post_languages as $lang) {
                        if (empty($lang)) continue;
                        $editParams = [
                          'type'      => $posttype_slug,
                          'post_lang' => $lang,
                        ];
                        $editUrl = admin_url('posts/edit/' . urlencode($post['id']))
                          . '?' . http_build_query($editParams);

                        $links[] = '<a href="' . htmlspecialchars($editUrl) . '" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-primary text-primary-foreground hover:bg-primary/90 transition-colors" title="' . __('Edit Post') . '"><i data-lucide="square-pen" class="h-3 w-3"></i><span>'
                          . htmlspecialchars(strtoupper($lang)) . '</span></a>';
                      }
                    }
                    
                    if (!empty($langs_not_post)) {
                      foreach ($langs_not_post as $lang) {
                        $cloneParams = [
                          'type'      => $posttype_slug,
                          'post_lang' => $lang,
                          'oldpost_lang' => $currentLang,
                        ];
                        $cloneUrl = admin_url('posts/clone/' . $post['id'])
                          . '?' . http_build_query($cloneParams);
                        $links[] = '<a href="' . htmlspecialchars($cloneUrl) . '" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-secondary text-secondary-foreground hover:bg-secondary/80 transition-colors" title="' . __('Add Post') . ': ' . strtoupper($lang) . '"><i data-lucide="plus" class="h-3 w-3"></i><span>'
                          . htmlspecialchars(strtoupper($lang)) . '</span></a>';
                      }
                    }
                    echo '<div class="flex flex-wrap gap-1">' . implode('', $links) . '</div>';
                    ?>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-1 justify-center">
                      <?php if (!empty($post['id'])): ?>
                        <a href="<?= admin_url('posts/edit/' . urlencode($post['id'])) . '?' . http_build_query([
                                    'type'      => $posttype_slug,
                                    'post_lang' => $currentLang,
                                  ]) ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" title="<?= __('Edit Post') ?>">
                          <i data-lucide="square-pen" class="h-4 w-4"></i>
                        </a>
                        <a href="<?= admin_url('posts/delete/' . urlencode($post['id'])) . '?' . http_build_query([
                                    'type'      => $posttype_slug,
                                    'post_lang' => $currentLang,
                                  ]) ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" onclick="return confirm('<?= __('Are you sure you want to delete this item?') ?>');" title="<?= __('Delete Post') ?>">
                          <i data-lucide="trash2" class="h-4 w-4"></i>
                        </a>
                      <?php else: ?>
                        <span class="text-muted-foreground text-xs">N/A</span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="9" class="text-center py-4 text-muted-foreground"><?= __('No posts found.') ?></td></tr>
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
        $total = $posts['total'] ?? count($data);
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
        // Cấu hình param build link pagination
        $query_params = [];
        if (!empty($search)) {
          $query_params['q'] = $search;
        }
        if ($limit != 10) {
          $query_params['limit'] = $limit;
        }
        if (!empty($sort)) {
          $query_params['sort'] = $sort;
        }
        if (!empty($order)) {
          $query_params['order'] = $order;
        }
        if (!empty($posttype_slug) && $posttype_slug !== 'post') {
          $query_params['type'] = $posttype_slug;
        }
        // Giữ post_lang nếu có
        if (!empty($currentLang)) {
          $query_params['post_lang'] = $currentLang;
        }
        // Giữ status filter nếu có
        if (!empty($currentStatus)) {
          $query_params['status'] = $currentStatus;
        }

        // Gọi hàm Render::pagination(...)
        echo Render::pagination(
          admin_url('posts/index'),
          $page,
          $is_next,
          $query_params
        );
        ?>
      </div>
    </div>
  </div>
</div>
<!-- [ Main Content ] end -->
</div>
<script>

  function showNotification(kind, msg) {
      const type = (kind === 'success' || kind === 'error' || kind === 'warning' || kind === 'info') ? kind : 'info';
      if (window.fastNotice && typeof window.fastNotice.show === 'function') {
          window.fastNotice.show(msg, type);
      } else if (window.FastNotice && typeof window.FastNotice.show === 'function') {
          window.FastNotice.show(msg, type);
      } else {
          alert(msg);
      }
  }
  function changePostStatus(postId, newStatus, oldStatus, event) {
    const selectEl = event.target;
    
    // Nếu chuyển từ active sang trạng thái khác → cần confirm
    if (oldStatus === 'active' && newStatus !== 'active') {
      if (!confirm('<?= __('Are you sure you want to change status from Active?') ?>')) {
        // Reset select về giá trị cũ
        selectEl.value = oldStatus;
        return;
      }
    }
    
    // Update UI màu ngay lập tức
    const statusColors = {
      'active': 'bg-primary text-primary-foreground',
      'pending': 'bg-amber-100 text-amber-700',
      'inactive': 'bg-secondary text-secondary-foreground',
      'schedule': 'bg-amber-100 text-amber-700',
      'draft': 'bg-slate-100 text-slate-700',
      'deleted': 'bg-red-100 text-red-700'
    };
    
    // Remove all color classes
    Object.values(statusColors).forEach(colorClass => {
      colorClass.split(' ').forEach(cls => selectEl.classList.remove(cls));
    });
    
    // Add new color classes
    const newColorClass = statusColors[newStatus] || statusColors['draft'];
    newColorClass.split(' ').forEach(cls => selectEl.classList.add(cls));
    
    // Send AJAX request
    fetch('<?= admin_url('posts/changestatus') ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({
        id: postId,
        status: newStatus,
        type: '<?= htmlspecialchars($posttype_slug) ?>',
        post_lang: '<?= htmlspecialchars($currentLang) ?>',
        csrf_token: '<?= Session::csrf_token() ?>'
      })
    })
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        // Revert trạng thái nếu lỗi
        selectEl.value = oldStatus;
        Object.values(statusColors).forEach(colorClass => {
          colorClass.split(' ').forEach(cls => selectEl.classList.remove(cls));
        });
        const oldColorClass = statusColors[oldStatus] || statusColors['draft'];
        oldColorClass.split(' ').forEach(cls => selectEl.classList.add(cls));
        
        showNotification('error', data.message || '<?= __('Failed to update status') ?>');
      }else{
        showNotification('success', data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      // Revert
      selectEl.value = oldStatus;
      Object.values(statusColors).forEach(colorClass => {
        colorClass.split(' ').forEach(cls => selectEl.classList.remove(cls));
      });
      const oldColorClass = statusColors[oldStatus] || statusColors['draft'];
      oldColorClass.split(' ').forEach(cls => selectEl.classList.add(cls));
      
      showNotification('error', JSON.stringify(error));
    });
  }

</script>
<?php
Render::block('Backend\Footer', ['layout' => 'default']);
?>


