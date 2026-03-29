<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
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
        
        // Verifikasi password menggunakan bcrypt function natif PHP
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['niy'] = $row['niy'];
            $_SESSION['role'] = $row['role'];

            // Redirect berdasarkan role
            if ($row['role'] == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: pegawai/dashboard.php'); // Buat folder pegawai jika ada pegawai
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-main) 0%, rgba(33, 169, 160, 0.1) 100%);
        }
        .login-card {
            background: var(--bg-white);
            width: 100%;
            max-width: 420px;
            padding: 48px 32px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-float);
            text-align: center;
        }
        .login-header h2 {
            font-size: 1.75rem;
            margin-bottom: 8px;
            color: var(--primary-dark);
        }
        .login-header p {
            color: var(--text-muted);
            margin-bottom: 32px;
            font-size: 0.95rem;
        }
        .logo-box {
            background-color: rgba(33, 169, 160, 0.1);
            color: var(--primary-color);
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 24px;
        }
        .form-group {
            margin-bottom: 24px;
            text-align: left;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #CBD5E1;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition-fast);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(33, 169, 160, 0.2);
        }
        .alert-error {
            background-color: #FEF2F2;
            color: #DC2626;
            padding: 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 0.875rem;
            border: 1px solid #FCA5A5;
        }
        .back-link {
            display: block;
            margin-top: 24px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .back-link:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body class="login-body">

    <div class="login-card">
        <div class="logo-box">
            <i class="fa-solid fa-lock"></i>
        </div>
        <div class="login-header">
            <h2>Masuk Pegawai</h2>
            <p>Masukkan NIY dan Kata Sandi SSO Anda</p>
        </div>

        <?php if($error) : ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group">
                <label for="niy" class="form-label">Nomor Induk Yayasan (NIY) / Username</label>
                <div style="position: relative;">
                    <i class="fa-regular fa-id-badge" style="position: absolute; left: 14px; top: 14px; color: var(--text-muted);"></i>
                    <input type="text" id="niy" name="niy" class="form-control" placeholder="Contoh: admin" required style="padding-left: 40px;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-key" style="position: absolute; left: 14px; top: 14px; color: var(--text-muted);"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required style="padding-left: 40px;">
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-primary" style="width: 100%; padding: 14px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>

        <a href="index.php" class="back-link">
            <i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Kembali ke Beranda
        </a>
    </div>

</body>
</html>
