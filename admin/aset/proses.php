<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    
    // Pembersihan input
    $nama_aset = $koneksi->real_escape_string($_POST['nama_aset']);
    $merk = $koneksi->real_escape_string($_POST['merk']);
    $warna = $koneksi->real_escape_string($_POST['warna']);
    $id_kategori = (int) $_POST['id_kategori'];
    $unit_pengguna = $koneksi->real_escape_string($_POST['unit_pengguna']);
    $lokasi_simpan = $koneksi->real_escape_string($_POST['lokasi_simpan']);
    $divisi_pembeli = isset($_POST['divisi_pembeli']) ? $koneksi->real_escape_string($_POST['divisi_pembeli']) : '';
    $toko_pembelian = $koneksi->real_escape_string($_POST['toko_pembelian']);
    $kota_pembelian = $koneksi->real_escape_string($_POST['kota_pembelian']);
    $harga_beli = (float) $_POST['harga_beli'];
    $kondisi = isset($_POST['kondisi']) ? $koneksi->real_escape_string($_POST['kondisi']) : 'baik';
    $bisa_dipinjam = isset($_POST['bisa_dipinjam']) ? $koneksi->real_escape_string($_POST['bisa_dipinjam']) : 'Y';
    $ada_garansi = isset($_POST['ada_garansi']) ? $koneksi->real_escape_string($_POST['ada_garansi']) : 'N';
    $garansi_sampai = !empty($_POST['garansi_sampai']) ? $_POST['garansi_sampai'] : NULL;
    $tahun_anggaran = $koneksi->real_escape_string($_POST['tahun_anggaran']);
    $tgl_beli = !empty($_POST['tgl_beli']) ? $koneksi->real_escape_string($_POST['tgl_beli']) : date('Y-m-d');
    $penanggung_jawab = $koneksi->real_escape_string($_POST['penanggung_jawab']);

    if ($_POST['aksi'] == 'tambah') {
        $kode_aset = $koneksi->real_escape_string($_POST['kode_aset']);
        
        $sql = "INSERT INTO aset (kode_aset, nama_aset, merk, warna, id_kategori, unit_pengguna, lokasi_simpan, divisi_pembeli, tahun_anggaran, toko_pembelian, kota_pembelian, harga_beli, tgl_beli, ada_garansi, garansi_sampai, kondisi, bisa_dipinjam, penanggung_jawab) 
                VALUES ('$kode_aset', '$nama_aset', '$merk', '$warna', $id_kategori, '$unit_pengguna', '$lokasi_simpan', '$divisi_pembeli', '$tahun_anggaran', '$toko_pembelian', '$kota_pembelian', $harga_beli, '$tgl_beli', '$ada_garansi', ".($garansi_sampai?"'$garansi_sampai'":"NULL").", '$kondisi', '$bisa_dipinjam', '$penanggung_jawab')";
        
        if ($koneksi->query($sql)) {
            $_SESSION['pesan'] = "Aset '$nama_aset' berhasil diregistrasi.";
        }
    } 
    
    elseif ($_POST['aksi'] == 'edit') {
        $id = (int) $_POST['id'];
        
        $sql = "UPDATE aset SET 
                nama_aset='$nama_aset', 
                merk='$merk', 
                warna='$warna', 
                id_kategori=$id_kategori, 
                unit_pengguna='$unit_pengguna', 
                lokasi_simpan='$lokasi_simpan', 
                divisi_pembeli='$divisi_pembeli',
                tahun_anggaran='$tahun_anggaran',
                toko_pembelian='$toko_pembelian', 
                kota_pembelian='$kota_pembelian', 
                harga_beli=$harga_beli, 
                tgl_beli='$tgl_beli',
                ada_garansi='$ada_garansi',
                garansi_sampai=".($garansi_sampai?"'$garansi_sampai'":"NULL").",
                kondisi='$kondisi', 
                bisa_dipinjam='$bisa_dipinjam',
                penanggung_jawab='$penanggung_jawab' 
                WHERE id=$id";
        
        if ($koneksi->query($sql)) {
            $_SESSION['pesan'] = "Data inventaris berhasil diperbarui.";
        } else {
            $_SESSION['pesan_error'] = "Gagal memperbarui data: " . $koneksi->error;
        }
    }

    elseif ($_POST['aksi'] == 'import') {
        if (isset($_FILES['file_aset']) && $_FILES['file_aset']['error'] == 0) {
            $filename = $_FILES['file_aset']['tmp_name'];
            $handle = fopen($filename, "r");
            $success = 0;
            $row_count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row_count++;
                if ($row_count == 1) continue; // Skip header

                $kode = $koneksi->real_escape_string($data[0]);
                $nama = $koneksi->real_escape_string($data[1]);
                $merk = $koneksi->real_escape_string($data[2]);
                $warna = $koneksi->real_escape_string($data[3]);
                $unit = $koneksi->real_escape_string($data[4]);
                $lokasi = $koneksi->real_escape_string($data[5]);
                $harga = (float)$data[6];
                $tahun = $koneksi->real_escape_string($data[7]);
                $tgl_skrg = date('Y-m-d');

                $sql = "INSERT INTO aset (kode_aset, nama_aset, merk, warna, unit_pengguna, lokasi_simpan, harga_beli, tahun_anggaran, tgl_beli, kondisi, status) 
                        VALUES ('$kode', '$nama', '$merk', '$warna', '$unit', '$lokasi', $harga, '$tahun', '$tgl_skrg', 'baik', 'tersedia') 
                        ON DUPLICATE KEY UPDATE nama_aset='$nama', merk='$merk', warna='$warna', harga_beli=$harga, tahun_anggaran='$tahun'";
                
                if ($koneksi->query($sql)) $success++;
            }
            fclose($handle);
            $_SESSION['pesan'] = "Berhasil mengimport $success data aset.";
        } else {
            $_SESSION['pesan_error'] = "Gagal mengunggah file.";
        }
    }
    
    header("Location: index.php");
    exit;
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($koneksi->query("DELETE FROM aset WHERE id=$id")) {
        $_SESSION['pesan'] = "Data aset telah berhasil dihapus.";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus data: " . $koneksi->error;
    }
    header("Location: index.php");
    exit;
}
?>
