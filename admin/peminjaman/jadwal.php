<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'peminjaman';

$selected_date = $_GET['date'] ?? date('Y-m-d');
$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));

$categories = $koneksi->query("SELECT * FROM kategori");
$cat_id = $_GET['cat'] ?? 1;

// Ambil Sesi Waktu dari Database
$waktu_res = $koneksi->query("SELECT * FROM waktu ORDER BY urutan ASC, jam_mulai ASC");
$time_slots = [];
while ($w = $waktu_res->fetch_assoc()) {
    $time_slots[] = $w;
}

$assets = $koneksi->query("SELECT * FROM aset WHERE id_kategori = $cat_id ORDER BY nama_aset ASC");

$bookings = [];
$booking_res = $koneksi->query("SELECT p.*, u.nama as user_real 
                                FROM peminjaman p 
                                JOIN users u ON p.id_user = u.id 
                                WHERE p.tgl_pinjam = '$selected_date' 
                                AND p.status_pinjam IN ('menunggu', 'disetujui')");
while ($b = $booking_res->fetch_assoc()) {
    foreach ($time_slots as $slot) {
        // Cek apakah booking ini melewati/berada di sesi waktu ini
        // Kondisi overlap: (S_booking < E_slot) AND (E_booking > S_slot)
        if (($b['jam_mulai'] < $slot['jam_selesai']) && ($b['jam_selesai'] > $slot['jam_mulai'])) {
            $bookings[$b['id_aset']][$slot['id']] = [
                'id_p' => $b['id'],
                'nama' => $b['nama_peminjam'] ?: $b['user_real'],
                'unit' => $b['unit_peminjam'],
                'status' => $b['status_pinjam']
            ];
        }
    }
}

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<style>
    .scheduler-container { max-width: 100%; overflow-x: auto; background: white; border-radius: 16px; border: 1px solid #f0f0f0; }
    .schedule-table { min-width: 1200px; border-collapse: separate; border-spacing: 0; }
    .schedule-table th { background: #fafafa; color: #888; font-size: 0.7rem; border-bottom: 1px solid #f0f0f0; padding: 12px 5px; text-align: center; }
    .schedule-table td { border-bottom: 1px solid #f8f8f8; border-right: 1px solid #f8f8f8; height: 65px; position: relative; padding: 0; }
    .asset-col { min-width: 200px; sticky: left; background: white; z-index: 10; padding: 12px 15px !important; text-align: left !important; border-right: 2px solid #f0f0f0 !important; }
    .slot-booked { background: #3b82f6 !important; color: white !important; font-size: 0.65rem; padding: 6px; text-align: center; cursor: pointer; height: 100%; display: flex; flex-direction: column; justify-content: center; transition: transform 0.2s; }
    .slot-booked:hover { transform: scale(1.02); filter: brightness(1.1); }
    .slot-pending { background: #fbbf24 !important; }
    .slot-free:hover { background: #f3f8ff; cursor: pointer; }
    .nav-tabs-custom { gap: 8px; border: 0; }
    .nav-tabs-custom .nav-link { border: 0; background: #f1f5f9; color: #64748b; border-radius: 10px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; }
    .nav-tabs-custom .nav-link.active { background: #3b82f6; color: white; }
</style>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Manajemen Waktu Peminjaman</h4>
                <p class="text-muted small mb-0">Monitor dan kelola jadwal pemakaian aset secara visual (Admin View).</p>
            </div>
            <div class="d-flex gap-2">
                <a href="?date=<?= $prev_date ?>&cat=<?= $cat_id ?>" class="btn btn-white border shadow-sm rounded-pill"><i class="fa-solid fa-chevron-left"></i></a>
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-regular fa-calendar-check me-2 text-primary"></i> <?= date('d M Y', strtotime($selected_date)) ?>
                    </button>
                    <div class="dropdown-menu p-3 border-0 shadow-lg rounded-4 mt-2">
                        <input type="date" class="form-control" onchange="location.href='?date='+this.value">
                    </div>
                </div>
                <a href="?date=<?= $next_date ?>&cat=<?= $cat_id ?>" class="btn btn-white border shadow-sm rounded-pill"><i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>

        <!-- Master Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom mb-3">
            <?php $categories->data_seek(0); while($c = $categories->fetch_assoc()): ?>
            <li class="nav-item">
                <a class="nav-link <?= $cat_id == $c['id'] ? 'active' : '' ?>" href="?date=<?= $selected_date ?>&cat=<?= $c['id'] ?>"><?= $c['nama_kategori'] ?></a>
            </li>
            <?php endwhile; ?>
        </ul>

        <div class="scheduler-container shadow-sm overflow-hidden">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th class="asset-col">Items</th>
                        <?php foreach($time_slots as $slot): ?>
                        <th>
                            <div class="fw-bold"><?= htmlspecialchars($slot['nama_waktu']) ?></div>
                            <div class="text-muted" style="font-size: 0.6rem;"><?= date('H:i', strtotime($slot['jam_mulai'])) ?>-<?= date('H:i', strtotime($slot['jam_selesai'])) ?></div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($assets->num_rows > 0): ?>
                        <?php while ($row = $assets->fetch_assoc()): ?>
                        <tr>
                            <td class="asset-col">
                                <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                <div class="text-muted" style="font-size: 0.6rem;"><?= htmlspecialchars($row['unit_pengguna'] ?: 'Umum') ?></div>
                            </td>
                            <?php foreach($time_slots as $slot): ?>
                            <td class="slot-cell">
                                <?php if (isset($bookings[$row['id']][$slot['id']])): 
                                    $b = $bookings[$row['id']][$slot['id']];
                                    $s_class = ($b['status'] == 'menunggu') ? 'slot-pending' : '';
                                ?>
                                    <div class="slot-booked <?= $s_class ?>" onclick="location.href='index.php?search=<?= urlencode($b['nama']) ?>'" title="Klik untuk mengelola: <?= $b['nama'] ?>">
                                        <div class="fw-bold"><?= htmlspecialchars($b['nama']) ?></div>
                                        <div style="font-size: 0.55rem;"><?= htmlspecialchars($b['unit']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="slot-free h-100"></div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($hours)+1 ?>" class="text-center py-5 text-muted small">Tidak ada aset.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../layouts/footer.php'; ?>
