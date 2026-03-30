<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// --- Logic Filter ---
$filter_start  = $_GET['tgl_mulai']  ?? '';
$filter_end    = $_GET['tgl_selesai']?? '';
$filter_status = $_GET['status']     ?? '';
$filter_search = $_GET['search']     ?? '';

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

$query = "SELECT p.*, u.nama as nama_user, a.nama_aset, a.kode_aset 
          FROM peminjaman p 
          LEFT JOIN users u ON p.id_user = u.id 
          LEFT JOIN aset a ON p.id_aset = a.id 
          WHERE $where_sql
          ORDER BY p.tgl_pinjam DESC, p.jam_mulai DESC";
$result = $koneksi->query($query);

// Summary Stats
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
$stats = $stats_res ? $stats_res->fetch_assoc() : ['total'=>0,'menunggu'=>0,'disetujui'=>0,'selesai'=>0];

$filename = "Laporan_Peminjaman_" . date('Ymd_His') . ".xls";

// Set headers untuk download Excel
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
    th { background-color: #4f46e5; color: white; font-weight: bold; padding: 8px; border: 1px solid #ccc; }
    td { padding: 6px 8px; border: 1px solid #ddd; }
    tr:nth-child(even) td { background-color: #f5f5f5; }
    .title-row td { background-color: #1e1b4b; color: white; font-size: 14pt; font-weight: bold; text-align: center; padding: 12px; }
    .subtitle-row td { background-color: #e0e7ff; font-size: 10pt; text-align: center; color: #374151; }
    .stat-label { background-color: #eef2ff; font-weight: bold; color: #4338ca; }
    .stat-value { font-weight: bold; color: #111827; }
    .badge-menunggu { color: #92400e; background-color: #fef3c7; }
    .badge-disetujui { color: #065f46; background-color: #d1fae5; }
    .badge-ditolak   { color: #991b1b; background-color: #fee2e2; }
    .badge-selesai   { color: #374151; background-color: #f3f4f6; }
    .footer-row td { background-color: #f0f0f0; color: #6b7280; font-style: italic; font-size: 9pt; }
</style>
</head>
<body>
<table>
    <!-- Judul -->
    <tr class="title-row"><td colspan="7">LAPORAN REKAP PEMINJAMAN ASET</td></tr>
    <tr class="subtitle-row">
        <td colspan="7">
            Periode: <?= !empty($filter_start) ? date('d F Y', strtotime($filter_start)) : 'Semua' ?>
            <?= !empty($filter_end) ? ' s/d ' . date('d F Y', strtotime($filter_end)) : '' ?>
            &nbsp;|&nbsp; Dicetak: <?= date('d F Y H:i') ?>
        </td>
    </tr>
    <tr><td colspan="7"></td></tr>

    <!-- Ringkasan -->
    <tr>
        <td colspan="2" class="stat-label">Total Pengajuan</td>
        <td colspan="2" class="stat-label">Menunggu</td>
        <td colspan="1" class="stat-label">Dipinjam</td>
        <td colspan="2" class="stat-label">Selesai</td>
    </tr>
    <tr>
        <td colspan="2" class="stat-value"><?= $stats['total'] ?></td>
        <td colspan="2" class="stat-value"><?= $stats['menunggu'] ?></td>
        <td colspan="1" class="stat-value"><?= $stats['disetujui'] ?></td>
        <td colspan="2" class="stat-value"><?= $stats['selesai'] ?></td>
    </tr>
    <tr><td colspan="7"></td></tr>

    <!-- Header Tabel -->
    <tr>
        <th>No</th>
        <th>Nama Peminjam</th>
        <th>Unit</th>
        <th>Aset (Kode)</th>
        <th>Tanggal &amp; Jam</th>
        <th>Keperluan</th>
        <th>Status</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): $no = 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $nama  = $row['nama_peminjam'] ?: ($row['nama_user'] ?? 'Anonim');
            $unit  = $row['unit_peminjam'] ?: 'Umum/LRC';
            $aset  = $row['nama_aset'] . ' (' . $row['kode_aset'] . ')';
            $tgl   = date('d/m/Y', strtotime($row['tgl_pinjam']));
            $jam   = substr($row['jam_mulai'],0,5) . ' - ' . substr($row['jam_selesai'],0,5);
            $waktu = $tgl . ', ' . $jam;
            $badge = 'badge-' . $row['status_pinjam'];
            $status_label = ucfirst($row['status_pinjam']);
            if($row['status_pinjam'] == 'disetujui') $status_label = 'Dipinjam';
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($nama) ?></td>
            <td><?= htmlspecialchars($unit) ?></td>
            <td><?= htmlspecialchars($aset) ?></td>
            <td><?= $waktu ?></td>
            <td><?= htmlspecialchars($row['keperluan']) ?></td>
            <td class="<?= $badge ?>"><?= $status_label ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7" style="text-align:center; color:#999;">Tidak ada data.</td></tr>
    <?php endif; ?>

    <!-- Footer -->
    <tr><td colspan="7"></td></tr>
    <tr class="footer-row">
        <td colspan="7">Diekspor dari Sistem Manajemen Aset An Nadzir &mdash; <?= date('d/m/Y H:i:s') ?></td>
    </tr>
</table>
</body>
</html>
