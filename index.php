<?php
session_start();

require_once __DIR__ . '/backend/helpers/utils.php';

define('PUBLIC_PATH', __DIR__ . '/public');

$route = currentRoutePath();

// Halaman utama: arahkan sesuai status login
if ($route === '' || $route === false) {
    if (!isset($_SESSION['user'])) {
        require PUBLIC_PATH . '/login.php';
        exit;
    }

    $role = $_SESSION['user']['role'] ?? 'user';
    if ($role === 'admin') {
        redirectTo('daftar_pegawai');
    }

    redirectTo('generator_surat');
}

$routes = [
    'index.php'        => function () { redirectTo(''); },
    'login'            => PUBLIC_PATH . '/login.php',
    'login.php'        => function () { redirectTo('login'); },
    'logout'           => PUBLIC_PATH . '/logout.php',
    'logout.php'       => function () { redirectTo('logout'); },
    'generator_surat'  => PUBLIC_PATH . '/generator_surat.php',
    'generator_surat.php' => function () { redirectTo('generator_surat'); },
    'generate_surat'   => function () { redirectTo('generator_surat'); },
    'daftar_pegawai'   => PUBLIC_PATH . '/daftar_pegawai.php',
    'daftar_pegawai.php' => function () { redirectTo('daftar_pegawai'); },
    'kelola_user'      => PUBLIC_PATH . '/kelola_user.php',
    'kelola_user.php'  => function () { redirectTo('kelola_user'); },
    'surat_tugas'      => PUBLIC_PATH . '/generator_surat.php',
    'surat_undangan'   => PUBLIC_PATH . '/generator_surat.php',
];

if (isset($routes[$route])) {
    $target = $routes[$route];

    if (is_callable($target)) {
        $target();
        exit;
    }

    if (file_exists($target)) {
        require $target;
        exit;
    }
}

http_response_code(404);
require PUBLIC_PATH . '/not_found.php';
exit;
?>