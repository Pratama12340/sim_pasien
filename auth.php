<?php
/**
 * auth.php - Memproses Login Petugas (/api/auth/login), Login Pasien (/api/auth/login-pasien),
 * dan Registrasi Pasien Mandiri (/api/auth/register-pasien) ke Server B (172.168.1.178:8099)
 */
require_once __DIR__ . '/api_client.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$act = $_POST['form_action'] ?? 'login';

// 1. Jika Registrasi Pasien Baru (/api/auth/register-pasien)
if ($act === 'register_pasien') {
    $payload = [
        'nik'                 => trim($_POST['nik'] ?? ''),
        'nama_lengkap'        => trim($_POST['nama_lengkap'] ?? ''),
        'jenis_kelamin'       => $_POST['jenis_kelamin'] ?? 'L',
        'tempat_lahir'        => trim($_POST['tempat_lahir'] ?? ''),
        'tanggal_lahir'       => trim($_POST['tanggal_lahir'] ?? '') ?: null,
        'golongan_darah'      => $_POST['golongan_darah'] ?? 'O',
        'no_hp'               => trim($_POST['no_hp'] ?? ''),
        'email'               => trim($_POST['email'] ?? ''),
        'alamat'              => trim($_POST['alamat'] ?? ''),
        'username'            => trim($_POST['username'] ?? ''),
        'password'            => $_POST['password'] ?? '',
        'konfirmasi_password' => $_POST['konfirmasi_password'] ?? ''
    ];

    $res = api_request('POST', '/auth/register-pasien', $payload);
    if (!empty($res['success'])) {
        $_SESSION['login_success'] = 'Registrasi akun pasien berhasil! Silakan login dengan username: ' . $payload['username'];
    } else {
        $_SESSION['login_error'] = $res['message'] ?? 'Gagal registrasi pasien.';
    }
    header('Location: login.php');
    exit;
}

// 2. Proses Login (/api/auth/login atau /api/auth/login-pasien)
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$loginType = $_POST['login_type'] ?? 'auto';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: login.php');
    exit;
}

$res = null;
if ($loginType === 'pasien') {
    $res = api_request('POST', '/auth/login-pasien', ['username' => $username, 'password' => $password]);
} else {
    // Coba /auth/login (petugas/staf) terlebih dahulu
    $res = api_request('POST', '/auth/login', ['username' => $username, 'password' => $password]);
    // Jika 401 pada /auth/login, otomatis coba /auth/login-pasien (akun pasien)
    if (empty($res['success']) && ($res['http_code'] ?? 0) === 401) {
        $resPasien = api_request('POST', '/auth/login-pasien', ['username' => $username, 'password' => $password]);
        if (!empty($resPasien['success'])) {
            $res = $resPasien;
        }
    }
    // Fallback ke /login jika menggunakan server simulasi lokal ws_pasien
    if (empty($res['success']) && ($res['http_code'] ?? 0) === 404) {
        $res = api_request('POST', '/login', ['username' => $username, 'password' => $password]);
    }
}

$token = $res['data']['token'] ?? $res['token'] ?? '';
$user  = $res['data']['user'] ?? $res['user'] ?? null;

if (!empty($res['success']) && $token !== '') {
    session_regenerate_id(true);
    $_SESSION['login'] = true;
    $_SESSION['token'] = $token;

    // Panggil GET /auth/me untuk melengkapi profil & daftar permissions dari token JWT
    $me = api_request('GET', '/auth/me');
    if (!empty($me['success']) && is_array($me['data'] ?? null)) {
        $user = array_merge(is_array($user) ? $user : [], $me['data']);
    }

    if (!is_array($user)) {
        $user = ['username' => $username];
    }

    // Normalisasi nama_lengkap & role
    $user['nama_lengkap'] = $user['nama_lengkap'] ?? $user['nama'] ?? $user['username'] ?? $username;
    if (!empty($user['roles']) && is_array($user['roles'])) {
        $firstRole = reset($user['roles']);
        $user['nama_role'] = is_array($firstRole) ? ($firstRole['nama_role'] ?? 'admin') : (string)$firstRole;
    } elseif (empty($user['nama_role'])) {
        $user['nama_role'] = $user['role'] ?? (($user['type'] ?? '') === 'pasien' ? 'pasien' : 'admin');
    }
    $user['role'] = strtolower($user['nama_role']);

    $_SESSION['user'] = $user;
    $_SESSION['flash_success'] = 'Selamat datang, ' . $user['nama_lengkap'] . ' (Role: ' . $user['role'] . ')!';
    header('Location: dashboard.php');
    exit;
}

$_SESSION['login_error'] = $res['message'] ?? 'Username atau password salah.';
header('Location: login.php');
exit;
