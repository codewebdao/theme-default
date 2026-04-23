 <section class="py-16 md:py-24 bg-white">
     <div class="container mx-auto px-4">
         <div class="text-center mb-16">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <?php _e('product_section.title') ?>
             </h2>
             <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                 <?php _e('product_section.description') ?>
             </p>
         </div>

         <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-8 mb-16">
             <div
                 class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 p-6 shadow-xl hover:shadow-2xl border border-blue-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer">
                 <div class="p-0">
                     <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="lucide lucide-palette text-white">
                             <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                             <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                             <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                             <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                             <path
                                 d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z">
                             </path>
                         </svg>
                     </div>
                     <div class="flex items-center justify-between mb-2">
                         <h3 class="text-xl font-bold text-slate-800"><?php _e('catalog.themes.title') ?></h3>
                         <span class="text-2xl font-bold text-blue-600"><?php _e('catalog.themes.count') ?></span>
                     </div>
                     <p class="text-slate-600 mb-4"><?php _e('catalog.themes.desc') ?></p>
                     <div class="space-y-2">
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.themes.feature_1') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.themes.feature_2') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.themes.feature_3') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.themes.feature_4') ?>
                         </div>
                     </div>
                 </div>
             </div>


             <div
                 class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-green-50 to-emerald-100 p-6 shadow-xl hover:shadow-2xl border border-green-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer">
                 <div class="p-0">
                     <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="lucide lucide-puzzle text-white">
                             <path
                                 d="M19.439 7.85c-.049.322.059.648.289.878l1.568 1.568c.47.47.706 1.087.706 1.704s-.235 1.233-.706 1.704l-1.611 1.611a.98.98 0 0 1-.837.276c-.47-.07-.802-.48-.968-.925a2.501 2.501 0 1 0-3.214 3.214c.446.166.855.497.925.968a.979.979 0 0 1-.276.837l-1.61 1.61a2.404 2.404 0 0 1-1.705.707 2.402 2.402 0 0 1-1.704-.706l-1.568-1.568a1.026 1.026 0 0 0-.877-.29c-.493.074-.84.504-1.02.968a2.5 2.5 0 1 1-3.237-3.237c.464-.18.894-.527.967-1.02a1.026 1.026 0 0 0-.289-.877l-1.568-1.568A2.402 2.402 0 0 1 1.998 12c0-.617.236-1.234.706-1.704L4.23 8.77c.24-.24.581-.353.917-.303.515.077.877.528 1.073 1.01a2.5 2.5 0 1 0 3.259-3.259c-.482-.196-.933-.558-1.01-1.073-.05-.336.062-.676.303-.917l1.525-1.525A2.402 2.402 0 0 1 12 1.998c.617 0 1.234.236 1.704.706l1.568 1.568c.23.23.556.338.877.29.493-.074.84-.504 1.02-.968a2.5 2.5 0 1 1 3.237 3.237c-.464.18-.894.527-.967 1.02Z" />
                         </svg>
                     </div>
                     <div class="flex items-center justify-between mb-2">
                         <h3 class="text-xl font-bold text-slate-800"><?php _e('catalog.plugins.title') ?></h3>
                         <span class="text-2xl font-bold text-blue-600"><?php _e('catalog.plugins.count') ?></span>
                     </div>
                     <p class="text-slate-600 mb-4"><?php _e('catalog.plugins.desc') ?></p>
                     <div class="space-y-2">
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.plugins.feature_1') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.plugins.feature_2') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.plugins.feature_3') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?php _e('catalog.plugins.feature_4') ?>
                         </div>
                     </div>
                 </div>
             </div>


             <div
                 class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-purple-50 to-violet-100 p-6 shadow-xl hover:shadow-2xl border border-purple-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer">
                 <div class="p-0">
                     <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="lucide lucide-sparkles text-white">
                             <path
                                 d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z">
                             </path>
                             <path d="M20 3v4"></path>
                             <path d="M22 5h-4"></path>
                             <path d="M4 17v2"></path>
                             <path d="M5 18H3"></path>
                         </svg>
                     </div>
                     <div class="flex items-center justify-between mb-2">
                         <h3 class="text-xl font-bold text-slate-800"><?= _e('catalog.ai.title') ?></h3>
                         <span class="text-2xl font-bold text-blue-600"><?= _e('catalog.ai.count') ?></span>
                     </div>
                     <p class="text-slate-600 mb-4"><?= _e('catalog.ai.desc') ?></p>
                     <div class="space-y-2">
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.ai.feature_1') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.ai.feature_2') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.ai.feature_3') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.ai.feature_4') ?>
                         </div>
                     </div>
                 </div>
             </div>

             <div
                 class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-red-50 to-pink-100 p-6 shadow-xl hover:shadow-2xl border border-red-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer">
                 <div class="p-0">
                     <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="lucide lucide-shield text-white">
                             <path
                                 d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                             </path>
                         </svg>
                     </div>
                     <div class="flex items-center justify-between mb-2">
                         <h3 class="text-xl font-bold text-slate-800"><?= _e('catalog.security.title') ?></h3>
                         <span class="text-2xl font-bold text-blue-600"><?= _e('catalog.security.count') ?></span>
                     </div>
                     <p class="text-slate-600 mb-4"><?= _e('catalog.security.desc') ?></p>
                     <div class="space-y-2">
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.security.feature_1') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.security.feature_2') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.security.feature_3') ?>
                         </div>
                         <div class="flex items-center text-sm text-slate-600">
                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div><?= _e('catalog.security.feature_4') ?>
                         </div>
                     </div>
                 </div>
             </div>


         </div>
         <div class="bg-gradient-to-br from-slate-50 to-blue-50 rounded-2xl p-8 border border-slate-200">

             <h3 class="text-2xl font-bold text-slate-800 text-center mb-8">
                 <?= _e('catalog.why_choose.title') ?>
             </h3>


             <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                 <div class="text-center">
                     <div class="flex justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star text-yellow-500">
                             <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" />
                         </svg>
                     </div>
                     <h4 class="font-semibold text-slate-800 mb-2"><?php _e('catalog.why_choose.reason_1.title') ?></h4>
                     <p class="text-sm text-slate-600"><?php _e('catalog.why_choose.reason_1.desc') ?></p>
                 </div>

                 <div class="text-center">
                     <div class="flex justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download text-blue-500">
                             <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                             <polyline points="7 10 12 15 17 10" />
                             <line x1="12" x2="12" y1="15" y2="3" />
                         </svg>
                     </div>
                     <h4 class="font-semibold text-slate-800 mb-2"><?php _e('catalog.why_choose.reason_2.title') ?></h4>
                     <p class="text-sm text-slate-600"><?php _e('catalog.why_choose.reason_2.desc') ?></p>
                 </div>

                 <div class="text-center">
                     <div class="flex justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users text-green-500">
                             <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                             <circle cx="9" cy="7" r="4" />
                             <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                             <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                         </svg>
                     </div>
                     <h4 class="font-semibold text-slate-800 mb-2"><?php _e('catalog.why_choose.reason_3.title') ?></h4>
                     <p class="text-sm text-slate-600"><?php _e('catalog.why_choose.reason_3.desc') ?></p>
                 </div>

                 <div class="text-center">
                     <div class="flex justify-center mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap text-purple-500">
                             <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
                         </svg>
                     </div>
                     <h4 class="font-semibold text-slate-800 mb-2"><?php _e('catalog.why_choose.reason_4.title') ?></h4>
                     <p class="text-sm text-slate-600"><?php _e('catalog.why_choose.reason_4.desc') ?></p>
                 </div>

             </div>
         </div>
     </div>
 </section>
