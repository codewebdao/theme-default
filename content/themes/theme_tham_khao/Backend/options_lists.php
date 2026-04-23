<?php

use System\Libraries\Render;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Backend/Global', APP_LANG);
Flang::load('Backend/Options', APP_LANG);

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Options'),
      'url' => admin_url('options/lists'),
      'active' => true
  ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => __('Options Management'), 'breadcrumb' => $breadcrumbs ]);

// Get pagination data
$optionsData = $options['data']   ?? [];
$page        = $options['page']   ?? 1;
$is_next     = $options['is_next'] ?? false;

// Get filter parameters (from controller data or fallback to GET)
$search = $search ?? S_GET('q')     ?? '';
$limit  = $limit  ?? S_GET('limit') ?? option('default_limit');
$type   = $type   ?? S_GET('type')  ?? '';
$status = $status ?? S_GET('status') ?? '';
$sort   = $sort   ?? S_GET('sort')  ?? 'id';
$order  = $order  ?? S_GET('order') ?? 'desc';
?>

<div class="">
  <!-- Header & Filter -->
  <div class="flex flex-col gap-4">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= __('Options Management') ?></h1>
      <p class="text-muted-foreground"><?= __('Manage system options and settings') ?></p>
    </div>

    <!-- Notifications -->
    <?php if (Session::has_flash('success')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
    <?php endif; ?>
    <?php if (Session::has_flash('error')): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
    <?php endif; ?>
    
    <div class="bg-card rounded-xl mb-4">
      <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto">
          <!-- Search -->
          <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
            <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10" 
              placeholder="<?= __('Search by name, label, description') ?>..." 
              name="q" 
              value="<?= htmlspecialchars($search) ?>" />
          </div>

          <!-- Type Filter -->
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="type" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value=""><?= __('All Types') ?></option>
              <option value="Text" <?= $type==='Text'?'selected':'' ?>><?= __('Text') ?></option>
              <option value="Textarea" <?= $type==='Textarea'?'selected':'' ?>><?= __('Textarea') ?></option>
              <option value="Number" <?= $type==='Number'?'selected':'' ?>><?= __('Number') ?></option>
              <option value="Email" <?= $type==='Email'?'selected':'' ?>><?= __('Email') ?></option>
              <option value="URL" <?= $type==='URL'?'selected':'' ?>><?= __('URL') ?></option>
              <option value="Boolean" <?= $type==='Boolean'?'selected':'' ?>><?= __('Boolean') ?></option>
              <option value="Select" <?= $type==='Select'?'selected':'' ?>><?= __('Select') ?></option>
              <option value="Image" <?= $type==='Image'?'selected':'' ?>><?= __('Image') ?></option>
              <option value="File" <?= $type==='File'?'selected':'' ?>><?= __('File') ?></option>
              <option value="WYSIWYG" <?= $type==='WYSIWYG'?'selected':'' ?>><?= __('WYSIWYG') ?></option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="min-w-[120px] w-full sm:w-auto">
            <select name="status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value=""><?= __('All Status') ?></option>
              <option value="active" <?= $status==='active'?'selected':'' ?>><?= __('Active') ?></option>
              <option value="inactive" <?= $status==='inactive'?'selected':'' ?>><?= __('Inactive') ?></option>
            </select>
          </div>

          <!-- Limit -->
          <div class="min-w-[100px] w-full sm:w-auto">
            <select name="limit" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value="5" <?= ($limit == 5) ? 'selected' : '' ?>>5</option>
              <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
              <option value="15" <?= ($limit == 15) ? 'selected' : '' ?>>15</option>
              <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
              <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
              <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
              <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
            </select>
          </div>
        </div>

        <!-- Add Button -->
        <a href="<?= admin_url('options/add') ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap w-full lg:w-auto">
          <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
          <?= __('Add Option') ?>
        </a>
      </form>
    </div>
  </div>
  
  <!-- Options Table -->
  <div class="bg-card card-content !p-0 border overflow-hidden">
    <div class="overflow-x-auto">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
              <?php
              // Helper to build sort link maintaining filters
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
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('ID'), 'id', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Label'), 'label', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Name'), 'name', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Type'), 'type', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Description') ?></th>
              <th class="px-4 py-3 text-center align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Status'), 'status', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Actions') ?></th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <?php if (!empty($optionsData)): ?>
              <?php foreach ($optionsData as $option): ?>
                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                  <td class="px-4 py-1 align-middle font-medium text-foreground whitespace-nowrap"><?= htmlspecialchars($option['id'] ?? '') ?></td>
                  <td class="px-4 py-1 align-middle text-foreground whitespace-nowrap truncate max-w-[200px]" title="<?= htmlspecialchars($option['label'] ?? '') ?>"><?= htmlspecialchars($option['label'] ?? '') ?></td>
                  <td class="px-4 py-1 align-middle">
                    <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold font-mono text-foreground whitespace-nowrap">
                      <?= htmlspecialchars($option['name'] ?? '') ?>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle">
                    <?php
                    $typeColors = [
                      'Text' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                      'Textarea' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
                      'Number' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                      'Email' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400',
                      'URL' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400',
                      'Boolean' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                      'Select' => 'bg-lime-100 text-lime-800 dark:bg-lime-900/30 dark:text-lime-400',
                      'Image' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                      'File' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400',
                      'WYSIWYG' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                    ];
                    $typeClass = $typeColors[$option['type']] ?? 'bg-secondary text-secondary-foreground';
                    ?>
                    <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-transparent <?= $typeClass ?> whitespace-nowrap">
                      <?= htmlspecialchars($option['type'] ?? '') ?>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-muted-foreground truncate max-w-[250px]" title="<?= htmlspecialchars($option['description'] ?? '') ?>"><?= htmlspecialchars($option['description'] ?? '-') ?></td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-transparent <?= $option['status']==='active'?'bg-primary text-primary-foreground':'bg-secondary text-secondary-foreground' ?>">
                      <?= $option['status']==='active'?__('Active'):__('Inactive') ?>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-1 justify-center">
                      <a href="<?= admin_url('options/edit/' . $option['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" title="<?= __('Edit Option') ?>">
                        <i data-lucide="square-pen" class="h-4 w-4"></i>
                      </a>
                      <?php if ($option['name'] !== 'option_groups'): ?>
                      <a href="<?= admin_url('options/delete/' . $option['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0 flex-shrink-0" onclick="return confirm('<?= __('Are you sure you want to delete this option?') ?>');" title="<?= __('Delete Option') ?>">
                        <i data-lucide="trash2" class="h-4 w-4"></i>
                      </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center py-4 text-muted-foreground"><?= __('No options found.') ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-1 border-t gap-4">
      <div class="text-sm text-muted-foreground">
        <?php
        $currentCount = count($optionsData);
        if ($currentCount > 0) {
          $from = ($page - 1) * $limit + 1;
          $to = $from + $currentCount - 1;
          _e('Showing %1% to %2% results', $from, $to);
          if ($is_next) {
            echo ' <span class="text-primary">(' . __('more available') . ')</span>';
          }
        } else {
          _e('No results');
        }
        ?>
      </div>
      <div class="flex items-center gap-2">
        <?php
        // Build pagination query parameters
        $query_params = [];
        if (!empty($search)) $query_params['q'] = $search;
        if ($limit != option('default_limit')) $query_params['limit'] = $limit;
        if (!empty($type)) $query_params['type'] = $type;
        if (!empty($status)) $query_params['status'] = $status;
        if (!empty($sort)) $query_params['sort'] = $sort;
        if (!empty($order)) $query_params['order'] = $order;
        
        echo Render::pagination(admin_url('options/lists'), $page, $is_next, $query_params);
        ?>
      </div>
    </div>
  </div>
</div>

<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>

