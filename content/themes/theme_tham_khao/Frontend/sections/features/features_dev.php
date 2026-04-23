 <section class="py-16 md:py-24 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                        <?= _e('dev_tools.title.before') ?>
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-cyan-600 to-blue-600">
                            <?= _e('dev_tools.title.highlight') ?>
                        </span>
                    </h2>
                    <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                        <?= _e('dev_tools.description') ?>
                    </p>

                </div>
                <div class="grid lg:grid-cols-3 gap-8">
                    <div
                        class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-cyan-50 to-blue-100 border border-cyan-200">
                        <div
    class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-cyan-50 to-blue-100 border border-cyan-200">
    <div class="p-8">
        <div class="w-16 h-16 bg-cyan-600 rounded-xl flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-database text-white">
                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                <path d="M3 12A9 3 0 0 0 21 12"></path>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-4">
            <?= _e('dev_tools.rest_api.title') ?>
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.rest_api.feature.1') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.rest_api.feature.2') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.rest_api.feature.3') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.rest_api.feature.4') ?></span>
            </div>
        </div>
        <div class="mt-6 bg-slate-900 rounded-lg p-4">
            <pre class="text-green-400 text-xs"><code>GET /api/posts
POST /api/posts
PUT /api/posts/{id}
DELETE /api/posts/{id}</code></pre>
        </div>
    </div>
</div>

                    </div>
                    <div
    class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-violet-50 to-purple-100 border border-violet-200">
    <div class="p-8">
        <div class="w-16 h-16 bg-violet-600 rounded-xl flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-route text-white">
                <circle cx="6" cy="19" r="3"></circle>
                <path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"></path>
                <circle cx="18" cy="5" r="3"></circle>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-4"><?= _e('dev_tools.router.title') ?></h3>
        <div class="space-y-3 text-sm">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.router.feature.1') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.router.feature.2') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.router.feature.3') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.router.feature.4') ?></span>
            </div>
        </div>
        <div class="mt-6 bg-slate-900 rounded-lg p-4">
            <pre class="text-green-400 text-xs"><code>/blogs/{slug}
/products/{category}/{slug}
/custom-page/{param}</code></pre>
        </div>
    </div>
</div>

                    <div
    class="rounded-lg bg-card text-card-foreground shadow-sm bg-gradient-to-br from-emerald-50 to-teal-100 border border-emerald-200">
    <div class="p-8">
        <div class="w-16 h-16 bg-emerald-600 rounded-xl flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-settings text-white">
                <path
                    d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z">
                </path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-4"><?= _e('dev_tools.custom_options.title') ?></h3>
        <div class="space-y-3 text-sm">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.custom_options.feature.1') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.custom_options.feature.2') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.custom_options.feature.3') ?></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-check text-green-600">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
                <span><?= _e('dev_tools.custom_options.feature.4') ?></span>
            </div>
        </div>
        <div class="mt-6 bg-slate-900 rounded-lg p-4">
            <pre class="text-green-400 text-xs"><code><?= _e('dev_tools.custom_options.example.title') ?>

Site Settings
Theme Options
Custom Fields
Widget Areas</code></pre>
        </div>
    </div>
</div>

                </div>
                <div
    class="rounded-lg bg-card text-card-foreground shadow-sm mt-12 bg-gradient-to-br from-teal-50 to-cyan-100 border border-teal-200">
    <div class="p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-teal-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-folder text-white">
                    <path
                        d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z">
                    </path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2"><?= _e('dev_tools.file_manager.title') ?></h3>
            <p class="text-slate-600"><?= _e('dev_tools.file_manager.description') ?></p>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h4 class="text-lg font-semibold text-slate-800 mb-4"><?= _e('dev_tools.file_manager.features.title') ?></h4>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-teal-600 rounded-full"></div><span><?= _e('dev_tools.file_manager.feature.1') ?></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-teal-600 rounded-full"></div><span><?= _e('dev_tools.file_manager.feature.2') ?></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-teal-600 rounded-full"></div><span><?= _e('dev_tools.file_manager.feature.3') ?></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-teal-600 rounded-full"></div><span><?= _e('dev_tools.file_manager.feature.4') ?></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-teal-600 rounded-full"></div><span><?= _e('dev_tools.file_manager.feature.5') ?></span>
                    </div>
                </div>
            </div>
            <div class="rounded-lg border text-card-foreground shadow-sm bg-white border-slate-200">
                <div class="p-4">
                    <div class="text-xs text-slate-500 mb-2"><?= _e('dev_tools.file_manager.interface.title') ?></div>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3 p-2 bg-slate-50 rounded">
                            <div class="w-4 h-4 bg-slate-300 rounded"></div><span
                                class="text-sm flex-1">hero-image.jpg</span><span
                                class="text-xs text-green-600"><?= _e('dev_tools.file_manager.file.1.status') ?></span>
                        </div>
                        <div class="flex items-center space-x-3 p-2 bg-slate-50 rounded">
                            <div class="w-4 h-4 bg-slate-300 rounded"></div><span
                                class="text-sm flex-1">document.pdf</span><span
                                class="text-xs text-blue-600"><?= _e('dev_tools.file_manager.file.2.status') ?></span>
                        </div>
                        <div class="flex items-center space-x-3 p-2 bg-slate-50 rounded">
                            <div class="w-4 h-4 bg-slate-300 rounded"></div><span
                                class="text-sm flex-1">demo-video.mp4</span><span
                                class="text-xs text-yellow-600"><?= _e('dev_tools.file_manager.file.3.status') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            </div>
        </section>