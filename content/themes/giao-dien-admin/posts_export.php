<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Backend/Global', APP_LANG);
Flang::load('Backend/Posts', APP_LANG);

$posttype_slug = $posttype['slug'] ?? 'posts';
$posttype_name = $posttype['name'] ?? 'Posts';
$posttype_fields = $posttype['fields'] ?? [];
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
      'name' => __('Export'),
      'url' => admin_url('posts/export') . '?type=' . $posttype_slug . '&post_lang=' . $currentLang,
      'active' => true
  ]
);
view_header([
    'title' => __('Export') . ' ' . $posttype_name,
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);
?>

<div x-data="exportForm()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground"><?= __('Export') ?> <?= $posttype_name ?></h1>
                <p class="text-sm text-muted-foreground"><?= __('Filter and export posts to JSON file') ?></p>
            </div>
        </div>
    </div>

    <!-- Filters Bar (giống Posts Index) -->
    <div class="bg-card rounded-xl mb-4">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto flex-wrap">
                <!-- Search -->
                <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
                    <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
                    <input type="text" x-model="filters.search" placeholder="<?= __('Search') ?>..."
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm pl-10 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>

                <!-- Status Filter -->
                <div class="min-w-[150px] w-full sm:w-auto">
                    <select x-model="filters.status" 
                        class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value=""><?= __('All Statuses') ?></option>
                        <option value="active"><?= __('Active') ?></option>
                        <option value="pending"><?= __('Pending') ?></option>
                        <option value="inactive"><?= __('Inactive') ?></option>
                        <option value="draft"><?= __('Draft') ?></option>
                        <option value="schedule"><?= __('Scheduled') ?></option>
                        <option value="deleted"><?= __('Deleted') ?></option>
                    </select>
                </div>

                <!-- Limit -->
                <div class="min-w-[120px] w-full sm:w-auto">
                    <select x-model="filters.limit"
                        class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="100">100</option>
                        <option value="500" selected>500</option>
                        <option value="1000">1000</option>
                        <option value="-1"><?= __('All') ?></option>
                    </select>
                </div>
            </div>

            <!-- Count Button -->
            <div class="flex gap-2 w-full lg:w-auto">
                <button @click="countPosts()" type="button"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 whitespace-nowrap w-full">
                    <i data-lucide="hash" class="h-4 w-4 mr-2"></i>
                    <?= __('Count') ?>
                </button>
            </div>
        </div>

        <!-- Posts Count Display -->
        <div x-show="postsCount !== null" class="px-4 pb-4">
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                <span class="text-sm font-medium text-blue-900" x-text="`✓ Found ${postsCount} posts`"></span>
            </div>
        </div>
    </div>

    <!-- Field Selection -->
    <div class="bg-card p-2 space-y-6">
        
        <!-- Step 1: Select Fields to Export -->
        <div class="space-y-6">
            <!-- Section 1: Terms/Taxonomies -->
            <?php if(!empty($posttype_terms)): ?>
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <i data-lucide="tag" class="h-5 w-5"></i>
                            <?= __('Terms / Taxonomies') ?>
                        </h3>
                        <p class="text-sm text-muted-foreground"><?= __('Select terms to include in export') ?></p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" @change="toggleAllTerms($event.target.checked)"
                            class="rounded border-input text-primary focus:ring-primary">
                        <span class="text-sm font-medium"><?= __('Check All') ?></span>
                    </label>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    <?php foreach($posttype_terms as $term): ?>
                        <label class="flex items-center space-x-2 p-2 rounded hover:bg-muted/50 cursor-pointer border border-input">
                            <input type="checkbox" value="<?= htmlspecialchars($term['type']) ?>" x-model="selectedTerms"
                                class="rounded border-input text-primary focus:ring-primary">
                            <span class="text-sm"><?= htmlspecialchars($term['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section 2: All Fields -->
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <i data-lucide="list" class="h-5 w-5"></i>
                            <?= __('Fields to Export') ?>
                        </h3>
                        <p class="text-sm text-muted-foreground"><?= __('Select fields to include in the export') ?></p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" @change="toggleAllFields($event.target.checked)"
                            class="rounded border-input text-primary focus:ring-primary">
                        <span class="text-sm font-medium"><?= __('Check All') ?></span>
                    </label>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    <!-- ID field (always required) -->
                    <label class="flex items-center space-x-2 p-2 rounded bg-muted/30 cursor-not-allowed border border-input">
                        <input type="checkbox" value="id" x-model="selectedFields" checked disabled
                            class="rounded border-input text-primary focus:ring-primary">
                        <span class="text-sm font-medium">ID <span class="text-red-500">*</span></span>
                    </label>
                    
                    <!-- All dynamic fields from posttype -->
                    <?php if(!empty($posttype_fields)): ?>
                        <?php foreach($posttype_fields as $field): 
                            $fieldName = $field['field_name'] ?? '';
                            $fieldLabel = $field['label'] ?? ucfirst($fieldName);
                            
                            // Skip ID (already shown above)
                            if($fieldName === 'id') continue;
                            if ($field['type'] == 'Taxonomy'){
                                continue;
                            }
                        ?>
                            <label class="flex items-center space-x-2 p-2 rounded hover:bg-muted/50 cursor-pointer border border-input">
                                <input type="checkbox" value="<?= htmlspecialchars($fieldName) ?>" x-model="selectedFields"
                                    class="rounded border-input text-primary focus:ring-primary">
                                <span class="text-sm"><?= htmlspecialchars($fieldLabel) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Export Progress -->
        <div x-show="isExporting" class="space-y-4">
            <div class="bg-muted/50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium" x-text="exportStatus"></span>
                    <span class="text-sm text-muted-foreground" x-text="`${exportedCount} / ${totalCount} items`"></span>
                </div>
                <div class="w-full bg-muted rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all" 
                         :style="`width: ${exportProgress}%`"></div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-4 border-t">
            <a :href="`<?= admin_url('posts') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>`" 
               class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                <i data-lucide="arrow-left" class="h-4 w-4 mr-2"></i>
                <?= __('Back') ?>
            </a>
            
            <button type="button" @click="startExport()" :disabled="isExporting"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 disabled:opacity-50 disabled:pointer-events-none">
                <i data-lucide="download" class="h-4 w-4 mr-2"></i>
                <span x-text="isExporting ? '<?= __('Exporting...') ?>' : '<?= __('Export to JSON') ?>'"></span>
            </button>
        </div>
    </div>
</div>

<script>
function exportForm() {
    return {
        selectedFields: ['id'],
        selectedTerms: [],
        filters: {
            search: '',
            status: '',
            limit: 500
        },
        postsCount: null,
        isExporting: false,
        exportStatus: '<?= __('Preparing export...') ?>',
        exportedCount: 0,
        totalCount: 0,
        exportProgress: 0,
        allData: [],
        
        toggleAllTerms(checked) {
            if (checked) {
                // Add all term types
                this.selectedTerms = <?= json_encode(array_column($posttype_terms, 'type')) ?>;
            } else {
                // Clear all
                this.selectedTerms = [];
            }
        },
        
        toggleAllFields(checked) {
            if (checked) {
                // Add all field names (including ID)
                const allFieldNames = <?= json_encode(array_column($posttype_fields, 'field_name')) ?>;
                this.selectedFields = ['id', ...allFieldNames.filter(f => f !== 'id')];
            } else {
                // Keep only ID (required)
                this.selectedFields = ['id'];
            }
        },
        
        async countPosts() {
            try {
                const formData = new FormData();
                formData.append('action', 'count_posts');
                formData.append('search', this.filters.search);
                formData.append('status', this.filters.status);
                
                const response = await fetch('<?= admin_url('posts/export') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if(result.success) {
                    this.postsCount = result.count;
                }
            } catch(error) {
                console.error('Count error:', error);
            }
        },
        
        async startExport() {
            if(this.selectedFields.length === 0) {
                if (window.fastNotice) {
                    window.fastNotice.show('<?= __('Please select at least one field') ?>', 'error');
                } else {
                    alert('<?= __('Please select at least one field') ?>');
                }
                return;
            }
            
            this.isExporting = true;
            this.exportedCount = 0;
            this.totalCount = 0;
            this.allData = [];
            this.exportStatus = '<?= __('Starting export...') ?>';
            
            try {
                let page = 1;
                let hasNext = true;
                
                while(hasNext) {
                    this.exportStatus = `<?= __('Fetching page') ?> ${page}...`;
                    
                    const formData = new FormData();
                    formData.append('action', 'export_posts');
                    formData.append('csrf_token', '<?= Session::csrf_token(600) ?>');
                    formData.append('fields', JSON.stringify(this.selectedFields));
                    formData.append('terms', JSON.stringify(this.selectedTerms));
                    formData.append('page', page);
                    formData.append('search', this.filters.search);
                    formData.append('status', this.filters.status);
                    formData.append('limit', this.filters.limit);
                    
                    const response = await fetch('<?= admin_url('posts/export') ?>?type=<?= $posttype_slug ?>&post_lang=<?= $currentLang ?>', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if(!result.success) {
                        throw new Error(result.message || 'Export failed');
                    }
                    
                    // Add data to collection
                    this.allData = this.allData.concat(result.data);
                    this.exportedCount = this.allData.length;
                    this.totalCount = this.exportedCount;
                    
                    // Check if there are more pages
                    hasNext = result.is_next;
                    if(hasNext) {
                        page++;
                        // Calculate approximate progress
                        this.exportProgress = Math.min(90, (page - 1) * 10);
                    } else {
                        this.exportProgress = 95;
                    }
                }
                
                // Export complete - download file
                this.exportStatus = '<?= __('Generating file...') ?>';
                this.exportProgress = 100;
                
                const filename = '<?= $posttype_slug ?>_' + Date.now() + '.json';
                const blob = new Blob([JSON.stringify(this.allData, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                a.click();
                URL.revokeObjectURL(url);
                
                this.exportStatus = '<?= __('Export complete!') ?>';
                
                // Show success notification
                if (window.fastNotice) {
                    window.fastNotice.show(`<?= __('Exported') ?> ${this.allData.length} <?= __('posts successfully') ?>`, 'success');
                }
                
                setTimeout(() => {
                    this.isExporting = false;
                    this.exportProgress = 0;
                }, 2000);
                
            } catch(error) {
                console.error('Export error:', error);
                
                // Show error notification
                if (window.fastNotice) {
                    window.fastNotice.show('<?= __('Export failed:') ?> ' + error.message, 'error');
                } else {
                    alert('<?= __('Export failed:') ?> ' + error.message);
                }
                
                this.isExporting = false;
            }
        }
    }
}
</script>

<?php view_footer(); ?>

