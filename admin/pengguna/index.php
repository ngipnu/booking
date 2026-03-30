<?php
session_start();
require_once '../../config/database.php';

$current_page = 'pengguna';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Kumpulkan semua user ke array
$result  = $koneksi->query("SELECT * FROM users ORDER BY role ASC, id DESC");
$user_list = [];
while ($u = $result->fetch_assoc()) {
    $user_list[] = $u;
}

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Pengguna</h4>
                <p class="text-muted small mb-0">Kelola akun admin dan user peminjam (akun berbagi).</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="fa-solid fa-user-plus"></i> <span>Tambah Akun</span>
            </button>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4 rounded-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['pesan_error'])): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive bg-white rounded-4 shadow-sm border">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light text-muted small text-uppercase" style="letter-spacing:0.5px;">
                        <th class="ps-4 py-3">Username &amp; Nama</th>
                        <th>Status Role</th>
                        <th>Email</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($user_list)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fs-1 opacity-25 d-block mb-3"></i>
                            Belum ada pengguna terdaftar.
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($user_list as $u): ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-card-ico bg-primary-soft text-primary"
                                     style="width:40px;height:40px;border-radius:10px;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($u['nama']) ?></div>
                                    <div class="text-muted small">@<?= htmlspecialchars($u['username'] ?? $u['niy'] ?? '-') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger-soft text-danger rounded-pill px-2">Administrator</span>
                            <?php else: ?>
                                <span class="badge bg-primary-soft text-primary rounded-pill px-2">User / Peminjam</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <!-- Edit -->
                                <button class="btn btn-sm btn-white border shadow-sm rounded-pill px-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit<?= $u['id'] ?>">
                                    <i class="fa-solid fa-user-pen me-1"></i> Edit
                                </button>
                                <!-- Hapus: trigger form luar via JS -->
                                <?php if ($u['id'] != intval($_SESSION['id_user'] ?? 0)): ?>
                                <button type="button"
                                        class="btn btn-sm btn-white border shadow-sm rounded-pill text-danger px-3"
                                        onclick="konfirmasiHapus(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['nama'])) ?>')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-white border shadow-sm rounded-pill text-muted px-3"
                                        disabled title="Tidak bisa hapus akun sendiri">
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ================================================ -->
<!-- FORM HAPUS — Di luar tabel, diisi via JavaScript -->
<!-- ================================================ -->
<form id="formHapus" action="proses.php" method="POST" style="display:none;">
    <input type="hidden" name="aksi" value="hapus">
    <input type="hidden" name="id" id="hapusId" value="">
</form>

<!-- ================================================ -->
<!-- MODAL TAMBAH -->
<!-- ================================================ -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Registrasi Akun Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <p class="text-muted small mb-4">Pastikan data akun sudah divalidasi oleh Div HUBIN / Admin Sekolah.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap / Instansi</label>
                        <input type="text" class="form-control" name="nama" placeholder="Contoh: Unit LRC SDIT" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="user_lrc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Hak Akses</label>
                            <select class="form-select" name="role">
                                <option value="user">User Peminjam</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="email@sekolah.com" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Password Login</label>
                        <input type="password" class="form-control" name="password" placeholder="Min. 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================ -->
<!-- MODAL EDIT (satu per user, di luar tabel) -->
<!-- ================================================ -->
<?php foreach ($user_list as $u): ?>
<div class="modal fade" id="modalEdit<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Modifikasi Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama"
                               value="<?= htmlspecialchars($u['nama']) ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Username</label>
                            <input type="text" class="form-control" name="username"
                                   value="<?= htmlspecialchars($u['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Role</label>
                            <select class="form-select" name="role">
                                <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : '' ?>>User Peminjam</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Email</label>
                        <input type="email" class="form-control" name="email"
                               value="<?= htmlspecialchars($u['email'] ?? '') ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">
                            Password Baru <span class="fw-normal">(Kosongkan jika tidak diganti)</span>
                        </label>
                        <input type="password" class="form-control" name="password" placeholder="••••••••">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
function konfirmasiHapus(id, nama) {
    if (confirm('Hapus akun "' + nama + '" secara permanen?\n\nData peminjaman terkait juga akan ikut terhapus.')) {
        document.getElementById('hapusId').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>

<?php include '../layouts/footer.php'; ?>
