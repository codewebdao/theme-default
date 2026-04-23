<?php


if (!function_exists('current_user')) {
    function current_user()
    {
        global $me_info;
        if (!empty($me_info)) {
            return $me_info;
        }
        $user_id = current_user_id();
        if (empty($user_id)) {
            return null;
        }
        $usersModel = new  \App\Models\UsersModel();
        $user = $usersModel->getUserById($user_id);
        if (!empty($user)) {
            $me_info = $user;
            return $user;
        }
        return null;
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id()
    {
        if ( \System\Libraries\Session::has('user_id') ) {
            return \System\Libraries\Session::get('user_id');
        }
        return null;
    }
}

if (!function_exists('current_user_role')) {
    function current_user_role()
    {
        if ( \System\Libraries\Session::has('role') ) {
            return \System\Libraries\Session::get('role');
        }
        return null;
    }
}

if (!function_exists('current_user_permissions')) {
    function current_user_permissions()
    {
        $user = current_user();
        if (empty($user)) {
            return [];
        }
        
        $userRole = current_user_role() ?? 'member';
        return user_permissions($userRole, $user['permissions'] ?? null);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if current user has permission for controller and action
     * 
     * @param string $controller Controller name (e.g., 'Backend\Files', 'Plugins\Ecommerce\Controllers\Backend\ProductsController')
     * @param string $action Action name (e.g., 'index', 'add', 'edit', 'delete')
     * @return bool
     */
    function has_permission($controller, $action)
    {
        $user = current_user();
        if (empty($user)) {
            return false;
        }

        $userPermissions = current_user_permissions();
        if (empty($userPermissions)) {
            return false;
        }

        // Check if permission exists for controller and action
        foreach ($userPermissions as $account_controller => $account_actions) {
            // Normalize controller name for comparison
            $normalizedAccountController = $account_controller;
            
            // Handle Plugins namespace
            if (strpos($account_controller, 'Plugins') !== false) {
                $normalizedAccountController = str_replace('Plugins\\', '', $account_controller);
            } else {
                // Add Controller suffix if not present
                $normalizedAccountController = '\\' . $account_controller . 'Controller';
            }
            
            // Check if controller matches (using strpos for partial match)
            if (strpos($controller, $normalizedAccountController) !== false) {
                // Check if action is in the allowed actions array
                if (is_array($account_actions) && in_array($action, $account_actions)) {
                    return true;
                }
            }
        }

        return false;
    }
}

