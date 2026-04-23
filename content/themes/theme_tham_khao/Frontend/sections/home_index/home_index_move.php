 <!-- Easy migration from old CMS -->
 <section id="migration" class="py-16 md:py-24 bg-gradient-to-br from-blue-50 to-indigo-100">
     <div class="container mx-auto px-4">
         <div class="text-center mb-12">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <?php _e('landing.migration.title_part1') ?>
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                     <?php _e('landing.migration.title_part2') ?>
                 </span>
                 <?php _e('landing.migration.title_part3') ?>
             </h2>
             <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                 <?php _e('landing.migration.subtitle') ?>
             </p>
         </div>

         <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center mb-16">
             <div>
                 <h3 class="text-2xl font-bold text-slate-800 mb-6"><?php _e('landing.migration.steps_title') ?></h3>
                 <div class="space-y-6">
                     <div
                         class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl border border-blue-200">
                         <div class="p-6">
                             <div class="flex items-start space-x-4">
                                 <div class="flex-shrink-0">
                                     <div
                                         class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                         1
                                     </div>
                                 </div>
                                 <div class="flex-grow">
                                     <!-- Added flex-wrap here -->
                                     <div class="flex items-center flex-wrap space-x-3 mb-2">
                                         <div class="hidden md:block p-2 rounded-lg bg-blue-600">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="24"
                                                 height="24"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-refresh-cw text-white">
                                                 <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                 <path d="M21 3v5h-5"></path>
                                                 <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                 <path d="M8 16H3v5"></path>
                                             </svg>
                                         </div>
                                         <h4 class="text-lg font-semibold text-slate-800"><?php _e('landing.migration.step1_title') ?></h4>
                                         <!-- Removed whitespace-nowrap -->
                                         <div
                                             class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs border-slate-300">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="12"
                                                 height="12"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-clock mr-1">
                                                 <circle cx="12" cy="12" r="10"></circle>
                                                 <polyline points="12 6 12 12 16 14"></polyline>
                                             </svg>
                                             5 <?php _e('minutes') ?>
                                         </div>
                                     </div>
                                     <p class="text-slate-600"><?php _e('landing.migration.step1_desc') ?></p>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <div
                         class="bg-card text-card-foreground bg-gradient-to-br from-green-50 to-emerald-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl border border-green-200">
                         <div class="p-6">
                             <div class="flex items-start space-x-4">
                                 <div class="flex-shrink-0">
                                     <div
                                         class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                         2
                                     </div>
                                 </div>
                                 <div class="flex-grow">
                                     <!-- Added flex-wrap here -->
                                     <div class="flex items-center flex-wrap space-x-3 mb-2">
                                         <div class="hidden md:block p-2 rounded-lg bg-green-600">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="24"
                                                 height="24"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-shield text-white">
                                                 <path
                                                     d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                                             </svg>
                                         </div>
                                         <h4 class="text-lg font-semibold text-slate-800"><?php _e('landing.migration.step2_title') ?></h4>
                                         <!-- Removed whitespace-nowrap -->
                                         <div
                                             class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs border-slate-300">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="12"
                                                 height="12"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-clock mr-1">
                                                 <circle cx="12" cy="12" r="10"></circle>
                                                 <polyline points="12 6 12 12 16 14"></polyline>
                                             </svg>
                                             10 <?php _e('minutes') ?>
                                         </div>
                                     </div>
                                     <p class="text-slate-600"><?php _e('landing.migration.step2_desc') ?></p>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <div
                         class="bg-card text-card-foreground bg-gradient-to-br from-purple-50 to-violet-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl border border-purple-200">
                         <div class="p-6">
                             <div class="flex items-start space-x-4">
                                 <div class="flex-shrink-0">
                                     <div
                                         class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                         3
                                     </div>
                                 </div>
                                 <div class="flex-grow">
                                     <!-- Added flex-wrap here -->
                                     <div class="flex items-center flex-wrap space-x-3 mb-2">
                                         <div class="hidden md:block p-2 rounded-lg bg-purple-600">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="24"
                                                 height="24"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-download text-white">
                                                 <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                 <polyline points="7 10 12 15 17 10"></polyline>
                                                 <line x1="12" x2="12" y1="15" y2="3"></line>
                                             </svg>
                                         </div>
                                         <h4 class="text-lg font-semibold text-slate-800"><?php _e('landing.migration.step3_title') ?></h4>
                                         <!-- Removed whitespace-nowrap -->
                                         <div
                                             class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs border-slate-300">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="12"
                                                 height="12"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-clock mr-1">
                                                 <circle cx="12" cy="12" r="10"></circle>
                                                 <polyline points="12 6 12 12 16 14"></polyline>
                                             </svg>
                                             15-30 <?php _e('minutes') ?>
                                         </div>
                                     </div>
                                     <p class="text-slate-600"><?php _e('landing.migration.step3_desc') ?></p>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <div
                         class="bg-card text-card-foreground bg-gradient-to-br from-emerald-50 to-teal-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl border border-emerald-200">
                         <div class="p-6">
                             <div class="flex items-start space-x-4">
                                 <div class="flex-shrink-0">
                                     <div
                                         class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                         4
                                     </div>
                                 </div>
                                 <div class="flex-grow">
                                     <!-- Added flex-wrap here -->
                                     <div class="flex items-center flex-wrap space-x-3 mb-2">
                                         <div class="hidden md:block p-2 rounded-lg bg-emerald-600">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="24"
                                                 height="24"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-circle-check-big text-white">
                                                 <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                                 <path d="m9 11 3 3L22 4"></path>
                                             </svg>
                                         </div>
                                         <h4 class="text-lg font-semibold text-slate-800"><?php _e('landing.migration.step4_title') ?></h4>
                                         <!-- Removed whitespace-nowrap -->
                                         <div
                                             class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs border-slate-300">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 width="12"
                                                 height="12"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 class="lucide lucide-clock mr-1">
                                                 <circle cx="12" cy="12" r="10"></circle>
                                                 <polyline points="12 6 12 12 16 14"></polyline>
                                             </svg>
                                             10 <?php _e('minutes') ?>
                                         </div>
                                     </div>
                                     <p class="text-slate-600"><?php _e('landing.migration.step4_desc') ?></p>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             <div>
                 <div class="rounded-lg text-card-foreground bg-white shadow-2xl border-0">
                     <div
                         class="flex flex-col space-y-1.5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6">
                         <div class="font-semibold tracking-tight text-xl text-center"><?php _e('landing.migration.supported_platforms') ?></div>
                     </div>
                     <div class="p-6">
                         <div class="grid grid-cols-2 gap-4">
                             <div
                                 class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                 <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                     <?= _img(
                                            theme_assets('images/WordPress.com-Logo.wine.png'),
                                            'WordPress',
                                            true,
                                            'w-6 h-6 sm:w-8 sm:h-8 object-contain flex-shrink-0'
                                        ) ?>
                                     <span class="font-medium text-slate-800 text-sm sm:text-base truncate">WordPress</span>
                                 </div>
                                 <div
                                     class="inline-flex items-center rounded-full border px-2 py-0.5 sm:px-2.5 sm:py-0.5 text-xs font-semibold transition-colors bg-green-100 text-green-800 border-green-200 ml-2 flex-shrink-0">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-3 w-3 sm:h-4 sm:w-4 mr-1 stroke-current"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke-width="2">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                     </svg>
                                 </div>
                             </div>
                             <!-- Joomla Card -->
                             <div
                                 class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                 <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                     <?= _img(
                                            theme_assets('images/joomla-svgrepo-com.png'),
                                            'Joomla',
                                            true,
                                            'w-6 h-6 sm:w-8 sm:h-8 object-contain flex-shrink-0'
                                        ) ?>
                                     <span class="font-medium text-slate-800 text-sm sm:text-base truncate">Joomla</span>
                                 </div>
                                 <div
                                     class="inline-flex items-center rounded-full border px-2 py-0.5 sm:px-2.5 sm:py-0.5 text-xs font-semibold transition-colors bg-red-100 text-red-800 border-red-200 ml-2 flex-shrink-0">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-3 w-3 sm:h-4 sm:w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                     </svg>
                                 </div>
                             </div>

                             <!-- Drupal Card -->
                             <div
                                 class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                 <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                     <?= _img(
                                            theme_assets('images/drupal-4.png'),
                                            'Drupal',
                                            true,
                                            'w-6 h-6 sm:w-8 sm:h-8 object-contain flex-shrink-0'
                                        ) ?>
                                     <span class="font-medium text-slate-800 text-sm sm:text-base truncate">Drupal</span>
                                 </div>
                                 <div
                                     class="inline-flex items-center rounded-full border px-2 py-0.5 sm:px-2.5 sm:py-0.5 text-xs font-semibold transition-colors bg-red-100 text-red-800 border-red-200 ml-2 flex-shrink-0">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-3 w-3 sm:h-4 sm:w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                     </svg>
                                 </div>
                             </div>

                             <!-- Magento Card -->
                             <div
                                 class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                 <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                     <?= _img(
                                            theme_assets('images/magento.png'),
                                            'Magento',
                                            true,
                                            'w-6 h-6 sm:w-8 sm:h-8 object-contain flex-shrink-0'
                                        ) ?>
                                     <span class="font-medium text-slate-800 text-sm sm:text-base truncate">Magento</span>
                                 </div>
                                 <div
                                     class="inline-flex items-center rounded-full border px-2 py-0.5 sm:px-2.5 sm:py-0.5 text-xs font-semibold transition-colors bg-red-100 text-red-800 border-red-200 ml-2 flex-shrink-0">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-3 w-3 sm:h-4 sm:w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                     </svg>
                                 </div>
                             </div>

                             <!-- Shopify Card -->
                             <div
                                 class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                 <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                     <?= _img(
                                            theme_assets('images/Shopify.png'),
                                            'Shopify',
                                            true,
                                            'w-6 h-6 sm:w-8 sm:h-8 object-contain flex-shrink-0'
                                        ) ?>
                                     <span class="font-medium text-slate-800 text-sm sm:text-base truncate">Shopify</span>
                                 </div>
                                 <div
                                     class="inline-flex items-center rounded-full border px-2 py-0.5 sm:px-2.5 sm:py-0.5 text-xs font-semibold transition-colors bg-red-100 text-red-800 border-red-200 ml-2 flex-shrink-0">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-3 w-3 sm:h-4 sm:w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                     </svg>
                                 </div>
                             </div>

                             <!-- Wix Card -->
                             <div
                                 class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                 <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                     <?= _img(
                                            theme_assets('images/380_Wix_logo-512.png'),
                                            'Wix',
                                            true,
                                            'w-6 h-6 sm:w-8 sm:h-8 object-contain flex-shrink-0'
                                        ) ?>
                                     <span class="font-medium text-slate-800 text-sm sm:text-base truncate">Wix</span>
                                 </div>
                                 <div
                                     class="inline-flex items-center rounded-full border px-2 py-0.5 sm:px-2.5 sm:py-0.5 text-xs font-semibold transition-colors bg-red-100 text-red-800 border-red-200 ml-2 flex-shrink-0">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-3 w-3 sm:h-4 sm:w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                     </svg>
                                 </div>
                             </div>
                         </div>
                         <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                             <div class="flex items-center space-x-2 text-blue-700">
                                 <svg
                                     xmlns="http://www.w3.org/2000/svg"
                                     width="20"
                                     height="20"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="lucide lucide-circle-check-big">
                                     <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                     <path d="m9 11 3 3L22 4"></path>
                                 </svg>
                                 <span class="font-semibold"><?php _e('landing.migration.guarantee_heading') ?></span>
                             </div>
                             <p class="text-sm text-blue-600 mt-2"><?php _e('landing.migration.guarantee_desc') ?></p>
                         </div>
                     </div>
                 </div>
             </div>
         </div>



         <div class="text-center">
             <div class="bg-white rounded-xl p-8 shadow-lg max-w-2xl mx-auto">
                 <h3 class="text-2xl font-bold text-slate-800 mb-4"><?php _e('Start Migrating Today') ?></h3>
                 <p class="text-slate-600 mb-6"><?php _e('Over 5,000 websites successfully migrated with a 99.8% success rate.') ?></p>
                 <div class="flex flex-col md:flex-row justify-center md:space-x-4 gap-2">
                     <a href="<?= base_url('download') ?>">
                        <button aria-label="<?php _e('Start Free Migration') ?>" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-primary/90 h-11 rounded-md px-8 bg-gradient-to-r from-blue-600 to-indigo-700 text-white"> <?php _e('Start Free Migration') ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-2">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                     <button aria-label="<?php _e('landing.migration.cta_button_consult') ?>"  class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 border-blue-600 text-blue-600 hover:bg-blue-50">
                         <?php _e('landing.migration.cta_button_consult') ?>
                     </button>
                 </div>
             </div>
         </div>


     </div>
 </section>
