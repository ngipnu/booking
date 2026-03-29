<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $nama = $koneksi->real_escape_string($_POST['nama_kategori']);
    $icon = $koneksi->real_escape_string($_POST['icon']);

    if ($_POST['aksi'] == 'tambah') {
        $koneksi->query("INSERT INTO kategori (nama_kategori, icon) VALUES ('$nama', '$icon')");
        $_SESSION['pesan'] = "Kategori '$nama' berhasil ditambahkan.";
    } 
    elseif ($_POST['aksi'] == 'edit') {
        $id = (int) $_POST['id'];
        $koneksi->query("UPDATE kategori SET nama_kategori='$nama', icon='$icon' WHERE id=$id");
        $_SESSION['pesan'] = "Kategori diperbarui.";
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = (int) $_GET['id'];
    // Unset id_kategori in assets first
    $koneksi->query("UPDATE aset SET id_kategori = NULL WHERE id_kategori = $id");
    $koneksi->query("DELETE FROM kategori WHERE id = $id");
    $_SESSION['pesan'] = "Kategori dihapus.";
    header("Location: index.php");
    exit;
}
?>
