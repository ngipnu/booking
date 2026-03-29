<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pegawai</title>
    <style>body { font-family: sans-serif; text-align: center; padding-top: 50px; }</style>
</head>
<body>
    <h2>Selamat Datang Pegawai, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
    <p>Ini adalah halaman area peminjaman pegawai (Dalam Pengembangan).</p>
    <a href="../logout.php">Keluar</a>
</body>
</html>
