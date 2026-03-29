<?php 
// pegawai/layouts/header.php
$profil_tema = $koneksi->query("SELECT sidebar_gradient, topbar_color FROM profil_lembaga LIMIT 1")->fetch_assoc();

// Ekstrak warna utama dari gradient untuk aksen UI lainnya
$grad = $profil_tema['sidebar_gradient'];
preg_match('/(#[a-f0-9]{3,6}|rgba?\([^)]+\))/i', $grad, $matches);
$primary_color = isset($matches[0]) ? $matches[0] : '#3b82f6';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'User Dashboard' ?> | Sarpras An Nadzir</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/logo/logo_round.png?v=2">
    <!-- Bootstrap 5 -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?= $primary_color ?>;
            --primary-gradient: <?= $profil_tema['sidebar_gradient'] ?>;
            --topbar-text: <?= $profil_tema['topbar_color'] ?>;
        }
        
        body { background: #f8fafc; font-family: 'Outfit', sans-serif; }
        
        /* Navbar Branding */
        .text-primary, .navbar-brand.text-success { color: var(--primary-color) !important; }
        .btn-primary, .btn-success, .date-nav, .hero-banner { background: var(--primary-gradient) !important; color: white !important; border: none !important; }
        
        .stat-card:hover { border-color: var(--primary-color) !important; }
        .category-card:hover { background: #f0fdf4; border-color: var(--primary-color) !important; }
        .category-card i { color: var(--primary-color) !important; }
        
        .btn-pinjam-lg { color: var(--primary-color) !important; }
        .nav-tabs-custom .nav-link.active { background: var(--primary-color) !important; color: white !important; border-color: var(--primary-color) !important; }
        .nav-tabs-custom .nav-link { color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
        
        .slot-booked { background: var(--primary-color) !important; }
        .asset-col .text-primary { color: var(--primary-color) !important; }
        
        .bg-success-soft { background-color: <?= $primary_color ?>20 !important; }
        .text-success { color: var(--primary-color) !important; }
        
        .navbar-brand .text-dark { color: var(--topbar-text) !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg glass-effect fixed-top shadow-sm py-2 py-md-3 px-3 px-md-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-success" href="dashboard.php">
                <img src="../assets/logo/logo_round.png" alt="Logo" width="40" height="40" class="rounded-circle shadow-sm">
                <div class="d-none d-sm-block">
                    <div class="lh-1" style="font-size: 1rem;">An Nadzir <span class="text-dark">LRC</span></div>
                    <div class="text-muted" style="font-size: 0.65rem; font-weight: 500;">Sistem Peminjaman Aset</div>
                </div>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="d-none d-md-block text-end">
                    <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="text-muted" style="font-size: 0.65rem;">Username: <?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm rounded-circle p-1 overflow-hidden" type="button" data-bs-toggle="dropdown" style="width: 42px; height: 42px;">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=<?= ltrim($primary_color, '#') ?>&color=fff&bold=true" alt="Avatar" width="38" height="38" class="rounded-circle">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 py-2" style="min-width: 200px;">
                        <li class="px-3 py-2 border-bottom mb-2">
                            <div class="fw-bold text-dark small">Persona: <?= htmlspecialchars($_SESSION['nama_pemakai'] ?? 'Belum Set') ?></div>
                            <div class="text-muted" style="font-size: 0.65rem;"><?= htmlspecialchars($_SESSION['unit_pemakai'] ?? '-') ?></div>
                        </li>
                        <li><a class="dropdown-item py-2 px-3 fw-medium" href="identifikasi.php"><i class="fa-solid fa-user-tag me-2 small text-primary"></i> Ganti Identitas</a></li>
                        <li><a class="dropdown-item py-2 px-3 fw-medium text-danger" href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2 small"></i> Keluar Sistem</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
