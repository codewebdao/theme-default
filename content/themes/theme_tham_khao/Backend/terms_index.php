<?php

use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Render;
// Load language files
Flang::load('Terms', APP_LANG);

// allTerm to options for languages tức là chuyển thành option select cho từng ngôn ngữ
$termsLanguages = [];
foreach ($allTerm as $term) {
  $termsLanguages[$term['lang']][] = $term;
}

// Pagination data
$data_terms = $allTerm;
$is_next = $is_next ?? 0;
$page_num = $page ?? 1;
$total_count = $total ?? count($data_terms);

// Lấy các tham số GET
$search      = S_GET('q')        ?? '';
$limit       = S_GET('limit')    ?? 20;
$type        = S_GET('type')     ?? '';
$posttype    = S_GET('posttype') ?? '';
$post_lang   = S_GET('post_lang') ?? '';
$posttypeData = [];
$termData = [];
if (!empty($allPostTypes)){
  foreach ($allPostTypes as $item){
    if ($item['slug'] == $posttype){
      $posttypeData = $item;
      break;
    }
  }
}
if (!empty($termsInfo)){
  foreach ($termsInfo as $termType){
    if ($termType['type'] == $type){
      $termData = $termType;
      break;
    }
  }
  if (empty($termData)){
    $termData = $termsInfo[0];
  }
}


$breadcrumbs = array(
  [
    'name' => __('Dashboard'),
    'url' => admin_url('home')
  ]
);

if (!empty($posttypeData)){
  $breadcrumbs[] = [
    'name' => __($posttypeData['name']),
    'url' => admin_url('terms/?posttype=' . $posttype . '&post_lang=' . $post_lang),
    'active' => empty($termData)
  ];
}

if (!empty($termData)){
  $breadcrumbs[] = [
    'name' => __($termData['name']),
    'url' => admin_url('terms/?posttype=' . $posttype . '&type=' . $type),
    'active' => true
  ];
}
Render::block('Backend\Header', ['layout' => 'default', 'title' => $title, 'breadcrumb' => $breadcrumbs]);

function buildOptions($tree, $level = 0, $current_id = null, $parent = null)
{
  $output = '';

  foreach ($tree as $node) {
    // Tạo dấu gạch dựa theo cấp độ
    $prefix = str_repeat('-', $level);
    // Không hiển thị chính node hiện tại trong danh sách cha
    if ($node['id'] == $current_id) {
      continue;
    }

    // Thiết lập `selected` nếu node hiện tại là `parent_id`
    $selected = ($node['id'] == $parent) ? ' selected' : '';
    // Xây dựng option
    $output .= '<option value="' . $node['id'] . '"' . $selected . '>' . $prefix . ' ' . $node['name'] . '</option>';

    // Nếu có children, đệ quy để xây dựng tiếp các options
    if (!empty($node['children'])) {
      $output .= buildOptions($node['children'], $level + 1, $current_id, $parent);
    }
  }

  return $output;
}
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
      formData.append('type', '<?= htmlspecialchars($type) ?>');
      formData.append('posttype', '<?= htmlspecialchars($posttype) ?>');
      
      const response = await fetch('<?= admin_url('terms/delete') ?>', {
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
      <h1 class="text-2xl font-bold text-foreground"><?= __('Terms Management') ?></h1>
      <p class="text-muted-foreground"><?= __('Manage terms and categories for your content') ?></p>
    </div>

    <!-- Thông báo -->
    <?php if (Session::has_flash('success')): ?>
      <?php Render::block('Backend\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php Render::block('Backend\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
    <?php endif; ?>

    <!-- Tabs ngôn ngữ -->
    <div class="mb-4">
      <div role="tablist" aria-orientation="horizontal" class="inline-flex p-1 items-center justify-center rounded-md bg-muted text-muted-foreground">
        <?php 
        $currentLang = is_string($posttypeData['languages']) ? json_decode($posttypeData['languages'], true) : $posttypeData['languages'];
        foreach ($currentLang as $lang): 
          $isActive = ($lang == $post_lang);
        ?>
          <a href="<?= admin_url('terms') . '?' . http_build_query([
            'posttype' => $posttype,
            'type' => $type,
            'post_lang' => $lang,
            'q' => $search,
            'limit' => $limit
          ]) ?>">
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

    <!-- Search and Filter Section -->
    <div class="bg-card rounded-xl mb-4">
      <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between" onsubmit="return cleanEmptySearch(this)">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto">
          <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
            <input
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10"
              placeholder="<?= __('Search') ?>..."
              name="q"
              value="<?= htmlspecialchars($search) ?>"
              @keydown.enter="$event.target.closest('form').submit()" />
          </div>
          
          <!-- Posttype Filter -->
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="posttype" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <?php foreach ($allPostTypes as $item): ?>
                <option value="<?= $item['slug'] ?>" <?= $item['slug'] == $posttype ? 'selected' : '' ?>><?= __($item['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <!-- Type Filter -->
          <?php if(!empty($termsInfo)): ?>
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="type" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <?php foreach ($termsInfo as $termType): ?>
                <option value="<?= $termType['type'] ?>" <?= $termType['type'] == $type ? 'selected' : '' ?>><?= __($termType['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          
          <!-- Limit Filter -->
          <div class="min-w-[100px] w-full sm:w-auto">
            <select name="limit" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
              <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
              <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
              <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
              <option value="200" <?= ($limit == 200) ? 'selected' : '' ?>>200</option>
            </select>
          </div>
        </div>

        <!-- Hidden inputs to preserve other params -->
        <?php if (!empty($post_lang)) echo '<input type="hidden" name="post_lang" value="' . htmlspecialchars($post_lang) . '">'; ?>

        <div class="flex gap-2">
          <!-- Delete Selected Button -->
          <button
            type="button"
            @click="deleteSelected()"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 h-10 px-4 py-2 whitespace-nowrap"
            :class="selectedItems.length > 0 ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
            :disabled="isDeleting || selectedItems.length === 0">
            <i x-show="!isDeleting" data-lucide="trash2" class="h-4 w-4 mr-2"></i>
            <i x-show="isDeleting" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
            <span x-text="isDeleting ? '<?= __('Deleting...') ?>' : '<?= __('Delete Selected') ?>'"></span>
          </button>

          <!-- Add New Button -->
          <a
            href="<?= admin_url('terms/add?posttype=' . $posttype . '&type=' . $type . '&post_lang=' . ($post_lang)) ?>"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap w-full lg:w-auto">
            <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
            <span><?= __('Add Term')?></span>
          </a>
        </div>
      </form>
    </div>
  </div>


  <!-- Bảng danh sách -->
  <div class="bg-card card-content !p-0 border overflow-hidden">
    <div class="overflow-x-auto">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm min-w-[1100px]">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
              <!-- Checkbox Select All -->
              <th class="px-2 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium w-12 whitespace-nowrap">
                <input type="checkbox" id="selectAll" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" @change="toggleSelectAll()">
              </th>
              <?php
              // Helper to build sort link while keeping filters
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
              <th class="px-2 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors w-16 whitespace-nowrap"><?php echo sort_link(__('ID'), 'id', $sort, $order); ?></th>
              <th class="px-2 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Name'), 'name', $sort, $order); ?></th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Parent') ?></th>
              <th class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Languages') ?></th>
              <th class="px-2 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors w-40 whitespace-nowrap"><?php echo sort_link(__('Created'), 'created_at', $sort, $order); ?></th>
              <th class="px-2 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Status') ?></th>
              <th class="px-2 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Actions') ?></th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <?php
            function renderTermRows($nodes, $level = 0, $currentLang = [], $post_lang = '')
            {
              foreach ($nodes as $node) {
                if (!$node) continue;
            ?>
                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                  <!-- Checkbox -->
                  <td class="px-2 py-1 align-middle text-center">
                    <input type="checkbox"
                      class="row-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                      value="<?= $node['id'] ?>"
                      @change="updateSelectedItems()">
                  </td>

                  <!-- ID with Edit Link -->
                  <td class="px-2 py-1 align-middle font-medium whitespace-nowrap">
                    <a href="<?= admin_url('terms/edit/' . $node['id'] . '?posttype=' . $node['posttype'] . '&type=' . $node['type']); ?>"
                      class="text-primary hover:underline hover:text-primary/80 transition-colors">
                      <?= htmlspecialchars($node['id'] ?? 'N/A') ?>
                    </a>
                  </td>

                  <!-- Name with Frontend Link -->
                  <?php 
                  $isBold = empty($node['parent']) || $node['parent'] == 0;
                  ?>
                  <td class="px-2 py-1 align-middle text-foreground">
                    <a href="<?= link_terms($node['type'], $node['slug'], $node['posttype'], $post_lang); ?>"
                      class="text-primary hover:underline hover:text-primary/80 transition-colors <?= $isBold ? 'font-bold' : '' ?>"
                      target="_blank">
                      <?= str_repeat('&mdash; ', $level) . htmlspecialchars($node['name']); ?>
                    </a>
                  </td>

                  <!-- Parent with Frontend Link -->
                  <td class="px-2 py-1 align-middle text-foreground whitespace-nowrap">
                    <?php if (!empty($node['parent_name'])): ?>
                      <?php 
                      // Get parent slug from node data (need to add this in controller)
                      $parentSlug = $node['parent_slug'] ?? '';
                      ?>
                      <?php if (!empty($parentSlug)): ?>
                        <a href="<?= link_terms($node['type'], $parentSlug, $node['posttype'], $post_lang); ?>"
                          class="text-primary hover:underline hover:text-primary/80 transition-colors"
                          target="_blank">
                          <?= htmlspecialchars($node['parent_name']); ?>
                        </a>
                      <?php else: ?>
                        <?= htmlspecialchars($node['parent_name']); ?>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted-foreground">—</span>
                    <?php endif; ?>
                  </td>

                  <!-- Languages -->
                  <td class="px-2 py-1 align-middle">
                    <?php
                    if(!empty($currentLang)) {
                      $links = [];
                      foreach($currentLang as $lang_term) {
                        if($lang_term == $post_lang) {
                          continue; // Skip current language
                        }
                        
                        if(!empty($node['lang_terms'][$lang_term])) {
                          // Edit existing term - Primary color
                          $editUrl = admin_url('terms/edit/' . $node['lang_terms'][$lang_term]['id'] . '?posttype=' . $node['lang_terms'][$lang_term]['posttype'] . '&type=' . $node['lang_terms'][$lang_term]['type']);
                          $links[] = '<a href="' . htmlspecialchars($editUrl) . '" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-primary text-primary-foreground hover:bg-primary/90 transition-colors" title="' . __('Edit Term') . ': ' . htmlspecialchars($node['lang_terms'][$lang_term]['name']) . '"><i data-lucide="square-pen" class="h-3 w-3"></i><span>' . htmlspecialchars(strtoupper($lang_term)) . '</span></a>';
                        } else {
                          // Add new term - Secondary color
                          $addUrl = admin_url('terms/add/' . $node['id'] . '?posttype=' . $node['posttype'] . '&type=' . $node['type'] . '&post_lang=' . $lang_term . '&mainterm=' . $node['id_main']);
                          $links[] = '<a href="' . htmlspecialchars($addUrl) . '" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-secondary text-secondary-foreground hover:bg-secondary/80 transition-colors" title="' . __('Add Term') . ': ' . strtoupper($lang_term) . '"><i data-lucide="plus" class="h-3 w-3"></i><span>' . htmlspecialchars(strtoupper($lang_term)) . '</span></a>';
                        }
                      }
                      echo '<div class="flex flex-wrap gap-1">' . implode('', $links) . '</div>';
                    }
                    ?>
                  </td>

                  <!-- Created At -->
                  <td class="px-2 py-1 align-middle text-foreground whitespace-nowrap">
                    <?php 
                    if (!empty($node['created_at'])) {
                      $timestamp = strtotime($node['created_at']);
                      echo date('Y-m-d H:i', $timestamp);
                    } else {
                      echo '<span class="text-muted-foreground">—</span>';
                    }
                    ?>
                  </td>

                  <!-- Status Toggle -->
                  <td class="px-2 py-1 align-middle text-center">
                    <div class="flex items-center gap-2 justify-center">
                      <button type="button" 
                        onclick="changeStatusTerm(<?= $node['id']; ?>, event);" 
                        class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background <?= ($node['status'] ?? 'active') === 'active' ? 'bg-primary' : 'bg-input' ?>">
                        <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform <?= ($node['status'] ?? 'active') === 'active' ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                      </button>
                      <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-transparent <?= ($node['status'] ?? 'active') === 'active' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground' ?>">
                        <?= ($node['status'] ?? 'active') === 'active' ? __('Active') : __('Inactive') ?>
                      </div>
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="px-2 py-1 align-middle text-center">
                    <div class="flex items-center gap-1 justify-center">
                      <a href="<?= admin_url('terms/edit/' . $node['id'] . '?posttype=' . $node['posttype'] . '&type=' . $node['type']); ?>"
                        class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0"
                        title="<?= __('Edit Term') ?>">
                        <i data-lucide="square-pen" class="h-4 w-4"></i>
                      </a>
                      <a href="<?= admin_url('terms/delete/' . $node['id'] . '?posttype=' . $node['posttype'] . '&type=' . $node['type']); ?>"
                        class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0"
                        onclick="return confirm('<?= __('Are you sure you want to delete this item?') ?>')"
                        title="<?= __('Delete Term') ?>">
                        <i data-lucide="trash2" class="h-4 w-4"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php
                // Nếu có children, tiếp tục gọi đệ quy để render các children
                if (!empty($node['children'])) {
                  renderTermRows($node['children'], $level + 1, $currentLang, $post_lang);
                }
              }
            }

            if (!empty($tree)) {
              renderTermRows($tree, 0, $currentLang, $post_lang);
            } else {
              ?>
              <tr>
                <td colspan="10" class="text-center py-4 text-muted-foreground">
                  <?= __('No terms found.') ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-3 border-t gap-4">
      <div class="text-sm text-muted-foreground">
        <?php
        $from = ($page_num - 1) * $limit + 1;
        $to = $from + count($data_terms) - 1;
        if ($total_count > 0) {
          _e('Showing %1% to %2% of %3% results', $from, $to, $total_count);
        } else {
          _e('No results');
        }
        ?>
      </div>
      <div class="flex items-center gap-2">
        <?php
        // Build query params for pagination
        $query_params = [];
        if (!empty($search)) {
          $query_params['q'] = $search;
        }
        if ($limit != 20) {
          $query_params['limit'] = $limit;
        }
        if (!empty($posttype)) {
          $query_params['posttype'] = $posttype;
        }
        if (!empty($type)) {
          $query_params['type'] = $type;
        }
        if (!empty($post_lang)) {
          $query_params['post_lang'] = $post_lang;
        }
        if (!empty($sort) && $sort !== 'id') {
          $query_params['sort'] = $sort;
        }
        if (!empty($order) && $order !== 'desc') {
          $query_params['order'] = $order;
        }

        // Render pagination
        echo Render::pagination(
          admin_url('terms/index'),
          $page_num,
          $is_next,
          $query_params
        );
        ?>
      </div>
    </div>
  </div>
</div>

<script>
// Clean empty search parameter before form submit
function cleanEmptySearch(form) {
  const searchInput = form.querySelector('input[name="q"]');
  if (searchInput && searchInput.value.trim() === '') {
    searchInput.removeAttribute('name');
  }
  return true;
}

// Change term status
function changeStatusTerm(id, event) {
  const button = event.currentTarget;
  const toggle = button.querySelector('span');
  const badge = button.nextElementSibling;
  const isActive = button.classList.contains('bg-primary');
  const newStatus = isActive ? 'inactive' : 'active';
  
  // Optimistic UI update
  if (isActive) {
    button.classList.remove('bg-primary');
    button.classList.add('bg-input');
    toggle.classList.remove('translate-x-5');
    toggle.classList.add('translate-x-0');
    badge.classList.remove('bg-primary', 'text-primary-foreground');
    badge.classList.add('bg-secondary', 'text-secondary-foreground');
    badge.textContent = '<?= __('Inactive') ?>';
  } else {
    button.classList.remove('bg-input');
    button.classList.add('bg-primary');
    toggle.classList.remove('translate-x-0');
    toggle.classList.add('translate-x-5');
    badge.classList.remove('bg-secondary', 'text-secondary-foreground');
    badge.classList.add('bg-primary', 'text-primary-foreground');
    badge.textContent = '<?= __('Active') ?>';
  }
  
  // Send AJAX request
  fetch('<?= admin_url('terms/changestatus') ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      id: id,
      status: newStatus,
      csrf_token: '<?= Session::csrf_token() ?>'
    })
  })
  .then(response => response.json())
  .then(data => {
    if (!data.success) {
      // Revert on error
      if (!isActive) {
        button.classList.remove('bg-primary');
        button.classList.add('bg-input');
        toggle.classList.remove('translate-x-5');
        toggle.classList.add('translate-x-0');
        badge.classList.remove('bg-primary', 'text-primary-foreground');
        badge.classList.add('bg-secondary', 'text-secondary-foreground');
        badge.textContent = '<?= __('Inactive') ?>';
      } else {
        button.classList.remove('bg-input');
        button.classList.add('bg-primary');
        toggle.classList.remove('translate-x-0');
        toggle.classList.add('translate-x-5');
        badge.classList.remove('bg-secondary', 'text-secondary-foreground');
        badge.classList.add('bg-primary', 'text-primary-foreground');
        badge.textContent = '<?= __('Active') ?>';
      }
      alert(data.message || '<?= __('Failed to update status') ?>');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    // Revert on error
    if (!isActive) {
      button.classList.remove('bg-primary');
      button.classList.add('bg-input');
      toggle.classList.remove('translate-x-5');
      toggle.classList.add('translate-x-0');
      badge.classList.remove('bg-primary', 'text-primary-foreground');
      badge.classList.add('bg-secondary', 'text-secondary-foreground');
      badge.textContent = '<?= __('Inactive') ?>';
    } else {
      button.classList.remove('bg-input');
      button.classList.add('bg-primary');
      toggle.classList.remove('translate-x-0');
      toggle.classList.add('translate-x-5');
      badge.classList.remove('bg-secondary', 'text-secondary-foreground');
      badge.classList.add('bg-primary', 'text-primary-foreground');
      badge.textContent = '<?= __('Active') ?>';
    }
  });
}
</script>

<?php Render::block('Backend\Footer', ['layout' => 'default']); ?>