  <section class="py-16 md:py-24 bg-white">
      <div class="container mx-auto px-4">
          <div class="text-center mb-16">
              <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                  <?= _e('comparison.title.before') ?>
                  <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                      <?= _e('comparison.title.highlight') ?>
                  </span>
              </h2>
              <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                  <?= _e('comparison.description') ?>
              </p>
          </div>

          <div class="text-card-foreground overflow-hidden shadow-2xl border-0 rounded-xl bg-white">
              <div class="overflow-x-auto">
                   <table class="w-full">
                           <thead class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                               <tr>
                                   <th class="px-6 py-4 text-left text-sm font-semibold text-white"><?php _e('comparison_column_feature') ?></th>
                                   <th class="px-6 py-4 text-center text-sm font-semibold text-white from-blue-600 to-indigo-700"><?php _e('comparison_column_cms') ?></th>
                                   <th class="px-6 py-4 text-center text-sm font-semibold text-white"><?php _e('comparison_column_wp') ?></th>
                                   <th class="px-6 py-4 text-center text-sm font-semibold text-white"><?php _e('comparison_column_joomla') ?></th>
                                   <th class="px-6 py-4 text-center text-sm font-semibold text-white"><?php _e('comparison_column_drupal') ?></th>
                               </tr>
                           </thead>

                           <tbody class="divide-y divide-slate-200">
                               <tr class="hover:bg-slate-50 transition-colors">
                                   <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php _e('feature_speed') ?></td>
                                   <td class="px-6 py-4 text-center bg-blue-50/50">
                                       <div class="flex items-center justify-center space-x-2"><svg
                                               xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                               stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-zap">
                                               <path
                                                   d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z">
                                               </path>
                                           </svg>
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-green-100 text-green-800 border-green-200 font-semibold">
                                               &lt; 1.5 – 2.5s</div><svg xmlns="http://www.w3.org/2000/svg" width="16"
                                               height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-check-big text-green-600">
                                               <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                               <path d="m9 11 3 3L22 4"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-red-100 text-red-800 border-red-200">
                                               2.5 – 5s</div><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                               stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-x text-red-600">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="m15 9-6 6"></path>
                                               <path d="m9 9 6 6"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-red-100 text-red-800 border-red-200">
                                               3 – 6s</div><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                               stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-x text-red-600">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="m15 9-6 6"></path>
                                               <path d="m9 9 6 6"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               2-4s</div><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>
                               </tr>
                       
                               <tr class="hover:bg-slate-50 transition-colors">
                                   <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php _e('feature_security') ?></td>
                                   <td class="px-6 py-4 text-center bg-blue-50/50">
                                       <div class="flex items-center justify-center space-x-2"><svg
                                               xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                               stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-shield">
                                               <path
                                                   d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                               </path>
                                           </svg>
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-green-100 text-green-800 border-green-200 font-semibold">
                                               Native support</div><svg xmlns="http://www.w3.org/2000/svg" width="16"
                                               height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-check-big text-green-600">
                                               <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                               <path d="m9 11 3 3L22 4"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               <?php _e('feature_security_wp') ?>
                                           </div><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               <?php _e('feature_security_joomla') ?>
                                           </div><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-blue-100 text-blue-800 border-blue-200">
                                               <?php _e('feature_security_drupal') ?>
                                           </div><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
                                               <g transform="scale(0.7) translate(6, 4)">
                                                   <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                               </g>
                                           </svg>
                                       </div>
                                   </td>
                               </tr>

                               <tr class="hover:bg-slate-50 transition-colors">
                                   <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php _e('feature_cpu') ?>
                                   </td>
                                   <td class="px-6 py-4 text-center bg-blue-50/50">
                                       <div class="flex items-center justify-center space-x-2"><svg
                                               xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                               stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-cpu">
                                               <rect width="16" height="16" x="4" y="4" rx="2"></rect>
                                               <rect width="6" height="6" x="9" y="9" rx="1"></rect>
                                               <path d="M15 2v2"></path>
                                               <path d="M15 20v2"></path>
                                               <path d="M2 15h2"></path>
                                               <path d="M2 9h2"></path>
                                               <path d="M20 15h2"></path>
                                               <path d="M20 9h2"></path>
                                               <path d="M9 2v2"></path>
                                               <path d="M9 20v2"></path>
                                           </svg>
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-green-100 text-green-800 border-green-200 font-semibold">
                                               <?php _e('feature_cpu_cms') ?>
                                           </div><svg xmlns="http://www.w3.org/2000/svg" width="16"
                                               height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-check-big text-green-600">
                                               <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                               <path d="m9 11 3 3L22 4"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               60-70%</div><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-red-100 text-red-800 border-red-200">
                                               50-60%</div><svg xmlns="http://www.w3.org/2000/svg" width="16"
                                               height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-x text-red-600">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="m15 9-6 6"></path>
                                               <path d="m9 9 6 6"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               65-75%</div><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>
                               </tr>

                               <tr class="hover:bg-slate-50 transition-colors">
                                   <td class="px-6 py-4 text-sm font-medium text-slate-900">Multi-language</td>
                                   <td class="px-6 py-4 text-center bg-blue-50/50">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-green-100 text-green-800 border-green-200 font-semibold">
                                               <?php _e('feature_multilang_cms') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-check-big text-green-600">
                                               <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                               <path d="m9 11 3 3L22 4"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               <?php _e('feature_multilang_wp') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>

                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-blue-100 text-blue-800 border-blue-200">
                                               <?php _e('feature_multilang_joomla') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
                                               <g transform="scale(0.7) translate(6, 4)">
                                                   <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                               </g>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               <?php _e('feature_multilang_drupal') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>

                               </tr>

                                <tr class="hover:bg-slate-50 transition-colors">
                                   <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php _e('feature_poststype') ?></td>
                                   <td class="px-6 py-4 text-center bg-blue-50/50">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-green-100 text-green-800 border-green-200 font-semibold">
                                               <?php _e('feature_poststype_cms') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-check-big text-green-600">
                                               <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                               <path d="m9 11 3 3L22 4"></path>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-blue-100 text-blue-800 border-blue-200">
                                               <?php _e('feature_poststype_wp') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
                                               <g transform="scale(0.7) translate(6, 4)">
                                                   <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                               </g>
                                           </svg>

                                       </div>
                                   </td>

                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-blue-100 text-blue-800 border-blue-200">
                                               <?php _e('feature_poststype_joomla') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
                                               <g transform="scale(0.7) translate(6, 4)">
                                                   <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                               </g>
                                           </svg>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-yellow-100 text-yellow-800 border-yellow-200">
                                               <?php _e('feature_poststype_drupal') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <circle cx="12" cy="12" r="10" stroke="currentColor" />
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                                           </svg>

                                       </div>
                                   </td>

                               </tr>

                               <tr class="hover:bg-slate-50 transition-colors">
                                   <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php _e('feature_ai') ?></td>

                                   <td class="px-6 py-4 text-center bg-blue-50/50">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-green-100 text-green-800 border-green-200 font-semibold">
                                               <?php _e('feature_ai_cms') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-check-big text-green-600">
                                               <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                               <path d="m9 11 3 3L22 4"></path>
                                           </svg>
                                       </div>
                                   </td>

                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-red-100 text-red-800 border-red-200">
                                               <?php _e('feature_ai_wp') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-x text-red-600">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="m15 9-6 6"></path>
                                               <path d="m9 9 6 6"></path>
                                           </svg>
                                       </div>
                                   </td>

                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-gray-100 text-gray-800 border-gray-200">
                                               <?php _e('feature_ai_joomla') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-x text-red-600">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="m15 9-6 6"></path>
                                               <path d="m9 9 6 6"></path>
                                           </svg>
                                       </div>
                                   </td>

                                   <td class="px-6 py-4 text-center">
                                       <div class="flex items-center justify-center space-x-2">
                                           <div
                                               class=" whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-primary/80 bg-red-100 text-red-800 border-red-200">
                                               <?php _e('feature_ai_drupal') ?>
                                           </div>
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                               class="lucide lucide-circle-x text-red-600">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="m15 9-6 6"></path>
                                               <path d="m9 9 6 6"></path>
                                           </svg>
                                       </div>
                                   </td>
                               </tr>

                           </tbody>
                       </table>
              </div>
          </div>

           <div class="mt-8 text-center">
               <p class="text-lg text-slate-600 mb-4">
                   <span class="font-semibold text-blue-600">CMS Full Form</span>
                   <?php _e('outperforms_in_all_criteria') ?>
               </p>
               <div class="flex justify-center space-x-4 text-sm text-slate-500">
                   <div class="flex items-center space-x-1">
                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big text-green-600">
                           <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                           <path d="m9 11 3 3L22 4"></path>
                       </svg>
                       <span><?php _e('label_excellent') ?></span>
                   </div>
                   <div class="flex items-center space-x-1">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"></circle>
                            <g transform="scale(0.7) translate(6, 4)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                            </g>
                        </svg>
                       <span><?php _e('label_good') ?></span>
                   </div>
                   <div class="flex items-center space-x-1">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke="currentColor"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"></path>
                        </svg>
                       <span><?php _e('label_average') ?></span>
                   </div>
                   <div class="flex items-center space-x-1">
                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x text-red-600">
                           <circle cx="12" cy="12" r="10"></circle>
                           <path d="m15 9-6 6"></path>
                           <path d="m9 9 6 6"></path>
                       </svg>
                       <span><?php _e('label_poor') ?></span>
                   </div>
               </div>
           </div>
           
      </div>
  </section>