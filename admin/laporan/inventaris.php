<?php
session_start();
require_once '../../config/database.php';

$current_page = 'laporan_inventaris';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// --- Logic Filter ---
$filter_kategori = $_GET['kategori'] ?? '';
$filter_kondisi = $_GET['kondisi'] ?? '';
$filter_unit = $_GET['unit'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';
$filter_search = $_GET['search'] ?? '';

$where_clauses = ["(k.nama_kategori != 'Ruangan' OR k.nama_kategori IS NULL)"];
if (!empty($filter_kategori)) {
    $where_clauses[] = "a.id_kategori = '" . $koneksi->real_escape_string($filter_kategori) . "'";
}
if (!empty($filter_kondisi)) {
    $where_clauses[] = "a.kondisi = '" . $koneksi->real_escape_string($filter_kondisi) . "'";
}
if (!empty($filter_unit)) {
    $where_clauses[] = "a.unit_pengguna = '" . $koneksi->real_escape_string($filter_unit) . "'";
}
if (!empty($filter_tahun)) {
    $where_clauses[] = "a.tahun_anggaran = '" . $koneksi->real_escape_string($filter_tahun) . "'";
}
if (!empty($filter_search)) {
    $search = $koneksi->real_escape_string($filter_search);
    $where_clauses[] = "(a.nama_aset LIKE '%$search%' OR a.kode_aset LIKE '%$search%' OR a.merk LIKE '%$search%')";
}

$where_sql = implode(" AND ", $where_clauses);

// Query Data Inventaris
$query = "SELECT a.*, k.nama_kategori 
          FROM aset a 
          LEFT JOIN kategori k ON a.id_kategori = k.id 
          WHERE $where_sql
          ORDER BY a.kode_aset ASC";
$result = $koneksi->query($query);

// Summary Stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN kondisi = 'baik' THEN 1 ELSE 0 END) as baik,
    SUM(CASE WHEN kondisi != 'baik' THEN 1 ELSE 0 END) as rusak,
    SUM(harga_beli) as total_nilai
    FROM aset a
    WHERE $where_sql";
$stats_res = $koneksi->query($stats_query);
$stats = $stats_res ? $stats_res->fetch_assoc() : ['total'=>0, 'baik'=>0, 'rusak'=>0, 'total_nilai'=>0];

// Data untuk dropdown
$kategori_list = $koneksi->query("SELECT * FROM kategori WHERE nama_kategori != 'Ruangan' ORDER BY nama_kategori ASC");
$unit_list = $koneksi->query("SELECT DISTINCT unit_pengguna FROM aset WHERE unit_pengguna != '' ORDER BY unit_pengguna ASC");
$tahun_list = $koneksi->query("SELECT DISTINCT tahun_anggaran FROM aset WHERE tahun_anggaran != '' ORDER BY tahun_anggaran DESC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<style>
@media print {
    .offcanvas-sidebar, .topbar, .d-print-none, .header-actions, .filter-panel, .btn-group, .fab-container { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
    .glass-card { border: 1px solid #dee2e6 !important; box-shadow: none !important; background: white !important; backdrop-filter: none !important; border-radius: 0 !important; }
    .animate-fade-up { transform: none !important; opacity: 1 !important; }
    body { background: white !important; font-size: 9pt; }
    .table thead th { background-color: #f8f9fa !important; border-bottom: 2px solid #333 !important; -webkit-print-color-adjust: exact; }
    .table td, .table th { border: 1px solid #dee2e6 !important; padding: 4px 8px !important; }
    .badge { border: 1px solid #ccc !important; color: black !important; background: transparent !important; }
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    overflow: hidden;
}
</style>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="d-print-none">
                <h4 class="font-heading fw-bold text-dark mb-1">Rekap Data Inventaris</h4>
                <p class="text-muted small mb-0">Laporan menyeluruh aset dan sarana prasarana lembaga.</p>
            </div>
            <div class="d-flex gap-2 d-print-none">
                <button onclick="window.print()" class="btn btn-white rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-print me-2 text-primary"></i> Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0">
                    <div class="text-muted small fw-bold mb-1">Total Item</div>
                    <div class="fs-3 fw-bold text-dark"><?= number_format($stats['total'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #10b981 !important;">
                    <div class="text-muted small fw-bold mb-1">Kondisi Baik</div>
                    <div class="fs-3 fw-bold text-success"><?= number_format($stats['baik'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #ef4444 !important;">
                    <div class="text-muted small fw-bold mb-1">Perlu Perbaikan</div>
                    <div class="fs-3 fw-bold text-danger"><?= number_format($stats['rusak'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #6366f1 !important;">
                    <div class="text-muted small fw-bold mb-1">Total Nilai Aset</div>
                    <div class="fs-4 fw-bold text-primary">Rp <?= number_format($stats['total_nilai'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="glass-card p-4 shadow-sm mb-4 d-print-none border-0">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Kategori</label>
                    <select class="form-select form-select-sm" name="kategori">
                        <option value="">Semua</option>
                        <?php while($k = $kategori_list->fetch_assoc()): ?>
                            <option value="<?= $k['id'] ?>" <?= $filter_kategori == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kategori'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Kondisi</label>
                    <select class="form-select form-select-sm" name="kondisi">
                        <option value="">Semua</option>
                        <option value="baik" <?= $filter_kondisi == 'baik' ? 'selected' : '' ?>>Baik</option>
                        <option value="rusak_ringan" <?= $filter_kondisi == 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                        <option value="rusak_berat" <?= $filter_kondisi == 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
                        <option value="hilang" <?= $filter_kondisi == 'hilang' ? 'selected' : '' ?>>Hilang</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Unit</label>
                    <select class="form-select form-select-sm" name="unit">
                        <option value="">Semua Unit</option>
                        <?php while($u = $unit_list->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($u['unit_pengguna']) ?>" <?= $filter_unit == $u['unit_pengguna'] ? 'selected' : '' ?>><?= $u['unit_pengguna'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold text-muted">Tahun</label>
                    <select class="form-select form-select-sm" name="tahun">
                        <option value="">All</option>
                        <?php while($t = $tahun_list->fetch_assoc()): ?>
                            <option value="<?= $t['tahun_anggaran'] ?>" <?= $filter_tahun == $t['tahun_anggaran'] ? 'selected' : '' ?>><?= $t['tahun_anggaran'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Cari Nama / Kode</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search opacity-50"></i></span>
                        <input type="text" class="form-control border-start-0" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="Masukkan kata kunci...">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold w-100">Terapkan</button>
                    <a href="inventaris.php" class="btn btn-light btn-sm rounded-pill px-3">Reset</a>
                </div>
            </form>
        </div>

        <!-- Print Header -->
        <div class="d-none d-print-block mb-4 text-center">
            <h3 class="fw-bold mb-1">LAPORAN REKAP DATA INVENTARIS</h3>
            <p class="mb-0">Dicetak pada: <?= date('d F Y') ?></p>
            <div class="mt-2 small">
                <?php if($filter_kategori) echo "Kategori: " . $filter_kategori . " | "; ?>
                <?php if($filter_kondisi) echo "Kondisi: " . $filter_kondisi . " | "; ?>
                <?php if($filter_unit) echo "Unit: " . $filter_unit . " | "; ?>
                <?php if($filter_tahun) echo "Tahun: " . $filter_tahun; ?>
            </div>
            <hr style="border-top: 2px solid #000;">
        </div>

        <!-- Data Table -->
        <div class="table-responsive glass-card shadow-sm p-0 border-0">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light text-uppercase">
                    <tr>
                        <th class="ps-4 py-3" style="width: 40px;">No</th>
                        <th>Kode Aset</th>
                        <th>Nama Inventaris</th>
                        <th>Kategori</th>
                        <th>Unit Pengguna</th>
                        <th>Lokasi</th>
                        <th class="text-center">Kondisi</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-center pe-4">Thn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4"><?= $no++ ?></td>
                            <td class="fw-bold text-primary"><?= $row['kode_aset'] ?></td>
                            <td>
                                <div class="fw-medium text-dark"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                <div class="text-muted small" style="font-size: 0.7rem;"><?= htmlspecialchars($row['merk'] . ' ' . $row['warna']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['unit_pengguna']) ?></td>
                            <td><?= htmlspecialchars($row['lokasi_simpan']) ?></td>
                            <td class="text-center">
                                <?php 
                                    $c = $row['kondisi'];
                                    $badge = 'bg-secondary';
                                    if($c == 'baik') $badge = 'bg-success';
                                    if($c == 'rusak_ringan') $badge = 'bg-warning text-dark';
                                    if($c == 'rusak_berat') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?= $badge ?> rounded-pill" style="font-size: 0.65rem;"><?= strtoupper(str_replace('_', ' ', $c)) ?></span>
                            </td>
                            <td class="text-end fw-bold">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                            <td class="text-center pe-4"><?= $row['tahun_anggaran'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <!-- Total Row -->
                        <tr class="bg-light fw-bold">
                            <td colspan="7" class="text-end py-3">GRAND TOTAL NILAI ASET</td>
                            <td class="text-end py-3 text-primary">Rp <?= number_format($stats['total_nilai'], 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open d-block fs-1 mb-3 opacity-25"></i>
                                Tidak ada data aset sesuai kriteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Print Footer -->
        <div class="d-none d-print-block mt-5">
            <div class="row">
                <div class="col-8">
                    <p class="small text-muted">Total data: <?= $stats['total'] ?> item | Dicetak melalui sistem Manajemen Aset An Nadzir.</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-5">Dicetak pada: <?= date('d/m/Y H:i') ?><br>Petugas Inventaris,</p>
                    <br><br>
                    <p class="fw-bold">( ........................................ )</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../layouts/footer.php'; ?>
