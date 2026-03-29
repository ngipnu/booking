<?php
session_start();
require_once '../../config/database.php';

$current_page = 'peminjaman';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil data peminjaman
$query = "SELECT p.*, u.nama, a.nama_aset, a.kode_aset 
          FROM peminjaman p 
          JOIN users u ON p.id_user = u.id 
          JOIN aset a ON p.id_aset = a.id 
          ORDER BY p.tgl_pengajuan DESC";
$result = $koneksi->query($query);

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Peminjaman</h4>
                <p class="text-muted small mb-0">Kelola persetujuan dan pengembalian aset lembaga.</p>
            </div>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?></div>
        <?php endif; ?>

        <div class="table-responsive bg-white rounded-4 shadow-sm border border-white-50">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-light bg-opacity-50">
                        <th class="ps-4">Peminjam</th>
                        <th>Aset & Barcode</th>
                        <th>Jadwal Pinjam</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_peminjam'] ? $row['nama_peminjam'] : $row['nama']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($row['unit_peminjam'] ? $row['unit_peminjam'] : 'Unit LRC/Umum') ?></div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                <div class="badge bg-light text-muted border py-0 px-2" style="font-size: 0.65rem;"><?= $row['kode_aset'] ?></div>
                            </td>
                            <td>
                                <div class="small text-dark"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])) ?> - <?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?></div>
                                <div class="text-muted" style="font-size: 0.65rem;">Diajukan: <?= date('d/m/Y H:i', strtotime($row['tgl_pengajuan'])) ?></div>
                            </td>
                            <td class="text-center">
                                <?php if($row['status_pinjam'] == 'menunggu'): ?>
                                    <span class="badge bg-warning-soft text-warning rounded-pill">Menunggu</span>
                                <?php elseif($row['status_pinjam'] == 'disetujui'): ?>
                                    <span class="badge bg-success-soft text-success rounded-pill">Sedang Pinjam</span>
                                <?php elseif($row['status_pinjam'] == 'ditolak'): ?>
                                    <span class="badge bg-danger-soft text-danger rounded-pill">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-soft text-muted rounded-pill">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if($row['status_pinjam'] == 'menunggu'): ?>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="proses.php?aksi=setujui&id=<?= $row['id'] ?>&id_aset=<?= $row['id_aset'] ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm border-0"><i class="fa-solid fa-check"></i></a>
                                        <a href="proses.php?aksi=tolak&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm border-0"><i class="fa-solid fa-xmark"></i></a>
                                    </div>
                                <?php elseif($row['status_pinjam'] == 'disetujui'): ?>
                                    <a href="proses.php?aksi=kembali&id=<?= $row['id'] ?>&id_aset=<?= $row['id_aset'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">Kembalikan</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data peminjaman.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../layouts/footer.php'; ?>
