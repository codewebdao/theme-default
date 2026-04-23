<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Posts', APP_LANG);

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
      'name' => __('Bulk Edit'),
      'url' => admin_url('posts/bulkedit'),
      'active' => true
  ]
);
view_header([
    'title' => __('Bulk Edit Posts'),
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);

// Get variables from controller data
$posttype_slug = $posttype['slug'] ?? 'post';
$postIds = $posts ?? [];
$currentLang = $currentLang ?? APP_LANG;
$search = S_GET('q') ?? '';
$limit = S_GET('limit') ?? 10;
$currentStatus = S_GET('status') ?? '';
?>

<!-- Load jspreadsheet v4 CSS & JS from CDN -->
<link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />
<link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v4/jexcel.css" type="text/css" />
<script src="https://jsuites.net/v4/jsuites.js"></script>
<script src="https://bossanova.uk/jspreadsheet/v4/jexcel.js"></script>

<!-- Load Spreadsheet Helper Library -->
<?php View::addJs('page-js-spreadsheet-helper-js', 'js/spreadsheet-helper.js', [], null, false, false, false, false); ?>

<!-- Custom CSS for row/cell height limits -->
<style>
#spreadsheet tbody tr {
  /*
    max-height: 100px !important;
    height: auto !important;
    */
}

</style>

<!-- [ Main Content ] start -->
<div class="pc-container">
  <div class="pc-content relative">

    <!-- Header & Description -->
    <div class="flex flex-col gap-4 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-foreground"><?= __('Bulk Edit Posts') ?></h1>
          <p class="text-muted-foreground"><?= __('Edit multiple posts at once using spreadsheet interface') ?></p>
        </div>
        <a href="<?= admin_url('posts') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $currentLang]) ?>" 
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">
          <i data-lucide="arrow-left" class="h-4 w-4 mr-2"></i>
          <?= __('Back to Posts') ?>
        </a>
      </div>

      <!-- Thông báo -->
      <?php if (Session::has_flash('success')): ?>
        <?php echo View::include('parts/ui/notification', ['type' => 'success', 'message' => Session::flash('success')]); ?>
      <?php endif; ?>
      <?php if (Session::has_flash('error')): ?>
        <?php echo View::include('parts/ui/notification', ['type' => 'error', 'message' => Session::flash('error')]); ?>
      <?php endif; ?>
    </div>
    
    <!-- Tabs ngôn ngữ -->
    <div class="mb-4">
      <div role="tablist" aria-orientation="horizontal" class="inline-flex p-1 items-center justify-center rounded-md bg-muted text-muted-foreground">
        <?php foreach ($languages as $lang): 
          $isActive = ($lang == $currentLang);
        ?>
          <a href="<?= admin_url('posts/bulkedit') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $lang]) ?>">
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
    
    <!-- Filter Form -->
    <div class="bg-card rounded-xl mb-4">
      <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto">
          <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
            <input 
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10" 
              placeholder="<?= __('Search') ?>..." 
              name="q" 
              value="<?= htmlspecialchars($search) ?>"
            />
          </div>
          
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="type" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <?php foreach ($allPostType as $item): ?>
                <option value="<?= $item['slug'] ?>" <?= $item['slug'] == $posttype_slug ? 'selected' : '' ?>><?= __($item['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
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
              foreach ($statusOptions as $value => $label): ?>
                <option value="<?= $value ?>" <?= $value === $currentStatus ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="min-w-[100px] w-full sm:w-auto">
            <select name="limit" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
              <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
              <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
              <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
              <option value="200" <?= ($limit == 200) ? 'selected' : '' ?>>200</option>
              <option value="500" <?= ($limit == 500) ? 'selected' : '' ?>>500</option>
            </select>
          </div>
        </div>
        
        <input type="hidden" name="post_lang" value="<?= htmlspecialchars($currentLang) ?>">
        
        <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full lg:w-auto whitespace-nowrap">
          <i data-lucide="filter" class="h-4 w-4 mr-2"></i>
          <?= __('Apply') ?>
        </button>
      </form>
    </div>

    <?php if (empty($postIds)): ?>
      <div class="bg-card rounded-xl p-8 text-center">
        <i data-lucide="alert-circle" class="h-12 w-12 mx-auto mb-4 text-muted-foreground"></i>
        <h3 class="text-lg font-semibold mb-2"><?= __('No posts selected') ?></h3>
        <p class="text-muted-foreground mb-4"><?= __('Please select posts from the posts list to edit them in bulk.') ?></p>
        <a href="<?= admin_url('posts') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $currentLang]) ?>" 
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
          <?= __('Go to Posts List') ?>
        </a>
      </div>
    <?php else: ?>

      <!-- Spreadsheet Container -->
      <div class="bg-card rounded overflow-hidden">
        <div class="px-2 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap">
          <h3 class="font-semibold text-foreground flex items-center gap-2">
            <i data-lucide="table" class="h-5 w-5"></i>
            <?= __('Editing') ?> <?= count($postIds) ?> <?= __('posts') ?>
          </h3>
        </div>
        <div class="overflow-x-auto">
          <div id="spreadsheet" class="min-w-full"></div>
          <div id="loading" class="text-center py-8">
            <i data-lucide="loader-2" class="h-8 w-8 animate-spin mx-auto mb-2 text-primary"></i>
            <p class="text-muted-foreground"><?= __('Loading posts...') ?></p>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="mt-4 flex flex-col sm:flex-row items-center justify-between text-sm text-muted-foreground gap-4">
        <div id="save-status" class="flex items-center gap-2"></div>
      </div>
      
      <!-- Pagination -->
      <?php if (!empty($pagination)): ?>
      <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-3 gap-4 bg-card rounded-xl">
        <div class="text-sm text-muted-foreground">
          <?php
          $total = $pagination['total'] ?? count($postIds);
          $page = $pagination['page'] ?? 1;
          $from = ($page - 1) * $limit + 1;
          $to = $from + count($postIds) - 1;
          if ($total > 0) {
            _e('Showing %1% to %2% of %3% results', $from, $to, $total);
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
          if ($limit != 10) {
            $query_params['limit'] = $limit;
          }
          if (!empty($posttype_slug)) {
            $query_params['type'] = $posttype_slug;
          }
          if (!empty($currentLang)) {
            $query_params['post_lang'] = $currentLang;
          }
          if (!empty($currentStatus)) {
            $query_params['status'] = $currentStatus;
          }

          // Render pagination
          echo view_pagination(
            admin_url('posts/bulkedit'),
            $page,
            $pagination['is_next'] ?? 0,
            $query_params
          );
          ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
<!-- [ Main Content ] end -->

<script>
let spreadsheetInstance = null;
let isUpdating = false;
const postIds = <?= json_encode($postIds) ?>;

// Load posts data
async function loadPostsData() {
  try {
    const formData = new FormData();
    formData.append('post_ids', postIds.join(','));
    formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
    formData.append('type', '<?= htmlspecialchars($posttype_slug) ?>');
    formData.append('post_lang', '<?= htmlspecialchars($currentLang) ?>');
    
    const response = await fetch('<?= admin_url('posts/getbulkposts') ?>', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.success && result.data) {
      initSpreadsheet(result.data, result.term_types || [], result.posttype_fields || [], result.terms_data || {});
    } else {
      throw new Error(result.message || 'Failed to load posts');
    }
  } catch (error) {
    console.error('Error loading posts:', error);
    document.getElementById('loading').innerHTML = `
      <i data-lucide="alert-circle" class="h-8 w-8 mx-auto mb-2 text-red-500"></i>
      <p class="text-red-600">${error.message}</p>
    `;
    lucide.createIcons();
  }
}

// Initialize spreadsheet
function initSpreadsheet(posts, termTypes, posttypeFields, termsData) {
  if (!posts || posts.length === 0) {
    throw new Error('No posts to display');
  }
  
  // Store termsData globally for later use
  window.bulkEditTermsData = termsData || {};
  
  // Get all keys from first post to determine columns
  const firstPost = posts[0];
  const allKeys = Object.keys(firstPost);
  
  // Build list of valid custom field names from posttype definition
  const posttypeFieldNames = [];
  if (posttypeFields && posttypeFields.length > 0) {
    posttypeFields.forEach(field => {
      if (field.field_name) {
        posttypeFieldNames.push(field.field_name);
      }
    });
  }
  
  // Base columns - always available in any posttype
  const baseColumns = ['id', 'title', 'slug', 'status', 'created_at', 'updated_at'];
  
  // Internal fields - should not be displayed
  const internalFields = ['posttype', 'lang', 'search_string'];
  
  // Term columns (terms:...)
  const termColumns = allKeys.filter(key => key.startsWith('terms:'));
  
  // Custom columns - only include if they are in posttype fields definition
  const customColumns = allKeys.filter(key => {
    if (baseColumns.includes(key)) return false;
    if (key.startsWith('terms:')) return false;
    if (internalFields.includes(key)) return false;
    return posttypeFieldNames.includes(key);
  });
  
  // Column order: id, title, slug, status + custom fields + terms + created_at, updated_at (at end)
  const columnOrder = ['id', 'title', 'slug', 'status', ...customColumns, ...termColumns, 'created_at', 'updated_at'];
  
  // ✅ Build fields maps and store in spreadsheetHelper
  if (posttypeFields && posttypeFields.length > 0) {
    posttypeFields.forEach(field => {
      if (field.field_name) {
        spreadsheetHelper.fieldsTypeMap[field.field_name] = field.type;
        
        if (field.data) {
          spreadsheetHelper.fieldsDataMap[field.field_name] = field.data;
        }
      }
    });
  }
  
  // Store terms data
  spreadsheetHelper.termsData = termsData;
  
  // Prepare data for spreadsheet
  const data = posts.map(post => {
    return columnOrder.map(key => {
      const value = post[key];
      
      if (value === undefined || value === null) {
        return '';
      }
      
      // ✅ Use SpreadsheetHelper to format values
      const fieldType = spreadsheetHelper.fieldsTypeMap[key];
      const fieldData = spreadsheetHelper.fieldsDataMap[key];
      
      return spreadsheetHelper.formatCellValue(key, value, fieldType, fieldData);
    });
  });
  
  // Prepare column definitions - Use SpreadsheetHelper
  const columns = [
    { type: 'text', title: '<?= __('ID') ?>', width: 80, readOnly: true },
  ];
  
  // Add base columns
  const baseFields = ['title', 'slug', 'status'];
  baseFields.forEach(fieldName => {
    if (!columnOrder.includes(fieldName)) return;
    
    if (fieldName === 'title') {
      columns.push({ type: 'text', title: '<?= __('Title') ?>', width: 300 });
    } else if (fieldName === 'slug') {
      columns.push({ type: 'text', title: '<?= __('Slug') ?>', width: 250 });
    } else if (fieldName === 'status') {
      columns.push({ 
        type: 'dropdown', 
        title: '<?= __('Status') ?>', 
        width: 120, 
        source: ['active', 'pending', 'inactive', 'schedule', 'draft', 'deleted'] 
      });
    }
  });
  
  // ✅ Add custom field columns using SpreadsheetHelper
  customColumns.forEach(colName => {
    const fieldDef = posttypeFields.find(f => f.field_name === colName);
    if (fieldDef) {
      const fieldData = spreadsheetHelper.fieldsDataMap[colName];
      const columnDef = spreadsheetHelper.getColumnDefinition(fieldDef, fieldData);
      columns.push(columnDef);
    } else {
      // Fallback if field not found
    columns.push({
      type: 'text',
      title: colName.charAt(0).toUpperCase() + colName.slice(1).replace(/_/g, ' '),
      width: 200
    });
    }
  });
  
  // ✅ Add term columns - text with custom multi-select editor
  termColumns.forEach(termCol => {
    const termType = termCol.substring(6);
    columns.push({
      type: 'text',
      title: termType.charAt(0).toUpperCase() + termType.slice(1),
      width: 200,
      placeholder: 'Click to select...'
    });
  });
  
  // ✅ Add datetime columns if exist in columnOrder
  if (columnOrder.includes('created_at')) {
    columns.push({
      type: 'calendar',
      title: '<?= __('Created At') ?>',
      width: 180,
      options: { format: 'YYYY-MM-DD HH:mm:ss', time: true }
    });
  }
  
  if (columnOrder.includes('updated_at')) {
    columns.push({
      type: 'calendar',
      title: '<?= __('Updated At') ?>',
      width: 180,
      options: { format: 'YYYY-MM-DD HH:mm:ss', time: true }
    });
    }
  
  // Hide loading
  document.getElementById('loading').style.display = 'none';
  
  // Create spreadsheet using jspreadsheet v4
  spreadsheetInstance = jspreadsheet(document.getElementById('spreadsheet'), {
    data: data,
    columns: columns,
    minDimensions: [columns.length, posts.length],
    tableOverflow: true,
    tableWidth: '100%',
    tableHeight: '600px',
    wordWrap: true,
    onchange: handleCellChange,
    oneditionstart: handleEditionStart  // Custom editors
  });
  
  // Store for later use
  window.bulkEditColumnOrder = columnOrder;
  window.bulkEditColumns = columns;
}

// Handle edition start - Show custom editors
function handleEditionStart(instance, cell, x, y, value) {
  const fieldName = window.bulkEditColumnOrder[x];
  const fieldType = spreadsheetHelper.fieldsTypeMap[fieldName];
  
  // ✅ User fields → Single-select dialog
  if (fieldType === 'User') {
    const usersData = spreadsheetHelper.fieldsDataMap[fieldName] || [];
    if (usersData.length > 0) {
      spreadsheetHelper.showUserDialog(x, y, value, usersData, (colIndex, rowIndex, newValue) => {
        spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, newValue, true);
      });
      return false;
    }
  }
  
  // ✅ Reference fields → Single-select dialog
  else if (fieldType === 'Reference') {
    const refData = spreadsheetHelper.fieldsDataMap[fieldName] || [];
    if (refData.length > 0) {
      const columnDef = window.bulkEditColumns[x];
      const fieldLabel = columnDef ? columnDef.title : 'Select Post';
      
      spreadsheetHelper.showReferenceDialog(x, y, value, refData, fieldLabel, (colIndex, rowIndex, newValue) => {
        spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, newValue, true);
      });
      return false;
    }
  }
  
  // ✅ Terms columns → Multi-select dialog
  else if (fieldName && fieldName.startsWith('terms:')) {
    const termType = fieldName.substring(6);
    const termsAvailable = spreadsheetHelper.termsData[termType] || [];
    
    if (termsAvailable.length > 0) {
      spreadsheetHelper.showTermsDialog(x, y, termType, termsAvailable, value, (colIndex, rowIndex, newValue) => {
        spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, newValue, true);
      });
      return false;
    }
  }
}

// All dialog functions now in spreadsheet-helper.js (vanilla JS)

// Handle cell change
async function handleCellChange(instance, cell, colIndex, rowIndex, value, oldValue) {
  // Ignore if value hasn't changed
  if (value === oldValue || isUpdating) {
    return;
  }
  
  isUpdating = true;
  
  // Get row data
  const rowData = spreadsheetInstance.getRowData(rowIndex);
  const postId = rowData[0];
  
  // Map column index to field name
  const fieldName = window.bulkEditColumnOrder[colIndex];
  const fieldType = spreadsheetHelper.fieldsTypeMap[fieldName];
  
  // ID is readonly
  if (!fieldName || fieldName === 'id') {
    isUpdating = false;
    return;
  }
  
  // ✅ Extract ID from "ID: Name" format for User/Reference fields
  let finalValue = value;
  if (fieldType === 'User' || fieldType === 'Reference') {
    if (typeof value === 'string' && value.includes(':')) {
      const parts = value.split(':', 2);
      finalValue = parts[0].trim();  // Get ID only
    }
  }
  
  // Check if this is a new row (no ID)
  if (!postId || postId === '' || postId === null) {
    await createNewPost(rowIndex, fieldName, finalValue, oldValue);
    return;
  }
  
  // Show saving status
  showSaveStatus('saving');
  
  try {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('field', fieldName);
    formData.append('value', finalValue);  // ✅ Use finalValue (extracted ID)
    formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
    formData.append('type', '<?= htmlspecialchars($posttype_slug) ?>');
    formData.append('post_lang', '<?= htmlspecialchars($currentLang) ?>');
    
    const response = await fetch('<?= admin_url('posts/updatebulkpost') ?>', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.success) {
      showSaveStatus('saved');
      
      // ✅ Re-format User/Reference value for display
      if (fieldType === 'User' || fieldType === 'Reference') {
        const fieldData = spreadsheetHelper.fieldsDataMap[fieldName];
        const displayValue = spreadsheetHelper.formatCellValue(fieldName, finalValue, fieldType, fieldData);
        if (displayValue !== finalValue) {
          // Update display value without triggering onChange
          spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, displayValue, false);
        }
      }
      
      // Update slug if title was changed
      if (fieldName === 'title' && result.data && result.data.slug) {
        const slugColIndex = window.bulkEditColumnOrder.indexOf('slug');
        if (slugColIndex !== -1) {
          spreadsheetInstance.setValueFromCoords(slugColIndex, rowIndex, result.data.slug, false);
        }
      }
    } else {
      // Format errors for notification
      let errorMessage = result.message || 'Failed to update post';
      
      if (result.errors && typeof result.errors === 'object') {
        const errorsList = [];
        for (const [field, messages] of Object.entries(result.errors)) {
          const fieldLabel = field.charAt(0).toUpperCase() + field.slice(1).replace(/_/g, ' ');
          if (Array.isArray(messages)) {
            messages.forEach(msg => errorsList.push(`<strong>${fieldLabel}:</strong> ${msg}`));
          } else {
            errorsList.push(`<strong>${fieldLabel}:</strong> ${messages}`);
          }
        }
        
        if (errorsList.length > 0) {
          errorMessage = errorsList.join('\n');
        }
      }
      
      throw new Error(errorMessage);
    }
  } catch (error) {
    console.error('Error updating post:', error);
    
    // Show errors in notification
    const errorMessage = error.message || 'Failed to update';
    showSaveStatus('error', errorMessage);
    
    // Revert value
    spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, oldValue, false);
  } finally {
    isUpdating = false;
  }
}

// Create new post from bulk edit
async function createNewPost(rowIndex, fieldName, value, oldValue) {
  // Show saving status
  showSaveStatus('saving');
  
  try {
    const formData = new FormData();
    formData.append('field', fieldName);
    formData.append('value', value);
    formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
    formData.append('type', '<?= htmlspecialchars($posttype_slug) ?>');
    formData.append('post_lang', '<?= htmlspecialchars($currentLang) ?>');
    
    const response = await fetch('<?= admin_url('posts/createbulkpost') ?>', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.success && result.data) {
      // Update all fields in the row with the created post data
      const columnOrder = window.bulkEditColumnOrder;
      const currentRowData = spreadsheetInstance.getRowData(rowIndex);
      
      // Update ID (first column)
      const idColIndex = columnOrder.indexOf('id');
      if (idColIndex !== -1 && result.data.id) {
        spreadsheetInstance.setValueFromCoords(idColIndex, rowIndex, result.data.id, true);
        currentRowData[idColIndex] = result.data.id;
      }
      
      // Update all fields from response data
      Object.keys(result.data).forEach(field => {
        if (field === 'id') return;
        
        const colIndex = columnOrder.indexOf(field);
        if (colIndex !== -1 && result.data[field] !== undefined && result.data[field] !== null) {
          spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, result.data[field], true);
          currentRowData[colIndex] = result.data[field];
        }
      });
      
      // Force update the entire row
      spreadsheetInstance.setRowData(rowIndex, currentRowData);
      
      showSaveStatus('saved');
      
      // Show success notification for new post
      if (window.fastNotice) {
        window.fastNotice.show('<?= __('New post created') ?>', 'success');
      }
    } else {
      // Format errors for notification
      let errorMessage = result.message || 'Failed to create post';
      
      if (result.errors && typeof result.errors === 'object') {
        const errorsList = [];
        for (const [field, messages] of Object.entries(result.errors)) {
          const fieldLabel = field.charAt(0).toUpperCase() + field.slice(1).replace(/_/g, ' ');
          if (Array.isArray(messages)) {
            messages.forEach(msg => errorsList.push(`<strong>${fieldLabel}:</strong> ${msg}`));
          } else {
            errorsList.push(`<strong>${fieldLabel}:</strong> ${messages}`);
          }
        }
        
        if (errorsList.length > 0) {
          errorMessage = errorsList.join('\n');
        }
      }
      
      throw new Error(errorMessage);
    }
  } catch (error) {
    console.error('Error creating post:', error);
    
    // Show errors in notification
    const errorMessage = error.message || 'Failed to create post';
    showSaveStatus('error', errorMessage);
    
    // Revert value - find the column index for this field
    const colIndex = window.bulkEditColumnOrder.indexOf(fieldName);
    if (colIndex !== -1) {
      spreadsheetInstance.setValueFromCoords(colIndex, rowIndex, oldValue, false);
    }
  } finally {
    isUpdating = false;
  }
}

// Show save status
function showSaveStatus(status, message = '') {
  const statusEl = document.getElementById('save-status');
  
  if (status === 'saving') {
    statusEl.innerHTML = '<span class="text-blue-600 font-medium"><i data-lucide="loader-2" class="h-4 w-4 inline animate-spin"></i> <?= __('Saving...') ?></span>';
  } else if (status === 'saved') {
    statusEl.innerHTML = '<span class="text-green-600 font-medium"><i data-lucide="check-circle" class="h-4 w-4 inline"></i> <?= __('Saved') ?></span>';
    
    // Show notification
    if (window.fastNotice) {
      window.fastNotice.show('<?= __('Saved successfully') ?>', 'success');
    }
    
    setTimeout(() => {
      statusEl.innerHTML = '';
    }, 2000);
  } else if (status === 'error') {
    statusEl.innerHTML = `<span class="text-red-600 font-medium"><i data-lucide="alert-circle" class="h-4 w-4 inline"></i> Error</span>`;
    
    // Parse errors and show in notification
    if (window.fastNotice) {
      // Check if message contains multiple errors (separated by \n)
      if (message && message.includes('\n')) {
        // Multiple errors - show as formatted list
        const errors = message.split('\n').filter(e => e.trim());
        
        // Create clean HTML list
        const errorItems = errors.map(e => `<div style="margin: 4px 0; padding: 4px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">${e}</div>`).join('');
        const errorHtml = `<div style="text-align: left; max-width: 400px;">
          <div style="font-weight: bold; margin-bottom: 8px; font-size: 14px;"><?= __('Validation Errors') ?>:</div>
          <div style="font-size: 13px; line-height: 1.5;">${errorItems}</div>
        </div>`;
        
        window.fastNotice.show(errorHtml, 'error', {
          duration: 6000,  // Show longer for multiple errors
          html: true
        });
      } else {
        // Single error
        window.fastNotice.show(message || '<?= __('Error saving') ?>', 'error');
      }
    }
    
    setTimeout(() => {
      statusEl.innerHTML = '';
    }, 3000);
  }
  
  lucide.createIcons();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  if (postIds && postIds.length > 0) {
    loadPostsData();
  }
});
</script>

<?php view_footer(); ?>

