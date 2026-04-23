  <section class="py-16 md:py-24 bg-slate-50">
      <div class="container mx-auto px-4">
          <div class="text-center mb-16">
              <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                  <?= _e('security.title.before') ?>
                  <span class="bg-clip-text text-transparent bg-gradient-to-r from-red-600 to-pink-600">
                      <?= _e('security.title.highlight') ?>
                  </span>
              </h2>
              <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                  <?= _e('security.description') ?>
              </p>
          </div>
          <div class="grid lg:grid-cols-2 gap-12">
              <div class="rounded-lg text-card-foreground bg-white shadow-xl border-0">
                  <div class="p-8">
                      <div class="flex items-center space-x-4 mb-6">
                          <div class="w-16 h-16 bg-gradient-to-r from-red-600 to-pink-700 rounded-xl flex items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-shield-check text-white">
                                  <path
                                      d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                  </path>
                                  <path d="m9 12 2 2 4-4"></path>
                              </svg>
                          </div>
                          <div>
                              <h3 class="text-2xl font-bold text-slate-800"><?= _e('security.ai.title') ?></h3>
                              <p class="text-slate-600"><?= _e('security.ai.subtitle') ?></p>
                          </div>
                      </div>
                      <div class="space-y-4">
                          <?php foreach (
                                [
                                    'security.feature.sql',
                                    'security.feature.xss',
                                    'security.feature.csrf',
                                    'security.feature.threat',
                                    'security.feature.monitoring'
                                ] as $feature
                            ): ?>
                              <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                  <div class="flex items-center space-x-3">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round" class="lucide lucide-circle-check-big text-green-600">
                                          <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                          <path d="m9 11 3 3L22 4"></path>
                                      </svg>
                                      <span class="font-medium"><?= _e($feature) ?></span>
                                  </div>
                                  <span class="text-sm text-green-600 font-semibold"><?= _e('security.feature.active') ?></span>
                              </div>
                          <?php endforeach; ?>
                      </div>
                      <div class="mt-6 p-4 bg-red-50 rounded-lg">
                          <div class="flex items-center space-x-3">
                              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-shield text-red-600">
                                  <path
                                      d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                  </path>
                              </svg>
                              <div>
                                  <p class="text-sm text-red-800 font-medium"><?= _e('security.stats.no_breach_title') ?></p>
                                  <p class="text-sm text-red-700"><?= _e('security.stats.no_breach_desc') ?></p>
                              </div>
                          </div>
                      </div>
                  </div>

              </div>

              <div class="rounded-lg text-card-foreground bg-white shadow-xl border-0">
                  <div class="p-8">
                      <div class="flex items-center space-x-4 mb-6">
                          <div class="hidden sm:flex w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-users text-white">
                                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                  <circle cx="9" cy="7" r="4"></circle>
                                  <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                              </svg>
                          </div>
                          <div>
                              <h3 class="text-2xl font-bold text-slate-800"><?= _e('security.permission.title') ?></h3>
                              <p class="text-slate-600"><?= _e('security.permission.subtitle') ?></p>
                          </div>
                      </div>

                      <div class="space-y-4">
                          <div class="rounded-lg bg-card text-card-foreground shadow-sm border border-slate-200">
                              <div class="bg-slate-50 px-4 py-2 border-b border-slate-200">
                                  <h4 class="font-semibold text-slate-800"><?= _e('security.roles.title') ?></h4>
                              </div>
                              <div class="p-4 space-y-3">
                                  <div class="flex items-center justify-between">
                                      <div class="flex items-center space-x-3">
                                          <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                  viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                  class="lucide lucide-crown text-red-600">
                                                  <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"></path>
                                                  <path d="M5 21h14"></path>
                                              </svg>
                                          </div><span class="font-medium"><?= _e('security.roles.super_admin') ?></span>
                                      </div><span class="text-sm text-slate-500"><?= _e('security.roles.super_admin.desc') ?></span>
                                  </div>
                                  <div class="flex items-center justify-between">
                                      <div class="flex items-center space-x-3">
                                          <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                  viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                  class="lucide lucide-user-check text-blue-600">
                                                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                  <circle cx="9" cy="7" r="4"></circle>
                                                  <polyline points="16 11 18 13 22 9"></polyline>
                                              </svg>
                                          </div><span class="font-medium"><?= _e('security.roles.admin') ?></span>
                                      </div><span class="text-sm text-slate-500"><?= _e('security.roles.admin.desc') ?></span>
                                  </div>
                                  <div class="flex items-center justify-between">
                                      <div class="flex items-center space-x-3">
                                          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                  viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                  class="lucide lucide-square-pen text-green-600">
                                                  <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                  <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                              </svg>
                                          </div><span class="font-medium"><?= _e('security.roles.editor') ?></span>
                                      </div><span class="text-sm text-slate-500"><?= _e('security.roles.editor.desc') ?></span>
                                  </div>
                                  <div class="flex items-center justify-between">
                                      <div class="flex items-center space-x-3">
                                          <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                  viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                  class="lucide lucide-eye text-yellow-600">
                                                  <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                                  <circle cx="12" cy="12" r="3"></circle>
                                              </svg>
                                          </div><span class="font-medium"><?= _e('security.roles.viewer') ?></span>
                                      </div><span class="text-sm text-slate-500"><?= _e('security.roles.viewer.desc') ?></span>
                                  </div>
                              </div>
                          </div>

                      </div>
                      <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                          <div class="flex items-center space-x-3">
                              <svg xmlns="http://www.w3.org/2000/svg"
                                  width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                  class="lucide lucide-lock text-blue-600">
                                  <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                              </svg>
                              <div>
                                  <p class="text-sm text-blue-800 font-medium"><?= _e('security.permission.title') ?></p>
                                  <p class="text-sm text-blue-700"><?= _e('security.permission.subtitle') ?></p>
                              </div>
                          </div>
                      </div>

                  </div>
              </div>
              
          </div>
          <div class="rounded-lg text-card-foreground mt-12 bg-white shadow-xl border-0">
              <div class="p-8">
                  <h3 class="text-2xl font-bold text-slate-800 text-center mb-8"><?= _e('security.stats.title') ?></h3>
                  <div class="grid md:grid-cols-4 gap-6">
                      <div class="text-center">
                          <div class="text-3xl font-bold text-green-600 mb-2">100%</div>
                          <div class="text-sm text-slate-600"><?= _e('security.stats.uptime') ?></div>
                      </div>
                      <div class="text-center">
                          <div class="text-3xl font-bold text-blue-600 mb-2">0</div>
                          <div class="text-sm text-slate-600"><?= _e('security.stats.breach') ?></div>
                      </div>
                      <div class="text-center">
                          <div class="text-3xl font-bold text-purple-600 mb-2">24/7</div>
                          <div class="text-sm text-slate-600"><?= _e('security.stats.ai_monitor') ?></div>
                      </div>
                      <div class="text-center">
                          <div class="text-3xl font-bold text-orange-600 mb-2">99.9%</div>
                          <div class="text-sm text-slate-600"><?= _e('security.stats.threat_detection') ?></div>
                      </div>
                  </div>
              </div>
          </div>

      </div>
  </section>