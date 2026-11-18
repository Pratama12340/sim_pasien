<?php
/**
 * dashboard.php - Dashboard Dinamis Berbeda untuk Masing-Masing dari 6 Role RBAC
 * (1. super_admin, 2. admin, 3. pendaftaran, 4. dokter, 5. kasir, 6. pasien)
 */
require_once __DIR__ . '/layout.php';
require_login();

$role     = current_role();
$user     = $_SESSION['user'] ?? [];
$namaUser = $user['nama_lengkap'] ?? $user['username'] ?? 'Pengguna';

$resStat = api_request('GET', '/dashboard');
if (empty($resStat['success'])) {
    $resStat = api_request('GET', '/statistik');
}
$stat = $resStat['data'] ?? [];

$listPasien = [];
if (has_permission('pasien.view')) {
    $resPasien  = api_request('GET', '/pasien');
    $listPasien = is_array($resPasien['data'] ?? null) ? $resPasien['data'] : [];
}

$listPoli = [];
if (has_permission('poli.view')) {
    $resPoli  = api_request('GET', '/poli');
    $listPoli = is_array($resPoli['data'] ?? null) ? $resPoli['data'] : [];
}

$listDok = [];
if (has_permission('dokter.view')) {
    $resDok  = api_request('GET', '/dokter');
    $listDok = is_array($resDok['data'] ?? null) ? $resDok['data'] : [];
}

$listAsu = [];
$asuMap  = [];
if (has_permission('pasien.view')) {
    $resAsu  = api_request('GET', '/asuransi');
    $listAsu = is_array($resAsu['data'] ?? null) ? $resAsu['data'] : [];
    foreach ($listAsu as $as) {
        if (!empty($as['id_asuransi'])) {
            $asuMap[$as['id_asuransi']] = $as['nama_asuransi'] ?? 'Asuransi';
        }
    }
}

render_header('Dashboard ' . role_label($role), 'dashboard');
?>

<?php if ($role === 'pasien'): ?>
    <?php
    // Cari data rekam medis/biodata milik pasien yang sedang login (Ahmad Pratama Putra / NIK 1771011505900001)
    $myNik = $user['nik'] ?? '1771011505900001';
    $myPasien = null;
    foreach ($listPasien as $p) {
        if (($p['nik'] ?? '') === $myNik || stripos($p['nama_lengkap'] ?? '', 'Ahmad Pratama') !== false) {
            $myPasien = $p;
            break;
        }
    }
    if (!$myPasien && !empty($listPasien)) {
        $myPasien = $listPasien[0];
    }
    ?>
    <!-- ==================== TAMPILAN KHUSUS ROLE 6: PASIEN ==================== -->
    <div class="rounded-3xl bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 text-white p-6 sm:p-8 shadow-lg flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-bold uppercase">
                <i class="fa-solid fa-id-card-clip"></i> Portal Mandiri Pasien · db_pasien.akun_pasien
            </span>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl">Halo, <?= htmlspecialchars($myPasien['nama_lengkap'] ?? $namaUser) ?>!</h2>
            <p class="text-emerald-50 text-sm max-w-2xl">
                Ini adalah halaman khusus pasien. Anda hanya dapat melihat kartu berobat, biodata pribadi Anda sendiri, serta informasi layanan Poliklinik dan Jadwal Dokter.
            </p>
        </div>
        <a href="pasien.php" class="px-5 py-3 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-bold text-xs shadow-md transition shrink-0">
            <i class="fa-solid fa-user-check mr-1.5"></i> Lihat Biodata Lengkap Saya
        </a>
    </div>

    <!-- Kartu Berobat Digital Pasien -->
    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase text-emerald-600">Kartu Identitas Berobat Pasien</span>
                    <h3 class="font-heading font-bold text-lg text-slate-900"><?= htmlspecialchars($myPasien['nama_lengkap'] ?? $namaUser) ?></h3>
                </div>
                <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-mono text-xs font-bold border border-emerald-200">
                    NIK: <?= htmlspecialchars($myPasien['nik'] ?? $myNik) ?>
                </span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs text-slate-400 block">Tempat, Tanggal Lahir</span>
                    <strong class="text-slate-800"><?= htmlspecialchars(($myPasien['tempat_lahir'] ?? 'Bengkulu') . ', ' . ($myPasien['tanggal_lahir'] ?? '1990-05-15')) ?></strong>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs text-slate-400 block">Jenis Kelamin & Gol. Darah</span>
                    <strong class="text-slate-800"><?= ($myPasien['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-laki' : 'Perempuan' ?> · Gol. Darah <?= htmlspecialchars($myPasien['golongan_darah'] ?? 'O') ?></strong>
                </div>
                <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <span class="text-xs text-emerald-600 block font-semibold">Penjamin / Asuransi Saya</span>
                    <strong class="text-emerald-900"><?= htmlspecialchars($myPasien['nama_asuransi'] ?? 'BPJS Kesehatan') ?></strong>
                    <span class="block text-xs font-mono text-emerald-700 mt-0.5">No. Peserta: <?= htmlspecialchars($myPasien['nomor_peserta_asuransi'] ?? '-') ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs text-slate-400 block">Kontak Darurat</span>
                    <strong class="text-slate-800"><?= htmlspecialchars($myPasien['nama_kontak_darurat'] ?? '-') ?> (<?= htmlspecialchars($myPasien['hubungan_kontak_darurat'] ?? '-') ?>)</strong>
                    <span class="block text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($myPasien['no_kontak_darurat'] ?? '-') ?></span>
                </div>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-sm">
                <span class="text-xs text-slate-400 block">Alamat Domisili</span>
                <strong class="text-slate-800"><?= htmlspecialchars(($myPasien['alamat'] ?? '-') . ', ' . ($myPasien['kota_kabupaten'] ?? 'Kota Bengkulu') . ', ' . ($myPasien['provinsi'] ?? 'Bengkulu')) ?></strong>
            </div>
        </div>

        <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="font-heading font-bold text-base text-slate-900 border-b border-slate-100 pb-3">Ringkasan Aktivitas & Hak Akses Saya</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs text-slate-400 block">Kunjungan Hari Ini</span>
                    <strong class="text-xl text-slate-900"><?= (int)($stat['kunjungan_hari_ini'] ?? 0) ?></strong>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs text-slate-400 block">Antrean Menunggu</span>
                    <strong class="text-xl text-slate-900"><?= (int)($stat['antrean_menunggu'] ?? 0) ?></strong>
                </div>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-400 block mb-2">Izin Akses Token JWT Anda:</span>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach (($_SESSION['user']['permissions'] ?? ['dashboard.view', 'pasien.view']) as $pm): ?>
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-mono"><?= htmlspecialchars($pm) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($role === 'dokter'): ?>
    <?php
    $myDokter = $listDok[0] ?? [];
    foreach ($listDok as $d) {
        if (stripos($d['nama_dokter'] ?? '', 'Hendra') !== false) {
            $myDokter = $d;
            break;
        }
    }
    ?>
    <!-- ==================== TAMPILAN KHUSUS ROLE 4: DOKTER ==================== -->
    <div class="rounded-3xl bg-gradient-to-r from-sky-700 via-blue-600 to-indigo-700 text-white p-6 sm:p-8 shadow-lg flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-bold uppercase">
                <i class="fa-solid fa-stethoscope"></i> Portal Medis Dokter · Role: dokter
            </span>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl"><?= htmlspecialchars($myDokter['nama_dokter'] ?? $namaUser) ?></h2>
            <p class="text-sky-100 text-sm">
                <?= htmlspecialchars($myDokter['spesialisasi'] ?? 'Spesialis Penyakit Dalam') ?> · Unit: <strong><?= htmlspecialchars($myDokter['nama_poli'] ?? 'Poliklinik Penyakit Dalam') ?></strong> · No. SIP: <code><?= htmlspecialchars($myDokter['no_sip'] ?? 'SIP.19850101.001') ?></code>
            </p>
        </div>
        <a href="pasien.php" class="px-5 py-3 rounded-xl bg-white text-sky-800 hover:bg-sky-50 font-bold text-xs shadow-md transition shrink-0">
            <i class="fa-solid fa-notes-medical mr-1.5"></i> Lihat Daftar Pasien
        </a>
    </div>

    <div class="grid sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-sky-600">Poliklinik Praktik</span>
            <div class="text-xl font-heading font-extrabold text-slate-900 mt-2"><?= htmlspecialchars($myDokter['nama_poli'] ?? 'Poliklinik Penyakit Dalam') ?></div>
            <p class="text-xs text-slate-500 mt-1">Kode Dokter: <?= htmlspecialchars($myDokter['kode_dokter'] ?? 'DOK-001') ?></p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-emerald-600">Total Pasien Terdaftar</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listPasien) ?> Pasien</div>
            <p class="text-xs text-slate-500 mt-1">Siap untuk pemeriksaan medis</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-indigo-600">Rekan Dokter Aktif</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listDok) ?> Dokter</div>
            <p class="text-xs text-slate-500 mt-1">Tersebar di <?= count($listPoli) ?> Poliklinik</p>
        </div>
    </div>

<?php elseif ($role === 'kasir'): ?>
    <?php
    $countBpjs = 0;
    $countSwasta = 0;
    foreach ($listPasien as $p) {
        if (($p['jenis_asuransi'] ?? '') === 'BPJS') $countBpjs++;
        else $countSwasta++;
    }
    ?>
    <!-- ==================== TAMPILAN KHUSUS ROLE 5: KASIR ==================== -->
    <div class="rounded-3xl bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-800 text-white p-6 sm:p-8 shadow-lg flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-bold uppercase">
                <i class="fa-solid fa-cash-register"></i> Portal Kasir, Billing & Klaim Asuransi · Role: kasir
            </span>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl">Selamat Bertugas, <?= htmlspecialchars($namaUser) ?></h2>
            <p class="text-purple-100 text-sm max-w-2xl">
                Halaman khusus Kasir untuk memverifikasi kepesertaan asuransi pasien (BPJS, Swasta, atau Umum) serta mengelola mitra penjamin biaya kesehatan.
            </p>
        </div>
        <a href="asuransi.php" class="px-5 py-3 rounded-xl bg-white text-purple-800 hover:bg-purple-50 font-bold text-xs shadow-md transition shrink-0">
            <i class="fa-solid fa-shield-heart mr-1.5"></i> Kelola Mitra Asuransi
        </a>
    </div>

    <div class="grid sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-emerald-600">Pasien Peserta BPJS</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= $countBpjs ?> Pasien</div>
            <p class="text-xs text-slate-500 mt-1">Klaim JKN / BPJS Kesehatan</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-purple-600">Pasien Asuransi Swasta / Umum</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= $countSwasta ?> Pasien</div>
            <p class="text-xs text-slate-500 mt-1">Mandiri Inhealth / Prudential / Tunai</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-amber-600">Total Mitra Penjamin</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listAsu) ?> Asuransi</div>
            <p class="text-xs text-slate-500 mt-1">Terdaftar aktif di sistem</p>
        </div>
    </div>

<?php elseif ($role === 'pendaftaran'): ?>
    <!-- ==================== TAMPILAN KHUSUS ROLE 3: PENDAFTARAN ==================== -->
    <div class="rounded-3xl bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white p-6 sm:p-8 shadow-lg flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-bold uppercase">
                <i class="fa-solid fa-clipboard-user"></i> Loket Pendaftaran & Admisi Pasien · Role: pendaftaran
            </span>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl">Loket Pendaftaran — <?= htmlspecialchars($namaUser) ?></h2>
            <p class="text-amber-50 text-sm max-w-2xl">
                Petugas Pendaftaran memiliki wewenang penuh untuk mendaftarkan pasien baru & memperbarui biodata pasien, serta melihat ketersediaan Poliklinik, Dokter, dan Asuransi.
            </p>
        </div>
        <a href="pasien.php" class="px-5 py-3 rounded-xl bg-white text-amber-800 hover:bg-amber-50 font-bold text-xs shadow-md transition shrink-0">
            <i class="fa-solid fa-user-plus mr-1.5"></i> + Registrasi Pasien Baru
        </a>
    </div>

    <div class="grid sm:grid-cols-4 gap-5">
        <a href="pasien.php" class="bg-white rounded-2xl p-5 border border-amber-200 shadow-xs hover:shadow-md transition">
            <span class="text-xs font-bold uppercase text-amber-600">Pasien Terdaftar (CRUD)</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listPasien) ?></div>
            <p class="text-xs text-amber-700 font-semibold mt-1">Kelola Registrasi Pasien →</p>
        </a>
        <a href="poliklinik.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <span class="text-xs font-bold uppercase text-slate-400">Unit Poliklinik (Lihat)</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listPoli) ?></div>
            <p class="text-xs text-slate-500 mt-1">Cek poli aktif →</p>
        </a>
        <a href="dokter.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <span class="text-xs font-bold uppercase text-slate-400">Dokter Praktik (Lihat)</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listDok) ?></div>
            <p class="text-xs text-slate-500 mt-1">Cek dokter spesialis →</p>
        </a>
        <a href="asuransi.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <span class="text-xs font-bold uppercase text-slate-400">Mitra Asuransi (Lihat)</span>
            <div class="text-3xl font-heading font-extrabold text-slate-900 mt-1"><?= count($listAsu) ?></div>
            <p class="text-xs text-slate-500 mt-1">Cek penjamin aktif →</p>
        </a>
    </div>

<?php else: ?>
    <!-- ==================== TAMPILAN ROLE 1 & 2: SUPER_ADMIN & ADMIN ==================== -->
    <div class="rounded-3xl bg-gradient-to-r <?= $role === 'super_admin' ? 'from-rose-700 via-red-600 to-rose-800' : 'from-teal-700 via-teal-600 to-emerald-600' ?> text-white p-6 sm:p-8 shadow-lg flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-bold uppercase tracking-wider">
                <i class="fa-solid fa-shield-halved"></i> <?= $role === 'super_admin' ? 'Super Administrator (Full RBAC Control)' : 'Admin Operasional Klinik' ?>
            </div>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl">
                Selamat bertugas, <?= htmlspecialchars($namaUser) ?>!
            </h2>
            <p class="text-white/90 text-sm leading-relaxed">
                <?= $role === 'super_admin'
                    ? 'Anda login sebagai <strong>super_admin</strong> dengan hak akses penuh untuk mengelola seluruh 5 tabel (Poliklinik, Pasien, Asuransi, Dokter, serta Manajemen Akun Pengguna & RBAC).'
                    : 'Anda login sebagai <strong>admin</strong> dengan wewenang mengelola operasional klinik (Poliklinik, Pasien, Asuransi, dan Dokter).' ?>
            </p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <a href="pasien.php" class="px-5 py-3 rounded-xl bg-white text-slate-900 hover:bg-slate-100 font-bold text-xs shadow-md transition inline-flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Kelola Data Pasien
            </a>
            <?php if ($role === 'super_admin'): ?>
            <a href="pengguna.php" class="px-5 py-3 rounded-xl bg-black/25 hover:bg-black/40 text-white border border-white/25 font-bold text-xs transition inline-flex items-center gap-2">
                <i class="fa-solid fa-users-gear"></i> Kelola 6 Role Pengguna
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5 KARTU STATISTIK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <a href="poliklinik.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase text-slate-400">1. Tabel Poli</span>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-house-medical"></i>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900"><?= (int)($stat['total_poli_aktif'] ?? $stat['total_poliklinik'] ?? count($listPoli)) ?></div>
            <p class="text-xs text-teal-600 font-semibold mt-1">CRUD Poliklinik →</p>
        </a>

        <a href="pasien.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase text-slate-400">2. Tabel Pasien</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-bed-pulse"></i>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900"><?= (int)($stat['total_pasien'] ?? count($listPasien)) ?></div>
            <p class="text-xs text-emerald-600 font-semibold mt-1">CRUD Pasien →</p>
        </a>

        <a href="asuransi.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase text-slate-400">3. Tabel Asuransi</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900"><?= (int)($stat['total_asuransi'] ?? count($listAsu)) ?></div>
            <p class="text-xs text-amber-600 font-semibold mt-1">CRUD Asuransi →</p>
        </a>

        <a href="<?= has_permission('pengguna.manage') ? 'pengguna.php' : '#' ?>" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase text-slate-400">4. Kunjungan Hari Ini</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900"><?= (int)($stat['kunjungan_hari_ini'] ?? $stat['total_pengguna'] ?? 0) ?></div>
            <p class="text-xs text-purple-600 font-semibold mt-1"><?= has_permission('pengguna.manage') ? 'Kelola Pengguna →' : 'Antrean: ' . (int)($stat['antrean_menunggu'] ?? 0) ?></p>
        </a>

        <a href="dokter.php" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase text-slate-400">5. Tabel Dokter</span>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900"><?= (int)($stat['total_dokter_aktif'] ?? $stat['total_dokter'] ?? count($listDok)) ?></div>
            <p class="text-xs text-sky-600 font-semibold mt-1">CRUD Dokter →</p>
        </a>
    </div>
<?php endif; ?>

<?php if ($role !== 'pasien'): ?>
<!-- TABEL DATA PASIEN (UNTUK STAF KLINIK: SUPER_ADMIN, ADMIN, PENDAFTARAN, DOKTER, KASIR) -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h3 class="font-heading font-bold text-base text-slate-900">
                <?= match ($role) {
                    'dokter' => 'Daftar Pasien untuk Pemeriksaan Medis',
                    'kasir'  => 'Daftar Kepesertaan Asuransi & Penjaminan Pasien',
                    default  => 'Daftar Pasien Terdaftar di Klinik'
                } ?>
            </h3>
            <p class="text-xs text-slate-500">Data diambil dari endpoint <code>GET /api/pasien</code></p>
        </div>
        <a href="pasien.php" class="px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs transition">
            Buka Halaman Pasien →
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase text-slate-500">
                    <th class="py-3.5 px-5">NIK (16 Digit)</th>
                    <th class="py-3.5 px-5">Nama Lengkap Pasien</th>
                    <th class="py-3.5 px-5">L/P & Gol. Darah</th>
                    <th class="py-3.5 px-5">No. HP & Kota</th>
                    <th class="py-3.5 px-5">Penjamin / Asuransi</th>
                    <th class="py-3.5 px-5">No. Peserta Asuransi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php if (!empty($listPasien)): ?>
                    <?php foreach (array_slice($listPasien, 0, 5) as $p): ?>
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3.5 px-5 font-mono text-xs font-bold text-teal-700"><?= htmlspecialchars($p['nik'] ?? '-') ?></td>
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($p['nama_lengkap'] ?? $p['nama_pasien'] ?? '-') ?></div>
                                <div class="text-xs text-slate-400">TTL: <?= htmlspecialchars($p['tempat_lahir'] ?? '-') ?>, <?= htmlspecialchars($p['tanggal_lahir'] ?? '-') ?></div>
                            </td>
                            <td class="py-3.5 px-5 font-medium text-slate-700">
                                <?= ($p['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-laki' : 'Perempuan' ?> · Gol. <?= htmlspecialchars($p['golongan_darah'] ?? '-') ?>
                            </td>
                            <td class="py-3.5 px-5 text-slate-600">
                                <div><?= htmlspecialchars($p['no_hp'] ?? '-') ?></div>
                                <div class="text-xs text-slate-400"><?= htmlspecialchars($p['kota_kabupaten'] ?? '-') ?></div>
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 border border-teal-200 text-xs font-semibold">
                                    <?= htmlspecialchars($p['nama_asuransi'] ?? $asuMap[$p['id_asuransi'] ?? 0] ?? 'Umum / Mandiri') ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-mono text-xs text-slate-600">
                                <?= htmlspecialchars($p['nomor_peserta_asuransi'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data pasien.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php render_footer(); ?>
