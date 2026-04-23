<?php
namespace System\Libraries;

use App\Libraries\Fastlang as Flang;

Flang::load('404', APP_LANG);
load_helpers(['languages']);
?>
<!DOCTYPE html>
<html lang="<?php echo APP_LANG; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Page Not Found - 404 Error</title>
    <meta name="description" content="The page you are looking for could not be found. Error 404.">
    <link rel="icon" type="image/x-icon" href="<?php echo base_url('favicon.ico'); ?>">

<script src="https://cdn.tailwindcss.com"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle all copy buttons
    document.querySelectorAll('.copybutton').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('for');
            const targetElement = document.getElementById(targetId);
            
            if (!targetElement) {
                console.error('Target element not found:', targetId);
                return;
            }
            
            const textToCopy = targetElement.textContent || targetElement.innerText;
            const originalHTML = this.innerHTML;
            
            // Show loading
            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>';
            
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Success
                this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M20 6 9 17l-5-5"></path></svg>';
                this.classList.add('bg-green-50', 'border-green-200');
                
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.classList.remove('bg-green-50', 'border-green-200');
                }, 1500);
            }).catch(() => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                this.innerHTML = originalHTML;
            });
        });
    });
});
</script>

<style>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Smooth transitions for all interactive elements */
* {
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}
</style>

</head>
<body>

<main class="min-h-screen bg-background p-4">
    <div class="max-w-6xl mx-auto py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-foreground mb-2">Error 404</h1>
            <p class="text-muted-foreground text-lg">Page Not Found</p>
        </div>
        
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6 rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-5 h-5 text-destructive">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                        <?php echo __('Oops! Something went wrong'); ?>
                    </h3>
                </div>
                <div class="p-6 pt-0 space-y-6">
                    <div class="text-center">
                        <div class="text-8xl font-bold text-primary/20 select-none mb-4">404</div>
                        <h2 class="text-2xl font-semibold text-foreground mb-2"><?php echo __('Page Not Found'); ?></h2>
                        <p class="text-muted-foreground"><?php echo __('Oops! The page you\'re looking for seems to have wandered off into the digital void. Don\'t worry, even the best explorers sometimes take a wrong turn.'); ?></p>
                    </div>
                    <div class="flex flex-col gap-3">
                        <a class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-black text-white text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full flex items-center justify-center gap-2" href="<?php echo base_url(); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house w-4 h-4">
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            </svg>
                            <?php echo __('Back to Home'); ?>
                        </a>
                        <a class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full bg-transparent flex items-center justify-center gap-2" href="javascript:history.back()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left w-4 h-4">
                                <path d="m12 19-7-7 7-7"></path>
                                <path d="M19 12H5"></path>
                            </svg>
                            <?php echo __('Go Back'); ?>
                        </a>
                        <button class="whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full flex items-center justify-center gap-2" onclick="location.reload()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw w-4 h-4">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                <path d="M8 16H3v5"></path>
                            </svg>
                            <?php echo __('Refresh Page', 'Refresh Page'); ?>
                        </button>
                    </div>
                </div>
                
                <?php if ($debug): ?>
                <!-- Debug Information -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bug w-5 h-5 text-muted-foreground">
                                <path d="m8 2 1.88 1.88"></path>
                                <path d="M14.12 3.88 16 2"></path>
                                <path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"></path>
                                <path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"></path>
                                <path d="M12 20v-9"></path>
                                <path d="M6.53 9C4.6 8.8 3 7.1 3 5"></path>
                                <path d="M6 13H2"></path>
                                <path d="M3 21c0-2.1 1.7-3.9 3.8-4"></path>
                                <path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"></path>
                                <path d="M22 13h-4"></path>
                                <path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"></path>
                            </svg>
                            Debug Information
                        </h3>
                    </div>
                    <div class="p-6 pt-0 space-y-6">
                        <!-- Error Message -->
                        <div class="p-4 bg-rose-50 border border-rose-200 rounded-lg">
                            <p class="text-rose-800 font-medium"><?php echo $message; ?></p>
                        </div>
                        
                        <!-- File & Line Information -->
                        <div class="grid gap-4 md:grid-cols-10">
                            <div class="md:col-span-8 space-y-2">
                                <h4 class="font-medium text-foreground flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-4 h-4 text-muted-foreground">
                                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                        <path d="M10 9H8"></path>
                                        <path d="M16 13H8"></path>
                                        <path d="M16 17H8"></path>
                                    </svg>
                                    File:
                                </h4>
                                <div class="flex items-center gap-2">
                                    <code id="debug-file" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700 break-all"><?php echo $file; ?></code>
                                    <button type="button" for="debug-file" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200" title="Copy file path">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <h4 class="font-medium text-foreground flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hash w-4 h-4 text-muted-foreground">
                                        <path d="M4 9h16"></path>
                                        <path d="M4 15h16"></path>
                                        <path d="M10 3v18"></path>
                                        <path d="M14 3v18"></path>
                                    </svg>
                                    Line:
                                </h4>
                                <div class="flex items-center gap-2">
                                    <code id="debug-line" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700"><?php echo $line; ?></code>
                                    <button type="button" for="debug-line" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200" title="Copy line number">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stack Trace -->
                        <?php if (!empty($trace)): ?>
                        <div class="space-y-2">
                            <h4 class="font-medium text-foreground flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list w-4 h-4 text-muted-foreground">
                                    <path d="M8 6h13"></path>
                                    <path d="M8 12h13"></path>
                                    <path d="M8 18h13"></path>
                                    <path d="M3 6h.01"></path>
                                    <path d="M3 12h.01"></path>
                                    <path d="M3 18h.01"></path>
                                </svg>
                                Stack Trace:
                            </h4>
                            <div class="flex items-start gap-2">
                                <pre id="debug-trace" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700 overflow-x-auto whitespace-pre-wrap break-all max-h-96 overflow-y-auto"><?php echo htmlspecialchars($trace); ?></pre>
                                <button type="button" for="debug-trace" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200 bg-white shadow-sm" title="Copy stack trace">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="space-y-6">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search w-5 h-5 text-primary">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                            <?php echo __('Quick Links', 'Quick Links'); ?>
                        </h3>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="space-y-3">
                            <a class="block p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors" href="<?php echo base_url('features'); ?>">
                                <div class="font-medium text-foreground"><?php echo __('Features'); ?></div>
                                <div class="text-sm text-muted-foreground"><?php echo __('Main features overview', 'Main features overview'); ?></div>
                            </a>
                            <a class="block p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors" href="<?php echo base_url('libraries'); ?>">
                                <div class="font-medium text-foreground"><?php echo __('Library'); ?></div>
                                <div class="text-sm text-muted-foreground"><?php echo __('Resource library', 'Resource library'); ?></div>
                            </a>
                            <a class="block p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors" href="<?php echo base_url('blog'); ?>">
                                <div class="font-medium text-foreground"><?php echo __('nav.blog'); ?></div>
                                <div class="text-sm text-muted-foreground"><?php echo __('Latest articles and news', 'Latest articles and news'); ?></div>
                            </a>
                            <a class="block p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors" href="<?php echo base_url('community'); ?>">
                                <div class="font-medium text-foreground"><?php echo __('Community'); ?></div>
                                <div class="text-sm text-muted-foreground"><?php echo __('Community and support', 'Community and support'); ?></div>
                            </a>
                            <a class="block p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors" href="<?php echo base_url('development'); ?>">
                                <div class="font-medium text-foreground"><?php echo __('nav.development'); ?></div>
                                <div class="text-sm text-muted-foreground"><?php echo __('Development'); ?></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight"><?php echo __('Need Help?'); ?></h3>
            </div>
            <div class="grid gap-6 md:grid-cols-2 p-6 pt-0 space-y-4">
                <div class="space-y-2">
                    <h4 class="font-medium text-foreground"><?php echo __('Common Causes:', 'Common Causes:'); ?></h4>
                    <ul class="text-sm text-muted-foreground space-y-1 list-disc list-inside">
                        <li><?php echo __('The URL was typed incorrectly', 'The URL was typed incorrectly'); ?></li>
                        <li><?php echo __('The page has been moved or deleted', 'The page has been moved or deleted'); ?></li>
                        <li><?php echo __('You don\'t have permission to access this page', 'You don\'t have permission to access this page'); ?></li>
                        <li><?php echo __('The link you followed is outdated', 'The link you followed is outdated'); ?></li>
                    </ul>
                </div>
                <div class="space-y-2">
                    <h4 class="font-medium text-foreground"><?php echo __('What you can do:', 'What you can do:'); ?></h4>
                    <ul class="text-sm text-muted-foreground space-y-1 list-disc list-inside">
                        <li><?php echo __('Check the URL for typos', 'Check the URL for typos'); ?></li>
                        <li><?php echo __('Use the navigation menu', 'Use the navigation menu'); ?></li>
                        <li><?php echo __('Go back to the previous page', 'Go back to the previous page'); ?></li>
                        <li><?php echo __('Contact support if the problem persists', 'Contact support if the problem persists'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm mt-8">
            <div class="p-6">
                <div class="text-center space-y-2">
                    <p class="text-sm text-muted-foreground"><?php echo __('Still can\'t find what you\'re looking for?', 'Still can\'t find what you\'re looking for?'); ?></p>
                    <div class="flex flex-col sm:flex-row gap-2 justify-center">
                        <a class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3" href="mailto:<?= option('site_email') ?>">
                            <?php echo __('Contact Support', 'Contact Support'); ?>
                        </a>
                        <a class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3" href="<?php echo base_url('help'); ?>">
                            <?php echo __('View Documentation', 'View Documentation'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
