<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: Tes Pengiriman Email
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'test_email') {
        require_once '../../config/mailer.php';
        
        $target = trim($_POST['test_email_target'] ?? '');
        if (empty($target) || !filter_var($target, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['pesan_error'] = "Format alamat email tujuan tes tidak valid.";
            header("Location: index.php");
            exit;
        }

        $subject = "[Uji Coba] Tes Notifikasi Email Sistem Sarpras";
        $body = "
        <div style='font-family:Arial,sans-serif;max-width:550px;margin:auto;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;'>
            <div style='background:linear-gradient(135deg,#1e3a8a,#3b82f6);padding:24px;text-align:center;'>
                <h3 style='color:#fff;margin:0;'>✅ Uji Coba Email Berhasil</h3>
                <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:0.9rem;'>Sistem Notifikasi Sarpras An Nadzir</p>
            </div>
            <div style='padding:24px;background:#fff;'>
                <p style='margin:0 0 12px;color:#334155;'>Halo Administrator,</p>
                <p style='margin:0 0 16px;color:#334155;line-height:1.6;'>
                    Email ini adalah pesan uji coba untuk memverifikasi bahwa konfigurasi Brevo SMTP dan alamat penerima notifikasi sudah berfungsi dengan baik.
                </p>
                <div style='background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;font-size:0.85rem;color:#475569;'>
                    <strong>Waktu Uji Coba:</strong> " . date('d F Y, H:i:s') . " WIB<br>
                    <strong>Pengirim (MAIL_FROM):</strong> " . htmlspecialchars(MAIL_FROM) . "
                </div>
            </div>
        </div>";

        $err = '';
        if (kirimEmail($target, $subject, $body, $err)) {
            $_SESSION['pesan'] = "✅ Berhasil! Email uji coba telah dikirim ke <strong>" . htmlspecialchars($target) . "</strong>. Silakan cek kotak masuk (atau spam).";
        } else {
            $_SESSION['pesan_error'] = "❌ Gagal mengirim email uji coba: " . htmlspecialchars($err);
        }

        header("Location: index.php");
        exit;
    }

    $nama = $koneksi->real_escape_string($_POST['nama_lembaga']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $sidebar_gradient = $koneksi->real_escape_string($_POST['sidebar_gradient']);
    $topbar_color = $koneksi->real_escape_string($_POST['topbar_color']);
    $email_inv = $koneksi->real_escape_string($_POST['email_pengelola_inventaris']);
    $email_ruang = $koneksi->real_escape_string($_POST['email_pengelola_ruangan']);

    $sql = "UPDATE profil_lembaga SET 
            nama_lembaga = '$nama', 
            email_admin = '$email', 
            telepon = '$telepon', 
            alamat = '$alamat',
            sidebar_gradient = '$sidebar_gradient',
            topbar_color = '$topbar_color',
            email_pengelola_inventaris = '$email_inv',
            email_pengelola_ruangan = '$email_ruang'
            LIMIT 1";

    if ($koneksi->query($sql)) {
        $_SESSION['pesan'] = "Profil lembaga berhasil diperbarui!";
    } else {
        $_SESSION['pesan_error'] = "Gagal memperbarui profil: " . $koneksi->error;
    }

    header("Location: index.php");
    exit;
}
?>
