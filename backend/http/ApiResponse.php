<?php
/**
 * API Response Formatter
 * 
 * Standardizes all API/AJAX responses with consistent format
 * Provides success, error, redirect, and validation error responses
 */

class ApiResponse {
    
    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return void (outputs JSON and exits)
     */
    public static function success($data = null, $message = 'Success', $code = 200) {
        self::send([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param mixed $errors Additional error details
     * @return void (outputs JSON and exits)
     */
    public static function error($message = 'Error', $code = 400, $errors = null) {
        self::send([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
    
    /**
     * Send validation error response
     * 
     * @param array $errors Validation errors by field
     * @param string $message Error message
     * @return void (outputs JSON and exits)
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::send([
            'status' => 'validation_error',
            'code' => 422,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
    
    /**
     * Send redirect response
     * Useful for AJAX requests that need to redirect
     * 
     * @param string $url Redirect URL
     * @param string $message Redirect message
     * @return void (outputs JSON and exits)
     */
    public static function redirect($url, $message = 'Redirecting...') {
        self::send([
            'status' => 'redirect',
            'code' => 302,
            'message' => $message,
            'url' => $url,
        ], 200);
    }
    
    /**
     * Send unauthorized response
     * 
     * @param string $message Error message
     * @return void (outputs JSON and exits)
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::send([
            'status' => 'error',
            'code' => 401,
            'message' => $message,
        ], 401);
    }
    
    /**
     * Send forbidden response
     * 
     * @param string $message Error message
     * @return void (outputs JSON and exits)
     */
    public static function forbidden($message = 'Forbidden') {
        self::send([
            'status' => 'error',
            'code' => 403,
            'message' => $message,
        ], 403);
    }
    
    /**
     * Send not found response
     * 
     * @param string $message Error message
     * @return void (outputs JSON and exits)
     */
    public static function notFound($message = 'Not found') {
        self::send([
            'status' => 'error',
            'code' => 404,
            'message' => $message,
        ], 404);
    }
    
    /**
     * Send server error response
     * 
     * @param string $message Error message
     * @param mixed $debug Debug info (only in development)
     * @return void (outputs JSON and exits)
     */
    public static function serverError($message = 'Server error', $debug = null) {
        $response = [
            'status' => 'error',
            'code' => 500,
            'message' => $message,
        ];
        
        // Include debug info only in development
        if (EnvLoader::get('APP_ENV') === 'development' && $debug !== null) {
            $response['debug'] = $debug;
        }
        
        self::send($response, 500);
    }
    
    /**
     * Send raw response
     * 
     * @param array $data Response array
     * @param int $httpCode HTTP status code
     * @return void (outputs JSON and exits)
     */
    public static function send($data, $httpCode = 200) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send file response
     * Used for file downloads
     * 
     * @param string $filePath Path to file
     * @param string $fileName Name for download
     * @param string $mimeType MIME type
     * @return void
     */
    public static function file($filePath, $fileName, $mimeType = 'application/octet-stream') {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            self::notFound('File not found');
        }
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }
    
    /**
     * Send HTML response
     * 
     * @param string $html HTML content
     * @param int $httpCode HTTP status code
     * @return void
     */
    public static function html($html, $httpCode = 200) {
        http_response_code($httpCode);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
    
    /**
     * Handle exception response
     * Converts exception to standardized error response
     * 
     * @param Exception $exception Exception to handle
     * @return void
     */
    public static function exception($exception) {
        $code = $exception->getCode() ?: 500;
        
        // Map custom exception codes
        if ($code === 422) {
            self::validationError([], $exception->getMessage());
        } elseif ($code === 401) {
            self::unauthorized($exception->getMessage());
        } elseif ($code === 403) {
            self::forbidden($exception->getMessage());
        } elseif ($code === 404) {
            self::notFound($exception->getMessage());
        } else {
            $debug = null;
            if (EnvLoader::get('APP_ENV') === 'development') {
                $debug = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ];
            }
            
            self::serverError($exception->getMessage(), $debug);
        }
    }
    
    /**
     * Get response format hints for development
     * 
     * @return array Format documentation
     */
    public static function documentation() {
        return [
            'success' => [
                'status' => 'success',
                'code' => 200,
                'message' => 'Operation successful',
                'data' => 'Response payload',
            ],
            'error' => [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error description',
                'errors' => 'Error details (optional)',
            ],
            'validation_error' => [
                'status' => 'validation_error',
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => [
                    'field_name' => 'Error message for field',
                ],
            ],
            'redirect' => [
                'status' => 'redirect',
                'code' => 302,
                'message' => 'Redirect message',
                'url' => 'Target URL',
            ],
        ];
    }
}
?>
