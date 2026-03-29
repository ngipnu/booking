<?php
session_start();
require_once '../../config/database.php';

$current_page = 'gedung';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil data gedung
$query = "SELECT * FROM gedung ORDER BY nama_gedung ASC";
$result = $koneksi->query($query);

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Gedung</h4>
                <p class="text-muted small mb-0">Kelola daftar gedung utama di lingkungan lembaga.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahGedung">
                    <i class="fa-solid fa-plus-circle"></i> <span>Tambah Gedung</span>
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show glass-effect mb-4 border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #166534;">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                    $id_g = $row['id'];
                    $jml_ruangan = $koneksi->query("SELECT COUNT(*) as total FROM ruangan WHERE id_gedung = $id_g")->fetch_assoc()['total'];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100 hover-zoom position-relative cursor-pointer" onclick="openGedungAction(<?= htmlspecialchars(json_encode($row)) ?>)">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-building fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_gedung']) ?></h5>
                                <span class="badge bg-light text-muted border"><?= $jml_ruangan ?> Ruangan</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($row['deskripsi'] ?: 'Tidak ada deskripsi gedung.') ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-city d-block fs-1 mb-3 opacity-25"></i>
                    Belum ada data gedung. Silakan tambah gedung baru.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Flyout Action Gedung -->
<div class="offcanvas offcanvas-end border-0 shadow-lg glass-effect" tabindex="-1" id="flyoutGedung" style="width: 380px;">
    <div class="offcanvas-header border-bottom px-4">
        <h5 class="offcanvas-title fw-bold">Opsi Gedung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <div class="text-center mb-4">
            <div class="bg-primary-soft text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                <i class="fa-solid fa-building-circle-check fs-2"></i>
            </div>
            <h4 id="g-nama" class="fw-bold text-dark mb-1">-</h4>
            <div class="badge bg-light text-muted border mb-3">Gedung Utama</div>
        </div>

        <div class="d-grid gap-3">
            <a id="g-link-ruangan" href="#" class="btn btn-outline-primary rounded-pill py-2 fw-bold">
                <i class="fa-solid fa-door-open me-2"></i> Lihat Daftar Ruangan
            </a>
            <button class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm" onclick="editGedung()">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit Data Gedung
            </button>
            <button class="btn btn-danger-soft text-danger rounded-pill py-2 fw-bold" onclick="deleteGedung()">
                <i class="fa-solid fa-trash me-2"></i> Hapus Gedung
            </button>
        </div>
        
        <hr class="my-4 opacity-10">
        <div class="small fw-bold text-muted mb-2 text-uppercase">Deskripsi / Alamat:</div>
        <p id="g-deskripsi" class="text-muted small">-</p>
    </div>
</div>

<!-- Modal Edit Gedung (Universal) -->
<div class="modal fade" id="modalEditGedung" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Update Data Gedung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST" id="formEditGedung">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Gedung</label>
                        <input type="text" class="form-control" name="nama_gedung" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Deskripsi / Lokasi</label>
                        <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Gedung</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let activeGedung = null;
function openGedungAction(data) {
    activeGedung = data;
    document.getElementById('g-nama').innerText = data.nama_gedung;
    document.getElementById('g-deskripsi').innerText = data.deskripsi || '-';
    document.getElementById('g-link-ruangan').href = '../ruangan/index.php?gedung=' + data.id;
    new bootstrap.Offcanvas(document.getElementById('flyoutGedung')).show();
}
function editGedung() {
    if(!activeGedung) return;
    document.getElementById('edit-id').value = activeGedung.id;
    document.getElementById('edit-nama').value = activeGedung.nama_gedung;
    document.getElementById('edit-deskripsi').value = activeGedung.deskripsi;
    
    bootstrap.Offcanvas.getInstance(document.getElementById('flyoutGedung')).hide();
    setTimeout(() => new bootstrap.Modal(document.getElementById('modalEditGedung')).show(), 500);
}
function deleteGedung() {
    if(!activeGedung) return;
    if(confirm('Hapus gedung ini dan semua ruangan di dalamnya?')) window.location = 'proses.php?aksi=hapus&id=' + activeGedung.id;
}
</script>

<!-- Modal Tambah Gedung -->
<div class="modal fade" id="modalTambahGedung" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title font-heading fw-bold">Daftarkan Gedung Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Gedung</label>
                        <input type="text" class="form-control" name="nama_gedung" placeholder="Contoh: Gedung A / Kampus Utama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Deskripsi / Lokasi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Jelaskan deskripsi atau alamat gedung..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Simpan Gedung</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hover-zoom { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.hover-zoom:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important; z-index: 10; cursor: pointer; }
</style>

<?php include '../layouts/footer.php'; ?>
