<?php
require_once 'config/database.php';

$kode_aset = $_GET['kode'] ?? '';

if (empty($kode_aset)) {
    die("Kode aset tidak valid.");
}

$query = "SELECT a.*, k.nama_kategori, k.icon as kat_icon, r.nama_ruangan, g.nama_gedung 
          FROM aset a 
          LEFT JOIN kategori k ON a.id_kategori = k.id 
          LEFT JOIN ruangan r ON a.id_ruangan = r.id
          LEFT JOIN gedung g ON r.id_gedung = g.id
          WHERE a.kode_aset = '" . $koneksi->real_escape_string($kode_aset) . "'";
$result = $koneksi->query($query);
$aset = $result->fetch_assoc();

if (!$aset) {
    die("Sarana/Aset tidak ditemukan.");
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identitas Sarana - <?= htmlspecialchars($aset['nama_aset']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .header-bg {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 3rem 1rem;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .glass-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: -40px;
            padding: 2rem;
        }
    </style>
</head>

<body>

    <div class="header-bg shadow-sm">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-<?= $aset['kat_icon'] ?: 'box' ?> me-2"></i><?= htmlspecialchars($aset['nama_aset']) ?></h3>
        <p class="mb-0 opacity-75">Sistem Informasi Manajemen Sarana Prasarana</p>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="glass-card text-center mb-4 border">
                    <span class="badge border text-dark fs-6 px-3 py-2 fw-bold mb-3 d-inline-block rounded-pill bg-light" style="letter-spacing: 1px;">
                        <i class="fa-solid fa-qrcode me-2 text-muted"></i><?= htmlspecialchars($aset['kode_aset']) ?>
                    </span>
                    <h5 class="fw-bold text-dark mb-1">Status: <?= $aset['kondisi'] == 'baik' ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i> Baik & Tersedia</span>' : '<span class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> ' . strtoupper(str_replace('_', ' ', $aset['kondisi'])) . '</span>' ?></h5>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>Spesifikasi Sarana</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Kategori</span>
                                <span class="fw-bold"><?= htmlspecialchars($aset['nama_kategori']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Merek</span>
                                <span class="fw-bold"><?= htmlspecialchars($aset['merk'] ?: '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Penanggung Jawab</span>
                                <span class="fw-bold"><?= htmlspecialchars($aset['penanggung_jawab'] ?: '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Unit Terkait</span>
                                <span class="fw-bold"><?= htmlspecialchars($aset['unit_pengguna'] ?: '-') ?></span>
                            </li>
                            <li class="list-group-item px-0">
                                <span class="text-muted d-block mb-1">Lokasi Penyimpanan</span>
                                <span class="fw-bold d-block">
                                    <?php if ($aset['id_ruangan']): ?>
                                        <div class="text-primary"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($aset['nama_ruangan']) ?> (<?= htmlspecialchars($aset['nama_gedung']) ?>)</div>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($aset['lokasi_simpan']) ?>
                                </span>
                            </li>
                        </ul>

                        <?php if ($aset['bisa_dipinjam'] == 'Y'): ?>
                            <div class="alert alert-info border-0 rounded-3 text-center mb-0">
                                <i class="fa-solid fa-comment-dots fs-3 text-info mb-2"></i>
                                <h6 class="fw-bold text-dark">Dapat Dipinjam</h6>
                                <p class="small text-muted mb-0">Sarana ini diperbolehkan untuk dipinjam oleh unit/pegawai melalui sistem reservasi.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary border-0 rounded-3 text-center mb-0">
                                <i class="fa-solid fa-lock fs-3 text-secondary mb-2"></i>
                                <h6 class="fw-bold text-dark">Khusus Internal</h6>
                                <p class="small text-muted mb-0">Sarana ini hanya untuk penggunaan internal lokasi dan tidak untuk dipinjam pakaikan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    Diverifikasi oleh Sistem Manajemen Aset
                </div>
            </div>
        </div>
    </div>
</body>

</html>