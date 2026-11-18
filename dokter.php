<?php
/**
 * dokter.php - UI CRUD Tabel `dokter` ke Web Service Server B
 */
require_once __DIR__ . '/layout.php';
require_login();

$canWrite = can_edit('dokter');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canWrite) {
    $act = $_POST['form_action'] ?? '';
    $id  = (int)($_POST['id_dokter'] ?? 0);
    if ($act === 'create') {
        $res = api_request('POST', '/dokter', $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'update') {
        $res = api_request('PUT', '/dokter/' . $id, $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'delete') {
        $res = api_request('DELETE', '/dokter/' . $id);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    }
    header('Location: dokter.php');
    exit;
}

$resDok   = api_request('GET', '/dokter');
$listDok  = is_array($resDok['data'] ?? null) ? $resDok['data'] : [];

$resPoli  = api_request('GET', '/poli');
$listPoli = is_array($resPoli['data'] ?? null) ? $resPoli['data'] : [];
$poliMap  = [];
foreach ($listPoli as $pl) {
    $idP = $pl['id_poli'] ?? $pl['id_poliklinik'] ?? null;
    if ($idP !== null) {
        $poliMap[$idP] = $pl['nama_poli'] ?? $pl['nama_poliklinik'] ?? '-';
    }
}

render_header('Manajemen Data Dokter (Tabel dokter)', 'dokter');
?>

<div x-data="{ modalOpen: false, isEdit: false, form: { id_dokter: '', kode_dokter: 'DOK-', no_sip: 'SIP.', nama_dokter: '', spesialisasi: 'Dokter Umum', id_poli: '1', no_hp: '0812', alamat: '', status: 'aktif' } }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-teal-600">Tabel 5 · db_pasien.dokter</span>
            <h2 class="font-heading font-bold text-lg text-slate-900">Daftar Dokter Spesialis & Umum</h2>
        </div>
        <?php if ($canWrite): ?>
        <button type="button"
                @click="isEdit = false; form = { id_dokter: '', kode_dokter: 'DOK-' + Math.floor(100 + Math.random()*900), no_sip: 'SIP.' + Math.floor(100000 + Math.random()*900000), nama_dokter: 'dr. ', spesialisasi: 'Spesialis Penyakit Dalam', id_poli: '1', no_hp: '0812', alamat: 'Bengkulu', status: 'aktif' }; modalOpen = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition">
            <i class="fa-solid fa-user-doctor"></i> Tambah Dokter (POST API)
        </button>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase text-slate-500">
                        <th class="py-3.5 px-5 w-14">No</th>
                        <th class="py-3.5 px-5">Kode & No. SIP</th>
                        <th class="py-3.5 px-5">Nama Dokter & Spesialisasi</th>
                        <th class="py-3.5 px-5">Poliklinik</th>
                        <th class="py-3.5 px-5">No. HP & Alamat</th>
                        <th class="py-3.5 px-5">Status</th>
                        <?php if ($canWrite): ?>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (!empty($listDok)): ?>
                        <?php $no = 1; foreach ($listDok as $row): ?>
                            <?php $st = strtolower($row['status'] ?? $row['status_dokter'] ?? 'aktif'); ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3.5 px-5 text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 font-mono text-xs font-bold border border-sky-200">
                                        <?= htmlspecialchars($row['kode_dokter'] ?? '-') ?>
                                    </span>
                                    <div class="text-xs text-slate-400 font-mono mt-1"><?= htmlspecialchars($row['no_sip'] ?? '-') ?></div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['nama_dokter'] ?? '') ?></div>
                                    <div class="text-xs text-teal-600 font-medium"><?= htmlspecialchars($row['spesialisasi'] ?? '') ?></div>
                                </td>
                                <td class="py-3.5 px-5 font-medium text-slate-700"><?= htmlspecialchars($row['nama_poli'] ?? $poliMap[$row['id_poli'] ?? 0] ?? '-') ?></td>
                                <td class="py-3.5 px-5 text-xs text-slate-600">
                                    <div><?= htmlspecialchars($row['no_hp'] ?? '-') ?></div>
                                    <div class="text-slate-400"><?= htmlspecialchars($row['alamat'] ?? '-') ?></div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $st === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                        <?= htmlspecialchars($st) ?>
                                    </span>
                                </td>
                                <?php if ($canWrite): ?>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <button type="button"
                                            @click='isEdit = true; form = <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>; modalOpen = true'
                                            class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition mr-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form method="post" onsubmit="return confirm('Hapus data dokter ini via API?');" class="inline">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="id_dokter" value="<?= (int)($row['id_dokter'] ?? 0) ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-xs font-bold transition">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada data dokter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Create / Edit -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-heading font-bold text-lg text-slate-900" x-text="isEdit ? 'Edit Data Dokter (PUT API)' : 'Tambah Dokter Baru (POST API)'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="post" class="mt-4 space-y-4">
                <input type="hidden" name="form_action" :value="isEdit ? 'update' : 'create'">
                <input type="hidden" name="id_dokter" :value="form.id_dokter">

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Kode Dokter</label>
                        <input type="text" name="kode_dokter" required x-model="form.kode_dokter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nomor SIP</label>
                        <input type="text" name="no_sip" x-model="form.no_sip" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Status</label>
                        <select name="status" x-model="form.status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="aktif">aktif</option>
                            <option value="nonaktif">nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nama Lengkap & Gelar Dokter</label>
                    <input type="text" name="nama_dokter" required x-model="form.nama_dokter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Spesialisasi</label>
                        <input type="text" name="spesialisasi" required x-model="form.spesialisasi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Unit Poliklinik</label>
                        <select name="id_poli" x-model="form.id_poli" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <?php foreach ($listPoli as $pl): ?>
                                <option value="<?= (int)($pl['id_poli'] ?? $pl['id_poliklinik']) ?>"><?= htmlspecialchars($pl['nama_poli'] ?? $pl['nama_poliklinik']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">No. HP</label>
                        <input type="text" name="no_hp" x-model="form.no_hp" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Alamat</label>
                        <input type="text" name="alamat" x-model="form.alamat" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold">Simpan ke Web Service</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php render_footer(); ?>
