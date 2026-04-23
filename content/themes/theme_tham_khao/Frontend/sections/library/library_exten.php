 <section class="py-16 md:py-24 bg-slate-50">
     <div class="container mx-auto px-4">
         <div class="text-center mb-16">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <?php _e('extensions_section.title.before') ?>
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                     <?php _e('extensions_section.title.highlight') ?>
                 </span>
             </h2>
             <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                 <?php _e('extensions_section.description') ?>
             </p>
         </div>

         <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

             <?php foreach ($exten as $item): ?>

                 <div
                     class="rounded-lg bg-card text-card-foreground <?= $item['background_class'] ?> shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 ">
                     <div class="flex flex-col space-y-1.5 p-6">
                         <div class="flex items-start justify-between">
                             <div class="p-3 rounded-xl <?= $item['icon_bg_class'] ?> shadow-lg">
                                 <?= $item['icon_url'] ?>
                             </div>
                             <!-- Changed 2xl:flex to md:flex for better responsiveness on tablets and larger -->
                             <div class="hidden md:flex flex-wrap gap-2">
                                 <div
                                     class="whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 text-xs font-semibold bg-green-500 text-white">
                                     <?php _e('extensions_section.title.tag1') ?>
                                 </div>
                             </div>
                         </div>
                         <div class="mt-4">
                             <div class="flex items-center justify-between mb-2">
                                 <h3 class="text-xl font-bold text-slate-800"><?= $item['title'] ?></h3>
                                 <span class="text-lg font-bold text-blue-600">
                                     <?= (empty($item['price']) || $item['price'] == 0) ? 'Free' : '$' . number_format($item['price']) ?>
                                 </span>
                             </div>
                             <div
                                 class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs mb-3">
                                 SEO
                             </div>
                         </div>
                     </div>
                     <div class="p-6 pt-0">
                         <p class="text-slate-600 text-sm mb-4"><?= $item['description'] ?></p>
                         <div class="flex items-center justify-between text-sm text-slate-500">
                             <div class="flex items-center space-x-1">
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
                                     class="lucide lucide-star text-yellow-500 fill-current">
                                     <path
                                         d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                 </svg>
                                 <span><?= $item['rating_avg'] ?></span>
                             </div>
                             <?php
                                if (!function_exists('formatCompactNumber')) {
                                    function formatCompactNumber($number)
                                    {
                                        if ($number >= 1000000) {
                                            return round($number / 1000000, 1) . 'M';
                                        } elseif ($number >= 1000) {
                                            return round($number / 1000, 1) . 'K';
                                        } else {
                                            return $number;
                                        }
                                    }
                                }
                                ?>
                             <div class="flex items-center space-x-1">
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
                                     class="lucide lucide-download">
                                     <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                     <polyline points="7 10 12 15 17 10"></polyline>
                                     <line x1="12" x2="12" y1="15" y2="3"></line>
                                 </svg>
                                 <span><?= formatCompactNumber($item['download'] ?? 0) ?></span>
                             </div>
                         </div>
                     </div>
                     <!-- Added flex-col for stacking on small screens, sm:flex-row for horizontal on larger -->
                     <div class="p-6 pt-0 flex flex-col sm:flex-row gap-2">
                         <a href="<?= content_url('extention', $item['slug']) ?>" target="_blank" rel="nofollow"
                             class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-10 px-4 py-2 flex-1 border-blue-500 text-blue-600 hover:bg-blue-50">
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
                                 class="lucide lucide-eye mr-2">
                                 <path
                                     d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                 <circle cx="12" cy="12" r="3"></circle>
                             </svg>
                             <?= _e('theme.preview') ?>
                         </a>
                         <a href="<?= base_url('download') ?>"
                             class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  h-10 px-4 py-2 flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
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
                                 class="lucide lucide-download mr-2">
                                 <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                 <polyline points="7 10 12 15 17 10"></polyline>
                                 <line x1="12" x2="12" y1="15" y2="3"></line>
                             </svg>
                             <?= _e('theme.download') ?>
                         </a>
                     </div>
                 </div>




             <?php endforeach; ?>
         </div>
         <div class="text-center mt-12"><button
                 class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 border-indigo-600 text-indigo-600 hover:bg-indigo-50"><?= _e('theme.extensions') ?></button></div>
     </div>
 </section>
