<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama_waktu = $koneksi->real_escape_string($_POST['nama_waktu']);
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $urutan = (int)$_POST['urutan'];

    $sql = "INSERT INTO waktu (nama_waktu, jam_mulai, jam_selesai, urutan) VALUES ('$nama_waktu', '$jam_mulai', '$jam_selesai', $urutan)";
    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Sesi waktu berhasil ditambahkan.";
    } else {
        $_SESSION['pesan'] = "Gagal menambahkan sesi waktu.";
    }
    header("Location: index.php");
    exit;
}

if ($aksi == 'edit') {
    $id = (int)$_POST['id'];
    $nama_waktu = $koneksi->real_escape_string($_POST['nama_waktu']);
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $urutan = (int)$_POST['urutan'];

    $sql = "UPDATE waktu SET nama_waktu='$nama_waktu', jam_mulai='$jam_mulai', jam_selesai='$jam_selesai', urutan=$urutan WHERE id=$id";
    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Sesi waktu berhasil diperbarui.";
    } else {
        $_SESSION['pesan'] = "Gagal memperbarui sesi waktu.";
    }
    header("Location: index.php");
    exit;
}

if ($aksi == 'hapus') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM waktu WHERE id=$id";
    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Sesi waktu berhasil dihapus.";
    } else {
        $_SESSION['pesan'] = "Gagal menghapus sesi waktu.";
    }
    header("Location: index.php");
    exit;
}

header("Location: index.php");
?>
