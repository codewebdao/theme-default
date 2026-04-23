 <section class="py-16 md:py-24 bg-slate-50">
     <div class="container mx-auto px-4">
         <div class="text-center mb-16">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                 <?php _e('related_posts_title') ?>
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">
                     <?php _e('related_posts_related') ?>
                 </span>
             </h2>
             <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                 <?php _e('related_posts_description') ?>
             </p>
         </div>
         <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">

             <?php foreach ($blogs as $blog): ?>

                 <div
                     class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-blue-200 overflow-hidden group">
                     <div class="flex flex-col space-y-1.5 p-0 relative"><img
                             src="<?php echo theme_assets('images/blogs/' . $blog['thumb_url']) ?>"
                             alt="<?php echo $blog['title'] ?>"
                             class="w-full h-48 object-cover">
                         <div class="absolute top-3 left-3">
                             <div
                                 class="inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 text-xs font-semibold bg-blue-500 text-white">
                                 Performance</div>
                         </div>
                     </div>
                     <div class="p-6">
                         <h3
                             class="text-xl font-bold text-slate-800 mb-3 group-hover:text-blue-700 transition-colors duration-300">
                             <a href="<?php echo content_url('blog', $blog['slug']) ?>"><?php echo $blog['title'] ?></a>
                         </h3>
                         <p class="text-slate-600 text-sm mb-4 line-clamp-3"><?php echo $blog['content'] ?></p>
                         <div class="flex items-center justify-between text-xs text-slate-500">
                             <div class="flex items-center space-x-1"><svg xmlns="http://www.w3.org/2000/svg"
                                     width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-user">
                                     <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                     <circle cx="12" cy="7" r="4"></circle>
                                 </svg><span><?php echo $blog['author'] ?></span></div>

                             <?php
                                $date = date('j', strtotime($blog['created_at']))
                                    . ' month '
                                    . date('n', strtotime($blog['created_at']))
                                    . ', '
                                    . date('Y', strtotime($blog['created_at']));
                                ?>
                             <div class="flex items-center space-x-1"><svg xmlns="http://www.w3.org/2000/svg"
                                     width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="lucide lucide-calendar">
                                     <path d="M8 2v4"></path>
                                     <path d="M16 2v4"></path>
                                     <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                     <path d="M3 10h18"></path>
                                 </svg><span><?php echo $date ?></span></div>
                         </div>
                         <div class="mt-2 text-xs text-slate-500 flex items-center space-x-1"><svg
                                 xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-eye">
                                 <path
                                     d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                 </path>
                                 <circle cx="12" cy="12" r="3"></circle>
                             </svg><span><?php echo format_views($blog['views']) ?></span></div>
                     </div>
                     <div class="flex items-center p-6 pt-0"><a
                             class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-blue-700 font-semibold group-hover:translate-x-1 transition-all duration-300"
                             href="<?php echo content_url('blog', $blog['slug']) ?>"><?php _e('read_more') ?> <svg
                                 xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                 <path d="M5 12h14"></path>
                                 <path d="m12 5 7 7-7 7"></path>
                             </svg></a></div>
                 </div>

             <?php endforeach; ?>
         </div>
     </div>
 </section>
