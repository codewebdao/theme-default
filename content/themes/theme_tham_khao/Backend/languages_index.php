<?php

use System\Libraries\Render;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Languages'),
      'url' => admin_url('languages'),
      'active' => true
  ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => __('languages list'), 'breadcrumb' => $breadcrumbs ]);

$languagesData = $languages['data']   ?? [];
$page          = $languages['page']   ?? 1;
$is_next       = $languages['is_next'] ?? 0;

$search = S_GET('q')     ?? '';
$limit  = S_GET('limit') ?? 10;
$status = S_GET('status') ?? '';
$sort   = S_GET('sort')  ?? 'status';
$order  = S_GET('order') ?? 'asc';
?>
<div class="" x-data="{ showAdd: <?= HAS_GET('showform') ? 'true' : 'false' ?>, showEdit: false, editData: {} }">

  <!-- Header & Filter -->
  <div class="flex flex-col gap-4">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= __('Languages Management') ?></h1>
      <p class="text-muted-foreground"><?= __('Manage system languages and their settings') ?></p>
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
            <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10" placeholder="<?= __('search') ?>..." name="q" value="<?= htmlspecialchars($search) ?>" />
          </div>
          <div class="min-w-[150px] w-full sm:w-auto">
            <select name="status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" onchange="this.form.submit()">
              <option value=""><?= __('All Status') ?></option>
              <option value="active" <?= $status==='active'?'selected':'' ?>><?= __('Active') ?></option>
              <option value="inactive" <?= $status==='inactive'?'selected':'' ?>><?= __('Inactive') ?></option>
            </select>
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
        <button type="button" @click="showAdd = true" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap w-full lg:w-auto">
          <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
          <?= __('Add Language') ?>
        </button>
      </form>
    </div>
  </div>
  <!-- Modal Add Language -->
  <div x-show="showAdd" x-transition.opacity class="fixed left-0 top-0 z-[9999] flex items-center justify-center w-full h-full bg-black/40" style="display: none;">
    <div @click.away="showAdd = false" class="relative w-full max-w-lg mx-auto bg-background p-6 rounded-lg shadow-lg border">
      <div class="flex flex-col space-y-1.5 text-center sm:text-left mb-4">
        <h2 class="text-lg font-semibold leading-none tracking-tight"><?= __('Add Language') ?></h2>
        <p class="text-sm text-muted-foreground"><?= __('Add a new language to the system. Fill in all the required information.') ?></p>
      </div>
      <form class="space-y-4" action="<?= admin_url('languages/add') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= isset($csrf_token) ? htmlspecialchars($csrf_token) : '' ?>">
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="name"><?= __('Language Name') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="name" name="name" placeholder="<?= __('e.g., English, Vietnamese') ?>" required>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="code"><?= __('Language Code') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="code" name="code" placeholder="<?= __('e.g., en, vi') ?>" maxlength="2" required>
          <p class="text-xs text-muted-foreground"><?= __('2-letter ISO language code (e.g., en, vi, fr)') ?></p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="flag"><?= __('Country Flag') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="flag" name="flag" placeholder="<?= __('e.g., us, vn, uk') ?>" maxlength="10" required>
          <p class="text-xs text-muted-foreground"><?= __('Country code for flag (e.g., us, vn, uk, fr)') ?></p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="locale"><?= __('Locale') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="locale" name="locale" placeholder="<?= __('e.g., en_US, vi_VN') ?>" maxlength="10">
          <p class="text-xs text-muted-foreground"><?= __('Locale code (auto-generated from code_FLAG). You can edit it manually.') ?></p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="status"><?= __('Status') ?></label>
          <select id="status" name="status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            <option value="active"><?= __('Active') ?></option>
            <option value="inactive"><?= __('Inactive') ?></option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" id="is_default" name="is_default" value="1" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
          <label class="text-sm font-medium leading-none" for="is_default"><?= __('Set as default language') ?></label>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 gap-2">
          <button type="button" @click="showAdd = false" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"><?= __('Cancel') ?></button>
          <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2" type="submit"><?= __('Add Language') ?></button>
        </div>
      </form>
      <button type="button" @click="showAdd = false" class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
        <i data-lucide="x" class="h-4 w-4"></i>
        <span class="sr-only"><?= __('Close') ?></span>
      </button>
    </div>
  </div>

  <!-- Modal Edit Language -->
  <div x-show="showEdit" x-transition.opacity class="fixed left-0 top-0 z-[9999] flex items-center justify-center w-full h-full bg-black/40" style="display: none;">
    <div @click.away="showEdit = false" class="relative w-full max-w-lg mx-auto bg-background p-6 rounded-lg shadow-lg border">
      <div class="flex flex-col space-y-1.5 text-center sm:text-left mb-4">
        <h2 class="text-lg font-semibold leading-none tracking-tight"><?= __('Edit Language') ?></h2>
        <p class="text-sm text-muted-foreground"><?= __('Update language information. Fill in all the required fields.') ?></p>
      </div>
      <form class="space-y-4" :action="'<?= admin_url('languages/edit/') ?>' + editData.id" method="POST">
        <input type="hidden" name="csrf_token" value="<?= isset($csrf_token) ? htmlspecialchars($csrf_token) : '' ?>">
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="edit_name"><?= __('Language Name') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="edit_name" name="name" x-model="editData.name" placeholder="<?= __('e.g., English, Vietnamese') ?>" required>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="edit_code"><?= __('Language Code') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="edit_code" name="code" x-model="editData.code" placeholder="<?= __('e.g., en, vi') ?>" maxlength="2" required>
          <p class="text-xs text-muted-foreground"><?= __('2-letter ISO language code (e.g., en, vi, fr)') ?></p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="edit_flag"><?= __('Country Flag') ?> *</label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="edit_flag" name="flag" x-model="editData.flag" placeholder="<?= __('e.g., us, vn, uk') ?>" maxlength="10" required>
          <p class="text-xs text-muted-foreground"><?= __('Country code for flag (e.g., us, vn, uk, fr)') ?></p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="edit_locale"><?= __('Locale') ?></label>
          <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" id="edit_locale" name="locale" x-model="editData.locale" placeholder="<?= __('e.g., en_US, vi_VN') ?>" maxlength="10">
          <p class="text-xs text-muted-foreground"><?= __('Locale code (auto-generated from code_FLAG). You can edit it manually.') ?></p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none" for="edit_status"><?= __('Status') ?></label>
          <select id="edit_status" name="status" x-model="editData.status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            <option value="active"><?= __('Active') ?></option>
            <option value="inactive"><?= __('Inactive') ?></option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" id="edit_is_default" name="is_default" value="1" :checked="editData.is_default" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
          <label class="text-sm font-medium leading-none" for="edit_is_default"><?= __('Set as default language') ?></label>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 gap-2">
          <button type="button" @click="showEdit = false" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"><?= __('Cancel') ?></button>
          <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2" type="submit"><?= __('Update Language') ?></button>
        </div>
      </form>
      <button type="button" @click="showEdit = false" class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
        <i data-lucide="x" class="h-4 w-4"></i>
        <span class="sr-only"><?= __('Close') ?></span>
      </button>
    </div>
  </div>
  
  <!-- Bảng danh sách -->
  <div class="bg-card card-content !p-0 border overflow-hidden">
    <div class="overflow-x-auto">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm ">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
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
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Code'), 'code', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Flag'), 'flag', $sort, $order); ?></th>
              <th class="px-4 py-3 text-left align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Locale'), 'locale', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Status'), 'status', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle cursor-pointer bg-menu-background-hover text-menu-text-hover font-medium hover:bg-menu-background-hover/90 transition-colors whitespace-nowrap"><?php echo sort_link(__('Default'), 'is_default', $sort, $order); ?></th>
              <th class="px-4 py-3 text-center align-middle bg-menu-background-hover text-menu-text-hover font-medium whitespace-nowrap"><?= __('Actions') ?></th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <?php if (!empty($languagesData)): ?>
              <?php foreach ($languagesData as $language): ?>
                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                  <td class="px-4 py-1 align-middle text-foreground whitespace-nowrap truncate max-w-[200px]" title="<?= htmlspecialchars($language['name'] ?? '') ?>"><?= htmlspecialchars($language['name'] ?? '') ?></td>
                  <td class="px-4 py-1 align-middle"><div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold font-mono text-foreground whitespace-nowrap"><?= htmlspecialchars(strtoupper($language['code'] ?? '')) ?></div></td>
                  <td class="px-4 py-1 align-middle">
                    <div class="inline-flex items-center gap-2 whitespace-nowrap">
                      <span class="text-2xl"><?= !empty($language['flag']) ? lang_flag($language['flag']) : '🏳️' ?></span>
                      <span class="text-sm font-mono text-muted-foreground"><?= htmlspecialchars(strtoupper($language['flag'] ?? 'us')) ?></span>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle">
                    <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold font-mono bg-secondary text-secondary-foreground whitespace-nowrap">
                      <?= htmlspecialchars($language['locale'] ?? '-') ?>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-2 justify-center">
                      <button type="button" onclick="changeStatusLanguage(<?= $language['id']; ?>, event);" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background data-[state=checked]:bg-primary data-[state=unchecked]:bg-input <?= $language['status']==='active'?'bg-primary':'bg-input' ?>">
                        <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform <?= $language['status']==='active'?'translate-x-5':'translate-x-0' ?>"></span>
                      </button>
                      <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-transparent <?= $language['status']==='active'?'bg-primary text-primary-foreground':'bg-secondary text-secondary-foreground' ?>">
                        <?= $language['status']==='active'?__('Active'):__('Inactive') ?>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-2 justify-center">
                      <button type="button" onclick="changeDefaultLanguage(<?= $language['id']; ?>, event);" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background <?= (!empty($language['is_default']) && $language['is_default'] == 1) ? 'bg-primary' : 'bg-input' ?>" <?= (!empty($language['is_default']) && $language['is_default'] == 1) ? 'disabled' : '' ?>>
                        <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform <?= (!empty($language['is_default']) && $language['is_default'] == 1) ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                      </button>
                    </div>
                  </td>
                  <td class="px-4 py-1 align-middle text-center">
                    <div class="flex items-center gap-1 justify-center">
                      <button type="button" @click="editData = {id: <?= $language['id'] ?>, name: '<?= htmlspecialchars($language['name'] ?? '', ENT_QUOTES) ?>', code: '<?= htmlspecialchars($language['code'] ?? '', ENT_QUOTES) ?>', flag: '<?= htmlspecialchars($language['flag'] ?? '', ENT_QUOTES) ?>', locale: '<?= htmlspecialchars($language['locale'] ?? '', ENT_QUOTES) ?>', status: '<?= htmlspecialchars($language['status'] ?? '', ENT_QUOTES) ?>', is_default: <?= (!empty($language['is_default']) && $language['is_default'] == 1) ? 'true' : 'false' ?>}; showEdit = true" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0" title="<?= __('Edit Language') ?>">
                        <i data-lucide="square-pen" class="h-4 w-4"></i>
                      </button>
                      <a href="<?= admin_url('languages/delete/' . $language['id']); ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0" onclick="return confirm('<?= __('Are you sure you want to delete this item?') ?>');" title="<?= __('Delete Language') ?>">
                        <i data-lucide="trash2" class="h-4 w-4"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center py-4 text-muted-foreground"><?= __('No languages found.') ?></td></tr>
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
        $total = $languages['total'] ?? count($languagesData);
        $from = ($page - 1) * $limit + 1;
        $to = $from + count($languagesData) - 1;
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
        if (!empty($status)) $query_params['status'] = $status;
        if (!empty($sort)) $query_params['sort'] = $sort;
        if (!empty($order)) $query_params['order'] = $order;
        echo Render::pagination(admin_url('languages/index'), $page, $is_next, $query_params);
        ?>
      </div>
    </div>
  </div>
</div>
<script>
function changeStatusLanguage(id, event) {
  event.preventDefault();
  if(confirm('<?= __('Are you sure you want to change the status?') ?>')) {
    window.location.href = '<?= admin_url('languages/changestatus/') ?>' + id + '/';
  }
}
function changeDefaultLanguage(id, event) {
  event.preventDefault();
  if(confirm('<?= __('Are you sure you want to set this as default?') ?>')) {
    window.location.href = '<?= admin_url('languages/setdefault/') ?>' + id + '/';
  }
}

// Auto-generate locale for Add Modal
// In Add mode: Always auto-update locale when code or flag changes
document.addEventListener('DOMContentLoaded', function() {
  const addCodeInput = document.getElementById('code');
  const addFlagInput = document.getElementById('flag');
  const addLocaleInput = document.getElementById('locale');

  function updateAddLocale() {
    const code = addCodeInput.value.trim().toLowerCase();
    const flag = addFlagInput.value.trim().toUpperCase();
    
    // Always update locale in Add mode when both code and flag are provided
    if (code && flag) {
      addLocaleInput.value = code + '_' + flag;
    }
  }

  if (addCodeInput && addFlagInput && addLocaleInput) {
    addCodeInput.addEventListener('input', updateAddLocale);
    addCodeInput.addEventListener('blur', updateAddLocale);
    addFlagInput.addEventListener('input', updateAddLocale);
    addFlagInput.addEventListener('blur', updateAddLocale);
  }
});

// Xử lý form edit
document.addEventListener('DOMContentLoaded', function() {
  const editForm = document.querySelector('form[action*="languages/edit/"]');
  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      // Form sẽ được submit bình thường, modal sẽ đóng sau khi redirect
    });
  }
});
</script>
<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>