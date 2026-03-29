<?php
session_start();
require_once '../../config/database.php';

$current_page = 'laporan_ruangan';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// --- Logic Filter ---
$filter_gedung = $_GET['gedung'] ?? '';
$filter_status = $_GET['status'] ?? '';

$where_clauses = ["1=1"];
if (!empty($filter_gedung)) {
    $where_clauses[] = "r.id_gedung = '" . $koneksi->real_escape_string($filter_gedung) . "'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "r.status = '" . $koneksi->real_escape_string($filter_status) . "'";
}

$where_sql = implode(" AND ", $where_clauses);

// Query Data Ruangan
$query = "SELECT r.*, g.nama_gedung 
          FROM ruangan r 
          JOIN gedung g ON r.id_gedung = g.id 
          WHERE $where_sql
          ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC";
$result = $koneksi->query($query);

// Summary Stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as tersedia,
    SUM(CASE WHEN bisa_dipinjam = 'Y' THEN 1 ELSE 0 END) as publik
    FROM ruangan r
    WHERE $where_sql";
$stats = $koneksi->query($stats_query)->fetch_assoc();

// Data untuk dropdown
$gedung_list = $koneksi->query("SELECT * FROM gedung ORDER BY nama_gedung ASC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="d-print-none">
                <h4 class="font-heading fw-bold text-dark mb-1">Rekap Data Ruangan</h4>
                <p class="text-muted small mb-0">Laporan fasilitas fisik dan ketersediaan ruangan.</p>
            </div>
            <div class="d-flex gap-2 d-print-none">
                <button onclick="window.print()" class="btn btn-white rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-print me-2 text-primary"></i> Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-6 col-md-4">
                <div class="glass-card p-3 shadow-sm h-100 border-0">
                    <div class="text-muted small fw-bold mb-1">Total Ruangan</div>
                    <div class="fs-2 fw-bold text-dark"><?= number_format($stats['total'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #10b981 !important;">
                    <div class="text-muted small fw-bold mb-1">Status Tersedia</div>
                    <div class="fs-2 fw-bold text-success"><?= number_format($stats['tersedia'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #6366f1 !important;">
                    <div class="text-muted small fw-bold mb-1">Dapat Dipinjam (Publik)</div>
                    <div class="fs-2 fw-bold text-primary"><?= number_format($stats['publik'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="glass-card p-4 shadow-sm mb-4 d-print-none border-0">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter Gedung</label>
                    <select class="form-select form-select-sm" name="gedung">
                        <option value="">Semua Gedung</option>
                        <?php while($g = $gedung_list->fetch_assoc()): ?>
                            <option value="<?= $g['id'] ?>" <?= $filter_gedung == $g['id'] ? 'selected' : '' ?>><?= $g['nama_gedung'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Status</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">Semua Status</option>
                        <option value="tersedia" <?= $filter_status == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="dipakai" <?= $filter_status == 'dipakai' ? 'selected' : '' ?>>Sedang Dipakai</option>
                        <option value="perbaikan" <?= $filter_status == 'perbaikan' ? 'selected' : '' ?>>Dalam Perbaikan</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold w-100">Terapkan</button>
                    <a href="ruangan.php" class="btn btn-light btn-sm rounded-pill px-3">Reset</a>
                </div>
            </form>
        </div>

        <!-- Print Header -->
        <div class="d-none d-print-block mb-4 text-center">
            <h3 class="fw-bold mb-1">LAPORAN REKAP DATA RUANGAN</h3>
            <p class="mb-0">Dicetak pada: <?= date('d F Y') ?></p>
            <hr style="border-top: 2px solid #000;">
        </div>

        <!-- Data Table -->
        <div class="table-responsive glass-card shadow-sm p-0 border-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">No</th>
                        <th>Kode</th>
                        <th>Nama Ruangan</th>
                        <th>Gedung</th>
                        <th class="text-center">Kapasitas</th>
                        <th class="text-center">Izin Pinjam</th>
                        <th class="text-center pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4"><?= $no++ ?></td>
                            <td class="fw-bold"><?= $row['kode_ruangan'] ?></td>
                            <td class="fw-medium text-dark"><?= htmlspecialchars($row['nama_ruangan']) ?></td>
                            <td><?= htmlspecialchars($row['nama_gedung']) ?></td>
                            <td class="text-center"><?= $row['kapasitas'] ?> Org</td>
                            <td class="text-center">
                                <?= $row['bisa_dipinjam'] == 'Y' ? '🌐 Publik' : '🔒 Internal' ?>
                            </td>
                            <td class="text-center pe-4">
                                <?php if($row['status'] == 'tersedia'): ?>
                                    <span class="badge bg-success-soft text-success rounded-pill">Tersedia</span>
                                <?php elseif($row['status'] == 'dipakai'): ?>
                                    <span class="badge bg-warning-soft text-warning rounded-pill">Dipakai</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-soft text-danger rounded-pill">Perbaikan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<style>
@media print {
    .offcanvas-sidebar, .topbar, .d-print-none, .header-actions, .filter-panel, .btn-group, .fab-container { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
    .glass-card { border: 1px solid #dee2e6 !important; box-shadow: none !important; background: white !important; backdrop-filter: none !important; border-radius: 0 !important; }
    body { background: white !important; }
    .table thead th { background-color: #f8f9fa !important; border-bottom: 2px solid #333 !important; }
}
.bg-warning-soft { background-color: #fffbeb; color: #d97706; }
.bg-success-soft { background-color: #f0fdf4; color: #16a34a; }
.bg-danger-soft { background-color: #fef2f2; color: #dc2626; }
.bg-secondary-soft { background-color: #f8f9fa; color: #6b7280; }
</style>

<?php include '../layouts/footer.php'; ?>
