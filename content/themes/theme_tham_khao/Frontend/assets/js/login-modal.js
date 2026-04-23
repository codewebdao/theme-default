/**
 * Login Modal Module
 * 
 * Handles login modal UI with multiple authentication options:
 * - Password login
 * - Social login (Google, Facebook, etc.)
 * 
 * Usage:
 *   LoginModal.open();
 *   LoginModal.close();
 * 
 * @module LoginModal
 */

class LoginModal {
    constructor() {
        this.modalId = 'loginModal';
        this.overlayId = 'loginModalOverlay';
        this.init();
    }

    /**
     * Initialize modal - creates DOM if not exists
     */
    init() {
        // Check if modal already exists
        if (document.getElementById(this.modalId)) {
            return;
        }

        // Create modal HTML
        this.createModalHTML();
        this.bindEvents();
    }

    /**
     * Create modal HTML structure
     */
    createModalHTML() {
        const modalHTML = `
            <!-- Login Modal Overlay -->
            <div id="${this.overlayId}" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden" onclick="LoginModal.close()"></div>
            
            <!-- Login Modal -->
            <div id="${this.modalId}" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900">Login</h2>
                        <button type="button" onclick="LoginModal.close()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <!-- Login Options -->
                        <div class="space-y-4">
                            <!-- Password Login Form -->
                            <div id="loginPasswordForm" class="space-y-4">
                                <div>
                                    <label for="loginUsername" class="block text-sm font-medium text-gray-700 mb-2">
                                        Username or Email
                                    </label>
                                    <input 
                                        type="text" 
                                        id="loginUsername" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                                        placeholder="Enter your username or email"
                                    >
                                </div>
                                <div>
                                    <label for="loginPassword" class="block text-sm font-medium text-gray-700 mb-2">
                                        Password
                                    </label>
                                    <input 
                                        type="password" 
                                        id="loginPassword" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                                        placeholder="Enter your password"
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="rememberMe" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                                    </label>
                                    <a href="/forgot-password" class="text-sm text-blue-600 hover:text-blue-800">Forgot password?</a>
                                </div>
                                <button 
                                    type="button" 
                                    id="loginPasswordBtn" 
                                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                >
                                    Login with Password
                                </button>
                            </div>

                            <!-- Divider -->
                            <div class="flex items-center my-6">
                                <div class="flex-1 border-t border-gray-300"></div>
                                <span class="px-4 text-sm text-gray-500">or</span>
                                <div class="flex-1 border-t border-gray-300"></div>
                            </div>

                            <!-- Social Login Options -->
                            <div id="loginSocialOptions" class="space-y-3">
                                <!-- Google Login -->
                                <button 
                                    type="button" 
                                    id="loginGoogleBtn" 
                                    class="w-full flex items-center justify-center gap-3 px-4 py-3 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all"
                                >
                                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    <span>Continue with Google</span>
                                </button>

                                <!-- Facebook Login -->
                                <button 
                                    type="button" 
                                    id="loginFacebookBtn" 
                                    class="w-full flex items-center justify-center gap-3 px-4 py-3 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all"
                                >
                                    <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                    <span>Continue with Facebook</span>
                                </button>

                                <!-- GitHub Login (Optional) -->
                                <button 
                                    type="button" 
                                    id="loginGithubBtn" 
                                    class="w-full flex items-center justify-center gap-3 px-4 py-3 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all"
                                >
                                    <svg class="w-5 h-5" fill="#181717" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                    <span>Continue with GitHub</span>
                                </button>
                            </div>
                        </div>

                        <!-- Sign Up Link -->
                        <div class="mt-6 text-center">
                            <p class="text-sm text-gray-600">
                                Don't have an account? 
                                <a href="/register" class="text-blue-600 hover:text-blue-800 font-medium">Sign up</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Append to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        const modal = document.getElementById(this.modalId);
        const overlay = document.getElementById(this.overlayId);

        if (!modal) return;

        // Password login button
        const passwordBtn = document.getElementById('loginPasswordBtn');
        if (passwordBtn) {
            passwordBtn.addEventListener('click', () => {
                this.handlePasswordLogin();
            });
        }

        // Social login buttons
        const googleBtn = document.getElementById('loginGoogleBtn');
        if (googleBtn) {
            googleBtn.addEventListener('click', () => {
                this.handleSocialLogin('google');
            });
        }

        const facebookBtn = document.getElementById('loginFacebookBtn');
        if (facebookBtn) {
            facebookBtn.addEventListener('click', () => {
                this.handleSocialLogin('facebook');
            });
        }

        const githubBtn = document.getElementById('loginGithubBtn');
        if (githubBtn) {
            githubBtn.addEventListener('click', () => {
                this.handleSocialLogin('github');
            });
        }

        // Enter key on password form
        const usernameInput = document.getElementById('loginUsername');
        const passwordInput = document.getElementById('loginPassword');
        
        if (usernameInput && passwordInput) {
            [usernameInput, passwordInput].forEach(input => {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.handlePasswordLogin();
                    }
                });
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                this.close();
            }
        });
    }

    /**
     * Open modal
     */
    open() {
        const modal = document.getElementById(this.modalId);
        const overlay = document.getElementById(this.overlayId);

        if (modal && overlay) {
            modal.classList.remove('hidden');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        }
    }

    /**
     * Close modal
     */
    close() {
        const modal = document.getElementById(this.modalId);
        const overlay = document.getElementById(this.overlayId);

        if (modal && overlay) {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scroll

            // Clear form
            this.clearForm();
        }
    }

    /**
     * Clear login form
     */
    clearForm() {
        const usernameInput = document.getElementById('loginUsername');
        const passwordInput = document.getElementById('loginPassword');
        const rememberMe = document.getElementById('rememberMe');

        if (usernameInput) usernameInput.value = '';
        if (passwordInput) passwordInput.value = '';
        if (rememberMe) rememberMe.checked = false;
    }

    /**
     * Handle password login
     * TODO: Implement API call
     */
    handlePasswordLogin() {
        const username = document.getElementById('loginUsername')?.value.trim();
        const password = document.getElementById('loginPassword')?.value;
        const rememberMe = document.getElementById('rememberMe')?.checked;

        // Validation
        if (!username) {
            alert('Please enter your username or email');
            document.getElementById('loginUsername')?.focus();
            return;
        }

        if (!password) {
            alert('Please enter your password');
            document.getElementById('loginPassword')?.focus();
            return;
        }

        // TODO: Implement API call
        console.log('Password login:', { username, password, rememberMe });
        
        // Placeholder: Show loading state
        const btn = document.getElementById('loginPasswordBtn');
        if (btn) {
            const originalText = btn.textContent;
            btn.textContent = 'Logging in...';
            btn.disabled = true;

            // Simulate API call (remove this when implementing real API)
            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
                alert('Login API will be implemented here');
            }, 1000);
        }
    }

    /**
     * Handle social login
     * TODO: Implement OAuth flow
     * 
     * @param {string} provider - Social provider ('google', 'facebook', 'github')
     */
    handleSocialLogin(provider) {
        // TODO: Implement OAuth flow
        console.log('Social login:', provider);
        alert(`${provider.charAt(0).toUpperCase() + provider.slice(1)} login will be implemented here`);
        
        // Placeholder: Redirect to OAuth provider
        // window.location.href = `/api/auth/${provider}`;
    }
}

// Create global instance only if not already exists
if (typeof window.LoginModal === 'undefined') {
    const loginModalInstance = new LoginModal();
    window.LoginModal = loginModalInstance;

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            loginModalInstance.init();
        });
    } else {
        loginModalInstance.init();
    }
}
