<?php
// Aplikasi Booking Fasilitas - Annadzir Islamic School
// Landing Page
session_start();
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
</head>
<body data-bs-spy="scroll" data-bs-target="#navbar" data-bs-offset="100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm py-3 bg-white transition-all" id="navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="assets/logo/logo_round.png?v=1" alt="Logo Annadzir" style="width: 45px; height: 45px; object-fit: contain;">
                <div class="d-flex flex-column lh-1">
                    <span class="font-heading fw-bold text-dark fs-5">Sarpras Annadzir</span>
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
                        <i class="fa-solid fa-user-lock"></i> Login Pegawai
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
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-4 shadow-sm border border-primary-subtle fw-medium">
                        <i class="fa-solid fa-shield-halved me-1"></i> Sistem Aman & Terpercaya
                    </span>
                    <h1 class="display-4 fw-bold font-heading mb-4 text-dark">
                        Sistem Manajemen <span class="text-primary position-relative d-inline-block">Aset & Fasilitas</span>
                    </h1>
                    <p class="lead text-muted mb-5 px-md-5">Platform cerdas khusus Divisi Sarana dan Prasarana (Sarpras) An Nadzir Islamic School. Digunakan untuk mendata aset lembaga, inventaris tak ternilai, serta mengatur izin sirkulasi peminjaman pegawai.</p>
                    
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                        <a href="login.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                            Masuk Sekarang <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                        <a href="#fasilitas" class="btn btn-outline-dark bg-white btn-lg rounded-pill px-4 shadow-sm">
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
            
            <div class="row g-4 mt-2">
                <!-- Fasilitas 1 -->
                <div class="col-md-4 animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover">
                        <div class="position-relative">
                            <img src="assets/images/official_car.png" class="card-img-top object-fit-cover" alt="Mobil Dinas" style="height: 240px;">
                            <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm">
                                <i class="fa-solid fa-check-circle me-1"></i> Tersedia
                            </span>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-primary-soft text-primary mb-3 mx-auto">
                                <i class="fa-solid fa-car-side fs-4"></i>
                            </div>
                            <h4 class="font-heading fw-bold">Kendaraan Dinas</h4>
                            <p class="text-muted small mb-4">Pesan mobil operasional untuk kepentingan rapat luar maupun dinas pendidikan.</p>
                            <a href="login.php" class="btn btn-outline-primary rounded-pill w-100">Cek Jadwal</a>
                        </div>
                    </div>
                </div>
                
                <!-- Fasilitas 2 -->
                <div class="col-md-4 animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover">
                        <div class="position-relative">
                            <img src="assets/images/multimedia_room.png" class="card-img-top object-fit-cover" alt="Ruang Multimedia" style="height: 240px;">
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm">
                                <i class="fa-solid fa-calendar-check me-1"></i> Bisa Dipesan
                            </span>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-tertiary-soft text-tertiary mb-3 mx-auto">
                                <i class="fa-solid fa-chalkboard-user fs-4"></i>
                            </div>
                            <h4 class="font-heading fw-bold">Ruang Presentasi</h4>
                            <p class="text-muted small mb-4">Sewa ruangan rapat atau multimedia lengkap dengan proyektor interaktif terpadu.</p>
                            <a href="login.php" class="btn btn-outline-primary rounded-pill w-100">Reservasi</a>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas 3 -->
                <div class="col-md-4 animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover">
                        <div class="position-relative">
                            <img src="assets/images/electronic_gear.png" class="card-img-top object-fit-cover" alt="Alat Dokumentasi" style="height: 240px;">
                            <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm">
                                <i class="fa-solid fa-check-circle me-1"></i> Tersedia
                            </span>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-secondary-soft text-secondary mx-auto mb-3">
                                <i class="fa-solid fa-camera-retro fs-4"></i>
                            </div>
                            <h4 class="font-heading fw-bold">Perangkat Ekstra</h4>
                            <p class="text-muted small mb-4">Peminjaman alat tulis, smartphone operasional admin, hingga kamera digital DSLR/Mirrorless.</p>
                            <a href="login.php" class="btn btn-outline-primary rounded-pill w-100">Lihat Alat</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROSEDUR SECTION -->
    <section class="py-5" id="prosedur" style="background-color: var(--bg-main);">
        <div class="container py-5">
            <div class="text-center mb-5 animate-fade-up">
                <h2 class="font-heading fw-bold pb-2 section-title mx-auto text-dark">Bagaimana Meminjam Aset?</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Hanya aset dengan status "Bisa Dipinjam" (seperti mobil/alat) yang diizinkan untuk diklaim sementara waktu. Aset inventaris internal tetap utuh dan terkalkulasi dalam total biaya lembaga.</p>
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-primary text-white shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">1</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Login SSO</h4>
                        <p class="text-muted small">Gunakan portal login aman dengan Nomor Induk (NIY) & Kata Sandi.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-white text-primary border border-primary shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">2</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Cari Fasilitas</h4>
                        <p class="text-muted small">Tentukan tanggal dan pilih armada/alat yang dibutuhkan.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-white text-primary border border-primary shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">3</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Proses Admin</h4>
                        <p class="text-muted small">Permohonan otomatis diteruskan pada tata usaha / sarpras.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-fade-up" style="animation-delay: 0.4s;">
                    <div class="timeline-step text-center px-2">
                        <div class="step-number fw-bold mb-3 mx-auto bg-white text-primary border border-primary shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-size: 1.25rem;">4</div>
                        <h4 class="h5 font-heading fw-bold text-dark">Bawa QR Bukti</h4>
                        <p class="text-muted small">Serahkan bukti disetujui digital kepada pihak sarpras secara mulus.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="py-5 bg-primary text-white position-relative overflow-hidden">
        <div class="container py-5 text-center position-relative z-1">
            <h2 class="display-6 fw-bold font-heading mb-4">Siap Memaksimalkan Kinerja Anda?</h2>
            <p class="lead mb-5 opacity-75" style="max-width: 700px; margin: 0 auto;">Bergabung dan mulailah kemudahan tanpa batas dengan mengotomatisasi dokumen secara ramah lingkungan.</p>
            <a href="login.php" class="btn btn-light text-primary btn-lg rounded-pill px-5 fw-bold shadow">
                Masuk Dashboard Pegawai <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
        <!-- Decorative bg -->
        <i class="fa-solid fa-building-columns position-absolute text-white opacity-10" style="font-size: 300px; top: -50px; right: -50px; transform: rotate(-15deg);"></i>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-light pt-5 pb-4">
        <div class="container pt-3">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="assets/logo/logo_round.png?v=1" alt="Logo" class="bg-white rounded-circle p-1" style="width: 50px; height: 50px; object-fit: contain;">
                        <h5 class="mb-0 font-heading fw-bold">Annadzir School</h5>
                    </div>
                    <p class="text-secondary small">Menjembatani keunggulan pendidikan melalui kolaborasi sistem manajemen fasilitas kelas atas dengan balutan integrasi yang profesional.</p>
                </div>
                
                <div class="col-lg-2 col-6">
                    <h6 class="text-white mb-3 fw-bold">Navigasi</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                        <li><a href="#beranda" class="text-secondary text-decoration-none footer-link">Beranda</a></li>
                        <li><a href="#fasilitas" class="text-secondary text-decoration-none footer-link">Fasilitas</a></li>
                        <li><a href="#prosedur" class="text-secondary text-decoration-none footer-link">Prosedur</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-6">
                    <h6 class="text-white mb-3 fw-bold">Layanan</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                        <li><a href="#" class="text-secondary text-decoration-none footer-link">Dokumentasi API</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none footer-link">Bantuan Penggunaan</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none footer-link">Tanya Tim IT</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3">
                    <h6 class="text-white mb-3 fw-bold">Hubungi Kami</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small text-secondary">
                        <li><i class="fa-solid fa-envelope me-2"></i> info@annadzir.sch.id</li>
                        <li><i class="fa-solid fa-phone me-2"></i> +62 821 0000 0000</li>
                        <li><i class="fa-solid fa-location-dot me-2"></i> Gedung Rektorat Lt.1</li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary opacity-25">
            <div class="text-center text-secondary small pt-2">
                &copy; <?= date("Y") ?> Annadzir Bagian Sarpras & Kepegawaian. PHP Native, Bootstrap 5.
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS (Lokal) -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
