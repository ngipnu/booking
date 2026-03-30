<?php
session_start();
require_once '../../config/database.php';

$current_page = 'dashboard';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Menghitung Metrik Aset
$total_aset = $koneksi->query("SELECT id FROM aset")->num_rows;

$q_nilai = $koneksi->query("SELECT SUM(harga_beli) as total_nilai FROM aset");
$row_nilai = $q_nilai->fetch_assoc();
$total_nilai = $row_nilai['total_nilai'] ? $row_nilai['total_nilai'] : 0;

$aset_dipinjam = $koneksi->query("SELECT id FROM aset WHERE status = 'dipinjam'")->num_rows;
$aset_bisa_dipinjam = $koneksi->query("SELECT id FROM aset WHERE bisa_dipinjam = 'Y'")->num_rows;
$aset_inventaris_saja = $koneksi->query("SELECT id FROM aset WHERE bisa_dipinjam = 'N'")->num_rows;

// Mengambil Data Peminjaman Aktif (Limit 5 untuk Dashboard)
$q_peminjam_aktif = "SELECT p.id, u.nama, a.nama_aset, p.tgl_pinjam, p.tgl_kembali, p.status_pinjam 
                     FROM peminjaman p 
                     JOIN users u ON p.id_user = u.id 
                     JOIN aset a ON p.id_aset = a.id 
                     WHERE p.status_pinjam IN ('menunggu', 'disetujui') 
                     ORDER BY p.tgl_pengajuan DESC LIMIT 5";
$peminjam_aktif = $koneksi->query($q_peminjam_aktif);

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<!-- Main Content wrapper starts -->
<main class="main-content">
    
    <?php include '../layouts/topbar.php'; ?>

    <!-- Dashboard Content -->
    <div class="px-3 px-md-4 pb-5">
        
        <!-- Valuation Banner (Non-Card) -->
        <div class="valuation-banner animate-fade-up">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-coins fs-4"></i>
                </div>
                <div>
                    <span class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Total Valuasi Aset Lembaga</span>
                    <h3 class="font-heading fw-bold mb-0 lh-1 mt-1 text-white">Rp <?= number_format($total_nilai, 0, ',', '.') ?></h3>
                </div>
            </div>
            <a href="../aset/index.php?showModal=tambah" class="btn btn-white rounded-pill px-4 shadow-sm fw-bold border-0" style="background: white; color: var(--primary-color);">
                <i class="fa-solid fa-plus-circle me-1"></i> Aset Baru
            </a>
        </div>

        <div class="row-kpi row g-3 g-lg-4 mb-4">
            <!-- Total Aset -->
            <div class="col-6 col-md-3 animate-fade-up show">
                <div class="glass-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="fw-medium text-muted text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">Aset Keseluruhan</span>
                        <div class="mt-2 d-flex align-items-end justify-content-between">
                            <h2 class="font-heading fw-bold mb-0 text-dark"><?= $total_aset ?></h2>
                            <div class="stat-card-ico bg-primary-soft text-primary"><i class="fa-solid fa-boxes-stacked"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Inventaris Internal -->
            <div class="col-6 col-md-3 animate-fade-up show" style="animation-delay: 0.1s;">
                <div class="glass-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="fw-medium text-muted text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">Internal (Internal)</span>
                        <div class="mt-2 d-flex align-items-end justify-content-between">
                            <h2 class="font-heading fw-bold mb-0 text-dark"><?= $aset_inventaris_saja ?></h2>
                            <div class="stat-card-ico bg-danger-soft text-danger"><i class="fa-solid fa-lock"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bisa Dipinjam -->
            <div class="col-6 col-md-3 animate-fade-up show" style="animation-delay: 0.2s;">
                <div class="glass-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="fw-medium text-muted text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">Bisa Dipinjam</span>
                        <div class="mt-2 d-flex align-items-end justify-content-between">
                            <h2 class="font-heading fw-bold mb-0 text-dark"><?= $aset_bisa_dipinjam ?></h2>
                            <div class="stat-card-ico bg-info-soft text-info"><i class="fa-solid fa-hand-holding-hand"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sedang Digunakan -->
            <div class="col-6 col-md-3 animate-fade-up show" style="animation-delay: 0.3s;">
                <div class="glass-card h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="fw-medium text-muted text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">Sedang Dipinjam</span>
                        <div class="mt-2 d-flex align-items-end justify-content-between">
                            <h2 class="font-heading fw-bold mb-0 text-dark"><?= $aset_dipinjam ?></h2>
                            <div class="stat-card-ico bg-warning-soft text-warning"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Tabel List Peminjam -->
            <div class="col-12 animate-fade-up show" style="animation-delay: 0.5s;">
                <div class="glass-card">
                    <div class="card-header bg-transparent border-bottom-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="font-heading fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Pengguna yang Sedang Meminjam</h6>
                        <a href="../peminjaman/index.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Semua Peminjaman</a>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-top mb-0 table-glass">
                                <thead class="text-muted small text-uppercase" style="letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="py-3 px-3">Nama Pegawai</th>
                                        <th class="py-3 px-3">Aset / Fasilitas</th>
                                        <th class="py-3 px-3">Tgl Peminjaman</th>
                                        <th class="py-3 px-3">Status</th>
                                        <th class="py-3 px-3 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($peminjam_aktif && $peminjam_aktif->num_rows > 0): ?>
                                        <?php while ($row = $peminjam_aktif->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-3 fw-medium text-dark">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        <i class="fa-solid fa-user"></i>
                                                    </div>
                                                    <?= htmlspecialchars($row['nama']) ?>
                                                </div>
                                            </td>
                                            <td class="px-3"><?= htmlspecialchars($row['nama_aset']) ?></td>
                                            <td class="px-3 text-muted small">
                                                <?= date('d M', strtotime($row['tgl_pinjam'])) ?> s/d <?= date('d M Y', strtotime($row['tgl_kembali'])) ?>
                                            </td>
                                            <td class="px-3">
                                                <?php if ($row['status_pinjam'] == 'menunggu'): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-medium"><i class="fa-solid fa-clock me-1"></i> Menunggu</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success px-2 py-1 rounded-pill fw-medium"><i class="fa-solid fa-check me-1"></i> Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 text-end">
                                                <button class="btn btn-sm btn-light border shadow-sm rounded-pill"><i class="fa-solid fa-eye text-muted"></i></button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <div class="fs-1 text-light mb-3"><i class="fa-solid fa-clipboard-check text-muted opacity-25"></i></div>
                                                <span class="d-block fw-medium text-dark">Tidak ada peminjaman aktif</span>
                                                <span class="small">Semua aset sarpras terpantau aman.</span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php include '../layouts/footer.php'; ?>
