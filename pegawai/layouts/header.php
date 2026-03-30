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
    <title><?= $page_title ?? 'User Dashboard' ?> | An Nadzir Learning Center</title>
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
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active,
        .btn-primary *  { color: white !important; }
        .btn-outline-primary { color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
        .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
            background: var(--primary-gradient) !important;
            color: white !important;
            border-color: transparent !important;
        }
        
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

        /* Pastikan navbar dan dropdown notifikasi selalu di atas konten */
        .navbar.fixed-top { z-index: 9990 !important; }
        .navbar .dropdown-menu { z-index: 9999 !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg glass-effect fixed-top shadow-sm py-2 py-md-3 px-3 px-md-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-success" href="dashboard.php">
                <img src="../assets/logo/logo_round.png" alt="Logo" width="40" height="40" class="rounded-circle shadow-sm">
                <div class="d-none d-sm-block">
                    <div class="lh-1" style="font-size: 1rem;">An Nadzir <span class="text-dark">Learning Center</span></div>
                    <div class="text-muted" style="font-size: 0.65rem; font-weight: 500;">Sistem Peminjaman Aset</div>
                </div>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="d-none d-md-block text-end">
                    <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="text-muted" style="font-size: 0.65rem;">Username: <?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>

                <!-- Bell Notifikasi User -->
                <div class="dropdown" id="notif-dropdown-user">
                    <button class="btn btn-light border-0 rounded-circle position-relative p-2" id="notifBellUser"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                            style="width:42px;height:42px;" title="Notifikasi">
                        <i class="fa-solid fa-bell fs-6 text-muted"></i>
                        <span id="notif-badge-user" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size:0.6rem;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 p-0" style="min-width:340px;max-width:360px;z-index:9999;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fa-solid fa-bell me-2 text-primary"></i> Notifikasi</h6>
                            <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none" onclick="bacaSemua('user')" style="font-size:0.75rem;">Tandai semua dibaca</button>
                        </div>
                        <div id="notif-list-user" style="max-height:360px;overflow-y:auto;">
                            <div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin"></i></div>
                        </div>
                        <div class="px-3 py-2 border-top text-center">
                            <a href="riwayat.php" class="text-primary small fw-bold text-decoration-none">Lihat semua aktivitas →</a>
                        </div>
                    </div>
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

<script>
const NOTIF_API_USER = '../api/notifikasi.php';

async function muatNotifikasiUser() {
    try {
        const res  = await fetch(NOTIF_API_USER + '?aksi=get');
        const data = await res.json();
        const badge = document.getElementById('notif-badge-user');
        const list  = document.getElementById('notif-list-user');

        if (data.unread > 0) {
            badge.textContent = data.unread > 9 ? '9+' : data.unread;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }

        if (!data.notifikasi || data.notifikasi.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-4 small"><i class="fa-solid fa-bell-slash mb-2 d-block fs-4 opacity-30"></i>Belum ada notifikasi</div>';
            return;
        }

        const colors = {success:'#10b981',danger:'#ef4444',warning:'#f59e0b',info:'#3b82f6'};
        list.innerHTML = data.notifikasi.map(n => `
            <a href="${n.link || '#'}" onclick="bacaNotif(${n.id},'user')" class="d-flex gap-3 px-3 py-3 text-decoration-none border-bottom ${
                n.is_read == 0 ? 'bg-primary bg-opacity-5' : ''}" style="transition:background .2s;">
                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:${colors[n.tipe]}20;">
                    <i class="fa-solid ${ {success:'fa-check',danger:'fa-xmark',warning:'fa-clock',info:'fa-info'}[n.tipe] } small" style="color:${colors[n.tipe]};"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-dark" style="font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.judul}</div>
                    <div class="text-muted" style="font-size:.72rem;line-clamp:2;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${n.pesan}</div>
                    <div class="text-muted opacity-60" style="font-size:.65rem;margin-top:2px;">${new Date(n.created_at).toLocaleString('id-ID',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})}</div>
                </div>
                ${n.is_read == 0 ? '<span class="flex-shrink-0 rounded-circle bg-primary align-self-center" style="width:8px;height:8px;"></span>' : ''}
            </a>`).join('');
    } catch(e) { console.error(e); }
}

async function bacaNotif(id, tipe) {
    await fetch(NOTIF_API_USER + '?aksi=baca&id=' + id);
    if (tipe === 'user') muatNotifikasiUser();
}

async function bacaSemua(tipe) {
    await fetch(NOTIF_API_USER + '?aksi=baca&id=0');
    if (tipe === 'user') muatNotifikasiUser();
}

document.getElementById('notifBellUser').addEventListener('show.bs.dropdown', muatNotifikasiUser);
muatNotifikasiUser(); // Muat badge saat load
setInterval(muatNotifikasiUser, 60000); // Auto-refresh tiap 1 menit
</script>
