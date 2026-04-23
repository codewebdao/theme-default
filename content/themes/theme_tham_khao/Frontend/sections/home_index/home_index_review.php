<?php
$review = $review ?? [];
?>

<!-- What do our customers say about us? -->
<section id="testimonials" class="py-16 md:py-24 bg-gradient-to-br from-slate-50 to-indigo-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('testimonial_section.title_part1') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                    <?php _e('testimonial_section.title_highlight') ?>
                </span>
                <?php _e('testimonial_section.title_part2') ?>
            </h2>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-2xl mx-auto">
                <?php _e('testimonial_section.description') ?>
            </p>
        </div>

        <!-- Review Slider Container -->
        <div class="blaze-slider reviews-slider">
            <div class="blaze-container">
                <div class="blaze-track-container">
                    <div class="blaze-track">
                        <?php
                        $bgGradients = [
                            'bg-gradient-to-br from-purple-400 via-pink-500 to-rose-500',
                            'bg-gradient-to-br from-green-400 via-emerald-500 to-teal-600',
                            'bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600',
                            'bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500',
                            'bg-gradient-to-br from-cyan-400 via-teal-500 to-green-500',
                            'bg-gradient-to-br from-orange-400 via-red-500 to-pink-500',
                            'bg-gradient-to-br from-violet-400 via-indigo-500 to-blue-600',
                            'bg-gradient-to-br from-emerald-400 via-green-500 to-cyan-500',
                            'bg-gradient-to-br from-indigo-400 via-purple-500 to-pink-600',
                            'bg-gradient-to-br from-teal-400 via-cyan-500 to-blue-500'
                        ];

                        $colorIndex = 0;
                        $step = 1;
                        $max = count($bgGradients) - 1;
                        ?>

                        <?php foreach (($reviews ?? []) as $review): ?>
                            <?php
                            $bgClass = $bgGradients[$colorIndex];

                            // increase/decrease color index
                            $colorIndex += $step;
                            if ($colorIndex === $max || $colorIndex === 0) {
                                $step *= -1;
                            }
                            ?>
                            <div>
                                <div class="border shadow-xl hover:shadow-xl transition-all duration-300 rounded-xl overflow-hidden transform hover:-translate-y-1 flex flex-col text-white mx-2 <?= $bgClass ?>">
                                    <div class="flex flex-col space-y-1.5 p-6">
                                        <div class="flex items-center space-x-4">
                                            <?= _img(theme_assets('images/avatar.png'), $review['author'], true, 'w-16 h-16 rounded-full border-2 border-white/50 shadow-md') ?>
                                            <div>
                                                <p class="text-lg font-semibold"><?= $review['author'] ?></p>
                                                <p class="text-sm opacity-80"><?= $review['position'] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6 flex-grow min-h-32">
                                        <p class="text-base italic leading-relaxed line-clamp-3">"<?= $review['content'] ?>"</p>
                                    </div>
                                    <div class="flex items-center p-6 border-t border-white/20">
                                        <div class="flex text-yellow-300">
                                            <?php for ($j = 0; $j < $review['rating_avg']; $j++): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star text-yellow-500">
                                                    <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="controls">
                <button class="blaze-prev" aria-label="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                        <path d="M0 0h24v24H0V0z" fill="none"></path>
                        <path d="M6.23 20.23 8 22l10-10L8 2 6.23 3.77 14.46 12z"></path>
                    </svg>
                </button>
                <div class="blaze-pagination"></div>
                <button class="blaze-next" aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                        <path d="M0 0h24v24H0V0z" fill="none"></path>
                        <path d="M6.23 20.23 8 22l10-10L8 2 6.23 3.77 14.46 12z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>
