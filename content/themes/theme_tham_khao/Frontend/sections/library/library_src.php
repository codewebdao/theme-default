<section class="py-16 md:py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('dev_resources.title.before') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-teal-600">
                    <?php _e('dev_resources.title.highlight') ?>
                </span>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('dev_resources.description') ?>
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- Documentation -->
            <div class="rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-blue-200 group">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book text-white">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20">
                            </path>
                        </svg></div>
                    <div class="tracking-tight text-xl font-bold text-slate-800">
                        <?php _e('dev_resources.doc.title') ?>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6"><?php _e('dev_resources.doc.description') ?></p>
                    <a href="<?= docs_url() ?>">
                        <button class="text-blue-600 hover:text-blue-700 font-semibold underline hover:translate-x-1 transition-all duration-300">
                            <?php _e('dev_resources.doc.cta') ?>
                        </button></a>
                </div>
            </div>

            <!-- API Reference -->
            <div class="rounded-lg bg-gradient-to-br from-green-50 to-emerald-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-green-200 group">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code text-white">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg></div>
                    <div class="tracking-tight text-xl font-bold text-slate-800">
                        <?php _e('dev_resources.api.title') ?>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6"><?php _e('dev_resources.api.description') ?></p>
                    <a href="#">
                        <button class="text-blue-600 hover:text-blue-700 font-semibold underline hover:translate-x-1 transition-all duration-300">
                            <?php _e('dev_resources.api.cta') ?>
                        </button>
                    </a>
                </div>
            </div>

            <!-- Video Tutorials -->
            <div class="rounded-lg bg-gradient-to-br from-purple-50 to-violet-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-purple-200 group">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-video text-white">
                            <path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.87a.5.5 0 0 0-.752-.432L16 10.5">
                            </path>
                            <rect x="2" y="6" width="14" height="12" rx="2"></rect>
                        </svg></div>
                    <div class="tracking-tight text-xl font-bold text-slate-800">
                        <?php _e('dev_resources.video.title') ?>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6"><?php _e('dev_resources.video.description') ?></p>
                    <a href="#">
                        <button class="text-blue-600 hover:text-blue-700 font-semibold underline hover:translate-x-1 transition-all duration-300">
                            <?php _e('dev_resources.video.cta') ?>
                        </button></a>
                </div>
            </div>

            <!-- Community Forum -->
            <div class="rounded-lg bg-gradient-to-br from-orange-50 to-amber-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-orange-200 group">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="w-16 h-16 bg-orange-600 rounded-xl flex items-center justify-center mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users text-white">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg></div>
                    <div class="tracking-tight text-xl font-bold text-slate-800">
                        <?php _e('dev_resources.community.title') ?>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6"><?php _e('dev_resources.community.description') ?></p>
                    <a href="#">
                        <button class="text-blue-600 hover:text-blue-700 font-semibold underline hover:translate-x-1 transition-all duration-300">
                            <?php _e('dev_resources.community.cta') ?>
                        </button></a>
                </div>
            </div>

            <!-- Code Examples -->
            <div class="rounded-lg bg-gradient-to-br from-teal-50 to-cyan-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-teal-200 group">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="w-16 h-16 bg-teal-600 rounded-xl flex items-center justify-center mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-white">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg></div>
                    <div class="tracking-tight text-xl font-bold text-slate-800">
                        <?php _e('dev_resources.examples.title') ?>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6"><?php _e('dev_resources.examples.description') ?></p>
                    <a href="#">
                        <button class="text-blue-600 hover:text-blue-700 font-semibold underline hover:translate-x-1 transition-all duration-300">
                            <?php _e('dev_resources.examples.cta') ?>
                        </button></a>
                </div>
            </div>

            <!-- Developer Support -->
            <div class="rounded-lg bg-gradient-to-br from-pink-50 to-rose-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-pink-200 group">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="w-16 h-16 bg-pink-600 rounded-xl flex items-center justify-center mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-headphones text-white">
                            <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3">
                            </path>
                        </svg></div>
                    <div class="tracking-tight text-xl font-bold text-slate-800">
                        <?php _e('dev_resources.support.title') ?>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6"><?php _e('dev_resources.support.description') ?></p>
                    <a href="#">
                        <button class="text-blue-600 hover:text-blue-700 font-semibold underline hover:translate-x-1 transition-all duration-300">
                            <?php _e('dev_resources.support.cta') ?>
                        </button></a>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-16 bg-gradient-to-br from-white to-slate-50 rounded-2xl p-8 border border-slate-200">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-slate-800 mb-4"><?php _e('dev_resources.cta.title') ?></h3>
                <p class="text-slate-600 mb-6 max-w-2xl mx-auto"><?php _e('dev_resources.cta.description') ?></p>
                <div class="flex flex-col md:flex-row justify-center gap-2">
                    <a href="#">
                        <button class="bg-gradient-to-r from-green-600 to-teal-700 text-white rounded-md px-8 py-2">
                            <?php _e('dev_resources.cta.sdk_button') ?>
                        </button>
                    </a>
                    <a href="<?= docs_url('') ?>">
                        <button class="border border-green-600 text-green-600 hover:bg-green-50 rounded-md px-8 py-2">
                            <?php _e('dev_resources.cta.guide_button') ?>
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
