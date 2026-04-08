<?php
session_start();
require_once '../../config/database.php';

$current_page = 'aset';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// --- Logic Filter ---
$filter_search = $_GET['search'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';
$filter_unit = $_GET['unit'] ?? '';
$filter_kondisi = $_GET['kondisi'] ?? '';

$where_clauses = ["(k.nama_kategori != 'Ruangan' OR k.nama_kategori IS NULL)"];
if (!empty($filter_search)) {
    $search = $koneksi->real_escape_string($filter_search);
    $where_clauses[] = "(a.nama_aset LIKE '%$search%' OR a.kode_aset LIKE '%$search%' OR a.merk LIKE '%$search%')";
}
if (!empty($filter_kategori)) {
    $where_clauses[] = "a.id_kategori = '" . $koneksi->real_escape_string($filter_kategori) . "'";
}
if (!empty($filter_unit)) {
    $where_clauses[] = "a.unit_pengguna = '" . $koneksi->real_escape_string($filter_unit) . "'";
}
if (!empty($filter_kondisi)) {
    $where_clauses[] = "a.kondisi = '" . $koneksi->real_escape_string($filter_kondisi) . "'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Ambil data aset dengan JOIN ke kategori + Ruangan + Filter
$query_aset = "SELECT a.*, k.nama_kategori, k.icon as kat_icon, r.nama_ruangan, g.nama_gedung 
               FROM aset a 
               LEFT JOIN kategori k ON a.id_kategori = k.id 
               LEFT JOIN ruangan r ON a.id_ruangan = r.id
               LEFT JOIN gedung g ON r.id_gedung = g.id
               $where_sql
               ORDER BY a.id DESC";
$kumpulan_aset = $koneksi->query($query_aset);

// Ambil semua ruangan untuk dropdown
$lokasi_ruangan = $koneksi->query("SELECT r.*, g.nama_gedung FROM ruangan r JOIN gedung g ON r.id_gedung = g.id ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC");

// Ambil semua kategori untuk dropdown (Kecuali Ruangan)
$semua_kategori = $koneksi->query("SELECT * FROM kategori WHERE nama_kategori != 'Ruangan' ORDER BY nama_kategori ASC");
$kategori_list = [];
while($k = $semua_kategori->fetch_assoc()) {
    $kategori_list[] = $k;
}

// Ambil data Unik Unit dan Lokasi untuk Saran Input (Datalist)
$unit_unik = $koneksi->query("SELECT DISTINCT unit_pengguna FROM aset WHERE unit_pengguna != '' ORDER BY unit_pengguna ASC");
$lokasi_unik = $koneksi->query("SELECT DISTINCT lokasi_simpan FROM aset WHERE lokasi_simpan != '' ORDER BY lokasi_simpan ASC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<style>
/* --- BASE TABLE STYLES --- */
.table-responsive {
    transition: all 0.4s ease;
    overflow: visible !important;
}

/* --- LIST VIEW (Default/Flat) --- */
.view-list .table tbody tr {
    transition: background 0.2s ease !important;
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.03);
}
.view-list .table tbody tr:hover {
    background: #ffffff !important;
    transform: none !important; /* Flat row, no zoom for list */
    box-shadow: none !important; /* No card shadow for list */
    z-index: 1;
}
.view-list .table tbody tr:hover td {
    background: #ffffff !important;
}
/* Remove container border/shadow in list mode for a flatter look if desired */
.view-list.glass-card {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    backdrop-filter: none !important;
}

/* --- GRID VIEW LOGIC (Card & Zoom) --- */
.view-grid .table thead { display: none; }
.view-grid .table tbody {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 15px;
}
.view-grid .table tbody tr {
    display: flex;
    flex-direction: column;
    background: #ffffff !important;
    border: 1px solid rgba(0,0,0,0.05);
    border-radius: 20px;
    padding: 15px !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    height: 100%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.view-grid .table tbody tr:hover {
    transform: scale(1.02) translateY(-5px) !important;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
    z-index: 10;
}
.view-grid .table tbody td {
    display: flex;
    flex-direction: column;
    align-items: flex-start !important;
    border: none !important;
    padding: 6px 0 !important;
    width: 100% !important;
    text-align: left !important;
    background: transparent !important;
}
.view-grid td[data-label="Identitas"] { border-bottom: 1px solid rgba(0,0,0,0.05) !important; margin-bottom: 10px; padding-bottom: 10px !important; }
.view-grid td[data-label="Tindakan"] { margin-top: auto; padding-top: 15px !important; border-top: 1px dashed rgba(0,0,0,0.1) !important; flex-direction: row !important; }

/* Grid Labels */
.view-grid td:not([data-label="Identitas"]):not([data-label="Tindakan"])::before {
    content: attr(data-label);
    font-size: 0.65rem;
    color: #94a3b8;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 3px;
}

/* --- MOBILE LOGIC (RESPONSIVE CARD) --- */
@media (max-width: 768px) {
    /* --- Grid View on Mobile (3 Columns Gallery) --- */
    .table-responsive:not(.view-list) .table thead { display: none; }
    .table-responsive:not(.view-list) .table tbody {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 10px;
    }
    .table-responsive:not(.view-list) .table tbody tr {
        display: flex;
        flex-direction: column;
        background: #ffffff !important;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 12px;
        padding: 8px !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        height: 100%;
        transition: transform 0.2s ease;
        text-align: center;
        align-items: center;
    }
    .table-responsive:not(.view-list) .table tbody td {
        display: flex;
        flex-direction: column;
        border: none !important;
        padding: 2px 0 !important;
        width: 100% !important;
        align-items: center !important;
        text-align: center !important;
    }
    
    /* Shrinking Content for 3 Col */
    .table-responsive:not(.view-list) .bg-primary-soft { 
        width: 32px !important; 
        height: 32px !important; 
        margin-bottom: 5px;
    }
    .table-responsive:not(.view-list) .bg-primary-soft i { font-size: 0.8rem !important; }
    .table-responsive:not(.view-list) .item-name { font-size: 0.65rem !important; line-height: 1.2; }
    .table-responsive:not(.view-list) .text-muted.small { font-size: 0.55rem !important; }
    
    .table-responsive:not(.view-list) td[data-label="Identitas"] { border-bottom: none !important; margin-bottom: 0; padding-bottom: 0 !important; }
    .table-responsive:not(.view-list) td[data-label="Lokasi"], 
    .table-responsive:not(.view-list) td[data-label="Kondisi"], 
    .table-responsive:not(.view-list) td[data-label="Anggaran"] { font-size: 0.55rem !important; }

    /* Only show Name, Badge and specific data in 3-col to avoid clutter */
    .table-responsive:not(.view-list) td::before { display: none !important; }
    .table-responsive:not(.view-list) .badge { font-size: 0.5rem !important; padding: 2px 4px !important; }

    /* --- Compact List View on Mobile (100% Width) --- */
    .view-list .table thead { 
        display: table-header-group !important; 
    }
    .view-list .table thead th {
        font-size: 0.6rem !important;
        padding: 10px 5px !important;
        letter-spacing: 0;
        text-transform: capitalize;
    }
    .view-list .table tbody tr {
        display: table-row !important;
        background: transparent !important;
        border-bottom: 1px solid rgba(0,0,0,0.05) !important;
    }
    .view-list .table tbody td { 
        display: table-cell !important;
        padding: 10px 5px !important; 
        font-size: 0.7rem !important;
        border: none !important;
        width: auto !important;
        background: transparent !important;
    }
    .view-list .table { 
        width: 100% !important; 
        table-layout: fixed; 
        margin: 0 !important;
    }
    /* Column specifically */
    .view-list .table th:nth-child(1), .view-list .table td:nth-child(1) { width: 40%; }
    .view-list .table th:nth-child(2), .view-list .table td:nth-child(2) { width: 25%; }
    .view-list .table th:nth-child(3), .view-list .table td:nth-child(3) { width: 15%; text-align: center; }
    .view-list .table th:nth-child(4), .view-list .table td:nth-child(4) { width: 20%; text-align: right !important; }

    /* Hide redundant labels in list mode to save space */
    .view-list .table td::before { display: none !important; }
}

.cursor-pointer { cursor: pointer; }
.item-name { transition: color 0.2s; }
.view-list .table tbody tr:hover .item-name { color: var(--primary-color) !important; }

/* Filter Styling */
.filter-panel {
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.6);
}
</style>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Barang & Inventaris</h4>
                <p class="text-muted small mb-0">Kelola aset bergerak, barang habis pakai, dan inventaris lembaga lainnya.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 header-actions">
                <div class="btn-group shadow-sm rounded-pill overflow-hidden me-md-2" role="group">
                    <button type="button" class="btn btn-white border-0 px-3 py-2 btn-view-toggle active" id="btn-list-view" onclick="toggleView('list')">
                        <i class="fa-solid fa-list"></i>
                    </button>
                    <button type="button" class="btn btn-white border-0 px-3 py-2 btn-view-toggle" id="btn-grid-view" onclick="toggleView('grid')">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </button>
                </div>
                <button class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-medium d-flex align-items-center gap-2" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                    <i class="fa-solid fa-filter"></i> <span>Filter</span>
                </button>
                <a href="cetak_qr.php" target="_blank" class="btn btn-outline-dark rounded-pill px-3 shadow-sm fw-medium d-flex align-items-center gap-2">
                    <i class="fa-solid fa-print"></i> <span class="d-md-inline">Cetak QR</span>
                </a>
                <button class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-medium d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fa-solid fa-file-import"></i> <span class="d-md-inline">Import</span>
                </button>
                <button class="btn btn-primary btn-primary-add rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalPilihKategori">
                    <i class="fa-solid fa-plus-circle"></i> <span>Tambah Aset</span>
                </button>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="collapse <?= ($filter_search || $filter_kategori || $filter_unit || $filter_kondisi) ? 'show' : '' ?> mb-4" id="collapseFilter">
            <div class="filter-panel p-4 shadow-sm">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="filter-search" class="form-label small fw-bold text-muted">Cari Nama/Kode/Merk</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search opacity-50"></i></span>
                            <input type="text" id="filter-search" class="form-control border-start-0" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="Masukkan kata kunci...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="filter-kategori" class="form-label small fw-bold text-muted">Kategori</label>
                        <select id="filter-kategori" class="form-select form-select-sm" name="kategori">
                            <option value="">Semua Kategori</option>
                            <?php foreach($kategori_list as $kat): ?>
                                <option value="<?= $kat['id'] ?>" <?= $filter_kategori == $kat['id'] ? 'selected' : '' ?>><?= $kat['nama_kategori'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter-unit" class="form-label small fw-bold text-muted">Unit Pengguna</label>
                        <select id="filter-unit" class="form-select form-select-sm" name="unit">
                            <option value="">Semua Unit</option>
                            <?php 
                            $unit_unik->data_seek(0);
                            while($u = $unit_unik->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($u['unit_pengguna']) ?>" <?= $filter_unit == $u['unit_pengguna'] ? 'selected' : '' ?>><?= $u['unit_pengguna'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter-kondisi" class="form-label small fw-bold text-muted">Kondisi</label>
                        <select id="filter-kondisi" class="form-select form-select-sm" name="kondisi">
                            <option value="">Semua Kondisi</option>
                            <option value="baik" <?= $filter_kondisi == 'baik' ? 'selected' : '' ?>>✅ Baik</option>
                            <option value="rusak_ringan" <?= $filter_kondisi == 'rusak_ringan' ? 'selected' : '' ?>>⚠️ Rusak Ringan</option>
                            <option value="rusak_berat" <?= $filter_kondisi == 'rusak_berat' ? 'selected' : '' ?>>❌ Rusak Berat</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Terapkan</button>
                        <a href="index.php" class="btn btn-light btn-sm rounded-pill px-3">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show glass-effect mb-4 border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #166534;">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Inventory List -->
        <div id="table-container" class="table-responsive glass-card shadow-sm border border-white-50 p-2 p-md-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama Inventaris</th>
                        <th>Lokasi</th>
                        <th class="text-center">Kondisi</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($kumpulan_aset && $kumpulan_aset->num_rows > 0): ?>
                        <?php while ($row = $kumpulan_aset->fetch_assoc()): ?>
                        <tr class="inventory-row cursor-pointer" data-json="<?= htmlspecialchars(json_encode($row)) ?>">
                            <td class="px-4" data-label="Identitas">
                                <div class="text-decoration-none d-flex align-items-center gap-3 group-item">
                                    <div class="bg-primary-soft text-primary rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                                        <i class="fa-solid fa-<?= $row['kat_icon'] ? $row['kat_icon'] : 'box' ?> fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark lh-1 mb-1 item-name"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                        <div class="text-muted small">
                                            <span class="badge bg-light text-dark border-0 fw-normal px-0 me-2"><?= htmlspecialchars($row['kode_aset']) ?></span>
                                            <span class="opacity-75"><?= htmlspecialchars($row['merk'] . ' ' . $row['warna']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Lokasi">
                                <div class="lh-sm">
                                    <div class="fw-medium text-dark small mb-1"><?= htmlspecialchars($row['unit_pengguna']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-location-dot me-1 opacity-50"></i> 
                                        <?php if($row['id_ruangan']): ?>
                                            <span class="text-primary fw-bold"><?= htmlspecialchars($row['nama_ruangan']) ?></span> (<?= htmlspecialchars($row['nama_gedung']) ?>)
                                        <?php else: ?>
                                            <?= htmlspecialchars($row['lokasi_simpan'] ? $row['lokasi_simpan'] : '-') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" data-label="Kondisi">
                                <div class="mb-1">
                                    <?php 
                                        $cond_class = 'bg-success';
                                        if($row['kondisi'] == 'rusak_ringan') $cond_class = 'bg-warning text-dark';
                                        if($row['kondisi'] == 'rusak_berat') $cond_class = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $cond_class ?> rounded-pill px-2" style="font-size: 0.65rem;"><?= strtoupper(str_replace('_', ' ', $row['kondisi'])) ?></span>
                                </div>
                                <?php if($row['ada_garansi'] == 'Y'): ?>
                                    <?php 
                                        $today = date('Y-m-d');
                                        $expired = ($row['garansi_sampai'] && $row['garansi_sampai'] < $today);
                                    ?>
                                    <span class="text-<?= $expired?'danger':'primary' ?> small fw-bold" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-shield-halved me-1"></i> <?= $row['garansi_sampai'] ? date('d/m/y', strtotime($row['garansi_sampai'])) : '-' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Anggaran">
                                <div class="fw-bold text-dark small">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    Tgl: <?= date('d/m/y', strtotime($row['tgl_beli'])) ?> 
                                    <span class="badge bg-light text-muted border py-0 px-1 ms-1"><?= $row['tahun_anggaran'] ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        // Simpan data untuk modal diluar loop agar stacking context benar
                        $list_data_aset[] = $row;
                        ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open d-block fs-1 mb-3 opacity-25"></i>
                                Data aset kosong.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab-container d-md-none">
        <button class="fab-btn" data-bs-toggle="modal" data-bs-target="#modalPilihKategori">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>
</main>

<!-- Modal Step 1: Pilih Kategori -->
<div class="modal fade" id="modalPilihKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-effect border-0">
            <div class="modal-header border-0 px-4 pt-4 text-center d-block">
                <h5 class="modal-title font-heading fw-bold fs-4">Pilih Kategori Aset</h5>
                <p class="text-muted small">Tentukan jenis inventaris yang ingin Anda daftarkan.</p>
            </div>
            <div class="modal-body px-4 pb-5">
                <div class="row g-3">
                    <?php foreach($kategori_list as $kat): ?>
                    <div class="col-6 col-md-4">
                        <div class="category-select-card glass-card p-4 text-center cursor-pointer h-100 d-flex flex-column align-items-center justify-content-center border-2" 
                             onclick="selectCategoryAndOpenAdd('<?= $kat['id'] ?>', '<?= htmlspecialchars($kat['nama_kategori']) ?>')">
                            <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fa-solid fa-<?= $kat['icon'] ? $kat['icon'] : 'box' ?> fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0"><?= $kat['nama_kategori'] ?></h6>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-select-card {
    transition: all 0.3s ease;
    border: 2px solid transparent !important;
}
.category-select-card:hover {
    transform: translateY(-5px);
    background: white !important;
    border-color: var(--primary-color) !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
</style>

<!-- Modal Tambah Aset -->
<div class="modal fade" id="modalTambahAset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title font-heading fw-bold">Registrasi Aset Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body px-4 py-3 text-start">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="add-kode" class="form-label small fw-bold text-muted">Kode Inventaris</label>
                            <input type="text" id="add-kode" class="form-control" name="kode_aset" placeholder="Otomatis (kosongkan) / Manual">
                        </div>
                        <div class="col-md-9">
                            <label for="add-nama" class="form-label small fw-bold text-muted">Nama Barang / Ruangan</label>
                            <input type="text" id="add-nama" class="form-control" name="nama_aset" placeholder="e.g. TV Android / Ruang Multimedia" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="add-kategori" class="form-label small fw-bold text-muted">Kategori</label>
                            <select id="add-kategori" class="form-select select-kategori-template" name="id_kategori" data-target-finance="finance-section-add" data-target-spec="spec-section-add">
                                <?php foreach($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" data-nama="<?= strtolower($kat['nama_kategori']) ?>"><?= $kat['nama_kategori'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="add-merk" class="form-label small fw-bold text-muted">Merk</label>
                            <input type="text" id="add-merk" class="form-control" name="merk" placeholder="e.g. Samsung / Sony">
                        </div>
                        <div class="col-md-4">
                            <label for="add-warna" class="form-label small fw-bold text-muted">Warna</label>
                            <input type="text" id="add-warna" class="form-control" name="warna" placeholder="e.g. Hitam">
                        </div>

                        <div class="col-md-6">
                            <label for="add-unit" class="form-label small fw-bold text-muted">Unit Pengguna (Pemegang)</label>
                            <input type="text" id="add-unit" class="form-control" name="unit_pengguna" placeholder="e.g. SDIT An Nadzir" list="list-unit">
                        </div>
                        <div class="col-md-4">
                            <label for="add-id-ruangan" class="form-label small fw-bold text-muted">Lokasi Ruangan (Master)</label>
                            <select id="add-id-ruangan" class="form-select" name="id_ruangan">
                                <option value="">-- Manual / Belum Diset --</option>
                                <?php 
                                $lokasi_ruangan->data_seek(0);
                                while($lr = $lokasi_ruangan->fetch_assoc()): ?>
                                    <option value="<?= $lr['id'] ?>"><?= $lr['nama_ruangan'] ?> (<?= $lr['nama_gedung'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="add-lokasi" class="form-label small fw-bold text-muted">Lokasi Spesifik (Opsional)</label>
                            <input type="text" id="add-lokasi" class="form-control" name="lokasi_simpan" placeholder="e.g. Di Atas Meja/Pojok" list="list-lokasi">
                        </div>
                        <div class="col-md-8">
                            <label for="add-pj" class="form-label small fw-bold text-muted">Penanggung Jawab / Pengelola</label>
                            <input type="text" id="add-pj" class="form-control" name="penanggung_jawab" placeholder="e.g. Ust. Ahmad / Divisi Sarpras">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="add-bisa-dipinjam" class="form-label small fw-bold text-muted">Bisa Dipinjam? (Publik)</label>
                            <select id="add-bisa-dipinjam" class="form-select" name="bisa_dipinjam">
                                <option value="Y">🌐 Bisa Dipinjam</option>
                                <option value="N">🔒 Internal Saja</option>
                            </select>
                        </div>

                        <div class="col-12"><hr class="my-1 opacity-5"></div>
                        
                        <div id="finance-section-add" class="row g-3 p-0 m-0">
                            <div class="col-md-4">
                                <label for="add-harga" class="form-label small fw-bold text-muted">Harga Beli (Rp)</label>
                                <input type="number" id="add-harga" class="form-control" name="harga_beli" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label for="add_tgl_beli" class="form-label small fw-bold text-muted">Tanggal Perolehan</label>
                                <input type="date" id="add_tgl_beli" class="form-control" name="tgl_beli" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="add-kondisi" class="form-label small fw-bold text-muted">Kondisi</label>
                                <select id="add-kondisi" class="form-select" name="kondisi">
                                    <option value="baik">✅ Baik</option>
                                    <option value="rusak_ringan">⚠️ Rusak Ringan</option>
                                    <option value="rusak_berat">❌ Rusak Berat</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="add_ada_garansi" class="form-label small fw-bold text-muted">Ada Garansi?</label>
                                <select id="add_ada_garansi" class="form-select" name="ada_garansi">
                                    <option value="N">Tidak Ada</option>
                                    <option value="Y">Ya, Bergaransi</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="add-durasi-garansi" class="form-label small fw-bold text-muted">Cepat Atur Garansi</label>
                                <select id="add-durasi-garansi" class="form-select select-duration" data-target="add_garansi_sampai" data-source="add_tgl_beli">
                                    <option value="">Pilih Durasi</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">1 Tahun</option>
                                    <option value="24">2 Tahun</option>
                                    <option value="36">3 Tahun</option>
                                    <option value="60">5 Tahun</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="add_garansi_sampai" class="form-label small fw-bold text-muted">Garansi Sampai</label>
                                <input type="date" id="add_garansi_sampai" class="form-control" name="garansi_sampai">
                            </div>

                            <div class="col-md-8">
                                <label for="add-divisi" class="form-label small fw-bold text-muted">Sumber Anggaran</label>
                                <input type="text" id="add-divisi" class="form-control" name="divisi_pembeli" placeholder="e.g. Divisi LRC">
                            </div>
                            <div class="col-md-6">
                                <label for="add-toko" class="form-label small fw-bold text-muted">Toko Pembelian</label>
                                <input type="text" id="add-toko" class="form-control" name="toko_pembelian" placeholder="Toko/Vendor">
                            </div>
                            <div class="col-md-6">
                                <label for="add-kota" class="form-label small fw-bold text-muted">Kota Toko</label>
                                <input type="text" id="add-kota" class="form-control" name="kota_pembelian" placeholder="Kota Lokasi Toko">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-3">
                    <label for="add-foto" class="form-label small fw-bold text-muted">Foto Aset <span class="fw-normal">(Opsional, maks. 3MB - JPG/PNG/WEBP)</span></label>
                    <input type="file" id="add-foto" class="form-control" name="foto_aset" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Foto akan tampil di halaman landing untuk mempublish aset kepada pegawai.</div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Simpan Aset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Import Data Aset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="import">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="bg-primary-soft text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fa-solid fa-file-excel fs-1"></i>
                        </div>
                        <p class="text-muted small">Pilih file Excel (XLSX/XLS/CSV) yang berisi data aset. <br>Gunakan format kolom yang sesuai dengan sistem.</p>
                        <a href="download_template.php" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="fa-solid fa-file-excel me-1"></i> Download Template
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <label for="import-file" class="form-label small fw-bold">File Excel/CSV</label>
                        <input type="file" id="import-file" class="form-control shadow-none" name="file_aset" accept=".csv, .xlsx, .xls" required>
                    </div>

                    <div class="bg-light rounded-3 p-3">
                        <p class="fw-bold small mb-1">Urutan Kolom CSV (Minimal):</p>
                        <p class="text-muted mb-0" style="font-size: 0.65rem;">
                            Kode Aset, Nama Aset, Merk, Warna, Unit Pengguna, Lokasi, Harga Beli, Tahun Anggaran
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Datalist untuk Saran Input -->
<datalist id="list-unit">
    <?php while($u = $unit_unik->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($u['unit_pengguna']) ?>">
    <?php endwhile; ?>
</datalist>

<datalist id="list-lokasi">
    <?php while($l = $lokasi_unik->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($l['lokasi_simpan']) ?>">
    <?php endwhile; ?>
</datalist>

<script>
function toggleView(view) {
    const container = document.getElementById('table-container');
    const btnList = document.getElementById('btn-list-view');
    const btnGrid = document.getElementById('btn-grid-view');
    
    if (view === 'grid') {
        container.classList.add('view-grid');
        container.classList.remove('view-list');
        btnGrid.classList.add('active', 'btn-primary');
        btnGrid.classList.remove('btn-white');
        btnList.classList.remove('active', 'btn-primary');
        btnList.classList.add('btn-white');
        localStorage.setItem('inventory-view', 'grid');
    } else {
        container.classList.remove('view-grid');
        container.classList.add('view-list');
        btnList.classList.add('active', 'btn-primary');
        btnList.classList.remove('btn-white');
        btnGrid.classList.remove('active', 'btn-primary');
        btnGrid.classList.add('btn-white');
        localStorage.setItem('inventory-view', 'list');
    }
}
</script>

<!-- Flyout Action (Modern Sidebar Flyout) -->
<div class="offcanvas offcanvas-end border-0 shadow-lg glass-effect" tabindex="-1" id="flyoutAset" aria-labelledby="flyoutAsetLabel" style="width: 400px; background: rgba(255,255,255,0.8) !important; backdrop-filter: blur(20px) !important;">
    <div class="offcanvas-header border-bottom px-4 py-3">
        <h5 class="offcanvas-title font-heading fw-bold" id="flyoutAsetLabel">Detail & Aksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <!-- Quick Info -->
        <div id="flyout-content">
            <div class="text-center mb-4">
                <div class="bg-primary-soft text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                    <i id="flyout-icon" class="fa-solid fa-box fs-1"></i>
                </div>
                <h4 id="flyout-nama" class="fw-bold text-dark mb-1">-</h4>
                <p id="flyout-kode" class="text-muted small mb-0">-</p>
            </div>

            <div class="list-group list-group-flush rounded-4 overflow-hidden border">
                <div class="list-group-item p-3 border-0 bg-light-soft">
                    <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Lokasi Saat Ini</div>
                    <div id="flyout-lokasi" class="fw-bold text-dark">-</div>
                </div>
                <div class="list-group-item p-3 border-0 bg-light-soft">
                    <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Penanggung Jawab</div>
                    <div id="flyout-pj" class="fw-bold text-dark">-</div>
                </div>
                <div class="list-group-item p-3 border-0 bg-light-soft">
                    <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Kondisi Barang</div>
                    <div id="flyout-kondisi" class="fw-bold text-dark">-</div>
                </div>
            </div>

            <div class="mt-5 d-grid gap-3">
                <a id="flyout-link-detail" href="#" class="btn btn-outline-primary rounded-pill py-2 fw-bold">
                    <i class="fa-solid fa-circle-info me-2"></i> Lihat Detail Lengkap
                </a>
                <button type="button" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm" onclick="triggerEditFromFlyout()">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit Data Inventaris
                </button>
                <button type="button" class="btn btn-danger-soft text-danger rounded-pill py-2 fw-bold" onclick="triggerDeleteFromFlyout()">
                    <i class="fa-solid fa-trash-can me-2"></i> Hapus Inventaris
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Aset (Universal - Single Modal) -->
<div class="modal fade" id="modalEditUniversal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title font-heading fw-bold">Update Data Inventaris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses.php" method="POST" enctype="multipart/form-data" id="formEditAset">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="foto_aset_lama" id="edit-foto-lama">
                <div class="modal-body px-4 py-3 text-start">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Kode Inventaris</label>
                            <input type="text" class="form-control" name="kode_aset" id="edit-kode" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small fw-bold text-muted">Nama Barang / Ruangan</label>
                            <input type="text" class="form-control" name="nama_aset" id="edit-nama" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Kategori</label>
                            <select class="form-select" name="id_kategori" id="edit-kategori">
                                <?php foreach($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" data-nama="<?= strtolower($kat['nama_kategori']) ?>"><?= $kat['nama_kategori'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Merk</label>
                            <input type="text" class="form-control" name="merk" id="edit-merk">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Warna</label>
                            <input type="text" class="form-control" name="warna" id="edit-warna">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Unit Pengguna</label>
                            <input type="text" class="form-control" name="unit_pengguna" id="edit-unit" list="list-unit">
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="form-label small fw-bold text-muted">Lokasi Ruangan (Master)</label>
                            <select class="form-select" name="id_ruangan" id="edit-id_ruangan">
                                <option value="">-- Manual / Belum Diset --</option>
                                <?php 
                                $lokasi_ruangan->data_seek(0);
                                while($lr = $lokasi_ruangan->fetch_assoc()): ?>
                                    <option value="<?= $lr['id'] ?>"><?= $lr['nama_ruangan'] ?> (<?= $lr['nama_gedung'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="form-label small fw-bold text-muted">Lokasi Spesifik (Opsional)</label>
                            <input type="text" class="form-control" name="lokasi_simpan" id="edit-lokasi-simpan" list="list-lokasi">
                        </div>
                        <div class="col-md-8 text-start">
                            <label class="form-label small fw-bold text-muted">Penanggung Jawab / Pengelola</label>
                            <input type="text" class="form-control" name="penanggung_jawab" id="edit-pj">
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="form-label small fw-bold text-muted">Status Pinjam (Publik)</label>
                            <select class="form-select" name="bisa_dipinjam" id="edit-bisa-dipinjam">
                                <option value="Y">🌐 Bisa Dipinjam</option>
                                <option value="N">🔒 Internal Saja</option>
                            </select>
                        </div>
                        
                        <div class="row g-3 p-0 m-0 finance-edit-section mt-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Kondisi</label>
                                <select class="form-select" name="kondisi" id="edit-kondisi">
                                    <option value="baik">✅ Baik</option>
                                    <option value="rusak_ringan">⚠️ Rusak Ringan</option>
                                    <option value="rusak_berat">❌ Rusak Berat</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Harga Beli</label>
                                <input type="number" class="form-control" name="harga_beli" id="edit-harga">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Ada Garansi?</label>
                                <select class="form-select" name="ada_garansi" id="edit-ada-garansi">
                                    <option value="N">Tidak Ada</option>
                                    <option value="Y">Ya, Bergaransi</option>
                                </select>
                            </div>
                        <div class="col-12">
                            <div id="edit-foto-preview" class="mb-2"></div>
                            <label for="edit-foto" class="form-label small fw-bold text-muted">Ganti Foto Aset <span class="text-muted fw-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                            <input type="file" id="edit-foto" class="form-control" name="foto_aset" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>
                </div>
            </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Always default to list view. Only use saved preference if explicitly set to 'grid'.
    const savedView = localStorage.getItem('inventory-view');
    toggleView(savedView === 'grid' ? 'grid' : 'list');
    // Check for showModal param
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('showModal') === 'tambah') {
        const myModal = new bootstrap.Modal(document.getElementById('modalTambahAset'));
        myModal.show();
    }
    // Inventory Row Clicks (Using Event Delegation for better reliability)
    const tableContainer = document.getElementById('table-container');
    if (tableContainer) {
        tableContainer.addEventListener('click', function(e) {
            const row = e.target.closest('.inventory-row');
            if (!row) return;

            // Prevent if clicking specific buttons or links inside
            if (e.target.closest('button, a')) return;
            
            try {
                const data = JSON.parse(row.getAttribute('data-json'));
                openActionFlyout(data);
            } catch (err) {
                console.error("Error parsing row data:", err);
            }
        });
    }
});

let currentSelectedAset = null;

function openActionFlyout(data) {
    currentSelectedAset = data;
    
    // Populate Flyout
    document.getElementById('flyout-nama').textContent = data.nama_aset;
    document.getElementById('flyout-kode').textContent = data.kode_aset;
    document.getElementById('flyout-pj').textContent = data.penanggung_jawab || '-';
    document.getElementById('flyout-kondisi').textContent = (data.kondisi || 'Baik').toUpperCase().replace('_', ' ');
    document.getElementById('flyout-icon').className = 'fa-solid fa-' + (data.kat_icon || 'box') + ' fs-1';
    
    let lokasiText = data.nama_ruangan ? `${data.nama_ruangan} (${data.nama_gedung})` : (data.lokasi_simpan || '-');
    document.getElementById('flyout-lokasi').textContent = lokasiText;
    
    document.getElementById('flyout-link-detail').href = 'detail.php?id=' + data.id;

    // Show Offcanvas
    const flyoutEl = document.getElementById('flyoutAset');
    const flyout = bootstrap.Offcanvas.getOrCreateInstance(flyoutEl);
    flyout.show();
}

function triggerEditFromFlyout() {
    if (!currentSelectedAset) return;
    
    const data = currentSelectedAset;
    
    // Populate Modal Edit
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-kode').value = data.kode_aset;
    document.getElementById('edit-nama').value = data.nama_aset;
    document.getElementById('edit-kategori').value = data.id_kategori;
    document.getElementById('edit-merk').value = data.merk;
    document.getElementById('edit-warna').value = data.warna;
    document.getElementById('edit-unit').value = data.unit_pengguna;
    document.getElementById('edit-lokasi-simpan').value = data.lokasi_simpan;
    document.getElementById('edit-id_ruangan').value = data.id_ruangan || '';
    document.getElementById('edit-pj').value = data.penanggung_jawab;
    document.getElementById('edit-bisa-dipinjam').value = data.bisa_dipinjam;
    document.getElementById('edit-kondisi').value = data.kondisi;
    document.getElementById('edit-harga').value = data.harga_beli;
    document.getElementById('edit-ada-garansi').value = data.ada_garansi;
    document.getElementById('edit-foto-lama').value = data.foto_aset || '';
    
    // Show foto preview
    const previewEl = document.getElementById('edit-foto-preview');
    if (data.foto_aset) {
        previewEl.innerHTML = `<div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3 mb-2">
            <img src="../../assets/uploads/aset/${data.foto_aset}" class="rounded-3 object-fit-cover shadow-sm" style="width:80px;height:60px;">
            <div><div class="small fw-bold text-dark">Foto saat ini</div><div class="text-muted" style="font-size:0.75rem;">${data.foto_aset}</div></div>
        </div>`;
    } else {
        previewEl.innerHTML = '<p class="text-muted small mb-2"><i class="fa-solid fa-image me-1 opacity-50"></i> Belum ada foto</p>';
    }

    // Close Flyout first
    const flyout = bootstrap.Offcanvas.getInstance(document.getElementById('flyoutAset'));
    if (flyout) flyout.hide();

    // Show Modal
    setTimeout(() => {
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEditUniversal'));
        modalEdit.show();
    }, 500);
}

function triggerDeleteFromFlyout() {
    if (!currentSelectedAset) return;
    if (confirm('Yakin ingin menghapus data ini?')) {
        window.location = 'proses.php?aksi=hapus&id=' + currentSelectedAset.id;
    }
}

function selectCategoryAndOpenAdd(id, name) {
    const modalPilih = bootstrap.Modal.getInstance(document.getElementById('modalPilihKategori'));
    if (modalPilih) modalPilih.hide();
    
    setTimeout(() => {
        const selectKat = document.querySelector('#modalTambahAset select[name="id_kategori"]');
        if (selectKat) {
            selectKat.value = id;
            selectKat.dispatchEvent(new Event('change'));
        }
        const myModal = new bootstrap.Modal(document.getElementById('modalTambahAset'));
        myModal.show();
    }, 400);
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate Warranty
    document.querySelectorAll('.select-duration').forEach(select => {
        select.addEventListener('change', function() {
            const months = parseInt(this.value);
            if (!months) return;
            const targetId = this.getAttribute('data-target');
            const sourceId = this.getAttribute('data-source');
            const sourceInput = document.getElementById(sourceId);
            const targetInput = document.getElementById(targetId);
            if (sourceInput && sourceInput.value) {
                let date = new Date(sourceInput.value);
                date.setMonth(date.getMonth() + months);
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                targetInput.value = `${y}-${m}-${d}`;
                const adaGaransiId = targetId.includes('edit') ? targetId.replace('garansi_sampai', 'ada_garansi') : 'add_ada_garansi';
                const elAda = document.getElementById(adaGaransiId);
                if (elAda) elAda.value = 'Y';
            }
        });
    });

    // Category Template Switcher
    document.querySelectorAll('.select-kategori-template, .form-select[name="id_kategori"]').forEach(select => {
        const toggleFields = () => {
            const selectedOption = select.options[select.selectedIndex];
            const selectedText = (selectedOption.getAttribute('data-nama') || selectedOption.textContent || '').toLowerCase();
            const modalBody = select.closest('.modal-body');
            if (!modalBody) return;
            const inputMerk = modalBody.querySelector('input[name="merk"]');
            const inputWarna = modalBody.querySelector('input[name="warna"]');
            const financeSection = modalBody.querySelector('#finance-section-add');
            const editFinanceRow = modalBody.querySelector('.finance-edit-section');
            const isRoom = selectedText.includes('ruang') || selectedText.includes('bangunan') || selectedText.includes('fasilitas') || selectedText.includes('umum');
            if (inputMerk && inputMerk.parentElement) inputMerk.parentElement.style.display = isRoom ? 'none' : 'block';
            if (inputWarna && inputWarna.parentElement) inputWarna.parentElement.style.display = isRoom ? 'none' : 'block';
            if (financeSection) financeSection.style.display = isRoom ? 'none' : 'flex';
            if (editFinanceRow) editFinanceRow.style.display = isRoom ? 'none' : 'flex';
        };
        select.addEventListener('change', toggleFields);
        toggleFields();
    });
});
</script>

<?php include '../layouts/footer.php'; ?>
