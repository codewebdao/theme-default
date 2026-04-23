// Theme Detail Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme detail functionality
    initializeThemeDetail();
});

function initializeThemeDetail() {
    // Heart/favorite button functionality
    initializeFavoriteButtons();
    
    // Image gallery functionality
    initializeImageGallery();
    
    // Download tracking
    initializeDownloadTracking();
    
    // Smooth scrolling for anchor links
    initializeSmoothScrolling();
    
    // Rating display
    initializeRatingDisplay();
}

/**
 * Initialize favorite buttons
 */
function initializeFavoriteButtons() {
    const favoriteButtons = document.querySelectorAll('[onclick*="toggleHeartWithAnimation"]');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            toggleHeartWithAnimation(this);
        });
    });
}

/**
 * Toggle heart animation for favorite button
 */
function toggleHeartWithAnimation(button) {
    const heart = button.querySelector('svg');
    const isActive = button.classList.contains('active');
    
    if (isActive) {
        button.classList.remove('active');
        heart.style.fill = 'none';
        showNotification('Removed from favorites', 'info');
    } else {
        button.classList.add('active');
        heart.style.fill = 'currentColor';
        
        // Add animation
        button.style.transform = 'scale(1.2)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 200);
        
        showNotification('Added to favorites', 'success');
    }
    
    // Save to localStorage
    saveFavoriteState(button);
}

/**
 * Initialize image gallery functionality
 */
function initializeImageGallery() {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    if (!modal || !modalImage) return;
    
    // Close modal on click outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeImageModal();
        }
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeImageModal();
        }
    });
}

/**
 * Open image modal
 */
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    if (!modal || !modalImage) return;
    
    modalImage.src = imageSrc;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Add fade in animation
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

/**
 * Close image modal
 */
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    
    if (!modal) return;
    
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}

/**
 * Initialize download tracking
 */
function initializeDownloadTracking() {
    const downloadButtons = document.querySelectorAll('a[href*="/download/"]');
    
    downloadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const themeName = this.href.split('/').pop();
            trackDownload(themeName);
        });
    });
}

/**
 * Track download event
 */
function trackDownload(themeName) {
    // Analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'download', {
            'event_category': 'Theme',
            'event_label': themeName
        });
    }
    
    // Internal tracking
    fetch('/api/track-download', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: 'theme',
            name: themeName,
            timestamp: new Date().toISOString()
        })
    }).catch(console.error);
}

/**
 * Initialize smooth scrolling
 */
function initializeSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize rating display
 */
function initializeRatingDisplay() {
    const ratingElements = document.querySelectorAll('.rating-stars');
    
    ratingElements.forEach(element => {
        const rating = parseFloat(element.dataset.rating || '5');
        const stars = element.querySelectorAll('svg');
        
        stars.forEach((star, index) => {
            if (index < Math.floor(rating)) {
                star.style.fill = 'currentColor';
            } else if (index < rating) {
                // Partial star
                star.style.fill = 'url(#half-star)';
            }
        });
    });
}

/**
 * Save favorite state to localStorage
 */
function saveFavoriteState(button) {
    const themeSlug = button.dataset.themeSlug || '';
    const isActive = button.classList.contains('active');
    
    if (!themeSlug) return;
    
    let favorites = JSON.parse(localStorage.getItem('theme_favorites') || '[]');
    
    if (isActive) {
        if (!favorites.includes(themeSlug)) {
            favorites.push(themeSlug);
        }
    } else {
        favorites = favorites.filter(slug => slug !== themeSlug);
    }
    
    localStorage.setItem('theme_favorites', JSON.stringify(favorites));
}

/**
 * Load favorite states from localStorage
 */
function loadFavoriteStates() {
    const favorites = JSON.parse(localStorage.getItem('theme_favorites') || '[]');
    const favoriteButtons = document.querySelectorAll('[data-theme-slug]');
    
    favoriteButtons.forEach(button => {
        const themeSlug = button.dataset.themeSlug;
        if (favorites.includes(themeSlug)) {
            button.classList.add('active');
            const heart = button.querySelector('svg');
            if (heart) {
                heart.style.fill = 'currentColor';
            }
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 ${getNotificationColor(type)}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Remove after delay
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

/**
 * Get notification color class based on type
 */
function getNotificationColor(type) {
    switch (type) {
        case 'success':
            return 'bg-green-500';
        case 'error':
            return 'bg-red-500';
        case 'warning':
            return 'bg-yellow-500';
        default:
            return 'bg-blue-500';
    }
}

// Load favorite states when page loads
document.addEventListener('DOMContentLoaded', loadFavoriteStates);
