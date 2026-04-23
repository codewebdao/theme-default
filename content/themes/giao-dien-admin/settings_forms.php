<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;
use System\Libraries\Session;

$settingType = $settingType ?? 'general';
$formHtml = $formHtml ?? '';

$breadcrumbs = [
    [
        'name' => __('Dashboard'),
        'url' => admin_url('home')
    ],
    [
        'name' => __('Settings'),
        'url' => admin_url('settings')
    ],
    [
        'name' => $title ?? __('Settings'),
        'url' => admin_url("settings/{$settingType}"),
        'active' => true
    ]
];

view_header([
    'title' => $title ?? __('Settings'),
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumbs,
]);

// Flash messages
$successMessage = Session::flash('success');
$errorMessage = Session::flash('error');
$warningMessage = Session::flash('warning');
?>

<div class="container mx-auto">
    <!-- Flash Messages -->
    <?php if ($successMessage): ?>
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center" 
         x-data="{ show: true }" 
         x-show="show" 
         x-transition>
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?= esc_html($successMessage) ?></span>
        <button @click="show = false" class="ml-auto text-green-700 hover:text-green-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center" 
         x-data="{ show: true }" 
         x-show="show" 
         x-transition>
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span><?= esc_html($errorMessage) ?></span>
        <button @click="show = false" class="ml-auto text-red-700 hover:text-red-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($warningMessage): ?>
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg flex items-center" 
         x-data="{ show: true }" 
         x-show="show" 
         x-transition>
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span><?= esc_html($warningMessage) ?></span>
        <button @click="show = false" class="ml-auto text-yellow-700 hover:text-yellow-900">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>

    <!-- Settings Form -->
    <div class="bg-white">
        <?= $formHtml ?>
    </div>

    <!-- Help Section (Context-specific based on setting type) -->
    <?php if ($settingType === 'performance'): ?>
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 mb-1">
                    <?= __('Performance Tips') ?>
                </h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><strong><?= __('Cache:') ?></strong> <?= __('Enable application cache to improve response times') ?></li>
                    <li><strong><?= __('Nginx:') ?></strong> <?= __('Configure Nginx URI cache for static content caching') ?></li>
                    <li><strong><?= __('Minify:') ?></strong> <?= __('Enable CSS/JS minification for production environments only') ?></li>
                </ul>
                
                <!-- Nginx Purge Tools -->
                <div class="mt-4 pt-4 border-t border-blue-200" x-data="nginxPurge()">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2"><?= __('Nginx Cache Management') ?></h4>
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="url" 
                               placeholder="<?= __('Enter URL to purge') ?>"
                               class="flex-1 px-3 py-2 text-sm border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button @click="purgeUrl()" 
                                :disabled="loading || !url"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!loading"><?= __('Purge URL') ?></span>
                            <span x-show="loading"><?= __('Purging...') ?></span>
                        </button>
                        <button @click="purgeAll()" 
                                :disabled="loading"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50">
                            <span x-show="!loading"><?= __('Purge All') ?></span>
                            <span x-show="loading"><?= __('Purging...') ?></span>
                        </button>
                    </div>
                    <p x-show="message" 
                       :class="success ? 'text-green-700' : 'text-red-700'" 
                       class="mt-2 text-sm" 
                       x-text="message"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function nginxPurge() {
        return {
            url: '',
            loading: false,
            message: '',
            success: false,
            
            async purgeUrl() {
                if (!this.url) return;
                
                this.loading = true;
                this.message = '';
                
                try {
                    const response = await fetch('<?= admin_url("settings/purge-nginx") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            csrf_token: '<?= $csrf_token ?? "" ?>',
                            url: this.url
                        })
                    });
                    
                    const data = await response.json();
                    this.message = data.message || '<?= __("Unknown error") ?>';
                    this.success = data.success;
                    
                    if (data.success) {
                        this.url = '';
                    }
                } catch (error) {
                    this.message = '<?= __("Failed to purge cache") ?>';
                    this.success = false;
                }
                
                this.loading = false;
            },
            
            async purgeAll() {
                if (!confirm('<?= __("Are you sure you want to purge all cache?") ?>')) {
                    return;
                }
                
                this.loading = true;
                this.message = '';
                
                try {
                    const response = await fetch('<?= admin_url("settings/purge-nginx") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            csrf_token: '<?= $csrf_token ?? "" ?>',
                            purge_all: '1'
                        })
                    });
                    
                    const data = await response.json();
                    this.message = data.message || '<?= __("Unknown error") ?>';
                    this.success = data.success;
                } catch (error) {
                    this.message = '<?= __("Failed to purge cache") ?>';
                    this.success = false;
                }
                
                this.loading = false;
            }
        }
    }
    </script>
    <?php endif; ?>

</div>

<?php view_footer(); ?>

