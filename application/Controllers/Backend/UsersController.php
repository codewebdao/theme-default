<?php
namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use System\Core\BaseController;
use App\Models\UsersModel;
use System\Libraries\Session;
use System\Libraries\Render\View;
use System\Libraries\Security;
use App\Libraries\Fastmail;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Validate;
use App\Libraries\Fasttoken;

class UsersController extends BackendController {

    protected $usersModel;
    protected $mailer;  

    public function __construct()
    {
        parent::__construct();
        $this->usersModel = new UsersModel();
        
        // Load assets for user management
        
        //Flang::load('general', APP_LANG);
        Flang::load('Backend/Users');
    }

    public function index()
    {
        // Get filter parameters
        $search = S_GET('q') ?? '';
        $limit = S_GET('limit') ?? option('default_limit');
        $sort = S_GET('sort') ?? 'id';
        $order = S_GET('order') ?? 'DESC';
        $paged = S_GET('page') ?? 1;
        $role = S_GET('role') ?? '';

        // Validate page and limit
        if ($paged < 1) {
            $paged = 1;
        }
        if ($limit < 1) {
            $limit = option('default_limit');
        }

        // Build WHERE clause
        $where = '';
        $params = [];

        // Search filter (wrap in parentheses for OR conditions)
        if (!empty($search)) {
            $where = "(username LIKE ? OR email LIKE ? OR phone LIKE ? OR fullname LIKE ?)";
            $params = [
                '%' . $search . '%', 
                '%' . $search . '%', 
                '%' . $search . '%', 
                '%' . $search . '%'
            ];
        }

        // Role filter
        if (!empty($role)) {
            if (!empty($where)) {
                $where .= " AND role = ?";
            } else {
                $where = "role = ?";
            }
            $params[] = $role;
        }

        // Build ORDER BY clause
        if (!empty($sort) && !empty($order)) {
            $orderBy = $sort . ' ' . $order;
        } else {
            $orderBy = 'id DESC';
        }

        // Get paginated users
        $users = $this->usersModel->getUsersPage($where, $params, $orderBy, $paged, $limit);
        
        // Pass data to view
        $this->data('users', $users);
        $this->data('search', $search);
        $this->data('limit', $limit);
        $this->data('role', $role);
        $this->data('sort', $sort);
        $this->data('order', $order);
        $this->data('title', __('list user'));
        $this->data('csrf_token', Session::csrf_token());
        
        echo View::make('users_index', $this->data)->render();
    }

    //index, add, edit, delete, update
    public function add() {
        //TODO: code: not yet coded permission check.  
        View::addCss('users-page-css', 'css/users.css', [], null, 'all', false);
        View::addJs('users-alpine', 'js/alpinejs.3.15.0.min.js', [], null, false, false, false, false);
        View::addJs('users-flatpickr', 'js/flatpickr.v4.6.13.min.js', [], null, false, false, false, false);
        View::addJs('users-lucide-auth', 'js/lucide-auth.min.js', [], null, false, false, false, false);
        
        if (HAS_POST('csrf_token')){
            $csrf_token = S_POST('csrf_token') ?? '';
            if (!Session::csrf_verify($csrf_token)){
                $this->data('error', __('csrf_failed'));
            } else {
                $errors = [];
                $input = [];
                $rules = [];
                
                // Username (required)
                if (HAS_POST('username')){
                    $input['username'] = trim(S_POST('username') ?? '');
                    $rules['username'] = [
                        'rules' => [Validate::alnum('_'), Validate::length(6, 30)],
                        'messages' => [
                            __('username_invalid'),
                            sprintf(__('username_length'), 6, 30)
                        ]
                    ];
                }
                
                // Fullname (required)
                if (HAS_POST('fullname')){
                    $input['fullname'] = trim(S_POST('fullname') ?? '');
                    $rules['fullname'] = [
                        'rules' => [Validate::name(2, 150)],
                        'messages' => [__('Min %1% & Max %2% char, and only letters (Spaces, hyphens, dots, and middle dots cannot be doubled consecutively)', 2, 150)]
                    ];
                }
                
                // Email (required)
                if (HAS_POST('email')){
                    $input['email'] = trim(S_POST('email') ?? '');
                    $rules['email'] = [
                        'rules' => [Validate::email(), Validate::length(6, 150)],
                        'messages' => [
                            __('email_invalid'),
                            sprintf(__('email_length'), 6, 150)
                        ]
                    ];
                }
                
                // Phone (optional)
                if (HAS_POST('phone')){
                    $input['phone'] = trim(S_POST('phone') ?? '');
                    $rules['phone'] = [
                        'rules' => [Validate::optional(Validate::phone()), Validate::optional(Validate::length(5, 30))],
                        'messages' => [
                            __('phone_invalid'),
                            sprintf(__('phone_length'), 5, 30)
                        ]
                    ];
                }
                
                // Password (required)
                if (HAS_POST('password')){
                    $input['password'] = S_POST('password') ?? '';
                    $rules['password'] = [
                        'rules' => [Validate::length(6, 60)],
                        'messages' => [sprintf(__('password_length'), 6, 60)]
                    ];
                }
                
                // Password repeat
                if (HAS_POST('password_repeat')){
                    $input['password_repeat'] = S_POST('password_repeat');
                    $rules['password_repeat'] = [
                        'rules' => [Validate::equals($input['password'])],
                        'messages' => [sprintf(__('password_repeat_invalid'), $input['password_repeat'])]
                    ];
                }
                
                // Role (required)
                if (HAS_POST('role')){
                    $input['role'] = S_POST('role') ?? '';
                    $rules['role'] = [
                        'rules' => [
                            Validate::notEmpty(),
                            Validate::length(1, 64),
                            Validate::callback(function($value) {
                                $roles = config_roles();
                                return isset($roles[$value]);
                            }),
                        ],
                        'messages' => [
                            __('role_option'),
                            __('Role must be between 1 and 64 characters'),
                            __('Invalid role or role is inactive'),
                        ]
                    ];
                }
                
                /**
                 * Permissions Override
                 * 
                 * So sánh permissions được chọn với base role permissions
                 * → Tạo structure add/remove
                 * → Chỉ lưu nếu có thay đổi, ngược lại lưu NULL
                 */
                if (HAS_POST('permissions')) {
                    $submittedPermissions = S_POST('permissions') ?? [];
                    $userRole = $input['role'] ?? 'member';
                    
                    // Get base role permissions
                    $basePermissions = user_permissions($userRole, null);
                    
                    // Generate add/remove structure
                    $override = override_permissions($basePermissions, $submittedPermissions);
                    
                    // Only save if có thay đổi
                    if (!empty($override['add']) || !empty($override['remove'])) {
                        $input['permissions'] = json_encode($override);
                    } else {
                        // Không thay đổi → NULL (dùng base role)
                        $input['permissions'] = null;
                    }
                } else {
                    // Không submit permissions → NULL
                    $input['permissions'] = null;
                }
                
                // Status (required)
                if (HAS_POST('status')){
                    $input['status'] = S_POST('status') ?? '';
                    $rules['status'] = [
                        'rules' => [Validate::notEmpty()],
                        'messages' => [__('status_option')]
                    ];
                }
                
                // Birthday (optional)
                if (HAS_POST('birthday') && !empty(S_POST('birthday'))){
                    $input['birthday'] = S_POST('birthday') ?? '';
                    $rules['birthday'] = [
                        'rules' => [Validate::date()],
                        'messages' => [__('birthday_invalid')]
                    ];
                }
                
                // Gender (optional)
                if (HAS_POST('gender') && !empty(S_POST('gender'))){
                    $input['gender'] = S_POST('gender') ?? '';
                    $rules['gender'] = [
                        'rules' => [Validate::in(['male', 'female', 'other'])],
                        'messages' => [__('gender_invalid')]
                    ];
                }
                
                // Country (optional)
                if (HAS_POST('country')){
                    $input['country'] = S_POST('country') ?? '';
                    $rules['country'] = [
                        'rules' => [Validate::optional(Validate::length(2, 2)), Validate::optional(Validate::alpha())],
                        'messages' => [
                            __('Country code must be exactly 2 characters'),
                            __('Country code can only contain letters')
                        ]
                    ];
                }
                
                // About me (optional)
                if (HAS_POST('about_me')){
                    $input['about_me'] = trim(S_POST('about_me') ?? '');
                    $rules['about_me'] = [
                        'rules' => [Validate::optional(Validate::length(10, 300))],
                        'messages' => [__('Personal description must be between %1% and %2% characters', 10, 300)]
                    ];
                }
                
                // Coin (optional)
                if (HAS_POST('coin')){
                    $input['coin'] = (int)(S_POST('coin') ?? 0);
                    $rules['coin'] = [
                        'rules' => [Validate::optional(Validate::numeric())],
                        'messages' => [__('Coin must be a number')]
                    ];
                }
                
                // Package name (optional)
                if (HAS_POST('package_name')){
                    $input['package_name'] = S_POST('package_name') ?? 'membership';
                    $rules['package_name'] = [
                        'rules' => [Validate::optional(Validate::length(1, 50))],
                        'messages' => [__('Package name must be between %1% and %2% characters', 1, 50)]
                    ];
                }
                
                // Package expiration (optional)
                if (HAS_POST('package_exp')){
                    $input['package_exp'] = S_POST('package_exp') ?? null;
                    $rules['package_exp'] = [
                        'rules' => [Validate::optional(Validate::date())],
                        'messages' => [__('Package expiration must be a valid date')]
                    ];
                }
                
                // Display (optional)
                if (HAS_POST('display') && in_array(S_POST('display'), [0, 1])){
                    $input['display'] = S_POST('display');
                    $rules['display'] = [
                        'rules' => [Validate::in([0, 1])],
                        'messages' => [__('Display must be 0 or 1')]
                    ];
                }
                
                // Address fields (optional)
                $addressData = [];
                $addressValidationData = [];
                $addressValidationRules = [];
                
                if (HAS_POST('address1')){
                    $addressData['address1'] = trim(S_POST('address1') ?? '');
                    $addressValidationData['address1'] = $addressData['address1'];
                    $addressValidationRules['address1'] = [
                        'rules' => [Validate::optional(Validate::address(3, 200))],
                        'messages' => [__('Address line 1 must be between %1% and %2% characters and contain only valid address characters', 3, 200)]
                    ];
                }
                if (HAS_POST('address2')){
                    $addressData['address2'] = trim(S_POST('address2') ?? '');
                    $addressValidationData['address2'] = $addressData['address2'];
                    $addressValidationRules['address2'] = [
                        'rules' => [Validate::optional(Validate::address(3, 200))],
                        'messages' => [__('Address line 2 must be between %1% and %2% characters and contain only valid address characters', 3, 200)]
                    ];
                }
                if (HAS_POST('city')){
                    $addressData['city'] = trim(S_POST('city') ?? '');
                    $addressValidationData['city'] = $addressData['city'];
                    $addressValidationRules['city'] = [
                        'rules' => [Validate::optional(Validate::address(2, 100))],
                        'messages' => [__('Min %1% & Max %2% char, and only a-zA-Z0-9 (Spaces, hyphens, dots, and middle dots cannot be doubled consecutively)', 2, 100)]
                    ];
                }
                if (HAS_POST('state')){
                    $addressData['state'] = trim(S_POST('state') ?? '');
                    $addressValidationData['state'] = $addressData['state'];
                    $addressValidationRules['state'] = [
                        'rules' => [Validate::optional(Validate::address(2, 100))],
                        'messages' => [__('Min %1% & Max %2% char, and only a-zA-Z0-9 (Spaces, hyphens, dots, and middle dots cannot be doubled consecutively)', 2, 100)]
                    ];
                }
                if (HAS_POST('zipcode')){
                    $addressData['zipcode'] = trim(S_POST('zipcode') ?? '');
                    $addressValidationData['zipcode'] = $addressData['zipcode'];
                    $addressValidationRules['zipcode'] = [
                        'rules' => [Validate::optional(Validate::alnum('- ')), Validate::optional(Validate::length(3, 20))],
                        'messages' => [
                            __('ZIP code can only contain letters, numbers, and hyphens'),
                            __('ZIP code must be between %1% and %2% characters', 3, 20)
                        ]
                    ];
                }
                
                // Validate main fields
                if (!empty($rules)) {
                    $validator = new Validate();
                    if (!$validator->check($input, $rules)) {
                        $errors = array_merge($errors, $validator->getErrors());
                    }
                }
                
                // Validate address fields
                if (!empty($addressValidationRules)) {
                    $addressValidator = new Validate();
                    if (!$addressValidator->check($addressValidationData, $addressValidationRules)) {
                        $errors = array_merge($errors, $addressValidator->getErrors());
                    }
                }
                
                // Check for duplicate username/email
                if (empty($errors)){
                    if (isset($input['username']) && $this->usersModel->getUserByUsername($input['username'])) {
                        $errors['username'] = [sprintf(__('username_double'), $input['username'])];
                    }
                    if (isset($input['email']) && $this->usersModel->getUserByEmail($input['email'])) {
                        $errors['email'] = [sprintf(__('email_double'), $input['email'])];
                    }
                }
                
                // If no errors, proceed to add user
                if (empty($errors)){
                    // Hash password
                    if (isset($input['password'])){
                        $input['password'] = Security::hashPassword($input['password']);
                    }
                    unset($input['password_repeat']);
                    
                    // Prepare address JSON
                    if (!empty(array_filter($addressData))) {
                        $input['address'] = json_encode($addressData);
                    }
                    
                    // Prepare personal JSON (about_me only for backend admin add)
                    $personalData = [];
                    if (isset($input['about_me'])) {
                        $personalData['about_me'] = $input['about_me'];
                        unset($input['about_me']);
                    }
                    if (!empty($personalData)) {
                        $input['personal'] = json_encode($personalData);
                    }
                    
                    // Set defaults
                    $input['online'] = $input['display'] ? 1 : 0;
                    $input['created_at'] = _DateTime();
                    $input['updated_at'] = _DateTime();
                    
                    return $this->_add($input);
                } else {
                    $this->data('errors', $errors);
                }
            }
        }
 
        $status = ['active', 'inactive', 'banned'];
        $this->data('roles', config_roles());
        $this->data('status', $status);
        $this->data('title', __('title_add_member'));
        $this->data('csrf_token', Session::csrf_token(600)); 
        echo View::make('users_add', $this->data)->render();
    }


    private function _add($input)
    {
        if ($input['status'] !== 'active') {
            $activationNo = strtoupper(random_string(6)); // Create 6-character code
            $activationCode = strtolower(random_string(20)); // Create 20-character code
            $optionalData = [
                'activation_no' => $activationNo,
                'activation_code' => $activationCode,
                'activation_expires' => time() + 86400,
            ];
            $input['optional'] = json_encode($optionalData);
        } else {
            $input['optional'] = null;
        }
        $user_id = $this->usersModel->addUser($input);
       
        if ($user_id) {
            // If status is not 'active' then send activation email
            if ($input['status'] !== 'active') {
                $activationLink = auth_url('activation/' . $user_id . '/' . $activationCode . '/');
                $this->mailer = new Fastmail();
                $this->mailer->send($input['email'], __('active_account'), 'activation', ['username' => $input['username'], 'activation_link' => $activationLink]);
            }
            Session::flash('success', __('User added successfully'));
            \System\Libraries\Events::run('Backend\\UsersAddEvent', $input);
            redirect(admin_url('users/index'));
        } else {
            Session::flash('error', __('Failed to add user'));
            redirect(auth_url('dashboard'));
        }
    }

    public function edit($user_id) {
        View::addCss('users-page-css', 'css/users.css', [], null, 'all', false);
        View::addJs('users-alpine', 'js/alpinejs.3.15.0.min.js', [], null, false, false, false, false);
        View::addJs('users-flatpickr', 'js/flatpickr.v4.6.13.min.js', [], null, false, false, false, false);
        View::addJs('users-lucide-auth', 'js/lucide-auth.min.js', [], null, false, false, false, false);
        
        // Check if the user exists
        $user = $this->usersModel->getUserById($user_id);
        // check session user if not admin, admin can not edit admin orther
        global $me_info;
        //xoi code: chua code check perrmision cua $me_info.

        if (!$user) {
            // User not found, redirect or show an error message
            Session::flash('error', __('User not found'));
            redirect(admin_url('users/index'));
        }
    
        if (!empty($_POST)) {
            $csrf_token = S_POST('csrf_token') ?? '';
            if (!Session::csrf_verify($csrf_token)) {
                $this->data('error', __('csrf_failed'));
            }else{
                // Initialize an empty array for rules
                $rules = [];
                $input = [];
                $errors = [];
        
                // Check each field and add the validation rules accordingly
                if (HAS_POST('username')) {
                    $input['username'] = S_POST('username') ?? '';
                    $rules['username'] = [
                        'rules' => [
                            Validate::alnum('_'),
                            Validate::length(6, 30)
                        ],
                        'messages' => [
                            __('username_invalid'),
                            sprintf(__('username_length'), 6, 30)
                        ]
                    ];
                }
                if (HAS_POST('fullname')) {
                    $input['fullname'] = S_POST('fullname') ?? '';
                    $rules['fullname'] = [
                        'rules' => [
                            Validate::length(6, 50)
                        ],
                        'messages' => [
                            sprintf(__('fullname_length'), 6, 50)
                        ]
                    ];
                }
                if (HAS_POST('email')) {
                    $input['email'] = S_POST('email') ?? '';
                    $rules['email'] = [
                        'rules' => [
                            Validate::email(),
                            Validate::length(6, 150)
                        ],
                        'messages' => [
                            __('email_invalid'),
                            sprintf(__('email_length'), 6, 150)
                        ]
                    ];
                }
                if (HAS_POST('phone')) {
                    $input['phone'] = S_POST('phone') ?? '';
                    $rules['phone'] = [
                        'rules' => [
                            Validate::optional(Validate::phone()),
                            Validate::optional(Validate::length(6, 30))
                        ],
                        'messages' => [
                            __('phone_invalid'),
                            sprintf(__('phone_length'), 6, 30)
                        ]
                    ];
                }
                if (HAS_POST('birthday')) {
                    $input['birthday'] = S_POST('birthday') ?? '';
                    $rules['birthday'] = [
                        'rules' => [
                            Validate::optional(Validate::date()),
                        ],
                        'messages' => [
                            __('birthday_invalid'),
                        ]
                    ];
                }
                if (HAS_POST('gender')) {
                    $input['gender'] = S_POST('gender') ?? '';
                    $rules['gender'] = [
                        'rules' => [
                            Validate::optional(Validate::in(['male', 'female', 'other'])),
                        ],
                        'messages' => [
                            __('gender_invalid'),
                        ]
                    ];
                }
                 if (HAS_POST('about_me')) {
                     $input['about_me'] = S_POST('about_me') ?? '';
                     $rules['about_me'] = [
                         'rules' => [
                             Validate::optional(Validate::length(10, 300)),
                         ],
                         'messages' => [
                             __('Personal description must be between %1% and %2% characters', 10, 300),
                         ]
                     ];
                 }
                 if (HAS_POST('country')) {
                     $input['country'] = S_POST('country') ?? '';
                     $rules['country'] = [
                         'rules' => [
                             Validate::optional(Validate::length(2, 2)),
                             Validate::optional(Validate::alpha()),
                         ],
                         'messages' => [
                             __('Country code must be exactly 2 characters'),
                             __('Country code can only contain letters'),
                         ]
                     ];
                 }
                 
                 // Handle display (direct column, not in JSON)
                 if (HAS_POST('display')){
                     $input['display'] = S_POST('display') ? 1 : 0;
                 }
                 
                 // Address fields (store individually for validation, merge into JSON later)
                 if (HAS_POST('address1')) {
                     $input['address1'] = trim(S_POST('address1') ?? '');
                     $rules['address1'] = [
                         'rules' => [
                             Validate::optional(Validate::address(3, 200)),
                         ],
                         'messages' => [
                             __('Address line 1 must be between %1% and %2% characters and contain only valid address characters', 3, 200),
                         ]
                     ];
                 }
                 if (HAS_POST('address2')) {
                     $input['address2'] = trim(S_POST('address2') ?? '');
                     $rules['address2'] = [
                         'rules' => [
                             Validate::optional(Validate::address(3, 200)),
                         ],
                         'messages' => [
                             __('Address line 2 must be between %1% and %2% characters and contain only valid address characters', 3, 200),
                         ]
                     ];
                 }
                 if (HAS_POST('city')) {
                     $input['city'] = trim(S_POST('city') ?? '');
                     $rules['city'] = [
                         'rules' => [
                             Validate::optional(Validate::address(2, 100)),
                         ],
                         'messages' => [
                             __('Min %1% & Max %2% char, and only a-zA-Z0-9 (Spaces, hyphens, dots, and middle dots cannot be doubled consecutively)', 2, 100),
                         ]
                     ];
                 }
                 if (HAS_POST('state')) {
                     $input['state'] = trim(S_POST('state') ?? '');
                     $rules['state'] = [
                         'rules' => [
                             Validate::optional(Validate::address(2, 100)),
                         ],
                         'messages' => [
                             __('Min %1% & Max %2% char, and only a-zA-Z0-9 (Spaces, hyphens, dots, and middle dots cannot be doubled consecutively)', 2, 100),
                         ]
                     ];
                 }
                 if (HAS_POST('zipcode')) {
                     $input['zipcode'] = trim(S_POST('zipcode') ?? '');
                     $rules['zipcode'] = [
                         'rules' => [
                             Validate::optional(Validate::alnum('- ')),
                             Validate::optional(Validate::length(3, 20)),
                         ],
                         'messages' => [
                             __('ZIP code can only contain letters, numbers, and hyphens'),
                             __('ZIP code must be between %1% and %2% characters', 3, 20),
                         ]
                     ];
                 }
                
                // Handle coin, package fields
                if (HAS_POST('coin')) {
                    $input['coin'] = (int)(S_POST('coin') ?? 0);
                }
                if (HAS_POST('package_name')) {
                    $input['package_name'] = S_POST('package_name') ?? 'membership';
                }
                if (HAS_POST('package_exp')) {
                    $input['package_exp'] = S_POST('package_exp') ?? null;
                }
               // Role (optional)
               if (HAS_POST('role')) {
                   $input['role'] = S_POST('role') ?? '';
                   $rules['role'] = [
                       'rules' => [
                           Validate::notEmpty(),
                           Validate::length(1, 64),
                           Validate::callback(function($value) {
                               // Validate role exists in config_roles()
                               $roles = config_roles();
                               return isset($roles[$value]);
                           }),
                       ],
                       'messages' => [
                           __('role_option'),
                           __('Role must be between 1 and 64 characters'),
                           __('Invalid role or role is inactive'),
                       ]
                   ];
               }
               
               /**
                * Permissions Override
                * 
                * So sánh permissions được chọn với base role permissions
                * → Tạo structure add/remove
                * → Chỉ lưu nếu có thay đổi, ngược lại lưu NULL
                */
               if (HAS_POST('permissions')) {
                   $submittedPermissions = S_POST('permissions') ?? [];
                   $userRole = $input['role'] ?? $user['role'] ?? 'member';
                   
                   // Get base role permissions
                   $basePermissions = user_permissions($userRole, null);
                   
                   // Generate add/remove structure
                   $override = override_permissions($basePermissions, $submittedPermissions);
                   
                   // Only save if có thay đổi
                   if (!empty($override['add']) || !empty($override['remove'])) {
                       $input['permissions'] = json_encode($override);
                   } else {
                       // Không thay đổi → NULL (dùng base role)
                       $input['permissions'] = null;
                   }
               }
               
               // Status (optional)
               if (HAS_POST('status')) {
                   $input['status'] = S_POST('status') ?? '';
                   $rules['status'] = [
                       'rules' => [
                           Validate::notEmpty(),
                       ],
                       'messages' => [
                           __('status_option'),
                       ]
                   ];
               }

               // Password (optional, only if provided)
               if(HAS_POST('password') && S_POST('password') != ''){
                   $input['password'] = S_POST('password') ?? '';
                   $rules['password'] = [
                       'rules' => [
                           Validate::length(6, 60),
                       ],
                       'messages' => [
                           sprintf(__('password_length'), 6, 60),
                       ]
                   ];
               }
               
               // Password repeat (only if password is provided)
               if(HAS_POST('password_repeat') && S_POST('password_repeat') != ''){
                   $input['password_repeat'] = S_POST('password_repeat');
                   $rules['password_repeat'] = [
                       'rules' => [
                           Validate::equals($input['password'])
                       ],
                       'messages' => [
                           sprintf(__('password_repeat_invalid'), $input['password_repeat'])
                       ]
                   ];
               }

               // Validate main fields
               if (!empty($rules)) {
                   $validator = new Validate();
                   if (!$validator->check($input, $rules)) {
                       $errors = array_merge($errors, $validator->getErrors());
                   }
               }
               
               // Check for duplicate username or email (only if no errors yet)
               if (empty($errors)) {
                   if (isset($input['username'])) {
                       $existingUser = $this->usersModel->getUserByUsername($input['username']);
                       if ($existingUser && $existingUser['id'] != $user_id) {
                           $errors['username'] = [sprintf(__('username_double'), $input['username'])];
                       }
                   }
       
                   if (isset($input['email'])) {
                       $existingEmailUser = $this->usersModel->getUserByEmail($input['email']);
                       if ($existingEmailUser && $existingEmailUser['id'] != $user_id) {
                           $errors['email'] = [sprintf(__('email_double'), $input['email'])];
                       }
                   }
               }
               
               // If no errors, process update
               if (empty($errors)) {
                   // Hash password if provided
                   if(!empty($input['password'])){
                       $input['password'] = Security::hashPassword($input['password']);
                   }
                   
                   // Process JSON fields similar to BaseAuthController::_updateDBProfile
                   $userData = ['updated_at' => _DateTime()];
                   
                   // Decode existing JSON data
                   $personalData = _json_decode($user['personal'] ?? '[]');
                   $addressData = _json_decode($user['address'] ?? '[]');
                   
                   // Update simple fields (direct columns)
                   $simpleFields = ['username', 'fullname', 'email', 'phone', 'password', 'birthday', 'gender', 
                                    'country', 'role', 'permissions', 'status', 'display', 'coin', 'package_name', 'package_exp'];
                   foreach ($simpleFields as $field) {
                       if (isset($input[$field]) && $input[$field] !== '') {
                           $userData[$field] = $input[$field];
                       }
                   }
                   
                   // Update address JSON
                   if (isset($input['address1'])) $addressData['address1'] = $input['address1'];
                   if (isset($input['address2'])) $addressData['address2'] = $input['address2'];
                   if (isset($input['city'])) $addressData['city'] = $input['city'];
                   if (isset($input['state'])) $addressData['state'] = $input['state'];
                   if (isset($input['zipcode'])) $addressData['zipcode'] = $input['zipcode'];
                   
                   // Update personal JSON - about_me
                   if (isset($input['about_me'])) {
                       $personalData['about_me'] = $input['about_me'];
                   }
                   
                   // Encode JSON data back
                   if (!empty($addressData)) {
                       $userData['address'] = json_encode($addressData);
                   }
                   if (!empty($personalData)) {
                       $userData['personal'] = json_encode($personalData);
                   }
                   
                   // Update user in database
                   $this->_edit($user_id, $userData);
   
                   // Set success message and retrieve updated user data
                   $this->data('success', __('User updated successfully'));
                   $user = $this->usersModel->getUserById($user_id); // Retrieve updated user
               } else {
                   $this->data('errors', $errors);
                   $this->data('error', __('Please fix the validation errors'));
               }
            }
        }
    
        // Preload roles and status for the form
        $status = ['active', 'inactive', 'banned'];

        $this->data('roles', config_roles());
        $this->data('status', $status);
        $this->data('user', $user); // Pass current user data to the view
        $this->data('title', __('title_edit_member'));
        $this->data('csrf_token', Session::csrf_token(600));
        echo View::make('users_add', $this->data)->render();
    }
    
    private function _edit($user_id, $userData) {
        // Get current user data
        $user = $this->usersModel->getUserById($user_id);
        if (!$user) {
            Session::flash('error', __('User not found'));
            redirect(admin_url('users/index'));
            return;
        }
        
        // Handle optional JSON for activation (if status changed)
        if (isset($userData['status'])) {
            $optionalData = _json_decode($user['optional'] ?? '[]');
            
            if ($userData['status'] !== 'active') {
                $activationNo = strtoupper(random_string(6));
                $activationCode = strtolower(random_string(20));
                $optionalData['activation_no'] = $activationNo;
                $optionalData['activation_code'] = $activationCode;
                $optionalData['activation_expires'] = time() + 86400;
                $userData['optional'] = json_encode($optionalData);
            } elseif ($userData['status'] === 'active') {
                // Clear activation data if status is active
                unset($optionalData['activation_no']);
                unset($optionalData['activation_code']);
                unset($optionalData['activation_expires']);
                $userData['optional'] = !empty($optionalData) ? json_encode($optionalData) : null;
            }
        }
        
        // Update user in database
        $result = $this->usersModel->updateUser($user_id, $userData);
        
        if ($result) {
            // Send activation email if needed
            if (isset($userData['status']) && $userData['status'] !== 'active' && isset($userData['email'])) {
                $activationLink = auth_url('activation/' . $user_id . '/' . $activationCode . '/');
                $this->mailer = new Fastmail();
                $this->mailer->send($userData['email'], __('active_account'), 'activation', [
                    'username' => $userData['username'] ?? $user['username'], 
                    'activation_link' => $activationLink
                ]);
            }
            
            Session::flash('success', __('User updated successfully'));
            \System\Libraries\Events::run('Backend\\UsersEditEvent', $userData);
            redirect(admin_url('users/index'));
        } else {
            $this->data('error', __('Failed to update user'));
        }
    }

    public function update_status() {
        $user_id = S_POST('user_id') ?? '';
        $status = S_POST('status') ?? '';
        $user = $this->usersModel->getUserById($user_id);
        if (empty($user)) {
            return $this->error(__('User not found'), [], 404);
        }
        if ($status == 'active') {
            $this->usersModel->updateUser($user_id, ['status' => 'active']);
        } elseif($status == 'inactive') {
            $this->usersModel->updateUser($user_id, ['status' => 'inactive']);
        } elseif($status == 'banned') {
            $this->usersModel->updateUser($user_id, ['status' => 'banned']);
        } else {
            return $this->error(__('status_option'), [], 400);
        }
        return $this->success([], __('User status updated successfully'), 200);
    }

    public function changestatus($id)
    {
        $user = $this->usersModel->getUserById($id);

        if (!$user) {
            Session::flash('error', __('User not found'));
            redirect(admin_url('users'));
        }

        $status = $user['status'] == 'active' ? 'inactive' : 'active';
        $data = [
            'status' => $status
        ];
        $result = $this->usersModel->updateUser($id, $data);

        if (!$result) {
            Session::flash('error', __('Failed to update user status'));
        } else {
            Session::flash('success', __('User status updated successfully'));
        }
        redirect(admin_url('users'));
    }

    // Xóa User
    public function delete($user_id = null) {
        if(!empty($user_id)) {
            $this->_delete($user_id);
        } elseif(HAS_POST('ids')) {
            $ids = S_POST('ids');
            $ids = json_decode($ids, true);
            foreach($ids as $id) {
                $this->_delete($id);
            }
            $this->success([], __('Users deleted successfully'));
        } else {
            $this->error(__('No users selected for deletion'));
        }
        redirect(admin_url('users/index'));
    }

    // Đoạn này dựng tạm để xử lý cho Event
    public function _delete($user_id) {
        if ($this->usersModel->deleteUser($user_id)){
            \System\Libraries\Events::run('Backend\\UsersDeleteEvent', $user_id);
            return true;
        }
        return false;
    }
    
}