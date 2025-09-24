<?php
// Router sederhana untuk MVC pattern
require_once '../backend/config/database.php';
require_once '../backend/helpers/utils.php';

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

switch ($path) {
    case '/':
    case '/index.php':
        include 'dashboard.php';
        break;
    case '/surat-tugas':
        include 'surat_tugas.php';
        break;
    case '/surat-undangan':
        include 'surat_undangan.php';
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}