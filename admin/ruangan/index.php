<?php
session_start();
require_once '../../config/database.php';

$current_page = 'ruangan';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$filter_gedung = $_GET['gedung'] ?? '';
$where_sql = "";
if (!empty($filter_gedung)) {
    $where_sql = "WHERE r.id_gedung = '" . $koneksi->real_escape_string($filter_gedung) . "'";
}

// Ambil data ruangan
$query = "SELECT r.*, g.nama_gedung 
          FROM ruangan r 
          JOIN gedung g ON r.id_gedung = g.id 
          $where_sql
          ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC";
$result = $koneksi->query($query);

// Ambil semua gedung untuk dropdown
$gedung_list = $koneksi->query("SELECT * FROM gedung ORDER BY nama_gedung ASC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Ruangan</h4>
                <p class="text-muted small mb-0">Kelola daftar ruangan, laboratorium, dan fasilitas fisik lainnya.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahRuangan">
                    <i class="fa-solid fa-plus-circle"></i> <span>Tambah Ruangan</span>
                </button>
            </div>
        </div>

        <!-- Filter Gedung -->
        <div class="glass-card p-4 shadow-sm mb-4 border-0">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter Berdasarkan Gedung</label>
                    <select class="form-select" name="gedung" onchange="this.form.submit()">
                        <option value="">Semua Gedung</option>
                        <?php 
                        $gedung_list->data_seek(0);
                        while($g = $gedung_list->fetch_assoc()): ?>
                            <option value="<?= $g['id'] ?>" <?= $filter_gedung == $g['id'] ? 'selected' : '' ?>><?= $g['nama_gedung'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="index.php" class="btn btn-light rounded-pill px-3">Reset Filter</a>
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show glass-effect mb-4 border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #166534;">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive glass-card shadow-sm border-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Ruangan & Kode</th>
                        <th>Lokasi Gedung</th>
                        <th>Penanggung Jawab</th>
                        <th class="text-center">Kapasitas</th>
                        <th class="text-center">Status Pinjam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="cursor-pointer" onclick="openRoomAction(<?= htmlspecialchars(json_encode($row)) ?>)">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_ruangan']) ?></div>
                                <div class="badge bg-light text-muted border py-0 px-2" style="font-size: 0.65rem;"><?= $row['kode_ruangan'] ?></div>
                            </td>
                            <td>
                                <div class="text-dark fw-medium"><?= htmlspecialchars($row['nama_gedung']) ?></div>
                            </td>
                            <td>
                                <?php if($row['penanggung_jawab']): ?>
                                <div class="fw-medium text-dark small"><?= htmlspecialchars($row['penanggung_jawab']) ?></div>
                                <?php if($row['kontak_pj']): ?>
                                <div class="text-muted" style="font-size:0.75rem;"><i class="fa-solid fa-phone me-1 opacity-50"></i><?= htmlspecialchars($row['kontak_pj']) ?></div>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold"><?= $row['kapasitas'] ?></span> <small class="text-muted">Orang</small>
                            </td>
                            <td class="text-center">
                                <?php if($row['bisa_dipinjam'] == 'Y'): ?>
                                    <span class="badge bg-success-soft text-success rounded-pill">🌐 Bisa Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-soft text-muted rounded-pill">🔒 Internal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data ruangan di lokasi ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Flyout Action Ruangan -->
<div class="offcanvas offcanvas-end border-0 shadow-lg glass-effect" tabindex="-1" id="flyoutRuangan" style="width: 380px;">
    <div class="offcanvas-header border-bottom px-4">
        <h5 class="offcanvas-title fw-bold">Opsi Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <div class="text-center mb-4">
            <div class="bg-primary-soft text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                <i class="fa-solid fa-door-open fs-2"></i>
            </div>
            <h4 id="f-nama" class="fw-bold text-dark mb-1">-</h4>
            <p id="f-kode" class="text-muted small">-</p>
        </div>

        <div class="d-grid gap-3">
            <button class="btn btn-primary rounded-pill py-2 fw-bold" onclick="editRoom()">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit Data Ruangan
            </button>
            <button class="btn btn-danger-soft text-danger rounded-pill py-2 fw-bold" onclick="deleteRoom()">
                <i class="fa-solid fa-trash me-2"></i> Hapus Ruangan
            </button>
        </div>
        
        <hr class="my-4 opacity-10">
        <div class="list-group list-group-flush rounded-3 overflow-hidden border mb-3">
            <div class="list-group-item p-3 border-0">
                <div class="small text-muted mb-1 fw-bold text-uppercase" style="font-size:0.65rem;">Penanggung Jawab</div>
                <div id="f-pj" class="fw-bold text-dark small">-</div>
            </div>
            <div class="list-group-item p-3 border-0">
                <div class="small text-muted mb-1 fw-bold text-uppercase" style="font-size:0.65rem;">No. Kontak / HP</div>
                <div id="f-kontak" class="fw-bold text-dark small">-</div>
            </div>
        </div>
        <div class="small fw-bold text-muted mb-2 text-uppercase">Fasilitas & Detail:</div>
        <p id="f-fasilitas" class="text-muted small">-</p>
    </div>
</div>

<!-- Modal Edit Ruangan (Universal) -->
<div class="modal fade" id="modalEditRuangan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Update Data Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST" id="formEditRuangan">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted">Nama Ruangan</label>
                            <input type="text" class="form-control" name="nama_ruangan" id="edit-nama" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Kode Ruangan</label>
                            <input type="text" class="form-control" name="kode_ruangan" id="edit-kode">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Gedung</label>
                            <select class="form-select" name="id_gedung" id="edit-gedung" required>
                                <?php 
                                $gedung_list->data_seek(0);
                                while($g = $gedung_list->fetch_assoc()): ?>
                                    <option value="<?= $g['id'] ?>"><?= $g['nama_gedung'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Kapasitas</label>
                            <input type="number" class="form-control" name="kapasitas" id="edit-kapasitas">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Bisa Dipinjam?</label>
                            <select class="form-select" name="bisa_dipinjam" id="edit-pinjam">
                                <option value="Y">Ya</option>
                                <option value="N">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Penanggung Jawab</label>
                            <input type="text" class="form-control" name="penanggung_jawab" id="edit-pj" placeholder="Nama PJ ruangan...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">No. Kontak / HP</label>
                            <input type="text" class="form-control" name="kontak_pj" id="edit-kontak" placeholder="Contoh: 08123456789">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Fasilitas & Deskripsi</label>
                            <textarea class="form-control" name="fasilitas" id="edit-fasilitas" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Ruangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let activeRoom = null;
function openRoomAction(data) {
    activeRoom = data;
    document.getElementById('f-nama').innerText = data.nama_ruangan;
    document.getElementById('f-kode').innerText = data.kode_ruangan;
    document.getElementById('f-pj').innerText = data.penanggung_jawab || '-';
    document.getElementById('f-kontak').innerText = data.kontak_pj || '-';
    document.getElementById('f-fasilitas').innerText = data.fasilitas || 'Tidak ada keterangan fasilitas.';
    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('flyoutRuangan')).show();
}
function editRoom() {
    if(!activeRoom) return;
    document.getElementById('edit-id').value = activeRoom.id;
    document.getElementById('edit-nama').value = activeRoom.nama_ruangan;
    document.getElementById('edit-kode').value = activeRoom.kode_ruangan;
    document.getElementById('edit-gedung').value = activeRoom.id_gedung;
    document.getElementById('edit-kapasitas').value = activeRoom.kapasitas;
    document.getElementById('edit-pinjam').value = activeRoom.bisa_dipinjam;
    document.getElementById('edit-pj').value = activeRoom.penanggung_jawab || '';
    document.getElementById('edit-kontak').value = activeRoom.kontak_pj || '';
    document.getElementById('edit-fasilitas').value = activeRoom.fasilitas || '';
    
    bootstrap.Offcanvas.getInstance(document.getElementById('flyoutRuangan')).hide();
    setTimeout(() => {
        const m = new bootstrap.Modal(document.getElementById('modalEditRuangan'));
        m.show();
    }, 500);
}
function deleteRoom() {
    if(!activeRoom) return;
    if(confirm('Hapus ruangan ini?')) window.location = 'proses.php?aksi=hapus&id=' + activeRoom.id;
}
</script>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="modalTambahRuangan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title font-heading fw-bold">Tambah Ruangan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted">Nama Ruangan</label>
                            <input type="text" class="form-control" name="nama_ruangan" placeholder="Contoh: Ruang Meeting / Lab Komputer" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Kode Ruangan</label>
                            <input type="text" class="form-control" name="kode_ruangan" placeholder="Contoh: R-101">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Gedung</label>
                            <select class="form-select" name="id_gedung" required>
                                <option value="">Pilih Gedung...</option>
                                <?php 
                                $gedung_list->data_seek(0);
                                while($g = $gedung_list->fetch_assoc()): ?>
                                    <option value="<?= $g['id'] ?>"><?= $g['nama_gedung'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Kapasitas</label>
                            <input type="number" class="form-control" name="kapasitas" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Bisa Dipinjam?</label>
                            <select class="form-select" name="bisa_dipinjam">
                                <option value="Y">🌐 Ya (Publik)</option>
                                <option value="N">🔒 Tidak (Internal)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Penanggung Jawab</label>
                            <input type="text" class="form-control" name="penanggung_jawab" placeholder="Nama PJ ruangan...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">No. Kontak / HP</label>
                            <input type="text" class="form-control" name="kontak_pj" placeholder="Contoh: 08123456789">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Fasilitas & Deskripsi</label>
                            <textarea class="form-control" name="fasilitas" rows="3" placeholder="Jelaskan fasilitas yang ada di ruangan ini..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Simpan Ruangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
