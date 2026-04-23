/**
 * Blog Detail JavaScript Functions
 * Handles all interactive functionality for blog detail pages
 * Optimized and refactored for better maintainability
 */

class BlogDetail {
    constructor() {
        this.blogId = this.getBlogId();
        this.config = this.getConfig();
        this.state = this.getInitialState();
        this.init();
    }

    /**
     * Get blog ID from DOM or URL
     */
    getBlogId() {
        return blogId;
    }

    /**
     * Get configuration object
     */
    getConfig() {
        return {
            selectors: {
                readingProgress: '#readingProgress .h-full',
                blogContent: '#blog-content',
                tableOfContents: '#table-of-contents',
                shareModal: '#shareModal',
                bookmarkModal: '#bookmarkModal',
                commentForm: '#comment-form',
                newsletterForm: '.space-y-3'
            },
            storageKeys: {
                saveLater: 'save_later',
                likedBlogs: 'liked_blogs',
                readBlogs: 'read_blogs'
            },
            animationDuration: 300,
            scrollThreshold: 500,
            maxReadBlogs: 5
        };
    }

    /**
     * Get initial state
     */
    getInitialState() {
        return {
            isBookmarked: false,
            isLiked: false,
            isSavedForLater: false,
            isRead: false
        };
    }

    /**
     * Initialize all functionality
     */
    init() {
        this.loadSavedStates();
        this.setupModules();
        this.markAsRead(); // Mark current blog as read
    }

    /**
     * Setup all modules
     */
    setupModules() {
        this.setupReadingProgress();
        this.setupTableOfContents();
        this.setupModals();
        this.setupSocialShare();
        this.setupInteractions();
        this.setupQuickActions();
        this.setupScrollEffects();
        this.setupLazyLoading();
    }

    // ==================== READING PROGRESS ====================

    setupReadingProgress() {
        const progressBar = document.querySelector(this.config.selectors.readingProgress);
        if (!progressBar) return;

        const updateProgress = this.debounce(() => {
            const article = document.querySelector(this.config.selectors.blogContent);
            if (!article) return;

            const scrollTop = window.scrollY;
            const articleTop = article.offsetTop;
            const articleHeight = article.offsetHeight;
            const windowHeight = window.innerHeight;

            const progress = Math.max(0, Math.min(100,
                ((scrollTop - articleTop + windowHeight / 2) / articleHeight) * 100
            ));

            progressBar.style.width = `${progress}%`;
        }, 16);

        window.addEventListener('scroll', updateProgress);
        updateProgress();
    }

    // ==================== TABLE OF CONTENTS ====================

    setupTableOfContents() {
        const toc = document.querySelector(this.config.selectors.tableOfContents);
        const content = document.querySelector(this.config.selectors.blogContent);

        if (!toc || !content) return;

        const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
        if (headings.length < 3) {
            const tocContainer = toc.closest('.bg-gray-50');
            if (tocContainer) tocContainer.style.display = 'none';
            return;
        }

        this.generateTableOfContents(toc, headings);
        this.setupTocNavigation(toc, headings);
    }

    generateTableOfContents(toc, headings) {
        let tocHTML = '';
        headings.forEach((heading, index) => {
            const id = `heading-${index}`;
            heading.id = id;
            const level = parseInt(heading.tagName.charAt(1));
            const indent = (level - 1) * 16;

            tocHTML += `
                <a href="#${id}" class="toc-link block py-1 text-sm text-gray-600 hover:text-blue-600 transition-colors" 
                   style="padding-left: ${indent}px" data-heading="${id}">
                    ${heading.textContent}
                </a>
            `;
        });

        toc.innerHTML = tocHTML;
    }

    setupTocNavigation(toc, headings) {
        // Smooth scroll for TOC links
        toc.addEventListener('click', (e) => {
            if (e.target.classList.contains('toc-link')) {
                e.preventDefault();
                const targetId = e.target.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            }
        });

        // Highlight active heading
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const tocLink = toc.querySelector(`[data-heading="${entry.target.id}"]`);
                if (tocLink && entry.isIntersecting) {
                    this.updateActiveTocLink(toc, tocLink);
                }
            });
        }, { rootMargin: '-20% 0px -80% 0px' });

        headings.forEach(heading => observer.observe(heading));
    }

    updateActiveTocLink(toc, activeLink) {
        toc.querySelectorAll('.toc-link').forEach(link => {
            link.classList.remove('text-blue-600', 'font-semibold');
            link.classList.add('text-gray-600');
        });
        activeLink.classList.remove('text-gray-600');
        activeLink.classList.add('text-blue-600', 'font-semibold');
    }

    // ==================== MODAL SYSTEM ====================

    setupModals() {
        const { shareModal, bookmarkModal } = this.getModals();

        this.setupModalOpeners(shareModal, bookmarkModal);
        this.setupModalClosers(shareModal, bookmarkModal);
    }

    getModals() {
        return {
            shareModal: document.querySelector(this.config.selectors.shareModal),
            bookmarkModal: document.querySelector(this.config.selectors.bookmarkModal)
        };
    }

    setupModalOpeners(shareModal, bookmarkModal) {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.blog-share-btn, .blog-share-btn-footer')) {
                e.preventDefault();
                this.openModal(shareModal);
            }
        });
    }

    setupModalClosers(shareModal, bookmarkModal) {
        // Close on close button click
        document.addEventListener('click', (e) => {
            if (e.target.closest('.close-modal')) {
                this.closeModal(shareModal);
                this.closeModal(bookmarkModal);
            }

            // Close on backdrop click
            if (e.target === shareModal || e.target === bookmarkModal) {
                this.closeModal(e.target);
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal(shareModal);
                this.closeModal(bookmarkModal);
            }
        });
    }

    openModal(modal) {
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modal) {
        if (modal && !modal.classList.contains('hidden')) {
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, this.config.animationDuration);
        }
    }

    // ==================== SOCIAL SHARE ====================

    setupSocialShare() {
        const blogData = this.getBlogData();

        this.setupShareButtons(blogData);
        this.setupCopyLink(blogData.url);
    }

    getBlogData() {
        return {
            title: document.querySelector('h1')?.textContent || '',
            url: window.location.href,
            description: document.querySelector('meta[name="description"]')?.content || ''
        };
    }

    setupShareButtons(blogData) {
        const shareHandlers = {
            '.share-facebook': () => this.shareOnFacebook(blogData.url),
            '.share-twitter': () => this.shareOnTwitter(blogData.url, blogData.title),
            '.share-linkedin': () => this.shareOnLinkedIn(blogData.url),
            '.share-whatsapp': () => this.shareOnWhatsApp(blogData.url, blogData.title)
        };

        Object.entries(shareHandlers).forEach(([selector, handler]) => {
            document.addEventListener('click', (e) => {
                if (e.target.closest(selector)) {
                    handler();
                }
            });
        });
    }

    setupCopyLink(url) {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.copy-link-btn, .copy-link')) {
                e.preventDefault();
                this.copyToClipboard(url);
            }
        });
    }

    shareOnFacebook(url) {
        this.openShareWindow(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`);
    }

    shareOnTwitter(url, title) {
        this.openShareWindow(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`);
    }

    shareOnLinkedIn(url) {
        this.openShareWindow(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`);
    }

    shareOnWhatsApp(url, title) {
        this.openShareWindow(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`);
    }

    openShareWindow(url) {
        window.open(url, '_blank', 'width=600,height=400,scrollbars=yes,resizable=yes');
    }

    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showToast('Link copied to clipboard!', 'success');
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showToast('Link copied!', 'success');
        }
    }

    // ==================== INTERACTIONS ====================

    setupInteractions() {
        this.setupBookmarkInteraction();
        this.setupLikeInteraction();
        this.setupCommentForm();
        this.setupNewsletterForm();
        this.setupPrintInteraction();
    }

    setupBookmarkInteraction() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.blog-bookmark-btn')) {
                e.preventDefault();
                this.toggleBookmark();
            }
        });
    }

    setupLikeInteraction() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.blog-like-btn')) {
                e.preventDefault();
                this.toggleLike();
            }
        });
    }

    setupCommentForm() {
        const commentForm = document.querySelector(this.config.selectors.commentForm);
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment(commentForm);
            });
        }
    }

    setupNewsletterForm() {
        const newsletterForm = document.querySelector(this.config.selectors.newsletterForm);
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.subscribeNewsletter(newsletterForm);
            });
        }
    }

    setupPrintInteraction() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('[class*="fa-print"]')) {
                e.preventDefault();
                this.printBlog();
            }
        });
    }

    // ==================== QUICK ACTIONS ====================

    setupQuickActions() {
        const quickActions = {
            '.quick-action-save': () => this.toggleSaveForLater(),
            '.quick-action-share': () => this.shareBlog(),
            '.quick-action-like': () => this.toggleQuickLike(),
            '.quick-action-print': () => this.printBlog()
        };

        Object.entries(quickActions).forEach(([selector, handler]) => {
            document.addEventListener('click', (e) => {
                if (e.target.closest(selector)) {
                    e.preventDefault();
                    handler();
                }
            });
        });
    }

    // ==================== STORAGE MANAGEMENT ====================

    loadSavedStates() {
        this.state.isBookmarked = this.isInSavedList(this.config.storageKeys.saveLater);
        this.state.isLiked = this.isInSavedList(this.config.storageKeys.likedBlogs);
        this.state.isSavedForLater = this.state.isBookmarked;
        this.state.isRead = this.isInSavedList(this.config.storageKeys.readBlogs);

        this.updateAllUI();
    }

    getSavedList(key) {
        const saved = localStorage.getItem(key);
        return saved ? saved.split(',').filter(id => id.trim() !== '') : [];
    }

    addToSavedList(key, blogId) {
        const currentList = this.getSavedList(key);
        if (!currentList.includes(blogId.toString())) {
            currentList.push(blogId.toString());

            // Special handling for read blogs - limit to maxReadBlogs
            if (key === this.config.storageKeys.readBlogs && currentList.length > this.config.maxReadBlogs) {
                // Remove oldest items (first items in array)
                const itemsToRemove = currentList.length - this.config.maxReadBlogs;
                currentList.splice(0, itemsToRemove);
            }

            localStorage.setItem(key, currentList.join(','));
        }
        return currentList;
    }

    removeFromSavedList(key, blogId) {
        const currentList = this.getSavedList(key);
        const filteredList = currentList.filter(id => id !== blogId.toString());
        localStorage.setItem(key, filteredList.join(','));
        return filteredList;
    }

    isInSavedList(key) {
        const currentList = this.getSavedList(key);
        return currentList.includes(this.blogId.toString());
    }

    // ==================== TOGGLE METHODS ====================

    toggleBookmark() {
        const isCurrentlySaved = this.isInSavedList(this.config.storageKeys.saveLater);

        if (isCurrentlySaved) {
            this.removeFromSavedList(this.config.storageKeys.saveLater, this.blogId);
            this.state.isBookmarked = false;
            this.showToast('Bookmark removed!', 'info');
        } else {
            this.addToSavedList(this.config.storageKeys.saveLater, this.blogId);
            this.state.isBookmarked = true;
            this.showToast('Blog bookmarked!', 'success');
        }

        this.updateBookmarkUI();
    }

    toggleLike() {
        const isCurrentlyLiked = this.isInSavedList(this.config.storageKeys.likedBlogs);

        if (isCurrentlyLiked) {
            this.removeFromSavedList(this.config.storageKeys.likedBlogs, this.blogId);
            this.state.isLiked = false;
        } else {
            this.addToSavedList(this.config.storageKeys.likedBlogs, this.blogId);
            this.state.isLiked = true;
            this.showToast('Thanks for liking!', 'success');
        }

        this.updateLikeUI();
    }

    toggleSaveForLater() {
        const isCurrentlySaved = this.isInSavedList(this.config.storageKeys.saveLater);

        if (isCurrentlySaved) {
            this.removeFromSavedList(this.config.storageKeys.saveLater, this.blogId);
            this.state.isSavedForLater = false;
            this.showToast('Removed from saved list!', 'info');
        } else {
            this.addToSavedList(this.config.storageKeys.saveLater, this.blogId);
            this.state.isSavedForLater = true;
            this.showToast('Saved for later!', 'success');
        }

        this.updateSaveForLaterUI();
    }

    toggleQuickLike() {
        this.toggleLike();
    }

    // ==================== UI UPDATES ====================

    updateAllUI() {
        this.updateBookmarkUI();
        this.updateLikeUI();
        this.updateSaveForLaterUI();
        this.updateReadStatusUI();
    }

    /**
     * Update read status UI
     */
    updateReadStatusUI() {
        const readStatusElements = document.querySelectorAll('.blog-read-status, .read-status');
        readStatusElements.forEach(element => {
            if (this.state.isRead) {
                element.classList.add('read');
                element.classList.remove('unread');
                element.textContent = 'Đã đọc';
            } else {
                element.classList.add('unread');
                element.classList.remove('read');
                element.textContent = 'Chưa đọc';
            }
        });
    }

    updateBookmarkUI() {
        const bookmarkBtns = document.querySelectorAll('.blog-bookmark-btn, .quick-action-save');
        bookmarkBtns.forEach(btn => {
            const icon = btn.querySelector('i, svg');
            if (icon) {
                if (this.state.isBookmarked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    btn.classList.add('active', 'bg-yellow-500', 'text-white');
                    btn.classList.remove('border-2', 'border-white', 'hover:bg-white', 'hover:text-blue-900');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    btn.classList.remove('active', 'bg-yellow-500', 'text-white');
                    btn.classList.add('border-2', 'border-white', 'hover:bg-white', 'hover:text-blue-900');
                }
            }
        });
    }

    updateLikeUI() {
        const likeBtns = document.querySelectorAll('.blog-like-btn, .quick-action-like');
        likeBtns.forEach(btn => {
            const icon = btn.querySelector('i, svg');
            if (icon) {
                if (this.state.isLiked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    btn.classList.add('active', 'text-red-500');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    btn.classList.remove('active', 'text-red-500');
                }
            }
        });
    }

    updateSaveForLaterUI() {
        const saveBtns = document.querySelectorAll('.quick-action-save');
        saveBtns.forEach(btn => {
            const icon = btn.querySelector('svg');
            const text = btn.querySelector('span');

            if (icon && text) {
                if (this.state.isSavedForLater) {
                    icon.classList.add('text-blue-600');
                    text.classList.add('text-blue-600');
                    btn.classList.add('active', 'bg-blue-50');
                } else {
                    icon.classList.remove('text-blue-600');
                    text.classList.remove('text-blue-600');
                    btn.classList.remove('active', 'bg-blue-50');
                }
            }
        });
    }

    // ==================== UTILITY METHODS ====================

    shareBlog() {
        const blogData = this.getBlogData();
        const shareModal = document.querySelector(this.config.selectors.shareModal);

        if (shareModal) {
            this.openModal(shareModal);
        } else if (navigator.share) {
            navigator.share({
                title: blogData.title,
                url: blogData.url
            });
        } else {
            this.copyToClipboard(blogData.url);
        }
    }

    printBlog() {
        const elementsToHide = [
            '.lg\\:col-span-1', // sidebar
            'header',
            'footer'
        ];

        // Hide elements for print
        elementsToHide.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) element.style.display = 'none';
        });

        window.print();

        // Restore elements after print
        setTimeout(() => {
            elementsToHide.forEach(selector => {
                const element = document.querySelector(selector);
                if (element) element.style.display = '';
            });
        }, 1000);
    }

    submitComment(form) {
        const name = form.querySelector('input[type="text"]')?.value;
        const email = form.querySelector('input[type="email"]')?.value;
        const comment = form.querySelector('textarea')?.value;

        if (!name || !email || !comment) {
            this.showToast('Please fill in all fields', 'error');
            return;
        }

        // TODO: Add AJAX call to submit comment
        this.showToast('Comment submitted successfully!', 'success');
        form.reset();
    }

    subscribeNewsletter(form) {
        const email = form.querySelector('input[type="email"]')?.value;

        if (!email || !this.isValidEmail(email)) {
            this.showToast('Please enter a valid email address', 'error');
            return;
        }

        // TODO: Add AJAX call to subscribe
        this.showToast('Successfully subscribed to newsletter!', 'success');
        form.reset();
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // ==================== SCROLL EFFECTS ====================

    setupScrollEffects() {
        this.createBackToTopButton();
        this.setupScrollAnimations();
    }

    createBackToTopButton() {
        const btn = document.createElement('button');
        btn.className = 'back-to-top';
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>`;
        btn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(btn);

        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', () => {
            if (window.scrollY > this.config.scrollThreshold) {
                btn.classList.add('show');
            } else {
                btn.classList.remove('show');
            }
        });

        return btn;
    }

    setupScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        const animateElements = document.querySelectorAll('.bg-white, .prose, article');
        animateElements.forEach(el => observer.observe(el));
    }

    // ==================== LAZY LOADING ====================

    setupLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('opacity-0');
                    img.classList.add('opacity-100');
                    imageObserver.unobserve(img);
                }
            });
        }, { rootMargin: '50px 0px' });

        images.forEach(img => {
            img.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            imageObserver.observe(img);
        });
    }

    // ==================== TOAST NOTIFICATIONS ====================

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // ==================== UTILITY FUNCTIONS ====================

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function () {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Mark current blog as read and add to localStorage
     */
    markAsRead() {
        const readBlogs = this.getSavedList(this.config.storageKeys.readBlogs);
        if (!readBlogs.includes(this.blogId.toString())) {
            this.addToSavedList(this.config.storageKeys.readBlogs, this.blogId);
            this.state.isRead = true;

            // Log information about read blogs
            const totalRead = this.getSavedList(this.config.storageKeys.readBlogs).length;
            console.log(`📖 Blog ${this.blogId} marked as read. Total read blogs: ${totalRead}/${this.config.maxReadBlogs}`);

            // Show toast notification if needed
            if (totalRead === this.config.maxReadBlogs) {
                this.showToast(`Đã đạt giới hạn ${this.config.maxReadBlogs} blog đã đọc. Blog cũ nhất sẽ được thay thế.`, 'info');
            }
        }
    }

    /**
     * Get read blogs count
     */
    getReadBlogsCount() {
        return this.getSavedList(this.config.storageKeys.readBlogs).length;
    }

    /**
     * Check if current blog is read
     */
    isBlogRead() {
        return this.isInSavedList(this.config.storageKeys.readBlogs);
    }

    /**
     * Get read blogs list
     */
    getReadBlogsList() {
        return this.getSavedList(this.config.storageKeys.readBlogs);
    }

    /**
     * Remove blog from read list
     */
    removeFromReadList(blogId) {
        return this.removeFromSavedList(this.config.storageKeys.readBlogs, blogId);
    }

    /**
     * Clear all read blogs
     */
    clearReadBlogs() {
        localStorage.removeItem(this.config.storageKeys.readBlogs);
        this.state.isRead = false;
        this.updateReadStatusUI();
        console.log('All read blogs cleared');
    }
}

// Initialize Blog Detail functionality when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BlogDetail();
});

// Export for use in other scripts if needed
window.BlogDetail = BlogDetail;
