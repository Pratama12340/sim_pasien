<?php
/**
 * login.php - Halaman Login RBAC (/api/auth/login & /api/auth/login-pasien)
 * serta Registrasi Akun Pasien (/api/auth/register-pasien)
 */
require_once __DIR__ . '/api_client.php';

if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_set_api_url'])) {
    set_api_base_url($_POST['api_base_url'] ?? '');
    $_SESSION['login_success'] = 'URL Backend API berhasil diatur ke: ' . get_api_base_url();
    header('Location: login.php');
    exit;
}

$error   = $_SESSION['login_error'] ?? '';
$success = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_success']);
$apiUrl  = get_api_base_url();

// Cek /api/health
$health = api_request('GET', '/health');
$apiOnline = !empty($health['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registrasi — SIM-RS Medika (Server Utama A)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4 sm:p-6" x-data="{ tab: 'login', loginType: 'auto' }">

    <div class="max-w-5xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid lg:grid-cols-12 border border-slate-200">
        
        <!-- LEFT PANEL: BRANDING & QUICK DEMO ACCOUNTS -->
        <div class="lg:col-span-6 bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 text-white p-8 sm:p-10 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-8">
                    <a href="index.php" class="inline-flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-slate-950 font-bold shadow-lg">
                            <i class="fa-solid fa-hospital-user text-xl"></i>
                        </div>
                        <div>
                            <span class="font-heading font-extrabold text-lg block leading-none">SIM-RS Medika</span>
                            <span class="text-xs text-teal-300">Server Utama (A) · RBAC & Web Service</span>
                        </div>
                    </a>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $apiOnline ? 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border-rose-400/30' ?>">
                        <span class="w-2 h-2 rounded-full <?= $apiOnline ? 'bg-emerald-400 animate-ping' : 'bg-rose-400' ?>"></span>
                        <?= $apiOnline ? 'API Connected' : 'API Offline' ?>
                    </span>
                </div>

                <h1 class="font-heading font-extrabold text-2xl sm:text-3xl leading-tight mb-3">
                    Autentikasi JWT & RBAC 6 Role Pengguna
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed mb-5">
                    Terhubung langsung ke Backend API (<code>/api/auth/login</code> & <code>/api/auth/login-pasien</code>). Klik salah satu dari 6 akun role di bawah untuk mengisi otomatis:
                </p>

                <!-- Quick Fill 6 Role Accounts -->
                <div class="space-y-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-teal-300">Pilih Cepat 6 Role Pengguna (Password: admin123):</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button type="button" @click="tab='login'; loginType='auto'" onclick="fillAccount('superadmin','admin123')" class="p-2.5 rounded-xl bg-white/10 hover:bg-teal-500/25 border border-white/15 text-left transition">
                            <span class="text-[11px] font-bold text-rose-300 block">1. super_admin</span>
                            <span class="text-xs text-white font-mono">superadmin</span>
                        </button>
                        <button type="button" @click="tab='login'; loginType='auto'" onclick="fillAccount('admin.klinik','admin123')" class="p-2.5 rounded-xl bg-white/10 hover:bg-teal-500/25 border border-white/15 text-left transition">
                            <span class="text-[11px] font-bold text-teal-300 block">2. admin</span>
                            <span class="text-xs text-white font-mono">admin.klinik</span>
                        </button>
                        <button type="button" @click="tab='login'; loginType='auto'" onclick="fillAccount('loket.dina','admin123')" class="p-2.5 rounded-xl bg-white/10 hover:bg-teal-500/25 border border-white/15 text-left transition">
                            <span class="text-[11px] font-bold text-amber-300 block">3. pendaftaran</span>
                            <span class="text-xs text-white font-mono">loket.dina</span>
                        </button>
                        <button type="button" @click="tab='login'; loginType='auto'" onclick="fillAccount('dr.hendra','admin123')" class="p-2.5 rounded-xl bg-white/10 hover:bg-teal-500/25 border border-white/15 text-left transition">
                            <span class="text-[11px] font-bold text-sky-300 block">4. dokter</span>
                            <span class="text-xs text-white font-mono">dr.hendra</span>
                        </button>
                        <button type="button" @click="tab='login'; loginType='auto'" onclick="fillAccount('kasir.fajar','admin123')" class="p-2.5 rounded-xl bg-white/10 hover:bg-teal-500/25 border border-white/15 text-left transition">
                            <span class="text-[11px] font-bold text-purple-300 block">5. kasir</span>
                            <span class="text-xs text-white font-mono">kasir.fajar</span>
                        </button>
                        <button type="button" @click="tab='login'; loginType='pasien'" onclick="fillAccount('pasien','admin123')" class="p-2.5 rounded-xl bg-white/10 hover:bg-teal-500/25 border border-white/15 text-left transition">
                            <span class="text-[11px] font-bold text-emerald-300 block">6. pasien</span>
                            <span class="text-xs text-white font-mono">pasien</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Konfigurasi Cepat URL API Server B -->
            <div class="mt-6 pt-5 border-t border-white/15">
                <form method="post" class="space-y-2">
                    <input type="hidden" name="action_set_api_url" value="1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        <i class="fa-solid fa-plug-circle-bolt text-teal-400 mr-1"></i> Endpoint Backend API (Server B):
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="api_base_url" value="<?= htmlspecialchars($apiUrl) ?>"
                               class="flex-1 px-3 py-2 rounded-xl bg-slate-950/80 border border-white/20 text-xs font-mono text-teal-300 outline-none">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs transition">
                            Simpan URL
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT PANEL: LOGIN & REGISTER TABS -->
        <div class="lg:col-span-6 p-8 sm:p-10 flex flex-col justify-center">
            
            <!-- Tab Switcher -->
            <div class="flex rounded-xl bg-slate-100 p-1 mb-6">
                <button type="button" @click="tab = 'login'"
                        :class="tab === 'login' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 py-2 rounded-lg text-xs font-bold transition">
                    <i class="fa-solid fa-right-to-bracket mr-1"></i> Login Sistem
                </button>
                <button type="button" @click="tab = 'register'"
                        :class="tab === 'register' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 py-2 rounded-lg text-xs font-bold transition">
                    <i class="fa-solid fa-user-plus mr-1"></i> Registrasi Pasien Baru
                </button>
            </div>

            <?php if ($error): ?>
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm font-medium flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm font-medium flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <!-- TAB 1: FORM LOGIN -->
            <div x-show="tab === 'login'">
                <div class="mb-5">
                    <h2 class="font-heading font-extrabold text-2xl text-slate-900">Masuk ke Sistem</h2>
                    <p class="text-xs text-slate-500 mt-1">Mendukung login Petugas (<code>/api/auth/login</code>) maupun Pasien (<code>/api/auth/login-pasien</code>).</p>
                </div>

                <form action="auth.php" method="post" class="space-y-4">
                    <input type="hidden" name="form_action" value="login">
                    <input type="hidden" name="login_type" :value="loginType">

                    <div>
                        <label for="username" class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Username</label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="username" name="username" required autofocus placeholder="Masukkan username"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="password" name="password" required placeholder="Masukkan password"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-sm shadow-lg shadow-teal-600/25 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk Sekarang</span>
                    </button>
                </form>
            </div>

            <!-- TAB 2: FORM REGISTRASI PASIEN MANDIRI (/api/auth/register-pasien) -->
            <div x-cloak x-show="tab === 'register'">
                <div class="mb-4">
                    <h2 class="font-heading font-extrabold text-xl text-slate-900">Registrasi Akun Pasien</h2>
                    <p class="text-xs text-slate-500">Endpoint: <code>POST /api/auth/register-pasien</code></p>
                </div>

                <form action="auth.php" method="post" class="space-y-3">
                    <input type="hidden" name="form_action" value="register_pasien">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">NIK (16 Digit)</label>
                            <input type="text" name="nik" required pattern="[0-9]{16}" maxlength="16" placeholder="177101..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" required placeholder="Nama sesuai KTP" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">L/P</label>
                            <select name="jenis_kelamin" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Tgl Lahir</label>
                            <input type="date" name="tanggal_lahir" value="2000-01-01" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">No. HP</label>
                            <input type="text" name="no_hp" placeholder="0812..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Alamat Lengkap</label>
                        <input type="text" name="alamat" required placeholder="Jl. ..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-1 border-t border-slate-100">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Username Login</label>
                            <input type="text" name="username" required placeholder="username" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Password</label>
                            <input type="password" name="password" required placeholder="Min 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Ulangi Password</label>
                            <input type="password" name="konfirmasi_password" required placeholder="Konfirmasi" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                        Daftar Akun Pasien Baru
                    </button>
                </form>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <a href="index.php" class="font-semibold text-teal-600 hover:text-teal-700">← Kembali ke Landing Page</a>
                <span>Target: <code><?= htmlspecialchars($apiUrl) ?></code></span>
            </div>
        </div>
    </div>

    <script>
        function fillAccount(u, p) {
            document.getElementById('username').value = u;
            document.getElementById('password').value = p;
        }
    </script>
</body>
</html>
