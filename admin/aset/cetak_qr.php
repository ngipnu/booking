<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil semua aset
$query = "SELECT a.*, k.nama_kategori 
          FROM aset a 
          LEFT JOIN kategori k ON a.id_kategori = k.id 
          ORDER BY a.kategori, a.id DESC";
// Wait, a.kategori doesn't exist, it should be a.id_kategori
$query = "SELECT a.*, k.nama_kategori 
          FROM aset a 
          LEFT JOIN kategori k ON a.id_kategori = k.id 
          ORDER BY a.id_kategori ASC, a.id DESC";
$result = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label QR Code Inventaris</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .print-controls {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn:hover {
            opacity: 0.9;
        }
        
        .page-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5cm; /* spacing between labels */
            justify-content: flex-start;
            background: white;
            padding: 1cm;
            min-height: 29.7cm; /* A4 height approx */
            width: 21cm; /* A4 width */
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* 10cm x 3cm per user request */
        .label-box {
            width: 10cm;
            height: 3cm;
            border: 1px solid #000;
            border-radius: 4px;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            padding: 0.2cm;
            background: #fff;
            page-break-inside: avoid;
        }

        .qr-container {
            width: 2.6cm;
            height: 2.6cm;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .qr-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .info-container {
            flex-grow: 1;
            margin-left: 0.3cm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .lbl-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            color: #333;
            letter-spacing: 0.5px;
        }

        .lbl-name {
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 4px 0;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lbl-code {
            font-size: 11px;
            margin: 0 0 4px 0;
            background: #f8f9fa;
            border: 1px dashed #ccc;
            padding: 2px 5px;
            display: inline-block;
            border-radius: 3px;
            font-family: monospace;
            font-weight: bold;
        }

        .lbl-footer {
            font-size: 8px;
            color: #666;
            margin: 0;
        }

        @media print {
            body { 
                background: none; 
                padding: 0;
            }
            .print-controls { 
                display: none; 
            }
            .page-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%; /* Use full paper width */
            }
            /* Hide print URL header/footer in browsers usually */
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <div>
            <h3 style="margin: 0; color: #333;">Rekap Label QR Code</h3>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">Format Ukuran: 10cm x 3cm</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary" style="margin-right: 10px;">Kembali</a>
            <button onclick="window.print()" class="btn">🖨️ Cetak Semua Label</button>
        </div>
    </div>

    <div class="page-container">
        <?php 
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $base_domain = $_SERVER['HTTP_HOST'];
        $path_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $base_path = "/" . $path_parts[0] . "/" . $path_parts[1] . "/scan.php"; 

        if ($result && $result->num_rows > 0): 
            while ($row = $result->fetch_assoc()): 
                $public_qr_url = $protocol . "://" . $base_domain . $base_path . "?kode=" . urlencode($row['kode_aset']);
                $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&margin=0&data=" . urlencode($public_qr_url);
        ?>
        <div class="label-box">
            <div class="qr-container">
                <img src="<?= $qr_api_url ?>" alt="QR">
            </div>
            <div class="info-container">
                <p class="lbl-title">SIM Sarana Prasarana</p>
                <p class="lbl-name"><?= htmlspecialchars($row['nama_aset']) ?></p>
                <div>
                    <span class="lbl-code"><?= htmlspecialchars($row['kode_aset']) ?></span>
                </div>
                <p class="lbl-footer">Aset: <?= htmlspecialchars($row['nama_kategori']) ?> &bull; Harap dijaga</p>
            </div>
        </div>
        <?php 
            endwhile; 
        else:
        ?>
            <p style="width: 100%; text-align: center; margin-top: 50px;">Belum ada data sarana untuk dicetak.</p>
        <?php endif; ?>
    </div>
</body>
</html>
