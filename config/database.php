<?php
// config/database.php

$host = 'localhost';
$username = 'root'; 
$password = ''; 
$database = 'annadzir_booking'; 

$koneksi = new mysqli($host, $username, $password);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Cek & Buat Database
$sql_db = "CREATE DATABASE IF NOT EXISTS $database";
$koneksi->query($sql_db);
$koneksi->select_db($database);

// Tabel Users (Pegawai & Admin)
$koneksi->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    niy VARCHAR(20) NULL UNIQUE,
    username VARCHAR(50) NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'pegawai') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Migrasi User: Tambah username & email jika belum ada
$check_u_username = $koneksi->query("SHOW COLUMNS FROM users LIKE 'username'");
if ($check_u_username->num_rows == 0) {
    $koneksi->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) AFTER niy");
    $koneksi->query("UPDATE users SET username = niy"); // Jadikan niy sebagai username default
}
$check_u_email = $koneksi->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($check_u_email->num_rows == 0) {
    $koneksi->query("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER nama");
}
$koneksi->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'pegawai') DEFAULT 'user'");

// Tabel Profil Lembaga
$koneksi->query("CREATE TABLE IF NOT EXISTS profil_lembaga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lembaga VARCHAR(100) DEFAULT 'An Nadzir Islamic School',
    email_admin VARCHAR(100) DEFAULT 'admin@annadzir.sch.id',
    alamat TEXT,
    telepon VARCHAR(20),
    sidebar_gradient VARCHAR(255) DEFAULT 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
    topbar_color VARCHAR(20) DEFAULT '#334155',
    email_pengelola_inventaris VARCHAR(100) NULL,
    email_pengelola_ruangan VARCHAR(100) NULL
)");

// Migrasi Profil: Tambah kolom tema & email pengelola jika belum ada
$check_p_theme = $koneksi->query("SHOW COLUMNS FROM profil_lembaga LIKE 'sidebar_gradient'");
if ($check_p_theme->num_rows == 0) {
    $koneksi->query("ALTER TABLE profil_lembaga ADD COLUMN sidebar_gradient VARCHAR(255) DEFAULT 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)' AFTER telepon");
    $koneksi->query("ALTER TABLE profil_lembaga ADD COLUMN topbar_color VARCHAR(20) DEFAULT '#334155' AFTER sidebar_gradient");
}
$check_p_emails = $koneksi->query("SHOW COLUMNS FROM profil_lembaga LIKE 'email_pengelola_inventaris'");
if ($check_p_emails->num_rows == 0) {
    $koneksi->query("ALTER TABLE profil_lembaga ADD COLUMN email_pengelola_inventaris VARCHAR(100) NULL AFTER topbar_color");
    $koneksi->query("ALTER TABLE profil_lembaga ADD COLUMN email_pengelola_ruangan VARCHAR(100) NULL AFTER email_pengelola_inventaris");
}

$cek_profil = $koneksi->query("SELECT * FROM profil_lembaga");
if ($cek_profil->num_rows == 0) {
    $koneksi->query("INSERT INTO profil_lembaga (nama_lembaga) VALUES ('An Nadzir Islamic School')");
}

// Tabel Kategori (Dinamis)
$koneksi->query("CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'box'
)");

// Isi kategori awal jika kosong
$cek_kat = $koneksi->query("SELECT * FROM kategori");
if ($cek_kat->num_rows == 0) {
    $koneksi->query("INSERT INTO kategori (nama_kategori, icon) VALUES 
    ('Elektronik', 'laptop'),
    ('Kendaraan', 'car'),
    ('Ruangan', 'building'),
    ('Mebel', 'chair'),
    ('Lainnya', 'box')");
}

// Create Tabel Aset 
$koneksi->query("CREATE TABLE IF NOT EXISTS aset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_aset VARCHAR(50) NOT NULL UNIQUE,
    nama_aset VARCHAR(150) NOT NULL,
    merk VARCHAR(100),
    warna VARCHAR(50),
    id_kategori INT NULL,
    kategori ENUM('kendaraan', 'ruangan', 'elektronik', 'mebel', 'lainnya') NULL,
    harga_beli DECIMAL(15,2) DEFAULT 0,
    tgl_beli DATE,
    ada_garansi ENUM('Y', 'N') DEFAULT 'N',
    garansi_sampai DATE NULL,
    toko_pembelian VARCHAR(150),
    kota_pembelian VARCHAR(100),
    divisi_pembeli VARCHAR(100) COMMENT 'Divisi yang melakukan pembelian',
    unit_pengguna VARCHAR(100) COMMENT 'Unit yang menggunakan',
    lokasi_simpan VARCHAR(150) COMMENT 'Ruangan/Lokasi fisik',
    kondisi ENUM('baik', 'rusak_ringan', 'rusak_berat', 'hilang') DEFAULT 'baik',
    bisa_dipinjam ENUM('Y', 'N') DEFAULT 'Y',
    status ENUM('tersedia', 'dipinjam', 'rusak', 'pemeliharaan') DEFAULT 'tersedia',
    deskripsi TEXT,
    penanggung_jawab VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Migrasi Aset: Tambah penanggung_jawab jika belum ada
$check_a_pj = $koneksi->query("SHOW COLUMNS FROM aset LIKE 'penanggung_jawab'");
if ($check_a_pj->num_rows == 0) {
    $koneksi->query("ALTER TABLE aset ADD COLUMN penanggung_jawab VARCHAR(100) NULL AFTER status");
}

// Migrasi: Tambahkan kolom baru jika tabel sudah ada sebelumnya
$check_cols = $koneksi->query("SHOW COLUMNS FROM aset LIKE 'id_kategori'");
if ($check_cols->num_rows == 0) {
    $koneksi->query("ALTER TABLE aset ADD COLUMN id_kategori INT NULL AFTER warna");
}
$check_garansi = $koneksi->query("SHOW COLUMNS FROM aset LIKE 'ada_garansi'");
if ($check_garansi->num_rows == 0) {
    $koneksi->query("ALTER TABLE aset ADD COLUMN ada_garansi ENUM('Y', 'N') DEFAULT 'N' AFTER tgl_beli");
    $koneksi->query("ALTER TABLE aset ADD COLUMN garansi_sampai DATE NULL AFTER ada_garansi");
}
$check_anggaran = $koneksi->query("SHOW COLUMNS FROM aset LIKE 'tahun_anggaran'");
if ($check_anggaran->num_rows == 0) {
    $koneksi->query("ALTER TABLE aset ADD COLUMN tahun_anggaran VARCHAR(4) NULL AFTER garansi_sampai");
}
$check_a_ruangan = $koneksi->query("SHOW COLUMNS FROM aset LIKE 'id_ruangan'");
if ($check_a_ruangan->num_rows == 0) {
    $koneksi->query("ALTER TABLE aset ADD COLUMN id_ruangan INT NULL AFTER unit_pengguna");
    $koneksi->query("ALTER TABLE aset ADD CONSTRAINT fk_aset_ruangan FOREIGN KEY (id_ruangan) REFERENCES ruangan(id) ON DELETE SET NULL");
}
$koneksi->query("ALTER TABLE aset MODIFY COLUMN kategori ENUM('kendaraan', 'ruangan', 'elektronik', 'mebel', 'lainnya') NULL");

// Migrasi Peminjaman: Tambahkan kolom identitas peminjam real (untuk multi-user account)
$check_p_nama = $koneksi->query("SHOW COLUMNS FROM peminjaman LIKE 'nama_peminjam'");
if ($check_p_nama->num_rows == 0) {
    $koneksi->query("ALTER TABLE peminjaman ADD COLUMN nama_peminjam VARCHAR(100) AFTER id_user");
    $koneksi->query("ALTER TABLE peminjaman ADD COLUMN unit_peminjam VARCHAR(100) AFTER nama_peminjam");
}

/* 
// Cek apakah tabel aset masih kosong, kita isi dummy baru (Nonaktifkan agar tidak muncul kembali setelah dihapus)
$cek_aset = $koneksi->query("SELECT * FROM aset");
if ($cek_aset->num_rows == 0) {
    $koneksi->query("INSERT IGNORE INTO aset (kode_aset, nama_aset, merk, warna, kategori, harga_beli, tgl_beli, toko_pembelian, kota_pembelian, divisi_pembeli, unit_pengguna, lokasi_simpan, kondisi, bisa_dipinjam, status) VALUES 
    ('INV-TV-001', 'Android TV ukuran 50 inci', 'TCL', 'Hitam', 'elektronik', 6000000.00, '2024-03-29', 'Informa', 'Cilegon', 'Divisi LRC', 'SDIT An Nadzir', 'Ruang Kelas 3B', 'baik', 'Y', 'tersedia'),
    ('INV-MOB-01', 'Honda Innova Zenix 2024', 'Toyota', 'Putih', 'kendaraan', 450000000.00, '2024-01-15', 'Auto2000', 'Serang', 'Sarpras Pusat', 'Yayasan An Nadzir', 'Parkir Gedung A', 'baik', 'Y', 'tersedia')");
}
*/

// Tabel Gedung
$koneksi->query("CREATE TABLE IF NOT EXISTS gedung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_gedung VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Tabel Ruangan
$koneksi->query("CREATE TABLE IF NOT EXISTS ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_gedung INT NOT NULL,
    nama_ruangan VARCHAR(100) NOT NULL,
    kode_ruangan VARCHAR(50) UNIQUE,
    kapasitas INT DEFAULT 0,
    fasilitas TEXT,
    foto_ruangan VARCHAR(255) NULL,
    bisa_dipinjam ENUM('Y', 'N') DEFAULT 'Y',
    status ENUM('tersedia', 'dipakai', 'perbaikan') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_gedung) REFERENCES gedung(id) ON DELETE CASCADE
)");

// Tabel Peminjaman (Update untuk mendukung Ruangan)
$koneksi->query("CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_aset INT NULL,
    id_ruangan INT NULL,
    nama_peminjam VARCHAR(100),
    unit_peminjam VARCHAR(100),
    tgl_pengajuan DATETIME DEFAULT CURRENT_TIMESTAMP,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali DATE NOT NULL,
    jam_mulai TIME NULL,
    jam_selesai TIME NULL,
    keperluan TEXT NOT NULL,
    status_pinjam ENUM('menunggu', 'disetujui', 'ditolak', 'selesai') DEFAULT 'menunggu',
    waktu_disetujui DATETIME NULL,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_aset) REFERENCES aset(id) ON DELETE CASCADE,
    FOREIGN KEY (id_ruangan) REFERENCES ruangan(id) ON DELETE CASCADE
)");

// Migrasi Peminjaman: Tambah id_ruangan jika belum ada
$check_p_ruang = $koneksi->query("SHOW COLUMNS FROM peminjaman LIKE 'id_ruangan'");
if ($check_p_ruang->num_rows == 0) {
    $koneksi->query("ALTER TABLE peminjaman ADD COLUMN id_ruangan INT NULL AFTER id_aset");
    $koneksi->query("ALTER TABLE peminjaman ADD CONSTRAINT fk_ruangan FOREIGN KEY (id_ruangan) REFERENCES ruangan(id) ON DELETE CASCADE");
}
$koneksi->query("ALTER TABLE peminjaman MODIFY COLUMN id_aset INT NULL");

// Migrasi Peminjaman: Tambah jam_mulai & jam_selesai jika belum ada
$check_p_jam = $koneksi->query("SHOW COLUMNS FROM peminjaman LIKE 'jam_mulai'");
if ($check_p_jam->num_rows == 0) {
    $koneksi->query("ALTER TABLE peminjaman ADD COLUMN jam_mulai TIME NULL AFTER tgl_kembali");
    $koneksi->query("ALTER TABLE peminjaman ADD COLUMN jam_selesai TIME NULL AFTER jam_mulai");
}

// Tabel Waktu (Sesi/Jam Pelajaran)
$koneksi->query("CREATE TABLE IF NOT EXISTS waktu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_waktu VARCHAR(100) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    urutan INT DEFAULT 0
)");

// Isi waktu/jam pelajaran awal jika kosong
$cek_waktu = $koneksi->query("SELECT * FROM waktu");
if ($cek_waktu->num_rows == 0) {
    $koneksi->query("INSERT INTO waktu (nama_waktu, jam_mulai, jam_selesai, urutan) VALUES 
    ('Jam 1', '07:15:00', '08:00:00', 1),
    ('Jam 2', '08:00:00', '08:45:00', 2),
    ('Jam 3', '08:45:00', '09:30:00', 3),
    ('Istirahat', '09:30:00', '10:00:00', 4),
    ('Jam 4', '10:00:00', '10:45:00', 5),
    ('Jam 5', '10:45:00', '11:30:00', 6),
    ('Sesi Siang', '13:00:00', '15:00:00', 7)");
}

// Make sure Admin Exists
$check_admin = $koneksi->query("SELECT * FROM users WHERE niy = 'admin'");
if ($check_admin->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
    $koneksi->query("INSERT INTO users (niy, nama, password, role) VALUES ('admin', 'Administrator System', '$hashed_password', 'admin')");
}
?>
