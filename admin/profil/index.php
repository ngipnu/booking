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

    <form action="proses.php" method="POST">
    <div class="px-3 px-md-4 pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="font-heading fw-bold text-dark mb-1">Identitas Lembaga</h4>
                <p class="text-muted small mb-0">Atur profil dan tema visual An Nadzir Islamic School.</p>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2">
                <i class="fa-solid fa-save"></i> <span>Simpan Perubahan</span>
            </button>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4"><?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card text-center mb-4">
                    <div class="card-body p-5">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="../../assets/logo/logo_round.png?v=2" alt="Logo" class="rounded-circle shadow-lg border border-5 border-white" style="width: 150px; height: 150px; object-fit: contain;">
                            <button type="button" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-2 shadow-sm border-white border-3"><i class="fa-solid fa-camera"></i></button>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= $profil['nama_lembaga'] ?></h5>
                        <p class="text-muted small">Panel Administrasi</p>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-body p-4">
                        <h6 class="font-heading fw-bold text-dark mb-4 border-bottom pb-2">Tema Dashboard</h6>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Aksen Sidebar (Gradient)</label>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php 
                                $gradients = [
                                    'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)' => 'Midnight',
                                    'linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%)' => 'Ocean',
                                    'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)' => 'Royal',
                                    'linear-gradient(135deg, #10b981 0%, #059669 100%)' => 'Emerald',
                                    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' => 'Sunset',
                                    'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)' => 'Ruby',
                                ];
                                foreach($gradients as $grad => $name): 
                                ?>
                                <label class="theme-preset" style="cursor: pointer;" title="<?= $name ?>">
                                    <input type="radio" name="preset_grad" value="<?= $grad ?>" class="btn-check preset-radio" <?= $profil['sidebar_gradient'] == $grad ? 'checked' : '' ?>>
                                    <div class="rounded-circle border border-2 preset-preview <?= $profil['sidebar_gradient'] == $grad ? 'border-primary shadow' : 'border-white' ?>" style="width: 38px; height: 38px; background: <?= $grad ?>;"></div>
                                </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="p-3 rounded-4 bg-light border border-white mb-3">
                                <label class="form-label small fw-bold text-muted mb-2">Custom Gradient Builder</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex flex-column align-items-center">
                                        <input type="color" id="grad_color_1" class="form-control form-control-color border-0 p-0 mb-1" value="#0ea5e9" style="width: 35px; height: 35px; background:none;">
                                        <span class="text-muted" style="font-size: 10px;">Warna 1</span>
                                    </div>
                                    <i class="fa-solid fa-arrow-right text-muted small"></i>
                                    <div class="d-flex flex-column align-items-center">
                                        <input type="color" id="grad_color_2" class="form-control form-control-color border-0 p-0 mb-1" value="#2563eb" style="width: 35px; height: 35px; background:none;">
                                        <span class="text-muted" style="font-size: 10px;">Warna 2</span>
                                    </div>
                                    <div class="ms-auto">
                                        <div id="live_grad_preview" class="rounded-3 shadow-sm border border-white" style="width: 80px; height: 45px; background: <?= $profil['sidebar_gradient'] ?>;"></div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="sidebar_gradient" id="final_sidebar_gradient" value="<?= $profil['sidebar_gradient'] ?>">
                            <div class="form-text small text-muted">Aksi pilih preset atau buat warna sendiri di atas.</div>
                        </div>

                        <div>
                            <label class="form-label small fw-bold text-muted">Warna Font Topbar</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" class="form-control form-control-color border-0 p-0" name="topbar_color" value="<?= $profil['topbar_color'] ?>" style="width: 40px; height: 40px; background: none;">
                                <input type="text" class="form-control form-control-sm" value="<?= $profil['topbar_color'] ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="card-body p-4">
                        <h6 class="font-heading fw-bold text-dark mb-4 border-bottom pb-2">Informasi Umum</h6>
                        <div class="row g-3">
                            <div class="col-md-12 text-start">
                                <label class="form-label small fw-bold text-muted">Nama Resmi Lembaga</label>
                                <input type="text" name="nama_lembaga" class="form-control" value="<?= $profil['nama_lembaga'] ?>" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Email Sekolah</label>
                                <input type="email" name="email" class="form-control" value="<?= $profil['email_admin'] ?>" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">No. Telepon / WhatsApp</label>
                                <input type="text" name="telepon" class="form-control" value="<?= $profil['telepon'] ?>">
                            </div>
                            <div class="col-12 mt-4 text-start">
                                <label class="form-label small fw-bold text-muted">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3"><?= $profil['alamat'] ?></textarea>
                            </div>
                        </div>

                        <h6 class="font-heading fw-bold text-dark mb-4 border-bottom pb-2 mt-5 text-start">Notifikasi Pengelola (Email)</h6>
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Email Pengelola Inventaris</label>
                                <input type="email" name="email_pengelola_inventaris" class="form-control" value="<?= $profil['email_pengelola_inventaris'] ?>" placeholder="admin-inventaris@annadzir.sch.id">
                                <div class="form-text small opacity-75">Menerima email jika ada pengajuan pinjam barang.</div>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Email Pengelola Ruangan</label>
                                <input type="email" name="email_pengelola_ruangan" class="form-control" value="<?= $profil['email_pengelola_ruangan'] ?>" placeholder="admin-ruangan@annadzir.sch.id">
                                <div class="form-text small opacity-75">Menerima email jika ada booking ruangan.</div>
                            </div>
                        </div>

                        <h6 class="font-heading fw-bold text-dark mb-4 border-bottom pb-2 mt-5 text-start">Konfigurasi Sistem</h6>
                        <div class="bg-primary-soft text-dark p-3 rounded-4 border border-white text-start">
                            <i class="fa-solid fa-circle-info me-1"></i> <span class="small fw-medium">Sistem saat ini dikonfigurasi untuk <strong>"Multi-User Sharing Account"</strong>. Peminjam diwajibkan mengisi Nama dan Unit secara manual.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</main>


<?php include '../layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const finalInput = document.getElementById('final_sidebar_gradient');
    const presetRadios = document.querySelectorAll('.preset-radio');
    const color1 = document.getElementById('grad_color_1');
    const color2 = document.getElementById('grad_color_2');
    const livePreview = document.getElementById('live_grad_preview');
    const presetPreviews = document.querySelectorAll('.preset-preview');

    function updateFinal(val) {
        finalInput.value = val;
        livePreview.style.background = val;
        // Juga update sidebar secara live jika mungkin
        const sidebar = document.querySelector('.offcanvas-sidebar');
        if(sidebar) sidebar.style.background = val;
    }

    presetRadios.forEach((radio, idx) => {
        radio.addEventListener('change', function() {
            if(this.checked) {
                updateFinal(this.value);
                // Highlight border
                presetPreviews.forEach(p => p.classList.replace('border-primary', 'border-white'));
                presetPreviews.forEach(p => p.classList.remove('shadow'));
                presetPreviews[idx].classList.replace('border-white', 'border-primary');
                presetPreviews[idx].classList.add('shadow');
            }
        });
    });

    function generateCustom() {
        const val = `linear-gradient(135deg, ${color1.value} 0%, ${color2.value} 100%)`;
        updateFinal(val);
        // Uncheck presets
        presetRadios.forEach(r => r.checked = false);
        presetPreviews.forEach(p => p.classList.replace('border-primary', 'border-white'));
    }

    color1.addEventListener('input', generateCustom);
    color2.addEventListener('input', generateCustom);
});
</script>
