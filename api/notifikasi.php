<?php
// api/notifikasi.php
// Endpoint untuk ambil & tandai baca notifikasi
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$user_id = (int) $_SESSION['user_id'];
$aksi    = $_GET['aksi'] ?? 'get';

if ($aksi === 'get') {
    // Ambil 8 notifikasi terbaru untuk user ini
    $notifs = $koneksi->query("SELECT * FROM notifikasi 
                               WHERE id_user = $user_id 
                               ORDER BY created_at DESC 
                               LIMIT 8");
    $data   = [];
    while ($n = $notifs->fetch_assoc()) {
        $data[] = $n;
    }
    $unread = (int) $koneksi->query("SELECT COUNT(*) FROM notifikasi WHERE id_user = $user_id AND is_read = 0")->fetch_row()[0];
    echo json_encode(['notifikasi' => $data, 'unread' => $unread]);
    exit;
}

if ($aksi === 'baca') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $koneksi->query("UPDATE notifikasi SET is_read = 1 WHERE id = $id AND id_user = $user_id");
    } else {
        // Tandai semua sebagai dibaca
        $koneksi->query("UPDATE notifikasi SET is_read = 1 WHERE id_user = $user_id");
    }
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>
