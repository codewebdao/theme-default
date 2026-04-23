<!-- FAQ Section -->
<section id="faq" class="py-16 md:py-24 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?= _e('faq.heading.part1') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                    <?= _e('faq.heading.highlight') ?>
                </span>
            </h2>
            <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto">
                <?= _e('faq.subheading') ?>
            </p>
        </div>

        <!-- FAQ Grid with items-start to avoid stretch -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
           
            <!-- FAQ Item 1 -->
            <div class="faq-item bg-gradient-to-r from-white to-blue-50 rounded-2xl border border-blue-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group rounded-t-2xl" data-faq="1">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-blue-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q1.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="1">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q1.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 2 -->
            <div class="faq-item bg-gradient-to-r from-white to-purple-50 rounded-2xl border border-purple-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group rounded-t-2xl" data-faq="2">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-purple-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q2.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="2">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q2.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 3 -->
            <div class="faq-item bg-gradient-to-r from-white to-green-50 rounded-2xl border border-green-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group rounded-t-2xl" data-faq="3">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-green-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q3.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="3">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q3.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 4 -->
            <div class="faq-item bg-gradient-to-r from-white to-orange-50 rounded-2xl border border-orange-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group rounded-t-2xl" data-faq="4">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-orange-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q4.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="4">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q4.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 5 -->
            <div class="faq-item bg-gradient-to-r from-white to-cyan-50 rounded-2xl border border-cyan-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group rounded-t-2xl" data-faq="5">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-cyan-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q5.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="5">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q5.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 6 -->
            <div class="faq-item bg-gradient-to-r from-white to-indigo-50 rounded-2xl border border-indigo-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group focus:outline-none rounded-t-2xl" data-faq="6">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q6.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="6">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q6.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 7 -->
            <div class="faq-item bg-gradient-to-r from-white to-pink-50 rounded-2xl border border-pink-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group  rounded-t-2xl" data-faq="7">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-pink-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q7.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-pink-500 to-rose-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="7">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q7.answer') ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Item 8 -->
            <div class="faq-item bg-gradient-to-r from-white to-emerald-50 rounded-2xl border border-emerald-100 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] overflow-hidden self-start">
                <button class="faq-question w-full px-8 py-6 text-left flex justify-between items-center group rounded-t-2xl" data-faq="8">
                    <span class="text-lg font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors duration-200 pr-4 leading-relaxed">
                        <?= _e('faq.q8.question') ?>
                    </span>
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full flex items-center justify-center shadow-md transition-all duration-300">
                        <svg class="faq-icon w-5 h-5 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 ease-in-out" data-answer="8">
                    <div class="p-6 text-slate-600 leading-relaxed text-base">
                        <?= _e('faq.q8.answer') ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

