<?php
namespace App\Controllers;

use System\Core\BaseController;
use App\Libraries\Fasttoken;
use System\Libraries\Session;
use System\Libraries\Response;

class ApiController extends BaseController
{
    protected $usersModel;

    public function __construct()
    {
        parent::__construct();
        load_helpers(['string']);
        $this->usersModel = new \App\Models\UsersModel();
        
        // Set JSON content type for API
        header('Content-Type: application/json; charset=utf-8');
    }

    protected function _auth() {
        global $me_info;
        if (!empty($me_info)) {
            return $me_info;
        }
        $access_token = Fasttoken::headerToken();
        //If Client/App send Bearer Token: (Bearer <token>) at Header: Focus validate by Token
        if (!empty($access_token)) {
            $token_data = Fasttoken::checkToken($access_token);
            if (empty($token_data) || !isset($token_data['password_at']) || !isset($token_data['user_id'])) {
                return null;
            }
            $user = array(
                'id' => $token_data['user_id'],
                'user_id' => $token_data['user_id'],
                'role' => $token_data['role'],
                'username' => $token_data['username'],
                'email' => $token_data['email'],
                'password_at' => $token_data['password_at'],
            );
            return $user;
            // $user = $this->usersModel->getUserById($token_data['user_id']);
            // if ($user && !empty($user['password_at']) && $user['password_at'] == $token_data['password_at']) {
            //     return $user;
            // }
            // return null;
        }
        //If Client/App not send Token: Focus validate by Session
        if(Session::has('user_id')) {
            $user_id = (int)Session::get('user_id');
            $me_info = $this->usersModel->getUserById($user_id);
            return $me_info;
        }
        return null;
    }

    /**
     * Send success JSON response using Response library
     * 
     * @param array $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default: 200)
     * @return void
     */
    protected function success($data = [], $message = 'Success', $statusCode = 200)
    {
        Response::sendSuccess($data, $message, $statusCode);
    }

    /**
     * Send error JSON response using Response library
     * 
     * @param string $message Error message
     * @param mixed $errors Error details (can include error_code)
     * @param int $statusCode HTTP status code (default: 400)
     * @return void
     */
    protected function error($message = 'An error occurred', $errors = [], $statusCode = 400)
    {
        // Extract error_code if present in errors array
        $errorCode = null;
        if (is_array($errors) && isset($errors['error_code'])) {
            $errorCode = $errors['error_code'];
            unset($errors['error_code']); // Remove from errors array
        }
        
        Response::sendError($message, $errors, $errorCode, $statusCode);
    }

    /**
     * Return success response array (without sending)
     * Uses Response library for consistent format
     * 
     * @param array $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default: 200)
     * @return array
     */
    protected function get_success($data = [], $message = 'Success', $statusCode = 200)
    {
        return Response::success($data, $message, $statusCode);
    }

    /**
     * Return error response array (without sending)
     * Uses Response library for consistent format
     * 
     * @param string $message Error message
     * @param mixed $errors Error details
     * @param string|null $errorCode Machine-readable error code
     * @param int $statusCode HTTP status code (default: 400)
     * @return array
     */
    protected function get_error($message = 'An error occurred', $errors = [], $errorCode = null, $statusCode = 400)
    {
        return Response::error($message, $errors, $errorCode, $statusCode);
    }
}