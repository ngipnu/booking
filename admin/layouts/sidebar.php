<?php
// admin/layouts/sidebar.php
$current_page = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- Sidebar -->
<aside class="offcanvas-sidebar glass-effect d-flex flex-column py-4 px-3" id="sidebar">
    <a href="../dashboard/index.php" class="d-flex align-items-center gap-3 text-decoration-none mb-4 pb-3 border-bottom px-2" style="border-color: rgba(0,0,0,0.05) !important;">
        <img src="../../assets/logo/logo_round.png?v=2" alt="Logo" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; object-fit: contain;">
        <div class="d-flex flex-column text-dark">
            <span class="fs-6 fw-bold font-heading lh-1">Sarpras An Nadzir</span>
            <span class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Manajemen Aset</span>
        </div>
    </a>
    
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="../dashboard/index.php" class="nav-link sidebar-link <?= ($current_page == 'dashboard') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../aset/index.php" class="nav-link sidebar-link <?= ($current_page == 'aset') ? 'active' : '' ?>">
                <i class="fa-solid fa-boxes-stacked"></i> <span>Data Inventaris</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../kategori/index.php" class="nav-link sidebar-link <?= ($current_page == 'kategori') ? 'active' : '' ?>">
                <i class="fa-solid fa-tags"></i> <span>Data Kategori</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../peminjaman/index.php" class="nav-link sidebar-link <?= ($current_page == 'peminjaman' && basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-hand-holding-hand"></i> <span>Daftar Pinjam</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../peminjaman/jadwal.php" class="nav-link sidebar-link <?= (basename($_SERVER['PHP_SELF']) == 'jadwal.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-day"></i> <span>Jadwal Pinjam</span>
            </a>
        </li>
        <li class="nav-item mt-4 mb-2 px-3 text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Pengaturan</li>
        <li class="nav-item">
            <a href="../pengguna/index.php" class="nav-link sidebar-link <?= ($current_page == 'pengguna') ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear"></i> <span>Pengguna</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../profil/index.php" class="nav-link sidebar-link <?= ($current_page == 'profil') ? 'active' : '' ?>">
                <i class="fa-solid fa-building-circle-check"></i> <span>Profil Lembaga</span>
            </a>
        </li>
    </ul>
    
    <div class="mt-auto pt-3 border-top">
        <a href="../../logout.php" class="nav-link sidebar-link text-danger w-100">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Keluar Sistem</span>
        </a>
    </div>
</aside>
