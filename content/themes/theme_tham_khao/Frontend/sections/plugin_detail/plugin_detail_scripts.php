<?php

/**
 * Plugin Detail Scripts Section
 * JavaScript functionality for image modal with zoom and drag
 */
?>

<script>
    let currentZoom = 1;
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let translateX = 0;
    let translateY = 0;
    let minZoom = 0.5;
    let maxZoom = 5;
    let isTransitioning = false;

    function openImageModal(imageSrc, imageTitle = '') {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        // Reset zoom and position
        currentZoom = 1;
        translateX = 0;
        translateY = 0;
        isTransitioning = false;

        // Load image
        modalImage.onload = function() {
            applyTransform();
            updateCursor();
        };

        modalImage.src = imageSrc;
        modalImage.alt = imageTitle;

        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Add escape key listener
        document.addEventListener('keydown', handleKeyDown);
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Remove event listeners
        document.removeEventListener('keydown', handleKeyDown);
    }

    function zoomImage(delta, centerX = null, centerY = null) {
        if (isTransitioning) return;

        const modalImage = document.getElementById('modalImage');
        const rect = modalImage.getBoundingClientRect();

        // Calculate zoom center point
        const zoomCenterX = centerX !== null ? centerX - rect.left - rect.width / 2 : 0;
        const zoomCenterY = centerY !== null ? centerY - rect.top - rect.height / 2 : 0;

        const oldZoom = currentZoom;
        currentZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom + delta));

        if (currentZoom !== oldZoom) {
            // Adjust translation to zoom towards the specified point
            if (centerX !== null && centerY !== null) {
                const zoomRatio = currentZoom / oldZoom;
                translateX = translateX * zoomRatio + zoomCenterX * (1 - zoomRatio);
                translateY = translateY * zoomRatio + zoomCenterY * (1 - zoomRatio);
            }

            // Apply bounds to prevent image from going too far off screen
            constrainTranslation();
            applyTransform();
            updateCursor();
        }
    }

    function constrainTranslation() {
        if (currentZoom <= 1) {
            translateX = 0;
            translateY = 0;
            return;
        }

        const modalImage = document.getElementById('modalImage');
        const rect = modalImage.getBoundingClientRect();
        const maxTranslateX = (rect.width * (currentZoom - 1)) / 2;
        const maxTranslateY = (rect.height * (currentZoom - 1)) / 2;

        translateX = Math.max(-maxTranslateX, Math.min(maxTranslateX, translateX));
        translateY = Math.max(-maxTranslateY, Math.min(maxTranslateY, translateY));
    }

    function resetZoom() {
        if (isTransitioning) return;

        isTransitioning = true;
        currentZoom = 1;
        translateX = 0;
        translateY = 0;
        applyTransform(true);
        updateCursor();

        setTimeout(() => {
            isTransitioning = false;
        }, 300);
    }

    function applyTransform(withTransition = false) {
        const modalImage = document.getElementById('modalImage');
        modalImage.style.transform = `scale(${currentZoom}) translate(${translateX / currentZoom}px, ${translateY / currentZoom}px)`;
        modalImage.style.transition = withTransition ? 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
    }

    function updateCursor() {
        const modalImage = document.getElementById('modalImage');
        if (currentZoom > 1) {
            modalImage.style.cursor = isDragging ? 'grabbing' : 'grab';
        } else {
            modalImage.style.cursor = 'zoom-in';
        }
    }

    function handleKeyDown(e) {
        switch (e.key) {
            case 'Escape':
                closeImageModal();
                break;
            case '+':
            case '=':
                e.preventDefault();
                zoomImage(0.3);
                break;
            case '-':
                e.preventDefault();
                zoomImage(-0.3);
                break;
            case '0':
                e.preventDefault();
                resetZoom();
                break;
        }
    }

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        // Mouse events for dragging
        modalImage.addEventListener('mousedown', function(e) {
            if (currentZoom > 1) {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                updateCursor();
                e.preventDefault();
            }
        });

        document.addEventListener('mousemove', function(e) {
            if (isDragging && currentZoom > 1 && !isTransitioning) {
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                constrainTranslation();
                applyTransform();
            }
        });

        document.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                updateCursor();
            }
        });

        // Double click to zoom
        modalImage.addEventListener('dblclick', function(e) {
            e.preventDefault();
            if (currentZoom === 1) {
                zoomImage(1, e.clientX, e.clientY);
            } else {
                resetZoom();
            }
        });

        // Mouse wheel zoom with cursor position
        modalImage.addEventListener('wheel', function(e) {
            e.preventDefault();
            if (isTransitioning) return;

            const delta = e.deltaY > 0 ? -0.3 : 0.3;
            zoomImage(delta, e.clientX, e.clientY);
        });

        // Touch events for mobile
        let touchStartDistance = 0;
        let touchStartZoom = 1;
        let touchStartCenter = {
            x: 0,
            y: 0
        };
        let isTouchZooming = false;

        modalImage.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                isTouchZooming = true;
                touchStartDistance = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                touchStartZoom = currentZoom;

                touchStartCenter.x = (e.touches[0].clientX + e.touches[1].clientX) / 2;
                touchStartCenter.y = (e.touches[0].clientY + e.touches[1].clientY) / 2;
            } else if (e.touches.length === 1 && currentZoom > 1) {
                isDragging = true;
                startX = e.touches[0].clientX - translateX;
                startY = e.touches[0].clientY - translateY;
            }
        });

        modalImage.addEventListener('touchmove', function(e) {
            if (e.touches.length === 2 && isTouchZooming) {
                e.preventDefault();
                const touchDistance = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );

                const scale = touchDistance / touchStartDistance;
                const newZoom = Math.max(minZoom, Math.min(maxZoom, touchStartZoom * scale));

                if (newZoom !== currentZoom) {
                    const centerX = (e.touches[0].clientX + e.touches[1].clientX) / 2;
                    const centerY = (e.touches[0].clientY + e.touches[1].clientY) / 2;

                    const rect = modalImage.getBoundingClientRect();
                    const zoomCenterX = centerX - rect.left - rect.width / 2;
                    const zoomCenterY = centerY - rect.top - rect.height / 2;

                    const zoomRatio = newZoom / currentZoom;
                    translateX = translateX * zoomRatio + zoomCenterX * (1 - zoomRatio);
                    translateY = translateY * zoomRatio + zoomCenterY * (1 - zoomRatio);

                    currentZoom = newZoom;
                    constrainTranslation();
                    applyTransform();
                    updateCursor();
                }
            } else if (e.touches.length === 1 && isDragging && currentZoom > 1) {
                e.preventDefault();
                translateX = e.touches[0].clientX - startX;
                translateY = e.touches[0].clientY - startY;
                constrainTranslation();
                applyTransform();
            }
        });

        modalImage.addEventListener('touchend', function(e) {
            if (e.touches.length === 0) {
                isDragging = false;
                isTouchZooming = false;
                updateCursor();
            }
        });

        // Prevent context menu on long press
        modalImage.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
</script>
