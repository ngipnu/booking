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
    <meta name="description" content="Sistem Informasi Peminjaman Fasilitas Annadzir Islamic School. Mengelola peminjaman mobil dinas, ruang multimedia, dan peralatan elektronik secara efisien dan aman.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="index.php" class="nav-brand">
                <i class="fa-solid fa-building-columns logo-icon"></i>
                <div class="brand-text">
                    <span>Annadzir Booking</span>
                    <span>Sistem Fasilitas Terpadu</span>
                </div>
            </a>
            <ul class="nav-menu">
                <li><a href="#beranda" class="nav-link">Beranda</a></li>
                <li><a href="#fasilitas" class="nav-link">Fasilitas</a></li>
                <li><a href="#prosedur" class="nav-link">Prosedur</a></li>
                <li>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fa-solid fa-user-lock"></i> Login Pegawai
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero" id="beranda">
        <div class="hero-bg">
            <img src="assets/images/hero_school_bg.png" alt="Annadzir Islamic School Lobby">
        </div>
        <div class="container">
            <div class="hero-content">
                <span class="hero-tag"><i class="fa-solid fa-shield-halved"></i> Aman & Terpercaya (PHP Native)</span>
                <h1 class="hero-title">Peminjaman Fasilitas <span>Lebih Mudah</span></h1>
                <p class="hero-text">Sistem informasi manajemen peminjaman fasilitas terpadu khusus untuk pegawai Annadzir Islamic School. Pinjam mobil dinas, ruang multimedia, hingga peralatan elektronik dalam satu platform.</p>
                <div class="hero-actions">
                    <a href="login.php" class="btn btn-accent">
                        Mulai Pinjam Fasilitas <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="#fasilitas" class="btn btn-outline" style="color:white; border-color:white;">
                        Lihat Daftar Fasilitas <i class="fa-solid fa-search"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FASILITAS SECTION -->
    <section class="section" id="fasilitas">
        <div class="container">
            <div class="text-center mb-12">
                <h2 class="section-title">Katalog Fasilitas</h2>
                <p class="section-subtitle">Berbagai fasilitas unggulan Annadzir Islamic School yang dapat dipinjam oleh pegawai untuk keperluan dinas dan operasional pendidikan.</p>
            </div>
            
            <div class="facilities-grid">
                <!-- Fasilitas 1 -->
                <div class="facility-card">
                    <div class="facility-badge tersedia">
                        <i class="fa-solid fa-circle-check"></i> Tersedia
                    </div>
                    <div class="facility-img">
                        <img src="assets/images/official_car.png" alt="Mobil Dinas Operasional">
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Kendaraan Dinas</h3>
                        <p class="facility-desc">Berbagai kendaraan roda empat untuk keperluan dinas luar kota, kunjungan, atau operasional penting sekolah.</p>
                        <a href="login.php" class="btn btn-outline" style="width: 100%;">
                            Cek Ketersediaan <i class="fa-solid fa-car-side"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Fasilitas 2 -->
                <div class="facility-card">
                    <div class="facility-badge">
                        <i class="fa-solid fa-calendar-check"></i> Bisa Dipesan
                    </div>
                    <div class="facility-img">
                        <img src="assets/images/multimedia_room.png" alt="Ruang Multimedia">
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Ruang Multimedia</h3>
                        <p class="facility-desc">Ruangan presentasi canggih dengan proyektor interaktif, sistem audio, dan spesifikasi komputer tingkat tinggi.</p>
                        <a href="login.php" class="btn btn-outline" style="width: 100%;">
                            Cek Jadwal <i class="fa-solid fa-chalkboard-user"></i>
                        </a>
                    </div>
                </div>

                <!-- Fasilitas 3 -->
                <div class="facility-card">
                    <div class="facility-badge tersedia">
                        <i class="fa-solid fa-circle-check"></i> Tersedia
                    </div>
                    <div class="facility-img">
                        <img src="assets/images/electronic_gear.png" alt="Handphone & Kamera">
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Peralatan Multimedia</h3>
                        <p class="facility-desc">Peminjaman Handphone dinas, Kamera DSLR/Mirrorless untuk dokumentasi kegiatan acara sekolah.</p>
                        <a href="login.php" class="btn btn-outline" style="width: 100%;">
                            Cek Ketersediaan <i class="fa-solid fa-camera-retro"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROSEDUR SECTION -->
    <section class="section steps-section" id="prosedur">
        <div class="container">
            <div class="text-center mb-12">
                <h2 class="section-title">Bagaimana Cara Kerjanya?</h2>
                <p class="section-subtitle">Proses peminjaman fasilitas di Annadzir didesain transparan, cepat, dan tercatat otomatis oleh sistem.</p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-user-check"></i></div>
                    <h3 class="step-title">1. Login SSO Pegawai</h3>
                    <p class="step-desc">Masuk menggunakan Nomor Induk Yayasan (NIY) dan kata sandi Anda ke dalam dashboard sistem.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-list-check"></i></div>
                    <h3 class="step-title">2. Pilih Fasilitas</h3>
                    <p class="step-desc">Pilih fasilitas yang dibutuhkan (Mobil, Ruangan, atau Alat), tentukan tanggal dan jam pemakaian.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-file-signature"></i></div>
                    <h3 class="step-title">3. Persetujuan Admin</h3>
                    <p class="step-desc">Permintaan Anda akan direview oleh Kepala Tata Usaha atau Bagian Sarpras untuk persetujuan.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-key"></i></div>
                    <h3 class="step-title">4. Pengambilan</h3>
                    <p class="step-desc">Bawa bukti approval digital (QR/PDF) untuk mengambil kunci kendaraan atau alat yang dipinjam.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="cta-section">
        <div class="container cta-content">
            <h2 class="cta-title">Siap Meminjam Fasilitas Hari Ini?</h2>
            <p class="cta-text">Sistem ini dibangun dengan PHP Native murni, menjamin keamanan tinggi dari ancaman malware dan serangan siber, sesuai dengan standar IT Annadzir Islamic School pada shared hosting.</p>
            <a href="login.php" class="btn btn-accent btn-lg" style="padding: 16px 40px; font-size: 1.125rem;">
                Akses Dashboard Pegawai <i class="fa-solid fa-right-to-bracket"></i>
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <i class="fa-solid fa-building-columns logo-icon"></i>
                    <h3 class="footer-title">Annadzir Islamic School</h3>
                    <p>Mencetak generasi rabbani dengan fasilitas dan teknologi terkini yang aman dan terintegrasi.</p>
                </div>
                
                <div>
                    <h4 class="footer-title">Tautan Cepat</h4>
                    <ul class="footer-links">
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#fasilitas">Katalog Fasilitas</a></li>
                        <li><a href="#prosedur">Prosedur Peminjaman</a></li>
                        <li><a href="login.php">Login Area</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Kategori Fasilitas</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-car"></i> Kendaraan Dinas</a></li>
                        <li><a href="#"><i class="fa-solid fa-building"></i> Ruang Pertemuan</a></li>
                        <li><a href="#"><i class="fa-solid fa-camera"></i> Alat Dokumentasi</a></li>
                        <li><a href="#"><i class="fa-solid fa-mobile-screen"></i> Perangkat IT</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Dukungan Tim IT</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-envelope"></i> it@annadzir.sch.id</a></li>
                        <li><a href="#"><i class="fa-solid fa-phone"></i> Ext: 102 (Sarpras)</a></li>
                        <li><a href="#"><i class="fa-solid fa-location-dot"></i> Gedung Utama Lt. 1</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> Annadzir Islamic School - Bagian IT & Sarana Prasarana. All Rights Reserved. Sistem PHP Native V1.0.
            </div>
        </div>
    </footer>

    <!-- JavaScript for UI Interactions -->
    <script src="assets/js/script.js"></script>
</body>
</html>
