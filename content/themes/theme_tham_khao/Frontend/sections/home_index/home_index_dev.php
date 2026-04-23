 <!-- Excellent Developer Experience -->
 <section id="developer-experience" class="py-16 md:py-24 bg-slate-900 text-white">
     <div class="container mx-auto px-4">
         <div class="text-center mb-12">
             <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                 <?php _e('developer_experience.title_start') ?>
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-cyan-400">
                     <?php _e('developer_experience.title_end') ?>
                 </span>
                 <?php _e('developer_experience.title_middle') ?>
             </h2>
             <p class="mt-4 text-lg md:text-xl text-slate-300 max-w-3xl mx-auto">
                 <?php _e('developer_experience.description') ?>
             </p>
         </div>

         <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">
             <div class="overflow-auto">
                 <h3 class="text-2xl font-bold mb-6 flex items-center">
                     <!-- 🚀 Rocket Icon -->
                     <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rocket text-white mr-2 w-8 h-8">
                         <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z">
                         </path>
                         <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z">
                         </path>
                         <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                         <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                     </svg>
                     <?php _e('developer_experience.card_speed_title') ?>
                 </h3>
                 <div dir="ltr" data-orientation="horizontal" class="w-full">
                     <div role="tablist" aria-orientation="horizontal"
                         class="h-10 items-center justify-center rounded-md p-1 text-muted-foreground grid w-full grid-cols-3 bg-slate-800"
                         tabindex="0" data-orientation="horizontal" style="outline:none">

                         <button type="button" role="tab" aria-selected="true"
                             aria-controls="code-example-content-api" data-state="active"
                             id="code-example-trigger-api"
                             class="code-example-tab-btn inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:text-foreground data-[state=active]:shadow-sm data-[state=active]:bg-blue-600 bg-blue-600 text-white"
                             tabindex="0" data-orientation="horizontal" data-code-tab="api">
                             <?php _e('developer_experience.tab_api') ?>
                         </button>

                         <button type="button" role="tab" aria-selected="false"
                             aria-controls="code-example-content-theme" data-state="inactive"
                             id="code-example-trigger-theme"
                             class="code-example-tab-btn inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:text-foreground data-[state=active]:shadow-sm data-[state=active]:bg-blue-600 text-slate-300 hover:text-white"
                             tabindex="-1" data-orientation="horizontal" data-code-tab="theme">
                             <?php _e('developer_experience.tab_theme') ?>
                         </button>

                         <button type="button" role="tab" aria-selected="false"
                             aria-controls="code-example-content-plugin" data-state="inactive"
                             id="code-example-trigger-plugin"
                             class="code-example-tab-btn inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:text-foreground data-[state=active]:shadow-sm data-[state=active]:bg-blue-600 text-slate-300 hover:text-white"
                             tabindex="-1" data-orientation="horizontal" data-code-tab="plugin">
                             <?php _e('developer_experience.tab_plugin') ?>
                         </button>

                     </div>

                     <!-- API Tab Content -->
                     <div data-state="active" data-orientation="horizontal" role="tabpanel"
                         aria-labelledby="code-example-trigger-api" id="code-example-content-api" tabindex="0"
                         class="code-example-tab-content ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4">
                         <div class="rounded-lg border text-card-foreground shadow-sm bg-slate-800 border-slate-700">
                             <div class="p-4">
                                 <pre class="text-sm text-green-400 overflow-x-auto"><code>// <?php _e('cms_create_api_endpoint') ?><br>
// Create a new blog post via API
POST /api/v1/posts
{
  "title": "My First Blog Post",
  "content": "This is the content of my blog post",
  "category_id": 1,
  "tags": ["blog", "cms"],
  "status": "published"
}

// <?php _e('cms_auto_generated_ai') ?>
// Auto-generate API documentation
GET /api/v1/docs/swagger.json

// <?php _e('cms_restful_endpoints') ?>
GET    /api/v1/posts          // List all posts
POST   /api/v1/posts          // Create new post
GET    /api/v1/posts/{id}     // Get specific post
PUT    /api/v1/posts/{id}     // Update post
DELETE /api/v1/posts/{id}     // Delete post</code></pre>
                             </div>
                             <div class="px-4 pb-4">
                                 <a href="<?= docs_url(); ?>"
                                     class="inline-flex items-center text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                     </svg>
                                     View Restful API Docs
                                 </a>
                             </div>
                         </div>

                     </div>

                     <!-- Theme Tab Content -->
                     <div data-state="inactive" data-orientation="horizontal" role="tabpanel"
                         aria-labelledby="code-example-trigger-theme" id="code-example-content-theme" tabindex="0"
                         class="code-example-tab-content ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4 hidden">
                         <div class="rounded-lg border text-card-foreground shadow-sm bg-slate-800 border-slate-700">
                             <div class="p-4 space-y-4">
                                 <pre class="text-sm text-blue-400 overflow-x-auto"><code>&lt;!-- <?php _e('cms_customize_theme') ?> --&gt;
&lt;!-- <?php _e('cms_create_theme_component') ?> --&gt;

// Create a custom blog card component
&lt;?php
get_header([
    'meta' => $meta->render(),
    'schema' => $librarySchema->render(),
    'layout' => 'library'
]);

get_template('sections/library/library_title');

get_template('sections/library/library_all');

get_template('sections/library/library_themes', ['themes' => $themesFeatured['data'] ?? []]);

get_template('sections/library/library_plugs', ['plug' => $pluginsFeatured['data'] ?? []]);

get_template('sections/library/library_statis');

get_template('sections/library/library_src');

get_template('sections/library/library_rate');

get_template('sections/library/library_experience');

get_footer();
?&gt;</code></pre>

                                 <a href="<?= docs_url(); ?>"
                                     class="inline-flex items-center text-sm font-medium text-purple-400 hover:text-purple-300 transition-colors">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                     </svg>
                                     View Theme Docs
                                 </a>
                             </div>
                         </div>


                     </div>

                     <!-- Plugin Tab Content -->
                     <div data-state="inactive" data-orientation="horizontal" role="tabpanel"
                         aria-labelledby="code-example-trigger-plugin" id="code-example-content-plugin" tabindex="0"
                         class="code-example-tab-content ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4 hidden">
                         <div class="rounded-lg border text-card-foreground shadow-sm bg-slate-800 border-slate-700">
                             <div class="p-4 space-y-4">
                                 <pre class="text-sm text-purple-400 overflow-x-auto"><code>// <?php _e('cms_install_plugin') ?><br>
// <?php _e('cms_simple_plugin_ai') ?>

// Simple SEO Plugin
&lt;?php
class SEOPlugin extends \System\Libraries\Plugin\BasePlugin {
    public function beforeSave($data) {
        if (empty($data['meta_description'])) {
            $data['meta_description'] = substr(strip_tags($data['content']), 0, 160) . '...';
        }
        return $data;
    }
}

Plugin::register(new SEOPlugin());
?&gt;</code></pre>

                                 <a href="<?= docs_url(); ?>"
                                     class="inline-flex items-center text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                     </svg>
                                     View Plugin Docs
                                 </a>
                             </div>
                         </div>


                     </div>
                 </div>


             </div>
             <div class="space-y-6">
                 <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6 rounded-xl">
                     <h4 class="text-xl font-bold mb-2 flex items-center">
                         <!-- 🚀 Rocket/Speed Icon -->
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-3 text-white">
                             <path d="M4.5 16.5c-1.5 1.25-2 5 2 5s3.5-3.5 2-5l-1.5-1.5L4.5 16.5z" />
                             <path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z" />
                             <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0" />
                             <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5" />
                         </svg>
                         <?php _e('developer_experience.card_speed_title') ?>
                     </h4>
                     <p class="text-blue-100"><?php _e('developer_experience.card_speed_desc') ?></p>
                 </div>

                 <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6 rounded-xl">
                     <h4 class="text-xl font-bold mb-2 flex items-center">
                         <!-- 🎨 Palette/Design Icon -->
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-3 text-white">
                             <circle cx="13.5" cy="6.5" r=".5" />
                             <circle cx="17.5" cy="10.5" r=".5" />
                             <circle cx="8.5" cy="7.5" r=".5" />
                             <circle cx="6.5" cy="12.5" r=".5" />
                             <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z" />
                         </svg>
                         <?php _e('developer_experience.card_design_title') ?>
                     </h4>
                     <p class="text-purple-100"><?php _e('developer_experience.card_design_desc') ?></p>
                 </div>

                 <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 rounded-xl">
                     <h4 class="text-xl font-bold mb-2 flex items-center">
                         <!-- ⚡ Lightning/Fast Reload Icon -->
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-3 text-white">
                             <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                         </svg>
                         <?php _e('developer_experience.card_reload_title') ?>
                     </h4>
                     <p class="text-green-100"><?php _e('developer_experience.card_reload_desc') ?></p>
                 </div>
             </div>
         </div>
         <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
             <div
                 class="bg-card text-card-foreground shadow-sm bg-gradient-to-br from-blue-50 to-indigo-100 border border-blue-200 hover:shadow-xl transition-all duration-300 rounded-xl hover:-translate-y-1">
                 <div class="flex flex-col space-y-1.5 p-6">
                     <div class="flex items-center space-x-4">
                         <div class="p-3 rounded-lg bg-blue-600 shadow-md">
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-code text-white">
                                 <polyline points="16 18 22 12 16 6"></polyline>
                                 <polyline points="8 6 2 12 8 18"></polyline>
                             </svg>
                         </div>
                         <div class="font-semibold tracking-tight text-xl text-slate-800">
                             <?php _e('feature.api_architecture.title'); ?>
                         </div>
                     </div>
                 </div>
                 <div class="p-6 pt-0">
                     <p class="text-slate-600"><?php _e('feature.api_architecture.desc'); ?></p>
                 </div>
             </div>

             <div
                 class="bg-card text-card-foreground shadow-sm bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200 hover:shadow-xl transition-all duration-300 rounded-xl hover:-translate-y-1">
                 <div class="flex flex-col space-y-1.5 p-6">
                     <div class="flex items-center space-x-4">
                         <div class="p-3 rounded-lg bg-green-600 shadow-md">
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-terminal text-white">
                                 <polyline points="4 17 10 11 4 5"></polyline>
                                 <line x1="12" x2="20" y1="19" y2="19"></line>
                             </svg>
                         </div>
                         <div class="font-semibold tracking-tight text-xl text-slate-800">
                             <?php _e('feature.cli_tools.title'); ?>
                         </div>
                     </div>
                 </div>
                 <div class="p-6 pt-0">
                     <p class="text-slate-600"><?php _e('feature.cli_tools.desc'); ?></p>
                 </div>
             </div>

             <div class="bg-card text-card-foreground shadow-sm bg-gradient-to-br from-purple-50 to-violet-100 border border-purple-200 hover:shadow-xl transition-all duration-300 rounded-xl hover:-translate-y-1">
                 <div class="flex flex-col space-y-1.5 p-6">
                     <div class="flex items-center space-x-4">
                         <div class="p-3 rounded-lg bg-purple-600 shadow-md">
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-palette text-white">
                                 <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                 <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                 <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                 <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                 <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                             </svg>
                         </div>
                         <div class="font-semibold tracking-tight text-xl text-slate-800">
                             <?php _e('feature.theme_kit.title'); ?>
                         </div>
                     </div>
                 </div>
                 <div class="p-6 pt-0">
                     <p class="text-slate-600"><?php _e('feature.theme_kit.desc'); ?></p>
                 </div>
             </div>

             <div class="bg-card text-card-foreground shadow-sm bg-gradient-to-br from-yellow-50 to-amber-100 border border-yellow-200 hover:shadow-xl transition-all duration-300 rounded-xl hover:-translate-y-1">
                 <div class="flex flex-col space-y-1.5 p-6">
                     <div class="flex items-center space-x-4">
                         <div class="p-3 rounded-lg bg-yellow-600 shadow-md">
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-zap text-white">
                                 <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                             </svg>
                         </div>
                         <div class="font-semibold tracking-tight text-xl text-slate-800">
                             <?php _e('feature.ai_development.title'); ?>
                         </div>
                     </div>
                 </div>
                 <div class="p-6 pt-0">
                     <p class="text-slate-600"><?php _e('feature.ai_development.desc'); ?></p>
                 </div>
             </div>

             <div class="bg-card text-card-foreground shadow-sm bg-gradient-to-br from-indigo-50 to-blue-100 border border-indigo-200 hover:shadow-xl transition-all duration-300 rounded-xl hover:-translate-y-1">
                 <div class="flex flex-col space-y-1.5 p-6">
                     <div class="flex items-center space-x-4">
                         <div class="p-3 rounded-lg bg-indigo-600 shadow-md">
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-git-branch text-white">
                                 <line x1="6" x2="6" y1="3" y2="15"></line>
                                 <circle cx="18" cy="6" r="3"></circle>
                                 <circle cx="6" cy="18" r="3"></circle>
                                 <path d="M18 9a9 9 0 0 1-9 9"></path>
                             </svg>
                         </div>
                         <div class="font-semibold tracking-tight text-xl text-slate-800">
                             <?php _e('feature.git_integration.title'); ?>
                         </div>
                     </div>
                 </div>
                 <div class="p-6 pt-0">
                     <p class="text-slate-600"><?php _e('feature.git_integration.desc'); ?></p>
                 </div>
             </div>

             <div
                 class="bg-card text-card-foreground shadow-sm bg-gradient-to-br from-red-50 to-pink-100 border border-red-200 hover:shadow-xl transition-all duration-300 rounded-xl hover:-translate-y-1">
                 <div class="flex flex-col space-y-1.5 p-6">
                     <div class="flex items-center space-x-4">
                         <div class="p-3 rounded-lg bg-red-600 shadow-md">
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-package text-white">
                                 <path
                                     d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                 </path>
                                 <path d="M12 22V12"></path>
                                 <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                 <path d="m7.5 4.27 9 5.15"></path>
                             </svg>
                         </div>
                         <div class="font-semibold tracking-tight text-xl text-slate-800">
                             <?php _e('feature.package_manager.title'); ?>
                         </div>
                     </div>
                 </div>
                 <div class="p-6 pt-0">
                     <p class="text-slate-600"><?php _e('feature.package_manager.desc'); ?></p>
                 </div>
             </div>

         </div>
         <div class="mt-12 text-center">
             <div class="bg-slate-800 rounded-xl p-8 max-w-4xl mx-auto">
                 <h3 class="text-2xl font-bold mb-4"><?php _e('developer_experience.community_title') ?></h3>
                 <p class="text-slate-300 mb-6"><?php _e('developer_experience.community_desc') ?></p>
                 <div class="flex flex-col md:flex-row justify-center md:space-x-4 gap-2">
                     <a href="<?= docs_url() ?>" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 text-primary-foreground h-11 rounded-md px-8 bg-blue-600 hover:bg-blue-700">
                         <?php _e('developer_experience.documentation') ?>
                     </a>

                     <button
                         class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 border-slate-600 text-slate-300 hover:bg-slate-700">
                         <?php _e('developer_experience.join_discord') ?>
                     </button>
                 </div>
             </div>
         </div>

     </div>
 </section>
