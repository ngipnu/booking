<?php
// admin/layouts/topbar.php
?>
<!-- Topbar -->
<div class="topbar glass-effect d-flex justify-content-between align-items-center mb-4 rounded-bottom" style="margin-top:-1px;">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none" id="sidebar-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h5 class="font-heading fw-bold mb-0 text-dark d-none d-sm-block">
            <?php 
                $page_title_map = [
                    'dashboard' => 'Dashboard Utama',
                    'aset' => 'Kelola Aset Lembaga',
                    'peminjaman' => 'Manajemen Peminjaman',
                    'pengguna' => 'Kelola Pengguna Sistem',
                    'profil' => 'Pengaturan Profil Lembaga'
                ];
                $top_title = isset($page_title_map[$current_page]) ? $page_title_map[$current_page] : 'Manajemen Aset Lembaga';
                echo $top_title;
            ?>
        </h5>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <!-- Notifikasi -->
        <div class="dropdown">
            <button class="topbar-btn border-0 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-bell text-muted"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="margin-top: 10px; margin-left: -10px;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end glass-effect border-0 shadow-lg mt-2 p-2" style="width: 300px; border-radius: 16px;">
                <div class="px-3 py-2 border-bottom mb-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Notifikasi</span>
                    <span class="badge bg-primary-soft text-primary small">Baru</span>
                </div>
                <div class="dropdown-item py-3 px-3 rounded-3 mb-1" style="white-space: normal;">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary-soft text-primary rounded-circle p-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-hand-holding-hand small"></i>
                        </div>
                        <div class="lh-sm">
                            <p class="mb-1 small fw-bold">Peminjaman Baru!</p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Pegawai A mengajukan peminjaman Laptop Apple.</p>
                        </div>
                    </div>
                </div>
                <div class="px-3 py-2 text-center mt-2">
                    <a href="#" class="text-primary small text-decoration-none fw-medium">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <div class="vr mx-1 text-muted opacity-25"></div>

        <!-- Profile User -->
        <div class="dropdown">
            <a href="#" class="profile-link d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary-soft text-primary rounded-pill d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="d-flex flex-column lh-1 d-none d-md-flex">
                    <span class="fw-bold text-dark text-truncate" style="font-size: 0.85rem; max-width: 120px;"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                    <span class="text-muted" style="font-size: 0.7rem;">Administrator</span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end glass-effect border-0 shadow-lg mt-2 p-2" style="border-radius: 16px; min-width: 200px;">
                <li><a class="dropdown-item py-2 px-3 rounded-3 mb-1" href="../profil/index.php"><i class="fa-solid fa-user-gear me-2 opacity-50"></i> Edit Profil</a></li>
                <li><a class="dropdown-item py-2 px-3 rounded-3 mb-1" href="../profil/index.php"><i class="fa-solid fa-shield-halved me-2 opacity-50"></i> Keamanan</a></li>
                <li><hr class="dropdown-divider opacity-10"></li>
                <li><a class="dropdown-item py-2 px-3 rounded-3 text-danger" href="../../logout.php"><i class="fa-solid fa-power-off me-2 opacity-50"></i> Keluar</a></li>
            </ul>
        </div>
    </div>
</div>
