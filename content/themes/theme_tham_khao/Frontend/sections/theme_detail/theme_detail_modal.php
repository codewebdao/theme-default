<?php

/**
 * Theme Detail Modal Section
 * Displays image modal for screenshots
 */

use App\Libraries\Fastlang;
?>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl w-full">
            <!-- Close Button -->
            <button type="button" onclick="closeImageModal()" class="absolute top-4 right-4 z-10 bg-white/90 backdrop-blur-sm rounded-full p-2 hover:bg-white transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Navigation Buttons -->
            <button type="button" onclick="previousImage()" class="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 bg-white/90 backdrop-blur-sm rounded-full p-3 hover:bg-white transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button type="button" onclick="nextImage()" class="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 bg-white/90 backdrop-blur-sm rounded-full p-3 hover:bg-white transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Image Container -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
                <img id="modalImage" src="" alt="" class="w-full h-auto max-h-[80vh] object-contain">
                <div class="p-6">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-2"></h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span id="modalCounter"></span>
                        <div class="flex items-center space-x-4">
                            <button type="button" onclick="previousImage()" class="flex items-center space-x-1 hover:text-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span><?= Fastlang::__('theme_detail.modal.previous') ?></span>
                            </button>
                            <button type="button" onclick="nextImage()" class="flex items-center space-x-1 hover:text-gray-700 transition-colors">
                                <span><?= Fastlang::__('theme_detail.modal.next') ?></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
