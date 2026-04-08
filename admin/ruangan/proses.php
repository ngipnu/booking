<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama_ruangan = $koneksi->real_escape_string($_POST['nama_ruangan'] ?? '');
    $id_gedung = $koneksi->real_escape_string($_POST['id_gedung'] ?? '');
    
    // Auto-generate kode ruangan jika kosong
    $input_kode = trim($_POST['kode_ruangan'] ?? '');
    if ($input_kode === '') {
        $q_count = $koneksi->query("SELECT COUNT(*) AS total FROM ruangan WHERE id_gedung = '$id_gedung'");
        $row_count = $q_count ? $q_count->fetch_assoc() : null;
        $urutan = ($row_count ? $row_count['total'] : 0) + 1;
        $kode_ruangan = "R-" . $id_gedung . "-" . str_pad($urutan, 3, "0", STR_PAD_LEFT);
    } else {
        $kode_ruangan = $koneksi->real_escape_string($input_kode);
    }

    $kapasitas_raw = $_POST['kapasitas'] ?? '';
    $kapasitas = $kapasitas_raw === '' ? 0 : intval($kapasitas_raw);
    $bisa_dipinjam = $koneksi->real_escape_string($_POST['bisa_dipinjam'] ?? 'Y');
    $fasilitas = $koneksi->real_escape_string($_POST['fasilitas'] ?? '');
    $penanggung_jawab = $koneksi->real_escape_string($_POST['penanggung_jawab'] ?? '');
    $kontak_pj = $koneksi->real_escape_string($_POST['kontak_pj'] ?? '');

    $query = "INSERT INTO ruangan (nama_ruangan, kode_ruangan, id_gedung, kapasitas, bisa_dipinjam, fasilitas, penanggung_jawab, kontak_pj) 
              VALUES ('$nama_ruangan', '$kode_ruangan', '$id_gedung', '$kapasitas', '$bisa_dipinjam', '$fasilitas', '$penanggung_jawab', '$kontak_pj')";
    if ($koneksi->query($query)) {
        $_SESSION['pesan'] = "Ruangan baru berhasil ditambahkan.";
    } else {
        $_SESSION['pesan'] = "Gagal menambah ruangan: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
} 

elseif ($aksi == 'edit') {
    $id = $_POST['id'];
    $nama_ruangan = $koneksi->real_escape_string($_POST['nama_ruangan'] ?? '');
    $id_gedung = $koneksi->real_escape_string($_POST['id_gedung'] ?? '');

    // Auto-generate kode ruangan jika kosong saat edit 
    $input_kode = trim($_POST['kode_ruangan'] ?? '');
    if ($input_kode === '') {
        $q_count = $koneksi->query("SELECT COUNT(*) AS total FROM ruangan WHERE id_gedung = '$id_gedung'");
        $row_count = $q_count ? $q_count->fetch_assoc() : null;
        $urutan = ($row_count ? $row_count['total'] : 0) + 1;
        $kode_ruangan = "R-" . $id_gedung . "-" . str_pad($urutan, 3, "0", STR_PAD_LEFT);
    } else {
        $kode_ruangan = $koneksi->real_escape_string($input_kode);
    }

    $kapasitas_raw = $_POST['kapasitas'] ?? '';
    $kapasitas = $kapasitas_raw === '' ? 0 : intval($kapasitas_raw);
    $bisa_dipinjam = $koneksi->real_escape_string($_POST['bisa_dipinjam'] ?? 'Y');
    $fasilitas = $koneksi->real_escape_string($_POST['fasilitas'] ?? '');
    $penanggung_jawab = $koneksi->real_escape_string($_POST['penanggung_jawab'] ?? '');
    $kontak_pj = $koneksi->real_escape_string($_POST['kontak_pj'] ?? '');

    $query = "UPDATE ruangan SET 
              nama_ruangan = '$nama_ruangan', 
              kode_ruangan = '$kode_ruangan', 
              id_gedung = '$id_gedung', 
              kapasitas = '$kapasitas', 
              bisa_dipinjam = '$bisa_dipinjam', 
              fasilitas = '$fasilitas',
              penanggung_jawab = '$penanggung_jawab',
              kontak_pj = '$kontak_pj'
              WHERE id = '$id'";
    if ($koneksi->query($query)) {
        $_SESSION['pesan'] = "Data ruangan berhasil diperbarui.";
    } else {
        $_SESSION['pesan'] = "Gagal memperbarui ruangan: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
} 

elseif ($aksi == 'hapus') {
    $id = $_GET['id'];
    $query = "DELETE FROM ruangan WHERE id = '$id'";
    if ($koneksi->query($query)) {
        $_SESSION['pesan'] = "Ruangan berhasil dihapus.";
    } else {
        $_SESSION['pesan'] = "Gagal menghapus ruangan: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
}
