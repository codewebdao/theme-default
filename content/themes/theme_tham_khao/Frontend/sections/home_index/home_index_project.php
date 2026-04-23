 <?php
    $blogs = $blogs ?? [];

    ?>

 <!-- Featured Projects -->
 <section id="portfolio" class="py-16 md:py-24 bg-white">
     <div class="container mx-auto px-4">
         <div class="text-center mb-16">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <?php _e('projects.heading.part1') ?> <span
                     class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600"><?php _e('projects.heading.highlight') ?></span>
             </h2>
             <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-2xl mx-auto">
                 <?php _e('projects.subheading') ?>
             </p>
         </div>
         <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
             <?php foreach (($projects['data'] ?? []) as $item): ?>
                 <div
                     class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 flex flex-col border border-blue-200">
                     <div class="flex flex-col space-y-1.5 p-0 relative">
                         <?= _img(
                                theme_assets($item['thumbnail_url']),
                                $item['title'],
                                true,
                                'w-full h-60 object-cover group-hover:scale-105 transition-transform duration-300'
                            ) ?>

                     </div>
                     <div class="p-6 flex-grow">
                         <p class="text-sm text-blue-600 font-semibold mb-1"><?= $item['title'] ?></p>
                         <div
                             class="tracking-tight text-xl font-semibold text-slate-800 mb-3 group-hover:text-indigo-700 transition-colors duration-300">
                             <a href="<?= $item['demo_url'] ?? '#' ?>" target="_blank"><?= $item['title'] ?></a>
                         </div>

                         <!-- Tags section -->
                         <div class="flex flex-wrap gap-1 mb-3">
                             <?php
                                // Updated color scheme to match the page theme
                                $tagColors = [
                                    'bg-gradient-to-r from-blue-500 to-blue-600',
                                    'bg-gradient-to-r from-indigo-500 to-indigo-600',
                                    'bg-gradient-to-r from-purple-500 to-purple-600',
                                    'bg-gradient-to-r from-slate-500 to-slate-600',
                                    'bg-gradient-to-r from-teal-500 to-teal-600',
                                    'bg-gradient-to-r from-emerald-500 to-emerald-600',
                                    'bg-gradient-to-r from-cyan-500 to-cyan-600'
                                ];

                                $usedColors = [];

                                $tags = explode(',', $item['tags']);
                                $tagCount = 0;
                                foreach ($tags as $tag):
                                    $tag = trim($tag);
                                    if (!$tag || $tagCount >= 3) continue; // Limit to 3 tags

                                    // Capitalize first letter
                                    $displayTag = ucfirst($tag);

                                    // Get color in order, then cycle
                                    $color = $tagColors[$tagCount % count($tagColors)];
                                    $tagCount++;
                                ?>
                                 <span class="text-xs font-medium px-2 py-1 rounded-md shadow-sm <?= $color ?> text-white backdrop-blur-sm border border-white/20 hover:scale-105 transition-transform duration-200">
                                     <?= htmlspecialchars($displayTag) ?>
                                 </span>
                             <?php endforeach; ?>
                         </div>
                     </div>
                     <div class="items-center p-6 bg-slate-50 border-t border-slate-200 flex space-x-3"><a
                             class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background h-10 px-4 py-2 flex-1 border-blue-500 text-blue-600 hover:bg-blue-50 hover:text-blue-700"
                             href="<?= $item['demo_url'] ?? '#' ?>" target="_blank" rel="nofollow"><svg xmlns="http://www.w3.org/2000/svg" width="18"
                                 height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye mr-2">
                                 <path
                                     d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                 </path>
                                 <circle cx="12" cy="12" r="3"></circle>
                             </svg><?php _e('button.view_demo') ?></a><aß
                             class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-primary/90 h-10 px-4 py-2 flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white"
                             href="<?= $item['demo_url'] ?? '#' ?>" target="_blank"><?php _e('button.details') ?><svg xmlns="http://www.w3.org/2000/svg"
                                 width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-arrow-right ml-2">
                                 <path d="M5 12h14"></path>
                                 <path d="m12 5 7 7-7 7"></path>
                             </svg></a></div>
                 </div>
             <?php endforeach; ?>
         </div>
         <!-- <div class="text-center mt-16"><button
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background h-11 rounded-md text-blue-600 border-blue-500 hover:border-blue-600 hover:bg-blue-50 hover:text-blue-700 font-semibold py-3 px-8 text-base transition-all duration-300">Xem
                    all projects</button></div> -->
     </div>
 </section>
