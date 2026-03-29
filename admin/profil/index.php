<?php
session_start();
require_once '../../config/database.php';

$current_page = 'profil';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil profil lembaga
$profil = $koneksi->query("SELECT * FROM profil_lembaga LIMIT 1")->fetch_assoc();

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<main class="main-content">
    <?php include '../layouts/topbar.php'; ?>

    <div class="px-3 px-md-4 pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Identitas Lembaga</h4>
                <p class="text-muted small mb-0">Atur profil An Nadzir Islamic School yang akan muncul di aplikasi.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2">
                <i class="fa-solid fa-save"></i> <span>Simpan Perubahan</span>
            </button>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card text-center">
                    <div class="card-body p-5">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="../../assets/logo/logo_round.png?v=2" alt="Logo" class="rounded-circle shadow-lg border border-5 border-white" style="width: 150px; height: 150px; object-fit: contain;">
                            <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-2 shadow-sm border-white border-3"><i class="fa-solid fa-camera"></i></button>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= $profil['nama_lembaga'] ?></h5>
                        <p class="text-muted small">Cilegon, Banten</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="card-body p-4">
                        <h6 class="font-heading fw-bold text-dark mb-4 border-bottom pb-2">Informasi Umum</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Nama Resmi Lembaga</label>
                                <input type="text" class="form-control" value="<?= $profil['nama_lembaga'] ?>">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Email Sekolah</label>
                                <input type="email" class="form-control" value="<?= $profil['email'] ?>">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">No. Telepon / WhatsApp</label>
                                <input type="text" class="form-control" value="<?= $profil['telepon'] ?>">
                            </div>
                            <div class="col-12 mt-4 text-start">
                                <label class="form-label small fw-bold text-muted">Alamat Lengkap</label>
                                <textarea class="form-control" rows="3"><?= $profil['alamat'] ?></textarea>
                            </div>
                        </div>

                        <h6 class="font-heading fw-bold text-dark mb-4 border-bottom pb-2 mt-5">Pengaturan Shared-Account</h6>
                        <div class="bg-primary-soft text-dark p-3 rounded-4 border border-white">
                            <i class="fa-solid fa-circle-info me-1"></i> <span class="small fw-medium">Sistem saat ini dikonfigurasi untuk <strong>"Multi-User Sharing Account"</strong>. Peminjam akan diwajibkan mengisi Nama dan Unit secara manual setiap kali melakukan peminjaman.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../layouts/footer.php'; ?>
