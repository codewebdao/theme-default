<div class="flex min-h-[calc(100vh-140px)]">
    <div
        class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 text-white p-12 flex-col justify-center relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute bottom-20 right-20 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
            <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-cyan-400/20 rounded-full blur-lg"></div>
        </div>
        <div class="relative z-10">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">🚀</span>
                    </div>
                    <h1 class="text-2xl font-bold"><?= _e('auth.cms_full_form'); ?></h1>
                </div>
                <h2 class="text-xl mb-2"><?= _e('auth.join_community'); ?></h2>
                <h3 class="text-3xl font-bold mb-4"><?= _e('auth.start_digital_journey'); ?></h3>
                <p class="text-emerald-100 text-lg leading-relaxed">
                    <?= _e('auth.register_description'); ?>
                </p>
            </div>
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-zap w-5 h-5">
                            <path
                                d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-lg"><?= _e('auth.quick_deployment'); ?></span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-shield w-5 h-5">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-lg"><?= _e('auth.absolute_security'); ?></span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-globe w-5 h-5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                            <path d="M2 12h20"></path>
                        </svg>
                    </div>
                    <span class="text-lg"><?= _e('auth.multilingual_support'); ?></span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-users w-5 h-5">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <span class="text-lg"><?= _e('auth.community_support'); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-2"><?= _e('auth.create_account'); ?></h2>
                <p class="text-gray-600"><?= _e('auth.fill_info_to_start'); ?></p>
            </div>
            <div class="space-y-3">
                <button
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground px-4 py-2 w-full h-12 border-gray-200 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z">
                        </path>
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z">
                        </path>
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z">
                        </path>
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z">
                        </path>
                    </svg>
                    <?= _e('auth.register_with_google'); ?>
                </button>
                <button
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground px-4 py-2 w-full h-12 border-gray-200 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="#1877F2" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z">
                        </path>
                    </svg>
                    <?= _e('auth.register_with_facebook'); ?>
                </button>
            </div>
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div data-orientation="horizontal" role="none"
                        class="shrink-0 bg-border h-[1px] w-full"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-2 text-gray-500"><?= _e('auth.or'); ?></span>
                </div>
            </div>
            <form class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label
                            class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 font-medium"
                            for="firstName"><?= _e('auth.first_name'); ?></label>
                        <div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg><input type="text"
                                class="flex w-full rounded-md border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 h-12 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                id="firstName" placeholder="<?= _e('auth.first_name'); ?>" required></div>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 font-medium"
                            for="lastName"><?= _e('auth.last_name'); ?></label>
                        <div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg><input type="text"
                                class="flex w-full rounded-md border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 h-12 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                id="lastName" placeholder="<?= _e('auth.last_name'); ?>" required></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 font-medium"
                        for="email"><?= _e('auth.email'); ?></label>
                    <div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-mail absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg><input type="email"
                            class="flex w-full rounded-md border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 h-12 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            id="email" placeholder="<?= _e('auth.email_placeholder'); ?>" required></div>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 font-medium"
                        for="phone"><?= _e('auth.phone'); ?></label>
                    <div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                            </path>
                        </svg><input type="tel"
                            class="flex w-full rounded-md border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 h-12 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            id="phone" placeholder="<?= _e('auth.phone_placeholder'); ?>" required></div>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 font-medium"
                        for="password"><?= _e('auth.password'); ?></label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password"
                            class="password-strength-input flex w-full rounded-md border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 pr-10 h-12 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            id="password" placeholder="<?= _e('auth.password_placeholder'); ?>" required>
                        <button type="button"
                            class="password-strength-toggle absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-eye w-4 h-4 eye-open">
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                </path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-eye-off w-4 h-4 eye-closed hidden">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                <line x1="2" x2="22" y1="2" y2="22"></line>
                            </svg>
                        </button>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="password-strength-bar h-2 rounded-full transition-all bg-red-500"
                                        style="width:0%"></div>
                                </div>
                                <span class="password-strength-text text-xs text-gray-600"><?= _e('auth.weak'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 font-medium"
                        for="confirmPassword"><?= _e('auth.confirm_password'); ?></label>
                    <div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg><input type="password"
                            class="flex w-full rounded-md border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 pr-10 h-12 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            id="confirmPassword" placeholder="<?= _e('auth.confirm_password_placeholder'); ?>" required><button
                            type="button"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-eye w-4 h-4">
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                </path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg></button></div>
                </div>
                <div class="space-y-3">
                    <label class="flex items-start space-x-2 cursor-pointer">
                        <input type="checkbox"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mt-1"
                            required>
                        <span class="text-sm text-gray-600">
                            <?= _e('auth.i_agree'); ?>
                            <a class="text-emerald-600 hover:text-emerald-800" href="/terms"><?= _e('auth.terms_of_use'); ?></a>
                            <?= _e('auth.and'); ?>
                            <a class="text-emerald-600 hover:text-emerald-800"
                                href="/privacy"><?= _e('auth.privacy_policy'); ?></a>
                        </span>
                    </label>
                    <label class="flex items-start space-x-2 cursor-pointer">
                        <input type="checkbox"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mt-1">
                        <span class="text-sm text-gray-600"><?= _e('auth.receive_info_updates'); ?></span>
                    </label>
                </div>
                <button
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  px-4 py-2 w-full h-12 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-medium text-base"
                    type="submit"><?= _e('auth.create_account'); ?></button>
            </form>
            <div class="text-center">
                <span class="text-gray-600"><?= _e('auth.already_have_account'); ?> </span>
                <a class="text-emerald-600 hover:text-emerald-800 font-medium" href="<?= base_url('download') ?>"><?= _e('auth.login_now'); ?></a>
            </div>
        </div>
    </div>
</div>


<script>
    document.getElementById('register').addEventListener('click', function() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        // If fullname exists then get it, otherwise leave empty
        const fullnameInput = document.getElementById('fullname');
        const fullname = fullnameInput ? fullnameInput.value : '';

        const formData = new FormData();
        formData.append('username', username);
        formData.append('fullname', fullname);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('password_repeat', confirmPassword);

        fetch('/api/v1/auth/register', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.access_token) {
                // Save access_token to cmsff_token cookie (expires in 7 days)
                document.cookie = "cmsff_token=" + data.data.access_token + "; path=/; max-age=" + (60*60*24*7) + ";";
                // Save user to localStorage if desired
                localStorage.setItem('user', JSON.stringify(data.data.me));
                // Return to home page
                window.location.href = '/';
            } else {
                alert(data.message || 'Đăng ký thất bại!');
            }
        });
    });
</script>