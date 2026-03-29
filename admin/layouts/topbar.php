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

        <!-- Bell Notifikasi Admin (Dinamis) -->
        <div class="dropdown">
            <button class="topbar-btn border-0 shadow-sm position-relative" type="button"
                    id="notifBellAdmin" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="fa-regular fa-bell text-muted"></i>
                <span id="notif-badge-admin" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size:0.6rem;margin-top:4px;margin-left:-8px;">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-0" style="width:340px;border-radius:16px;">
                <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-bell me-2 text-primary"></i> Notifikasi</h6>
                    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none" onclick="bacaSemuaAdmin()" style="font-size:0.75rem;">Tandai semua dibaca</button>
                </div>
                <div id="notif-list-admin" style="max-height:360px;overflow-y:auto;">
                    <div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin"></i></div>
                </div>
                <div class="px-3 py-2 border-top text-center">
                    <a href="../peminjaman/index.php" class="text-primary small fw-bold text-decoration-none">Lihat semua peminjaman →</a>
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

<script>
const NOTIF_API_ADMIN = '../../api/notifikasi.php';

async function muatNotifikasiAdmin() {
    try {
        const res  = await fetch(NOTIF_API_ADMIN + '?aksi=get');
        const data = await res.json();
        const badge = document.getElementById('notif-badge-admin');
        const list  = document.getElementById('notif-list-admin');

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
        const icons  = {success:'fa-check',danger:'fa-xmark',warning:'fa-clock',info:'fa-info'};
        list.innerHTML = data.notifikasi.map(n => `
            <a href="${n.link || '#'}" onclick="bacaNotifAdmin(${n.id})" 
               class="d-flex gap-3 px-3 py-3 text-decoration-none border-bottom ${n.is_read == 0 ? 'bg-warning bg-opacity-10' : ''}" 
               style="transition:background .2s;">
                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:${colors[n.tipe]}20;">
                    <i class="fa-solid ${icons[n.tipe]} small" style="color:${colors[n.tipe]};"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-dark" style="font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.judul}</div>
                    <div class="text-muted" style="font-size:.72rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${n.pesan}</div>
                    <div class="text-muted opacity-60" style="font-size:.65rem;margin-top:2px;">${new Date(n.created_at).toLocaleString('id-ID',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})}</div>
                </div>
                ${n.is_read == 0 ? '<span class="flex-shrink-0 rounded-circle bg-warning align-self-center" style="width:8px;height:8px;"></span>' : ''}
            </a>`).join('');
    } catch(e) { console.error(e); }
}

async function bacaNotifAdmin(id) {
    await fetch(NOTIF_API_ADMIN + '?aksi=baca&id=' + id);
    muatNotifikasiAdmin();
}

async function bacaSemuaAdmin() {
    await fetch(NOTIF_API_ADMIN + '?aksi=baca&id=0');
    muatNotifikasiAdmin();
}

document.getElementById('notifBellAdmin').addEventListener('show.bs.dropdown', muatNotifikasiAdmin);
muatNotifikasiAdmin();
setInterval(muatNotifikasiAdmin, 60000);
</script>
