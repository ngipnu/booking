<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// --- Logic Filter ---
$filter_kategori = $_GET['kategori'] ?? '';
$filter_kondisi  = $_GET['kondisi']  ?? '';
$filter_unit     = $_GET['unit']     ?? '';
$filter_tahun    = $_GET['tahun']    ?? '';
$filter_search   = $_GET['search']   ?? '';

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
$stats = $stats_res ? $stats_res->fetch_assoc() : ['total'=>0,'baik'=>0,'rusak'=>0,'total_nilai'=>0];

$filename = "Laporan_Inventaris_" . date('Ymd_His') . ".xls";

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
    th { background-color: #0f766e; color: white; font-weight: bold; padding: 8px; border: 1px solid #ccc; }
    td { padding: 6px 8px; border: 1px solid #ddd; }
    tr:nth-child(even) td { background-color: #f5f5f5; }
    .title-row td { background-color: #134e4a; color: white; font-size: 14pt; font-weight: bold; text-align: center; padding: 12px; }
    .subtitle-row td { background-color: #ccfbf1; font-size: 10pt; text-align: center; color: #374151; }
    .stat-label { background-color: #f0fdfa; font-weight: bold; color: #0f766e; }
    .stat-value { font-weight: bold; color: #111827; }
    .total-row td { background-color: #e0f2fe; font-weight: bold; color: #0c4a6e; }
    .footer-row td { background-color: #f0f0f0; color: #6b7280; font-style: italic; font-size: 9pt; }
    .kondisi-baik { color: #065f46; background-color: #d1fae5; }
    .kondisi-rusak_ringan { color: #92400e; background-color: #fef3c7; }
    .kondisi-rusak_berat { color: #991b1b; background-color: #fee2e2; }
    .kondisi-hilang { color: #374151; background-color: #e5e7eb; }
</style>
</head>
<body>
<table>
    <!-- Judul -->
    <tr class="title-row"><td colspan="9">LAPORAN REKAP DATA INVENTARIS</td></tr>
    <tr class="subtitle-row">
        <td colspan="9">
            Dicetak: <?= date('d F Y H:i') ?>
            <?= $filter_kondisi ? ' &nbsp;|&nbsp; Kondisi: ' . strtoupper(str_replace('_',' ',$filter_kondisi)) : '' ?>
            <?= $filter_unit ? ' &nbsp;|&nbsp; Unit: ' . htmlspecialchars($filter_unit) : '' ?>
            <?= $filter_tahun ? ' &nbsp;|&nbsp; Tahun: ' . $filter_tahun : '' ?>
        </td>
    </tr>
    <tr><td colspan="9"></td></tr>

    <!-- Ringkasan -->
    <tr>
        <td colspan="3" class="stat-label">Total Item</td>
        <td colspan="3" class="stat-label">Kondisi Baik</td>
        <td colspan="3" class="stat-label">Perlu Perbaikan</td>
    </tr>
    <tr>
        <td colspan="3" class="stat-value"><?= $stats['total'] ?> Item</td>
        <td colspan="3" class="stat-value"><?= $stats['baik'] ?> Item</td>
        <td colspan="3" class="stat-value"><?= $stats['rusak'] ?> Item</td>
    </tr>
    <tr><td colspan="9" class="stat-label">Total Nilai Aset: Rp <?= number_format($stats['total_nilai'] ?? 0, 0, ',', '.') ?></td></tr>
    <tr><td colspan="9"></td></tr>

    <!-- Header Tabel -->
    <tr>
        <th>No</th>
        <th>Kode Aset</th>
        <th>Nama Inventaris</th>
        <th>Merk / Warna</th>
        <th>Kategori</th>
        <th>Unit Pengguna</th>
        <th>Lokasi Simpan</th>
        <th>Kondisi</th>
        <th>Harga Beli</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): $no = 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $kondisi_class = 'kondisi-' . $row['kondisi'];
            $kondisi_label = strtoupper(str_replace('_',' ', $row['kondisi']));
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td style="font-weight:bold; color:#0f766e;"><?= htmlspecialchars($row['kode_aset']) ?></td>
            <td><?= htmlspecialchars($row['nama_aset']) ?></td>
            <td><?= htmlspecialchars(trim($row['merk'] . ' ' . $row['warna'])) ?></td>
            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['unit_pengguna']) ?></td>
            <td><?= htmlspecialchars($row['lokasi_simpan']) ?></td>
            <td class="<?= $kondisi_class ?>"><?= $kondisi_label ?></td>
            <td style="text-align:right;">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
        </tr>
        <?php endwhile; ?>
        <!-- Total Row -->
        <tr class="total-row">
            <td colspan="8" style="text-align:right; font-weight:bold;">GRAND TOTAL NILAI ASET</td>
            <td style="text-align:right; font-weight:bold;">Rp <?= number_format($stats['total_nilai'], 0, ',', '.') ?></td>
        </tr>
    <?php else: ?>
        <tr><td colspan="9" style="text-align:center; color:#999;">Tidak ada data aset sesuai kriteria.</td></tr>
    <?php endif; ?>

    <!-- Footer -->
    <tr><td colspan="9"></td></tr>
    <tr class="footer-row">
        <td colspan="9">Total data: <?= $stats['total'] ?> item &mdash; Diekspor dari Sistem Manajemen Aset An Nadzir &mdash; <?= date('d/m/Y H:i:s') ?></td>
    </tr>
</table>
</body>
</html>
