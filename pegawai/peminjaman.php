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

// List Kategori untuk Filter Tab
$categories = $koneksi->query("SELECT * FROM kategori");
$cat_id = $_GET['cat'] ?? 1; // Default kategori pertama

// Ambil Sesi Waktu dari Database
$waktu_res = $koneksi->query("SELECT * FROM waktu ORDER BY urutan ASC, jam_mulai ASC");
$time_slots = [];
while ($w = $waktu_res->fetch_assoc()) {
    $time_slots[] = $w;
}

// Ambil data peminjaman saya
if (!isset($_SESSION['nama_pemakai']) || !isset($_SESSION['unit_pemakai'])) {
    header("Location: identifikasi.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$nama_pemakai = $_SESSION['nama_pemakai'];
$unit_pemakai = $_SESSION['unit_pemakai'];
$my_active = $koneksi->query("SELECT p.*, a.nama_aset FROM peminjaman p 
                                JOIN aset a ON p.id_aset = a.id 
                                WHERE p.id_user = $user_id 
                                AND p.status_pinjam IN ('menunggu', 'disetujui')
                                ORDER BY p.tgl_pinjam DESC");

// Ambil aset berdasarkan kategori terpilih
$assets = $koneksi->query("SELECT * FROM aset WHERE id_kategori = $cat_id ORDER BY nama_aset ASC");

// Ambil data peminjaman untuk tanggal & jam ini
$bookings = [];
$booking_res = $koneksi->query("SELECT p.*, u.nama as user_real 
                                FROM peminjaman p 
                                JOIN users u ON p.id_user = u.id 
                                WHERE p.tgl_pinjam = '$selected_date' 
                                AND p.status_pinjam IN ('menunggu', 'disetujui')");
while ($b = $booking_res->fetch_assoc()) {
    foreach ($time_slots as $slot) {
        // Cek overlap
        if (($b['jam_mulai'] < $slot['jam_selesai']) && ($b['jam_selesai'] > $slot['jam_mulai'])) {
            $bookings[$b['id_aset']][$slot['id']] = [
                'nama' => $b['nama_peminjam'] ?: $b['user_real'],
                'unit' => $b['unit_peminjam'],
                'status' => $b['status_pinjam']
            ];
        }
    }
}
?>
<?php 
$page_title = 'Jadwal Pesanan';
include 'layouts/header.php'; 
?>

<style>
    .scheduler-container { max-width: 100%; overflow-x: auto; background: white; border-radius: 12px; border: 1px solid #eee; }
    .schedule-table { min-width: 1200px; border-collapse: separate; border-spacing: 0; }
    .schedule-table th { background: #fcfcfc; color: #666; font-size: 0.75rem; border: 1px solid #f0f0f0; weight: 400; padding: 10px 5px; text-align: center; }
    .schedule-table td { border: 1px solid #f5f5f5; height: 60px; min-width: 60px; position: relative; padding: 0; }
    .asset-col { min-width: 180px; sticky: left; background: white; z-index: 10; padding: 10px 15px !important; text-align: left !important; border-right: 2px solid #eee !important; }
    .slot-booked { background: var(--primary-color) !important; color: white !important; font-size: 0.65rem; padding: 4px; text-align: center; cursor: not-allowed; border-radius: 2px; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .slot-pending { background: #f59e0b !important; opacity: 0.7; }
    .slot-free:hover { background: #f0f7ff; cursor: pointer; }
    .date-nav { background: var(--primary-gradient) !important; color: white; border-radius: 8px; font-weight: 600; }
    .nav-tabs-custom { gap: 10px; border: 0; }
    .nav-tabs-custom .nav-link { border: 1px solid var(--primary-color); color: var(--primary-color); border-radius: 6px; padding: 4px 12px; font-size: 0.85rem; font-weight: 500; }
    .nav-tabs-custom .nav-link.active { background: var(--primary-color); color: white; }
</style>

    <div class="container-fluid max-width-1400" style="margin-top: 100px;">
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['pesan_sukses'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 py-3 animate-fade-up">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['pesan_error'])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 py-3 animate-fade-up">
                <i class="fa-solid fa-circle-xmark me-2"></i> <?= $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Info Header -->
        <div class="alert alert-info py-2 rounded-2 border-0 shadow-sm mb-3 d-flex align-items-center gap-2" style="background: #f0f7ff; color: #1e40af; border: 1px solid rgba(59,130,246,0.1) !important;">
            <i class="fa-solid fa-circle-info"></i> <span class="small fw-medium">Silakan klik kotak kosong pada jadwal untuk mengajukan peminjaman aset.</span>
        </div>

        <div class="d-flex align-items-center gap-3 mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary border-0 bg-white rounded-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle bg-white rounded-2 shadow-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i class="fa-regular fa-calendar-days"></i> <?= date('l dS F Y', strtotime($selected_date)) ?>
                </button>
                <div class="dropdown-menu p-3 shadow-lg border-0 rounded-4">
                    <input type="date" class="form-control" onchange="location.href='?date='+this.value">
                </div>
            </div>
        </div>

        <!-- Green Navigation -->
        <div class="date-nav p-2 d-flex justify-content-between align-items-center mb-3">
            <a href="?date=<?= $prev_date ?>&cat=<?= $cat_id ?>" class="text-white text-decoration-none small"><i class="fa-solid fa-arrow-left"></i> Sebelumnya</a>
            <span class="small"><?= date('l, d M Y', strtotime($selected_date)) ?> - Jadwal Peminjaman</span>
            <a href="?date=<?= $next_date ?>&cat=<?= $cat_id ?>" class="text-white text-decoration-none small">Selanjutnya <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <!-- Layout 2 Kolom -->
        <div class="row g-4">
            <div class="col-lg-9">
                <!-- Category Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom mb-3">
                    <?php while($c = $categories->fetch_assoc()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $cat_id == $c['id'] ? 'active' : '' ?>" href="?date=<?= $selected_date ?>&cat=<?= $c['id'] ?>"><?= $c['nama_kategori'] ?></a>
                    </li>
                    <?php endwhile; ?>
                </ul>

                <!-- Scheduler Grid -->
                <div class="scheduler-container shadow-sm mb-3">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="asset-col text-start">Daftar Aset</th>
                                <?php foreach($time_slots as $slot): ?>
                                <th>
                                    <div class="fw-bold fs-7"><?= htmlspecialchars($slot['nama_waktu']) ?></div>
                                    <div class="text-muted small" style="font-size: 0.6rem;"><?= date('H:i', strtotime($slot['jam_mulai'])) ?> - <?= date('H:i', strtotime($slot['jam_selesai'])) ?></div>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $assets->data_seek(0);
                            if ($assets->num_rows > 0): 
                                while ($row = $assets->fetch_assoc()): ?>
                                <tr>
                                    <td class="asset-col">
                                        <div class="fw-bold text-primary small mb-0"><?= htmlspecialchars($row['nama_aset']) ?></div>
                                        <div class="text-muted" style="font-size: 0.6rem;"><?= htmlspecialchars($row['divisi_pembeli'] ?: 'LRC An Nadzir') ?></div>
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
                                            <div class="slot-free h-100" data-bs-toggle="modal" data-bs-target="#modalPinjam" 
                                                onclick="setBookingInfo(<?= $row['id'] ?>, '<?= $row['nama_aset'] ?>', '<?= date('H:i', strtotime($slot['jam_mulai'])) ?>', '<?= date('H:i', strtotime($slot['jam_selesai'])) ?>')"></div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="<?= count($hours)+1 ?>" class="text-center py-5 text-muted">Belum ada aset ditambahkan di kategori ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="glass-card p-4 shadow-sm mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Pinjaman Aktif Saya</h6>
                    <hr class="my-3 opacity-25">
                    <?php if ($my_active->num_rows > 0): ?>
                        <div class="d-flex flex-column gap-3">
                        <?php while ($ma = $my_active->fetch_assoc()): ?>
                            <div class="p-3 bg-light rounded-4 border border-white position-relative">
                                <div class="fw-bold text-dark small mb-1"><?= htmlspecialchars($ma['nama_aset']) ?></div>
                                <div class="text-muted mb-2" style="font-size: 0.65rem;">
                                    <i class="fa-regular fa-calendar-check me-1"></i> <?= date('d M', strtotime($ma['tgl_pinjam'])) ?> | <?= substr($ma['jam_mulai'], 0, 5) ?> - <?= substr($ma['jam_selesai'], 0, 5) ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <?php if($ma['status_pinjam'] == 'menunggu'): ?>
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-2 py-1" style="font-size: 0.55rem;">⏳ Menunggu</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-soft text-success rounded-pill px-2 py-1" style="font-size: 0.55rem;">✅ Disetujui</span>
                                    <?php endif; ?>
                                    
                                    <a href="proses_pinjam.php?aksi=batal&id=<?= $ma['id'] ?>" class="text-danger small fw-bold text-decoration-none" onclick="return confirm('Batalkan pengajuan ini?')">
                                        <i class="fa-solid fa-trash-can me-1" style="font-size: 0.6rem;"></i> Batal
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 border rounded-4 border-dashed">
                            <i class="fa-solid fa-folder-open mb-2 text-muted opacity-50"></i>
                            <div class="text-muted small">Belum ada pinjaman aktif</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="glass-card p-4 shadow-sm text-center">
                    <p class="text-muted small mb-0">Butuh bantuan IT Sarpras?</p>
                    <a href="https://wa.me/62812345678" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill w-100 mt-2 fw-bold"><i class="fa-brands fa-whatsapp me-1"></i> Hubungi Admin</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pinjam -->
    <div class="modal fade" id="modalPinjam" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-effect">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Konfirmasi Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_pinjam.php" method="POST">
                    <input type="hidden" name="id_aset" id="modal_id_aset">
                    <input type="hidden" name="tgl_pinjam" value="<?= $selected_date ?>">
                    <div class="modal-body">
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">Item Terpilih</label>
                            <input type="text" class="form-control bg-light" id="modal_nama_aset" readonly>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 text-start">
                                <label class="form-label small fw-bold">Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="modal_jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-6 text-start">
                                <label class="form-label small fw-bold">Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="modal_jam_selesai" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-danger">Nama Peminjam *</label>
                            <input type="text" name="nama_peminjam" class="form-control bg-light" value="<?= htmlspecialchars($nama_pemakai) ?>" readonly required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-danger">Dari Unit *</label>
                            <input type="text" name="unit_peminjam" class="form-control bg-light" value="<?= htmlspecialchars($unit_pemakai) ?>" readonly required>
                        </div>
                        <div class="mb-0 text-start">
                            <label class="form-label small fw-bold">Keperluan</label>
                            <textarea name="keperluan" class="form-control" rows="2" placeholder="Tujuan peminjaman..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">Ajukan Pinjam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function setBookingInfo(id, nama, start, end) {
            document.getElementById('modal_id_aset').value = id;
            document.getElementById('modal_nama_aset').value = nama;
            document.getElementById('modal_jam_mulai').value = start;
            document.getElementById('modal_jam_selesai').value = end;
        }
    </script>
</body>
</html>
