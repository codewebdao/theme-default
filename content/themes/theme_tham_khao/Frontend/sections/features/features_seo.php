  <section class="py-16 md:py-24 bg-white">
      <div class="container mx-auto px-4">
          <div class="text-center mb-16">
              <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                  <?= _e('seo_performance.title.before') ?>
                  <span class="bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-pink-600">
                      <?= _e('seo_performance.title.highlight') ?>
                  </span>
              </h2>
              <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                  <?= _e('seo_performance.description') ?>
              </p>

          </div>
          <div class="grid lg:grid-cols-3 gap-8">
              <div
                  class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-purple-50 to-violet-100 border border-purple-200">
                  <div class="p-8">
                      <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-6">
                          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round" class="lucide lucide-search text-white">
                              <circle cx="11" cy="11" r="8"></circle>
                              <path d="m21 21-4.3-4.3"></path>
                          </svg>
                      </div>
                      <h3 class="text-xl font-bold text-slate-800 mb-4"><?= _e('seo_performance.seo_meta.title') ?></h3>
                      <div class="space-y-3 text-sm">
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.seo_meta.feature.meta') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.seo_meta.feature.opengraph') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.seo_meta.feature.sitemap') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.seo_meta.feature.robots') ?></span>
                          </div>
                      </div>
                      <div class="mt-6 p-4 bg-white rounded-lg">
                          <div class="text-xs text-slate-500 mb-1"><?= _e('seo_performance.seo_meta.score.label') ?></div>
                          <div class="flex items-center space-x-2 ">
                              <div aria-valuemax="100" aria-valuemin="0" role="progressbar" aria-label="SEO Score"
                                  data-state="indeterminate" data-max="100"
                                  class="relative h-4 w-full overflow-hidden rounded-full bg-purple-200 flex-1">
                                  <div data-state="indeterminate" data-max="100"
                                      class="h-full w-full flex-1 bg-purple-600 transition-all"
                                      style="transform: translateX(-5%);"></div>
                              </div>
                              <span class="text-sm font-semibold text-green-600"><?= _e('seo_performance.seo_meta.score.value') ?></span>
                          </div>
                      </div>
                  </div>
              </div>

              <div
                  class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-orange-50 to-amber-100 border border-orange-200">
                  <div class="p-8">
                      <div class="w-16 h-16 bg-orange-600 rounded-xl flex items-center justify-center mb-6">
                          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round" class="lucide lucide-code text-white">
                              <polyline points="16 18 22 12 16 6"></polyline>
                              <polyline points="8 6 2 12 8 18"></polyline>
                          </svg>
                      </div>
                      <h3 class="text-xl font-bold text-slate-800 mb-4"><?= _e('seo_performance.schema.title') ?></h3>
                      <div class="space-y-3 text-sm">
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.schema.feature.article') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.schema.feature.product') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.schema.feature.organization') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.schema.feature.faq') ?></span>
                          </div>
                      </div>
                      <div class="mt-6 p-4 bg-white rounded-lg">
                          <div class="text-xs text-slate-500 mb-2"><?= _e('seo_performance.schema.results.label') ?></div>
                          <div class="flex items-center space-x-2">
                              <?php for ($i = 0; $i < 5; $i++): ?>
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round" stroke-linejoin="round"
                                      class="lucide lucide-star text-yellow-500 fill-current">
                                      <path
                                          d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                                      </path>
                                  </svg>
                              <?php endfor; ?>
                              <span class="text-sm text-slate-600 ml-2"><?= _e('seo_performance.schema.results.text') ?></span>
                          </div>
                      </div>
                  </div>
              </div>



              <div
                  class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-yellow-50 to-amber-100 border border-yellow-200">
                  <div class="p-8">
                      <div class="w-16 h-16 bg-yellow-600 rounded-xl flex items-center justify-center mb-6">
                          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round" class="lucide lucide-zap text-white">
                              <path
                                  d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z">
                              </path>
                          </svg>
                      </div>
                      <h3 class="text-xl font-bold text-slate-800 mb-4"><?= _e('seo_performance.cache.title') ?></h3>
                      <div class="space-y-3 text-sm">
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.cache.feature.nginx') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.cache.feature.redis') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.cache.feature.cdn') ?></span>
                          </div>
                          <div class="flex items-center space-x-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round" class="lucide lucide-check text-green-600">
                                  <path d="M20 6 9 17l-5-5"></path>
                              </svg><span><?= _e('seo_performance.cache.feature.invalidate') ?></span>
                          </div>
                      </div>
                      <div class="mt-6 p-4 bg-white rounded-lg">
                          <div class="text-xs text-slate-500 mb-1"><?= _e('seo_performance.cache.label.load_time') ?></div>
                          <div class="text-2xl font-bold text-green-600"><?= _e('seo_performance.cache.load_time') ?></div>
                          <div class="text-xs text-slate-500"><?= _e('seo_performance.cache.note') ?></div>
                      </div>
                  </div>
              </div>



          </div>

          <!-- Updated HTML with necessary classes -->
          <div class="performance-metrics rounded-lg text-card-foreground shadow-sm mt-12 bg-slate-50 border-0">
              <div class="p-8">
                  <h3 class="text-2xl font-bold text-slate-800 text-center mb-8"><?= _e('seo_performance.metrics.title') ?></h3>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                      <div class="text-center metric-item">
                          <div class="relative w-24 h-24 mx-auto mb-4">
                              <svg class="progress-circle w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                  <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                  <path class="progress-path text-green-500" stroke="currentColor" stroke-width="3" fill="none"
                                      stroke-dasharray="0, 100"
                                      stroke-linecap="round"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                              </svg>
                              <div class="absolute inset-0 flex items-center justify-center">
                                  <span class="progress-number text-xl font-bold text-slate-800">0</span>
                              </div>
                          </div>
                          <div class="font-semibold text-slate-800"><?= _e('seo_performance.metrics.performance.label') ?></div>
                          <div class="text-sm text-slate-600"><?= _e('seo_performance.metrics.performance.sub') ?></div>
                      </div>

                      <div class="text-center metric-item">
                          <div class="relative w-24 h-24 mx-auto mb-4">
                              <svg class="progress-circle w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                  <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                  <path class="progress-path text-blue-500" stroke="currentColor" stroke-width="3" fill="none"
                                      stroke-dasharray="0, 100"
                                      stroke-linecap="round"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                              </svg>
                              <div class="absolute inset-0 flex items-center justify-center">
                                  <span class="progress-number text-xl font-bold text-slate-800">0</span>
                              </div>
                          </div>
                          <div class="font-semibold text-slate-800"><?= _e('seo_performance.metrics.seo.label') ?></div>
                          <div class="text-sm text-slate-600"><?= _e('seo_performance.metrics.seo.sub') ?></div>
                      </div>

                      <div class="text-center metric-item">
                          <div class="relative w-24 h-24 mx-auto mb-4">
                              <svg class="progress-circle w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                  <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                  <path class="progress-path text-purple-500" stroke="currentColor" stroke-width="3" fill="none"
                                      stroke-dasharray="0, 100"
                                      stroke-linecap="round"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                              </svg>
                              <div class="absolute inset-0 flex items-center justify-center">
                                  <span class="progress-number text-xl font-bold text-slate-800">0</span>
                              </div>
                          </div>
                          <div class="font-semibold text-slate-800"><?= _e('seo_performance.metrics.accessibility.label') ?></div>
                          <div class="text-sm text-slate-600"><?= _e('seo_performance.metrics.accessibility.sub') ?></div>
                      </div>

                      <div class="text-center metric-item">
                          <div class="relative w-24 h-24 mx-auto mb-4">
                              <svg class="progress-circle w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                  <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                  <path class="progress-path text-orange-500" stroke="currentColor" stroke-width="3" fill="none"
                                      stroke-dasharray="0, 100"
                                      stroke-linecap="round"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                              </svg>
                              <div class="absolute inset-0 flex items-center justify-center">
                                  <span class="progress-number text-xl font-bold text-slate-800">0</span>
                              </div>
                          </div>
                          <div class="font-semibold text-slate-800"><?= _e('seo_performance.metrics.best.label') ?></div>
                          <div class="text-sm text-slate-600"><?= _e('seo_performance.metrics.best.sub') ?></div>
                      </div>
                  </div>
              </div>
          </div>

      </div>
  </section>