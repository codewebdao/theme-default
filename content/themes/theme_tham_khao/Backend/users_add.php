<?php

use System\Libraries\Render;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Load language files
Flang::load('Backend/Global', APP_LANG);
Flang::load('Backend/Users', APP_LANG);

if (!empty($user)) {
    $isEdit = true;
    $actionUrl = admin_url('users/edit/' . $user['id']);
} else {
    $actionUrl = admin_url('users/add');
    $isEdit = false;
}

$breadcrumbs = array(
  [
      'name' => __('Dashboard'),
      'url' => admin_url('home')
  ],
  [
      'name' => __('Users'),
      'url' => admin_url('users')
  ],
  [
      'name' => $isEdit ? __('Edit User') : __('Add User'),
      'url' => admin_url('users/' . ($isEdit ? 'edit/' . $user['id'] : 'add')),
      'active' => true
  ]
);
Render::block('Backend\\Header', ['layout' => 'default', 'title' => $title ?? 'CMS Full Form', 'breadcrumb' => $breadcrumbs ]);

// Get all available permissions (từ tất cả roles + plugins)
$all_permissions = all_permissions();

// Get current user permissions (cho edit mode)
$current_user_permissions = [];
if ($isEdit) {
    // Merge base role + user overrides để show trong UI
    $current_user_permissions = user_permissions($user['role'], $user['permissions'] ?? null);
} else {
    // Add mode: Không có current permissions
    $current_user_permissions = [];
}

?>

<div x-data="userForm()">

  <!-- Header -->
  <div class="flex flex-col gap-4">
    <div>
      <h1 class="text-2xl font-bold text-foreground"><?= $isEdit ? __('Edit User') : __('Add New User') ?></h1>
      <p class="text-muted-foreground"><?= $isEdit ? __('Update user information and permissions') : __('Create a new user account with roles and permissions') ?></p>
    </div>

    <?php if (!empty($error)): ?>
      <?php Render::block('Backend\\Notification', ['layout' => 'default', 'type' => 'error', 'message' => $error]) ?>
    <?php endif; ?>
  </div>

  <!-- Main Content -->
    <div class="p-0">
      <div class="space-y-4 sm:space-y-6 w-full">
        
        <!-- Tabs Navigation -->
        <div dir="ltr" data-orientation="horizontal" class="w-full">
          <div role="tablist" aria-orientation="horizontal"
            class="items-center justify-center rounded-md bg-muted py-1 px-1 text-muted-foreground grid w-full grid-cols-3"
            tabindex="0" data-orientation="horizontal" style="outline: none;">
            <button type="button" role="tab"
              :aria-selected="activeTab === 'basic'" 
              :data-state="activeTab === 'basic' ? 'active' : 'inactive'"
              @click="activeTab = 'basic'"
              class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2"
              tabindex="0" data-orientation="horizontal" data-radix-collection-item="">
              <i data-lucide="user" class="h-4 w-4"></i>
              <?= __('Basic Information') ?>
            </button>
            <button type="button" role="tab"
              :aria-selected="activeTab === 'personal'" 
              :data-state="activeTab === 'personal' ? 'active' : 'inactive'"
              @click="activeTab = 'personal'"
              class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2"
              tabindex="-1" data-orientation="horizontal" data-radix-collection-item="">
              <i data-lucide="user-circle" class="h-4 w-4"></i>
              <?= __('Personal Information') ?>
            </button>
            <button type="button" role="tab"
              :aria-selected="activeTab === 'security'" 
              :data-state="activeTab === 'security' ? 'active' : 'inactive'"
              @click="activeTab = 'security'"
              class="justify-center whitespace-nowrap rounded-sm px-2.5 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm flex items-center gap-2"
              tabindex="-1" data-orientation="horizontal" data-radix-collection-item="">
              <i data-lucide="shield" class="h-4 w-4"></i>
              <?= __('Roles Permission') ?>
            </button>
          </div>

        <!-- Main Form -->
        <form id="userForm" action="<?= $actionUrl ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
            
            <!-- Tab Content -->
            <div :data-state="activeTab === 'basic' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel"
              :aria-labelledby="'tab-basic'" tabindex="0"
              class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4"
              :hidden="activeTab !== 'basic'">
              
              <div class="">

                    

              
                <!-- Basic Information Tab -->
                <div>
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-xl font-bold flex items-center gap-2">
                            <i data-lucide="user" class="h-5 w-5"></i>
                            <?= __('Basic Information') ?>
                            </h3>
                            <p class="text-sm text-muted-foreground"><?= __('Enter the user\'s basic information') ?></p>
                        </div>

                    
                        
                        <div class="grid gap-y-6 gap-x-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                <label for="username" class="text-sm font-medium"><?= __('username') ?> <span class="text-red-500">*</span></label>
                                <input id="username" name="username" type="text" 
                                        value="<?= $isEdit ? htmlspecialchars($user['username']) : '' ?>"
                                        placeholder="<?= __('placeholder_username') ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                                <?php if (!empty($errors['username'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                    <?php foreach ($errors['username'] as $error): ?>
                                        <p><?= $error; ?></p>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                <label for="fullname" class="text-sm font-medium"><?= __('fullname') ?> <span class="text-red-500">*</span></label>
                                <input id="fullname" name="fullname" type="text" 
                                        value="<?= $isEdit ? htmlspecialchars($user['fullname']) : '' ?>"
                                        placeholder="<?= __('placeholder_fullname') ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                                <?php if (!empty($errors['fullname'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                    <?php foreach ($errors['fullname'] as $error): ?>
                                        <p><?= $error; ?></p>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                <label for="email" class="text-sm font-medium"><?= __('email') ?> <span class="text-red-500">*</span></label>
                                <input id="email" name="email" type="email" 
                                        value="<?= $isEdit ? htmlspecialchars($user['email']) : '' ?>"
                                        placeholder="<?= __('placeholder_email') ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                    <?php foreach ($errors['email'] as $error): ?>
                                        <p><?= $error; ?></p>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                <label for="phone" class="text-sm font-medium"><?= __('phone') ?></label>
                                <input id="phone" name="phone" type="tel" 
                                        value="<?= $isEdit ? htmlspecialchars($user['phone']) : '' ?>"
                                        placeholder="<?= __('placeholder_phone') ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <?php if (!empty($errors['phone'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                    <?php foreach ($errors['phone'] as $error): ?>
                                        <p><?= $error; ?></p>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                <label for="status" class="text-sm font-medium"><?= __('status') ?></label>
                                <select id="status" name="status" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                    <?php foreach ($status as $status_option): ?>
                                    <option value="<?= $status_option ?>" <?= ($isEdit && $user['status'] == $status_option) ? 'selected' : '' ?>><?= __($status_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($errors['status'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                    <?php foreach ($errors['status'] as $error): ?>
                                        <p><?= $error; ?></p>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                <label for="role" class="text-sm font-medium"><?= __('role') ?></label>
                                <select id="role" name="role" x-model="selectedRole" @change="setActiveRole($event.target.value)" class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                    <?php foreach ($roles as $role_key => $role_permissions): ?>
                                    <option value="<?= $role_key ?>" <?= ($isEdit && $user['role'] == $role_key) ? 'selected' : '' ?>><?= __(ucfirst($role_key)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($errors['role'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                    <?php foreach ($errors['role'] as $error): ?>
                                        <p><?= $error; ?></p>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    
                    </div>
                </div>

                <!-- Password Section (Outside tabs) -->
                <div class="mt-6">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold flex items-center gap-2">
                                    <i data-lucide="lock" class="h-5 w-5"></i>
                                    <?= __('Password Settings') ?>
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    <?php if ($isEdit): ?>
                                        <?= __('Change user password (leave blank to keep current password)') ?>
                                    <?php else: ?>
                                        <?= __('Set up user password for account access') ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if ($isEdit): ?>
                            <!-- Edit Mode: Optional password change -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div><h4 class="text-base font-medium"><?= __('change password') ?>?</h4></div>
                                    <button type="button" @click="changePassword = !changePassword" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                                        <i data-lucide="square-pen" class="h-4 w-4 mr-2"></i>
                                        <?= __('change password') ?>
                                    </button>
                                </div>

                                <div x-show="changePassword" x-transition class="space-y-4 p-4 bg-muted/50 rounded-lg">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label for="password" class="text-sm font-medium"><?= __('password') ?></label>
                                            <input id="password" name="password" type="password" 
                                                placeholder="<?= __('placeholder_password') ?>"
                                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <?php if (!empty($errors['password'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['password'] as $error): ?>
                                                <p><?= $error; ?></p>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="space-y-2">
                                            <label for="password_repeat" class="text-sm font-medium"><?= __('password_repeat') ?></label>
                                            <input id="password_repeat" name="password_repeat" type="password" 
                                                placeholder="<?= __('placeholder_password_repeat') ?>"
                                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <?php if (!empty($errors['password_repeat'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['password_repeat'] as $error): ?>
                                                <p><?= $error; ?></p>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Add Mode: Required password -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="password" class="text-sm font-medium"><?= __('password') ?> <span class="text-red-500">*</span></label>
                                    <input id="password" name="password" type="password" 
                                        placeholder="<?= __('placeholder_password') ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                                    <?php if (!empty($errors['password'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                        <?php foreach ($errors['password'] as $error): ?>
                                        <p><?= $error; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                    <label for="password_repeat" class="text-sm font-medium"><?= __('password_repeat') ?> <span class="text-red-500">*</span></label>
                                    <input id="password_repeat" name="password_repeat" type="password" 
                                        placeholder="<?= __('placeholder_password_repeat') ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                                    <?php if (!empty($errors['password_repeat'])): ?>
                                    <div class="text-red-500 text-sm mt-1">
                                        <?php foreach ($errors['password_repeat'] as $error): ?>
                                        <p><?= $error; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

              </div>
            </div>

        <!-- Personal Information Tab -->
        <div :data-state="activeTab === 'personal' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel"
          :aria-labelledby="'tab-personal'" tabindex="0"
          class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4"
          :hidden="activeTab !== 'personal'">
          
          <div class="">
          
          <!-- Personal Information Tab Content -->
          <div>
              <div class="space-y-6">
                  <div>
                      <h3 class="text-xl font-bold flex items-center gap-2">
                      <i data-lucide="user-circle" class="h-5 w-5"></i>
                      <?= __('Personal Information') ?>
                      </h3>
                      <p class="text-sm text-muted-foreground"><?= __('Manage personal details and profile settings') ?></p>
                  </div>

                  <div class="space-y-6">
                      
                      <!-- Profile Visibility Section -->
                      <div class="space-y-4">
                          <div>
                              <h4 class="text-lg font-medium"><?= __('Profile Visibility') ?></h4>
                              <p class="text-sm text-muted-foreground"><?= __('Control profile visibility settings') ?></p>
                          </div>
                          
                          <!-- Display Profile Toggle -->
                          <div class="space-y-1">
                              <div class="flex items-center justify-between p-4 border border-border rounded-lg">
                                  <div class="flex items-center">
                                      <i data-lucide="eye" class="w-5 h-5 text-muted-foreground mr-3"></i>
                                      <div>
                                          <h4 class="text-sm font-medium text-foreground"><?= __('Profile Visibility') ?></h4>
                                          <p class="text-xs text-muted-foreground"><?= __('Allow others to find and view this user\'s profile') ?></p>
                                      </div>
                                  </div>
                                  <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="hidden" name="display" value="0">
                                      <input
                                          type="checkbox"
                                          id="display"
                                          name="display"
                                          value="1"
                                          <?= ($isEdit && ($user['display'] ?? 0) == 1) ? 'checked' : ''; ?>
                                          class="sr-only peer">
                                      <div class="w-11 h-6 bg-muted peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-background after:border-border after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                  </label>
                              </div>
                          </div>
                      </div>

                      <!-- Personal Details Section -->
                      <div class="space-y-4">
                          <div>
                              <h4 class="text-lg font-medium"><?= __('Personal Details') ?></h4>
                              <p class="text-sm text-muted-foreground"><?= __('Enter personal information') ?></p>
                          </div>

                          <?php 
                          // Get personal data
                          $personal = [];
                          if ($isEdit && !empty($user['personal'])) {
                              $personal = is_string($user['personal']) ? json_decode($user['personal'], true) : $user['personal'];
                          }
                          ?>

                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                              <!-- Birthday -->
                              <div class="space-y-2">
                                  <label for="birthday" class="text-sm font-medium"><?= __('Birthday') ?></label>
                                  <div class="relative">
                                      <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                          <i data-lucide="calendar" class="w-4 h-4 text-muted-foreground"></i>
                                      </div>
                                      <input
                                          type="date"
                                          id="birthday"
                                          name="birthday"
                                          value="<?= $isEdit ? htmlspecialchars($user['birthday'] ?? '') : '' ?>"
                                          class="flex h-10 w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                  </div>
                                  <?php if (!empty($errors['birthday'])): ?>
                                      <div class="text-red-500 text-sm mt-1">
                                          <?php foreach ($errors['birthday'] as $error): ?>
                                              <p><?= $error; ?></p>
                                          <?php endforeach; ?>
                                      </div>
                                  <?php endif; ?>
                              </div>

                              <!-- Gender -->
                              <div class="space-y-2">
                                  <label for="gender" class="text-sm font-medium"><?= __('Gender') ?></label>
                                  <div class="relative">
                                      <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                          <i data-lucide="users" class="w-4 h-4 text-muted-foreground"></i>
                                      </div>
                                      <select
                                          id="gender"
                                          name="gender"
                                          class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                          <option value=""><?= __('Select Gender') ?></option>
                                          <option value="male" <?= ($isEdit && ($user['gender'] ?? '') === 'male') ? 'selected' : ''; ?>><?= __('Male') ?></option>
                                          <option value="female" <?= ($isEdit && ($user['gender'] ?? '') === 'female') ? 'selected' : ''; ?>><?= __('Female') ?></option>
                                          <option value="other" <?= ($isEdit && ($user['gender'] ?? '') === 'other') ? 'selected' : ''; ?>><?= __('Other') ?></option>
                                      </select>
                                  </div>
                                  <?php if (!empty($errors['gender'])): ?>
                                      <div class="text-red-500 text-sm mt-1">
                                          <?php foreach ($errors['gender'] as $error): ?>
                                              <p><?= $error; ?></p>
                                          <?php endforeach; ?>
                                      </div>
                                  <?php endif; ?>
                              </div>
                          </div>

                          <!-- About Me -->
                          <div class="space-y-2">
                              <label for="about_me" class="text-sm font-medium"><?= __('About Me') ?></label>
                              <div class="relative">
                                  <div class="absolute top-3 left-0 flex items-start pl-3">
                                      <i data-lucide="file-text" class="w-4 h-4 text-muted-foreground"></i>
                                  </div>
                                  <textarea
                                      id="about_me"
                                      name="about_me"
                                      rows="4"
                                      class="flex w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                                      placeholder="<?= __('Tell us about this user...') ?>"><?= htmlspecialchars($personal['about_me'] ?? '') ?></textarea>
                              </div>
                              <?php if (!empty($errors['about_me'])): ?>
                                  <div class="text-red-500 text-sm mt-1">
                                      <?php foreach ($errors['about_me'] as $error): ?>
                                          <p><?= $error; ?></p>
                                      <?php endforeach; ?>
                                  </div>
                              <?php endif; ?>
                          </div>
                      </div>

                      <!-- Contact & Location Section -->
                      <div class="space-y-4">
                          <div>
                              <h4 class="text-lg font-medium"><?= __('Contact & Location') ?></h4>
                              <p class="text-sm text-muted-foreground"><?= __('Address and location information') ?></p>
                          </div>

                          <!-- Country -->
                          <div class="space-y-2">
                              <label for="country" class="text-sm font-medium"><?= __('Country') ?></label>
                              <div class="relative">
                                  <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                      <i data-lucide="flag" class="w-4 h-4 text-muted-foreground"></i>
                                  </div>
                                  <select
                                      id="country"
                                      name="country"
                                      class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                      <option value=""><?= __('Select Country') ?></option>
                                      <option value="VN" <?= ($isEdit && ($user['country'] ?? '') === 'VN') ? 'selected' : ''; ?>>🇻🇳 Vietnam</option>
                                      <option value="US" <?= ($isEdit && ($user['country'] ?? '') === 'US') ? 'selected' : ''; ?>>🇺🇸 United States</option>
                                      <option value="GB" <?= ($isEdit && ($user['country'] ?? '') === 'GB') ? 'selected' : ''; ?>>🇬🇧 United Kingdom</option>
                                      <option value="JP" <?= ($isEdit && ($user['country'] ?? '') === 'JP') ? 'selected' : ''; ?>>🇯🇵 Japan</option>
                                      <option value="KR" <?= ($isEdit && ($user['country'] ?? '') === 'KR') ? 'selected' : ''; ?>>🇰🇷 South Korea</option>
                                      <option value="CN" <?= ($isEdit && ($user['country'] ?? '') === 'CN') ? 'selected' : ''; ?>>🇨🇳 China</option>
                                      <option value="TH" <?= ($isEdit && ($user['country'] ?? '') === 'TH') ? 'selected' : ''; ?>>🇹🇭 Thailand</option>
                                      <option value="SG" <?= ($isEdit && ($user['country'] ?? '') === 'SG') ? 'selected' : ''; ?>>🇸🇬 Singapore</option>
                                  </select>
                              </div>
                              <?php if (!empty($errors['country'])): ?>
                                  <div class="text-red-500 text-sm mt-1">
                                      <?php foreach ($errors['country'] as $error): ?>
                                          <p><?= $error; ?></p>
                                      <?php endforeach; ?>
                                  </div>
                              <?php endif; ?>
                          </div>

                          <?php 
                          // Get address data
                          $address = [];
                          if ($isEdit && !empty($user['address'])) {
                              $address = is_string($user['address']) ? json_decode($user['address'], true) : $user['address'];
                          }
                          ?>

                          <!-- Address Fields -->
                          <div class="space-y-4">
                              <!-- Address 1 -->
                              <div class="space-y-2">
                                  <label for="address1" class="text-sm font-medium"><?= __('Address Line 1') ?></label>
                                  <div class="relative">
                                      <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                          <i data-lucide="home" class="w-4 h-4 text-muted-foreground"></i>
                                      </div>
                                      <input
                                          type="text"
                                          id="address1"
                                          name="address1"
                                          value="<?= htmlspecialchars($address['address1'] ?? '') ?>"
                                          class="flex h-10 w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                          placeholder="<?= __('Address Line 1') ?>">
                                  </div>
                              </div>

                              <!-- Address 2 -->
                              <div class="space-y-2">
                                  <label for="address2" class="text-sm font-medium"><?= __('Address Line 2') ?></label>
                                  <div class="relative">
                                      <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                          <i data-lucide="building" class="w-4 h-4 text-muted-foreground"></i>
                                      </div>
                                      <input
                                          type="text"
                                          id="address2"
                                          name="address2"
                                          value="<?= htmlspecialchars($address['address2'] ?? '') ?>"
                                          class="flex h-10 w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                          placeholder="<?= __('Address Line 2') ?>">
                                  </div>
                              </div>

                              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                  <!-- City -->
                                  <div class="space-y-2">
                                      <label for="city" class="text-sm font-medium"><?= __('City') ?></label>
                                      <div class="relative">
                                          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                              <i data-lucide="map-pin" class="w-4 h-4 text-muted-foreground"></i>
                                          </div>
                                          <input
                                              type="text"
                                              id="city"
                                              name="city"
                                              value="<?= htmlspecialchars($address['city'] ?? '') ?>"
                                              class="flex h-10 w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                              placeholder="<?= __('City') ?>">
                                      </div>
                                  </div>

                                  <!-- State -->
                                  <div class="space-y-2">
                                      <label for="state" class="text-sm font-medium"><?= __('State/Province') ?></label>
                                      <div class="relative">
                                          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                              <i data-lucide="map" class="w-4 h-4 text-muted-foreground"></i>
                                          </div>
                                          <input
                                              type="text"
                                              id="state"
                                              name="state"
                                              value="<?= htmlspecialchars($address['state'] ?? '') ?>"
                                              class="flex h-10 w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                              placeholder="<?= __('State/Province') ?>">
                                      </div>
                                  </div>

                                  <!-- Zip Code -->
                                  <div class="space-y-2">
                                      <label for="zipcode" class="text-sm font-medium"><?= __('ZIP/Postal Code') ?></label>
                                      <div class="relative">
                                          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                              <i data-lucide="hash" class="w-4 h-4 text-muted-foreground"></i>
                                          </div>
                                          <input
                                              type="text"
                                              id="zipcode"
                                              name="zipcode"
                                              value="<?= htmlspecialchars($address['zipcode'] ?? '') ?>"
                                              class="flex h-10 w-full rounded-md border border-input bg-background px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                              placeholder="<?= __('ZIP/Postal Code') ?>">
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              </div>
          </div>

          </div>
        </div>

        <!-- Security Tab -->
        <div :data-state="activeTab === 'security' ? 'active' : 'inactive'" data-orientation="horizontal" role="tabpanel"
          :aria-labelledby="'tab-security'" tabindex="0"
          class="ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 mt-4"
          :hidden="activeTab !== 'security'">
          
          <div class="">
          
          <!-- Security Tab Content -->
          <div>
              <div class="space-y-6">
                  <div>
                      <h3 class="text-xl font-bold flex items-center gap-2">
                      <i data-lucide="shield" class="h-5 w-5"></i>
                      <?= __('Roles Permission') ?>
                      </h3>
                      <p class="text-sm text-muted-foreground"><?= __('Manage user roles and permissions') ?></p>
                  </div>

                  <div class="space-y-6">
                      

                      <!-- Roles & Permissions Section -->
                      <div class="space-y-4">
                          <div>
                              <h4 class="text-lg font-medium"><?= __('roles_permissions') ?></h4>
                              <p class="text-sm text-muted-foreground"><?= __('Configure user roles and permissions') ?></p>
                          </div>

                          <div class="bg-card border rounded-lg overflow-hidden">
                              <div class="flex">
                                  <!-- Roles Sidebar -->
                                  <div class="border-r border-border">
                                      <div class="p-4 border-b border-border">
                                          <h5 class="text-sm font-medium text-foreground"><?= __('Select Role') ?></h5>
                                          <p class="text-xs text-muted-foreground mt-1"><?= __('Choose a role to configure permissions') ?></p>
                                      </div>
                                      <div class="p-2 space-y-1 max-h-[400px] overflow-y-auto">
                                          <template x-for="(permissions, role) in roles" :key="role">
                                              <div class="flex items-center space-x-3 p-3 rounded-lg cursor-pointer transition-colors hover:bg-accent/50"
                                                  :class="{ 'bg-accent border border-primary/20': selectedRole === role }"
                                                  @click="setActiveRole(role)">
                                                  <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-white text-sm font-medium"
                                                      :class="{
                                                          'bg-red-600': role === 'admin',
                                                          'bg-orange-500': role === 'moderator',
                                                          'bg-purple-500': role === 'editor',
                                                          'bg-blue-500': role === 'author',
                                                          'bg-indigo-600': role === 'shop_manager',
                                                          'bg-teal-500': role === 'vendor',
                                                          'bg-cyan-500': role === 'fulfillment',
                                                          'bg-emerald-500': role === 'accountant',
                                                          'bg-lime-600': role === 'support',
                                                          'bg-sky-500': role === 'customer',
                                                          'bg-gray-500': role === 'member',
                                                          'bg-slate-600': !['admin','moderator','editor','author','shop_manager','vendor','fulfillment','accountant','support','customer','member'].includes(role)
                                                      }">
                                                      <i :class="getRoleIcon(role)" class="text-sm"></i>
                                                  </div>
                                                  <div class="flex-1 min-w-0">
                                                      <div class="text-sm font-medium text-foreground capitalize" x-text="role"></div>
                                                      <div class="text-xs text-muted-foreground" x-text="getRoleDescription(role)"></div>
                                                  </div>
                                                  <div x-show="selectedRole === role" class="flex-shrink-0">
                                                      <i data-lucide="check" class="text-primary"></i>
                                                  </div>
                                              </div>
                                          </template>
                                      </div>
                                  </div>

                                  <!-- Role Content -->
                                  <div class="flex-1 h-[470px] overflow-y-auto" x-show="selectedRole">
                                      <div class="p-6">
                                          <!-- Role Header -->
                                          <div class="flex items-center space-x-4 mb-0 pb-2">
                                              <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center text-white text-lg font-medium"
                                                  :class="{
                                                      'bg-red-600': selectedRole === 'admin',
                                                      'bg-orange-500': selectedRole === 'moderator',
                                                      'bg-purple-500': selectedRole === 'editor',
                                                      'bg-blue-500': selectedRole === 'author',
                                                      'bg-indigo-600': selectedRole === 'shop_manager',
                                                      'bg-teal-500': selectedRole === 'vendor',
                                                      'bg-cyan-500': selectedRole === 'fulfillment',
                                                      'bg-emerald-500': selectedRole === 'accountant',
                                                      'bg-lime-600': selectedRole === 'support',
                                                      'bg-sky-500': selectedRole === 'customer',
                                                      'bg-gray-500': selectedRole === 'member',
                                                      'bg-slate-600': !['admin','moderator','editor','author','shop_manager','vendor','fulfillment','accountant','support','customer','member'].includes(selectedRole)
                                                  }">
                                                  <i :class="getRoleIcon(selectedRole)" class="text-lg"></i>
                                              </div>
                                              <div class="flex-1">
                                                  <h3 class="text-lg font-semibold text-foreground capitalize" x-text="selectedRole"></h3>
                                                  <p class="text-sm text-muted-foreground" x-text="getRoleDescription(selectedRole)"></p>
                                              </div>
                                              <!-- Button có nền đỏ xóa tất cả permissions -->
                                              <button type="button" 
                                                      @click="clearAllPermissions()"
                                                      class="btn inline-flex items-center px-4 py-2 text-xs font-medium text-white bg-red-500 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors"
                                                      title="<?= __('Clear All Permissions') ?>">
                                                  <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                                                  <?= __('Clear All') ?>
                                              </button>
                                          </div>

                                          <!-- Permission Summary -->
                                          <div class="bg-muted/50 rounded-lg p-4 py-2 mb-2">
                                              <div class="flex items-center gap-8 mb-3">
                                                  <div class="flex items-center space-x-4">
                                                      <div class="text-center">
                                                          <div class="text-2xl font-bold text-foreground" x-text="getGrantedPermissionsCount() + '/' + getTotalPermissionsCount()"></div>
                                                          <div class="text-xs text-muted-foreground" x-text="translations['Permissions']"></div>
                                                      </div>
                                                      <div class="text-center">
                                                          <div class="text-lg font-semibold text-foreground" x-text="getPermissionPercentage() + '%'"></div>
                                                          <div class="text-xs text-muted-foreground" x-text="translations['Access Level']"></div>
                                                      </div>
                                                  </div>
                                                  <div class="text-right flex-1">
                                                      <div class="text-sm font-medium text-foreground" x-text="getPermissionLevelText()"></div>
                                                          <div class="w-auto h-2 bg-muted rounded-full overflow-hidden mt-1">
                                                              <div class="h-full rounded-full transition-all duration-300"
                                                                  :class="{
                                                                  'bg-green-500': getPermissionPercentage() >= 90,
                                                                  'bg-blue-500': getPermissionPercentage() >= 70 && getPermissionPercentage() < 90,
                                                                  'bg-yellow-500': getPermissionPercentage() >= 40 && getPermissionPercentage() < 70,
                                                                  'bg-orange-500': getPermissionPercentage() >= 20 && getPermissionPercentage() < 40,
                                                                  'bg-red-500': getPermissionPercentage() < 20
                                                                  }"
                                                                  :style="'width: ' + getPermissionPercentage() + '%'"></div>
                                                          </div>
                                                      </div>
                                                  </div>

                                                  <!-- Permission Groups -->
                                                  <div class="space-y-4">
                                                      <template x-for="(permissions, resource) in allPermissions" :key="resource">
                                                          <div class="border border-border rounded-lg overflow-hidden bg-background">
                                                              <div class="flex items-center justify-between p-3 cursor-pointer hover:bg-muted/30 transition-colors"
                                                                   @click="toggleResourceExpand(resource)">
                                                                  <div class="flex items-center space-x-3 flex-1">
                                                                      <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                                                          <i :class="getResourceIcon(resource)" class="text-primary text-sm"></i>
                                                                      </div>
                                                                      <div class="flex-1">
                                                                          <h4 class="text-sm font-medium text-foreground capitalize" x-text="resource"></h4>
                                                                          <p class="text-xs text-muted-foreground" x-text="Array.isArray(permissions) ? permissions.length + ' ' + translations['permissions available'] : '0 ' + translations['permissions available']"></p>
                                                                      </div>
                                                                  </div>
                                                                  <div class="flex items-center space-x-3">
                                                                      <div class="flex items-center space-x-2">
                                                                          <span class="text-xs text-muted-foreground" x-text="translations['Enable All']"></span>
                                                                          <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                                                              <input type="checkbox" 
                                                                                      :checked="isAllPermissionsInGroupEnabled(resource)"
                                                                                      @change="toggleAllPermissionsInGroup(resource, $event.target.checked)"
                                                                                      class="sr-only peer">
                                                                              <div class="w-9 h-5 bg-muted peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-background after:border-border after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                                                          </label>
                                                                      </div>
                                                                      <div class="text-muted-foreground transition-transform duration-200"
                                                                           :class="{'rotate-180': isResourceExpanded(resource)}">
                                                                          <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              <div x-show="isResourceExpanded(resource)" 
                                                                   x-transition:enter="transition ease-out duration-200"
                                                                   x-transition:enter-start="opacity-0 -translate-y-2"
                                                                   x-transition:enter-end="opacity-100 translate-y-0"
                                                                   x-transition:leave="transition ease-in duration-150"
                                                                   x-transition:leave-start="opacity-100 translate-y-0"
                                                                   x-transition:leave-end="opacity-0 -translate-y-2"
                                                                   class="border-t border-border">
                                                                  <div class="p-2 space-y-1">
                                                                      <template x-for="permission in (Array.isArray(permissions) ? permissions : [])" :key="permission">
                                                                          <div class="flex items-center justify-between px-2 py-1 rounded-lg hover:bg-muted/50 transition-colors">
                                                                              <div class="flex items-center space-x-3 flex-1">
                                                                                  <div class="w-5 h-5 rounded bg-muted flex items-center justify-center">
                                                                                      <i class="fas fa-key text-xs text-muted-foreground"></i>
                                                                                  </div>
                                                                                  <div class="flex-1">
                                                                                      <div class="text-sm font-medium text-foreground capitalize" x-text="permission"></div>
                                                                                      <div class="text-xs text-muted-foreground" x-text="getPermissionDescription(permission)"></div>
                                                                                  </div>
                                                                              </div>
                                                                              <div class="flex-shrink-0">
                                                                                  <label class="relative inline-flex items-center cursor-pointer">
                                                                                      <input type="checkbox" 
                                                                                          :name="`permissions[${resource}][]`" 
                                                                                          :value="permission" 
                                                                                          x-model="selectedPermissions[resource]"
                                                                                          class="sr-only peer">
                                                                                      <div class="w-9 h-5 bg-muted peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-background after:border-border after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                                                                  </label>
                                                                              </div>
                                                                          </div>
                                                                      </template>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </template>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              </div>
          </div>

          </div>
         </div>
       </div>

       <!-- Submit Buttons -->
       <div class="flex flex-col sm:flex-row sm:justify-end sm:space-x-2 gap-2 pt-6">
           <a href="<?= admin_url('users') ?>" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
               <i data-lucide="chevron-left" class="h-4 w-4 mr-2"></i>
               <?= __('back_to_list') ?>
           </a>
           <button type="submit" form="userForm" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
               <i data-lucide="save" class="h-4 w-4 mr-2"></i>
               <?= $isEdit ? __('submit_edit') : __('submit_add') ?>
           </button>
       </div>
     </div>
      </div>
    </div>
    </form>
</div>

<style type="text/css">
.flatpickr-calendar{background:transparent;opacity:0;display:none;text-align:center;visibility:hidden;padding:0;-webkit-animation:none;animation:none;direction:ltr;border:0;font-size:14px;line-height:24px;border-radius:5px;position:absolute;width:307.875px;-webkit-box-sizing:border-box;box-sizing:border-box;-ms-touch-action:manipulation;touch-action:manipulation;background:#fff;-webkit-box-shadow:1px 0 0 #e6e6e6,-1px 0 0 #e6e6e6,0 1px 0 #e6e6e6,0 -1px 0 #e6e6e6,0 3px 13px rgba(0,0,0,0.08);box-shadow:1px 0 0 #e6e6e6,-1px 0 0 #e6e6e6,0 1px 0 #e6e6e6,0 -1px 0 #e6e6e6,0 3px 13px rgba(0,0,0,0.08)}.flatpickr-calendar.open,.flatpickr-calendar.inline{opacity:1;max-height:640px;visibility:visible}.flatpickr-calendar.open{display:inline-block;z-index:99999}.flatpickr-calendar.animate.open{-webkit-animation:fpFadeInDown 300ms cubic-bezier(.23,1,.32,1);animation:fpFadeInDown 300ms cubic-bezier(.23,1,.32,1)}.flatpickr-calendar.inline{display:block;position:relative;top:2px}.flatpickr-calendar.static{position:absolute;top:calc(100% + 2px)}.flatpickr-calendar.static.open{z-index:999;display:block}.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer:nth-child(n+1) .flatpickr-day.inRange:nth-child(7n+7){-webkit-box-shadow:none !important;box-shadow:none !important}.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer:nth-child(n+2) .flatpickr-day.inRange:nth-child(7n+1){-webkit-box-shadow:-2px 0 0 #e6e6e6,5px 0 0 #e6e6e6;box-shadow:-2px 0 0 #e6e6e6,5px 0 0 #e6e6e6}.flatpickr-calendar .hasWeeks .dayContainer,.flatpickr-calendar .hasTime .dayContainer{border-bottom:0;border-bottom-right-radius:0;border-bottom-left-radius:0}.flatpickr-calendar .hasWeeks .dayContainer{border-left:0}.flatpickr-calendar.hasTime .flatpickr-time{height:40px;border-top:1px solid #e6e6e6}.flatpickr-calendar.noCalendar.hasTime .flatpickr-time{height:auto}.flatpickr-calendar:before,.flatpickr-calendar:after{position:absolute;display:block;pointer-events:none;border:solid transparent;content:'';height:0;width:0;left:22px}.flatpickr-calendar.rightMost:before,.flatpickr-calendar.arrowRight:before,.flatpickr-calendar.rightMost:after,.flatpickr-calendar.arrowRight:after{left:auto;right:22px}.flatpickr-calendar.arrowCenter:before,.flatpickr-calendar.arrowCenter:after{left:50%;right:50%}.flatpickr-calendar:before{border-width:5px;margin:0 -5px}.flatpickr-calendar:after{border-width:4px;margin:0 -4px}.flatpickr-calendar.arrowTop:before,.flatpickr-calendar.arrowTop:after{bottom:100%}.flatpickr-calendar.arrowTop:before{border-bottom-color:#e6e6e6}.flatpickr-calendar.arrowTop:after{border-bottom-color:#fff}.flatpickr-calendar.arrowBottom:before,.flatpickr-calendar.arrowBottom:after{top:100%}.flatpickr-calendar.arrowBottom:before{border-top-color:#e6e6e6}.flatpickr-calendar.arrowBottom:after{border-top-color:#fff}.flatpickr-calendar:focus{outline:0}.flatpickr-wrapper{position:relative;display:inline-block}.flatpickr-months{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex}.flatpickr-months .flatpickr-month{background:transparent;color:rgba(0,0,0,0.9);fill:rgba(0,0,0,0.9);height:34px;line-height:1;text-align:center;position:relative;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;overflow:hidden;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1}.flatpickr-months .flatpickr-prev-month,.flatpickr-months .flatpickr-next-month{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;text-decoration:none;cursor:pointer;position:absolute;top:0;height:34px;padding:10px;z-index:3;color:rgba(0,0,0,0.9);fill:rgba(0,0,0,0.9)}.flatpickr-months .flatpickr-prev-month.flatpickr-disabled,.flatpickr-months .flatpickr-next-month.flatpickr-disabled{display:none}.flatpickr-months .flatpickr-prev-month i,.flatpickr-months .flatpickr-next-month i{position:relative}.flatpickr-months .flatpickr-prev-month.flatpickr-prev-month,.flatpickr-months .flatpickr-next-month.flatpickr-prev-month{/*
      /*rtl:begin:ignore*/left:0/*
      /*rtl:end:ignore*/}/*
      /*rtl:begin:ignore*/
/*
      /*rtl:end:ignore*/
.flatpickr-months .flatpickr-prev-month.flatpickr-next-month,.flatpickr-months .flatpickr-next-month.flatpickr-next-month{/*
      /*rtl:begin:ignore*/right:0/*
      /*rtl:end:ignore*/}/*
      /*rtl:begin:ignore*/
/*
      /*rtl:end:ignore*/
.flatpickr-months .flatpickr-prev-month:hover,.flatpickr-months .flatpickr-next-month:hover{color:#959ea9}.flatpickr-months .flatpickr-prev-month:hover svg,.flatpickr-months .flatpickr-next-month:hover svg{fill:#f64747}.flatpickr-months .flatpickr-prev-month svg,.flatpickr-months .flatpickr-next-month svg{width:14px;height:14px}.flatpickr-months .flatpickr-prev-month svg path,.flatpickr-months .flatpickr-next-month svg path{-webkit-transition:fill .1s;transition:fill .1s;fill:inherit}.numInputWrapper{position:relative;height:auto}.numInputWrapper input,.numInputWrapper span{display:inline-block}.numInputWrapper input{width:100%}.numInputWrapper input::-ms-clear{display:none}.numInputWrapper input::-webkit-outer-spin-button,.numInputWrapper input::-webkit-inner-spin-button{margin:0;-webkit-appearance:none}.numInputWrapper span{position:absolute;right:0;width:14px;padding:0 4px 0 2px;height:50%;line-height:50%;opacity:0;cursor:pointer;border:1px solid rgba(57,57,57,0.15);-webkit-box-sizing:border-box;box-sizing:border-box}.numInputWrapper span:hover{background:rgba(0,0,0,0.1)}.numInputWrapper span:active{background:rgba(0,0,0,0.2)}.numInputWrapper span:after{display:block;content:"";position:absolute}.numInputWrapper span.arrowUp{top:0;border-bottom:0}.numInputWrapper span.arrowUp:after{border-left:4px solid transparent;border-right:4px solid transparent;border-bottom:4px solid rgba(57,57,57,0.6);top:26%}.numInputWrapper span.arrowDown{top:50%}.numInputWrapper span.arrowDown:after{border-left:4px solid transparent;border-right:4px solid transparent;border-top:4px solid rgba(57,57,57,0.6);top:40%}.numInputWrapper span svg{width:inherit;height:auto}.numInputWrapper span svg path{fill:rgba(0,0,0,0.5)}.numInputWrapper:hover{background:rgba(0,0,0,0.05)}.numInputWrapper:hover span{opacity:1}.flatpickr-current-month{font-size:135%;line-height:inherit;font-weight:300;color:inherit;position:absolute;width:75%;left:12.5%;padding:7.48px 0 0 0;line-height:1;height:34px;display:inline-block;text-align:center;-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0)}.flatpickr-current-month span.cur-month{font-family:inherit;font-weight:700;color:inherit;display:inline-block;margin-left:.5ch;padding:0}.flatpickr-current-month span.cur-month:hover{background:rgba(0,0,0,0.05)}.flatpickr-current-month .numInputWrapper{width:6ch;width:7ch\0;display:inline-block}.flatpickr-current-month .numInputWrapper span.arrowUp:after{border-bottom-color:rgba(0,0,0,0.9)}.flatpickr-current-month .numInputWrapper span.arrowDown:after{border-top-color:rgba(0,0,0,0.9)}.flatpickr-current-month input.cur-year{background:transparent;-webkit-box-sizing:border-box;box-sizing:border-box;color:inherit;cursor:text;padding:0 0 0 .5ch;margin:0;display:inline-block;font-size:inherit;font-family:inherit;font-weight:300;line-height:inherit;height:auto;border:0;border-radius:0;vertical-align:initial;-webkit-appearance:textfield;-moz-appearance:textfield;appearance:textfield}.flatpickr-current-month input.cur-year:focus{outline:0}.flatpickr-current-month input.cur-year[disabled],.flatpickr-current-month input.cur-year[disabled]:hover{font-size:100%;color:rgba(0,0,0,0.5);background:transparent;pointer-events:none}.flatpickr-current-month .flatpickr-monthDropdown-months{appearance:menulist;background:transparent;border:none;border-radius:0;box-sizing:border-box;color:inherit;cursor:pointer;font-size:inherit;font-family:inherit;font-weight:300;height:auto;line-height:inherit;margin:-1px 0 0 0;outline:none;padding:0 0 0 .5ch;position:relative;vertical-align:initial;-webkit-box-sizing:border-box;-webkit-appearance:menulist;-moz-appearance:menulist;width:auto}.flatpickr-current-month .flatpickr-monthDropdown-months:focus,.flatpickr-current-month .flatpickr-monthDropdown-months:active{outline:none}.flatpickr-current-month .flatpickr-monthDropdown-months:hover{background:rgba(0,0,0,0.05)}.flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month{background-color:transparent;outline:none;padding:0}.flatpickr-weekdays{background:transparent;text-align:center;overflow:hidden;width:100%;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center;height:28px}.flatpickr-weekdays .flatpickr-weekdaycontainer{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1}span.flatpickr-weekday{cursor:default;font-size:90%;background:transparent;color:rgba(0,0,0,0.54);line-height:1;margin:0;text-align:center;display:block;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;font-weight:bolder}.dayContainer,.flatpickr-weeks{padding:1px 0 0 0}.flatpickr-days{position:relative;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-align:start;-webkit-align-items:flex-start;-ms-flex-align:start;align-items:flex-start;width:307.875px}.flatpickr-days:focus{outline:0}.dayContainer{padding:0;outline:0;text-align:left;width:307.875px;min-width:307.875px;max-width:307.875px;-webkit-box-sizing:border-box;box-sizing:border-box;display:inline-block;display:-ms-flexbox;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-wrap:wrap;-ms-flex-pack:justify;-webkit-justify-content:space-around;justify-content:space-around;-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);opacity:1}.dayContainer + .dayContainer{-webkit-box-shadow:-1px 0 0 #e6e6e6;box-shadow:-1px 0 0 #e6e6e6}.flatpickr-day{background:none;border:1px solid transparent;border-radius:150px;-webkit-box-sizing:border-box;box-sizing:border-box;color:#393939;cursor:pointer;font-weight:400;width:14.2857143%;-webkit-flex-basis:14.2857143%;-ms-flex-preferred-size:14.2857143%;flex-basis:14.2857143%;max-width:39px;height:39px;line-height:39px;margin:0;display:inline-block;position:relative;-webkit-box-pack:center;-webkit-justify-content:center;-ms-flex-pack:center;justify-content:center;text-align:center}.flatpickr-day.inRange,.flatpickr-day.prevMonthDay.inRange,.flatpickr-day.nextMonthDay.inRange,.flatpickr-day.today.inRange,.flatpickr-day.prevMonthDay.today.inRange,.flatpickr-day.nextMonthDay.today.inRange,.flatpickr-day:hover,.flatpickr-day.prevMonthDay:hover,.flatpickr-day.nextMonthDay:hover,.flatpickr-day:focus,.flatpickr-day.prevMonthDay:focus,.flatpickr-day.nextMonthDay:focus{cursor:pointer;outline:0;background:#e6e6e6;border-color:#e6e6e6}.flatpickr-day.today{border-color:#959ea9}.flatpickr-day.today:hover,.flatpickr-day.today:focus{border-color:#959ea9;background:#959ea9;color:#fff}.flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange,.flatpickr-day.selected.inRange,.flatpickr-day.startRange.inRange,.flatpickr-day.endRange.inRange,.flatpickr-day.selected:focus,.flatpickr-day.startRange:focus,.flatpickr-day.endRange:focus,.flatpickr-day.selected:hover,.flatpickr-day.startRange:hover,.flatpickr-day.endRange:hover,.flatpickr-day.selected.prevMonthDay,.flatpickr-day.startRange.prevMonthDay,.flatpickr-day.endRange.prevMonthDay,.flatpickr-day.selected.nextMonthDay,.flatpickr-day.startRange.nextMonthDay,.flatpickr-day.endRange.nextMonthDay{background:#569ff7;-webkit-box-shadow:none;box-shadow:none;color:#fff;border-color:#569ff7}.flatpickr-day.selected.startRange,.flatpickr-day.startRange.startRange,.flatpickr-day.endRange.startRange{border-radius:50px 0 0 50px}.flatpickr-day.selected.endRange,.flatpickr-day.startRange.endRange,.flatpickr-day.endRange.endRange{border-radius:0 50px 50px 0}.flatpickr-day.selected.startRange + .endRange:not(:nth-child(7n+1)),.flatpickr-day.startRange.startRange + .endRange:not(:nth-child(7n+1)),.flatpickr-day.endRange.startRange + .endRange:not(:nth-child(7n+1)){-webkit-box-shadow:-10px 0 0 #569ff7;box-shadow:-10px 0 0 #569ff7}.flatpickr-day.selected.startRange.endRange,.flatpickr-day.startRange.startRange.endRange,.flatpickr-day.endRange.startRange.endRange{border-radius:50px}.flatpickr-day.inRange{border-radius:0;-webkit-box-shadow:-5px 0 0 #e6e6e6,5px 0 0 #e6e6e6;box-shadow:-5px 0 0 #e6e6e6,5px 0 0 #e6e6e6}.flatpickr-day.flatpickr-disabled,.flatpickr-day.flatpickr-disabled:hover,.flatpickr-day.prevMonthDay,.flatpickr-day.nextMonthDay,.flatpickr-day.notAllowed,.flatpickr-day.notAllowed.prevMonthDay,.flatpickr-day.notAllowed.nextMonthDay{color:rgba(57,57,57,0.3);background:transparent;border-color:transparent;cursor:default}.flatpickr-day.flatpickr-disabled,.flatpickr-day.flatpickr-disabled:hover{cursor:not-allowed;color:rgba(57,57,57,0.1)}.flatpickr-day.week.selected{border-radius:0;-webkit-box-shadow:-5px 0 0 #569ff7,5px 0 0 #569ff7;box-shadow:-5px 0 0 #569ff7,5px 0 0 #569ff7}.flatpickr-day.hidden{visibility:hidden}.rangeMode .flatpickr-day{margin-top:1px}.flatpickr-weekwrapper{float:left}.flatpickr-weekwrapper .flatpickr-weeks{padding:0 12px;-webkit-box-shadow:1px 0 0 #e6e6e6;box-shadow:1px 0 0 #e6e6e6}.flatpickr-weekwrapper .flatpickr-weekday{float:none;width:100%;line-height:28px}.flatpickr-weekwrapper span.flatpickr-day,.flatpickr-weekwrapper span.flatpickr-day:hover{display:block;width:100%;max-width:none;color:rgba(57,57,57,0.3);background:transparent;cursor:default;border:none}.flatpickr-innerContainer{display:block;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-sizing:border-box;box-sizing:border-box;overflow:hidden}.flatpickr-rContainer{display:inline-block;padding:0;-webkit-box-sizing:border-box;box-sizing:border-box}.flatpickr-time{text-align:center;outline:0;display:block;height:0;line-height:40px;max-height:40px;-webkit-box-sizing:border-box;box-sizing:border-box;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex}.flatpickr-time:after{content:"";display:table;clear:both}.flatpickr-time .numInputWrapper{-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;width:40%;height:40px;float:left}.flatpickr-time .numInputWrapper span.arrowUp:after{border-bottom-color:#393939}.flatpickr-time .numInputWrapper span.arrowDown:after{border-top-color:#393939}.flatpickr-time.hasSeconds .numInputWrapper{width:26%}.flatpickr-time.time24hr .numInputWrapper{width:49%}.flatpickr-time input{background:transparent;-webkit-box-shadow:none;box-shadow:none;border:0;border-radius:0;text-align:center;margin:0;padding:0;height:inherit;line-height:inherit;color:#393939;font-size:14px;position:relative;-webkit-box-sizing:border-box;box-sizing:border-box;-webkit-appearance:textfield;-moz-appearance:textfield;appearance:textfield}.flatpickr-time input.flatpickr-hour{font-weight:bold}.flatpickr-time input.flatpickr-minute,.flatpickr-time input.flatpickr-second{font-weight:400}.flatpickr-time input:focus{outline:0;border:0}.flatpickr-time .flatpickr-time-separator,.flatpickr-time .flatpickr-am-pm{height:inherit;float:left;line-height:inherit;color:#393939;font-weight:bold;width:2%;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;-webkit-align-self:center;-ms-flex-item-align:center;align-self:center}.flatpickr-time .flatpickr-am-pm{outline:0;width:18%;cursor:pointer;text-align:center;font-weight:400}.flatpickr-time input:hover,.flatpickr-time .flatpickr-am-pm:hover,.flatpickr-time input:focus,.flatpickr-time .flatpickr-am-pm:focus{background:#eee}.flatpickr-input[readonly]{cursor:pointer}@-webkit-keyframes fpFadeInDown{from{opacity:0;-webkit-transform:translate3d(0,-20px,0);transform:translate3d(0,-20px,0)}to{opacity:1;-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0)}}@keyframes fpFadeInDown{from{opacity:0;-webkit-transform:translate3d(0,-20px,0);transform:translate3d(0,-20px,0)}to{opacity:1;-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0)}}
</style>


<script>
function userForm() {
    return {
        activeTab: 'basic',
        showAdd: false,
        changePassword: <?php echo isset($errors) && (isset($errors['password']) || isset($errors['password_repeat'])) ? 'true' : 'false' ?>,
        roles: <?php echo json_encode($roles); ?>,
        allPermissions: <?php echo json_encode($all_permissions); ?>,
        selectedRole: '<?= $isEdit ? $user['role'] : 'member' ?>',
        selectedPermissions: {},
        currentUserPermissions: <?php echo json_encode($current_user_permissions); ?>,
        isEditMode: <?= $isEdit ? 'true' : 'false' ?>,
        expandedResources: {},
        translations: {
            'Standard user role': '<?= __('Standard user role') ?>',
            'Permission access': '<?= __('Permission access') ?>',
            'Create new items': '<?= __('Create new items') ?>',
            'View and read items': '<?= __('View and read items') ?>',
            'Edit existing items': '<?= __('Edit existing items') ?>',
            'Remove items': '<?= __('Remove items') ?>',
            'Full management access': '<?= __('Full management access') ?>',
            'Publish content': '<?= __('Publish content') ?>',
            'Moderate content': '<?= __('Moderate content') ?>',
            'permissions available': '<?= __('permissions available') ?>',
            'Enable All': '<?= __('Enable All') ?>',
            'Permissions': '<?= __('Permissions') ?>',
            'Access Level': '<?= __('Access Level') ?>',
            'Full Access': '<?= __('Full Access') ?>',
            'High Access': '<?= __('High Access') ?>',
            'Medium Access': '<?= __('Medium Access') ?>',
            'Low Access': '<?= __('Low Access') ?>',
            'Minimal Access': '<?= __('Minimal Access') ?>'
        },
        formData: {
            username: '<?= $isEdit ? htmlspecialchars($user['username']) : '' ?>',
            fullname: '<?= $isEdit ? htmlspecialchars($user['fullname']) : '' ?>',
            email: '<?= $isEdit ? htmlspecialchars($user['email']) : '' ?>',
            phone: '<?= $isEdit ? htmlspecialchars($user['phone']) : '' ?>',
            password: '',
            password_repeat: '',
            status: '<?= $isEdit ? htmlspecialchars($user['status']) : 'active' ?>'
        },

        init() {
            // Initialize all resources as collapsed
            for (let resource in this.allPermissions) {
                if (this.allPermissions.hasOwnProperty(resource)) {
                    this.expandedResources[resource] = false;
                }
            }
            
            // Set default role if none selected
            if (!this.selectedRole && Object.keys(this.roles).length > 0) {
                this.selectedRole = Object.keys(this.roles)[0];
            }
            
            // Load permissions based on mode
            this.updatePermissions(this.selectedRole);
            
            // Add form submit event listener
            const form = document.getElementById('userForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    // Remove old permission inputs
                    const oldInputs = form.querySelectorAll('input[name^="permissions"]');
                    oldInputs.forEach(input => input.remove());
                    
                    // Create hidden inputs from selectedPermissions
                    // Chỉ gửi resources CÓ PERMISSIONS (bao gồm cả empty array)
                    for (let resource in this.selectedPermissions) {
                        if (!this.selectedPermissions.hasOwnProperty(resource)) continue;
                        
                        const permissions = this.selectedPermissions[resource];
                        
                        if (Array.isArray(permissions)) {
                            if (permissions.length > 0) {
                                // Add each permission as separate input
                                permissions.forEach(permission => {
                                    if (permission) {  // Skip empty values
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = `permissions[${resource}][]`;
                                        input.value = permission;
                                        form.appendChild(input);
                                    }
                                });
                            } else {
                                // Empty array → send empty marker để indicate resource bị clear
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = `permissions[${resource}][]`;
                                input.value = '';
                                form.appendChild(input);
                            }
                        }
                    }
                });
            }
        },

        setActiveRole(role) {
            const oldRole = this.selectedRole;
            this.selectedRole = role;
            
            // Nếu đang ở edit mode và đổi role, hỏi xác nhận
            if (this.isEditMode && oldRole !== role) {
                if (confirm('<?= __('Switching role will reset permissions to default. Continue?') ?>')) {
                    // Tắt edit mode để load permissions mặc định của role mới
                    this.isEditMode = false;
                    this.updatePermissions(role);
                } else {
                    // Revert về role cũ
                    this.selectedRole = oldRole;
                    return;
                }
            } else {
                // Add mode hoặc cùng role → update permissions
                this.updatePermissions(role);
            }
        },
        
        getRoleIcon(role) {
            const icons = {
                'admin': 'fas fa-crown',
                'moderator': 'fas fa-user-shield',
                'editor': 'fas fa-edit',
                'author': 'fas fa-pen-fancy',
                'shop_manager': 'fas fa-store-alt',
                'vendor': 'fas fa-store',
                'fulfillment': 'fas fa-shipping-fast',
                'accountant': 'fas fa-calculator',
                'support': 'fas fa-headset',
                'customer': 'fas fa-shopping-bag',
                'member': 'fas fa-user',
                'subscriber': 'fas fa-user-plus'
            };
            
            // Try exact match first
            if (icons[role]) return icons[role];
            
            // Fuzzy match for plugin roles
            const roleLower = role.toLowerCase();
            if (roleLower.includes('admin')) return 'fas fa-crown';
            if (roleLower.includes('moderator')) return 'fas fa-user-shield';
            if (roleLower.includes('editor')) return 'fas fa-edit';
            if (roleLower.includes('author')) return 'fas fa-pen-fancy';
            if (roleLower.includes('manager')) return 'fas fa-user-tie';
            if (roleLower.includes('vendor') || roleLower.includes('seller')) return 'fas fa-store';
            if (roleLower.includes('fulfill') || roleLower.includes('shipping')) return 'fas fa-shipping-fast';
            if (roleLower.includes('account') || roleLower.includes('finance')) return 'fas fa-calculator';
            if (roleLower.includes('support') || roleLower.includes('help')) return 'fas fa-headset';
            if (roleLower.includes('customer') || roleLower.includes('buyer')) return 'fas fa-shopping-bag';
            
            return 'fas fa-user';
        },
        
        getRoleDescription(role) {
            // Get description from roles config
            if (this.roles[role] && this.roles[role].description) {
                const desc = this.roles[role].description;
                // Handle array (từ merge plugins) - chỉ lấy phần tử đầu
                if (Array.isArray(desc)) {
                    return desc[0] || this.translations['Standard user role'];
                }
                return desc;
            }
            
            // Fallback descriptions nếu không có trong config
            const fallbackDescriptions = {
                'admin': 'Full system access and control',
                'moderator': 'Content moderation and user management',
                'editor': 'Content editing and publishing',
                'author': 'Content creation and editing',
                'shop_manager': 'Manage shop and products',
                'vendor': 'Sell products and manage inventory',
                'fulfillment': 'Process and fulfill orders',
                'accountant': 'Financial management and reporting',
                'support': 'Customer support and assistance',
                'customer': 'Shopping and order management',
                'member': 'Basic user access'
            };
            
            return fallbackDescriptions[role] || this.translations['Standard user role'];
        },
        
        getResourceIcon(resource) {
            const resourceLower = resource.toLowerCase();
            
            // Backend controllers
            if (resourceLower.includes('home') || resourceLower.includes('dashboard')) return 'fas fa-home';
            if (resourceLower.includes('users')) return 'fas fa-users';
            if (resourceLower.includes('posts')) return 'fas fa-file-alt';
            if (resourceLower.includes('pages')) return 'fas fa-file';
            if (resourceLower.includes('terms')) return 'fas fa-tags';
            if (resourceLower.includes('files')) return 'fas fa-folder';
            if (resourceLower.includes('media')) return 'fas fa-images';
            if (resourceLower.includes('comments')) return 'fas fa-comments';
            if (resourceLower.includes('settings')) return 'fas fa-cog';
            if (resourceLower.includes('languages')) return 'fas fa-language';
            if (resourceLower.includes('options')) return 'fas fa-sliders-h';
            if (resourceLower.includes('libraries')) return 'fas fa-book';
            if (resourceLower.includes('backups')) return 'fas fa-database';
            
            // Plugin controllers
            if (resourceLower.includes('acfields')) return 'fas fa-th-large';
            if (resourceLower.includes('commentflow')) return 'fas fa-comment-dots';
            if (resourceLower.includes('ecommerce') || resourceLower.includes('shop')) return 'fas fa-shopping-cart';
            
            return 'fas fa-folder';
        },
        
        getPermissionDescription(permission) {
            if (typeof permission !== 'string') {
                return this.translations['Permission access'];
            }
            
            const permLower = permission.toLowerCase();
            const descriptions = {
                'index': this.translations['View and read items'],
                'add': this.translations['Create new items'],
                'create': this.translations['Create new items'],
                'edit': this.translations['Edit existing items'],
                'update': this.translations['Edit existing items'],
                'delete': this.translations['Remove items'],
                'manage': this.translations['Full management access'],
                'publish': this.translations['Publish content'],
                'moderate': this.translations['Moderate content'],
                'export': 'Export data',
                'import': 'Import data',
                'bulkedit': 'Bulk edit items',
                'clone': 'Clone/duplicate items',
                'copy': 'Copy items',
                'changestatus': 'Change status',
                'settings': 'Manage settings',
                'approve': 'Approve content',
                'spam': 'Mark as spam',
                'trash': 'Move to trash',
                'restore': 'Restore deleted items',
                'download': 'Download files'
            };
            
            return descriptions[permLower] || this.translations['Permission access'];
        },
        
        updatePermissions(role) {
            // Reset selected permissions
            this.selectedPermissions = {};

            // Initialize permissions arrays cho TẤT CẢ resources
            for (let resource in this.allPermissions) {
                if (this.allPermissions.hasOwnProperty(resource)) {
                    this.selectedPermissions[resource] = [];
                }
            }

            if (this.isEditMode) {
                /**
                 * EDIT MODE:
                 * - Load permissions từ user_permissions($user['role'], $user['permissions'])
                 * - Đã được tính toán ở PHP (merge base + override)
                 */
                for (let resource in this.currentUserPermissions) {
                    if (this.currentUserPermissions.hasOwnProperty(resource) && 
                        Array.isArray(this.currentUserPermissions[resource])) {
                        this.selectedPermissions[resource] = [...this.currentUserPermissions[resource]];
                    }
                }
            } else {
                /**
                 * ADD MODE:
                 * - Load permissions từ base role config
                 * - Chọn role nào thì check permissions của role đó
                 */
                if (role && this.roles[role] && this.roles[role].permissions) {
                    const rolePermissions = this.roles[role].permissions;
                    for (let resource in rolePermissions) {
                        if (rolePermissions.hasOwnProperty(resource) && 
                            Array.isArray(rolePermissions[resource])) {
                            this.selectedPermissions[resource] = [...rolePermissions[resource]];
                        }
                    }
                }
            }
        },
        
        isAllPermissionsInGroupEnabled(resource) {
            if (!this.selectedPermissions[resource] || !this.allPermissions[resource] || !Array.isArray(this.allPermissions[resource])) {
                return false;
            }
            return this.allPermissions[resource].every(permission => 
                this.selectedPermissions[resource].includes(permission)
            );
        },
        
        toggleAllPermissionsInGroup(resource, enabled) {
            if (!this.selectedPermissions[resource]) {
                this.selectedPermissions[resource] = [];
            }
            
            if (enabled && Array.isArray(this.allPermissions[resource])) {
                this.selectedPermissions[resource] = [...this.allPermissions[resource]];
            } else {
                this.selectedPermissions[resource] = [];
            }
        },
        
        toggleResourceExpand(resource) {
            this.expandedResources[resource] = !this.expandedResources[resource];
        },
        
        isResourceExpanded(resource) {
            return this.expandedResources[resource] === true;
        },

        resetToRoleDefaults() {
            if (this.selectedRole && this.roles[this.selectedRole] && this.roles[this.selectedRole].permissions) {
                const rolePermissions = this.roles[this.selectedRole].permissions;
                
                // Reset về permissions mặc định của role
                for (let resource in this.allPermissions) {
                    if (this.allPermissions.hasOwnProperty(resource)) {
                        if (rolePermissions[resource] && Array.isArray(rolePermissions[resource])) {
                            this.selectedPermissions[resource] = [...rolePermissions[resource]];
                        } else {
                            this.selectedPermissions[resource] = [];
                        }
                    }
                }
            }
        },
        
        getGrantedPermissionsCount() {
            let count = 0;
            for (let resource in this.selectedPermissions) {
                if (this.selectedPermissions[resource]) {
                    count += this.selectedPermissions[resource].length;
                }
            }
            return count;
        },
        
        getTotalPermissionsCount() {
            let count = 0;
            for (let resource in this.allPermissions) {
                if (Array.isArray(this.allPermissions[resource])) {
                    count += this.allPermissions[resource].length;
                }
            }
            return count;
        },
        
        getPermissionPercentage() {
            const total = this.getTotalPermissionsCount();
            const granted = this.getGrantedPermissionsCount();
            return total > 0 ? Math.round((granted / total) * 100) : 0;
        },
        
        getPermissionLevelClass() {
            const percentage = this.getPermissionPercentage();
            if (percentage >= 90) return 'permission-level-full';
            if (percentage >= 70) return 'permission-level-high';
            if (percentage >= 40) return 'permission-level-medium';
            if (percentage >= 20) return 'permission-level-low';
            return 'permission-level-minimal';
        },
        
        getPermissionLevelText() {
            const percentage = this.getPermissionPercentage();
            if (percentage >= 90) return this.translations['Full Access'];
            if (percentage >= 70) return this.translations['High Access'];
            if (percentage >= 40) return this.translations['Medium Access'];
            if (percentage >= 20) return this.translations['Low Access'];
            return this.translations['Minimal Access'];
        },
        
        // Clear all permissions for the current user
        clearAllPermissions() {
            // Reset all permissions to empty arrays
            for (let resource in this.selectedPermissions) {
                if (this.selectedPermissions.hasOwnProperty(resource)) {
                    this.selectedPermissions[resource] = [];
                }
            }
        }
     }
 }

 // Flatpickr initialization for date inputs
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

             flatpickr(el, opts);
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
         input.setAttribute('title', '<?= __('Please enter date in YYYY-MM-DD format') ?>');
         
         // Add placeholder text for browsers that show it
         if (!input.placeholder) {
             input.placeholder = 'YYYY-MM-DD';
         }
         
         // Add validation message
         input.addEventListener('invalid', function(e) {
             if (e.target.validity.patternMismatch) {
                 e.target.setCustomValidity('<?= __('Please enter date in YYYY-MM-DD format') ?>');
             } else if (e.target.validity.valueMissing) {
                 e.target.setCustomValidity('<?= __('Please enter a valid date') ?>');
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

 // Initialize everything when DOM is ready
 document.addEventListener('DOMContentLoaded', function() {
     // Check date input support
     if (!checkDateInputSupport()) {
         console.warn('Date input not supported, using fallback behavior');
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
         observer.observe(container, { childList: true, subtree: true });
     }
 });
 </script>

<?php Render::block('Backend\\Footer', ['layout' => 'default']); ?>
