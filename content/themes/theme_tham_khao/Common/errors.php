<?php
namespace System\Libraries;

use System\Libraries\Render;
use App\Libraries\Fastlang as Flang;

Flang::load('Errors', APP_LANG);
load_helpers(['frontend', 'languages']);

// Extract controller info from trace if available
$controllerInfo = '';
if (!empty($trace) && preg_match('/App\\\\Controllers\\\\([^:]+)/', $trace, $matches)) {
    $controllerInfo = $matches[1];
}
?>
<!DOCTYPE html>
<html lang="<?php echo APP_LANG; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Error <?php echo $statusCode; ?> - Internal Server Error</title>
    <meta name="description" content="An internal server error occurred. Error <?php echo $statusCode; ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo base_url('favicon.ico'); ?>">

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

<script src="https://cdn.tailwindcss.com"></script>

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

.copy-btn {
    transition: all 0.2s ease-in-out;
}

.copy-btn:hover {
    transform: scale(1.05);
}

.copy-btn:active {
    transform: scale(0.95);
}

/* Custom scrollbar for stack trace */
pre::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

pre::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

pre::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

pre::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
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
            <h1 class="text-4xl font-bold text-foreground mb-2">Error <?php echo $statusCode; ?></h1>
            <p class="text-muted-foreground text-lg">Internal Server Error</p>
        </div>
        
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Error Details & Stack Trace -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-5 h-5 text-destructive">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg>
                            <?php echo __('Error Details', 'Error Details'); ?>
                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80 ml-auto"><?php echo $statusCode; ?></div>
                        </h3>
                    </div>
                    <div class="p-6 pt-0 space-y-6">
                        <!-- Error Message -->
                        <div class="p-4 bg-rose-50 border border-rose-200 rounded-lg">
                            <p class="text-rose-800 font-medium"><?php echo $message; ?></p>
                        </div>
                        
                        <?php if ($debug): ?>
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
                                    <code id="error-file" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700 break-all"><?php echo $file; ?></code>
                                    <button type="button" for="error-file" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200" title="Copy file path">
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
                                    <code id="error-line" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700"><?php echo $line; ?></code>
                                    <button type="button" for="error-line" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200" title="Copy line number">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($controllerInfo): ?>
                        <!-- Controller Information -->
                        <div class="space-y-2">
                            <h4 class="font-medium text-foreground flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code w-4 h-4 text-muted-foreground">
                                    <polyline points="16,18 22,12 16,6"></polyline>
                                    <polyline points="8,6 2,12 8,18"></polyline>
                                </svg>
                                Controller
                            </h4>
                            <div class="flex items-center gap-2">
                                <code id="error-controller" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700 break-all"><?php echo $controllerInfo; ?></code>
                                <button type="button" for="error-controller" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200" title="Copy controller">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Stack Trace -->
                        <?php if (!empty($trace)): ?>
                        <div class="space-y-2">
                            <h4 class="font-medium text-foreground flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bug w-4 h-4 text-muted-foreground">
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
                                Stack Trace
                            </h4>
                            <div class="flex items-start gap-2">
                                <pre id="error-trace" class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700 overflow-x-auto whitespace-pre-wrap break-all max-h-96 overflow-y-auto"><?php echo htmlspecialchars($trace); ?></pre>
                                <button type="button" for="error-trace" class="copybutton inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 h-10 w-10 rounded-md border border-gray-200 bg-white shadow-sm" title="Copy stack trace">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight"><?php echo __('Quick Actions', 'Quick Actions'); ?></h3>
                    </div>
                    <div class="p-6 pt-0 space-y-3">
                        <a class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full flex items-center justify-center gap-2" href="<?php echo base_url('dashboard'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house w-4 h-4">
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            </svg>
                            <?php echo __('Go to Dashboard', 'Go to Dashboard'); ?>
                        </a>
                        <a class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full bg-transparent flex items-center justify-center gap-2" href="<?php echo base_url(); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left w-4 h-4">
                                <path d="m12 19-7-7 7-7"></path>
                                <path d="M19 12H5"></path>
                            </svg>
                            <?php echo __('Back to Homepage', 'Back to Homepage'); ?>
                        </a>
                        <button type="button" class="whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full flex items-center justify-center gap-2" onclick="location.reload()">
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
                
                <!-- About This Error -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight"><?php echo __('About This Error', 'About This Error'); ?></h3>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div class="space-y-2">
                            <h4 class="font-medium text-foreground"><?php echo __('Internal Server Error (500)', 'Internal Server Error (500)'); ?></h4>
                            <ul class="text-sm text-muted-foreground space-y-1 list-disc list-inside">
                                <li><?php echo __('Something went wrong on the server', 'Something went wrong on the server'); ?></li>
                                <li><?php echo __('This is usually a temporary issue', 'This is usually a temporary issue'); ?></li>
                                <li><?php echo __('Try refreshing the page', 'Try refreshing the page'); ?></li>
                                <li><?php echo __('Contact support if the problem persists', 'Contact support if the problem persists'); ?></li>
                            </ul>
                        </div>
                        <div data-orientation="horizontal" role="none" class="shrink-0 bg-border h-[1px] w-full"></div>
                        <div class="space-y-2">
                            <h4 class="font-medium text-foreground"><?php echo __('What you can do:', 'What you can do:'); ?></h4>
                            <ul class="text-sm text-muted-foreground space-y-1 list-disc list-inside">
                                <li><?php echo __('Try refreshing the page', 'Try refreshing the page'); ?></li>
                                <li><?php echo __('Go back to the previous page', 'Go back to the previous page'); ?></li>
                                <li><?php echo __('Check your permissions', 'Check your permissions'); ?></li>
                                <li><?php echo __('Contact support with the error details', 'Contact support with the error details'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm mt-8">
            <div class="p-6">
                <div class="text-center space-y-2">
                    <p class="text-sm text-muted-foreground"><?php echo __('errors.404.still_cant_find', 'Still can\'t find what you\'re looking for?'); ?></p>
                    <div class="flex flex-col sm:flex-row gap-2 justify-center">
                        <a class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3" href="mailto:<?= option('site_email') ?>">
                            <?php echo __('errors.404.contact_support', 'Contact Support'); ?>
                        </a>
                        <a class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3" href="<?php echo base_url('help'); ?>">
                            <?php echo __('errors.404.view_docs', 'View Documentation'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>