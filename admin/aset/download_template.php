<?php
header("Content-Type: application/vnd.ms-excel");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Disposition: attachment; filename=Template_Import_Aset.xls");

// Membuat tabel HTML yang akan dibaca sebagai Excel oleh MS Excel
?>
<table border="1">
    <thead>
        <tr style="background-color: #0ea5e9; color: white; font-weight: bold;">
            <th>Kode Aset</th>
            <th>Nama Aset</th>
            <th>Merk</th>
            <th>Warna</th>
            <th>Unit Pengguna</th>
            <th>Lokasi Simpan</th>
            <th>Harga Beli</th>
            <th>Tahun Anggaran</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>INV-001</td>
            <td>TV Android 50 Inch</td>
            <td>TCL</td>
            <td>Hitam</td>
            <td>SDIT An Nadzir</td>
            <td>Ruang Kelas 3B</td>
            <td>6000000</td>
            <td>2024</td>
        </tr>
        <tr>
            <td>RUANG-01</td>
            <td>Ruang Multimedia</td>
            <td></td>
            <td></td>
            <td>Sarpras</td>
            <td>Lantai 2 Gedung SMT</td>
            <td>0</td>
            <td>2024</td>
        </tr>
    </tbody>
</table>
<?php
exit;
?>
