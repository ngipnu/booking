<?php
session_start();
require_once '../config/database.php';

// Cek apakah sudah login & rolenya admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data fasilitas ringkasan
$total_fasilitas = $koneksi->query("SELECT id FROM fasilitas")->num_rows;
$fasilitas_tersedia = $koneksi->query("SELECT id FROM fasilitas WHERE status = 'tersedia'")->num_rows;
$fasilitas_dipinjam = $koneksi->query("SELECT id FROM fasilitas WHERE status = 'dipinjam'")->num_rows;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Booking Fasilitas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background-color: var(--primary-dark);
            color: var(--text-light);
            padding: 32px 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }
        .sidebar-brand {
            padding: 0 24px 32px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.25rem;
        }
        .sidebar-brand i { color: var(--accent-color); font-size: 1.5rem; }
        .nav-sidebar { list-style: none; padding: 0; margin: 0; }
        .nav-item { margin-bottom: 8px; padding: 0 16px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.7);
            border-radius: var(--radius-sm);
            font-weight: 500;
            transition: var(--transition-fast);
        }
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: var(--white);
        }
        .nav-link i { width: 20px; text-align: center; }
        .main-content {
            flex: 1;
            margin-left: 280px;
            background-color: var(--bg-main);
            padding: 32px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--bg-white);
            padding: 16px 24px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 32px;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background-color: var(--primary-color);
            color: white; display: flex; align-items: center; justify-content: center;
        }
        .stat-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px;
        }
        .stat-card {
            background: var(--bg-white); padding: 24px; border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px;
        }
        .stat-icon {
            width: 64px; height: 64px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center; font-size: 2rem;
        }
        .stat-info h3 { font-size: 2rem; color: var(--text-dark); margin-bottom: 4px; }
        .stat-info p { color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-building-columns"></i>
                <div>
                    <div>Annadzir</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); font-weight: 400;">Admin Panel</div>
                </div>
            </div>
            <ul class="nav-sidebar">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fa-solid fa-chart-line"></i> Ringkasan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fa-solid fa-cubes"></i> Kelola Fasilitas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fa-solid fa-clipboard-list"></i> Peminjaman Baru
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fa-solid fa-users"></i> Kelola Pegawai
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="topbar">
                <h2 style="font-family: var(--font-heading); color: var(--primary-dark);">Dasbor Utama</h2>
                <div class="user-profile">
                    <div class="avatar"><i class="fa-solid fa-user-shield"></i></div>
                    <div>
                        <div style="line-height: 1.2;"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Administrator</div>
                    </div>
                    <a href="../logout.php" style="margin-left: 16px; color: #DC2626;" title="Keluar">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(33, 169, 160, 0.1); color: var(--primary-color);">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $total_fasilitas ?></h3>
                        <p>Total Fasilitas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $fasilitas_tersedia ?></h3>
                        <p>Tersedia</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="fa-solid fa-hand-holding-hand"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $fasilitas_dipinjam ?></h3>
                        <p>Sedang Dipinjam</p>
                    </div>
                </div>
            </div>

            <!-- Welcome Banner -->
            <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%); border-radius: var(--radius-md); padding: 40px; color: white; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-md);">
                <div>
                    <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 12px; color: white;">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h2>
                    <p style="font-size: 1.125rem; opacity: 0.9; max-width: 600px;">Kelola seluruh fasilitas sekolah, periksa permintaan peminjaman yang masuk, dan jaga ketersediaan fasilitas melalui panel ini dengan mudah & aman.</p>
                </div>
                <div style="font-size: 8rem; opacity: 0.2;">
                    <i class="fa-solid fa-school"></i>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
