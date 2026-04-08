<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

$id_gedung = '1';
$q_count = $koneksi->query("SELECT COUNT(*) AS total FROM ruangan WHERE id_gedung = '$id_gedung'");
if (!$q_count) {
    echo "Error on valid id_gedung: " . $koneksi->error . "\n";
} else {
    print_r($q_count->fetch_assoc());
}

$id_gedung = '';
$q_count = $koneksi->query("SELECT COUNT(*) AS total FROM ruangan WHERE id_gedung = '$id_gedung'");
if (!$q_count) {
    echo "Error on empty id_gedung: " . $koneksi->error . "\n";
} else {
    print_r($q_count->fetch_assoc());
}
