<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama_gedung = $koneksi->real_escape_string($_POST['nama_gedung']);
    $deskripsi = $koneksi->real_escape_string($_POST['deskripsi']);

    $query = "INSERT INTO gedung (nama_gedung, deskripsi) VALUES ('$nama_gedung', '$deskripsi')";
    if ($koneksi->query($query)) {
        $_SESSION['pesan'] = "Gedung baru berhasil ditambahkan.";
    } else {
        $_SESSION['pesan'] = "Gagal menambah gedung: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
} 

elseif ($aksi == 'edit') {
    $id = $_POST['id'];
    $nama_gedung = $koneksi->real_escape_string($_POST['nama_gedung']);
    $deskripsi = $koneksi->real_escape_string($_POST['deskripsi']);

    $query = "UPDATE gedung SET nama_gedung = '$nama_gedung', deskripsi = '$deskripsi' WHERE id = '$id'";
    if ($koneksi->query($query)) {
        $_SESSION['pesan'] = "Data gedung berhasil diperbarui.";
    } else {
        $_SESSION['pesan'] = "Gagal memperbarui gedung: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
} 

elseif ($aksi == 'hapus') {
    $id = $_GET['id'];
    $query = "DELETE FROM gedung WHERE id = '$id'";
    if ($koneksi->query($query)) {
        $_SESSION['pesan'] = "Gedung berhasil dihapus.";
    } else {
        $_SESSION['pesan'] = "Gagal menghapus gedung: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
}
