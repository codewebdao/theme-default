<?php

use System\Libraries\Render;
use System\Libraries\Session;

$type = $managerType ?? 'plugins';
$items = $$type ?? [];
$titleText = $type === 'themes' ? __('themes management') : __('Plugin Management');
$descText = $type === 'themes' ? __('theme management description') : __('Manage and install plugins for your website');
$breadcrumbs = array(
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home')
    ],
    [
        'name' => $type === 'themes' ? __('Themes') : __('Plugins'),
        'url' => $type === 'themes' ? admin_url('themes') : admin_url('plugins'),
        'active' => true
    ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => $title ?? $titleText, 'breadcrumb' => $breadcrumbs]);
?>


<div class="" x-data="librariesManager('<?= $type ?>', <?= htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8') ?>)">
    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground"><?= $titleText ?></h1>
            <p class="text-muted-foreground"><?= $descText ?></p>
        </div>

        <?php if (Session::has_flash('success')): ?>
            <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'success', 'message' => Session::flash('success')]) ?>
        <?php endif; ?>
        <?php if (Session::has_flash('error')): ?>
            <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => Session::flash('error')]) ?>
        <?php endif; ?>

        <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'info', 'message' => 'This is Development Mode (may be have high risk), for Disable, set development to False at Config.php']) ?>

        <div dir="ltr" data-orientation="horizontal" class="w-full">
            <div role="tablist" aria-orientation="horizontal" class="items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-2" tabindex="0" data-orientation="horizontal" style="outline: none;">
                <button type="button" role="tab" :aria-selected="mainTab === 'installed'" :data-state="mainTab === 'installed' ? 'active' : 'inactive'" @click="mainTab = 'installed'" class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2" tabindex="0" data-orientation="horizontal" data-radix-collection-item=""><i data-lucide="package" class="h-4 w-4"></i><span x-text="type === 'themes' ? '<?= __('installed themes') ?>' : '<?= __('installed plugins') ?>'"></span></button>
                <button type="button" role="tab" :aria-selected="mainTab === 'add-new'" :data-state="mainTab === 'add-new' ? 'active' : 'inactive'" @click="mainTab = 'add-new'" class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><i data-lucide="plus" class="h-4 w-4"></i><span x-text="type === 'themes' ? '<?= __('add theme') ?>' : '<?= __('add plugin') ?>'"></span></button>
            </div>

            <!-- Installed tab -->
            <div :data-state="mainTab === 'installed' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel" :aria-labelledby="'tab-installed'" tabindex="0" class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4" :hidden="mainTab !== 'installed'">
                <!-- Search & bulk actions -->
                <div class="bg-card rounded-xl mb-4">
                    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center flex-1 w-full lg:w-auto">
                            <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
                                <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4"></i>
                                <input x-model="installedSearchTerm" placeholder="<?= __('place search') ?>" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10" />
                            </div>
                        </div>
                        <div class="flex gap-2" x-show="updateCount > 0">
                            <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"><i data-lucide="refresh-cw" class="h-4 w-4 mr-2"></i><?= __('update all') ?> (<span x-text="updateCount"></span>)</button>
                        </div>
                    </div>
                </div>

                <!-- Filter tabs -->
                <div dir="ltr" data-orientation="horizontal">
                    <div role="tablist" aria-orientation="horizontal" class="inline-flex items-center justify-center rounded-md bg-muted p-1 px-2 text-muted-foreground h-9" tabindex="0" data-orientation="horizontal" style="outline: none;">
                        <button type="button" role="tab" :aria-selected="installedFilterTab === 'all'" :data-state="installedFilterTab === 'all' ? 'active' : 'inactive'" @click="installedFilterTab = 'all'" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-2.5 py-1 font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm text-xs" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><?= __('all') ?> (<span x-text="items.length"></span>)</button>
                        <button type="button" role="tab" :aria-selected="installedFilterTab === 'active'" :data-state="installedFilterTab === 'active' ? 'active' : 'inactive'" @click="installedFilterTab = 'active'" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-2.5 py-1 font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm text-xs" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><?= __('active') ?> (<span x-text="activeCount"></span>)</button>
                        <button type="button" role="tab" :aria-selected="installedFilterTab === 'inactive'" :data-state="installedFilterTab === 'inactive' ? 'active' : 'inactive'" @click="installedFilterTab = 'inactive'" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-2.5 py-1 font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm text-xs" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><?= __('inactive') ?> (<span x-text="inactiveCount"></span>)</button>
                    </div>
                </div>

                <!-- List -->
                <div class="bg-card card-content mt-6 !p-0 border overflow-hidden">
                    <div class="overflow-x-auto">
                        <div class="relative w-full overflow-auto">
                            <div class="grid gap-2">
                                <template x-for="item in filteredInstalledItems" :key="item.slug">
                                    <div class="bg-card text-card-foreground rounded-lg border border-border hover:shadow-sm transition-shadow">
                                        <div class="p-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <h3 class="font-medium text-sm" x-text="item.name"></h3>
                                                        <span :class="item.is_active ? 'bg-accent text-accent-foreground' : 'bg-secondary text-secondary-foreground'" class="text-xs font-medium px-2 py-0.5 rounded-full" x-text="item.is_active ? '<?= __('active') ?>' : '<?= __('inactive') ?>'"></span>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground mb-2 truncate" x-text="item.description"></p>
                                                    <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground mb-3">
                                                        <span x-text="'v' + (item.version || '')"></span>
                                                        <span x-text="item.author"></span>
                                                        <template x-if="Array.isArray(item.categories) && item.categories.length">
                                                            <span>
                                                                <template x-for="cat in item.categories" :key="cat">
                                                                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full border border-border mr-1" x-text="cat"></span>
                                                                </template>
                                                            </span>
                                                        </template>
                                                        <template x-if="!item.categories || !item.categories.length">
                                                            <span class="text-xs font-medium px-1.5 py-0.5 rounded-full border border-border" x-text="item.category"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 ml-4" x-show="type === 'plugins'">
                                                    <!-- Show loading spinner when processing -->
                                                    <template x-if="loadingItems[item.slug]">
                                                        <div class="inline-flex h-5 w-9 items-center justify-center">
                                                            <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <!-- Show toggle button when not loading -->
                                                    <template x-if="!loadingItems[item.slug]">
                                                        <button type="button" @click="toggleItem(item)" :class="item.is_active ? 'bg-primary' : 'bg-input'" class="peer inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background data-[state=checked]:bg-primary data-[state=unchecked]:bg-input" role="switch" :aria-checked="item.is_active">
                                                            <span :class="item.is_active ? 'translate-x-4' : 'translate-x-0'" class="pointer-events-none block h-4 w-4 rounded-full bg-background shadow-lg ring-0 transition-transform"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5">
                                                
                                                
                                                <!-- Action buttons: Active/Disable and Delete -->
                                                <template x-if="type === 'plugins'">
                                                    <div class="flex gap-1.5">
                                                        <!-- Active/Disable button (normal color) -->
                                                        <button 
                                                            @click="toggleItem(item)" 
                                                            class="h-7 px-2 inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <i :data-lucide="item.is_active ? 'power' : 'power-off'" class="h-3 w-3 mr-1"></i>
                                                            <span x-text="!!item.is_active ? '<?= __('disable') ?>' : '<?= __('enable') ?>'"></span>
                                                        </button>
                                                        <!-- Delete button (primary color, disabled if active) -->
                                                        <button 
                                                            @click="deleteItem(item)" 
                                                            :disabled="!!item.is_active"
                                                            class="h-7 px-2 inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-primary text-primary-foreground hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <i data-lucide="trash2" class="h-3 w-3 mr-1"></i>
                                                            <?= __('delete') ?>
                                                        </button>
                                                    </div>
                                                </template>
                                                <template x-if="type === 'themes'">
                                                    <div class="flex gap-1.5">
                                                        <template x-if="!item.is_active">
                                                            <button type="button" @click="activateTheme(item)" class="h-8 px-3 inline-flex items-center justify-center rounded-md text-xs font-medium bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"><i data-lucide="plus" class="h-4 w-4 mr-1.5"></i><?= __('activate') ?></button>
                                                        </template>
                                                        <template x-if="!item.is_active">
                                                            <button class="h-8 px-3 inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-destructive text-destructive-foreground hover:bg-destructive/90 transition-colors" @click="deleteItem(item)"><i data-lucide="trash2" class="h-3 w-3 mr-1.5"></i><?= __('delete') ?></button>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- Custom buttons from config (Settings, etc.) -->
                                                <template x-if="!!item.is_active && Array.isArray(item.buttons) && item.buttons.length > 0">
                                                    <div class="flex gap-1.5">
                                                        <template x-for="btn in item.buttons" :key="btn.url">
                                                            <a :href="btn.url" class="h-7 px-2 inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground">
                                                                <i :data-lucide="btn.icon || 'settings'" class="h-3 w-3 mr-1"></i>
                                                                <span x-text="btn.label || '<?= __('settings') ?>'"></span>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>

                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="filteredInstalledItems.length === 0">
                                    <div class="bg-card text-card-foreground rounded-lg border border-border">
                                        <div class="text-center py-8">
                                            <i data-lucide="info" class="h-8 w-8 text-muted-foreground mx-auto mb-2"></i>
                                            <h3 class="text-sm font-medium mb-1"><?= __('No results') ?></h3>
                                            <p class="text-xs text-muted-foreground" x-text="installedSearchTerm ? (type === 'themes' ? '<?= __('no themes match') ?>' : '<?= __('no plugins match') ?>') + ' \' + installedSearchTerm + '\'' : (type === 'themes' ? '<?= __('no themes in category') ?>' : '<?= __('no plugins in category') ?>')"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-1 border-t gap-4">
                        <div class="text-sm text-muted-foreground">
                            <span x-text="'<?= __('Total') ?> ' + filteredInstalledItems.length + ' <?= __('results') ?>'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add-new tab -->
            <div :data-state="mainTab === 'add-new' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel" :aria-labelledby="'tab-add-new'" tabindex="0" class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4" :hidden="mainTab !== 'add-new'">
                <div class="bg-card text-card-foreground rounded-lg border border-border">
                    <div class="p-4 sm:p-6">
                        <div class="border-b border-border pb-4 mb-4">
                            <h3 class="text-lg font-semibold" x-text="type === 'themes' ? '<?= __('upload theme') ?>' : '<?= __('upload plugin') ?>'"></h3>
                            <p class="text-sm text-muted-foreground" x-text="type === 'themes' ? '<?= __('upload theme description') ?>' : '<?= __('upload plugin description') ?>'"></p>
                        </div>
                        <div class="space-y-4">
                            <div @dragenter.prevent="dragActive = true" @dragover.prevent="dragActive = true" @dragleave.prevent="dragActive = false" @drop.prevent="handleDrop" :class="dragActive ? 'border-primary bg-primary/10' : 'border-input'" class="border-2 border-dashed rounded-lg p-6 text-center transition-colors">
                                <i data-lucide="upload" class="h-8 w-8 text-muted-foreground mx-auto mb-3"></i>
                                <h3 class="text-sm font-medium mb-1"><?= __('drag drop files') ?></h3>
                                <p class="text-xs text-muted-foreground mb-3"><?= __('or click to select') ?></p>
                                <input type="file" multiple accept=".zip" @change="handleFileSelect($event)" class="hidden" x-ref="fileInput">
                                <button type="button" @click="$refs.fileInput.click()" class="h-8 px-3 inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground my-2"><?= __('choose zip files') ?></button>
                                <ul class="space-y-2">
                                    <template x-for="(file, idx) in selectedFiles" :key="file.name + idx">
                                        <li><span x-text="file.name"></span><button @click="removeSelectedFile(idx)" class="ml-2 text-red-500 text-xs"><?= __('remove') ?></button></li>
                                    </template>
                                </ul>
                                <button type="button" @click="uploadAllFiles" :disabled="selectedFiles.length === 0" class="h-8 px-3 inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground mt-4"><?= __('upload') ?></button>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-sm font-medium"><?= __('install from url') ?></h3>
                                <div class="flex gap-2">
                                    <input :placeholder="type === 'themes' ? 'https://example.com/theme.zip' : 'https://example.com/plugin.zip'" class="flex-1 h-9 px-3 w-full bg-background border border-input rounded-md text-xs focus:ring-ring focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-background" />
                                    <button class="h-9 px-4 inline-flex items-center justify-center rounded-md text-xs font-medium bg-primary text-primary-foreground hover:bg-primary/90"><?= __('install') ?></button>
                                </div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-950/50 border border-yellow-200 dark:border-yellow-800/50 rounded-lg p-3"><div class="flex gap-2"><i data-lucide="alert-triangle" class="h-4 w-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5"></i><div><h4 class="font-medium text-yellow-800 dark:text-yellow-200 mb-1 text-xs"><?= __('security notice') ?></h4><p class="text-xs text-yellow-700 dark:text-yellow-300"><?= __('security notice description') ?></p></div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
function librariesManager(type, items) {
    // Normalize is_active to boolean for all items
    const normalizedItems = Array.isArray(items) ? items.map(item => {
        // Convert is_active to proper boolean
        let isActive = false;
        if (item.is_active === true || item.is_active === 1 || item.is_active === '1' || item.is_active === 'true') {
            isActive = true;
        } else if (item.is_active === false || item.is_active === 0 || item.is_active === '0' || item.is_active === 'false') {
            isActive = false;
        }
        return {
            ...item,
            is_active: isActive
        };
    }) : [];
    
    return {
        type,
        items: normalizedItems,
        mainTab: 'installed',
        installedSearchTerm: '',
        installedFilterTab: 'all',
        updateCount: 0,
        dragActive: false,
        selectedFiles: [],
        loadingItems: {}, // Track loading state per item
        

        get filteredInstalledItems() {
            const term = this.installedSearchTerm.toLowerCase();
            let filtered = this.items.filter(it =>
                (it.name || '').toLowerCase().includes(term) ||
                (it.description || '').toLowerCase().includes(term)
            );
            if (this.installedFilterTab === 'active') filtered = filtered.filter(it => !!it.is_active);
            if (this.installedFilterTab === 'inactive') filtered = filtered.filter(it => !it.is_active);
            return filtered;
        },
        get activeCount() { return this.items.filter(p => !!p.is_active).length; },
        get inactiveCount() { return this.items.filter(p => !p.is_active).length; },

        showNotification(kind, msg) {
            const type = (kind === 'success' || kind === 'error' || kind === 'warning' || kind === 'info') ? kind : 'info';
            if (window.fastNotice && typeof window.fastNotice.show === 'function') {
                window.fastNotice.show(msg, type);
            } else if (window.FastNotice && typeof window.FastNotice.show === 'function') {
                window.FastNotice.show(msg, type);
            } else {
                alert(msg);
            }
        },
        toggleItem(item) {
            const action = item.is_active ? 'deactivate' : 'activate';
            const confirmMessage = item.is_active 
                ? '<?= __("Are you sure you want to deactivate this plugin? This may affect site functionality.") ?>'
                : '<?= __("Are you sure you want to activate this plugin?") ?>';
            
            // Show confirmation dialog
            if (!confirm(confirmMessage)) {
                return;
            }
            
            // Set loading state
            this.loadingItems[item.slug] = true;
            
            fetch('<?= admin_url('libraries/action') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ action, type: this.type, [this.type.slice(0, -1)]: item.slug })
            })
            .then(res => res.json())
            .then(data => {
                if (data?.success === true) {
                    item.is_active = !item.is_active;
                    this.showNotification('info', data.message || 'Success');
                    setTimeout(function(){
                        if (confirm('<?= __("Do you want to reload the page to see the changes?") ?>')) {
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    this.showNotification('error', data.message || 'Action failed');
                }
            })
            .catch(() => {
                this.showNotification('error', '<?= __("Network error occurred") ?>');
            })
            .finally(() => {
                // Remove loading state
                delete this.loadingItems[item.slug];
            });
        },
        activateTheme(item) { this.toggleItem(item); },
        deleteItem(item) {
            if (!confirm('<?= __('Are you sure you want to delete this item?') ?>')) return;
            fetch('<?= admin_url('libraries/action') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ action: 'delete', [this.type.slice(0, -1)]: item.slug })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === true) {
                    this.items = this.items.filter(p => p.slug !== item.slug);
                    this.showNotification('info', data.message || 'Deleted');
                } else {
                    this.showNotification('error', data.message || 'Action failed');
                }
            })
            .catch(() => alert('Network error'));
        },
        handleDrop(e) {
            this.dragActive = false;
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                this.mergeSelectedFiles(e.dataTransfer.files);
            }
        },
        handleFileSelect(e) {
            const files = e.target.files || e.dataTransfer.files;
            this.mergeSelectedFiles(files);
        },
        mergeSelectedFiles(fileList) {
            const validFiles = Array.from(fileList).filter(file => file.name.endsWith('.zip'));
            const allFiles = [...this.selectedFiles, ...validFiles];
            this.selectedFiles = allFiles.filter((file, idx, arr) => arr.findIndex(f => f.name === file.name) === idx);
        },
        removeSelectedFile(idx) { this.selectedFiles.splice(idx, 1); },
        async getFolderNameFromZip(file) {
            if (typeof JSZip === 'undefined') throw new Error('JSZip not loaded');
            const zip = await JSZip.loadAsync(file);
            const firstEntry = Object.keys(zip.files)[0];
            return firstEntry.split('/')[0];
        },
        async uploadAllFiles() {
            if (this.selectedFiles.length === 0) return;
            const overwriteItems = [];
            // Detect overwrite items by reading slugs
            for (const file of this.selectedFiles) {
                try {
                    const folderName = await this.getFolderNameFromZip(file);
                    const slug = (folderName || '').toLowerCase();
                    if (this.items.some(p => p.slug === slug)) overwriteItems.push(slug);
                } catch (e) { /* ignore */ }
            }
            const formData = new FormData();
            this.selectedFiles.forEach(file => formData.append('item_files[]', file));
            formData.append('overwrite_items', JSON.stringify(overwriteItems));
            formData.append('type', this.type);
            fetch('<?= admin_url('libraries/uploadWithOverwrite') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) { 
                    this.showNotification('success', data.message || '<?= __('upload success') ?>'); 
                    setTimeout(function(){
                        if (confirm('<?= __("Do you want to reload the page to see the changes?") ?>')) {
                            window.location.reload();
                        }
                    }, 500);
                } else { 
                    this.showNotification('error', data.message || '<?= __('upload error') ?>'); 
                }
            })
            .catch(() => alert('<?= __('error occurred') ?>'));
        }
    }
}
if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>
<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>


