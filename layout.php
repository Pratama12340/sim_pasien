<?php
/**
 * layout.php - Template Master dengan Navigasi & Tema Dinamis Berbeda untuk 6 Role RBAC
 */
require_once __DIR__ . '/api_client.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_set_api_url'])) {
    $newUrl = trim($_POST['api_base_url'] ?? '');
    if ($newUrl !== '') {
        set_api_base_url($newUrl);
        $_SESSION['flash_success'] = 'Alamat Web Service Server B berhasil diubah ke: ' . get_api_base_url();
    }
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? 'dashboard.php'));
    exit;
}

function render_header(string $title, string $activeMenu = 'dashboard'): void
{
    $user      = $_SESSION['user'] ?? [];
    $namaUser  = $user['nama_lengkap'] ?? $user['username'] ?? 'Pengguna';
    $roleUser  = current_role();
    $roleTitle = role_label($roleUser);
    $inisial   = strtoupper(substr($namaUser, 0, 2));
    $apiUrl    = get_api_base_url();

    // Warna identitas berbeda untuk tiap Role agar langsung terlihat perbedaannya
    $roleTheme = match ($roleUser) {
        'super_admin' => [
            'badge'   => 'bg-rose-50 text-rose-700 border-rose-200',
            'sidebar' => 'from-rose-600 to-red-600 shadow-rose-900/40',
            'icon_bg' => 'from-rose-500 to-red-600 shadow-rose-500/25',
            'pill'    => 'bg-rose-500/20 text-rose-300 border-rose-500/30'
        ],
        'admin' => [
            'badge'   => 'bg-teal-50 text-teal-700 border-teal-200',
            'sidebar' => 'from-teal-600 to-emerald-600 shadow-teal-900/40',
            'icon_bg' => 'from-teal-400 to-emerald-600 shadow-teal-500/25',
            'pill'    => 'bg-teal-500/20 text-teal-300 border-teal-500/30'
        ],
        'pendaftaran' => [
            'badge'   => 'bg-amber-50 text-amber-700 border-amber-200',
            'sidebar' => 'from-amber-500 to-orange-600 shadow-amber-900/40',
            'icon_bg' => 'from-amber-400 to-orange-500 shadow-amber-500/25',
            'pill'    => 'bg-amber-500/20 text-amber-300 border-amber-500/30'
        ],
        'dokter' => [
            'badge'   => 'bg-sky-50 text-sky-700 border-sky-200',
            'sidebar' => 'from-sky-600 to-blue-600 shadow-sky-900/40',
            'icon_bg' => 'from-sky-400 to-blue-600 shadow-sky-500/25',
            'pill'    => 'bg-sky-500/20 text-sky-300 border-sky-500/30'
        ],
        'kasir' => [
            'badge'   => 'bg-purple-50 text-purple-700 border-purple-200',
            'sidebar' => 'from-purple-600 to-indigo-600 shadow-purple-900/40',
            'icon_bg' => 'from-purple-500 to-indigo-600 shadow-purple-500/25',
            'pill'    => 'bg-purple-500/20 text-purple-300 border-purple-500/30'
        ],
        'pasien' => [
            'badge'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'sidebar' => 'from-emerald-600 to-green-600 shadow-emerald-900/40',
            'icon_bg' => 'from-emerald-400 to-green-600 shadow-emerald-500/25',
            'pill'    => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
        ],
        default => [
            'badge'   => 'bg-slate-100 text-slate-700 border-slate-200',
            'sidebar' => 'from-teal-600 to-emerald-600 shadow-teal-900/40',
            'icon_bg' => 'from-teal-400 to-emerald-600 shadow-teal-500/25',
            'pill'    => 'bg-teal-500/20 text-teal-300 border-teal-500/30'
        ]
    };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — SIM-RS Medika</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        h1, h2, h3, h4, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-slate-800 antialiased flex h-screen overflow-hidden" x-data="{ sidebarOpen: true, apiModalOpen: false }">

    <!-- SIDEBAR NAVIGASI SESUAI ROLE -->
    <aside class="bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 z-30 shrink-0 shadow-xl"
           :class="sidebarOpen ? 'w-64' : 'w-20'">
        
        <!-- Brand Header -->
        <div class="h-20 flex items-center justify-between px-5 border-b border-slate-800/80">
            <a href="dashboard.php" class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $roleTheme['icon_bg'] ?> flex items-center justify-center text-white shadow-lg shrink-0">
                    <i class="fa-solid fa-hospital-user text-lg"></i>
                </div>
                <div class="flex flex-col whitespace-nowrap" x-show="sidebarOpen">
                    <span class="font-heading font-extrabold text-white text-base tracking-tight leading-none">SIM-RS Medika</span>
                    <span class="text-[11px] text-slate-400 font-medium mt-1">Portal: <strong class="text-white"><?= htmlspecialchars($roleUser) ?></strong></span>
                </div>
            </a>
        </div>

        <!-- Role & User Card -->
        <div class="p-4 border-b border-slate-800/80" x-show="sidebarOpen">
            <div class="bg-slate-800/70 rounded-xl p-3 border border-slate-700/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg border flex items-center justify-center font-bold text-sm shrink-0 <?= $roleTheme['pill'] ?>">
                    <?= htmlspecialchars($inisial) ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($namaUser) ?></p>
                    <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-bold uppercase rounded border <?= $roleTheme['pill'] ?>">
                        <?= htmlspecialchars($roleUser) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Links (Berbeda untuk tiap Role) -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">
                <?= $roleUser === 'pasien' ? 'Portal Mandiri Pasien' : 'Menu Role ' . strtoupper($roleUser) ?>
            </div>

            <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $activeMenu === 'dashboard' ? 'bg-gradient-to-r ' . $roleTheme['sidebar'] . ' text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' ?>">
                <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">
                    <?= $roleUser === 'pasien' ? 'Beranda & Kartu Saya' : 'Dashboard ' . ucfirst($roleUser) ?>
                </span>
            </a>

            <?php if (can_view_menu('pasien')): ?>
            <a href="pasien.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $activeMenu === 'pasien' ? 'bg-gradient-to-r ' . $roleTheme['sidebar'] . ' text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' ?>">
                <i class="fa-solid fa-bed-pulse w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">
                    <?php
                    echo match ($roleUser) {
                        'pasien'      => 'Profil & Biodata Saya',
                        'pendaftaran' => 'Registrasi Data Pasien',
                        'dokter'      => 'Daftar Pasien Diperiksa',
                        'kasir'       => 'Data Pasien & Tagihan',
                        default       => 'Data Master Pasien'
                    };
                    ?>
                </span>
            </a>
            <?php endif; ?>

            <?php if (can_view_menu('poliklinik')): ?>
            <a href="poliklinik.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $activeMenu === 'poliklinik' ? 'bg-gradient-to-r ' . $roleTheme['sidebar'] . ' text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' ?>">
                <i class="fa-solid fa-house-medical w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">
                    <?= in_array($roleUser, ['super_admin', 'admin'], true) ? 'Kelola Poliklinik' : 'Info Unit Poliklinik' ?>
                </span>
            </a>
            <?php endif; ?>

            <?php if (can_view_menu('dokter')): ?>
            <a href="dokter.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $activeMenu === 'dokter' ? 'bg-gradient-to-r ' . $roleTheme['sidebar'] . ' text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' ?>">
                <i class="fa-solid fa-user-doctor w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">
                    <?= in_array($roleUser, ['super_admin', 'admin'], true) ? 'Kelola Data Dokter' : 'Jadwal & Direktori Dokter' ?>
                </span>
            </a>
            <?php endif; ?>

            <?php if (can_view_menu('asuransi')): ?>
            <a href="asuransi.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $activeMenu === 'asuransi' ? 'bg-gradient-to-r ' . $roleTheme['sidebar'] . ' text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' ?>">
                <i class="fa-solid fa-shield-heart w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">
                    <?= $roleUser === 'kasir' ? 'Klaim & Mitra Asuransi' : 'Data Mitra Asuransi' ?>
                </span>
            </a>
            <?php endif; ?>

            <?php if (can_view_menu('pengguna')): ?>
            <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">
                Manajemen Akun & RBAC
            </div>

            <a href="pengguna.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $activeMenu === 'pengguna' ? 'bg-gradient-to-r ' . $roleTheme['sidebar'] . ' text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' ?>">
                <i class="fa-solid fa-users-gear w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">
                    <?= $roleUser === 'super_admin' ? 'Kelola Pengguna & RBAC' : 'Daftar Pengguna Staf' ?>
                </span>
            </a>
            <?php endif; ?>

            <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">
                Koneksi SOA
            </div>

            <button type="button" @click="apiModalOpen = true" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/70 hover:text-teal-300 transition-all text-left">
                <i class="fa-solid fa-plug-circle-bolt w-5 text-center text-base text-teal-400"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Konfigurasi IP API</span>
            </button>

            <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/70 hover:text-white transition-all">
                <i class="fa-solid fa-globe w-5 text-center text-base"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Lihat Landing Page</span>
            </a>
        </nav>

        <!-- Bottom API Status -->
        <div class="p-4 border-t border-slate-800/80" x-show="sidebarOpen">
            <div class="bg-slate-950/70 rounded-xl p-3 border border-slate-800 text-xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-slate-400 font-medium">Server B (API):</span>
                    <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Online
                    </span>
                </div>
                <p class="text-[11px] font-mono text-slate-300 truncate" title="<?= htmlspecialchars($apiUrl) ?>">
                    <?= htmlspecialchars($apiUrl) ?>
                </p>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- TOPBAR -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 lg:px-8 flex items-center justify-between shrink-0 z-20 shadow-xs">
            <div class="flex items-center gap-4">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div>
                    <h1 class="font-heading font-bold text-lg sm:text-xl text-slate-900 leading-tight"><?= htmlspecialchars($title) ?></h1>
                    <p class="text-xs text-slate-500 hidden sm:block"><?= htmlspecialchars($roleTitle) ?> · Login sebagai: <strong><?= htmlspecialchars($namaUser) ?></strong></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="apiModalOpen = true" class="hidden md:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-700 border border-slate-200 text-xs font-semibold transition">
                    <i class="fa-solid fa-network-wired text-teal-600"></i>
                    <span>API: <?= htmlspecialchars($apiUrl) ?></span>
                </button>

                <span class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-xl border text-xs font-bold <?= $roleTheme['badge'] ?>">
                    <i class="fa-solid fa-user-shield mr-1.5"></i> <?= htmlspecialchars($roleUser) ?>
                </span>

                <a href="logout.php" onclick="return confirm('Keluar dari sesi aplikasi?');" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-xs font-bold transition">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Ganti Role / Keluar</span>
                </a>
            </div>
        </header>

        <!-- CONTENT SCROLL -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flex items-center justify-between p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                        <span class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
<?php
}

function render_footer(): void
{
    $apiUrl = get_api_base_url();
?>
        </main>

        <!-- MODAL PENGATURAN ALAMAT IP WEB SERVICE SERVER B -->
        <div x-cloak x-show="apiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div @click.away="apiModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i class="fa-solid fa-server text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-slate-900 text-base">Konfigurasi Web Service (Server B)</h3>
                            <p class="text-xs text-slate-500">Hubungkan UI ini ke IP Server B teman Anda saat API selesai</p>
                        </div>
                    </div>
                    <button type="button" @click="apiModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form method="post" class="mt-5 space-y-4">
                    <input type="hidden" name="action_set_api_url" value="1">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Base URL Endpoint API Server B</label>
                        <input type="text" name="api_base_url" required value="<?= htmlspecialchars($apiUrl) ?>"
                               placeholder="Contoh: http://192.168.1.10/ws_pasien/api"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            • Untuk simulasi lokal di laptop ini: <code>http://localhost/ws_pasien/api</code><br>
                            • Untuk terhubung ke laptop Server B teman: ganti <code>localhost</code> dengan IP laptop teman (misal <code>http://192.168.7.101/ws_pasien/api</code>).
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="apiModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20">Simpan & Hubungkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}
