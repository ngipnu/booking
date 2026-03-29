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

// Jam Operasional (4:00 - 22:00)
$hours = range(4, 21); // 4-21 (slot 21-22)

// Ambil data peminjaman saya
$user_id = $_SESSION['user_id'];
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
    $start_h = (int)date('H', strtotime($b['jam_mulai']));
    $end_h = (int)date('H', strtotime($b['jam_selesai']));
    for ($h = $start_h; $h < $end_h; $h++) {
        $bookings[$b['id_aset']][$h] = [
            'nama' => $b['nama_peminjam'] ?: $b['user_real'],
            'unit' => $b['unit_peminjam'],
            'status' => $b['status_pinjam']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Peminjaman | An Nadzir</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/logo/logo_round.png?v=2">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body { background: #fdfdfd; font-family: 'Outfit', sans-serif; }
        .scheduler-container { max-width: 100%; overflow-x: auto; background: white; border-radius: 12px; border: 1px solid #eee; }
        .schedule-table { min-width: 1200px; border-collapse: separate; border-spacing: 0; }
        .schedule-table th { background: #fcfcfc; color: #666; font-size: 0.75rem; border: 1px solid #f0f0f0; weight: 400; padding: 10px 5px; text-align: center; }
        .schedule-table td { border: 1px solid #f5f5f5; height: 60px; min-width: 60px; position: relative; padding: 0; }
        .asset-col { min-width: 180px; sticky: left; background: white; z-index: 10; padding: 10px 15px !important; text-align: left !important; border-right: 2px solid #eee !important; }
        .slot-booked { background: #2b78b8 !important; color: white !important; font-size: 0.65rem; padding: 4px; text-align: center; cursor: not-allowed; border-radius: 2px; height: 100%; display: flex; flex-direction: column; justify-content: center; }
        .slot-pending { background: #f59e0b !important; opacity: 0.7; }
        .slot-free:hover { background: #f0f7ff; cursor: pointer; }
        .date-nav { background: #00b012; color: white; border-radius: 8px; font-weight: 600; }
        .nav-tabs-custom { gap: 10px; border: 0; }
        .nav-tabs-custom .nav-link { border: 1px solid #2b78b8; color: #2b78b8; border-radius: 6px; padding: 4px 12px; font-size: 0.85rem; font-weight: 500; }
        .nav-tabs-custom .nav-link.active { background: #2b78b8; color: white; }
    </style>
</head>
<body class="bg-light">

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg glass-effect fixed-top shadow-sm py-2 py-md-3 px-3 px-md-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary" href="#">
                <img src="../assets/logo/logo_round.png" alt="Logo" width="40" height="40" class="rounded-circle shadow-sm">
                <div class="d-none d-sm-block">
                    <div class="lh-1" style="font-size: 1rem;">An Nadzir <span class="text-dark">LRC</span></div>
                    <div class="text-muted" style="font-size: 0.65rem; font-weight: 500;">Sistem Peminjaman Aset</div>
                </div>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="d-none d-md-block text-end">
                    <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="text-muted" style="font-size: 0.65rem;">Username: <?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm rounded-circle p-1 overflow-hidden" type="button" data-bs-toggle="dropdown" style="width: 42px; height: 42px;">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=0284c7&color=fff&bold=true" alt="Avatar" width="38" height="38" class="rounded-circle">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 py-2" style="min-width: 200px;">
                        <li class="px-3 py-2 border-bottom mb-2 d-md-none">
                            <div class="fw-bold text-dark small"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                            <div class="text-muted small">@<?= htmlspecialchars($_SESSION['username']) ?></div>
                        </li>
                        <li><a class="dropdown-item py-2 px-3 fw-medium text-danger" href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2 small"></i> Keluar Sistem</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

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

        <div class="d-flex align-items-center gap-2 mb-3">
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
                                <?php foreach($hours as $h): ?>
                                <th>
                                    <div class="fw-bold fs-7">Jam <?= $h ?></div>
                                    <div class="text-muted small" style="font-size: 0.6rem;"><?= sprintf('%02d:00', $h) ?></div>
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
                                    <?php foreach($hours as $h): ?>
                                    <td class="slot-cell">
                                        <?php if (isset($bookings[$row['id']][$h])): 
                                            $b = $bookings[$row['id']][$h];
                                            $status_class = ($b['status'] == 'menunggu') ? 'slot-pending' : '';
                                        ?>
                                            <div class="slot-booked <?= $status_class ?>" title="<?= $b['nama'] ?> (<?= $b['unit'] ?>)">
                                                <div class="fw-bold"><?= htmlspecialchars($b['nama']) ?></div>
                                                <div style="font-size: 0.55rem;"><?= htmlspecialchars($b['unit']) ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="slot-free h-100" data-bs-toggle="modal" data-bs-target="#modalPinjam" 
                                                onclick="setBookingInfo(<?= $row['id'] ?>, '<?= $row['nama_aset'] ?>', <?= $h ?>)"></div>
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
                            <div class="p-3 bg-light rounded-4 border border-white">
                                <div class="fw-bold text-dark small mb-1"><?= htmlspecialchars($ma['nama_aset']) ?></div>
                                <div class="text-muted mb-2" style="font-size: 0.65rem;">
                                    <i class="fa-regular fa-calendar-check me-1"></i> <?= date('d M', strtotime($ma['tgl_pinjam'])) ?> | <?= substr($ma['jam_mulai'], 0, 5) ?> - <?= substr($ma['jam_selesai'], 0, 5) ?>
                                </div>
                                <?php if($ma['status_pinjam'] == 'menunggu'): ?>
                                    <span class="badge bg-warning-soft text-warning rounded-pill px-2 py-1" style="font-size: 0.55rem;">Persetujuan Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-success-soft text-success rounded-pill px-2 py-1" style="font-size: 0.55rem;">Tengah Digunakan</span>
                                <?php endif; ?>
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
                            <input type="text" name="nama_peminjam" class="form-control" placeholder="Siapa yang memakai?" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-danger">Dari Unit *</label>
                            <input type="text" name="unit_peminjam" class="form-control" list="list_unit" placeholder="Unit/Lembaga" required>
                            <datalist id="list_unit">
                                <option value="LRC SDIT">
                                    <option value="SMPIT An Nadzir">
                                    <option value="TKIT An Nadzir">
                                    <option value="Yayasan / Pusat">
                            </datalist>
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
        function setBookingInfo(id, nama, jam) {
            document.getElementById('modal_id_aset').value = id;
            document.getElementById('modal_nama_aset').value = nama;
            
            let start = jam.toString().padStart(2, '0') + ':00';
            let end = (jam + 1).toString().padStart(2, '0') + ':00';
            
            document.getElementById('modal_jam_mulai').value = start;
            document.getElementById('modal_jam_selesai').value = end;
        }
    </script>
</body>
</html>
