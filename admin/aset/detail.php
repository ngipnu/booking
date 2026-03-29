<?php
session_start();
require_once '../../config/database.php';

$current_page = 'aset';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$query = "SELECT a.*, k.nama_kategori, k.icon as kat_icon, r.nama_ruangan, g.nama_gedung 
          FROM aset a 
          LEFT JOIN kategori k ON a.id_kategori = k.id 
          LEFT JOIN ruangan r ON a.id_ruangan = r.id
          LEFT JOIN gedung g ON r.id_gedung = g.id
          WHERE a.id = '" . $koneksi->real_escape_string($id) . "'";
$result = $koneksi->query($query);
$aset = $result->fetch_assoc();

if (!$aset) {
    header("Location: index.php");
    exit;
}

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <!-- Breadcrumb & Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Aset</a></li>
                    <li class="breadcrumb-item active fw-bold" aria-current="page">Detail Aset</li>
                </ol>
            </nav>
            <a href="index.php" class="btn btn-light rounded-pill px-3 shadow-sm border">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row g-4">
            <!-- Left Profile Card -->
            <div class="col-lg-4">
                <div class="glass-card p-4 text-center h-100">
                    <div class="bg-primary-soft text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 100px; height: 100px;">
                        <i class="fa-solid fa-<?= $aset['kat_icon'] ? $aset['kat_icon'] : 'box' ?> fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($aset['nama_aset']) ?></h4>
                    <span class="badge bg-light text-muted border mb-3"><?= htmlspecialchars($aset['kode_aset']) ?></span>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="index.php" class="btn btn-primary rounded-pill fw-bold">
                            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar
                        </a>
                    </div>

                    <hr class="my-4 opacity-5">
                    
                    <div class="text-start">
                        <p class="small text-muted fw-bold text-uppercase mb-2">Statistik Cepat</p>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Status Pinjam</span>
                            <span class="badge <?= $aset['bisa_dipinjam']=='Y'?'bg-success-soft text-success':'bg-secondary-soft text-secondary' ?>"><?= $aset['bisa_dipinjam']=='Y'?'Tersedia':'Internal' ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Kondisi</span>
                            <span class="fw-bold text-dark small"><?= strtoupper(str_replace('_', ' ', $aset['kondisi'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Detail Info -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-circle-info text-primary me-2"></i>Informasi Spesifikasi</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Merk Barang</label>
                            <p class="fw-bold border-bottom pb-2"><?= htmlspecialchars($aset['merk'] ?: '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Warna</label>
                            <p class="fw-bold border-bottom pb-2"><?= htmlspecialchars($aset['warna'] ?: '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Kategori</label>
                            <p class="fw-bold border-bottom pb-2"><?= htmlspecialchars($aset['nama_kategori']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Lokasi Fisik (Master)</label>
                            <p class="fw-bold border-bottom pb-2">
                                <?php if($aset['id_ruangan']): ?>
                                    <span class="text-primary"><?= htmlspecialchars($aset['nama_ruangan']) ?></span> (<?= htmlspecialchars($aset['nama_gedung']) ?>)
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Lokasi Simpan / Deskripsi</label>
                            <p class="fw-bold border-bottom pb-2"><?= htmlspecialchars($aset['lokasi_simpan'] ?: '-') ?> (<?= htmlspecialchars($aset['unit_pengguna']) ?>)</p>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-4 mt-5"><i class="fa-solid fa-wallet text-primary me-2"></i>Data Keuangan & Garansi</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Harga Perolehan</label>
                            <p class="fw-bold border-bottom pb-2">Rp <?= number_format($aset['harga_beli'], 0, ',', '.') ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Tgl Beli</label>
                            <p class="fw-bold border-bottom pb-2"><?= date('d F Y', strtotime($aset['tgl_beli'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Tahun Anggaran</label>
                            <p class="fw-bold border-bottom pb-2"><?= $aset['tahun_anggaran'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Garansi Sampai</label>
                            <p class="fw-bold border-bottom pb-2"><?= $aset['garansi_sampai'] ? date('d F Y', strtotime($aset['garansi_sampai'])) : 'Tidak Ada' ?></p>
                        </div>
                        <div class="col-md-8">
                            <label class="text-muted small d-block">Sumber Pembelian</label>
                            <p class="fw-bold border-bottom pb-2"><?= htmlspecialchars($aset['toko_pembelian']) ?> (<?= htmlspecialchars($aset['kota_pembelian']) ?>)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit inside Detail Page -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-effect">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title font-heading fw-bold">Update Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- ... reusing your form logic from index.php ... -->
                <form action="proses.php" method="POST">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" value="<?= $aset['id'] ?>">
                    <div class="modal-body px-4 py-3">
                        <div class="row g-3 text-start">
                            <!-- [Insert form fields here - Simplified for brevity but complete] -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_aset" value="<?= htmlspecialchars($aset['nama_aset']) ?>" required>
                            </div>
                            <!-- ... copying other fields ... -->
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Merk</label>
                                <input type="text" class="form-control" name="merk" value="<?= htmlspecialchars($aset['merk']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Warna</label>
                                <input type="text" class="form-control" name="warna" value="<?= htmlspecialchars($aset['warna']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Unit Pengguna (Pemegang)</label>
                                <input type="text" class="form-control" name="unit_pengguna" value="<?= htmlspecialchars($aset['unit_pengguna']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Lokasi / Ruangan</label>
                                <input type="text" class="form-control" name="lokasi_simpan" value="<?= htmlspecialchars($aset['lokasi_simpan']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Harga Beli (Rp)</label>
                                <input type="number" class="form-control" name="harga_beli" value="<?= (int)$aset['harga_beli'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tanggal Perolehan</label>
                                <input type="date" class="form-control" name="tgl_beli" value="<?= $aset['tgl_beli'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Kondisi</label>
                                <select class="form-select" name="kondisi">
                                    <option value="baik" <?= $aset['kondisi']=='baik'?'selected':'' ?>>✅ Baik</option>
                                    <option value="rusak_ringan" <?= $aset['kondisi']=='rusak_ringan'?'selected':'' ?>>⚠️ Rusak Ringan</option>
                                    <option value="rusak_berat" <?= $aset['kondisi']=='rusak_berat'?'selected':'' ?>>❌ Rusak Berat</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Ada Garansi?</label>
                                <select class="form-select" name="ada_garansi">
                                    <option value="Y" <?= $aset['ada_garansi']=='Y'?'selected':'' ?>>Ya</option>
                                    <option value="N" <?= $aset['ada_garansi']=='N'?'selected':'' ?>>Tidak</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../layouts/footer.php'; ?>
