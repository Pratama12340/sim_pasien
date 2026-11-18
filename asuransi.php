<?php
/**
 * asuransi.php - UI CRUD Tabel `asuransi` ke Web Service Server B
 */
require_once __DIR__ . '/layout.php';
require_login();

$canWrite = can_edit('asuransi');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canWrite) {
    $act = $_POST['form_action'] ?? '';
    $id  = (int)($_POST['id_asuransi'] ?? 0);
    if ($act === 'create') {
        $res = api_request('POST', '/asuransi', $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'update') {
        $res = api_request('PUT', '/asuransi/' . $id, $_POST);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'delete') {
        $res = api_request('DELETE', '/asuransi/' . $id);
        $_SESSION[($res['status'] ?? '') === 'success' ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    }
    header('Location: asuransi.php');
    exit;
}

$res     = api_request('GET', '/asuransi');
$listAsu = is_array($res['data'] ?? null) ? $res['data'] : [];

render_header('Manajemen Data Asuransi (Tabel asuransi)', 'asuransi');
?>

<div x-data="{ modalOpen: false, isEdit: false, form: { id_asuransi: '', nama_asuransi: '', jenis_asuransi: 'BPJS', nomor_kontak: '', alamat: '', status: 'aktif' } }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-teal-600">Tabel 3 · db_pasien.asuransi</span>
            <h2 class="font-heading font-bold text-lg text-slate-900">Daftar Mitra Asuransi & Jaminan Kesehatan</h2>
        </div>
        <?php if ($canWrite): ?>
        <button type="button"
                @click="isEdit = false; form = { id_asuransi: '', nama_asuransi: '', jenis_asuransi: 'swasta', nomor_kontak: '', alamat: '', status: 'aktif' }; modalOpen = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition">
            <i class="fa-solid fa-plus"></i> Tambah Asuransi (POST API)
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
                        <th class="py-3.5 px-5">Nama Asuransi</th>
                        <th class="py-3.5 px-5">Jenis Asuransi</th>
                        <th class="py-3.5 px-5">Nomor Kontak</th>
                        <th class="py-3.5 px-5">Alamat</th>
                        <th class="py-3.5 px-5">Status</th>
                        <?php if ($canWrite): ?>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (!empty($listAsu)): ?>
                        <?php $no = 1; foreach ($listAsu as $row): ?>
                            <?php $st = strtolower($row['status'] ?? $row['status_asuransi'] ?? 'aktif'); ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3.5 px-5 text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-5 font-bold text-slate-900"><?= htmlspecialchars($row['nama_asuransi'] ?? '') ?></td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold text-xs border border-amber-200">
                                        <?= htmlspecialchars($row['jenis_asuransi'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-mono text-xs text-slate-600"><?= htmlspecialchars($row['nomor_kontak'] ?? $row['kontak_darurat'] ?? '-') ?></td>
                                <td class="py-3.5 px-5 text-slate-600"><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
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
                                    <form method="post" onsubmit="return confirm('Hapus asuransi ini melalui Web Service?');" class="inline">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="id_asuransi" value="<?= (int)($row['id_asuransi'] ?? 0) ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-xs font-bold transition">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada data asuransi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Create / Edit -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-heading font-bold text-lg text-slate-900" x-text="isEdit ? 'Edit Data Asuransi (PUT API)' : 'Tambah Asuransi Baru (POST API)'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="post" class="mt-4 space-y-4">
                <input type="hidden" name="form_action" :value="isEdit ? 'update' : 'create'">
                <input type="hidden" name="id_asuransi" :value="form.id_asuransi">

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nama Asuransi</label>
                    <input type="text" name="nama_asuransi" required x-model="form.nama_asuransi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jenis Asuransi</label>
                        <select name="jenis_asuransi" x-model="form.jenis_asuransi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="BPJS">BPJS</option>
                            <option value="swasta">swasta</option>
                            <option value="lainnya">lainnya</option>
                        </select>
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
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nomor Kontak</label>
                    <input type="text" name="nomor_kontak" x-model="form.nomor_kontak" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Alamat</label>
                    <textarea name="alamat" rows="2" x-model="form.alamat" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
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
