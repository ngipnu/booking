<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'pegawai' && $_SESSION['role'] !== 'user')) {
    header("Location: ../login.php");
    exit;
}

// Tanggal terpilih (Default: Hari ini)
$selected_date = $_GET['date'] ?? date('Y-m-d');
$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));

// List Gedung untuk Filter Tab
$gedungs = $koneksi->query("SELECT * FROM gedung ORDER BY nama_gedung ASC");
$gedung_id = $_GET['gedung'] ?? '';
if (empty($gedung_id) && $gedungs->num_rows > 0) {
    $gedung_row = $koneksi->query("SELECT id FROM gedung ORDER BY nama_gedung ASC LIMIT 1")->fetch_assoc();
    $gedung_id = $gedung_row['id'];
}

// Ambil Sesi Waktu dari Database
$waktu_res = $koneksi->query("SELECT * FROM waktu ORDER BY urutan ASC, jam_mulai ASC");
$time_slots = [];
while ($w = $waktu_res->fetch_assoc()) {
    $time_slots[] = $w;
}

// Ambil data peminjaman saya
$user_id = $_SESSION['user_id'];
$nama_pemakai = $_SESSION['nama_pemakai'];
$unit_pemakai = $_SESSION['unit_pemakai'];

// Ambil ruangan berdasarkan gedung terpilih
$rooms = $koneksi->query("SELECT * FROM ruangan WHERE id_gedung = '$gedung_id' AND bisa_dipinjam = 'Y' ORDER BY nama_ruangan ASC");

// Ambil data peminjaman untuk tanggal & jam ini
$bookings = [];
$booking_res = $koneksi->query("SELECT p.*, u.nama as user_real 
                                FROM peminjaman p 
                                JOIN users u ON p.id_user = u.id 
                                WHERE p.tgl_pinjam = '$selected_date' 
                                AND p.id_ruangan IS NOT NULL
                                AND p.status_pinjam IN ('menunggu', 'disetujui')");
while ($b = $booking_res->fetch_assoc()) {
    foreach ($time_slots as $slot) {
        if (($b['jam_mulai'] < $slot['jam_selesai']) && ($b['jam_selesai'] > $slot['jam_mulai'])) {
            $bookings[$b['id_ruangan']][$slot['id']] = [
                'nama' => $b['nama_peminjam'] ?: $b['user_real'],
                'unit' => $b['unit_peminjam'],
                'status' => $b['status_pinjam']
            ];
        }
    }
}

$page_title = 'Jadwal Ruangan';
include 'layouts/header.php'; 
?>

<style>
    .scheduler-container { max-width: 100%; overflow-x: auto; background: white; border-radius: 12px; border: 1px solid #eee; }
    .schedule-table { min-width: 1200px; border-collapse: separate; border-spacing: 0; }
    .schedule-table th { background: #fcfcfc; color: #666; font-size: 0.75rem; border: 1px solid #f0f0f0; weight: 400; padding: 10px 5px; text-align: center; }
    .schedule-table td { border: 1px solid #f5f5f5; height: 60px; min-width: 60px; position: relative; padding: 0; }
    .asset-col { min-width: 200px; sticky: left; background: white; z-index: 10; padding: 10px 15px !important; text-align: left !important; border-right: 2px solid #eee !important; }
    .slot-booked { background: var(--primary-color) !important; color: white !important; font-size: 0.65rem; padding: 4px; text-align: center; cursor: not-allowed; border-radius: 2px; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .slot-pending { background: #f59e0b !important; opacity: 0.7; }
    .slot-free:hover { background: #f0f7ff; cursor: pointer; }
    .date-nav { background: #2563eb !important; color: white; border-radius: 8px; font-weight: 600; }
    .nav-tabs-custom { gap: 10px; border: 0; }
    .nav-tabs-custom .nav-link { border: 1px solid #2563eb; color: #2563eb; border-radius: 6px; padding: 4px 12px; font-size: 0.85rem; font-weight: 500; }
    .nav-tabs-custom .nav-link.active { background: #2563eb; color: white; }
</style>

    <div class="container-fluid max-width-1400" style="margin-top: 100px;">
        <div class="d-flex align-items-center gap-3 mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary border-0 bg-white rounded-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle bg-white rounded-2 shadow-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i class="fa-regular fa-calendar-days"></i> <?= date('l dS F Y', strtotime($selected_date)) ?>
                </button>
                <div class="dropdown-menu p-3 shadow-lg border-0 rounded-4">
                    <input type="date" class="form-control" onchange="location.href='?date='+this.value+'&gedung=<?= $gedung_id ?>'">
                </div>
            </div>
        </div>

        <div class="date-nav p-2 d-flex justify-content-between align-items-center mb-3">
            <a href="?date=<?= $prev_date ?>&gedung=<?= $gedung_id ?>" class="text-white text-decoration-none small"><i class="fa-solid fa-arrow-left"></i> Sebelumnya</a>
            <span class="small"><?= date('l, d M Y', strtotime($selected_date)) ?> - Jadwal Penggunaan Ruangan</span>
            <a href="?date=<?= $next_date ?>&gedung=<?= $gedung_id ?>" class="text-white text-decoration-none small">Selanjutnya <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            <div class="col-lg-12">
                <ul class="nav nav-tabs nav-tabs-custom mb-3">
                    <?php 
                    $gedungs->data_seek(0);
                    while($g = $gedungs->fetch_assoc()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $gedung_id == $g['id'] ? 'active' : '' ?>" href="?date=<?= $selected_date ?>&gedung=<?= $g['id'] ?>"><?= $g['nama_gedung'] ?></a>
                    </li>
                    <?php endwhile; ?>
                </ul>

                <div class="scheduler-container shadow-sm mb-3">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="asset-col text-start">Daftar Ruangan</th>
                                <?php foreach($time_slots as $slot): ?>
                                <th>
                                    <div class="fw-bold fs-7"><?= htmlspecialchars($slot['nama_waktu']) ?></div>
                                    <div class="text-muted small" style="font-size: 0.6rem;"><?= date('H:i', strtotime($slot['jam_mulai'])) ?> - <?= date('H:i', strtotime($slot['jam_selesai'])) ?></div>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rooms->num_rows > 0): 
                                while ($row = $rooms->fetch_assoc()): ?>
                                <tr>
                                    <td class="asset-col">
                                        <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($row['nama_ruangan']) ?></div>
                                        <div class="text-muted" style="font-size: 0.6rem;">Kapasitas: <?= $row['kapasitas'] ?> Orang</div>
                                    </td>
                                    <?php foreach($time_slots as $slot): ?>
                                    <td class="slot-cell">
                                        <?php if (isset($bookings[$row['id']][$slot['id']])): 
                                            $b = $bookings[$row['id']][$slot['id']];
                                            $status_class = ($b['status'] == 'menunggu') ? 'slot-pending' : '';
                                        ?>
                                            <div class="slot-booked <?= $status_class ?>" title="<?= $b['nama'] ?> (<?= $b['unit'] ?>)">
                                                <div class="fw-bold"><?= htmlspecialchars($b['nama']) ?></div>
                                                <div style="font-size: 0.55rem;"><?= htmlspecialchars($b['unit']) ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="slot-free h-100" data-bs-toggle="modal" data-bs-target="#modalPinjamRuang" 
                                                onclick="setRoomBookingInfo(<?= $row['id'] ?>, '<?= $row['nama_ruangan'] ?>', '<?= date('H:i', strtotime($slot['jam_mulai'])) ?>', '<?= date('H:i', strtotime($slot['jam_selesai'])) ?>', '<?= $row['nama_gedung'] ?>')"></div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="<?= count($time_slots)+1 ?>" class="text-center py-5 text-muted">Belum ada ruangan yang tersedia untuk dipinjam di gedung ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pinjam Ruangan -->
    <div class="modal fade" id="modalPinjamRuang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-effect">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Booking Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_pinjam.php" method="POST">
                    <input type="hidden" name="aksi" value="tambah_ruangan">
                    <input type="hidden" name="id_ruangan" id="modal_id_ruangan">
                    <input type="hidden" name="tgl_pinjam" value="<?= $selected_date ?>">
                    <div class="modal-body">
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">Ruangan Terpilih</label>
                            <input type="text" class="form-control bg-light" id="modal_nama_ruangan" readonly>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 text-start">
                                <label class="form-label small fw-bold">Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="modal_r_jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-6 text-start">
                                <label class="form-label small fw-bold">Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="modal_r_jam_selesai" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">Peminjam</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($nama_pemakai) ?> (<?= htmlspecialchars($unit_pemakai) ?>)" readonly>
                        </div>
                        <div class="mb-0 text-start">
                            <label class="form-label small fw-bold">Keperluan / Acara</label>
                            <textarea name="keperluan" class="form-control" rows="2" placeholder="Sebutkan nama acara atau keperluan ruangan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">Booking Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function setRoomBookingInfo(id, nama, start, end, gedung) {
            document.getElementById('modal_id_ruangan').value = id;
            document.getElementById('modal_nama_ruangan').value = nama + ' (' + gedung + ')';
            document.getElementById('modal_r_jam_mulai').value = start;
            document.getElementById('modal_r_jam_selesai').value = end;
        }
    </script>
</body>
</html>
