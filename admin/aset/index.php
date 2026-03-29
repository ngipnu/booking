<?php
session_start();
require_once '../../config/database.php';

$current_page = 'aset';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil data aset dengan JOIN ke kategori
$query_aset = "SELECT a.*, k.nama_kategori, k.icon as kat_icon 
               FROM aset a 
               LEFT JOIN kategori k ON a.id_kategori = k.id 
               ORDER BY a.id DESC";
$kumpulan_aset = $koneksi->query($query_aset);

// Ambil semua kategori untuk dropdown
$semua_kategori = $koneksi->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
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

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Inventaris Detail</h4>
                <p class="text-muted small mb-0">Pelacakan aset, kategori dinamis, dan masa berlaku garansi.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-medium d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fa-solid fa-file-import"></i> <span class="d-none d-sm-inline">Import Excel</span>
                </button>
                <a href="../kategori/index.php" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-medium d-flex align-items-center gap-2">
                    <i class="fa-solid fa-tags"></i> <span class="d-none d-sm-inline">Kelola Kategori</span>
                </a>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahAset">
                    <i class="fa-solid fa-plus-circle"></i> <span>Tambah Aset</span>
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show glass-effect mb-4 border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #166534;">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Inventory Table (Clean List Style) -->
        <div class="table-responsive bg-white rounded-4 shadow-sm border border-white-50">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-50">
                        <th class="ps-4">Item & Spesifikasi</th>
                        <th>Unit / Lokasi</th>
                        <th class="text-center">Kondisi / Garansi</th>
                        <th class="text-end">Valuasi & Thn Anggaran</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                        <tbody>
                            <?php if ($kumpulan_aset && $kumpulan_aset->num_rows > 0): ?>
                                <?php while ($row = $kumpulan_aset->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-soft text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; flex-shrink: 0;">
                                                <i class="fa-solid fa-<?= $row['kat_icon'] ? $row['kat_icon'] : 'box' ?> fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark lh-1 mb-1"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                                <div class="text-muted small">
                                                    <span class="badge bg-light text-dark border-0 fw-normal px-0 me-2"><?= htmlspecialchars($row['kode_aset']) ?></span>
                                                    <span class="opacity-75"><?= htmlspecialchars($row['merk']) ?> <?= htmlspecialchars($row['warna']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="lh-sm">
                                            <div class="fw-medium text-dark small mb-1"><?= htmlspecialchars($row['unit_pengguna']) ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><i class="fa-solid fa-location-dot me-1 opacity-50"></i> <?= htmlspecialchars($row['lokasi_simpan'] ? $row['lokasi_simpan'] : '-') ?></div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="mb-1">
                                            <?php 
                                                $cond_class = 'bg-success';
                                                if($row['kondisi'] == 'rusak_ringan') $cond_class = 'bg-warning text-dark';
                                                if($row['kondisi'] == 'rusak_berat') $cond_class = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $cond_class ?> rounded-pill px-2" style="font-size: 0.6rem;"><?= strtoupper(str_replace('_', ' ', $row['kondisi'])) ?></span>
                                        </div>
                                        <?php if($row['ada_garansi'] == 'Y'): ?>
                                            <?php 
                                                $today = date('Y-m-d');
                                                $expired = ($row['garansi_sampai'] < $today);
                                            ?>
                                            <span class="text-<?= $expired?'danger':'primary' ?> small fw-bold" style="font-size: 0.65rem;">
                                                <i class="fa-solid fa-shield-halved me-1"></i> Garansi: <?= date('d/m/y', strtotime($row['garansi_sampai'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-end">
                                        <div class="fw-bold text-dark small">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;">
                                            Tgl: <?= date('d/m/y', strtotime($row['tgl_beli'])) ?> 
                                            <span class="badge bg-light text-muted border py-0 px-1 ms-1"><?= $row['tahun_anggaran'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-white border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id'] ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <a href="proses.php?aksi=hapus&id=<?= $row['id'] ?>" class="btn btn-sm btn-white border shadow-sm text-danger" onclick="return confirm('Hapus aset ini?');"><i class="fa-solid fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content glass-effect">
                                            <div class="modal-header border-0 px-4 pt-4">
                                                <h5 class="modal-title font-heading fw-bold">Detail Inventaris: <?= htmlspecialchars($row['kode_aset']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="proses.php" method="POST">
                                                <input type="hidden" name="aksi" value="edit">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <div class="modal-body px-4 py-3">
                                                    <div class="row g-3 text-start">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold text-muted">Nama Barang</label>
                                                            <input type="text" class="form-control" name="nama_aset" value="<?= htmlspecialchars($row['nama_aset']) ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-bold text-muted">Merk</label>
                                                            <input type="text" class="form-control" name="merk" value="<?= htmlspecialchars($row['merk']) ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-bold text-muted">Warna</label>
                                                            <input type="text" class="form-control" name="warna" value="<?= htmlspecialchars($row['warna']) ?>">
                                                        </div>
                                                        <div class="col-md-4 text-start">
                                                            <label class="form-label small fw-bold text-muted">Kategori</label>
                                                            <select class="form-select" name="id_kategori">
                                                                <?php foreach($kategori_list as $kat): ?>
                                                                    <option value="<?= $kat['id'] ?>" <?= $row['id_kategori']==$kat['id']?'selected':'' ?>><?= $kat['nama_kategori'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Unit Pengguna (Pemegang)</label>
                                                            <input type="text" class="form-control" name="unit_pengguna" value="<?= htmlspecialchars($row['unit_pengguna']) ?>" list="list-unit">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Lokasi / Ruangan</label>
                                                            <input type="text" class="form-control" name="lokasi_simpan" value="<?= htmlspecialchars($row['lokasi_simpan']) ?>" list="list-lokasi">
                                                        </div>
                                                        <div class="col-12"><hr class="my-1 opacity-5"></div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Harga Beli (Rp)</label>
                                                            <input type="number" class="form-control" name="harga_beli" value="<?= (int)$row['harga_beli'] ?>">
                                                        </div>
                                                        <div class="col-md-4 text-start">
                                                            <label class="form-label small fw-bold text-muted">Tanggal Perolehan</label>
                                                            <input type="date" class="form-control" id="edit_tgl_beli_<?= $row['id'] ?>" name="tgl_beli" value="<?= $row['tgl_beli'] ?>">
                                                        </div>
                                                        <div class="col-md-4 text-start">
                                                            <label class="form-label small fw-bold text-muted">Kondisi Saat Ini</label>
                                                            <select class="form-select" name="kondisi">
                                                                <option value="baik" <?= $row['kondisi']=='baik'?'selected':'' ?>>✅ Baik</option>
                                                                <option value="rusak_ringan" <?= $row['kondisi']=='rusak_ringan'?'selected':'' ?>>⚠️ Rusak Ringan</option>
                                                                <option value="rusak_berat" <?= $row['kondisi']=='rusak_berat'?'selected':'' ?>>❌ Rusak Berat</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Ada Garansi?</label>
                                                            <select class="form-select" name="ada_garansi" id="edit_ada_garansi_<?= $row['id'] ?>">
                                                                <option value="Y" <?= $row['ada_garansi']=='Y'?'selected':'' ?>>Ya, Bergaransi</option>
                                                                <option value="N" <?= $row['ada_garansi']=='N'?'selected':'' ?>>Tidak Ada</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Durasi Garansi</label>
                                                            <select class="form-select select-duration" data-target="edit_garansi_sampai_<?= $row['id'] ?>" data-source="edit_tgl_beli_<?= $row['id'] ?>">
                                                                <option value="">Pilih Durasi</option>
                                                                <option value="6">6 Bulan</option>
                                                                <option value="12">1 Tahun</option>
                                                                <option value="24">2 Tahun</option>
                                                                <option value="36">3 Tahun</option>
                                                                <option value="60">5 Tahun</option>
                                                            </select>
                                                            <input type="date" class="form-control mt-2" name="garansi_sampai" id="edit_garansi_sampai_<?= $row['id'] ?>" value="<?= $row['garansi_sampai'] ?>">
                                                        </div>
                                                        <div class="col-md-4 text-start">
                                                            <label class="form-label small fw-bold text-muted">Kondisi Saat Ini</label>
                                                            <select class="form-select" name="kondisi">
                                                                <option value="baik" <?= $row['kondisi']=='baik'?'selected':'' ?>>✅ Baik</option>
                                                                <option value="rusak_ringan" <?= $row['kondisi']=='rusak_ringan'?'selected':'' ?>>⚠️ Rusak Ringan</option>
                                                                <option value="rusak_berat" <?= $row['kondisi']=='rusak_berat'?'selected':'' ?>>❌ Rusak Berat</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Sumber Anggaran (Unit)</label>
                                                            <input type="text" class="form-control" name="divisi_pembeli" value="<?= htmlspecialchars($row['divisi_pembeli']) ?>" placeholder="e.g. Divisi LRC">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Tahun Anggaran</label>
                                                            <input type="text" class="form-control" name="tahun_anggaran" value="<?= $row['tahun_anggaran'] ?>" placeholder="YYYY">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold text-muted">Toko Pembelian</label>
                                                            <input type="text" class="form-control" name="toko_pembelian" value="<?= htmlspecialchars($row['toko_pembelian']) ?>">
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
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Data aset kosong.</td>
                                </tr>
                            <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Tambah Aset -->
<div class="modal fade" id="modalTambahAset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-effect">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title font-heading fw-bold">Registrasi Aset Institusi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body px-4 py-3 text-start">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Kode Inventaris</label>
                            <input type="text" class="form-control" name="kode_aset" placeholder="INV-..." required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small fw-bold text-muted">Nama Barang / Ruangan</label>
                            <input type="text" class="form-control" name="nama_aset" placeholder="e.g. Ruang Multimedia / TV Android" required>
                        </div>
                        
                        <!-- Specs Section (Hidden for Rooms) -->
                        <div id="spec-section-add" class="row g-3 p-0 m-0">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Merk</label>
                                <input type="text" class="form-control" name="merk" placeholder="e.g. TCL / Samsung">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Warna</label>
                                <input type="text" class="form-control" name="warna" placeholder="e.g. Putih">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Kategori</label>
                                <select class="form-select select-kategori-template" name="id_kategori" data-target-finance="finance-section-add" data-target-spec="spec-section-add">
                                    <?php foreach($kategori_list as $kat): ?>
                                        <option value="<?= $kat['id'] ?>" data-nama="<?= strtolower($kat['nama_kategori']) ?>"><?= $kat['nama_kategori'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Unit Pengguna (Pemegang)</label>
                            <input type="text" class="form-control" name="unit_pengguna" placeholder="e.g. SDIT An Nadzir" list="list-unit">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Lokasi / Ruangan Simpan</label>
                            <input type="text" class="form-control" name="lokasi_simpan" placeholder="e.g. Ruang Kelas 3B" list="list-lokasi">
                        </div>
                        
                        <!-- Finance Section -->
                        <div id="finance-section-add" class="row g-3 p-0 m-0">
                            <div class="col-12"><hr class="my-1 opacity-5"></div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Harga Beli (Rp)</label>
                                <input type="number" class="form-control" name="harga_beli" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tanggal Perolehan</label>
                                <input type="date" class="form-control" id="add_tgl_beli" name="tgl_beli" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label small fw-bold text-muted">Ada Garansi?</label>
                                <select class="form-select" name="ada_garansi" id="add_ada_garansi">
                                    <option value="N">Tidak Ada</option>
                                    <option value="Y">Ya, Bergaransi</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Durasi Garansi</label>
                                <select class="form-select select-duration" data-target="add_garansi_sampai" data-source="add_tgl_beli">
                                    <option value="">Pilih Durasi</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">1 Tahun</option>
                                    <option value="24">2 Tahun</option>
                                    <option value="36">3 Tahun</option>
                                    <option value="60">5 Tahun</option>
                                </select>
                                <input type="date" class="form-control mt-2" name="garansi_sampai" id="add_garansi_sampai">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Sumber Anggaran (Unit)</label>
                                <input type="text" class="form-control" name="divisi_pembeli" placeholder="e.g. Divisi LRC">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tahun Anggaran</label>
                                <input type="text" class="form-control" name="tahun_anggaran" placeholder="YYYY" value="<?= date('Y') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Status Pinjam</label>
                            <select class="form-select" name="bisa_dipinjam">
                                <option value="Y">🌐 Bisa Dipinjam</option>
                                <option value="N">🔒 Inventaris Internal</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Sumber Anggaran (Unit)</label>
                            <input type="text" class="form-control" name="divisi_pembeli" placeholder="e.g. Divisi LRC">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Tahun Anggaran</label>
                            <input type="text" class="form-control" name="tahun_anggaran" placeholder="YYYY" value="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Toko Pembelian</label>
                            <input type="text" class="form-control" name="toko_pembelian" placeholder="Toko/Vendor">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Simpan Aset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel/CSV -->
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
                            <i class="fa-solid fa-file-excel me-1"></i> Download Template Native Excel
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">File Excel/CSV</label>
                        <input type="file" class="form-control" name="file_aset" accept=".csv, .xlsx, .xls" required>
                    </div>

                    <div class="bg-light rounded-3 p-3">
                        <p class="fw-bold small mb-1">Urutan Kolom CSV:</p>
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
<!-- Datalist untuk Saran Input (Mencegah Typo & Double Data) -->
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
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('showModal') === 'tambah') {
        var myModal = new bootstrap.Modal(document.getElementById('modalTambahAset'));
        myModal.show();
    }

    // Auto-calculate Warranty
    document.querySelectorAll('.select-duration').forEach(select => {
        select.addEventListener('change', function() {
            const months = parseInt(this.value);
            const targetId = this.getAttribute('data-target');
            const sourceId = this.getAttribute('data-source');
            
            const sourceInput = document.getElementById(sourceId) || document.getElementsByName('tgl_beli')[0];
            const targetInput = document.getElementById(targetId);

            if (months && sourceInput && sourceInput.value) {
                let date = new Date(sourceInput.value);
                date.setMonth(date.getMonth() + months);
                
                // Format YYYY-MM-DD
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                targetInput.value = `${y}-${m}-${d}`;
            }
        });
    });
    // Category Template Switcher (Hide Finance & Specs for Rooms)
    document.querySelectorAll('.select-kategori-template').forEach(select => {
        const toggleFinance = () => {
            const selectedText = select.options[select.selectedIndex].getAttribute('data-nama');
            const financeId = select.getAttribute('data-target-finance');
            const specId = select.getAttribute('data-target-spec');
            const targetFinance = document.getElementById(financeId);
            const targetSpec = document.getElementById(specId);
            
            if (selectedText.includes('ruang') || selectedText.includes('fasilitas') || selectedText.includes('bangunan')) {
                if(targetFinance) targetFinance.style.display = 'none';
                // Move category select out before hiding spec for "Ruangan" logic if needed, 
                // but simpler just hide what's not needed.
                // For room, Merk and Warna usually empty.
                const merkInput = select.closest('.modal-body').querySelector('input[name="merk"]');
                const warnaInput = select.closest('.modal-body').querySelector('input[name="warna"]');
                if(merkInput) merkInput.parentElement.style.display = 'none';
                if(warnaInput) warnaInput.parentElement.style.display = 'none';
            } else {
                if(targetFinance) targetFinance.style.display = 'flex';
                const merkInput = select.closest('.modal-body').querySelector('input[name="merk"]');
                const warnaInput = select.closest('.modal-body').querySelector('input[name="warna"]');
                if(merkInput) merkInput.parentElement.style.display = 'block';
                if(warnaInput) warnaInput.parentElement.style.display = 'block';
            }
        };

        select.addEventListener('change', toggleFinance);
        toggleFinance(); // Run once on init
    });
});
</script>

<?php include '../layouts/footer.php'; ?>
