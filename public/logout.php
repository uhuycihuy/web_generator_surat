<?php
require_once __DIR__ . '/../backend/controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();