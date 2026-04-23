<!-- Direct Demo Experience -->
<section id="live-demo" class="py-16 md:py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('landing.demo_experience.title_part1') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                    <?php _e('landing.demo_experience.title_part2') ?>
                </span>
            </h2>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('landing.demo_experience.description') ?>
            </p>
        </div>

        <style>
            .template-item.active {
                border-color: #3B82F6;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                transform: scale(1.05);
            }

            .device-frame.desktop-view {
                width: 100%;
                max-width: 800px;
            }

            .device-frame.tablet-view {
                width: 100%;
                max-width: 500px;
            }

            .device-frame.mobile-view {
                width: 100%;
                max-width: 320px;
            }

            .fade-in {
                animation: fadeIn 0.3s ease-in-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>

        <div class="grid lg:grid-cols-3 gap-8 mb-12">
            <!-- E-commerce Store -->
            <div class="template-item rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 cursor-pointer transition-all duration-300 border border-blue-200 ring-2 ring-blue-500 shadow-xl scale-105 active"
                data-template="ecommerce">
                <div class="flex flex-col space-y-1.5 p-0">
                    <img src="<?= theme_assets('images/ecomere.png') ?>" alt="E-commerce Store"
                        class="w-full h-48 object-cover rounded-t-lg">
                </div>

                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold tracking-tight text-lg">
                            <?php _e('template.ecommerce.title'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-green-100 text-green-800">
                            0.3s
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm mb-4">
                        <?php _e('template.ecommerce.description'); ?>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.ecommerce.feature_1'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.ecommerce.feature_2'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.ecommerce.feature_more'); ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Magazine Blog -->
            <div class="template-item rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-green-50 to-emerald-100 cursor-pointer transition-all duration-300 border border-green-200 hover:shadow-lg hover:scale-102"
                data-template="blog">
                <div class="flex flex-col space-y-1.5 p-0">
                    <img src="<?= theme_assets('images/blog.png') ?>" alt="Magazine Blog"
                        class="w-full h-48 object-cover rounded-t-lg">
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold tracking-tight text-lg">
                            <?php _e('template.blog.title'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-green-100 text-green-800">
                            0.2s
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm mb-4">
                        <?php _e('template.blog.description'); ?>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.blog.feature_1'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.blog.feature_2'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.blog.feature_more'); ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Corporate Website -->
            <div class="template-item rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-purple-50 to-violet-100 cursor-pointer transition-all duration-300 border border-purple-200 hover:shadow-lg hover:scale-102"
                data-template="corporate">
                <div class="flex flex-col space-y-1.5 p-0">
                    <img src="<?= theme_assets('images/cop.png') ?>" alt="Corporate Website"
                        class="w-full h-48 object-cover rounded-t-lg">
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold tracking-tight text-lg">
                            <?php _e('template.corporate.title'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-green-100 text-green-800">
                            0.4s
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm mb-4">
                        <?php _e('template.corporate.description'); ?>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.corporate.feature_1'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.corporate.feature_2'); ?>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs">
                            <?php _e('template.corporate.feature_more'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Template Detail Section -->
        <div class="rounded-lg bg-card text-card-foreground shadow-2xl border-0 overflow-hidden">
            <!-- Header -->
            <div class="flex flex-col space-y-1.5 bg-gradient-to-r from-slate-800 to-slate-900 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="text-2xl font-semibold leading-none tracking-tight flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-play">
                            <polygon points="6 3 20 12 6 21 6 3"></polygon>
                        </svg>
                        <span class="template-title">E-commerce Store - Live Demo</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-sm">Live</span>
                        </div>
                        <div
                            class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-blue-600 text-white">
                            Performance: <span class="template-performance">98</span>/100
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="p-0">
                <div dir="ltr" data-orientation="horizontal" class="w-full">
                    <div class="flex items-center justify-between p-4 bg-slate-100 border-b">
                        <!-- Preview/Code Tabs -->
                        <div role="tablist" aria-orientation="horizontal"
                            class="h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-fit grid-cols-2"
                            tabindex="0" data-orientation="horizontal" style="outline:none">
                            <button type="button" role="tab" aria-selected="true"
                                aria-controls="template-content-preview" data-state="active"
                                id="template-trigger-preview"
                                class="template-tab justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center space-x-2 bg-white text-gray-900 shadow-sm"
                                tabindex="0" data-orientation="horizontal" data-tab="preview">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-eye">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                    </path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>Preview</span>
                            </button>
                            <button type="button" role="tab" aria-selected="false"
                                aria-controls="template-content-code" data-state="inactive"
                                id="template-trigger-code"
                                class="template-tab justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center space-x-2 text-gray-600"
                                tabindex="-1" data-orientation="horizontal" data-tab="code">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-code">
                                    <polyline points="16 18 22 12 16 6"></polyline>
                                    <polyline points="8 6 2 12 8 18"></polyline>
                                </svg>
                                <span>Code</span>
                            </button>
                        </div>

                        <!-- Device Selector -->
                        <div class="flex items-center space-x-2">
                            <button aria-label="Desktop"
                                class="device-btn inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3"
                                data-device="desktop" title="Desktop">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-monitor">
                                    <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                                    <line x1="8" x2="16" y1="21" y2="21"></line>
                                    <line x1="12" x2="12" y1="17" y2="21"></line>
                                </svg>
                            </button>
                            <button aria-label="Tablet"
                                class="device-btn inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3"
                                data-device="tablet" title="Tablet">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-tablet">
                                    <rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect>
                                    <line x1="12" x2="12.01" y1="18" y2="18"></line>
                                </svg>
                            </button>
                            <button aria-label="Mobile"
                                class="device-btn inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3"
                                data-device="mobile" title="Mobile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-smartphone">
                                    <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                                    <path d="M12 18h.01"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <!-- Preview Content -->
                    <div data-state="active" data-orientation="horizontal" role="tabpanel"
                        aria-labelledby="template-trigger-preview" id="template-content-preview" tabindex="0"
                        class="template-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 p-6">
                        <div class="flex justify-center">
                            <div
                                class="device-frame tablet-view border border-slate-300 rounded-lg overflow-hidden shadow-lg transition-all duration-300">
                                <!-- E-commerce Preview -->
                                <div class="template-preview h-80" data-template="ecommerce">
                                    <div class="w-full h-full bg-gradient-to-br from-blue-50 to-blue-100 p-6">
                                        <div class="bg-white rounded-lg shadow-md h-full p-4">
                                            <div class="flex items-center justify-between mb-4">
                                                <h2 class="text-xl font-bold text-blue-600">ShopMart</h2>
                                                <div class="flex space-x-2">
                                                    <div class="w-6 h-6 bg-blue-500 rounded"></div>
                                                    <div class="w-6 h-6 bg-gray-300 rounded"></div>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 mb-4">
                                                <div class="bg-gray-100 rounded p-3">
                                                    <div class="w-full h-16 bg-blue-200 rounded mb-2"></div>
                                                    <div class="text-sm font-medium">Product 1</div>
                                                    <div class="text-xs text-gray-600">$29.99</div>
                                                </div>
                                                <div class="bg-gray-100 rounded p-3">
                                                    <div class="w-full h-16 bg-green-200 rounded mb-2"></div>
                                                    <div class="text-sm font-medium">Product 2</div>
                                                    <div class="text-xs text-gray-600">$39.99</div>
                                                </div>
                                            </div>
                                            <button class="w-full bg-blue-500 text-white py-2 rounded text-sm">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Blog Preview -->
                                <div class="template-preview h-80 hidden" data-template="blog">
                                    <div class="w-full h-full bg-gradient-to-br from-green-50 to-green-100 p-6">
                                        <div class="bg-white rounded-lg shadow-md h-full p-4">
                                            <div class="flex items-center justify-between mb-4">
                                                <h2 class="text-xl font-bold text-green-600">TechBlog</h2>
                                                <div class="text-xs text-gray-500">Latest Posts</div>
                                            </div>
                                            <div class="space-y-3">
                                                <article class="border-b pb-3">
                                                    <div class="w-full h-12 bg-green-200 rounded mb-2"></div>
                                                    <h3 class="text-sm font-medium">Latest Technology Trends</h3>
                                                    <p class="text-xs text-gray-600">Published 2 hours ago</p>
                                                </article>
                                                <article class="border-b pb-3">
                                                    <div class="w-full h-12 bg-blue-200 rounded mb-2"></div>
                                                    <h3 class="text-sm font-medium">Web Development Tips</h3>
                                                    <p class="text-xs text-gray-600">Published 1 day ago</p>
                                                </article>
                                            </div>
                                            <button
                                                class="w-full bg-green-500 text-white py-2 rounded text-sm mt-4">
                                                Read More
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Corporate Preview -->
                                <div class="template-preview h-80 hidden" data-template="corporate">
                                    <div class="w-full h-full bg-gradient-to-br from-purple-50 to-purple-100 p-6">
                                        <div class="bg-white rounded-lg shadow-md h-full p-4">
                                            <div class="text-center mb-4">
                                                <h2 class="text-xl font-bold text-purple-600">CorpTech</h2>
                                                <p class="text-xs text-gray-600">Professional Solutions</p>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2 mb-4">
                                                <div class="text-center">
                                                    <div class="w-8 h-8 bg-purple-200 rounded-full mx-auto mb-1">
                                                    </div>
                                                    <div class="text-xs">Services</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="w-8 h-8 bg-blue-200 rounded-full mx-auto mb-1">
                                                    </div>
                                                    <div class="text-xs">About</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="w-8 h-8 bg-green-200 rounded-full mx-auto mb-1">
                                                    </div>
                                                    <div class="text-xs">Contact</div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-100 rounded p-3 mb-4">
                                                <div class="text-sm font-medium mb-2">Our Team</div>
                                                <div class="flex space-x-2">
                                                    <div class="w-6 h-6 bg-purple-300 rounded-full"></div>
                                                    <div class="w-6 h-6 bg-blue-300 rounded-full"></div>
                                                    <div class="w-6 h-6 bg-green-300 rounded-full"></div>
                                                </div>
                                            </div>
                                            <button class="w-full bg-purple-500 text-white py-2 rounded text-sm">
                                                Get Quote
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Code Content -->
                    <div data-state="inactive" data-orientation="horizontal" role="tabpanel"
                        aria-labelledby="template-trigger-code" id="template-content-code" tabindex="0"
                        class="template-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 p-6 hidden">
                        <div class="template-code-container">
                            <!-- E-commerce Code -->
                            <div class="template-code" data-template="ecommerce">
                                <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;!-- E-commerce Store Template --&gt;
&lt;div class="min-h-screen bg-gray-50"&gt;
  &lt;header class="bg-white shadow-sm"&gt;
    &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"&gt;
      &lt;div class="flex justify-between items-center py-6"&gt;
        &lt;h1 class="text-2xl font-bold text-gray-900"&gt;ShopMart&lt;/h1&gt;
        &lt;div class="flex items-center space-x-4"&gt;
          &lt;button class="text-gray-500 hover:text-gray-700"&gt;
            &lt;svg class="w-6 h-6" fill="none" stroke="currentColor"&gt;
              &lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v5a2 2 0 01-2 2H9a2 2 0 01-2-2v-5m6-5V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2"&gt;&lt;/path&gt;
            &lt;/svg&gt;
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/header&gt;

  &lt;main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8"&gt;
    &lt;div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"&gt;
      &lt;div class="bg-white rounded-lg shadow-md overflow-hidden"&gt;
        &lt;img src="product1.jpg" alt="Product 1" class="w-full h-48 object-cover"&gt;
        &lt;div class="p-4"&gt;
          &lt;h3 class="text-lg font-semibold text-gray-900"&gt;Product Name&lt;/h3&gt;
          &lt;p class="text-gray-600 text-sm mt-1"&gt;Product description&lt;/p&gt;
          &lt;div class="mt-4 flex items-center justify-between"&gt;
            &lt;span class="text-xl font-bold text-gray-900"&gt;$29.99&lt;/span&gt;
            &lt;button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"&gt;
              Add to Cart
            &lt;/button&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/main&gt;
&lt;/div&gt;</code>
</pre>
                            </div>

                            <!-- Blog Code -->
                            <div class="template-code hidden" data-template="blog">
                                <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;!-- Magazine Blog Template --&gt;
&lt;div class="min-h-screen bg-gray-50"&gt;
  &lt;header class="bg-white shadow-sm"&gt;
    &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"&gt;
      &lt;div class="flex justify-between items-center py-6"&gt;
        &lt;h1 class="text-2xl font-bold text-gray-900"&gt;TechBlog&lt;/h1&gt;
        &lt;nav class="hidden md:flex space-x-8"&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;Home&lt;/a&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;Categories&lt;/a&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;About&lt;/a&gt;
        &lt;/nav&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/header&gt;

  &lt;main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8"&gt;
    &lt;div class="grid grid-cols-1 lg:grid-cols-3 gap-8"&gt;
      &lt;div class="lg:col-span-2"&gt;
        &lt;article class="bg-white rounded-lg shadow-md overflow-hidden mb-6"&gt;
          &lt;img src="article1.jpg" alt="Article" class="w-full h-64 object-cover"&gt;
          &lt;div class="p-6"&gt;
            &lt;h2 class="text-2xl font-bold text-gray-900 mb-2"&gt;Article Title&lt;/h2&gt;
            &lt;p class="text-gray-600 mb-4"&gt;Article excerpt goes here...&lt;/p&gt;
            &lt;div class="flex items-center text-sm text-gray-500"&gt;
              &lt;span&gt;By Author Name&lt;/span&gt;
              &lt;span class="mx-2"&gt;•&lt;/span&gt;
              &lt;span&gt;March 15, 2024&lt;/span&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/article&gt;
      &lt;/div&gt;
      
      &lt;aside class="lg:col-span-1"&gt;
        &lt;div class="bg-white rounded-lg shadow-md p-6"&gt;
          &lt;h3 class="text-lg font-semibold text-gray-900 mb-4"&gt;Recent Posts&lt;/h3&gt;
          &lt;div class="space-y-4"&gt;
            &lt;div class="flex space-x-3"&gt;
              &lt;img src="thumb1.jpg" alt="Thumbnail" class="w-16 h-16 object-cover rounded"&gt;
              &lt;div&gt;
                &lt;h4 class="text-sm font-medium text-gray-900"&gt;Post Title&lt;/h4&gt;
                &lt;p class="text-xs text-gray-500"&gt;2 hours ago&lt;/p&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/aside&gt;
    &lt;/div&gt;
  &lt;/main&gt;
&lt;/div&gt;</code></pre>
                            </div>

                            <!-- Corporate Code -->
                            <div class="template-code hidden" data-template="corporate">
                                <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;!-- Corporate Website Template --&gt;
&lt;div class="min-h-screen bg-gray-50"&gt;
  &lt;header class="bg-white shadow-sm"&gt;
    &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"&gt;
      &lt;div class="flex justify-between items-center py-6"&gt;
        &lt;h1 class="text-2xl font-bold text-gray-900"&gt;CorpTech&lt;/h1&gt;
        &lt;nav class="hidden md:flex space-x-8"&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;Services&lt;/a&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;About&lt;/a&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;Team&lt;/a&gt;
          &lt;a href="#" class="text-gray-500 hover:text-gray-900"&gt;Contact&lt;/a&gt;
        &lt;/nav&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/header&gt;

  &lt;main&gt;
    &lt;section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-20"&gt;
      &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"&gt;
        &lt;h2 class="text-4xl font-bold mb-4"&gt;Professional Solutions&lt;/h2&gt;
        &lt;p class="text-xl mb-8"&gt;We deliver innovative technology solutions for your business&lt;/p&gt;
        &lt;button class="bg-white text-blue-600 px-8 py-3 rounded-md font-semibold hover:bg-gray-100"&gt;
          Get Started
        &lt;/button&gt;
      &lt;/div&gt;
    &lt;/section&gt;

    &lt;section class="py-16"&gt;
      &lt;div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"&gt;
        &lt;h3 class="text-3xl font-bold text-center text-gray-900 mb-12"&gt;Our Services&lt;/h3&gt;
        &lt;div class="grid grid-cols-1 md:grid-cols-3 gap-8"&gt;
          &lt;div class="text-center"&gt;
            &lt;div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4"&gt;
              &lt;svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20"&gt;
                &lt;path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"&gt;&lt;/path&gt;
              &lt;/svg&gt;
            &lt;/div&gt;
            &lt;h4 class="text-xl font-semibold text-gray-900 mb-2"&gt;Consulting&lt;/h4&gt;
            &lt;p class="text-gray-600"&gt;Expert advice for your business needs&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/section&gt;
  &lt;/main&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function() {
                'use strict';

                // Template Grid Handler
                const TemplateGrid = {
                    currentTemplate: 'ecommerce',
                    currentDevice: 'tablet',
                    currentTab: 'preview',

                    // Template data
                    templateData: {
                        'ecommerce': {
                            title: 'E-commerce Store - Live Demo',
                            performance: 98
                        },
                        'blog': {
                            title: 'Magazine Blog - Live Demo',
                            performance: 95
                        },
                        'corporate': {
                            title: 'Corporate Website - Live Demo',
                            performance: 92
                        }
                    },

                    // Initialize
                    init: function() {
                        this.bindTemplateEvents();
                        this.bindDeviceEvents();
                        this.bindTabEvents();
                        this.updateDisplay();
                    },

                    // Bind template grid events
                    bindTemplateEvents: function() {
                        const templateItems = document.querySelectorAll('.template-item');
                        templateItems.forEach(item => {
                            item.addEventListener('click', (e) => {
                                const template = item.getAttribute('data-template');
                                this.selectTemplate(template);
                            });
                        });
                    },

                    // Bind device selector events
                    bindDeviceEvents: function() {
                        const deviceBtns = document.querySelectorAll('.device-btn');
                        deviceBtns.forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                const device = btn.getAttribute('data-device');
                                this.selectDevice(device);
                            });
                        });
                    },

                    // Bind preview/code tab events
                    bindTabEvents: function() {
                        const tabBtns = document.querySelectorAll('.template-tab');
                        tabBtns.forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                const tab = btn.getAttribute('data-tab');
                                this.selectTab(tab);
                            });
                        });
                    },

                    // Select template
                    selectTemplate: function(template) {
                        this.currentTemplate = template;
                        this.updateTemplateSelection();
                        this.updateHeader();
                        this.updateDisplay();
                    },

                    // Select device
                    selectDevice: function(device) {
                        this.currentDevice = device;
                        this.updateDeviceSelection();
                        this.updateDeviceFrame();
                    },

                    // Select tab
                    selectTab: function(tab) {
                        this.currentTab = tab;
                        this.updateTabSelection();
                        this.updateContentDisplay();
                    },

                    // Update template selection
                    updateTemplateSelection: function() {
                        const templateItems = document.querySelectorAll('.template-item');
                        templateItems.forEach(item => {
                            const template = item.getAttribute('data-template');
                            if (template === this.currentTemplate) {
                                item.classList.add('active');
                            } else {
                                item.classList.remove('active');
                            }
                        });
                    },

                    // Update device selection
                    updateDeviceSelection: function() {
                        const deviceBtns = document.querySelectorAll('.device-btn');
                        deviceBtns.forEach(btn => {
                            const device = btn.getAttribute('data-device');
                            if (device === this.currentDevice) {
                                btn.classList.remove('border', 'border-input', 'bg-background', 'hover:bg-accent', 'hover:text-accent-foreground');
                                btn.classList.add('bg-primary', 'text-primary-foreground', 'hover:bg-primary/90');
                            } else {
                                btn.classList.add('border', 'border-input', 'bg-background', 'hover:bg-accent', 'hover:text-accent-foreground');
                                btn.classList.remove('bg-primary', 'text-primary-foreground', 'hover:bg-primary/90');
                            }
                        });
                    },

                    // Update tab selection
                    updateTabSelection: function() {
                        const tabBtns = document.querySelectorAll('.template-tab');
                        tabBtns.forEach(btn => {
                            const tab = btn.getAttribute('data-tab');
                            if (tab === this.currentTab) {
                                btn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                                btn.classList.remove('text-gray-600');
                            } else {
                                btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                                btn.classList.add('text-gray-600');
                            }
                        });
                    },

                    // Update header info
                    updateHeader: function() {
                        const data = this.templateData[this.currentTemplate];
                        const titleElement = document.querySelector('.template-title');
                        const performanceElement = document.querySelector('.template-performance');

                        if (titleElement && data) {
                            titleElement.textContent = data.title;
                        }
                        if (performanceElement && data) {
                            performanceElement.textContent = data.performance;
                        }
                    },

                    // Update device frame
                    updateDeviceFrame: function() {
                        const deviceFrame = document.querySelector('.device-frame');
                        if (deviceFrame) {
                            deviceFrame.classList.remove('desktop-view', 'tablet-view', 'mobile-view');
                            deviceFrame.classList.add(`${this.currentDevice}-view`);
                        }
                    },

                    // Update content display
                    updateContentDisplay: function() {
                        const previewContent = document.getElementById('template-content-preview');
                        const codeContent = document.getElementById('template-content-code');

                        if (this.currentTab === 'preview') {
                            previewContent.classList.remove('hidden');
                            previewContent.setAttribute('data-state', 'active');
                            codeContent.classList.add('hidden');
                            codeContent.setAttribute('data-state', 'inactive');
                        } else {
                            previewContent.classList.add('hidden');
                            previewContent.setAttribute('data-state', 'inactive');
                            codeContent.classList.remove('hidden');
                            codeContent.setAttribute('data-state', 'active');
                        }
                    },

                    // Update template display
                    updateDisplay: function() {
                        // Update preview templates
                        const previewTemplates = document.querySelectorAll('.template-preview');
                        previewTemplates.forEach(preview => {
                            const template = preview.getAttribute('data-template');
                            if (template === this.currentTemplate) {
                                preview.classList.remove('hidden');
                                preview.classList.add('fade-in');
                            } else {
                                preview.classList.add('hidden');
                                preview.classList.remove('fade-in');
                            }
                        });

                        // Update code templates
                        const codeTemplates = document.querySelectorAll('.template-code');
                        codeTemplates.forEach(code => {
                            const template = code.getAttribute('data-template');
                            if (template === this.currentTemplate) {
                                code.classList.remove('hidden');
                            } else {
                                code.classList.add('hidden');
                            }
                        });

                        this.updateDeviceFrame();
                        this.updateContentDisplay();
                    }
                };

                // Initialize when DOM is ready
                TemplateGrid.init();

            })();
        </script>
        <div class="mt-8 text-center">
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="text-3xl font-bold text-blue-600 mb-2">0.3s</div>
                    <div class="text-sm text-slate-600"><?php _e('landing.metrics.avg_load_time') ?></div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="text-3xl font-bold text-green-600 mb-2">98/100</div>
                    <div class="text-sm text-slate-600"><?php _e('landing.metrics.pagespeed_score') ?></div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="text-3xl font-bold text-purple-600 mb-2">99.9%</div>
                    <div class="text-sm text-slate-600"><?php _e('landing.metrics.uptime_guarantee') ?></div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('download') ?>">
                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0  h-11 rounded-md px-8 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                        <?php _e('landing.actions.create_demo_site') ?>
                    </button>
                </a>
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 border-blue-600 text-blue-600 hover:bg-blue-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings mr-2">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z">
                        </path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <?php _e('landing.actions.customize_demo') ?>
                </button>
            </div>
        </div>

    </div>
</section>
