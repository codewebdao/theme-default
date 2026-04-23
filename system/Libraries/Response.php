<?php

namespace System\Libraries;

/**
 * Response - Production-Ready Response Handler
 * 
 * Unified response system for API and Web
 * Compliant with REST API standards and JSON:API spec
 * 
 * Features:
 * - Consistent format across all endpoints
 * - HTTP status code support
 * - Error code support
 * - JSON output with proper headers
 * - Pagination support
 * - Metadata support
 * 
 * Standard Format:
 * {
 *   "success": true/false,
 *   "message": "Human-readable message",
 *   "data": {...},              // On success
 *   "errors": {...},            // On error
 *   "error_code": "CODE",       // Optional error code
 *   "timestamp": 1703001234,    // Unix timestamp
 *   "datetime": "2024-12-16 10:30:00"
 * }
 * 
 * @package System\Libraries
 * @version 3.0.0
 */
class Response
{
    /**
     * Create success response array
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default: 200)
     * @return array
     */
    public static function success($data = [], $message = 'Success', $statusCode = 200)
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'status_code' => $statusCode
        ];
    }
    
    /**
     * Create error response array
     * 
     * @param string $message Error message
     * @param mixed $errors Error details (array or string)
     * @param string|null $errorCode Machine-readable error code
     * @param int $statusCode HTTP status code (default: 400)
     * @return array
     */
    public static function error($message = 'An error occurred', $errors = [], $errorCode = null, $statusCode = 400)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'status_code' => $statusCode
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }
        
        return $response;
    }

    /**
     * Send JSON response and exit
     * 
     * @param array $response Response array
     * @param int|null $statusCode HTTP status code (overrides response status_code)
     * @return void
     */
    public static function json($response, $statusCode = null)
    {
        // Use status code from parameter or response array
        $code = $statusCode ?? $response['status_code'] ?? 200;
        
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        // Remove status_code from response body (it's in HTTP header)
        unset($response['status_code']);
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send success JSON and exit
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function sendSuccess($data = [], $message = 'Success', $statusCode = 200)
    {
        self::json(self::success($data, $message, $statusCode), $statusCode);
    }

    /**
     * Send error JSON and exit
     * 
     * @param string $message Error message
     * @param mixed $errors Error details
     * @param string|null $errorCode Error code
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function sendError($message, $errors = [], $errorCode = null, $statusCode = 400)
    {
        self::json(self::error($message, $errors, $errorCode, $statusCode), $statusCode);
    }
    
    /**
     * Trả về data response (không có message)
     * 
     * @param mixed $data Dữ liệu trả về
     * @return array
     */
    public static function data($data)
    {
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    /**
     * Trả về validation error response
     * 
     * @param array $validationErrors Mảng validation errors
     * @param string $message Thông báo chính
     * @return array
     */
    public static function validationError($validationErrors = [], $message = 'Validation failed')
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $validationErrors
        ];
    }
    
    /**
     * Trả về not found response
     * 
     * @param string $resource Tên resource không tìm thấy
     * @return array
     */
    public static function notFound($resource = 'Resource')
    {
        return [
            'success' => false,
            'message' => "{$resource} not found",
            'errors' => []
        ];
    }
    
    /**
     * Create unauthorized (401) response
     * 
     * @param string $message Error message
     * @param string|null $errorCode Error code
     * @return array
     */
    public static function unauthorized($message = 'Unauthorized', $errorCode = 'UNAUTHORIZED')
    {
        return self::error($message, [], $errorCode, 401);
    }
    
    /**
     * Create forbidden (403) response
     * 
     * @param string $message Error message
     * @param string|null $errorCode Error code
     * @return array
     */
    public static function forbidden($message = 'Forbidden', $errorCode = 'FORBIDDEN')
    {
        return self::error($message, [], $errorCode, 403);
    }

    /**
     * Send unauthorized JSON and exit
     * 
     * @param string $message Error message
     * @param string|null $errorCode Error code
     * @return void
     */
    public static function sendUnauthorized($message = 'Unauthorized', $errorCode = 'UNAUTHORIZED')
    {
        self::json(self::unauthorized($message, $errorCode), 401);
    }

    /**
     * Send forbidden JSON and exit
     * 
     * @param string $message Error message
     * @param string|null $errorCode Error code
     * @return void
     */
    public static function sendForbidden($message = 'Forbidden', $errorCode = 'FORBIDDEN')
    {
        self::json(self::forbidden($message, $errorCode), 403);
    }
    
    /**
     * Trả về server error response
     * 
     * @param string $message Thông báo lỗi
     * @param mixed $debugInfo Debug info (chỉ hiện ở dev mode)
     * @return array
     */
    public static function serverError($message = 'Internal server error', $debugInfo = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => []
        ];
        
        // Chỉ thêm debug info ở development mode
        if ($debugInfo !== null && APP_DEBUGBAR) {
            $response['debug'] = $debugInfo;
        }
        
        return $response;
    }
    
    /**
     * Wrap response với metadata
     * 
     * @param array $response Response cần wrap
     * @param array $metadata Metadata thêm vào
     * @return array
     */
    public static function withMeta($response, $metadata = [])
    {
        return array_merge($response, ['meta' => $metadata]);
    }
    
    /**
     * Tạo paginated response
     * 
     * @param array $items Danh sách items
     * @param int $page Trang hiện tại
     * @param int $limit Số items per page
     * @param int $total Tổng số items
     * @param string $message Thông báo
     * @return array
     */
    public static function paginated($items, $page, $limit, $total, $message = 'Success')
    {
        $totalPages = ceil($total / $limit);
        
        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]
        ];
    }
    
    /**
     * Check nếu response là success
     * 
     * @param array $response Response cần check
     * @return bool
     */
    public static function isSuccess($response)
    {
        return isset($response['success']) && $response['success'] === true;
    }
    
    /**
     * Check nếu response là error
     * 
     * @param array $response Response cần check
     * @return bool
     */
    public static function isError($response)
    {
        return isset($response['success']) && $response['success'] === false;
    }
    
    /**
     * Lấy data từ response
     * 
     * @param array $response Response
     * @param mixed $default Giá trị default nếu không có data
     * @return mixed
     */
    public static function getData($response, $default = null)
    {
        return $response['data'] ?? $default;
    }
    
    /**
     * Lấy error message từ response
     * 
     * @param array $response Response
     * @param string $default Message default
     * @return string
     */
    public static function getMessage($response, $default = '')
    {
        return $response['message'] ?? $default;
    }
    
    /**
     * Lấy errors từ response
     * 
     * @param array $response Response
     * @return array
     */
    public static function getErrors($response)
    {
        return $response['errors'] ?? [];
    }
}
