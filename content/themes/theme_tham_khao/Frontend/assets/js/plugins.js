/**
 * Plugins JavaScript functionality (updated)
 * Switch /search/... and /sort/... to query params ?search=&sort=
 * Preserves language prefix and keeps category in the path.
 */

document.addEventListener('DOMContentLoaded', function () {

    // Initialize the plugins page functionality
    initPluginsPage();

    // Load favorite states for plugins
    loadFavoriteStates();

    function initPluginsPage() {
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

    // Function to create URL-friendly string
    function createUrlFriendlyString(str) {
        // Remove Vietnamese diacritics using function from main.js
        const withoutDiacritics = removeVietnameseDiacritics(str);

        // Convert to lowercase
        const lowercase = withoutDiacritics.toLowerCase();

        // Replace spaces with hyphens
        const withHyphens = lowercase.replace(/\s+/g, '-');

        // Remove special characters except hyphens and alphanumeric
        const clean = withHyphens.replace(/[^a-z0-9\-]/g, '');

        // Remove multiple consecutive hyphens
        const final = clean.replace(/-+/g, '-').replace(/^-|-$/g, '');

        return final;
    }

    // Function to get base URL with language preservation
    function getBaseUrlWithLanguage() {
        const currentPath = window.location.pathname;
        // Fix regex to avoid duplicate language prefix
        const baseUrlMatch = currentPath.match(/^(\/[a-z]{2}\/library\/plugins)/);
        return baseUrlMatch ? baseUrlMatch[1] : '/library/plugins';
    }

    // Helper: extract current category slug from URL path
    function getCurrentCategory() {
        const urlMatch = window.location.pathname.match(/\/library\/plugins(?:\/category\/([^\/]+))?/);
        return urlMatch && urlMatch[1] ? urlMatch[1] : '';
    }

    // === NEW: Single builder to generate URLs using query params for search/sort ===
    function buildPluginsFilterUrl() {
        // Prefer desktop input, then mobile, then any input[name="search"]
        const searchInput = document.getElementById('desktop-search-input')
            || document.getElementById('mobile-search-input')
            || document.querySelector('input[name="search"]');
        const sortSelect = document.getElementById('sort-select')
            || document.querySelector('select[name="sort"]');
        const categorySelect = document.getElementById('mobile-category-select');

        const rawSearch = searchInput ? searchInput.value.trim() : '';
        const search = rawSearch ? createUrlFriendlyString(rawSearch) : '';
        const sort = sortSelect ? sortSelect.value.trim() : '';
        let category = categorySelect ? categorySelect.value.trim() : '';

        // If no category selected, get from current URL
        if (!category) {
            category = getCurrentCategory();
        }

        let url = getBaseUrlWithLanguage();

        // Add category to path if exists
        if (category) url += '/category/' + category;

        // Always add trailing slash before query parameters
        url += '/';

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (sort && sort !== 'created_at_desc') params.set('sort', sort);

        const qs = params.toString();
        if (qs) url += '?' + qs;
        return url;
    }

    // Initialize hero search functionality
    function initHeroSearch() {
        const form = document.querySelector('form');
        const searchInput = form ? form.querySelector('input[name="search"]') : null;



        if (form && searchInput) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Force form method to GET and remove any action
                form.method = 'GET';
                form.removeAttribute('action');

                // Build URL using query params (search kept in ?search=)
                const url = buildPluginsFilterUrl();

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
        const mobileCategorySelect = document.getElementById('mobile-category-select');
        const sortSelect = document.getElementById('sort-select');



        if (mobileSearchForm && mobileSearchInput) {
            // Handle form submission
            mobileSearchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Force form method to GET and remove any action
                mobileSearchForm.method = 'GET';
                mobileSearchForm.removeAttribute('action');

                showLoadingState();

                const url = buildPluginsFilterUrl();

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

                    showLoadingState();

                    const url = buildPluginsFilterUrl();

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

                    showLoadingState();

                    const url = buildPluginsFilterUrl();

                    // Use setTimeout to ensure form is properly updated
                    setTimeout(() => {
                        window.location.href = url;
                    }, 100);
                });
            }
        }

        // Handle category select change
        if (mobileCategorySelect) {
            mobileCategorySelect.addEventListener('change', function () {
                showLoadingState();

                const url = buildPluginsFilterUrl();

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });
        }

        // Handle sort select change
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                showLoadingState();

                const url = buildPluginsFilterUrl();

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });
        }
    }

    // Show loading state
    function showLoadingState() {
        const submitButton = document.querySelector('#mobileSearchForm button[type="submit"]');
        if (submitButton) {
            const originalContent = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
            submitButton.classList.add('opacity-75');
        }

        // Add loading overlay to filter section
        const filterSection = document.querySelector('.sticky.top-20');
        if (filterSection) {
            filterSection.classList.add('relative');

            const loadingOverlayHTML = '<div id="plugins-filter-loading-overlay" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg z-10"><div class="flex flex-col items-center"><i class="fas fa-spinner fa-spin text-xl text-purple-600 mb-2"></i><p class="text-slate-600 text-sm">Filtering...</p></div></div>';

            filterSection.insertAdjacentHTML('beforeend', loadingOverlayHTML);
        }
    }

    // Restore scroll position after form submission
    const savedScrollPosition = sessionStorage.getItem('pluginsFilterScrollPosition');
    if (savedScrollPosition) {
        setTimeout(() => {
            window.scrollTo({
                top: parseInt(savedScrollPosition),
                behavior: 'smooth'
            });
            sessionStorage.removeItem('pluginsFilterScrollPosition');
        }, 200);
    }

    function removeVietnameseDiacritics(str) {
        const vietnameseMap = {
            'à': 'a',
            'á': 'a',
            'ả': 'a',
            'ã': 'a',
            'ạ': 'a',
            'ă': 'a',
            'ằ': 'a',
            'ắ': 'a',
            'ẳ': 'a',
            'ẵ': 'a',
            'ặ': 'a',
            'â': 'a',
            'ầ': 'a',
            'ấ': 'a',
            'ẩ': 'a',
            'ẫ': 'a',
            'ậ': 'a',
            'đ': 'd',
            'è': 'e',
            'é': 'e',
            'ẻ': 'e',
            'ẽ': 'e',
            'ẹ': 'e',
            'ê': 'e',
            'ề': 'e',
            'ế': 'e',
            'ể': 'e',
            'ễ': 'e',
            'ệ': 'e',
            'ì': 'i',
            'í': 'i',
            'ỉ': 'i',
            'ĩ': 'i',
            'ị': 'i',
            'ò': 'o',
            'ó': 'o',
            'ỏ': 'o',
            'õ': 'o',
            'ọ': 'o',
            'ô': 'o',
            'ồ': 'o',
            'ố': 'o',
            'ổ': 'o',
            'ỗ': 'o',
            'ộ': 'o',
            'ơ': 'o',
            'ờ': 'o',
            'ớ': 'o',
            'ở': 'o',
            'ỡ': 'o',
            'ợ': 'o',
            'ù': 'u',
            'ú': 'u',
            'ủ': 'u',
            'ũ': 'u',
            'ụ': 'u',
            'ư': 'u',
            'ừ': 'u',
            'ứ': 'u',
            'ử': 'u',
            'ữ': 'u',
            'ự': 'u',
            'ỳ': 'y',
            'ý': 'y',
            'ỷ': 'y',
            'ỹ': 'y',
            'ỵ': 'y',
            'À': 'A',
            'Á': 'A',
            'Ả': 'A',
            'Ã': 'A',
            'Ạ': 'A',
            'Ă': 'A',
            'Ằ': 'A',
            'Ắ': 'A',
            'Ẳ': 'A',
            'Ẵ': 'A',
            'Ặ': 'A',
            'Â': 'A',
            'Ầ': 'A',
            'Ấ': 'A',
            'Ẩ': 'A',
            'Ẫ': 'A',
            'Ậ': 'A',
            'Đ': 'D',
            'È': 'E',
            'É': 'E',
            'Ẻ': 'E',
            'Ẽ': 'E',
            'Ẹ': 'E',
            'Ê': 'E',
            'Ề': 'E',
            'Ế': 'E',
            'Ể': 'E',
            'Ễ': 'E',
            'Ệ': 'E',
            'Ì': 'I',
            'Í': 'I',
            'Ỉ': 'I',
            'Ĩ': 'I',
            'Ị': 'I',
            'Ò': 'O',
            'Ó': 'O',
            'Ỏ': 'O',
            'Õ': 'O',
            'Ọ': 'O',
            'Ô': 'O',
            'Ồ': 'O',
            'Ố': 'O',
            'Ổ': 'O',
            'Ỗ': 'O',
            'Ộ': 'O',
            'Ơ': 'O',
            'Ờ': 'O',
            'Ớ': 'O',
            'Ở': 'O',
            'Ỡ': 'O',
            'Ợ': 'O',
            'Ù': 'U',
            'Ú': 'U',
            'Ủ': 'U',
            'Ũ': 'U',
            'Ụ': 'U',
            'Ư': 'U',
            'Ừ': 'U',
            'Ứ': 'U',
            'Ử': 'U',
            'Ữ': 'U',
            'Ự': 'U',
            'Ỳ': 'Y',
            'Ý': 'Y',
            'Ỷ': 'Y',
            'Ỹ': 'Y',
            'Ỵ': 'Y'
        };

        return str.split('').map(char => vietnameseMap[char] || char).join('');
    }

    // Enhanced search functionality
    function initSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = searchInput?.closest('form');
        let searchTimeout;

        if (!searchInput || !searchForm) return;

        // Function to create clear button dynamically
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
                // Navigate to URL without search parameter
                const url = buildPluginsFilterUrl();

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });

            return clearBtn;
        }

        // Function to toggle clear button visibility
        function toggleClearButton() {
            const existingClearBtn = searchForm.querySelector('.clear-search');
            const hasValue = searchInput.value.trim().length > 0;

            if (hasValue && !existingClearBtn) {
                const clearBtn = createClearButton();
                searchForm.appendChild(clearBtn);
            } else if (!hasValue && existingClearBtn) {
                existingClearBtn.remove();
            }
        }

        // Initialize clear button based on current value
        toggleClearButton();

        // Add search suggestions and handle typing
        searchInput.addEventListener('input', function (e) {
            const query = e.target.value.trim();

            // Toggle clear button based on input value
            toggleClearButton();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            // Debounce search suggestions
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    showSearchSuggestions(query);
                }, 300);
            } else {
                hideSearchSuggestions();
            }
        });

        // Handle search form submission with query params
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Force form method to GET and remove any action
            searchForm.method = 'GET';
            searchForm.removeAttribute('action');

            // Show loading state
            showSearchLoading();

            // Navigate to URL with query params
            const url = buildPluginsFilterUrl();

            // Use setTimeout to ensure form is properly updated
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        });

        // Handle existing clear button in HTML (if any)
        const existingClearBtn = document.querySelector('.clear-search');
        if (existingClearBtn) {
            existingClearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                // Navigate to URL without search parameter
                const url = buildPluginsFilterUrl();

                // Use setTimeout to ensure proper handling
                setTimeout(() => {
                    window.location.href = url;
                }, 100);
            });
        }
    }

    // Search suggestions (placeholder)
    function showSearchSuggestions(query) {
        // This would typically make an AJAX call to get suggestions
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

    async function toggleFavorite(pluginId, button) {
        try {
            // Add loading state
            button.classList.add('loading');

            // Toggle visual state
            const svg = button.querySelector('svg');
            const isLiked = button.classList.contains('liked');

            if (isLiked) {
                // Unlike
                button.classList.remove('liked', 'text-red-500');
                button.classList.add('text-slate-700');
                svg.setAttribute('fill', 'none');
                svg.classList.remove('text-red-500');
                svg.classList.add('text-slate-700');

                // Remove from localStorage
                removeFromFavorites(pluginId);
                showNotification('Plugin removed from favorites', 'info');
            } else {
                // Like
                button.classList.add('liked', 'text-red-500');
                button.classList.remove('text-slate-700');
                svg.setAttribute('fill', 'currentColor');
                svg.classList.remove('text-slate-700');
                svg.classList.add('text-red-500');

                // Add to localStorage
                addToFavorites(pluginId);
                showNotification('Plugin added to favorites', 'success');
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
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 200);
    }

    // View toggle functionality (grid/list)
    function initViewToggle() {
        const gridViewBtn = document.getElementById('gridViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const pluginsContainer = document.getElementById('pluginsContainer');

        if (!gridViewBtn || !listViewBtn || !pluginsContainer) return;

        // Save user preference
        const savedView = localStorage.getItem('plugins_view_preference') || 'grid';

        if (savedView === 'list') {
            switchToListView();
        }

        gridViewBtn.addEventListener('click', function () {
            switchToGridView();
            localStorage.setItem('plugins_view_preference', 'grid');
        });

        listViewBtn.addEventListener('click', function () {
            switchToListView();
            localStorage.setItem('plugins_view_preference', 'list');
        });

        function switchToGridView() {
            // Update button states
            gridViewBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            gridViewBtn.classList.remove('text-gray-500');

            listViewBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            listViewBtn.classList.add('text-gray-500');

            // Update container
            pluginsContainer.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8';

            // Update plugin cards for grid view
            const pluginCards = pluginsContainer.querySelectorAll('.group');
            pluginCards.forEach(card => {
                card.classList.remove('flex', 'items-center', 'space-x-4');
                card.classList.add('block');
            });
        }

        function switchToListView() {
            // Update button states
            listViewBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            listViewBtn.classList.remove('text-gray-500');

            gridViewBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            gridViewBtn.classList.add('text-gray-500');

            // Update container
            pluginsContainer.className = 'space-y-6';

            // Update plugin cards for list view
            const pluginCards = pluginsContainer.querySelectorAll('.group');
            pluginCards.forEach(card => {
                card.classList.remove('block');
                card.classList.add('flex', 'items-center', 'space-x-4');
            });
        }
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;

        // Set color based on type
        switch (type) {
            case 'success':
                notification.classList.add('bg-green-500');
                break;
            case 'error':
                notification.classList.add('bg-red-500');
                break;
            case 'warning':
                notification.classList.add('bg-yellow-500');
                break;
            default:
                notification.classList.add('bg-blue-500');
        }

        notification.textContent = message;
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 10);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Lazy loading for plugin images
    function initLazyLoading() {
        const pluginImages = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        pluginImages.forEach(img => imageObserver.observe(img));
    }

    // Filter animations
    function initFilterAnimations() {
        const filterButtons = document.querySelectorAll('.filter-btn');

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Remove active state from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));

                // Add active state to clicked button
                this.classList.add('active');

                // Animate plugin cards
                animatePluginCards();
            });
        });
    }

    function animatePluginCards() {
        const cards = document.querySelectorAll('.plugin-card');

        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    // Keyboard shortcuts
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput && document.activeElement === searchInput) {
                    searchInput.value = '';
                    searchInput.blur();
                }
            }
        });
    }

    // Tooltips
    function initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');

        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', showTooltip);
            element.addEventListener('mouseleave', hideTooltip);
        });
    }

    function showTooltip(e) {
        const text = e.target.dataset.tooltip;
        const tooltip = createTooltip(text);

        document.body.appendChild(tooltip);
        positionTooltip(tooltip, e.target);
    }

    function hideTooltip() {
        const tooltip = document.querySelector('.custom-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    function createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg';
        tooltip.textContent = text;
        return tooltip;
    }

    function positionTooltip(tooltip, target) {
        const rect = target.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.bottom + 5 + 'px';
    }

    // Auto-submit form when sort changes
    const sortSelect = document.querySelector('select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
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

            // Build URL with query params
            const url = buildPluginsFilterUrl();

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
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
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

        // Observe elements that should animate in
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            animationObserver.observe(el);
        });
    }
});

// CSS for fade-in animation
const style = document.createElement('style');
style.textContent = `
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .animate-in {
        animation: slideInUp 0.6s ease-out forwards;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Global function for plugin favorites (compatible with library.js)
function toggleHeartWithAnimation(button) {
    // Use the new FavoriteThemes class if available
    if (window.favoriteThemes) {
        window.favoriteThemes.toggleFavorite(button);
        return;
    }

    // Fallback for basic functionality with localStorage
    const pluginId = button.dataset.id;
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
        removeFromFavorites(pluginId);
    } else {
        // Like
        button.classList.add("liked", "text-red-500");
        button.classList.remove("text-slate-700");
        svg.setAttribute("fill", "currentColor");
        svg.classList.remove("text-slate-700");
        svg.classList.add("text-red-500");

        // Add to localStorage
        addToFavorites(pluginId);
    }
}

// Global localStorage functions for plugins
function addToFavorites(pluginId) {
    const storageKey = 'plugins_favourite';
    const stored = localStorage.getItem(storageKey);
    let favorites = stored ? stored.split(',').filter(id => id.trim()) : [];

    if (!favorites.includes(pluginId)) {
        favorites.push(pluginId);
        localStorage.setItem(storageKey, favorites.join(','));
    }
}

function removeFromFavorites(pluginId) {
    const storageKey = 'plugins_favourite';
    const stored = localStorage.getItem(storageKey);
    let favorites = stored ? stored.split(',').filter(id => id.trim()) : [];

    favorites = favorites.filter(id => id !== pluginId);

    if (favorites.length > 0) {
        localStorage.setItem(storageKey, favorites.join(','));
    } else {
        localStorage.removeItem(storageKey);
    }
}

// Load favorite states for all plugin buttons
function loadFavoriteStates() {
    const storageKey = 'plugins_favourite';
    const stored = localStorage.getItem(storageKey);
    const favorites = stored ? stored.split(',').filter(id => id.trim()) : [];

    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    favoriteButtons.forEach(button => {
        const pluginId = button.dataset.id;
        const svg = button.querySelector('svg');
        const isLiked = favorites.includes(pluginId);

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

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;

    // Set color based on type
    switch (type) {
        case 'success':
            toast.classList.add('bg-green-500');
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            break;
        default:
            toast.classList.add('bg-blue-500');
    }

    toast.textContent = message;
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Plugin installation helper
function installPlugin(pluginId, installUrl) {
    if (!installUrl || installUrl === '#') {
        showToast('Installation URL not available', 'error');
        return;
    }

    // Track installation attempt
    trackPluginInstallation(pluginId);

    // Open installation URL
    window.open(installUrl, '_blank');
}

function trackPluginInstallation(pluginId) {
    // Track plugin installation for analytics

    // Could send to analytics service
    // gtag('event', 'plugin_install', { plugin_id: pluginId });
}
