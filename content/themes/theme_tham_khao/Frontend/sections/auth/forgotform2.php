<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="container relative h-screen flex-col items-center justify-center md:grid lg:max-w-none lg:grid-cols-2 lg:px-0">

        <!-- Left Panel - Branding -->
        <div class="relative hidden h-full flex-col p-10 pb-24 text-slate-800 lg:flex overflow-hidden">
            <!-- Modern gradient background -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600"></div>

            <!-- Decorative elements -->
            <div class="absolute top-20 left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-20 right-10 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute top-1/2 right-20 w-16 h-16 bg-white/5 rounded-full blur-lg"></div>

            <!-- Header -->
            <div class="relative z-20 flex items-center text-lg font-semibold text-white">
                <a class="flex items-center gap-3 hover:opacity-80 transition-opacity" href="/">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>
                    </div>
                    <span class="text-xl font-bold"><?php _e('cms_name') ?></span>
                </a>
            </div>

            <!-- Main content -->
            <div class="relative z-20 flex-1 flex flex-col justify-center">
                <div class="space-y-8">
                    <!-- Logo -->
                    <div class="text-center">
                        <?= _img(
                            theme_assets('images/logo/logo-icon.png'),
                            'Logo CMS',
                            false,
                            'mx-auto mb-6 h-32 w-32 object-contain drop-shadow-lg'
                        ) ?>
                    </div>

                    <!-- Description -->
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-white mb-4 leading-tight">
                            Welcome to Modern CMS
                        </h1>
                        <p class="text-xl text-blue-100 leading-relaxed max-w-md mx-auto">
                            <?php _e('cms_description') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Feature cards -->
            <div class="relative z-20 grid grid-cols-2 gap-4">
                <div class="group flex flex-col items-center p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M6 3h12l4 6-10 13L2 9Z"></path>
                            <path d="M11 3 8 9l4 13 4-13-3-6"></path>
                            <path d="M2 9h20"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm mb-1"><?php _e('outstanding_features_title') ?></h3>
                    <p class="text-xs text-center text-blue-100 leading-relaxed"><?php _e('outstanding_features_description') ?></p>
                </div>

                <div class="group flex flex-col items-center p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm mb-1"><?php _e('optimized_speed_title') ?></h3>
                    <p class="text-xs text-center text-blue-100 leading-relaxed"><?php _e('optimized_speed_description') ?></p>
                </div>

                <div class="group flex flex-col items-center p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm mb-1"><?php _e('premium_security_title') ?></h3>
                    <p class="text-xs text-center text-blue-100 leading-relaxed"><?php _e('premium_security_description') ?></p>
                </div>

                <div class="group flex flex-col items-center p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white text-sm mb-1"><?php _e('intuitive_interface_title') ?></h3>
                    <p class="text-xs text-center text-blue-100 leading-relaxed"><?php _e('intuitive_interface_description') ?></p>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="flex items-center h-full justify-center p-8 bg-white/80 backdrop-blur-sm">
            <div class="w-full max-w-sm space-y-6">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h2 class="text-3xl font-bold text-gray-900"><?php _e('forgot_password') ?></h2>
                    <p class="text-gray-600 text-sm">
                        <?php _e('enter_email_instruction') ?>
                    </p>
                </div>

                <!-- Forgot Password Form -->
                <form class="space-y-5">
                    <!-- Email Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                        </div>
                        <input
                            type="email"
                            class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-2xl bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-gray-400 text-sm font-medium"
                            placeholder="<?php _e('email_placeholder') ?>"
                            required>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"></path>
                            <path d="M6 12h16"></path>
                        </svg>
                        <?php _e('send_instructions') ?>
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="text-center">
                    <a
                        href="<?php echo auth_url('login') ?>"
                        class="inline-flex items-center gap-2 px-4 py-3 border border-gray-200 rounded-2xl bg-white hover:bg-gray-50 hover:border-blue-300 transition-all duration-300 text-gray-700 font-medium text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"></path>
                            <path d="M19 12H5"></path>
                        </svg>
                        <?php _e('back_to_login') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>