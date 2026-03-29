<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_GET['aksi'] == 'setujui') {
    $id = $_GET['id'];
    $id_aset = $_GET['id_aset'];
    
    // Update peminjaman
    $sql_p = "UPDATE peminjaman SET status_pinjam = 'disetujui', waktu_disetujui = NOW() WHERE id = $id";
    $koneksi->query($sql_p);
    
    // Update status aset
    $sql_a = "UPDATE aset SET status = 'dipinjam' WHERE id = $id_aset";
    $koneksi->query($sql_a);
    
    $_SESSION['pesan'] = "Peminjaman berhasil disetujui.";
}

elseif ($_GET['aksi'] == 'tolak') {
    $id = $_GET['id'];
    $sql = "UPDATE peminjaman SET status_pinjam = 'ditolak' WHERE id = $id";
    $koneksi->query($sql);
    $_SESSION['pesan'] = "Peminjaman ditolak.";
}

elseif ($_GET['aksi'] == 'kembali') {
    $id = $_GET['id'];
    $id_aset = $_GET['id_aset'];
    
    // Update peminjaman
    $sql_p = "UPDATE peminjaman SET status_pinjam = 'selesai' WHERE id = $id";
    $koneksi->query($sql_p);
    
    // Update status aset
    $sql_a = "UPDATE aset SET status = 'tersedia' WHERE id = $id_aset";
    $koneksi->query($sql_a);
    
    $_SESSION['pesan'] = "Aset telah berhasil dikembalikan.";
}

header("Location: index.php");
exit;
?>
