/**
 * Themes JavaScript functionality (updated)
 * Switch /search/... and /sort/... to query params ?search=&sort=
 * Preserves language prefix and keeps category in the path.
 */

document.addEventListener('DOMContentLoaded', function () {

    // Initialize the themes page functionality
    initThemesPage();

    // Load favorite states for themes
    loadFavoriteStates();

    function initThemesPage() {
        // Initialize search functionality
        initSearch();

        // Initialize hero search functionality
        initHeroSearch();

        // Initialize filter functionality
        initFilter();

        // Initialize favorite functionality
        initFavorites();

        // Initialize view toggle
        initViewToggle();

        // Initialize lazy loading
        initLazyLoading();

        // Initialize filter animations
        initFilterAnimations();

        // Initialize keyboard shortcuts
        initKeyboardShortcuts();

        // Initialize tooltips
        initTooltips();
    }

    function removeVietnameseDiacritics(str) {
        const vietnameseMap = {
            'à': 'a', 'á': 'a', 'ả': 'a', 'ã': 'a', 'ạ': 'a', 'ă': 'a', 'ằ': 'a', 'ắ': 'a', 'ẳ': 'a', 'ẵ': 'a', 'ặ': 'a', 'â': 'a', 'ầ': 'a', 'ấ': 'a', 'ẩ': 'a', 'ẫ': 'a', 'ậ': 'a', 'đ': 'd', 'è': 'e', 'é': 'e', 'ẻ': 'e', 'ẽ': 'e', 'ẹ': 'e', 'ê': 'e', 'ề': 'e', 'ế': 'e', 'ể': 'e', 'ễ': 'e', 'ệ': 'e', 'ì': 'i', 'í': 'i', 'ỉ': 'i', 'ĩ': 'i', 'ị': 'i', 'ò': 'o', 'ó': 'o', 'ỏ': 'o', 'õ': 'o', 'ọ': 'o', 'ô': 'o', 'ồ': 'o', 'ố': 'o', 'ổ': 'o', 'ỗ': 'o', 'ộ': 'o', 'ơ': 'o', 'ờ': 'o', 'ớ': 'o', 'ở': 'o', 'ỡ': 'o', 'ợ': 'o', 'ù': 'u', 'ú': 'u', 'ủ': 'u', 'ũ': 'u', 'ụ': 'u', 'ư': 'u', 'ừ': 'u', 'ứ': 'u', 'ử': 'u', 'ữ': 'u', 'ự': 'u', 'ỳ': 'y', 'ý': 'y', 'ỷ': 'y', 'ỹ': 'y', 'ỵ': 'y', 'À': 'A', 'Á': 'A', 'Ả': 'A', 'Ã': 'A', 'Ạ': 'A', 'Ă': 'A', 'Ằ': 'A', 'Ắ': 'A', 'Ẳ': 'A', 'Ẵ': 'A', 'Ặ': 'A', 'Â': 'A', 'Ầ': 'A', 'Ấ': 'A', 'Ẩ': 'A', 'Ẫ': 'A', 'Ậ': 'A', 'Đ': 'D', 'È': 'E', 'É': 'E', 'Ẻ': 'E', 'Ẽ': 'E', 'Ẹ': 'E', 'Ê': 'E', 'Ề': 'E', 'Ế': 'E', 'Ể': 'E', 'Ễ': 'E', 'Ệ': 'E', 'Ì': 'I', 'Í': 'I', 'Ỉ': 'I', 'Ĩ': 'I', 'Ị': 'I', 'Ò': 'O', 'Ó': 'O', 'Ỏ': 'O', 'Õ': 'O', 'Ọ': 'O', 'Ô': 'O', 'Ồ': 'O', 'Ố': 'O', 'Ổ': 'O', 'Ỗ': 'O', 'Ộ': 'O', 'Ơ': 'O', 'Ờ': 'O', 'Ớ': 'O', 'Ở': 'O', 'Ỡ': 'O', 'Ợ': 'O', 'Ù': 'U', 'Ú': 'U', 'Ủ': 'U', 'Ũ': 'U', 'Ụ': 'U', 'Ư': 'U', 'Ừ': 'U', 'Ứ': 'U', 'Ử': 'U', 'Ữ': 'U', 'Ự': 'U', 'Ỳ': 'Y', 'Ý': 'Y', 'Ỷ': 'Y', 'Ỹ': 'Y', 'Ỵ': 'Y'
        };
        return str.split('').map(char => vietnameseMap[char] || char).join('');
    }

    // Optional: still available if you need slugs elsewhere
    function createUrlFriendlyString(str) {
        const withoutDiacritics = removeVietnameseDiacritics(str);
        const lowercase = withoutDiacritics.toLowerCase();
        const withHyphens = lowercase.replace(/\s+/g, '-');
        const clean = withHyphens.replace(/[^a-z0-9\-]/g, '');
        const final = clean.replace(/-+/g, '-').replace(/^-|-$/g, '');
        return final;
    }

    // Function to get base URL with language preservation, e.g. "/vi/library/themes"
    function getBaseUrlWithLanguage() {
        const currentPath = window.location.pathname;
        // Fix regex to avoid duplicate language prefix
        const baseUrlMatch = currentPath.match(/^(\/[a-z]{2}\/library\/themes)/);
        return baseUrlMatch ? baseUrlMatch[1] : '/library/themes';
    }

    // Helper: extract current category slug from URL path
    function getCurrentCategory() {
        const urlMatch = window.location.pathname.match(/\/library\/themes(?:\/category\/([^\/]+))?/);
        return urlMatch && urlMatch[1] ? urlMatch[1] : '';
    }

    // === NEW: Single builder to generate URLs using query params for search/sort ===
    function buildThemesFilterUrl() {
        // Prefer desktop input, then mobile, then any input[name="search"]
        const searchInput = document.getElementById('desktop-search-input')
            || document.getElementById('mobile-search-input')
            || document.querySelector('input[name="search"]');
        const sortSelect = document.getElementById('sortSelect')
            || document.querySelector('select[name="sort"]');

        const rawSearch = searchInput ? searchInput.value.trim() : '';
        const search = rawSearch ? createUrlFriendlyString(rawSearch) : '';
        const sort = sortSelect ? sortSelect.value.trim() : '';

        let url = getBaseUrlWithLanguage();
        const category = getCurrentCategory();
        if (category) url += '/category/' + category;

        // Always add trailing slash before query parameters
        url += '/';

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        console.log('Search term:', search);
        if (sort && sort !== 'created_at_desc') params.set('sort', sort);

        const qs = params.toString();
        if (qs) url += '?' + qs;
        console.log('Final URL:', url);
        return url;
    }

    // Initialize hero search functionality
    function initHeroSearch() {
        const form = document.getElementById('desktopSearchForm');
        const searchInput = document.getElementById('desktop-search-input');

        if (form && searchInput) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Force form method to GET and remove any action
                form.method = 'GET';
                form.removeAttribute('action');

                // Build URL using query params (search kept in ?search=)
                const url = buildThemesFilterUrl();
                console.log('Hero search submitting to URL:', url);

                // Use setTimeout to ensure form is properly updated
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });

            // Handle Enter key
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });

            // Handle button click
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                });
            }
        }
    }

    // Initialize filter functionality
    function initFilter() {
        const mobileSearchForm = document.getElementById('mobileSearchForm');
        const mobileSearchInput = document.getElementById('mobile-search-input');
        const sortSelect = document.getElementById('sortSelect');

        if (mobileSearchForm && mobileSearchInput) {
            // Handle form submission
            mobileSearchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Force form method to GET and remove any action
                mobileSearchForm.method = 'GET';
                mobileSearchForm.removeAttribute('action');

                const url = buildThemesFilterUrl();
                console.log('Mobile search submitting to URL:', url);

                // Use setTimeout to ensure form is properly updated
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });

            // Handle Enter key
            mobileSearchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();

                    // Force form method to GET and remove any action
                    mobileSearchForm.method = 'GET';
                    mobileSearchForm.removeAttribute('action');

                    const url = buildThemesFilterUrl();
                    console.log('Mobile search Enter key to URL:', url);

                    // Use setTimeout to ensure form is properly updated
                    setTimeout(() => {
                        window.location.href = url;
                    }, 100);
                }
            });

            // Handle button click
            const submitButton = mobileSearchForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Force form method to GET and remove any action
                    mobileSearchForm.method = 'GET';
                    mobileSearchForm.removeAttribute('action');

                    const url = buildThemesFilterUrl();
                    console.log('Mobile search button click to URL:', url);

                    // Use setTimeout to ensure form is properly updated
                    setTimeout(() => {
                        window.location.href = url;
                    }, 100);
                });
            }
        }

        // Handle sort select change
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                console.log('Themes sort changed');

                const url = buildThemesFilterUrl();
                console.log('Sort change to URL:', url);

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });
        }
    }

    // Enhanced search functionality
    function initSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = searchInput?.closest('form');
        if (!searchInput || !searchForm) return;

        function createClearButton() {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'clear-search absolute right-32 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-gray-100 rounded-full';
            clearBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            `;

            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                toggleClearButton();

                const url = buildThemesFilterUrl();
                console.log('Clear button to URL:', url);

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });
            return clearBtn;
        }

        function toggleClearButton() {
            const existingClearBtn = searchForm.querySelector('.clear-search');
            const hasValue = searchInput.value.trim().length > 0;
            if (hasValue && !existingClearBtn) {
                const clearBtn = createClearButton();
                searchForm.querySelector('.relative')?.appendChild(clearBtn);
                searchInput.classList.remove('pr-32');
                searchInput.classList.add('pr-36');
            } else if (!hasValue && existingClearBtn) {
                existingClearBtn.remove();
                searchInput.classList.remove('pr-36');
                searchInput.classList.add('pr-32');
            }
        }

        // Initialize clear button based on current value
        toggleClearButton();

        // Submit using query params
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Force form method to GET and remove any action
            searchForm.method = 'GET';
            searchForm.removeAttribute('action');

            showSearchLoading();

            // Build URL manually to avoid any encoding issues
            const url = buildThemesFilterUrl();
            console.log('Submitting to URL:', url);

            // Use setTimeout to ensure form is properly updated
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        });

        const existingClearBtn = document.querySelector('.clear-search');
        if (existingClearBtn) {
            existingClearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                toggleClearButton();

                const url = buildThemesFilterUrl();
                console.log('Existing clear button to URL:', url);

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });
        }
    }

    function hideSearchSuggestions() {
        const suggestions = document.querySelector('.search-suggestions');
        if (suggestions) suggestions.remove();
    }

    function showSearchLoading() {
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Searching...
            `;
        }
    }

    // Enhanced favorites functionality
    function initFavorites() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
        favoriteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleHeartWithAnimation(this);
            });
        });
    }

    async function toggleFavorite(themeId, button) {
        try {
            button.classList.add('loading');

            // Toggle visual state
            const svg = button.querySelector('svg');
            const isLiked = button.classList.contains('liked');

            if (isLiked) {
                // Unlike
                button.classList.remove('liked', 'text-red-500');
                button.classList.remove('text-slate-700');
                button.classList.add('text-slate-700');
                svg.setAttribute('fill', 'none');
                svg.classList.remove('text-red-500');
                svg.classList.add('text-slate-700');

                // Remove from localStorage
                removeFromFavorites(themeId);
                showNotification('Theme removed from favorites', 'info');
            } else {
                // Like
                button.classList.add('liked', 'text-red-500');
                button.classList.remove('text-slate-700');
                svg.setAttribute('fill', 'currentColor');
                svg.classList.remove('text-slate-700');
                svg.classList.add('text-red-500');

                // Add to localStorage
                addToFavorites(themeId);
                showNotification('Theme added to favorites', 'success');
                animateHeart(button);
            }

        } catch (error) {
            console.error('Error toggling favorite:', error);
            showNotification('Error updating favorites', 'error');
        } finally {
            button.classList.remove('loading');
        }
    }



    function animateHeart(button) {
        button.style.transform = 'scale(1.3)';
        setTimeout(() => { button.style.transform = 'scale(1)'; }, 200);
    }

    // View toggle functionality
    function initViewToggle() {
        const gridViewBtn = document.getElementById('gridViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const themesContainer = document.getElementById('themesContainer');
        if (!gridViewBtn || !listViewBtn || !themesContainer) return;

        const savedView = localStorage.getItem('themes_view_preference') || 'grid';
        if (savedView === 'list') switchToListView();

        gridViewBtn.addEventListener('click', function () {
            switchToGridView();
            localStorage.setItem('themes_view_preference', 'grid');
        });
        listViewBtn.addEventListener('click', function () {
            switchToListView();
            localStorage.setItem('themes_view_preference', 'list');
        });

        function switchToGridView() {
            gridViewBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            gridViewBtn.classList.remove('text-gray-500');
            listViewBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            listViewBtn.classList.add('text-gray-500');
            themesContainer.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8';
            const themeCards = themesContainer.querySelectorAll('.group');
            themeCards.forEach(card => {
                card.className = 'group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 hover:border-blue-200 transform hover:-translate-y-2';
            });
        }
        function switchToListView() {
            listViewBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            listViewBtn.classList.remove('text-gray-500');
            gridViewBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            gridViewBtn.classList.add('text-gray-500');
            themesContainer.className = 'space-y-6';
            const themeCards = themesContainer.querySelectorAll('.group');
            themeCards.forEach(card => {
                card.className = 'group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200 hover:border-blue-200 flex';
            });
        }
    }

    // Lazy loading for images
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.dataset.src;
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                            imageObserver.unobserve(img);
                            img.classList.add('fade-in');
                        }
                    }
                });
            });
            document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
        }
    }

    // Filter animations
    function initFilterAnimations() {
        const filterTags = document.querySelectorAll('.filter-tag');
        filterTags.forEach((tag, index) => { tag.style.animationDelay = `${index * 0.1}s`; });
    }

    // Keyboard shortcuts
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) { searchInput.focus(); searchInput.select(); }
            }
            if (e.key === 'Escape') {
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput && document.activeElement === searchInput) {
                    searchInput.blur();
                    if (searchInput.value.trim()) {
                        const clearBtn = document.querySelector('.clear-search');
                        if (clearBtn) clearBtn.click();
                    }
                }
            }
        });
    }

    // Tooltips initialization
    function initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            let tooltip;
            element.addEventListener('mouseenter', function () {
                const text = this.dataset.tooltip;
                tooltip = createTooltip(text);
                document.body.appendChild(tooltip);
                positionTooltip(tooltip, this);
            });
            element.addEventListener('mouseleave', function () {
                if (tooltip) { tooltip.remove(); tooltip = null; }
            });
        });
    }

    function createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'fixed z-50 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg pointer-events-none transition-opacity duration-200';
        tooltip.textContent = text; return tooltip;
    }
    function positionTooltip(tooltip, element) {
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        let top = rect.top - tooltipRect.height - 8;
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        if (top < 8) top = rect.bottom + 8;
        if (left < 8) left = 8; else if (left + tooltipRect.width > window.innerWidth - 8) left = window.innerWidth - tooltipRect.width - 8;
        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
        switch (type) {
            case 'success': notification.classList.add('bg-green-500'); break;
            case 'error': notification.classList.add('bg-red-500'); break;
            case 'warning': notification.classList.add('bg-yellow-500'); break;
            default: notification.classList.add('bg-blue-500');
        }
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => { notification.classList.remove('translate-x-full'); }, 10);
        setTimeout(() => { notification.classList.add('translate-x-full'); setTimeout(() => { notification.remove(); }, 300); }, 3000);
    }

    // Auto-submit when sort changes (fallback for other selects)
    const sortSelectGlobal = document.querySelector('select[name="sort"]');
    if (sortSelectGlobal) {
        sortSelectGlobal.addEventListener('change', function () {
            // Optional UI loading state for any form around it
            const form = this.closest('form');
            if (form) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.003 8.003 0 014.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Updating...
                    `;
                }
            }

            const url = buildThemesFilterUrl();
            console.log('Global sort select to URL:', url);

            // Use setTimeout to ensure proper handling
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Performance optimization: Intersection Observer for animations
    if ('IntersectionObserver' in window) {
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    animationObserver.unobserve(entry.target);
                }
            });
        });
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            animationObserver.observe(el);
        });
    }
});

// Global function for theme favorites (compatible with library.js)
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

// Global localStorage functions for themes
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

// Load favorite states for all theme buttons
function loadFavoriteStates() {
    const storageKey = 'themes_favourite';
    const stored = localStorage.getItem(storageKey);
    const favorites = stored ? stored.split(',').filter(id => id.trim()) : [];

    const favoriteButtons = document.querySelectorAll('.favorite-btn');

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

// CSS for fade-in animation
const style = document.createElement('style');
style.textContent = `
    .fade-in { animation: fadeIn 0.5s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .animate-in { animation: slideInUp 0.6s ease-out forwards; }
    @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
`;
document.head.appendChild(style);
