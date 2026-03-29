<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_GET['aksi'] == 'setujui') {
    $id = $_GET['id'];
    
    // Ambil data peminjaman
    $data_p = $koneksi->query("SELECT id_aset, id_ruangan FROM peminjaman WHERE id = $id")->fetch_assoc();
    $id_aset = $data_p['id_aset'];
    $id_ruangan = $data_p['id_ruangan'];
    
    // Update peminjaman status
    $sql_p = "UPDATE peminjaman SET status_pinjam = 'disetujui', waktu_disetujui = NOW() WHERE id = $id";
    $koneksi->query($sql_p);
    
    if ($id_aset) {
        $koneksi->query("UPDATE aset SET status = 'dipinjam' WHERE id = $id_aset");
    } elseif ($id_ruangan) {
        $koneksi->query("UPDATE ruangan SET status = 'dipakai' WHERE id = $id_ruangan");
    }
    
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
    
    // Ambil data peminjaman
    $data_p = $koneksi->query("SELECT id_aset, id_ruangan FROM peminjaman WHERE id = $id")->fetch_assoc();
    $id_aset = $data_p['id_aset'];
    $id_ruangan = $data_p['id_ruangan'];
    
    // Update peminjaman status
    $sql_p = "UPDATE peminjaman SET status_pinjam = 'selesai' WHERE id = $id";
    $koneksi->query($sql_p);
    
    if ($id_aset) {
        $koneksi->query("UPDATE aset SET status = 'tersedia' WHERE id = $id_aset");
    } elseif ($id_ruangan) {
        $koneksi->query("UPDATE ruangan SET status = 'tersedia' WHERE id = $id_ruangan");
    }
    
    $_SESSION['pesan'] = "Peminjaman telah selesai dan status diperbarui.";
}

header("Location: index.php");
exit;
?>
