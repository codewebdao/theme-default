<?php

use System\Libraries\Render;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Backend/Global', APP_LANG);
Flang::load('Backend/Posts', APP_LANG);

$posttype_slug = $posttype['slug'] ?? 'posts';
$posttype_name = $posttype['name'] ?? 'Posts';
$posttype_fields = $availableFields ?? [];
$posttype_terms = $posttype['terms'] ?? [];

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => $posttype_name,
      'url' => admin_url('posts') . '?type=' . $posttype_slug . '&post_lang=' . $currentLang
  ],
  [
      'name' => __('Import'),
      'url' => admin_url('posts/import') . '?type=' . $posttype_slug . '&post_lang=' . $currentLang,
      'active' => true
  ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => __('Import') . ' ' . $posttype_name, 'breadcrumb' => $breadcrumbs]);
?>

<div x-data="importForm()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground"><?= __('Import') ?> <?= $posttype_name ?></h1>
                <p class="text-sm text-muted-foreground"><?= __('Import posts from JSON file') ?></p>
            </div>
            <a :href="`<?= admin_url('posts') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>`" 
               class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent h-10 px-4 py-2">
                <i data-lucide="arrow-left" class="h-4 w-4 mr-2"></i>
                <?= __('Back to Posts') ?>
            </a>
        </div>
    </div>

    <!-- Step 1: Upload File -->
    <div x-show="currentStep === 1" class="bg-card rounded-lg p-2 space-y-6">
        <div>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="upload" class="h-5 w-5"></i>
                <?= __('Step 1: Upload JSON File') ?>
            </h3>
            <p class="text-sm text-muted-foreground"><?= __('Select a JSON file to import') ?></p>
        </div>

        <div class="space-y-4">
            <!-- File Upload -->
            <div class="border-2 border-dashed rounded-lg p-8 text-center">
                <input type="file" id="jsonFile" accept=".json" @change="handleFileUpload($event)"
                       class="hidden">
                <label for="jsonFile" class="cursor-pointer">
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="file-json" class="h-12 w-12 text-muted-foreground"></i>
                        <p class="text-sm font-medium"><?= __('Click to upload JSON file') ?></p>
                        <p class="text-xs text-muted-foreground"><?= __('or drag and drop') ?></p>
                    </div>
                </label>
            </div>

            <!-- Upload Progress -->
            <div x-show="isUploading" class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium"><?= __('Uploading...') ?></span>
                    <span class="text-sm text-muted-foreground" x-text="`${uploadProgress}%`"></span>
                </div>
                <div class="w-full bg-muted rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all" 
                         :style="`width: ${uploadProgress}%`"></div>
                </div>
            </div>

            <!-- File Info -->
            <div x-show="fileUploaded && !isUploading" class="bg-muted/50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-check" class="h-5 w-5 text-green-500"></i>
                        <div>
                            <p class="text-sm font-medium" x-text="fileName"></p>
                            <p class="text-xs text-muted-foreground" x-text="`${totalItems} items found`"></p>
                        </div>
                    </div>
                    <button @click="resetUpload()" 
                            class="text-sm text-muted-foreground hover:text-foreground">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Next Button -->
        <div class="flex justify-end">
            <button type="button" @click="goToMapping()" :disabled="!fileUploaded || isUploading"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 disabled:opacity-50 disabled:pointer-events-none">
                <?= __('Next: Map Columns') ?>
                <i data-lucide="arrow-right" class="h-4 w-4 ml-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 2: Map Columns -->
    <div x-show="currentStep === 2" class="bg-card rounded-lg p-2 space-y-6">
        <div>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="git-merge" class="h-5 w-5"></i>
                <?= __('Step 2: Map Columns') ?>
            </h3>
            <p class="text-sm text-muted-foreground"><?= __('Match JSON columns with post fields') ?></p>
        </div>

        <!-- Section 1: Terms Mapping -->
        <template x-if="posttypeTerms.length > 0">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="tag" class="h-5 w-5"></i>
                    <h4 class="text-base font-semibold"><?= __('Terms / Taxonomies') ?></h4>
                </div>
                <div class="space-y-2">
                    <template x-for="term in posttypeTerms" :key="term.type">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 border rounded-lg hover:bg-muted/30">
                            <div class="flex flex-col">
                                <label class="text-sm font-medium" x-text="term.name"></label>
                                <p class="text-xs text-muted-foreground"><?= __('Format: slug1,slug2,slug3') ?></p>
                            </div>
                            <div>
                                <select x-model="columnMapping['terms:' + term.type]"
                                    class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                                    <option value=""><?= __('-- Select Column --') ?></option>
                                    <template x-for="col in jsonColumns" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Section 2: Fields Mapping -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <i data-lucide="list" class="h-5 w-5"></i>
                <h4 class="text-base font-semibold"><?= __('Post Fields') ?></h4>
            </div>
            <div class="space-y-2">
                <template x-for="field in availableFields" :key="field.field_name">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 border rounded-lg hover:bg-muted/30">
                        <div class="flex flex-col">
                            <label class="text-sm font-medium" x-text="field.label || field.field_name"></label>
                            <span x-show="field.required" class="text-xs text-red-500"><?= __('Required') ?></span>
                        </div>
                        <div>
                            <select x-model="columnMapping[field.field_name]"
                                class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                                <option value=""><?= __('-- Select Column --') ?></option>
                                <template x-for="col in jsonColumns" :key="col">
                                    <option :value="col" x-text="col"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Preview -->
        <div x-show="previewData.length > 0" class="space-y-2">
            <h4 class="text-sm font-semibold"><?= __('Preview (First 5 items)') ?></h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <template x-for="col in jsonColumns" :key="col">
                                <th class="p-2 text-left" x-text="col"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in previewData" :key="idx">
                            <tr class="border-b">
                                <template x-for="col in jsonColumns" :key="col">
                                    <td class="p-2" x-text="item[col]"></td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-4 border-t">
            <button type="button" @click="currentStep = 1"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                <i data-lucide="arrow-left" class="h-4 w-4 mr-2"></i>
                <?= __('Back') ?>
            </button>
            
            <button type="button" @click="startImport()"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <?= __('Start Import') ?>
                <i data-lucide="arrow-right" class="h-4 w-4 ml-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 3: Import Progress -->
    <div x-show="currentStep === 3" class="bg-card rounded-lg p-2 space-y-6">
        <div>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="loader" class="h-5 w-5"></i>
                <?= __('Step 3: Importing Data') ?>
            </h3>
            <p class="text-sm text-muted-foreground"><?= __('Please wait while data is being imported') ?></p>
        </div>

        <!-- Import Progress -->
        <div class="space-y-4">
            <div class="bg-muted/50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium" x-text="importStatus"></span>
                    <span class="text-sm text-muted-foreground" x-text="`${importedCount} / ${totalItems}`"></span>
                </div>
                <div class="w-full bg-muted rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all" 
                         :style="`width: ${importProgress}%`"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                    <span x-text="`Imported: ${statsImported}`"></span>
                    <span x-text="`Updated: ${statsUpdated}`"></span>
                    <span x-text="`Skipped: ${statsSkipped}`"></span>
                    <span x-text="`Errors: ${statsErrors}`"></span>
                </div>
            </div>

            <!-- Errors List -->
            <div x-show="errors.length > 0" class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-destructive mb-2"><?= __('Errors') ?></h4>
                <div class="space-y-1 max-h-64 overflow-y-auto">
                    <template x-for="(error, idx) in errors" :key="idx">
                        <p class="text-xs text-destructive" x-text="error"></p>
                    </template>
                </div>
            </div>

            <!-- Success Message -->
            <div x-show="importComplete" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
                    <span class="text-sm font-medium text-green-700" x-text="importCompleteMessage"></span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-4 border-t">
            <a :href="`<?= admin_url('posts') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>`" 
               class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                <i data-lucide="arrow-left" class="h-4 w-4 mr-2"></i>
                <?= __('Back to Posts') ?>
            </a>
            
            <button type="button" @click="resetImport()" x-show="importComplete"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <i data-lucide="refresh-cw" class="h-4 w-4 mr-2"></i>
                <?= __('Import Another File') ?>
            </button>
        </div>
    </div>
</div>

<script>
function importForm() {
    return {
        currentStep: 1,
        isUploading: false,
        uploadProgress: 0,
        fileUploaded: false,
        fileName: '',
        filePath: '',
        jsonColumns: [],
        totalItems: 0,
        previewData: [],
        columnMapping: {},
        availableFields: <?= json_encode($availableFields ?? []) ?>,
        posttypeTerms: <?= json_encode($posttype_terms) ?>,
        
        // Import progress
        isImporting: false,
        importStatus: '',
        importedCount: 0,
        importProgress: 0,
        statsImported: 0,
        statsUpdated: 0,
        statsSkipped: 0,
        statsErrors: 0,
        errors: [],
        importComplete: false,
        importCompleteMessage: '',
        
        async handleFileUpload(event) {
            const file = event.target.files[0];
            if(!file) return;
            
            this.fileName = file.name;
            this.isUploading = true;
            this.uploadProgress = 0;
            
            try {
                const formData = new FormData();
                formData.append('action', 'upload_json');
                formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
                formData.append('json_file', file);
                
                // Simulate progress
                const progressInterval = setInterval(() => {
                    if(this.uploadProgress < 90) {
                        this.uploadProgress += 10;
                    }
                }, 100);
                
                const response = await fetch('<?= admin_url('posts/import') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>', {
                    method: 'POST',
                    body: formData
                });
                
                clearInterval(progressInterval);
                this.uploadProgress = 100;
                
                const result = await response.json();
                
                if(!result.success) {
                    throw new Error(result.message || 'Upload failed');
                }
                
                // Store upload data
                this.filePath = result.data.file_path;
                this.jsonColumns = result.data.columns;
                this.totalItems = result.data.total_items;
                this.previewData = result.data.preview;
                this.fileUploaded = true;
                
                // Auto-map columns if names match
                this.autoMapColumns();
                
            } catch(error) {
                console.error('Upload error:', error);
                
                // Show error notification
                if (window.fastNotice) {
                    window.fastNotice.show('<?= __('Upload failed:') ?> ' + error.message, 'error');
                }
                
                this.resetUpload();
            } finally {
                this.isUploading = false;
            }
        },
        
        autoMapColumns() {
            // Auto-map columns with matching names
            this.availableFields.forEach(field => {
                const fieldName = field.field_name;
                if(this.jsonColumns.includes(fieldName)) {
                    this.columnMapping[fieldName] = fieldName;
                }
            });
            
            // Auto-map terms columns (terms:type format)
            this.posttypeTerms.forEach(term => {
                const termKey = 'terms:' + term.type;
                if(this.jsonColumns.includes(termKey)) {
                    this.columnMapping[termKey] = termKey;
                }
            });
        },
        
        goToMapping() {
            // Validate: Check if required fields are missing in JSON
            const requiredFields = this.availableFields.filter(f => f.required);
            const missingRequiredFields = [];
            
            for(const field of requiredFields) {
                const fieldName = field.field_name;
                if(!this.jsonColumns.includes(fieldName)) {
                    missingRequiredFields.push(field.label || fieldName);
                }
            }
            
            // Show warning if required fields are missing
            if(missingRequiredFields.length > 0) {
                const fieldsList = missingRequiredFields.join(', ');
                const message = `⚠️ <?= __('Warning') ?>: <?= __('The following required fields are missing in your JSON file') ?>:\n\n${fieldsList}\n\n<?= __('Items without these fields will be skipped during import. Do you want to continue?') ?>`;
                
                if(!confirm(message)) {
                    return; // Don't proceed to mapping step
                }
            }
            
            this.currentStep = 2;
        },
        
        async startImport() {
            if(!this.filePath) {
                alert('<?= __('No file uploaded') ?>');
                return;
            }
            
            this.currentStep = 3;
            this.isImporting = true;
            this.importedCount = 0;
            this.importProgress = 0;
            this.statsImported = 0;
            this.statsUpdated = 0;
            this.statsErrors = 0;
            this.errors = [];
            this.importComplete = false;
            
            try {
                const batchSize = 100;
                let startIndex = 0;
                let hasMore = true;
                
                while(hasMore) {
                    const batchNumber = Math.floor(startIndex / batchSize) + 1;
                    this.importStatus = `<?= __('Importing batch') ?> ${batchNumber}...`;
                    
                    const formData = new FormData();
                    formData.append('action', 'import_json');
                    formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
                    formData.append('file_path', this.filePath);
                    formData.append('column_mapping', JSON.stringify(this.columnMapping));
                    formData.append('start_index', startIndex);
                    formData.append('batch_size', batchSize);
                    
                    const response = await fetch('<?= admin_url('posts/import') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if(!result.success) {
                        throw new Error(result.message || 'Import failed');
                    }
                    
                    // Update stats
                    this.statsImported += result.data.imported || 0;
                    this.statsUpdated += result.data.updated || 0;
                    this.statsSkipped += result.data.skipped || 0;
                    
                    if(result.data.errors && result.data.errors.length > 0) {
                        this.errors = this.errors.concat(result.data.errors);
                        this.statsErrors += result.data.errors.length;
                    }
                    
                    // Update progress
                    startIndex += batchSize;
                    this.importedCount = Math.min(startIndex, this.totalItems);
                    this.importProgress = Math.min(100, Math.round((this.importedCount / this.totalItems) * 100));
                    
                    // Check if there are more items
                    hasMore = startIndex < this.totalItems;
                    
                    // Add small delay to prevent overwhelming the server
                    if(hasMore) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }
                }
                
                // Import complete
                this.importStatus = '<?= __('Import complete!') ?>';
                this.importProgress = 100;
                this.importComplete = true;
                this.importCompleteMessage = `<?= __('Successfully imported') ?> ${this.statsImported} <?= __('posts') ?>, <?= __('updated') ?> ${this.statsUpdated} <?= __('posts') ?>, <?= __('skipped') ?> ${this.statsSkipped} <?= __('posts') ?>.`;
                
                // Show success notification
                if (window.fastNotice) {
                    window.fastNotice.show(this.importCompleteMessage, 'success');
                }
                
            } catch(error) {
                console.error('Import error:', error);
                
                // Show error notification
                if (window.fastNotice) {
                    window.fastNotice.show('<?= __('Import failed:') ?> ' + error.message, 'error');
                }
                
                this.importStatus = '<?= __('Import failed') ?>';
            } finally {
                this.isImporting = false;
            }
        },
        
        resetUpload() {
            this.fileUploaded = false;
            this.fileName = '';
            this.filePath = '';
            this.jsonColumns = [];
            this.totalItems = 0;
            this.previewData = [];
            this.columnMapping = {};
            document.getElementById('jsonFile').value = '';
        },
        
        resetImport() {
            this.currentStep = 1;
            this.resetUpload();
            this.importedCount = 0;
            this.importProgress = 0;
            this.statsImported = 0;
            this.statsUpdated = 0;
            this.statsSkipped = 0;
            this.statsErrors = 0;
            this.errors = [];
            this.importComplete = false;
        }
    }
}
</script>

<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>

