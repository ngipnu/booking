<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $koneksi->real_escape_string($_POST['nama_lembaga']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $sidebar_gradient = $koneksi->real_escape_string($_POST['sidebar_gradient']);
    $topbar_color = $koneksi->real_escape_string($_POST['topbar_color']);

    $sql = "UPDATE profil_lembaga SET 
            nama_lembaga = '$nama', 
            email_admin = '$email', 
            telepon = '$telepon', 
            alamat = '$alamat',
            sidebar_gradient = '$sidebar_gradient',
            topbar_color = '$topbar_color'
            LIMIT 1";

    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Profil lembaga berhasil diperbarui!";
    } else {
        $_SESSION['pesan'] = "Gagal memperbarui profil: " . $koneksi->error;
    }

    header("Location: index.php");
    exit;
}
