<!-- Multi Language Support -->
<section id="multi-language" class="py-16 md:py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <!-- <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600"><?php _e('Support') ?></span>
            </h2> -->
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">Multi Languages</span>
                <?php _e('Support') ?>
            </h2>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto"><?php _e('Easily build multilingual websites with CMS Full Form, reaching global audiences without technical complexity.') ?></p>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-center mb-12">
            <div class="flex order-1">
                <div class="rounded-lg bg-card text-card-foreground overflow-hidden border-0">
                    <div class="p-0">
                        <?= _img(
                            theme_assets('images/dangonngu.webp'),
                            'Multilang',
                            true,
                            'w-full h-auto object-cover drop-shadow-lg'
                        ) ?>
                    </div>
                </div>
            </div>
            <div class="order-1 md:order-2">
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <!-- Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-globe">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                                <path d="M2 12h20"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php _e('Unlimited Languages') ?></h3>
                            <p class="text-slate-600"><?php _e('Add and manage unlimited languages for your website. Fully supports all major global languages, including Vietnamese.') ?></p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                            <!-- Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-database">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                                <path d="M3 12A9 3 0 0 0 21 12"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php _e('Optimized Data Structure') ?></h3>
                            <p class="text-slate-600"><?php _e('Each language is stored in a <strong class="text-blue-600">dedicated data table</strong>, ensuring high performance and scalability.') ?></p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <!-- Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-import">
                                <path d="M12 3v12"></path>
                                <path d="m8 11 4 4 4-4"></path>
                                <path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php _e('Integrated Content Translation Tools') ?></h3>
                            <p class="text-slate-600"><?php _e('Built-in automated translation tools save time and effort when creating multilingual content.') ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="bg-white rounded-xl p-6 md:p-8 shadow-lg">
            <div class="flex items-center space-x-3 mb-6"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-table text-blue-600">
                    <path d="M12 3v18"></path>
                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                    <path d="M3 9h18"></path>
                    <path d="M3 15h18"></path>
                </svg>
                <h3 class="text-xl font-semibold text-slate-800">
                    <?php _e('Multilingual Data Structure') ?>
                </h3>
            </div>
            <div dir="ltr" data-orientation="horizontal" class="w-full">
                <!-- Tab Navigation -->
                <div role="tablist" aria-orientation="horizontal"
                    class="h-10 items-center justify-center rounded-md bg-slate-100 p-1 text-slate-600 grid w-full grid-cols-2 mb-6"
                    tabindex="0" data-orientation="horizontal" style="outline:none">

                    <button type="button" role="tab" aria-selected="true" aria-controls="content-structure"
                        data-state="active" id="trigger-structure"
                        class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-white data-[state=active]:text-slate-900 data-[state=active]:shadow-sm bg-white text-slate-900 shadow-sm"
                        tabindex="-1" data-orientation="horizontal" data-tab="structure">
                        <?php _e('Data Structure') ?>
                    </button>

                    <button type="button" role="tab" aria-selected="false" aria-controls="content-example"
                        data-state="inactive" id="trigger-example"
                        class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-white data-[state=active]:text-slate-900 data-[state=active]:shadow-sm"
                        tabindex="-1" data-orientation="horizontal" data-tab="example">
                        <?php _e('Real-World Example') ?>
                    </button>
                </div>

                <!-- Tab Content 1: Data structure -->
                <div data-state="active" data-orientation="horizontal" role="tabpanel"
                    aria-labelledby="trigger-structure" id="content-structure" tabindex="0"
                    class="tab-content active mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 overflow-x-auto">

                    <table class="min-w-full bg-white rounded-lg overflow-hidden shadow-sm">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    <?php _e('table_col_posttype') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    <?php _e('table_col_language') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    <?php _e('table_col_table') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">Post</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">English (US)</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_posts_en</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">Post</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">Vietnamese</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_posts_vi</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">Product</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">English (US)</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_products_en</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">Product</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">Vietnamese</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_products_vi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <!-- Tab Content 2: Real example -->
                <div data-state="inactive" data-orientation="horizontal" role="tabpanel"
                    aria-labelledby="trigger-example" id="content-example" tabindex="0"
                    class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 overflow-x-auto hidden">

                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <?php _e('example_table_col_id') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <?php _e('example_table_col_title_en') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <?php _e('table_col_table') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <?php _e('example_table_col_id') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <?php _e('example_table_col_title_vi') ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <?php _e('table_col_table') ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php _e('example_about_us') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_posts_en</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php _e('example_ve_chung_toi') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_posts_vi</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php _e('example_our_services') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_posts_en</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php _e('example_dich_vu_cua_chung_toi') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">fast_posts_vi</td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>


            <div class="mt-6 text-sm text-slate-500 flex items-center"><svg xmlns="http://www.w3.org/2000/svg"
                    width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"
                    class="hidden md:block lucide lucide-languages mr-2 text-slate-400">
                    <path d="m5 8 6 6"></path>
                    <path d="m4 14 6-6 2-3"></path>
                    <path d="M2 5h12"></path>
                    <path d="M7 2h1"></path>
                    <path d="m22 22-5-10-5 10"></path>
                    <path d="M14 18h6"></path>
                </svg><span><?php _e('note_multilanguage_structure') ?>
                </span></div>
        </div>
        <div class="mt-10 text-center">
            <a href="<?= base_url('download') ?>">
                
                <button aria-label="<?php _e('Add New Language') ?>" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  h-10 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white"><svg
                        xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-plus mr-2">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg> <?php _e('Add New Language') ?></button></a>

        </div>
    </div>
</section>
