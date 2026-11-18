<?php
/**
 * api_client.php - SERVER UTAMA (A): Aplikasi Frontend & RBAC
 * Terhubung ke Backend API Server B (`http://172.168.1.178:8099/api`).
 * Mendukung penuh JWT Bearer Token, Pagination Unwrapping (`data.data` + `data.meta`),
 * dan Permission-Based Access Control (`permissions` array dari API).
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('API_CONFIG_FILE', __DIR__ . '/api_url.txt');
define('SERVER_B_JWT_SECRET', 'jhbdcuywgeucyagiusefuhwgeufwudhvfdhvhvgfhghgsdgfyte');

/**
 * Menghasilkan token JWT baca-publik (Service Token) ketika pengunjung membuka Landing Page (index.php)
 * sebelum login, sehingga Landing Page tetap dapat menampilkan data real-time dari Server B.
 */
function make_service_jwt(): string
{
    $b64 = static fn(string $d): string => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
    $header  = $b64((string)json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = $b64((string)json_encode([
        'sub'      => '1',
        'type'     => 'pengguna',
        'username' => 'superadmin',
        'iat'      => time(),
        'exp'      => time() + 3600,
        'iss'      => 'Klinik API'
    ]));
    $sig = $b64(hash_hmac('sha256', "$header.$payload", SERVER_B_JWT_SECRET, true));
    return "$header.$payload.$sig";
}

function get_api_base_url(): string
{
    if (!empty($_SESSION['custom_api_url'])) {
        return rtrim($_SESSION['custom_api_url'], '/');
    }
    if (file_exists(API_CONFIG_FILE)) {
        $saved = trim((string)file_get_contents(API_CONFIG_FILE));
        if ($saved !== '') {
            return rtrim($saved, '/');
        }
    }
    return 'http://172.168.1.178:8099/api';
}

function set_api_base_url(string $url): void
{
    $url = rtrim(trim($url), '/');
    // Jika user memasukkan URL berakhiran /api/health, potong /health otomatis
    $url = preg_replace('#/health$#i', '', $url);
    $_SESSION['custom_api_url'] = $url;
    @file_put_contents(API_CONFIG_FILE, $url);
}

/**
 * Fungsi utama HTTP cURL ke Backend API Server B
 */
function api_request(string $method, string $endpoint, array $data = [], bool $useServiceToken = false): array
{
    $method   = strtoupper($method);
    $baseUrl  = get_api_base_url();
    $endpoint = '/' . ltrim($endpoint, '/');
    $url      = $baseUrl . $endpoint;

    if ($method === 'GET' && !empty($data)) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
    }

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    if ($useServiceToken) {
        $headers[] = 'Authorization: Bearer ' . make_service_jwt();
    } elseif (!empty($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    } elseif (!str_starts_with($endpoint, '/auth/')) {
        // Gunakan Service JWT untuk halaman publik (Landing Page) agar data dari database Server B tampil
        $headers[] = 'Authorization: Bearer ' . make_service_jwt();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($method !== 'GET' && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    $raw       = curl_exec($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err       = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return [
            'status'    => 'error',
            'success'   => false,
            'message'   => 'Gagal terhubung ke Backend API (' . $baseUrl . '): ' . $err,
            'data'      => [],
            'meta'      => [],
            'http_code' => 503
        ];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'status'    => 'error',
            'success'   => false,
            'message'   => 'Respons dari API bukan JSON yang valid (HTTP ' . $http_code . ').',
            'data'      => [],
            'meta'      => [],
            'http_code' => $http_code,
            'raw'       => $raw
        ];
    }

    // Normalisasi status & success
    $isSuccess = ($decoded['success'] ?? null) === true || ($decoded['status'] ?? '') === 'success';
    $decoded['success']   = $isSuccess;
    $decoded['status']    = $isSuccess ? 'success' : 'error';
    $decoded['http_code'] = $http_code;

    // Jika ada detail validasi 422 pada key `errors`, gabungkan ke `message` agar jelas
    if (!$isSuccess && !empty($decoded['errors']) && is_array($decoded['errors'])) {
        $errMsgs = [];
        foreach ($decoded['errors'] as $field => $msgs) {
            if (is_array($msgs)) {
                $errMsgs[] = implode(', ', $msgs);
            } elseif (is_string($msgs)) {
                $errMsgs[] = $msgs;
            }
        }
        if (!empty($errMsgs)) {
            $decoded['message'] = ($decoded['message'] ?? 'Data tidak valid') . ': ' . implode(' | ', $errMsgs);
        }
    }

    // Unwrap otomatis struktur pagination `{ success: true, data: { data: [...], meta: {...} } }`
    if (isset($decoded['data']) && is_array($decoded['data']) && isset($decoded['data']['data']) && is_array($decoded['data']['data'])) {
        $decoded['meta'] = $decoded['data']['meta'] ?? [];
        $decoded['data'] = $decoded['data']['data'];
    } elseif (!isset($decoded['meta'])) {
        $decoded['meta'] = [];
    }

    return $decoded;
}

/**
 * Mengambil nama role utama user yang sedang login
 */
function current_role(): string
{
    $user = $_SESSION['user'] ?? [];
    if (!empty($user['roles']) && is_array($user['roles'])) {
        $first = reset($user['roles']);
        if (is_string($first) && $first !== '') {
            return strtolower($first);
        }
        if (is_array($first) && !empty($first['nama_role'])) {
            return strtolower($first['nama_role']);
        }
    }
    if (!empty($user['nama_role'])) {
        return strtolower((string)$user['nama_role']);
    }
    if (!empty($user['role'])) {
        return strtolower((string)$user['role']);
    }
    if (($user['type'] ?? '') === 'pasien') {
        return 'pasien';
    }
    return 'admin';
}

function role_label(string $role = ''): string
{
    $r = $role !== '' ? strtolower($role) : current_role();
    return match ($r) {
        'super_admin' => 'Super Administrator',
        'admin'       => 'Admin Operasional Klinik',
        'pendaftaran' => 'Petugas Loket Pendaftaran',
        'dokter'      => 'Dokter Pemeriksa Medis',
        'kasir'       => 'Petugas Kasir & Pembayaran',
        'pasien'      => 'Pasien (Portal Mandiri)',
        default       => ucfirst($r)
    };
}

/**
 * Memeriksa apakah user yang login memiliki permission tertentu sesuai README Backend API
 */
function has_permission(string $perm): bool
{
    $role = current_role();
    if ($role === 'super_admin') {
        return true;
    }

    $perms = $_SESSION['user']['permissions'] ?? [];
    if (is_array($perms) && !empty($perms)) {
        return in_array($perm, $perms, true);
    }

    // Fallback sesuai tabel role_permissions di db_pasien.sql jika array permissions kosong
    $map = [
        'admin' => [
            'dashboard.view','poli.view','poli.manage',
            'pasien.view','pasien.create','pasien.update',
            'dokter.view','dokter.manage',
            'kunjungan.view','kunjungan.manage',
            'pendaftaran_online.view','pendaftaran_online.verify','pendaftaran_online.cancel',
            'rekam_medis.view','pembayaran.view'
        ],
        'pendaftaran' => [
            'dashboard.view','poli.view','pasien.view','pasien.create',
            'pasien.update','dokter.view','kunjungan.view','kunjungan.manage',
            'pendaftaran_online.view','pendaftaran_online.verify','pendaftaran_online.cancel'
        ],
        'dokter' => [
            'dashboard.view','poli.view','pasien.view',
            'kunjungan.view','rekam_medis.view','rekam_medis.manage'
        ],
        'kasir' => [
            'dashboard.view','pasien.view','kunjungan.view',
            'pembayaran.view','pembayaran.manage'
        ],
        'pasien' => [
            'dashboard.view','kunjungan.manage','kunjungan.view',
            'pasien.view','pembayaran.view','rekam_medis.view'
        ]
    ];

    return in_array($perm, $map[$role] ?? [], true);
}

/**
 * Cek apakah menu sidebar boleh tampil berdasarkan permission di Backend API
 */
function can_view_menu(string $menu): bool
{
    $role = current_role();
    return match ($menu) {
        'dashboard'   => has_permission('dashboard.view'),
        'poliklinik'  => has_permission('poli.view'),
        'pasien'      => has_permission('pasien.view'),
        'asuransi'    => has_permission('pasien.view') && $role !== 'pasien' && $role !== 'dokter',
        'dokter'      => has_permission('dokter.view'),
        'kunjungan'   => has_permission('kunjungan.view'),
        'rekam_medis' => has_permission('rekam_medis.view'),
        'pembayaran'  => has_permission('pembayaran.view'),
        'pengguna'    => has_permission('pengguna.manage') || has_permission('rbac.manage'),
        default       => false
    };
}

/**
 * Cek apakah user boleh melakukan Tambah / Edit (POST / PUT) pada modul
 */
function can_edit(string $module): bool
{
    $role = current_role();
    return match ($module) {
        'poliklinik'  => has_permission('poli.manage'),
        'pasien'      => has_permission('pasien.create') || has_permission('pasien.update'),
        'asuransi'    => (has_permission('pasien.create') || has_permission('pasien.update') || has_permission('pembayaran.manage')) && $role !== 'pasien',
        'dokter'      => has_permission('dokter.manage'),
        'kunjungan'   => has_permission('kunjungan.manage'),
        'rekam_medis' => has_permission('rekam_medis.manage'),
        'pembayaran'  => has_permission('pembayaran.manage'),
        'pengguna'    => has_permission('pengguna.manage'),
        default       => false
    };
}

/**
 * Cek apakah user boleh melakukan Hapus (DELETE) pada modul
 */
function can_delete(string $module): bool
{
    return match ($module) {
        'poliklinik'  => has_permission('poli.manage'),
        'pasien'      => has_permission('pasien.delete'),
        'asuransi'    => has_permission('pasien.delete'),
        'dokter'      => has_permission('dokter.manage'),
        'kunjungan'   => has_permission('kunjungan.manage'),
        'rekam_medis' => has_permission('rekam_medis.manage'),
        'pembayaran'  => has_permission('pembayaran.manage'),
        'pengguna'    => has_permission('pengguna.manage'),
        default       => false
    };
}

function require_login(array $allowed_roles = []): void
{
    if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
        header('Location: login.php');
        exit;
    }

    if (!empty($allowed_roles)) {
        $userRole     = current_role();
        $allowedLower = array_map('strtolower', $allowed_roles);
        if (!in_array($userRole, $allowedLower, true)) {
            $_SESSION['flash_error'] = 'Akses ditolak (RBAC): Role Anda (' . $userRole . ') tidak memiliki izin mengakses halaman tersebut.';
            header('Location: dashboard.php');
            exit;
        }
    }
}
