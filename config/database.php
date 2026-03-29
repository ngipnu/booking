<?php
// config/database.php

$host = 'localhost';
$username = 'root'; // Setup bawaan XAMPP Mac
$password = ''; // Kosong untuk XAMPP default
$database = 'annadzir_booking'; // Nama database

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password);

// Cek koneksi db server
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Cek & Buat Database jika belum ada
$sql_db = "CREATE DATABASE IF NOT EXISTS $database";
$koneksi->query($sql_db);

// Pilih Database
$koneksi->select_db($database);

// Array of table creation queries
$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        niy VARCHAR(20) NOT NULL UNIQUE COMMENT 'Nomor Induk Yayasan',
        nama VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'pegawai') DEFAULT 'pegawai',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS fasilitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_fasilitas VARCHAR(50) NOT NULL UNIQUE,
        nama_fasilitas VARCHAR(100) NOT NULL,
        kategori ENUM('kendaraan', 'ruangan', 'elektronik') NOT NULL,
        deskripsi TEXT,
        status ENUM('tersedia', 'dipinjam', 'rusak', 'pemeliharaan') DEFAULT 'tersedia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($queries as $query) {
    $koneksi->query($query);
}

// Check jika admin default belum ada, kita insert otomatis saat aplikasi berjalan.
// NIY: admin
// Pass: admin123
$check_admin = $koneksi->query("SELECT * FROM users WHERE niy = 'admin'");
if ($check_admin->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
    $koneksi->query("INSERT INTO users (niy, nama, password, role) VALUES ('admin', 'Administrator System', '$hashed_password', 'admin')");
}
?>
