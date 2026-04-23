<?php
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Backend/Global', APP_LANG);
Flang::load('Backend/Home', APP_LANG);

$siteUrl = config('app_url');
$timezone = config('app_timezone');
$themeActive = APP_THEME_NAME;
$logo = option('site_logo');
$brandName = option('site_brand');
$slogan = option('site_desc');

// Additional system information
$appVersion = defined('APP_VER') ? APP_VER : 'Unknown';
$currentMemory = memory_get_usage();
$memoryLimit = ini_get('memory_limit');
$uploadMaxFilesize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');

// Get statistics
$usersModel = new \App\Models\UsersModel();
$totalUsers = $usersModel->count(APP_PREFIX.'users');

// Helper function để format URL
function formatUrl($url) {
    return str_replace(['http://', 'https://'], '', $url);
}

// Helper function để get logo URL
function getLogoUrl($logo) {
    if (is_array($logo) && isset($logo['path'])) {
        return base_url('uploads/' . $logo['path']);
    }
    return base_url('uploads/default-logo.png');
}

// Helper function để get timezone offset
function getTimezoneOffset($timezone) {
    $dateTimeZone = new DateTimeZone($timezone);
    $dateTime = new DateTime('now', $dateTimeZone);
    return $dateTime->format('P');
}

// Helper function để format memory
function formatMemory($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

?>

<!-- Website Overview Component -->
<div class="bg-card text-card-foreground rounded-xl">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="p-3 rounded-full">
                <i data-lucide="monitor" class="w-6 h-6 text-primary"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-foreground"><?= __('System Dashboard') ?></h3>
                <p class="text-sm text-muted-foreground"><?= __('Monitor your website performance and system status') ?></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 text-xs rounded-full"><?= __('Active') ?></span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Brand Information -->
        <div class="space-y-4">
            
            <!-- Application Version -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="tag" class="w-4 h-4 text-accent-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Application Version') ?></p>
                    <p class="text-sm text-muted-foreground"><?= htmlspecialchars($appVersion) ?></p>
                </div>
            </div>

            <!-- Website URL -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="link" class="w-4 h-4 text-accent-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Website URL') ?></p>
                    <p class="text-sm text-muted-foreground font-mono"><?= formatUrl($siteUrl) ?></p>
                </div>
                <a href="<?= $siteUrl ?>" target="_blank" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0" title="<?= __('Visit Website') ?>">
                    <i data-lucide="external-link" class="h-4 w-4"></i>
                </a>
            </div>

            <!-- Timezone -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4 text-accent-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Timezone') ?></p>
                    <p class="text-sm text-muted-foreground"><?= htmlspecialchars($timezone) ?> (<?= getTimezoneOffset($timezone) ?>)</p>
                </div>
            </div>

            <!-- Theme Information -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-secondary/20 flex items-center justify-center">
                    <i data-lucide="palette" class="w-4 h-4 text-secondary-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Active Theme') ?></p>
                    <p class="text-sm text-muted-foreground"><?= htmlspecialchars($themeActive) ?></p>
                </div>
                <a href="<?= admin_url('libraries/themes') ?>" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0" title="<?= __('Manage Themes') ?>">
                    <i data-lucide="settings" class="h-4 w-4"></i>
                </a>
            </div>


            <!-- Total Users -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20  flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4 text-primary"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Total Users') ?></p>
                    <p class="text-sm text-muted-foreground"><?= number_format($totalUsers) ?></p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center overflow-hidden">
                    <?php if ($logo && is_array($logo)): ?>
                        <img src="<?= getLogoUrl($logo) ?>" alt="<?= htmlspecialchars($brandName) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i data-lucide="image" class="w-6 h-6 text-muted-foreground"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-foreground"><?= htmlspecialchars($brandName) ?></h4>
                    <p class="text-sm text-muted-foreground"><?= htmlspecialchars($slogan) ?></p>
                </div>
            </div>

            
        </div>

        <!-- System Information -->
        <div class="space-y-4">
            <!-- PHP Version -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="code" class="w-4 h-4 text-primary"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('PHP Version') ?></p>
                    <p class="text-sm text-muted-foreground"><?= PHP_VERSION ?></p>
                </div>
            </div>

            <!-- Server Status -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="server" class="w-4 h-4 text-accent-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Server Status') ?></p>
                    <p class="text-sm text-muted-foreground"><?= $_SERVER['SERVER_SOFTWARE'] ?? __('Unknown') ?></p>
                </div>
            </div>

            <!-- Database Status -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="database" class="w-4 h-4 text-primary"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Database') ?></p>
                    <p class="text-sm text-muted-foreground"><?= __('Connected') ?></p>
                </div>
                <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 text-xs rounded-full"><?= __('Online') ?></span>
            </div>

            <!-- Memory Usage -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-4 h-4 text-primary"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Memory Usage') ?></p>
                    <p class="text-sm text-muted-foreground"><?= formatMemory($currentMemory) ?> / <?= $memoryLimit ?></p>
                </div>
            </div>

            <!-- Upload Limits -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-secondary/20 flex items-center justify-center">
                    <i data-lucide="upload" class="w-4 h-4 text-secondary-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Upload Limit') ?></p>
                    <p class="text-sm text-muted-foreground"><?= $uploadMaxFilesize ?> (Max: <?= $postMaxSize ?>)</p>
                </div>
            </div>

            <!-- Memory Limit -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-accent/20 flex items-center justify-center">
                    <i data-lucide="hard-drive" class="w-4 h-4 text-accent-foreground"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-foreground"><?= __('Memory Limit') ?></p>
                    <p class="text-sm text-muted-foreground"><?= $memoryLimit ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 pt-6 border-t border-border">
        <h4 class="text-sm font-medium text-foreground mb-3"><?= __('Quick Actions') ?></h4>
        <div class="flex flex-wrap gap-2">
            <a href="<?= admin_url('options') ?>" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-muted rounded-lg hover:bg-accent hover:text-accent-foreground transition-colors">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <?= __('Site Settings') ?>
            </a>
            <a href="<?= admin_url('libraries/plugins') ?>" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-muted rounded-lg hover:bg-accent hover:text-accent-foreground transition-colors">
                <i data-lucide="plug" class="w-4 h-4"></i>
                <?= __('Manage Plugins') ?>
            </a>
            <a href="<?= admin_url('libraries/themes') ?>" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-muted rounded-lg hover:bg-accent hover:text-accent-foreground transition-colors">
                <i data-lucide="palette" class="w-4 h-4"></i>
                <?= __('Manage Themes') ?>
            </a>
            <a href="<?= admin_url('files') ?>" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-muted rounded-lg hover:bg-accent hover:text-accent-foreground transition-colors">
                <i data-lucide="folder" class="w-4 h-4"></i>
                <?= __('Media Library') ?>
            </a>
        </div>
    </div>
</div>

<script>
// Initialize Lucide icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
