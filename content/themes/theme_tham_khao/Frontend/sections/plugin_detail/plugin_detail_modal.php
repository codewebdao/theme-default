<?php
/**
 * Plugin Detail Image Modal Component
 * Enhanced image modal with zoom functionality
 */
?>

<!-- Enhanced Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden bg-transparent backdrop-blur-md transition-all duration-300">
    <!-- Modal Overlay -->
    <div class="absolute inset-0 bg-transparent" onclick="closeImageModal()"></div>
    
    <!-- Close Button - Top Right -->
    <button onclick="closeImageModal()" 
            class="fixed top-4 right-4 z-20 w-14 h-14 bg-black/80 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/90 transition-all duration-200 shadow-xl border border-white/10">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    
    <!-- Modal Content - Centered Container -->
    <div class="flex items-center justify-center min-h-screen p-4 sm:p-8">
        <!-- Image Container -->
        <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden max-w-[90vw] max-h-[85vh]">
            <!-- Image -->
            <div class="relative flex items-center justify-center bg-gray-100">
                <img id="modalImage" 
                     src="" 
                     alt="" 
                     class="block object-contain"
                     style="max-width: 85vw; max-height: 80vh;">
            </div>
        </div>
    </div>
</div>