<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

Flang::load('Posts', APP_LANG);

$breadcrumbs = array(
  [
    'name' => __('Dashboard'),
    'url' => admin_url('home')
  ],
  [
    'name' => __('Posts'),
    'url' => admin_url('posts')
  ],
  [
    'name' => __('Import'),
    'url' => admin_url('posts/import'),
    'active' => true
  ]
);

view_header([
    'title' => $title ?? __('Import'),
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);
?>

<?php
$posttype_slug = S_GET('type') ?? ($posttype['slug'] ?? 'post');
$posttype_languages = is_string($posttype['languages']) ? json_decode($posttype['languages'], true) : (is_array($posttype['languages']) ? $posttype['languages'] : []);
$currentLang = S_GET('post_lang') ?? ($currentLang ?? '');
?>

<div class="pc-container">
  <div class="pc-content">
    <div class="flex flex-col gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-foreground"><?= __('Import') ?> <?= isset($posttype['name']) ? __($posttype['name']) : '' ?></h1>
        <p class="text-muted-foreground"><?= __('Upload CSV and map columns to fields') ?></p>
      </div>
    </div>

    <div class="mb-4">
      <div role="tablist" aria-orientation="horizontal" class="inline-flex p-1 items-center justify-center rounded-md bg-muted text-muted-foreground">
        <?php 
        $langParams = $_GET;
        foreach (($languages ?? []) as $lang): 
          $langParams['post_lang'] = $lang;
          $isActive = ($lang == $currentLang);
        ?>
          <a href="<?= admin_url('posts/import') . '?' . http_build_query($langParams) ?>">
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

    <div class="mb-3 flex items-center justify-end">
      <a href="<?= admin_url('posts') . '?' . http_build_query(['type' => $posttype_slug, 'post_lang' => $currentLang]) ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 whitespace-nowrap">
        <i data-lucide="chevron-left" class="h-4 w-4 mr-2"></i>
        <?= __('Back to list') ?>
      </a>
    </div>

    <div class="bg-card card-content border rounded-xl p-4"
      x-data="postImportData($el)"
      data-fields='<?= htmlspecialchars(json_encode($availableFields ?? []), ENT_QUOTES) ?>'
      data-posttype='<?= htmlspecialchars($posttype_slug, ENT_QUOTES) ?>'
      data-url='<?= htmlspecialchars(admin_url('posts/import') . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''), ENT_QUOTES) ?>'
      data-csrf='<?= htmlspecialchars(Session::csrf_token(600), ENT_QUOTES) ?>'>

      <div class="flex flex-col gap-4">
        <div class="w-full">
          <div class="relative w-full max-w-full rounded-md bg-muted p-2 shadow-sm">
            <div class="flex items-center justify-center gap-4">
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                  <div class="h-6 w-6 rounded-full flex items-center justify-center text-[11px] font-semibold ring-2 transition-colors"
                    :class="currentStep >= 1 ? 'bg-primary text-primary-foreground ring-primary/40' : 'bg-gray-200 text-gray-600 ring-transparent'">1</div>
                  <span class="text-sm transition-colors" :class="currentStep === 1 ? 'text-foreground font-medium' : 'text-muted-foreground'"><?= __('Step 1: Upload CSV') ?></span>
                </div>
              </div>
              <i class="h-3 w-3 text-muted-foreground" data-lucide="chevron-right"></i>
              <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded-full flex items-center justify-center text-[11px] font-semibold ring-2 transition-colors"
                  :class="currentStep >= 2 ? 'bg-primary text-primary-foreground ring-primary/40' : 'bg-gray-200 text-gray-600 ring-transparent'">2</div>
                <span class="text-sm transition-colors" :class="currentStep === 2 ? 'text-foreground font-medium' : 'text-muted-foreground'"><?= __('Step 2: Mapping & Preview') ?></span>
              </div>
              <i class="h-3 w-3 text-muted-foreground" data-lucide="chevron-right"></i>
              <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded-full flex items-center justify-center text-[11px] font-semibold ring-2 transition-colors"
                  :class="currentStep >= 3 ? 'bg-primary text-primary-foreground ring-primary/40' : 'bg-gray-200 text-gray-600 ring-transparent'">3</div>
                <span class="text-sm transition-colors" :class="currentStep === 3 ? 'text-foreground font-medium' : 'text-muted-foreground'"><?= __('Step 3: Import') ?></span>
              </div>
            </div>
          </div>
        </div>

        <div x-show="currentStep === 1" class="flex items-center justify-center gap-4 flex-col border rounded-md bg-background shadow-sm p-6">
          <div class="w-full max-w-2xl">
            <div class="border-2 border-dashed rounded-lg p-6 bg-muted/30 flex flex-col items-center justify-center gap-3 min-h-[140px]">
              <i data-lucide="file-spreadsheet" class="h-8 w-8 text-muted-foreground"></i>
              <div class="text-center">
                <div class="text-sm font-medium mb-1"><?= __('Upload CSV file') ?></div>
                <div class="text-xs text-muted-foreground"><?= __('Maximum file size 5MB. Accepts .csv only.') ?></div>
              </div>
              <label class="mt-2 inline-flex cursor-pointer items-center justify-center rounded-md text-sm font-medium h-11 px-4 bg-background border border-input hover:bg-accent hover:text-accent-foreground">
                <input type="file" accept=".csv" x-ref="csvFile" class="hidden" @change="selectedName = $event.target.files?.[0]?.name || ''" />
                <i data-lucide="upload" class="h-4 w-4 mr-2"></i>
                <span><?= __('Choose file') ?></span>
              </label>
              <div class="text-xs text-muted-foreground" x-show="selectedName" x-text="selectedName"></div>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click.prevent="uploadCSV()" :disabled="uploading" class="inline-flex items-center justify-center rounded-md text-sm font-medium h-11 px-5 ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" :class="uploading ? 'bg-gray-200 text-gray-500' : 'bg-primary text-primary-foreground hover:bg-primary/90'">
              <i x-show="!uploading" data-lucide="arrow-up-from-line" class="h-4 w-4 mr-2"></i>
              <i x-show="uploading" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
              <span x-text="uploading ? '<?= __('Uploading...') ?>' : '<?= __('Upload CSV') ?>'"></span>
            </button>
          </div>
        </div>

        <div x-show="currentStep === 2" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="lg:col-span-2 border rounded-md p-3 bg-background shadow-sm">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-semibold"><?= __('Preview') ?></h3>
              <span class="text-xs text-muted-foreground" x-text="csv.rows.length ? (csv.rows.length + ' <?= __('rows') ?>') : ''"></span>
            </div>
            <div class="overflow-x-auto" x-show="csv.headers.length">
              <table class="w-full text-sm">
                <thead>
                  <tr>
                    <template x-for="h in csv.headers" :key="h">
                      <th class="text-left px-2 py-1 bg-muted text-muted-foreground sticky top-0" x-text="h"></th>
                    </template>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="(r, ri) in csv.rows.slice(0, 10)" :key="ri">
                    <tr>
                      <template x-for="(h, hi) in csv.headers" :key="hi">
                        <td class="px-2 py-1 align-top border-t" x-text="r[hi] ?? ''"></td>
                      </template>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
            <div x-show="!csv.headers.length" class="text-sm text-muted-foreground py-6">
              <?= __('No CSV loaded yet.') ?>
            </div>
          </div>

          <div class="lg:col-span-1 border rounded-md p-3 bg-background shadow-sm">
            <h3 class="font-semibold mb-2"><?= __('Mapping & Options') ?></h3>
            <div class="space-y-3 max-h-[420px] overflow-auto pr-1">
              <template x-for="field in availableFields" :key="field.field_name">
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium" :class="field.required && !mapping[field.field_name] ? 'text-red-600' : ''" x-text="field.label + ' (' + field.field_name + ')' "></label>
                  <select class="flex h-9 w-full rounded-md border border-input bg-background px-2 text-sm focus-visible:ring-2 focus-visible:ring-ring" :class="field.required && !mapping[field.field_name] ? 'border-red-400' : ''" x-model="mapping[field.field_name]" @change="updateCanProceed()">
                    <option value="">— <?= __('None') ?> —</option>
                    <template x-for="h in csv.headers" :key="h">
                      <option :value="h" x-text="h"></option>
                    </template>
                  </select>
                </div>
              </template>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium"><?= __('Import Mode') ?></label>
                <select class="flex h-9 w-full rounded-md border border-input bg-background px-2 text-sm" x-model="importMode">
                  <option value="create"><?= __('Create only') ?></option>
                  <option value="update"><?= __('Update by slug') ?></option>
                  <option value="overwrite"><?= __('Overwrite by slug') ?></option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div x-show="currentStep === 3" class="border rounded-md p-4 bg-background shadow-sm">
          <div class="flex items-start flex-col gap-3">
            <div class="w-full flex items-center justify-between">
              <div>
                <div class="text-sm text-muted-foreground"><?= __('Final check before import') ?></div>
                <div class="text-xs text-muted-foreground">
                  <?= __('Make sure the mapping and mode are correct, then start import.') ?>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button x-show="!hasImported" @click.prevent="doImport()" :disabled="importing || !csv.headers.length" class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 px-4 ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" :class="(importing || !csv.headers.length) ? 'bg-gray-200 text-gray-500' : 'bg-primary text-primary-foreground hover:bg-primary/90'">
                  <i x-show="!importing" data-lucide="download" class="h-4 w-4 mr-2"></i>
                  <i x-show="importing" data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>
                  <span x-text="importing ? '<?= __('Importing...') ?>' : '<?= __('Start Import') ?>'"></span>
                </button>
              </div>
            </div>

            <template x-if="importResult">
              <div class="w-full rounded-md p-3 text-sm"
                :class="importResult.success && importResult.imported > 0 && importResult.errorsCount === 0 ? 'bg-green-50 text-green-800 border border-green-200' : (importResult.errorsCount > 0 ? 'bg-yellow-50 text-yellow-800 border border-yellow-200' : 'bg-blue-50 text-blue-800 border border-blue-200')">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border"
                      :class="importResult.errorsCount === 0 ? 'bg-green-100 text-green-800 border-green-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200'">
                      <span><?= __('Imported') ?>:&nbsp;</span><span x-text="importResult.imported"></span>
                    </div>
                    <div class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border"
                      :class="importResult.errorsCount === 0 ? 'bg-green-100 text-green-800 border-green-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200'">
                      <span><?= __('Errors') ?>:&nbsp;</span><span x-text="importResult.errorsCount"></span>
                    </div>
                  </div>
                </div>
                <div class="mt-2 font-medium" x-text="importResult.message"></div>
              </div>
            </template>

            <div class="w-full" x-show="errorRows && errorRows.length">
              <div class="flex items-center justify-between mb-2">
                <div class="text-sm font-medium"><?= __('Sample errors') ?></div>
                <button type="button" class="text-xs underline text-muted-foreground" @click="showAllErrors = !showAllErrors" x-text="showAllErrors ? '<?= __('Collapse') ?>' : '<?= __('Expand') ?>'"></button>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-xs">
                  <thead>
                    <tr>
                      <th class="text-left px-2 py-1 bg-muted text-muted-foreground">#</th>
                      <th class="text-left px-2 py-1 bg-muted text-muted-foreground"><?= __('Row') ?></th>
                      <th class="text-left px-2 py-1 bg-muted text-muted-foreground"><?= __('Errors') ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <template x-for="(er, idx) in (showAllErrors ? errorRows : errorRows.slice(0, 5))" :key="idx">
                      <tr>
                        <td class="px-2 py-1 align-top" x-text="idx + 1"></td>
                        <td class="px-2 py-1 align-top" x-text="er.row_index"></td>
                        <td class="px-2 py-1 align-top">
                          <ul class="list-disc pl-4">
                            <template x-for="(msg, mi) in er.errors" :key="mi">
                              <li x-text="msg"></li>
                            </template>
                          </ul>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <button class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 px-4 border border-input bg-background hover:bg-accent hover:text-accent-foreground" @click.prevent="goPrev()" :disabled="currentStep === 1">
            <?= __('Back') ?>
          </button>
          <button class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 px-4 bg-primary text-primary-foreground hover:bg-primary/90" @click.prevent="goNext()" :disabled="!canProceed">
            <span x-text="currentStep === 1 ? '<?= __('Next: Mapping') ?>' : (currentStep === 2 ? '<?= __('Next: Import') ?>' : '<?= __('Finish') ?>')"></span>
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
window.postImportData = function(rootEl) {
  const availableFields = JSON.parse(rootEl.dataset.fields || '[]');
  const posttypeSlug = rootEl.dataset.posttype || 'post';
  const actionUrl = rootEl.dataset.url || '';
  const csrfToken = rootEl.dataset.csrf || '';
  return {
    currentStep: 1,
    canProceed: false,
    uploading: false,
    importing: false,
    hasImported: false,
    message: '',
    errors: [],
    importResult: null,
    errorRows: [],
    showAllErrors: false,
    csv: { headers: [], rows: [] },
    mapping: {},
    importMode: 'create',
    availableFields: availableFields,
    fileInput: null,
    selectedName: '',
    init() {
      this.fileInput = this.$refs.csvFile;
      (this.availableFields || []).forEach(f => { this.mapping[f.field_name] = ''; });
      this.updateCanProceed();
    },
    updateCanProceed() {
      if (this.currentStep === 1) {
        this.canProceed = !!(this.csv && this.csv.headers && this.csv.headers.length);
      } else if (this.currentStep === 2) {
        // Cho phép qua bước 3 nếu không còn field required nào thiếu mapping
        const missingRequired = [];
        (this.availableFields || []).forEach(f => {
          if (f.required && !this.mapping[f.field_name]) missingRequired.push(f.field_name);
        });
        this.canProceed = missingRequired.length === 0 && (this.csv && this.csv.headers && this.csv.headers.length);
      } else {
        this.canProceed = true;
      }
    },
    goNext() {
      if (this.currentStep === 1 && !this.canProceed) return;
      if (this.currentStep === 2 && !this.canProceed) return;
      this.currentStep = Math.min(3, this.currentStep + 1);
      this.updateCanProceed();
    },
    goPrev() {
      this.currentStep = Math.max(1, this.currentStep - 1);
      this.updateCanProceed();
    },
    autoMap() {
      const headers = (this.csv && this.csv.headers) ? this.csv.headers.map(h => (h||'').toString().trim().toLowerCase()) : [];
      if (!headers.length) return;
      const normalize = (s) => (s||'').toString().trim().toLowerCase().replace(/\s+/g,'_');
      (this.availableFields || []).forEach(f => {
        const name = normalize(f.field_name);
        const label = normalize(f.label || '');
        let idx = headers.indexOf(name);
        if (idx === -1 && label) idx = headers.indexOf(label);
        const aliases = {
          'title': ['name'],
          'slug': ['permalink'],
          'status': ['state'],
          'created_at': ['created','createdat','created_at','date'],
          'updated_at': ['updated','updatedat','updated_at','modified']
        };
        if (idx === -1 && aliases[name]) {
          for (const a of aliases[name]) {
            const aidx = headers.indexOf(a);
            if (aidx !== -1) { idx = aidx; break; }
          }
        }
        this.mapping[f.field_name] = idx !== -1 ? (this.csv.headers[idx] || '') : (this.mapping[f.field_name] || '');
      });
      this.updateCanProceed();
    },
    async uploadCSV() {
      if (!this.fileInput || !this.fileInput.files || this.fileInput.files.length === 0) return;
      this.uploading = true;
      this.message = '';
      this.errors = [];
      try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'upload_csv');
        formData.append('csv_file', this.fileInput.files[0]);
        formData.append('type', posttypeSlug);
        const res = await fetch(actionUrl, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          this.csv = data.data || { headers: [], rows: [] };
          (this.availableFields || []).forEach(f => { this.mapping[f.field_name] = ''; });
          this.$nextTick(() => this.autoMap());
          this.currentStep = 2;
          this.updateCanProceed();
        } else {
          this.message = data.message || '<?= __('Upload failed') ?>';
        }
      } catch (e) {
        this.message = '<?= __('Network error occurred') ?>';
      } finally {
        this.uploading = false;
      }
    },
    async doImport() {
      const missingRequired = [];
      (this.availableFields || []).forEach(f => {
        if (f.required && !this.mapping[f.field_name]) missingRequired.push(f.label || f.field_name);
      });
      if (missingRequired.length) {
        this.message = '<?= __('Missing required mappings for') ?>: ' + missingRequired.join(', ');
        return;
      }
      this.currentStep = 3;
      this.importing = true;
      this.message = '';
      this.errors = [];
      try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'import_csv');
        formData.append('csv_data', JSON.stringify(this.csv));
        formData.append('column_mapping', JSON.stringify(this.mapping));
        formData.append('import_mode', this.importMode);
        formData.append('type', posttypeSlug);
        const res = await fetch(actionUrl, { method: 'POST', body: formData });
        const data = await res.json();
        const payload = data && data.data ? data.data : data;
        const success = !!(payload && payload.success);
        const imported = (payload && typeof payload.imported !== 'undefined') ? payload.imported : 0;
        const errorsArr = (payload && Array.isArray(payload.errors)) ? payload.errors : [];
        const errorRows = (payload && Array.isArray(payload.error_rows)) ? payload.error_rows : [];
        const msg = (payload && payload.message) ? payload.message : (success ? '<?= __('Imported successfully') ?>' : '<?= __('Import failed') ?>');
        this.message = msg;
        this.errors = errorsArr;
        this.errorRows = errorRows;
        this.importResult = {
          success: success,
          imported: imported,
          errorsCount: errorsArr.length,
          message: msg
        };
        if (success) {
          this.hasImported = true;
        }
      } catch (e) {
        this.message = '<?= __('Network error occurred') ?>';
      } finally {
        this.importing = false;
      }
    }
  };
}
</script>

<?php view_footer(); ?>


