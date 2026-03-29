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

        // Notifikasi in-app ke semua admin
        $admins = $koneksi->query("SELECT id FROM users WHERE role = 'admin'");
        $admin_ids = [];
        while ($a = $admins->fetch_assoc()) $admin_ids[] = $a['id'];

        if ($id_aset !== 'NULL') {
            $item_info = $koneksi->query("SELECT nama_aset FROM aset WHERE id = $id_aset")->fetch_assoc();
            $item_name = $item_info['nama_aset'];
            $notif_link = '../../admin/peminjaman/index.php';
        } else {
            $item_info = $koneksi->query("SELECT nama_ruangan FROM ruangan WHERE id = $id_ruangan")->fetch_assoc();
            $item_name = $item_info['nama_ruangan'];
            $notif_link = '../../admin/peminjaman/index.php';
        }
        kirimNotifikasi($koneksi, $admin_ids,
            "📋 Pengajuan Baru: $item_name",
            "{$_SESSION['nama_pemakai']} ({$_SESSION['unit_pemakai']}) mengajukan peminjaman " .
            "\"$item_name\" pada " . date('d M Y', strtotime($tgl_pinjam)) . " pukul " . 
            substr($jam_mulai,0,5) . "–" . substr($jam_selesai,0,5) . ".",
            'warning',
            $notif_link
        );

        try {
            require_once '../config/mailer.php';
            
            $profil = $koneksi->query("SELECT email_pengelola_inventaris, email_pengelola_ruangan, email_admin, nama_lembaga FROM profil_lembaga LIMIT 1")->fetch_assoc();
            
            $to_email  = '';
            $item_name = '';
            $tipe_label = '';

            if ($id_aset !== 'NULL') {
                $to_email   = $profil['email_pengelola_inventaris'] ?: $profil['email_admin'];
                $aset_info  = $koneksi->query("SELECT nama_aset, kode_aset FROM aset WHERE id = $id_aset")->fetch_assoc();
                $item_name  = $aset_info['nama_aset'] . ' (' . $aset_info['kode_aset'] . ')';
                $tipe_label = '📦 Barang / Inventaris';
                $subject    = "[Sarpras] Pengajuan Pinjam Barang: {$aset_info['nama_aset']}";
            } else {
                $to_email   = $profil['email_pengelola_ruangan'] ?: $profil['email_admin'];
                $ruang_info = $koneksi->query("SELECT nama_ruangan, kode_ruangan FROM ruangan WHERE id = $id_ruangan")->fetch_assoc();
                $item_name  = $ruang_info['nama_ruangan'] . ' (' . $ruang_info['kode_ruangan'] . ')';
                $tipe_label = '🏫 Ruangan';
                $subject    = "[Sarpras] Booking Ruangan: {$ruang_info['nama_ruangan']}";
            }

            if (!empty($to_email)) {
                $tgl_fmt    = date('l, d F Y', strtotime($tgl_pinjam));
                $lembaga    = htmlspecialchars($profil['nama_lembaga']);
                $nama_html  = htmlspecialchars($_SESSION['nama_pemakai']);
                $unit_html  = htmlspecialchars($_SESSION['unit_pemakai']);
                $item_html  = htmlspecialchars($item_name);
                $need_html  = nl2br(htmlspecialchars($keperluan));

                $body = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;'>
                    <div style='background:linear-gradient(135deg,#1e3a8a,#3b82f6);padding:30px 24px;text-align:center;'>
                        <h2 style='color:#fff;margin:0;font-size:1.4rem;'>📋 Pengajuan Peminjaman Baru</h2>
                        <p style='color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:0.9rem;'>$lembaga — Sistem Sarpras</p>
                    </div>
                    <div style='padding:28px 24px;background:#fff;'>
                        <table style='width:100%;border-collapse:collapse;'>
                            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;width:40%;font-size:0.85rem;'>Jenis</td>
                                <td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:700;'>$tipe_label</td></tr>
                            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:0.85rem;'>Item</td>
                                <td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:700;color:#1e3a8a;'>$item_html</td></tr>
                            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:0.85rem;'>Peminjam</td>
                                <td style='padding:10px 0;border-bottom:1px solid #f1f5f9;'>$nama_html</td></tr>
                            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:0.85rem;'>Unit / Divisi</td>
                                <td style='padding:10px 0;border-bottom:1px solid #f1f5f9;'>$unit_html</td></tr>
                            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:0.85rem;'>Tanggal</td>
                                <td style='padding:10px 0;border-bottom:1px solid #f1f5f9;'>$tgl_fmt</td></tr>
                            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:0.85rem;'>Waktu</td>
                                <td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:700;'>" . substr($jam_mulai,0,5) . " – " . substr($jam_selesai,0,5) . "</td></tr>
                            <tr><td style='padding:10px 0;color:#64748b;font-size:0.85rem;vertical-align:top;'>Keperluan</td>
                                <td style='padding:10px 0;'>$need_html</td></tr>
                        </table>
                        <div style='margin-top:28px;text-align:center;'>
                            <a href='http://localhost/booking/booking/admin/' style='background:#1e3a8a;color:#fff;padding:12px 30px;border-radius:50px;text-decoration:none;font-weight:700;font-size:0.95rem;'>
                                Proses di Panel Admin →
                            </a>
                        </div>
                    </div>
                    <div style='background:#f8fafc;padding:16px 24px;text-align:center;font-size:0.75rem;color:#94a3b8;'>
                        Email otomatis dari Sistem Sarpras $lembaga. Jangan balas email ini.
                    </div>
                </div>";

                kirimEmail($to_email, $subject, $body);
            }
        } catch (\Throwable $e) {
            error_log("Email error: " . $e->getMessage());
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
