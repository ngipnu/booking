<?php
session_start();
require_once '../../config/database.php';

$current_page = 'kategori';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$kumpulan_kategori = $koneksi->query("SELECT * FROM kategori ORDER BY id DESC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 text-start">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Kelola Kategori Aset</h4>
                <p class="text-muted small mb-0">Atur pengelompokan aset sarana prasarana.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahKat">
                <i class="fa-solid fa-plus-circle me-1"></i> Baru
            </button>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4"><?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?></div>
        <?php endif; ?>

        <!-- Categories Table (Clean List Style) -->
        <div class="table-responsive bg-white rounded-4 shadow-sm border border-white-50 animate-fade-up">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-50">
                        <th class="ps-4 py-3">Ikon & Nama Kategori</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($kumpulan_kategori->num_rows > 0): ?>
                        <?php while ($kat = $kumpulan_kategori->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-card-ico bg-primary-soft text-primary" style="width: 40px; height: 40px; border-radius: 10px;">
                                        <i class="fa-solid fa-<?= htmlspecialchars($kat['icon']) ?>"></i>
                                    </div>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($kat['nama_kategori']) ?></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php 
                                $id_kat = $kat['id'];
                                $jml = $koneksi->query("SELECT id FROM aset WHERE id_kategori = $id_kat")->num_rows;
                                ?>
                                <span class="badge bg-light text-muted border rounded-pill px-3"><?= $jml ?> Item</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                    <button class="btn btn-sm btn-white px-3" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $kat['id'] ?>"><i class="fa-solid fa-pen-to-square text-muted"></i></button>
                                    <a href="proses.php?aksi=hapus&id=<?= $kat['id'] ?>" class="btn btn-sm btn-white px-3" onclick="return confirm('Hapus kategori ini? Semua aset dalam kategori ini akan kehilangan relasi kategorinya.')"><i class="fa-solid fa-trash text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        $list_kategori_data[] = $kat;
                        endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center py-5 text-muted">Belum ada kategori yang ditambahkan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab-container d-md-none">
        <button class="fab-btn" data-bs-toggle="modal" data-bs-target="#modalTambahKat">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>
</main>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahKat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" placeholder="e.g. Alat Musik" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Ikon (FontAwesome Name)</label>
                        <input type="text" class="form-control" name="icon" placeholder="e.g. guitar" value="box">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($list_kategori_data)): foreach($list_kategori_data as $kat): ?>
<!-- Modal Edit -->
<div class="modal fade" id="modalEdit<?= $kat['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" value="<?= $kat['id'] ?>">
                <div class="modal-body px-4">
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" value="<?= htmlspecialchars($kat['nama_kategori']) ?>" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Ikon (FontAwesome)</label>
                        <input type="text" class="form-control" name="icon" value="<?= htmlspecialchars($kat['icon']) ?>" required>
                        <div class="form-text small text-muted">Contoh: car, laptop, chair</div>
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
