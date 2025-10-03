<?php
require_once __DIR__ . '/../helpers/utils.php';

abstract class BaseController {
    protected function render($template, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../templates/{$template}.php";
        return ob_get_clean();
    }
    
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        redirectTo($url);
    }
    
    protected function downloadFile($content, $filename, $contentType = 'application/msword') {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header("Content-Type: {$contentType}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        
        echo $content;
        exit;
    }
}