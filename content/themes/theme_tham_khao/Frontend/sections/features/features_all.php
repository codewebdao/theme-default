  <section class="py-16 md:py-24 bg-white">
      <div class="container mx-auto px-4">
          <div class="text-center mb-16">
              <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                  <?= _e('feature.overview.title') ?> <span
                      class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600"><?= _e('feature.overview.subtitle.1') ?></span>
              </h2>
              <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                  <?= _e('feature.overview.subtitle') ?>
              </p>

          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

              <!-- Multi Posttype -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 p-6 shadow-lg hover:shadow-xl border border-blue-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers text-white">
                              <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z">
                              </path>
                              <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"></path>
                              <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.multi_posttype.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.multi_posttype.description') ?></p>
                  </div>
              </div>

              <!-- Multi Languages -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-green-50 to-emerald-100 p-6 shadow-lg hover:shadow-xl border border-green-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe text-white">
                              <circle cx="12" cy="12" r="10"></circle>
                              <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                              <path d="M2 12h20"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.multi_languages.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.multi_languages.description') ?></p>
                  </div>
              </div>

              <!-- SEO Meta -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-purple-50 to-violet-100 p-6 shadow-lg hover:shadow-xl border border-purple-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search text-white">
                              <circle cx="11" cy="11" r="8"></circle>
                              <path d="m21 21-4.3-4.3"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.seo_meta.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.seo_meta.description') ?></p>
                  </div>
              </div>

              <!-- Rich Schema -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-orange-50 to-amber-100 p-6 shadow-lg hover:shadow-xl border border-orange-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code text-white">
                              <polyline points="16 18 22 12 16 6"></polyline>
                              <polyline points="8 6 2 12 8 18"></polyline>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.rich_schema.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.rich_schema.description') ?></p>
                  </div>
              </div>


              <!-- Editor -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-pink-50 to-rose-100 p-6 shadow-lg hover:shadow-xl border border-pink-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pen-line text-white">
                              <path d="M12 20h9"></path>
                              <path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z">
                              </path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.editor.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.editor.description') ?></p>
                  </div>
              </div>

              <!-- Files Manager -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-teal-50 to-cyan-100 p-6 shadow-lg hover:shadow-xl border border-teal-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-teal-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder text-white">
                              <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z">
                              </path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.files_manager.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.files_manager.description') ?></p>
                  </div>
              </div>

              <!-- Super Cache -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-yellow-50 to-amber-100 p-6 shadow-lg hover:shadow-xl border border-yellow-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap text-white">
                              <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z">
                              </path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.super_cache.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.super_cache.description') ?></p>
                  </div>
              </div>

              <!-- AI Automation -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-indigo-50 to-blue-100 p-6 shadow-lg hover:shadow-xl border border-indigo-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles text-white">
                              <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z">
                              </path>
                              <path d="M20 3v4"></path>
                              <path d="M22 5h-4"></path>
                              <path d="M4 17v2"></path>
                              <path d="M5 18H3"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.ai_automation.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.ai_automation.description') ?></p>
                  </div>
              </div>

              <!-- Security -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-red-50 to-pink-100 p-6 shadow-lg hover:shadow-xl border border-red-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check text-white">
                              <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                              </path>
                              <path d="m9 12 2 2 4-4"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.security.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.security.description') ?></p>
                  </div>
              </div>

              <!-- Marketplace -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-slate-50 to-gray-100 p-6 shadow-lg hover:shadow-xl border border-slate-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-slate-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-puzzle text-white">
                              <path d="M19.439 7.85c-.049.322.059.648.289.878l1.568 1.568c.47.47.706 1.087.706 1.704s-.235 1.233-.706 1.704l-1.611 1.611a.98.98 0 0 1-.837.276c-.47-.07-.802-.48-.968-.925a2.501 2.501 0 1 0-3.214 3.214c.446.166.855.497.925.968a.979.979 0 0 1-.276.837l-1.61 1.61a2.404 2.404 0 0 1-1.705.707 2.402 2.402 0 0 1-1.704-.706l-1.568-1.568a1.026 1.026 0 0 0-.877-.29c-.493.074-.84.504-1.02.968a2.5 2.5 0 1 1-3.237-3.237c.464-.18.894-.527.967-1.02a1.026 1.026 0 0 0-.289-.877l-1.568-1.568A2.402 2.402 0 0 1 1.998 12c0-.617.236-1.234.706-1.704L4.23 8.77c.24-.24.581-.353.917-.303.515.077.877.528 1.073 1.01a2.5 2.5 0 1 0 3.259-3.259c-.482-.196-.933-.558-1.01-1.073-.05-.336.062-.676.303-.917l1.525-1.525A2.402 2.402 0 0 1 12 1.998c.617 0 1.234.236 1.704.706l1.568 1.568c.23.23.556.338.877.29.493-.074.84-.504 1.02-.968a2.5 2.5 0 1 1 3.237 3.237c-.464.18-.894.527-.967 1.02Z">
                              </path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.marketplace.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.marketplace.description') ?></p>
                  </div>
              </div>

              <!-- Custom Options -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-emerald-50 to-teal-100 p-6 shadow-lg hover:shadow-xl border border-emerald-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-emerald-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings text-white">
                              <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z">
                              </path>
                              <circle cx="12" cy="12" r="3"></circle>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.custom_options.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.custom_options.description') ?></p>
                  </div>
              </div>

              <!-- Dynamic Router -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-violet-50 to-purple-100 p-6 shadow-lg hover:shadow-xl border border-violet-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-violet-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-route text-white">
                              <circle cx="6" cy="19" r="3"></circle>
                              <path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"></path>
                              <circle cx="18" cy="5" r="3"></circle>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.dynamic_router.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.dynamic_router.description') ?></p>
                  </div>
              </div>

              <!-- REST API -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-cyan-50 to-blue-100 p-6 shadow-lg hover:shadow-xl border border-cyan-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-cyan-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database text-white">
                              <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                              <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                              <path d="M3 12A9 3 0 0 0 21 12"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.rest_api.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.rest_api.description') ?></p>
                  </div>
              </div>

              <!-- Migration Tool -->
              <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-lime-50 to-green-100 p-6 shadow-lg hover:shadow-xl border border-lime-200 transition-all duration-300 hover:-translate-y-2">
                  <div class="p-0">
                      <div class="w-12 h-12 bg-lime-600 rounded-lg flex items-center justify-center mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw text-white">
                              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                              <path d="M21 3v5h-5"></path>
                              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                              <path d="M8 16H3v5"></path>
                          </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('feature.migration_tool.title') ?></h3>
                      <p class="text-sm text-slate-600"><?= _e('feature.migration_tool.description') ?></p>
                  </div>
              </div>

          </div>
      </div>
  </section>