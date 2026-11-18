<?php
/**
 * poliklinik.php - UI CRUD Tabel `poli` (Poliklinik) ke Web Service Server B
 */
require_once __DIR__ . '/layout.php';
require_login();

$canWrite = can_edit('poliklinik');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canWrite) {
    $act = $_POST['form_action'] ?? '';
    $id  = (int)($_POST['id_poli'] ?? $_POST['id_poliklinik'] ?? 0);
    if ($act === 'create') {
        $res = api_request('POST', '/poli', $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'update') {
        $res = api_request('PUT', '/poli/' . $id, $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'delete') {
        $res = api_request('DELETE', '/poli/' . $id);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    }
    header('Location: poliklinik.php');
    exit;
}

$res      = api_request('GET', '/poli');
$listPoli = is_array($res['data'] ?? null) ? $res['data'] : [];

render_header('Manajemen Data Poliklinik (Tabel poli)', 'poliklinik');
?>

<div x-data="{ modalOpen: false, isEdit: false, form: { id_poli: '', kode_poli: '', nama_poli: '', keterangan: '', status: 'aktif' } }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-teal-600">Tabel 1 · db_pasien.poli</span>
            <h2 class="font-heading font-bold text-lg text-slate-900">Daftar Unit Poliklinik</h2>
        </div>
        <?php if ($canWrite): ?>
        <button type="button"
                @click="isEdit = false; form = { id_poli: '', kode_poli: 'POLI-', nama_poli: '', keterangan: '', status: 'aktif' }; modalOpen = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition">
            <i class="fa-solid fa-plus"></i> Tambah Poliklinik (POST API)
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
                        <th class="py-3.5 px-5">Kode Poli</th>
                        <th class="py-3.5 px-5">Nama Poliklinik</th>
                        <th class="py-3.5 px-5">Keterangan / Layanan</th>
                        <th class="py-3.5 px-5">Status</th>
                        <?php if ($canWrite): ?>
                        <th class="py-3.5 px-5 text-right">Aksi (PUT / DELETE)</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (!empty($listPoli)): ?>
                        <?php $no = 1; foreach ($listPoli as $row): ?>
                            <?php
                            $idPoli = (int)($row['id_poli'] ?? $row['id_poliklinik'] ?? 0);
                            $kode   = $row['kode_poli'] ?? $row['kode_poliklinik'] ?? '';
                            $nama   = $row['nama_poli'] ?? $row['nama_poliklinik'] ?? '';
                            $ket    = $row['keterangan'] ?? $row['lokasi_ruangan'] ?? '-';
                            $st     = strtolower($row['status'] ?? $row['status_poli'] ?? 'aktif');
                            ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3.5 px-5 text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 font-mono text-xs font-bold border border-teal-200">
                                        <?= htmlspecialchars($kode) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-bold text-slate-900"><?= htmlspecialchars($nama) ?></td>
                                <td class="py-3.5 px-5 text-slate-600"><?= htmlspecialchars($ket) ?></td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= in_array($st, ['aktif', 'buka'], true) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                        <?= htmlspecialchars($st) ?>
                                    </span>
                                </td>
                                <?php if ($canWrite): ?>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <button type="button"
                                            @click='isEdit = true; form = <?= json_encode([
                                                "id_poli" => $idPoli,
                                                "kode_poli" => $kode,
                                                "nama_poli" => $nama,
                                                "keterangan" => $ket,
                                                "status" => $st
                                            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>; modalOpen = true'
                                            class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition mr-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form method="post" onsubmit="return confirm('Hapus poliklinik ini melalui Web Service?');" class="inline">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="id_poli" value="<?= $idPoli ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-xs font-bold transition">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada data poliklinik.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Create / Edit -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-heading font-bold text-lg text-slate-900" x-text="isEdit ? 'Edit Data Poliklinik (PUT API)' : 'Tambah Poliklinik Baru (POST API)'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="post" class="mt-4 space-y-4">
                <input type="hidden" name="form_action" :value="isEdit ? 'update' : 'create'">
                <input type="hidden" name="id_poli" :value="form.id_poli">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Kode Poli</label>
                        <input type="text" name="kode_poli" required x-model="form.kode_poli" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
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
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nama Poliklinik</label>
                    <input type="text" name="nama_poli" required x-model="form.nama_poli" placeholder="Contoh: Poliklinik Jantung" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" x-model="form.keterangan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
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
