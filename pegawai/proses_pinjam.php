<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_SESSION['user_id'];
    $id_aset = !empty($_POST['id_aset']) ? $_POST['id_aset'] : 'NULL';
    $id_ruangan = !empty($_POST['id_ruangan']) ? $_POST['id_ruangan'] : 'NULL';
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $nama_peminjam = $koneksi->real_escape_string($_SESSION['nama_pemakai']);
    $unit_peminjam = $koneksi->real_escape_string($_SESSION['unit_pemakai']);
    $keperluan = $koneksi->real_escape_string($_POST['keperluan']);

    // Cek tabrakan jadwal (Overlap check)
    $where_overlap = "";
    if ($id_aset !== 'NULL') {
        $where_overlap = "id_aset = $id_aset";
    } else {
        $where_overlap = "id_ruangan = $id_ruangan";
    }

    $check = $koneksi->query("SELECT id FROM peminjaman 
                              WHERE $where_overlap 
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

    $sql = "INSERT INTO peminjaman (id_user, id_aset, id_ruangan, tgl_pinjam, tgl_kembali, jam_mulai, jam_selesai, nama_peminjam, unit_peminjam, keperluan, status_pinjam) 
            VALUES ($id_user, $id_aset, $id_ruangan, '$tgl_pinjam', '$tgl_pinjam', '$jam_mulai', '$jam_selesai', '$nama_peminjam', '$unit_peminjam', '$keperluan', 'menunggu')";

    if ($koneksi->query($sql)) {
        $_SESSION['pesan_sukses'] = "Peminjaman berhasil diajukan! Menunggu persetujuan admin.";

        // --- Fitur Notifikasi Email ---
        // Ambil email pengelola dari profil
        $profil = $koneksi->query("SELECT email_pengelola_inventaris, email_pengelola_ruangan, nama_lembaga FROM profil_lembaga LIMIT 1")->fetch_assoc();
        
        $to = "";
        $item_name = "";
        
        if ($id_aset !== 'NULL') {
            $to = $profil['email_pengelola_inventaris'];
            $aset_info = $koneksi->query("SELECT nama_aset FROM aset WHERE id = $id_aset")->fetch_assoc();
            $item_name = $aset_info['nama_aset'];
            $subject = "Pengajuan Pinjam Barang Baru: $item_name";
        } else {
            $to = $profil['email_pengelola_ruangan'];
            $ruang_info = $koneksi->query("SELECT nama_ruangan FROM ruangan WHERE id = $id_ruangan")->fetch_assoc();
            $item_name = $ruang_info['nama_ruangan'];
            $subject = "Booking Ruangan Baru: $item_name";
        }

        if (!empty($to)) {
            $message = "Halo Pengelola,\n\n";
            $message .= "Ada pengajuan baru di sistem booking " . $profil['nama_lembaga'] . ":\n\n";
            $message .= "Item: " . $item_name . "\n";
            $message .= "Peminjam: " . $_SESSION['nama_pemakai'] . " (" . $_SESSION['unit_pemakai'] . ")\n";
            $message .= "Tanggal: " . date('d-m-Y', strtotime($tgl_pinjam)) . "\n";
            $message .= "Waktu: " . $jam_mulai . " s/d " . $jam_selesai . "\n";
            $message .= "Keperluan: " . $keperluan . "\n\n";
            $message .= "Silakan login ke panel admin untuk memproses pengajuan ini.\n";
            $message .= "Terima kasih.";

            $headers = "From: no-reply@annadzir.sch.id";
            
            // Kirim email (Menggunakan fungsi mail bawaan PHP)
            @mail($to, $subject, $message, $headers);
        }
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
