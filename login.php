<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard/index.php');
        exit;
    } else {
        header('Location: pegawai/dashboard.php');
        exit;
    }
}

$error = '';

if (isset($_POST['login'])) {
    $niy = $koneksi->real_escape_string($_POST['niy']);
    $password = $_POST['password'];

    $result = $koneksi->query("SELECT * FROM users WHERE niy = '$niy'");

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['niy'] = $row['niy'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header('Location: admin/dashboard/index.php');
            } else {
                header('Location: pegawai/dashboard.php');
            }
            exit;
        } else {
            $error = "Kata sandi salah.";
        }
    } else {
        $error = "NIY tidak ditemukan atau akun belum terdaftar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Booking Fasilitas Annadzir</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo/logo_round.png?v=1">
    <!-- Bootstrap 5 (Lokal) -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Lokal) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--bg-soft) 0%, rgba(59, 130, 246, 0.1) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4 py-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-up show">
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="text-center mb-4">
                            <img src="assets/logo/logo_round.png?v=1" alt="Logo Annadzir" class="mb-3 rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: contain;">
                            <h3 class="font-heading fw-bold text-dark mb-1">Portal Sign In</h3>
                            <p class="text-muted small">Authentication Khusus Pegawai Yayasan</p>
                        </div>

                        <?php if($error) : ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                <div><?= $error ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="niy" class="form-label fw-medium small">Nomor Induk Yayasan (NIY)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0" id="basic-addon1"><i class="fa-regular fa-id-badge"></i></span>
                                    <input type="text" id="niy" name="niy" class="form-control border-start-0 ps-0 bg-light" placeholder="Masukkan NIY Anda" autocomplete="off" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-medium small">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0" id="basic-addon2"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" id="password" name="password" class="form-control border-start-0 ps-0 bg-light" placeholder="Masukkan kata sandi" required>
                                </div>
                            </div>

                            <button type="submit" name="login" class="btn btn-primary w-100 py-2 mb-3 rounded-pill fw-medium shadow-sm">
                                Masuk Sistem
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="index.php" class="text-decoration-none text-muted small footer-link">
                                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
