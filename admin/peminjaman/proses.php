<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_GET['aksi'] == 'setujui') {
    $id = (int)$_GET['id'];
    
    $data_p = $koneksi->query("SELECT p.*, a.nama_aset, r.nama_ruangan 
                                FROM peminjaman p
                                LEFT JOIN aset a ON p.id_aset = a.id
                                LEFT JOIN ruangan r ON p.id_ruangan = r.id
                                WHERE p.id = $id")->fetch_assoc();
    $id_aset    = $data_p['id_aset'];
    $id_ruangan = $data_p['id_ruangan'];
    $item_name  = $data_p['nama_aset'] ?: $data_p['nama_ruangan'];
    $tgl_fmt    = date('d M Y', strtotime($data_p['tgl_pinjam']));
    $jam        = substr($data_p['jam_mulai'],0,5) . '–' . substr($data_p['jam_selesai'],0,5);

    $koneksi->query("UPDATE peminjaman SET status_pinjam = 'disetujui', waktu_disetujui = NOW() WHERE id = $id");
    
    if ($id_aset) {
        $koneksi->query("UPDATE aset SET status = 'dipinjam' WHERE id = $id_aset");
    } elseif ($id_ruangan) {
        $koneksi->query("UPDATE ruangan SET status = 'dipakai' WHERE id = $id_ruangan");
    }

    // Notifikasi ke peminjam
    kirimNotifikasi($koneksi, $data_p['id_user'],
        "✅ Disetujui: $item_name",
        "Pengajuan Anda untuk \"$item_name\" pada $tgl_fmt pukul $jam telah DISETUJUI oleh admin. Silakan ambil barang/ruangan sesuai jadwal.",
        'success',
        'peminjaman.php'
    );

    $_SESSION['pesan'] = "Peminjaman berhasil disetujui.";
}

elseif ($_GET['aksi'] == 'tolak') {
    $id = (int)$_GET['id'];

    $data_p = $koneksi->query("SELECT p.*, a.nama_aset, r.nama_ruangan 
                                FROM peminjaman p
                                LEFT JOIN aset a ON p.id_aset = a.id
                                LEFT JOIN ruangan r ON p.id_ruangan = r.id
                                WHERE p.id = $id")->fetch_assoc();
    $item_name = $data_p['nama_aset'] ?: $data_p['nama_ruangan'];
    $tgl_fmt   = date('d M Y', strtotime($data_p['tgl_pinjam']));

    $koneksi->query("UPDATE peminjaman SET status_pinjam = 'ditolak' WHERE id = $id");

    // Notifikasi ke peminjam
    kirimNotifikasi($koneksi, $data_p['id_user'],
        "❌ Ditolak: $item_name",
        "Maaf, pengajuan Anda untuk \"$item_name\" pada $tgl_fmt tidak dapat disetujui. Hubungi admin untuk informasi lebih lanjut.",
        'danger',
        'peminjaman.php'
    );

    $_SESSION['pesan'] = "Peminjaman ditolak.";
}

elseif ($_GET['aksi'] == 'kembali') {
    $id = (int)$_GET['id'];
    
    $data_p = $koneksi->query("SELECT p.*, a.nama_aset, r.nama_ruangan 
                                FROM peminjaman p
                                LEFT JOIN aset a ON p.id_aset = a.id
                                LEFT JOIN ruangan r ON p.id_ruangan = r.id
                                WHERE p.id = $id")->fetch_assoc();
    $id_aset    = $data_p['id_aset'];
    $id_ruangan = $data_p['id_ruangan'];
    $item_name  = $data_p['nama_aset'] ?: $data_p['nama_ruangan'];

    $koneksi->query("UPDATE peminjaman SET status_pinjam = 'selesai' WHERE id = $id");
    
    if ($id_aset) {
        $koneksi->query("UPDATE aset SET status = 'tersedia' WHERE id = $id_aset");
    } elseif ($id_ruangan) {
        $koneksi->query("UPDATE ruangan SET status = 'tersedia' WHERE id = $id_ruangan");
    }

    // Notifikasi ke peminjam
    kirimNotifikasi($koneksi, $data_p['id_user'],
        "🔄 Selesai: $item_name",
        "Peminjaman \"$item_name\" Anda telah dicatat selesai oleh admin. Terima kasih!",
        'info',
        'peminjaman.php'
    );

    $_SESSION['pesan'] = "Peminjaman telah selesai dan status diperbarui.";
}

header("Location: index.php");
exit;
?>
