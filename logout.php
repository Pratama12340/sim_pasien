<?php
require_once __DIR__ . '/api_client.php';
$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
session_start();
$_SESSION['login_success'] = 'Anda telah berhasil keluar dari sistem.';
header('Location: login.php');
exit;
