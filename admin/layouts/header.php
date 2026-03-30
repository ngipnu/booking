<?php 
// admin/layouts/header.php
$profil_tema = $koneksi->query("SELECT sidebar_gradient, topbar_color FROM profil_lembaga LIMIT 1")->fetch_assoc();

// Ekstrak warna utama dari gradient untuk aksen UI lainnya
$grad = $profil_tema['sidebar_gradient'];
preg_match('/(#[a-f0-9]{3,6}|rgba?\([^)]+\))/i', $grad, $matches);
$primary_color = isset($matches[0]) ? $matches[0] : '#3b82f6';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Sarana & Prasarana</title>
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
            --primary-color: <?= $primary_color ?>;
            --primary-gradient: <?= $profil_tema['sidebar_gradient'] ?>;
        }
        
        /* Tema Sidebar */
        .offcanvas-sidebar {
            background: var(--sidebar-gradient) !important;
        }
        
        /* Sidebar Branding */
        .offcanvas-sidebar a .text-dark, .offcanvas-sidebar a .fs-6 {
            color: white !important;
        }
        .offcanvas-sidebar a .text-muted {
            color: rgba(255,255,255,0.7) !important;
        }
        .offcanvas-sidebar a.border-bottom {
            border-color: rgba(255,255,255,0.1) !important;
        }

        /* Tema Topbar */
        .topbar h5, .topbar .topbar-btn i, .topbar span.text-dark, .topbar .profile-link span {
            color: var(--topbar-text) !important;
        }
        
        /* Tema Aksen Global */
        .btn-primary {
            background: var(--primary-gradient) !important;
            border: none !important;
        }
        .text-primary {
            color: var(--primary-color) !important;
        }
        .bg-primary-soft {
            background-color: <?= $primary_color ?>20 !important; /* Opacity 20% hex hack (simplistic) */
        }
        
        /* Hover Table & List */
        .table tbody tr:hover {
            background: rgba(0,0,0,0.01) !important;
            border-left: 3px solid var(--primary-color);
        }
        
        /* Floating Action Button */
        .fab-btn {
            background: var(--primary-gradient) !important;
        }

        /* Modal & Offcanvas Fix */
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal {
            z-index: 1060 !important;
        }
        .offcanvas-backdrop {
            z-index: 1070 !important;
        }
        .offcanvas {
            z-index: 1080 !important;
        }
        /* Ensure glass effect doesn't trap clicks */
        .glass-card {
            position: relative;
            z-index: 1;
        }
        .main-content {
            position: relative;
            z-index: 0;
        }
    </style>
    <!-- Bootstrap JS (Moved to Header for component availability) -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=\"0 0 100 100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Ccircle cx=\"20\" cy=\"20\" r=\"50\" fill=\"%23e0f2fe\" filter=\"blur(30px)\" opacity=\"0.6\"/%3E%3Ccircle cx=\"80\" cy=\"80\" r=\"50\" fill=\"%23e0e7ff\" filter=\"blur(30px)\" opacity=\"0.6\"/%3E%3Ccircle cx=\"80\" cy=\"20\" r=\"50\" fill=\"%23fce7f3\" filter=\"blur(30px)\" opacity=\"0.4\"/%3E%3C/svg%3E'); background-size: cover; background-attachment: fixed;">
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <div class="app-wrapper">
