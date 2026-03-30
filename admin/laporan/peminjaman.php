<?php
session_start();
require_once '../../config/database.php';

$current_page = 'laporan_peminjaman';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// --- Logic Filter ---
$filter_start = $_GET['tgl_mulai'] ?? '';
$filter_end = $_GET['tgl_selesai'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_search = $_GET['search'] ?? '';

$where_clauses = ["1=1"];
if (!empty($filter_start)) {
    $where_clauses[] = "p.tgl_pinjam >= '" . $koneksi->real_escape_string($filter_start) . "'";
}
if (!empty($filter_end)) {
    $where_clauses[] = "p.tgl_pinjam <= '" . $koneksi->real_escape_string($filter_end) . "'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "p.status_pinjam = '" . $koneksi->real_escape_string($filter_status) . "'";
}
if (!empty($filter_search)) {
    $search = $koneksi->real_escape_string($filter_search);
    $where_clauses[] = "(p.nama_peminjam LIKE '%$search%' OR u.nama LIKE '%$search%' OR a.nama_aset LIKE '%$search%' OR a.kode_aset LIKE '%$search%')";
}

$where_sql = implode(" AND ", $where_clauses);

// Query Data Peminjaman
$query = "SELECT p.*, u.nama as nama_user, a.nama_aset, a.kode_aset 
          FROM peminjaman p 
          LEFT JOIN users u ON p.id_user = u.id 
          LEFT JOIN aset a ON p.id_aset = a.id 
          WHERE $where_sql
          ORDER BY p.tgl_pinjam DESC, p.jam_mulai DESC";
$result = $koneksi->query($query);

// Summary Stats for Filtered Data
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_pinjam = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN status_pinjam = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status_pinjam = 'selesai' THEN 1 ELSE 0 END) as selesai
    FROM peminjaman p
    LEFT JOIN users u ON p.id_user = u.id
    LEFT JOIN aset a ON p.id_aset = a.id
    WHERE $where_sql";
$stats_res = $koneksi->query($stats_query);
$stats = $stats_res ? $stats_res->fetch_assoc() : ['total'=>0, 'menunggu'=>0, 'disetujui'=>0, 'selesai'=>0];

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<style>
@media print {
    .offcanvas-sidebar, .topbar, .d-print-none, .header-actions, .filter-panel, .btn-group, .fab-container { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
    .glass-card { border: 1px solid #dee2e6 !important; box-shadow: none !important; background: white !important; backdrop-filter: none !important; border-radius: 0 !important; }
    .animate-fade-up { transform: none !important; opacity: 1 !important; }
    body { background: white !important; font-size: 10pt; }
    .table thead th { background-color: #f8f9fa !important; border-bottom: 2px solid #333 !important; -webkit-print-color-adjust: exact; }
    .table td, .table th { border: 1px solid #dee2e6 !important; }
    .badge { border: 1px solid #ccc !important; color: black !important; background: transparent !important; }
}

/* Glass Card styling if not already in header */
.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    overflow: hidden;
}

.bg-warning-soft { background-color: #fffbeb; color: #d97706; }
.bg-success-soft { background-color: #f0fdf4; color: #16a34a; }
.bg-danger-soft { background-color: #fef2f2; color: #dc2626; }
.bg-secondary-soft { background-color: #f8f9fa; color: #6b7280; }
</style>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5 animate-fade-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="d-print-none">
                <h4 class="font-heading fw-bold text-dark mb-1">Rekap Peminjaman Aset</h4>
                <p class="text-muted small mb-0">Laporan aktivitas peminjaman sarana dan prasarana.</p>
            </div>
            <div class="d-flex gap-2 d-print-none">
                <?php
                    $export_params = http_build_query(array_filter([
                        'tgl_mulai'  => $filter_start,
                        'tgl_selesai'=> $filter_end,
                        'status'     => $filter_status,
                        'search'     => $filter_search,
                    ]));
                ?>
                <a href="export_peminjaman.php<?= $export_params ? '?' . $export_params : '' ?>" 
                   class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-file-excel me-2"></i> Export Excel
                </a>
                <button onclick="window.print()" class="btn btn-white rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-print me-2 text-primary"></i> Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0">
                    <div class="text-muted small fw-bold mb-1">Total Pengajuan</div>
                    <div class="fs-3 fw-bold text-dark"><?= number_format($stats['total'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #f59e0b !important;">
                    <div class="text-muted small fw-bold mb-1">Menunggu</div>
                    <div class="fs-3 fw-bold text-warning"><?= number_format($stats['menunggu'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #10b981 !important;">
                    <div class="text-muted small fw-bold mb-1">Dipinjam</div>
                    <div class="fs-3 fw-bold text-success"><?= number_format($stats['disetujui'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 shadow-sm h-100 border-0" style="border-left: 4px solid #6366f1 !important;">
                    <div class="text-muted small fw-bold mb-1">Selesai</div>
                    <div class="fs-3 fw-bold text-primary"><?= number_format($stats['selesai'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="glass-card p-4 shadow-sm mb-4 d-print-none border-0">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Rentang Tanggal</label>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control" name="tgl_mulai" value="<?= htmlspecialchars($filter_start) ?>">
                        <span class="input-group-text bg-light border-0">s/d</span>
                        <input type="date" class="form-control" name="tgl_selesai" value="<?= htmlspecialchars($filter_end) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Status</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">Semua Status</option>
                        <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="disetujui" <?= $filter_status == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Cari Peminjam / Aset</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search opacity-50"></i></span>
                        <input type="text" class="form-control border-start-0" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="Nama, Unit, Kode Aset...">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold w-100">Tampilkan</button>
                    <a href="peminjaman.php" class="btn btn-light btn-sm rounded-pill px-3">Reset</a>
                </div>
            </form>
        </div>

        <!-- Print Header -->
        <div class="d-none d-print-block mb-4 text-center">
            <h3 class="fw-bold mb-1">LAPORAN REKAP PEMINJAMAN ASET</h3>
            <p class="mb-0"><?= !empty($filter_start) ? 'Periode: ' . date('d F Y', strtotime($filter_start)) : 'Semua Periode' ?> <?= !empty($filter_end) ? ' s/d ' . date('d F Y', strtotime($filter_end)) : '' ?></p>
            <hr style="border-top: 2px solid #000;">
        </div>

        <!-- Data Table -->
        <div class="table-responsive glass-card shadow-sm p-0 border-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">No</th>
                        <th>Peminjam & Unit</th>
                        <th>Aset & Kode</th>
                        <th>Waktu Pinjam</th>
                        <th>Keperluan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_peminjam'] ? $row['nama_peminjam'] : ($row['nama_user'] ?? 'Anonim')) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($row['unit_peminjam'] ? $row['unit_peminjam'] : 'Umum/LRC') ?></div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                <div class="text-muted small" style="font-size: 0.7rem;"><?= $row['kode_aset'] ?></div>
                            </td>
                            <td>
                                <div class="small fw-bold text-dark"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])) ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 15rem;" title="<?= htmlspecialchars($row['keperluan']) ?>">
                                    <?= htmlspecialchars($row['keperluan']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if($row['status_pinjam'] == 'menunggu'): ?>
                                    <span class="badge bg-warning-soft text-warning rounded-pill">Menunggu</span>
                                <?php elseif($row['status_pinjam'] == 'disetujui'): ?>
                                    <span class="badge bg-success-soft text-success rounded-pill">Dipinjam</span>
                                <?php elseif($row['status_pinjam'] == 'ditolak'): ?>
                                    <span class="badge bg-danger-soft text-danger rounded-pill">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-soft text-muted rounded-pill">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open d-block fs-1 mb-3 opacity-25"></i>
                                Tidak ada data hasil filter.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Print Footer -->
        <div class="d-none d-print-block mt-5">
            <div class="row">
                <div class="col-8"></div>
                <div class="col-4 text-center">
                    <p class="mb-5">Dicetak pada: <?= date('d/m/Y H:i') ?><br>Petugas Sarpras,</p>
                    <br><br>
                    <p class="fw-bold">( ........................................ )</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../layouts/footer.php'; ?>
