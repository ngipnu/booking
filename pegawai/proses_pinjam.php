<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_SESSION['user_id'];
    $id_aset = $_POST['id_aset'];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $nama_peminjam = $koneksi->real_escape_string($_POST['nama_peminjam']);
    $unit_peminjam = $koneksi->real_escape_string($_POST['unit_peminjam']);
    $keperluan = $koneksi->real_escape_string($_POST['keperluan']);

    // Cek tabrakan jadwal (Overlap check)
    $check = $koneksi->query("SELECT id FROM peminjaman 
                              WHERE id_aset = $id_aset 
                              AND tgl_pinjam = '$tgl_pinjam' 
                              AND status_pinjam IN ('menunggu', 'disetujui')
                              AND (
                                (jam_mulai < '$jam_selesai' AND jam_selesai > '$jam_mulai')
                              )");

    if ($check->num_rows > 0) {
        $_SESSION['pesan_error'] = "Gagal: Jadwal tersebut sudah terisi oleh peminjaman lain.";
        header("Location: dashboard.php?date=$tgl_pinjam");
        exit;
    }

    $sql = "INSERT INTO peminjaman (id_user, id_aset, tgl_pinjam, tgl_kembali, jam_mulai, jam_selesai, nama_peminjam, unit_peminjam, keperluan, status_pinjam) 
            VALUES ($id_user, $id_aset, '$tgl_pinjam', '$tgl_pinjam', '$jam_mulai', '$jam_selesai', '$nama_peminjam', '$unit_peminjam', '$keperluan', 'menunggu')";

    if ($koneksi->query($sql)) {
        $_SESSION['pesan_sukses'] = "Peminjaman berhasil diajukan! Menunggu persetujuan admin.";
    } else {
        $_SESSION['pesan_error'] = "Terjadi kesalahan sistem: " . $koneksi->error;
    }

    header("Location: dashboard.php?date=$tgl_pinjam");
    exit;
}

// Batal Ajuan
if (isset($_GET['aksi']) && $_GET['aksi'] == 'batal' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $nama_pemakai = $koneksi->real_escape_string($_SESSION['nama_pemakai']);
    $unit_pemakai = $koneksi->real_escape_string($_SESSION['unit_pemakai']);
    
    // Pastikan milik sendiri (berdasarkan session identitas) dan masih dalam status 'menunggu' atau 'disetujui'
    $sql = "DELETE FROM peminjaman WHERE id = $id 
            AND id_user = $user_id 
            AND nama_peminjam = '$nama_pemakai' 
            AND unit_peminjam = '$unit_pemakai' 
            AND status_pinjam IN ('menunggu', 'disetujui')";
            
    if ($koneksi->query($sql)) {
        if ($koneksi->affected_rows > 0) {
            $_SESSION['pesan_sukses'] = "Ajuan peminjaman berhasil dibatalkan.";
        } else {
            $_SESSION['pesan_error'] = "Gagal membatalkan ajuan: Anda tidak memiliki akses untuk menghapus ajuan ini.";
        }
    } else {
        $_SESSION['pesan_error'] = "Terjadi kesalahan sistem.";
    }
    
    header("Location: dashboard.php");
    exit;
}
?>
