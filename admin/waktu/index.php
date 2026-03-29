<?php
session_start();
require_once '../../config/database.php';

$current_page = 'waktu';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$kumpulan_waktu = $koneksi->query("SELECT * FROM waktu ORDER BY urutan ASC, jam_mulai ASC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 text-start">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Kelola Sesi Waktu</h4>
                <p class="text-muted small mb-0">Atur pembagian jam pelajaran atau sesi peminjaman aset.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahWaktu">
                <i class="fa-solid fa-plus-circle me-1"></i> Sesi Baru
            </button>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4"><?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?></div>
        <?php endif; ?>

        <!-- Waktu Table -->
        <div class="table-responsive bg-white rounded-4 shadow-sm border border-white-50 animate-fade-up">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-50">
                        <th class="ps-4 py-3">Nama Sesi / Jam</th>
                        <th class="text-center">Waktu (Mulai - Selesai)</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($kumpulan_waktu->num_rows > 0): ?>
                        <?php while ($wkt = $kumpulan_waktu->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-card-ico bg-info-soft text-info" style="width: 40px; height: 40px; border-radius: 10px;">
                                        <i class="fa-solid fa-clock"></i>
                                    </div>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($wkt['nama_waktu']) ?></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary border rounded-pill px-3 fw-bold">
                                    <?= date('H:i', strtotime($wkt['jam_mulai'])) ?> - <?= date('H:i', strtotime($wkt['jam_selesai'])) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small"><?= $wkt['urutan'] ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                    <button class="btn btn-sm btn-white px-3" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $wkt['id'] ?>"><i class="fa-solid fa-pen-to-square text-muted"></i></button>
                                    <a href="proses.php?aksi=hapus&id=<?= $wkt['id'] ?>" class="btn btn-sm btn-white px-3" onclick="return confirm('Hapus sesi waktu ini?')"><i class="fa-solid fa-trash text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        $list_waktu_data[] = $wkt;
                        endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada sesi waktu yang ditambahkan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab-container d-md-none">
        <button class="fab-btn" data-bs-toggle="modal" data-bs-target="#modalTambahWaktu">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>
</main>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahWaktu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Sesi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Sesi</label>
                        <input type="text" class="form-control" name="nama_waktu" placeholder="e.g. Jam ke-1" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Jam Mulai</label>
                            <input type="time" class="form-control" name="jam_mulai" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Jam Selesai</label>
                            <input type="time" class="form-control" name="jam_selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Urutan</label>
                        <input type="number" class="form-control" name="urutan" value="0">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($list_waktu_data)): foreach($list_waktu_data as $wkt): ?>
<!-- Modal Edit -->
<div class="modal fade" id="modalEdit<?= $wkt['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Edit Sesi Waktu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" value="<?= $wkt['id'] ?>">
                <div class="modal-body text-start px-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Sesi</label>
                        <input type="text" class="form-control" name="nama_waktu" value="<?= htmlspecialchars($wkt['nama_waktu']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Jam Mulai</label>
                            <input type="time" class="form-control" name="jam_mulai" value="<?= $wkt['jam_mulai'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Jam Selesai</label>
                            <input type="time" class="form-control" name="jam_selesai" value="<?= $wkt['jam_selesai'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Urutan Tampil</label>
                        <input type="number" class="form-control" name="urutan" value="<?= $wkt['urutan'] ?>">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; endif; ?>

<?php include '../layouts/footer.php'; ?>
