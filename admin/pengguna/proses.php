<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama     = $koneksi->real_escape_string($_POST['nama']);
    $username = $koneksi->real_escape_string($_POST['username']);
    $email    = $koneksi->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $sql = "INSERT INTO users (nama, username, email, password, role) VALUES ('$nama', '$username', '$email', '$password', '$role')";
    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Akun baru berhasil ditambahkan.";
    } else {
        $_SESSION['pesan_error'] = "Gagal menambahkan akun: " . $koneksi->error;
    }
}

elseif ($aksi == 'edit') {
    $id       = intval($_POST['id']);
    $nama     = $koneksi->real_escape_string($_POST['nama']);
    $username = $koneksi->real_escape_string($_POST['username']);
    $email    = $koneksi->real_escape_string($_POST['email']);
    $role     = $_POST['role'];

    $sql = "UPDATE users SET nama='$nama', username='$username', email='$email', role='$role' WHERE id=$id";

    // Update password jika diisi
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $koneksi->query("UPDATE users SET password='$password' WHERE id=$id");
    }

    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Data akun berhasil diperbarui.";
    } else {
        $_SESSION['pesan_error'] = "Gagal memperbarui akun: " . $koneksi->error;
    }
}

elseif ($aksi == 'hapus') {
    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        $_SESSION['pesan_error'] = "ID tidak valid.";
    } elseif ($id == intval($_SESSION['id_user'] ?? 0)) {
        $_SESSION['pesan_error'] = "Anda tidak bisa menghapus akun sendiri.";
    } else {
        if ($koneksi->query("DELETE FROM users WHERE id = $id")) {
            if ($koneksi->affected_rows > 0) {
                $_SESSION['pesan'] = "Akun berhasil dihapus.";
            } else {
                $_SESSION['pesan_error'] = "Akun tidak ditemukan.";
            }
        } else {
            $_SESSION['pesan_error'] = "Gagal menghapus: " . $koneksi->error;
        }
    }
}

header("Location: index.php");
exit;
?>
