<section class="bg-white py-20 md:py-28">
    <div class="container mx-auto px-4">
        <!-- Title Section -->
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600"><?php _e('Smart') ?></span>
                <?php _e('File Management') ?>
            </h2>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('A modern AI-integrated file management system, enabling efficient organization and retrieval of files.') ?>
            </p>
        </div>

        <!-- Content Grid -->
        <div class="grid xl:grid-cols-10 gap-12 items-center">
            <!-- Right Content - Image --> <!-- Right Image -->
            <div class="xl:col-span-6 relative">
                <div class="relative">
                    <?= _img(
                        theme_assets('images/filemanager.webp'),
                        'File Manager Interface',
                        true,
                        'w-full h-auto rounded-2xl shadow-2xl'
                    ) ?>

                    <!-- Optional: Floating badge -->
                    <div class="absolute -top-4 -right-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                       Auto Optimize, Crop
                    </div>
                </div>
            </div>

            <!-- Left Content - Features -->
            <div class="xl:col-span-4 space-y-8">
                <!-- Features List -->
                <div class="space-y-2">
                    <!-- Feature 1 -->
                    <div class="flex items-start space-x-4 group hover:bg-slate-50 p-4 rounded-xl transition-all duration-300">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-800 mb-2"><?php _e('Drag-and-Drop Uploads with Fast Multi-Format Support') ?></h3>
                            <p class="text-slate-600"><?php _e('Easily drag and drop files with high-speed simultaneous uploads. Supports all popular file formats.') ?></p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <!-- <div class="flex items-start space-x-4 group hover:bg-slate-50 p-4 rounded-xl transition-all duration-300">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-800 mb-2"><?php _e('AI-Powered Auto-Categorization & Usage Suggestions') ?></h3>
                            <p class="text-slate-600"><?php _e('Intelligent AI automatically categorizes files by content, suggests tags, and recommends optimal usage for each file type.') ?></p>
                        </div>
                    </div> -->

                    <!-- Feature 3 -->
                    <div class="flex items-start space-x-4 group hover:bg-slate-50 p-4 rounded-xl transition-all duration-300">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-800 mb-2"><?php _e('Instant Search & Advanced Filtering') ?></h3>
                            <p class="text-slate-600"><?php _e('feature_3_description') ?></p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="flex items-start space-x-4 group hover:bg-slate-50 p-4 rounded-xl transition-all duration-300">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-800 mb-2"><?php _e('feature_4_title') ?></h3>
                            <p class="text-slate-600"><?php _e('feature_4_description') ?></p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="flex items-start space-x-4 group hover:bg-slate-50 p-4 rounded-xl transition-all duration-300">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-teal-400 to-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-800 mb-2"><?php _e('feature_5_title') ?></h3>
                            <p class="text-slate-600"><?php _e('feature_5_description') ?></p>
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="pt-6">
                    <a href="<?= base_url('download') ?>">
                        <button aria-label="<?php _e('Experience Now') ?>" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <?php _e('Experience Now') ?>
                        </button>
                    </a>
                </div>
            </div>


        </div>
    </div>
</section>
