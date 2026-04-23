  <section id="themes" class="py-16 md:py-24 bg-slate-50">
      <div class="container mx-auto px-4">
          <div class="text-center mb-16">
              <div class="text-center mb-16">
                  <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                      <?php _e('featured_themes.title.before') ?>
                      <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                          <?php _e('featured_themes.title.highlight') ?>
                      </span>
                  </h2>
                  <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                      <?php _e('featured_themes.description') ?>
                  </p>
              </div>


          </div>
          <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
              <?php
                $gradients = [
                    ['bg' => 'bg-gradient-to-br from-blue-50 to-indigo-100', 'border' => 'border-blue-200'],
                    ['bg' => 'bg-gradient-to-br from-green-50 to-emerald-100', 'border' => 'border-green-200'],
                    ['bg' => 'bg-gradient-to-br from-purple-50 to-violet-100', 'border' => 'border-purple-200'],
                    ['bg' => 'bg-gradient-to-br from-orange-50 to-amber-100', 'border' => 'border-orange-200'],
                    ['bg' => 'bg-gradient-to-br from-red-50 to-pink-100', 'border' => 'border-red-200'],
                    ['bg' => 'bg-gradient-to-br from-teal-50 to-cyan-100', 'border' => 'border-teal-200']
                ];
                ?>
              <!-- Hiển thị 6 themes -->
              <?php foreach ($themes as $index => $theme) : ?>
                  <?php
                    $themeStyle = $gradients[$index % count($gradients)]; // Sử dụng index để đảm bảo thứ tự
                    ?>

                  <div
                      class="rounded-lg flex flex-col group justify-between bg-card text-card-foreground <?= $themeStyle['bg'] ?> shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border <?= $themeStyle['border'] ?> overflow-hidden">
                      <div class="flex flex-col space-y-1.5 p-0 relative">
                          <a href="<?= base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>" class="block">
                              <?= _img(
                                    theme_assets(get_image_full($theme['thumbnail_url'] ?? $theme['thumbnail'] ?? '')), // Sử dụng thumbnail_url từ bảng mới
                                    $theme['title'],
                                    true,
                                    'w-full h-64 object-cover'
                                ) ?>
                          </a>

                          <?php if (!empty($theme['is_featured'])): ?>
                              <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                                  <div
                                      class="inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 text-xs font-semibold bg-yellow-500 text-white">
                                      <?php _e('featured') ?>
                                  </div>
                              </div>
                          <?php endif; ?>
                          <div class="absolute top-3 right-3">
                              <button
                                  onclick="toggleHeartWithAnimation(this)"
                                  type="button"
                                  data-id="<?= $theme['id'] ?>"
                                  class="favorite-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:text-accent-foreground h-9 rounded-md px-3 bg-white/80 hover:bg-white text-slate-700 hover:scale-105 active:scale-95 transition-all duration-200">
                                  <svg
                                      xmlns="http://www.w3.org/2000/svg"
                                      width="16"
                                      height="16"
                                      viewBox="0 0 24 24"
                                      fill="none"
                                      stroke="currentColor"
                                      stroke-width="2"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      class="lucide lucide-heart transition-all duration-300 group-hover:scale-110">
                                      <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                  </svg>
                              </button>
                          </div>


                          <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1">
                              <span class="text-lg font-bold text-slate-800">
                                  <?= (empty($theme['price']) || $theme['price'] == 0) ? \App\Libraries\Fastlang::__('free') : '$' . number_format($theme['price']) ?>
                              </span>
                          </div>
                      </div>
                      <div>
                          <div class="p-6">
                              <div class="flex items-center justify-between mb-2">
                                  <h3 class="text-xl font-bold text-slate-800 min-h-8 line-clamp-2">
                                      <a href="<?= base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>" class="group-hover:text-blue-600 inline"><?= $theme['title'] ?></a>
                                  </h3>

                              </div>
                              <p class="text-slate-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($theme['seo_desc'] ?? $theme['description'] ?? $theme['tagline'] ?? '') ?></p>
                              <div class="flex items-center justify-between text-sm text-slate-500">
                                  <div class="flex items-center space-x-1"><svg xmlns="http://www.w3.org/2000/svg"
                                          width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                          class="lucide lucide-star text-yellow-500 fill-current">
                                          <path
                                              d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                                          </path>
                                      </svg>
                                      <span><?= $theme['rating_avg'] ?? 4.8 ?></span>
                                  </div>
                                  <!-- Sử dụng rating_avg từ database -->
                                  <div class="flex items-center space-x-1"><svg xmlns="http://www.w3.org/2000/svg"
                                          width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                          class="lucide lucide-download">
                                          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                          <polyline points="7 10 12 15 17 10"></polyline>
                                          <line x1="12" x2="12" y1="15" y2="3"></line>
                                      </svg>
                                      <span><?= format_views($theme['download'] ?? $theme['views'] ?? 0) ?></span>
                                  </div>
                              </div>
                          </div>
                          <div class="items-center p-6 pt-0 flex justify-between gap-3">
                              <!-- <a href="<?= base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>" rel="nofollow">
                                  <button
                                      class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-10 px-4 py-2 flex-1 border-blue-500 text-blue-600 hover:bg-blue-50"><svg
                                          xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round" class="lucide lucide-eye mr-2">
                                          <path
                                              d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                          </path>
                                          <circle cx="12" cy="12" r="3"></circle>
                                      </svg><?= _e('theme.preview') ?>

                                  </button>
                                </a> -->


                              <a href="<?= base_url('libraries/themes/' . $theme['slug'], APP_LANG) ?>" class="flex-1">
                                  <button
                                      class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  h-10 px-4 py-2 flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 text-white"><svg
                                          xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round" class="lucide lucide-download mr-2">
                                          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                          <polyline points="7 10 12 15 17 10"></polyline>
                                          <line x1="12" x2="12" y1="15" y2="3"></line>
                                      </svg><?= _e('theme.download') ?>
                                  </button>
                              </a>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          </div>
          <div class="text-center mt-12">
              <a href="<?= base_url('libraries/themes', APP_LANG) ?>">
                  <button
                      class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 border-blue-600 text-blue-600 hover:bg-blue-50"><?= _e('theme.view_all') ?>
                  </button>
              </a>
          </div>
      </div>
  </section>
