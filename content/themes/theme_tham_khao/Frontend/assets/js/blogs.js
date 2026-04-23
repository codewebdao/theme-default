/**
 * Blogs JavaScript (Updated)
 * Handles all blog-related functionality including search, filtering, pagination, and UI interactions
 * Updated to use query parameters for search and sort, category remains in URL path
 */

class BlogsManager {
    constructor() {
        // Initialize DOM elements
        this.searchForm = document.getElementById('search-form');
        this.searchFormContainer = document.getElementById('blog-search-form');
        this.searchInput = document.querySelector('input[name="search"]');
        this.typeSelect = document.querySelector('select[name="type"]');
        this.tagsInput = document.querySelector('input[name="tags"]');

        this.init();
    }

    /**
     * Initialize blogs functionality
     */
    init() {
        this.bindEvents();
        this.handlePageLoad();
        this.initSearchSuggestions();
        this.initKeyboardShortcuts();
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Handle form submission with query parameters
        this.handleFormSubmission();

        // Handle mobile category select changes
        this.handleMobileCategorySelect();

        // Handle type select changes
        this.handleTypeSelectChanges();

        // Handle Enter key in search input
        this.handleSearchInputKeypress();

        // Handle form submission for scroll position
        this.handleFormScrollPosition();

        // Handle tags input enhancement
        if (this.tagsInput) {
            this.tagsInput.addEventListener('input', () => {
                this.saveSearchState();
            });
        }
    }

    /**
     * Handle page load events
     */
    handlePageLoad() {
        // Handle tag parameter from other pages first
        this.handleTagParameter();

        // Restore scroll position or scroll to search form
        setTimeout(() => {
            const hasSearchParams = this.hasActiveSearch();
            const savedScrollPosition = sessionStorage.getItem('searchFormScrollPosition');
            const hasTagParam = new URLSearchParams(window.location.search).has('tag');

            if (savedScrollPosition) {
                window.scrollTo({
                    top: parseInt(savedScrollPosition),
                    behavior: 'smooth'
                });
                sessionStorage.removeItem('searchFormScrollPosition');
                this.highlightSearchForm();
            } else if (hasSearchParams && !hasTagParam) {
                // Only auto-scroll if it's not a tag parameter from another page
                this.scrollToSearchForm(true);
            }
        }, 100);

        // Restore search state if needed
        this.restoreSearchState();
    }

    /**
     * Handle form submission with query parameters
     */
    handleFormSubmission() {
        if (!this.searchForm) return;

        this.searchForm.addEventListener('submit', (e) => {
            console.log('🔍 [DEBUG] Form submission triggered');
            e.preventDefault(); // Prevent default form submission

            console.log('🔍 [DEBUG] Search input element:', this.searchInput);
            console.log('🔍 [DEBUG] Search input value:', this.searchInput?.value);
            console.log('🔍 [DEBUG] Search input type:', typeof this.searchInput?.value);

            this.showLoadingState();
            this.saveScrollPosition();
            this.saveSearchState();

            // Build URL rewrite format
            this.submitWithQueryParams();
        });
    }

    /**
     * Submit form with query parameters format
     */
    submitWithQueryParams() {
        console.log('🔍 [DEBUG] submitWithQueryParams called');
        console.log('🔍 [DEBUG] this.searchInput:', this.searchInput);
        console.log('🔍 [DEBUG] this.searchInput.value:', this.searchInput?.value);
        console.log('🔍 [DEBUG] this.searchInput.value type:', typeof this.searchInput?.value);

        const search = this.searchInput?.value.trim() || '';
        const tags = this.tagsInput?.value.trim() || '';
        const type = this.typeSelect?.value.trim() || '';

        console.log('🔍 [DEBUG] Original search value:', search);
        console.log('🔍 [DEBUG] Original tags value:', tags);
        console.log('🔍 [DEBUG] Original type value:', type);

        // Get current category from URL if exists
        const pathSegments = window.location.pathname.split('/');
        const categoryIndex = pathSegments.indexOf('category');
        const currentCategory = categoryIndex !== -1 && pathSegments[categoryIndex + 1] ? pathSegments[categoryIndex + 1] : '';

        console.log('🔍 [DEBUG] Current category from URL:', currentCategory);

        // Build base URL
        let baseUrl = this.getBaseUrlWithLanguage();

        // Add category to path if exists
        if (currentCategory) {
            baseUrl += '/category/' + currentCategory;
        }

        // Always add trailing slash before query parameters
        baseUrl += '/';

        // Build query parameters
        const params = new URLSearchParams();
        if (search) {
            console.log('🔍 [DEBUG] Processing search term:', search);
            const urlFriendlySearch = this.createUrlFriendlyString(search);
            console.log('🔍 [DEBUG] URL-friendly search:', urlFriendlySearch);
            params.set('search', urlFriendlySearch);
        }
        if (tags) {
            console.log('🔍 [DEBUG] Processing tags:', tags);
            const urlFriendlyTags = this.createUrlFriendlyString(tags);
            console.log('🔍 [DEBUG] URL-friendly tags:', urlFriendlyTags);
            params.set('tags', urlFriendlyTags);
        }
        if (type && type !== '') {
            params.set('type', type);
        }

        // Construct final URL
        const queryString = params.toString();
        const finalUrl = queryString ? baseUrl + '?' + queryString : baseUrl;

        console.log('🔍 [DEBUG] Final URL:', finalUrl);

        // Use setTimeout to ensure proper handling
        setTimeout(() => {
            window.location.href = finalUrl;
        }, 100);
    }

    /**
     * Get base URL with language segment
     */
    getBaseUrlWithLanguage() {
        const currentPath = window.location.pathname;

        // Check for language segment in URL (e.g., /vi/blogs, /en/blogs)
        // Fix regex to avoid duplicate language prefix
        const baseUrlMatch = currentPath.match(/^(\/[a-z]{2}\/blogs)/);
        if (baseUrlMatch) {
            return baseUrlMatch[1];
        }

        // Check for blogs without language segment
        const blogsMatch = currentPath.match(/^(\/blogs)/);
        if (blogsMatch) {
            return blogsMatch[1];
        }

        // Default fallback
        return '/blogs';
    }

    /**
     * Create URL-friendly string from search terms
     */
    createUrlFriendlyString(str) {
        if (!str) return '';

        console.log('🔍 [DEBUG] Creating URL-friendly string from:', str);

        // Remove Vietnamese diacritics
        const withoutDiacritics = this.removeVietnameseDiacritics(str);
        console.log('🔍 [DEBUG] After removing diacritics:', withoutDiacritics);

        // Convert to lowercase
        const lowercase = withoutDiacritics.toLowerCase();
        console.log('🔍 [DEBUG] After converting to lowercase:', lowercase);

        // Replace spaces with hyphens
        const withHyphens = lowercase.replace(/\s+/g, '-');
        console.log('🔍 [DEBUG] After replacing spaces with hyphens:', withHyphens);

        // Remove special characters except hyphens and alphanumeric
        const clean = withHyphens.replace(/[^a-z0-9\-]/g, '');
        console.log('🔍 [DEBUG] After removing special characters:', clean);

        // Remove multiple consecutive hyphens
        const final = clean.replace(/-+/g, '-').replace(/^-|-$/g, '');
        console.log('🔍 [DEBUG] Final URL-friendly string:', final);

        return final;
    }

    /**
     * Remove Vietnamese diacritics
     */
    removeVietnameseDiacritics(str) {
        const vietnameseMap = {
            'à': 'a', 'á': 'a', 'ả': 'a', 'ã': 'a', 'ạ': 'a',
            'ă': 'a', 'ằ': 'a', 'ắ': 'a', 'ẳ': 'a', 'ẵ': 'a', 'ặ': 'a',
            'â': 'a', 'ầ': 'a', 'ấ': 'a', 'ẩ': 'a', 'ẫ': 'a', 'ậ': 'a',
            'đ': 'd',
            'è': 'e', 'é': 'e', 'ẻ': 'e', 'ẽ': 'e', 'ẹ': 'e',
            'ê': 'e', 'ề': 'e', 'ế': 'e', 'ể': 'e', 'ễ': 'e', 'ệ': 'e',
            'ì': 'i', 'í': 'i', 'ỉ': 'i', 'ĩ': 'i', 'ị': 'i',
            'ò': 'o', 'ó': 'o', 'ỏ': 'o', 'õ': 'o', 'ọ': 'o',
            'ô': 'o', 'ồ': 'o', 'ố': 'o', 'ổ': 'o', 'ỗ': 'o', 'ộ': 'o',
            'ơ': 'o', 'ờ': 'o', 'ớ': 'o', 'ở': 'o', 'ỡ': 'o', 'ợ': 'o',
            'ù': 'u', 'ú': 'u', 'ủ': 'u', 'ũ': 'u', 'ụ': 'u',
            'ư': 'u', 'ừ': 'u', 'ứ': 'u', 'ử': 'u', 'ữ': 'u', 'ự': 'u',
            'ỳ': 'y', 'ý': 'y', 'ỷ': 'y', 'ỹ': 'y', 'ỵ': 'y',
            'À': 'A', 'Á': 'A', 'Ả': 'A', 'Ã': 'A', 'Ạ': 'A',
            'Ă': 'A', 'Ằ': 'A', 'Ắ': 'A', 'Ẳ': 'A', 'Ẵ': 'A', 'Ặ': 'A',
            'Â': 'A', 'Ầ': 'A', 'Ấ': 'A', 'Ẩ': 'A', 'Ẫ': 'A', 'Ậ': 'A',
            'Đ': 'D',
            'È': 'E', 'É': 'E', 'Ẻ': 'E', 'Ẽ': 'E', 'Ẹ': 'E',
            'Ê': 'E', 'Ề': 'E', 'Ế': 'E', 'Ể': 'E', 'Ễ': 'E', 'Ệ': 'E',
            'Ì': 'I', 'Í': 'I', 'Ỉ': 'I', 'Ĩ': 'I', 'Ị': 'I',
            'Ò': 'O', 'Ó': 'O', 'Ỏ': 'O', 'Õ': 'O', 'Ọ': 'O',
            'Ô': 'O', 'Ồ': 'O', 'Ố': 'O', 'Ổ': 'O', 'Ỗ': 'O', 'Ộ': 'O',
            'Ơ': 'O', 'Ờ': 'O', 'Ớ': 'O', 'Ở': 'O', 'Ỡ': 'O', 'Ợ': 'O',
            'Ù': 'U', 'Ú': 'U', 'Ủ': 'U', 'Ũ': 'U', 'Ụ': 'U',
            'Ư': 'U', 'Ừ': 'U', 'Ứ': 'U', 'Ử': 'U', 'Ữ': 'U', 'Ự': 'U',
            'Ỳ': 'Y', 'Ý': 'Y', 'Ỷ': 'Y', 'Ỹ': 'Y', 'Ỵ': 'Y'
        };

        return str.split('').map(char => vietnameseMap[char] || char).join('');
    }

    /**
     * Store scroll position for form
     */
    storeScrollPosition() {
        if (this.searchFormContainer) {
            const rect = this.searchFormContainer.getBoundingClientRect();
            const scrollPosition = window.pageYOffset + rect.top - 100;
            sessionStorage.setItem('searchFormScrollPosition', scrollPosition);
        }
    }

    /**
     * Restore scroll position after form submission
     */
    restoreScrollPosition() {
        const savedScrollPosition = sessionStorage.getItem('searchFormScrollPosition');
        if (savedScrollPosition) {
            setTimeout(() => {
                window.scrollTo({
                    top: parseInt(savedScrollPosition),
                    behavior: 'smooth'
                });
                sessionStorage.removeItem('searchFormScrollPosition');
            }, 200);
        }
    }

    /**
     * Scroll to search form if search parameters exist
     */
    scrollToSearchForm(highlight = true) {
        if (this.searchFormContainer) {
            this.searchFormContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'nearest'
            });

            if (highlight) {
                this.highlightSearchForm();
            }
        }
    }

    /**
     * Highlight search form
     */
    highlightSearchForm() {
        if (this.searchFormContainer) {
            this.searchFormContainer.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.3)';
            this.searchFormContainer.style.transform = 'scale(1.02)';

            setTimeout(() => {
                this.searchFormContainer.style.boxShadow = '';
                this.searchFormContainer.style.transform = '';
            }, 2000);
        }
    }

    /**
     * Check if there are active search parameters
     */
    hasActiveSearch() {
        // Check URL rewrite format first
        const pathSegments = window.location.pathname.split('/');
        const hasSearchInPath = pathSegments.includes('search') || pathSegments.includes('tags') || pathSegments.includes('category');

        // Also check query parameters as fallback
        const urlParams = new URLSearchParams(window.location.search);
        const hasSearchInQuery = urlParams.has('search') || urlParams.has('type') || urlParams.has('tags') || urlParams.has('tag');

        return hasSearchInPath || hasSearchInQuery;
    }

    /**
     * Handle single tag parameter from other pages
     */
    handleTagParameter() {
        const urlParams = new URLSearchParams(window.location.search);
        const singleTag = urlParams.get('tag');

        if (singleTag && this.tagsInput) {
            // Set the tag in the input field
            const currentTags = this.tagsInput.value.trim();
            if (currentTags === '') {
                this.tagsInput.value = singleTag;
            } else {
                // Add to existing tags if not already present
                const tagsArray = currentTags.split(',').map(t => t.trim());
                if (!tagsArray.includes(singleTag)) {
                    this.tagsInput.value = currentTags + ', ' + singleTag;
                }
            }

            // Auto-submit with URL rewrite format
            setTimeout(() => {
                this.showLoadingState();
                this.saveScrollPosition();
                this.saveSearchState();
                this.submitWithQueryParams();
            }, 500); // Small delay to ensure form is ready
        }
    }

    /**
     * Handle mobile category select changes
     */
    handleMobileCategorySelect() {
        const mobileCategorySelect = document.getElementById('mobile-category-select');
        if (!mobileCategorySelect) return;

        mobileCategorySelect.addEventListener('change', (e) => {
            const selectedCategory = e.target.value;
            const pathSegments = window.location.pathname.split('/');
            let baseUrl = this.getBaseUrlWithLanguage();

            if (selectedCategory) {
                baseUrl += '/category/' + selectedCategory;
            }

            // Always add trailing slash before query parameters
            baseUrl += '/';

            // Build query parameters from current URL
            const params = new URLSearchParams();

            // Add search parameter if exists in URL
            const searchIndex = pathSegments.indexOf('search');
            if (searchIndex !== -1 && pathSegments[searchIndex + 1]) {
                params.set('search', pathSegments[searchIndex + 1]);
            }

            // Add tags parameter if exists in URL
            const tagsIndex = pathSegments.indexOf('tags');
            if (tagsIndex !== -1 && pathSegments[tagsIndex + 1]) {
                params.set('tags', pathSegments[tagsIndex + 1]);
            }

            // Add sort parameter if exists in URL
            const sortIndex = pathSegments.indexOf('sort');
            if (sortIndex !== -1 && pathSegments[sortIndex + 1]) {
                params.set('sort', pathSegments[sortIndex + 1]);
            }

            // Add paged parameter if exists in URL
            const pagedIndex = pathSegments.indexOf('paged');
            if (pagedIndex !== -1 && pathSegments[pagedIndex + 1]) {
                params.set('paged', pathSegments[pagedIndex + 1]);
            }

            // Store scroll position for filter section
            this.storeFilterScrollPosition();

            // Construct final URL
            const queryString = params.toString();
            const finalUrl = queryString ? baseUrl + '?' + queryString : baseUrl;

            // Use setTimeout to ensure proper handling
            setTimeout(() => {
                window.location.href = finalUrl;
            }, 100);
        });
    }

    /**
     * Handle type select changes
     */
    handleTypeSelectChanges() {
        if (!this.typeSelect) return;

        this.typeSelect.addEventListener('change', () => {
            this.showLoadingState();
            this.saveScrollPosition();
            this.saveSearchState();
            this.submitWithQueryParams();
        });
    }

    /**
     * Handle Enter key in search input
     */
    handleSearchInputKeypress() {
        if (!this.searchInput) return;

        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                console.log('🔍 [DEBUG] Enter key pressed');
                console.log('🔍 [DEBUG] Search input value on Enter:', this.searchInput.value);
                e.preventDefault();
                this.showLoadingState();
                this.saveScrollPosition();
                this.saveSearchState();
                this.submitWithQueryParams();
            }
        });

        // Auto-save search state
        this.searchInput.addEventListener('input', () => {
            this.saveSearchState();
        });
    }

    /**
     * Handle form submission for scroll position
     */
    handleFormScrollPosition() {
        if (!this.searchForm) return;

        this.searchForm.addEventListener('submit', () => {
            this.showLoadingState();
            this.saveScrollPosition();
        });
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        const submitButton = this.searchForm?.querySelector('button[type="submit"]');
        if (!submitButton) return;

        const originalContent = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
        submitButton.classList.add('opacity-75');

        // Add loading overlay
        if (this.searchFormContainer) {
            this.searchFormContainer.classList.add('relative');

            const existingOverlay = document.getElementById('search-loading-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'search-loading-overlay';
            loadingOverlay.className = 'absolute inset-0 bg-white bg-opacity-75 backdrop-blur-sm flex items-center justify-center rounded-2xl z-10';
            loadingOverlay.innerHTML = `
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-3"></div>
                    <p class="text-slate-600 font-medium">Searching blogs...</p>
                </div>
            `;

            this.searchFormContainer.appendChild(loadingOverlay);
        }
    }

    /**
     * Initialize keyboard shortcuts
     */
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + F to focus search input
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                if (this.searchInput) {
                    this.scrollToSearchForm(false);
                    setTimeout(() => {
                        this.searchInput.focus();
                        this.searchInput.select();
                    }, 300);
                }
            }

            // Escape to clear search
            if (e.key === 'Escape') {
                if (this.searchInput && this.searchInput === document.activeElement) {
                    this.searchInput.value = '';
                    this.searchInput.blur();
                    this.saveSearchState();
                }
            }
        });
    }

    /**
     * Initialize search suggestions
     */
    initSearchSuggestions() {
        if (!this.searchInput) return;

        let suggestionTimeout;

        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(suggestionTimeout);
            const query = e.target.value.trim();

            if (query.length >= 2) {
                suggestionTimeout = setTimeout(() => {
                    // You can implement AJAX search suggestions here
                    // this.fetchSearchSuggestions(query);
                }, 300);
            } else {
                this.hideSuggestions();
            }
        });

        // Hide suggestions on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-suggestions-container')) {
                this.hideSuggestions();
            }
        });
    }

    /**
     * Hide search suggestions
     */
    hideSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.remove();
        }
    }

    /**
     * Add tag to input
     */
    addTag(tag) {
        if (!this.tagsInput) return;

        const currentTags = this.tagsInput.value.trim();

        if (currentTags === '') {
            this.tagsInput.value = tag;
        } else {
            const tagsArray = currentTags.split(',').map(t => t.trim());
            if (!tagsArray.includes(tag)) {
                this.tagsInput.value = currentTags + ', ' + tag;
            }
        }

        this.tagsInput.focus();
        this.saveSearchState();
    }

    /**
     * Save search state
     */
    saveSearchState() {
        const searchState = {
            search: this.searchInput?.value || '',
            type: this.typeSelect?.value || '',
            tags: this.tagsInput?.value || '',
            timestamp: Date.now()
        };

        sessionStorage.setItem('blogSearchState', JSON.stringify(searchState));
    }

    /**
     * Restore search state
     */
    restoreSearchState() {
        const savedState = sessionStorage.getItem('blogSearchState');
        if (!savedState) return;

        try {
            const state = JSON.parse(savedState);
            const now = Date.now();

            // Only restore if saved within last 10 minutes
            if (now - state.timestamp < 600000) {
                if (this.searchInput && !this.searchInput.value) {
                    this.searchInput.value = state.search;
                }
                if (this.typeSelect && !this.typeSelect.value) {
                    this.typeSelect.value = state.type;
                }
                if (this.tagsInput && !this.tagsInput.value) {
                    this.tagsInput.value = state.tags;
                }
            } else {
                // Clear old state
                sessionStorage.removeItem('blogSearchState');
            }
        } catch (e) {
            console.warn('Failed to restore search state:', e);
            sessionStorage.removeItem('blogSearchState');
        }
    }

    /**
     * Store scroll position for filter section
     */
    storeFilterScrollPosition() {
        const filterSection = document.querySelector('.sticky.top-20');
        if (filterSection) {
            const filterRect = filterSection.getBoundingClientRect();
            const scrollPosition = window.pageYOffset + filterRect.top - 100;
            sessionStorage.setItem('blogsFilterScrollPosition', scrollPosition);
        }
    }

    /**
     * Restore scroll position for filter section
     */
    restoreFilterScrollPosition() {
        const savedScrollPosition = sessionStorage.getItem('blogsFilterScrollPosition');
        if (savedScrollPosition) {
            setTimeout(() => {
                window.scrollTo({
                    top: parseInt(savedScrollPosition),
                    behavior: 'smooth'
                });
                sessionStorage.removeItem('blogsFilterScrollPosition');
            }, 200);
        }
    }
}

// Global function for backward compatibility
function addTag(tag) {
    if (window.blogsManager) {
        window.blogsManager.addTag(tag);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Feather icons
    feather.replace();
    if (typeof feather !== 'undefined') {
        
    }

    // Initialize blogs manager
    window.blogsManager = new BlogsManager();

    // Restore scroll position for filter section
    window.blogsManager.restoreFilterScrollPosition();
});

// Handle back/forward browser navigation
window.addEventListener('popstate', function () {
    // Small delay to let the page settle
    setTimeout(() => {
        if (window.blogsManager) {
            const hasSearch = window.blogsManager.hasActiveSearch();
            if (hasSearch) {
                window.blogsManager.scrollToSearchForm(true);
            }
        }
    }, 100);
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BlogsManager;
}
