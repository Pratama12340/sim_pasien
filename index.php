<?php
/**
 * index.php - LANDING PAGE SERVER UTAMA (A)
 * Menampilkan informasi Poliklinik, Dokter Spesialis, dan Asuransi Mitra dari Web Service Server B (dbPasien).
 */
require_once __DIR__ . '/api_client.php';

$isLoggedIn = !empty($_SESSION['login']);

// Ambil data live dari Web Service (Server B: /api/dashboard, /api/poli, /api/dokter, /api/asuransi) menggunakan Service Token
$resStat = api_request('GET', '/dashboard', [], true);
if (empty($resStat['success'])) {
    $resStat = api_request('GET', '/statistik', [], true);
}
$stat = $resStat['data'] ?? [];

$resPoli  = api_request('GET', '/poli', [], true);
$listPoli = is_array($resPoli['data'] ?? null) ? $resPoli['data'] : [];
$poliMap  = [];
foreach ($listPoli as $pl) {
    $idP = $pl['id_poli'] ?? $pl['id_poliklinik'] ?? null;
    if ($idP !== null) {
        $poliMap[$idP] = $pl['nama_poli'] ?? $pl['nama_poliklinik'] ?? 'Poliklinik';
    }
}

$resDok  = api_request('GET', '/dokter', [], true);
$listDok = is_array($resDok['data'] ?? null) ? $resDok['data'] : [];

$resAsu  = api_request('GET', '/asuransi', [], true);
$listAsu = is_array($resAsu['data'] ?? null) ? $resAsu['data'] : [];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-RS Medika UMB — Sistem Informasi Manajemen Pasien & Poliklinik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/25">
                    <i class="fa-solid fa-hospital-user text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-extrabold text-xl text-slate-900 tracking-tight">SIM-RS Medika</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-teal-50 text-teal-700 border border-teal-200">SOA 2 Server</span>
                    </div>
                    <p class="text-xs text-slate-500">Sistem Informasi Pasien, Poliklinik, Dokter & Asuransi</p>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-600">
                <a href="#beranda" class="hover:text-teal-600 transition">Beranda</a>
                <a href="#poliklinik" class="hover:text-teal-600 transition">Poliklinik</a>
                <a href="#dokter" class="hover:text-teal-600 transition">Dokter Spesialis</a>
                <a href="#asuransi" class="hover:text-teal-600 transition">Mitra Asuransi</a>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold shadow-md shadow-teal-600/20 transition">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Dashboard SIMRS</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-bold shadow-lg shadow-teal-600/25 transition">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Login Petugas / Dokter</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section id="beranda" class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 text-white py-20 lg:py-28">
        <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(#14b8a6 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-500/15 border border-teal-400/30 text-teal-300 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Terintegrasi Web Service (REST API) — dbPasien
                </div>
                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl leading-[1.15] tracking-tight">
                    Pelayanan Rekam Medis & Pasien <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 to-emerald-300">Cepat, Akurat & Terpadu</span>
                </h1>
                <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl">
                    Platform manajemen pelayanan rumah sakit berbasis <strong>Service-Oriented Architecture (SOA)</strong> yang memisahkan <strong>Server Utama (A) Antarmuka & RBAC</strong> dengan <strong>Server (B) Database <code>dbPasien</code> & Web Service</strong>.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="<?= $isLoggedIn ? 'pasien.php' : 'login.php' ?>" class="inline-flex items-center gap-2.5 px-7 py-4 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-slate-950 font-extrabold text-sm shadow-xl shadow-teal-500/25 transition">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Kelola Pendaftaran Pasien</span>
                    </a>
                    <a href="#poliklinik" class="inline-flex items-center gap-2 px-6 py-4 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/20 text-white font-bold text-sm backdrop-blur-xs transition">
                        <i class="fa-solid fa-stethoscope text-teal-300"></i>
                        <span>Jadwal Poliklinik & Dokter</span>
                    </a>
                </div>
            </div>

            <!-- Right Card: Live Stats from Server B Web Service -->
            <div class="lg:col-span-5">
                <div class="bg-white/10 backdrop-blur-xl border border-white/15 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider text-teal-300">Status Layanan Real-Time</span>
                            <h3 class="font-heading font-bold text-xl text-white mt-0.5">Data Server B (dbPasien)</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold">
                            Online
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-900/60 border border-white/10 rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-300 flex items-center justify-center mb-3">
                                <i class="fa-solid fa-house-medical text-lg"></i>
                            </div>
                            <div class="text-3xl font-heading font-extrabold text-white"><?= (int)($stat['total_poli_aktif'] ?? $stat['total_poliklinik'] ?? count($listPoli)) ?></div>
                            <div class="text-xs text-slate-400 mt-1">Unit Poliklinik Aktif</div>
                        </div>

                        <div class="bg-slate-900/60 border border-white/10 rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center mb-3">
                                <i class="fa-solid fa-user-doctor text-lg"></i>
                            </div>
                            <div class="text-3xl font-heading font-extrabold text-white"><?= (int)($stat['total_dokter_aktif'] ?? $stat['total_dokter'] ?? count($listDok)) ?></div>
                            <div class="text-xs text-slate-400 mt-1">Dokter Spesialis & Umum</div>
                        </div>

                        <div class="bg-slate-900/60 border border-white/10 rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center mb-3">
                                <i class="fa-solid fa-bed-pulse text-lg"></i>
                            </div>
                            <div class="text-3xl font-heading font-extrabold text-white"><?= (int)($stat['total_pasien'] ?? 0) ?></div>
                            <div class="text-xs text-slate-400 mt-1">Pasien Terdaftar</div>
                        </div>

                        <div class="bg-slate-900/60 border border-white/10 rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-300 flex items-center justify-center mb-3">
                                <i class="fa-solid fa-shield-heart text-lg"></i>
                            </div>
                            <div class="text-3xl font-heading font-extrabold text-white"><?= (int)($stat['total_asuransi'] ?? count($listAsu)) ?></div>
                            <div class="text-xs text-slate-400 mt-1">Mitra Asuransi & BPJS</div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-300/90 bg-slate-950/60 rounded-xl p-3.5 border border-white/10 flex items-center justify-between">
                        <span>Endpoint API Server B:</span>
                        <code class="text-teal-300 font-mono"><?= htmlspecialchars(get_api_base_url()) ?></code>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DAFTAR POLIKLINIK -->
    <section id="poliklinik" class="py-16 lg:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Layanan Medis Unggulan</span>
                <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 mt-1">Unit Poliklinik Tersedia</h2>
            </div>
            <a href="<?= $isLoggedIn ? 'poliklinik.php' : 'login.php' ?>" class="text-sm font-bold text-teal-600 hover:text-teal-700 inline-flex items-center gap-1.5">
                <span>Kelola Data Poliklinik</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($listPoli as $poli): ?>
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-lg bg-teal-50 text-teal-700 font-mono text-xs font-bold border border-teal-200">
                                <?= htmlspecialchars($poli['kode_poli'] ?? $poli['kode_poliklinik'] ?? '-') ?>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= in_array(strtolower($poli['status'] ?? 'aktif'), ['aktif', 'buka'], true) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                ● <?= htmlspecialchars($poli['status'] ?? $poli['status_poli'] ?? 'aktif') ?>
                            </span>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-slate-900 mb-2"><?= htmlspecialchars($poli['nama_poli'] ?? $poli['nama_poliklinik'] ?? '') ?></h3>
                        <p class="text-xs text-slate-500 flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-teal-600 w-4 mt-0.5"></i>
                            <span><?= htmlspecialchars($poli['keterangan'] ?? $poli['lokasi_ruangan'] ?? '-') ?></span>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- DAFTAR DOKTER -->
    <section id="dokter" class="py-16 bg-white border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Tenaga Medis Profesional</span>
                    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 mt-1">Tim Dokter Spesialis & Umum</h2>
                </div>
                <a href="<?= $isLoggedIn ? 'dokter.php' : 'login.php' ?>" class="text-sm font-bold text-teal-600 hover:text-teal-700 inline-flex items-center gap-1.5">
                    <span>Kelola Data Dokter</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($listDok as $dok): ?>
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center text-lg font-bold shrink-0 shadow-sm">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-heading font-bold text-base text-slate-900 truncate"><?= htmlspecialchars($dok['nama_dokter'] ?? '') ?></h3>
                            <p class="text-xs font-semibold text-teal-700 mt-0.5"><?= htmlspecialchars($dok['spesialisasi'] ?? '') ?></p>
                            <p class="text-xs text-slate-500 mt-2">
                                <i class="fa-solid fa-house-medical mr-1 text-slate-400"></i>
                                <?= htmlspecialchars($dok['nama_poli'] ?? $poliMap[$dok['id_poli'] ?? 0] ?? 'Poliklinik') ?>
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                <i class="fa-solid fa-id-badge mr-1 text-slate-400"></i>
                                SIP: <?= htmlspecialchars($dok['no_sip'] ?? $dok['kode_dokter'] ?? '-') ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- MITRA ASURANSI -->
    <section id="asuransi" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Penjaminan Biaya Kesehatan</span>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 mt-1">Mitra Asuransi & Jaminan Kesehatan</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($listAsu as $asu): ?>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs text-center">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mx-auto mb-3 text-lg">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-teal-600 uppercase"><?= htmlspecialchars($asu['jenis_asuransi'] ?? '') ?></span>
                    <h4 class="font-heading font-bold text-base text-slate-900 mt-0.5"><?= htmlspecialchars($asu['nama_asuransi'] ?? '') ?></h4>
                    <p class="text-xs text-slate-500 mt-1">Kontak: <?= htmlspecialchars($asu['nomor_kontak'] ?? '-') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 text-center text-xs">
        <div class="max-w-7xl mx-auto px-4">
            <p class="font-semibold text-white">SIM-RS Medika — Sistem Informasi Manajemen Pasien Berbasis Web Service (SOA)</p>
            <p class="mt-1">Server Utama (A) Frontend & RBAC $\leftrightarrow$ Server (B) Database <code>dbPasien</code> & REST API</p>
        </div>
    </footer>

</body>
</html>
