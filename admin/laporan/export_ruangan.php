<?php
session_start();
require_once '../../config/database.php';

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

$filename = "Laporan_Ruangan_" . date('Ymd_His') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; // BOM UTF-8
?>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 11pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #7c3aed; color: white; font-weight: bold; padding: 8px; border: 1px solid #ccc; }
    td { padding: 6px 8px; border: 1px solid #ddd; }
    tr:nth-child(even) td { background-color: #f5f5f5; }
    .title-row td { background-color: #4c1d95; color: white; font-size: 14pt; font-weight: bold; text-align: center; padding: 12px; }
    .subtitle-row td { background-color: #ede9fe; font-size: 10pt; text-align: center; color: #374151; }
    .stat-label { background-color: #f5f3ff; font-weight: bold; color: #6d28d9; }
    .stat-value { font-weight: bold; color: #111827; }
    .status-tersedia { color: #065f46; background-color: #d1fae5; }
    .status-dipakai  { color: #92400e; background-color: #fef3c7; }
    .status-perbaikan { color: #991b1b; background-color: #fee2e2; }
    .footer-row td { background-color: #f0f0f0; color: #6b7280; font-style: italic; font-size: 9pt; }
</style>
</head>
<body>
<table>
    <!-- Judul -->
    <tr class="title-row"><td colspan="7">LAPORAN REKAP DATA RUANGAN</td></tr>
    <tr class="subtitle-row">
        <td colspan="7">Dicetak: <?= date('d F Y H:i') ?></td>
    </tr>
    <tr><td colspan="7"></td></tr>

    <!-- Ringkasan -->
    <tr>
        <td colspan="2" class="stat-label">Total Ruangan</td>
        <td colspan="3" class="stat-label">Status Tersedia</td>
        <td colspan="2" class="stat-label">Dapat Dipinjam (Publik)</td>
    </tr>
    <tr>
        <td colspan="2" class="stat-value"><?= $stats['total'] ?></td>
        <td colspan="3" class="stat-value"><?= $stats['tersedia'] ?></td>
        <td colspan="2" class="stat-value"><?= $stats['publik'] ?></td>
    </tr>
    <tr><td colspan="7"></td></tr>

    <!-- Header Tabel -->
    <tr>
        <th>No</th>
        <th>Kode Ruangan</th>
        <th>Nama Ruangan</th>
        <th>Gedung</th>
        <th>Kapasitas</th>
        <th>Izin Pinjam</th>
        <th>Status</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): $no = 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td style="font-weight:bold;"><?= htmlspecialchars($row['kode_ruangan']) ?></td>
            <td><?= htmlspecialchars($row['nama_ruangan']) ?></td>
            <td><?= htmlspecialchars($row['nama_gedung']) ?></td>
            <td style="text-align:center;"><?= $row['kapasitas'] ?> Org</td>
            <td><?= $row['bisa_dipinjam'] == 'Y' ? 'Publik' : 'Internal' ?></td>
            <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7" style="text-align:center; color:#999;">Data tidak ditemukan.</td></tr>
    <?php endif; ?>

    <!-- Footer -->
    <tr><td colspan="7"></td></tr>
    <tr class="footer-row">
        <td colspan="7">Total data: <?= $stats['total'] ?> ruangan &mdash; Diekspor dari Sistem Manajemen Aset An Nadzir &mdash; <?= date('d/m/Y H:i:s') ?></td>
    </tr>
</table>
</body>
</html>
