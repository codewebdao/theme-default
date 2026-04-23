<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

require_once __DIR__ . '/_auth-site-logo.php';
$auth_brand_logo = cmsfullform_auth_site_logo_url();
$auth_brand_alt = cmsfullform_auth_site_brand_label();

// Set page title
Head::setTitle([Fastlang::__('Profile Settings')]);

// Get countries list
$locale = lang_code();
if (class_exists('\Symfony\Component\Intl\Countries')) {
    $countries = \Symfony\Component\Intl\Countries::getNames($locale);
} else {
    // Fallback: Common countries list
    $countries = [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'FI' => 'Finland',
        'PL' => 'Poland',
        'CZ' => 'Czech Republic',
        'GR' => 'Greece',
        'PT' => 'Portugal',
        'IE' => 'Ireland',
        'CN' => 'China',
        'JP' => 'Japan',
        'KR' => 'South Korea',
        'IN' => 'India',
        'PK' => 'Pakistan',
        'BD' => 'Bangladesh',
        'TH' => 'Thailand',
        'VN' => 'Vietnam',
        'ID' => 'Indonesia',
        'MY' => 'Malaysia',
        'SG' => 'Singapore',
        'PH' => 'Philippines',
        'MM' => 'Myanmar',
        'KH' => 'Cambodia',
        'LA' => 'Laos',
        'BN' => 'Brunei',
        'TW' => 'Taiwan',
        'HK' => 'Hong Kong',
        'MO' => 'Macau',
        'RU' => 'Russia',
        'UA' => 'Ukraine',
        'TR' => 'Turkey',
        'SA' => 'Saudi Arabia',
        'AE' => 'United Arab Emirates',
        'EG' => 'Egypt',
        'ZA' => 'South Africa',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        'AR' => 'Argentina',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'PE' => 'Peru',
        'VE' => 'Venezuela',
    ];
}

view_header(null, ['layout' => 'default', 'title' => Fastlang::__('Profile Settings')]);
?>
<!-- intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.5/build/css/intlTelInput.min.css">
<!-- intl-tel-input JS -->
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.5/build/js/intlTelInput.min.js" defer></script>
<!-- libphonenumber-js for phone validation (Google libphonenumber) -->
<script type="module" defer>
    // Import and expose parsePhoneNumberFromString to window
    import { parsePhoneNumberFromString } from 'https://cdn.jsdelivr.net/npm/libphonenumber-js@1.11.0/+esm';
    window.parsePhoneNumberFromString = parsePhoneNumberFromString;
    window.libphonenumberLoaded = true;
    // Dispatch event to notify that libphonenumber is loaded
    window.dispatchEvent(new Event('libphonenumber:loaded'));
</script>

<div class="auth-portal">
<?php echo View::include('auth-left'); ?>
<div class="auth-prof-scope">
    <div class="auth-prof-container">
        <header class="auth-prof-topbar">
            <a href="<?php echo base_url(); ?>" class="auth-prof-back">
                <i data-lucide="arrow-left" class="auth-prof-back__icon" aria-hidden="true"></i>
                <span class="auth-prof-back__text"><?php _e('Back to Home') ?></span>
            </a>
            <div class="auth-prof-topbar__center">
                <?php if (!empty($auth_brand_logo)): ?>
                    <a href="<?php echo base_url(); ?>" class="auth-prof-brand" aria-label="<?php echo htmlspecialchars($auth_brand_alt); ?>">
                        <img src="<?php echo htmlspecialchars($auth_brand_logo); ?>" alt="" width="140" height="44" decoding="async" />
                    </a>
                <?php endif; ?>
                <h1 class="auth-prof-page-title"><?php _e('Profile Settings') ?></h1>
                </div>
            <div class="auth-prof-topbar__end">
                        <?php echo View::include('language-switcher'); ?>
                    </div>
        </header>

        <?php if ($error = Session::flash('error')): ?>
            <div class="auth-prof-flash auth-prof-flash--error" role="alert">
                <i data-lucide="alert-triangle" class="auth-prof-flash__icon" aria-hidden="true"></i>
                <p class="auth-prof-flash__text"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($success = Session::flash('success')): ?>
            <div class="auth-prof-flash auth-prof-flash--success" role="status">
                <i data-lucide="shield-check" class="auth-prof-flash__icon" aria-hidden="true"></i>
                <p class="auth-prof-flash__text"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors) && is_array($errors)): ?>
            <div class="auth-prof-flash auth-prof-flash--error" role="alert">
                <i data-lucide="alert-triangle" class="auth-prof-flash__icon" aria-hidden="true"></i>
                <div class="auth-prof-flash__body">
                    <h3 class="auth-prof-flash__heading"><?php _e('Please fix the following errors:') ?></h3>
                    <ul class="auth-prof-flash__list">
                            <?php foreach ($errors as $field => $fieldErrors): ?>
                                <?php if (is_array($fieldErrors) && !empty($fieldErrors)): ?>
                                <li>
                                    <span class="auth-prof-flash__field"><?php echo ucfirst(str_replace('_', ' ', $field)); ?>:</span>
                                    <?php echo implode(', ', array_map('htmlspecialchars', $fieldErrors)); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="auth-prof-app" x-data="profileTabs()" x-init="init()" x-ref="profileContainer">

                        <?php
            $auth_prof_show_admin = isset($me_info['role']) && in_array($me_info['role'], ['admin', 'moderator', 'editor', 'author', 'contributor'], true)
                && (has_permission('App\Controllers\Backend\PostsController', 'index') || has_permission('App\Controllers\Backend\HomeController', 'index'));
            ?>

            <?php if ($auth_prof_show_admin): ?>
                <a href="<?php echo admin_url('home'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="auth-prof-admin-cta"
                    aria-label="<?php echo htmlspecialchars(Fastlang::__('Admin Panel') . ' — ' . Fastlang::__('Go to admin home — manage site'), ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="auth-prof-admin-cta__icon-wrap" aria-hidden="true">
                        <svg class="auth-prof-admin-cta__icon-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    </span>
                    <span class="auth-prof-admin-cta__text">
                        <span class="auth-prof-admin-cta__title"><?php _e('Admin Panel') ?></span>
                        <span class="auth-prof-admin-cta__sub"><?php _e('Go to admin home — manage site') ?></span>
                    </span>
                    <svg class="auth-prof-admin-cta__external-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                </a>
                        <?php endif; ?>

            <div class="auth-prof-rail">
                <nav class="auth-prof-tabs" role="tablist" aria-label="<?php echo htmlspecialchars(Fastlang::__('Profile Settings'), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="auth-prof-tabs__scroll">
                        <button type="button" role="tab"
                            :aria-selected="activeTab === 'personal_info' ? 'true' : 'false'"
                            @click="switchTab('personal_info')"
                            class="profile-tab-trigger"
                            :class="{ 'profile-tab-trigger--active': activeTab === 'personal_info' }"
                                data-tab="personal_info">
                            <i data-lucide="user" class="profile-tab-trigger__icon" aria-hidden="true"></i>
                            <span class="profile-tab-trigger__label"><?php _e('Personal Information') ?></span>
                        </button>
                        <button type="button" role="tab"
                            :aria-selected="activeTab === 'detailed_info' ? 'true' : 'false'"
                            @click="switchTab('detailed_info')"
                            class="profile-tab-trigger"
                            :class="{ 'profile-tab-trigger--active': activeTab === 'detailed_info' }"
                                data-tab="detailed_info">
                            <i data-lucide="briefcase" class="profile-tab-trigger__icon" aria-hidden="true"></i>
                            <span class="profile-tab-trigger__label"><?php _e('Detailed Information') ?></span>
                        </button>
                        <button type="button" role="tab"
                            :aria-selected="activeTab === 'social_media' ? 'true' : 'false'"
                            @click="switchTab('social_media')"
                            class="profile-tab-trigger"
                            :class="{ 'profile-tab-trigger--active': activeTab === 'social_media' }"
                                data-tab="social_media">
                            <i data-lucide="share-2" class="profile-tab-trigger__icon" aria-hidden="true"></i>
                            <span class="profile-tab-trigger__label"><?php _e('Social Media') ?></span>
                        </button>
                        <button type="button" role="tab"
                            :aria-selected="activeTab === 'security' ? 'true' : 'false'"
                            @click="switchTab('security')"
                            class="profile-tab-trigger"
                            :class="{ 'profile-tab-trigger--active': activeTab === 'security' }"
                                data-tab="security">
                            <i data-lucide="shield" class="profile-tab-trigger__icon" aria-hidden="true"></i>
                            <span class="profile-tab-trigger__label"><?php _e('Password & Security') ?></span>
                        </button>
                    </div>
                </nav>

                <a href="<?php echo auth_url('logout'); ?>" class="auth-prof-aux-link auth-prof-aux-link--logout">
                    <i data-lucide="log-out" class="auth-prof-aux-link__icon" aria-hidden="true"></i>
                    <span><?php _e('Logout') ?></span>
                </a>
            </div>

            <div class="auth-prof-stage">
                <!-- Personal Information Section -->
                <div id="personal_info" class="profile-section">
                    <div class="auth-prof-panel">
                        <div class="auth-prof-panel__head">
                            <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                <i data-lucide="user" class="w-5 h-5 mr-2 text-blue-600"></i>
                                <?php _e('Personal Information') ?>
                            </h3>
                            <p class="mt-1 text-xs md:text-sm text-gray-600"><?php _e('Update your basic personal details') ?></p>
                        </div>
                        <div class="auth-prof-panel__body">
                            <!-- Profile Form -->
                            <form class="space-y-4" action="<?php echo auth_url('set-profile'); ?>" method="post" id="profileForm">
                                <input type="hidden" name="page_type" value="personal_info">
                                <input type="hidden" name="csrf_token" value="<?php echo Session::csrf_token(600); ?>">

                                <!-- Profile Visibility Section -->
                                <div class="space-y-4">
                                    <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                        <i data-lucide="eye" class="w-5 h-5 mr-2 text-blue-600"></i>
                                        <?php _e('Profile Visibility') ?>
                                    </h3>

                                    <!-- Display Profile Toggle -->
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-md">
                                            <div class="flex items-center">
                                                <i data-lucide="eye" class="w-5 h-5 text-gray-400 mr-3"></i>
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900"><?php _e('Profile Visibility') ?></h4>
                                                    <p class="text-xs md:text-sm text-gray-500"><?php _e('Allow others to find and view your profile') ?></p>
                                                </div>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="hidden" name="display" value="0">
                                                <input
                                                    type="checkbox"
                                                    id="display"
                                                    name="display"
                                                    value="1"
                                                    <?php echo ($me_info['display'] ?? 0) ? 'checked' : ''; ?>
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-gray-200 my-6"></div>

                                <!-- Personal Information Section -->
                                <div class="space-y-4">
                                    <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                        <i data-lucide="user" class="w-5 h-5 mr-2 text-blue-600"></i>
                                        <?php _e('Personal Information') ?>
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Full Name -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('Full Name') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="text"
                                                    id="fullname"
                                                    name="fullname"
                                                    value="<?php echo htmlspecialchars($me_info['fullname'] ?? ''); ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['fullname']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                    placeholder="<?php _e('Full Name Placeholder') ?>"
                                                    required>
                                            </div>
                                            <?php if (isset($errors['fullname'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['fullname'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Username (Editable) -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('Username') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="at-sign" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="text"
                                                    id="username"
                                                    name="username"
                                                    value="<?php echo htmlspecialchars($me_info['username'] ?? ''); ?>"
                                                    placeholder="<?php _e('Username Placeholder') ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['username']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>">
                                            </div>
                                            <?php if (isset($errors['username'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['username'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Email (Read-only) -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Email Address') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="email"
                                                id="email"
                                                value="<?php echo htmlspecialchars($me_info['email'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed"
                                                readonly>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1"><?php _e('Email cannot be changed') ?></p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Birthday -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('Birthday') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="date"
                                                    id="birthday"
                                                    name="birthday"
                                                    value="<?php echo htmlspecialchars($me_info['birthday'] ?? ''); ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['birthday']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                    placeholder="YYYY-MM-DD">
                                                <!-- Fallback for old browsers -->
                                                <noscript>
                                                    <div class="mt-2 text-sm text-gray-500">
                                                        <?php _e('Please enter date in YYYY-MM-DD format') ?>
                                                    </div>
                                                </noscript>
                                            </div>
                                            <?php if (isset($errors['birthday'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['birthday'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Gender -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('Gender') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <select
                                                    id="gender"
                                                    name="gender"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 text-sm font-medium <?php echo (isset($errors['gender']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>">
                                                    <option value=""><?php _e('Select Gender') ?></option>
                                                    <option value="male" <?php echo ($me_info['gender'] ?? '') === 'male' ? 'selected' : ''; ?>><?php _e('Male') ?></option>
                                                    <option value="female" <?php echo ($me_info['gender'] ?? '') === 'female' ? 'selected' : ''; ?>><?php _e('Female') ?></option>
                                                    <option value="other" <?php echo ($me_info['gender'] ?? '') === 'other' ? 'selected' : ''; ?>><?php _e('Other') ?></option>
                                                </select>
                                            </div>
                                            <?php if (isset($errors['gender'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['gender'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Personal Description -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Personal Description') ?></label>
                                        <div class="relative">
                                            <div class="absolute top-3 left-0 flex items-start pl-4">
                                                <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <textarea
                                                id="about_me"
                                                name="about_me"
                                                rows="4"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium resize-none <?php echo (isset($errors['about_me']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('Tell us about yourself...') ?>"><?php echo htmlspecialchars($me_info['about_me'] ?? ''); ?></textarea>
                                        </div>
                                        <?php if (isset($errors['about_me'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['about_me'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <!-- Divider -->
                                <div class="border-t border-gray-200 my-6"></div>

                                <!-- Contact & Location Section -->
                                <div class="space-y-4">
                                    <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                        <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-blue-600"></i>
                                        <?php _e('Contact & Location') ?>
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Phone -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('Phone Number') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="tel"
                                                    id="phone"
                                                    name="phone"
                                                    value="<?php echo htmlspecialchars($me_info['phone'] ?? ''); ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['phone']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                    placeholder="<?php _e('Phone Number Placeholder') ?>">
                                            </div>
                                            <?php if (isset($errors['phone'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['phone'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Country -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('Country') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="flag" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <select
                                                    id="country"
                                                    name="country"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 text-sm font-medium <?php echo (isset($errors['country']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>">
                                                    <option value=""><?php _e('Select Country') ?></option>
                                                    <?php
                                                    $selectedCountry = $me_info['country'] ?? '';
                                                    foreach ($countries as $code => $name):
                                                    ?>
                                                        <option value="<?php echo htmlspecialchars($code); ?>" <?php echo $selectedCountry === $code ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php if (isset($errors['country'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['country'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Address Fields -->
                                    <?php
                                    // Use pre-processed address data from _prepare_profile_data
                                    $address = $me_info['address'] ?? [];
                                    ?>

                                    <!-- Address 1 -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Address Line 1') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="home" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="address1"
                                                name="address1"
                                                value="<?php echo htmlspecialchars($address['address1'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['address1']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('Address Line 1') ?>">
                                        </div>
                                        <?php if (isset($errors['address1'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['address1'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Address 2 -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Address Line 2') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="building" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="address2"
                                                name="address2"
                                                value="<?php echo htmlspecialchars($address['address2'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['address2']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('Address Line 2') ?>">
                                        </div>
                                        <?php if (isset($errors['address2'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['address2'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- City -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('City') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="text"
                                                    id="city"
                                                    name="city"
                                                    value="<?php echo htmlspecialchars($address['city'] ?? ''); ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['city']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                    placeholder="<?php _e('City') ?>">
                                            </div>
                                            <?php if (isset($errors['city'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['city'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- State -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('State/Province') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="map" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="text"
                                                    id="state"
                                                    name="state"
                                                    value="<?php echo htmlspecialchars($address['state'] ?? ''); ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['state']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                    placeholder="<?php _e('State/Province') ?>">
                                            </div>
                                            <?php if (isset($errors['state'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['state'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Zip Code -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700"><?php _e('ZIP/Postal Code') ?></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <i data-lucide="hash" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="text"
                                                    id="zipcode"
                                                    name="zipcode"
                                                    value="<?php echo htmlspecialchars($address['zipcode'] ?? ''); ?>"
                                                    class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['zipcode']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                    placeholder="<?php _e('ZIP/Postal Code') ?>">
                                            </div>
                                            <?php if (isset($errors['zipcode'])): ?>
                                                <div class="text-red-500 text-xs mt-1">
                                                    <?php foreach ($errors['zipcode'] as $error): ?>
                                                        <div><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Profile Button -->
                                <button
                                    type="submit"
                                    id="updateProfile"
                                    class="auth-prof-btn w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-400 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    <?php _e('Update Profile') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Detailed Information Section -->
                <div id="detailed_info" x-data="detailedInfo()" class="profile-section hidden">
                    <div class="auth-prof-panel">
                        <div class="auth-prof-panel__head">
                            <h2 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                <i data-lucide="briefcase" class="w-5 h-5 mr-2 text-blue-600"></i>
                                <?php _e('Detailed Information') ?>
                            </h2>
                            <p class="mt-1 text-sm text-gray-600"><?php _e('Manage your professional and personal details') ?></p>
                        </div>
                        <div class="auth-prof-panel__body">
                            <form class="space-y-6" action="<?php echo auth_url('set-profile'); ?>" method="post" id="detailedInfoForm">
                                <input type="hidden" name="page_type" value="detailed_info">
                                <input type="hidden" name="csrf_token" value="<?php echo Session::csrf_token(600); ?>">

                                <!-- Work Experience -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                            <i data-lucide="briefcase" class="w-5 h-5 mr-2 text-blue-600"></i>
                                            <?php _e('Work Experience') ?>
                                        </h3>
                                        <button type="button" @click="addWorkExperience()" class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                            <?php _e('Add Experience') ?>
                                        </button>
                                    </div>

                                    <div class="space-y-4" x-show="workExperiences.length > 0">
                                        <template x-for="(work, index) in workExperiences" :key="index">
                                            <div class="border border-gray-200 rounded-md p-4">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900"><?php _e('Experience') ?> <span x-text="index + 1"></span></h4>
                                                    <button type="button" @click="removeWorkExperience(index)" class="text-red-500 hover:text-red-700">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Company') ?></label>
                                                        <input type="text" x-model="work.company" :name="`work_experiences[${index}][company]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Position') ?></label>
                                                        <input type="text" x-model="work.position" :name="`work_experiences[${index}][position]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Start Date') ?></label>
                                                        <input type="date" x-model="work.start_date" :name="`work_experiences[${index}][start_date]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('End Date') ?></label>
                                                        <input type="date" x-model="work.end_date" :name="`work_experiences[${index}][end_date]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="md:col-span-2 space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Description') ?></label>
                                                        <textarea x-model="work.description" :name="`work_experiences[${index}][description]`" rows="3" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 resize-none" placeholder="<?php _e('Describe your role and achievements...') ?>"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Education -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                            <i data-lucide="graduation-cap" class="w-5 h-5 mr-2 text-blue-600"></i>
                                            <?php _e('Education') ?>
                                        </h3>
                                        <button type="button" @click="addEducation()" class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                            <?php _e('Add Education') ?>
                                        </button>
                                    </div>

                                    <div class="space-y-4" x-show="educations.length > 0">
                                        <template x-for="(edu, index) in educations" :key="index">
                                            <div class="border border-gray-200 rounded-md p-4">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900"><?php _e('Education') ?> <span x-text="index + 1"></span></h4>
                                                    <button type="button" @click="removeEducation(index)" class="text-red-500 hover:text-red-700">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Institution') ?></label>
                                                        <input type="text" x-model="edu.institution" :name="`educations[${index}][institution]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Degree') ?></label>
                                                        <input type="text" x-model="edu.degree" :name="`educations[${index}][degree]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Start Date') ?></label>
                                                        <input type="date" x-model="edu.start_date" :name="`educations[${index}][start_date]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('End Date') ?></label>
                                                        <input type="date" x-model="edu.end_date" :name="`educations[${index}][end_date]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Skills -->
                                <div class="space-y-4">
                                    <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                        <i data-lucide="award" class="w-5 h-5 mr-2 text-blue-600"></i>
                                        <?php _e('Skills') ?>
                                    </h3>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Add Skills') ?></label>
                                        <div class="flex flex-wrap gap-2 mb-4" x-show="skills.length > 0">
                                            <template x-for="(skill, index) in skills" :key="index">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                                    <span x-text="skill"></span>
                                                    <button type="button" @click="removeSkill(index)" class="ml-2 text-blue-600 hover:text-blue-800">
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </button>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="flex gap-2">
                                            <input type="text" x-model="newSkill" @keydown.enter.prevent="addSkill()" class="flex-1 px-3 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300" placeholder="<?php _e('Enter a skill and press Enter') ?>">
                                            <button type="button" @click="addSkill()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div x-show="skills.some(skill => skill.toLowerCase() === newSkill.toLowerCase()) && newSkill.trim() !== ''" class="text-amber-500 text-xs mt-1">
                                            <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                                            <?php _e('This skill already exists') ?>
                                        </div>
                                        <template x-for="(skill, index) in skills" :key="index">
                                            <input type="hidden" :name="`skills[${index}]`" :value="skill">
                                        </template>
                                    </div>
                                </div>

                                <!-- Languages -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                            <i data-lucide="globe" class="w-5 h-5 mr-2 text-blue-600"></i>
                                            <?php _e('Languages') ?>
                                        </h3>
                                        <button type="button" @click="addLanguage()" class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                            <?php _e('Add Language') ?>
                                        </button>
                                    </div>

                                    <div class="space-y-4" x-show="languages.length > 0">
                                        <template x-for="(lang, index) in languages" :key="index">
                                            <div class="border border-gray-200 rounded-md p-4">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900"><?php _e('Language') ?> <span x-text="index + 1"></span></h4>
                                                    <button type="button" @click="removeLanguage(index)" class="text-red-500 hover:text-red-700">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Language') ?></label>
                                                        <input type="text" x-model="lang.language" :name="`languages[${index}][language]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Proficiency') ?></label>
                                                        <select x-model="lang.proficiency" :name="`languages[${index}][proficiency]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                            <option value=""><?php _e('Select Proficiency') ?></option>
                                                            <option value="beginner"><?php _e('Beginner') ?></option>
                                                            <option value="intermediate"><?php _e('Intermediate') ?></option>
                                                            <option value="advanced"><?php _e('Advanced') ?></option>
                                                            <option value="native"><?php _e('Native') ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Hobbies -->
                                <div class="space-y-4">
                                    <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                        <i data-lucide="heart" class="w-5 h-5 mr-2 text-blue-600"></i>
                                        <?php _e('Hobbies & Interests') ?>
                                    </h3>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Add Hobbies') ?></label>
                                        <div class="flex flex-wrap gap-2 mb-4" x-show="hobbies.length > 0">
                                            <template x-for="(hobby, index) in hobbies" :key="index">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                                    <span x-text="hobby"></span>
                                                    <button type="button" @click="removeHobby(index)" class="ml-2 text-green-600 hover:text-green-800">
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </button>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="flex gap-2">
                                            <input type="text" x-model="newHobby" @keydown.enter.prevent="addHobby()" class="flex-1 px-3 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300" placeholder="<?php _e('Enter a hobby and press Enter') ?>">
                                            <button type="button" @click="addHobby()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div x-show="hobbies.some(hobby => hobby.toLowerCase() === newHobby.toLowerCase()) && newHobby.trim() !== ''" class="text-amber-500 text-xs mt-1">
                                            <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                                            <?php _e('This hobby already exists') ?>
                                        </div>
                                        <template x-for="(hobby, index) in hobbies" :key="index">
                                            <input type="hidden" :name="`hobbies[${index}]`" :value="hobby">
                                        </template>
                                    </div>
                                </div>

                                <!-- Certifications -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                            <i data-lucide="award" class="w-5 h-5 mr-2 text-blue-600"></i>
                                            <?php _e('Certifications & Achievements') ?>
                                        </h3>
                                        <button type="button" @click="addCertification()" class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                            <?php _e('Add Certification') ?>
                                        </button>
                                    </div>

                                    <div class="space-y-4" x-show="certifications.length > 0">
                                        <template x-for="(cert, index) in certifications" :key="index">
                                            <div class="border border-gray-200 rounded-md p-4">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900"><?php _e('Certification') ?> <span x-text="index + 1"></span></h4>
                                                    <button type="button" @click="removeCertification(index)" class="text-red-500 hover:text-red-700">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Name') ?></label>
                                                        <input type="text" x-model="cert.name" :name="`certifications[${index}][name]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Issuing Organization') ?></label>
                                                        <input type="text" x-model="cert.issuer" :name="`certifications[${index}][issuer]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Issue Date') ?></label>
                                                        <input type="date" x-model="cert.issue_date" :name="`certifications[${index}][issue_date]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Expiry Date') ?></label>
                                                        <input type="date" x-model="cert.expiry_date" :name="`certifications[${index}][expiry_date]`" class="w-full px-2 py-2 border border-gray-200 rounded-md duration-300 placeholder:text-slate-400 text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Update Button -->
                                <button
                                    type="submit"
                                    class="auth-prof-btn w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-400 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    <?php _e('Update Detailed Information') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Social Media Section -->
                <div id="social_media" x-data="socialMedia()" class="profile-section hidden">
                    <div class="auth-prof-panel">
                        <div class="auth-prof-panel__head">
                            <h2 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                <i data-lucide="share-2" class="w-5 h-5 mr-2 text-blue-600"></i>
                                <?php _e('Social Media') ?>
                            </h2>
                            <p class="mt-1 text-sm text-gray-600"><?php _e('Connect your social media accounts') ?></p>
                        </div>
                        <div class="auth-prof-panel__body">
                            <form class="space-y-4" action="<?php echo auth_url('set-profile'); ?>" method="post" id="socialForm">
                                <input type="hidden" name="page_type" value="social_media">
                                <input type="hidden" name="csrf_token" value="<?php echo Session::csrf_token(600); ?>">

                                <?php
                                // Get social media data from me_info
                                $allSocials = $me_info['socials'] ?? [];
                                $socials = [
                                    'facebook' => $allSocials['facebook'] ?? '',
                                    'linkedin' => $allSocials['linkedin'] ?? '',
                                    'telegram' => $allSocials['telegram'] ?? '',
                                    'whatsapp' => $allSocials['whatsapp'] ?? ''
                                ];
                                ?>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Facebook -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Facebook') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="facebook" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="facebook"
                                                name="facebook"
                                                value="<?php echo htmlspecialchars($socials['facebook'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['facebook']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('Facebook Username/URL') ?>">
                                        </div>
                                        <?php if (isset($errors['facebook'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['facebook'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Telegram -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Telegram') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="message-circle" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="telegram"
                                                name="telegram"
                                                value="<?php echo htmlspecialchars($socials['telegram'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['telegram']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('Telegram Username') ?>">
                                        </div>
                                        <?php if (isset($errors['telegram'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['telegram'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('WhatsApp') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="smartphone" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="whatsapp"
                                                name="whatsapp"
                                                value="<?php echo htmlspecialchars($socials['whatsapp'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['whatsapp']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('WhatsApp Number') ?>">
                                        </div>
                                        <?php if (isset($errors['whatsapp'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['whatsapp'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- LinkedIn -->
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700"><?php _e('LinkedIn') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                <i data-lucide="linkedin" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                id="linkedin"
                                                name="linkedin"
                                                value="<?php echo htmlspecialchars($socials['linkedin'] ?? ''); ?>"
                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium <?php echo (isset($errors['linkedin']) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''); ?>"
                                                placeholder="<?php _e('LinkedIn Profile URL') ?>">
                                        </div>
                                        <?php if (isset($errors['linkedin'])): ?>
                                            <div class="text-red-500 text-xs mt-1">
                                                <?php foreach ($errors['linkedin'] as $error): ?>
                                                    <div><?php echo htmlspecialchars($error); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Custom Social Media Fields -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                            <i data-lucide="link" class="w-5 h-5 mr-2 text-blue-600"></i>
                                            <?php _e('Custom Social Media') ?>
                                        </h3>
                                        <button type="button" @click="addCustomSocial()" class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                            <?php _e('Add Custom Social Media') ?>
                                        </button>
                                    </div>

                                    <div class="space-y-4" x-show="customSocials.length > 0">
                                        <template x-for="(social, index) in customSocials" :key="index">
                                            <div class="border border-gray-200 rounded-md p-4">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-sm font-medium text-gray-900"><?php _e('Custom Social') ?> <span x-text="index + 1"></span></h4>
                                                    <button type="button" @click="removeCustomSocial(index)" class="text-red-500 hover:text-red-700">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Social Platform Name') ?></label>
                                                        <div class="relative">
                                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                                <i data-lucide="link" class="w-4 h-4 text-gray-400"></i>
                                                            </div>
                                                            <input
                                                                type="text"
                                                                x-model="social.name"
                                                                :name="`custom_social_name[${index}]`"
                                                                :class="`w-full pl-8 pr-2 py-2 border rounded-2xl bg-white focus:ring-4 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium ${isDuplicateName(social.name, index) ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-200 focus:border-blue-500 focus:ring-blue-500/20'}`"
                                                                placeholder="<?php _e('Social Platform Name') ?>">
                                                        </div>
                                                        <div x-show="isDuplicateName(social.name, index)" class="text-red-500 text-xs">
                                                            <span x-text="getDuplicateWarning(social.name, index)"></span>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-sm font-medium text-gray-700"><?php _e('Username/URL') ?></label>
                                                        <div class="relative">
                                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                                <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                                            </div>
                                                            <input
                                                                type="text"
                                                                x-model="social.value"
                                                                :name="`custom_social_value[${index}]`"
                                                                class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium"
                                                                placeholder="<?php _e('Username/URL') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="customSocials.length === 0" class="text-center py-8 text-gray-500">
                                        <i data-lucide="link" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                                        <p><?php _e('No custom social media added yet') ?></p>
                                    </div>
                                </div>

                                <!-- Update Social Media Button -->
                                <button
                                    type="submit"
                                    class="auth-prof-btn w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-400 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    <?php _e('Update Social Media') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Security Section -->
                <div id="security" class="profile-section hidden">
                    <div class="auth-prof-panel">
                        <div class="auth-prof-panel__head">
                            <h2 class="text-md md:text-lg font-semibold text-gray-900 flex items-center">
                                <i data-lucide="shield" class="w-5 h-5 mr-2 text-blue-600"></i>
                                <?php _e('Security') ?>
                            </h2>
                            <p class="mt-1 text-sm text-gray-600"><?php _e('Change your password and manage security settings') ?></p>
                        </div>
                        <div class="auth-prof-panel__body">
                            <form class="space-y-4" action="<?php echo auth_url('change-password'); ?>" method="post" id="changePasswordForm">
                                <input type="hidden" name="csrf_token" value="<?php echo Session::csrf_token(600); ?>">
                                <input type="hidden" name="page_type" value="security">

                                <!-- Current Password -->
                                <div class="space-y-1">
                                    <label class="block text-sm font-medium text-gray-700"><?php _e('Current Password') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                            <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input
                                            type="password"
                                            autocomplete="false"
                                            id="current_password"
                                            name="current_password"
                                            class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium"
                                            placeholder="<?php _e('Current Password Placeholder') ?>"
                                            required>
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div class="space-y-1">
                                    <label class="block text-sm font-medium text-gray-700"><?php _e('New Password') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                            <i data-lucide="key" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input
                                            type="password"
                                            autocomplete="false"
                                            id="new_password"
                                            name="new_password"
                                            class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium"
                                            placeholder="<?php _e('New Password Placeholder') ?>"
                                            required>
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="space-y-1">
                                    <label class="block text-sm font-medium text-gray-700"><?php _e('Confirm New Password') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                                            <i data-lucide="shield-check" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input
                                            type="password"
                                            autocomplete="false"
                                            id="confirm_password"
                                            name="confirm_password"
                                            class="w-full pl-8 pr-2 py-2 border border-gray-200 rounded-md bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder:text-slate-400 text-sm font-medium"
                                            placeholder="<?php _e('Confirm New Password Placeholder') ?>"
                                            required>
                                    </div>
                                </div>

                                <!-- Change Password Button -->
                                <button
                                    type="submit"
                                    id="changePassword"
                                    class="auth-prof-btn w-full flex items-center justify-center gap-2 px-2 py-2 bg-gradient-to-r from-blue-400 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-md shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                    <?php _e('Change Password') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .auth-prof-scope {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        padding: 1.25rem 1rem 2.5rem;
        color: #e2e8f0;
        box-sizing: border-box;
    }
    .auth-prof-container {
        max-width: 50rem;
        margin-left: auto;
        margin-right: auto;
    }
    .auth-prof-app {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .auth-prof-admin-cta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        box-sizing: border-box;
        padding: 0.85rem 1rem;
        border-radius: 1rem;
        text-decoration: none;
        color: #042f2e;
        font-weight: 600;
        font-size: 0.9375rem;
        letter-spacing: -0.02em;
        line-height: 1.25;
        background: linear-gradient(135deg, #5eead4 0%, #2dd4bf 38%, #6366f1 100%);
        border: 1px solid rgba(255, 255, 255, 0.28);
        box-shadow:
            0 0 0 1px rgba(0, 0, 0, 0.15) inset,
            0 10px 36px -8px rgba(45, 212, 191, 0.55),
            0 18px 48px -16px rgba(99, 102, 241, 0.35);
        transition: transform 0.18s ease, filter 0.18s ease, box-shadow 0.18s ease;
    }
    .auth-prof-admin-cta:hover {
        color: #022c22;
        filter: brightness(1.04);
        transform: translateY(-2px);
        box-shadow:
            0 0 0 1px rgba(255, 255, 255, 0.35) inset,
            0 14px 40px -8px rgba(45, 212, 191, 0.6),
            0 22px 56px -14px rgba(99, 102, 241, 0.45);
    }
    .auth-prof-admin-cta:focus-visible {
        outline: none;
        box-shadow:
            0 0 0 3px rgba(45, 212, 191, 0.45),
            0 10px 36px -8px rgba(45, 212, 191, 0.55);
    }
    .auth-prof-admin-cta__icon-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        background: rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .auth-prof-admin-cta__icon-svg {
        width: 1.25rem;
        height: 1.25rem;
        color: #042f2e;
        flex-shrink: 0;
    }
    .auth-prof-admin-cta__text {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.15rem;
        min-width: 0;
        flex: 1;
    }
    .auth-prof-admin-cta__title {
        font-size: 0.9375rem;
        font-weight: 700;
    }
    .auth-prof-admin-cta__sub {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgba(4, 47, 46, 0.72);
        line-height: 1.35;
    }
    .auth-prof-admin-cta__external-svg {
        flex-shrink: 0;
        width: 1.1rem;
        height: 1.1rem;
        opacity: 0.75;
        color: #042f2e;
    }
    .auth-prof-admin-cta:hover .auth-prof-admin-cta__external-svg {
        opacity: 1;
    }
    .auth-prof-rail {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    @media (min-width: 720px) {
        .auth-prof-rail {
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
        }
        .auth-prof-tabs {
            flex: 1 1 0;
            min-width: 0;
            order: 0;
        }
        .auth-prof-aux-link--logout {
            order: 1;
            flex: 0 0 auto;
            margin-left: auto;
        }
    }
    .auth-prof-tabs__scroll {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.45rem;
        overflow-x: auto;
        padding: 0.15rem 0 0.4rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(45, 212, 191, 0.35) transparent;
    }
    .auth-prof-tabs__scroll::-webkit-scrollbar {
        height: 4px;
    }
    .auth-prof-tabs__scroll::-webkit-scrollbar-thumb {
        background: rgba(45, 212, 191, 0.35);
        border-radius: 999px;
    }
    .profile-tab-trigger {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        padding: 0.5rem 0.85rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(0, 0, 0, 0.32);
        color: #94a3b8;
        font-size: 0.8125rem;
        font-weight: 500;
        font-family: inherit;
        line-height: 1.2;
        cursor: pointer;
        transition: color 0.15s, border-color 0.15s, background 0.15s, box-shadow 0.15s;
        white-space: nowrap;
    }
    .profile-tab-trigger:hover {
        color: #e2e8f0;
        border-color: rgba(255, 255, 255, 0.2);
        background: rgba(0, 0, 0, 0.45);
    }
    .profile-tab-trigger:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.25);
    }
    .profile-tab-trigger--active {
        color: #f8fafc !important;
        border-color: rgba(45, 212, 191, 0.5) !important;
        background: rgba(45, 212, 191, 0.14) !important;
        box-shadow: 0 0 24px -8px rgba(45, 212, 191, 0.45);
    }
    .profile-tab-trigger__icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        opacity: 0.9;
    }
    .profile-tab-trigger__label {
        max-width: 9.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (min-width: 520px) {
        .profile-tab-trigger__label {
            max-width: none;
        }
    }
    .auth-prof-aux-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 500;
        text-decoration: none;
        color: #94a3b8;
        padding: 0.35rem 0.2rem;
        border-radius: 0.5rem;
        transition: color 0.15s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .auth-prof-aux-link:hover {
        color: #2dd4bf;
    }
    .auth-prof-aux-link__icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }
    .auth-prof-aux-link--logout:hover {
        color: #f87171;
    }
    .auth-prof-stage {
        min-height: 10rem;
    }
    .auth-prof-panel {
        border-radius: 1.15rem;
        overflow: hidden;
        background: rgba(12, 14, 22, 0.82);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid rgba(255, 255, 255, 0.09);
        box-shadow:
            0 0 0 1px rgba(0, 0, 0, 0.35) inset,
            0 28px 56px -18px rgba(0, 0, 0, 0.65);
    }
    .auth-prof-panel__head {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .auth-prof-panel__body {
        padding: 1.15rem 1.15rem 1.35rem;
    }
    @media (min-width: 640px) {
        .auth-prof-panel__head {
            padding: 1.05rem 1.35rem;
        }
        .auth-prof-panel__body {
            padding: 1.35rem 1.35rem 1.5rem;
        }
    }
    .auth-prof-topbar {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding: 0.65rem 0.85rem;
        border-radius: 1rem;
        background: rgba(12, 14, 22, 0.72);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.09);
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.35) inset;
    }
    .auth-prof-back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.65rem;
        border-radius: 0.65rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #cbd5e1;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.25);
        transition: color 0.15s, border-color 0.15s, background 0.15s;
    }
    .auth-prof-back:hover {
        color: #f8fafc;
        border-color: rgba(45, 212, 191, 0.35);
        background: rgba(0, 0, 0, 0.4);
    }
    .auth-prof-back__icon { width: 1rem; height: 1rem; flex-shrink: 0; }
    .auth-prof-back__text { display: none; }
    @media (min-width: 640px) {
        .auth-prof-back__text { display: inline; }
    }
    .auth-prof-topbar__center {
        text-align: center;
        min-width: 0;
    }
    .auth-prof-brand {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.25rem;
    }
    .auth-prof-brand img {
        max-height: 2.25rem;
        width: auto;
        max-width: 10rem;
        object-fit: contain;
    }
    .auth-prof-page-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        color: #f8fafc;
        line-height: 1.25;
    }
    @media (min-width: 640px) {
        .auth-prof-page-title { font-size: 1.125rem; }
    }
    .auth-prof-topbar__end { justify-self: end; min-width: 0; }

    .auth-prof-flash {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 1.25rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        border: 1px solid;
        font-size: 0.875rem;
        line-height: 1.45;
    }
    .auth-prof-flash:has(.auth-prof-flash__body) {
        align-items: flex-start;
    }
    .auth-prof-flash:has(.auth-prof-flash__body) .auth-prof-flash__icon {
        margin-top: 0.2em;
    }
    .auth-prof-flash__icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .auth-prof-flash__icon svg {
        width: 1.25rem;
        height: 1.25rem;
        display: block;
    }
    .auth-prof-flash__text { margin: 0; flex: 1; }
    .auth-prof-flash__body { flex: 1; min-width: 0; }
    .auth-prof-flash__heading { margin: 0 0 0.5rem; font-size: 0.875rem; font-weight: 600; }
    .auth-prof-flash__list { margin: 0; padding-left: 1.1rem; }
    .auth-prof-flash__list li { margin: 0.2rem 0; }
    .auth-prof-flash__field { font-weight: 600; margin-right: 0.25rem; }
    .auth-prof-flash--error {
        background: rgba(127, 29, 29, 0.35);
        border-color: rgba(248, 113, 113, 0.35);
        color: #fecaca;
    }
    .auth-prof-flash--error .auth-prof-flash__icon { color: #f87171; }
    .auth-prof-flash--success {
        background: rgba(6, 78, 59, 0.35);
        border-color: rgba(52, 211, 153, 0.35);
        color: #a7f3d0;
    }
    .auth-prof-flash--success .auth-prof-flash__icon { color: #34d399; }

    .auth-prof-btn {
        background: linear-gradient(135deg, #0d9488 0%, #14b8a6 48%, #4f46e5 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #fff !important;
        border-radius: 0.65rem !important;
        box-shadow: 0 12px 32px -14px rgba(20, 184, 166, 0.55);
        transition: filter 0.15s, box-shadow 0.15s;
    }
    .auth-prof-btn:hover {
        filter: brightness(1.06);
        box-shadow: 0 16px 40px -12px rgba(20, 184, 166, 0.65);
    }

    .auth-prof-scope .hidden,
    .auth-prof-scope .profile-section.hidden {
        display: none !important;
    }

    .auth-prof-scope .border-gray-200,
    .auth-prof-scope .border-gray-300 {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    .auth-prof-scope .text-gray-900,
    .auth-prof-scope .text-gray-800 {
        color: #f1f5f9 !important;
    }
    .auth-prof-scope .text-gray-700 {
        color: #e2e8f0 !important;
    }
    .auth-prof-scope .text-gray-600,
    .auth-prof-scope .text-gray-500 {
        color: #94a3b8 !important;
    }
    .auth-prof-scope .text-gray-400 {
        color: #64748b !important;
    }
    .auth-prof-scope .text-blue-600,
    .auth-prof-scope .text-blue-500 {
        color: #2dd4bf !important;
    }
    .auth-prof-scope input[type="text"],
    .auth-prof-scope input[type="email"],
    .auth-prof-scope input[type="password"],
    .auth-prof-scope input[type="url"],
    .auth-prof-scope input[type="number"],
    .auth-prof-scope input[type="tel"],
    .auth-prof-scope input[type="search"],
    .auth-prof-scope input[type="date"],
    .auth-prof-scope textarea,
    .auth-prof-scope select {
        background: rgba(0, 0, 0, 0.35) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #f1f5f9 !important;
    }
    .auth-prof-scope input::placeholder,
    .auth-prof-scope textarea::placeholder {
        color: #64748b !important;
    }
    .auth-prof-scope input:focus,
    .auth-prof-scope textarea:focus,
    .auth-prof-scope select:focus {
        outline: none;
        border-color: rgba(45, 212, 191, 0.55) !important;
        box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.18);
    }
    .auth-prof-scope .border-red-500 {
        border-color: rgba(248, 113, 113, 0.75) !important;
    }
    .auth-prof-scope .text-red-500,
    .auth-prof-scope .text-red-700,
    .auth-prof-scope .text-red-800 {
        color: #fca5a5 !important;
    }

    .auth-prof-scope input.peer:checked + div.rounded-full {
        background: linear-gradient(135deg, #0d9488, #14b8a6) !important;
    }
    .auth-prof-scope input.peer:focus + div.rounded-full {
        box-shadow: 0 0 0 4px rgba(45, 212, 191, 0.22);
    }
    .auth-prof-scope .bg-gray-200 {
        background-color: rgba(51, 65, 85, 0.85) !important;
    }

    .auth-prof-scope .iti__selected-flag {
        background: rgba(0, 0, 0, 0.35) !important;
    }
    .auth-prof-scope .iti__country-list {
        background: #0f172a !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #e2e8f0;
    }
    .auth-prof-scope .iti__country-list .iti__country:hover,
    .auth-prof-scope .iti__country-list .iti__country.iti__highlight {
        background: rgba(45, 212, 191, 0.15) !important;
    }

    .profile-section {
        display: block;
    }
    .profile-section.hidden {
        display: none;
    }
    .profile-section {
        transition: opacity 0.2s ease;
    }

    .form-section {
        animation: authProfFadeIn 0.3s ease-in-out;
    }
    @keyframes authProfFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script defer>
    // Alpine.js Data
    function detailedInfo() {
        return {
            // Work Experience
            workExperiences: <?php echo json_encode($me_info['work_experiences'] ?? []); ?>,
            addWorkExperience() {
                this.workExperiences.push({
                    company: '',
                    position: '',
                    start_date: '',
                    end_date: '',
                    description: ''
                });
            },
            removeWorkExperience(index) {
                this.workExperiences.splice(index, 1);
            },

            // Education
            educations: <?php echo json_encode($me_info['educations'] ?? []); ?>,
            addEducation() {
                this.educations.push({
                    institution: '',
                    degree: '',
                    start_date: '',
                    end_date: ''
                });
            },
            removeEducation(index) {
                this.educations.splice(index, 1);
            },

            // Skills
            skills: <?php echo json_encode($me_info['skills'] ?? []); ?>,
            newSkill: '',
            addSkill() {
                if (this.newSkill.trim()) {
                    const skill = this.newSkill.trim();
                    // Check for duplicates (case insensitive)
                    if (!this.skills.some(existingSkill =>
                            existingSkill.toLowerCase() === skill.toLowerCase()
                        )) {
                        this.skills.push(skill);
                    }
                    this.newSkill = '';
                }
            },
            removeSkill(index) {
                this.skills.splice(index, 1);
            },

            // Languages
            languages: <?php echo json_encode($me_info['languages'] ?? []); ?>,
            addLanguage() {
                this.languages.push({
                    language: '',
                    proficiency: ''
                });
            },
            removeLanguage(index) {
                this.languages.splice(index, 1);
            },

            // Hobbies
            hobbies: <?php echo json_encode($me_info['hobbies'] ?? []); ?>,
            newHobby: '',
            addHobby() {
                if (this.newHobby.trim()) {
                    const hobby = this.newHobby.trim();
                    // Check for duplicates (case insensitive)
                    if (!this.hobbies.some(existingHobby =>
                            existingHobby.toLowerCase() === hobby.toLowerCase()
                        )) {
                        this.hobbies.push(hobby);
                    }
                    this.newHobby = '';
                }
            },
            removeHobby(index) {
                this.hobbies.splice(index, 1);
            },

            // Certifications
            certifications: <?php echo json_encode($me_info['certifications'] ?? []); ?>,
            addCertification() {
                this.certifications.push({
                    name: '',
                    issuer: '',
                    issue_date: '',
                    expiry_date: ''
                });
            },
            removeCertification(index) {
                this.certifications.splice(index, 1);
            }
        }
    }

    // Tab Navigation (pill rail + Alpine activeTab; không còn sidebar CMS)
    function profileTabs() {
        return {
            activeTab: 'personal_info',
            switchTab(tab) {
                document.querySelectorAll('.profile-section').forEach((el) => el.classList.add('hidden'));
                let selectedTab = document.getElementById(tab);
                if (!selectedTab) {
                    tab = 'personal_info';
                    selectedTab = document.getElementById(tab);
                }
                if (selectedTab) {
                    selectedTab.classList.remove('hidden');
                }
                this.activeTab = tab;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },
            init() {
                const activetab = '<?php echo Session::flash("activetab") ?? ""; ?>';
                if (activetab) {
                    this.switchTab(activetab);
                } else {
                    this.switchTab('personal_info');
                }
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            }
        }
    }

    // Social Media Management
    function socialMedia() {
        return {
            customSocials: <?php
                            // Get custom social media from me_info['socials'] excluding standard ones
                            $standardSocials = ['facebook', 'linkedin', 'telegram', 'whatsapp'];
                            $allSocials = $me_info['socials'] ?? [];
                            $customSocials = [];
                            foreach ($allSocials as $key => $value) {
                                if (!in_array($key, $standardSocials) && !empty($value)) {
                                    $customSocials[] = [
                                        'name' => ucfirst($key),
                                        'value' => $value
                                    ];
                                }
                            }
                            echo json_encode($customSocials);
                            ?>,
            addCustomSocial() {
                this.customSocials.push({
                    name: '',
                    value: ''
                });
                // Re-initialize icons after adding new element
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },
            removeCustomSocial(index) {
                this.customSocials.splice(index, 1);
                // Re-initialize icons after removing element
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },
            // Check for duplicate social media names
            isDuplicateName(name, currentIndex) {
                return this.customSocials.some((social, index) =>
                    index !== currentIndex &&
                    social.name.toLowerCase().trim() === name.toLowerCase().trim() &&
                    name.trim() !== ''
                );
            },
            // Get duplicate warning message
            getDuplicateWarning(name, currentIndex) {
                if (this.isDuplicateName(name, currentIndex)) {
                    return '<?php _e('This social platform name already exists') ?>';
                }
                return '';
            }
        }
    }

    // Initialize Lucide icons when Alpine is ready
    document.addEventListener('alpine:init', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        initFlatpickrPickers();
    });

    // Flatpickr initialization for date, datetime, and time inputs
    function initFlatpickrPickers(root = document) {
        if (typeof flatpickr === 'undefined') return;

        // Date: Y-m-d with contextual min/max limits
        const dateInputs = root.querySelectorAll('input[type="date"], input[data-picker="date"]');
        dateInputs.forEach(el => {
            if (!el._flatpickr) {
                const now = new Date();
                const year = now.getFullYear();

                const opts = {
                    dateFormat: 'Y-m-d',
                    allowInput: true
                };

                const name = (el.getAttribute('name') || '').toLowerCase();
                const id = (el.getAttribute('id') || '').toLowerCase();

                // Birthday: between 130 years ago and 12 years ago
                if (id === 'birthday' || name === 'birthday') {
                    opts.minDate = new Date(year - 130, now.getMonth(), now.getDate());
                    opts.maxDate = new Date(year - 12, now.getMonth(), now.getDate());
                }

                // Work/Education Start/End Dates: last 100 years up to today
                else if (name.includes('[start_date]') || name.includes('[end_date]')) {
                    opts.minDate = new Date(year - 100, now.getMonth(), now.getDate());
                    opts.maxDate = now;
                }

                // Certifications Issue Date: last 100 years up to today
                else if (name.includes('[issue_date]')) {
                    opts.minDate = new Date(year - 100, now.getMonth(), now.getDate());
                    opts.maxDate = now;
                }

                flatpickr(el, opts);
            }
        });

        // DateTime: Y-m-d H:i:s
        const dateTimeInputs = root.querySelectorAll('input[data-picker="datetime"]');
        dateTimeInputs.forEach(el => {
            if (!el._flatpickr) {
                flatpickr(el, {
                    enableTime: true,
                    enableSeconds: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i:s',
                    allowInput: true
                });
            }
        });

        // Time: H:i:s
        const timeInputs = root.querySelectorAll('input[type="time"], input[data-picker="time"]');
        timeInputs.forEach(el => {
            if (!el._flatpickr) {
                flatpickr(el, {
                    enableTime: true,
                    noCalendar: true,
                    enableSeconds: true,
                    time_24hr: true,
                    dateFormat: 'H:i:s',
                    allowInput: true
                });
            }
        });
    }

    // Date input compatibility check and fallback
    function checkDateInputSupport() {
        const dateInput = document.createElement('input');
        dateInput.type = 'date';
        // Check if browser supports date input
        if (dateInput.type !== 'date') {
            // Browser doesn't support date input
            console.warn('Browser does not support HTML5 date input');
            return false;
        }

        // Additional check for older browsers that claim support but don't work properly
        const testValue = '2024-01-01';
        dateInput.value = testValue;
        return dateInput.value === testValue;
    }

    // Enhanced date input fallback
    function enhanceDateInputs() {
        const dateInputs = document.querySelectorAll('input[type="date"]');

        dateInputs.forEach(input => {
            // Add pattern attribute for better validation
            input.setAttribute('pattern', '[0-9]{4}-[0-9]{2}-[0-9]{2}');

            // Add title for better UX
            input.setAttribute('title', '<?php _e('Please enter date in YYYY-MM-DD format') ?>');

            // Add placeholder text for browsers that show it
            if (!input.placeholder) {
                input.placeholder = 'YYYY-MM-DD';
            }

            // Add validation message
            input.addEventListener('invalid', function(e) {
                if (e.target.validity.patternMismatch) {
                    e.target.setCustomValidity('<?php _e('Please enter date in YYYY-MM-DD format') ?>');
                } else if (e.target.validity.valueMissing) {
                    e.target.setCustomValidity('<?php _e('Please enter a valid date') ?>');
                } else {
                    e.target.setCustomValidity('');
                }
            });

            // Clear custom validity on input
            input.addEventListener('input', function(e) {
                e.target.setCustomValidity('');
            });
        });
    }

    // Form submission loading states
    document.addEventListener('DOMContentLoaded', function() {
        // Check date input support
        if (!checkDateInputSupport()) {
            console.warn('Date input not supported, using fallback behavior');
            // You could add a date picker library here if needed
        }

        // Enhance date inputs
        enhanceDateInputs();

        // Initialize Flatpickr for existing inputs
        if (typeof initFlatpickrPickers === 'function') {
            initFlatpickrPickers(document);
        }

        // Observe dynamic DOM updates to initialize Flatpickr on new inputs
        const container = document.querySelector('[x-ref="profileContainer"]') || document.body;
        if (window.MutationObserver && container && typeof initFlatpickrPickers === 'function') {
            const observer = new MutationObserver(mutations => {
                mutations.forEach(m => {
                    if (m.addedNodes && m.addedNodes.length) {
                        m.addedNodes.forEach(node => {
                            if (node.nodeType === 1) {
                                initFlatpickrPickers(node);
                            }
                        });
                    }
                });
            });
            observer.observe(container, {
                childList: true,
                subtree: true
            });
        }

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>Processing...';

                    // Re-enable after 3 seconds as fallback
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });

        // Initialize intl-tel-input for phone field
        initializePhoneInput();
    });

    // Initialize intl-tel-input for phone field
    function initializePhoneInput() {
        const phoneInput = document.getElementById('phone');
        if (!phoneInput || typeof intlTelInput === 'undefined') {
            // Retry if script hasn't loaded yet
            if (typeof intlTelInput === 'undefined') {
                setTimeout(initializePhoneInput, 100);
            }
            return;
        }

        // Check if already initialized
        if (phoneInput.dataset.intlTelInputInitialized === 'true') {
            return;
        }

        // Get current phone value and extract country code if present
        let currentPhone = phoneInput.value.trim();
        let initialCountry = 'US'; // default
        let phoneNumber = currentPhone;

        // If phone starts with +, try to extract country code
        if (currentPhone.startsWith('+')) {
            // Try to parse with libphonenumber if available
            if (typeof window.parsePhoneNumberFromString === 'function') {
                try {
                    const parsed = window.parsePhoneNumberFromString(currentPhone);
                    if (parsed && parsed.country) {
                        initialCountry = parsed.country;
                        phoneNumber = parsed.nationalNumber || currentPhone.replace(/^\+\d+/, '');
                    }
                } catch (e) {
                    // Fallback
                }
            }
        }

        // Get current language code
        const currentLang = '<?php echo strtolower(lang_code()); ?>';
        const i18nLang = currentLang.length === 2 ? currentLang : 'en';

        // Initialize intl-tel-input
        const itiInstance = intlTelInput(phoneInput, {
            separateDialCode: true,
            initialCountry: initialCountry,
            i18n: i18nLang,
            nationalMode: true,
            formatOnDisplay: true,
            autoPlaceholder: "aggressive",
        });

        // Set phone number if we extracted it
        if (phoneNumber && phoneNumber !== currentPhone) {
            phoneInput.value = phoneNumber;
        }

        // Mark as initialized
        phoneInput.dataset.intlTelInputInitialized = 'true';

        // Store instance globally for easy access
        if (!window.itiInstances) {
            window.itiInstances = {};
        }
        window.itiInstances[phoneInput.id] = itiInstance;

        // Remove leading zero when user types
        phoneInput.addEventListener('input', function(e) {
            let value = this.value.trim();
            // Remove leading zero if present
            if (value.length > 0 && value[0] === '0') {
                value = value.substring(1);
                this.value = value;
            }
        });
    }

    // Helper to get itiInstance
    function getItiInstance() {
        const phoneInput = document.getElementById('phone');
        if (!phoneInput) return null;
        // Try to get from window.itiInstances first
        if (window.itiInstances && window.itiInstances[phoneInput.id]) {
            return window.itiInstances[phoneInput.id];
        }
        // Fallback: try intlTelInputGlobals
        if (window.intlTelInputGlobals && typeof window.intlTelInputGlobals.getInstance === 'function') {
            try {
                return window.intlTelInputGlobals.getInstance(phoneInput);
            } catch (e) {
                // Silent fail
            }
        }
        return null;
    }

    // Update profile form submit to include country code
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            if (phoneInput && phoneInput.value.trim()) {
                const iti = getItiInstance();
                if (iti) {
                    try {
                        // Remove leading zero from phone number if present
                        let phoneValue = phoneInput.value.trim();
                        if (phoneValue.length > 0 && phoneValue[0] === '0') {
                            phoneValue = phoneValue.substring(1);
                            phoneInput.value = phoneValue;
                        }

                        // Get full phone number with country code (E.164 format: +84901234567)
                        // Try getNumber() first, if not available, build manually
                        let fullNumber = null;
                        if (typeof iti.getNumber === 'function') {
                            fullNumber = iti.getNumber();
                        }
                        
                        // Fallback: build manually if getNumber() not available
                        if (!fullNumber) {
                            const countryData = iti.getSelectedCountryData();
                            if (countryData && countryData.dialCode && phoneValue) {
                                fullNumber = '+' + countryData.dialCode + phoneValue;
                            }
                        }

                        if (fullNumber) {
                            phoneInput.value = fullNumber;
                        }
                    } catch (e) {
                        console.warn('Failed to get full phone number:', e);
                    }
                }
            }
        });
    }
</script>

<?php
view_footer();