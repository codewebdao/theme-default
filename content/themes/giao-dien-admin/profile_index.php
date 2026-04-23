<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use App\Libraries\Fastlang as Flang;
use App\Libraries\Fastlang;
use System\Libraries\Session;
use System\Libraries\Render\View;
// [1] LẤY CÁC THÔNG TIN CHUNG
$breadcrumb = [
    [
        'name' => Flang::__('title_languages'),
        'url' => admin_url('profile')
    ]
];
view_header([
    'title' => $title ?? 'CMS Full Form',
    'layout' => 'default',
    'user_info' => $user_info ?? [],
    'menuData' => $menuData ?? [],
    'breadcrumb' => $breadcrumb,
]);
?>
<!-- [ Main Content ] start -->
<div class="pc-container">
    <div class="pc-content">
        <div class="card">


            <!-- FORM -->
            <div class="card-body">
                <div class="space-y-4 sm:space-y-6 w-full min-w-0" x-data="{
                    activeTab: 'profile',
                    isEditingProfile: false,
                    showMessage: false,
                    messageType: '',
                    messageText: '',
                    profile: {
                        fullname: '<?= $user_info['fullname'] ?>',
                        email: '<?= $user_info['email'] ?>',
                        phone: '<?= $user_info['phone'] ?>',
                        dateOfBirth: '<?= $user_info['birthday'] ?>',
                        gender: '<?= $user_info['gender'] ?>',
                        created_at: '<?= $user_info['created_at'] ?>',
                        bio: '<?= $user_info['about_me'] ?>',
                        province: '<?= $user_info['province'] ?? '' ?>',
                        district: '<?= $user_info['district'] ?? '' ?>',
                        ward: '<?= $user_info['ward'] ?? '' ?>',
                        address: '<?= $user_info['address'] ?? '' ?>',
                    },
                    // Address data
                    provinces: [],
                    districts: [],
                    wards: [],
                    security: {
                        currentPassword: '',
                        newPassword: '',
                        confirmPassword: '',
                        twoFactorEnabled: false,
                        loginAlerts: true,
                    },
                    notifications: {
                        communication: true,
                        security: true,
                        features: false,
                        pushAll: true,
                        pushEmail: false,
                        pushNone: false,
                    },
                    saveProfile() {
                        // Submit the profile form directly
                        document.getElementById('profileForm').submit();
                    },
                    updatePassword() {
                        if (!this.security.currentPassword || !this.security.newPassword || !this.security.confirmPassword) {
                            this.showMessageAlert('error', 'Please fill in all password fields');
                            return;
                        }
                        
                        if (this.security.newPassword !== this.security.confirmPassword) {
                            this.showMessageAlert('error', 'New password and confirm password do not match');
                            return;
                        }
                        
                        // Submit the password form directly
                        document.getElementById('passwordForm').submit();
                    },
                    showMessageAlert(type, message) {
                        this.messageType = type;
                        this.messageText = message;
                        this.showMessage = true;
                        
                        // Auto hide after 5 seconds
                        setTimeout(() => {
                            this.showMessage = false;
                        }, 5000);
                    },
                    
                    // Address functions
                    async loadProvinces() {
                        try {
                            const response = await fetch('https://provinces.open-api.vn/api/p/');
                            const data = await response.json();
                            this.provinces = data;
                        } catch (error) {
                            console.error('Error loading provinces:', error);
                            this.showMessageAlert('error', 'Không thể tải danh sách tỉnh/thành phố');
                        }
                    },
                    
                    async loadDistricts() {
                        if (!this.profile.province) {
                            this.districts = [];
                            this.wards = [];
                            this.profile.district = '';
                            this.profile.ward = '';
                            return;
                        }
                        
                        try {
                            const response = await fetch(`https://provinces.open-api.vn/api/p/${this.profile.province}?depth=2`);
                            const data = await response.json();
                            this.districts = data.districts || [];
                            this.wards = [];
                            this.profile.district = '';
                            this.profile.ward = '';
                        } catch (error) {
                            console.error('Error loading districts:', error);
                            this.showMessageAlert('error', 'Không thể tải danh sách quận/huyện');
                        }
                    },
                    
                    async loadWards() {
                        if (!this.profile.district) {
                            this.wards = [];
                            this.profile.ward = '';
                            return;
                        }
                        
                        try {
                            const response = await fetch(`https://provinces.open-api.vn/api/d/${this.profile.district}?depth=2`);
                            const data = await response.json();
                            this.wards = data.wards || [];
                            this.profile.ward = '';
                        } catch (error) {
                            console.error('Error loading wards:', error);
                            this.showMessageAlert('error', 'Không thể tải danh sách phường/xã');
                        }
                    },
                                                                                async init() {
                        // Show flash messages from server
                        <?php
                        $flash_success = Session::flash('success');
                        $flash_error = Session::flash('error');
                        ?>
                        
                        <?php if (!empty($flash_success)): ?>
                            this.showMessageAlert('success', '<?= addslashes($flash_success) ?>');
                        <?php endif; ?>
                        
                        <?php if (!empty($flash_error)): ?>
                            this.showMessageAlert('error', '<?= addslashes($flash_error) ?>');
                        <?php endif; ?>
                        
                        // Load provinces data
                        await this.loadProvinces();
                        
                        // Load districts and wards if user has existing data
                        if (this.profile.province) {
                            await this.loadDistricts();
                            if (this.profile.district) {
                                await this.loadWards();
                            }
                        }
                    }
                }">
                    <!-- Header -->
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight"><?= Fastlang::__('settings') ?></h1>
                        <p class="text-muted-foreground mt-1"><?= Fastlang::__('manage_account_settings') ?></p>
                    </div>

                    <!-- Alert Messages -->
                    <div x-show="showMessage" x-transition class="mb-4" x-cloak>
                        <div :class="messageType === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-blue-50 border border-blue-200 text-blue-700'"
                            class="px-4 py-3 rounded-md flex items-center justify-between">
                            <div class="flex items-center">
                                <i x-show="messageType === 'error'" data-lucide="x-circle" class="w-5 h-5 mr-2"></i>
                                <i x-show="messageType === 'success'" data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                                <span x-text="messageText"></span>
                            </div>
                            <button @click="showMessage = false" class="text-gray-400 hover:text-gray-600">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div dir="ltr" data-orientation="horizontal" class="w-full">
                        <div role="tablist" aria-orientation="horizontal"
                            class="items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-2"
                            tabindex="0" data-orientation="horizontal" style="outline: none;">
                            <button type="button" role="tab"
                                :aria-selected="activeTab === 'profile'" 
                                :data-state="activeTab === 'profile' ? 'active' : 'inactive'"
                                @click="activeTab = 'profile'"
                                class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2"
                                tabindex="0" data-orientation="horizontal" data-radix-collection-item="">
                                <i data-lucide="user" class="h-4 w-4"></i>
                                <?= Fastlang::__('profile_information') ?>
                            </button>
                            <button type="button" role="tab"
                                :aria-selected="activeTab === 'security'" 
                                :data-state="activeTab === 'security' ? 'active' : 'inactive'"
                                @click="activeTab = 'security'"
                                class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2"
                                tabindex="-1" data-orientation="horizontal" data-radix-collection-item="">
                                <i data-lucide="shield" class="h-4 w-4"></i>
                                <?= Fastlang::__('security') ?>
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="space-y-8">
                        <!-- Profile Tab -->
                        <div :data-state="activeTab === 'profile' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel"
                            :aria-labelledby="'tab-profile'" tabindex="0"
                            class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4"
                            :hidden="activeTab !== 'profile'">
                            <div class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-xl font-bold flex items-center gap-2">
                                            <i data-lucide="user" class="h-5 w-5"></i>
                                            <?= Fastlang::__('profile_information') ?>
                                        </h3>
                                        <p class="text-sm text-muted-foreground"><?= Fastlang::__('update_personal_info') ?></p>
                                    </div>
                                    <template x-if="!isEditingProfile">
                                        <button @click="isEditingProfile = true" class="px-4 py-2 text-sm bg-primary text-primary-foreground hover:bg-primary/90 rounded-md font-semibold"><?= Fastlang::__('edit_profile') ?></button>
                                    </template>
                                    <template x-if="isEditingProfile">
                                        <div class="flex gap-2">
                                            <button @click="saveProfile()" class="px-4 py-2 text-sm bg-primary text-primary-foreground hover:bg-primary/90 rounded-md font-semibold"><?= Fastlang::__('save') ?></button>
                                            <button @click="isEditingProfile = false" class="px-4 py-2 text-sm bg-secondary text-secondary-foreground hover:bg-secondary/80 rounded-md font-semibold border border-border"><?= Fastlang::__('cancel') ?></button>
                                        </div>
                                    </template>
                                </div>
                                <div class="space-y-8">
                                    <div class="flex items-center space-x-4 p-4 bg-muted/50 rounded-lg">
                                        <img src="<?= theme_assets('images/avatar.png') ?>" alt="Avatar" class="h-16 w-16 rounded-full">
                                        <div>
                                            <h3 class="font-medium"><?= $user_info['fullname'] ?></h3>
                                            <p class="text-sm text-muted-foreground"><?= $user_info['email'] ?></p>
                                        </div>
                                    </div>

                                    <!-- Profile Form -->
                                    <form id="profileForm" method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <div class="grid gap-y-6 gap-x-4">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <label for="fullName" class="text-sm font-medium"><?= Fastlang::__('full_name') ?></label>
                                                    <input id="fullName" name="fullname" x-model="profile.fullname" :disabled="!isEditingProfile"
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                </div>
                                                <div class="space-y-2">
                                                    <label for="email" class="text-sm font-medium"><?= Fastlang::__('email_address') ?></label>
                                                    <input id="email" name="email" type="email" x-model="profile.email" disabled
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <label for="phone" class="text-sm font-medium"><?= Fastlang::__('phone_number') ?></label>
                                                    <input id="phone" name="phone" type="tel" x-model="profile.phone" :disabled="!isEditingProfile"
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                </div>
                                                <div class="space-y-2">
                                                    <label for="birthday" class="text-sm font-medium"><?= Fastlang::__('date_of_birth') ?></label>
                                                    <input id="birthday" name="birthday" type="date" x-model="profile.dateOfBirth" :disabled="!isEditingProfile"
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <label for="gender" class="text-sm font-medium"><?= Fastlang::__('gender') ?></label>
                                                    <select id="gender" name="gender" x-model="profile.gender" :disabled="!isEditingProfile"
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                        <option value="male"><?= Fastlang::__('male') ?></option>
                                                        <option value="female"><?= Fastlang::__('female') ?></option>
                                                        <option value="other"><?= Fastlang::__('other') ?></option>
                                                    </select>
                                                </div>
                                                <div class="space-y-2">
                                                    <label for="created_at" class="text-sm font-medium"><?= Fastlang::__('created_date') ?></label>
                                                    <input id="created_at" x-model="profile.created_at" disabled
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <label for="about_me" class="text-sm font-medium"><?= Fastlang::__('bio') ?></label>
                                                <textarea id="about_me" name="about_me" x-model="profile.bio" :disabled="!isEditingProfile" rows="4" class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70"></textarea>
                                            </div>

                                            <!-- Address Section -->
                                            <div class="space-y-4 border-t pt-4">
                                                <h4 class="text-md font-medium"><?= Fastlang::__('address') ?></h4>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                    <div class="space-y-2">
                                                        <label for="province" class="text-sm font-medium"><?= Fastlang::__('province') ?></label>
                                                        <select id="province" name="province" x-model="profile.province" :disabled="!isEditingProfile" @change="loadDistricts()"
                                                            class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                            <option value=""><?= Fastlang::__('select_province') ?></option>
                                                            <template x-for="province in provinces" :key="province.code">
                                                                <option :value="province.code" x-text="province.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label for="district" class="text-sm font-medium"><?= Fastlang::__('district') ?></label>
                                                        <select id="district" name="district" x-model="profile.district" :disabled="!isEditingProfile || !profile.province" @change="loadWards()"
                                                            class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                            <option value=""><?= Fastlang::__('select_district') ?></option>
                                                            <template x-for="district in districts" :key="district.code">
                                                                <option :value="district.code" x-text="district.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label for="ward" class="text-sm font-medium"><?= Fastlang::__('ward') ?></label>
                                                        <select id="ward" name="ward" x-model="profile.ward" :disabled="!isEditingProfile || !profile.district"
                                                            class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                            <option value=""><?= Fastlang::__('select_ward') ?></option>
                                                            <template x-for="ward in wards" :key="ward.code">
                                                                <option :value="ward.code" x-text="ward.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="space-y-2">
                                                    <label for="address" class="text-sm font-medium"><?= Fastlang::__('detailed_address') ?></label>
                                                    <input id="address" name="address" x-model="profile.address" :disabled="!isEditingProfile" placeholder="<?= Fastlang::__('address_placeholder') ?>"
                                                        class="w-full px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div :data-state="activeTab === 'security' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel"
                            :aria-labelledby="'tab-security'" tabindex="0"
                            class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4"
                            :hidden="activeTab !== 'security'">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-xl font-bold flex items-center gap-2">
                                        <i data-lucide="shield" class="h-5 w-5"></i>
                                        <?= Fastlang::__('security') ?>
                                    </h3>
                                    <p class="text-sm text-muted-foreground"><?= Fastlang::__('update_password_2fa') ?></p>
                                </div>
                                <div class="border-t border-border pt-6 space-y-6">
                                    <!-- Password Form -->
                                    <form id="passwordForm" method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium"><?= Fastlang::__('password') ?></label>
                                            <div class="space-y-4">
                                                <input type="password" name="current_password" placeholder="<?= Fastlang::__('current_password') ?>" x-model="security.currentPassword" class="w-full sm:w-1/2 px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none">
                                                <input type="password" name="new_password" placeholder="<?= Fastlang::__('new_password') ?>" x-model="security.newPassword" class="w-full sm:w-1/2 px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none">
                                                <input type="password" name="confirm_password" placeholder="<?= Fastlang::__('confirm_new_password') ?>" x-model="security.confirmPassword" class="w-full sm:w-1/2 px-3 py-2 bg-background border border-input rounded-md text-sm focus:ring-ring focus:ring-1 focus:outline-none">
                                            </div>
                                            <button type="button" @click="updatePassword()" class="mt-2 px-4 py-2 text-sm bg-primary text-primary-foreground hover:bg-primary/90 rounded-md font-semibold"><?= Fastlang::__('update_password') ?></button>
                                        </div>
                                    </form>
                                    <!-- <div class="border-t border-border pt-6 flex items-center justify-between">
                                        <div>
                                            <p class="font-medium">Two-Factor Authentication</p>
                                            <p class="text-sm text-muted-foreground">Add an extra layer of security to your account.</p>
                                        </div>
                                        <button @click="security.twoFactorEnabled = !security.twoFactorEnabled" :class="security.twoFactorEnabled ? 'bg-primary' : 'bg-input'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                                            <span :class="security.twoFactorEnabled ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                        </button>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Tab -->
                        <!-- <div x-show="activeTab === 'notifications'" x-transition>
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-xl font-bold flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                        </svg>
                                        Notifications
                                    </h3>
                                    <p class="text-sm text-muted-foreground">Manage how you receive notifications.</p>
                                </div>
                                <div class="border-t border-border pt-6 space-y-6">
                                    <div class="space-y-4">
                                        <p class="font-medium">Email Notifications</p>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm">Communication Emails</p>
                                                    <p class="text-xs text-muted-foreground">Receive emails about your account activity.</p>
                                                </div>
                                                <input type="checkbox" x-model="notifications.communication" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm">Security Emails</p>
                                                    <p class="text-xs text-muted-foreground">Receive emails about important security events.</p>
                                                </div>
                                                <input type="checkbox" x-model="notifications.security" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm">Feature Updates</p>
                                                    <p class="text-xs text-muted-foreground">Receive emails about new features and updates.</p>
                                                </div>
                                                <input type="checkbox" x-model="notifications.features" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-border pt-6 space-y-4">
                                        <p class="font-medium">Push Notifications</p>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3">
                                                <input id="pushAll" type="radio" name="push" x-model="notifications.pushAll" class="h-4 w-4 border-gray-300 text-primary focus:ring-primary">
                                                <label for="pushAll" class="text-sm">Everything</label>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <input id="pushEmail" type="radio" name="push" x-model="notifications.pushEmail" class="h-4 w-4 border-gray-300 text-primary focus:ring-primary">
                                                <label for="pushEmail" class="text-sm">Same as email</label>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <input id="pushNone" type="radio" name="push" x-model="notifications.pushNone" class="h-4 w-4 border-gray-300 text-primary focus:ring-primary">
                                                <label for="pushNone" class="text-sm">No push notifications</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Vue.js and FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>


<?php view_footer(); ?>