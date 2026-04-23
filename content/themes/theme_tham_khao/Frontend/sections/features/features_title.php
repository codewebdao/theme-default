<section class="min-h-screen bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 text-white relative overflow-hidden flex items-center">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-20 h-20 bg-yellow-400/20 rounded-full animate-pulse"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-pink-400/20 rounded-full animate-pulse"
            style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-green-400/20 rounded-full animate-pulse"
            style="animation-delay: 4s;"></div>
    </div>

    <!-- Main Content Container - Centered -->
    <div class="relative z-10 w-full py-20 md:py-28">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">

                <div class="flex justify-center mb-6">
                    <div class="p-4 bg-white/20 rounded-full backdrop-blur-sm">
                        <!-- 3D Star with shadow -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-300">
                            <defs>
                                <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                                    <feDropShadow dx="2" dy="2" stdDeviation="2" flood-color="#000" flood-opacity="0.3" />
                                </filter>
                            </defs>
                            <polygon points="12,2 15.09,8.26 22,9 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9 8.91,8.26"
                                fill="currentColor" filter="url(#shadow)"></polygon>
                            <polygon points="12,3 14.5,8 20,8.5 16,12.5 17,18 12,15.5 7,18 8,12.5 4,8.5 9.5,8"
                                fill="#fff" opacity="0.3"></polygon>
                        </svg>
                    </div>
                </div>

                <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                    <?= _e('feature.hero.title.part1') ?>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 to-orange-400">
                        <?= _e('feature.hero.title.highlight') ?>
                    </span><br><?= _e('feature.hero.title.part2') ?>
                </h1>

                <p class="text-xl lg:text-2xl text-blue-100 mb-8 leading-relaxed"><?= _e('feature.hero.subtitle') ?></p>

                <div class="flex flex-col sm:flex-row justify-center items-center gap-2 mb-12">
                    <a href="<?= base_url('features') ?>" class="w-full sm:w-auto">
                        <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-11 rounded-md px-8 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-play mr-2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="10 8 16 12 10 16 10 8"></polygon>
                            </svg>
                            <?= _e('feature.hero.button.demo') ?>
                        </button>
                    </a>

                    <a href="<?= download_url(); ?>" class="w-full sm:w-auto">
                        <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-transparent h-11 rounded-md px-8 border-2 border-white text-white hover:bg-white hover:text-slate-900 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download mr-2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                            <?= _e('feature.hero.button.download') ?>
                        </button>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300"><?= _e('feature.stats.themes') ?>: 1,500+</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300"><?= _e('feature.stats.plugins') ?>: 800+</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300"><?= _e('feature.stats.extensions') ?>: 200+</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300"><?= _e('feature.stats.downloads') ?>: 50K+</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
