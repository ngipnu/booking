<?php
// Aplikasi Booking Fasilitas - Annadzir Islamic School
// Landing Page
session_start();

// Ambil data aset + ruangan yang bisa dipinjam untuk landing page
require_once 'config/database.php';
$is_logged_in  = isset($_SESSION['login']) && $_SESSION['login'] === true;
$user_role     = $_SESSION['role'] ?? '';
$is_user       = in_array($user_role, ['user', 'pegawai']);

$sql_landing = "
    SELECT a.id, a.kode_aset as kode, a.nama_aset as nama, a.status, a.foto_aset, 
           a.merk, a.lokasi_simpan, a.unit_pengguna,
           k.nama_kategori, k.icon as kat_icon, 'aset' as tipe, a.created_at,
           NULL as kapasitas,
           MIN(CASE WHEN p.status_pinjam IN ('disetujui','menunggu') AND p.tgl_pinjam = CURDATE() THEN p.jam_mulai END) as jam_mulai_aktif,
           MAX(CASE WHEN p.status_pinjam IN ('disetujui','menunggu') AND p.tgl_pinjam = CURDATE() THEN p.jam_selesai END) as jam_selesai_aktif
    FROM aset a 
    LEFT JOIN kategori k ON a.id_kategori = k.id
    LEFT JOIN peminjaman p ON p.id_aset = a.id AND p.status_pinjam IN ('disetujui','menunggu') AND p.tgl_pinjam = CURDATE()
    WHERE a.bisa_dipinjam = 'Y' AND a.status != 'rusak'
    GROUP BY a.id, a.kode_aset, a.nama_aset, a.status, a.foto_aset, a.merk, a.lokasi_simpan, a.unit_pengguna, k.nama_kategori, k.icon, a.created_at

    UNION ALL
    
    SELECT r.id, r.kode_ruangan as kode, r.nama_ruangan as nama, r.status, 
           r.foto_ruangan as foto_aset,
           NULL as merk, g.nama_gedung as lokasi_simpan, NULL as unit_pengguna,
           'Ruangan' as nama_kategori, 'building' as kat_icon, 'ruangan' as tipe, r.created_at,
           r.kapasitas,
           MIN(CASE WHEN p.status_pinjam IN ('disetujui','menunggu') AND p.tgl_pinjam = CURDATE() THEN p.jam_mulai END) as jam_mulai_aktif,
           MAX(CASE WHEN p.status_pinjam IN ('disetujui','menunggu') AND p.tgl_pinjam = CURDATE() THEN p.jam_selesai END) as jam_selesai_aktif
    FROM ruangan r
    LEFT JOIN gedung g ON r.id_gedung = g.id
    LEFT JOIN peminjaman p ON p.id_ruangan = r.id AND p.status_pinjam IN ('disetujui','menunggu') AND p.tgl_pinjam = CURDATE()
    WHERE r.bisa_dipinjam = 'Y' AND r.status != 'perbaikan'
    GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.status, r.foto_ruangan, g.nama_gedung, r.created_at, r.kapasitas
    
    ORDER BY foto_aset DESC, created_at DESC
    LIMIT 6";
$aset_landing = $koneksi->query($sql_landing);

// Ambil tema dari profil lembaga agar sinkron
$profil_tema = $koneksi->query("SELECT sidebar_gradient FROM profil_lembaga LIMIT 1")->fetch_assoc();
$grad = $profil_tema['sidebar_gradient'] ?? 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
preg_match('/(#[a-f0-9]{3,6}|rgba?\([^)]+\))/i', $grad, $matches);
$primary_color = isset($matches[0]) ? $matches[0] : '#3b82f6';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Fasilitas | Annadzir Islamic School</title>
    
    <!-- Meta Tags for SEO -->
    <meta name="description" content="Sistem Informasi Peminjaman Fasilitas Annadzir Islamic School. Mengelola peminjaman operasional dan alat.">
    
    <!-- Bootstrap 5 CSS (Lokal) -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo/logo_round.png?v=1">
    
    <!-- Font Awesome (Lokal) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Dynamic Theme Sync -->
    <style>
        :root {
            --primary-color: <?= $primary_color ?>;
            --primary-gradient: <?= $grad ?>;
            /* Adaptive backgrounds to make the design 'senada' (synchronized hue) */
            --bg-soft: color-mix(in srgb, var(--primary-color) 8%, #ffffff);
            --bg-main: color-mix(in srgb, var(--primary-color) 3%, #ffffff);
        }
        /* Override primary classes to match dashboard theme */
        .text-primary { color: var(--primary-color) !important; }
        .bg-primary { background: var(--primary-gradient) !important; border: none; }
        .border-primary { border-color: var(--primary-color) !important; }
        
        .btn-primary { background: var(--primary-gradient) !important; border: none; color: white !important; transition: all 0.3s ease; }
        .btn-primary:hover { filter: brightness(0.9); transform: translateY(-2px); box-shadow: 0 10px 20px -10px var(--primary-color) !important; }
        
        .btn-outline-primary { color: var(--primary-color) !important; border-color: var(--primary-color) !important; background: transparent; transition: all 0.3s ease; }
        .btn-outline-primary:hover { background: var(--primary-gradient) !important; border-color: transparent !important; color: white !important; transform: translateY(-2px); box-shadow: 0 10px 20px -10px var(--primary-color) !important; }
        
        .bg-primary-soft { background-color: <?= $primary_color ?>20 !important; }
        
        /* Specific adjustments */
        .timeline-step .step-number.bg-white { border: 2px solid var(--primary-color) !important; }
        .timeline-step .step-number.bg-white.text-primary { color: var(--primary-color) !important; }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#navbar" data-bs-offset="100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm py-3 bg-white transition-all" id="navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="assets/logo/logo_round.png?v=1" alt="Logo Annadzir" style="width: 45px; height: 45px; object-fit: contain;">
                <div class="d-flex flex-column lh-1">
                    <span class="font-heading fw-bold text-dark fs-5">Sarana & Prasarana</span>
                    <span class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Manajemen Aset</span>
                </div>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <i class="fa-solid fa-bars fs-4 text-primary"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium">
                    <li class="nav-item"><a class="nav-link text-dark px-3" href="#beranda">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link text-dark px-3" href="#fasilitas">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link text-dark px-3" href="#prosedur">Prosedur</a></li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-lock"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section text-center position-relative" id="beranda" style="padding-top: 140px; padding-bottom: 100px; background: var(--bg-soft); overflow: hidden;">
        <!-- Dekoras Lingkaran -->
        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-primary opacity-10 blur-xl" style="width: 400px; height: 400px; filter: blur(50px);"></div>
        <div class="position-absolute bottom-0 end-0 translate-middle-x rounded-circle bg-tertiary opacity-10 blur-xl" style="width: 300px; height: 300px; filter: blur(40px);"></div>
        
        <div class="container position-relative z-1">
            <div class="row justify-content-center">
                <div class="col-lg-8 animate-fade-up">
                    <h1 class="display-4 fw-bold font-heading mb-4 text-dark">
                        Sistem Manajemen <span class="text-primary position-relative d-inline-block">Aset & Fasilitas</span>
                    </h1>
                    <p class="lead text-muted mb-5 px-md-5">Platform cerdas khusus Divisi Sarana dan Prasarana (Sarpras) An Nadzir Islamic School. Digunakan untuk mendata aset lembaga, inventaris tak ternilai, serta mengatur izin peminjaman fasilitas bagi civitas akademika.</p>
                    
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                        <a href="login.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg shadow-primary">
                            Login Sekarang<i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                        <a href="#fasilitas" class="btn btn-outline-primary bg-white btn-lg rounded-pill px-4 shadow-sm">
                            Eksplor Fasilitas <i class="fa-solid fa-magnifying-glass ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FASILITAS SECTION -->
    <section class="py-5 bg-white" id="fasilitas">
        <div class="container py-5">
            <div class="text-center mb-5 animate-fade-up">
                <h2 class="font-heading fw-bold pb-2 section-title mx-auto text-dark">Katalog Aset Lembaga</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Telusuri berbagai kategori aset inventaris sarana dan prasarana yang kami sediakan untuk menunjang aktivitas seluruh civitas akademika.</p>
            </div>
            
            <div class="row g-4 mt-2" id="fasilitas-grid">
                <?php 
                $delay = 0.1;
                $icon_map = ['laptop'=>'fa-laptop','car'=>'fa-car-side','building'=>'fa-building','chair'=>'fa-chair','box'=>'fa-box'];
                
                if ($aset_landing && $aset_landing->num_rows > 0):
                    while ($item = $aset_landing->fetch_assoc()):
                        $icon = $icon_map[$item['kat_icon']] ?? 'fa-box';
                        
                        // Badge status: cek pinjaman hari ini (disetujui/menunggu)
                        if (!empty($item['jam_mulai_aktif']) && !empty($item['jam_selesai_aktif'])) {
                            $jam_dari   = substr($item['jam_mulai_aktif'], 0, 5);
                            $jam_sd     = substr($item['jam_selesai_aktif'], 0, 5);
                            $now        = date('H:i');
                            // Cek apakah sekarang sedang dalam rentang waktu booking
                            if ($now >= $jam_dari && $now < $jam_sd) {
                                $status_badge = '<span class="badge bg-danger position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm" style="font-size:0.7rem;"><i class="fa-solid fa-circle-dot me-1"></i> Sedang Dipakai s/d ' . $jam_sd . '</span>';
                            } else {
                                $status_badge = '<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm" style="font-size:0.7rem;"><i class="fa-solid fa-clock me-1"></i> Akan digunakan pk ' . $jam_dari . '</span>';
                            }
                        } elseif ($item['status'] === 'tersedia') {
                            $status_badge = '<span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm"><i class="fa-solid fa-check-circle me-1"></i> Tersedia</span>';
                        } else {
                            $status_badge = '<span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm"><i class="fa-solid fa-check-circle me-1"></i> Tersedia</span>';
                        }
                        
                        // Tentukan folder foto berdasarkan tipe
                        $foto = $item['foto_aset'];
                        $foto_folder = $item['tipe'] === 'ruangan' ? 'assets/uploads/ruangan/' : 'assets/uploads/aset/';
                        
                        if (!empty($foto) && file_exists(__DIR__ . '/' . $foto_folder . $foto)) {
                            $img_tag = '<img src="' . $foto_folder . htmlspecialchars($foto) . '" class="card-img-top object-fit-cover" alt="' . htmlspecialchars($item['nama']) . '" style="height: 240px;">';
                        } else {
                            $colors = ['Elektronik'=>'#6366f1,#8b5cf6','Kendaraan'=>'#0ea5e9,#06b6d4','Ruangan'=>'#10b981,#34d399','Mebel'=>'#f59e0b,#fbbf24','Lainnya'=>'#64748b,#94a3b8'];
                            $c = $colors[$item['nama_kategori']] ?? '#6366f1,#8b5cf6';
                            $img_tag = '<div class="d-flex align-items-center justify-content-center" style="height:240px;background:linear-gradient(135deg,'.$c.');"><i class="fa-solid '.$icon.' text-white" style="font-size:5rem;opacity:0.4;"></i></div>';
                        }
                        
                        // Tentukan link tombol
                        if ($is_logged_in && $is_user) {
                            $link_btn = $item['tipe'] === 'ruangan' 
                                ? 'pegawai/pinjam_ruangan.php?id=' . $item['id']
                                : 'pegawai/peminjaman.php?id=' . $item['id'];
                        } elseif ($is_logged_in && $user_role === 'admin') {
                            $link_btn = 'login.php'; // admin tidak perlu pinjam
                        } else {
                            $link_btn = 'login.php'; // belum login
                        }
                ?>
                <div class="col-md-4 animate-fade-up" style="animation-delay: <?= $delay ?>s;">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover">
                        <div class="position-relative">
                            <?= $img_tag ?>
                            <?= $status_badge ?>
                            <?php if($item['nama_kategori']): ?>
                            <span class="badge bg-dark bg-opacity-50 text-white position-absolute bottom-0 start-0 m-3 rounded-pill px-2 py-1" style="font-size:0.65rem;backdrop-filter:blur(8px);">
                                <i class="fa-solid <?= $icon ?> me-1"></i><?= htmlspecialchars($item['nama_kategori']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-primary-soft text-primary mb-3 mx-auto">
                                <i class="fa-solid <?= $icon ?> fs-4"></i>
                            </div>
                            <h4 class="font-heading fw-bold"><?= htmlspecialchars($item['nama']) ?></h4>
                            <p class="text-muted small mb-1">
                                <?php if($item['merk']): ?><strong><?= htmlspecialchars($item['merk']) ?></strong> · <?php endif; ?>
                                Kode: <code><?= htmlspecialchars($item['kode']) ?></code>
                            </p>
                            <?php if($item['tipe'] === 'ruangan' && !empty($item['kapasitas'])): ?>
                            <p class="mb-2">
                                <span class="badge rounded-pill px-3 py-2 bg-primary-soft text-primary" style="font-size:0.75rem;">
                                    <i class="fa-solid fa-users me-1"></i> Kapasitas <?= (int)$item['kapasitas'] ?> orang
                                </span>
                            </p>
                            <?php endif; ?>
                            <?php if($item['lokasi_simpan']): ?>
                            <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1 opacity-50"></i><?= htmlspecialchars($item['lokasi_simpan']) ?></p>
                            <?php endif; ?>
                            <?php if(!empty($item['jam_mulai_aktif'])): ?>
                            <a href="<?= $link_btn ?>" class="btn btn-outline-secondary rounded-pill w-100" disabled style="opacity:0.6;cursor:not-allowed;">
                                <i class="fa-solid fa-lock me-1"></i> Sedang Dipakai
                            </a>
                            <?php else: ?>
                            <a href="<?= $link_btn ?>" class="btn btn-outline-primary rounded-pill w-100">
                                <i class="fa-solid fa-calendar-plus me-1"></i> Ajukan Peminjaman
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php $delay += 0.1; endwhile;
                else: ?>
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="fa-solid fa-box-open fs-1 mb-3 d-block opacity-30"></i>
                        <p>Belum ada aset yang tersedia untuk dipinjam.</p>
                        <small>Admin dapat menambahkan aset dengan mengaktifkan opsi "Bisa Dipinjam".</small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- PROSEDUR SECTION -->
    <section class="py-5" id="prosedur" style="background-color: var(--bg-main);">
        <div class="container py-5">
            <div class="text-center mb-5 animate-fade-up">
                <h2 class="font-heading fw-bold pb-2 section-title mx-auto text-dark">Bagaimana Meminjam Aset?</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Hanya aset dengan status "Bisa Dipinjam" (seperti mobil/alat) yang diizinkan untuk diklaim sementara waktu.</p>
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-primary text-white shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">1</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Login SSO</h4>
                        <p class="text-muted small">Gunakan portal login aman dengan Akun & Kata Sandi.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-white text-primary border border-primary shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">2</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Cari Fasilitas</h4>
                        <p class="text-muted small">Tentukan tanggal dan pilih alat/ruangan yang dibutuhkan.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-white text-primary border border-primary shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">3</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Proses Admin</h4>
                        <p class="text-muted small">Permohonan otomatis diteruskan pada pengelola sarpras.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.4s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-white text-primary border border-primary shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">4</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Tunggu Notifikasi</h4>
                        <p class="text-muted small">Tunggu hingga notifikasi persetujuan muncul.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="py-5 bg-primary text-white position-relative overflow-hidden">
        <div class="container py-5 text-center position-relative z-1">
            <h2 class="display-6 fw-bold font-heading mb-4">Masih bingung cara pinjam fasilitas?</h2>
            <p class="lead mb-5 opacity-75" style="max-width: 700px; margin: 0 auto;">Hubungi <a href="https://wa.me/6285161252008" class="text-white">Humas</a> atau <a href="login.php" class="text-white">Masuk Dashboard</a> untuk bantuan lebih lanjut.</p>
            <a href="login.php" class="btn btn-light text-primary btn-lg rounded-pill px-5 fw-bold shadow">
                Masuk Dashboard Pegawai <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-light pt-5 pb-4">
        <div class="container pt-3">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="assets/logo/logo_round.png?v=1" alt="Logo" class="bg-white rounded-circle p-1" style="width: 50px; height: 50px; object-fit: contain;">
                        <div class="d-flex flex-column lh-1">
                            <h5 class="mb-1 font-heading fw-bold">Sarana & Prasarana</h5>
                            <span class="text-white opacity-75" style="font-size: 0.8rem; font-weight: 500;">Manajemen Aset</span>
                        </div>
                    </div>
                    <p class="text-secondary small">Menjadi Lembaga Pendidikan Berwawasan Lingkungan yang Efektif, Profesional, dan Bermutu dalam Membina Pribadi Unggul.</p>
                </div>
                
                <div class="col-lg-2 col-6">
                    <h6 class="text-white mb-3 fw-bold">Navigasi</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                        <li><a href="#beranda" class="text-secondary text-decoration-none footer-link">Beranda</a></li>
                        <li><a href="#fasilitas" class="text-secondary text-decoration-none footer-link">Fasilitas</a></li>
                        <li><a href="#prosedur" class="text-secondary text-decoration-none footer-link">Prosedur</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3">
                    <h6 class="text-white mb-3 fw-bold">Hubungi Kami</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small text-secondary">
                        <li><i class="fa-solid fa-envelope me-2"></i> info@annadzir.sch.id</li>
                        <li><i class="fa-solid fa-phone me-2"></i> +62 851 6125 2008</li>
                        <li><i class="fa-solid fa-location-dot me-2"></i> Bendungan Karet Cisirih RT. 09/02 Desa Kamasan Kec. Cinangka Kab. Serang Prov. Banten 42167</li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary opacity-25">
            <div class="text-center text-secondary small pt-2">
                &copy; <?= date("Y") ?> An Nadzir Islamic School
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS (Lokal) -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
