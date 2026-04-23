 <!-- Flexible Multi Posttype -->
 <section id="multi-posttype" class="py-16 md:py-24 bg-slate-100">
     <div class="container mx-auto px-4">
         <div class="text-center mb-12">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">Multi Posttype</span>
                 <?php _e('Support') ?>
             </h2>
             <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                 <?php _e('Create and manage unlimited content types with dedicated data structures tailored to any website needs.') ?>
             </p>
         </div>
         <div class="grid xl:grid-cols-2 gap-12 items-center mb-12">

             <div>
                 <div class="space-y-6">
                     <div class="flex items-start space-x-4">
                         <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-file-type">
                                 <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                 <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                 <path d="M9 13v-1h6v1"></path>
                                 <path d="M12 12v6"></path>
                                 <path d="M11 18h2"></path>
                             </svg>
                         </div>
                         <div>
                             <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php _e('Unlimited Customization') ?></h3>
                             <p class="text-slate-600"><?php _e('Create any content type you need: Posts, Products, Projects, Events, Services, Team Members, Reviews, etc., with custom fields.') ?></p>
                         </div>
                     </div>

                     <div class="flex items-start space-x-4">
                         <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-layers">
                                 <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"></path>
                                 <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"></path>
                                 <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"></path>
                             </svg>
                         </div>
                         <div>
                             <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php _e('Multi-Table Post Type') ?></h3>
                             <p class="text-slate-600"><?php _e('Each Posttype is stored in a <strong class="text-blue-600">Separate SQL Table</strong>, allowing for fast query performance and scalability.') ?></p>
                         </div>
                     </div>

                     <div class="flex items-start space-x-4">
                         <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-settings">
                                 <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                 <circle cx="12" cy="12" r="3"></circle>
                             </svg>
                         </div>
                         <div>
                             <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php _e('Field Management') ?></h3>
                             <p class="text-slate-600"><?php _e('Add, edit, or delete fields with various types: text, number, date, image, file, link, map, and more.') ?></p>
                         </div>
                     </div>
                 </div>
             </div>

             <div>
                 <div class="rounded-lg bg-card text-card-foreground overflow-hidden border-0">
                     <div class="p-0">
                         <?= _img(
                                theme_assets('images/content.webp'),
                                'UI Multi Posttype',
                                true,
                                'w-full drop-shadow-lg h-auto object-cover'
                            ) ?>

                     </div>
                 </div>
             </div>
         </div>

         <!-- Data table -->
         <div class="bg-slate-50 rounded-xl p-6 md:p-8 shadow-lg">
             <div class="flex items-center space-x-3 mb-6">
                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database text-blue-600">
                     <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                     <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                     <path d="M3 12A9 3 0 0 0 21 12"></path>
                 </svg>
                 <h3 class="text-xl font-semibold text-slate-800"><?php _e('posttype_table_heading') ?></h3>
             </div>
             <div class="overflow-x-auto">
                 <table class="min-w-full bg-white rounded-lg overflow-hidden">
                     <thead class="bg-slate-100">
                         <tr>
                             <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"><?php _e('posttype_table_col_posttype') ?></th>
                             <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"><?php _e('posttype_table_col_table') ?></th>
                             <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"><?php _e('posttype_table_col_desc') ?></th>
                         </tr>
                     </thead>
                     <tbody class="divide-y divide-slate-200">
                         <tr>
                             <td class="p-4 text-sm whitespace-nowrap font-medium text-slate-900"><?php _e('posttype_table_post') ?></td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600">fast_posts_en<br />fast_posts_jp</td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600"><?php _e('posttype_table_post_desc') ?></td>
                         </tr>
                         <tr>
                             <td class="p-4 text-sm whitespace-nowrap font-medium text-slate-900"><?php _e('posttype_table_product') ?></td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600">fast_products_en<br/>fast_products_jp</td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600"><?php _e('posttype_table_product_desc') ?></td>
                         </tr>
                         <tr>
                             <td class="p-4 text-sm whitespace-nowrap font-medium text-slate-900"><?php _e('posttype_table_movies') ?></td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600">fast_movies</td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600"><?php _e('posttype_table_movies_desc') ?></td>
                         </tr>
                         <tr>
                             <td class="p-4 text-sm whitespace-nowrap font-medium text-slate-900"><?php _e('posttype_table_custom') ?></td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600">fast_custom_name</td>
                             <td class="p-4 text-sm whitespace-nowrap text-slate-600"><?php _e('posttype_table_custom_desc') ?></td>
                         </tr>
                     </tbody>
                 </table>
             </div>
             <div class="mt-6 text-sm text-slate-500 flex items-center">
                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden md:block lucide lucide-table mr-2 text-slate-400">
                     <path d="M12 3v18"></path>
                     <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                     <path d="M3 9h18"></path>
                     <path d="M3 15h18"></path>
                 </svg>
                 <span><?php _e('posttype_note') ?></span>
             </div>
         </div>

         <div class="mt-10 text-center">
             <a href="<?= base_url('download') ?>">
                 <button aria-label="<?php _e('Create New Content Type') ?>" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2  h-10 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white">
                     <svg class="lucide lucide-plus mr-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round">
                         <path d="M5 12h14"></path>
                         <path d="M12 5v14"></path>
                     </svg>
                     <?php _e('Create New Content Type') ?>
                 </button>
             </a>
         </div>
     </div>

 </section>
