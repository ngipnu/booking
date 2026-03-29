<?php 
// admin/layouts/header.php
$profil_tema = $koneksi->query("SELECT sidebar_gradient, topbar_color FROM profil_lembaga LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Sarpras An Nadzir</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/logo/logo_round.png?v=2">
    <!-- Bootstrap 5 (Lokal) -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Lokal) -->
    <link rel="stylesheet" href="../../assets/vendor/fontawesome/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        :root {
            --sidebar-gradient: <?= $profil_tema['sidebar_gradient'] ?>;
            --topbar-text: <?= $profil_tema['topbar_color'] ?>;
        }
        .offcanvas-sidebar {
            background: var(--sidebar-gradient) !important;
        }
        .offcanvas-sidebar .sidebar-title {
            color: rgba(255,255,255,0.9) !important;
        }
        .offcanvas-sidebar .nav-link.sidebar-link {
            color: rgba(255,255,255,0.6) !important;
        }
        .offcanvas-sidebar .nav-link.sidebar-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
        }
        .offcanvas-sidebar .nav-link.sidebar-link.active {
            background: white !important;
            color: var(--primary-color) !important;
        }
        .topbar h5, .topbar .topbar-btn i, .topbar .profile-link span, .topbar .profile-link .text-dark {
            color: var(--topbar-text) !important;
        }
    </style>
</head>
<body class="bg-light" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=\"0 0 100 100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Ccircle cx=\"20\" cy=\"20\" r=\"50\" fill=\"%23e0f2fe\" filter=\"blur(30px)\" opacity=\"0.6\"/%3E%3Ccircle cx=\"80\" cy=\"80\" r=\"50\" fill=\"%23e0e7ff\" filter=\"blur(30px)\" opacity=\"0.6\"/%3E%3Ccircle cx=\"80\" cy=\"20\" r=\"50\" fill=\"%23fce7f3\" filter=\"blur(30px)\" opacity=\"0.4\"/%3E%3C/svg%3E'); background-size: cover; background-attachment: fixed;">
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <div class="app-wrapper">
