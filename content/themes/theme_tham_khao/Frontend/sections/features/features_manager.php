 <section class="py-16 md:py-24 bg-slate-50">
     <div class="container mx-auto px-4">
         <div class="text-center mb-16">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <?php _e('content.smart_management.title.before') ?>
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                     <?php _e('content.smart_management.title.highlight') ?>
                 </span>
             </h2>
             <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                 <?php _e('content.smart_management.description') ?>
             </p>

         </div>

         <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">

             <!-- div1 -->
             <div>
                 <div class="rounded-lg text-card-foreground bg-white shadow-xl border-0">
                     <div class="p-8">

                         <div class="flex items-center space-x-4 mb-6">
                             <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl hidden md:flex items-center justify-center">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-layers text-white">
                                     <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"></path>
                                     <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"></path>
                                     <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"></path>
                                 </svg>
                             </div>
                             <div>
                                 <h3 class="text-2xl font-bold text-slate-800"><?php _e('content.multi_posttype.title') ?></h3>
                                 <p class="text-slate-600"><?php _e('content.multi_posttype.subtitle') ?></p>
                             </div>
                         </div>


                         <div class="space-y-4">
                             <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                 <div class="flex items-center space-x-3">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="lucide lucide-file-text text-blue-600">
                                         <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                         <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                         <path d="M10 9H8"></path>
                                         <path d="M16 13H8"></path>
                                         <path d="M16 17H8"></path>
                                     </svg>
                                     <span class="font-medium"><?php _e('content.multi_posttype.items.posts') ?></span>
                                 </div>
                                 <span class="text-sm text-slate-500"><?php _e('content.multi_posttype.items.posts.table') ?></span>
                             </div>

                             <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                 <div class="flex items-center space-x-3">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="lucide lucide-shopping-bag text-green-600">
                                         <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                         <path d="M3 6h18"></path>
                                         <path d="M16 10a4 4 0 0 1-8 0"></path>
                                     </svg>
                                     <span class="font-medium"><?php _e('content.multi_posttype.items.products') ?></span>
                                 </div>
                                 <span class="text-sm text-slate-500"><?php _e('content.multi_posttype.items.products.table') ?></span>
                             </div>

                             <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                 <div class="flex items-center space-x-3">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="lucide lucide-briefcase text-purple-600">
                                         <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                         <rect width="20" height="14" x="2" y="6" rx="2"></rect>
                                     </svg>
                                     <span class="font-medium"><?php _e('content.multi_posttype.items.projects') ?></span>
                                 </div>
                                 <span class="text-sm text-slate-500"><?php _e('content.multi_posttype.items.projects.table') ?></span>
                             </div>

                             <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-2 border-dashed border-blue-300">
                                 <div class="flex items-center space-x-3">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="lucide lucide-plus text-blue-600">
                                         <path d="M5 12h14"></path>
                                         <path d="M12 5v14"></path>
                                     </svg>
                                     <span class="font-medium text-blue-600"><?php _e('content.multi_posttype.items.create') ?></span>
                                 </div>
                                 <span class="text-sm text-blue-500"><?php _e('content.multi_posttype.items.create.table') ?></span>
                             </div>
                         </div>

                         <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                             <div class="flex items-start space-x-3">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-info text-blue-600 mt-0.5">
                                     <circle cx="12" cy="12" r="10"></circle>
                                     <path d="M12 16v-4"></path>
                                     <path d="M12 8h.01"></path>
                                 </svg>
                                 <div>
                                     <p class="text-sm text-blue-800 font-medium"><?php _e('content.multi_posttype.note.title') ?></p>
                                     <p class="text-sm text-blue-700"><?php _e('content.multi_posttype.note.description') ?></p>
                                 </div>
                             </div>
                         </div>

                     </div>
                 </div>
             </div>

             <!-- div2 -->
             <div>
                 <div class="rounded-lg text-card-foreground bg-white shadow-xl border-0">
                     <div class="p-8">
                         <div class="flex items-center space-x-4 mb-6">
                             <div class="w-16 h-16 bg-gradient-to-r from-green-600 to-emerald-700 rounded-xl hidden md:flex items-center justify-center">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-globe text-white">
                                     <circle cx="12" cy="12" r="10"></circle>
                                     <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                                     <path d="M2 12h20"></path>
                                 </svg>
                             </div>
                             <div>
                                 <h3 class="text-2xl font-bold text-slate-800"><?php _e('content.multi_language.title') ?></h3>
                                 <p class="text-slate-600"><?php _e('content.multi_language.subtitle') ?></p>
                             </div>
                         </div>

                         <div class="space-y-4">
                             <div class="grid grid-cols-2 gap-4">
                                 <div class="p-4 bg-slate-50 rounded-lg text-center">
                                     <div class="text-2xl mb-2">🇻🇳</div>
                                     <div class="font-medium"><?php _e('content.multi_language.lang.vi') ?></div>
                                     <div class="text-sm text-slate-500">fast_posts_vi</div>
                                 </div>
                                 <div class="p-4 bg-slate-50 rounded-lg text-center">
                                     <div class="text-2xl mb-2">🇺🇸</div>
                                     <div class="font-medium"><?php _e('content.multi_language.lang.en') ?></div>
                                     <div class="text-sm text-slate-500">fast_posts_en</div>
                                 </div>
                                 <div class="p-4 bg-slate-50 rounded-lg text-center">
                                     <div class="text-2xl mb-2">🇯🇵</div>
                                     <div class="font-medium"><?php _e('content.multi_language.lang.ja') ?></div>
                                     <div class="text-sm text-slate-500">fast_posts_ja</div>
                                 </div>
                                 <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg text-center border-2 border-dashed border-green-300">
                                     <div class="text-2xl mb-2">➕</div>
                                     <div class="font-medium text-green-600"><?php _e('content.multi_language.lang.add') ?></div>
                                     <div class="text-sm text-green-500"><?php _e('content.multi_language.lang.add.table') ?></div>
                                 </div>
                             </div>
                         </div>

                         <div class="mt-6 p-4 bg-green-50 rounded-lg">
                             <div class="flex items-start space-x-3">
                                 <svg xmlns="http://www.w3.org/2000/svg"
                                     width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-sparkles text-green-600 mt-0.5">
                                     <path
                                         d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z">
                                     </path>
                                     <path d="M20 3v4"></path>
                                     <path d="M22 5h-4"></path>
                                     <path d="M4 17v2"></path>
                                     <path d="M5 18H3"></path>
                                 </svg>
                                 <div>
                                     <p class="text-sm text-green-800 font-medium"><?php _e('content.multi_language.note.title') ?></p>
                                     <p class="text-sm text-green-700"><?php _e('content.multi_language.note.description') ?></p>
                                 </div>
                             </div>
                         </div>

                     </div>
                 </div>
             </div>


         </div>


         <div class="rounded-lg text-card-foreground bg-white shadow-xl border-0">
             <div class="p-2 sm:p-8">
                 <div class="text-center mb-8">
                     <div class="w-16 h-16 bg-gradient-to-r from-pink-600 to-rose-700 rounded-xl hidden md:flex items-center justify-center mx-auto mb-4">
                         <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="lucide lucide-pen-line text-white">
                             <path d="M12 20h9"></path>
                             <path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"></path>
                         </svg>
                     </div>
                     <h3 class="text-2xl font-bold text-slate-800 mb-2"><?php _e('content.editor.title') ?></h3>
                     <p class="text-slate-600"><?php _e('content.editor.description') ?></p>
                 </div>

                 <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center max-w-6xl mx-auto">
                     <!-- Content Section - 5 columns -->
                     <div class="order-2 lg:order-1 lg:col-span-5 space-y-8">
                         <!-- Title -->
                         <div>
                             <h4 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-8 leading-tight">
                                 <?php _e('content.editor.features.title') ?>
                             </h4>

                             <!-- Features List -->
                             <div class="space-y-2 md:space-y-5">
                                 <!-- Rich Text Feature -->
                                 <div class="flex items-start space-x-5 group hover:bg-slate-50/80 p-4 rounded-xl transition-all duration-300 hover:translate-x-1">
                                     <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-lg">
                                         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                             <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                             <polyline points="14,2 14,8 20,8"></polyline>
                                             <line x1="16" y1="13" x2="8" y2="13"></line>
                                             <line x1="16" y1="17" x2="8" y2="17"></line>
                                             <polyline points="10,9 9,9 8,9"></polyline>
                                         </svg>
                                     </div>
                                     <span class="text-slate-700 leading-relaxed font-medium text-base"><?php _e('content.editor.features.rich_text') ?></span>
                                 </div>

                                 <!-- Media Drag Feature -->
                                 <div class="flex items-start space-x-5 group hover:bg-slate-50/80 p-4 rounded-xl transition-all duration-300 hover:translate-x-1">
                                     <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-lg">
                                         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                             <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                             <circle cx="9" cy="9" r="2"></circle>
                                             <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                                         </svg>
                                     </div>
                                     <span class="text-slate-700 leading-relaxed font-medium text-base"><?php _e('content.editor.features.media_drag') ?></span>
                                 </div>

                                 <!-- Table List Quote Feature -->
                                 <div class="flex items-start space-x-5 group hover:bg-slate-50/80 p-4 rounded-xl transition-all duration-300 hover:translate-x-1">
                                     <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-lg">
                                         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                             <path d="M12 2v20"></path>
                                             <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                         </svg>
                                     </div>
                                     <span class="text-slate-700 leading-relaxed font-medium text-base"><?php _e('content.editor.features.table_list_quote') ?></span>
                                 </div>

                                 <!-- Code Block Feature -->
                                 <div class="flex items-start space-x-5 group hover:bg-slate-50/80 p-4 rounded-xl transition-all duration-300 hover:translate-x-1">
                                     <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-lg">
                                         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                             <polyline points="16 18 22 12 16 6"></polyline>
                                             <polyline points="8 6 2 12 8 18"></polyline>
                                         </svg>
                                     </div>
                                     <span class="text-slate-700 leading-relaxed font-medium text-base"><?php _e('content.editor.features.code_block') ?></span>
                                 </div>

                                 <!-- AI Writer Feature -->
                                 <div class="flex items-start space-x-5 group hover:bg-slate-50/80 p-4 rounded-xl transition-all duration-300 hover:translate-x-1">
                                     <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-lg">
                                         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                             <path d="M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3m3.343-5.657-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386z"></path>
                                         </svg>
                                     </div>
                                     <span class="text-slate-700 leading-relaxed font-medium text-base"><?php _e('content.editor.features.ai_writer') ?></span>
                                 </div>
                             </div>
                         </div>

                         <!-- CTA Button -->
                         <div class="pt-6">
                             <a href="<?= base_url('download') ?>" class="inline-block w-full sm:w-auto">
                                 <button aria-label="<?php _e('content.editor.button') ?>" class="group inline-flex items-center justify-center gap-2
                                w-full sm:w-auto
                                bg-gradient-to-r from-pink-600 via-pink-600 to-rose-700
                                hover:from-pink-700 hover:to-rose-800
                                text-white font-bold
                                px-5 py-3 sm:px-6 sm:py-3 md:px-8 md:py-4
                                rounded-xl sm:rounded-2xl
                                shadow-lg hover:shadow-xl
                                transform hover:scale-105
                                transition-all duration-300
                                text-base sm:text-lg
                                ">
                                     <span><?php _e('content.editor.button') ?></span>
                                     <svg
                                         width="18" height="18"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="2.5"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="group-hover:translate-x-1 transition-transform duration-300">
                                         <path d="M5 12h14"></path>
                                         <path d="m12 5 7 7-7 7"></path>
                                     </svg>
                                 </button>
                             </a>
                         </div>

                     </div>

                     <!-- Image Section - 7 columns -->
                     <div class="order-1 lg:order-2 lg:col-span-7 relative">
                         <div class="relative group">
                             <!-- Main Image -->
                             <div class="relative overflow-hidden rounded-3xl shadow-2xl group-hover:shadow-3xl transition-all duration-500">
                                 <?= _img(
                                        theme_assets('images/editor.webp'),
                                        __('editor_image_caption'),
                                        true,
                                        'w-auto h-auto object-cover transform transition-transform duration-700'
                                    ) ?>

                                 <!-- Overlay gradient -->
                                 <div class="absolute inset-0 bg-gradient-to-t from-black/15 via-transparent to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                             </div>

                             <!-- Floating badge -->
                             <div class="absolute -top-4 -right-4 bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-2.5 rounded-2xl text-sm font-bold shadow-xl transform rotate-2 hover:rotate-0 transition-transform duration-300">
                                 <div class="flex items-center gap-2">
                                     <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                         <path d="M8 1C8 1 12 2 12 6C12 8 11 9 10 10L11 11L10 12L9 11C8.5 11.5 7.5 11.5 7 11L6 12L5 11L6 10C5 9 4 8 4 6C4 2 8 1 8 1Z" fill="white" />
                                     </svg>
                                     <span>Editor</span>
                                 </div>
                             </div>

                             <!-- Background decoration -->
                             <div class="absolute -z-10 top-8 right-8 w-32 h-32 bg-gradient-to-br from-pink-100 to-rose-100 rounded-full blur-3xl opacity-50"></div>
                             <div class="absolute -z-10 bottom-8 left-8 w-24 h-24 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full blur-2xl opacity-30"></div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </section>
