<?php

/**
 * Template Name: Trang 404
 * Description: Trang 404 - Không tìm thấy trang.
 *
 * @package CMSFullForm
 */

namespace System\Libraries;

use System\Libraries\Render;
use App\Libraries\Fastlang as Flang;
use App\Blocks\Schema\Templates\WebPage;

Flang::load('CMS', APP_LANG);
Flang::load('404', APP_LANG);

load_helpers(['frontend', 'languages']);

// Load CSS và JS cho trang 404
Render::asset('css', '/content/themes/cmsfullform/Frontend/assets/css/404_styles.css', ['area' => 'frontend', 'location' => 'head']);
Render::asset('js', '/content/themes/cmsfullform/Frontend/assets/js/404.js', ['area' => 'frontend', 'location' => 'footer']);

// Prepare errors data for sections
$errorsLanguageData = [
    'meta' => [
        'title' => __('404 - Page Not Found'),
        'description' => __('The page you are looking for could not be found.'),
    ],
    'content' => [
        'heading' => __('Page Not Found'),
        'message' => __('Oops! The page you\'re looking for seems to have wandered off into the digital void. Don\'t worry, even the best explorers sometimes take a wrong turn.'),
    ],
    'search' => [
        'placeholder' => __('Search for pages, features, or help...'),
        'button' => __('Search'),
    ],
    'actions' => [
        'back_home' => __('Back to Home'),
        'go_back' => __('Go Back'),
    ],
    'popular_pages' => [
        'title' => __('Popular Pages'),
    ],
    'help' => [
        'title' => __('errors.404.need_help'),
        'message' => __('errors.404.help_message'),
    ],
    'navigation' => [
        'home' => __('Home'),
        'features' => __('nav.features'),
        'library' => __('nav.library'),
        'blog' => __('nav.blog'),
        'community' => __('nav.community'),
    ]
];

use App\Blocks\Meta\MetaBlock;

// Create meta tags for 404 page
$meta = new MetaBlock();

// Get current URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$meta
    ->title($errorsLanguageData['meta']['title'])
    ->description($errorsLanguageData['meta']['description'])
    ->robots('noindex, nofollow')
    ->canonical($current_url);

// Add basic meta tags
$meta
    ->custom('<meta name="generator" content="CMSFullForm">')
    ->custom('<meta name="language" content="' . APP_LANG . '">')
    ->custom('<meta name="theme-color" content="#354BD9">');

// Add Open Graph tags
$locale = APP_LANG === 'en' ? 'en_US' : 'vi_VN';
$meta
    ->og('locale', $locale)
    ->og('type', 'website')
    ->og('title', $errorsLanguageData['meta']['title'])
    ->og('description', $errorsLanguageData['meta']['description'])
    ->og('url', $current_url)
    ->og('site_name', option('site_title', APP_LANG));

// Add Twitter Card tags
$meta
    ->twitter('card', 'summary')
    ->twitter('title', $errorsLanguageData['meta']['title'])
    ->twitter('description', $errorsLanguageData['meta']['description']);

// Add favicon if available
if (option('site_logo')) {
    $logoUrl = is_string(option('site_logo')) ? option('site_logo') : base_url('assets/images/logo.png');
    $meta
        ->og('image', $logoUrl)
        ->twitter('image', $logoUrl)
        ->custom('<link rel="icon" href="' . $logoUrl . '" sizes="32x32" />')
        ->custom('<link rel="apple-touch-icon" href="' . $logoUrl . '" />');
}

// Initialize WebPage schema for 404 page
$errorSchema = new WebPage([
    '@id'             => $current_url . '#webpage',
    'name'            => $errorsLanguageData['meta']['title'],
    'description'     => $errorsLanguageData['meta']['description'],
    'url'             => $current_url,
    'inLanguage'      => APP_LANG === 'en' ? 'en-US' : 'vi-VN',
    'isPartOf'        => [
        '@type' => 'WebSite',
        '@id'   => base_url() . '#website',
        'name'  => option('site_title'),
        'url'   => base_url()
    ],
    'breadcrumb'      => [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => $errorsLanguageData['navigation']['home'],
                'item'     => base_url()
            ],
            [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $errorsLanguageData['meta']['title'],
                'item'     => $current_url
            ]
        ]
    ]
]);

// Render meta tags and schema in header
get_header([
    'meta' => $meta->render(),
    'schema' => $errorSchema->render()
]);
?>

<main class="container mx-auto px-4 py-16">
    <div class="max-w-4xl mx-auto text-center">
        <!-- 404 Number with Animation -->
        <div class="mb-12">
            <div class="relative inline-block">
                <div class="text-[200px] md:text-[300px] font-bold text-slate-100 dark:text-slate-800 leading-none select-none">404</div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 md:w-48 md:h-48 bg-gradient-to-br from-blue-500 via-indigo-600 to-purple-700 rounded-3xl flex items-center justify-center shadow-2xl animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search w-16 h-16 md:w-24 md:h-24 text-white">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Title and Description -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold tracking-tighter sm:text-5xl md:text-6xl bg-clip-text text-transparent bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 pb-2 leading-tight md:leading-[1.4]">
                <?php echo $errorsLanguageData['content']['heading']; ?>
            </h1>
            <p class="text-xl text-slate-600 dark:text-slate-300 mb-8 max-w-2xl mx-auto">
                <?php echo $errorsLanguageData['content']['message']; ?>
            </p>
        </div>

        <!-- Search Box -->
        <div class="mb-16">
            <form action="<?php echo base_url('search'); ?>" method="GET" class="max-w-lg mx-auto flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400 w-5 h-5">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input 
                type="text" 
                name="s" 
                class="w-full pl-14 pr-5 py-5 h-16 text-lg border-2 border-slate-200 rounded-2xl bg-white text-slate-900 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300" 
                placeholder="<?php echo $errorsLanguageData['search']['placeholder']; ?>"
                value="<?php echo htmlspecialchars(S_GET('s') ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
            <button type="submit" class="flex items-center justify-center px-8 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-lg font-medium rounded-2xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 w-full sm:w-auto">
                <?php echo $errorsLanguageData['search']['button']; ?>
            </button>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
            <a class="flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" href="<?php echo base_url(); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                    <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
                <?php echo $errorsLanguageData['actions']['back_home']; ?>
            </a>
            <a class="flex items-center justify-center px-8 py-3 border-2 border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" href="javascript:history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
                <?php echo $errorsLanguageData['actions']['go_back']; ?>
            </a>
        </div>

        <!-- Popular Pages -->
        <div class="mb-16">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-white mb-8">
                <?php echo $errorsLanguageData['popular_pages']['title']; ?>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-0 rounded-2xl p-6 text-center hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 shadow-lg bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 group">
                    <a class="block" href="<?php echo base_url('features'); ?>">
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white group-hover:text-emerald-600 transition-colors duration-300">
                            <?php echo $errorsLanguageData['navigation']['features']; ?>
                        </h3>
                    </a>
                </div>
                
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-0 rounded-2xl p-6 text-center hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 shadow-lg bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 group">
                    <a class="block" href="<?php echo base_url('libraries'); ?>">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white group-hover:text-purple-600 transition-colors duration-300">
                            <?php echo $errorsLanguageData['navigation']['library']; ?>
                        </h3>
                    </a>
                </div>
                
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-0 rounded-2xl p-6 text-center hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 shadow-lg bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 group">
                    <a class="block" href="<?php echo base_url('blog'); ?>">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white group-hover:text-blue-600 transition-colors duration-300">
                            <?php echo $errorsLanguageData['navigation']['blog']; ?>
                        </h3>
                    </a>
                </div>
                
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-0 rounded-2xl p-6 text-center hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 shadow-lg bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 group">
                    <a class="block" href="<?php echo base_url('community'); ?>">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white group-hover:text-orange-600 transition-colors duration-300">
                            <?php echo $errorsLanguageData['navigation']['community']; ?>
                        </h3>
                    </a>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-100 rounded-3xl p-8 shadow-xl">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">
                <?php echo $errorsLanguageData['help']['title']; ?>
            </h2>
            <p class="text-slate-600 mb-8">
                <?php echo $errorsLanguageData['help']['message']; ?>
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                    </div>
                    <a href="mailto:support@cmsfullform.com" class="text-slate-600 hover:text-blue-600 transition-colors font-medium">
                        support@cmsfullform.com
                    </a>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <a href="tel:+84123456789" class="text-slate-600 hover:text-blue-600 transition-colors font-medium">
                        +84 123 456 789
                    </a>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors font-medium">
                        Hà Nội, Việt Nam
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>