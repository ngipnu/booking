<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'pegawai' && $_SESSION['role'] !== 'user')) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['nama_pemakai']) || !isset($_SESSION['unit_pemakai'])) {
    header("Location: identifikasi.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_pemakai = $_SESSION['nama_pemakai'];
$unit_pemakai = $_SESSION['unit_pemakai'];

// Statistik
$total_aset = $koneksi->query("SELECT COUNT(*) as total FROM aset WHERE bisa_dipinjam = 'Y'")->fetch_assoc()['total'];
$total_pinjam = $koneksi->query("SELECT COUNT(*) as total FROM peminjaman WHERE tgl_pinjam = '".date('Y-m-d')."' AND status_pinjam = 'disetujui'")->fetch_assoc()['total'];
$my_pending = $koneksi->query("SELECT COUNT(*) as total FROM peminjaman WHERE id_user = $user_id AND status_pinjam = 'menunggu'")->fetch_assoc()['total'];

// Kategori
$categories = $koneksi->query("SELECT * FROM kategori");

// Pinjaman Saya
$my_active = $koneksi->query("SELECT p.*, a.nama_aset, k.nama_kategori, k.icon as kat_icon 
                                FROM peminjaman p 
                                JOIN aset a ON p.id_aset = a.id 
                                LEFT JOIN kategori k ON a.id_kategori = k.id
                                WHERE p.id_user = $user_id 
                                ORDER BY p.tgl_pinjam DESC, p.jam_mulai DESC LIMIT 5");
$page_title = 'Beranda';
include 'layouts/header.php'; 
?>

<style>
    .hero-banner {
        background: var(--primary-gradient) !important;
        border-radius: 24px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .hero-banner::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .category-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        text-align: center;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
        height: 100%;
    }
    .btn-pinjam-lg {
        background: white;
        color: var(--primary-color) !important;
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
</style>

    <div class="container-fluid max-width-1400" style="margin-top: 100px; padding-bottom: 50px;">
        
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Hero Section -->
                <div class="hero-banner animate-fade-up">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h2 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($nama_pemakai) ?>! 👋</h2>
                            <p class="opacity-90 mb-4">Anda login menggunakan unit <b><?= htmlspecialchars($unit_pemakai) ?></b>. Cek ketersediaan dan ajukan peminjaman dengan mudah dalam hitungan detik.</p>
                            <a href="peminjaman.php" class="btn-pinjam-lg">
                                <i class="fa-solid fa-calendar-plus me-2"></i> Ajukan Pinjaman Baru
                            </a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-end">
                            <img src="https://cdni.iconscout.com/illustration/premium/thumb/checking-office-inventory-illustration-download-in-svg-png-gif-formats--inventory-management-taking-stock-warehouse-operations-pack-people-illustrations-5219213.png" alt="Hero" class="img-fluid" style="max-height: 200px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));">
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-3 mb-4 animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="text-muted small fw-bold text-uppercase mb-1">Aset Tersedia</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $total_aset ?></div>
                            <div class="text-success small mt-1"><i class="fa-solid fa-check-circle me-1"></i> Siap Dipinjam</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="text-muted small fw-bold text-uppercase mb-1">Dipakai Hari Ini</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $total_pinjam ?></div>
                            <div class="text-primary small mt-1"><i class="fa-solid fa-clock me-1"></i> Jadwal Aktif</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="text-muted small fw-bold text-uppercase mb-1">Ajuan Pending Saya</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $my_pending ?></div>
                            <div class="text-warning small mt-1"><i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Admin</div>
                        </div>
                    </div>
                </div>

                <!-- Categories Grid -->
                <h6 class="fw-bold text-dark mb-3 animate-fade-up" style="animation-delay: 0.2s;">Kategori Inventaris</h6>
                <div class="row g-3 animate-fade-up" style="animation-delay: 0.25s;">
                    <?php while($cat = $categories->fetch_assoc()): ?>
                    <div class="col-6 col-md-3">
                        <a href="peminjaman.php?cat=<?= $cat['id'] ?>" class="category-card">
                            <i class="fa-solid fa-<?= $cat['icon'] ?: 'box' ?>"></i>
                            <h6 class="fw-bold text-dark mb-0 small"><?= $cat['nama_kategori'] ?></h6>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Activity Section -->
                <div class="glass-card p-4 shadow-sm animate-fade-up h-100" style="animation-delay: 0.3s;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0">Aktivitas Pinjam Saya</h6>
                        <a href="peminjaman.php" class="text-success small fw-bold text-decoration-none">Lihat Semua</a>
                    </div>
                    
                    <?php if ($my_active->num_rows > 0): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php while ($ma = $my_active->fetch_assoc()): ?>
                                <div class="p-3 bg-light rounded-4 border border-white position-relative">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="bg-white text-success rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                            <i class="fa-solid fa-<?= $ma['kat_icon'] ?: 'box' ?> small"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($ma['nama_aset']) ?></div>
                                            <div class="text-muted" style="font-size: 0.65rem;"><?= htmlspecialchars($ma['nama_kategori']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-muted mb-2 ps-1" style="font-size: 0.7rem;">
                                        <i class="fa-regular fa-calendar me-1"></i> <?= date('d M Y', strtotime($ma['tgl_pinjam'])) ?>
                                        <span class="mx-1 opacity-25">|</span>
                                        <i class="fa-regular fa-clock me-1"></i> <?= substr($ma['jam_mulai'], 0, 5) ?> - <?= substr($ma['jam_selesai'], 0, 5) ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                        <?php if($ma['status_pinjam'] == 'menunggu'): ?>
                                            <span class="badge bg-warning-soft text-warning rounded-pill px-2 py-1" style="font-size: 0.6rem;">Menunggu Konfirmasi</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-soft text-success rounded-pill px-2 py-1" style="font-size: 0.6rem;">Disetujui</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($ma['nama_peminjam'] == $nama_pemakai && $ma['unit_peminjam'] == $unit_pemakai): ?>
                                        <a href="proses_pinjam.php?aksi=batal&id=<?= $ma['id'] ?>" class="text-danger small fw-bold text-decoration-none" onclick="return confirm('Batalkan pengajuan ini?')">
                                            <i class="fa-solid fa-trash-can me-1" style="font-size: 0.6rem;"></i> Batal
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small" style="font-size: 0.6rem;"><i class="fa-solid fa-lock me-1"></i> Milik <?= htmlspecialchars($ma['nama_peminjam'] ?: 'Unit Lain') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="https://cdni.iconscout.com/illustration/premium/thumb/no-data-found-illustration-download-in-svg-png-gif-formats--missing-not-available-file-search-empty-state-pack-user-interface-illustrations-5218443.png" alt="Empty" class="img-fluid mb-3" style="max-height: 120px; opacity: 0.5;">
                            <p class="text-muted small">Belum ada riwayat peminjaman.</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 p-3 bg-success-soft rounded-4 border border-success-subtle">
                        <p class="small text-success mb-2 fw-bold"><i class="fa-solid fa-headset me-2"></i> Bantuan Sarpras</p>
                        <p class="text-muted mb-0" style="font-size: 0.65rem;">Mengalami kendala saat peminjaman? Hubungi operator di unit masing-masing atau IT Pusat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
