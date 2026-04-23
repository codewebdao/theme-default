/**
 * Enhanced Blog Search JavaScript (Updated)
 * Provides smooth scrolling, state management, and improved UX
 * Updated to use query parameters for search and sort, category remains in URL path
 */

class BlogSearchManager {
    constructor() {
        this.searchForm = document.getElementById('search-form');
        this.searchFormContainer = document.getElementById('blog-search-form');
        this.searchInput = document.querySelector('input[name="search"]');
        this.typeSelect = document.querySelector('select[name="type"]');
        this.tagsInput = document.querySelector('input[name="tags"]');

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.handlePageLoad();
        this.setupKeyboardShortcuts();
    }

    setupEventListeners() {
        // Form submission handling
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => {
                this.handleFormSubmission(e);
            });
        }

        // Auto-submit on filter change
        if (this.typeSelect) {
            this.typeSelect.addEventListener('change', () => {
                this.showLoadingState();
                this.saveScrollPosition();
                this.saveSearchState();
                this.submitWithQueryParams();
            });
        }

        // Search input enhancements
        if (this.searchInput) {
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

        // Tags input enhancement
        if (this.tagsInput) {
            this.tagsInput.addEventListener('input', () => {
                this.saveSearchState();
            });
        }
    }

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

    handleFormSubmission(e) {
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
    }

    // Function to remove Vietnamese diacritics
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

    // Function to create URL-friendly string
    createUrlFriendlyString(str) {
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

    hasActiveSearch() {
        // Check URL rewrite format first
        const pathSegments = window.location.pathname.split('/');
        const hasSearchInPath = pathSegments.includes('search') || pathSegments.includes('tags') || pathSegments.includes('category');

        // Also check query parameters as fallback
        const urlParams = new URLSearchParams(window.location.search);
        const hasSearchInQuery = urlParams.has('search') || urlParams.has('type') || urlParams.has('tags') || urlParams.has('tag');

        return hasSearchInPath || hasSearchInQuery;
    }

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

    saveScrollPosition() {
        if (this.searchFormContainer) {
            const rect = this.searchFormContainer.getBoundingClientRect();
            const scrollPosition = window.pageYOffset + rect.top - 100;
            sessionStorage.setItem('searchFormScrollPosition', scrollPosition);
        }
    }

    saveSearchState() {
        const searchState = {
            search: this.searchInput?.value || '',
            type: this.typeSelect?.value || '',
            tags: this.tagsInput?.value || '',
            timestamp: Date.now()
        };

        sessionStorage.setItem('blogSearchState', JSON.stringify(searchState));
    }

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

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + F to focus search
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

            // Escape to clear and blur search
            if (e.key === 'Escape' && document.activeElement === this.searchInput) {
                this.searchInput.value = '';
                this.searchInput.blur();
                this.saveSearchState();
            }
        });
    }

    // Public method to add tags (called from onclick handlers)
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

    // Handle single tag parameter from other pages (like blog detail)
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
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    window.blogSearchManager = new BlogSearchManager();

    // Make addTag function globally available for onclick handlers
    window.addTag = function (tag) {
        window.blogSearchManager.addTag(tag);
    };
});

// Handle back/forward browser navigation
window.addEventListener('popstate', function () {
    // Small delay to let the page settle
    setTimeout(() => {
        if (window.blogSearchManager) {
            const hasSearch = window.blogSearchManager.hasActiveSearch();
            if (hasSearch) {
                window.blogSearchManager.scrollToSearchForm(true);
            }
        }
    }, 100);
});
