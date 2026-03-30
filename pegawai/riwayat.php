<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'pegawai' && $_SESSION['role'] !== 'user')) {
    header("Location: ../login.php");
    exit;
}
if (!isset($_SESSION['nama_pemakai'])) {
    header("Location: identifikasi.php");
    exit;
}

$page_title = 'Riwayat Peminjaman';
$user_id    = $_SESSION['user_id'];
$nama_pemakai = $_SESSION['nama_pemakai'];
$unit_pemakai = $_SESSION['unit_pemakai'];

// Filter status
$filter = $_GET['status'] ?? 'semua';
if ($filter === 'aktif') {
    $where_status = "AND p.status_pinjam IN ('menunggu','disetujui')";
} elseif ($filter === 'selesai') {
    $where_status = "AND p.status_pinjam = 'selesai'";
} elseif ($filter === 'ditolak') {
    $where_status = "AND p.status_pinjam = 'ditolak'";
} else {
    $where_status = '';
}

// Ambil semua riwayat peminjaman user ini
$riwayat = $koneksi->query("
    SELECT p.*, 
           a.nama_aset, a.kode_aset, k.nama_kategori, k.icon as kat_icon,
           r.nama_ruangan, r.kode_ruangan
    FROM peminjaman p
    LEFT JOIN aset a ON p.id_aset = a.id
    LEFT JOIN kategori k ON a.id_kategori = k.id
    LEFT JOIN ruangan r ON p.id_ruangan = r.id
    WHERE p.id_user = $user_id $where_status
    ORDER BY p.tgl_pinjam DESC, p.jam_mulai DESC
");

// Hitung tiap status untuk badge
$counts = $koneksi->query("
    SELECT status_pinjam, COUNT(*) as total 
    FROM peminjaman WHERE id_user = $user_id 
    GROUP BY status_pinjam
")->fetch_all(MYSQLI_ASSOC);
$cnt = array_column($counts, 'total', 'status_pinjam');
$total_aktif   = ($cnt['menunggu'] ?? 0) + ($cnt['disetujui'] ?? 0);
$total_selesai = $cnt['selesai']  ?? 0;
$total_ditolak = $cnt['ditolak']  ?? 0;
$total_semua   = array_sum($cnt);

include 'layouts/header.php';
?>

<div class="container-fluid px-3 px-md-4 py-4" style="margin-top: 80px;">

    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="dashboard.php" class="btn btn-light border rounded-circle p-2" style="width:40px;height:40px;">
            <i class="fa-solid fa-arrow-left small"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">Riwayat Peminjaman</h5>
            <div class="text-muted small">Semua pengajuan atas nama <b><?= htmlspecialchars($nama_pemakai) ?></b> · <?= htmlspecialchars($unit_pemakai) ?></div>
        </div>
        <a href="peminjaman.php" class="btn btn-primary rounded-pill ms-auto px-4" style="font-size:0.8rem;">
            <i class="fa-solid fa-calendar-plus me-2"></i> Ajukan Baru
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 flex-wrap mb-4">
        <?php 
        $tabs = [
            'semua'   => ["label" => "Semua",    "count" => $total_semua,   "color" => "secondary"],
            'aktif'   => ["label" => "Aktif",    "count" => $total_aktif,   "color" => "warning"],
            'selesai' => ["label" => "Selesai",  "count" => $total_selesai, "color" => "success"],
            'ditolak' => ["label" => "Ditolak",  "count" => $total_ditolak, "color" => "danger"],
        ];
        foreach ($tabs as $key => $tab): ?>
        <a href="?status=<?= $key ?>" 
           class="btn rounded-pill px-3 py-2 d-flex align-items-center gap-2 <?= $filter === $key ? 'btn-' . $tab['color'] . ' text-white shadow-sm' : 'btn-light text-muted border' ?>"
           style="font-size:0.82rem;font-weight:600;">
            <?= $tab['label'] ?>
            <span class="badge rounded-pill <?= $filter === $key ? 'bg-white text-' . $tab['color'] : 'bg-' . $tab['color'] . ' text-white' ?>"
                  style="font-size:0.65rem;"><?= $tab['count'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- List Riwayat -->
    <?php if ($riwayat && $riwayat->num_rows > 0): ?>
    <div class="d-flex flex-column gap-3">
        <?php while ($row = $riwayat->fetch_assoc()):
            $is_ruangan  = !empty($row['id_ruangan']);
            $item_name   = $is_ruangan ? $row['nama_ruangan'] : $row['nama_aset'];
            $item_code   = $is_ruangan ? $row['kode_ruangan'] : $row['kode_aset'];
            $item_cat    = $is_ruangan ? 'Ruangan' : $row['nama_kategori'];
            $item_icon   = $is_ruangan ? 'fa-door-open' : ('fa-' . ($row['kat_icon'] ?? 'box'));
            $is_mine     = ($row['nama_peminjam'] == $nama_pemakai && $row['unit_peminjam'] == $unit_pemakai);
            $status      = $row['status_pinjam'];
            if ($status === 'menunggu') {
                $badge = ['bg-warning text-dark', 'fa-hourglass-half', 'Menunggu'];
            } elseif ($status === 'disetujui') {
                $badge = ['bg-success text-white', 'fa-check-circle', 'Disetujui'];
            } elseif ($status === 'selesai') {
                $badge = ['bg-primary text-white', 'fa-check-double', 'Selesai'];
            } elseif ($status === 'ditolak') {
                $badge = ['bg-danger text-white', 'fa-xmark-circle', 'Ditolak'];
            } else {
                $badge = ['bg-secondary text-white', 'fa-question', $status];
            }
        ?>
        <div class="glass-card p-4 rounded-4 shadow-sm">
            <div class="row align-items-center g-3">
                <!-- Icon & Nama -->
                <div class="col-auto">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm <?= $is_ruangan ? 'bg-primary-soft text-primary' : 'bg-success-soft text-success' ?>"
                         style="width:48px;height:48px;">
                        <i class="fa-solid <?= $item_icon ?>"></i>
                    </div>
                </div>
                <div class="col">
                    <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($item_name) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($item_cat) ?> · <code style="font-size:0.72rem;"><?= htmlspecialchars($item_code) ?></code></div>
                    <div class="text-muted mt-1" style="font-size:0.72rem;">
                        <i class="fa-regular fa-calendar me-1"></i><?= date('d M Y', strtotime($row['tgl_pinjam'])) ?>
                        <span class="mx-1 opacity-30">|</span>
                        <i class="fa-regular fa-clock me-1"></i><?= substr($row['jam_mulai'],0,5) ?>–<?= substr($row['jam_selesai'],0,5) ?>
                    </div>
                    <?php if (!$is_mine): ?>
                    <div class="text-muted mt-1" style="font-size:0.68rem;">
                        <i class="fa-solid fa-user me-1"></i><?= htmlspecialchars($row['nama_peminjam'] ?: '-') ?> · <?= htmlspecialchars($row['unit_peminjam'] ?: '-') ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status & Aksi -->
                <div class="col-auto text-end d-flex flex-column align-items-end gap-2">
                    <span class="badge rounded-pill px-3 py-2 <?= $badge[0] ?>" style="font-size:0.72rem;">
                        <i class="fa-solid <?= $badge[1] ?> me-1"></i><?= $badge[2] ?>
                    </span>
                    <?php if ($is_mine && $status === 'disetujui'): ?>
                    <a href="proses_pinjam.php?aksi=kembali&id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-outline-success rounded-pill px-3"
                       style="font-size:0.72rem;"
                       onclick="return confirm('Konfirmasi pengembalian <?= htmlspecialchars(addslashes($item_name)) ?>?')">
                        <i class="fa-solid fa-rotate-left me-1"></i> Kembalikan
                    </a>
                    <?php elseif ($is_mine && $status === 'menunggu'): ?>
                    <a href="proses_pinjam.php?aksi=batal&id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-outline-danger rounded-pill px-3"
                       style="font-size:0.72rem;"
                       onclick="return confirm('Batalkan pengajuan ini?')">
                        <i class="fa-solid fa-trash-can me-1"></i> Batal
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Keperluan -->
            <?php if (!empty($row['keperluan'])): ?>
            <div class="mt-3 pt-3 border-top">
                <div class="text-muted small"><i class="fa-solid fa-quote-left me-1 opacity-40"></i><?= htmlspecialchars($row['keperluan']) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="text-center py-5">
        <i class="fa-solid fa-clock-rotate-left text-muted fs-1 mb-3 d-block opacity-20"></i>
        <p class="text-muted fw-semibold">Belum ada riwayat peminjaman.</p>
        <p class="text-muted small">Mulai buat pengajuan peminjaman barang atau ruangan.</p>
        <a href="peminjaman.php" class="btn btn-primary rounded-pill px-4 mt-2">Pinjam Sekarang</a>
    </div>
    <?php endif; ?>

</div>


<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

