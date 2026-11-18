<?php
/**
 * pengguna.php - UI CRUD Tabel Pengguna & Role RBAC (/api/pengguna & /api/roles)
 */
require_once __DIR__ . '/layout.php';
require_login();

if (!has_permission('pengguna.manage') && !has_permission('rbac.manage')) {
    $_SESSION['flash_error'] = 'Akses ditolak: Anda memerlukan izin pengguna.manage (Super Admin) untuk mengakses manajemen pengguna.';
    header('Location: dashboard.php');
    exit;
}

$canWrite = has_permission('pengguna.manage');

$resRoles  = api_request('GET', '/roles');
$listRoles = is_array($resRoles['data'] ?? null) ? $resRoles['data'] : [];

// Peta nama_role <-> id_role
$roleIdByName = [
    'super_admin' => 1,
    'admin'       => 2,
    'pendaftaran' => 3,
    'dokter'      => 4,
    'kasir'       => 5,
    'pasien'      => 6
];
foreach ($listRoles as $r) {
    if (!empty($r['nama_role']) && !empty($r['id_role'])) {
        $roleIdByName[strtolower($r['nama_role'])] = (int)$r['id_role'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canWrite) {
    $act      = $_POST['form_action'] ?? '';
    $namaRole = strtolower(trim($_POST['nama_role'] ?? 'pendaftaran'));
    $idRole   = $roleIdByName[$namaRole] ?? 3;

    $payload = [
        'nama_lengkap'          => trim($_POST['nama_lengkap'] ?? ''),
        'username'              => trim($_POST['username'] ?? ''),
        'email'                 => trim($_POST['email'] ?? '') ?: null,
        'no_hp'                 => trim($_POST['no_hp'] ?? '') ?: null,
        'status'                => $_POST['status'] ?? 'aktif',
        'nama_role'             => $namaRole,
        'role'                  => $namaRole,
        'id_role'               => $idRole,
        'roles'                 => [$idRole],
    ];
    if (!empty($_POST['password'])) {
        $payload['password']              = $_POST['password'];
        $payload['konfirmasi_password']   = $_POST['password'];
        $payload['password_confirmation'] = $_POST['password'];
    }

    if ($act === 'create') {
        $res = api_request('POST', '/pengguna', $payload);
        $_SESSION[!empty($res['success']) ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'update') {
        $id  = (int)($_POST['id_pengguna'] ?? 0);
        $res = api_request('PUT', '/pengguna/' . $id, $payload);
        $_SESSION[!empty($res['success']) ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    } elseif ($act === 'delete') {
        $id  = (int)($_POST['id_pengguna'] ?? 0);
        $res = api_request('DELETE', '/pengguna/' . $id);
        $_SESSION[!empty($res['success']) ? 'flash_success' : 'flash_error'] = $res['message'] ?? 'Selesai.';
    }
    header('Location: pengguna.php');
    exit;
}

$res      = api_request('GET', '/pengguna');
$listUser = is_array($res['data'] ?? null) ? $res['data'] : [];

render_header('Manajemen Pengguna & RBAC (/api/pengguna)', 'pengguna');
?>

<div x-data="{ modalOpen: false, isEdit: false, form: { id_pengguna: '', username: '', nama_lengkap: '', email: '', no_hp: '', nama_role: 'pendaftaran', status: 'aktif', password: '' } }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-teal-600">Tabel 4 · /api/pengguna & /api/roles</span>
            <h2 class="font-heading font-bold text-lg text-slate-900">Daftar Akun Pengguna & Hak Akses RBAC</h2>
        </div>
        <?php if ($canWrite): ?>
        <button type="button"
                @click="isEdit = false; form = { id_pengguna: '', username: '', nama_lengkap: '', email: '', no_hp: '0811', nama_role: 'pendaftaran', status: 'aktif', password: '' }; modalOpen = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna (POST API)
        </button>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase text-slate-500">
                        <th class="py-3.5 px-5 w-14">ID</th>
                        <th class="py-3.5 px-5">Username</th>
                        <th class="py-3.5 px-5">Nama Lengkap</th>
                        <th class="py-3.5 px-5">Email & No. HP</th>
                        <th class="py-3.5 px-5">Role (RBAC)</th>
                        <th class="py-3.5 px-5">Status</th>
                        <?php if ($canWrite): ?>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (!empty($listUser)): ?>
                        <?php foreach ($listUser as $row): ?>
                            <?php
                            if (!is_array($row)) continue;
                            // Ekstrak role baik dari array `roles`, string `nama_role`, `role`, atau deteksi dari username/nama
                            $uname = strtolower(trim($row['username'] ?? ''));
                            $nlengkap = strtolower(trim($row['nama_lengkap'] ?? $row['nama'] ?? ''));
                            $rName = '';
                            if (!empty($row['roles']) && is_array($row['roles'])) {
                                $firstR = reset($row['roles']);
                                $rName = is_array($firstR) ? ($firstR['nama_role'] ?? '') : (string)$firstR;
                            } elseif (!empty($row['nama_role'])) {
                                $rName = (string)$row['nama_role'];
                            } elseif (!empty($row['role'])) {
                                $rName = (string)$row['role'];
                            }
                            if ($rName === '') {
                                if ($uname === 'superadmin' || str_contains($uname, 'super')) {
                                    $rName = 'super_admin';
                                } elseif (str_contains($uname, 'admin')) {
                                    $rName = 'admin';
                                } elseif (str_starts_with($uname, 'dr.') || str_starts_with($uname, 'drg.') || str_starts_with($nlengkap, 'dr.') || str_starts_with($nlengkap, 'drg.') || str_contains($uname, 'dokter')) {
                                    $rName = 'dokter';
                                } elseif (str_contains($uname, 'kasir')) {
                                    $rName = 'kasir';
                                } elseif (str_contains($uname, 'loket') || str_contains($uname, 'pendaftaran')) {
                                    $rName = 'pendaftaran';
                                } elseif (str_contains($uname, 'pasien')) {
                                    $rName = 'pasien';
                                } else {
                                    $rName = 'pendaftaran';
                                }
                            }
                            $rName = strtolower($rName);
                            $st    = strtolower($row['status'] ?? $row['status_aktif'] ?? 'aktif');

                            $roleBadge = match ($rName) {
                                'super_admin' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'admin'       => 'bg-teal-50 text-teal-700 border-teal-200',
                                'pendaftaran' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'dokter'      => 'bg-sky-50 text-sky-700 border-sky-200',
                                'kasir'       => 'bg-purple-50 text-purple-700 border-purple-200',
                                'pasien'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                default       => 'bg-slate-100 text-slate-700 border-slate-200'
                            };
                            $editData = [
                                'id_pengguna'  => $row['id_pengguna'] ?? $row['id'] ?? '',
                                'username'     => $row['username'] ?? '',
                                'nama_lengkap' => $row['nama_lengkap'] ?? $row['nama'] ?? '',
                                'email'        => $row['email'] ?? '',
                                'no_hp'        => $row['no_hp'] ?? '',
                                'nama_role'    => $rName,
                                'status'       => $st,
                                'password'     => ''
                            ];
                            ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3.5 px-5 text-slate-400 font-semibold">#<?= (int)($row['id_pengguna'] ?? $row['id'] ?? 0) ?></td>
                                <td class="py-3.5 px-5 font-mono text-xs font-bold text-slate-900"><?= htmlspecialchars($row['username'] ?? '') ?></td>
                                <td class="py-3.5 px-5 font-bold text-slate-900"><?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama'] ?? '') ?></td>
                                <td class="py-3.5 px-5">
                                    <div class="text-slate-700"><?= htmlspecialchars($row['email'] ?? '-') ?></div>
                                    <div class="text-xs text-slate-400"><?= htmlspecialchars($row['no_hp'] ?? '-') ?></div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-lg font-bold text-xs border <?= $roleBadge ?>">
                                        <?= htmlspecialchars($rName) ?>
                                    </span>
                                    <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars(role_label($rName)) ?></div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $st === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                        <?= htmlspecialchars($st) ?>
                                    </span>
                                </td>
                                <?php if ($canWrite): ?>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <button type="button"
                                            @click='isEdit = true; form = <?= json_encode($editData, JSON_HEX_APOS | JSON_HEX_QUOT) ?>; modalOpen = true'
                                            class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition mr-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form method="post" onsubmit="return confirm('Hapus pengguna ini via API?');" class="inline">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="id_pengguna" value="<?= (int)($row['id_pengguna'] ?? $row['id'] ?? 0) ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-xs font-bold transition">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada data pengguna.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Create / Edit -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-heading font-bold text-lg text-slate-900" x-text="isEdit ? 'Edit Akun Pengguna (PUT API)' : 'Tambah Pengguna Baru (POST API)'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="post" class="mt-4 space-y-4">
                <input type="hidden" name="form_action" :value="isEdit ? 'update' : 'create'">
                <input type="hidden" name="id_pengguna" :value="form.id_pengguna">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Username</label>
                        <input type="text" name="username" required x-model="form.username" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Role (RBAC)</label>
                        <select name="nama_role" x-model="form.nama_role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="super_admin">super_admin</option>
                            <option value="admin">admin</option>
                            <option value="pendaftaran">pendaftaran</option>
                            <option value="dokter">dokter</option>
                            <option value="kasir">kasir</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required x-model="form.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" x-model="form.email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">No. HP</label>
                        <input type="text" name="no_hp" x-model="form.no_hp" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Status</label>
                        <select name="status" x-model="form.status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="aktif">aktif</option>
                            <option value="nonaktif">nonaktif</option>
                            <option value="terkunci">terkunci</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1" x-text="isEdit ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password'"></label>
                    <input type="password" name="password" :required="!isEdit" x-model="form.password" placeholder="Masukkan password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
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
