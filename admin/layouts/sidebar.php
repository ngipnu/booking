<?php
// admin/layouts/sidebar.php
$current_page = dirname($_SERVER['PHP_SELF']);
$current_dir = basename($current_page);
$current_file = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar-link {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        border-radius: 12px !important;
        margin-bottom: 2px !important;
        color: rgba(255,255,255,0.7) !important;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
    }
    .sidebar-link i { width: 20px; text-align: center; font-size: 1rem; }
    .sidebar-link:hover, .sidebar-link.active {
        background: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
    .sidebar-link.active {
        background: var(--primary-color, #2563eb) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .nav-group-header {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: rgba(255,255,255,0.4);
        padding: 24px 15px 8px;
    }
    .submenu {
        padding-left: 20px;
        position: relative;
        margin-bottom: 8px;
    }
    .submenu::before {
        content: '';
        position: absolute;
        left: 24px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: rgba(255,255,255,0.08);
    }
    .submenu .sidebar-link {
        font-size: 0.8rem !important;
        padding: 7px 15px !important;
        border-radius: 10px !important;
    }
    .dropdown-toggle::after {
        content: '\f107';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        border: 0;
        margin-left: auto;
        font-size: 0.8rem;
        transition: transform 0.2s ease;
        opacity: 0.5;
    }
    .dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(180deg);
        opacity: 1;
    }
    .offcanvas-sidebar {
        width: 250px !important;
    }
</style>

<!-- Sidebar -->
<aside class="offcanvas-sidebar glass-effect d-flex flex-column py-4 px-3" id="sidebar">
    <a href="../dashboard/index.php" class="d-flex align-items-center gap-3 text-decoration-none mb-4 pb-3 border-bottom px-2" style="border-color: rgba(255,255,255,0.1) !important;">
        <img src="../../assets/logo/logo_round.png?v=2" alt="Logo" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: contain; background: white;">
        <div class="d-flex flex-column text-white">
            <span class="fs-6 fw-bold font-heading lh-1">Sarana & Prasarana</span>
            <span class="opacity-50" style="font-size: 0.7rem; font-weight: 500;">Sistem Manajemen Aset</span>
        </div>
    </a>
    
    <div class="overflow-y-auto px-1" style="flex: 1;">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="../dashboard/index.php" class="nav-link sidebar-link <?= ($current_dir == 'dashboard') ? 'active' : '' ?>">
                    <i class="fa-solid fa-house-chimney"></i> <span>Dashboard</span>
                </a>
            </li>

            <!-- Master Data -->
            <li class="nav-item">
                <?php 
                $master_active = in_array($current_dir, ['aset', 'gedung', 'ruangan', 'kategori', 'waktu']); 
                ?>
                <a href="#menuMaster" class="nav-link sidebar-link dropdown-toggle <?= $master_active ? 'active' : '' ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= $master_active ? 'true' : 'false' ?>">
                    <i class="fa-solid fa-database"></i> <span>Data Master</span>
                </a>
                <div class="collapse <?= $master_active ? 'show' : '' ?>" id="menuMaster">
                    <ul class="nav flex-column submenu">
                        <li><a href="../aset/index.php" class="nav-link sidebar-link <?= ($current_dir == 'aset') ? 'active text-white' : '' ?>">Inventaris</a></li>
                        <li><a href="../gedung/index.php" class="nav-link sidebar-link <?= ($current_dir == 'gedung') ? 'active text-white' : '' ?>">Gedung</a></li>
                        <li><a href="../ruangan/index.php" class="nav-link sidebar-link <?= ($current_dir == 'ruangan') ? 'active text-white' : '' ?>">Ruangan</a></li>
                        <li><a href="../kategori/index.php" class="nav-link sidebar-link <?= ($current_dir == 'kategori') ? 'active text-white' : '' ?>">Kategori</a></li>
                        <li><a href="../waktu/index.php" class="nav-link sidebar-link <?= ($current_dir == 'waktu') ? 'active text-white' : '' ?>">Waktu</a></li>
                    </ul>
                </div>
            </li>

            <!-- Peminjaman -->
            <li class="nav-item">
                <?php $pinjam_active = ($current_dir == 'peminjaman'); ?>
                <a href="#menuPinjam" class="nav-link sidebar-link dropdown-toggle <?= $pinjam_active ? 'active' : '' ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= $pinjam_active ? 'true' : 'false' ?>">
                    <i class="fa-solid fa-hand-holding-hand"></i> <span>Transaksi</span>
                </a>
                <div class="collapse <?= $pinjam_active ? 'show' : '' ?>" id="menuPinjam">
                    <ul class="nav flex-column submenu">
                        <li><a href="../peminjaman/index.php" class="nav-link sidebar-link <?= ($current_file == 'index.php' && $current_dir == 'peminjaman') ? 'active text-white' : '' ?>">Daftar Pinjam</a></li>
                        <li><a href="../peminjaman/jadwal.php" class="nav-link sidebar-link <?= ($current_file == 'jadwal.php' && $current_dir == 'peminjaman') ? 'active text-white' : '' ?>">Jadwal Detail</a></li>
                    </ul>
                </div>
            </li>

            <!-- Laporan -->
            <li class="nav-item">
                <?php $lap_active = ($current_dir == 'laporan'); ?>
                <a href="#menuLaporan" class="nav-link sidebar-link dropdown-toggle <?= $lap_active ? 'active' : '' ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= $lap_active ? 'true' : 'false' ?>">
                    <i class="fa-solid fa-file-invoice"></i> <span>Laporan</span>
                </a>
                <div class="collapse <?= $lap_active ? 'show' : '' ?>" id="menuLaporan">
                    <ul class="nav flex-column submenu">
                        <li><a href="../laporan/peminjaman.php" class="nav-link sidebar-link <?= ($current_file == 'peminjaman.php') ? 'active text-white' : '' ?>">Rekap Pinjam</a></li>
                        <li><a href="../laporan/inventaris.php" class="nav-link sidebar-link <?= ($current_file == 'inventaris.php') ? 'active text-white' : '' ?>">Rekap Inventaris</a></li>
                        <li><a href="../laporan/ruangan.php" class="nav-link sidebar-link <?= ($current_file == 'ruangan.php') ? 'active text-white' : '' ?>">Rekap Ruangan</a></li>
                    </ul>
                </div>
            </li>

            <!-- Sistem -->
            <div class="nav-group-header">Konfigurasi</div>
            <li class="nav-item">
                <a href="../pengguna/index.php" class="nav-link sidebar-link <?= ($current_dir == 'pengguna') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users-gear"></i> <span>Manajemen User</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../profil/index.php" class="nav-link sidebar-link <?= ($current_dir == 'profil') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i> <span>Profil Lembaga</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="mt-auto pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
        <a href="../../logout.php" class="nav-link sidebar-link text-danger w-100 hover-danger" onclick="return confirm('Keluar dari sistem?')">
            <i class="fa-solid fa-power-off"></i> <span>Keluar</span>
        </a>
    </div>
</aside>
