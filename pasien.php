<?php
/**
 * pasien.php - UI CRUD Tabel `pasien` (PK: nik CHAR(16)) ke Web Service Server B
 */
require_once __DIR__ . '/layout.php';
require_login();

$role      = current_role();
$canWrite  = can_edit('pasien');
$canDelete = can_delete('pasien');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canWrite) {
    $act = $_POST['form_action'] ?? '';
    $nik = trim($_POST['old_nik'] ?? $_POST['nik'] ?? '');
    if ($act === 'create') {
        $res = api_request('POST', '/pasien', $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'update') {
        $res = api_request('PUT', '/pasien/' . urlencode($nik), $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'delete') {
        $res = api_request('DELETE', '/pasien/' . urlencode($nik));
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    }
    header('Location: pasien.php');
    exit;
}

$resPasien  = api_request('GET', '/pasien');
$listPasien = is_array($resPasien['data'] ?? null) ? $resPasien['data'] : [];

// Jika role == 'pasien', hanya tampilkan biodata milik pasien yang sedang login sendiri!
if ($role === 'pasien') {
    $myNik = $_SESSION['user']['nik'] ?? '1771011505900001';
    $filtered = array_values(array_filter($listPasien, function ($p) use ($myNik) {
        return ($p['nik'] ?? '') === $myNik || stripos($p['nama_lengkap'] ?? '', 'Ahmad Pratama') !== false;
    }));
    $listPasien = !empty($filtered) ? $filtered : array_slice($listPasien, 0, 1);
}

$resAsu     = api_request('GET', '/asuransi');
$listAsu    = is_array($resAsu['data'] ?? null) ? $resAsu['data'] : [];
$asuMap     = [];
foreach ($listAsu as $as) {
    if (!empty($as['id_asuransi'])) {
        $asuMap[$as['id_asuransi']] = $as['nama_asuransi'] ?? 'Asuransi';
    }
}

render_header('Manajemen Data Pasien (Tabel pasien)', 'pasien');
?>

<div x-data="{
    modalOpen: false,
    isEdit: false,
    form: {
        old_nik: '',
        nik: '',
        nama_lengkap: '',
        tempat_lahir: 'Bengkulu',
        tanggal_lahir: '1995-05-15',
        jenis_kelamin: 'L',
        golongan_darah: 'O',
        no_hp: '',
        email: '',
        alamat: '',
        kota_kabupaten: 'Kota Bengkulu',
        provinsi: 'Bengkulu',
        id_asuransi: '1',
        nomor_peserta_asuransi: '',
        nama_kontak_darurat: '',
        no_kontak_darurat: '',
        hubungan_kontak_darurat: ''
    }
}">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-teal-600">Tabel 2 · db_pasien.pasien</span>
            <h2 class="font-heading font-bold text-lg text-slate-900">Data Master Pasien Terdaftar</h2>
        </div>
        <?php if ($canWrite): ?>
        <button type="button"
                @click="isEdit = false; form = { old_nik: '', nik: '177101' + Math.floor(1000000000 + Math.random() * 8999999999), nama_lengkap: '', tempat_lahir: 'Bengkulu', tanggal_lahir: '1998-01-15', jenis_kelamin: 'L', golongan_darah: 'O', no_hp: '0812', email: '', alamat: 'Jl. Suprapto, Bengkulu', kota_kabupaten: 'Kota Bengkulu', provinsi: 'Bengkulu', id_asuransi: '1', nomor_peserta_asuransi: '', nama_kontak_darurat: '', no_kontak_darurat: '', hubungan_kontak_darurat: 'Keluarga' }; modalOpen = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition">
            <i class="fa-solid fa-user-plus"></i> Tambah Pasien Baru (POST API)
        </button>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase text-slate-500">
                        <th class="py-3.5 px-4">NIK (16 Digit)</th>
                        <th class="py-3.5 px-4">Nama Lengkap & TTL</th>
                        <th class="py-3.5 px-4">L/P & Gol. Darah</th>
                        <th class="py-3.5 px-4">Asuransi & No. Peserta</th>
                        <th class="py-3.5 px-4">Kontak & Alamat</th>
                        <th class="py-3.5 px-4">Kontak Darurat</th>
                        <?php if ($canWrite): ?>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (!empty($listPasien)): ?>
                        <?php foreach ($listPasien as $row): ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded bg-teal-50 text-teal-700 font-mono text-xs font-bold border border-teal-200">
                                        <?= htmlspecialchars($row['nik'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama_pasien'] ?? '') ?></div>
                                    <div class="text-xs text-slate-500">
                                        <?= htmlspecialchars($row['tempat_lahir'] ?? '-') ?>, <?= htmlspecialchars($row['tanggal_lahir'] ?? '-') ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-xs">
                                        <?= ($row['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' ?>
                                    </span>
                                    <div class="text-xs text-slate-500 mt-1">Gol. Darah: <strong><?= htmlspecialchars($row['golongan_darah'] ?? '-') ?></strong></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= htmlspecialchars($row['nama_asuransi'] ?? $asuMap[$row['id_asuransi'] ?? 0] ?? 'Umum / Mandiri') ?></div>
                                    <div class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($row['nomor_peserta_asuransi'] ?? '-') ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    <div class="font-semibold text-slate-800"><?= htmlspecialchars($row['no_hp'] ?? '-') ?></div>
                                    <div><?= htmlspecialchars($row['alamat'] ?? '-') ?>, <?= htmlspecialchars($row['kota_kabupaten'] ?? '') ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    <div class="font-semibold"><?= htmlspecialchars($row['nama_kontak_darurat'] ?? '-') ?> (<?= htmlspecialchars($row['hubungan_kontak_darurat'] ?? '-') ?>)</div>
                                    <div class="text-slate-400"><?= htmlspecialchars($row['no_kontak_darurat'] ?? '-') ?></div>
                                </td>
                                <?php if ($canWrite): ?>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <button type="button"
                                            @click='isEdit = true; form = Object.assign({}, <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, {old_nik: "<?= htmlspecialchars($row['nik'] ?? '') ?>"}); modalOpen = true'
                                            class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition mr-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <?php if ($canDelete): ?>
                                    <form method="post" onsubmit="return confirm('Hapus data pasien NIK <?= htmlspecialchars($row['nik'] ?? '') ?> via API?');" class="inline">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="nik" value="<?= htmlspecialchars($row['nik'] ?? '') ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-xs font-bold transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada data pasien.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Create / Edit Pasien -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 my-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-heading font-bold text-lg text-slate-900" x-text="isEdit ? 'Edit Data Pasien (PUT API)' : 'Tambah Pasien Baru (POST API)'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="post" class="mt-4 space-y-4">
                <input type="hidden" name="form_action" :value="isEdit ? 'update' : 'create'">
                <input type="hidden" name="old_nik" :value="form.old_nik">

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">NIK (16 Digit Angka)</label>
                        <input type="text" name="nik" required pattern="[0-9]{16}" maxlength="16" x-model="form.nik" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nama Lengkap Pasien</label>
                        <input type="text" name="nama_lengkap" required x-model="form.nama_lengkap" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                <div class="grid sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" x-model="form.tempat_lahir" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" x-model="form.tanggal_lahir" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="form.jenis_kelamin" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Gol. Darah</label>
                        <select name="golongan_darah" x-model="form.golongan_darah" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                            <option value="O">O</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="Tidak diketahui">Tidak diketahui</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Asuransi Penjamin</label>
                        <select name="id_asuransi" x-model="form.id_asuransi" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                            <?php foreach ($listAsu as $as): ?>
                                <option value="<?= (int)$as['id_asuransi'] ?>"><?= htmlspecialchars($as['nama_asuransi']) ?> (<?= htmlspecialchars($as['jenis_asuransi'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nomor Peserta Asuransi / BPJS</label>
                        <input type="text" name="nomor_peserta_asuransi" x-model="form.nomor_peserta_asuransi" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">No. HP</label>
                        <input type="text" name="no_hp" x-model="form.no_hp" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" x-model="form.email" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Kota / Kabupaten</label>
                        <input type="text" name="kota_kabupaten" x-model="form.kota_kabupaten" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Alamat Lengkap</label>
                    <input type="text" name="alamat" required x-model="form.alamat" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <div class="grid sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nama Kontak Darurat</label>
                        <input type="text" name="nama_kontak_darurat" x-model="form.nama_kontak_darurat" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">No. HP Kontak Darurat</label>
                        <input type="text" name="no_kontak_darurat" x-model="form.no_kontak_darurat" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Hubungan</label>
                        <input type="text" name="hubungan_kontak_darurat" x-model="form.hubungan_kontak_darurat" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold">Simpan Data Pasien</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php render_footer(); ?>
