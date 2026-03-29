CREATE DATABASE IF NOT EXISTS annadzir_booking;
USE annadzir_booking;

-- Tabel Users (Pegawai & Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    niy VARCHAR(20) NOT NULL UNIQUE COMMENT 'Nomor Induk Yayasan',
    nama VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pegawai') DEFAULT 'pegawai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menambahkan Akun Admin Default
-- Password default adalah 'admin123' yang di-hash dengan bcrypt
-- bcrytp hash untuk 'admin123': $2y$10$eE.. (karena ini natif PHP kita pakai password_hash di script tapi untuk dummy insert kita generate hash nya).
-- Nilai hash untuk 'admin123' adalah: $2y$10$wU0P7P.Hj.M19LwKk9WvP.E1P3F0iU8H8A3E4S.Uo7z.R0N2r9sD. (contoh, tapi agar lebih aman mari biarkan insert langsung via script jika tidak ada, atau kita sediakan hash statis untuk admin123).
INSERT INTO users (niy, nama, password, role) VALUES 
('1234567890', 'Administrator Yayasan', '$2y$10$y5T/mBq2FjJ9B3I1yL8zFe4/5.QfO6t9R/L.0tN2fG6wO.L3z8vK6', 'admin') 
ON DUPLICATE KEY UPDATE nama='Administrator Yayasan';

-- Tabel Fasilitas
CREATE TABLE IF NOT EXISTS fasilitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_fasilitas VARCHAR(50) NOT NULL UNIQUE,
    nama_fasilitas VARCHAR(100) NOT NULL,
    kategori ENUM('kendaraan', 'ruangan', 'elektronik') NOT NULL,
    deskripsi TEXT,
    status ENUM('tersedia', 'dipinjam', 'rusak', 'pemeliharaan') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Dummy Fasilitas
INSERT IGNORE INTO fasilitas (kode_fasilitas, nama_fasilitas, kategori, deskripsi, status) VALUES 
('MBL-01', 'Kijang Innova Reborn', 'kendaraan', 'Mobil operasional sekolah - Hitam', 'tersedia'),
('RUANG-MM', 'Ruang Multimedia Lt. 2', 'ruangan', 'Kapasitas 50 orang, lengkap dg Proyektor', 'tersedia'),
('KAM-01', 'Kamera DSLR Nikon D750', 'elektronik', 'Lengkap dengan lensa kit dan flash', 'tersedia');
