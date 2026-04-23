<section class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-700 to-indigo-800 text-white relative overflow-hidden flex items-center">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-20 h-20 bg-yellow-400/20 rounded-full animate-pulse"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-pink-400/20 rounded-full animate-pulse"
            style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-green-400/20 rounded-full animate-pulse"
            style="animation-delay: 4s;"></div>
    </div>
    
    <!-- Main Content Container - Centered -->
    <div class="relative z-10 w-full py-20">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto space-y-8">
                
                <!-- Icon -->
                <div class="flex justify-center">
                    <div class="p-4 bg-white/20 rounded-full backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-palette text-yellow-300">
                            <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                            <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                            <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Title -->
                <h1 class="text-4xl lg:text-6xl font-bold leading-tight">
                    <?= _e('library.title.before') ?>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 to-orange-400">
                        <?= _e('library.title.highlight') ?>
                    </span><br><?= _e('library.title.sub') ?>
                </h1>

                <!-- Description -->
                <p class="text-xl lg:text-2xl text-purple-100 leading-relaxed max-w-3xl mx-auto">
                    <?= _e('library.description') ?>
                </p>

                <!-- Buttons -->
                <div class="flex flex-col md:flex-row justify-center items-center gap-4 sm:gap-6">
                    <a href="#themes" class="w-full sm:w-auto smooth-scroll">
                        <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-12 px-8 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                            <?= _e('library.button.browse_themes') ?>
                        </button>
                    </a>

                    <a href="#plugins" class="w-full sm:w-auto smooth-scroll">
                        <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-12 px-8 bg-transparent border-2 border-white text-white hover:bg-white hover:text-slate-900 font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg>
                            <?= _e('library.button.top_plugins') ?>
                        </button>
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto pt-4">
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">1,500+</div>
                        <div class="text-sm text-purple-200 mt-1"><?= _e('library.stats.themes') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">800+</div>
                        <div class="text-sm text-purple-200 mt-1"><?= _e('library.stats.plugins') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">200+</div>
                        <div class="text-sm text-purple-200 mt-1"><?= _e('library.stats.extensions') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">50K+</div>
                        <div class="text-sm text-purple-200 mt-1"><?= _e('library.stats.downloads') ?></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
// Smooth scroll với hiệu ứng mượt mà
document.addEventListener('DOMContentLoaded', function() {
    // Thêm smooth scroll behavior cho toàn bộ trang
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Xử lý click cho các link smooth-scroll
    document.querySelectorAll('.smooth-scroll').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Tính toán offset để scroll chính xác
                const headerOffset = 80; // Khoảng cách từ header nếu có
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                // Smooth scroll với animation tùy chỉnh
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Thêm hiệu ứng highlight cho target element
                targetElement.style.transform = 'scale(1.02)';
                targetElement.style.transition = 'transform 0.3s ease';
                
                setTimeout(() => {
                    targetElement.style.transform = 'scale(1)';
                }, 300);
            }
        });
    });
});
</script>
