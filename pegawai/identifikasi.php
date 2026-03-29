<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $unit = trim($_POST['unit']);
    
    if (!empty($nama) && !empty($unit)) {
        $_SESSION['nama_pemakai'] = $nama;
        $_SESSION['unit_pemakai'] = $unit;
        header("Location: dashboard.php");
        exit;
    }
}

// Ambil warna tema
$profil_tema = $koneksi->query("SELECT sidebar_gradient FROM profil_lembaga LIMIT 1")->fetch_assoc();
$grad = $profil_tema['sidebar_gradient'];
preg_match('/(#[a-f0-9]{3,6}|rgba?\([^)]+\))/i', $grad, $matches);
$primary_color = isset($matches[0]) ? $matches[0] : '#3b82f6';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identifikasi Pengguna | An Nadzir</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/logo/logo_round.png?v=2">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: <?= $grad ?>;
            --primary-color: <?= $primary_color ?>;
        }
        body {
            background: #f8fafc;
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ident-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(0,0,0,0.05);
            text-align: center;
        }
        .logo-box {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1.5px solid #eee;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px <?= $primary_color ?>20;
        }
        .btn-start {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            width: 100%;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px <?= $primary_color ?>30;
            color: white;
        }
    </style>
</head>
<body>

    <div class="ident-card">
        <div class="logo-box">
            <img src="../assets/logo/logo_round.png" alt="Logo" width="60">
        </div>
        <h4 class="fw-bold mb-1">Siapa Anda?</h4>
        <p class="text-muted small mb-4">Karena akun ini digunakan bersama, mohon isi nama untuk mulai.</p>
        
        <form action="" method="POST">
            <div class="text-start">
                <label class="small fw-bold mb-1 ms-1">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Contoh: Ibnu / Hani" value="<?= htmlspecialchars($_SESSION['nama_pemakai'] ?? '') ?>" required autofocus>
                
                <label class="small fw-bold mb-1 ms-1">Divisi / Unit</label>
                <input type="text" name="unit" class="form-control" list="list_unit" placeholder="Unit kerja" value="<?= htmlspecialchars($_SESSION['unit_pemakai'] ?? '') ?>" required>
                <datalist id="list_unit">
                    <option value="LRC SDIT">
                    <option value="SMPIT An Nadzir">
                    <option value="TKIT An Nadzir">
                    <option value="Yayasan / Pusat">
                </datalist>
            </div>
            
            <button type="submit" class="btn btn-start mt-2">
                Simpan & Lanjutkan <i class="fa-solid fa-arrow-right ms-1"></i>
            </button>
        </form>
    </div>

</body>
</html>
