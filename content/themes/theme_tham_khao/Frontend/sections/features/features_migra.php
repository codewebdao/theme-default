<section class="py-12 md:py-16 lg:py-24 bg-gradient-to-br from-green-50 to-emerald-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 md:mb-16">
            <h2 class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold text-slate-800 mb-4 leading-tight">
                <?= _e('migration.title.before') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-emerald-600">
                    <?= _e('migration.title.highlight') ?>
                </span>
            </h2>
            <p class="text-base md:text-lg lg:text-xl text-slate-600 max-w-3xl mx-auto px-4">
                <?= _e('migration.description') ?>
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <!-- Left Column - Sources & Steps -->
            <div class="space-y-6 lg:space-y-8">
                <div>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-4 md:mb-6">
                        <?= _e('migration.supported_sources.title') ?>
                    </h3>

                    <!-- Sources Grid - Responsive -->
                    <div class="grid grid-cols-2 gap-3 md:gap-4">
                        <!-- WordPress -->
                        <div class="rounded-lg bg-white shadow-lg border border-green-200 hover:shadow-xl transition-shadow">
                            <div class="p-3 md:p-4">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <?= _img(
                                            theme_assets('images/WordPress.com-Logo.wine.png'),
                                            'WordPress',
                                            true,
                                            'w-5 h-5 md:w-6 md:h-6'
                                        ) ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-sm md:text-base truncate">
                                            <?= _e('migration.source.wordpress.name') ?>
                                        </div>
                                        <div class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800 border-green-200 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="hidden sm:inline"><?= _e('migration.tool.support') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Joomla -->
                        <div class="rounded-lg bg-white shadow-lg border border-green-200 hover:shadow-xl transition-shadow">
                            <div class="p-3 md:p-4">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <?= _img(
                                            theme_assets('images/joomla-svgrepo-com.png'),
                                            'Joomla',
                                            true,
                                            'w-5 h-5 md:w-6 md:h-6'
                                        ) ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-sm md:text-base truncate">
                                            <?= _e('migration.source.joomla.name') ?>
                                        </div>
                                        <div class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800 border-red-200 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span class="hidden sm:inline"><?= _e('migration.tool.unsupport') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drupal -->
                        <div class="rounded-lg bg-white shadow-lg border border-green-200 hover:shadow-xl transition-shadow">
                            <div class="p-3 md:p-4">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <?= _img(
                                            theme_assets('images/drupal-4.png'),
                                            'Drupal',
                                            true,
                                            'w-5 h-5 md:w-6 md:h-6'
                                        ) ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-sm md:text-base truncate">
                                            <?= _e('migration.source.drupal.name') ?>
                                        </div>
                                        <div class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800 border-red-200 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span class="hidden sm:inline"><?= _e('migration.tool.unsupport') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Magento -->
                        <div class="rounded-lg bg-white shadow-lg border border-green-200 hover:shadow-xl transition-shadow">
                            <div class="p-3 md:p-4">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <?= _img(
                                            theme_assets('images/magento.png'),
                                            'Magento',
                                            true,
                                            'w-5 h-5 md:w-6 md:h-6'
                                        ) ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-sm md:text-base truncate">
                                            <?= _e('migration.source.magento.name') ?>
                                        </div>
                                        <div class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800 border-red-200 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span class="hidden sm:inline"><?= _e('migration.tool.unsupport') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Migration Steps -->
                <div class="rounded-lg bg-white shadow-lg border border-green-200">
                    <div class="p-4 md:p-6">
                        <h4 class="font-semibold text-slate-800 mb-4 text-base md:text-lg">
                            <?= _e('migration.steps.title') ?>
                        </h4>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5">
                                    1
                                </div>
                                <span class="text-sm md:text-base leading-relaxed"><?= _e('migration.step.1') ?></span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5">
                                    2
                                </div>
                                <span class="text-sm md:text-base leading-relaxed"><?= _e('migration.step.2') ?></span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5">
                                    3
                                </div>
                                <span class="text-sm md:text-base leading-relaxed"><?= _e('migration.step.3') ?></span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5">
                                    4
                                </div>
                                <span class="text-sm md:text-base leading-relaxed"><?= _e('migration.step.4') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Migration Tool -->
            <div class="rounded-lg bg-white shadow-xl border-0">
                <div class="p-4 md:p-6 lg:p-8">
                    <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-4 md:mb-6">
                        <?= _e('migration.tool.title') ?>
                    </h3>

                    <div class="space-y-4 md:space-y-6">
                        <!-- Source Selection -->
                        <div class="border border-slate-200 rounded-lg p-3 md:p-4">
                            <div class="space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <span class="font-medium text-sm md:text-base"><?= _e('migration.tool.source_label') ?></span>
                                    <select class="border border-slate-300 rounded px-3 py-2 text-sm w-full sm:w-auto min-w-0 sm:min-w-[160px]">
                                        <option><?= _e('migration.source.wordpress.name') ?></option>
                                        <option><?= _e('migration.source.joomla.name') ?></option>
                                        <option><?= _e('migration.source.drupal.name') ?></option>
                                    </select>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <span class="font-medium text-sm md:text-base"><?= _e('migration.tool.database_url') ?></span>
                                    <input placeholder="<?= _e('migration.tool.database_placeholder') ?>"
                                        class="border border-slate-300 rounded px-3 py-2 text-sm w-full sm:w-auto min-w-0 sm:min-w-[200px]" type="text">
                                </div>
                            </div>
                        </div>

                        <!-- Migration Options -->
                        <div class="border border-slate-200 rounded-lg p-3 md:p-4">
                            <h4 class="font-medium mb-3 text-sm md:text-base"><?= _e('migration.tool.options.title') ?></h4>
                            <div class="space-y-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input class="rounded" type="checkbox" checked>
                                    <span class="text-sm md:text-base"><?= _e('migration.tool.option.posts_pages') ?></span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input class="rounded" type="checkbox" checked>
                                    <span class="text-sm md:text-base"><?= _e('migration.tool.option.media') ?></span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input class="rounded" type="checkbox" checked>
                                    <span class="text-sm md:text-base"><?= _e('migration.tool.option.users_permissions') ?></span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input class="rounded" type="checkbox">
                                    <span class="text-sm md:text-base"><?= _e('migration.tool.option.comments') ?></span>
                                </label>
                            </div>
                        </div>

                        <!-- Start Button -->
                        <button class="w-full bg-gradient-to-r from-green-600 to-emerald-700 text-white py-3 px-4 rounded-md font-semibold hover:from-green-700 hover:to-emerald-800 transition-all duration-200 flex items-center justify-center gap-2 text-sm md:text-base">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-play">
                                <polygon points="6 3 20 12 6 21 6 3"></polygon>
                            </svg>
                            <?= _e('migration.tool.start_button') ?>
                        </button>

                        <!-- Safety Notice -->
                        <div class="p-3 md:p-4 bg-green-50 rounded-lg">
                            <div class="flex items-start space-x-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-shield-check text-green-600 flex-shrink-0 mt-0.5">
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                                <div>
                                    <p class="text-sm md:text-base text-green-800 font-medium"><?= _e('migration.safety.title') ?></p>
                                    <p class="text-sm text-green-700 mt-1"><?= _e('migration.safety.description') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
