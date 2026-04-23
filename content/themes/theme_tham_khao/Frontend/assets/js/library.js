/**
 * Library page specific JavaScript
 * Works in conjunction with favorite-themes.js for complete functionality
 */

function toggleHeartWithAnimation(button) {
    // Use the new FavoriteThemes class if available
    if (window.favoriteThemes) {
        window.favoriteThemes.toggleFavorite(button);
        return;
    }

    // Fallback for basic functionality with localStorage
    const themeId = button.dataset.id;
    const svg = button.querySelector("svg");
    const isLiked = button.classList.contains("liked");

    // Animation khi click
    svg.style.transform = "scale(1.3)";
    setTimeout(() => {
        svg.style.transform = "scale(1)";
    }, 150);

    if (isLiked) {
        // Unlike
        button.classList.remove("liked", "text-red-500");
        button.classList.add("text-slate-700");
        svg.setAttribute("fill", "none");
        svg.classList.remove("text-red-500");
        svg.classList.add("text-slate-700");

        // Remove from localStorage
        removeFromFavorites(themeId);
    } else {
        // Like
        button.classList.add("liked", "text-red-500");
        button.classList.remove("text-slate-700");
        svg.setAttribute("fill", "currentColor");
        svg.classList.remove("text-slate-700");
        svg.classList.add("text-red-500");

        // Add to localStorage
        addToFavorites(themeId);
    }
}

// Fallback localStorage functions
function addToFavorites(themeId) {
    const storageKey = 'themes_favourite';
    const stored = localStorage.getItem(storageKey);
    let favorites = stored ? stored.split(',').filter(id => id.trim()) : [];

    if (!favorites.includes(themeId)) {
        favorites.push(themeId);
        localStorage.setItem(storageKey, favorites.join(','));

    }
}

function removeFromFavorites(themeId) {
    const storageKey = 'themes_favourite';
    const stored = localStorage.getItem(storageKey);
    let favorites = stored ? stored.split(',').filter(id => id.trim()) : [];

    favorites = favorites.filter(id => id !== themeId);

    if (favorites.length > 0) {
        localStorage.setItem(storageKey, favorites.join(','));
    } else {
        localStorage.removeItem(storageKey);
    }
}

// Initialize library page functionality
document.addEventListener('DOMContentLoaded', function () {
    // Load favorite states
    loadFavoriteStates();

    // Additional library-specific functionality can be added here
    initLibraryFeatures();
});

// Load favorite states for all buttons
function loadFavoriteStates() {
    const storageKey = 'themes_favourite';
    const stored = localStorage.getItem(storageKey);
    const favorites = stored ? stored.split(',').filter(id => id.trim()) : [];
    const favoriteButtons = document.querySelectorAll('.favorite-btn, button[onclick*="toggleHeartWithAnimation"]');

    favoriteButtons.forEach(button => {
        const themeId = button.dataset.id;
        const svg = button.querySelector('svg');
        const isLiked = favorites.includes(themeId);

        if (isLiked) {
            button.classList.add('liked', 'text-red-500');
            button.classList.remove('text-slate-700');
            if (svg) {
                svg.setAttribute('fill', 'currentColor');
                svg.classList.remove('text-slate-700');
                svg.classList.add('text-red-500');
            }
        } else {
            button.classList.remove('liked', 'text-red-500');
            button.classList.add('text-slate-700');
            if (svg) {
                svg.setAttribute('fill', 'none');
                svg.classList.remove('text-red-500');
                svg.classList.add('text-slate-700');
            }
        }
    });
}

function initLibraryFeatures() {
    // Initialize theme filtering
    initThemeFiltering();

    // Initialize search functionality
    initLibrarySearch();

    // Initialize sorting
    initThemeSorting();
}

function initThemeFiltering() {
    const filterButtons = document.querySelectorAll('[data-filter]');

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            const filter = this.dataset.filter;
            filterThemes(filter);

            // Update active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function filterThemes(category) {
    const themeCards = document.querySelectorAll('[data-theme-category]');

    themeCards.forEach(card => {
        if (category === 'all' || card.dataset.themeCategory === category) {
            card.style.display = 'block';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
            card.classList.remove('fade-in');
        }
    });
}

function initLibrarySearch() {
    const searchInput = document.querySelector('#theme-search');
    if (!searchInput) return;

    let searchTimeout;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchThemes(this.value.trim());
        }, 300);
    });
}

function searchThemes(query) {
    const themeCards = document.querySelectorAll('.theme-card');
    const queryLower = query.toLowerCase();

    themeCards.forEach(card => {
        const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = card.querySelector('p')?.textContent.toLowerCase() || '';

        if (query === '' || title.includes(queryLower) || description.includes(queryLower)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function initThemeSorting() {
    const sortSelect = document.querySelector('#theme-sort');
    if (!sortSelect) return;

    sortSelect.addEventListener('change', function () {
        sortThemes(this.value);
    });
}

function sortThemes(sortBy) {
    const container = document.querySelector('.themes-grid');
    if (!container) return;

    const themes = Array.from(container.children);

    themes.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                const nameA = a.querySelector('h3')?.textContent || '';
                const nameB = b.querySelector('h3')?.textContent || '';
                return nameA.localeCompare(nameB);

            case 'price':
                const priceA = parseFloat(a.dataset.price || '0');
                const priceB = parseFloat(b.dataset.price || '0');
                return priceA - priceB;

            case 'popularity':
                const downloadsA = parseInt(a.dataset.downloads || '0');
                const downloadsB = parseInt(b.dataset.downloads || '0');
                return downloadsB - downloadsA;

            default:
                return 0;
        }
    });

    // Re-append sorted themes
    themes.forEach(theme => container.appendChild(theme));
}
